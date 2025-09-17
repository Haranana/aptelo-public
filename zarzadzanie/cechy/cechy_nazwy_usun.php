<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //			
        $db->delete_query('products_options' , " products_options_id = '".(int)$_POST["id"]."'");
        //
        Funkcje::PrzekierowanieURL('cechy.php');
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Kasowanie pozycji</div>
    <div id="cont">
          
          <form action="cechy/cechy_nazwy_usun.php" method="post" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Usuwanie danych</div>
            
            <?php
            
            if ( !isset($_GET['id_cechy']) ) {
                 $_GET['id_cechy'] = 0;
            }    
            
            $zapytanie = "select * from products_options where products_options_id = '" . (int)$_GET['id_cechy'] . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
            
                $info = $sql->fetch_assoc();
            
                $sprawdzanie_produktow = "select options_id from products_attributes where options_id = '" . (int)$_GET['id_cechy'] . "'";
                $sql_produkty = $db->open_query($sprawdzanie_produktow);
                
                if ((int)$db->ile_rekordow($sql_produkty) == 0) {

                    $sprawdzanie_cech = "select pop.products_options_id, pop.products_options_values_id, po.products_options_values_id from products_options_values po, products_options_values_to_products_options pop where pop.products_options_values_id = po.products_options_values_id and products_options_id = '" . (int)$_GET['id_cechy'] . "'";
                    $sql_cechy = $db->open_query($sprawdzanie_cech);

                    if ((int)$db->ile_rekordow($sql_cechy) == 0) {                
                    ?>            
            
                        <div class="pozycja_edytowana">
                        
                            <input type="hidden" name="akcja" value="zapisz" />
                        
                            <input type="hidden" name="id" value="<?php echo (int)$_GET['id_cechy']; ?>" />
                            
                            <p>
                              Czy skasować pozycje ?
                            </p>   

                        </div>

                        <div class="przyciski_dolne">
                          <input type="submit" class="przyciskNon" value="Usuń dane" />
                          <button type="button" class="przyciskNon" onclick="cofnij('cechy','<?php echo Funkcje::Zwroc_Get(array('x','y')); ?>','cechy');">Powrót</button> 
                        </div>
                        
                    <?php } else { ?>
                    
                        <div class="pozycja_edytowana">
                            <p>
                                Do cechy <span style="font-weight:bold"><?php echo $info['products_options_name']; ?></span> przypisane są wartości. <br /><br />
                                <span class="ostrzezenie">Nie można usunąć cechy dopóki wartości cechy będą do niej przypisane.</span>
                            </p>
                        </div>
                        
                        <div class="przyciski_dolne">
                          <button type="button" class="przyciskNon" onclick="cofnij('cechy','<?php echo Funkcje::Zwroc_Get(array('x','y')); ?>','cechy');">Powrót</button> 
                        </div>                        

                    <?php
                    }  
                    $db->close_query($sql_cechy);
                    unset($sprawdzanie_cech);                       
                
                } else { ?>
                
                    <div class="pozycja_edytowana">
                        <p>
                            Do wartości cechy <span style="font-weight:bold"><?php echo $info['products_options_name']; ?></span> przypisane są produkty. <br /><br />
                            <span class="ostrzezenie">Nie można usunąć wartości cechy dopóki produkty będą do niej przypisane.</span>
                        </p>
                    </div>
                    
                    <div class="przyciski_dolne">
                      <button type="button" class="przyciskNon" onclick="cofnij('cechy','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','cechy');">Powrót</button> 
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