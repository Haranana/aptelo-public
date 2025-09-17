<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //			
        $warunki_szukania = '';

        if ( isset($_POST['data_od']) && $_POST['data_od'] != '' ) {
            //     
            $szukana_wartosc = date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_POST['data_od'] . ' 00:00:00')));
            $warunki_szukania .= " and o.date_purchased >= '".$szukana_wartosc."'";
            unset($szukana_wartosc);
            //
        }
        
        if ( isset($_POST['data_do']) && $_POST['data_do'] != '' ) {
            //     
            $szukana_wartosc = date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_POST['data_do'] . ' 23:59:59')));
            $warunki_szukania .= " and o.date_purchased <= '".$szukana_wartosc."'";
            unset($szukana_wartosc);
            //
        }     

        if ( isset($_POST['zamowienie_status']) && $_POST['zamowienie_status'] != '0' ) {
            $szukana_wartosc = $filtr->process($_POST['zamowienie_status']);
            $warunki_szukania .= " and o.orders_status = '".$szukana_wartosc."'";
            unset($szukana_wartosc);
        }
        
        if ( isset($_POST['zamowienie_typ']) && (int)$_POST['zamowienie_typ'] > 0 ) {
            $szukana_wartosc = (int)$_POST['zamowienie_typ'];
            if ( $szukana_wartosc < 5 ) {
                 $warunki_szukania .= " and o.orders_source = '".$szukana_wartosc."'";
              } else if ( $szukana_wartosc == 5 ) {
                 $warunki_szukania .= " and o.orders_source != '3'";
            }
            unset($szukana_wartosc);
        }       

        if ( isset($_POST['zamowienie_rodzaj']) && (int)$_POST['zamowienie_rodzaj'] != '0' ) {
            $szukana_wartosc = (int)$_POST['zamowienie_rodzaj'];
            $warunki_szukania .= " and os.orders_status_type = '".$szukana_wartosc."'";
            unset($szukana_wartosc);
        }       
        
        if ( $warunki_szukania != '' ) {
          $warunki_szukania = preg_replace('/and/i', 'WHERE', $warunki_szukania, 1);
        }        

        $zapytanie = "SELECT o.orders_id 
                      FROM orders o
                      LEFT JOIN orders_status os ON os.orders_status_id = o.orders_status " . $warunki_szukania;         
                      
        $sql = $db->open_query($zapytanie);
        
        $ile_usunieto = 0;

        if ((int)$db->ile_rekordow($sql) > 0) {
          
            while ( $info = $sql->fetch_assoc() ) {
            
                $db->delete_query('orders' , " orders_id = '".(int)$info['orders_id']."'");  
                $db->delete_query('orders_status_history' , " orders_id = '".(int)$info['orders_id']."'");  
                $db->delete_query('orders_total' , " orders_id = '".(int)$info['orders_id']."'");  

                $db->delete_query('orders_products' , " orders_id = '".(int)$info['orders_id']."'");  
                $db->delete_query('orders_products_set' , " orders_id = '".(int)$info['orders_id']."'");  
                $db->delete_query('orders_products_attributes' , " orders_id = '".(int)$info['orders_id']."'");  
                $db->delete_query('orders_to_extra_fields' , " orders_id = '".(int)$info['orders_id']."'");  
                $db->delete_query('orders_shipping' , " orders_id = '".(int)$info['orders_id']."'");  
                $db->delete_query('orders_file_shopping' , " orders_id = '".(int)$info['orders_id']."'");  

                $db->delete_query('invoices' , " orders_id = '".(int)$info['orders_id']."'");  
                $db->delete_query('invoices_products' , " orders_id = '".(int)$info['orders_id']."'");  
                $db->delete_query('invoices_total' , " orders_id = '".(int)$info['orders_id']."'");  

                $pola = array(
                        array('orders_id','0'),
                );
                $db->update_query('allegro_transactions' , $pola, " orders_id = '".(int)$info['orders_id']."'");	
                unset($pola);
                
                $ile_usunieto++;
                
            }
            
            $db->close_query($sql);
            
        }

        Funkcje::PrzekierowanieURL('zamowienia_usun_masowe.php?skasowane=' . $ile_usunieto);
        
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Kasowanie pozycji</div>
    <div id="cont">
          
        <form action="sprzedaz/zamowienia_usun_masowe.php" method="post" class="cmxform" id="usunZam">          

        <div class="poleForm">
          <div class="naglowek">Masowe usuwanie zamówień</div>
          
          <div class="pozycja_edytowana">
          
              <div class="info_content">
              
                  <?php if ( !isset($_GET['skasowane']) ) { ?>
          
                  <script>
                  $(document).ready(function() {  

                      $("#usunZam").validate({
                        rules: {
                          data_od: {
                            required: true
                          },
                          data_do: {
                            required: true             
                          }                
                        }
                      });
            
                      $('input.datepicker').Zebra_DatePicker({
                         format: 'd-m-Y',
                         inside: false,
                         readonly_element: true
                      });       

                  })
                  </script>
              
                  <input type="hidden" name="akcja" value="zapisz" />
                  
                  <div class="maleInfo">Wybierz paremetry usuwanych zamówień</div>
                  
                  <p>
                    <label for="data_od">Data złożenia początkowa:</label>
                    <input type="text" name="data_od" id="data_od" value="" size="20" class="datepicker" />                                        
                    <label for="data_od" generated="true" class="error" style="display:none">Pole jest wymagane.</label>
                  </p>

                  <p>
                    <label for="data_do">Data złożenia końcowa:</label>
                    <input type="text" name="data_do" id="data_do" value="" size="20" class="datepicker" /> 
                    <label for="data_do" generated="true" class="error" style="display:none">Pole jest wymagane.</label>                    
                  </p>           
                  
                  <p>
                    <label for="zamowienie_typ">Rodzaj zamówienia:</label>
                    <?php
                    $tablica_typ = Array();
                    $tablica_typ = Sprzedaz::TypyZamowien( true );
                    $tablica_typ[] = array('id' => '5', 'text' => 'zamówienia bez zamówień z Allegro');
                    echo Funkcje::RozwijaneMenu('zamowienie_typ', $tablica_typ, '', ' style="max-width:300px" id="zamowienie_typ"'); ?>                    
                  </p>     

                  <p>
                    <label for="zamowienie_status">Status zamówienia:</label>
                    <?php
                    $tablica_status= array();
                    $tablica_status = Sprzedaz::ListaStatusowZamowien(true);
                    echo Funkcje::RozwijaneMenu('zamowienie_status', $tablica_status, '', ' style="max-width:300px" id="zamowienie_status"'); ?>                    
                  </p>
                  
                  <p>
                    <label for="zamowienie_rodzaj">Rodzaj zamówienia:</label>
                    <?php
                    $tablica_rodzaj = Array();
                    $tablica_rodzaj[] = array('id' => '0', 'text' => 'dowolny');
                    $tablica_rodzaj[] = array('id' => '1', 'text' => 'Nowe');
                    $tablica_rodzaj[] = array('id' => '2', 'text' => 'W realizacji');
                    $tablica_rodzaj[] = array('id' => '3', 'text' => 'Zamknięte (zrealizowane)');
                    $tablica_rodzaj[] = array('id' => '4', 'text' => 'Zamknięte (niezrealizowane)');
                    echo Funkcje::RozwijaneMenu('zamowienie_rodzaj', $tablica_rodzaj, '', ' style="max-width:300px" id="zamowienie_rodzaj"'); ?>    
                  </p>        

                  <?php } else { ?>
                  
                  <div class="maleInfo">Usunięto zamówień: <?php echo (int)$_GET['skasowane']; ?></div>
                  
                  <?php } ?>
                  
              </div>
              
          </div>
          
          <?php if ( !isset($_GET['skasowane']) ) { ?>
          
          <div class="ostrzezenie" style="margin:15px">Operacja usunięcia jest nieodracalna ! Zamówień po usunięciu nie będzie można przywrócić !</div>
          
          <?php } ?>

          <div class="przyciski_dolne">
            <?php if ( !isset($_GET['skasowane']) ) { ?>
            <input type="submit" class="przyciskNon" value="Usuń dane" />
            <?php } ?>
            <button type="button" class="przyciskNon" onclick="cofnij('zamowienia','<?php echo Funkcje::Zwroc_Get(array('x','y')); ?>','sprzedaz');">Powrót</button> 
          </div>

        </div>                      
        </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}