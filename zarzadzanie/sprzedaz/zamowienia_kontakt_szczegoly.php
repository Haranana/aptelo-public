<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');
// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

        //
        $pola = array( array('orders_fast_status',(int)$_POST['status']) );
                
        $db->update_query('orders_fast' , $pola, " orders_fast_id  = '".(int)$_POST["id"]."'");	
        unset($pola);

        //
        $pola = array(
                array('orders_fast_id',(int)$_POST["id"]),
                array('orders_fast_status_id',(int)$_POST['status']),
                array('date_added','now()'),
                array('comments',$filtr->process($_POST['komentarz'])));

        $db->insert_query('orders_fast_status_history' , $pola);
        unset($pola);

        //
        if ( isset($_POST['powrot']) && (int)$_POST['powrot'] == 1 ) {
            //
            Funkcje::PrzekierowanieURL('zamowienia_kontakt.php?id_poz='.(int)$_POST["id"]);
            //
          } else {
            //
            Funkcje::PrzekierowanieURL('zamowienia_kontakt_szczegoly.php?id_poz='.(int)$_POST["id"].'&zakladka='.(int)$_POST["zakladka"]);
            //
        }

    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>

    <div id="naglowek_cont">Szczegóły zamówienia do kontaktu</div>
    <div id="cont">

    <?php
    if ( !isset($_GET['id_poz']) ) {
         $_GET['id_poz'] = 0;
    }     
    
    $zapytanie = "SELECT * FROM orders_fast o
                    LEFT JOIN orders_fast_status_history ofs ON o.orders_fast_id = ofs.orders_fast_id
                        WHERE o.orders_fast_id = '" . (int)$_GET['id_poz'] . "'";
                    
    $sql = $db->open_query($zapytanie);

    if ((int)$db->ile_rekordow($sql) > 0) {
    
        $info = $sql->fetch_assoc();
        ?>

          <div class="cmxform"> 
          
            <div class="poleForm">
            
                <div class="naglowek">Edycja pozycji</div>

                <div id="ZakladkiEdycji">
                
                    <div id="LeweZakladki">
                    
                        <a href="javascript:gold_tabs_horiz('0','0')" class="a_href_info_zakl" id="zakl_link_0">Podstawowe dane</a>  
                        <?php
                        // oblicza ile bylo zmian statusow
                        $zapytanie_statusy = "SELECT orders_fast_status_history_id, orders_fast_status_id FROM orders_fast_status_history WHERE orders_fast_id = '" . $info['orders_fast_id'] . "'";
                        $sql_statusy = $db->open_query($zapytanie_statusy);
                        $ile_statusow = (int)$db->ile_rekordow($sql_statusy);
                        ?>
                        <a href="javascript:gold_tabs_horiz('1','1')" class="a_href_info_zakl" id="zakl_link_1">Historia zamówienia [<?php echo $ile_statusow; ?>]</a>
                        <?php
                        unset($ile_statusow, $zapytanie_statusy, $sql_statusy);
                        ?>
                        
                    </div>
                    
                    <?php $licznik_zakladek = 0; ?>

                    <div id="PrawaStrona" style="padding:20px">
                    
                        <?php // ********************************************* INFORMACJE OGOLNE *************************************************** ?>
                    
                        <div id="zakl_id_0" style="display:none;">
                        
                            <div class="ObramowanieTabeli">
                            
                              <table class="listing_tbl">
                              
                                <tr class="div_naglowek">
                                  <td colspan="2">
                                  <div class="lf">Dane zamówienia do kontaktu</div>
                                  <div class="LinkEdycjiZamowienia"><a href="sprzedaz/zamowienia_kontakt_edytuj.php?id_poz=<?php echo (int)$_GET['id_poz']; ?>&amp;zakladka=0">edytuj</a></div>
                                  </td>
                                </tr>         
                                
                                <tr class="pozycja_off">
                                  <td style="width:25%">Nr zamówienia:</td>
                                  <td class="PozycjaBold" style="width:65%"><?php echo $info['orders_fast_id']; ?></td>
                                </tr>        
                                
                                <tr class="pozycja_off">
                                  <td>Data zamówienia:</td>
                                  <td class="PozycjaBold"><?php echo date('d-m-Y H:i:s', FunkcjeWlasnePHP::my_strtotime($info['date_purchased'])); ?></td>
                                </tr>
                                
                                <tr class="pozycja_off">
                                  <td>Data ostatniej zmiany statusu zamówienia:</td>
                                  <?php
                                  $zapytanieStatus = "select max(date_added) as data from orders_fast_status_history where orders_fast_id = '" . $info['orders_fast_id'] . "'";
                                  $sqlp = $db->open_query($zapytanieStatus);
                                  $infoData = $sqlp->fetch_assoc();                                  
                                  ?>
                                  <td class="PozycjaBold"><?php echo date('d-m-Y H:i:s', FunkcjeWlasnePHP::my_strtotime($infoData['data'])); ?></td>
                                  <?php 
                                  $db->close_query($sqlp);
                                  unset($zapytanieStatus, $infoData);                                  
                                  ?>
                                </tr>
                                
                                <tr class="pozycja_off">
                                  <td>Klient:</td>
                                  <td class="PozycjaBold">
                                  <?php echo $info['customers_name']; ?>
                                  </td>
                                </tr>

                                <tr class="pozycja_off">
                                  <td>Adres e-mail:</td>
                                  <td class="PozycjaBold"><?php echo $info['customers_email_address']; ?></td>
                                </tr>
                                
                                <tr class="pozycja_off">
                                  <td>Nr telefonu:</td>
                                  <td class="PozycjaBold"><?php echo $info['customers_telephone']; ?></td>
                                </tr> 

                                <tr class="pozycja_off">
                                  <td>Uwagi klienta przesłane w zamówieniu:</td>
                                  <td class="PozycjaBold">
                                  
                                  <?php
                                  // szuka pierwszego statusu zamowienia zeby zaktualizowac komentarz
                                  $zapytanie_komentarz = "select comments from orders_fast_status_history where orders_fast_id = '" . $info['orders_fast_id'] . "' order by date_added asc limit 1";
                                  $sql_komentarz = $db->open_query($zapytanie_komentarz);        
                                  $info_komentarz = $sql_komentarz->fetch_assoc();
                                  //
                                  echo $info_komentarz['comments'];   
                                  //
                                  $db->close_query($sql_komentarz);
                                  unset($info_komentarz, $zapytanie_komentarz);                        
                                  ?>                                  
                                  
                                  </td>
                                </tr> 

                                <tr class="pozycja_off">
                                  <td>Produkt:</td>
                                  <td class="PozycjaBold">
                                  <?php
                                  $zapytanie_produkt = "SELECT p.products_id, p.products_image, pd.products_name
                                                          FROM products p, products_description pd
                                                         WHERE p.products_id = '" . (int)$info['orders_fast_products_id'] . "' AND p.products_id = pd.products_id AND pd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'";
                                                         
                                  $sql_produkt = $db->open_query($zapytanie_produkt);
                                  $produkt = $sql_produkt->fetch_assoc();
                                  
                                  // czy produkt ma cechy
                                  $JakieCechy = '';
                                  if ( $info['orders_fast_products_stock_attributes'] != '' ) {
                                    
                                      $CechaPrd = Produkty::CechyProduktuPoId( $info['orders_fast_products_id'] . 'x' . str_replace(',', 'x', (string)$info['orders_fast_products_stock_attributes']) );                                  
                                      if (count($CechaPrd) > 0) {
                                          //
                                          for ($a = 0, $c = count($CechaPrd); $a < $c; $a++) {
                                              $JakieCechy .= '<div class="MaleNrKatSzybkieZamowienie">' . $CechaPrd[$a]['nazwa_cechy'] . ': <b>' . $CechaPrd[$a]['wartosc_cechy'] . '</b></div>';
                                          }
                                          //
                                      }                  
 
                                  }
                                  
                                  echo ((isset($produkt['products_name'])) ? '<a class="LinkProduktu" target="_blank" href="produkty/produkty_edytuj.php?id_poz=' . $produkt['products_id'] . '">' . $produkt['products_name'] . '</a>' . $JakieCechy : 'Brak nazwy produktu ...');
                                  
                                  echo (($produkt['products_image'] != '') ? '<div class="ZdjecieSzybkieZamowienie">' . Funkcje::pokazObrazek($produkt['products_image'], $produkt['products_name'], '100', '100') . '</div>' : '');
                                  
                                  $db->close_query($sql_produkt);
                                  unset($zapytanie_produkt, $JakieCechy, $CechaPrd);                                    
                                  ?>
                                  </td>
                                </tr>                                
                                
                                <tr class="pozycja_off">
                                  <td>Cena produktu:</td>
                                  <td class="PozycjaBold">
                                  <?php echo $waluty->FormatujCene($info['products_final_price_tax'], false, $info['orders_fast_currencies_id']); ?>
                                  </td>
                                </tr>                                                       
                                
                                <tr class="pozycja_off">
                                  <td>Wydruk zamówienia:</td>
                                  <td class="PozycjaBold"><a class="TipChmurka" href="sprzedaz/zamowienia_kontakt_pdf.php?id_poz=<?php echo (int)$_GET['id_poz']; ?>"><b>Wydruk zamówienia</b><img src="obrazki/pdf_2.png" alt="Wydruk zamówienia" /></a></td>
                                </tr>  
                                
                              </table>
                              
                            </div>
                            
                        </div>
                        
                        
                        <?php // ********************************************* HISTORIA *************************************************** ?>
                        
                        <div id="zakl_id_1" style="display:none;">

                          <?php
                          $zapytanie_statusy = "SELECT * FROM orders_fast_status_history WHERE orders_fast_id = '" . (int)$_GET['id_poz'] . "' ORDER BY date_added";
                          $sql_statusy = $db->open_query($zapytanie_statusy);

                          if ((int)$db->ile_rekordow($sql_statusy) > 0) {
                            ?>
                            
                            <div class="ObramowanieTabeli" style="padding:3px 4px 3px 2px">
                            
                              <table class="listing_tbl" id="InfoTabelaHistoria">
                              
                                <tr class="div_naglowek">
                                  <td>Data dodania</td>
                                  <td>Status</td>
                                  <td style="width:50%">Komentarze</td>
                                </tr>
                                
                                <?php while ($info_statusy = $sql_statusy->fetch_assoc()) { ?>
                                
                                <tr class="pozycja_off">
                                  <td><?php echo date('d-m-Y H:i', FunkcjeWlasnePHP::my_strtotime($info_statusy['date_added'])); ?></td>
                                  <td><?php echo Sprzedaz::pokazNazweStatusuZamowienia($info_statusy['orders_fast_status_id'], $_SESSION['domyslny_jezyk']['id']); ?></td>
                                  <td style="text-align:left"><?php echo $info_statusy['comments']; ?></td>
                                </tr>
                                <?php } ?>
                                
                              </table>
                              
                            </div>
                            
                            <?php } ?>

                            <div class="pozycja_edytowana" style="padding-top:20px;">
                            
                                <div class="info_content">

                                  <form action="sprzedaz/zamowienia_kontakt_szczegoly.php" method="post" id="zamowieniaUwagiForm" class="cmxform">

                                    <div>
                                        <input type="hidden" name="akcja" value="zapisz" />
                                        <input type="hidden" name="id" value="<?php echo (int)$_GET['id_poz']; ?>" />
                                        <input type="hidden" name="zakladka" value="1" />
                                    </div>

                                    <script>
                                    function UkryjZapiszKomentarz(id) {
                                        if (parseInt(id) > 0) {
                                            $('#przyciski').slideDown('fast');     
                                        } else {
                                            $('#przyciski').slideUp('fast');
                                            $("#komentarz_tresc").val('');
                                        }                     
                                    }   
                                    </script>                                  

                                    <p>
                                      <label for="status">Nowy status zamówienia:</label>
                                      <?php
                                      $tablica = Sprzedaz::ListaStatusowZamowien(true, '--- wybierz z listy ---');
                                      echo Funkcje::RozwijaneMenu('status', $tablica,'','id="status" onchange="UkryjZapiszKomentarz(this.value)" style="width:350px;"'); ?>
                                    </p>

                                    <p>
                                      <label>Komentarz:</label>
                                      <textarea cols="100" rows="10" name="komentarz" class="wysiwyg" id="komentarz_tresc"></textarea>
                                    </p>

                                    <div class="przyciski_dolne" id="przyciski" style="display:none">
                                      <input type="submit" class="przyciskNon" value="Zapisz dane" />
                                      <input type="hidden" name="powrot" id="powrot" value="0" />
                                      <input type="submit" class="przyciskNon" value="Zapisz dane i wróć do listy zamówień" onclick="$('#powrot').val(1)" />
                                    </div>

                                  </form>

                                </div>
                             
                            </div>
                        </div>

                        <?php
                        $zakladka = '0';
                        if (isset($_GET['zakladka'])) $zakladka = (int)$_GET['zakladka'];
                        ?>
                        
                        <script>
                        gold_tabs_horiz(<?php echo $zakladka; ?>,'0');
                        </script>                         
                    
                    </div>
                    
                </div>

            </div>
            
          </div>
          
          <div class="przyciski_dolne">
                <button type="button" class="przyciskNon" onclick="cofnij('zamowienia_kontakt','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>', 'sprzedaz');">Powrót</button>    
          </div>           

          <?php

          $db->close_query($sql);

      } else {

          echo '<div class="poleForm">
                    <div class="naglowek">Edycja danych</div>
                    <div class="pozycja_edytowana">Brak danych do wyświetlenia</div>
                </div>';
            
      }
      ?>

    </div>
    
    <?php
    include('stopka.inc.php');    
    
} ?>
