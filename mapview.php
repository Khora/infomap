<?php
    include "lib/lib.php";
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Khora Infomap | MAP VIEW</title>
        <?php
            echo getHeadContent();
        ?>
    </head>
    <body>
        <?php
            echo getTopArea(i18n("MAP VIEW"));
            
            echo getMapSearch();
        ?>
            
        <div id="content">
            <?php
                $address = "Eptachalkou 25, Athina";
                
                if (isset($_GET["address"])) {
                    $address = htmlspecialchars($_GET["address"]);
                    echo "<script>document.getElementById('infomapSearch').value='" . $address . "';</script>";
                }
                
                $latLon = mapquestGeocodeApiAddressToLocation($address);
                
                if (!isMobile()) {
                    echo "<center><table style='margin-bottom: 10px;'>
                            <td>
                                " . getButton(i18n("back"), "img/backArrow.png", "document.location='infomap.php';") . "
                            </td>
                            <td style='padding-left: 220px;'>
                                <img src='img/location.png'>
                            </td>
                            <td style='padding-left: 0px;'>
                                " . $address . "
                            </td>
                            <td style='padding-left: 220px;'>
                                <img src='img/marker.png'>
                            </td>
                            <td style='padding-left: 0px;'>
                                " . $latLon[0] . ", " . $latLon[1] . "
                            </td>
                        </table><br>";
                    echo "<img src='" . downloadStaticMapWithMarkers($latLon, 14, "500x300", array($latLon)) . "'></center>";
                } else {
                    echo "<center>
                                " . getButton(i18n("back"), "img/backArrow.png", "document.location='infomap.php';") . "<br>
                            <img src='img/location.png'> " . $address . "<br><br>
                            <img src='img/marker.png'> " . $latLon[0] . ", " . $latLon[1] . "<br><br>";
                    echo "<img src='" . downloadStaticMapWithMarkers($latLon, 14, "200x200", array($latLon)) . "'></center>";
                }
                
                echo "<br><br>";
            ?>
        </div>
        
        <script>
            function searchMap() {
                if (event.key === "Enter") {
                    startAddressSearch(document.getElementById("infomapSearch").value);
                }
            }
            
            function searchMapDirectly() {
                startAddressSearch(document.getElementById("infomapSearch").value);
            }
        
            function startAddressSearch(addressString) {
                window.location.href = window.location.protocol + "//" + window.location.hostname + window.location.pathname + "?address=" + encodeURIComponent(addressString);
            }
        </script>
    </body>
</html>