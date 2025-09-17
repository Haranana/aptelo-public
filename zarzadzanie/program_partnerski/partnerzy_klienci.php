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
    
    <div id="naglowek_cont">Lista klientów przypisana do partnera</div>
    <div id="cont">    
    
        <div class="poleForm">
            <div class="naglowek">Lista klientów </div>   
            
                <?php if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] > 0) { ?>

                <?php
                $zapytanie = "SELECT * FROM customers c
                                  LEFT JOIN address_book a on c.customers_id = a.customers_id and c.customers_default_address_id = a.address_book_id
                                      WHERE pp_id_customers = '" . (int)$_GET['id_poz'] . "' OR pp_id_customers_coupon = '" . (int)$_GET['id_poz'] . "' order by customers_firstname, customers_lastname desc";
                $sql = $db->open_query($zapytanie);

                if ((int)$db->ile_rekordow($sql) > 0) {
                    //
                    ?>
                    
                    <div style="margin:10px; margin-top:0px;">
                    
                        <div class="ObramowanieTabeli">
                        
                            <table class="listing_tbl" id="PktLista">
                            
                                <tr class="div_naglowek">
                                  <td style="text-align:left">Klient</td>
                                  <td>Adres email</td>
                                  <td>Status klienta</td>
                                  <td>Rodzaj PP</td>
                                  <td>&nbsp;</td>
                                </tr>  
                                
                                <?php
                                while ($info = $sql->fetch_assoc()) {
                                    //
                                    echo '<tr class="pozycja_off">';
                                    echo '<td style="text-align:left"><a href="klienci/klienci_edytuj.php?id_poz=' . $info['customers_id'] . '">' . $info['customers_firstname'] . ' ' . $info['customers_lastname'] . '<br />' . $info['entry_street_address']. '<br />' . $info['entry_postcode'] . ' ' . $info['entry_city'] . '</td>';
                                    echo '<td>' . $info['customers_email_address'] . '</td>';
                                    echo '<td>';
                                    
                                    if ($info['customers_status'] == '1') { $obraz = 'aktywny_on.png'; $alt = 'Konto jest aktywne'; } else { $obraz = 'aktywny_off.png'; $alt = 'Konto jest nieaktywne'; }                           
                                    
                                    if ($info['customers_guest_account'] == '0') {
                                        echo '<img src="obrazki/' . $obraz . '" alt="' . $alt . '" />';
                                    }                                
                                    
                                    echo '</td>';
                                    echo '<td>';
                                    
                                    if ( $info['pp_id_customers'] == (int)$_GET['id_poz'] ) {
                                         echo '<div style="padding:5px">PP bannery<em class="TipIkona"><b>Klient uczestniczy w programie PP w oparciu o bannery (kod polecający)</b></em></div>';
                                    }
                                    if ( $info['pp_id_customers_coupon'] == (int)$_GET['id_poz'] ) {
                                         echo '<div style="padding:5px">PP kupon rabatowy<em class="TipIkona"><b>Klient uczestniczy w programie PP w oparciu o kupony rabatowe</b></em></div>';
                                    }                                
                                    
                                    echo '</td>';
                             
                                    $zmienne_do_przekazania = '?id_poz='.(int)$_GET['id_poz'] . '&id_klient=' . $info['customers_id']; 
                                    echo '<td class="rg_right IkonyPionowo"><a class="TipChmurka" href="program_partnerski/partnerzy_klienta_usun.php'.$zmienne_do_przekazania.'"><b>Usuń przypisanie PP dla tego klienta</b><img src="obrazki/kasuj.png" alt="Usuń przypisanie PP dla tego klienta" /></a>';
                                    echo '</td>';
                                    echo '</tr>';
                                    //
                                }
                                ?>
                                
                            </table>

                        </div>
                    
                    </div>
                    
                    <script>
                    $(document).ready(function(){
                        pokazChmurki();     
                    });
                    </script>                    
                    <?php
                } else {
                    ?>
                    
                    <div class="maleInfo" style="margin:10px 0px 0px 20px">Brak przypisanych klientów dla tego partnera</div>
                    
                    <?php
                }
                ?>
                
                <div class="przyciski_dolne">
                  <button type="button" class="przyciskNon" onclick="cofnij('partnerzy','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','program_partnerski');">Powrót</button>    
                </div>
                
                <?php 
                } else { 
    
                 echo '<div class="pozycja_edytowana">Brak danych do wyświetlenia</div>';
                
                } ?>
                            
        </div>
    
    </div>
    
    <?php
    include('stopka.inc.php');   
}