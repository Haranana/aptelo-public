<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone && ( isset($_SESSION['programista']) && $_SESSION['programista'] == '1' )) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

        $zalaczniki = '';
        if ( isset($_POST["zalacznik"]) ) {
            $zalaczniki = implode(';', (array)$filtr->process($_POST["zalacznik"]));
        }
        //
        $pola = array(
                array('email_var_id',$filtr->process($_POST["kod"])),
                array('text_name',$filtr->process($_POST["nazwa"])),
                array('sender_name',$filtr->process($_POST["nadawca_nazwa"])),
                array('sender_email',$filtr->process($_POST["nadawca_email"])),
                array('dw',$filtr->process($_POST["cc_email"])),
                array('email_group',$filtr->process($_POST["grupa"])),
                array('template_id',$filtr->process($_POST["szablon"])),
                array('email_file',$zalaczniki));
                
        //
        $db->insert_query('email_text' , $pola);
        $id_dodanej_pozycji = $db->last_id_query();
        unset($pola);

        //
        $ile_jezykow = Funkcje::TablicaJezykow();
        for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
            //
            if (!empty($_POST['edytor_'.$w])) {
                $pola = array(
                        array('email_text_id',(int)$id_dodanej_pozycji),
                        array('email_title',$filtr->process($_POST['tytul_'.$w])),
                        array('description',$filtr->process($_POST['edytor_'.$w])),
                        array('description_sms',$filtr->process($_POST['sms_'.$w])),
                        array('language_id',$ile_jezykow[$w]['id'])
                 );
            } else {
                $pola = array(
                        array('email_text_id',(int)$id_dodanej_pozycji),
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
        Funkcje::PrzekierowanieURL('emaile_tresci.php');
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Dodanie pozycji</div>
    <div id="cont">
          
          <script type="text/javascript" src="javascript/jquery.bestupper.min.js"></script>        

          <script >
          $(document).ready(function() {
             $('.bestupper').bestupper();
          });

          function updateKey() {
              var key=$("#kod").val();
              key=key.replace(" ","_");
              $("#kod").val(key);
          }

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
                },              
                kod: {
                  required: true,
                  remote: "ajax/sprawdz_czy_zmienna_szablonu_maila.php"
                }
              },
              messages: {
                kod: {
                  required: "Pole jest wymagane.",
                  remote: "Zmienna o takiej nazwie już istnieje."
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

          <form action="slowniki/emaile_tresci_dodaj.php" method="post" id="slownikForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Nowa pozycja</div>

                  <div class="pozycja_edytowana">
                  
                      <input type="hidden" name="akcja" value="zapisz" />
                      
                      <p>
                        <label class="required">Nazwa szablonu:</label>
                        <input type="text" name="nazwa" size="60" value="" id="nazwa" class="required" />
                      </p>

                      <p>
                        <label class="required">Kod:</label>
                        <input type="text" name="kod" id="kod" value="" size="60" class="bestupper" onkeyup="updateKey();" />
                      </p>   

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
                          
                              ?>
                              
                              <div id="info_tab_id_<?php echo $w; ?>" style="display:none;">
                              
                                  <p>
                                        <label <?php echo ( $w == '0' ? 'class="required"' : '' ); ?> >Tytuł emaila:</label>
                                        <input type="text" name="tytul_<?php echo $w; ?>" size="60" value="" id="tytul_<?php echo $w; ?>" />
                                  </p>

                                  <div class="edytor">
                                    <textarea cols="50" rows="30" id="edytor_<?php echo $w; ?>" name="edytor_<?php echo $w; ?>"></textarea>
                                  </div>   

                                  <p>
                                     <label>Treść wiadomości SMS:</label>
                                     <input type="text" name="sms_<?php echo $w; ?>" size="140" value="" id="sms_<?php echo $w; ?>" />
                                  </p>

                              </div>
                              <?php                    
                          }                    
                          ?>                      
                      </div>
                      
                      <script>
                      gold_tabs('0','edytor_', 300);
                      </script> 

                      <p>
                        <label class="required">Nadawca nazwa:</label>
                        <input type="text" name="nadawca_nazwa" size="60" value="{INFO_NAZWA_SKLEPU}" id="nadawca_nazwa" />
                      </p>

                      <p>
                        <label class="required">Nadawca email:</label>
                        <input type="text" name="nadawca_email" size="60" value="{INFO_EMAIL_SKLEPU}" id="nadawca_email" />
                      </p>

                      <p>
                        <label>Prześlij do wiadomości:</label>
                        <input type="text" name="cc_email" size="60" value="" id="cc_email" />
                      </p>

                      <p>
                        <label>Grupa:</label>
                        <?php
                        $tablica[] = array('id' => 'E-maile do klientów sklepu', 'text' => 'E-maile do klientów sklepu');
                        $tablica[] = array('id' => 'E-maile administratora', 'text' => 'E-maile administratora');
                        echo Funkcje::RozwijaneMenu('grupa', $tablica, '' ); 
                        unset($tablica);
                        ?>
                      </p>

                      <p>
                        <label>Szablon emaila:</label>
                        <?php
                        $tablica = Funkcje::ListaSzablonowEmail(false);
                        echo Funkcje::RozwijaneMenu('szablon', $tablica, '' ); 
                        unset($tablica);
                        ?>
                      </p>

                      <div id="wyniki">
                          <div id="wyniki1">
                            <p>
                                <label>Plik załącznika:</label>
                                <input type="text" name="zalacznik[]" size="60" ondblclick="openFileBrowser('zalacznik_1','','pobieranie')" id="zalacznik_1" value="" autocomplete="off" /><em class="TipIkona"><b>Kliknij dwukrotnie w to pole żeby otworzyć okno przeglądarki plików</b></em>
                                <em class="TipChmurka"><b>Skasuj</b><span class="UsunZalacznik" onclick="usun_zalacznik('1')"></span></em>
                                <span class="PrzegladarkaZdjec TipChmurka" onclick="openFileBrowser('zalacznik_<?php echo $l; ?>','','pobieranie')"><b>Kliknij żeby otworzyć okno przeglądarki zdjęć</b></span>
                            </p>
                          </div>
                      </div>
                          
                  </div>

                  <input value="1" type="hidden" name="ile_pol" id="ile_pol" />

                  <div style="padding:10px;padding-top:20px;padding-left:30px;">
                    <span class="dodaj" onclick="dodaj_zalacznik()" style="cursor:pointer">dodaj plik do dołączenia do maila</span>
                  </div>   

                  <div class="przyciski_dolne">
                    <input type="submit" class="przyciskNon" value="Zapisz dane" />
                    <button type="button" class="przyciskNon" onclick="cofnij('emaile_tresci','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','slowniki');">Powrót</button>   
                  </div>            

              </div>                      
            </form>
    </div>    
    
    <?php           
    include('stopka.inc.php');

}
