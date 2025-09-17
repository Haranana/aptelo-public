<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
    
        $pola = array(
                array('newsdesk_status','1'),
                array('newsdesk_image',$filtr->process($_POST['zdjecie'])),
                array('newsdesk_icon',$filtr->process($_POST['ikona'])),
                array('newsdesk_author',$filtr->process($_POST['autor'])),
                array('newsdesk_date_added',date('Y-m-d', FunkcjeWlasnePHP::my_strtotime($filtr->process($_POST['data_dodania'])))),
                array('newsdesk_customers_group_id',((isset($_POST['grupa_klientow'])) ? implode(',', (array)$_POST['grupa_klientow']) : 0)),
                array('newsdesk_structured_data_status',(int)$_POST['dane_strukturalne_status']),
                array('newsdesk_structured_data_type',$filtr->process($_POST['dane_strukturalne_typ'])),
                array('newsdesk_structured_data_publisher_name',$filtr->process($_POST['dane_strukturalne_wydawca'])),
                array('newsdesk_structured_data_publisher_image',$filtr->process($_POST['dane_strukturalne_wydawca_logo'])));                
        
        $sql = $db->insert_query('newsdesk' , $pola);
        $id_dodanej_pozycji = $db->last_id_query();
        
        unset($pola);
        
        $pola = array(
                array('newsdesk_id',(int)$id_dodanej_pozycji),
                array('categories_id',$filtr->process($_POST['kategoria'])));
        
        $sql = $db->insert_query('newsdesk_to_categories' , $pola);
        
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
                    array('newsdesk_id',(int)$id_dodanej_pozycji),
                    array('language_id',(int)$ile_jezykow[$w]['id']),
                    array('newsdesk_article_name',$filtr->process($_POST['nazwa_'.$w])),
                    array('newsdesk_article_short_text',$filtr->process($_POST['opis_krotki_'.$w])),
                    array('newsdesk_article_description',$filtr->process($_POST['opis_'.$w])),
                    array('newsdesk_meta_title_tag',$filtr->process($_POST['tytul_meta_'.$w])),      
                    array('newsdesk_meta_desc_tag',$filtr->process($_POST['opis_meta_'.$w])),
                    array('newsdesk_meta_keywords_tag',$filtr->process($_POST['slowa_meta_'.$w])),
                    array('newsdesk_link_canonical',$filtr->process($_POST['link_kanoniczny_'.$w])));                      

            if ( trim((string)$_POST['og_title_'.$w]) != '' && trim((string)$_POST['og_description_'.$w]) && trim((string)$_POST['og_image_'.$w]) != '' ) {
                 //
                 $pola[] = array('newsdesk_og_title',$filtr->process($_POST['og_title_'.$w]));
                 $pola[] = array('newsdesk_og_description',$filtr->process($_POST['og_description_'.$w]));
                 $pola[] = array('newsdesk_og_image',$filtr->process($_POST['og_image_'.$w]));
                 //
            }    
            
            $sql = $db->insert_query('newsdesk_description' , $pola);
            unset($pola);
            
        }

        unset($ile_jezykow);

        if (isset($id_dodanej_pozycji) && $id_dodanej_pozycji > 0) {
          
            if ( isset($_POST['powrot']) && (int)$_POST['powrot'] == 1 ) {
                //            
                Funkcje::PrzekierowanieURL('aktualnosci_edytuj.php?id_poz='.$id_dodanej_pozycji);
                //
              } else {
                //
                Funkcje::PrzekierowanieURL('aktualnosci.php?id_poz='.$id_dodanej_pozycji);
                //
            }             

        } else {
          
            Funkcje::PrzekierowanieURL('aktualnosci.php');
            
        }
        
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>

    <div id="naglowek_cont">Dodawanie pozycji</div>
    <div id="cont">

          <form action="aktualnosci/aktualnosci_dodaj.php" method="post" id="poForm" class="cmxform"> 
          
          <div class="poleForm">
            <div class="naglowek">Dodawanie danych</div>
            
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
                    
                    $('input.datepicker').Zebra_DatePicker({
                       format: 'd-m-Y',
                       inside: false,
                       readonly_element: true,
                       show_clear_date: false
                    });      
                });            
                </script> 
                
                <div id="ZakladkiEdycji">
                
                    <div id="LeweZakladki">
                    
                        <a href="javascript:gold_tabs_horiz('0','0','opis_')" class="a_href_info_zakl" id="zakl_link_0">Podstawowe dane</a>   
                        <a href="javascript:gold_tabs_horiz('1','100','opis_krotki_')" class="a_href_info_zakl" id="zakl_link_1">Tekst skrócony</a>
                        <a href="javascript:gold_tabs_horiz('2','200')" class="a_href_info_zakl" id="zakl_link_2">Pozycjonowanie</a>
                        
                    </div>

                    <div id="PrawaStrona">
                    
                        <div id="zakl_id_0" style="display:none;">

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
                              <label for="ikona">Grafika ikony:</label>           
                              <input type="text" name="ikona" size="95" value="" class="obrazek" ondblclick="openFileBrowser('ikona','','<?php echo KATALOG_ZDJEC; ?>')" id="ikona" />   
                              <em class="TipIkona"><b>Kliknij dwukrotnie w pole obok żeby otworzyć okno przeglądarki zdjęć</b></em>
                              <span class="usun_zdjecie TipChmurka" data-foto="ikona"><b>Kliknij w ikonę żeby usunąć przypisany obrazek</b></span>
                              <span class="PrzegladarkaZdjec TipChmurka" onclick="openFileBrowser('ikona','','<?php echo KATALOG_ZDJEC; ?>')"><b>Kliknij żeby otworzyć okno przeglądarki zdjęć</b></span>
                            </p>      

                            <div id="divikona" style="padding-left:10px;display:none">
                                <label>Ikona:</label>
                                <span id="foikona">
                                    <span class="zdjecie_tbl">
                                        <img src="obrazki/_loader_small.gif" alt="" />
                                    </span>
                                </span> 
                            </div> 
                            
                            <p>
                                <label for="kategoria">Przypisany do kategorii:</label>
                                <?php
                                $sqls = $db->open_query('select distinct * from newsdesk_categories n, newsdesk_categories_description nd where n.categories_id = nd.categories_id and nd.language_id = "' . (int)$_SESSION['domyslny_jezyk']['id'] . '" order by n.sort_order, nd.categories_name ');  
                                //
                                $tab_tmp = array();
                                $tab_tmp[] = array('id' => 0, 'text' => 'bez kategorii ...');      
                                //
                                while ($kategorie = $sqls->fetch_assoc()) {
                                    //
                                    $nazwa = $kategorie['categories_name'];
                                    //
                                    // sprawdzi czy nie jest podkategoria
                                    if ( $kategorie['parent_id'] > 0 ) {
                                         //
                                         $sqlsp = $db->open_query('select distinct * from newsdesk_categories n, newsdesk_categories_description nd where n.categories_id = nd.categories_id and nd.language_id = "'.(int)$_SESSION['domyslny_jezyk']['id'].'" and n.categories_id = ' . (int)$kategorie['parent_id']);  
                                         if ((int)$db->ile_rekordow($sqlsp) > 0) {
                                            //
                                            $kategorie_parent = $sqlsp->fetch_assoc();
                                            $nazwa = $kategorie_parent['categories_name'] . ' / ' . $nazwa;
                                            //
                                         }
                                         //
                                         $db->close_query($sqlsp);
                                         //
                                    }
                                    //
                                    $tab_tmp[] = array('id' => $kategorie['categories_id'],
                                                       'text' => $nazwa);                                
                                    //
                                    unset($nazwa);
                                    //
                                }
                                $db->close_query($sqls);
                                //
                                echo Funkcje::RozwijaneMenu('kategoria', $tab_tmp, '', 'id="kategoria"'); 
                                //
                                unset($tab_tmp);
                                ?>
                            </p> 
                            
                            <p>
                                <label for="data_dodania">Data dodania:</label>
                                <input type="text" name="data_dodania" id="data_dodania" value="<?php echo date('d-m-Y',time()); ?>" size="20"  class="datepicker" />                                             
                            </p>  
                            
                            <p>
                                <label for="autor">Autor:</label>
                                <input type="text" name="autor" id="autor" value="" size="30" />                                             
                            </p>                               

                            <table class="WyborCheckbox">
                                <tr>
                                    <td><label>Widoczny dla grupy klientów:</label></td>
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
                            
                            <div class="ostrzezenie" style="margin:0px 15px 10px 25px">Jeżeli nie zostanie wybrana żadna grupa klientów to artykuł będzie widoczny dla wszystkich klientów.</div>                           
                            
                            <div class="info_tab">
                            <?php
                            for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                                echo '<span id="link_'.$w.'" class="a_href_info_tab" onclick="gold_tabs(\''.$w.'\',\'opis_\',400)">'.$ile_jezykow[$w]['text'].'</span>';
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
                                            <label class="required" for="nazwa_0">Tytuł artykułu:</label>
                                            <input type="text" name="nazwa_<?php echo $w; ?>" size="75" value="" id="nazwa_0" />
                                           <?php } else { ?>
                                            <label for="nazwa_<?php echo $w; ?>">Tytuł artykułu:</label>   
                                            <input type="text" name="nazwa_<?php echo $w; ?>" id="nazwa_<?php echo $w; ?>" size="75" value="" />
                                           <?php } ?>
                                        </p>                                      
                                    
                                        <div class="edytor">
                                          <textarea cols="50" rows="30" id="opis_<?php echo $w; ?>" name="opis_<?php echo $w; ?>"></textarea>
                                        </div>                            

                                    </div>
                                    <?php                    
                                }                    
                                ?>                      
                            </div>
                            
                        </div>
                            
                        <div id="zakl_id_1" style="display:none;">

                            <div class="info_tab">
                            <?php
                            for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                                echo '<span id="link_'.($w+100).'" class="a_href_info_tab" onclick="gold_tabs(\''.($w+100).'\',\'opis_krotki_\',400)">'.$ile_jezykow[$w]['text'].'</span>';
                            }                    
                            ?>                   
                            </div>
                            
                            <div style="clear:both"></div>
                            
                            <div class="info_tab_content">
                                <?php
                                for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                                    ?>
                                    
                                    <div id="info_tab_id_<?php echo ($w + 100); ?>" style="display:none;">
                                    
                                        <div class="edytor">
                                          <textarea cols="50" rows="30" id="opis_krotki_<?php echo ($w + 100); ?>" name="opis_krotki_<?php echo $w; ?>"></textarea>
                                        </div>                            

                                    </div>
                                    <?php                    
                                }                    
                                ?>                      
                            </div>
                            
                        </div>                        
                        
                        <div id="zakl_id_2" style="display:none;">
                        
                            <div class="info_tab">
                            <?php
                            for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                                echo '<span id="link_'.($w+200).'" class="a_href_info_tab" onclick="gold_tabs(\''.($w+200).'\')">'.$ile_jezykow[$w]['text'].'</span>';
                            }                    
                            ?>                   
                            </div>
                            
                            <div style="clear:both"></div>
                            
                            <div class="info_tab_content">
                                <?php
                                for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                                    ?>
                                    
                                    <div id="info_tab_id_<?php echo ($w + 200); ?>" style="display:none;">                        

                                        <p>
                                          <label for="tytul_meta_<?php echo $w; ?>">Meta Tagi - Tytuł:</label>
                                          <textarea name="tytul_meta_<?php echo $w; ?>" id="tytul_meta_<?php echo $w; ?>" onkeyup="licznik_znakow_meta(this,'iloscZnakowNazwa_<?php echo $w; ?>')" rows="4" cols="70"></textarea>
                                        </p> 
                                        
                                        <p class="LicznikMeta">
                                          <label></label>
                                          Ilość wpisanych znaków: <span class="iloscZnakow" id="iloscZnakowNazwa_<?php echo $w; ?>">0</span>
                                          zalecana maksymalna ilość: <span class="iloscZnakow" id="iloscZnakowNazwa_<?php echo $w; ?>Max"><?php echo DLUGOSC_META_NAZWA; ?></span>
                                        </p>                                            
                                        
                                        <p>
                                          <label for="opis_meta_<?php echo $w; ?>">Meta Tagi - Opis:</label>
                                          <textarea name="opis_meta_<?php echo $w; ?>" id="opis_meta_<?php echo $w; ?>" onkeyup="licznik_znakow_meta(this,'iloscZnakowOpis_<?php echo $w; ?>')" rows="4" cols="70"></textarea>
                                        </p>   
                                        
                                        <p class="LicznikMeta">
                                          <label></label>
                                          Ilość wpisanych znaków: <span class="iloscZnakow" id="iloscZnakowOpis_<?php echo $w; ?>">0</span>
                                          zalecana maksymalna ilość: <span class="iloscZnakow" id="iloscZnakowOpis_<?php echo $w; ?>Max"><?php echo DLUGOSC_META_OPIS; ?></span>
                                        </p>                                         
                                        
                                        <p>
                                          <label for="slowa_meta_<?php echo $w; ?>">Meta Tagi - Słowa kluczowe:</label>
                                          <textarea name="slowa_meta_<?php echo $w; ?>" id="slowa_meta_<?php echo $w; ?>" onkeyup="licznik_znakow_meta(this,'iloscZnakowSlowa_<?php echo $w; ?>')" rows="4" cols="70"></textarea>
                                        </p>
                                        
                                        <p class="LicznikMeta">
                                          <label></label>
                                          Ilość wpisanych znaków: <span class="iloscZnakow" id="iloscZnakowSlowa_<?php echo $w; ?>">0</span>
                                          zalecana maksymalna ilość: <span class="iloscZnakow" id="iloscZnakowSlowa_<?php echo $w; ?>Max"><?php echo DLUGOSC_META_SLOWA; ?></span>
                                        </p>
                                        
                                        <hr style="color:#82b4cd;border-top:1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;margin:20px 10px 20px 10px" />

                                        <div class="ostrzezenie" style="margin:0px 0px 15px 10px">Warunkiem wyświetlania tagów Open Graph dla tego artykułu jest wypełnienie pola tytułu strony oraz wskazanie obrazka</div>
                                        
                                        <p>
                                          <label for="og_title_<?php echo $w; ?>">Tag Open Graph - tytuł strony:</label>   
                                          <input type="text" name="og_title_<?php echo $w; ?>" id="og_title_<?php echo $w; ?>" size="120" value="" />
                                        </p> 
                                        
                                        <p>
                                          <label for="og_description_<?php echo $w; ?>">Tag Open Graph - krótki opis treści:</label>   
                                          <textarea name="og_description_<?php echo $w; ?>" id="og_description_<?php echo $w; ?>" cols="117" rows="3"></textarea>
                                        </p>    

                                        <p>
                                          <label for="foto">Tag Open Graph - obrazek artykułu:<em class="TipIkona"><b>Obrazek, który będzie użyty jako miniaturka, musi mieć rozmiary co najmniej 50 x 50 pikseli, proporcje maksymalnie 3:1 i być zapisany w formacie JPG, PNG lub GIF</b></em></label>           
                                          <input type="text" name="og_image_<?php echo $w; ?>" size="120" value="" class="obrazek" ondblclick="openFileBrowser('foto_<?php echo $w; ?>','','<?php echo KATALOG_ZDJEC; ?>')" id="foto_<?php echo $w; ?>" autocomplete="off" />                 
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
                                        </div>     

                                        <hr style="color:#82b4cd;border-top:1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;margin:20px 10px 20px 10px" />

                                        <p>
                                          <label for="link_kanoniczny_<?php echo $w; ?>">Link kanoniczny:</label>
                                          <input type="text" name="link_kanoniczny_<?php echo $w; ?>" id="link_kanoniczny_<?php echo $w; ?>" size="80" value="" />
                                          <em class="TipIkona"><b>Sam adres artykułu - np moj-artykuł-n-1.html - bez adresu sklepu z http:\\...</b></em>
                                        </p>                                         
                            
                                    </div>
                                    <?php                    
                                }                    
                                ?>                      
                            </div>
                            
                            <div class="DaneStrukturalneNaglowek">Dane strukturalne (schema.org)</div>
                            
                            <div class="ostrzezenie" style="margin:0px 0px 15px 25px">Warunkiem poprawnego wyświetlania danych strukturalnych jest dodanie do artykułu zdjęcia, autora, daty dodania oraz n/w danych.</div>
                            
                            <p>
                              <label>Wyświetlaj dane strukturalne dla tego artukułu:</label>
                              <input type="radio" value="0" name="dane_strukturalne_status" id="newsdesk_structured_data_status_nie" checked="checked" /><label class="OpisFor" for="newsdesk_structured_data_status_nie">nie</label>
                              <input type="radio" value="1" name="dane_strukturalne_status" id="newsdesk_structured_data_status_tak" /><label class="OpisFor" for="newsdesk_structured_data_status_tak">tak</label>
                            </p>    

                            <p>
                              <label>Rodzaj artykułu:</label>
                              <input type="radio" value="artykuł" name="dane_strukturalne_typ" id="dane_strukturalne_typ_artykul" checked="checked" /><label class="OpisFor" for="dane_strukturalne_typ_artykul">artykuł (schema.org/Article)</label>
                              <input type="radio" value="blog" name="dane_strukturalne_typ" id="dane_strukturalne_typ_blog" /><label class="OpisFor" for="dane_strukturalne_typ_blog">wpis z bloga (schema.org/BlogPosting)</label>
                            </p> 

                            <p>
                              <label for="wydawca">Wydawca (publisher):</label>
                              <input type="text" name="dane_strukturalne_wydawca" id="wydawca" size="80" value="<?php echo DANE_NAZWA_FIRMY_SKROCONA; ?>" />
                              <em class="TipIkona"><b>Nazwa wydawcy artykułu (firmy publikującej artykuł - domyślne nazwa skrócona sklepu z menu Konfiguracja / Dane teleadresowe) - niewidoczny w treści artykułu</b></em>
                            </p>   

                            <p>
                              <label for="wydawca_logo">Wydawca (publisher) logo:</label>           
                              <input type="text" name="dane_strukturalne_wydawca_logo" size="80" value="<?php echo LOGO_FIRMA; ?>" class="obrazek" ondblclick="openFileBrowser('wydawca_logo','','<?php echo KATALOG_ZDJEC; ?>')" id="wydawca_logo" autocomplete="off" />                 
                              <em class="TipIkona"><b>Logo wydawcy artykułu (firmy publikującej artykuł - domyślne logo sklepu z menu Konfiguracja / Dane teleadresowe) - niewidoczny w treści artykułu</b></em>
                              <span class="usun_zdjecie TipChmurka" data-foto="wydawca_logo"><b>Usuń przypisane zdjęcie</b></span>
                              <span class="PrzegladarkaZdjec TipChmurka" onclick="openFileBrowser('wydawca_logo','','<?php echo KATALOG_ZDJEC; ?>')"><b>Kliknij żeby otworzyć okno przeglądarki zdjęć</b></span>
                            </p>                              
                            
                        </div>                        
                        
                    </div>  
                
                </div>  
                
                <script>
                gold_tabs_horiz('0','0','opis_');
                </script>            
            
          </div>
          
          <br />
             
          <div class="przyciski_dolne">
              <input type="hidden" name="powrot" id="powrot" value="0" />
              <input type="submit" class="przyciskNon" value="Zapisz dane" />
              <input type="submit" class="przyciskNon" value="Zapisz dane i pozostań w edycji" onclick="$('#powrot').val(1)" />   
              <button type="button" class="przyciskNon" onclick="cofnij('aktualnosci','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>');">Powrót</button>    
          </div>           

          </form>

    </div>
    
    <?php
    include('stopka.inc.php');    
    
} ?>
