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
                        <td style='padding: 10px; padding-left: 5mm;'>
                            " . getButton(i18n("back"), "img/backArrow.png", "document.location='infomap.php';") . "
                        </td>
                        <td id=\"favoriteStar\" style='padding: 10px; padding-left: 25mm; font-size: 8mm; font-weight: bold;'>
                            " . i18n("favorite") . ":&nbsp;<img src='img/starActive.png'>
                        </td>
                    </table>
                    <script>
                        if (getFavorites().indexOf(" . $id . ") < 0) {
                            document.getElementById(\"favoriteStar\").innerHTML = \"" . i18n("favorite") . ":&nbsp;<img src='img/starInactive.png'>\";
                        }
                    </script>";
                
                if ($id != -1) {
                    $details = getDetailsContentFromSpreadsheet($id);
                    $isFavorite = true;
                    $starSrc = "img/starInactive.png";
                    if ($isFavorite) {
                        $starSrc = "img/starActive.png";
                    }
                    
                    echo "<table id='table' class='gridtable' style='margin: 5mm; width: calc(100% - 10mm);'>
                        <tr>
                            <th>" . i18n("id") . "</th>
                            <th>" . $id . "</th>
                        <tr>
                        <tr>
                            <td>" . i18n("category") . "</td>
                            <td>" . $details['category'] . "</td>
                        <tr>
                        <tr>
                            <td>" . i18n("name") . "</td>
                            <td>" . $details['name'] . "</td>
                        <tr>
                        <tr>
                            <td>" . i18n("openingHours") . "</td>
                            <td>" . $details['openingHours'] . "</td>
                        <tr>
                        <tr>
                            <td>" . i18n("address") . "</td>
                            <td><a href='https://www.google.de/maps/place/" . $details['address'] . "'>" . $details['address'] . "</td>
                        <tr>
                        <tr>
                            <td>" . i18n("services") . "</td>
                            <td>" . $details['services'] . "</td>
                        <tr>
                        <tr>
                            <td>" . i18n("description") . "</td>
                            <td>" . $details['description'] . "</td>
                        <tr>
                        <tr>
                            <td>" . i18n("emailAddress") . "</td>
                            <td><a href='mailto:" . $details['emailAddress'] . "'>" . $details['emailAddress'] . "</td>
                        <tr>
                        <tr>
                            <td>" . i18n("phoneNumber") . "</td>
                            <td><a href='tel:" . $details['phoneNumber'] . "'>" . $details['phoneNumber'] . "</td>
                        <tr>
                        <tr>
                            <td>" . i18n("website") . "</td>
                            <td><a href='" . $details['website'] . "'>" . $details['website'] . "</td>
                        <tr>
                        <tr>
                            <td>" . i18n("facebook") . "</td>
                            <td><a href='" . $details['facebook'] . "'>" . $details['facebook'] . "</td>
                        <tr>
                        <tr>
                            <td>" . i18n("notes") . "</td>
                            <td>" . $details['notes'] . "</td>
                        <tr>
                        <tr>
                            <td>" . i18n("needPapers") . "</td>
                            <td>" . $details['needPapers'] . "</td>
                        <tr>
                        <tr>
                            <td>" . i18n("dateLastUpdated") . "</td>
                            <td>" . $details['dateLastUpdated'] . "</td>
                        <tr>
                    </table><br>";
                } else {
                    echo i18n("detailsError");
                }
                    
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