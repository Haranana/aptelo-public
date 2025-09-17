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
        $db->delete_query('orders_status_history' , " orders_status_history_id = '".(int)$_POST["status_id"]."'");  

        $pola = array(array('orders_status',$filtr->process($_POST["nowy_status"])));
        //
        $db->update_query('orders' , $pola, " orders_id = '".(int)$_POST["id"]."'");
        unset($pola);

        Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz='.(int)$_POST["id"].'&zakladka=3');
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Kasowanie pozycji</div>
    <div id="cont">
          
          <form action="sprzedaz/zamowienia_historia_usun.php" method="post" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Usuwanie danych dotyczących zamówienia numer : <?php echo $_GET['id_poz']; ?></div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select * from orders_status_history where orders_status_history_id = '" . (int)$_GET['status_id'] . "'";
            $sql = $db->open_query($zapytanie);

            $zamowienie = new Zamowienie((int)$_GET['id_poz']);

            unset($zamowienie->statusy[$_GET['status_id']]);

            end($zamowienie->statusy);
            $key = key($zamowienie->statusy);

            if ((int)$db->ile_rekordow($sql) > 0) {
            
                $info = $sql->fetch_assoc();

                ?> 
            
                <div class="pozycja_edytowana">
                
                    <input type="hidden" name="akcja" value="zapisz" />
                
                    <input type="hidden" name="id" value="<?php echo (int)$_GET['id_poz']; ?>" />
                    <input type="hidden" name="status_id" value="<?php echo (int)$_GET['status_id']; ?>" />
                    <input type="hidden" name="nowy_status" value="<?php echo $zamowienie->statusy[$key]['status_id']; ?>" />

                    <p>
                      Czy skasować pozycje ?
                    </p>   
                    
                    <p>
                      Status zamówienia: <?php echo Sprzedaz::pokazNazweStatusuZamowienia($info['orders_status_id'], $_SESSION['domyslny_jezyk']['id']); ?>
                    </p>   
                    
                    <?php if ( !empty($info['comments']) ) { ?>
                    
                    <p>Komentarz:</p>
                    
                    <div style="padding:10px">
                       <?php echo $info['comments']; ?>
                    </div>   
                    
                    <?php } ?>

                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Usuń dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('zamowienia_szczegoly','<?php echo Funkcje::Zwroc_Get(array('x','y')); ?>','sprzedaz');">Powrót</button> 
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