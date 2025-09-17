<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        $pola = array(array('products_name',$filtr->process($_POST['nazwa'])),
                      array('products_link',$filtr->process($_POST['link'])),
                      array('products_price',(float)$_POST['cena_podstawa']),
                      array('products_price_tax',(float)$_POST['brut_podstawa']),
                      array('products_quantity',(float)$_POST['ilosc']),
                      array('products_model',$filtr->process($_POST['nr_katalogowy'])),
                      array('products_man_code',$filtr->process($_POST['kod_producenta'])),                      
                      array('sort',(int)$_POST['sort']),
                      array('products_description',$filtr->process($_POST['edytor'])));
                      
        if ( (int)$_POST['zdjecie'] == 0 ) {
            //
            $pola[] = array('products_image', '');
            //
          } else {
            //
            if ( $_POST['foto_produktu'] == 'inne_zdjecie' ) {
                 //
                 $pola[] = array('products_image', $filtr->process($_POST['foto_inne']));
                 //
              } else {
                 //
                 $pola[] = array('products_image', $filtr->process($_POST['foto_produktu']));
                 //
            }
            //          
        }
        //	
        $db->update_query('offers_products' , $pola, " id_products_offers = '".(int)$_POST["id"]."'");	
        
        unset($pola);
        
        Funkcje::PrzekierowanieURL('oferty_produkty.php?oferta_id='.(int)$_POST['oferta_id'].'&id_poz='.(int)$_POST["id"]);
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Edycja pozycji</div>
    <div id="cont">
          
          <script>   
          $(document).ready(function(){
          
              $("#poForm").validate({
                rules: {
                  nazwa: {
                    required: true
                  }                    
                },
                messages: {
                  nazwa: {
                    required: "Pole jest wymagane"
                  }                     
                }
              }); 

              $(".oblicz_oferta").change(		
                function () {
                    //
                    // zamiana przecinkow na kropki
                    var cena_netto = $(this).val();
                    cena_netto = cena_netto.replace(',','.');
                    cena_netto_val = cena_netto.replace(',','.');
                    //
                    if (parseFloat(cena_netto) == 0 || isNaN(cena_netto) || cena_netto == '') {
                        var cena_netto = '';           
                      } else {            
                        var cena_netto = format_zl( cena_netto );
                    }
                    
                    $('#cena_podstawa').val( cena_netto );
                    
                    if ( $('#przelicz_cen').prop('checked') && cena_netto != '' ) {
                         //
                         $('#brut_podstawa').val( format_zl(cena_netto_val * ((100 + parseFloat($('#vat').val())) / 100)) );
                         //
                    }

                }
              );   

              $(".oblicz_brutto_oferta").change(		
                function () {
                    //
                    // zamiana przecinkow na kropki
                    var cena_brutto = $(this).val();
                    cena_brutto = cena_brutto.replace(',','.');
                    cena_brutto_val = cena_brutto.replace(',','.');
                    //
                    if (parseFloat(cena_brutto) == 0 || isNaN(cena_brutto) || cena_brutto == '') {
                        var cena_brutto = '';          
                      } else {
                        var cena_brutto = format_zl( cena_brutto );
                    }

                    $('#brut_podstawa').val( cena_brutto );  
                    
                    if ( $('#przelicz_cen').prop('checked') && cena_brutto != '' ) {
                         //
                         $('#cena_podstawa').val( format_zl(cena_brutto_val / ((100 + parseFloat($('#vat').val())) / 100)) );
                         //
                    }                    
                    
                }
              );
              
              $(".oblicz_ilosc").change(		
                function () {
                    //
                    // zamiana przecinkow na kropki
                    var ilosc = $(this).val();
                    ilosc = ilosc.replace(',','.');
                    //
                    if (parseFloat(ilosc) == 0 || isNaN(ilosc) || ilosc == '') {
                        var ilosc = '';          
                    }

                    $('#ilosc').val( ilosc );  
                    
                }
              );              

          });
          </script>   

          <form action="oferty/oferty_produkty_edytuj.php" method="post" id="poForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Edycja danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            if ( !isset($_GET['oferta_id']) ) {
                 $_GET['oferta_id'] = 0;
            }            
            
            $zapytanie = "select * from offers_products where id_products_offers = '" . (int)$_GET['id_poz'] . "' and offers_id = '" . (int)$_GET['oferta_id'] . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
            
                $info = $sql->fetch_assoc();
                
                // vat produktu
                $zapytanie_produkt = "select p.products_tax_class_id, tr.tax_rate from products p, tax_rates tr where p.products_tax_class_id = tr.tax_rates_id and p.products_id = '" . (int)$info['products_id'] . "'";
                $sql_produkt = $db->open_query($zapytanie_produkt);
                //
                $infp = $sql_produkt->fetch_assoc();
                $vat_produktu = $infp['tax_rate'];
                //
                $db->close_query($sql_produkt);
                unset($zapytanie_produkt, $infp);
                ?>
                <div class="pozycja_edytowana">
                
                    <div class="info_content">
                
                    <div>
                        <input type="hidden" name="akcja" value="zapisz" />
                        <input type="hidden" name="id" value="<?php echo (int)$_GET['id_poz']; ?>" />
                        <input type="hidden" name="oferta_id" value="<?php echo (int)$_GET["oferta_id"]; ?>" />
                        <input type="hidden" name="vat" id="vat" value="<?php echo $vat_produktu; ?>" />
                    </div>
                    
                    <p>
                      <label class="required" for="nazwa">Nazwa produktu:</label>
                      <input type="text" name="nazwa" id="nazwa" value="<?php echo $info['products_name']; ?>" size="90" />
                    </p> 

                    <p>
                      <label for="link">Link produktu:</label>
                      <input type="text" name="link" id="link" value="<?php echo $info['products_link']; ?>" size="90" />
                      <em class="TipIkona"><b>Link do produktu w sklepie - pozostawienie pustego pola spowoduje, że link nie będzie generowany w PDF</b></em>
                    </p>    
                    
                    <p>
                      <label>Dołączać zdjęcie produktu ?</label>           
                      <input type="radio" name="zdjecie" value="1" <?php echo (($info['products_image'] != '') ? 'checked="checked"' : ''); ?> id="zdjecie_tak" /> <label class="OpisFor" for="zdjecie_tak">tak</label>
                      <input type="radio" name="zdjecie" value="0" <?php echo (($info['products_image'] == '') ? 'checked="checked"' : ''); ?> id="zdjecie_nie" /> <label class="OpisFor" for="zdjecie_nie">nie</label>
                    </p> 
                    
                    <table style="margin:0px 0px 5px 10px"><tr>
                        
                        <td><label>Zdjęcie z bazy produktu:</label></td>
                        
                        <td>
                    
                            <?php
                            $byloZdjecie = false;
                            
                            // zdjecie glowne produktu
                            $zapytanie_glowne = 'SELECT products_image FROM products WHERE products_id = "' . $info['products_id'] . '"';

                            $sql_glowne = $db->open_query($zapytanie_glowne);
                            $info_glowne = $sql_glowne->fetch_assoc();
                            
                            $db->close_query($sql_glowne);
                            unset($zapytanie_glowne);                             
    
                            $wynikZdjecia = '<table class="WyborFoto"><tr>';
                            
                            // pierwsze zdjecie w dodatkowych zdjeciach - glowne zdjecie produktu
                            
                            $zaznacz = '';
                            if ( $info['products_image'] == $info_glowne['products_image'] ) {
                                 $zaznacz = ' checked="checked"';
                                 $byloZdjecie = true;
                            }
                            
                            $wynikZdjecia .= '<td>' . Funkcje::pokazObrazek($info_glowne['products_image'], '', '50', '50') . '<br /><input type="radio" id="foto_produktu" value="' . $info_glowne['products_image'] . '" name="foto_produktu" ' . $zaznacz . ' /><label class="OpisFor" for="foto_produktu">wybierz</label></td>';
                            
                            unset($zaznacz);
                                
                            // dodatkowe zdjecia produktu
                            $zapytanie_zdjecia = "SELECT * FROM additional_images WHERE products_id = '" . $info['products_id'] . "' order by sort_order";
                            $sql_zdjecia = $db->open_query($zapytanie_zdjecia);    
                            
                            $j = 1;
                            $col = 1;
                            while ( $info_zdjecia = $sql_zdjecia->fetch_assoc() ) {
                            
                                if ( is_file('../' . KATALOG_ZDJEC . '/' . $info_zdjecia['popup_images']) ) {
                                    //
                                    $zaznacz = '';
                                    if ( $info['products_image'] == $info_zdjecia['popup_images'] ) {
                                         $zaznacz = ' checked="checked"';
                                         $byloZdjecie = true;
                                    }
                                    //
                                    $wynikZdjecia .= '<td>' . Funkcje::pokazObrazek($info_zdjecia['popup_images'], '', '50', '50') . '<br /><input type="radio" id="foto_produktu_' . $j . '" value="' . $info_zdjecia['popup_images'] . '" name="foto_produktu" ' . $zaznacz . ' /><label class="OpisFor" for="foto_produktu_' . $j . '">wybierz</label></td>';
                                    $j++;
                                    //
                                    unset($zaznacz);
                                    //
                                }
                                
                                if ( $col == 7 || $col == 14 || $col == 21 ) {
                                    $wynikZdjecia .= '</tr><tr>';
                                }
                              
                            }   

                            $wynikZdjecia .= '</tr></table>';
                            
                            echo $wynikZdjecia;
                            
                            $db->close_query($sql_zdjecia);
                            unset($zapytanie_zdjecia, $info_zdjecia, $info_glowne);    
                            ?>
                            
                        </td>
                        
                    </tr></table>
                    
                    <p>
                      <label for="foto">Zdjęcie spoza bazy:</label>           
                      <input type="text" name="foto_inne" size="95" value="<?php echo (($byloZdjecie == false) ? $info['products_image'] : ''); ?>" class="obrazek" ondblclick="openFileBrowser('foto','','<?php echo KATALOG_ZDJEC; ?>')" id="foto" autocomplete="off" />  
                      <em class="TipIkona"><b>Kliknij dwukrotnie w pole obok żeby otworzyć okno przeglądarki zdjęć</b></em>
                      <span class="usun_zdjecie TipChmurka" data-foto="foto"><b>Kliknij w ikonę żeby usunąć przypisane zdjęcie</b></span>
                      <span class="PrzegladarkaZdjec TipChmurka" onclick="openFileBrowser('foto','','<?php echo KATALOG_ZDJEC; ?>')"><b>Kliknij żeby otworzyć okno przeglądarki zdjęć</b></span>
                    </p>      

                    <div id="divfoto" style="padding-left:10px; display:none">
                      <label>Zdjęcie:</label>
                      <span>
                        <span id="fofoto">
                            <span class="zdjecie_tbl">
                                <img src="obrazki/_loader_small.gif" alt="" />
                            </span>
                        </span>
                        &nbsp; <input type="radio" id="foto_produktu_inne" value="inne_zdjecie" name="foto_produktu" <?php echo (($byloZdjecie == false) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="foto_produktu_inne">wybierz</label>
                      </span>
                      
                      <?php if ($byloZdjecie == false && trim((string)$info['products_image']) != '') { ?>
                      
                      <script>         
                      pokaz_obrazek_ajax('foto', '<?php echo $info['products_image']; ?>')
                      </script>  
                      
                      <?php } ?>                      
                      
                    </div>                        
                    
                    <p>
                      <label for="cena_podstawa">Cena produktu netto:</label>
                      <?php 
                      if ($info['products_price'] == 0) {
                          $info['products_price'] = '';
                      }
                      ?>
                      <input type="text" class="oblicz_oferta" name="cena_podstawa" id="cena_podstawa" value="<?php echo $info['products_price']; ?>" size="10" />
                      <em class="TipIkona"><b>Pozostawienie pustego pola spowoduje, że cena nie będzie generowana w PDF</b></em>
                    </p> 
                    
                    <p>
                      <label for="brut_podstawa">Cena produktu brutto:</label>
                      <?php 
                      if ($info['products_price_tax'] == 0) {
                          $info['products_price_tax'] = '';
                      }
                      ?>                      
                      <input type="text" class="oblicz_brutto_oferta" name="brut_podstawa" id="brut_podstawa" value="<?php echo $info['products_price_tax']; ?>" size="10" />
                      <em class="TipIkona"><b>Pozostawienie pustego pola spowoduje, że cena nie będzie generowana w PDF</b></em>
                    </p> 
                    
                    <p>
                      <label></label>
                      <input type="checkbox" value="1" name="przelicz_ceny" id="przelicz_cen" checked="checked" /><label class="OpisFor" for="przelicz_cen">przeliczaj ceny brutto / netto</label>
                    </p>
                    
                    <p>
                      <label for="ilosc">Ilość produktu:</label>
                      <?php 
                      if ($info['products_quantity'] == 0) {
                          $info['products_quantity'] = '';
                      }
                      ?>                      
                      <input type="text" class="oblicz_ilosc" name="ilosc" id="ilosc" value="<?php echo $info['products_quantity']; ?>" size="5" />
                      <em class="TipIkona"><b>Pozostawienie pustego pola spowoduje, że ilość nie będzie generowana w PDF</b></em>
                    </p>     

                    <p>
                      <label for="ilosc">Nr katalogowy:</label>                    
                      <input type="text" name="nr_katalogowy" value="<?php echo $info['products_model']; ?>" size="30" />
                      <em class="TipIkona"><b>Pozostawienie pustego pola spowoduje, że pole nie będzie generowane w PDF</b></em>
                    </p> 

                    <p>
                      <label for="ilosc">Kod producenta:</label>                    
                      <input type="text" name="kod_producenta" value="<?php echo $info['products_man_code']; ?>" size="30" />
                      <em class="TipIkona"><b>Pozostawienie pustego pola spowoduje, że pole nie będzie generowane w PDF</b></em>
                    </p>
                    
                    <p>
                      <label for="sort">Sortowanie:</label>
                      <input type="text" name="sort" id="sort" value="<?php echo $info['sort']; ?>" size="10" />
                    </p>                    

                    <div class="edytor">
                      <textarea cols="50" rows="30" id="edytor" name="edytor"><?php echo $info['products_description']; ?></textarea>
                    </div>     

                    <script> 
                    ckedit('edytor','99%','500'); 
                    $('#ButZapis').show();
                    </script>                     
                        
                    </div>

                </div>

            <div class="przyciski_dolne">
              <input type="submit" class="przyciskNon" value="Zapisz dane" />
              <button type="button" class="przyciskNon" onclick="cofnij('oferty_produkty','<?php echo Funkcje::Zwroc_Get(array('x','y')); ?>','oferty');">Powrót</button>   
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