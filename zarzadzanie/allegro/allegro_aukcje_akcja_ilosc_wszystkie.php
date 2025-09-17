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

                $zapytanie = "SELECT * FROM allegro_auctions WHERE auction_id = '".floatval($_POST['id_aukcji'])."' and auction_status = 'ACTIVE' OR auction_status = 'ACTIVATING'";
                $sql = $db->open_query($zapytanie);

                if ( $db->ile_rekordow($sql) > 0 ) {
                
                  $info = $sql->fetch_assoc();

                  // okresli stan magazynowy produktu
                  $zapytanie_produkt = "SELECT products_quantity FROM products WHERE products_id = '".$info['products_id']."'";
                  $sql_produkt = $db->open_query($zapytanie_produkt);
                  
                  if ( $db->ile_rekordow($sql_produkt) > 0 ) {
                  
                      $info_produkt = $sql_produkt->fetch_assoc();
                      
                      $IloscMagazyn = $info_produkt['products_quantity'];
                      
                      // jezeli jest powiazanie cech z magazynem
                      if ( CECHY_MAGAZYN == 'tak' ) {

                          $zapytanie_ilosc_cechy = "SELECT * 
                                                      FROM products_stock
                                                     WHERE products_id = '" . (int)$info['products_id']. "' 
                                                       AND products_stock_attributes = '".str_replace('x', ',' , (string)$info['products_stock_attributes'])."'";
                                                       
                          $sql_ilosc_cechy = $db->open_query($zapytanie_ilosc_cechy);

                          if ((int)$db->ile_rekordow($sql_ilosc_cechy) > 0) {
                          
                              $info_ilosc_cechy = $sql_ilosc_cechy->fetch_assoc();
                              $IloscMagazyn = $info_ilosc_cechy['products_stock_quantity'];
                              
                          }
                          
                          $db->close_query($sql_ilosc_cechy);
                          unset($zapytanie_ilosc_cechy, $info_ilosc_cechy, $cechy_produktu);

                      }

                      $NowaIloscAllegro = '0';
                      $NowaIloscAllegro = $IloscMagazyn;

                      if ( $NowaIloscAllegro > 0 ) {
                      
                          $id_aukcji = floatval($info['auction_id']);

                          if ( $info['auction_quantity'] != $IloscMagazyn ) {

                                  $DaneDoAktualizacji = new stdClass();
                                  $DaneDoAktualizacji->stock = new stdClass();
                                  $DaneDoAktualizacji->stock->available = floor($NowaIloscAllegro);

                                  $rezultat = $AllegroRest->commandPatch('sale/product-offers/'.$id_aukcji, $PrzetwarzanaAukcja );

                                  if ( is_object($rezultat) && !isset($rezultat->errors) ) {
                    
                                      $pola = array(
                                                array('auction_quantity',floor($NowaIloscAllegro)),
                                                array('products_quantity',floor($NowaIloscAllegro))
                                      );

                                      $db->update_query('allegro_auctions' , $pola, " auction_id = '".$id_aukcji."'");
                                      
                                      unset($pola);

                                      echo '<span>Aukcja o nr <b>' . $id_aukcji . '</b> - ilość produktów na aukcji została zaktualizowana ' . ((isset($info['products_name'])) ? '- produkt: <b>' . $info['products_name'] : '') . '</b></span> <br />';
                                    

                                  } elseif ( is_object($rezultat) && ( isset($rezultat->errors) && count($rezultat->errors) > 0 ) ) {

                                      echo '<span style="color:#ff0000">Aukcja o nr <b>' . $id_aukcji . '</b> - ';

                                      foreach ( $rezultat->errors as $Blad ) {

                                          if ( isset($Blad->userMessage) ) {
                                            
                                               echo $Blad->userMessage;
                                               
                                          } else if ( isset($Blad->message) ) {
                                            
                                               echo $Blad->message;
                                               
                                          }
                                          
                                      }
                                      
                                      echo '</span> <br />';
                                
                                  } else {
                                  
                                      echo '<span style="color:#ff0000">Aukcja o nr <b>' . $id_aukcji . '</b> - nie została odnaleziona</span> <br />';
                                    
                                  }

                          } else {

                            echo '<span style="color:#ff0000">Aukcja o nr <b>' . $id_aukcji . '</b> - ilość produktów na aukcji jest taka jak w magazynie</span> <br />';
                          }

                      } else {
                        
                          echo '<span style="color:#ff0000">Aukcja o nr <b>' . (int)$_POST['id_aukcji'] . '</b> - ilość produktów na Allegro nie może być równa 0 ' . ((isset($info['products_name'])) ? '- produkt: ' . $info['products_name'] : '') . '</span> <br />';
                        
                      }
                      
                      unset($info_produkt, $IloscAllegro);
                      
                  }
                  
                  $db->close_query($sql_produkt);
                  unset($zapytanie_produkt);                 
                  
                  unset($info);
                  
              }
              
              $db->close_query($sql);
              unset($zapytanie);      
              
            }
        
        } else {

            if ( isset($_POST['akcja_dolna']) ) {

                // wczytanie naglowka HTML
                include('naglowek.inc.php');
                ?>
                
                <div id="naglowek_cont">Aktualizacja ilości produktów na aukcjach</div>
                
                <div id="cont">
                      
                    <form action="allegro/allegro_aukcje_akcja_ilosc_wszystkie.php" method="post" class="cmxform">          

                    <div class="poleForm">
                    
                      <div class="naglowek">Synchronizacja ilości produktów na aukcjach ze stanem magazynowym sklepu</div>
                      
                      <div class="pozycja_edytowana">
                      
                          <?php $ilosc = 0; ?>
                          
                          <?php if ( $_POST['akcja_dolna'] != 'ilosc' ) { ?>
                      
                              <input type="hidden" name="akcja" value="ilosc" />  
                              <input type="hidden" name="akcja_dolna" value="ilosc" />
                          
                              <p>
                                Czy zaktualizować ilość produktów na aukcjach dla poniższych produktów ?
                                
                                <span class="maleInfo" style="margin-left:0px">sprawdzane i aktualizowane będą tylko aukcje trwające, pomijane będą aukcje zakończone</span>
                              </p> 

                              <p class="ListaAukcji">
                                <?php

                                $zapytanie = "SELECT * FROM allegro_auctions WHERE auction_status = 'ACTIVE' OR auction_status = 'ACTIVATING'";

                                $sql = $db->open_query($zapytanie);
                                
                                while ( $info = $sql->fetch_assoc() ) {

                                    // okresli stan magazynowy produktu
                                    $zapytanie_produkt = "SELECT products_quantity FROM products WHERE products_id = '".$info['products_id']."'";
                                    $sql_produkt = $db->open_query($zapytanie_produkt);
                  
                                    if ( $db->ile_rekordow($sql_produkt) > 0 ) {
                  
                                        $info_produkt = $sql_produkt->fetch_assoc();
                      
                                        $IloscMagazyn = $info_produkt['products_quantity'];
                      
                                        // jezeli jest powiazanie cech z magazynem
                                        if ( CECHY_MAGAZYN == 'tak' ) {

                                          $zapytanie_ilosc_cechy = "SELECT * 
                                                                      FROM products_stock
                                                                     WHERE products_id = '" . (int)$info['products_id']. "' 
                                                                       AND products_stock_attributes = '".str_replace('x', ',' , (string)$info['products_stock_attributes'])."'";
                                                                       
                                          $sql_ilosc_cechy = $db->open_query($zapytanie_ilosc_cechy);

                                          if ((int)$db->ile_rekordow($sql_ilosc_cechy) > 0) {
                                          
                                              $info_ilosc_cechy = $sql_ilosc_cechy->fetch_assoc();
                                              $IloscMagazyn = $info_ilosc_cechy['products_stock_quantity'];
                                              
                                          }
                                          
                                          $db->close_query($sql_ilosc_cechy);
                                          unset($zapytanie_ilosc_cechy, $info_ilosc_cechy, $cechy_produktu);

                                        }

                                        if ( $info['auction_quantity'] != $IloscMagazyn ) {
                                    
                                            echo '<input type="hidden" name="opcja[]" value="'.$info['auction_id'].'" />';
                                            
                                            $link = '';
                                            if ( $AllegroRest->polaczenie['CONF_SANDBOX'] == 'nie' ) {
                                              $link = 'http://allegro.pl/i' . $info['auction_id'] . '.html';
                                            } else {
                                              $link = 'http://allegro.pl.allegrosandbox.pl/i' . $info['auction_id'] . '.html';
                                            }                          
                                            
                                            echo '<a href="' . $link . '" target="_blank">' . $info['auction_id'] . '</a> - ' . $info['products_name'] . '<br />';
                                            
                                            $ilosc++;
                                            
                                            unset($link);

                                        }
                                    
                                    }
                                }

                                $db->close_query($sql);
                                unset($zapytanie, $idAukcji); 

                                if ( $ilosc == 0 ) {
                                     //
                                     echo '<div class="ostrzezenie" style="margin-left:10px">Brak produktów z różnicą ilości do aktualizacji</div>';
                                     //
                                }
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

                                    $.post( "allegro/allegro_aukcje_akcja_ilosc.php?tok=<?php echo Sesje::Token(); ?>", 
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

                                        $('#procent').html('Stopień realizacji: <span>' + procent + '%</span><br />Przetworzono: <span id="licz_produkty">' + (nr + 1) + '</span>');    

                                        $('#suwak_aktywny').css('width' , (procent * 5) + 5 + 'px');

                                        if (nr < tablicaId.length - 1) {
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

                      <div class="przyciski_dolne" id="przyciski" <?php echo (( $_POST['akcja_dolna'] == 'ilosc' )  ? 'style="display:none"' : ''); ?>>
                        
                        <?php if ( $_POST['akcja_dolna'] != 'ilosc' && $ilosc > 0 ) { ?>
                            <input type="submit" class="przyciskNon" value="Aktualizuj ilość produktów" />
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