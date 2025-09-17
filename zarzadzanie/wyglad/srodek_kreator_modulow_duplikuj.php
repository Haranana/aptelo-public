<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz' && ( Wyglad::TypSzablonu() == true || ( isset($_SESSION['programista']) && $_SESSION['programista'] == '1' ))) {
         //
         $zapytanie = "select * from theme_modules where modul_id = '".(int)$_POST['id']."'";
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
                  if ( $klucz != 'modul_id' ) {
                       //
                       if ( $klucz == 'modul_status' ) {
                            //
                            $pola[] = array( 'modul_status', 0 );
                            //                            
                       } else {
                            //
                            $pola[] = array( $klucz, $wartosc );
                            //
                       }
                       //
                  }
                  //
             }
             //
             $id_dodanej_pozycji = $db->insert_query('theme_modules' , $pola, '', false, true);
             unset($pola);  
             //
             $db->close_query($sql);
             //        
             $ile_jezykow = Funkcje::TablicaJezykow();
             //
             for ( $w = 0, $c = count($ile_jezykow); $w < $c; $w++ ) {
                   //
                   $zapytanie = "select * from theme_modules_description where modul_id = '".(int)$_POST['id']."' and language_id = '".$ile_jezykow[$w]['id']."'";
                   $sql = $db->open_query($zapytanie);
                   //       
                   $info = $sql->fetch_assoc();
                   //
                   $pola = array();
                   //
                   foreach ( $info as $klucz => $wartosc ) {
                        //
                        if ( $klucz != 'modul_id' ) {
                             //
                             $pola[] = array( $klucz, $wartosc );
                             //
                        } else {
                             //
                             $pola[] = array( 'modul_id', $id_dodanej_pozycji );
                             //
                        }
                        //
                   }
                   //
                   $db->insert_query('theme_modules_description' , $pola, '', false, true);
                   unset($pola);  
                   //
                   $db->close_query($sql);
                   //
             }                
             //
         }
         //
         Funkcje::PrzekierowanieURL('srodek.php?id_poz=' . $id_dodanej_pozycji);
         
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Duplikowanie pozycji</div>
    <div id="cont">
          
          <form action="wyglad/srodek_kreator_modulow_duplikuj.php" method="post" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Duplikowanie pozycji</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select * from theme_modules where modul_id = '" . (int)$_GET['id_poz'] . "' and modul_type = 'kreator'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0 && ( Wyglad::TypSzablonu() == true || ( isset($_SESSION['programista']) && $_SESSION['programista'] == '1' ))) {
                ?>            
            
                <div class="pozycja_edytowana">
                
                    <input type="hidden" name="akcja" value="zapisz" />
                
                    <input type="hidden" name="id" value="<?php echo (int)$_GET['id_poz']; ?>" />
                    
                    <p>
                      Czy zduplikować moduł kreatora ?                    
                    </p>   

                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Duplikuj moduł" />
                  <button type="button" class="przyciskNon" onclick="cofnij('srodek','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','wyglad');">Powrót</button>    
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