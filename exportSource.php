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
        <div id="content" style="top: 0px;">
            <?php
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
                
                $addressesData = array();
                $namesData = array();
                // fetch the data from the cached spreadsheet in the given language
                $data = getFileContentAsCsv($_SESSION["dataCacheFilePath" . getLanguage()]);
                $dataEnglish = getFileContentAsCsv($_SESSION["dataCacheFilePathEnglish"]);
                for ($i = 1; $i < count($dataEnglish); $i++) {
                    array_push($namesData, $dataEnglish[$i][2]);
                    array_push($addressesData, $dataEnglish[$i][4]);
                }
                
                echo '<center style="font-size: 25px;"><br></center><center style="font-size: 50px;"><img src="img/khoraLogo.png" style="height: 40px;">&nbsp;&nbsp;&nbsp;Khora Infomap&nbsp;-&nbsp;' . $language . '&nbsp;&nbsp;&nbsp;<img src="img/' . $flagImage . '" style="height: 40px;"></center><center style="font-size: 25px;"><br></center>';
                echo getLeafletMap() . '<center style="font-size: 25px;"><br><br></center>';
                
                // determine which ids to export from get params
                $idsToExport = array();
                if (isset($_GET["ids"]) && strcmp($_GET["ids"], "") != 0) {
                    $idsString = htmlspecialchars($_GET["ids"]);
                    if (isset($idsString) && strcmp($idsString, "") != 0) {
                        $idsToExport = explode(',', $idsString);
                    }
                }
                
                if (count($data) == 0 || count($dataEnglish) == 0) {
                    error("NO DATA FETCHED");
                }
                
                $sizeOfPageInPx = 793;
                $currentPaddingTop = $sizeOfPageInPx;
                for ($i = 0; $i < count($idsToExport) / 9; $i++) {
                    for ($j = 0; $j < 3; $j++) {
                        if ((9 * $i + 3 * $j) < count($idsToExport)) {
                            echo '<div id="content' . $i . $j . '" style="position: absolute; margin: 0px; padding: 0px; top: ' . $currentPaddingTop . 'px; left: ' . ($j * 33) . '%; width: 33%; height: 792px;">';
                            for ($k = 0; $k < 3; $k++) {
                                $currentPointer = (9 * $i + 3 * $j + $k);
                                if ($currentPointer < count($idsToExport)) {
                                    echo '<div style="margin: 20px; padding: 20px;">
                                            <b>' . $idsToExport[$currentPointer] . '. ' . htmlspecialchars(getOrDefault($data, $dataEnglish, $idsToExport[$currentPointer], 2)) . '</b><br>';
                                    if (htmlspecialchars(getOrDefault($data, $dataEnglish, $idsToExport[$currentPointer], 4)) != '') {
                                        echo '<b>' . htmlspecialchars(getOrDefault($data, $dataEnglish, $idsToExport[$currentPointer], 4)) . '</b><br>';
                                    }
                                    if (htmlspecialchars(getOrDefault($data, $dataEnglish, $idsToExport[$currentPointer], 5)) != '') {
                                        echo '<img src="img/info.png" style="height: 11px;"> ' . htmlspecialchars(getOrDefault($data, $dataEnglish, $idsToExport[$currentPointer], 5)) . '<br>';
                                    }
                                    if (htmlspecialchars(getOrDefault($data, $dataEnglish, $idsToExport[$currentPointer], 3)) != '') {
                                        echo '<img src="img/clock.png" style="height: 11px;"> ' . htmlspecialchars(getOrDefault($data, $dataEnglish, $idsToExport[$currentPointer], 3)) . '<br>';
                                    }
                                    if (htmlspecialchars(getOrDefault($data, $dataEnglish, $idsToExport[$currentPointer], 7)) != '') {
                                        echo '<img src="img/envelope.png" style="height: 11px;"> ' . htmlspecialchars(getOrDefault($data, $dataEnglish, $idsToExport[$currentPointer], 7)) . '<br>';
                                    }
                                    if (htmlspecialchars(getOrDefault($data, $dataEnglish, $idsToExport[$currentPointer], 8)) != '') {
                                        echo '<img src="img/phone.png" style="height: 11px;"> ' . htmlspecialchars(getOrDefault($data, $dataEnglish, $idsToExport[$currentPointer], 8)) . '<br>';
                                    }
                                    echo '</div>';
                                }
                            }
                            echo '</div>';
                        }
                    }
                    $currentPaddingTop += $sizeOfPageInPx;
                }
                
                echo '<script>';
                echo 'var geoPositionsOfAddresses = ' . getFileContent($_SESSION["dataCacheGeocodedPositionOfAddresses"]) . ';';
                for ($i = 0; $i < count($idsToExport); $i++) {
                    echo 'showInMap(' . $idsToExport[$i] . ');';
                }
                echo '</script>';
                
                /*
                 * Gets a leaflet map with the possibility to place markers on positions.
                 */
                function getLeafletMap() {
                    return '<div id="map" style="width: 100%; height: 600px;">
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
                                        
                                        var athensNorthWestCorner = [38.020431, 23.677112];
                                        var athensSouthEastCorner = [37.958107, 23.785241];
                                        var bounds = new L.LatLngBounds([athensNorthWestCorner, athensSouthEastCorner]);
                                        map.fitBounds(bounds, { padding: [20, 20] });
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
                                            addMarkerToLeafletMap(geoPositionsOfAddresses[id][0], geoPositionsOfAddresses[id][1], id.toString(), "", "red");
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