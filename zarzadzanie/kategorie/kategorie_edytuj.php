<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
    
        $id = (int)$_POST['id'];
    
        $pola = array(
                array('categories_image',$filtr->process($_POST['zdjecie'])),
                array('categories_icon',$filtr->process($_POST['ikona'])),
                array('sort_order',(int)$_POST['sort']));
                
        if ( isset($_POST['kolor_status']) ) {
             $pola[] = array('categories_color',$filtr->process($_POST['kolor']));
             $pola[] = array('categories_color_status',1);
           } else {
             $pola[] = array('categories_color','');
             $pola[] = array('categories_color_status',0);
        }
        
        if ( isset($_POST['kolor_status_tla']) ) {
             $pola[] = array('categories_background_color',$filtr->process($_POST['kolor_tla']));
             $pola[] = array('categories_background_color_status',1);
           } else {
             $pola[] = array('categories_background_color','');
             $pola[] = array('categories_background_color_status',0);
        }    

        if ( isset($_POST['banner_kategoria']) && $_POST['banner_kategoria'] == 'tak' ) {
             $pola[] = array('categories_banner_image_status',1);
             $pola[] = array('categories_banner_image',$filtr->process($_POST['banner_kategoria_grafika']));
             $pola[] = array('categories_banner_color',$filtr->process($_POST['kolor_banner_czcionka']));
             $pola[] = array('categories_banner_font_size',$filtr->process($_POST['rozmiar_banner_czcionka']));
             $pola[] = array('categories_banner_font_align',$filtr->process($_POST['wyrownanie_tekstu_banner_czcionka']));             
           } else {
             $pola[] = array('categories_banner_image_status',0);
             $pola[] = array('categories_banner_image','');
             $pola[] = array('categories_banner_color','');
             $pola[] = array('categories_banner_font_size','');
             $pola[] = array('categories_banner_font_align',''); 
        }        
        
        $sql = $db->update_query('categories' , $pola, " categories_id = '".(int)$id."'");
        
        unset($pola);
        
        // kasuje rekordy w tablicy
        $db->delete_query('categories_description' , " categories_id = '".(int)$_POST["id"]."'");        
        
        $ile_jezykow = Funkcje::TablicaJezykow();
        $nazwa_domyslna = '';
        
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
                    array('categories_id',(int)$id),
                    array('categories_name',$filtr->process($_POST['nazwa_'.$w])),
                    array('categories_name_info',$filtr->process($_POST['nazwa_info_'.$w])),
                    array('categories_name_menu',$filtr->process($_POST['nazwa_menu_'.$w])),
                    array('categories_description_info',$filtr->process($_POST['opis_info_'.$w])),
                    array('categories_meta_title_tag',$filtr->process($_POST['tytul_'.$w])),
                    array('categories_meta_desc_tag',$filtr->process($_POST['opis_'.$w])),        
                    array('categories_meta_keywords_tag',$filtr->process($_POST['slowa_'.$w])),
                    array('categories_seo_url',$filtr->process($_POST['url_meta_'.$w])),
                    array('categories_link_canonical',$filtr->process($_POST['link_kanoniczny_'.$w])),
                    array('categories_description',$filtr->process($_POST['edytor_'.$w])),
                    array('categories_description_bottom',$filtr->process($_POST['edytor_dol_'.$w])),
                    array('categories_info_name',$filtr->process($_POST['info_nazwa_'.$w])),
                    array('categories_info_text',$filtr->process($_POST['edytor_info_'.$w])),                    
                    array('language_id',(int)$ile_jezykow[$w]['id']));        
            $sql = $db->insert_query('categories_description' , $pola);  
            unset($pola);
            //
            if ( $_SESSION['domyslny_jezyk']['id'] == $ile_jezykow[$w]['id'] ) {
                 $nazwa_domyslna = $filtr->process($_POST['nazwa_'.$w]);
            }
            //
        }

        unset($ile_jezykow);    
        
        // -------------------------------- przekierowanie ze starego sklepu
        
        // kasuje rekordy w tablicy
        $db->delete_query('location', " categories_id = '".$id."'");           
        
        if ( !empty($_POST['url_stary']) ) {
             //
             $pola = array(
                     array('urlf',$filtr->process($_POST['url_stary'])),
                     array('urlt',Seo::link_SEO( $nazwa_domyslna, $id, 'kategoria', '', false, false )),
                     array('url_type','kategoria'),
                     array('categories_id',(int)$id));
             $sql = $db->insert_query('location' , $pola);
             //
             unset($pola);             
             //
        }        

        if ( isset($_POST['powrot']) && (int)$_POST['powrot'] == 1 ) {
            //            
            Funkcje::PrzekierowanieURL('kategorie_edytuj.php?id_poz=' . $id);
            //
          } else {
            //
            if ( isset($_POST['zakladka']) && (int)$_POST['zakladka'] > 0 ) {
              
                Funkcje::PrzekierowanieURL('/zarzadzanie/wyglad/wyglad.php?zakladka='.(int)$_POST['zakladka']);
              
              } else {
              
                Funkcje::PrzekierowanieURL('kategorie.php?id_poz='.$id);
                
            } 
            //
        }       

    }
    
    // wczytanie naglowka HTML
    include('naglowek.inc.php');     
    ?>

    <div id="naglowek_cont">Edycja pozycji</div>
    <div id="cont">

        <form action="kategorie/kategorie_edytuj.php" method="post" id="poForm" class="cmxform"> 
    
        <div class="poleForm">
            <div class="naglowek">Edycja danych</div>        

            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select distinct * from categories where categories_id = '".(int)$_GET['id_poz']."'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {

                $info = $sql->fetch_assoc();  
                
                ?>
                <div class="pozycja_edytowana">    
                
                    <input type="hidden" name="akcja" value="zapisz" />
                    
                    <input type="hidden" name="id" value="<?php echo $info['categories_id']; ?>" />
                    
                    <?php if (isset($_GET['zakladka']) && (int)$_GET['zakladka'] > 0 ) { ?>
                    <input type="hidden" name="zakladka" value="<?php echo (int)$_GET['zakladka']; ?>" />
                    <?php } ?>                     

                    <?php $ile_jezykow = Funkcje::TablicaJezykow(); ?>

                    <script>
                    $(document).ready(function() {
                    $("#poForm").validate({
                      rules: {
                        nazwa_0: {
                          required: true
                        },               
                      },
                      messages: {
                        nazwa_0: {
                          required: "Pole jest wymagane."
                        },             
                      }
                    });
                    });
                    </script> 
                    
                    <script src="programy/jscolor/jscolor.js"></script> 

                    <p>
                      <label for="foto">Ścieżka zdjęcia:</label>           
                      <input type="text" name="zdjecie" size="95" value="<?php echo $info['categories_image']; ?>" class="obrazek" ondblclick="openFileBrowser('foto','','<?php echo KATALOG_ZDJEC; ?>')" id="foto" autocomplete="off" />                 
                      <em class="TipIkona"><b>Kliknij dwukrotnie w pole obok żeby otworzyć okno przeglądarki zdjęć</b></em>
                      <span class="usun_zdjecie TipChmurka" data-foto="foto"><b>Kliknij w ikonę żeby usunąć przypisane zdjęcie</b></span>
                      <span class="PrzegladarkaZdjec TipChmurka" onclick="openFileBrowser('foto','','<?php echo KATALOG_ZDJEC; ?>')"><b>Kliknij żeby otworzyć okno przeglądarki zdjęć</b></span>
                    </p>      

                    <div id="divfoto" style="padding-left:10px;display:none">
                        <label>Zdjęcie:</label>
                        <span id="fofoto">
                            <span class="zdjecie_tbl">
                                <img src="obrazki/_loader_small.gif" alt="" />
                            </span>
                        </span> 

                        <?php if (!empty($info['categories_image'])) { ?>
                        
                        <script>         
                        pokaz_obrazek_ajax('foto', '<?php echo $info['categories_image']; ?>')
                        </script>  
                        
                        <?php } ?>   
                        
                    </div>
                    
                    <p>
                      <label for="ikona">Grafika ikony:</label>           
                      <input type="text" name="ikona" size="95" value="<?php echo $info['categories_icon']; ?>" class="obrazek" ondblclick="openFileBrowser('ikona','','<?php echo KATALOG_ZDJEC; ?>')" id="ikona" />   
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

                        <?php if (!empty($info['categories_icon'])) { ?>
                        
                        <script>           
                        pokaz_obrazek_ajax('ikona', '<?php echo $info['categories_icon']; ?>')
                        </script>
                        
                        <?php } ?>   
                        
                    </div>                    
                    
                    <p>
                      <label for="sort">Kolejność wyświetlania:</label>
                      <input type="text" name="sort" size="5" value="<?php echo $info['sort_order']; ?>" id="sort" />
                    </p> 

                    <p>
                      <label for="kolor">Kolor nazwy kategorii:</label>
                      <input name="kolor" class="color" style="-moz-box-shadow:none" value="<?php echo $info['categories_color']; ?>" size="8" id="kolor" /> 
                      <em class="TipIkona"><b>Określa kolor wyświetlania nazwy kategorii w boxie kategorii</b></em> &nbsp;
                      <input type="checkbox" name="kolor_status" id="kolor_status" value="1" <?php echo (($info['categories_color_status'] == 1) ? 'checked="checked"' : ''); ?> /> <label class="OpisFor" for="kolor_status">wyświetlaj nazwę tej kategorii w kolorze<em class="TipIkona"><b>Czy nazwa kategorii ma być wyświetlana w kolorze ?</b></em></label>
                    </p>   

                    <p>
                      <label for="kolor_tla">Kolor tła nazwy kategorii:</label>
                      <input name="kolor_tla" class="color" style="-moz-box-shadow:none" value="<?php echo $info['categories_background_color']; ?>" size="8" id="kolor_tla" /> 
                      <em class="TipIkona"><b>Określa kolor tła pod nazwą kategorii w boxie kategorii</b></em> &nbsp; 
                      <input type="checkbox" name="kolor_status_tla" id="kolor_status_tla" value="1" <?php echo (($info['categories_background_color_status'] == 1) ? 'checked="checked"' : ''); ?> /> <label class="OpisFor" for="kolor_status_tla">wyświetlaj tło tej kategorii w kolorze<em class="TipIkona"><b>Czy tło nazwy kategorii ma być wyświetlane w kolorze ?</b></em></label>
                    </p>  
                    
                    <?php if ( Wyglad::TypSzablonu() != true ) { echo '<div style="display:none">'; } ?>

                    <p>
                      <label>Czy wyświetlać banner z nazwą kategorii nad listingiem produktów kategorii ?</label>
                      <input type="radio" value="tak" name="banner_kategoria" id="banner_kategoria_tak" onclick="$('#GrafikaKategorie').stop().slideDown()" <?php echo (($info['categories_banner_image_status'] == 1) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="banner_kategoria_tak">tak</label>
                      <input type="radio" value="nie" name="banner_kategoria" id="banner_kategoria_nie" onclick="$('#GrafikaKategorie').stop().slideUp()" <?php echo (($info['categories_banner_image_status'] == 0) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="banner_kategoria_nie">nie</label>
                    </p>
                    
                    <div id="GrafikaKategorie" <?php echo (($info['categories_banner_image_status'] == 0) ? 'style="display:none"' : ''); ?>>

                        <p>
                          <label for="banner_kategoria_grafika">Banner kategorii (będzie wyświetlany na 100% szerokości ekranu):</label>           
                          <input type="text" name="banner_kategoria_grafika" size="95" value="<?php echo $info['categories_banner_image']; ?>" class="obrazek" ondblclick="openFileBrowser('banner_kategoria_grafika','','<?php echo KATALOG_ZDJEC; ?>')" id="banner_kategoria_grafika" />   
                          <em class="TipIkona"><b>Kliknij dwukrotnie w pole obok żeby otworzyć okno przeglądarki zdjęć</b></em>
                          <span class="usun_zdjecie TipChmurka" data-foto="banner_kategoria_grafika"><b>Kliknij w ikonę żeby usunąć przypisany obrazek</b></span>
                          <span class="PrzegladarkaZdjec TipChmurka" onclick="openFileBrowser('banner_kategoria_grafika','','<?php echo KATALOG_ZDJEC; ?>')"><b>Kliknij żeby otworzyć okno przeglądarki zdjęć</b></span>
                        </p>      

                        <div id="divbanner_kategoria_grafika" style="padding-left:10px;display:none">
                            <label>Banner:</label>
                            <span id="fobanner_kategoria_grafika">
                                <span class="zdjecie_tbl">
                                    <img src="obrazki/_loader_small.gif" alt="" />
                                </span>
                            </span> 

                            <?php if (!empty($info['categories_banner_image'])) { ?>
                            
                            <script>           
                            pokaz_obrazek_ajax('banner_kategoria_grafika', '<?php echo $info['categories_banner_image']; ?>')
                            </script>
                            
                            <?php } ?>   
                            
                        </div>   

                        <p>
                          <label for="kolor_banner_czcionka">Kolor czcionki nazwy kategorii:</label>
                          <input name="kolor_banner_czcionka" class="color" style="-moz-box-shadow:none" value="<?php echo $info['categories_banner_color']; ?>" size="8" id="kolor_banner_czcionka" /> 
                          <em class="TipIkona"><b>Określa kolor czcionki wyświetlanej na bannerze kategorii</b></em>
                        </p> 

                        <p>
                          <label for="rozmiar_banner_czcionka">Rozmiar czcionki:</label>             
                          <?php
                          $tablica = array();
                          for ( $t = 0; $t < 40; $t++ ) {
                                $tablica[] = array('id' => (100 + ($t * 10)), 'text' => (100 + ($t * 10)) . '%');
                          }
                          echo Funkcje::RozwijaneMenu('rozmiar_banner_czcionka', $tablica, $info['categories_banner_font_size'], ' id="rozmiar_banner_czcionka"');
                          unset($tablica);
                          ?>
                        </p>           

                        <p>
                          <label for="wyrownanie_tekstu_banner_czcionka">Wyrównanie tekstu w poziomie:</label>             
                          <?php
                          $tablica = array(array('id' => 'left', 'text' => 'do lewej strony'),
                                           array('id' => 'center', 'text' => 'wyśrodkowane'),
                                           array('id' => 'right', 'text' => 'do prawej strony'));
                                            
                          echo Funkcje::RozwijaneMenu('wyrownanie_tekstu_banner_czcionka', $tablica, $info['categories_banner_font_align'], ' id="wyrownanie_tekstu_banner_czcionka"');
                          unset($tablica);
                          ?>
                        </p>                          

                    </div>

                    <?php if ( Wyglad::TypSzablonu() != true ) { echo '</div>'; } ?>
                    
                    <div class="info_tab">
                    <?php
                    for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                        echo '<span id="link_'.$w.'" class="a_href_info_tab" onclick="gold_tabs(\''.$w.'\',\'edytor_\',200,\'normalny\',\'edytor_info_\',\'edytor_dol_\')">'.$ile_jezykow[$w]['text'].'</span>';
                    }                    
                    ?>                   
                    </div>
                    
                    <div style="clear:both"></div>
                    
                    <div class="info_tab_content">
                        <?php
                        for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                            ?>
                            
                            <div id="info_tab_id_<?php echo $w; ?>" style="display:none;">
                            
                                <?php
                                // pobieranie danych jezykowych
                                $zapytanie_jezyk = "select distinct * from categories_description where categories_id = '".(int)$_GET['id_poz']."' and language_id = '" .(int)$ile_jezykow[$w]['id']."'";
                                $sqls = $db->open_query($zapytanie_jezyk);
                                $kategoria = $sqls->fetch_assoc();   
                                ?>                                    
                            
                                <p>
                                   <?php if ($w == '0') { ?>
                                    <label class="required" for="nazwa_<?php echo $w; ?>">Nazwa kategorii:</label>                                     
                                    <input type="text" name="nazwa_<?php echo $w; ?>" id="nazwa_<?php echo $w; ?>" size="65" value="<?php echo (isset($kategoria['categories_name']) ? Funkcje::formatujTekstInput($kategoria['categories_name']) : ''); ?>" id="nazwa_0" />
                                   <?php } else { ?>
                                    <label for="nazwa_<?php echo $w; ?>">Nazwa kategorii:</label>   
                                    <input type="text" name="nazwa_<?php echo $w; ?>" id="nazwa_<?php echo $w; ?>" size="65" value="<?php echo (isset($kategoria['categories_name']) ? Funkcje::formatujTekstInput($kategoria['categories_name']) : ''); ?>" />
                                   <?php } ?>
                                   
                                   <em class="TipIkona"><b>Nazwa wyświetlana m.in. w boxie kategorii</b></em>
                                   
                                </p> 
                                
                                <p>
                                   <label for="nazwa_menu_<?php echo $w; ?>">Nazwa kategorii w górnym menu:</label>
                                   <input type="text" name="nazwa_menu_<?php echo $w; ?>" id="nazwa_menu_<?php echo $w; ?>" size="65" value="<?php echo (isset($kategoria['categories_name_menu']) ? Funkcje::formatujTekstInput($kategoria['categories_name_menu']) : ''); ?>" /><em class="TipIkona"><b>Inna nazwa kategorii wyświetlana w górnym menu (jako główna pozycja menu) - pozostawienie pola pustego spowoduje wyświetlanie standardowej nazwy kategorii</b></em>
                                </p>
                                
                                <p>
                                   <label for="nazwa_info_<?php echo $w; ?>">Dodatkowa nazwa:</label>
                                   <input type="text" name="nazwa_info_<?php echo $w; ?>" id="nazwa_info_<?php echo $w; ?>" size="65" value="<?php echo (isset($kategoria['categories_name_info']) ? Funkcje::formatujTekstInput($kategoria['categories_name_info']) : ''); ?>" /><em class="TipIkona"><b>Dodatkowa nazwa kategorii wyświetlana w boxie z kategoriami</b></em>
                                </p>   

                                <p>
                                   <label for="opis_info_<?php echo $w; ?>">Dodatkowy opis tekstowy kategorii:</label>
                                   <textarea name="opis_info_<?php echo $w; ?>" id="opis_info_<?php echo $w; ?>" cols="65" style="width:60%;height:70px"><?php echo (isset($kategoria['categories_description_info']) ? Funkcje::formatujTekstInput($kategoria['categories_description_info']) : ''); ?></textarea><em class="TipIkona"><b>Dodatkowy opis wyświetlany pod nazwą podkategorii w listingu produktów</b></em>
                                </p>                                 
                                
                                <p>
                                  <label for="tytul_<?php echo $w; ?>">Meta Tagi - Tytuł:</label>
                                  <input type="text" name="tytul_<?php echo $w; ?>" id="tytul_<?php echo $w; ?>" size="120" onkeyup="licznik_znakow_meta(this,'iloscZnakowNazwa_<?php echo $w; ?>')" value="<?php echo (isset($kategoria['categories_meta_title_tag']) ? $kategoria['categories_meta_title_tag'] : ''); ?>" />                                  
                                </p> 
                                
                                <p class="LicznikMeta">
                                  <label></label>
                                  Ilość wpisanych znaków: <span class="iloscZnakow" id="iloscZnakowNazwa_<?php echo $w; ?>"><?php echo (isset($kategoria['categories_meta_title_tag']) ? strlen(mb_convert_encoding((string)$kategoria['categories_meta_title_tag'], 'ISO-8859-1', 'UTF-8')) : '0'); ?></span>
                                  zalecana maksymalna ilość: <span class="iloscZnakow" id="iloscZnakowNazwa_<?php echo $w; ?>Max"><?php echo DLUGOSC_META_NAZWA; ?></span>
                                </p>
                                
                                <p>
                                  <label for="opis_<?php echo $w; ?>">Meta Tagi - Opis:</label>
                                  <input type="text" name="opis_<?php echo $w; ?>" id="opis_<?php echo $w; ?>" size="120" onkeyup="licznik_znakow_meta(this,'iloscZnakowOpis_<?php echo $w; ?>')" value="<?php echo (isset($kategoria['categories_meta_desc_tag']) ? $kategoria['categories_meta_desc_tag'] : ''); ?>" />
                                </p>   
                                
                                <p class="LicznikMeta">
                                  <label></label>
                                  Ilość wpisanych znaków: <span class="iloscZnakow" id="iloscZnakowOpis_<?php echo $w; ?>"><?php echo (isset($kategoria['categories_meta_desc_tag']) ? strlen(mb_convert_encoding((string)$kategoria['categories_meta_desc_tag'], 'ISO-8859-1', 'UTF-8')) : ''); ?></span>
                                  zalecana maksymalna ilość: <span class="iloscZnakow" id="iloscZnakowOpis_<?php echo $w; ?>Max"><?php echo DLUGOSC_META_OPIS; ?></span>
                                </p>                                
                                
                                <p>
                                  <label for="slowa_<?php echo $w; ?>">Meta Tagi - Słowa kluczowe:</label>
                                  <input type="text" name="slowa_<?php echo $w; ?>" id="slowa_<?php echo $w; ?>" size="120" onkeyup="licznik_znakow_meta(this,'iloscZnakowSlowa_<?php echo $w; ?>')" value="<?php echo (isset($kategoria['categories_meta_keywords_tag']) ? $kategoria['categories_meta_keywords_tag'] : ''); ?>" />
                                </p> 

                                <p class="LicznikMeta">
                                  <label></label>
                                  Ilość wpisanych znaków: <span class="iloscZnakow" id="iloscZnakowSlowa_<?php echo $w; ?>"><?php echo (isset($kategoria['categories_meta_keywords_tag']) ? strlen(mb_convert_encoding((string)$kategoria['categories_meta_keywords_tag'], 'ISO-8859-1', 'UTF-8')) : '0'); ?></span>
                                  zalecana maksymalna ilość: <span class="iloscZnakow" id="iloscZnakowSlowa_<?php echo $w; ?>Max"><?php echo DLUGOSC_META_SLOWA; ?></span>
                                </p>   

                                <p>
                                  <label for="url_meta_<?php echo $w; ?>">Adres URL:</label>
                                  <input type="text" name="url_meta_<?php echo $w; ?>" id="url_meta_<?php echo $w; ?>" size="80" value="<?php echo (isset($kategoria['categories_seo_url']) ? $kategoria['categories_seo_url'] : ''); ?>" />
                                </p>     

                                <p>
                                  <label for="link_kanoniczny_<?php echo $w; ?>">Link kanoniczny:</label>
                                  <input type="text" name="link_kanoniczny_<?php echo $w; ?>" id="link_kanoniczny_<?php echo $w; ?>" size="80" value="<?php echo (isset($kategoria['categories_link_canonical']) ? $kategoria['categories_link_canonical'] : ''); ?>" />
                                  <em class="TipIkona"><b>Sam adres kategorii - np moja-kategoria-c-1.html - bez adresu sklepu z http:\\...</b></em>
                                </p>                                   
                                
                                <br />

                                <p>
                                  <label style="margin-bottom:8px;width:350px;">Opis kategorii (nad listingiem produktów):</label>
                                  <textarea cols="50" rows="20" id="edytor_<?php echo $w; ?>" name="edytor_<?php echo $w; ?>"><?php echo (isset($kategoria['categories_description']) ? $kategoria['categories_description'] : ''); ?></textarea>
                                </p> 
                                
                                <div class="maleInfo">Jeżeli od pewnego momentu tekst ma być ukryty należy w treści wstawić znacznik {__DALSZA_CZESC_UKRYTA}. Tekst znajdujący się po tym znaczniku będzie niewidoczny - z możliwością rozwinięcia / zwinięcia</div>
                                
                                <br />
                                
                                <p>
                                  <label style="margin-bottom:8px;width:350px;">Opis kategorii (pod listingiem produktów):</label>
                                  <textarea cols="50" rows="20" id="edytor_dol_<?php echo $w; ?>" name="edytor_dol_<?php echo $w; ?>"><?php echo (isset($kategoria['categories_description_bottom']) ? $kategoria['categories_description_bottom'] : ''); ?></textarea>
                                </p>  

                                <div class="maleInfo">Jeżeli od pewnego momentu tekst ma być ukryty należy w treści wstawić znacznik {__DALSZA_CZESC_UKRYTA}. Tekst znajdujący się po tym znaczniku będzie niewidoczny - z możliwością rozwinięcia / zwinięcia</div>
                                
                                <br />

                                <p>
                                  <label for="info_nazwa_<?php echo $w; ?>">Nazwa zakładki:</label>
                                  <input type="text" id="info_nazwa_<?php echo $w; ?>" name="info_nazwa_<?php echo $w; ?>" size="80" value="<?php echo (isset($kategoria['categories_info_name']) ? $kategoria['categories_info_name'] : ''); ?>" />
                                </p>  
                                                                
                                <div class="maleInfo">Informacja wyświetlana w formie zakładki na karcie produktu dla produktów przypisanych do kategorii</div>
                                
                                <p style="padding-top:5px">
                                  <textarea cols="50" rows="30" id="edytor_info_<?php echo $w; ?>" name="edytor_info_<?php echo $w; ?>"><?php echo (isset($kategoria['categories_info_text']) ? $kategoria['categories_info_text'] : ''); ?></textarea>                                  
                                </p>

                                <?php
                                $db->close_query($sqls);
                                unset($kategoria); 
                                ?>

                            </div>
                            <?php                    
                        }                    
                        ?>                      
                    </div>
                    
                    <script>
                    gold_tabs('0','edytor_',200,'normalny','edytor_info_','edytor_dol_');
                    </script> 
                    
                    <?php
                    $zapytanie_tmp = "select distinct urlf from location where categories_id = '".(int)$info['categories_id']."' and url_type = 'kategoria'";
                    $sqls = $db->open_query($zapytanie_tmp);
                    $seo = $sqls->fetch_assoc();
                    //
                    if ((int)$db->ile_rekordow($sqls) > 0) {
                        $kategoria_old_url = $seo['urlf'];
                    } else {
                        $kategoria_old_url = '';
                    }                    
                    ?>
                    
                    <p>
                      <label for="url_stary">Adres URL do kategorii w poprzednim sklepie:</label>
                      <input type="text" name="url_stary" id="url_stary" size="110" value="<?php echo $kategoria_old_url; ?>" /><em class="TipIkona"><b>Adres jest wykorzystywany tylko w przypadku jeżeli sklep funkcjonował wcześniej na innym oprogramowaniu</b></em>
                    </p>            

                    <div class="maleInfo" style="margin:0px 0px 10px 25px">
                      Przekierowanie ze starego adresu będzie działało jeżeli w sklepie będzie włączony moduł przekierowań w menu Narzędzia / Przekierowania URL
                    </div>  

                    <?php
                    //
                    $db->close_query($sqls);
                    unset($zapytanie_tmp, $seo);
                    //                    
                    ?>
                    
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
                  
                  <button type="button" class="przyciskNon" onclick="cofnij('kategorie','<?php echo Funkcje::Zwroc_Get(array('x','y')); ?>');">Powrót</button>
                  
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

}