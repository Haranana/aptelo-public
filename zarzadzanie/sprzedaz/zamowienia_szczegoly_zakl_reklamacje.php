<?php
if ( isset($toks) ) {
// statusy reklamacji - tylko zamkniete
$zapytanie_reklamacja = "SELECT complaints_status_id FROM complaints_status WHERE complaints_status_type = 3 or complaints_status_type = 4";
$sql_reklamacja = $db->open_query($zapytanie_reklamacja);

$statusy_reklamacji = array(99);
    
while ($id_statusu = $sql_reklamacja->fetch_assoc()) {
    //
    $statusy_reklamacji[] = $id_statusu['complaints_status_id'];
    //
}
$db->close_query($sql_reklamacja);
unset($zapytanie_reklamacja);        

?>

    <div id="zakl_id_8" style="display:none;" class="pozycja_edytowana">

        <div class="ObramowanieTabeli">
        
          <table class="listing_tbl" id="InfoTabelaHistoria">
          
            <tr class="div_naglowek">
              <td>Nr zgłoszenia</td>
              <td>Tytuł zgłoszenia</td>
              <td>Data zgłoszenia</td>
              <td>Data ostatniej zmiany statusu</td>
              <td>Data rozpatrzenia</td>
              <td>Status</td>
              <td></td>
            </tr>
            
            <?php 
            
            $zapytanie_reklam = "SELECT * FROM complaints cus WHERE complaints_customers_orders_id = '" . $zamowienie->info['id_zamowienia'] . "'";

            $sql_reklam = $db->open_query($zapytanie_reklam);
            while ($info_reklam = $sql_reklam->fetch_assoc()) {

                // zmienne do przekazania
                $zmienne_do_przekazania = '?id_poz='.(int)$info_reklam['complaints_id']; 

                ?>
                <tr class="pozycja_off">
                
                  <td><?php echo $info_reklam['complaints_rand_id']; ?></td>
                  <td><?php echo $info_reklam['complaints_subject']; ?></td>
                  <td><?php echo date('d-m-Y H:i',FunkcjeWlasnePHP::my_strtotime($info_reklam['complaints_date_created'])); ?></td>
                  <td class="ListingSchowajMobile">
                      <?php
                      if ( Funkcje::CzyNiePuste($info_reklam['complaints_date_modified']) ) {
                           echo date('d-m-Y H:i',FunkcjeWlasnePHP::my_strtotime($info_reklam['complaints_date_modified']));
                        } else {
                           echo '-';
                      }
                      ?>
                  </td>
                  <?php
                      if ( Funkcje::CzyNiePuste($info_reklam['complaints_date_end']) && !in_array((string)$info_reklam['complaints_status_id'], $statusy_reklamacji) ) {
                           $tgm = date('d-m-Y H:i',FunkcjeWlasnePHP::my_strtotime($info_reklam['complaints_date_end']));
                           //
                           $temu = '';
                           $sek = (FunkcjeWlasnePHP::my_strtotime($info_reklam['complaints_date_end']) - time());
                           if ( $sek > 0 ) {
                               if ( $sek < 3660 ) {
                                    $temu = $sek . ' min. ';
                                    $css = 'style="color:#ff0000"';                            
                               }
                               if ( $sek < 86400 ) {
                                    $temu = date('G',$sek) . ' godz. ' . date('i',$sek) . ' min. ';
                                    $css = 'style="color:#ff0000"';
                               }
                               if ( $sek > 86400 ) {
                                    $temu = (date('j',$sek) - 1) . ' dni ';
                                    $css = 'style="color:#248c0c"';
                               }
                           } else {
                               $temu = 'PRZETERMINOWANA';
                               $css = 'style="color:#ff0000"';
                           }
                           if ( $temu != '' ) {
                                $tgm .= '<div class="Pozostalo">Pozostało: <b ' . $css . '>' . $temu . '</b></div>';
                           }                   
                           //
                           echo '<td>'.$tgm.'</td>';
                           unset($tgm);
                        } else {
                           echo '<td>-</td>';
                      }                  
                  ?>
                  <td><?php echo Reklamacje::pokazNazweStatusuReklamacji($info_reklam['complaints_status_id'], $_SESSION['domyslny_jezyk']['id']); ?></td>

                  <td class="rg_right IkonyPionowo">
                    <a class="TipChmurka" href="reklamacje/reklamacje_reklamacja_pdf.php<?php echo $zmienne_do_przekazania; ?>"><b>Wydruk reklamacji</b><img src="obrazki/pdf_2.png" alt="Wydruk reklamacji" /></a>
                    <a class="TipChmurka" href="reklamacje/reklamacje_szczegoly.php<?php echo $zmienne_do_przekazania; ?>"><b>Edytuj</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>
                  </td>
                </tr>
                <?php
              }
            ?>
            
          </table>
          
        </div>

    </div>
    
<?php
}
?>    