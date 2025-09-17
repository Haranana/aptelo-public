<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone && isset($_GET['id_klienta']) && (int)$_GET['id_klienta'] > 0 && Sesje::TokenSpr()) {   

    $zapytanie_koszyki = "SELECT * FROM basket_save WHERE customers_id = '" . (int)$_GET['id_klienta'] . "' ORDER BY basket_date_added DESC";
    $sql_koszyki = $db->open_query($zapytanie_koszyki);

    if ((int)$db->ile_rekordow($sql_koszyki) > 0) {

      ?>
      <div class="ObramowanieTabeli" style="padding:2px 2px 2px 1px">
      
        <table class="listing_tbl">
        
          <tr class="div_naglowek">
            <td>Nazwa koszyka</td>
            <td>Opis koszyka</td>
            <td>Dodano</td>
            <td></td>
          </tr>
          
          <?php
          while ($info_koszyk = $sql_koszyki->fetch_assoc()) {
          ?>
          
            <tr class="pozycja_off">

              <td style="text-align:left"><?php echo $info_koszyk['basket_name']; ?></td>
              <td style="text-align:left"><?php echo $info_koszyk['basket_description']; ?></td>
              <td><?php echo date('d-m-Y H:i',FunkcjeWlasnePHP::my_strtotime($info_koszyk['basket_date_added'])); ?></td>
              <td><em class="TipChmurka"><b>Pokaz zawartość zapisanego koszyka</b><img onclick="podgladZapisanegoKoszyka('<?php echo (int)$info_koszyk['basket_id']; ?>')" style="cursor:pointer;" src="obrazki/zobacz.png" alt="" /></em></td>
              
            </tr>
            
          <?php } ?>
          
        </table>
        
      </div>
      
      <?php
      
   }
   
   $db->close_query($sql_koszyki);
   
}
?>