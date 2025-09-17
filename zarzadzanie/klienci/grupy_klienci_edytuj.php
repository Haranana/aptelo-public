<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        $pola = array(
                array('customers_groups_name',$filtr->process($_POST["nazwa"])),
                array('customers_groups_discount',(float)$_POST["rabat"]),
                array('customers_groups_price',(int)$_POST["cena"]),
                array('customers_groups_min_amount',(float)$_POST["wartosc"]),
                array('customers_groups_description',$filtr->process($_POST["opis"]))
        );
        //			
        $db->update_query('customers_groups' , $pola, " customers_groups_id = '".(int)$_POST["id"]."'");	
        unset($pola);
        //
        Funkcje::PrzekierowanieURL('grupy_klienci.php?id_poz='.(int)$_POST["id"]);
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Edycja pozycji</div>
    <div id="cont">
          
          <script>
          $(document).ready(function() {
            $("#slownikForm").validate({
              rules: {
                nazwa: {
                  required: true
                },
                rabat: {
                  range: [-100, 0],
                  number: true
                },
                wartosc: {
                  range: [0, 999999],
                  number: true
                }
              },
              messages: {
                nazwa: {
                  required: "Pole jest wymagane."
                }
              }
            });
          });
          </script>        

          <form action="klienci/grupy_klienci_edytuj.php" method="post" id="slownikForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Edycja danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select * from customers_groups where customers_groups_id = '" . (int)$_GET['id_poz'] . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
            
                $info = $sql->fetch_assoc();
                ?>
            
                <div class="pozycja_edytowana">
                    
                    <div class="info_content">
                
                    <input type="hidden" name="akcja" value="zapisz" />
                
                    <input type="hidden" name="id" value="<?php echo (int)$_GET['id_poz']; ?>" />
                    
                    <p>
                      <label class="required" for="nazwa">Nazwa grupy:</label>
                      <input type="text" name="nazwa" id="nazwa" value="<?php echo $info['customers_groups_name']; ?>" size="53" /><em class="TipIkona"><b>Nazwa identyfikująca grupę dla obsługi sklepu</b></em>
                    </p>   

                    <p>
                      <label for="rabat">Rabat [%]:</label>
                      <input type="text" name="rabat" id="rabat" value="<?php echo $info['customers_groups_discount']; ?>" size="5" /><em class="TipIkona"><b>liczba z zakresu -100 do 0</b></em>
                    </p>
                    
                    <p>
                      <label for="cena">Grupa cenowa:</label>
                        <?php
                        $tablica = array();
                        for ($x = 1; $x <= ILOSC_CEN; $x++) {
                          $tablica[] = array('id' => $x, 'text' => 'Cena nr ' . $x);
                        }
                        ?>                                          
                        <?php echo Funkcje::RozwijaneMenu('cena', $tablica, $info['customers_groups_price'], 'style="width:100px;" id="cena"'); ?>
                    </p>

                    <p>
                      <label for="wartosc">Minimalne zamówienie:</label>
                      <input type="text" name="wartosc" id="wartosc" value="<?php echo $info['customers_groups_min_amount']; ?>" size="15" /><em class="TipIkona"><b>liczba większa lub równa 0.00</b></em>
                    </p>

                    <p>
                      <label for="opis">Opis:</label>
                      <textarea cols="50" rows="7" name="opis" id="opis" onkeyup="licznik_znakow(this,'iloscZnakow',255)"><?php echo $info['customers_groups_description']; ?></textarea>
                    </p>
                    
                    <p>
                      <label></label>
                      Ilość znaków do wpisania: <span class="iloscZnakow" id="iloscZnakow"><?php echo (255-strlen(mb_convert_encoding((string)$info['customers_groups_description'], 'ISO-8859-1', 'UTF-8'))); ?></span>
                    </p>                    

                    </div>
                 
                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Zapisz dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('grupy_klienci','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','klienci');">Powrót</button>           
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