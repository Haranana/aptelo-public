<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //			
        $db->delete_query('newsletters_group' , " newsletters_group_id = '".(int)$_POST["id"]."'"); 
        
        // czyszczenie tablicy klientow
        // szuka czy grupa jest przypisana do klientow
        $zapytanie = "select customers_id, customers_newsletter_group from customers";
        $sqld = $db->open_query($zapytanie);  
        //
        while ($info = $sqld->fetch_assoc()) {
            //
            if ( in_array((string)$_POST["id"], explode(',', (string)$info['customers_newsletter_group']) ) ) {
                 //
                 $nowaTablica = explode(',', (string)$info['customers_newsletter_group']);
                 foreach ( $nowaTablica as $id => $wartosc ) {
                    //
                    if ( $wartosc == (int)$_POST["id"] || $wartosc == '' ) {
                         unset( $nowaTablica[$id] );
                    }
                    //
                 }                     
                 //
                 $pola = array( array('customers_newsletter_group', implode(',', (array)$nowaTablica)) );
                 $db->update_query('customers' , $pola, " customers_id = '".$info['customers_id']."'");
                 unset($pola);
                 //
            }
            //
        }
        //
        $db->close_query($sqld);
        unset($zapytanie); 
        //   
        
        // czyszczenie tablicy newslettera
        // szuka czy grupa jest przypisana do newslettera
        $zapytanie = "select newsletters_id, customers_newsletter_group from newsletters";
        $sqld = $db->open_query($zapytanie);  
        //
        while ($info = $sqld->fetch_assoc()) {
            //
            if ( in_array((string)$_POST["id"], explode(',', (string)$info['customers_newsletter_group']) ) ) {
                 //
                 $nowaTablica = explode(',', (string)$info['customers_newsletter_group']);
                 foreach ( $nowaTablica as $id => $wartosc ) {
                    //
                    if ( $wartosc == (int)$_POST["id"] || $wartosc == '' ) {
                         unset( $nowaTablica[$id] );
                    }
                    //
                 }                     
                 //
                 $pola = array( array('customers_newsletter_group', implode(',', (array)$nowaTablica)) );
                 $db->update_query('newsletters' , $pola, " newsletters_id = '".$info['newsletters_id']."'");
                 unset($pola);
                 //
            }
            //
        }
        //
        $db->close_query($sqld);
        unset($zapytanie); 
        //           
        Funkcje::PrzekierowanieURL('newsletter_grupy.php');
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Kasowanie pozycji</div>
    <div id="cont">
          
          <form action="newsletter/newsletter_grupy_usun.php" method="post" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Usuwanie danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select * from newsletters_group where newsletters_group_id = '" . (int)$_GET['id_poz'] . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
            
                $info = $sql->fetch_assoc();
                ?>            
            
                <div class="pozycja_edytowana">
                
                    <input type="hidden" name="akcja" value="zapisz" />
                
                    <input type="hidden" name="id" value="<?php echo (int)$_GET['id_poz']; ?>" />
                    
                    <p>
                      Czy skasować pozycje ?
                    </p>   
                 
                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Usuń dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('newsletter_grupy','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','newsletter');">Powrót</button> 
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