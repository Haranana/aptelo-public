<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if ( isset($_POST['plik']) ) {
      
         if ( file_exists('../' . KATALOG_ZDJEC . '/' . $_POST['plik']) ) {
      
             $ex = pathinfo('../' . KATALOG_ZDJEC . '/' . $_POST['plik']);
             //
             if ( !isset($ex['extension']) ) {
                  //
                  $roz = explode('.', (string)$_POST['plik']);
                  $ex['extension'] = $roz[ count($roz) - 1];
                  //
             }      
             
             list($szerokosc, $wysokosc, $typ, $atrybuty) = getimagesize('../' . KATALOG_ZDJEC . '/' . $_POST['plik']);
             
             if ( $typ == '1' || $typ == '2' || $typ == '3' || $ex['extension'] == 'webp' ) {
         
                  echo $szerokosc;

             }
             
         }
         
         
    }

}
?>