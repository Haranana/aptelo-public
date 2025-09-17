<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone && isset($_GET['id_klienta']) && (int)$_GET['id_klienta'] > 0 && Sesje::TokenSpr()) {   

    $zapytanie = "select c.customers_id, 
                       ci.customers_info_date_of_last_logon,
                       ci.customers_info_number_of_logons,
                       ci.customers_info_date_account_created,
                       c.customers_guest_account,
                       IF (DATEDIFF(now(),ci.customers_info_date_account_created) = 0, (DATEDIFF(now(),ci.customers_info_date_account_created)), ((DATEDIFF(now(),ci.customers_info_date_account_created)) - 1)) as ilosc_dni_od_rejestracji,
                       IF (ci.customers_info_number_of_logons > 0, (DATEDIFF(now(),ci.customers_info_date_of_last_logon)), 0) as ilosc_dni_od_logowania                       
                  from customers c
             left join customers_info ci on c.customers_id = ci.customers_info_id
                 where c.customers_id = '" . (int)$_GET['id_klienta'] . "'";
                 
    $sql = $db->open_query($zapytanie);

    $info = $sql->fetch_assoc();                   
    ?>
    
    <p>
      <label>Data rejestracji:</label>
      <span class="Daty"><?php echo date('d-m-Y H:i',FunkcjeWlasnePHP::my_strtotime($info['customers_info_date_account_created'])); ?></span>
    </p> 
    
    <?php if ( $info['customers_guest_account'] == '0' ) { ?>

    <p>
      <label>Data ostatniego logowania:</label>
      <span class="daty"><?php echo ((Funkcje::czyNiePuste($info['customers_info_date_of_last_logon'])) ? date('d-m-Y H:i',FunkcjeWlasnePHP::my_strtotime($info['customers_info_date_of_last_logon'])) : '-'); ?></span>
    </p>   
    
    <p>
      <label>Ilość dni od daty rejestracji:</label>
      <span class="daty"><?php echo $info['ilosc_dni_od_rejestracji']; ?> dni</span>
    </p>       
    
    <p>
      <label>Ilość dni od ostatniego logowania:</label>
      <span class="daty"><?php echo $info['ilosc_dni_od_logowania']; ?> dni</span>
    </p>       

    <p>
      <label>Ilość logowań:</label>
      <span class="daty"><?php echo $info['customers_info_number_of_logons']; ?></span>
    </p> 

    <?php } ?>

    <hr style="color:#82b4cd;border-top: 1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:96%;" />
    
    <?php
    $IloscZamowienKlienta = Klienci::pokazIloscZamowienKlienta($info['customers_id']);
    ?>
    
    <p>
      <label>Ilość zamówień:</label>
      <span class="daty"><?php echo $IloscZamowienKlienta; ?></span>
    </p>  

    <p>
      <label>Ilość zamówionych produktów:</label>
      <span class="daty">
          <?php
          $ile_pozycji = '-';
          $zapytanie_stat = "SELECT sum(op.products_quantity) as ilosc_produktow
                               FROM orders o, orders_products op 
                              WHERE o.customers_id = '".$info['customers_id']."' AND
                                    o.orders_id = op.orders_id";
          $sql_stat = $db->open_query($zapytanie_stat);
          $wyn = $sql_stat->fetch_assoc();
          if ($wyn['ilosc_produktow'] > 0) {
              $ile_pozycji = $wyn['ilosc_produktow'];
          }
          echo $ile_pozycji;
          $db->close_query($sql_stat);
          unset($ile_pozycji, $zapytanie_stat, $ile_pozycji);
          ?>
      </span>
    </p>   

    <?php
    $zapytanieWaluty = "select currencies_id, code, title, symbol from currencies";
    $sqlWaluta = $db->open_query($zapytanieWaluty);
    
    while ($infr = $sqlWaluta->fetch_assoc()) { 
    
      $wartoscZamowien = Klienci::pokazWartoscZamowienKlienta($info['customers_id'], 0, $infr['code'], $infr['currencies_id']);
      
      if ($wartoscZamowien > 0) {
          ?>
          <p>
              <label>Wartość wszystkich zamówień w <?php echo $infr['title']; ?>:</label>
              <span class="daty"><?php echo $wartoscZamowien; ?></span>
          </p>                           
    
      <?php
      }
      unset($wartoscZamowien);
      
    }
    
    $db->close_query($sqlWaluta);
    unset($zapytanieWaluty, $infr);                          
    
    ?>
    
    <br />
    
    <div class="ObramowanieTabeli" style="width:94%; margin:0px auto; padding:2px 2px 2px 1px">
      <table class="listing_tbl">
      
          <tr class="div_naglowek">
            <td>&nbsp;</td>
            <td class="ListingSchowajMobile">Ostatnie 7 dni</td>
            <td class="ListingSchowajMobile">Ostatnie 30 dni</td>
            <td>Ostatnie 90 dni</td>
            <td>Ostatnie 180 dni</td>
            <td>Ogółem</td>
          </tr>   

          <tr class="pozycja_off">
              <td class="ListingSchowajMobile">Ilość zamówień</td>
              <td><b><?php echo Klienci::pokazIloscZamowienKlienta($info['customers_id'], 7); ?></b></td>
              <td class="ListingSchowajMobile"><b><?php echo Klienci::pokazIloscZamowienKlienta($info['customers_id'], 30); ?></b></td>
              <td><b><?php echo Klienci::pokazIloscZamowienKlienta($info['customers_id'], 90); ?></b></td>
              <td><b><?php echo Klienci::pokazIloscZamowienKlienta($info['customers_id'], 180); ?></b></td>
              <td><b><?php echo $IloscZamowienKlienta; ?></b></td>
          </tr>
          
          <?php
          $zapytanieWaluty = "select currencies_id, code, title, symbol from currencies";
          $sqlWaluta = $db->open_query($zapytanieWaluty);

          while ($infr = $sqlWaluta->fetch_assoc()) {
              ?>
              
              <tr class="pozycja_off">
                  <td>Wartość zamówień w <?php echo $infr['title']; ?></td>
                  <td class="ListingSchowajMobile"><b>
                      <?php
                      $WartTMP = Klienci::pokazWartoscZamowienKlienta($info['customers_id'], 7, $infr['code'], $infr['currencies_id']); 
                      if ( $WartTMP > 0) {
                          echo $WartTMP;
                        } else {
                          echo $WartTMP;
                      }
                      unset($WartTMP);
                      ?></b>
                  </td> 
                  <td class="ListingSchowajMobile"><b>
                      <?php
                      $WartTMP = Klienci::pokazWartoscZamowienKlienta($info['customers_id'], 30, $infr['code'], $infr['currencies_id']); 
                      if ( $WartTMP > 0) {
                          echo $WartTMP;
                        } else {
                          echo $WartTMP;
                      }
                      unset($WartTMP);
                      ?></b>
                  </td>
                  <td><b>
                      <?php
                      $WartTMP = Klienci::pokazWartoscZamowienKlienta($info['customers_id'], 90, $infr['code'], $infr['currencies_id']); 
                      if ( $WartTMP > 0) {
                          echo $WartTMP;
                        } else {
                          echo $WartTMP;
                      }
                      unset($WartTMP);
                      ?></b>
                  </td>
                  <td><b>
                      <?php
                      $WartTMP = Klienci::pokazWartoscZamowienKlienta($info['customers_id'], 180, $infr['code'], $infr['currencies_id']); 
                      if ( $WartTMP > 0) {
                          echo $WartTMP;
                        } else {
                          echo $WartTMP;
                      }
                      unset($WartTMP);
                      ?></b>
                  </td>
                  <td><b>
                      <?php
                      $WartTMP = Klienci::pokazWartoscZamowienKlienta($info['customers_id'], 0, $infr['code'], $infr['currencies_id']); 
                      if ( $WartTMP > 0) {
                          echo $WartTMP;
                        } else {
                          echo $WartTMP;
                      }
                      unset($WartTMP);
                      ?></b>
                  </td>                                         
              </tr>
              
              <?php
          }

          $db->close_query($sqlWaluta);
          unset($zapytanieWaluty, $infr);                          

          ?>                
          
      </table>
    </div>
    
    <?php
    
    $db->close_query($sql);
    unset($zapytanie, $info); 
    
    unset($IloscZamowienKlienta);
      
}
?>