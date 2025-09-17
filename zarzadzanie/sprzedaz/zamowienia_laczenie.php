<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
    
        $i18n = new Translator($db, $_SESSION['domyslny_jezyk']['id']);
        $GLOBALS['tlumacz'] = $i18n->tlumacz( array('WYSYLKI','PODSUMOWANIE_ZAMOWIENIA','PLATNOSCI'), null, true );

        $nr_glowny = (int)$_POST['nr_zamowienia'];
        $id_glownej_pozycji = $nr_glowny;
        
        $sposob = 'nowe';        
        if ( $_POST['sposob'] == 'aktualne' ) {
             $sposob = 'aktualne';
        }
        
        $nr_zamowien = array();
        
        if ( $sposob == 'aktualne' ) {
             //
             foreach ( explode(',', (string)$_POST['nr_zamowien']) as $klucz => $wartosc ) {
                //
                if ( $wartosc != $nr_glowny ) {
                     $nr_zamowien[] = $wartosc;
                }
                //
             }
             //
        } else {
             //        
             $nr_zamowien = explode(',', (string)$_POST['nr_zamowien']);
             //
        }

        // ------------ orders
        
        if ( $sposob == 'nowe' ) {
        
            $wykluczenia = array('orders_id',
                                 'invoice_dokument',
                                 'invoice_proforma_nr',
                                 'invoice_proforma_date',
                                 'last_modified',
                                 'date_purchased',
                                 'orders_status',
                                 'orders_source',
                                 'payment_method',
                                 'payment_info',
                                 'payment_method_class',
                                 'payment_method_array',
                                 'shipping_module',
                                 'shipping_info',
                                 'reference',
                                 'tracker_ip',
                                 'inpost_paczka_numer',
                                 'service',
                                 'orders_file_shopping');

            $sql = $db->open_query("SELECT * FROM orders WHERE orders_id = " . $nr_glowny); 
            
            $pola = array();

            while ($info = $sql->fetch_assoc()) {
            
                foreach ($info as $pole => $wartosc) {
                  //
                  if ( !in_array((string)$pole, $wykluczenia) ) {
                      //
                      $pola[] = array($pole, $wartosc);
                      //
                  }
                }

            }   

            $db->close_query($sql); 
            
            // dokument sprzedazy
            $pola[] = array('invoice_dokument',(int)$_POST['dokument_sprzedazy']);
            
            // wysylka
            $pola[] = array('shipping_module',$filtr->process($_POST['rodzaj_wysylki']));
            
            // platnosc
            $PodzialPlatnosci = explode('|', (string)$filtr->process($_POST['rodzaj_platnosci']));
            //
            $pola[] = array('payment_method',((isset($PodzialPlatnosci[1])) ? $PodzialPlatnosci[1] : ''));        
            $pola[] = array('payment_method_class',((isset($PodzialPlatnosci[0])) ? $PodzialPlatnosci[0] : ''));        
            $pola[] = array('payment_info',((isset($PodzialPlatnosci[2])) ? $GLOBALS['tlumacz']['PLATNOSC_'.$PodzialPlatnosci[2].'_TEKST'] : ''));        
            
            // opiekun
            $pola[] = array('service',$filtr->process($_POST['opiekun'])); 
            
            // status zamowienia
            $pola[] = array('orders_status',(int)$_POST['status_zamowienia']);         

            $pola[] = array('last_modified', 'now()');
            $pola[] = array('date_purchased', 'now()');
            $pola[] = array('orders_source', '4');

            $db->insert_query('orders', $pola);
            $id_dodanej_pozycji = $db->last_id_query();
            unset($pola, $wykluczenia);  
            
        } else {
          
            $pola = array();
          
            // opiekun
            $pola[] = array('service',$filtr->process($_POST['opiekun'])); 
            
            // status zamowienia
            $pola[] = array('orders_status',(int)$_POST['status_zamowienia']);           
            
            $pola[] = array('last_modified', 'now()');
            
            $db->update_query('orders', $pola, 'orders_id = ' . $id_glownej_pozycji);            

            unset($pola);  
            
        }

        // ------------ podsumowanie zamowienia
        
        $podsumowanie = $_POST['podsumowanie'];
        
        if ( $sposob == 'aktualne' ) {
             //
             $db->delete_query('orders_total', 'orders_id = ' . $id_glownej_pozycji);   
             //
        }
        
        foreach ($podsumowanie as $sumaSerial) {
            //
            $suma = unserialize(base64_decode((string)$sumaSerial));
            //
            $pola = array(
                    array('orders_id',(($sposob == 'nowe') ? (int)$id_dodanej_pozycji : $id_glownej_pozycji)),
                    array('title',$suma['tytul']),
                    array('text',$suma['tekst']),
                    array('value',(float)$suma['wartosc']),
                    array('prefix',$suma['prefix']),
                    array('class',$suma['klasa']),
                    array('sort_order',(int)$suma['sortowanie']),
                    array('tax',(float)$suma['vat_stawka']),
                    array('tax_class_id',(int)$suma['vat_id']));
            //  
            $db->insert_query('orders_total', $pola);
            unset($pola); 
            //
        }
        
        unset($podsumowanie);
        
        // ------------ produkty
        
        $wykluczenia = array('orders_products_id',
                             'orders_id',
                             'orders_products_attributes_id');
                             
        foreach ( $nr_zamowien as $laczone_zamowienie ) {

            $sql = $db->open_query("SELECT * FROM orders_products WHERE orders_id = " . (int)$laczone_zamowienie); 

            while ($info = $sql->fetch_assoc()) {
            
                $id_produktu = $info['orders_products_id'];
            
                $pola = array();
              
                foreach ($info as $pole => $wartosc) {
                  //
                  if ( !in_array((string)$pole, $wykluczenia) ) {
                      //
                      $pola[] = array($pole, $wartosc);
                      //
                  }
                  //
                }
                
                if ( $sposob == 'nowe' ) {
                
                    // nr zamowienia
                    $pola[] = array('orders_id',(int)$id_dodanej_pozycji);             
                  
                } else {
                  
                    // nr zamowienia
                    $pola[] = array('orders_id',(int)$id_glownej_pozycji);         
                    
                }
                
                $db->insert_query('orders_products', $pola);
                $id_dodanego_produktu = $db->last_id_query();
                unset($pola);  

                // sprawdza czy do produktu byly tez cechy
                $sql_produkt = $db->open_query("SELECT * FROM orders_products_attributes WHERE orders_products_id = " . $id_produktu);
                
                while ($infp = $sql_produkt->fetch_assoc()) {
                
                    $pola = array();
                  
                    foreach ($infp as $pole => $wartosc) {
                      //
                      if ( !in_array((string)$pole, $wykluczenia) ) {
                          //
                          $pola[] = array($pole, $wartosc);
                          //
                      }
                      //
                    }
                    
                    if ( $sposob == 'nowe' ) {
                    
                        // nr zamowienia
                        $pola[] = array('orders_id',(int)$id_dodanej_pozycji);     
                      
                    } else {
                      
                        // nr zamowienia
                        $pola[] = array('orders_id',(int)$id_glownej_pozycji);         
                        
                    }
                
                    // id produktu
                    $pola[] = array('orders_products_id',(int)$id_dodanego_produktu);
                  
                    $db->insert_query('orders_products_attributes', $pola);
                    unset($pola);             
                
                }
                
                $db->close_query($sql_produkt);
                unset($id_dodanego_produktu);

            }   

            $db->close_query($sql); 
            
        }
        
        unset($wykluczenia);
        
        // ------------ historia zamowienia
        
        $pola = array(
                array('orders_id ',(($sposob == 'nowe') ? (int)$id_dodanej_pozycji : $id_glownej_pozycji)),
                array('orders_status_id',(int)$_POST['status_zamowienia']),
                array('date_added','now()'),
                array('customer_notified ','0'),
                array('customer_notified_sms','0'),
                array('comments',$filtr->process($_POST['komentarz'])),
                array('admin_id',(int)$_SESSION['userID']));
                
        $db->insert_query('orders_status_history' , $pola);
        unset($pola);        
        
        // ------------ dodatkowe pola do zamowien
        
        if ( $sposob == 'nowe' ) {
            
            $wykluczenia = array('orders_id');

            $sql = $db->open_query("SELECT * FROM orders_to_extra_fields WHERE orders_id = " . $nr_glowny); 
            
            $pola = array();

            while ($info = $sql->fetch_assoc()) {
            
                foreach ($info as $pole => $wartosc) {
                  //
                  if ( !in_array((string)$pole, $wykluczenia) ) {
                      //
                      $pola[] = array($pole, $wartosc);
                      //
                  }
                }
                
                // nr zamowienia
                $pola[] = array('orders_id',$id_dodanej_pozycji); 

                $db->insert_query('orders_to_extra_fields', $pola);
                unset($pola);              

            }   

            $db->close_query($sql); 
            
            unset($wykluczenia);
            
        }
        
        // --------------- informacja dla laczonych zamowien
        
        foreach ( $nr_zamowien as $laczone_zamowienie ) {
        
            $pola = array(
                    array('orders_id ',(int)$laczone_zamowienie),
                    array('orders_status_id',(int)$_POST['status_zamowien']),
                    array('date_added','now()'),
                    array('customer_notified ','0'),
                    array('customer_notified_sms','0'),
                    array('comments',$filtr->process($_POST['komentarz_laczony'])));
                    
            $db->insert_query('orders_status_history' , $pola);
            unset($pola);
            
            $pola = array(
                    array('orders_status',(int)$_POST['status_zamowien']));
                    
            $db->update_query('orders' , $pola, 'orders_id = ' . (int)$laczone_zamowienie);
            unset($pola);                
        
        }
        
        if ( isset($_POST['wyslij_mail']) && $_POST['wyslij_mail'] == 'tak' ) {
          
            $zamowienie = new Zamowienie( (($sposob == 'nowe') ? (int)$id_dodanej_pozycji : $id_glownej_pozycji) );

            $email = new Mailing;

            $nadawca_email   = INFO_EMAIL_SKLEPU;
            $nadawca_nazwa   = INFO_NAZWA_SKLEPU;
            $cc              = '';
            $szablon         = (int)$_POST['szablon'];
            $tekst           = str_replace('{__NR_GLOWNEGO_ZAMOWIENIA}', (($sposob == 'nowe') ? (int)$id_dodanej_pozycji : $id_glownej_pozycji), $filtr->process($_POST['tresc_email']));
            $temat           = strip_tags($filtr->process($_POST['tytul_email']));              

            $adresat_email   = $zamowienie->klient['adres_email'];
            $adresat_nazwa   = $zamowienie->klient['nazwa'];
   
            $jezyk           = (int)$_POST["jezyk"];

            $zalaczniki = array();

            $email->wyslijEmail($nadawca_email,$nadawca_nazwa,$adresat_email, $adresat_nazwa, $cc, $temat, $tekst, $szablon, $jezyk, $zalaczniki, false);
          
        }
        
        Funkcje::PrzekierowanieURL('zamowienia_laczenie.php?id_nowe=' . (($sposob == 'nowe') ? (int)$id_dodanej_pozycji : $id_glownej_pozycji));
    
    }
    
    if ( !isset($_GET['id_nowe']) ) {
    
        $_GET['id'] = base64_decode((string)$_GET['id']);
        
        $TablicaZamowien = explode(',', ((isset($_GET['id']) ? $_GET['id'] : '')));
        if ( count($TablicaZamowien) == 1 ) {
             Funkcje::PrzekierowanieURL('zamowienia.php');
        }

    }
    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Łączenie wybranych zamówień</div>
    <div id="cont">
          
          <form action="sprzedaz/zamowienia_laczenie.php" method="post" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Łączenie zamówień</div>
            
            <div class="pozycja_edytowana">
            
                <div class="info_content">
                
                <?php
                // sprawdzi czy nie są rowne waluty
                $rozna_waluta = false;
                $waluta_zamowienia = '';
                if ( !isset($_GET['id_nowe']) ) {
                    
                    for ( $r = 0; $r < count($TablicaZamowien); $r++ ) {
                        //
                        $zamowienie = new Zamowienie($TablicaZamowien[$r]);
                        //
                        if ( $zamowienie->info['waluta'] != $waluta_zamowienia && $waluta_zamowienia != '' ) {
                             //                         
                             $rozna_waluta = true;
                             //
                        }
                        //
                        $waluta_zamowienia = $zamowienie->info['waluta'];
                        //
                        unset($zamowienie);
                        //
                    }
                }

                if ( $rozna_waluta == true ) { ?>
                
                    <div class="ostrzezenie" style="margin:10px">Nie można połączyć zamówień które są w różnych walutach.</div>
                
                <?php } else {
                
                    if ( !isset($_GET['id_nowe']) ) {
                    ?>
                
                    <input type="hidden" name="akcja" value="zapisz" />
                    <input type="hidden" name="nr_zamowien" value="<?php echo $_GET['id']; ?>" />
                    
                    <p>
                      <label><b>Wybierz sposób łączenia zamówień:</b></label>
                      <input type="radio" name="sposob" id="sposob_nowe" value="nowe" checked="checked" /> <label class="OpisFor" for="sposob_nowe">utwórz nowe zamówienie na podstawie łączonych zamówień</label>
                      <input type="radio" name="sposob" id="sposob_aktualne" value="aktualne" /> <label class="OpisFor" for="sposob_aktualne">dodaj dane z zamówień do wybranego głównego zamówienia</label>
                    </p>                    
                    
                    <script>
                    $(document).ready(function() {
                       $('#sposob_nowe').click(function() {
                          $('#komentarz_laczony').html('Zamówienie anulowane - połączone w jedno zamówienie z zamówień nr: <?php echo implode(', ', (array)$TablicaZamowien); ?>');
                       });
                       $('#sposob_aktualne').click(function() {
                          $('#komentarz_laczony').html('Zamówienie anulowane - połączone w jedno zamówienie o numerze ' + $("input[name='nr_zamowienia']:checked").val() + ' z zamówień nr: <?php echo implode(', ', (array)$TablicaZamowien); ?>');
                       });                
                       $('input[name=\'nr_zamowienia\']').click(function() {
                          if ( $('#sposob_aktualne').prop('checked') == true ) {
                               $('#komentarz_laczony').html('Zamówienie anulowane - połączone w jedno zamówienie o numerze ' + $("input[name='nr_zamowienia']:checked").val() + ' z zamówień nr: <?php echo implode(', ', (array)$TablicaZamowien); ?>');
                          }
                       });
                    });
                    </script>
                    
                    <div class="maleInfo">
                        Wybierz zamówienie główne z którego zostaną pobrane dane klienta, adresy wysyłki i płatnika, dane osobowe. Z pozostałych zamówień do nowego zamówienia zostaną dodane produkty oraz wartości z podsumowania zamówienia.
                    </div>                
                    
                    <p>
                      <label>Wybierz zamówienie główne:</label>
                    
                      <?php
                      for ( $r = 0; $r < count($TablicaZamowien); $r++ ) {
                          //
                          echo '<input type="radio" name="nr_zamowienia" id="nr_zam_' . $TablicaZamowien[$r] . '" value="' . $TablicaZamowien[$r] . '" ' . (($r == 0) ? 'checked="checked"' : '') . ' /> <label class="OpisFor" for="nr_zam_' . $TablicaZamowien[$r] . '">' . $TablicaZamowien[$r] . '</label> &nbsp; ';
                          //
                      }
                      ?>            
                    
                    </p>
                    
                    <p>
                      <label>Forma wysyłki nowego zamówienia:</label>
                      <?php
                      echo Funkcje::RozwijaneMenu('rodzaj_wysylki', Sprzedaz::ListaWysylekZamowien(false));
                      ?>
                    </p>
                    
                    <p>
                      <label>Forma płatności nowego zamówienia:</label>
                      <?php
                      echo Funkcje::RozwijaneMenu('rodzaj_platnosci', Sprzedaz::ListaPlatnosciZamowien(false, true));
                      ?>
                    </p>        

                    <p>
                      <label>Dokument sprzedaży dla nowego zamówienia:</label>
                      <input type="radio" name="dokument_sprzedazy" id="dokument_paragon" value="0" checked="checked" /> <label class="OpisFor" for="dokument_paragon">paragon</label>
                      <input type="radio" name="dokument_sprzedazy" id="dokument_faktura" value="1" /> <label class="OpisFor" for="dokument_faktura">faktura</label>
                    </p>
                    
                    <p>
                      <label>Opiekun zamówienia nowego zamówienia:</label>
                      <?php
                      // pobieranie informacji od uzytkownikach
                      $lista_uzytkownikow = array();
                      $zapytanie_uzytkownicy = "SELECT * FROM admin ORDER BY admin_lastname, admin_firstname";
                      $sql_uzytkownicy = $db->open_query($zapytanie_uzytkownicy);
                      //
                      $lista_uzytkownikow[] = array( 'id' => 0, 'text' => 'Nie przypisane ...' );
                      while ($uzytkownicy = $sql_uzytkownicy->fetch_assoc()) { 
                        $lista_uzytkownikow[] = array( 'id' => $uzytkownicy['admin_id'], 'text' => $uzytkownicy['admin_firstname'] . ' ' . $uzytkownicy['admin_lastname'] );
                      }
                      $db->close_query($sql_uzytkownicy); 
                      unset($zapytanie_uzytkownicy, $uzytkownicy);    
                      //
                      echo Funkcje::RozwijaneMenu('opiekun', $lista_uzytkownikow);
                      unset($lista_uzytkownikow);
                      //                                   
                      ?> 
                    </p>
                    
                    <p>
                      <label>Status nowego zamówienia:</label>
                      <?php
                      echo Funkcje::RozwijaneMenu('status_zamowienia', Sprzedaz::ListaStatusowZamowien(false));
                      ?>
                    </p>                   
                      
                    <p>
                      <label>Komentarz do nowego zamówienia:</label>
                      <textarea name="komentarz" cols="90" rows="5">Zamówienie połączone z zamówień o nr: <?php echo implode(', ', (array)$TablicaZamowien); ?></textarea>
                    </p>  

                    <p>
                      <label>Status dla zamówień łączonych:</label>
                      <?php
                      echo Funkcje::RozwijaneMenu('status_zamowien', Sprzedaz::ListaStatusowZamowien(false));
                      ?>
                    </p>                   
                      
                    <p>
                      <label>Komentarz dla zamówień łączonych:</label>
                      <textarea name="komentarz_laczony" id="komentarz_laczony" cols="90" rows="3">Zamówienie anulowane - połączone w jedno zamówienie z zamówień nr: <?php echo implode(', ', (array)$TablicaZamowien); ?></textarea>
                    </p>          
                    
                    <p>
                      <label>Czy wysłać do klienta e-mail o połączeniu zamówień ?</label>
                      <input type="radio" name="wyslij_mail" id="wyslij_mail_nie" value="nie" checked="checked" /> <label class="OpisFor" for="wyslij_mail_nie">nie</label>
                      <input type="radio" name="wyslij_mail" id="wyslij_mail_tak" value="tak" /> <label class="OpisFor" for="wyslij_mail_tak">tak</label>
                    </p>      
                    
                    <script>
                    $(document).ready(function() {
                       $("#wyslij_mail_tak").click(function() {
                          $('#wyslanie_maila').stop().slideDown();
                       });
                       $("#wyslij_mail_nie").click(function() {
                          $('#wyslanie_maila').stop().slideUp();
                       });                       
                    });
                    </script>                    
                    
                    <div id="wyslanie_maila" style="display:none">

                        <p id="wersja">
                          <label>Wersja językowa szablonu:</label>
                          <?php
                          echo Funkcje::RadioListaJezykow();
                          ?>
                        </p>
                            
                        <p>
                          <label>Tytuł wiadomości e-mail:</label>
                          <input type="text" name="tytul_email" id="tytul_email" size="80" value="Połączenie zamówień w jedno zamówienie" />
                        </p>                     
                        
                        <p>
                          <label>Treść wiadomości e-mail:</label>
                          <textarea name="tresc_email" id="tresc_email" cols="90" rows="3">Zamówienie od numerach: <?php echo implode(', ', (array)$TablicaZamowien); ?> zostały połączone w jedno zamówienie o numerze: {__NR_GLOWNEGO_ZAMOWIENIA}</textarea>
                        </p>  

                        <p>
                            <label for="szablon">Szablon emaila:</label>
                            <?php
                            $tablica = Funkcje::ListaSzablonowEmail(false);
                            echo Funkcje::RozwijaneMenu('szablon', $tablica, '', '', '', '', 'szablon' ); ?>
                        </p>   

                    </div>
                    
                    <br />

                    <div class="ObramowanieTabeli" style="padding:2px 2px 2px 1px">
                    
                      <table class="listing_tbl" id="InfoTabelaProdukty">
                      
                        <tr class="div_naglowek">
                          <td>Nr zamówienia</td>
                          <td>ID</td>
                          <td class="ListingSchowajMobile">Foto</td>
                          <td>Nazwa</td>
                          <td>Cena netto</td>
                          <td>Podatek</td>
                          <td>Cena brutto</td>
                          <td>Ilość</td>
                          <td>Wartość brutto</td>
                        </tr>
                        
                        <?php 
                        for ( $r = 0; $r < count($TablicaZamowien); $r++ ) {
                          
                            $zamowienie = new Zamowienie($TablicaZamowien[$r]);
                        
                            foreach ( $zamowienie->produkty as $produkt ) {

                              $wyswietl_cechy = '';

                              if (isset($produkt['attributes']) && (count($produkt['attributes']) > 0)) {

                                foreach ($produkt['attributes'] as $cecha ) {
                                  $wyswietl_cechy .= '<span class="MaleNrKatalogowy">'.$cecha['cecha'] . ': <b>' . $cecha['wartosc'] . '</b></span>';
                                }
                              }
                              
                              // czyszczenie z &nbsp; i zbyt dlugiej nazwy
                              $produkt['nazwa'] = Funkcje::PodzielNazwe($produkt['nazwa']);
                              $produkt['model'] = Funkcje::PodzielNazwe($produkt['model']);

                              ?>
                              <tr class="pozycja_off">
                                <td><?php echo $zamowienie->info['id_zamowienia']; ?></td>
                                <td><?php echo (($produkt['id_produktu'] > 0) ? $produkt['id_produktu'] : '-'); ?></td>
                                <td class="ListingSchowajMobile"><?php echo Funkcje::pokazObrazek($produkt['zdjecie'], $produkt['nazwa'], '40', '40'); ?></td>
                                <td style="text-align:left">
                                <?php 
                                echo '<span class="LinkProduktu">'.$produkt['nazwa'].'</span>';
                                if (trim((string)$produkt['model']) != '') {
                                  echo '<span class="MaleNrKatalogowy">Nr kat: <b>'.$produkt['model'].'</b></span>';
                                }
                                // pobieranie danych o producencie
                                if (trim((string)$produkt['producent']) != '') {                      
                                    //
                                    echo '<span class="MaleProducent">Producent: <b>'.$produkt['producent'].'</b></span>';
                                    //
                                }                  
                                // wyswietlenie cech produktu
                                if (!empty($wyswietl_cechy)) {                     
                                    //
                                    echo $wyswietl_cechy;
                                    //
                                }
                                // komentarz do produktu
                                if (!empty($produkt['komentarz'])) {
                                  echo '<span class="MaleNrKatalogowy">Komentarz: <b>'.$produkt['komentarz'].'</b></span>';
                                }       
                                // dodatkowe pola opisowe
                                if (!empty($produkt['pola_txt'])) {
                                  //
                                  $poleTxt = Funkcje::serialCiag($produkt['pola_txt']);
                                  if ( count($poleTxt) > 0 ) {
                                      foreach ( $poleTxt as $wartoscTxt ) {
                                          // jezeli pole to plik
                                          if ( $wartoscTxt['typ'] == 'plik' ) {
                                              echo '<span class="MaleNrKatalogowy">' . $wartoscTxt['nazwa'] . ': <a target="_blank" href="' . ADRES_URL_SKLEPU . '/wgrywanie/' . $wartoscTxt['tekst'] . '"><b>załączony plik</b></a></span>';
                                            } else {
                                              echo '<span class="MaleNrKatalogowy">' . $wartoscTxt['nazwa'] . ': <b>' . $wartoscTxt['tekst'] . '</b></span>';
                                          }                                          
                                      }
                                  }
                                  unset($poleTxt);
                                  //
                                }                                           

                                ?>
                                </td>
                                <td style="white-space: nowrap">
                                    <?php 
                                    if ( $produkt['cena_punkty'] > 0 ) {
                                         echo $produkt['cena_punkty'] . ' pkt + '; 
                                    }
                                    echo $waluty->FormatujCene($produkt['cena_koncowa_netto'], false, $zamowienie->info['waluta']); 
                                    ?>                                
                                </td>
                                <td><?php echo (($produkt['tax_info'] != $produkt['tax']) ? $produkt['tax_info'] . ' - ' . $produkt['tax'] . '%' : $produkt['tax'] . '%'); ?> </td>
                                <td style="white-space: nowrap">
                                    <?php 
                                    if ( $produkt['cena_punkty'] > 0 ) {
                                         echo $produkt['cena_punkty'] . ' pkt + '; 
                                    }
                                    echo $waluty->FormatujCene($produkt['cena_koncowa_brutto'], false, $zamowienie->info['waluta']); 
                                    ?>                                
                                </td>
                                <td><?php echo $produkt['ilosc']; ?></td>
                                <td style="white-space: nowrap">
                                    <?php                                     
                                    if ( $produkt['cena_punkty'] > 0 ) {
                                         echo ($produkt['cena_punkty'] * $produkt['ilosc']) . ' pkt + '; 
                                    }
                                    echo $waluty->FormatujCene($produkt['cena_koncowa_brutto'] * $produkt['ilosc'], false, $zamowienie->info['waluta']); 
                                    ?>
                                </td>
                              </tr>
                              
                            <?php 
                            } 
                            
                            unset($zamowienie);

                        }                    
                        ?>
                        
                      </table> 

                    </div>
                    
                    <br />

                    <div class="ObramowanieTabeli" style="padding:2px 2px 2px 1px">
                    
                      <table class="listing_tbl">
                      
                        <tr class="div_naglowek">
                          <td colspan="2">Podsumowanie zamówień</td>
                        </tr>
                        
                        <?php
                        $laczniePodsumowanie = array(); 

                        for ( $r = 0; $r < count($TablicaZamowien); $r++ ) {
                          
                            $zamowienie = new Zamowienie($TablicaZamowien[$r]);
                            
                            for ($i = 0, $n = count($zamowienie->podsumowanie); $i < $n; $i++) {
                                 //
                                 $sort = $zamowienie->podsumowanie[$i]['sortowanie'];
                                 if ( (int)$sort < 10 ) {
                                      $sort = '0' . $sort;
                                 }
                                 //
                                 if ( !isset( $laczniePodsumowanie[ $sort . '_' . $zamowienie->podsumowanie[$i]['klasa'] ] ) ) {
                                      //
                                      $laczniePodsumowanie[ $sort . '_' . $zamowienie->podsumowanie[$i]['klasa'] ] = $zamowienie->podsumowanie[$i];
                                      //
                                      // info o vat
                                      $laczniePodsumowanie[ $sort . '_' . $zamowienie->podsumowanie[$i]['klasa'] ]['wspolny_vat'] = 0;                             
                                      //                                  
                                    } else {
                                      //
                                      // sprawdza czy vat pozycji nie jest rozny
                                      if ( $laczniePodsumowanie[ $sort . '_' . $zamowienie->podsumowanie[$i]['klasa'] ]['vat_id'] != $zamowienie->podsumowanie[$i]['vat_id'] ) {
                                           // info o vat
                                           $laczniePodsumowanie[ $sort . '_' . $zamowienie->podsumowanie[$i]['klasa'] ]['wspolny_vat'] = 1;                             
                                           // 
                                      }
                                      //
                                      $laczniePodsumowanie[ $sort . '_' . $zamowienie->podsumowanie[$i]['klasa'] ]['wartosc'] = $laczniePodsumowanie[ $sort . '_' . $zamowienie->podsumowanie[$i]['klasa'] ]['wartosc'] + $zamowienie->podsumowanie[$i]['wartosc'];
                                      //
                                 }
                                 //
                                 // usuwa nr id bo jest niepotrzebny
                                 unset($laczniePodsumowanie[ $sort . '_' . $zamowienie->podsumowanie[$i]['klasa'] ]['orders_total_id']);
                                 //
                                 // dodaje walute
                                 $laczniePodsumowanie[ $sort . '_' . $zamowienie->podsumowanie[$i]['klasa'] ]['waluta'] = $zamowienie->info['waluta'];
                                 //
                                 // dodaje wartosc w walucie
                                 $laczniePodsumowanie[ $sort . '_' . $zamowienie->podsumowanie[$i]['klasa'] ]['tekst'] = $waluty->FormatujCene($laczniePodsumowanie[ $sort . '_' . $zamowienie->podsumowanie[$i]['klasa'] ]['wartosc'], false, $zamowienie->info['waluta']);
                                 //
                                 unset($sort);
                                 //
                            }
      
                            unset($zamowienie);
                            
                        }

                        ksort($laczniePodsumowanie);
                        
                        $infoVat = false;

                        foreach ( $laczniePodsumowanie as $zamowieniePodsumowanie ) {
                        ?>
                        
                          <tr class="pozycja_off">
                            <td style="width:80%">
                                <?php                             
                                //
                                echo $zamowieniePodsumowanie['tytul'];
                                //
                                if ( $zamowieniePodsumowanie['wspolny_vat'] == 1 ) {
                                    echo '<span class="MalyVat">różne stawki VAT</span>';
                                    $infoVat = true;
                                }   
                                //
                                echo '<input type="hidden" name="podsumowanie[]" value="' . base64_encode(serialize($zamowieniePodsumowanie)) . '" />';
                                ?>
                            </td>
                            <td style="text-align:right"><b>
                                <?php
                                if ( $zamowieniePodsumowanie['prefix'] == '0' ) {
                                    echo '<span style="color:red">';
                                }
                                echo $waluty->FormatujCene($zamowieniePodsumowanie['wartosc'], false, $zamowieniePodsumowanie['waluta']);
                                if ( $zamowieniePodsumowanie['prefix'] == '0' ) {
                                    echo '</span>';
                                } 
                                ?>
                            </b></td>
                          </tr>
                          
                          <?php
                          
                        }  

                        unset($sort);
                        ?>                    

                      </table> 
                      
                      <?php
                      if ( $infoVat == true ) {
                          echo '<div class="maleInfo" style="margin:5px;font-weight:normal">Niektóre pozycje podsumowania zamówienia w łączonych zamówieniach mają różne stawki podatku VAT. Po połączeniu zamówień podczas edycji zamówienia można dokonać korekty stawki VAT przypisanej do poszczególnych pozycji.</div>';
                      }       

                      unset($infoVat);
                      ?>
                      
                    </div>        

                    <?php } else { ?>
                    
                    <div class="ZamowieniePolaczono">Połączono zamówienia - zamówienie o nr: <span><?php echo (int)$_GET['id_nowe']; ?></span></div>

                    <?php }
                    
                } ?>
                
                </div>
            
            </div>
            
            <?php
            if ( !isset($_GET['id_nowe']) && $rozna_waluta == false ) {
            ?>            
            
            <div class="przyciski_dolne">
              <input type="submit" class="przyciskNon" value="Połącz zamówienia" />
              <button type="button" class="przyciskNon" onclick="cofnij('zamowienia','<?php echo Funkcje::Zwroc_Get(array('x','y')); ?>','sprzedaz');">Powrót</button> 
            </div>
            
            <?php } else if ( $rozna_waluta == false ) { ?>
            
            <div class="przyciski_dolne">
              <button type="button" class="przyciskNon" onclick="document.location='/zarzadzanie/sprzedaz/zamowienia_szczegoly.php?id_poz=<?php echo (int)$_GET['id_nowe']; ?>'">Przejdź do zamówienia</button> 
              <button type="button" class="przyciskNon" onclick="cofnij('zamowienia','<?php echo Funkcje::Zwroc_Get(array('x','y','id_nowe')); ?>','sprzedaz');">Powrót</button> 
            </div>            
            
            <?php } else if ( $rozna_waluta == true ) { ?>
            
            <div class="przyciski_dolne">
              <button type="button" class="przyciskNon" onclick="cofnij('zamowienia','<?php echo Funkcje::Zwroc_Get(array('x','y','id_nowe')); ?>','sprzedaz');">Powrót</button> 
            </div>  

            <?php } ?>

          </div>   
          
          </form>

    </div>    
    
    <?php
    unset($TablicaZamowien);
    
    include('stopka.inc.php');

}
?>