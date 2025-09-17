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
                array('title',$filtr->process($_POST["nazwa"])),
                array('code',$filtr->process($_POST["kod"])),
                array('symbol',$filtr->process($_POST["symbol"])),
                array('decimal_point',$filtr->process($_POST["separator"])),
                array('value',$filtr->process($_POST["przelicznik"])),
                array('currencies_marza',$filtr->process($_POST["prowizja"])),
                array('last_updated ','now()')
                );
        //	
        $db->update_query('currencies' , $pola, " currencies_id = '".(int)$_POST["id"]."'");	
        
        if ( $_SESSION['domyslna_waluta']['id'] == (int)$_POST["id"] ) {
          
            unset($_SESSION['domyslna_waluta']);

            $waluta = array('id' => (int)$_POST["id"],
                            'nazwa' => $filtr->process($_POST['nazwa']),
                            'kod' => $filtr->process($_POST['kod']),
                            'symbol' => $filtr->process($_POST['symbol']),
                            'separator' => $filtr->process($_POST['separator']),
                            'przelicznik' => (((float)$_POST['przelicznik'] == 0) ? 1 : $filtr->process($_POST['przelicznik'])),
                            'marza' => $filtr->process($_POST['prowizja']));

            $_SESSION['domyslna_waluta'] = $waluta;
            
        }
        
        // zeruje tablice walut
        unset($_SESSION['tablica_walut_kod']);
        unset($_SESSION['tablica_walut_id']);

        unset($pola);
        
        Funkcje::PrzekierowanieURL('waluty.php?id_poz='.(int)$_POST["id"]);
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Edycja pozycji</div>
    <div id="cont">
          
          <?php
          $tabAktualne = array();
          //
          $zapytanie = "select currencies_id, code from currencies where currencies_id != '" . (int)$_GET['id_poz'] . "'";
          $sql = $db->open_query($zapytanie);         
          while ($wynik = $sql->fetch_assoc()) {
              $tabAktualne[] = $wynik['code'];
          }
          $db->close_query($sql);
          unset($wynik,$zapytanie);          
          ?>
          
          <script>
          jQuery.validator.addMethod("notEqual", function(value, element, params) {
            pod = params.split('|');
            wynik = true;
            for (x = 0; x < pod.length; x++) {
                 if ( value == pod[x] ) {
                      wynik = false;
                 }
            }
            return wynik;
          }, "Taka wartość jest już używana."); 
          
          $(document).ready(function() {
            $("#slownikForm").validate({
              rules: {
                nazwa: {
                  required: true
                },
                kod: {
                  required: true,
                  notEqual: "<?php echo implode('|', (array)$tabAktualne); ?>"
                },
                symbol: {
                  required: true
                },
                przelicznik: {
                  required: true,
                  min: 0.0001
                }                
              },
              messages: {
                nazwa: {
                  required: "Pole jest wymagane."
                },
                kod: {
                  required: "Pole jest wymagane."
                },
                symbol: {
                  required: "Pole jest wymagane."
                },
                przelicznik: {
                  required: "Pole jest wymagane."
                }                 
              }
            });
          });
          </script>     
          
          <?php
          unset($tabAktualne);
          ?>          

          <form action="slowniki/waluty_edytuj.php" method="post" id="slownikForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Edycja danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select * from currencies where currencies_id = '" . (int)$_GET['id_poz'] . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
            
                $info = $sql->fetch_assoc();
                ?>            
            
                <div class="pozycja_edytowana">
                
                    <div class="info_content">
                
                    <input type="hidden" name="akcja" value="zapisz" />
                    
                    <input type="hidden" name="id" value="<?php echo (int)$_GET['id_poz']; ?>" />
                    
                    <p>
                      <label class="required" for="nazwa">Nazwa:</label>
                      <input type="text" name="nazwa" id="nazwa" value="<?php echo $info['title']; ?>" size="35" />
                    </p>

                    <p>
                      <label class="required" for="kod">Kod:</label>
                      <?php if ( $info['currencies_id'] != '1' ) { ?>
                         <input type="text" name="kod" id="kod" value="<?php echo $info['code']; ?>" size="5" /><em class="TipIkona"><b>kod waluty wg tabeli NBP</b></em>
                        <?php } else { ?>
                         <input type="text" name="kod" id="kod" value="<?php echo $info['code']; ?>" size="5" disabled="disabled" /><em class="TipIkona"><b>kod waluty wg tabeli NBP</b></em>
                         <input type="hidden" name="kod" value="<?php echo $info['code']; ?>" />
                      <?php } ?>                          
                    </p>

                    <p>
                      <label class="required" for="symbol">Symbol:</label>
                      <input type="text" name="symbol" id="symbol" value="<?php echo $info['symbol']; ?>" size="5" /><em class="TipIkona"><b>symbol waluty wyświetlany w sklepie</b></em>
                    </p>

                    <p>
                      <label>Separator dziesiętny:</label>
                      <input type="radio" value="." name="separator" id="separator_kropka" <?php echo (($info['decimal_point'] == '.') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="separator_kropka">. (kropka)</label>
                      <input type="radio" value="," name="separator" id="separator_przecinek" <?php echo (($info['decimal_point'] == ',') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="separator_przecinek">, (przecinek)</label>
                    </p>

                    <p>
                      <label class="required" for="przelicznik">Przelicznik:</label>
                      <input type="text" name="przelicznik" id="przelicznik" value="<?php echo $info['value']; ?>" size="15" /><em class="TipIkona"><b>przelicznik w stosunku do waluty domyślnej</b></em>
                    </p>                 

                    <p>
                      <label for="prowizja">Prowizja na walucie:</label>
                      <input type="text" name="prowizja" id="prowizja" value="<?php echo $info['currencies_marza']; ?>" size="5" /> % <em class="TipIkona"><b>wartość procentowa doliczana do kursu waluty - od 1 do 99</b></em>
                    </p>                

                    </div>
                    
                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Zapisz dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('waluty','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','slowniki');">Powrót</button>   
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