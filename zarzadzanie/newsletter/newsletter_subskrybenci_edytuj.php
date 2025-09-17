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
                array('customers_id',(int)$infoData['customers_id']),
                array('subscribers_email_address',$filtr->process($_POST['email'])),
        );
        
        $db->update_query('subscribers' , $pola, " subscribers_id = '".(int)$_POST["id"]."'");
        unset($pola); 

        if ((int)$infoData['customers_id'] > 0) {
            //
            // wlaczanie newslettera w kliencie jezeli pozycja to klient
            $pola = array(
                    array('customers_newsletter','1')
            );
            $db->update_query('customers' , $pola, " customers_email_address = '".$filtr->process($_POST["email"])."'");	
            unset($pola);   
        }
        
        //
        Funkcje::PrzekierowanieURL('newsletter_subskrybenci.php?id_poz='.(int)$_POST["id"]);
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Edycja pozycji</div>
    <div id="cont">
          
          <script>
          $(document).ready(function() {
            $("#newsForm").validate({
              rules: {
                email: {
                  required: true,
                  email: true,
                  remote: "ajax/sprawdz_czy_jest_mail_newsletter.php?mail_id=<?php echo (int)$_GET['id_poz']; ?>"
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

          <form action="newsletter/newsletter_subskrybenci_edytuj.php" method="post" id="newsForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Edycja danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select * from subscribers where subscribers_id = '" . (int)$_GET['id_poz'] . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
            
                $info = $sql->fetch_assoc();
                ?>
            
                <div class="pozycja_edytowana">
                    
                    <div class="info_content">
                
                    <input type="hidden" name="akcja" value="zapisz" />
                
                    <input type="hidden" name="id" value="<?php echo (int)$_GET['id_poz']; ?>" />
                    
                    <p>
                      <label class="required" for="email">Adres email:</label>
                      <input type="text" name="email" id="email" value="<?php echo $info['subscribers_email_address']; ?>" size="55" />
                    </p>                    

                    </div>
                 
                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Zapisz dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('newsletter_subskrybenci','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','newsletter');">Powrót</button>           
                </div>                 

            <?php
            
            $db->close_query($sql);
            unset($info);            
            
            } else {
            
                echo '<div class="pozycja_edytowana">Brak danych do wyświetlenia</div>';
            
            }
            ?>

          </div>                      
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}