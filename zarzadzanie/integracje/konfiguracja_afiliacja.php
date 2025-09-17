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
      foreach ( $_POST as $key => $value ) {
        
          if ( $key != 'akcja' ) {
               //
               if ( is_array($value) && $key == 'integracja_podziel_sie_portale' ) {
                    //
                    $pola = array(
                            array('value',implode(',',(array)$value))
                    );
                    //
               } else if ( is_array($value) && $key == 'integracja_podziel_sie_strony' ) {
                    //
                    $pola = array(
                            array('value',implode(',',(array)$value))
                    );
                    //                    
               } else if ( is_array($value) && $key == 'integracja_zaufanepl_status_zamowien' ) {
                    //
                    $pola = array(
                            array('value',implode(',',(array)$value))
                    );
                    //                    
               } else if ( $key == 'integracja_trustmate_widget_muskart_id' ) {
                    //
                    if ( isset($_POST['integracja_trustmate_widget_muskart']) ) {
                        $DoZapisania = '1|'.$_POST['integracja_trustmate_widget_muskart_id'];
                    } else {
                        $DoZapisania = '0|'.$_POST['integracja_trustmate_widget_muskart_id'];
                    }
                    $pola = array(
                            array('value',$DoZapisania)
                    );
                    unset($DoZapisania);
                    //                    
               } else if ( $key == 'integracja_trustmate_widget_lemur_id' ) {
                    //
                    if ( isset($_POST['integracja_trustmate_widget_lemur']) ) {
                        $DoZapisania = '1|'.$_POST['integracja_trustmate_widget_lemur_id'];
                    } else {
                        $DoZapisania = '0|'.$_POST['integracja_trustmate_widget_lemur_id'];
                    }
                    $pola = array(
                            array('value',$DoZapisania)
                    );
                    unset($DoZapisania);
                    //                    
               } else if ( $key == 'integracja_trustmate_widget_dodo_id' ) {
                    //
                    if ( isset($_POST['integracja_trustmate_widget_dodo']) ) {
                        $DoZapisania = '1|'.$_POST['integracja_trustmate_widget_dodo_id'];
                    } else {
                        $DoZapisania = '0|'.$_POST['integracja_trustmate_widget_dodo_id'];
                    }
                    $pola = array(
                            array('value',$DoZapisania)
                    );
                    unset($DoZapisania);
                    //                    
               } else if ( $key == 'integracja_trustmate_widget_badger_id' ) {
                    //
                    if ( isset($_POST['integracja_trustmate_widget_badger']) ) {
                        $DoZapisania = '1|'.$_POST['integracja_trustmate_widget_badger_id'];
                    } else {
                        $DoZapisania = '0|'.$_POST['integracja_trustmate_widget_badger_id'];
                    }
                    $pola = array(
                            array('value',$DoZapisania)
                    );
                    unset($DoZapisania);
                    //                    
               } else if ( $key == 'integracja_trustmate_widget_ferret_id' ) {
                    //
                    if ( isset($_POST['integracja_trustmate_widget_ferret']) ) {
                        $DoZapisania = '1|'.$_POST['integracja_trustmate_widget_ferret_id'];
                    } else {
                        $DoZapisania = '0|'.$_POST['integracja_trustmate_widget_ferret_id'];
                    }
                    $pola = array(
                            array('value',$DoZapisania)
                    );
                    unset($DoZapisania);
                    //                    
               } else if ( $key == 'integracja_trustmate_widget_hornet_id' ) {
                    //
                    if ( isset($_POST['integracja_trustmate_widget_hornet']) ) {
                        $DoZapisania = '1|'.$_POST['integracja_trustmate_widget_hornet_id'];
                    } else {
                        $DoZapisania = '0|'.$_POST['integracja_trustmate_widget_hornet_id'];
                    }
                    $pola = array(
                            array('value',$DoZapisania)
                    );
                    unset($DoZapisania);
                    //                    
               } else if ( $key == 'integracja_trustmate_widget_multihornet_id' ) {
                    //
                    if ( isset($_POST['integracja_trustmate_widget_multihornet']) ) {
                        $DoZapisania = '1|'.$_POST['integracja_trustmate_widget_multihornet_id'];
                    } else {
                        $DoZapisania = '0|'.$_POST['integracja_trustmate_widget_multihornet_id'];
                    }
                    $pola = array(
                            array('value',$DoZapisania)
                    );
                    unset($DoZapisania);
                    //                    
               } else {
                    //
                    $pola = array(
                            array('value',stripslashes((string)$value))
                    );
                    //
               }
               //
               $db->update_query('settings' , $pola, " code = '".strtoupper((string)$key)."'");	
               unset($pola);
               //
          }
        
      }

      $wynik = '<div id="'.$system.'" class="maleSukces" style="margin-left:20px;margin-top:10px;">dane zostały zmienione</div>';

    }

    $zapytanie = "SELECT * FROM settings WHERE type = 'afiliacja' ORDER BY sort ";
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

    <div id="naglowek_cont">Konfiguracja parametrów systemów afiliacyjnych</div>
    <div id="cont">

      <div class="poleForm">
        <div class="naglowek">Edycja danych</div>
        
        <div class="SledzenieNaglowki">
        
            <div class="SledzenieOkno">
            
                <div class="SledzenieDiv" data-id="podzielForm">
                    <div class="Foto"><img src="obrazki/logo/logo_podziel_sie.png" alt="" /></div>
                    <span>Podziel się na <br /> karcie produktu / artykułach</span>
                </div>
              
            </div>
            
            <div class="SledzenieOkno">
            
                <div class="SledzenieDiv" data-id="googlelogowanieForm">
                    <div class="Foto"><img src="obrazki/logo/logowanie_google.jpg" alt="" /></div>
                    <span>Google - logowanie do sklepu <br /> poprzez konto Google</span>
                </div>
              
            </div>

            <div class="SledzenieOkno">
            
                <div class="SledzenieDiv" data-id="fblogowanieForm">
                    <div class="Foto"><img src="obrazki/logo/logo_lubie_to.png" alt="" /></div>
                    <span>Facebook - logowanie do sklepu <br /> poprzez konto Facebook</span>
                </div>
              
            </div>            

            <div class="SledzenieOkno">
            
                <div class="SledzenieDiv" data-id="facebookOpinieForm">
                    <div class="Foto"><img src="obrazki/logo/logo_komentarze.png" alt="" /></div>
                    <span>Facebook - komentarze (opinie) <br /> na karcie produktu</span>
                </div>
              
            </div>             

            <div class="SledzenieOkno">
            
                <div class="SledzenieDiv" data-id="webepartnersForm">
                    <div class="Foto"><img src="obrazki/logo/logo_webepartners.png" alt="" /></div>
                    <span>Program afiliacyjny <br /> WebePartners</span>
                </div>
              
            </div>
            
            <div class="SledzenieOkno">
            
                <div class="SledzenieDiv" data-id="freshmailForm">
                    <div class="Foto"><img src="obrazki/logo/logo_freshmail.png" alt="" /></div>
                    <span>FreshMail <br /> email marketing</span>
                </div>
              
            </div>   
            
            <div class="SledzenieOkno">
            
                <div class="SledzenieDiv" data-id="mailerliteForm">
                    <div class="Foto"><img src="obrazki/logo/logo_mailerlite.png" alt="" /></div>
                    <span>MailerLite <br /> email marketing</span>
                </div>
              
            </div>             
            
            <div class="SledzenieOkno">
            
                <div class="SledzenieDiv" data-id="ecomailForm">
                    <div class="Foto"><img src="obrazki/logo/logo_ecomail.png" alt="" /></div>
                    <span>Ecomail <br /> email marketing</span>
                </div>
              
            </div>             
            
            <div class="SledzenieOkno">
            
                <div class="SledzenieDiv" data-id="mailjetForm">
                    <div class="Foto"><img src="obrazki/logo/logo_mailjet.png" alt="" /></div>
                    <span>Mailjet <br /> email marketing</span>
                </div>
              
            </div>             

            <div class="SledzenieOkno">
            
                <div class="SledzenieDiv" data-id="getallForm">
                    <div class="Foto"><img src="obrazki/logo/logo_getall.png" alt="" /></div>
                    <span>GetAll <br /> email marketing</span>
                </div>
              
            </div>   

            <div class="SledzenieOkno">
            
                <div class="SledzenieDiv" data-id="trustedshopsForm">
                    <div class="Foto"><img src="obrazki/logo/trustedshops.png" alt="" /></div>
                    <span>Trusted Shops - znak jakości <br /> dla sklepów internetowych</span>
                </div>
              
            </div>               

            <div class="SledzenieOkno">
            
                <div class="SledzenieDiv" data-id="trustistoForm">
                    <div class="Foto"><img src="obrazki/logo/logo_trustisto.png" alt="" /></div>
                    <span>Marketing Automation<br />Twój sklep na autopilocie</span>
                </div>
              
            </div>  

            <div class="SledzenieOkno">
            
                <div class="SledzenieDiv" data-id="shopeneoForm">
                    <div class="Foto"><img src="obrazki/logo/logo_shopeneo.png" alt="" /></div>
                    <span>Program afiliacyjny <br /> shopeneo.network</span>
                </div>
              
            </div>                

            <div class="SledzenieOkno">
            
                <div class="SledzenieDiv" data-id="zaufaneplForm">
                    <div class="Foto"><img src="obrazki/logo/logo_zaufanepl.png" alt="" /></div>
                    <span>Program opinii <br /> Zaufane.pl</span>
                </div>
              
            </div>                

            <div class="SledzenieOkno">
            
                <div class="SledzenieDiv" data-id="trustpilotForm">
                    <div class="Foto"><img src="obrazki/logo/logo_trustpilot.png" alt="" /></div>
                    <span>Program recenzji <br /> TrustPilot</span>
                </div>
              
            </div>                

            <div class="SledzenieOkno">
            
                <div class="SledzenieDiv" data-id="callback24Form">
                    <div class="Foto"><img src="obrazki/logo/logo_callback24.png" alt="" /></div>
                    <span>Callback24<br />rozwiązanie lead generation</span>
                </div>
              
            </div>
            
            <div class="SledzenieOkno">
            
                <div class="SledzenieDiv" data-id="trustmateForm">
                    <div class="Foto"><img src="obrazki/logo/logo-trustmate.svg" alt="" /></div>
                    <span>Trustmate.io<br />Opinie klientów w atrakcyjnych widgetach</span>
                </div>
              
            </div>                

        
        </div>
          
        <div class="cl"></div>         

        <div class="pozycja_edytowana">  

          <script>
          $(document).ready(function() {
            
            $('#integracja_zaufanepl_token').on("keyup",function() {
               $('#SciezkaCron').html( $('#SciezkaCron').attr('data-link') + '?token=' + $(this).val() );
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
            
            $("#webepartnersForm").validate({
              rules: {
                integracja_webepartners_mid: {required: function() {var wynik = true; if ( $("input[name='integracja_webepartners_zamowienia_wlaczony']:checked", "#webepartnersForm").val() == "nie" ) { wynik = false; } return wynik; }}
              }
            });
            
            $("#fblogowanieForm").validate({
              rules: {
                integracja_fb_logowanie_identyfikator: {required: function() {var wynik = true; if ( $("input[name='integracja_fb_logowanie_wlaczony']:checked", "#fblogowanieForm").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_fb_logowanie_secret: {required: function() {var wynik = true; if ( $("input[name='integracja_fb_logowanie_wlaczony']:checked", "#fblogowanieForm").val() == "nie" ) { wynik = false; } return wynik; }}
              }
            });            
            
            $("#googlelogowanieForm").validate({
              rules: {
                integracja_google_logowanie_identyfikator: {required: function() {var wynik = true; if ( $("input[name='integracja_google_logowanie_wlaczony']:checked", "#googlelogowanieForm").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_google_logowanie_klucz: {required: function() {var wynik = true; if ( $("input[name='integracja_google_logowanie_wlaczony']:checked", "#googlelogowanieForm").val() == "nie" ) { wynik = false; } return wynik; }}
              }
            });            
                        
            $("#freshmailForm").validate({
              rules: {
                integracja_freshmail_key: {required: function() {var wynik = true; if ( $("input[name='integracja_freshmail_wlaczony']:checked", "#freshmailForm").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_freshmail_sekret: {required: function() {var wynik = true; if ( $("input[name='integracja_freshmail_wlaczony']:checked", "#freshmailForm").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_domyslna_lista: {required: function() {var wynik = true; if ( $("input[name='integracja_freshmail_wlaczony']:checked", "#freshmailForm").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_freshmail_produkty_prefix: {required: function() {var wynik = true; if ( $("input[name='integracja_freshmail_wlaczony_produkty']:checked", "#freshmailForm").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_freshmail_kupujacy_prefix: {required: function() {var wynik = true; if ( $("input[name='integracja_freshmail_wlaczony_kupujacy']:checked", "#freshmailForm").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_freshmail_rejestracja_prefix: {required: function() {var wynik = true; if ( $("input[name='integracja_freshmail_wlaczony_rejestracja']:checked", "#freshmailForm").val() == "nie" ) { wynik = false; } return wynik; }}
              }
            });  
            
            $("#mailerliteForm").validate({
              rules: {
                integracja_mailerlite_key: {required: function() {var wynik = true; if ( $("input[name='integracja_mailerlite_wlaczony']:checked", "#mailerliteForm").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_mailerlite_domyslna_lista: {required: function() {var wynik = true; if ( $("input[name='integracja_mailerlite_wlaczony']:checked", "#mailerliteForm").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_mailerlite_produkty_prefix: {required: function() {var wynik = true; if ( $("input[name='integracja_mailerlite_wlaczony_produkty']:checked", "#mailerliteForm").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_mailerlite_kupujacy_prefix: {required: function() {var wynik = true; if ( $("input[name='integracja_mailerlite_wlaczony_kupujacy']:checked", "#mailerliteForm").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_mailerlite_rejestracja_prefix: {required: function() {var wynik = true; if ( $("input[name='integracja_mailerlite_wlaczony_rejestracja']:checked", "#mailerliteForm").val() == "nie" ) { wynik = false; } return wynik; }}
              }
            });              
            
            $("#ecomailForm").validate({
              rules: {
                integracja_ecomail_key: {required: function() {var wynik = true; if ( $("input[name='integracja_ecomail_wlaczony']:checked", "#ecomailForm").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_ecomail_domyslna_lista: {required: function() {var wynik = true; if ( $("input[name='integracja_ecomail_wlaczony']:checked", "#ecomailForm").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_ecomail_produkty_prefix: {required: function() {var wynik = true; if ( $("input[name='integracja_ecomail_wlaczony_produkty']:checked", "#ecomailForm").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_ecomail_kupujacy_prefix: {required: function() {var wynik = true; if ( $("input[name='integracja_ecomail_wlaczony_kupujacy']:checked", "#ecomailForm").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_ecomail_rejestracja_prefix: {required: function() {var wynik = true; if ( $("input[name='integracja_ecomail_wlaczony_rejestracja']:checked", "#ecomailForm").val() == "nie" ) { wynik = false; } return wynik; }}
              }
            });

            $("#mailjetForm").validate({
              rules: {
                integracja_mailjet_key: {required: function() {var wynik = true; if ( $("input[name='integracja_mailjet_wlaczony']:checked", "#mailjetForm").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_mailjet_secret: {required: function() {var wynik = true; if ( $("input[name='integracja_mailjet_wlaczony']:checked", "#mailjetForm").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_domyslna_lista: {required: function() {var wynik = true; if ( $("input[name='integracja_mailjet_wlaczony']:checked", "#mailjetForm").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_mailjet_produkty_prefix: {required: function() {var wynik = true; if ( $("input[name='integracja_mailjet_wlaczony_produkty']:checked", "#mailjetForm").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_mailjet_kupujacy_prefix: {required: function() {var wynik = true; if ( $("input[name='integracja_mailjet_wlaczony_kupujacy']:checked", "#mailjetForm").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_mailjet_rejestracja_prefix: {required: function() {var wynik = true; if ( $("input[name='integracja_mailjet_wlaczony_rejestracja']:checked", "#mailjetForm").val() == "nie" ) { wynik = false; } return wynik; }}
              }
            });              
            
            $("#getallForm").validate({
              rules: {
                integracja_getall_apikey: {required: function() {var wynik = true; if ( $("input[name='integracja_getall_wlaczony']:checked", "#getallForm").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_getall_domyslna_lista: {required: function() {var wynik = true; if ( $("input[name='integracja_getall_wlaczony']:checked", "#getallForm").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_getall_produkty_prefix: {required: function() {var wynik = true; if ( $("input[name='integracja_getall_wlaczony_produkty']:checked", "#getallForm").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_getall_kupujacy_prefix: {required: function() {var wynik = true; if ( $("input[name='integracja_getall_wlaczony_kupujacy']:checked", "#getallForm").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_getall_rejestracja_prefix: {required: function() {var wynik = true; if ( $("input[name='integracja_getall_wlaczony_rejestracja']:checked", "#getallForm").val() == "nie" ) { wynik = false; } return wynik; }}
              }
            });             

            $("#trustedshopsForm").validate({
              rules: {
                integracja_trustedshops_partnerid: {required: function() {var wynik = true; if ( $("input[name='integracja_trustedshops_wlaczony']:checked", "#trustedshopsForm").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_trustedshops_przesuniecie: { 
                    range: [0, 250],
                    number: true
                }
              }
            });               

            $("#trustistoForm").validate({
              rules: {
                integracja_trustisto_partnerid: {required: function() {var wynik = true; if ( $("input[name='integracja_trustisto_wlaczony']:checked", "#trustistoForm").val() == "nie" ) { wynik = false; } return wynik; }}
              }
            });               

            $("#trustpilotForm").validate({
              rules: {
                integracja_trustpilot_key: {required: function() {var wynik = true; if ( $("input[name='integracja_trustpilot_wlaczony']:checked", "#trustistoForm").val() == "nie" ) { wynik = false; } return wynik; }}
              }
            });               

            $("#zaufaneplForm").validate({
              rules: {
                integracja_zaufanepl_token: {required: function() {var wynik = true; if ( $("input[name='integracja_zaufanepl_cron']:checked", "#zaufaneplForm").val() == "nie" ) { wynik = false; } return wynik; }},

                integracja_zaufanepl_ftphost: {required: function() {var wynik = true; if ( $("input[name='integracja_zaufanepl_ftp']:checked", "#zaufaneplForm").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_zaufanepl_ftplogin: {required: function() {var wynik = true; if ( $("input[name='integracja_zaufanepl_ftp']:checked", "#zaufaneplForm").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_zaufanepl_ftphaslo: {required: function() {var wynik = true; if ( $("input[name='integracja_zaufanepl_ftp']:checked", "#zaufaneplForm").val() == "nie" ) { wynik = false; } return wynik; }}

              }
            });               

            $("#callback24Form").validate({
              rules: {
                integracja_callback24_nazwa: {required: function() {var wynik = true; if ( $("input[name='pole_integracja_callback24_wlaczony']:checked", "#callback24Form").val() == "nie" ) { wynik = false; } return wynik; }}
              }
            });
            
            $("#trustmateForm").validate({
              rules: {
                integracja_trustmate_id: {required: function() {var wynik = true; if ( $("input[name='pole_integracja_trustmate_wlaczony']:checked", "#trustmateForm").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_trustmate_widget_muskart_id: {required: "#integracja_trustmate_widget_muskart:checked" },
                integracja_trustmate_widget_lemur_id: {required: "#integracja_trustmate_widget_lemur:checked" },
                integracja_trustmate_widget_dodo_id: {required: "#integracja_trustmate_widget_dodo:checked" },
                integracja_trustmate_widget_badger_id: {required: "#integracja_trustmate_widget_badger:checked" },
                integracja_trustmate_widget_ferret_id: {required: "#integracja_trustmate_widget_ferret:checked" },
                integracja_trustmate_widget_hornet_id: {required: "#integracja_trustmate_widget_hornet:checked" },
                integracja_trustmate_widget_multihornet_id: {required: "#integracja_trustmate_widget_multihornet:checked" },
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

          <!-- Portale spolecznosciowe na karcie produktu -->
          <div class="Sledzenie SledzenieUkryte">
          
            <form action="integracje/konfiguracja_afiliacja.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="podzielForm" class="cmxform">
            
              <div>
                <input type="hidden" name="akcja" value="zapisz" />
                <input type="hidden" name="system" value="podziel" />
              </div>
              
              <div class="ObramowanieForm">
              
                  <table>
                  
                    <tr class="DivNaglowek">
                      <td colspan="2">Podziel się na karcie produktu i artykułach - ikonki z odnośnikami do portali społecznościowych</td>
                    </tr>
                    
                    <tr><td colspan="2" class="SledzenieOpis">
                      <div>Dzięki wtyczce umieścisz na swojej stronie internetowej odnośniki, prosto z której użytkownicy będą mogli podzielić się linkiem do produktu.</div>
                      <img src="obrazki/logo/logo_podziel_sie.png" alt="" />
                    </td></tr>                  
                  
                    <tr class="SledzeniePozycja">
                      <td>
                        <label>Włącz moduł "Podziel się":</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_PODZIEL_SIE_WLACZONY']['1'], $parametr['INTEGRACJA_PODZIEL_SIE_WLACZONY']['0'], 'integracja_podziel_sie_wlaczony', $parametr['INTEGRACJA_PODZIEL_SIE_WLACZONY']['2'], '', $parametr['INTEGRACJA_PODZIEL_SIE_WLACZONY']['3'] );
                        ?>
                      </td>
                    </tr>
                    
                    <tr class="SledzeniePozycja">
                        <td><label>Jakie portale mają być wyświetlane ?</label></td>
                        <td>
                            <?php                        
                            $f = 1;
                            foreach ( explode(',', (string)$parametr['INTEGRACJA_PODZIEL_SIE_PORTALE'][1]) as $tmp ) {
                                  //
                                  echo '<input type="checkbox" value="' . $tmp . '" name="integracja_podziel_sie_portale[]" id="integracja_podziel_sie_portale_' . $f . '" ' . ((in_array((string)$tmp, explode(',', (string)$parametr['INTEGRACJA_PODZIEL_SIE_PORTALE'][0]))) ? 'checked="checked"' : '') . ' /><label class="OpisFor" for="integracja_podziel_sie_portale_' . $f . '">' . $tmp . '</label><br />';
                                  $f++;
                                  //
                            }
                            ?>
                        </td>
                    </tr>                  
                    
                    <tr class="SledzeniePozycja">
                        <td><label>Na jakich stronach ma być wyświetlany moduł ?</label></td>
                        <td>
                            <?php                        
                            $f = 1;
                            foreach ( explode(',', (string)$parametr['INTEGRACJA_PODZIEL_SIE_STRONY'][1]) as $tmp ) {
                                  //
                                  echo '<input type="checkbox" value="' . $tmp . '" name="integracja_podziel_sie_strony[]" id="integracja_podziel_sie_strony_' . $f . '" ' . ((in_array((string)$tmp, explode(',', (string)$parametr['INTEGRACJA_PODZIEL_SIE_STRONY'][0]))) ? 'checked="checked"' : '') . ' /><label class="OpisFor" for="integracja_podziel_sie_strony_' . $f . '">' . $tmp . '</label><br />';
                                  $f++;
                                  //
                            }
                            ?>
                        </td>
                    </tr>                      

                    <tr>
                      <td colspan="2">
                        <div class="przyciski_dolne">
                          <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'podziel' ? $wynik : '' ); ?>
                        </div>
                      </td>
                    </tr>
                  </table>

              </div>
            </form>
            
          </div>          
          
          
          <!-- Google plus - logowanie -->
          <div class="Sledzenie SledzenieUkryte">
          
            <form action="integracje/konfiguracja_afiliacja.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="googlelogowanieForm" class="cmxform"> 
            
              <div>
                <input type="hidden" name="akcja" value="zapisz" />
                <input type="hidden" name="system" value="googlelogowanie" />
              </div>
              
              <div class="ObramowanieForm">
              
                  <table>
                  
                    <tr class="DivNaglowek">
                      <td colspan="2">Google - logowanie do sklepu poprzez konto Google</td>
                    </tr>
                    
                    <tr><td colspan="2" class="SledzenieOpis">
                      <div>Dzięki tej funkcji klienci będą mogli połączyć swoje konto w sklepie z kontem na Google i logować się do sklepu bez podawania loginu i hasła do sklepu (wystarczy samo zalogowanie się do Google).</div>
                      <img src="obrazki/logo/logowanie_google.jpg" alt="" />
                    </td></tr>                   
                  
                    <tr class="SledzeniePozycja">
                      <td>
                        <label>Włącz logowanie poprzez Google:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_GOOGLE_LOGOWANIE_WLACZONY']['1'], $parametr['INTEGRACJA_GOOGLE_LOGOWANIE_WLACZONY']['0'], 'integracja_google_logowanie_wlaczony', $parametr['INTEGRACJA_GOOGLE_LOGOWANIE_WLACZONY']['2'], '', $parametr['INTEGRACJA_GOOGLE_LOGOWANIE_WLACZONY']['3'] );
                        ?>
                      </td>
                    </tr>
                    
                    <tr class="SledzeniePozycja">
                      <td>
                        <label class="required" for="integracja_google_logowanie_identyfikator">Identyfikator klienta:</label>
                      </td>
                      <td>
                        <?php
                        echo '<input type="text" name="integracja_google_logowanie_identyfikator" id="integracja_google_logowanie_identyfikator" value="'.$parametr['INTEGRACJA_GOOGLE_LOGOWANIE_IDENTYFIKATOR']['0'].'" size="53" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_GOOGLE_LOGOWANIE_IDENTYFIKATOR']['2'].'</b></em>';
                        ?>
                      </td>
                    </tr>
                    
                    <tr class="SledzeniePozycja">
                      <td>
                        <label class="required" for="integracja_google_logowanie_klucz">Tajny klucz klienta:</label>
                      </td>
                      <td>
                        <?php
                        echo '<input type="text" name="integracja_google_logowanie_klucz" id="integracja_google_logowanie_klucz" value="'.$parametr['INTEGRACJA_GOOGLE_LOGOWANIE_KLUCZ']['0'].'" size="53" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_GOOGLE_LOGOWANIE_KLUCZ']['2'].'</b></em>';
                        ?>
                      </td>
                    </tr>                    

                    <tr>
                      <td colspan="2">
                        <div class="przyciski_dolne">
                          <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'googlelogowanie' ? $wynik : '' ); ?>
                        </div>
                      </td>
                    </tr>
                    
                  </table>

              </div>
            </form>
          </div>        
          
          
          <!-- Facebook - logowanie -->
          <div class="Sledzenie SledzenieUkryte">
          
            <form action="integracje/konfiguracja_afiliacja.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="fblogowanieForm" class="cmxform"> 
            
              <div>
                <input type="hidden" name="akcja" value="zapisz" />
                <input type="hidden" name="system" value="fblogowanie" />
              </div>
              
              <div class="ObramowanieForm">
              
                  <table>
                  
                    <tr class="DivNaglowek">
                      <td colspan="2">Facebook - logowanie do sklepu poprzez konto Facebook</td>
                    </tr>
                    
                    <tr><td colspan="2" class="SledzenieOpis">
                      <div>Dzięki tej funkcji klienci będą mogli połączyć swoje konto w sklepie z kontem na Facebook i logować się do sklepu bez podawania loginu i hasła do sklepu (wystarczy samo zalogowanie się do Facebook).</div>
                      <img src="obrazki/logo/logo_lubie_to.png" alt="" />
                    </td></tr>                   
                  
                    <tr class="SledzeniePozycja">
                      <td>
                        <label>Włącz logowanie poprzez Facebook:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_FB_LOGOWANIE_WLACZONY']['1'], $parametr['INTEGRACJA_FB_LOGOWANIE_WLACZONY']['0'], 'integracja_fb_logowanie_wlaczony', $parametr['INTEGRACJA_FB_LOGOWANIE_WLACZONY']['2'], '', $parametr['INTEGRACJA_FB_LOGOWANIE_WLACZONY']['3'] );
                        ?>
                      </td>
                    </tr>
                    
                    <tr class="SledzeniePozycja">
                      <td>
                        <label class="required" for="integracja_fb_logowanie_identyfikator">Identyfikator aplikacji Facebook:</label>
                      </td>
                      <td>
                        <?php
                        echo '<input type="text" name="integracja_fb_logowanie_identyfikator" id="integracja_fb_logowanie_identyfikator" value="'.$parametr['INTEGRACJA_FB_LOGOWANIE_IDENTYFIKATOR']['0'].'" size="53" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_FB_LOGOWANIE_IDENTYFIKATOR']['2'].'</b></em>';
                        ?>
                      </td>
                    </tr>
                    
                    <tr class="SledzeniePozycja">
                      <td>
                        <label class="required" for="integracja_fb_logowanie_secret">Numer App Secret:</label>
                      </td>
                      <td>
                        <?php
                        echo '<input type="text" name="integracja_fb_logowanie_secret" id="integracja_fb_logowanie_secret" value="'.$parametr['INTEGRACJA_FB_LOGOWANIE_SECRET']['0'].'" size="53" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_FB_LOGOWANIE_SECRET']['2'].'</b></em>';
                        ?>
                      </td>
                    </tr>                    
                    
                    <tr>
                      <td colspan="2">
                        <div class="przyciski_dolne">
                          <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'fblogowanie' ? $wynik : '' ); ?>
                        </div>
                      </td>
                    </tr>
                    
                  </table>

              </div>
            </form>
          </div>          


          <!-- recenzje FB na karcie produktu -->
          <div class="Sledzenie SledzenieUkryte">
          
            <form action="integracje/konfiguracja_afiliacja.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="facebookOpinieForm" class="cmxform">
            
              <div>
                <input type="hidden" name="akcja" value="zapisz" />
                <input type="hidden" name="system" value="facebookOpinie" />
              </div>
              
              <div class="ObramowanieForm">
              
                  <table>
                  
                    <tr class="DivNaglowek">
                      <td colspan="2">Facebook - komentarze (opinie) na karcie produktu</td>
                    </tr>
                    
                    <tr><td colspan="2" class="SledzenieOpis">
                      <div>Wtyczka Komentarze Facebook pozwala opiniować klientom za pomocą swojego profilu na Facebooku produkty oferowane w sklepie. <br /><br />
                      <span class="ostrzezenie">Wtyczka jest powiązana z recenzjami sklepu. Wtyczka jest wyświetlana w zakładce Recenzje na karcie produktu.
                      Aby wtyczka była aktywna muszą być w sklepie włączone recenzje produktu (menu Konfiguracja / Konfiguracja sklepu / Ustawienia produktów).</span>                      
                      <span class="maleInfo">Możliwość komentowania poprzez ten moduł dostępna jest tylko dla użytkowników sklepu, którzy są zalogowani w FB. Klient bez zalogowania nie widzi okna komentarzy. Jest to zwiazane z polityką prywatności FB.</span>
                      </div>                      
                      <img src="obrazki/logo/logo_komentarze.png" alt="" />
                    </td></tr>                  
                  
                    <tr class="SledzeniePozycja">
                      <td>
                        <label>Włącz Komentarze Facebook:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_FB_OPINIE_WLACZONY']['1'], $parametr['INTEGRACJA_FB_OPINIE_WLACZONY']['0'], 'integracja_fb_opinie_wlaczony', $parametr['INTEGRACJA_FB_OPINIE_WLACZONY']['2'], '', $parametr['INTEGRACJA_FB_OPINIE_WLACZONY']['3'] );
                        ?>
                      </td>
                    </tr>
                    
                    <tr class="SledzeniePozycja">
                      <td>
                        <label for="integracja_fb_opinie_ilosc_postow">Ilość wyświetlanych komentarzy:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_FB_OPINIE_ILOSC_POSTOW']['1'], $parametr['INTEGRACJA_FB_OPINIE_ILOSC_POSTOW']['0'], 'integracja_fb_opinie_ilosc_postow', $parametr['INTEGRACJA_FB_OPINIE_ILOSC_POSTOW']['2'], '', $parametr['INTEGRACJA_FB_OPINIE_ILOSC_POSTOW']['3'], '', '', 'id="integracja_fb_opinie_ilosc_postow"' );
                        ?>
                      </td>
                    </tr>                    

                    <tr>
                      <td colspan="2">
                        <div class="przyciski_dolne">
                          <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'facebookOpinie' ? $wynik : '' ); ?>
                        </div>
                      </td>
                    </tr>
                  </table>

              </div>
            </form>
            
          </div>          


          <!-- System afiliacyjny WebePartners -->
          <div class="Sledzenie SledzenieUkryte">
          
            <form action="integracje/konfiguracja_afiliacja.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="webepartnersForm" class="cmxform"> 
            
              <div>
                <input type="hidden" name="akcja" value="zapisz" />
                <input type="hidden" name="system" value="webepartners" />
              </div>
              
              <div class="ObramowanieForm">
              
                  <table>
                  
                    <tr class="DivNaglowek">
                      <td colspan="2">Program afiliacyjny WebePartners</td>
                    </tr>
                    
                    <tr><td colspan="2" class="SledzenieOpis">
                      <div>Sieć WebePartners specjalizuje się w profesjonalnej obsłudze programów partnerskich sklepów internetowych. Rozlicza kampanie marketingowe w efektywnościowym modelu współpracy Cost Per Sale. Poprzez sieć wydawców zwiększa sprzedaż w sklepach internetowych w zamian za prowizję od sprzedaży.</div>
                      <img src="obrazki/logo/logo_webepartners.png" alt="" />
                    </td></tr>                   
                  
                    <tr class="SledzeniePozycja">
                      <td>
                        <label>Śledzenie zamówień:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_WEBEPARTNERS_ZAMOWIENIA_WLACZONY']['1'], $parametr['INTEGRACJA_WEBEPARTNERS_ZAMOWIENIA_WLACZONY']['0'], 'integracja_webepartners_zamowienia_wlaczony', $parametr['INTEGRACJA_WEBEPARTNERS_ZAMOWIENIA_WLACZONY']['2'], '', $parametr['INTEGRACJA_WEBEPARTNERS_ZAMOWIENIA_WLACZONY']['3'] );
                        ?>
                      </td>
                    </tr>
                    
                    <tr class="SledzeniePozycja">
                      <td>
                        <label class="required" for="integracja_webepartners_mid">Identyfikator sprzedawcy (MID):</label>
                      </td>
                      <td>
                        <?php
                        echo '<input type="text" name="integracja_webepartners_mid" id="integracja_webepartners_mid" value="'.$parametr['INTEGRACJA_WEBEPARTNERS_MID']['0'].'" size="53" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_WEBEPARTNERS_MID']['2'].'</b></em>';
                        ?>
                      </td>
                    </tr>
                    
                    <tr>
                      <td colspan="2">
                        <div class="przyciski_dolne">
                          <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'webepartners' ? $wynik : '' ); ?>
                        </div>
                      </td>
                    </tr>
                    
                  </table>

              </div>
            </form>
          </div>
          
          
          <!-- Integracja z FreshMail -->
          <div class="Sledzenie SledzenieUkryte">
          
            <form action="integracje/konfiguracja_afiliacja.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="freshmailForm" class="cmxform"> 
            
              <div>
                <input type="hidden" name="akcja" value="zapisz" />
                <input type="hidden" name="system" value="freshmail" />
              </div>
              
              <div class="ObramowanieForm">
              
                  <table>
                  
                    <tr class="DivNaglowek">
                      <td colspan="2">FreshMail email marketing to intuicyjny program do wysyłania newsletterów, mailingów i autoresponderów.</td>
                    </tr>
                    
                    <tr><td colspan="2" class="SledzenieOpis">
                      <div>FreshMail to nowoczesne narzędzie do tworzenia oraz wysyłki newsletterów i mailingów do własnej bazy klientów. Pozwala w pełni zautomatyzować budowanie listy odbiorców oraz wysyłkę newsletterów. Wystarczy skorzystać z jeden z dziesiątek szablonów, uzupełnić go własną treścią i już można pozyskać nowych klientów na swoje produkty i usługi. Wysyłaj newslettery i informuj swoich klientów o nowościach oraz ofertach specjalnych.</div>                      
                      <img src="obrazki/logo/logo_freshmail.png" alt="" />
                      <span class="maleSukces" style="margin:0px 10px 15px 10px">Integracja sklepu z FreshMail pozwala na automatyzację przenoszenia adresów email ze sklepu do FreshMail. Klient zapisując się w sklepie do newslettera jest również automatycznie dodawany do listy odbiorców w systemie FreshMail. </span>
                    </td></tr>                   
                  
                    <tr class="SledzeniePozycja">
                      <td>
                        <label>Włącz integrację:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_FRESHMAIL_WLACZONY']['1'], $parametr['INTEGRACJA_FRESHMAIL_WLACZONY']['0'], 'integracja_freshmail_wlaczony', $parametr['INTEGRACJA_FRESHMAIL_WLACZONY']['2'], '', $parametr['INTEGRACJA_FRESHMAIL_WLACZONY']['3'] );
                        ?>
                      </td>
                    </tr>
                    
                    <tr class="SledzeniePozycja">
                      <td>
                        <label class="required" for="integracja_freshmail_key">Klucz API:</label>
                      </td>
                      <td>
                        <?php
                        echo '<input type="text" name="integracja_freshmail_key" id="integracja_freshmail_key" value="'.$parametr['INTEGRACJA_FRESHMAIL_KEY']['0'].'" size="53" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_FRESHMAIL_KEY']['2'].'</b></em>';
                        ?>
                      </td>
                    </tr>                    
                    
                    <tr class="SledzeniePozycja">
                      <td>
                        <label class="required" for="integracja_freshmail_sekret">Klucz API sekret:</label>
                      </td>
                      <td>
                        <?php
                        echo '<input type="text" name="integracja_freshmail_sekret" id="integracja_freshmail_sekret" value="'.$parametr['INTEGRACJA_FRESHMAIL_SEKRET']['0'].'" size="53" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_FRESHMAIL_SEKRET']['2'].'</b></em>';
                        ?>
                      </td>
                    </tr>  
                    
                    <tr>
                      <td colspan="2" class="NaglowekAfiliacja">
                        <strong>Automatyczna subskrypcja wszystkich klientów którzy wyrazili zgodę na otrzymywanie newslettera</strong>
                        <span class="maleInfo">
                            Funkcja ułatwiająca tworzenie list mailingowych klientów, którzy wyrazili zgodę na otrzymywanie newslettera. Po zapisaniu się klienta do newslettera (poprzez box, przy rejestracji czy module newslettera) jego adres email 
                            jest dodawany do domyślnej (zdefiniowanej poniżej) listy odbiorców. Jeżeli lista nie istnieje w systemie FreshMail - zostanie utworzona.
                        </span>
                      </td>
                    </tr>                     
                    
                    <tr class="SledzeniePozycja">
                      <td>
                        <label class="required" for="integracja_domyslna_lista">Domyślna lista odbiorców:</label>
                      </td>
                      <td>
                        <?php
                        echo '<input type="text" name="integracja_domyslna_lista" id="integracja_domyslna_lista" value="'.$parametr['INTEGRACJA_DOMYSLNA_LISTA']['0'].'" size="53" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_DOMYSLNA_LISTA']['2'].'</b></em>';
                        ?>
                      </td>
                    </tr>          

                    <tr>
                      <td colspan="2" class="NaglowekAfiliacja">
                        <strong>Dodatkowa lista odbiorców dla klientów przy rejestracji konta</strong>
                        <span class="maleInfo">
                            Funkcja ułatwiająca tworzenie list mailingowych klientów, którzy dokonali rejestracji konta w sklepie. Dzięki temu można wysłać maile tylko do klientów, którzy założyli w sklepie konto.
                            Po założeniu przez klienta konta jego adres email jest dodawany oprócz domyślnej listy odbiorców także do dodatkowej listy, której nazwa jest tworzona na podstawie ustalonej nazwy listy.
                            Jeżeli lista nie istnieje w systemie FreshMail - zostanie utworzona. Warunkiem zapisu adresu klienta do list jest wyrażenie przez klienta zgody na otrzymywanie newslettera.
                        </span>
                      </td>
                    </tr>    

                    <tr class="SledzeniePozycja">
                      <td>
                        <label>Włącz subskrypcję klientów:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_FRESHMAIL_WLACZONY_REJESTRACJA']['1'], $parametr['INTEGRACJA_FRESHMAIL_WLACZONY_REJESTRACJA']['0'], 'integracja_freshmail_wlaczony_rejestracja', $parametr['INTEGRACJA_FRESHMAIL_WLACZONY_REJESTRACJA']['2'], '', $parametr['INTEGRACJA_FRESHMAIL_WLACZONY_REJESTRACJA']['3'] );
                        ?>
                      </td>
                    </tr>      

                    <tr class="SledzeniePozycja">
                      <td>
                        <label class="required" for="integracja_freshmail_rejestracja_prefix">Nazwa listy odbiorców:</label>
                      </td>
                      <td>
                        <?php
                        echo '<input type="text" name="integracja_freshmail_rejestracja_prefix" id="integracja_freshmail_rejestracja_prefix" value="'.$parametr['INTEGRACJA_FRESHMAIL_REJESTRACJA_PREFIX']['0'].'" size="53" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_FRESHMAIL_REJESTRACJA_PREFIX']['2'].'</b></em>';
                        ?>
                      </td>
                    </tr>                       
                    
                    <tr>
                      <td colspan="2" class="NaglowekAfiliacja">
                        <strong>Automatyczna subskrypcja zakupionych produktów</strong>
                        <span class="maleInfo">
                            Funkcja ułatwiająca tworzenie list mailingowych klientów w oparciu o zakupione przez klientów produkty. Dzięki temu można wysłać maile tylko do klientów, którzy zakupili określone produkty.
                            Po złożeniu przez klienta zamówienia jego adres email jest dodawany do listy odbiorców, której nazwa jest tworzona na podstawie ustalonego prefiksu oraz id produktu np. Produkt 55
                            Jeżeli klient dokona zakupu kilku produktów zostanie zapisany do kilku list odpowiadających poszczególnym produktom. Jeżeli lista nie istnieje w systemie FreshMail - zostanie utworzona.
                            Warunkiem zapisu adresu klienta do list jest wyrażenie przez klienta zgody na otrzymywanie newslettera.
                        </span>
                      </td>
                    </tr>    

                    <tr class="SledzeniePozycja">
                      <td>
                        <label>Włącz subskrypcję produktów:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_FRESHMAIL_WLACZONY_PRODUKTY']['1'], $parametr['INTEGRACJA_FRESHMAIL_WLACZONY_PRODUKTY']['0'], 'integracja_freshmail_wlaczony_produkty', $parametr['INTEGRACJA_FRESHMAIL_WLACZONY_PRODUKTY']['2'], '', $parametr['INTEGRACJA_FRESHMAIL_WLACZONY_PRODUKTY']['3'] );
                        ?>
                      </td>
                    </tr>      

                    <tr class="SledzeniePozycja">
                      <td>
                        <label class="required" for="integracja_freshmail_produkty_prefix">Prefix listy odbiorców:</label>
                      </td>
                      <td>
                        <?php
                        echo '<input type="text" name="integracja_freshmail_produkty_prefix" id="integracja_freshmail_produkty_prefix" value="'.$parametr['INTEGRACJA_FRESHMAIL_PRODUKTY_PREFIX']['0'].'" size="53" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_FRESHMAIL_PRODUKTY_PREFIX']['2'].'</b></em>';
                        ?>
                      </td>
                    </tr>                    
                    
                    <tr>
                      <td colspan="2" class="NaglowekAfiliacja">
                        <strong>Automatyczna subskrypcja klientów dokonujących zakupy</strong>
                        <span class="maleInfo">
                            Funkcja ułatwiająca tworzenie list mailingowych klientów, którzy złożyli w sklepie zamówienia. Dzięki temu można wysłać maile tylko do klientów, którzy dokonali w sklepie zakupów.
                            Po złożeniu przez klienta zamówienia jego adres email jest dodawany do listy odbiorców, której nazwa jest tworzona na podstawie ustalonej nazwy listy. Jeżeli lista nie istnieje w systemie FreshMail - zostanie utworzona.
                            Warunkiem zapisu adresu klienta do list jest wyrażenie przez klienta zgody na otrzymywanie newslettera.
                        </span>
                      </td>
                    </tr>    

                    <tr class="SledzeniePozycja">
                      <td>
                        <label>Włącz subskrypcję kupujących klientów:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_FRESHMAIL_WLACZONY_KUPUJACY']['1'], $parametr['INTEGRACJA_FRESHMAIL_WLACZONY_KUPUJACY']['0'], 'integracja_freshmail_wlaczony_kupujacy', $parametr['INTEGRACJA_FRESHMAIL_WLACZONY_KUPUJACY']['2'], '', $parametr['INTEGRACJA_FRESHMAIL_WLACZONY_KUPUJACY']['3'] );
                        ?>
                      </td>
                    </tr>      

                    <tr class="SledzeniePozycja">
                      <td>
                        <label class="required" for="integracja_freshmail_kupujacy_prefix">Nazwa listy odbiorców:</label>
                      </td>
                      <td>
                        <?php
                        echo '<input type="text" name="integracja_freshmail_kupujacy_prefix" id="integracja_freshmail_kupujacy_prefix" value="'.$parametr['INTEGRACJA_FRESHMAIL_KUPUJACY_PREFIX']['0'].'" size="53" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_FRESHMAIL_KUPUJACY_PREFIX']['2'].'</b></em>';
                        ?>
                      </td>
                    </tr>                      
                    <tr>
                      <td colspan="2">
                        <div class="przyciski_dolne">
                          <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'freshmail' ? $wynik : '' ); ?>
                        </div>
                      </td>
                    </tr>
                    
                  </table>

              </div>
            </form>
          </div>
          
          <!-- Integracja z Mailjet -->
          <div class="Sledzenie SledzenieUkryte">
          
            <form action="integracje/konfiguracja_afiliacja.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="mailjetForm" class="cmxform"> 
            
              <div>
                <input type="hidden" name="akcja" value="zapisz" />
                <input type="hidden" name="system" value="mailjet" />
              </div>
              
              <div class="ObramowanieForm">
              
                  <table>
                  
                    <tr class="DivNaglowek">
                      <td colspan="2">Mailjet email marketing to intuicyjny program do wysyłania newsletterów, mailingów i autoresponderów.</td>
                    </tr>
                    
                    <tr><td colspan="2" class="SledzenieOpis">
                      <div>Mailjet to francuska platforma e-mail marketingu założona w 2010 roku. Mailjet to oparty na chmurze system dostarczania i śledzenia poczty e-mail, który umożliwia użytkownikom wysyłanie e-maili marketingowych i e-maili transakcyjnych.</div>                      
                      <img src="obrazki/logo/logo_mailjet.png" alt="" />
                      <span class="maleSukces" style="margin:0px 10px 15px 10px">Integracja sklepu z Mailjet pozwala na automatyzację przenoszenia adresów email ze sklepu do Mailjet. Klient zapisując się w sklepie do newslettera jest również automatycznie dodawany do listy odbiorców w systemie Mailjet. </span>
                    </td></tr>                   
                  
                    <tr class="SledzeniePozycja">
                      <td>
                        <label>Włącz integrację:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_MAILJET_WLACZONY']['1'], $parametr['INTEGRACJA_MAILJET_WLACZONY']['0'], 'integracja_mailjet_wlaczony', $parametr['INTEGRACJA_MAILJET_WLACZONY']['2'], '', $parametr['INTEGRACJA_MAILJET_WLACZONY']['3'] );
                        ?>
                      </td>
                    </tr>
                    
                    <tr class="SledzeniePozycja">
                      <td>
                        <label class="required" for="integracja_mailjet_key">Klucz API:</label>
                      </td>
                      <td>
                        <?php
                        echo '<input type="text" name="integracja_mailjet_key" id="integracja_mailjet_key" value="'.$parametr['INTEGRACJA_MAILJET_KEY']['0'].'" size="53" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_MAILJET_KEY']['2'].'</b></em>';
                        ?>
                      </td>
                    </tr>                    
                    
                    <tr class="SledzeniePozycja">
                      <td>
                        <label class="required" for="integracja_mailjet_secret">Klucz API secret:</label>
                      </td>
                      <td>
                        <?php
                        echo '<input type="text" name="integracja_mailjet_secret" id="integracja_mailjet_secret" value="'.$parametr['INTEGRACJA_MAILJET_SECRET']['0'].'" size="53" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_MAILJET_SECRET']['2'].'</b></em>';
                        ?>
                      </td>
                    </tr>  
                    
                    <tr>
                      <td colspan="2" class="NaglowekAfiliacja">
                        <strong>Automatyczna subskrypcja wszystkich klientów którzy wyrazili zgodę na otrzymywanie newslettera</strong>
                        <span class="maleInfo">
                            Funkcja ułatwiająca tworzenie list mailingowych klientów, którzy wyrazili zgodę na otrzymywanie newslettera. Po zapisaniu się klienta do newslettera (poprzez box, przy rejestracji czy module newslettera) jego adres email 
                            jest dodawany do domyślnej (zdefiniowanej poniżej) listy odbiorców. Jeżeli lista nie istnieje w systemie Mailjet - zostanie utworzona.
                        </span>
                      </td>
                    </tr>                     
                    
                    <tr class="SledzeniePozycja">
                      <td>
                        <label class="required" for="integracja_mailjet_domyslna_lista">Domyślna lista odbiorców:</label>
                      </td>
                      <td>
                        <?php
                        echo '<input type="text" name="integracja_mailjet_domyslna_lista" id="integracja_mailjet_domyslna_lista" value="'.$parametr['INTEGRACJA_MAILJET_DOMYSLNA_LISTA']['0'].'" size="53" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_MAILJET_DOMYSLNA_LISTA']['2'].'</b></em>';
                        ?>
                      </td>
                    </tr>          

                    <tr>
                      <td colspan="2" class="NaglowekAfiliacja">
                        <strong>Dodatkowa lista odbiorców dla klientów przy rejestracji konta</strong>
                        <span class="maleInfo">
                            Funkcja ułatwiająca tworzenie list mailingowych klientów, którzy dokonali rejestracji konta w sklepie. Dzięki temu można wysłać maile tylko do klientów, którzy założyli w sklepie konto.
                            Po założeniu przez klienta konta jego adres email jest dodawany oprócz domyślnej listy odbiorców także do dodatkowej listy, której nazwa jest tworzona na podstawie ustalonej nazwy listy.
                            Jeżeli lista nie istnieje w systemie Mailjet - zostanie utworzona. Warunkiem zapisu adresu klienta do list jest wyrażenie przez klienta zgody na otrzymywanie newslettera.
                        </span>
                      </td>
                    </tr>    

                    <tr class="SledzeniePozycja">
                      <td>
                        <label>Włącz subskrypcję klientów:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_MAILJET_WLACZONY_REJESTRACJA']['1'], $parametr['INTEGRACJA_MAILJET_WLACZONY_REJESTRACJA']['0'], 'integracja_mailjet_wlaczony_rejestracja', $parametr['INTEGRACJA_MAILJET_WLACZONY_REJESTRACJA']['2'], '', $parametr['INTEGRACJA_MAILJET_WLACZONY_REJESTRACJA']['3'] );
                        ?>
                      </td>
                    </tr>      

                    <tr class="SledzeniePozycja">
                      <td>
                        <label class="required" for="integracja_mailjet_rejestracja_prefix">Nazwa listy odbiorców:</label>
                      </td>
                      <td>
                        <?php
                        echo '<input type="text" name="integracja_mailjet_rejestracja_prefix" id="integracja_mailjet_rejestracja_prefix" value="'.$parametr['INTEGRACJA_MAILJET_REJESTRACJA_PREFIX']['0'].'" size="53" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_MAILJET_REJESTRACJA_PREFIX']['2'].'</b></em>';
                        ?>
                      </td>
                    </tr>                       
                    
                    <tr>
                      <td colspan="2" class="NaglowekAfiliacja">
                        <strong>Automatyczna subskrypcja zakupionych produktów</strong>
                        <span class="maleInfo">
                            Funkcja ułatwiająca tworzenie list mailingowych klientów w oparciu o zakupione przez klientów produkty. Dzięki temu można wysłać maile tylko do klientów, którzy zakupili określone produkty.
                            Po złożeniu przez klienta zamówienia jego adres email jest dodawany do listy odbiorców, której nazwa jest tworzona na podstawie ustalonego prefiksu oraz id produktu np. Produkt 55
                            Jeżeli klient dokona zakupu kilku produktów zostanie zapisany do kilku list odpowiadających poszczególnym produktom. Jeżeli lista nie istnieje w systemie Mailjet - zostanie utworzona.
                            Warunkiem zapisu adresu klienta do list jest wyrażenie przez klienta zgody na otrzymywanie newslettera.
                        </span>
                      </td>
                    </tr>    

                    <tr class="SledzeniePozycja">
                      <td>
                        <label>Włącz subskrypcję produktów:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_MAILJET_WLACZONY_PRODUKTY']['1'], $parametr['INTEGRACJA_MAILJET_WLACZONY_PRODUKTY']['0'], 'integracja_mailjet_wlaczony_produkty', $parametr['INTEGRACJA_MAILJET_WLACZONY_PRODUKTY']['2'], '', $parametr['INTEGRACJA_MAILJET_WLACZONY_PRODUKTY']['3'] );
                        ?>
                      </td>
                    </tr>      

                    <tr class="SledzeniePozycja">
                      <td>
                        <label class="required" for="integracja_mailjet_produkty_prefix">Prefix listy odbiorców:</label>
                      </td>
                      <td>
                        <?php
                        echo '<input type="text" name="integracja_mailjet_produkty_prefix" id="integracja_mailjet_produkty_prefix" value="'.$parametr['INTEGRACJA_MAILJET_PRODUKTY_PREFIX']['0'].'" size="53" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_MAILJET_PRODUKTY_PREFIX']['2'].'</b></em>';
                        ?>
                      </td>
                    </tr>                    
                    
                    <tr>
                      <td colspan="2" class="NaglowekAfiliacja">
                        <strong>Automatyczna subskrypcja klientów dokonujących zakupy</strong>
                        <span class="maleInfo">
                            Funkcja ułatwiająca tworzenie list mailingowych klientów, którzy złożyli w sklepie zamówienia. Dzięki temu można wysłać maile tylko do klientów, którzy dokonali w sklepie zakupów.
                            Po złożeniu przez klienta zamówienia jego adres email jest dodawany do listy odbiorców, której nazwa jest tworzona na podstawie ustalonej nazwy listy. Jeżeli lista nie istnieje w systemie Mailjet - zostanie utworzona.
                            Warunkiem zapisu adresu klienta do list jest wyrażenie przez klienta zgody na otrzymywanie newslettera.
                        </span>
                      </td>
                    </tr>    

                    <tr class="SledzeniePozycja">
                      <td>
                        <label>Włącz subskrypcję kupujących klientów:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_MAILJET_WLACZONY_KUPUJACY']['1'], $parametr['INTEGRACJA_MAILJET_WLACZONY_KUPUJACY']['0'], 'integracja_mailjet_wlaczony_kupujacy', $parametr['INTEGRACJA_MAILJET_WLACZONY_KUPUJACY']['2'], '', $parametr['INTEGRACJA_MAILJET_WLACZONY_KUPUJACY']['3'] );
                        ?>
                      </td>
                    </tr>      

                    <tr class="SledzeniePozycja">
                      <td>
                        <label class="required" for="integracja_mailjet_kupujacy_prefix">Nazwa listy odbiorców:</label>
                      </td>
                      <td>
                        <?php
                        echo '<input type="text" name="integracja_mailjet_kupujacy_prefix" id="integracja_mailjet_kupujacy_prefix" value="'.$parametr['INTEGRACJA_MAILJET_KUPUJACY_PREFIX']['0'].'" size="53" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_MAILJET_KUPUJACY_PREFIX']['2'].'</b></em>';
                        ?>
                      </td>
                    </tr>                      
                    <tr>
                      <td colspan="2">
                        <div class="przyciski_dolne">
                          <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'mailjet' ? $wynik : '' ); ?>
                        </div>
                      </td>
                    </tr>
                    
                  </table>

              </div>
            </form>
          </div>
          
         
          <!-- Integracja z MailerLite -->
          <div class="Sledzenie SledzenieUkryte">
          
            <form action="integracje/konfiguracja_afiliacja.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="mailerliteForm" class="cmxform"> 
            
              <div>
                <input type="hidden" name="akcja" value="zapisz" />
                <input type="hidden" name="system" value="mailerlite" />
              </div>
              
              <div class="ObramowanieForm">
              
                  <table>
                  
                    <tr class="DivNaglowek">
                      <td colspan="2">MailerLite email marketing to intuicyjny program do wysyłania newsletterów, mailingów i autoresponderów.</td>
                    </tr>
                    
                    <tr><td colspan="2" class="SledzenieOpis">
                      <div>MailerLite to nowoczesne narzędzie do tworzenia oraz wysyłki newsletterów i mailingów do własnej bazy klientów. Pozwala w pełni zautomatyzować budowanie listy odbiorców oraz wysyłkę newsletterów. Wystarczy skorzystać z jeden z dziesiątek szablonów, uzupełnić go własną treścią i już można pozyskać nowych klientów na swoje produkty i usługi. Wysyłaj newslettery i informuj swoich klientów o nowościach oraz ofertach specjalnych.</div>                      
                      <img src="obrazki/logo/logo_mailerlite.png" alt="" />
                      <span class="maleSukces" style="margin:0px 10px 15px 10px">Integracja sklepu z MailerLite pozwala na automatyzację przenoszenia adresów email ze sklepu do MailerLite. Klient zapisując się w sklepie do newslettera jest również automatycznie dodawany do listy odbiorców w systemie MailerLite. </span>
                    </td></tr>                   
                  
                    <tr class="SledzeniePozycja">
                      <td>
                        <label>Włącz integrację:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_MAILERLITE_WLACZONY']['1'], $parametr['INTEGRACJA_MAILERLITE_WLACZONY']['0'], 'integracja_mailerlite_wlaczony', $parametr['INTEGRACJA_MAILERLITE_WLACZONY']['2'], '', $parametr['INTEGRACJA_MAILERLITE_WLACZONY']['3'] );
                        ?>
                      </td>
                    </tr>
                    
                    <tr class="SledzeniePozycja">
                      <td>
                        <label class="required" for="integracja_mailerlite_key">Klucz API:</label>
                      </td>
                      <td>
                        <?php
                        echo '<input type="text" name="integracja_mailerlite_key" id="integracja_mailerlite_key" value="'.$parametr['INTEGRACJA_MAILERLITE_KEY']['0'].'" size="53" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_MAILERLITE_KEY']['2'].'</b></em>';
                        ?>
                      </td>
                    </tr>                    

                    <tr>
                      <td colspan="2" class="NaglowekAfiliacja">
                        <strong>Automatyczna subskrypcja wszystkich klientów którzy wyrazili zgodę na otrzymywanie newslettera</strong>
                        <span class="maleInfo">
                            Funkcja ułatwiająca tworzenie list mailingowych klientów, którzy wyrazili zgodę na otrzymywanie newslettera. Po zapisaniu się klienta do newslettera (poprzez box, przy rejestracji czy module newslettera) jego adres email 
                            jest dodawany do domyślnej (zdefiniowanej poniżej) listy odbiorców. Jeżeli lista nie istnieje w systemie FreshMail - zostanie utworzona.
                        </span>
                      </td>
                    </tr>                     
                    
                    <tr class="SledzeniePozycja">
                      <td>
                        <label class="required" for="integracja_mailerlite_domyslna_lista">Domyślna lista odbiorców:</label>
                      </td>
                      <td>
                        <?php
                        echo '<input type="text" name="integracja_mailerlite_domyslna_lista" id="integracja_mailerlite_domyslna_lista" value="'.$parametr['INTEGRACJA_MAILERLITE_DOMYSLNA_LISTA']['0'].'" size="53" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_MAILERLITE_DOMYSLNA_LISTA']['2'].'</b></em>';
                        ?>
                      </td>
                    </tr>          

                    <tr>
                      <td colspan="2" class="NaglowekAfiliacja">
                        <strong>Dodatkowa lista odbiorców dla klientów przy rejestracji konta</strong>
                        <span class="maleInfo">
                            Funkcja ułatwiająca tworzenie list mailingowych klientów, którzy dokonali rejestracji konta w sklepie. Dzięki temu można wysłać maile tylko do klientów, którzy założyli w sklepie konto.
                            Po założeniu przez klienta konta jego adres email jest dodawany oprócz domyślnej listy odbiorców także do dodatkowej listy, której nazwa jest tworzona na podstawie ustalonej nazwy listy.
                            Jeżeli lista nie istnieje w systemie FreshMail - zostanie utworzona. Warunkiem zapisu adresu klienta do list jest wyrażenie przez klienta zgody na otrzymywanie newslettera.
                        </span>
                      </td>
                    </tr>    

                    <tr class="SledzeniePozycja">
                      <td>
                        <label>Włącz subskrypcję klientów:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_MAILERLITE_WLACZONY_REJESTRACJA']['1'], $parametr['INTEGRACJA_MAILERLITE_WLACZONY_REJESTRACJA']['0'], 'integracja_mailerlite_wlaczony_rejestracja', $parametr['INTEGRACJA_MAILERLITE_WLACZONY_REJESTRACJA']['2'], '', $parametr['INTEGRACJA_MAILERLITE_WLACZONY_REJESTRACJA']['3'] );
                        ?>
                      </td>
                    </tr>      

                    <tr class="SledzeniePozycja">
                      <td>
                        <label class="required" for="integracja_mailerlite_rejestracja_prefix">Nazwa listy odbiorców:</label>
                      </td>
                      <td>
                        <?php
                        echo '<input type="text" name="integracja_mailerlite_rejestracja_prefix" id="integracja_mailerlite_rejestracja_prefix" value="'.$parametr['INTEGRACJA_MAILERLITE_REJESTRACJA_PREFIX']['0'].'" size="53" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_MAILERLITE_REJESTRACJA_PREFIX']['2'].'</b></em>';
                        ?>
                      </td>
                    </tr>                       
                    
                    <tr>
                      <td colspan="2" class="NaglowekAfiliacja">
                        <strong>Automatyczna subskrypcja zakupionych produktów</strong>
                        <span class="maleInfo">
                            Funkcja ułatwiająca tworzenie list mailingowych klientów w oparciu o zakupione przez klientów produkty. Dzięki temu można wysłać maile tylko do klientów, którzy zakupili określone produkty.
                            Po złożeniu przez klienta zamówienia jego adres email jest dodawany do listy odbiorców, której nazwa jest tworzona na podstawie ustalonego prefiksu oraz id produktu np. Produkt 55
                            Jeżeli klient dokona zakupu kilku produktów zostanie zapisany do kilku list odpowiadających poszczególnym produktom. Jeżeli lista nie istnieje w systemie FreshMail - zostanie utworzona.
                            Warunkiem zapisu adresu klienta do list jest wyrażenie przez klienta zgody na otrzymywanie newslettera.
                        </span>
                      </td>
                    </tr>    

                    <tr class="SledzeniePozycja">
                      <td>
                        <label>Włącz subskrypcję produktów:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_MAILERLITE_WLACZONY_PRODUKTY']['1'], $parametr['INTEGRACJA_MAILERLITE_WLACZONY_PRODUKTY']['0'], 'integracja_mailerlite_wlaczony_produkty', $parametr['INTEGRACJA_MAILERLITE_WLACZONY_PRODUKTY']['2'], '', $parametr['INTEGRACJA_MAILERLITE_WLACZONY_PRODUKTY']['3'] );
                        ?>
                      </td>
                    </tr>      

                    <tr class="SledzeniePozycja">
                      <td>
                        <label class="required" for="integracja_mailerlite_produkty_prefix">Prefix listy odbiorców:</label>
                      </td>
                      <td>
                        <?php
                        echo '<input type="text" name="integracja_mailerlite_produkty_prefix" id="integracja_mailerlite_produkty_prefix" value="'.$parametr['INTEGRACJA_MAILERLITE_PRODUKTY_PREFIX']['0'].'" size="53" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_MAILERLITE_PRODUKTY_PREFIX']['2'].'</b></em>';
                        ?>
                      </td>
                    </tr>                    
                    
                    <tr>
                      <td colspan="2" class="NaglowekAfiliacja">
                        <strong>Automatyczna subskrypcja klientów dokonujących zakupy</strong>
                        <span class="maleInfo">
                            Funkcja ułatwiająca tworzenie list mailingowych klientów, którzy złożyli w sklepie zamówienia. Dzięki temu można wysłać maile tylko do klientów, którzy dokonali w sklepie zakupów.
                            Po złożeniu przez klienta zamówienia jego adres email jest dodawany do listy odbiorców, której nazwa jest tworzona na podstawie ustalonej nazwy listy. Jeżeli lista nie istnieje w systemie FreshMail - zostanie utworzona.
                            Warunkiem zapisu adresu klienta do list jest wyrażenie przez klienta zgody na otrzymywanie newslettera.
                        </span>
                      </td>
                    </tr>    

                    <tr class="SledzeniePozycja">
                      <td>
                        <label>Włącz subskrypcję kupujących klientów:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_MAILERLITE_WLACZONY_KUPUJACY']['1'], $parametr['INTEGRACJA_MAILERLITE_WLACZONY_KUPUJACY']['0'], 'integracja_mailerlite_wlaczony_kupujacy', $parametr['INTEGRACJA_MAILERLITE_WLACZONY_KUPUJACY']['2'], '', $parametr['INTEGRACJA_MAILERLITE_WLACZONY_KUPUJACY']['3'] );
                        ?>
                      </td>
                    </tr>      

                    <tr class="SledzeniePozycja">
                      <td>
                        <label class="required" for="integracja_mailerlite_kupujacy_prefix">Nazwa listy odbiorców:</label>
                      </td>
                      <td>
                        <?php
                        echo '<input type="text" name="integracja_mailerlite_kupujacy_prefix" id="integracja_mailerlite_kupujacy_prefix" value="'.$parametr['INTEGRACJA_MAILERLITE_KUPUJACY_PREFIX']['0'].'" size="53" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_MAILERLITE_KUPUJACY_PREFIX']['2'].'</b></em>';
                        ?>
                      </td>
                    </tr>                      
                    <tr>
                      <td colspan="2">
                        <div class="przyciski_dolne">
                          <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'mailerlite' ? $wynik : '' ); ?>
                        </div>
                      </td>
                    </tr>
                    
                  </table>

              </div>
            </form>
          </div>
          
          
          <!-- Integracja z Ecomail -->
          <div class="Sledzenie SledzenieUkryte">
          
            <form action="integracje/konfiguracja_afiliacja.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="ecomailForm" class="cmxform"> 
            
              <div>
                <input type="hidden" name="akcja" value="zapisz" />
                <input type="hidden" name="system" value="ecomail" />
              </div>
              
              <div class="ObramowanieForm">
              
                  <table>
                  
                    <tr class="DivNaglowek">
                      <td colspan="2">Ecomail email marketing to intuicyjny program do wysyłania newsletterów, mailingów i autoresponderów.</td>
                    </tr>
                    
                    <tr><td colspan="2" class="SledzenieOpis">
                      <div>Ecomail to nowoczesne narzędzie do tworzenia oraz wysyłki newsletterów i mailingów do własnej bazy klientów. Pozwala w pełni zautomatyzować budowanie listy odbiorców oraz wysyłkę newsletterów. Wystarczy skorzystać z jeden z dziesiątek szablonów, uzupełnić go własną treścią i już można pozyskać nowych klientów na swoje produkty i usługi. Wysyłaj newslettery i informuj swoich klientów o nowościach oraz ofertach specjalnych.</div>                      
                      <img src="obrazki/logo/logo_ecomail.png" alt="" />
                      <span class="maleSukces" style="margin:0px 10px 15px 10px">Integracja sklepu z Ecomail pozwala na automatyzację przenoszenia adresów email ze sklepu do Ecomail. Klient zapisując się w sklepie do newslettera jest również automatycznie dodawany do listy odbiorców w systemie Ecomail. </span>
                    </td></tr>                   
                  
                    <tr class="SledzeniePozycja">
                      <td>
                        <label>Włącz integrację:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_ECOMAIL_WLACZONY']['1'], $parametr['INTEGRACJA_ECOMAIL_WLACZONY']['0'], 'integracja_ecomail_wlaczony', $parametr['INTEGRACJA_ECOMAIL_WLACZONY']['2'], '', $parametr['INTEGRACJA_ECOMAIL_WLACZONY']['3'] );
                        ?>
                      </td>
                    </tr>
                    
                    <tr class="SledzeniePozycja">
                      <td>
                        <label class="required" for="integracja_ecomail_key">Klucz API:</label>
                      </td>
                      <td>
                        <?php
                        echo '<input type="text" name="integracja_ecomail_key" id="integracja_ecomail_key" value="'.$parametr['INTEGRACJA_ECOMAIL_KEY']['0'].'" size="53" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_ECOMAIL_KEY']['2'].'</b></em>';
                        ?>
                      </td>
                    </tr>                    

                    <tr>
                      <td colspan="2" class="NaglowekAfiliacja">
                        <strong>Automatyczna subskrypcja wszystkich klientów którzy wyrazili zgodę na otrzymywanie newslettera</strong>
                        <span class="maleInfo">
                            Funkcja ułatwiająca tworzenie list mailingowych klientów, którzy wyrazili zgodę na otrzymywanie newslettera. Po zapisaniu się klienta do newslettera (poprzez box, przy rejestracji czy module newslettera) jego adres email 
                            jest dodawany do domyślnej (zdefiniowanej poniżej) listy odbiorców. Jeżeli lista nie istnieje w systemie FreshMail - zostanie utworzona.
                        </span>
                      </td>
                    </tr>                     
                    
                    <tr class="SledzeniePozycja">
                      <td>
                        <label class="required" for="integracja_ecomail_domyslna_lista">Domyślna lista odbiorców:</label>
                      </td>
                      <td>
                        <?php
                        echo '<input type="text" name="integracja_ecomail_domyslna_lista" id="integracja_ecomail_domyslna_lista" value="'.$parametr['INTEGRACJA_ECOMAIL_DOMYSLNA_LISTA']['0'].'" size="53" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_ECOMAIL_DOMYSLNA_LISTA']['2'].'</b></em>';
                        ?>
                      </td>
                    </tr>          

                    <tr>
                      <td colspan="2" class="NaglowekAfiliacja">
                        <strong>Dodatkowa lista odbiorców dla klientów przy rejestracji konta</strong>
                        <span class="maleInfo">
                            Funkcja ułatwiająca tworzenie list mailingowych klientów, którzy dokonali rejestracji konta w sklepie. Dzięki temu można wysłać maile tylko do klientów, którzy założyli w sklepie konto.
                            Po założeniu przez klienta konta jego adres email jest dodawany oprócz domyślnej listy odbiorców także do dodatkowej listy, której nazwa jest tworzona na podstawie ustalonej nazwy listy.
                            Jeżeli lista nie istnieje w systemie FreshMail - zostanie utworzona. Warunkiem zapisu adresu klienta do list jest wyrażenie przez klienta zgody na otrzymywanie newslettera.
                        </span>
                      </td>
                    </tr>    

                    <tr class="SledzeniePozycja">
                      <td>
                        <label>Włącz subskrypcję klientów:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_ECOMAIL_WLACZONY_REJESTRACJA']['1'], $parametr['INTEGRACJA_ECOMAIL_WLACZONY_REJESTRACJA']['0'], 'integracja_ecomail_wlaczony_rejestracja', $parametr['INTEGRACJA_ECOMAIL_WLACZONY_REJESTRACJA']['2'], '', $parametr['INTEGRACJA_ECOMAIL_WLACZONY_REJESTRACJA']['3'] );
                        ?>
                      </td>
                    </tr>      

                    <tr class="SledzeniePozycja">
                      <td>
                        <label class="required" for="integracja_ecomail_rejestracja_prefix">Nazwa listy odbiorców:</label>
                      </td>
                      <td>
                        <?php
                        echo '<input type="text" name="integracja_ecomail_rejestracja_prefix" id="integracja_ecomail_rejestracja_prefix" value="'.$parametr['INTEGRACJA_ECOMAIL_REJESTRACJA_PREFIX']['0'].'" size="53" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_ECOMAIL_REJESTRACJA_PREFIX']['2'].'</b></em>';
                        ?>
                      </td>
                    </tr>                       
                    
                    <tr>
                      <td colspan="2" class="NaglowekAfiliacja">
                        <strong>Automatyczna subskrypcja zakupionych produktów</strong>
                        <span class="maleInfo">
                            Funkcja ułatwiająca tworzenie list mailingowych klientów w oparciu o zakupione przez klientów produkty. Dzięki temu można wysłać maile tylko do klientów, którzy zakupili określone produkty.
                            Po złożeniu przez klienta zamówienia jego adres email jest dodawany do listy odbiorców, której nazwa jest tworzona na podstawie ustalonego prefiksu oraz id produktu np. Produkt 55
                            Jeżeli klient dokona zakupu kilku produktów zostanie zapisany do kilku list odpowiadających poszczególnym produktom. Jeżeli lista nie istnieje w systemie FreshMail - zostanie utworzona.
                            Warunkiem zapisu adresu klienta do list jest wyrażenie przez klienta zgody na otrzymywanie newslettera.
                        </span>
                      </td>
                    </tr>    

                    <tr class="SledzeniePozycja">
                      <td>
                        <label>Włącz subskrypcję produktów:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_ECOMAIL_WLACZONY_PRODUKTY']['1'], $parametr['INTEGRACJA_ECOMAIL_WLACZONY_PRODUKTY']['0'], 'integracja_ecomail_wlaczony_produkty', $parametr['INTEGRACJA_ECOMAIL_WLACZONY_PRODUKTY']['2'], '', $parametr['INTEGRACJA_ECOMAIL_WLACZONY_PRODUKTY']['3'] );
                        ?>
                      </td>
                    </tr>      

                    <tr class="SledzeniePozycja">
                      <td>
                        <label class="required" for="integracja_ecomail_produkty_prefix">Prefix listy odbiorców:</label>
                      </td>
                      <td>
                        <?php
                        echo '<input type="text" name="integracja_ecomail_produkty_prefix" id="integracja_ecomail_produkty_prefix" value="'.$parametr['INTEGRACJA_ECOMAIL_PRODUKTY_PREFIX']['0'].'" size="53" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_ECOMAIL_PRODUKTY_PREFIX']['2'].'</b></em>';
                        ?>
                      </td>
                    </tr>                    
                    
                    <tr>
                      <td colspan="2" class="NaglowekAfiliacja">
                        <strong>Automatyczna subskrypcja klientów dokonujących zakupy</strong>
                        <span class="maleInfo">
                            Funkcja ułatwiająca tworzenie list mailingowych klientów, którzy złożyli w sklepie zamówienia. Dzięki temu można wysłać maile tylko do klientów, którzy dokonali w sklepie zakupów.
                            Po złożeniu przez klienta zamówienia jego adres email jest dodawany do listy odbiorców, której nazwa jest tworzona na podstawie ustalonej nazwy listy. Jeżeli lista nie istnieje w systemie FreshMail - zostanie utworzona.
                            Warunkiem zapisu adresu klienta do list jest wyrażenie przez klienta zgody na otrzymywanie newslettera.
                        </span>
                      </td>
                    </tr>    

                    <tr class="SledzeniePozycja">
                      <td>
                        <label>Włącz subskrypcję kupujących klientów:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_ECOMAIL_WLACZONY_KUPUJACY']['1'], $parametr['INTEGRACJA_ECOMAIL_WLACZONY_KUPUJACY']['0'], 'integracja_ecomail_wlaczony_kupujacy', $parametr['INTEGRACJA_ECOMAIL_WLACZONY_KUPUJACY']['2'], '', $parametr['INTEGRACJA_ECOMAIL_WLACZONY_KUPUJACY']['3'] );
                        ?>
                      </td>
                    </tr>      

                    <tr class="SledzeniePozycja">
                      <td>
                        <label class="required" for="integracja_ecomail_kupujacy_prefix">Nazwa listy odbiorców:</label>
                      </td>
                      <td>
                        <?php
                        echo '<input type="text" name="integracja_ecomail_kupujacy_prefix" id="integracja_ecomail_kupujacy_prefix" value="'.$parametr['INTEGRACJA_ECOMAIL_KUPUJACY_PREFIX']['0'].'" size="53" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_ECOMAIL_KUPUJACY_PREFIX']['2'].'</b></em>';
                        ?>
                      </td>
                    </tr>                      
                    <tr>
                      <td colspan="2">
                        <div class="przyciski_dolne">
                          <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'ecomail' ? $wynik : '' ); ?>
                        </div>
                      </td>
                    </tr>
                    
                  </table>

              </div>
            </form>
          </div>
          
          
          <!-- Integracja z Getall -->
          <div class="Sledzenie SledzenieUkryte">
          
            <form action="integracje/konfiguracja_afiliacja.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="getallForm" class="cmxform"> 
            
              <div>
                <input type="hidden" name="akcja" value="zapisz" />
                <input type="hidden" name="system" value="getall" />
              </div>
              
              <div class="ObramowanieForm">
              
                  <table>
                  
                    <tr class="DivNaglowek">
                      <td colspan="2">GetAll to program do newslettera i kompleksowy pakiet profesjonalnych narzędzi do email marketingu.</td>
                    </tr>
                    
                    <tr><td colspan="2" class="SledzenieOpis">
                    <div>GetAll to kompleksowy zestaw narzędzi do email marketingu i reklamy w Internecie. Jest to profesjonalna platforma marketingowa i całościowy system do skutecznego budowania internetowych biznesów przy zastosowaniu wysokiego poziomu automatyzacji.</div>                      
                      <img src="obrazki/logo/logo_getall.png" alt="" />
                      <span class="maleSukces" style="margin:0px 10px 15px 10px">Integracja sklepu z GetAll pozwala na automatyzację przenoszenia adresów email ze sklepu do GetAll. Klient zapisując się w sklepie do newslettera jest również automatycznie dodawany do listy odbiorców w systemie GetAll. </span>
                      <div style="color:#ff0000;float:none;width:auto;padding:0px;margin:0px 10px 15px 35px">WAŻNE !! Do działania integracji wymagany jest kontakt z GetAll w celu aktywowania funkcji ResponderAddSubscriber() - http://api.getall.pl/responderaddsubscriber/ <i>(Metoda wymaga ręcznej aktywacji na koncie użytkownika. Aby aktywować napisz na info@getall.pl)</i></div>
                    </td></tr>                   
                  
                    <tr class="SledzeniePozycja">
                      <td>
                        <label>Włącz integrację:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_GETALL_WLACZONY']['1'], $parametr['INTEGRACJA_GETALL_WLACZONY']['0'], 'integracja_getall_wlaczony', $parametr['INTEGRACJA_GETALL_WLACZONY']['2'], '', $parametr['INTEGRACJA_GETALL_WLACZONY']['3'] );
                        ?>
                      </td>
                    </tr>
                    
                    <tr class="SledzeniePozycja">
                      <td>
                        <label class="required" for="integracja_getall_key">Klucz API:</label>
                      </td>
                      <td>
                        <?php
                        echo '<input type="text" name="integracja_getall_apikey" id="integracja_getall_apikey" value="'.$parametr['INTEGRACJA_GETALL_APIKEY']['0'].'" size="53" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_GETALL_APIKEY']['2'].'</b></em>';
                        ?>
                      </td>
                    </tr>  

                </table>
                
                <?php
                // sprawdzi czy pokazac dodatkowe opcje
                $zapytanie_getall = "select code, value from settings where code LIKE '%INTEGRACJA_GETALL%'";
                $sql_getall = $db->open_query($zapytanie_getall);
                //
                $getall_stale = array();
                //
                while ($info_getall = $sql_getall->fetch_assoc()) {
                       $getall_stale[$info_getall['code']] = $info_getall['value'];
                }
                //
                $db->close_query($sql_getall);
                
                $tablica_list = array();
                $blad = '';
                
                if ( $getall_stale['INTEGRACJA_GETALL_WLACZONY'] == 'tak' && $getall_stale['INTEGRACJA_GETALL_APIKEY'] != '' ) {
                     //
                     $getall = new GetAll($getall_stale['INTEGRACJA_GETALL_APIKEY']);   
                     $tablica_list = $getall->PobierzListy();
                     //
                     if ( !is_array($tablica_list) ) {
                          //
                          $blad = $tablica_list;
                          //
                     }
                     //
                }
                ?>
                
                <?php if ( $getall_stale['INTEGRACJA_GETALL_WLACZONY'] == 'tak' && !is_array($tablica_list) ) { ?>
                
                    <table>
                        
                            <tr>
                              <td colspan="2" class="NaglowekAfiliacja">
                                <strong style="color:#ff0000">BŁĄD !! Nie można pobrać danych o dostępnych listach klientów</strong>
                                <span class="maleInfo">
                                    Sprawdź czy masz utworzone listy klientów w Getall oraz czy klucz API jest poprawny.
                                    <?php
                                    if ( !empty($blad) ) {
                                        echo '<div style="margin-top:8px"><b>Błąd zwracany przez system Getall: <span style="color:#ff0000">' . $blad . '</span></div>';
                                    }
                                    ?>
                                </span>
                              </td>
                            </tr>   

                    </table>

                <?php } ?>
                
                <div <?php echo (($getall_stale['INTEGRACJA_GETALL_WLACZONY'] == 'tak' && count($tablica_list) > 0 && $blad == '') ? '' : 'style="display:none"'); ?>>
                
                    <table>

                        <tr>
                          <td colspan="2" class="NaglowekAfiliacja">
                            <strong>Automatyczna subskrypcja wszystkich klientów którzy wyrazili zgodę na otrzymywanie newslettera</strong>
                            <span class="maleInfo">
                                Funkcja ułatwiająca tworzenie list mailingowych klientów, którzy wyrazili zgodę na otrzymywanie newslettera. Po zapisaniu się klienta do newslettera (poprzez box, przy rejestracji czy module newslettera) jego adres email 
                                jest dodawany do domyślnej (zdefiniowanej poniżej) listy odbiorców. Jeżeli lista nie istnieje w systemie Getall - zostanie utworzona.
                            </span>
                          </td>
                        </tr>                     
                        
                        <tr class="SledzeniePozycja">
                          <td>
                            <label class="required" for="integracja_domyslna_lista">Domyślna lista odbiorców:</label>
                          </td>
                          <td>
                            <?php
                            if ( is_array($tablica_list) && count($tablica_list) > 0 ) {
                                 //
                                 echo '<select name="integracja_getall_domyslna_lista" id="integracja_getall_domyslna_lista">';
                                 foreach ( $tablica_list as $lista ) {
                                    //
                                    echo '<option value="' . $lista['id'] . '" ' . (($parametr['INTEGRACJA_GETALL_DOMYSLNA_LISTA']['0'] == $lista['id']) ? 'selected="selected"' : '') . '>' . $lista['nazwa'] . '</option>';
                                    //
                                 }
                                 //
                                 echo '</select>';
                                 //
                            }
                            ?>
                          </td>
                        </tr>          

                        <tr>
                          <td colspan="2" class="NaglowekAfiliacja">
                            <strong>Dodatkowa lista odbiorców dla klientów przy rejestracji konta</strong>
                            <span class="maleInfo">
                                Funkcja ułatwiająca tworzenie list mailingowych klientów, którzy dokonali rejestracji konta w sklepie. Dzięki temu można wysłać maile tylko do klientów, którzy założyli w sklepie konto.
                                Po założeniu przez klienta konta jego adres email jest dodawany oprócz domyślnej listy odbiorców także do dodatkowej listy, której nazwa jest tworzona na podstawie ustalonej nazwy listy.
                                Jeżeli lista nie istnieje w systemie FreshMail - zostanie utworzona. Warunkiem zapisu adresu klienta do list jest wyrażenie przez klienta zgody na otrzymywanie newslettera.
                            </span>
                          </td>
                        </tr>    

                        <tr class="SledzeniePozycja">
                          <td>
                            <label>Włącz subskrypcję klientów:</label>
                          </td>
                          <td>
                            <?php
                            echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_GETALL_WLACZONY_REJESTRACJA']['1'], $parametr['INTEGRACJA_GETALL_WLACZONY_REJESTRACJA']['0'], 'integracja_getall_wlaczony_rejestracja', $parametr['INTEGRACJA_GETALL_WLACZONY_REJESTRACJA']['2'], '', $parametr['INTEGRACJA_GETALL_WLACZONY_REJESTRACJA']['3'] );
                            ?>
                          </td>
                        </tr>      

                        <tr class="SledzeniePozycja">
                          <td>
                            <label class="required" for="integracja_getall_rejestracja_prefix">Nazwa listy odbiorców:</label>
                          </td>
                          <td>
                            <?php
                            if ( is_array($tablica_list) && count($tablica_list) > 0 ) {
                                 //
                                 echo '<select name="integracja_getall_rejestracja_prefix" id="integracja_getall_rejestracja_prefix">';
                                 foreach ( $tablica_list as $lista ) {
                                    //
                                    echo '<option value="' . $lista['id'] . '" ' . (($parametr['INTEGRACJA_GETALL_REJESTRACJA_PREFIX']['0'] == $lista['id']) ? 'selected="selected"' : '') . '>' . $lista['nazwa'] . '</option>';
                                    //
                                 }
                                 //
                                 echo '</select>';
                                 //
                            }
                            ?>                          
                          </td>
                        </tr>                       
                        
                        <tr>
                          <td colspan="2" class="NaglowekAfiliacja">
                            <strong>Automatyczna subskrypcja zakupionych produktów</strong>
                            <span class="maleInfo">
                                Funkcja ułatwiająca tworzenie list mailingowych klientów w oparciu o zakupione przez klientów produkty. Dzięki temu można wysłać maile tylko do klientów, którzy zakupili określone produkty.
                                Po złożeniu przez klienta zamówienia jego adres email jest dodawany do listy odbiorców, której nazwa jest tworzona na podstawie ustalonego prefiksu oraz id produktu np. Produkt 55
                                Jeżeli klient dokona zakupu kilku produktów zostanie zapisany do kilku list odpowiadających poszczególnym produktom. Jeżeli lista nie istnieje w systemie Getall - zostanie utworzona.
                                Warunkiem zapisu adresu klienta do list jest wyrażenie przez klienta zgody na otrzymywanie newslettera.
                            </span>
                          </td>
                        </tr>    

                        <tr class="SledzeniePozycja">
                          <td>
                            <label>Włącz subskrypcję produktów:</label>
                          </td>
                          <td>
                            <?php
                            echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_GETALL_WLACZONY_PRODUKTY']['1'], $parametr['INTEGRACJA_GETALL_WLACZONY_PRODUKTY']['0'], 'integracja_getall_wlaczony_produkty', $parametr['INTEGRACJA_GETALL_WLACZONY_PRODUKTY']['2'], '', $parametr['INTEGRACJA_GETALL_WLACZONY_PRODUKTY']['3'] );
                            ?>
                          </td>
                        </tr>      

                        <tr class="SledzeniePozycja">
                          <td>
                            <label class="required" for="integracja_getall_produkty_prefix">Prefix listy odbiorców:</label>
                          </td>
                          <td>
                            <?php
                            if ( is_array($tablica_list) && count($tablica_list) > 0 ) {
                                 //
                                 echo '<select name="integracja_getall_produkty_prefix" id="integracja_getall_produkty_prefix">';
                                 foreach ( $tablica_list as $lista ) {
                                    //
                                    echo '<option value="' . $lista['id'] . '" ' . (($parametr['INTEGRACJA_GETALL_PRODUKTY_PREFIX']['0'] == $lista['id']) ? 'selected="selected"' : '') . '>' . $lista['nazwa'] . '</option>';
                                    //
                                 }
                                 //
                                 echo '</select>';
                                 //
                            }
                            ?>                          
                          </td>
                        </tr>                    
                        
                        <tr>
                          <td colspan="2" class="NaglowekAfiliacja">
                            <strong>Automatyczna subskrypcja klientów dokonujących zakupy</strong>
                            <span class="maleInfo">
                                Funkcja ułatwiająca tworzenie list mailingowych klientów, którzy złożyli w sklepie zamówienia. Dzięki temu można wysłać maile tylko do klientów, którzy dokonali w sklepie zakupów.
                                Po złożeniu przez klienta zamówienia jego adres email jest dodawany do listy odbiorców, której nazwa jest tworzona na podstawie ustalonej nazwy listy. Jeżeli lista nie istnieje w systemie Getall - zostanie utworzona.
                                Warunkiem zapisu adresu klienta do list jest wyrażenie przez klienta zgody na otrzymywanie newslettera.
                            </span>
                          </td>
                        </tr>    

                        <tr class="SledzeniePozycja">
                          <td>
                            <label>Włącz subskrypcję kupujących klientów:</label>
                          </td>
                          <td>
                            <?php
                            echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_GETALL_WLACZONY_KUPUJACY']['1'], $parametr['INTEGRACJA_GETALL_WLACZONY_KUPUJACY']['0'], 'integracja_getall_wlaczony_kupujacy', $parametr['INTEGRACJA_GETALL_WLACZONY_KUPUJACY']['2'], '', $parametr['INTEGRACJA_GETALL_WLACZONY_KUPUJACY']['3'] );
                            ?>
                          </td>
                        </tr>      

                        <tr class="SledzeniePozycja">
                          <td>
                            <label class="required" for="integracja_getall_kupujacy_prefix">Nazwa listy odbiorców:</label>
                          </td>
                          <td>
                            <?php
                            if ( is_array($tablica_list) && count($tablica_list) > 0 ) {
                                 //
                                 echo '<select name="integracja_getall_kupujacy_prefix" id="integracja_getall_kupujacy_prefix">';
                                 foreach ( $tablica_list as $lista ) {
                                    //
                                    echo '<option value="' . $lista['id'] . '" ' . (($parametr['INTEGRACJA_GETALL_KUPUJACY_PREFIX']['0'] == $lista['id']) ? 'selected="selected"' : '') . '>' . $lista['nazwa'] . '</option>';
                                    //
                                 }
                                 //
                                 echo '</select>';
                                 //
                            }
                            ?>                            
                          </td>
                        </tr> 

                    </table>
                      
                </div>
                    
                <table>
                
                  <tr>
                    <td colspan="2">
                      <div class="przyciski_dolne">
                        <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'getall' ? $wynik : '' ); ?>
                      </div>
                    </td>
                  </tr>
                  
                </table>

                <?php unset($getall_stale, $info_getall, $zapytanie_getall); ?>

              </div>
            </form>
          </div>          

          <!-- TrustedShops -->
          <div class="Sledzenie SledzenieUkryte">
          
            <form action="integracje/konfiguracja_afiliacja.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="trustedshopsForm" class="cmxform"> 
            
              <div>
                <input type="hidden" name="akcja" value="zapisz" />
                <input type="hidden" name="system" value="trustedshops" />
              </div>
              
              <div class="ObramowanieForm">
              
                  <table>
                  
                    <tr class="DivNaglowek">
                      <td colspan="2">Trusted Shops - wiodący znak jakości dla sklepów internetowych w Europie</td>
                    </tr>
                    
                    <tr><td colspan="2" class="SledzenieOpis">
                      <div>Trusted Shops to wiodący znak jakości dla sklepów internetowych w Europie oferujący klientom porównanie cen i ochronę kupującego. Dzięki znakowi jakości zwiększasz wiarygodność w oczach klientów. Oferujesz im także dodatkowe zabezpieczenie w postaci ochrony kupującego.</div>
                      <img src="obrazki/logo/trustedshops.png" alt="" />
                    </td></tr>                   
                  
                    <tr class="SledzeniePozycja">
                      <td>
                        <label>Włącz integrację:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_TRUSTEDSHOPS_WLACZONY']['1'], $parametr['INTEGRACJA_TRUSTEDSHOPS_WLACZONY']['0'], 'integracja_trustedshops_wlaczony', $parametr['INTEGRACJA_TRUSTEDSHOPS_WLACZONY']['2'], '', $parametr['INTEGRACJA_TRUSTEDSHOPS_WLACZONY']['3'] );
                        ?>
                      </td>
                    </tr>
                    
                    <tr class="SledzeniePozycja">
                      <td>
                        <label class="required" for="integracja_trustedshops_partnerid">Identyfikator Trusted Shops:</label>
                      </td>
                      <td>
                        <?php
                        echo '<input type="text" name="integracja_trustedshops_partnerid" id="integracja_trustedshops_partnerid" value="'.$parametr['INTEGRACJA_TRUSTEDSHOPS_PARTNERID']['0'].'" size="53" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_TRUSTEDSHOPS_PARTNERID']['2'].'</b></em>';
                        ?>
                      </td>
                    </tr>
                    
                    <tr class="SledzeniePozycja">
                      <td>
                        <label for="integracja_trustedshops_przesuniecie">Przesunięcie trustbadge do góry:</label>
                      </td>
                      <td>
                        <?php
                        echo '<input type="text" name="integracja_trustedshops_przesuniecie" id="integracja_trustedshops_przesuniecie" value="'.$parametr['INTEGRACJA_TRUSTEDSHOPS_PRZESUNIECIE']['0'].'" size="53" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_TRUSTEDSHOPS_PRZESUNIECIE']['2'].'</b></em>';
                        ?>
                      </td>
                    </tr>
                    
                    <tr class="SledzeniePozycja">
                      <td>
                        <label>Format wyświetlania:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_TRUSTEDSHOPS_FORMAT']['1'], $parametr['INTEGRACJA_TRUSTEDSHOPS_FORMAT']['0'], 'integracja_trustedshops_format', $parametr['INTEGRACJA_TRUSTEDSHOPS_FORMAT']['2'], '', $parametr['INTEGRACJA_TRUSTEDSHOPS_FORMAT']['3'] );
                        ?>
                      </td>
                    </tr>
                    
                    <tr class="SledzeniePozycja">
                      <td>
                        <label>Położenie widgetu Trustbadge w wersji mobilnej:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_TRUSTEDSHOPS_MOBILE']['1'], $parametr['INTEGRACJA_TRUSTEDSHOPS_MOBILE']['0'], 'integracja_trustedshops_mobile', $parametr['INTEGRACJA_TRUSTEDSHOPS_MOBILE']['2'], '', $parametr['INTEGRACJA_TRUSTEDSHOPS_MOBILE']['3'] );
                        ?>
                      </td>
                    </tr>
                    
                    <tr>
                      <td colspan="2">
                        <div class="przyciski_dolne">
                          <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'trustedshops' ? $wynik : '' ); ?>
                        </div>
                      </td>
                    </tr>
                    
                  </table>

              </div>
            </form>
          </div>  

          <!-- System afiliacyjny trustisto -->
          <div class="Sledzenie SledzenieUkryte">
          
            <form action="integracje/konfiguracja_afiliacja.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="trustistoForm" class="cmxform"> 
            
              <div>
                <input type="hidden" name="akcja" value="zapisz" />
                <input type="hidden" name="system" value="trustisto" />
              </div>
              
              <div class="ObramowanieForm">
              
                  <table>
                  
                    <tr class="DivNaglowek">
                      <td colspan="2">Marketing Automation Trustisto</td>
                    </tr>
                    
                    <tr><td colspan="2" class="SledzenieOpis">
                      <div>Wykorzystaj Marketing Automation od Trustisto, zwiększ sprzedaż i powracalność klientów. Dzięki Trustisto stworzysz interaktywne powiadomienia socialproof, popupy, paski informacyjne które poprawią skuteczność Twojego sklepu, a także uratujesz porzucone koszyki i przeprowadzić skuteczną kampanię e-mail marketingową.</div>
                      <img src="obrazki/logo/logo_trustisto.png" alt="" />
                    </td></tr>                   
                  
                    <tr class="SledzeniePozycja">
                      <td>
                        <label>Integracja aktywna:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_TRUSTISTO_WLACZONY']['1'], $parametr['INTEGRACJA_TRUSTISTO_WLACZONY']['0'], 'integracja_trustisto_wlaczony', $parametr['INTEGRACJA_TRUSTISTO_WLACZONY']['2'], '', $parametr['INTEGRACJA_TRUSTISTO_WLACZONY']['3'] );
                        ?>
                      </td>
                    </tr>
                    
                    <tr class="SledzeniePozycja">
                      <td>
                        <label class="required" for="integracja_trustisto_kodwitryny">Kod witryny:</label>
                      </td>
                      <td>
                        <?php
                        echo '<input type="text" name="integracja_trustisto_kodwitryny" id="integracja_trustisto_kodwitryny" value="'.$parametr['INTEGRACJA_TRUSTISTO_KODWITRYNY']['0'].'" size="53" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_TRUSTISTO_KODWITRYNY']['2'].'</b></em>';
                        ?>
                      </td>
                    </tr>
                    
                    <tr>
                      <td colspan="2">
                        <div class="przyciski_dolne">
                          <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'trustisto' ? $wynik : '' ); ?>
                        </div>
                      </td>
                    </tr>
                    
                  </table>

              </div>
            </form>
          </div>
          
          
          <!-- System opinii Zaufane.pl -->
          <div class="Sledzenie SledzenieUkryte">
          
            <form action="integracje/konfiguracja_afiliacja.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="zaufaneplForm" class="cmxform"> 
            
              <div>
                <input type="hidden" name="akcja" value="zapisz" />
                <input type="hidden" name="system" value="zaufanepl" />
              </div>
              
              <div class="ObramowanieForm">
              
                  <table>
                  
                    <tr class="DivNaglowek">
                      <td colspan="2">Program opinii Zaufane.pl</td>
                    </tr>
                    
                    <tr><td colspan="2" class="SledzenieOpis">
                      <div>Zaufane.pl to nowa jakość marketingu opinii w Polsce! Unikalne metody i zaawansowana strategia pozyskiwania recenzji oraz status oficjalnego Partnera Google ds. opinii pozwoliły naszym klientem wznieść się na nowy poziom reputacji online, zwiększając zaufanie i sprzedaż. Opinie w końcu są prezentowane tam gdzie najczęściej przebywają Twoi klienci oraz posiadasz do nich wszelkie niezbędne prawa!</div>
                      <img src="obrazki/logo/logo_zaufanepl.png" alt="" />
                    </td></tr>                   
                  
                    <tr class="SledzeniePozycja">
                      <td>
                        <label>Włącz integrację:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_ZAUFANEPL_WLACZONY']['1'], $parametr['INTEGRACJA_ZAUFANEPL_WLACZONY']['0'], 'integracja_zaufanepl_wlaczony', $parametr['INTEGRACJA_ZAUFANEPL_WLACZONY']['2'], '', $parametr['INTEGRACJA_ZAUFANEPL_WLACZONY']['3'] );
                        ?>
                      </td>
                    </tr>
                    
                    <tr class="SledzeniePozycja">
                      <td>
                        <label for="integracja_zaufanepl_status_zamowien">Status zamówień eksportowanych do Zaufane.pl:</label>
                      </td>
                      <td>
                        <?php
                        $default = $parametr['INTEGRACJA_ZAUFANEPL_STATUS_ZAMOWIEN']['0'];
                        $tablicaStatusow = Sprzedaz::ListaStatusowZamowien(false, '');
                        foreach ( $tablicaStatusow as $tablicaStatus ) {
                            echo '<input type="checkbox" value="' . $tablicaStatus['id'] . '" name="integracja_zaufanepl_status_zamowien[]" id="integracja_zaufanepl_status_zamowien_' . $tablicaStatus['id'] . '" ' . ((in_array((string)$tablicaStatus['id'], explode(',', (string)$parametr['INTEGRACJA_ZAUFANEPL_STATUS_ZAMOWIEN']['0']))) ? 'checked="checked" ' : '') . ' /><label class="OpisFor" for="integracja_zaufanepl_status_zamowien_' . $tablicaStatus['id'] . '">' . $tablicaStatus['text'] . '</label><br />';
                        }              
                        //echo Funkcje::RozwijaneMenu('integracja_zaufanepl_status_zamowien', $tablica, $default,' id="integracja_zaufanepl_status_zamowien" style="width: 300px;"'); 
                        ?>
                      </td>
                    </tr>                    

                    <tr class="SledzeniePozycja">
                      <td>
                        <label for="integracja_zaufanepl_status_czas">Status dodany w ciągu ostatnich ilu godzin:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_ZAUFANEPL_STATUS_CZAS']['1'], $parametr['INTEGRACJA_ZAUFANEPL_STATUS_CZAS']['0'], 'integracja_zaufanepl_status_czas', $parametr['INTEGRACJA_ZAUFANEPL_STATUS_CZAS']['2'], '', $parametr['INTEGRACJA_ZAUFANEPL_STATUS_CZAS']['3'], '', '', 'id="integracja_zaufanepl_status_czas"' );
                        ?>
                      </td>
                    </tr>                    
                    
                             
                    <tr>
                      <td colspan="2" class="NaglowekAfiliacja">
                        <strong>Automatyczne generowanie pliku CSV i przesylanie FTP do Zaufane.pl</strong>
                        <span class="maleInfo">
                            Podany poniżej link można użyć do generowania pliku dla Zaufane.pl poza sklepem - bezpośrednio z poziomu przeglądarki. Można go również użyć do cyklicznego wykonywania w zadaniach CRON na serwerze. Podanego skryptu nie można dodać do Harmonogramu zadań w sklepie (menu Narzędzia) ponieważ spowoduje to zablokowanie działania sklepu
                        </span>
                        <p>
                            <label>Ścieżka pliku generowania pliku poza sklepem:</label>
                            <span id="SciezkaCron" data-link="<?php echo ADRES_URL_SKLEPU . '/zaufanepl_csv.php'; ?>"><?php echo ADRES_URL_SKLEPU . '/zaufanepl_csv.php?token=' . $parametr['INTEGRACJA_ZAUFANEPL_TOKEN']['0']; ?></span>
                        </p>   
                      </td>
                    </tr>                     
                    
                    <tr class="SledzeniePozycja">
                      <td>
                        <label>Czy umożliwić generowanie poprzez zewnętrzny link:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_ZAUFANEPL_CRON']['1'], $parametr['INTEGRACJA_ZAUFANEPL_CRON']['0'], 'integracja_zaufanepl_cron', $parametr['INTEGRACJA_ZAUFANEPL_CRON']['2'], '', $parametr['INTEGRACJA_ZAUFANEPL_CRON']['3'] );
                        ?>
                      </td>
                    </tr>

                    <tr class="SledzeniePozycja">
                      <td>
                        <label for="integracja_zaufanepl_token">Token pliku:</label>
                      </td>
                      <td>
                        <?php
                        echo '<input type="text" name="integracja_zaufanepl_token" id="integracja_zaufanepl_token" value="'.$parametr['INTEGRACJA_ZAUFANEPL_TOKEN']['0'].'" size="53" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_ZAUFANEPL_TOKEN']['2'].'</b></em>';
                        ?>
                      </td>
                    </tr>                    
                    
                    <tr>
                      <td colspan="2" class="NaglowekAfiliacja">
                        <strong>Dane do serwera FTP Zaufane.pl</strong>
                        <span class="maleInfo">
                            Należy wprowadzić dane do połączenia z FTP-em serwisu Zaufane.pl na który będzie przesyłany plik CSV z zamówieniami ze sklepu. Dane te należy uzyskać w serwisie Zaufane.pl
                        </span>
                      </td>
                    </tr>                     
                    
                    <tr class="SledzeniePozycja">
                      <td>
                        <label>Czy wygenerowany plik ma być wysyłany na FTP Zaufane.pl:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_ZAUFANEPL_FTP']['1'], $parametr['INTEGRACJA_ZAUFANEPL_FTP']['0'], 'integracja_zaufanepl_ftp', $parametr['INTEGRACJA_ZAUFANEPL_FTP']['2'], '', $parametr['INTEGRACJA_ZAUFANEPL_FTP']['3'] );
                        ?>
                      </td>
                    </tr>

                    <tr class="SledzeniePozycja">
                      <td>
                        <label for="integracja_zaufanepl_ftphost">Adres serwera FTP:</label>
                      </td>
                      <td>
                        <?php
                        echo '<input type="text" name="integracja_zaufanepl_ftphost" id="integracja_zaufanepl_ftphost" value="'.$parametr['INTEGRACJA_ZAUFANEPL_FTPHOST']['0'].'" size="53" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_ZAUFANEPL_FTPHOST']['2'].'</b></em>';
                        ?>
                      </td>
                    </tr>                    

                    <tr class="SledzeniePozycja">
                      <td>
                        <label for="integracja_zaufanepl_ftplogin">Login do serwera FTP:</label>
                      </td>
                      <td>
                        <?php
                        echo '<input type="text" name="integracja_zaufanepl_ftplogin" id="integracja_zaufanepl_ftplogin" value="'.$parametr['INTEGRACJA_ZAUFANEPL_FTPLOGIN']['0'].'" size="53" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_ZAUFANEPL_FTPLOGIN']['2'].'</b></em>';
                        ?>
                      </td>
                    </tr>                    

                    <tr class="SledzeniePozycja">
                      <td>
                        <label for="integracja_zaufanepl_ftphaslo">Hasło do serwera FTP:</label>
                      </td>
                      <td>
                        <?php
                        echo '<input type="password" name="integracja_zaufanepl_ftphaslo" id="integracja_zaufanepl_ftphaslo" value="'.$parametr['INTEGRACJA_ZAUFANEPL_FTPHASLO']['0'].'" size="53" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_ZAUFANEPL_FTPHASLO']['2'].'</b></em>';
                        ?>
                      </td>
                    </tr>                    
                    
                    
                    
                    <tr>
                      <td colspan="2">
                        <div class="przyciski_dolne">
                          <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'zaufanepl' ? $wynik : '' ); ?>
                        </div>
                      </td>
                    </tr>
                    
                  </table>

              </div>
            </form>
          </div>          

 
           <!-- System afiliacyjny TrustPilot -->
          <div class="Sledzenie SledzenieUkryte">
          
            <form action="integracje/konfiguracja_afiliacja.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="trustpilotForm" class="cmxform"> 
            
              <div>
                <input type="hidden" name="akcja" value="zapisz" />
                <input type="hidden" name="system" value="trustpilot" />
              </div>
              
              <div class="ObramowanieForm">
              
                  <table>
                  
                    <tr class="DivNaglowek">
                      <td colspan="2">Program recenzji TrustPilot</td>
                    </tr>
                    
                    <tr><td colspan="2" class="SledzenieOpis">
                      <div>Czujemy się zobowiązani do bycia najbardziej zaufaną platformą z recenzjami recenzentów na świecie</div>
                      <img src="obrazki/logo/logo_trustpilot.png" alt="" />
                    </td></tr>                   
                  
                    <tr class="SledzeniePozycja">
                      <td>
                        <label>Integracja aktywna:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_TRUSTPILOT_WLACZONY']['1'], $parametr['INTEGRACJA_TRUSTPILOT_WLACZONY']['0'], 'integracja_trustpilot_wlaczony', $parametr['INTEGRACJA_TRUSTPILOT_WLACZONY']['2'], '', $parametr['INTEGRACJA_TRUSTPILOT_WLACZONY']['3'] );
                        ?>
                      </td>
                    </tr>
                    
                    <tr class="SledzeniePozycja">
                      <td>
                        <label for="integracja_trustpilot_key">Klucz do integracji:</label>
                      </td>
                      <td>
                        <?php
                        echo '<input type="text" name="integracja_trustpilot_key" id="integracja_trustpilot_key" value="'.$parametr['INTEGRACJA_TRUSTPILOT_KEY']['0'].'" size="53" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_TRUSTPILOT_KEY']['2'].'</b></em>';
                        ?>
                      </td>
                    </tr>                    

                    <tr>
                      <td colspan="2">
                        <div class="przyciski_dolne">
                          <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'trustpilot' ? $wynik : '' ); ?>
                        </div>
                      </td>
                    </tr>
                    
                  </table>

              </div>
            </form>
          </div>
          
          
           <!-- System Callback24 -->
          <div class="Sledzenie SledzenieUkryte">
          
            <form action="integracje/konfiguracja_afiliacja.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="callback24Form" class="cmxform"> 
            
              <div>
                <input type="hidden" name="akcja" value="zapisz" />
                <input type="hidden" name="system" value="callback24" />
              </div>
              
              <div class="ObramowanieForm">
              
                  <table>
                  
                    <tr class="DivNaglowek">
                      <td colspan="2">Callback24 - narzędzie ułatwiające telefoniczny</td>
                    </tr>
                    
                    <tr><td colspan="2" class="SledzenieOpis">
                      <div>Callback24 to inteligentne narzędzie ułatwiające telefoniczny kontakt z Twoim klientem – dostępne zupełnie za darmo! Wypróbuj narzędzie Callback24 za darmo!<br /><a class="przyciskLink" href="https://panel.callback24.io/partners/invite/1283?utm_source=PP&utm_medium=studiokomputerowekamelianet" target="_blank">ZAŁÓŻ DARMOWE KONTO</a></div>
                      <img src="obrazki/logo/logo_callback24.png" alt="" />
                    </td></tr>                   
                  
                    <tr class="SledzeniePozycja">
                      <td>
                        <label>Integracja aktywna:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_CALLBACK24_WLACZONY']['1'], $parametr['INTEGRACJA_CALLBACK24_WLACZONY']['0'], 'integracja_callback24_wlaczony', $parametr['INTEGRACJA_CALLBACK24_WLACZONY']['2'], '', $parametr['INTEGRACJA_CALLBACK24_WLACZONY']['3'] );
                        ?>
                      </td>
                    </tr>
                    
                    <tr class="SledzeniePozycja">
                      <td>
                        <label class="required"  for="integracja_callback24_kod">Kod wyświetlający widget:</label>
                      </td>
                      <td>
                        <?php
                        echo '<textarea cols="110" rows="5" id="integracja_callback24_kod" name="integracja_callback24_kod">'.$parametr['INTEGRACJA_CALLBACK24_KOD']['0'].'</textarea><em class="TipIkona"><b>'. $parametr['INTEGRACJA_CALLBACK24_KOD']['2'].'</b></em>';
                        ?>
                      </td>
                    </tr>                    

                    <tr>
                      <td colspan="2">
                        <div class="przyciski_dolne">
                          <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'callback24' ? $wynik : '' ); ?>
                        </div>
                      </td>
                    </tr>
                    
                  </table>

              </div>
            </form>
          </div>
          
          
          <!-- System afiliacyjny shopeneo Openrate -->
          <div class="Sledzenie SledzenieUkryte">
          
            <form action="integracje/konfiguracja_afiliacja.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="shopeneoForm" class="cmxform"> 
            
              <div>
                <input type="hidden" name="akcja" value="zapisz" />
                <input type="hidden" name="system" value="shopeneo" />
              </div>
              
              <div class="ObramowanieForm">
              
                  <table>
                  
                    <tr class="DivNaglowek">
                      <td colspan="2">Program afiliacyjny shopeneo.network</td>
                    </tr>
                    
                    <tr><td colspan="2" class="SledzenieOpis">
                      <div>Płacisz prowizję tylko wtedy, gdy Twój produkt zostaje sprzedany poprzez program partnerski. Oferujemy szeroki dostęp do wydawców co gwarantuje promocję Twoich produktów w miejscach gdzie są Twoi klienci. W ramach społeczności afiliacyjnej stawiamy nacisk na wysokiej jakości ruch.</div>
                      <img src="obrazki/logo/logo_shopeneo.png" alt="" />
                    </td></tr>                   
                  
                    <tr class="SledzeniePozycja">
                      <td>
                        <label>Włącz integrację:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_SHOPENEO_WLACZONY']['1'], $parametr['INTEGRACJA_SHOPENEO_WLACZONY']['0'], 'integracja_shopeneo_wlaczony', $parametr['INTEGRACJA_SHOPENEO_WLACZONY']['2'], '', $parametr['INTEGRACJA_SHOPENEO_WLACZONY']['3'] );
                        ?>
                      </td>
                    </tr>
                    
                    <tr>
                      <td colspan="2">
                        <div class="przyciski_dolne">
                          <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'shopeneo' ? $wynik : '' ); ?>
                        </div>
                      </td>
                    </tr>
                    
                  </table>

              </div>
            </form>
          </div>          
          
           <!-- System Trustmate.io -->
          <div class="Sledzenie SledzenieUkryte">
          
            <form action="integracje/konfiguracja_afiliacja.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="trustmateForm" class="cmxform"> 
            
              <div>
                <input type="hidden" name="akcja" value="zapisz" />
                <input type="hidden" name="system" value="trustmate" />
              </div>
              
              <div class="ObramowanieForm">
              
                  <table>
                  
                    <tr class="DivNaglowek">
                      <td colspan="2">Trustmate.io - Opinie klientów w atrakcyjnych widgetach</td>
                    </tr>
                    
                    <tr><td colspan="2" class="SledzenieOpis">
                      <div>TrustMate to narzędzia umożliwiające budowanie wizerunku w sieci poprzez zarządzanie opiniami o prowadzonym sklepie internetowym lub firmie usługowej. W skład wchodzą: mediacje, oceny szczegółowe, ankiety, raporty, statystyki, alarmy, zaproszenia do wystawienia opinii i inne.</div>
                      <img style="height: 35px;" src="obrazki/logo/logo-trustmate.svg" alt="" />
                    </td></tr>                   
                  
                    <tr class="SledzeniePozycja">
                      <td>
                        <label>Integracja aktywna:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_TRUSTMATE_WLACZONY']['1'], $parametr['INTEGRACJA_TRUSTMATE_WLACZONY']['0'], 'integracja_trustmate_wlaczony', $parametr['INTEGRACJA_TRUSTMATE_WLACZONY']['2'], '', $parametr['INTEGRACJA_TRUSTMATE_WLACZONY']['3'] );
                        ?>
                      </td>
                    </tr>
                    
                    <tr class="SledzeniePozycja">
                      <td>
                        <label for="integracja_trustpilot_key">Klucz API do integracji:</label>
                      </td>
                      <td>
                        <?php
                        echo '<input type="text" name="integracja_trustmate_key" id="integracja_trustmate_key" value="'.$parametr['INTEGRACJA_TRUSTMATE_KEY']['0'].'" size="53" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_TRUSTMATE_KEY']['2'].'</b></em>';
                        ?>
                      </td>
                    </tr>                    

                    <tr>
                      <td colspan="2" class="NaglowekAfiliacja">
                        <strong>Wdigety firmowe</strong>
                        <span class="maleInfo">
                            Dostępne do włączenia widgety firmowe. Widgety należy skonfigurować w serwisie Trustmate.io i w ustawieniach wkleic ID widgetu, które jest dostępne w wygenerowanym kodzie.
                        </span>
                      </td>
                    </tr>

                    <tr class="SledzeniePozycja">
                      <td>
                        <label>MUSKRAT:</label>
                      </td>
                      <td>
                        <?php
                            $wartosci = array();
                            $wartosci = explode('|',$parametr['INTEGRACJA_TRUSTMATE_WIDGET_MUSKART_ID']['0']);
                        ?>
                        <input type="checkbox" value="1" name="integracja_trustmate_widget_muskart" id="integracja_trustmate_widget_muskart" <?php echo (isset($wartosci['0']) && $wartosci['0'] == '1' ? 'checked="checked"' : '' ); ?> style="border: 0px;"><label class="OpisFor" for="integracja_trustmate_widget_muskart" style="min-width:120px;"><?php echo ($wartosci['0'] && $wartosci['0'] == '1' ? 'wyłącz' : 'włącz' ); ?></label>
                        <?php
                        echo '<input type="text" name="integracja_trustmate_widget_muskart_id" id="integracja_trustmate_widget_muskart_id" value="'.( isset($wartosci['1']) ? $wartosci['1'] : '' ).'" size="53" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_TRUSTMATE_WIDGET_MUSKART_ID']['2'].'</b></em>';
                        unset($wartosci);
                        ?>
                      </td>
                    </tr>
                    <tr class="SledzeniePozycja">
                      <td>
                        <label>LEMUR:</label>
                      </td>
                      <td>
                        <?php
                            $wartosci = array();
                            $wartosci = explode('|',$parametr['INTEGRACJA_TRUSTMATE_WIDGET_LEMUR_ID']['0']);
                        ?>
                        <input type="checkbox" value="1" name="integracja_trustmate_widget_lemur" id="integracja_trustmate_widget_lemur" <?php echo (isset($wartosci['0']) && $wartosci['0'] == '1' ? 'checked="checked"' : '' ); ?> style="border: 0px;"><label class="OpisFor" for="integracja_trustmate_widget_lemur" style="min-width:120px;"><?php echo (isset($wartosci['0']) && $wartosci['0'] == '1' ? 'wyłącz' : 'włącz' ); ?></label>
                        <?php
                        echo '<input type="text" name="integracja_trustmate_widget_lemur_id" id="integracja_trustmate_widget_lemur_id" value="'.( isset($wartosci['1']) ? $wartosci['1'] : '' ).'" size="53" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_TRUSTMATE_WIDGET_LEMUR_ID']['2'].'</b></em>';
                        unset($wartosci);
                        ?>
                      </td>
                    </tr>
                    <tr class="SledzeniePozycja">
                      <td>
                        <label>DODO:</label>
                      </td>
                      <td>
                        <?php
                            $wartosci = array();
                            $wartosci = explode('|',$parametr['INTEGRACJA_TRUSTMATE_WIDGET_DODO_ID']['0']);
                        ?>
                        <input type="checkbox" value="1" name="integracja_trustmate_widget_dodo" id="integracja_trustmate_widget_dodo" <?php echo (isset($wartosci['0']) && $wartosci['0'] == '1' ? 'checked="checked"' : '' ); ?> style="border: 0px;"><label class="OpisFor" for="integracja_trustmate_widget_dodo" style="min-width:120px;"><?php echo (isset($wartosci['0']) && $wartosci['0'] == '1' ? 'wyłącz' : 'włącz' ); ?></label>
                        <?php
                        echo '<input type="text" name="integracja_trustmate_widget_dodo_id" id="integracja_trustmate_widget_dodo_id" value="'.( isset($wartosci['1']) ? $wartosci['1'] : '' ).'" size="53" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_TRUSTMATE_WIDGET_DODO_ID']['2'].'</b></em>';
                        unset($wartosci);
                        ?>
                      </td>
                    </tr>
                    <tr>
                      <td colspan="2" class="NaglowekAfiliacja">
                        <strong>Wdigety produktowe</strong>
                        <span class="maleInfo">
                            Dostępne do włączenia widgety produktowe. Widgety należy skonfigurować w serwisie Trustmate.io i w ustawieniach wkleic ID widgetu, które jest dostępne w wygenerowanym kodzie.
                        </span>
                      </td>
                    </tr>

                    <tr class="SledzeniePozycja">
                      <td>
                        <label>BADGER:</label>
                      </td>
                      <td>
                        <?php
                            $wartosci = array();
                            $wartosci = explode('|',$parametr['INTEGRACJA_TRUSTMATE_WIDGET_BADGER_ID']['0']);
                        ?>
                        <input type="checkbox" value="1" name="integracja_trustmate_widget_badger" id="integracja_trustmate_widget_badger" <?php echo (isset($wartosci['0']) && $wartosci['0'] == '1' ? 'checked="checked"' : '' ); ?> style="border: 0px;"><label class="OpisFor" for="integracja_trustmate_widget_badger" style="min-width:120px;"><?php echo (isset($wartosci['0']) && $wartosci['0'] == '1' ? 'wyłącz' : 'włącz' ); ?></label>
                        <?php
                        echo '<input type="text" name="integracja_trustmate_widget_badger_id" id="integracja_trustmate_widget_badger_id" value="'.( isset($wartosci['1']) ? $wartosci['1'] : '' ).'" size="53" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_TRUSTMATE_WIDGET_BADGER_ID']['2'].'</b></em>';
                        unset($wartosci);
                        ?>
                      </td>
                    </tr>

                    <tr class="SledzeniePozycja">
                      <td>
                        <label>PRODUCTFERRET:</label>
                      </td>
                      <td>
                        <?php
                            $wartosci = array();
                            $wartosci = explode('|',$parametr['INTEGRACJA_TRUSTMATE_WIDGET_FERRET_ID']['0']);
                        ?>
                        <input type="checkbox" value="1" name="integracja_trustmate_widget_ferret" id="integracja_trustmate_widget_ferret" <?php echo (isset($wartosci['0']) && $wartosci['0'] == '1' ? 'checked="checked"' : '' ); ?> style="border: 0px;"><label class="OpisFor" for="integracja_trustmate_widget_ferret" style="min-width:120px;"><?php echo (isset($wartosci['0']) && $wartosci['0'] == '1' ? 'wyłącz' : 'włącz' ); ?></label>
                        <?php
                        echo '<input type="text" name="integracja_trustmate_widget_ferret_id" id="integracja_trustmate_widget_ferret_id" value="'.( isset($wartosci['1']) ? $wartosci['1'] : '' ).'" size="53" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_TRUSTMATE_WIDGET_FERRET_ID']['2'].'</b></em>';
                        unset($wartosci);
                        ?>
                      </td>
                    </tr>

                    <tr class="SledzeniePozycja">
                      <td>
                        <label>HORNET:</label>
                      </td>
                      <td>
                        <?php
                            $wartosci = array();
                            $wartosci = explode('|',$parametr['INTEGRACJA_TRUSTMATE_WIDGET_HORNET_ID']['0']);
                        ?>
                        <input type="checkbox" value="1" name="integracja_trustmate_widget_hornet" id="integracja_trustmate_widget_hornet" <?php echo (isset($wartosci['0']) && $wartosci['0'] == '1' ? 'checked="checked"' : '' ); ?> style="border: 0px;"><label class="OpisFor" for="integracja_trustmate_widget_hornet" style="min-width:120px;"><?php echo (isset($wartosci['0']) && $wartosci['0'] == '1' ? 'wyłącz' : 'włącz' ); ?></label>
                        <?php
                        echo '<input type="text" name="integracja_trustmate_widget_hornet_id" id="integracja_trustmate_widget_hornet_id" value="'.( isset($wartosci['1']) ? $wartosci['1'] : '' ).'" size="53" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_TRUSTMATE_WIDGET_HORNET_ID']['2'].'</b></em>';
                        unset($wartosci);
                        ?>
                      </td>
                    </tr>

                    <tr class="SledzeniePozycja">
                      <td>
                        <label>MULTIHORNET:</label>
                      </td>
                      <td>
                        <?php
                            $wartosci = array();
                            $wartosci = explode('|',$parametr['INTEGRACJA_TRUSTMATE_WIDGET_MULTIHORNET_ID']['0']);
                        ?>
                        <input type="checkbox" value="1" name="integracja_trustmate_widget_multihornet" id="integracja_trustmate_widget_multihornet" <?php echo (isset($wartosci['0']) && $wartosci['0'] == '1' ? 'checked="checked"' : '' ); ?> style="border: 0px;"><label class="OpisFor" for="integracja_trustmate_widget_multihornet" style="min-width:120px;"><?php echo (isset($wartosci['0']) && $wartosci['0'] == '1' ? 'wyłącz' : 'włącz' ); ?></label>
                        <?php
                        echo '<input type="text" name="integracja_trustmate_widget_multihornet_id" id="integracja_trustmate_widget_multihornet_id" value="'.( isset($wartosci['1']) ? $wartosci['1'] : '' ).'" size="53" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_TRUSTMATE_WIDGET_MULTIHORNET_ID']['2'].'</b></em>';
                        unset($wartosci);
                        ?>
                      </td>
                    </tr>
                    
                    <tr>
                      <td colspan="2">
                        <div class="przyciski_dolne">
                          <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'trustmate' ? $wynik : '' ); ?>
                        </div>
                      </td>
                    </tr>
                    
                  </table>

              </div>
            </form>
          </div>
          


          <?php
          /*
          // aktualnie nieuzywane
          
          <!-- chceto -->
          <div class="Sledzenie SledzenieUkryte">
          
            <form action="integracje/konfiguracja_afiliacja.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="chcetoForm" class="cmxform">
            
              <div>
                <input type="hidden" name="akcja" value="zapisz" />
                <input type="hidden" name="system" value="chceto" />
              </div>
              
              <div class="ObramowanieForm">
              
                  <table>
                  
                    <tr class="DivNaglowek">
                      <td colspan="2">Chce.to - odnośnik, który umożliwia jednym kliknięciem dodawać produkty na swoje chcelisty.</td>
                    </tr>
                    
                    <tr><td colspan="2" class="SledzenieOpis">
                      <div>Chce.to - serwis internetowy umożliwiający tworzenie chcelist, czyli list rzeczy, jakie chcemy mieć. Serwis ma na celu ułatwienie użytkownikom prostą z pozoru czynność dawania i dostawania prezentów.</div>
                      <img src="obrazki/logo/logo_chceto.png" alt="" />
                    </td></tr>                  
                  
                    <tr class="SledzeniePozycja">
                      <td>
                        <label>Włącz przycisk +chce.to:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_CHCE_TO_WLACZONY']['1'], $parametr['INTEGRACJA_CHCE_TO_WLACZONY']['0'], 'integracja_chce_to_wlaczony', $parametr['INTEGRACJA_CHCE_TO_WLACZONY']['2'], '', $parametr['INTEGRACJA_CHCE_TO_WLACZONY']['3'] );
                        ?>
                      </td>
                    </tr>

                    <tr>
                      <td colspan="2">
                        <div class="przyciski_dolne">
                          <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'chceto' ? $wynik : '' ); ?>
                        </div>
                      </td>
                    </tr>
                  </table>

              </div>
            </form>
            
          </div>      
          */
          ?>
        
        </div>
      </div>
    </div>

    
    <?php
    include('stopka.inc.php');    
    
} ?>
