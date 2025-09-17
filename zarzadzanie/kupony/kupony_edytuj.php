<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        $pola = array(
                array('coupons_description',$filtr->process($_POST["opis"])),
                array('coupons_discount_type',$filtr->process($_POST["rodzaj"])),    
                array('coupons_currency',$filtr->process($_POST["rodzaj_waluta"])),   
                array('coupons_min_order',(float)$_POST["wartosc"]),
                array('coupons_min_quantity',(int)$_POST["ilosc"]),
                array('coupons_max_order',(float)$_POST["wartosc_max"]),
                array('coupons_max_quantity',(int)$_POST["ilosc_max"]),                 
                array('coupons_specials',(int)$_POST["promocja"]),
                array('coupons_quantity',(int)$_POST["ilosc_kuponow"]),
                array('coupons_hidden',(int)$_POST["widoczny"]),
                array('coupons_customers_use',(int)$_POST["uzycie_kuponu"]),
                array('coupons_customers_first_purchase',(int)$_POST["pierwsze_zakupy"]),
                array('coupons_countries',((isset($_POST['kraj'])) ? implode(',', (array)$_POST['kraj']) : '')),
                array('coupons_customers_groups_id',((isset($_POST['grupa_klientow'])) ? implode(',', (array)$_POST['grupa_klientow']) : '')),
                array('coupons_customers_payment_id',((isset($_POST['modul_platnosci'])) ? implode(',', (array)$_POST['modul_platnosci']) : ''))
        );
        
        if ($filtr->process($_POST["rodzaj"]) == 'fixed') {
            $pola[] = array('coupons_discount_value',(float)$_POST["rabat_kwota"]);
        }
        
        if ($filtr->process($_POST["rodzaj"]) == 'percent') {
            $pola[] = array('coupons_discount_value',(float)$_POST["rabat_procent"]);
        }        
        
        if ($filtr->process($_POST["rodzaj"]) == 'shipping' && isset($_POST["id_wysylki"])) {
            $pola[] = array('coupons_modules_shipping',implode(',', (array)$filtr->process($_POST["id_wysylki"])));
        } else {
            $pola[] = array('coupons_modules_shipping','');
        }        
        
        if (!empty($_POST['data_od'])) {
            $pola[] = array('coupons_date_start',date('Y-m-d', FunkcjeWlasnePHP::my_strtotime($filtr->process($_POST['data_od']))));
          } else {
            $pola[] = array('coupons_date_start','0000-00-00');            
        }  

        if (!empty($_POST['data_do'])) {
            $pola[] = array('coupons_date_end',date('Y-m-d', FunkcjeWlasnePHP::my_strtotime($filtr->process($_POST['data_do']))));
          } else {
            $pola[] = array('coupons_date_end','0000-00-00');            
        }  
        
        // sprawdzi ile bylo wykorzystane kuponow - jezeli wiecej niz ilosc to wylaczy kupon
        $wykorzystanie = $db->open_query("select distinct count(*) as ile_wykorzystanych from coupons_to_orders where coupons_id = '".(int)$_POST["id"]."'");
        $ile_kuponow = $wykorzystanie->fetch_assoc();
        
        if ( (int)$ile_kuponow['ile_wykorzystanych'] > (int)$_POST["ilosc_kuponow"] ) {
             $pola[] = array('coupons_status','0');   
        } else {
             $pola[] = array('coupons_status','1');   
        }
        
        $db->close_query($wykorzystanie);
        unset($ile_kuponow);   

        $db->update_query('coupons' , $pola, " coupons_id = '".(int)$_POST["id"]."'");	
        unset($pola);
        
        if ( $filtr->process($_POST['rodzaj']) == 'fixed' ) {
             $_POST['rodzaj'] = 'kwota';
           } else {
             $_POST['rodzaj'] = 'procent';
        }
        if ( isset($_GET['rodzaj_opcja']) && ( $_GET['rodzaj_opcja'] != $filtr->process($_POST['rodzaj']) ) ) {
             unset($_GET);
        }    
        
        Funkcje::PrzekierowanieURL('kupony.php?id_poz='.(int)$_POST["id"]);
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Edycja pozycji</div>
    <div id="cont">
          
          <script>
          $(document).ready(function() {
            $("#kuponyForm").validate({
              rules: {
                rabat_kwota: {
                  number: true,
                  required: function(element) {
                    if ($("#rodzaj_kwota").css('display') == 'block') {
                        return true;
                      } else {
                        return false;
                    }
                  }                
                },
                rabat_procent: {
                  number: true,
                  required: function(element) {
                    if ($("#rodzaj_procent").css('display') == 'block') {
                        return true;
                      } else {
                        return false;
                    }
                  } 
                },
                ilosc: {
                  range: [0, 100000],
                  number: true
                },
                wartosc: {
                  range: [1, 100000],
                  number: true
                },
                ilosc_max: {
                  range: [0, 100000],
                  number: true
                },
                wartosc_max: {
                  range: [1, 100000],
                  number: true
                },                 
                ilosc_kuponow: {
                  range: [1, 100000],
                  number: true,
                  required: true
                }                
              },
              messages: {
                rabat_kwota: {
                  required: "Pole jest wymagane.",
                  range: "Wartość musi być wieksza lub równa 0.01"
                },
                rabat_procent: {
                  required: "Pole jest wymagane.",
                  range: "Wartość musi być wieksza lub równa 0.01"
                },              
                ilosc: {
                  range: "Wartość musi być wieksza lub równa 1."
                },
                wartosc: {
                  range: "Wartość musi być wieksza lub równa 1."
                },
                ilosc_max: {
                  range: "Wartość musi być wieksza lub równa 1."
                },
                wartosc_max: {
                  range: "Wartość musi być wieksza lub równa 1."
                },                   
                ilosc_kuponow: {
                  required: "Pole jest wymagane.",
                  range: "Wartość musi być wieksza lub równa 1."
                }                 
              }
            });
            
            $('input.datepicker').Zebra_DatePicker({
               format: 'd-m-Y',
               inside: false,
               readonly_element: true
            });             
            
          });
          
          function rodzaj_rabat(elem) {
             $('#rodzaj_kwota').slideUp();
             $('#rodzaj_procent').slideUp();
             $('#rodzaj_wysylka').slideUp();
             //
             if (elem != '') {
                $('#rodzaj_' + elem).slideDown();
             }
          }             
          </script>      

          <form action="kupony/kupony_edytuj.php" method="post" id="kuponyForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Edycja danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select * from coupons where coupons_id = '" . (int)$_GET['id_poz'] . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
            
                $info = $sql->fetch_assoc();
                ?>
            
                <div class="pozycja_edytowana">
                    
                    <div class="info_content">
                
                    <input type="hidden" name="akcja" value="zapisz" />
                
                    <input type="hidden" name="id" value="<?php echo (int)$_GET['id_poz']; ?>" />
                    
                    <p>
                      <label for="kod">Kod kuponu:</label>
                      <input type="text" name="kod" id="kod" value="<?php echo $info['coupons_name']; ?>" size="35" disabled="disabled" />
                    </p>   

                    <p>
                      <label for="opis">Opis kuponu:</label>
                      <input type="text" name="opis" id="opis" value="<?php echo $info['coupons_description']; ?>" size="50" /><em class="TipIkona"><b>Opis kuponu - widoczny tylko dla administratora sklepu</b></em>
                    </p>
                    
                    <?php if ( !empty($info['coupons_email']) ) { ?>
                    
                    <p>
                      <label>Wysłany na mail:</label>
                      <strong class="AdresatEmail"><?php echo $info['coupons_email']; ?></strong>
                    </p>                    
                    
                    <?php } ?>
                    
                    <p>
                      <label>Rodzaj rabatu:</label>
                      <input type="radio" value="fixed" name="rodzaj" id="rodzaj_kwotowy" onclick="rodzaj_rabat('kwota')" <?php echo (($info['coupons_discount_type'] == 'fixed') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="rodzaj_kwotowy">kwotowy<em class="TipIkona"><b>Rabat jest stały kwotowy</b></em></label>
                      <input type="radio" value="percent" name="rodzaj" id="rodzaj_procentowy" onclick="rodzaj_rabat('procent')" <?php echo (($info['coupons_discount_type'] == 'percent') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="rodzaj_procentowy">procentowy<em class="TipIkona"><b>Rabat obliczany jest procentowo od wartości zamówienia</b></em></label>
                      <input type="radio" value="shipping" name="rodzaj" id="rodzaj_wysylki" onclick="rodzaj_rabat('wysylka')" <?php echo (($info['coupons_discount_type'] == 'shipping') ? 'checked="checked"' : ''); ?> /> <label class="OpisFor" for="rodzaj_wysylki">darmowa wysyłka<em class="TipIkona"><b>Rabat równy kosztom wysyłki - umożliwia darmową wysyłkę</b></em></label>
                    </p>
                    
                    <div id="rodzaj_kwota" <?php echo (($info['coupons_discount_type'] == 'fixed') ? 'style="display:block"' : 'style="display:none"'); ?>>
                      <p>
                          <label class="required" for="rabat_kwota">Wartość rabatu:</label>
                          <input type="text" name="rabat_kwota" id="rabat_kwota" value="<?php echo $info['coupons_discount_value']; ?>" size="10" /><em class="TipIkona"><b>Wartość kwotowa powyżej 0.01</b></em>
                      </p>
                    </div>
                    
                    <div id="rodzaj_wysylka" style="margin:10px<?php echo (($info['coupons_discount_type'] == 'shipping') ? ';display:block' : ';display:none'); ?>">
                    
                        <?php
                        $ListaWysylek = array();
                        $sql_wysylki = $db->open_query("SELECT id, nazwa, klasa FROM modules_shipping WHERE status = '1' order by sortowanie");
                        //
                        $i18n = new Translator($db, $_SESSION['domyslny_jezyk']['id']);
                        $tlumacz = $i18n->tlumacz('WYSYLKI');
                        //
                        while ($wysylki = $sql_wysylki->fetch_assoc()) {
                               //
                               $ListaWysylek[$wysylki['id']] = $tlumacz['WYSYLKA_'.$wysylki['id'].'_TYTUL'] . ' (' . $wysylki['nazwa'] . ')';
                               //
                        }
                        //
                        unset($tlumacz);
                        $db->close_query($sql_wysylki);
                        ?>
                        
                        <table>
                            <tr>
                                <td><label>Dostępna tylko dla wysyłek:</label></td>
                                <td style="padding-left:4px">
                                    <?php                        
                                    $PodzialWysylek = explode(',', (string)$info['coupons_modules_shipping']);
                                    foreach ( $ListaWysylek as $Klucz => $TmpWysylka ) {
                                        echo '<input type="checkbox" value="' . $Klucz . '" ' . ((in_array((string)$Klucz, $PodzialWysylek)) ? 'checked="checked"' : '') . ' name="id_wysylki[]" id="id_wysylki_' . $Klucz . '" /> <label class="OpisFor" for="id_wysylki_' . $Klucz . '">' . $TmpWysylka . '</label><br />';
                                    }               
                                    unset($ListaWysylek, $PodzialWysylek);
                                    ?>
                                </td>
                            </tr>
                        </table>                         
                        
                        <div class="maleInfo odlegloscRwd" style="margin-bottom:10px">Jeżeli nie zostanie wybrana żadna forma wysyłki to kupon będzie dostępny dla wszystkich wysyłek.</div>
                        
                    </div>                    
                    
                    <div id="rodzaj_procent" <?php echo (($info['coupons_discount_type'] == 'percent') ? 'style="display:block"' : 'style="display:none"'); ?>>
                      <p>
                          <label class="required" for="rabat_procent">Wartość rabatu (w %):</label>
                          <input type="text" name="rabat_procent" id="rabat_procent" value="<?php echo $info['coupons_discount_value']; ?>" size="3" /><em class="TipIkona"><b>Wartość procentowa od 0.01 do 100</b></em>
                      </p>
                    </div>
                    
                    <p>
                        <label for="data_od">Data rozpoczęcia:</label>
                        <input type="text" name="data_od" id="data_od" value="<?php echo ((Funkcje::czyNiePuste($info['coupons_date_start'])) ? date('d-m-Y',FunkcjeWlasnePHP::my_strtotime($info['coupons_date_start'])) : ''); ?>" size="20" class="datepicker" />                                        
                    </p>

                    <p>
                        <label for="data_do">Data zakończenia:</label>
                        <input type="text" name="data_do" id="data_do" value="<?php echo ((Funkcje::czyNiePuste($info['coupons_date_end'])) ? date('d-m-Y',FunkcjeWlasnePHP::my_strtotime($info['coupons_date_end'])) : ''); ?>" size="20" class="datepicker" />                                        
                    </p>
                    
                    <div class="RamkaWarunki">
                    
                        <b>Dodatkowe warunki użycia kuponu</b> 

                        <table style="margin:10px 0 10px 0">
                            <tr>
                                <td><label>Dostępny tylko dla wysyłek do kraju:</label></td>
                                <td style="padding-left:4px">
                                    <div style="max-height:200px;width:500px;overflow-y:auto">
                                        <?php
                                        $KrajeTablica = array();
                                        $KrajeTablica[] = array('id' => '0', 'text' => '-- wszystkie --');
                                        //
                                        $sqlKraje = $db->open_query("SELECT * FROM countries s, countries_description sd WHERE s.countries_id = sd.countries_id AND sd.language_id = '" . $_SESSION['domyslny_jezyk']['id'] . "' order by sd.countries_name");
                                        while ($infk = $sqlKraje->fetch_assoc()) {
                                            //
                                            echo '<input type="checkbox" value="' . $infk['countries_id'] . '" name="kraj[]" id="kraj_' . $infk['countries_id'] . '" ' . ((in_array((string)$infk['countries_id'], explode(',', (string)$info['coupons_countries']))) ? 'checked="checked" ' : '') . ' /> <label class="OpisFor" for="kraj_' . $infk['countries_id'] . '">' . $infk['countries_name'] . '</label><br />';
                                            //
                                        }
                                        $db->close_query($sqlKraje);
                                        //
                                        unset($KrajeTablica);
                                        ?>
                                    </div>
                                </td>
                            </tr>
                        </table>  

                        <div class="ostrzezenie odlegloscRwd" style="margin-bottom:10px">Jeżeli nie zostanie wybrany żaden kraj to kupon będzie dostępny dla wszystkich państw.</div>
                        
                        <table style="margin:10px 0 10px 0">
                            <tr>
                                <td><label>Dostępny dla form płatności:</label></td>
                                <td style="padding-left:4px">
                                    <div style="max-height:200px;width:500px;overflow-y:auto">
                                        <?php                        
                                        $TablicaModulowPlatnosci = Moduly::TablicaPlatnosciId(false);
                                        foreach ( $TablicaModulowPlatnosci as $ModulPlatnosci ) {
                                            echo '<input type="checkbox" value="' . $ModulPlatnosci['id'] . '" name="modul_platnosci[]" id="modul_platnosci_' . $ModulPlatnosci['id'] . '" ' . ((in_array((string)$ModulPlatnosci['id'], explode(',', (string)$info['coupons_customers_payment_id']))) ? 'checked="checked" ' : '') . ' /> <label class="OpisFor" for="modul_platnosci_' . $ModulPlatnosci['id'] . '">' . $ModulPlatnosci['text'] . '</label><br />';
                                        }               
                                        unset($TablicaModulowPlatnosci);
                                        ?>
                                    </div>
                                </td>
                            </tr>
                        </table>       

                        <div class="ostrzezenie odlegloscRwd" style="margin-bottom:10px">Jeżeli nie zostanie wybrana żadna forma płatności to kupon będzie dostępny dla wszystkich dostępnych płatności.</div>
                        
                        <table style="margin:10px 0 10px 0">
                            <tr>
                                <td><label>Dostępny dla grupy klientów:</label></td>
                                <td style="padding-left:4px">
                                    <div style="max-height:200px;width:500px;overflow-y:auto">
                                        <?php                        
                                        $TablicaGrupKlientow = Klienci::ListaGrupKlientow(true, 'Klienci bez rejestracji konta' );
                                        foreach ( $TablicaGrupKlientow as $GrupaKlienta ) {
                                            echo '<input type="checkbox" value="' . $GrupaKlienta['id'] . '" name="grupa_klientow[]" id="grupa_klientow_' . $GrupaKlienta['id'] . '" ' . ((in_array((string)$GrupaKlienta['id'], explode(',', (string)$info['coupons_customers_groups_id']))) ? 'checked="checked" ' : '') . ' /> <label class="OpisFor" for="grupa_klientow_' . $GrupaKlienta['id'] . '">' . $GrupaKlienta['text'] . '</label><br />';
                                        }               
                                        unset($TablicaGrupKlientow);
                                        ?>
                                    </div>
                                </td>
                            </tr>
                        </table> 
                        
                        <div class="ostrzezenie odlegloscRwd" style="margin-bottom:10px">Jeżeli nie zostanie wybrana żadna grupa klientów to kupon będzie dostępny dla wszystkich klientów.</div>

                        <p>
                          <label for="rodzaj_waluta">Dostępny tylko dla waluty:</label>
                          <?php
                          $tablica_walut = array();
                          $tablica_walut[] = array('id' => '',
                                                   'text' => '-- dowolna --');
                                                       
                          $zapytanie = "select currencies_id, title, code, currencies_marza, value from currencies";
                       
                          $sqlw = $db->open_query($zapytanie);   
                          while ($wynik = $sqlw->fetch_assoc()) {
                              $tablica_walut[] = array('id' => $wynik['code'],
                                                       'text' => $wynik['title']);
                          }
                          $db->close_query($sqlw); 
                          unset($zapytanie);      

                          echo Funkcje::RozwijaneMenu('rodzaj_waluta', $tablica_walut, $info['coupons_currency'],'style="width:200px" id="rodzaj_waluta"');
                          unset($tablica_walut);
                          ?>
                        </p>  
                        
                        <p>
                          <label>Czy wyświetlać kupon na karcie produktu ?</label>
                          <input type="radio" value="0" name="widoczny" id="widoczny_tak" <?php echo (($info['coupons_hidden'] == '0') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="widoczny_tak">tak<em class="TipIkona"><b>Czy kupon ma być widoczny dla wszystkich klientów na karcie produktu ?</b></em></label>
                          <input type="radio" value="1" name="widoczny" id="widoczny_nie" <?php echo (($info['coupons_hidden'] == '1') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="widoczny_nie">nie<em class="TipIkona"><b>Czy kupon ma być widoczny dla wszystkich klientów na karcie produktu ?</b></em></label>
                        </p>                          

                        <p>
                          <label for="ilosc">Minimalna ilość produktów:</label>
                          <input class="kropkaPusta" type="text" name="ilosc" id="ilosc" value="<?php echo (($info['coupons_min_quantity'] == 0) ? '' : $info['coupons_min_quantity']); ?>" size="3" /><em class="TipIkona"><b>Ilość produktów w koszyku od jakiej będzie można zrealizować kupon</b></em>
                        </p> 
                        
                        <p>
                          <label for="ilosc_max">Maksymalna ilość produktów:</label>
                          <input class="kropkaPusta" type="text" name="ilosc_max" id="ilosc_max" value="<?php echo (($info['coupons_max_quantity'] == 0) ? '' : $info['coupons_max_quantity']); ?>" size="3" /><em class="TipIkona"><b>Maksymalna ilość produktów w koszyku do jakiej będzie można zrealizować kupon</b></em>
                        </p>                             

                        <p>
                          <label for="wartosc">Minimalna wartość zamówienia:</label>
                          <input class="kropkaPusta" type="text" name="wartosc" id="wartosc" value="<?php echo (($info['coupons_min_order'] == 0) ? '' : $info['coupons_min_order']); ?>" size="10" /><em class="TipIkona"><b>Wartość zamówienia od jakiej będzie można zrealizować kupon</b></em>
                        </p>  
                        
                        <p>
                          <label for="wartosc_max">Maksymalna wartość zamówienia:</label>
                          <input class="kropkaPusta" type="text" name="wartosc_max" id="wartosc_max" value="<?php echo (($info['coupons_max_order'] == 0) ? '' : $info['coupons_max_order']); ?>" size="10" /><em class="TipIkona"><b>Maksymalna wartość zamówienia do jakiej będzie można zrealizować kupon</b></em>
                        </p>     

                        <p>
                          <label>Produkty promocyjne / wyprzedaż / produkt dnia:</label>
                          <input type="radio" value="1" name="promocja" id="promocja_tak" <?php echo (($info['coupons_specials'] == '1') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="promocja_tak">tak<em class="TipIkona"><b>Czy kuponem mają być objęte produkty promocyjne / wyprzedaży oraz przypisane jako produkt dnia ?</b></em></label>
                          <input type="radio" value="0" name="promocja" id="promocja_nie" <?php echo (($info['coupons_specials'] == '0') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="promocja_nie">nie<em class="TipIkona"><b>Czy kuponem mają być objęte produkty promocyjne / wyprzedaży oraz przypisane jako produkt dnia ?</b></em></label>
                        </p>                          

                        <p>
                          <label>Czy kupon przez jednego klienta może być użyty tylko 1 raz ?</label>
                          <input type="radio" value="1" name="uzycie_kuponu" id="uzycie_kuponu_tak" <?php echo (($info['coupons_customers_use'] == '1') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="uzycie_kuponu_tak">tak<em class="TipIkona"><b>Klient będzie mógł tylko raz użyć kupon (sprawdzany adres email i nr telefonu)</b></em></label>
                          <input type="radio" value="0" name="uzycie_kuponu" id="uzycie_kuponu_nie" <?php echo (($info['coupons_customers_use'] == '0') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="uzycie_kuponu_nie">nie<em class="TipIkona"><b>Klient może wiele razu użyć kupon</b></em></label>
                        </p>                        
                        
                        <p>
                          <label>Czy kupon może być użyty tylko na pierwsze zakupy klienta ?</label>
                          <input type="radio" value="1" name="pierwsze_zakupy" id="pierwsze_zakupy_tak" <?php echo (($info['coupons_customers_first_purchase'] == '1') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="pierwsze_zakupy_tak">tak<em class="TipIkona"><b>Klient będzie mógł tylko na pierwsze zakupy w sklepie (sprawdzany adres email i nr telefonu)</b></em></label>
                          <input type="radio" value="0" name="pierwsze_zakupy" id="pierwsze_zakupy_nie" <?php echo (($info['coupons_customers_first_purchase'] == '0') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="pierwsze_zakupy_nie">nie<em class="TipIkona"><b>Klient może wiele razu użyć kupon</b></em></label>
                        </p>                         

                        <p>
                          <label>Dostępny tylko dla:</label>
                          <?php
                          $warunki = '<strong>brak</strong>';
                          if ( $info['coupons_exclusion'] == 'kategorie' && $info['coupons_exclusion_id'] != '' ) {
                               $warunki = '<strong>wybranych kategorii</strong>';
                               //
                               $warunki .= '<span id="ListaWarunkow">';
                               //
                               $kategoria_nazwa = $db->open_query("select distinct categories_id, categories_name from categories_description where categories_id in (".$info['coupons_exclusion_id'].") and language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'");
                               while ($nazwa = $kategoria_nazwa->fetch_assoc()) {
                                      $warunki .= '&raquo ' . $nazwa['categories_name'] . '<br />';                               
                               }
                               $db->close_query($kategoria_nazwa);    
                               unset($kategoria_nazwa, $nazwa);                               
                               //
                               $warunki .= '</span>';
                               //
                          }
                          if ( $info['coupons_exclusion'] == 'producenci' && $info['coupons_exclusion_id'] != '' ) {
                               $warunki = '<strong>wybranych producentów</strong>';
                               //
                               $warunki .= '<span id="ListaWarunkow">';
                               //
                               $producent_nazwa = $db->open_query("select distinct manufacturers_name from manufacturers where manufacturers_id in (".$info['coupons_exclusion_id'].")");
                               while ($nazwa = $producent_nazwa->fetch_assoc()) {
                                      $warunki .= '&raquo ' . $nazwa['manufacturers_name'] . '<br />';                               
                               }
                               $db->close_query($producent_nazwa);    
                               unset($producent_nazwa, $nazwa);                               
                               //
                               $warunki .= '</span>';
                               //                               
                          }  
                          if ( $info['coupons_exclusion'] == 'kategorie_producenci' && $info['coupons_exclusion_id'] != '' ) {
                               //
                               $pod = explode('#', (string)$info['coupons_exclusion_id']);
                               //
                               if ( count($pod) == 2 ) {                            
                                   $warunki = '<strong>wybranych producentów oraz kategorii</strong>';
                                   //
                                   $warunki .= '<span id="ListaWarunkow" style="margin-bottom:15px">';
                                   //
                                   $producent_nazwa = $db->open_query("select distinct manufacturers_name from manufacturers where manufacturers_id in (0,".$pod[0].")");
                                   while ($nazwa = $producent_nazwa->fetch_assoc()) {
                                          $warunki .= '&raquo ' . $nazwa['manufacturers_name'] . '<br />';                               
                                   }
                                   $db->close_query($producent_nazwa);    
                                   unset($producent_nazwa, $nazwa);                               
                                   //
                                   $warunki .= '</span>';

                                   $warunki .= '<span id="ListaWarunkow">';
                                   //
                                   $kategoria_nazwa = $db->open_query("select distinct categories_id, categories_name from categories_description where categories_id in (0,".$pod[1].") and language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'");
                                   while ($nazwa = $kategoria_nazwa->fetch_assoc()) {
                                          $warunki .= '&raquo ' . $nazwa['categories_name'] . '<br />';                               
                                   }
                                   $db->close_query($kategoria_nazwa);    
                                   unset($kategoria_nazwa, $nazwa);                               
                                   //
                                   $warunki .= '</span>';
                                   //    
                               }
                               //
                          }                            
                          if ( $info['coupons_exclusion'] == 'produkty' && $info['coupons_exclusion_id'] != '' ) {
                               $warunki = '<strong>wybranych produktów</strong>';
                               //
                               $warunki .= '<span id="ListaWarunkow">';
                               //
                               $produkt_nazwa = $db->open_query("select distinct products_id, products_name from products_description where products_id in (".$info['coupons_exclusion_id'].") and language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'");
                               while ($nazwa = $produkt_nazwa->fetch_assoc()) {
                                      $warunki .= '&raquo ' . $nazwa['products_name'] . '<br />';                               
                               }
                               $db->close_query($produkt_nazwa);    
                               unset($produkt_nazwa, $nazwa);                               
                               //
                               $warunki .= '</span>';
                               //                               
                          }    
                          echo $warunki;
                          unset($warunki);
                          ?>
                        </p>                         

                        <?php if ( $info['coupons_products_only'] == '1' && $info['coupons_products_only_id'] != '' ) { ?>
                        <p>
                          <label>Dostępny tylko w powiązaniu z innym produktem:</label>
                          <?php
                          $warunki = '<strong>dla produktów</strong>';
                          //
                          $warunki .= '<span id="ListaWarunkow">';
                          //
                          $produkt_nazwa = $db->open_query("select distinct products_id, products_name from products_description where products_id in (".$info['coupons_products_only_id'].") and language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'");
                          while ($nazwa = $produkt_nazwa->fetch_assoc()) {
                                 $warunki .= '&raquo ' . $nazwa['products_name'] . '<br />';                               
                          }
                          $db->close_query($produkt_nazwa);    
                          unset($produkt_nazwa, $nazwa);                               
                          //
                          $warunki .= '</span>';
                          //                                
                          echo $warunki;
                          unset($warunki);
                          ?>
                        </p> 
                        <?php } ?>
                        
                    </div>
                    
                    <p>
                      <label class="required" for="ilosc_kuponow">Ilość dostępnych kuponów:</label>
                      <input type="text" name="ilosc_kuponow" id="ilosc_kuponow" value="<?php echo $info['coupons_quantity']; ?>" size="6"  /><em class="TipIkona"><b>Wartość określa ile kuponów może zostać wykorzystanych w sklepie</b></em>
                    </p>   

                    </div>
                 
                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Zapisz dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('kupony','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','kupony');">Powrót</button>           
                </div>                 

            <?php
            
            $db->close_query($sql);
            unset($info);            
            
            } else {
            
                echo '<div class="pozycja_edytowana">Brak danych do wyświetlenia</div>';
            
            }
            ?>

          </div>                      
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}