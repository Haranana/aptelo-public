<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

$AktywnyHash = false;

if ( isset($_GET['zamowienie']) && STR_PANELU_KLIENTA_BEZ_LOGOWANIA == 'tak' && isset($_GET['id_poz']) && (int)$_GET['id_poz'] > 0) {
  
    $zamowienie = new Zamowienie((int)$_GET['id_poz']);
    
    if ( count($zamowienie->info) == 0 ) {
         //
         Funkcje::PrzekierowanieURL('brak-strony.html'); 
         //
    }
    
    // ilosc dni od zlozenia zamowienia
    $IloscDni = round(((time() - FunkcjeWlasnePHP::my_strtotime($zamowienie->info['data_zamowienia'])) / 86400), 0);
    
    if ( $IloscDni <= STR_PANELU_KLIENTA_BEZ_LOGOWANIA_WAZNOSC ) {        
    
        $hashKod = hash("sha1", $zamowienie->info['id_zamowienia'] . ';' . $zamowienie->info['data_zamowienia'] . ';' . $zamowienie->klient['adres_email'] . ';' . $zamowienie->klient['id']);

        if ( $_GET['zamowienie'] === $hashKod ) {
             $AktywnyHash = true;
        }
        
    } else {
      
        $_SESSION['bladDniToken'] = true;
        Funkcje::PrzekierowanieSSL( 'logowanie.html' );
      
    }        

    unset($zamowienie);
  
}

$blad = false;

if ( (isset($_GET['id_poz']) && (int)$_GET['id_poz'] > 0) && FAKTURA_POBIERANIE == 'tak' ) {
  
    $zapytanie = "SELECT customers_id FROM orders WHERE orders_id = '" . (int)$_GET['id_poz'] . "' LIMIT 1";
    $sql = $GLOBALS['db']->open_query($zapytanie);
    
    if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
      //
      $info = $sql->fetch_assoc();
      //
      $TylkoGosc = false;
      // sprawdzenia dla podsumowania zamowienia
      if ( isset($_SESSION['gosc']) && (int)$_SESSION['gosc'] == '1' && isset($_SESSION['gosc_id_pdf']) && (int)$_SESSION['gosc_id_pdf'] > 0 && (int)$info['customers_id'] == (int)$_SESSION['gosc_id_pdf'] ) {
           //
           $TylkoGosc = true;
           //
      }
      //
      if ( (isset($_SESSION['customer_id']) && (int)$info['customers_id'] == (int)$_SESSION['customer_id']) || $AktywnyHash == true || $TylkoGosc == true ) {
            //
            $zamowienie = new Zamowienie((int)$_GET['id_poz']);
            //
            if ( count($zamowienie->info) == 0 ) {
                 //
                 Funkcje::PrzekierowanieURL('brak-strony.html'); 
                 //
            }
            //
            // dodaje date pobrania faktury proforma
            $pola = array(
                    array('invoice_proforma_date',time()),
                    array('invoice_proforma_nr',(int)$_GET['id_poz']));
                    
            $GLOBALS['db']->update_query('orders' , $pola, " orders_id = '".(int)$_GET['id_poz']."'");	
            unset($pola);        
            //
      } else {
            //
            $blad = true;
            //
      }
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

$GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('KLIENCI', 'KLIENCI_PANEL', 'FAKTURA') ), $GLOBALS['tlumacz'] );

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
$pdf->SetTitle($GLOBALS['tlumacz']['FAKTURA']);
$pdf->SetSubject($GLOBALS['tlumacz']['FAKTURA']);
$pdf->SetKeywords($GLOBALS['tlumacz']['FAKTURA']);

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

$text = PDFFaktura::WydrukFakturyPDF((int)$_GET['id_poz'], '', '1', '');
$pdf->writeHTML($text, true, false, false, false, '');

$pdf->Output('faktura_'.(int)$_GET['id_poz'].'_'.time().'.pdf', 'D');

?>