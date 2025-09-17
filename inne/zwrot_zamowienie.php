<?php
chdir('../'); 

if (isset($_POST['data']) && !empty($_POST['data'])) {

    // wczytanie ustawien inicjujacych system
    require_once('ustawienia/init.php');
    
    // rozdziela serializowane dane z ajaxa na tablice POST
    parse_str($_POST['data'], $PostTablica);
    unset($_POST['data']);
    $_POST = $PostTablica;

    if ( isset($_POST['zamowienie_id']) && (int)$_POST['zamowienie_id'] > 0 && isset($_POST['id']) && (int)$_POST['id'] > 0 && Sesje::TokenSpr()) {
         //
         $zamowienie = new Zamowienie((int)$_POST['zamowienie_id']);
         //
         if ( count($zamowienie->info) > 0 ) {
           
              if ( (int)$zamowienie->klient['id'] == (int)$_POST['id'] ) {
                
                  $GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('KLIENCI','REKLAMACJE') ), $GLOBALS['tlumacz'] );
           
                  echo '<p class="PozycjaZwrotu">
                           <span><label class="formSpan" for="telefon_klienta">' . $GLOBALS['tlumacz']['NUMER_TELEFONU_KLIENTA'] . ':</label></span>
                           <input type="text" name="telefon" id="telefon_klienta" size="20" value="' . $zamowienie->klient['telefon'] . '" />
                           <input type="hidden" name="data_zamowienia" value="' . $zamowienie->info['data_zamowienia'] . '" />
                        </p>';
                        
                  echo '<div style="font-size:120%;font-weight:bold;padding:10px 0 20px 0" class="PozycjaZwrotu">' . $GLOBALS['tlumacz']['PRODUKTY_DO_ZWROTU'] . '</div>';
                  
                  $ilosc_produktow = 0;
                        
                  foreach ( $zamowienie->produkty as $produkt ) {
                                        
                      // rodzaj produktu
                      
                      $wyswietl = true;
                      
                      if ( $produkt['rodzaj_produktu'] != 'standard' ) {
                      
                          if ( ZWROTY_RODZAJ_PRODUKTU == 'tylko standardowe' ) {
                               $wyswietl = false;
                          }

                      }
                            
                      if ( $wyswietl == true ) {

                           $cechy = array();
                           $cechy_txt = '';
                           
                           if ( isset($produkt['attributes']) && (count($produkt['attributes']) > 0) ) {  
                                //
                                foreach ($produkt['attributes'] as $cecha ) {
                                    $cechy[] = $produkt['attributes'][$cecha['id_cechy']]['cecha'] . ': ' . $produkt['attributes'][$cecha['id_cechy']]['wartosc'];
                                }
                                //
                           }      

                           if ( count($cechy) > 0 ) {
                                //
                                $cechy_txt = ' (' . implode(', ', $cechy) . ')';
                                //
                           }
                                   
                           echo '<p>
                                   <label for="produkt_' . $produkt['orders_products_id'] . '">' . $produkt['nazwa'] . $cechy_txt . '
                                      <input type="checkbox" class="ZwrotCheckbox" value="' . $produkt['orders_products_id'] . '" data-id="' . $produkt['orders_products_id'] . '" name="produkty[' . $produkt['orders_products_id'] . ']" id="produkt_' . $produkt['orders_products_id'] . '" />
                                      <span class="check" id="produkt_' . $produkt['orders_products_id'] . '"></span>
                                      <input type="hidden" name="id_produktu_sklep_' . $produkt['orders_products_id'] . '" value="' . $produkt['id_produktu'] . '" />
                                   </label>';

                                   echo '<p id="zwrot_' . $produkt['orders_products_id'] . '" style="display:none">
                                             <span><label class="formSpan" for="powod_' . $produkt['orders_products_id'] . '">' . $GLOBALS['tlumacz']['POWOD_ZWROTU'] . ':</label></span>
                                             <textarea rows="2" cols="50" name="powod_' . $produkt['orders_products_id'] . '" id="powod_' . $produkt['orders_products_id'] . '"></textarea>
                                             <br /><br />
                                             <span><label class="formSpan" for="ilosc_' . $produkt['orders_products_id'] . '">' . $GLOBALS['tlumacz']['ZWROT_ILOSC'] . ':</label></span>
                                             <input type="text" class="IloscZwrotu" size="5" name="ilosc_' . $produkt['orders_products_id'] . '" data-calkowita="' . (($produkt['wartosc_calkowita'] == true) ? 'calkowita' : 'ulamek') . '" data-max="' . $produkt['ilosc'] . '" id="ilosc_' . $produkt['orders_products_id'] . '" value="" />
                                         </p>';
                                   
                           echo '</p>';    
                          
                           $ilosc_produktow++;

                      }                          
                            
                  }
                  
                  if ( $ilosc_produktow  > 0 ) {
                    
                       echo '<p><input type="hidden" name="id_produktow" id="id_produktow" value="" /></p>';
                  
                       ?>
                      
                       <script>
                       function SprawdzProdukt() {
                        
                          var checked = [];
                           $("input.ZwrotCheckbox:checked").each( function() {
                               var idt = $(this).attr('data-id');
                               if ( parseFloat($('#ilosc_' + idt).val()) > 0 ) {
                                    checked.push(parseInt($(this).val()));
                               }
                           });
                           if ( checked.length == 0 ) {
                                $('#id_produktow').val('');
                           } else {
                                $('#id_produktow').val('x');
                           }
                         
                       }
               
                       $(document).ready(function() {
                          //
                          if ( $('.KontenerInpostZwroty').length ) {
                               //
                               $('.KontenerInpostZwroty').show();
                               //
                          }
                          //
                          $('.ZwrotCheckbox').click(function() {
                              //
                              var itr = $(this).attr('data-id');
                              //
                              if ( $(this).prop('checked') == true ) {
                                   //
                                   $('#zwrot_' + itr).stop().slideDown();
                                   //
                              } else {
                                   //
                                   $('#zwrot_' + itr).stop().slideUp();
                                   $('#ilosc_' + itr).val('');
                                   //
                              }  
                              //     
                              SprawdzProdukt();
                              //
                          });
                          //
                          $('.IloscZwrotu').change(function() {
                              //
                              var rodzaj = $(this).attr('data-calkowita');
                              //
                              if ( rodzaj == 'calkowita' ) {
                                   //
                                   var max = parseInt($(this).attr('data-max'));
                                   //
                                   if (isNaN($(this).val())) {
                                       $(this).val('');
                                      } else {
                                       if ( isNaN(parseInt($(this).val())) ) {
                                           $(this).val('');
                                         } else {
                                          $(this).val( parseInt($(this).val()) );
                                       }
                                   }                                
                                   //
                                   if ( parseInt($(this).val()) > max ) {
                                        $(this).val(max);
                                   }
                                   //
                              } else {
                                   //
                                   var max = parseFloat($(this).attr('data-max'));
                                   //
                                   var wart = $(this).val();
                                   regexp = eval("/,/g");
                                   wart =  wart.replace(regexp,".");                               
                                   //
                                   if (isNaN(wart)) {
                                       $(this).val('');
                                      } else {
                                       if ( isNaN(parseFloat(wart)) ) {
                                           $(this).val('');
                                         } else {
                                           licz = parseFloat(wart);
                                           $(this).val( licz.toFixed(2) );
                                       }
                                   }                                
                                   //
                                   if ( parseFloat(wart) > max ) {
                                        $(this).val(max.toFixed(2));
                                   }                    
                                   //
                              }
                              //
                              SprawdzProdukt();
                              //
                          });                      
                          //
                       });                  
                       </script>
                      
                       <?php
                       
                  } else { 
                  
                       echo '<div class="Informacja" style="margin-bottom:15px">' . $GLOBALS['tlumacz']['BRAK_PRODUKTOW_DO_ZWROTU'] . '</div>';
                       
                       ?>
                       
                       <script>
                       $(document).ready(function() {                       
                       
                          if ( $('.KontenerInpostZwroty').length ) {
                               //
                               $('.KontenerInpostZwroty').hide();
                               //
                          }
                       
                       });
                       </script>
                       
                       <?php
                  
                  }
                        
              }
           
         }
         //
    }
    
}

?>