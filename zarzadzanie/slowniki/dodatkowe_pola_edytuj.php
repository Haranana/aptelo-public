<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        // jezeli pola ma byc obrazkowe nie moze byc w filtrach
        if ($_POST['obrazek'] == '1') {
            $_POST['filtr'] = '0';
        }
        //
        // jezeli jest zmiana formy wyswietlania na obrazek lub tekst to usuwa dane ze slownika
        if ( $_POST["obrazek"] != $_POST["poprzedni_typ"] ) {
            //
            $db->delete_query('products_extra_fields_book' , " products_extra_fields_id = '".(int)$_POST["id"]."'");  
            //
        }
        //
        $pola = array(
                array('products_extra_fields_name',$filtr->process($_POST["nazwa"])),
                array('products_extra_fields_icon',$filtr->process($_POST["ikonka"])),
                array('products_extra_fields_order',$filtr->process($_POST["sort"])),
                array('products_extra_fields_status','1'),
                array('languages_id',$filtr->process($_POST["jezyk"])),
                array('products_extra_fields_filter',(int)$_POST["filtr"]),
                array('products_extra_fields_number',(int)$_POST["format"]),
                array('products_extra_fields_search',(int)$_POST["szukanie"]),
                array('products_extra_fields_compare',(int)$_POST["porownanie"]),
                array('products_extra_fields_view',(int)$_POST["widocznosc"]),
                array('products_extra_fields_allegro',(int)$_POST["allegro"]),
                array('products_extra_fields_location',$filtr->process($_POST["polozenie"])),
                array('products_extra_fields_image',(((int)$_POST["format"] == 1) ? 0 : (int)$_POST["obrazek"]))
                );
        //			
        $db->update_query('products_extra_fields' , $pola, " products_extra_fields_id = '".(int)$_POST["id"]."'");	
        unset($pola);
        //
        Funkcje::PrzekierowanieURL('dodatkowe_pola.php?id_poz='.(int)$_POST["id"]);
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

          <form action="slowniki/dodatkowe_pola_edytuj.php" method="post" id="slownikForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Edycja danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select * from products_extra_fields where products_extra_fields_id = '" . (int)$_GET['id_poz'] . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
            
                $info = $sql->fetch_assoc();
                ?>
            
                <div class="pozycja_edytowana">
                
                    <div class="info_content">
                
                    <input type="hidden" name="akcja" value="zapisz" />
                
                    <input type="hidden" name="id" value="<?php echo (int)$_GET['id_poz']; ?>" />
                    
                    <p>
                      <label class="required" for="nazwa">Nazwa pola:</label>
                      <input type="text" name="nazwa" id="nazwa" value="<?php echo $info['products_extra_fields_name']; ?>" size="35" />
                    </p>
                    
                    <p>
                      <label for="nazwa">Ikonka:</label>
                      <input type="text" name="ikonka" size="95" value="<?php echo $info['products_extra_fields_icon']; ?>" class="obrazek" ondblclick="openFileBrowser('foto','','<?php echo KATALOG_ZDJEC; ?>')" id="foto" autocomplete="off" />                 
                      <em class="TipIkona"><b>Kliknij dwukrotnie w pole obok żeby otworzyć okno przeglądarki zdjęć</b></em>
                      <span class="usun_zdjecie TipChmurka" data-foto="foto"><b>Kliknij w ikonę żeby usunąć przypisane zdjęcie</b></span>
                      <span class="PrzegladarkaZdjec TipChmurka" onclick="openFileBrowser('foto','','<?php echo KATALOG_ZDJEC; ?>')"><b>Kliknij żeby otworzyć okno przeglądarki zdjęć</b></span>
                    </p>
                    <div id="divfoto" style="padding-left:10px;display:none">
                        <label>Podgląd:</label>
                        <span id="fofoto">
                            <span class="zdjecie_tbl">
                                <img src="obrazki/_loader_small.gif" alt="" />
                            </span>
                        </span> 

                        <?php if (!empty($info['products_extra_fields_icon'])) { ?>
                        
                        <script>         
                        pokaz_obrazek_ajax('foto', '<?php echo $info['products_extra_fields_icon']; ?>')
                        </script>  
                        
                        <?php } ?>   
                        
                    </div>

                    
                    <p>
                      <label for="jezyk">Dostępne dla wersji językowej:</label>
                      <?php
                      $tablica_jezykow = Funkcje::TablicaJezykow(true);                 
                      echo Funkcje::RozwijaneMenu('jezyk',$tablica_jezykow,$info['languages_id'], 'id="jezyk"');
                      ?>                  
                    </p> 

                    <p>
                      <label>Format danych:</label>
                      <input type="radio" value="0" name="format" id="dowolny" onclick="$('#obrazek_pole').slideDown();$('#obrazek_nie').prop('checked',true)" <?php echo (($info['products_extra_fields_number'] == '0') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="dowolny">dowolne znaki</label>
                      <input type="radio" value="1" name="format" id="liczby" onclick="$('#obrazek_pole').slideUp();$('#filtr').slideDown();$('#obrazek_nie').prop('checked',true)" <?php echo (($info['products_extra_fields_number'] == '1') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="liczby">tylko liczby</label>                      
                    </p> 
                    
                    <?php
                    if ( $info['products_extra_fields_number'] == '1' ) {
                         //
                         $info['products_extra_fields_image'] = '0';
                         //
                    }
                    ?>
                    
                    <div id="obrazek_pole" <?php echo (($info['products_extra_fields_number'] == '1') ? 'style="display:none"' : ''); ?>>
                    
                        <p>
                          <label>Wyświetlane w formie obrazka:</label>
                          <input type="radio" value="0" name="obrazek" id="obrazek_nie" onclick="$('#filtr').slideDown()" <?php echo (($info['products_extra_fields_image'] == '0') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="obrazek_nie">nie</label>
                          <input type="radio" value="1" name="obrazek" id="obrazek_tak" onclick="$('#filtr').slideUp()" <?php echo (($info['products_extra_fields_image'] == '1') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="obrazek_tak">tak</label>                      
                        </p> 
                    
                    </div>

                    <p>
                      <span class="maleInfo odlegloscRwd" style="padding-top:0px; padding-bottom:0px">
                        zmiana formy wyświetlania dodatkowego pola spowoduje usunięcie wartości w słowniku danego pola
                        <input type="hidden" value="<?php echo $info['products_extra_fields_image']; ?>" name="poprzedni_typ" />
                      </span>
                    </p>
                    
                    <div id="filtr" <?php echo (($info['products_extra_fields_image'] == '1') ? 'style="display:none"' : ''); ?>>
                    
                        <p>
                          <label>Czy pole ma być wyświetlane w filtrach w listingu produktów:</label>
                          <input type="radio" value="0" name="filtr" id="filtr_nie" <?php echo (($info['products_extra_fields_filter'] == '0') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="filtr_nie">nie</label>
                          <input type="radio" value="1" name="filtr" id="filtr_tak" <?php echo (($info['products_extra_fields_filter'] == '1') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="filtr_tak">tak</label>
                        </p>

                        <p>
                          <label>Czy pole ma być wyświetlane w wyszukiwaniu zaawansowanym produktów:</label>
                          <input type="radio" value="0" name="szukanie" id="szukanie_nie" <?php echo (($info['products_extra_fields_search'] == '0') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="szukanie_nie">nie</label>
                          <input type="radio" value="1" name="szukanie" id="szukanie_tak" <?php echo (($info['products_extra_fields_search'] == '1') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="szukanie_tak">tak</label>
                        </p>

                    </div> 
                    
                    <p>
                      <label>Czy pole ma być wyświetlane przy porównywaniu produktów (jako osobna pozycja):</label>
                      <input type="radio" value="0" name="porownanie" id="porownanie_nie" <?php echo (($info['products_extra_fields_compare'] == '0') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="porownanie_nie">nie</label>
                      <input type="radio" value="1" name="porownanie" id="porownanie_tak" <?php echo (($info['products_extra_fields_compare'] == '1') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="porownanie_tak">tak</label>
                    </p>                    

                    <p>
                      <label>Czy wyświetlać pole na karcie produktu:</label>
                      <input type="radio" value="0" name="widocznosc" id="widocznosc_nie" onclick="$('#widok').slideUp()" <?php echo (($info['products_extra_fields_view'] == '0') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="widocznosc_nie">nie</label>
                      <input type="radio" value="1" name="widocznosc" id="widocznosc_tak" onclick="$('#widok').slideDown()" <?php echo (($info['products_extra_fields_view'] == '1') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="widocznosc_tak">tak</label>              
                    </p> 

                    <p>
                      <label>Czy wyświetlać pole na aukcjach Allegro:</label>
                      <input type="radio" value="0" name="allegro" id="allegro_nie" <?php echo (($info['products_extra_fields_allegro'] == '0') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="allegro_nie">nie</label>
                      <input type="radio" value="1" name="allegro" id="allegro_tak" <?php echo (($info['products_extra_fields_allegro'] == '1') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="allegro_tak">tak</label>              
                    </p>                    

                    <div id="widok" <?php echo (($info['products_extra_fields_view'] == '0') ? 'style="display:none"' : ''); ?>>

                        <p>
                          <label>Miejsce wyświetlania:</label>
                          <input type="radio" value="foto" id="polozenie_foto" name="polozenie" <?php echo (($info['products_extra_fields_location'] == 'foto') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="polozenie_foto">obok zdjęcia produktu<em class="TipIkona"><b>Dodatkowe pola będą wyświetlane na karcie produktu obok zdjęcia razem z dostępnością, nr kat, czasem wysyłki</b></em></label>
                          <input type="radio" value="opis" id="polozenie_opis" name="polozenie" <?php echo (($info['products_extra_fields_location'] == 'opis') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="polozenie_opis">pod opisem produktu<em class="TipIkona"><b>Dodatkowe pola będą wyświetlane na karcie produktu pod opisem produktu</b></em></label>              
                        </p>  

                    </div>

                    <p>
                      <label for="sort">Kolejność wyświetlania:</label>
                      <input type="text" class="calkowita" name="sort" id="sort" value="<?php echo $info['products_extra_fields_order']; ?>" size="5" />
                    </p> 

                    </div>
                 
                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Zapisz dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('dodatkowe_pola','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','slowniki');">Powrót</button>           
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
?>