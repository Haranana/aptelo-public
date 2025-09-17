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

        }

        $TimeStamp = time();
        $pola = array(
                array('allegro_user_login',$filtr->process($_POST["user_name"])),
                array('allegro_user_clientid',$filtr->process($_POST["client_id"])),
                array('allegro_user_clientsecret',$filtr->process($_POST["client_secret"])),
                array('allegro_token_expires',$TimeStamp),
                array('allegro_user_status',$_POST["status"]),
                array('allegro_user_type',$_POST["typ"]),
                array('allegro_user_default',(int)$_POST["domyslny"]),
                array('allegro_additionalinfo',$filtr->process($_POST["additionalinfo"])),
                array('allegro_user_city',$filtr->process($_POST["city"])),
                array('allegro_user_postcode',$filtr->process($_POST["postcode"])),
                array('allegro_user_province',$filtr->process($_POST["province"]))
        );
        //	
        $db->insert_query('allegro_users' , $pola);	
        $id_dodanej_pozycji = $db->last_id_query();

        if ($_POST["domyslny"] == '1') {
            $_SESSION['domyslny_uzytkownik_allegro'] = (int)$id_dodanej_pozycji;

        }
        
        unset($pola);
        
        //
        if (isset($id_dodanej_pozycji) && $id_dodanej_pozycji > 0) {
            Funkcje::PrzekierowanieURL('konfiguracja_uzytkownicy.php?id_poz='.$id_dodanej_pozycji);
        } else {
            Funkcje::PrzekierowanieURL('konfiguracja_uzytkownicy.php');
        }
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');

    $zapis = true;

    ?>
    
    <div id="naglowek_cont">Dodawanie pozycji</div>
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

          <form action="allegro/konfiguracja_uzytkownicy_dodaj.php" method="post" id="allegroForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Dodawanie danych</div>
            
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
            
                <div class="DaneAukcjaNaglowek" style="margin-bottom:10px">Dane aplikacji do połączenia z REST API Allegro</div>

                <p>
                  <label>Typ konta:</label>
                  <input type="radio" value="P" name="typ" id="typ_nie" checked="checked" /><label class="OpisFor" for="typ_nie">Prywatne</label>
                  <input type="radio" value="F" name="typ" id="typ_tak" /><label class="OpisFor" for="typ_tak">Firmowe</label>                      
                </p>

                <p>
                  <label>Status:</label>
                  <input type="radio" value="0" name="status" id="status_nie"/><label class="OpisFor" for="status_nie">nieaktywny</label>
                  <input type="radio" value="1" name="status" id="status_tak" checked="checked"  /><label class="OpisFor" for="status_tak">aktywny</label>
                </p> 
                
                <p>
                  <label class="required" for="user_name">Nazwa użytkownika w Allegro:</label>
                  <input type="text" name="user_name" size="53" value="" id="user_name" />
                </p>

                <p>
                  <label class="required" for="client_id">Client ID:</label>
                  <textarea name="client_id" id="client_id" cols="80" rows="3"></textarea>
                </p>

                <p>
                  <label class="required" for="client_secret">Client Secret:</label>
                  <textarea name="client_secret" id="client_secret" cols="80" rows="3"></textarea>
                </p>
                
                <div class="DaneAukcjaNaglowek" style="margin-bottom:10px">Dane kontaktowe</div>                          

                    <p>
                        <label for="province">Województwo:</label>
                         <select name="province" id="province">
                            <option value="DOLNOSLASKIE">dolnośląskie</option>
                            <option value="KUJAWSKO_POMORSKIE">kujawsko-pomorskie</option>
                            <option value="LUBELSKIE">lubelskie</option>
                            <option value="LUBUSKIE">lubuskie</option>
                            <option value="LODZKIE">łódzkie</option>
                            <option value="MALOPOLSKIE">małopolskie</option>
                            <option value="MAZOWIECKIE">mazowieckie</option>
                            <option value="OPOLSKIE">opolskie</option>
                            <option value="PODKARPACKIE">podkarpackie</option>
                            <option value="PODLASKIE">podlaskie</option>
                            <option value="POMORSKIE">pomorskie</option>
                            <option value="SLASKIE">śląskie</option>
                            <option value="SWIETOKRZYSKIE">świętokrzyskie</option>
                            <option value="WARMINSKO_MAZURSKIE">warmińsko-mazurskie</option>
                            <option value="WIELKOPOLSKIE">wielkopolskie</option>
                            <option value="ZACHODNIOPOMORSKIE">zachodniopomorskie</option>
                         </select>
                    </p>

                    <p>
                      <label class="required" for="city">Miasto:</label>
                      <input type="text" name="city" size="53" value="" id="city" />
                    </p>
                    <p>
                      <label class="required" for="postcode">Kod pocztowy:</label>
                      <input type="text" name="postcode" id="postcode" value="" maxlength="6" placeholder="XX-XXX" />
                    </p>

                    <p>
                      <label for="additionalinfo">Dodatkowe informacje o dostawie:</label>
                      <textarea name="additionalinfo" id="additionalinfo" cols="80" rows="3"></textarea><em class="TipIkona"><b>dodatkowe informacje o dostawie, możesz tutaj wpisać informację w formie tekstowej, którą wyświetlimy w ofercie w sekcji Dostawa i płatność</b></em>
                    </p>

                <div class="DaneAukcjaNaglowek" style="margin-bottom:10px">Pozostałe</div>  
               
                <p>
                  <label>Czy konto jest domyślne:</label>
                  <input type="radio" value="0" name="domyslny" id="domyslna_nie" /><label class="OpisFor" for="domyslna_nie">nie</label>
                  <input type="radio" value="1" name="domyslny" id="domyslna_tak" checked="checked" /><label class="OpisFor" for="domyslna_tak">tak</label>
                </p>     

                <br />
                
                <div class="maleInfo">Po zapisaniu danych konta możesz przejść do jego edycji w celu uzupełnienia domyślnych opcji wystawiania aukcji</div>

                </div>

            </div>

            <div class="przyciski_dolne">
              <?php if ( $zapis ) { ?>
                <input type="submit" class="przyciskNon" value="Zapisz dane" />
              <?php } ?>
              <button type="button" class="przyciskNon" onclick="cofnij('konfiguracja_uzytkownicy','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','allegro');">Powrót</button>   
            </div>            

          </div>                      
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}
