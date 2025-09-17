<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
      
        $id_edytowanej_pozycji = (int)$_POST['id'];
        $id_klienta = (int)$_POST['id_poz'];      
      
        $pola = array(array('entry_company',$filtr->process($_POST['nazwa_firmy'])),
                      array('entry_firstname',$filtr->process($_POST['imie'])),
                      array('entry_lastname',$filtr->process($_POST['nazwisko'])),
                      array('entry_street_address',$filtr->process($_POST['ulica'])),
                      array('entry_postcode',$filtr->process($_POST['kod_pocztowy'])),
                      array('entry_city',$filtr->process($_POST['miasto'])),
                      array('entry_country_id',(int)$_POST['panstwo']),
                      array('entry_zone_id',(( isset($_POST['wojewodztwo'])) ? (int)$_POST['wojewodztwo'] : '' )),
                      array('entry_telephone',$filtr->process($_POST['telefon'])));
                  
        //			
        $db->update_query('address_book' , $pola, " address_book_id = '" . $id_edytowanej_pozycji . "'");	
        unset($pola);
        //
        Funkcje::PrzekierowanieURL('klienci_edytuj.php?id_poz=' . (int)$id_klienta . '&zakladka=1');
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Edycja pozycji</div>
    <div id="cont">
    
          <script>
          $(document).ready(function() {
            $("#klienciForm").validate({
              rules: {
                imie: {
                  required: true
                },
                nazwisko: {
                  required: true
                },                
                ulica: {
                  required: true
                },
                kod_pocztowy: {
                  required: true
                },
                miasto: {
                  required: true
                },
                kraj: {
                  required: true
                }
              }
            });

            $("#selection").change( function() {
              $("#selectionresult").html('<img src="obrazki/_loader_small.gif">');
              $.ajax({
                  type: "post",
                  data: "data=" + $(this).val(),
                  url: "ajax/wybor_wojewodztwa.php",
                  success: function(msg){
                    if (msg != '') { 
                      $("#selectionresult").html(msg).show(); 
                     } else { 
                      $("#selectionresult").html('<em>Brak</em>'); 
                    }
                  }
              });
            });

          });
          </script>        

          <?php
          
          if ( !isset($_GET['id_poz']) ) {
               $_GET['id_poz'] = 0;
          }             
          if ( !isset($_GET['id']) ) {
               $_GET['id'] = 0;
          }           
          
          $zapytanie = "SELECT c.customers_id, 
                               a.address_book_id, 
                               a.entry_company, 
                               a.entry_firstname, 
                               a.entry_lastname, 
                               a.entry_street_address, 
                               a.entry_postcode, 
                               a.entry_city, 
                               a.entry_country_id, 
                               a.entry_zone_id,
                               a.entry_telephone
                          FROM customers c 
                     LEFT JOIN address_book a ON a.customers_id = c.customers_id
                         WHERE a.address_book_id = '" . (int)$_GET['id'] . "' AND c.customers_id = '" . (int)$_GET['id_poz'] . "'";
                   
          $sql = $db->open_query($zapytanie);
            
          if ((int)$db->ile_rekordow($sql) > 0) {

            $info = $sql->fetch_assoc();
            
            ?>
            
            <form action="klienci/klienci_adres_edytuj.php" method="post" id="klienciForm" class="cmxform">          

              <div class="poleForm">
                <div class="naglowek">Edycja danych adresowych</div>
                
                    <div class="pozycja_edytowana">
                        
                        <div class="info_content">
                    
                        <input type="hidden" name="akcja" value="zapisz" />
                    
                        <input type="hidden" name="id" value="<?php echo (int)$_GET['id']; ?>" />
                        <input type="hidden" name="id_poz" value="<?php echo (int)$_GET['id_poz']; ?>" />
                        
                        <p>
                            <label class="required" for="imie">Imię:</label>
                            <input type="text" name="imie" id="imie" size="53" value="<?php echo $info['entry_firstname']; ?>" class="required" />
                        </p> 
                        
                        <p>
                            <label class="required" for="nazwisko">Nazwisko:</label>
                            <input type="text" name="nazwisko" id="nazwisko" size="53" value="<?php echo $info['entry_lastname']; ?>" class="required" />
                        </p>                         
                        
                        <p>
                            <label for="nazwa_firmy">Nazwa firmy:</label>
                            <input type="text" name="nazwa_firmy" id="nazwa_firmy" size="53" value="<?php echo $info['entry_company']; ?>" />
                        </p> 
                        
                        <p>
                            <label for="telefon">Telefon:</label>
                            <input type="text" name="telefon" id="telefon" size="25" value="<?php echo $info['entry_telephone']; ?>" />
                        </p>                                                
                        
                        <p>
                            <label class="required" for="ulica">Adres:</label>
                            <input type="text" name="ulica" id="ulica" size="53" value="<?php echo $info['entry_street_address']; ?>" class="required" />
                        </p>                         
                        
                        <p>
                            <label class="required" for="kod_pocztowy">Kod pocztowy:</label>
                            <input type="text" name="kod_pocztowy" id="kod_pocztowy" size="25" value="<?php echo $info['entry_postcode']; ?>" class="required" />
                        </p>

                        <p>
                            <label class="required" for="miasto">Miejscowość:</label>
                            <input type="text" name="miasto" id="miasto" size="53" value="<?php echo $info['entry_city']; ?>" class="required" />
                        </p>
                        
                        <p>
                          <label class="required" for="selection">Kraj:</label>
                          <?php
                          $tablicaPanstw = Klienci::ListaPanstw();
                          echo Funkcje::RozwijaneMenu('panstwo', $tablicaPanstw, $info['entry_country_id'], 'id="selection"'); ?>
                        </p>

                        <?php if ( KLIENT_POKAZ_WOJEWODZTWO == 'tak' ) { ?>
                          <p>
                            <label>Województwo:</label>
                            <?php
                            $tablicaWojewodztw = Klienci::ListaWojewodztw($info['entry_country_id']);
                            echo '<span id="selectionresult">'.Funkcje::RozwijaneMenu('wojewodztwo', $tablicaWojewodztw, $info['entry_zone_id']).'</span>';
                            ?>
                          </p>
                        <?php } ?>

                        </div>
                     
                    </div>

                    <div class="przyciski_dolne">
                      <input type="submit" class="przyciskNon" value="Zapisz dane" />
                      <button type="button" class="przyciskNon" onclick="cofnij('klienci_edytuj','<?php echo Funkcje::Zwroc_Wybrane_Get(array('zakladka','id_poz')); ?>','klienci');">Powrót</button>           
                    </div>

              </div>                      
            </form>

            <?php

          } else {
          
            ?>
            
            <div class="poleForm"><div class="naglowek">Edycja danych adresowych</div>
                <div class="pozycja_edytowana">Brak danych do wyświetlenia</div>
            </div>
            
            <?php

          }

          $db->close_query($sql);
          unset($zapytanie, $info);            
          ?>

    </div>    
    
    <?php
    include('stopka.inc.php');

}
?>