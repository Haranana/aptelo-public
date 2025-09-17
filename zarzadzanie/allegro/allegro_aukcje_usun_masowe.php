<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        
        $warunki_szukania = '';
        
        if ( isset($_POST['data_od']) && $_POST['data_od'] != '' ) {
            //     
            $szukana_wartosc = date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_POST['data_od'] . ' 00:00:00')));
            $warunki_szukania .= " and ap.auction_date_start >= '".$szukana_wartosc."'";
            unset($szukana_wartosc);
            //
        }
        
        if ( isset($_POST['data_do']) && $_POST['data_do'] != '' ) {
            //     
            $szukana_wartosc = date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_POST['data_do'] . ' 23:59:59')));
            $warunki_szukania .= " and ap.auction_date_end <= '".$szukana_wartosc."'";
            unset($szukana_wartosc);
            //
        }     

        if ( isset($_POST['szukaj_status']) && $_POST['szukaj_status'] != '0' && $_POST['szukaj_status'] != 'ARCHIVE' ) {
             //
             $szukana_wartosc = $filtr->process($_POST['szukaj_status']);
             $warunki_szukania .= " and ap.auction_status = '".$szukana_wartosc."' and ap.archiwum_allegro != '1'";
             unset($szukana_wartosc);
             //
        }
        if ( isset($_POST['szukaj_status']) && $_POST['szukaj_status'] != '0' && $_POST['szukaj_status'] == 'ARCHIVE' ) {
             //
             $szukana_wartosc = $filtr->process($_POST['szukaj_status']);
             $warunki_szukania .= " and ap.archiwum_allegro = '1'";
             unset($szukana_wartosc);
             //
        }
        
        if ( $warunki_szukania != '' ) {
             //
             $warunki_szukania = preg_replace('/and/i', 'WHERE', $warunki_szukania, 1);
             //
        }        

        $zapytanie = "SELECT ap.allegro_id, ap.auction_id, ap.products_id, ap.products_buy_now_price, ap.products_stock_attributes, ap.external_id
                        FROM allegro_auctions ap " . $warunki_szukania . "
                    GROUP BY ap.auction_id";
                       
        $sql = $db->open_query($zapytanie);
        
        $ile_usunieto = 0;

        if ((int)$db->ile_rekordow($sql) > 0) {
          
            while ( $info = $sql->fetch_assoc() ) {
              
                $db->delete_query('allegro_auctions' , " allegro_id = '" . (int)$info['allegro_id'] . "'");  

                $ile_usunieto++;
                
            }
            
            $db->close_query($sql);
            
        }

        Funkcje::PrzekierowanieURL('allegro_aukcje_usun_masowe.php?skasowane=' . $ile_usunieto);
        
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Kasowanie pozycji</div>
    <div id="cont">
          
        <form action="allegro/allegro_aukcje_usun_masowe.php" method="post" class="cmxform" id="usunAllegro">          

        <div class="poleForm">
          <div class="naglowek">Masowe usuwanie aukcji z bazy sklepu</div>
          
          <div class="pozycja_edytowana">
          
              <div class="info_content">
              
                  <?php if ( !isset($_GET['skasowane']) ) { ?>
          
                  <script>
                  $(document).ready(function() {  

                      $('input.datepicker').Zebra_DatePicker({
                         format: 'd-m-Y',
                         inside: false,
                         readonly_element: true
                      });       

                  })
                  </script>
              
                  <input type="hidden" name="akcja" value="zapisz" />
                  
                  <div class="maleInfo">Wybierz paremetry usuwanych aukcji</div>

                  <p>
                    <label for="data_od">Data rozpoczęcia aukcji:</label>
                    <input type="text" name="data_od" id="data_od" value="" size="20" class="datepicker" />                                        
                  </p>

                  <p>
                    <label for="data_do">Data zakończenia aukcji:</label>
                    <input type="text" name="data_do" id="data_do" value="" size="20" class="datepicker" />                  
                  </p>           
                  
                  <p>
                    <label for="zamowienie_typ">Status aukcji:</label>
                    <?php
                    $tablica_status = Array();
                    $tablica_status[] = array('id' => '0', 'text' => '-- dowolny --');
                    $tablica_status[] = array('id' => 'ACTIVE', 'text' => 'trwająca');
                    $tablica_status[] = array('id' => 'ENDED', 'text' => 'zakończona');
                    $tablica_status[] = array('id' => 'ACTIVATING', 'text' => 'oczekująca');
                    $tablica_status[] = array('id' => 'ARCHIVE', 'text' => 'archiwum');
                    $tablica_status[] = array('id' => 'NOT_FOUND', 'text' => 'nie znalezione w Allegro');
                    //
                    echo Funkcje::RozwijaneMenu('szukaj_status', $tablica_status, '', ' style="max-width:300px"'); 
                    unset($tablica_status);
                    //
                    ?>                    
                  </p>     

                  <?php } else { ?>
                  
                  <div class="maleInfo">Usunięto aukcji: <?php echo (int)$_GET['skasowane']; ?></div>
                  
                  <?php } ?>
                  
              </div>
              
          </div>
          
          <?php if ( !isset($_GET['skasowane']) ) { ?>
          
          <div class="ostrzezenie" style="margin:15px">Operacja usunięcia jest nieodracalna ! Aukcje po usunięciu nie będzie można przywrócić ! <br /> Usuwane są tylko wpisy w bazie danych sklepu. W Allegro nie jest nic usuwane ani nie jest zmieniany status aukcji.</div>
          
          <?php } ?>

          <div class="przyciski_dolne">
            <?php if ( !isset($_GET['skasowane']) ) { ?>
            <input type="submit" class="przyciskNon" value="Usuń dane" />
            <?php } ?>
            <button type="button" class="przyciskNon" onclick="cofnij('allegro_aukcje','<?php echo Funkcje::Zwroc_Get(array('x','y')); ?>','allegro');">Powrót</button> 
          </div>

        </div>                      
        </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}