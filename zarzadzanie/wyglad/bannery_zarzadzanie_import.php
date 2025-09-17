<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

$czy_jest_blad = false;

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
      
        $wynik = 'blad';
      
        if (isset($_FILES) && count($_FILES) > 0 && isset($_FILES['file']['tmp_name']) && !empty($_FILES['file']['tmp_name'])) {

            $tresc = unserialize(base64_decode(file_get_contents($_FILES['file']['tmp_name'])));
            
            if ( is_array($tresc) ) {
              
                foreach ( $tresc as $tablica => $dane ) {
                  
                    foreach ( $dane as $podtablica ) {
                      
                        if ( $tablica == 'banners_group' ) {

                             if ( isset($podtablica['kod']) && isset($podtablica['opis']) ) {
                  
                                 $zapytanie = "select * from banners_group where banners_group_code = '" . $podtablica['kod'] . "'";
                                 $sql = $db->open_query($zapytanie);
                                 //
                                 if ($db->ile_rekordow($sql) == 0) {
                                     //
                                     $pola = array();
                                     $pola[] = array( 'banners_group_code', $podtablica['kod'] );
                                     $pola[] = array( 'banners_group_title', $podtablica['opis'] );
                                     //               
                                     $db->insert_query('banners_group' , $pola);
                                     //
                                     unset($pola);
                                     //
                                 }
                                 //
                                 $db->close_query($sql);

                             }                                     
                      
                        }
                                                  
                        if ( $tablica == 'banners' ) {
                          
                             $pola = array();
                          
                             foreach ( $podtablica as $wartosc ) {

                                $pola[] = array( $wartosc[0], $wartosc[1] );

                             }      

                             $db->insert_query('banners' , $pola);
                             //
                             unset($pola);
                                 
                        }                        
                        
                    }
                  
                }

                $wynik = 'sukces';

            } else {
              
                Funkcje::PrzekierowanieURL('bannery_zarzadzanie_import.php?wynik=' . $wynik);
                            
            }
                        
        }
        
        Funkcje::PrzekierowanieURL('bannery_zarzadzanie_import.php?wynik=' . $wynik);
        
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Import danych</div>
    
    <div id="cont">
    
      <?php if (isset($_GET['wynik'])) { ?>
      
          <div class="poleForm">
              
              <div class="naglowek">Import danych</div>    

              <?php if ( $_GET['wynik'] == 'sukces' ) { ?>
              
              <div id="sukcesImportu">
                  Plik został wczytany.
              </div>
              
              <?php } else { ?>
              
              <div id="bladImportu">
                  Plik nie został wczytany. Plik zawiera błędy i nie można wczytać danych.
              </div>          
              
              <?php } ?>

              <div class="przyciski_dolne">
                <button type="button" class="przyciskNon" onclick="cofnij('bannery_zarzadzanie','','wyglad');">Powrót</button>    
              </div>  

          </div>
      
      <?php } else { ?>      

          <form action="wyglad/bannery_zarzadzanie_import.php" method="post" id="srodekForm" class="cmxform" enctype="multipart/form-data">   
          
              <script>
              $(function(){
                 $('#upload').MultiFile({
                  max: 1,
                  accept:'data',
                  STRING: {
                   denied:'Nie można przesłać pliku w tym formacie $ext!',
                   selected:'Wybrany plik: $file'
                  }
                 }); 
              });
              </script>          

              <div class="poleForm">
                <div class="naglowek">Import danych</div>
                
                <?php if ( Wyglad::TypSzablonu() == true || ( isset($_SESSION['programista']) && $_SESSION['programista'] == '1' )) { ?>
                
                <div class="pozycja_edytowana">
                    
                    <div class="info_content">

                    <input type="hidden" name="akcja" value="zapisz" />

                    <p style="padding:12px;">
                      <label for="upload">Plik do importu:</label>
                      <input type="file" name="file" id="upload" size="53" />
                    </p>

                    <span class="maleInfo" style="margin-left:10px">Maksymalna wielkość pliku do wczytania: <?php echo Funkcje::MaxUpload(); ?> Mb</span>

                    </div>
                 
                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Importuj dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('bannery_zarzadzanie','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','wyglad');">Powrót</button>           
                </div>                 

                <?php
                } else {
                
                    echo '<div class="pozycja_edytowana">Brak danych do wyświetlenia</div>';
                
                }
                ?>
                
              </div> 
              
          </form>
          
      <?php } ?>

    </div>    

    <?php
    include('stopka.inc.php');

}
?>