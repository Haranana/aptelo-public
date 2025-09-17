<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

$czy_jest_blad = false;

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        if ( isset($_POST['jezyk']) ) {
            //
            $jezyki = $filtr->process($_POST['jezyk']);
            //
            $warunki = ' AND (';
            foreach ( $jezyki as $klucz => $kod ) {
                //
                $warunki .= " t.language_id = '" . $klucz . "' or ";
                //
            }
            $warunki .= " t.language_id = '0')";
            //
            if ( (int)$_POST['sekcja'] != 0 ) {
                //
                $warunki .= " AND s.section_id = '" . (int)$_POST['sekcja'] . "'";
                //
            }
            //       
            $zapytanie = "SELECT
                            w.translate_constant_id AS id, 
                            w.translate_constant AS wyrazenie, 
                            t.translate_value AS tresc,
                            t.language_id AS jezyk
                        FROM
                            translate_section AS s,
                            translate_constant AS w,
                            translate_value AS t
                        WHERE
                            w.section_id = s.section_id " . $warunki . " AND
                            t.translate_constant_id = w.translate_constant_id
                   ORDER BY w.translate_constant";
                            
            unset($warunki);
                            
            $sql = $db->open_query($zapytanie);
            
            $tablicaTlumaczen = array();
            
            while ($info = $sql->fetch_assoc()) {
            
                $tablicaTlumaczen[$info['wyrazenie']][$info['jezyk']] = $info['tresc'];
            
            }
            
            //

            $ciag_do_zapisu = 'Nazwa w bazie;';
            
            foreach ( $jezyki as $klucz => $kod ) {
            
                $ciag_do_zapisu .= 'Jezyk ' . $kod . ';';
            
            }
            $ciag_do_zapisu .= "\n";
            
            
            foreach ( $tablicaTlumaczen as $klucz => $wartosc ) {
            
                $ciag_do_zapisu .= $klucz . ';';
                
                foreach ( $jezyki as $klucz => $kod ) {
                
                    if ( isset($wartosc[$klucz]) ) {
                        $ciag_do_zapisu .= preg_replace("/\r\n|\r|\n/", ' <br /> ', (string)$wartosc[$klucz]) . ';';
                    } else {
                        $ciag_do_zapisu .= ';';
                    }

                }

                $ciag_do_zapisu .= "\n";
            }

            //
            $db->close_query($sql);
            unset($info, $jezyki, $zapytanie, $tablicaTlumaczen);      

            header("Content-Type: application/force-download\n");
            header("Cache-Control: cache, must-revalidate");   
            header("Pragma: public");
            header("Content-Disposition: attachment; filename=eksport_tlumaczen_" . date("d-m-Y") . ".txt");
            print $ciag_do_zapisu;
            exit;   
                
            $db->close_query($sql);        

          } else {
            
            Funkcje::PrzekierowanieURL('tlumaczenia.php');
            
        }
        
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Eksport danych tłumaczeń</div>
    <div id="cont">
    
          <form action="slowniki/tlumaczenia_export.php" method="post" id="tlumaczeniaForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Eksport danych tłumaczeń</div>
            
            <div class="pozycja_edytowana">
                
                <div class="info_content">
            
                <input type="hidden" name="akcja" value="zapisz" />
                
                <div class="NaglowekExport">Zakres danych do eksportu</div>
            
                <p>
                  <label>Tłumaczenia w języku:</label>
                  
                  <?php
                  $jezyki = Funkcje::TablicaJezykow();
                  for ($w = 0, $c = count($jezyki); $w < $c; $w++) {
                       //
                       echo '<input type="checkbox" name="jezyk[' . $jezyki[$w]['id'] . ']" id="jezyk_' . $jezyki[$w]['id'] . '" value="' . $jezyki[$w]['kod'] . '" checked="checked" /><label class="OpisFor" for="jezyk_' . $jezyki[$w]['id'] . '">' . $jezyki[$w]['text'] . '</label> &nbsp; ';
                       //
                  }
                  unset($jezyki)
                  ?>
                </p>   
                
                <p>
                  <label for="sekcja">Tylko z sekcji:</label>
                  
                  <?php
                  $tablica = Tlumaczenia::ListaSekcjiTlumaczen();
                  echo Funkcje::RozwijaneMenu('sekcja', $tablica, '', 'id="sekcja"'); 
                  ?>
                <p>              

                </div>
             
            </div>

            <div class="przyciski_dolne">
              <input type="submit" class="przyciskNon" value="Wygeneruj dane" />
              <button type="button" class="przyciskNon" onclick="cofnij('tlumaczenia','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','slowniki');">Powrót</button>           
            </div>                 


          </div>                      
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}
?>