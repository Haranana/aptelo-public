<?php
chdir('../'); 

define('POKAZ_ILOSC_ZAPYTAN', false);

require_once('ustawienia/ustawienia_db.php');
include('klasy/Bazadanych.php');
include('klasy/Funkcje.php');
include('klasy/CzyscFormularz.php');

$db = new Bazadanych();
$filtr = new CzyscFormularz(array(), array(), '1', '1', '1');

if ( isset($_POST['tok']) && isset($_POST['czat_info']) && isset($_POST['czat_foto']) && isset($_POST['czat_link']) && isset($_COOKIE['eGold']) ) {

     $ciag = $_POST['tok'];
     $wynikPowr = '';
     for ($t = 0; $t < strlen((string)$ciag); $t++) {
        $wynikPowr .= substr((string)$ciag, (strlen((string)$ciag) - 1) - $t, 1 );
     }            
     $wynikDekod = '';
     for ($t = 0; $t < strlen((string)$wynikPowr); $t++) {
        if ($t%2 == 0) {
           $wynikDekod .= substr((string)$wynikPowr, $t, 1 );
        }
     }  
     unset($wynikPowr);
     
     $CzatInfo = explode('##', base64_decode((string)$filtr->process($_POST['czat_info'])));
     $CzatFoto = base64_decode((string)$filtr->process($_POST['czat_foto']));
     $CzatLinkFormularz = base64_decode((string)$filtr->process($_POST['czat_link']));
     
     if ( strpos((string)$_COOKIE['eGold'], (string)$wynikDekod) > -1 && count($CzatInfo) == 8 ) {
          //
          if ( !isset($_COOKIE['__kc']) ) {
               //
               setcookie("__kc", substr((string)$_COOKIE['eGold'], 0, 15), time() + 86400, '/');
               //
          }

          if ( isset($_POST['start']) && (int)$_POST['start'] == 1 ) {
            
              ?>

              <?php if ( !empty($CzatFoto) ) { ?>
              
              <div id="IkonaCzat"><img src="<?php echo $CzatFoto; ?>" alt="Czat" /></div>
              
              <?php } ?>
              
              <div id="OknoCzat">

                    <div id="IkonaCzatZwin" <?php echo ((isset($_COOKIE['czat']) && (int)$_COOKIE['czat'] == 1) ? 'style="display:block"' : ''); ?>>x</div>
                    
                    <div class="CzatWitaj"><?php echo $CzatInfo[3]; ?></div>
                    
                    <div id="DaneCzatu" <?php echo ((isset($_COOKIE['czat']) && (int)$_COOKIE['czat'] == 1) ? 'style="display:block"' : ''); ?>>
                    
                        <div id="TrescCzat"></div>
                        
                        <div id="WiadomoscCzat">
                            
                            <?php
                            // sprawdza czy konsultant nie jest niedostepny powyzej 10 sek
                            $KonsultantDostepny = false;
                            //
                            if ( file_exists('czat/Cache_Czat') ) {
                                 //
                                 $DataPlikuCzatu = getdate(filemtime('czat/Cache_Czat'));
                                 //
                                 if ( (int)time() - (int)$DataPlikuCzatu[0] < 10 ) {
                                      //
                                      $KonsultantDostepny = true;
                                      //
                                 }
                            }                             
                            ?>
                            
                            <textarea id="KlientCzat" name="KlientCzat" <?php echo (($KonsultantDostepny == false) ? 'style="display:none"' : ''); ?> placeholder="<?php echo $CzatInfo[5]; ?>"></textarea>
                            
                            <div id="PrzyciskWyslijCzat" <?php echo (($KonsultantDostepny == false) ? 'style="display:none"' : ''); ?>>
                                <button type="buttom"><?php echo $CzatInfo[4]; ?></button>
                            </div>

                            <div id="KonsultantNiedostepny" <?php echo (($KonsultantDostepny == true) ? 'style="display:none"' : ''); ?>>
                            
                                <?php echo $CzatInfo[6]; ?>
                                
                                <?php
                                if ( !empty($CzatLinkFormularz) ) {
                                     //
                                     echo '<a href="' . $CzatLinkFormularz . '">' . $CzatInfo[7] . '</a>';
                                     //
                                }
                                ?>
                                
                            </div>
                            
                        </div>
                        
                    </div>
              
              </div>
              
              <audio id="audio" src="czat/wiadomosc_czat.mp3"></audio>

          <?php } else {
          
              // dodanie wpisu klienta
              if ( isset($_POST['tresc']) && isset($_POST['wpis']) && (int)$_POST['wpis'] == 1 && isset($_POST['ip']) ) {
              
                  $pola = array(array('id_customer', substr((string)$_COOKIE['eGold'], 0, 15)),
                                array('ip_customer', $filtr->process($_POST['ip'])),
                                array('data', 'now()'),
                                array('from_message', 'klient'),
                                array('text', $filtr->process(strip_tags((string)$_POST['tresc']))));		
                                
                  $db->insert_query('chat' , $pola);	
                  unset($pola);           
                  
                  echo '<div class="InfoKlient"><ul><li>' . $CzatInfo[0] . '</li><li>' . date('H:i', time()) . '</li></ul>' . $filtr->process(strip_tags((string)$_POST['tresc'])) . '</div>'; 
                  
              } else {
                
                  // wyswietlenie listy wpisow czatu
                  //
                  $sql = $db->open_query("select id_customer, data, from_message, text from chat where id_customer = '" . substr((string)$_COOKIE['eGold'], 0, 15) . "' order by data");  
                  //
                  if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
                      //
                      while ( $info = $sql->fetch_assoc() ) {
                          //
                          if ( $info['from_message'] == 'klient' ) {
                               //
                               echo '<div class="InfoKlient"><ul><li>' . $CzatInfo[0] . '</li><li>' . date('H:i', FunkcjeWlasnePHP::my_strtotime($info['data'])) . '</li></ul>' . $info['text'] . '</div>';
                               //
                          }
                          if ( $info['from_message'] == 'admin' ) {
                               //
                               echo '<div class="InfoAdmin"><ul><li>' . $CzatInfo[1] . '</li><li>' . date('H:i', FunkcjeWlasnePHP::my_strtotime($info['data'])) . '</li></ul>' . $info['text'] . '</div>';
                               //
                          }                  
                          //                
                      }
                      //
                  } else {
                      //
                      echo '<div class="InfoRozmowa">' . $CzatInfo[2] . '</div>';
                      //
                  }
                  //
                  $db->close_query($sql); 
                  unset($info);                
                  //
                  
              }
              
          }
          
          // sprawdza czy konsultant nie jest niedostepny powyzej 10 sek
          
          // zapisanie pliku ze jest aktywny czat
          if ( !file_exists('czat/Cache_Czat') ) {
               //
               $plikKlucz = fopen('czat/Cache_Czat','a+');
               if (!$plikKlucz) throw new Exception('Nie moge zapisac pliku cache czatu');
               flock($plikKlucz,LOCK_EX);
               fseek($plikKlucz,0);
               ftruncate($plikKlucz,0);
               if (fwrite($plikKlucz,time()) === false) {
                   throw new Exception('Nie moge zapisac pliku cache czatu');
               }
               fclose($plikKlucz);          
               //
               echo "<script>$('#KlientCzat').hide();$('#PrzyciskWyslijCzat').hide();$('#KonsultantNiedostepny').show()</script>";
               //
          } else {
               //
               $DataPlikuCzatu = getdate(filemtime('czat/Cache_Czat'));
               $KonsultantDostepny = false;
               if ( (int)time() - (int)$DataPlikuCzatu[0] < 10 ) {
                   //
                   echo "<script>$('#KlientCzat').show();$('#PrzyciskWyslijCzat').show();$('#KonsultantNiedostepny').hide()</script>";
                   //
               } else {
                   //
                   echo "<script>$('#KlientCzat').hide();$('#PrzyciskWyslijCzat').hide();$('#KonsultantNiedostepny').show()</script>";
                   //
               } 
               //
          }
          
     }

}
?>