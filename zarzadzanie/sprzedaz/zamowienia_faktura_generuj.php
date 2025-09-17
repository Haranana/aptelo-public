<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    $i18n = new Translator($db, $_SESSION['domyslny_jezyk']['id']);
    $tlumacz = $i18n->tlumacz( array('KLIENCI_PANEL') );

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

      $wartosc_towarow_brutto = 0;

      // dodanie rekordu do tablicy invoices
      $pola = array(
              array('orders_id',$filtr->process($_POST['zamowienie_id'])),
              array('invoices_type',$filtr->process($_POST['rodzaj_faktury'])),
              array('invoices_nr',$filtr->process($_POST['faktura_numer'])),
              array('invoices_date_sell',date('Y-m-d', FunkcjeWlasnePHP::my_strtotime($filtr->process($_POST['data_sprzedazy'])))),
              array('invoices_date_generated',date('Y-m-d', FunkcjeWlasnePHP::my_strtotime($filtr->process($_POST['data_wystawienia'])))),
              array('invoices_date_modified','now()'),
              array('invoices_billing_name',((isset($_POST['klient_nazwa'])) ? $filtr->process($_POST['klient_nazwa']) : '')),
              array('invoices_billing_company_name',((isset($_POST['klient_firma'])) ? $filtr->process($_POST['klient_firma']) : '')),
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
              
      $db->insert_query('invoices' , $pola);
      unset($pola);
      
      Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz='.(int)$_POST["zamowienie_id"].'&zakladka='.$filtr->process($_POST["zakladka"]));

    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');

    $numer_faktury = Sprzedaz::WygenerujNumerFaktury( '2' ); 
    
    $zapytanie = "SELECT * FROM orders WHERE orders_id = '" . (int)$_GET['id_poz'] . "'";
    $sql = $db->open_query($zapytanie);
          
    if ((int)$db->ile_rekordow($sql) > 0) {    
    
        $zamowienie = new Zamowienie((int)$_GET['id_poz']);

    }
    $db->close_query($sql);
    unset($zapytanie);
    ?>
    
    <div id="naglowek_cont">Faktura</div>

    <div id="cont">

      <?php if ( isset($zamowienie) ) { ?>
        
        <script>
        $(document).ready(function() {

          $("#fakturaForm").validate({
            rules: {
              faktura_numer: {required: true, remote: "ajax/sprawdz_numer_faktury.php?typ=2"},
              <?php if ( trim((string)$zamowienie->platnik['firma']) != '' ) { ?>
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
            readonly_element: true
          });

          $('#jezyk').on('change', function() {
             <?php if ( FAKTURA_KOMENTARZ_TEKST == 'tak' ) { ?>
                const tlumaczenie = [];
                <?php 
                $zapytanie = "SELECT e.translate_constant_id AS id, e.translate_constant AS element, ec.translate_value AS content, ec.language_id AS jezyk FROM (translate_constant e, translate_section s, translate_value ec) WHERE e.translate_constant_id = ec.translate_constant_id AND e.translate_constant = 'KLIENT_NUMER_ZAMOWIENIA' AND e.section_id = s.section_id"; 
                $sql = $db->open_query($zapytanie);
                while ($info = $sql->fetch_assoc()) {
                    echo 'tlumaczenie['.$info['jezyk'].'] = "'.$info['content'].'";';
                    echo "\n";
                }
                $db->close_query($sql);
                unset($zapytanie, $info);
                ?>
                var nr_zam = '<?php echo $_GET["id_poz"]; ?>';
                var tekst = tlumaczenie[this.value];
                $('#komentarz').text(tekst + ': ' + nr_zam);
             <?php } ?>
          });
        });
        </script>
            
        <form action="sprzedaz/zamowienia_faktura_generuj.php" method="post" id="fakturaForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Faktura do zamówienia nr: <?php echo $_GET['id_poz']; ?></div>
                
            <div class="pozycja_edytowana">
            
              <div class="info_content">
                    
                <input type="hidden" name="akcja" value="zapisz" />
                <input type="hidden" name="zamowienie_id" value="<?php echo (int)$_GET['id_poz']; ?>" />
                <input type="hidden" name="zakladka" value="<?php echo (int)$_GET['zakladka']; ?>" />
                <input type="hidden" name="rodzaj_faktury" value="2" />

                <p>
                    <label class="required">Numer faktury:</label>
                    <input type="text" name="faktura_numer" id="faktura_numer" size="10" value="<?php echo $numer_faktury; ?>" /> <span class="RokFaktury">/<?php echo ROK_KSIEGOWY_FAKTUROWANIA; ?></span> 
                    <?php if ( MIESIECZNE_FAKTURY == 'tak' ) { ?>
                    - numeracja miesięczna za miesiąc <?php echo date('m', time()) . '.' . ROK_KSIEGOWY_FAKTUROWANIA; ?>
                    <?php } ?>
                    <label style="display:none" class="error" for="faktura_numer" generated="true"></label>
                </p> 

                <p>
                    <label>Data sprzedaży:</label>
                    <input type="text" name="data_sprzedazy" id="data_sprzedazy" size="20" value="<?php echo date('d-m-Y', FunkcjeWlasnePHP::my_strtotime($zamowienie->info['data_zamowienia'])); ?>" class="datepicker" />
                </p> 

                <p>
                    <label>Data wystawienia:</label>
                    <input type="text" name="data_wystawienia" id="data_wystawienia" size="20" value="<?php echo date("d-m-Y"); ?>" class="datepicker" />
                </p> 
                
                <hr style="color:#82b4cd;border-top: 1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:98%;" />

                <p>
                    <label class="required">Nabywca:</label>
                    <?php if ( trim((string)$zamowienie->platnik['firma']) != '' ) { ?>
                        <input type="text" name="klient_firma" id="klient_firma" size="120" value="<?php echo Funkcje::formatujTekstInput($zamowienie->platnik['firma']); ?>" />
                    <?php } else { ?>
                        <input type="text" name="klient_nazwa" id="klient_nazwa" size="120" value="<?php echo Funkcje::formatujTekstInput($zamowienie->platnik['nazwa']); ?>" />
                    <?php } ?>
                </p> 

                <p>
                    <label>NIP:</label>
                    <input type="text" name="klient_nip" id="klient_nip" size="30" value="<?php echo $zamowienie->platnik['nip']; ?>" />
                    <label style="padding-left:20px;width:45px;">PESEL:</label>
                    <input type="text" name="klient_pesel" id="pesel" size="20" value="<?php echo $zamowienie->platnik['pesel']; ?>" />
                </p> 

                <p>
                    <label class="required">Adres:</label>
                    <input type="text" name="klient_ulica" id="klient_ulica" size="120" value="<?php echo Funkcje::formatujTekstInput($zamowienie->platnik['ulica']); ?>" />
                </p> 

                <p>
                    <label class="required">Miejscowość:</label>
                    <input type="text" name="klient_miasto" id="klient_miasto" size="80" value="<?php echo Funkcje::formatujTekstInput($zamowienie->platnik['miasto']); ?>" />
                </p> 

                <p>
                    <label class="required">Kod pocztowy:</label>
                    <input type="text" name="klient_kod_pocztowy" id="klient_kod_pocztowy" size="53" value="<?php echo $zamowienie->platnik['kod_pocztowy']; ?>" />
                </p> 
                
                <p>
                    <label>Kraj:</label>
                    <input type="text" name="klient_panstwo" id="klient_panstwo" size="53" value="<?php echo $zamowienie->platnik['kraj']; ?>" />
                </p> 

                <hr style="color:#82b4cd;border-top: 1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:98%;" />

                <?php
                $tablica = array();
                $tablica = Sprzedaz::TablicaTypowPlatnosci();
                ?>
                <p>
                    <label>Rodzaj płatności:</label>
                    <?php echo Funkcje::RozwijaneMenu('platnosc', $tablica, ''); ?>
                </p> 

                <p>
                  <label>Zapłacona:</label>
                  <input type="radio" value="1" name="rozliczona" id="rozliczona_tak" onclick="$('#data').slideUp()" /> <label class="OpisFor" for="rozliczona_tak">tak</label>
                  <input type="radio" value="0" name="rozliczona" id="rozliczona_nie" onclick="$('#data').slideDown()" checked="checked" /> <label class="OpisFor" for="rozliczona_nie">nie</label>
                </p> 

                <p id="data">
                    <label>Data płatności:</label>
                    <input type="text" name="data_platnosci" id="data_platnosci" size="20" value="<?php echo date('d-m-Y', time()); ?>" class="datepicker" />
                </p> 

                <p>
                  <label>Dodaj informację o "split payment":</label>
                  <input type="radio" value="1" name="split_payment" id="split_payment_tak" /> <label class="OpisFor" for="split_payment_tak">tak</label>
                  <input type="radio" value="0" name="split_payment" id="split_payment_nie" checked="checked" /> <label class="OpisFor" for="split_payment_nie">nie</label>
                </p>
                
                <p>
                  <label for="jezyk">Język w jakim ma być utworzona faktura:</label>
                  <?php
                  $tablica_jezykow = Funkcje::TablicaJezykow();                 
                  echo Funkcje::RozwijaneMenu('jezyk', $tablica_jezykow, '',  'id="jezyk"');
                  unset($tablica_jezykow);
                  ?>                  
                </p>                 

              </div>

              <p>
                  <label style="padding-left:0px">Komentarz:</label>
                  <textarea cols="70" class="KomentarzFaktury" rows="5" name="komentarz" id="komentarz"><?php echo ( FAKTURA_KOMENTARZ_TEKST == 'tak' ? $tlumacz['KLIENT_NUMER_ZAMOWIENIA'] . ': ' . $_GET['id_poz'] : ''); ?></textarea>
              </p> 
                
            </div>

            <div class="przyciski_dolne">
                <input type="submit" class="przyciskNon" value="Zapisz dane" />
                <button type="button" class="przyciskNon" onclick="cofnij('zamowienia_szczegoly','<?php echo Funkcje::Zwroc_Get(array('typ','x','y')); ?>','sprzedaz');">Powrót</button>           
            </div>
          </div>

        </form>

        <?php

      } else {

        echo '<div class="pozycja_edytowana">Brak danych do wyświetlenia</div>';

      }

      ?>

    </div>
    
    <?php
    include('stopka.inc.php');

}

?>