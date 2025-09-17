<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if ( Funkcje::SprawdzAktywneAllegro() ) {

        $AllegroRest = new AllegroRest( array('allegro_user' => $_SESSION['domyslny_uzytkownik_allegro']) );

        if ( isset($_POST['ajax']) ) {
        
            if ( isset($_POST['id_aukcji']) && (int)$_POST['id_aukcji'] > 0 ) {

                $UUID = $AllegroRest->UUIDv4();

                $DaneDoWyslania = new stdClass();
                $DaneDoWyslania->publication = new stdClass();
                $DaneDoWyslania->offerCriteria = array();
                $DaneDoWyslania->offerCriteria['0'] = new stdClass();
                $DaneDoWyslania->offerCriteria['0']->offers = array();
                $DaneDoWyslania->offerCriteria['0']->offers['0'] = new stdClass();

                $DaneDoWyslania->publication->action = 'END';
                $DaneDoWyslania->offerCriteria['0']->offers['0']->id = $_POST['id_aukcji'];
                $DaneDoWyslania->offerCriteria['0']->type = "CONTAINS_OFFERS";

                $rezultat = $AllegroRest->commandPut('sale/offer-publication-commands/'.$UUID, $DaneDoWyslania );

                if ( is_object($rezultat) && !isset($rezultat->errors) ) {
              
                  $pola = array(
                        array('auction_uuid',$rezultat->id)
                  );
                        
                  $db->update_query('allegro_auctions' , $pola, " auction_id = '".floatval($_POST['id_aukcji'])."'");	
                  unset($pola);

                  echo '<span>Aukcja o nr <b>' . floatval($_POST['id_aukcji']) . '</b> - została zaktualizowana ' . ((isset($info['products_name'])) ? '- produkt: ' . $info['products_name'] : '') . '</span> <br />';

                } elseif ( is_object($rezultat) && ( isset($rezultat->errors) && count($rezultat->errors) > 0 ) ) {

                    echo '<span>Aukcja o nr <b>' . floatval($_POST['id_aukcji']) . '</b> - ';

                    foreach ( $rezultat->errors as $Blad ) {

                        echo $Blad->userMessage . '; ';

                    }
                } else {

                  echo '<span style="color:#ff0000">Aukcja o nr <b>' . floatval($_POST['id_aukcji']) . '</b> - wystąpił błąd</span> <br />';
                
                }

                unset($allegro);
            
            }
        
        } else {

            if ( isset($_POST['akcja_dolna']) && isset($_POST['opcja']) && count($_POST['opcja']) > 0 ) {

                // wczytanie naglowka HTML
                include('naglowek.inc.php');
                ?>
                
                <div id="naglowek_cont">Zakończenie aukcji Allegro przed czasem</div>
                
                <div id="cont">
                      
                    <form action="allegro/allegro_aukcje_akcja_zakoncz.php" method="post" class="cmxform">          

                    <div class="poleForm">
                    
                      <div class="naglowek">Zakończenie aukcji Allegro przed czasem</div>
                      
                      <div class="pozycja_edytowana">

                          <?php if ( $_POST['akcja_dolna'] != 'zakoncz' ) { ?>
                      
                              <input type="hidden" name="akcja" value="zakoncz" />  
                              <input type="hidden" name="akcja_dolna" value="zakoncz" />
                          
                              <p>
                                Czy zakończyć poniższe aukcje produktów ?
                              </p> 

                              <p class="ListaAukcji">
                                <?php
                                $Aktualizuj = false;
                                $idAukcji = implode(',', (array)$_POST['opcja']);

                                $zapytanie = "SELECT * FROM allegro_auctions WHERE allegro_id IN (" . $idAukcji . ")";
                                $sql = $db->open_query($zapytanie);
                                
                                while ( $info = $sql->fetch_assoc() ) {
                                
                                    if ( $info['auction_status'] == 'ACTIVE' || $info['auction_status'] == 'ACTIVATING' ) {
                                        echo '<input type="hidden" name="opcja[]" value="'.$info['auction_id'].'" />';
                                        
                                        $link = '';
                                        if ( $AllegroRest->polaczenie['CONF_SANDBOX'] == 'nie' ) {
                                          $link = 'http://allegro.pl/i' . $info['auction_id'] . '.html';
                                        } else {
                                          $link = 'http://allegro.pl.allegrosandbox.pl/i' . $info['auction_id'] . '.html';
                                        }                          
                                        
                                        echo '<a href="' . $link . '" target="_blank">' . $info['auction_id'] . '</a> - ' . $info['products_name'] . '<br />';
                                        
                                        unset($link);
                                        $Aktualizuj = true;

                                    }
                                    
                                }
                                
                                $db->close_query($sql);
                                unset($zapytanie, $idAukcji);                          
                                ?>
                              </p> 
                              
                          <?php } else { ?>
                          
                              <?php
                              $komunikaty = '';
                              
                              if ( Funkcje::SprawdzAktywneAllegro() ) {
                                  ?>
                                  
                                  <div id="import">
                                        
                                    <div id="postep">Postęp procesu ...</div>
                                        
                                    <div id="suwak">
                                      <div style="margin:1px;overflow:hidden">
                                        <div id="suwak_aktywny"></div>
                                      </div>
                                    </div>
                                            
                                    <div id="procent"></div>  

                                    <div id="wynik" class="ListaAukcji" style="margin-top:10px;"></div>
                                    
                                  </div>   
                                        
                                  <div id="zaimportowano" style="display:none">
                                    Dane zostały przetworzone
                                  </div>                          
                                  
                                  <script>

                                  <?php
                                  $tab_tmp = '';
                                  foreach ( $_POST['opcja'] as $klucz => $id_aukcji_allegro ) {
                                  
                                      $tab_tmp .= "'" . $id_aukcji_allegro . "',";
                                      
                                  }     
                                  $tab_tmp = substr((string)$tab_tmp, 0, -1);
                                  ?>   
                                  
                                  var tablicaId = new Array(<?php echo $tab_tmp; ?>);
                                  
                                  function allegro_dane(nr) {

                                    $.post( "allegro/allegro_aukcje_akcja_zakoncz.php?tok=<?php echo Sesje::Token(); ?>", 
                                      { 
                                        id_aukcji : tablicaId[nr],
                                        ajax : 'tak'
                                      },
                                      function(data) {

                                        if (tablicaId.length - 1 <= 1) {
                                          procent = 100;
                                        } else {
                                          procent = parseInt( ((nr + 1) / tablicaId.length) * 100 );
                                          if (procent > 100) {
                                            procent = 100;
                                          }
                                        }
                                        
                                        if ( nr == (tablicaId.length - 1) ) {
                                            procent = 100;
                                        }

                                        $('#procent').html('Stopień realizacji: <span>' + procent + '%</span><br />Przetworzono: <span id="licz_produkty">' + nr + '</span>');    

                                        $('#suwak_aktywny').css('width' , (procent * 5) + 5 + 'px');

                                        if (nr < tablicaId.length) {
                                          allegro_dane(nr + 1);
                                        } else {
                                          $('#postep').css('display','none');
                                          $('#suwak').slideUp("fast");
                                          $('#zaimportowano').slideDown("fast");
                                          $('#przyciski').slideDown("fast");
                                        }   
                                        
                                        if (data != '') {
                                          $('#licz_produkty').html(nr + 1);
                                          $('#wynik').html( $('#wynik').html() + data );
                                        }
                                          
                                      }
                                    );
                                    
                                  };    

                                  allegro_dane(0);
                                  </script> 
                                  
                                  <?php
                                  unset($tab_tmp);
                                  
                              } else {
                              
                                  $komunikaty = Okienka::pokazOkno('Błąd', 'Nie jesteś zalogowany w serwisie Allegro', 'index.php');
                              
                              }
                              ?>

                              <p class="ListaAukcji">
                                <?php echo $komunikaty; ?>
                              </p>
                              
                              <?php
                              unset($komunikaty);
                              ?>                      
                          
                          <?php } ?>
                          
                      </div>

                      <div class="przyciski_dolne" id="przyciski" <?php echo (( $_POST['akcja_dolna'] == 'zakoncz' )  ? 'style="display:none"' : ''); ?>>
                        
                        <?php if ( $_POST['akcja_dolna'] != 'zakoncz' && $Aktualizuj ) { ?>
                            <input type="submit" class="przyciskNon" value="Wykonaj" />
                        <?php } ?>
                        
                        <button type="button" class="przyciskNon" onclick="cofnij('allegro_aukcje','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','allegro');">Powrót</button> 
                        
                      </div>

                    </div>

                    </form>

                </div>    
                
                <?php
                include('stopka.inc.php');

            } else {
            
                Funkcje::PrzekierowanieURL('allegro_aukcje.php');
                
            }
            
        }
    } else {

        Funkcje::PrzekierowanieURL('allegro_aukcje.php');

    }

}

?>