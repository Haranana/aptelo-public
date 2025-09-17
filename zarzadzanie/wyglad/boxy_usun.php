<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //			
        $db->delete_query('theme_box' , " box_id = '".$filtr->process($_POST["id"])."'");  
        $db->delete_query('theme_box_description' , " box_id = '".$filtr->process($_POST["id"])."'");
        //
        // jezeli jest plik sprawdza czy nie ma jakis definicji w bazie
        if (!empty($_POST['plik'])) {
            //
            // jezeli jest plik boxu
            if (is_file('../boxy/' . $_POST['plik'])) {
                //            
                $lines = file('../boxy/' . $_POST['plik']);
                for ($i = 0, $j = count($lines); $i < $j; $i++) {
                    //
                    if (strpos((string)$lines[$i],'{{') > -1) {
                        //
                        $preg = preg_match('|{{([0-9A-Za-ząćęłńóśźż _,;]+?)}}|', $lines[$i], $matches);
                        //
                        $PodzialOpis = explode(';',str_replace(array('{{','}}'), '', (string)$matches[0]));
                        //
                        $db->delete_query('settings' , " code = '".$PodzialOpis[0]."'");
                        //
                    }
                    //
                }            
                //
            }
        }
        //
        // usuwa wpis w stronach informacyjnych jezeli box wyswietlal strone info
        if ( (int)$_POST['strona'] > 0 ) {
            //
            $pola = array( array('pages_modul',0));
            $db->update_query('pages' , $pola, 'pages_id = ' . (int)$_POST['strona']);
            //
        }
        //
        //
        Funkcje::PrzekierowanieURL('boxy.php');
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Kasowanie pozycji</div>
    <div id="cont">
          
          <form action="wyglad/boxy_usun.php" method="post" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Usuwanie danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select * from theme_box where box_id = '" . (int)$_GET['id_poz'] . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
            
                $info = $sql->fetch_assoc();
                ?>            
            
                <div class="pozycja_edytowana">
                
                    <input type="hidden" name="akcja" value="zapisz" />
                    
                    <input type="hidden" name="plik" value="<?php echo $info['box_file']; ?>" />        
                    <input type="hidden" name="strona" value="<?php echo (int)$info['box_pages_id']; ?>" />    
                    
                    <input type="hidden" name="id" value="<?php echo (int)$_GET['id_poz']; ?>" />
                    
                    <p>
                      Czy skasować box ?
                    </p>   
                 
                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Usuń dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('boxy','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','wyglad');">Powrót</button> 
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