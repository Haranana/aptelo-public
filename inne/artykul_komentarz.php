<?php
chdir('../');           

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php'); 

if (Sesje::TokenSpr() && ((isset($_POST['id']) && (int)$_POST['id'] > 0) || isset($_POST['data']))) {  

    if ( isset($_POST['data']) ) {
      
        // rozdziela serializowane dane z ajaxa na tablice POST
        parse_str($_POST['data'], $PostTablica);
        unset($_POST['data']);
        
        $_POST = $PostTablica;
        
        if ( (isset($_POST['nick']) && !empty($_POST['nick']) && isset($_POST['komentarz']) && !empty($_POST['komentarz']) && isset($_POST['id']) && (int)$_POST['id'] > 0) && Sesje::TokenSpr()) {
          
              $Artykul = Aktualnosci::AktualnoscId( (int)$_POST['id'] );

              if (!empty($Artykul)) { 

                  if ( $Artykul['status_komentarzy'] == 'tak' ) {
              
                      $pola = array(array('newsdesk_id',(int)$_POST['id']),
                                    array('nick',$filtr->process($_POST['nick'])),
                                    array('email',( isset($_POST['email']) ? $filtr->process($_POST['email']) : '' )),
                                    array('telefon',( isset($_POST['telefon']) ? $filtr->process($_POST['telefon']) : '')),
                                    array('date_added','now()'),
                                    array('comments',$filtr->process(strip_tags((string)$_POST['komentarz']), false, true)),
                                    array('status',0));
                                    
                      $sql = $GLOBALS['db']->insert_query('newsdesk_comments', $pola);
                      unset($pola);    
                      
                      if ( KOMENTARZE_WYSLIJ_EMAIL == 'tak' ) {
                        
                          // wyslanie maila do klienta - START
                          $zapytanie_tresc = "SELECT t.sender_name, t.email_var_id, t.sender_email, t.dw, t.template_id, t.email_file, tz.email_title, tz.description, tz.description_sms FROM email_text t LEFT JOIN email_text_description tz ON tz.email_text_id = t.email_text_id AND tz.language_id = '" . (int)$_SESSION['domyslnyJezyk']['id'] . "' WHERE t.email_var_id = 'EMAIL_NAPISANY_KOMENTARZ_ARTYKULU'";

                          $sql = $GLOBALS['db']->open_query($zapytanie_tresc);
                          $tresc = $sql->fetch_assoc();    

                          define('LINK_ARTYKULU', '<a style="font-weight:bold" href="' . ADRES_URL_SKLEPU . '/zarzadzanie/aktualnosci/aktualnosci_komentarze.php?art_id=' . (int)$_POST['id'] . '">' . $Artykul['tytul'] . '</a>');
                          
                          define('NICK', $filtr->process($_POST['nick']));
                          define('ADRES_EMAIL', ( isset($_POST['email']) ? $filtr->process($_POST['email']) : '' ));
                          define('TELEFON', ( isset($_POST['telefon']) ? $filtr->process($_POST['telefon']) : ''));
                          define('KOMENTARZ', $filtr->process(strip_tags((string)$_POST['komentarz'])));

                          $email = new Mailing;

                          if ( $tresc['email_file'] != '' ) {
                              $tablicaZalacznikow = explode(';', (string)$tresc['email_file']);
                          } else {
                              $tablicaZalacznikow = array();
                          }

                          $nadawca_email   = Funkcje::parsujZmienne($tresc['sender_email']);
                          $nadawca_nazwa   = Funkcje::parsujZmienne($tresc['sender_name']);
                          $cc              = Funkcje::parsujZmienne($tresc['dw']);

                          $adresat_email   = Funkcje::parsujZmienne($tresc['sender_email']);
                          $adresat_nazwa   = Funkcje::parsujZmienne($tresc['sender_name']);

                          $temat           = Funkcje::parsujZmienne($tresc['email_title']);
                          $tekst           = $tresc['description'];
                          $zalaczniki      = $tablicaZalacznikow;
                          $szablon         = $tresc['template_id'];
                          $jezyk           = (int)$_SESSION['domyslnyJezyk']['id'];

                          $tekst = Funkcje::parsujZmienne($tekst);
                          $tekst = preg_replace("{(<br[\\s]*(>|\/>)\s*){2,}}i", "<br /><br />", (string)$tekst);

                          $email->wyslijEmail($nadawca_email,$nadawca_nazwa,$adresat_email, $adresat_nazwa, $cc, $temat, $tekst, $szablon, $jezyk, $zalaczniki);
  
                          $GLOBALS['db']->close_query($sql);

                      }

                      echo '<div id="PopUpInfo">';  

                      echo $GLOBALS['tlumacz']['DODANIE_KOMENTARZA'];

                      echo '</div>';

                      echo '<div id="PopUpPrzyciski">';
                                 
                          echo '<a href="' . $Artykul['seo'] . '" class="przycisk">' . $GLOBALS['tlumacz']['PRZYCISK_ZAMKNIJ'] . '</a>'; 

                      echo '</div>'; 

                  }
              
              }
          
        }
    
    } else {

        $GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('KLIENCI') ), $GLOBALS['tlumacz'] ); 
        
        if (((isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0') && KOMENTARZE_ZALOGOWANI_WIDOCZNOSC == 'tak') || KOMENTARZE_ZALOGOWANI_WIDOCZNOSC == 'nie') {
          
              $Artykul = Aktualnosci::AktualnoscId( (int)$_POST['id'] );

              if (!empty($Artykul)) { 

                  if ( $Artykul['status_komentarzy'] == 'tak' ) {          

                      ?>
                      <script>
                      $(document).ready(function() {
                          //
                          $("#komentarzForm").validate({
                            rules: {
                              nick: { required: true },
                              komentarz: { required: true },
                              <?php if ( KOMENTARZE_ADRES_EMAIL == 'tak' ) { ?>
                              email: { required: true, email: true }
                              <?php } ?>                              
                            },
                            messages: {
                              nick: { required: '<?php echo $GLOBALS['tlumacz']['BLAD_WYMAGANE_POLE']; ?>' },
                              komentarz: { required: '<?php echo $GLOBALS['tlumacz']['BLAD_WYMAGANE_POLE']; ?>' },
                              <?php if ( KOMENTARZE_ADRES_EMAIL == 'tak' ) { ?>
                              email: { required: '<?php echo $GLOBALS['tlumacz']['BLAD_WYMAGANE_POLE']; ?>', email: '<?php echo $GLOBALS['tlumacz']['BLAD_ZLY_EMAIL']; ?>' }
                              <?php } ?>                                 
                            },
                            submitHandler: function() {
                              var sear = $('#komentarzForm').serialize(); 
                              $.post("inne/artykul_komentarz.php?tok=<?php echo Sesje::Token(); ?>", { data: sear }, function(data) { 
                                  var myModal = new jBox('Modal',{ 
                                    content : '<div class="message">'+data+'</div>',
                                    closeButton: false,
                                    closeOnEsc: false,
                                    closeOnMouseleave: false,
                                    closeOnClick: false,
                                    onOpen: function () {
                                        const popupElement = this.wrapper;
                                        setTimeout(function () {
                                            initFocusTrap(popupElement);
                                        }, 50);
                                    }      
                                  });
                                  myModal.open();
                              });
                            }           
                          });        
                          //
                      });
                      </script>
                      
                      <form action="" method="post" class="cmxform KomentarzArtykuluForm LiniaGorna" id="komentarzForm">
                      
                          <b><?php echo $GLOBALS['tlumacz']['DODAJ_KOMENTARZ']; ?></b>
                          
                          <input type="hidden" name="id" value="<?php echo (int)$_POST['id']; ?>" />
                          
                          <p>
                              <span><?php echo $GLOBALS['tlumacz']['NICK_KLIENTA']; ?> <em class="required" id="em_<?php echo uniqid(); ?>"></em></span>
                              <input type="text" size="35" style="width:70%" name="nick" id="nick" />
                          </p>  
                          
                          <?php if ( KOMENTARZE_ADRES_EMAIL == 'tak' ) { ?>
                          
                          <p>
                              <span><?php echo $GLOBALS['tlumacz']['ADRES_EMAIL_KLIENTA']; ?> <br /> <small><?php echo $GLOBALS['tlumacz']['DANE_TYLKO_DLA_ADMINISTRATORA']; ?></small> <em class="required"  id="em_<?php echo uniqid(); ?>"></em></span>
                              <input type="text" size="35" style="width:70%" name="email" id="email" />
                          </p>                            
                          
                          <?php } ?>
                          
                          <?php if ( KOMENTARZE_TELEFON == 'tak' ) { ?>
                          
                          <p>
                              <span><?php echo $GLOBALS['tlumacz']['NUMER_TELEFONU_KLIENTA']; ?> <br /> <small><?php echo $GLOBALS['tlumacz']['DANE_TYLKO_DLA_ADMINISTRATORA']; ?></small> </span>
                              <input type="text" size="35" style="width:70%" name="telefon" id="telefon" />
                          </p>                            
                          
                          <?php } ?>                          

                          <p>
                              <span><?php echo $GLOBALS['tlumacz']['WPISZ_KOMENTARZ']; ?> <em class="required" id="em_<?php echo uniqid(); ?>"></em></span>
                              <textarea cols="60" rows="5" style="width:70%" name="komentarz" id="komentarz"></textarea>
                          </p>         

                          <input type="submit" class="przycisk" value="<?php echo $GLOBALS['tlumacz']['DODAJ_KOMENTARZ']; ?>" />        
                          
                      </form>
                  
                      <?php    
                      
                  }
                  
              }
              
        }
      
    }
}    
?>