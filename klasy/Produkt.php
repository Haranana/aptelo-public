<?php

class Produkt {

    protected $infoSql;

    public $id_produktu;
    public $waterMark;
    public $cssKoszyka;
    public $cssKoszykaTekst;
    public $szerImg;
    public $wysImg;
    public $jezykDomyslnyId;
    public $info;
    public $fotoGlowne;
    public $ikonki;
    public $recenzje;
    public $recenzjeZdjecia;
    public $recenzjeSrednia;
    public $dostepnosc;
    public $czas_wysylki;
    public $czas_wysylki_dni;
    public $stan_produktu;
    public $stan_produktu_microdata;
    public $stan_produktu_opengraph;
    public $gwarancja;
    public $vat_podstawa;
    public $producent;
    public $metaTagi;
    public $dodatkowePolaFoto;
    public $dodatkowePolaOpis;
    public $dodatkowePola;
    public $dodatkowePolaTekstowe;
    public $Linki;
    public $dodatkoweZakladki;
    public $Pliki;
    public $Youtube;
    public $FilmyFlv;
    public $Mp3;
    public $Faq;
    public $LinkiPowiazane;
    public $InneWarianty;
    public $AukcjeAllegro;
    public $inputIlosc;
    public $znizkiZalezneOdIlosci;
    public $znizkiZalezneOdIlosciTyp;
    public $cechyIlosc;
    public $zestawProdukty;
    public $zestawTaniej;
    public $produktDnia;
    public $produktDniaRabat;
    public $rabatWlasny;
    public $iloscKupionych;
    public $tablicaKupionych;
    public $iloscKupionychSztuk;
    public $CenaIndywidualna;
    public $preloadImg;
    public $idUnikat;
    public $CzyJestProdukt;
    public $zakupy;
    public $podziel_sie;
    public $kupon;
    public $kupon_dostawa;

    // uzywane funkcje
    /*
    ProduktInfo() - ogolne informacje o produkcie
    ProduktDodatkoweZdjecia() - dodatkowe zdjecia produktu - zwraca w formie tablicy
    ProduktKupowanie( id ) - kupowanie produktu - koszyk, ilosc, czy mozna kupowac
    ProduktRecenzje() - recenzje produktu - w formie tablicy
    ProduktCzasWysylki() - czas wysylki produktu - ilosc dni
    ProduktStanProduktu() - stan produktu - nowy/uzywany
    ProduktStanProduktuMicroData() - stan produktu - nowy/uzywany
    ProduktGwarancja() - gwarancja produktu
    ProduktDostepnosc() - dostepnosc produktu - w formie tablicy
    ProduktProducent() - dane producenta produktu
    ProduktZnizkiZalezneOdIlosci() - okresla znizke produktu w zaleznosci od ilosci w koszyku - wartosc liczbowa
    ProduktZnizkiZalezneOdIlosciTablica() - zwraca tablice z znizkami od ilosci w koszyku
    ProduktDodatkowePola() - dodatkowe pola do produktu
    ProduktDodatkowePolaTekstowe() - dodatkowe pola tekstowe do produktu
    ProduktCechyIlosc() - ilosc cech produktu - przekazuje do $this->cechyIlosc
    ProduktWartoscCechy() - cena wybranych cech
    ProduktCechyGeneruj() - generuje cechy na karcie produktu
    ProduktCechyNrKatalogowy() - podaje nr katalogowy dla danych cech produktu
    ProduktLinki() - linki do produktu
    ProduktDodatkoweZakladki() - dodatkowe zakladki do produktu
    ProduktDodatkoweOpisy() - dodatkowe opisy do produktu
    ProduktPliki() - pliki do produktu
    ProduktYoutube() - filmy youtube
    ProduktFilmyFLV() - filmy flv
    ProduktMp3() - pliki muzyczne Mp3
    ProduktFaq() - pytania i odpowiedzi Faq
    ProduktLinkiPowiazane() - linki powiazane z produktem
    ProduktInneWarianty() - linki do innych wariantow produktu
    ProduktAllegro() - aukcje produktu na Allegro
    ProduktKategoriaGlowna() - zwraca id i nazwe kategorii glownej produktu
    ProduktZestawy() - zwraca dane na temat zestawu
    ProduktJakieZestawy() - zwraca dane na temat zestawow do jakich jest przypisany produkt
    MicroDataAvailability() - zwraca dostepnosc produktu do Microdanych
    ProduktWielkoscPojemnosc() - zwraca informacji o cenie za kg/m/litr
    */

    public function __construct( $id_produktu, $szerokoscObrazka = '', $wysokoscObrazka = '', $nazwaKlasyKoszyka = '', $preloadImg = true, $rabat_wlasny = false ) {
    
        $this->id_produktu = $id_produktu;
        $this->waterMark = 'maly';
        
        if ($nazwaKlasyKoszyka == '') {
            $this->cssKoszyka = 'DoKoszyka';
            $this->cssKoszykaTekst = $GLOBALS['tlumacz']['PRZYCISK_DO_KOSZYKA'];
          } else {
            $this->cssKoszyka = $nazwaKlasyKoszyka;
            $this->cssKoszykaTekst = $GLOBALS['tlumacz']['PRZYCISK_DODAJ_DO_KOSZYKA'];
        }
        
        if ($szerokoscObrazka == '') {
            $this->szerImg = SZEROKOSC_OBRAZEK_MALY;
          } else {
            $this->szerImg = $szerokoscObrazka;
        }

        if ($szerokoscObrazka < SZEROKOSC_OBRAZEK_SREDNI) {
            $this->waterMark = 'maly';
        } else {
            $this->waterMark = 'sredni';
        }
        
        if ($wysokoscObrazka == '') {
            $this->wysImg = WYSOKOSC_OBRAZEK_MALY;
          } else {
            $this->wysImg = $wysokoscObrazka;
        }
        
        $this->jezykDomyslnyId = (int)$_SESSION['domyslnyJezyk']['id'];

        // informacje ogolne o produkcie
        $this->info = array();
        // tablica zapytanie sql
        $this->infoSql = '';
        // informacje o glownym zdjeciu
        $this->fotoGlowne = array();
        // informacje o ikonkach
        $this->ikonki = array( 'rabat' => '0', 'rabat_wartosc' => '0', 'cena_specjalna' => '0' );        
        // informacje o recenzjach
        $this->recenzje = array();  
        $this->recenzjeZdjecia = array();  
        $this->recenzjeSrednia = array(); 
        // informacje o dostepnosci
        $this->dostepnosc = array(); 
        // czas wysylki
        $this->czas_wysylki = '';
        // czas wysylki - ilosc dni
        $this->czas_wysylki_dni = ''; 
        // stan produktu
        $this->stan_produktu = '';
        // stan produktu do microdanych
        $this->stan_produktu_microdata = '';
        $this->stan_produktu_opengraph = '';
        // gwarancja produktu
        $this->gwarancja = '';        
        // vat podstawowy produktu
        $this->vat_podstawa = 0;            
        // informacje o producencie
        $this->producent = array(); 
        // meta tagi
        $this->metaTagi = array();
        // dodatkowe pola opisowe - obok zdjecia lub pod opisem
        $this->dodatkowePolaFoto = array();
        $this->dodatkowePolaOpis = array();
        $this->dodatkowePola = array();
        // dodatkowe pola tekstowe
        $this->dodatkowePolaTekstowe = array();
        // linki
        $this->Linki = array();
        // dodatkowe zakladki
        $this->dodatkoweZakladki = array();
        // pliki
        $this->Pliki = array();     
        // filmy youtube
        $this->Youtube = array();     
        // filmy flv
        $this->FilmyFlv = array();       
        // pliki mp3
        $this->Mp3 = array();   
        // pytania i odpowiedzi Faq
        $this->Faq = array();          
        // linki powiazane
        $this->LinkiPowiazane = array();     
        // inne warianty
        $this->InneWarianty = array();          
        // aukcje Allegro
        $this->AukcjeAllegro = array();         
        // input ilosci i wartosci ilosci zakupu
        $this->inputIlosc = array();
        // znizka w zaleznosci od iloscu produktow w koszyku
        $this->znizkiZalezneOdIlosci = '';       
        $this->znizkiZalezneOdIlosciTyp = '';           
        // czy produkt ma cechy - trzeba do tego wywolac funkcje ProduktCechy
        $this->cechyIlosc = 0;  
        // zestaw produktow
        $this->zestawProdukty = array();
        $this->zestawTaniej = 0;
        // produkt dnia
        $this->produktDnia = false;
        $this->produktDniaRabat = 0;
        
        // inny rabat - uzywane przy zestawach
        $this->rabatWlasny = $rabat_wlasny;
        
        // ilosc zakupow danego produktu
        $this->iloscKupionych = 0;
        $this->tablicaKupionych = array();
        $this->iloscKupionychSztuk = 0;

        // czy sa indywidualne ceny produktu
        $this->CenaIndywidualna = false;
        
        // czy do obrazka ma byc dodawana klasa do preloadera obrazkow  
        $this->preloadImg = $preloadImg;
        
        // unikalny id produktu dla unikniecia dubli
        $this->idUnikat = rand(1,99999) . '_';
        
        // zwraca czy produkt jest czy nie
        $this->CzyJestProdukt = $this->ProduktInfo();
        
    }
    
    private function ProduktInfo() {
    
        $DodatkoweCeny = '';
        if ( (int)ILOSC_CEN > 1 ) {
            //
            for ($n = 2; $n <= (int)ILOSC_CEN; $n++) {
                //
                $DodatkoweCeny .= 'p.products_price_tax_' . $n . ', p.products_price_' . $n . ', p.products_old_price_' . $n . ', p.products_retail_price_' . $n . ', ';
                //
            }
            //
        }

        $zapProdukt = "SELECT p.products_id,
                          p.products_quantity,
                          p.products_quantity_alarm,
                          p.products_model,
                          p.products_man_code,
                          p.products_ean,
                          p.products_pkwiu,
                          p.products_gtu,
                          p.products_safety_information,
                          p.products_image,
                          p.products_image_description,
                          p.products_price_tax,
                          p.products_price,
                          p.products_retail_price,
                          " . $DodatkoweCeny . "
                          p.products_old_price,
                          p.products_min_price_30_day,
                          p.products_min_price_30_day_date,
                          p.products_min_price_30_day_date_created,
                          p.products_currencies_id,
                          p.products_tax_class_id,
                          p.products_availability_id,
                          p.products_shipping_time_id,
                          p.products_shipping_time_zero_quantity_id,
                          p.products_status,
                          p.products_buy,
                          p.products_fast_buy,
                          p.products_price_login,
                          p.products_accessory,
                          p.products_weight,
                          p.products_weight_width,
                          p.products_weight_height,
                          p.products_weight_length,
                          p.products_pack_type,
                          p.products_separate_package,
                          p.products_separate_package_quantity,
                          p.products_comments,                          
                          p.new_status,
                          p.specials_status,
                          p.specials_date, 
                          p.specials_date_end,  
                          p.sale_status,
                          p.featured_status,
                          p.featured_date, 
                          p.featured_date_end,                          
                          p.star_status,
                          p.star_date,
                          p.star_date_end,
                          p.products_jm_id,
                          p.products_minorder,
                          p.products_minorder_time,
                          p.products_minorder_date,
                          p.products_minorder_date_end,                          
                          p.products_maxorder,
                          p.products_maxorder_time,
                          p.products_maxorder_date,
                          p.products_maxorder_date_end,                          
                          p.products_quantity_order,
                          p.products_discount,
                          p.products_discount_type,
                          p.products_discount_date,
                          p.products_discount_date_end,
                          p.products_discount_group_id,
                          p.shipping_method,
                          p.shipping_cost,
                          p.shipping_cost_quantity,
                          p.shipping_cost_delivery,
                          p.products_make_an_offer,
                          p.free_shipping_status,
                          p.free_shipping_status_customers_group_id,
                          p.free_shipping_excluded,
                          p.pickup_excluded,
                          p.icon_1_status,
                          p.icon_2_status,
                          p.icon_3_status,
                          p.icon_4_status,
                          p.icon_5_status,                          
                          p.products_date_available,
                          p.products_date_available_end,
                          p.products_date_available_buy,
                          p.products_date_available_clock,
                          p.products_date_available_status,
                          p.options_type,
                          p.products_condition_products_id,
                          p.products_warranty_products_id,
                          p.products_points_only,
                          p.products_points_value,           
                          p.products_points_value_money,
                          p.products_points_purchase,
                          p.products_type,
                          p.products_ordered,
                          p.products_control_storage,
                          p.inpost_size,
                          p.inpost_quantity,
                          p.products_set,
                          p.products_set_products,
                          p.products_not_discount,
                          p.products_counting_points,                      
                          p.products_size,
                          p.products_size_type,                            
                          p.products_other_variant_text,
                          p.products_other_variant_range,
                          p.products_other_variant_method,
                          p.products_other_variant_image,
                          p.products_other_variant_name,
                          p.products_other_variant_name_type,
                          p.products_other_variant_price,                          
                          p.products_energy,
                          p.products_energy_img,
                          p.products_energy_pdf,
                          m.manufacturers_id,
                          m.manufacturers_name,
                          m.manufacturers_image,
                          pd.products_seo_url,
                          pd.products_name,
                          pd.products_name_info,
                          pd.products_name_short,
                          pd.products_description,
                          pd.products_short_description,
                          pd.products_meta_title_tag,
                          pd.products_meta_desc_tag,
                          pd.products_meta_keywords_tag,
                          pd.products_link_canonical,
                          pd.products_search_tag,
                          pd.products_og_title,
                          pd.products_og_description,
                          pd.products_viewed,
                          pd.products_review_summary,
                          ptc.categories_id,
                          t.tax_rate
                      FROM products p
                      LEFT JOIN products_description pd ON pd.products_id = p.products_id AND pd.language_id = '" . $this->jezykDomyslnyId . "'
                      LEFT JOIN manufacturers m ON p.manufacturers_id = m.manufacturers_id
                      INNER JOIN products_to_categories ptc ON ptc.products_id = p.products_id
                      INNER JOIN categories c ON c.categories_id = ptc.categories_id AND c.categories_status = 1
                      LEFT JOIN tax_rates t ON t.tax_rates_id = p.products_tax_class_id  
                      WHERE p.products_id = '" . $this->id_produktu . "' and p.products_status = '1'" . $GLOBALS['warunekProduktu'];

        // cache zapytania
        $WynikCache = $GLOBALS['cache']->odczytaj('Produkt_Id_' . $this->id_produktu . '_' . $_SESSION['domyslnyJezyk']['kod'], CACHE_PRODUKTY, true);   

        if ( !$WynikCache ) {
            $sqlProdukt = $GLOBALS['db']->open_query($zapProdukt);      
            $IleRekordow = (int)$GLOBALS['db']->ile_rekordow($sqlProdukt);
          } else {
            $IleRekordow = count($WynikCache);
        }        
                      
        if ( $IleRekordow > 0 ) {
        
            if ( !$WynikCache ) {
                $this->infoSql = $sqlProdukt->fetch_assoc();
                //
                $GLOBALS['cache']->zapisz('Produkt_Id_' . $this->id_produktu . '_' . $_SESSION['domyslnyJezyk']['kod'], $this->infoSql, CACHE_PRODUKTY, true);
            } else {
                $this->infoSql = $WynikCache;
            }   
            
            // min i maks ilosc ograniczona czasowo
            if ( (float)$this->infoSql['products_minorder_time'] > 0 && Funkcje::czyNiePuste($this->infoSql['products_minorder_date'])  && Funkcje::czyNiePuste($this->infoSql['products_minorder_date_end']) ) { 
                 //
                 if ( FunkcjeWlasnePHP::my_strtotime($this->infoSql['products_minorder_date']) <= time() && FunkcjeWlasnePHP::my_strtotime($this->infoSql['products_minorder_date_end']) >= time() ) {
                      //
                      $this->infoSql['products_minorder'] = $this->infoSql['products_minorder_time'];
                      //
                 }
                 //                   
            }
            if ( (float)$this->infoSql['products_maxorder_time'] > 0 && Funkcje::czyNiePuste($this->infoSql['products_maxorder_date'])  && Funkcje::czyNiePuste($this->infoSql['products_maxorder_date_end']) ) { 
                 //
                 if ( FunkcjeWlasnePHP::my_strtotime($this->infoSql['products_maxorder_date']) <= time() && FunkcjeWlasnePHP::my_strtotime($this->infoSql['products_maxorder_date_end']) >= time() ) {
                      //
                      $this->infoSql['products_maxorder'] = $this->infoSql['products_maxorder_time'];
                      //
                 }
                 //                   
            }
            
            // jezeli stan magazynowy nizszy o 0
            if ( STAN_MAGAZYNOWY_PONIZEJ_ZERO == 'tak' && (float)$this->infoSql['products_quantity'] < 0 ) {              
                 //
                 $this->infoSql['products_quantity'] = 0;
                 //
            }
            
            // sprawdzi czy nie sa daty z poza zakresu - jezeli tak wylaczy kupowanie
            if ( Funkcje::czyNiePuste($this->infoSql['products_date_available']) ) {
                 //
                 if ( $this->infoSql['products_date_available_status'] == 1 ) {
                      //
                      if ( FunkcjeWlasnePHP::my_strtotime($this->infoSql['products_date_available']) > time() ) {
                           //
                           $this->infoSql['products_buy'] = 0;
                           $this->infoSql['products_fast_buy'] = 0;
                           //
                      }
                      //
                 }
            }
            //
            // sprawdzi czy data sprzedazy nie jest wieksza od daty dostepnosci
            //
            if ( Funkcje::czyNiePuste($this->infoSql['products_date_available_buy']) ) {
                //
                if ( Funkcje::czyNiePuste($this->infoSql['products_date_available']) ) {
                     //
                     if ( FunkcjeWlasnePHP::my_strtotime($this->infoSql['products_date_available_buy']) > FunkcjeWlasnePHP::my_strtotime($this->infoSql['products_date_available']) ) {
                          //
                          if ( date('Y-m-d', FunkcjeWlasnePHP::my_strtotime($this->infoSql['products_date_available_buy'])) != date('Y-m-d', time()) ) {
                               //
                               $this->infoSql['products_date_available'] = $this->infoSql['products_date_available_buy'];
                               //
                          }
                          //
                     }
                     if ( FunkcjeWlasnePHP::my_strtotime($this->infoSql['products_date_available_buy']) < FunkcjeWlasnePHP::my_strtotime($this->infoSql['products_date_available']) ) {
                          //
                          $this->infoSql['products_date_available_buy'] = '0000-00-00';
                          $this->infoSql['products_buy'] = 0;
                          $this->infoSql['products_fast_buy'] = 0;                          
                          //
                     }                      
                     //
                }
                //
                if ( FunkcjeWlasnePHP::my_strtotime($this->infoSql['products_date_available_buy']) > time() ) {
                     //
                     $this->infoSql['products_buy'] = 0;
                     $this->infoSql['products_fast_buy'] = 0;
                     //
                }
                //
            }
            //

            if ( Funkcje::czyNiePuste($this->infoSql['products_date_available_end']) ) {
                 //
                 if ( FunkcjeWlasnePHP::my_strtotime($this->infoSql['products_date_available_end'] . ' 23:59:59') < time() ) {
                      //
                      $this->infoSql['products_buy'] = 0;
                      $this->infoSql['products_fast_buy'] = 0;
                      //
                 }
                 //
            }
            
            // stawka vat podstawowa produktu
            $this->vat_podstawa = Funkcje::StawkaPodatekVat( $this->infoSql['products_tax_class_id'] );
            
            // jezeli produkt nie jest promocja a ma przypisana cene poprzednia - wyzeruje cene
            if ( $this->infoSql['specials_status'] == 0  && $this->infoSql['sale_status'] == 0 ) {
                 //
                 $this->infoSql['products_old_price'] = 0;
                 //
                 if ( (int)ILOSC_CEN > 1 ) {
                     //
                     for ($n = 2; $n <= (int)ILOSC_CEN; $n++) {
                         //
                         $this->infoSql['products_old_price_' . $n] = 0;
                         //
                     }
                     //
                 }                 
                 //
            }
            
            // ceny netto
            if ( isset($_SESSION['netto']) && $_SESSION['netto'] == 'tak' ) {
                 //
                 $this->infoSql['products_tax_class_id'] = $_SESSION['vat_zwolniony_id'];
                 $this->infoSql['tax_rate'] = $_SESSION['vat_zwolniony_wartosc'];
                 $this->infoSql['products_price_tax'] = $this->infoSql['products_price'];
                 $this->infoSql['products_old_price'] = round(($this->infoSql['products_old_price'] / (1 + ($this->vat_podstawa/100))), 2);
                 $this->infoSql['products_retail_price'] = round(($this->infoSql['products_retail_price'] / (1 + ($this->vat_podstawa/100))), 2);
                 //
            }
            
            // indywidualne ceny produktow
            $pobierzFunkcje = true;
            include('produkt/ProduktIndywidualneCeny.php');
            unset($pobierzFunkcje);
            
            if ( $this->CenaIndywidualna == false ) {
            
                // jezeli klient ma inny poziom cen
                if ( $_SESSION['poziom_cen'] > 1 ) {
                    //
                    // jezeli cena w innym poziomie nie jest pusta
                    if ( $this->infoSql['products_price_' . $_SESSION['poziom_cen']] > 0 ) {
                        //
                        if ( isset($_SESSION['netto']) && $_SESSION['netto'] == 'tak' ) {
                             //
                             $this->infoSql['products_price_tax'] = $this->infoSql['products_price_' . $_SESSION['poziom_cen']];
                             //
                          } else {
                             //
                             $this->infoSql['products_price_tax'] = $this->infoSql['products_price_tax_' . $_SESSION['poziom_cen']];
                             //
                        }
                        //
                        $this->infoSql['products_price'] = $this->infoSql['products_price_' . $_SESSION['poziom_cen']];
                        //
                    }
                    //
                    // cena poprzednia przy promocji
                    if ( $this->infoSql['products_old_price_' . $_SESSION['poziom_cen']] > 0 ) {
                        //
                        if ( isset($_SESSION['netto']) && $_SESSION['netto'] == 'tak' ) {                        
                             //
                             $this->infoSql['products_old_price'] = round(($this->infoSql['products_old_price_' . $_SESSION['poziom_cen']] / (1 + ($this->vat_podstawa/100))), 2);
                             //
                          } else {
                             //
                             $this->infoSql['products_old_price'] = $this->infoSql['products_old_price_' . $_SESSION['poziom_cen']];
                             //
                        }
                        //
                    }
                    //
                    // cena katalogowa
                    if ( $this->infoSql['products_retail_price_' . $_SESSION['poziom_cen']] > 0 ) {
                        //
                        if ( isset($_SESSION['netto']) && $_SESSION['netto'] == 'tak' ) {                        
                             //
                             $this->infoSql['products_retail_price'] = round(($this->infoSql['products_retail_price_' . $_SESSION['poziom_cen']] / (1 + ($this->vat_podstawa/100))), 2);
                             //
                          } else {
                             //
                             $this->infoSql['products_retail_price'] = $this->infoSql['products_retail_price_' . $_SESSION['poziom_cen']];
                             //
                        }
                        //
                    }
                    //
                }            
                
                // zestawy produktow
                if ( $this->infoSql['products_set'] == 1 ) {
                     //
                     $this->ProduktZestawy();
                     //
                     $CenaBruttoZestaw = 0;
                     $CenaNettoZestaw = 0;
                     //
                     $IloscTmp = $this->infoSql['products_quantity'];
                     //
                     $TablicaPrdZestaw = $this->zestawProdukty;
                     
                     if ( count($TablicaPrdZestaw) > 0 ) {
                         //
                         foreach ( $TablicaPrdZestaw as $Id => $Dane ) {
                             //
                             if ( (int)($Dane['ilosc_magazyn'] / $Dane['ilosc']) < $IloscTmp ) {
                                  $IloscTmp = (int)($Dane['ilosc_magazyn'] / $Dane['ilosc']);
                             }
                             //
                             $CenaBruttoZestaw += $Dane['cena_brutto'] * $Dane['ilosc'];
                             $CenaNettoZestaw += $Dane['cena_netto'] * $Dane['ilosc'];
                             //
                         }
                         //
                         $this->infoSql['products_price_tax'] = $CenaBruttoZestaw;
                         $this->infoSql['products_price'] = $CenaNettoZestaw;
                         $this->infoSql['products_tax'] = $CenaBruttoZestaw - $CenaNettoZestaw;
                         //
                         // ilosc poszczegolnych produktow zestawu - tylko jezeli jest kontrola stanu magazynowego
                         if ( MAGAZYN_SPRAWDZ_STANY == 'tak' && MAGAZYN_SPRZEDAJ_MIMO_BRAKU == 'nie' && $this->infoSql['products_control_storage'] > 0 ) {
                              //
                              $this->infoSql['products_quantity'] = $IloscTmp;
                              //
                         } 
                         //
                     } else {
                         //
                         $this->infoSql['products_price_tax'] = 0;
                         $this->infoSql['products_price'] = 0;
                         $this->infoSql['products_tax'] = 0;
                         $this->infoSql['products_quantity'] = 0;
                         //
                     }
                     //
                     unset($TablicaPrdZestaw, $IloscTmp);
                     //
                }              


                // ustawienia promocji - sprawdzi czy produkt nie jest cena promocyjna z datami - jezeli daty nie lapia sie na aktualny czas to przyjmie cene poprzednia
                if ( ((FunkcjeWlasnePHP::my_strtotime($this->infoSql['specials_date']) > time() && $this->infoSql['specials_date'] != '0000-00-00 00:00:00') || (FunkcjeWlasnePHP::my_strtotime($this->infoSql['specials_date_end']) < time() && $this->infoSql['specials_date_end'] != '0000-00-00 00:00:00') ) && $this->infoSql['specials_status'] == 1 && $this->infoSql['products_old_price'] > 0 ) {

                    // nie dotyczy zestawow
                    if ( $this->infoSql['products_set'] == 0 ) {
                        
                         $this->infoSql['products_price_tax'] = $this->infoSql['products_old_price'];
                         //
                         // obliczanie netto i vatu             
                         $netto = round(($this->infoSql['products_price_tax'] / (1 + (Funkcje::StawkaPodatekVat( $this->infoSql['products_tax_class_id'] )/100))), 2);
                         $podatek = $this->infoSql['products_price_tax'] - $netto;
                         //
                         $this->infoSql['products_price'] = $netto;
                         $this->infoSql['products_tax'] = $podatek;

                         unset($netto, $podatek);

                    }

                    $this->infoSql['products_old_price'] = 0;
                    $this->infoSql['specials_status'] = 0;

                }

                // jezeli produkt jest tylko za PUNKTY
                if ( $this->infoSql['products_points_only'] == '1' && SYSTEM_PUNKTOW_STATUS == 'tak' && SYSTEM_PUNKTOW_STATUS_KUPOWANIA == 'tak' && Punkty::PunktyAktywneDlaKlienta() ) {
                  
                     // obliczanie netto i vatu             
                     $netto = round(($this->infoSql['products_points_value_money'] / (1 + (Funkcje::StawkaPodatekVat( $this->infoSql['products_tax_class_id'] )/100))), 2);
                     $podatek = $this->infoSql['products_points_value_money'] - $netto;
                     //              
                     // przyjmuje cene 
                     $this->infoSql['products_price_tax'] = $this->infoSql['products_points_value_money'];
                     $this->infoSql['products_price'] = $netto;
                     $this->infoSql['products_tax'] = $podatek;                        
                     //
                     // zeruje takze ewentualna promocje
                     $this->infoSql['products_old_price'] = 0;
                     $this->infoSql['specials_status'] = 0;                 
                     //
                     unset($netto, $podatek);

                }     

            }
            
            // jezeli nie jest zdefiniowana jednostka miary
            if ( empty($this->infoSql['products_jm_id']) ) {
                 $this->infoSql['products_jm_id'] = 0;
            }
            
            // usuwanie znakow nowej linii
            foreach ( $this->infoSql as $klucz => $wartosc ) {
                 //
                 if ( $klucz != 'products_description' && $klucz != 'products_short_description' ) {
                      //
                      if ( $wartosc != '' ) {
                        $CoZmieniamy = array("\n", "\r");
                        $NaCoZmianiamy = '';
                        $this->infoSql[$klucz] = str_replace($CoZmieniamy, (string)$NaCoZmianiamy, (string)$wartosc);
                        $this->infoSql[$klucz] = trim((string)$this->infoSql[$klucz]);
                      }
                      //
                 }
                 //
            }

            // ustala jaka ma byc tresc linku
            $linkSeo = ((trim((string)$this->infoSql['products_seo_url']) != '') ? $this->infoSql['products_seo_url'] : $this->infoSql['products_name']);
            
            // produkt dnia
            $ProduktDnia = false;
            $ProduktDniaCena = 0;
            //
            if ( PRODUKT_DNIA_STATUS == 'tak' ) {
              
                $GrupaKlientowProduktDnia = true;
                // sprawdzi czy jest dostepny dla wybranej grupy klientow
                if ( count(explode(',', (string)PRODUKT_DNIA_GRUPY_KLIENTOW)) > 0 && PRODUKT_DNIA_GRUPY_KLIENTOW != '' ) {
                     //
                     // dla klientow bez rejestracji
                     if (isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '1' || (!isset($_SESSION['customer_id']) || (int)$_SESSION['customer_id'] == 0)) {
                         //
                         if ( !in_array('0', explode(',', (string)PRODUKT_DNIA_GRUPY_KLIENTOW)) ) {
                              //
                              $GrupaKlientowProduktDnia = false;
                              //
                         }
                         // 
                     }
                     // dla klientow z rejestracja
                     if (isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0' ) {
                         //
                         if ( !in_array($_SESSION['customers_groups_id'], explode(',', (string)PRODUKT_DNIA_GRUPY_KLIENTOW)) ) {
                              //
                              $GrupaKlientowProduktDnia = false;
                              //
                         }
                         //
                     }   
                     //
                }
                
                if ( $GrupaKlientowProduktDnia == true ) {

                    if ( isset($GLOBALS['produkt_dnia'][date('Y-m-d', time())]) && $GLOBALS['produkt_dnia'][date('Y-m-d', time())]['id_produktu'] == $this->infoSql['products_id'] ) {
                         //
                         $ProduktDnia = true;
                         //
                         $ProduktDniaCenaTmp = $GLOBALS['waluty']->FormatujCene( $this->infoSql['products_price_tax'], $this->infoSql['products_price'], $this->infoSql['products_old_price'], $this->infoSql['products_currencies_id'], false );
                         $ProduktDniaCenaPoprzedniaTmp = $GLOBALS['waluty']->FormatujCene( $this->infoSql['products_price_tax'], $this->infoSql['products_price'], $this->infoSql['products_old_price'], $_SESSION['domyslnaWaluta']['id'], false );
                         //
                         $ProduktDniaCena = $ProduktDniaCenaTmp['brutto'];
                         $RabatProduktDnia = (100 - (float)$GLOBALS['produkt_dnia'][date('Y-m-d', time())]['rabat']) / 100;
                         //
                         $this->infoSql['products_price_tax'] =  $this->infoSql['products_price_tax'] * $RabatProduktDnia;
                         $this->infoSql['products_price'] = $this->infoSql['products_price'] * $RabatProduktDnia;
                         $this->infoSql['products_old_price'] = $ProduktDniaCenaPoprzedniaTmp['brutto'];
                         //
                         $this->produktDnia = true;
                         $this->produktDniaRabat = $RabatProduktDnia;                         
                         //
                         unset($RabatProduktDnia, $ProduktDniaCenaTmp, $ProduktDniaCenaPoprzedniaTmp);
                         //   
                    }
                    
                }
                
            }
            
            // info o promocji dla cech
            $this->infoSql['specials_status_cechy'] = $this->infoSql['specials_status'];             

            // rabaty klienta od ceny produktu
            $CenaRabaty = $this->CenaProduktuPoRabatach( $this->infoSql['products_price'], $this->infoSql['products_price_tax'] );
            //
            if ( $this->rabatWlasny == false ) {
                 //
                 // rabaty klienta - tylko jezeli produkt nie jest produktem dnia
                 if ( $ProduktDnia == false ) {
                  
                    if ( $CenaRabaty['rabat'] != 0 && RABATY_PROMOCJE == 'nie' && RABATY_PROMOCJE_WYSWIETLAJ == 'tak' && $this->infoSql['specials_status'] == '1' && $this->infoSql['products_old_price'] > 0 && $this->infoSql['products_set'] == 0 ) {
                        //
                        $CenaRabaty = $this->CenaProduktuPoRabatach(round( ($this->infoSql['products_old_price'] / (1 + (Funkcje::StawkaPodatekVat( $this->infoSql['products_tax_class_id'] )/100))), 2), $this->infoSql['products_old_price'] );
                        //
                        $this->infoSql['products_price'] = $CenaRabaty['netto'];
                        $this->infoSql['products_price_tax'] = $CenaRabaty['brutto']; 
                        //
                        // zeruje promocje
                        $this->infoSql['products_old_price'] = 0;
                        $this->infoSql['specials_status'] = 0;                   
                        //              
                    } else {
                        //
                        $this->infoSql['products_price'] = $CenaRabaty['netto'];
                        $this->infoSql['products_price_tax'] = $CenaRabaty['brutto'];
                        //
                    }
                
                 }
                
            } else {
                  
                 $CenaRabatyTmp = array( 'netto' => $this->infoSql['products_price'], 'brutto' => $this->infoSql['products_price_tax'], 'rabat' => 0 );
                 
                 if ( $CenaRabaty['rabat'] != 0 && RABATY_PROMOCJE == 'nie' && RABATY_PROMOCJE_WYSWIETLAJ == 'tak' && $this->infoSql['specials_status'] == '1' && $this->infoSql['products_old_price'] > 0 && $this->infoSql['products_set'] == 0 ) {
                      //
                      $CenaRabatyTmp['netto'] = round(($this->infoSql['products_old_price'] / (1 + (Funkcje::StawkaPodatekVat( $this->infoSql['products_tax_class_id'] )/100))), 2);
                      $CenaRabatyTmp['brutto'] = $this->infoSql['products_old_price'];
                      //                 
                 }
                 
                 $this->infoSql['products_price'] = $CenaRabatyTmp['netto'];
                 $this->infoSql['products_price_tax'] = $CenaRabatyTmp['brutto'];                       
                 
                 unset($CenaRabatyTmp);
                
            }
            
            // jezeli produkt jest tylko za PUNKTY
            if ( $this->infoSql['products_points_only'] == '1' && SYSTEM_PUNKTOW_STATUS == 'tak' && SYSTEM_PUNKTOW_STATUS_KUPOWANIA == 'tak' && Punkty::PunktyAktywneDlaKlienta() && $ProduktDnia == false ) {            
                 //
                 $CenaProduktuPktTmp = $GLOBALS['waluty']->FormatujCene( $this->infoSql['products_points_value_money'], 0, 0, $this->infoSql['products_currencies_id'], false );
                 //
                 // cena produktu
                 $CenaProduktu = $GLOBALS['waluty']->PokazCenePunkty( $this->infoSql['products_points_value'], $CenaProduktuPktTmp['brutto'], true, CENY_BRUTTO_NETTO, (( isset($GLOBALS['jednostkiMiary'][$this->infoSql['products_jm_id']]['nazwa']) ) ? $GLOBALS['jednostkiMiary'][$this->infoSql['products_jm_id']]['nazwa'] : '') );
                 // uzywane do autouzupelnienia - pokazuje tylko cene brutto
                 $CenaProduktuBrutto = $GLOBALS['waluty']->PokazCenePunkty( $this->infoSql['products_points_value'], $CenaProduktuPktTmp['brutto'], true, 'nie', (( isset($GLOBALS['jednostkiMiary'][$this->infoSql['products_jm_id']]['nazwa']) ) ? $GLOBALS['jednostkiMiary'][$this->infoSql['products_jm_id']]['nazwa'] : '') );
                     
                 unset($CenaProduktuPktTmp);
                 
                 // jezeli jest cena w pkt to nie ma rabatu
                 $this->ikonki['rabat'] = '0';
                 $this->ikonki['rabat_wartosc'] = '0';
                 $this->ikonki['cena_specjalna'] = '0';
                     
              } else {
                 //
                 // cena produktu
                 $CenaProduktu = $GLOBALS['waluty']->PokazCene( $this->infoSql['products_price_tax'], $this->infoSql['products_price'], $this->infoSql['products_old_price'], $this->infoSql['products_currencies_id'], CENY_BRUTTO_NETTO, true, (( isset($GLOBALS['jednostkiMiary'][$this->infoSql['products_jm_id']]['nazwa']) ) ? $GLOBALS['jednostkiMiary'][$this->infoSql['products_jm_id']]['nazwa'] : '') );
                 // uzywane do autouzupelnienia - pokazuje tylko cene brutto
                 $CenaProduktuBrutto = $GLOBALS['waluty']->PokazCene( $this->infoSql['products_price_tax'], $this->infoSql['products_price'], $this->infoSql['products_old_price'], $this->infoSql['products_currencies_id'], 'nie', true, (( isset($GLOBALS['jednostkiMiary'][$this->infoSql['products_jm_id']]['nazwa']) ) ? $GLOBALS['jednostkiMiary'][$this->infoSql['products_jm_id']]['nazwa'] : '') );
                 //
            }

            // jezeli cena jest rowna 0
            if ( $this->infoSql['products_price_tax'] <= 0 ) {
                if ( isset(Wyglad::PobierzNazwyMenu('formularz')[2]) ) {
                    $CenaProduktu = '<span class="BrakCeny"><a href="'.( WLACZENIE_SSL == 'tak' ? ADRES_URL_SKLEPU_SSL."/" : '') . Seo::link_SEO( Wyglad::PobierzNazwyMenu('formularz')[2], 2, 'formularz' ) . '/produkt=' . $this->infoSql['products_id'].'">' . $GLOBALS['tlumacz']['CENA_ZAPYTAJ_O_CENE'] . '</a></span>';
                    $CenaProduktuBrutto = '';
                } else {
                    $CenaProduktu = '<span class="BrakCeny">' . $GLOBALS['tlumacz']['CENA_ZAPYTAJ_O_CENE'] . '</span>';
                }
                $CenaProduktuBrutto = '';
            }        
            // jezeli ceny sa tylko widoczne dla klientow zalogowanych
            if ( CENY_DLA_WSZYSTKICH == 'nie' && ((int)$_SESSION['customer_id'] == 0 || $_SESSION['gosc'] == '1')) {
                $CenaProduktu = '<span class="CenaDlaZalogowanych">' . $GLOBALS['tlumacz']['CENA_TYLKO_DLA_ZALOGOWANYCH'] . '</span>';
                $CenaProduktuBrutto = '';
            }
            //
            $PokazCeneNiezalogowanym = 'tak';
            // ukrycie ceny dla niezalogowanych tylko dla konkretnego produktu
            if ( (int)$this->infoSql['products_price_login'] == 1 ) {
                 //
                 if ( ((int)$_SESSION['customer_id'] == 0 || $_SESSION['gosc'] == '1')) {
                     $CenaProduktu = '<span class="CenaDlaZalogowanych">' . $GLOBALS['tlumacz']['CENA_TYLKO_DLA_ZALOGOWANYCH'] . '</span>';
                     $CenaProduktuBrutto = '';
                     $PokazCeneNiezalogowanym = 'nie';
                 }
                 //
            }
            // ukrycie cen dla wszystkich
            if ( UKRYJ_CENY == 'nie' ) {
                $CenaProduktu = '';
                $CenaProduktuBrutto = '';
            }                
            
            // ceny bez formatowania - same kwoty po przeliczeniu - cena brutto, netto i promocyjna
            $TablicaCenyProduktu = $GLOBALS['waluty']->FormatujCene( $this->infoSql['products_price_tax'], $this->infoSql['products_price'], $this->infoSql['products_old_price'], $this->infoSql['products_currencies_id'], false );
            
            // sprawdzi czy cena katalogowa nie jest nizsza od glownej
            if ( $this->infoSql['products_retail_price'] < $this->infoSql['products_price_tax'] ) {
                 $this->infoSql['products_retail_price'] = 0;
            }
            
            // cena katalogowa
            $CenaKatalogowa = $GLOBALS['waluty']->FormatujCene( $this->infoSql['products_retail_price'], 0, 0, $this->infoSql['products_currencies_id'], false );
            
            // wykluczenie grup klientow z darmowej wysylki
            $DarmowaWysylkaGrupaKlientow = true;
            // dla klientow bez rejestracji
            if (isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '1' || (!isset($_SESSION['customer_id']) || (int)$_SESSION['customer_id'] == 0)) {
                //
                if ( in_array('0', explode(',', (string)$this->infoSql['free_shipping_status_customers_group_id'])) ) {
                     //
                     $DarmowaWysylkaGrupaKlientow = false;
                     //
                }
                //              
            }
            // dla klientow z rejestracja
            if (isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0' ) {
                //
                if ( in_array($_SESSION['customers_groups_id'], explode(',', (string)$this->infoSql['free_shipping_status_customers_group_id'])) ) {
                     //
                     $DarmowaWysylkaGrupaKlientow = false;
                     //
                }
                //
            }
            
            // jezeli jest stan = 0 i id czasu wysylki dla stanu 0
            if ( (float)$this->infoSql['products_quantity'] < 0.01 && (int)$this->infoSql['products_shipping_time_zero_quantity_id'] > 0 ) {
                 //
                 $this->infoSql['products_shipping_time_id'] = (int)$this->infoSql['products_shipping_time_zero_quantity_id'];
                 //
            }
            
            // najnizsza cena za 30 dni
            $CenaProduktuNajnizszaBrutto = '';
            $ListingInfoHistoriaCeny = '';
            
            if ( HISTORIA_CEN_LISTINGI == 'tak' && ($ProduktDniaCena == true || (float)$TablicaCenyProduktu['promocja'] > 0) ) {
                 //
                 $ListingInfoHistoriaCeny = '<div class="InfoCena30dni">' . $GLOBALS['tlumacz']['HISTORIA_CENY_BRAK'] . '</div>';
                 //
            }
            
            $IloscDni = 30;
            //
            if ( $this->infoSql['products_min_price_30_day_date_created'] != '0000-00-00' ) {
                 //
                 $IloscDni += ceil((time() - FunkcjeWlasnePHP::my_strtotime($this->infoSql['products_min_price_30_day_date_created'])) / 86400);
                 //
            }   

            if ( $this->infoSql['products_min_price_30_day'] > 0 && FunkcjeWlasnePHP::my_strtotime($this->infoSql['products_min_price_30_day_date']) > (time() - (86400 * $IloscDni)) ) {
                 //
                 $CenaNajnizszaTmp = $GLOBALS['waluty']->FormatujCene( $this->infoSql['products_min_price_30_day'], 0, 0, $this->infoSql['products_currencies_id'] );
                 $CenaProduktuNajnizszaBrutto = $CenaNajnizszaTmp['brutto'];
                 unset($CenaNajnizszaTmp);
                 //
                 if ( HISTORIA_CEN_LISTINGI == 'tak' ) {
                      //
                      if ( $ProduktDniaCena == true || (float)$TablicaCenyProduktu['promocja'] > 0 ) {
                           //
                           $ListingInfoHistoriaCeny = '<div class="InfoCena30dni">' . str_replace('{DATA}', date('d-m-Y', FunkcjeWlasnePHP::my_strtotime($this->infoSql['products_min_price_30_day_date'])), str_replace('{CENA}', $CenaProduktuNajnizszaBrutto, $GLOBALS['tlumacz']['HISTORIA_CENY_KOMUNIKAT_PROMOCJA'])) . '</div>';
                           //
                      } else {
                           //
                           if ( HISTORIA_CEN_PROMOCJE == 'nie' ) {
                                //
                                $ListingInfoHistoriaCeny = '<div class="InfoCena30dni">' . str_replace('{DATA}', date('d-m-Y', FunkcjeWlasnePHP::my_strtotime($this->infoSql['products_min_price_30_day_date'])), str_replace('{CENA}', $CenaProduktuNajnizszaBrutto, $GLOBALS['tlumacz']['HISTORIA_CENY_KOMUNIKAT'])) . '</div>';
                                //
                           }
                           //
                      }
                      //
                 }
                 //
            }
            
            // jezeli ceny sa tylko widoczne dla klientow zalogowanych
            if ( CENY_DLA_WSZYSTKICH == 'nie' && ((int)$_SESSION['customer_id'] == 0 || $_SESSION['gosc'] == '1')) {
                 //
                 $ListingInfoHistoriaCeny = '';
                 //
            }            
            
            unset($IloscDni);

            if ( preg_replace("/<((?:style)).*>.*<\/style>/si", ' ',  (string)$this->infoSql['products_description']) != '' ) {
                $TekstKrotkiAlt = Funkcje::przytnijTekst(strip_tags(preg_replace("/<((?:style)).*>.*<\/style>/si", ' ',  (string)$this->infoSql['products_description'])), '250');
            } else {
                $TekstKrotkiAlt = '';
            }
            
            // opis produktu
            $OpisProduktu = $this->infoSql['products_description'];
            
            if ( strpos((string)$OpisProduktu, '{__DALSZA_CZESC_UKRYTA}') > -1 ) {
                 //
                 $PodzielTekst = explode('{__DALSZA_CZESC_UKRYTA}', (string)$OpisProduktu);
                 //
                 if ( count($PodzielTekst) == 2 ) {
                      $OpisProduktu = Funkcje::TrimBr($PodzielTekst[0]) . '<div style="clear:both"></div><div class="StronaInfoRozwiniecie" id="StronaInfoText-' . '0-' . $this->idUnikat . $this->id_produktu . '"><div class="StronaInfoRozwiniecieTresc">' . Funkcje::TrimBr($PodzielTekst[1]) . '</div></div><div id="StronaInfoWiecej-' . '0-' . $this->idUnikat . $this->id_produktu . '" class="StronaInfo StronaInfoWiecej"><span class="przycisk" data-strona-id="' . '0-' . $this->idUnikat . $this->id_produktu . '">' . $GLOBALS['tlumacz']['CZYTAJ_WIECEJ'] . '</span></div><div style="clear:both"></div>';
                 } else {
                      $OpisProduktu = str_replace('{__DALSZA_CZESC_UKRYTA}', '', (string)$OpisProduktu) . '<div style="clear:both"></div>';
                 }
                 //
                 unset($PodzielTekst);
                 //
            }                 

            $this->info = array('id'                               => $this->infoSql['products_id'],
                                'status_kupowania'                 => (( $this->infoSql['products_buy'] == '1' ) ? 'tak' : 'nie' ),
                                'status_szybkie_kupowanie'         => (( $this->infoSql['products_fast_buy'] == '1' ) ? 'tak' : 'nie' ),
                                'status_akcesoria'                 => (( $this->infoSql['products_accessory'] == '1' ) ? 'tak' : 'nie' ),
                                'tylko_za_punkty'                  => (( $this->infoSql['products_points_only'] == '1' && SYSTEM_PUNKTOW_STATUS == 'tak' && SYSTEM_PUNKTOW_STATUS_KUPOWANIA == 'tak' && Punkty::PunktyAktywneDlaKlienta() ) ? 'tak' : 'nie' ),
                                'cena_w_punktach'                  => (( $this->infoSql['products_points_only'] == '1' && SYSTEM_PUNKTOW_STATUS == 'tak' && SYSTEM_PUNKTOW_STATUS_KUPOWANIA == 'tak' && Punkty::PunktyAktywneDlaKlienta() ) ? $this->infoSql['products_points_value'] : 0 ),
                                'zakup_za_punkty'                  => (( $this->infoSql['products_points_purchase'] == '1' ) ? 'tak' : 'nie' ),                                
                                'ilosc'                            => ( $this->infoSql['products_quantity'] != '' ? $this->infoSql['products_quantity'] : 0 ),
                                'przyrost'                         => (((float)$this->infoSql['products_quantity_order'] == 0) ? 0 : $this->infoSql['products_quantity_order']),
                                'alarm_magazyn'                    => $this->infoSql['products_quantity_alarm'],
                                'nr_katalogowy'                    => $this->infoSql['products_model'],
                                'kod_producenta'                   => $this->infoSql['products_man_code'],
                                'ean'                              => $this->infoSql['products_ean'],
                                'pkwiu'                            => $this->infoSql['products_pkwiu'],
                                'gtu'                              => $this->infoSql['products_gtu'],
                                'informacja_o_bezpieczenstwie'     => ((!empty($this->infoSql['products_safety_information'])) ? '<a class="przycisk" href="' . $this->infoSql['products_safety_information'] . '" target="_blank">' . $GLOBALS['tlumacz']['ZOBACZ_SZCZEGOLY'] . '</a>' : ''),
                                'nazwa'                            => $this->infoSql['products_name'],
                                'nazwa_dodatkowa'                  => $this->infoSql['products_name_info'],
                                'nazwa_krotka'                     => $this->infoSql['products_name_short'],
                                'nazwa_seo'                        => $linkSeo,
                                'adres_seo'                        => Seo::link_SEO( $linkSeo, $this->infoSql['products_id'], 'produkt' ),
                                'link'                             => '<a href="' . Seo::link_SEO( $linkSeo, $this->infoSql['products_id'], 'produkt' ) . '" title="' . str_replace('"', '', (string)$this->infoSql['products_name']) . '">' . $this->infoSql['products_name'] . '</a>',
                                'link_z_domena'                    => '<a href="' . ADRES_URL_SKLEPU . '/' . Seo::link_SEO( $linkSeo, $this->infoSql['products_id'], 'produkt' ) . '" title="' . str_replace('"', '', (string)$this->infoSql['products_name']) . '">' . $this->infoSql['products_name'] . '</a>',
                                'link_szczegoly'                   => '<a href="' . Seo::link_SEO( $linkSeo, $this->infoSql['products_id'], 'produkt' ) . '" title="' . str_replace('"', '', (string)$this->infoSql['products_name']) . '">' . $GLOBALS['tlumacz']['ZOBACZ_SZCZEGOLY'] . '</a>',
                                'jest_cena'                        => (( $CenaProduktuBrutto == '' ) ? 'nie' : 'tak' ),
                                'jest_promocja'                    => ( $this->infoSql['specials_status'] == 0 ? '0' : '1' ),
                                'cena_dla_niezalogowanych'         => $PokazCeneNiezalogowanym,
                                'cena'                             => $CenaProduktu . $ListingInfoHistoriaCeny,
                                'cena_brutto'                      => $CenaProduktuBrutto,
                                'cena_brutto_bez_formatowania'     => $TablicaCenyProduktu['brutto'],
                                'cena_netto_bez_formatowania'      => $TablicaCenyProduktu['netto'],
                                'cena_poprzednia_bez_formatowania' => (($ProduktDnia == true) ? $ProduktDniaCena : $TablicaCenyProduktu['promocja']),
                                'cena_katalogowa_bez_formatowania' => $CenaKatalogowa['brutto'],                                
                                'cena_najnizsza_30_dni'            => $CenaProduktuNajnizszaBrutto,
                                'cena_najnizsza_30_dni_data'       => date('d-m-Y', FunkcjeWlasnePHP::my_strtotime($this->infoSql['products_min_price_30_day_date'])),
                                'rabat_produktu'                   => (($ProduktDnia == false) ? $CenaRabaty['rabat'] : $GLOBALS['produkt_dnia'][date('Y-m-d', time())]['rabat']),
                                'wylaczone_rabaty'                 => (($this->infoSql['products_not_discount'] == 0) ? 'nie' : 'tak'),
                                'produkt_dnia'                     => (($ProduktDnia == true) ? 'tak' : 'nie'),
                                'vat_bez_formatowania'             => $TablicaCenyProduktu['brutto'] - $TablicaCenyProduktu['netto'],
                                'stawka_vat'                       => $this->infoSql['tax_rate'],
                                'stawka_vat_id'                    => $this->infoSql['products_tax_class_id'],
                                'opis'                             => '<div class="FormatEdytor">' . $OpisProduktu . '</div>',
                                'opis_krotki'                      => '<div class="FormatEdytor">' . (( !empty($this->infoSql['products_short_description']) ) ? $this->infoSql['products_short_description'] : $TekstKrotkiAlt ) . '</div>',
                                'id_dostepnosci'                   => $this->infoSql['products_availability_id'],
                                'id_waluty'                        => $this->infoSql['products_currencies_id'],
                                'id_producenta'                    => $this->infoSql['manufacturers_id'],
                                'id_czasu_wysylki'                 => $this->infoSql['products_shipping_time_id'], 	
                                'id_czasu_wysylki_stan_zero'       => $this->infoSql['products_shipping_time_zero_quantity_id'], 	
                                'nazwa_producenta'                 => (string)$this->infoSql['manufacturers_name'],
                                'foto_producenta'                  => (string)$this->infoSql['manufacturers_image'],
                                'jednostka_miary'                  => (( isset($GLOBALS['jednostkiMiary'][$this->infoSql['products_jm_id']]['nazwa']) ) ? $GLOBALS['jednostkiMiary'][$this->infoSql['products_jm_id']]['nazwa'] : ''),
                                'jednostka_miary_typ'              => (( isset($GLOBALS['jednostkiMiary'][$this->infoSql['products_jm_id']]['typ']) ) ? $GLOBALS['jednostkiMiary'][$this->infoSql['products_jm_id']]['typ'] : ''),
                                'waga'                             => $this->infoSql['products_weight'],
                                'waga_szerokosc'                   => $this->infoSql['products_weight_width'],
                                'waga_wysokosc'                    => $this->infoSql['products_weight_height'],
                                'waga_dlugosc'                     => $this->infoSql['products_weight_length'],
                                'gabaryt'                          => $this->infoSql['products_pack_type'],
                                'osobna_paczka'                    => $this->infoSql['products_separate_package'],
                                'osobna_paczka_ilosc'              => $this->infoSql['products_separate_package_quantity'],
                                'ilosc_wyswietlen'                 => $this->infoSql['products_viewed'],
                                'komentarze_do_produktu'           => (( $this->infoSql['products_comments'] == '1' ) ? 'tak' : 'nie' ),
                                'dostepne_wysylki'                 => $this->infoSql['shipping_method'],
                                'darmowa_wysylka'                  => (( $this->infoSql['free_shipping_status'] == '1' && $DarmowaWysylkaGrupaKlientow == true ) ? 'tak' : 'nie' ),
                                'wykluczona_darmowa_wysylka'       => (( $this->infoSql['free_shipping_excluded'] == '1' ) ? 'tak' : 'nie' ),
                                'wykluczony_punkt_odbioru'         => (( $this->infoSql['pickup_excluded'] == '1' ) ? 'tak' : 'nie' ),
                                'koszt_wysylki'                    => $this->infoSql['shipping_cost'],
                                'koszt_wysylki_ilosc'              => $this->infoSql['shipping_cost_quantity'],
                                'koszt_wysylki_pobranie'           => $this->infoSql['shipping_cost_delivery'],
                                'inpost_gabaryt'                   => $this->infoSql['inpost_size'],
                                'inpost_ilosc'                     => $this->infoSql['inpost_quantity'],
                                'negocjacja'                       => (( $this->infoSql['products_points_only'] == '1' && SYSTEM_PUNKTOW_STATUS == 'tak' && SYSTEM_PUNKTOW_STATUS_KUPOWANIA == 'tak' && Punkty::PunktyAktywneDlaKlienta() ) ? 'nie' : (( $this->infoSql['products_make_an_offer'] == '1' ) ? 'tak' : 'nie' ) ),
                                'data_dostepnosci'                 => ((Funkcje::czyNiePuste($this->infoSql['products_date_available']) && FunkcjeWlasnePHP::my_strtotime($this->infoSql['products_date_available']) > time()) ? date('d-m-Y',FunkcjeWlasnePHP::my_strtotime($this->infoSql['products_date_available'])) : ''),
                                'data_od_kiedy_kupowac'            => ((Funkcje::czyNiePuste($this->infoSql['products_date_available_buy']) && FunkcjeWlasnePHP::my_strtotime($this->infoSql['products_date_available_buy']) > time()) ? date('d-m-Y H:i',FunkcjeWlasnePHP::my_strtotime($this->infoSql['products_date_available_buy'])) : ''),
                                'zegar_dostepnosci'                => (((int)$this->infoSql['products_date_available_clock'] == 1 && Funkcje::czyNiePuste($this->infoSql['products_date_available_buy'])) ? (((FunkcjeWlasnePHP::my_strtotime($this->infoSql['products_date_available_buy']) - time()) > 0) ? 'tak' : 'nie') : 'nie'),
                                'typ_cech'                         => $this->infoSql['options_type'],
                                'id_kategorii'                     => $this->infoSql['categories_id'],
                                'typ_produktu'                     => $this->infoSql['products_type'],
                                'ilosc_kupionych'                  => $this->infoSql['products_ordered'],
                                'kontrola_magazynu'                => $this->infoSql['products_control_storage'],
                                'zestaw'                           => (($this->infoSql['products_set'] == 1) ? 'tak' : 'nie'),
                                'zestaw_produkty'                  => (($this->infoSql['products_set'] == 1) ? $this->infoSql['products_set_products'] : ''),
                                'pkt_naliczanie'                   => (($this->infoSql['products_counting_points'] == 1) ? 'tak' : 'nie'),
                                'rozmiar'                          => (float)$this->infoSql['products_size'],
                                'rozmiar_jm'                       => $this->infoSql['products_size_type'],
                                'klasa_energetyczna'               => (($this->infoSql['products_energy'] != '') ? (($this->infoSql['products_energy_img'] != '') ? '<a class="FotoEtykietaEnergetyczna" title="' . $GLOBALS['tlumacz']['KLASA_ENERGETYCZNA'] . ' ' . $this->infoSql['products_energy'] . '" href="' . KATALOG_ZDJEC . '/' . $this->infoSql['products_energy_img'] . '">' : '') . '<b class="KlasaEnergetyczna KlasaEnergetyczna-' . str_replace('+','p',$this->infoSql['products_energy']) . '">' . $this->infoSql['products_energy'] . '</b>' . (($this->infoSql['products_energy_img'] != '') ? '</a>' : '') . (($this->infoSql['products_energy_pdf'] != '') ? '<a class="PdfEtykietaEnergetyczna" href="' . KATALOG_ZDJEC . '/' . $this->infoSql['products_energy_pdf'] . '">' . $GLOBALS['tlumacz']['KLASA_ENERGETYCZNA_KARTA_INFORMACYJNA'] . '</a>' : '') : ''),
                                'inne_warianty'                    => array( 'foto' => (((int)$this->infoSql['products_other_variant_image'] == 1) ? 'tak' : 'nie'), 'nazwa' => (((int)$this->infoSql['products_other_variant_name'] == 1) ? 'tak' : 'nie'), 'nazwa_typ' => (((int)$this->infoSql['products_other_variant_name_type'] == 1) ? 'pelna' : 'krotka'), 'cena' => (((int)$this->infoSql['products_other_variant_price'] == 1) ? 'tak' : 'nie') ),
                                'podsumowanie_recenzji'            => $this->infoSql['products_review_summary']
            );     

            $ZapytanieProdukt = '';
            
            if ( isset($_SESSION['formularze']) ) {
                 //
                 if ( isset($_SESSION['formularze'][2]) ) {
                      //
                      $ZapytanieProdukt = '<a class="przycisk ZapytanieProduktListing" title="' . $GLOBALS['tlumacz']['ZAPYTAJ_O_PRODUKT'] . ' ' . str_replace('"', '', (string)$this->infoSql['products_name']) . '" aria-label="' . $GLOBALS['tlumacz']['ZAPYTAJ_O_PRODUKT'] . ' ' . str_replace('"', '', (string)$this->infoSql['products_name']) . '" href="' . Seo::link_SEO( $_SESSION['formularze'][2], 2, 'formularz' ) . '/produkt=' . $this->infoSql['products_id'] . '">' . $GLOBALS['tlumacz']['ZAPYTAJ_O_PRODUKT'] . '</a>';
                      //
                 }
                 //
            }
            
            $this->info['zapytanie_o_produkt'] = $ZapytanieProdukt;

            unset($TablicaCenyProduktu, $CenaRabaty, $CenaKatalogowa, $ProduktDniaCena, $PokazCene, $ListingInfoHistoriaCeny, $OpisProduktu);
            
            // ciag znizek zaleznych od ilosci
            $this->znizkiZalezneOdIlosciTyp = $this->infoSql['products_discount_type'];
            //
            if ( $this->CenaIndywidualna == false && $this->infoSql['products_not_discount'] == 0 ) {
                 //
                 if (ZNIZKI_OD_ILOSCI_PROMOCJE == 'tak' || (ZNIZKI_OD_ILOSCI_PROMOCJE == 'nie' && ($this->info['cena_poprzednia_bez_formatowania'] == 0 || $this->info['cena_poprzednia_bez_formatowania'] == '')) || $this->znizkiZalezneOdIlosciTyp == 'cena') {
                   //
                   if (ZNIZKI_OD_ILOSCI_SUMOWANIE_RABATOW == 'tak' || (ZNIZKI_OD_ILOSCI_SUMOWANIE_RABATOW == 'nie' && $this->info['rabat_produktu'] == 0)) {
                      //
                      // jezeli produkt jest tylko za PUNKTY
                      if ( $this->infoSql['products_points_only'] == '1' && SYSTEM_PUNKTOW_STATUS == 'tak' && SYSTEM_PUNKTOW_STATUS_KUPOWANIA == 'tak' && Punkty::PunktyAktywneDlaKlienta() ) {
                           $this->znizkiZalezneOdIlosci = '';
                         } else {
                           //
                           $this->znizkiZalezneOdIlosci = $this->infoSql['products_discount'];
                           //
                           // sprawdzi czas obowiazywania znizki
                           if ( ($this->infoSql['products_discount_date'] != '0000-00-00 00:00:00' && FunkcjeWlasnePHP::my_strtotime($this->infoSql['products_discount_date']) > time()) || ($this->infoSql['products_discount_date_end'] != '0000-00-00 00:00:00' && FunkcjeWlasnePHP::my_strtotime($this->infoSql['products_discount_date_end']) < time()) ) {
                                 $this->znizkiZalezneOdIlosci = '';
                           }
                           //
                      }
                      //
                   }
                   //
                 }     
                 //
            }
            
            // wylaczenie znizek od ilosci dla klientow
            if ( $this->infoSql['products_discount_group_id'] != '' ) {
                 //
                 $WylaczZnizki = false;
                 //
                 if ( count(explode(',', (string)$this->infoSql['products_discount_group_id'])) > 0 ) {
                     //
                     if ( isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0' && isset($_SESSION['customers_groups_id']) && in_array($_SESSION['customers_groups_id'], explode(',', (string)$this->infoSql['products_discount_group_id'])) ) {
                          //
                          $WylaczZnizki = false;
                          //
                       } else {
                          //
                          $WylaczZnizki = true;
                          //
                          // sprawdzi czy nie jest dla niezalogowanych
                          //
                          if ( !isset($_SESSION['customers_groups_id']) ) {
                               //
                               if ( in_array('0', explode(',', (string)$this->infoSql['products_discount_group_id'])) ) {
                                    //
                                    $WylaczZnizki = false;
                                    //
                               }
                               //
                          }
                          //
                     }
                     //
                 }
                 //
                 if ( $WylaczZnizki == true ) {
                      //
                      $this->znizkiZalezneOdIlosci = '';       
                      $this->znizkiZalezneOdIlosciTyp = '';
                      //
                 }
                 //
            }
            
            $this->metaTagi = array('tytul' => (( empty($this->infoSql['products_meta_title_tag']) ) ? strip_tags((string)$this->infoSql['products_name']) : $this->infoSql['products_meta_title_tag']),
                                    'tytul_uzupelniony' => (( empty($this->infoSql['products_meta_title_tag']) ) ? false : true),
                                    'opis' => (( empty($this->infoSql['products_meta_desc_tag']) ) ? strip_tags((string)$this->infoSql['products_name']) : $this->infoSql['products_meta_desc_tag']),
                                    'opis_uzupelniony' => (( empty($this->infoSql['products_meta_desc_tag']) ) ? false : true),
                                    'slowa' => (( empty($this->infoSql['products_meta_keywords_tag']) ) ? strip_tags((string)$this->infoSql['products_name']) : $this->infoSql['products_meta_keywords_tag']),
                                    'slowa_uzupelnione' => (( empty($this->infoSql['products_meta_keywords_tag']) ) ? false : true),
                                    'link_kanoniczny' => $this->infoSql['products_link_canonical'],
                                    'tagi_szukania' => $this->infoSql['products_search_tag'],
                                    'og_title' => $this->infoSql['products_og_title'],
                                    'og_description' => $this->infoSql['products_og_description']);
            
            
            // ustala jaka ma alt zdjecia
            $altFoto = htmlspecialchars(((!empty($this->infoSql['products_image_description'])) ? (string)$this->infoSql['products_image_description'] : strip_tags((string)$this->infoSql['products_name'])));

            if ( FunkcjeWlasnePHP::my_strtotime($this->infoSql['star_date']) < time() || ((int)FunkcjeWlasnePHP::my_strtotime($this->infoSql['star_date_end']) > 0 && FunkcjeWlasnePHP::my_strtotime($this->infoSql['star_date_end']) < time()) ) {
              $this->ikonki['hit'] = $this->infoSql['star_status'];
            } else {
              $this->ikonki['hit'] = '0';
            }
            if ( FunkcjeWlasnePHP::my_strtotime($this->infoSql['specials_date']) < time() || ((int)FunkcjeWlasnePHP::my_strtotime($this->infoSql['specials_date_end']) > 0 && FunkcjeWlasnePHP::my_strtotime($this->infoSql['specials_date_end']) < time()) ) {
              $this->ikonki['promocja'] = $this->infoSql['specials_status'];
            } else {
              $this->ikonki['promocja'] = '0';          
            }
            if ( $this->ikonki['promocja'] != '0' ) {    
              if ( $this->info['cena_poprzednia_bez_formatowania'] > 0 ) {
                   $this->ikonki['promocja_procent'] = round((100 - (($this->info['cena_brutto_bez_formatowania'] / $this->info['cena_poprzednia_bez_formatowania']) * 100)),0);
              } else {
                   $this->ikonki['promocja_procent'] = 0;
              }
            } else {
              $this->ikonki['promocja_procent'] = 0;
            }
            //
            $this->ikonki['promocja_data_od'] = FunkcjeWlasnePHP::my_strtotime($this->infoSql['specials_date']);
            $this->ikonki['promocja_data_do'] = FunkcjeWlasnePHP::my_strtotime($this->infoSql['specials_date_end']);   
            //
            if ( $this->ikonki['promocja'] == '0' ) {
              $this->ikonki['wyprzedaz'] = $this->infoSql['sale_status'];
            } else {
              $this->ikonki['wyprzedaz'] = '0';
            }   
            //
            //  jezeli produkt jest produktem dnia
            if ( $ProduktDnia == true ) {
              $this->ikonki['promocja'] = '0'; 
              $this->ikonki['rabat'] = '0';
            }            
            //
            if ( FunkcjeWlasnePHP::my_strtotime($this->infoSql['featured_date']) < time() || ((int)FunkcjeWlasnePHP::my_strtotime($this->infoSql['featured_date_end']) > 0 && FunkcjeWlasnePHP::my_strtotime($this->infoSql['featured_date_end']) < time()) ) {
              $this->ikonki['polecany'] = $this->infoSql['featured_status'];
            } else {
              $this->ikonki['polecany'] = '0';
            }

            $this->ikonki['nowosc'] = $this->infoSql['new_status'];    

            $this->ikonki['darmowa_dostawa'] = (($this->infoSql['free_shipping_excluded'] == '1' || $DarmowaWysylkaGrupaKlientow == false) ? '0' : $this->infoSql['free_shipping_status']);             
            
            // dodatkowe ikonki
            $TablicaOpcje = array(array('nr' => 1, 'aktywne' => IKONY_NA_ZDJECIACH_DODATKOWA_1),
                                  array('nr' => 2, 'aktywne' => IKONY_NA_ZDJECIACH_DODATKOWA_2),
                                  array('nr' => 3, 'aktywne' => IKONY_NA_ZDJECIACH_DODATKOWA_3),
                                  array('nr' => 4, 'aktywne' => IKONY_NA_ZDJECIACH_DODATKOWA_4),
                                  array('nr' => 5, 'aktywne' => IKONY_NA_ZDJECIACH_DODATKOWA_5));               
                       
            foreach ( $TablicaOpcje as $Tmp ) {
                //
                if ( $Tmp['aktywne'] == 'tak' && $this->infoSql['icon_' . $Tmp['nr'] . '_status'] == '1' ) {
                     //
                     $this->ikonki['ikona_' . $Tmp['nr']] = '1'; 
                     //
                } else {
                     //
                     $this->ikonki['ikona_' . $Tmp['nr']] = '0'; 
                     //
                }
                //
            }
            
            unset($TablicaOpcje, $Tmp);
            
            // czy jest wypelnione pola obrazka glownego
            if ((empty($this->infoSql['products_image']) && POKAZ_DOMYSLNY_OBRAZEK == 'tak') || !empty($this->infoSql['products_image'])) {
                //
                if (empty($this->infoSql['products_image'])) {
                    $this->infoSql['products_image'] = 'domyslny.webp';
                }
                //
                $linkIdFoto = 'id="fot_' . $this->idUnikat . $this->id_produktu . '" ';
                //
                $this->fotoGlowne = array('plik_zdjecia'       => $this->infoSql['products_image'],
                                          'zdjecie_bez_css'    => Funkcje::pokazObrazek($this->infoSql['products_image'], $altFoto, $this->szerImg, $this->wysImg, array(), (($this->preloadImg == true ) ? 'class="Reload"' : ''), $this->waterMark, true, $this->preloadImg),
                                          'zdjecie'            => Funkcje::pokazObrazek($this->infoSql['products_image'], $altFoto, $this->szerImg, $this->wysImg, array(), $linkIdFoto . 'class="Zdjecie' . (($this->preloadImg == true ) ? ' Reload' : '') . '"', $this->waterMark, true, $this->preloadImg),
                                          'zdjecie_ikony'      => Funkcje::pokazObrazek($this->infoSql['products_image'], $altFoto, $this->szerImg, $this->wysImg, $this->ikonki, $linkIdFoto . 'class="Zdjecie' . (($this->preloadImg == true ) ? ' Reload' : '') . '"', $this->waterMark, true, $this->preloadImg),
                                          'zdjecie_link'       => '<a class="Zoom" href="' . Seo::link_SEO( $linkSeo, $this->infoSql['products_id'], 'produkt' ) . '">' . Funkcje::pokazObrazek($this->infoSql['products_image'], $altFoto, $this->szerImg, $this->wysImg, array(), $linkIdFoto . 'class="Zdjecie' . (($this->preloadImg == true ) ? ' Reload' : '') . '"', $this->waterMark, true, $this->preloadImg) . '</a>',
                                          'zdjecie_link_ikony' => '<a class="Zoom" href="' . Seo::link_SEO( $linkSeo, $this->infoSql['products_id'], 'produkt' ) . '">' . Funkcje::pokazObrazek($this->infoSql['products_image'], $altFoto, $this->szerImg, $this->wysImg, $this->ikonki, $linkIdFoto . 'class="Zdjecie' . (($this->preloadImg == true ) ? ' Reload' : '') . '"', $this->waterMark, true, $this->preloadImg) . '</a>',
                                          'same_ikony'         => $this->ikonki,
                                          'opis_zdjecia'       => $altFoto); 
                
                if ( defined('PODMIANA_ZDJEC') && $this->szerImg > 100 ) {
                     //
                     // zdjecie podmiana po najechaniu na zdjecie
                     $DrugieZdjecieTablica = $this->ProduktDodatkoweZdjecia();
                     //
                     if ( count($DrugieZdjecieTablica) > 0 ) {
                          //
                          $fotoTmp = '<div class="PodmianaZdjecie">
                                         <a  class="Zoom" href="' . Seo::link_SEO( $linkSeo, $this->infoSql['products_id'], 'produkt' ) . '">
                                             <span class="ZdjecieGlownePodmiana">' . Funkcje::pokazObrazek($this->infoSql['products_image'], $altFoto, $this->szerImg, $this->wysImg, $this->ikonki, $linkIdFoto . 'class="Zdjecie' . (($this->preloadImg == true ) ? ' Reload' : '') . '"', $this->waterMark, true, $this->preloadImg) . '</span>
                                             <span class="ZdjecieDrugiePodmiana">' . Funkcje::pokazObrazek($DrugieZdjecieTablica[0]['zdjecie'], $DrugieZdjecieTablica[0]['alt'], $this->szerImg, $this->wysImg, array(), 'id="fotd_' . $this->idUnikat . $this->id_produktu . '" class="Zdjecie ZdjecieDrugie"', $this->waterMark, true, false) . '</span>
                                         </a>
                                     </div>';
                          //
                          $this->fotoGlowne['zdjecie_link_ikony'] = $fotoTmp;
                          unset($fotoTmp);
                          //
                          $fotoTmp = '<div class="PodmianaZdjecie">
                                          <a class="Zoom" href="' . Seo::link_SEO( $linkSeo, $this->infoSql['products_id'], 'produkt' ) . '">
                                            <span class="ZdjecieGlownePodmiana">' . 
                                            Funkcje::pokazObrazek($this->infoSql['products_image'], $altFoto, $this->szerImg, $this->wysImg, array(), $linkIdFoto . 'class="Zdjecie' . (($this->preloadImg == true ) ? ' Reload' : '') . '"', $this->waterMark, true, $this->preloadImg) . '</span>
                                            <span class="ZdjecieDrugiePodmiana">' . Funkcje::pokazObrazek($DrugieZdjecieTablica[0]['zdjecie'], $DrugieZdjecieTablica[0]['alt'], $this->szerImg, $this->wysImg, array(), 'id="fotd_' . $this->idUnikat . $this->id_produktu . '" class="Zdjecie ZdjecieDrugie"', $this->waterMark, true, false) . '</span>
                                         </a>
                                     </div>';
                          $this->fotoGlowne['zdjecie_link'] = $fotoTmp;
                          unset($fotoTmp);
                          //
                     } else {
                          //
                          $this->fotoGlowne['zdjecie_link_ikony'] = '<a class="Zoom" href="' . Seo::link_SEO( $linkSeo, $this->infoSql['products_id'], 'produkt' ) . '">' . Funkcje::pokazObrazek($this->infoSql['products_image'], $altFoto, $this->szerImg, $this->wysImg, $this->ikonki, $linkIdFoto . 'class="Zdjecie' . (($this->preloadImg == true ) ? ' Reload' : '') . '"', $this->waterMark, true, $this->preloadImg) . '</a>';
                          //
                          $this->fotoGlowne['zdjecie_link'] = '<a class="Zoom" href="' . Seo::link_SEO( $linkSeo, $this->infoSql['products_id'], 'produkt' ) . '">' . Funkcje::pokazObrazek($this->infoSql['products_image'], $altFoto, $this->szerImg, $this->wysImg, array(), $linkIdFoto . 'class="Zdjecie' . (($this->preloadImg == true ) ? ' Reload' : '') . '"', $this->waterMark, true, $this->preloadImg) . '</a>';
                          //
                     }
                     //
                     unset($DrugieZdjecieTablica);
                     //
                }
                unset($linkIdFoto);
                //
              } else {
                //
                $this->fotoGlowne = array('plik_zdjecia' => '', 'zdjecie_bez_css' => '', 'zdjecie' => '', 'zdjecie_ikony' => '', 'zdjecie_link'  => '', 'zdjecie_link_ikony' => '', 'same_ikony' => $this->ikonki, 'opis_zdjecia' => '');           
                //
            }
            
            unset($linkSeo, $altFoto);
            
            return true;
        
        } else {
        
            return false;
            
        }
        
        if ( !$WynikCache ) {
            $GLOBALS['db']->close_query($sqlProdukt); 
        }        
                    
        unset($zapProdukt, $IleRekordow, $WynikCache);
    
    }    
    
    public function CenaProduktuPoRabatach($netto, $brutto) {
        //
        $pobierzFunkcje = true;
        include('produkt/CenaProduktuPoRabatach.php');
        unset($pobierzFunkcje);
        //
        // jezeli produkt jest tylko za punkty 
        if ( $this->infoSql['products_points_only'] == '1' && SYSTEM_PUNKTOW_STATUS == 'tak' && SYSTEM_PUNKTOW_STATUS_KUPOWANIA == 'tak' && Punkty::PunktyAktywneDlaKlienta() ) {
             // jezeli jest tylko za punkty nie ma rabatow
             return array( 'netto' => $netto, 'brutto' => $brutto, 'rabat' => 0 );
          } else {
             return array( 'netto' => $cenaNetto, 'brutto' => $cenaBrutto, 'rabat' => $Rabat );
        }
        //
    }
    
    // dodatkowe zdjecia produktu
    public function ProduktDodatkoweZdjecia() {
        //
        $pobierzFunkcje = true;
        include('produkt/ProduktDodatkoweZdjecia.php');
        unset($pobierzFunkcje);
        //
        return $DodatkoweZdjecia;
        //
    }
    
    // kupowanie produktu
    public function ProduktKupowanie( $id = '' ) {
        //
        $pobierzFunkcje = true;
        include('produkt/ProduktKupowanie.php');
        unset($pobierzFunkcje);
        //
    }
    
    // recenzje produktu
    public function ProduktRecenzje() { 
        //
        $pobierzFunkcje = true;
        include('produkt/ProduktRecenzje.php');
        unset($pobierzFunkcje);
        //
    }
    
    // czas wysylki produktu
    public function ProduktCzasWysylki( $id_wysylki = 0 ) {
        //
        $WysylkaTablica = array('nazwa' => '', 'dni' => 0);
        //
        $pobierzFunkcje = true;
        include('produkt/ProduktCzasWysylki.php');
        unset($pobierzFunkcje);
        //
        return $WysylkaTablica;
        //
    }
    
    // stan produktu
    public function ProduktStanProduktu() {
        //
        $pobierzFunkcje = true;
        include('produkt/ProduktStanProduktu.php');
        unset($pobierzFunkcje);
        //
    }
    
    // stan produktu MicroData
    public function ProduktStanProduktuMicroData() {
        //
        $pobierzFunkcje = true;
        include('produkt/ProduktStanProduktuMicroData.php');
        unset($pobierzFunkcje);
        //
    }   


    // gwarancja
    public function ProduktGwarancja() {
        //
        $pobierzFunkcje = true;
        include('produkt/ProduktGwarancja.php');
        unset($pobierzFunkcje);
        //
    }    
    
    // dostepnosc produktu
    public function ProduktDostepnosc( $idDostepnosci = '', $iloscProduktu = '' ) {
        //
        $pobierzFunkcje = true;
        include('produkt/ProduktDostepnosc.php');
        unset($pobierzFunkcje);
        //
    }
    
    // funkcja zwracajaca ID dostepnosci produktu dla dostepnosci automatycznych
    public function PokazIdDostepnosciAutomatycznych( $iloscProduktu ) {
        //
        $pobierzFunkcje = true;
        include('produkt/PokazIdDostepnosciAutomatycznych.php');
        unset($pobierzFunkcje);
        //
        return $dostepnosc_id;
        //
    }    
    
    // dane producenta
    public function ProduktProducent( $szerokoscImg = SZEROKOSC_LOGO_PRODUCENTA, $wysokoscImg = WYSOKOSC_LOGO_PRODUCENTA ) {
        //
        $pobierzFunkcje = true;
        include('produkt/ProduktProducent.php');
        unset($pobierzFunkcje);
        //
    }    

    // okresla znizke produktu w zaleznosci od ilosci w koszyku
    public function ProduktZnizkiZalezneOdIlosci( $ilosc ) {
        //
        $pobierzFunkcje = true;
        include('produkt/ProduktZnizkiZalezneOdIlosci.php');
        unset($pobierzFunkcje);
        //
        return $ZnizkaWynik; 
        //
    }
    
    // zwraca tablice z znizkami od ilosci w koszyku
    public function ProduktZnizkiZalezneOdIlosciTablica() {
        //
        $pobierzFunkcje = true;
        include('produkt/ProduktZnizkiZalezneOdIlosciTablica.php');
        unset($pobierzFunkcje);
        //
        return $ZnizkaTablica;
        //    
    }    
    
    // dodatkowe pola do produktu
    public function ProduktDodatkowePola() {
        //
        $pobierzFunkcje = true;
        include('produkt/ProduktDodatkowePola.php');
        unset($pobierzFunkcje);
        //
    }   
    
    // dodatkowe pola tekstowe do produktu
    public function ProduktDodatkowePolaTekstowe() {
        //
        $pobierzFunkcje = true;
        include('produkt/ProduktDodatkowePolaTekstowe.php');
        unset($pobierzFunkcje);
        //
    }     

    // cechy produktu
    public function ProduktCechyIlosc() {
        //
        $pobierzFunkcje = true;
        include('produkt/ProduktCechyIlosc.php');
        unset($pobierzFunkcje);
        //
    }    
    
    // cena wybranych cech
    public function ProduktWartoscCechy( $cechy, $cenaPoZnizkachBrutto = 0, $cenaPoZnizkachNetto = 0 ) {
        //
        $pobierzFunkcje = true;
        include('produkt/ProduktWartoscCechy.php');
        unset($pobierzFunkcje);
        //
        return array( 'brutto' => $TablicaCen['brutto'], 'netto' => $TablicaCen['netto'], 'waga' => $WagaCechy ) ;
        //    
    }
    
    // cena produktu z okreslona kombinacja cech - uzywane jezeli produkt ma stale ceny dla cech
    public function ProduktWartoscCechyCeny( $cechy ) {
        //
        $pobierzFunkcje = true;
        include('produkt/ProduktWartoscCechyCeny.php');
        unset($pobierzFunkcje);
        //
        return array( 'brutto' => $TablicaCen['brutto'], 'netto' => $TablicaCen['netto'], 'waga' => $WagaCechy ) ; 
        //    
    }
    
    // podaje nr katalogowy dla danych cech produktu
    public function ProduktCechyNrKatalogowy( $cechy ) {
        //
        $pobierzFunkcje = true;
        include('produkt/ProduktCechyNrKatalogowy.php');
        unset($pobierzFunkcje);
        //
        return array( 'nr_kat' => $NrKatalogowyCechy, 'czas_wysylki' => $CzasWysylkiCechy, 'ean' => $KodEan, 'zdjecie' => $ZdjecieCechy );
        //        
    }
    
    public function ProduktCechyGeneruj() {
        //
        $pobierzFunkcje = true;
        include('produkt/ProduktCechyGeneruj.php');
        unset($pobierzFunkcje);
        //
        return $CiagJs . $Wynik;
        //
    }    
    
    public function ProduktCechyGenerujPDF() {
        //
        $pobierzFunkcje = true;
        include('produkt/ProduktCechyGenerujPDF.php');
        unset($pobierzFunkcje);
        //
        return $Wynik;
        //        
    }    

    // linki do produktu
    public function ProduktLinki() {
        //
        $pobierzFunkcje = true;
        include('produkt/ProduktLinki.php');
        unset($pobierzFunkcje);
        //
    }  

    // dodatkowe zakladki do produktu
    public function ProduktDodatkoweZakladki() {
        //
        $pobierzFunkcje = true;
        include('produkt/ProduktDodatkoweZakladki.php'); 
        unset($pobierzFunkcje);
        //
    }     
    
    // dodatkowe opisy do produktu
    public function ProduktDodatkoweOpisy() {
        //
        $pobierzFunkcje = true;
        include('produkt/ProduktDodatkoweOpisy.php'); 
        unset($pobierzFunkcje);
        //
        return $TablicaDodatkoweOpisy;
        //
    }         
    
    // pliki do produktu
    public function ProduktPliki() {
        //
        $pobierzFunkcje = true;
        include('produkt/ProduktPliki.php'); 
        unset($pobierzFunkcje);
        //
    }   

    // filmy youtube
    public function ProduktYoutube() {
        //
        $pobierzFunkcje = true;
        include('produkt/ProduktYoutube.php'); 
        unset($pobierzFunkcje);
        //
    }   

    // filmy flv
    public function ProduktFilmyFLV() {
        //
        $pobierzFunkcje = true;
        include('produkt/ProduktFilmyFLV.php'); 
        unset($pobierzFunkcje);
        //
    }    

    // pliki muzyczne mp3
    public function ProduktMp3() { 
        //
        $pobierzFunkcje = true;
        include('produkt/ProduktMp3.php');   
        unset($pobierzFunkcje);
        //   
    }
    
    // pytania i odpowiedzi faq
    public function ProduktFaq() { 
        //
        $pobierzFunkcje = true;
        include('produkt/ProduktFaq.php');   
        unset($pobierzFunkcje);
        //   
    }    
    
    // linki powiazane z produktem
    public function ProduktLinkiPowiazane() {
        //
        $pobierzFunkcje = true;
        include('produkt/ProduktLinkiPowiazane.php');   
        unset($pobierzFunkcje);
        //   
    }
    
    // inne warianty
    public function ProduktInneWarianty() {
        //
        $pobierzFunkcje = true;
        include('produkt/ProduktInneWarianty.php');   
        unset($pobierzFunkcje);
        //   
    }
    
    // aukcje produktu na Allegro
    public function ProduktAllegro() { 
        //
        $pobierzFunkcje = true;
        include('produkt/ProduktAllegro.php');   
        unset($pobierzFunkcje);
        //    
    }    
    
    // tablica wybranych cech
    public function ProduktCechyTablica( $cechy ) {
        //
        $pobierzFunkcje = true;
        include('produkt/ProduktCechyTablica.php'); 
        unset($pobierzFunkcje);
        //
        return $TablicaCech;
        //
    }
    
    // zakupy produktu
    public function ProduktZakupy() {
        //
        if ( KARTA_PRODUKTU_ZAKLADKA_ZAKUPY == 'tak' || LISTING_ILOSC_KUPIONYCH == 'tak' || KARTA_PRODUKTU_ZAKUPY_HISTORIA == 'tak' ) {
          
            $pobierzFunkcje = true;
            include('produkt/ProduktKupione.php'); 
            unset($pobierzFunkcje);
            //
            $this->iloscKupionych = count($TablicaZakupow);
            $this->tablicaKupionych = $TablicaZakupow;
            $this->iloscKupionychSztuk = $IloscSztuk;
         
        }

    }
    
    // zwraca id i nazwe kategorii glownej produktu
    public function ProduktKategoriaGlowna() {
        //
        $KategorieProduktu = Kategorie::ProduktKategorie($this->infoSql['products_id'], true);
        //
        // przyjmuje na poczatek pierwsza przypisana kategorie
        $DomyslnaGlowna = $KategorieProduktu[0];
        //
        foreach ( $KategorieProduktu as $Kategoria ) {
            //
            if ( $Kategoria['domyslna'] == 1 ) {
                 //
                 $DomyslnaGlowna = $Kategoria;
                 break;
                 //
            }
            //          
        }
        //
        return $DomyslnaGlowna;
        //    
    }
    
    // zwraca linki tagow produktu
    public function ProduktTagiLinki() {
        //
        $TagiProduktu = explode(',', (string)$this->infoSql['products_search_tag']);
        //
        $TablicaTagow = array();
        //
        foreach ( $TagiProduktu as $Tag ) {
            //
            if ( mb_strlen(trim((string)$Tag)) > 1 ) {
                 //
                 $TablicaTagow[] = trim((string)$Tag);
                 //
            }
            //          
        }
        //
        return $TablicaTagow;
        //    
    }    
    
    // zwraca dane o produktach zestawu
    public function ProduktZestawy($tablica_produktu = array()) {
        //
        $pobierzFunkcje = true;
        include('produkt/ProduktZestawy.php');
        unset($pobierzFunkcje);
        //      
        if ( count($tablica_produktu) == 0 ) {
             return $TablicaProduktowZestawu;
        }
        if ( count($tablica_koncowa) > 0 ) {
             return $tablica_koncowa;
        }
        //
    }
    
    // zwraca dane o zestawach do jakich jest przypisany produkt
    public function ProduktJakieZestawy() {
        //
        $pobierzFunkcje = true;
        include('produkt/ProduktJakieZestawy.php');
        unset($pobierzFunkcje);
        //      
        return $TablicaZestawowProduktu;
        //
    }    
    
    // zwraca informacji o cenie za kg/m/litr
    public function ProduktWielkoscPojemnosc( $rozmiar_inny = 0, $cena_inna = 0 ) {
        //
        $pobierzFunkcje = true;
        include('produkt/ProduktWielkoscPojemnosc.php');
        unset($pobierzFunkcje);
        //      
        return $Wynik;
        //
    }         
    
    // zwraca dostepnosc produktu do Microdanych
    public function MicroDataAvailability( $iloscProduktu, $open_graph = false ) {
        //
        //
        $dostepnosc_produktu = $this->info['id_dostepnosci'];

        $DostepnoscGoogle = 'InStock';
        $DostepnoscOpenGraph = 'in stock';

        if ( $dostepnosc_produktu == '99999' ) {
            $dostepnosc_produktu = $this->PokazIdDostepnosciAutomatycznych($iloscProduktu);
        }

        if ( $dostepnosc_produktu == '0' ) {

            $DostepnoscGoogle = 'InStock';
            $DostepnoscOpenGraph = 'in stock';

        } else {

            $zapytanieAvail = "SELECT products_availability_id, googleshopping AS dostepnosc FROM products_availability WHERE products_availability_id = '".$dostepnosc_produktu."' ";

            $sqlAvail = $GLOBALS['db']->open_query($zapytanieAvail);

            if ( (int)$GLOBALS['db']->ile_rekordow($sqlAvail) > 0 ) {

                while ($infoAvail = $sqlAvail->fetch_assoc()) {
                //
                    $DostepnoscGoogleId = $infoAvail['dostepnosc'];
                }

                switch($DostepnoscGoogleId) {
                    case '1':   $DostepnoscGoogle = 'InStock'; $DostepnoscOpenGraph = 'in stock'; break;
                    case '3':   $DostepnoscGoogle = 'OutOfStock'; $DostepnoscOpenGraph = 'out of stock'; break;
                    case '4':   $DostepnoscGoogle = 'PreOrder'; $DostepnoscOpenGraph = 'preorder'; break;
                    default:    $DostepnoscGoogle = 'InStock'; $DostepnoscOpenGraph = 'in stock'; break;
                }

            } else {

                $DostepnoscGoogle = 'InStock';
                $DostepnoscOpenGraph = 'in stock';

            }
            $GLOBALS['db']->close_query($sqlAvail); 
            unset($zapytanieAvail, $infoAvail);
            //
        }

        return array('google' => $DostepnoscGoogle, 'opengraph' => $DostepnoscOpenGraph);
    }

}
?>