<?php

ob_start();

chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

if ( !isset($_GET['id_poz']) || (int)$_GET['id_poz'] == 0 ) {
    Funkcje::PrzekierowanieURL('brak-strony.html'); 
}

$Produkt = new Produkt( Funkcje::SamoIdProduktuBezCech($_GET['id_poz']) );                

$AdresUrl = ADRES_URL_SKLEPU . '/' . $Produkt->info['adres_seo'];

if ($Produkt->CzyJestProdukt == true && KARTA_PRODUKTU_LINK_SPECYFIKACJA_PDF == 'tak') {

    require_once('tcpdf/config/lang/pol.php');
    require_once('tcpdf/tcpdf.php');    

    class MYPDF extends TCPDF {

        public function Footer() {
          global $AdresUrl;

          $this->SetY(-15);
          $this->SetFont('helvetica', 'I', 6);
          $this->Cell(0, 0, $GLOBALS['tlumacz']['WYGENEROWANO_W_PROGRAMIE'], 'T', false, 'L', 0, '', 0, false, 'T', 'M');

          if (PDF_POKAZ_KOD_QR == 'tak') {
            $qrSize = 20; // Rozmiar kodu QR (szerokość i wysokość w mm)
            $padding = 2;
            $opisQR = $GLOBALS['tlumacz']['KARTA_PRODUKTU_PDF_KOD_QR'];

            $pageWidth = $this->getPageWidth();
            $pageHeight = $this->getPageHeight();

            // Obliczanie współrzędnych X i Y dla prawego dolnego rogu
            $x = $pageWidth - $qrSize - PDF_MARGIN_RIGHT;
            $y = $pageHeight - $qrSize - (PDF_MARGIN_BOTTOM/4);
            $x_right = $pageWidth - $qrSize - PDF_MARGIN_RIGHT - (3 * $padding) - $this->GetStringWidth($opisQR);

            // Rysowanie tła (biały prostokąt z lekkim marginesem)
            $this->SetFillColor(255, 255, 255); // Kolor biały
            $this->Rect($x - $padding, $y - $padding, $qrSize + (2 * $padding), $qrSize + (2 * $padding), 'F');

            $this->write2DBarcode($AdresUrl, 'QRCODE,H', $x, $y, $qrSize, $qrSize, null, 'N');

            // Dodanie opisu pod kodem QR
            $this->SetFont('dejavusans', '', 6);
            $this->SetTextColor(0, 0, 0); // Czarny kolor tekstu
            $this->Text($x_right, $y + $qrSize - 8, $GLOBALS['tlumacz']['KARTA_PRODUKTU_PDF_KOD_QR']);

          }
        }
        
    }

    $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    //$fontname = $pdf->addTTFfont('tcpdf/fonts/tahomabd.ttf', 'TrueTypeUnicode', '', 96);

    $pdf->SetCreator('shopGold');
    $pdf->SetAuthor('shopGold');
    $pdf->SetTitle($GLOBALS['tlumacz']['PRODUKT']);
    $pdf->SetSubject($GLOBALS['tlumacz']['PRODUKT']);
    $pdf->SetKeywords($GLOBALS['tlumacz']['PRODUKT']);

    if (PDF_PLIK_NAGLOWKA != '' && file_exists(KATALOG_SKLEPU . KATALOG_ZDJEC . '/'.PDF_PLIK_NAGLOWKA)) {
        $plik_naglowka = PDF_PLIK_NAGLOWKA;
        $szerokosc_pliku_naglowka = PDF_PLIK_NAGLOWKA_SZEROKOSC;
    } else {
        $plik_naglowka = '';
        $szerokosc_pliku_naglowka = '';
    }

    $daneFirmy = explode(PHP_EOL, (string)PDF_DANE_FIRMY);
    $pozostaleDaneFirmy = '';
    for ( $y = 1; $y < count($daneFirmy); $y++ ) {  
        $pozostaleDaneFirmy .= $daneFirmy[$y] . "\n";
    }
    $pdf->SetHeaderData($plik_naglowka, $szerokosc_pliku_naglowka, trim((string)$daneFirmy[0]), $pozostaleDaneFirmy);
    unset($daneFirmy, $pozostaleDaneFirmy);    

    $pdf->SetFont('dejavusans', '', 6);

    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', '6'));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    // ---------------------------------------------------------

    $pdf->AddPage();

    $pdf->SetFont('dejavusans', '', 8);

    $text = PDFKartaProduktu::WydrukKartyProduktuPDF( $Produkt->info['id'] );
    
    // zamiana https na http
    $text = str_replace('src="https', 'src="http', (string)$text);
    
    $pdf->writeHTML($text, true, false, false, false, '');

    ob_end_clean();
    $pdf->Output( str_replace('.html', '.pdf', Seo::link_SEO( $Produkt->info['nazwa_seo'], '', 'inna' )), 'D');

} else {

    Funkcje::PrzekierowanieURL('brak-strony.html'); 

}
?>