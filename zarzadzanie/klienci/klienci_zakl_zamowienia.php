<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone && isset($_GET['id_klienta']) && (int)$_GET['id_klienta'] > 0 && Sesje::TokenSpr()) {   

    $zapytanie_zamowienia = "SELECT o.orders_id, o.customers_id, o.payment_method, o.date_purchased, o.orders_status, o.orders_source, o.service, o.shipping_module, ot.value, ot.class, ot.text as order_total 
                              FROM orders_total ot
                              RIGHT JOIN orders o ON o.orders_id = ot.orders_id 
                              WHERE ot.class = 'ot_total' and customers_id = '" . (int)$_GET['id_klienta'] . "'
                           ORDER BY o.date_purchased desc"; 
    $sql_zamowienia = $db->open_query($zapytanie_zamowienia);

    if ((int)$db->ile_rekordow($sql_zamowienia) > 0) {

      ?>
      
      <div style="margin:0px 0px 5px 5px" id="fraza">
          <div>Wyszukaj zamówienia z produktem: <input type="text" size="15" value="<?php echo ((isset($_GET['produkt']) && trim((string)$_GET['produkt']) != '') ? $_GET['produkt'] : ''); ?>" id="szukany_zamowienie" /><em class="TipIkona"><b>Wpisz nazwę produktu, kod producenta lub nr katalogowy</b></em></div> <span id="SzukajProduktuZamowienie"></span>
          
          <?php if ( isset($_GET['produkt']) && !empty($_GET['produkt']) ) { ?>
          <div style="margin:6px 0px 0px 20px; float:left"><a href="klienci/klienci_edytuj.php?id_poz=<?php echo (int)$_GET['id_klienta']; ?>&zakladka=9"><img src="obrazki/reset_szukaj.png" alt="Anuluj wyszukiwanie" /></a></div>
          <?php } ?>
          
      </div>             
      
      <div class="ObramowanieTabeli">
      
        <script>
        $(document).ready(function(){
        
            $('.zmzoom_zamowienie_klient').hover(function(event) {
               PodgladIn($(this),event,'zamowienie');
            }, function() {
               PodgladOut($(this),'zamowienie_klient');
            }); 
            
        });
        
        $('#SzukajProduktuZamowienie').click(function() {
            //
            var fraza = $('#szukany_zamowienie').val();
            if ( fraza.length < 2 ) {
                 $.colorbox( { html:'<div id="PopUpInfo">Minimalna ilość znaków do wyszukiwania to 2</div>', initialWidth:50, initialHeight:50, maxWidth:'90%', maxHeight:'90%' } );
                 return false;
            }
            //
            setTimeout(function(){ window.location = 'klienci/klienci_edytuj.php?id_poz=<?php echo (int)$_GET['id_klienta']; ?>&zakladka=9&produkt=' + fraza }, 300);
            //
        });          
        </script>           
      
        <table class="listing_tbl ListaZamowien" id="lista_zamowien">
        
          <tr class="div_naglowek">
            <td class="ListingSchowajMobile">Info</td>
            <td>ID</td>
            <td>Data zamówienia</td>
            <td>Wartość</td>
            <td class="ListingSchowajMobile">Płatność</td>
            <td class="ListingSchowajMobile">Dostawa</td>
            <td>Status</td>
            <td>Typ</td>
            <td>&nbsp;</td>
          </tr>
          
          <?php 
          $SumaZamowien = 0;
          
          while ($info_zamowienie = $sql_zamowienia->fetch_assoc()) { ?>
          
            <?php
            $PokazZamowienie = false;
            if ( isset($_GET['produkt']) && !empty($_GET['produkt']) ) {
              //
              $szukana_wartosc = $filtr->process($_GET['produkt']);
              //
              $zapytanie_produkty = "SELECT products_name, products_model, products_man_code FROM orders_products
                                      WHERE orders_id = '" . $info_zamowienie['orders_id'] . "' and (products_name LIKE '%" . $szukana_wartosc . "%' or products_model LIKE '%" . $szukana_wartosc . "%' or products_man_code LIKE '%" . $szukana_wartosc . "%')";

              $sql_produkty = $db->open_query($zapytanie_produkty);
              if ((int)$db->ile_rekordow($sql_produkty) > 0) {
                 //
                 $PokazZamowienie = true;
                 //
              }
              //
            } else {
              //
              $PokazZamowienie = true;
              //
            }
            ?>
            
            <?php if ( $PokazZamowienie == true ) { $SumaZamowien++; ?>
          
            <tr class="pozycja_off">
              <td class="ListingSchowajMobile"><div id="zamowienie_<?php echo $info_zamowienie['orders_id']; ?>" class="zmzoom_zamowienie_klient"><div class="podglad_zoom"></div><img src="obrazki/info_duze.png" alt="Szczegóły" /></div></td>
              <td><?php echo $info_zamowienie['orders_id']; ?></td>
              <td><?php echo date('d-m-Y H:i',FunkcjeWlasnePHP::my_strtotime($info_zamowienie['date_purchased'])); ?></td>
              <td><?php echo '<span class="InfoCena">' . $info_zamowienie['order_total' ]. '</span>'; ?></td>
              <td class="ListingSchowajMobile"><?php echo $info_zamowienie['payment_method']; ?></td>
              <td class="ListingSchowajMobile"><?php echo $info_zamowienie['shipping_module']; ?></td>
              <td><?php echo Sprzedaz::pokazNazweStatusuZamowienia($info_zamowienie['orders_status'], $_SESSION['domyslny_jezyk']['id']); ?></td>
              <td>
              <?php
              switch ($info_zamowienie['orders_source']) {
                case "3":
                    echo '<em class="TipChmurka"><b>Zamówienie z Allegro</b><img src="obrazki/allegro_lapka.png" alt="Zamówienie z Allegro" /></em>';
                    break;                 
                case "4":
                    echo '<em class="TipChmurka"><b>Zamówienie ręczne</b><img src="obrazki/raczka.png" alt="Zamówienie ręczne" /></em>';
                    break;             
              }               
              ?>
              </td>
              <td class="IkonyPionowo">
                <a class="TipChmurka" href="sprzedaz/zamowienia_szczegoly.php?id_poz=<?php echo $info_zamowienie['orders_id']; ?>"><b>Szczegóły zamówienia</b><img src="obrazki/zobacz.png" alt="Szczegóły zamówienia" /></a> <br />
                <a class="TipChmurka" href="sprzedaz/zamowienia_zamowienie_pdf.php?id_poz=<?php echo $info_zamowienie['orders_id']; ?>"><b>Wydruk zamówienia</b><img src="obrazki/zamowienie_pdf.png" alt="Wydruk zamówienia" /></a>
                <a class="TipChmurka" href="sprzedaz/zamowienia_faktura_proforma.php?id_poz=<?php echo $info_zamowienie['orders_id']; ?>"><b>Wydruk faktury proforma</b><img src="obrazki/proforma_pdf.png" alt="Wydruk faktury proforma" /></a>
              </td>
            </tr>
            
            <?php } ?>
            
          <?php } ?>
          
          <?php if ( $SumaZamowien == 0 ) { ?>
          
          <tr>
            <td colspan="9" style="padding:15px">
            
                Brak wyników wyszukiwania ...

            </td>          
          </tr>             
          
          <?php } ?>
          
        </table>
        
      </div>
      <?php
      
    } else {
   
      ?>
      
      <span class="maleInfo">Brak zamówień dla klienta</span>
      
      <?php
      
    }
}
?>