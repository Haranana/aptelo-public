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
            <div class="naglowek">Klienci wg logowań</div>

                <div class="pozycja_edytowana">  

                    <span class="maleInfo">Raport listę klientów sklepu wg logowań (aktywności w sklepie)</span>

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
                                $sortowanie = 'ci.customers_info_date_account_created desc';
                                $sortUstaw = "sort_a3";
                                break;
                            case "sort_a4":
                                $sortowanie = 'ci.customers_info_date_account_created asc';
                                $sortUstaw = "sort_a4";
                                break;     
                            case "sort_a5":
                                $sortowanie = 'ci.customers_info_date_of_last_logon desc';
                                $sortUstaw = "sort_a5";
                                break;
                            case "sort_a6":
                                $sortowanie = 'ci.customers_info_date_of_last_logon asc';
                                $sortUstaw = "sort_a6";
                                break;                                
                            case "sort_a7":
                                $sortowanie = 'ilosc_dni_od_rejestracji desc';
                                $sortUstaw = "sort_a7";
                                break;
                            case "sort_a8":
                                $sortowanie = 'ilosc_dni_od_rejestracji asc';
                                $sortUstaw = "sort_a8";
                                break;                                 
                            case "sort_a9":
                                $sortowanie = 'ilosc_dni_od_logowania desc';
                                $sortUstaw = "sort_a9";
                                break;
                            case "sort_a10":
                                $sortowanie = 'ilosc_dni_od_logowania asc';
                                $sortUstaw = "sort_a10";
                                break;
                            case "sort_a11":
                                $sortowanie = 'ci.customers_info_number_of_logons desc';
                                $sortUstaw = "sort_a11";
                                break;
                            case "sort_a12":
                                $sortowanie = 'ci.customers_info_number_of_logons asc';
                                $sortUstaw = "sort_a12";
                                break;              
                        }            
                    } else { $sortowanie = 'c.customers_lastname desc'; $sortUstaw = "sort_a1"; } 
                    ?>
                  
                    <div id="sortowanie" style="padding:15px 0px 5px 10px">
                    
                        <span>Sortowanie:</span>

                        <a <?php echo (($sortUstaw == "sort_a1") ? 'class="sortowanie_zaznaczone"' : 'class="sortowanie"'); ?> href="statystyki/logowania_klientow.php?sort=sort_a1">nazwisko malejąco</a>
                        <a <?php echo (($sortUstaw == "sort_a2") ? 'class="sortowanie_zaznaczone"' : 'class="sortowanie"'); ?> href="statystyki/logowania_klientow.php?sort=sort_a2">nazwisko rosnąco</a>
                        <a <?php echo (($sortUstaw == "sort_a3") ? 'class="sortowanie_zaznaczone"' : 'class="sortowanie"'); ?> href="statystyki/logowania_klientow.php?sort=sort_a3">data rejestracji malejąco</a>
                        <a <?php echo (($sortUstaw == "sort_a4") ? 'class="sortowanie_zaznaczone"' : 'class="sortowanie"'); ?> href="statystyki/logowania_klientow.php?sort=sort_a4">data rejestracji rosnąco</a>
                        <a <?php echo (($sortUstaw == "sort_a5") ? 'class="sortowanie_zaznaczone"' : 'class="sortowanie"'); ?> href="statystyki/logowania_klientow.php?sort=sort_a5">data ostatniego logowania malejąco</a>
                        <a <?php echo (($sortUstaw == "sort_a6") ? 'class="sortowanie_zaznaczone"' : 'class="sortowanie"'); ?> href="statystyki/logowania_klientow.php?sort=sort_a6">data ostatniego logowania rosnąco</a>                    
                        <a <?php echo (($sortUstaw == "sort_a7") ? 'class="sortowanie_zaznaczone"' : 'class="sortowanie"'); ?> href="statystyki/logowania_klientow.php?sort=sort_a7">ilość dni od rejestracji malejąco</a>
                        <a <?php echo (($sortUstaw == "sort_a8") ? 'class="sortowanie_zaznaczone"' : 'class="sortowanie"'); ?> href="statystyki/logowania_klientow.php?sort=sort_a8">ilość dni od rejestracji rosnąco</a>     
                        <a <?php echo (($sortUstaw == "sort_a9") ? 'class="sortowanie_zaznaczone"' : 'class="sortowanie"'); ?> href="statystyki/logowania_klientow.php?sort=sort_a9">ilość dni od ostatniego logowania malejąco</a>
                        <a <?php echo (($sortUstaw == "sort_a10") ? 'class="sortowanie_zaznaczone"' : 'class="sortowanie"'); ?> href="statystyki/logowania_klientow.php?sort=sort_a10">ilość dni od ostatniego logowania rosnąco</a>                     
                        <a <?php echo (($sortUstaw == "sort_a11") ? 'class="sortowanie_zaznaczone"' : 'class="sortowanie"'); ?> href="statystyki/logowania_klientow.php?sort=sort_a11">ilość logowań malejąco</a>
                        <a <?php echo (($sortUstaw == "sort_a12") ? 'class="sortowanie_zaznaczone"' : 'class="sortowanie"'); ?> href="statystyki/logowania_klientow.php?sort=sort_a12">ilość logowań rosnąco</a>                                                             
                        
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
                                         ci.customers_info_date_of_last_logon,
                                         ci.customers_info_number_of_logons,
                                         ci.customers_info_date_account_created,
                                         IF (DATEDIFF(now(),ci.customers_info_date_account_created) = 0, (DATEDIFF(now(),ci.customers_info_date_account_created)), ((DATEDIFF(now(),ci.customers_info_date_account_created)) - 1)) as ilosc_dni_od_rejestracji,
                                         IF (ci.customers_info_number_of_logons > 0, (DATEDIFF(now(),ci.customers_info_date_of_last_logon)), 0) as ilosc_dni_od_logowania
                                    from customers c
                               left join customers_info ci on c.customers_id = ci.customers_info_id
                                   where c.customers_guest_account = '0'
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
                                         ci.customers_info_date_of_last_logon,
                                         ci.customers_info_number_of_logons,
                                         ci.customers_info_date_account_created,
                                         IF (DATEDIFF(now(),ci.customers_info_date_account_created) = 0, (DATEDIFF(now(),ci.customers_info_date_account_created)), ((DATEDIFF(now(),ci.customers_info_date_account_created)) - 1)) as ilosc_dni_od_rejestracji,
                                         IF (ci.customers_info_number_of_logons > 0, (DATEDIFF(now(),ci.customers_info_date_of_last_logon)), 0) as ilosc_dni_od_logowania
                                    from customers c
                               left join customers_info ci on c.customers_id = ci.customers_info_id
                                   where c.customers_guest_account = '0'
                                   group by c.customers_id, c.customers_firstname, c.customers_lastname order by " . $sortowanie . " limit " . $PoczatekLimit . "," . $IleNaStronie;
                                   
                    $sql = $db->open_query($zapytanie);
                    
                    if ((int)$db->ile_rekordow($sql) > 0) {
                        ?>

                        <div class="RamkaStatystyki" style="margin-top:8px">
                        
                            <table class="TabelaStatystyki">
                            
                            <tr class="TyNaglowek">
                                <td style="text-align:center">Lp</td>
                                <td style="text-align:center">Id</td>
                                <td>Imię i nazwisko</td>
                                <td style="text-align:center">Data rejestracji</td>
                                <td style="text-align:center">Data ostatniego logowania</td>
                                <td style="text-align:center">Ilość dni od rejestracji</td>
                                <td style="text-align:center">Ilość dni od ostatniego logowania</td>
                                <td style="text-align:center">Ilość logowań</td>
                            </tr>                       
                            
                            <?php
                            $poKolei = 1 + $PoczatekLimit;
                            while ($info = $sql->fetch_assoc()) {
                            
                                echo '<tr>';
                                
                                echo '<td class="poKolei">' . $poKolei . '</td>'; 
                                echo '<td class="inne">' . $info['customers_id'] . '</td>';
                                echo '<td class="linkProd" style="width:20%"><a href="klienci/klienci_edytuj.php?id_poz='.$info['customers_id'].'">' . $info['customers_firstname'] . ' ' . $info['customers_lastname'] . '</a></td>';
                                echo '<td class="inne">' . ((Funkcje::czyNiePuste($info['customers_info_date_account_created'])) ? date('d-m-Y H:i',FunkcjeWlasnePHP::my_strtotime($info['customers_info_date_account_created'])) : '-') . '</td>';
                                echo '<td class="inne">' . ((Funkcje::czyNiePuste($info['customers_info_date_of_last_logon'])) ? date('d-m-Y H:i',FunkcjeWlasnePHP::my_strtotime($info['customers_info_date_of_last_logon'])) : '-') . '</td>';
                                echo '<td class="inne">' . $info['ilosc_dni_od_rejestracji'] . '</td>';
                                echo '<td class="inne">' . $info['ilosc_dni_od_logowania'] . '</td>';
                                echo '<td class="inne">' . $info['customers_info_number_of_logons'] . '</td>';
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
                                    echo '<a class="Przycisk' . $Rozszerzenie . '" href="statystyki/logowania_klientow.php'.$zmienne.'">1</a>';
                                  } else {
                                    echo '<a class="Przycisk' . $Rozszerzenie . '" href="statystyki/logowania_klientow.php?str='.$c.$zmienne.'">' . ($c + 1) . '</a>';
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