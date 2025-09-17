<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
    
        $id_galeria = (int)$_POST['id'];
        
        $pola = array(
                array('gallery_image_thumbnail',$filtr->process($_POST['miniatury'])),
                array('gallery_width_image',$filtr->process($_POST['szerokosc'])),
                array('gallery_height_image',$filtr->process($_POST['wysokosc'])),
                array('gallery_crop_image',$filtr->process($_POST['kadrowanie'])),
                array('gallery_cols',$filtr->process($_POST['kolumny'])),
                array('gallery_customers_group_id',((isset($_POST['grupa_klientow'])) ? implode(',', (array)$_POST['grupa_klientow']) : 0)));
                
        if ( $_POST['strony'] == '1' ) {
             //
             $pola[] = array('gallery_pages','1');
             $pola[] = array('gallery_pages_quantity', (int)$_POST['strony_ilosc']);
             //
          } else {
             //
             $pola[] = array('gallery_pages','0');
             $pola[] = array('gallery_pages_quantity', '10');
             //
        }
          
        $sql = $db->update_query('gallery' , $pola, " id_gallery = '".$id_galeria."'");
        unset($pola);    
        
        // kasuje rekordy w tablicy
        $db->delete_query('gallery_description' , " id_gallery = '".$id_galeria."'");   
        $db->delete_query('gallery_image' , " id_gallery = '".$id_galeria."'");              
        
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
                    array('id_gallery',$id_galeria),
                    array('language_id',$ile_jezykow[$w]['id']),
                    array('gallery_name',$filtr->process($_POST['nazwa_'.$w])),
                    array('gallery_description',$filtr->process($_POST['edytor_'.$w])),
                    array('gallery_meta_title_tag',$filtr->process($_POST['tytul_meta_'.$w])),      
                    array('gallery_meta_desc_tag',$filtr->process($_POST['opis_meta_'.$w])),
                    array('gallery_meta_keywords_tag',$filtr->process($_POST['slowa_meta_'.$w])));                        
            $sql = $db->insert_query('gallery_description' , $pola);
            unset($pola);
            
        }

        // dodawanie pol galerie
        for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
        
            for ($q = 1; $q <= (int)($_POST['ile_pol_'.$w]); $q++) {
                
                if (!empty($_POST['zdjecie_'.$q.'_'.$w])) {
                    $pola = array(
                            array('id_gallery',$id_galeria),
                            array('language_id',$ile_jezykow[$w]['id']),
                            array('gallery_image',$filtr->process($_POST['zdjecie_'.$q.'_'.$w])),
                            array('gallery_image_sort',(int)$_POST['sort_'.$q.'_'.$w]),
                            array('gallery_image_description',$filtr->process($_POST['opis_zdjecia_'.$q.'_'.$w])),
                            array('gallery_image_alt',$filtr->process($_POST['alt_'.$q.'_'.$w])));

                    $sql = $db->insert_query('gallery_image' , $pola);
                    unset($pola);
                }
                
            }
        
        }     

        unset($ile_jezykow);    
        
        if ( isset($_POST['powrot']) && (int)$_POST['powrot'] == 1 ) {
            //            
            Funkcje::PrzekierowanieURL('galerie_edytuj.php?id_poz=' . (int)$id_galeria . ((isset($_POST['zakladka']) && (int)$_POST['zakladka'] > 0) ? '&zakladka='.(int)$_POST['zakladka'] : ''));
            //
          } else {        
            //
            if ( isset($_POST['zakladka']) && (int)$_POST['zakladka'] > 0 ) {
              
                Funkcje::PrzekierowanieURL('/zarzadzanie/wyglad/wyglad.php?zakladka='.(int)$_POST['zakladka']);
              
              } else {
              
                Funkcje::PrzekierowanieURL('galerie.php?id_poz='.$id_galeria);
                
            } 
            //
        }

    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>

    <div id="naglowek_cont">Edycja pozycji</div>
    <div id="cont">

          <form action="galerie/galerie_edytuj.php" method="post" id="pogallery" class="cmxform"> 
          
          <div class="poleForm">
            <div class="naglowek">Edycja danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select distinct * from gallery where id_gallery = '".(int)$_GET['id_poz']."'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {

                $info = $sql->fetch_assoc();  
                ?>            
            
                <div class="pozycja_edytowana">    
                
                    <input type="hidden" name="akcja" value="zapisz" />
                    
                    <input type="hidden" name="id" value="<?php echo $info['id_gallery']; ?>" />
                    
                    <?php if (isset($_GET['zakladka']) && (int)$_GET['zakladka'] > 0 ) { ?>
                    <input type="hidden" name="zakladka" value="<?php echo (int)$_GET['zakladka']; ?>" />
                    <?php } ?>                       

                    <?php $ile_jezykow = Funkcje::TablicaJezykow(); ?>

                    <script>
                    $(document).ready(function() {
                        $("#pogallery").validate({
                          rules: {
                            nazwa_0: {
                              required: true
                            },
                            szerokosc: {
                              range: [10, 1000],
                              number: true,
                              required: true
                            },
                            wysokosc: {
                              range: [10, 1000],
                              number: true,
                              required: true
                            },
                            kolumny: {
                              range: [1, 100],
                              number: true,
                              required: true
                            },
                            strony_ilosc: {
                              range: [1, 50],
                              number: true,
                              required: function() { var wynik = false; if ( $("input[name='strony']:checked", "#pogallery").val() == "1" ) { wynik = true; } return wynik; } 
                            }                             
                          },
                          messages: {
                            nazwa_0: {
                              required: "Pole jest wymagane."
                            },
                            wysokosc: {
                              required: "Pole jest wymagane.",
                              range: "Wartość musi być wieksza od 10."
                            },
                            szeroksc: {
                              required: "Pole jest wymagane.",
                              range: "Wartość musi być wieksza od 10."
                            },     
                            kolumny: {
                              required: "Pole jest wymagane.",
                              range: "Wartość musi być wieksza od 0 i mniejsza od 100."
                            },
                            strony_ilosc: {
                              required: "Pole jest wymagane.",
                              range: "Wartość musi być wieksza od 0 i mniejsza od 50."
                            }                             
                          }
                        });
                    });                    

                    function dodaj_galerie(id_jezyk) {
                        ile_pol = parseInt($("#ile_pol_"+id_jezyk).val()) + 1;
                        //
                        $('#wyniki_'+id_jezyk).append('<div id="wyniki_'+id_jezyk+'_'+ile_pol+'"></div>');
                        $('#wyniki_'+id_jezyk+'_'+ile_pol).css('display','none');
                        //
                        $.get('ajax/galeria.php?tok=<?php echo Sesje::Token(); ?>', { id: ile_pol, id_jezyk: id_jezyk }, function(data) {
                            $('#wyniki_'+id_jezyk+'_'+ile_pol).html(data);
                            $("#ile_pol_"+id_jezyk).val(ile_pol);
                            
                            $('#wyniki_'+id_jezyk+'_'+ile_pol).slideDown("fast");

                            $("gallery input:radio").css('border','0px');
                            $("gallery input:checkbox").css('border','0px');		
                            
                            usunPlikZdjecie();
                        });
                    }            

                    function ilosc_stron(id) {
                        if ( id == 1 ) {
                             $('#galeria_strony').slideDown();
                          } else {
                             $('#galeria_strony').slideUp();
                        }
                    }

                    function miniatury_zdjec(id) {
                        if ( id == 1 ) {
                             $('#minitury_zdjec').slideDown();
                          } else {
                             $('#minitury_zdjec').slideUp();
                        }
                    }
                    </script> 
                    
                    <p>
                      <label>Czy na listingu galerii tworzyc miniatury zdjęć ?</label>
                      <input type="radio" name="miniatury" value="1" onclick="miniatury_zdjec(1)" id="miniatury_tak" <?php echo (($info['gallery_image_thumbnail'] == '1') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="miniatury_tak">tak</label>
                      <input type="radio" name="miniatury" value="0" onclick="miniatury_zdjec(0)" id="miniatury_nie" <?php echo (($info['gallery_image_thumbnail'] == '0') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="miniatury_nie">nie</label>
                    </p> 

                    <div id="minitury_zdjec" <?php echo (($info['gallery_image_thumbnail'] == '0') ? 'style="display:none"' : ''); ?>>
                        <p>
                          <label class="required" for="szerokosc">Szerokość miniatur na listingu galerii w px:</label>
                          <input type="text" name="szerokosc" id="szerokosc" size="5" value="<?php echo $info['gallery_width_image']; ?>" class="calkowita" /><em class="TipIkona"><b>Szerokość miniatur wyświetlanych zdjęc na listingu galerii</b></em>
                        </p>

                        <p>
                          <label class="required" for="wysokosc">Wysokość miniatur na listingu galerii w px:</label>
                          <input type="text" name="wysokosc" id="wysokosc" size="5" value="<?php echo $info['gallery_height_image']; ?>" class="calkowita" /><em class="TipIkona"><b>Wysokość miniatur wyświetlanych zdjęc na listingu galerii</b></em>
                        </p>                
                        
                        <p>
                          <label>Sposób tworzenia miniatur na listingu</label>
                          <span class="Miniaturki">
                              <span>
                                <img src="obrazki/mini_oryginalny.png" alt="oryginalny rozmiar" />
                                <input type="radio" name="kadrowanie" value="0" id="kadrowanie_tak" <?php echo (($info['gallery_crop_image'] == '0') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="kadrowanie_tak">standardowe</label>
                              </span>
                              <span>
                                <img src="obrazki/mini_kadrowany.png" alt="wykadrowane" />
                                <input type="radio" name="kadrowanie" value="1" id="kadrowanie_nie" <?php echo (($info['gallery_crop_image'] == '1') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="kadrowanie_nie">wykadrowane</label>                          
                              </span>
                          </span>
                        </p>
                    </div>

                    <p>
                      <label class="required" for="kolumny">W ilu kolumnach mają wyświetlać się zdjęcia:</label>
                      <input type="text" name="kolumny" id="kolumny" size="5" value="<?php echo $info['gallery_cols']; ?>" class="calkowita" />
                    </p> 
                    
                    <p>
                      <label>Czy galeria ma być dzielona na strony ?</label>
                      <input type="radio" name="strony" value="1" onclick="ilosc_stron(1)" id="strony_tak" <?php echo (($info['gallery_pages'] == '1') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="strony_tak">tak</label>
                      <input type="radio" name="strony" value="0" onclick="ilosc_stron(0)" id="strony_nie" <?php echo (($info['gallery_pages'] == '0') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="strony_nie">nie</label>
                    </p> 

                    <div id="galeria_strony" <?php echo (($info['gallery_pages'] == '0') ? 'style="display:none"' : ''); ?>>
                        <p>
                          <label class="required" for="strony_ilosc">Ile zdjęć ma być wyświetlanych na jednej stronie ?</label>
                          <input type="text" name="strony_ilosc" id="strony_ilosc" size="5" value="<?php echo $info['gallery_pages_quantity']; ?>" class="calkowita" />
                        </p>                     
                    </div>
                    
                    <table class="WyborCheckbox">
                        <tr>
                            <td><label>Widoczna dla grupy klientów:</label></td>
                            <td>
                                <?php                        
                                $TablicaGrupKlientow = Klienci::ListaGrupKlientow(false);
                                foreach ( $TablicaGrupKlientow as $GrupaKlienta ) {
                                    echo '<input type="checkbox" value="' . $GrupaKlienta['id'] . '" name="grupa_klientow[]" id="grupa_klientow_' . $GrupaKlienta['id'] . '" ' . ((in_array((string)$GrupaKlienta['id'], explode(',', (string)$info['gallery_customers_group_id']))) ? 'checked="checked" ' : '') . ' /><label class="OpisFor" for="grupa_klientow_' . $GrupaKlienta['id'] . '">' . $GrupaKlienta['text'] . '</label><br />';
                                }               
                                unset($TablicaGrupKlientow);
                                ?>
                            </td>
                        </tr>
                    </table> 
                    
                    <div class="ostrzezenie" style="margin:0px 15px 10px 25px">Jeżeli nie zostanie wybrana żadna grupa klientów to galeria będzie widoczna dla wszystkich klientów.</div>    

                    <div class="info_tab">
                    <?php
                    for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                        echo '<span id="link_'.$w.'" class="a_href_info_tab" onclick="gold_tabs(\''.$w.'\',\'edytor_\')">'.$ile_jezykow[$w]['text'].'</span>';
                    }                    
                    ?>                   
                    </div>
                    
                    <div style="clear:both"></div>
                    
                    <div class="info_tab_content">
                        <?php
                        for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                        
                            // pobieranie danych jezykowych
                            $zapytanie_jezyk = "select distinct * from gallery_description where id_gallery = '".(int)$_GET['id_poz']."' and language_id = '" .$ile_jezykow[$w]['id']."'";
                            $sqls = $db->open_query($zapytanie_jezyk);
                            $galeria = $sqls->fetch_assoc();                           
                        
                            ?>
                            
                            <div id="info_tab_id_<?php echo $w; ?>" style="display:none;">
                            
                                <p>
                                   <?php if ($w == '0') { ?>
                                    <label class="required" for="nazwa_0">Nazwa galerii:</label>
                                    <input type="text" name="nazwa_<?php echo $w; ?>" size="65" value="<?php echo (isset($galeria['gallery_name']) ? Funkcje::formatujTekstInput($galeria['gallery_name']) : ''); ?>" id="nazwa_0" />
                                   <?php } else { ?>
                                    <label for="nazwa_<?php echo $w; ?>">Nazwa galerii:</label>   
                                    <input type="text" name="nazwa_<?php echo $w; ?>" id="nazwa_<?php echo $w; ?>" size="65" value="<?php echo (isset($galeria['gallery_name']) ? Funkcje::formatujTekstInput($galeria['gallery_name']) : ''); ?>" />
                                   <?php } ?>
                                </p>          

                                <p>
                                  <label for="tytul_meta_<?php echo $w; ?>">Meta Tagi - Tytuł:</label>
                                  <input type="text" name="tytul_meta_<?php echo $w; ?>" id="tytul_meta_<?php echo $w; ?>" size="120" onkeyup="licznik_znakow_meta(this,'iloscZnakowNazwa_<?php echo $w; ?>')" value="<?php echo (isset($galeria['gallery_meta_title_tag']) ? $galeria['gallery_meta_title_tag'] : ''); ?>" />
                                </p> 
                                
                                <p class="LicznikMeta">
                                  <label></label>
                                  Ilość wpisanych znaków: <span class="iloscZnakow" id="iloscZnakowNazwa_<?php echo $w; ?>"><?php echo (isset($galeria['gallery_meta_title_tag']) ? strlen(mb_convert_encoding((string)$galeria['gallery_meta_title_tag'], 'ISO-8859-1', 'UTF-8')) : '0'); ?></span>
                                  zalecana maksymalna ilość: <span class="iloscZnakow" id="iloscZnakowNazwa_<?php echo $w; ?>Max"><?php echo DLUGOSC_META_NAZWA; ?></span>
                                </p>                                
                                
                                <p>
                                  <label for="opis_meta_<?php echo $w; ?>">Meta Tagi - Opis:</label>
                                  <input type="text" name="opis_meta_<?php echo $w; ?>" id="opis_meta_<?php echo $w; ?>" size="120" onkeyup="licznik_znakow_meta(this,'iloscZnakowOpis_<?php echo $w; ?>')" value="<?php echo (isset($galeria['gallery_meta_desc_tag']) ? $galeria['gallery_meta_desc_tag'] : ''); ?>" />
                                </p>   
                                
                                <p class="LicznikMeta">
                                  <label></label>
                                  Ilość wpisanych znaków: <span class="iloscZnakow" id="iloscZnakowOpis_<?php echo $w; ?>"><?php echo (isset($galeria['gallery_meta_desc_tag']) ? strlen(mb_convert_encoding((string)$galeria['gallery_meta_desc_tag'], 'ISO-8859-1', 'UTF-8')) : '0'); ?></span>
                                  zalecana maksymalna ilość: <span class="iloscZnakow" id="iloscZnakowOpis_<?php echo $w; ?>Max"><?php echo DLUGOSC_META_OPIS; ?></span>
                                </p>                                 
                                
                                <p>
                                  <label for="slowa_meta_<?php echo $w; ?>">Meta Tagi - Słowa kluczowe:</label>
                                  <input type="text" name="slowa_meta_<?php echo $w; ?>" id="slowa_meta_<?php echo $w; ?>" size="120" onkeyup="licznik_znakow_meta(this,'iloscZnakowSlowa_<?php echo $w; ?>')" value="<?php echo (isset($galeria['gallery_meta_keywords_tag']) ? $galeria['gallery_meta_keywords_tag'] : ''); ?>" />
                                </p>      

                                <p class="LicznikMeta">
                                  <label></label>
                                  Ilość wpisanych znaków: <span class="iloscZnakow" id="iloscZnakowSlowa_<?php echo $w; ?>"><?php echo (isset($galeria['gallery_meta_keywords_tag']) ? strlen(mb_convert_encoding((string)$galeria['gallery_meta_keywords_tag'], 'ISO-8859-1', 'UTF-8')) : '0'); ?></span>
                                  zalecana maksymalna ilość: <span class="iloscZnakow" id="iloscZnakowSlowa_<?php echo $w; ?>Max"><?php echo DLUGOSC_META_SLOWA; ?></span>
                                </p>                                   
                                
                                <div class="edytor" style="margin-bottom:10px">
                                  <textarea cols="50" rows="30" id="edytor_<?php echo $w; ?>" name="edytor_<?php echo $w; ?>"><?php echo (isset($galeria['gallery_description']) ? $galeria['gallery_description'] : ''); ?></textarea>
                                </div>                                 

                                <div id="wyniki_<?php echo $w; ?>" class="PoleGalerii">
                                
                                    <?php
                                    $q = 1;
                                    // pobieranie poszczegolnych pol galleryularza
                                    $zapytanie_odpowiedz = "select distinct * from gallery_image where id_gallery = '".(int)$_GET['id_poz']."' and language_id = '" .$ile_jezykow[$w]['id']."' order by gallery_image_sort";
                                    $sqlsp = $db->open_query($zapytanie_odpowiedz);
                                    //
                                    while ($pole = $sqlsp->fetch_assoc()) {
                                        ?>                                   
                                
                                        <div class="NaglowekGalerii">Zdjęcie galerii nr <span><?php echo $q; ?></span></div>
                                        
                                        <p>
                                          <label for="foto_<?php echo $q; ?>_<?php echo $w; ?>">Ścieżka zdjęcia:</label>           
                                          <input type="text" name="zdjecie_<?php echo $q; ?>_<?php echo $w; ?>" size="95" value="<?php echo (isset($pole['gallery_image']) ? $pole['gallery_image'] : ''); ?>" class="obrazek" ondblclick="openFileBrowser('foto_<?php echo $q; ?>_<?php echo $w; ?>','','<?php echo KATALOG_ZDJEC; ?>')" id="foto_<?php echo $q; ?>_<?php echo $w; ?>" autocomplete="off" /><em class="TipIkona"><b>Kliknij dwukrotnie w pole obok żeby otworzyć okno przeglądarki zdjęć</b></em>
                                          <em class="TipChmurka"><b>Usuń przypisane zdjęcie</b><span class="usun_zdjecie" data-foto="foto_<?php echo $q; ?>_<?php echo $w; ?>" ></span></em>
                                          <span class="PrzegladarkaZdjec TipChmurka" onclick="openFileBrowser('foto_<?php echo $q; ?>_<?php echo $w; ?>','','<?php echo KATALOG_ZDJEC; ?>')"><b>Kliknij żeby otworzyć okno przeglądarki zdjęć</b></span>
                                        </p>      
                                        
                                        <div id="divfoto_<?php echo $q; ?>_<?php echo $w; ?>" style="padding-left:10px">
                                            <label>Zdjęcie:</label>
                                            <span id="fofoto_<?php echo $q; ?>_<?php echo $w; ?>">
                                                <span class="zdjecie_tbl">
                                                    <?php 
                                                    if (isset($pole['gallery_image']) && !empty($pole['gallery_image'])) { 
                                                        echo Funkcje::pokazObrazek($pole['gallery_image'], '', 60, 60);
                                                    }
                                                    ?> 
                                                </span>
                                            </span> 
                                        </div>                                          

                                        <p>
                                            <label for="opis_zdjecia_<?php echo $q; ?>_<?php echo $w; ?>">Opis zdjęcia:</label>
                                            <textarea name="opis_zdjecia_<?php echo $q; ?>_<?php echo $w; ?>" id="opis_zdjecia_<?php echo $q; ?>_<?php echo $w; ?>" rows="5" cols="50"><?php echo (isset($pole['gallery_image_description']) ? $pole['gallery_image_description'] : ''); ?></textarea>
                                        </p>
                                        
                                        <p>
                                            <label for="alt_<?php echo $q; ?>_<?php echo $w; ?>">Opis znacznika ALT:</label>
                                            <input type="text" value="<?php echo (isset($pole['gallery_image_alt']) ? $pole['gallery_image_alt'] : ''); ?>" name="alt_<?php echo $q; ?>_<?php echo $w; ?>" id="alt_<?php echo $q; ?>_<?php echo $w; ?>" size="60" />
                                        </p>
                                        
                                        <p>
                                            <label for="sort_<?php echo $q; ?>_<?php echo $w; ?>">Kolejność wyświetlania w galerii:</label>  
                                            <input class="calkowita" type="text" value="<?php echo (isset($pole['gallery_image_sort']) ? $pole['gallery_image_sort'] : ''); ?>" name="sort_<?php echo $q; ?>_<?php echo $w; ?>" id="sort_<?php echo $q; ?>_<?php echo $w; ?>" size="4" />
                                        </p> 

                                        <?php 
                                        $q++;
                                    } 
                                    ?> 

                                    <?php
                                    $db->close_query($sqlsp);
                                    unset($pole);                                
                                    ?>                                          
                                    
                                </div>      

                                <div style="padding:10px;padding-top:20px;">
                                    <span class="dodaj" onclick="dodaj_galerie(<?php echo $w; ?>)" style="cursor:pointer">dodaj nowe zdjęcie</span>
                                </div>                            

                                <input value="<?php echo (($q > 1) ? ($q - 1) : 0); ?>" type="hidden" name="ile_pol_<?php echo $w; ?>" id="ile_pol_<?php echo $w; ?>" />
                                
                            </div>
                            <?php     

                            $db->close_query($sqls);
                            unset($galeria);                            
                            
                        }                    
                        ?>                      
                    </div>
                    
                    <script>
                    gold_tabs('0','edytor_');
                    </script> 
                
                </div>
                
                <div class="przyciski_dolne">
                
                    <input type="hidden" name="powrot" id="powrot" value="0" />
                    <input type="submit" class="przyciskNon" value="Zapisz dane" />
                    <input type="submit" class="przyciskNon" value="Zapisz dane i pozostań w edycji" onclick="$('#powrot').val(1)" />  
                    
                    <?php 
                    // jezeli jest get zakladka wraca do ustawien wygladu
                    if (isset($_GET['zakladka']) ) { ?>
                    
                      <button type="button" class="przyciskNon" onclick="cofnij('wyglad','<?php echo Funkcje::Zwroc_Wybrane_Get(array('zakladka')); ?>','wyglad');">Powrót</button> 
                      
                    <?php } else { ?>

                      <button type="button" class="przyciskNon" onclick="cofnij('galerie','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>');">Powrót</button>   
                    
                    <?php } ?>
  
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
    
} ?>