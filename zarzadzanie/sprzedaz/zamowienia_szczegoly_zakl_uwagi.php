<?php
if ( isset($toks) ) {
?>

    <div id="zakl_id_4" style="display:none;" class="pozycja_edytowana">

        <div class="ObramowanieTabeli">
        
          <table class="listing_tbl">
          
            <tr class="div_naglowek">
              <td style="text-align:left;">
                <div class="lf">Uwagi obsługi sklepu o kliencie</div>
                <div class="LinkEdycjiZamowienia"><a href="sprzedaz/zamowienia_uwagi_edytuj.php?id_poz=<?php echo (int)$_GET['id_poz']; ?>&amp;zakladka=4">edytuj</a></div>
              </td>
            </tr>

            <tr class="pozycja_off">
                <td  style="text-align:left;">
                    <?php 
                    //
                    // poszuka uwag do podobnych klientow - wg maila i nr telefonu
                    //
                    $uwagaZew = array();
                    //
                    if ( ZAMOWIENIE_UWAGI_INNYCH_KLIENTOW == 'tak' ) {
                         //
                         $zapytanie_uwagi = "SELECT customers_id, customers_telephone, customers_email_address, customers_dod_info FROM customers WHERE customers_dod_info != '' AND customers_id != " . $zamowienie->klient['id'];
                         $sql_uwagi = $GLOBALS['db']->open_query($zapytanie_uwagi);   
                         //
                         while ($infz = $sql_uwagi->fetch_assoc()) {
                              //
                              // mail
                              if ( $infz['customers_email_address'] == $zamowienie->klient['adres_email'] ) {
                                   //
                                   $uwagaZew[$infz['customers_id']] = $infz['customers_dod_info'] . ' <small style="display:block;margin:5px 0px 5px 0px;opacity:0.7">(uwagi z innego konta użytkownika o id <a style="text-decoration:underline" target="_blank" href="/zarzadzanie/klienci/klienci_edytuj.php?id_poz=' . $infz['customers_id'] . '">' . $infz['customers_id'] . '</a> - ten sam adres email)</small>';
                                   //
                              }
                              //
                              $telefon = preg_replace('/\D/', '', str_replace('+48', '', (string)$infz['customers_telephone']));
                              $telefon_klient = preg_replace('/\D/', '', str_replace('+48', '', (string)$zamowienie->klient['telefon']));
                              //
                              if ( $telefon == $telefon_klient ) {
                                   //
                                   $uwagaZew[$infz['customers_id']] = $infz['customers_dod_info'] . ' <small style="display:block;margin:5px 0px 5px 0px;opacity:0.7">(uwagi z innego konta użytkownika o id <a style="text-decoration:underline" target="_blank" href="/zarzadzanie/klienci/klienci_edytuj.php?id_poz=' . $infz['customers_id'] . '">' . $infz['customers_id'] . '</a> - ten sam numer telefonu)</small>';
                                   //
                              }
                              //
                              unset($telefon, $telefon_klient);
                              //
                         }
                         //
                         $GLOBALS['db']->close_query($sql_uwagi);
                         unset($zapytanie_uwagi);                            
                         //  
                    }                        
                    //
                    if ( $zamowienie->klient['uwagi'] != '' || ( isset($uwagaZew) && count($uwagaZew) > 0 ) ) {
                         //
                         echo nl2br($zamowienie->klient['uwagi']);
                         //
                         if ( isset($uwagaZew) && count($uwagaZew) > 0 ) { 
                              //
                              echo (($zamowienie->klient['uwagi'] != '' ) ? '<br /><br />' : '') . implode('<br />', (array)$uwagaZew);
                              //
                         }
                         //
                    } else {
                         //
                         echo 'brak';
                         //
                    }
                    ?>
                </td>
             </tr>
          </table>
          
        </div>

        <div class="ObramowanieTabeli" style="margin-top:10px;">
        
          <table class="listing_tbl">
          
            <tr class="div_naglowek">
              <td style="text-align:left;">
                <div class="lf">Uwagi obsługi sklepu do zamówienia</div>
                <div class="LinkEdycjiZamowienia"><a href="sprzedaz/zamowienia_uwagi_edytuj.php?id_poz=<?php echo (int)$_GET['id_poz']; ?>&amp;zakladka=4">edytuj</a></div>
              </td>
            </tr>

            <tr class="pozycja_off">
                <td  style="text-align:left;">
                    <?php 
                    if ( $zamowienie->info['uwagi'] != '' ) {
                        echo nl2br($zamowienie->info['uwagi']);
                    } else {
                        echo 'brak';
                    }
                    ?>
                </td>
             </tr>
          </table>
          
        </div>

    </div>  
    
<?php
}
?>        