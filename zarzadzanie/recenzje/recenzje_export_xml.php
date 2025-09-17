<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

        $zapytanie = 'SELECT p.products_id, 
                             p.products_image, 
                             p.products_model, 
                             p.products_man_code,
                             p.products_status,
                             p.products_ean,
                             pd.products_id, 
                             pd.language_id, 
                             pd.products_name,  
                             pd.products_seo_url,
                             r.reviews_id,
                             r.products_id,
                             r.customers_id,
                             r.customers_name,
                             r.reviews_rating,
                             r.date_added,
                             r.approved,
                             r.comments_answers,
                             r.reviews_image,
                             rd.reviews_text,
                             m.manufacturers_name
                      FROM reviews r
                             LEFT JOIN reviews_description rd ON rd.reviews_id = r.reviews_id
                             LEFT JOIN products p ON p.products_id = r.products_id AND p.products_status = 1
                             LEFT JOIN manufacturers m on p.manufacturers_id = m.manufacturers_id
                             LEFT JOIN products_description pd ON pd.products_id = p.products_id AND pd.language_id = "' . (int)$_SESSION['domyslny_jezyk']['id'] . '"  WHERE r.approved = 1 GROUP BY r.reviews_id '; 
        
        $sql = $db->open_query($zapytanie);
        
        //
        if ((int)$db->ile_rekordow($sql) > 0) {
        
            $ciag_do_zapisu = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            $ciag_do_zapisu .= '<feed xmlns:vc="http://www.w3.org/2007/XMLSchema-versioning"' . "\n";
            $ciag_do_zapisu .= 'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"' . "\n";
            $ciag_do_zapisu .= 'xsi:noNamespaceSchemaLocation=' . "\n";
            $ciag_do_zapisu .= '"http://www.google.com/shopping/reviews/schema/product/2.3/product_reviews.xsd">' . "\n";
            $ciag_do_zapisu .= '<version>2.3</version>' . "\n";
            $ciag_do_zapisu .= '<aggregator>' . "\n";
            $ciag_do_zapisu .= '   <name>' . DANE_NAZWA_FIRMY_SKROCONA . '</name>' . "\n";
            $ciag_do_zapisu .= '</aggregator>' . "\n";
            $ciag_do_zapisu .= '<publisher>' . "\n";
            $ciag_do_zapisu .= '   <name>' . DANE_NAZWA_FIRMY_SKROCONA . '</name>' . "\n";
            $ciag_do_zapisu .= '</publisher>' . "\n";
            $ciag_do_zapisu .= '<reviews>' . "\n";
            
            while ($info = $sql->fetch_assoc()) {
              
                $linkSeo = '';
                
                if ( isset($info['products_name']) && !empty($info['products_name']) ) {
                     //
                     $linkSeo = ((isset($info['products_seo_url']) && trim($info['products_seo_url']) != '') ? $info['products_seo_url'] : strip_tags((string)$info['products_name']));
                     //
                }
            
                $ciag_do_zapisu .= '  <review>' . "\n";
                $ciag_do_zapisu .= '      <review_id>' . $info['reviews_id'] . '</review_id>' . "\n";
                $ciag_do_zapisu .= '      <reviewer>' . "\n";
                $ciag_do_zapisu .= '          <name>' . $info['customers_name'] . '</name>' . "\n";
                $ciag_do_zapisu .= '      </reviewer>' . "\n";
                $ciag_do_zapisu .= '      <review_timestamp>' . str_replace('#', 'Z', (str_replace('*', 'T', ((Funkcje::czyNiePuste($info['date_added'])) ? date('Y-m-d*H:i:00#',FunkcjeWlasnePHP::my_strtotime($info['date_added'])) : '-')))) . '</review_timestamp>' . "\n";
                $ciag_do_zapisu .= '      <content>' . $info['reviews_text'] . '</content>' . "\n";
                $ciag_do_zapisu .= '      <review_url type="singleton">' . Seo::link_SEO( $linkSeo, $info['reviews_id'], 'recenzja' ) . '</review_url>' . "\n";
                
                if ( !empty($info['reviews_image']) ) {
                     //
                     if ( file_exists('../grafiki_inne/' . $info['reviews_image']) ) {
                          //
                          $ciag_do_zapisu .= '      <reviewer_images>' . "\n";
                          $ciag_do_zapisu .= '          <reviewer_image>' . "\n";
                          $ciag_do_zapisu .= '              <url>' . ADRES_URL_SKLEPU . '/grafiki_inne/' . $info['reviews_image'] . '</url>' . "\n";
                          $ciag_do_zapisu .= '          </reviewer_image>' . "\n";
                          $ciag_do_zapisu .= '      </reviewer_images>' . "\n";                         
                          //
                     }
                     //
                }
                
                $ciag_do_zapisu .= '      <ratings>' . "\n";
                $ciag_do_zapisu .= '          <overall min="1" max="5">' . $info['reviews_rating'] . '</overall>' . "\n";
                $ciag_do_zapisu .= '      </ratings>' . "\n";
                $ciag_do_zapisu .= '      <products>' . "\n";
                $ciag_do_zapisu .= '          <product>' . "\n";
                
                if ( (isset($info['products_ean']) && !empty($info['products_ean'])) || ($info['products_man_code'] && !empty($info['products_man_code'])) || ($info['manufacturers_name'] && !empty($info['manufacturers_name'])) ) {
                
                    $ciag_do_zapisu .= '             <product_ids>' . "\n";
                    
                    if ( isset($info['products_ean']) && !empty($info['products_ean']) ) {
                         //
                         $ciag_do_zapisu .= '                <gtins>' . "\n";
                         $ciag_do_zapisu .= '                   <gtin>' . $info['products_ean'] . '</gtin>' . "\n";
                         $ciag_do_zapisu .= '                </gtins>' . "\n";
                         //
                    }
                    
                    if ( $info['products_model'] && !empty($info['products_model']) ) {
                         //
                         $ciag_do_zapisu .= '                <mpns>' . "\n";
                         $ciag_do_zapisu .= '                   <mpn>' . $info['products_model'] . '</mpn>' . "\n";
                         $ciag_do_zapisu .= '                </mpns>' . "\n";
                         //
                    }
                    
                    if ( $info['products_man_code'] && !empty($info['products_man_code']) ) {
                         //
                         $ciag_do_zapisu .= '                <skus>' . "\n";
                         $ciag_do_zapisu .= '                   <sku>' . $info['products_man_code'] . '</sku>' . "\n";
                         $ciag_do_zapisu .= '                </skus>' . "\n";                    
                         //
                    }
                    
                    if ( $info['manufacturers_name'] && !empty($info['manufacturers_name']) ) {
                         //
                         $ciag_do_zapisu .= '                <brands>' . "\n";
                         $ciag_do_zapisu .= '                   <brand>' . $info['manufacturers_name'] . '</brand>' . "\n";
                         $ciag_do_zapisu .= '                </brands>' . "\n";
                         //
                    }

                    $ciag_do_zapisu .= '             </product_ids>' . "\n";
                    
                }
                
                if ( isset($info['products_name']) && !empty($info['products_name']) ) {                
                     //
                     
                     //$ciag_do_zapisu .= '           <product_id>' . $info['products_id'] . '</product_id>' . "\n";
                     $ciag_do_zapisu .= '             <product_url>' . Seo::link_SEO( $linkSeo, $info['products_id'], 'produkt' ) . '</product_url>' . "\n";
                     $ciag_do_zapisu .= '             <product_name>' . strip_tags((string)$info['products_name']) . '</product_name>' . "\n";
                     
                     //
                }
                
                $ciag_do_zapisu .= '          </product>' . "\n";
                $ciag_do_zapisu .= '      </products>' . "\n";
                $ciag_do_zapisu .= '      <is_spam>false</is_spam>' . "\n";
                $ciag_do_zapisu .= '  </review>' . "\n";
                
                unset($linkSeo);
            
            }
            
            //
            $db->close_query($sql);
            unset($info);      

            $ciag_do_zapisu .= '</reviews>' . "\n";
            $ciag_do_zapisu .= '</feed>' . "\n";
            
            header("Content-Type: application/force-download\n");
            header("Cache-Control: cache, must-revalidate");   
            header("Pragma: public");
            header("Content-Disposition: attachment; filename=eksport_recenzje_" . date("d-m-Y") . ".xml");
            print $ciag_do_zapisu;
            exit;   
            
        }
        
        $db->close_query($sql);        

}