<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');


// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

  $export_csv = 'Nazwa;Nazwa firmy;Ulica;Kod pocztowy;Miasto;Telefon;E-mail;Nr referencyjny;Zawartość;Uwagi do doręczenia;Pobranie;Przesyłka wartościowa;Waluta;Waga'."\n";

  foreach ( $_POST['wiersz'] as $wiersz ) {

    $export_csv .= $wiersz['klient'] .';';
    if ( $wiersz['firma'] != '' ) {
        $export_csv .= $wiersz['firma'] .';';
    } else {
        $export_csv .= $wiersz['klient'] .';';
    }
    $export_csv .= $wiersz['ulica'] .';';
    $export_csv .= $wiersz['kod'] .';';
    $export_csv .= $wiersz['miasto'] .';';
    $export_csv .= $wiersz['telefon'] .';';
    $export_csv .= $wiersz['email'] .';';
    $export_csv .= $wiersz['zamowienie_id'] .';';
    $export_csv .= $wiersz['zawartosc'] .';';
    $export_csv .= Funkcje::CzyszczenieTekstu($wiersz['komentarz']) .';';
    if ( isset($wiersz['pobranie']) && $wiersz['pobranie'] == '1' ) {
        $export_csv .= $wiersz['wartosc'].';';
    } else {
        $export_csv .= ';';
    }
    if ( isset($wiersz['wartosciowa']) && $wiersz['wartosciowa'] == '1' ) {
        $export_csv .= $wiersz['wartosc'].';';
    } else {
        $export_csv .= ';';
    }
    $export_csv .= $wiersz['waluta'] .';';
    $export_csv .= $wiersz['waga'] .'';
    $export_csv .= "\n";

  }

  header("Content-Type: application/force-download\n");
  header("Cache-Control: cache, must-revalidate");   
  header("Pragma: public");
  header("Content-Disposition: attachment; filename=zamowienia_dpd_" . date("Ymd") . ".csv");
  print $export_csv;
  exit;        

}

?>