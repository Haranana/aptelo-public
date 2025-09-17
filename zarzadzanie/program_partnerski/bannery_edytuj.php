<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        $pola = array(array('pp_description',$filtr->process($_POST['nazwa'])),
                      array('pp_image',$filtr->process($_POST['zdjecie'])),
                      array('pp_image_alt',$filtr->process($_POST['alt'])));
        //			
        $db->update_query('pp_banners' , $pola, " id = '".(int)$_POST["id"]."'");	
        unset($pola);
        //
        Funkcje::PrzekierowanieURL('bannery.php?id_poz='.(int)$_POST["id"]);
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Edycja pozycji</div>
    <div id="cont">
          
          <script>
          $(document).ready(function() {
            $("#ppForm").validate({
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

          <form action="program_partnerski/bannery_edytuj.php" method="post" id="ppForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Edycja danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select * from pp_banners where id = '" . (int)$_GET['id_poz'] . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
            
                $info = $sql->fetch_assoc();
                ?>
            
                <div class="pozycja_edytowana">
                    
                    <div class="info_content">
                
                    <input type="hidden" name="akcja" value="zapisz" />
                
                    <input type="hidden" name="id" value="<?php echo (int)$_GET['id_poz']; ?>" />
                    
                    <p>
                      <label class="required" for="nazwa">Opis banneru:</label>
                      <textarea cols="62" rows="7" name="nazwa" id="nazwa"><?php echo $info['pp_description']; ?></textarea> 
                    </p>                  

                    <p>
                      <label for="foto">Ścieżka obrazka:</label>           
                      <input type="text" name="zdjecie" size="95" value="<?php echo $info['pp_image']; ?>" class="obrazek" ondblclick="openFilePPBrowser('foto')" id="foto" autocomplete="off" /><em class="TipIkona"><b>Kliknij dwukrotnie w to pole żeby otworzyć okno przeglądarki plików</b></em>
                    </p>      

                    <p>
                        <label for="alt">Opis obrazka:</label>
                        <input type="text" name="alt" id="alt" value="<?php echo $info['pp_image_alt']; ?>" size="90" />
                    </p>                    

                    </div>
                 
                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Zapisz dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('bannery','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','program_partnerski');">Powrót</button>             
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