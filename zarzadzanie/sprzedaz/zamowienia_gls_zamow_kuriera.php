<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

  $apiKurier = new GlsApi();

  ?>

    <script>
        $(document).ready(function() {

            $('input.datepicker').Zebra_DatePicker({
                format: 'Y-m-d',
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
                    <input type="text" id="data_odbioru" name="data_odbioru" value="<?php echo date('Y-m-d',time()+3600*24); ?>" size="20"  class="datepicker" />&nbsp;
                </p>        
                                
                <p>
                
                    <label>Email potwierdzający dla zlecającego (na adres zarejestrowany w systemie):</label>
                    
                    <input type="radio" name="powiadomienie" id="powiadomienie_1" value="1" checked="checked"><label class="OpisFor" for="powiadomienie_1">tak</label>
                    <input type="radio" name="powiadomienie" id="powiadomienie_0" value="0"><label class="OpisFor" for="powiadomienie_0">nie</label>
                    
                </p>

            </div>

        </div>
        
    </div>

  <?php
  }
?>