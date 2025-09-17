<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
    
        $pola = array(
                array('categories_image',$filtr->process($_POST['zdjecie'])),
                array('sort_order',(int)$_POST['sort']),
                array('parent_id',(int)$_POST['id_kat']),
                array('search',(int)$_POST['szukaj']),
                array('newsdesk_categories_structured_data_status',(int)$_POST['dane_strukturalne_status']),
                array('newsdesk_categories_structured_data_type',$filtr->process($_POST['dane_strukturalne_typ'])));                   
        
        $sql = $db->insert_query('newsdesk_categories' , $pola);
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
                    array('categories_id',(int)$id_dodanej_pozycji),
                    array('language_id',(int)$ile_jezykow[$w]['id']),
                    array('categories_name',$filtr->process($_POST['nazwa_'.$w])),
                    array('categories_meta_title_tag',$filtr->process($_POST['tytul_'.$w])),
                    array('categories_meta_desc_tag',$filtr->process($_POST['opis_'.$w])),        
                    array('categories_meta_keywords_tag',$filtr->process($_POST['slowa_'.$w])),
                    array('categories_description',$filtr->process($_POST['edytor_'.$w])));        
            $sql = $db->insert_query('newsdesk_categories_description' , $pola);
            unset($pola);
            //
        }

        unset($ile_jezykow);    

        if ( isset($_POST['powrot']) && (int)$_POST['powrot'] == 1 ) {
            //            
            Funkcje::PrzekierowanieURL('aktualnosci_kategorie_edytuj.php?kat_id=' . (int)$id_dodanej_pozycji);
            //
          } else {        
            //
            Funkcje::PrzekierowanieURL('aktualnosci.php');
            //
        }        
        
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>

    <div id="naglowek_cont">Dodawanie pozycji</div>
    <div id="cont">

          <form action="aktualnosci/aktualnosci_kategorie_dodaj.php" method="post" id="poForm" class="cmxform"> 
          
          <div class="poleForm">
            <div class="naglowek">Dodawanie danych</div>
            
            <div class="pozycja_edytowana">    
            
                <input type="hidden" name="akcja" value="zapisz" />

                <?php $ile_jezykow = Funkcje::TablicaJezykow(); ?>

                <script>
                $(document).ready(function() {
                  $("#poForm").validate({
                    rules: {
                      nazwa_0: {
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

                <p>
                  <label>Kategoria nadrzędna:</label>
                </p> 
                
                <div id="drzewo" style="width:95%; max-width:650px">
                    <?php
                    echo '<table class="pkc">
                          <tr>
                            <td class="lfp"><input type="radio" value="0" name="id_kat" id="id_kat" checked="checked" /><label class="OpisFor" for="id_kat">-- brak kategorii nadrzędnej --</label></td>
                          </tr>';                    
                    
                    $sqls = $db->open_query('select distinct * from newsdesk_categories n, newsdesk_categories_description nd where n.categories_id = nd.categories_id and nd.language_id = "'.(int)$_SESSION['domyslny_jezyk']['id'].'" and parent_id = 0 order by n.sort_order, nd.categories_name ');  
                    
                    if ((int)$db->ile_rekordow($sqls) > 0) {
                        //
                        while ($kategorie = $sqls->fetch_assoc()) {
                            //
                            echo '<tr>
                                    <td class="lfp"><input type="radio" value="'.$kategorie['categories_id'].'" name="id_kat" id="id_kat_'.$kategorie['categories_id'].'" />
                                          <label class="OpisFor" for="id_kat_'.$kategorie['categories_id'].'"> '.$kategorie['categories_name'].'</label></td>
                                    </td>
                                  </tr>';
                            //
                        }
                        //
                    }
                    //
                    $db->close_query($sqls);
                    //
                    echo '</table>';
                    ?> 
                </div>                

                <p>
                  <label for="foto">Ścieżka zdjęcia:</label>           
                  <input type="text" name="zdjecie" size="95" value="" class="obrazek" ondblclick="openFileBrowser('foto','','<?php echo KATALOG_ZDJEC; ?>')" id="foto" autocomplete="off" /><em class="TipIkona"><b>Kliknij dwukrotnie w pole obok żeby otworzyć okno przeglądarki zdjęć</b></em>
                  <span class="usun_zdjecie TipChmurka" data-foto="foto"><b>Kliknij w ikonę żeby usunąć przypisane zdjęcie</b></span>
                  <span class="PrzegladarkaZdjec TipChmurka" onclick="openFileBrowser('foto','','<?php echo KATALOG_ZDJEC; ?>')"><b>Kliknij żeby otworzyć okno przeglądarki zdjęć</b></span>
                </p>      

                <div id="divfoto" style="padding-left:10px; display:none">
                    <label>Zdjęcie:</label>
                    <span id="fofoto">
                        <span class="zdjecie_tbl">
                            <img src="obrazki/_loader_small.gif" alt="" />
                        </span>
                    </span>  
                </div>
                
                <p>
                  <label>Czy w tej kategorii ma być wyświetlana wyszukiwarka artykułów ?</label>
                  <input type="radio" name="szukaj" value="1" id="szukaj_tak" checked="checked" /><label class="OpisFor" for="szukaj_tak">tak</label>
                  <input type="radio" name="szukaj" value="0" id="szukaj_nie" /><label class="OpisFor" for="szukaj_nie">nie</label>              
                </p>                       
                
                <p>
                  <label for="sort">Kolejność wyświetlania:</label>
                  <input type="text" name="sort" size="5" value="" id="sort" />
                </p>              

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
                                <label class="required" for="nazwa_0">Nazwa kategorii:</label>
                                <input type="text" name="nazwa_<?php echo $w; ?>" size="65" value="" id="nazwa_0" />
                               <?php } else { ?>
                                <label for="nazwa_<?php echo $w; ?>">Nazwa kategorii:</label>   
                                <input type="text" name="nazwa_<?php echo $w; ?>" id="nazwa_<?php echo $w; ?>" size="65" value="" />
                               <?php } ?>
                            </p> 
                            
                            <p>
                              <label for="tytul_<?php echo $w; ?>">Meta Tagi - Tytuł:</label>
                              <input type="text" name="tytul_<?php echo $w; ?>" id="tytul_<?php echo $w; ?>" size="120" onkeyup="licznik_znakow_meta(this,'iloscZnakowNazwa_<?php echo $w; ?>')" value="" />
                            </p> 
                            
                            <p class="LicznikMeta">
                              <label></label>
                              Ilość wpisanych znaków: <span class="iloscZnakow" id="iloscZnakowNazwa_<?php echo $w; ?>">0</span>
                              zalecana maksymalna ilość: <span class="iloscZnakow" id="iloscZnakowNazwa_<?php echo $w; ?>Max"><?php echo DLUGOSC_META_NAZWA; ?></span>
                            </p>                              
                            
                            <p>
                              <label for="opis_<?php echo $w; ?>">Meta Tagi - Opis:</label>
                              <input type="text" name="opis_<?php echo $w; ?>" id="opis_<?php echo $w; ?>" size="120" onkeyup="licznik_znakow_meta(this,'iloscZnakowOpis_<?php echo $w; ?>')" value="" />
                            </p>   
                            
                            <p class="LicznikMeta">
                              <label></label>
                              Ilość wpisanych znaków: <span class="iloscZnakow" id="iloscZnakowOpis_<?php echo $w; ?>">0</span>
                              zalecana maksymalna ilość: <span class="iloscZnakow" id="iloscZnakowOpis_<?php echo $w; ?>Max"><?php echo DLUGOSC_META_OPIS; ?></span>
                            </p>                            
                            
                            <p>
                              <label for="slowa_<?php echo $w; ?>">Meta Tagi - Słowa kluczowe:</label>
                              <input type="text" name="slowa_<?php echo $w; ?>" id="slowa_<?php echo $w; ?>" size="120" onkeyup="licznik_znakow_meta(this,'iloscZnakowSlowa_<?php echo $w; ?>')" value="" />
                            </p>    
                            
                            <p class="LicznikMeta">
                              <label></label>
                              Ilość wpisanych znaków: <span class="iloscZnakow" id="iloscZnakowSlowa_<?php echo $w; ?>">0</span>
                              zalecana maksymalna ilość: <span class="iloscZnakow" id="iloscZnakowSlowa_<?php echo $w; ?>Max"><?php echo DLUGOSC_META_SLOWA; ?></span>
                            </p>                                
                            
                            <div class="edytor">
                              <textarea cols="50" rows="30" id="edytor_<?php echo $w; ?>" name="edytor_<?php echo $w; ?>"></textarea>
                            </div>                            

                        </div>
                        <?php                    
                    }                    
                    ?>                      
                </div>
                
                <script>
                gold_tabs('0','edytor_');
                </script> 
                
            </div>
            
            <div class="DaneStrukturalneNaglowek" style="margin-top:10px">Dane strukturalne (schema.org)</div>
            
            <div class="ostrzezenie" style="margin:0px 0px 15px 25px">Warunkiem poprawnego wyświetlania danych strukturalnych jest dodanie do artykułów z tej kategorii zdjęcia, autora, daty dodania oraz danych wydawcy.</div>
            
            <p>
              <label>Wyświetlaj dane strukturalne dla tej kategorii artykułów:</label>
              <input type="radio" value="0" name="dane_strukturalne_status" id="newsdesk_categories_structured_data_status_nie" checked="checked" /><label class="OpisFor" for="newsdesk_categories_structured_data_status_nie">nie</label>
              <input type="radio" value="1" name="dane_strukturalne_status" id="newsdesk_categories_structured_data_status_tak" /><label class="OpisFor" for="newsdesk_categories_structured_data_status_tak">tak</label>
            </p>    

            <p>
              <label>Rodzaj artykułu:</label>
              <input type="radio" value="artykuł" name="dane_strukturalne_typ" id="dane_strukturalne_typ_artykul" checked="checked" /><label class="OpisFor" for="dane_strukturalne_typ_artykul">lista zwykłych artykułów (schema.org/Article)</label>
              <input type="radio" value="blog" name="dane_strukturalne_typ" id="dane_strukturalne_typ_blog" /><label class="OpisFor" for="dane_strukturalne_typ_blog">wpisy z bloga (schema.org/BlogPosting)</label>
            </p>                 
            
            <div class="przyciski_dolne">
              <input type="hidden" name="powrot" id="powrot" value="0" />
              <input type="submit" class="przyciskNon" value="Zapisz dane" />
              <input type="submit" class="przyciskNon" value="Zapisz dane i pozostań w edycji" onclick="$('#powrot').val(1)" />
              <button type="button" class="przyciskNon" onclick="cofnij('aktualnosci','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>');">Powrót</button>       
            </div>            
            
          </div>

          </form>

    </div>
    
    <?php
    include('stopka.inc.php');    
    
} ?>
