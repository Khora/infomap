<?php
    include "lib/lib.php";
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Khora Infomap | FAVORITES</title>
        <?php
            echo getHeadContent();
        ?>
    </head>
    <body>
        <?php
            echo getTopArea(i18n("favorites"));
            
            echo getSearch();
        ?>
        <div id="content">
            <?php
                if (!isMobile()) {
                    echo "<table style='margin-bottom: 10px;'>
                            <td style='padding: 0px;'>
                                " . getButton(i18n("back"), "img/backArrow.png", "document.location='infomap.php';") . "
                            </td>
                            <td style='padding-left: 20px;'>
                                " . getButton(i18n("exportToPdf"), "img/printer.png", "document.location='export.php?ids=' + getFavorites();") . "
                            </td>
                            <td style='padding-left: 20px;'>
                                " . getButton(i18n("clearAll"), "img/clear.png", "clearFavorites();") . "
                            </td>
                            <td style='padding-left: 20px; position: absolute; right: 0;'>
                                " . getButton(i18n("mapView"), "img/location.png", "document.location='mapview.php';") . "
                            </td>
                        </table>";
                    echo getTableWithContentFromSpreadsheet();
                } else {
                    echo "<table style='width: 100%; margin-bottom: 10px;'>
                            <tr>
                                <td style='padding-left: 1mm;'>
                                    " . getButton(i18n("back"), "img/backArrow.png", "document.location='infomap.php';") . "
                                </td>
                                <td style='padding-right: 1mm'>
                                    " . getButton(i18n("exportToPdf"), "img/printer.png", "document.location='export.php?ids=' + getFavorites();") . "
                                </td>
                            </tr>
                        </table>
                        <table style='width: 100%; margin-bottom: 10px;'>
                            <tr>
                                <td style='padding-left: 1mm;'>
                                    " . getButton(i18n("clearAll"), "img/clear.png", "clearFavorites();") . "
                                </td>
                                <td style='padding-right: 1mm'>
                                    " . getButton(i18n("mapView"), "img/location.png", "document.location='mapview.php';") . "
                                </td>
                            </tr>
                        </table>";
                    echo getMobileTableWithContentFromSpreadsheet();
                }
                
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
                    $retVal = "<table id='table' class='gridtable' width='100%'> <tr>";
                    $retVal = $retVal . "<th style='color: #ffffff; background-color: #555555;'>ID</th>";
                    for ($i = 1; $i < $previewCount - 1; $i++) {
                        $retVal = $retVal . "<th>" . htmlspecialchars($dataEnglish[0][$i]) . "</th>";
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
                                    $retVal = $retVal . "<td onClick='document.location.href=\"details.php?language=" . getLanguage() . "&id=" . $i . "\"' style='border-bottom: 0px; cursor: pointer; background-color: " . $backgroundColor . ";'><i>" . htmlspecialchars(getOrDefault($data, $dataEnglish, $i, $j)) . "</i></td>";
                                } else if ($j == 2) {
                                    $retVal = $retVal . "<td onClick='document.location.href=\"details.php?language=" . getLanguage() . "&id=" . $i . "\"' style='border-bottom: 0px; cursor: pointer; background-color: " . $backgroundColor . ";'><b>" . htmlspecialchars(getOrDefault($data, $dataEnglish, $i, $j)) . "</b></td>";
                                } else {
                                    $retVal = $retVal . "<td onClick='document.location.href=\"details.php?language=" . getLanguage() . "&id=" . $i . "\"' style='border-bottom: 0px; cursor: pointer; background-color: " . $backgroundColor . ";'>" . htmlspecialchars(getOrDefault($data, $dataEnglish, $i, $j)) . "</td>";
                                }
                            }
                        }
                        $retVal = $retVal . "</tr>\n";
                        
                        $retVal = $retVal . "<tr>";
                        $retVal = $retVal . "<td id='s_" . $i . "' onClick='toggleFavoritesAndUpdateStarImages([" . $i . "])' style=' background-color: " . $backgroundColor . ";cursor: pointer; border-right: 0px; border-top: 0px;'><img src='img/starInactive.png'></td>";
                        $retVal = $retVal . "<td onClick='document.location.href=\"details.php?language=" . getLanguage() . "&id=" . $i . "\"' style=' background-color: " . $backgroundColor . ";cursor: pointer; border-left: 0px; border-top: 0px;' colspan='" . ($previewCount - 1) . "'>" . htmlspecialchars(getOrDefault($data, $dataEnglish, $i, 5)) . "</td>";
                        $retVal = $retVal . "</tr>\n";
                        $retVal = $retVal . "<script>starImageElements.push(" . $i . ");</script>";
                    }
                    
                    $retVal = $retVal . "</table>";
                    $retVal = $retVal . "<script>updateAllStarImages();</script>";
                    $retVal = $retVal . "<script>hideAllInTableThatAreNotFavorites(false);</script>";
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
                    $retVal = $retVal . "<script>hideAllInTableThatAreNotFavorites(true);</script>";
                    return $retVal;
                }
            ?>
        </div>
    </body>
</html>