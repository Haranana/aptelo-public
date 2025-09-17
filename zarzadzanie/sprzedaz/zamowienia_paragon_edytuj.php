<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

      // dodanie rekordu do tablicy
      $pola = array(
              array('receipts_nr',$filtr->process($_POST['paragon_numer'])),
              array('receipts_date_sell',date('Y-m-d', FunkcjeWlasnePHP::my_strtotime($filtr->process($_POST['data_sprzedazy'])))),
              array('receipts_date_generated',date('Y-m-d', FunkcjeWlasnePHP::my_strtotime($filtr->process($_POST['data_wystawienia'])))),
              array('receipts_date_modified','now()'),
              array('receipts_comments',$filtr->process($_POST['komentarz'])),
              array('receipts_language_id',(int)$_POST["jezyk"]));

      $db->update_query('receipts' , $pola, " receipts_id = '".(int)$_POST['id_paragonu']."'");
      unset($pola);

      Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz='.(int)$_POST["zamowienie_id"].'&zakladka='.(int)$_POST["zakladka"].'');

    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Paragon</div>

    <div id="cont">

      <?php
    
      if ( !isset($_GET['id']) ) {
         $_GET['id'] = 0;
      }    
            
      $zapytanie = "SELECT * FROM receipts WHERE receipts_id  = '" . (int)$_GET['id'] . "'";
      $sql = $db->open_query($zapytanie);
            
      if ((int)$db->ile_rekordow($sql) > 0) {

        $zamowienie = new Zamowienie((int)$_GET['id_poz']);

        $info = $sql->fetch_assoc();
        ?>
        
        <script>
        $(document).ready(function() {
        
          $("#paragonForm").validate({
            rules: {
              paragon_numer: {required: true, remote: "ajax/sprawdz_numer_paragonu.php?id=<?php echo (int)$_GET['id']; ?>"}
            },
            messages: {
              paragon_numer: {required: "Pole jest wymagane.", remote: "Taki numer paragonu już istnieje."}
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
            
        <form action="sprzedaz/zamowienia_paragon_edytuj.php" method="post" id="paragonForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Paragon do zamówienia nr: <?php echo $_GET['id_poz']; ?></div>
                
            <div class="pozycja_edytowana">
            
              <div class="info_content">
                    
                <input type="hidden" name="akcja" value="zapisz" />
                <input type="hidden" name="zamowienie_id" value="<?php echo (int)$_GET['id_poz']; ?>" />
                <input type="hidden" name="zakladka" value="<?php echo (int)$_GET['zakladka']; ?>" />
                <input type="hidden" name="id_paragonu" value="<?php echo (int)$_GET['id']; ?>" />

                <p>
                    <label class="required">Numer paragonu:</label>
                    <input type="text" name="paragon_numer" id="paragon_numer" size="10" value="<?php echo $info['receipts_nr']; ?>" /> <span class="RokFaktury">/<?php echo date('Y', FunkcjeWlasnePHP::my_strtotime($info['receipts_date_generated'])); ?></span>
                    <?php if ( MIESIECZNE_FAKTURY == 'tak' ) { ?>
                    - numeracja miesięczna za miesiąc <?php echo date('m', time()) . '.' . ROK_KSIEGOWY_FAKTUROWANIA; ?>
                    <?php } ?>                        
                    <label style="display:none" class="error" for="paragon_numer" generated="true"></label>
                </p> 

                <p>
                    <label>Data sprzedaży:</label>
                    <input type="text" name="data_sprzedazy" id="data_sprzedazy" size="20" value="<?php echo date('d-m-Y', FunkcjeWlasnePHP::my_strtotime($info['receipts_date_sell'])); ?>" class="datepicker" />
                </p> 

                <p>
                    <label>Data wystawienia:</label>
                    <input type="text" name="data_wystawienia" id="data_wystawienia" size="20" value="<?php echo date('d-m-Y', FunkcjeWlasnePHP::my_strtotime($info['receipts_date_generated'])); ?>" class="datepicker" />
                </p> 
                
                <p>
                  <label for="jezyk">Język w jakim ma być utworzony paragon:</label>
                  <?php
                  $tablica_jezykow = Funkcje::TablicaJezykow();                 
                  echo Funkcje::RozwijaneMenu('jezyk', $tablica_jezykow, $info['receipts_language_id'],  'id="jezyk"');
                  unset($tablica_jezykow);
                  ?>                  
                </p>     
                
              </div>

              <div class="info_content">

                <p>
                    <label>Komentarz:</label>
                    <textarea cols="70" class="KomentarzFaktury" rows="5" name="komentarz" id="komentarz"><?php echo $info['receipts_comments'] ?></textarea>
                </p> 

              </div>

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