<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    
    //
    $tablica_email = array();
    //    
    
    if (!isset($_GET['wyslano'])) {
    
        // tablice dostepnosci
        $dostepnosci = array();
        $dostepnosci_automatyczne = '';
        //
        $dostepnosci_zapytanie = "SELECT distinct * FROM products_availability p, products_availability_description pd WHERE p.products_availability_id = pd.products_availability_id AND pd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'";
        $sqls = $db->open_query($dostepnosci_zapytanie);
        //
        while ($infs = $sqls->fetch_assoc()) { 
            $dostepnosci[$infs['products_availability_id']] = array('kupowanie' => $infs['shipping_mode'], 'nazwa' => $infs['products_availability_name']);
        }
        $db->close_query($sqls); 
        unset($dostepnosci_zapytanie, $infs);           

        // tablica dostepnosci automatycznych
        $dostepnosci_zapytanie = "SELECT GROUP_CONCAT(CONVERT(quantity, CHAR(8)),':', CONVERT(products_availability_id, CHAR(8)) ORDER BY quantity DESC SEPARATOR ',') as wartosc FROM products_availability WHERE mode = '1'";
        $sqls = $db->open_query($dostepnosci_zapytanie);
        //
        while ($infs = $sqls->fetch_assoc()) {
            $dostepnosci_automatyczne = $infs['wartosc'];
        }
        $db->close_query($sqls);   
        unset($dostepnosci_zapytanie, $infs);     

        
        // ile maili do wyslania
        $zapytanie = "SELECT pn.products_notifications_id,
                             pn.customers_email_address,
                             pn.products_id,
                             pn.products_stock_attributes,
                             pd.products_name,         
                             pd.products_seo_url,
                             p.products_status,
                             p.products_quantity,
                             p.products_availability_id,
                             p.products_buy,
                             ps.products_stock_quantity,
                             ps.products_stock_availability_id
                        FROM products_notifications pn
                   LEFT JOIN products p ON p.products_id = pn.products_id
                   LEFT JOIN products_description pd ON pd.products_id = p.products_id AND pd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'
                   LEFT JOIN products_stock ps ON ps.products_id = pn.products_id AND ps.products_stock_attributes = pn.products_stock_attributes";   
                   
        $sql = $db->open_query($zapytanie);
        
        while ($info = $sql->fetch_assoc()) {
          
            $mozna_kupic = true;
            
            $ilosc_magazyn = $info['products_quantity'];
            
            if ( $info['products_status'] == 0 ) {
                 //
                 $mozna_kupic = false;
                 //
            } else {
                 //
                 // sprawdzi czy kategorie do jakich nalezy produkt sa wlaczone
                 $kategorie = $db->open_query("select ctc.categories_id from products_to_categories ctc, categories c where ctc.products_id = '" . (int)$info['products_id'] . "' and ctc.categories_id = c.categories_id and c.categories_status = '1'");
                 //
                 if ( (int)$db->ile_rekordow($kategorie) == 0 ) {
                     //
                     $mozna_kupic = false;
                     //
                 }
                 //
                 $db->close_query($kategorie);                    
                 //
            }
            
            // jezeli jest powiazanie cech z magazynem
            if ( CECHY_MAGAZYN == 'tak' ) {

                if ( $info['products_stock_attributes'] != '' ) {
                  
                     $ilosc_magazyn = (float)$info['products_stock_quantity'];
                     
                }
                
            }     

            if ( MAGAZYN_SPRAWDZ_STANY == 'tak' && MAGAZYN_SPRZEDAJ_MIMO_BRAKU == 'nie' && $ilosc_magazyn <= 0 ) {
                 //
                 $mozna_kupic = false;
                 //
            }        
            
            // sprawdzi jaka jest dostepnosc produktu i czy mozna przy niej kupowac
            $id_dostepnosci = $info['products_availability_id'];
            
            if ( isset($info['products_stock_attributes']) && $info['products_stock_attributes'] != '' ) {
                 //
                 $id_dostepnosci = $info['products_stock_availability_id'];
                 //
            }        
            
            $dostepnosc_produktu = Produkt::ProduktDostepnoscNazwa($dostepnosci, $dostepnosci_automatyczne, $id_dostepnosci, $ilosc_magazyn);
            
            if ( $dostepnosc_produktu['kupowanie'] == '0' ) {
                 //
                 $mozna_kupic = false;
                 //
            }    
            
            unset($dostepnosc_produktu, $id_dostepnosci, $ilosc_magazyn);
            
            // sprawdzi czy produkt nie ma wylaczonej opcji kupowania
            if ( $info['products_buy'] == '0' ) {
                 //
                 $mozna_kupic = false;
                 //
            }    

            if ( PRODUKT_KUPOWANIE_STATUS == 'tak' && $mozna_kupic == true ) {
                 //
                 $tablica_email[ $info['products_notifications_id'] ] = array( 'id' => $info['products_notifications_id'], 'email' => $info['customers_email_address'], 'produkt_id' => $info['products_id'], 'seo' => $info['products_seo_url'], 'produkt_nazwa' => $info['products_name'], 'cechy' => $info['products_stock_attributes'] );
                 //
            }        

        }

        $db->close_query($sql);
        unset($zapytanie, $info);
        
    }
    
    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
      
        if (isset($_POST['produkt']) && count($_POST['produkt']) > 0) {
          
            foreach ($tablica_email as $wyslanie) {
              
                if (in_array((string)$wyslanie['produkt_id'] . '_' . $wyslanie['cechy'], (array)$_POST['produkt'])) {
    
                    $zapytanie_tresc = "SELECT t.sender_name, t.email_var_id, t.sender_email, t.dw, t.template_id, t.email_file, tz.email_title, tz.description, tz.description_sms FROM email_text t LEFT JOIN email_text_description tz ON tz.email_text_id = t.email_text_id AND tz.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."' WHERE t.email_var_id = 'EMAIL_POWIADOMIENIE_O_PRODUKCIE'";
                    $sql = $db->open_query($zapytanie_tresc);
                    $tresc = $sql->fetch_assoc();    
                    
                    $email = new Mailing;

                    $nadawca_email   = Funkcje::parsujZmienne($tresc['sender_email']);
                    $nadawca_nazwa   = Funkcje::parsujZmienne($tresc['sender_name']); 
                    $cc              = Funkcje::parsujZmienne($tresc['dw']);

                    $adresat_email   = $filtr->process($wyslanie['email']);
                    $adresat_nazwa   = $filtr->process($wyslanie['email']);
                    
                    $temat           = str_replace('{NAZWA_PRODUKTU}', (string)$wyslanie['produkt_nazwa'], (string)Funkcje::parsujZmienne($tresc['email_title']));

                    $tekst           = str_replace('{NAZWA_PRODUKTU}', (string)$wyslanie['produkt_nazwa'], (string)$tresc['description']);
                    $tekst           = str_replace('{LINK}','<a href="' . Seo::link_SEO( ((!empty($wyslanie['seo'])) ? $wyslanie['seo'] : $wyslanie['produkt_nazwa']), $wyslanie['produkt_id'], 'produkt', '', false ) . '">' . $wyslanie['produkt_nazwa'] . '</a>', (string)$tekst);                    
                    $tekst           = Funkcje::parsujZmienne($tekst);
                    
                    $zalaczniki      = array();
                    $szablon         = $tresc['template_id'];
                    $jezyk           = $_SESSION['domyslny_jezyk']['id'];  

                    $email->wyslijEmail($nadawca_email, $nadawca_nazwa, $adresat_email, $adresat_nazwa, $cc, $temat, $tekst, $szablon, $jezyk, array());

                    $db->close_query($sql);
                    
                    unset($tresc, $zapytanie_tresc, $nadawca_email, $nadawca_nazwa, $adresat_email, $adresat_nazwa, $cc, $temat, $tekst, $szablon, $jezyk); 
                    
                    // usuwa wpis z bazy
                    $db->delete_query('products_notifications' , " products_notifications_id = '" . $wyslanie['id'] . "'");

                }
                
            }
            
            Funkcje::PrzekierowanieURL('klienci_powiadomienia_wyslij.php?wyslano');
            
        }
            
    }    
    
    ?>
    
    <div id="naglowek_cont">Wysyłanie powiadomień o dostępności produktów</div>
    <div id="cont">
          
          <form action="klienci/klienci_powiadomienia_wyslij.php" method="post" class="cmxform"> 

          <input type="hidden" name="akcja" value="zapisz" />          

          <div class="poleForm">
            <div class="naglowek">Wysyłanie powiadomień o dostępności produktów</div>

            <div class="pozycja_edytowana">
            
                <?php if (!isset($_GET['wyslano']) ) { ?>
            
                <div id="DanePowiadomien">

                    <div <?php echo ((count($tablica_email) > 0) ? 'style="margin-bottom:25px"' : ''); ?>>
                    
                        Ilość maili do wysłania: <span><?php echo count($tablica_email); ?></span>
                        
                        <?php if (count($tablica_email) > 0) { ?>
                        
                        <span class="maleInfo" style="font-weight:normal;margin:0px">Po wysłaniu wiadomości pozycje zostaną usunięte</span>
                        
                        <?php } ?>
                        
                    </div>
                                        
                    <?php if (count($tablica_email) > 0) { ?>
                    
                        <div>
                            Powiadomienia zostaną wysłane dla produktów:
                        </div>                    
                        
                        <?php
                        $lista_produktow = array();
                        //
                        foreach ( $tablica_email as $wyslanie ) {
                            //
                            if ( !in_array((string)$wyslanie['produkt_id'] . '_' . $wyslanie['cechy'], $lista_produktow) ) {
                                //
                                $wyswietl_cechy = array();
                                //
                                if ( $wyslanie['cechy'] != '' ) {
                                
                                    $tablica_kombinacji_cech = explode(',', (string)$wyslanie['cechy']);
                                    
                                    for ( $t = 0, $c = count($tablica_kombinacji_cech); $t < $c; $t++ ) {
                                    
                                      $tablica_wartosc_cechy = explode('-', (string)$tablica_kombinacji_cech[$t]);
                                      
                                      $wyswietl_cechy[] = Funkcje::NazwaCechy( (int)$tablica_wartosc_cechy['0'] ) . ': ' . Funkcje::WartoscCechy( (int)$tablica_wartosc_cechy['1'] );
                                      
                                      unset($tablica_wartosc_cechy);
                                      
                                    }
                                    
                                    unset($tablica_kombinacji_cech);                        
                                    
                                }
                                //
                                ?>
                                <input id="mail_<?php echo $wyslanie['id']; ?>" value="<?php echo $wyslanie['produkt_id'] . '_' . $wyslanie['cechy']; ?>" name="produkt[]" type="checkbox" checked="checked" />
                                <label class="OpisFor" for="mail_<?php echo $wyslanie['id']; ?>"><?php echo $wyslanie['produkt_nazwa'] . ((count($wyswietl_cechy) > 0) ? ' <span class="InfoOpisCecha"> - ' . implode(', ', (array)$wyswietl_cechy) . ' </span>' : ''); ?></label> <br />                      
                                <?php
                                //
                                unset($wyswietl_cechy);
                                //
                                $lista_produktow[] = $wyslanie['produkt_id'] . '_' . $wyslanie['cechy'];
                                //
                            }
                        }
                        //
                        unset($lista_produktow);
                        ?>
                        
                    <?php } ?>
                    
                </div>
                
                <?php } else { ?>

                <div class="MailWyslano">
                    Powiadomienia zostały wysłane do klientów ...
                </div>
                
                <?php } ?>
                
            </div>
            
            <div class="przyciski_dolne" id="przyciski">
            
              <?php if (count($tablica_email) > 0 && !isset($_GET['wyslano'])) { ?>
              <input type="submit" class="przyciskNon" value="Wyślij powiadomienia" />
              <?php } ?>
              
              <button type="button" class="przyciskNon" onclick="cofnij('klienci_powiadomienia','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','klienci');">Powrót</button> 
            </div>

          </div>                      
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}