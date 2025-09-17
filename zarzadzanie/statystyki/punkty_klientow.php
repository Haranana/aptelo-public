<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {
  
    if (isset($_GET['akcja']) && $_GET['akcja'] == 'usun') {
        //			
        if (isset($_GET['id']) && (int)$_GET['id'] > 0) {
            //
            $id = (int)$_GET['id'];
            //
            $pola = array(array('customers_shopping_points',0));
            $db->update_query('customers', $pola, "customers_id = '".$id."'");
            unset($pola); 
            //
            $db->delete_query('customers_points', "customers_id = '".$id."'"); 
            //
        }    
        //
        Funkcje::PrzekierowanieURL('punkty_klientow.php' . Funkcje::Zwroc_Get(array('akcja','zakres','id','x','y')));
    }
    
    if (isset($_GET['akcja']) && $_GET['akcja'] == 'usun_wszystko') {
        //
        $pola = array(array('customers_shopping_points',0));
        $db->update_query('customers', $pola, "customers_id > 0");
        unset($pola); 
        //		
        $db->delete_query('customers_points', "customers_id > 0");  
        //
        Funkcje::PrzekierowanieURL('punkty_klientow.php' . Funkcje::Zwroc_Get(array('akcja','zakres','id','x','y')));
    }      

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Raporty</div>
    <div id="cont">

          <div class="poleForm">
            <div class="naglowek">Klienci wg zebranych punktów</div>

                <div class="pozycja_edytowana">  

                    <span class="maleInfo">Raport wyświetla listę klientów sklepu wg zebranych punktów</span>

                    <?php
                    if (isset($_GET['sort'])) {
                        switch ($_GET['sort']) {
                            case "sort_a1":
                                $sortowanie = 'c.customers_lastname desc';
                                $sortUstaw = "sort_a1";
                                break;
                            case "sort_a2":                          
                                $sortowanie = 'c.customers_lastname asc';
                                $sortUstaw = "sort_a2";
                                break;         
                            case "sort_a3":
                                $sortowanie = 'c.customers_shopping_points desc';
                                $sortUstaw = "sort_a3";
                                break;
                            case "sort_a4":
                                $sortowanie = 'c.customers_shopping_points asc';
                                $sortUstaw = "sort_a4";
                                break;             
                        }            
                    } else { $sortowanie = 'c.customers_shopping_points desc'; $sortUstaw = "sort_a3"; } 
                    ?>
                  
                    <div id="sortowanie" style="padding:15px 0px 5px 10px">
                    
                        <span>Sortowanie:</span>

                        <a <?php echo (($sortUstaw == "sort_a1") ? 'class="sortowanie_zaznaczone"' : 'class="sortowanie"'); ?> href="statystyki/punkty_klientow.php?sort=sort_a1">nazwisko malejąco</a>
                        <a <?php echo (($sortUstaw == "sort_a2") ? 'class="sortowanie_zaznaczone"' : 'class="sortowanie"'); ?> href="statystyki/punkty_klientow.php?sort=sort_a2">nazwisko rosnąco</a>
                        <a <?php echo (($sortUstaw == "sort_a3") ? 'class="sortowanie_zaznaczone"' : 'class="sortowanie"'); ?> href="statystyki/punkty_klientow.php?sort=sort_a3">ilość pkt malejąco</a>
                        <a <?php echo (($sortUstaw == "sort_a4") ? 'class="sortowanie_zaznaczone"' : 'class="sortowanie"'); ?> href="statystyki/punkty_klientow.php?sort=sort_a4">ilość pkt rosnąco</a>                                                         
                        
                    </div>

                    <?php
                    // ile na stronie
                    $IleNaStronie = 50;
                    
                    $PoczatekLimit = 0;
                    if (isset($_GET['str']) && (int)$_GET['str'] > 0) {
                        $PoczatekLimit = (int)$_GET['str'] * $IleNaStronie;
                    }
 
                    // zapytanie bez limitu - ogolna ilosc 
                    $zapytanie = "select c.customers_id, 
                                         c.customers_firstname, 
                                         c.customers_lastname,
                                         c.customers_email_address,
                                         c.customers_shopping_points
                                    from customers c
                                   where c.customers_guest_account = '0' and c.customers_shopping_points > 0
                                   group by c.customers_id, c.customers_firstname, c.customers_lastname
                                   order by " . $sortowanie;                                  
                                   
                    $sql = $db->open_query($zapytanie);
                    $OgolnaIlosc = (int)$db->ile_rekordow($sql);
                    
                    $db->close_query($sql);
                    unset($zapytanie);
                    
                    // zapytanie z limitem
                    $zapytanie = "select c.customers_id, 
                                         c.customers_firstname, 
                                         c.customers_lastname,
                                         c.customers_email_address,
                                         c.customers_shopping_points
                                    from customers c
                                   where c.customers_guest_account = '0' and c.customers_shopping_points > 0
                                   group by c.customers_id, c.customers_firstname, c.customers_lastname
                                   order by " . $sortowanie . " limit " . $PoczatekLimit . "," . $IleNaStronie;
                                   
                    $sql = $db->open_query($zapytanie);
                    
                    if ((int)$db->ile_rekordow($sql) > 0) {
                        ?>
                        
                        <div style="margin:20px 0px 15px 10px">
                            <a class="usun" href="statystyki/punkty_klientow.php?akcja=usun_wszystko">usuń wszystkie dane</a>
                        </div>                        

                        <div class="RamkaStatystyki" style="margin-top:8px">
                        
                            <table class="TabelaStatystyki">
                            
                            <tr class="TyNaglowek">
                                <td style="text-align:center">Lp</td>
                                <td style="text-align:center">Id</td>
                                <td>Imię i nazwisko</td>
                                <td>Adres e-mail</td>
                                <td style="text-align:center">Ilość pkt</td>
                                <td>&nbsp;</td>
                            </tr>                       
                            
                            <?php
                            $poKolei = 1 + $PoczatekLimit;
                            while ($info = $sql->fetch_assoc()) {
                            
                                echo '<tr>';
                                
                                echo '<td class="poKolei">' . $poKolei . '</td>'; 
                                echo '<td class="inne">' . $info['customers_id'] . '</td>';
                                echo '<td class="linkProd"><a href="klienci/klienci_edytuj.php?id_poz='.$info['customers_id'].'">' . $info['customers_firstname'] . ' ' . $info['customers_lastname'] . '</a></td>';
                                echo '<td><a href="klienci/klienci_edytuj.php?id_poz='.$info['customers_id'].'">' . $info['customers_email_address'] . '</a></td>';
                                echo '<td class="inne">' . $info['customers_shopping_points'] . '</td>';
                                echo '<td class="WyczyscStat"><a class="TipChmurka" href="statystyki/punkty_klientow.php?akcja=usun&id='.$info['customers_id'].'"><b>Usuń tą frazę</b><img src="obrazki/kasuj.png" alt="Skasuj" /></a></td>';
                                
                                echo '</tr>';
                                
                                $poKolei++;
                            
                            }            
                            unset($poKolei);
                            $db->close_query($sql);
                            ?>
                            
                            </table>
                            
                        </div>
                        
                        <div id="DolneStrony">
                            <?php
                            $limit = $OgolnaIlosc / $IleNaStronie;
                            if ($limit < ($OgolnaIlosc / $IleNaStronie)) {
                                $limit++;
                            }
                            //
                            for ($c = 0; $c < $limit; $c++) {
                                //
                                $Rozszerzenie = 'Nieaktywny';
                                if ((!isset($_GET['str']) || (int)$_GET['str'] == 0) && $c == 0) {
                                    $Rozszerzenie = 'Aktywny';
                                }
                                if (isset($_GET['str']) && (int)$_GET['str'] == $c) {
                                    $Rozszerzenie = 'Aktywny';
                                }
                                //
                                // sprawdzanie czy są jakies zmienne GET
                                $zmienne = Funkcje::Zwroc_Get(array('str'),(((isset($_GET['sort'])) && $c > 0) ? true : false));
                                //
                                if ($c == 0) {
                                    echo '<a class="Przycisk' . $Rozszerzenie . '" href="statystyki/punkty_klientow.php'.$zmienne.'">1</a>';
                                  } else {
                                    echo '<a class="Przycisk' . $Rozszerzenie . '" href="statystyki/punkty_klientow.php?str='.$c.$zmienne.'">' . ($c + 1) . '</a>';
                                }
                            }
                            //
                            echo '<span id="IleStrona">Wyświetlanie: ' . ($PoczatekLimit + 1) . ' do ' . (($PoczatekLimit + $IleNaStronie) > $OgolnaIlosc ? $OgolnaIlosc : ($PoczatekLimit + $IleNaStronie)) . ' z ' . $OgolnaIlosc . '</span>';
                            ?>
                        </div>
                        
                        <div class="cl"></div>
                        
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