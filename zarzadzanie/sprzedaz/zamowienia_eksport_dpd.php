<?php
chdir('../'); 

// INSERT INTO `admin_acces` (`menu_id`, `menu_nazwa`, `plik`, `nadrzedna`, `sortowanie`, `status`, `grupa_id_upraw_1`, `grupa_id_upraw_2`) VALUES ('', 'Eksport CSV dla DPD', 'sprzedaz/zamowienia_eksport_dpd.php', 90, 6, 1, 1, 1);

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'wyszukaj' ) {

      $warunek_wysylek = "";
      $warunek_platnosci = "";
      $warunek_daty = "";
      $warunek_status = "";

      //warunki wyboru wysylek
      if ( isset($_POST['wysylka']) && count($_POST['wysylka']) > 0 ) {
        $warunek_wysylek .= ' AND (';
        for ( $i =0, $c = count($_POST['wysylka']); $i < $c; $i++ ) {
          if ( $i == '0' ) {
            $warunek_wysylek .= "o.shipping_module IN ('".str_replace('|', '\',\'', (string)$_POST['wysylka'][$i])."') ";
          } else {
            $warunek_wysylek .= " OR o.shipping_module IN ('".str_replace('|', '\',\'', (string)$_POST['wysylka'][$i])."') ";
          }
        }
        $warunek_wysylek .= ')';
      }

      //warunki wyboru platnosci
      if ( isset($_POST['platnosc']) && count($_POST['platnosc']) > 0 ) {
        $warunek_platnosci .= ' AND (';
        for ( $i = 0, $c = count($_POST['platnosc']); $i < $c; $i++ ) {
          if ( $i == '0' ) {
            $warunek_platnosci .= "o.payment_method IN ('".str_replace('|', '\',\'', (string)$_POST['platnosc'][$i])."') ";
          } else {
            $warunek_platnosci .= " OR o.payment_method IN ('".str_replace('|', '\',\'', (string)$_POST['platnosc'][$i])."') ";
          }
        }
        $warunek_platnosci .= ')';
      }

      //warunki wyboru statusu zamowiena
      if ( isset($_POST['status']) && $_POST['status'] != '' && $_POST['status'] != '0' ) {
        $warunek_status = " AND o.orders_status = ". $_POST['status'];
      }

      //warunki wyboru dat
      //warunki wyboru dat
      if ( $_POST['data_statusu_od'] != '' && $_POST['data_statusu_do'] == '' ) {
        $startdate       = date("Y-m-d H:i:s",FunkcjeWlasnePHP::my_strtotime($_POST['data_statusu_od'] . ' 00:00:00'));
        $enddate         = date("Y-m-d H:i:s");
      } elseif ( $_POST['data_statusu_od'] == '' && $_POST['data_statusu_do'] != '' ) {
        $startdate       = date("Y-m-d H:i:s",FunkcjeWlasnePHP::my_strtotime('1970-01-01 00:00:00'));
        $enddate         = date("Y-m-d H:i:s",FunkcjeWlasnePHP::my_strtotime($_POST['data_statusu_do'] . ' 00:00:00'));
      } elseif ( $_POST['data_statusu_od'] != '' && $_POST['data_statusu_do'] != '' ) {
        $startdate       = date("Y-m-d H:i:s",FunkcjeWlasnePHP::my_strtotime($_POST['data_statusu_od'] . ' 00:00:00'));
        $enddate         = date("Y-m-d H:i:s",FunkcjeWlasnePHP::my_strtotime($_POST['data_statusu_do'] . ' 23:59:59'));
      } else {
        $startdate       = date("Y-m-d H:i:s",FunkcjeWlasnePHP::my_strtotime('1970-01-01 00:00:00'));
        $enddate         = date("Y-m-d H:i:s");
      }
      $warunek_daty    = " o.last_modified between '" . $startdate . "' AND '" . $enddate . "' ";

      $zapytanie = "SELECT o.orders_id
        FROM orders o
        LEFT JOIN orders_status_history h ON h.orders_id = o.orders_id AND h.date_added = 
       ( SELECT MIN(date_added)
           FROM orders_status_history
           WHERE orders_id = o.orders_id )
        LEFT JOIN orders_total ot ON ot.orders_id = o.orders_id AND ot.class = 'ot_total'
        WHERE " . $warunek_daty . " " .
        $warunek_status . " " . 
        $warunek_wysylek . " " .
        $warunek_platnosci . " 
        GROUP BY o.orders_id";

      $sql = $db->open_query($zapytanie);
      $ile_pozycji = (int)$db->ile_rekordow($sql);

    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Eksport zamówień do pliku CSV do DPD</div>
    <div id="cont">
          
    <script type="text/javascript" src="javascript/jquery.multi-select.js"></script>
    <script type="text/javascript" src="javascript/jquery.application.js"></script>

    <script type="text/javascript" src="javascript/dpd.js"></script>

    <script>
    $(document).ready(function() {
      $('input.datepicker').Zebra_DatePicker({
        format: 'd-m-Y',
        inside: false,
        readonly_element: false
      });                
    });
    </script>        

    <form action="sprzedaz/zamowienia_eksport_dpd.php" method="post" id="dpdForm" class="cmxform">          

    <div class="poleForm">
    <div class="naglowek">Wybór zamówień</div>
        
    <div class="pozycja_edytowana">
        
        <div class="info_content">
    
            <input type="hidden" name="akcja" value="wyszukaj" />
              
            <div class="TablicaEdycja">
            
                <div class="CzescWyboru">
        
                    <div class="ObramowanieTabeli">
                    
                        <table class="listing_tbl">
                        
                          <tr class="div_naglowek">
                            <td>Wybierz wysyłki</td>
                          </tr>

                          <tr>
                            <td style="padding:10px">
                              <?php
                              if ( isset($_POST['wysylka']) && count($_POST['wysylka']) > 0 ) {
                                $tablica_wysylek = $_POST['wysylka'];
                              } else {
                                $tablica_wysylek = array();
                              }

                              $wszystkie_wysylki_tmp = Array();
                              $wszystkie_wysylki_tmp = Moduly::TablicaWysylekNazwy(false, true);

                              echo '<select name="wysylka[]" multiple="multiple" id="multipleHeadersEmptyWysylka">';
                              foreach ( $wszystkie_wysylki_tmp as $value ) {
                                $wybrany = '';
                                if ( in_array((string)$value['id'], $tablica_wysylek ) ) {
                                  $wybrany = 'selected="selected"';
                                }
                                echo '<option value="'.$value['id'].'" '.$wybrany.'>'.$value['text'].'</option>';
                              }
                              echo '</select>';
                              ?>
                            </td>
                          </tr>
                          
                        </table>
                        
                    </div>
                    
                </div>
                
                <div class="CzescWyboru">
                
                    <div class="ObramowanieTabeli">
                    
                      <table class="listing_tbl">
                      
                        <tr class="div_naglowek">
                          <td>Wybierz płatności</td>
                        </tr>

                        <tr>
                          <td style="padding:10px">
                            <?php
                            if ( isset($_POST['platnosc']) && count($_POST['platnosc']) > 0 ) {
                              $tablica_platnosci = $_POST['platnosc'];
                            } else {
                              $tablica_platnosci = array();
                            }

                            $wszystkie_platnosci_tmp = Array();
                            $wszystkie_platnosci_tmp = Moduly::TablicaPlatnosciNazwy(false, true);

                            echo '<select name="platnosc[]" multiple="multiple" id="multipleHeadersEmptyPlatnosc">';
                            foreach ( $wszystkie_platnosci_tmp as $value ) {
                              $wybrany = '';
                              if ( in_array((string)$value['id'], $tablica_platnosci ) ) {
                                $wybrany = 'selected="selected"';
                              }
                              echo '<option value="'.$value['id'].'" '.$wybrany.'>'.$value['text'].'</option>';
                            }
                            echo '</select>';
                            ?>
                          
                          </td>
                        </tr>
                        
                      </table>
                      
                    </div>
                    
                </div>
                
            </div>

        </div>
             
        <div class="info_content" style="margin-top:15px">
        
          <p>
            <label>Zamówienia ze statusem:</label>
            <?php
            $default = '';
            if ( isset($_POST['status']) ) $default = $_POST['status'];
            $tablica = Sprzedaz::ListaStatusowZamowien(true, '--- dowolny ---');
            echo Funkcje::RozwijaneMenu('status', $tablica); ?>
          </p>

          <p>
            <label>Data zamówienia:</label>
            od <input type="text" id="data_statusu_od" name="data_statusu_od" value="<?php echo ( isset($_POST['data_statusu_od']) ? $_POST['data_statusu_od'] : '' ); ?>" size="10" class="datepicker" />&nbsp;do&nbsp;
            <input type="text" id="data_statusu_do" name="data_statusu_do" value="<?php echo ( isset($_POST['data_statusu_do']) ? $_POST['data_statusu_do'] : '' ); ?>" size="10" class="datepicker" />
          </p>

        </div>

        </div>

        <div class="przyciski_dolne">
          <input type="submit" class="przyciskNon" value="Wybierz zamówienia" />
        </div>

    </div>                      
    </form>

    </div>

    <?php
    if (isset($_POST['akcja']) && $_POST['akcja'] == 'wyszukaj' ) {
      ?>
      <div id="contWynik" style="padding-top:10px;">
        <?php
        if ( $ile_pozycji > 0 ) {
          ?>
          <div class="ObramowanieTabeli">
            <form action="sprzedaz/zamowienia_eksport_dpd_csv.php" method="post" id="wynikForm" class="cmxform">          
              <div>
                <input type="hidden" name="akcja" value="wydrukuj" />

                <table class="listing_tbl">
                
                  <tr class="div_naglowek NaglowekCentruj">
                    <td>Usuń</td>
                    <td>Zam.</td>
                    <td>Wartość</td>
                    <td>Adresat</td>
                    <td>Firma</td>
                    <td>Ulica</td>
                    <td>Kod</td>
                    <td>Miejscowość</td>
                    <td>Pobranie</td>
                    <td>Wart.</td>
                  </tr>

                  <?php
                  $i = 1;
                  while ($info = $sql->fetch_assoc()) {

                    $zamowienie = new Zamowienie($info['orders_id']);

                    $zawartosc = '';
                    foreach ( $zamowienie->produkty as $produkt ) {
                        $zawartosc .= $produkt['nazwa'] .',';
                    }
                    $zawartosc = substr((string)$zawartosc,0,-1);

                    $komentarzArray = array_shift($zamowienie->statusy);
                    
                    $komentarz = $komentarzArray['komentarz'];

                    $pobranie = Funkcje::strposa($zamowienie->info['metoda_platnosci'], array('pobranie', 'pobraniowa', 'odbiorze'));

                    echo '<tr class="item-row FakturaProduktKsiazka">';
                    echo '<td class="FakturaProdukt"><div class="UsunKontener"><a class="UsunPozycjeListy TipChmurka" href="javascript:void(0)"><b>Skasuj</b><img style="cursor:pointer" src="obrazki/kasuj.png" alt="Skasuj" /></a></div></td>';
                    echo '<td class="FakturaProdukt">'.$info['orders_id'].'</td>';
                    echo '<td class="FakturaProdukt"><input type="text" class="kropka" name="wiersz['.$i.'][wartosc]" size="6" value="'.$zamowienie->info['wartosc_zamowienia_val'].'" style="text-align:right;" /></td>';
                    echo '<td class="FakturaProdukt"><textarea cols="20" rows="2" name="wiersz['.$i.'][klient]">'.$zamowienie->dostawa['nazwa'].'</textarea></td>';
                    echo '<td class="FakturaProdukt"><textarea cols="20" rows="2" name="wiersz['.$i.'][firma]">'.$zamowienie->dostawa['firma'].'</textarea></td>';
                    echo '<td class="FakturaProdukt"><input type="text" name="wiersz['.$i.'][ulica]" size="15" value="'.$zamowienie->dostawa['ulica'].'" /></td>';
                    echo '<td class="FakturaProdukt"><input type="text" name="wiersz['.$i.'][kod]" size="6" value="'.$zamowienie->dostawa['kod_pocztowy'].'" /></td>';
                    echo '<td class="FakturaProdukt"><input type="text" name="wiersz['.$i.'][miasto]" size="15" value="'.$zamowienie->dostawa['miasto'].'" /></td>';
                    echo '<td class="FakturaProdukt"><input id="pobranie_' . $i . '" type="checkbox" '.($pobranie ? 'checked="checked"' : '' ).' name="wiersz['.$i.'][pobranie]" value="1" /><label class="OpisForPustyLabel" for="pobranie_' . $i . '"></label></td>';
                    echo '<td class="FakturaProdukt">
                        <input id="wartosciowa_' . $i . '" type="checkbox" name="wiersz['.$i.'][wartosciowa]" value="1" /><label class="OpisForPustyLabel" for="wartosciowa_' . $i . '"></label>
                        <input type="hidden" name="wiersz['.$i.'][komentarz]" value="'.$komentarz.'" />
                        <input type="hidden" name="wiersz['.$i.'][zawartosc]" value="'.$zawartosc.'" />
                        <input type="hidden" name="wiersz['.$i.'][zamowienie_id]" value="'.$info['orders_id'].'" />
                        <input type="hidden" name="wiersz['.$i.'][telefon]" value="'.$zamowienie->klient['telefon'].'" />
                        <input type="hidden" name="wiersz['.$i.'][email]" value="'.$zamowienie->klient['adres_email'].'" />
                        <input type="hidden" name="wiersz['.$i.'][waluta]" value="'.$zamowienie->info['waluta'].'" />
                        <input type="hidden" name="wiersz['.$i.'][waga]" value="'.$zamowienie->waga_produktow.'" />
                    </td>';
                    echo '</tr>';
                    unset($zamowienie, $komentarz, $komentarzArray, $zawartosc);

                    $i++;

                  }
                  ?>

                </table>
              </div>

              <div class="przyciski_dolne">
                 <input type="submit" class="przyciskNon" value="Wygeneruj plik CSV" />
              </div>

                  <div><input type="hidden" name="licznik" id="licznik" value="<?php echo $i; ?>" /></div>
              
              </form>
            
          </div>

          <?php
          
        } else {
          
          echo '<div class="poleForm"><div class="pozycja_edytowana"><span class="maleInfo">Brak wyników do wyświetlenia</span></div></div>';
          
        }
        ?>
        
      </div>
      
      <?php
    }
    ?>

    <?php
    include('stopka.inc.php');

}