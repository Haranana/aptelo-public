<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

      $pola = array(
              array('orders_id',(int)$_POST["id"]),
              array('products_id',(int)$_POST["produkt_id"]),
              array('products_model',$filtr->process($_POST["model"])),
              array('products_man_code',$filtr->process($_POST["kod_producenta"])),
              array('products_ean',$filtr->process($_POST["ean"])),
              array('products_name',$filtr->process($_POST["nazwa"])),
              array('products_pkwiu',$filtr->process($_POST["pkwiu"])),
              array('products_gtu',$filtr->process($_POST["gtu"])),
              array('products_quantity',(float)$_POST["ilosc"]),
              array('products_comments',$filtr->process($_POST["komentarz"])));
      
      $WspolczynnikRabatu = 1;
      
      if ( isset($_POST['rabat']) && $_POST['rabat'] == '1' && abs($_POST['rabat_wielkosc']) > 0 ) {
           $pola[] = array('products_discount', abs($_POST['rabat_wielkosc']));
           //
           $WspolczynnikRabatu = ((100 - abs($_POST['rabat_wielkosc'])) / 100);
           //
      }
      
      $stawka_vat = explode('|', (string)$filtr->process($_POST['vat']));
      $pola[] = array('products_tax',(float)$stawka_vat[0]);
      $pola[] = array('products_tax_class_id',(int)$stawka_vat[1]);   
      unset($stawka_vat);
      //			

      $sql = $db->insert_query('orders_products' , $pola);

      $id_dodanej_pozycji = $db->last_id_query();

      unset($pola);

      $wartosc_cech_netto = 0;
      $wartosc_cech_brutto = 0;
      $kombinacja_cech = array();

      if ( isset($_POST['cecha']) && count($_POST['cecha']) > 0 ) {

        foreach ( $_POST['cecha'] as $key ) {
        
          $tablica_wartosc_cechy = explode( ';', (string)$key );
          $prefix  = $_POST['cecha_prefix'][$tablica_wartosc_cechy['1']];
          $cena_cechy_netto = (float)$_POST['cecha_cena_netto'][$tablica_wartosc_cechy['1']] * $WspolczynnikRabatu;
          $cena_cechy_brutto = (float)$_POST['cecha_cena_brutto'][$tablica_wartosc_cechy['1']] * $WspolczynnikRabatu;
          $kombinacja_cech[ $tablica_wartosc_cechy['1'] ] = $tablica_wartosc_cechy['1'].'-'.$tablica_wartosc_cechy['0'];

          $zapytanie_nazwa_cechy = "SELECT * FROM products_options
                                        WHERE products_options_id = '" . (int)$tablica_wartosc_cechy['1']. "' 
                                        AND language_id =  '".(int)$_SESSION['domyslny_jezyk']['id']."'";
                                        
          $sql_nazwa_cechy = $db->open_query($zapytanie_nazwa_cechy);
          unset($zapytanie_nazwa_cechy);

          if ((int)$db->ile_rekordow($sql_nazwa_cechy) > 0) {
            $info_nazwa_cechy = $sql_nazwa_cechy->fetch_assoc();
            $nazwa_cechy = $info_nazwa_cechy['products_options_name'];
          }

          $zapytanie_wartosc_cechy = "SELECT * FROM products_options_values
                                        WHERE products_options_values_id = '" . (int)$tablica_wartosc_cechy['0']. "' 
                                        AND language_id =  '".(int)$_SESSION['domyslny_jezyk']['id']."'";
                                        
          $sql_wartosc_cechy = $db->open_query($zapytanie_wartosc_cechy);
          unset($zapytanie_wartosc_cechy);

          if ((int)$db->ile_rekordow($sql_wartosc_cechy) > 0) {
            $info_wartosc_cechy = $sql_wartosc_cechy->fetch_assoc();
            $nazwa_wartosci_cechy = $info_wartosc_cechy['products_options_values_name'];
            unset($info_wartosc_cechy);
          }

          if ( $prefix != '*' ) {

              $pola = array(
                      array('orders_id',(int)$_POST["id"]),
                      array('orders_products_id',(int)$id_dodanej_pozycji),
                      array('products_options',$nazwa_cechy),
                      array('products_options_id',(int)$tablica_wartosc_cechy['1']),
                      array('products_options_values',$nazwa_wartosci_cechy),
                      array('products_options_values_id',(int)$tablica_wartosc_cechy['0']),
                      array('options_values_price',(float)$cena_cechy_netto),
                      array('options_values_tax',((float)$cena_cechy_brutto - (float)$cena_cechy_netto)),
                      array('options_values_price_tax',(float)$cena_cechy_brutto),
                      array('price_prefix',$prefix));
              
          } else {
            
              $pola = array(
                      array('orders_id',(int)$_POST["id"]),
                      array('orders_products_id',(int)$id_dodanej_pozycji),
                      array('products_options',$nazwa_cechy),
                      array('products_options_id',(int)$tablica_wartosc_cechy['1']),
                      array('products_options_values',$nazwa_wartosci_cechy),
                      array('products_options_values_id',(int)$tablica_wartosc_cechy['0']),
                      array('options_values_price',0),
                      array('options_values_tax',0),
                      array('options_values_price_tax',((float)$cena_cechy_brutto) / ((float)$_POST["brut_1_podstawa"] * $WspolczynnikRabatu)),
                      array('price_prefix',$prefix));

          }          

          $sql = $db->insert_query('orders_products_attributes' , $pola);
          unset($pola, $tablica_wartosc_cechy, $prefix, $cena_cechy_netto, $cena_cechy_brutto, $cena_cechy_netto);

        }
      }
      
      ksort($kombinacja_cech);
      $kombinacja_cech = implode(',', (array)$kombinacja_cech);

      // szuka czy dana kombinacja cech nie ma unikalnego nr katalogowego i ean
      $nr_katalogowy_cechy = $filtr->process($_POST["model"]);
      $zdjecie_cechy = '';
      $kod_ean = $filtr->process($_POST["ean"]);

      // szuka zdjecia wartosci cechy
      $tab_cech = explode(',',(string)$kombinacja_cech);
            
      foreach ( $tab_cech as $tmp_cecha ) {
          //
          $podziel_tmp = explode('-', (string)$tmp_cecha);
          //
          if ( count($podziel_tmp) == 2 ) {
               //
               $zapytanie_cechy = "select options_values_image from products_attributes where products_id = '" . (int)$_POST["produkt_id"] . "' and options_values_id = '" . (int)$podziel_tmp[1] . "' and options_values_image != ''";
               $sql_cechy = $GLOBALS['db']->open_query($zapytanie_cechy);   
               //
               if ((int)$GLOBALS['db']->ile_rekordow($sql_cechy) > 0) {
                   // 
                   $info_dane_cechy = $sql_cechy->fetch_assoc();
                   //
                   if ( isset($info_dane_cechy['options_values_image']) && $info_dane_cechy['options_values_image'] != '' ) {
                        //                  
                        $zdjecie_cechy = $info_dane_cechy['options_values_image'];
                        //
                   }
                   //
              }
              //
              $GLOBALS['db']->close_query($sql_cechy);   
              unset($zapytanie_cechy);                
              //
          }
          //
          unset($podziel_tmp);
          //
      }     
                      
      $zapytanie_cechy = "SELECT products_stock_model, products_stock_image, products_stock_ean FROM products_stock WHERE products_stock_attributes = '" . $kombinacja_cech . "' and products_id = '" . (int)$_POST["produkt_id"] . "'";
      $sql_dane_cechy = $db->open_query($zapytanie_cechy);
      //
      if ((int)$db->ile_rekordow($sql_dane_cechy) > 0) {
        $info_dane_cechy = $sql_dane_cechy->fetch_assoc();
        //
        if (!empty($info_dane_cechy['products_stock_model'])) {
            $nr_katalogowy_cechy = $info_dane_cechy['products_stock_model'];
        }
        if (!empty($info_dane_cechy['products_stock_image'])) {
            $zdjecie_cechy = $info_dane_cechy['products_stock_image'];
        }           
        if (!empty($info_dane_cechy['products_stock_ean'])) {
            $kod_ean = $info_dane_cechy['products_stock_ean'];
        }        
        //
        unset($info_dane_cechy);
      }   
      //
      $db->close_query($sql_dane_cechy);      
      //      

      $pola = array(
              array('products_price',((isset($_POST['ma_cechy']) && $_POST['ma_cechy'] == 'tak') ? (float)$_POST["cena_1_podstawa"] * $WspolczynnikRabatu : (float)$_POST["cena_1"] * $WspolczynnikRabatu)),
              array('products_price_tax',((isset($_POST['ma_cechy']) && $_POST['ma_cechy'] == 'tak') ? (float)$_POST["brut_1_podstawa"] * $WspolczynnikRabatu : (float)$_POST["brut_1"] * $WspolczynnikRabatu)),      
              array('final_price',(float)$_POST["cena_1"] * $WspolczynnikRabatu),
              array('final_price_tax',(float)$_POST["brut_1"] * $WspolczynnikRabatu),
              array('products_stock_attributes',$kombinacja_cech),
              array('products_model',$nr_katalogowy_cechy),
              array('products_ean',$kod_ean));

      if ( $zdjecie_cechy != '' ) {
           //
           $pola[] = array('products_image', $zdjecie_cechy);
           //
      } else {
           //
           $pola[] = array('products_image', '');
           //
      }      
      //			

      $db->update_query('orders_products' , $pola, " orders_products_id = '".(int)$id_dodanej_pozycji."'");	
      unset($pola, $nr_katalogowy_cechy, $zdjecie_cechy, $kod_ean);

      // aktualizacja ilosci sprzedanych produktow
      if ( (int)$_POST["produkt_id"] > 0 ) {
        
          $zapytanie_sprzedane = "SELECT products_ordered, products_quantity FROM products WHERE products_id = '".(int)$_POST["produkt_id"]."'";
          $sql_sprzedane = $db->open_query($zapytanie_sprzedane);
          $sprzedane = $sql_sprzedane->fetch_assoc();

          $sprzedane_akt = $sprzedane['products_ordered'] + $_POST['ilosc'];
          $stanMagazynu_akt = $sprzedane['products_quantity'] - $_POST['ilosc'];

          if ( MAGAZYN_SPRAWDZ_STANY == 'tak' ) {

              $pola = array(
                      array('products_ordered',(int)$sprzedane_akt),
                      array('products_quantity',(float)$stanMagazynu_akt));

          } else {

              $pola = array(
                      array('products_ordered',(int)$sprzedane_akt));

          }

          $db->update_query('products' , $pola, "products_id = '" . (int)$_POST["produkt_id"] . "'");

          $db->close_query($sql_sprzedane);         
          unset($zapytanie_sprzedane, $sprzedane, $pola, $sprzedane_akt, $stanMagazynu_akt);

      }

      // aktualizacja ilosci cech sprzedanych produktow
      if ( CECHY_MAGAZYN == 'tak' ) {

          if ( $kombinacja_cech != '' ) {

              $zapytanie_cechy_sprzedane = "SELECT products_stock_quantity, products_stock_id, products_stock_attributes FROM products_stock WHERE products_id = '".(int)$_POST["produkt_id"]."'";
              $sql_cechy_sprzedane = $db->open_query($zapytanie_cechy_sprzedane);

              if ( (int)$db->ile_rekordow($sql_cechy_sprzedane) > 0 ) {

                  $TablicaKombinacji = explode(',', (string)$kombinacja_cech );
                  ksort($TablicaKombinacji);

                  while ( $cechy_sprzedane = $sql_cechy_sprzedane->fetch_assoc() ) {

                      $TablicaKombinacjiBaza = array();
                      $TablicaKombinacjiBaza = explode(',', (string)$cechy_sprzedane['products_stock_attributes'] );

                      ksort($TablicaKombinacjiBaza);

                      if ( !array_diff($TablicaKombinacji, $TablicaKombinacjiBaza) && !array_diff($TablicaKombinacjiBaza, $TablicaKombinacji) ) {

                          $cechyMagazyn_akt = $cechy_sprzedane['products_stock_quantity'] - $_POST['ilosc'];

                          $pola = array(
                                  array('products_stock_quantity',(float)$cechyMagazyn_akt));

                          $db->update_query('products_stock' , $pola, "products_id = '" . (int)$_POST["produkt_id"] . "' AND products_stock_id = '".(int)$cechy_sprzedane['products_stock_id']."'");

                      }

                      unset($TablicaKombinacjiBaza);

                  }

              }

              $db->close_query($sql_cechy_sprzedane);         
              unset($zapytanie_cechy_sprzedane, $cechy_sprzedane, $pola, $cechyMagazyn_akt);

          }
      }

      Sprzedaz::PodsumowanieZamowieniaAktualizuj($_POST["id"], $_POST["waluta"]);

      Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz='.(int)$_POST["id"].((isset($_POST['zakladka'])) ? '&zakladka='.$filtr->process($_POST["zakladka"]) : ''));
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Dodawanie pozycji</div>
    <div id="cont">
    
    <?php
    if ( !isset($_GET['id_poz']) ) {
         $_GET['id_poz'] = 0;
    }        
    
    if ( (int)$_GET['id_poz'] == 0 ) {
    ?>
       
      <div class="poleForm"><div class="naglowek">Dodawanie produktu</div>
        <div class="pozycja_edytowana">Brak danych do wyświetlenia</div>
      </div>      
      
    <?php
    } else {
    ?>      

      <div class="poleForm">
      
        <div class="naglowek">Dodawanie produktu</div>
        
        <form action="sprzedaz/zamowienia_szczegoly_produkt_dodaj.php" method="post" id="zamowieniaForm" class="cmxform">  
        
        <div class="pozycja_edytowana">
        
            <input type="hidden" name="akcja" value="zapisz" />
            
            <input type="hidden" id="rodzaj_modulu" value="zamowienie_produkt" />
            <input type="hidden" name="id" value="<?php echo (int)$_GET['id_poz']; ?>" />
            <?php if ( isset($_GET['zakladka']) ) { ?>
            <input type="hidden" name="zakladka" value="<?php echo (int)$_GET['zakladka']; ?>" />
            <?php } ?>
            
            <?php
            // wyszuka id klienta
            $zapytanie_klient = "select customers_id from orders where orders_id = '" . (int)$_GET['id_poz'] . "'";
            $sql_klient = $db->open_query($zapytanie_klient);
            $infs = $sql_klient->fetch_assoc();
            //
            echo '<input type="hidden" name="id_klienta" id="id_klienta" value="' . $infs['customers_id'] . '" />';
            //
            $db->close_query($sql_klient);
            unset($infs, $zapytanie_klient);          
            ?>

            <div class="GlownyListing">

                <div class="GlownyListingKategorieEdycja" id="drzewo_zamowienie_produkt">                   
            
                    <p style="font-weight:bold">
                    Wyszukaj produkt lub wybierz kategorię z której<br /> chcesz wybrać produkt do zamówienia
                    </p>
                    
                    <div style="margin-left:10px;margin-top:7px;" id="fraza">
                        <div>Wyszukaj produkt: <input type="text" size="15" value="" id="szukany" /></div><em class="TipChmurka"><b>Wpisz nazwę produktu lub kod producenta</b><span onclick="fraza_produkty()"></span></em>
                    </div>                        
                    
                    <div id="drzewo" style="margin-left:10px;margin-top:7px">
                        <?php
                        //
                        echo '<table class="pkc">';
                        //
                        $tablica_kat = Kategorie::DrzewoKategorii('0', '', '', '', false, true);
                        for ($w = 0, $c = count($tablica_kat); $w < $c; $w++) {
                            $podkategorie = false;
                            if ($tablica_kat[$w]['podkategorie'] == 'true') { $podkategorie = true; }
                            //
                            echo '<tr>
                                    <td class="lfp"><input type="radio" onclick="podkat_produkty(this.value)" value="'.$tablica_kat[$w]['id'].'" name="id_kat" id="kat_nr_'.$tablica_kat[$w]['id'].'" /> <label class="OpisFor" for="kat_nr_'.$tablica_kat[$w]['id'].'">'.$tablica_kat[$w]['text'].(($tablica_kat[$w]['status'] == 0) ? '<div class="wylKat TipChmurka"><b>Kategoria jest nieaktywna</b></div>' : '').'</label></td>
                                    <td class="rgp" '.(($podkategorie) ? 'id="img_'.$tablica_kat[$w]['id'].'"' : '').'>'.(($podkategorie) ? '<img src="obrazki/rozwin.png" alt="Rozwiń" onclick="podkat(\''.$tablica_kat[$w]['id'].'\',\'\',\'radio\')" />' : '').'</td>
                                  </tr>
                                  '.(($podkategorie) ? '<tr><td colspan="2"><div id="p_'.$tablica_kat[$w]['id'].'"></div></td></tr>' : '').'';
                        }
                        echo '</table>';
                        unset($tablica_kat,$podkategorie);
                        ?> 
                    </div> 
                        
                </div>
                
                <div style="GlownyListingProduktyEdycja">                               

                    <div id="wynik_produktow_zamowienie_produkt" class="WynikProduktowZamowienie" style="display:none"></div>     

                    <div id="formi" style="display:none">
                    
                        <div id="wybrany_produkt" class="WybranyProdukt"></div>
                        
                    </div>
                        
                </div>
                
            </div>                           
                        
        </div>

        <div class="przyciski_dolne" style="margin-left:2px">
          <input type="submit" id="ButZapis" class="przyciskNon" value="Zapisz dane" />
          <button type="button" class="przyciskNon" onclick="cofnij('zamowienia_szczegoly','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz','zakladka')); ?>','sprzedaz');">Powrót</button>   
        </div>

        </form>  

        <span id="dodajProdukt" class="ProduktInnyBaza" onclick="produkt_akcja(0,'zamowienie_produkt')">dodaj produkt spoza bazy sklepu</span>

      </div>     
      
    <?php } ?>

    </div>    
    
    <?php
    include('stopka.inc.php');

}
