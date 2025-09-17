<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {
  
    $wynik = '';
    
    if ( Funkcje::SprawdzAktywneAllegro() ) {

        if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
            //		
            $AllegroRest = new AllegroRest( array('allegro_user' => $_SESSION['domyslny_uzytkownik_allegro']) );

            $allegro = array();
            $allegro['benefits'] = array(array( 'specification' => array( 'type' => 'UNIT_PERCENTAGE_DISCOUNT', 'configuration' => array( 'percentage' => (int)$_POST["rabat"] ), 'trigger' => array( 'forEachQuantity' => (int)$_POST["ktory_przedmiot"], 'discountedNumber' => '1' ) )));
            $allegro['offerCriteria'] = array(array( 'type' => 'CONTAINS_OFFERS', 'offers' => array( array( 'id' => $filtr->process($_POST["aukcja_id"]) )) ));

            $PrzetwarzanaAukcja = $AllegroRest->commandPost('sale/loyalty/promotions', $allegro );
            
            if ( isset($PrzetwarzanaAukcja->errors) ) {
                 //
                 $wynik = '<div style="margin:10px"><div class="ostrzezenie" style="margin-bottom:15px;display:block">Wystąpił błąd podczas tworzenia rabatu !</div>';
                 //
                 $wynik .= '<b style="margin-bottom:5px;display:block">Informacje jakie zwrócił portal Allegro:</b>';
                 //
                 foreach ( $PrzetwarzanaAukcja->errors as $blad ) {
                     //
                     $wynik .= '<div>' . $blad->code . ' ' . $blad->userMessage . '</div>';
                     //
                 }        
                 //
                 $wynik .= '</div>';
                 //
                 $pola = array(
                         array('allegro_benefits',''),
                         array('allegro_benefits_quantity',0),
                         array('allegro_benefits_discount',0),
                         array('allegro_benefits_status',0));

                 $db->update_query('allegro_auctions' , $pola, " allegro_id = '" . (int)$_POST['id'] . "'");             
                 unset($pola);
                 //
            } else {
                 //        
                 if ( isset($PrzetwarzanaAukcja->id) ) {
                      //
                      $wynik = '<div id="zaimportowano">Rabat dla aukcji ' . $_POST["aukcja_id"] . ' został utworzony</div>'; 
                      //
                      $pola = array(
                              array('allegro_benefits',$PrzetwarzanaAukcja->id),
                              array('allegro_benefits_quantity',(int)$_POST["ktory_przedmiot"]),
                              array('allegro_benefits_discount',(int)$_POST["rabat"]),
                              array('allegro_benefits_status', ((isset($PrzetwarzanaAukcja->status) && $PrzetwarzanaAukcja->status == 'ACTIVE') ? '1' : '0')));
     
                      $db->update_query('allegro_auctions' , $pola, " allegro_id = '" . (int)$_POST['id'] . "'");             
                      unset($pola);
                      //
                 } else {
                      //
                      $wynik = '<div style="margin:10px"><div class="ostrzezenie" style="margin-bottom:15px;display:block">Wystąpił błąd podczas tworzenia rabatu !</div></div>';
                      //
                 }
                 //
            }
            //
            $_GET['id_poz'] = $filtr->process($_POST['id']);
            //	        
        }
        
        if (isset($_POST['akcja']) && $_POST['akcja'] == 'usun') {
            //		
            $AllegroRest = new AllegroRest( array('allegro_user' => $_SESSION['domyslny_uzytkownik_allegro']) );

            $PrzetwarzanaAukcja = $AllegroRest->commandDelete('sale/loyalty/promotions/' . $filtr->process($_POST['id_rabatu']));
            
            $wynik = '<div id="zaimportowano">Rabat dla aukcji został usunięty</div>'; 
            //
            $pola = array(
                    array('allegro_benefits',''),
                    array('allegro_benefits_quantity',0),
                    array('allegro_benefits_discount',0),
                    array('allegro_benefits_status',0));

            $db->update_query('allegro_auctions' , $pola, " allegro_id = '" . (int)$_POST['id'] . "'");             
            unset($pola);            
            //
            $_GET['id_poz'] = $filtr->process($_POST['id']);
            //	        
        }        

        // wczytanie naglowka HTML
        include('naglowek.inc.php');
        ?>
        
        <div id="naglowek_cont">Rabat przy zakupie kilku sztuk tego samego przedmiotu</div>
        <div id="cont">
              
              <form action="allegro/allegro_aukcja_rabat.php" id="rabatForm" method="post" class="cmxform">          

              <div class="poleForm">
                <div class="naglowek">Edycja danych</div>
                
                <?php
                
                if ( !isset($_GET['id_poz']) ) {
                     $_GET['id_poz'] = 0;
                }    
                
                $zapytanie = "SELECT * FROM allegro_auctions WHERE allegro_id = '" . (int)$_GET['id_poz'] . "'";
                $sql = $db->open_query($zapytanie);
                
                if ((int)$db->ile_rekordow($sql) > 0) {
                  
                    $info = $sql->fetch_assoc();
                  
                    if ((isset($_SESSION['domyslny_uzytkownik_allegro']) && $_SESSION['domyslny_uzytkownik_allegro'] == $info['auction_seller'])) {
                  
                        if ( $wynik == '' ) {

                            // sprawdzi czy nie ma utworzonego rabatu
                            if ( $info['allegro_benefits'] != '' ) { ?>            

                                <div class="pozycja_edytowana">
                                
                                    <div class="info_content">
                                
                                        <input type="hidden" name="akcja" value="usun" />
                                    
                                        <input type="hidden" name="aukcja_id" value="<?php echo $info['auction_id']; ?>" />
                                        <input type="hidden" name="id" value="<?php echo (int)$_GET['id_poz']; ?>" />
                                        <input type="hidden" name="id_rabatu" value="<?php echo $info['allegro_benefits']; ?>" />
                                        
                                        <p>
                                            Ta aukcja ma już ustawiony rabat. Czy chcesz go usunąć ?
                                        </p>

                                    </div>
                                    
                                </div>

                                <div class="przyciski_dolne">
                                  <input type="submit" class="przyciskNon" value="Usuń rabat" />
                                  <button type="button" class="przyciskNon" onclick="cofnij('allegro_aukcje','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','allegro');">Powrót</button> 
                                </div>                        
                            
                            <?php } else { ?>
                        
                                <script>
                                $(document).ready(function() {
                                  $("#rabatForm").validate({
                                    rules: {
                                      rabat: { required: true, min: function() { return sprawdzMin(); }, max:100, number: true } 
                                    },
                                    messages: {
                                      rabat: {
                                        required: "Pole jest wymagane."
                                      } 
                                    }
                                  });
                                  function sprawdzMin() {
                                      if ( $('#ktory_przedmiot').val() == '2' ) {
                                           return 15;
                                      }
                                      if ( $('#ktory_przedmiot').val() == '3' ) {
                                           return 30;
                                      }
                                      if ( $('#ktory_przedmiot').val() == '4' ) {
                                           return 40;
                                      }
                                      if ( $('#ktory_przedmiot').val() == '5' ) {
                                           return 50;
                                      } 
                                  }
                                  $('#ktory_przedmiot').change(function() {
                                      if ( $(this).val() == '2' ) {
                                           $('#info_rabat').html('minimalna wartość to 15%');
                                      }
                                      if ( $(this).val() == '3' ) {
                                           $('#info_rabat').html('minimalna wartość to 30%');
                                      }
                                      if ( $(this).val() == '4' ) {
                                           $('#info_rabat').html('minimalna wartość to 40%');
                                      }
                                      if ( $(this).val() == '5' ) {
                                           $('#info_rabat').html('minimalna wartość to 50%');
                                      }                  
                                  });
                                });
                                </script>
                            
                                <div class="pozycja_edytowana">
                                
                                    <div class="info_content">
                                
                                        <input type="hidden" name="akcja" value="zapisz" />
                                    
                                        <input type="hidden" name="aukcja_id" value="<?php echo $info['auction_id']; ?>" />
                                        <input type="hidden" name="id" value="<?php echo (int)$_GET['id_poz']; ?>" />
                                        
                                        <p>
                                           <label class="required" for="ktory_przedmiot">Który przedmiot ma podlegać obniżce:</label>
                                           <select name="ktory_przedmiot" id="ktory_przedmiot">
                                              <option value="2">drugi</option>
                                              <option value="3">trzeci</option>
                                              <option value="4">czwarty</option>
                                              <option value="5">piąty</option>
                                           </select>
                                        </p> 
                                        
                                        <p>
                                           <label class="required" for="rabat">Wartość rabatu:</label>
                                           <input type="text" size="5" name="rabat" class="calkowita" id="rabat" /> % &nbsp; <span id="info_rabat" style="color:#999">minimalna wartość to 15%</span>
                                        </p>        

                                        <div class="maleInfo" style="padding-left:30px;margin-top:10px">
                                            <div style="margin-bottom:5px">Minimalne wartości rabatów wynoszą:</div>
                                            - 15% na drugą sztukę,<br />
                                            - 30% na trzecią,<br />
                                            - 40% na czwartą,<br />
                                            - 50% na piątą.<br />
                                            - 100% oznacza, że dany przedmiot jest gratis.                        
                                        </div>
                                        
                                    </div>
                                    
                                </div>

                                <div class="przyciski_dolne">
                                  <input type="submit" class="przyciskNon" value="Zapisz rabat" />
                                  <button type="button" class="przyciskNon" onclick="cofnij('allegro_aukcje','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','allegro');">Powrót</button> 
                                </div>
                                
                            <?php } ?>
                        
                        <?php } else { ?>
                            
                            <div class="pozycja_edytowana">
                        
                                <?php echo $wynik; ?>

                            </div>
                            
                            <div class="przyciski_dolne">
                              <button type="button" class="przyciskNon" onclick="cofnij('allegro_aukcje','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','allegro');">Powrót</button> 
                            </div>                    
                            
                        <?php }
                        
                    } else {
                
                        echo '<div class="pozycja_edytowana">Brak danych do wyświetlenia</div>';
                
                    }

                } else {
                
                    echo '<div class="pozycja_edytowana">Brak danych do wyświetlenia</div>';
                
                }
                $db->close_query($sql);
                unset($zapytanie);               
                ?>

              </div>                      
              </form>

        </div>    
    
        <?php
        include('stopka.inc.php');

    } else {
    
      Funkcje::PrzekierowanieURL('index.php');
      
    }
      
}