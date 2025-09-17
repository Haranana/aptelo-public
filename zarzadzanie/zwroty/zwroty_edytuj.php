<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        if ( isset($_POST['id_produktu']) && is_array($_POST['id_produktu']) && count($_POST['id_produktu']) > 0 ) {

              $pola = array(
                      array('return_customers_telephone',$filtr->process($_POST["telefon"])),
                      array('return_customers_invoice_number',$filtr->process($_POST["dokument_sprzedazy"])),
                      array('return_adminnotes',$filtr->process($_POST["uwagi"])),
                      array('return_customers_bank',$filtr->process($_POST["nr_banku"])),
                      array('return_value',(float)$_POST["suma_zwrotu"]),
                      array('return_service',$filtr->process($_POST["opiekun_id"])));
                     
              if ( Funkcje::czyNiePuste($_POST['data_rozpatrzenia']) ) {
                   $pola[] = array('return_date_end',date("Y-m-d H:i:s", FunkcjeWlasnePHP::my_strtotime($filtr->process($_POST['data_rozpatrzenia']))));               
              }                     
                     
              $db->update_query('return_list', $pola, " return_id = '".(int)$_POST["id"]."'");	
              unset($pola);
              
              $db->delete_query('return_products', " return_id = '".(int)$_POST["id"]."'");           
              
              foreach ( $_POST['id_produktu'] as $klucz => $produkt ) {
               
                 $pola = array(
                         array('return_id',(int)$_POST["id"]),
                         array('return_products_orders_id',(int)$produkt),
                         array('return_products_shop_id',(int)$_POST['id_produktu_sklep_' . $klucz]),
                         array('return_products_quantity',(float)$_POST['ilosc_' . $klucz]),
                         array('return_products_notes',$filtr->process($_POST['powod_' . $klucz])));
     
                 $db->insert_query('return_products' , $pola);	
                 unset($pola);   
               
              }

              Funkcje::PrzekierowanieURL('zwroty_szczegoly.php?id_poz='.(int)$_POST["id"].'&zakladka='.(int)$_POST["zakladka"]);
             
        } else {
          
              Funkcje::PrzekierowanieURL('zwroty.php');
              
        }
        
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Dodawanie pozycji</div>
    <div id="cont">
          
          <script>
          $(document).ready(function() {
            
              $("#zwrotForm").validate({
                rules: {
                  id_zam: {
                    required: true,
                    range: [1, 1000000],
                    number: true
                  },
                  klient: { required: true },
                  adres: { required: true },
                  email: { required: true },
                  id_produktow: {
                    required: function(element) {
                      if ($("#id_produktow").val() == '') {
                          return true;
                        } else {
                          return false;
                      }
                    }
                  },                  
                },                
                messages: {
                  id_zam: {
                    required: "Pole jest wymagane."
                  },
                  klient: {
                    required: "Pole jest wymagane."
                  },
                  adres: {
                    required: "Pole jest wymagane."
                  },
                  email: {
                    required: "Pole jest wymagane."
                  },                  
                  id_produktow: {
                    required: "Nie został wybrany produkt."
                  }                
                }
              });

              $('.IloscZwrotu').change(function() {
                  
                  var max = parseInt($(this).attr('data-max'));
                  
                  if ( parseInt($(this).val()) > max ) {
                       $(this).val(max);
                  }
                  
                  PrzeliczZwrot();
                  
              });
              
              $('input.datepicker').Zebra_DatePicker({
                 format: 'd-m-Y H:i',
                 inside: false,
                 readonly_element: true,
                 show_clear_date: false
              });               
              
              PrzeliczZwrot();
              
          });
          
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
          
          function PolaZwrotu(id) {
             
              if ( $('#id_produktu_' + id).prop('checked') == true ) {
                   //
                   $('#zwrot_' + id).stop().slideDown();
                   //
              } else {
                   //
                   $('#zwrot_' + id).stop().slideUp();
                   $('#ilosc_' + id).val('');
                   $('#powod_' + id).val('');                   
                   //
              }
               
              PrzeliczZwrot();
             
          }
          
          function PrzeliczZwrot() {
            
              SprawdzProdukt();
            
              var suma = 0;
            
              $('.CenaZwrotu').each(function() {
                
                    atr_id = $(this).attr('data-id');
                
                    wart = parseFloat($(this).val());
                    ile = parseFloat($('#ilosc_' + atr_id).val());
                    
                    if ( !isNaN(ile) ) {
                         suma = suma + (wart * ile);
                    }
                
              });
              
              if ( suma > 0 ) {
                   $('#suma_zwrotu').val( format_zl(suma) );
              } else {
                   $('#suma_zwrotu').val('');
              }
            
          }
          </script>    

          <form action="zwroty/zwroty_edytuj.php" method="post" id="zwrotForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Edycja danych</div>
            
            <?php
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            } 
            if ( !isset($_GET['zakladka']) ) {
                 $_GET['zakladka'] = '0';
            }            
            
            $wyswietl_edycje = false;
            
            $zapytanie = "select * from return_list where return_id = '" . (int)$_GET['id_poz'] . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
            
                $info = $sql->fetch_assoc();
                
                $zamowienie = new Zamowienie((int)$info['return_customers_orders_id']);
                
                if ( count($zamowienie->info) > 0 ) {
                  
                     $wyswietl_edycje = true;
                     
                }
                
            }
            
            $db->close_query($sql);
            
            if ( $wyswietl_edycje == true ) { ?>            
            
                <div class="pozycja_edytowana">
                
                    <div class="info_content">
                
                    <input type="hidden" name="akcja" value="zapisz" />
                    
                    <input type="hidden" name="id" value="<?php echo (int)$_GET['id_poz']; ?>" />
                    <input type="hidden" name="zakladka" value="<?php echo (int)$_GET['zakladka']; ?>" />

                    <p>
                      <label class="required" for="id_tmp">Nr zgłoszenia:</label>
                      <input type="text" name="id_tmp" id="id_tmp" size="25" value="<?php echo $info['return_rand_id']; ?>" disabled="disabled" />     
                    </p> 
                    
                    <p>
                      <label class="required" for="nr_zamowienia_tmp">Nr zamówienia:</label>
                      <input type="text" name="nr_zamowienia_tmp" id="nr_zamowienia_tmp" size="15" value="<?php echo $info['return_customers_orders_id']; ?>" disabled="disabled" />     
                    </p>                        
                    
                    <p>
                      <label class="required" for="data_zamowienia_tmp">Data zamówienia:</label>
                      <input type="text" name="data_zamowienia_tmp" id="data_zamowienia_tmp" size="30" value="<?php echo date('d-m-Y H:i:s', FunkcjeWlasnePHP::my_strtotime($info['return_customers_orders_date_purchased'])); ?>" disabled="disabled" />     
                    </p>    

                    <p>
                      <label for="dokument_sprzedazy">Numer dokumentu sprzedaży:</label>
                      <input type="text" name="dokument_sprzedazy" id="dokument_sprzedazy" size="55" value="<?php echo $info['return_customers_invoice_number']; ?>" />     
                    </p> 

                    <div class="ListaProduktowZwrot">

                          <div class="ZwrotLabel"><label>Produkty do zwrotu:</label></div>

                          <div class="SameProduktyZwrot">
                          
                              <?php
                              $produkty_zwrotu = array();
                              
                              $zapytanie_produkty = "SELECT return_products_quantity, return_products_notes, return_products_orders_id FROM return_products WHERE return_id = '" . $info['return_id'] . "'";
                              $sql_produkty = $db->open_query($zapytanie_produkty);    

                              if ((int)$db->ile_rekordow($sql_produkty) > 0) {

                                  while ($infs = $sql_produkty->fetch_assoc()) {
                                    
                                      $produkty_zwrotu[$infs['return_products_orders_id']] = array('ilosc' => $infs['return_products_quantity'],
                                                                                                   'powod' => $infs['return_products_notes']);
                                    
                                  }
                                  
                              }

                              unset($zapytanie_produkty);  
                              $db->close_query($sql_produkty);   
                  
                              foreach ( $zamowienie->produkty as $produkt ) {
                                
                                  echo '<div class="WyborProduktuZwrot">
                                            <div>
                                                <input class="ZwrotCheckbox" ' . ((isset($produkty_zwrotu[$produkt['orders_products_id']])) ? 'checked="checked"' : '') . ' type="checkbox" data-id="' . $produkt['orders_products_id'] . '" id="id_produktu_' . $produkt['orders_products_id'] . '" onclick="PolaZwrotu(' . $produkt['orders_products_id'] . ')" name="id_produktu[' . $produkt['orders_products_id'] . ']" value="' . $produkt['orders_products_id'] . '" /><label class="OpisForPustyLabel" for="id_produktu_' . $produkt['orders_products_id'] . '"></label>
                                                <input type="hidden" name="id_produktu_sklep_' . $produkt['orders_products_id'] . '" value="' . $produkt['id_produktu'] . '" />
                                            </div>
                                            <div>
                                                <b>' . $produkt['nazwa'] . '</b>';
                                            
                                                if ( isset($produkt['model']) && $produkt['model'] != '' ) {  
                                                     //
                                                     echo '<div class="MaleNrKatalogowy">Nr kat: <b>' . $produkt['model'] . '</b></div>';
                                                     //
                                                }                                              
                                            
                                                if ( isset($produkt['attributes']) && (count($produkt['attributes']) > 0) ) {  
                                                     //
                                                     echo '<div class="ListaCechZwrotu">';
                                                     foreach ($produkt['attributes'] as $cecha ) {
                                                         echo '<div>'.$produkt['attributes'][$cecha['id_cechy']]['cecha'] . ': <b>' . $produkt['attributes'][$cecha['id_cechy']]['wartosc'] . '</b></div>';
                                                     }
                                                     echo '</div>';
                                                }                                                   
                                            
                                            echo '</div>
                                            <div class="ZwrotWybrany" id="zwrot_' . $produkt['orders_products_id'] . '"' . ((isset($produkty_zwrotu[$produkt['orders_products_id']])) ? 'style="display:block"' : '') . '>
                                                <div>
                                                    <div>Ilość do zwrotu:</div>
                                                    <div><input type="text" class="' . (($produkt['wartosc_calkowita'] == true) ? 'calkowita' : 'kropkaPusta') . ' IloscZwrotu" size="8" data-max="' . $produkt['ilosc'] . '" id="ilosc_' . $produkt['orders_products_id'] . '" name="ilosc_' . $produkt['orders_products_id'] . '" value="' . ((isset($produkty_zwrotu[$produkt['orders_products_id']])) ? (($produkt['wartosc_calkowita'] == true) ? (int)$produkty_zwrotu[$produkt['orders_products_id']]['ilosc'] : $produkty_zwrotu[$produkt['orders_products_id']]['ilosc']) : '') . '" /></div>
                                                    <div><em>kupiona ilość: ' . $produkt['ilosc'] . ', wartość jednostkowa: ' . $waluty->FormatujCene($produkt['cena_koncowa_brutto'], false, $zamowienie->info['waluta']) . '</em></div>
                                                    <input type="hidden" class="CenaZwrotu" data-id="' . $produkt['orders_products_id'] . '" id="cena_' . $produkt['orders_products_id'] . '" value="' . $produkt['cena_koncowa_brutto'] . '" />
                                                </div>
                                                <div>
                                                    <div>Powód zwrotu:</div>
                                                    <div><textarea rows="4" cols="50" name="powod_' . $produkt['orders_products_id'] . '" />' . ((isset($produkty_zwrotu[$produkt['orders_products_id']])) ? $produkty_zwrotu[$produkt['orders_products_id']]['powod'] : '') . '</textarea></div>
                                                </div>
                                            </div>
                                        </div>';
                                
                              }
                              
                              unset($produkty_zwrotu);
                              ?>
                          
                          </div>
                          
                    </div>
                    
                    <p>
                      <input type="hidden" name="id_produktow" id="id_produktow" value="" />
                    </p>                                    
                    
                    <p>
                      <label for="suma_zwrotu"><b style="color:#ff0000">Suma zwrotu wg cen produktów:</b></label>
                      <?php
                      $symbol = '';
                      //
                      $zapytanie_symbol = "select symbol from currencies where code = '" . $zamowienie->info['waluta'] . "'";
                      $sql_symbol = $db->open_query($zapytanie_symbol);
                      //
                      if ((int)$db->ile_rekordow($sql_symbol) > 0) {
                          //
                          $wynik = $sql_symbol->fetch_assoc();
                          $symbol = $wynik['symbol'];
                          unset($wynik);
                          //
                      }
                      //
                      $db->close_query($sql_symbol);                          
                      unset($zapytanie_symbol);
                      ?>
                      <input type="text" name="suma_zwrotu" id="suma_zwrotu" class="kropkaPusta" size="15" value="<?php echo $info['return_value']; ?>" /> <?php echo $symbol; ?>     
                      <div class="maleInfo">Wartość obliczona na podstawie wartości produktów z zamówienia. Może być modyfikowana przez administratora sklepu (np + koszt wysyłki).</div>
                    </p>              

                    <p>
                      <label for="nr_banku">Numer rachunku bankowego do zwrotu:</label>
                      <input type="text" name="nr_banku" id="nr_banku" size="85" value="<?php echo $info['return_customers_bank']; ?>" />     
                    </p>   

                    <p>
                      <label for="telefon">Numer telefonu:</label>
                      <input type="text" name="telefon" id="telefon" size="20" value="<?php echo $info['return_customers_telephone']; ?>" />
                    </p>   

                    <p>
                      <label for="opiekun_id">Opiekun zwrotu:</label>
                      <?php
                      // pobieranie informacji od uzytkownikach
                      $lista_uzytkownikow = Array();
                      $zapytanie_uzytkownicy = "select distinct * from admin order by admin_lastname, admin_firstname";
                      $sql_uzytkownicy = $db->open_query($zapytanie_uzytkownicy);
                      //
                      $lista_uzytkownikow[] = array('id' => 0, 'text' => 'Nie przypisany ...');
                      //
                      while ($uzytkownicy = $sql_uzytkownicy->fetch_assoc()) {
                        $lista_uzytkownikow[] = array('id' => $uzytkownicy['admin_id'], 'text' => $uzytkownicy['admin_firstname'] . ' ' . $uzytkownicy['admin_lastname']);
                      }
                      $db->close_query($sql_uzytkownicy); 
                      unset($zapytanie_uzytkownicy, $uzytkownicy);    
                      //                                   
                      echo Funkcje::RozwijaneMenu('opiekun_id', $lista_uzytkownikow, $info['return_service'], 'style="width:200px;" id="opiekun_id"'); 
                      unset($lista_uzytkownikow);
                      ?>
                    </p>        

                    <p>
                      <label for="data_promocja_od">Data rozpatrzenia:</label>
                      <input type="text" id="data_rozpatrzenia" name="data_rozpatrzenia" value="<?php echo ((Funkcje::czyNiePuste($info['return_date_end'])) ? date('d-m-Y H:i',FunkcjeWlasnePHP::my_strtotime($info['return_date_end'])) : ''); ?>" size="20"  class="datepicker" />
                    </p>                       

                    <p>
                      <label for="uwagi">Uwagi zwrotu:</label>
                      <textarea type="text" name="uwagi" cols="100" rows="10" id="uwagi"><?php echo $info['return_adminnotes']; ?></textarea>
                    </p>                   

                    </div>

                </div>
                
                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Zapisz dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('zwroty','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','zwroty');">Powrót</button>   
                </div>

                <?php
            
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
