<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>

    <div id="naglowek_cont">Szczegóły transakcji aukcji</div>
    <div id="cont">

      <?php
    
      if ( !isset($_GET['id_poz']) ) {
           $_GET['id_poz'] = 0;
      }    
    
      if ( isset($_GET['id']) && (int)$_GET['id'] > 0 ) {
        
          $zapytanie = "SELECT a.*, t.*, auc.*, a.auction_status AS Status  FROM allegro_auctions_sold a 
                     LEFT JOIN allegro_transactions t ON t.auction_id = a.auction_id 
                     LEFT JOIN allegro_auctions auc ON auc.auction_id = a.auction_id
                         WHERE allegro_auction_id = '" . (int)$_GET['id_poz'] . "' AND t.transaction_id = '" . (int)$_GET['id'] . "'";
                         
      } else {
        
          $zapytanie = "SELECT * FROM allegro_auctions_sold a LEFT JOIN allegro_auctions auc ON auc.auction_id = a.auction_id WHERE a.allegro_auction_id = '" . (int)$_GET['id_poz'] . "'";
          
      }    

      $sql = $db->open_query($zapytanie);

      if ((int)$db->ile_rekordow($sql) > 0) {
        
        $info = $sql->fetch_assoc();

        ?>
        <div class="info_content">
        
          <div class="ObramowanieForm" style="margin-top:10px;">
          
            <table>
            
              <tr class="div_naglowek">
                <td colspan="2" style="padding-left:10px;">Numer aukcji: <?php echo $info['auction_id']; ?></td>
              </tr>
              
              <tr class="PozycjaAllegroForm DodatkowayOdstep">
                <td>Produkt:</td>
                <td>
                   <?php
                   $cechy = '';

                   if ( isset($info['products_stock_attributes']) && $info['products_stock_attributes'] != '' ) {

                     $tablica_kombinacji_cech = explode(';', (string)$info['products_stock_attributes']);
                    
                     for ( $t = 0, $c = count($tablica_kombinacji_cech); $t < $c; $t++ ) {
                    
                       $tablica_wartosc_cechy = explode('-', (string)$tablica_kombinacji_cech[$t]);

                       $nazwa_cechy = Funkcje::NazwaCechy( (int)$tablica_wartosc_cechy['0'] );
                       $nazwa_wartosci_cechy = Funkcje::WartoscCechy( (int)$tablica_wartosc_cechy['1'] );

                       $cechy .= '<span class="MaleInfoCecha">'.$nazwa_cechy . ': <b>' . $nazwa_wartosci_cechy . '</b></span>';
                      
                       unset($tablica_wartosc_cechy);
                      
                     }
                    
                     unset($tablica_kombinacji_cech);
                    
                   }          

                   echo '<b><a href="produkty/produkty_edytuj.php?id_poz='.$info['products_id'].'">' . $info['products_name'] . '</a></b>' . ((!empty($cechy)) ? $cechy : '');
                   
                   unset($cechy);
                   ?>
                </td>
              </tr>              

              <tr class="PozycjaAllegroForm DodatkowayOdstep">
                <td>Nick kupującego:</td>
                <td><?php echo $info['buyer_name']; ?></td>
              </tr>
              
              <tr class="PozycjaAllegroForm DodatkowayOdstep">
                <td>Adres e-mail:</td>
                <td><?php echo $info['buyer_email_address']; ?></td>
              </tr>              
              
              <tr class="PozycjaAllegroForm DodatkowayOdstep">
                <td>Status zakupu:</td>
                <td>
                   <?php
                   if ( $info['Status'] == '1' ) {
                       echo 'oferta zakończona sprzedażą';
                   } elseif ( $info['Status'] == '-1' ) {
                       echo 'oferta odwołana';
                   } elseif ( $info['Status'] == '0' ) {
                       echo 'oferta nie zakończona sprzedażą';
                   }
                   ?>
                </td>
              </tr> 

              <tr class="PozycjaAllegroForm DodatkowayOdstep">
                <td>Data zakupu:</td>
                <td><?php echo date('d-m-Y H:i:s',$info['auction_buy_date']); ?></td>
              </tr>                 

              <tr class="PozycjaAllegroForm DodatkowayOdstep">
                <td>Status odwołania:</td>
                <td>
                   <?php
                   if ( $info['auction_lost_status'] == '1' ) {
                     echo 'oferta odwołana przez sprzedającego';
                   } elseif ( $info['auction_lost_status'] == '2' ) {
                     echo 'oferta odwołana przez administratora serwisu';
                   } elseif ( $info['auction_lost_status'] == '0' ) {
                     echo 'oferta nieodwołana';
                   }               
                   ?>
                </td>
              </tr>              
              
              <?php if ( isset($_GET['id']) && (int)$_GET['id'] > 0 ) { ?>

                  <tr class="PozycjaAllegroForm DodatkowayOdstep">
                    <td>Numer transakcji:</td>
                    <td><?php echo $info['transaction_id']; ?></td>
                  </tr>              

                  <tr class="PozycjaAllegroForm DodatkowayOdstep">
                    <td>Kwota transakcji:</td>
                    <td><?php echo $waluty->FormatujCene($info['post_buy_form_amount']); ?></td>
                  </tr>              

                  <tr class="PozycjaAllegroForm DodatkowayOdstep">
                    <td>Wartość towaru:</td>
                    <td><?php echo $waluty->FormatujCene($info['post_buy_form_it_amount']); ?></td>
                  </tr>              

                  <tr class="PozycjaAllegroForm DodatkowayOdstep">
                    <td>Koszt dostawy:</td>
                    <td><?php echo $waluty->FormatujCene($info['post_buy_form_postage_amount']); ?></td>
                  </tr>              

                  <?php if ( $info['post_buy_form_msg_to_seller'] != '' ) { ?>
                  
                  <tr class="PozycjaAllegroForm DodatkowayOdstep">
                    <td>Informacja od kupującego:</td>
                    <td><?php echo $info['post_buy_form_msg_to_seller']; ?></td>
                  </tr>              
                  
                  <?php } ?>

                  <?php if ( $info['shipping_post_buy_form_adr_phone'] != '' ) { ?>
                  
                  <tr class="PozycjaAllegroForm DodatkowayOdstep">
                    <td>Telefon do kupującego:</td>
                    <td><?php echo $info['shipping_post_buy_form_adr_phone']; ?></td>
                  </tr>              
                  
                  <?php } ?>

                  <tr class="PozycjaAllegroForm DodatkowayOdstep">
                    <td>Rodzaj płatności:</td>
                    <td><?php echo $info['post_buy_form_pay_type']; ?></td>
                  </tr>       

                  <tr class="PozycjaAllegroForm DodatkowayOdstep">
                    <td>Data rozpoczęcia płatności:</td>
                    <td><?php 
                    if ( $info['post_buy_form_date_init'] != '0000-00-00 00:00:00' && $info['post_buy_form_date_init'] != '' ) {
                      echo $info['post_buy_form_date_init'];
                    }
                    ?></td>
                  </tr>       

                  <tr class="PozycjaAllegroForm DodatkowayOdstep">
                    <td>Data zakończenia płatności:</td>
                    <td><?php
                    if ( $info['post_buy_form_date_recv'] != '0000-00-00 00:00:00' && $info['post_buy_form_date_recv'] != '' ) {
                      echo $info['post_buy_form_date_recv'];
                    }
                  ?></td>
                  </tr>       

                  <tr class="PozycjaAllegroForm DodatkowayOdstep">
                    <td>Status płatności:</td>
                    <td><?php echo $info['post_buy_form_pay_status']; ?></td>
                  </tr>              

                  <tr class="PozycjaAllegroForm DodatkowayOdstep">
                    <td>Rodzaj dostawy:</td>
                    <td><?php echo $info['post_buy_form_shipment_id']; ?></td>
                  </tr> 

              <?php } else { ?>

                  <tr class="PozycjaAllegroForm DodatkowayOdstep">
                    <td colspan="2"><span class="ostrzezenie" style="margin:10px 0px 10px 0px">Brak wypełnionego formularza pozakupowego</span></td>
                  </tr>               
              
              <?php } ?>

            </table>
            
          </div>
          
        </div>

        <?php
        
        $db->close_query($sqla);
        unset($zapytaniea, $infoa);

      } else {

        echo '<div class="pozycja_edytowana">Brak danych do wyświetlenia</div>';

      }
      $db->close_query($sql);
      unset($zapytanie, $info);

      ?>
      
      <div class="przyciski_dolne">
        <?php if ( isset($_GET['aukcja_id']) ) { ?>
            <button type="button" class="przyciskNon" onclick="cofnij('allegro_sprzedaz_tranzakcja','<?php echo Funkcje::Zwroc_Get(array('x','y')); ?>','allegro');">Powrót</button> 
        <?php } else { ?>
            <button type="button" class="przyciskNon" onclick="cofnij('allegro_sprzedaz','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','allegro');">Powrót</button> 
        <?php } ?>
      </div> 
      
    </div>
    
    <?php
    include('stopka.inc.php');    
    
} ?>
