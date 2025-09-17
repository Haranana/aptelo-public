<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
         //
         $zapytanie = "select * from offers where offers_id = '".(int)$_POST['id']."'";
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
                  if ( $klucz != 'offers_id' ) {
                       //
                       $pola[] = array( $klucz, $wartosc );
                       //
                  }
                  //
             }
             //
             $id_dodanej_pozycji = $db->insert_query('offers' , $pola, '', false, true);
             unset($pola);  
             //
             $db->close_query($sql);
             //
             // pozycje oferty
             //
             //
             $zapytanie = "select * from offers_products where offers_id = '".(int)$_POST['id']."'";
             $sql = $db->open_query($zapytanie);
             //             
             while ( $info = $sql->fetch_assoc() ) {
                  //
                  $pola = array();
                  //
                  foreach ( $info as $klucz => $wartosc ) {
                       //
                       if ( $klucz != 'id_products_offers' && $klucz != 'offers_id' ) {
                            //
                            $pola[] = array( $klucz, $wartosc );
                            //
                       }
                       //
                  } 
                  //
                  $pola[] = array('offers_id', $id_dodanej_pozycji);
                  //
                  if ( count($pola) > 1 ) {
                       //
                       $db->insert_query('offers_products' , $pola);
                       //
                  }
                  //
             }
             //
             $db->close_query($sql);
             //             
         }
         //
         Funkcje::PrzekierowanieURL('oferty.php?id_poz=' . $id_dodanej_pozycji);
         
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Duplikowanie pozycji</div>
    <div id="cont">
          
          <form action="oferty/oferty_duplikuj.php" method="post" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Duplikowanie pozycji</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select * from offers where offers_id = '" . (int)$_GET['id_poz'] . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
                ?>            
            
                <div class="pozycja_edytowana">
                
                    <input type="hidden" name="akcja" value="zapisz" />
                
                    <input type="hidden" name="id" value="<?php echo (int)$_GET['id_poz']; ?>" />
                    
                    <p>
                      Czy zduplikowąć ofertę ?                    
                    </p>   

                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Duplikuj ofertę" />
                  <button type="button" class="przyciskNon" onclick="cofnij('oferty','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>');">Powrót</button>    
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