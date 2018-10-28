<?php
require('lib/fpdf/tfpdf.php');
require('lib/lib.php');

class PDF extends tFPDF
{
    protected $col = 0; // Current column
    protected $y0;      // Ordinate of column start

    function Header() {
        // Page header
        global $title;

        $this->SetFont('DejaVu','',12);
        $w = $this->GetStringWidth($title) + 6;
        $this->SetX((300 - $w) / 2);
        $this->SetDrawColor(0, 0, 0);
        $this->SetFillColor(230, 230, 230);
        $this->SetTextColor(0, 0, 0);
        $this->SetLineWidth(1);
        $this->Cell($w, 9, $title, 1, 1, 'C', true);
        $this->Ln(10);
        // Save ordinate
        $this->y0 = $this->GetY();
    }

    function Footer() {
        // Page footer
        $this->SetY(-15);
        $this->SetFont('DejaVu','',12);
        $this->SetTextColor(128);
        $this->Cell(0, 10, $this->PageNo() . '/{nb}', 0, 0, 'C');
    }

    function SetCol($col) {
        // Set position at a given column
        $this->col = $col;
        $x = 10 + $col * 65;
        $this->SetLeftMargin($x);
        $this->SetX($x);
    }

    function AcceptPageBreak() {
        // Method accepting or not automatic page break
        if($this->col < 2) {
            // Go to next column
            $this->SetCol($this->col + 1);
            // Set ordinate to top
            $this->SetY($this->y0);
            // Keep on page
            return false;
        } else {
            // Go back to first column
            $this->SetCol(0);
            // Page break
            return true;
        }
    }

    function ChapterTitle($num, $label) {
        // Title
        $this->SetFont('DejaVu','',12);
        $this->SetFillColor(230, 230, 230);
        $this->Cell(0, 6, "$num: $label", 0, 1 ,'L', true);
        $this->Ln(4);
        // Save ordinate
        $this->y0 = $this->GetY();
    }

    function ChapterBody($txt) {
        // Read text file
        // Font
        $this->SetFont('DejaVu','',12);
        // Output text in a 6 cm width column
        $this->MultiCell(60, 5, $txt);
        $this->Ln();
        // Mention
        $this->SetFont('DejaVu','',12);
        // Go back to first column
        $this->SetCol(0);
    }

    function PrintChapter($num, $title, $txt) {
        // Add chapter
        $this->AddPage("L");
        $this->ChapterTitle($num, $title);
        $this->ChapterBody($txt);
        
        
        
        
        $address = "Eptachalkou 25,Athina";
        $latLon = mapquestGeocodeApiAddressToLocation($address);
        $latLon2 = array($latLon[0] + 0.001, $latLon[1] + 0.001);
        $latLon3 = array($latLon[0] - 0.002, $latLon[1] - 0.002);
        $this->Image(downloadStaticMapWithMarkers($latLon, 14, "200x200", array($latLon, $latLon2, $latLon3)), 78, 120);
    }
}

/*
 *Gets the content that can be put in a PDF export file
 */
function getInfomapContent($language, $spreadsheetUrl, $idsString) {
    // which shall be exported? if empty, export all
    $idsToExport = array();
    if (isset($idsString) && strcmp($idsString, "") != 0) {
        $idsToExport = explode(',', $idsString);
    }
    
    // how many columns do we want to present in the list?
    $previewCount = 6;
    
    // fetch the data from the cached spreadsheet in the given language
    $data = getFileContentAsCsv($_SESSION["dataCacheFilePath" . getLanguage()]);
    $dataEnglish = getFileContentAsCsv($_SESSION["dataCacheFilePathEnglish"]);
    
    // construct a string with the given information
    $retVal = "";
    for ($i = 1; $i < count($data); $i++) {
        if (count($idsToExport) == 0 || in_array(strval($i), $idsToExport)) {
            for ($j = 0; $j < $previewCount; $j++) {
                if ($j == 0) {
                    $retVal = $retVal . getOrDefault($data, $dataEnglish, $i, $j) . ". ";
                } else {
                    $retVal = $retVal . getOrDefault($data, $dataEnglish, $i, $j) . "\n";
                }
            }
            $retVal = $retVal . "\n";      
        }
    }
    return $retVal;
}

$pdf = new PDF();
// Add a Unicode font (uses UTF-8)
$pdf->AddFont('DejaVu','','DejaVuSansCondensed.ttf',true);
$pdf->SetFont('DejaVu','',14);
$title = 'Khora Infomap';
$pdf->SetTitle($title);
$pdf->AliasNbPages();
$pdf->SetAuthor('InfoMap');

$idsToExportString = "";
if (isset($_GET["ids"]) && strcmp($_GET["ids"], "") != 0) {
    $idsToExportString = htmlspecialchars($_GET["ids"]);
}
    
$infomapContent = getInfomapContent($_SESSION["language"], $_SESSION["spreadsheetUrl" . $_SESSION["language"]], $idsToExportString);
$pdf->PrintChapter(1, 'INFOMAP_EXAMPLE', $infomapContent);
$pdf->Output();
?>