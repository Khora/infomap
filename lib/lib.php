<?php
    // set max exec time to infinity
    set_time_limit(0);
    
    // init session and other data
    session_start();
    initSessionVariables();
    checkDataSource();

    /*
     * Initialisation method, to be called first.
     */
    function initSessionVariables() {
        // how old (in seconds) data may be at most (7 days)
        $_SESSION["dataExpiryTimeSeconds"] = 7 * 24 * 60 * 60;
        
        // cache for the lat long geocoded positions of addresses
        $_SESSION["dataCacheGeocodedPositionOfAddresses"] = "downloads/idsToAdressLocationsMapping.json";
        
        // where to save the data cache to
        $_SESSION["dataCacheFilePathEnglish"] = "downloads/currentDataCacheEnglish.csv";
        $_SESSION["dataCacheFilePathArabic"] = "downloads/currentDataCacheArabic.csv";
        $_SESSION["dataCacheFilePathFarsi"] = "downloads/currentDataCacheFarsi.csv";
        $_SESSION["dataCacheFilePathGreek"] = "downloads/currentDataCacheGreek.csv";
        $_SESSION["dataCacheFilePathFrench"] = "downloads/currentDataCacheFrench.csv";
        $_SESSION["dataCacheFilePathUrdu"] = "downloads/currentDataCacheUrdu.csv";
        $_SESSION["dataCacheFilePathKurdish"] = "downloads/currentDataCacheKurdish.csv";
        
        // this is the data source for the PHP system, it is read-only because of the parameter "export"
        // so from the PHP system's point of view no possibility to change anything there (for security reasons)
        $_SESSION["spreadsheetUrlEnglish"] = "https://docs.google.com/spreadsheets/d/1gknk1sQaYNBDdisf2Aj0jmxKdyIx9PrDOArB-oeWxwk/export?format=csv&gid=0";
        $_SESSION["spreadsheetUrlArabic"] = "https://docs.google.com/spreadsheets/d/1gknk1sQaYNBDdisf2Aj0jmxKdyIx9PrDOArB-oeWxwk/export?format=csv&gid=272972858";
        $_SESSION["spreadsheetUrlFarsi"] = "https://docs.google.com/spreadsheets/d/1gknk1sQaYNBDdisf2Aj0jmxKdyIx9PrDOArB-oeWxwk/export?format=csv&gid=2092257293";
        $_SESSION["spreadsheetUrlGreek"] = "https://docs.google.com/spreadsheets/d/1gknk1sQaYNBDdisf2Aj0jmxKdyIx9PrDOArB-oeWxwk/export?format=csv&gid=1538519839";
        $_SESSION["spreadsheetUrlFrench"] = "https://docs.google.com/spreadsheets/d/1gknk1sQaYNBDdisf2Aj0jmxKdyIx9PrDOArB-oeWxwk/export?format=csv&gid=2011609971";
        $_SESSION["spreadsheetUrlUrdu"] = "https://docs.google.com/spreadsheets/d/1gknk1sQaYNBDdisf2Aj0jmxKdyIx9PrDOArB-oeWxwk/export?format=csv&gid=1531450539";
        $_SESSION["spreadsheetUrlKurdish"] = "https://docs.google.com/spreadsheets/d/1gknk1sQaYNBDdisf2Aj0jmxKdyIx9PrDOArB-oeWxwk/export?format=csv&gid=1847047387";
        
        // get if the client is a mobile device
        $mobile = "false";
        if (isset($_SESSION["mobile"])) {
            $mobile = $_SESSION["mobile"];
        }
        if (isset($_GET["mobile"])) {
            $mobile = strtolower(htmlspecialchars($_GET["mobile"]));
        }
        $_SESSION["mobile"] = $mobile;
        
        // the chosen language of the client
        $language = "English";
        if (isset($_SESSION["language"])) {
            $language = $_SESSION["language"];
        }
        if (isset($_GET["language"])) {
            $language = htmlspecialchars($_GET["language"]);
        }
        $_SESSION["language"] = $language;
    }

    /*
     * Checks how old the cached data is and refreshes it if necessary
     */
    function checkDataSource() {  
        $performDataUpdate = false;
        if (isset($_GET["reload"]) && strcmp($_GET["reload"], "true") == 0) {
            $performDataUpdate = true;
        }
        
        $nowInSecondsUnixTimestamp = time();
        $ageOfCacheFiles = $nowInSecondsUnixTimestamp - filemtime($_SESSION["dataCacheFilePathEnglish"]);
        if ($performDataUpdate || !file_exists($_SESSION["dataCacheFilePathEnglish"]) || $ageOfCacheFiles > $_SESSION["dataExpiryTimeSeconds"]) {
            $performDataUpdate = true;
        }
        
        if ($performDataUpdate) {
            debug("Now performing data cache update!");
        }
        
        // English, Arabic, Farsi, Greek, French, Urdu, Kurdish
        $supportedLanguages = array("English", "Arabic", "Farsi", "Greek", "French", "Urdu", "Kurdish");
        foreach ($supportedLanguages as $l) {
            if ($performDataUpdate) {
                downloadFileViaCurl($_SESSION["spreadsheetUrl" . $l], $_SESSION["dataCacheFilePath" . $l]);
                debug("Downloaded: " . $l . " data file.");
            }
        }
        
        $idsToAddressStrings = array();
        $dataEnglish = getFileContentAsCsv($_SESSION["dataCacheFilePathEnglish"]);
        for ($i = 1; $i < count($dataEnglish); $i++) {
            $id = htmlspecialchars(getOrDefault($dataEnglish, $dataEnglish, $i, 0));
            $address = htmlspecialchars(getOrDefault($dataEnglish, $dataEnglish, $i, 4));
            $idsToAddressStrings += array($id => $address);
        }
        
        if ($performDataUpdate) {
            $idsToGeoPositions = array();
            $i = 0;
            foreach ($idsToAddressStrings as $id => $address) {
                $idsToGeoPositions += array($id => mapquestGeocodeApiAddressToLocation($address));
            }
            
            writeStringToFile("downloads/idsToAdressLocationsMapping.json", json_encode($idsToGeoPositions));
            debug("Wrote idsToAdressLocationsMapping.json file.");
        }
    }
    
    /*
     * Getter for if the client wants to be served the mobile version
     */
    function isMobile() {
        if (strcmp($_SESSION["mobile"], "true") == 0) {
            return true;
        }
        return false;
    }
    
    /*
     * Getter for the language of the client.
     */
    function getLanguage() {
        $currentLanguage = "English";
        if (isset($_SESSION["language"]) && strcmp($_SESSION["language"], "") != 0) {
            $currentLanguage = $_SESSION["language"];
        }
        return $currentLanguage;
    }
    
    /*
     * Gets the common content of the HTML head tag.
     */
    function getHeadContent() {
        if (!isMobile()) {
            return '<link rel="stylesheet" type="text/css" href="css/style.css">
            <script language="javascript" type="text/javascript" src="lib/lib.js"></script>
            <link rel="icon" type="image/x-icon" href="img/favicon.png">
            <meta charset="utf-8"/>
            <script>
                if (isMobileOrTablet()) {
                    document.location = "' . basename($_SERVER['PHP_SELF']) . '?mobile=true";
                }
            </script>';
        } else {
            return '<link rel="stylesheet" type="text/css" href="css/styleMobile.css">
            <script language="javascript" type="text/javascript" src="lib/lib.js"></script>
            <link rel="icon" type="image/x-icon" href="img/favicon.png">
            <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0"/>
            <meta charset="utf-8"/>';
        }
    }
    
    /*
     * Gets the content of the head tag needed for the leaflet map view.
     */
    function getPreparationForLeaflet() {
        return '<link rel="stylesheet" href="https://unpkg.com/leaflet@1.3.4/dist/leaflet.css"
               integrity="sha512-puBpdR0798OZvTTbP4A8Ix/l+A4dHDD0DGqYW6RQ+9jxkRFclaxxQb/SJAWZfWAkuyeQUytO7+7N4QKrDh+drA=="
               crossorigin=""/>
             <script src="https://unpkg.com/leaflet@1.3.4/dist/leaflet.js"
               integrity="sha512-nMMmRyTVoLYqjP9hrbed9S+FzjZHW5gY1TWCHA5ckwXZBadntCNs8kEqAWdrb9O7rxbCaA4lKTIWjDXZxflOcA=="
               crossorigin=""></script>
               <link rel="stylesheet" href="lib/LeafletExtraMarkers/dist/css/leaflet.extra-markers.min.css">
               <script src="lib/LeafletExtraMarkers/dist/js/leaflet.extra-markers.min.js"></script>';
    }
    
    /*
     * Gets the top area containing the common menu.
     */
    function getTopArea($title) {
        if (!isMobile()) {
            return '<div id="logo">
                <a href="infomap.php"><img src="img/khoraLogo.png"></a>
            </div>
            <div id="title"><h1>' . $title . '</h1></div>
            <div id="topBackground"></div>
            <div id="menuArea">
                <p id="languageTextField" style="width: 100%; text-align: center; vertical-align: middle; font-size: 15px; font-weight: bold;">&nbsp;</p>
                <table style="width: 100%; text-align: right;" onmouseleave="document.getElementById(\'languageTextField\').innerHTML=\'&nbsp;\'">
                    <tr>
                        <td><a href="' . basename($_SERVER['PHP_SELF']) . '?language=English"><img src="img/britainFlag.png" style="height: 30px;" onmouseover="document.getElementById(\'languageTextField\').innerHTML=\'&#x25A0;&#x25A0;&#x25A0;     English     &#x25A0;&#x25A0;&#x25A0;\';"></a></td>
                        <td><a href="' . basename($_SERVER['PHP_SELF']) . '?language=Greek"><img src="img/greeceFlag.png" style="height: 30px;" onmouseover="document.getElementById(\'languageTextField\').innerHTML=\'&#x25A0;&#x25A0;&#x25A0;     &#x3B5;&#x3BB;&#x3BB;&#x3B7;&#x3BD;&#x3B9;&#x3BA;&#x3AC; (Greek)     &#x25A0;&#x25A0;&#x25A0;\';"></a></td>
                        <td><a href="' . basename($_SERVER['PHP_SELF']) . '?language=French"><img src="img/franceFlag.png" style="height: 30px;" onmouseover="document.getElementById(\'languageTextField\').innerHTML=\'&#x25A0;&#x25A0;&#x25A0;     Fran&#xE7;ais (French)     &#x25A0;&#x25A0;&#x25A0;\';"></a></td>
                        <td><a href="' . basename($_SERVER['PHP_SELF']) . '?language=Farsi"><img src="img/afghanistanFlag.png" style="height: 30px;" onmouseover="document.getElementById(\'languageTextField\').innerHTML=\'&#x25A0;&#x25A0;&#x25A0;     &#x641;&#x627;&#x631;&#x633;&#x6CC; (Farsi)     &#x25A0;&#x25A0;&#x25A0;\';"></a></td>
                        <td><a href="' . basename($_SERVER['PHP_SELF']) . '?language=Arabic"><img src="img/syriaFlag.png" style="height: 30px;" onmouseover="document.getElementById(\'languageTextField\').innerHTML=\'&#x25A0;&#x25A0;&#x25A0;     &#x627;&#x644;&#x639;&#x64E;&#x631;&#x64E;&#x628;&#x650;&#x64A;&#x64E;&#x651;&#x629;&#x200E; (Arabic)     &#x25A0;&#x25A0;&#x25A0;\';"></a></td>
                        <td><a href="' . basename($_SERVER['PHP_SELF']) . '?language=Urdu"><img src="img/pakistanFlag.png" style="height: 30px;" onmouseover="document.getElementById(\'languageTextField\').innerHTML=\'&#x25A0;&#x25A0;&#x25A0;     &#x627;&#x64F;&#x631;&#x62F;&#x64F;&#x648;&#x202C; (Urdu)     &#x25A0;&#x25A0;&#x25A0;\';"></a></td>
                        <td><a href="' . basename($_SERVER['PHP_SELF']) . '?language=Kurdish"><img src="img/kurdistanFlag.png" style="height: 30px;" onmouseover="document.getElementById(\'languageTextField\').innerHTML=\'&#x25A0;&#x25A0;&#x25A0;     &#x6A9;&#x648;&#x631;&#x62F;&#x6CC; (Kurdish)     &#x25A0;&#x25A0;&#x25A0;\';"></a></td>
                    </tr>
                </table>
                <table style="width: 97%; text-align: right; margin-right: 0px; margin-left: auto;">
                    <tr>
                        <td>' . getButton(i18n("help"), "img/questionmark.png", "document.location='help.php';") . '</td>
                        <td>' . getButton(i18n("reloadData"), "img/reload.png", "clearFavorites(); document.location='infomap.php?reload=true';") . '</td>
                        <td><a href="https://www.facebook.com/KhoraAthens/"><img id="socialNetworkButton" src="img/facebook.png"></a></td>
                        <td><a href="https://www.instagram.com/khoraathens/"><img id="socialNetworkButton" src="img/instagram.png"></a></td>
                        <td><a href="http://www.khora-athens.org/"><img id="socialNetworkButton" src="img/webpage.png"></a></td>
                    </tr>
                </table>
            </div>
            <div id="shadow"></div>
            <script>
                if (window.addEventListener) {
                    window.addEventListener(\'resize\', function() {
                        resizeHeaderElements();
                    }, true);
                }
                
                resizeHeaderElements();
            </script>';
        } else {
            return '<div id="mobileMenuBackground">
                </div>
                <div id="mobileLogo">
                    <a href="infomap.php"><img src="img/khoraLogo.png" style="width: 9mm; height: 8mm;"></a>
                </div>
                <div id="mobileTitle">
                    ' . $title . '
                </div>
                <div id="mobileBurgerIcon">
                    <img src="img/burgerMenuIcon.png" id="mobileLogoImg" style="width: 9mm; height: 9mm;" onclick="closeOrOpenMobileMenu();">
                </div>
                <div id="mobileShadow">
                </div>
                
                <div id="mobileMenuContent" style="background: red;">
                <table id="mobileMenuTable" class="gridtable" width="100%">
                    <tr>
                        <td style="height: 10mm; vertical-align: middle; border-color: #ffffff;">
                            <a href="' . basename($_SERVER['PHP_SELF']) . '?language=English" style="color: #555555; text-decoration: none;"><img src="img/britainFlag.png" style="height: 8mm; vertical-align: middle;"><span style="font-size: 5mm;">&nbsp;&nbsp;&nbsp;English</span></a>
                        </td>
                    </tr>
                    <tr>
                        <td style="height: 10mm; vertical-align: middle; border-color: #ffffff;">
                            <a href="' . basename($_SERVER['PHP_SELF']) . '?language=Greek" style="color: #555555; text-decoration: none;"><img src="img/greeceFlag.png" style="height: 8mm; vertical-align: middle;"><span style="font-size: 5mm;">&nbsp;&nbsp;&nbsp;&#x3B5;&#x3BB;&#x3BB;&#x3B7;&#x3BD;&#x3B9;&#x3BA;&#x3AC; (Greek)</span></a>
                        </td>
                    </tr>
                    <tr>
                        <td style="height: 10mm; vertical-align: middle; border-color: #ffffff;">
                            <a href="' . basename($_SERVER['PHP_SELF']) . '?language=French" style="color: #555555; text-decoration: none;"><img src="img/franceFlag.png" style="height: 8mm; vertical-align: middle;"><span style="font-size: 5mm;">&nbsp;&nbsp;&nbsp;Fran&#xE7;ais (French)</span></a>
                        </td>
                    </tr>
                    <tr>
                        <td style="height: 10mm; vertical-align: middle; border-color: #ffffff;">
                            <a href="' . basename($_SERVER['PHP_SELF']) . '?language=Farsi" style="color: #555555; text-decoration: none;"><img src="img/afghanistanFlag.png" style="height: 8mm; vertical-align: middle;"><span style="font-size: 5mm;">&nbsp;&nbsp;&nbsp;&#x641;&#x627;&#x631;&#x633;&#x6CC; (Farsi)</span></a>
                        </td>
                    </tr>
                    <tr>
                        <td style="height: 10mm; vertical-align: middle; border-color: #ffffff;">
                            <a href="' . basename($_SERVER['PHP_SELF']) . '?language=Arabic" style="color: #555555; text-decoration: none;"><img src="img/syriaFlag.png" style="height: 8mm; vertical-align: middle;"><span style="font-size: 5mm;">&nbsp;&nbsp;&nbsp;&#x627;&#x644;&#x639;&#x64E;&#x631;&#x64E;&#x628;&#x650;&#x64A;&#x64E;&#x651;&#x629;&#x200E; (Arabic)</span></a>
                        </td>
                    </tr>
                    <tr>
                        <td style="height: 10mm; vertical-align: middle; border-color: #ffffff;">
                            <a href="' . basename($_SERVER['PHP_SELF']) . '?language=Urdu" style="color: #555555; text-decoration: none;"><img src="img/pakistanFlag.png" style="height: 8mm; vertical-align: middle;"><span style="font-size: 5mm;">&nbsp;&nbsp;&nbsp;&#x627;&#x64F;&#x631;&#x62F;&#x64F;&#x648;&#x202C; (Urdu)</span></a>
                        </td>
                    </tr>
                    <tr>
                        <td style="height: 10mm; vertical-align: middle; border-color: #ffffff;">
                            <a href="' . basename($_SERVER['PHP_SELF']) . '?language=Kurdish" style="color: #555555; text-decoration: none;"><img src="img/kurdistanFlag.png" style="height: 8mm; vertical-align: middle;"><span style="font-size: 5mm;">&nbsp;&nbsp;&nbsp;&#x6A9;&#x648;&#x631;&#x62F;&#x6CC; (Kurdish)</span></a>
                        </td>
                    </tr>
                    <tr>
                        <td style="height: 10mm; vertical-align: middle; border-color: #ffffff;">
                            <a href="help.php" style="color: #555555; text-decoration: none;">&nbsp;<img src="img/questionmark.png" style="width: 8mm; vertical-align: middle;"><span style="font-size: 5mm;">&nbsp;&nbsp;&nbsp;&nbsp;' . i18n("help") . '</span></a>
                        </td>
                    </tr>
                    <tr>
                        <td style="height: 10mm; vertical-align: middle; border-color: #ffffff;">
                            <a onclick="clearFavorites(); document.location=\'infomap.php?reload=true\';" style="color: #555555; text-decoration: none;">&nbsp;<img src="img/reload.png" style="width: 8mm; vertical-align: middle;"><span style="font-size: 5mm;">&nbsp;&nbsp;&nbsp;&nbsp;' . i18n("reloadData") . '</span></a>
                        </td>
                    </tr>
                    <tr>
                        <td style="height: 10mm; vertical-align: middle; border-color: #ffffff;">
                            <a href="https://www.facebook.com/KhoraAthens/" style="color: #555555; text-decoration: none;">&nbsp;<img src="img/facebook.png" style="width: 8mm; vertical-align: middle;"><span style="font-size: 5mm;">&nbsp;&nbsp;&nbsp;&nbsp;KHORA FACEBOOK</span></a>
                        </td>
                    </tr>
                    <tr>
                        <td style="height: 10mm; vertical-align: middle; border-color: #ffffff;">
                            <a href="https://www.instagram.com/khoraathens/" style="color: #555555; text-decoration: none;">&nbsp;<img src="img/instagram.png" style="width: 8mm; vertical-align: middle;"><span style="font-size: 5mm;">&nbsp;&nbsp;&nbsp;&nbsp;KHORA INSTAGRAM</span></a>
                        </td>
                    </tr>
                    <tr>
                        <td style="height: 10mm; vertical-align: middle; border-color: #ffffff;">
                            <a href="http://www.khora-athens.org/" style="color: #555555; text-decoration: none;">&nbsp;<img src="img/webpage.png" style="width: 8mm; vertical-align: middle;"><span style="font-size: 5mm;">&nbsp;&nbsp;&nbsp;&nbsp;KHORA WEBPAGE</span></a>
                        </td>
                    </tr>
                    <tr>
                        <td id="switchToDesktop" style="height: 10mm; vertical-align: middle; border-color: #ffffff;">
                            <a href="' . basename($_SERVER['PHP_SELF']) . '?mobile=false" style="color: #555555; text-decoration: none;">&nbsp;<img src="img/computer.png" style="width: 8mm; vertical-align: middle;"><span style="font-size: 5mm;">&nbsp;&nbsp;&nbsp;&nbsp;' . i18n("desktopVersion") . '</span></a>
                        </td>
                    </tr>
                </table>
            </div>

            <script>
                document.getElementById("mobileMenuContent").style.display = "none";
                if (elementIsVisible(document.getElementById("mobileMenuContent"))) {
                    console.log("test");
                }
                    
                function closeOrOpenMobileMenu() {
                    if (!elementIsVisible(document.getElementById("mobileMenuContent"))) {
                        console.log("test");
                        changeIcon(document.getElementById("mobileLogoImg"), "img/burgerMenuCloseIcon.png");
                    } else {
                        changeIcon(document.getElementById("mobileLogoImg"), "img/burgerMenuIcon.png");
                    }
                    
                    changeIsOpen(document.getElementById("mobileMenuContent"));
                    changeIsOpen(document.getElementById("content"));
                }
                
                if (isMobileOrTablet()) {
                    document.getElementById("switchToDesktop").innerHTML = "";
                }
            </script>';
        }
    }
    
    /*
     * Gets the search bar and its functionality.
     */
    function getSearch($filterOnFavorites) {
        if (!isMobile()) {
            $filterOnFavoritesString = "";
            if ($filterOnFavorites) {
                $filterOnFavoritesString = "delayTimeMs = 500;
                                            doTaskAfterTimeWhenNotRescheduled('searchTable(false, true)', delayTimeMs);
                                            doTaskAfterTimeWhenNotRescheduled('updateAllStarImages()', delayTimeMs);
                                            doTaskAfterTimeWhenNotRescheduled('hideAllInTableThatAreNotFavorites(false)', delayTimeMs);
                                            doTaskAfterTimeWhenNotRescheduled('filterMapOnFavorites()', delayTimeMs);";
            } else {
                $filterOnFavoritesString = "delayTimeMs = 500;
                                            doTaskAfterTimeWhenNotRescheduled('searchTable(false, false)', delayTimeMs);";
            }
            
            return '<div id="searchImage">
                <img src="img/searchIcon.png"">
            </div>
            <div id="search">
                <input type="text" id="infomapSearch" placeholder="search..." onkeyup="' . $filterOnFavoritesString . '">
            </div>
            <div id="searchGo">
                <img src="img/searchClearIcon.png" onclick="document.getElementById(\'infomapSearch\').value = \'\';' . $filterOnFavoritesString . '">&nbsp;&nbsp;
                <img src="img/searchGoIcon.png" onclick="' . $filterOnFavoritesString . '">
            </div>';

        } else {
            return '<div id="searchImage">
                <img src="img/searchIcon.png"">
            </div>
            <div id="search">
                <input type="text" id="infomapSearch" placeholder="search..." onkeyup="searchTable(true, false)">
            </div>
            <div id="searchGo">
                <img src="img/searchGoIcon.png" onclick="searchTable(true, false)">
            </div>';

        }
    }
    
    /*
     * Gets the map search bar and its functionality.
     */
    function getMapSearch() {
        return '<div id="searchImage">
            <img src="img/searchIcon.png"">
        </div>
        <div id="search">
            <input type="text" id="infomapSearch" placeholder="search..." onkeyup="searchMap()">
        </div>
        <div id="searchGo">
            <img src="img/searchGoIcon.png" onclick="searchMapDirectly()">
        </div>';
    }
    
    /*
     * Gets a formatted button containing the given text and image.
     * The Javascript given to this function will be executed on click.
     */
    function getButton($textToDisplay, $imagePath, $javascriptToCallOnClick) {
        $textToDisplay = strtoupper($textToDisplay);
        $textToDisplay = str_replace("&NBSP;", "&nbsp;", $textToDisplay);
        return '<div id="buttonRoundedEdges" onclick="' . $javascriptToCallOnClick . '" style="cursor: pointer; text-align: left;">
                <img src="' . $imagePath . '">
                <div id="buttonRoundedEdgesText" style="cursor: pointer;">' . $textToDisplay . '&nbsp;&nbsp;</div>
            </div>';
    }
    
    function getHeaderLabels() {
        $category = "Category";
        $name = "Name";
        $openingHours = "Opening Hours";
        $address = "Address";
        $description = "Description";
        $contactName = "Contact Name";
        $emailAddress = "Email Address";
        $phoneNumber = "Phone Number";
        $website = "Website";
        $facebook = "Facebook";
        $notes = "Notes";
        $services = "Services";
        $type = "Type";
        $division = "Division";
        $dateLastUpdated = "Date last updated";
        if (getLanguage() === "French") {
            $category = "Category";
            $name = "Nom";
            $openingHours = "Opening Hours";
            $address = "Address";
            $description = "Description";
            $contactName = "Contact Name";
            $emailAddress = "Email Address";
            $phoneNumber = "Phone Number";
            $website = "Website";
            $facebook = "Facebook";
            $notes = "Notes";
            $services = "Services";
            $type = "Type";
            $division = "Division";
            $dateLastUpdated = "Date last updated";
        }
        return array(
            "category" => $category,
            "name" => $name,
            "openingHours" => $openingHours,
            "address" => $address,
            "description" => $description,
            "contactName" => $contactName,
            "emailAddress" => $emailAddress,
            "phoneNumber" => $phoneNumber,
            "website" => $website,
            "facebook" => $facebook,
            "notes" => $notes,
            "services" => $services,
            "type" => $type,
            "division" => $division,
            "dateLastUpdated" => $dateLastUpdated);
    }
    
    function mapquestGeocodeApiAddressToLocation($addressString) {
        // TODO extract constant strings
        // example: http://www.mapquestapi.com/geocoding/v1/address?key=1KAc2n9SOCMZD7dAO8hj2egYO4JZwoKk&location=Stournari%2014,Athens,Greece&maxResults=1
        $url = "http://www.mapquestapi.com/geocoding/v1/address?key=1KAc2n9SOCMZD7dAO8hj2egYO4JZwoKk&location=" . urlencode($addressString) . "&maxResults=1";
        $retStr = getFileContent(downloadFileViaCurl($url, "downloads/currentGeoCodingRequest.txt"));
        $startLat = strpos($retStr, '"lat":') + strlen('"lat":');
        $retStr = substr($retStr, $startLat);
        $endLon = strpos($retStr, '}');
        $retStr = substr($retStr, 0, $endLon);
        $latLonStr = explode(',"lng":', $retStr);
        return array(floatval($latLonStr[0]), floatval($latLonStr[1]));
    }
    
    function downloadStaticMapWithMarkers($center, $zoomLevel, $size, $markers) {
        // example: https://api.mapbox.com/styles/v1/mapbox/streets-v10/static/pin-s-1+000000(23.734849,37.986317),pin-s-10+000000(23.734949,37.984317)/23.734849,37.986317,13.67,0.00,0.00/1000x600@2x?access_token=pk.eyJ1IjoiY2FlemUiLCJhIjoiY2ptdXp6d3QyMGpweDN3bzhweTZ5MjlqNyJ9.Si72Tah5nyXFdynwiSY9yg
        
        $pinString = "";
        for ($i = 0; $i < count($markers); $i++) {
            if ($pinString !== "") {
                $pinString = $pinString . ",";
            }
            $pinString = $pinString . "pin-s-" . $i . "+000000(" . $markers[$i][1] . "," . $markers[$i][0] . ")";
        }
        
        $url = "https://api.mapbox.com/styles/v1/mapbox/streets-v10/static/" . $pinString . "/" . $center[1] . "," . $center[0] . "," . $zoomLevel . ",0.00,0.00/" . $size . "@2x?access_token=pk.eyJ1IjoiY2FlemUiLCJhIjoiY2ptdXp6d3QyMGpweDN3bzhweTZ5MjlqNyJ9.Si72Tah5nyXFdynwiSY9yg";
        return downloadFileViaCurl($url, "downloads/map.png");
    }
    
    function getLatLongPositionOfId($id) {
        return getFileContentAsObjectFromJsonString($_SESSION["dataCacheGeocodedPositionOfAddresses"])[$id];
    }
    
    /*
     * Gets the value of the array if it is not empty or the corresponding value of the default array
     */
    function getOrDefault($array, $defaultArray, $i, $j) {
        if ($i < count($array) && $j < count($array[$i]) && isset($array[$i][$j]) && strcmp($array[$i][$j], "") != 0) {
            return $array[$i][$j];
        }
        return $defaultArray[$i][$j];
    }
    
    function error($message) {
        echo "<p style='z-index: 50005;font-size: 60px; background-color: red;'>ERROR: " . $message . "</p>";
    }
    
    function debug($message) {
        echo "<p style='z-index: 50000; font-size: 8px'>Debug: " . $message . "</p>";
    }
    
    function i18n($key) {
        // find corresponding value to key
        $value = json_decode(getFileContent("i18n/i18n.json"), true)[getLanguage()][$key];
        // in the end make spaces to hard spaces
        return str_replace(" ", "&nbsp;", htmlentities($value));
    }
    
    /*
     * Writes the content to the given file
     */
    function writeStringToFile($path, $content) {
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }
        
        file_put_contents($path, $content);
    }
    
    /*
     * Gets the content of the given file as a string
     */
    function getFileContent($path) {
        $content = "";
        if (($handle = fopen($path, "r")) !== FALSE) {
            while (($line = fgets($handle)) !== false) {
                $content = $content . $line;
            }
            fclose($handle);
        } else {
            error("Problem loading from " . $path . "!");
        }
        return $content;
    }
    
    /*
     * Gets the content of the given file as a two dimensional array
     */
    function getFileContentAsCsv($path) {
        $dataToReturn = array();
        if (($handle = fopen($path, "r")) !== FALSE) {
            // read all cells
            while (($line = fgetcsv($handle, 0, ',')) !== false) {
                // two dimensional array
                $lineData = array();
                foreach ($line as $cell) {
                    array_push($lineData, $cell);
                }
                array_push($dataToReturn, $lineData);
            }
            fclose($handle);
        } else {
            error("Problem reading cached file, path: " . $path . "!");
        }
        
        return $dataToReturn;
    }
    
    /*
     * Reads the content of the given file as a json file and returns it as PHP objects
     */
    function getFileContentAsObjectFromJsonString($path) {
        return json_decode(getFileContent($path), true);
    }
    
    function downloadFileViaCurl($url, $destination) {
        if (!ini_set('default_socket_timeout', 1500)) {
            error("Unable to change socket timeout!");
        }
                    
        if (!is_dir(dirname($destination))) {
            mkdir(dirname($destination), 0777, true);
        }
        
        $c = curl_init();
        curl_setopt($c, CURLOPT_URL, $url);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c, CURLOPT_SSLVERSION, 6);
        $data = curl_exec($c);
        $error = curl_error($c); 
        curl_close($c);
        
        if ($error !== "") {
            error($error);
        }

        $file = fopen($destination, "w+");
        fputs($file, $data);
        fclose($file);
        
        return $destination;
    }

    function downloadHtmlToPdf($source) {
        $destination = "downloads/pdfExport.pdf";
        if (!is_dir(dirname($destination))) {
            mkdir(dirname($destination), 0777, true);
        }
        
        $c = curl_init();

        curl_setopt_array($c, array(
            CURLOPT_URL => "https://api.pdfshift.io/v2/convert/",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode(array("source" => $source, "landscape" => true, "use_print" => false)),
            CURLOPT_HTTPHEADER => array('Content-Type:application/json'),
            CURLOPT_USERPWD => '03e85607fee24a56b9f7b1584ca6e99a'
        ));
        
        $data = curl_exec($c);
        $error = curl_error($c); 
        curl_close($c);
        
        if ($error !== "") {
            error($error);
        }

        $file = fopen($destination, "w+");
        fputs($file, $data);
        fclose($file);
        
        return $destination;
    }
?>