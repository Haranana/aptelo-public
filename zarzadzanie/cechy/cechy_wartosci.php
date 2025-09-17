<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone && Sesje::TokenSpr()) {

    if (!isset($_GET['akcja']) && isset($_GET['id_cechy']) && (int)$_GET['id_cechy'] > 0) {

        $zapytanie_cechy = "select distinct po.products_options_values_name, 
                                            pop.products_options_id, 
                                            po.products_options_values_thumbnail, 
                                            po.products_options_values_id, 
                                            po.products_options_values_status, 
                                            pop.products_options_values_id, 
                                            pop.products_options_values_sort_order,
                                            po.global_options_values_price_tax,
                                            po.global_price_prefix,
                                            po.global_options_values_weight
                                       from products_options_values po, products_options_values_to_products_options pop where po.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."' and po.products_options_values_id = pop.products_options_values_id and pop.products_options_id = '".(int)($_GET['id_cechy'])."' order by pop.products_options_values_sort_order, po.products_options_values_name COLLATE utf8_polish_ci";
        $sqls = $db->open_query($zapytanie_cechy);
        
        if ((int)$db->ile_rekordow($sqls) > 0) {    
        ?>

            <div class="RamkaCech">

            <?php
            
            // typ cechy - procent czy wartosc
            $sqlq = $db->open_query('select products_options_value from products_options where products_options_id = "' . (int)$_GET['id_cechy'] . '"');
            $infq = $sqlq->fetch_assoc();
            $typ = $infq['products_options_value'];
            $db->close_query($sqlq);
            
            // informacje o produktach - zakres
            $listing_danych = new Listing();

            $tablica_naglowek = array(array('ID', 'center'),
                                      array('Wartość <br /> cechy'),
                                      array('Sort', 'center'),
                                      array('Zdjęcie', 'center'),
                                      array('Wartość <br /> (cena brutto / <br /> przelicznik)', 'center'),
                                      array('Prefix', 'center'),
                                      array('Waga', 'center'),
                                      array('Status', 'center'));
            echo $listing_danych->naglowek($tablica_naglowek);

            $tekst = '';
            while ($info = $sqls->fetch_assoc()) {

                  $tekst .= '<tr class="pozycja_off" onmouseover="this.className=\'pozycja_on\'" onmouseout="this.className=\'pozycja_off\'">';   

                  $tablica = array();
                  
                  $tablica[] = array($info['products_options_values_id'],'center');
                  
                  $tablica[] = array($info['products_options_values_name']);
                  
                  $tablica[] = array($info['products_options_values_sort_order'],'center');

                  $tgm = Funkcje::pokazObrazek($info['products_options_values_thumbnail'], $info['products_options_values_name'], '40', '40');
                  $tablica[] = array($tgm,'center');   
                  unset($tgm);
                  
                  if ( $typ == 'kwota' ) {
                       $tgm = (($info['global_options_values_price_tax'] > 0) ? $waluty->FormatujCene($info['global_options_values_price_tax'], false, $_SESSION['domyslna_waluta']['id']) : '-');
                  } else {
                       $tgm = (($info['global_options_values_price_tax'] > 0) ? $info['global_options_values_price_tax'] . ' %' : '-');
                  }
                  if ( $info['global_price_prefix'] == '*' ) {
                    $tgm = (($info['global_options_values_price_tax'] > 0) ? 'x * ' . $info['global_options_values_price_tax'] . '</div>' : '-');
                  }
                  $tablica[] = array($tgm, 'center');
                  unset($tgm);
                  
                  $tablica[] = array((($info['global_options_values_price_tax'] > 0) ? $info['global_price_prefix'] : ''),'center');
                  
                  $tablica[] = array((($info['global_options_values_weight'] > 0) ? $info['global_options_values_weight'] : '-'),'center');

                  if ($info['products_options_values_status'] == '1') {
                    $tablica[] = array('<img src="obrazki/aktywny_on.png" alt="Ta wartość cecha jest aktywna i wyświetlana w sklepie"', 'center');
                   } else {
                    $tablica[] = array('<img src="obrazki/aktywny_off.png" alt="Ta wartość cecha nie jest aktywna i nie wyświetlana w sklepie"', 'center');
                  }                     
                  
                  $tekst .= $listing_danych->pozycje($tablica);
                  
                  $tekst .= '<td class="rg_right">';
                  
                  // zmienne do przekazania
                  $zmienne_do_przekazania = '?id_poz='.(int)$info['products_options_values_id'] . Funkcje::Zwroc_Wybrane_Get(array('id_cechy'),true); 
                  
                  $tekst .= '<a class="TipChmurka" href="cechy/cechy_wartosci_edytuj.php'.$zmienne_do_przekazania.'"><b>Edytuj</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>';
                  $tekst .= '<a class="TipChmurka" href="cechy/cechy_wartosci_usun.php'.$zmienne_do_przekazania.'"><b>Skasuj</b><img src="obrazki/kasuj.png" alt="Skasuj" /></a>';
                  
                  $tekst .= '</td></tr>';
                  
            } 
            $tekst .= '</table>';
            //
            echo $tekst;
            //
            $db->close_query($sqls);
            unset($listing_danych,$tekst,$tablica,$tablica_naglowek); 

            echo '<script>$("#cechy_wartosci_dodawanie").css("display","block");</script>';        

            ?>
            
            </div>
            
            <?php
        
          } else {
          
            echo '<div id="komnik" class="KomunikatCechy"><span>Brak przypisanych wartości do cechy ...</span></div>
                 <script>$("#cechy_wartosci_dodawanie").css("display","block");</script>';
          
        }
    }
    
}
?>
