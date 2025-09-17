<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        // ustala czy mail jest w bazie klientow - jezeli tak to wstawi id klienta
        $sqlData = $db->open_query("select customers_id from customers where customers_email_address = '".$filtr->process($_POST["email"])."'");
        $infoData = $sqlData->fetch_assoc();
        $db->close_query($sqlData);
        //
        $pola = array(
                array('customers_id',((isset($infoData['customers_id'])) ? (int)$infoData['customers_id'] : 0)),
                array('subscribers_email_address',$filtr->process($_POST['email'])),
                array('date_added','now()'),
                array('date_account_accept','now()'),
                array('customers_newsletter','1')
        );
        
        $sql = $db->insert_query('subscribers' , $pola);
        unset($pola);         
     
        $id_dodanej_pozycji = $db->last_id_query();
        
        if (isset($infoData['customers_id']) && (int)$infoData['customers_id'] > 0) {
            //
            // wlaczanie newslettera w kliencie jezeli pozycja to klient
            $pola = array(
                    array('customers_newsletter','1')
            );
            $db->update_query('customers' , $pola, " customers_email_address = '".$filtr->process($_POST["email"])."'");	
            unset($pola);   
        }
        
        //
        if (isset($id_dodanej_pozycji) && $id_dodanej_pozycji > 0) {
            Funkcje::PrzekierowanieURL('newsletter_subskrybenci.php?id_poz='.$id_dodanej_pozycji);
        } else {
            Funkcje::PrzekierowanieURL('newsletter_subskrybenci.php');
        }
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Dodawanie pozycji</div>
    <div id="cont">
          
          <script>
          $(document).ready(function() {
            $("#newsForm").validate({
              rules: {
                email: {
                  required: true,
                  email: true,
                  remote: "ajax/sprawdz_czy_jest_mail_newsletter.php"
                }            
              },
              messages: {
                email: {
                  required: "Pole jest wymagane.",
                  email: "Wpisano niepoprawny adres e-mail.",
                  remote: "Taki adres jest już używany."
                }      
              }
            });
          });
          </script>         

          <form action="newsletter/newsletter_subskrybenci_dodaj.php" method="post" id="newsForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Dodawanie danych</div>
            
            <div class="pozycja_edytowana">
            
                <div class="info_content">
            
                <input type="hidden" name="akcja" value="zapisz" />

                <p>
                  <label class="required" for="email">Adres email:</label>
                  <input type="text" name="email" id="email" value="" size="55" />
                </p>
                
                </div>
                
            </div>

            <div class="przyciski_dolne">
              <input type="submit" class="przyciskNon" value="Zapisz dane" />
              <button type="button" class="przyciskNon" onclick="cofnij('newsletter_subskrybenci','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','newsletter');">Powrót</button>   
            </div>            

          </div>                      
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}