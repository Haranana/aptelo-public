<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

        //
        if ( isset($_POST['magazyn']) && $_POST['magazyn'] == '1' && (int)$_POST['id_produktu_org'] > 0 ) {

          $ilosc_produktow = Sprzedaz::IloscProduktowAktualna($filtr->process($_POST["id_produktu_org"]),$filtr->process($_POST["ilosc"]));

          $pola = array(
                  array('products_quantity',(float)$ilosc_produktow),
          );
          $db->update_query('products' , $pola, " products_id = '".(int)$filtr->process($_POST["id_produktu_org"])."'");	
          unset($pola);

          //
          if ( isset($_POST['cechy']) && $_POST['cechy'] != '' ) {

            $ilosc_produktow_cechy = Sprzedaz::IloscProduktowCechyAktualna($filtr->process($_POST["id_produktu_org"]),$filtr->process($_POST["cechy"]),$filtr->process($_POST["ilosc"]));

            $pola = array(
                    array('products_stock_quantity',(float)$ilosc_produktow_cechy),
            );
            $db->update_query('products_stock' , $pola, " products_id = '".(int)$filtr->process($_POST["id_produktu_org"])."' AND products_stock_attributes = '".$filtr->process($_POST["cechy"])."'");	
            unset($pola);

          }

        }

        $db->delete_query('orders_products' , " orders_products_id = '".(int)$filtr->process($_POST["id_produktu"])."'");  
        $db->delete_query('orders_products_attributes' , " orders_products_id = '".(int)$filtr->process($_POST["id_produktu"])."'");  

        // aktualizacja ilosci sprzedanych produktow
        if ( (int)$_POST['id_produktu_org'] > 0 ) {
          
            $zapytanie_sprzedane = "SELECT products_ordered FROM products WHERE products_id = '".(int)$_POST['id_produktu_org']."'";
            $sql_sprzedane = $db->open_query($zapytanie_sprzedane);
            $sprzedane = $sql_sprzedane->fetch_assoc();

            $sprzedane_akt = $sprzedane['products_ordered'] - $_POST['ilosc'];

            $pola = array(
                    array('products_ordered',(int)$sprzedane_akt));

            $db->update_query('products' , $pola, "products_id = '" . (int)$_POST['id_produktu_org'] . "'");

            $db->close_query($sql_sprzedane);         
            unset($zapytanie_sprzedane, $sprzedane, $pola, $sprzedane_akt);

        }
        
        Sprzedaz::PodsumowanieZamowieniaAktualizuj($_POST["id"], ((!isset($_SESSION['waluta_zamowienia'])) ? $_SESSION['domyslna_waluta']['kod'] : $_SESSION['waluta_zamowienia']));

        //
        Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz='.(int)$_POST["id"].((isset($_POST['zakladka'])) ? '&zakladka='.$filtr->process($_POST["zakladka"]) : ''));
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Kasowanie pozycji</div>
    <div id="cont">
          
      <?php
        
      if ( !isset($_GET['id_poz']) ) {
           $_GET['id_poz'] = 0;
      }    
      if ( !isset($_GET['produkt_id']) ) {
           $_GET['produkt_id'] = 0;
      }  
      
      $zapytanie = "SELECT 
                    * 
                    FROM orders_products
                    WHERE orders_id = '" . (int)$_GET['id_poz']. "' 
                    AND orders_products_id =  '" . (int)$_GET['produkt_id']. "'";
                    
      $sql = $db->open_query($zapytanie);

      if ((int)$db->ile_rekordow($sql) > 0) {

        $info = $sql->fetch_assoc();
        ?>
        
        <form action="sprzedaz/zamowienia_produkt_usun.php" method="post" id="zamowieniaForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Usuwanie danych</div>
            
                <div class="pozycja_edytowana">
                    
                    <input type="hidden" name="akcja" value="zapisz" />
                
                    <input type="hidden" name="id" value="<?php echo (int)$_GET['id_poz']; ?>" />
                    <input type="hidden" name="id_produktu" value="<?php echo (int)$_GET['produkt_id']; ?>" />
                    <?php if ( isset($_GET['zakladka']) ) { ?>                    
                    <input type="hidden" name="zakladka" value="<?php echo (int)$_GET['zakladka']; ?>" />
                    <?php } ?>
                    <input type="hidden" name="id_produktu_org" value="<?php echo $info['products_id']; ?>" />
                    <input type="hidden" name="ilosc" value="<?php echo $info['products_quantity']; ?>" />
                    <input type="hidden" name="cena_netto" value="<?php echo $info['products_price']; ?>" />
                    <input type="hidden" name="cena_brutto" value="<?php echo $info['products_price_tax']; ?>" />
                    <input type="hidden" name="cena_koncowa_netto" value="<?php echo $info['final_price']; ?>" />
                    <input type="hidden" name="cena_koncowa_brutto" value="<?php echo $info['final_price_tax']; ?>" />
                    <input type="hidden" name="cechy" value="<?php echo $info['products_stock_attributes']; ?>" />

                    <p>
                      Czy skasować pozycję ?
                    </p>
                    <?php if ( $info['products_id'] != '0' ) { ?>
                    <p>
                      <label style="padding-left:0px">Aktualizuj stany magazynowe:</label>
                      <input type="radio" value="1" name="magazyn" checked="checked" id="magazyn_tak" /> <label class="OpisFor" for="magazyn_tak">tak<em class="TipIkona"><b>Czy po usunięciu produktu przywrócić stan magazynowy ?</b></label></em>
                      <input type="radio" value="0" name="magazyn" id="magazyn_nie" /> <label class="OpisFor" for="magazyn_nie">nie<em class="TipIkona"><b>Czy po usunięciu produktu przywrócić stan magazynowy ?</b></em></label>       
                    </p>                     
                    <?php } ?>
                </div>
                
                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Zapisz dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('zamowienia_szczegoly','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz','zakladka')); ?>','sprzedaz');">Powrót</button>           
                </div>

          </div>                      
        </form>

        <?php

      } else {

        ?>
        
        <div class="poleForm"><div class="naglowek">Usuwanie danych</div>
            <div class="pozycja_edytowana">Brak danych do wyświetlenia</div>
        </div>
        
        <?php
        
      }

      $db->close_query($sql);
      unset($info);            
      ?>

    </div>    
    
    <?php
    include('stopka.inc.php');

}