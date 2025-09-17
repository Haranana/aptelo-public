<?php
chdir('../');            

//  wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

if ( isset($_POST['offset']) && (int)$_POST['offset'] > -1 && Sesje::TokenSpr() ) {

  $dane = unserialize(stripslashes((string)$_POST['dane']));

  if ((int)$_POST['offset'] == 0) {

    if ( $dane['warunek_kat'] != '3' ) {
        
      // aktualizacja kategorii ######################################################################
      // ustalenie zakresu danych do przetwarzania
      if ( $dane['zakres'] == '0' ) {
        $warunek = '';
      } else {
        $warunek  = implode(',', (array)$dane['id_kat']);
        $warunek = " categories_id IN (".$warunek.") AND ";
      }

      $zapytanie_kat = "SELECT * FROM categories_description WHERE ".$warunek." language_id = '".$dane['jezyk']."'";
      $sql_kat = $db->open_query($zapytanie_kat);

      if ( (int)$db->ile_rekordow($sql_kat) > 0 ) {
        while ( $info_kat = $sql_kat->fetch_assoc() ) {

          $id_kategorii = $info_kat['categories_id'];

          if ( $dane['sposob'] == '0' ) {

            $nazwa_kategorii = stripslashes(strip_tags((string)$info_kat['categories_name']));
            $opis_kategorii = MetaTagi::UsunFormatowanie($info_kat['categories_description']);
            $slowa_kategorii = stripslashes(strip_tags((string)$info_kat['categories_name']));

          } elseif ( $dane['sposob'] == '1' ) {

            $opis_kategorii = MetaTagi::UsunFormatowanie($info_kat['categories_description']);
 
            $tablice_we = array(
                                '{NAZWA_KATEGORII}', 
                                '{SCIEZKA_KATEGORII}',
                                '{OPIS_KATEGORII}',
                                '{DUZE_NAZWA_KATEGORII}', 
                                '{Z_DUZEJ_NAZWA_KATEGORII}', 
                                '{MALE_NAZWA_KATEGORII}');
                                
            $tablice_wy = array(
                                strip_tags((string)$info_kat['categories_name']),
                                Kategorie::SciezkaKategoriiTekst($info_kat['categories_id'], ' - '),
                                $opis_kategorii,
                                mb_convert_case(strip_tags((string)$info_kat['categories_name']), MB_CASE_UPPER, "UTF-8"), 
                                mb_convert_case(strip_tags((string)$info_kat['categories_name']), MB_CASE_TITLE, "UTF-8"), 
                                mb_convert_case(strip_tags((string)$info_kat['categories_name']), MB_CASE_LOWER, "UTF-8"));

            $nazwa_kategorii = stripslashes(str_replace( $tablice_we, $tablice_wy, (string)$dane['tytul_kat']));
            $opis_kategorii  = stripslashes(str_replace( $tablice_we, $tablice_wy, (string)$dane['opis_kat']));
            $slowa_kategorii = stripslashes(str_replace( $tablice_we, $tablice_wy, (string)$dane['slowa_kat']));

          }
          // aktualizacja tylko jezeli sa puste pola
          if ( $dane['warunek_kat'] == '1' ) {
            if ( $info_kat['categories_meta_title_tag'] == '' ) {
              $pola = array(
                      array('categories_meta_title_tag',$nazwa_kategorii),
                      array('categories_meta_desc_tag',$opis_kategorii),
                      array('categories_meta_keywords_tag',$slowa_kategorii),
              );
              $db->update_query('categories_description' , $pola, " categories_id = '".(int)$id_kategorii."' AND language_id = '".$dane['jezyk']."'");
              unset($pola);
            }
          // aktualizacja wszystkich rekordow
          } elseif ( $dane['warunek_kat'] == '0' ) {
            $pola = array(
                    array('categories_meta_title_tag',$nazwa_kategorii),
                    array('categories_meta_desc_tag',$opis_kategorii),
                    array('categories_meta_keywords_tag',$slowa_kategorii),
            );
            $db->update_query('categories_description' , $pola, " categories_id = '".(int)$id_kategorii."' AND language_id = '".$dane['jezyk']."'");
            unset($pola);
          // wyczyszczenie wszystkich rekordow
          } elseif ( $dane['warunek_kat'] == '2' ) {
            $pola = array(
                    array('categories_meta_title_tag',''),
                    array('categories_meta_desc_tag',''),
                    array('categories_meta_keywords_tag',''),
            );
            $db->update_query('categories_description' , $pola, " categories_id = '".(int)$id_kategorii."' AND language_id = '".$dane['jezyk']."'");
            unset($pola);
          }
        }
      }
      $db->close_query($sql_kat);
      unset($info_kat,$zapytanie_kat);
      
    }

    if ( $dane['warunek_producent'] != '3' ) {
      
      // aktualizacja producentow ####################################################################
      // ustalenie zakresu danych do przetwarzania
      if ( $dane['zakres'] == '0' ) {
        $warunek = '';
      } else {
        $warunek  = implode(',', (array)$dane['id_kat']);
        $warunek = " p2c.categories_id IN (".$warunek.") AND ";
      }

      $zapytanie_man = "SELECT m.manufacturers_id, m.manufacturers_name, mi.manufacturers_description, mi.manufacturers_meta_title_tag, p.products_id, p2c.categories_id FROM manufacturers m 
        LEFT JOIN manufacturers_info mi ON m.manufacturers_id = mi.manufacturers_id
        LEFT JOIN products p ON p.manufacturers_id = m.manufacturers_id
        LEFT JOIN products_to_categories p2c ON p2c.products_id = p.products_id
        WHERE ".$warunek." languages_id = '".$dane['jezyk']."'
        GROUP BY m.manufacturers_id";
        
      $sql_man = $db->open_query($zapytanie_man);

      if ( (int)$db->ile_rekordow($sql_man) > 0 ) {
        while ( $info_man = $sql_man->fetch_assoc() ) {

          $id_producenta = $info_man['manufacturers_id'];

          if ( $dane['sposob'] == '0' ) {

            $nazwa_producenta  = stripslashes(strip_tags((string)$info_man['manufacturers_name']));
            $opis_producenta = MetaTagi::UsunFormatowanie($info_man['manufacturers_description']);
            $slowa_producenta  = stripslashes(strip_tags((string)$info_man['manufacturers_name']));

          } elseif ( $dane['sposob'] == '1' ) {

            $opis_producenta = MetaTagi::UsunFormatowanie($info_man['manufacturers_description']);

            $tablice_we = array(
                                '{NAZWA_PRODUCENTA}', 
                                '{OPIS_PRODUCENTA}',
                                '{DUZE_NAZWA_PRODUCENTA}', 
                                '{Z_DUZEJ_NAZWA_PRODUCENTA}', 
                                '{MALE_NAZWA_PRODUCENTA}');
                                
            $tablice_wy = array(
                                strip_tags((string)$info_man['manufacturers_name']),
                                $opis_producenta,
                                mb_convert_case(strip_tags((string)$info_man['manufacturers_name']), MB_CASE_UPPER, "UTF-8"), 
                                mb_convert_case(strip_tags((string)$info_man['manufacturers_name']), MB_CASE_TITLE, "UTF-8"), 
                                mb_convert_case(strip_tags((string)$info_man['manufacturers_name']), MB_CASE_LOWER, "UTF-8"));

            $nazwa_producenta = stripslashes(str_replace( $tablice_we, $tablice_wy, (string)$dane['tytul_producent']));
            $opis_producenta  = stripslashes(str_replace( $tablice_we, $tablice_wy, (string)$dane['opis_producent']));
            $slowa_producenta = stripslashes(str_replace( $tablice_we, $tablice_wy, (string)$dane['slowa_producent']));

          }
          // aktualizacja tylko jezeli sa puste pola
          if ( $dane['warunek_producent'] == '1' ) {
            if ( $info_man['manufacturers_meta_title_tag'] == '' ) {
              $pola = array(
                      array('manufacturers_meta_title_tag',$nazwa_producenta),
                      array('manufacturers_meta_desc_tag',$opis_producenta),
                      array('manufacturers_meta_keywords_tag',$slowa_producenta),
              );
              $db->update_query('manufacturers_info' , $pola, " manufacturers_id = '".(int)$id_producenta."' AND languages_id = '".$dane['jezyk']."'");
              unset($pola);
            }
          // aktualizacja wszystkich rekordow
          } elseif ( $dane['warunek_producent'] == '0' ) {
            $pola = array(
                    array('manufacturers_meta_title_tag',$nazwa_producenta),
                    array('manufacturers_meta_desc_tag',$opis_producenta),
                    array('manufacturers_meta_keywords_tag',$slowa_producenta),
            );
            $db->update_query('manufacturers_info' , $pola, " manufacturers_id = '".(int)$id_producenta."' AND languages_id = '".$dane['jezyk']."'");
            unset($pola);
          // wyczyszczenie wszystkich rekordow
          } elseif ( $dane['warunek_producent'] == '2' ) {
            $pola = array(
                    array('manufacturers_meta_title_tag',''),
                    array('manufacturers_meta_desc_tag',''),
                    array('manufacturers_meta_keywords_tag',''),
            );
            $db->update_query('manufacturers_info' , $pola, " manufacturers_id = '".(int)$id_producenta."' AND languages_id = '".$dane['jezyk']."'");
            unset($pola);
          }
        }
      }
      
      $db->close_query($sql_man);
      unset($info_man,$zapytanie_man);
      
    }
  }

  if ( $dane['warunek_produkt'] != '3' ) {

    // ustalenie zakresu danych do przetwarzania
    if ( $dane['zakres'] == '0' ) {
      $warunek = '';
    } else {
      $warunek  = implode(',', (array)$dane['id_kat']);
      $warunek = " WHERE p2c.categories_id IN (".$warunek.") ";
    }

    $zapytanie_produkty = "
        SELECT DISTINCT
                  pd.products_name,
                  pd.products_description,
                  pd.products_meta_title_tag,
                  p.products_id,
                  p.products_model,
                  p.products_man_code,
                  p.products_ean,
                  p2c.categories_id,
                  mi.manufacturers_name
        FROM products p FORCE INDEX (idx_products_status)
        LEFT JOIN products_description pd ON p.products_id = pd.products_id AND pd.language_id = '".$dane['jezyk']."'
        LEFT JOIN manufacturers mi on p.manufacturers_id = mi.manufacturers_id
        LEFT JOIN products_to_categories p2c ON p2c.products_id = p.products_id" . $warunek;

    $zapytanie_produkty .= " GROUP BY p.products_id";
    $zapytanie_produkty .= " LIMIT ".(int)$_POST['offset'].", ".(int)$_POST['limit']."";

    $sql_produkty = $db->open_query($zapytanie_produkty);

    if ( (int)$db->ile_rekordow($sql_produkty) > 0 ) {
      
      while ( $info = $sql_produkty->fetch_assoc() ) {

          $id_produktu = $info['products_id'];

          if ( $dane['sposob'] == '0' ) {

            $nazwa_produktu  = stripslashes(strip_tags((string)$info['products_name']));
            $opis_produktu = MetaTagi::UsunFormatowanie($info['products_description']);
            $slowa_produktu  = stripslashes(strip_tags((string)$info['products_name']));

          } elseif ( $dane['sposob'] == '1' ) {
            
            // ustala kategorie produktu
            $zapytanie_kategorie = "select ptc.categories_id, 
                                           ptc.categories_default,
                                           cd.categories_name
                                      from products_to_categories ptc, 
                                           categories c, 
                                           categories_description cd 
                                     where ptc.categories_id = c.categories_id and 
                                           cd.categories_id = c.categories_id and 
                                           cd.language_id = '".$dane['jezyk']."' and
                                           c.categories_status = '1' and 
                                           ptc.products_id = '" . $id_produktu . "'";
                                     
            $sql_kategorie = $db->open_query($zapytanie_kategorie);
            
            $kategorie_produktu = array();
            $kategoria_produktu = array();

            while ($infr = $sql_kategorie->fetch_assoc()) {
                //
                $kategorie_produktu[] = array('id' => $infr['categories_id'], 'domyslna' => $infr['categories_default'], 'nazwa' => $infr['categories_name']);
                //            
            }  

            $db->close_query($sql_kategorie);           
            //
            // przyjmuje na poczatek pierwsza przypisana kategorie
            if ( count($kategorie_produktu) > 0 ) {
                $kategoria_produktu = $kategorie_produktu[0];
                //
                foreach ( $kategorie_produktu as $kategoria ) {
                    //
                    if ( $kategoria['domyslna'] == 1 ) {
                         //
                         $kategoria_produktu = $kategoria;
                         break;
                         //
                    }
                    //          
                }
            }
            //
            unset($kategorie_produktu);
            //         

            $opis_produktu = MetaTagi::UsunFormatowanie($info['products_description']);

            $tablice_we = array(
              '{NAZWA_PRODUKTU}', 
              '{DUZE_NAZWA_PRODUKTU}', 
              '{Z_DUZEJ_NAZWA_PRODUKTU}', 
              '{MALE_NAZWA_PRODUKTU}', 
              '{OPIS_PRODUKTU}',
              '{NR_KATALOGOWY}',
              '{KOD_PRODUCENTA}',
              '{KOD_EAN}',
              '{NAZWA_PRODUCENTA}', 
              '{DUZE_NAZWA_PRODUCENTA}', 
              '{Z_DUZEJ_NAZWA_PRODUCENTA}', 
              '{MALE_NAZWA_PRODUCENTA}',
              '{SCIEZKA_KATEGORII}',
              '{NAZWA_KATEGORII}', 
              '{DUZE_NAZWA_KATEGORII}', 
              '{Z_DUZEJ_NAZWA_KATEGORII}', 
              '{MALE_NAZWA_KATEGORII}'
            );

            $tablice_wy = array(
              strip_tags((string)$info['products_name']), 
              mb_convert_case(strip_tags((string)$info['products_name']), MB_CASE_UPPER, "UTF-8"), 
              mb_convert_case(strip_tags((string)$info['products_name']), MB_CASE_TITLE, "UTF-8"), 
              mb_convert_case(strip_tags((string)$info['products_name']), MB_CASE_LOWER, "UTF-8"), 
              $opis_produktu,
              strip_tags((string)$info['products_model']), 
              strip_tags((string)$info['products_man_code']), 
              strip_tags((string)$info['products_ean']), 
              strip_tags((string)$info['manufacturers_name']), 
              mb_convert_case(strip_tags((string)$info['manufacturers_name']), MB_CASE_UPPER, "UTF-8"), 
              mb_convert_case(strip_tags((string)$info['manufacturers_name']), MB_CASE_TITLE, "UTF-8"), 
              mb_convert_case(strip_tags((string)$info['manufacturers_name']), MB_CASE_LOWER, "UTF-8"),
              ( count($kategoria_produktu) > 0 ? Kategorie::SciezkaKategoriiTekst($kategoria_produktu['id'], ' - ') : ''),
              ( count($kategoria_produktu) > 0 ? strip_tags((string)$kategoria_produktu['nazwa']) : '' ), 
              ( count($kategoria_produktu) > 0 ? mb_convert_case(strip_tags((string)$kategoria_produktu['nazwa']), MB_CASE_UPPER, "UTF-8") : '' ), 
              ( count($kategoria_produktu) > 0 ? mb_convert_case(strip_tags((string)$kategoria_produktu['nazwa']), MB_CASE_TITLE, "UTF-8") : '' ), 
              ( count($kategoria_produktu) > 0 ? mb_convert_case(strip_tags((string)$kategoria_produktu['nazwa']), MB_CASE_LOWER, "UTF-8") : '' )
            );

            $nazwa_produktu = stripslashes(str_replace( $tablice_we, $tablice_wy, (string)$dane['tytul_produkt']));
            $opis_produktu  = stripslashes(str_replace( $tablice_we, $tablice_wy, (string)$dane['opis_produkt']));
            $slowa_produktu = stripslashes(str_replace( $tablice_we, $tablice_wy, (string)$dane['slowa_produkt']));

          }
          
          unset($kategoria_produktu);
          
          // aktualizacja tylko jezeli sa puste pola
          if ( $dane['warunek_produkt'] == '1' ) {
            if ( $info['products_meta_title_tag'] == '' ) {
              $pola = array(
                      array('products_meta_title_tag',$nazwa_produktu),
                      array('products_meta_desc_tag',$opis_produktu),
                      array('products_meta_keywords_tag',$slowa_produktu),
              );
              $db->update_query('products_description' , $pola, " products_id = '".(int)$id_produktu."' AND language_id = '".$dane['jezyk']."'");
              unset($pola);
            }
          // aktualizacja wszystkich rekordow
          } elseif ( $dane['warunek_produkt'] == '0' ) {
            $pola = array(
                    array('products_meta_title_tag',$nazwa_produktu),
                    array('products_meta_desc_tag',$opis_produktu),
                    array('products_meta_keywords_tag',$slowa_produktu),
            );
            $db->update_query('products_description' , $pola, " products_id = '".(int)$id_produktu."' AND language_id = '".$dane['jezyk']."'");
            unset($pola);
          // wyczyszczenie wszystkich rekordow
          } elseif ( $dane['warunek_produkt'] == '2' ) {
            $pola = array(
                    array('products_meta_title_tag',''),
                    array('products_meta_desc_tag',''),
                    array('products_meta_keywords_tag',''),
            );
            $db->update_query('products_description' , $pola, " products_id = '".(int)$id_produktu."' AND language_id = '".$dane['jezyk']."'");
            unset($pola);
          }
        }
      }
      $db->close_query($sql_produkty);
      unset($info,$zapytanie_produkty);
  }
  echo 'OK';

} else {
  Funkcje::PrzekierowanieURL('meta_tagi_automat.php');
}

?>