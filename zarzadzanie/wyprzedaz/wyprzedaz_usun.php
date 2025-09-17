<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        $pola = array(array('sale_status','0'),
                      array('products_old_price','0'));
        //
        for ($x = 2; $x <= ILOSC_CEN; $x++) {
            //
            $pola[] = array('products_old_price_'.$x,'0');
            //
        }
        //
        
        $sql_wynik = $db->update_query('products' , $pola, " products_id = '".(int)$_POST["id"]."'"); 
        
        // aktualizowanie cen dla kombinacji cech
        if ( isset($_POST['ceny_cechy']) ) {
          
            $pola = array(array('products_stock_old_price','0'));
            //
            for ($x = 2; $x <= ILOSC_CEN; $x++) {
                //
                $pola[] = array('products_stock_old_price_'.$x,'0');
                //
            }
            //
            
            $sql_wynik = $db->update_query('products_stock' , $pola, " products_id = '".(int)$_POST["id"]."'"); 

        }

        //
        Funkcje::PrzekierowanieURL('wyprzedaz.php');
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Kasowanie pozycji</div>
    <div id="cont">
          
          <form action="wyprzedaz/wyprzedaz_usun.php" method="post" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Usuwanie danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select * from products where products_id= '" . (int)$_GET['id_poz'] . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
              
                $info = $sql->fetch_assoc();  
                ?>            
            
                <div class="pozycja_edytowana">
                
                    <input type="hidden" name="akcja" value="zapisz" />
                
                    <input type="hidden" name="id" value="<?php echo (int)$_GET['id_poz']; ?>" />
                    
                    <?php if ( $info['options_type'] == 'ceny' ) { ?>
                    
                        <input type="hidden" name="ceny_cechy" value="1" />
                    
                    <?php } ?>
                    
                    <p>
                      Czy skasować wyprzedaż dla tego produktu ?                    
                    </p>   
                    
                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Usuń dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('wyprzedaz','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>');">Powrót</button>    
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