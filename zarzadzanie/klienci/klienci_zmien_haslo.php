<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

        $zakodowane_haslo = Funkcje::zakodujHaslo($filtr->process($_POST["password"]));
        $pola = array(
                array('customers_password',$zakodowane_haslo)
        );
        //			
        $db->update_query('customers' , $pola, " customers_id = '".(int)$_POST["id"]."'");	
        unset($pola);
        //
        Funkcje::PrzekierowanieURL('klienci.php?id_poz='.(int)$_POST["id"].Funkcje::Zwroc_Get(array('id_poz','x','y'),true));
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Edycja pozycji</div>
    <div id="cont">
          
          <script>
          $(document).ready(function() {
            $("#slownikForm").validate({
              rules: {
                password: {
                  required: true
                }
              },
              messages: {
                password: {
                  required: "Pole jest wymagane."
                }          
              }
            });
          });
          </script>        

          <form action="klienci/klienci_zmien_haslo.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="slownikForm" class="cmxform">          

          <div class="poleForm">
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select * from customers where customers_id = '" . (int)$_GET['id_poz'] . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
            
                $info = $sql->fetch_assoc();
                ?>
                <div class="naglowek">Zmiana hasła klienta : <?php echo $info['customers_firstname'] . ' ' . $info['customers_lastname']; ?></div>
            
                <div class="pozycja_edytowana">
                    
                    <div class="info_content">
                
                    <input type="hidden" name="akcja" value="zapisz" />
                
                    <input type="hidden" name="id" value="<?php echo (int)$_GET['id_poz']; ?>" />
                    
                    <p>
                      <label class="required" for="password">Hasło:</label>
                      <input type="password" name="password" id="password" value="" size="53" />
                    </p>

                    <p>
                      <label class="required" for="nowe_haslo_powtorz">Powtórz hasło:</label>
                      <input type="password" name="nowe_haslo_powtorz" id="nowe_haslo_powtorz" value="" size="53" equalTo="#password" />
                    </p>

                    </div>
                 
                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Zapisz dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('klienci','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','klienci');">Powrót</button>           
                </div>                 

                </div>                      
            <?php
            
            $db->close_query($sql);
            unset($info);            
            
            } else {
            
                echo '<div class="pozycja_edytowana">Brak danych do wyświetlenia</div>';
            
            }
            ?>

          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}