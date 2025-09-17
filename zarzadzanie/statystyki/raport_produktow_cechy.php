<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Raporty</div>
    <div id="cont">

          <div class="poleForm">
            <div class="naglowek">Raport sprzedaży dla produktu w określonym przedziale czasowym w podziale na poszczególne cechy</div>

                <div class="pozycja_edytowana">  

                    <span class="maleInfo">Raport prezentuje sprzedaż określonym przedziale czasowym w podziale na poszczególne cechy</span>
                    
                    <?php
                    Listing::postGet(basename($_SERVER['SCRIPT_NAME']));
                    ?>                     
                    
                    <form action="statystyki/raport_produktow_cechy.php" method="post" id="statForm" class="cmxform">

                    <script>
                    $(document).ready(function() {
                      $('input.datepicker').Zebra_DatePicker({
                        format: 'd-m-Y',
                        inside: false,
                        direction: false,
                        readonly_element: true
                      });                
                    });
                    
                    function funkcja_produktu(id) {
                        $('#statForm').submit();
                    }
                    </script>          
                    
                    <?php
                    if (isset($_GET['id_produkt']) && (int)$_GET['id_produkt'] > 0) {
                        echo '<input type="hidden" name="id_produkt" value="' . (int)$_GET['id_produkt'] . '" />';
                    }
                    ?>                    

                    <div id="zakresDat" style="margin-top:10px">
                        <span>Przedział czasowy wyników od:</span>
                        <input type="text" id="data_od" name="data_od" value="<?php echo ((isset($_GET['data_od'])) ? $filtr->process($_GET['data_od']) : ''); ?>" size="10" class="datepicker" />&nbsp;do&nbsp;
                        <input type="text" id="data_do" name="data_do" value="<?php echo ((isset($_GET['data_do'])) ? $filtr->process($_GET['data_do']) : ''); ?>" size="10" class="datepicker" />

                        <span style="margin-left:20px">Status:</span>
                        <?php
                        $tablia_status= Array();
                        $tablia_status = Sprzedaz::ListaStatusowZamowien(true);
                        echo Funkcje::RozwijaneMenu('szukaj_status', $tablia_status, ((isset($_GET['szukaj_status'])) ? $filtr->process($_GET['szukaj_status']) : ''), ' style="width:170px"'); ?>
                    </div>                     

                    <div class="wyszukaj_przycisk"><input type="image" alt="Szukaj" src="obrazki/ok.png" /></div>
                    
                    <?php
                    if ( Listing::wylaczFiltr(basename($_SERVER['SCRIPT_NAME'])) == true ) {
                      echo '<div id="wyszukaj_ikona"><a href="statystyki/raport_produktow_cechy.php?filtr=nie"><img src="obrazki/reset_szukaj.png" alt="Anuluj wyszukiwanie" /></a></div>';
                    }
                    ?>                    

                    <div class="cl"></div>
                    
                    <?php
                    if (!isset($_GET['id_produkt'])) {
                    ?>
                    
                    <div class="WybieranieKategorii">

                        <div class="GlownyListing">

                            <div class="GlownyListingKategorieEdycja">
                        
                                <p style="font-weight:bold;margin-bottom:10px">
                                Wybierz kategorię z której chcesz wybrać <br />produkt do wyświetlania raportu
                                </p>                        
                            
                                <div id="drzewo" style="margin:10px">
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
                                                <td class="lfp"><input type="radio" onclick="podkat_produkty(this.value)" value="'.$tablica_kat[$w]['id'].'" name="id_kat" id="id_kat_'.$tablica_kat[$w]['id'].'" /> <label class="OpisFor" for="id_kat_'.$tablica_kat[$w]['id'].'">'.$tablica_kat[$w]['text'].(($tablica_kat[$w]['status'] == 0) ? '<em class="TipChmurka"><b>Kategoria jest nieaktywna</b><span class="wylKat"></span></em>' : '').'</label></td>
                                                <td class="rgp" '.(($podkategorie) ? 'id="img_'.$tablica_kat[$w]['id'].'"' : '').'>'.(($podkategorie) ? '<img src="obrazki/rozwin.png" alt="Rozwiń" onclick="podkat(\''.$tablica_kat[$w]['id'].'\',\'\',\'radio\')" />' : '').'</td>
                                              </tr>
                                              '.(($podkategorie) ? '<tr><td colspan="2"><div id="p_'.$tablica_kat[$w]['id'].'"></div></td></tr>' : '').'';
                                    }
                                    echo '</table>';
                                    unset($tablica_kat,$podkategorie);   
                                    ?>            
                                </div>  

                            </div>
                            
                            <div style="GlownyListingProduktyEdycja">  
                                
                                <input type="hidden" id="rodzaj_modulu" value="raport_produkty" />
                                <div id="wynik_produktow_raport_produkty" class="WynikProduktowStatystyka"></div>                     
                                
                            </div>
                            
                        </div>
                        
                    </div>

                    <br />
                    
                    <?php } ?>
                    
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
                    
                    if ( isset($_GET['szukaj_status']) && (int)$_GET['szukaj_status'] > 0 ) {
                        $warunki_szukania .= " and o.orders_status = " . (int)$_GET['szukaj_status'] . " ";
                    }                    
                    
                    if (isset($_GET['id_produkt']) && (int)$_GET['id_produkt'] > 0) {

                        $Wynik = '<div class="RamkaStatystyki" style="margin-top:5px"><table class="TabelaStatystyki">';
                        
                        // szukanie danych o produkcie, zdjecie, nazwa
                        $zapytanie = 'SELECT DISTINCT
                                             p.products_id, 
                                             p.products_image,
                                             p.products_model,     
                                             pd.language_id, 
                                             pd.products_name
                                      FROM products p, products_description pd
                                      WHERE pd.products_id = p.products_id AND pd.language_id = "' . (int)$_SESSION['domyslny_jezyk']['id'] . '" AND p.products_id = "'.(int)$_GET['id_produkt'].'"';   
                        
                        $sql = $db->open_query($zapytanie);
                        $info = $sql->fetch_assoc();
                        
                        $Wynik .= '<tr class="NazwaNaglowek">';
                        $Wynik .= '<td class="statZdjecie"><div>'.Funkcje::pokazObrazek($info['products_image'], $info['products_name'], '50', '50').'</div></td>';
                        $Wynik .= '<td class="nazwa"><span>' . $info['products_name'] . '</span></td>';
                        $Wynik .= '<td class="wybierzInny" colspan="3"><a href="statystyki/raport_produktow_cechy.php?filtr=nie">wybierz inny produkt</a></td>';
                        $Wynik .= '</tr>';      
                  
                        unset($zapytanie);
                        $db->close_query($sql);                                      
                        
                        //                    
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
                                             IF ((SELECT distinct orders_id FROM orders_products_attributes WHERE orders_products_id = op.orders_products_id limit 1), GROUP_CONCAT(DISTINCT ap.products_options ,'#', ap.products_options_values ORDER BY ap.products_options, ap.products_options_values SEPARATOR '|' ), '') as cechy
                                        FROM orders o, 
                                             orders_products op, 
                                             orders_products_attributes ap
                                       WHERE o.orders_id = op.orders_id AND 
                                             op.products_id = '" . (int)$_GET['id_produkt'] . "' AND
                                             IF ((SELECT distinct orders_id FROM orders_products_attributes WHERE orders_products_id = op.orders_products_id limit 1), o.orders_id = ap.orders_id AND op.orders_products_id = ap.orders_products_id, o.orders_id = o.orders_id)
                                             " . $warunki_szukania . "
                                    GROUP BY op.orders_products_id ORDER BY TRIM(op.products_name), cechy";
        
                        $sql = $db->open_query($zapytanie);

                        if ((int)$db->ile_rekordow($sql) > 0) {
                        
                            $Wynik .= '<tr class="TyNaglowek">';
                            $Wynik .= '<td>Nr katalogowy</td>';
                            $Wynik .= '<td>Nazwa produktu</td>';
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
                                                            'nazwa' => trim((string)$info['products_name']),
                                                            'cechy' => $info['cechy'],
                                                            'ilosc' => $info['ilosc'],
                                                            'wartosc_netto' => $info['wartosc_netto'] * $info['ilosc'],
                                                            'wartosc_brutto' => $info['wartosc_brutto'] * $info['ilosc'],
                                                            'waluta' => $info['currency'],
                                                            'przelicznik' => $info['currency_value'],
                                                            'nazwa_cecha' => trim((string)$info['products_name']) . $info['cechy']);
                                //
                            }
                            
                            // usuwanie duplikatow
                            $ProduktyBezDuplikatow = Statystyki::UsunDuplikaty($ProduktyDuplikat, ((isset($_GET['typ']) && $_GET['typ'] == 'data_zamowienia') ? 'data' : ''));
                            // tworzenie tablicy koncowej z produktami bez duplkatow - dupliaty polaczne i zsumowane
                            $ProduktyKoncowe = Statystyki::TablicaKoncowa($ProduktyBezDuplikatow, $ProduktyDuplikat);

                            unset($ProduktyDuplikat, $ProduktyBezDuplikatow);
                            
                            // zeby wylistowac wszystkie produkty (z duplikatami) 
                            // foreach ($ProduktyDuplikat as $Produkt) {
                            
                            $PoprzedniaWartosc = '';
                            
                            foreach ($ProduktyKoncowe as $Produkt) {
                            
                                $Wynik .= '<tr>';
                                $Wynik .= '<td class="nrKat">' . $Produkt['model'] . '</td>';
                                $Wynik .= '<td class="linkProd"><a href="produkty/produkty_edytuj.php?id_poz=' . $Produkt['id'] . '">' . $Produkt['nazwa'] . Statystyki::PodzielCechy($Produkt['cechy']).'</a></td>';
                                $Wynik .= '<td class="inne">' . $Produkt['ilosc'] . '</td>';
                                $Wynik .= '<td class="walutaZam">' . $waluty->FormatujCene($Produkt['wartosc_netto'], false, $Produkt['waluta']) . '</td>';
                                $Wynik .= '<td class="walutaZam">' . $waluty->FormatujCene($Produkt['wartosc_brutto'], false, $Produkt['waluta']) . '</td>';                                
                                $Wynik .= '</tr>';
                                
                                if (isset($_GET['typ'])) {
                                    $PoprzedniaWartosc = $Produkt['data_zamowienia'];
                                  } else {
                                    $PoprzedniaWartosc = $Produkt['nazwa'];
                                }
                                
                            }                        

                            unset($info, $TrescZam);

                          } else {
                          
                            $Wynik .= '<tr><td style="padding:20px; border:0px; padding-left:10px;" colspan="7">Brak wyników ...</td></tr>';
                         
                        }
                              
                        unset($zapytanie);
                        $db->close_query($sql);

                        $Wynik .= '</table></div>';   
                    
                        echo $Wynik;
                        unset($Wynik);

                    }
                    ?>

                </div>

          </div>                      

    </div>    
    
    <?php
    include('stopka.inc.php');

}