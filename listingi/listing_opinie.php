<?php
// czy jest zapytanie
if ($IloscOpinii > 0) { 

    while ($info = $sql->fetch_assoc()) {
        //
        // ************************ wyglad opinii - poczatek **************************
        //
        echo '<div class="Opinie LiniaDolna">';
            //
            echo '<div class="OcenaSklepu">';
            
                echo '<div class="SredniaOcena">{__TLUMACZ:SREDNIA_OCENA_OPINII}<b>' . number_format(round($info['average_rating'],1), 1, ',', ' ') . '</b></div>';
                
                echo '<ul class="Oceny LiniaGorna">';
                        if ( Wyglad::TypSzablonu() == true ) {
                            echo '
                                  <li>{__TLUMACZ:OCENA_JAKOSC_OBSLUGI}<span class="Gwiazdki Gwiazdka_'.$info['handling_rating'].'" id="radio_'.uniqid().'" style="--ocena: '.$info['handling_rating'].';"></span></li>
                                  <li>{__TLUMACZ:OCENA_CZAS_REALIZACJI}<span class="Gwiazdki Gwiazdka_'.$info['lead_time_rating'].'" id="radio_'.uniqid().'" style="--ocena: '.$info['lead_time_rating'].';"></span></li>
                                  <li>{__TLUMACZ:OCENA_CENY}<span class="Gwiazdki Gwiazdka_'.$info['price_rating'].'" id="radio_'.uniqid().'" style="--ocena: '.$info['price_rating'].';"></span></li>
                                  <li>{__TLUMACZ:OCENA_PRODUKTOW}<span class="Gwiazdki Gwiazdka_'.$info['quality_products_rating'].'" id="radio_'.uniqid().'" style="--ocena: '.$info['quality_products_rating'].';"></span></li>';
                        } else {
                            echo '
                                  <li class="Ocena_' . $info['handling_rating'] . '">{__TLUMACZ:OCENA_JAKOSC_OBSLUGI}</li>
                                  <li class="Ocena_' . $info['lead_time_rating'] . '">{__TLUMACZ:OCENA_CZAS_REALIZACJI}</li>
                                  <li class="Ocena_' . $info['price_rating'] . '">{__TLUMACZ:OCENA_CENY}</li>
                                  <li class="Ocena_' . $info['quality_products_rating'] . '">{__TLUMACZ:OCENA_PRODUKTOW}</li>';
                        }
                echo '</ul>';              
            
            echo '</div>';
            
            echo '<div class="OcenaInfo">';
            
                echo '<ul class="AutorData">';
                
                    echo '<li>{__TLUMACZ:AUTOR_OPINII}: <b>' . $info['customers_name'] . '</b></li>';
                    echo '<li>{__TLUMACZ:DATA_NAPISANIA_RECENZJI}: <b>' . date('d-m-Y',FunkcjeWlasnePHP::my_strtotime($info['date_added'])) . '</b></li>';
                
                echo '</ul>';
                
                echo '<div class="KomentarzOpinii">';
                
                    echo $info['comments'];
                
                    // komentarz do opinii
                    if ( !empty($info['comments_answers']) ) {
                         echo '<div style="font-style:italic;margin-top:10px">{__TLUMACZ:ODPOWIEDZ_SKLEPU} <br /> ' . $info['comments_answers'] . '</div>';
                    }                
                
                echo '</div>';
                
                // jezeli uzytkownik poleca sklep
                if ( $info['recommending'] == 1 ) {
                     //
                     echo '<div class="KlientPoleca InformacjaOk">{__TLUMACZ:KLIENT_POLECA_SKLEP}</div>';
                     //
                }
                
                // zdjecie opinii
                if ( !empty($info['reviews_shop_image']) ) {
                     //
                     if ( file_exists('grafiki_inne/' . $info['reviews_shop_image']) ) {
                          //
                          echo '<div class="ZdjeciaOpinii" style="padding:10px 0 10px 0">
                                    <a class="GaleriaOpinie" href="grafiki_inne/' . $info['reviews_shop_image'] . '"><img style="width:auto !important;max-height:200px !important" src="grafiki_inne/' . $info['reviews_shop_image'] . '" alt="" /></a>
                                </div>';
                          //
                     }
                     //
                }             
                
                // jezeli uzytkownik zezwala na liste produktow
                if ( $info['products_approved'] == 1 && OPINIE_PRODUKTY == 'tak' ) {
                     //
                     $IdWidoczne = array();
                     $IdProduktow = explode(',', (string)$info['products_id']);
                     //
                     foreach ($IdProduktow as $Id) {
                          //
                          $Produkt = new Produkt( (int)$Id );
                          //
                          if ($Produkt->CzyJestProdukt == true) {
                              //
                              $IdWidoczne[] = $Produkt->info['link'];
                              //
                          }   
                          //
                          unset($Produkt);
                          //
                     }
                     
                     $IdWidoczne = array_unique($IdWidoczne);
                     
                     $Suma = 0;
                     
                     if ( count($IdWidoczne) > 0 ) {
                     
                         echo '<div class="ProduktyZakupil"><strong>{__TLUMACZ:OPINIE_KLIENT_PRODUKTY}</strong>';
                         //
                         foreach ($IdWidoczne as $LinkProduktu) {
                              //
                              echo '<ul>';
                              echo '<li>' . $LinkProduktu . '</li>';
                              echo '</ul>';
                              //
                              $Suma++;
                              //
                              if ( $Suma == OPINIE_PRODUKTY_ILOSC ) {
                                   break;
                              }
                              //
                         }
                         //
                         echo '</div>';
                         //
                         
                     }
                     //
                     unset($IdProduktow, $IdWidoczne);
                     //
                }                
            
            echo '</div>';
            //
        echo '</div>';
        //
        // ************************ wyglad opinii - koniec **************************
        //
    }

    unset($info);
    
    echo '<script>
          $(document).ready(function() {
            $(\'.GaleriaOpinie\').jBox(\'Image\', {
                imageSize: \'auto\'
            });      
          });
          </script>';
          
} else {

    echo '<div id="BrakProduktow" class="Informacja">{__TLUMACZ:BLAD_BRAK_OPINII}</div>';
  
}

$GLOBALS['db']->close_query($sql); 

unset($IloscOpinii, $zapytanie);  
?>