<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

require_once('../tcpdf/config/lang/pol.php');
require_once('../tcpdf/tcpdf.php');

require_once('../tcpdf/label/class.label.php');
require_once('../tcpdf/label/class.etykiety.php');

$data = array();

for ( $l = 0; $l < $_POST['offset']-1; $l++ ) {
    $data[] = array(
      'wiersz1'       => '', 
      'wiersz2'       => '', 
      'wiersz3'       => '', 
      'wiersz4'       => '', 
      'wiersz5'       => '', 
    );
}
foreach ( $_POST['wiersz'] as $wiersz ) {
  $tablica = explode("<br />", nl2br($wiersz['adresat']));
  $data[] = array(
      'wiersz1'       => ( isset($tablica['0']) ? trim((string)$tablica['0']) : '' ), 
      'wiersz2'       => ( isset($tablica['1']) ? trim((string)$tablica['1']) : '' ), 
      'wiersz3'       => ( isset($tablica['2']) ? trim((string)$tablica['2']) : '' ), 
      'wiersz4'       => ( isset($tablica['3']) ? trim((string)$tablica['3']) : '' ), 
      'wiersz5'       => ( isset($tablica['4']) ? trim((string)$tablica['4']) : '' ), 
    );

}

$pdf = new etykiety( $_POST['id'], $data , ( $_POST['ramka'] == '1' ? true : false ));

$pdf->SetCreator('shopGold');
$pdf->SetAuthor('shopGold');
$pdf->SetTitle('Etykiety adresowe');
$pdf->SetSubject('Etykiety adresowe');

$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

$pdf->SetHeaderMargin(0);
$pdf->SetFooterMargin(0);

$pdf->SetAutoPageBreak( true, 0);

//set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);  

/*************************/
$pdf->Addlabel();
/************************/

$nazwa_pliku = 'etykiety_'.time().'.pdf';
$pdf->Output($nazwa_pliku, "D");

?>