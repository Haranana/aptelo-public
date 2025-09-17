<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        // jezeli cecha ma byc obrazkowa nie moze byc w filtrach
        if ($_POST['obrazek'] == '1') {
            $_POST['filtr'] = '0';
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
        $db->insert_query('products_extra_fields' , $pola);	
        $id_dodanej_pozycji = $db->last_id_query();
        
        unset($pola);
        //
        if (isset($id_dodanej_pozycji) && $id_dodanej_pozycji > 0) {
            Funkcje::PrzekierowanieURL('dodatkowe_pola.php?id_poz='.$id_dodanej_pozycji);
        } else {
            Funkcje::PrzekierowanieURL('dodatkowe_pola.php');
        }
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Dodawanie pozycji</div>
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

          <form action="slowniki/dodatkowe_pola_dodaj.php" method="post" id="slownikForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Dodawanie danych</div>
            
            <div class="pozycja_edytowana">
            
                <div class="info_content">
            
                <input type="hidden" name="akcja" value="zapisz" />
            
                <p>
                  <label class="required" for="nazwa">Nazwa pola:</label>
                  <input type="text" name="nazwa" id="nazwa" value="" size="35" />
                </p>
                
                <p>
                      <label for="nazwa">Ikonka:</label>
                      <input type="text" name="ikonka" size="95" value="" class="obrazek" ondblclick="openFileBrowser('foto','','<?php echo KATALOG_ZDJEC; ?>')" id="foto" autocomplete="off" />                 
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
                  echo Funkcje::RozwijaneMenu('jezyk',$tablica_jezykow,0, 'id="jezyk"');
                  ?>                  
                </p>   
                
                <p>
                  <label>Format danych:</label>
                  <input type="radio" value="0" name="format" id="dowolny" onclick="$('#obrazek_pole').slideDown();$('#obrazek_nie').prop('checked',true)" checked="checked" /><label class="OpisFor" for="dowolny">dowolne znaki</label>
                  <input type="radio" value="1" name="format" id="liczby" onclick="$('#obrazek_pole').slideUp();$('#filtr').slideDown();$('#obrazek_nie').prop('checked',true)" /><label class="OpisFor" for="liczby">tylko liczby</label>                      
                </p>                 
                
                <div id="obrazek_pole">

                    <p>
                      <label>Wyświetlane w formie obrazka:</label>
                      <input type="radio" value="0" name="obrazek" id="obrazek_nie" onclick="$('#filtr').slideDown()" checked="checked" /><label class="OpisFor" for="obrazek_nie">nie</label>
                      <input type="radio" value="1" name="obrazek" id="obrazek_tak" onclick="$('#filtr').slideUp()" /><label class="OpisFor" for="obrazek_tak">tak</label>
                    </p> 

                </div>
                
                <div id="filtr">
                
                    <p>
                      <label>Czy pole ma być wyświetlane w filtrach w listingu produktów:</label>
                      <input type="radio" value="0" name="filtr" id="filtr_nie" checked="checked" /><label class="OpisFor" for="filtr_nie">nie</label>
                      <input type="radio" value="1" name="filtr" id="filtr_tak" /><label class="OpisFor" for="filtr_tak">tak</label>
                    </p>

                    <p>
                      <label>Czy pole ma być wyświetlane w wyszukiwaniu zaawansowanym produktów:</label>
                      <input type="radio" value="0" name="szukanie" id="szukanie_nie" checked="checked" /><label class="OpisFor" for="szukanie_nie">nie</label>
                      <input type="radio" value="1" name="szukanie" id="szukanie_tak" /><label class="OpisFor" for="szukanie_tak">tak</label>
                    </p>

                </div>   
                
                <p>
                  <label>Czy pole ma być wyświetlane przy porównywaniu produktów (jako osobna pozycja):</label>
                  <input type="radio" value="0" name="porownanie" id="porownanie_nie" checked="checked" /><label class="OpisFor" for="porownanie_nie">nie</label>
                  <input type="radio" value="1" name="porownanie" id="porownanie_tak" /><label class="OpisFor" for="porownanie_tak">tak</label>
                </p>                    

                <p>
                  <label>Czy wyświetlać pole na karcie produktu:</label>
                  <input type="radio" value="0" name="widocznosc" id="widocznosc_nie" onclick="$('#widok').slideUp()" /><label class="OpisFor" for="widocznosc_nie">nie</label>
                  <input type="radio" value="1" name="widocznosc" id="widocznosc_tak" onclick="$('#widok').slideDown()" checked="checked" /><label class="OpisFor" for="widocznosc_tak">tak</label>
                </p>        
                
                <p>
                  <label>Czy wyświetlać pole na aukcjach Allegro:</label>
                  <input type="radio" value="0" name="allegro" id="allegro_nie" /><label class="OpisFor" for="allegro_nie">nie</label>
                  <input type="radio" value="1" name="allegro" id="allegro_tak" checked="checked" /><label class="OpisFor" for="allegro_tak">tak</label>
                </p>                   

                <div id="widok">

                    <p>
                      <label>Miejsce wyświetlania:</label>
                      <input type="radio" value="foto" id="polozenie_foto" name="polozenie" checked="checked" /><label class="OpisFor" for="polozenie_foto">obok zdjęcia produktu<em class="TipIkona"><b>Dodatkowe pola będą wyświetlane na karcie produktu obok zdjęcia razem z dostępnością, nr kat, czasem wysyłki</b></em></label>
                      <input type="radio" value="opis" id="polozenie_opis" name="polozenie" /><label class="OpisFor" for="polozenie_opis">pod opisem produktu<em class="TipIkona"><b>Dodatkowe pola będą wyświetlane na karcie produktu pod opisem produktu</b></em></label>
                    </p>  

                </div>                                   

                <p>
                  <label for="sort">Kolejność wyświetlania:</label>
                  <input type="text" class="calkowita" name="sort" id="sort" value="" size="5" />
                </p> 

                </div>
                
            </div>

            <div class="przyciski_dolne">
              <input type="submit" class="przyciskNon" value="Zapisz dane" />
              <button type="button" class="przyciskNon" onclick="cofnij('dodatkowe_pola','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','slowniki');">Powrót</button>   
            </div>            

          </div>                      
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}