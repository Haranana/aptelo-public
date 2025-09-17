<?php
if ( isset($toks) ) {
?>

    <?php if ( PRODUKTY_SZCZEGOLY_ZAMOWIENIA == 'dodatkowa zakładka' ) { ?>
    
    <div id="zakl_id_2" style="display:none;" class="pozycja_edytowana">
    
    <?php } else { ?>
    
    <div class="ZakupioneProdukty">Zakupione produkty</div><div>
    
    <?php } ?>

      <?php
      if ( isset($zamowienie->produkty) && count($zamowienie->produkty) > 0) {
      ?>

      <div style="margin:0px 0px 5px <?php echo (( PRODUKTY_SZCZEGOLY_ZAMOWIENIA == 'dodatkowa zakładka' ) ? '5px' : '0px'); ?>" id="fraza">
          <div>Wyszukaj produkt: <input type="text" size="15" value="<?php echo ((isset($_GET['produkt']) && trim((string)$_GET['produkt']) != '') ? $_GET['produkt'] : ''); ?>" id="szukany_zamowienie" /><em class="TipIkona"><b>Wpisz nazwę produktu, kod producenta lub nr katalogowy</b></em></div> <span id="SzukajProduktuZamowienie"></span>
          
          <?php if ( isset($_GET['produkt']) ) { ?>
          <div style="margin:6px 0px 0px 20px; float:left"><a href="sprzedaz/zamowienia_szczegoly.php?id_poz=<?php echo (int)$_GET['id_poz']; ?><?php echo ((PRODUKTY_SZCZEGOLY_ZAMOWIENIA == 'dodatkowa zakładka') ? '&zakladka=2' : ''); ?>"><img src="obrazki/reset_szukaj.png" alt="Anuluj wyszukiwanie" /></a></div>
          <?php } ?>
          
      </div>       

      <div style="padding:0px 0px 10px 8px">
          <input type="radio" name="vat_od" value="1" id="vat_od_brutto" checked="checked" /><label class="OpisFor" for="vat_od_brutto">zmiana VAT od ceny brutto</label>
          <input type="radio" name="vat_od" value="0" id="vat_od_netto" /><label class="OpisFor" for="vat_od_netto">zmiana VAT od ceny netto</label>              
      </div>
        
      <?php
      $Zestawy = array();
      //
      $sql_zestawy = $db->open_query("SELECT * FROM orders_products_set WHERE orders_id = '" . $zamowienie->info['id_zamowienia'] . "'");
      while ($info_zestawy = $sql_zestawy->fetch_assoc()) {
          //
          $Zestawy[] = $info_zestawy;
          //
      }
      $db->close_query($sql_zestawy);
      //
      if ( count($Zestawy) > 0 ) {
           //
           echo '<div class="ObramowanieTabeli" style="margin-bottom:20px"><table class="listing_tbl" id="InfoTabelaProdukty">
                    <tr class="div_naglowek">
                        <td>Nazwa zestawu</td>
                        <td>Ilość kupionych zestawów</td>
                    </tr>';
           
           foreach ($Zestawy as $Zestaw) {
                //
                echo '<tr class="pozycja_off">
                          <td style="text-align:left"><a class="LinkProduktu" target="_blank" href="' . Seo::link_SEO( $Zestaw['products_set_name'], $Zestaw['products_set_id'], 'produkt', '', false ) . '">' . $Zestaw['products_set_name'] . '</a></td>
                          <td>' . (int)$Zestaw['products_set_quantity'] . '</td>
                      </tr>';
                //
           }
           //
           echo '</table></div>';
           //
      }
      //
      ?>
            
      <div class="ObramowanieTabeli">
      
        <script>
        $(document).ready(function(){
        
            $('.zmzoom_produkt').hover(function(event) {
               PodgladIn($(this),event,'produkt');
            }, function() {
               PodgladOut($(this),'produkt');
            });
            
            if (document.cookie != "") { 
                var cookies=document.cookie.split("; "); 
                for ( i = 0; i < cookies.length; i++ ) { 
                    var nazwaCookie=cookies[i].split("=")[0]; 
                    var wartoscCookie=cookies[i].split("=")[1];
                    if ( nazwaCookie === 'scroll' ) {
                         $('body').scrollTo( parseInt( unescape(wartoscCookie) ) );  
                    }
                }
            }       
            createCookie('scroll', "", -1);
            
            $('.VatProduktu').change(function() {
                //
                $('#ekr_preloader').css('display','block');
                //
                var aktualnyScroll = $(document).scrollTop();
                createCookie('scroll', aktualnyScroll, 1);                
                //
                $.post("ajax/zamowienie_produkt_vat.php?tok=" + $('#tok').val(), { id : $(this).attr('data-id'), zamowienie : $(this).attr('data-zamowienie'), vat : $(this).val(), vat_od: $("input[name=vat_od]:checked").val() },
                    function(data) {                        
                      window.location = 'sprzedaz/zamowienia_szczegoly.php?id_poz=<?php echo (int)$_GET['id_poz']; ?><?php echo ((PRODUKTY_SZCZEGOLY_ZAMOWIENIA == 'dodatkowa zakładka') ? '&zakladka=2' : ''); ?><?php echo ((isset($_GET['produkt']) && trim((string)$_GET['produkt']) != '') ? '&produkt=' . $_GET['produkt'] : ''); ?>';
                    }           
                );  
                //
            });
            
            $('#ZastosujVat').click(function() {
                //
                $('#ekr_preloader').css('display','block');
                //
                var aktualnyScroll = $(document).scrollTop();
                createCookie('scroll', aktualnyScroll, 1);                
                //
                $.post("ajax/zamowienie_produkt_vat.php?tok=" + $('#tok').val(), { zamowienie : $(this).attr('data-zamowienie'), vat : $('#ListaVatZmiany').val(), vat_od: $("input[name=vat_od]:checked").val() },
                    function(data) {                    
                      window.location = 'sprzedaz/zamowienia_szczegoly.php?id_poz=<?php echo (int)$_GET['id_poz']; ?><?php echo ((PRODUKTY_SZCZEGOLY_ZAMOWIENIA == 'dodatkowa zakładka') ? '&zakladka=2' : ''); ?>';
                    }           
                );  
                //
            });               
            
            $('.RabatProduktu').change(function() {
                //
                var ew = $(this).attr('data-glownyrabat');
                if ( parseFloat($(this).find('input').val()) > 100 ) {
                    //
                    $(this).find('input').val(ew);
                    $.colorbox( { html:'<div id="PopUpInfo">Maksymalna wartość rabatu to 100%</div>', initialWidth:50, initialHeight:50, maxWidth:'90%', maxHeight:'90%' } );
                    //
                } else {
                    //
                    $('#ekr_preloader').css('display','block');
                    //
                    var aktualnyScroll = $(document).scrollTop();
                    createCookie('scroll', aktualnyScroll, 1);                
                    //
                    $.post("ajax/zamowienie_produkt_rabat.php?tok=" + $('#tok').val(), { id : $(this).attr('data-id'), zamowienie : $(this).attr('data-zamowienie'), nowy_rabat : $(this).find('input').val() },
                        function(data) {                       
                          window.location = 'sprzedaz/zamowienia_szczegoly.php?id_poz=<?php echo (int)$_GET['id_poz']; ?><?php echo ((PRODUKTY_SZCZEGOLY_ZAMOWIENIA == 'dodatkowa zakładka') ? '&zakladka=2' : ''); ?><?php echo ((isset($_GET['produkt']) && trim((string)$_GET['produkt']) != '') ? '&produkt=' . $_GET['produkt'] : ''); ?>';
                        }           
                    );  
                    //
                }
                //
            });
            
            $('#ZastosujRabat').click(function() {
                //
                if ( parseFloat($('#OgolnyRabat').val()) > 100 ) {
                    //
                    $('#OgolnyRabat').val(0);
                    $.colorbox( { html:'<div id="PopUpInfo">Maksymalna wartość rabatu to 100%</div>', initialWidth:50, initialHeight:50, maxWidth:'90%', maxHeight:'90%' } );
                    //                  
                } else {
                    //
                    $('#ekr_preloader').css('display','block');
                    //
                    var aktualnyScroll = $(document).scrollTop();
                    createCookie('scroll', aktualnyScroll, 1);                
                    //
                    $.post("ajax/zamowienie_produkt_rabat.php?tok=" + $('#tok').val(), { zamowienie : $(this).attr('data-zamowienie'), nowy_rabat : $('#OgolnyRabat').val() },
                        function(data) {                    
                          window.location = 'sprzedaz/zamowienia_szczegoly.php?id_poz=<?php echo (int)$_GET['id_poz']; ?><?php echo ((PRODUKTY_SZCZEGOLY_ZAMOWIENIA == 'dodatkowa zakładka') ? '&zakladka=2' : ''); ?>';
                        }           
                    );  
                    //
                }
                //
            });   

            $('#SzukajProduktuZamowienie').click(function() {
                //
                var fraza = $('#szukany_zamowienie').val();
                if ( fraza.length < 2 ) {
                     $.colorbox( { html:'<div id="PopUpInfo">Minimalna ilość znaków do wyszukiwania to 2</div>', initialWidth:50, initialHeight:50, maxWidth:'90%', maxHeight:'90%' } );
                     return false;
                }
                //
                $('#ekr_preloader').css('display','block');
                //
                setTimeout(function(){ window.location = '/zarzadzanie/sprzedaz/zamowienia_szczegoly.php?id_poz=<?php echo (int)$_GET['id_poz']; ?><?php echo ((PRODUKTY_SZCZEGOLY_ZAMOWIENIA == 'dodatkowa zakładka') ? '&zakladka=2' : ''); ?>&produkt=' + fraza }, 300);
                //
            });             

        });
        </script>         
        
        <?php 
        // sprawdzi czy sa jakies produkty z kodem gtu
        
        $kod_gtu = false;          
        foreach ( $zamowienie->produkty as $produkt ) {
              //
              if ( $produkt['gtu'] != '' ) {
                   $kod_gtu = true;   
              }
              //            
        } 
        ?>
        
        <table class="listing_tbl" id="InfoTabelaProdukty">
        
          <tr class="div_naglowek">
            <td class="ListingSchowajMobile">Info</td>
            <td>ID</td>
            <td class="ListingSchowajMobile">Foto</td>
            <td>Nazwa</td>
            <?php echo (($kod_gtu == true) ? '<td>Kod GTU</td>' : ''); ?>
            <td>Rabat %</td>
            <td class="ListingSchowajMobile">Cena netto</td>
            <td class="ListingSchowajMobile">Podatek</td>
            <td>Cena brutto</td>
            <td>Ilość</td>
            <td>Wartość brutto</td>
            <td></td>
          </tr>
          
          <?php 

          //for ($i=0, $n=count($zamowienie->produkty); $i<$n; $i++) {
           
          $SumaCenBrutto = 0;
          $SumaCenNetto = 0;
          $SumaSzukania = 0;

          foreach ( $zamowienie->produkty as $produkt ) {
            
            $wyswietl = false; 
            
            if ( isset($_GET['produkt']) && trim((string)$_GET['produkt']) != '' ) {
                 //
                 if ( mb_strpos( mb_strtolower((string)$produkt['nazwa'], 'UTF-8'), trim(mb_strtolower((string)$_GET['produkt'], 'UTF-8')) ) > -1 ||
                      mb_strpos( mb_strtolower((string)$produkt['model'], 'UTF-8'), trim(mb_strtolower((string)$_GET['produkt'], 'UTF-8')) ) > -1 ||
                      mb_strpos( mb_strtolower((string)$produkt['kod_producenta'], 'UTF-8'), trim(mb_strtolower((string)$_GET['produkt'], 'UTF-8')) ) > -1 ) {
                      $wyswietl = true;
                 }
                 //
            } else {
                 //
                 $wyswietl = true;
                 //
            }

            if ( $wyswietl == true ) {

                $SumaSzukania++;
                
                $wyswietl_cechy = '';

                if (isset($produkt['attributes']) && (count($produkt['attributes']) > 0)) {

                  foreach ($produkt['attributes'] as $cecha ) {
                    $wyswietl_cechy .= '<span class="MaleNrKatalogowy">'.$cecha['cecha'] . ': <b>' . $cecha['wartosc'] . '</b></span>';
                  }
                }
                
                // czyszczenie z &nbsp; i zbyt dlugiej nazwy
                $produkt['nazwa'] = Funkcje::PodzielNazwe($produkt['nazwa']);
                $produkt['model'] = Funkcje::PodzielNazwe($produkt['model']);

                ?>
                <tr class="pozycja_off">
                  <td style="width:30px" class="ListingSchowajMobile">
                      <?php if ( $produkt['id_produktu'] > 0 ) { ?>
                      <div id="produkt<?php echo rand(1,999); ?>_<?php echo $produkt['id_produktu']; ?>" class="zmzoom_produkt"><div class="podglad_zoom"></div><img src="obrazki/info_duze.png" alt="Szczegóły" /></div>
                      <?php } ?>
                  </td>
                  <td><?php echo (($produkt['id_produktu'] > 0) ? $produkt['id_produktu'] : '-'); ?></td>
                  <td class="ListingSchowajMobile"><?php echo Funkcje::pokazObrazek($produkt['zdjecie'], $produkt['nazwa'], '40', '40'); ?></td>
                  <td style="text-align:left">
                  <?php 
                  if ( $produkt['id_produktu'] > 0 ) {
                       echo '<a class="LinkProduktu" target="_blank" href="' . Seo::link_SEO( $produkt['nazwa'], $produkt['id_produktu'], 'produkt', '', false ) . '">'.$produkt['nazwa'].'</a>';
                     } else {
                       echo '<span class="LinkProduktu">'.$produkt['nazwa'].'</span>';
                  }
                  if (trim((string)$produkt['model']) != '') {
                    echo '<span class="MaleNrKatalogowy">Nr kat: <b>'.$produkt['model'].'</b></span>';
                  }
                  if (trim((string)$produkt['ean']) != '') {
                    echo '<span class="MaleNrKatalogowy">EAN: <b>'.$produkt['ean'].'</b></span>';
                  }  
                  if (trim((string)$produkt['kod_producenta']) != '') {
                    echo '<span class="MaleNrKatalogowy">Kod producenta: <b>'.$produkt['kod_producenta'].'</b></span>';
                  }                 
                  if (trim((string)$produkt['kod_plu']) != '') {
                    echo '<span class="MaleNrKatalogowy">Kod PLU: <b>'.$produkt['kod_plu'].'</b></span>';
                  }
                  // pobieranie danych o producencie
                  if (trim((string)$produkt['producent']) != '') {                      
                      //
                      echo '<span class="MaleNrKatalogowy">Producent: <b>'.$produkt['producent'].'</b></span>';
                      //
                  }                  
                  // wyswietlenie cech produktu
                  if (!empty($wyswietl_cechy)) {                     
                      //
                      echo '<div class="ListaCechy">' . $wyswietl_cechy . '</div>';
                      //
                  }
                  // komentarz do produktu
                  if (!empty($produkt['komentarz'])) {
                    echo '<span class="MaleNrKatalogowy">Komentarz: <b>'.$produkt['komentarz'].'</b></span>';
                  }       
                  // dodatkowe pola opisowe
                  if (!empty($produkt['pola_txt'])) {
                    //
                    $poleTxt = Funkcje::serialCiag($produkt['pola_txt']);
                    if ( count($poleTxt) > 0 ) {
                        foreach ( $poleTxt as $wartoscTxt ) {
                            // jezeli pole to plik
                            if ( $wartoscTxt['typ'] == 'plik' ) {
                                echo '<span class="MaleNrKatalogowy">' . $wartoscTxt['nazwa'] . ': <a target="_blank" href="' . ADRES_URL_SKLEPU . '/wgrywanie/' . $wartoscTxt['tekst'] . '"><b>załączony plik</b></a></span>';
                              } else {
                                echo '<span class="MaleNrKatalogowy">' . $wartoscTxt['nazwa'] . ': <b>' . $wartoscTxt['tekst'] . '</b></span>';
                            }                                          
                        }
                    }
                    unset($poleTxt);
                    //
                  }      

                  // id zewnetrzne
                  if ( ZAMOWIENIE_LISTING_ID_ZEWNETRZNE == 'tak' ) {
                       //
                       if (!empty($produkt['id_produktu_magazyn'])) {
                         echo '<span class="MaleNrKatalogowy">Id zewnętrzne: <b>' . $produkt['id_produktu_magazyn'] . '</b></span>';
                       }
                       //
                  }             

                  // nr referencyjne
                  if ( ZAMOWIENIE_LISTING_NR_REFERENCYJNE == 'tak' ) {
                       //
                       for ( $r = 1; $r < 6; $r++ ) {
                            //
                           if (!empty($produkt['nr_referen_' . $r])) {
                              if ( !empty($produkt['nr_referen_' . $r . '_opis'])) {
                                 echo '<span class="MaleNrKatalogowy">' . $produkt['nr_referen_' . $r . '_opis'] . ': <b>' . $produkt['nr_referen_' . $r] . '</b></span>';
                              } else {
                                 echo '<span class="MaleNrKatalogowy">Nr referencyjny ' . $r . ': <b>' . $produkt['nr_referen_' . $r] . '</b></span>';
                              }
                           } 
                       }
                       //
                  }                       

                  // czy czesc zestawu
                  if ( $produkt['id_zestawu'] > 0 ) {
                       //
                       echo '<div class="CzescZestawu">produkt z zestawu: <a class="LinkProduktu" target="_blank" href="' . Seo::link_SEO( $produkt['nazwa_zestawu'], $produkt['id_zestawu'], 'produkt', '', false ) . '">' . $produkt['nazwa_zestawu'] . '</a></div>';
                       //
                  }

                  ?>
                  </td>
                  
                  <?php if ( $kod_gtu == true ) { ?>
                  <td>
                      <?php 
                      if ( $produkt['gtu'] != '' ) {
                           echo $produkt['gtu'];
                      } 
                      ?>
                  </td> 
                  <?php } ?>
                  
                  <td>
                      <?php if ( $produkt['cena_punkty'] == 0 ) { ?>
                      <div class="RabatProduktu" data-id="<?php echo $produkt['orders_products_id']; ?>" data-zamowienie="<?php echo $zamowienie->info['id_zamowienia']; ?>" data-rabat="<?php echo $produkt['rabat']; ?>" data-glownyrabat="<?php echo $produkt['rabat']; ?>">
                          <input type="text" class="kropka" size="5" value="<?php echo $produkt['rabat']; ?>" />
                      </div>
                      <?php } ?>
                  </td>
                  <td class="ListingSchowajMobile" style="white-space: nowrap">
                      <?php                   
                      if ( $produkt['cena_punkty'] > 0 ) {
                           echo $produkt['cena_punkty'] . ' pkt + '; 
                      }
                      echo $waluty->FormatujCene($produkt['cena_koncowa_netto'], false, $zamowienie->info['waluta']); 
                      ?>
                  </td>
                  <td class="ListingSchowajMobile">
                      <?php 
                      echo Funkcje::RozwijaneMenu('vat[' . $produkt['orders_products_id'] . ']', Produkty::TablicaStawekVat('', true), $produkt['tax'] . '|' . $produkt['tax_id'], 'data-id="' . $produkt['orders_products_id'] . '" data-zamowienie="' . $zamowienie->info['id_zamowienia'] . '" class="VatProduktu" style="width:80px"'); 
                      ?> 
                  </td>
                  <td style="white-space: nowrap">
                      <?php 
                      if ( $produkt['cena_punkty'] > 0 ) {
                           echo $produkt['cena_punkty'] . ' pkt + '; 
                      }
                      echo $waluty->FormatujCene($produkt['cena_koncowa_brutto'], false, $zamowienie->info['waluta']);
                      ?>
                  </td>
                  <td><?php echo $produkt['ilosc']; ?></td>
                  <td style="white-space: nowrap">
                      <?php                   
                      if ( $produkt['cena_punkty'] > 0 ) {
                           echo ($produkt['cena_punkty'] * $produkt['ilosc']) . ' pkt + '; 
                      }
                      echo $waluty->FormatujCene($produkt['cena_koncowa_brutto'] * $produkt['ilosc'], false, $zamowienie->info['waluta']); 
                      //
                      if ( $produkt['tax'] > 0 ) {
                           $NettoTmp = ($produkt['cena_koncowa_brutto'] * $produkt['ilosc']) / ((100 + $produkt['tax']) / 100);
                      } else {
                           $NettoTmp = $produkt['cena_koncowa_brutto'] * $produkt['ilosc'];
                      }
                      //
                      $SumaCenNetto += $NettoTmp;
                      $SumaCenBrutto += ($produkt['cena_koncowa_brutto'] * $produkt['ilosc']);
                      //
                      unset($NettoTmp);
                      //
                      ?>
                  </td>

                  <td class="rg_right IkonyPionowo">
                    <a class="TipChmurka" href="sprzedaz/zamowienia_produkt_edytuj.php?id_poz=<?php echo (int)$_GET['id_poz']; ?>&amp;produkt_id=<?php echo (int)$produkt['orders_products_id'];?><?php echo ((PRODUKTY_SZCZEGOLY_ZAMOWIENIA == 'dodatkowa zakładka') ? '&amp;zakladka=2' : ''); ?>"><b>Edytuj produkt w zamówieniu</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>                    
                    <a class="TipChmurka" href="sprzedaz/zamowienia_produkt_usun.php?id_poz=<?php echo (int)$_GET['id_poz']; ?>&amp;produkt_id=<?php echo (int)$produkt['orders_products_id'];?><?php echo ((PRODUKTY_SZCZEGOLY_ZAMOWIENIA == 'dodatkowa zakładka') ? '&amp;zakladka=2' : ''); ?>"><b>Usuń produkt z zamówienia</b><img src="obrazki/kasuj.png" alt="Usuń produkt" /></a>                    
                    <?php if ( (int)$produkt['id_produktu'] > 0 ) { ?>
                    <br /><a class="TipChmurka" href="produkty/produkty_edytuj.php?id_poz=<?php echo (int)$produkt['id_produktu']; ?>&amp;zamowienie_id=<?php echo (int)$_GET['id_poz'];?><?php echo ((PRODUKTY_SZCZEGOLY_ZAMOWIENIA == 'dodatkowa zakładka') ? '&amp;zakladka_zamowienie=2' : ''); ?>"><b>Edytuj dane produktu w sklepie</b><img src="obrazki/duplikuj.png" alt="Edytuj produkt" /></a>
                    <?php } ?>
                  </td>
                  
                </tr>
                
            <?php } ?>
            
          <?php } ?>
          
          <?php if ( $SumaSzukania > 0 ) { ?>
          
          <?php if ( !isset($_GET['produkt']) ) { ?>
          
          <tr class="OgolnyRabat">
            <td colspan="<?php echo (( $kod_gtu == true ) ? 12 : 11); ?>">
            
                Ustaw jednakowy rabat dla wszystkich produktów: &nbsp;
                <input type="text" class="kropka" size="5" id="OgolnyRabat" value="0.00" />
                <input data-zamowienie="<?php echo $zamowienie->info['id_zamowienia']; ?>" type="submit" class="przyciskNon" value="Zastosuj" id="ZastosujRabat" /> 

            </td>          
          </tr>
          
          <tr class="OgolnyVat">
            <td colspan="<?php echo (( $kod_gtu == true ) ? 12 : 11); ?>">
            
                Ustaw jednakową stawkę VAT dla wszystkich produktów: &nbsp;
                <?php echo Funkcje::RozwijaneMenu('vat_zmiana', Produkty::TablicaStawekVat('', true), '', ' id="ListaVatZmiany" style="maxwidth:130px"'); ?>
                <input data-zamowienie="<?php echo $zamowienie->info['id_zamowienia']; ?>" type="submit" class="przyciskNon" value="Zastosuj" id="ZastosujVat" /> 

            </td>          
          </tr>          
          
          <?php } ?>
          
          <tr class="WartoscProduktow">
            <td colspan="<?php echo (( $kod_gtu == true ) ? 12 : 11); ?>">
            
                <div>Wartość produktów:</div>
                <div>
                     <strong id="SumaProduktowBrutto"><?php echo $waluty->FormatujCene($SumaCenBrutto, false, $zamowienie->info['waluta']); ?></strong> brutto 
                     <strong id="SumaProduktowNetto"><?php echo $waluty->FormatujCene($SumaCenNetto, false, $zamowienie->info['waluta']); ?></strong> netto
                </div>
                
            </td>
          </tr>          
          
          <?php } else { ?>
          
          <tr>
            <td colspan="<?php echo (( $kod_gtu == true ) ? 12 : 11); ?>" style="padding:15px">
            
                Brak wyników wyszukiwania ...

            </td>          
          </tr>          
          
          <?php } ?>
          
          <?php
          unset($SumaSzukania, $SumaCenBrutto, $SumaCenNetto);
          ?>
          
        </table>
        
      </div>
      
      <?php } ?>

      <div id="dodaj_pozycje" <?php echo (( PRODUKTY_SZCZEGOLY_ZAMOWIENIA == 'dodatkowa zakładka' ) ? 'style="padding:20px 10px"' : 'style="padding-top:20px"'); ?>>
          <div>
              <a class="dodaj" href="sprzedaz/zamowienia_szczegoly_produkt_dodaj.php?id_poz=<?php echo (int)$_GET['id_poz']; ?><?php echo ((PRODUKTY_SZCZEGOLY_ZAMOWIENIA == 'dodatkowa zakładka') ? '&amp;zakladka=2' : ''); ?>">dodaj nową pozycję</a>
          </div>
      </div>      
      
    </div>
    
<?php
}
?>