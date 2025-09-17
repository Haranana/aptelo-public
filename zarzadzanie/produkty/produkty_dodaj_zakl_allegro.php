<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone && isset($_GET['id_produktu']) && (int)$_GET['id_produktu'] >= 0 && Sesje::TokenSpr()) { 

    $id_produktu = (int)$_GET['id_produktu'];
 
    ?>

    <div class="info_content">
    
        <script src="javascript/jquery.KategorieAllegro.js" type="text/javascript"></script>
        
        <script>
        $(document).ready( function() {

          $(".kropkaPusta").change(		
            function () {
              var type = this.type;
              var tag = this.tagName.toLowerCase();
              if (type == 'text' && tag != 'textarea' && tag != 'radio' && tag != 'checkbox') {
                  //
                  zamien_krp($(this),'');
                  //
              }
            }
          );          
          
        });      
        </script>    
    
        <?php
        $products_description_allegro = '';
        $products_price_allegro = '';
        $products_name_allegro = '';
        $products_image_allegro = '';
        $products_weight_allegro = '';
        $kategoria_id_allegro = '';
        //
        if ($id_produktu > 0) {    
            //
            $zapytanie_tmp = "select * from products_allegro_info where products_id = '".$id_produktu."'";
            $sqls = $db->open_query($zapytanie_tmp);
            //
            if ((int)$db->ile_rekordow($sqls) > 0) {
                //
                $dane_allegro = $sqls->fetch_assoc();
                //
                $products_description_allegro = $dane_allegro['products_description_allegro'];
                $products_price_allegro = $dane_allegro['products_price_allegro'];
                $products_name_allegro = Funkcje::formatujTekstInput($dane_allegro['products_name_allegro']);
                $products_image_allegro = $dane_allegro['products_image_allegro'];
                $products_weight_allegro = $dane_allegro['products_weight_allegro'];
                $kategoria_id_allegro = (($dane_allegro['products_cat_id_allegro'] > 0) ? $dane_allegro['products_cat_id_allegro'] : '');
                //
            }
            //
            $db->close_query($sqls); 
            unset($zapytanie_tmp);         
            //
        }
        ?>   

        <span class="maleSukces">Indywidualne parametry produktu przy wystawianiu aukcji na Allegro.</span>
        
        <input type="hidden" name="dane_allegro" value="1" />

        <?php if ( Funkcje::SprawdzAktywneAllegro() ) { ?>
        
        <span class="maleInfo">Kategoria do jakiej będzie przypisany produkt w Allegro.</span>   

        <?php
        $ListaKategorii = '';
        $SciezkaKategorii = '<span id="kt_1" data-id="" data-ostatnia="" data-nr="1">Allegro</span>';
        $OstatniElement = false;
        $BlednaKategoria = false;
        //
        $AllegroRest = new AllegroRest( array('allegro_user' => $_SESSION['domyslny_uzytkownik_allegro']) );
        $KategorieAllegro = $AllegroRest->commandRequest('sale/categories', $kategoria_id_allegro, '');                          
        
        if ( $kategoria_id_allegro != '' ) {
             //
             if ( isset($KategorieAllegro->errors) && $KategorieAllegro->errors ) {
                  //
                  // jezeli jest bledna kategoria wyswietla domyslna liste kategorii glownych
                  //
                  $KategorieAllegro = $AllegroRest->commandRequest('sale/categories', '', ''); 
                  //
                  foreach ( $KategorieAllegro->categories as $KategoriaAllegro ) {
                      //
                      $ListaKategorii .= '<li data-id="' . $KategoriaAllegro->id . '" data-ostatnia="' . (($KategoriaAllegro->leaf == '1') ? '1' : '') . '">' . $KategoriaAllegro->name . '</li>';
                      //
                  }
                  //
                  $BlednaKategoria = true;
                  //
               } else {
                  //
                  if ( isset($KategorieAllegro->leaf) && $KategorieAllegro->leaf == '1' ) {
                       //
                       $OstatniElement = true;
                       //
                  }
                  //
                  if ( isset($KategorieAllegro->parent->id) && $OstatniElement == true ) {
                       //
                       $KolejnaPozycjaParent = $AllegroRest->commandRequest('sale/categories', array('parent.id' => $KategorieAllegro->parent->id), '');            
                       //
                       foreach ( $KolejnaPozycjaParent->categories as $KategoriaAllegro_parent ) {
                           //
                           $ListaKategorii .= '<li data-id="' . $KategoriaAllegro_parent->id . '" data-ostatnia="' . $KategoriaAllegro_parent->leaf . '">' . $KategoriaAllegro_parent->name . '</li>';
                           //
                       }       
                       //
                       unset($KolejnaPozycjaParent);
                       //
                       $SciezkaKategoriiTablica = array();              
                       $SciezkaKategoriiTablica[0] = array( 'span' => '<span id="kt_" data-id="' . $KategorieAllegro->id . '" data-ostatnia="' . (($KategorieAllegro->leaf == '1') ? '1' : '') . '" data-nr="">' . $KategorieAllegro->name . '</span>' );
                       //
                       $ParentKategoria = $KategorieAllegro->parent->id;
                       
                       for ( $x = 1; $x < 9; $x++ ) {
                             //
                             // kolejne pozycje
                             $KolejnaPozycjaParent = $AllegroRest->commandRequest('sale/categories', $ParentKategoria, '');
                             $SciezkaKategoriiTablica[ $x ] = array( 'span' => '<span id="kt_" data-id="' . $KolejnaPozycjaParent->id . '" data-ostatnia="' . (($KolejnaPozycjaParent->leaf == '1') ? '1' : '') . '" data-nr="">' . $KolejnaPozycjaParent->name . '</span>' );
                             //
                             if ( isset($KolejnaPozycjaParent->parent->id) ) {
                                  //
                                  $ParentKategoria = $KolejnaPozycjaParent->parent->id;
                                  //
                             } else {
                                  //
                                  break;
                                  //
                             }
                              //
                       }
                       
                       unset($ParentKategoria);
                      
                       $SciezkaKategoriiTablica[] = array( 'span' => '<span id="kt_" data-id="" data-ostatnia="" data-nr="">Allegro</span>' );
                      
                       krsort($SciezkaKategoriiTablica);                      
                       //
                       $SciezkaKategorii = '';
                       //
                       $nr = 1;
                       foreach ( $SciezkaKategoriiTablica as $Tmp ) {
                            //
                            $SciezkaKategorii .= str_replace('nr=""', 'nr="' . $nr . '"', str_replace('kt_', 'kt_' . $nr, (string)$Tmp[ 'span' ]));
                            $nr++;
                            //
                       }                                
                       //
                       unset($KolejnaPozycjaParent, $nr);
                       //
                  } else {
                      //
                      // jezeli wybrana kategoria nie jest ostatnia - wyswietla domyslna liste kategorii glownych
                      //
                      $KategorieAllegro = $AllegroRest->commandRequest('sale/categories', '', ''); 
                      //
                      if ( isset($KategorieAllegro->categories) ) {
                           //
                           foreach ( $KategorieAllegro->categories as $KategoriaAllegro ) {
                               //
                               $ListaKategorii .= '<li data-id="' . $KategoriaAllegro->id . '" data-ostatnia="' . (($KategoriaAllegro->leaf == '1') ? '1' : '') . '">' . $KategoriaAllegro->name . '</li>';
                               //
                           }
                           //
                           $BlednaKategoria = true;
                           //                        
                      }
                      //                       
                  }
                  //
             }
             //
        } else {
             //
             if ( isset($KategorieAllegro->categories) ) {
                  //
                  foreach ( $KategorieAllegro->categories as $KategoriaAllegro ) {
                      //
                      $ListaKategorii .= '<li data-id="' . $KategoriaAllegro->id . '" data-ostatnia="' . (($KategoriaAllegro->leaf == '1') ? '1' : '') . '">' . $KategoriaAllegro->name . '</li>';
                      //
                  }
                  //
             }
             //
        } ?>
      
        <p>
          <label>Kategoria Allegro:</label>
          <input type="text" id="kategoria_allegro_widoczna" size="15" value="<?php echo (($BlednaKategoria == true) ? '' : $kategoria_id_allegro); ?>" disabled="disabled" />
          <input type="hidden" name="kategoria_allegro" id="kategoria_allegro" value="<?php echo (($BlednaKategoria == true) ? '' : $kategoria_id_allegro); ?>" />
        </p>  
        
        <?php unset($BlednaKategoria); ?>
        
        <div class="OknoKategorieAllegro">
                
            <div class="OknoKategorieAllegroSciezka"><?php echo $SciezkaKategorii; ?></div>
        
            <div class="OknoKategorieAllegroLista" <?php echo (( $OstatniElement == true ) ? 'style="display:none"' : ''); ?>>
            
                <ul><?php echo $ListaKategorii; ?></ul>
                        
            </div>
            
            <div class="cl"></div>
            
            <div class="WyborKategorie" <?php echo (( $OstatniElement == true ) ? 'style="display:block"' : ''); ?>>
            
                <span>zmień kategorię</span>
                
            </div>
            
            <?php unset($OstatniElement, $ListaKategorii, $SciezkaKategorii, $KategoriAllegro); ?>
            
        </div> 
                                              
        <script>
        $(document).ready(function() { 
            //
            DrzewoKategoriiAllegro( 2 );
            SciezkaKategorieAllegro();
            //
            $('.WyborKategorie').click(function() {
                $(this).hide();
                $('.OknoKategorieAllegroLista').stop().slideDown();
            });
            //            
        });    
        function DrzewoKategoriiAllegro( nr ) {
            //
            $('.OknoKategorieAllegro li').click(function() {
                //
                $('#kategoria_allegro').val('');
                $('#kategoria_allegro_widoczna').val('');
                //
                if ( $('#kt_' + nr).length ) {
                     $('#kt_' + nr).remove();
                }
                //
                WyswietlKategorieAllegro( $(this), nr );
                //
            });
            //
        }
        function WyswietlKategorieAllegro( element, nr ) {
            //
            $('.OknoKategorieAllegroSciezka').html( $('.OknoKategorieAllegroSciezka').html() + '<span data-id="' + $(element).attr('data-id') + '" data-ostatnia="' + $(element).attr('data-ostatnia') + '" data-nr="' + nr + '" id="kt_' + nr + '">' + $(element).html() + '</span>' );
            SciezkaKategorieAllegro();
            //
            $('#ekr_preloader').css('display','block');   
            //
            if ( $(element).attr('data-ostatnia') == '' ) {
                 //
                 $.post("ajax/allegro_dane_kategorie.php?tok=" + $('#tok').val(), { id : $(element).attr('data-id'), ostatnia : $(element).attr('data-ostatnia'), nr : nr }, function(data) {   
                         $('#ekr_preloader').css('display','none');
                         $('.OknoKategorieAllegroLista').html(data); 
                 });                                              
                 //
            } else {
                 //                                   
                 $('#ekr_preloader').css('display','none');
                 //
                 $('.OknoKategorieAllegroLista').stop().slideUp();
                 //
                 $('.WyborKategorie').show();                 
                 //
                 $('#kategoria_allegro').val($(element).attr('data-id'));
                 $('#kategoria_allegro_widoczna').val($(element).attr('data-id'));                 
                 //
            }
            //
        }
        function SciezkaKategorieAllegro() {
            //
            $('.OknoKategorieAllegroSciezka span').click(function() {
                //
                if ( $(this).attr('data-ostatnia') == '' ) {
                     //
                     $('#kategoria_allegro').val('');
                     $('#kategoria_allegro_widoczna').val('');
                     //
                     $('.OknoKategorieAllegroLista').stop().slideDown();
                     $('.WyborKategorie').hide();    
                     //
                }
                //
                var ilt = $('.OknoKategorieAllegroSciezka span').length;
                for ( x = parseInt($(this).attr('data-nr')); x <= ilt; x++ ) {
                      if ( $('#kt_' + x).length ) {
                           $('#kt_' + x).remove();
                      }
                }
                //                                  
                WyswietlKategorieAllegro( $(this), parseInt($(this).attr('data-nr')) );
                //
            });
            //
        }        
        </script>
        
        <?php } else { ?>
        
        <input type="hidden" name="kategoria_allegro" value="0" />
        
        <?php } ?>

        <span class="maleInfo">Nazwa produktu do Allegro. Jeżeli nazwa nie będzie podana przy wystawianiu aukcji będzie pobierana nazwa główna produktu.</span>        
      
        <p>
          <label for="nazwa_allegro">Nazwa produktu na aukcję:</label>
          <input type="text" name="nazwa_allegro" id="nazwa_allegro" onkeyup="licznik_znakow(this,'iloscZnakowAllegro',75)" size="60" value="<?php echo $products_name_allegro; ?>" />
        </p> 
        
        <p>
          <label></label>
          <span style="display:inline-block; margin:0px 0px 8px 4px">Ilość znaków do wpisania: <span class="iloscZnakow" id="iloscZnakowAllegro"><?php echo (75 - strlen(mb_convert_encoding((string)$products_name_allegro, 'ISO-8859-1', 'UTF-8'))); ?></span></span>
        </p>        

        <span class="maleInfo">Indywidualne zdjęcie produktu do Allegro. Jeżeli zdjęcie nie będzie wybrane przy wystawianiu aukcji będzie pobierane główne zdjęcie produktu.</span>        
      
        <p>
          <label for="foto">Ścieżka zdjęcia:</label>
          <input type="text" name="zdjecie_allegro" size="60" value="<?php echo $products_image_allegro; ?>" class="obrazek" ondblclick="openFileBrowser('foto','','<?php echo KATALOG_ZDJEC; ?>')" id="foto" autocomplete="off" />                 
          <em class="TipIkona"><b>Kliknij dwukrotnie w pole żeby otworzyć okno przeglądarki zdjęć</b></em>
          <span class="usun_zdjecie TipChmurka" data-foto="foto"><b>Kliknij w ikonę żeby usunąć przypisane zdjęcie</b></span>
          <span class="PrzegladarkaZdjec TipChmurka" onclick="openFileBrowser('foto','','<?php echo KATALOG_ZDJEC; ?>')"><b>Kliknij żeby otworzyć okno przeglądarki zdjęć</b></span>
        </p>      

        <div id="divfoto" style="padding-left:10px;display:none">
            <label>Zdjęcie:</label>
            <span id="fofoto">
                <span class="zdjecie_tbl">
                    <img src="obrazki/_loader_small.gif" alt="" />
                </span>
            </span> 

            <?php if (!empty($products_image_allegro)) { ?>
            <script>           
            pokaz_obrazek_ajax('foto', '<?php echo $products_image_allegro; ?>')
            </script> 
            <?php } ?>  
            
        </div>        
      
        <span class="maleInfo">Cena brutto do Allegro. Jeżeli cena nie będzie podana przy wystawianiu aukcji będzie pobierana cena główna produktu.</span>        
      
        <p>
          <label for="cena_brutto_allegro">Cena brutto na aukcję:</label>
          <input type="text" name="cena_brutto_allegro" id="cena_brutto_allegro" class="kropkaPusta" size="10" value="<?php echo (($products_price_allegro > 0) ? $products_price_allegro : ''); ?>" /> w zł
        </p>     

        <span class="maleInfo">Waga z opakowaniem do Allegro.</span>        
      
        <p>
          <label for="waga_allegro">Waga z opakowaniem:</label>
          <input type="text" name="waga_allegro" id="waga_allegro" class="kropkaPusta" size="10" value="<?php echo (($products_weight_allegro > 0) ? $products_weight_allegro : ''); ?>" /> w kg
        </p>          
      
        <span class="maleInfo">Opis wykorzystywany do Allegro. Jeżeli opis nie będzie wypełniony przy wystawianiu aukcji będzie pobierany główny opis produktu.</span>
    
        <?php
        $tresc_opisu = '';
        
        if ( strpos((string)$products_description_allegro, '{') > -1 && strlen((string)$products_description_allegro) > 20 ) {
             $tresc_opisu = @unserialize($products_description_allegro);
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
              if ( typ == 'listing' || typ == 'zdjecie_listing' || typ == 'listing_zdjecie' ) {
                   opis = $('#Opis_nr_' + nr).find('textarea').val();
              }
              //
              if ( typ == 'zdjecie' || typ == 'zdjecie_listing' || typ == 'listing_zdjecie' || typ == 'zdjecie_zdjecie' ) {
                   //
                   var e = 1;
                   $('#Opis_nr_' + nr).find('.ListaFoto').each(function() {
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
                       //
                       e++;
                       //
                   });
                   //
              }
              //
              $.post('ajax/allegro_dane_opis.php?tok=' + $('#tok').val(), { foto_1_img: foto_1_img, foto_2_img: foto_2_img, foto_1_input: foto_1_input, foto_2_input: foto_2_input, opis: opis, nr_losowy: nr_losowy, nr: nr, typ: typ, zmiana_trybu: 'tak', produkt: 'tak' }, function(data) {
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
            $.post('ajax/allegro_dane_opis.php?tok=' + $('#tok').val(), { nr_losowy: nr_losowy, nr: 1, typ: 'listing', zmiana_trybu: 'nie', produkt: 'tak' }, function(data) {
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
                   var nowy_nr = parseInt($('#licznik_wierszy_opisu').val()) + 1;
                   var nr_losowy = Math.floor(Math.random() * 10000000) + 1000;
                   $('#licznik_wierszy_opisu').val( nowy_nr );
                   //
                   $('.WierszeOpisu').append('<div id="Opis_nr_' + nowy_nr + '" class="OpisWystawianieAukcji"></div>');
                   //
                   $.post('ajax/allegro_dane_opis.php?tok=' + $('#tok').val(), { nr_losowy: nr_losowy, nr: nowy_nr, typ: 'listing', zmiana_trybu: 'nie', produkt: 'tak' }, function(data) {
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
                  var idimg = $(this).attr('id');
                  $(this).attr('name', 'opis_img[' + t + ']');
                  //
                  if ( idimg.indexOf('_a_') > -1 ) {
                       $(this).attr('name', 'opis_img[' + t + '][1]'); 
                  }
                  if ( idimg.indexOf('_b_') > -1 ) {
                       $(this).attr('name', 'opis_img[' + t + '][2]'); 
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
        <input type="hidden" name="opis_typ_allegro" value="nowy" />
                                  
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
                   $edycja_zakladki_allegro = 'tak';
                   //
                   include('ajax/allegro_dane_opis.php');
                   //
                   $zawartosc_wiersza = ob_get_contents();
                   //
                   ob_end_clean();
                   //
                   echo '<div id="Opis_nr_' . $t . '" class="OpisWystawianieAukcji"><div class="NrWiersza">Wiersz nr <b>' . $t . '</b></div>';
                       //
                       echo str_replace(ADRES_URL_SKLEPU . '/' . KATALOG_ZDJEC . '/', '', $zawartosc_wiersza);
                       //
                   echo '</div>';

                   unset($zawartosc_wiersza, $nr, $nr_losowy, $typ, $zmiana_trybu, $dane_opisu, $edycja_zakladki_allegro);
                       
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

        <div class="objasnienia" id="ObjasnieniaNowyOpis">
        
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
        unset($tresc_opisu, $products_description_allegro, $products_price_allegro, $products_name_allegro);                      
        ?>   
        
    </div> 
    
<?php } ?>