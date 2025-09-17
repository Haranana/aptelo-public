<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        $id_klienta = (int)$_POST['id_poz'];
        //
        $pola = array(array('customers_id',$id_klienta),
                      array('points',(int)$_POST['pkt']),
                      array('points_status',(int)$_POST['status']),
                      array('points_comment',$filtr->process($_POST['nazwa'])),
                      array('date_added','now()'),
                      array('points_type','PM'));
        
        // jezeli status anulowane lub zatwierdzone
        if ( (int)$_POST['status'] == 2 || (int)$_POST['status'] == 3 ) {
            $pola[] = array('date_confirm','now()');
        }
        //	
        $sql = $db->insert_query('customers_points', $pola);
        unset($pola);        
        
        // czy ma zmienic ogolna ilosc punktow klienta
        if ($_POST['tryb'] == '1' || $_POST['tryb'] == '0') {
            //
            // ile klient ma punktow
            $zapytanie = "select distinct customers_shopping_points from customers where customers_id = '".$id_klienta."'";
            $sqlc = $db->open_query($zapytanie);       
            $info = $sqlc->fetch_assoc();
            $IleMaPkt = $info['customers_shopping_points'];
            $db->close_query($sqlc);
            unset($info, $zapytanie);            
            //
            if ( $_POST['tryb'] == '1' ) {
                $LiczbaPkt = (int)$IleMaPkt + (int)$_POST['pkt'];
            } elseif ( $_POST['tryb'] == '0' ) {
                $LiczbaPkt = (int)$IleMaPkt - (int)$_POST['pkt'];
            }
            if ($LiczbaPkt < 0) {
                $LiczbaPkt = 0;
            }
            //
            $pola = array(array('customers_shopping_points', $LiczbaPkt));
            //	
            $sql = $db->update_query('customers', $pola, 'customers_id = ' . $id_klienta);
            unset($pola, $LiczbaPkt);            
            //
        }        
        
        // jezeli ma wyslac do klienta maila
        if (isset($_POST['mail']) && $_POST['mail'] == 'tak') {  
        
            $zapytanie_tresc = "SELECT t.sender_name, t.email_var_id, t.sender_email, t.dw, t.template_id, t.email_file, tz.email_title, tz.description, tz.description_sms FROM email_text t LEFT JOIN email_text_description tz ON tz.email_text_id = t.email_text_id AND tz.language_id = '".(int)$_POST["jezyk"]."' WHERE t.email_var_id = 'EMAIL_DODANIE_RECZNE_PUNKTOW'";
            $sql = $db->open_query($zapytanie_tresc);
            $tresc = $sql->fetch_assoc();        
        
            $zapytanie_klient = "SELECT * FROM customers WHERE customers_id = '".$id_klienta."'";
            $sql_klient = $db->open_query($zapytanie_klient);
            $info_klient = $sql_klient->fetch_assoc();       

            define('STATUS_PUNKTOW', Klienci::pokazNazweStatusuPunktow( (int)$_POST['status'], (int)$_POST["jezyk"] ));
            define('ILOSC_PUNKTOW', (int)$_POST['pkt'] );
            define('OGOLNA_ILOSC_PUNKTOW', $info_klient['customers_shopping_points'] );
            define('KOMENTARZ', $filtr->process($_POST['komentarz']));  

            $email = new Mailing;

            if ( $tresc['email_file'] != '' ) {
                $tablicaZalacznikow = explode(';', (string)$tresc['email_file']);
            } else {
                $tablicaZalacznikow = array();
            }

            $nadawca_email   = Funkcje::parsujZmienne($tresc['sender_email']);
            $nadawca_nazwa   = Funkcje::parsujZmienne($tresc['sender_name']);
            $cc              = Funkcje::parsujZmienne($tresc['dw']);

            $adresat_email   = $info_klient['customers_email_address'];
            $adresat_nazwa   = $info_klient['customers_firstname'] . ' ' . $info_klient['customers_lastname'];

            $temat           = Funkcje::parsujZmienne($tresc['email_title']);
            $tekst           = $tresc['description'];
            $zalaczniki      = $tablicaZalacznikow;
            $szablon         = $tresc['template_id'];
            $jezyk           = (int)$_POST["jezyk"];


            $tekst = Funkcje::parsujZmienne($tekst);
            $tekst = preg_replace('#(<br */?>\s*)+#i', '<br /><br />', (string)$tekst);

            $email->wyslijEmail($nadawca_email,$nadawca_nazwa,$adresat_email, $adresat_nazwa, $cc, $temat, $tekst, $szablon, $jezyk, $zalaczniki, false);
            
            $db->close_query($sql_klient);
            $db->close_query($sql);
            unset($zapytanie_klient, $info_klient, $tresc, $zapytanie_tresc);             

        }
        
        Funkcje::PrzekierowanieURL('partnerzy_operacje.php?id_poz='.(int)$id_klienta);

    }   

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Dodawanie pozycji</div>
    <div id="cont">
          
          <script>
          $(document).ready(function() {
            $("#punktyForm").validate({
              rules: {
                nazwa: {
                  required: true
                },
                pkt: {
                  required: true,
                  range: [1, 100000],
                  number: true
                } 
              },
              messages: {
                nazwa: {
                  required: "Pole jest wymagane."
                },
                pkt: {
                  required: "Pole jest wymagane."
                }                 
              }
            });
          });
          </script>     

          <form action="program_partnerski/partnerzy_operacje_dodaj.php<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>" method="post" id="punktyForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Dodawanie danych</div>
            
            <div class="pozycja_edytowana">
            
                <div class="info_content">
            
                <input type="hidden" name="akcja" value="zapisz" />
                
                <input type="hidden" name="id_poz" value="<?php echo (int)$_GET['id_poz']; ?>" />
                
                <p>
                    <label class="required" for="nazwa">Tytuł punktów:</label>
                    <input type="text" name="nazwa" size="45" value="" id="nazwa" />
                </p> 
                            
                <p>
                    <label class="required" for="pkt">Ilość punktów:</label>
                    <input type="text" name="pkt" value="" size="8" class="calkowita" id="pkt" />
                </p>                

                <p>
                    <label for="status">Status punktów:</label>
                    <?php        
                    echo Funkcje::RozwijaneMenu('status', Klienci::ListaStatusowPunktow(false), '', 'id="status"')
                    ?>                        
                </p>     

                <p>
                    <label>Zmiana punktów klienta:</label>
                    <input type="radio" value="1" name="tryb" id="tryb_dodaj" checked="checked" /><label class="OpisFor" for="tryb_dodaj">dodaj punkty klientowi<em class="TipIkona"><b>Ogólna ilość punktów klienta zostanie zmieniona</b></em></label>
                    <input type="radio" value="0" name="tryb" id="tryb_odejmij" /><label class="OpisFor" for="tryb_odejmij">odejmij punkty klientowi<em class="TipIkona"><b>Ogólna ilość punktów klienta zostanie zmieniona</b></em></label>
                    <input type="radio" value="2" name="tryb" id="tryb_bezzmian" /><label class="OpisFor" for="tryb_bezzmian">nie dodawaj punktów<em class="TipIkona"><b>Ogólna ilość punktów klienta pozostanie bez zmian</b></em></label>
                </p>
                
                <hr style="color:#82b4cd;border-top: 1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:96%;" />
                
                <p>
                  <label for="mail">Poinformuj klienta e-mail:</label>
                  <input type="checkbox" checked="checked" value="tak" name="mail" id="mail" />
                  <label class="OpisForPustyLabel" for="mail"></label>
                </p>                    
                
                <p>
                  <label>W jakim języku wysłać email:</label>
                  <?php
                  echo Funkcje::RadioListaJezykow();
                  ?>
                </p>  

                <p>
                  <label>Komentarz:</label>
                  <textarea cols="100" rows="10" name="komentarz" class="wysiwyg"></textarea>
                </p>                  
                
                </div>             
               
            </div>

            <div class="przyciski_dolne">
              <input type="submit" class="przyciskNon" value="Zapisz dane" />
              <button type="button" class="przyciskNon" onclick="cofnij('partnerzy_operacje','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','program_partnerski');">Powrót</button>   
            </div>            

          </div>                      
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}
