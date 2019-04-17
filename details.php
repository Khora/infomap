<?php
    include "lib/lib.php";
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Khora Infomap | DETAILS</title>
        <?php
            echo getHeadContent();
        ?>
    </head>
    <body>
        <?php
            $headerText = i18n("details");
            $id = -1;
            if (!isset($_GET["id"])) {
                $headerText = i18n("detailsError");
            } else {
                $id = htmlspecialchars($_GET["id"]);
                $headerText = i18n("details") . "&nbsp;-&nbsp;ID:&nbsp;" . $id;
            }
            echo getTopArea($headerText);
        ?>
        <div id="content" style='top: 200px;'>
            <?php
                echo "<table style='margin-bottom: 10px;'>
                        <tr>
                            <td style='padding: 10px; padding-left: 5mm;'>
                                " . getButton(i18n("back"), "img/backArrow.png", "document.location='infomap.php';") . "
                            </td>
                            <td style='padding: 10px; padding-left: 5mm; position: absolute; right: 3mm;'>
                                " . getButton(i18n("reportIncorrectData"), "img/reportIncorrectData.png", "document.location='details.php?id=" . $id . "&reportIncorrectDataId=" . $id . "';") . "
                            </td>
                        </tr>
                    </table>";
                
                if ($id != -1) {
                    $details = getDetailsContentFromSpreadsheet($id);
                    
                    // send an e-mail if someone marked this data as incorrect
                    if (isset($_GET["reportIncorrectDataId"])) {
                        reportDataAsIncorrect($id, $details['name']);
                        echo "<h2 style='color: green;'>&nbsp;&nbsp;&nbsp;" . i18n("reportIncorrectDataDone") . "</h2>";
                    }
                    
                    $websiteString = $details['website'];
                    $matches = array();
                    preg_match_all('/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;\(\)]*[-a-z0-9+&@#\/%=~_|\(\)]/i', $websiteString, $matches);
                    $matches = $matches[0];
                    for ($i = 0; $i < count($matches); $i++) {
                        $currentWebsiteString = $matches[$i];
                        if (strpos($currentWebsiteString, "http") === false) {
                            $currentWebsiteString = "http://" . $currentWebsiteString;
                        }
                        $currentWebsiteString = str_replace("p//", "p://" , $currentWebsiteString);
                        $currentWebsiteString = str_replace("ps//", "ps://" , $currentWebsiteString);
                        $websiteString = str_replace($matches[$i], "<a href='" . $currentWebsiteString . "'>" . $matches[$i] . "</a>" , $websiteString);
                    }
                    $websiteString = str_replace("\n", "<br>", $websiteString);
                    
                    $facebookString = $details['facebook'];
                    $matches = array();
                    preg_match_all('/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;\(\)]*[-a-z0-9+&@#\/%=~_|\(\)]/i', $facebookString, $matches);
                    $matches = $matches[0];
                    for ($i = 0; $i < count($matches); $i++) {
                        $currentFacebookString = $matches[$i];
                        if (strpos($currentFacebookString, "http") === false) {
                            $currentFacebookString = "http://" . $currentFacebookString;
                        }
                        $currentFacebookString = str_replace("p//", "p://" , $currentFacebookString);
                        $currentFacebookString = str_replace("ps//", "ps://" , $currentFacebookString);
                        $facebookString = str_replace($matches[$i], "<a href='" . $currentFacebookString . "'>" . $matches[$i] . "</a>" , $facebookString);
                    }
                    $facebookString = str_replace("\n", "<br>", $facebookString);
                    
                    echo "
                    <script>
                        function updateIsFavorite() {
                            if (getFavorites().indexOf(" . $id . ") < 0) {
                                document.getElementById(\"favoriteStar\").innerHTML = \"<img src='img/starInactive.png'>\";
                            } else {
                                document.getElementById(\"favoriteStar\").innerHTML = \"<img src='img/starActive.png'>\";
                            }
                        }
                    </script>";
                    
                    if (!isMobile()) {
                        echo "<table id='table' class='gridtable' style='margin: 5mm; width: calc(100% - 10mm);'>
                                <tr>
                                    <td style=\"color: #000000; background-color: #999999;\">" . i18n("favorite") . "</td>
                                    <td id=\"favoriteStar\" onClick='toggleFavoritesAndUpdateStarImages([" . $id . "]);updateIsFavorite();' style=\"text-align: center; color: #000000; background-color: #999999;\"><img src='img/starActive.png'></td>
                                </tr>
                                <tr>
                                    <th>" . i18n("id") . "</th>
                                    <th>" . $id . "</th>
                                </tr>
                                <tr>
                                    <td>" . i18n("category") . "</td>
                                    <td>" . $details['category'] . "</td>
                                </tr>
                                <tr>
                                    <td>" . i18n("name") . "</td>
                                    <td>" . $details['name'] . "</td>
                                </tr>
                                <tr>
                                    <td>" . i18n("openingHours") . "</td>
                                    <td>" . $details['openingHours'] . "</td>
                                </tr>
                                <tr>
                                    <td>" . i18n("address") . "</td>
                                    <td><a href='https://www.google.de/maps/place/" . $details['address'] . "'>" . $details['address'] . "</td>
                                </tr>
                                <tr>
                                    <td>" . i18n("services") . "</td>
                                    <td>" . $details['services'] . "</td>
                                </tr>
                                <tr>
                                    <td>" . i18n("description") . "</td>
                                    <td>" . $details['description'] . "</td>
                                </tr>
                                <tr>
                                    <td>" . i18n("emailAddress") . "</td>";
                                    $text = "<td>" . $details['emailAddress'] . "</td>";
                                    $matches = array();
                                    preg_match_all("/[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})/i", $text, $matches);
                                    $matches = $matches[0];
                                    for ($i = 0; $i < count($matches); $i++) {
                                        $text = str_replace($matches[$i] , "<a href='mailto:" . $matches[$i] . "'>" . $matches[$i] . "</a>" , $text);
                                    }
                                    $text = str_replace("\n", "<br>", $text);
                                    echo $text;
                        echo    "</tr>
                                <tr>
                                    <td>" . i18n("phoneNumber") . "</td>";
                                    $text = "<td>" . $details['phoneNumber'] . "</td>";
                                    $matches = array();
                                    preg_match_all("/\+?[\+]*[\(]*[\+]*[0-9][0-9()\-\s+]{4,20}[0-9]/i", $text, $matches);
                                    $matches = $matches[0];
                                    for ($i = 0; $i < count($matches); $i++) {
                                        $text = str_replace($matches[$i] , "<a href='tel:" . $matches[$i] . "'>" . $matches[$i] . "</a>" , $text);
                                    }
                                    $text = str_replace("\n", "<br>", $text);
                                    echo $text;
                        echo    "</tr>
                                <tr>
                                    <td>" . i18n("website") . "</td>
                                    <td>" . $websiteString . "</td>
                                </tr>
                                <tr>
                                    <td>" . i18n("facebook") . "</td>
                                    <td>" . $facebookString . "</td>
                                </tr>
                                <tr>
                                    <td>" . i18n("notes") . "</td>
                                    <td>" . $details['notes'] . "</td>
                                </tr>
                                <tr>
                                    <td>" . i18n("needPapers") . "</td>
                                    <td>" . $details['needPapers'] . "</td>
                                </tr>
                                <tr>
                                    <td>" . i18n("dateLastUpdated") . "</td>
                                    <td>" . $details['dateLastUpdated'] . "</td>
                                </tr>
                            </table><br>";
                    } else {
                        echo "<table id='table' class='gridtable' style='margin: 5mm; width: calc(100% - 10mm);'>
                                <tr>
                                    <td style=\"width: 50%; color: #000000; background-color: #999999;\">" . i18n("favorite") . "</td>
                                    <td id=\"favoriteStar\" onClick='toggleFavoritesAndUpdateStarImages([" . $id . "]);updateIsFavorite();' style=\"text-align: center; color: #000000; background-color: #999999;\"><img src='img/starActive.png'></td>
                                </tr>
                            </table>
                            
                            <table id='table' class='gridtable' style='margin: 5mm; width: calc(100% - 10mm);'>
                                <tr>
                                    <th style=\"width: 50%\">" . i18n("id") . "</th>
                                    <th>" . $id . "</th>
                                </tr>
                            </table>
                            
                            <table id='table' class='gridtable' style='margin: 5mm; width: calc(100% - 10mm);'>
                                <tr>
                                    <th>" . i18n("name") . "</th>
                                </tr>
                                <tr>
                                    <th>" . $details['name'] . "</th>
                                <tr>
                            </table>
                            
                            <table id='table' class='gridtable' style='margin: 5mm; width: calc(100% - 10mm);'>
                                <tr>
                                    <th>" . i18n("category") . "</th>
                                </tr>
                                <tr>
                                    <td>" . $details['category'] . "</td>
                                <tr>
                            </table>
                            
                            <table id='table' class='gridtable' style='margin: 5mm; width: calc(100% - 10mm);'>
                                <tr>
                                    <th>" . i18n("openingHours") . "</th>
                                </tr>
                                <tr>
                                    <td>" . $details['openingHours'] . "</td>
                                <tr>
                            </table>
                            
                            <table id='table' class='gridtable' style='margin: 5mm; width: calc(100% - 10mm);'>
                                <tr>
                                    <th>" . i18n("address") . "</th>
                                </tr>
                                <tr>
                                    <td><a href='https://www.google.de/maps/place/" . $details['address'] . "'>" . $details['address'] . "</td>
                                <tr>
                            </table>
                            
                            <table id='table' class='gridtable' style='margin: 5mm; width: calc(100% - 10mm);'>
                                <tr>
                                    <th>" . i18n("services") . "</th>
                                </tr>
                                <tr>
                                    <td>" . $details['services'] . "</td>
                                <tr>
                            </table>
                            
                            <table id='table' class='gridtable' style='margin: 5mm; width: calc(100% - 10mm);'>
                                <tr>
                                    <th>" . i18n("description") . "</th>
                                </tr>
                                <tr>
                                    <td>" . $details['description'] . "</td>
                                <tr>
                            </table>
                            
                            <table id='table' class='gridtable' style='margin: 5mm; width: calc(100% - 10mm);'>
                                <tr>
                                    <th>" . i18n("emailAddress") . "</th>
                                </tr>
                                <tr>";
                                    $text = "<td>" . $details['emailAddress'] . "</td>";
                                    $matches = array();
                                    preg_match_all("/[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})/i", $text, $matches);
                                    $matches = $matches[0];
                                    for ($i = 0; $i < count($matches); $i++) {
                                        $text = str_replace($matches[$i] , "<a href='mailto:" . $matches[$i] . "'>" . $matches[$i] . "</a>" , $text);
                                    }
                                    $text = str_replace("\n", "<br>", $text);
                                    echo $text;
                        echo    "</tr>
                            </table>
                            
                            <table id='table' class='gridtable' style='margin: 5mm; width: calc(100% - 10mm);'>
                                <tr>
                                    <th>" . i18n("phoneNumber") . "</th>
                                </tr>
                                <tr>";
                                    $text = "<td>" . $details['phoneNumber'] . "</td>";
                                    $matches = array();
                                    preg_match_all("/\+?[\+]*[\(]*[\+]*[0-9][0-9()\-\s+]{4,20}[0-9]/i", $text, $matches);
                                    $matches = $matches[0];
                                    for ($i = 0; $i < count($matches); $i++) {
                                        $text = str_replace($matches[$i] , "<a href='tel:" . $matches[$i] . "'>" . $matches[$i] . "</a>" , $text);
                                    }
                                    $text = str_replace("\n", "<br>", $text);
                                    echo $text;
                        echo    "</tr>
                            </table>
                            
                            <table id='table' class='gridtable' style='margin: 5mm; width: calc(100% - 10mm);'>
                                <tr>
                                    <th>" . i18n("website") . "</th>
                                </tr>
                                <tr>
                                    <td>" . $websiteString . "</td>
                                <tr>
                            </table>
                            
                            <table id='table' class='gridtable' style='margin: 5mm; width: calc(100% - 10mm);'>
                                <tr>
                                    <th>" . i18n("facebook") . "</th>
                                </tr>
                                <tr>
                                    <td>" . $facebookString . "</td>
                                <tr>
                            </table>
                            
                            <table id='table' class='gridtable' style='margin: 5mm; width: calc(100% - 10mm);'>
                                <tr>
                                    <th>" . i18n("notes") . "</th>
                                </tr>
                                <tr>
                                    <td>" . $details['notes'] . "</td>
                                <tr>
                            </table>
                            
                            <table id='table' class='gridtable' style='margin: 5mm; width: calc(100% - 10mm);'>
                                <tr>
                                    <th>" . i18n("needPapers") . "</th>
                                </tr>
                                <tr>
                                    <td>" . $details['needPapers'] . "</td>
                                <tr>
                            </table>
                            
                            <table id='table' class='gridtable' style='margin: 5mm; width: calc(100% - 10mm);'>
                                <tr>
                                    <th>" . i18n("dateLastUpdated") . "</th>
                                </tr>
                                <tr>
                                    <td>" . $details['dateLastUpdated'] . "</td>
                                <tr>
                            </table>";
                        }
                    } else {
                        echo i18n("detailsError");
                    }
                
                echo "
                    <script>
                        if (isMobileOrTablet()) {
                            document.getElementById(\"content\").style.top = \"70px\";
                        }
                        updateIsFavorite();
                    </script>";
                    
                /*
                 * Gets the content that can be displayed on the details page.
                 */
                function getDetailsContentFromSpreadsheet($id) {
                    // fetch the data from the cached spreadsheet in the given language
                    $data = getFileContentAsCsv($_SESSION["dataCacheFilePath" . getLanguage()]);
                    $dataEnglish = getFileContentAsCsv($_SESSION["dataCacheFilePathEnglish"]);
                    
                    // put together the relevant information
                    $j = 1;
                    $retData = array();
                    $retData['category'] = htmlspecialchars(getOrDefault($data, $dataEnglish, $id, $j++));
                    $retData['name'] = htmlspecialchars(getOrDefault($data, $dataEnglish, $id, $j++));
                    $retData['openingHours'] = htmlspecialchars(getOrDefault($data, $dataEnglish, $id, $j++));
                    $retData['address'] = htmlspecialchars(getOrDefault($data, $dataEnglish, $id, $j++));
                    $retData['services'] = htmlspecialchars(getOrDefault($data, $dataEnglish, $id, $j++));
                    $retData['description'] = htmlspecialchars(getOrDefault($data, $dataEnglish, $id, $j++));
                    $retData['emailAddress'] = htmlspecialchars(getOrDefault($data, $dataEnglish, $id, $j++));
                    $retData['phoneNumber'] = htmlspecialchars(getOrDefault($data, $dataEnglish, $id, $j++));
                    $retData['website'] = htmlspecialchars(getOrDefault($data, $dataEnglish, $id, $j++));
                    $retData['facebook'] = htmlspecialchars(getOrDefault($data, $dataEnglish, $id, $j++));
                    $retData['notes'] = htmlspecialchars(getOrDefault($data, $dataEnglish, $id, $j++));
                    $retData['needPapers'] = htmlspecialchars(getOrDefault($data, $dataEnglish, $id, $j++));
                    // skip over "Internal Contact Data (Contact Name, Telephone Number, ...)"
                    $j++;
                    $retData['dateLastUpdated'] = htmlspecialchars(getOrDefault($data, $dataEnglish, $id, $j++));
                    
                    return $retData;
                }
            ?>
        </div>
    </body>
</html>