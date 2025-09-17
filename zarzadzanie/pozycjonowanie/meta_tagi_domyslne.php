<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

        // kasuje rekordy w tablicy
        $db->truncate_query('headertags_default');        
        
        $ile_jezykow = Funkcje::TablicaJezykow();
        for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
            //
            $pola = array(
                    array('default_title',( $_POST['tytul_'.$w] == '' ? $filtr->process($_POST['tytul_0']) : $filtr->process($_POST['tytul_'.$w]))),
                    array('default_description',( $_POST['opis_'.$w] == '' ? $filtr->process($_POST['opis_0']) : $filtr->process($_POST['opis_'.$w]))),
                    array('default_keywords',( $_POST['slowa_'.$w] == '' ? $filtr->process($_POST['slowa_0']) : $filtr->process($_POST['slowa_'.$w]))),
                    array('default_index_title',( $_POST['tytul_index_'.$w] == '' ? $filtr->process($_POST['tytul_index_0']) : $filtr->process($_POST['tytul_index_'.$w]))),
                    array('default_index_description',( $_POST['opis_index_'.$w] == '' ? $filtr->process($_POST['opis_index_0']) : $filtr->process($_POST['opis_index_'.$w]))),
                    array('default_index_keywords',( $_POST['slowa_index_'.$w] == '' ? $filtr->process($_POST['slowa_index_0']) : $filtr->process($_POST['slowa_index_'.$w]))),                                        
                    array('og_title',( $_POST['og_title_'.$w] == '' ? $filtr->process($_POST['og_title_0']) : $filtr->process($_POST['og_title_'.$w]))),                    
                    array('og_site_name',( $_POST['og_site_name_'.$w] == '' ? $filtr->process($_POST['og_site_name_0']) : $filtr->process($_POST['og_site_name_'.$w]))),                    
                    array('og_description',( $_POST['og_description_'.$w] == '' ? $filtr->process($_POST['og_description_0']) : $filtr->process($_POST['og_description_'.$w]))),                    
                    array('og_image',( $_POST['og_image_'.$w] == '' ? $filtr->process($_POST['og_image_0']) : $filtr->process($_POST['og_image_'.$w]))), 
                    array('default_title_tab', $filtr->process($_POST['tytul_zakladka_'.$w])),
                    array('language_id',$ile_jezykow[$w]['id']));

            $db->insert_query('headertags_default' , $pola);
            unset($pola);
            //           
        }
        
        foreach ( $_POST['konfiguracja'] as $klucz => $pol ) {
            
            $pola = array(array('value', $pol));
            $db->update_query('settings', $pola, "code = '" . $klucz . "'");
            unset($pola);
            
        }   

        Funkcje::PrzekierowanieURL('meta_tagi_domyslne.php');
        
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Edycja pozycji</div>
    <div id="cont">
          
          <script>
          $(document).ready(function() {
            $("#metaForm").validate({
              rules: {
                tytul: {
                  required: true
                }
              }
            });
            setTimeout(function() {
                $('#meta').fadeOut();
            }, 3000);
          });
          </script>         

          <form action="pozycjonowanie/meta_tagi_domyslne.php" method="post" id="metaForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Edycja danych sekcji META strony oraz tagów Open Graph dla strony głównej</div>
            
                <div class="pozycja_edytowana">
                
                    <input type="hidden" name="akcja" value="zapisz" />
                
                    <span class="maleInfo">Ustawienie domyślnych wartości znaczników tytuł, opis, słowa kluczowe dla strony głównej sklepu oraz wszystkich pozostałych podstron, które nie posiadają definiowanych własnych znaczników META.</span>
                    
                    <div class="TytulMeta" style="margin-left:25px">
                        Ustawienia tytułu strony wyświetlany po opuszczeniu przez klienta zakładki ze sklepem w przeglądarce
                    </div>      

                    <p>
                      <label>Czy wyświetlać tytuł po opuszczeniu zakładki sklepu ?</label>
                      <input type="radio" name="konfiguracja[TYTUL_ZAKLADKI_WLACZONY]" id="tytul_zakladki_wlaczony_tak" value="tak" <?php echo ((TYTUL_ZAKLADKI_WLACZONY == 'tak') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="tytul_zakladki_wlaczony_tak">tak</label>
                      <input type="radio" name="konfiguracja[TYTUL_ZAKLADKI_WLACZONY]" id="tytul_zakladki_wlaczony_nie" value="nie" <?php echo ((TYTUL_ZAKLADKI_WLACZONY == 'nie') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="tytul_zakladki_wlaczony_nie">nie</label>
                    </p>                     
                    
                    <p>
                      <label>Czy wyświetlać tytuł w formie migania (co 1 sek) ?</label>
                      <input type="radio" name="konfiguracja[TYTUL_ZAKLADKI_MIGANIE]" id="tytul_zakladki_miganie_tak" value="tak" <?php echo ((TYTUL_ZAKLADKI_MIGANIE == 'tak') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="tytul_zakladki_miganie_tak">tak</label>
                      <input type="radio" name="konfiguracja[TYTUL_ZAKLADKI_MIGANIE]" id="tytul_zakladki_miganie_nie" value="nie" <?php echo ((TYTUL_ZAKLADKI_MIGANIE == 'nie') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="tytul_zakladki_miganie_nie">nie</label>
                    </p>  
                    
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
                            $zapytanie_jezyk = "select * from headertags_default where language_id = '" .$ile_jezykow[$w]['id']."'";
                            $sqls = $db->open_query($zapytanie_jezyk);
                            $nazwa = $sqls->fetch_assoc();   
                            
                            ?>
                            
                            <div id="info_tab_id_<?php echo $w; ?>" style="display:none;">
                            
                                <div class="TytulMeta">
                                    Tytuł strony wyświetlany po opuszczeniu przez klienta zakładki ze sklepem w przeglądarce
                                </div>
                                
                                <p>
                                    <label for="tytul_zakladka_<?php echo $w; ?>">Wyświetlany tekst:</label>   
                                    <input type="text" name="tytul_zakladka_<?php echo $w; ?>" id="tytul_zakladka_<?php echo $w; ?>" size="120" value="<?php echo Funkcje::formatujTekstInput($nazwa['default_title_tab']); ?>" />
                                </p>                                 
                                
                                <div class="maleInfo" style="margin:0px 0px 15px 10px">Jeżeli wartość nie zostanie wpisana - nie będzie wyświetlana</div>
                                
                                <div class="TytulMeta">
                                    Wartości domyślne dla strony głównej sklepu
                                </div>
                            
                                <p>
                                   <?php if ($w == '0') { ?>
                                    <label class="required" for="tytul_index_<?php echo $w; ?>">Domyślny tytuł:</label>
                                    <input type="text" name="tytul_index_<?php echo $w; ?>" id="tytul_index_<?php echo $w; ?>" size="120" onkeyup="licznik_znakow_meta(this,'iloscZnakowNazwa_<?php echo $w; ?>')" value="<?php echo Funkcje::formatujTekstInput($nazwa['default_index_title']); ?>" class="required" />
                                   <?php } else { ?>
                                    <label for="tytul_index_<?php echo $w; ?>">Domyślny tytuł:</label>   
                                    <input type="text" name="tytul_index_<?php echo $w; ?>" id="tytul_index_<?php echo $w; ?>" size="120" onkeyup="licznik_znakow_meta(this,'iloscZnakowNazwa_<?php echo $w; ?>')" value="<?php echo Funkcje::formatujTekstInput($nazwa['default_index_title']); ?>" />
                                   <?php } ?>
                                </p> 
                                
                                <p class="LicznikMeta">
                                  <label></label>
                                  Ilość wpisanych znaków: <span class="iloscZnakow" id="iloscZnakowNazwa_<?php echo $w; ?>"><?php echo strlen(mb_convert_encoding((string)$nazwa['default_index_title'], 'ISO-8859-1', 'UTF-8')); ?></span>
                                  zalecana maksymalna ilość: <span class="iloscZnakow" id="iloscZnakowNazwa_<?php echo $w; ?>Max"><?php echo DLUGOSC_META_NAZWA; ?></span>
                                </p>                                

                                <p>
                                  <label for="opis_index_<?php echo $w; ?>">Domyślny opis:</label>   
                                  <textarea name="opis_index_<?php echo $w; ?>" id="opis_index_<?php echo $w; ?>" onkeyup="licznik_znakow_meta(this,'iloscZnakowOpis_<?php echo $w; ?>')" cols="117" rows="3"><?php echo $nazwa['default_index_description']; ?></textarea>
                                </p> 
                                
                                <p class="LicznikMeta">
                                  <label></label>
                                  Ilość wpisanych znaków: <span class="iloscZnakow" id="iloscZnakowOpis_<?php echo $w; ?>"><?php echo strlen(mb_convert_encoding((string)$nazwa['default_index_description'], 'ISO-8859-1', 'UTF-8')); ?></span>
                                  zalecana maksymalna ilość: <span class="iloscZnakow" id="iloscZnakowOpis_<?php echo $w; ?>Max"><?php echo DLUGOSC_META_OPIS; ?></span>
                                </p>                                

                                <p>
                                  <label for="slowa_index_<?php echo $w; ?>">Domyślne słowa kluczowe:</label>   
                                  <textarea name="slowa_index_<?php echo $w; ?>" id="slowa_index_<?php echo $w; ?>" onkeyup="licznik_znakow_meta(this,'iloscZnakowSlowa_<?php echo $w; ?>')" cols="117" rows="3"><?php echo $nazwa['default_index_keywords']; ?></textarea>
                                </p> 
                                
                                <p class="LicznikMeta">
                                  <label></label>
                                  Ilość wpisanych znaków: <span class="iloscZnakow" id="iloscZnakowSlowa_<?php echo $w; ?>"><?php echo strlen(mb_convert_encoding((string)$nazwa['default_index_keywords'], 'ISO-8859-1', 'UTF-8')); ?></span>
                                  zalecana maksymalna ilość: <span class="iloscZnakow" id="iloscZnakowSlowa_<?php echo $w; ?>Max"><?php echo DLUGOSC_META_SLOWA; ?></span>
                                </p>   

                                <br />
                                
                                <div class="TytulMeta">
                                    Wartości tagów Open Graph dla strony głównej sklepu
                                </div>
                                
                                <div class="ostrzezenie" style="margin:0px 0px 15px 10px">Warunkiem wyświetlania tagów Open Graph jest wypełnienie wszystkich pól</div>
                                
                                <p>
                                  <label for="og_title_<?php echo $w; ?>">Tytuł strony:</label>   
                                  <input type="text" name="og_title_<?php echo $w; ?>" id="og_title_<?php echo $w; ?>" size="120" value="<?php echo Funkcje::formatujTekstInput($nazwa['og_title']); ?>" />
                                </p> 
                                
                                <p>
                                  <label for="og_site_name_<?php echo $w; ?>">Nazwa sklepu:<em class="TipIkona"><b>Będzie wyświetlona pod tytułem strony jako link prowadzący do strony głównej</b></em></label>   
                                  <input type="text" name="og_site_name_<?php echo $w; ?>" id="og_site_name_<?php echo $w; ?>" size="120" value="<?php echo Funkcje::formatujTekstInput($nazwa['og_site_name']); ?>" />
                                </p> 

                                <p>
                                  <label for="og_description_<?php echo $w; ?>">Krótki opis treści:</label>   
                                  <textarea name="og_description_<?php echo $w; ?>" id="og_description_<?php echo $w; ?>" cols="117" rows="3"><?php echo $nazwa['og_description']; ?></textarea>
                                </p>    

                                <p>
                                  <label for="foto">Obrazek:<em class="TipIkona"><b>Obrazek, który będzie użyty jako miniaturka, musi mieć rozmiary co najmniej 50 x 50 pikseli, proporcje maksymalnie 3:1 i być zapisany w formacie JPG, PNG lub GIF</b></em></label>           
                                  <input type="text" name="og_image_<?php echo $w; ?>" size="120" value="<?php echo $nazwa['og_image']; ?>" class="obrazek" ondblclick="openFileBrowser('foto_<?php echo $w; ?>','','<?php echo KATALOG_ZDJEC; ?>')" id="foto_<?php echo $w; ?>" autocomplete="off" />                 
                                  <em class="TipIkona"><b>Kliknij dwukrotnie w pole obok żeby otworzyć okno przeglądarki zdjęć</b></em>
                                  <span class="usun_zdjecie TipChmurka" data-foto="foto_<?php echo $w; ?>"><b>Usuń przypisane zdjęcie</b></span>
                                  <span class="PrzegladarkaZdjec TipChmurka" onclick="openFileBrowser('foto_<?php echo $w; ?>','','<?php echo KATALOG_ZDJEC; ?>')"><b>Kliknij żeby otworzyć okno przeglądarki zdjęć</b></span>
                                </p>      
                                
                                <div id="divfoto_<?php echo $w; ?>" style="padding-left:10px;display:none">
                                  <label>Obrazek:</label>
                                  <span id="fofoto_<?php echo $w; ?>">
                                      <span class="zdjecie_tbl">
                                          <img src="obrazki/_loader_small.gif" alt="" />
                                      </span>
                                  </span> 

                                  <?php if (!empty($nazwa['og_image'])) { ?>
                                  <script>          
                                  pokaz_obrazek_ajax('foto_<?php echo $w; ?>', '<?php echo $nazwa['og_image']; ?>')
                                  </script> 
                                  <?php } ?>   
                                  
                                </div>          

                                <br />
                                
                                <div class="TytulMeta">
                                    Wartości domyślne dla podstron sklepu 
                                </div>                                
                                
                                <p>
                                   <?php if ($w == '0') { ?>
                                    <label class="required" for="tytul_<?php echo $w; ?>">Domyślny tytuł:</label>
                                    <input type="text" name="tytul_<?php echo $w; ?>" id="tytul_<?php echo $w; ?>" size="120" onkeyup="licznik_znakow_meta(this,'iloscZnakowPodstronyNazwa_<?php echo $w; ?>')" value="<?php echo Funkcje::formatujTekstInput($nazwa['default_title']); ?>" class="required" />
                                   <?php } else { ?>
                                    <label for="tytul_<?php echo $w; ?>">Domyślny tytuł:</label>   
                                    <input type="text" name="tytul_<?php echo $w; ?>" id="tytul_<?php echo $w; ?>" size="120" onkeyup="licznik_znakow_meta(this,'iloscZnakowPodstronyNazwa_<?php echo $w; ?>')" value="<?php echo Funkcje::formatujTekstInput($nazwa['default_title']); ?>" />
                                   <?php } ?>
                                </p> 
                                
                                <p class="LicznikMeta">
                                  <label></label>
                                  Ilość wpisanych znaków: <span class="iloscZnakow" id="iloscZnakowPodstronyNazwa_<?php echo $w; ?>"><?php echo strlen(mb_convert_encoding((string)$nazwa['default_title'], 'ISO-8859-1', 'UTF-8')); ?></span>
                                  zalecana maksymalna ilość: <span class="iloscZnakow" id="iloscZnakowPodstronyNazwa_<?php echo $w; ?>Max"><?php echo DLUGOSC_META_NAZWA; ?></span>
                                </p>                                

                                <p>
                                  <label for="opis_<?php echo $w; ?>">Domyślny opis:</label>   
                                  <textarea name="opis_<?php echo $w; ?>" id="opis_<?php echo $w; ?>" onkeyup="licznik_znakow_meta(this,'iloscZnakowPodstronyOpis_<?php echo $w; ?>')" cols="117" rows="3"><?php echo $nazwa['default_description']; ?></textarea>
                                </p> 
                                
                                <p class="LicznikMeta">
                                  <label></label>
                                  Ilość wpisanych znaków: <span class="iloscZnakow" id="iloscZnakowPodstronyOpis_<?php echo $w; ?>"><?php echo strlen(mb_convert_encoding((string)$nazwa['default_description'], 'ISO-8859-1', 'UTF-8')); ?></span>
                                  zalecana maksymalna ilość: <span class="iloscZnakow" id="iloscZnakowPodstronyOpis_<?php echo $w; ?>Max"><?php echo DLUGOSC_META_OPIS; ?></span>
                                </p>                                

                                <p>
                                  <label for="slowa_<?php echo $w; ?>">Domyślne słowa kluczowe:</label>   
                                  <textarea name="slowa_<?php echo $w; ?>" id="slowa_<?php echo $w; ?>" onkeyup="licznik_znakow_meta(this,'iloscZnakowPodstronySlowa_<?php echo $w; ?>')" cols="117" rows="3"><?php echo $nazwa['default_keywords']; ?></textarea>
                                </p> 

                                <p class="LicznikMeta">
                                  <label></label>
                                  Ilość wpisanych znaków: <span class="iloscZnakow" id="iloscZnakowPodstronySlowa_<?php echo $w; ?>"><?php echo strlen(mb_convert_encoding((string)$nazwa['default_keywords'], 'ISO-8859-1', 'UTF-8')); ?></span>
                                  zalecana maksymalna ilość: <span class="iloscZnakow" id="iloscZnakowPodstronySlowa_<?php echo $w; ?>Max"><?php echo DLUGOSC_META_SLOWA; ?></span>
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
                    
                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Zapisz dane" />
                </div>                 
          </div>                      
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}
