<?php if ($prot->wyswietlStrone) { ?>

<div id="zakl_id_6" style="display:none;">

    <div class="info_tab" style="padding-top:0px">
    <?php
    $licznik_zakladek = $tab_6;
    $liczba = $licznik_zakladek;
    for ($w = 0; $w < $jezyk_szt; $w++) {
        echo '<span id="link_'.$licznik_zakladek.'" class="a_href_info_tab" onclick="gold_tabs(\''.$licznik_zakladek.'\')">'.$ile_jezykow[$w]['text'].'</span>';
        $licznik_zakladek++;
    }                      
    ?>                   
    </div>
    
    <div style="clear:both"></div>
    
    <div class="info_tab_content">
    
        <?php
        for ($w = 0; $w < $jezyk_szt; $w++) {
        
            if ($id_produktu > 0) {  
                // pobieranie danych jezykowych
                $zapytanie_tmp = "select distinct * from products_description where products_id = '".$id_produktu."' and language_id = '" .$ile_jezykow[$w]['id']."'";
                $sqls = $db->open_query($zapytanie_tmp);
                $opis = $sqls->fetch_assoc();
                //
                $products_meta_title_tag = (isset($opis['products_meta_title_tag']) ? $opis['products_meta_title_tag'] : '');
                $products_meta_desc_tag = (isset($opis['products_meta_desc_tag']) ? $opis['products_meta_desc_tag'] : '' );
                $products_meta_keywords_tag = (isset($opis['products_meta_keywords_tag']) ? $opis['products_meta_keywords_tag'] : '' );
                $products_seo_url = (isset($opis['products_seo_url']) ? $opis['products_seo_url'] : '' );
                $products_canonical = (isset($opis['products_link_canonical'] ) ? $opis['products_link_canonical'] : '');
                $products_search = (isset($opis['products_search_tag']) ? $opis['products_search_tag'] : '');
                //
                $og_title = (isset($opis['products_og_title']) ? $opis['products_og_title'] : '');
                $og_description = (isset($opis['products_og_description']) ? $opis['products_og_description'] : '');
                //
              } else {
                //
                $products_meta_title_tag = '';
                $products_meta_desc_tag = '';
                $products_meta_keywords_tag = '';
                $products_seo_url = '';
                $products_canonical = '';
                $products_search = '';
                //
                $og_title = '';
                $og_description = '';
                //
            }
            ?>  
            
            <div id="info_tab_id_<?php echo $w + $liczba; ?>" style="display:none;">

                <p>
                  <label for="tytul_meta_<?php echo $w; ?>">Meta Tagi - Tytuł:</label>
                  <textarea name="tytul_meta_<?php echo $w; ?>" id="tytul_meta_<?php echo $w; ?>" onkeyup="licznik_znakow_meta(this,'iloscZnakowNazwa_<?php echo $w; ?>')" rows="4" cols="70"><?php echo $products_meta_title_tag; ?></textarea>
                </p> 
                
                <p class="LicznikMeta">
                  <label></label>
                  Ilość wpisanych znaków: <span class="iloscZnakow" id="iloscZnakowNazwa_<?php echo $w; ?>"><?php echo strlen(mb_convert_encoding((string)$products_meta_title_tag, 'ISO-8859-1', 'UTF-8')); ?></span>
                  zalecana maksymalna ilość: <span class="iloscZnakow" id="iloscZnakowNazwa_<?php echo $w; ?>Max"><?php echo DLUGOSC_META_NAZWA; ?></span>
                </p>                
                
                <p>
                  <label for="opis_meta_<?php echo $w; ?>">Meta Tagi - Opis:</label>
                  <textarea name="opis_meta_<?php echo $w; ?>" id="opis_meta_<?php echo $w; ?>" onkeyup="licznik_znakow_meta(this,'iloscZnakowOpis_<?php echo $w; ?>')" rows="4" cols="70"><?php echo $products_meta_desc_tag; ?></textarea>
                </p>  

                <p class="LicznikMeta">
                  <label></label>
                  Ilość wpisanych znaków: <span class="iloscZnakow" id="iloscZnakowOpis_<?php echo $w; ?>"><?php echo strlen(mb_convert_encoding((string)$products_meta_desc_tag, 'ISO-8859-1', 'UTF-8')); ?></span>
                  zalecana maksymalna ilość: <span class="iloscZnakow" id="iloscZnakowOpis_<?php echo $w; ?>Max"><?php echo DLUGOSC_META_OPIS; ?></span>
                </p>                 
                
                <p>
                  <label for="slowa_meta_<?php echo $w; ?>">Meta Tagi - Słowa kluczowe:</label>
                  <textarea name="slowa_meta_<?php echo $w; ?>" id="slowa_meta_<?php echo $w; ?>" onkeyup="licznik_znakow_meta(this,'iloscZnakowSlowa_<?php echo $w; ?>')" rows="4" cols="70"><?php echo $products_meta_keywords_tag; ?></textarea>
                </p>    
                
                <p class="LicznikMeta">
                  <label></label>
                  Ilość wpisanych znaków: <span class="iloscZnakow" id="iloscZnakowSlowa_<?php echo $w; ?>"><?php echo strlen(mb_convert_encoding((string)$products_meta_keywords_tag, 'ISO-8859-1', 'UTF-8')); ?></span>
                  zalecana maksymalna ilość: <span class="iloscZnakow" id="iloscZnakowSlowa_<?php echo $w; ?>Max"><?php echo DLUGOSC_META_SLOWA; ?></span>
                </p> 
                
                <hr style="color:#82b4cd;border-top:1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;margin:20px 10px 20px 10px" />

                <p>
                  <label for="og_title_<?php echo $w; ?>">Tag Open Graph - tytuł strony: <em class="TipIkona"><b>Jeżeli tytuł nie zostanie wypełniony będzie wyświetlana wartość z pola Meta Tagi - TYTUŁ</b></em></label>
                  <input type="text" name="og_title_<?php echo $w; ?>" id="og_title_<?php echo $w; ?>" size="80" value="<?php echo $og_title; ?>" />
                </p>  

                <p>
                  <label for="og_description_<?php echo $w; ?>">Tag Open Graph - krótki opis strony: <em class="TipIkona"><b>Jeżeli opis nie zostanie wypełniony będzie wyświetlana wartość z pola Meta Tagi - OPIS</b></em></label>
                  <textarea name="og_description_<?php echo $w; ?>" id="og_description_<?php echo $w; ?>" rows="2" cols="70"><?php echo $og_description; ?></textarea>
                </p>                
                
                <hr style="color:#82b4cd;border-top:1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;margin:20px 10px 20px 10px" />
                
                <p>
                  <label for="url_meta_<?php echo $w; ?>">Adres URL:</label>
                  <input type="text" name="url_meta_<?php echo $w; ?>" id="url_meta_<?php echo $w; ?>" size="80" value="<?php echo $products_seo_url; ?>" />
                </p> 
                
                <hr style="color:#82b4cd;border-top:1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;margin:20px 10px 20px 10px" />

                <p>
                  <label for="link_kanoniczny_<?php echo $w; ?>">Link kanoniczny:</label>
                  <input type="text" name="link_kanoniczny_<?php echo $w; ?>" id="link_kanoniczny_<?php echo $w; ?>" size="80" value="<?php echo $products_canonical; ?>" />
                  <em class="TipIkona"><b>Sam adres <?php echo (($zestaw) ? 'zestawu' : 'produktu'); ?> - np moj-zestaw-p-1.html - bez adresu sklepu z http:\\...</b></em>
                </p>   
                
                <hr style="color:#82b4cd;border-top:1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;margin:20px 10px 20px 10px" />

                <p>
                  <label for="slowa_szukaj_<?php echo $w; ?>">Tagi: </label>
                  <textarea name="slowa_szukaj_<?php echo $w; ?>" id="slowa_szukaj_<?php echo $w; ?>" rows="5" cols="70"><?php echo $products_search; ?></textarea>
                  <em class="TipIkona"><b>Słowa (frazy) należy rozdzielić przecinkami</b></em>
                </p>                 
                
            </div>
            <?php

            if ($id_produktu > 0) {  
                $db->close_query($sqls); 
                unset($zapytanie_tmp, $opis);
            }
            unset($products_meta_title_tag, $products_meta_desc_tag, $products_meta_keywords_tag, $products_seo_url, $products_canonical, $products_search, $og_title, $og_description);        
                        
        }          
        ?>
        
    </div>
    
    <?php
    // adres innego sklepu
    $products_old_url = '';
    $products_przekierowanie = '';
    $typ_przekierowania = '404';
    
    if ($id_produktu > 0) {
        //
        $zapytanie_tmp = "select distinct urlf, urlt, forwarding, type from location where products_id = '".$id_produktu."' and url_type = 'produkt'";
        $sqls = $db->open_query($zapytanie_tmp);        
        //
        if ($db->ile_rekordow($sqls) > 0) {
            //
            while ($seo = $sqls->fetch_assoc()) {
                  //
                  if ( (int)$seo['forwarding'] == 0 ) {
                       //
                       $products_old_url = $seo['urlf'];
                       //
                  }
                  //
                  if ( (int)$seo['forwarding'] == 1 ) {
                       //
                       $products_przekierowanie = $seo['urlt'];
                       $typ_przekierowania = $seo['type'];
                       //
                  }
                  //
            }
            //
        }
        //
    }
    ?>
    
    <p>
      <label for="url_stary">Adres URL do <?php echo (($zestaw) ? 'zestawu' : 'produktu'); ?> w poprzednim sklepie:</label>
      <input type="text" name="url_stary" id="url_stary" size="110" value="<?php echo $products_old_url; ?>" /><em class="TipIkona"><b>Adres jest wykorzystywany tylko w przypadku jeżeli sklep funkcjonował wcześniej na innym oprogramowaniu</b></em>
    </p>    

    <div class="maleInfo" style="margin:0px 0px 0px 25px">
      Przekierowanie ze starego adresu będzie działało jeżeli w sklepie będzie włączony moduł przekierowań w menu Narzędzia / Przekierowania URL
    </div>
    
    <script>  
    function adres_zmien(wartosc) {
        if ( wartosc == '404' ) {
             $('#inna_strona').stop().slideUp();
        } else {
             $('#inna_strona').stop().slideDown();
        }
    } 
    </script>  

    <p>
      <label for="url_stary">Ustawienia <?php echo (($zestaw) ? 'zestawu' : 'produktu'); ?> jeżeli nie jest aktywny:</label>
      <select name="url_przekierowanie" onchange="adres_zmien(this.value)">
          <option value="404" <?php echo (($typ_przekierowania == '404') ? 'selected="selected"' : ''); ?>>strona błędu - nie odnaleziono szukanego produktu (błąd 404)</option>
          <option value="301" <?php echo (($typ_przekierowania == '301') ? 'selected="selected"' : ''); ?>>przekierowanie na inną stronę (przekierowanie 301)</option>
          <option value="302" <?php echo (($typ_przekierowania == '302') ? 'selected="selected"' : ''); ?>>przekierowanie na inną stronę (przekierowanie 302)</option>
      </select>
    </p>       

    <div id="inna_strona" <?php echo (($typ_przekierowania == '404') ? 'style="display:none"' : ''); ?>>
    
      <p>
        <label for="adres_przekierowania">Adres URL do jakiej ma nastąpić przekierowanie:</label>
        <input type="text" name="adres_przekierowania" id="adres_przekierowania" size="110" value="<?php echo $products_przekierowanie; ?>" />
      </p>

      <div class="maleInfo" style="margin:0px 0px 0px 25px">
        Należy podać sam adres linku - bez http:// np moj-adres-przekierowania-p-30.html <br />
        Przekierowanie będzie działało jeżeli w sklepie będzie włączony moduł przekierowań w menu Narzędzia / Przekierowania URL
      </div>      
      
    </div>
    
    <?php
    if ($id_produktu > 0) {
        //
        $db->close_query($sqls);
        unset($zapytanie_tmp);
        //
    }
    ?>     

</div>  

<?php } ?>