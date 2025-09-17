<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ( $_SESSION['grupaID'] != '1' ) {
     $prot->wyswietlStrone = false;
}

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        $db->delete_query('complaints_status_history' , " complaints_status_history_id = '".(int)$_POST["status_id"]."'");  

        // nowy status
        $zapytanie = "select * from complaints_status_history where complaints_id = '" . (int)$_POST['id'] . "' order by date_added desc";
        $sql = $db->open_query($zapytanie);

        if ((int)$db->ile_rekordow($sql) > 0) {
        
            $info = $sql->fetch_assoc();
            //
            $pola = array(array('complaints_status_id',$info['complaints_status_id']));
            $db->update_query('complaints' , $pola, " complaints_id = '".(int)$_POST["id"]."'");
            //
            unset($pola, $info);

        }
        
        Funkcje::PrzekierowanieURL('reklamacje_szczegoly.php?id_poz='.(int)$_POST["id"].'&zakladka=1');
        
        $db->close_query($sql);
        unset($zapytanie, $info);   
            
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Kasowanie pozycji</div>
    <div id="cont">
          
          <form action="reklamacje/reklamacje_historia_usun.php" method="post" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Usuwanie danych reklamacji</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select * from complaints_status_history where complaints_status_history_id = '" . (int)$_GET['status_id'] . "'";
            $sql = $db->open_query($zapytanie);

            if ((int)$db->ile_rekordow($sql) > 0) {
            
                $info = $sql->fetch_assoc();

                ?> 
            
                <div class="pozycja_edytowana">
                
                    <input type="hidden" name="akcja" value="zapisz" />
                
                    <input type="hidden" name="id" value="<?php echo (int)$_GET['id_poz']; ?>" />
                    <input type="hidden" name="status_id" value="<?php echo (int)$_GET['status_id']; ?>" />

                    <p>
                      Czy skasować pozycje ?
                    </p>   
                    
                    <p>
                      Status reklamacji: <?php echo Reklamacje::pokazNazweStatusuReklamacji($info['complaints_status_id'], $_SESSION['domyslny_jezyk']['id']); ?>
                    </p>   
                    
                    <?php if ( !empty($info['comments']) ) { ?>
                    
                    <p>
                      Komentarz : <?php echo $info['comments']; ?>
                    </p>   
                    
                    <?php } ?>

                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Usuń dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('reklamacje_szczegoly','<?php echo Funkcje::Zwroc_Get(array('x','y','status_id')); ?>','reklamacje');">Powrót</button> 
                </div>

                <?php
                
            } else {
            
                echo '<div class="pozycja_edytowana">Brak danych do wyświetlenia</div>';
            
            }
            
            $db->close_query($sql);
            unset($zapytanie, $info);            
            ?>

          </div>                      
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}