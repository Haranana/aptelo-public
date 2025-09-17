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

      // dodanie rekordu do tablicy
      $pola = array(
              array('orders_id',(int)$_POST['zamowienie_id']),
              array('receipts_nr',$filtr->process($_POST['paragon_numer'])),
              array('receipts_date_sell',date('Y-m-d', FunkcjeWlasnePHP::my_strtotime($filtr->process($_POST['data_sprzedazy'])))),
              array('receipts_date_generated',date('Y-m-d', FunkcjeWlasnePHP::my_strtotime($filtr->process($_POST['data_wystawienia'])))),
              array('receipts_date_modified','now()'),
              array('receipts_comments',$filtr->process($_POST['komentarz'])),
              array('receipts_language_id',(int)$_POST["jezyk"]));
              
      $db->insert_query('receipts' , $pola);
      unset($pola);
      
      $id_dodanej_pozycji = $db->last_id_query();

      Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz='.(int)$_POST["zamowienie_id"].'&zakladka='.(int)$_POST["zakladka"].'');

    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    
    $numer_paragonu = Sprzedaz::WygenerujNumerParagonu(); 
    
    $zapytanie = "SELECT * FROM orders WHERE orders_id = '" . (int)$_GET['id_poz'] . "'";
    $sql = $db->open_query($zapytanie);
          
    if ((int)$db->ile_rekordow($sql) > 0) {    
    
        $zamowienie = new Zamowienie((int)$_GET['id_poz']);

    }
    $db->close_query($sql);
    unset($zapytanie);
    ?>
    
    <div id="naglowek_cont">Paragon</div>

    <div id="cont">

      <?php if ( isset($zamowienie) ) { ?>
        
        <script>
        $(document).ready(function() {

          $("#paragonForm").validate({
            rules: {
              paragon_numer: {required: true, remote: "ajax/sprawdz_numer_paragonu.php"}
            },
            messages: {
              paragon_numer: {required: "Pole jest wymagane.", remote: "Taki numer paragonu już istnieje."}
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
            
        <form action="sprzedaz/zamowienia_paragon_generuj.php" method="post" id="paragonForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Paragon do zamówienia nr: <?php echo $_GET['id_poz']; ?></div>
                
            <div class="pozycja_edytowana">
            
              <div class="info_content">
                    
                <input type="hidden" name="akcja" value="zapisz" />
                <input type="hidden" name="zamowienie_id" value="<?php echo (int)$_GET['id_poz']; ?>" />
                <input type="hidden" name="zakladka" value="<?php echo (int)$_GET['zakladka']; ?>" />

                <p>
                    <label class="required">Numer paragonu:</label>
                    <input type="text" name="paragon_numer" id="paragon_numer" size="10" value="<?php echo $numer_paragonu; ?>" /> <span class="RokFaktury">/<?php echo ROK_KSIEGOWY_FAKTUROWANIA; ?></span>
                    <?php if ( MIESIECZNE_FAKTURY == 'tak' ) { ?>
                    - numeracja miesięczna za miesiąc <?php echo date('m', time()) . '.' . ROK_KSIEGOWY_FAKTUROWANIA; ?>
                    <?php } ?>                    
                    <label style="display:none" class="error" for="paragon_numer" generated="true"></label>
                </p> 

                <p>
                    <label>Data sprzedaży:</label>
                    <input type="text" name="data_sprzedazy" id="data_sprzedazy" size="20" value="<?php echo date('d-m-Y', FunkcjeWlasnePHP::my_strtotime($zamowienie->info['data_zamowienia'])); ?>" class="datepicker" />
                </p> 

                <p>
                    <label>Data wystawienia:</label>
                    <input type="text" name="data_wystawienia" id="data_wystawienia" size="20" value="<?php echo date("d-m-Y"); ?>" class="datepicker" />
                </p> 
                
                <p>
                  <label for="jezyk">Język w jakim ma być utworzony paragon:</label>
                  <?php
                  $tablica_jezykow = Funkcje::TablicaJezykow();                 
                  echo Funkcje::RozwijaneMenu('jezyk', $tablica_jezykow, '',  'id="jezyk"');
                  unset($tablica_jezykow);
                  ?>                  
                </p>                   

              </div>

              <div class="info_content">

                <p>
                    <label>Komentarz:</label>
                    <textarea cols="70" class="KomentarzFaktury" rows="5" name="komentarz" id="komentarz"><?php echo ( FAKTURA_KOMENTARZ_TEKST == 'tak' ? $tlumacz['KLIENT_NUMER_ZAMOWIENIA'] . ': ' . $_GET['id_poz'] : ''); ?></textarea>
                </p> 

              </div>

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