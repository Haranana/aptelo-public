<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        if ($_POST["domyslny"] == '1') {
            $pola = array(array('label_default','0'));
            $db->update_query('print_labels' , $pola);	        
        }
        //
        $pola = array(
                array('brand',$filtr->process($_POST["producent"])),
                array('name',$filtr->process($_POST["typ"])),
                array('description',$filtr->process($_POST["opis"])),
                array('width',(float)$_POST["szerokosc"]),
                array('height',(float)$_POST["wysokosc"]),
                array('margin',(float)$_POST["odstep"]),
                array('orientation',$filtr->process($_POST["orientacja"])),
                array('format',$filtr->process($_POST["format"])),
                array('kolumny',(int)$_POST["kolumn"]),
                array('wiersze',(int)$_POST["wierszy"]),
                array('topmargin',(float)$_POST["margines_gorny"]),
                array('leftmargin',(float)$_POST["margines_lewy"]),
                array('bordercolor',$filtr->process($_POST["kolor_ramki"])),
                array('borderwidth',(float)$_POST["grubosc_ramki"]),
                array('border',(int)$_POST["ramka"]),
                array('label_default',(int)$_POST["domyslny"]));
                
        //
        $db->update_query('print_labels' , $pola, " id = '".(int)$_POST["id"]."'");
        unset($pola);
        
        //
        Funkcje::PrzekierowanieURL('etykiety.php?id_poz='.(int)$_POST["id"]);
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Edycja pozycji</div>
    <div id="cont">
          
          <script>
          $(document).ready(function() {
            $("#slownikForm").validate({
              rules: {
                nazwa: {
                  required: true
                }
              },
              messages: {
                nazwa: {
                  required: "Pole jest wymagane."
                }               
              }
            });
          });
          </script>  
          
          <script type="text/javascript" src="programy/jscolor/jscolor.js"></script> 

          <form action="slowniki/etykiety_edytuj.php" method="post" id="slownikForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Edycja danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "SELECT * FROM print_labels WHERE id = '" . (int)$_GET['id_poz'] . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
            
                $info = $sql->fetch_assoc();
                ?>
            
                <div class="pozycja_edytowana">
                
                    <div class="info_content">

                    <input type="hidden" name="akcja" value="zapisz" />
                
                    <input type="hidden" name="id" value="<?php echo (int)$_GET['id_poz']; ?>" />
                    
                    <p>
                      <label for="producent">Producent:</label>
                      <input type="text" name="producent" size="53" value="<?php echo $info['brand']; ?>" id="producent" />
                    </p>

                    <p>
                      <label class="required" for="typ">Typ:</label>
                      <input type="text" name="typ" size="53" value="<?php echo $info['name']; ?>" id="typ" class="required" />
                    </p>

                    <p>
                      <label for="opis">Opis:</label>
                      <input type="text" name="opis" size="53" value="<?php echo $info['description']; ?>" id="opis" />
                    </p>

                    <p>
                      <label class="required" for="szerokosc">Szerokość [mm]:</label>
                      <input type="text" name="szerokosc" size="20" value="<?php echo $info['width']; ?>" id="szerokosc" class="required" /><em class="TipIkona"><b>Szerokość pojedynczej etykiety</b></em>
                    </p>

                    <p>
                      <label class="required" for="wysokosc">Wysokość [mm]:</label>
                      <input type="text" name="wysokosc" size="20" value="<?php echo $info['height']; ?>" id="wysokosc" class="required" /><em class="TipIkona"><b>Wysokość pojedynczej etykiety</b></em>
                    </p>

                    <p>
                      <label class="required" for="odstep">Odstęp poziomy [mm]:</label>
                      <input type="text" name="odstep" size="20" value="<?php echo $info['margin']; ?>" id="odstep" class="required" /><em class="TipIkona"><b>Poziomy odstęp pomiędzy etykietami</b></em>
                    </p>

                    <p>
                      <label class="required" for="margines_gorny">Górny margines [mm]:</label>
                      <input type="text" name="margines_gorny" size="20" value="<?php echo $info['topmargin']; ?>" id="margines_gorny" class="required" /><em class="TipIkona"><b>Margines od górnej krawędzi arkusza</b></em>
                    </p>

                    <p>
                      <label class="required" for="margines_lewy">Lewy margines [mm]:</label>
                      <input type="text" name="margines_lewy" size="20" value="<?php echo $info['leftmargin']; ?>" id="margines_lewy" class="required" /><em class="TipIkona"><b>Margines od lewej krawędzi arkusza</b></em>
                    </p>

                    <p>
                      <label class="required" for="kolumn">Ilość kolumn:</label>
                      <input type="text" name="kolumn" size="20" value="<?php echo $info['kolumny']; ?>" id="kolumn" class="required" /><em class="TipIkona"><b>Ilość kolumn etykiet na pojedynczym arkuszu papieru</b></em>
                    </p>

                    <p>
                      <label class="required" for="wierszy">Ilość wierszy:</label>
                      <input type="text" name="wierszy" size="20" value="<?php echo $info['wiersze']; ?>" id="wierszy" class="required" /><em class="TipIkona"><b>Ilość wierszy etykiet na pojedynczym arkuszu papieru</b></em>
                    </p>

                    <p>
                      <label class="required" for="format">Format arkusza:</label>
                      <?php
                      $tablica = array();
                      $tablica[] = array('id' => 'A4', 'text' => 'A4');
                      $tablica[] = array('id' => 'A5', 'text' => 'A5');
                      $tablica[] = array('id' => 'B5', 'text' => 'B5');
                      echo Funkcje::RozwijaneMenu('format', $tablica, $info['format'], 'id="format"');
                      unset($tablica);
                      ?><em class="TipIkona"><b>Format papieru arkusza etykiet</b></em>
                    </p>

                    <p>
                      <label class="required" for="orientacja">Orientacja arkusza:</label>
                      <?php
                      $tablica = array();
                      $tablica[] = array('id' => 'P', 'text' => 'Pionowa');
                      $tablica[] = array('id' => 'L', 'text' => 'Pozioma');
                      echo Funkcje::RozwijaneMenu('orientacja', $tablica, $info['orientation'], 'id="orientacja"');
                      unset($tablica);
                      ?><em class="TipIkona"><b>Orientacja wydruku arkusza etykiet</b></em>
                    </p>

                    <p>
                      <label>Czy drukować ramkę:</label>
                      <input type="radio" value="0" name="ramka" id="ramka_nie" <?php echo ( $info['border'] == '1' ? 'checked="checked"' : '' ); ?> /><label class="OpisFor" for="ramka_nie">nie</label>
                      <input type="radio" value="1" name="ramka" id="ramka_tak" <?php echo ( $info['border'] == '0' ? 'checked="checked"' : '' ); ?> /><label class="OpisFor" for="ramka_tak">tak</label>                       
                    </p>
                    
                    <p>
                      <label for="kolor_ramki">Kolor ramki:</label>
                      <input name="kolor_ramki" id="kolor_ramki" class="color" style="-moz-box-shadow:none" value="<?php echo $info['bordercolor']; ?>" size="8" />                    
                    </p> 

                    <p>
                      <label for="grubosc_ramki">Grubość ramki:</label>
                      <input type="text" name="grubosc_ramki" size="20" value="<?php echo $info['borderwidth']; ?>" id="grubosc_ramki" />
                    </p>

                    <?php if ($info['label_default'] == '0') { ?>
                    
                    <p>
                      <label>Czy etykieta jest domyślna:</label>
                      <input type="radio" value="0" name="domyslny" id="etykieta_nie" checked="checked" /><label class="OpisFor" for="etykieta_nie">nie</label>
                      <input type="radio" value="1" name="domyslny" id="etykieta_tak" /><label class="OpisFor" for="etykieta_tak">tak</label>                      
                    </p>
                    
                    <?php } else { ?>
                    
                    <input type="hidden" name="domyslny" value="1" />
                    
                    <?php } ?>
                    
                    </div>

                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Zapisz dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('etykiety','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','slowniki');">Powrót</button>           
                </div>                 

            <?php
            
            $db->close_query($sql);
            unset($info);            
            
            } else {
            
                echo '<div class="pozycja_edytowana">Brak danych do wyświetlenia</div>';
            
            }
            ?>

          </div>                      
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}
