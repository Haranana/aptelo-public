<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        if ( isset($_POST['przywroc_ceny']) && $_POST['przywroc_ceny'] == '1' ) {
            //            
            Funkcje::AktualizujHistorieCenProduktowPromocji((int)$_POST["id"]);
            //
            // pobieranie informacji o vat - tworzy tablice ze stawkami
            $zapytanie_vat = "select distinct * from tax_rates order by tax_rate desc";
            $sqls = $db->open_query($zapytanie_vat);
            //
            $tablicaVat = array();
            while ($infs = $sqls->fetch_assoc()) { 
                $tablicaVat[$infs['tax_rates_id']] = $infs['tax_rate'];
            }
            $db->close_query($sqls);
            unset($zapytanie_vat, $infs);  
            //
            
            $pola = array(array('specials_status','0'),
                          array('products_old_price','0'),
                          array('specials_date','0000-00-00'),
                          array('specials_date_end','0000-00-00'));            
            //
            $zapytanie = "select distinct * from products where products_id = '".(int)$_POST["id"]."'";
            $sql = $db->open_query($zapytanie);    
            $info = $sql->fetch_assoc();  
            //
            $vat_id = $info['products_tax_class_id'];
            //                            
            $wartosc = $info['products_old_price'];
            $netto = round(($wartosc / (1 + ($tablicaVat[$vat_id]/100))), 2);
            $podatek = $wartosc - $netto;
            //
            $pola[] = array('products_price_tax',(float)$wartosc);
            $pola[] = array('products_price',(float)$netto);
            $pola[] = array('products_tax',(float)$podatek);  
            //
            unset($wartosc, $netto, $podatek);
            //
            // ceny dla pozostalych poziomow cen
            for ( $x = 2; $x <= ILOSC_CEN; $x++ ) {
                  //
                  // cena poprzednia
                  if ( $info['products_old_price_'.$x] > 0 ) {
                      //
                      $wartosc = $info['products_old_price_'.$x];
                      $netto = round(($wartosc / (1 + ($tablicaVat[$vat_id]/100))), 2);
                      $podatek = $wartosc - $netto;    
                      //
                      $pola[] = array('products_old_price_'.$x,'0');
                      $pola[] = array('products_price_tax_'.$x,(float)$wartosc);
                      $pola[] = array('products_price_'.$x,(float)$netto);
                      $pola[] = array('products_tax_'.$x,(float)$podatek);
                      //    
                      unset($wartosc, $netto, $podatek); 
                      //                
                  }
                  //
            }      
            $db->close_query($sql);
            unset($info);
            //
            
            $sql_wynik = $db->update_query('products' , $pola, " products_id = '".(int)$_POST["id"]."'"); 
            
            // aktualizowanie cen dla kombinacji cech
            if ( isset($_POST['ceny_cechy']) ) {

                $zapytanie = "select distinct * from products_stock where products_id = '".(int)$_POST["id"]."'";
                $sql = $db->open_query($zapytanie);   
                
                while ( $info = $sql->fetch_assoc() ) {
                    //                            
                    if ( $info['products_stock_old_price'] > 0 ) {
                      
                        $wartosc = $info['products_stock_old_price'];
                        $netto = round(($wartosc / (1 + ($tablicaVat[$vat_id]/100))), 2);
                        $podatek = $wartosc - $netto;
                        //
                        $pola = array(array('products_stock_old_price','0')); 
                        $pola[] = array('products_stock_price_tax',(float)$wartosc);
                        $pola[] = array('products_stock_price',(float)$netto);
                        $pola[] = array('products_stock_tax',(float)$podatek);  
                        //
                        unset($wartosc, $netto, $podatek);
                        //
                        // ceny dla pozostalych poziomow cen
                        for ( $x = 2; $x <= ILOSC_CEN; $x++ ) {
                              //
                              // cena poprzednia
                              if ( $info['products_stock_old_price_'.$x] > 0 ) {
                                  //
                                  $wartosc = $info['products_old_price_'.$x];
                                  $netto = round(($wartosc / (1 + ($tablicaVat[$vat_id]/100))), 2);
                                  $podatek = $wartosc - $netto;    
                                  //
                                  $pola[] = array('products_stock_old_price_'.$x,'0');
                                  $pola[] = array('products_stock_price_tax_'.$x,(float)$wartosc);
                                  $pola[] = array('products_stock_price_'.$x,(float)$netto);
                                  $pola[] = array('products_stock_tax_'.$x,(float)$podatek);
                                  //    
                                  unset($wartosc, $netto, $podatek); 
                                  //                
                              }
                              //
                        }      

                        $sql_wynik = $db->update_query('products_stock' , $pola, " products_id = '" . (int)$_POST["id"] . "' and products_stock_id = '" . (int)$info['products_stock_id'] . "'");
                        
                    }
                  
                }
                
                $db->close_query($sql);
                unset($info);
                //                  
            }
            
        } else {
            //
            $pola = array(array('specials_status','0'),
                          array('products_old_price','0'),
                          array('specials_date','0000-00-00'),
                          array('specials_date_end','0000-00-00'));
            //
            for ($x = 2; $x <= ILOSC_CEN; $x++) {
                //
                $pola[] = array('products_old_price_'.$x,'0');
                //
            }
            //
            
            $sql_wynik = $db->update_query('products' , $pola, " products_id = '".(int)$_POST["id"]."'"); 
            
            // aktualizowanie cen dla kombinacji cech
            if ( isset($_POST['ceny_cechy']) ) {
              
                $pola = array(array('products_stock_old_price','0'));
                //
                for ($x = 2; $x <= ILOSC_CEN; $x++) {
                    //
                    $pola[] = array('products_stock_old_price_'.$x,'0');
                    //
                }
                //
                
                $sql_wynik = $db->update_query('products_stock' , $pola, " products_id = '".(int)$_POST["id"]."'"); 

            }
            
        }
    
        //
        Funkcje::PrzekierowanieURL('promocje.php');
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Kasowanie pozycji</div>
    <div id="cont">
          
          <form action="promocje/promocje_usun.php" method="post" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Usuwanie danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select * from products where products_id= '" . (int)$_GET['id_poz'] . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
              
                $info = $sql->fetch_assoc();  
                ?>            
            
                <div class="pozycja_edytowana">
                
                    <input type="hidden" name="akcja" value="zapisz" />
                
                    <input type="hidden" name="id" value="<?php echo (int)$_GET['id_poz']; ?>" />
                    
                    <?php if ( $info['options_type'] == 'ceny' ) { ?>
                    
                        <input type="hidden" name="ceny_cechy" value="1" />
                    
                    <?php } ?>
                    
                    <p>
                      Czy skasować promocję dla tego produktu ?                    
                    </p>   
                    
                    <p>
                      <input type="checkbox" value="1" name="przywroc_ceny" id="przywroc_ceny" checked="checked" /> <label class="OpisFor" for="przywroc_ceny">po usunięciu ustaw jako cenę produktu cenę poprzednią <?php echo (($info['options_type'] == 'ceny') ? '(również dla cen kombinacji cech produktu)' : ''); ?></label>
                    </p>
                 
                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Usuń dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('promocje','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>');">Powrót</button>    
                </div>

                <?php
                unset($info);
            
            } else {
            
                echo '<div class="pozycja_edytowana">Brak danych do wyświetlenia</div>';
            
            }
            $db->close_query($sql);
            unset($zapytanie);               
            ?>

          </div>  
          
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}