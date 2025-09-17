<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
    
        $pola = array(
                array('manufacturers_image',$filtr->process($_POST['zdjecie'])),
                array('manufacturers_name',$filtr->process($_POST['nazwa'])),
                array('manufacturers_full_name',$filtr->process($_POST['nazwa_pelna'])),
                array('manufacturers_street',$filtr->process($_POST['producent_ulica'])),
                array('manufacturers_post_code',$filtr->process($_POST['producent_kod_pocztowy'])),
                array('manufacturers_city',$filtr->process($_POST['producent_miasto'])),
                array('manufacturers_country',$filtr->process($_POST['producent_kraj'])),
                array('manufacturers_email',$filtr->process($_POST['producent_email'])),
                array('manufacturers_phone',$filtr->process($_POST['producent_telefon'])),
                array('importer_name',$filtr->process($_POST['importer_nazwa'])),
                array('importer_street',$filtr->process($_POST['importer_ulica'])),
                array('importer_post_code',$filtr->process($_POST['importer_kod_pocztowy'])),
                array('importer_city',$filtr->process($_POST['importer_miasto'])),
                array('importer_country',$filtr->process($_POST['importer_kraj'])),
                array('importer_email',$filtr->process($_POST['importer_email'])),
                array('importer_phone',$filtr->process($_POST['importer_telefon'])),
                array('importer_unchanged',((isset($_POST['importer_producent'])) ? 0 : 1)));                
        
        $sql = $db->insert_query('manufacturers' , $pola);
        $id_dodanej_pozycji = $db->last_id_query();
        
        unset($pola);
        
        $ile_jezykow = Funkcje::TablicaJezykow();
        for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
            $pola = array(
                    array('manufacturers_id',$id_dodanej_pozycji),
                    array('languages_id',$ile_jezykow[$w]['id']),
                    array('manufacturers_url',$filtr->process($_POST['url_'.$w])),
                    array('manufacturers_meta_title_tag',$filtr->process($_POST['tytul_'.$w])),
                    array('manufacturers_meta_desc_tag',$filtr->process($_POST['opis_'.$w])),      
                    array('manufacturers_meta_keywords_tag',$filtr->process($_POST['slowa_'.$w])),
                    array('manufacturers_description',$filtr->process($_POST['edytor_'.$w])),
                    array('manufacturers_description_bottom',$filtr->process($_POST['edytor_dol_'.$w])),
                    array('manufacturers_info_name',$filtr->process($_POST['info_nazwa_'.$w])),
                    array('manufacturers_info_text',$filtr->process($_POST['edytor_info_'.$w])));          
            $sql = $db->insert_query('manufacturers_info' , $pola);
            unset($pola);
        }

        unset($ile_jezykow); 

        // -------------------------------- przekierowanie ze starego sklepu

        if ( !empty($_POST['url_stary']) ) {
             //
             $pola = array(
                     array('urlf',$filtr->process($_POST['url_stary'])),
                     array('urlt',Seo::link_SEO( $filtr->process($_POST['nazwa']), $id_producent, 'producent', '', false, false )),
                     array('url_type','producent'),
                     array('manufacturers_id',$id_producent));
             $sql = $db->insert_query('location' , $pola);
             //
             unset($pola);             
             //
        }         
        

        if (isset($id_dodanej_pozycji) && $id_dodanej_pozycji > 0) {
            Funkcje::PrzekierowanieURL('producenci.php?id_poz='.$id_dodanej_pozycji);
        } else {
            Funkcje::PrzekierowanieURL('producenci.php');
        }
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>

    <div id="naglowek_cont">Dodawanie pozycji</div>
    <div id="cont">

          <form action="producenci/producenci_dodaj.php" method="post" id="poForm" class="cmxform"> 
          
          <div class="poleForm">
            <div class="naglowek">Dodawanie danych</div>
            
            <div class="pozycja_edytowana">    
            
                <input type="hidden" name="akcja" value="zapisz" />

                <?php $ile_jezykow = Funkcje::TablicaJezykow(); ?>

                <script>
                $(document).ready(function() {
                  $("#poForm").validate({
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
                  $('#importer_producent_tak').click(function() {
                      if ($(this).prop('checked') ) {
                          $('.DaneImportera').stop().slideUp();                   
                      } else {
                          $('.DaneImportera').stop().slideDown(); 
                      }
                  });                    
                });
                </script>  

                <p>
                  <label class="required" for="nazwa">Nazwa producenta:</label>
                  <input type="text" name="nazwa" size="80" value="" id="nazwa" />
                </p>                  

                <p>
                  <label for="foto">Ścieżka zdjęcia:</label>           
                  <input type="text" name="zdjecie" size="95" value="" class="obrazek" ondblclick="openFileBrowser('foto','','<?php echo KATALOG_ZDJEC; ?>')" id="foto" autocomplete="off" />                 
                  <em class="TipIkona"><b>Kliknij dwukrotnie w pole obok żeby otworzyć okno przeglądarki zdjęć</b></em>
                  <span class="usun_zdjecie TipChmurka" data-foto="foto"><b>Usuń przypisane zdjęcie</b></span>
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
 
                <p style="padding:10px 10px 15px 25px; font-weight:bold; color:#3f5d6b">
                    Dane adresowe i kontaktowe producenta
                </p>
                
                <p>
                  <label for="nazwa_pelna">Pełna nazwa producenta (jeżeli jest inna niż podana powyżej):</label>
                  <input type="text" name="nazwa_pelna" id="nazwa_pelna" size="80" value="" id="nazwa_pelna" />
                </p>  
                
                <p>
                  <label for="producent_ulica">Ulica:</label>
                  <input type="text" name="producent_ulica" id="producent_ulica" size="40" value="" />
                </p>     

                <p>
                  <label for="producent_kod_pocztowy">Kod pocztowy:</label>
                  <input type="text" name="producent_kod_pocztowy" id="producent_kod_pocztowy" size="10" value="" />
                </p>  
                
                <p>
                  <label for="producent_miasto">Miasto:</label>
                  <input type="text" name="producent_miasto" id="producent_miasto" size="20" value="" />
                </p>  
                
                <p>
                  <label for="producent_kraj">Kraj:</label>  
                  <select name="producent_kraj" id="producent_kraj">
                  <?php foreach ( Funkcje::KrajeIso() as $nazwa => $kod ) { ?>
                      <option value="<?php echo $kod; ?>"><?php echo $nazwa; ?></option>                      
                  <?php } ?>
                  </select>
                </p>
                
                <p>
                  <label for="producent_email">Adres e-mail:</label>
                  <input type="text" name="producent_email" id="producent_email"size="40" value="" />
                </p> 
                
                <p>
                  <label for="producent_telefon">Numer telefonu:</label>
                  <input type="text" name="producent_telefon" id="producent_telefon" size="20" value="" />
                </p> 
                
                <div style="padding:15px 10px 15px 25px">
                    <input type="checkbox" name="importer_producent" id="importer_producent_tak" checked="checked" value="1" /> <label class="OpisFor" for="importer_producent_tak">dane importera / pełnomocnika takie same jak producenta</label>
                </div>       

                <div style="display:none" class="DaneImportera">
                
                    <p style="padding:5px 10px 15px 25px; font-weight:bold; color:#3f5d6b">
                        Dane adresowe i kontaktowe importera / pełnomocnika
                    </p>
                    
                    <p>
                      <label for="importer_nazwa">Nazwa:</label>
                      <input type="text" name="importer_nazwa" id="importer_nazwa" size="80" value="" />
                    </p>                          
                    
                    <p>
                      <label for="importer_ulica">Ulica:</label>
                      <input type="text" name="importer_ulica" id="importer_ulica" size="40" value="" />
                    </p>     

                    <p>
                      <label for="importer_kod_pocztowy">Kod pocztowy:</label>
                      <input type="text" name="importer_kod_pocztowy" id="importer_kod_pocztowy" size="10" value="" />
                    </p>  
                    
                    <p>
                      <label for="importer_miasto">Miasto:</label>
                      <input type="text" name="importer_miasto" id="importer_miasto" size="20" value="" />
                    </p>  
                    
                    <p>
                      <label for="importer_kraj">Kraj:</label>  
                      <select name="importer_kraj" id="importer_kraj">
                      <?php foreach ( Funkcje::KrajeIso() as $nazwa => $kod ) { ?>
                          <option value="<?php echo $kod; ?>"><?php echo $nazwa; ?></option>                     
                      <?php } ?>
                      </select>
                    </p>
                    
                    <p>
                      <label for="importer_email">Adres e-mail:</label>
                      <input type="text" name="importer_email" id="importer_email" size="40" value="" />
                    </p> 
                    
                    <p>
                      <label for="importer_telefon">Numer telefonu:</label>
                      <input type="text" name="importer_telefon" id="importer_telefon" size="20" value="" />
                    </p> 
                    
                </div>                

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
                              <label for="url_<?php echo $w; ?>">Adres URL do strony WWW:</label>
                              <input type="text" name="url_<?php echo $w; ?>" size="120" value="" id="url_<?php echo $w; ?>" />
                            </p>                         
                        
                            <p>
                              <label for="tytul_<?php echo $w; ?>">Meta Tagi - Tytuł:</label>
                              <input type="text" name="tytul_<?php echo $w; ?>" size="120" onkeyup="licznik_znakow_meta(this,'iloscZnakowNazwa_<?php echo $w; ?>')" value="" id="tytul_<?php echo $w; ?>" />
                            </p> 
                            
                            <p class="LicznikMeta">
                              <label></label>
                              Ilość wpisanych znaków: <span class="iloscZnakow" id="iloscZnakowNazwa_<?php echo $w; ?>">0</span>
                              zalecana maksymalna ilość: <span class="iloscZnakow" id="iloscZnakowNazwa_<?php echo $w; ?>Max"><?php echo DLUGOSC_META_NAZWA; ?></span>
                            </p>  
                            
                            <p>
                              <label for="opis_<?php echo $w; ?>">Meta Tagi - Opis:</label>
                              <input type="text" name="opis_<?php echo $w; ?>" size="120" onkeyup="licznik_znakow_meta(this,'iloscZnakowOpis_<?php echo $w; ?>')" value="" id="opis_<?php echo $w; ?>" />
                            </p>   
                            
                            <p class="LicznikMeta">
                              <label></label>
                              Ilość wpisanych znaków: <span class="iloscZnakow" id="iloscZnakowOpis_<?php echo $w; ?>">0</span>
                              zalecana maksymalna ilość: <span class="iloscZnakow" id="iloscZnakowOpis_<?php echo $w; ?>Max"><?php echo DLUGOSC_META_OPIS; ?></span>
                            </p>                            
                            
                            <p>
                              <label for="slowa_<?php echo $w; ?>">Meta Tagi - Słowa kluczowe:</label>
                              <input type="text" name="slowa_<?php echo $w; ?>" size="120" onkeyup="licznik_znakow_meta(this,'iloscZnakowSlowa_<?php echo $w; ?>')" value="" id="slowa_<?php echo $w; ?>" />
                            </p>   

                            <p class="LicznikMeta">
                              <label></label>
                              Ilość wpisanych znaków: <span class="iloscZnakow" id="iloscZnakowSlowa_<?php echo $w; ?>">0</span>
                              zalecana maksymalna ilość: <span class="iloscZnakow" id="iloscZnakowSlowa_<?php echo $w; ?>Max"><?php echo DLUGOSC_META_SLOWA; ?></span>
                            </p>                              
                        
                            <br />

                            <p>
                              <label style="margin-bottom:8px;width:350px;">Opis producenta (nad listingiem produktów):</label>
                              <textarea cols="50" rows="20" id="edytor_<?php echo $w; ?>" name="edytor_<?php echo $w; ?>"></textarea>
                            </p> 
                        
                            <div class="maleInfo">Jeżeli od pewnego momentu tekst ma być ukryty należy w treści wstawić znacznik {__DALSZA_CZESC_UKRYTA}. Tekst znajdujący się po tym znaczniku będzie niewidoczny - z możliwością rozwinięcia / zwinięcia</div>

                            <br />                            
                            
                            <p>
                              <label style="margin-bottom:8px;width:350px;">Opis producenta (pod listingiem produktów):</label>
                              <textarea cols="50" rows="20" id="edytor_dol_<?php echo $w; ?>" name="edytor_dol_<?php echo $w; ?>"></textarea>
                            </p>                              
                            
                            <div class="maleInfo">Jeżeli od pewnego momentu tekst ma być ukryty należy w treści wstawić znacznik {__DALSZA_CZESC_UKRYTA}. Tekst znajdujący się po tym znaczniku będzie niewidoczny - z możliwością rozwinięcia / zwinięcia</div>

                            <br />                            

                            <p>
                              <label for="info_nazwa_<?php echo $w; ?>">Nazwa zakładki:</label>
                              <input type="text" name="info_nazwa_<?php echo $w; ?>" size="80" value="" id="info_nazwa_<?php echo $w; ?>" />
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
                  <label for="url_stary">Adres URL do producenta w poprzednim sklepie:</label>
                  <input type="text" name="url_stary" id="url_stary" size="110" value="" /><em class="TipIkona"><b>Adres jest wykorzystywany tylko w przypadku jeżeli sklep funkcjonował wcześniej na innym oprogramowaniu</b></em>
                </p>            

                <div class="maleInfo" style="margin:0px 0px 10px 25px">
                  Przekierowanie ze starego adresu będzie działało jeżeli w sklepie będzie włączony moduł przekierowań w menu Narzędzia / Przekierowania URL
                </div>                  
                
            </div>
            
            <div class="przyciski_dolne">
              <input type="submit" class="przyciskNon" value="Zapisz dane" />
              <button type="button" class="przyciskNon" onclick="cofnij('producenci','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>');">Powrót</button>    
            </div>            
            
          </div>

          </form>

    </div>
    
    <?php
    include('stopka.inc.php');    
    
} ?>