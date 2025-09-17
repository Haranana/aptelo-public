<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Raporty</div>
    <div id="cont">

          <div class="poleForm">
            <div class="naglowek">Najczęściej kupowane produkty</div>

                <div class="pozycja_edytowana">  

                    <span class="maleInfo">Raport prezentuje 100 najczęściej kupowanych produktów w sklepie</span>
                    
                    <?php
                    Listing::postGet(basename($_SERVER['SCRIPT_NAME']));
                    ?>                     
                    
                    <form action="statystyki/najczesciej_kupowane.php" method="post" id="statForm" class="cmxform">
                    
                    <script>
                    $(document).ready(function() {
                      $('input.datepicker').Zebra_DatePicker({
                        format: 'd-m-Y',
                        inside: false,
                        readonly_element: false
                      });                
                    });
                    </script>                    
                    
                    <div id="zakresDat" style="margin-top:10px">
                        <span>Przedział czasowy wyników od:</span>
                        <input type="text" id="data_od" name="data_od" value="<?php echo ((isset($_GET['data_od'])) ? $filtr->process($_GET['data_od']) : ''); ?>" size="10" class="datepicker" />&nbsp;do&nbsp;
                        <input type="text" id="data_do" name="data_do" value="<?php echo ((isset($_GET['data_do'])) ? $filtr->process($_GET['data_do']) : ''); ?>" size="10" class="datepicker" />
                    </div>    

                    <div class="wyszukaj_przycisk"><input type="image" alt="Szukaj" src="obrazki/ok.png" /></div>
                    
                    <?php
                    if ( Listing::wylaczFiltr(basename($_SERVER['SCRIPT_NAME'])) == true ) {
                      echo '<div id="wyszukaj_ikona"><a href="statystyki/najczesciej_kupowane.php?filtr=nie"><img src="obrazki/reset_szukaj.png" alt="Anuluj wyszukiwanie" /></a></div>';
                    }
                    ?>                     
                    
                    <div class="cl"></div>
                    
                    </form>
                    
                    <?php
                    $warunki_szukania = '';
                    if ( isset($_GET['data_od']) && $_GET['data_od'] != '' ) {
                        $szukana_wartosc = date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_GET['data_od'] . ' 00:00:00')));
                        $warunki_szukania .= " and date_purchased >= '".$szukana_wartosc."'";
                    }

                    if ( isset($_GET['data_do']) && $_GET['data_do'] != '' ) {
                        $szukana_wartosc = date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_GET['data_do'] . ' 23:59:59')));
                        $warunki_szukania .= " and date_purchased <= '".$szukana_wartosc."'";                      
                    }
                    //

                    $zapytanie = "select o.date_purchased, 
                                         op.products_name, 
                                         op.products_id, 
                                         sum(op.products_quantity) as ilosc
                                    from orders as o, orders_products as op 
                                    where o.orders_id = op.orders_id ".$warunki_szukania."
                                    GROUP by products_id ORDER BY ilosc DESC, op.products_name limit 100";
                                    
                    $sql = $db->open_query($zapytanie);
                    
                    if ((int)$db->ile_rekordow($sql) > 0) {
                        ?>
                        
                        <div class="WykresStatystki">
                        
                            <h3>
                                <?php
                                echo '20 najczęściej kupowanych produktów';
                                ?>
                            </h3>                    
                        
                            <div class="SzerokiWykres"><canvas id="kupowane_produkty" width="800" height="230"></canvas></div>
                            
                        </div>  
                        
                        <?php
                        include('statystyki/najczesciej_kupowane_wykres.php');
                        ?>   
                        
                        <br />
                        
                        <div class="RamkaStatystyki">
                        
                            <table class="TabelaStatystyki">

                            <tr class="TyNaglowek">
                                <td>Lp</td>
                                <td>Nazwa produktu</td>
                                <td>Ilość sprzedanych</td>
                            </tr>  
                            
                            <?php
                            $poKolei = 1;
                            while ($info = $sql->fetch_assoc()) {
                            
                                echo '<tr>';
                                
                                echo '<td class="poKolei">' . $poKolei . '</td>'; 
                                
                                // jezeli nie ma nazwy 
                                if ($info['products_name'] == '') {
                                     echo '<td class="linkProd">-- brak nazwy --</td>';
                                   } else {
                                     echo '<td class="linkProd"><a href="produkty/produkty_edytuj.php?id_poz=' . $info['products_id'] . '" ' . (($poKolei < 11) ? 'style="font-weight:bold"' : ''). '>' . $info['products_name'] . '</a></td>';
                                }
                                
                                echo '<td class="wynikStat">' . $info['ilosc'] . '<span>jm</span></td>';
                                echo '</tr>';
                                
                                $poKolei++;
                            
                            }            
                            $db->close_query($sql);
                            unset($poKolei);
                            ?>
                            
                            </table>
                            
                        </div>
                        
                        <script src="statystyki/najczesciej_kupowane_wykres.js"></script>                         

                        <?php
                        
                    } else {
                        //
                        echo '<div style="margin:10px">Brak statystyk ...</div>';
                        //
                    }
                    ?>
                    
                     

                </div>

          </div>                      

    </div>    
    
    <?php
    include('stopka.inc.php');

}