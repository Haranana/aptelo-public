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
            $TabDefinicji = str_getcsv($DefinicjeCSV, $_POST['sep']);   
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

                    $TabDaneCsv = str_getcsv($DaneCsv, $_POST['sep']);
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

                    if ( count($TablicaDane) == 6 ) {
                         //
                         $warunek = " where products_id = '0'";
                         //
                         // jezeli po id
                         if ( $_POST['identyfikator'] == 'id' && isset($TablicaDane['Identyfikator']) && (int)$TablicaDane['Identyfikator'] > 0 ) {
                              //
                              $warunek = " where products_id = '" . (int)$TablicaDane['Identyfikator'] . "'";
                              //
                         }
                         // jezeli po ean
                         if ( $_POST['identyfikator'] == 'ean' && isset($TablicaDane['Identyfikator']) && trim((string)$filtr->process($TablicaDane['Identyfikator'])) != '' ) {
                              //
                              $warunek = " where products_ean = '" . trim((string)$filtr->process($TablicaDane['Identyfikator'])) . "'";
                              //
                         }
                         // jezeli po nr kat
                         if ( $_POST['identyfikator'] == 'nr_kat' && isset($TablicaDane['Identyfikator']) && trim((string)$filtr->process($TablicaDane['Identyfikator'])) != '' ) {
                              //
                              $warunek = " where products_model = '" . trim((string)$filtr->process($TablicaDane['Identyfikator'])) . "'";
                              //
                         }
                         //
                         $zapytanie = "select products_id from products" . $warunek;
                         //
                         $sql = $db->open_query($zapytanie); 
                         //
                         if ( (int)$db->ile_rekordow($sql) > 0 ) {
                              //
                              $pola = array();
                              $pola_opis = array();
                              //
                              $info = $sql->fetch_assoc();
                              //
                              $pola[] = array('customers_id','0');
                              //
                              // ocena
                              if ( isset($TablicaDane['Ocena']) && (int)$TablicaDane['Ocena'] > 0 && (int)$TablicaDane['Ocena'] < 6 ) {
                                   //
                                   $pola[] = array('reviews_rating', (int)$TablicaDane['Ocena']);
                                   //
                              }
                              // recenzent
                              if ( isset($TablicaDane['Recenzent_nazwa']) && trim((string)$filtr->process($TablicaDane['Recenzent_nazwa'])) != '' ) {
                                   //
                                   $pola[] = array('customers_name', trim((string)$filtr->process($TablicaDane['Recenzent_nazwa'])));
                                   //
                              }
                              // data
                              if ( isset($TablicaDane['Data_napisania']) && trim((string)$filtr->process($TablicaDane['Data_napisania'])) != '' ) {
                                   //
                                   if ( FunkcjeWlasnePHP::my_strtotime($filtr->process($TablicaDane['Data_napisania'])) < time() && FunkcjeWlasnePHP::my_strtotime($filtr->process($TablicaDane['Data_napisania'])) > time() - ((86400 * 365) * 10) ) {
                                        //
                                        $pola[] = array('date_added', date('Y-m-d H:i', FunkcjeWlasnePHP::my_strtotime($filtr->process($TablicaDane['Data_napisania']))));
                                        //
                                   }
                                   //
                              } 
                              // status
                              if ( isset($TablicaDane['Status']) && (strtolower(trim((string)$filtr->process($TablicaDane['Status']))) == 'tak' || strtolower(trim((string)$filtr->process($TablicaDane['Status']))) == 'nie') ) {
                                   //
                                   $pola[] = array('approved', ((strtolower(trim((string)$filtr->process($TablicaDane['Status']))) == 'tak') ? '1' : '0'));
                                   //
                              }                       
                              // tresc
                              if ( isset($TablicaDane['Recenzja_tresc']) && trim((string)$filtr->process($TablicaDane['Recenzja_tresc'])) != '' ) {
                                   //
                                   $pola_opis[] = array('reviews_text', trim((string)$filtr->process($TablicaDane['Recenzja_tresc'])));
                                   //
                              }                      
                              //
                              
                              if ( count($pola) == 5 && count($pola_opis) == 1 ) {
                                
                                   $pola[] = array('products_id', $info['products_id']);
                                   $db->insert_query('reviews', $pola);
                                   $id_dodanej_pozycji = $db->last_id_query();
                                                
                                   $pola_opis[] = array('reviews_id', $id_dodanej_pozycji);
                                   $pola_opis[] = array('languages_id', $_SESSION['domyslny_jezyk']['id']);
                                   $db->insert_query('reviews_description', $pola_opis);
                                   
                                   $Przetworzono++;
                                   
                              }
                              
                              unset($pola, $pola_opis);   
                              
                         }
                         
                         $db->close_query($sql);
                         unset($zapytanie);  
                         
                    }
                    
                }
                
            }
            
        }

        Funkcje::PrzekierowanieURL('recenzje_import.php?suma=' . $Przetworzono);
        
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Import recenzji z pliku CSV</div>
    <div id="cont">
    
      <div class="poleForm">
          
          <div class="naglowek">Import danych</div>
                
          <?php
          if (isset($_GET['suma'])) {
          ?>

          <div id="sukcesAktualizacji">
              Plik został wczytany. <br />
              Ilość dodanych recenzji: <strong><?php echo (int)$_GET['suma']; ?></strong>
          </div>

          <div class="przyciski_dolne">
            <button type="button" class="przyciskNon" onclick="cofnij('recenzje','','recenzje');">Powrót</button>    
          </div>                 
          
          <?php
          
          } else { 
          
          ?>    
    
          <form action="recenzje/recenzje_import.php" method="post" id="recenzjeForm" class="cmxform" enctype="multipart/form-data">   

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

                  <div class="NaglowekEksport">Parametry importu recenzji do produktów</div>
                  
                  <div class="RamkaRecenzje">

                      <p style="padding:12px;">
                          <label>Wg jakiego parametru będą identyfikowane produkty:</label>
                          <input type="radio" name="identyfikator" id="identyfikator_id" value="id" checked="checked" /><label class="OpisFor" for="identyfikator_id">ID produktu &nbsp;</label>
                          <input type="radio" name="identyfikator" id="identyfikator_nr_kat" value="nr_kat" /><label class="OpisFor" for="identyfikator_nr_kat">nr katalogowy &nbsp;</label>
                          <input type="radio" name="identyfikator" id="identyfikator_ean" value="ean" /><label class="OpisFor" for="identyfikator_ean">kod EAN</label>
                      </p>                    
                  
                      <p style="padding:12px;">
                          <label>Separator pól:</label>
                          <input type="radio" name="sep" id="sep_srednik" value=";" checked="checked" /><label class="OpisFor" for="sep_srednik">; (średnik) &nbsp;</label>
                          <input type="radio" name="sep" id="sep_plotek" value="#" /><label class="OpisFor" for="sep_plotek"># (płotek)</label>
                      </p>
                      
                      <p style="padding:12px;">
                        <label>Plik do importu:</label>
                        <input type="file" name="file" id="upload" size="53" />
                      </p>

                  </div>
                                  
                  <div class="LegnedaRecenzje">
                  
                      <span class="maleInfo" style="margin-left:0px">Maksymalna wielkość pliku do wczytania: <?php echo Funkcje::MaxUpload(); ?> Mb</span>
                  
                      <div class="ostrzezenie">Plik CSV musi zawierać kolumny: Identyfikator;Ocena;Data_napisania;Recenzent_nazwa;Recenzja_tresc;Status - w dowolnej kolejności</div> <br />                    
                      <div class="ostrzezenie">Jeżeli importowany plik nie będzie zawierał wszystkich kolumn nie będzie zaimportowany.</div> <br />
                      <div class="ostrzezenie">Jeżeli sklep podczas importu nie znajdzie produktu wg wybranego identyfikatora - recenzja nie zostanie zaimportowana.</div> <br />
                      <div class="ostrzezenie">Kolumna Ocena może przyjmować wartość od 1 do 5 - jeżeli będzie zawierała inną wartość - recenzja nie zostanie zaimportowana.</div> <br />
                      <div class="ostrzezenie">Data musi być formacie d-m-Y g:m (dzień-miesiąc-rok godzina:minuty) - np 01-12-2019 11:52 - jeżeli format daty będzie inny - recenzja nie zostanie zaimportowana.</div> <br />
                      <div class="ostrzezenie">Treść recenzji nie może zawierać kodu html (zostanie usunięty podczas importu).</div> <br />
                      <div class="ostrzezenie">Nazwa recenzenta musi składać się z min 5 znaków - jeżeli nazwa będzie krótsza - recenzja nie zostanie zaimportowana.</div>
                      <div class="ostrzezenie">Kolumna Status może przejmować wartości Tak/Nie (wielkość liter nie ma znaczenia) - Tak oznacza, że recenzja jest zatwierdzona, Nie - recenzja nie jest zatwierdzona.</div>
                  </div>
                  
                  </div>
               
              </div>

              <div class="przyciski_dolne">
                <input type="submit" class="przyciskNon" value="Importuj dane" />
                <button type="button" class="przyciskNon" onclick="cofnij('recenzje','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','recenzje');">Powrót</button>           
              </div>                 

          </form>
          
          <?php } ?>
          
      </div>
      
    </div>    

    <?php
    include('stopka.inc.php');

}
?>