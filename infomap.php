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
            echo getSearch(false);
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
                    echo getLeafletMap() . "<br><br><br>" . getMobileTableWithContentFromSpreadsheet();
                }
                
                echo "<script>
                            rescaleHeight();
                            document.getElementById('tableDiv').style.display = 'block';
                            document.getElementById('map').style.display = 'block';
                        </script>";
                
                $addressesData = array();
                $namesData = array();
                $dataEnglish = getFileContentAsCsv($_SESSION["dataCacheFilePathEnglish"]);
                for ($i = 1; $i < count($dataEnglish); $i++) {
                    array_push($namesData, $dataEnglish[$i][2]);
                    array_push($addressesData, $dataEnglish[$i][4]);
                }
                
                echo "<script>
                            var geoPositionsOfAddresses = " . getFileContent($_SESSION["dataCacheGeocodedPositionOfAddresses"]) . "
                            var namesData = " . json_encode($namesData) . "
                            var addressesData = " . json_encode($addressesData) . "
                            for (i = 0; i < Object.keys(geoPositionsOfAddresses).length; i++) {
                                addMarkerToLeafletMap(geoPositionsOfAddresses[i + 1][0], geoPositionsOfAddresses[i + 1][1], (i + 1).toString(), namesData[i], 'red');
                            }";
                
                echo "function toggleMapView() {
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
                                
                                if (map !== null && map !== undefined) {
                                    map.invalidateSize();
                                }
                            }
                                
                            if (map !== null && map !== undefined) {
                                map.invalidateSize();
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
                        $backgroundColor = "#FFFFFF";
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
                                    $retVal = $retVal . "<td onClick='showInMapAndRemoveOthers(\"" . $i . "\")' rowspan='2' style='border: 0px; cursor: pointer; background-color: " . $backgroundColor . ";'>" . htmlspecialchars(getOrDefault($data, $dataEnglish, $i, $j)) . "&nbsp&#187;</td>";
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
                                    var map = L.map(\'map\').setView([37.97688, 3.71871], 13);
                                    var markers = new L.FeatureGroup();
                                    map.addLayer(markers);

                                    var markersMap = new Map();
                                    L.tileLayer(\'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png\', {
                                        attribution: \'&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors\'
                                    }).addTo(map);
                                    
                                    function addMarkerToLeafletMap(lat, long, id, text, color) {
                                        var numMarker = L.ExtraMarkers.icon({
                                            icon: \'fa-number\',
                                            number: id,
                                            markerColor: \'blue\'
                                            });
                                        var m = L.marker([lat, long], {icon: numMarker});
                                        markers.addLayer(m);
                                        
                                        m.bindPopup(id + " - " + text);
                                        m.on("mouseover", function (e) {
                                            this.openPopup();
                                        });
                                        m.on("mouseout", function (e) {
                                            this.closePopup();
                                        });
                                        m.on("click", function (e) {
                                            document.getElementById("infomapSearch").value = text;
                                            searchTable(false, false);
                                        });
                                        m.openPopup();
                                        
                                        markersMap.set(id, m);
                                        
                                        var group = new L.featureGroup([ ...markersMap.values() ]);
                                        map.fitBounds(group.getBounds());
                                    }
                                    
                                    function removeMarkerFromLeafletMap(id) {
                                        var m = markersMap.get(id);
                                        if (m !== null && m !== undefined) {
                                            markers.removeLayer(m);
                                            markersMap.delete(id);
                                        }
                                    }
                                    
                                    function removeAllMarkerFromLeafletMap() {
                                        markers.clearLayers();
                                        markersMap = new Map();
                                    }
                                    
                                    function showInMapAndRemoveOthers(id) {
                                        removeAllMarkerFromLeafletMap();
                                        showInMap(id);
                                    }
                                    
                                    function showInMap(id) {
                                        if (id !== null && id !== undefined && geoPositionsOfAddresses[id] !== null && geoPositionsOfAddresses[id] !== undefined && geoPositionsOfAddresses[id][0] !== null && geoPositionsOfAddresses[id][0] !== undefined && geoPositionsOfAddresses[id][1] !== null && geoPositionsOfAddresses[id][1] !== undefined) {
                                            addMarkerToLeafletMap(geoPositionsOfAddresses[id][0], geoPositionsOfAddresses[id][1], id.toString(), namesData[id - 1], "red");
                                        }
                                    }
                                    
                                    function rescaleHeight() {
                                        var height = window.innerHeight
                                                                || document.documentElement.clientHeight
                                                                || document.body.clientHeight;
                                                    document.getElementById("tableDiv").style.height = height - 395 + "px";
                                                    document.getElementById("map").style.height = height - 395 + "px";
                                    }
                                    
                                    window.onresize = function(event) {
                                        rescaleHeight();
                                    };
                                </script>
                            </div>';
                }
            ?>
        </div>
    </body>
</html>