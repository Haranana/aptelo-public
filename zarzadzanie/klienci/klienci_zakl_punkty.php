<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone && isset($_GET['id_klienta']) && (int)$_GET['id_klienta'] > 0 && Sesje::TokenSpr()) {   

    $idKlienta = (int)$_GET['id_klienta'];
    ?>
    
    <table id="PunktyTabela">
        <tr>
            <td id="OgolnaIloscPkt" style="text-align:left">Całkowita ilość punktów klienta: <span><?php echo (int)$_GET['ogolem']; ?></span> pkt</td>
            <td style="text-align:right">
                <a class="dodaj" href="klienci/klienci_punkty_dodaj.php<?php echo '?id_poz='.$idKlienta.'&zakladka=5'; ?>">dodaj nową pozycję</a>
            </td>
        </tr>
    </table>             
    
    <?php
    $zapytanie_punkty = "SELECT * FROM customers_points WHERE customers_id = '" . $idKlienta . "' order by date_added desc";
    $sql_punkty = $db->open_query($zapytanie_punkty);

    if ((int)$db->ile_rekordow($sql_punkty) > 0) {
        //
        ?>
        <div class="ObramowanieTabeli" style="padding:2px 2px 2px 1px">
        
            <table class="listing_tbl">
            
              <tr class="div_naglowek">
                <td style="text-align:left">Tytuł punktów</td>
                <td>Status</td>
                <td>Data dodania</td>
                <td>Data zatwierdzenia <br /> anulowania</td>
                <td>Punkty</td>
                <td>&nbsp;</td>
              </tr>                                
              <?php
              //
              /*
              typy punktow
              1 - oczekujace
              2 - zatwierdzone
              3 - anulowane
              4 - wykorzystane
              */
              //
              while ($pkt = $sql_punkty->fetch_assoc()) {
                  //
                  echo '<tr class="pozycja_off">';
                  echo '<td style="text-align:left" class="LinkPunkty">';
                  
                  switch ($pkt['points_type']) {
                      case "RV":
                          //
                          $zapytanie_recenzja = "SELECT r.reviews_id, pd.products_name FROM reviews r, products_description pd WHERE r.products_id = pd.products_id and reviews_id = '" . (int)$pkt['reviews_id'] . "' and language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'";
                          $sql_recenzja = $db->open_query($zapytanie_recenzja);
                          $infr = $sql_recenzja->fetch_assoc();
                          //
                          // jezeli nie ma juz recenzji
                          if ( empty($infr['products_name']) ) {
                               $infr['products_name'] = '<span style="color:#ff0000">Nieznana - usunięta ...</span>';
                          }
                          //
                          if ((int)$db->ile_rekordow($sql_recenzja) > 0) {
                              echo '<a href="recenzje/recenzje_edytuj.php?id_poz=' . $pkt['reviews_id'] . '">';
                          }
                          //
                          echo 'Punkty za recenzję produktu ' . '<strong>' . $infr['products_name'] . '</strong>';
                          //
                          if ((int)$db->ile_rekordow($sql_recenzja) > 0) {
                              echo '</a>';
                          }
                          //
                          $db->close_query($sql_recenzja);
                          unset($infr);
                          //
                          break;
                      case "SP":
                          echo '<a href="sprzedaz/zamowienia_szczegoly.php?id_poz=' . $pkt['orders_id'] . '">Punkty za zamówienie nr <strong>' . $pkt['orders_id'] . '</strong></a>';
                          break; 
                      case "PP":
                          echo '<a href="sprzedaz/zamowienia_szczegoly.php?id_poz=' . $pkt['orders_id'] . '">Program partnerski - punkty za zamówienie nr <strong>' . $pkt['orders_id'] . '</strong></a>';
                          break;                        
                      case "SC":
                          echo '<a href="sprzedaz/zamowienia_szczegoly.php?id_poz=' . $pkt['orders_id'] . '">Punkty wykorzystane w zamówieniu nr <strong>' . $pkt['orders_id'] . '</strong></a>';
                          break;                         
                      case "RJ":
                          echo 'Punkty za rejestrację';
                          break;                         
                      default:
                          echo $pkt['points_comment'];
                          break;                 
                  }                                          
                  
                  echo '</td>';
                  echo '<td>' . Klienci::pokazNazweStatusuPunktow($pkt['points_status'], $_SESSION['domyslny_jezyk']['id']) . '</td>';
                  echo '<td>' . date('d-m-Y H:i',FunkcjeWlasnePHP::my_strtotime($pkt['date_added'])) . '</td>';
                  echo '<td>' . ((Funkcje::czyNiePuste($pkt['date_confirm'])) ? date('d-m-Y H:i',FunkcjeWlasnePHP::my_strtotime($pkt['date_confirm'])) : '-') . '</td>';
                  echo '<td>' . $pkt['points'] . '</td>';
                                                          
                  $zmienne_do_przekazania = '?id_poz='.$idKlienta.'&id='.$pkt['unique_id'].'&zakladka=5'; 
                  echo '<td class="rg_right IkonyPionowo"><a class="TipChmurka" href="klienci/klienci_punkty_usun.php'.$zmienne_do_przekazania.'"><b>Usuń tę pozycję</b><img src="obrazki/kasuj.png" alt="Usuń tę pozycję" /></a>';
                  
                  if ( (int)$pkt['points_status'] != 4 ) {
                      //
                      echo '<a class="TipChmurka" href="klienci/klienci_punkty_edytuj.php'.$zmienne_do_przekazania.'"><b>Edytuj ilość punktów</b><img src="obrazki/edytuj.png" alt="Edytuj ilość punktów" /></a>
                            <a class="TipChmurka" href="klienci/klienci_punkty_status.php'.$zmienne_do_przekazania.'"><b>Zmień status</b><img src="obrazki/zatwierdz.png" alt="Zmień status" /></a>';
                      //
                  }
                  
                  echo '</td>';
                  echo '</tr>';
                  //
              }
              ?>
              
            </table>

        </div>
        <?php
        
    } else {
     
        echo '<div class="pozycja_edytowana"><span class="maleInfo" style="margin:-10px 0px 0px -8px">Brak operacji punktowych na koncie klienta</span></div>';
        
    }
    
   $db->close_query($sql_punkty);
   unset($zapytanie_punkty);         

}
?>