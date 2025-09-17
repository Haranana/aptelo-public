<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone && isset($_GET['id_klienta']) && (int)$_GET['id_klienta'] > 0 && Sesje::TokenSpr()) {   

   $zapytaPkt = 'SELECT DISTINCT
                        p.products_id, 
                        p.products_image, 
                        p.products_status, 
                        pd.products_name, 
                        r.reviews_id,
                        r.reviews_rating,
                        r.date_added,
                        r.approved,
                        rd.reviews_text
                   FROM reviews r
                        LEFT JOIN reviews_description rd ON rd.reviews_id = r.reviews_id
                        LEFT JOIN products p ON p.products_id = r.products_id
                        LEFT JOIN products_description pd ON pd.products_id = p.products_id AND pd.language_id = "' . (int)$_SESSION['domyslny_jezyk']['id'] . '"
                  WHERE r.customers_id = "' . (int)$_GET['id_klienta'] . '" ORDER BY r.date_added desc';
                  
    $sqlPkt = $db->open_query($zapytaPkt);
    
    if ((int)$db->ile_rekordow($sqlPkt) > 0) {
        //
        ?>
        <div class="ObramowanieTabeli" style="padding:2px 2px 2px 1px">
        
            <table class="listing_tbl">
            
              <tr class="div_naglowek">
                <td>Id opinii</td>
                <td class="ListingSchowajMobile">Foto</td>
                <td>Nazwa produktu</td>
                <td class="ListingSchowajMobile">Treść</td>
                <td>Ocena</td>
                <td>Zatwie- dzona</td>
                <td>Dodano</td>
                <td>Status produktu</td>
              </tr>           
              
              <?php 
              while ($infoPkt = $sqlPkt->fetch_assoc()) {
                ?>
                <tr class="pozycja_off">                                 
                  <td><?php echo $infoPkt['reviews_id']; ?></td>
                  <td class="ListingSchowajMobile"><?php echo Funkcje::pokazObrazek($infoPkt['products_image'], $infoPkt['products_name'], '40', '40'); ?></td>
                  <td class="LinkKoszyk" style="text-align:left"><?php echo '<a href="recenzje/recenzje_edytuj.php?id_poz=' . $infoPkt['reviews_id'] . '"><b>' . $infoPkt['products_name']  . '</b></a>'; ?></td>
                  <td class="ListingSchowajMobile" style="text-align:left"><?php echo $infoPkt['reviews_text']; ?></td>
                  <td><?php echo '<em class="TipChmurka"><b>Ocena '.$infoPkt['reviews_rating'].'/5</b><img src="obrazki/recenzje/star_'.$infoPkt['reviews_rating'].'.png" alt="Ocena '.$infoPkt['reviews_rating'].'/5" /></em>'; ?></td>
                  <td>
                      <?php
                      if ($infoPkt['approved'] == '1') { $obraz = '<img src="obrazki/aktywny_on.png" alt="Ta recenzja jest zatwierdzona" />'; } else { $obraz = '<img src="obrazki/aktywny_off.png" alt="Ta recenzja nie jest zatwierdzona" />'; }
                      echo $obraz;
                      unset($obraz);
                      ?>
                  </td>
                  <td><?php echo ((!empty($infoPkt['date_added'])) ? date('d-m-Y H:i', FunkcjeWlasnePHP::my_strtotime($infoPkt['date_added'])) : ''); ?></td>
                  <td>
                      <?php

                      if ($infoPkt['products_status'] == '1') { $obraz = '<img src="obrazki/aktywny_on.png" alt="Ten produkt jest aktywny" />'; $tekst_opisu = 'Ten produkt jest aktywny'; } else { $obraz = '<img src="obrazki/aktywny_off.png" alt="Ten produkt jest nieaktywny" />'; $tekst_opisu = 'Ten produkt jest nieaktywny'; }
                      echo $obraz;
                                    
                      unset($obraz, $tekst_opisu);

                      ?>
                  </td>                                        
                </tr>
              <?php 
              } 
              ?>
            
            </table>
            
        </div>
        <?php
    }

}
?>