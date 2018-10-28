<?php
    include "lib/lib.php";
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Khora Infomap | HELP</title>
        <?php
            echo getHeadContent();
        ?>
    </head>
    <body>
        <?php
            echo getTopArea(i18n("help"));
        ?>
        
        <?php
            if (!isMobile()) {
                echo '<div id="content" style="top: 200px; left: 23%; width: 54%;">';
            } else {
                echo '<div id="content" style="top: 15mm; left: 5mm; width: calc(100% - 10mm);">';
            }
            echo(i18n("helpContent"));
        ?>
            <br><br><br>
        </div>
    </body>
</html>