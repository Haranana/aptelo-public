<?php if ($prot->wyswietlStrone) { ?>

<div id="zakl_id_0" style="display:none;">

    <div class="info_tab" style="padding-top:0px">
    <?php
    $licznik_zakladek = $tab_0;
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
            ?>
            
            <div id="info_tab_id_<?php echo $w + $liczba; ?>" style="display:none;">
            
                <?php
                if ($id_produktu > 0) {
                    // pobieranie danych jezykowych
                    $zapytanie_jezyk = "select distinct products_name, products_name_info, products_name_short from products_description where products_id = '".$id_produktu."' and language_id = '" .$ile_jezykow[$w]['id']."'";
                    $sqls = $db->open_query($zapytanie_jezyk);
                    $prod = $sqls->fetch_assoc();
                    //
                    $nazwa_produktu = (isset($prod['products_name']) ? $prod['products_name'] : '');
                    $dodatkowa_nazwa = (isset($prod['products_name_info']) ? $prod['products_name_info'] : '');
                    $nazwa_krotka = (isset($prod['products_name_short']) ? $prod['products_name_short'] : '');
                    //
                  } else {
                    //
                    $nazwa_produktu = '';
                    $dodatkowa_nazwa = '';
                    $nazwa_krotka = '';
                    //
                }
                ?>
            
                <p>
                   <?php if ($w == '0') { ?>
                    <label class="required" for="nazwa_0">Nazwa <?php echo (($zestaw) ? 'zestawu' : 'produktu'); ?>:</label>
                    <input type="text" name="nazwa_<?php echo $w; ?>" size="80" value="<?php echo Funkcje::formatujTekstInput($nazwa_produktu); ?>" id="nazwa_0" />
                    
                    <script>
                    $('#NazwaProduktu').html('<?php echo str_replace("'","&apos;",$nazwa_produktu); ?>');
                    </script>
                    
                   <?php } else { ?>
                    <label for="nazwa_<?php echo $w; ?>">Nazwa produktu:</label>   
                    <input type="text" name="nazwa_<?php echo $w; ?>" id="nazwa_<?php echo $w; ?>" size="80" value="<?php echo Funkcje::formatujTekstInput($nazwa_produktu); ?>" />
                   <?php } ?>
                </p> 
                
                <p>
                   <label for="nazwa_info_<?php echo $w; ?>">Dodatkowa nazwa:</label>
                   <input type="text" name="nazwa_info_<?php echo $w; ?>" id="nazwa_info_<?php echo $w; ?>" size="80" value="<?php echo Funkcje::formatujTekstInput($dodatkowa_nazwa); ?>" /><em class="TipIkona"><b>Dodatkowa informacja przy eksporcie do porównywarek</b></em>
                </p>

                <p>
                   <label for="nazwa_krotka_<?php echo $w; ?>">Nazwa skrócona:</label>
                   <input type="text" name="nazwa_krotka_<?php echo $w; ?>" id="nazwa_krotka_<?php echo $w; ?>" size="80" value="<?php echo Funkcje::formatujTekstInput($nazwa_krotka); ?>" /><em class="TipIkona"><b>Dodatkowa informacja używna np. przy wyświetlaniu wariantów</b></em>
                </p>
                
                <?php
                if ($id_produktu > 0) {  
                    $db->close_query($sqls);
                    unset($prod);
                }
                unset($dodatkowa_nazwa);                  

                ?>
              
            </div>
            <?php                    
        }                    
        ?>                      
    </div>   
    
    <?php
    // pobieranie danych od produkcie z tablicy products
    $zapytanie_produkt = "select * from products where products_id = '".$id_produktu."'";
    $sqls = $db->open_query($zapytanie_produkt);
    $prod = $sqls->fetch_assoc();
    ?>    

    <?php if ($zestaw) { ?>
    
    <script>
    function produkty_dodania_zestawu(szukanie) { 
        //
        if ( $('#ProduktyLista').html() != '' || $('#szukany').val() != '' ) {
             var fraza = $('#szukany').val();
             if ( fraza.length < 2 ) {
                 $.colorbox( { html:'<div id="PopUpInfo">Minimalna ilość znaków do wyszukiwania to 2</div>', initialWidth:50, initialHeight:50, maxWidth:'90%', maxHeight:'90%' } );
                 return false;
             }
        
            if ($('#ProduktyLista').length) {
                //
                $('#ProduktyLista').css('display','block');
                $('#ProduktyLista').html('<img src="obrazki/_loader.gif">');
                $.get("ajax/lista_produktow_zestawy.php", 
                    { fraza: $('#szukany').val(), id_wybrane: $('#id_zestawu').val(), tok: $('#tok').val() },
                    function(data) { 
                        $('#ProduktyLista').css('display','none');
                        $('#ProduktyLista').html(data);
                        $('#ProduktyLista').css('display','block');    
                        pokazChmurki();
                });    
                //
            }
        }
        //
    }
    
    function dodaj_do_zestawu(id) {
        //
        var wybrane_id = $('#id_zestawu').val();        
        var tab_id = wybrane_id.split(',');
        var tab_wynik = new Array();
        var txt_id = '';
        //
        tab_wynik[id] = id;
        //
        for ( x = 0; x < tab_id.length; x++ ) {
              tab_wynik[tab_id[x]] = tab_id[x];
        }
        //
        for ( x = 0; x < tab_wynik.length; x++ ) {
              if ( parseInt(tab_wynik[x]) > 0 ) {
                   txt_id = txt_id + tab_wynik[x] + ',';
              }
        }       
        //
        $('#id_zestawu').val(txt_id);
        //
        lista_produktow_zestawu();
        //
        produkty_dodania_zestawu();
        //
    }
    
    function usun_z_zestawu(id) {
        //
        var wybrane_id = $('#id_zestawu').val();        
        var tab_id = wybrane_id.split(',');
        var tab_wynik = new Array();
        var txt_id = '';
        //
        for ( x = 0; x < tab_id.length; x++ ) {
              if ( tab_id[x] != id ) {
                   tab_wynik[tab_id[x]] = tab_id[x];
              }
        }
        //
        for ( x = 0; x < tab_wynik.length; x++ ) {
              if ( parseInt(tab_wynik[x]) > 0 ) {
                   txt_id = txt_id + tab_wynik[x] + ',';
              }
        }       
        //
        $('#id_zestawu').val(txt_id);
        //
        lista_produktow_zestawu();
        //
        produkty_dodania_zestawu();
        //
    }    
    
    function suma_zestawu() {
        //
        var suma_zestawu = 0;
        var suma_produktow = 0;
        var wartosc_rabatu = 0;
        //
        $('#WynikPrzewijany .TabelaProdukt tr').each(function() {
           //
           var cena = parseFloat($(this).attr('data-cena'));
           var id_pr = $(this).attr('data-id');
           if ( cena > 0 ) {
               //
               suma_produktow += parseInt($('#rabat_ilosc_' + id_pr).val()) * cena;
               //
               var rabat_kwotowy = 0;
               var rabat_procentowy = 0;
               //
               if ( parseFloat($('#rabat_kwota_' + id_pr).val()) > 0 ) {
                    rabat_kwotowy = parseFloat($('#rabat_kwota_' + id_pr).val());
               }
               if ( parseFloat($('#rabat_procent_' + id_pr).val()) > 0 ) {
                    rabat_procentowy = parseFloat($('#rabat_procent_' + id_pr).val());
               }               
               //
               if ( rabat_procentowy > 0 ) {
                    cena = cena * ((100 - rabat_procentowy)/100);
               }               
               if ( rabat_kwotowy > 0 ) {
                    cena = cena - rabat_kwotowy;
               }
               cena = cena.toFixed(2);
               //
               suma_zestawu += parseInt($('#rabat_ilosc_' + id_pr).val()) * cena;
               //
           }
        });
        //
        wartosc_rabatu = format_zl(suma_produktow - suma_zestawu);
        suma_zestawu = format_zl(suma_zestawu);
        suma_produktow = format_zl(suma_produktow);
        //
        $('#KoncowaCenaZestawu').html( suma_zestawu.replace('.', ',') );
        $('#WartoscProduktowZestawu').html( suma_produktow.replace('.', ',') );
        $('#WartoscRabatu').html( wartosc_rabatu.replace('.', ',') );
        //
    }
    
    function lista_produktow_zestawu( domyslna_wartosc ) {
        //
        if ($('#ProduktyListaDodane').length) {
            //
            var dane = new Array();
            var wybrane_id = $('#id_zestawu').val();  
            var tab_id = wybrane_id.split(',');
            var t = 0;
            for ( x = 0; x < tab_id.length; x++ ) {
                  //
                  if ( parseInt(tab_id[x]) > 0 ) {
                      dane[t] = new Array('rabat_kwota_' + tab_id[x], $('#rabat_kwota_' + tab_id[x]).val());
                      dane[t + 1] = new Array('rabat_procent_' + tab_id[x], $('#rabat_procent_' + tab_id[x]).val());
                      dane[t + 2] = new Array('rabat_ilosc_' + tab_id[x], $('#rabat_ilosc_' + tab_id[x]).val());
                      //
                      t = t + 3;
                  }
                  //
            }
            //
            $('#ProduktyListaDodane').css('display','block');
            $('#ProduktyListaDodane').html('<img src="obrazki/_loader.gif">');
            //            
            if ( domyslna_wartosc == undefined ) {
                 var dane_rabatu = JSON.stringify( dane );
                 var domyslna = 0;
              } else {
                 var dane_rabatu = domyslna_wartosc; 
                 var domyslna = 1;
            }
            //
            $.get("ajax/lista_produktow_zestawy_dodane.php", 
                { id_wybrane: $('#id_zestawu').val(), data: dane_rabatu, domyslna: domyslna, tok: $('#tok').val() },
                function(data) { 
                    $('#ProduktyListaDodane').css('display','none');
                    $('#ProduktyListaDodane').html(data);
                    $('#ProduktyListaDodane').css('display','block');    
                    //
                    $(".kropkaPusta").change(		
                      function () {
                        var type = this.type;
                        var tag = this.tagName.toLowerCase();
                        zamien_krp($(this),'');
                      }
                    );       
                    $(".calkowitaPelna").change(	
                        function () {
                            if (isNaN($(this).val())) {
                                $(this).val('1');
                               } else {
                                if ( isNaN(parseInt($(this).val())) ) {
                                    $(this).val('1');
                                  } else {
                                    $(this).val( parseInt($(this).val()) );
                                }
                            }
                            suma_zestawu();                            
                        }
                    );                    
                    //
                    $('.rabatZestawu').change(function() {
                        suma_zestawu();
                    });
                    //
                    pokazChmurki();
                    //
                    suma_zestawu();
                    //                    
            });    
            //
        } 
        //
    }
    </script>
    
    <div class="SkladnikiZestawu">
    
        <?php
        $id_zestawu = '';
        $rabaty_zestaw = array();
        //
        if ( isset($prod['products_set_products']) ) {
             //
             $tab_id = unserialize($prod['products_set_products']);
             //
             if ( isset($tab_id) && count($tab_id) > 0 ) {
                  //
                  foreach ($tab_id as $id_klucz => $tmp) {
                      $id_zestawu .= $id_klucz . ',';
                      $rabaty_zestaw['rabat_kwota_' . $id_klucz] = $tmp['rabat_kwota'];
                      $rabaty_zestaw['rabat_procent_' . $id_klucz] = $tmp['rabat_procent'];
                      $rabaty_zestaw['rabat_ilosc_' . $id_klucz] = $tmp['rabat_ilosc'];
                  }
                  //
             }
             //
        }
        ?>
        <input type="hidden" id="id_zestawu" name="id_zestawu" value="<?php echo $id_zestawu; ?>" />

        <?php unset($id_zestawu); ?>
        
        <div class="NaglowekZestawu">Składniki zestawu</div>
        
        <div id="ProduktyListaDodane"></div>

        <div id="SzukanieProduktu">
            <div>Wyszukaj produkt: <input type="text" id="szukany" size="25" value="" /><em class="TipIkona"><b>Wyszukaj produkty, które mają zostać dodane do zestawu. Wpisz nazwę produktu, nr katalogowy lub kod producenta</b></em></div><span onclick="produkty_dodania_zestawu()"></span>
            <div class="cl"></div>
        </div>  

        <div id="ProduktyLista"></div>
        
        <script>
        lista_produktow_zestawu(<?php echo ((count($rabaty_zestaw) > 0) ? "'" . base64_encode(serialize($rabaty_zestaw)) . "'" : ''); ?>);
        </script>
    
    </div>
    
    <?php } ?>

    <div class="ProduktGlowneInformacje">
    
        <div class="ProduktInfoLewe">
            
            <p>
              <label style="font-weight:bold">Czy <?php echo (($zestaw) ? 'zestaw' : 'produkt'); ?> jest aktywny ?</label>
              <input type="radio" name="status" value="1" id="status_tak" <?php echo ((isset($prod['products_status']) && $prod['products_status'] == '1' || empty($prod['products_status']) || !isset($prod['products_status'])) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="status_tak">tak</label>
              <input type="radio" name="status" value="0" id="status_nie" <?php echo (((isset($prod['products_status']) && $prod['products_status'] == '0')) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="status_nie">nie</label>              
            </p>

            <p>
              <label for="data_dostepnosci">Dostępny od dnia:</label>
              <input type="text" id="data_dostepnosci" name="data_dostepnosci" value="<?php echo ((isset($prod['products_date_available'])) ? ((Funkcje::czyNiePuste($prod['products_date_available'])) ? date('d-m-Y',FunkcjeWlasnePHP::my_strtotime($prod['products_date_available'])) : '') : ''); ?>" size="20" class="datepicker" />
              <em class="TipIkona"><b>Przy produkcie będzie wyświetlana informacja od kiedy będzie dostępny - produkt będzie wyświetlany w produktach oczekiwanych</b></em>
            </p>
            
            <p>
              <label>Czy wyłączyć produkt jeżeli data dostępności jest większa od bieżącej daty i włączyć kiedy osiągnie datę dostępności ?</label>
              <input type="radio" name="data_dostepnosci_status" value="1" id="data_dostepnosci_status_tak" <?php echo ((isset($prod['products_date_available_status']) && $prod['products_date_available_status'] == '1') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="data_dostepnosci_status_tak">tak</label>
              <input type="radio" name="data_dostepnosci_status" value="0" id="data_dostepnosci_status_nie" <?php echo (((isset($prod['products_date_available_status']) && $prod['products_date_available_status'] == '0') || !isset($prod['products_date_available_status'])) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="data_dostepnosci_status_nie">nie<em class="TipIkona"><b>Opcja dostępna jeżeli ustawiona będzie data dostępności</b></em></label>
            </p>  
                        
            <p>
              <label for="data_sprzedazy">Sprzedaż dostępna od dnia:</label>
              <input type="text" id="data_sprzedazy" name="data_sprzedazy" value="<?php echo ((isset($prod['products_date_available_buy'])) ? ((Funkcje::czyNiePuste($prod['products_date_available_buy'])) ? date('d-m-Y',FunkcjeWlasnePHP::my_strtotime($prod['products_date_available_buy'])) : '') : ''); ?>" size="20" class="datepickerMinuta" />
              <em class="TipIkona"><b>Data od kiedy produkt będzie można kupić - jeżeli nie będzie uzupełniona produkt będzie cały czas dostępny</b></em>
            </p>   
            
            <p>
              <label>Czy wyświetlać zegar kiedy produkt będzie można kupić ?</label>
              <input type="radio" name="zegar_sprzedaz" value="1" id="zegar_sprzedaz_tak" <?php echo ((isset($prod['products_date_available_clock']) && $prod['products_date_available_clock'] == '1') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="zegar_sprzedaz_tak">tak</label>
              <input type="radio" name="zegar_sprzedaz" value="0" id="zegar_sprzedaz_nie" <?php echo (((isset($prod['products_date_available_clock']) && $prod['products_date_available_clock'] == '0') || !isset($prod['products_date_available_clock'])) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="zegar_sprzedaz_nie">nie<em class="TipIkona"><b>Opcja dostępna jeżeli ustawiona będzie data rozpoczęcia sprzedaży</b></em></label>
            </p>            

            
            <p>
              <label for="data_dostepnosci_koniec">Dostępny do dnia:</label>
              <input type="text" id="data_dostepnosci_koniec" name="data_dostepnosci_koniec" value="<?php echo ((isset($prod['products_date_available_end'])) ? ((Funkcje::czyNiePuste($prod['products_date_available_end'])) ? date('d-m-Y',FunkcjeWlasnePHP::my_strtotime($prod['products_date_available_end'])) : '') : ''); ?>" size="20" class="datepicker" />
              <em class="TipIkona"><b>Produkt po tej dacie zostanie wyłączony (nie będzie wyświetlany w sklepie)</b></em>
            </p>

            <hr style="color:#82b4cd;border-top:1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:70%;margin:15px 10px 10px 25px;" />
            
            <p>
              <label>Czy <?php echo (($zestaw) ? 'zestaw' : 'produkt'); ?> wyświetlać w listingach produktów ?</label>
              <input type="radio" name="listing" value="0" id="listing_tak" <?php echo (((isset($prod['listing_status']) && $prod['listing_status'] == '0') || !isset($prod['listing_status'])) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="listing_tak">tak</label>
              <input type="radio" name="listing" value="1" id="listing_nie" <?php echo ((isset($prod['listing_status']) && $prod['listing_status'] == '1') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="listing_nie">nie <em class="TipIkona"><b>Produkt nie będzie wyświetlany w listingach produktów - szczegóły produktu będzie można zobaczyć tylko poprzez bezpośrednie wejście na stronę karty produktu</b></em></label>
            </p>             
            
            <p>
              <label for="rodzaj_produktu">Rodzaj <?php echo (($zestaw) ? 'zestawu' : 'produktu'); ?>:</label>
              <?php
              echo Funkcje::RozwijaneMenu('rodzaj_produktu', Produkty::TablicaRodzajProduktow(), ((isset($prod['products_type'])) ? $prod['products_type'] : ''), 'style="width:200px" id="rodzaj_produktu"');
              ?>
            </p>                
            
            <script>
            $(document).ready(function() {
                $('#stala_kwota').change(function() {
                   if ( parseFloat($(this).val()) < 0.01 || isNaN(parseFloat($(this).val())) ) {
                        $(this).val('0.01');
                   }
                });
                
                $('#nazwa_0').on("keyup",function() {
                   $('#NazwaProduktu').html( $(this).val() );
                });
            });
            
            function KupowaniePunkty(tryb) {
                if ( tryb == 1 ) {
                     $('.TylkoPunkty').slideDown();
                  } else {
                     $('.TylkoPunkty').slideUp();
                }
            };
            </script>                 
            
            <p <?php echo (($zestaw) ? 'style="display:none"' : ''); ?>>
              <label>Czy produkt można kupować ?</label>
              <input type="radio" name="kupowanie" id="kupowanie_tak" value="1" <?php echo (((isset($prod['products_buy']) && $prod['products_buy'] == '1') || !isset($prod['products_buy']))  ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="kupowanie_tak">tak</label>
              <input type="radio" name="kupowanie" id="kupowanie_nie" value="0" <?php echo ((isset($prod['products_buy']) && $prod['products_buy'] == '0') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="kupowanie_nie">nie</label>
            </p> 

            <p>
              <label>Czy ma być aktywna opcja zakupu przez 1 kliknięcie ?</label>
              <input type="radio" name="szybki_zakup" id="szybki_zakup_tak" value="1" <?php echo (((isset($prod['products_fast_buy']) && $prod['products_fast_buy'] == '1') || !isset($prod['products_fast_buy'])) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="szybki_zakup_tak">tak</label>
              <input type="radio" name="szybki_zakup" id="szybki_zakup_nie" value="0" <?php echo ((isset($prod['products_fast_buy']) && $prod['products_fast_buy'] == '0') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="szybki_zakup_nie">nie</label>
            </p>             
            
            <p>
              <label>Czy za zakup <?php echo (($zestaw) ? 'zestawu' : 'produktu'); ?> mają być naliczane punkty ?</label>
              <input type="radio" name="pkt_naliczanie" id="pkt_naliczanie_tak" value="1" <?php echo (((isset($prod['products_counting_points']) && $prod['products_counting_points'] == '1') || !isset($prod['products_counting_points'])) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="pkt_naliczanie_tak">tak</label>
              <input type="radio" name="pkt_naliczanie" id="pkt_naliczanie_nie" value="0" <?php echo ((isset($prod['products_counting_points']) && $prod['products_counting_points'] == '0') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="pkt_naliczanie_nie">nie</label>
            </p>              

            <p <?php echo (($zestaw) ? 'style="display:none"' : ''); ?>>
              <label>Czy produkt będzie można kupić tylko za punkty ?</label>
              <input type="radio" name="kupowanie_pkt" id="kupowanie_pkt_tak" onclick="KupowaniePunkty(1)" value="1" <?php echo (((isset($prod['products_points_only']) && $prod['products_points_only'] == '1') || !isset($prod['products_points_only'])) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="kupowanie_pkt_tak">tak<em class="TipIkona"><b>Produkt będzie można kupić tylko za punkty - nie będzie dostępny w normalnej sprzedaży</b></em></label>
              <input type="radio" name="kupowanie_pkt" id="kupowanie_pkt_nie" onclick="KupowaniePunkty(0)" value="0" <?php echo ((isset($prod['products_points_only']) && $prod['products_points_only'] == '0') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="kupowanie_pkt_nie">nie</label>
            </p>             

            <div <?php echo (($zestaw) ? 'style="display:none"' : ''); ?>>
            
                <p class="TylkoPunkty" <?php echo ((isset($prod['products_points_only']) && $prod['products_points_only'] != '0') ? '' : 'style="display:none"'); ?>>
                  <label for="ilosc_pkt">Wartość produktu w punktach:</label>
                  <input type="text" name="ilosc_pkt" id="ilosc_pkt" size="5" value="<?php echo ((isset($prod['products_points_value']) && (int)$prod['products_points_value'] > 0) ? (int)$prod['products_points_value'] : ''); ?>" /> pkt &nbsp; + &nbsp; 
                  <input type="text" name="stala_kwota" id="stala_kwota" class="kropka" size="5" value="<?php echo ((isset($prod['products_points_value_money']) &&  (float)$prod['products_points_value_money'] > 0) ? (float)$prod['products_points_value_money'] : '0.01'); ?>" /> <?php echo $_SESSION['domyslna_waluta']['symbol']; ?> &nbsp; 
                  <em class="TipIkona"><b>Ilość punktów jaką klient będzie musiał wydać, żeby zakupić produkt</b></em>
                </p> 

            </div>

            <p>
              <label>Czy za ten produkt można zapłacić punktami ?</label>
              <input type="radio" name="zakup_pkt" id="zakup_pkt_tak" value="1" <?php echo (((isset($prod['products_points_purchase']) && $prod['products_points_purchase'] == '1') || !isset($prod['products_points_purchase'])) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="zakup_pkt_tak">tak<em class="TipIkona"><b>Za produkt będzie można zapłacić punktami na stronie koszyka</b></em></label>
              <input type="radio" name="zakup_pkt" id="zakup_pkt_nie" value="0" <?php echo ((isset($prod['products_points_purchase']) && $prod['products_points_purchase'] == '0') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="zakup_pkt_nie">nie</label>
            </p>             

            <p>
              <label>Czy produkt ma mieć kontrolę stanu magazynowego ?</label>
              <input type="radio" name="magazyn" id="magazyn_tak" value="1" <?php echo (((isset($prod['products_control_storage']) && $prod['products_control_storage'] == '1') || !isset($prod['products_control_storage'])) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="magazyn_tak">tak<em class="TipIkona"><b>Produkt będzie objęty kontrolą stanu magazynowego zgodnie z ustawieniami magazynu w menu Konfiguracja</b></em></label>
              <input type="radio" name="magazyn" id="magazyn_nie" value="0" <?php echo ((isset($prod['products_control_storage']) && $prod['products_control_storage'] == '0') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="magazyn_nie">nie<em class="TipIkona"><b>Produkt będzie kupić w dowolnej ilości niezależnie od ustawień kontroli stanu magazynowego w sklepie oraz minimalnej i maksymalnej ilości zakupu</b></em></label>
              <input type="radio" name="magazyn" id="magazyn_tak_ograniczony" value="2" <?php echo ((isset($prod['products_control_storage']) && $prod['products_control_storage'] == '2') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="magazyn_tak_ograniczony">tak (ograniczoną)<em class="TipIkona"><b>Produkt będzie kupić w dowolnej ilości, po zakupie będzie aktualizowany stan magazynowy produktu</b></em></label>
            </p>         

            <p <?php echo (($zestaw) ? 'style="display:none"' : ''); ?>>
              <label style="color:#ff0000">Czy produkt ma mieć wyłączone wszystkie rabaty ?</label>
              <input type="radio" name="rabaty" id="rabaty_tak" value="1" <?php echo ((isset($prod['products_not_discount']) && $prod['products_not_discount'] == '1') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="rabaty_tak">tak<em class="TipIkona"><b>Cena produktu nie będzie podlegała żadnym rabatom - oprócz promocji, wyprzedaży, ofercie produkt dnia oraz w ramach zestawów produktów</b></em></label>
              <input type="radio" name="rabaty" id="rabaty_nie" value="0" <?php echo (((isset($prod['products_not_discount']) && $prod['products_not_discount'] == '0') || !isset($prod['products_not_discount'])) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="rabaty_nie">nie</label>             
            </p>              
            
            <p <?php echo (($zestaw) ? 'style="display:none"' : ''); ?>>
              <label>Czy produkt będzie dostępny tylko jako akcesoria dodatkowe ?</label>                  
              <input type="radio" name="akcesoria" id="akcesoria_tak" value="1" <?php echo ((isset($prod['products_accessory']) && $prod['products_accessory'] == '1') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="akcesoria_tak">tak<em class="TipIkona"><b>Produkt będzie dostępny tylko jako akcesoria dodatkowe i nie będzie go można zakupić osobno</b></em></label>
              <input type="radio" name="akcesoria" id="akcesoria_nie" value="0" <?php echo (((isset($prod['products_accessory']) && $prod['products_accessory'] == '0') || !isset($prod['products_accessory'])) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="akcesoria_nie">nie</label>
            </p>  
            
            <?php
            $TablicaGrupKlientow = Klienci::ListaGrupKlientow(false);
            ?>
            
            <table class="WyborCheckbox">
                <tr>
                    <td><label><?php echo (($zestaw) ? 'Zestaw' : 'Produkt'); ?> <b style="color:#549f11">widoczny</b> tylko dla grup klientów:</label></td>
                    <td>
                        <?php                        
                        foreach ( $TablicaGrupKlientow as $GrupaKlienta ) {
                            echo '<input type="checkbox" value="' . $GrupaKlienta['id'] . '" name="grupa_klientow[]" id="grupa_klientow_' . $GrupaKlienta['id'] . '" ' . ((in_array((string)$GrupaKlienta['id'], explode(',', ((isset($prod['customers_group_id'])) ? (string)$prod['customers_group_id'] : '')))) ? 'checked="checked" ' : '') . ' /><label class="OpisFor" for="grupa_klientow_' . $GrupaKlienta['id'] . '">' . $GrupaKlienta['text'] . '</label><br />';
                        }              
                        ?>
                    </td>
                </tr>
            </table>

            <table class="WyborCheckbox">
                <tr>
                    <td><label><?php echo (($zestaw) ? 'Zestaw' : 'Produkt'); ?> <b style="color:#373737">niewidoczny</b> dla grup klientów:</label></td>
                    <td>
                        <?php                        
                        foreach ( $TablicaGrupKlientow as $GrupaKlienta ) {
                            echo '<input type="checkbox" value="' . $GrupaKlienta['id'] . '" name="nie_grupa_klientow[]" id="nie_grupa_klientow_' . $GrupaKlienta['id'] . '" ' . ((in_array((string)$GrupaKlienta['id'], explode(',', ((isset($prod['not_customers_group_id'])) ? (string)$prod['not_customers_group_id'] : '')))) ? 'checked="checked" ' : '') . ' /><label class="OpisFor" for="nie_grupa_klientow_' . $GrupaKlienta['id'] . '">' . $GrupaKlienta['text'] . '</label><br />';
                        }              
                        ?>
                    </td>
                </tr>
            </table>
            
            <?php
            unset($TablicaGrupKlientow);
            ?>
            
            <div class="ostrzezenie odlegloscRwdTab" style="margin-bottom:10px;margin-right:20px">Jeżeli nie zostanie wybrana żadna grupa klientów to produkt będzie widoczny dla wszystkich klientów.</div>

            <p>
              <label for="sort">Kolejność wyświetlania:</label>
              <input type="text" name="sort" id="sort" size="5" value="<?php echo ((isset($prod['sort_order'])) ? ((Funkcje::czyNiePuste($prod['sort_order'])) ? $prod['sort_order'] : '') : ''); ?>" />
            </p>

            <p>
              <label for="data_dodania">Data dodania:</label>
              <input type="text" name="data_dodania" id="data_dodania" size="20" value="<?php echo ((isset($prod['products_date_added'])) ? ((Funkcje::czyNiePuste($prod['products_date_added'])) ? date('d-m-Y H:i',FunkcjeWlasnePHP::my_strtotime($prod['products_date_added'])) : date('d-m-Y H:i',time())) : date('d-m-Y H:i',time())); ?>" />
            </p>                 
        
            <p>
              <label for="nr_kat">Nr katalogowy:</label>
              <input type="text" name="nr_kat" id="nr_kat" size="30" value="<?php echo ((isset($prod['products_model'])) ? $prod['products_model'] : ''); ?>" />
            </p> 
            
            <p>
              <label for="kod_producenta">Kod producenta:</label>
              <input type="text" name="kod_producenta" id="kod_producenta" size="30" value="<?php echo ((isset($prod['products_man_code'])) ? $prod['products_man_code'] : ''); ?>" />
            </p> 

            <p>
              <label for="kod_plu">Kod PLU:</label>
              <input type="text" name="kod_plu" id="kod_plu" size="30" value="<?php echo ((isset($prod['products_plu_code'])) ? ((Funkcje::czyNiePuste($prod['products_plu_code'])) ? $prod['products_plu_code'] : '') : ''); ?>" /><em class="TipIkona"><b>(ang. price look-up unit) - numer (kod) towaru zaprogramowany w kasie fiskalnej.</b></em>
            </p> 
            
            <p>
              <label for="nr_ean">Kod EAN:</label>
              <input type="text" name="nr_ean" id="nr_ean" size="30" value="<?php echo ((isset($prod['products_ean'])) ? $prod['products_ean'] : ''); ?>" />
            </p> 
            
            <p>
              <label for="pkwiu">PWKIU:</label>
              <input type="text" name="pkwiu" id="pkwiu" size="30" value="<?php echo ((isset($prod['products_pkwiu'])) ? $prod['products_pkwiu'] : ''); ?>" />
            </p>                                         
            
            <?php
            //
            $tablica = array();
            $tablica[] = array('id' => '', 'text' => '-- brak --');
            $tablica[] = array('id' => 'GTU 01', 'text' => 'GTU 01 - Napoje alkoholowe');
            $tablica[] = array('id' => 'GTU 02', 'text' => 'GTU 02 - Paliwa');
            $tablica[] = array('id' => 'GTU 03', 'text' => 'GTU 03 - Oleje opałowe i oleje smarowe');
            $tablica[] = array('id' => 'GTU 04', 'text' => 'GTU 04 - Wyroby tytoniowe');
            $tablica[] = array('id' => 'GTU 05', 'text' => 'GTU 05 - Odpady');
            $tablica[] = array('id' => 'GTU 06', 'text' => 'GTU 06 - Urządzenia elektroniczne oraz części i materiałów do nich');
            $tablica[] = array('id' => 'GTU 07', 'text' => 'GTU 07 - Pojazdy oraz części samochodowe');
            $tablica[] = array('id' => 'GTU 08', 'text' => 'GTU 08 - Metale szlachetne oraz nieszlachetne');
            $tablica[] = array('id' => 'GTU 09', 'text' => 'GTU 09 - Leki oraz wyroby medyczne');
            $tablica[] = array('id' => 'GTU 10', 'text' => 'GTU 10 - Budynki, budowle i grunty');
            $tablica[] = array('id' => 'GTU 11', 'text' => 'GTU 11 - Obrót uprawnieniami do emisji gazów cieplarnianych');
            $tablica[] = array('id' => 'GTU 12', 'text' => 'GTU 12 - Usługi niematerialne m.in. marketingowe, reklamowe');
            $tablica[] = array('id' => 'GTU 13', 'text' => 'GTU 13 - Usługi transportowe i gospodarki magazynowej');            
            //             
            ?>
            
            <p>
              <label for="gtu">Kod GTU:</label>
              <?php                         
              echo Funkcje::RozwijaneMenu('gtu', $tablica, ((isset($prod['products_gtu'])) ? $prod['products_gtu'] : ''), 'style="max-width:200px" id="gtu"');
              unset($tablica);
              ?>
            </p>             

            <p>
              <label for="waga">Waga:</label>
              <input type="text" name="waga" id="waga" size="8" value="<?php echo ((isset($prod['products_weight'])) ? ((Funkcje::czyNiePuste($prod['products_weight'])) ? $prod['products_weight'] : '') : ''); ?>" class="Waga" /><em class="TipIkona"><b>Waga w kg np: 1 = 1kg, 0.2 = 200 gram - separatorem dziesiętnym jest kropka</b></em>
            </p>
            
            <p>
              <label for="waga_szerokosc">Waga wolumetryczna (szerokość):</label>
              <input type="text" name="waga_szerokosc" id="waga_szerokosc" size="8" value="<?php echo ((isset($prod['products_weight_width'])) ? ((Funkcje::czyNiePuste($prod['products_weight_width'])) ? $prod['products_weight_width'] : '') : ''); ?>" class="calkowita" /><em class="TipIkona"><b>Wartość całkowita w cm</b></em>
            </p>            
            
            <p>
              <label for="waga_wysokosc">Waga wolumetryczna (wysokość):</label>
              <input type="text" name="waga_wysokosc" id="waga_wysokosc" size="8" value="<?php echo ((isset($prod['products_weight_height'])) ? ((Funkcje::czyNiePuste($prod['products_weight_height'])) ? $prod['products_weight_height'] : '') : ''); ?>" class="calkowita" /><em class="TipIkona"><b>Wartość całkowita w cm</b></em>
            </p>     

            <p>
              <label for="waga_dlugosc">Waga wolumetryczna (długość):</label>
              <input type="text" name="waga_dlugosc" id="waga_dlugosc" size="8" value="<?php echo ((isset($prod['products_weight_length'])) ? ((Funkcje::czyNiePuste($prod['products_weight_length'])) ? $prod['products_weight_length'] : '') : ''); ?>" class="calkowita" /><em class="TipIkona"><b>Wartość całkowita w cm</b></em>
            </p>     
            
            <hr style="color:#82b4cd;border-top:1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:70%;margin:15px 10px 10px 25px;<?php echo (($zestaw) ? 'display:none' : ''); ?>" />

            <p <?php echo (($zestaw) ? 'style="display:none"' : ''); ?>>
              <label for="producent">Producent:</label>                                       
              <?php echo Funkcje::RozwijaneMenu('producent', Funkcje::TablicaProducenci('-- brak --'), ((isset($prod['manufacturers_id'])) ? $prod['manufacturers_id'] : ''), 'style="width:200px" id="producent"'); ?>
            </p>

            <p <?php echo (($zestaw) ? 'style="display:none"' : ''); ?>>
              <label for="link_o_bezpieczenstwie">Informacja o bezpieczeństwie:</label>
              <input type="text" name="link_o_bezpieczenstwie" id="link_o_bezpieczenstwie" size="30" value="<?php echo $prod['products_safety_information']; ?>" /><em class="TipIkona"><b>Link do pdf/zdjęcia z informacją o bezpieczeństwie produktu - zgodnie z wymaganiami rozporządzenia GPSR</b></em>
            </p>
            
            <hr style="color:#82b4cd;border-top:1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:70%;margin:15px 10px 10px 25px;<?php echo (($zestaw) ? 'display:none' : ''); ?>" />            
            
            <p>
              <label for="dostepnosci">Stan dostępności:</label>                                       
              <?php echo Funkcje::RozwijaneMenu('dostepnosci', Produkty::TablicaDostepnosci('-- brak --'), ((isset($prod['products_availability_id'])) ? $prod['products_availability_id'] : ''), 'style="width:200px" id="dostepnosci"'); ?>
            </p>     

            <p>
              <label for="wysylka">Wysyłka:</label>                                        
              <?php echo Funkcje::RozwijaneMenu('wysylka', Produkty::TablicaCzasWysylki('-- brak --'), ((isset($prod['products_shipping_time_id'])) ? $prod['products_shipping_time_id'] : ''), 'style="width:200px" id="wysylka"'); ?>
            </p> 

            <p>
              <label for="stan_produktu">Stan <?php echo (($zestaw) ? 'zestawu' : 'produktu'); ?>:</label>
              <?php
              //                  
              $domyslnyStan = 0;
              $sqle = $db->open_query("select * from products_condition cp, products_condition_description cpd where cp.products_condition_id = cpd.products_condition_id and cpd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'");

              $tablica = array();
              $tablica[] = array('id' => '0', 'text' => '-- brak --');

              while ($stan = $sqle->fetch_assoc()) {
                   $tablica[] = array('id' => $stan['products_condition_id'], 'text' => $stan['products_condition_name']);
                   //
                   if ($stan['products_condition_default'] == 1) {
                       $domyslnyStan = $stan['products_condition_id'];
                   }
              }
              $db->close_query($sqle);
              
              $wybierz = ((isset($prod['products_condition_products_id'])) ? $prod['products_condition_products_id'] : '');
              if (( empty($prod['products_condition_products_id']) || $prod['products_condition_products_id'] == '0' ) && $id_produktu == 0 ) {
                  $wybierz = $domyslnyStan;
              }
              //
              echo Funkcje::RozwijaneMenu('stan_produktu', $tablica, $wybierz, 'style="width:200px" id="stan_produktu"'); 
              //
              unset($stan, $tablica, $domyslnyStan, $wybierz);
              ?>
            </p>    

            <p>
              <label for="gwarancja">Gwarancja:</label>                                        
              <?php echo Funkcje::RozwijaneMenu('gwarancja', Produkty::TablicaGwarancjaProduktow('-- brak --'), ((isset($prod['products_warranty_products_id'])) ? $prod['products_warranty_products_id'] : ''), 'style="width:200px" id="gwarancja"'); ?>
            </p>                  

            <p>
              <label for="ilosc">Ilość w magazynie:</label>
              <input type="text" name="ilosc" id="ilosc" size="5" value="<?php echo ((isset($prod['products_quantity'])) ? ((Funkcje::czyNiePuste($prod['products_quantity'])) ? $prod['products_quantity'] : '') : ''); ?>" /><em class="TipIkona"><b>Ilość <?php echo (($zestaw) ? 'zestawów' : 'produktów'); ?> w magazynie <?php echo (($zestaw) ? '' : ', nie należy wypełniać pola jeżeli produkt ma cechy ze stanem magazynowym - wtedy ilość zostanie obliczona na podstawie magazynu cech'); ?></b></em>
            </p> 
            
            <p>
              <label for="ilosc">Alarm magazynowy:</label>
              <input type="text" name="alarm_ilosc" id="alarm_ilosc" size="5" value="<?php echo ((isset($prod['products_quantity_alarm'])) ? ((Funkcje::czyNiePuste($prod['products_quantity_alarm'])) ? $prod['products_quantity_alarm'] : '') : ''); ?>" /><em class="TipIkona"><b>Minimalny stan magazynowy <?php echo (($zestaw) ? 'zestawu' : 'produktu'); ?>, przy którym wyświetlać ostrzeżenie  w magazynie</b></em>
            </p>     

            <p>
              <label for="ilosc">Maksymalny stan magazynowy:</label>
              <input type="text" name="alarm_max_ilosc" id="alarm_max_ilosc" size="5" value="<?php echo ((isset($prod['products_quantity_max_alarm'])) ? ((Funkcje::czyNiePuste($prod['products_quantity_max_alarm'])) ? $prod['products_quantity_max_alarm'] : '') : ''); ?>" /><em class="TipIkona"><b>Maksymalny stan magazynowy <?php echo (($zestaw) ? 'zestawu' : 'produktu'); ?>. Wartość informacyjna dla obsługi sklepu.</b></em>
            </p>              

            <script>
            function SprawdzIlosci(id) {
                $('#OpisIlosci').html('');
                $('#OpisIlosci').hide();
                $.post("ajax/jednostka_miary.php?tok=<?php echo Sesje::Token(); ?>", { id: id }, function(data){ $('#OpisIlosci').html(data); $('#OpisIlosci').slideDown('fast'); });
            };
            </script>                
            
            <p>
              <label for="jednostka_miary">Jednostka miary:</label>
              <?php
              // musi ustalic co ma wyswietlic domyslnie
              //
              $tablica = array();
              $domyslnaJm = 0;
              $sqle = $db->open_query("select * from products_jm s, products_jm_description sd where s.products_jm_id = sd.products_jm_id and sd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."' order by s.products_jm_default, sd.products_jm_name");  
              while ($jm = $sqle->fetch_assoc()) {
                   $tablica[] = array('id' => $jm['products_jm_id'],
                                      'text' => $jm['products_jm_name']);
                   //
                   if ($jm['products_jm_default'] == 1) {
                       $domyslnaJm = $jm['products_jm_id'];
                   }
              }
              $db->close_query($sqle);

              $wybierz = ((isset($prod['products_jm_id'])) ? $prod['products_jm_id'] : '');
              if (empty($prod['products_jm_id'])) {
                  $wybierz = $domyslnaJm;
              }
              //
              echo Funkcje::RozwijaneMenu('jednostka_miary', $tablica, $wybierz, 'style="width:200px" onchange="SprawdzIlosci(this.value)" id="jednostka_miary"'); 
              //
              unset($jm, $tablica, $domyslnaJm);
              ?>
            </p>    

            <div id="OpisIlosci" class="odlegloscRwdTab"></div>
            
            <script>
            SprawdzIlosci(<?php echo $wybierz; ?>);
            </script>     

            <?php unset($wybierz); ?>
            
            
            <p>
              <label for="wielkosc_produktu">Rozmiar / pojemność:</label>
              <input type="text" name="wielkosc_produktu" id="wielkosc_produktu" class="Waga" size="15" value="<?php echo ((isset($prod['products_size'])) ? ((Funkcje::czyNiePuste($prod['products_size'])) ? (float)$prod['products_size'] : '') : ''); ?>" /><em class="TipIkona"><b>Wielkość / pojemność produktu np 150 ml, 1 mb</b></em>
            </p>

            <p>
              <label>Jednostka rozmiaru:</label>
              <select name="wielkosc_produktu_jm" id="wielkosc_produktu_jm">
                  <option value="" <?php echo (((isset($prod['products_size_type']) && $prod['products_size_type'] == '') || !isset($prod['products_size_type'])) ? 'selected="selected"' : ''); ?>>-- brak --</option>
                  <option value="ml" <?php echo ((isset($prod['products_size_type']) && $prod['products_size_type'] == 'ml') ? 'selected="selected"' : ''); ?>>ml (mililitr)</option>
                  <option value="g" <?php echo ((isset($prod['products_size_type']) && $prod['products_size_type'] == 'g') ? 'selected="selected"' : ''); ?>>g (gram)</option>
                  <option value="cb" <?php echo ((isset($prod['products_size_type']) && $prod['products_size_type'] == 'cb') ? 'selected="selected"' : ''); ?>>cb (centymetr bieżący)</option>
                  <option value="c2" <?php echo ((isset($prod['products_size_type']) && $prod['products_size_type'] == 'c2') ? 'selected="selected"' : ''); ?>>c2 (centymetr kwadratowy)</option>
                  <option value="c3" <?php echo ((isset($prod['products_size_type']) && $prod['products_size_type'] == 'c3') ? 'selected="selected"' : ''); ?>>c3 (centymetr sześcienny)</option>
                  <option value="szt" <?php echo ((isset($prod['products_size_type']) && $prod['products_size_type'] == 'szt') ? 'selected="selected"' : ''); ?>>szt (sztuk)</option>
                  <option value="pkg" <?php echo ((isset($prod['products_size_type']) && $prod['products_size_type'] == 'pkg') ? 'selected="selected"' : ''); ?>>pkg (paczka)</option>
                  <option value="tbl" <?php echo ((isset($prod['products_size_type']) && $prod['products_size_type'] == 'tbl') ? 'selected="selected"' : ''); ?>>tabletka</option>

              </select>
            </p>            
            
            <hr style="color:#82b4cd;border-top:1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:70%;margin:15px 10px 10px 25px;<?php echo (($zestaw) ? 'display:none' : ''); ?>" />

            <p <?php echo (($zestaw) ? 'style="display:none"' : ''); ?>>
              <label for="min_ilosc">Minimalna ilość zakupu:</label>
              <input type="text" name="min_ilosc" id="min_ilosc" size="8" value="<?php echo ((isset($prod['products_minorder'])) ? ((Funkcje::czyNiePuste($prod['products_minorder'])) ? $prod['products_minorder'] : '') : ''); ?>" /><em class="TipIkona"><b>Ilość produktów jaką klient może zakupić minimalnie</b></em>
            </p> 
            
            <p <?php echo (($zestaw) ? 'style="display:none"' : ''); ?>>
              <label>Inna ilość w wybranym okresie:</label>
              <input type="radio" name="min_ilosc_czas_wybor" onclick="$('#min_ilosc_czas').slideDown()" id="min_ilosc_czas_tak" value="1" <?php echo ((isset($prod['products_minorder_time']) && (float)$prod['products_minorder_time'] > 0) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="min_ilosc_czas_tak">tak</label>
              <input type="radio" name="min_ilosc_czas_wybor" onclick="$('#min_ilosc_czas').slideUp()" id="min_ilosc_czas_nie" value="0" <?php echo (((isset($prod['products_minorder_time']) && (float)$prod['products_minorder_time'] == 0) || !isset($prod['products_minorder_time'])) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="min_ilosc_czas_nie">nie</label>
            </p>              
            
            <div id="min_ilosc_czas" <?php echo (((float)$prod['products_minorder_time'] > 0) ? '' : 'style="display:none"'); ?>>
                            
                <div class="maleInfo" style="margin-left:25px">Dane zostaną zapisane jeżeli będzie podana ilość oraz data od-do</div>
                
                <p <?php echo (($zestaw) ? 'style="display:none"' : ''); ?>>
                  <label for="min_ilosc">Minimalna ilość zakupu w wybranym okresie:</label>
                  <input type="text" name="min_ilosc_czas" id="min_ilosc_czas" size="8" value="<?php echo ((isset($prod['products_minorder_time'])) ? ((Funkcje::czyNiePuste($prod['products_minorder_time'])) ? $prod['products_minorder_time'] : '') : ''); ?>" /><em class="TipIkona"><b>Ilość produktów jaką klient może zakupić minimalnie w określonym okresie czasowym</b></em>
                </p>             
                
                <p <?php echo (($zestaw) ? 'style="display:none"' : ''); ?>>
                    <label for="data_min_ilosc_od">Od kiedy inna ilość ?</label>
                    <input type="text" id="data_min_ilosc_od" name="data_min_ilosc_od" value="<?php echo ((isset($prod['products_minorder_date']) && Funkcje::czyNiePuste($prod['products_minorder_date'])) ? date('d-m-Y H:i',FunkcjeWlasnePHP::my_strtotime($prod['products_minorder_date'])) : ''); ?>" size="20" class="datepickerPelny" />
                </p> 
                
                <p <?php echo (($zestaw) ? 'style="display:none"' : ''); ?>>
                    <label for="data_min_ilosc_do">Do kiedy inna ilość ?</label>
                    <input type="text" id="data_min_ilosc_do" name="data_min_ilosc_do" value="<?php echo ((isset($prod['products_minorder_date_end']) && Funkcje::czyNiePuste($prod['products_minorder_date_end'])) ? date('d-m-Y H:i',FunkcjeWlasnePHP::my_strtotime($prod['products_minorder_date_end'])) : ''); ?>" size="20" class="datepickerPelny" />
                </p> 

            </div>
            
            <hr style="color:#82b4cd;border-top:1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:70%;margin:15px 10px 10px 25px;<?php echo (($zestaw) ? 'display:none' : ''); ?>" />

            <p <?php echo (($zestaw) ? 'style="display:none"' : ''); ?>>
              <label for="max_ilosc">Maksymalna ilość zakupu:</label>
              <input type="text" name="max_ilosc" id="max_ilosc" size="8" value="<?php echo ((isset($prod['products_maxorder'])) ? ((Funkcje::czyNiePuste($prod['products_maxorder'])) ? $prod['products_maxorder'] : '') : ''); ?>" /><em class="TipIkona"><b>Ilość produktów jaką klient może zakupić maksymalnie</b></em>
            </p>   
            
            <p <?php echo (($zestaw) ? 'style="display:none"' : ''); ?>>
              <label>Inna ilość w wybranym okresie:</label>
              <input type="radio" name="max_ilosc_czas_wybor" onclick="$('#max_ilosc_czas').slideDown()" id="max_ilosc_czas_tak" value="1" <?php echo ((isset($prod['products_maxorder_time']) && (float)$prod['products_maxorder_time'] > 0) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="max_ilosc_czas_tak">tak</label>
              <input type="radio" name="max_ilosc_czas_wybor" onclick="$('#max_ilosc_czas').slideUp()" id="max_ilosc_czas_nie" value="0" <?php echo (((isset($prod['products_maxorder_time']) && (float)$prod['products_maxorder_time'] == 0) || !isset($prod['products_maxorder_time'])) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="max_ilosc_czas_nie">nie</label>
            </p>              
            
            <div id="max_ilosc_czas" <?php echo (((float)$prod['products_maxorder_time'] > 0) ? '' : 'style="display:none"'); ?>>
                            
                <div class="maleInfo" style="margin-left:25px">Dane zostaną zapisane jeżeli będzie podana ilość oraz data od-do</div>
                
                <p <?php echo (($zestaw) ? 'style="display:none"' : ''); ?>>
                  <label for="max_ilosc">Maksymalna ilość zakupu w wybranym okresie:</label>
                  <input type="text" name="max_ilosc_czas" id="max_ilosc_czas" size="8" value="<?php echo ((isset($prod['products_maxorder_time'])) ? ((Funkcje::czyNiePuste($prod['products_maxorder_time'])) ? $prod['products_maxorder_time'] : '') : ''); ?>" /><em class="TipIkona"><b>Ilość produktów jaką klient może zakupić maksymalnie w określonym okresie czasowym</b></em>
                </p>             
                
                <p <?php echo (($zestaw) ? 'style="display:none"' : ''); ?>>
                    <label for="data_max_ilosc_od">Od kiedy inna ilość ?</label>
                    <input type="text" id="data_max_ilosc_od" name="data_max_ilosc_od" value="<?php echo ((isset($prod['products_maxorder_date']) && Funkcje::czyNiePuste($prod['products_maxorder_date'])) ? date('d-m-Y H:i',FunkcjeWlasnePHP::my_strtotime($prod['products_maxorder_date'])) : ''); ?>" size="20" class="datepickerPelny" />
                </p> 
                
                <p <?php echo (($zestaw) ? 'style="display:none"' : ''); ?>>
                    <label for="data_max_ilosc_do">Do kiedy inna ilość ?</label>
                    <input type="text" id="data_max_ilosc_do" name="data_max_ilosc_do" value="<?php echo ((isset($prod['products_maxorder_date_end']) && Funkcje::czyNiePuste($prod['products_maxorder_date_end'])) ? date('d-m-Y H:i',FunkcjeWlasnePHP::my_strtotime($prod['products_maxorder_date_end'])) : ''); ?>" size="20" class="datepickerPelny" />
                </p> 
                
            </div>          

            <hr style="color:#82b4cd;border-top:1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:70%;margin:15px 10px 10px 25px;<?php echo (($zestaw) ? 'display:none' : ''); ?>" />
            
            <p <?php echo (($zestaw) ? 'style="display:none"' : ''); ?>>
              <label for="ilosc_zbiorcza">Przyrost ilości:</label>
              <input type="text" name="ilosc_zbiorcza" id="ilosc_zbiorcza" size="8" value="<?php echo ((isset($prod['products_quantity_order'])) ? ((Funkcje::czyNiePuste($prod['products_quantity_order'])) ? $prod['products_quantity_order'] : '') : ''); ?>" /><em class="TipIkona"><b>Klient będzie mogł zakupić tylko wielokrotność tej wartości</b></em>
            </p>                 

            <p>
              <label><?php echo (($zestaw) ? 'Zestaw' : 'Produkt'); ?> gabarytowy:</label>
              <input type="radio" name="gabaryt" id="gabaryt_tak" value="1" <?php echo ((isset($prod['products_pack_type']) && $prod['products_pack_type'] == '1') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="gabaryt_tak">gabaryt</label>
              <input type="radio" name="gabaryt" id="gabaryt_nie" value="0" <?php echo (((isset($prod['products_pack_type']) && $prod['products_pack_type'] == '0') || !isset($prod['products_pack_type'])) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="gabaryt_nie">zwykły</label>
            </p>  
            
            <hr style="color:#82b4cd;border-top:1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:70%;margin:15px 10px 10px 25px;" />
            
            <script>
            $(document).ready(function() {
                $('#klasa_energetyczna').change(function() {
                   if ( $(this).val() != '' ) {
                        $('.KlasyEnergetyczne').stop().slideDown();
                   } else {
                        $('.KlasyEnergetyczne').stop().slideUp();
                   }
                });
            });
            </script>
            
            <p>
              <label for="klasa_energetyczna">Klasa energetyczna produktu:</label>
              <select name="klasa_energetyczna" id="klasa_energetyczna">
                  <option value="" <?php echo (((isset($prod['products_energy']) && $prod['products_energy'] == '') || !isset($prod['products_energy'])) ? 'selected="selected"' : ''); ?>>-- brak --</option>
                  <option value="A+++" <?php echo ((isset($prod['products_energy']) && $prod['products_energy'] == 'A+++') ? 'selected="selected"' : ''); ?>>A+++</option>
                  <option value="A++" <?php echo ((isset($prod['products_energy']) && $prod['products_energy'] == 'A++') ? 'selected="selected"' : ''); ?>>A++</option>
                  <option value="A+" <?php echo ((isset($prod['products_energy']) && $prod['products_energy'] == 'A+') ? 'selected="selected"' : ''); ?>>A+</option>
                  <option value="A" <?php echo ((isset($prod['products_energy']) && $prod['products_energy'] == 'A') ? 'selected="selected"' : ''); ?>>A</option>
                  <option value="B" <?php echo ((isset($prod['products_energy']) && $prod['products_energy'] == 'B') ? 'selected="selected"' : ''); ?>>B</option>
                  <option value="C" <?php echo ((isset($prod['products_energy']) && $prod['products_energy'] == 'C') ? 'selected="selected"' : ''); ?>>C</option>
                  <option value="D" <?php echo ((isset($prod['products_energy']) && $prod['products_energy'] == 'D') ? 'selected="selected"' : ''); ?>>D</option>
                  <option value="E" <?php echo ((isset($prod['products_energy']) && $prod['products_energy'] == 'E') ? 'selected="selected"' : ''); ?>>E</option>
                  <option value="F" <?php echo ((isset($prod['products_energy']) && $prod['products_energy'] == 'F') ? 'selected="selected"' : ''); ?>>F</option>
                  <option value="G" <?php echo ((isset($prod['products_energy']) && $prod['products_energy'] == 'G') ? 'selected="selected"' : ''); ?>>G</option>
              </select>
            </p>   
            
            <div class="KlasyEnergetyczne" <?php echo ((isset($prod['products_energy']) && $prod['products_energy'] != '') ? '' : 'style="display:none"'); ?>>
                
                <p>
                  <label for="klasa_energetyczna_min">Minimalna klasa energetyczna produktu:</label>
                  <select name="klasa_energetyczna_min" id="klasa_energetyczna_min">
                      <option value="" <?php echo (((isset($prod['products_min_energy']) && $prod['products_min_energy'] == '') || !isset($prod['products_min_energy'])) ? 'selected="selected"' : ''); ?>>-- brak --</option>
                      <option value="A+++" <?php echo ((isset($prod['products_min_energy']) && $prod['products_min_energy'] == 'A+++') ? 'selected="selected"' : ''); ?>>A+++</option>
                      <option value="A++" <?php echo ((isset($prod['products_min_energy']) && $prod['products_min_energy'] == 'A++') ? 'selected="selected"' : ''); ?>>A++</option>
                      <option value="A+" <?php echo ((isset($prod['products_min_energy']) && $prod['products_min_energy'] == 'A+') ? 'selected="selected"' : ''); ?>>A+</option>
                      <option value="A" <?php echo ((isset($prod['products_min_energy']) && $prod['products_min_energy'] == 'A') ? 'selected="selected"' : ''); ?>>A</option>
                      <option value="B" <?php echo ((isset($prod['products_min_energy']) && $prod['products_min_energy'] == 'B') ? 'selected="selected"' : ''); ?>>B</option>
                      <option value="C" <?php echo ((isset($prod['products_min_energy']) && $prod['products_min_energy'] == 'C') ? 'selected="selected"' : ''); ?>>C</option>
                      <option value="D" <?php echo ((isset($prod['products_min_energy']) && $prod['products_min_energy'] == 'D') ? 'selected="selected"' : ''); ?>>D</option>
                      <option value="E" <?php echo ((isset($prod['products_min_energy']) && $prod['products_min_energy'] == 'E') ? 'selected="selected"' : ''); ?>>E</option>
                      <option value="F" <?php echo ((isset($prod['products_min_energy']) && $prod['products_min_energy'] == 'F') ? 'selected="selected"' : ''); ?>>F</option>
                      <option value="G" <?php echo ((isset($prod['products_min_energy']) && $prod['products_min_energy'] == 'G') ? 'selected="selected"' : ''); ?>>G</option>
                  </select>
                </p>               
            
                <p>
                  <label for="klasa_energetyczna_max">Maksymalna klasa energetyczna produktu:</label>
                  <select name="klasa_energetyczna_max" id="klasa_energetyczna_max">
                      <option value="" <?php echo (((isset($prod['products_max_energy']) && $prod['products_max_energy'] == '') || !isset($prod['products_max_energy'])) ? 'selected="selected"' : ''); ?>>-- brak --</option>
                      <option value="A+++" <?php echo ((isset($prod['products_max_energy']) && $prod['products_max_energy'] == 'A+++') ? 'selected="selected"' : ''); ?>>A+++</option>
                      <option value="A++" <?php echo ((isset($prod['products_max_energy']) && $prod['products_max_energy'] == 'A++') ? 'selected="selected"' : ''); ?>>A++</option>
                      <option value="A+" <?php echo ((isset($prod['products_max_energy']) && $prod['products_max_energy'] == 'A+') ? 'selected="selected"' : ''); ?>>A+</option>
                      <option value="A" <?php echo ((isset($prod['products_max_energy']) && $prod['products_max_energy'] == 'A') ? 'selected="selected"' : ''); ?>>A</option>
                      <option value="B" <?php echo ((isset($prod['products_max_energy']) && $prod['products_max_energy'] == 'B') ? 'selected="selected"' : ''); ?>>B</option>
                      <option value="C" <?php echo ((isset($prod['products_max_energy']) && $prod['products_max_energy'] == 'C') ? 'selected="selected"' : ''); ?>>C</option>
                      <option value="D" <?php echo ((isset($prod['products_max_energy']) && $prod['products_max_energy'] == 'D') ? 'selected="selected"' : ''); ?>>D</option>
                      <option value="E" <?php echo ((isset($prod['products_max_energy']) && $prod['products_max_energy'] == 'E') ? 'selected="selected"' : ''); ?>>E</option>
                      <option value="F" <?php echo ((isset($prod['products_max_energy']) && $prod['products_max_energy'] == 'F') ? 'selected="selected"' : ''); ?>>F</option>
                      <option value="G" <?php echo ((isset($prod['products_max_energy']) && $prod['products_max_energy'] == 'G') ? 'selected="selected"' : ''); ?>>G</option>
                  </select>
                </p>   
                
                <p>
                  <label for="klasa_energetyczna_etykieta">Plik graficzny etykiety energetycznej:</label>
                  <input type="text" name="klasa_energetyczna_etykieta" size="20" ondblclick="openFileBrowser('klasa_energetyczna_etykieta','','<?php echo KATALOG_ZDJEC; ?>')" id="klasa_energetyczna_etykieta" value="<?php echo ((isset($prod['products_energy_img'])) ? $prod['products_energy_img'] : ''); ?>" autocomplete="off" />
                  <em class="TipIkona"><b>Kliknij dwukrotnie w pole obok żeby otworzyć okno przeglądarki zdjęć</b></em>
                  <span class="usun_plik TipChmurka" data-plik="klasa_energetyczna_etykieta"><b>Kliknij w ikonę żeby usunąć przypisany plik</b></span>
                  <em class="PrzegladarkaZdjec TipChmurka" onclick="openFileBrowser('klasa_energetyczna_etykieta','','<?php echo KATALOG_ZDJEC; ?>')"><b>Kliknij żeby otworzyć okno przeglądarki</b></em>
                </p>                
                
                <p>
                  <label for="klasa_energetyczna_karta">Karta informacyjna PDF:</label>
                  <input type="text" name="klasa_energetyczna_karta" size="20" ondblclick="openFileBrowser('klasa_energetyczna_karta','','<?php echo KATALOG_ZDJEC; ?>')" id="klasa_energetyczna_karta" value="<?php echo ((isset($prod['products_energy_pdf'])) ? $prod['products_energy_pdf'] : ''); ?>" autocomplete="off" />
                  <em class="TipIkona"><b>Kliknij dwukrotnie w pole obok żeby otworzyć okno przeglądarki zdjęć</b></em>
                  <span class="usun_plik TipChmurka" data-plik="klasa_energetyczna_karta"><b>Kliknij w ikonę żeby usunąć przypisany plik</b></span>
                  <em class="PrzegladarkaZdjec TipChmurka" onclick="openFileBrowser('klasa_energetyczna_karta','','<?php echo KATALOG_ZDJEC; ?>')"><b>Kliknij żeby otworzyć okno przeglądarki</b></em>
                </p>   
                
            </div>
            
            <hr style="color:#82b4cd;border-top:1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:70%;margin:15px 10px 10px 25px;" />
            <div class="maleInfo" style="margin:10px 0px 5px 20px;padding-left:22px;">Opcja dostępna tylko dla wysyłek kurierskich i pocztowych</div>

            <p>
              <label>Osobna paczka:</label>
              <input type="radio" name="osobna_paczka" onclick="$('#osobna_paczka').slideDown()" id="osobna_paczka_tak" value="1" <?php echo ((isset($prod['products_separate_package']) && $prod['products_separate_package'] == '1') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="osobna_paczka_tak">tak</label>
              <input type="radio" name="osobna_paczka" onclick="$('#osobna_paczka').slideUp()" id="osobna_paczka_nie" value="0" <?php echo (((isset($prod['products_separate_package']) && $prod['products_separate_package'] == '0') || !isset($prod['products_separate_package'])) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="osobna_paczka_nie">nie</label>
            </p>  
            
            <script>
            function sprIloscMin(wartosc) {
              wart = parseInt(wartosc);
              if ( wart < 1 || isNaN(wart) ) {
                   $('#osobna_paczka_ilosc').val(1);
              }
            }
            </script>
            
            <div id="osobna_paczka" <?php echo ((isset($prod['products_separate_package']) && $prod['products_separate_package'] == '1') || !isset($prod['products_separate_package']) ? '' : 'style="display:none"'); ?>>
                <p>
                  <label>Ile sztuk w paczce:</label>
                  <input type="number" name="osobna_paczka_ilosc" id="osobna_paczka_ilosc" onchange="sprIloscMin(this.value)" min="1" size="5" value="<?php echo ((isset($prod['products_separate_package_quantity']) && (int)$prod['products_separate_package_quantity'] >= 1) ? (int)$prod['products_separate_package_quantity'] : '1'); ?>" />
                </p>
            </div>
            
            <hr style="color:#82b4cd;border-top:1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:70%;margin:15px 10px 10px 25px;" />
            
            <p>
              <label for="koszt_wysylki">Indywidualny koszt wysyłki:</label>
              <input type="text" name="koszt_wysylki" id="koszt_wysylki" size="8" value="<?php echo ((isset($prod['shipping_cost']) && (float)$prod['shipping_cost'] > 0) ? (float)$prod['shipping_cost'] : ''); ?>" /><em class="TipIkona"><b>Indywidualny koszt wysyłki produktu - jeżeli w koszyku jest produkt z indywidualnym kosztem pozostałe metody wysyłki są niedostępne (wartość pusta lub 0 oznacza brak indywidualnego kosztu wysyłki)</b></em>
            </p>
            
            <p>
              <label for="koszt_wysylki_ilosc">Ilość sztuk w paczce:</label>
              <input type="number" name="koszt_wysylki_ilosc" id="koszt_wysylki_ilosc" size="5" min="1" value="<?php echo ((isset($prod['shipping_cost_quantity']) && (float)$prod['shipping_cost_quantity'] > 0) ? (float)$prod['shipping_cost_quantity'] : '1'); ?>" /><em class="TipIkona"><b>Ilość sztuk jaka mieści w jednej paczce (w podanym koszcie wysyłki)</b></em>
            </p>
            
            <p>
              <label for="koszt_wysylki">Koszt pobrania do indywidualnego kosztu wysyłki:</label>
              <input type="text" name="koszt_wysylki_pobranie" id="koszt_wysylki_pobranie" size="8" value="<?php echo ((isset($prod['shipping_cost_delivery']) && (float)$prod['shipping_cost_delivery'] > 0) ? (float)$prod['shipping_cost_delivery'] : ''); ?>" /><em class="TipIkona"><b>Koszt pobrania doliczany do indywidualnego kosztu wysyłki</b></em>
            </p> 

            <hr style="color:#82b4cd;border-top:1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:70%;margin:0px 10px 15px 25px;" />          

            <p>
              <label>Komentarze do <?php echo (($zestaw) ? 'zestawu' : 'produktu'); ?>:</label>
              <input type="radio" name="komentarz" id="komentarz_tak" value="1" <?php echo ((isset($prod['products_comments']) && $prod['products_comments'] == '1') ? 'checked="checked"' : ''); ?> /> <label class="OpisFor" for="komentarz_tak">tak</label>
              <input type="radio" name="komentarz" id="komentarz_nie" value="0" <?php echo (((isset($prod['products_comments']) && $prod['products_comments'] == '0') || !isset($prod['products_comments'])) ? 'checked="checked"' : ''); ?> /> <label class="OpisFor" for="komentarz_nie">nie</label>
            </p>                                    

            <script>                     
            function dodaj_znizke(ilosc_cen) {
                ile_znizek = parseInt($("#ile_znizek").val()) + 1;
                //
                $('#wyniki_znizek').append('<div id="znizka_'+ile_znizek+'" class="Znizka"></div>');                
                //
                $.get('ajax/dodaj_znizke.php', { id: ile_znizek, rodzaj: $('#dodaj_znizke').attr('data-rodzaj'), ilosc_cen: ilosc_cen }, function(data) {
                    $('#znizka_'+ile_znizek).html(data);
                    $("#ile_znizek").val(ile_znizek);                    
                });
            }   

            function zmien_znizke(rodzaj, ilosc_cen) {
                $('#ile_znizek').val(1);
                $('#wyniki_znizek').hide();
                $('#wyniki_znizek').html('');
                //
                $('#dodaj_znizke').attr('data-rodzaj', rodzaj);
                $('#wyniki_znizek').append('<div id="znizka_1" class="Znizka"></div>');
                //
                if ( rodzaj == 'procent' ) {
                     $('#InfoCena').hide();
                     $('#InfoProcent').slideDown();
                  } else {
                     $('#InfoCena').slideDown();
                     $('#InfoProcent').hide();
                  }
                //
                $.get('ajax/dodaj_znizke.php', { id: 1, rodzaj: rodzaj, ilosc_cen: ilosc_cen }, function(data) {
                    $('#znizka_1').html(data);
                    $('#wyniki_znizek').slideDown();
                });                
            }
            </script>        
            
            <?php 
            if ( !isset($prod['products_discount_type']) ) { 
                 //
                 $prod['products_discount_type'] = 'procent';
                 //
            }
            ?>            

            <div id="ZnizkiRamka" <?php echo (($zestaw) ? 'style="display:none"' : ''); ?>> 
                <div id="ZnizkiTytul">Zniżki zależne od ilości produktów w koszyku:</div>

                <p style="padding:5px 0px 0px 0px">
                  <label style="width:auto;padding:0px 10px 0px 0px">Rodzaj zniżki:</label>                  
                  <input type="radio" name="rodzaj_znizki" onclick="zmien_znizke('procent','<?php echo ILOSC_CEN; ?>')" id="rodzaj_znizki_procent" value="procent" <?php echo (($prod['products_discount_type'] == 'procent' || !isset($prod['products_discount_type']) || empty($prod['products_discount_type'])) ? 'checked="checked"' : ''); ?> /> <label class="OpisFor" for="rodzaj_znizki_procent">procentowa</label>
                  <input type="radio" name="rodzaj_znizki" onclick="zmien_znizke('cena','<?php echo ILOSC_CEN; ?>')" id="rodzaj_znizki_cena" value="cena" <?php echo (($prod['products_discount_type'] == 'cena') ? 'checked="checked"' : ''); ?> /> <label class="OpisFor" for="rodzaj_znizki_cena">kwotowa</label>
                </p>        

                <div id="InfoProcent" class="maleInfo" style="margin:0px<?php echo (($prod['products_discount_type'] == 'procent' || !isset($prod['products_discount_type']) || empty($prod['products_discount_type'])) ? '' : ';display:none'); ?>">
                    Dane zostaną zapisane tylko dla pozycji w których będą wypełnione wszystkie pola (od,do oraz wartość procentowa)
                </div>
                
                <div id="InfoCena" class="maleInfo" style="margin:0px<?php echo (($prod['products_discount_type'] == 'cena') ? '' : ';display:none'); ?>">
                    Dane zostaną zapisane tylko dla pozycji w których będą wypełnione wszystkie pola (od,do oraz wszystkie ceny)
                </div>                

                <div id="wyniki_znizek" class="WynikZnizek">
                
                <?php if ( $prod['products_discount_type'] == 'procent' || !isset($prod['products_discount_type']) || empty($prod['products_discount_type']) ) {

                    $ile_juz_bylo_pozycji = 1;
                    if (!empty($prod['products_discount'])) {
                        $znizki_produktow = explode(';', (string)$prod['products_discount']);
                        if (count($znizki_produktow) > 0) {
                            //
                            for ($a = 0, $c = count($znizki_produktow); $a < $c; $a++) {
                                //
                                $podtablica_pozycji = explode(':', (string)$znizki_produktow[$a]);
                                //
                                ?>
                                <div id="znizka_<?php echo $ile_juz_bylo_pozycji; ?>" class="Znizka">
                                    od <input class="kropka" type="text" value="<?php echo number_format($podtablica_pozycji[0], 2, '.', ''); ?>" name="znizki_od_<?php echo $ile_juz_bylo_pozycji; ?>" size="5" /> 
                                    do <input class="kropka" type="text" value="<?php echo number_format($podtablica_pozycji[1], 2, '.', ''); ?>" name="znizki_do_<?php echo $ile_juz_bylo_pozycji; ?>" size="5" /> 
                                    zniżka <input class="kropka" type="text" value="<?php echo number_format($podtablica_pozycji[2], 2, '.', ''); ?>" name="znizki_wart_<?php echo $ile_juz_bylo_pozycji; ?>" size="7" /> %
                                </div>                           
                                <?php
                                unset($podtablica_pozycji);
                                $ile_juz_bylo_pozycji++;
                            }
                            //
                        }
                    }
                    ?>
                    <div id="znizka_<?php echo $ile_juz_bylo_pozycji; ?>" class="Znizka">
                        od <input class="kropka" type="text" value="" name="znizki_od_<?php echo $ile_juz_bylo_pozycji; ?>" size="5" />
                        do <input class="kropka" type="text" value="" name="znizki_do_<?php echo $ile_juz_bylo_pozycji; ?>" size="5" /> 
                        zniżka <input class="kropka" type="text" value="" name="znizki_wart_<?php echo $ile_juz_bylo_pozycji; ?>" size="7" /> %
                    </div>
                    
                <?php } ?>
                
                <?php if ( $prod['products_discount_type'] == 'cena' ) {
                  
                    $ile_juz_bylo_pozycji = 1;
                    if (!empty($prod['products_discount'])) {
                        $znizki_produktow = explode(';', (string)$prod['products_discount']);
                        if (count($znizki_produktow) > 0) {
                            //
                            for ($a = 0, $c = count($znizki_produktow); $a < $c; $a++) {
                                //
                                $podtablica_pozycji = explode(':', (string)$znizki_produktow[$a]);
                                //
                                ?>
                                <div id="znizka_<?php echo $ile_juz_bylo_pozycji; ?>" class="Znizka">
                                    <table class="TbZnizkaCena">
                                      <tr>
                                          <td>od <input class="kropka" type="text" value="<?php echo number_format($podtablica_pozycji[0], 2, '.', ''); ?>" name="znizki_od_<?php echo $ile_juz_bylo_pozycji; ?>" size="5" /></td>
                                          <td>do <input class="kropka" type="text" value="<?php echo number_format($podtablica_pozycji[1], 2, '.', ''); ?>" name="znizki_do_<?php echo $ile_juz_bylo_pozycji; ?>" size="5" /></td>
                                          <td>                                               
                                              <?php for ($x = 1; $x <= ILOSC_CEN; $x++) { ?>
                                                  <div>cena brutto <?php echo ((ILOSC_CEN > 1) ? 'nr ' . $x . '.' : ''); ?> <input class="kropka" type="text" value="<?php echo ((isset($podtablica_pozycji[1 + $x])) ? number_format($podtablica_pozycji[1 + $x], 2, '.', '') : ''); ?>" name="znizki_wart_<?php echo $x . '_' . $ile_juz_bylo_pozycji; ?>" size="9" /> </div>
                                              <?php } ?>
                                          </td>
                                      </tr>
                                    </table>
                                </div>                           
                                <?php
                                unset($podtablica_pozycji);
                                $ile_juz_bylo_pozycji++;
                            }
                            //
                        }
                    }
                    ?>
                    <div id="znizka_<?php echo $ile_juz_bylo_pozycji; ?>" class="Znizka">
                        <table class="TbZnizkaCena">
                          <tr>
                            <td>od <input class="kropka" type="text" value="" name="znizki_od_<?php echo $ile_juz_bylo_pozycji; ?>" size="5" /></td>
                            <td>do <input class="kropka" type="text" value="" name="znizki_do_<?php echo $ile_juz_bylo_pozycji; ?>" size="5" /></td>
                            <td>                                               
                                <?php for ($x = 1; $x <= ILOSC_CEN; $x++) { ?>
                                    <div>cena brutto <?php echo ((ILOSC_CEN > 1) ? 'nr ' . $x . '.' : ''); ?> <input class="kropka" type="text" value="" name="znizki_wart_<?php echo $x . '_' . $ile_juz_bylo_pozycji; ?>" size="9" /> </div>
                                <?php } ?>
                            </td>
                          </tr>
                        </table>                            
                    </div>
                    
                <?php } ?>   

                </div>                
                
                <input value="<?php echo $ile_juz_bylo_pozycji; ?>" type="hidden" name="ile_znizek" id="ile_znizek" />  
                
                <div style="padding-top:15px"><span class="dodaj" id="dodaj_znizke" data-rodzaj="<?php echo (($prod['products_discount_type'] == 'procent' || !isset($prod['products_discount_type']) || empty($prod['products_discount_type'])) ? 'procent' : 'cena'); ?>" onclick="dodaj_znizke('<?php echo ILOSC_CEN; ?>')" style="cursor:pointer">dodaj pozycję</span></div>
                
                <div style="margin:20px 0px 0px 0px">
                
                    <div style="display:flex;align-items:center;margin-bottom:10px">
                        <span class="dat" style="width:100px">Od kiedy obowiązują:</span>
                        <div><input type="text" id="data_znizki_ilosci_od" name="data_znizki_ilosci_od" value="<?php echo ((isset($prod['products_discount_date']) && Funkcje::czyNiePuste($prod['products_discount_date'])) ? date('d-m-Y H:i',FunkcjeWlasnePHP::my_strtotime($prod['products_discount_date'])) : ''); ?>" size="20" class="datepickerPelny" /></div>
                    </div>
                    <div style="display:flex;align-items:center;margin-bottom:10px">
                        <span class="dat" style="width:100px">Do kiedy obowiązują:</span>
                        <div><input type="text" id="data_znizki_ilosci_do" name="data_znizki_ilosci_do" value="<?php echo ((isset($prod['products_discount_date_end']) && Funkcje::czyNiePuste($prod['products_discount_date_end'])) ? date('d-m-Y H:i',FunkcjeWlasnePHP::my_strtotime($prod['products_discount_date_end'])) : ''); ?>" size="20" class="datepickerPelny" /></div>
                    </div>
                    
                </div>

                <div style="margin:20px 0px 0px 0px">
                    
                    <div>Grupy klientów dla jakich mają być dostępne zniżki od ilości:</div>
                    
                    <div style="margin:10px 0px 10px 0px">
                        <?php     
                        $TablicaGrupKlientow = Klienci::ListaGrupKlientow(false);
                        //
                        echo '<input type="checkbox" value="0" name="znizki_grupy_klientow[]" id="znizki_grupy_klientow_0" ' . ((in_array('0', explode(',', ((isset($prod['products_discount_group_id'])) ? (string)$prod['products_discount_group_id'] : '')))) ? 'checked="checked" ' : '') . ' /><label class="OpisFor" for="znizki_grupy_klientow_0">Klienci bez rejestracji konta</label><br />';
                        //
                        foreach ( $TablicaGrupKlientow as $GrupaKlienta ) {
                            echo '<input type="checkbox" value="' . $GrupaKlienta['id'] . '" name="znizki_grupy_klientow[]" id="znizki_grupy_klientow_' . $GrupaKlienta['id'] . '" ' . ((in_array((string)$GrupaKlienta['id'], explode(',', ((isset($prod['products_discount_group_id'])) ? (string)$prod['products_discount_group_id'] : '')))) ? 'checked="checked" ' : '') . ' /><label class="OpisFor" for="znizki_grupy_klientow_' . $GrupaKlienta['id'] . '">' . $GrupaKlienta['text'] . '</label><br />';
                        }              
                        unset($TablicaGrupKlientow);
                        ?>
                    </div>
                    
                    <div class="ostrzezenie">Jeżeli nie zostanie wybrana żadna grupa klientów zniżki będą dostępne dla wszystkich klientów</div>

                </div>                
                
            </div>

            <div class="PozycjeNiewidoczne">Dane produktu niewidoczne dla klientów</div>
            
            <p>
              <label for="nr_kat_klienta">Id zewnętrzne:</label>
              <input type="text" name="nr_kat_klienta" id="nr_kat_klienta" size="30" value="<?php echo ((isset($prod['products_id_private'])) ? $prod['products_id_private'] : ''); ?>" />
            </p>                

            <p>
              <label for="notatki">Notatki <?php echo (($zestaw) ? 'zestawu' : 'produktu'); ?>:</label>              
            </p>      
            
            <div class="NotatkiKont">
              <textarea name="notatki" id="notatki" cols="30" rows="5"><?php echo ((isset($prod['products_adminnotes'])) ? $prod['products_adminnotes'] : ''); ?></textarea>
            </div>
            
            <p>
              <label for="pozycja_magazyn">Lokalizacja w magazynie:</label>
            </p>
            
            <div class="NotatkiKont">
              <textarea rows="1" cols="30" style="width:80%" name="pozycja_magazyn" id="pozycja_magazyn"><?php echo ((isset($prod['location'])) ? ((Funkcje::czyNiePuste($prod['location'])) ? $prod['location'] : '') : ''); ?></textarea><em class="TipIkona"><b>Lokalizacja produktu w magazynie - pole tekstowe maksymalnie 255 znaków.</b></em>
            </div> 
            
            <div class="NrReferencyjne">
            
                <table>
                
                    <tr>  
                        <th>Nr</td>
                        <th>Nr referencyjny</td>
                        <th>Czego dotyczy ?</td>
                    </tr>
                
                    <?php 
                    for ( $r = 1; $r < 6; $r++ ) {
                          //
                          echo '<tr>
                                    <td>' . $r . '</td>
                                    <td><input type="text" name="nr_referencyjny_' . $r . '" value="' . ((isset($prod['products_reference_number_' . $r])) ? $prod['products_reference_number_' . $r] : '') . '" /></td>
                                    <td><input type="text" name="nr_referencyjny_' . $r . '_opis" value="' . ((isset($prod['products_reference_number_' . $r . '_description'])) ? $prod['products_reference_number_' . $r . '_description'] : '') . '" /></td>
                                </tr>';
                          //
                    }
                    ?>
                    
                </table>
            
            </div>

        </div>
            
        <div class="ProduktInfoPrawe">

            <?php
            $vat = Produkty::TablicaStawekVat('', true, true);
            $domyslny_vat = $vat[1];
            
            // jezeli jest edycja produktu to musi zmienic domyslny vat
            if ($id_produktu > 0) {
                //
                $rvat = '';
                if ( isset($prod['products_tax_class_id']) ) {
                     $rvat = $prod['products_tax_class_id'];
                }
                //
                foreach ( $vat[0] as $poz_vat ) {
                    //
                    $tb_tmp = explode('|', (string)$poz_vat['id']);
                    if ( $tb_tmp[1] == $rvat ) {
                         $domyslny_vat = $poz_vat['id'];
                    }
                    //
                }
                //
                unset($poz_vat);
            }                
            ?>
            
            <?php if ($id_produktu > 0) { ?>
            <script>
            function InfoVat(wartosc) {
                var staryVat = '<?php echo $domyslny_vat; ?>';
                if ( wartosc != staryVat ) {
                     $('#InfoCechy').slideDown();
                  } else {
                     $('#InfoCechy').slideUp(); 
                }
            }
            </script>
            <?php } ?>
            
            <div <?php echo (($zestaw) ? 'style="display:none"' : ''); ?>>

                <table class="TabelaCena">
                
                    <tr>
                        <td style="padding-bottom:15px">Stawka VAT:</td>
                        <td style="padding-bottom:15px">
                            <?php echo Funkcje::RozwijaneMenu('vat', $vat[0], $domyslny_vat, ' id="vat" ' . (($id_produktu > 0) ? 'onchange="InfoVat(this.value)"' : '') ); ?>
                            
                            <?php if ($id_produktu > 0) { ?>
                            <div class="maleInfo" id="InfoCechy">Jeżeli produkt ma cechy do których są przypisane wartości kwotowe pamiętaj o przeliczeniu cen cech na nowy podatek</div>
                            <?php } ?>
                        </td>
                    </tr>

                    <tr><td colspan="2" class="TabelaCenaNaglowek" style="padding-bottom:7px">Cena produktu:</td></tr>
     
                </table>

                <?php
                unset($vat, $domyslny_vat);
                ?>
                
                <div class="TabelaCenaRamka">
                
                    <table class="TabelaCena">

                        <tr>
                            <td style="text-align:center"><span style="white-space:nowrap;">Cena nr</span></td>
                            <td style="text-align:center"><span>Cena netto</span></td>
                            <td style="text-align:center"><span>VAT</span></td>
                            <td style="text-align:center"><span>Cena brutto</span></td>
                        </tr>
                        
                        <?php 
                        for ($x = 1; $x <= ILOSC_CEN; $x++) { ?>
                        <tr>
                            <td style="text-align:center"><?php echo $x; ?>.</td>
                            <td><input type="text" class="oblicz" name="cena_<?php echo $x; ?>" size="9" value="<?php echo ((isset($prod['products_price' . (($x > 1) ? '_'.$x : '')])) ? ((Funkcje::czyNiePuste($prod['products_price' . (($x > 1) ? '_'.$x : '')])) ? $prod['products_price' . (($x > 1) ? '_'.$x : '')] : '') : ''); ?>" id="cena_<?php echo $x; ?>" /></td>
                            <td><input type="text" class="kropkaPusta" name="v_at_<?php echo $x; ?>" size="5" value="<?php echo ((isset($prod['products_price' . (($x > 1) ? '_'.$x : '')])) ? ((Funkcje::czyNiePuste($prod['products_price' . (($x > 1) ? '_'.$x : '')])) ? $prod['products_tax' . (($x > 1) ? '_'.$x : '')] : '') : ''); ?>" id="v_at_<?php echo $x; ?>" /></td>
                            <td><input type="text" class="oblicz_brutto min" name="brut_<?php echo $x; ?>" size="9" value="<?php echo ((isset($prod['products_price_tax' . (($x > 1) ? '_'.$x : '')])) ? ((Funkcje::czyNiePuste($prod['products_price_tax' . (($x > 1) ? '_'.$x : '')])) ? $prod['products_price_tax' . (($x > 1) ? '_'.$x : '')] : '') : ''); ?>" id="brut_<?php echo $x; ?>" /></td>
                        </tr>
                        <?php } ?>
                        
                    </table>
                    
                </div>
                
                <table class="TabelaCena">
                    
                    <tr>
                        <td style="padding:8px">
                            <div style="padding-bottom:5px;font-weight:bold">Czy cena <?php echo (($zestaw) ? 'zestawu' : 'produktu'); ?> ma być widoczna dla klientów niezalogowanych ?</div>
                            <input type="radio" name="cena_zalogowanych" id="cena_zalogowanych_tak" value="0" <?php echo (((isset($prod['products_price_login']) && $prod['products_price_login'] == '0') || !isset($prod['products_price_login'])) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="cena_zalogowanych_tak">tak</label>
                            <input type="radio" name="cena_zalogowanych" id="cena_zalogowanych_nie" value="1" <?php echo ((isset($prod['products_price_login']) && $prod['products_price_login'] == '1') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="cena_zalogowanych_nie">nie</label>                                          
                        </td>
                    </tr>      
                    
                </table>                 

                <table class="TabelaCena">
                    
                    <tr>
                        <td style="padding:8px">
                            <div style="padding-bottom:5px;font-weight:bold">Ceny podane w walucie:</div>
                            <?php
                            $sqlWaluta = $db->open_query("select * from currencies");  
                            $tab = array();
                            // jezeli jest edycja produktu
                            if ($id_produktu > 0 && isset($prod['products_currencies_id'])) {
                                $domyslnaWal = $prod['products_currencies_id'];
                              } else {
                                $domyslnaWal = $_SESSION['domyslna_waluta']['id'];
                            }                            
                            while ($cust = $sqlWaluta->fetch_assoc()) {
                                echo '<input type="radio" name="waluta" id="waluta_'.$cust['currencies_id'].'" value="'.$cust['currencies_id'].'" '.(($cust['currencies_id'] == $domyslnaWal) ? 'checked="checked"' : '').' /> <label class="OpisFor" for="waluta_'.$cust['currencies_id'].'">' . $cust['title'] . '</label><br />';
                            }
                            $db->close_query($sqlWaluta); 

                            $NajnizszaCena = $waluty->FormatujCene($prod['products_min_price_30_day'], false, $domyslnaWal, '', '2', $domyslnaWal);
                            
                            unset($domyslnaWal);
                            ?>                                            
                        </td>
                    </tr>      
                    
                </table>  

                <div style="clear:both"></div>
                
                <div class="TabelaCenaNaglowek" style="padding:5px">Cena zakupu produktu:</div>               

                <div class="ModulInfo"> 
                    <div>
                        <span class="dat">Cena zakupu:</span><input type="text" name="cena_zakupu" class="kropkaPusta" value="<?php echo ((isset($prod['products_purchase_price'])) ? ((Funkcje::czyNiePuste($prod['products_purchase_price'])) ? $prod['products_purchase_price'] : '') : ''); ?>" size="20" /><em class="TipIkona"><b>Cena zakupu produktu - nie jest wyświetlana klientom</b></em>
                    </div>                                              
                </div>            
                
            </div>
            
            <?php if ( isset($prod['products_min_price_30_day']) && (float)$prod['products_min_price_30_day'] > 0 ) { ?>
            
                <div class="maleInfo" style="margin:10px 0 0 0">
                    Najniższa cena produktu z ostatnich 30 dni to <b><?php echo $NajnizszaCena; ?></b> z dnia <b><?php echo date('d-m-Y',FunkcjeWlasnePHP::my_strtotime($prod['products_min_price_30_day_date'])); ?></b>
                    <?php
                    if ( isset($prod['products_min_price_30_day_date_created']) && $prod['products_min_price_30_day_date_created'] != '0000-00-00' ) {
                         echo '<br />Data obniżki: <b>' . date('d-m-Y',FunkcjeWlasnePHP::my_strtotime($prod['products_min_price_30_day_date_created'])) . '</b>';
                    }
                    ?>
                </div>
                
                <div style="margin:0 0 15px 0">
                    <a class="usun" style="font-weight:normal" href="produkty/produkty_usun_cene_30_dni.php?id_poz=<?php echo $prod['products_id']; ?>">usuń tą cenę z produktu</a>
                </div>
                
            <?php } ?>

            <div class="TabelaCenaNaglowek" style="padding:5px">Cena katalogowa:</div>               

            <div class="ModulInfo"> 
                <div>
                    <span class="dat">Cena katalogowa:</span><input type="text" name="cena_katalogowa_1" class="kropkaPusta" data-linked="brut_1" value="<?php echo ((isset($prod['products_retail_price'])) ? ((Funkcje::czyNiePuste($prod['products_retail_price'])) ? $prod['products_retail_price'] : '') : ''); ?>" size="20" /><em class="TipIkona"><b>Cena będzie wyświetlana jako cena katalogowa produktu - wyższa niż cena sklepowa</b></em>
                </div>                                            
                
                <?php
                for ($x = 2; $x <= ILOSC_CEN; $x++) { ?>
                    
                <div>
                    <span class="dat">Dla ceny nr <?php echo $x; ?>:</span><input type="text" name="cena_katalogowa_<?php echo $x; ?>" class="kropkaPusta" value="<?php echo ((isset($prod['products_retail_price_' . $x])) ? ((Funkcje::czyNiePuste($prod['products_retail_price_' . $x])) ? $prod['products_retail_price_' . $x] : '') : ''); ?>" size="20" data-linked="brut_<?php echo $x; ?>" /><em class="TipIkona"><b>Cena będzie wyświetlana jako cena katalogowa produktu - wyższa niż cena sklepowa</b></em>
                </div> 

                <?php } ?>     
            </div>
            
            <div class="TabelaCena_naglowek" style="padding:5px"><?php echo (($zestaw) ? 'Zestaw' : 'Produkt'); ?> widoczny w modułach:</div>               

            <div class="ModulInfo">
                <?php
                if ( NOWOSCI_USTAWIENIA == 'automatycznie wg daty dodania' ) {
                ?>
                <span><input type="checkbox" disabled="disabled" name="nowosc" value="1" <?php echo ((isset($prod['new_status']) && $prod['new_status'] == '1') ? 'checked="checked"' : ''); ?> /> <span class="wylaczony"><?php echo (($zestaw) ? 'zestaw' : 'produkt'); ?> jako <b>NOWOŚĆ</b></span></span><em class="TipIkona"><b>Opcja nieaktywna - nowości określane na podstawie daty dodania</b></em>
                <?php } else { ?>
                <input type="checkbox" name="nowosc" id="nowosc_tak" value="1" <?php echo ((isset($prod['new_status']) && $prod['new_status'] == '1') ? 'checked="checked"' : ''); ?> /> <label class="OpisFor" for="nowosc_tak"><?php echo (($zestaw) ? 'zestaw' : 'produkt'); ?> jako <b>NOWOŚĆ</b></label>
                <?php } ?>
            </div>
            
            <div class="ModulInfo"> 
                <input type="checkbox" name="hit" id="hit_tak" value="1" <?php echo ((isset($prod['star_status']) && $prod['star_status'] == '1') ? 'checked="checked"' : ''); ?> /> <label class="OpisFor" for="hit_tak"><?php echo (($zestaw) ? 'zestaw' : 'produkt'); ?> jako <b>NASZ HIT</b></label>
                 
                <div>
                    <span class="dat">Data rozpoczęcia:</span><input type="text" id="data_hit_od" name="data_hit_od" value="<?php echo ((isset($prod['star_date']) && Funkcje::czyNiePuste($prod['star_date'])) ? date('d-m-Y',FunkcjeWlasnePHP::my_strtotime($prod['star_date'])) : ''); ?>" size="20" class="datepicker" />
                </div>
                <div>
                    <span class="dat">Data zakończenia:</span><input type="text" id="data_hit_do" name="data_hit_do" value="<?php echo ((isset($prod['star_date_end']) && Funkcje::czyNiePuste($prod['star_date_end'])) ? date('d-m-Y',FunkcjeWlasnePHP::my_strtotime($prod['star_date_end'])) : ''); ?>" size="20" class="datepicker" />
                </div>
            </div>
            
            <div class="ModulInfo"> 
                <input type="checkbox" name="polecany" id="polecany_tak" value="1" <?php echo ((isset($prod['featured_status']) && $prod['featured_status'] == '1') ? 'checked="checked"' : ''); ?> /> <label class="OpisFor" for="polecany_tak"><?php echo (($zestaw) ? 'zestaw' : 'produkt'); ?> jako <b>POLECANY</b></label>
                 
                <div>
                    <span class="dat">Data rozpoczęcia:</span><input type="text" id="data_polecany_od" name="data_polecany_od" value="<?php echo ((isset($prod['featured_date']) && Funkcje::czyNiePuste($prod['featured_date'])) ? date('d-m-Y',FunkcjeWlasnePHP::my_strtotime($prod['featured_date'])) : ''); ?>" size="20" class="datepicker" />
                </div>
                <div>
                    <span class="dat">Data zakończenia:</span><input type="text" id="data_polecany_do" name="data_polecany_do" value="<?php echo ((isset($prod['featured_date_end']) && Funkcje::czyNiePuste($prod['featured_date_end'])) ? date('d-m-Y',FunkcjeWlasnePHP::my_strtotime($prod['featured_date_end'])) : ''); ?>" size="20" class="datepicker" />
                </div>
            </div>

            <div class="ModulInfo"> 
                <input type="checkbox" name="promocja" id="promocja_tak" value="1" <?php echo ((isset($prod['specials_status']) && $prod['specials_status'] == '1') ? 'checked="checked"' : ''); ?> /> <label class="OpisFor" for="promocja_tak"><?php echo (($zestaw) ? 'zestaw' : 'produkt'); ?> jako <b>PROMOCJA</b></label> <br />
                <input type="checkbox" name="wyprzedaz" id="wyprzedaz_tak" value="1" <?php echo ((isset($prod['sale_status']) && $prod['sale_status'] == '1') ? 'checked="checked"' : ''); ?> /> <label class="OpisFor" for="wyprzedaz_tak"><?php echo (($zestaw) ? 'zestaw' : 'produkt'); ?> jako <b>WYPRZEDAŻ</b></label>
                
                <div>
                    <span class="dat">Cena poprzednia:</span><input type="text" name="cena_poprzednia" class="kropka" id="cena_poprzednia" value="<?php echo ((isset($prod['products_old_price'])) ? ((Funkcje::czyNiePuste($prod['products_old_price'])) ? $prod['products_old_price'] : '') : ''); ?>" size="20" /><em class="TipIkona"><b>Cena będzie wyświetlana jako przekreślona - pole musi być wypełnione żeby produkt wyświetlał się jako promocja</b></em>
                </div>                                            
                
                <?php
                for ($x = 2; $x <= ILOSC_CEN; $x++) { ?>
                    
                <div>
                    <span class="dat">Dla ceny nr <?php echo $x; ?>:</span><input type="text" name="cena_poprzednia_<?php echo $x; ?>" class="kropka" id="cena_poprzednia_<?php echo $x; ?>" value="<?php echo ((isset($prod['products_old_price_' . $x])) ? ((Funkcje::czyNiePuste($prod['products_old_price_' . $x])) ? $prod['products_old_price_' . $x] : '') : ''); ?>" size="20" /><em class="TipIkona"><b>Cena będzie wyświetlana jako przekreślona - pole musi być wypełnione żeby produkt wyświetlał się jako promocja</b></em>
                </div> 

                <?php } ?>
                
                <div class="maleInfo" style="margin:10px 0px 5px 3px;padding-left:22px;">Data rozpoczęcia / zakończenia dotyczy tylko ustawień promocji</div>

                <div>
                    <span class="dat">Data rozpoczęcia:</span><input type="text" id="data_promocja_od" name="data_promocja_od" value="<?php echo ((isset($prod['specials_date']) && Funkcje::czyNiePuste($prod['specials_date'])) ? date('d-m-Y H:i',FunkcjeWlasnePHP::my_strtotime($prod['specials_date'])) : ''); ?>" size="20"  class="datepickerPelny" />
                </div>
                <div style="padding:5px">
                    <span class="dat">Data zakończenia:</span><input type="text" id="data_promocja_do" name="data_promocja_do" value="<?php echo ((isset($prod['specials_date_end']) && Funkcje::czyNiePuste($prod['specials_date_end'])) ? date('d-m-Y H:i',FunkcjeWlasnePHP::my_strtotime($prod['specials_date_end'])) : ''); ?>" size="20" class="datepickerPelny" />
                </div>
            </div> 
            
            <script>
            $(document).ready(function() {
               $('#export_tak').click(function() {
                    if ($(this).prop('checked') ) {
                        $('.ListaPorownywarek').slideDown();                        
                    } else {
                        $('.ListaPorownywarek').slideUp();
                    }
               });
            });
            </script>             
            
            <div class="ModulInfo">
                <input type="checkbox" name="export" id="export_tak" value="1" <?php echo ((isset($prod['export_status']) && $prod['export_status'] == '1') ? 'checked="checked"' : ''); ?> /> <label class="OpisFor" for="export_tak"><?php echo (($zestaw) ? 'zestaw' : 'produkt'); ?> eksportowany do <b>PORÓWNYWAREK</b></label>
            </div>  
            
            <div class="ListaPorownywarek" <?php echo ((isset($prod['export_status']) && $prod['export_status'] == '1') ? '' : ' style="display:none"'); ?>>
            
                <div class="maleInfo" style="margin-left:15px">Jeżeli nie zostanie wybrana żadna porownywarka produkt będzie eksportowany do wszystkich porównywarek</div>
            
                <ul>
                
                <?php
                $idp = explode(',', ((isset($prod['export_id'])) ? (string)$prod['export_id'] : ''));
                //
                $zapytaniePorownywarki = "SELECT * FROM comparisons";
                $sqlPorownywarki = $db->open_query($zapytaniePorownywarki);     
                //
                while ($infp = $sqlPorownywarki->fetch_assoc()) {                
                    //
                    echo '<li><input type="checkbox" name="porownywarka[]" id="porownywarka_' . $infp['comparisons_id'] . '" value="' . $infp['comparisons_id'] . '" ' . ((in_array((string)$infp['comparisons_id'], $idp)) ? 'checked="checked"' : '') . ' /> <label class="OpisFor" for="porownywarka_' . $infp['comparisons_id'] . '">' . $infp['comparisons_name'] . '</label></li>';
                    //
                }
                $db->close_query($sqlPorownywarki);
                unset($zapytaniePorownywarki, $infp, $idp);
                ?>
                
                </ul>
                
                <div class="cl"></div>
            
            </div>

            <div class="ModulInfo">
                <input type="checkbox" name="porownywarki_ceneo_kup_teraz" id="porownywarki_ceneo_kup_teraz_tak" value="1" <?php echo ((isset($prod['export_ceneo_buy_now']) && $prod['export_ceneo_buy_now'] == '1') ? 'checked="checked"' : ''); ?> /> <label class="OpisFor" for="porownywarki_ceneo_kup_teraz_tak"><?php echo (($zestaw) ? 'zestaw' : 'produkt'); ?> <b style="color:#ff0000">oznaczony</b> <b>KUP TERAZ</b> do CENEO<em class="TipIkona"><b>Po zaznaczeniu tej opcji produkt będzie eksportowany do CENEO z opcją kup teraz</b></em></label> <br />
            </div>              

            <div class="ModulInfo">
                <input type="checkbox" name="porownywarki_wykluczony" id="porownywarki_wykluczony_tak" value="1" <?php echo ((isset($prod['export_status_exclude']) && $prod['export_status_exclude'] == '1') ? 'checked="checked"' : ''); ?> /> <label class="OpisFor" for="porownywarki_wykluczony_tak"><?php echo (($zestaw) ? 'zestaw' : 'produkt'); ?> <b style="color:#ff0000">wykluczony</b> z <b>EKSPORTU</b> do porównywarek<em class="TipIkona"><b>Po zaznaczeniu tej opcji produkt nie będzie eksportowany do żadnych porównywarek</b></em></label> <br />
            </div>              
            
            <div class="ModulInfo">
                <input type="checkbox" name="negocjacja" id="negocjacja_tak" value="1" <?php echo ((isset($prod['products_make_an_offer']) && $prod['products_make_an_offer'] == '1') ? 'checked="checked"' : ''); ?> /> <label class="OpisFor" for="negocjacja_tak">pozwalaj na <b>NEGOCJACJĘ CENY</b></label>
            </div> 

            <div class="ModulInfo">
                <input type="checkbox" name="darmowa_dostawa" id="darmowa_dostawa_tak" value="1" <?php echo ((isset($prod['free_shipping_status']) && $prod['free_shipping_status'] == '1') ? 'checked="checked"' : ''); ?> /> <label class="OpisFor" for="darmowa_dostawa_tak"><?php echo (($zestaw) ? 'zestaw' : 'produkt'); ?> objęty <b>DARMOWĄ DOSTAWĄ</b><em class="TipIkona"><b>Dla tego <?php echo (($zestaw) ? 'zestawu' : 'produktu'); ?> koszty wysyłki dla wszystkich dostępnych wysyłek będą wynosiły 0 zł</b></em></label> <br />
                
                <table class="WyborCheckbox">
                    <tr>
                        <td><label><b>DARMOWA DOSTAWA</b> <b style="color:#ff0000">niedostępna</b> dla grup klientów:</label></td>
                        <td>
                            <?php     
                            $TablicaGrupKlientow = Klienci::ListaGrupKlientow(false);
                            //
                            echo '<input type="checkbox" value="0" name="darmowa_wysylka_grupa_klientow[]" id="darmowa_wysylka_grupa_klientow_0" ' . ((in_array('0', explode(',', ((isset($prod['free_shipping_status_customers_group_id'])) ? (string)$prod['free_shipping_status_customers_group_id'] : '')))) ? 'checked="checked" ' : '') . ' /><label class="OpisFor" for="darmowa_wysylka_grupa_klientow_0">Klienci bez rejestracji konta</label><br />';
                            //
                            foreach ( $TablicaGrupKlientow as $GrupaKlienta ) {
                                echo '<input type="checkbox" value="' . $GrupaKlienta['id'] . '" name="darmowa_wysylka_grupa_klientow[]" id="darmowa_wysylka_grupa_klientow_' . $GrupaKlienta['id'] . '" ' . ((in_array((string)$GrupaKlienta['id'], explode(',', ((isset($prod['free_shipping_status_customers_group_id'])) ? (string)$prod['free_shipping_status_customers_group_id'] : '')))) ? 'checked="checked" ' : '') . ' /><label class="OpisFor" for="darmowa_wysylka_grupa_klientow_' . $GrupaKlienta['id'] . '">' . $GrupaKlienta['text'] . '</label><br />';
                            }              
                            unset($TablicaGrupKlientow);
                            ?>
                        </td>
                    </tr>
                </table>                
            </div> 

            <div class="ModulInfo">
                <input type="checkbox" name="darmowa_dostawa_wykluczona" id="darmowa_dostawa_wykluczona_tak" value="1" <?php echo ((isset($prod['free_shipping_excluded']) && $prod['free_shipping_excluded'] == '1') ? 'checked="checked"' : ''); ?> /> <label class="OpisFor" for="darmowa_dostawa_wykluczona_tak"><?php echo (($zestaw) ? 'zestaw' : 'produkt'); ?> <b style="color:#ff0000">wykluczony</b> z DARMOWEJ DOSTAWY<em class="TipIkona"><b>Jeżeli ten produkt zostanie dodany do koszyka - jego wysyłka nie będzie darmowa, niezależnie od innych ustawień wysyłek</b></em></label> <br />
            </div>              
            
            <div class="ModulInfo">
            
                <div class="KosztyPaczkomaty">
                
                    <p>
                      <label style="padding-left:0px">Rozmiar gabarytu (dla paczkomatów i kuriera):</label> <br />
                      <input type="radio" name="inpost_rodzaj_gabarytu" id="inpost_rodzaj_gabarytu_x" value="x" <?php echo (((isset($prod['inpost_size']) && $prod['inpost_size'] == 'x') || (!isset($prod['inpost_size']) || empty($prod['inpost_size']))) ? 'checked="checked"' : ''); ?> /> <label class="OpisFor" for="inpost_rodzaj_gabarytu_x">nieprzypisany</label><br />
                      <input type="radio" name="inpost_rodzaj_gabarytu" id="inpost_rodzaj_gabarytu_a" value="a" <?php echo ((isset($prod['inpost_size']) && $prod['inpost_size'] == 'a') ? 'checked="checked"' : ''); ?> /> <label class="OpisFor" for="inpost_rodzaj_gabarytu_a">gabaryt A</label><br />
                      <input type="radio" name="inpost_rodzaj_gabarytu" id="inpost_rodzaj_gabarytu_b" value="b" <?php echo ((isset($prod['inpost_size']) && $prod['inpost_size'] == 'b') ? 'checked="checked"' : ''); ?> /> <label class="OpisFor" for="inpost_rodzaj_gabarytu_b">gabaryt B</label><br />
                      <input type="radio" name="inpost_rodzaj_gabarytu" id="inpost_rodzaj_gabarytu_c" value="c" <?php echo ((isset($prod['inpost_size']) && $prod['inpost_size'] == 'c') ? 'checked="checked"' : ''); ?> /> <label class="OpisFor" for="inpost_rodzaj_gabarytu_c">gabaryt C</label><br />
                      <input type="radio" name="inpost_rodzaj_gabarytu" id="inpost_rodzaj_gabarytu_d" value="d" <?php echo ((isset($prod['inpost_size']) && $prod['inpost_size'] == 'd') ? 'checked="checked"' : ''); ?> /> <label class="OpisFor" for="inpost_rodzaj_gabarytu_d">gabaryt D (tylko kurier)</label>
                    </p> 

                    <p>
                      <label style="padding-left:0px;width:auto">Ilość sztuk w paczce:</label> 
                      <input type="number" name="inpost_ilosc_paczka" class="calkowita" id="inpost_ilosc_paczka" size="5" min="1" value="<?php echo ((isset($prod['inpost_quantity']) && (int)$prod['inpost_quantity'] > 0) ? (int)$prod['inpost_quantity'] : '1'); ?>" /> 
                    </p>                     
                
                </div>
            
            </div>
            
            <div class="ModulInfo">
                <input type="checkbox" name="odbior_punkt_wykluczony" id="odbior_punkt_wykluczony_tak" value="1" <?php echo ((isset($prod['pickup_excluded']) && $prod['pickup_excluded'] == '1') ? 'checked="checked"' : ''); ?> /> <label class="OpisFor" for="odbior_punkt_wykluczony_tak"><?php echo (($zestaw) ? 'zestaw' : 'produkt'); ?> <b style="color:#ff0000">wykluczony</b> z ODBIORU W PUNKCIE<em class="TipIkona"><b>Dla produktu nie będą dostępne wysyłki odbioru w punkcie jak Paczkomaty, DPD Pickup itd</b></em></label> <br />
            </div>             

            <?php
            unset($tablica, $info);
            ?>

        </div>

    </div>

</div>

<?php } ?>