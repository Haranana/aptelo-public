<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

      // aktualizacja rekordu w tablicy invoices
      $pola = array(
              array('invoices_nr',$filtr->process($_POST['faktura_numer'])),
              array('invoices_date_sell',date('Y-m-d', FunkcjeWlasnePHP::my_strtotime($filtr->process($_POST['data_sprzedazy'])))),
              array('invoices_date_generated',date('Y-m-d', FunkcjeWlasnePHP::my_strtotime($filtr->process($_POST['data_wystawienia'])))),
              array('invoices_date_modified','now()'),
              array('invoices_billing_name',(isset($_POST['klient_nazwa']) ? $filtr->process($_POST['klient_nazwa']) : '')),
              array('invoices_billing_company_name',(isset($_POST['klient_firma']) ? $filtr->process($_POST['klient_firma']) : '')),
              array('invoices_billing_nip',$filtr->process($_POST['klient_nip'])),
              array('invoices_billing_pesel',$filtr->process($_POST['klient_pesel'])),
              array('invoices_billing_street_address',$filtr->process($_POST['klient_ulica'])),
              array('invoices_billing_city',$filtr->process($_POST['klient_miasto'])),
              array('invoices_billing_postcode',$filtr->process($_POST['klient_kod_pocztowy'])),
              array('invoices_billing_country',$filtr->process($_POST['klient_panstwo'])),
              array('invoices_payment_type',$filtr->process($_POST['platnosc'])),
              array('invoices_payment_status', ( isset($_POST['rozliczona']) && $_POST['rozliczona'] == '1' ? $_POST['rozliczona'] : '0') ),
              array('invoices_date_payment',( isset($_POST['rozliczona']) && $_POST['rozliczona'] == '1' ? date('Y-m-d', FunkcjeWlasnePHP::my_strtotime($filtr->process($_POST['data_wystawienia']))) : date('Y-m-d', FunkcjeWlasnePHP::my_strtotime($filtr->process($_POST['data_platnosci']))) )),
              array('invoices_comments',$filtr->process($_POST['komentarz'])),
              array('split_payment',(int)$_POST['split_payment']),
              array('invoices_language_id',(int)$_POST["jezyk"]));

      $db->update_query('invoices' , $pola, " invoices_id = '".(int)$_POST['id_faktury']."'");
      unset($pola);
      
      if ( INTEGRACJA_FAKTUROWNIA_WLACZONY == 'tak' ) {
      
          if ( isset($_POST['fakturownia']) && $_POST['fakturownia'] == '1' ) {
               //
               $fakturownia = new Fakturownia((int)$_POST["zamowienie_id"]);
               //
               if ( $_POST['rozliczona'] == '1' ) {
                    //
                    $fakturownia->ZmienStatusFaktury('oplacona');
                    //
               }
               if ( $_POST['rozliczona'] == '0' ) {
                    //
                    $fakturownia->ZmienStatusFaktury('wystawiona');
                    //
               }           
               //
               unset($fakturownia);
               //
          }
          
      }

      Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz='.(int)$_POST["zamowienie_id"].'&zakladka='.(int)$_POST["zakladka"]);

    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Faktura</div>

    <?php
    $kopia_proformy = false;
    if ( isset($_GET['proforma_id']) && $_GET['proforma_id'] != '' ) {
      $_GET['id'] = $_GET['proforma_id'];
      $kopia_proformy = true;
      $numer_faktury = Sprzedaz::WygenerujNumerFaktury($_GET['typ']); 
    }
    ?>

    <div id="cont">

      <?php
    
      if ( !isset($_GET['id']) ) {
         $_GET['id'] = 0;
      }    
            
      $zapytanie = "SELECT * FROM invoices WHERE invoices_id  = '" . (int)$_GET['id'] . "'";
      $sql = $db->open_query($zapytanie);
            
      if ((int)$db->ile_rekordow($sql) > 0) {

        $zamowienie = new Zamowienie((int)$_GET['id_poz']);

        $info = $sql->fetch_assoc();
        ?>
        
        <script>
        $(document).ready(function() {

          $("#fakturaForm").validate({
            rules: {
              faktura_numer: {required: true, remote: "ajax/sprawdz_numer_faktury.php?typ=2&id=<?php echo (int)$_GET['id']; ?>"},
              <?php if ( trim((string)$info['invoices_billing_company_name']) != '' ) { ?>
              klient_firma: {required: true},
              <?php } else { ?>
              klient_nazwa: {required: true},
              <?php } ?>
              klient_ulica: {required: true},
              klient_miasto: {required: true},
              klient_kod_pocztowy: {required: true}
            },
            messages: {
              faktura_numer: {required: "Pole jest wymagane.", remote: "Taki numer faktury już istnieje."}
            }
          });

          $('input.datepicker').Zebra_DatePicker({
            format: 'd-m-Y',
            inside: false,
            readonly_element: true,
            show_clear_date: false
          });
          
        });
        </script>            
            
        <form action="sprzedaz/zamowienia_faktura_edytuj.php" method="post" id="fakturaForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Faktura do zamówienia nr: <?php echo $_GET['id_poz']; ?></div>
                
            <div class="pozycja_edytowana">
              <div class="info_content">
                    
                <input type="hidden" name="akcja" value="zapisz" />
                <input type="hidden" name="zamowienie_id" value="<?php echo (int)$_GET['id_poz']; ?>" />
                <input type="hidden" name="zakladka" value="<?php echo (int)$_GET['zakladka']; ?>" />
                <input type="hidden" name="id_faktury" value="<?php echo (int)$_GET['id']; ?>" />

                <p>
                    <label class="required">Numer faktury:</label>
                    <input type="text" name="faktura_numer" id="faktura_numer" size="10" value="<?php echo ( $kopia_proformy ? $numer_faktury : $info['invoices_nr'] ); ?>" /> <span class="RokFaktury">/<?php echo date('Y', FunkcjeWlasnePHP::my_strtotime($info['invoices_date_generated'])); ?></span>
                    <?php if ( MIESIECZNE_FAKTURY == 'tak' ) { ?>
                    - numeracja miesięczna za miesiąc <?php echo date('m', time()) . '.' . ROK_KSIEGOWY_FAKTUROWANIA; ?>
                    <?php } ?>                    
                    <label style="display:none" class="error" for="faktura_numer" generated="true"></label>
                </p> 

                <p>
                    <label>Data sprzedaży:</label>
                    <input type="text" name="data_sprzedazy" id="data_sprzedazy" size="20" value="<?php echo date('d-m-Y', FunkcjeWlasnePHP::my_strtotime($info['invoices_date_sell'])); ?>" class="datepicker" />
                </p> 

                <p>
                    <label>Data wystawienia:</label>
                    <input type="text" name="data_wystawienia" id="data_wystawienia" size="20" value="<?php echo ( $kopia_proformy ? date("d-m-Y") : date('d-m-Y', FunkcjeWlasnePHP::my_strtotime($info['invoices_date_generated'])) ); ?>" class="datepicker" />
                </p> 
                
                <hr style="color:#82b4cd;border-top: 1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:98%;" />

                <p>
                    <label class="required">Nabywca:</label>
                    <?php if ( trim((string)$info['invoices_billing_company_name']) != '' ) { ?>
                        <input type="text" name="klient_firma" id="klient_firma" size="120" value="<?php echo Funkcje::formatujTekstInput($info['invoices_billing_company_name']); ?>" />
                    <?php } else { ?>
                        <input type="text" name="klient_nazwa" id="klient_nazwa" size="120" value="<?php echo Funkcje::formatujTekstInput($info['invoices_billing_name']); ?>" />
                    <?php } ?>
                </p> 

                <p>
                    <label>NIP:</label>
                    <input type="text" name="klient_nip" id="klient_nip" size="30" value="<?php echo $info['invoices_billing_nip']; ?>" />
                    <label style="padding-left:20px;width:45px;">PESEL:</label>
                    <input type="text" name="klient_pesel" id="pesel" size="20" value="<?php echo $info['invoices_billing_pesel']; ?>" />
                </p> 

                <p>
                    <label class="required">Adres:</label>
                    <input type="text" name="klient_ulica" id="klient_ulica" size="120" value="<?php echo Funkcje::formatujTekstInput($info['invoices_billing_street_address']); ?>" />
                </p> 

                <p>
                    <label class="required">Miejscowość:</label>
                    <input type="text" name="klient_miasto" id="klient_miasto" size="80" value="<?php echo Funkcje::formatujTekstInput($info['invoices_billing_city']); ?>" />
                </p> 

                <p>
                    <label class="required">Kod pocztowy:</label>
                    <input type="text" name="klient_kod_pocztowy" id="klient_kod_pocztowy" size="53" value="<?php echo $info['invoices_billing_postcode']; ?>" />
                </p> 
                
                <p>
                    <label>Kraj:</label>
                    <input type="text" name="klient_panstwo" id="klient_panstwo" size="53" value="<?php echo $info['invoices_billing_country']; ?>" />
                </p> 
                
                <hr style="color:#82b4cd;border-top: 1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:98%;" />

                <?php
                $tablica = array();
                $tablica = Sprzedaz::TablicaTypowPlatnosci();
                ?>
                <p>
                    <label>Rodzaj płatności:</label>
                    <?php echo Funkcje::RozwijaneMenu('platnosc', $tablica, $info['invoices_payment_type']); ?>
                </p> 

                <p>
                    <label>Zapłacona:</label>
                    <input type="radio" value="1" name="rozliczona" id="rozliczona_tak" <?php echo ( $info['invoices_payment_status'] == '1' ? 'checked="checked"' : '' ); ?> onclick="$('#data').slideUp()" /> <label class="OpisFor" for="rozliczona_tak">tak</label>
                    <input type="radio" value="0" name="rozliczona" id="rozliczona_nie" <?php echo ( $info['invoices_payment_status'] == '0' ? 'checked="checked"' : '' ); ?> onclick="$('#data').slideDown()" /> <label class="OpisFor" for="rozliczona_nie">nie</label>
                </p> 
                
                <p>
                  <label>Dodaj informację o "split payment":</label>
                  <input type="radio" value="1" name="split_payment" id="split_payment_tak" <?php echo ( $info['split_payment'] == '1' ? 'checked="checked"' : '' ); ?> /> <label class="OpisFor" for="split_payment_tak">tak</label>
                  <input type="radio" value="0" name="split_payment" id="split_payment_nie" <?php echo ( $info['split_payment'] == '0' ? 'checked="checked"' : '' ); ?> /> <label class="OpisFor" for="split_payment_nie">nie</label>
                </p>                
                
                <?php if ( INTEGRACJA_FAKTUROWNIA_WLACZONY == 'tak' ) { ?>
                
                <p>
                    <label>&nbsp;</label>
                    zmień status w Fakturownia.pl <em class="TipIkona"><b>Zmiana statusu zapłacona / niezapłacona w serwisie Fakturownia.pl</b></em> &nbsp; <input type="radio" value="1" name="fakturownia" id="fakturownia_tak" checked="checked" /> <label class="OpisFor" for="fakturownia_tak">tak</label>
                    <input type="radio" value="0" name="fakturownia" id="fakturownia_nie" /> <label class="OpisFor" for="fakturownia_nie">nie</label>
                </p>                 
                
                <?php } ?>

                <p id="data" <?php echo ($info['invoices_payment_status'] == '1' ? 'style="display:none;"' : '' ); ?>>
                    <label>Data płatności:</label>
                    <input type="text" name="data_platnosci" id="data_platnosci" size="20" value="<?php echo date('d-m-Y', ($info['invoices_date_payment'] == '0000-00-00 00:00:00' ? time() : FunkcjeWlasnePHP::my_strtotime($info['invoices_date_payment']) )); ?>" class="datepicker" />
                </p> 

                <p>
                  <label for="jezyk">Język w jakim ma być utworzona faktura:</label>
                  <?php
                  $tablica_jezykow = Funkcje::TablicaJezykow();                 
                  echo Funkcje::RozwijaneMenu('jezyk', $tablica_jezykow, $info['invoices_language_id'],  'id="jezyk"');
                  unset($tablica_jezykow);
                  ?>                  
                </p>              

              </div>

              <p>
                  <label style="padding-left:0px">Komentarz:</label>
                  <textarea cols="70" class="KomentarzFaktury" rows="5" name="komentarz" id="komentarz"><?php echo $info['invoices_comments'] ?></textarea>
              </p>        

            </div>

            <div class="przyciski_dolne">
                <input type="submit" class="przyciskNon" value="Zapisz dane" />
                <button type="button" class="przyciskNon" onclick="cofnij('zamowienia_szczegoly','<?php echo Funkcje::Zwroc_Get(array('x','y')); ?>','sprzedaz');">Powrót</button>           
            </div>
            
          </div>

        </form>

        <?php

      } else {

        echo '<div class="pozycja_edytowana">Brak danych do wyświetlenia</div>';

      }
      $db->close_query($sql);
      unset($zapytanie, $info);

      ?>

    </div>
    
    <?php
    include('stopka.inc.php');

}

?>