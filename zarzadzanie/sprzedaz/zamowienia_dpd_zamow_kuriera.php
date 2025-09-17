<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

  $apiKurier = new DpdApi('', 'obj');

  ?>

    <script>
        $(document).ready(function() {

            $('input.datepicker').Zebra_DatePicker({
                format: 'd-m-Y',
                inside: false,
                readonly_element: false
            });                
                            
        });
    </script>  

    
    <div class="EdycjaOdstep">

        <div class="pozycja_edytowana">

            <div class="info_content">

                                <input type="hidden" name="akcja" value="zapisz" />

                                <p>
                                  <label>Data odbioru:</label>
                                  <input type="text" id="data_odbioru" name="data_odbioru" value="<?php echo date('d-m-Y',time()+3600*24); ?>" size="20"  class="datepicker" />&nbsp;
                                </p>        
                                
                                <p>
                                  <label>Godziny odbioru:</label>
                                  <?php
                                  $DostepneGodziny = array();
                                  $tablica = $apiKurier->getCourierOrderAvailability($_GET['fid']);
                                  if ( is_array($tablica) ) {
                                      foreach ( $tablica as $key => $value ) {
                                        $DostepneGodziny[] = array('id' => $value, 'text' => $value);
                                      }
                                      echo Funkcje::RozwijaneMenu('godziny_odbioru', $DostepneGodziny, '', 'style="width:150px;" id="cena"');
                                  } else {
                                      echo $tablica;
                                  }
                                  ?>
                                </p>        
                                 

            </div>

        </div>
        
    </div>

  <?php
  }
?>