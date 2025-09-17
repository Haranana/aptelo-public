<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

if ( $GLOBALS['koszykKlienta']->KoszykIloscProduktow() > 0 && PDF_KOSZYK_POBRANIE_PDF == 'tak' ) {

    require_once('tcpdf/config/lang/pol.php');
    require_once('tcpdf/tcpdf.php');    

    class MYPDF extends TCPDF {

        public function Footer() {
          $this->SetY(-15);
          $this->SetFont('helvetica', 'I', 6);
          $this->Cell(0, 0, $GLOBALS['tlumacz']['WYGENEROWANO_W_PROGRAMIE'], 'T', false, 'L', 0, '', 0, false, 'T', 'M');
        }
        
    }

    $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    $pdf->SetCreator('shopGold');
    $pdf->SetAuthor('shopGold');
    $pdf->SetTitle($GLOBALS['tlumacz']['KOSZYK']);
    $pdf->SetSubject($GLOBALS['tlumacz']['KOSZYK']);
    $pdf->SetKeywords($GLOBALS['tlumacz']['KOSZYK']);

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

    $text = PDFKoszyk::WydrukKoszykaPDF();
    
    // zamiana https na http
    $text = str_replace('src="https', 'src="http', (string)$text);
    
    $pdf->writeHTML($text, true, false, false, false, '');

    $pdf->Output( 'szczegoly-koszyka.pdf', 'D');
  
} else {

    Funkcje::PrzekierowanieURL('brak-strony.html'); 

}

?>