<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
         //
         $zapytanie = "select * from comparisons where comparisons_id = '".(int)$_POST['id']."'";
         $sql = $db->open_query($zapytanie);
         //
         if ($db->ile_rekordow($sql) > 0) { 
             //
             $info = $sql->fetch_assoc();
             //
             $pola = array();
             //
             foreach ( $info as $klucz => $wartosc ) {
                  //
                  if ( $klucz != 'comparisons_duplicated' && $klucz != 'comparisons_last_export' && $klucz != 'comparisons_products_exported' && $klucz != 'comparisons_id' && $klucz != 'comparisons_plugin' ) {
                       //
                       $pola[] = array( $klucz, $wartosc );
                       //
                  }
                  //
             }
             //
             $pola[] = array('comparisons_duplicated',1);
             //
             $id_dodanej_pozycji = $db->insert_query('comparisons' , $pola, '', false, true);
             unset($pola);  
             //
             // jezeli jest duplikowanie z innego modulu
             if ( strpos((string)$info['comparisons_plugin'], '__') > -1 ) {
                  //
                  $podzial = explode('__', (string)$info['comparisons_plugin']);
                  $info['comparisons_plugin'] = $podzial[0];
                  //
             }
             //
             $pola = array();
             $pola[] = array('comparisons_plugin', $info['comparisons_plugin'] . '__' . $id_dodanej_pozycji);
             //
             $db->update_query('comparisons' , $pola, 'comparisons_id = ' . $id_dodanej_pozycji);
             //
             $db->close_query($sql);
             //        
         }
         //
         Funkcje::PrzekierowanieURL('porownywarki.php?id_poz=' . $id_dodanej_pozycji);
         
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Duplikowanie pozycji</div>
    <div id="cont">
          
          <form action="porownywarki/porownywarki_duplikuj.php" method="post" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Duplikowanie pozycji</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select * from comparisons where comparisons_id = '" . (int)$_GET['id_poz'] . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
                ?>            
            
                <div class="pozycja_edytowana">
                
                    <input type="hidden" name="akcja" value="zapisz" />
                
                    <input type="hidden" name="id" value="<?php echo (int)$_GET['id_poz']; ?>" />
                    
                    <p>
                      Czy zduplikować porównywarkę ?                    
                    </p>   

                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Duplikuj porównywarkę" />
                  <button type="button" class="przyciskNon" onclick="cofnij('porownywarki','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','porownywarki');">Powrót</button>    
                </div>

            <?php
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
?>