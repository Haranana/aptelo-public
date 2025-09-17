<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        if ($_POST["domyslny"] == '1') {
            $pola = array(array('allegro_user_default','0'));
            $db->update_query('allegro_users' , $pola);
            $_SESSION['domyslny_uzytkownik_allegro'] = (int)$_POST["id"];
        }

        $pola = array(
                array('allegro_user_login',$filtr->process($_POST["user_name"])),
                array('allegro_user_clientid',$filtr->process($_POST["client_id"])),
                array('allegro_user_clientsecret',$filtr->process($_POST["client_secret"])),
                array('allegro_user_status',$_POST["status"]),
                array('allegro_user_type',$_POST["typ"]),
                array('allegro_user_default',(int)$_POST["domyslny"]),
                array('allegro_additionalinfo',$filtr->process($_POST["additionalinfo"])),
                array('allegro_user_city',$filtr->process($_POST["city"])),
                array('allegro_user_postcode',$filtr->process($_POST["postcode"])),
                array('allegro_user_province',$filtr->process($_POST["province"]))
        );
        
        $konfig = array();
        
        foreach ($_POST['konfiguracja'] as $klucz => $tmp ) {
            //
            $konfig[$klucz] = $tmp;
            //
        }
        
        $pola[] = array( 'allegro_auction_settings', serialize($konfig) );
                
        //			
        $db->update_query('allegro_users' , $pola, " allegro_user_id = '".(int)$_POST["id"]."'");	
        unset($pola);
        
        //
        Funkcje::PrzekierowanieURL('konfiguracja_uzytkownicy.php?id_poz='.(int)$_POST["id"]);
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Edycja pozycji</div>
    <div id="cont">
          
          <script>
          $(document).ready(function() {
            $("#allegroForm").validate({
              rules: {
                user_name: {
                  required: true
                },
                city: {
                  required: true
                },
                postcode: {
                  required: true
                },
                client_id: {
                  required: true
                },
                client_secret: {
                  required: true
                }
              },
              messages: {
                client_id: {
                  required: "Pole jest wymagane."
                },               
                city: {
                  required: "Pole jest wymagane."
                },               
                postcode: {
                  required: "Pole jest wymagane."
                },               
                document_uid: {
                  required: "Pole jest wymagane."
                },
                client_secret: {
                  required: "Pole jest wymagane."
                }               
              }
            });
          });
          </script>     

          <form action="allegro/konfiguracja_uzytkownicy_edytuj.php" method="post" id="allegroForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Edycja danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select * from allegro_users where allegro_user_id = '" . (int)$_GET['id_poz'] . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
            
                $info = $sql->fetch_assoc();
                ?>
            
                <div class="pozycja_edytowana">
                
                    <div class="maleInfo">
                        <a href="https://apps.developer.allegro.pl/" target="_blank">Generowanie klucza REST API - Allegro rzeczywiste</a>
                    </div>
                    <div class="maleInfo">
                        <a href="https://apps.developer.allegro.pl.allegrosandbox.pl/" target="_blank">Generowanie klucza REST API - Allegro testowe</a>
                    </div>
                    <div class="maleInfo">
                        WAŻNE: Przy rejestracji aplikacji należy podać adres powrotu (Redirect URI): <strong><?php echo ADRES_URL_SKLEPU; ?>/zarzadzanie/allegro/allegro_logowanie.php</strong>
                    </div>

                    <div class="info_content">
                
                    <input type="hidden" name="akcja" value="zapisz" />
                
                    <input type="hidden" name="id" value="<?php echo (int)$_GET['id_poz']; ?>" />

                    <div class="DaneAukcjaNaglowek" style="margin-bottom:10px">Dane aplikacji do połączenia z REST API Allegro</div>                          
                    
                    <p>
                      <label>Typ konta:</label>
                      <input type="radio" value="P" name="typ" id="typ_nie" <?php echo ( $info['allegro_user_type'] == 'P' ? 'checked="checked"' : '' ); ?> /><label class="OpisFor" for="typ_nie">Prywatne</label>
                      <input type="radio" value="F" name="typ" id="typ_tak" <?php echo ( $info['allegro_user_type'] == 'F' ? 'checked="checked"' : '' ); ?> /><label class="OpisFor" for="typ_tak">Firmowe</label>                      
                    </p>

                    <p>
                      <label>Status:</label>
                      <input type="radio" value="0" name="status" id="status_nie" <?php echo ( $info['allegro_user_status'] == '0' ? 'checked="checked"' : '' ); ?>/><label class="OpisFor" for="status_nie">nieaktywny</label>
                      <input type="radio" value="1" name="status" id="status_tak" <?php echo ( $info['allegro_user_status'] == '1' ? 'checked="checked"' : '' ); ?> /><label class="OpisFor" for="status_tak">aktywny</label>
                    </p> 

                    <p>
                      <label class="required" for="user_name">Nazwa użytkownika w Allegro:</label>
                      <input type="text" name="user_name" size="53" value="<?php echo $info['allegro_user_login']; ?>" id="user_name" />
                    </p>

                    <p>
                      <label class="required" for="client_id">Client ID:</label>
                      <textarea name="client_id" id="client_id" cols="80" rows="3"><?php echo $info['allegro_user_clientid']; ?></textarea>
                    </p>

                    <p>
                      <label class="required" for="client_id">Client Secret:</label>
                      <textarea name="client_secret" id="client_secret" cols="80" rows="3"><?php echo $info['allegro_user_clientsecret']; ?></textarea>
                    </p>

                    <div class="DaneAukcjaNaglowek" style="margin-bottom:10px">Dane kontaktowe</div>                          
                    <p>
                        <label for="province">Województwo:</label>
                         <select name="province" id="province">
                            <option value="DOLNOSLASKIE" <?php echo ($info['allegro_user_province'] == 'DOLNOSLASKIE' ? 'selected="selected"' : ''); ?>>dolnośląskie</option>
                            <option value="KUJAWSKO_POMORSKIE" <?php echo ($info['allegro_user_province'] == 'KUJAWSKO_POMORSKIE' ? 'selected="selected"' : ''); ?>>kujawsko-pomorskie</option>
                            <option value="LUBELSKIE" <?php echo ($info['allegro_user_province'] == 'LUBELSKIE' ? 'selected="selected"' : ''); ?>>lubelskie</option>
                            <option value="LUBUSKIE" <?php echo ($info['allegro_user_province'] == 'LUBUSKIE' ? 'selected="selected"' : ''); ?>>lubuskie</option>
                            <option value="LODZKIE" <?php echo ($info['allegro_user_province'] == 'LODZKIE' ? 'selected="selected"' : ''); ?>>łódzkie</option>
                            <option value="MALOPOLSKIE" <?php echo ($info['allegro_user_province'] == 'MALOPOLSKIE' ? 'selected="selected"' : ''); ?>>małopolskie</option>
                            <option value="MAZOWIECKIE" <?php echo ($info['allegro_user_province'] == 'MAZOWIECKIE' ? 'selected="selected"' : ''); ?>>mazowieckie</option>
                            <option value="OPOLSKIE" <?php echo ($info['allegro_user_province'] == 'OPOLSKIE' ? 'selected="selected"' : ''); ?>>opolskie</option>
                            <option value="PODKARPACKIE" <?php echo ($info['allegro_user_province'] == 'PODKARPACKIE' ? 'selected="selected"' : ''); ?>>podkarpackie</option>
                            <option value="PODLASKIE" <?php echo ($info['allegro_user_province'] == 'PODLASKIE' ? 'selected="selected"' : ''); ?>>podlaskie</option>
                            <option value="POMORSKIE" <?php echo ($info['allegro_user_province'] == 'POMORSKIE' ? 'selected="selected"' : ''); ?>>pomorskie</option>
                            <option value="SLASKIE" <?php echo ($info['allegro_user_province'] == 'SLASKIE' ? 'selected="selected"' : ''); ?>>śląskie</option>
                            <option value="SWIETOKRZYSKIE" <?php echo ($info['allegro_user_province'] == 'SWIETOKRZYSKIE' ? 'selected="selected"' : ''); ?>>świętokrzyskie</option>
                            <option value="WARMINSKO_MAZURSKIE" <?php echo ($info['allegro_user_province'] == 'WARMINSKO_MAZURSKIE' ? 'selected="selected"' : ''); ?>>warmińsko-mazurskie</option>
                            <option value="WIELKOPOLSKIE" <?php echo ($info['allegro_user_province'] == 'WIELKOPOLSKIE' ? 'selected="selected"' : ''); ?>>wielkopolskie</option>
                            <option value="ZACHODNIOPOMORSKIE" <?php echo ($info['allegro_user_province'] == 'ZACHODNIOPOMORSKIE' ? 'selected="selected"' : ''); ?>>zachodniopomorskie</option>
                         </select>
                    </p>

                    <p>
                      <label class="required" for="city">Miasto:</label>
                      <input type="text" name="city" size="53" value="<?php echo $info['allegro_user_city']; ?>" id="city" />
                    </p>
                    <p>
                      <label class="required" for="postcode">Kod pocztowy:</label>
                      <input type="text" name="postcode" id="postcode" value="<?php echo $info['allegro_user_postcode']; ?>" maxlength="6" placeholder="XX-XXX" />
                    </p>

                    <p>
                      <label for="additionalinfo">Dodatkowe informacje o dostawie:</label>
                      <textarea name="additionalinfo" id="additionalinfo" cols="80" rows="3"><?php echo $info['allegro_additionalinfo']; ?></textarea><em class="TipIkona"><b>dodatkowe informacje o dostawie, możesz tutaj wpisać informację w formie tekstowej, którą wyświetlimy w ofercie w sekcji Dostawa i płatność</b></em>
                    </p>

                    <?php if ($info['allegro_user_default'] == '0') { ?>
                    
                        <div class="DaneAukcjaNaglowek" style="margin-bottom:10px">Pozostałe</div>                          
                        <p>
                          <label>Czy konto jest domyślne:</label>
                          <input type="radio" value="0" name="domyslny" id="domyslna_nie" checked="checked" /><label class="OpisFor" for="domyslna_nie">nie</label>
                          <input type="radio" value="1" name="domyslny" id="domyslna_tak" /><label class="OpisFor" for="domyslna_tak">tak</label>                      
                        </p>
                    
                    <?php } else { ?>
                    
                        <input type="hidden" name="domyslny" value="1" />
                    
                    <?php } ?>
                    
                    <div class="DaneAukcjaNaglowek" style="margin-bottom:10px">Domyślne wartości wystawianych aukcji dla edytowanego konta</div>                          

                    <?php
                    // pobranie dodatkowych ustawien domyslnych
                    $konfig = @unserialize($info['allegro_auction_settings']);
                    //
                    if ( !is_array($konfig) ) {
                         $konfig = array();
                    }
                    //
                    $AllegroRest = new AllegroRest( array('allegro_user' => (int)$_GET['id_poz']) );
                    ?>
                    
                    <p>
                      <label for="czas_trwania">Czas trwania aukcji:</label>
                      <select name="konfiguracja[publication]" id="czas_trwania">
                          <option value="" <?php echo ((isset($konfig['publication']) && $konfig['publication'] == '') ? 'selected="selected"' : ''); ?>>--- wybierz ---</option>
                          <option value="PT72H" <?php echo ((isset($konfig['publication']) && $konfig['publication'] == 'PT72H') ? 'selected="selected"' : ''); ?>>3 dni</option>
                          <option value="PT120H" <?php echo ((isset($konfig['publication']) && $konfig['publication'] == 'PT120H') ? 'selected="selected"' : ''); ?>>5 dni</option>
                          <option value="PT168H" <?php echo ((isset($konfig['publication']) && $konfig['publication'] == 'PT168H') ? 'selected="selected"' : ''); ?>>7 dni</option>
                          <option value="PT240H" <?php echo ((isset($konfig['publication']) && $konfig['publication'] == 'PT240H') ? 'selected="selected"' : ''); ?>>10 dni</option>
                          <option value="PT480H" <?php echo ((isset($konfig['publication']) && $konfig['publication'] == 'PT480H') ? 'selected="selected"' : ''); ?>>20 dni</option>
                          <option value="PT720H"<?php echo ((isset($konfig['publication']) && $konfig['publication'] == 'PT720H') ? 'selected="selected"' : ''); ?> >30 dni</option>
                          <option value="PT1000" <?php echo ((isset($konfig['publication']) && $konfig['publication'] == 'PT1000') ? 'selected="selected"' : ''); ?>>do wyczerpania zapasów</option>
                      </select>                     
                    </p>         

                    <p>
                      <label for="czas_wysylki">Czas wysyłki:</label>
                      <select name="konfiguracja[delivery]" id="czas_wysylki">
                          <option value="" <?php echo ((isset($konfig['delivery']) && $konfig['delivery'] == '') ? 'selected="selected"' : ''); ?>>--- wybierz ---</option>
                          <option value="PT0S" <?php echo ((isset($konfig['delivery']) && $konfig['delivery'] == 'PT0S') ? 'selected="selected"' : ''); ?>>natychmiast</option>
                          <option value="PT24H" <?php echo ((isset($konfig['delivery']) && $konfig['delivery'] == 'PT24H') ? 'selected="selected"' : ''); ?>>24 godziny</option>
                          <option value="PT48H" <?php echo ((isset($konfig['delivery']) && $konfig['delivery'] == 'PT48H') ? 'selected="selected"' : ''); ?>>2 dni</option>
                          <option value="PT72H" <?php echo ((isset($konfig['delivery']) && $konfig['delivery'] == 'PT72H') ? 'selected="selected"' : ''); ?>>3 dni</option>
                          <option value="PT96H" <?php echo ((isset($konfig['delivery']) && $konfig['delivery'] == 'PT96H') ? 'selected="selected"' : ''); ?>>4 dni</option>
                          <option value="PT120H" <?php echo ((isset($konfig['delivery']) && $konfig['delivery'] == 'PT120H') ? 'selected="selected"' : ''); ?>>5 dni</option>
                          <option value="PT168H" <?php echo ((isset($konfig['delivery']) && $konfig['delivery'] == 'PT168H') ? 'selected="selected"' : ''); ?>>7 dni</option>
                          <option value="PT240H" <?php echo ((isset($konfig['delivery']) && $konfig['delivery'] == 'PT240H') ? 'selected="selected"' : ''); ?>>10 dni</option>
                          <option value="PT336H" <?php echo ((isset($konfig['delivery']) && $konfig['delivery'] == 'PT336H') ? 'selected="selected"' : ''); ?>>14 dni</option>
                          <option value="PT504H" <?php echo ((isset($konfig['delivery']) && $konfig['delivery'] == 'PT504H') ? 'selected="selected"' : ''); ?>>21 dni</option>
                          <option value="PT720H" <?php echo ((isset($konfig['delivery']) && $konfig['delivery'] == 'PT720H') ? 'selected="selected"' : ''); ?>>30 dni</option>
                          <option value="PT1440H" <?php echo ((isset($konfig['delivery']) && $konfig['delivery'] == 'PT1440H') ? 'selected="selected"' : ''); ?>>60 dni</option>
                      </select>
                    </p>             

                    <p>
                      <label for="cennik_dostawy">Cennik dostawy:</label>
                      <select name="konfiguracja[shippingRates]" id="cennik_dostawy">
                          <option value="">--- wybierz ---</option>

                          <?php
                          $dane = array( 'seller.id' => $AllegroRest->ParametryPolaczenia['UserId'] ); 
                          $cennik_dostaw = $AllegroRest->commandRequest('sale/shipping-rates', $dane, '');
                          
                          if ( isset($cennik_dostaw->shippingRates) && count($cennik_dostaw->shippingRates) > 0 ) {
                               //
                               foreach ( $cennik_dostaw->shippingRates as $cennik ) {
                                   //
                                   echo '<option value="' . $cennik->id . '"' . ((isset($konfig['shippingRates']) && $konfig['shippingRates'] == $cennik->id) ? 'selected="selected"' : '') . '>' . $cennik->name . '</option>';
                                   //
                               }
                               //
                          }
                          
                          unset($cennik_dostaw);
                          ?>
                                                    
                      </select><em class="TipIkona"><b>Wyświetlane są cenniki dostaw zdefiniowane bezpośrednio w Allegro</b></em>
                    </p> 

                    <p>
                      <label for="warunki_zwrotow">Warunki zwrotów:</label>
                      <select name="konfiguracja[returnPolicy]" id="warunki_zwrotow">
                          <option value="">--- wybierz ---</option>
                          
                          <?php
                          //
                          $dane = array( 'seller.id' => $AllegroRest->ParametryPolaczenia['UserId'] ); 
                          $warunki_zwrotow = $AllegroRest->commandRequest('after-sales-service-conditions/return-policies', $dane, '');
                          
                          if ( isset($warunki_zwrotow->returnPolicies) && count($warunki_zwrotow->returnPolicies) > 0 ) {
                               //
                               foreach ( $warunki_zwrotow->returnPolicies as $zwrot ) {
                                   //
                                   echo '<option value="' . $zwrot->id . '"' . ((isset($konfig['returnPolicy']) && $konfig['returnPolicy'] == $zwrot->id) ? 'selected="selected"' : '') . '>' . $zwrot->name . '</option>';
                                   //
                               }
                               //
                          }
                          //
                          unset($zwroty, $zwrot);
                          ?>                                    
               
                      </select><em class="TipIkona"><b>Wyświetlane są warunki zwrotów zdefiniowane bezpośrednio w Allegro</b></em>                                
                    </p>    

                    <p>
                      <label for="warunki_reklamacji">Warunki reklamacji:</label>
                      <select name="konfiguracja[impliedWarranty]" id="warunki_reklamacji">
                          <option value="">--- wybierz ---</option>
                          
                          <?php
                          //
                          $dane = array( 'seller.id' => $AllegroRest->ParametryPolaczenia['UserId'] ); 
                          $reklamacje = $AllegroRest->commandRequest('after-sales-service-conditions/implied-warranties', $dane, '');
                          
                          if ( isset($reklamacje->impliedWarranties) && count($reklamacje->impliedWarranties) > 0 ) {
                               //
                               foreach ( $reklamacje->impliedWarranties as $reklamacja ) {
                                   //
                                   echo '<option value="' . $reklamacja->id . '"' . ((isset($konfig['impliedWarranty']) && $konfig['impliedWarranty'] == $reklamacja->id) ? 'selected="selected"' : '') . '>' . $reklamacja->name . '</option>';
                                   //
                               }
                               //
                          }
                          //
                          unset($reklamacje, $reklamacja);
                          ?>      
                          
                      </select><em class="TipIkona"><b>Wyświetlane są warunki reklamacji zdefiniowane bezpośrednio w Allegro</b></em>
                    </p>       

                    <p>
                      <label for="warunki_gwarancji">Gwarancja:</label>
                      <select name="konfiguracja[warranty]" id="warunki_gwarancji">
                          <option value="">--- wybierz ---</option>

                          <?php
                          //
                          $dane = array( 'seller.id' => $AllegroRest->ParametryPolaczenia['UserId'] ); 
                          $gwarancje = $AllegroRest->commandRequest('after-sales-service-conditions/warranties', $dane, '');
                          
                          if ( isset($gwarancje->warranties) && count($gwarancje->warranties) > 0 ) {
                               //
                               foreach ( $gwarancje->warranties as $gwarancja ) {
                                   //
                                   echo '<option value="' . $gwarancja->id . '"' . ((isset($konfig['warranty']) && $konfig['warranty'] == $gwarancja->id) ? 'selected="selected"' : '') . '>' . $gwarancja->name . '</option>';
                                   //
                               }
                               //
                          }
                          //
                          unset($gwarancje, $gwarancja);
                          ?>
                                                    
                      </select><em class="TipIkona"><b>Wyświetlane są gwarancje zdefiniowane bezpośrednio w Allegro</b></em>
                    </p>     

                    <p>
                      <label for="uslugi_dodatkowe">Usługi dodatkowe:</label>
                      <select name="konfiguracja[additionalServices]" id="uslugi_dodatkowe">
                          <option value="">--- wybierz ---</option>

                          <?php
                          $dane = array( 'user.id' => $AllegroRest->ParametryPolaczenia['UserId'] ); 
                          $uslugi_dodatkowe = $AllegroRest->commandRequest('sale/offer-additional-services/groups', $dane, '');

                          if ( isset($uslugi_dodatkowe->additionalServicesGroups) && count($uslugi_dodatkowe->additionalServicesGroups) > 0 ) {
                               //
                               foreach ( $uslugi_dodatkowe->additionalServicesGroups as $usluga ) {
                                   //
                                   echo '<option value="' . $usluga->id . '"' . ((isset($konfig['additionalServices']) && $konfig['additionalServices'] == $usluga->id) ? 'selected="selected"' : '') . '>' . $usluga->name . '</option>';
                                   //
                               }
                               //
                          }
                          
                          unset($uslugi_dodatkowe);
                          ?>
                                                    
                      </select><em class="TipIkona"><b>Wyświetlane są usługi dodatkowe zdefiniowane bezpośrednio w Allegro</b></em>
                    </p>     
                    
                    <p>
                      <label for="tabela_rozmiarow">Tabela rozmiarów:</label>
                      <select name="konfiguracja[sizeTable]" id="tabela_rozmiarow">
                          <option value="">--- wybierz ---</option>

                          <?php
                          $dane = array( 'user.id' => $AllegroRest->ParametryPolaczenia['UserId'] ); 
                          $tabela_rozmiarow = $AllegroRest->commandRequest('sale/size-tables', $dane, '');

                          if ( isset($tabela_rozmiarow->tables) && count($tabela_rozmiarow->tables) > 0 ) {
                               //
                               foreach ( $tabela_rozmiarow->tables as $tab_rozmiar ) {
                                   //
                                   echo '<option value="' . $tab_rozmiar->id . '"' . ((isset($konfig['sizeTable']) && $konfig['sizeTable'] == $tab_rozmiar->id) ? 'selected="selected"' : '') . '>' . $tab_rozmiar->name . '</option>';
                                   //
                               }
                               //
                          }
                          
                          unset($uslugi_dodatkowe);
                          ?>
                                                   
                      </select><em class="TipIkona"><b>Wyświetlane są tabele rozmiarów zdefiniowane bezpośrednio w Allegro</b></em> 
                    </p>                            
                    
                    <p>
                      <label for="faktura_vat">Faktura VAT:</label>
                      <select name="konfiguracja[invoice]" id="faktura_vat">
                          <option value="" <?php echo ((isset($konfig['invoice']) && $konfig['invoice'] == '') ? 'selected="selected"' : ''); ?>>--- wybierz ---</option>
                          <option value="VAT" <?php echo ((isset($konfig['invoice']) && $konfig['invoice'] == 'VAT') ? 'selected="selected"' : ''); ?>>faktura VAT</option>
                          <option value="VAT_MARGIN" <?php echo ((isset($konfig['invoice']) && $konfig['invoice'] == 'VAT_MARGIN') ? 'selected="selected"' : ''); ?>>faktura VAT marża</option>
                          <option value="WITHOUT_VAT" <?php echo ((isset($konfig['invoice']) && $konfig['invoice'] == 'WITHOUT_VAT') ? 'selected="selected"' : ''); ?>>faktura bez VAT</option>
                          <option value="NO_INVOICE" <?php echo ((isset($konfig['invoice']) && $konfig['invoice'] == 'NO_INVOICE') ? 'selected="selected"' : ''); ?>>nie wystawiam faktury</option>
                      </select>
                    </p>                      
                                  
                    </div>
                    
                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Zapisz dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('konfiguracja_uzytkownicy','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','allegro');">Powrót</button>           
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
