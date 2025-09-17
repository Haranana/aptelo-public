<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
    
        $pola = array(
                array('gallery_status','1'),
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
        
        $sql = $db->insert_query('gallery' , $pola);
        $id_dodanej_pozycji = $db->last_id_query();
        
        unset($pola);
        
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
                    array('id_gallery',$id_dodanej_pozycji),
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
                            array('id_gallery',$id_dodanej_pozycji),
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
        
        if (isset($id_dodanej_pozycji) && $id_dodanej_pozycji > 0) {
          
            if ( isset($_POST['powrot']) && (int)$_POST['powrot'] == 1 ) {
                //            
                Funkcje::PrzekierowanieURL('galerie_edytuj.php?id_poz='.$id_dodanej_pozycji);
                //
              } else {
                //
                Funkcje::PrzekierowanieURL('galerie.php?id_poz='.$id_dodanej_pozycji);
                //
            }             

        } else {
          
            Funkcje::PrzekierowanieURL('galerie.php');
            
        }

    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>

    <div id="naglowek_cont">Dodawanie pozycji</div>
    <div id="cont">

          <form action="galerie/galerie_dodaj.php" method="post" id="pogallery" class="cmxform"> 
          
          <div class="poleForm">
            <div class="naglowek">Dodawanie danych</div>
            
            <div class="pozycja_edytowana">    
            
                <input type="hidden" name="akcja" value="zapisz" />

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
                          required: "Pole jest wymagan.e"
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
                    <input type="radio" name="miniatury" value="1" onclick="miniatury_zdjec(1)" id="miniatury_tak" checked="checked" /><label class="OpisFor" for="miniatury_tak">tak</label>
                    <input type="radio" name="miniatury" value="0" onclick="miniatury_zdjec(0)" id="miniatury_nie" /><label class="OpisFor" for="miniatury_nie">nie</label>
                </p> 

                <div id="minitury_zdjec">
                    <p>
                      <label class="required" for="szerokosc">Szerokość miniatur na listingu galerii w px:</label>
                      <input type="text" name="szerokosc" id="szerokosc" size="5" value="500" class="calkowita" /><em class="TipIkona"><b>Szerokość miniatur wyświetlanych zdjęc na listingu galerii</b></em>
                    </p>

                    <p>
                      <label class="required" for="wysokosc">Wysokość  miniatur na listingu galerii w px:</label>
                      <input type="text" name="wysokosc" id="wysokosc" size="5" value="375" class="calkowita" /><em class="TipIkona"><b>Wysokość miniatur wyświetlanych zdjęc na listingu galerii</b></em>
                    </p>                

                    <p>
                          <label>Sposób tworzenia miniatur na listingu</label>
                          <span class="Miniaturki">
                              <span>
                                <img src="obrazki/mini_oryginalny.png" alt="oryginalny rozmiar" />
                                <input type="radio" name="kadrowanie" value="0" id="kadrowanie_tak" checked="checked" /><label class="OpisFor" for="kadrowanie_tak">standardowe</label>
                              </span>
                              <span>
                                <img src="obrazki/mini_kadrowany.png" alt="wykadrowane" />
                                <input type="radio" name="kadrowanie" value="1" id="kadrowanie_nie" /><label class="OpisFor" for="kadrowanie_nie">wykadrowane</label>                          
                              </span>
                          </span>
                    </p>

                </div>

                <p>
                  <label class="required" for="kolumny">W ilu kolumnach mają wyświetlać się zdjęcia:</label>
                  <input type="text" name="kolumny" id="kolumny" size="5" value="2" class="calkowita" />
                </p>    

                <p>
                  <label>Czy galeria ma być dzielona na strony ?</label>
                  <input type="radio" name="strony" value="1" onclick="ilosc_stron(1)" id="strony_tak" /><label class="OpisFor" for="strony_tak">tak</label>
                  <input type="radio" name="strony" value="0" onclick="ilosc_stron(0)" id="strony_nie" checked="checked" /><label class="OpisFor" for="strony_nie">nie</label>
                </p> 

                <div id="galeria_strony" style="display:none">
                    <p>
                      <label class="required" for="strony_ilosc">Ile zdjęć ma być wyświetlanych na jednej stronie ?</label>
                      <input type="text" name="strony_ilosc" id="strony_ilosc" size="5" value="" class="calkowita" />
                    </p>                     
                </div>                
                
                <table class="WyborCheckbox">
                    <tr>
                        <td><label>Widoczna dla grupy klientów:</label></td>
                        <td>
                            <?php                        
                            $TablicaGrupKlientow = Klienci::ListaGrupKlientow(false);
                            foreach ( $TablicaGrupKlientow as $GrupaKlienta ) {
                                echo '<input type="checkbox" value="' . $GrupaKlienta['id'] . '" name="grupa_klientow[]" id="grupa_klientow_' . $GrupaKlienta['id'] . '" /><label class="OpisFor" for="grupa_klientow_' . $GrupaKlienta['id'] . '">' . $GrupaKlienta['text'] . '</label><br />';
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
                        ?>
                        
                        <div id="info_tab_id_<?php echo $w; ?>" style="display:none;">
                        
                            <p>
                               <?php if ($w == '0') { ?>
                                <label class="required" for="nazwa_0">Nazwa galerii:</label>
                                <input type="text" name="nazwa_<?php echo $w; ?>" size="65" value="" id="nazwa_0" />
                               <?php } else { ?>
                                <label for="nazwa_<?php echo $w; ?>">Nazwa galerii:</label>   
                                <input type="text" name="nazwa_<?php echo $w; ?>" size="65" value="" id="nazwa_<?php echo $w; ?>" />
                               <?php } ?>
                            </p>          

                            <p>
                              <label for="tytul_meta_<?php echo $w; ?>">Meta Tagi - Tytuł:</label>
                              <input type="text" name="tytul_meta_<?php echo $w; ?>" id="tytul_meta_<?php echo $w; ?>" size="120" onkeyup="licznik_znakow_meta(this,'iloscZnakowNazwa_<?php echo $w; ?>')" value="" />
                            </p> 
                            
                            <p class="LicznikMeta">
                              <label></label>
                              Ilość wpisanych znaków: <span class="iloscZnakow" id="iloscZnakowNazwa_<?php echo $w; ?>">0</span>
                              zalecana maksymalna ilość: <span class="iloscZnakow" id="iloscZnakowNazwa_<?php echo $w; ?>Max"><?php echo DLUGOSC_META_NAZWA; ?></span>
                            </p>                              
                            
                            <p>
                              <label for="opis_meta_<?php echo $w; ?>">Meta Tagi - Opis:</label>
                              <input type="text" name="opis_meta_<?php echo $w; ?>" id="opis_meta_<?php echo $w; ?>" size="120" onkeyup="licznik_znakow_meta(this,'iloscZnakowOpis_<?php echo $w; ?>')" value="" />
                            </p> 

                            <p class="LicznikMeta">
                              <label></label>
                              Ilość wpisanych znaków: <span class="iloscZnakow" id="iloscZnakowOpis_<?php echo $w; ?>">0</span>
                              zalecana maksymalna ilość: <span class="iloscZnakow" id="iloscZnakowOpis_<?php echo $w; ?>Max"><?php echo DLUGOSC_META_OPIS; ?></span>
                            </p>                             
                            
                            <p>
                              <label for="slowa_meta_<?php echo $w; ?>">Meta Tagi - Słowa kluczowe:</label>
                              <input type="text" name="slowa_meta_<?php echo $w; ?>" id="slowa_meta_<?php echo $w; ?>" size="120" onkeyup="licznik_znakow_meta(this,'iloscZnakowSlowa_<?php echo $w; ?>')" value="" />
                            </p>      

                            <p class="LicznikMeta">
                              <label></label>
                              Ilość wpisanych znaków: <span class="iloscZnakow" id="iloscZnakowSlowa_<?php echo $w; ?>">0</span>
                              zalecana maksymalna ilość: <span class="iloscZnakow" id="iloscZnakowSlowa_<?php echo $w; ?>Max"><?php echo DLUGOSC_META_SLOWA; ?></span>
                            </p>                            
                            
                            <div class="edytor" style="margin-bottom:10px">
                              <textarea cols="50" rows="30" id="edytor_<?php echo $w; ?>" name="edytor_<?php echo $w; ?>"></textarea>
                            </div>                                 

                            <div id="wyniki_<?php echo $w; ?>" class="PoleGalerii">
                            
                                <div class="NaglowekGalerii">Zdjęcie galerii nr <span>1</span></div>
                                
                                <p>
                                  <label for="foto_1_<?php echo $w; ?>">Ścieżka zdjęcia:</label>           
                                  <input type="text" name="zdjecie_1_<?php echo $w; ?>" size="95" value="" ondblclick="openFileBrowser('foto_1_<?php echo $w; ?>','','<?php echo KATALOG_ZDJEC; ?>')" id="foto_1_<?php echo $w; ?>" autocomplete="off" /><em class="TipIkona"><b>Kliknij dwukrotnie w pole obok żeby otworzyć okno przeglądarki zdjęć</b></em>
                                  <em class="TipChmurka"><b>Usuń przypisane zdjęcie</b><span class="usun_zdjecie" data-foto="foto_1_<?php echo $w; ?>" ></span></em>
                                  <span class="PrzegladarkaZdjec TipChmurka" onclick="openFileBrowser('foto_1_<?php echo $w; ?>','','<?php echo KATALOG_ZDJEC; ?>')"><b>Kliknij żeby otworzyć okno przeglądarki zdjęć</b></span>
                                </p>      

                                <div id="divfoto_1_<?php echo $w; ?>" style="padding-left:10px; display:none">
                                    <label>Zdjęcie:</label>
                                    <span id="fofoto_1_<?php echo $w; ?>">
                                        <span class="zdjecie_tbl">
                                            <img src="obrazki/_loader_small.gif" alt="" />
                                        </span>
                                    </span> 
                                </div>                                  
                            
                                <p>
                                    <label for="opis_zdjecia_1_<?php echo $w; ?>">Opis zdjęcia:</label>
                                    <textarea name="opis_zdjecia_1_<?php echo $w; ?>" id="opis_zdjecia_1_<?php echo $w; ?>" rows="5" cols="50"></textarea>
                                </p>
                                
                                <p>
                                    <label for="alt_1_<?php echo $w; ?>">Opis znacznika ALT:</label>
                                    <input type="text" value="" name="alt_1_<?php echo $w; ?>" id="alt_1_<?php echo $w; ?>" size="60" />
                                </p>
                                
                                <p>
                                    <label for="sort_1_<?php echo $w; ?>">Kolejność wyświetlania w galerii:</label>  
                                    <input class="calkowita" type="text" value="" name="sort_1_<?php echo $w; ?>" id="sort_1_<?php echo $w; ?>" size="4" />
                                </p>                                 
                                
                            </div>      

                            <div style="padding:10px;padding-top:20px;">
                                <span class="dodaj" onclick="dodaj_galerie(<?php echo $w; ?>)" style="cursor:pointer">dodaj nowe zdjęcie</span>
                            </div> 

                            <input value="1" type="hidden" name="ile_pol_<?php echo $w; ?>" id="ile_pol_<?php echo $w; ?>" />                            
                            
                        </div>
                        <?php                    
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
              <button type="button" class="przyciskNon" onclick="cofnij('galerie','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>');">Powrót</button>    
            </div>            
            
          </div>

          </form>

    </div>
    
    <?php
    include('stopka.inc.php');    
    
} ?>