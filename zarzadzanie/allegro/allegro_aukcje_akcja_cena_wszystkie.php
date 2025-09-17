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

                $zapytanie = "SELECT ap.auction_id,
                                     ap.products_id,
                                     ap.products_name,
                                     ap.products_stock_attributes, 
                                     ap.products_buy_now_price, 
                                     ps.products_stock_price_tax,
                                     p.products_price_tax,
                                     p.products_currencies_id, 
                                     p.products_points_only,     
                                     p.options_type
                                FROM allegro_auctions ap 
                           LEFT JOIN products p ON p.products_id = ap.products_id 
                           LEFT JOIN products_stock ps ON ps.products_id = ap.products_id AND ps.products_stock_attributes = replace(ap.products_stock_attributes,'x', ',')
                                     WHERE ap.auction_id = '" . floatval($_POST['id_aukcji']) . "'  and ap.auction_status = 'ACTIVE' OR ap.auction_status = 'ACTIVATING'";  
                                                  
                $sql = $db->open_query($zapytanie);

                if ( $db->ile_rekordow($sql) > 0 ) {
                
                    $info = $sql->fetch_assoc();

                    //
                    $cena_sklep = 0;

                    if ( $info['products_points_only'] == 0 ) {
                         //
                         if ( $info['options_type'] == 'ceny' && $info['products_stock_price_tax'] > 0 ) {
                              $cena_sklep = $waluty->FormatujCeneBezSymbolu($info['products_stock_price_tax'], true, '', '', 2, $info['products_currencies_id']);
                           } else {
                              $cena_sklep = $waluty->FormatujCeneBezSymbolu(Produkt::ProduktCenaCechy($info['products_id'], $info['products_price_tax'], str_replace('x', ',', (string)$info['products_stock_attributes'])), true, '', '', 2, $info['products_currencies_id']);
                         }
                         //
                    }   
                    //
                    
                    // cena z zakladki dane allegro produktu
                    $sqlProdukt = $db->open_query("SELECT * FROM products_allegro_info WHERE products_id = '" . (int)$info['products_id'] . "'");                                
                    $produkt = $sqlProdukt->fetch_assoc();  

                    if ( $produkt['products_price_allegro'] > 0 ) {
                         //
                         $cena_sklep = $produkt['products_price_allegro'];
                         //
                    }
                    
                    $db->close_query($sqlProdukt);                  
                    
                    unset($IdPLN);                  

                    if ( $cena_sklep > 0 ) {
                    
                        $id_aukcji = floatval($_POST['id_aukcji']);
                        
                        if ( $info['products_buy_now_price'] != $cena_sklep ) {

                            $DaneDoAktualizacji = new stdClass();
                            $DaneDoAktualizacji->sellingMode = new stdClass();
                            $DaneDoAktualizacji->sellingMode->price = new stdClass();
                            $DaneDoAktualizacji->sellingMode->price->amount = $cena_sklep;

                            $rezultat = $AllegroRest->commandPatch('sale/product-offers/'.$id_aukcji, $DaneDoAktualizacji );

                            if ( is_object($rezultat) && !isset($rezultat->errors) ) {
                      
                                $pola = array(
                                array('products_buy_now_price', (float)$cena_sklep)
                                );

                                $db->update_query('allegro_auctions' , $pola, " auction_id = '".$id_aukcji."'");
                                unset($pola);                   

                                echo '<span>Aukcja o nr <b>' . $id_aukcji . '</b> - cena produktów na aukcji została zaktualizowana ' . ((isset($info['products_name'])) ? '- produkt: <b>' . $info['products_name'] : '') . '</b></span> <br />';
                        
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

                                echo '<span style="color:#ff0000">Aukcja o nr <b>' . $id_aukcji . '</b> nie została odnaleziona</span> <br />';

                            }

                        } else {

                            echo '<span style="color:#ff0000">Aukcja o nr <b>' . $id_aukcji . '</b> - cena jest taka sama w sklepie i na Allegro</span> <br />';
                        }

                    } else {
                      
                        echo '<span style="color:#ff0000">Aukcja o nr <b>' . (int)$_POST['id_aukcji'] . '</b> - cena produktu na Allegro nie może być równa 0 ' . ((isset($info['products_name'])) ? '- produkt: ' . $info['products_name'] : '') . '</span> <br />';
                      
                    }
                    
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
                
                <div id="naglowek_cont">Aktualizacja cen wszystkich produktów na aukcjach</div>
                
                <div id="cont">
                      
                    <form action="allegro/allegro_aukcje_akcja_cena_wszystkie.php" method="post" class="cmxform">          

                    <div class="poleForm">
                    
                      <div class="naglowek">Synchronizacja cen wszystkich produktów na aukcjach z cenami produktów w sklepie</div>
                      
                      <div class="pozycja_edytowana">

                          <?php $ilosc = 0; ?>
                          
                          <?php if ( $_POST['akcja_dolna'] != 'cena' ) { ?>
                      
                              <input type="hidden" name="akcja" value="cena" />  
                              <input type="hidden" name="akcja_dolna" value="cena" />
                          
                              <p>
                                Czy zaktualizować ceny produktów na aukcjach dla poniższych produktów ?
                                
                                <span class="maleInfo" style="margin-left:0px">sprawdzane i aktualizowane będą tylko aukcje trwające, pomijane będą aukcje zakończone</span>
                              </p> 

                              <p class="ListaAukcji">
                              
                                <?php
                                $zapytanie = "SELECT ap.auction_id,
                                     ap.products_id,
                                     ap.products_name,
                                     ap.products_stock_attributes, 
                                     ap.products_buy_now_price, 
                                     ps.products_stock_price_tax,
                                     p.products_price_tax,
                                     p.products_currencies_id, 
                                     p.products_points_only,     
                                     p.options_type
                                     FROM allegro_auctions ap 
                                     LEFT JOIN products p ON p.products_id = ap.products_id 
                                     LEFT JOIN products_stock ps ON ps.products_id = ap.products_id AND ps.products_stock_attributes = replace(ap.products_stock_attributes,'x', ',')
                                     WHERE ap.auction_status = 'ACTIVE' OR ap.auction_status = 'ACTIVATING'";

                                $sql = $db->open_query($zapytanie);
                                
                                while ( $info = $sql->fetch_assoc() ) {

                                    $cena_sklep = 0;

                                    if ( $info['products_points_only'] == 0 ) {
                                         //
                                         if ( $info['options_type'] == 'ceny' && $info['products_stock_price_tax'] > 0 ) {
                                              $cena_sklep = $waluty->FormatujCeneBezSymbolu($info['products_stock_price_tax'], true, '', '', 2, $info['products_currencies_id']);
                                           } else {
                                              $cena_sklep = $waluty->FormatujCeneBezSymbolu(Produkt::ProduktCenaCechy($info['products_id'], $info['products_price_tax'], str_replace('x', ',', (string)$info['products_stock_attributes'])), true, '', '', 2, $info['products_currencies_id']);
                                         }
                                         //
                                    }   
                                    //
                                    
                                    // cena z zakladki dane allegro produktu
                                    $sqlProdukt = $db->open_query("SELECT * FROM products_allegro_info WHERE products_id = '" . (int)$info['products_id'] . "'");                                
                                    $produkt = $sqlProdukt->fetch_assoc();  

                                    if ( $produkt['products_price_allegro'] > 0 ) {
                                         //
                                         $cena_sklep = $produkt['products_price_allegro'];
                                         //
                                    }
                                    
                                    $db->close_query($sqlProdukt);                  
                    
                                    if ( $cena_sklep != $info['products_buy_now_price'] ) {
                                      
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
                                
                                $db->close_query($sql);
                                unset($zapytanie, $idAukcji); 

                                if ( $ilosc == 0 ) {
                                     //
                                     echo '<div class="ostrzezenie" style="margin-left:10px">Brak produktów z różnicą cen do aktualizacji</div>';
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

                                    $.post( "allegro/allegro_aukcje_akcja_cena_wszystkie.php?tok=<?php echo Sesje::Token(); ?>", 
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

                      <div class="przyciski_dolne" id="przyciski" <?php echo (( $_POST['akcja_dolna'] == 'cena' )  ? 'style="display:none"' : ''); ?>>
                        
                        <?php if ( $_POST['akcja_dolna'] != 'cena' && $ilosc > 0 ) { ?>
                            <input type="submit" class="przyciskNon" value="Aktualizuj ceny produktów" />
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