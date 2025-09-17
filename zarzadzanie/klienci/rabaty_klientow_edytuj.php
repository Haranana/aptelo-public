<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        $pola = array(array('customers_discount',(float)$_POST["rabat"]));
        //			
        $db->update_query('customers' , $pola, " customers_id = '".(int)$_POST["id"]."'");	
        unset($pola);
        //
        if ((float)$filtr->process($_POST["rabat"]) != 0) {
            Funkcje::PrzekierowanieURL('rabaty_klientow.php?id_poz='.(int)$_POST["id"]);
          } else {
            Funkcje::PrzekierowanieURL('rabaty_klientow.php');
        }
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
                rabat: {
                  range: [-100, 0],
                  number: true
                }
              }
            });
          });
          </script>        

          <form action="klienci/rabaty_klientow_edytuj.php" method="post" id="slownikForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Edycja danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select * from customers where customers_id = '" . (int)$_GET['id_poz'] . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
            
                $info = $sql->fetch_assoc();
                ?>
            
                <div class="pozycja_edytowana">
                    
                    <div class="info_content">
                
                    <input type="hidden" name="akcja" value="zapisz" />
                
                    <input type="hidden" name="id" value="<?php echo (int)$_GET['id_poz']; ?>" />
                    
                    <p>
                      <label for="rabat">Rabat [%]:</label>
                      <input type="text" name="rabat" id="rabat" value="<?php echo $info['customers_discount']; ?>" size="5" /><em class="TipIkona"><b>liczba z zakresu od -100 do 0</b></em>
                    </p>

                    </div>
                 
                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Zapisz dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('rabaty_klientow','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','klienci');">Powrót</button>           
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