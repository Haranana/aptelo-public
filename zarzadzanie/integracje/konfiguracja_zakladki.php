<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    $tab_zakladki = array('pierwsza', 'druga', 'trzecia');

    $wynik  = '';
    $system = ( isset($_POST['system']) ? $_POST['system'] : '' );

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

      if ( isset($_POST['system']) && $_POST['system'] != 'konfiguracja' ) {
      
          reset($_POST);
          foreach ( $_POST as $key => $value ) {
            if ( $key != 'akcja' ) {
              //
              // usuwa http z adresu facebook
              if ( strtoupper((string)$key) == 'ZAKLADKA_FACEBOOK_PROFIL' ) {
                   $value = str_replace( array('http://','https://','http:\\','https:\\'), '', (string)$value);
              }
              //
              $pola = array(
                      array('value',stripslashes((string)$value))
              );
              $db->update_query('settings' , $pola, " code = '".strtoupper((string)$key)."'");	
              unset($pola);
            }
          }

      }
      
      if ( isset($_POST['system']) && $_POST['system'] == 'konfiguracja' ) {
      
        $pola = array(
                array('value',$_POST['konfiguracja'])
        );
        $db->update_query('settings' , $pola, " code = 'WYSUWANE_ZAKLADKI_WYSWIETLANIE'");	
        unset($pola);
        
        $pola = array(
                array('value',(int)$_POST['odleglosc_px'])
        );
        $db->update_query('settings' , $pola, " code = 'WYSUWANE_ZAKLADKI_PX'");	
        unset($pola);        

      }
      
      $wynik = '<div id="'.$system.'" class="maleSukces" style="margin-left:20px;margin-top:10px;">dane zostały zmienione</div>';

    }

    $zapytanie = "SELECT * FROM settings WHERE type = 'zakladki' ORDER BY sort ";
    $sql = $db->open_query($zapytanie);

    $parametr = array();

    if ( $db->ile_rekordow($sql) > 0 ) {
      while ($info = $sql->fetch_assoc()) {
        $parametr[$info['code']] = array($info['value'], $info['limit_values'], $info['description'], $info['form_field_type']);
      }
    }
    $db->close_query($sql);
    unset($zapytanie, $info);
    
    if ( Funkcje::czyFolderJestPusty("../cache") ) {
        clearstatcache();
        $files = glob('../cache/Cache*');
        if ( !empty($files) ) {
            foreach($files as $file){
              if(is_file($file))
                @unlink($file);
            }
        }
        unset($files);

    }    

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>

    <div id="naglowek_cont">Konfiguracja parametrów wysuwanych zakładek</div>
    <div id="cont">

      <div class="poleForm">
        <div class="naglowek">Edycja danych</div>
        
        <div class="SledzeniKonfiguracja">
        
          <form action="integracje/konfiguracja_zakladki.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="konfiguracjaForm" class="cmxform">
          
            <div>
                <input type="hidden" name="akcja" value="zapisz" />
                <input type="hidden" name="system" value="konfiguracja" />
            </div>
            
            <div class="ObramowanieForm">
            
                <table>
                
                  <tr class="DivNaglowek">
                    <td style="text-align:left" colspan="2">Konfiguracja wyświetlania wysuwanych zakładek</td>
                  </tr>                  
                
                  <tr class="SledzeniePozycja">
                    <td>
                      <label>Czy wysuwane zakładki mają być widoczne cały czas niezależnie od szerokości sklepu ?</label>
                    </td>
                    <td>
                      <input type="radio" name="konfiguracja" id="wysuwane_tak" value="tak" <?php echo (($parametr['WYSUWANE_ZAKLADKI_WYSWIETLANIE'][0] == 'tak') ? 'checked="checked"' : ''); ?> /> <label class="OpisFor" for="wysuwane_tak">tak, mają być widoczne cały czas</label> <br />
                      <input type="radio" name="konfiguracja" id="wysuwane_nie" value="nie" <?php echo (($parametr['WYSUWANE_ZAKLADKI_WYSWIETLANIE'][0] == 'nie') ? 'checked="checked"' : ''); ?> /> <label class="OpisFor" for="wysuwane_nie">mają być ukrywane jeżeli szerokość sklepu jest zbliżona do szerokości ekranu (zapobiega nachodzeniu zakładek na treść sklepu)</label>
                      <input type="radio" name="konfiguracja" id="wysuwane_mobile" value="mobile" <?php echo (($parametr['WYSUWANE_ZAKLADKI_WYSWIETLANIE'][0] == 'mobile') ? 'checked="checked"' : ''); ?> /> <label class="OpisFor" for="wysuwane_mobile">mają być ukrywane przy małych rozdzielczości ekranu (na urządzeniach mobilnych)</label>
                    </td>
                  </tr>

                  <tr class="SledzeniePozycja">
                    <td>
                      <label>Odległość zakładek od górnej krawędzi ekranu (w pikselach):</label>
                    </td>
                    <td>
                      <select name="odleglosc_px">
                          <option value="70" <?php echo (($parametr['WYSUWANE_ZAKLADKI_PX'][0] == '70') ? 'selected="selectd"' : ''); ?>>70</option>
                          <option value="90" <?php echo (($parametr['WYSUWANE_ZAKLADKI_PX'][0] == '90') ? 'selected="selectd"' : ''); ?>>90</option>
                          <option value="110" <?php echo (($parametr['WYSUWANE_ZAKLADKI_PX'][0] == '110') ? 'selected="selectd"' : ''); ?>>110</option>
                          <option value="130" <?php echo (($parametr['WYSUWANE_ZAKLADKI_PX'][0] == '130') ? 'selected="selectd"' : ''); ?>>130</option>
                          <option value="150" <?php echo (($parametr['WYSUWANE_ZAKLADKI_PX'][0] == '150') ? 'selected="selectd"' : ''); ?>>150</option>
                          <option value="170" <?php echo (($parametr['WYSUWANE_ZAKLADKI_PX'][0] == '170') ? 'selected="selectd"' : ''); ?>>170</option>
                          <option value="190" <?php echo (($parametr['WYSUWANE_ZAKLADKI_PX'][0] == '190') ? 'selected="selectd"' : ''); ?>>190</option>
                          <option value="210" <?php echo (($parametr['WYSUWANE_ZAKLADKI_PX'][0] == '210') ? 'selected="selectd"' : ''); ?>>210</option>
                          <option value="230" <?php echo (($parametr['WYSUWANE_ZAKLADKI_PX'][0] == '230') ? 'selected="selectd"' : ''); ?>>230</option>
                          <option value="250" <?php echo (($parametr['WYSUWANE_ZAKLADKI_PX'][0] == '250') ? 'selected="selectd"' : ''); ?>>250</option>
                          <option value="270" <?php echo (($parametr['WYSUWANE_ZAKLADKI_PX'][0] == '270') ? 'selected="selectd"' : ''); ?>>270</option>
                          <option value="290" <?php echo (($parametr['WYSUWANE_ZAKLADKI_PX'][0] == '290') ? 'selected="selectd"' : ''); ?>>290</option>
                          <option value="310" <?php echo (($parametr['WYSUWANE_ZAKLADKI_PX'][0] == '310') ? 'selected="selectd"' : ''); ?>>310</option>
                          <option value="330" <?php echo (($parametr['WYSUWANE_ZAKLADKI_PX'][0] == '330') ? 'selected="selectd"' : ''); ?>>330</option>
                          <option value="350" <?php echo (($parametr['WYSUWANE_ZAKLADKI_PX'][0] == '350') ? 'selected="selectd"' : ''); ?>>350</option>
                      </select>
                    </td>
                  </tr>

                  <tr>
                    <td colspan="2">
                      <div class="przyciski_dolne">
                        <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'konfiguracja' ? $wynik : '' ); ?>
                      </div>
                    </td>
                  </tr>
                </table>

            </div>
          </form>
          
        </div>          
        
        <div class="SledzenieNaglowki">

            <div class="SledzenieOkno">
            
                <div class="SledzenieDiv" data-id="allegroOcenyForm">
                    <div class="Foto"><img src="obrazki/logo/logo_allegro.png" alt="" /></div>
                    <span>Wysuwana zakładka <br /> z oceną sprzedaży z Allegro</span>
                </div>
              
            </div>            
            
            <div class="SledzenieOkno">
            
                <div class="SledzenieDiv" data-id="facebookForm">
                    <div class="Foto"><img src="obrazki/logo/logo_lubie_to.png" alt="" /></div>
                    <span>Wysuwana zakładka <br /> Facebook - Like Box</span>
                </div>
              
            </div> 

            <div class="SledzenieOkno">
            
                <div class="SledzenieDiv" data-id="ggForm">
                    <div class="Foto"><img src="obrazki/logo/logo_gg.png" alt="" /></div>
                    <span>Wysuwana zakładka GG <br /> okno komunikatora</span>
                </div>
              
            </div>   

            <div class="SledzenieOkno">
            
                <div class="SledzenieDiv" data-id="youtubeForm">
                    <div class="Foto"><img src="obrazki/logo/logo_youtube.png" alt="" /></div>
                    <span>Wysuwana zakładka <br /> Youtube</span>
                </div>
              
            </div>  

            <div class="SledzenieOkno">
            
                <div class="SledzenieDiv" data-id="twitterForm">
                    <div class="Foto"><img src="obrazki/logo/logo_twitter.png" alt="" /></div>
                    <span>Wysuwana zakładka <br /> X (Twitter)</span>
                </div>
              
            </div>   

            <div class="SledzenieOkno">
            
                <div class="SledzenieDiv" data-id="instagramForm">
                    <div class="Foto"><img src="obrazki/logo/logo_instagram.png" alt="" /></div>
                    <span>Wysuwana zakładka <br /> Instagram</span>
                </div>
              
            </div>        
            
            <div class="SledzenieOkno">
            
                <div class="SledzenieDiv" data-id="pinterestForm">
                    <div class="Foto"><img src="obrazki/logo/logo_pinterest.png" alt="" /></div>
                    <span>Wysuwana zakładka <br /> Printerest</span>
                </div>
              
            </div>                   

            <div class="SledzenieOkno">
            
                <div class="SledzenieDiv" data-id="ceneoForm">
                    <div class="Foto"><img src="obrazki/logo/logo_ceneo.png" alt="" /></div>
                    <span>Wysuwana zakładka <br /> Ceneo Sprawdź nas</span>
                </div>
              
            </div>  

            <div class="SledzenieOkno">
            
                <div class="SledzenieDiv" data-id="okazjeForm">
                    <div class="Foto"><img src="obrazki/logo/logo_opinie_okazje.png" alt="" /></div>
                    <span>Wysuwana zakładka <br /> Okazje.info Wiarygodne opinie</span>
                </div>
              
            </div>    

            <div class="SledzenieOkno">
            
                <div class="SledzenieDiv" data-id="opineoForm">
                    <div class="Foto"><img src="obrazki/logo/logo_opineo.png" alt="" /></div>
                    <span>Wysuwana zakładka <br /> OPINEO Zaufane opinie</span>
                </div>
              
            </div>   

            <div class="SledzenieOkno">
            
                <div class="SledzenieDiv" data-id="wlasneopinieForm">
                    <div class="Foto"><img src="obrazki/logo/logo_opinie_sklepu.png" alt="" /></div>
                    <span>Wysuwana zakładka <br /> Opinie o sklepie</span>
                </div>
              
            </div> 

            <?php
            // dodatkowe indywidualne zakladki
            for ($r = 1; $r <= count($tab_zakladki); $r++ ) {
                 //
                 $nazwa = $tab_zakladki[$r - 1];
                 ?>
            
                 <div class="SledzenieOkno">
                
                    <div class="SledzenieDiv" data-id="<?php echo $nazwa; ?>Form">
                        <div class="Foto"><img src="obrazki/ksiazka.png" alt="" /></div>
                        <span>Indywidualna zakładka <br /> nr <?php echo $r; ?></span>
                    </div>
                  
                 </div>             
             
                 <?php
                 unset($nazwa);
        
            }
            ?>               

        </div>
          
        <div class="cl"></div>             

        <div class="pozycja_edytowana">  

          <script>
          $(document).ready(function() {
            
            $('.SledzenieOkno .SledzenieDiv').click(function() { 
               //
               var ido = $(this).attr('data-id');
               //
               $('.SledzenieOkno .SledzenieDiv').css({ 'opacity' : 0.5 }).removeClass('OknoAktywne');
               $(this).css({ 'opacity' : 1 }).addClass('OknoAktywne');
               //
               $('.Sledzenie form').hide();
               $('#' + ido).slideDown();
               //
               $.scrollTo('#' + ido,400);
               //
            });   
                
            $("#allegroOcenyForm").validate({
              rules: {
                zakladka_allegro_opinie_id: {required: function() {var wynik = true; if ( $("input[name='zakladka_allegro_opinie_wlaczona']:checked", "#allegroOcenyForm").val() == "nie" ) { wynik = false; } return wynik; }}
              }
            });             
            $("#facebookForm").validate({
              rules: {
                zakladka_facebook_profil: {required: function() {var wynik = true; if ( $("input[name='zakladka_facebook_wlaczona']:checked", "#facebookForm").val() == "nie" ) { wynik = false; } return wynik; }}
              }
            });
            $("#ggForm").validate({
              rules: {
                zakladka_gg_profil: {required: function() {var wynik = true; if ( $("input[name='zakladka_gg_wlaczona']:checked", "#ggForm").val() == "nie" ) { wynik = false; } return wynik; }},
                zakladka_gg_numer: {required: function() {var wynik = true; if ( $("input[name='zakladka_gg_wlaczona']:checked", "#ggForm").val() == "nie" ) { wynik = false; } return wynik; }}
              }
            });    
            $("#youtubeForm").validate({
              rules: {
                zakladka_youtube_profil: {required: function() {var wynik = true; if ( $("input[name='zakladka_youtube_wlaczona']:checked", "#youtubeForm").val() == "nie" ) { wynik = false; } return wynik; }}
              }
            });    
            $("#twitterForm").validate({
              rules: {
                zakladka_twitter_profil: {required: function() {var wynik = true; if ( $("input[name='zakladka_twitter_wlaczona']:checked", "#twitterForm").val() == "nie" ) { wynik = false; } return wynik; }}
                // zakladka_twitter_widget: {required: function() {var wynik = true; if ( $("input[name='zakladka_twitter_wlaczona']:checked", "#twitterForm").val() == "nie" ) { wynik = false; } return wynik; }}
              }
            });    
            $("#instagramForm").validate({
              rules: {
                zakladka_instagram_profil: {required: function() {var wynik = true; if ( $("input[name='zakladka_instagram_wlaczona']:checked", "#instagramForm").val() == "nie" ) { wynik = false; } return wynik; }}
              }
            }); 
            $("#pinterestForm").validate({
              rules: {
                zakladka_pinterest_profil: {required: function() {var wynik = true; if ( $("input[name='zakladka_pinterest_wlaczona']:checked", "#pinterestForm").val() == "nie" ) { wynik = false; } return wynik; }}
              }
            });             
            $("#ceneoForm").validate({
              rules: {
                zakladka_ceneo_kod: {required: function() {var wynik = true; if ( $("input[name='zakladka_ceneo_wlaczona']:checked", "#ceneoForm").val() == "nie" ) { wynik = false; } return wynik; }}
              }
            });               
            $("#okazjeForm").validate({
              rules: {
                zakladka_okazje_info_kod: {required: function() {var wynik = true; if ( $("input[name='zakladka_okazje_info_wlaczona']:checked", "#okazjeForm").val() == "nie" ) { wynik = false; } return wynik; }}
              }
            });               
            $("#opineoForm").validate({
              rules: {
                zakladka_opineo_kod: {required: function() {var wynik = true; if ( $("input[name='zakladka_opineo_wlaczona']:checked", "#opineoForm").val() == "nie" ) { wynik = false; } return wynik; }}
              }
            });  
            
            <?php
            for ($r = 1; $r <= count($tab_zakladki); $r++ ) {
            ?>
            $("#<?php echo $tab_zakladki[$r - 1]; ?>Form").validate({
              rules: {
                zakladka_<?php echo $tab_zakladki[$r - 1]; ?>_ikona: {required: function() {var wynik = true; if ( $("input[name='zakladka_<?php echo $tab_zakladki[$r - 1]; ?>_wlaczona']:checked", "#<?php echo $tab_zakladki[$r - 1]; ?>Form").val() == "nie" ) { wynik = false; } return wynik; }},
                zakladka_<?php echo $tab_zakladki[$r - 1]; ?>_szerokosc: {required: function() {var wynik = true; if ( $("input[name='zakladka_<?php echo $tab_zakladki[$r - 1]; ?>_wlaczona']:checked", "#<?php echo $tab_zakladki[$r - 1]; ?>Form").val() == "nie" ) { wynik = false; } return wynik; }, number: true }
              }
            });
            <?php
            }             
            ?>
            
            <?php if ( $system != '' ) { ?>
            
            $('#<?php echo $system; ?>Form').show();
            $('.SledzenieOkno .SledzenieDiv').css({ 'opacity' : 0.5 }).removeClass('OknoAktywne');
            
            $('.SledzenieOkno .SledzenieDiv').each(function() {
               //
               var ido = $(this).attr('data-id');
               //
               if ( ido == '<?php echo $system; ?>Form' ) {
                    $(this).css({ 'opacity' : 1 }).addClass('OknoAktywne');
               }
               //
            }); 
               
            $.scrollTo('#<?php echo $system; ?>Form',400);

            setTimeout(function() {
              $('#<?php echo $system; ?>').fadeOut();
            }, 3000);
            
            <?php } ?>
            
          });
          </script> 

          <div class="Sledzenie SledzenieUkryte">
          
            <form action="integracje/konfiguracja_zakladki.php<?php echo Funkcje::Zwroc_Get(array('aktualizacja','aktualizacja_blad')); ?>" method="post" id="allegroOcenyForm" class="cmxform">
            
              <div>
                  <input type="hidden" name="akcja" value="zapisz" />
                  <input type="hidden" name="system" value="allegroOceny" />
              </div>
              
              <div class="ObramowanieForm">
              
                  <table>
                  
                    <tr class="DivNaglowek">
                      <td style="text-align:left" colspan="2">Wysuwana zakładka z ocenami z Allegro</td>
                    </tr>
                    
                    <tr><td colspan="2" class="SledzenieOpis">
                      <div>
                          Wyświetla wysuwaną zakładkę z <b>ocenami</b> z Allegro (ocena zgodności z opisem, obsługi klienta, kosztów wysyłki, ocena użytkownika)
                      </div>
                      <img src="obrazki/logo/logo_allegro.png" alt="" />
                    </td></tr> 

                    <?php 
                    $sql_allegro = $db->open_query("select code, value from settings where code LIKE '%ZAKLADKA_ALLEGRO_OPINIE_ID%'");
                    //
                    $allegro_stale = array();
                    //
                    while ($info_allegro = $sql_allegro->fetch_assoc()) {
                           $allegro_stale[$info_allegro['code']] = $info_allegro['value'];
                    }
                    //
                    $db->close_query($sql_allegro);                    
                    
                    if ( (int)$allegro_stale['ZAKLADKA_ALLEGRO_OPINIE_ID'] > 0 && Funkcje::SprawdzAktywneAllegro() ) { ?>
                    
                    <tr class="SledzeniePozycja">
                      <td>
                        <label>Pobieranie danych:</label>
                      </td>
                      <td>
                        
                        <a class="PobierzKomentarze" href="allegro/allegro_oceny.php">pobierz <b>oceny</b> z Allegro</a>

                        <?php if ( isset($_GET['oceny_aktualizacja']) && !isset($_POST['system']) ) { ?>
                        <span id="pobranieAllegro" class="maleSukces">dane zostały zapamiętane</span>
                        <?php } ?>
                        
                        <?php if ( isset($_GET['oceny_aktualizacja_blad']) && !isset($_POST['system']) ) { ?>
                        <span id="pobranieAllegro" class="ostrzezenie" style="color:#ff0000;font-weight:bold;letter-spacing:1px">sklep nie mógł pobrać ocen - pobranie ocen zakończone niepowodzeniem !!</span>
                        <?php } ?>                        
                        
                      </td>
                    </tr>     

                    <tr class="SledzeniePozycja">
                      <td>
                        <label>Oceny z Allegro:</label>
                      </td>
                      <td>
                        <?php
                        if (ZAKLADKA_ALLEGRO_OPINIE_DATA_POBRANIA != '') { 
                            //
                            echo '<span class="DataKomentarzy">dane ocen ostatnio pobierane: ' . ZAKLADKA_ALLEGRO_OPINIE_DATA_POBRANIA . '</span>';
                            //
                        } else {
                            //
                            echo '<span class="DataKomentarzy">dane ocen nie były jeszcze pobierane !</span>';
                            //
                        }
                        ?>
                      </td>
                    </tr>                    

                    <?php 
                    unset($allegro_stale);
                    } else {
                    ?>
                    
                    <tr class="SledzeniePozycja">
                      <td colspan="2" style="padding:15px 20px 15px 20px">
                        <div class="ostrzezenie" style="color:#ff0000;line-height:1.5">Nie możną wyświetlić wszystkich danych Allegro. Nie został wpisany poprawny nr użytkownika allegro lub brak jest skonfigurowanych użytkowników Allegro, użytkownicy nie są zalogowani lub wygasła ważność sesji użytkownika - <a href="allegro/konfiguracja_uzytkownicy.php">[SPRAWDŹ]</a></div>
                      </td>
                    </tr>                       
                    
                    <?php
                    }
                    ?>                  
                  
                    <tr class="SledzeniePozycja">
                      <td>
                        <label>Włącz zakładkę z ocenami allegro:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['ZAKLADKA_ALLEGRO_OPINIE_WLACZONA']['1'], $parametr['ZAKLADKA_ALLEGRO_OPINIE_WLACZONA']['0'], 'zakladka_ALLEGRO_OPINIE_wlaczona', $parametr['ZAKLADKA_ALLEGRO_OPINIE_WLACZONA']['2'], '', $parametr['ZAKLADKA_ALLEGRO_OPINIE_WLACZONA']['3'] );
                        ?>
                      </td>
                    </tr>
                    
                    <tr class="SledzeniePozycja">
                      <td>
                        <label class="required" for="zakladka_allegro_opinie_id">Numer użytkownika w portalu Allegro:</label>
                      </td>
                      <td>
                        <?php
                        echo '<input type="text" id="zakladka_allegro_opinie_id" name="zakladka_allegro_opinie_id" value="'.$parametr['ZAKLADKA_ALLEGRO_OPINIE_ID']['0'].'" size="53" /><em class="TipIkona"><b>'. $parametr['ZAKLADKA_ALLEGRO_OPINIE_ID']['2'].'</b></em>';
                        ?>
                        <span class="maleInfo">Po skonfigurowaniu połączenia z API Allegro UserID jest widoczne w menu Integracje / Allegro / Allegro - użytkownicy.</span>
                      </td>
                    </tr>   

                    <tr class="SledzeniePozycja">
                      <td>
                        <label for="zakladka_allegro_opinie_nazwa">Nazwa (login) użytkownika w portalu Allegro:</label>
                      </td>
                      <td>
                        <?php
                        echo '<input type="text" id="zakladka_allegro_opinie_nazwa" name="zakladka_allegro_opinie_nazwa" value="'.$parametr['ZAKLADKA_ALLEGRO_OPINIE_NAZWA']['0'].'" size="53" /><em class="TipIkona"><b>'. $parametr['ZAKLADKA_ALLEGRO_OPINIE_NAZWA']['2'].'</b></em>';
                        ?>
                        <span class="maleInfo">W polu należy wpisać nazwę użytkownika na Allegro (nick pod jakim są oferowane aukcje).</span>
                      </td>
                    </tr>                        
                    
                    <tr class="SledzeniePozycja">
                      <td>
                        <label>Strona po której ma się wyświetlać zakładka:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['ZAKLADKA_ALLEGRO_OPINIE_STRONA']['1'], $parametr['ZAKLADKA_ALLEGRO_OPINIE_STRONA']['0'], 'zakladka_ALLEGRO_OPINIE_strona', $parametr['ZAKLADKA_ALLEGRO_OPINIE_STRONA']['2'], '', $parametr['ZAKLADKA_ALLEGRO_OPINIE_STRONA']['3'] );
                        ?>
                      </td>
                    </tr>   
                    
                    <tr class="SledzeniePozycja">
                      <td>
                        <label for="zakladka_allegro_opinie_sort">Kolejność wyświetlania na stronie:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['ZAKLADKA_ALLEGRO_OPINIE_SORT']['1'], $parametr['ZAKLADKA_ALLEGRO_OPINIE_SORT']['0'], 'zakladka_allegro_opinie_sort', $parametr['ZAKLADKA_ALLEGRO_OPINIE_SORT']['2'], '', $parametr['ZAKLADKA_ALLEGRO_OPINIE_SORT']['3'], '', '', 'id="zakladka_allegro_opinie_sort"' );
                        ?>
                      </td>
                    </tr>  
                    
                    <tr>
                      <td colspan="2">
                        <div class="przyciski_dolne">
                          <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'allegroOceny' ? $wynik : '' ); ?>
                        </div>
                      </td>
                    </tr>

                  </table>

              </div>
            </form>
            
          </div>                   

          <div class="Sledzenie SledzenieUkryte">
          
            <form action="integracje/konfiguracja_zakladki.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="facebookForm" class="cmxform">
            
              <div>
                  <input type="hidden" name="akcja" value="zapisz" />
                  <input type="hidden" name="system" value="facebook" />
              </div>
              
              <div class="ObramowanieForm">
              
                  <table>
                  
                    <tr class="DivNaglowek">
                      <td style="text-align:left" colspan="2">Wysuwana zakładka Facebook - Like Box</td>
                    </tr>
                    
                    <tr><td colspan="2" class="SledzenieOpis">
                      <div>Wyświetla wysuwaną zakładkę z treścią z Facebook w formie Like Box.</div>
                      <img src="obrazki/logo/logo_lubie_to.png" alt="" />
                    </td></tr>                  
                  
                    <tr class="SledzeniePozycja">
                      <td>
                        <label>Włącz zakładkę Facebook:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['ZAKLADKA_FACEBOOK_WLACZONA']['1'], $parametr['ZAKLADKA_FACEBOOK_WLACZONA']['0'], 'zakladka_facebook_wlaczona', $parametr['ZAKLADKA_FACEBOOK_WLACZONA']['2'], '', $parametr['ZAKLADKA_FACEBOOK_WLACZONA']['3'] );
                        ?>
                      </td>
                    </tr>
                    
                    <tr class="SledzeniePozycja">
                      <td>
                        <label class="required" for="zakladka_facebook_profil">Adres profilu strony na Facebook:</label>
                      </td>
                      <td>
                        <?php
                        echo '<input type="text" id="zakladka_facebook_profil" name="zakladka_facebook_profil" value="'.$parametr['ZAKLADKA_FACEBOOK_PROFIL']['0'].'" size="53" /><em class="TipIkona"><b>'. $parametr['ZAKLADKA_FACEBOOK_PROFIL']['2'].'</b></em>';
                        ?>
                        <span class="maleInfo">adres profilu facebook w postaci adresu np www.facebook.com/platform - bez http:</span>
                      </td>
                    </tr>    

                    <tr class="SledzeniePozycja">
                      <td>
                        <label>Strona po której ma się wyświetlać zakładka:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['ZAKLADKA_FACEBOOK_STRONA']['1'], $parametr['ZAKLADKA_FACEBOOK_STRONA']['0'], 'zakladka_facebook_strona', $parametr['ZAKLADKA_FACEBOOK_STRONA']['2'], '', $parametr['ZAKLADKA_FACEBOOK_STRONA']['3'] );
                        ?>
                      </td>
                    </tr>   
                    
                    <tr class="SledzeniePozycja">
                      <td>
                        <label for="zakladka_facebook_sort">Kolejność wyświetlania na stronie:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['ZAKLADKA_FACEBOOK_SORT']['1'], $parametr['ZAKLADKA_FACEBOOK_SORT']['0'], 'zakladka_facebook_sort', $parametr['ZAKLADKA_FACEBOOK_SORT']['2'], '', $parametr['ZAKLADKA_FACEBOOK_SORT']['3'], '', '', 'id="zakladka_facebook_sort"' );
                        ?>
                      </td>
                    </tr>                    

                    <tr>
                      <td colspan="2">
                        <div class="przyciski_dolne">
                          <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'facebook' ? $wynik : '' ); ?>
                        </div>
                      </td>
                    </tr>
                  </table>

              </div>
            </form>
            
          </div>          
          
          <div class="Sledzenie SledzenieUkryte">
          
            <form action="integracje/konfiguracja_zakladki.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="ggForm" class="cmxform">
            
              <div>
                  <input type="hidden" name="akcja" value="zapisz" />
                  <input type="hidden" name="system" value="gg" />
              </div>
              
              <div class="ObramowanieForm">
              
                  <table>
                  
                    <tr class="DivNaglowek">
                      <td style="text-align:left" colspan="2">Wysuwana zakładka GG - okno komunikatora</td>
                    </tr>
                    
                    <tr><td colspan="2" class="SledzenieOpis">
                      <div>Wyświetla wysuwaną zakładkę z oknem komunikatora GG.</div>
                      <img src="obrazki/logo/logo_gg.png" alt="" />
                    </td></tr>                  
                  
                    <tr class="SledzeniePozycja">
                      <td>
                        <label>Włącz zakładkę GG:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['ZAKLADKA_GG_WLACZONA']['1'], $parametr['ZAKLADKA_GG_WLACZONA']['0'], 'zakladka_gg_wlaczona', $parametr['ZAKLADKA_GG_WLACZONA']['2'], '', $parametr['ZAKLADKA_GG_WLACZONA']['3'] );
                        ?>
                      </td>
                    </tr>
                    
                    <tr class="SledzeniePozycja">
                      <td>
                        <label class="required" for="zakladka_gg_profil">Unikalny kod widgetu GG:</label>
                      </td>
                      <td>
                        <?php
                        echo '<input type="text" id="zakladka_gg_profil" name="zakladka_gg_profil" value="'.$parametr['ZAKLADKA_GG_PROFIL']['0'].'" size="73" /><em class="TipIkona"><b>'. $parametr['ZAKLADKA_GG_PROFIL']['2'].'</b></em>';
                        ?>
                        <span class="maleInfo">
                            unikalny kod widgetu GG - fragment z wygenerowanego kodu widgetu GG zaznaczony na obrazku poniżej żółtym kolorem - kod widgetu generuje się na stronie: http://www.gg.pl/info/komunikator-na-twoja-strone/ 
                            (przy wyświetlaniu w sklepie widgetu nie są brane pod uwagę ustawienia wpisane podczas generowania widgetu na stronie GG, tj. kolor, nazwa na przycisku, wiadomość powitalna i pozostałe) <br /><br />
                            <img style="border:1px solid #ccc" src="obrazki/pomoc/gg_zakladka.jpg" id="ImgZakladkGG" alt="" />
                        </span>
                      </td>
                    </tr> 

                    <tr class="SledzeniePozycja">
                      <td>
                        <label class="required" for="zakladka_gg_numer">Numer komunikatora GG:</label>
                      </td>
                      <td>
                        <?php
                        echo '<input type="text" id="zakladka_gg_numer" name="zakladka_gg_numer" value="'.$parametr['ZAKLADKA_GG_NUMER']['0'].'" size="20" />';
                        ?>
                      </td>
                    </tr>    

                    <tr class="SledzeniePozycja">
                      <td>
                        <label for="zakladka_gg_online">Wiadomość powitalna, gdy użytkownik jest <b style="color:#44a04c">Dostępny</b>:</label>
                      </td>
                      <td>
                        <?php
                        echo '<input type="text" id="zakladka_gg_online" name="zakladka_gg_online" value="'.$parametr['ZAKLADKA_GG_ONLINE']['0'].'" size="60" />';
                        ?>
                      </td>
                    </tr>    

                    <tr class="SledzeniePozycja">
                      <td>
                        <label for="zakladka_gg_offline">Wiadomość powitalna, gdy użytkownik jest <b style="color:#ff0000">Niedostępny</b>:</label>
                      </td>
                      <td>
                        <?php
                        echo '<input type="text" id="zakladka_gg_offline" name="zakladka_gg_offline" value="'.$parametr['ZAKLADKA_GG_OFFLINE']['0'].'" size="60" />';
                        ?>
                      </td>
                    </tr>                     

                    <tr class="SledzeniePozycja">
                      <td>
                        <label>Strona po której ma się wyświetlać zakładka:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['ZAKLADKA_GG_STRONA']['1'], $parametr['ZAKLADKA_GG_STRONA']['0'], 'zakladka_gg_strona', $parametr['ZAKLADKA_GG_STRONA']['2'], '', $parametr['ZAKLADKA_GG_STRONA']['3'] );
                        ?>
                      </td>
                    </tr>  

                    <tr class="SledzeniePozycja">
                      <td>
                        <label for="zakladka_gg_sort">Kolejność wyświetlania na stronie:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['ZAKLADKA_GG_SORT']['1'], $parametr['ZAKLADKA_GG_SORT']['0'], 'zakladka_gg_sort', $parametr['ZAKLADKA_GG_SORT']['2'], '', $parametr['ZAKLADKA_GG_SORT']['3'], '', '', 'id="zakladka_gg_sort"' );
                        ?>
                      </td>
                    </tr>                     

                    <tr>
                      <td colspan="2">
                        <div class="przyciski_dolne">
                          <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'gg' ? $wynik : '' ); ?>
                        </div>
                      </td>
                    </tr>
                  </table>

              </div>
            </form>
            
          </div>           

          <div class="Sledzenie SledzenieUkryte">
          
            <form action="integracje/konfiguracja_zakladki.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="youtubeForm" class="cmxform">
            
              <div>
                  <input type="hidden" name="akcja" value="zapisz" />
                  <input type="hidden" name="system" value="youtube" />
              </div>
              
              <div class="ObramowanieForm">
              
                  <table>
                  
                    <tr class="DivNaglowek">
                      <td style="text-align:left" colspan="2">Wysuwana zakładka Youtube</td>
                    </tr>
                    
                    <tr><td colspan="2" class="SledzenieOpis">
                      <div>Wyświetla wysuwaną zakładkę z Youtube.</div>
                      <img src="obrazki/logo/logo_youtube.png" alt="" />
                    </td></tr>                  
                  
                    <tr class="SledzeniePozycja">
                      <td>
                        <label>Włącz zakładkę Youtube:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['ZAKLADKA_YOUTUBE_WLACZONA']['1'], $parametr['ZAKLADKA_YOUTUBE_WLACZONA']['0'], 'zakladka_youtube_wlaczona', $parametr['ZAKLADKA_YOUTUBE_WLACZONA']['2'], '', $parametr['ZAKLADKA_YOUTUBE_WLACZONA']['3'] );
                        ?>
                      </td>
                    </tr>
                    
                    <tr class="SledzeniePozycja">
                      <td>
                        <label>Dane będą wyświetlane na podstawie:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['ZAKLADKA_YOUTUBE_IDENTYFIKATOR']['1'], $parametr['ZAKLADKA_YOUTUBE_IDENTYFIKATOR']['0'], 'zakladka_youtube_identyfikator', $parametr['ZAKLADKA_YOUTUBE_IDENTYFIKATOR']['2'], '', $parametr['ZAKLADKA_YOUTUBE_IDENTYFIKATOR']['3'] );
                        ?>
                      </td>
                    </tr>                    
                    
                    <tr class="SledzeniePozycja">
                      <td>
                        <label class="required" for="zakladka_youtube_profil">Nazwa użytkownika lub identyfikator kanału:</label>
                      </td>
                      <td>
                        <?php
                        echo '<input type="text" id="zakladka_youtube_profil" name="zakladka_youtube_profil" value="'.$parametr['ZAKLADKA_YOUTUBE_PROFIL']['0'].'" size="53" /><em class="TipIkona"><b>'. $parametr['ZAKLADKA_YOUTUBE_PROFIL']['2'].'</b></em>';
                        ?>
                        <span class="maleInfo"> 
                            <b>nazwa użytkownika</b> - jest to wartość wyświetlana w linku youtube za słowem user np: https://www.youtube.com/user/mojanazwa - należy wpisać samą nazwę - w tym przykładzie słowo: mojanazwa                          
                        </span>
                        <span class="maleInfo"> 
                            <b>identyfikator kanału</b> - jest to wartość wyświetlana w linku youtube za słowem user np: https://www.youtube.com/channel/UClgRkhTL3_hIfksl22GD233 - należy wpisać samą nazwę - w tym przykładzie słowo: UClgRkhTL3_hIfksl22GD233
                        </span>
                      </td>
                    </tr>    
                    
                    <tr class="SledzeniePozycja">
                      <td>
                        <label for="zakladka_youtube_sort">Szerokość wysuwanej zakładki w px:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['ZAKLADKA_YOUTUBE_SZEROKOSC']['1'], $parametr['ZAKLADKA_YOUTUBE_SZEROKOSC']['0'], 'zakladka_youtube_szerokosc', $parametr['ZAKLADKA_YOUTUBE_SZEROKOSC']['2'], '', $parametr['ZAKLADKA_YOUTUBE_SZEROKOSC']['3'], '', '', 'id="zakladka_youtube_szerokosc"'  );
                        ?>
                        px
                      </td>
                    </tr>                       

                    <tr class="SledzeniePozycja">
                      <td>
                        <label>Strona po której ma się wyświetlać zakładka:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['ZAKLADKA_YOUTUBE_STRONA']['1'], $parametr['ZAKLADKA_YOUTUBE_STRONA']['0'], 'zakladka_youtube_strona', $parametr['ZAKLADKA_YOUTUBE_STRONA']['2'], '', $parametr['ZAKLADKA_YOUTUBE_STRONA']['3'] );
                        ?>
                      </td>
                    </tr>  

                    <tr class="SledzeniePozycja">
                      <td>
                        <label for="zakladka_youtube_sort">Kolejność wyświetlania na stronie:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['ZAKLADKA_YOUTUBE_SORT']['1'], $parametr['ZAKLADKA_YOUTUBE_SORT']['0'], 'zakladka_youtube_sort', $parametr['ZAKLADKA_YOUTUBE_SORT']['2'], '', $parametr['ZAKLADKA_YOUTUBE_SORT']['3'], '', '', 'id="zakladka_youtube_sort"'  );
                        ?>
                      </td>
                    </tr>                     

                    <tr>
                      <td colspan="2">
                        <div class="przyciski_dolne">
                          <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'youtube' ? $wynik : '' ); ?>
                        </div>
                      </td>
                    </tr>
                  </table>

              </div>
            </form>
            
          </div> 

          <div class="Sledzenie SledzenieUkryte">
          
            <form action="integracje/konfiguracja_zakladki.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="twitterForm" class="cmxform">
            
              <div>
                  <input type="hidden" name="akcja" value="zapisz" />
                  <input type="hidden" name="system" value="twitter" />
              </div>
              
              <div class="ObramowanieForm">
              
                  <table>
                  
                    <tr class="DivNaglowek">
                      <td style="text-align:left" colspan="2">Wysuwana zakładka X (Twitter)</td>
                    </tr>
                    
                    <tr><td colspan="2" class="SledzenieOpis">
                      <div>Wyświetla wysuwaną zakładkę z X (Twitter).</div>
                      <img src="obrazki/logo/logo_twitter.png" alt="" />
                    </td></tr>                  
                  
                    <tr class="SledzeniePozycja">
                      <td>
                        <label>Włącz zakładkę X (Twitter):</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['ZAKLADKA_TWITTER_WLACZONA']['1'], $parametr['ZAKLADKA_TWITTER_WLACZONA']['0'], 'zakladka_twitter_wlaczona', $parametr['ZAKLADKA_TWITTER_WLACZONA']['2'], '', $parametr['ZAKLADKA_TWITTER_WLACZONA']['3'] );
                        ?>
                      </td>
                    </tr>
                    
                    <tr class="SledzeniePozycja">
                      <td>
                        <label class="required" for="zakladka_twitter_profil">Nazwa użytkownika X (twitter.com):</label>
                      </td>
                      <td>
                        <?php
                        echo '<input type="text" id="zakladka_twitter_profil" name="zakladka_twitter_profil" value="'.$parametr['ZAKLADKA_TWITTER_PROFIL']['0'].'" size="53" /><em class="TipIkona"><b>'. $parametr['ZAKLADKA_TWITTER_PROFIL']['2'].'</b></em>';
                        ?>
                        <span class="maleInfo">nazwa użytkownika X (Twitter) - jest to wartość wyświetlana w linku za adresem portalu np: https://twitter.com/adres24pl - należy wpisać samą nazwę - w tym przykładzie słowo: adres24pl</span>
                      </td>
                    </tr>  

                    <tr class="SledzeniePozycja">
                      <td>
                        <label>Strona po której ma się wyświetlać zakładka:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['ZAKLADKA_TWITTER_STRONA']['1'], $parametr['ZAKLADKA_TWITTER_STRONA']['0'], 'zakladka_twitter_strona', $parametr['ZAKLADKA_TWITTER_STRONA']['2'], '', $parametr['ZAKLADKA_TWITTER_STRONA']['3'] );
                        ?>
                      </td>
                    </tr>  

                    <tr class="SledzeniePozycja">
                      <td>
                        <label for="zakladka_twitter_sort">Kolejność wyświetlania na stronie:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['ZAKLADKA_TWITTER_SORT']['1'], $parametr['ZAKLADKA_TWITTER_SORT']['0'], 'zakladka_twitter_sort', $parametr['ZAKLADKA_TWITTER_SORT']['2'], '', $parametr['ZAKLADKA_TWITTER_SORT']['3'], '', '', 'id="zakladka_twitter_sort"' );
                        ?>
                      </td>
                    </tr>                     

                    <tr>
                      <td colspan="2">
                        <div class="przyciski_dolne">
                          <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'twitter' ? $wynik : '' ); ?>
                        </div>
                      </td>
                    </tr>
                  </table>

              </div>
            </form>
            
          </div>
          
          <div class="Sledzenie SledzenieUkryte">
          
            <form action="integracje/konfiguracja_zakladki.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="instagramForm" class="cmxform">
            
              <div>
                  <input type="hidden" name="akcja" value="zapisz" />
                  <input type="hidden" name="system" value="instagram" />
              </div>
              
              <div class="ObramowanieForm">
              
                  <table>
                  
                    <tr class="DivNaglowek">
                      <td style="text-align:left" colspan="2">Wysuwana zakładka Instagram</td>
                    </tr>
                    
                    <tr><td colspan="2" class="SledzenieOpis">
                      <div>Wyświetla wysuwaną zakładkę z Instagram.</div>
                      <img src="obrazki/logo/logo_instagram.png" alt="" />
                    </td></tr>                  
                  
                    <tr class="SledzeniePozycja">
                      <td>
                        <label>Włącz zakładkę Instagram:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['ZAKLADKA_INSTAGRAM_WLACZONA']['1'], $parametr['ZAKLADKA_INSTAGRAM_WLACZONA']['0'], 'zakladka_instagram_wlaczona', $parametr['ZAKLADKA_INSTAGRAM_WLACZONA']['2'], '', $parametr['ZAKLADKA_INSTAGRAM_WLACZONA']['3'] );
                        ?>
                      </td>
                    </tr>
                    
                    <tr class="SledzeniePozycja">
                      <td>
                        <label class="required" for="zakladka_instagram_profil">Kod z serwisu SnapWidget:</label>
                      </td>
                      <td>
                        <?php
                        echo '<input type="text" id="zakladka_instagram_profil" name="zakladka_instagram_profil" value="'.$parametr['ZAKLADKA_INSTAGRAM_PROFIL']['0'].'" size="53" /><em class="TipIkona"><b>'. $parametr['ZAKLADKA_INSTAGRAM_PROFIL']['2'].'</b></em>';
                        ?>
                        <span class="maleInfo">
                            kod z serwisu SnapWidget - w celu wyświetlania zdjęć Instagram należy wygenerować kod widgetu na stronie <a target="_blank" href="https://snapwidget.com/widgets/create?plan=free&service=instagram&type=grid">https://snapwidget.com/widgets/create?plan=free&service=instagram&type=grid</a>
                            <br /><br />
                            podczas generowania widgetu należy podać nazwę użytkownika Instagram (Username), wielkość zdjęć miniaturek (Thumbnail Size) należy ustawić na 110px, ilość miniaturek (Layout) 2 x 3, pozostałe opcje bez zmian;
                            po wprowadzeniu danych należy przyciskiem GET WIDGET wygenerować kod widgetu (przy pierwszym generowaniu kodu pojawi się dodatkowe okno autoryzacji kodu w Instagram), z otrzymanego kodu należy wpisać w powyższe pole ciąg znaków oznaczony na czerwono .... https://snapwidget.com/embed/<b style="color:#ff0000">12345</b>" ....(tylko samą wartość liczbową)</span>
                      </td>
                    </tr>    
                    
                    <tr class="SledzeniePozycja">
                      <td>
                        <label>Strona po której ma się wyświetlać zakładka:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['ZAKLADKA_INSTAGRAM_STRONA']['1'], $parametr['ZAKLADKA_INSTAGRAM_STRONA']['0'], 'zakladka_instagram_strona', $parametr['ZAKLADKA_INSTAGRAM_STRONA']['2'], '', $parametr['ZAKLADKA_INSTAGRAM_STRONA']['3'] );
                        ?>
                      </td>
                    </tr>  

                    <tr class="SledzeniePozycja">
                      <td>
                        <label for="zakladka_instagram_sort">Kolejność wyświetlania na stronie:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['ZAKLADKA_INSTAGRAM_SORT']['1'], $parametr['ZAKLADKA_INSTAGRAM_SORT']['0'], 'zakladka_instagram_sort', $parametr['ZAKLADKA_INSTAGRAM_SORT']['2'], '', $parametr['ZAKLADKA_INSTAGRAM_SORT']['3'], '', '', 'id="zakladka_instagram_sort"' );
                        ?>
                      </td>
                    </tr>                     

                    <tr>
                      <td colspan="2">
                        <div class="przyciski_dolne">
                          <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'instagram' ? $wynik : '' ); ?>
                        </div>
                      </td>
                    </tr>
                  </table>

              </div>
            </form>
            
          </div>          
          
          <div class="Sledzenie SledzenieUkryte">
          
            <form action="integracje/konfiguracja_zakladki.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="pinterestForm" class="cmxform">
            
              <div>
                  <input type="hidden" name="akcja" value="zapisz" />
                  <input type="hidden" name="system" value="pinterest" />
              </div>
              
              <div class="ObramowanieForm">
              
                  <table>
                  
                    <tr class="DivNaglowek">
                      <td style="text-align:left" colspan="2">Wysuwana zakładka Pinterest</td>
                    </tr>
                    
                    <tr><td colspan="2" class="SledzenieOpis">
                      <div>Wyświetla wysuwaną zakładkę z Pinterest. W zależności o wybranego widgetu wyświetla informacje o profilu użytkownika lub wybranej tablicy.</div>
                      <img src="obrazki/logo/logo_pinterest.png" alt="" />
                    </td></tr>                  
                  
                    <tr class="SledzeniePozycja">
                      <td>
                        <label>Włącz zakładkę Pinterest:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['ZAKLADKA_PINTEREST_WLACZONA']['1'], $parametr['ZAKLADKA_PINTEREST_WLACZONA']['0'], 'zakladka_pinterest_wlaczona', $parametr['ZAKLADKA_PINTEREST_WLACZONA']['2'], '', $parametr['ZAKLADKA_PINTEREST_WLACZONA']['3'] );
                        ?>
                      </td>
                    </tr>
                    
                    <tr class="SledzeniePozycja">
                      <td>
                        <label class="required" for="zakladka_pinterest_profil">Nazwa profilu Pinterest:</label>
                      </td>
                      <td>
                        <?php
                        echo '<input type="text" id="zakladka_pinterest_profil" name="zakladka_pinterest_profil" value="'.$parametr['ZAKLADKA_PINTEREST_PROFIL']['0'].'" size="53" /><em class="TipIkona"><b>'. $parametr['ZAKLADKA_PINTEREST_PROFIL']['2'].'</b></em>';
                        ?>
                        <span class="maleInfo">
                            nazwa użytkownika Pinterest - jest to wartość wyświetlana w linku Pinterest za adresem portalu np: https://www.pinterest.com/adres24pl/ <br /><br />
                            1) w przypadku jeżeli został wybrany widget <b>profilu</b> należy podać nazwę profilu np: https://www.pinterest.com/adres24pl/ - należy wpisać w tym przykładzie: adres24pl<br />
                            2) w przypadku jeżeli został wybrany widget <b>tablicy</b> należy podać profil wraz z adresem tablicy np: https://www.pinterest.com/adres24pl/moje-artykuly/ - należy wpisać w tym przykładzie: pinterest/moje-artykuly
                        </span>
                      </td>
                    </tr>  
                    
                    <tr class="SledzeniePozycja">
                      <td>
                        <label for="zakladka_pinterest_widget">Rodzaj wyświetlanego widgetu:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['ZAKLADKA_PINTEREST_WIDGET']['1'], $parametr['ZAKLADKA_PINTEREST_WIDGET']['0'], 'zakladka_pinterest_widget', $parametr['ZAKLADKA_PINTEREST_WIDGET']['2'], '', $parametr['ZAKLADKA_PINTEREST_WIDGET']['3'], '', '', 'id="zakladka_pinterest_widget"' );
                        ?>
                      </td>
                    </tr>    

                    <tr class="SledzeniePozycja">
                      <td>
                        <label for="zakladka_pinterest_zdjecia">Wielkość zdjęć wyświetlanych w widgecie (szerokość):</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['ZAKLADKA_PINTEREST_ZDJECIA']['1'], $parametr['ZAKLADKA_PINTEREST_ZDJECIA']['0'], 'zakladka_pinterest_zdjecia', $parametr['ZAKLADKA_PINTEREST_ZDJECIA']['2'], '', $parametr['ZAKLADKA_PINTEREST_ZDJECIA']['3'], '', '', 'id="zakladka_pinterest_zdjecia"' ) . ' px';
                        ?>
                      </td>
                    </tr>                         
   
                    <tr class="SledzeniePozycja">
                      <td>
                        <label>Strona po której ma się wyświetlać zakładka:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['ZAKLADKA_PINTEREST_STRONA']['1'], $parametr['ZAKLADKA_PINTEREST_STRONA']['0'], 'zakladka_pinterest_strona', $parametr['ZAKLADKA_PINTEREST_STRONA']['2'], '', $parametr['ZAKLADKA_PINTEREST_STRONA']['3'] );
                        ?>
                      </td>
                    </tr>  

                    <tr class="SledzeniePozycja">
                      <td>
                        <label for="zakladka_pinterest_sort">Kolejność wyświetlania na stronie:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['ZAKLADKA_PINTEREST_SORT']['1'], $parametr['ZAKLADKA_PINTEREST_SORT']['0'], 'zakladka_pinterest_sort', $parametr['ZAKLADKA_PINTEREST_SORT']['2'], '', $parametr['ZAKLADKA_PINTEREST_SORT']['3'], '', '', 'id="zakladka_pinterest_sort"' );
                        ?>
                      </td>
                    </tr>                     

                    <tr>
                      <td colspan="2">
                        <div class="przyciski_dolne">
                          <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'pinterest' ? $wynik : '' ); ?>
                        </div>
                      </td>
                    </tr>
                  </table>

              </div>
            </form>
            
          </div>    

          <?php
          // dodatkowe indywidualne zakladki
          for ($r = 1; $r <= count($tab_zakladki); $r++ ) {
          
              $nazwa = $tab_zakladki[$r - 1];
              $nr = $r;
              //
              include('konfiguracja_zakladki_indywidualna.php');
              //
              unset($nazwa, $nr);
              
          }
          unset($tab_zakladki);
          ?>         

          <div class="Sledzenie SledzenieUkryte">
          
            <form action="integracje/konfiguracja_zakladki.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="wlasneopinieForm" class="cmxform">
            
              <div>
                  <input type="hidden" name="akcja" value="zapisz" />
                  <input type="hidden" name="system" value="wlasneopinie" />
              </div>
              
              <div class="ObramowanieForm">
              
                  <table>
                  
                    <tr class="DivNaglowek">
                      <td style="text-align:left" colspan="2">Wysuwana zakładka Opinie o sklepie</td>
                    </tr>
                    
                    <tr><td colspan="2" class="SledzenieOpis">
                      <div>Wyświetla wysuwaną zakładkę z opiniemi o sklepie. Narzędzie, dzięki któremu można opublikować opinie o sklepie napisane poprzez moduł Opinii o sklepie dostępny w sklepie (menu Sprzedaż / Opinie o sklepie).<br /><span class="maleInfo">Uwaga: Zakładka jest wyświetlana tylko kiedy włączony jest moduł Opinii o sklepie (menu Sprzedaż / Opinie o sklepie / Konfiguracja opinii o sklepie).</span></div>
                      <img src="obrazki/logo/logo_opinie_sklepu.png" alt="" />
                    </td></tr>                  
                  
                    <tr class="SledzeniePozycja">
                      <td>
                        <label>Włącz zakładkę Opinie o sklepie:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['ZAKLADKA_OPINIE_WLACZONA']['1'], $parametr['ZAKLADKA_OPINIE_WLACZONA']['0'], 'zakladka_opinie_wlaczona', $parametr['ZAKLADKA_OPINIE_WLACZONA']['2'], '', $parametr['ZAKLADKA_OPINIE_WLACZONA']['3'] );
                        ?>
                      </td>
                    </tr>
                    
                    <tr class="SledzeniePozycja">
                      <td>
                        <label>Strona po której ma się wyświetlać zakładka:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['ZAKLADKA_OPINIE_STRONA']['1'], $parametr['ZAKLADKA_OPINIE_STRONA']['0'], 'zakladka_opinie_strona', $parametr['ZAKLADKA_OPINIE_STRONA']['2'], '', $parametr['ZAKLADKA_OPINIE_STRONA']['3'] );
                        ?>
                      </td>
                    </tr>           

                    <tr class="SledzeniePozycja">
                      <td>
                        <label for="zakladka_opinie_sort">Kolejność wyświetlania na stronie:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['ZAKLADKA_OPINIE_SORT']['1'], $parametr['ZAKLADKA_OPINIE_SORT']['0'], 'zakladka_opinie_sort', $parametr['ZAKLADKA_OPINIE_SORT']['2'], '', $parametr['ZAKLADKA_OPINIE_SORT']['3'], '', '', 'id="zakladka_opinie_sort"' );
                        ?>
                      </td>
                    </tr>                      
  
                    <tr>
                      <td colspan="2">
                        <div class="przyciski_dolne">
                          <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'wlasneopinie' ? $wynik : '' ); ?>
                        </div>
                      </td>
                    </tr>
                  </table>

              </div>
            </form>
            
          </div>          

          <div class="Sledzenie SledzenieUkryte">
          
            <form action="integracje/konfiguracja_zakladki.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="ceneoForm" class="cmxform">
            
              <div>
                  <input type="hidden" name="akcja" value="zapisz" />
                  <input type="hidden" name="system" value="ceneo" />
              </div>
              
              <div class="ObramowanieForm">
              
                  <table>
                  
                    <tr class="DivNaglowek">
                      <td style="text-align:left" colspan="2">Wysuwana zakładka Ceneo Sprawdź nas</td>
                    </tr>
                    
                    <tr><td colspan="2" class="SledzenieOpis">
                      <div>Wyświetla wysuwaną zakładkę z Ceneo "Sprawdź nas". Narzędzie, dzięki któremu sklepy mogą opublikować informacje gromadzone przez Ceneo (m.in. oceny klientów) na swojej stronie.<br /><span class="maleInfo">Uwaga: Zakładka działa niezależnie od zakładek systemów społacznościowch - jej zawartość konfiguruje się w panelu w serwisie Ceneo - skąd należy pobrać gotowy kod do wklejenia w poniższy formularz.</span></div>
                      <img src="obrazki/logo/logo_ceneo.png" alt="" />
                    </td></tr>                  
                  
                    <tr class="SledzeniePozycja">
                      <td>
                        <label>Włącz zakładkę Ceneo:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['ZAKLADKA_CENEO_WLACZONA']['1'], $parametr['ZAKLADKA_CENEO_WLACZONA']['0'], 'zakladka_ceneo_wlaczona', $parametr['ZAKLADKA_CENEO_WLACZONA']['2'], '', $parametr['ZAKLADKA_CENEO_WLACZONA']['3'] );
                        ?>
                      </td>
                    </tr>
                    
                    <tr class="SledzeniePozycja">
                      <td>
                        <label class="required"  for="zakladka_ceneo_kod">Kod wyświetlający widget:</label>
                      </td>
                      <td>
                        <?php
                        echo '<textarea cols="110" rows="5" id="zakladka_ceneo_kod" name="zakladka_ceneo_kod">'.$parametr['ZAKLADKA_CENEO_KOD']['0'].'</textarea><em class="TipIkona"><b>'. $parametr['ZAKLADKA_CENEO_KOD']['2'].'</b></em>';
                        ?>
                      </td>
                    </tr>    
                    <tr>
                      <td colspan="2">
                        <div class="przyciski_dolne">
                          <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'ceneo' ? $wynik : '' ); ?>
                        </div>
                      </td>
                    </tr>
                  </table>

              </div>
            </form>
            
          </div>

          <div class="Sledzenie SledzenieUkryte">
          
            <form action="integracje/konfiguracja_zakladki.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="okazjeForm" class="cmxform">
            
              <div>
                  <input type="hidden" name="akcja" value="zapisz" />
                  <input type="hidden" name="system" value="okazje" />
              </div>
              
              <div class="ObramowanieForm">
              
                  <table>
                  
                    <tr class="DivNaglowek">
                      <td style="text-align:left" colspan="2">Wysuwana zakładka Okazje.info Wiarygodne opinie</td>
                    </tr>
                    
                    <tr><td colspan="2" class="SledzenieOpis">
                      <div>Wyświetla wysuwaną zakładkę z Okazje.info "Wiarygodne opinie". Program Wiarygodne Opinie to darmowy system zarządzania opiniami i ocenami Twojego sklepu wystawianymi przez Klientów po dokonaniu zakupu. Najlepsze sklepy wyróżniane są certyfikatem "Polecany przez Klientów".<br /><span class="maleInfo">Uwaga: Zakładka działa niezależnie od zakładek systemów społacznościowch - jej zawartość konfiguruje się w panelu w serwisie Okazje.info - skąd należy pobrać gotowy kod do wklejenia w poniższy formularz.</span></div>
                      <img src="obrazki/logo/logo_opinie_okazje.png" alt="" />
                    </td></tr>                  
                  
                    <tr class="SledzeniePozycja">
                      <td>
                        <label>Włącz zakładkę Okazje.info:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['ZAKLADKA_OKAZJE_INFO_WLACZONA']['1'], $parametr['ZAKLADKA_OKAZJE_INFO_WLACZONA']['0'], 'zakladka_okazje_info_wlaczona', $parametr['ZAKLADKA_OKAZJE_INFO_WLACZONA']['2'], '', $parametr['ZAKLADKA_OKAZJE_INFO_WLACZONA']['3'] );
                        ?>
                      </td>
                    </tr>
                    
                    <tr class="SledzeniePozycja">
                      <td>
                        <label class="required" for="zakladka_okazje_info_kod">Kod wyświetlający widget:</label>
                      </td>
                      <td>
                        <?php
                        echo '<textarea cols="110" rows="5" name="zakladka_okazje_info_kod" id="zakladka_okazje_info_kod">'.$parametr['ZAKLADKA_OKAZJE_INFO_KOD']['0'].'</textarea><em class="TipIkona"><b>'. $parametr['ZAKLADKA_OKAZJE_INFO_KOD']['2'].'</b></em>';
                        ?>
                      </td>
                    </tr>    
                    <tr>
                      <td colspan="2">
                        <div class="przyciski_dolne">
                          <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'okazje' ? $wynik : '' ); ?>
                        </div>
                      </td>
                    </tr>
                  </table>

              </div>
            </form>
            
          </div>
        
          <div class="Sledzenie SledzenieUkryte">
          
            <form action="integracje/konfiguracja_zakladki.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="opineoForm" class="cmxform">
            
              <div>
                  <input type="hidden" name="akcja" value="zapisz" />
                  <input type="hidden" name="system" value="opineo" />
              </div>
              
              <div class="ObramowanieForm">
              
                  <table>
                  
                    <tr class="DivNaglowek">
                      <td style="text-align:left" colspan="2">Wysuwana zakładka OPINEO Zaufane opinie</td>
                    </tr>
                    
                    <tr><td colspan="2" class="SledzenieOpis">
                      <div>Wyświetla wysuwaną zakładkę z OPINEO "Zaufane opinie". Opineo.pl jest serwisem propagującym zakupy w internecie. Gromadzi opinie użytkowników o dokonanych przez nich transakcjach po to, by e-zakupy były jak najmniej ryzykowne.<br /><span class="maleInfo">Uwaga: Zakładka działa niezależnie od zakładek systemów społacznościowch - jej zawartość konfiguruje się w panelu w serwisie Opineo.pl - skąd należy pobrać gotowy kod do wklejenia w poniższy formularz.</span></div>
                      <img src="obrazki/logo/logo_opineo.png" alt="" />
                    </td></tr>                  
                  
                    <tr class="SledzeniePozycja">
                      <td>
                        <label>Włącz zakładkę OPINEO:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['ZAKLADKA_OPINEO_WLACZONA']['1'], $parametr['ZAKLADKA_OPINEO_WLACZONA']['0'], 'zakladka_opineo_wlaczona', $parametr['ZAKLADKA_OPINEO_WLACZONA']['2'], '', $parametr['ZAKLADKA_OPINEO_WLACZONA']['3'] );
                        ?>
                      </td>
                    </tr>
                    
                    <tr class="SledzeniePozycja">
                      <td>
                        <label class="required"  for="zakladka_opineo_kod">Kod wyświetlający widget:</label>
                      </td>
                      <td>
                        <?php
                        echo '<textarea cols="110" rows="5" name="zakladka_opineo_kod" id="zakladka_opineo_kod">'.$parametr['ZAKLADKA_OPINEO_KOD']['0'].'</textarea><em class="TipIkona"><b>'. $parametr['ZAKLADKA_OPINEO_KOD']['2'].'</b></em>';
                        ?>
                      </td>
                    </tr>    
                    <tr>
                      <td colspan="2">
                        <div class="przyciski_dolne">
                          <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'opineo' ? $wynik : '' ); ?>
                        </div>
                      </td>
                    </tr>
                  </table>

              </div>
            </form>
            
          </div>
        
        </div>
      </div>
    </div>

    
    <?php
    include('stopka.inc.php');    
    
} ?>
