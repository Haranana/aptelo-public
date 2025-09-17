<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        if ( isset($_POST['id_produktu_zamowienie']) && is_array($_POST['id_produktu_zamowienie']) && count($_POST['id_produktu_zamowienie']) > 0 ) {
             //
             // data ostatniej zmiany statusu
             $data_statusu = FunkcjeWlasnePHP::my_strtotime('1990-12-31 23:59:59');
             
             $zapytanie_status = "SELECT date_added FROM orders_status_history WHERE orders_id = '" . (int)$_POST["nr_zamowienia"] . "' ORDER BY date_added DESC LIMIT 1";
             $sql_status = $db->open_query($zapytanie_status);    
              
             if ( (int)$db->ile_rekordow($sql_status) > 0 ) {
                  //
                  $infs = $sql_status->fetch_assoc();
                  //
                  $data_statusu = FunkcjeWlasnePHP::my_strtotime($infs['date_added']);
                  //
                  unset($infs);
                  //
             }
             unset($zapytanie_status);  
             $db->close_query($sql_status);  
                  
             //
             $pola = array(
                     array('return_rand_id',$filtr->process($_POST["id_zwrotu"])),
                     array('return_customers_orders_id',(int)$_POST["nr_zamowienia"]),
                     array('return_customers_orders_date_purchased',$filtr->process($_POST["data_zamowienia"])),
                     array('return_customers_id',(int)$_POST["id_klienta"]),
                     array('return_customers_telephone',$filtr->process($_POST["telefon"])),
                     array('return_customers_invoice_number',$filtr->process($_POST["dokument_sprzedazy"])),
                     array('return_adminnotes',$filtr->process($_POST["uwagi"])),
                     array('return_customers_bank',$filtr->process($_POST["nr_banku"])),
                     array('return_value',(float)$_POST["suma_zwrotu"]),
                     array('return_status_id',$filtr->process($_POST["status_id"])),
                     array('return_date_created','now()'),
                     array('return_date_modified','now()'),
                     array('return_date_end',date("Y-m-d H:i:s", time() + (REKLAMACJA_CZAS_ROZPATRZENIA * 86400))),
                     array('return_service',$filtr->process($_POST["opiekun_id"])));
                     
             $db->insert_query('return_list', $pola);	
             $id_dodanej_pozycji = $db->last_id_query();
             
             unset($pola, $data_statusu);
              
             $pola = array(
                     array('return_id',$id_dodanej_pozycji),
                     array('return_status_id',$filtr->process($_POST["status_id"])),
                     array('date_added','now()'),
                     array('comments',$filtr->process($_POST["wiadomosc"])));
  
             $db->insert_query('return_status_history' , $pola);	
             unset($pola);   
 
             foreach ( $_POST['id_produktu_zamowienie'] as $klucz => $produkt ) {
               
                $pola = array(
                        array('return_id',$id_dodanej_pozycji),
                        array('return_products_orders_id',(int)$produkt),
                        array('return_products_shop_id',(int)$_POST['id_produktu_sklep_' . $klucz]),
                        array('return_products_quantity',(float)$_POST['ilosc_' . $klucz]),
                        array('return_products_notes',$filtr->process($_POST['powod_' . $klucz])));
    
                $db->insert_query('return_products' , $pola);	
                unset($pola);   
              
             }

             if (isset($id_dodanej_pozycji) && $id_dodanej_pozycji > 0) {
                 Funkcje::PrzekierowanieURL('zwroty.php?id_poz='.$id_dodanej_pozycji);
             } else {
                Funkcje::PrzekierowanieURL('zwroty.php');
             }
             
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
                  email: {
                    required: "Pole jest wymagane."
                  },                  
                  id_produktow: {
                    required: "Nie został wybrany produkt."
                  }                
                }
              });
              
              <?php if (isset($_GET['id_zam'])) { ?>
              ckedit('wiadomosc','99%','200');     
              <?php } ?>
              
              $('.IloscZwrotu').change(function() {
                                    
                  var rodzaj = $(this).attr('data-calkowita');
                  
                  if ( rodzaj == 'calkowita' ) {
                  
                      var max = parseInt($(this).attr('data-max'));
                      
                      if ( parseInt($(this).val()) > max ) {
                           $(this).val(max);
                      }
                  
                  } else {
                    
                      var max = parseFloat($(this).attr('data-max'));
                      
                      if ( parseFloat($(this).val()) > max ) {
                           $(this).val(max.toFixed(2));
                      }                    
                    
                  }
                  
                  PrzeliczZwrot();
                  
              });
              
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
             
              if ( $('#id_produktu_zamowienie_' + id).prop('checked') == true ) {
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

          <?php $zamowienie_blad = false; ?>
          
          <?php if (!isset($_GET['id_zam'])) { ?>
          
          <form action="zwroty/zwroty_dodaj.php" method="get" id="zwrotForm" class="cmxform">          
          
          <?php } else { ?>
          
          <form action="zwroty/zwroty_dodaj.php" method="post" id="zwrotForm" class="cmxform">          
          
          <?php } ?>

          <div class="poleForm">
            <div class="naglowek">Dodawanie danych</div>
            
            <div class="pozycja_edytowana">
            
                <div class="info_content">
            
                <?php if (!isset($_GET['id_zam'])) { ?>
                
                    <p>
                      <label id="id_zam" class="required" for="id_zam">Nr zamówienia:</label>
                      <input type="text" name="id_zam" id="id_zam" class="calkowita" size="15" value="" /> 
                    </p>
                
                <?php } else { ?>
                
                    <?php
                    $zamowienie = new Zamowienie((int)$_GET['id_zam']);
                    //
                    if ( count($zamowienie->info) > 0 ) { ?>
            
                        <input type="hidden" name="akcja" value="zapisz" />
                        <input type="hidden" name="id_klienta" value="<?php echo $zamowienie->klient['id']; ?>" />
                        
                        <?php
                        $id_zwrotu = Zwroty::UtworzIdZwrotu(13);
                        ?>
                        
                        <input type="hidden" name="id_zwrotu" value="<?php echo $id_zwrotu; ?>" />
                        
                        <p>
                          <label class="required" for="id_tmp">Nr zgłoszenia:</label>
                          <input type="text" name="id_tmp" id="id_tmp" size="25" value="<?php echo $id_zwrotu; ?>" disabled="disabled" />     
                        </p> 
                        
                        <input type="hidden" name="nr_zamowienia" value="<?php echo $zamowienie->info['id_zamowienia']; ?>" />

                        <p>
                          <label class="required" for="nr_zamowienia_tmp">Nr zamówienia:</label>
                          <input type="text" name="nr_zamowienia_tmp" id="nr_zamowienia_tmp" size="15" value="<?php echo $zamowienie->info['id_zamowienia']; ?>" disabled="disabled" />     
                        </p>                        
                        
                        <input type="hidden" name="data_zamowienia" value="<?php echo date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($zamowienie->info['data_zamowienia'])); ?>" />
                        
                        <p>
                          <label class="required" for="data_zamowienia_tmp">Data zamówienia:</label>
                          <input type="text" name="data_zamowienia_tmp" id="data_zamowienia_tmp" size="30" value="<?php echo date('d-m-Y H:i:s', FunkcjeWlasnePHP::my_strtotime($zamowienie->info['data_zamowienia'])); ?>" disabled="disabled" />     
                        </p>    

                        <p>
                          <label for="dokument_sprzedazy">Numer dokumentu sprzedaży:</label>
                          <input type="text" name="dokument_sprzedazy" id="dokument_sprzedazy" size="55" value="" />     
                        </p> 

                        <div class="ListaProduktowZwrot">

                              <div class="ZwrotLabel"><label>Produkty do zwrotu:</label></div>

                              <div class="SameProduktyZwrot">
                              
                                  <?php
                                  foreach ( $zamowienie->produkty as $produkt ) {
                                    
                                      echo '<div class="WyborProduktuZwrot">                                                
                                                <div>
                                                    <input class="ZwrotCheckbox" type="checkbox" data-id="' . $produkt['orders_products_id'] . '" id="id_produktu_zamowienie_' . $produkt['orders_products_id'] . '" onclick="PolaZwrotu(' . $produkt['orders_products_id'] . ')" name="id_produktu_zamowienie[' . $produkt['orders_products_id'] . ']" value="' . $produkt['orders_products_id'] . '" /><label class="OpisForPustyLabel" for="id_produktu_zamowienie_' . $produkt['orders_products_id'] . '"></label>
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
                                                         //
                                                    }                                                   
                                                
                                                echo '</div>
                                                <div class="ZwrotWybrany" id="zwrot_' . $produkt['orders_products_id'] . '">
                                                    <div>
                                                        <div>Ilość do zwrotu:</div>
                                                        <div><input type="text" class="' . (($produkt['wartosc_calkowita'] == true) ? 'calkowita' : 'kropkaPusta') . ' IloscZwrotu" size="8" data-calkowita="' . (($produkt['wartosc_calkowita'] == true) ? 'calkowita' : 'ulamek') . '" data-max="' . $produkt['ilosc'] . '" id="ilosc_' . $produkt['orders_products_id'] . '" name="ilosc_' . $produkt['orders_products_id'] . '" value="" /></div>
                                                        <div><em>kupiona ilość: ' . $produkt['ilosc'] . ', wartość jednostkowa: ' . $waluty->FormatujCene($produkt['cena_koncowa_brutto'], false, $zamowienie->info['waluta']) . '</em></div>
                                                        <input type="hidden" class="CenaZwrotu" data-id="' . $produkt['orders_products_id'] . '" id="cena_' . $produkt['orders_products_id'] . '" value="' . $produkt['cena_koncowa_brutto'] . '" />
                                                    </div>
                                                    <div>
                                                        <div>Powód zwrotu:</div>
                                                        <div><textarea rows="4" cols="50" name="powod_' . $produkt['orders_products_id'] . '" /></textarea></div>
                                                    </div>
                                                </div>
                                            </div>';
                                    
                                  }
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
                          <input type="text" name="suma_zwrotu" id="suma_zwrotu" class="kropkaPusta" size="15" value="" /> <?php echo $symbol; ?>     
                          <div class="maleInfo">Wartość obliczona na podstawie wartości produktów z zamówienia. Może być modyfikowana przez administratora sklepu (np + koszt wysyłki).</div>
                        </p>              

                        <p>
                          <label for="nr_banku">Numer rachunku bankowego do zwrotu:</label>
                          <input type="text" name="nr_banku" id="nr_banku" size="85" value="" />     
                        </p>   

                        <p>
                          <label for="telefon">Numer telefonu:</label>
                          <input type="text" name="telefon" id="telefon" size="20" value="<?php echo $zamowienie->klient['telefon']; ?>" />
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
                          echo Funkcje::RozwijaneMenu('opiekun_id', $lista_uzytkownikow, '', 'style="width:200px;" id="opiekun_id"'); 
                          unset($lista_uzytkownikow);
                          ?>
                        </p>           

                        <p>
                          <label for="uwagi">Uwagi do zwrotu:</label>
                          <textarea type="text" name="uwagi" cols="100" rows="10" id="uwagi"></textarea>
                        </p>                   
                        
                        <p>
                          <label class="required" for="status_id">Status zwrotu:</label>
                          <?php echo Funkcje::RozwijaneMenu('status_id', Zwroty::ListaStatusowZwrotow( false ), '', 'style="width:300px;" id="status_id"'); ?>
                        </p>

                        <p>
                          <label for="wiadomosc">Opis zwrotu:</label>
                          <textarea id="wiadomosc" name="wiadomosc" cols="90" rows="5"></textarea>
                        </p>                          
                        
                    <?php } else { ?>
                    
                        <div class="pozycja_edytowana">Brak zamówienia o podanym numerze</div>
                        
                        <?php $zamowienie_blad = true; ?>
                    
                    <?php } ?>
                
                <?php } ?>
                
                </div>

            </div>
            
            <?php if ($zamowienie_blad == false) { ?>

                <div class="przyciski_dolne">
                  <?php if (!isset($_GET['id_zam'])) { ?>
                  <input type="submit" class="przyciskNon" value="Pobierz dane zamówienia" />
                  <?php } else { ?>
                  <input type="submit" class="przyciskNon" value="Zapisz dane" />
                  <?php } ?>
                  <button type="button" class="przyciskNon" onclick="cofnij('zwroty','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','zwroty');">Powrót</button>   
                </div>  

            <?php } ?>

          </div>                      
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}
