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
                array('zone_name',$filtr->process($_POST["nazwa"])),
                array('zone_code',$filtr->process($_POST["kod"]))
        );
        //	
        $db->update_query('zones' , $pola, " zone_id = '".(int)$_POST["id"]."'");	
        
        unset($pola);
        
        //
        Funkcje::PrzekierowanieURL('kraje_wojewodztwa.php?id_poz='.(int)$_POST["id"].'&kraj_id='.(int)$_POST["kraj_id"]);
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
                nazwa: {
                  required: true
                }
              },
              messages: {
                nazwa: {
                  required: "Pole jest wymagane."
                }               
              }
            });
          });
          </script>     

          <form action="slowniki/kraje_wojewodztwa_edytuj.php" method="post" id="slownikForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Edycja danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            if ( !isset($_GET['kraj_id']) ) {
                 $_GET['kraj_id'] = 0;
            }             
            
            $zapytanie = "select * from zones where zone_id = '" . (int)$_GET['id_poz'] . "' and zone_country_id = '" . (int)$_GET['kraj_id'] . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
            
                $info = $sql->fetch_assoc();
                ?>
                <div class="pozycja_edytowana">
                
                    <div class="info_content">
                
                    <div>
                        <input type="hidden" name="akcja" value="zapisz" />
                        <input type="hidden" name="id" value="<?php echo (int)$_GET['id_poz']; ?>" />
                        <input type="hidden" name="kraj_id" value="<?php echo $_GET["kraj_id"]; ?>" />
                    </div>
                    
                    <p>
                      <label class="required" for="nazwa">Nazwa:</label>
                      <input type="text" name="nazwa" size="53" value="<?php echo Funkcje::formatujTekstInput($info['zone_name']); ?>" id="nazwa" />
                    </p>

                    <p>
                      <label for="kod">Kod:</label>
                      <input type="text" name="kod" size="5" value="<?php echo $info['zone_code']; ?>" id="kod" />
                    </p>
                        
                    </div>

                </div>

            <div class="przyciski_dolne">
              <input type="submit" class="przyciskNon" value="Zapisz dane" />
              <button type="button" class="przyciskNon" onclick="cofnij('kraje_wojewodztwa','<?php echo Funkcje::Zwroc_Get(array('x','y')); ?>','slowniki');">Powrót</button>   
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