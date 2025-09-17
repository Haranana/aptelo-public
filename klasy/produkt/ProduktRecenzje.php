<?php

if ( isset($pobierzFunkcje) ) {

    if ( RECENZJE_STATUS == 'tak' ) {

        $zapRecenzja = "SELECT r.products_id, 
                          r.reviews_id,
                          r.customers_id,
                          r.customers_name,
                          r.reviews_rating,
                          r.date_added,
                          r.comments_answers,
                          r.reviews_image,
                          r.reviews_confirm,
                          rd.reviews_text,
                          p.products_image, 
                          p.products_image_description,
                          p.products_model, 
                          pd.products_name, 
                          pd.products_seo_url
                        FROM reviews r
                        INNER JOIN reviews_description rd ON rd.reviews_id = r.reviews_id AND r.approved = '1' AND rd.languages_id = '" . $this->jezykDomyslnyId . "'
                        LEFT JOIN products p ON r.products_id = p.products_id
                        LEFT JOIN products_description pd ON pd.products_id = r.products_id AND pd.language_id = '" . $this->jezykDomyslnyId . "'
                        WHERE r.products_id = '" . $this->id_produktu . "' ORDER BY r.date_added DESC";

        $sqlRecenzje = $GLOBALS['db']->open_query($zapRecenzja);
        
        $SumaGlosow = 0;
        $IloscGlosow = 0;
        
        if ( (int)$GLOBALS['db']->ile_rekordow($sqlRecenzje) > 0 ) {
        
            while ($infoRecenzja = $sqlRecenzje->fetch_assoc()) {    

                // ustala jaka ma byc tresc linku
                $linkSeo = ((trim((string)$infoRecenzja['products_seo_url']) != '') ? $infoRecenzja['products_seo_url'] : strip_tags((string)$infoRecenzja['products_name']));
                // ustala jaka ma alt zdjecia
                $altFoto = htmlspecialchars(((!empty($infoRecenzja['products_image_description'])) ? (string)$infoRecenzja['products_image_description'] : strip_tags((string)$infoRecenzja['products_name'])));
                
                // czy jest wypelnione pola obrazka glownego
                if (empty($infoRecenzja['products_image']) && POKAZ_DOMYSLNY_OBRAZEK == 'tak') {            
                   $infoRecenzja['products_image'] = 'domyslny.webp';
                }
                //
                $linkIdFoto = 'id="fot_' . $this->idUnikat . $this->id_produktu . '" ';
                //
                $zdjecieKlienta = '';
                if ( !empty($infoRecenzja['reviews_image']) ) {
                     //
                     if ( file_exists('grafiki_inne/' . $infoRecenzja['reviews_image']) ) {
                          $zdjecieKlienta = '<a class="GaleriaRecenzje" href="grafiki_inne/' . $infoRecenzja['reviews_image'] . '"><img src="grafiki_inne/' . $infoRecenzja['reviews_image'] . '" alt="' . $altFoto . '" /></a>';
                     }
                     //
                }
                //
                $this->recenzje[$infoRecenzja['reviews_id']] = array('recenzja_id'                 => $infoRecenzja['reviews_id'],
                                                                     'recenzja_link'               => '<a href="' . Seo::link_SEO( $linkSeo, $infoRecenzja['reviews_id'], 'recenzja' ) . '">' . $infoRecenzja['products_name'] . '</a>',
                                                                     'recenzja_zdjecie_link'       => ((!empty($infoRecenzja['products_image'])) ? '<a class="Zoom" href="' . Seo::link_SEO( $linkSeo, $infoRecenzja['reviews_id'], 'recenzja' ) . '">' . Funkcje::pokazObrazek($infoRecenzja['products_image'], $altFoto, $this->szerImg, $this->wysImg, array(), $linkIdFoto . 'class="Zdjecie"', 'maly') . '</a>' : ''),
                                                                     'recenzja_zdjecie_link_ikony' => ((!empty($infoRecenzja['products_image'])) ? '<a class="Zoom" href="' . Seo::link_SEO( $linkSeo, $infoRecenzja['reviews_id'], 'recenzja' ) . '">' . Funkcje::pokazObrazek($infoRecenzja['products_image'], $altFoto, $this->szerImg, $this->wysImg, $this->ikonki, $linkIdFoto . 'class="Zdjecie"', 'maly') . '</a>' : ''),
                                                                     'recenzja_tekst'              => strip_tags((string)$infoRecenzja['reviews_text']),
                                                                     'recenzja_tekst_krotki'       => Funkcje::przytnijTekst(strip_tags((string)$infoRecenzja['reviews_text']), 200),
                                                                     'recenzja_odpowiedz'          => $infoRecenzja['comments_answers'],
                                                                     'recenzja_tekst_odpowiedz'    => strip_tags((string)$infoRecenzja['reviews_text']) . ((!empty($infoRecenzja['comments_answers'])) ? '<p style="font-style:italic;line-height:1.5;margin-top:10px">' . $GLOBALS['tlumacz']['ODPOWIEDZ_SKLEPU'] . '<br />' . $infoRecenzja['comments_answers'] . '</p>' : ''),
                                                                     'recenzja_data_dodania'       => date('d-m-Y',FunkcjeWlasnePHP::my_strtotime($infoRecenzja['date_added'])),
                                                                     'recenzja_ocena'              => $infoRecenzja['reviews_rating'],
                                                                     'recenzja_ocena_obrazek'      => ( Wyglad::TypSzablonu() == true ? '<span class="Gwiazdki Gwiazdka_'.round($infoRecenzja['reviews_rating'], 0).'" style="--ocena: '.round($infoRecenzja['reviews_rating'], 0).';" id="radio_'.uniqid().'"></span>' : '<img src="szablony/'.DOMYSLNY_SZABLON.'/obrazki/recenzje/ocena_' . $infoRecenzja['reviews_rating'] . '.png" alt="' . $GLOBALS['tlumacz']['OCENA_PRODUKTU'] . ' ' . $infoRecenzja['reviews_rating'] . '/5" />'),
                                                                     'recenzja_oceniajacy'         => $infoRecenzja['customers_name'],
                                                                     'potwierdzony_zakup'          => (((int)$infoRecenzja['reviews_confirm'] == 1) ? 'tak' : 'nie'));
                                                                     
                if ( $zdjecieKlienta != '' ) {
                     //
                     $this->recenzjeZdjecia[$infoRecenzja['reviews_id']] = array('recenzja_zdjecie_klienta' => $zdjecieKlienta);
                     //
                }
                                          
                unset($linkSeo, $altFoto, $linkIdFoto, $zdjecieKlienta); 

                $SumaGlosow = $SumaGlosow + $infoRecenzja['reviews_rating'];
                $IloscGlosow++;

            }
            
            unset($infoRecenzja);
        
        }
        
        $SredniaOcena = 0;
        
        if ($SumaGlosow > 0) {
            $SredniaOcena = round(($SumaGlosow / $IloscGlosow),2);
        }

        $this->recenzjeSrednia = array('srednia_ocena'         => $SredniaOcena,
                                       'ilosc_glosow'          => $IloscGlosow,
                                       'srednia_ocena_obrazek' => ( Wyglad::TypSzablonu() == true ? '<span class="Gwiazdki Gwiazdka_'.round($SredniaOcena,0).'" style="--ocena: '.round($SredniaOcena,0).';" id="radio_'.uniqid().'"></span>' : '<img src="szablony/'.DOMYSLNY_SZABLON.'/obrazki/recenzje/ocena_' . round($SredniaOcena,0) . '.png" alt="' . $GLOBALS['tlumacz']['SREDNIA_OCENA_PRODUKTU'] . ' ' . $SredniaOcena . '/5" />' )
        ); 
        
        $GLOBALS['db']->close_query($sqlRecenzje); 
        unset($sredniaOcena, $SumaGlosow, $IloscGlosow, $zapRecenzja);  
        
    } else {
      
        $this->recenzjeSrednia = array('srednia_ocena'         => 0,
                                       'ilosc_glosow'          => 0,
                                       'srednia_ocena_obrazek' => ''); 
        
    }
        
}
       
?>