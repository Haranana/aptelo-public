<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

// wyswietla tylko nowe zamowienia
if ( isset($_GET['nowe']) ) {
     //
     unset($_SESSION['filtry']['zamowienia_kontakt.php']);
     //
     $_SESSION['filtry']['zamowienia_kontakt.php']['szukaj_status'] = Sprzedaz::PokazDomyslnyStatusZamowienia();
     //
     Funkcje::PrzekierowanieURL('zamowienia_kontakt.php');
}

if ($prot->wyswietlStrone) {

    Listing::postGet(basename($_SERVER['SCRIPT_NAME']));

    $warunki_szukania = '';
    // jezeli jest szukanie
    if (isset($_GET['szukaj']) && $_GET['szukaj'] != '' ) {
        $szukana_wartosc = $filtr->process($_GET['szukaj']);
        $warunki_szukania = " and (customers_name LIKE '%".$szukana_wartosc."%' OR customers_telephone LIKE '%".$szukana_wartosc."%' OR customers_email_address LIKE '%".$szukana_wartosc."%')";
        unset($szukana_wartosc);
    }
    
    if ( isset($_GET['szukaj_data_zamowienia_od']) && $_GET['szukaj_data_zamowienia_od'] != '' ) {
        $szukana_wartosc = date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_GET['szukaj_data_zamowienia_od'] . ' 00:00:00')));
        $warunki_szukania .= " and date_purchased >= '".$szukana_wartosc."'";
        unset($szukana_wartosc);
    }

    if ( isset($_GET['szukaj_data_zamowienia_do']) && $_GET['szukaj_data_zamowienia_do'] != '' ) {
        $szukana_wartosc = date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_GET['szukaj_data_zamowienia_do'] . ' 23:59:59')));
        $warunki_szukania .= " and date_purchased <= '".$szukana_wartosc."'";
        unset($szukana_wartosc);
    }
    
    if ( isset($_GET['szukaj_status']) && $_GET['szukaj_status'] != '0' ) {
        $szukana_wartosc = $filtr->process($_GET['szukaj_status']);
        $warunki_szukania .= " and orders_fast_status = '".$szukana_wartosc."'";
        unset($szukana_wartosc);
    }    

    if ( isset($_GET['szukaj_numer']) && (int)$_GET['szukaj_numer'] > 0 ) {
        $szukana_wartosc = (int)$_GET['szukaj_numer'];
        $warunki_szukania .= " and orders_fast_id = '".$szukana_wartosc."'";
        unset($szukana_wartosc);
    }

    if ( isset($_GET['szukaj_wartosc_zamowienia_od']) && (float)$_GET['szukaj_wartosc_zamowienia_od'] > 0 ) {
        $szukana_wartosc = (float)$_GET['szukaj_wartosc_zamowienia_od'];
        $warunki_szukania .= " and products_final_price_tax >= '".$szukana_wartosc."'";
        unset($szukana_wartosc);
    }  

    if ( isset($_GET['szukaj_wartosc_zamowienia_do']) && (float)$_GET['szukaj_wartosc_zamowienia_do'] > 0 ) {
        $szukana_wartosc = (float)$_GET['szukaj_wartosc_zamowienia_do'];
        $warunki_szukania .= " and products_final_price_tax <= '".$szukana_wartosc."'";
        unset($szukana_wartosc);
    }    

    if ( $warunki_szukania != '' ) {
      $warunki_szukania = preg_replace('/and/i', 'WHERE', $warunki_szukania, 1);
    }

    $zapytanie = "SELECT * FROM orders_fast " . $warunki_szukania;

    $sql = $db->open_query($zapytanie);

    // tworzenie paska do nastepnych okien - obliczanie ile bedzie podstron
    $ile_pozycji = (int)$db->ile_rekordow($sql); // ile jest wszystkich produktow
    $ile_licznika = ($ile_pozycji / ILOSC_WYNIKOW_NA_STRONIE);
    if ($ile_licznika == (int)$ile_licznika) { $ile_licznika = (int)$ile_licznika; } else { $ile_licznika = (int)$ile_licznika+1; }

    $db->close_query($sql);
         
    // jezeli jest sortowanie
    if (isset($_GET['sort'])) {
        switch ($_GET['sort']) {
            case "sort_a1":
                $sortowanie = 'orders_fast_id desc';
                break;
            case "sort_a2":
                $sortowanie = 'orders_fast_id asc';
                break;                 
            case "sort_a3":
                $sortowanie = 'date_purchased desc';
                break;
            case "sort_a4":
                $sortowanie = 'date_purchased asc';
                break;                       
        }            
    } else { $sortowanie = 'orders_fast_id desc'; }    
    
    $zapytanie .= " ORDER BY ".$sortowanie;    
    
    // ******************************************************************************************************************************************************************
    // obsluga listingu AJAX
    if (isset($_GET['parametr'])) {

        if ($ile_pozycji > 0) {

            $zapytanie .= " limit ".$_GET['parametr'];    

            $sql = $db->open_query($zapytanie);

            $listing_danych = new Listing();
            
            $tablica_naglowek = array(array('ID', 'center'),   
                                      array('Info', 'center', '', 'class="ListingSchowajMobile"'),
                                      array('Produkt'),
                                      array('Cena produktu', 'center'),            
                                      array('Data zamówienia', 'center'),                                      
                                      array('Klient'),        
                                      array('Status', 'center'),
                                      array('Komentarz klienta', 'center', '', 'class="ListingSchowajMobile"'));

            echo $listing_danych->naglowek($tablica_naglowek);
            
            $tekst = '';
            while ($info = $sql->fetch_assoc()) {
            
                  if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $info['orders_fast_id']) {
                     $tekst .= '<tr class="pozycja_on" id="sk_'.$info['orders_fast_id'].'">';
                   } else {
                     $tekst .= '<tr class="pozycja_off" id="sk_'.$info['orders_fast_id'].'">';
                  }        

                  $tablica = array();
                  
                  $tablica[] = array($info['orders_fast_id'],'center');
                  
                  $zapytanie_produkt = "SELECT p.products_id, p.products_image, pd.products_name
                                          FROM products p, products_description pd
                                         WHERE p.products_id = '" . $info['orders_fast_products_id'] . "' AND p.products_id = pd.products_id AND pd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'";
                                         
                  $sql_produkt = $db->open_query($zapytanie_produkt);
                  $produkt = $sql_produkt->fetch_assoc(); 
                  
                  $tablica[] = array( '<div id="produkt' . rand(1,999) . '_' . $produkt['products_id'] . '" class="ZamowieniaKontakt zmzoom_produkt"><div class="podglad_zoom"></div><img src="obrazki/info_duze.png" alt="Szczegóły" /></div>', 'center', '', 'class="ListingSchowajMobile"'); 

                  // czy produkt ma cechy
                  $JakieCechy = '';
                  if ( $info['orders_fast_products_stock_attributes'] != '' ) {
                    
                      $CechaPrd = Produkty::CechyProduktuPoId( $info['orders_fast_products_id'] . 'x' . str_replace(',', 'x', (string)$info['orders_fast_products_stock_attributes']) );                      
                      if (count($CechaPrd) > 0) {
                          //
                          for ($a = 0, $c = count($CechaPrd); $a < $c; $a++) {
                              $JakieCechy .= '<div class="MaleNrKatSzybkieZamowienie">' . $CechaPrd[$a]['nazwa_cechy'] . ': <b>' . $CechaPrd[$a]['wartosc_cechy'] . '</b></div>';
                          }
                          //
                      }                  

                  }
                  
                  $tablica[] = array(((isset($produkt['products_name'])) ? '<a class="LinkProduktu" target="_blank" href="produkty/produkty_edytuj.php?id_poz=' . $produkt['products_id'] . '">' . $produkt['products_name'] . '</a>' . $JakieCechy : 'Brak nazwy produktu ...'));

                  $db->close_query($sql_produkt);
                  unset($zapytanie_produkt, $JakieCechy, $CechaPrd);                   

                  $tablica[] = array( '<div class="cena">'.$waluty->FormatujCene($info['products_final_price_tax'], false, $info['orders_fast_currencies_id']).'</div>' ,'center');
                  
                  $tablica[] = array(date('d-m-Y H:i',FunkcjeWlasnePHP::my_strtotime($info['date_purchased'])),'center');

                  $wyswietlana_nazwa = $info['customers_name'];
                  // email
                  if (!empty($info['customers_email_address'])) {
                      $wyswietlana_nazwa .= '<span class="MalyMailSzybkieZamowienie">' . $info['customers_email_address'] . '</span>';
                  }
                  // telefon
                  if (!empty($info['customers_telephone'])) {
                      $wyswietlana_nazwa .= '<span class="MalyTelefonSzybkieZamowienie">' . $info['customers_telephone'] . '</span>';
                  }                  
                  
                  $tablica[] = array($wyswietlana_nazwa,'','line-height:17px');

                  $tablica[] = array(Sprzedaz::pokazNazweStatusuZamowienia($info['orders_fast_status'], $_SESSION['domyslny_jezyk']['id']),'center');
                  
                  // szuka pierwszego statusu zamowienia zeby zaktualizowac komentarz
                  $zapytanie_komentarz = "select comments from orders_fast_status_history where orders_fast_id = '" . $info['orders_fast_id'] . "' order by date_added asc limit 1";
                  $sql_komentarz = $db->open_query($zapytanie_komentarz);        
                  $info_komentarz = $sql_komentarz->fetch_assoc();
                  //
                  $tablica[] = array( $info_komentarz['comments'], '', '', 'class="ListingSchowajMobile"');   
                  //
                  $db->close_query($sql_komentarz);
                  unset($info_komentarz, $zapytanie_komentarz);  

                  // zmienne do przekazania
                  $zmienne_do_przekazania = '?id_poz='.(int)$info['orders_fast_id']; 
                  
                  $tekst .= $listing_danych->pozycje($tablica);
                  
                  $tekst .= '<td class="rg_right IkonyPionowo">';
                  
                  $tekst .= '<a class="TipChmurka" href="sprzedaz/zamowienia_kontakt_pdf.php'.$zmienne_do_przekazania.'"><b>Wydruk zamówienia</b><img src="obrazki/pdf_2.png" alt="Wydruk zamówienia" /></a>';
                  $tekst .= '<a class="TipChmurka" href="sprzedaz/zamowienia_kontakt_szczegoly.php'.$zmienne_do_przekazania.'"><b>Edytuj</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>';
                  $tekst .= '<a class="TipChmurka" href="sprzedaz/zamowienia_kontakt_usun.php'.$zmienne_do_przekazania.'"><b>Skasuj</b><img src="obrazki/kasuj.png" alt="Skasuj" /></a>';
                  $tekst .= '<a class="TipChmurka" href="sprzedaz/zamowienia_kontakt_wyslij_email.php'.$zmienne_do_przekazania.'"><b>Wyślij e-mail</b><img src="obrazki/wyslij_mail.png" alt="Wyślij e-mail z zamówieniem" /></a>';                  
                  
                  $tekst .= '</td></tr>';
                  
            } 
            $tekst .= '</table>';
            //
            echo $tekst;
            //
            $db->close_query($sql);
            unset($listing_danych,$tekst,$tablica,$tablica_naglowek);        

        }
    }  
    
    // ******************************************************************************************************************************************************************
    // wyswietlanie listingu
    if (!isset($_GET['parametr'])) { 

        // wczytanie naglowka HTML
        include('naglowek.inc.php');
        ?>
      
        <script>
        $(document).ready(function() {
            $('input.datepicker').Zebra_DatePicker({
              format: 'd-m-Y',
              inside: false,
              readonly_element: false
            });                
        });
        </script>          

        <div id="caly_listing">
        
            <div id="ajax"></div>
            
            <div id="naglowek_cont">Zamówienia do kontaktu</div>

            <div id="wyszukaj">
                <form action="sprzedaz/zamowienia_kontakt.php" method="post" id="zamowieniaForm" class="cmxform">

                <div id="wyszukaj_text">
                    <span>Wyszukaj:</span>
                    <input type="text" name="szukaj" id="szukaj" value="<?php echo ((isset($_GET['szukaj'])) ? Funkcje::formatujTekstInput($filtr->process($_GET['szukaj'])) : ''); ?>" size="40" />
                </div>  
                
                <div class="wyszukaj_select">
                    <span>Numer zamówienia:</span>
                    <input type="text" id="numer" name="szukaj_numer" value="<?php echo ((isset($_GET['szukaj_numer'])) ? $filtr->process($_GET['szukaj_numer']) : ''); ?>" size="5" />
                </div>  

                <div class="wyszukaj_select">
                    <span>Data złożenia:</span>
                    <input type="text" id="data_zamowienia_od" name="szukaj_data_zamowienia_od" value="<?php echo ((isset($_GET['szukaj_data_zamowienia_od'])) ? $filtr->process($_GET['szukaj_data_zamowienia_od']) : ''); ?>" size="10" class="datepicker" />&nbsp;do&nbsp;
                    <input type="text" id="data_zamowienia_do" name="szukaj_data_zamowienia_do" value="<?php echo ((isset($_GET['szukaj_data_zamowienia_do'])) ? $filtr->process($_GET['szukaj_data_zamowienia_do']) : ''); ?>" size="10" class="datepicker" />
                </div>  
                
                <div class="wyszukaj_select">
                    <span>Status:</span>
                    <?php
                    $tablia_status= Array();
                    $tablia_status = Sprzedaz::ListaStatusowZamowien(true);
                    echo Funkcje::RozwijaneMenu('szukaj_status', $tablia_status, ((isset($_GET['szukaj_status'])) ? $filtr->process($_GET['szukaj_status']) : ''), ' style="max-width:200px"'); ?>
                </div>                   

                <div class="wyszukaj_select">
                    <span>Wartość zamówienia:</span>
                    <input type="text" name="szukaj_wartosc_zamowienia_od" value="<?php echo ((isset($_GET['szukaj_wartosc_zamowienia_od'])) ? $filtr->process($_GET['szukaj_wartosc_zamowienia_od']) : ''); ?>" size="6" /> do
                    <input type="text" name="szukaj_wartosc_zamowienia_do" value="<?php echo ((isset($_GET['szukaj_wartosc_zamowienia_do'])) ? $filtr->process($_GET['szukaj_wartosc_zamowienia_do']) : ''); ?>" size="6" />
                </div> 

                <?php 
                // tworzy ukryte pola hidden do wyszukiwania - filtra  
                if (isset($_GET['sort'])) { 
                    echo '<div><input type="hidden" name="sort" value="'.$filtr->process($_GET['sort']).'" /></div>';
                }                
                ?>
                <div class="wyszukaj_przycisk"><input type="image" alt="Szukaj" src="obrazki/ok.png" /></div>
                </form>
                
                <?php
                if ( Listing::wylaczFiltr(basename($_SERVER['SCRIPT_NAME'])) == true ) {
                  echo '<div id="wyszukaj_ikona"><a href="sprzedaz/zamowienia_kontakt.php?filtr=nie"><img src="obrazki/reset_szukaj.png" alt="Anuluj wyszukiwanie" /></a></div>';
                }
                ?>                 
                
                <div style="clear:both"></div>
            </div>        

            <div id="sortowanie">
            
                <span>Sortowanie: </span>
                
                <a id="sort_a1" class="sortowanie" href="sprzedaz/zamowienia_kontakt.php?sort=sort_a1">numeru malejąco</a>
                <a id="sort_a2" class="sortowanie" href="sprzedaz/zamowienia_kontakt.php?sort=sort_a2">numeru rosnąco</a>
                <a id="sort_a3" class="sortowanie" href="sprzedaz/zamowienia_kontakt.php?sort=sort_a3">daty malejąco</a>
                <a id="sort_a4" class="sortowanie" href="sprzedaz/zamowienia_kontakt.php?sort=sort_a4">daty rosnąco</a>
            
            </div>             

            <div style="clear:both;"></div>               
        
            <div id="wynik_zapytania"></div>
            <div id="aktualna_pozycja">1</div>
            <div id="dolny_pasek_stron"></div>
            <div id="pokaz_ile_pozycji"></div>
            <div id="ile_rekordow"><?php echo $ile_pozycji; ?></div>
            
            <script>
            $("#wynik_zapytania").html('<div style="padding:10px">Trwa ładowanie danych ...</div>');
            var skocz = '<?php echo ((isset($_GET['id_poz']) && (int)$_GET['id_poz'] > 0) ? (int)$_GET['id_poz'] : ''); ?>';
            <?php Listing::pokazAjax('sprzedaz/zamowienia_kontakt.php', $zapytanie, $ile_licznika, $ile_pozycji, 'orders_fast_id'); ?>
            </script>             

        </div>
                    
        <?php include('stopka.inc.php'); ?>

    <?php }

}
?>
