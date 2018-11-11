<?php
require('lib/lib.php');
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Khora Infomap | REDIRECTING...</title>
        <?php
            echo getHeadContent();
        ?>
    </head>
    <body>
        <?php
            $idsToExportString = "";
            if (isset($_GET["ids"]) && strcmp($_GET["ids"], "") != 0) {
                $idsToExportString = htmlspecialchars($_GET["ids"]);
            }
            
            $urlForData = "https://$_SERVER[HTTP_HOST]/infomap/exportSource.php?language=" . getLanguage() . "&ids=" . $idsToExportString;
            $filepath = downloadHtmlToPdf($urlForData);
            echo "Redirecting to the PDF file. If it does not work, click <a href='" . $filepath . "'>here</a>!<br>
            <script>
                document.location = '" . $filepath . "';
            </script>" . $urlForData;
        ?>
    </body>
</html>