<?php
//chdir('../'); 

// wczytanie ustawien inicjujacych system
//require_once('ustawienia/init.php');

$blad = false;

if ( isset($_SESSION['zamowienie_id']) && $_SESSION['zamowienie_id'] != '' ) {

    $zapytanie = "SELECT customers_id FROM orders WHERE orders_id = '".(int)$_SESSION['zamowienie_id']."' LIMIT 1";
    $sql = $GLOBALS['db']->open_query($zapytanie);

    if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
        //
        $info = $sql->fetch_assoc();
        //
        if ( (int)$info['customers_id'] == (int)$_SESSION['customer_id'] || (int)$info['customers_id'] == (int)$_SESSION['gosc_id'] ) {
              //
              $zamowienie = new Zamowienie((int)$_SESSION['zamowienie_id']);
              //
              if ( count($zamowienie->info) == 0 ) {
                   //
                   Funkcje::PrzekierowanieURL('brak-strony.html'); 
                   //
              } 
              //
          } else {  
              //
              $blad = true;
              //
              
        }
        //
        unset($info);
        //
    } else {
        //
        $blad = true;
        //
    }

    $GLOBALS['db']->close_query($sql);
    unset($zapytanie);

} else {
  
    $blad = true;
    
}

if ( $blad ) {
    Funkcje::PrzekierowanieURL('brak-strony.html'); 
}

unset($blad);

require_once('tcpdf/config/lang/pol.php');
require_once('tcpdf/tcpdf.php');  

$GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('KLIENCI', 'KLIENCI_PANEL', 'ZAMOWIENIE_REALIZACJA') ), $GLOBALS['tlumacz'] );

class MYPDF extends TCPDF {

  public function Footer() {
      $this->SetY(-15);
      $this->SetFont('helvetica', 'I', 8);
      $this->Cell(0, 0, $GLOBALS['tlumacz']['WYGENEROWANO_W_PROGRAMIE'], 'T', false, 'L', 0, '', 0, false, 'T', 'M');
      $this->Cell(0, 0, $GLOBALS['tlumacz']['LISTING_STRONA'].' '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'T', 'M');
  }
  
}

$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdf->SetCreator('shopGold');
$pdf->SetAuthor('shopGold');
$pdf->SetTitle($GLOBALS['tlumacz']['DRUKUJ_ZAMOWIENIE']);
$pdf->SetSubject($GLOBALS['tlumacz']['DRUKUJ_ZAMOWIENIE']);
$pdf->SetKeywords($GLOBALS['tlumacz']['DRUKUJ_ZAMOWIENIE']);

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

$text = PDFZamowienie::WydrukZmowieniaPDF($_SESSION['zamowienie_id']);

// zamiana https na http
$text = str_replace('src="https', 'src="http', (string)$text);

$pdf->writeHTML($text, true, false, false, false, '');

// Sprawdza czy istnieje katalog  - jesli nie to go tworzy
if (is_dir(KATALOG_SKLEPU . PDF_ZAPISANIE_ZAMOWIENIA_KATALOG) == false) {
    $old_mask = umask(0);
    mkdir(KATALOG_SKLEPU . PDF_ZAPISANIE_ZAMOWIENIA_KATALOG, 0777, true);
    umask($old_mask);
}

$pdf->Output(KATALOG_SKLEPU . PDF_ZAPISANIE_ZAMOWIENIA_KATALOG.'/'.$_SESSION['zamowienie_id'].'_'.$_SERVER['REQUEST_TIME'].'.pdf', 'F');

?>