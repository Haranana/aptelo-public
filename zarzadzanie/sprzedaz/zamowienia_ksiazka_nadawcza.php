<?php
chdir('../'); 

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
      $warunek_daty    = " h.date_added between '" . $startdate . "' AND '" . $enddate . "' ";

      $zapytanie = "SELECT DISTINCT 
        o.orders_id, o.shipping_module, o.orders_status, o.delivery_name, o.delivery_company, o. delivery_street_address, o.delivery_postcode, o.delivery_city, o.delivery_state, o.delivery_country, o.payment_method, o.currency,
        h.comments, h.date_added, 
        ot.class, ot.title, ot.value 
        FROM orders o
        LEFT JOIN orders_status_history h ON h.orders_id = o.orders_id AND h.orders_status_id = o.orders_status
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
    
    <div id="naglowek_cont">Generowanie książki nadawczej</div>
    <div id="cont">
          
    <script type="text/javascript" src="javascript/jquery.multi-select.js"></script>
    <script type="text/javascript" src="javascript/jquery.application.js"></script>
    <script type="text/javascript" src="javascript/ksiazka.js"></script>

    <script>
    $(document).ready(function() {
      $('input.datepicker').Zebra_DatePicker({
        format: 'd-m-Y',
        inside: false,
        readonly_element: false
      });                
    });
    </script>        

    <form action="sprzedaz/zamowienia_ksiazka_nadawcza.php" method="post" id="ksiazkaForm" class="cmxform">          

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
                            $wszystkie_wysylki_tmp = Moduly::TablicaWysylekNazwy();

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
                            $wszystkie_platnosci_tmp = Moduly::TablicaPlatnosciNazwy();

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
            <label>Status zmieniony:</label>
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
      <div id="contWynik" style="padding-top:10px">
        <?php
        if ( $ile_pozycji > 0 ) {
          ?>
          <div class="ObramowanieTabeli">
          
              <form action="sprzedaz/zamowienia_ksiazka_nadawcza_pdf.php" method="post" id="wynikForm" class="cmxform">          

                  <input type="hidden" name="akcja" value="wydrukuj" />

                  <table class="listing_tbl">
                  
                    <tr class="div_naglowek NaglowekCentruj">
                      <td>Usuń</td>
                      <td>Zam.</td>
                      <td>Wysyłka</td>
                      <td>Adresat</td>
                      <td>Miejsce doręczenia</td>
                      <td>Wartość</td>
                      <td>Rodzaj</td>
                      <td>Pobr.</td>
                      <td>Wart.</td>
                    </tr>

                    <?php
                    $i = 1;
                    while ($info = $sql->fetch_assoc()) {

                      $adresat = $info['delivery_name'];
                      if ( $info['delivery_company'] != '' ) {
                        $adresat .= "\n" . $info['delivery_company'];
                      }
                      $adres_dostawy = $info['delivery_street_address'];
                      $adres_dostawy .= "\n" . $info['delivery_postcode'] . ' ' . $info['delivery_city'];
                      $adres_dostawy .= "\n" . $info['delivery_country'];

                      $wartosc_zamowienia = $info['value'];

                      $ekonom = false;
                      $prior  = false;
                      if ( stripos((string)$info['shipping_module'], 'ekonom') !== false ) {
                        $ekonom = true;
                      }
                      if ( stripos((string)$info['shipping_module'], 'priory') !== false ) {
                        $prior = true;
                      }
                      echo '<tr class="item-row FakturaProduktKsiazka">';
                      echo '<td class="FakturaProdukt"><div class="UsunKontener"><a class="UsunPozycjeListy TipChmurka" href="javascript:void(0)"><b>Skasuj</b><img style="cursor:pointer" src="obrazki/kasuj.png" alt="Skasuj" /></a></div></td>';
                      echo '<td class="FakturaProdukt">'.$info['orders_id'].'</td>';
                      echo '<td class="FakturaProdukt">'.$info['shipping_module'].'</td>';
                      echo '<td class="FakturaProdukt"><textarea cols="35" rows="2" name="wiersz['.$i.'][adresat]">'.$adresat.'</textarea></td>';
                      echo '<td class="FakturaProdukt"><textarea cols="35" rows="2" name="wiersz['.$i.'][adres_dostawy]">'.$adres_dostawy . '</textarea></td>';
                      echo '<td class="FakturaProdukt"><input type="text" class="kropka" name="wiersz['.$i.'][wartosc]" size="10" value="'.$waluty->FormatujCeneBezSymbolu($wartosc_zamowienia, true, '', '', '2', $waluty->ZwrocIdWalutyKod($info['currency'])).'" style="text-align:right;" /></td>';
                      echo '<td class="FakturaProdukt"><input type="checkbox" id="ekon_' . $i . '" name="wiersz['.$i.'][rodzaj_wysylki]" value="0" '.( $ekonom ? 'checked="checked"' : '' ).' /> <label class="OpisFor" for="ekon_' . $i . '">EKON</label> <input type="checkbox" id="prior_' . $i . '" name="wiersz['.$i.'][rodzaj_wysylki]" value="1" '.( $prior ? 'checked="checked"' : '' ).' /> <label class="OpisFor" for="prior_' . $i . '">PRIOR</label></td>';
                      echo '<td class="FakturaProdukt"><input type="checkbox" id="pobranie_' . $i . '" name="wiersz['.$i.'][pobranie]" value="1" /> <label class="OpisForPustyLabel" for="pobranie_' . $i . '"></label></td>';
                      echo '<td class="FakturaProdukt"><input type="checkbox" id="wartosciowa_' . $i . '" name="wiersz['.$i.'][wartosciowa]" value="1" /> <label class="OpisForPustyLabel" for="wartosciowa_' . $i . '"></label></td>';
                      echo '</tr>';

                      $i++;
                    }
                    ?>
                    
                    <tr id="hiderow">
                      <td colspan="9" style="padding:20px;"><a id="addrow" href="javascript::void(0)" class="dodaj">dodaj pozycję</a></td>
                    </tr>

                  </table>

                  <div class="przyciski_dolne">
                     <input type="submit" class="przyciskNon" value="Wydrukuj książkę nadawczą" />
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