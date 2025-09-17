<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

        $tablica_opisu = array();

        $nr_wiersza = 1;

        for ( $x = 1; $x <= 100; $x++ ) {
          
            if ( isset($_POST['opis_txt'][$x]) ) {
                 $_POST['opis_txt'][$x] = str_replace('<b>[OP', '[OP', (string)$_POST['opis_txt'][$x]);
                 $_POST['opis_txt'][$x] = str_replace('IS]</b>', 'IS]', (string)$_POST['opis_txt'][$x]);  
            }                           
         
            if ( isset($_POST['opis_sekcja'][$x]) ) {
              
                 // tylko sam listing
                 if ( $_POST['opis_sekcja'][$x] == 'listing' ) {
                      // 
                      if ( !empty($_POST['opis_txt'][$x]) ) {
                           $tablica_opisu[$nr_wiersza] = array( $_POST['opis_sekcja'][$x], array( trim(preg_replace('/\s\s+/', '', (string)$filtr->process($_POST['opis_txt'][$x]))) ) );            
                      }
                      // 
                 }
                 
                 // tylko zdjecie
                 if ( $_POST['opis_sekcja'][$x] == 'zdjecie' ) {
                      // 
                      if ( !empty($_POST['opis_img'][$x]) && $_POST['rodzaj_zdjecia'][$x] == 'dowolne' ) {
                           $tablica_opisu[$nr_wiersza] = array( $_POST['opis_sekcja'][$x], array( $_POST['opis_img'][$x] ) );                     
                      } else {
                           // jezeli nie ma wybranego zdjecia
                           $tablica_opisu[$nr_wiersza] = array( $_POST['opis_sekcja'][$x], array( '[ZDJECIE_' . $_POST['nr_foto'][$x] . ']' ) );    
                      }
                      // 
                 }    

                 // zdjecie i listing  
                 if ( $_POST['opis_sekcja'][$x] == 'zdjecie_listing' ) {
                      // 
                      if ( !empty($_POST['opis_img'][$x]) && !empty($_POST['opis_txt'][$x]) && $_POST['rodzaj_zdjecia'][$x] == 'dowolne' ) {
                           $tablica_opisu[$nr_wiersza] = array( $_POST['opis_sekcja'][$x], array( $_POST['opis_img'][$x], trim(preg_replace('/\s\s+/', ' ', (string)$filtr->process($_POST['opis_txt'][$x]))) ) );                   
                      } else {
                           // jezeli nie ma wybranego zdjecia
                           $tablica_opisu[$nr_wiersza] = array( $_POST['opis_sekcja'][$x], array( '[ZDJECIE_' . $_POST['nr_foto'][$x] . ']' , trim(preg_replace('/\s\s+/', ' ', (string)$filtr->process($_POST['opis_txt'][$x]))) ) );   
                      }
                      // 
                 }
                 
                 // listing i zdjecie
                 if ( $_POST['opis_sekcja'][$x] == 'listing_zdjecie' ) {
                      // 
                      if ( !empty($_POST['opis_img'][$x]) && !empty($_POST['opis_txt'][$x]) && $_POST['rodzaj_zdjecia'][$x] == 'dowolne' ) {
                           $tablica_opisu[$nr_wiersza] = array( $_POST['opis_sekcja'][$x], array( trim(preg_replace('/\s\s+/', ' ', (string)$filtr->process($_POST['opis_txt'][$x]))), $_POST['opis_img'][$x] ) );                       
                      } else {
                           // jezeli nie ma wybranego zdjecia
                           $tablica_opisu[$nr_wiersza] = array( $_POST['opis_sekcja'][$x], array( trim(preg_replace('/\s\s+/', ' ', (string)$filtr->process($_POST['opis_txt'][$x]))), '[ZDJECIE_' . $_POST['nr_foto'][$x] . ']' ) );   
                      }
                      // 
                 }      

                 // zdjecie i zdjecie
                 if ( $_POST['opis_sekcja'][$x] == 'zdjecie_zdjecie' ) {
                      // 
                      if ( !empty($_POST['opis_img'][$x][1]) && $_POST['rodzaj_zdjecia_a'][$x] == 'dowolne' && !empty($_POST['opis_img'][$x][2]) && $_POST['rodzaj_zdjecia_b'][$x] == 'dowolne' ) {
                           $tablica_opisu[$nr_wiersza] = array( $_POST['opis_sekcja'][$x], array( $_POST['opis_img'][$x][1], $_POST['opis_img'][$x][2] ) );                       
                      }
                      //
                      // jezeli nie ma wybranego zdjecia
                      if ( ( empty($_POST['opis_img'][$x][1]) || $_POST['rodzaj_zdjecia_a'][$x] == 'produkt' ) && !empty($_POST['opis_img'][$x][2]) ) {
                           $tablica_opisu[$nr_wiersza] = array( $_POST['opis_sekcja'][$x], array( '[ZDJECIE_' . $_POST['nr_foto_a'][$x] . ']', $_POST['opis_img'][$x][2] ) );   
                      }
                      if ( !empty($_POST['opis_img'][$x][1]) && ( empty($_POST['opis_img'][$x][2]) || $_POST['rodzaj_zdjecia_b'][$x] == 'produkt') ) {
                           $tablica_opisu[$nr_wiersza] = array( $_POST['opis_sekcja'][$x], array( $_POST['opis_img'][$x][1], '[ZDJECIE_' . $_POST['nr_foto_b'][$x] . ']' ) );         
                      }   
                      if ( ( empty($_POST['opis_img'][$x][1]) || $_POST['rodzaj_zdjecia_a'][$x] == 'produkt' ) && ( empty($_POST['opis_img'][$x][2]) || $_POST['rodzaj_zdjecia_b'][$x] == 'produkt') ) {
                           $tablica_opisu[$nr_wiersza] = array( $_POST['opis_sekcja'][$x], array( '[ZDJECIE_' . $_POST['nr_foto_a'][$x] . ']', '[ZDJECIE_' . $_POST['nr_foto_b'][$x] . ']' ) );         
                      }                       
                      // 
                 }      
                 
                 $nr_wiersza++;

            }        
          
        }
        
        unset($nr_wiersza);

        $opis_allegro = serialize($tablica_opisu);

        $pola = array(array('allegro_theme_name', $filtr->process($_POST['nazwa_szablonu'])),
                      array('allegro_theme_description', $opis_allegro));
                      
        if ( $_POST['id'] == 'nowy' ) {
             //
             $sql = $db->insert_query('allegro_theme', $pola);        
             //
        } else {
             //
             $sql = $db->update_query('allegro_theme', $pola, "allegro_theme_id = '" . (int)$_POST['id'] . "'");        
             //
        }

        Funkcje::PrzekierowanieURL('szablony.php?id_nowy=' . $_POST["id"]);
    }

    // wczytanie naglowka HTML

    include('naglowek.inc.php');

    ?>
    
    <div id="naglowek_cont">Edycja szablonu aukcji Allegro</div>
    
    <div id="cont">

        <script>
        $(document).ready(function() {
          $("#allegroForm").validate({
            rules: {
              nazwa_szablonu: {
                required: true
              }
            },
            messages: {
              nazwa_szablonu: {
                required: "Pole jest wymagane."
              }
            }            
          });
        });

        function ZmienZdjecie(wartosc, nr, prefix) {
            if ( wartosc == 'dowolne' ) {
                 wylacz = '1';
                 wlacz = '2';
            } else {
                 wylacz = '2';
                 wlacz = '1';
            }
            //
            $('#kont_rodzaj_zdjecia_' + wylacz + prefix + '_' + nr).stop().slideUp();  
            $('#kont_rodzaj_zdjecia_' + wlacz + prefix + '_' + nr).stop().slideDown();  
            //
            if ( wylacz == '2' ) {
                 $('#opis_img' + prefix + '_' + nr).removeClass('required');
            } else {
                 $('#opis_img' + prefix + '_' + nr).addClass('required');
            }
            //
        }
        </script>        
    
        <form action="allegro/szablony_nowy_dodaj_edytuj.php" method="post" id="allegroForm" class="cmxform">     

            <div class="poleForm">          

              <div class="naglowek">Edycja danych</div>
            
              <?php
              
              if ( !isset($_GET['id_nowy']) ) {
                   $_GET['id_nowy'] = 0;
              }    
              
              $zapytanie = "select distinct * from allegro_theme where allegro_theme_id = '" . (int)$_GET['id_nowy'] . "'";
              $sql = $db->open_query($zapytanie);
              
              if ((int)$db->ile_rekordow($sql) > 0 || $_GET['id_nowy'] == 'nowy') {
              
                  if ( $_GET['id_nowy'] != 'nowy' ) {
                       //
                       $info = $sql->fetch_assoc();
                       //
                  }
                  ?>
              
                  <div class="pozycja_edytowana">
                
                    <div class="info_content">
                
                        <input type="hidden" name="akcja" value="zapisz" />
                    
                        <input type="hidden" name="id" value="<?php echo (($_GET['id_nowy'] != 'nowy') ? $info['allegro_theme_id'] : 'nowy'); ?>" />
                        
                        <p class="DodanieSzablonuEdycja">
                          <label for="nazwa_szablonu" class="required" >Nazwa szablonu:</label>
                          <input type="text" name="nazwa_szablonu" id="nazwa_szablonu" size="60" value="<?php echo (($_GET['id_nowy'] != 'nowy') ? $info['allegro_theme_name'] : ''); ?>" />
                        </p>     

                        <?php
                        $tresc_opisu = '';

                        if ( $_GET['id_nowy'] != 'nowy' ) {
                             //
                             if ( strpos((string)$info['allegro_theme_description'], '{') > -1 && strlen((string)$info['allegro_theme_description']) > 20 ) {
                                  $tresc_opisu = unserialize($info['allegro_theme_description']);
                             }
                             //
                        }
                        ?>   
                         
                        <script>
                        function ckeditAllegro(id, szerokosc, wysokosc) {
                           CKEDITOR.replace( id, {
                               width: szerokosc,
                               height: wysokosc,
                               autoGrow_minHeight : wysokosc,
                               filebrowserWindowFeatures : 'menubar=no,toolbar=no,minimizable=no,resizable=no,scrollbars=no',
                               coreStyles_bold: { element: 'b' },
                               toolbar_cms: [[ 'Source','Format','Bold','NumberedList','BulletedList' ]],
                               format_tags: 'p;h1;h2',
                               enterMode: CKEDITOR.ENTER_P,
                               basicEntities: false,
                               entities_latin: false,
                               entities_greek: false,
                               entities_additional: '',
                               fillEmptyBlocks: false,
                               tabSpaces: 0,
                               allowedContent: 'p h1 h2 ul ol li b',
                               on: {
                                       paste: function(e) {
                                           if (e.data.dataValue !== 'undefined')
                                               e.data.dataValue = e.data.dataValue.replace(/(\<br ?\/?\>)+/gi, '<p>');
                                       }
                                   }                
                               
                             }
                           );
                        }    
                                 
                        // zmiana sposobu wyswietlania opisu
                        function ZmienSposobWyswietlaniaOpisu() {
                         
                          $('.SposobWyswietlania').click(function() {
                              //
                              $('#ekr_preloader').css('display','block');
                              //
                              $('#Opis_nr_' + $(this).attr('data-nr') + ' .WyborOpisu li').removeClass('Wl');
                              $(this).addClass('Wl');
                              //
                              var nr = $(this).attr('data-nr');
                              var typ = $(this).attr('data-typ');
                              var nr_losowy = Math.floor(Math.random() * 10000000) + 1000;
                              //
                              for ( instance in CKEDITOR.instances ) {
                                    CKEDITOR.instances[instance].updateElement();
                              }              
                              //
                              var opis = '';
                              var foto_1_img = '';
                              var foto_1_input = '';
                              var foto_2_img = '';
                              var foto_2_input = '';
                              var foto_nr_zdjecia_1 = '';
                              var foto_nr_zdjecia_2 = '';
                              //
                              if ( typ == 'listing' || typ == 'zdjecie_listing' || typ == 'listing_zdjecie' ) {
                                   opis = $('#Opis_nr_' + nr).find('textarea').val();
                              }
                              //
                              if ( typ == 'zdjecie' || typ == 'zdjecie_listing' || typ == 'listing_zdjecie' || typ == 'zdjecie_zdjecie' ) {
                                   //
                                   var e = 1;
                                   //
                                   $('#Opis_nr_' + nr).find('.ListaFoto').each(function() {
                                      //
                                      var typ_zdjecia = $(this).find('.ZmianaZdjeciaAllegro').val();
                                      //
                                      if ( typ_zdjecia == 'dowolne' ) {
                                           //
                                           if ( e == 1 ) {
                                                //
                                                if ( $(this).find('img').length ) {
                                                     foto_1_img = $(this).find('img').attr('src');
                                                     foto_1_input = $(this).find('input').val();
                                                }
                                                //
                                           }
                                           if ( e == 2 ) {
                                                //
                                                if ( $(this).find('img').length ) {
                                                     foto_2_img = $(this).find('img').attr('src');
                                                     foto_2_input = $(this).find('input').val();
                                                }
                                                //
                                           }    
                                      } else {
                                           //
                                           if ( e == 1 ) {
                                                //
                                                if ( $(this).find('.WyborNrZdjeciaAllegro').length ) {
                                                     foto_nr_zdjecia_1 = $(this).find('.WyborNrZdjeciaAllegro').val();
                                                }
                                                //
                                           }
                                           if ( e == 2 ) {
                                                //
                                                if ( $(this).find('.WyborNrZdjeciaAllegro').length ) {
                                                     foto_nr_zdjecia_2 = $(this).find('.WyborNrZdjeciaAllegro').val();
                                                }
                                                //
                                           }  
                                           //
                                      }
                                      //
                                      e++;
                                      //
                                   });
                                   //
                              }
                              //
                              $.post('ajax/allegro_dane_opis.php?tok=' + $('#tok').val(), { foto_nr_zdjecia_1: foto_nr_zdjecia_1, foto_nr_zdjecia_2: foto_nr_zdjecia_2, foto_1_img: foto_1_img, foto_2_img: foto_2_img, foto_1_input: foto_1_input, foto_2_input: foto_2_input, opis: opis, nr_losowy: nr_losowy, nr: nr, typ: typ, zmiana_trybu: 'tak', szablon: 'tak' }, function(data) {
                                    //
                                    $('#ekr_preloader').fadeOut();
                                    $('#Opis_nr_' + nr + ' .SekcjaOpisuAllegro').html(data);
                                    //
                                    ObslugaEdytora();
                                    //
                               });         
                               //
                          });
                          $('.KasujPoleOpisuAllegro').click(function() {
                               //
                               $('#Opis_nr_' + $(this).attr('data-nr')).stop().slideUp('fast', function() { 
                                    //
                                    $(this).remove();
                                    //
                                    PrzeliczPolaWierszy();
                                    //
                              });
                              //
                          });                              

                        };       
                        
                        // wybor zdjecia dla opisu allegro - podczas produktu
                        function WyborFotoAllegro( id ) {
                            //
                            przegladarka( $('#opis_typ').attr('data-images'), id, 'strona', '', '' );
                            //
                        }           

                        $(document).ready(function() {
                            //                      
                            if ( $('#WygladPop').length ) {
                                 $('#WygladPop').insertBefore('#ekr_preloader');
                            }     
                            var nr_losowy = Math.floor(Math.random() * 10000000) + 1000;
                            //
                            <?php if ( !is_array($tresc_opisu) ) { ?>
                            $.post('ajax/allegro_dane_opis.php?tok=' + $('#tok').val(), { nr_losowy: nr_losowy, nr: 1, typ: 'listing', zmiana_trybu: 'nie', szablon: 'tak' }, function(data) {
                                   //
                                   $('#Opis_nr_1').html('<div class="NrWiersza">Wiersz nr <b>1</b></div>' + data);
                                   //
                                   ZmienSposobWyswietlaniaOpisu();
                                   //
                                   PrzesunWiersze();
                                   ObslugaEdytora();
                                   //                                      
                            });   
                            <?php } ?>
                            //
                            $('.NowyWierszOpisu span').click(function() {
                                   //
                                   $('#ekr_preloader').css('display','block');
                                   //
                                   // usunie edytory
                                   $('.WierszeOpisu textarea').each(function() {
                                       //
                                       var idt = $(this).attr('id');
                                       var txu = $(this).attr('data-id');
                                       //
                                       for (instance in CKEDITOR.instances) {
                                         if (CKEDITOR.instances.hasOwnProperty(instance)) {
                                             if (CKEDITOR.instances[instance].name == idt) {
                                                 CKEDITOR.instances[instance].updateElement();
                                                 CKEDITOR.instances[instance].destroy();
                                             }
                                         }
                                       } 
                                       //
                                       $('#opis_txt_' + txu).hide();
                                       $('#opis_edytor_' + txu).html( $('#opis_txt_' + txu).val() ).show();                                            
                                       $('#przyciski_edytor_' + txu).show();
                                       $('#przyciski_edytor_' + txu).find('.EdytujOpis').css({ 'display' : 'inline-block' });     
                                       $('#przyciski_edytor_' + txu).find('.ZapiszOpis').css({ 'display' : 'none' });                                         
                                       //                                       
                                   }); 
                                   //
                                   $('#ZapiszDaneAllegro').show();
                                   //                                   
                                   var nowy_nr = parseInt($('#licznik_wierszy_opisu').val()) + 1;
                                   var nr_losowy = Math.floor(Math.random() * 10000000) + 1000;
                                   $('#licznik_wierszy_opisu').val( nowy_nr );
                                   //
                                   $('.WierszeOpisu').append('<div id="Opis_nr_' + nowy_nr + '" class="OpisWystawianieAukcji"></div>');
                                   //
                                   $.post('ajax/allegro_dane_opis.php?tok=' + $('#tok').val(), { nr_losowy: nr_losowy, nr: nowy_nr, typ: 'listing', zmiana_trybu: 'nie', szablon: 'tak' }, function(data) {
                                          //
                                          $('#ekr_preloader').css('display','none');
                                          //
                                          var nr_wiersza = 0;
                                          $('.OpisWystawianieAukcji').each(function() {
                                             //
                                             nr_wiersza++;
                                             //
                                          });                                                
                                          $('#Opis_nr_' + nowy_nr).html('<div class="NrWiersza">Wiersz nr <b>' + nr_wiersza + '</b></div>' + data);
                                          //
                                          ZmienSposobWyswietlaniaOpisu();
                                          //
                                          PrzesunWiersze();
                                          ObslugaEdytora();
                                          //  
                                   });                                          
                                   //
                            });
                            //
                            PrzesunWiersze();
                            ObslugaEdytora();
                            //
                        });
                        
                        String.prototype.replaceAllegro = function (stringToFind, stringToReplace) {
                            if (stringToFind === stringToReplace) return this;
                            var temp = this;
                            var index = temp.indexOf(stringToFind);
                            while (index != -1) {
                                temp = temp.replace(stringToFind, stringToReplace);
                                index = temp.indexOf(stringToFind);
                            }
                            return temp;
                        };                        
                        
                        function ObslugaEdytora() {
                            //
                            $('.PrzyciskiEdytora span.EdytujOpis').click(function() {
                                //
                                var tu = $(this).attr('data-id');
                                var tnr = $(this).attr('data-sekcja');
                                //
                                $('.PrzyciskiEdytora').each(function() {
                                    //
                                    var tr = $(this).attr('id');
                                    if ( tr != 'przyciski_edytor_' + tu ) {
                                         $(this).hide();
                                    }
                                    //
                                });
                                //
                                $('#opis_edytor_' + tu).hide();
                                //
                                var byl = false;
                                $('.WierszeOpisu textarea').each(function() {
                                   //
                                   var idtc = $(this).attr('id');
                                   //
                                   for (instance in CKEDITOR.instances) {
                                     if (CKEDITOR.instances.hasOwnProperty(instance)) {
                                         if (CKEDITOR.instances[instance].name == idtc) {
                                             byl = true;
                                         }
                                     }
                                   } 
                                   //
                                });    
                                if ( byl == false ) {
                                     ckeditAllegro('opis_txt_' + tu,'99%','200');
                                }
                                //
                                $('#przyciski_edytor_' + tu).show();
                                $('#przyciski_edytor_' + tu).find('.EdytujOpis').css({ 'display' : 'none' });
                                $('#przyciski_edytor_' + tu).find('.ZapiszOpis').css({ 'display' : 'inline-block' });
                                //
                                $.scrollTo($('#Opis_nr_' + tnr), 0);
                                $.scrollTo('-=20px', 0);      
                                //
                                $('#ZapiszDaneAllegro').hide();
                                //
                            });
                            $('.PrzyciskiEdytora span.ZapiszOpis').click(function() {
                                //
                                var tu = $(this).attr('data-id');    
                                var tnr = $(this).attr('data-sekcja');
                                //
                                for (instance in CKEDITOR.instances) {
                                  if (CKEDITOR.instances.hasOwnProperty(instance)) {
                                      if (CKEDITOR.instances[instance].name == 'opis_txt_' + tu) {
                                          CKEDITOR.instances[instance].updateElement();
                                          CKEDITOR.instances[instance].destroy();
                                      }
                                  }
                                }         
                                $('#opis_txt_' + tu).hide();
                                $('#opis_edytor_' + tu).html( $('#opis_txt_' + tu).val() ).show();
                                //
                                var dodany_opis = $('#opis_txt_' + tu).val();
                                dodany_opis = dodany_opis.replaceAllegro("<p></p>", "");
                                //
                                $('#opis_txt_' + tu).val(dodany_opis);
                                //
                                if ( dodany_opis != '' ) {
                                     $('#opis_edytor_' + tu).removeClass('PustyOpisEdytora');                                      
                                } else {
                                     $('#opis_edytor_' + tu).addClass('PustyOpisEdytora');                                      
                                }
                                //
                                $('#przyciski_edytor_' + tu).find('.EdytujOpis').css({ 'display' : 'inline-block' });
                                $('#przyciski_edytor_' + tu).find('.ZapiszOpis').css({ 'display' : 'none' });
                                //
                                $('.PrzyciskiEdytora').show();
                                //
                                $.scrollTo($('#Opis_nr_' + tnr), 0);
                                $.scrollTo('-=20px', 0);
                                //
                                $('#ZapiszDaneAllegro').show();
                                //
                            });     
                            //
                        }
                        
                        function PrzesunWiersze() {
                            //
                            $('.PrzesunDol').click(function() {
                                //
                                $('#ekr_preloader').css('display','block');
                                //
                                var iyt = $(this).attr('data-nr');
                                // 
                                $('#Opis_nr_' + iyt).hide().before($('#Opis_nr_' + (parseInt(iyt) + 1))).fadeIn(300, function() {
                                   //
                                   PrzeliczPolaWierszy();
                                   //
                                   $.scrollTo($('#Opis_nr_' + (parseInt(iyt) + 1)), 0);
                                   $.scrollTo('-=20px', 0);
                                   //
                                }); 
                                //
                                $('#ekr_preloader').fadeOut('slow');
                                //
                                ObslugaEdytora();
                                //
                            });
                            $('.PrzesunGora').click(function() {
                                //
                                $('#ekr_preloader').css('display','block');
                                //
                                var iyt = $(this).attr('data-nr');
                                $('#Opis_nr_' + iyt).hide().after($('#Opis_nr_' + (parseInt(iyt) - 1))).fadeIn(300, function() {
                                   //
                                   PrzeliczPolaWierszy();
                                   //
                                   $.scrollTo($('#Opis_nr_' + (parseInt(iyt) - 1)), 0);
                                   $.scrollTo('-=20px', 0);
                                   //                        
                                });
                                //
                                $('#ekr_preloader').fadeOut('slow');
                                //
                                ObslugaEdytora();
                                //
                            });           
                            //
                        }
                        
                        function PrzeliczPolaWierszy() {
                            //
                            var t = 1;
                            $('.WierszeOpisu .OpisWystawianieAukcji').each(function() {
                               //
                               $(this).attr('id', 'Opis_nr_' + t);
                               $(this).find('.NrWiersza').find('b').html(t);
                               //
                               $(this).find('.SposobWyswietlania').attr('data-nr', t);
                               $(this).find('.KasujPoleOpisuAllegro').attr('data-nr', t);               
                               $(this).find('.PrzesunGora').attr('data-nr', t); 
                               $(this).find('.PrzesunDol').attr('data-nr', t); 
                               //
                               $(this).find('.PrzyciskiEdytora').find('.przyciskAllegro').attr('data-sekcja', t); 
                               //
                               $(this).find('.UkryteOpisSekcja').attr('name', 'opis_sekcja[' + t + ']');
                               //
                               $(this).find('.OpisFoto').find('textarea').each(function() {
                                  //
                                  $(this).attr('name', 'opis_txt[' + t + ']');
                                  //
                               });
                               $(this).find('.OpisFoto').find('input').each(function() {
                                  //
                                  var idinp = $(this).attr('id');
                                  //
                                  if ( $(this).attr('type') == 'hidden' ) {
                                       //
                                       $(this).attr('name', 'opis_img[' + t + ']');
                                       //
                                       if ( idinp.indexOf('_a_') > -1 ) {
                                            $(this).attr('name', 'opis_img[' + t + '][1]'); 
                                       }
                                       if ( idinp.indexOf('_b_') > -1 ) {
                                            $(this).attr('name', 'opis_img[' + t + '][2]'); 
                                       }                  
                                       //
                                  }
                                  //
                               });
                               $(this).find('.WyborZdjeciaAllegro').find('.ZmianaZdjeciaAllegro').each(function() {
                                  //
                                  var idinp = $(this).attr('data-id');
                                  //                               
                                  $(this).attr('name', 'rodzaj_zdjecia[' + t + ']');
                                  //
                                  if ( idinp.indexOf('_a_') > -1 ) {
                                       $(this).attr('name', 'rodzaj_zdjecia_a[' + t + ']'); 
                                  }
                                  if ( idinp.indexOf('_b_') > -1 ) {
                                       $(this).attr('name', 'rodzaj_zdjecia_b[' + t + ']');
                                  }                                      
                                  //
                               }); 
                               $(this).find('.OpisFoto').find('.WyborNrZdjeciaAllegro').each(function() {
                                  //
                                  var idimg = $(this).attr('data-id');
                                  $(this).attr('name', 'nr_foto[' + t + ']');
                                  //
                                  if ( idimg.indexOf('_a_') > -1 ) {
                                       $(this).attr('name', 'nr_foto_a[' + t + '][1]'); 
                                  }
                                  if ( idimg.indexOf('_b_') > -1 ) {
                                       $(this).attr('name', 'nr_foto_b[' + t + '][2]'); 
                                  }                  
                                  //
                               });                                
                               //
                               t++;
                            });
                            //
                            $('#licznik_wierszy_opisu').val( t - 1 );
                            //
                        }                                    
                        </script>
                        
                        <input type="hidden" id="opis_typ" data-images="<?php echo KATALOG_ZDJEC; ?>" value="produkt" />
                        <input type="hidden" id="szablony_slownik" value="tak" />
                        
                        <?php
                        if ( is_array($tresc_opisu) ) {
                             //
                             // zabezpieczenie dla zlej numeracji kluczy
                             $tresc_opisu_tmp = array();
                             $q = 1;
                             //
                             foreach ( $tresc_opisu as $klucz => $wart ) {
                                //
                                $tresc_opisu_tmp[$q] = $wart;
                                $q++;
                                //
                             }
                             //
                             $tresc_opisu = $tresc_opisu_tmp;
                             unset($tresc_opisu_tmp);
                             //
                             echo '<input type="hidden" value="' . count($tresc_opisu) . '" id="licznik_wierszy_opisu" />';
                             //
                             echo '<div class="WierszeOpisu">';
                             //
                             $bez_ajax = true;
                             //
                             for ( $t = 1; $t <= count($tresc_opisu); $t++ ) {
                                   //
                                   $zawartosc_wiersza = '';
                                   //
                                   ob_start();
                                   //
                                   $nr = $t;
                                   $nr_losowy = rand(1000,1000000);
                                   $typ = $tresc_opisu[$t][0];
                                   $zmiana_trybu = 'nie';
                                   $dane_opisu = $tresc_opisu[$t][1];
                                   $szablon_allegro = 'tak';
                                   //
                                   include('ajax/allegro_dane_opis.php');
                                   //
                                   $zawartosc_wiersza = ob_get_contents();
                                   //
                                   ob_end_clean();
                                   //
                                   echo '<div id="Opis_nr_' . $t . '" class="OpisWystawianieAukcji"><div class="NrWiersza">Wiersz nr <b>' . $t . '</b></div>';
                                       //
                                       echo $zawartosc_wiersza;
                                       //
                                   echo '</div>';

                                   unset($zawartosc_wiersza, $nr, $nr_losowy, $typ, $zmiana_trybu, $dane_opisu, $szablon_allegro);
                                       
                             }
                             //
                             echo '</div>';  
                             
                             unset($bez_ajax);

                             echo '<script>$(document).ready(function() { ZmienSposobWyswietlaniaOpisu(); });</script>';                 
                             //
                        } else {
                             //
                             ?>
                             
                             <input type="hidden" value="1" id="licznik_wierszy_opisu" />
                            
                             <div class="WierszeOpisu">
                             
                                 <div id="Opis_nr_1" class="OpisWystawianieAukcji"></div>
                                 
                             </div>

                             <?php
                             //
                        }
                        ?>   

                        <div class="NowyWierszOpisu">
                            <span class="dodaj">dodaj kolejny wiersz</span>
                        </div>                         

                    </div>

                  </div>

                  <div class="przyciski_dolne">
                      <input type="submit" class="przyciskNon" id="ZapiszDaneAllegro" value="Zapisz dane" />
                      <button type="button" class="przyciskNon" onclick="cofnij('szablony','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_nowy')); ?>','allegro');">Powrót</button>           
                  </div>   

              <?php
            
              $db->close_query($sql);
              
              if ( $_GET['id_nowy'] != 'nowy' ) {
                   //
                   unset($info);            
                   //
              }
            
              } else {
              
                  echo '<div class="pozycja_edytowana">Brak danych do wyświetlenia</div>';
              
              }
              ?>                  

            </div>
            
        </form>

    </div>    
    
    <div class="objasnienia">
    
          <div class="objasnieniaTytul">Znaczniki, które możesz użyć poszczególnych wierszach opisu aukcji</div>
          
          <br />
          
          <div class="objasnieniaTresc">

            <div style="padding-bottom:10px;font-weight:bold;">Treść szablonu aukcji</div>
            <ul class="mcol">
              <li><b>[OPIS]</b> - opis produktu - dane są zwracane w formie tekstu (bez formatowania HTML)</li>
              <li><b>[NAZWA]</b> - nazwa produktu - dane są zwracane w formie tekstu (bez formatowania HTML)</li>
              <li><b>[DODATKOWA_NAZWA]</b> - dodatkowy tekst do nazwy produktu (bez formatowania HTML)</li>
              <li><b>[UZYTKOWNIK_ALLEGRO]</b> - login użytkownika w serwisie Allegro</li>
              <li><b>[CECHY_PRODUKTU]</b> - cechy produktu wystawianego produktu (wybrane podczas wystawiania aukcji w postaci listy HTML &lt;ul&gt;&lt;/ul&gt;)</li>
              <li><b>[CENA_KUP_TERAZ]</b> - cena produktu na aukcji</li>
              <li><b>[CZAS_WYSYLKI]</b> - czas wysyłki produktu wystawionego na aukcji</li>
              <li><b>[STAN_PRODUKTU]</b> - stan produktu wystawionego na aukcji</li>
              <li><b>[GWARANCJA]</b> - gwarancja produktu wystawionego na aukcji (w formie tekstu)</li>
              <li><b>[NUMER_KATALOGOWY]</b> - numer katalogowy produktu </li>
              <li><b>[KOD_PRODUCENTA]</b> - kod producenta produktu</li>
              <li><b>[KOD_EAN]</b> - kod EAN</li>
              <li><b>[PKWIU]</b> - PKWiU</li>
              <li><b>[PRODUCENT]</b> - nazwa producenta produktu</li>
              <li><b>[WAGA]</b> - waga produktu w KG</li>
              <li><b>[DODATKOWA_ZAKLADKA_x_NAZWA]</b> - tytuł dodatkowej zakładki o nr x - x to wartości od 1 do 4 (bez formatowania HTML)</li>
              <li><b>[DODATKOWA_ZAKLADKA_x_TRESC]</b> - treść dodatkowej zakładki o nr x - x to wartości od 1 do 4 (bez formatowania HTML)</li>
              <li><b>[DODATKOWE_POLA_OPISOWE]</b> - dodatkowe pola opisowe wystawianego produktu (w postaci listy HTML &lt;ul&gt;&lt;/ul&gt; - tylko pole tekstowe - bez linków i grafik)</li>            
            </ul>

          </div>
          
    </div>     

    <?php
    include('stopka.inc.php');

}
