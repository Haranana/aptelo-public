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
        // sprawdz warunki
        //
        $warunki_szukania = '';
        //
        // jezeli jest prefix
        if (isset($_POST['prefix']) && !empty($_POST['prefix'])) {
            $Prefix = $filtr->process($_POST['prefix']);
            $dlugosc = strlen((string)$Prefix);
            //
            $warunki_szukania = " and SUBSTR(coupons_name,1,".$dlugosc.") = '" .$Prefix. "'";
            unset($Prefix, $dlugosc);
        }
        
        // jezeli jest data od
        if (isset($_POST['data_od']) && !empty($_POST['data_od'])) {
            $DataOd = $filtr->process($_POST['data_od']);
            $warunki_szukania .= " and coupons_date_added >= '".date('Y-m-d', FunkcjeWlasnePHP::my_strtotime($DataOd))."'";            
            //
            unset($DataOd);
        }   

        // jezeli jest data do
        if (isset($_POST['data_od']) && !empty($_POST['data_do'])) {
            $DataDo = $filtr->process($_POST['data_do']);
            $warunki_szukania .= " and coupons_date_added <= '".date('Y-m-d', FunkcjeWlasnePHP::my_strtotime($DataDo))."'";            
            //
            unset($DataDo);            
        }           
        
        if ( $warunki_szukania != '' ) {
          $warunki_szukania = preg_replace('/and/i', 'WHERE', $warunki_szukania, 1);
        }        
    
        $zapytanie = "select * from coupons" . $warunki_szukania;
        $sql = $db->open_query($zapytanie);
        
        //
        if ((int)$db->ile_rekordow($sql) > 0) {
        
            $ciag_do_zapisu = '';
            
            $tablica_pol = array();
            $tablica_pol[] = array('kod','Kod kuponu;','coupons_name');
            $tablica_pol[] = array('opis','Opis;','coupons_description');
            $tablica_pol[] = array('email','Email;','coupons_email');
            $tablica_pol[] = array('rodzaj','Rodzaj rabatu;','coupons_discount_type');
            $tablica_pol[] = array('znizka','Wartość rabatu;','coupons_discount_value');
            $tablica_pol[] = array('data_utworzenia','Data utworzenia kuponu;','coupons_date_added');
            $tablica_pol[] = array('waznosc_od','Data rozpoczęcia;','coupons_date_start');
            $tablica_pol[] = array('waznosc_do','Data zakończenia;','coupons_date_end');
            $tablica_pol[] = array('grupa_klientow','Grupa klientów;','coupons_customers_groups_id');
            $tablica_pol[] = array('moduly_platnosci','Moduły płatnosci;','coupons_customers_payment_id');
            $tablica_pol[] = array('kraj','Tylko dla kraju;','coupons_countries');
            $tablica_pol[] = array('ilosc_produktow','Minimalna ilość produktów;','coupons_min_quantity');
            $tablica_pol[] = array('max_ilosc_produktow','Maksymalna ilość produktów;','coupons_max_quantity');
            $tablica_pol[] = array('wartosc_zamowienia','Minimalna wartość zamówienia;','coupons_min_order');            
            $tablica_pol[] = array('max_wartosc_zamowienia','Maksymalna wartość zamówienia;','coupons_max_order');
            $tablica_pol[] = array('ilosc_kuponow','Ilość dostępnych kuponów;','coupons_quantity');
            $tablica_pol[] = array('promocje','Promocje;','coupons_specials');
            $tablica_pol[] = array('uzycie_kuponu','Uzycie_tylko_raz;','coupons_customers_use');
            $tablica_pol[] = array('pierwsze_zakupy','Pierwsze_zakupy;','coupons_customers_first_purchase');
            $tablica_pol[] = array('warunki','Ograniczenia typ;','coupons_exclusion');
            $tablica_pol[] = array('warunki_id','Ograniczenia ID;','coupons_exclusion_id');
            $tablica_pol[] = array('produkty_id','Produkty powiązane ID;','coupons_products_only_id');
            $tablica_pol[] = array('rodzaj_waluta','Waluta;','coupons_currency');
            $tablica_pol[] = array('widoczny','Widoczność kuponu;','coupons_hidden');
            $tablica_pol[] = array('status','Status kuponu;','coupons_status');
            $tablica_pol[] = array('wykorzystane','Ilość wykorzystanych;','coupons_id');

            for ($w = 0, $c = count($tablica_pol); $w < $c; $w++) {
                if (isset($_POST[$tablica_pol[$w][0]])) {
                    //
                    if ((int)$_POST[$tablica_pol[$w][0]] == 1) {
                        $ciag_do_zapisu .= $tablica_pol[$w][1];
                    }
                    //
                }
            }            

            $ciag_do_zapisu = substr((string)$ciag_do_zapisu, 0, -1);
            $ciag_do_zapisu .= "\n";            
            
            while ($info = $sql->fetch_assoc()) {

                for ($w = 0, $c = count($tablica_pol); $w < $c; $w++) {
                    if (isset($_POST[$tablica_pol[$w][0]])) {
                        //
                        if ((int)$_POST[$tablica_pol[$w][0]] == 1) {
                            if (Funkcje::czyNiePuste($info[$tablica_pol[$w][2]]) || ($tablica_pol[$w][0] == 'status' && (int)$info[$tablica_pol[$w][2]] == 0) ) {
                                //
                                $DoZapisu = $info[$tablica_pol[$w][2]];
                                //
                                // jezeli email
                                if ($tablica_pol[$w][0] == 'email') {
                                    if ($info[$tablica_pol[$w][2]] != '') {
                                        $DoZapisu = $info[$tablica_pol[$w][2]];
                                    } else {
                                        $DoZapisu = '-';
                                    }
                                }
                                // jezeli rodzaj rabatu
                                if ($tablica_pol[$w][0] == 'rodzaj') {
                                    switch ($info[$tablica_pol[$w][2]]) {
                                        case "fixed":
                                            $DoZapisu = 'kwota';
                                            break;
                                        case "percent":
                                            $DoZapisu = 'procent';
                                            break;  
                                        case "shipping":
                                            $DoZapisu = 'wysylka';
                                            break;                                                 
                                    }                                     
                                }
                                //
                                // jezeli data
                                if ($tablica_pol[$w][0] == 'data_utworzenia' || $tablica_pol[$w][0] == 'waznosc_od' || $tablica_pol[$w][0] == 'waznosc_do') {
                                    $DoZapisu = date('d-m-Y',FunkcjeWlasnePHP::my_strtotime($info[$tablica_pol[$w][2]]));
                                }
                                
                                if ($tablica_pol[$w][0] == 'promocje') {
                                    if ($info[$tablica_pol[$w][2]] == '1') {
                                        $DoZapisu = 'tak';
                                      } else {
                                        $DoZapisu = 'nie';
                                    }
                                }
                                
                                if ($tablica_pol[$w][0] == 'uzycie_kuponu') {
                                    if ($info[$tablica_pol[$w][2]] == '1') {
                                        $DoZapisu = 'tak';
                                      } else {
                                        $DoZapisu = 'nie';
                                    }
                                }   

                                if ($tablica_pol[$w][0] == 'pierwsze_zakupy') {
                                    if ($info[$tablica_pol[$w][2]] == '1') {
                                        $DoZapisu = 'tak';
                                      } else {
                                        $DoZapisu = 'nie';
                                    }
                                }                                      
                                
                                // kraj 
                                if ($tablica_pol[$w][0] == 'kraj') {
                                    if ((int)$info[$tablica_pol[$w][2]] != '') {
                                       $DoZapisu = (string)$info[$tablica_pol[$w][2]];
                                    }
                                }   

                                // jezeli status
                                if ($tablica_pol[$w][0] == 'status') {
                                    switch ((int)$info[$tablica_pol[$w][2]]) {
                                        case 1:
                                            $DoZapisu = 'aktywny';
                                            break;
                                        case 0:
                                            $DoZapisu = 'nieaktywny';
                                            break;                                                 
                                    }                                  
                                }              
    
                                // jezeli widocznosc
                                if ($tablica_pol[$w][0] == 'widoczny') {
                                    if ($info[$tablica_pol[$w][2]] == '0') {
                                        $DoZapisu = 'tak';
                                      } else {
                                        $DoZapisu = 'nie';
                                    }
                                }   

                                // ile wykorzystanych
                                if ($tablica_pol[$w][0] == 'wykorzystane') {
                                    if ((int)$info[$tablica_pol[$w][2]] > 0) {
                                       //
                                       $wykorzystanie = $db->open_query("select distinct count(*) as ile_wykorzystanych from coupons_to_orders where coupons_id = '" . (int)$info[$tablica_pol[$w][2]] . "'");
                                       $ile_kuponow = $wykorzystanie->fetch_assoc();
                                       //
                                       $DoZapisu = $ile_kuponow['ile_wykorzystanych']; 
                                       //
                                       $db->close_query($wykorzystanie);
                                       unset($ile_kuponow);                                            
                                       //
                                    }
                                }                                   

                                $ciag_do_zapisu .=  $DoZapisu . ';';
                                
                            } else {
                              
                                $tmp = '-;';
                                if ($tablica_pol[$w][0] == 'promocje' || $tablica_pol[$w][0] == 'uzycie_kuponu' || $tablica_pol[$w][0] == 'pierwsze_zakupy') {
                                    if ($info[$tablica_pol[$w][2]] == '0') {
                                        $tmp = 'nie;';
                                    }
                                }
                                if ($tablica_pol[$w][0] == 'widoczny') {
                                    if ($info[$tablica_pol[$w][2]] == '0') {
                                        $tmp = 'tak;';
                                    }
                                }
                                
                                $ciag_do_zapisu .= $tmp;

                            }
                        }
                        //
                    }
                }    
                
                $ciag_do_zapisu = substr((string)$ciag_do_zapisu, 0, -1);
                $ciag_do_zapisu .= "\n";

            }
            
            //
            $db->close_query($sql);
            unset($info);      

            header("Content-Type: application/force-download\n");
            header("Cache-Control: cache, must-revalidate");   
            header("Pragma: public");
            header("Content-Disposition: attachment; filename=eksport_kuponow_rabatowych_" . date("d-m-Y") . ".csv");
            print $ciag_do_zapisu;
            exit;   
            
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
    
          <script>
          $(document).ready(function() {

            $('input.datepicker').Zebra_DatePicker({
               format: 'd-m-Y',
               inside: false,
               readonly_element: false
            });             
            
          });       
          </script>       
          
          <form action="kupony/kupony_export.php" method="post" id="kuponyForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Eksport danych</div>
            
            <div class="pozycja_edytowana">
                
                <div class="info_content">
            
                <input type="hidden" name="akcja" value="zapisz" />
                
                <div class="NaglowekEksport">Zakres danych do eksportu (pozostawienie pol pustych spowoduje eksport wszystkich kuponów)</div>
            
                <p>
                  <label for="prefix">Kupony o prefixie:</label>
                  <input type="text" name="prefix" id="prefix" value="" size="10" />
                </p>   

                <p>
                    <label for="data_od">Data dodania od:</label>
                    <input type="text" name="data_od" id="data_od" value="" size="20" class="datepicker" />                                        
                </p>

                <p>
                    <label for="data_do">Data dodania do:</label>
                    <input type="text" name="data_do" id="data_do" value="" size="20" class="datepicker" />                                        
                </p>
                
                <div class="NaglowekEksport">Jakie dane eksportować ?</div>
                
                <table class="Inputy JednaLinia">
                
                    <tr>
                        <td><input type="checkbox" name="kod" id="par_kod" value="1" checked="checked" /> <label class="OpisFor" for="par_kod">kod kuponu</label></td>
                        <td><input type="checkbox" name="opis" id="par_opis" value="1" /> <label class="OpisFor" for="par_opis">opis</label></td>
                        <td><input type="checkbox" name="rodzaj" id="par_rodzaj" value="1" checked="checked" /> <label class="OpisFor" for="par_rodzaj">rodzaj kuponu</label></td>
                        <td><input type="checkbox" name="znizka" id="par_znizka" value="1" checked="checked" /> <label class="OpisFor" for="par_znizka">zniżka</label></td>
                        <td><input type="checkbox" name="data_utworzenia" id="par_data_utworzenia" value="1" checked="checked" /> <label class="OpisFor" for="par_data_utworzenia">data utworzenia</label></td>                        
                    </tr>
                    
                    <tr>
                        <td><input type="checkbox" name="waznosc_od" id="par_waznosc_od" value="1" checked="checked" /> <label class="OpisFor" for="par_waznosc_od">ważność od</label></td>
                        <td><input type="checkbox" name="waznosc_do" id="par_waznosc_do" value="1" checked="checked" /> <label class="OpisFor" for="par_waznosc_do">ważność do</label></td>
                        <td><input type="checkbox" name="grupa_klientow" id="par_grupa_klientow" value="1" /> <label class="OpisFor" for="par_grupa_klientow">id grupy klientów</label></td>
                        <td><input type="checkbox" name="kraj" id="par_kraj" value="1" /> <label class="OpisFor" for="par_kraj">id państw do wysyłek</label></td>
                        <td><input type="checkbox" name="ilosc_produktow" id="par_ilosc_produktow" value="1" /> <label class="OpisFor" for="par_ilosc_produktow">minimalna ilość produktów</label></td>                        
                        
                    </tr>
                    
                    <tr>
                        <td><input type="checkbox" name="max_ilosc_produktow" id="par_max_ilosc_produktow" value="1" /> <label class="OpisFor" for="par_max_ilosc_produktow">maksymalna ilość produktów</label></td>
                        <td><input type="checkbox" name="wartosc_zamowienia" id="par_wartosc_zamowienia" value="1" /> <label class="OpisFor" for="par_wartosc_zamowienia">minimalna wartość zamówienia</label></td>
                        <td><input type="checkbox" name="max_wartosc_zamowienia" id="par_max_wartosc_zamowienia" value="1" /> <label class="OpisFor" for="par_max_wartosc_zamowienia">maksymalna wartość zamówienia</label></td>                        
                        <td><input type="checkbox" name="ilosc_kuponow" id="par_ilosc_kuponow" value="1" checked="checked" /> <label class="OpisFor" for="par_ilosc_kuponow">ilość dostępnych kuponów</label></td>
                        <td><input type="checkbox" name="promocje" id="par_promocje" value="1" checked="checked" /> <label class="OpisFor" for="par_promocje">wykluczenia promocji</label></td>
                    </tr> 
                       
                    <tr>
                        <td><input type="checkbox" name="warunki" id="par_warunki" value="1" checked="checked" /> <label class="OpisFor" for="par_warunki">ograniczenia kategorii / producentów / produktów</label></td>
                        <td><input type="checkbox" name="warunki_id" id="par_warunki_id" value="1" checked="checked" /> <label class="OpisFor" for="par_warunki_id">id ograniczen kategorii / producentów / produktów</label></td>
                        <td><input type="checkbox" name="status" id="par_status" value="1" checked="checked" /> <label class="OpisFor" for="par_status">status kuponu (aktywny/nieaktywny)</label></td>
                        <td><input type="checkbox" name="wykorzystane" id="par_wykorzystane" value="1" checked="checked" /> <label class="OpisFor" for="par_wykorzystane">ilość wykorzystanych kuponów</label></td>
                        <td><input type="checkbox" name="email" id="par_email" value="1" /> <label class="OpisFor" for="par_email">email</label></td>
                    </tr> 
                    
                    <tr>
                        <td><input type="checkbox" name="uzycie_kuponu" id="par_uzycie_kuponu" value="1" checked="checked" /> <label class="OpisFor" for="par_uzycie_kuponu">użycie przez klienta tylko 1 raz</label></td>
                        <td><input type="checkbox" name="pierwsze_zakupy" id="par_pierwsze_zakupy" value="1" checked="checked" /> <label class="OpisFor" for="par_pierwsze_zakupy">tylko na pierwsze zakupy</label></td>
                        <td><input type="checkbox" name="moduly_platnosci" id="par_moduly_platnosci" value="1" /> <label class="OpisFor" for="par_moduly_platnosci">id modułów płatności</label></td>
                        <td><input type="checkbox" name="widoczny" id="par_widoczny" value="1" checked="checked" /> <label class="OpisFor" for="par_widoczny">widoczność na karcie produktu</label></td>
                        <td><input type="checkbox" name="produkty_id" id="par_produkty_id" value="1" checked="checked" /> <label class="OpisFor" for="par_produkty_id">produkty powiązane</label></td>
                    </tr>
                    
                    <tr>
                        <td><input type="checkbox" name="rodzaj_waluta" id="par_rodzaj_waluta" value="1" checked="checked" /> <label class="OpisFor" for="par_rodzaj_waluta">waluta</label></td>
                    </tr>                    
                    
                </table>
                
                </div>
             
            </div>

            <div class="przyciski_dolne">
              <input type="submit" class="przyciskNon" value="Wygeneruj dane" />
              <button type="button" class="przyciskNon" onclick="cofnij('kupony','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','kupony');">Powrót</button>           
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