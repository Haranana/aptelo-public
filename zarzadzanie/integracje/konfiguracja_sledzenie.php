<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    $wynik  = '';
    $system = ( isset($_POST['system']) ? $_POST['system'] : '' );

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

      reset($_POST);
      //
      foreach ( $_POST as $key => $value ) {
        if ( $key != 'akcja' ) {
          $pola = array(
                  array('value',$filtr->process($value))
          );
          $db->update_query('settings' , $pola, " code = '".strtoupper((string)$key)."'");	
          //       
          unset($pola);
        }
      }
      
      $wynik = '<div id="'.$system.'" class="maleSukces" style="margin-left:20px;margin-top:10px;">dane zostały zmienione</div>';

    }

    $zapytanie = "SELECT * FROM settings WHERE type = 'sledzenie' ORDER BY sort ";
    $sql = $db->open_query($zapytanie);

    $parametr = array();

    if ( $db->ile_rekordow($sql) > 0 ) {
      while ($info = $sql->fetch_assoc()) {
        $parametr[$info['code']] = array($info['value'], $info['limit_values'], $info['description'], $info['form_field_type']);
      }
    }
    $db->close_query($sql);
    unset($zapytanie, $info);

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>

    <div id="naglowek_cont">Konfiguracja parametrów systemów śledzących</div>
    <div id="cont">

      <div class="poleForm">
        <div class="naglowek">Edycja danych</div>
          
        <div class="SledzenieNaglowki">
        
            <div class="SledzenieOkno">
            
                <div class="SledzenieDiv" data-id="googleForm">
                    <div class="Foto"><img src="obrazki/logo/logo_google_analytics.png" alt="" /></div>
                    <span>Google Analytics</span>
                </div>
              
            </div>
            
            <div class="SledzenieOkno">
            
                <div class="SledzenieDiv" data-id="google4Form">
                    <div class="Foto"><img src="obrazki/logo/logo_google_analytics_4.png" alt="" /></div>
                    <span>Google Analytics 4</span>
                </div>
              
            </div>            

            <div class="SledzenieOkno">
            
                <div class="SledzenieDiv" data-id="googleGTMForm">
                    <div class="Foto"><img src="obrazki/logo/logo_google_gtm.png" alt="" /></div>
                    <span>Google Tag Manager</span>
                </div>
              
            </div>    
            
            <div class="SledzenieOkno">
            
                <div class="SledzenieDiv" data-id="googleConversionForm">
                    <div class="Foto"><img src="obrazki/logo/logo_google_conversion.png" alt="" /></div>
                    <span>Google Ads</span>
                </div>
              
            </div>    
            
            <div class="SledzenieOkno">
            
                <div class="SledzenieDiv" data-id="googleWebForm">
                    <div class="Foto"><img src="obrazki/logo/logo_google_web.png" alt="" /></div>
                    <span>Google (weryfikacja własności witryny)</span>
                </div>
              
            </div>     

            <div class="SledzenieOkno">
            
                <div class="SledzenieDiv" data-id="fbpixelForm">
                    <div class="Foto"><img src="obrazki/logo/fb_pixel.png" alt="" /></div>
                    <span>Pixel Facebook</span>
                </div>
              
            </div>     

            <div class="SledzenieOkno">
            
                <div class="SledzenieDiv" data-id="ceneoForm">
                    <div class="Foto"><img src="obrazki/logo/logo_opinie_ceneo.png" alt="" /></div>
                    <span>CENEO zaufane opinie</span>
                </div>
              
            </div>    

            <div class="SledzenieOkno">
            
                <div class="SledzenieDiv" data-id="opineoForm">
                    <div class="Foto"><img src="obrazki/logo/logo_opineo.png" alt="" /></div>
                    <span>OPINEO</span>
                </div>
              
            </div>   

            <div class="SledzenieOkno">
            
                <div class="SledzenieDiv" data-id="okazjeForm">
                    <div class="Foto"><img src="obrazki/logo/logo_okazje.png" alt="" /></div>
                    <span>Wiarygodne Opinie okazje.info</span>
                </div>
              
            </div> 

            <div class="SledzenieOkno">
            
                <div class="SledzenieDiv" data-id="googleopinieForm">
                    <div class="Foto"><img src="obrazki/logo/google_opinie.png" alt="" /></div>
                    <span>Opinie konsumenckie Google</span>
                </div>
              
            </div>      

            <div class="SledzenieOkno">
            
                <div class="SledzenieDiv" data-id="salesmediaForm">
                    <div class="Foto"><img src="obrazki/logo/logo_salesmedia.png" alt="" /></div>
                    <span>Program Salesmedia.pl</span>
                </div>
              
            </div>               
        
            <div class="SledzenieOkno">
            
                <div class="SledzenieDiv" data-id="edroneForm">
                    <div class="Foto"><img src="obrazki/logo/edrone.png" alt="" /></div>
                    <span>Edrone</span>
                </div>
              
            </div>
            
            <div class="SledzenieOkno">
            
                <div class="SledzenieDiv" data-id="salesmangoForm">
                    <div class="Foto"><img src="obrazki/logo/salesmanago.png" alt="" /></div>
                    <span>Salesmanago</span>
                </div>
              
            </div>  

            <div class="SledzenieOkno">
            
                <div class="SledzenieDiv" data-id="criteoForm">
                    <div class="Foto"><img src="obrazki/logo/criteo.png" alt="" /></div>
                    <span>Criteo - One Tag</span>
                </div>
              
            </div>       
            
            <div class="SledzenieOkno">
            
                <div class="SledzenieDiv" data-id="sareForm">
                    <div class="Foto"><img src="obrazki/logo/sare.png" alt="" /></div>
                    <span>SARE - marketing automation</span>
                </div>
              
            </div>               

            <div class="SledzenieOkno">
            
                <div class="SledzenieDiv" data-id="nokautTrackerForm">
                    <div class="Foto"><img src="obrazki/logo/logo_nokaut.png" alt="" /></div>
                    <span>NOKAUT.pl</span>
                </div>
              
            </div>  

            <div class="SledzenieOkno">
            
                <div class="SledzenieDiv" data-id="AllaniForm">
                    <div class="Foto"><img src="obrazki/logo/logo_allani_domodi.png" alt="" /></div>
                    <span>DOMODI i ALLANI</span>
                </div>
              
            </div>   
            
            <div class="SledzenieOkno">
            
                <div class="SledzenieDiv" data-id="WpForm">
                    <div class="Foto"><img src="obrazki/logo/wp_ads.png" alt="" /></div>
                    <span>WP ads (pixel)</span>
                </div>
              
            </div>               

            <div class="SledzenieOkno">
            
                <div class="SledzenieDiv" data-id="pinterestForm">
                    <div class="Foto"><img src="obrazki/logo/logo_pinterest.png" alt="" /></div>
                    <span>Pinterest TAG</span>
                </div>
              
            </div>               

            <div class="SledzenieOkno">
            
                <div class="SledzenieDiv" data-id="salesforceForm">
                    <div class="Foto"><img src="obrazki/logo/salesforce.png" alt="" /></div>
                    <span>SalesForce</span>
                </div>

            </div>

            <div class="SledzenieOkno">
            
                <div class="SledzenieDiv" data-id="klaviyoForm">
                    <div class="Foto"><img src="obrazki/logo/klaviyo.svg" alt="" /></div>
                    <span>Klaviyo</span>
                </div>
              
            </div>               
              
        </div>
          
        <div class="cl"></div>

        <div class="pozycja_edytowana">  

          <script>
          $(document).ready(function() {
            
            $('#pole_integracja_allani_sledzenie_wlaczony_0').click(function() {
               $('#pole_integracja_domodi_pixel_wlaczony_0').prop('checked',false);
               $('#pole_integracja_domodi_pixel_wlaczony_1').prop('checked',true);
            });
            $('#pole_integracja_domodi_pixel_wlaczony_0').click(function() {
               $('#pole_integracja_allani_sledzenie_wlaczony_0').prop('checked',false);
               $('#pole_integracja_allani_sledzenie_wlaczony_1').prop('checked',true);
            });
            
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
            
            $("#googleForm").validate({
              rules: {
                integracja_google_id: {required: function() {var wynik = true; if ( $("input[name='integracja_google_wlaczony']:checked", "#googleForm").val() == "nie" ) { wynik = false; } return wynik; }}
              }
            });

            $("#google4Form").validate({
              rules: {
                integracja_google_id: {required: function() {var wynik = true; if ( $("input[name='integracja_google4_wlaczony']:checked", "#google4Form").val() == "nie" ) { wynik = false; } return wynik; }}
              }
            });
            
            $("#googleGTMForm").validate({
              rules: {
                integracja_google_gtm_id: {required: function() {var wynik = true; if ( $("input[name='integracja_google_gtm_wlaczony']:checked", "#googleGTMForm").val() == "nie" ) { wynik = false; } return wynik; }}
              }
            });
            
            $("#googleWebForm").validate({
              rules: {
                integracja_google_web_id: {required: function() {var wynik = true; if ( $("input[name='integracja_google_web_wlaczony']:checked", "#googleWebForm").val() == "nie" ) { wynik = false; } return wynik; }}
              }
            });              

            $("#googleConversionForm").validate({
              rules: {
                integracja_google_konwersja: {required: function() {var wynik = true; if ( $("input[name='integracja_google_konwersja_wlaczony']:checked", "#googleConversionForm").val() == "nie" ) { wynik = false; } return wynik; }}
              }
            });
            
            $("#fbpixelForm").validate({
              rules: {
                integracja_fb_pixel_id: {required: function() {var wynik = true; if ( $("input[name='integracja_fb_pixel_wlaczony']:checked", "#fbpixelForm").val() == "nie" ) { wynik = false; } return wynik; }}
              }
            });   
            
            $("#AllaniForm").validate({
              rules: {
                integracja_domodi_pixel_id: {required: function() {var wynik = true; if ( $("input[name='integracja_domodi_pixel_wlaczony']:checked", "#AllaniForm").val() == "nie" ) { wynik = false; } return wynik; }}
              }
            });              

            $("#WpForm").validate({
              rules: {
                integracja_wp_pixel_id: {required: function() {var wynik = true; if ( $("input[name='integracja_wp_pixel_wlaczony']:checked", "#WpForm").val() == "nie" ) { wynik = false; } return wynik; }}
              }
            });   
            
            $("#ceneoForm").validate({
              rules: {
                integracja_ceneo_opinie_id: {required: function() {
                  var wynik = true; 
                  if ( $("input[name='integracja_ceneo_opinie_wlaczony']:checked", "#ceneoForm").val() == "nie" && $("input[name='integracja_ceneo_widget_wlaczony']:checked", "#ceneoForm").val() == "nie" ) { wynik = false; }
                  return wynik; 
                }},
              }
            });

            $("#opineoForm").validate({
              rules: {
                integracja_opineo_opinie_login: {required: function() {var wynik = true; if ( $("input[name='integracja_opineo_opinie_wlaczony']:checked", "#opineoForm").val() == "nie" ) { wynik = false; } return wynik; }}
              }
            });

            $("#okazjeForm").validate({
              rules: {
                integracja_okazje_id: {required: function() {var wynik = true; if ( $("input[name='integracja_okazje_wlaczony']:checked", "#okazjeForm").val() == "nie" ) { wynik = false; } return wynik; }}
              }
            });
            
            $("#googleopinieForm").validate({
              rules: {
                integracja_google_opinie_merchant_id: {required: function() {var wynik = true; if ( $("input[name='integracja_google_opinie_wlaczony']:checked", "#googleopinieForm").val() == "nie" ) { wynik = false; } return wynik; }},
              }
            });            
            
            $("#salesmediaForm").validate({
              rules: {
                integracja_salesmedia_id: {required: function() {var wynik = true; if ( $("input[name='integracja_salesmedia_wlaczony']:checked", "#salesmediaForm").val() == "nie" ) { wynik = false; } return wynik; }}
              }
            });               
            
            $("#edroneForm").validate({
              rules: {
                integracja_edrone_api: {required: function() {var wynik = true; if ( $("input[name='integracja_edrone_wlaczony']:checked", "#edroneForm").val() == "nie" ) { wynik = false; } return wynik; }}
              }
            });   

            $("#nokautTrackerForm").validate({
              rules: {
                integracja_nokaut_sledzenie_id: {required: function() {var wynik = true; if ( $("input[name='integracja_nokaut_sledzenie_wlaczony']:checked", "#nokautTrackerForm").val() == "nie" ) { wynik = false; } return wynik; }}
              }
            });   

            $("#sareForm").validate({
              rules: {
                integracja_sare_uid: {required: function() {var wynik = true; if ( $("input[name='integracja_sare_wlaczony']:checked", "#sareForm").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_sare_api_key: {required: function() {var wynik = true; if ( $("input[name='integracja_sare_wlaczony']:checked", "#sareForm").val() == "nie" ) { wynik = false; } return wynik; }}
              }
            });   

            $("#salesforceForm").validate({
              rules: {
                integracja_salesforce_client_id: {required: function() {var wynik = true; if ( $("input[name='integracja_salesforce_wlaczony']:checked", "#salesforceForm").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_salesforce_client_secret: {required: function() {var wynik = true; if ( $("input[name='integracja_salesforce_wlaczony']:checked", "#salesforceForm").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_salesforce_subscription: {required: function() {var wynik = true; if ( $("input[name='integracja_salesforce_wlaczony']:checked", "#salesforceForm").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_salesforce_source: {required: function() {var wynik = true; if ( $("input[name='integracja_salesforce_wlaczony']:checked", "#salesforceForm").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_salesforce_url: {required: function() {var wynik = true; if ( $("input[name='integracja_salesforce_wlaczony']:checked", "#salesforceForm").val() == "nie" ) { wynik = false; } return wynik; }}
              }
            });   

            $("#salesmanagoForm").validate({
              rules: {
                integracja_salesmanago_endpoint: {required: function() {var wynik = true; if ( $("input[name='integracja_salesmanago_wlaczony']:checked", "#salesmanagoForm").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_salesmanago_id_klienta: {required: function() {var wynik = true; if ( $("input[name='integracja_salesmanago_wlaczony']:checked", "#salesmanagoForm").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_salesmanago_api_secret: {required: function() {var wynik = true; if ( $("input[name='integracja_salesmanago_wlaczony']:checked", "#salesmanagoForm").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_salesmanago_email: {required: function() {var wynik = true; if ( $("input[name='integracja_salesmanago_wlaczony']:checked", "#salesmanagoForm").val() == "nie" ) { wynik = false; } return wynik; }}
              }
            });            
            
            $("#criteoForm").validate({
              rules: {
                integracja_criteo_id: {required: function() {var wynik = true; if ( $("input[name='integracja_criteo_wlaczony']:checked", "#criteoForm").val() == "nie" ) { wynik = false; } return wynik; }}
              }
            });      

            $("#klaviyoForm").validate({
              rules: {
                integracja_klaviyo_public_key: {required: function() {var wynik = true; if ( $("input[name='integracja_klaviyo_wlaczony']:checked", "#klaviyoForm").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_klaviyo_private_key: {required: function() {var wynik = true; if ( $("input[name='integracja_klaviyo_wlaczony']:checked", "#klaviyoForm").val() == "nie" ) { wynik = false; } return wynik; }}
              }
            });   

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
          
            <form action="integracje/konfiguracja_sledzenie.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="googleForm" class="cmxform"> 
            
            <div>
              <input type="hidden" name="akcja" value="zapisz" />
              <input type="hidden" name="system" value="google" />
            </div>
            
            <div class="ObramowanieForm">
            
              <table>
              
                <tr class="DivNaglowek">
                  <td colspan="2">Google Analytics</td>
                </tr>
                
                <tr><td colspan="2" class="SledzenieOpis">
                  <div>Usługa Google Analytics nie tylko umożliwia pomiar wielkości sprzedaży i liczby konwersji, ale również zapewnia bieżący wgląd w to, jak użytkownicy korzystają z Twojej witryny, jak do niej dotarli i co możesz zrobić, by chętnie do niej wracali.</div>
                  <img src="obrazki/logo/logo_google_analytics.png" alt="" />
                </td></tr>
                
                <tr class="SledzeniePozycja">
                  <td>
                    <label>Włącz moduł Google Analytics:</label>
                  </td>
                  <td>
                    <?php
                    echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_GOOGLE_WLACZONY']['1'], $parametr['INTEGRACJA_GOOGLE_WLACZONY']['0'], 'integracja_google_wlaczony', $parametr['INTEGRACJA_GOOGLE_WLACZONY']['2'], '', $parametr['INTEGRACJA_GOOGLE_WLACZONY']['3'] );
                    ?>
                  </td>
                </tr>
                
                <tr class="SledzeniePozycja">
                  <td>
                    <label>Używana wersja Google Analytics:</label>
                  </td>
                  <td id="RodzajGoogle">
                    <?php
                    echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_GOOGLE_RODZAJ']['1'], $parametr['INTEGRACJA_GOOGLE_RODZAJ']['0'], 'integracja_google_rodzaj', $parametr['INTEGRACJA_GOOGLE_RODZAJ']['2'], '', $parametr['INTEGRACJA_GOOGLE_RODZAJ']['3'] );
                    ?>
                  </td>
                </tr>

                <tr class="SledzeniePozycja">
                  <td>
                    <label class="required" for="integracja_google_id">Identyfikator Google:</label>
                  </td>
                  <td>
                    <?php
                    echo '<input type="text" id="integracja_google_id" name="integracja_google_id" value="'.$parametr['INTEGRACJA_GOOGLE_ID']['0'].'" size="53" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_GOOGLE_ID']['2'].'</b></em>';
                    ?>
                  </td>
                </tr>

                <tr>
                  <td colspan="2">
                    <div class="przyciski_dolne">
                      <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'google' ? $wynik : '' ); ?>
                    </div>
                  </td>
                </tr>
                
              </table>
              
            </div>

            </form>
            
          </div>
          
          <div class="Sledzenie SledzenieUkryte">
          
            <form action="integracje/konfiguracja_sledzenie.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="google4Form" class="cmxform"> 
            
            <div>
              <input type="hidden" name="akcja" value="zapisz" />
              <input type="hidden" name="system" value="google4" />
            </div>
            
            <div class="ObramowanieForm">
            
              <table>
              
                <tr class="DivNaglowek">
                  <td colspan="2">Google Analytics 4</td>
                </tr>
                
                <tr><td colspan="2" class="SledzenieOpis">
                  <div>Google Analytics 4 to usługa pozwalająca na mierzenie i analizę danych zebranych z podpiętych stron internetowych i aplikacji mobilnych w jednym, wspólnym panelu.</div>
                  <img src="obrazki/logo/logo_google_analytics_4.png" alt="" />
                </td></tr>
                
                <tr class="SledzeniePozycja">
                  <td>
                    <label>Włącz moduł Google Analytics 4:</label>
                  </td>
                  <td>
                    <?php
                    echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_GOOGLE4_WLACZONY']['1'], $parametr['INTEGRACJA_GOOGLE4_WLACZONY']['0'], 'integracja_google4_wlaczony', $parametr['INTEGRACJA_GOOGLE4_WLACZONY']['2'], '', $parametr['INTEGRACJA_GOOGLE4_WLACZONY']['3'] );
                    ?>
                  </td>
                </tr>
                                
                <tr class="SledzeniePozycja">
                  <td>
                    <label class="required" for="integracja_google4_id">Identyfikator Google:</label>
                  </td>
                  <td>
                    <?php
                    echo '<input type="text" id="integracja_google4_id" name="integracja_google4_id" value="'.$parametr['INTEGRACJA_GOOGLE4_ID']['0'].'" size="53" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_GOOGLE4_ID']['2'].'</b></em>';
                    ?>
                  </td>
                </tr>
                
                <tr class="SledzeniePozycja">
                  <td>
                    <label>Generuj dodatkowo tablicę dataLayer ecommerce:</label>
                  </td>
                  <td>
                    <?php
                    echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_GOOGLE4_DATALAYER']['1'], $parametr['INTEGRACJA_GOOGLE4_DATALAYER']['0'], 'integracja_google4_datalayer', $parametr['INTEGRACJA_GOOGLE4_DATALAYER']['2'], '', $parametr['INTEGRACJA_GOOGLE4_DATALAYER']['3'] );
                    ?>
                  </td>
                </tr>                

                <tr>
                  <td colspan="2">
                    <div class="przyciski_dolne">
                      <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'google4' ? $wynik : '' ); ?>
                    </div>
                  </td>
                </tr>
                
              </table>
              
            </div>

            </form>
            
          </div>          
          

          <div class="Sledzenie SledzenieUkryte">
          
            <form action="integracje/konfiguracja_sledzenie.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="googleGTMForm" class="cmxform"> 
            
            <div>
              <input type="hidden" name="akcja" value="zapisz" />
              <input type="hidden" name="system" value="googleGTM" />
            </div>
            
            <div class="ObramowanieForm">
            
              <table>
              
                <tr class="DivNaglowek">
                  <td colspan="2">Google Tag Manager</td>
                </tr>
                
                <tr><td colspan="2" class="SledzenieOpis">
                  <div>Menedżer tagów Google to system zarządzania tagami, umożliwiający szybkie i łatwe aktualizowanie kodów śledzenia i powiązanych fragmentów kodu, czyli tagów, w witrynie lub aplikacji mobilnej. Po dodaniu niewielkiego fragmentu kodu Menedżera tagów do projektu możesz łatwo i bezpiecznie wdrożyć ustawienia analityki i tagów pomiarowych, korzystając z internetowego interfejsu użytkownika.</div>
                  <img src="obrazki/logo/logo_google_gtm.png" alt="" />
                </td></tr>
                
                <tr class="SledzeniePozycja">
                  <td>
                    <label>Włącz moduł Google Tag Manager:</label>
                  </td>
                  <td>
                    <?php
                    echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_GOOGLE_GTM_WLACZONY']['1'], $parametr['INTEGRACJA_GOOGLE_GTM_WLACZONY']['0'], 'integracja_google_gtm_wlaczony', $parametr['INTEGRACJA_GOOGLE_GTM_WLACZONY']['2'], '', $parametr['INTEGRACJA_GOOGLE_GTM_WLACZONY']['3'] );
                    ?>
                  </td>
                </tr>
                                
                <tr class="SledzeniePozycja">
                  <td>
                    <label class="required" for="integracja_google_gtm_id">Identyfikator Google Tag Manager:</label>
                  </td>
                  <td>
                    <?php
                    echo '<input type="text" id="integracja_google_gtm_id" name="integracja_google_gtm_id" value="'.$parametr['INTEGRACJA_GOOGLE_GTM_ID']['0'].'" size="53" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_GOOGLE_GTM_ID']['2'].'</b></em>';
                    ?>
                  </td>
                </tr>

                <tr>
                  <td colspan="2">
                    <div class="przyciski_dolne">
                      <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'googleGTM' ? $wynik : '' ); ?>
                    </div>
                  </td>
                </tr>
                
              </table>
              
            </div>

            </form>
            
          </div>          
          
          
          
          
          
          
          
          
          
          
          
          
          
          
          
          
          
          <div class="Sledzenie SledzenieUkryte">
          
            <form action="integracje/konfiguracja_sledzenie.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="googleWebForm" class="cmxform"> 
            
            <div>
              <input type="hidden" name="akcja" value="zapisz" />
              <input type="hidden" name="system" value="googleWeb" />
            </div>
            
            <div class="ObramowanieForm">
            
              <table>
              
                <tr class="DivNaglowek">
                  <td colspan="2">Google (weryfikacja własności witryny)</td>
                </tr>
                
                <tr><td colspan="2" class="SledzenieOpis">
                  <div>Weryfikacja jest procesem, w którym możesz udowodnić, że jesteś właścicielem sklepu. Search Console musi zweryfikować prawo własności, bo zweryfikowani właściciele mają dostęp do poufnych danych witryny w wyszukiwarce Google i mogą mieć wpływ na jej pozycję oraz działanie w wyszukiwarce i innych usługach Google. </div>
                  <img src="obrazki/logo/logo_google_web.png" alt="" />
                </td></tr>
                
                <tr class="SledzeniePozycja">
                  <td>
                    <label for="integracja_google_weryfikacja">Kod weryfikacyjny:</label>
                  </td>
                  <td>
                    <?php
                    echo '<input type="text" name="integracja_google_weryfikacja" id="integracja_google_weryfikacja" value="'.$parametr['INTEGRACJA_GOOGLE_WERYFIKACJA']['0'].'" size="53" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_GOOGLE_WERYFIKACJA']['2'].'</b></em>';
                    ?>
                    <span class="maleInfo">
                        Szczegóły weryfikacji opisane są na stronie google: <a href="https://support.google.com/webmasters/answer/9008080?hl=pl#meta_tag_verification" target="_blank">https://support.google.com/webmasters/answer/9008080?hl=pl</a> <br /><br />
                        Trzeba wybrać opcję weryfikacji przy użyciu tagu HTML - otrzyma się wtedy kod: <br />
                        <code style="margin-top:5px;display:block">&lt;meta name="google-site-verification" content="<b style="color:#ff0000">1234545</b>" /&gt;</code> <br />
                        W polu "kod weryfikacyjny" trzeba wpisać oznaczoną powyżej na czerwono
                    </span>                     
                  </td>
                </tr>
                
                <tr>
                  <td colspan="2">
                    <div class="przyciski_dolne">
                      <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'googleWeb' ? $wynik : '' ); ?>
                    </div>
                  </td>
                </tr>
                
              </table>
              
            </div>

            </form>
            
          </div>          

          <div class="Sledzenie SledzenieUkryte">
          
            <form action="integracje/konfiguracja_sledzenie.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="googleConversionForm" class="cmxform"> 
            
            <div>
              <input type="hidden" name="akcja" value="zapisz" />
              <input type="hidden" name="system" value="googleConversion" />
            </div>
            
            <div class="ObramowanieForm">
            
              <table>
              
                <tr class="DivNaglowek">
                  <td colspan="2">Google Ads</td>
                </tr>
                
                <tr><td colspan="2" class="SledzenieOpis">
                  <div>Dodaj do swojej witryny tag Google, aby skonfigurować w niej lub w aplikacjach źródło danych o odbiorcach pomagające docierać do osób, które odwiedziły Twoją witrynę lub skorzystały z Twojej aplikacji. Tag Google to biblioteka do tagowania witryn internetowych umożliwiająca Google pomiar ich skuteczności, śledzenie konwersji i stosowanie usług korzystających z segmentów danych. Jest to blok kodu powodujący dodawanie użytkowników witryny do segmentów danych, na które można potem kierować reklamy.</div>
                  <img src="obrazki/logo/logo_google_conversion.png" alt="" />
                </td></tr>
                
                <tr class="SledzeniePozycja">
                  <td>
                    <label>Włącz kod Google Ads:</label>
                  </td>
                  <td>
                    <?php
                    echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_GOOGLE_KONWERSJA_WLACZONY']['1'], $parametr['INTEGRACJA_GOOGLE_KONWERSJA_WLACZONY']['0'], 'integracja_google_konwersja_wlaczony', $parametr['INTEGRACJA_GOOGLE_KONWERSJA_WLACZONY']['2'], '', $parametr['INTEGRACJA_GOOGLE_KONWERSJA_WLACZONY']['3'] );
                    ?>
                  </td>
                </tr>

                <tr class="SledzeniePozycja">
                  <td>
                    <label class="required" for="integracja_google_konwersja">Identyfikator konwersji:</label>
                  </td>
                  <td>
                    <?php
                    echo '<input type="text" name="integracja_google_konwersja" id="integracja_google_konwersja" value="'.$parametr['INTEGRACJA_GOOGLE_KONWERSJA']['0'].'" size="53" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_GOOGLE_KONWERSJA']['2'].'</b></em>';
                    ?>
                  </td>
                </tr>
                
                <tr>
                  <td colspan="2">
                    <div class="przyciski_dolne">
                      <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'googleConversion' ? $wynik : '' ); ?>
                    </div>
                  </td>
                </tr>
                
              </table>
              
            </div>

            </form>
            
          </div>          

          <div class="Sledzenie SledzenieUkryte">
          
            <form action="integracje/konfiguracja_sledzenie.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="fbpixelForm" class="cmxform"> 
            
            <div>
              <input type="hidden" name="akcja" value="zapisz" />
              <input type="hidden" name="system" value="fbpixel" />
            </div>
            
            <div class="ObramowanieForm">
            
              <table>
              
                <tr class="DivNaglowek">
                  <td colspan="2">Pixel Facebook</td>
                </tr>
                
                <tr><td colspan="2" class="SledzenieOpis">
                  <div>Piksel Facebooka to kod, umożliwiający pomiar, optymalizację i tworzenie grup odbiorców kampanii reklamowych. Dzięki pikselowi Facebooka możesz wykorzystać działania podejmowane przez odbiorców w witrynie na różnych urządzeniach, aby przeprowadzać bardziej skuteczne kampanie reklamowe.</div>
                  <img src="obrazki/logo/fb_pixel.png" alt="" />
                </td></tr>
                
                <tr class="SledzeniePozycja">
                  <td>
                    <label>Włącz moduł piksel Facebook:</label>
                  </td>
                  <td>
                    <?php
                    echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_FB_PIXEL_WLACZONY']['1'], $parametr['INTEGRACJA_FB_PIXEL_WLACZONY']['0'], 'integracja_fb_pixel_wlaczony', $parametr['INTEGRACJA_FB_PIXEL_WLACZONY']['2'], '', $parametr['INTEGRACJA_FB_PIXEL_WLACZONY']['3'] );
                    ?>
                  </td>
                </tr>
                
                <tr class="SledzeniePozycja">
                  <td>
                    <label class="required" for="integracja_fb_pixel_id">Identyfikator piksela pobrany z Facebook:</label>
                  </td>
                  <td>
                    <?php
                    echo '<input type="text" id="integracja_fb_pixel_id" name="integracja_fb_pixel_id" value="'.$parametr['INTEGRACJA_FB_PIXEL_ID']['0'].'" size="53" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_FB_PIXEL_ID']['2'].'</b></em>';
                    ?>
                    <span class="maleInfo">
                        Integracja z oparciu o kod Javascript<br />
                        Unikalny kod piksela Facebook generowany na stronie Facebook; w pole trzeba wpisać unikalny numer zaznaczony na poniższym obrazku żółtym kolorem; obsługiwane zdarzenia: <br /><br />
                        ViewContent - strona karty produktu <br />
                        Search - wyniki wyszukiwania oraz strona wyszukiwania zaawansowanego <br />
                        AddToCart - dodanie produktu do koszyka <br />
                        AddToWishlist - dodanie produktu do schowka <br />
                        InitiateCheckout - strona koszyka <br />
                        Purchase - strona podsumowania zamówienia (z przekazaniem wartości zamówienia) <br /><br />
                        <img style="border:1px solid #ccc" src="obrazki/pomoc/fb_pixel.jpg" id="ImgFbPixel" alt="" />
                    </span>                    
                  </td>
                </tr>
                
                <tr class="SledzeniePozycja">
                  <td>
                    <label for="integracja_fb_pixel_token">Token (access token) pobrany z Facebook:</label>
                  </td>
                  <td>
                    <?php
                    echo '<input type="text" id="integracja_fb_pixel_token" name="integracja_fb_pixel_token" value="'.$parametr['INTEGRACJA_FB_PIXEL_TOKEN']['0'].'" size="53" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_FB_PIXEL_TOKEN']['2'].'</b></em>';
                    ?>    
                    <span class="maleInfo">
                        Integracja z oparciu o Graph API FB. <br />
                        Token jest wymagany do poprawnej integracji Pixel FB w oparciu o Graph API FB. Jeżeli token nie będzie wpisany - ta część integracji nie będzie aktywna. Do poprawnego działania wymagany jest podanie poprawnego identyfikatora pixel w oknie powyżej.<br /><br />
                    </span>                     
                  </td>
                </tr>                
                
                <tr>
                  <td colspan="2">
                    <div class="przyciski_dolne">
                      <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'fbpixel' ? $wynik : '' ); ?>
                    </div>
                  </td>
                </tr>
                
              </table>
              
            </div>

            </form>
            
          </div>                   
          
          <div class="Sledzenie SledzenieUkryte">
          
            <form action="integracje/konfiguracja_sledzenie.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="ceneoForm" class="cmxform"> 
            
            <div>
              <input type="hidden" name="akcja" value="zapisz" />
              <input type="hidden" name="system" value="ceneo" />
            </div>
            
            <div class="ObramowanieForm">
            
              <table>
              
                <tr class="DivNaglowek">
                  <td colspan="2">CENEO zaufane opinie</td>
                </tr>
                
                <tr><td colspan="2" class="SledzenieOpis">
                  <div>"Zaufane Opinie" to szczególny system zbierania opinii o transakcjach w sklepach internetowych. Komentarze z zielonym znaczkiem są publikowane na podstawie ankiet wypełnianych przez osoby, które złożyły zamówienie on-line w sklepie objętym programem „Zaufanych Opinii”. Czytając taką opinię masz pewność, że informacje o wybranym sklepie pochodzą od rzeczywistych Klientów.</div>
                  <img src="obrazki/logo/logo_opinie_ceneo.png" alt="" />
                </td></tr>
                
                <tr class="SledzeniePozycja">
                  <td>
                    <label>Włącz moduł zaufanych opinii CENEO:</label>
                  </td>
                  <td>
                    <?php
                    echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_CENEO_OPINIE_WLACZONY']['1'], $parametr['INTEGRACJA_CENEO_OPINIE_WLACZONY']['0'], 'integracja_ceneo_opinie_wlaczony', $parametr['INTEGRACJA_CENEO_OPINIE_WLACZONY']['2'], '', $parametr['INTEGRACJA_CENEO_OPINIE_WLACZONY']['3'] );
                    ?>
                  </td>
                </tr>

                <tr class="SledzeniePozycja">
                  <td>
                    <label class="required" for="integracja_ceneo_opinie_id">Identyfikator GUID:</label>
                  </td>
                  <td>
                    <?php
                    echo '<input type="text" id="integracja_ceneo_opinie_id" name="integracja_ceneo_opinie_id" value="'.$parametr['INTEGRACJA_CENEO_OPINIE_ID']['0'].'" size="53" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_CENEO_OPINIE_ID']['2'].'</b></em>';
                    ?>
                  </td>
                </tr>

                <tr class="SledzeniePozycja">
                  <td>
                    <label for="integracja_ceneo_opinie_czas">Liczba dni do wysłania ankiety:</label>
                  </td>
                  <td>
                    <?php
                    echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_CENEO_OPINIE_CZAS']['1'], $parametr['INTEGRACJA_CENEO_OPINIE_CZAS']['0'], 'integracja_ceneo_opinie_czas', $parametr['INTEGRACJA_CENEO_OPINIE_CZAS']['2'], '', $parametr['INTEGRACJA_CENEO_OPINIE_CZAS']['3'], '', '', 'id="integracja_ceneo_opinie_czas"' );
                    ?>
                  </td>
                </tr>
                
                <tr class="SledzeniePozycja">
                  <td>
                    <label>Wyświetlanie recenzji o produktach (Zaufane Opinie Plus):</label>
                  </td>
                  <td>
                    <?php
                    echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_CENEO_OPINIE_RECENZJE_PRODUKTOW']['1'], $parametr['INTEGRACJA_CENEO_OPINIE_RECENZJE_PRODUKTOW']['0'], 'integracja_ceneo_opinie_recenzje_produktow', $parametr['INTEGRACJA_CENEO_OPINIE_RECENZJE_PRODUKTOW']['2'], '', $parametr['INTEGRACJA_CENEO_OPINIE_RECENZJE_PRODUKTOW']['3'] );
                    ?>
                  </td>
                </tr>

                <tr class="SledzeniePozycja">
                  <td>
                    <label for="integracja_ceneo_opinie_recenzje_produktow_miejsce">Miejsce wyświetlania recenzji o produktach na karcie produktu (Zaufane Opinie Plus):</label>
                  </td>
                  <td>
                    <?php
                    echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_CENEO_OPINIE_RECENZJE_PRODUKTOW_MIEJSCE']['1'], $parametr['INTEGRACJA_CENEO_OPINIE_RECENZJE_PRODUKTOW_MIEJSCE']['0'], 'integracja_ceneo_opinie_recenzje_produktow_miejsce', $parametr['INTEGRACJA_CENEO_OPINIE_RECENZJE_PRODUKTOW_MIEJSCE']['2'], '', $parametr['INTEGRACJA_CENEO_OPINIE_RECENZJE_PRODUKTOW_MIEJSCE']['3'], '', '', 'id="integracja_ceneo_opinie_recenzje_produktow_miejsce"' );
                    ?>
                    <span class="maleInfo">
                        Opcja pod zakładkami dostępna tylko dla szablonów, które mają taką funkcję.
                    </span>                      
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
          
            <form action="integracje/konfiguracja_sledzenie.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="opineoForm" class="cmxform"> 
            
            <div>
              <input type="hidden" name="akcja" value="zapisz" />
              <input type="hidden" name="system" value="opineo" />
            </div>
            
            <div class="ObramowanieForm">
            
              <table>
              
                <tr class="DivNaglowek">
                  <td colspan="2">OPINEO</td>
                </tr>
                
                <tr><td colspan="2" class="SledzenieOpis">
                  <div>Opineo.pl jest serwisem propagującym zakupy w internecie. Gromadzi opinie użytkowników o dokonanych przez nich transakcjach po to, by e-zakupy były jak najmniej ryzykowne. Przez ponad 2 lata działalności zespół Opineo.pl stworzył jeden z największych w Polsce serwisów oceniających sklepy internetowe. Każdy sklep jest traktowany na równych zasadach, nie jesteśmy zależni od żadnego sklepu czy porównywarki cenowej, to przekłada się na naszą wiarygodność i dużą liczbę użytkowników.</div>
                  <img src="obrazki/logo/logo_opineo.png" alt="" />
                </td></tr>
                
                <tr class="SledzeniePozycja">
                  <td>
                    <label>Włącz moduł Opinie OPINEO:</label>
                  </td>
                  <td>
                    <?php
                    echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_OPINEO_OPINIE_WLACZONY']['1'], $parametr['INTEGRACJA_OPINEO_OPINIE_WLACZONY']['0'], 'integracja_opineo_opinie_wlaczony', $parametr['INTEGRACJA_OPINEO_OPINIE_WLACZONY']['2'], '', $parametr['INTEGRACJA_OPINEO_OPINIE_WLACZONY']['3'] );
                    ?>
                  </td>
                </tr>
                
                <tr class="SledzeniePozycja">
                  <td>
                    <label class="required" for="integracja_opineo_opinie_login">Identyfikator:</label>
                  </td>
                  <td>
                    <?php
                    echo '<input type="text" name="integracja_opineo_opinie_login" id="integracja_opineo_opinie_login" value="'.$parametr['INTEGRACJA_OPINEO_OPINIE_LOGIN']['0'].'" size="53" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_OPINEO_OPINIE_LOGIN']['2'].'</b></em>';
                    ?>
                  </td>
                </tr>
                
                <tr class="SledzeniePozycja">
                  <td>
                    <label for="integracja_opineo_opinie_czas">Liczba dni do wysłania zaproszenia:</label>
                  </td>
                  <td>
                    <?php
                    echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_OPINEO_OPINIE_CZAS']['1'], $parametr['INTEGRACJA_OPINEO_OPINIE_CZAS']['0'], 'integracja_opineo_opinie_czas', $parametr['INTEGRACJA_OPINEO_OPINIE_CZAS']['2'], '', $parametr['INTEGRACJA_OPINEO_OPINIE_CZAS']['3'], '', '', 'id="integracja_opineo_opinie_czas"' );
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
          
          <div class="Sledzenie SledzenieUkryte">
          
            <form action="integracje/konfiguracja_sledzenie.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="okazjeForm" class="cmxform"> 
            
            <div>
              <input type="hidden" name="akcja" value="zapisz" />
              <input type="hidden" name="system" value="okazje" />
            </div>
            
            <div class="ObramowanieForm">
            
              <table>
              
                <tr class="DivNaglowek">
                  <td colspan="2">Wiarygodne Opinie okazje.info</td>
                </tr>
                
                <tr><td colspan="2" class="SledzenieOpis">
                  <div>Program Wiarygodne Opinie to system gromadzenia i kontrolowania wartościowych opinii i ocen, wystawianych przez Twoich klientów po dokonaniu zakupu. Udział w Programie pozwala na podniesienie wiarygodności Twojego sklepu wśród użytkowników, kupujących online. W Programie może uczestniczyć każdy sklep, który współpracuje z Okazje.info. Przystępując do Programu, Twój sklep otrzyma specjalne oznaczenie na listingach oraz na stronie sklepu, co pozwoli wyróżnić go na tle konkurentów.</div>
                  <img src="obrazki/logo/logo_okazje.png" alt="" />
                </td></tr>
                
                <tr class="SledzeniePozycja">
                  <td>
                    <label>Włącz moduł opinii okazje.info:</label>
                  </td>
                  <td>
                    <?php
                    echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_OKAZJE_WLACZONY']['1'], $parametr['INTEGRACJA_OKAZJE_WLACZONY']['0'], 'integracja_okazje_wlaczony', $parametr['INTEGRACJA_OKAZJE_WLACZONY']['2'], '', $parametr['INTEGRACJA_OKAZJE_WLACZONY']['3'] );
                    ?>
                  </td>
                </tr>
                
                <tr class="SledzeniePozycja">
                  <td>
                    <label class="required" for="integracja_okazje_id">Identyfikator w serwisie okazje.info:</label>
                  </td>
                  <td>
                    <?php
                    echo '<input type="text" id="integracja_okazje_id" name="integracja_okazje_id" value="'.$parametr['INTEGRACJA_OKAZJE_ID']['0'].'" size="53" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_OKAZJE_ID']['2'].'</b></em>';
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
          
            <form action="integracje/konfiguracja_sledzenie.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="googleopinieForm" class="cmxform"> 
            
            <div>
              <input type="hidden" name="akcja" value="zapisz" />
              <input type="hidden" name="system" value="googleopinie" />
            </div>
            
            <div class="ObramowanieForm">
            
              <table>
              
                <tr class="DivNaglowek">
                  <td colspan="2">Opinie konsumenckie Google</td>
                </tr>
                
                <tr><td colspan="2" class="SledzenieOpis">
                  <div>Opinie konsumenckie Google to bezpłatny program, który pozwala Google gromadzić w Twoim imieniu opinie Twoich klientów dotyczące zakupów u Ciebie (lub opinie o produktach, które sprzedajesz). Cały proces rejestracji może potrwać mniej niż kilka godzin, a uczestnicy programu mogą wyświetlać w swoich witrynach plakietkę z logo Google i swoją oceną sprzedawcy.</div>
                  <img src="obrazki/logo/google_opinie.png" alt="" />
                </td></tr>
                
                <tr class="SledzeniePozycja">
                  <td>
                    <label>Włącz moduł Opinie konsumenckie Google:</label>
                  </td>
                  <td>
                    <?php
                    echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_GOOGLE_OPINIE_WLACZONY']['1'], $parametr['INTEGRACJA_GOOGLE_OPINIE_WLACZONY']['0'], 'integracja_google_opinie_wlaczony', $parametr['INTEGRACJA_GOOGLE_OPINIE_WLACZONY']['2'], '', $parametr['INTEGRACJA_GOOGLE_OPINIE_WLACZONY']['3'] );
                    ?>
                  </td>
                </tr>
                
                <tr class="SledzeniePozycja">
                  <td>
                    <label class="required" for="integracja_google_opinie_merchant_id">Identyfikator sprzedawcy w Merchant Center:</label>
                  </td>
                  <td>
                    <?php
                    echo '<input type="text" name="integracja_google_opinie_merchant_id" id="integracja_google_opinie_merchant_id" value="'.$parametr['INTEGRACJA_GOOGLE_OPINIE_MERCHANT_ID']['0'].'" size="23" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_GOOGLE_OPINIE_MERCHANT_ID']['2'].'</b></em>';
                    ?>
                  </td>
                </tr>
                
                <tr class="SledzeniePozycja">
                  <td>
                    <label for="integracja_google_opinie_czas">Liczba dni po ilu należy wysłać zaproszenie do napisania opinii (ilość dni od złożenia zamówienia):</label>
                  </td>
                  <td>
                    <?php
                    echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_GOOGLE_OPINIE_CZAS']['1'], $parametr['INTEGRACJA_GOOGLE_OPINIE_CZAS']['0'], 'integracja_google_opinie_czas', $parametr['INTEGRACJA_GOOGLE_OPINIE_CZAS']['2'], '', $parametr['INTEGRACJA_GOOGLE_OPINIE_CZAS']['3'], '', '', 'id="integracja_google_opinie_czas"' );
                    ?>
                  </td>
                </tr>
                
                <tr class="SledzeniePozycja">
                  <td>
                    <label>Czy wyświetlać grafikę plakietki z oceną Opinie konsumenckie Google ?</label>
                  </td>
                  <td>
                    <?php
                    echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_GOOGLE_OPINIE_PLAKIETKA']['1'], $parametr['INTEGRACJA_GOOGLE_OPINIE_PLAKIETKA']['0'], 'integracja_google_opinie_plakietka', $parametr['INTEGRACJA_GOOGLE_OPINIE_PLAKIETKA']['2'], '', $parametr['INTEGRACJA_GOOGLE_OPINIE_PLAKIETKA']['3'] );
                    ?>
                  </td>
                </tr>   

                <tr class="SledzeniePozycja">
                  <td>
                    <label for="integracja_google_opinie_plakietka_polozenie">Położenie plakietki z oceną:</label>
                  </td>
                  <td>
                    <?php
                    echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_GOOGLE_OPINIE_PLAKIETKA_POLOZENIE']['1'], $parametr['INTEGRACJA_GOOGLE_OPINIE_PLAKIETKA_POLOZENIE']['0'], 'integracja_google_opinie_plakietka_polozenie', $parametr['INTEGRACJA_GOOGLE_OPINIE_PLAKIETKA_POLOZENIE']['2'], '', $parametr['INTEGRACJA_GOOGLE_OPINIE_PLAKIETKA_POLOZENIE']['3'], '', '', 'id="integracja_google_opinie_plakietka_polozenie"' );
                    ?>
                  </td>
                </tr>                
                
                <tr>
                  <td colspan="2">
                    <div class="przyciski_dolne">
                      <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'googleopinie' ? $wynik : '' ); ?>
                    </div>
                  </td>
                </tr>
                
              </table>
              
            </div>

            </form>
            
          </div>          

          <div class="Sledzenie SledzenieUkryte">
          
            <form action="integracje/konfiguracja_sledzenie.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="salesmediaForm" class="cmxform"> 
            
            <div>
              <input type="hidden" name="akcja" value="zapisz" />
              <input type="hidden" name="system" value="salesmedia" />
            </div>
            
            <div class="ObramowanieForm">
            
              <table>
              
                <tr class="DivNaglowek">
                  <td colspan="2">Program partnerski Salesmedia.pl</td>
                </tr>
                
                <tr><td colspan="2" class="SledzenieOpis">
                  <div>Sieć afiliacyjna nastawiona na budowanie długotrwałych relacji z wydawcą i reklamodawcą. Naszym celem jest jak najefektywniejsze wykorzystywanie powierzchni reklamowych w postaci generowanych sprzedaży. Rozliczamy się tylko za wygenerowane sprzedaże przez Wydawców z naszej sieci, a przy tym świadczymy innowacyjną i transparentną technologie, która da Wydawcy jak i Reklamodawcy jeszcze większą kontrolę na prowadzonymi działaniami.</div>
                  <img src="obrazki/logo/logo_salesmedia.png" alt="" />
                </td></tr>
                
                <tr class="SledzeniePozycja">
                  <td>
                    <label>Włącz integrację Salesmedia:</label>
                  </td>
                  <td>
                    <?php
                    echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_SALESMEDIA_WLACZONY']['1'], $parametr['INTEGRACJA_SALESMEDIA_WLACZONY']['0'], 'integracja_salesmedia_wlaczony', $parametr['INTEGRACJA_SALESMEDIA_WLACZONY']['2'], '', $parametr['INTEGRACJA_SALESMEDIA_WLACZONY']['3'] );
                    ?>
                  </td>
                </tr>
                
                <tr class="SledzeniePozycja">
                  <td>
                    <label class="required" for="integracja_salesmedia_id">Identyfikator w serwisie Salesmedia:</label>
                  </td>
                  <td>
                    <?php
                    echo '<input type="text" id="integracja_salesmedia_id" name="integracja_salesmedia_id" value="'.$parametr['INTEGRACJA_SALESMEDIA_ID']['0'].'" size="53" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_SALESMEDIA_ID']['2'].'</b></em>';
                    ?>
                  </td>
                </tr>
                
                <tr>
                  <td colspan="2">
                    <div class="przyciski_dolne">
                      <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'salesmedia' ? $wynik : '' ); ?>
                    </div>
                  </td>
                </tr>
                
              </table>
              
            </div>

            </form>
            
          </div>
          
          <div class="Sledzenie SledzenieUkryte">
          
            <form action="integracje/konfiguracja_sledzenie.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="edroneForm" class="cmxform"> 
            
            <div>
              <input type="hidden" name="akcja" value="zapisz" />
              <input type="hidden" name="system" value="edrone" />
            </div>
            
            <div class="ObramowanieForm">
            
              <table>
              
                <tr class="DivNaglowek">
                  <td colspan="2">Edrone - system klasy CRM dla ecommerce</td>
                </tr>
                
                <tr><td colspan="2" class="SledzenieOpis">
                  <div>edrone CRM identyfikuje użytkowników Twojego sklepu. Dzięki edrone zatrzymasz klientów, którzy dodali w Twoim sklepie produkty do koszyka, ale nie sfinalizowali zakupu. Social CRM (ang. customer relationship management) podpowie Ci, czego szukają odwiedzający, ilu Twój klient ma subskrybentów, a ile osób śledzi go na Twitterze, z jakiego systemu korzysta, czy jest obiecującym, nowym, czy też może powracającym klientem.</div>
                  <img src="obrazki/logo/edrone.png" alt="" />
                </td></tr>
                
                <tr class="SledzeniePozycja">
                  <td>
                    <label>Włącz integrację edrone.me:</label>
                  </td>
                  <td>
                    <?php
                    echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_EDRONE_WLACZONY']['1'], $parametr['INTEGRACJA_EDRONE_WLACZONY']['0'], 'integracja_edrone_wlaczony', $parametr['INTEGRACJA_EDRONE_WLACZONY']['2'], '', $parametr['INTEGRACJA_EDRONE_WLACZONY']['3'] );
                    ?>
                  </td>
                </tr>
                
                <tr class="SledzeniePozycja">
                  <td>
                    <label class="required" for="integracja_edrone_id">Identyfikator w serwisie edrone.me:</label>
                  </td>
                  <td>
                    <?php
                    echo '<input type="text" id="integracja_edrone_api" name="integracja_edrone_api" value="'.$parametr['INTEGRACJA_EDRONE_API']['0'].'" size="53" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_EDRONE_API']['2'].'</b></em>';
                    ?>
                  </td>
                </tr>
                
                <tr>
                  <td colspan="2">
                    <div class="przyciski_dolne">
                      <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'edrone' ? $wynik : '' ); ?>
                    </div>
                  </td>
                </tr>
                
              </table>
              
            </div>

            </form>
            
          </div>          

          <div class="Sledzenie SledzenieUkryte">
          
            <form action="integracje/konfiguracja_sledzenie.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="salesmangoForm" class="cmxform"> 
            
            <div>
              <input type="hidden" name="akcja" value="zapisz" />
              <input type="hidden" name="system" value="salesmango" />
            </div>
            
            <div class="ObramowanieForm">
            
              <table>
              
                <tr class="DivNaglowek">
                  <td colspan="2">SALESmanago - system automatyzacji marketingu</td>
                </tr>
                
                <tr><td colspan="2" class="SledzenieOpis">
                  <div>Identyfikuj osoby odwiedzające Twoją stronę. Monitoruj ich zachowanie i transakcje. Dostarczaj spersonalizowane oferty we wszystkich kanałach marketingowych. Zwiększ skuteczność emaili o 400% i konwersję na stronie www o 100%.</div>
                  <img src="obrazki/logo/salesmanago.png" alt="" />
                </td></tr>
                
                <tr class="SledzeniePozycja">
                  <td>
                    <label>Włącz integrację Salesmanago.pl:</label>
                  </td>
                  <td>
                    <?php
                    echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_SALESMANAGO_WLACZONY']['1'], $parametr['INTEGRACJA_SALESMANAGO_WLACZONY']['0'], 'integracja_salesmanago_wlaczony', $parametr['INTEGRACJA_SALESMANAGO_WLACZONY']['2'], '', $parametr['INTEGRACJA_SALESMANAGO_WLACZONY']['3'] );
                    ?>
                  </td>
                </tr>
                
                <tr class="SledzeniePozycja">
                  <td>
                    <label class="required" for="integracja_salesmanago_endpoint">Adres Endpoint:</label>
                  </td>
                  <td>
                    <?php
                    echo '<input type="text" id="integracja_salesmanago_endpoint" name="integracja_salesmanago_endpoint" value="'.$parametr['INTEGRACJA_SALESMANAGO_ENDPOINT']['0'].'" size="53" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_SALESMANAGO_ENDPOINT']['2'].'</b></em>';
                    ?>
                  </td>
                </tr>
                
                <tr class="SledzeniePozycja">
                  <td>
                    <label class="required" for="integracja_salesmanago_id_klienta">ID klienta:</label>
                  </td>
                  <td>
                    <?php
                    echo '<input type="text" id="integracja_salesmanago_id_klienta" name="integracja_salesmanago_id_klienta" value="'.$parametr['INTEGRACJA_SALESMANAGO_ID_KLIENTA']['0'].'" size="53" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_SALESMANAGO_ID_KLIENTA']['2'].'</b></em>';
                    ?>
                  </td>
                </tr>

                <tr class="SledzeniePozycja">
                  <td>
                    <label class="required" for="integracja_salesmanago_api_secret">API Secret:</label>
                  </td>
                  <td>
                    <?php
                    echo '<input type="text" id="integracja_salesmanago_api_secret" name="integracja_salesmanago_api_secret" value="'.$parametr['INTEGRACJA_SALESMANAGO_API_SECRET']['0'].'" size="53" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_SALESMANAGO_API_SECRET']['2'].'</b></em>';
                    ?>
                  </td>
                </tr>

                <tr class="SledzeniePozycja">
                  <td>
                    <label class="required" for="integracja_salesmanago_email">Adres email w  serwisie Salesmanago:</label>
                  </td>
                  <td>
                    <?php
                    echo '<input type="text" id="integracja_salesmanago_email" name="integracja_salesmanago_email" value="'.$parametr['INTEGRACJA_SALESMANAGO_EMAIL']['0'].'" size="53" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_SALESMANAGO_EMAIL']['2'].'</b></em>';
                    ?>
                  </td>
                </tr>                
                
                <tr>
                  <td colspan="2">
                    <div class="przyciski_dolne">
                      <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'salesmango' ? $wynik : '' ); ?>
                    </div>
                  </td>
                </tr>
                
              </table>
              
            </div>

            </form>
            
          </div>

          <div class="Sledzenie SledzenieUkryte">
          
            <form action="integracje/konfiguracja_sledzenie.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="criteoForm" class="cmxform"> 
            
            <div>
              <input type="hidden" name="akcja" value="zapisz" />
              <input type="hidden" name="system" value="criteo" />
            </div>
            
            <div class="ObramowanieForm">
            
              <table>
              
                <tr class="DivNaglowek">
                  <td colspan="2">Criteo - One Tag</td>
                </tr>
                
                <tr><td colspan="2" class="SledzenieOpis">
                  <div>Criteo OneTag to tag JavaScript, który pozwala Criteo gromadzić zamiary użytkowników odwiedzających Państwa stronę internetową.</div>
                  <img src="obrazki/logo/criteo.png" alt="" />
                </td></tr>
                
                <tr class="SledzeniePozycja">
                  <td>
                    <label>Włącz integrację Criteo:</label>
                  </td>
                  <td>
                    <?php
                    echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_CRITEO_WLACZONY']['1'], $parametr['INTEGRACJA_CRITEO_WLACZONY']['0'], 'integracja_criteo_wlaczony', $parametr['INTEGRACJA_CRITEO_WLACZONY']['2'], '', $parametr['INTEGRACJA_CRITEO_WLACZONY']['3'] );
                    ?>
                  </td>
                </tr>
                
                <tr class="SledzeniePozycja">
                  <td>
                    <label class="required" for="integracja_edrone_id">Identyfikator w serwisie Criteo:</label>
                  </td>
                  <td>
                    <?php
                    echo '<input type="text" id="integracja_criteo_id" name="integracja_criteo_id" value="'.$parametr['INTEGRACJA_CRITEO_ID']['0'].'" size="53" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_CRITEO_ID']['2'].'</b></em>';
                    ?>
                  </td>
                </tr>
                
                <tr>
                  <td colspan="2">
                    <div class="przyciski_dolne">
                      <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'criteo' ? $wynik : '' ); ?>
                    </div>
                  </td>
                </tr>
                
              </table>
              
            </div>

            </form>
            
          </div>         

          <div class="Sledzenie SledzenieUkryte">
          
            <form action="integracje/konfiguracja_sledzenie.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="sareForm" class="cmxform"> 
            
            <div>
              <input type="hidden" name="akcja" value="zapisz" />
              <input type="hidden" name="system" value="sare" />
            </div>
            
            <div class="ObramowanieForm">
            
              <table>
              
                <tr class="DivNaglowek">
                  <td colspan="2">SARE - marketing automation</td>
                </tr>
                
                <tr><td colspan="2" class="SledzenieOpis">
                  <div>Jedna marka, wiele kanałów, jedno wrażenie! Komunikuj się za pomocą różnych kanałów w sposób spójny i zintegrowany. Dbaj o jak najlepsze doświadczenia odbiorców i indywidualne podejście, niezależnie od wybranego kanału komunikacji.</div>
                  <img src="obrazki/logo/sare.png" alt="" />
                </td></tr>
                
                <tr class="SledzeniePozycja">
                  <td>
                    <label>Włącz integrację SARE:</label>
                  </td>
                  <td>
                    <?php
                    echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_SARE_WLACZONY']['1'], $parametr['INTEGRACJA_SARE_WLACZONY']['0'], 'integracja_sare_wlaczony', $parametr['INTEGRACJA_SARE_WLACZONY']['2'], '', $parametr['INTEGRACJA_SARE_WLACZONY']['3'] );
                    ?>
                  </td>
                </tr>

                <tr class="SledzeniePozycja">
                  <td>
                    <label class="required" for="integracja_sare_uid">UID:</label>
                  </td>
                  <td>
                    <?php
                    echo '<input type="text" id="integracja_sare_uid" name="integracja_sare_uid" value="'.$parametr['INTEGRACJA_SARE_UID']['0'].'" size="53" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_SARE_UID']['2'].'</b></em>';
                    ?>
                  </td>
                </tr>

                <tr class="SledzeniePozycja">
                  <td>
                    <label class="required" for="integracja_sare_api_key">Klucz API:</label>
                  </td>
                  <td>
                    <?php
                    echo '<input type="text" id="integracja_sare_api_key" name="integracja_sare_api_key" value="'.$parametr['INTEGRACJA_SARE_API_KEY']['0'].'" size="53" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_SARE_API_KEY']['2'].'</b></em>';
                    ?>
                  </td>
                </tr>

                <tr class="SledzeniePozycja">
                  <td>
                    <label>Dodać do nowych grup, nie usuwać z grup aktualnie przypisanych:</label>
                  </td>
                  <td>
                    <?php
                    echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_SARE_ONLY_ADD_TO_GROUPS']['1'], $parametr['INTEGRACJA_SARE_ONLY_ADD_TO_GROUPS']['0'], 'integracja_sare_only_add_to_groups', $parametr['INTEGRACJA_SARE_ONLY_ADD_TO_GROUPS']['2'], '', $parametr['INTEGRACJA_SARE_ONLY_ADD_TO_GROUPS']['3'] );
                    ?>
                  </td>
                </tr>

                <tr class="SledzeniePozycja">
                  <td>
                    <label>Czy nadpisać wysłanymi danymi w przypadku duplikatu adresu email:</label>
                  </td>
                  <td>
                    <?php
                    echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_SARE_UPDATE_ON_DUPLICATE']['1'], $parametr['INTEGRACJA_SARE_UPDATE_ON_DUPLICATE']['0'], 'integracja_sare_update_on_duplicate', $parametr['INTEGRACJA_SARE_UPDATE_ON_DUPLICATE']['2'], '', $parametr['INTEGRACJA_SARE_UPDATE_ON_DUPLICATE']['3'] );
                    ?>
                  </td>
                </tr>

                <tr class="SledzeniePozycja">
                  <td>
                    <label>Czy nadpisać aktualizować status w przypadku duplikatu adresu email:</label>
                  </td>
                  <td>
                    <?php
                    echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_SARE_UPDATE_STATUS_ON_DUPLICATE']['1'], $parametr['INTEGRACJA_SARE_UPDATE_STATUS_ON_DUPLICATE']['0'], 'integracja_sare_update_status_on_duplicate', $parametr['INTEGRACJA_SARE_UPDATE_STATUS_ON_DUPLICATE']['2'], '', $parametr['INTEGRACJA_SARE_UPDATE_STATUS_ON_DUPLICATE']['3'] );
                    ?>
                  </td>
                </tr>


                <tr class="SledzeniePozycja">
                  <td>
                    <label for="integracja_sare_group_klienci">Identyfikator grupy w systemie SARE dla klientów sklepu:</label>
                  </td>
                  <td>
                    <?php
                    echo '<input type="text" id="integracja_sare_group_klienci" name="integracja_sare_group_klienci" value="'.$parametr['INTEGRACJA_SARE_GROUP_KLIENCI']['0'].'" size="30" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_SARE_GROUP_KLIENCI']['2'].'</b></em>';
                    ?>
                  </td>
                </tr>

                <tr class="SledzeniePozycja">
                  <td>
                    <label for="integracja_sare_group_subskrybenci">Identyfikator grupy w systemie SARE dla subskrybentów:</label>
                  </td>
                  <td>
                    <?php
                    echo '<input type="text" id="integracja_sare_group_subskrybenci" name="integracja_sare_group_subskrybenci" value="'.$parametr['INTEGRACJA_SARE_GROUP_SUBSKRYBENCI']['0'].'" size="30" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_SARE_GROUP_SUBSKRYBENCI']['2'].'</b></em>';
                    ?>
                  </td>
                </tr>

                <tr>
                  <td colspan="2">
                    <div class="przyciski_dolne">
                      <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'sare' ? $wynik : '' ); ?>
                    </div>
                  </td>
                </tr>
                
              </table>
              
            </div>

            </form>
            
          </div>  

          <div class="Sledzenie SledzenieUkryte">
          
            <form action="integracje/konfiguracja_sledzenie.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="salesforceForm" class="cmxform"> 
            
            <div>
              <input type="hidden" name="akcja" value="zapisz" />
              <input type="hidden" name="system" value="salesforce" />
            </div>
            
            <div class="ObramowanieForm">
            
              <table>
              
                <tr class="DivNaglowek">
                  <td colspan="2">SalesForce - marketing automation</td>
                </tr>
                
                <tr><td colspan="2" class="SledzenieOpis">
                  <div>Zapewnij klientom najlepszą obsługę za pomocą jednego narzędzia CRM dla sprzedaży, obsługi klienta, marketingu, handlu i IT</div>
                  <img src="obrazki/logo/salesforce.png" alt="" />
                </td></tr>
                
                <tr class="SledzeniePozycja">
                  <td>
                    <label>Włącz integrację SalesForce:</label>
                  </td>
                  <td>
                    <?php
                    echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_SALESFORCE_WLACZONY']['1'], $parametr['INTEGRACJA_SALESFORCE_WLACZONY']['0'], 'integracja_salesforce_wlaczony', $parametr['INTEGRACJA_SALESFORCE_WLACZONY']['2'], '', $parametr['INTEGRACJA_SALESFORCE_WLACZONY']['3'] );
                    ?>
                  </td>
                </tr>

                <tr class="SledzeniePozycja">
                  <td>
                    <label class="required" for="integracja_salesforce_client_id">Client ID:</label>
                  </td>
                  <td>
                    <?php
                    echo '<input type="text" id="integracja_salesforce_client_id" name="integracja_salesforce_client_id" value="'.$parametr['INTEGRACJA_SALESFORCE_CLIENT_ID']['0'].'" size="53" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_SALESFORCE_CLIENT_ID']['2'].'</b></em>';
                    ?>
                  </td>
                </tr>

                <tr class="SledzeniePozycja">
                  <td>
                    <label class="required" for="integracja_salesforce_client_secret">Client Secret:</label>
                  </td>
                  <td>
                    <?php
                    echo '<input type="text" id="integracja_salesforce_client_secret" name="integracja_salesforce_client_secret" value="'.$parametr['INTEGRACJA_SALESFORCE_CLIENT_SECRET']['0'].'" size="53" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_SALESFORCE_CLIENT_SECRET']['2'].'</b></em>';
                    ?>
                  </td>
                </tr>

                <tr class="SledzeniePozycja">
                  <td>
                    <label class="required" for="integracja_salesforce_subscription">Identyfikator subskrypcji w systemie SalesForce:</label>
                  </td>
                  <td>
                    <?php
                    echo '<input type="text" id="integracja_salesforce_subscription" name="integracja_salesforce_subscription" value="'.$parametr['INTEGRACJA_SALESFORCE_SUBSCRIPTION']['0'].'" size="30" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_SALESFORCE_SUBSCRIPTION']['2'].'</b></em>';
                    ?>
                  </td>
                </tr>

                <tr class="SledzeniePozycja">
                  <td>
                    <label class="required" for="integracja_salesforce_source">Źródło subskrypcji:</label>
                  </td>
                  <td>
                    <?php
                    echo '<input type="text" id="integracja_salesforce_source" name="integracja_salesforce_source" value="'.$parametr['INTEGRACJA_SALESFORCE_SOURCE']['0'].'" size="30" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_SALESFORCE_SOURCE']['2'].'</b></em>';
                    ?>
                  </td>
                </tr>

                <tr class="SledzeniePozycja">
                  <td>
                    <label class="required" for="integracja_salesforce_url">Adres punktu API W SalesForce:</label>
                  </td>
                  <td>
                    <?php
                    echo '<input type="text" id="integracja_salesforce_url" name="integracja_salesforce_url" value="'.$parametr['INTEGRACJA_SALESFORCE_URL']['0'].'" size="30" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_SALESFORCE_URL']['2'].'</b></em>';
                    ?>
                  </td>
                </tr>

                <tr>
                  <td colspan="2">
                    <div class="przyciski_dolne">
                      <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'salesforce' ? $wynik : '' ); ?>
                    </div>
                  </td>
                </tr>
                
              </table>
              
            </div>

            </form>
            
          </div>  

          <div class="Sledzenie SledzenieUkryte">
          
            <form action="integracje/konfiguracja_sledzenie.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="nokautTrackerForm" class="cmxform"> 
            
            <div>
              <input type="hidden" name="akcja" value="zapisz" />
              <input type="hidden" name="system" value="nokautTracker" />
            </div>
            
            <div class="ObramowanieForm">
            
              <table>
              
                <tr class="DivNaglowek">
                  <td colspan="2">NOKAUT.pl</td>
                </tr>
                
                <tr><td colspan="2" class="SledzenieOpis">
                  <div>Wtyczka konwersji NOKAUT.pl - która będzie wyświetlać w panelu sklepowym NOKAUT.pl dane odnośnie zakupu i konwersji.</div>
                  <img src="obrazki/logo/logo_nokaut.png" alt="" />
                </td></tr>
                
                <tr class="SledzeniePozycja">
                  <td>
                    <label>Włącz integrację NOKAUT.pl:</label>
                  </td>
                  <td>
                    <?php
                    echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_NOKAUT_SLEDZENIE_WLACZONY']['1'], $parametr['INTEGRACJA_NOKAUT_SLEDZENIE_WLACZONY']['0'], 'integracja_nokaut_sledzenie_wlaczony', $parametr['INTEGRACJA_NOKAUT_SLEDZENIE_WLACZONY']['2'], '', $parametr['INTEGRACJA_NOKAUT_SLEDZENIE_WLACZONY']['3'] );
                    ?>
                  </td>
                </tr>
                
                <tr class="SledzeniePozycja">
                  <td>
                    <label class="required" for="integracja_nokaut_sledzenie_id">Identyfikator sklepu:</label>
                  </td>
                  <td>
                    <?php
                    echo '<input type="text" id="integracja_nokaut_sledzenie_id" name="integracja_nokaut_sledzenie_id" value="'.$parametr['INTEGRACJA_NOKAUT_SLEDZENIE_ID']['0'].'" size="53" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_NOKAUT_SLEDZENIE_ID']['2'].'</b></em>';
                    ?>
                  </td>
                </tr>

                <tr>
                  <td colspan="2">
                    <div class="przyciski_dolne">
                      <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'nokautTracker' ? $wynik : '' ); ?>
                    </div>
                  </td>
                </tr>
                
              </table>
              
            </div>

            </form>
            
          </div>          

          <div class="Sledzenie SledzenieUkryte">
          
            <form action="integracje/konfiguracja_sledzenie.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="AllaniForm" class="cmxform"> 
            
            <div>
              <input type="hidden" name="akcja" value="zapisz" />
              <input type="hidden" name="system" value="Allani" />
            </div>
            
            <div class="ObramowanieForm">
            
              <table>
              
                <tr class="DivNaglowek">
                  <td colspan="2">DOMODI i ALLANI - Jeden kod dla obu serwisów</td>
                </tr>
                
                <tr><td colspan="2" class="SledzenieOpis">
                  <div>Kod, który pozwala na precyzyjne monitorowanie i analizę prowadzonych przez sklep kampanii reklamowych w serwisach Allani i Domodi. Skrypt śledzi zachowanie użytkowników od momentu przejścia na stronę sklepu, aż do chwili dokonania przez niego zakupu. Wiedza na temat transakcji w połączeniu z danymi dotyczącymi wyświetlanej oferty sklepu na stronach Allani i Domodi pozwoli skutecznie zoptymalizować kampanię.</div>
                  <img src="obrazki/logo/logo_allani_domodi.png" alt="" />
                </td></tr>
                
                <tr class="SledzeniePozycja"><td colspan="2">
                  <div style="margin:5px;color:#3f5d6b"><b style="font-size:110%;color:#000">Kod trackingowy</b> <br /> Integracja Allani/Domodi ze stroną podziękowania za zamówienie (podsumowanie zamówienia). Na koncie w Allani/Domodi będzie wgląd w listę produktów, które zostały zamówione w sklepie.</div>
                </td></tr>                
                
                <tr class="SledzeniePozycja">
                  <td>
                    <label>Włącz kod trackingowy DOMODI i ALLANI:</label>
                  </td>
                  <td>
                    <?php
                    echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_ALLANI_SLEDZENIE_WLACZONY']['1'], $parametr['INTEGRACJA_ALLANI_SLEDZENIE_WLACZONY']['0'], 'integracja_allani_sledzenie_wlaczony', $parametr['INTEGRACJA_ALLANI_SLEDZENIE_WLACZONY']['2'], '', $parametr['INTEGRACJA_ALLANI_SLEDZENIE_WLACZONY']['3'] );
                    ?>
                  </td>
                </tr>
                
                <tr class="SledzeniePozycja"><td colspan="2">
                  <div style="margin:5px;color:#3f5d6b"><b style="font-size:110%;color:#000">DomodiPixel</b> <br /> Narzędzie DomodiPixel wspiera zarządzanie kampaniami produktowymi prowadzonymi na stronach Domodi i Allanii.</div>
                </td></tr>    
                
                <tr class="SledzeniePozycja">
                  <td>
                    <label>Włącz moduł piksel DomodiPixel:</label>
                  </td>
                  <td>
                    <?php
                    echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_DOMODI_PIXEL_WLACZONY']['1'], $parametr['INTEGRACJA_DOMODI_PIXEL_WLACZONY']['0'], 'integracja_domodi_pixel_wlaczony', $parametr['INTEGRACJA_DOMODI_PIXEL_WLACZONY']['2'], '', $parametr['INTEGRACJA_DOMODI_PIXEL_WLACZONY']['3'] );
                    ?>
                  </td>
                </tr>
                
                <tr class="SledzeniePozycja">
                  <td>
                    <label class="required" for="integracja_domodi_pixel_id">Klucz sklepu Domodi Shop Key:</label>
                  </td>
                  <td>
                    <?php
                    echo '<input type="text" id="integracja_domodi_pixel_id" name="integracja_domodi_pixel_id" value="'.$parametr['INTEGRACJA_DOMODI_PIXEL_ID']['0'].'" size="53" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_DOMODI_PIXEL_ID']['2'].'</b></em>';
                    ?>
                        <span class="maleInfo">
                            Pixel aktualnie obsługuje trzy standardowe zdarzenia: <br /><br />
                            Purchase - kiedy proces zakupowy został zakończony <br />
                            ViewContent - wyświetlenie strony produktu, kategorii <br />
                            AddToCart - dodanie produktu do koszyka (tylko przy włączonej opcji okna PopUp po dodaniu do koszyka) 
                        </span>                    
                  </td>
                </tr>                
                
                <tr>
                  <td colspan="2">
                    <div class="przyciski_dolne">
                      <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'Allani' ? $wynik : '' ); ?>
                    </div>
                  </td>
                </tr>
                
              </table>
              
            </div>

            </form>
            
          </div>          

          <div class="Sledzenie SledzenieUkryte">
          
            <form action="integracje/konfiguracja_sledzenie.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="WpForm" class="cmxform"> 
            
            <div>
              <input type="hidden" name="akcja" value="zapisz" />
              <input type="hidden" name="system" value="Wp" />
            </div>
            
            <div class="ObramowanieForm">
            
              <table>
              
                <tr class="DivNaglowek">
                  <td colspan="2">WP Pixel</td>
                </tr>
                
                <tr><td colspan="2" class="SledzenieOpis">
                  <div>Pixel WP jest wykorzystywany do zbierania zdarzeń użytkowników odwiedzających witrynę internetową. Na podstawie zebranych danych można zrealizować analizy skuteczności ścieżek konwersji oraz budowę lejka sprzedażowego. Zebrane dane dostępne są w narzędziach analitycznych WP.</div>
                  <img src="obrazki/logo/wp_ads.png" alt="" />
                </td></tr>                

                <tr class="SledzeniePozycja">
                  <td>
                    <label>Włącz moduł pixel WP:</label>
                  </td>
                  <td>
                    <?php
                    echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_WP_PIXEL_WLACZONY']['1'], $parametr['INTEGRACJA_WP_PIXEL_WLACZONY']['0'], 'integracja_wp_pixel_wlaczony', $parametr['INTEGRACJA_WP_PIXEL_WLACZONY']['2'], '', $parametr['INTEGRACJA_WP_PIXEL_WLACZONY']['3'] );
                    ?>
                  </td>
                </tr>
                
                <tr class="SledzeniePozycja">
                  <td>
                    <label class="required" for="integracja_domodi_pixel_id">Identyfikator klienta:</label>
                  </td>
                  <td>
                    <?php
                    echo '<input type="text" id="integracja_wp_pixel_id" name="integracja_wp_pixel_id" value="'.$parametr['INTEGRACJA_WP_PIXEL_ID']['0'].'" size="53" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_WP_PIXEL_ID']['2'].'</b></em>';
                    ?>
                        <span class="maleInfo">
                            Pixel aktualnie obsługuje trzy standardowe zdarzenia: <br /><br />
                            Purchase - kiedy proces zakupowy został zakończony <br />
                            ViewContent - wyświetlenie strony produktu, kategorii <br />
                            AddToCart - dodanie produktu do koszyka (tylko przy włączonej opcji okna PopUp po dodaniu do koszyka) 
                        </span>                    
                  </td>
                </tr>                
                
                <tr>
                  <td colspan="2">
                    <div class="przyciski_dolne">
                      <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'Wp' ? $wynik : '' ); ?>
                    </div>
                  </td>
                </tr>
                
              </table>
              
            </div>

            </form>
            
          </div>          

          <div class="Sledzenie SledzenieUkryte">
          
            <form action="integracje/konfiguracja_sledzenie.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="pinterestForm" class="cmxform"> 
            
            <div>
              <input type="hidden" name="akcja" value="zapisz" />
              <input type="hidden" name="system" value="pinterest" />
            </div>
            
            <div class="ObramowanieForm">
            
              <table>
              
                <tr class="DivNaglowek">
                  <td colspan="2">Pinterest TAG</td>
                </tr>
                
                <tr><td colspan="2" class="SledzenieOpis">
                  <div>Tag Pinteresta to fragment kodu, który dodaje się do witryny, aby umożliwić Pinterestowi śledzenie osób odwiedzających witrynę oraz działań podejmowanych przez nich w witrynie po wyświetleniu reklamy na Pintereście. Dzięki temu możesz zmierzyć skuteczność reklam na Pintereście i zrozumieć podejmowane przez użytkowników w witrynie działania, zwane również konwersjami, po wyświetleniu reklamy lub po zareagowaniu na nią.</div>
                  <img src="obrazki/logo/logo_pinterest.png" alt="" />
                </td></tr>
                
                <tr class="SledzeniePozycja">
                  <td>
                    <label>Włącz moduł piksel Pinterest TAG:</label>
                  </td>
                  <td>
                    <?php
                    echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_PINTEREST_TAG_WLACZONY']['1'], $parametr['INTEGRACJA_PINTEREST_TAG_WLACZONY']['0'], 'integracja_pinterest_tag_wlaczony', $parametr['INTEGRACJA_PINTEREST_TAG_WLACZONY']['2'], '', $parametr['INTEGRACJA_PINTEREST_TAG_WLACZONY']['3'] );
                    ?>
                  </td>
                </tr>
                
                <tr class="SledzeniePozycja">
                  <td>
                    <label class="required" for="integracja_pinterest_tag_id">Id TAG:</label>
                  </td>
                  <td>
                    <?php
                    echo '<input type="text" id="integracja_pinterest_tag_id" name="integracja_pinterest_tag_id" value="'.$parametr['INTEGRACJA_PINTEREST_TAG_ID']['0'].'" size="53" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_PINTEREST_TAG_ID']['2'].'</b></em>';
                    ?>
                    <span class="maleInfo">
                        Obsługiwane zdarzenia: <br /><br />
                        
                        Checkout - strona podsumowania zamówienia (z przekazaniem wartości zamówienia) <br />
                        AddToCart - dodanie produktu do koszyka <br />
                        PageVisit - strona karty produktu <br />
                        Lead - zapisanie do newslettera <br />
                        Rejestracja - strona rejestracji klienta <br />
                        Wyszukaj - wyniki wyszukiwania <br />
                        ViewCategory - strona listingu produktów kategorii czy producenta
                    </span>                    
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
        
          <div class="Sledzenie SledzenieUkryte">
          
            <form action="integracje/konfiguracja_sledzenie.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="klaviyoForm" class="cmxform"> 
            
            <div>
              <input type="hidden" name="akcja" value="zapisz" />
              <input type="hidden" name="system" value="klaviyo" />
            </div>
            
            <div class="ObramowanieForm">
            
              <table>
              
                <tr class="DivNaglowek">
                  <td colspan="2">Klaviyo</td>
                </tr>
                
                <tr><td colspan="2" class="SledzenieOpis">
                  <div>Wtyczka KLAVIYO - platforma automatyzacji do marketingu e-mailowego i SMS-ów z funkcjami AI dla szybszego, wydajnego wzrostu. Zmień dane swoich klientów w hiperpersonalizowane wiadomości.</div>
                  <img src="obrazki/logo/klaviyo.svg" alt="" style="width:120px; height:auto;"/>
                </td></tr>
                
                <tr class="SledzeniePozycja">
                  <td>
                    <label>Włącz integrację KLAVIYO:</label>
                  </td>
                  <td>
                    <?php
                    echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_KLAVIYO_WLACZONY']['1'], $parametr['INTEGRACJA_KLAVIYO_WLACZONY']['0'], 'integracja_klaviyo_wlaczony', $parametr['INTEGRACJA_KLAVIYO_WLACZONY']['2'], '', $parametr['INTEGRACJA_KLAVIYO_WLACZONY']['3'] );
                    ?>
                  </td>
                </tr>
                
                <tr class="SledzeniePozycja">
                  <td>
                    <label class="required" for="integracja_klaviyo_public_key">Publiczny klucz API:</label>
                  </td>
                  <td>
                    <?php
                    echo '<input type="text" id="integracja_klaviyo_public_key" name="integracja_klaviyo_public_key" value="'.$parametr['INTEGRACJA_KLAVIYO_PUBLIC_KEY']['0'].'" size="53" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_KLAVIYO_PUBLIC_KEY']['2'].'</b></em>';
                    ?>
                  </td>
                </tr>

                <tr class="SledzeniePozycja">
                  <td>
                    <label class="required" for="integracja_klaviyo_private_key">Prywatny klucz API:</label>
                  </td>
                  <td>
                    <?php
                    echo '<input type="text" id="integracja_klaviyo_private_key" name="integracja_klaviyo_private_key" value="'.$parametr['INTEGRACJA_KLAVIYO_PRIVATE_KEY']['0'].'" size="53" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_KLAVIYO_PRIVATE_KEY']['2'].'</b></em>';
                    ?>

                        <span class="maleInfo">
                            Integracja obsługuje standardowe zdarzenia: <br /><br />
                            Active on Site - strona na którą wszedł klient <br />
                            Viewed Product - wyświetlenie strony produktu, kategorii <br />
                            Added to Cart - dodanie produktu do koszyka <br />
                            Started Checkout - rozpoczęcie procesu zakupu <br />
                            Placed Order - złożenie zamówienia <br />
                            Ordered Product - zamówione produkty <br />
                        </span>                    

                  </td>
                </tr>

                <tr>
                  <td colspan="2">
                    <div class="przyciski_dolne">
                      <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'klaviyo' ? $wynik : '' ); ?>
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
