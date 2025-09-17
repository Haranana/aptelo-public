<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    $cron_status = CRON_EKSPORT_XML;
    $cron_token = CRON_EKSPORT_XML_TOKEN;
    $nazwa_pliku = CRON_EKSPORT_XML_PLIK;

    $wynik = '';

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        $pola = array( array('status',0) );
        $db->update_query('export_configuration', $pola, "code != ''");
        unset($pola);
        
        foreach ($_POST as $klucz => $wartosc) {
            //
            if ( $klucz != 'cron_status' && $klucz != 'cron_token' && $klucz != 'nazwa_pliku' ) {
                $pola = array( array('status',1) );
                $db->update_query('export_configuration', $pola, "code = '" . $klucz . "'");
                unset($pola);
            }
            if ( $klucz == 'cron_status' ) {
                $cron_status = $wartosc;
                $pola = array( array('value',$wartosc) );
                $db->update_query('settings', $pola, "code = 'CRON_EKSPORT_XML'");
                unset($pola);
            }
            if ( $klucz == 'cron_token' ) {
                $cron_token = $wartosc;
                $pola = array( array('value',$wartosc) );
                $db->update_query('settings', $pola, "code = 'CRON_EKSPORT_XML_TOKEN'");
                unset($pola);
            }
            if ( $klucz == 'nazwa_pliku' ) {
                $nazwa_pliku = $wartosc;
                $pola = array( array('value',$wartosc) );
                $db->update_query('settings', $pola, "code = 'CRON_EKSPORT_XML_PLIK'");
                unset($pola);
            }
            //
        }
        
        $wynik = '<div id="zapisano" class="maleSukces" style="margin-left:20px;margin-top:10px;">dane zostały zmienione</div>';

    }   

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>

    <div id="naglowek_cont">Konfiguracja eksportu produktów w formacie CSV oraz XML</div>
    <div id="cont">

      <div class="poleForm">
      
        <div class="naglowek">Edycja danych</div>

        <div class="pozycja_edytowana"> 

            <script>
            $(document).ready(function() {
            
              jQuery.validator.addMethod("lettersonly", function(value, element) {
                return this.optional(element) || /^[a-z]+$/i.test(value);
              }, "Można wprowadzić tylko litery");         
            
              $('#cron_token').on("keyup",function() {
                 $('#SciezkaCron').html( $('#SciezkaCron').attr('data-link') + '?token=' + $(this).val() );
              });
              
              $("#exportkonfForm").validate({
                rules: {
                  cron_token: {required: function() {var wynik = true; if ( $("input[name='cron_status']:checked", "#exportkonfForm").val() == "0" ) { wynik = false; } return wynik; }, lettersonly: true }
                }
              });                      
          });                  
          function AktywnyCron(tryb) {
              if ( tryb == 1 ) {
                   $('.EksportCronInfo').slideDown();
                } else {
                   $('.EksportCronInfo').slideUp();
              }
          };
          </script> 

            <script>
            $(document).ready(function() {
              setTimeout(function() {
                $('#zapisano').fadeOut();
              }, 3000);
            });
            </script>         
        
            
            <div class="Export">
            
                <form action="import_danych/konfiguracja_exportu.php" method="post" id="exportkonfForm" class="cmxform">
                
                <input type="hidden" value="zapisz" name="akcja" />

                <?php
                $zapytanie = "select distinct * from export_configuration order by description";
                $sql = $db->open_query($zapytanie);
                
                $suma_pozycji = (int)$db->ile_rekordow($sql) / 2;
                $licznik = 0;
                
                $konfiguracja_eksportu = '';
                $konfiguracja_zdjec = '';

                ?>

                <div class="EksportCron">
                  
                    <p style="padding-left:0">
                        <label>Czy umożliwić generowanie poprzez zewnętrzny link:</label>
                        <input type="radio" value="0" name="cron_status" onclick="AktywnyCron(0)" id="cron_status_nie" <?php echo ($cron_status == '0' ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="cron_status_nie">nie</label>
                        <input type="radio" value="1" name="cron_status" onclick="AktywnyCron(1)" id="cron_status_tak" <?php echo ($cron_status == '1' ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="cron_status_tak">tak</label>
                    </p>        

                    <div class="EksportCronInfo" <?php echo ($cron_status == '0' ? ' style="display:none"' : ''); ?>  style="border-bottom:1px dashed #dbdbdb;margin-bottom:10px;padding-bottom:10px">
                      
                        <p>
                          <label>Token pliku:</label>
                          <input type="text" value="<?php echo $cron_token; ?>" name="cron_token" id="cron_token" size="15" /> <em class="TipIkona"><b>Token (ciąg znaków z liter) do zabezpieczenia generowania pliku przez osoby nieupoważnione</b></em>
                        </p>   

                        <p>
                          <label>Ścieżka pliku generowania pliku poza sklepem:</label>
                          <span id="SciezkaCron" data-link="<?php echo ADRES_URL_SKLEPU . '/export_xml.php'; ?>"><?php echo ADRES_URL_SKLEPU . '/export_xml.php?token=' . $cron_token; ?></span>
                        </p>   

                        <p>
                          <label for="nazwa_pliku">Nazwa pliku wynikowego (bez rozszerzenia):</label>
                          <input type="text" size="50" name="nazwa_pliku" id="nazwa_pliku" value="<?php echo $nazwa_pliku; ?>" /> <em class="TipIkona"><b>Jeżeli nie zostanie podana nazwa, to plik bedzie mial postać export_xml_BIEZACA_DATA.xml</b></em>
                        </p>   
                
                        <div class="maleInfo" style="margin-left:0">
                            Podany powyżej link można użyć do generowania pliku XML z produktami poza sklepem - bezpośrednio z poziomu przeglądarki. Można go również użyć do cyklicznego wykonywania w zadaniach CRON na serwerze. 
                            Podanego skryptu <b>nie</b> można dodać do Harmonogramu zadań w sklepie (menu Narzędzia) ponieważ spowoduje to zablokowanie działania sklepu.
                         </div>
                      
                    </div>
                  
                </div>

                <div class="maleInfo" style="margin-left:0;margin-bottom:10px;padding-bottom:10px">Zaznacz pozycje które mają być eksportowane dla plików CSV oraz XML</div>

                <?php
                while ( $info = $sql->fetch_assoc() ) {
                
                    if ( $info['code'] != 'Zdjecia_url' ) {
                      
                        //
                        $konfiguracja_eksportu .= '<li><input type="checkbox" name="' . $info['code'] . '" id="' . $info['code'] . '" value="1" ' . (($info['status'] == 1) ? 'checked="checked"' : '') . '/><label class="OpisFor" for="' . $info['code'] . '">' . $info['description'] . '</label></li>';
                        //

                        if ( $licznik == (int)$suma_pozycji ) {
                             $konfiguracja_eksportu .= '</ul></div><div class="lf"><ul>';
                        }
                        
                        $licznik ++;     

                    } else {
                      
                        $konfiguracja_zdjec = '<div style="border-bottom:1px dashed #dbdbdb;margin-bottom:10px;padding-bottom:10px">';
                        
                        $konfiguracja_zdjec .= '<input type="checkbox" name="' . $info['code'] . '" id="' . $info['code'] . '" value="1" ' . (($info['status'] == 1) ? 'checked="checked"' : '') . '/><label class="OpisFor" for="' . $info['code'] . '">' . $info['description'] . '</label>';
                        
                        $konfiguracja_zdjec .= '</div>';
                      
                    }
                
                }
                
                $db->close_query($sql);
                unset($info);         
                ?>
                
                <?php
                echo $konfiguracja_zdjec;
                ?>                
                
                <div class="lf"><ul>
                
                <?php
                echo $konfiguracja_eksportu;
                ?>
                
                </ul></div>
                
                <div class="cl"></div>
                
                <div class="przyciski_dolne" style="margin:15px 0px 0px -15px">
                  <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo $wynik; ?>
                </div>          

                </form>
           
            </div> 
        
        </div>
        
      </div>
      
    </div>
                    
    <?php include('stopka.inc.php'); ?>

<?php } ?>
