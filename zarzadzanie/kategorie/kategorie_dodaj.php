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
                array('categories_icon',$filtr->process($_POST['ikona'])),
                array('parent_id',(int)$_POST['id_kat']),
                array('sort_order',(int)$_POST['sort']),
                array('categories_status','1'));
                
        if ( isset($_POST['color_status']) ) {
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
        
        $sql = $db->insert_query('categories' , $pola);
        $id_dodanej_pozycji = $db->last_id_query();
        
        unset($pola);
        
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
                    array('categories_id',(int)$id_dodanej_pozycji),
                    array('language_id',(int)$ile_jezykow[$w]['id']),
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
                    array('categories_info_text',$filtr->process($_POST['edytor_info_'.$w])));                    
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
        
        if ( !empty($_POST['url_stary']) ) {
             //
             $pola = array(
                     array('urlf',$filtr->process($_POST['url_stary'])),
                     array('urlt',Seo::link_SEO( $nazwa_domyslna, $id_dodanej_pozycji, 'kategoria', '', false, false )),
                     array('url_type','kategoria'),
                     array('categories_id',$id_dodanej_pozycji));
             $sql = $db->insert_query('location' , $pola);
             //
             unset($pola);             
             //
        }          

        if (isset($id_dodanej_pozycji) && $id_dodanej_pozycji > 0) {
          
            if ( isset($_POST['powrot']) && (int)$_POST['powrot'] == 1 ) {
                //            
                Funkcje::PrzekierowanieURL('kategorie_edytuj.php?id_poz=' . $id_dodanej_pozycji);
                //
              } else {
                //
                Funkcje::PrzekierowanieURL('kategorie.php?id_poz='.$id_dodanej_pozycji);
                //
            }            

        } else {
            Funkcje::PrzekierowanieURL('kategorie.php');
        }
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>

    <div id="naglowek_cont">Dodawanie pozycji</div>
    <div id="cont">

          <form action="kategorie/kategorie_dodaj.php" method="post" id="poForm" class="cmxform"> 
          
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
                  <label>Kategoria nadrzędna:</label>
                </p> 
                
                <div id="drzewo" style="width:95%; max-width:650px">
                    <?php
                    //
                    echo '<table class="pkc">
                          <tr>
                            <td class="lfp" colspan="2"><input type="radio" value="0" name="id_kat" id="id_kat" checked="checked" /><label class="OpisFor" for="id_kat">-- brak kategorii nadrzędnej --</label></td>
                          </tr>';
                    //
                    $tablica_kat = Kategorie::DrzewoKategorii('0', '', '', '', false, true);
                    for ($w = 0, $c = count($tablica_kat); $w < $c; $w++) {
                        $podkategorie = false;
                        if ($tablica_kat[$w]['podkategorie'] == 'true') { $podkategorie = true; }
                        //
                        echo '<tr>
                                <td class="lfp"><input type="radio" value="'.$tablica_kat[$w]['id'].'" name="id_kat" id="id_kat_'.$tablica_kat[$w]['id'].'" /><label class="OpisFor" for="id_kat_'.$tablica_kat[$w]['id'].'"> '.$tablica_kat[$w]['text'].'</label></td>
                                <td class="rgp" '.(($podkategorie) ? 'id="img_'.$tablica_kat[$w]['id'].'"' : '').'>'.(($podkategorie) ? '<img src="obrazki/rozwin.png" alt="Rozwiń" onclick="podkat(\''.$tablica_kat[$w]['id'].'\',\'\',\'radio\')" />' : '').'</td>
                              </tr>
                              '.(($podkategorie) ? '<tr><td colspan="2"><div id="p_'.$tablica_kat[$w]['id'].'"></div></td></tr>' : '').'';
                    }
                    echo '</table>';
                    unset($tablica_kat,$podkategorie);
                    ?> 
                </div>

                <p>
                  <label for="foto">Ścieżka zdjęcia:</label>           
                  <input type="text" name="zdjecie" size="95" value="" class="obrazek" ondblclick="openFileBrowser('foto','','<?php echo KATALOG_ZDJEC; ?>')" id="foto" autocomplete="off" />  
                  <em class="TipIkona"><b>Kliknij dwukrotnie w pole obok żeby otworzyć okno przeglądarki zdjęć</b></em>
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
                  <input type="text" name="ikona" size="95" value="" class="obrazek" ondblclick="openFileBrowser('ikona','','<?php echo KATALOG_ZDJEC; ?>')" id="ikona"  />                 
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
                  <label for="sort">Kolejność wyświetlania:</label>
                  <input type="text" name="sort" size="5" value="" id="sort" />
                </p>      

                <p>
                  <label for="kolor">Kolor nazwy kategorii:</label>
                  <input name="kolor" id="kolor" class="color" style="-moz-box-shadow:none" value="" size="8" /> 
                  <em class="TipIkona"><b>Określa kolor wyświetlania nazwy kategorii w boxie kategorii</b></em> &nbsp; 
                  <input type="checkbox" name="color_status" id="kolor_status" value="1" /><label class="OpisFor" for="kolor_status">wyświetlaj nazwę tej kategorii w kolorze<em class="TipIkona"><b>Czy nazwa kategorii ma być wyświetlana w kolorze ?</b></em></label>                  
                </p>    

                <p>
                  <label for="kolor_tla">Kolor tła nazwy kategorii:</label>
                  <input name="kolor_tla" id="kolor_tla" class="color" style="-moz-box-shadow:none" value="" size="8" /> 
                  <em class="TipIkona"><b>Określa kolor tła pod nazwą kategorii w boxie kategorii</b></em> &nbsp; 
                  <input type="checkbox" name="kolor_status_tla" id="kolor_status_tla" value="1" /> <label class="OpisFor" for="kolor_status_tla">wyświetlaj tło tej kategorii w kolorze<em class="TipIkona"><b>Czy tło nazwy kategorii ma być wyświetlane w kolorze ?</b></em></label>
                </p>     

                <?php if ( Wyglad::TypSzablonu() != true ) { echo '<div style="display:none">'; } ?>
                
                <p>
                  <label>Czy wyświetlać banner z nazwą kategorii nad listingiem produktów kategorii ?</label>
                  <input type="radio" value="tak" name="banner_kategoria" id="banner_kategoria_tak" onclick="$('#GrafikaKategorie').stop().slideDown()" /><label class="OpisFor" for="banner_kategoria_tak">tak</label>
                  <input type="radio" value="nie" name="banner_kategoria" id="banner_kategoria_nie" onclick="$('#GrafikaKategorie').stop().slideUp()" checked="checked" /><label class="OpisFor" for="banner_kategoria_nie">nie</label>
                </p>
                
                <div id="GrafikaKategorie" style="display:none">

                    <p>
                      <label for="banner_kategoria_grafika">Banner kategorii (będzie wyświetlany na 100% szerokości ekranu):</label>           
                      <input type="text" name="banner_kategoria_grafika" size="95" value="" class="obrazek" ondblclick="openFileBrowser('banner_kategoria_grafika','','<?php echo KATALOG_ZDJEC; ?>')" id="banner_kategoria_grafika" />   
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
                    </div>   

                    <p>
                      <label for="kolor_banner_czcionka">Kolor czcionki nazwy kategorii:</label>
                      <input name="kolor_banner_czcionka" class="color" style="-moz-box-shadow:none" value="" size="8" id="kolor_banner_czcionka" /> 
                      <em class="TipIkona"><b>Określa kolor czcionki wyświetlanej na bannerze kategorii</b></em>
                    </p> 

                    <p>
                      <label for="rozmiar_banner_czcionka">Rozmiar czcionki:</label>             
                      <?php
                      $tablica = array();
                      for ( $t = 0; $t < 40; $t++ ) {
                            $tablica[] = array('id' => (100 + ($t * 10)), 'text' => (100 + ($t * 10)) . '%');
                      }
                      echo Funkcje::RozwijaneMenu('rozmiar_banner_czcionka', $tablica, '', ' id="rozmiar_banner_czcionka"');
                      unset($tablica);
                      ?>
                    </p>           

                    <p>
                      <label for="wyrownanie_tekstu_banner_czcionka">Wyrównanie tekstu w poziomie:</label>             
                      <?php
                      $tablica = array(array('id' => 'left', 'text' => 'do lewej strony'),
                                       array('id' => 'center', 'text' => 'wyśrodkowane'),
                                       array('id' => 'right', 'text' => 'do prawej strony'));
                                        
                      echo Funkcje::RozwijaneMenu('wyrownanie_tekstu_banner_czcionka', $tablica, '', ' id="wyrownanie_tekstu_banner_czcionka"');
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
                        
                            <p>
                               <?php if ($w == '0') { ?>
                                <label class="required" for="nazwa_0">Nazwa kategorii:</label>
                                <input type="text" name="nazwa_<?php echo $w; ?>" size="65" value="" id="nazwa_0" />
                               <?php } else { ?>
                                <label for="nazwa_<?php echo $w; ?>">Nazwa kategorii:</label>   
                                <input type="text" name="nazwa_<?php echo $w; ?>" size="65" value="" id="nazwa_<?php echo $w; ?>" />
                               <?php } ?>
                               
                               <em class="TipIkona"><b>Nazwa wyświetlana m.in. w boxie kategorii</b></em>
                               
                            </p> 
                            
                            <p>
                               <label for="nazwa_menu_<?php echo $w; ?>">Nazwa kategorii w górnym menu:</label>
                               <input type="text" name="nazwa_menu_<?php echo $w; ?>" id="nazwa_menu_<?php echo $w; ?>" size="65" value="" /><em class="TipIkona"><b>Inna nazwa kategorii wyświetlana w górnym menu (jako główna pozycja menu) - pozostawienie pola pustego spowoduje wyświetlanie standardowej nazwy kategorii</b></em>
                            </p>                            
                            
                            <p>
                               <label for="nazwa_info_<?php echo $w; ?>">Dodatkowa nazwa:</label>
                               <input type="text" name="nazwa_info_<?php echo $w; ?>" id="nazwa_info_<?php echo $w; ?>" size="65" value="" /><em class="TipIkona"><b>Dodatkowa nazwa kategorii wyświetlana w boxie z kategoriami</b></em>
                            </p>                       

                            <p>
                               <label for="opis_info_<?php echo $w; ?>">Dodatkowy opis tekstowy kategorii:</label>
                               <textarea name="opis_info_<?php echo $w; ?>" id="opis_info_<?php echo $w; ?>" cols="65" style="width:60%;height:70px"></textarea><em class="TipIkona"><b>Dodatkowy opis wyświetlany pod nazwą podkategorii w listingu produktów</b></em>
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

                            <p>
                              <label for="url_meta_<?php echo $w; ?>">Adres URL:</label>
                              <input type="text" name="url_meta_<?php echo $w; ?>" id="url_meta_<?php echo $w; ?>" size="80" value="" />
                            </p>                             
                            
                            <p>
                              <label for="link_kanoniczny_<?php echo $w; ?>">Link kanoniczny:</label>
                              <input type="text" name="link_kanoniczny_<?php echo $w; ?>" id="link_kanoniczny_<?php echo $w; ?>" size="80" value="" />
                              <em class="TipIkona"><b>Sam adres kategorii - np moja-kategoria-c-1.html - bez adresu sklepu z http:\\...</b></em>
                            </p>                               
                            
                            <br />

                            <p>
                              <label style="margin-bottom:8px;width:350px;">Opis kategorii (nad listingiem produktów):</label>
                              <textarea cols="50" rows="20" id="edytor_<?php echo $w; ?>" name="edytor_<?php echo $w; ?>"></textarea>
                            </p> 
                            
                            <div class="maleInfo">Jeżeli od pewnego momentu tekst ma być ukryty należy w treści wstawić znacznik {__DALSZA_CZESC_UKRYTA}. Tekst znajdujący się po tym znaczniku będzie niewidoczny - z możliwością rozwinięcia / zwinięcia</div>

                            <br /> 
                            
                            <p>
                              <label style="margin-bottom:8px;width:350px;">Opis kategorii (pod listingiem produktów):</label>
                              <textarea cols="50" rows="20" id="edytor_dol_<?php echo $w; ?>" name="edytor_dol_<?php echo $w; ?>"></textarea>
                            </p>                                
                            
                            <div class="maleInfo">Jeżeli od pewnego momentu tekst ma być ukryty należy w treści wstawić znacznik {__DALSZA_CZESC_UKRYTA}. Tekst znajdujący się po tym znaczniku będzie niewidoczny - z możliwością rozwinięcia / zwinięcia</div>

                            <br /> 
                            
                            <p>
                              <label for="info_nazwa_<?php echo $w; ?>">Nazwa zakładki:</label>
                              <input type="text" name="info_nazwa_<?php echo $w; ?>" id="info_nazwa_<?php echo $w; ?>" size="80" value="" />
                            </p>  
                            
                            <div class="maleInfo">Informacja producenta wyświetlana w formie zakładki na karcie produktu dla produktów przypisanych do producenta</div>
                            
                            <p style="padding-top:5px">
                              <textarea cols="50" rows="30" id="edytor_info_<?php echo $w; ?>" name="edytor_info_<?php echo $w; ?>"></textarea>                                  
                            </p>                          

                        </div>
                        <?php                    
                    }                    
                    ?>                      
                </div>
                
                <script>
                gold_tabs('0','edytor_',200,'normalny','edytor_info_','edytor_dol_');
                </script> 
                
                <p>
                  <label for="url_stary">Adres URL do kategorii w poprzednim sklepie:</label>
                  <input type="text" name="url_stary" id="url_stary" size="110" value="" /><em class="TipIkona"><b>Adres jest wykorzystywany tylko w przypadku jeżeli sklep funkcjonował wcześniej na innym oprogramowaniu</b></em>
                </p>            

                <div class="maleInfo" style="margin:0px 0px 10px 25px">
                  Przekierowanie ze starego adresu będzie działało jeżeli w sklepie będzie włączony moduł przekierowań w menu Narzędzia / Przekierowania URL
                </div>               
                
            </div>
            
            <div class="przyciski_dolne">
              <input type="hidden" name="powrot" id="powrot" value="0" />
              <input type="submit" class="przyciskNon" value="Zapisz dane" />
              <input type="submit" class="przyciskNon" value="Zapisz dane i pozostań w edycji" onclick="$('#powrot').val(1)" />   
              <button type="button" class="przyciskNon" onclick="cofnij('kategorie','<?php echo Funkcje::Zwroc_Get(array('x','y')); ?>');">Powrót</button>       
            </div>            
            
          </div>

          </form>

    </div>
    
    <?php
    include('stopka.inc.php');    
    
} ?>
