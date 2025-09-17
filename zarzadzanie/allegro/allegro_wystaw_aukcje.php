<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    //jezeli nie ma ustawionego ID produktu przekierowanie na strone listy produktow
    if ( isset($_GET['id_poz']) && $_GET['id_poz'] == '0' ) {
      Funkcje::PrzekierowanieURL('../produkty/produkty.php');
    }
    
    if ( Funkcje::SprawdzAktywneAllegro() ) {

         echo '';
         
    } else {
    
         Funkcje::PrzekierowanieURL('../produkty/produkty.php');
    
    }    

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    
    if ( isset($_SESSION['allegro_produkt']) && !isset($_GET['szukaj']) ) {
         unset($_SESSION['allegro_produkt']);
         unset($_SESSION['allegro_produkt_szukaj']);
         unset($_SESSION['allegro_produkt_zakres']);
    }
    
    if ( !isset($_GET['id_poz']) ) {
         $_GET['id_poz'] = 0;
    }        
    
    $id_produktu = (int)$_GET['id_poz'];
    
    // sprawdzi czy nie jest to zestaw
    $zapytanie = 'SELECT DISTINCT products_set FROM products WHERE products_id = "' . $id_produktu . '"'; 
    $sql = $db->open_query($zapytanie);
    //
    if ((int)$db->ile_rekordow($sql) > 0) {
        //
        $info = $sql->fetch_assoc();
        //
        if ( $info['products_set'] != '0' ) {
             //
             Funkcje::PrzekierowanieURL('../produkty/zestawy_produktow.php');
             //
        }
        //
    }
    
    $db->close_query($sql);
    unset($zapytanie); 
                  
    $zapytanie = 'SELECT DISTINCT p.products_id, 
                                  p.products_image,
                                  p.products_model,
                                  p.products_ean,
                                  p.products_price_tax,
                                  p.products_quantity,
                                  p.products_pkwiu,
                                  p.products_tax_class_id,
                                  p.products_currencies_id,
                                  p.options_type,
                                  pd.products_id, 
                                  pd.language_id, 
                                  pd.products_name,
                                  pd.products_description
                             FROM products p, products_description pd
                            WHERE pd.products_id = p.products_id AND pd.language_id = "' . (int)$_SESSION['domyslny_jezyk']['id'] . '" AND p.products_id = "' . (int)$id_produktu . '"'; 
                
    $sql = $db->open_query($zapytanie);
    $wybor_szablonu = true;
    //
    $zdjecia_opisu = array();
    $tresc_opisu = '';
    //
    $AllegroRest = new AllegroRest( array('allegro_user' => $_SESSION['domyslny_uzytkownik_allegro']) );
    ?>
    
    <div id="naglowek_cont">Obsługa Allegro</div>
    
    <div id="cont">
    
        <div class="poleForm cmxform" style="margin-bottom:10px">
        
            <div class="naglowek">Ustawienia konfiguracji połączenia i wystawiania Allegro</div>

            <div class="pozycja_edytowana">
              
                <?php require_once('allegro_naglowek.php'); ?>
                                
                <?php
                $AllegroRest = new AllegroRest( array('allegro_user' => $_SESSION['domyslny_uzytkownik_allegro']) );            
                //
                if ( !isset($_GET['szablon']) ) {
                  
                    $sql_szablon = $db->open_query("select distinct * from allegro_theme");   
                    //
                    if ((int)$db->ile_rekordow($sql_szablon) > 0) { ?>
                    
                        <div class="info_content">
                        
                            <div id="SzablonyWystawianieAukcji">
                            
                                <strong>Wybierz szablon w oparciu o który chcesz wystawić aukcję</strong>

                                <div class="PodgladSzablonu">
                                
                                    <a href="allegro/allegro_wystaw_aukcje.php?id_poz=<?php echo $id_produktu; ?>&szablon=brak">
                                    
                                        <span></span><br />--- brak szablonu ---
                                    
                                    </a>
                                    
                                </div>
                                    
                                <?php while ($infe = $sql_szablon->fetch_assoc()) { ?>
                                  
                                    <div class="PodgladSzablonu">
                                    
                                        <a href="allegro/allegro_wystaw_aukcje.php?id_poz=<?php echo $id_produktu; ?>&szablon=<?php echo $infe['allegro_theme_id']; ?>">
                                        
                                            <span></span><br /><?php echo $infe['allegro_theme_name']; ?>
                                        
                                        </a>
                                        
                                    </div>

                                <?php } ?>
                                
                                <div class="cl"></div>
                                
                            </div>   

                        </div>

                        <?php 
                      
                        $wybor_szablonu = false;
                    
                    } else {
                        
                        $wybor_szablonu = true;
                         
                    }
                    $db->close_query($sql_szablon);
                    
                }
                
                // jezeli jest wybrany szablon
                if ( isset($_GET['szablon']) && (int)$_GET['szablon'] > 0 ) {
                     //
                     $sql_szablon = $db->open_query("select distinct * from allegro_theme where allegro_theme_id = '" . (int)$_GET['szablon'] . "'"); 
                     //
                     if ((int)$db->ile_rekordow($sql_szablon) > 0) {
                          //
                          $infe = $sql_szablon->fetch_assoc();
                          //
                          if ( strpos((string)$infe['allegro_theme_description'], '{') > -1 && strlen((string)$infe['allegro_theme_description']) > 20 ) {
                               //
                               $tresc_opisu = @unserialize($infe['allegro_theme_description']);
                               //
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
                                     for ( $r = 1; $r <= count($tresc_opisu); $r++ ) {
                                           //
                                           if ( $tresc_opisu[$r][0] == 'zdjecie_listing' ) {
                                                //
                                                if ( strpos((string)$tresc_opisu[$r][1][0], '[ZDJECIE_') === false ) {
                                                     //
                                                     $zdjecia_opisu[] = $tresc_opisu[$r][1][0];
                                                     //
                                                }
                                                //
                                           }
                                           if ( $tresc_opisu[$r][0] == 'listing_zdjecie' ) {
                                                //
                                                if ( strpos((string)$tresc_opisu[$r][1][1], '[ZDJECIE_') === false ) {
                                                     //
                                                     $zdjecia_opisu[] = $tresc_opisu[$r][1][1];
                                                     //
                                                }
                                                //
                                           }  
                                           if ( $tresc_opisu[$r][0] == 'zdjecie' ) {
                                                //
                                                if ( strpos((string)$tresc_opisu[$r][1][0], '[ZDJECIE_') === false ) {
                                                     //
                                                     $zdjecia_opisu[] = $tresc_opisu[$r][1][0];
                                                     //
                                                }
                                                //
                                           }                                
                                           if ( $tresc_opisu[$r][0] == 'zdjecie_zdjecie' ) {
                                                //
                                                if ( strpos((string)$tresc_opisu[$r][1][0], '[ZDJECIE_') === false ) {
                                                     //
                                                     $zdjecia_opisu[] = $tresc_opisu[$r][1][0];
                                                     //
                                                }
                                                if ( strpos((string)$tresc_opisu[$r][1][1], '[ZDJECIE_') === false ) {
                                                     //
                                                     $zdjecia_opisu[] = $tresc_opisu[$r][1][1];
                                                     //
                                                }                                     
                                                //
                                           }                                   
                                           //
                                     }      
                                     //
                               }
                               //
                          }
                          //
                     }
                     //
                     $db->close_query($sql_szablon);
                     //
                }
                ?>

            </div>     
              
        </div>

        <?php if ( $wybor_szablonu == true ) { ?>
        
        <form action="/" method="post" id="allegroForm" class="cmxform"> 

          <div>
              <input type="hidden" name="akcja" value="zapisz" />
              <input type="hidden" name="produkt_id" id="produkt_id" value="<?php echo $id_produktu; ?>" />
          </div>
          
          <div class="poleForm">
          
              <div class="naglowek">Wystawianie aukcji</div>
              
              <?php 
              if ((int)$db->ile_rekordow($sql) > 0) {
        
                  $info = $sql->fetch_assoc();
                  
                  if ( $tresc_opisu == '' ) {
                       //
                       // standardowy opis produktu
                       $tresc_opisu = array( '1' => array( 'listing' , array( $info['products_description'] )) );
                       // 
                  } 
                  
                  $waga_ajax = 0;
                  
                  // dodatkowe parametry allegro dla produktu
                  $zapytanie_tmp = "select * from products_allegro_info where products_id = '" . $id_produktu . "'";
                  $sqls = $db->open_query($zapytanie_tmp);
                  
                  $id_kategorii = 0;
                  $zdjecie_glowne = $info['products_image'];

                  if ((int)$db->ile_rekordow($sqls) > 0) {
                      //
                      $dane_allegro = $sqls->fetch_assoc();
                      //
                      // jezeli nie jest wybrany szablon
                      if ( (isset($_GET['szablon']) && $_GET['szablon'] == 'brak') || !isset($_GET['szablon']) ) {
                           //                      
                           if ( isset($dane_allegro['products_description_allegro']) && strpos((string)$dane_allegro['products_description_allegro'], '{') > -1 && strlen((string)$dane_allegro['products_description_allegro']) > 20 ) {
                                //
                                $tresc_opisu = @unserialize($dane_allegro['products_description_allegro']);
                                //
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
                                     for ( $r = 1; $r <= count($tresc_opisu); $r++ ) {
                                          //
                                          if ( $tresc_opisu[$r][0] == 'zdjecie_listing' ) {
                                               //
                                               $zdjecia_opisu[] = $tresc_opisu[$r][1][0];
                                               //
                                          }
                                          if ( $tresc_opisu[$r][0] == 'listing_zdjecie' ) {
                                               //
                                               $zdjecia_opisu[] = $tresc_opisu[$r][1][1];
                                               //
                                          }  
                                          if ( $tresc_opisu[$r][0] == 'zdjecie' ) {
                                               //
                                               $zdjecia_opisu[] = $tresc_opisu[$r][1][0];
                                               //
                                          }                                
                                          if ( $tresc_opisu[$r][0] == 'zdjecie_zdjecie' ) {
                                               //
                                               $zdjecia_opisu[] = $tresc_opisu[$r][1][0];
                                               $zdjecia_opisu[] = $tresc_opisu[$r][1][1];                                 
                                               //
                                          }                                   
                                          //
                                    }
                                    //
                                }
                                //
                           }
                           //
                      }
                      if ( isset($dane_allegro['products_price_allegro']) && $dane_allegro['products_price_allegro'] > 0 ) {
                           $info['products_price_tax'] = $dane_allegro['products_price_allegro'];
                        } else {
                           $info['products_price_tax'] = $waluty->FormatujCeneBezSymbolu($info['products_price_tax'], true, '', '', '2', $info['products_currencies_id']);
                      }
                      //
                      if ( isset($dane_allegro['products_name_allegro']) && !empty($dane_allegro['products_name_allegro']) ) {
                           $info['products_name'] = Funkcje::formatujTekstInput($dane_allegro['products_name_allegro']);
                      }
                      //
                      if ( isset($dane_allegro['products_image_allegro']) && !empty($dane_allegro['products_image_allegro']) ) {
                           $zdjecie_glowne = $dane_allegro['products_image_allegro'];
                      } 
                      //
                      if ( isset($dane_allegro['products_weight_allegro']) && $dane_allegro['products_weight_allegro'] > 0 ) {
                           $waga_ajax = $dane_allegro['products_weight_allegro'];
                      }
                      //
                      //
                      if ( isset($dane_allegro['products_cat_id_allegro']) && $dane_allegro['products_cat_id_allegro'] > 0 ) {
                          $id_kategorii = $dane_allegro['products_cat_id_allegro'];
                      }
                      //
                      unset($dane_allegro);
                      //
                  }

                  $db->close_query($sqls);
                  unset($zapytanie_tmp);                  
                  //
                  $nazwa_produktu = mb_substr((string)$info['products_name'],0,75);
                  
                  // jezeli jest wybrany produkt allegro
                  if ( isset($_SESSION['allegro_produkt']) ) {
                       //
                       $id_kategorii = $_SESSION['allegro_produkt']->category->id;
                       if ( isset($_SESSION['allegro_produkt_zakres']) && $_SESSION['allegro_produkt_zakres'] == 'ean' ) {
                            $info['products_ean'] = $_SESSION['allegro_produkt_szukaj'];
                       }
                       //
                  }
                  ?>

                  <div class="pozycja_edytowana">
                  
                      <div class="info_content">
                      
                          <div class="WgrywanieZdjecAllegro">

                              <?php
                              $tablica_zdjec = array();
                              $tablica_zdjec_produktu = array();
                              //
                              // pierwsze zdjecie - zdjecie przypisane do produktu allegro
                              if ( $zdjecie_glowne != '' && $zdjecie_glowne != $info['products_image'] && is_file('../' . KATALOG_ZDJEC . '/' . $zdjecie_glowne) ) {
                                  //
                                  $tablica_zdjec[] = $zdjecie_glowne;
                                  //
                              } else {
                                  //
                                  $tablica_zdjec[] = 'brak_foto';
                                  //
                              }

                              // drugie zdjecie - glowne zdjecie produktu
                              if ( $info['products_image'] != '' && is_file('../' . KATALOG_ZDJEC . '/' . $info['products_image']) ) {
                                   //                 
                                   $tablica_zdjec[] = $info['products_image'];    
                                   $tablica_zdjec_produktu[] = $info['products_image'];  
                                   //
                              } else {
                                  //
                                  $tablica_zdjec[] = 'brak_foto';
                                  $tablica_zdjec_produktu[] = 'domyslny.webp';
                                  //
                              }                               

                              // zdjecia dodatkowe
                              $zapytanie_zdjecia = "SELECT * FROM additional_images WHERE products_id = '".$id_produktu."' order by sort_order";
                              $sql_zdjecia = $db->open_query($zapytanie_zdjecia); 

                              if ((int)$db->ile_rekordow($sql_zdjecia) > 0 || $info['products_image'] != '') {
                                
                                  while ( $info_zdjecia = $sql_zdjecia->fetch_assoc()) {
                                  
                                      if ( is_file('../' . KATALOG_ZDJEC . '/' . $info_zdjecia['popup_images']) ) {
                                           //
                                           $tablica_zdjec[] = $info_zdjecia['popup_images'];
                                           $tablica_zdjec_produktu[] = $info_zdjecia['popup_images'];
                                           //
                                      } else {
                                          //
                                          $tablica_zdjec[] = 'brak_foto';
                                          $tablica_zdjec_produktu[] = 'domyslny.webp';
                                          //
                                      }

                                  }                       

                              }
                                                            
                              $db->close_query($sql_zdjecia);
                              unset($zapytanie_zdjecia, $info_zdjecia); 

                              // dodatkowe zdjecia w przypadku szablonu
                              if ( isset($zdjecia_opisu) && count($zdjecia_opisu) ) {
                                   //
                                   foreach ( $zdjecia_opisu as $zdjecie ) {
                                        //
                                        if ( is_file('../' . KATALOG_ZDJEC . '/' . $zdjecie) ) {
                                             //
                                             if ( !in_array($zdjecie, $tablica_zdjec) ) {
                                                  $tablica_zdjec[] = $zdjecie;
                                             }
                                             //
                                        } else {
                                            //
                                            $tablica_zdjec[] = 'brak_foto';
                                            //
                                        }
                                        //
                                   }
                                   //
                              }
                              
                              $ciag_zdjec = '';
                              
                              foreach ($tablica_zdjec as $nr_zdjecia => $zdjecie ) {
                                
                                   if ( $zdjecie != 'brak_foto' ) {
                                     
                                        $ciag_zdjec .= '<div class="ZdjecieRestAllegro" id="zdjecie_nr_' . $nr_zdjecia . '" data-url-foto="' . '/' . KATALOG_ZDJEC . '/' . $zdjecie . '" data-url="' . ADRES_URL_SKLEPU . '/' . KATALOG_ZDJEC . '/' . $zdjecie . '">
                                                             <span data-nr="' . $nr_zdjecia . '"></span><img src="/' . KATALOG_ZDJEC . '/' . $zdjecie . '" alt="" /><input type="hidden" name="images[]" value="' . ADRES_URL_SKLEPU . '/' . KATALOG_ZDJEC . '/' . $zdjecie . '" />
                                                        </div>';        
                                                         
                                   } else {
                                     
                                        $ciag_zdjec .= '<div class="ZdjecieRestAllegro" id="zdjecie_nr_' . $nr_zdjecia . '" data-url-foto="" data-url="">
                                                             <span data-nr="' . $nr_zdjecia . '"></span><img src="obrazki/kasuj.png" alt="" /><input type="hidden" name="images[]" value="" />
                                                        </div>';        

                                   }                                                         
                                        
                              }
                              ?>      
                              
                              <script type="text/javascript" src="javascript/jquery-ui.js"></script>
                                                            
                              <script>
                              <?php
                              $tablica_zdjec_js = array();
                              //
                              foreach ( $tablica_zdjec as $tmp ) {
                                  //
                                  $tablica_zdjec_js[] = '"' . $tmp . '"';
                                  //
                              }                             
                              ?>
                              var tablica_zdjec = new Array( <?php echo implode(',' , (array)$tablica_zdjec_js); ?> );
                              var tablica_zdjec_produktu = new Array();
                              <?php
                              $r = 0;
                              foreach ( $tablica_zdjec_produktu as $tmp ) {
                                  //
                                  echo 'tablica_zdjec_produktu[' . $r . '] = "/' . KATALOG_ZDJEC . '/' . $tmp . '"' . "\n";
                                  $r++;
                                  //
                              }                               
                              ?>
                              
                              $(document).ready(function() {

                                 // podstawi zdjecia
                                 $('.ListaFoto').each(function() {
                                    //
                                    for ( poczatek = 0; poczatek < tablica_zdjec_produktu.length; poczatek++ ) {
                                      
                                        if ( $(this).find('input').val() == '[ZDJECIE_' + ( poczatek + 1 ) + ']' ) {
                                             //
                                             // jezeli jest zdjecie
                                             if ( tablica_zdjec_produktu[poczatek] != undefined ) {
                                                  //
                                                  $(this).find('img').attr('src', tablica_zdjec_produktu[poczatek]);
                                                  $(this).find('input').val('<?php echo ADRES_URL_SKLEPU; ?>' + tablica_zdjec_produktu[poczatek]);
                                                  //
                                             } else {
                                                  //
                                                  $(this).find('img').attr('src', 'obrazki/allegro_foto.png');
                                                  //
                                             }
                                             //
                                        }
                                        
                                    }
                                    //
                                 });          
                                 
                                 // usunie puste obrazki
                                 $('.ZdjecieRestAllegro').each(function() {
                                     //
                                     if ( $(this).attr('data-url') == '' ) {
                                          $(this).remove();
                                     }
                                     //
                                 });
                                 //
                                 // czy sa zdjecia produktu
                                 if ( $('.ZdjeciaRestAllegro span').length == 0 ) { 
                                      //
                                      $('.ZdjeciaRestAllegro').html('<b class="BrakZdjecAukcji">Brak zdjęć do wyboru ...</b>');
                                      $('#same_zdjecia_allegro').prop('checked', true);
                                      //
                                 }
                                 //
                                 $('.WgrywanieZdjecAllegro').stop().slideUp("fast");
                                 $('.DaneAukcji').stop().slideDown("fast");
                                 KategoriaProduktu();
                                 //
                                 if ( $('.ZdjeciaRestAllegro span').length > 0 ) { 
                                      //
                                      $(".ZdjeciaRestAllegro").sortable({ 
                                          opacity: 0.6, 
                                          cursor: 'move', 
                                          update: function() {
                                              var order = $(this).sortable("serialize");                     															 
                                          }								  
                                      });	
                                      $(".ZdjeciaRestAllegro").disableSelection();
                                      //
                                      $('.ZdjecieRestAllegro span').click(function() {
                                          //
                                          var id_zdjecie = $(this).attr('data-nr');
                                          //
                                          if ( $('.ZdjecieRestAllegro').length > 1 ) {
                                               //
                                               var zrodlo = $('#zdjecie_nr_' + id_zdjecie).find('img').attr('src');
                                               //
                                               $('.ListaFoto').each(function() {
                                                  //
                                                  if ( $(this).find('img').attr('src') == zrodlo ) {
                                                       //
                                                       $(this).find('img').attr('src','/zarzadzanie/obrazki/allegro_foto.png');
                                                       $(this).find('input').val('');
                                                       //
                                                  }
                                                  //
                                               });
                                               //
                                               $('#zdjecie_nr_' + id_zdjecie).remove();
                                               //
                                          } else {
                                               //
                                               $.colorbox( { html:'<div id="PopUpInfo">Nie można usunąć wszystkich zdjęć.', initialWidth:50, initialHeight:50, maxWidth:'90%', maxHeight:'90%' } );
                                               //
                                          }
 
                                      });  
                                      //
                                 }
                                
                                 // podstawi puste zdjecia po pozycje gdzie nie ma zdjec
                                 $('.ListaFoto').each(function() {
                                     //
                                     if ( $(this).find('img').attr('src').indexOf('[ZDJECIE_') > -1 ) {
                                          //
                                          $(this).find('img').attr('src', 'obrazki/allegro_foto.png');
                                          //
                                     }
                                     //
                                 });                                                                                           
                                          
                              });
                              </script>                               
                          
                          </div>
                          
                          <div class="DaneAukcji">
                          
                              <div class="DaneAukcjiUkryj"></div>

                              <script>
                              $(document).ready(function() {
                                  $("#allegroForm").validate({
                                    rules: {
                                      nazwa_produktu: {
                                        required: true
                                      },
                                      cena_produktu: {
                                        required: true,
                                        range: [1, 100000],
                                      },
                                      ilosc_produktu: {
                                        required: true,
                                        range: [1, 100000]
                                      }                             
                                    },
                                    messages: {
                                       nazwa_produktu: {
                                        required: "Pole jest wymagane."
                                      },
                                      cena_produktu: {
                                        required: "Pole jest wymagane.",
                                        range: "Cena musi być większa lub równa 1 zł"
                                      },
                                      ilosc_produktu: {
                                        required: "Pole jest wymagane.",
                                        range: "Minimalna ilość przedmiotów do wystawienia to 1"
                                      }                                    
                                    },
                                    submitHandler: function() {
                                      // ile jest zdjec
                                      if ( $('.ZdjecieRestAllegro').length > 0 ) {
                                           //
                                           if ( $('.ZdjecieRestAllegro').length > <?php echo (($AllegroRest->ParametryPolaczenia['ClientType'] == 'F') ? 18 : 10 ); ?> ) {
                                                $('#ilosc_zdjec').show();
                                                return false;
                                           }
                                           //
                                      }
                                      // rodzaj produktu
                                      if ( $('#nowy_produkt_nie').prop('checked') == true && $('#id_produktu_allegro').length == 0 ) {
                                           //
                                           $.colorbox( { html:'<div id="PopUpInfo">Nie został wybrany produkt z katalogu Allegro.</div>', initialWidth:50, initialHeight:50, maxWidth:'90%', maxHeight:'90%' } );
                                           return false;
                                           //
                                      }
                                      //
                                      $('#ekr_preloader').css('display','block');                                      
                                      //
                                      for ( instance in CKEDITOR.instances ) {
                                            CKEDITOR.instances[instance].updateElement();
                                      }
                                      //
                                      var dane_aukcji = $('#allegroForm').serialize(); 
                                      //
                                      $.post("ajax/allegro_wystaw_aukcje.php?tok=" + $('#tok').val(), { data: dane_aukcji }, function(data) { 
                                          $('#ekr_preloader').css('display','none');      
                                          $('#WynikAukcja').html(data);
                                      });                                                                              
                                    }                                       
                                  });
                              });   
                              </script>
                          
                              <input type="hidden" value="<?php echo $info['products_id']; ?>" name="id_produktu" id="id_produktu" />
                              <input type="hidden" value="<?php echo $info['options_type']; ?>" name="typ_cech" />
                              <input type="hidden" value="<?php echo $zdjecie_glowne; ?>" name="zdjecie_produktu" />
                                
                              <?php if ( isset($_SESSION['allegro_produkt']) ) { ?>
                              
                              <div style="display:none">
                              
                                  <p>
                                    <label for="id_produktu_allegro">Id produktu allegro:</label>
                                    <input name="id_produktu_allegro" id="id_produktu_allegro" type="text" value="<?php echo $_SESSION['allegro_produkt']->id; ?>" size="50" />
                                  </p>  
                                  
                              </div>
                              
                              <div class="PobranyProdukt"><div style="margin:10px"><img src="obrazki/_loader.gif"></div></div>
                              
                              <script>
                              $(document).ready(function() {
                                
                                  var szukaj = $('#szukana_tresc').val();
                                  var zakres = $("input[name=szukana_tresc_rodzaj]:checked").val();
                                  
                                  $.post("ajax/allegro_lista_produktow.php?tok=" + $('#tok').val(), { szukaj: szukaj, zakres: zakres, id_poz: <?php echo $_GET['id_poz']; ?>, szablon: '<?php echo ((isset($_GET['szablon'])) ? $_GET['szablon'] : ''); ?>', id_produktu: '<?php echo $_SESSION['allegro_produkt']->id; ?>' }, function(data) {     
                                        $('.PobranyProdukt').html(data);
                                  });

                              });
                              </script>                              
                              
                              <?php } ?>

                              <?php if ( isset($_SESSION['allegro_produkt']) ) { ?>
                              
                                    <div style="display:none">
                                    
                              <?php } ?>
                              
                              <div class="DaneAukcjaNaglowek">Powiązanie produktu z katalogiem Allegro</div>
                              
                              <div class="OknoDiv">
                                 <input type="radio" name="nowy_produkt" value="1" id="nowy_produkt_tak" /><label class="OpisFor" for="nowy_produkt_tak" style="font-size:110%;font-weight:bold">dodaj produkt jako NOWY do katalogu allegro</label>
                              </div>                                
                              
                              <div class="OknoDiv">
                                 <input type="radio" name="nowy_produkt" value="0" checked="checked" id="nowy_produkt_nie" /><label class="OpisFor" for="nowy_produkt_nie" style="font-size:110%;font-weight:bold">wyszukaj produkt w katalogu allegro</label>
                              </div>        

                              <script>
                              $(document).ready(function() {
                                  $('#nowy_produkt_tak').click(function() {
                                      $('#WyszukiwanieAllegro').stop().slideUp();
                                  });
                                  $('#nowy_produkt_nie').click(function() {
                                      $('#WyszukiwanieAllegro').stop().slideDown();
                                  });
                              });
                              </script>
                              
                              <?php if ( isset($_SESSION['allegro_produkt']) ) { ?>
                              
                                    </div>
                                    
                              <?php } ?>   

                              <?php if ( isset($_SESSION['allegro_produkt']) ) { ?>
                              
                                    <div style="display:none">
                                    
                              <?php } ?>  
                              
                              <div id="WyszukiwanieAllegro" style="margin:10px;padding:10px;background:#f3f3f3;border-radius:5px">
                              
                                  <p>
                                    <label for="szukana_tresc">Szukana treść:</label>
                                    <input name="szukana_tresc" id="szukana_tresc"  type="text" value="<?php echo ((isset($_SESSION['allegro_produkt_szukaj'])) ? $_SESSION['allegro_produkt_szukaj'] : ''); ?>" size="50" />
                                  </p>                               
                                  
                                  <p>
                                    <label>Zakres wyszukiwania:</label>
                                    <input type="radio" name="szukana_tresc_rodzaj" id="szukana_tresc_rodzaj_ean" value="ean" <?php echo (((isset($_SESSION['allegro_produkt_zakres']) && $_SESSION['allegro_produkt_zakres'] == 'ean') || !isset($_SESSION['allegro_produkt_zakres'])) ? 'checked="checked"' : ''); ?> /> <label class="OpisFor" for="szukana_tresc_rodzaj_ean">kod EAN</label> 
                                    <input type="radio" name="szukana_tresc_rodzaj" id="szukana_tresc_rodzaj_kod_producenta" value="kod_producenta" <?php echo ((isset($_SESSION['allegro_produkt_zakres']) && $_SESSION['allegro_produkt_zakres'] == 'kod_producenta') ? 'checked="checked"' : ''); ?> /> <label class="OpisFor" for="szukana_tresc_rodzaj_kod_producenta">kod producenta</label>
                                    <input type="radio" name="szukana_tresc_rodzaj" id="szukana_tresc_rodzaj_nazwa" value="nazwa" <?php echo ((isset($_SESSION['allegro_produkt_zakres']) && $_SESSION['allegro_produkt_zakres'] == 'nazwa') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="szukana_tresc_rodzaj_nazwa">fraza, która dotyczy szukanego produktu</label>
                                  </p>                                
                                  
                                  <div id="PobierzProdukty" style="margin-top:10px">
                                      <p>
                                        <label></label>
                                        <span class="przyciskNon" style="margin:0">Pobierz produkty na podstawie wpisanej treści</span>
                                      </p>
                                  </div>

                                  <div class="PobraneProdukty"></div>
                                  
                              </div>
                              
                              <?php if ( isset($_SESSION['allegro_produkt']) ) { ?>
                              
                                    </div>
                                    
                              <?php } ?>                              

                              <?php $cssGpsr = 'display:none'; ?>

                              <?php if ( isset($_SESSION['allegro_produkt']) && !isset($_SESSION['allegro_produkt']->productSafety) ) { ?>
                              
                                    <?php $cssGpsr = ''; ?>

                              <?php } ?>
                              
                              <div id="DaneGpsr" style="<?php //echo $cssGpsr; ?>">
                              
                                  <div class="DaneAukcjaNaglowek">Dane GPSR</div>

                                  <p>
                                    <label>Produkt wprowadzono do obrotu w UE przed 13 grudnia 2024:</label> 
                                    <input type="checkbox" name="data_wprowadzenia_gpsr" value="1" id="data_wprowadzenia_gpsr" /><label class="OpisFor" for="data_wprowadzenia_gpsr">tak (przed 13 grudnia 2024)</label> 
                                  </p>

                                  <script>
                                  $(document).ready(function() {
                                      $('#info_gpsr_brak').click(function() {
                                          $('#PlikGpsr').stop().slideUp();
                                          $('#OpisGpsr').stop().slideUp();
                                      });
                                      $('#info_gpsr_plik').click(function() {
                                          $('#PlikGpsr').stop().slideDown();
                                          $('#OpisGpsr').stop().slideUp();
                                      });
                                      $('#info_gpsr_opis').click(function() {
                                          $('#PlikGpsr').stop().slideUp();
                                          $('#OpisGpsr').stop().slideDown();
                                      });                                      
                                  });
                                  </script>
                              
                                  <p>
                                    <label>Informacji o bezpieczeństwie:</label>
                                    <input type="radio" name="info_gpsr" id="info_gpsr_brak" value="brak" checked="checked" /> <label class="OpisFor" for="info_gpsr_brak">brak</label> 
                                    <input type="radio" name="info_gpsr" id="info_gpsr_plik" value="plik" /> <label class="OpisFor" for="info_gpsr_plik">plik PDF</label>
                                    <input type="radio" name="info_gpsr" id="info_gpsr_opis" value="opis" /> <label class="OpisFor" for="info_gpsr_opis">opis</label>
                                  </p>  
                                  
                                  <div id="PlikGpsr" style="display:none">
                                  
                                      <p>
                                        <label for="plik_gpsr">Plik informacji o bezpieczeństwie (PDF):</label>
                                        <input type="text" name="plik_gpsr" size="80" ondblclick="openFileAllBrowser('plik_gpsr')" id="plik_gpsr" value="" autocomplete="off" />
                                        <em class="TipIkona"><b>Kliknij dwukrotnie w pole obok żeby otworzyć okno przeglądarki</b></em>
                                        <span class="usun_plik TipChmurka" data-plik="plik_gpsr"><b>Kliknij w ikonę żeby usunąć przypisany plik</b></span>
                                      </p>
                                  
                                  </div>
                                  
                                  <div id="OpisGpsr" style="display:none">
                                            
                                      <p>
                                        <label style="margin-bottom:8px;width:auto;display:block">Opis tekstowy (dopuszczalna liczba znaków od 1 do 5000, nie obsługuje tagów HTML):</label>
                                        <textarea cols="50" rows="10" id="opis_gpsr" name="opis_gpsr" style="width:calc(100% - 20px)"></textarea>
                                      </p> 
                                  
                                  </div>    
                                  
                                  <script>
                                  $(document).ready(function() {
                                      $('#dodaj_producenta_gpsr').click(function() {
                                          if ( $(this).prop('checked') == true ) {
                                               $('#NowyProducentGpsr').stop().slideDown();
                                          } else { 
                                               $('#NowyProducentGpsr').stop().slideUp();
                                          }
                                      });                                      
                                  });
                                  </script>                                  
                                  
                                  <p>
                                    <label>Dodaj nowego producenta:</label> 
                                    <input type="checkbox" name="dodaj_producenta_gpsr" value="1" id="dodaj_producenta_gpsr" /><label class="OpisFor" for="dodaj_producenta_gpsr">dodaj</label> 
                                  </p>
                                  
                                  <div id="NowyProducentGpsr" style="display:none">
                                            
                                      <p>
                                        <label for="nazwa_producent">Nazwa własna (unikalna - niewidoczna w aukcjach):</label>
                                        <input type="text" name="nazwa_producent" size="80" id="nazwa_producent" value="" />
                                      </p>
                                  
                                      <p>
                                        <label for="nazwa_producent_gpsr">Nazwa producenta:</label>
                                        <input type="text" name="nazwa_producent_gpsr" size="80" id="nazwa_producent_gpsr" value="" />
                                      </p>
                                    
                                      <p>
                                        <label for="ulica_producent_gpsr">Ulica:</label>
                                        <input type="text" name="ulica_producent_gpsr" size="50" id="ulica_producent_gpsr" value="" />
                                      </p>
                                      
                                      <p>
                                        <label for="kod_producent_gpsr">Kod pocztowy:</label>
                                        <input type="text" name="kod_producent_gpsr" size="20" id="kod_producent_gpsr" value="" />
                                      </p>
                                      
                                      <p>
                                        <label for="miasto_producent_gpsr">Miasto:</label>
                                        <input type="text" name="miasto_producent_gpsr" size="50" id="miasto_producent_gpsr" value="" />
                                      </p> 
                                      
                                      <p>
                                        <label for="kraj_producent_gpsr">Kraj:</label>  
                                        <select name="kraj_producent_gpsr" id="kraj_producent_gpsr">
                                        <?php foreach ( Funkcje::KrajeIso() as $nazwa => $kod ) { ?>
                                            <option value="<?php echo $kod; ?>"><?php echo $nazwa; ?></option>                      
                                        <?php } ?>
                                        </select>
                                      </p>
                                        
                                      <p>
                                        <label for="email_producent_gpsr">Adres e-mail:</label>
                                        <input type="text" name="email_producent_gpsr" id="email_producent_gpsr" size="40" value="" />
                                      </p> 
                                      
                                      <p>
                                        <label for="telefon_producent_gpsr">Numer telefonu:</label>
                                        <input type="text" name="telefon_producent_gpsr" id="telefon_producent_gpsr" size="20" value="" />
                                      </p> 
                                      
                                      <div class="maleInfo">Jeżeli nie zostaną uzupełnione wszystkie dane producenta lub nazwa własna będzie już wcześniej dodana w Allegro zostanie wygenerowany błąd wystawiania aukcji.</div>
                
                                  </div>   
                                  
                              </div>
                              
                              <script>
                              $(document).ready(function() {
                                
                                  $('#PobierzProdukty span').click(function() {
                                      //
                                      var szukaj = $('#szukana_tresc').val();
                                      var zakres = $("input[name=szukana_tresc_rodzaj]:checked").val();
                                      
                                      if ( szukaj.length < 2 ) {
                                           $.colorbox( { html:'<div id="PopUpInfo">Minimalna ilość znaków w polu wyszukiwania to 2</div>', initialWidth:50, initialHeight:50, maxWidth:'90%', maxHeight:'90%' } );
                                           return false;
                                      }
    
                                      $('#ekr_preloader').css('display','block');
                                      $.post("ajax/allegro_lista_produktow.php?tok=" + $('#tok').val(), { szukaj: szukaj, zakres: zakres, id_poz: <?php echo $_GET['id_poz']; ?>, szablon: '<?php echo ((isset($_GET['szablon'])) ? $_GET['szablon'] : ''); ?>' }, function(data) {     
                                            $('#ekr_preloader').css('display','none'); 
                                            $('.PobraneProdukty').html(data);
                                      });
                                      //
                                  });
                                
                              });
                              </script>

                              <div class="DaneAukcjaNaglowek">Podstawowe dane produktu</div>
                  
                              <p>
                                <label class="required" for="nazwa_produktu">Tytuł aukcji:</label>
                                <input name="nazwa_produktu" id="nazwa_produktu" type="text" value="<?php echo $nazwa_produktu; ?>" maxlength="75" size="100" onkeyup="licznik_znakow(this,'iloscZnakowNazwa', 75)" />
                              </p> 
                              
                              <p>
                                <label for="nazwa_produktu">&nbsp;</label>
                                <span style="display:inline-block">Ilość znaków do wpisania: <span class="iloscZnakow" id="iloscZnakowNazwa"><?php echo (75-strlen(mb_convert_encoding((string)$nazwa_produktu, 'ISO-8859-1', 'UTF-8'))); ?></span></span>
                              </p>    

                              <?php
                              $zapytanie_cechy = "SELECT DISTINCT popt.products_options_id,
                                                                  popt.products_options_name 
                                                             FROM products_options popt, products_attributes patrib 
                                                            WHERE patrib.products_id='" . (int)$id_produktu . "' and patrib.options_id = popt.products_options_id and popt.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."' and patrib.options_values_id != '0' 
                                                         ORDER BY popt.products_options_sort_order asc";

                              $sql_cechy = $db->open_query($zapytanie_cechy);

                              if ((int)$db->ile_rekordow($sql_cechy) > 0) {
                                  
                                  ?>
                                  <script>
                                  $(document).ready(function() {
                                      $('.CechyDlaAllegro').change(function() {
                                          //
                                          var ileZaznaczone = 0;
                                          $('.CechyDlaAllegro').each(function() {
                                              //
                                              if ( $(this).val() != '0' ) {
                                                   ileZaznaczone++;
                                              }
                                              //
                                          });
                                          <?php if ( $info['options_type'] == 'cechy' ) { ?>
                                          //
                                          ileZaznaczone = $('.CechyDlaAllegro').length;
                                          //
                                          <?php } ?>
                                          //
                                          if ( ileZaznaczone == $('.CechyDlaAllegro').length ) {
                                               //
                                               $('#ekr_preloader').css('display','block');
                                               var sear = $('#allegroForm').serialize(); 
                                               $.post("ajax/allegro_dane_produktu.php?tok=" + $('#tok').val(), { data: sear }, function(data) {     
                                                      $('#ekr_preloader').css('display','none');
                                                      // kod ean
                                                      if ( data.ean != '' ) {
                                                           $('#ean').val( data.ean );
                                                      } else {
                                                           $('#ean').val( $('#ean').attr('data-ean') );
                                                      }
                                                      // cena
                                                      if ( data.cena != '' ) {
                                                           $('#cena_produktu').val( data.cena );
                                                      } else {
                                                           $('#cena_produktu').val( $('#cena_produktu').attr('data-cena') );
                                                      }  
                                                      // ilosc
                                                      if ( data.ilosc != '' ) {
                                                           $('#ilosc_produktu').val( data.ilosc );
                                                      } else {
                                                           if ( $('#kontrola_cech_aktywna').val() == 'tak' ) {
                                                                $('#ilosc_produktu').val( 0 );
                                                           } else {
                                                               // podstawia ilosc domyslna
                                                               $('#ilosc_produktu').val( $('#ilosc_produktu').attr('data-ilosc') );
                                                           }
                                                      }                                                      
                                               }, "json");                                              
                                               //
                                               var ciag_cech = new Array();
                                               $('.CechyDlaAllegro').each(function() {
                                                  //
                                                  if ( $(this).val() != '0' ) {
                                                       //
                                                       ciag_cech[ $(this).attr('data-id') ] = 'x' + $(this).attr('data-id') + '-' + $(this).val();
                                                       //
                                                  }
                                                  //
                                               });
                                               $('#cechy_ukryte').val( $('#id_produktu').val() + ciag_cech.join('') );
                                               $('#id_zewentrzne').val( $('#id_produktu').val() + ciag_cech.join('') );
                                              //
                                          }
                                          //
                                      });
                                  });
                                  </script>
                                  <?php
                                
                                  echo '<input type="hidden" id="kontrola_cech_aktywna" value="' . ((MAGAZYN_SPRAWDZ_STANY == 'tak' && CECHY_MAGAZYN == 'tak') ? 'tak' : 'nie') . '" name="kontrola_cech" />';

                                  while ( $info_cechy = $sql_cechy->fetch_assoc() ) {
                                    
                                      $tablica_cechy = Funkcje::ListaWartosciCechyProduktuDlaAllegro($info['products_id'], $info_cechy['products_options_id']);

                                      echo '<p>';
                                      echo '<label class="required" for="cecha_' . $info_cechy['products_options_id'] . '">' . $info_cechy['products_options_name'] . ':</label>';
                                      echo Funkcje::RozwijaneMenu('cecha[' . $info_cechy['products_options_id'] . ']', $tablica_cechy, '', 'data-id="' . $info_cechy['products_options_id'] . '" id="cecha_' . $info_cechy['products_options_id'] . '" style="width:250px;margin-left:3px;" class="CechyDlaAllegro"');
                                      echo '</p>';
          
                                      echo '<script>' . "\n";
                                      echo '$(document).ready(function() {' . "\n";
                                      echo '    $(\'#cecha_' . $info_cechy['products_options_id'] . '\').rules( "add", {' . "\n";
                                      echo '        required: true, min: 1, messages: { min: "Proszę wybrać wartość cechy." }' . "\n";   
                                      echo '    });' . "\n";  
                                      echo '});' . "\n";  
                                      echo '</script>' . "\n";                                      
                     
                                      unset($tablica_cechy);

                                  }

                              }   

                              $db->close_query($sql_cechy);
                              unset($zapytanie_cechy);                            
                              ?>
     
                              <p>
                                <label for="ean">Kod EAN:</label>
                                <input name="ean" id="ean" type="text" value="<?php echo $info['products_ean']; ?>" data-ean="<?php echo $info['products_ean']; ?>" size="50" placeholder="-- podaj kod EAN produktu --" />
                              </p>  

                              <p>
                                <label for="external">Sygnatura produktu:</label>
                                <input name="external" id="id_zewentrzne" type="text" value="<?php echo $info['products_id']; ?>" size="50" />
                                <input name="cechy_ukryte" id="cechy_ukryte" type="hidden" value="<?php echo $info['products_id']; ?>" size="50" />
                              </p>                                
                              
                              <p>
                                <label class="required" for="cena_produktu">Cena:</label>
                                <input name="cena_produktu" type="text" id="cena_produktu" value="<?php echo $info['products_price_tax']; ?>" data-cena="<?php echo $info['products_price_tax']; ?>" size="20" /> zł
                                <input name="cena_bazowa_produktu" type="hidden" value="<?php echo $info['products_price_tax']; ?>" />
                              </p>    

                              <p>
                                <label class="required" for="ilosc_produktu">Ilość:</label>
                                <input name="ilosc_produktu" type="text" id="ilosc_produktu" value="<?php echo (int)$info['products_quantity']; ?>" data-ilosc="<?php echo (int)$info['products_quantity']; ?>" size="10" /> &nbsp; &nbsp; 
                                <input type="radio" name="unit" id="jm_szt" value="UNIT" checked="checked" /> <label class="OpisFor" for="jm_szt">sztuk</label> 
                                <input type="radio" name="unit" id="jm_kpl" value="SET" /> <label class="OpisFor" for="jm_kpl">kompletów</label>
                                <input type="radio" name="unit" id="jm_pary" value="PAIR" /><label class="OpisFor" for="jm_pary">par</label>
                              </p>  
                              
                              <div class="DaneAukcjaNaglowek" style="margin-bottom:10px">Kategoria w której będzie wyświetlany produkt</div>                          

                              <script>
                              $(document).ready(function() {
                                  $("#kategoria").on("keypress keyup blur",function (event) {    
                                    $(this).val($(this).val().replace(/[^\d].+/, ""));
                                        if ((event.which < 48 || event.which > 57)) {
                                            event.preventDefault();
                                        }
                                  });
                                  $('#kategoria').click(function() {
                                     //
                                     $('.DaneAukcjiUkryj').show();
                                     $('.PoleWyboruKategorii').css({ 'z-index' : '999'  });
                                     //
                                  });
                                  $('#kategoria').blur(function() {
                                     //
                                     $('.DaneAukcjiUkryj').hide();
                                     $('.PoleWyboruKategorii').css({ 'z-index' : '1'  });
                                     RecznaSciezkaKategorii();
                                     //
                                  });
                              });
                              //
                              function RecznaSciezkaKategorii(element) {
                                  //
                                  if ( $('#kategoria').val() != '' ) {
                                       //
                                       $('#ekr_preloader').css('display','block');   
                                       $.post("ajax/allegro_dane_kategorie.php?tok=" + $('#tok').val(), { id_reczne : $('#kategoria').val(), waga : '<?php echo $waga_ajax; ?>' }, function(data) {     
                                               $('#ekr_preloader').css('display','none');
                                               //
                                               // jezeli blad
                                               if ( data.indexOf('BladKategorieInfo') > 0 ) {
                                                    //
                                                    $('.KategoriaBlad').html(data).stop().slideDown();
                                                    //
                                                    // podstawia domyslna wartosc sciezki
                                                    $('.OknoKategorieAllegroSciezka').html('<span id="kt_1" data-id="" data-ostatnia="" data-nr="1">Allegro</span>');
                                                    $('.OknoKategorieAllegroParametry').html('');
                                                    //
                                                    $('#kategoria').val(''); 
                                                    $('#kategoria_sciezka').val('');
                                                    //
                                                    $('#ekr_preloader').css('display','block'); 
                                                    $.post("ajax/allegro_dane_kategorie.php?tok=" + $('#tok').val(), { id : '-1', ostatnia : '', nr : 1, waga : '<?php echo $waga_ajax; ?>' }, function(data) {     
                                                            $('#ekr_preloader').css('display','none');
                                                            $('.OknoKategorieAllegroLista').html(data);
                                                            //
                                                            $('.OknoKategorieAllegroLista').stop().slideDown();
                                                            $('.WyborKategorie').hide(); 
                                                            //
                                                            setTimeout('zamknijBladKategorie()', 4000);
                                                            //
                                                    });                                              
                                                    //
                                                    $('.PozostaleDaneAukcji').hide();
                                                    $('.przyciski_dolne').hide();
                                                    //
                                               } else {
                                                    //
                                                    $('.KategoriaBlad').html(data).hide();
                                                    SciezkaKategorieAllegro();
                                                    //
                                                    $('.PozostaleDaneAukcji').stop().slideDown('fast');
                                                    $('.przyciski_dolne').show();
                                                    //
                                                    // podstawi sciezke kategorii allegro
                                                    var sciezka_txt = '';
                                                    var sciezka_nr = 0;
                                                    $('.OknoKategorieAllegroSciezka span').each(function() {
                                                       //
                                                       if ( sciezka_nr > 0 ) {
                                                            sciezka_txt += $(this).html() + ';';
                                                       }
                                                       //
                                                       sciezka_nr++;
                                                       //                                          
                                                    });
                                                    $('#kategoria_sciezka').val(sciezka_txt);
                                                    //                                               
                                               }
                                               //
                                       });                                  
                                       //
                                  } else {
                                       //
                                       $('#ekr_preloader').css('display','none');
                                       //
                                       $('.KategoriaBlad').css({ 'height' : 'auto' }).html('Pole kategorii nie może być puste !').stop().slideDown();
                                       //
                                       // podstawia domyslna wartosc sciezki
                                       $('.OknoKategorieAllegroSciezka').html('<span id="kt_1" data-id="" data-ostatnia="" data-nr="1">Allegro</span>');
                                       $('.OknoKategorieAllegroParametry').html('');
                                       //
                                       $('#ekr_preloader').css('display','block'); 
                                       $.post("ajax/allegro_dane_kategorie.php?tok=" + $('#tok').val(), { id : '-1', ostatnia : '', nr : 1, waga : '<?php echo $waga_ajax; ?>' }, function(data) {     
                                               $('#ekr_preloader').css('display','none');
                                               $('.OknoKategorieAllegroLista').html(data);
                                               //
                                               $('.OknoKategorieAllegroLista').stop().slideDown();
                                               $('.WyborKategorie').hide();    
                                               //
                                               setTimeout('zamknijBladKategorie()', 4000);
                                               //
                                       });                                          
                                       //
                                       $('.PozostaleDaneAukcji').hide();
                                       $('.przyciski_dolne').hide();                                       
                                       //
                                  }
                                  //
                              }
                              function zamknijBladKategorie() {
                                  //
                                  $('.KategoriaBlad').stop().slideUp( function() { $('.KategoriaBlad').html('') } );
                                  //
                              }
                              function DrzewoKategoriiAllegro( nr ) {
                                  //
                                  $('.OknoKategorieAllegro li').click(function() {
                                      //
                                      $('#kategoria').val('');
                                      $('#kategoria_sciezka').val('');
                                      //
                                      $('.OknoKategorieAllegroParametry').html('');
                                      $('.KategoriaBlad').hide().html('');
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
                                       $.post("ajax/allegro_dane_kategorie.php?tok=" + $('#tok').val(), { id : $(element).attr('data-id'), ostatnia : $(element).attr('data-ostatnia'), nr : nr, waga : '<?php echo $waga_ajax; ?>' }, function(data) {   
                                               $('#ekr_preloader').css('display','none');
                                               $('.OknoKategorieAllegroLista').html(data); 
                                               //
                                               $('.PozostaleDaneAukcji').hide();
                                               $('.przyciski_dolne').hide();
                                               //
                                       });                                              
                                       //
                                  } else {
                                       //                                   
                                       $('.OknoKategorieAllegroLista').stop().slideUp();
                                       //
                                       $('.WyborKategorie').show();
                                       //
                                       $.post("ajax/allegro_dane_kategorie.php?tok=" + $('#tok').val(), { id_parametry : $(element).attr('data-id'), waga : '<?php echo $waga_ajax; ?>' }, function(data) {     
                                               $('#ekr_preloader').css('display','none');
                                               $('.OknoKategorieAllegroParametry').html(data);   
                                               //
                                               $('.RozwinDodatkoweParametry').click(function() {
                                                  $(this).stop().slideUp();
                                                  $('.DodatkoweParametry').stop().slideDown();
                                               });
                                               //
                                               $('.UlamekParametry').change( function () { var type = this.type; var tag = this.tagName.toLowerCase(); if (type == 'text') { zamien_krp($(this),''); } } );
                                               $('.CalkowitaParametry').change( function () { var type = this.type; var tag = this.tagName.toLowerCase(); if (type == 'text') { zamien_krp($(this),'', 1); } } );                                           
                                               //
                                               pokazChmurki();
                                       });    
                                       //
                                       $('.PozostaleDaneAukcji').stop().slideDown('fast');
                                       //
                                       $('#kategoria').val($(element).attr('data-id'));
                                       //                                       
                                       // podstawi sciezke kategorii allegro
                                       var sciezka_txt = '';
                                       var sciezka_nr = 0;
                                       $('.OknoKategorieAllegroSciezka span').each(function() {
                                          //
                                          if ( sciezka_nr > 0 ) {
                                               sciezka_txt += $(this).html() + ';';
                                          }
                                          //
                                          sciezka_nr++;
                                          //                                          
                                       });
                                       $('#kategoria_sciezka').val(sciezka_txt);
                                       //
                                       $('.przyciski_dolne').show();
                                       //
                                  }
                                  //
                              }
                              function SciezkaKategorieAllegro() {
                                  //
                                  $('.OknoKategorieAllegroSciezka span').click(function() {
                                      //
                                      $('.KategoriaBlad').html('');
                                      //
                                      if ( $(this).attr('data-ostatnia') == '' ) {
                                           //
                                           $('#kategoria').val('');
                                           $('#kategoria_sciezka').val('');
                                           //
                                           $('.OknoKategorieAllegroParametry').html('');
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
                              </script>                          
                              
                              <?php
                              $ListaKategorii = '';
                              $SciezkaKategorii = '<span id="kt_1" data-id="" data-ostatnia="" data-nr="1">Allegro</span>';
                              $OstatniElement = false;
                              $BlednaKategoria = false;
                              //
                              $KategorieAllegro = $AllegroRest->commandRequest('sale/categories', (($id_kategorii > 0) ? $id_kategorii : ''), '', false);                         
                              
                              if ( $id_kategorii != '' ) {
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
                              } else {
                                   //
                                   foreach ( $KategorieAllegro->categories as $KategoriaAllegro ) {
                                       //
                                       $ListaKategorii .= '<li data-id="' . $KategoriaAllegro->id . '" data-ostatnia="' . (($KategoriaAllegro->leaf == '1') ? '1' : '') . '">' . $KategoriaAllegro->name . '</li>';
                                       //
                                   }
                                   //
                              } ?>            

                              <script>
                              function KategoriaProduktu() {

                                  <?php if ( $id_kategorii > 0 && $BlednaKategoria == false ) { ?>

                                  $('#ekr_preloader').css('display','block');
                                  $.post("ajax/allegro_dane_kategorie.php?tok=" + $('#tok').val(), { id_parametry : <?php echo $id_kategorii; ?>, waga : '<?php echo $waga_ajax; ?>' }, function(data) {     
                                          $('#ekr_preloader').fadeOut();
                                          $('.OknoKategorieAllegroParametry').html(data);   
                                          //
                                          $('.RozwinDodatkoweParametry').click(function() {
                                             $(this).stop().slideUp();
                                             $('.DodatkoweParametry').stop().slideDown();
                                          });
                                          //
                                          $('.UlamekParametry').change( function () { var type = this.type; var tag = this.tagName.toLowerCase(); if (type == 'text') { zamien_krp($(this),''); } } );
                                          $('.CalkowitaParametry').change( function () { var type = this.type; var tag = this.tagName.toLowerCase(); if (type == 'text') { zamien_krp($(this),'', 1); } } );                                           
                                          //
                                          pokazChmurki();
                                  });    
                                  //
                                  $('.PozostaleDaneAukcji').show();
                                  $('.przyciski_dolne').show();

                                  <?php } ?>
    
                              }                             
                              </script>
    
                              <p class="PoleWyboruKategorii">
                                <label class="required" for="kategoria">Kategoria (ID w Allegro):</label>
                                <input name="kategoria" id="kategoria" type="text" value="<?php echo (($id_kategorii > 0 && $BlednaKategoria == false) ? $id_kategorii : ''); ?>" size="30" />                                
                                <div class="KategoriaBlad"></div>
                                <input name="kategoria_sciezka" id="kategoria_sciezka" type="hidden" value="" />
                              </p>     
                              
                              <?php if ( $id_kategorii > 0 && $BlednaKategoria == false ) { ?>
                              
                              <script>
                              $(document).ready(function() {
                                  //
                                  // podstawi sciezke kategorii allegro
                                  var sciezka_txt = '';
                                  var sciezka_nr = 0;
                                  $('.OknoKategorieAllegroSciezka span').each(function() {
                                     //
                                     if ( sciezka_nr > 0 ) {
                                          sciezka_txt += $(this).html() + ';';
                                     }
                                     //
                                     sciezka_nr++;
                                     //                                          
                                  });
                                  $('#kategoria_sciezka').val(sciezka_txt);
                                  //
                              });
                              </script>
                              
                              <?php } ?>
                              
                              <div class="OknoKategorieAllegro">
                              
                                  <div class="OknoKategorieAllegroSciezka"><?php echo $SciezkaKategorii; ?></div>
                              
                                  <div class="OknoKategorieAllegroLista" <?php echo (( $OstatniElement == true ) ? 'style="display:none"' : ''); ?>>
                                  
                                      <ul><?php echo $ListaKategorii; ?></ul>
                                              
                                  </div>
                                  
                                  <div class="cl"></div>
                                  
                                  <div class="WyborKategorie" <?php echo (( $OstatniElement == true ) ? 'style="display:block"' : ''); ?>>
                                  
                                      <span>zmień kategorię</span>
                                      
                                  </div>     
                              
                              </div>
                              
                              <div class="OknoKategorieAllegroParametry"></div>

                              <div class="PozostaleDaneAukcji" style="display:none">
                              
                                  <?php
                                  // domyslne parametry konfiguracyjne dla konta
                                  $zapytanie_konfig = "SELECT allegro_auction_settings FROM allegro_users WHERE allegro_user_id = '" . (int)$_SESSION['domyslny_uzytkownik_allegro'] . "'";
                                  $sql_konfig = $db->open_query($zapytanie_konfig);
                                  $info_konfig = $sql_konfig->fetch_assoc();
                                  //
                                  $konfig = @unserialize($info_konfig['allegro_auction_settings']);
                                  //
                                  if ( !is_array($konfig) ) {
                                       $konfig = array();
                                  }
                                  //
                                  $db->close_query($sql_konfig);
                                  unset($zapytanie_konfig, $info_konfig);                                  
                                  ?>
                                  
                                  <div class="DaneAukcjaNaglowek">Format sprzedaży</div>
                                                         
                                  <p>
                                    <label class="required" for="czas_trwania">Czas trwania:</label>
                                    <select name="publication[duration]" id="czas_trwania">
                                        <option value="PT72H" <?php echo ((isset($konfig['publication']) && $konfig['publication'] == 'PT72H') ? 'selected="selected"' : ''); ?>>3 dni</option>
                                        <option value="PT120H" <?php echo ((isset($konfig['publication']) && $konfig['publication'] == 'PT120H') ? 'selected="selected"' : ''); ?>>5 dni</option>
                                        <option value="PT168H" <?php echo ((isset($konfig['publication']) && $konfig['publication'] == 'PT168H') ? 'selected="selected"' : ''); ?>>7 dni</option>
                                        <option value="PT240H" <?php echo ((isset($konfig['publication']) && $konfig['publication'] == 'PT240H') ? 'selected="selected"' : ''); ?>>10 dni</option>
                                        <option value="PT480H" <?php echo ((isset($konfig['publication']) && $konfig['publication'] == 'PT480H') ? 'selected="selected"' : ''); ?>>20 dni</option>
                                        <option value="PT720H"<?php echo ((isset($konfig['publication']) && $konfig['publication'] == 'PT720H') ? 'selected="selected"' : ''); ?> >30 dni</option>
                                        <option value="" <?php echo ((isset($konfig['publication']) && $konfig['publication'] == 'PT1000') ? 'selected="selected"' : ''); ?>>do wyczerpania zapasów</option>                                        
                                    </select>
                                  </p>

                                  <p>
                                    <label class="required" for="format_sprzedazy">Format sprzedaży:</label>
                                    <select name="sellingMode[format]" id="format_sprzedazy">
                                        <option value="BUY_NOW">kup teraz</option>
                                    </select>
                                  </p>         

                                  <div class="DaneAukcjaNaglowek">Dostawa, płatność i warunki oferty</div>
                                  
                                  <p>
                                    <label class="required" for="czas_wysylki">Czas wysyłki:</label>
                                    <select name="delivery[handlingTime]" id="czas_wysylki">
                                        <option value="PT0S" <?php echo ((isset($konfig['delivery']) && $konfig['delivery'] == 'PT0S') ? 'selected="selected"' : ''); ?>>natychmiast</option>
                                        <option value="PT24H" <?php echo ((isset($konfig['delivery']) && $konfig['delivery'] == 'PT24H') ? 'selected="selected"' : ''); ?>>24 godziny</option>
                                        <option value="PT48H" <?php echo ((isset($konfig['delivery']) && $konfig['delivery'] == 'PT48H') ? 'selected="selected"' : ''); ?>>2 dni</option>
                                        <option value="PT72H" <?php echo ((isset($konfig['delivery']) && $konfig['delivery'] == 'PT72H') ? 'selected="selected"' : ''); ?>>3 dni</option>
                                        <option value="PT96H" <?php echo ((isset($konfig['delivery']) && $konfig['delivery'] == 'PT96H') ? 'selected="selected"' : ''); ?>>4 dni</option>
                                        <option value="PT120H" <?php echo ((isset($konfig['delivery']) && $konfig['delivery'] == 'PT120H') ? 'selected="selected"' : ''); ?>>5 dni</option>
                                        <option value="PT168H" <?php echo ((isset($konfig['delivery']) && $konfig['delivery'] == 'PT168H') ? 'selected="selected"' : ''); ?>>7 dni</option>
                                        <option value="PT240H" <?php echo ((isset($konfig['delivery']) && $konfig['delivery'] == 'PT240H') ? 'selected="selected"' : ''); ?>>10 dni</option>
                                        <option value="PT336H" <?php echo ((isset($konfig['delivery']) && $konfig['delivery'] == 'PT336H') ? 'selected="selected"' : ''); ?>>14 dni</option>
                                        <option value="PT504H" <?php echo ((isset($konfig['delivery']) && $konfig['delivery'] == 'PT504H') ? 'selected="selected"' : ''); ?>>21 dni</option>
                                        <option value="PT720H" <?php echo ((isset($konfig['delivery']) && $konfig['delivery'] == 'PT720H') ? 'selected="selected"' : ''); ?>>30 dni</option>
                                        <option value="PT1440H" <?php echo ((isset($konfig['delivery']) && $konfig['delivery'] == 'PT1440H') ? 'selected="selected"' : ''); ?>>60 dni</option>
                                    </select>
                                  </p>                  

                                  <p>
                                    <label class="required" for="cennik_dostawy">Cennik dostawy:</label>
                                    <select name="delivery[shippingRates]" id="cennik_dostawy">
                                        <option value="">--- wybierz ---</option>

                                        <?php
                                        $dane = array( 'seller.id' => $AllegroRest->ParametryPolaczenia['UserId'] ); 
                                        $cennik_dostaw = $AllegroRest->commandRequest('sale/shipping-rates', $dane, '');
                                        
                                        if ( isset($cennik_dostaw->shippingRates) && count($cennik_dostaw->shippingRates) > 0 ) {
                                             //
                                             foreach ( $cennik_dostaw->shippingRates as $cennik ) {
                                                 //
                                                 echo '<option value="' . $cennik->id . '"' . ((isset($konfig['shippingRates']) && $konfig['shippingRates'] == $cennik->id) ? 'selected="selected"' : '') . '>' . $cennik->name . '</option>';
                                                 //
                                             }
                                             //
                                        }
                                        
                                        unset($cennik_dostaw);
                                        ?>
                                                                  
                                    </select><em class="TipIkona"><b>Wyświetlane są cenniki dostaw zdefiniowane bezpośrednio w Allegro</b></em>
                                  </p>    

                                  <script>
                                  $(document).ready(function() {
                                      $('#cennik_dostawy').rules( "add", {
                                          required: true, messages: { required: "Proszę wybrać cennik dostawy." } 
                                      });
                                  });
                                  </script>  

                                  <p>
                                    <label for="warunki_zwrotow" <?php echo (($AllegroRest->ParametryPolaczenia['ClientType'] == 'F') ? 'class="required"' : ''); ?>>Warunki zwrotów:</label>
                                    <select name="afterSalesServices[returnPolicy]" id="warunki_zwrotow">
                                        <option value="">--- wybierz ---</option>
                                        
                                        <?php
                                        //
                                        $dane = array( 'seller.id' => $AllegroRest->ParametryPolaczenia['UserId'] ); 
                                        $warunki_zwrotow = $AllegroRest->commandRequest('after-sales-service-conditions/return-policies', $dane, '');
                                        
                                        if ( isset($warunki_zwrotow->returnPolicies) && count($warunki_zwrotow->returnPolicies) > 0 ) {
                                             //
                                             foreach ( $warunki_zwrotow->returnPolicies as $zwrot ) {
                                                 //
                                                 echo '<option value="' . $zwrot->id . '"' . ((isset($konfig['returnPolicy']) && $konfig['returnPolicy'] == $zwrot->id) ? 'selected="selected"' : '') . '>' . $zwrot->name . '</option>';
                                                 //
                                             }
                                             //
                                        }
                                        //
                                        unset($zwroty, $zwrot);
                                        ?>                                    
                             
                                    </select><em class="TipIkona"><b>Wyświetlane są warunki zwrotów zdefiniowane bezpośrednio w Allegro</b></em>                                
                                  </p> 
                                  
                                  <?php if ( $AllegroRest->ParametryPolaczenia['ClientType'] == 'F' ) { ?>
                                  <script>
                                  $(document).ready(function() {
                                      $('#warunki_zwrotow').rules( "add", {
                                          required: true, messages: { required: "Proszę wybrać warunki zwrotów." } 
                                      });
                                  });
                                  </script>                                 
                                  <?php } ?>

                                  <p>
                                    <label for="warunki_reklamacji" <?php echo (($AllegroRest->ParametryPolaczenia['ClientType'] == 'F') ? 'class="required"' : ''); ?>>Warunki reklamacji:</label>
                                    <select name="afterSalesServices[impliedWarranty]" id="warunki_reklamacji">
                                        <option value="">--- wybierz ---</option>
                                        
                                        <?php
                                        //
                                        $dane = array( 'seller.id' => $AllegroRest->ParametryPolaczenia['UserId'] ); 
                                        $reklamacje = $AllegroRest->commandRequest('after-sales-service-conditions/implied-warranties', $dane, '');
                                        
                                        if ( isset($reklamacje->impliedWarranties) && count($reklamacje->impliedWarranties) > 0 ) {
                                             //
                                             foreach ( $reklamacje->impliedWarranties as $reklamacja ) {
                                                 //
                                                 echo '<option value="' . $reklamacja->id . '"' . ((isset($konfig['impliedWarranty']) && $konfig['impliedWarranty'] == $reklamacja->id) ? 'selected="selected"' : '') . '>' . $reklamacja->name . '</option>';
                                                 //
                                             }
                                             //
                                        }
                                        //
                                        unset($reklamacje, $reklamacja);
                                        ?>      
                                        
                                    </select><em class="TipIkona"><b>Wyświetlane są warunki reklamacji zdefiniowane bezpośrednio w Allegro</b></em>
                                  </p>   

                                  <?php if ( $AllegroRest->ParametryPolaczenia['ClientType'] == 'F' ) { ?>
                                  <script>
                                  $(document).ready(function() {
                                      $('#warunki_reklamacji').rules( "add", {
                                          required: true, messages: { required: "Proszę wybrać warunki reklamacji." } 
                                      });
                                  });
                                  </script>                                 
                                  <?php } ?>                              

                                  <p>
                                    <label for="warunki_gwarancji">Gwarancja:</label>
                                    <select name="afterSalesServices[warranty]" id="warunki_gwarancji">
                                        <option value="">--- wybierz ---</option>

                                        <?php
                                        //
                                        $dane = array( 'seller.id' => $AllegroRest->ParametryPolaczenia['UserId'] ); 
                                        $gwarancje = $AllegroRest->commandRequest('after-sales-service-conditions/warranties', $dane, '');
                                        
                                        if ( isset($gwarancje->warranties) && count($gwarancje->warranties) > 0 ) {
                                             //
                                             foreach ( $gwarancje->warranties as $gwarancja ) {
                                                 //
                                                 echo '<option value="' . $gwarancja->id . '"' . ((isset($konfig['warranty']) && $konfig['warranty'] == $gwarancja->id) ? 'selected="selected"' : '') . '>' . $gwarancja->name . '</option>';
                                                 //
                                             }
                                             //
                                        }
                                        //
                                        unset($gwarancje, $gwarancja);
                                        ?>
                                                                  
                                    </select><em class="TipIkona"><b>Wyświetlane są gwarancje zdefiniowane bezpośrednio w Allegro</b></em>
                                  </p>             

                                  <p>
                                    <label for="uslugi_dodatkowe">Usługi dodatkowe:</label>
                                    <select name="additionalServices" id="uslugi_dodatkowe">
                                        <option value="">--- wybierz ---</option>

                                        <?php
                                        $dane = array( 'user.id' => $AllegroRest->ParametryPolaczenia['UserId'] ); 
                                        $uslugi_dodatkowe = $AllegroRest->commandRequest('sale/offer-additional-services/groups', $dane, '');

                                        if ( isset($uslugi_dodatkowe->additionalServicesGroups) && count($uslugi_dodatkowe->additionalServicesGroups) > 0 ) {
                                             //
                                             foreach ( $uslugi_dodatkowe->additionalServicesGroups as $usluga ) {
                                                 //
                                                 echo '<option value="' . $usluga->id . '"' . ((isset($konfig['additionalServices']) && $konfig['additionalServices'] == $usluga->id) ? 'selected="selected"' : '') . '>' . $usluga->name . '</option>';
                                                 //
                                             }
                                             //
                                        }
                                        
                                        unset($uslugi_dodatkowe);
                                        ?>
                                                                  
                                    </select><em class="TipIkona"><b>Wyświetlane są usługi dodatkowe zdefiniowane bezpośrednio w Allegro</b></em>
                                  </p>     
                                  
                                  <p>
                                    <label for="tabela_rozmiarow">Tabela rozmiarów:</label>
                                    <select name="sizeTable" id="tabela_rozmiarow">
                                        <option value="">--- wybierz ---</option>

                                        <?php
                                        $dane = array( 'user.id' => $AllegroRest->ParametryPolaczenia['UserId'] ); 
                                        $tabela_rozmiarow = $AllegroRest->commandRequest('sale/size-tables', $dane, '');

                                        if ( isset($tabela_rozmiarow->tables) && count($tabela_rozmiarow->tables) > 0 ) {
                                             //
                                             foreach ( $tabela_rozmiarow->tables as $tab_rozmiar ) {
                                                 //
                                                 echo '<option value="' . $tab_rozmiar->id . '"' . ((isset($konfig['sizeTable']) && $konfig['sizeTable'] == $tab_rozmiar->id) ? 'selected="selected"' : '') . '>' . $tab_rozmiar->name . '</option>';
                                                 //
                                             }
                                             //
                                        }
                                        
                                        unset($uslugi_dodatkowe);
                                        ?>
                                                                 
                                    </select><em class="TipIkona"><b>Wyświetlane są tabele rozmiarów zdefiniowane bezpośrednio w Allegro</b></em> 
                                  </p>                            
                                  
                                  <p>
                                    <label class="required" for="faktura_vat">Faktura VAT:</label>
                                    <select name="payments[invoice]" id="faktura_vat">
                                        <option value="" <?php echo ((isset($konfig['invoice']) && $konfig['invoice'] == '') ? 'selected="selected"' : ''); ?>>--- wybierz ---</option>
                                        <option value="VAT" <?php echo ((isset($konfig['invoice']) && $konfig['invoice'] == 'VAT') ? 'selected="selected"' : ''); ?>>faktura VAT</option>
                                        <option value="VAT_MARGIN" <?php echo ((isset($konfig['invoice']) && $konfig['invoice'] == 'VAT_MARGIN') ? 'selected="selected"' : ''); ?>>faktura VAT marża</option>
                                        <option value="WITHOUT_VAT" <?php echo ((isset($konfig['invoice']) && $konfig['invoice'] == 'WITHOUT_VAT') ? 'selected="selected"' : ''); ?>>faktura bez VAT</option>
                                        <option value="NO_INVOICE" <?php echo ((isset($konfig['invoice']) && $konfig['invoice'] == 'NO_INVOICE') ? 'selected="selected"' : ''); ?>>nie wystawiam faktury</option>
                                    </select>
                                  </p>           

                                  <script>
                                  $(document).ready(function() {
                                      $('#faktura_vat').rules( "add", {
                                          required: true, messages: { required: "Proszę wybrać rodzaj faktury." } 
                                      });
                                  });
                                  </script>    

                                  <?php unset($konfig); ?>

                                  <div class="DaneAukcjaNaglowek">Opcje promowania</div>
                                  
                                  <div class="OknoDiv">
                                     <label>Opcje promowania:</label>
                                     
                                     <div class="OknoParametryWybor">  
                                        <input type="radio" name="basepackage" value="emphasized10d" id="wyroznienie10d" /><label class="OpisFor" for="wyroznienie10d">Wyróżnienie (10 dni)</label> <br />
                                        <input type="radio" name="basepackage" value="emphasized1d" id="wyroznienie1d" /><label class="OpisFor" for="wyroznienie1d">Wyróżnienie (1 dzień)</label> <br />
                                        <input type="radio" name="basepackage" value="promoPackage" id="pakiet_promo" /><label class="OpisFor" for="pakiet_promo">Pakiet Promo (wyróżnienie, podświetlenie i pogrubienie)</label> <br />
                                        <br /><input type="checkbox" name="extrapackage" value="departmentPage" id="strona_dzialu" /><label class="OpisFor" for="strona_dzialu">Promowanie na stronie działu</label> <br />
                                     </div>
                                  </div>
                                  
                                  <div class="DaneAukcjaNaglowek">Czas wystawienia</div>
                                  
                                  <script>
                                  $(document).ready(function() {
                                      $('input.dataukcji').Zebra_DatePicker({
                                         format: 'd-m-Y',
                                         inside: false,
                                         direction: [1, 60],
                                         readonly_element: true
                                      });
                                  });
                                  </script>
                                  
                                  <div class="OknoDiv">
                                    <label>Oferta pojawi się:</label>
                                    
                                    <div class="OknoParametryWybor"> 
                                        <input type="radio" name="startingAt[aktywne]" value="od_razu" id="czas_wystawienia_od_razu" checked="checked" /><label class="OpisFor" for="czas_wystawienia_od_razu">od razu</label> <br />
                                        <input type="radio" name="startingAt[aktywne]" value="pozniej" id="czas_wystawienia_pozniej" /><label class="OpisFor" for="czas_wystawienia_pozniej">ustalona data</label> &nbsp; &nbsp;                                
                                        <input type="text" size="20" name="startingAt[data]" value="<?php echo date('d-m-Y', time() + 86400); ?>" class="dataukcji"> &nbsp; &nbsp;
                                        godz: 
                                        <select name="startingAt[godzina]">
                                        <?php
                                        for ($c = 0; $c < 24; $c++) { 
                                            echo '<option value="' . $c . '"' . (($c == 12) ? 'selected="selected"' : '') . '>' . $c . '</option>'; 
                                        } 
                                        ?>
                                        </select>
                                        min: 
                                        <select name="startingAt[minuty]">
                                        <?php
                                        for ($c = 0; $c < 6; $c++) { 
                                            echo '<option value="'. ( $c * 10 ) . '"' . (($c * 10 == 30) ? 'selected="selected"' : '') . '>' . ($c * 10 ) . '</option>'; 
                                        } 
                                        ?>
                                        </select>                                    
                                     </div>
                                  </div>

                                  <div class="DaneAukcjaNaglowek">Zdjęcia i opis produktu</div>
                                  
                                  <div class="ZdjeciaRestAllegro"><?php echo $ciag_zdjec; ?></div>   

                                  <div class="InfoBledow" style="margin-left:10px">
                                      <label id="ilosc_zdjec" class="error" style="display:none">Za duża ilość zdjęć. Maksymalnie może być dodane <?php echo (($AllegroRest->ParametryPolaczenia['ClientType'] == 'F') ? 18 : 10); ?> zdjęć.</label>
                                  </div>                    

                                  <?php if ( isset($_SESSION['allegro_produkt']) ) { ?>
                                  
                                  <div class="OknoDiv">
                                     <input type="radio" name="same_zdjecia" value="produkt" checked="checked" id="same_zdjecia_produkt" /><label class="OpisFor" for="same_zdjecia_produkt">dodaj do aukcji TYLKO zdjęcia produktu ze sklepu</label><br />
                                     <input type="radio" name="same_zdjecia" value="allegro" id="same_zdjecia_allegro" /><label class="OpisFor" for="same_zdjecia_allegro">dodaj do aukcji TYLKO zdjęcia produktu wybranego z Allegro</label><br />
                                     <input type="radio" name="same_zdjecia" value="wszystkie" id="same_zdjecia_wszystkie" /><label class="OpisFor" for="same_zdjecia_wszystkie">dodaj do aukcji zdjęcia produktu ze sklepu oraz wybranego produktu z Allegro</label>
                                  </div>                                  
                                  
                                  <?php } ?>

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
                                  
                                  // zamkniecie okna edycji
                                  function ZamknijOknoZdjec() {
                                      $('#ekr_edit').fadeOut( function(data) { $('#glowne_okno_edycji').html(''); } );
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
                                        $.post('ajax/allegro_dane_opis.php?tok=' + $('#tok').val(), { foto_1_img: foto_1_img, foto_2_img: foto_2_img, foto_1_input: foto_1_input, foto_2_input: foto_2_input, opis: opis, nr_losowy: nr_losowy, nr: nr, typ: typ, zmiana_trybu: 'tak', wystawianie: 'tak' }, function(data) {
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

                                  // wybor zdjecia dla nowego opisu allegro
                                  function WyborFotoAllegro( id ) {
                                     //
                                     var zdjecia_allegro = '<div class="NaglowekMiniaturkiProduktu">Wybierz zdjęcie</div><ul class="MiniaturkiProduktu">';
                                     
                                     $('.ZdjeciaRestAllegro div').each(function() {
                                          //
                                          zdjecia_allegro = zdjecia_allegro + '<li data-url-foto="' + $(this).attr('data-url-foto') + '" data-url="' + $(this).attr('data-url') + '" data-nr="' + id + '"><img src="' + $(this).find('img').attr('src') + '" alt="" /></li>';
                                          //
                                     });

                                     zdjecia_allegro = zdjecia_allegro + '</ul>';

                                     // czy sa zdjecia produktu
                                     if ( $('.ZdjeciaRestAllegro span').length == 0 ) { 
                                          //
                                          zdjecia_allegro = zdjecia_allegro + '<b class="BrakZdjecAukcji">Brak zdjęć do wyboru ...</b>';
                                          //
                                     }                                     
                                     
                                     $('#ekr_edit').css('display','none');
                                     $('#glowne_okno_edycji').html(zdjecia_allegro);
                                     //
                                     $('#ekr_edit').show();
                                     $('#ekr_edit').css({'visibility':'hidden'});
                                     var margines = $(window).height() - $('#edytuj_okno').height() - 50;
                                     //
                                     if ( margines < 10 ) {
                                          margines = 40;
                                     }
                                     if ( $('#StrGlowna').width() < 900 ) {
                                          margines = 80;
                                     }        
                                     //
                                     $('#edytuj_stale').css({ 'top' : margines / 2 });
                                     
                                     if ( $('#edytuj_okno').outerHeight() > $(window).height() ) {
                                          $('#edytuj_okno').css({ 'max-height' : $(window).height() - 100, 'overflow-y' : 'scroll' });
                                     }
                                     
                                     $('#ekr_edit').css({'visibility':'visible'});
                                     $('#ekr_edit').hide();
                                     //
                                     $('#ekr_edit').fadeIn();
                                     //
                                     $('.MiniaturkiProduktu li').click(function() {
                                        //
                                        var url = $(this).attr('data-url');
                                        var url_foto = $(this).attr('data-url-foto');
                                        var idnr = $(this).attr('data-nr');
                                        //
                                        $('#kont_' + idnr).find('img').attr('src', url_foto);
                                        $('#' + idnr).val( url );                                    
                                        //
                                        $('#ekr_edit').fadeOut( function(data) { $('#glowne_okno_edycji').html(''); } );
                                        //
                                     });
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
                                      $.post('ajax/allegro_dane_opis.php?tok=' + $('#tok').val(), { nr_losowy: nr_losowy, nr: 1, typ: 'listing', zmiana_trybu: 'nie', wystawianie: 'tak' }, function(data) {
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
                                             $('#przycisk_wystaw').show();
                                             //
                                             var nowy_nr = parseInt($('#licznik_wierszy_opisu').val()) + 1;
                                             var nr_losowy = Math.floor(Math.random() * 10000000) + 1000;
                                             $('#licznik_wierszy_opisu').val( nowy_nr );
                                             //
                                             $('.WierszeOpisu').append('<div id="Opis_nr_' + nowy_nr + '" class="OpisWystawianieAukcji"></div>');
                                             //
                                             $.post('ajax/allegro_dane_opis.php?tok=' + $('#tok').val(), { nr_losowy: nr_losowy, nr: nowy_nr, typ: 'listing', zmiana_trybu: 'nie', wystawianie: 'tak' }, function(data) {
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
                                          $('#przycisk_wystaw').hide();
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
                                          $('#przycisk_wystaw').show();
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
                                  
                                  <?php if ( !is_array($tresc_opisu) ) { ?>

                                  <input type="hidden" value="1" id="licznik_wierszy_opisu" />
                                  
                                  <div class="WierszeOpisu">
                                  
                                      <div id="Opis_nr_1" class="OpisWystawianieAukcji"></div>
                                      
                                  </div>
                                  
                                  <?php } ?>
                                  
                                  <?php 
                                  // jezeli jest wybrany szablon lub indywidualny opis aukcji dla produktu
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
                                             $tryb_wystawianie = 'tak';
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

                                             unset($zawartosc_wiersza, $nr, $nr_losowy, $typ, $zmiana_trybu, $dane_opisu);
                                                 
                                       }
                                       //
                                       echo '</div>';  
                                       
                                       unset($bez_ajax);

                                       echo '<script>$(document).ready(function() { ZmienSposobWyswietlaniaOpisu(); });</script>';                                   
                                  }                              
                                  ?>
                                  
                                  <div class="NowyWierszOpisu">
                                      <span class="dodaj">dodaj kolejny wiersz</span>
                                  </div>

                              </div>
                              
                          </div>
                          
                      </div>

                  </div>
                  
                  <input type="hidden" value="" name="id_aukcji" id="id_aukcji" />
                  <input type="hidden" value="" name="data_utworzenia" id="data_utworzenia" />
                  <input type="hidden" value="" name="data_modyfikacji" id="data_modyfikacji" />
                  <input type="hidden" value="" name="wystawienie" id="wystawienie" id="nie" />

                  <div id="WynikAukcja"></div>
                  
                  <script>
                  function sprawdz_prowizje() {
                      //
                      $('#ekr_preloader').css('display','block'); 
                      //
                      $.post("ajax/allegro_sprawdz_prowizje.php?tok=" + $('#tok').val(), { id_aukcji: $('#id_aukcji').val() }, function(data) { 
                          $('#ekr_preloader').css('display','none');      
                          $('#WynikAukcja').html(data);
                      });                                     
                      //
                  }        
                  </script>
                                  
                  <div class="przyciski_dolne" style="display:none">
                    <div id="przycisk_wystaw" style="float:left"><input id="form_submit" type="submit" class="przyciskNon" value="Sprawdź poprawność przed wystawieniem" /></div>
                    <button type="button" class="przyciskNon" id="sprawdz_koszty" style="display:none" onclick="sprawdz_prowizje();">Sprawdź koszt wystawienia</button> 
                    <button type="button" class="przyciskNon" onclick="cofnij('allegro_aukcje','<?php echo Funkcje::Zwroc_Get(array('x','y','szablon','id_poz','szukaj','zakres')); ?>','allegro');">Lista aukcji</button>     
                    <button type="button" class="przyciskNon" onclick="cofnij('produkty','<?php echo Funkcje::Zwroc_Get(array('x','y','szablon','szukaj','zakres')); ?>','produkty');">Powrót</button>                
                  </div>

                <?php unset($info); ?>
            
            <?php } else {
              
                echo '<div class="pozycja_edytowana">Brak danych do wyświetlenia</div>';
              
            } ?>    

            </div>
          
        </form>

        <?php } ?>
        
    </div>
    
    <div id="WygladPop">

        <div id="ekr_edit" class="EkranEdycjiAllegro">
        
            <div id="edit_tlo" class="TloEdycjiAllegro"></div>
            
            <div id="edytuj_stale" class="EdytujWygladAllegro">
            
                <div id="edytuj_okno" class="OknoEdycjiWygladu">
                
                    <img class="ZamknijBox" onclick="ZamknijOknoZdjec()" src="obrazki/zamknij.png" alt="Zamknij okno" />
                    
                    <div id="glowne_okno_edycji"></div>
                    
                </div>
                
            </div>
            
        </div>

    </div>       
    
    <?php
    
    $db->close_query($sql);
    unset($zapytanie);  

    include('stopka.inc.php');

}
?>