<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {
  
    if ( isset($_GET['eksport']) ) {
    
         $zamowienia = 'eksport';
         Listing::postGet(basename($_SERVER['SCRIPT_NAME']));         
         include('raport_okresowy_export.php');
         
         Funkcje::PrzekierowanieURL('raport_okresowy.php');
    
    }
    
    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Raporty</div>
    <div id="cont">

          <div class="poleForm">
            <div class="naglowek">Raport sprzedaży produktów w określonym przedziale czasowym</div>

                <div class="pozycja_edytowana">  

                    <span class="maleInfo">Raport prezentuje sprzedaż produktów w określonym przedziale czasowym</span>
                    
                    <?php
                    Listing::postGet(basename($_SERVER['SCRIPT_NAME']));
                    ?>                    
                    
                    <form action="statystyki/raport_okresowy.php" method="post" id="statForm" class="cmxform">
                    
                    <?php
                    if (!isset($_GET['typ'])) {
                        $_GET['typ'] = 'nazwa';
                    }
                    //
                    echo '<input type="hidden" name="typ" value="' . $filtr->process($_GET['typ']) . '" />';
                    //  
                    ?>
                    
                    <?php
                    // przedzial poczatkowy czasu - zawsze miesiac wczesniej
                    $Dzien = '01';
                    $Miesiac = date('m');
                    $Rok = date('Y');
                    //
                    $Dzien_od = '01';
                    $Miesiac_od = (($Miesiac > 1) ? $Miesiac - 1 : '12');
                    //
                    if ( $Miesiac_od < 10 ) {
                         $Miesiac_od = '0' . $Miesiac_od;
                    }
                    //
                    $Rok_od = (($Miesiac > 1) ? date('Y') : date('Y') - 1);                        
                    //
                    $Data_poczatkowa = $Rok_od . "-" . $Miesiac_od . "-" . $Dzien_od;
                    $Data_koncowa = $Rok . "-" . $Miesiac . "-" . $Dzien;
                    // dla kalendarza w postaci dd-mm-rr
                    $Data_poczatkowa_kalendarz = $Dzien_od . "-" . $Miesiac_od . "-" . $Rok_od;
                    $Data_koncowa_kalendarz = $Dzien . "-" . $Miesiac . "-" . $Rok;             
                    //
                    unset($Dzien, $Miesiac, $Rok);
                    ?>                    
                    
                    <script>
                    $(document).ready(function() {
                      $('input.datepicker').Zebra_DatePicker({
                        format: 'd-m-Y',
                        inside: false,
                        direction: false,
                        readonly_element: true
                      });                
                    });
                    
                    function tryb_wyswietl(id) {
                        if (id == 0) {
                            $('#tabWynik').slideDown(); 
                        } else {
                            $('#tabWynik').css('display','none');  
                        }
                        for (x = 1; x < 3; x++) {
                            $('#tryb_'+x).css('display','none');                               
                        }
                        $('#tryb_'+id).slideDown();      
                    }                    
                    </script>                    
                    
                    <div id="zakresDat" style="margin-top:10px">
                        <span>Przedział czasowy wyników od:</span>
                        <input type="text" id="data_od" name="data_od" value="<?php echo ((isset($_GET['data_od'])) ? $filtr->process($_GET['data_od']) : $Data_poczatkowa_kalendarz); ?>" size="10" class="datepicker" />&nbsp;do&nbsp;
                        <input type="text" id="data_do" name="data_do" value="<?php echo ((isset($_GET['data_do'])) ? $filtr->process($_GET['data_do']) : $Data_koncowa_kalendarz); ?>" size="10" class="datepicker" />

                        <span style="margin-left:20px">Status:</span>
                        <?php
                        $tablia_status= Array();
                        $tablia_status = Sprzedaz::ListaStatusowZamowien(true);
                        echo Funkcje::RozwijaneMenu('szukaj_status', $tablia_status, ((isset($_GET['szukaj_status'])) ? $filtr->process($_GET['szukaj_status']) : ''), ' style="width:170px"'); ?>
                    </div>                     

                    <div class="wyszukaj_przycisk"><input type="image" alt="Szukaj" src="obrazki/ok.png" /></div>
                    
                    <?php
                    if ( Listing::wylaczFiltr(basename($_SERVER['SCRIPT_NAME'])) == true ) {
                      echo '<div id="wyszukaj_ikona"><a href="statystyki/raport_okresowy.php?filtr=nie"><img src="obrazki/reset_szukaj.png" alt="Anuluj wyszukiwanie" /></a></div>';
                    }
                    ?>
                    
                    <div class="cl"></div>
                    
                    <div style="padding:0 10px 10px 10px">

                    <?php if (!isset($_GET['kategoria_id']) && !isset($_GET['producent_id'])) { ?>
                    
                        <div class="WyszukajRadio">                           
                            <input type="radio" name="tryb" id="tryb_wszystkie" value="wszystkie" onclick="tryb_wyswietl(0)" checked="checked" /> <label class="OpisFor" for="tryb_wszystkie"><span>wszystkie produkty</span></label>
                        </div>                        
                        
                        <div class="WyszukajRadio">
                            <input type="radio" name="tryb" id="tryb_kat" value="kat" onclick="tryb_wyswietl(1)" /> <label class="OpisFor" for="tryb_kat"><span>tylko z wybranej kategorii</span></label>
                        </div>
                        
                        <div class="WyszukajRadio">
                            <input type="radio" name="tryb" id="tryb_prd" value="prd" onclick="tryb_wyswietl(2)" /> <label class="OpisFor" for="tryb_prd"><span>tylko producenta</span></label>
                        </div>   
                    
                    <?php } else {

                        if (isset($_GET['kategoria_id']) && (int)$_GET['kategoria_id'] > 0) { 
                        // pobieranie informacji o nazwie kategorii
                        $zapytanie_tmp = "select distinct categories_name from categories_description where categories_id = '" . (int)$_GET['kategoria_id'] . "' and language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'";
                        $sqls = $db->open_query($zapytanie_tmp);            
                        $infs = $sqls->fetch_assoc()
                        ?>
                        <div class="wyszukaj_select" style="margin-right:10px;margin-top:6px">
                            <span>Kategoria: &nbsp; </span>
                            <input type="text" name="tryb" style="width:250px" value="<?php echo $infs['categories_name']; ?>" disabled="disabled" />
                            <input type="hidden" name="kategoria_id" value="<?php echo (int)$_GET['kategoria_id']; ?>" />
                        </div>                        
                        <?php 
                        $db->close_query($sqls); 
                        unset($zapytanie_tmp, $infs);
                        } 
                        
                        if (isset($_GET['producent_id']) && (int)$_GET['producent_id'] > 0) {
                        // pobieranie informacji o nazwie producenta
                        $zapytanie_tmp = "select distinct manufacturers_name from manufacturers where manufacturers_id = '" . (int)$_GET['producent_id'] . "'";
                        $sqls = $db->open_query($zapytanie_tmp);            
                        $infs = $sqls->fetch_assoc()
                        ?>
                        <div class="wyszukaj_select" style="margin-right:10px;margin-top:6px">
                            <span>Producent: &nbsp; </span>
                            <input type="text" name="tryb" style="width:180px" value="<?php echo $infs['manufacturers_name']; ?>" disabled="disabled" />
                            <input type="hidden" name="producent_id" value="<?php echo (int)$_GET['producent_id']; ?>" />
                        </div>                        
                        <?php 
                        $db->close_query($sqls); 
                        unset($zapytanie_tmp, $infs);
                        }                         

                    }                    

                    ?>   
                    
                    <div class="cl"></div>
                    
                    </div>  

                    <div class="cl"></div>
                    
                    <div id="wybor">                 
                    <span>Format wyświetlania:</span>
                    <a class="sortowanie<?php echo ((isset($_GET['typ']) && $_GET['typ'] == 'nazwa') ? '_zaznaczone' : ''); ?>" href="statystyki/raport_okresowy.php?typ=nazwa">wg nazwy produktów</a>
                    <a class="sortowanie<?php echo ((isset($_GET['typ']) && $_GET['typ'] == 'data') ? '_zaznaczone' : ''); ?>" href="statystyki/raport_okresowy.php?typ=data">wg daty zamówień</a>
                    </div>                     

                    <div class="cl"></div>
                    
                    <div id="tryb_1" style="display:none">
                    
                        <div id="drzewo" style="margin-left:10px;max-width:650px;width:95%">
                            <?php
                            //
                            echo '<table class="pkc">';
                            //
                            $tablica_kat = Kategorie::DrzewoKategorii('0', '', '', '', false, true);
                            for ($w = 0, $c = count($tablica_kat); $w < $c; $w++) {
                                $podkategorie = false;
                                if ($tablica_kat[$w]['podkategorie'] == 'true') { $podkategorie = true; }
                                //
                                echo '<tr>
                                        <td class="lfp">
                                            <a href="statystyki/raport_okresowy.php?kategoria_id='.$tablica_kat[$w]['id'].'">'.$tablica_kat[$w]['text'].(($tablica_kat[$w]['status'] == 0) ? '<em class="TipChmurka"><b>Kategoria jest nieaktywna</b><span class="wylKat"></span></em>' : '').'</a>
                                        </td>
                                        <td class="rgp" '.(($podkategorie) ? 'id="img_'.$tablica_kat[$w]['id'].'"' : '').'>'.(($podkategorie) ? '<img src="obrazki/rozwin.png" alt="Rozwiń" onclick="podkat(\''.$tablica_kat[$w]['id'].'\',\'\',\'\',\'\',\'raport_okresowy\',\'statystyki\')" />' : '').'</td>
                                      </tr>
                                      '.(($podkategorie) ? '<tr><td colspan="3"><div id="p_'.$tablica_kat[$w]['id'].'"></div></td></tr>' : '').'';
                            }
                            echo '</table>';
                            unset($tablica_kat,$podkategorie);
                            ?> 
                        </div>  
                        
                    </div> 
                    
                    <div id="tryb_2" style="display:none">
                    
                        <div id="producent" class="WyborProducenta">
                            <?php
                            $Prd = Funkcje::TablicaProducenci();
                            for ($b = 0, $c = count($Prd); $b < $c; $b++) {
                                echo '<a href="statystyki/raport_okresowy.php?producent_id='.$Prd[$b]['id'].'">'.$Prd[$b]['text'].'</a>';
                            }
                            unset($Prd);
                            ?>
                        </div>
                        
                    </div>                    

                    </form>

                    <?php
                    //
                    $warunki_szukania = '';
                    if ( isset($_GET['data_od']) && $_GET['data_od'] != '' ) {
                        $szukana_wartosc = date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_GET['data_od'] . ' 00:00:00')));
                        $warunki_szukania .= " and date_purchased >= '".$szukana_wartosc."'";
                    }

                    if ( isset($_GET['data_do']) && $_GET['data_do'] != '' ) {
                        $szukana_wartosc = date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_GET['data_do'] . ' 23:59:59')));
                        $warunki_szukania .= " and date_purchased <= '".$szukana_wartosc."'";                     
                    }
                    
                    // jezeli nic nie wypelnione przyjmuje dziesiejsza date
                    if ($warunki_szukania == '') {
                        $warunki_szukania = " and date_purchased >= '". $Data_poczatkowa . " 00:00:00' and date_purchased <= '". $Data_koncowa . " 23:59:59'";
                    }
                    
                    if ( isset($_GET['szukaj_status']) && (int)$_GET['szukaj_status'] > 0 ) {
                        $warunki_szukania .= " and o.orders_status = " . (int)$_GET['szukaj_status'] . " ";
                    }                    
                    
                    if (isset($_GET['kategoria_id']) && (int)$_GET['kategoria_id'] > 0) {
                        $warunki_szukania .= " and ptc.categories_id = '" . (int)$_GET['kategoria_id'] . "'";
                    }                    
                    
                    if (isset($_GET['producent_id']) && (int)$_GET['producent_id'] > 0) {
                        $warunki_szukania .= " and p.manufacturers_id = '" . (int)$_GET['producent_id'] . "'";
                    }                         
                    
                    $Wynik = '<div id="tabWynik">';
                    
                    $Wynik .= '<div style="margin:15px 10px 15px 10px"><div class="rg">
                                  <a class="Export" href="statystyki/raport_okresowy.php?eksport' . ((isset($_GET['typ']) && $_GET['typ']) ? '&typ=' . $_GET['typ'] : '') . '">eksportuj dane do pliku</a>
                               </div>        

                               <div class="cl"></div></div>';

                    $Wynik .= '<div class="RamkaStatystyki"><table class="TabelaStatystyki">';

                    $zapytanie = "SELECT op.final_price_tax AS wartosc_brutto, 
                                         op.final_price AS wartosc_netto,
                                         o.date_purchased, 
                                         o.currency,
                                         o.currency_value,
                                         op.products_name, 
                                         op.products_id,
                                         op.products_quantity AS ilosc, 
                                         op.products_model, 
                                         op.orders_products_id,
                                         p.manufacturers_id,
                                         p.products_purchase_price,
                                         IF ((SELECT distinct orders_id FROM orders_products_attributes WHERE orders_products_id = op.orders_products_id limit 1), GROUP_CONCAT(DISTINCT ap.products_options ,'#', ap.products_options_values ORDER BY ap.products_options, ap.products_options_values SEPARATOR '|' ), '') as cechy
                                    FROM orders o 
                                    LEFT JOIN orders_products op ON o.orders_id = op.orders_id
                                    LEFT JOIN orders_products_attributes ap ON o.orders_id = ap.orders_id
                                    LEFT JOIN products_to_categories ptc ON ptc.products_id = op.products_id
                                    LEFT JOIN products p ON p.products_id = op.products_id
                                    WHERE o.orders_id = op.orders_id AND 
                                         IF ((SELECT distinct orders_id FROM orders_products_attributes WHERE orders_products_id = op.orders_products_id limit 1), op.orders_products_id = ap.orders_products_id, o.orders_id = o.orders_id)
                                         " . $warunki_szukania . "
                                GROUP BY op.orders_products_id ORDER BY " . ((isset($_GET['typ']) && $_GET['typ'] == 'data') ? 'o.date_purchased, ' : '') . "op.products_name, cechy";

                    $sql = $db->open_query($zapytanie);

                    if ((int)$db->ile_rekordow($sql) > 0) {

                        $WartoscNetto = 0;
                        $WartoscBrutto = 0;
                        $IloscProduktow = 0;
                        $Wynik .= '<tr class="TyNaglowek">';
                        $Wynik .= '<td>Nr katalogowy</td>';
                        $Wynik .= '<td>Nazwa produktu</td>';
                        $Wynik .= '<td style="text-align:center">Cena zakupu</td>';
                        $Wynik .= '<td style="text-align:center">Ilość sprzedanych</td>';
                        $Wynik .= '<td style="text-align:center">Wartość netto</td>';
                        $Wynik .= '<td style="text-align:center">Wartość brutto</td>';
                        $Wynik .= '</tr>';                      
                                     
                        // tworzenie tymczasowej tablicy do usuwania duplikatow
                        $ProduktyDuplikat = array();
                        while ($info = $sql->fetch_assoc()) {
                            //
                            $ProduktyDuplikat[] = array('id' => $info['products_id'],
                                                        'data_zamowienia' => date('d-m-Y',FunkcjeWlasnePHP::my_strtotime($info['date_purchased'])),
                                                        'model' => $info['products_model'],
                                                        'nazwa' => $info['products_name'],
                                                        'cechy' => $info['cechy'],
                                                        'ilosc' => $info['ilosc'],
                                                        'cena_zakupu' => $info['products_purchase_price'],
                                                        'wartosc_netto' => $info['wartosc_netto'] * $info['ilosc'],
                                                        'wartosc_brutto' => $info['wartosc_brutto'] * $info['ilosc'],
                                                        'waluta' => $info['currency'],
                                                        'przelicznik' => $info['currency_value'],
                                                        'nazwa_cecha' => trim((string)$info['products_name']) . trim((string)$info['cechy']));
                            //
                        }
                        
                        // usuwanie duplikatow
                        $ProduktyBezDuplikatow = Statystyki::UsunDuplikaty($ProduktyDuplikat, ((isset($_GET['typ']) && $_GET['typ'] == 'data') ? 'data' : ''));

                        // tworzenie tablicy koncowej z produktami bez duplkatow - dupliaty polaczne i zsumowane
                        if ( isset($_GET['typ']) && $_GET['typ'] == 'data' ) {
                            $Typ = 'data';
                        } else {
                            $Typ = '';
                        }
                        $ProduktyKoncowe = Statystyki::TablicaKoncowa($ProduktyBezDuplikatow, $ProduktyDuplikat, $Typ);

                        unset($ProduktyDuplikat, $ProduktyBezDuplikatow);
                        
                        // zeby wylistowac wszystkie produkty (z duplikatami) 
                        // foreach ($ProduktyDuplikat as $Produkt) {
                        
                        $PoprzedniaWartosc = '';
                        
                        foreach ($ProduktyKoncowe as $Produkt) {
                        
                            if ($PoprzedniaWartosc != ((isset($_GET['typ']) && $_GET['typ'] == 'data') ? $Produkt['data_zamowienia'] : $Produkt['nazwa'])) {
                                //
                                $Wynik .= '<tr class="NazwaNaglowek">';
                                $Wynik .= '<td colspan="6"><span>' . ((isset($_GET['typ']) && $_GET['typ'] == 'data') ? $Produkt['data_zamowienia'] : $Produkt['nazwa'])   . '</span></td>';
                                $Wynik .= '</tr>';                         
                                //
                            }
                            
                            $Wynik .= '<tr>';
                            $Wynik .= '<td class="nrKat">' . $Produkt['model'] . '</td>';
                            $Wynik .= '<td class="linkProd" style="width:50%"><a href="produkty/produkty_edytuj.php?id_poz=' . $Produkt['id'] . '">' . $Produkt['nazwa'] . Statystyki::PodzielCechy($Produkt['cechy']).'</a></td>';
                            $Wynik .= '<td class="walutaZam">' . (((float)$Produkt['cena_zakupu'] > 0) ? $waluty->FormatujCene($Produkt['cena_zakupu'], false, $_SESSION['domyslna_waluta']['kod']) : '') . '</td>';
                            $Wynik .= '<td class="inne">' . $Produkt['ilosc'] . '</td>';
                            $Wynik .= '<td class="walutaZam">' . $waluty->FormatujCene($Produkt['wartosc_netto'], false, $Produkt['waluta']) . '</td>';
                            $Wynik .= '<td class="walutaZam">' . $waluty->FormatujCene($Produkt['wartosc_brutto'], false, $Produkt['waluta']) . '</td>';                            
                            $Wynik .= '</tr>';
                            
                            if (isset($_GET['typ']) && $_GET['typ'] == 'data') {
                                $PoprzedniaWartosc = $Produkt['data_zamowienia'];
                              } else {
                                $PoprzedniaWartosc = $Produkt['nazwa'];
                            }
                            
                            $IloscProduktow += $Produkt['ilosc'];

                            if ( $_SESSION['domyslna_waluta']['kod'] == $Produkt['waluta'] ) {
                                $WartoscNetto = $WartoscNetto + $Produkt['wartosc_netto'];
                                $WartoscBrutto = $WartoscBrutto + $Produkt['wartosc_brutto'];
                            } else {
                                $WartoscNetto = $WartoscNetto + ( $Produkt['wartosc_netto'] / $Produkt['przelicznik'] );
                                $WartoscBrutto = $WartoscBrutto + ( $Produkt['wartosc_brutto'] / $Produkt['przelicznik'] );
                            }

                            
                        }                        
                        $Wynik .= '<tr class="TyNaglowek">';
                        $Wynik .= '<td colspan="3">Razem wartość sprzedaży w przeliczeniu na domyślną walutę</td>';
                        $Wynik .= '<td class="walutaZam" style="font-weight:bold;text-align:center">' . $IloscProduktow . '</td>';
                        $Wynik .= '<td class="walutaZam" style="font-weight:bold;">' . $waluty->FormatujCene($WartoscNetto, false,  $_SESSION['domyslna_waluta']['kod']) . '</td>';
                        $Wynik .= '<td class="walutaZam" style="font-weight:bold;">' . $waluty->FormatujCene($WartoscBrutto, false, $_SESSION['domyslna_waluta']['kod']) . '</td>';
                        $Wynik .= '</tr>';

                        unset($info, $TrescZam, $WartoscNetto, $WartoscBrutto);

                      } else {
                      
                        $Wynik .= '<tr><td style="padding:10px; border:0px;" colspan="8">Brak wyników ...</td></tr>';
                     
                    }
                          
                    unset($zapytanie);
                    $db->close_query($sql);

                    $Wynik .= '</table></div></div>';   
                    
                    echo $Wynik;
                    unset($Wynik);                    
                    ?>

                </div>

          </div>                      

    </div>    
    
    <?php
    include('stopka.inc.php');

}