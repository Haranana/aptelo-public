<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

$czy_jest_blad = false;

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
      
        $Przetworzono = 0;
      
        if (isset($_FILES) && count($_FILES) > 0 && isset($_FILES['file']['tmp_name']) && !empty($_FILES['file']['tmp_name'])) {

            $nazwa_plik = $_FILES['file']['tmp_name']; 

            // tworzy tablice z nazwami naglowkow i danymi z pliku csv
            $file = new SplFileObject($nazwa_plik);
            $file->seek( 0 );
            $DefinicjeCSV = $file->current();        

            $TabDefinicji = array();
            
            // stworzenie tablicy z definicjami
            $TabDefinicji = str_getcsv($DefinicjeCSV, ';');   
            $TablicaDef = array();

            foreach ($TabDefinicji as $Definicja) {

                $TablicaDef[] = trim((string)$Definicja);

            }   
            
            $IlePozycji = Funkcje::IloscLinii($nazwa_plik) - 1;

            if ( $IlePozycji > 0 ) {
            
                for ( $linia = 1; $linia <= $IlePozycji; $linia++ ) {
                  
                    $file->seek( $linia );
                    $DaneCsv = $file->current(); 

                    $TabDaneCsv = array();

                    $TabDaneCsv = str_getcsv($DaneCsv, ';');
                    $TablicaDane = array();

                    if (count($TabDaneCsv) > 0) {
                        //
                        for ($q = 0, $c = count($TablicaDef); $q < $c; $q++) {
                            
                            if (isset($TabDaneCsv[$q])) {
                                //
                                $TablicaDane[$TablicaDef[$q]] = trim((string)$TabDaneCsv[$q]);

                            }
                            
                        }
                        //
                    }
                    
                    if ( isset($TablicaDane['Id']) && (int)$TablicaDane['Id'] > 0 && isset($TablicaDane['Id_produktu']) && (int)$TablicaDane['Id_produktu'] > 0) {
                         //
                         $IdPozycji = (int)$TablicaDane['Id'];
                         //
                         $sql = $db->open_query("SELECT products_id, products_tax_class_id FROM products WHERE products_id = " . (int)$TablicaDane['Id_produktu']);
                         $info = $sql->fetch_assoc();
                         //
                         $vat = Produkty::PokazStawkeVAT($info['products_tax_class_id']);
                         //
                         $db->close_query($sql);  
                         //
                         if ( isset($TablicaDane['Cena_indywidualna_netto']) && (float)$TablicaDane['Cena_indywidualna_netto'] > 0 ) {
                              //
                              $netto = (float)$TablicaDane['Cena_indywidualna_netto'];
                              $brutto = (float)$TablicaDane['Cena_indywidualna_netto'] * ((100 + $vat) / 100);
                              $ile_vat = $brutto - $netto;
                              //
                              $pola = array();
                              $pola[] = array('cp_price', $netto);
                              $pola[] = array('cp_price_tax', $brutto);
                              $pola[] = array('cp_tax', $ile_vat);
                              //
                              $db->update_query('customers_price', $pola, ' cp_id = ' . (int)$TablicaDane['Id']);
                              //
                              $Przetworzono++;
                              //
                         } else if ( isset($TablicaDane['Cena_indywidualna_brutto']) && (float)$TablicaDane['Cena_indywidualna_brutto'] > 0 ) {
                              //
                              $brutto = (float)$TablicaDane['Cena_indywidualna_brutto'];
                              $netto = (float)$TablicaDane['Cena_indywidualna_brutto'] / ((100 + $vat) / 100);
                              $ile_vat = $brutto - $netto;
                              //
                              $pola = array();
                              $pola[] = array('cp_price', $netto);
                              $pola[] = array('cp_price_tax', $brutto);
                              $pola[] = array('cp_tax', $ile_vat);
                              //
                              $db->update_query('customers_price', $pola, ' cp_id = ' . (int)$TablicaDane['Id']);
                              //
                              $Przetworzono++;
                              //
                         }
                         //
                    }
                    
                }
                
            }
            
        }
        
        Funkcje::PrzekierowanieURL('indywidualne_ceny_produktow_import.php?suma=' . $Przetworzono);
        
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Import z pliku CSV</div>
    <div id="cont">
    
      <div class="poleForm">
          
          <div class="naglowek">Import danych</div>
                
          <?php
          if (isset($_GET['suma'])) {
          ?>

          <div id="sukcesAktualizacji">
              Plik został wczytany. <br />
              Ilość zmodyfikowanych pozycji: <strong><?php echo (int)$_GET['suma']; ?></strong>
          </div>

          <div class="przyciski_dolne">
            <button type="button" class="przyciskNon" onclick="cofnij('indywidualne_ceny_produktow','','klienci');">Powrót</button>    
          </div>                 
          
          <?php
          
          } else { 
          
          ?>    
    
          <form action="klienci/indywidualne_ceny_produktow_import.php" method="post" id="indywidualne_ceny_produktowForm" class="cmxform" enctype="multipart/form-data">   

              <script>
              $(function(){
                 $('#upload').MultiFile({
                  max: 1,
                  accept:'txt|csv',
                  STRING: {
                   denied:'Nie można przesłać pliku w tym formacie $ext!',
                   selected:'Wybrany plik: $file',
                  }
                 }); 
              });
              </script>          

              <div class="pozycja_edytowana">
                  
                  <div class="info_content">
              
                  <input type="hidden" name="akcja" value="zapisz" />
                  
                  <div class="NaglowekEksport">Parametry importu</div>
                  
                  <div class="RamkaImport">                  

                      <p style="padding:12px;">
                        <label>Plik do importu:</label>
                        <input type="file" name="file" id="upload" size="53" />
                      </p>

                  </div>
                  
                  <div class="LegnedaImport">
                  
                      <span class="maleInfo" style="margin-left:0px">Maksymalna wielkość pliku do wczytania: <?php echo Funkcje::MaxUpload(); ?> Mb</span>
                  
                      <div class="ostrzezenie">Plik CSV musi zawierać kolumny: Id (id pozycji rabatu - nie id produktu) oraz Cena_indywidualna_netto lub Cena_indywidualna_brutto - w dowolnej kolejności</div> <br />                    
                      <div class="ostrzezenie">Jeżeli importowany plik nie będzie zawierał wymaganych kolumn nie będzie zaimportowany.</div> <br />
                      <div class="ostrzezenie">Jeżeli sklep podczas importu nie znajdzie produktu wg wybranego identyfikatora - pozycja nie zostanie zaimportowana.</div> <br />
                  </div>                  
                  
                  </div>
               
              </div>

              <div class="przyciski_dolne">
                <input type="submit" class="przyciskNon" value="Importuj dane" />
                <button type="button" class="przyciskNon" onclick="cofnij('indywidualne_ceny_produktow','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','klienci');">Powrót</button>           
              </div>                 

          </form>
          
          <?php } ?>
          
      </div>
      
    </div>    

    <?php
    include('stopka.inc.php');

}
?>