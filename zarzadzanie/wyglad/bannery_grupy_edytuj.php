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
                array('banners_group_title',$filtr->process($_POST['opis'])));
                
        //			
        $db->update_query('banners_group' , $pola, " banners_group_id = '".(int)$_POST["id"]."'");	
        unset($pola);
        
        //
        Funkcje::PrzekierowanieURL('bannery_grupy.php?id_poz='.(int)$_POST["id"]);
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Edycja pozycji</div>
    <div id="cont">
          
          <script>
          $(document).ready(function() {
            $("#wygladForm").validate({
              rules: {
                opis: {
                  required: true
                }                
              },
              messages: {
                opis: {
                  required: "Pole jest wymagane."
                }                 
              }
            }); 
          });        
          </script>         

          <form action="wyglad/bannery_grupy_edytuj.php" method="post" id="wygladForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Edycja danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select * from banners_group where banners_group_id = '" . (int)$_GET['id_poz'] . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
            
                $info = $sql->fetch_assoc();
                ?>
            
                <div class="pozycja_edytowana">
                
                    <div class="info_content">
                
                    <input type="hidden" name="akcja" value="zapisz" />
                
                    <input type="hidden" name="id" value="<?php echo (int)$_GET['id_poz']; ?>" />

                    <p>
                        <label class="required" for="kod">Kod grupy:</label>
                        <input type="text" name="kod" id="kod" value="<?php echo $info['banners_group_code']; ?>" size="40" value="" disabled="disabled" /><em class="TipIkona"><b>Kod banneru jaki będzie używany w szablonach - nie może zawierać spacji i polskich znaków - musi być unikalny - np BANNERY_ANIMACJA</b></em>
                    </p>
                    
                    <p>
                        <label class="required" for="opis">Opis grupy:</label>
                        <input type="text" name="opis" id="opis" value="<?php echo $info['banners_group_title']; ?>" size="80" /><em class="TipIkona"><b>Opis będzie wyświetlany przy dodawaniu nowych bannerów</b></em>
                    </p>
                    
                    </div>

                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Zapisz dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('bannery_grupy','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','wyglad');">Powrót</button>      
                </div>                 

                <?php

                unset($info);            
            
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

}
