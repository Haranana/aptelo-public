<?php

class Zamowienie {

  public $info;
  public $podsumowanie;
  public $produkty;
  public $klient;
  public $dostawa;
  public $platnik;
  public $statusy;
  public $dostawy;
  public $dostawy_nr_przesylki;
  public $dostawy_link_sledzenia;
  public $sprzedaz_online;
  public $sprzedaz_online_pliki;
  public $sprzedaz_online_link;
  public $sprzedaz_online_kody;
  public $sprzedaz_online_kody_lista;
  public $waga_produktow;
  public $link_opinii;
  public $link_recenzji;
  public $zwrot;

  function __construct($id_zamowienia, $sortowanie = '') {
  
    $this->info                       = array();
    $this->podsumowanie               = array();
    $this->produkty                   = array();
    $this->klient                     = array();
    $this->dostawa                    = array();
    $this->platnik                    = array();
    $this->statusy                    = array();
    $this->dostawy                    = array();
    $this->dostawy_nr_przesylki       = '';
    $this->dostawy_link_sledzenia     = '';
    $this->sprzedaz_online            = false;
    $this->sprzedaz_online_pliki      = array();
    $this->sprzedaz_online_link       = '/';
    $this->sprzedaz_online_kody       = false; 
    $this->sprzedaz_online_kody_lista = array();
    $this->waga_produktow             = 0;
    $this->link_opinii                = '';
    $this->link_recenzji              = array();
    $this->zwrot                      = false;
    
    $this->zapytanie($id_zamowienia, $sortowanie);
    
  }

  function zapytanie($id_zamowienia, $sortowanie) {

     $zapytanie_suma = "SELECT title, text, class, tax, tax_class_id, value, sort_order, orders_total_id, prefix, gtu FROM orders_total WHERE orders_id = '" . (int)$id_zamowienia . "' ORDER BY sort_order";
     $sql_suma = $GLOBALS['db']->open_query($zapytanie_suma);
     
     $ogolna_wartosc_zamowienia = 0;
     $wartosc_zamowienia        = 0;
     $vat_domyslny              = Funkcje::domyslnyPodatekVat(); 

     while ($info_suma = $sql_suma->fetch_assoc()) {
     
        // jezeli pozycja nie ma vat id to przypisze domyslny
        if ( (int)$info_suma['tax_class_id'] == 0 ) {
            //
            $info_suma['tax_class_id'] = $vat_domyslny['id'];
            $info_suma['tax'] = $vat_domyslny['stawka'];
            //
        }
     
        $tablica_vat = Produkty::PokazStawkeVAT($info_suma['tax_class_id'], true); 
            
        $this->podsumowanie[] = array('tytul'           => $info_suma['title'],
                                      'tekst'           => $info_suma['text'],
                                      'klasa'           => $info_suma['class'],
                                      'wartosc'         => $info_suma['value'],
                                      'vat_id'          => $info_suma['tax_class_id'],
                                      'vat_stawka'      => $info_suma['tax'],
                                      'vat_info'        => ((isset($tablica_vat['opis_krotki'])) ? $tablica_vat['opis_krotki'] : ''),                                      
                                      'kod_gtu'         => $info_suma['gtu'],
                                      'sortowanie'      => $info_suma['sort_order'],                                      
                                      'orders_total_id' => $info_suma['orders_total_id'],
                                      'prefix'          => $info_suma['prefix']);
                                
        if ($info_suma['class'] == 'ot_total') {
            $ogolna_wartosc_zamowienia = strip_tags((string)$info_suma['text']);
            $wartosc_zamowienia        = $info_suma['value'];
        }
        
        unset($tablica_vat);
        
     }
     
     $GLOBALS['db']->close_query($sql_suma);
     
     unset($zapytanie_suma,$info_suma, $vat_domyslny);

     $zapytanie_zamowienie = "SELECT orders_id, customers_id, currency, currency_value, payment_method, payment_info, payment_method_class, payment_method_array, p24_session_id, invoice_dokument, shipping_tax, date_purchased, orders_status, last_modified, shipping_module, shipping_module_id, shipping_info, reference, tracker_ip, device, service, customers_name, customers_company, customers_nip, customers_street_address, customers_city, customers_postcode, customers_state, customers_country, customers_telephone, customers_email_address, customers_dummy_account, delivery_name, delivery_company, delivery_nip, delivery_pesel, delivery_street_address, delivery_city, delivery_postcode, delivery_state, delivery_country, delivery_telephone, billing_name, billing_company, billing_nip, billing_pesel, billing_street_address,  billing_city, billing_postcode, billing_state, billing_country, orders_source, orders_file_shopping, automater_id_cart, automater_id_cart_send, shipping_date FROM orders WHERE orders_id = '" . (int)$id_zamowienia . "'";
     $sql_zamowienie = $GLOBALS['db']->open_query($zapytanie_zamowienie);
     
     if ((int)$GLOBALS['db']->ile_rekordow($sql_zamowienie) == 0) {
         return false;
     }
       
     $info_zamowienie = $sql_zamowienie->fetch_assoc();

     // domyslny jak klient nie ma jezyka                    
     $jezyk = '1';
     $zapytanie_jezyk = "SELECT c.customers_id, c.language_id, c.customers_id_private
                         FROM customers c WHERE c.customers_id = '".(int)$info_zamowienie['customers_id']."'";
                  
     $sql_jezyk = $GLOBALS['db']->open_query($zapytanie_jezyk);
     
     if ((int)$GLOBALS['db']->ile_rekordow($sql_jezyk) > 0) {
     
       $info_jezyk = $sql_jezyk->fetch_assoc();
       $jezyk = $info_jezyk['language_id'];
       $nrKlientaMagazyn = $info_jezyk['customers_id_private'];
       unset($info_jezyk);
       
     }
     
     $GLOBALS['db']->close_query($sql_jezyk);
     unset($zapytanie_jezyk);     

     // nazwa dokumentu sprzedazy
     $dokument = (($info_zamowienie['invoice_dokument'] == 0) ? 'DOKUMENT_SPRZEDAZY_PARAGON' : 'DOKUMENT_SPRZEDAZY_FAKTURA');
     $zapytanie_dokument = "select tv.translate_value, tc.translate_constant_id from translate_constant tc, translate_value tv where tv.translate_constant_id = tc.translate_constant_id and tc.translate_constant = '" . $dokument . "' and tv.language_id = '" . $jezyk . "'";
     
     $sql_dokument = $GLOBALS['db']->open_query($zapytanie_dokument);
     
     $info_dokument = $sql_dokument->fetch_assoc();
     $dokument_nazwa = $info_dokument['translate_value'];
     unset($info_dokument); 

     $GLOBALS['db']->close_query($sql_dokument);
     unset($info_dokument, $dokument);       
     
     // link do opinii do maila
     $this->link_opinii = base64_encode(serialize(array('id' => $info_zamowienie['orders_id'], 'czas' => FunkcjeWlasnePHP::my_strtotime($info_zamowienie['date_purchased']))));

     $this->info = array('id_zamowienia'           => $info_zamowienie['orders_id'],
                         'waluta'                  => $info_zamowienie['currency'],
                         'waluta_kurs'             => $info_zamowienie['currency_value'],
                         'metoda_platnosci'        => $info_zamowienie['payment_method'],
                         'platnosc_info'           => $info_zamowienie['payment_info'],
                         'platnosc_klasa'          => $info_zamowienie['payment_method_class'],
                         'platnosc_tablica'        => $info_zamowienie['payment_method_array'],
                         'p24_session_id'          => $info_zamowienie['p24_session_id'],
                         'dokument_zakupu'         => $info_zamowienie['invoice_dokument'],
                         'dokument_zakupu_nazwa'   => $dokument_nazwa,
                         'wysylka_vat'             => $info_zamowienie['shipping_tax'],
                         'data_zamowienia'         => $info_zamowienie['date_purchased'],
                         'status_zamowienia'       => $info_zamowienie['orders_status'],
                         'data_modyfikacji'        => $info_zamowienie['last_modified'],
                         'data_wysylki'            => $info_zamowienie['shipping_date'],
                         'wysylka_modul'           => $info_zamowienie['shipping_module'],
                         'wysylka_modul_id'        => $info_zamowienie['shipping_module_id'],
                         'wysylka_info'            => $info_zamowienie['shipping_info'],
                         'referer'                 => $info_zamowienie['reference'],
                         'adres_ip'                => $info_zamowienie['tracker_ip'],
                         'urzadzenie'              => $info_zamowienie['device'],
                         'opiekun'                 => $info_zamowienie['service'],
                         'typ_zamowienia'          => $info_zamowienie['orders_source'],
                         'wartosc_zamowienia'      => $ogolna_wartosc_zamowienia,
                         'wartosc_zamowienia_val'  => $wartosc_zamowienia,
                         'ilosc_pobran_plikow'     => $info_zamowienie['orders_file_shopping'],                         
                         'automater_id_cart'       => $info_zamowienie['automater_id_cart'],
                         'automater_wyslane'       => $info_zamowienie['automater_id_cart_send']);

     $this->klient = array('nazwa'                => trim((string)preg_replace('!\s+!', ' ', (string)$info_zamowienie['customers_name'])),
                           'id'                   => $info_zamowienie['customers_id'],
                           'id_klienta_magazyn'   => (isset($nrKlientaMagazyn) ? $nrKlientaMagazyn : ''),
                           'firma'                => $info_zamowienie['customers_company'],
                           'nip'                  => $info_zamowienie['customers_nip'],
                           'ulica'                => $info_zamowienie['customers_street_address'],
                           'miasto'               => $info_zamowienie['customers_city'],
                           'kod_pocztowy'         => $info_zamowienie['customers_postcode'],
                           'wojewodztwo'          => $info_zamowienie['customers_state'],
                           'kraj'                 => $info_zamowienie['customers_country'],
                           'telefon'              => $info_zamowienie['customers_telephone'],
                           'adres_email'          => $info_zamowienie['customers_email_address'],
                           'gosc'                 => $info_zamowienie['customers_dummy_account'],
                           'jezyk'                => $jezyk);
                           
     unset($jezyk);

     $this->dostawa = array('nazwa'               => $info_zamowienie['delivery_name'],
                            'firma'               => $info_zamowienie['delivery_company'],
                            'nip'                 => $info_zamowienie['delivery_nip'],
                            'pesel'               => $info_zamowienie['delivery_pesel'],
                            'ulica'               => $info_zamowienie['delivery_street_address'],
                            'miasto'              => $info_zamowienie['delivery_city'],
                            'kod_pocztowy'        => $info_zamowienie['delivery_postcode'],
                            'wojewodztwo'         => $info_zamowienie['delivery_state'],
                            'kraj'                => $info_zamowienie['delivery_country'],
                            'telefon'             => $info_zamowienie['delivery_telephone']);

     $this->platnik = array('nazwa'               => $info_zamowienie['billing_name'],
                            'firma'               => $info_zamowienie['billing_company'],
                            'nip'                 => $info_zamowienie['billing_nip'],
                            'pesel'               => $info_zamowienie['billing_pesel'],
                            'ulica'               => $info_zamowienie['billing_street_address'],
                            'miasto'              => $info_zamowienie['billing_city'],
                            'kod_pocztowy'        => $info_zamowienie['billing_postcode'],
                            'wojewodztwo'         => $info_zamowienie['billing_state'],
                            'kraj'                => $info_zamowienie['billing_country']);
                            
     $GLOBALS['db']->close_query($sql_zamowienie);
     unset($info_zamowienie, $sql_zamowienie, $zapytanie_zamowienie, $nrKlientaMagazyn);                               

     $zapytanie_dostawy = "SELECT orders_shipping_id, orders_shipping_type, orders_shipping_number, orders_shipping_link, orders_shipping_weight, orders_parcels_quantity, orders_shipping_status, orders_shipping_date_created, orders_shipping_date_modified, orders_shipping_comments FROM orders_shipping WHERE orders_id = '" . (int)$id_zamowienia . "'";

     $sql_dostawy = $GLOBALS['db']->open_query($zapytanie_dostawy);

     if ((int)$GLOBALS['db']->ile_rekordow($sql_dostawy) > 0) {
     
       while ($info_dostawy = $sql_dostawy->fetch_assoc()) {

         $index_dostaw = $info_dostawy['orders_shipping_id'];
         $this->dostawy[$index_dostaw] = array('rodzaj_przesylki'     => $info_dostawy['orders_shipping_type'],
                                               'numer_przesylki'      => $info_dostawy['orders_shipping_number'],
                                               'link_sledzenia'       => $info_dostawy['orders_shipping_type'] . ' <a href="' . $info_dostawy['orders_shipping_link'] . '" target="_blank"><strong>' . $info_dostawy['orders_shipping_number'] . '</strong></a>',
                                               'waga_przesylki'       => $info_dostawy['orders_shipping_weight'],
                                               'ilosc_paczek'         => $info_dostawy['orders_parcels_quantity'],
                                               'status_przesylki'     => $info_dostawy['orders_shipping_status'],
                                               'data_utworzenia'      => $info_dostawy['orders_shipping_date_created'],
                                               'data_aktualizacji'    => $info_dostawy['orders_shipping_date_modified'],
                                               'komentarz'            => $info_dostawy['orders_shipping_comments']);
                                              
         // jezeli nie xml DHL
         if ( !strpos((string)$info_dostawy['orders_shipping_number'], '.xml') ) {
              //
              $this->dostawy_nr_przesylki = $info_dostawy['orders_shipping_number'];
              if ( $info_dostawy['orders_shipping_link'] != '' ) {
                   $this->dostawy_link_sledzenia = $info_dostawy['orders_shipping_link'];
              } else {
                   $this->dostawy_link_sledzenia = '';
              }
              //
         }
         
       }
       
       unset($info_dostawy);
       
     }
     
     $GLOBALS['db']->close_query($sql_dostawy);
     unset($sql_dostawy, $zapytanie_dostawy);           

     // sortowanie produktow 
     $sortowanieProduktow = ' ORDER BY op.products_name';

     if ( $sortowanie == '' ) {
          //
          $sortowanie = PDF_ZAMOWIENIE_SORTOWANIE_PRODUKTOW;
          //
     }
     
     switch ($sortowanie) {
        case 'nazwa produktu':
            $sortowanieProduktow = ' ORDER BY op.products_name';                             
            break; 
        case 'cena':
            $sortowanieProduktow = ' ORDER BY op.final_price_tax';                           
            break; 
        case 'numer katalogowy':
            $sortowanieProduktow = ' ORDER BY op.products_model';                    
            break;
        case 'kod producenta':
            $sortowanieProduktow = ' ORDER BY op.products_man_code';                    
            break;   
        case 'kod EAN':
            $sortowanieProduktow = ' ORDER BY op.products_ean';                    
            break;                    
        case 'ilosc':
            $sortowanieProduktow = ' ORDER BY op.products_quantity, op.products_name';                    
            break;                  
        case 'id produktu':
            $sortowanieProduktow = ' ORDER BY op.products_id';                    
            break;                  
        case 'brak sortowania':
            $sortowanieProduktow = ' ORDER BY op.orders_products_id';                      
            break;                  
     }           

     $zapytanie_produkty = "SELECT
                            op.orders_products_id,
                            op.products_id,
                            op.products_name,
                            op.products_model,
                            op.products_man_code,
                            op.products_ean as ean_cechy,
                            op.products_pkwiu,
                            op.products_gtu,
                            op.products_price,
                            op.products_price_tax,
                            op.products_tax,
                            op.products_tax_class_id,
                            op.products_quantity,
                            op.final_price,
                            op.final_price_tax,
                            op.products_id,
                            op.orders_id,
                            op.products_comments,
                            op.products_text_fields,
                            op.products_price_points,
                            op.products_shipping_time,
                            op.products_warranty,
                            op.products_condition,
                            op.products_code_shopping,
                            op.products_image as zdjecie_produktu_zamowienie,
                            p.products_weight,
                            p.products_image,
                            p.products_id_private,
                            p.manufacturers_id,
                            p.products_currencies_id,
                            p.products_jm_id,
                            p.products_ean,
                            p.products_warranty_products_id,
                            p.products_condition_products_id,
                            p.products_shipping_time_id,
                            p.products_code_shopping as kody_elektroniczne_wszystkie,
                            p.automater_products_id,
                            p.products_type,
                            p2c.categories_id,
                            m.manufacturers_name
                            FROM orders_products op
                            LEFT JOIN products p ON op.products_id = p.products_id
                            LEFT JOIN products_to_categories p2c ON op.products_id = p2c.products_id
                            LEFT JOIN manufacturers m ON p.manufacturers_id = m.manufacturers_id
                            WHERE orders_id = '" . (int)$id_zamowienia . "'" . $sortowanieProduktow;
                            
     unset($sortowanieProduktow);                              
     
     // tablica unikalnych id - dla okreslenia wagi
     $waga_tablica = array();
     
     $sql_produkty = $GLOBALS['db']->open_query($zapytanie_produkty);
     
     while ($info_produkty = $sql_produkty->fetch_assoc()) {

        $index = $info_produkty['orders_products_id'];
        $calkowita_ilosc = false;
        // 
        // okresla czy z przecinkiem czy bez
        if ( isset( $GLOBALS['jednostkiMiary'][ $info_produkty['products_jm_id'] ] ) ) {
             //
             // jezeli calkowite
             if ( $GLOBALS['jednostkiMiary'][ $info_produkty['products_jm_id'] ]['typ'] == 1 ) {
                  //
                  $info_produkty['products_quantity'] = (int)$info_produkty['products_quantity'];
                  $calkowita_ilosc = true;
                  //
             }
             //
        } else {
             //
             if ( isset($GLOBALS['jednostkiMiary'][0]) ) { 
                  //
                  if ( $GLOBALS['jednostkiMiary'][0]['typ'] == 1 ) {
                       //
                       // sprawdzi czy wartosc ilosci nie jest ulamkowa
                      if ( (int)$info_produkty['products_quantity'] == $info_produkty['products_quantity'] ) {
                            $info_produkty['products_quantity'] = (int)$info_produkty['products_quantity'];
                            $calkowita_ilosc = true;
                       }
                       //
                  }
                  //
             }
             //
        }
        
        // jezeli nie ma id stawki vat
        if ( $info_produkty['products_tax_class_id'] == 0 ) {
            //
            $sql_tmp = $GLOBALS['db']->open_query("SELECT * FROM tax_rates WHERE tax_rate = '" . $info_produkty['products_tax'] . "'");
            if ((int)$GLOBALS['db']->ile_rekordow($sql_tmp) > 0) {
              while ($info_tmp = $sql_tmp->fetch_assoc()) {
                  $info_produkty['products_tax_class_id'] = $info_tmp['tax_rates_id'];
              }
            }
            $GLOBALS['db']->close_query($sql_tmp);
            unset($info_tmp);               
            //
        }        


        // jezeli id produktu = 0 - reczne dodanie
        if ( $info_produkty['products_id'] == 0 ) {
            //
            // ustali id waluty
            $info_produkty['products_currencies_id'] = $_SESSION['domyslnaWaluta']['id'];
        }     
        
        $tablica_vat = Produkty::PokazStawkeVAT($info_produkty['products_tax_class_id'], true);    
        
        //
        $this->produkty[$index] = array('ilosc'               => $info_produkty['products_quantity'],
                                        'wartosc_calkowita'   => $calkowita_ilosc,
                                        'nazwa'               => $info_produkty['products_name'],
                                        'link'                => (($info_produkty['products_id'] > 0) ? '<a href="' . Seo::link_SEO( $info_produkty['products_name'], $info_produkty['products_id'], 'produkt' ) . '" title="' . str_replace('"', '', (string)$info_produkty['products_name']) . '">' . $info_produkty['products_name'] . '</a>' : '<a title="' . str_replace('"', '', (string)$info_produkty['products_name']) . '">' . $info_produkty['products_name'] . '</a>'),
                                        'adres_url'           => (($info_produkty['products_id'] > 0) ? Seo::link_SEO( $info_produkty['products_name'], $info_produkty['products_id'], 'produkt' ) : ''),
                                        'zdjecie_produktu'    => ((!empty($info_produkty['zdjecie_produktu_zamowienie'])) ? Funkcje::pokazObrazek($info_produkty['zdjecie_produktu_zamowienie'], $info_produkty['products_name'], '40', '40', array(), 'class="Zdjecie"', 'maly') : Funkcje::pokazObrazek($info_produkty['products_image'], $info_produkty['products_name'], '40', '40', array(), 'class="Zdjecie"', 'maly')),
                                        'zdjecie'             => ((!empty($info_produkty['zdjecie_produktu_zamowienie'])) ? $info_produkty['zdjecie_produktu_zamowienie'] : $info_produkty['products_image']),
                                        'products_id'         => $info_produkty['products_id'],
                                        'id_produktu'         => $info_produkty['products_id'],
                                        'id_produktu_magazyn' => $info_produkty['products_id_private'],
                                        'model'               => $info_produkty['products_model'],
                                        'kod_producenta'      => $info_produkty['products_man_code'],
                                        'pkwiu'               => $info_produkty['products_pkwiu'],
                                        'gtu'                 => $info_produkty['products_gtu'],
                                        'ean'                 => ((!empty($info_produkty['ean_cechy'])) ? $info_produkty['ean_cechy'] : $info_produkty['products_ean']),
                                        'gwarancja'           => ( $info_produkty['products_warranty'] != '' ? $info_produkty['products_warranty'] : '' ),
                                        'stan'                => ( $info_produkty['products_condition'] != ''  ? $info_produkty['products_condition'] : '' ),
                                        'czas_wysylki'        => ( $info_produkty['products_shipping_time'] != '' ? $info_produkty['products_shipping_time'] : '' ),
                                        'jm'                  => ( $info_produkty['products_jm_id'] == '' || $info_produkty['products_jm_id'] == '0' ? $GLOBALS['jednostkiMiary'][0]['id'] : $info_produkty['products_jm_id']),
                                        'tax'                 => $info_produkty['products_tax'],
                                        'tax_id'              => $info_produkty['products_tax_class_id'],
                                        'tax_info'            => ((isset($tablica_vat['opis_krotki'])) ? $tablica_vat['opis_krotki'] : ''),
                                        'cena_netto'          => $info_produkty['products_price'],
                                        'cena_koncowa_netto'  => $info_produkty['final_price'],
                                        'cena_brutto'         => $info_produkty['products_price_tax'],
                                        'cena_koncowa_brutto' => $info_produkty['final_price_tax'],
                                        'cena_punkty'         => $info_produkty['products_price_points'],
                                        'weight'              => $info_produkty['products_weight'],
                                        'komentarz'           => $info_produkty['products_comments'],
                                        'pola_txt'            => $info_produkty['products_text_fields'],
                                        'producent'           => $info_produkty['manufacturers_name'],
                                        'orders_products_id'  => $info_produkty['orders_products_id'],
                                        'id_waluty'           => $info_produkty['products_currencies_id'],
                                        'kody_elektroniczne'  => $info_produkty['products_code_shopping'],
                                        'kody_elektroniczne_wszystkie' => $info_produkty['kody_elektroniczne_wszystkie'],
                                        'automater_id'        => $info_produkty['automater_products_id'],
                                        'id_kategorii'        => $info_produkty['categories_id'],
                                        'rodzaj_produktu'     => (($info_produkty['products_id'] > 0) ? $info_produkty['products_type'] : 'standard')
        );
                                        
        unset($tablica_vat);

        if ( !isset($waga_tablica[$index]) ) {
             //
             $this->waga_produktow += $info_produkty['products_weight'] * $info_produkty['products_quantity'];
             //
        }

        $zapytanie_cechy = "SELECT distinct pa.products_options,
                                   pa.products_options_id,
                                   pa.products_options_values,
                                   pa.products_options_values_id,
                                   pa.price_prefix,
                                   pa.options_values_price,
                                   pa.options_values_tax,
                                   pa.options_values_price_tax,
                                   pa.orders_products_attributes_id,
                                   p.options_values_weight
                            FROM orders_products_attributes pa
                            LEFT JOIN products_options po ON pa.products_options_id = po.products_options_id AND po.language_id = '" . (int)$_SESSION['domyslnyJezyk']['id'] . "'
                            LEFT JOIN products_attributes p ON pa.products_options_id = p.options_id AND p.options_values_id = pa.products_options_values_id AND p.products_id = (SELECT products_id FROM orders_products WHERE orders_products_id = pa.orders_products_id)
                            WHERE pa.orders_id = '" . (int)$id_zamowienia . "'
                            AND pa.orders_products_id = '" . (int)$info_produkty['orders_products_id'] . "' ORDER BY po.products_options_sort_order asc ";
    
        $sql_cechy = $GLOBALS['db']->open_query($zapytanie_cechy);

        if ((int)$GLOBALS['db']->ile_rekordow($sql_cechy) > 0) {
        
          while ($info_cechy = $sql_cechy->fetch_assoc()) {

            $subindex = $info_cechy['products_options_id'];

            $this->produkty[$index]['attributes'][$subindex] = array('cecha'                         => $info_cechy['products_options'],
                                                                     'id_cechy'                      => $info_cechy['products_options_id'],
                                                                     'wartosc'                       => $info_cechy['products_options_values'],
                                                                     'id_wartosci'                   => $info_cechy['products_options_values_id'],
                                                                     'prefix'                        => $info_cechy['price_prefix'],
                                                                     'cena_netto'                    => $info_cechy['options_values_price'],
                                                                     'podatek'                       => $info_cechy['options_values_tax'],
                                                                     'cena_brutto'                   => $info_cechy['options_values_price_tax'],
                                                                     'orders_products_attributes_id' => $info_cechy['orders_products_attributes_id']);

            if ( !isset($waga_tablica[$index]) ) {
                 //
                 $this->waga_produktow += $info_cechy['options_values_weight'] * $info_produkty['products_quantity'];
                 //
            }
            
            $subindex++;
            
          }
          
        }

        $waga_tablica[$index] = $info_produkty['products_weight'] * $info_produkty['products_quantity'];
        
        $GLOBALS['db']->close_query($sql_cechy);
        unset($info_cechy, $sql_cechy, $zapytanie_cechy);           
        
        // sprzedaz elektroniczna - pliki
        $zapytanie_online = "SELECT products_file_shopping_unique_id, products_file_shopping_name, products_file_shopping 
                               FROM products_file_shopping
                              WHERE products_id = '" . (int)$info_produkty['products_id'] . "' and language_id = '" . (int)$_SESSION['domyslnyJezyk']['id'] . "'";

        $sql_online = $GLOBALS['db']->open_query($zapytanie_online); 

        if ((int)$GLOBALS['db']->ile_rekordow($sql_online) > 0) {
        
          $this->sprzedaz_online = true;
        
          while ($info_online = $sql_online->fetch_assoc()) {

            // sprawdzi czy plik istnieje
            if ( file_exists($info_online['products_file_shopping']) ) { 
                //
                $this->sprzedaz_online_pliki[] = array('plik'        => $info_online['products_file_shopping'],
                                                       'nazwa_pliku' => $info_online['products_file_shopping_name'],
                                                       'id_pliku'    => $info_online['products_file_shopping_unique_id']);
                //
            }

          }
          
        }

        // sprzedaz elektroniczna - kody
        if ( $info_produkty['kody_elektroniczne_wszystkie'] != '' ) {
          
           $this->sprzedaz_online_kody = true; 
             
        }
        
        if ( $info_produkty['products_code_shopping'] != '' ) {

          $this->sprzedaz_online_kody_lista[ $info_produkty['orders_products_id'] ] = array('nazwa_produktu' => $info_produkty['products_name'],
                                                                                            'adres_url' => $this->produkty[$index]['adres_url'],
                                                                                            'kody' => $info_produkty['products_code_shopping']);
          
        }                
        
        // link do recenzji do maila
        $this->link_recenzji[(int)$info_produkty['products_id']] = base64_encode(serialize(array('id' => $this->info['id_zamowienia'], 'czas' => FunkcjeWlasnePHP::my_strtotime($this->info['data_zamowienia']), 'klient' => $this->klient['id'], 'produkt' => (int)$info_produkty['products_id'])));       

        $GLOBALS['db']->close_query($sql_online);
        unset($info_online, $sql_online, $zapytanie_online);           
                              
     }
     
     unset($waga_tablica);

     // generowanie linku do pobrania sprzedazy elektronicznej
     if ( $this->sprzedaz_online == true || $this->sprzedaz_online_kody == true ) {
          //
          $UnikalnyLinkTablica = array( 'nr_zamowienia'   => (int)$id_zamowienia,
                                        'adres_email'     => $this->klient['adres_email'],
                                        'data_zamowienia' => $this->info['data_zamowienia'] );
          //
          $UnikalnyLink = base64_encode(serialize($UnikalnyLinkTablica));
          $UnikalnyLinkWynik = '';
          
          $Wstawki = array(10,20,35,40,52,60,71,92);
          $WstawkiZnaki = array('bbb','nnn','ttt','RRR','WWW','QQQ','OOO','ppp','VVV','NNN','ccc');
          
          for ( $r = 0; $r <= strlen((string)$UnikalnyLink); $r++ ) {
                $UnikalnyLinkWynik .= substr((string)$UnikalnyLink, strlen((string)$UnikalnyLink) - $r, 1);
                //
                if ( in_array($r, $Wstawki) ) {
                     $UnikalnyLinkWynik .= $WstawkiZnaki[ rand(0,9) ];
                }
                //
          }
          
          unset($Wstawki, $WstawkiZnaki);
          //
          $this->sprzedaz_online_link = 'AAV' . $UnikalnyLinkWynik . '-d-' . (int)$id_zamowienia . '.html';
          //
          // dodanie linku do plikow
          for ( $f = 0; $f < count($this->sprzedaz_online_pliki); $f++ ) {
              //
              $KodowanyIdPliku = $this->sprzedaz_online_pliki[$f]['id_pliku'] * $this->sprzedaz_online_pliki[$f]['id_pliku'];
              //
              $TablicaCyfr = array(1,2,3,4,5,6,7,8,9,0);
              $TablicaLiter = array('b','v','q','w','c','g','z','t','u','d');
              $IdWynik = str_replace($TablicaCyfr, $TablicaLiter, (string)$KodowanyIdPliku);
              //
              $this->sprzedaz_online_pliki[$f]['plik_pobrania'] = $this->sprzedaz_online_link . '/pobierz=' . $IdWynik;
              //
              unset($KodowanyIdPliku, $TablicaCyfr, $TablicaLiter, $IdWynik);
              //
          }
          //
     }
     
     $GLOBALS['db']->close_query($sql_produkty);
     unset($info_produkty, $sql_produkty, $zapytanie_produkty);        
     
     // usuwanie duplikatow plikow z tablicy plikow elektronicznych
     $this->sprzedaz_online_pliki = Funkcje::CzyscTabliceUnikalne($this->sprzedaz_online_pliki); 

     $zapytanie_statusy = "SELECT orders_status_history_id, orders_status_id, date_added, customer_notified, customer_notified_sms, comments FROM orders_status_history WHERE orders_id = '" . (int)$id_zamowienia . "' ORDER BY date_added DESC";
     $sql_statusy = $GLOBALS['db']->open_query($zapytanie_statusy);

     while ($info_statusy = $sql_statusy->fetch_assoc()) {

          $s = $info_statusy['orders_status_history_id'];
          
          $this->statusy[$s] = array('zamowienie_status_id' => $info_statusy['orders_status_history_id'],
                                     'status_id'            => $info_statusy['orders_status_id'],
                                     'status_nazwa'         => Funkcje::pokazNazweStatusuZamowienia($info_statusy['orders_status_id'], (int)$_SESSION['domyslnyJezyk']['id']),
                                     'data_dodania'         => date('d-m-Y H:i:s',FunkcjeWlasnePHP::my_strtotime($info_statusy['date_added'])),
                                     'powiadomienie_mail'   => $info_statusy['customer_notified'],
                                     'powiadomienie_sms'    => $info_statusy['customer_notified_sms'],
                                     'komentarz'            => '<div class="FormatEdytor">' . $info_statusy['comments'] . '</div>');
                                     
     }
     
     $GLOBALS['db']->close_query($sql_statusy);
     unset($info_statusy, $sql_statusy, $zapytanie_statusy);   

     // zwrot
     
     if ( ZWROTY_STATUS == 'tak' ) {
     
         $zapytanie_zwrot = "select * from return_list where return_customers_orders_id = '" . $this->info['id_zamowienia'] . "'";
         $sql_zwrot = $GLOBALS['db']->open_query($zapytanie_zwrot);
          
         if ((int)$GLOBALS['db']->ile_rekordow($sql_zwrot) > 0) {
           
              $this->zwrot = true;

         }
          
         $GLOBALS['db']->close_query($sql_zwrot); 
         
     }
       
  }
    
  // funkcja generujaca dodatkowe pola do zamowien
  static public function pokazDodatkowePolaZamowienia($languages_id = '1' ) {
    global $i18n;

    $ciag_dodatkowych_pol ='';

    $dodatkowe_pola_zamowienia = "
      SELECT oe.fields_id, oe.fields_input_type, oe.fields_required_status, oei.fields_input_value, oei.fields_name, oe.fields_status, oe.fields_input_type, oe.fields_type, oe.fields_file_type, oe.fields_file_size 
        FROM orders_extra_fields oe, orders_extra_fields_info oei 
        WHERE oe.fields_status = '1' 
        AND oei.fields_id = oe.fields_id 
        AND oei.languages_id = '".$languages_id."'
        ORDER BY oe.fields_order";

    $sql = $GLOBALS['db']->open_query($dodatkowe_pola_zamowienia);

    if ( (int)$GLOBALS['db']->ile_rekordow($sql) > 0  ) {

      while ( $dodatkowePola = $sql->fetch_assoc() ) {

        $wartosc = '';

        $ciag_dodatkowych_pol .= '<p>';

        $ciag_dodatkowych_pol .= '<span>' . (($dodatkowePola['fields_input_type'] != 2 && $dodatkowePola['fields_input_type'] != 3) ? '<label class="formSpan" for="fields_' . $dodatkowePola['fields_id'] . '">' : '') . $dodatkowePola['fields_name'] . (($dodatkowePola['fields_file_type' ] != '' ) ? ' ('.$dodatkowePola['fields_file_type' ].') ': '') . ': ' . (($dodatkowePola['fields_required_status' ]== 1) ? '<em class="required" id="em_'.uniqid().'"></em>': '') . (($dodatkowePola['fields_input_type'] != 2 && $dodatkowePola['fields_input_type'] != 3) ? '</label>' : '') . '</span>';

        $wartosci_pola_lista = explode("\n", (string)$dodatkowePola['fields_input_value']);
        $wartosci_pola_tablica = array();
        
        foreach($wartosci_pola_lista as $wartosc_pola) {
          $wartosc_pola = trim((string)$wartosc_pola);
          $wartosci_pola_tablica[] = array('id' => $wartosc_pola, 'text' => $wartosc_pola);
        }

        switch($dodatkowePola['fields_input_type']) {
          // Pole typu INPUT
          case 0:
            if ( $dodatkowePola['fields_type'] == 'kalendarz' ) {
                 $ciag_dodatkowych_pol .= '<input type="text" name="fields_'.$dodatkowePola['fields_id'].'" value="" id="fields_' . $dodatkowePola['fields_id'] . '" ' . (($dodatkowePola['fields_required_status']==1) ? 'class="required datefields"': 'class="datefields"').' size="30" />';
               } else {
                 $ciag_dodatkowych_pol .= '<input type="text" name="fields_'.$dodatkowePola['fields_id'].'" value="" id="fields_' . $dodatkowePola['fields_id'] . '" ' . (($dodatkowePola['fields_required_status']==1) ? 'class="required"': '').' size="40" style="width:80%" />';
            }
            $ciag_dodatkowych_pol .= '<label class="error" for="fields_' . $dodatkowePola['fields_id'].'" style="display:none">' . $GLOBALS['tlumacz']['BLAD_WYMAGANE_POLE'] . '</label>';
            break;

          // Pole typu TEXTAREA
          case 1:
            $ciag_dodatkowych_pol .= '<textarea name="fields_' . $dodatkowePola['fields_id'].'" cols="40" style="width:80%" rows="4" id="fields_'.$dodatkowePola['fields_id'].'" '.(($dodatkowePola['fields_required_status']==1) ? 'class="required"': '').'></textarea>';
            $ciag_dodatkowych_pol .= '<label class="error" for="fields_' . $dodatkowePola['fields_id'].'" style="display:none">' . $GLOBALS['tlumacz']['BLAD_WYMAGANE_POLE'] . '</label>';
            break;

          // Pole typu RADIO
          case 2:
            $cnt = 0;
            foreach($wartosci_pola_lista as $wartosc_pola) {
              $pole_rand = 'pole_' . rand(1,100000000);
              $wartosc_pola = trim((string)$wartosc_pola);

              $ciag_dodatkowych_pol .= '<label for="' . $pole_rand . '">' . FunkcjeWlasnePHP::my_htmlentities($wartosc_pola);
              $ciag_dodatkowych_pol .= '<input type="radio" value="'.FunkcjeWlasnePHP::my_htmlentities($wartosc_pola).'" id="' . $pole_rand . '" name="fields_' . $dodatkowePola['fields_id'].'" '.(($dodatkowePola['fields_required_status']==1) ? 'class="required"': '').' />';
              $ciag_dodatkowych_pol .= '<span class="radio" id="radio_' . $pole_rand . '"></span>';
              $ciag_dodatkowych_pol .= '</label>';

              $cnt++;
              unset($pole_rand);
            }
            $ciag_dodatkowych_pol .= '<div class="errorInformacjazamowienie">';
            $ciag_dodatkowych_pol .= '<label class="error" for="fields_' . $dodatkowePola['fields_id'].'" style="display:none">' . $GLOBALS['tlumacz']['BLAD_ZAZNACZ_JEDNA_OPCJE'] . '</label>';
            $ciag_dodatkowych_pol .= '</div>';
            break;

          // Pole typu CHECKBOX
          case 3:
            $cnt = 0;
            foreach($wartosci_pola_lista as $wartosc_pola) {
              $pole_rand = 'pole_' . rand(1,100000000);
              $wartosc_pola = trim((string)$wartosc_pola);
              $ciag_dodatkowych_pol .= '<label for="' . $pole_rand . '">' . FunkcjeWlasnePHP::my_htmlentities($wartosc_pola);
              $ciag_dodatkowych_pol .= '<input type="checkbox"  value="'.FunkcjeWlasnePHP::my_htmlentities($wartosc_pola).'" id="' . $pole_rand . '" name="fields_' . $dodatkowePola['fields_id'].'[]" '.(($dodatkowePola['fields_required_status']==1) ? 'class="required"': '').' />';
              $ciag_dodatkowych_pol .= '<span class="check" id="check_' . $pole_rand . '"></span>';

              $ciag_dodatkowych_pol .= '</label>';

              $cnt++;
              if ( $cnt < count($wartosci_pola_lista) ) {
                $ciag_dodatkowych_pol .= '<br />';
              }
              unset($pole_rand);
            }
            $ciag_dodatkowych_pol .= '<div class="errorInformacjazamowienie">';
            $ciag_dodatkowych_pol .= '<label class="error" for="fields_' . $dodatkowePola['fields_id'].'[]" style="display:none">' . $GLOBALS['tlumacz']['BLAD_ZAZNACZ_OPCJE'] . '</label>';
            $ciag_dodatkowych_pol .= '</div>';
            break;

          // Pole typu SELECT
          case 4:
              $ciag_dodatkowych_pol .= Funkcje::RozwijaneMenu('fields_' . $dodatkowePola['fields_id'], $wartosci_pola_tablica, '', ' id="fields_' . $dodatkowePola['fields_id'] . '" style="width:80%"');
            break;

          // Pole typu PLIK
          case 5:
            $ciag_dodatkowych_pol .= '<input type="hidden" id="fields_' . $dodatkowePola['fields_id'] . '" name="fields_' . $dodatkowePola['fields_id'] . '" value="" ' . (($dodatkowePola['fields_required_status'] == 1) ? 'data-required="1"': 'data-required="0"') . ' />
                                           <input type="file" class="wgraniePliku'.(($dodatkowePola['fields_required_status']==1) ? ' required': '').'" onchange="WgraniePliku(this)" id="plik_' . $dodatkowePola['fields_id'] . '" name="plik_' . $dodatkowePola['fields_id'] . '" />
                                           <div class="wynik_plik_' . $dodatkowePola['fields_id'] . '"><img src="" id="imgprev_' . $dodatkowePola['fields_id'] . '" style="display:none; padding:8px; max-width:70px !important; max-height:70px !important;" ></div>'; 
            break;

          default:
            $ciag_dodatkowych_pol .= '<input type="text" name="fields_'.$dodatkowePola['fields_id'].'" value="" id="fields_' . $dodatkowePola['fields_id'] . '" ' . (($dodatkowePola['fields_required_status']==1) ? 'class="required"': '').' size="40" style="width:80%" />';
            break;
        }

        $ciag_dodatkowych_pol .= '</p>';
      }
      
       
    }
    $GLOBALS['db']->close_query($sql);
    
    unset($dodatkowe_pola_zamowienia, $dodatkowe_pola);   

    return $ciag_dodatkowych_pol;
  }      
    
}
?>
