<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {
  
        $zapytanie = "select distinct
                             cp.cp_id,
                             cp.cp_groups_id,
                             cp.cp_customers_id,
                             cp.cp_products_id,
                             cp.cp_price,
                             cp.cp_price_tax,
                             cp.cp_tax,
                             cg.customers_groups_id,
                             cg.customers_groups_name,
                             p.products_id,
                             p.products_model,
                             p.products_price, 
                             p.products_price_tax,                          
                             pd.products_name,
                             cu.customers_id,
                             cu.customers_firstname,
                             cu.customers_lastname,
                             cu.customers_email_address,
                             cu.customers_default_address_id,
                             a.entry_company
                        FROM customers_price cp
                             LEFT JOIN customers_groups cg ON cp.cp_groups_id = cg.customers_groups_id
                             LEFT JOIN products_description pd ON cp.cp_products_id = pd.products_id AND pd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'
                             LEFT JOIN products p ON cp.cp_products_id = p.products_id
                             LEFT JOIN customers cu ON cp.cp_customers_id = cu.customers_id
                             LEFT JOIN address_book a on cu.customers_id = a.customers_id and cu.customers_default_address_id = a.address_book_id ";  

        $sql = $db->open_query($zapytanie);
        
        //
        if ((int)$db->ile_rekordow($sql) > 0) {
          
            $ciag_do_zapisu = 'Id;Id_produktu;Nazwa_produktu;Nr_katalogowy;Cena_netto;Cena_brutto;Cena_indywidualna_netto;Cena_indywidualna_brutto;Grupa_klientow;Klient' . "\n";
        
            while ($info = $sql->fetch_assoc()) {
              
                $ciag_do_zapisu .= $info['cp_id'] . ";";
                $ciag_do_zapisu .= $info['products_id'] . ";";
                $ciag_do_zapisu .= Funkcje::CzyszczenieTekstu($info['products_name']) . ";";
                $ciag_do_zapisu .= Funkcje::CzyszczenieTekstu($info['products_model']) . ";";
                $ciag_do_zapisu .= $info['products_price'] . ";";
                $ciag_do_zapisu .= $info['products_price_tax'] . ";";                
                $ciag_do_zapisu .= $info['cp_price'] . ";";
                $ciag_do_zapisu .= $info['cp_price_tax'] . ";";
                $ciag_do_zapisu .= Funkcje::CzyszczenieTekstu($info['customers_groups_name']) . ";";
                
                $wyswietlana_nazwa = '';
                if (!empty($info['customers_lastname'])) {
                   $wyswietlana_nazwa = '';
                   if ( $info['entry_company'] != '' ) {
                      $wyswietlana_nazwa = $info['entry_company'] . ', ';
                   }                  
                   $wyswietlana_nazwa .= $info['customers_firstname'] . ' ' . $info['customers_lastname'] . ', ' . $info['customers_email_address'];
                }                
                
                $ciag_do_zapisu .= Funkcje::CzyszczenieTekstu($wyswietlana_nazwa) . "\n";
                
            }

            header("Content-Type: application/force-download\n");
            header("Cache-Control: cache, must-revalidate");   
            header("Pragma: public");
            header("Content-Disposition: attachment; filename=eksport_indywidualne_ceny_" . date("d-m-Y") . ".csv");
            print $ciag_do_zapisu;
            exit;     
            
        }
        
        $db->close_query($sql);        

}