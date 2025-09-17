<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        if ($_POST["domyslny"] == '1') {
            $pola = array(array('tax_default','0'));
            $db->update_query('tax_rates' , $pola);	        
        }
        //    
        $pola = array(
                array('tax_rate',(float)$_POST["wartosc"]),
                array('tax_default',(int)$_POST["domyslny"]),
                array('tax_description',$filtr->process($_POST["opis"])),
                array('tax_short_description',$filtr->process($_POST["opis_skrocony"])),
                array('sort_order',(int)$_POST["kolejnosc"]),
                );
        //			
        $db->update_query('tax_rates' , $pola, " tax_rates_id = '".(int)$_POST["id"]."'");	
        unset($pola);
        //
        Funkcje::PrzekierowanieURL('podatek_vat.php?id_poz='.(int)$_POST["id"]);
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
                kolejnosc: {
                  required: true,
                  range: [0, 100]
                },
                wartosc: {
                  required: true,
                  range: [0, 100]
                },
                opis: {
                  required: true
                },
                opis_skrocony: {
                  required: true
                }                 
              },
              messages: {
                wartosc: {
                  required: "Pole jest wymagane."
                },
                kolejnosc: {
                  required: "Pole jest wymagane."
                },
                opis: {
                  required: "Pole jest wymagane."
                },                 
                opis_skrocony: {
                  required: "Pole jest wymagane."
                }                 
              }
            });
          });
          </script>        

          <form action="slowniki/podatek_vat_edytuj.php" method="post" id="slownikForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Edycja danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select * from tax_rates where tax_rates_id = '" . (int)$_GET['id_poz'] . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
            
                $info = $sql->fetch_assoc();
                ?>
            
                <div class="pozycja_edytowana">
                    
                    <div class="info_content">
                
                    <input type="hidden" name="akcja" value="zapisz" />
                
                    <input type="hidden" name="id" value="<?php echo (int)$_GET['id_poz']; ?>" />
                    
                    <div style="margin:8px 8px 15px 8px">
                        <span class="ostrzezenie">Jeżeli zostanie zmieniona wartość procentowa - ceny w sklepie nie ulegną zmianie ! Należy dokonać przeliczenia cen w menu Narzedzia / Masowa zmiana parametrów produktów</span>
                    </div>

                    <p>
                      <label class="required" for="wartosc">Wartość w %:</label>
                      <input type="text" name="wartosc" id="wartosc" value="<?php echo $info['tax_rate']; ?>" size="5" class="kropka" /><em class="TipIkona"><b>liczba z zakresu 0 do 100</b></em>
                    </p>
                    
                    <p>
                      <label class="required" for="kolejnosc">Kolejność:</label>
                      <input type="text" name="kolejnosc" id="kolejnosc" value="<?php echo $info['sort_order']; ?>" size="5" /><em class="TipIkona"><b>liczba z zakresu 0 do 100</b></em>
                    </p>

                    <p>
                      <label class="required" for="opis">Opis:</label>
                      <input type="text" name="opis" id="opis" value="<?php echo $info['tax_description']; ?>" size="15" />
                    </p>
                    
                    <p>
                      <label class="required" for="opis_skrocony">Opis skrócony:</label>
                      <input type="text" name="opis_skrocony" id="opis_skrocony" value="<?php echo $info['tax_short_description']; ?>" size="15" />
                    </p>

                    <?php if ($info['tax_default'] == '0') { ?>
                    
                    <p>
                      <label>Czy podatek jest domyślnym:</label>
                      <input type="radio" value="0" name="domyslny" id="domyslny_nie" checked="checked" /><label class="OpisFor" for="domyslny_nie">nie</label>
                      <input type="radio" value="1" name="domyslny" id="domyslny_tak" /><label class="OpisFor" for="domyslny_tak">tak</label>   
                    </p>
                    
                    <?php } else { ?>
                    
                    <input type="hidden" name="domyslny" value="1" />
                    
                    <?php } ?>
                    
                    </div>
                 
                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Zapisz dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('podatek_vat','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','slowniki');">Powrót</button>           
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