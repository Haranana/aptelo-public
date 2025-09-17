<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

$czy_jest_blad = false;

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
      
        if (isset($_FILES) && count($_FILES) > 0 && isset($_FILES['file']['tmp_name']) && !empty($_FILES['file']['tmp_name'])) {

            $nazwa_plik = $_FILES['file']['tmp_name']; 
            
            if ( !empty($nazwa_plik) ) {
            
                $dane = file($nazwa_plik);

                for($i = 0, $c = count($dane); $i < $c; $i++) {                   
                    //
                    $email = $filtr->process(trim(strtolower((string)$dane[$i])));

                    // trzeba sprawdzic czy takiego emaila juz nie ma w bazie
                    $zapytanie = "select mailing_email_address from mailing where mailing_email_address = '" . $email . "'";
                    $sql = $db->open_query($zapytanie);

                    if ((int)$db->ile_rekordow($sql) == 0) {
                        //
                        $pola = array();
                        $pola[] = array('mailing_email_address',$filtr->process($email));    
                        //
                        $db->insert_query('mailing' , $pola); 
                        //
                        unset($pola);                    
                    }
                    
                    $db->close_query($sql);

                }        

            }
                        
        }
        
        Funkcje::PrzekierowanieURL('newsletter_mailing.php');
        
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Import danych</div>
    <div id="cont">
    
          <form action="newsletter/newsletter_mailing_import.php" method="post" id="newsletterForm" class="cmxform" enctype="multipart/form-data">   
          
          <script>
          $(function(){
             $('#upload').MultiFile({
              max: 1,
              accept:'txt|csv',
              STRING: {
               denied:'Nie można przesłać pliku w tym formacie $ext!',
               selected:'Wybrany plik: $file'
              }
             }); 
          });
          </script>          

          <div class="poleForm">
            <div class="naglowek">Import danych</div>
            
            <div class="pozycja_edytowana">
                
                <div class="info_content">
            
                <input type="hidden" name="akcja" value="zapisz" />

                <p style="padding:12px;">
                  <label for="upload">Plik do importu:</label>
                  <input type="file" name="file" id="upload" size="53" />
                </p>

                <div style="padding:12px;">
                
                    <span class="maleInfo" style="margin-left:0px">Maksymalna wielkość pliku do wczytania: <?php echo Funkcje::MaxUpload(); ?> Mb</span>
                
                    <div class="ostrzezenie">Każdy dodawany adres email musi być w nowym wierszu (osobnej linii).</div> <br />
                    <div class="ostrzezenie">Jeżeli w bazie będzie istniał importowany email to importowany email nie zostanie dodany.</div>
                </div>
                
                </div>
             
            </div>

            <div class="przyciski_dolne">
              <input type="submit" class="przyciskNon" value="Importuj dane" />
              <button type="button" class="przyciskNon" onclick="cofnij('newsletter_mailing','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','newsletter');">Powrót</button>           
            </div>                 


          </div>                      
          </form>

    </div>    

    <?php
    include('stopka.inc.php');

}
?>