<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        $pola = array(
                array('customers_name',$filtr->process($_POST["klient"])),
                array('customers_telephone',$filtr->process($_POST["telefon"])),
                array('customers_email_address',$filtr->process($_POST["email"])));

        //			
        $db->update_query('orders_fast' , $pola, " orders_fast_id = '".(int)$_POST["id"]."'");		
        unset($pola);
        
        // szuka pierwszego statusu zamowienia zeby zaktualizowac komentarz
        $zapytanie = "select orders_fast_status_history_id from orders_fast_status_history where orders_fast_id = '" . (int)$_POST['id'] . "' order by date_added asc limit 1";
        $sql = $db->open_query($zapytanie);        
        $info = $sql->fetch_assoc();
        //
        $pola = array(array('comments',$filtr->process($_POST["uwagi"])));
        $db->update_query('orders_fast_status_history' , $pola, " orders_fast_status_history_id = '" . $info['orders_fast_status_history_id'] . "'");		
        unset($pola);       
        //
        $db->close_query($sql);
        unset($info, $zapytanie);          
        //
        Funkcje::PrzekierowanieURL('zamowienia_kontakt_szczegoly.php?id_poz='.(int)$_POST["id"].'&zakladka='.$filtr->process($_POST["zakladka"]));
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Edycja pozycji</div>
    <div id="cont">
          
          <script>
          $(document).ready(function() {
            $("#zamowienieForm").validate({
              rules: {
                klient: {
                  required: true
                }, 
                email: {
                  required: true, email: true
                }, 
                telefon: {
                  required: true
                }            
              },
              messages: {
                klient: {
                  required: "Pole jest wymagane."
                },
                email: {
                  required: "Pole jest wymagane."
                },
                telefon: {
                  required: "Pole jest wymagane."
                }                    
              }
            });  
          });

          </script>     

          <form action="sprzedaz/zamowienia_kontakt_edytuj.php" method="post" id="zamowienieForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Edycja danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            } 
            if ( !isset($_GET['zakladka']) ) {
                 $_GET['zakladka'] = '0';
            }            
            
            $zapytanie = "select * from orders_fast where orders_fast_id = '" . (int)$_GET['id_poz'] . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
            
                $info = $sql->fetch_assoc();
                ?>
            
                <div class="pozycja_edytowana">
                
                    <div class="info_content">

                    <input type="hidden" name="akcja" value="zapisz" />
                
                    <input type="hidden" name="id" value="<?php echo (int)$_GET['id_poz']; ?>" />
                    <input type="hidden" name="zakladka" value="<?php echo (int)$_GET['zakladka']; ?>" />
                    
                    <p>
                      <label class="required" for="id_tmp">Nr zamówienia:</label>
                      <input type="text" name="id_tmp" size="5" value="<?php echo $info['orders_fast_id']; ?>" disabled="disabled" />     
                    </p>
                    
                    <p>
                      <label class="required" for="klient">Klient:</label>
                      <input type="text" name="klient" id="klient" size="55" value="<?php echo $info['customers_name']; ?>" />     
                    </p>
                    
                    <p>
                      <label class="required" for="email">Adres email:</label>
                      <input type="text" name="email" id="email" size="55" value="<?php echo $info['customers_email_address']; ?>" />     
                    </p>

                    <p>
                      <label class="required" for="telefon">Nr telefonu:</label>
                      <input type="text" name="telefon" id="telefon" size="35" value="<?php echo $info['customers_telephone']; ?>" />     
                    </p> 

                    <p>
                      <label for="uwagi">Uwagi klienta przesłane w zamówieniu:</label>

                      <?php
                      // szuka pierwszego statusu zamowienia zeby zaktualizowac komentarz
                      $zapytanie_komentarz = "select comments from orders_fast_status_history where orders_fast_id = '" . $info['orders_fast_id'] . "' order by date_added asc limit 1";
                      $sql_komentarz = $db->open_query($zapytanie_komentarz);        
                      $info_komentarz = $sql_komentarz->fetch_assoc();
                      //
                      echo '<textarea name="uwagi" id="uwagi" rows="5" cols="70">' . $info_komentarz['comments'] . '</textarea>';   
                      //
                      $db->close_query($sql_komentarz);
                      unset($info_komentarz, $zapytanie_komentarz);                        
                      ?>

                    </p> 
                    
                    </div>

                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Zapisz dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('zamowienia_kontakt_szczegoly','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','sprzedaz');">Powrót</button>           
                </div>                 

            <?php
            
            $db->close_query($sql);
            unset($info);            
            
            } else {
            
                echo '<div class="pozycja_edytowana">Brak danych do wyświetlenia</div>';
            
            }
            ?>

          </div>                      
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}
