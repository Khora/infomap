<?php
    include "lib/lib.php";
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Khora Infomap | INFOMAP</title>
        <?php
            echo getHeadContent();
            echo getPreparationForLeaflet();
        ?>
    </head>
    <body>
        <?php
            echo getSearch();
            echo getTopArea(i18n("infomap"));
        ?>
        <div id="content">
            <?php
                if (!isMobile()) {
                    echo "<table style='margin-bottom: 10px;'>
                            <td style='padding-top: 2px;'>
                                <img src='img/arrowDown.png'>
                            </td>
                            <td style='padding: 0px;'>
                                " . getButton("&nbsp;&nbsp;&nbsp;&nbsp;" . i18n("toggleAll"), "img/starOn.png", "toggleAllCurrentlyVisibleAndUpdateStarImages(false);") . "
                            </td>
                            <td style='padding-left: 20px;'>
                                " . getButton(i18n("favorites"), "img/starInactive.png", "document.location='favorites.php';") . "
                            </td>
                            <td style='padding-left: 20px;'>
                                " . getButton(i18n("clearAll"), "img/clear.png", "clearFavorites();") . "
                            </td>
                            <td style='padding-left: 20px;'>
                                " . getButton(i18n("exportToPdf"), "img/printer.png", "document.location='export.php?ids=' + getCurrentlyVisible(false);") . "
                            </td>
                            <td style='padding-left: 20px; position: absolute; right: 75mm;'>
                                " . getButton(i18n("mapView"), "img/location.png", "document.location='mapview.php';") . "
                            </td>
                            <td style='padding-left: 20px; position: absolute; right: 0;'>
                                " . getButton(i18n("toggleMapView"), "img/locationToggle.png", "toggleMapView();") . "
                            </td>
                        </table>";
                } else {
                    echo "<table style='width: 100%; margin-bottom: 10px;'>
                            <tr>
                                <td style='padding-left: 1mm;'>
                                    " . getButton(i18n("favorites"), "img/starInactive.png", "document.location='favorites.php';") . "
                                </td>
                                <td style='padding-right: 1mm'>
                                    " . getButton(i18n("exportToPdf"), "img/printer.png", "document.location='export.php?ids=' + getCurrentlyVisible(true);") . "
                                </td>
                            </tr>
                        </table>
                        <table style='width: 100%; margin-bottom: 10px;'>
                            <tr>
                                <td style='padding-left: calc(50% - 20mm); text-align: center;'>
                                    " . getButton(i18n("mapView"), "img/location.png", "document.location='mapview.php';") . "
                                </td>
                            </tr>
                        </table>
                        <table style='width: 100%; margin-bottom: 10px; padding: 1mm;'>
                            <tr>
                                <td style='text-align: right;  padding: 0px;'>
                                    " . getButton("&nbsp;&nbsp;&nbsp;&nbsp;" . i18n("toggleAll"), "img/starOn.png", "toggleAllCurrentlyVisibleAndUpdateStarImages(true);") . "
                                </td>
                                <td style='text-align: right;'>
                                    <img src='img/arrowDown.png'>
                                </td>
                            </tr>
                        </table>";
                }
            
                if (!isMobile()) {
                    echo "<table style='width: 100%;'>
                            <tr>
                                <td style='width: 50%; vertical-align: top;'><div id='tableDiv' style='overflow-y: scroll;'>" . getTableWithContentFromSpreadsheet() . "</div></td>
                                <td style='width: 50%; vertical-align: top;'>" . getLeafletMap() . "</td>
                            </tr>";
                } else {
                    echo "<table style='width: 100%;'>
                            <tr>
                                <td>" . getMobileTableWithContentFromSpreadsheet() . "</td>
                                <td>" . getLeafletMap() . "</td>
                            </tr>";
                }
                
                echo "<script>
                            var height = window.innerHeight
                                        || document.documentElement.clientHeight
                                        || document.body.clientHeight;
                            document.getElementById('tableDiv').style.height = height - 395 + 'px';
                            document.getElementById('map').style.height = height - 395 + 'px';
                            document.getElementById('tableDiv').style.display = 'block';
                            document.getElementById('map').style.display = 'block';
                        </script>";
                
                echo "<script>
                            var geoPositionsOfAddresses = " . getFileContent($_SESSION["dataCacheGeocodedPositionOfAddresses"]) . "
                                console.log(geoPositionsOfAddresses);
                            for (i = 1; i < Object.keys(geoPositionsOfAddresses).length; i++) {
                                addMarkerToLeafletMap(geoPositionsOfAddresses[i][0], geoPositionsOfAddresses[i][1], i.toString(), i.toString() + ' TEXT', 'red');
                                console.log(geoPositionsOfAddresses[i][0]);
                            }
                        </script>";
                
                echo "<script>
                            function toggleMapView() {
                                var width = window.innerWidth
                                            || document.documentElement.clientWidth
                                            || document.body.clientWidth;
                                
                                var hideMapNow = (document.getElementById('map').style.display == 'block');
                                if (hideMapNow) {
                                    document.getElementById('map').style.display = 'none';
                                    document.getElementById('tableDiv').style.width = width - 15 + 'px';
                                } else {
                                    document.getElementById('map').style.display = 'block';
                                    document.getElementById('tableDiv').style.width = width / 2 - 10 + 'px';
                                }
                            }
                        </script>";
                
                /*
                 * Gets a searchable table with the content from the cached Google Spreadsheet.
                 */
                function getTableWithContentFromSpreadsheet() {
                    // how many columns do we want to present in the list?
                    $previewCount = 6;
                    
                    // fetch the data from the cached spreadsheet in the given language
                    $data = getFileContentAsCsv($_SESSION["dataCacheFilePath" . getLanguage()]);
                    $dataEnglish = getFileContentAsCsv($_SESSION["dataCacheFilePathEnglish"]);
                    
                    // construct an HTML table with the given information
                    $retVal = "<table id='table' class='gridtable' style='width: 100%;'> <tr>";
                    $retVal = $retVal . "<th style='color: #ffffff; background-color: #555555;'>ID</th>";
                    for ($i = 1; $i < $previewCount - 1; $i++) {
                        $retVal = $retVal . "<th>" . htmlspecialchars($data[0][$i]) . "</th>";
                    }
                    $retVal = $retVal . "</tr>";
                    for ($i = 1; $i < count($dataEnglish); $i++) {
                        $backgroundColor = "FDFDFD";
                        if ($i % 2 == 0) {
                            $backgroundColor = "#F1F1F1";
                        }
                        $retVal = $retVal . "<tr>";
                        for ($j = 0; $j < $previewCount; $j++) {
                            if ($j !== 5) {
                                if ($j == 1) {
                                    $retVal = $retVal . "<td onClick='document.location.href=\"details.php?language=" . getLanguage() . "&id=" . $i . "\"' style='border: 0px; cursor: pointer; background-color: " . $backgroundColor . ";'><i>" . htmlspecialchars(getOrDefault($data, $dataEnglish, $i, $j)) . "</i></td>";
                                } else if ($j == 2) {
                                    $retVal = $retVal . "<td onClick='document.location.href=\"details.php?language=" . getLanguage() . "&id=" . $i . "\"' style='border: 0px; cursor: pointer; background-color: " . $backgroundColor . ";'><b>" . htmlspecialchars(getOrDefault($data, $dataEnglish, $i, $j)) . "</b></td>";
                                } else if ($j == 4) {
                                    $retVal = $retVal . "<td onClick='showInMap(\"" . $i . "\")' rowspan='2' style='border: 0px; cursor: pointer; background-color: " . $backgroundColor . ";'>" . htmlspecialchars(getOrDefault($data, $dataEnglish, $i, $j)) . "&nbsp&#187;</td>";
                                } else {
                                    $retVal = $retVal . "<td onClick='document.location.href=\"details.php?language=" . getLanguage() . "&id=" . $i . "\"' style='border: 0px; cursor: pointer; background-color: " . $backgroundColor . ";'>" . htmlspecialchars(getOrDefault($data, $dataEnglish, $i, $j)) . "</td>";
                                }
                            }
                        }
                        $retVal = $retVal . "</tr>\n";
                        
                        $retVal = $retVal . "<tr>";
                        $retVal = $retVal . "<td id='s_" . $i . "' onClick='toggleFavoritesAndUpdateStarImages([" . $i . "])' style=' background-color: " . $backgroundColor . "; cursor: pointer; border: 0px;'><img src='img/starInactive.png'></td>";
                        $retVal = $retVal . "<td onClick='document.location.href=\"details.php?language=" . getLanguage() . "&id=" . $i . "\"' style=' background-color: " . $backgroundColor . "; cursor: pointer; border: 0px;' colspan='" . ($previewCount - 3) . "'>" . htmlspecialchars(getOrDefault($data, $dataEnglish, $i, 5)) . "</td>";
                        $retVal = $retVal . "</tr>\n";
                        $retVal = $retVal . "<script>starImageElements.push(" . $i . ");</script>";
                    }
                    
                    $retVal = $retVal . "</table>";
                    $retVal = $retVal . "<script>updateAllStarImages();</script>";
                    return $retVal;
                }
                
                /*
                 * Gets a searchable table for mobile display with the content from the cached Google Spreadsheet.
                 */
                function getMobileTableWithContentFromSpreadsheet() {
                    // how many columns do we want to present in the list?
                    $previewCount = 6;
                    
                    // fetch the data from the cached spreadsheet in the given language
                    $data = getFileContentAsCsv($_SESSION["dataCacheFilePath" . getLanguage()]);
                    $dataEnglish = getFileContentAsCsv($_SESSION["dataCacheFilePathEnglish"]);
                    
                    // construct an HTML table with the given information
                    $retVal = "<table id='table' class='gridtable' style='margin: 2mm; width: calc(100% - 4mm);'>";
                    for ($i = 1; $i < count($dataEnglish); $i++) {
                        $index = htmlspecialchars(getOrDefault($data, $dataEnglish, $i, 0));
                        $category = htmlspecialchars(getOrDefault($data, $dataEnglish, $i, 1));
                        $name = htmlspecialchars(getOrDefault($data, $dataEnglish, $i, 2));
                        $openingHours = htmlspecialchars(getOrDefault($data, $dataEnglish, $i, 3));
                        $address = htmlspecialchars(getOrDefault($data, $dataEnglish, $i, 4));
                        $servicesDescription = htmlspecialchars(getOrDefault($data, $dataEnglish, $i, 5));
                        
                        $retVal = $retVal . "<tr>
                                <td onClick='document.location.href=\"details.php?id=" . $i . "\"' style='font-weight: bold; text-align: center; color: #ffffff; background-color: #555555; width: 10%;'>" . $index . "</td>
                                <td onClick='document.location.href=\"details.php?id=" . $i . "\"' style='font-weight: bold; text-align: center; color: #ffffff; background-color: #555555; width: 80%;'>" . $name . "</td>
                                <td id='s_" . $i . "' onClick='toggleFavoritesAndUpdateStarImages([" . $i . "])' style='font-weight: bold; text-align: center; color: #ffffff; background-color: #555555; width: 10%;'><img src='img/starActive.png'></td>
                            </tr>
                            <tr>
                                <td onClick='document.location.href=\"details.php?id=" . $i . "\"' colspan='3' style='border: 0px; background-color: #f5f5f5; width: 100%; font-weight: bold;'>" . $category . "</td>
                            </tr>
                            <tr>
                                <td onClick='document.location.href=\"details.php?id=" . $i . "\"' colspan='3' style='border: 0px; background-color: #f5f5f5; width: 100%; color: #888888;'>" . $openingHours . "</td>
                            </tr>
                            <tr>
                                <td onClick='document.location.href=\"details.php?id=" . $i . "\"' colspan='3' style='border: 0px; background-color: #f5f5f5; width: 100%; color: #888888;'>" . $address . "</td>
                            </tr>
                            <tr>
                                <td onClick='document.location.href=\"details.php?id=" . $i . "\"' colspan='3' style='border: 0px; background-color: #f5f5f5; width: 100%;'>" . $servicesDescription . "</td>
                            </tr>
                            <tr>
                                <td onClick='document.location.href=\"details.php?id=" . $i . "\"' colspan='3' style='border: 0px; background-color: #ffffff; width: 100%; height: 1mm;'></td>
                            </tr>";
                            $retVal = $retVal . "<script>starImageElements.push(" . $i . ");</script>";
                    }
                    
                    $retVal = $retVal . "</table>";
                    $retVal = $retVal . "<script>updateAllStarImages();</script>";
                    return $retVal;
                }
                
                /*
                 * Gets a leaflet map with the possibility to place markers on positions.
                 */
                function getLeafletMap() {
                    return '<div id="map" style="width: 100%; height: 380px;">
                                <script>
                                    var map = L.map(\'map\').setView([37.97688, 23.71871], 13);
                                    var markers = new Map();


                                    L.tileLayer(\'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png\', {
                                        attribution: \'&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors\'
                                    }).addTo(map);
                                    
                                    function addMarkerToLeafletMap(lat, long, id, text, color) {
                                        var m = L.marker([lat, long]);
                                        m.addTo(map).bindPopup(text).openPopup();
                                        markers.set(id, m);
                                    }
                                    
                                    function removeMarkerFromLeafletMap(id) {
                                        var m = markers.get(id);
                                        if (m !== null && m !== undefined) {
                                            map.removeLayer(m);
                                        }
                                    }
                                    
                                    function showInMap(id) {
                                        removeMarkerFromLeafletMap(id);
                                    }
                                </script>
                            </div>';
                }
            ?>
        </div>
    </body>
</html>