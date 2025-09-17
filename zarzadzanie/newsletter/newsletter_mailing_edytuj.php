<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        $pola = array(
                array('mailing_email_address',$filtr->process($_POST['email'])),
        );
        
        $db->update_query('mailing' , $pola, " mailing_id = '".(int)$_POST["id"]."'");
        unset($pola);         
        
        //
        Funkcje::PrzekierowanieURL('newsletter_mailing.php?id_poz='.(int)$_POST["id"]);
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
                  remote: "ajax/sprawdz_czy_jest_mail_mailing.php?mail_id=<?php echo (int)$_GET['id_poz']; ?>"
                }            
              },
              messages: {
                email: {
                  required: "Pole jest wymagane.",
                  email: "Wpisano niepoprawny adres e-mail.",
                  remote: "Taki adres jest już w bazie mailingu."
                }      
              }
            });
          });
          </script>        

          <form action="newsletter/newsletter_mailing_edytuj.php" method="post" id="newsForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Edycja danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select * from mailing where mailing_id = '" . (int)$_GET['id_poz'] . "'";
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
                      <input type="text" name="email" id="email" value="<?php echo $info['mailing_email_address']; ?>" size="55" />
                    </p>                    

                    </div>
                 
                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Zapisz dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('newsletter_mailing','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','newsletter');">Powrót</button>           
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