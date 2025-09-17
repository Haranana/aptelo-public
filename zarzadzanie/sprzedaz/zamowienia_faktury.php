<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    Listing::postGet(basename($_SERVER['SCRIPT_NAME']));

    $warunki_szukania = '';
    // jezeli jest szukanie
    if (isset($_GET['szukaj']) && $_GET['szukaj'] != '' ) {
        $szukana_wartosc = $filtr->process($_GET['szukaj']);
        $warunki_szukania = " AND invoices_nr = '".$szukana_wartosc."'";
    }
    
    if (isset($_GET['rozliczenie']) && $_GET['rozliczenie'] != '' ) {
        if ( (int)$_GET['rozliczenie'] == 1 ) {
             $warunki_szukania .= " AND invoices_payment_status = '1'";
        }
        if ( (int)$_GET['rozliczenie'] == 2 ) {
             $warunki_szukania .= " AND invoices_payment_status = '0'";
        } 
        if ( (int)$_GET['rozliczenie'] == 0 ) {
             $warunki_szukania .= " AND invoices_payment_status in (0,1)";
        }        
    }    

    if ( isset($_GET['szukaj_data_zamowienia_od']) && $_GET['szukaj_data_zamowienia_od'] != '' ) {
        $szukana_wartosc = date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_GET['szukaj_data_zamowienia_od'] . ' 00:00:00')));
        $warunki_szukania .= " AND invoices_date_generated >= '".$szukana_wartosc."'";
    }

    if ( isset($_GET['szukaj_data_zamowienia_do']) && $_GET['szukaj_data_zamowienia_do'] != '' ) {
        $szukana_wartosc = date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_GET['szukaj_data_zamowienia_do'] . ' 23:59:59')));
        $warunki_szukania .= " AND invoices_date_generated <= '".$szukana_wartosc."'";
    }
    
    if (isset($_GET['szukaj_klienta']) && $_GET['szukaj_klienta'] != '' ) {
        $szukana_wartosc = $filtr->process($_GET['szukaj_klienta']);
        $warunki_szukania .= " AND CONCAT(invoices_billing_name, ' ',invoices_billing_company_name,invoices_billing_nip,invoices_billing_pesel,invoices_billing_street_address,invoices_billing_city,invoices_billing_postcode) LIKE '%".$szukana_wartosc."%'";
    }    

    $zapytanie = "SELECT *
    FROM invoices WHERE invoices_type = '2'
     " . $warunki_szukania;

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
                $sortowanie = 'invoices_date_generated desc, cast(invoices_nr as unsigned) desc';
                break;
            case "sort_a2":
                $sortowanie = 'invoices_date_generated asc, cast(invoices_nr as unsigned) asc';
                break;                 
            case "sort_a3":
                $sortowanie = 'invoices_date_generated desc';
                break;
            case "sort_a4":
                $sortowanie = 'invoices_date_generated asc';
                break;
            case "sort_a5":
                $sortowanie = 'orders_id desc';
                break;
            case "sort_a6":
                $sortowanie = 'orders_id asc';
                break;                  
        }            
    } else { $sortowanie = 'invoices_date_generated desc, cast(invoices_nr as unsigned) desc'; }    
    
    $zapytanie .= " ORDER BY ".$sortowanie;    

    // ******************************************************************************************************************************************************************
    // obsluga listingu AJAX
    if (isset($_GET['parametr'])) {

        if ($ile_pozycji > 0) {
        
            $zapytanie .= " limit ".$_GET['parametr'];  

            $sql = $db->open_query($zapytanie);

            $listing_danych = new Listing();
            
            $tablica_naglowek = array(array('Numer faktury', 'center'),
                                      array('Numer zamówienia', 'center'),
                                      array('Data zamówienia', 'center'),
                                      array('Wartość zamówienia', 'center'),                                      
                                      array('Data wystawienia', 'center'),
                                      array('Data płatności', 'center'),
                                      array('Klient', 'center', '', 'class="ListingRwd"'),
                                      array('Rozliczona', 'center'));
            echo $listing_danych->naglowek($tablica_naglowek);
            
            $tekst = '';
            while ($info = $sql->fetch_assoc()) {
            
                if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $info['invoices_id']) {
                   $tekst .= '<tr class="pozycja_on" id="sk_'.$info['invoices_id'].'">';
                 } else {
                   $tekst .= '<tr class="pozycja_off" id="sk_'.$info['invoices_id'].'">';
                }        

                $tablica = array();

                $tablica[] = array(NUMER_FAKTURY_PREFIX . str_pad($info['invoices_nr'], FAKTURA_NUMER_ZERA_WIODACE, 0, STR_PAD_LEFT) . FunkcjeWlasnePHP::my_strftime((string)NUMER_FAKTURY_SUFFIX, FunkcjeWlasnePHP::my_strtotime($info['invoices_date_generated'])),'center');

                $tablica[] = array($info['orders_id'],'center');
                
                $zamowienie = new Zamowienie($info['orders_id']);
                if ( isset($zamowienie->info['data_zamowienia']) ) {
                     $tablica[] = array(date('d-m-Y',FunkcjeWlasnePHP::my_strtotime( $zamowienie->info['data_zamowienia'] )),'center');
                } else {
                     $tablica[] = array('-','center');
                }
                if ( isset($zamowienie->info['wartosc_zamowienia']) ) {
                     $tablica[] = array($zamowienie->info['wartosc_zamowienia'],'center');
                } else {
                     $tablica[] = array('-','center');
                }  
                unset($zamowienie);                  
                
                $wyswietlana_nazwa = '';
                $kontakt = '';
                $status = '';

                if ( trim((string)$info['invoices_billing_company_name']) != '' ) {
                  $wyswietlana_nazwa .= '<span class="Firma">'.$info['invoices_billing_company_name'] . '</span><br />';
                } else {
                  $wyswietlana_nazwa .= $info['invoices_billing_name'] . '<br />';
                }
                
                $wyswietlana_nazwa .= $info['invoices_billing_street_address']. '<br />';
                $wyswietlana_nazwa .= $info['invoices_billing_postcode']. ' ' . $info['invoices_billing_city'] . '<br />';
                
                if ( trim((string)$info['invoices_billing_company_name']) != '' ) {
                  if ( trim((string)$info['invoices_billing_nip']) != '' ) {
                      $wyswietlana_nazwa .= 'NIP: ' . $info['invoices_billing_nip'];
                  }   
                 } else {
                  if ( trim((string)$info['invoices_billing_pesel']) != '' ) {
                      $wyswietlana_nazwa .= 'PESEL: ' . $info['invoices_billing_pesel'];
                  }   
                }

                $tablica[] = array(date('d-m-Y',FunkcjeWlasnePHP::my_strtotime($info['invoices_date_generated'])),'center');
                $tablica[] = array(date('d-m-Y',FunkcjeWlasnePHP::my_strtotime($info['invoices_date_payment'])),'center');

                $tablica[] = array($wyswietlana_nazwa, 'left', '', 'class="ListingRwd"');

                if ( $info['invoices_payment_status'] == '0' ) {
                    $status .= '<a class="TipChmurka" href="sprzedaz/zamowienia_faktury_rozliczenie.php?id_poz='.$info['invoices_id'].'"><b>Fatura nieopłacona</b><img src="obrazki/aktywny_off.png" alt="Fatura nieopłacona" /></a>';
                } else {
                    $status .= '<a class="TipChmurka" href="sprzedaz/zamowienia_faktury_rozliczenie.php?id_poz='.$info['invoices_id'].'"><b>Fatura opłacona</b><img src="obrazki/aktywny_on.png" alt="Fatura opłacona" /></a>';
                }
                $tablica[] = array($status,'center');

                $tekst .= $listing_danych->pozycje($tablica);

                // zmienne do przekazania
                $zmienne_do_przekazania = '?id_poz='.(int)$info['orders_id']; 
                
                $tekst .= '<td class="rg_right IkonyPionowo">';
                
                $tekst .= '<a class="TipChmurka" href="sprzedaz/zamowienia_faktura_pdf.php'.$zmienne_do_przekazania.'&amp;id='.$info['invoices_id'].'"><b>Wydrukuj fakturę VAT</b><img src="obrazki/pdf.png" alt="Wydrukuj fakturę VAT" /></a>';
                $tekst .= '<a class="TipChmurka" href="sprzedaz/zamowienia_szczegoly.php?id_poz='.$info['orders_id'].'"><b>Pokaż szczegóły zamówienia</b><img src="obrazki/lista_wojewodztw.png" alt="Pokaż szczegóły zamówienia" /></a>';
                $tekst .= '<a class="TipChmurka" href="sprzedaz/zamowienia_faktura_usun.php?id_poz='.(int)$info['invoices_id'].'"><b>Skasuj</b><img src="obrazki/kasuj.png" alt="Skasuj" /></a>';
                
                if ( !empty($info['fakturownia_id']) && INTEGRACJA_FAKTUROWNIA_WLACZONY == 'tak' ) {
                  
                     $tekst .= '<br /><a class="TipChmurka" href="sprzedaz/zamowienia_faktura_fakturownia.php?id_poz='.(int)$info['orders_id'].'&typ=2"><b>pobierz fakturę PDF z Fakturownia.pl</b><img src="obrazki/logo/fakturownia_ikona.png" alt="Fakturownia.pl" /></a>';
                     
                }
                
                $tekst .= '</td></tr>';

                $tekst .= '<tr class="pozycjaRwd"><td class="WynikRwd" colspan="10" id="rwd_sk_'.$info['invoices_id'].'"></td></tr>';
                  
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
            
            <div id="naglowek_cont">Zestawienie faktur</div>

            <div id="wyszukaj">
                <form action="sprzedaz/zamowienia_faktury.php" method="post" id="fakturyForm" class="cmxform">

                <div id="wyszukaj_text">
                    <span>Wyszukaj fakturę nr:</span>
                    <input type="text" name="szukaj" id="szukaj" value="<?php echo ((isset($_GET['szukaj'])) ? $filtr->process($_GET['szukaj']) : ''); ?>" size="10" />
                </div>  
                
                <div id="wyszukaj_text">
                    <span>Wyszukaj klienta:</span>
                    <input type="text" name="szukaj_klienta" id="szukaj_klienta" value="<?php echo ((isset($_GET['szukaj_klienta'])) ? Funkcje::formatujTekstInput($filtr->process($_GET['szukaj_klienta'])) : ''); ?>" size="30" />
                </div>                  
                
                <div class="wyszukaj_select">
                    <span>Rozliczona:</span>
                    <?php
                    $tablia_typ = Array();
                    $tablia_typ[] = array('id' => '0', 'text' => 'wszystkie');
                    $tablia_typ[] = array('id' => '1', 'text' => 'rozliczone');
                    $tablia_typ[] = array('id' => '2', 'text' => 'nierozliczone');
                    echo Funkcje::RozwijaneMenu('rozliczenie', $tablia_typ, ((isset($_GET['rozliczenie'])) ? $filtr->process($_GET['rozliczenie']) : '')); ?>
                </div>                
                
                <div class="wyszukaj_select">
                    <span>Data wystawienia:</span>
                    <input type="text" id="data_zamowienia_od" name="szukaj_data_zamowienia_od" value="<?php echo ((isset($_GET['szukaj_data_zamowienia_od'])) ? $filtr->process($_GET['szukaj_data_zamowienia_od']) : ''); ?>" size="10" class="datepicker" />&nbsp;do&nbsp;
                    <input type="text" id="data_zamowienia_do" name="szukaj_data_zamowienia_do" value="<?php echo ((isset($_GET['szukaj_data_zamowienia_do'])) ? $filtr->process($_GET['szukaj_data_zamowienia_do']) : ''); ?>" size="10" class="datepicker" />
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
                  echo '<div id="wyszukaj_ikona"><a href="sprzedaz/zamowienia_faktury.php?filtr=nie"><img src="obrazki/reset_szukaj.png" alt="Anuluj wyszukiwanie" /></a></div>';
                }
                ?>                 
                
                <div style="clear:both"></div>
                
            </div>
            
            <form action="sprzedaz/zamowienia_faktury_pdf.php" method="post" class="cmxform">

            <div id="sortowanie">
            
                <span>Sortowanie: </span>
                
                <a id="sort_a1" class="sortowanie" href="sprzedaz/zamowienia_faktury.php?sort=sort_a1">nr faktury malejąco</a>
                <a id="sort_a2" class="sortowanie" href="sprzedaz/zamowienia_faktury.php?sort=sort_a2">nr faktury rosnąco</a>
                <a id="sort_a3" class="sortowanie" href="sprzedaz/zamowienia_faktury.php?sort=sort_a3">data wystawienia malejąco</a>
                <a id="sort_a4" class="sortowanie" href="sprzedaz/zamowienia_faktury.php?sort=sort_a4">data wystawienia rosnąco</a>
                <a id="sort_a5" class="sortowanie" href="sprzedaz/zamowienia_faktury.php?sort=sort_a5">nr zamówienia malejąco</a>
                <a id="sort_a6" class="sortowanie" href="sprzedaz/zamowienia_faktury.php?sort=sort_a6">nr zamówienia rosnąco</a> 
            
            </div>             

            <div id="wynik_zapytania"></div>
            <div id="aktualna_pozycja">1</div>

            <div id="akcja">
            
                <div id="akc">
                
                  Drukuj zestawienie:
                  
                  <select name="data_wydruku_mc" id="data_wydruku_mc">
                      <option value="01" <?php echo ( date('m') == '01' ? 'selected="selected"' : ''); ?>>styczeń</option>
                      <option value="02" <?php echo ( date('m') == '02' ? 'selected="selected"' : ''); ?>>luty</option>
                      <option value="03" <?php echo ( date('m') == '03' ? 'selected="selected"' : ''); ?>>marzec</option>
                      <option value="04" <?php echo ( date('m') == '02' ? 'selected="selected"' : ''); ?>>kwiecień</option>
                      <option value="05" <?php echo ( date('m') == '05' ? 'selected="selected"' : ''); ?>>maj</option>
                      <option value="06" <?php echo ( date('m') == '06' ? 'selected="selected"' : ''); ?>>czerwiec</option>
                      <option value="07" <?php echo ( date('m') == '07' ? 'selected="selected"' : ''); ?>>lipiec</option>
                      <option value="08" <?php echo ( date('m') == '08' ? 'selected="selected"' : ''); ?>>sierpień</option>
                      <option value="09" <?php echo ( date('m') == '09' ? 'selected="selected"' : ''); ?>>wrzesień</option>
                      <option value="10" <?php echo ( date('m') == '10' ? 'selected="selected"' : ''); ?>>październik</option>
                      <option value="11" <?php echo ( date('m') == '11' ? 'selected="selected"' : ''); ?>>listopad</option>
                      <option value="12" <?php echo ( date('m') == '12' ? 'selected="selected"' : ''); ?>>grudzień</option>
                  </select> &nbsp;
                  
                  <select name="data_wydruku_rok" id="data_wydruku_rok">
                      <?php
                      for ( $r = 2005; $r < (int)date('Y') + 1; $r++ ) {
                            echo '<option value="' . $r . '" ' . ((date('Y') == $r) ? 'selected="selected"' : '') . '>' . $r . '</option>';
                      }
                      ?>
                  </select>
                  
                  &nbsp; &nbsp; lub zakres dat:
                  
                  <input type="text" name="data_od" value="" size="10" class="datepicker" /> &nbsp; do &nbsp;
                  <input type="text" name="data_do" value="" size="10" class="datepicker" />   

                  &nbsp; &nbsp; format zapisu:
                  
                  <select name="format">
                      <option value="pdf" selected="selected">PDF</option>
                      <option value="html">HTML</option>
                      <option value="csv">CSV</option>
                  </select>                  
                  
                </div>
                
                <div style="clear:both;"></div>
                          
            </div>
            
            <div id="page"></div>
            
            <div id="dolny_pasek_stron"></div>
            <div id="pokaz_ile_pozycji"></div>
            <div id="ile_rekordow"><?php echo $ile_pozycji; ?></div>

            <?php if ($ile_pozycji > 0) { ?>
              <div style="text-align:right" id="zapisz_zmiany"><input type="submit" class="przyciskBut" value="Wygeneruj zestawienie" /></div>
            <?php } ?>                

            </form>

            <script>
            $("#wynik_zapytania").html('<div style="padding:10px">Trwa ładowanie danych ...</div>');
            <?php Listing::pokazAjax('sprzedaz/zamowienia_faktury.php', $zapytanie, $ile_licznika, $ile_pozycji, 'invoices_id'); ?>
            </script>              

        </div>
        <?php include('stopka.inc.php'); ?>

    <?php }

}
?>
