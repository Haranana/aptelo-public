<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone && isset($_GET['id_klienta']) && (int)$_GET['id_klienta'] > 0 && Sesje::TokenSpr()) {   

    $zapytanie_koszyk = "SELECT cb.customers_basket_id, 
                                cb.customers_basket_quantity, 
                                cb.products_id, 
                                cb.customers_basket_date_added, 
                                cb.products_comments,
                                cb.products_text_fields,
                                p.products_status, 
                                p.products_image, 
                                p.products_jm_id,
                                pd.products_name,
                                pj.products_jm_quantity_type
                            FROM customers_basket cb 
                       LEFT JOIN products p ON p.products_id = cb.products_id 
                       LEFT JOIN products_jm pj ON p.products_jm_id = pj.products_jm_id
                       LEFT JOIN products_description pd ON cb.products_id = pd.products_id AND pd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."' 
                           WHERE cb.customers_id = '" . (int)$_GET['id_klienta'] . "'";
                           
    $sql_koszyk = $db->open_query($zapytanie_koszyk);

    if ((int)$db->ile_rekordow($sql_koszyk) > 0) {

      ?>
      <div class="ObramowanieTabeli" style="padding:2px 2px 2px 1px">
      
        <table class="listing_tbl">
        
          <tr class="div_naglowek">
            <td>Id</td>
            <td class="ListingSchowajMobile">Foto</td>
            <td>Nazwa produktu</td>
            <td>Ilość</td>
            <td>Status</td>
            <td>Dodano</td>
            <td></td>
          </tr>
          
          <?php 
          while ($info_koszyk = $sql_koszyk->fetch_assoc()) {
            
            $id_do_usuniecia = $info_koszyk['products_id'];
            
            // usuwa znacznik unikalnosci jezeli jest wlaczone dodawanie produktow jako osobnych pozycji
            if ( strpos((string)$info_koszyk['products_id'], 'U') > -1 ) {
                
                $info_koszyk['products_id'] = substr((string)$info_koszyk['products_id'], 0, strpos((string)$info_koszyk['products_id'], 'U'));
            
            }               
            
            $tgm = Funkcje::pokazObrazek($info_koszyk['products_image'], $info_koszyk['products_name'], '40', '40');
            $zmienne_do_przekazania = '?id_poz='.(int)$_GET['id_klienta'].'&product_id='.$id_do_usuniecia.'&zakladka=2'; 
            unset($id_do_usuniecia);

            ?>
            <tr class="pozycja_off">
              <?php
              // czy produkt ma cechy
              $CechaPrd = Produkty::CechyProduktuPoId($info_koszyk['products_id']);
              $JakieCechy = '';
              if (count($CechaPrd) > 0) {
                  //
                  for ($a = 0, $c = count($CechaPrd); $a < $c; $a++) {
                      $JakieCechy .= '<div class="WglCecha">' . $CechaPrd[$a]['nazwa_cechy'] . ': <b>' . $CechaPrd[$a]['wartosc_cechy'] . '</b></div>';
                  }
                  //
              }
              
              $KomentarzProduktu = '';
              if ( $info_koszyk['products_comments'] != '' ) $KomentarzProduktu .= '<div class="WglCecha">Komentarz: <b>' . $info_koszyk['products_comments'] . '</b></div>';
              
              $PolaTekstowe = '';
              if (!empty($info_koszyk['products_text_fields'])) {
                //
                $PoleTxt = Funkcje::serialCiag($info_koszyk['products_text_fields']);
                if ( count($PoleTxt) > 0 ) {
                    foreach ( $PoleTxt as $WartoscTxt ) {
                        // jezeli pole to plik
                        if ( $WartoscTxt['typ'] == 'plik' ) {
                            $PolaTekstowe .= '<div class="WglCecha">' . $WartoscTxt['nazwa'] . ': <a target="_blank" href="/inne/wgranie.php?src=' . base64_encode(str_replace('.', ';', (string)$WartoscTxt['tekst'])) . '"><b>wgrany plik</b></a></div>';
                          } else {
                            $PolaTekstowe .= '<div class="WglCecha">' . $WartoscTxt['nazwa'] . ': <b>' . $WartoscTxt['tekst'] . '</b></div>';
                        }                                          
                    }
                }
                unset($PoleTxt);
                //
              }               
              ?>
              <td><?php echo Produkty::IdProduktuCech($info_koszyk['products_id']); ?></td>
              <td class="ListingSchowajMobile"><?php echo $tgm; ?></td>
              <td class="LinkKoszyk" style="text-align:left"><?php echo '<a href="produkty/produkty_edytuj.php?id_poz=' . Produkty::IdProduktuCech($info_koszyk['products_id']) . '"><b>' . $info_koszyk['products_name']  . '</b></a>' . $JakieCechy . $KomentarzProduktu . $PolaTekstowe; ?></td>
              
              <?php
              unset($JakieCechy, $KomentarzProduktu, $PolaTekstowe);

              // jezeli calkowite
              if ( ( $info_koszyk['products_jm_quantity_type'] == 1 && (int)$info_koszyk['products_jm_id'] != 0 ) ) {
                  //
                  // sprawdzi czy wartosc ilosci nie jest ulamkowa
                  if ( (int)$info_koszyk['customers_basket_quantity'] == $info_koszyk['customers_basket_quantity'] ) {
                       $info_koszyk['customers_basket_quantity'] = (int)$info_koszyk['customers_basket_quantity'];
                  }
                  //
              }  
              ?>
              
              <td><?php echo $info_koszyk['customers_basket_quantity']; ?></td>
              <td>
                  <?php

                  if ($info_koszyk['products_status'] == '1') { $obraz = '<img src="obrazki/aktywny_on.png" alt="Ten produkt jest aktywny" />'; $tekst_opisu = 'Ten produkt jest aktywny'; } else { $obraz = '<img src="obrazki/aktywny_off.png" alt="Ten produkt jest nieaktywny" />'; $tekst_opisu = 'Ten produkt jest nieaktywny'; }
                  echo '<em class="TipChmurka">'.$obraz.'<b>'.$tekst_opisu.'</b></em>';
                                
                  unset($obraz, $tekst_opisu);

                  ?>
              </td>
              <td><?php echo ((!empty($info_koszyk['customers_basket_date_added'])) ? date('d-m-Y', FunkcjeWlasnePHP::my_strtotime($info_koszyk['customers_basket_date_added'])) : ''); ?></td>
              <td><a class="TipChmurka" href="klienci/klienci_kasuj_koszyk.php<?php echo $zmienne_do_przekazania; ?>"><b>Usuń tę pozycję</b><img src="obrazki/kasuj.png" alt="Usuń tę pozycję" /></a></td>
            </tr>
            
          <?php } ?>
          
        </table>
        
      </div>
      <?php
      
   }
   
   $db->close_query($sql_koszyk);
   unset($zapytanie_koszyk);  
   
}
?>