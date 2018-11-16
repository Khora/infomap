<?php
    include "lib/lib.php";
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Khora Infomap | EXPORT</title>
        <?php
            echo getHeadContent();
            echo getPreparationForLeaflet();
        ?>
    </head>
    <body>
        <?php
            for ($i = 0; $i < 10; $i++) {
                $id = 'content' . ($i - 1);
                $height = 790;
                if ($i == 0) {
                    $id = 'mapContentFirstPage';
                    $height = 770;
                }
                echo '<div id=' . $id . ' style="margin: 0px; padding: 0px; top: ' . $i * $height . 'px; left: 0px; width: 100%; height: ' . $height . 'px; border: 1px solid black; background-color: #' . (10 - $i) . 'A' . (10 - $i) . 'A' . (10 - $i) . 'A' . ';"></div>';
            }
        ?>
        <div id="content" style="top: 0px;">
            <?php
                $idsToExport = array();
                if (isset($_GET["ids"]) && strcmp($_GET["ids"], "") != 0) {
                    $idsString = htmlspecialchars($_GET["ids"]);
                    if (isset($idsString) && strcmp($idsString, "") != 0) {
                        $idsToExport = explode(',', $idsString);
                    }
                }
                
                $language = getLanguage();
                $flagImage = "britainFlag.png";
                if ($language == "Greek") {
                    $flagImage = "greeceFlag.png";
                } else if ($language == "French") {
                    $flagImage = "franceFlag.png";
                } else if ($language == "Farsi") {
                    $flagImage = "afghanistanFlag.png";
                } else if ($language == "Arabic") {
                    $flagImage = "syriaFlag.png";
                } else if ($language == "Urdu") {
                    $flagImage = "pakistanFlag.png";
                } else if ($language == "Kurdish") {
                    $flagImage = "kurdistanFlag.png";
                }
                
                echo '<center style="font-size: 25px;"><br></center><center style="font-size: 50px;"><img src="img/khoraLogo.png" style="height: 40px;">&nbsp;&nbsp;&nbsp;Khora Infomap&nbsp;-&nbsp;' . $language . '&nbsp;&nbsp;&nbsp;<img src="img/' . $flagImage . '" style="height: 40px;"><center><center style="font-size: 25px;"><br></center>';
                echo getLeafletMap() . '<center style="font-size: 25px;"><br><br></center>';
                echo getTableWithContentFromSpreadsheet($idsToExport) . "<br><br><br>";
                
                echo "<script>
                            document.getElementById('map').style.display = 'block';
                            rescaleHeight();
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
                            }
                        </script>";
                
                /*
                 * Gets a searchable table with the content from the cached Google Spreadsheet.
                 */
                function getTableWithContentFromSpreadsheet($idsToExport) {
                    // fetch the data from the cached spreadsheet in the given language
                    $data = getFileContentAsCsv($_SESSION["dataCacheFilePath" . getLanguage()]);
                    $dataEnglish = getFileContentAsCsv($_SESSION["dataCacheFilePathEnglish"]);
                    
                    if (count($data) == 0 || count($dataEnglish) == 0) {
                        return "";
                    }
                    
                    $header = array();
                    for ($i = 0; $i < count($dataEnglish[0]); $i++) {
                        array_push($header, getOrDefault($data, $dataEnglish, 0, $i));
                    }
                    
                    $itemsPerPage = 10;
                    $retStr = "";
                    for ($i = 1; $i < count($dataEnglish); $i = $i + $itemsPerPage) {
                        $dataToUse = array($header);
                        $dataEnglishToUse = array($header);
                        for ($j = $i; $j < $i + $itemsPerPage && $j < count($dataEnglish); $j++) {
                            if (count($idsToExport) == 0 || in_array(strval($i), $idsToExport)) {
                                array_push($dataToUse, $data[$j]);
                                array_push($dataEnglishToUse, $dataEnglish[$j]);
                            }
                        }
                        $retStr = $retStr . getTableOnePage($dataToUse, $dataEnglishToUse);
                    }
                        
                    return $retStr;
                }
                
                /*
                 * Gets a searchable table with the content from the cached Google Spreadsheet.
                 */
                function getTableOnePage($data, $dataEnglish) {
                    // how many columns do we want to present in the list?
                    $previewCount = 6;
                    
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
                            $backgroundColor = "#D0D0D0";
                        }
                        $retVal = $retVal . "<tr>";
                        for ($j = 0; $j < $previewCount; $j++) {
                            if ($j !== 5) {
                                if ($j == 1) {
                                    $retVal = $retVal . "<td onClick='document.location.href=\"details.php?language=" . getLanguage() . "&id=" . $i . "\"' style='border: 0px; cursor: pointer; background-color: " . $backgroundColor . ";'><i>" . htmlspecialchars(getOrDefault($data, $dataEnglish, $i, $j)) . "</i></td>";
                                } else if ($j == 2) {
                                    $retVal = $retVal . "<td onClick='document.location.href=\"details.php?language=" . getLanguage() . "&id=" . $i . "\"' style='border: 0px; cursor: pointer; background-color: " . $backgroundColor . ";'><b>" . htmlspecialchars(getOrDefault($data, $dataEnglish, $i, $j)) . "</b></td>";
                                } else if ($j == 4) {
                                    $retVal = $retVal . "<td onClick='showInMapAndRemoveOthers(\"" . $i . "\")' rowspan='2' style='border: 0px; cursor: pointer; background-color: " . $backgroundColor . ";'>" . htmlspecialchars(getOrDefault($data, $dataEnglish, $i, $j)) . "</td>";
                                } else {
                                    $retVal = $retVal . "<td onClick='document.location.href=\"details.php?language=" . getLanguage() . "&id=" . $i . "\"' style='border: 0px; cursor: pointer; background-color: " . $backgroundColor . ";'>" . htmlspecialchars(getOrDefault($data, $dataEnglish, $i, $j)) . "</td>";
                                }
                            }
                        }
                        $retVal = $retVal . "</tr>\n";
                        
                        $retVal = $retVal . "<tr>";
                        $retVal = $retVal . "<td style=' background-color: " . $backgroundColor . "; cursor: pointer; border: 0px;'></td>";
                        $retVal = $retVal . "<td onClick='document.location.href=\"details.php?language=" . getLanguage() . "&id=" . $i . "\"' style=' background-color: " . $backgroundColor . "; cursor: pointer; border: 0px;' colspan='" . ($previewCount - 3) . "'>" . htmlspecialchars(getOrDefault($data, $dataEnglish, $i, 5)) . "</td>";
                        $retVal = $retVal . "</tr>\n";
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
                                    var map = L.map(\'map\', { dragging: !L.Browser.mobile, zoomControl: false }).setView([37.97688, 23.71871], 13);
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
                                                    document.getElementById("map").style.height = height - 355 + "px";
                                
                                        if (map !== null && map !== undefined) {
                                            map.invalidateSize();
                                        }
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