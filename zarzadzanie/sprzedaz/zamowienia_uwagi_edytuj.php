<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        $pola = array(
                array('orders_adminnotes',$filtr->process($_POST["komentarzZamowienie"]))
        );
        //	
        $db->update_query('orders' , $pola, " orders_id = '".(int)$_POST["id"]."'");	
        unset($pola);
        //
        $pola = array(
                array('customers_dod_info',$filtr->process($_POST["komentarzKlient"]))
        );
        //	
        $db->update_query('customers' , $pola, " customers_id = '".(int)$_POST["id_uzytkownika"]."'");	
        unset($pola);
        //
        Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz='.(int)$_POST["id"].'&zakladka='.(int)$_POST["zakladka"]);
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Edycja pozycji</div>
    <div id="cont">
    
          <?php
          
          if ( !isset($_GET['id_poz']) ) {
               $_GET['id_poz'] = 0;
          }             
          if ( !isset($_GET['zakladka']) ) {
               $_GET['zakladka'] = '0';
          }           
          
          $zapytanie = "select o.*, c.customers_dod_info FROM orders o LEFT JOIN customers c ON c.customers_id = o.customers_id WHERE orders_id  = '" . (int)$_GET['id_poz'] . "'";
          $sql = $db->open_query($zapytanie);
            
          if ((int)$db->ile_rekordow($sql) > 0) {

            $info = $sql->fetch_assoc();
            
            ?>
            
            <form action="sprzedaz/zamowienia_uwagi_edytuj.php" method="post" id="zamowieniaForm" class="cmxform">          

              <div class="poleForm">
                <div class="naglowek">Edycja uwag do zamówienia - zamówienie numer : <?php echo $_GET['id_poz']; ?></div>
                
                    <div class="pozycja_edytowana">
                        
                        <div class="info_content">
                    
                        <input type="hidden" name="akcja" value="zapisz" />
                    
                        <input type="hidden" name="id" value="<?php echo (int)$_GET['id_poz']; ?>" />
                        <input type="hidden" name="zakladka" value="<?php echo (int)$_GET['zakladka']; ?>" />
                        <input type="hidden" name="id_uzytkownika" value="<?php echo (int)$info['customers_id']; ?>" />

                        <p>
                            <label for="komentarzKlient">Uwagi do klienta:</label>
                            <textarea cols="100" rows="10" name="komentarzKlient" id="komentarzKlient"><?php echo $info['customers_dod_info']; ?></textarea>
                        </p>

                        <p>
                            <label for="komentarzZamowienie">Uwagi do zamówienia:</label>
                            <textarea cols="100" rows="10" name="komentarzZamowienie" id="komentarzZamowienie"><?php echo $info['orders_adminnotes']; ?></textarea>
                        </p>


                        </div>
                     
                    </div>

                    <div class="przyciski_dolne">
                      <input type="submit" class="przyciskNon" value="Zapisz dane" />
                      <button type="button" class="przyciskNon" onclick="cofnij('zamowienia_szczegoly','<?php echo Funkcje::Zwroc_Wybrane_Get(array('zakladka','id_poz')); ?>','sprzedaz');">Powrót</button>           
                    </div>

              </div>                      
            </form>

            <?php

          } else {
          
            ?>
            
            <div class="poleForm"><div class="naglowek">Edycja uwag do zamówienia</div>
                <div class="pozycja_edytowana">Brak danych do wyświetlenia</div>
            </div>
            
            <?php

          }

          $db->close_query($sql);
          unset($zapytanie, $info);            
          ?>

    </div>    
    
    <?php
    include('stopka.inc.php');

}