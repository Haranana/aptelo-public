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
            $pola = array(array('countries_default','0'));
            $db->update_query('countries' , $pola);	        
        }
        //
        $pola = array(
                array('countries_default',$filtr->process($_POST["domyslny"])),
                array('countries_iso_code_2',$filtr->process($_POST["iso2"])),
                array('countries_iso_code_3',$filtr->process($_POST["iso3"]))
        );
        //			
        $db->update_query('countries' , $pola, " countries_id = '".(int)$_POST["id"]."'");	
        unset($pola);
        
        // kasuje rekordy w tablicy
        $db->delete_query('countries_description' , " countries_id = '".(int)$_POST["id"]."'");        
        
        $ile_jezykow = Funkcje::TablicaJezykow();
        for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
            //
            // jezeli nazwa w innym jezyku nie jest wypelniona
            if ( $w > 0 ) {
                if (empty($_POST['nazwa_'.$w])) {
                    $_POST['nazwa_'.$w] = $_POST['nazwa_0'];
                }
            }
            //
            $pola = array(
                    array('countries_id',(int)$_POST["id"]),
                    array('countries_name',$filtr->process($_POST['nazwa_'.$w])),
                    array('language_id',(int)$ile_jezykow[$w]['id']));           
            $db->insert_query('countries_description' , $pola);
            unset($pola);
            //               
        }              
        //        
        //
        Funkcje::PrzekierowanieURL('kraje.php?id_poz='.(int)$_POST["id"]);
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
                nazwa_0: {
                  required: true
                },
                iso2: {
                  required: true
                },
                iso3: {
                  required: true
                }
              },
              messages: {
                nazwa_0: {
                  required: "Pole jest wymagane."
                }               
              }
            });
          });
          </script>        

          <form action="slowniki/kraje_edytuj.php" method="post" id="slownikForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Edycja danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select * from countries where countries_id = '" . (int)$_GET['id_poz'] . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
            
                $info = $sql->fetch_assoc();
                ?>
            
                <div class="pozycja_edytowana">

                    <input type="hidden" name="akcja" value="zapisz" />
                
                    <input type="hidden" name="id" value="<?php echo (int)$_GET['id_poz']; ?>" />
                    
                    <?php $ile_jezykow = Funkcje::TablicaJezykow(); ?>
                    
                    <div class="info_tab">
                    <?php
                    for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                        echo '<span id="link_'.$w.'" class="a_href_info_tab" onclick="gold_tabs(\''.$w.'\')">'.$ile_jezykow[$w]['text'].'</span>';
                    }                    
                    ?>                   
                    </div>
                    
                    <div style="clear:both"></div>
                    
                    <div class="info_tab_content">
                        <?php
                        for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                        
                            // pobieranie danych jezykowych
                            $zapytanie_jezyk = "select distinct * from countries_description where countries_id = '".(int)$_GET['id_poz']."' and language_id = '" .$ile_jezykow[$w]['id']."'";
                            $sqls = $db->open_query($zapytanie_jezyk);
                            $nazwa = $sqls->fetch_assoc();   
                            
                            ?>
                            
                            <div id="info_tab_id_<?php echo $w; ?>" style="display:none;">
                            
                                <p>
                                   <?php if ($w == '0') { ?>
                                    <label class="required" for="nazwa_0">Nazwa:</label>
                                    <input type="text" name="nazwa_<?php echo $w; ?>" size="45" value="<?php echo (isset($nazwa['countries_name']) ? Funkcje::formatujTekstInput($nazwa['countries_name']) : ''); ?>" id="nazwa_0" />
                                   <?php } else { ?>
                                    <label for="nazwa_<?php echo $w; ?>">Nazwa:</label>   
                                    <input type="text" name="nazwa_<?php echo $w; ?>" size="45" value="<?php echo (isset($nazwa['countries_name']) ? Funkcje::formatujTekstInput($nazwa['countries_name']) : ''); ?>" id="nazwa_<?php echo $w; ?>" />
                                   <?php } ?>
                                </p> 
                                            
                            </div>
                            <?php                    
                            $db->close_query($sqls);
                            unset($zapytanie_jezyk);
                        }                    
                        ?>                      
                    </div>
                    
                    <script>
                    gold_tabs('0');
                    </script>     

                    <p>
                      <label class="required" for="iso2">Kod ISO-2:</label>
                      <input type="text" name="iso2" size="5" value="<?php echo $info['countries_iso_code_2']; ?>" id="iso2" />
                    </p>

                    <p>
                      <label class="required" for="iso3">Kod ISO-3:</label>
                      <input type="text" name="iso3" size="5" value="<?php echo $info['countries_iso_code_3']; ?>" id="iso3" />
                    </p>

                    <?php if ($info['countries_default'] == '0') { ?>
                    
                    <p>
                      <label>Czy kraj jest domyślnym:</label>
                      <input type="radio" value="0" name="domyslny" id="domyslny_nie" checked="checked" /><label class="OpisFor" for="domyslny_nie">nie</label>
                      <input type="radio" value="1" name="domyslny" id="domyslny_tak" /><label class="OpisFor" for="domyslny_tak">tak</label>                      
                    </p>
                    
                    <?php } else { ?>
                    
                    <input type="hidden" name="domyslny" value="1" />
                    
                    <?php } ?>

                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Zapisz dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('kraje','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','slowniki');">Powrót</button>           
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
