<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {
  
    $wynik = '';
    
    if ( Funkcje::SprawdzAktywneAllegro() ) {
      
        $AllegroRest = new AllegroRest( array('allegro_user' => $_SESSION['domyslny_uzytkownik_allegro']) );

        if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

            $allegro = array();
            $allegro['benefits'] = array(array( 'specification' => array( 'type' => 'ORDER_FIXED_DISCOUNT', 'value' => array( 'amount' => (($_POST['rabat_rodzaj'] == '1') ? (float)$_POST["rabat"] : 0), 'currency' => "PLN" ))));
            
            $aukcje = array();
            
            if ( isset($_POST['id_aukcji']) && count($_POST['id_aukcji']) > 0 ) {
                 //
                 foreach ( $_POST['id_aukcji'] as $aukcja ) {
                    //
                    $aukcje[] = array( 'id' => $filtr->process($aukcja), 'quantity' => ((isset($_POST['ilosc_' . $aukcja])) ? (int)$_POST['ilosc_' . $aukcja] : 1), 'promotionEntryPoint' => ((isset($_POST['pokaz_' . $aukcja])) ? 'true' : 'false') );
                    //
                 }
                 //
            }
            
            $allegro['offerCriteria'] = array(array( 'type' => 'CONTAINS_OFFERS', 'offers' => $aukcje ) );
            
            $PrzetwarzanaAukcja = $AllegroRest->commandPost('sale/loyalty/promotions', $allegro );

            if ( isset($PrzetwarzanaAukcja->errors) ) {
                 //
                 $wynik = '<div style="margin:10px"><div class="ostrzezenie" style="margin-bottom:15px;display:block">Wystąpił błąd podczas tworzenia zestawu !</div>';
                 //
                 $wynik .= '<b style="margin-bottom:5px;display:block">Informacje jakie zwrócił portal Allegro:</b>';
                 //
                 foreach ( $PrzetwarzanaAukcja->errors as $blad ) {
                     //
                     $wynik .= '<div>' . $blad->code . ' ' . $blad->userMessage . '</div>';
                     //
                 }        
                 //
                 $wynik .= '</div>';
                 //
            } else {
                 //        
                 if ( isset($PrzetwarzanaAukcja->id) ) {
                      //
                      $wynik = '<div id="zaimportowano">Zestaw dla wybranych aukcji został utworzony</div>'; 
                      //
                      foreach ( $_POST['id_aukcji'] as $aukcja ) { 
                          //
                          $pola = array(
                                  array('allegro_benefits_set_auction_id',$aukcja),
                                  array('allegro_benefits_set_auction_quantity',((isset($_POST['ilosc_' . $aukcja])) ? (int)$_POST['ilosc_' . $aukcja] : 1)),
                                  array('allegro_benefits_set_auction_view',((isset($_POST['pokaz_' . $aukcja])) ? 1 : 0)),
                                  array('allegro_benefits_set_amount',(($_POST['rabat_rodzaj'] == '1') ? (float)$_POST["rabat"] : 0)),
                                  array('allegro_benefits_set_id_set',$PrzetwarzanaAukcja->id),
                                  array('allegro_benefits_set_auction_seller',(int)$_SESSION['domyslny_uzytkownik_allegro']));
         
                          $db->insert_query('allegro_benefits_set' , $pola);             
                          unset($pola);                          
                          //
                      }
                      //
                 } else {
                      //
                      $wynik = '<div style="margin:10px"><div class="ostrzezenie" style="margin-bottom:15px;display:block">Wystąpił błąd podczas tworzenia zestawu !</div></div>';
                      //
                 }
                 //
            }
            //	        
        }

        // wczytanie naglowka HTML
        include('naglowek.inc.php');
        ?>
        
        <div id="naglowek_cont">Zestaw produktów</div>
        <div id="cont">
              
              <form action="allegro/allegro_aukcja_zestaw.php" id="zestawForm" method="post" class="cmxform">          

              <div class="poleForm">
                <div class="naglowek">Edycja danych</div>
                
                <?php if ( $wynik == '' ) { ?>

                    <script>
                    $(document).ready(function() {
                      //
                      $('.AnulujWyszukiwanie').hide();
                      //
                      $("#zestawForm").validate({
                        rules: {
                          rabat: { required: function() {var wynik = true; if ( $("input[name='rabat_rodzaj']:checked", "#zestawForm").val() == "0" ) { wynik = false; } return wynik; },
                                   min: function() {var wynik = 1; if ( $("input[name='rabat_rodzaj']:checked", "#zestawForm").val() == "0" ) { wynik = 0; } return wynik; }},
                          "id_aukcji[]": { required: true, minlength: 2, maxlength: 9 }
                        },                    
                        messages: {
                          rabat: {
                            required: "Pole jest wymagane."
                          },
                          "id_aukcji[]": {
                            required: "Nie zostały wybrane aukcje które mają wchodzi w skład zestawu.",
                            minlength: "Minimalna ilość aukcji w zestawie to 2.",
                            maxlength: "Maksymalna ilość aukcji w zestawie to 9.",
                          }
                        }
                      });
                      $('.WyborAukcji').click(function() {
                          //
                          PrzeliczSuma();
                          //
                      });
                      $('.ZmianaIlosci').change(function() {
                          //
                          PrzeliczSuma();
                          //
                      });                            
                      //                  
                      $('#rabat').change(function() {
                          //
                          PrzeliczSuma();
                          //
                      });                 
                      //
                    });

                    function WyswietlAukcje( szukaj ) {
                      //
                      $('.AnulujWyszukiwanie').hide();
                      $('.BrakWynikow').hide();
                      //
                      var fraza = '';
                      if ( szukaj == 'x' ) {
                           //
                           fraza = $('#szukana_aukcja').val();
                           if ( fraza.length < 2 ) {
                                $.colorbox( { html:'<div id="PopUpInfo">Minimalna ilość znaków do wyszukiwania to 2</div>', initialWidth:50, initialHeight:50, maxWidth:'90%', maxHeight:'90%' } );
                                return false;
                           }        
                           //
                           $('.AnulujWyszukiwanie').css({ 'display' : 'inline-block' });
                           //
                           $('.NrAukcji').each(function() {
                              //
                              // sprawdza id aukcji                                   
                              var nr_aukcja = $(this).attr('data-nr');
                              if ( nr_aukcja.indexOf(fraza) == -1 ) {
                                   //
                                   $('#tr_' + nr_aukcja).hide();
                                   //
                              } else {
                                   //
                                   $('#tr_' + nr_aukcja).show();
                                   //
                              }
                              //
                           });
                           //
                           $('.TytulAukcji').each(function() {
                              //
                              // sprawdza nazwe aukcji             
                              var nr_aukcja = $(this).attr('data-nr');
                              var nazwa_aukcja = $(this).html().toLowerCase();
                              if ( nazwa_aukcja.indexOf(fraza.toLowerCase()) > -1 ) {
                                   //
                                   $('#tr_' + nr_aukcja).show();
                                   //
                              }
                              //
                           });    
                           //
                           var ile_widocznych = 0;
                           $('.listing_tbl tr').each(function() {
                              //
                              if ( $(this).css('display') != 'none' ) {
                                   ile_widocznych++;
                              }
                           });
                           //
                           if ( ile_widocznych > 1 ) {
                                $('.BrakWynikow').hide();
                            } else {
                                $('.BrakWynikow').show();
                           }
                           //
                      } else {
                           //
                           $('#WynikPrzewijanyAllegro').find('tr').show();
                           $('.BrakWynikow').hide();
                           $('#szukana_aukcja').val('');
                           //
                      }
                      //
                    }
                    
                    function PrzeliczSuma() {
                        var sum = 0;
                        var ile = 0;
                        $('input[name="id_aukcji[]"]').each(function() {
                            //
                            if ( $(this).prop('checked') == true ) {
                                 //
                                 var id = $(this).val();
                                 var cena_allegro = parseFloat($('#ilosc_' + id).val()) * parseFloat($('#cena_' + id).attr('data-cena'));
                                 sum += cena_allegro;
                                 ile++;
                                 //
                            }
                            //
                        });
                        //
                        var rabat = $('input[name="rabat_rodzaj"]:checked').val();
                        var wartosc_rabat = 0;
                        if ( rabat == '1' && $('#rabat').val() != '' ) {
                             var wartosc_rabat = parseFloat($('#rabat').val());
                        }
                        //
                        if ( sum > 0 ) {
                             //
                             sum_rabat = format_zl(sum);
                             sum = format_zl(sum - wartosc_rabat);                                                    
                             //
                             $('#SumaPrzecena').html(sum.replace('.',',') + ' zł');
                             if ( wartosc_rabat > 0 ) {
                                  $('#SumaCala').html(sum_rabat.replace('.',',') + ' zł').show();
                                  wartosc_rabat = format_zl(wartosc_rabat);
                                  $('#RabatZestaw').html('- ' + wartosc_rabat.replace('.',',') + ' zł').show();
                             } else {
                                  $('#SumaCala').html(sum.replace('.',',') + ' zł').hide();
                                  $('#RabatZestaw').html('0 zł').hide();
                             }
                             $('#SumaOfert').html(ile);
                             $('.SumaZestawu').slideDown();                         
                        } else {
                             $('#SumaPrzecena').html('0 zł');
                             $('#SumaCala').html('0 zł');
                             $('#SumaOfert').html('0');
                             $('.SumaZestawu').hide();
                        }
                    }                 
                    </script>
                
                    <div class="pozycja_edytowana">
                    
                        <div class="info_content">
                    
                            <input type="hidden" name="akcja" value="zapisz" />

                            <p>
                               <label class="required">Rabat dla kupującego:</label>
                               <input type="radio" name="rabat_rodzaj" id="rabat_rodzaj_jest" onclick="$('#ile_rabat').slideDown();PrzeliczSuma()" value="1" checked="checked" /> <label class="OpisFor" for="rabat_rodzaj_jest">kwotowy</label> 
                               <input type="radio" name="rabat_rodzaj" id="rabat_rodzaj_brak" onclick="$('#ile_rabat').slideUp();PrzeliczSuma()" value="0" /> <label class="OpisFor" for="rabat_rodzaj_brak">brak rabatu</label> 
                            </p> 
                            
                            <div id="ile_rabat">
                            
                                <p>
                                   <label class="required" for="rabat">Wartość rabatu:</label>
                                   <input type="text" size="5" name="rabat" class="kropka" id="rabat" /> zł
                                </p>                             
                                
                            </div>
                            
                            <div class="NaglowekListaAukcji">Wybierz aukcje które mają wchodzić w skład zestawu</div>
                            
                            <div class="SzukanieZestaw" id="fraza">
                                <div>Wyszukaj aukcje: <input type="text" size="35" value="" id="szukana_aukcja" /><em class="TipIkona"><b>Wpisz tytuł aukcji lub nr aukcji</b></em></div> <span onclick="WyswietlAukcje('x')"></span> <div class="AnulujWyszukiwanie" onclick="WyswietlAukcje()">anuluj wyszukiwanie</div>
                            </div>                                 
                            
                            <div class="ListaAukcjiZestaw" id="ListaAukcjiZestaw">
                            
                                <?php
                                $zapytanie = "SELECT ap.*, 
                                                     p.products_image as zdjecieOryginal 
                                                FROM allegro_auctions ap 
                                           LEFT JOIN products p ON p.products_id = ap.products_id 
                                               WHERE ap.auction_status = 'ACTIVE' AND ap.auction_seller = '" . $_SESSION['domyslny_uzytkownik_allegro'] . "'
                                               GROUP BY ap.auction_id ORDER BY ap.products_name";                 
                                               
                                $sql = $db->open_query($zapytanie);   

                                echo '<div id="WynikPrzewijanyAllegro"><table class="listing_tbl">';
                                
                                echo '<tr class="div_naglowek">
                                    <td>Wybór</td>
                                    <td>Ilość w zestawie</td>
                                    <td>Pokaż zestaw <br /> w tej ofercie</td>
                                    <td>ID aukcji</td>
                                    <td>Zdjęcie</td>
                                    <td>Tytuł aukcji</td>
                                    <td class="ListingSchowaj">Data rozpoczęcia</td>
                                    <td class="ListingSchowaj">Data zakończenia</td>
                                    <td>Cena Allegro</td>
                                </tr>';                               
                                
                                while ($info = $sql->fetch_assoc()) {

                                    echo '<tr class="pozycja_off" id="tr_' . $info['auction_id'] . '"><td class="NrAukcji" data-nr="' . $info['auction_id'] . '"><input type="checkbox" class="WyborAukcji" name="id_aukcji[]" value="' . $info['auction_id'] . '" id="produkt_id_' . $info['auction_id'] . '" /><label class="OpisForPustyLabel" for="produkt_id_' . $info['auction_id'] . '"></label></td>';
                                    
                                    echo '<td><input type="number" size="3" min="1" class="calkowita ZmianaIlosci" value="1" data-id="' . $info['auction_id'] . '" id="ilosc_' . $info['auction_id'] . '" name="ilosc_' . $info['auction_id'] . '" /></td>';
                                    
                                    echo '<td><input type="checkbox" name="pokaz_' . $info['auction_id'] . '" value="1" id="pokaz_' . $info['auction_id'] . '" checked="checked" /><label class="OpisForPustyLabel" for="pokaz_' . $info['auction_id'] . '"></label></td>';
                                    
                                    $link = '';
                                    if ( $AllegroRest->polaczenie['CONF_SANDBOX'] == 'nie' ) {
                                         //
                                         $link = 'http://allegro.pl/i' . $info['auction_id'] . '.html';
                                         //
                                    } else {
                                         //
                                         $link = 'http://allegro.pl.allegrosandbox.pl/i' . $info['auction_id'] . '.html';
                                         //
                                    }                                
                                    echo '<td><a href="' . $link . '" target="_blank">' . $info['auction_id'] . '</a></td>';
                                    unset($link);
                                        
                                    echo '<td>';
                                    
                                    if ( !empty($info['zdjecieOryginal']) || !empty($info['products_image']) ) {
                                         //
                                         if ( $info['products_image'] != '' ) {
                                              //
                                              echo Funkcje::pokazObrazek($info['products_image'], $info['products_name'], '40', '40'); 
                                              //
                                         } else {                          
                                              //
                                              echo Funkcje::pokazObrazek($info['zdjecieOryginal'], $info['products_name'], '40', '40'); 
                                              //
                                         }
                                         //
                                       } else { 
                                         //
                                         echo '-';
                                         //
                                    }   
                                    
                                    echo '</td>';

                                    echo '<td class="TytulAukcji" data-nr="' . $info['auction_id'] . '"><b>' . $info['products_name'] . '</b></td>';  
                                    
                                    echo '<td class="ListingSchowaj">' . ((!empty($info['auction_date_start']) && date('Y',FunkcjeWlasnePHP::my_strtotime($info['auction_date_start'])) > 1970) ? date('d-m-Y H:i:s',FunkcjeWlasnePHP::my_strtotime($info['auction_date_start'])) : '-') . '</td>';

                                    $data_zakonczenia_allegro = '-';
                                    if ( !empty($info['auction_date_end']) && FunkcjeWlasnePHP::my_strtotime($info['auction_date_end']) > 0 ) {
                                         //
                                         $data_zakonczenia_allegro = date('d-m-Y H:i:s',FunkcjeWlasnePHP::my_strtotime($info['auction_date_end']));
                                         //
                                    } else {
                                         //
                                         if ( date('Y',FunkcjeWlasnePHP::my_strtotime($info['auction_date_start'])) > 1970 ) {
                                              $data_zakonczenia_allegro = 'do wyczerpania';
                                         } else {
                                              $data_zakonczenia_allegro = '-';
                                         }
                                         //
                                    }      
                                    
                                    echo '<td class="ListingSchowaj">' . $data_zakonczenia_allegro . '</td>';
                                    
                                    unset($data_zakonczenia_allegro);
                                    
                                    echo '<td><div class="CenaAllegro" id="cena_' . $info['auction_id'] . '" data-cena="' . $info['products_buy_now_price'] . '">' . number_format($info['products_buy_now_price'], 2, ',', '') . ' zł</div></td>';

                                }
                                
                                echo '<tr class="BrakWynikow"><td colspan="9" class="WynikBrak"><span>Brak aukcji do wyboru ...</span></td></tr>';
                                
                                echo '</table></div>';
                                
                                $db->close_query($sql);
                                unset($info, $zapytanie);                         
                                ?>
                                
                            </div>
                            
                            <label for="id_aukcji[]" generated="true" class="error" style="display:none;margin-left:10px"></label>
                            
                            <div class="SumaZestawu" style="display:none">
                            
                                <div style="margin-bottom:8px">Wartość zestawu - ilość ofert w zestawie <b id="SumaOfert">0</b></div> 
                                
                                <div id="JestRabat">
                                    <div id="RabatZestaw">0 zł</div><div id="SumaCala">0 zł</div> 
                                    <div class="cl"></div>
                                </div>
                                
                                <div id="SumaPrzecena">0 zł</div> 
                                
                            </div>

                        </div>
                        
                    </div>

                    <div class="przyciski_dolne">
                      <input type="submit" class="przyciskNon" value="Zapisz zestaw" />
                      <button type="button" class="przyciskNon" onclick="cofnij('allegro_aukcje','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','allegro');">Powrót</button> 
                    </div>
                
                <?php } else { ?>
                
                    <div class="pozycja_edytowana">
                
                        <?php echo $wynik; ?>

                    </div>
                    
                    <div class="przyciski_dolne">
                      <button type="button" class="przyciskNon" onclick="cofnij('allegro_aukcje','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','allegro');">Powrót</button> 
                    </div>                    
                
                <?php } ?>

              </div>                      
              </form>

        </div>    
    
        <?php
        include('stopka.inc.php');

    } else {
    
      Funkcje::PrzekierowanieURL('index.php');
      
    }
      
}