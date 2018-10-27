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
            $headerText = i18n("Details");
            $id = -1;
            if (!isset($_GET["id"])) {
                $headerText = i18n("Details - Error, no ID specified!");
            } else {
                $id = htmlspecialchars($_GET["id"]);
                $headerText = i18n("Details") . "&nbsp;-&nbsp;ID:&nbsp;" . $id;
            }
            echo getTopArea($headerText);
            
            echo getSearch();
        ?>
        <div id="content">
            <?php
                echo "<table style='margin-bottom: 10px;'>
                        <td style='padding: 10px; padding-left: 5mm;'>
                            " . getButton(i18n("back"), "img/backArrow.png", "document.location='infomap.php';") . "
                        </td>
                        <td id=\"favoriteStar\" style='padding: 10px; padding-left: 25mm; font-size: 8mm; font-weight: bold;'>
                            " . i18n("Favorite") . ":&nbsp;<img src='img/starActive.png'>
                        </td>
                    </table>
                    <script>
                        if (getFavorites().indexOf(" . $id . ") < 0) {
                            document.getElementById(\"favoriteStar\").innerHTML = \"" . i18n("Favorite") . ":&nbsp;<img src='img/starInactive.png'>\";
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
                            <th>ID</th>
                            <th>" . $id . "</th>
                        <tr>
                        <tr>
                            <td>Category</td>
                            <td>" . $details['category'] . "</td>
                        <tr>
                        <tr>
                            <td>Name</td>
                            <td>" . $details['name'] . "</td>
                        <tr>
                        <tr>
                            <td>Opening Hours</td>
                            <td>" . $details['openingHours'] . "</td>
                        <tr>
                        <tr>
                            <td>Address</td>
                            <td><a href='https://www.google.de/maps/place/" . $details['address'] . "'>" . $details['address'] . "</td>
                        <tr>
                        <tr>
                            <td>Services</td>
                            <td>" . $details['services'] . "</td>
                        <tr>
                        <tr>
                            <td>Description</td>
                            <td>" . $details['description'] . "</td>
                        <tr>
                        <tr>
                            <td>Email Address</td>
                            <td><a href='mailto:" . $details['emailAddress'] . "'>" . $details['emailAddress'] . "</td>
                        <tr>
                        <tr>
                            <td>Phone Number</td>
                            <td><a href='tel:" . $details['phoneNumber'] . "'>" . $details['phoneNumber'] . "</td>
                        <tr>
                        <tr>
                            <td>Website</td>
                            <td><a href='" . $details['website'] . "'>" . $details['website'] . "</td>
                        <tr>
                        <tr>
                            <td>Facebook</td>
                            <td><a href='" . $details['facebook'] . "'>" . $details['facebook'] . "</td>
                        <tr>
                        <tr>
                            <td>Notes</td>
                            <td>" . $details['notes'] . "</td>
                        <tr>
                        <tr>
                            <td>Need Papers?</td>
                            <td>" . $details['needPapers'] . "</td>
                        <tr>
                        <tr>
                            <td>Date last updated</td>
                            <td>" . $details['dateLastUpdated'] . "</td>
                        <tr>
                    </table><br>";
                } else {
                    echo i18n("Details - Error, no ID specified!");
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