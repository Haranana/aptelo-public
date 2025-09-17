<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

        $zalaczniki = '';

        if ( isset($_POST["zalacznik"]) ) {
            foreach($_POST["zalacznik"] as $key => $val){
                if(empty($val)){
                    unset($_POST["zalacznik"][$key]);
                }
            }
            $zalaczniki = implode(';', (array)$_POST["zalacznik"]);
        }
        //
        $pola = array(
                    array('sender_name',$filtr->process($_POST["nadawca_nazwa"])),
                    array('sender_email',$filtr->process($_POST["nadawca_email"])),
                    array('dw',$filtr->process($_POST["cc_email"])),
                    array('email_group',$filtr->process($_POST["grupa"])),
                    array('template_id',$filtr->process($_POST["szablon"])),
                    array('email_file',$zalaczniki),
                    array('email_send',(int)$_POST['wyslij_mail']),
                    array('email_service',(int)$_POST['opiekun']));
                    
        //
        $db->update_query('email_text' , $pola, " email_text_id = '".(int)$_POST["id"]."'");	
        unset($pola);             

        //
        $db->delete_query('email_text_description', "email_text_id = '".(int)$_POST["id"]."'");

        $ile_jezykow = Funkcje::TablicaJezykow();
        for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                //
                if (!empty($_POST['edytor_'.$w])) {
                        $pola = array(
                                    array('email_text_id',(int)$_POST["id"]),
                                    array('email_title',$filtr->process($_POST['tytul_'.$w])),
                                    array('description',$filtr->process($_POST['edytor_'.$w])),
                                    array('description_sms',$filtr->process($_POST['sms_'.$w])),
                                    array('language_id',$ile_jezykow[$w]['id'])
                         );
                } else {
                        $pola = array(
                                    array('email_text_id',(int)$_POST["id"]),
                                    array('email_title',$filtr->process($_POST['tytul_0'])),
                                    array('description',$filtr->process($_POST['edytor_0'])),
                                    array('description_sms',$filtr->process($_POST['sms_0'])),
                                    array('language_id',$ile_jezykow[$w]['id'])
                         );
                }
                $sql = $db->insert_query('email_text_description' , $pola);
                unset($pola);
        }                
        //
        Funkcje::PrzekierowanieURL('emaile_tresci.php?id_poz='.(int)$_POST["id"]);

    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
        
    <div id="naglowek_cont">Edycja pozycji</div>
    <div id="cont">

    <script>
    $(document).ready(function() {
        $("#slownikForm").validate({
            rules: {
                tytul_0: {
                    required: true
                },                            
                nadawca_nazwa: {
                    required: true
                },                            
                nadawca_email: {
                    required: true
                }                           
            }
        });
    });

    function dodaj_zalacznik() {
        var ile_pol = parseInt($("#ile_pol").val()) + 1;
        //
        $('#wyniki').append('<div id="wyniki'+ile_pol+'"></div>');
        //
        $.get('ajax/dodaj_zalacznik.php', { id: ile_pol, katalog: 'pobieranie' }, function(data) {
            $('#wyniki'+ile_pol).html(data);
            $("#ile_pol").val(ile_pol);
            //
            pokazChmurki();  
        });
    } 
    function usun_zalacznik(id) {
        $('.tip-twitter').css({'visibility':'hidden'});
        $('#wyniki' + id).remove();
    }
    </script>     

    <div class="poleForm">
        <div class="naglowek">Edycja danych</div>    

        <?php
            
        if ( !isset($_GET['id_poz']) ) {
             $_GET['id_poz'] = 0;
        }        
                            
        $zapytanie = "SELECT t.email_service, t.email_send, t.email_var_id, t.email_text_id, t.text_name, t.sender_name, t.sender_email, t.template_id, t.dw, t.email_group, t.email_file, tz.email_title, tz.description FROM email_text t LEFT JOIN email_text_description tz ON tz.email_text_id = t.email_text_id AND tz.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."' WHERE t.email_text_id = '" . (int)$_GET['id_poz'] . "'";

        $sql = $db->open_query($zapytanie);
        
        $pokazObjasnienia = false;

        if ((int)$db->ile_rekordow($sql) > 0) {
            
            $info = $sql->fetch_assoc();

            $dowolny = false;
            
            if ( substr((string)$info['email_var_id'], 0, 8) == 'DOWOLNY_' ) {
                 $dowolny = true;
            }             
            
            $pokazObjasnienia = true;
            
            if ( $dowolny == false ) {
            ?>

            <form action="slowniki/emaile_tresci_edytuj.php" method="post" id="slownikForm" class="cmxform"> 
            
            <div class="pozycja_edytowana">
            
                <input type="hidden" name="akcja" value="zapisz" />
                
                <input type="hidden" name="id" value="<?php echo (int)$_GET['id_poz']; ?>" />

                <?php $ile_jezykow = Funkcje::TablicaJezykow(); ?>

                <div class="info_tab">
                <?php
                for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                        echo '<span id="link_'.$w.'" class="a_href_info_tab" onclick="gold_tabs(\''.$w.'\',\'edytor_\', 300)">'.$ile_jezykow[$w]['text'].'</span>';
                }                                        
                ?>                                     
                </div>
                
                <div style="clear:both"></div>
                
                <div class="info_tab_content">
                    <?php
                    for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                    
                        // pobieranie danych jezykowych
                        $zapytanie_jezyk = "select distinct * from email_text_description where email_text_id = '".(int)$_GET['id_poz']."' and language_id = '" .$ile_jezykow[$w]['id']."'";
                        $sqls = $db->open_query($zapytanie_jezyk);
                        $nazwa = $sqls->fetch_assoc();     
                        
                        ?>
                        
                        <div id="info_tab_id_<?php echo $w; ?>" style="display:none;">
                        
                            <p>
                                <label <?php echo ( $w == '0' ? 'class="required"' : '' ); ?>  for="tytul_<?php echo $w; ?>" >Tytuł emaila:</label>
                                <input type="text" name="tytul_<?php echo $w; ?>" size="90" value="<?php echo (isset($nazwa['email_title']) ? $nazwa['email_title'] : ''); ?>" id="tytul_<?php echo $w; ?>" />
                            </p>

                            <div class="edytor">
                                <textarea cols="50" rows="30" id="edytor_<?php echo $w; ?>" name="edytor_<?php echo $w; ?>"><?php echo (isset($nazwa['description']) ? $nazwa['description'] : ''); ?></textarea>
                            </div>     
                                
                            <?php 
                            $Ukryj = '';
                            if ( $info['email_var_id'] != 'EMAIL_ZMIANA_STATUSU_ZAMOWIENIA' && $info['email_var_id'] != 'EMAIL_ZAMOWIENIE' ) {
                                $Ukryj = 'style="display:none"';
                            }
                            ?>
                            <p <?php echo $Ukryj; ?>>
                                <label for="sms_<?php echo $w; ?>">Treść wiadomości SMS:</label>
                                <input type="text" name="sms_<?php echo $w; ?>" size="140" value="<?php echo (isset($nazwa['description_sms']) ? $nazwa['description_sms'] : ''); ?>" id="sms_<?php echo $w; ?>" />
                            </p>

                            <?php
                            $db->close_query($sqls);
                            unset($nazwa); 
                            ?>

                        </div>
                        <?php                                        
                    }                                        
                    ?>                                            
                </div>
                
                <script>
                gold_tabs('0','edytor_', 300);
                </script> 

                <p>
                    <label class="required" for="nadawca_nazwa">Nadawca nazwa:</label>
                    <input type="text" name="nadawca_nazwa" size="60" value="<?php echo $info['sender_name']; ?>" id="nadawca_nazwa" />
                </p>
                
                <div class="maleInfo odlegloscRwdTab">domyślnie {INFO_NAZWA_SKLEPU} - nazwa firmy zdefiniowana w menu Konfiguracja / Komunikacja / Ustawienia email / Nazwa Twojego sklepu - podawana również jako nadawca maila</div>

                <p>
                    <label class="required" for="nadawca_email">Nadawca email:</label>
                    <input type="text" name="nadawca_email" size="60" value="<?php echo $info['sender_email']; ?>" id="nadawca_email" />
                </p>
                
                <div class="maleInfo odlegloscRwdTab">domyślnie {INFO_EMAIL_SKLEPU} - adres email zdefiniowany w menu Konfiguracja / Komunikacja / Ustawienia email / Głowny adres e-mail sklepu</div>

                <p>
                    <label for="cc_email">Prześlij do wiadomości:</label>
                    <input type="text" name="cc_email" size="60" value="<?php echo $info['dw']; ?>" id="cc_email" />
                </p>

                <p>
                    <label for="grupa">Grupa:</label>
                    <?php
                    $tablica[] = array('id' => 'E-maile do klientów sklepu', 'text' => 'E-maile do klientów sklepu');
                    $tablica[] = array('id' => 'E-maile administratora', 'text' => 'E-maile administratora');
                    echo Funkcje::RozwijaneMenu('grupa', $tablica, $info['email_group'], 'id="grupa"' ); 
                    unset($tablica);
                    ?>
                </p>

                <p>
                    <label for="szablon">Szablon emaila:</label>
                    <?php
                    $tablica = Funkcje::ListaSzablonowEmail(false);
                    echo Funkcje::RozwijaneMenu('szablon', $tablica, $info['template_id'], 'id="szablon"' ); ?>
                </p>

                <!-- Zalaczniki do maila -->
                <div id="wyniki">
                <?php
                    $ile_zalacznikow = 0;
                    $tablicaZalacznikow = explode(';', (string)$info['email_file']);
                    foreach($tablicaZalacznikow as $key => $val){
                        if( empty($val) ){
                            unset($tablicaZalacznikow[$key]);
                        }
                    }

                    if ( count($tablicaZalacznikow) > 0 ) {
                        $l = 1;
                        $ile_zalacznikow = count($tablicaZalacznikow);
                        foreach ( $tablicaZalacznikow as $zalacznik ) {
                            ?>
                            <div id="wyniki<?php echo $l; ?>">
                                <p>
                                <label  for="zalacznik_<?php echo $l; ?>">Plik załącznika:</label>
                                <input type="text" name="zalacznik[]" size="60" ondblclick="openFileBrowser('zalacznik_<?php echo $l; ?>','','pobieranie')" id="zalacznik_<?php echo $l; ?>" value="<?php echo $zalacznik; ?>" autocomplete="off" /><em class="TipIkona"><b>Kliknij dwukrotnie w to pole żeby otworzyć okno przeglądarki plików</b></em>
                                <em class="TipChmurka"><b>Skasuj</b><span class="UsunZalacznik" onclick="usun_zalacznik('<?php echo $l; ?>')"></span></em>
                                <span class="PrzegladarkaZdjec TipChmurka" onclick="openFileBrowser('zalacznik_<?php echo $l; ?>','','pobieranie')"><b>Kliknij żeby otworzyć okno przeglądarki zdjęć</b></span>
                                </p>
                            </div>
                            <?php
                            $l++;
                        }
                    }
                ?>
                </div>
                
                <?php if ( $info['email_var_id'] == 'EMAIL_ZAMOWIENIE' ) { ?>
                
                <p>
                  <label>Wysyłaj tego maila po złożeniu zamówienia:</label>
                  <input type="radio" name="wyslij_mail" id="wyslij_mail_tak" value="1" <?php echo (($info['email_send'] == '1') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="wyslij_mail_tak">tak</label>
                  <input type="radio" name="wyslij_mail" id="wyslij_mail_nie" value="0" <?php echo (($info['email_send'] == '0') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="wyslij_mail_nie">nie</label>
                </p>    

                <p>
                  <label>Wysyłaj maila z kopią zamówienia na adres email opiekuna przepisanego do klienta składającego zamówienie (tylko jeżeli klient ma przypisanego opiekuna):</label>
                  <input type="radio" name="opiekun" id="opiekun_tak" value="1" <?php echo (($info['email_service'] == '1') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="opiekun_tak">tak</label>
                  <input type="radio" name="opiekun" id="opiekun_nie" value="0" <?php echo (($info['email_service'] == '0') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="opiekun_nie">nie</label>
                </p>                        
                
                <?php } else { ?>
                
                <input type="hidden" name="wyslij_mail" value="1" /> 
                <input type="hidden" name="opiekun" value="0" /> 
                
                <?php } ?>

            </div>

            <input value="<?php echo ($ile_zalacznikow > 0 ? $ile_zalacznikow : '0'); ?>" type="hidden" name="ile_pol" id="ile_pol" />

            <div style="padding:10px;padding-top:20px;padding-left:30px;">
                <span class="dodaj" onclick="dodaj_zalacznik()" style="cursor:pointer">dodaj plik do dołączenia do maila</span>
            </div>   

            <div class="przyciski_dolne">
                <input type="submit" class="przyciskNon" value="Zapisz dane" />
                <button type="button" class="przyciskNon" onclick="cofnij('emaile_tresci','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','slowniki');">Powrót</button>     
            </div>

            </form>                

            <?php
            
            } else {
            
            echo '<div class="pozycja_edytowana">Brak danych do wyświetlenia</div>';
            
            } ?>
            
        <?php

        $db->close_query($sql);
                    
        } else {

            echo '<div class="pozycja_edytowana">Brak danych do wyświetlenia</div>';

        }
    ?>
    
    </div>   

    <?php
    if ( $pokazObjasnienia == true ) {
    ?>    

    <div class="objasnienia">
        <div class="objasnieniaTytul">Znaczniki, które możesz użyć w tym e-mailu (w treści maila):</div>
        <div class="objasnieniaTresc">

        <div style="padding-bottom:10px;font-weight:bold;">Treść wiadomości</div>
            <ul class="mcol">

                <?php if ( $info['email_var_id'] == 'EMAIL_REJESTRACJA_KLIENTA_KONTO_AKTYWNE' ) { ?>
                    <li><b>{LINK}</b> - Link do strony logowania w sklepie</li>
                    <li><b>{LOGIN}</b> - Login zarejestrowanego klienta</li>
                    <li><b>{HASLO}</b> - Hasło zarejestrowanego klienta</li>
                    <li><b>{BIEZACA_DATA}</b> - Data wygenerowania wiadomości</li>
                    <li><b>{KLIENT_IP}</b> - IP komputera z którego rejestrował się klient</li>
                <?php } ?>
                
                <?php if ( $info['email_var_id'] == 'EMAIL_AKTYWACJA_KONTA' ) { ?>
                    <li><b>{LINK}</b> - Link do strony logowania w sklepie</li>
                <?php } ?>                
                
                <?php if ( $info['email_var_id'] == 'EMAIL_AKTYWACJA_KONTA_PRZEZ_KLIENTA' ) { ?>
                    <li><b>{LINK_DO_POTWIERDZENIA_REJESTRACJI}</b> - Link do strony gdzie klient będzie mógł aktywować konto (sam link do znacznika &lt;a href)</li>
                    <li><b>{EMAIL_KLIENTA}</b> - Adres email zarejestrowanego klienta</li>                    
                <?php } ?>                      

                <?php if ( $info['email_var_id'] == 'EMAIL_PRZYPOMNIENIE_HASLA' || $info['email_var_id'] == 'EMAIL_PRZYPOMNIENIE_HASLA_KLIENTA' ) { ?>
                    <li><b>{LINK}</b> - Link do strony zmiany hasła</li>
                    <li><b>{BIEZACA_DATA}</b> - Aktualna data</li>
                    <li><b>{KLIENT_IP}</b> - Adres IP komputerza klienta</li>
                <?php } ?>
                <?php if ( $info['email_var_id'] == 'EMAIL_PRZYPOMNIENIE_HASLA_KLIENTA' ) { ?>
                    <li><b>{HASLO}</b> - Wygenerowane hasło do logowania do sklepu</li>
                <?php } ?>

                <?php if ( $info['email_var_id'] == 'EMAIL_ZMIANA_STATUSU_ZAMOWIENIA' ) { ?>
                    <li><b>{NUMER_ZAMOWIENIA}</b> - Numer zamówienia (może być użyte również w tytule maila)</li>
                    <li><b>{STATUS_ZAMOWIENIA}</b> - Status zamówienia</li>
                    <li><b>{DATA_ZAMOWIENIA}</b> - Data złożenia zamówienia</li>
                    <li><b>{KOMENTARZ}</b> - Komentarz dołączany przez admina</li>
                    <li><b>{LINK}</b> - Adres do historii zamówień klienta</li>
                    <li><b>{NR_PRZESYLKI}</b> - Numer dokumentu przewozowego firmy kurierskiej</li>
                    <li><b>{LINK_SLEDZENIA_PRZESYLKI}</b> - Link do strony śledzenia przesyłki</li>
                    <li><b>{WARTOSC_ZAMOWIENIA}</b> - Wartość zamówienia</li>
                    <li><b>{ILOSC_PUNKTOW}</b> - Ilość punktów za zamówienie</li>
                    <li><b>{DOKUMENT_SPRZEDAZY}</b> - Dokument sprzedaży do zamówienia: paragon lub faktura</li>
                    <li><b>{FORMA_PLATNOSCI}</b> - Wybrana przez klienta forma płatności za zamówienie</li>
                    <li><b>{FORMA_WYSYLKI}</b> - Wybrana przez klienta forma wysyłki zamówienia</li>                    
                    <li><b>{POMIN_ALLEGRO_START} ........... {POMIN_ALLEGRO_KONIEC}</b> - Znaczniki które umożliwiają usunięcie informacji dla zamówień z Allegro. Treść zawarta pomiędzy tymi znacznikami nie będzie wysyłana na maila w przypadku jeżeli zamówienie pochodzi z Allegro</li>     
                    
                <?php } ?>

                <?php if ( $info['email_var_id'] == 'EMAIL_ZMIANA_STATUSU_REKLAMACJI' ) { ?>
                    <li><b>{NUMER_REKLAMACJI}</b> - Numer reklamacji (może być użyte również w tytule maila)</li>
                    <li><b>{DATA_REKLAMACJI}</b> - Data zgłoszenia reklamacji</li>
                    <li><b>{STATUS_REKLAMACJI}</b> - Status reklamacji</li>
                    <li><b>{KOMENTARZ}</b> - Komentarz dołączany przez admina</li>
                    <li><b>{LINK}</b> - Adres do historii reklamacji klienta</li>
                <?php } ?>
                
                <?php if ( $info['email_var_id'] == 'EMAIL_ZMIANA_STATUSU_ZWROTU' ) { ?>
                    <li><b>{NUMER_ZWROTU}</b> - Numer zwrotu (może być użyte również w tytule maila)</li>
                    <li><b>{DATA_ZWROTU}</b> - Data zgłoszenia zwrotu</li>
                    <li><b>{STATUS_ZWROTU}</b> - Status zwrotu</li>
                    <li><b>{KOMENTARZ}</b> - Komentarz dołączany przez admina</li>
                    <li><b>{LINK}</b> - Adres do historii zwrotu klienta</li>
                <?php } ?>                

                <?php if ( $info['email_var_id'] == 'EMAIL_ZMIANA_STATUSU_PUNKTOW' ) { ?>
                    <li><b>{STATUS_PUNKTOW}</b> - Status punktów</li>
                    <li><b>{DATA_PUNKTOW}</b> - Data dopisania punktów do tabeli punktów klienta</li>
                    <li><b>{ILOSC_PUNKTOW}</b> - Ilość punktów których dotyczy zmiana statusu</li>
                    <li><b>{OGOLNA_ILOSC_PUNKTOW}</b> - Ogólna ilość punktów klienta</li>
                    <li><b>{KOMENTARZ}</b> - Komentarz dołączany przez admina</li>
                <?php } ?>

                <?php if ( $info['email_var_id'] == 'EMAIL_DODANIE_RECZNE_PUNKTOW' ) { ?>
                    <li><b>{STATUS_PUNKTOW}</b> - Status punktów</li>
                    <li><b>{ILOSC_PUNKTOW}</b> - Ilość punktów których dotyczy zmiana statusu</li>
                    <li><b>{OGOLNA_ILOSC_PUNKTOW}</b> - Ogólna ilość punktów klienta</li>
                    <li><b>{KOMENTARZ}</b> - Komentarz dołączany przez admina</li>
                <?php } ?>

                <?php if ( $info['email_var_id'] == 'EMAIL_POTWIERDZENIE_EMAIL_NEWSLETTERA' ) { ?>
                    <li><b>{LINK} tutaj tekst {/LINK}</b> - Link do potwierdzenia subskrypcji newslettera</li>
                <?php } ?>

                <?php if ( $info['email_var_id'] == 'EMAIL_REKLAMACJA_ZGLOSZENIE' ) { ?>
                    <li><b>{LINK}</b> - Link do strony ze szczegółami zgłoszenia</li>
                    <li><b>{KLIENT}</b> - Imię i nazwisko klienta</li>
                    <li><b>{NUMER_ZAMOWIENIA}</b> - Numer zamówienia</li>
                    <li><b>{NUMER_REKLAMACJI}</b> - Numer zgłoszenia reklamacji</li>
                    <li><b>{TYTUL_REKLAMACJI}</b> - Tytuł zgłoszenia reklamacji</li>
                    <li><b>{OPIS_REKLAMACJI}</b> - Opis zgłoszenia reklamacji</li>
                    <li><b>{BIEZACA_DATA}</b> - Data wysłania wiadomości</li>
                    <li><b>{KLIENT_IP}</b> - IP komputera z którego było wysłane zgłoszenie</li>
                <?php } ?>
                
                <?php if ( $info['email_var_id'] == 'EMAIL_ZWROT_ZGLOSZENIE' ) { ?>
                    <li><b>{LINK}</b> - Link do strony ze szczegółami zgłoszenia</li>
                    <li><b>{KLIENT}</b> - Imię i nazwisko klienta</li>
                    <li><b>{NUMER_ZAMOWIENIA}</b> - Numer zamówienia</li>
                    <li><b>{NUMER_ZWROTU}</b> - Numer zgłoszenia zwrotu</li>
                    <li><b>{BIEZACA_DATA}</b> - Data wysłania wiadomości</li>
                    <li><b>{KLIENT_IP}</b> - IP komputera z którego było wysłane zgłoszenie</li>
                <?php } ?>                

                <?php if ( $info['email_var_id'] == 'EMAIL_ZAMOWIENIE' ) { ?>
                    <li><b>{IMIE_NAZWISKO_KUPUJACEGO}</b> - Imię i nazwisko osoby kupującej</li>
                    <li><b>{EMAIL_KUPUJACEGO}</b> - Adres email osoby kupującej</li>
                    <li><b>{TELEFON_KUPUJACEGO}</b> - Nr telefonu osoby kupującej</li>
                    <li><b>{LINK}</b> - Link do strony ze szczegółami zgłoszenia</li>
                    <li><b>{NUMER_ZAMOWIENIA}</b> - Numer zamówienia (może być użyte również w tytule maila)</li>
                    <li><b>{DATA_ZAMOWIENIA}</b> - Data złożenia zamówienia</li>
                    <li><b>{FORMA_PLATNOSCI}</b> - Wybrana przez klienta forma płatności za zamówienie</li>
                    <li><b>{OPIS_FORMY_PLATNOSCI}</b> - Informacja do wybranej przez klienta formy płatności - np numer konta bankowego</li>
                    <li><b>{FORMA_WYSYLKI}</b> - Wybrana przez klienta forma wysyłki zamówienia</li>
                    <li><b>{OPIS_FORMY_WYSYLKI}</b> - Informacja do wybranej przez klienta formie wysyłki - np miejsce odbioru osobistego</li>                                        
                    <li><b>{WAGA_PRODUKTOW}</b> - Waga produktów w postaci xx,xxx </li>
                    <li><b>{DOKUMENT_SPRZEDAZY}</b> - Dokument sprzedaży - faktura lub paragon</li>
                    <li><b>{LINK_PLIKOW_ELEKTRONICZNYCH}</b> - Link wraz z informacją do pobrania plików elektronicznych lub kodów licencyjnych - używane tylko przy sprzedaży produktów online</li>
                    <li><b>{MODULY_PODSUMOWANIA}</b> - Poszczególne pozycje zamówienia: wartość produktów, koszty wysyłki, zniżki, rabaty, ogólna wartość zamówienia etc</li>
                    <li><b>{KOMENTARZ_DO_ZAMOWIENIA}</b> - Komentarz dodany przez klienta do zamówienia</li>
                    <li><b>{ADRES_ZAMAWIAJACEGO}</b> - Dane adresowe klienta</li>
                    <li><b>{ADRES_DOSTAWY}</b> - Adres dostawy produktów</li>
                    <li><b>{TELEFON_DOSTAWY}</b> - Nr telefonu dla adresu dostawy produktów</li>
                    <li><b>{ADRES_EMAIL_ZAMAWIAJACEGO}</b> - Adres email klienta</li>
                    <li><b>{POMIN_ALLEGRO_START} ........... {POMIN_ALLEGRO_KONIEC}</b> - Znaczniki które umożliwiają usunięcie informacji dla zamówień z Allegro. Treść zawarta pomiędzy tymi znacznikami nie będzie wysyłana na maila w przypadku jeżeli zamówienie pochodzi z Allegro</li>     
                <?php } ?>      

                <?php if ( $info['email_var_id'] == 'EMAIL_NOWY_KLIENT' ) { ?>
                    <li><b>{DANE_KLIENTA}</b> - Dane zarejestrowanego klienta</li>
                    <li><b>{EMAIL_KLIENTA}</b> - Adres email zarejestrowanego klienta</li>
                    <li><b>{STATUS_KLIENTA}</b> - Czy konto klienta jest aktywne (tak / nie)</li>
                    <li><b>{DODATKOWE_POLA}</b> - Dodatkowe pola do klientów</li>
                <?php } ?>                
                
                <?php if ( $info['email_var_id'] == 'OPINIA_O_SKLEPIE' ) { ?>
                    <li><b>{LINK_DO_FORMULARZA_OPINII}</b> - Link do strony gdzie klient można napisać opinię o sklepie (sam link do znacznika &lt;a href)</li>
                <?php } ?>

                <?php if ( $info['email_var_id'] == 'RECENZJA_O_PRODUKTACH' ) { ?>
                    <li><b>{ILOSC_PKT_ZA_RECENZJE}</b> - Ilość pkt jaką klient otrzyma za napisanie recenzji (wartość liczbowa)</li>
                    <li><b>{LINKI_DO_RECENZJI}</b> - Linki do podstron gdzie klient może napisać recenzje dla poszczególnych produktów</li>
                <?php } ?>                 

                <?php if ( $info['email_var_id'] == 'EMAIL_ZAMOWIENIE_KONTAKT' ) { ?>
                    <li><b>{IMIE_NAZWISKO_KLIENTA}</b> - Imię i nazwisko klienta</li>
                    <li><b>{EMAIL_KLIENTA}</b> - Adres email klienta</li>
                    <li><b>{TELEFON_KLIENTA}</b> - Nr telefonu klienta</li>
                    <li><b>{KOMENTARZ_DO_ZAMOWIENIA}</b> - Komentarz dodany przez klienta do zamówienia</li>
                    <li><b>{NAZWA_PRODUKTU}</b> - Nazwa zakupionego produktu</li>
                    <li><b>{NUMER_ZAMOWIENIA}</b> - Numer zamówienia do kontaktu (może być użyte również w tytule maila)</li>
                <?php } ?>           

                <?php if ( $info['email_var_id'] == 'EMAIL_POWIADOMIENIE_O_PRODUKCIE' ) { ?>
                    <li><b>{NAZWA_PRODUKTU}</b> - Nazwa produktu do którego jest wysyłane powiadomienie o dostępności (może być użyte również w tytule maila)</li>
                    <li><b>{LINK}</b> - Link do strony produktu w sklepie</li>
                <?php } ?>    

                <?php if ( $info['email_var_id'] == 'EMAIL_WYGENEROWANA_FAKTURA' || $info['email_var_id'] == 'EMAIL_WYGENEROWANY_PARAGON' ) { ?>
                    <li><b>{NUMER_ZAMOWIENIA}</b> - Numer zamówienia</li>
                <?php } ?>    

                <?php if ( $info['email_var_id'] == 'EMAIL_ZMIANA_DATY_WYSYLKI' ) { ?>
                    <li><b>{NUMER_ZAMOWIENIA}</b> - Numer zamówienia</li>
                    <li><b>{NOWA_DATA_WYSYLKI}</b> - Nowy termin wysyłki (w miejsce tego znacznika zostanie wstawiona nowa data)</li>
                <?php } ?>    
                
                <?php if ( $info['email_var_id'] == 'EMAIL_NAPISANY_KOMENTARZ_ARTYKULU' ) { ?>
                    <li><b>{LINK_ARTYKULU}</b> - Link do artykułu w panelu zarządzania</li>
                    <li><b>{NICK}</b> - Nick klienta</li>
                    <li><b>{ADRES_EMAIL}</b> - Adres email klienta</li>
                    <li><b>{TELEFON}</b> - Telefon klienta</li>
                    <li><b>{KOMENTARZ}</b> - Komentarz do artykułu</li>
                <?php } ?>                     

                <li><b>{ADRES_URL_SKLEPU}</b> - Adres internetowy sklepu</li>
                <li><b>{INFO_NAZWA_SKLEPU}</b> - Nazwa sklepu</li>
            </ul>
            
            <?php if ( $info['email_var_id'] == 'EMAIL_ZAMOWIENIE' ) { ?>
            
            <div style="padding-bottom:10px;font-weight:bold;">Zamówione produkty w formie tabeli</div>
            <ul class="mcol">
            
                <li><b>{LISTA_PRODUKTOW}</b> - Lista zamówionych przez klienta produktów w formie tabeli: nazwa produktu | nr katalogowy | cena jednostkowa | ilość | wartość brutto</li>
                
            </ul>
            
            <div style="padding-bottom:10px;font-weight:bold;">Zamówione produkty w rozbiciu na pozycje</div>
            <ul class="mcol">
            
                <li><b>{ZAMOWIONE_PRODUKTY_START} ........... {ZAMOWIONE_PRODUKTY_KONIEC}</b> - Znaczniki wewnętrz których umieszczane są dane zakupionych produktów</li>
                <li><b>{NAZWA_PRODUKTU}</b> - Nazwa zakupionego produktu</li>
                <li><b>{PRODUKT_NR_KATALOGOWY}</b> - Numer katalogowy zakupionego produktu</li>
                <li><b>{PRODUKT_KOD_EAN}</b> - Kod EAN zakupionego produktu</li>
                <li><b>{PRODUKT_CENA_JEDNOSTKOWA_BRUTTO}</b> - Cena jednostkowa brutto zakupionego produktu</li>
                <li><b>{PRODUKT_KUPIONA_ILOSC}</b> - Ilość zakupionego produktu</li>
                <li><b>{PRODUKT_WARTOSC_BRUTTO}</b> - Wartość brutto zakupionego produktu</li>
                
            </ul>            
            
            <div class="maleInfo" style="margin:0">Przykładowe zastosowanie znaczników:</div>
                
            <div style="font-family:Courier;margin-bottom:20px;color:#444">
            {ZAMOWIONE_PRODUKTY_START} <br />
            {NAZWA_PRODUKTU} <br />
            Nr katalogowy: {PRODUKT_NR_KATALOGOWY} <br />
            Zakupiona ilość: {PRODUKT_KUPIONA_ILOSC}  <br />
            Wartość produktów: {PRODUKT_WARTOSC_BRUTTO} <br />
            {ZAMOWIONE_PRODUKTY_KONIEC}
            </div>
            
            <?php } ?>

            <div style="padding-bottom:10px;font-weight:bold;">Dane sklepu</div>
            <ul class="mcol">
                <?php
                $zapytanie = "SELECT * FROM settings WHERE type = 'firma' OR type = 'sklep' ORDER BY type, sort";

                $sql = $db->open_query($zapytanie);

                while ($info = $sql->fetch_assoc()) {
                    echo '<li><b>{'.$info['code'].'}</b> - '.$info['description'].'</li>';
                }
                $db->close_query($sql);
                unset($zapytanie,$info);

                ?>
            </ul>
        
        </div> 
    </div>

    <?php
    }
    unset($pokazObjasnienia);
    ?>    

    </div> 
    
    <?php
    unset($info);                        
    include('stopka.inc.php');

}
