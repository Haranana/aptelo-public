<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //			
        $warunek = " products_options_values_id = '".(int)$_POST['id_poz']."'";
        $sql = $db->delete_query('products_options_values' , $warunek);  
        unset($warunek);
        $warunek = " products_options_values_id = '".(int)$_POST['id_poz']."'";
        $sql = $db->delete_query('products_options_values_to_products_options' , $warunek);  
        unset($warunek);     
        //
        Funkcje::PrzekierowanieURL('cechy.php?id_cechy=' . $filtr->process($_POST['id_cechy']) . Funkcje::Zwroc_Get(array('id_poz','id_cechy'),true));
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Kasowanie pozycji</div>
    <div id="cont">
          
          <form action="cechy/cechy_wartosci_usun.php" method="post" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Usuwanie danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select * from products_options_values where products_options_values_id = '" . (int)$_GET['id_poz'] . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
            
                $sprawdzanie_produktow = "select options_values_id from products_attributes where options_values_id = '" . (int)$_GET['id_poz'] . "'";
                $sql_produkty = $db->open_query($sprawdzanie_produktow);
                
                $info = $sql->fetch_assoc();
                
                if ((int)$db->ile_rekordow($sql_produkty) == 0) {
                ?>            
            
                    <div class="pozycja_edytowana">
                    
                        <input type="hidden" name="akcja" value="zapisz" />
                    
                        <input type="hidden" name="id_cechy" value="<?php echo (int)$_GET['id_cechy']; ?>" />
                        
                        <input type="hidden" name="id_poz" value="<?php echo (int)$_GET['id_poz']; ?>" />
                        
                        <p>
                          Czy skasować pozycje ?
                        </p>   

                    </div>

                    <div class="przyciski_dolne">
                      <input type="submit" class="przyciskNon" value="Usuń dane" />
                      <button type="button" class="przyciskNon" onclick="cofnij('cechy','<?php echo Funkcje::Zwroc_Get(array('id_poz','x','y')); ?>','cechy');">Powrót</button> 
                    </div>
                
                <?php } else { ?>
                
                    <div class="pozycja_edytowana">
                        <p>
                            Do wartości cechy <span style="font-weight:bold"><?php echo $info['products_options_values_name']; ?></span> przypisane są produkty. <br /><br />
                            <span class="ostrzezenie">Nie można usunąć wartości cechy dopóki produkty będą do niej przypisane.</span>    
                        </p>
                    </div>
                    
                    <div class="przyciski_dolne">
                      <button type="button" class="przyciskNon" onclick="cofnij('cechy','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_cechy')); ?>','cechy');">Powrót</button> 
                    </div>                    
                    
                <?php
                }
                
                $db->close_query($sql_produkty);
                unset($info, $sprawdzanie_produktow);                

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