<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz' && isset($_POST['id'])) {
         //
         $zapytaniel_tmp = "select * from categories where categories_id = '" . (int)$_POST['id'] . "'";
         $sql_tmp = $db->open_query($zapytaniel_tmp);
         //
         $id_dodanej_pozycji = 0;
         //
         if ( $db->ile_rekordow($sql_tmp) > 0) { 
              //
              $infs = $sql_tmp->fetch_assoc();
              //
              $id_dodanej_pozycji = Kategorie::DuplikujKategorie($infs['categories_id'], $infs['parent_id'], true, ((isset($_POST['podkategorie']) && (int)$_POST['podkategorie'] == 1) ? true : false), ((isset($_POST['produkty']) && (int)$_POST['produkty'] == 1) ? true : false));
              //
        }
        //
        $db->close_query($sql_tmp);
        unset($zapytaniel_tmp);
        //
        if ( $id_dodanej_pozycji > 0 ) {
             //
             Funkcje::PrzekierowanieURL('kategorie.php?id_poz=' . $id_dodanej_pozycji);
             //
        } else {
             //
             Funkcje::PrzekierowanieURL('kategorie.php');
             //
        }
        //
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Duplikowanie pozycji</div>
    <div id="cont">
          
          <form action="kategorie/kategorie_duplikuj.php" method="post" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Duplikowanie pozycji</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select * from categories where categories_id = '" . (int)$_GET['id_poz'] . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
                ?>            
            
                <div class="pozycja_edytowana">
                
                    <input type="hidden" name="akcja" value="zapisz" />
                
                    <input type="hidden" name="id" value="<?php echo (int)$_GET['id_poz']; ?>" />

                    <p>
                      Czy zduplikować kategorię ?                    
                    </p>  

                    <p style="margin-left:-15px">
                      <label>Czy zduplikować też podkategorie (jeżeli kategoria posiada podkategorie) ?</label>
                      <input type="radio" value="1" name="podkategorie" checked="checked" id="podkategorie_tak" /> <label class="OpisFor" for="podkategorie_tak">tak</label>
                      <input type="radio" value="0" name="podkategorie" id="podkategorie_nie" /> <label class="OpisFor" for="podkategorie_nie">nie</label>
                    </p> 

                    <p style="margin-left:-15px">
                      <label>Czy zduplikować też powiązania produktów z duplikowanymi kategoriami ?</label>
                      <input type="radio" value="1" name="produkty" checked="checked" id="produkty_tak" /> <label class="OpisFor" for="produkty_tak">tak</label>
                      <input type="radio" value="0" name="produkty" id="produkty_nie" /> <label class="OpisFor" for="produkty_nie">nie</label>
                    </p>                           

                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Duplikuj kategorię" />
                  <button type="button" class="przyciskNon" onclick="cofnij('kategorie','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','kategorie');">Powrót</button>    
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