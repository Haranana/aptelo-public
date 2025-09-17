<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

require_once('../tcpdf/config/lang/pol.php');
require_once('../tcpdf/tcpdf.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

  class MYPDF extends TCPDF {

  }

  // create new PDF document
  $pdf = new MYPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

  // set document information
  $pdf->SetCreator('shopGold');
  $pdf->SetAuthor('shopGold');
  $pdf->SetTitle('Książka nadawcza');
  $pdf->SetSubject('Książka nadawcza');
  $pdf->SetKeywords('Książka nadawcza');

  $pdf->SetHeaderData('', '', 'Załącznik Nr .........................................', 'Imię i nazwisko (nazwa) oraz adres nadawcy : ' . DANE_NAZWA_FIRMY_PELNA . ', ' . DANE_ADRES_LINIA_1 . ', ' . DANE_KOD_POCZTOWY . ' ' . DANE_MIASTO);

  // set header and footer fonts
  $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', '8'));
  //$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

  // set default monospaced font
  $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

  //set margins
  $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
  $pdf->SetHeaderMargin(10);

  //set auto page breaks
  $pdf->SetAutoPageBreak(FALSE, PDF_MARGIN_BOTTOM);

  //set image scale factor
  $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

  //set some language-dependent strings
  //$pdf->setLanguageArray($l);

  // ---------------------------------------------------------


  // add a page
  $pdf->AddPage();

  // set font
  $pdf->SetFont('dejavusans', '', 8);

  //$pdf->Write(0, '', '', 0, 'L', true, 0, false, false, 0);

  // -----------------------------------------------------------------------------
  $html               = '';
  $definicje_styli    = '';
  $naglowek_tabeli    = '';
  $tresc_tabeli       = '';
  $stopka_tabeli      = '';
  $koniec_tabeli      = '</table>';

  $kwota_pobrania_razem = 0;
  $kwota_wartosci_razem = 0;

  $definicje_styli .= '<style>';
  $definicje_styli .= '
  .naglowek { color: #000000; text-align:center; vertical-align: middle;}
  .trescText { color: #000000; text-align:left; height:50px;}
  .trescLiczba { color: #000000; text-align:right;}
  ';
  $definicje_styli .= '</style>';


  $html = $definicje_styli;

  $lp           = 1;
  $strona       = 1;
  $ilosc_danych = count($_POST['wiersz']);
  $mnoznik      = 1;

  foreach ( $_POST['wiersz'] as $wiersz ) {
    $rodzaj_wysylki = '';
    if ( isset($wiersz['rodzaj_wysylki']) ) {
      if ( $wiersz['rodzaj_wysylki'] == '0' ) {
        $rodzaj_wysylki = 'EKON';
      } else {
        $rodzaj_wysylki = 'PRIOR';
      }
    }

    $kwota_pobrania_tablica = array();
    if ( isset($wiersz['pobranie']) && $wiersz['pobranie'] == '1' ) {
      $kwota_pobrania_tablica = explode('.', number_format($wiersz['wartosc'], 2, '.', ' '));
      $kwota_pobrania_razem = $kwota_pobrania_razem + $wiersz['wartosc'];
    }

    $kwota_wartosci_tablica = array();
    if ( isset($wiersz['wartosciowa']) && $wiersz['wartosciowa'] == '1' ) {
      $kwota_wartosci_tablica = explode('.', number_format($wiersz['wartosc'], 2, '.', ' '));
      $kwota_wartosci_razem = $kwota_wartosci_razem + $wiersz['wartosc'];
    }

    if ( $lp == '1' || $lp == $mnoznik ) {
      $html .= naglowekTabeli( ( $lp == '1' ? '' : $kwota_pobrania_razem ) );
    }

    $html .= '
          <tr>
            <td class="trescLiczba" style="width:30px;border-left: #c0c0c0 1px solid;border-top: #c0c0c0 1px solid;">'.$lp.'</td>
            <td class="trescText" style="width:200px;border-left: #c0c0c0 1px solid;border-top: #c0c0c0 1px solid;">'.nl2br($wiersz['adresat']).'</td>
            <td class="trescText" style="width:150px;border-left: #c0c0c0 1px solid;border-top: #c0c0c0 1px solid;">'.nl2br($wiersz['adres_dostawy']).'</td>
            <td class="trescLiczba" style="width:50px;border-left: #c0c0c0 1px solid;border-top: #c0c0c0 1px solid;">'.( isset($kwota_wartosci_tablica['0']) ? $kwota_wartosci_tablica['0']: '' ).'</td>
            <td class="trescLiczba" style="width:30px;border-left: #c0c0c0 1px solid;border-top: #c0c0c0 1px solid;">'.( isset($kwota_wartosci_tablica['1']) ? $kwota_wartosci_tablica['1']: '' ).'</td>
            <td class="naglowek" style="width:50px;border-left: #c0c0c0 1px solid;border-top: #c0c0c0 1px solid;"></td>
            <td class="naglowek" style="width:30px;border-left: #c0c0c0 1px solid;border-top: #c0c0c0 1px solid;"></td>
            <td class="naglowek" style="width:150px;border-left: #c0c0c0 1px solid;border-top: #c0c0c0 1px solid;"></td>
            <td class="naglowek" style="width:110px;border-left: #c0c0c0 1px solid;border-top: #c0c0c0 1px solid;">'.$rodzaj_wysylki.'</td>
            <td class="naglowek" style="width:60px;border-left: #c0c0c0 1px solid;border-top: #c0c0c0 1px solid;"></td>
            <td class="naglowek" style="width:30px;border-left: #c0c0c0 1px solid;border-top: #c0c0c0 1px solid;"></td>
            <td class="trescLiczba" style="width:60px;border-left: #c0c0c0 1px solid;border-top: #c0c0c0 1px solid;">'.( isset($kwota_pobrania_tablica['0']) ? $kwota_pobrania_tablica['0']: '' ).'</td>
            <td class="trescLiczba" style="width:30px;border-left: #c0c0c0 1px solid;border-top: #c0c0c0 1px solid;border-right: #c0c0c0 1px solid;">'.( isset($kwota_pobrania_tablica['1']) ? $kwota_pobrania_tablica['1']: '' ).'</td>
        </tr>
    ';

    if ( ( $lp == ($strona * 10) ) || $ilosc_danych == $lp ) {
      $html .= stopkaTabeli( $kwota_pobrania_razem );
      $html .= $koniec_tabeli;
      $pdf->writeHTML($html, true, false, false, false, '');
      if ( $lp == ($strona * 10) && $lp < $ilosc_danych ) {
        $pdf->AddPage();
        $html = $definicje_styli;
      }
      $mnoznik = ($strona * 10) + 1;
      $strona++;
    }

    $lp++;
  }


  //Close and output PDF document
  $nazwa_pliku = 'ksiazka_nadawcza_'.time().'.pdf';
  $pdf->Output($nazwa_pliku, 'D');

}


function naglowekTabeli( $pobranie = array() ) {

  if ( !empty($pobranie) )  {
    $kwota_pobrania_razem_tablica = explode('.', number_format($pobranie, 2, '.', ' '));
  }

  $naglowek_tabeli = '
  <table cellspacing="0" cellpadding="3" border="0" width="100%">
      <tr>
          <td class="naglowek" style="width:30px;border-left: #c0c0c0 1px solid;border-top: #c0c0c0 1px solid;">Lp.</td>
          <td class="naglowek" style="width:200px;border-left: #c0c0c0 1px solid;border-top: #c0c0c0 1px solid;">ADRESAT<br />(imię i nazwisko<br />lub nazwa)</td>
          <td class="naglowek" style="width:150px;border-left: #c0c0c0 1px solid;border-top: #c0c0c0 1px solid;">Dokładne miejsce<br />doręczenia</td>
          <td colspan="2" class="naglowek" style="width:80px;border-left: #c0c0c0 1px solid;border-top: #c0c0c0 1px solid;">Kwota zadekl.<br />wartości</td>
          <td colspan="2" class="naglowek" style="width:80px;border-left: #c0c0c0 1px solid;border-top: #c0c0c0 1px solid;">Masa</td>
          <td class="naglowek" style="width:150px;border-left: #c0c0c0 1px solid;border-top: #c0c0c0 1px solid;">Nr<br />nadawczy</td>
          <td class="naglowek" style="width:110px;border-left: #c0c0c0 1px solid;border-top: #c0c0c0 1px solid;">Uwagi</td>
          <td colspan="2" class="naglowek" style="width:90px;border-left: #c0c0c0 1px solid;border-top: #c0c0c0 1px solid;">Opłata</td>
          <td colspan="2" class="naglowek" style="width:90px;border-left: #c0c0c0 1px solid;border-top: #c0c0c0 1px solid;border-right: #c0c0c0 1px solid;">Kwota<br />pobrania</td>
      </tr>
      <tr>
          <td class="naglowek" style="width:30px;border-left: #c0c0c0 1px solid;"></td>
          <td class="naglowek" style="width:200px;border-left: #c0c0c0 1px solid;"></td>
          <td class="naglowek" style="width:150px;border-left: #c0c0c0 1px solid;"></td>
          <td class="naglowek" style="width:50px;border-left: #c0c0c0 1px solid;border-top: #c0c0c0 1px solid;">zł</td>
          <td class="naglowek" style="width:30px;border-left: #c0c0c0 1px solid;border-top: #c0c0c0 1px solid;">gr</td>
          <td class="naglowek" style="width:50px;border-left: #c0c0c0 1px solid;border-top: #c0c0c0 1px solid;">kg</td>
          <td class="naglowek" style="width:30px;border-left: #c0c0c0 1px solid;border-top: #c0c0c0 1px solid;">g</td>
          <td class="naglowek" style="width:150px;border-left: #c0c0c0 1px solid;"></td>
          <td class="naglowek" style="width:110px;border-left: #c0c0c0 1px solid;"></td>
          <td class="naglowek" style="width:60px;border-left: #c0c0c0 1px solid;border-top: #c0c0c0 1px solid;">zł</td>
          <td class="naglowek" style="width:30px;border-left: #c0c0c0 1px solid;border-top: #c0c0c0 1px solid;">gr</td>
          <td class="naglowek" style="width:60px;border-left: #c0c0c0 1px solid;border-top: #c0c0c0 1px solid;">zł</td>
          <td class="naglowek" style="width:30px;border-left: #c0c0c0 1px solid;border-top: #c0c0c0 1px solid;border-right: #c0c0c0 1px solid;">gr</td>
      </tr>
      <tr>
          <td class="naglowek" style="width:30px;border-left: #c0c0c0 1px solid;border-top: #c0c0c0 1px solid;">1</td>
          <td class="naglowek" style="width:200px;border-left: #c0c0c0 1px solid;border-top: #c0c0c0 1px solid;">2</td>
          <td class="naglowek" style="width:150px;border-left: #c0c0c0 1px solid;border-top: #c0c0c0 1px solid;">3</td>
          <td colspan="2" class="naglowek" style="width:80px;border-left: #c0c0c0 1px solid;border-top: #c0c0c0 1px solid;">4</td>
          <td colspan="2" class="naglowek" style="width:80px;border-left: #c0c0c0 1px solid;border-top: #c0c0c0 1px solid;">5</td>
          <td class="naglowek" style="width:150px;border-left: #c0c0c0 1px solid;border-top: #c0c0c0 1px solid;">6</td>
          <td class="naglowek" style="width:110px;border-left: #c0c0c0 1px solid;border-top: #c0c0c0 1px solid;">7</td>
          <td colspan="2" class="naglowek" style="width:90px;border-left: #c0c0c0 1px solid;border-top: #c0c0c0 1px solid;">8</td>
          <td colspan="2" class="naglowek" style="width:90px;border-left: #c0c0c0 1px solid;border-top: #c0c0c0 1px solid;border-right: #c0c0c0 1px solid;">9</td>
      </tr>
      <tr>
          <td colspan="3" class="naglowek" style="text-align:right;width:380px;border-left: #c0c0c0 1px solid;border-top: #c0c0c0 1px solid;">Z przeniesienia</td>
          <td class="naglowek" style="width:50px;border-left: #c0c0c0 1px solid;border-top: #c0c0c0 1px solid;"></td>
          <td class="naglowek" style="width:30px;border-left: #c0c0c0 1px solid;border-top: #c0c0c0 1px solid;"></td>
          <td colspan="3" class="naglowek" style="text-align:right;width:340px;border-left: #c0c0c0 1px solid;border-top: #c0c0c0 1px solid;">Z przeniesienia</td>
          <td class="naglowek" style="width:60px;border-left: #c0c0c0 1px solid;border-top: #c0c0c0 1px solid;"></td>
          <td class="naglowek" style="width:30px;border-left: #c0c0c0 1px solid;border-top: #c0c0c0 1px solid;"></td>
          <td class="trescLiczba" style="width:60px;border-left: #c0c0c0 1px solid;border-top: #c0c0c0 1px solid;">'.( isset($kwota_pobrania_razem_tablica['0']) && $kwota_pobrania_razem_tablica['0'] > 0 ? $kwota_pobrania_razem_tablica['0']: '' ).'</td>
          <td class="trescLiczba" style="width:30px;border-left: #c0c0c0 1px solid;border-top: #c0c0c0 1px solid;border-right: #c0c0c0 1px solid;">'.( isset($kwota_pobrania_razem_tablica['1']) && $kwota_pobrania_razem_tablica['0'] > 0 ? $kwota_pobrania_razem_tablica['1']: '' ).'</td>
      </tr>';

  return $naglowek_tabeli;
}

function stopkaTabeli( $pobranie = array() ) {

  if ( !empty($pobranie) )  {
    $kwota_pobrania_razem_tablica = explode('.', number_format($pobranie, 2, '.', ' '));
  }

  $stopka_tabeli = '
    <tr>
      <td colspan="3" class="naglowek" style="text-align:right;width:380px;border-top: #c0c0c0 1px solid;">Do przeniesienia</td>
      <td class="naglowek" style="width:50px;border-left: #c0c0c0 1px solid;border-top: #c0c0c0 1px solid;border-bottom: #c0c0c0 1px solid;"></td>
      <td class="naglowek" style="width:30px;border-left: #c0c0c0 1px solid;border-top: #c0c0c0 1px solid;border-bottom: #c0c0c0 1px solid;"></td>
      <td colspan="3" class="naglowek" style="text-align:right;width:340px;border-left: #c0c0c0 1px solid;border-top: #c0c0c0 1px solid;">Do przeniesienia</td>
      <td class="naglowek" style="width:60px;border-left: #c0c0c0 1px solid;border-top: #c0c0c0 1px solid;border-bottom: #c0c0c0 1px solid;"></td>
      <td class="naglowek" style="width:30px;border-left: #c0c0c0 1px solid;border-top: #c0c0c0 1px solid;border-bottom: #c0c0c0 1px solid;"></td>
      <td class="trescLiczba" style="width:60px;border-left: #c0c0c0 1px solid;border-top: #c0c0c0 1px solid;border-bottom: #c0c0c0 1px solid;">'.( isset($kwota_pobrania_razem_tablica['0']) && $kwota_pobrania_razem_tablica['0'] > 0 ? $kwota_pobrania_razem_tablica['0']: '' ).'</td>
      <td class="trescLiczba" style="width:30px;border-left: #c0c0c0 1px solid;border-top: #c0c0c0 1px solid;border-right: #c0c0c0 1px solid;border-bottom: #c0c0c0 1px solid;">'.( isset($kwota_pobrania_razem_tablica['1']) && $kwota_pobrania_razem_tablica['0'] > 0 ? $kwota_pobrania_razem_tablica['1']: '' ).'</td>
      </tr>';

  return $stopka_tabeli;
}
?>