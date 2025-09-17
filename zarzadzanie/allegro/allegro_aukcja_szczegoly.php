<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    // wczytanie naglowka HTML
    include('naglowek.inc.php');

    $AllegroRest = new AllegroRest( array('allegro_user' => $_SESSION['domyslny_uzytkownik_allegro']) );

    $TablicaUzytkownikow = $AllegroRest->TablicaUzytkownikow();

    ?>

    <div id="naglowek_cont">Szczegóły aukcji</div>

    <div id="cont">

      <?php
    
      if ( !isset($_GET['id_poz']) ) {
           $_GET['id_poz'] = 0;
      }    
    
      $zapytanie = "SELECT * FROM allegro_auctions WHERE allegro_id ='" .(int)$_GET['id_poz']. "'";

      $sql = $db->open_query($zapytanie);

      if ((int)$db->ile_rekordow($sql) > 0) {
        
        $info = $sql->fetch_assoc();

        if ( $_SESSION['domyslny_uzytkownik_allegro'] == $info['auction_seller'] && $info['archiwum_allegro'] != '1' ) {

            $PrzetwarzanaAukcja = $AllegroRest->AukcjaSzczegoly($info['auction_id']);

            if ( isset($PrzetwarzanaAukcja) && count($PrzetwarzanaAukcja) > 0 ) {

                $DataStart = '';
                $DataEnd = '';
                $Modification = time();

                if ( $PrzetwarzanaAukcja->publication->startingAt != '' ) {
                    $DataStart = $PrzetwarzanaAukcja->publication->startingAt;
                }
                if ( $PrzetwarzanaAukcja->publication->endingAt != '' ) {
                    $DataEnd = $PrzetwarzanaAukcja->publication->endingAt;
                }
                $pola = array(
                        array('products_name',$filtr->process($PrzetwarzanaAukcja->name)),
                        array('allegro_category',(int)$PrzetwarzanaAukcja->category->id),
                        array('auction_price',(float)$PrzetwarzanaAukcja->sellingMode->price->amount),
                        array('auction_quantity',(int)$PrzetwarzanaAukcja->stock->available),
                        array('auction_status',$PrzetwarzanaAukcja->publication->status),
                        array('products_buy_now_price',(float)$PrzetwarzanaAukcja->sellingMode->price->amount),
                        array('auction_hits',(int)$PrzetwarzanaAukcja->stats->visitsCount),
                        array('products_sold',(int)$PrzetwarzanaAukcja->stock->sold),
                        array('auction_watching',(int)$PrzetwarzanaAukcja->stats->watchersCount),
                        array('auction_last_modification',$Modification)
                );

                $db->update_query('allegro_auctions' , $pola, " auction_id = '".$PrzetwarzanaAukcja->id."'");
                unset($pola);

                if ( $DataStart != '' ) {
                    unset($pola);
                    $pola = array(
                            array('auction_date_start',date('Y-m-d G:i:s',FunkcjeWlasnePHP::my_strtotime($DataStart)))
                    );
                    $db->update_query('allegro_auctions' , $pola, " auction_id = '".$PrzetwarzanaAukcja->id."'");
                }
                if ( $DataEnd != '' ) {
                    unset($pola);
                    $pola = array(
                            array('auction_date_end',date('Y-m-d G:i:s',FunkcjeWlasnePHP::my_strtotime($DataEnd)))
                    );
                    $db->update_query('allegro_auctions' , $pola, " auction_id = '".$PrzetwarzanaAukcja->id."'");
                }

                $info['products_name'] = $PrzetwarzanaAukcja->name;
                $info['auction_status'] = $PrzetwarzanaAukcja->publication->status;
                $info['auction_date_start'] = $DataStart;
                $info['auction_date_end'] = $DataEnd;
                $info['auction_quantity'] = $PrzetwarzanaAukcja->stock->available;
                $info['products_buy_now_price'] = $PrzetwarzanaAukcja->sellingMode->price->amount;
                $info['auction_watching'] = $PrzetwarzanaAukcja->stats->watchersCount;
                $info['auction_hits'] = $PrzetwarzanaAukcja->stats->visitsCount;
                $info['products_sold'] = $PrzetwarzanaAukcja->stock->sold;

                unset($pola);
            }

        }

        ?>

        <div class="info_content">
        
          <div class="ObramowanieForm" style="margin-top:10px;">
          
            <table>
            
              <tr class="div_naglowek">
                <td colspan="2" style="padding-left:10px;">Aukcja numer: <?php echo $info['auction_id']; ?></td>
              </tr>

              <tr class="PozycjaAllegroForm DodatkowayOdstep">
                <td>Tytuł aukcji:</td>
                <td><?php echo $info['products_name']; ?></td>
              </tr>
              
              <tr class="PozycjaAllegroForm DodatkowayOdstep">
                <td>Początkowa ilość produktów:</td>
                <td><?php echo $info['products_quantity']; ?></td>
              </tr>
              
              <tr class="PozycjaAllegroForm DodatkowayOdstep">
                <td>Status aukcji:</td>
                <td>
                  <?php 
                  if ( $info['auction_status'] == 'ACTIVE' ) {
                    echo '<span class="zielony">TRWA</span>';
                  } elseif ( $info['auction_status'] == 'ENDED' ) {
                    echo '<span class="czerwony">ZAKOŃCZONA</span>';
                  } elseif ( $info['auction_status'] == 'ACTIVATING' ) {
                    echo '<span class="czerwony">OFERTA CZEKA NA WYSTAWIENIE</span>';
                  }
                  ?>
                </td>
              </tr>
              
              <tr class="PozycjaAllegroForm DodatkowayOdstep">
                <td>Data rozpoczęcia (sklep):</td>
                <td><?php echo date('d-m-Y H:i:s',FunkcjeWlasnePHP::my_strtotime($info['products_date_start'])); ?></td>
              </tr>
              
              <tr class="PozycjaAllegroForm DodatkowayOdstep">
                <td>Data zakończenia (sklep):</td>
                <td><?php echo ( FunkcjeWlasnePHP::my_strtotime($info['products_date_end']) > 0 ? date('d-m-Y H:i:s',FunkcjeWlasnePHP::my_strtotime($info['products_date_end'])) : '' ); ?></td>
              </tr>
              
              <tr class="PozycjaAllegroForm DodatkowayOdstep">
                <td>Data rozpoczęcia (allegro):</td>
                <td><?php echo date('d-m-Y H:i:s',FunkcjeWlasnePHP::my_strtotime($info['auction_date_start'])); ?></td>
              </tr>
              
              <tr class="PozycjaAllegroForm DodatkowayOdstep">
                <td>Data zakończenia (allegro):</td>
                <td><?php echo ( FunkcjeWlasnePHP::my_strtotime($info['auction_date_end']) > 0 ? date('d-m-Y H:i:s',FunkcjeWlasnePHP::my_strtotime($info['auction_date_end'])) : 'do wyczerpania' ); ?></td>
              </tr>
              
              <tr class="PozycjaAllegroForm DodatkowayOdstep">
                <td>Wystawiający:</td>
                <td><?php echo $TablicaUzytkownikow[$info['auction_seller']]; ?></td>
              </tr>
              
              <tr class="PozycjaAllegroForm DodatkowayOdstep">
                <td>Pozostała ilość przedmiotów:</td>
                <td><?php echo $info['auction_quantity']; ?></td>
              </tr>
              
              <tr class="PozycjaAllegroForm DodatkowayOdstep">
                <td>Cena Kup teraz:</td>
                <td><?php echo $waluty->FormatujCene($info['products_buy_now_price']); ?></td>
              </tr>
              
              <tr class="PozycjaAllegroForm DodatkowayOdstep">
                <td>Ilość obserwujących:</td>
                <td><?php echo $info['auction_watching']; ?></td>
              </tr>
              
              <tr class="PozycjaAllegroForm DodatkowayOdstep">
                <td>Ilość wyświetleń:</td>
                <td><?php echo $info['auction_hits']; ?></td>
              </tr>
              
              <tr class="PozycjaAllegroForm DodatkowayOdstep">
                <td>Ilość sprzedanych przedmiotów:</td>
                <td><?php echo $info['products_sold']; ?></td>
              </tr>
              
            </table>
            
          </div>
          
        </div>

        <?php
        $zapytaniea = "SELECT DISTINCT a.*, t.post_buy_form_pay_status, t.post_buy_form_it_quantity
                      FROM allegro_auctions_sold a
                      LEFT JOIN allegro_transactions t ON t.auction_id = a.auction_id AND a.buyer_id = t.buyer_id
                      WHERE a.auction_id='".$info['auction_id']."'";
        $sqla = $db->open_query($zapytaniea);

        if ( $db->ile_rekordow($sqla) > 0 ) {
          ?>
          <div class="info_content" id="aukcje_lista">
          
            <div class="ObramowanieForm" style="margin-top:10px;">

              <table>
              
                <tr class="div_naglowek"> 
                  <td>Kupujący</td> 
                  <td>Konto</td> 
                  <td>Ilość produktów</td> 
                  <td>Data zakupu</td> 
                  <td>Cena</td> 
                  <td>Status<br />zakupu</td> 
                  <td>Numer<br />zamówienia</td> 
                  <td>Status<br />odwołania</td> 
                  <td>Data<br />odwołania</td> 
                  <td>Formularz<br />pozakupowy</td> 
                </tr>
                
                <?php while ( $infoa = $sqla->fetch_assoc() ) { ?>
                
                  <tr class="pozycja_off">
                  
                    <td><?php echo $infoa['buyer_name']; ?></td> 
                    <td><?php echo ( $infoa['buyer_status'] == '0' ? 'aktywne' : 'zablokowane'); ?></td> 
                    <td><?php echo round(( isset($infoa['post_buy_form_it_quantity']) ? $infoa['post_buy_form_it_quantity'] : $infoa['auction_quantity'] ),0); ?></td> 
                    <td><?php echo date('d-m-Y H:i:s',$infoa['auction_buy_date']); ?></td> 
                    <td><?php echo $waluty->FormatujCene($infoa['auction_price']); ?></td> 
                    <td>
                      <?php 
                      if ( $infoa['auction_status'] == '1' ) {
                        echo 'oferta zakończona sprzedażą';
                      } elseif ( $infoa['auction_status'] == '-1' ) {
                        echo 'oferta odwołana';
                      } elseif ( $infoa['auction_status'] == '0' ) {
                        echo 'oferta nie zakończona sprzedażą';
                      }
                      ?>
                    </td> 
                    <td><?php echo ( $infoa['orders_id'] != '0' ? $infoa['orders_id'] : '---' ); ?></td> 
                    <td>
                      <?php 
                      if ( $infoa['auction_lost_status'] == '1' ) {
                        echo 'oferta odwołana przez sprzedającego';
                      } elseif ( $infoa['auction_lost_status'] == '2' ) {
                        echo 'oferta odwołana przez administratora serwisu';
                      } elseif ( $infoa['auction_lost_status'] == '0' ) {
                        echo 'oferta nieodwołana';
                      }
                      ?>
                    </td> 
                    <td><?php echo ( $infoa['auction_lost_date'] != '' ? date('d-m-Y H:i:s',$infoa['auction_lost_date']) : '---'); ?></td> 
                    <td>
                      <?php
                      $stan_tranzakcji = '<em class="TipChmurka"><b>Kupujący nie wypełnił formularza pozakupowego</b><img src="obrazki/aktywny_off.png" alt="Kupujący nie wypełnił formularza pozakupowego" /></em>';
                      if ( $infoa['auction_postbuy_forms'] == '1' ) {
                        $stan_tranzakcji = '<em class="TipChmurka"><b>Kupujący wypełnił formularz pozakupowy</b><img src="obrazki/aktywny_on.png" alt="Kupujący wypełnił formularz pozakupowy" /></em>';
                        if ( $infoa['post_buy_form_pay_status'] == 'Anulowana' ) {
                          $stan_tranzakcji = '<em class="TipChmurka"><b>Formularz pozakupowy został anulowany</b><img src="obrazki/uwaga.png" alt="Formularz pozakupowy został anulowany" /></em>';
                        }
                      }

                      echo $stan_tranzakcji;
                      ?>
                    </td> 
                    
                  </tr>
                  <?php
                }
                ?>
              </table>
              
            </div>
            
          </div>

        <?php
        }
        
        $db->close_query($sqla);
        unset($zapytaniea, $infoa);

      } else {

        echo '<div class="pozycja_edytowana">Brak danych do wyświetlenia</div>';

      }
      $db->close_query($sql);
      unset($zapytanie, $info);

      ?>
      
      <div class="przyciski_dolne">
        <button type="button" class="przyciskNon" onclick="cofnij('allegro_aukcje','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','allegro');">Powrót</button> 
      </div> 
      
    </div>
    
    <?php
    include('stopka.inc.php');    
    
} ?>
