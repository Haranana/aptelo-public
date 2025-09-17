<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

$czy_jest_blad = false;

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        $warunki_szukania = ' and p.products_id > 0 ';
        //

        if ( isset($_SESSION['filtry']['produkty_magazyn.php']) && count($_SESSION['filtry']['produkty_magazyn.php']) > 0 ) {
             //
             foreach ( $_SESSION['filtry']['produkty_magazyn.php'] as $klucz => $wartosc ) {
                //
                // sprawdza czy nie ma get - wtedy ma to pierwszenstwo
                if ( !isset($_GET[$klucz]) ) {
                    //
                    if ( $_POST['zakres'] == 'filtry' || ($_POST['zakres'] != 'filtry' && $klucz == 'sort') ) {             
                         $_GET[$klucz] = $wartosc;
                    }
                    //
                }
                //
             }
             //
        }    
        
        // zamienia ' na \' do zapytan sql
        foreach ( $_GET as $klucz => $wartosc ) {
            //
            // sprawdza czy jest wylaczone magic_quotes_gpc
            $_GET[$klucz] = str_replace("'", "\'", (string)$wartosc);
            //
        }
    
        include('produkty_magazyn/produkty_magazyn_filtry.php');            
        //
        
        $zapytanie = 'SELECT DISTINCT
                             p.products_id, 
                             p.products_quantity, 
                             p.products_quantity_alarm,
                             p.products_quantity_max_alarm,
                             p.products_image, 
                             p.products_model, 
                             p.products_man_code,
                             p.products_status,
                             pd.products_id, 
                             pd.language_id, 
                             pd.products_name,
                             (select sum(products_quantity) as ilosc from orders_products where products_id = p.products_id) as ilosc_sprzedanych
                      FROM products p
                             '.((isset($_GET['kategoria_id'])) ? 'LEFT JOIN products_to_categories pc ON pc.products_id = p.products_id' : '').'                              
                             LEFT JOIN products_description pd ON pd.products_id = p.products_id
                             AND pd.language_id = "1"' . 
                             (( (isset($_GET['dodatkowe_opcje']) && $_GET['dodatkowe_opcje'] == 'cechy') || isset($_GET['cecha_nazwa']) ) ? 'RIGHT JOIN products_attributes pa ON p.products_id = pa.products_id' . $warunki_cech : '') . $warunki_szukania;
                             
        // sortowanie
        include('produkty_magazyn/produkty_magazyn_sortowanie.php');  

        $sql = $db->open_query($zapytanie);
        
        //
        if ((int)$db->ile_rekordow($sql) > 0) {
        
            if ( $_POST['format'] == 'csv' ) {
        
                $ciag_do_zapisu = 'Numer katalogowy;Nazwa produktu;Status produktu;Calkowita sprzedaz;Stan magazynowy;Alarm magazynowy;Maksymalny stan magazynowy' . "\n";
                
                while ($info = $sql->fetch_assoc()) {

                    $ciag_do_zapisu .= $info['products_model'] . ';';
                    $ciag_do_zapisu .= $info['products_name'] . ';';
                    $ciag_do_zapisu .= (($info['products_status'] == 1) ? 'tak' : 'nie') . ';';
                    $ciag_do_zapisu .= $info['ilosc_sprzedanych'] . ';';
                    $ciag_do_zapisu .= $info['products_quantity'] . ';';
                    $ciag_do_zapisu .= (($info['products_quantity_alarm'] > 0) ? $info['products_quantity_alarm'] : '-') . ';';
                    $ciag_do_zapisu .= (($info['products_quantity_max_alarm'] > 0) ? $info['products_quantity_max_alarm'] : '-') . ';';
                    
                    $ciag_do_zapisu = substr((string)$ciag_do_zapisu, 0, -1) . "\n";

                }
                
                //
                $db->close_query($sql);
                unset($info);      

                header("Content-Type: application/force-download\n");
                header("Cache-Control: cache, must-revalidate");   
                header("Pragma: public");
                header("Content-Disposition: attachment; filename=eksport_magazynu_produktow_" . date("d-m-Y") . ".csv");
                print $ciag_do_zapisu;
                exit;   
                
            }
            
            if ( $_POST['format'] == 'html' ) {
            
                $ciag_do_zapisu = '<!DOCTYPE HTML>
                                   <html lang="pl">
                                   <head>
                                      <meta charset="utf-8" />
                                      <style>
                                          body { font-size:12px; font-family: Arial, Tahoma, Verdana, sans-serif; font-weight:normal }  
                                          table { border-collapse: collapse; border-spacing:0; }
                                          table td { padding:1px 4px 1px 4px; border:1px solid #cccccc; }
                                          .naglowek td { background:#e6e6e6; padding:5px; font-weight:bold; }
                                      </style>
                                   </head>
                                   <body>';              
        
                $ciag_do_zapisu .= '<table>';
                
                $ciag_do_zapisu .= '<tr class="naglowek">
                                      <td>Zdjęcie produktu</td>
                                      <td>Numer katalogowy</td>
                                      <td>Nazwa produktu</td>
                                      <td style="text-align:center">Status produktu</td>
                                      <td style="text-align:center">Całkowita sprzedaż</td>
                                      <td style="text-align:center">Stan magazynowy</td>
                                      <td style="text-align:center">Alarm magazynowy</td>
                                      <td style="text-align:center">Maksymalny stan magazynowy</td>
                                   </tr>' . "\n";
                
                while ($info = $sql->fetch_assoc()) {
                
                    $ciag_do_zapisu .= '<tr>';

                    $ciag_do_zapisu .= '<td style="text-align:center">' . str_replace('src="', 'src="' . ADRES_URL_SKLEPU, Funkcje::pokazObrazek($info['products_image'], '', '40', '40')) . '</td>';
                    $ciag_do_zapisu .= '<td>' . $info['products_model'] . '</td>';
                    $ciag_do_zapisu .= '<td>' . $info['products_name'] . '</td>';
                    $ciag_do_zapisu .= '<td style="text-align:center">' . (($info['products_status'] == 1) ? 'tak' : 'nie') . '</td>';
                    $ciag_do_zapisu .= '<td style="text-align:center">' . $info['ilosc_sprzedanych'] . '</td>';
                    $ciag_do_zapisu .= '<td style="text-align:center">' . $info['products_quantity'] . '</td>' . "\n";
                    $ciag_do_zapisu .= '<td style="text-align:center">' . (($info['products_quantity_alarm'] > 0) ? $info['products_quantity_alarm'] : '-') . '</td>' . "\n";
                    $ciag_do_zapisu .= '<td style="text-align:center">' . (($info['products_quantity_max_alarm'] > 0) ? $info['products_quantity_max_alarm'] : '-') . '</td>' . "\n";
                    
                    $ciag_do_zapisu .= '</tr>';

                }
                
                $ciag_do_zapisu .= '</table>';
                
                $ciag_do_zapisu .= '<script>window.print()</script>';
                
                $ciag_do_zapisu .= '</body></html>';
                
                //
                $db->close_query($sql);
                unset($info);      

                header("Content-Type: application/force-download\n");
                header("Cache-Control: cache, must-revalidate");   
                header("Pragma: public");
                header("Content-Disposition: attachment; filename=eksport_magazynu_produktow_" . date("d-m-Y") . ".html");
                print $ciag_do_zapisu;
                exit;   
                
            }            
            
        } else {
        
            $czy_jest_blad = true;
        
        }
        
        $db->close_query($sql);        

        //Funkcje::PrzekierowanieURL('kupony.php');
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Eksport danych</div>
    <div id="cont">
    
        <form action="produkty_magazyn/produkty_magazyn_export.php" method="post" id="magazynForm" class="cmxform">          

        <div class="poleForm">
          <div class="naglowek">Eksport danych</div>
          
          <div class="pozycja_edytowana">
              
              <div class="info_content">
          
              <input type="hidden" name="akcja" value="zapisz" />
              
              <div class="NaglowekExport">Wybierz parametry eksportowanych danych</div>
          
              <p>
                <label>Format danych wynikowych:</label>
                <input type="radio" name="format" id="format_csv" value="csv" checked="checked" /><label class="OpisFor" for="format_csv">format CSV</label>
                <input type="radio" name="format" id="format_html" value="html" /> <label class="OpisFor" for="format_html">format HTML</label>
              </p>   

              <p>
                <label>Zakres eksportu:</label>
                <input type="radio" name="zakres" id="zakres_wszystkie" value="wszystkie" checked="checked" /> <label class="OpisFor" for="zakres_wszystkie">wszystkie produkty</label>
                <input type="radio" name="zakres" id="zakres_filtry" value="filtry" /> <label class="OpisFor" for="zakres_filtry">wg filtrów ustawionych w listingu magazynu produktów</label>
              </p>

              </div>
           
          </div>

          <div class="przyciski_dolne">
            <input type="submit" class="przyciskNon" value="Wygeneruj dane" />
            <button type="button" class="przyciskNon" onclick="cofnij('produkty_magazyn','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','produkty_magazyn');">Powrót</button>           
          </div>                 


        </div>                      
        </form>

    </div>    
    
    <?php
    if ($czy_jest_blad == true) {
        //
        echo Okienka::pokazOkno('Błąd generowania','Nie wygenerowano pliku - brak danych wynikowych');
        //
    }
    
    include('stopka.inc.php');

}