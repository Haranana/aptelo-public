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
                array('blacklist_customers_id',(int)$_POST["id_klienta"]),
                array('blacklist_company',strtolower((string)$filtr->process($_POST["firma"]))),
                array('blacklist_nip',strtolower((string)$filtr->process($_POST["nip"]))),
                array('blacklist_firstname',strtolower((string)$filtr->process($_POST["imie"]))),
                array('blacklist_lastname',strtolower((string)$filtr->process($_POST["nazwisko"]))),
                array('blacklist_street_address',strtolower((string)$filtr->process($_POST["adres"]))),
                array('blacklist_postcode',strtolower((string)$filtr->process($_POST["kod_pocztowy"]))),
                array('blacklist_city',strtolower((string)$filtr->process($_POST["miasto"]))),
                array('blacklist_ip',$filtr->process($_POST["ip"])),
                array('blacklist_email_address',strtolower((string)$filtr->process($_POST["email"]))),
                array('blacklist_telephone',strtolower((string)$filtr->process($_POST["telefon"]))));
                
        //	
        $db->insert_query('blacklist', $pola);	
        
        unset($pola);
        
        $pola = array(array('customers_black_list',1));      
        
        $db->update_query('customers', $pola, 'customers_id = ' . (int)$_POST["id_klienta"]);	
        
        unset($pola);
               
        //
        Funkcje::PrzekierowanieURL('klienci.php?id_poz=' . (int)$_POST["id_klienta"]);

    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Dodawanie pozycji</div>
    <div id="cont">
    
        <script>
        $(document).ready(function() {
          $("#klienciForm").validate({
            rules: {           
              email: {
                required: true
              }              
            },
            messages: {          
              email: {
                required: "Pole jest wymagane."
              }              
            }
          });
        });
        </script>      
          
        <form action="klienci/klienci_czarna_lista_dodaj.php" method="post" id="klienciForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Dodawanie danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "SELECT * FROM customers c, address_book ab WHERE c.customers_id = '" . (int)$_GET['id_poz'] . "' and c.customers_id = ab.customers_id and c.customers_default_address_id = ab.address_book_id";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
            
                $info = $sql->fetch_assoc();
                ?>            
            
                <div class="pozycja_edytowana">
                
                    <div class="info_content">
                
                        <input type="hidden" name="akcja" value="zapisz" />
                        
                        <input type="hidden" name="id_klienta" value="<?php echo (int)$_GET['id_poz']; ?>" />
                        
                        <p>
                          <label>Imię klienta:</label>
                          <input type="text" name="imie" id="imie" size="60" value="<?php echo $info['customers_firstname']; ?>" />
                        </p>                  

                        <p>
                          <label>Nazwisko klienta:</label>
                          <input type="text" name="nazwisko" id="nazwisko" size="60" value="<?php echo $info['customers_lastname']; ?>" />
                        </p>
                        
                        <p>
                          <label>Firma:</label>
                          <input type="text" name="firma" size="60" value="<?php echo $info['entry_company']; ?>" />
                        </p>

                        <p>
                          <label>NIP:</label>
                          <input type="text" name="nip" size="30" value="<?php echo str_replace(array('(',')','+','-',' '), '', (string)$info['entry_nip']); ?>" /> <em class="TipIkona"><b>Zalecamy usunięcie znaków spacji, myślników - pozostawienie samych cyfr</b></em>
                        </p>    

                        <p>
                          <label>Ulica i numer domu:</label>
                          <input type="text" name="adres" size="30" value="<?php echo $info['entry_street_address']; ?>" /> <em class="TipIkona"><b>Zalecamy usunięcie znaków spacji, myślników, skrótów ul. etc</b></em>
                        </p>  

                        <p>
                          <label>Kod pocztowy:</label>
                          <input type="text" name="kod_pocztowy" size="10" value="<?php echo str_replace(array('(',')','+','-',' '), '', (string)$info['entry_postcode']); ?>" /> <em class="TipIkona"><b>Zalecamy usunięcie znaków spacji, myślników - pozostawienie samych cyfr</b></em>
                        </p> 
                        
                        <p>
                          <label>Miasto:</label>
                          <input type="text" name="miasto" size="60" value="<?php echo $info['entry_city']; ?>" />
                        </p>                        

                        <p>
                          <label class="required">Adres email:</label>
                          <input type="text" name="email" id="email" size="60" value="<?php echo $info['customers_email_address']; ?>" class="required" />
                        </p>                        
                        
                        <p>
                          <label>Nr telefonu:</label>
                          <input type="text" name="telefon" size="15" value="<?php echo str_replace(array('(',')','+','-',' '), '', (string)$info['customers_telephone']); ?>" /> <em class="TipIkona"><b>Zalecamy usunięcie znaków spacji, myślników - pozostawienie samych cyfr</b></em>
                        </p> 

                        <p>
                          <label>Adres IP klienta:</label>
                          <input type="text" name="ip" size="25" value="<?php echo $info['customers_ip']; ?>" />
                        </p>                          

                    </div>

                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Zapisz dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('klienci','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','klienci');">Powrót</button>   
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
