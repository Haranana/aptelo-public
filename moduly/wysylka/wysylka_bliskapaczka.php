<?php

if(!class_exists('wysylka_bliskapaczka')) {
  class wysylka_bliskapaczka {

    public $paramatery;
    public $produktId;
    public $produktWaga;
    public $produktCena;
    public $produktWysylki;
    public $produktGabaryt;
    public $produktKosztWysylki;
    public $wykluczonaDarmowaWysylka;
    public $produktPromocja;
    public $zawszePlatna;
    public $tytul;
    public $objasnienie;
    public $kolejnosc;
    public $klasa;
    public $ikona;
    public $wyswietl;
    public $id;
    public $ilosc_paczek_wg_wagi;
    public $ilosc_paczek_wg_szt;
    public $gabaryt;
    public $stawka_vat;
    public $kod_gtu;
    public $pkwiu;
    public $max_waga;
    public $min_waga;
    public $max_waga_tryb;
    public $max_ilosc_prod;
    public $max_wartosc;
    public $min_wartosc;
    public $darmowa;
    public $darmowa_paczki;
    public $darmowa_waga;
    public $darmowa_promocje;
    public $rodzaj_oplaty;
    public $kraje;
    public $koszty;
    public $platnosci;
    public $grupa;
    public $grupa_wylacz;
    public $waga_wolumetr;
    public $waga_zamowienia;
    public $ilosc_produktow;
    public $wartosc_zamowienia;
    public $wartosc_zamowienia_bez_promocji;
    public $api_key;
    public $koszt;

    // class constructor
    function __construct( $parametry = array(), $kraj = '', $idProduktu = '', $WagaProduktu = '', $CenaProduktu = '', $WysylkiProduktu = '', $GabarytProduktu = '', $KosztWysylkiProduktu = '0', $WykluczonaDarmowaWysylka = 'nie', $ProduktWPromocji = '0' ) {
      global $zamowienie, $Tlumaczenie, $Operatorzy;

        $Tlumaczenie = $GLOBALS['tlumacz'];

        $this->paramatery  = $parametry;

        // czy przesylka ma byc liczona dla produktu czy koszyka
        $this->produktId           = $idProduktu;
        $this->produktWaga         = $WagaProduktu;
        $this->produktCena         = $CenaProduktu;
        $this->produktWysylki      = $WysylkiProduktu;
        $this->produktGabaryt      = $GabarytProduktu;
        $this->produktKosztWysylki = $KosztWysylkiProduktu;
        $this->wykluczonaDarmowaWysylka = $WykluczonaDarmowaWysylka;
        $this->produktPromocja          = $ProduktWPromocji;
        
        $this->zawszePlatna = 'nie';
        if ( isset($this->paramatery['parametry']['WYSYLKA_DARMOWA_WYKLUCZONA']) && $this->paramatery['parametry']['WYSYLKA_DARMOWA_WYKLUCZONA'] == 'tak' ) {
             $this->zawszePlatna = 'tak';
        }        

        $this->tytul                = ( isset($Tlumaczenie['WYSYLKA_'.$this->paramatery['id'].'_TYTUL']) ? $Tlumaczenie['WYSYLKA_'.$this->paramatery['id'].'_TYTUL'] : '' );
        $this->objasnienie          = ( isset($Tlumaczenie['WYSYLKA_'.$this->paramatery['id'].'_OBJASNIENIE']) ? $Tlumaczenie['WYSYLKA_'.$this->paramatery['id'].'_OBJASNIENIE'] : '' );
        $this->kolejnosc            = $this->paramatery['sortowanie'];
        $this->klasa                = $this->paramatery['klasa'];
        $this->wyswietl             = false;
        $this->id                   = $this->paramatery['id'];

        $this->gabaryt          = $this->paramatery['parametry']['WYSYLKA_GABARYT'];
        $this->stawka_vat       = $this->paramatery['parametry']['WYSYLKA_STAWKA_VAT'];
        $this->kod_gtu          = $this->paramatery['parametry']['WYSYLKA_KOD_GTU'];
        $this->pkwiu            = $this->paramatery['parametry']['WYSYLKA_PKWIU'];
        $this->max_waga         = $this->paramatery['parametry']['WYSYLKA_MAKSYMALNA_WAGA'];
        $this->min_waga         = $this->paramatery['parametry']['WYSYLKA_MINIMALNA_WAGA'];
        $this->max_ilosc_prod   = $this->paramatery['parametry']['WYSYLKA_MAKSYMALNA_ILOSC_W_PACZCE'];        
        $this->max_wartosc      = $GLOBALS['waluty']->PokazCeneBezSymbolu((float)$this->paramatery['parametry']['WYSYLKA_MAKSYMALNA_WARTOSC'],'',true);
        $this->min_wartosc      = $GLOBALS['waluty']->PokazCeneBezSymbolu((float)$this->paramatery['parametry']['WYSYLKA_MINIMALNA_WARTOSC'],'',true); 
        $this->darmowa          = $GLOBALS['waluty']->PokazCeneBezSymbolu((float)$this->paramatery['parametry']['WYSYLKA_DARMOWA_WYSYLKA'],'',true);
        $this->darmowa_waga     = $this->paramatery['parametry']['WYSYLKA_DARMOWA_WYSYLKA_WAGA'];
        $this->darmowa_promocje = $this->paramatery['parametry']['WYSYLKA_DARMOWA_PROMOCJE'];
        $this->rodzaj_oplaty    = '2';
        $this->kraje            = $this->paramatery['parametry']['WYSYLKA_KRAJE_DOSTAWY'];
        $this->platnosci        = $this->paramatery['parametry']['WYSYLKA_DOSTEPNE_PLATNOSCI'];
        $this->grupa            = $this->paramatery['parametry']['WYSYLKA_GRUPA_KLIENTOW'];
        $this->grupa_wylacz     = $this->paramatery['parametry']['WYSYLKA_GRUPA_KLIENTOW_WYLACZENIE'];
        $this->ikona            = $this->paramatery['parametry']['WYSYLKA_IKONA'];

        $this->waga_zamowienia    = $this->paramatery['waga_zamowienia'];
        $this->ilosc_produktow    = $this->paramatery['ilosc_produktow'];
        $this->wartosc_zamowienia = $this->paramatery['wartosc_zamowienia'];
        $this->wartosc_zamowienia_bez_promocji = $this->paramatery['wartosc_zamowienia_bez_promocji']; 
        $this->api_key          = $this->paramatery['parametry']['WYSYLKA_KLUCZ_API_BLISKAPACZKA']; 

        $this->koszt            = 9999;
        unset($Tlumaczenie);

        if ( $this->waga_zamowienia == 0 || $this->waga_zamowienia < 1 ) {
            $this->waga_zamowienia = 1;
        }
        $dane = '{
            "parcel":{
                "dimensions":{
                        "length":10,
                        "width":10,
                        "height":10,
                        "weight":'.$this->waga_zamowienia.'
                },
                "insuranceValue":'.$this->wartosc_zamowienia.'
            },
            "deliveryType":"P2P"
        }';

        $url = 'https://api.bliskapaczka.pl/v2/pricing' ;
        //$url = 'https://api.sandbox-bliskapaczka.pl/v2/pricing';
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->api_key
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dane);    

        $content = curl_exec($ch);

        $Operatorzy = false;

        if ( is_string($content) && is_array(json_decode($content, true)) && (json_last_error() == JSON_ERROR_NONE) ) {

            if ( $content === false ) {
                $Operatorzy = false;
            } else {
                $Operatorzy = json_decode($content);
            }
        
        }

        $this->operatorzy        = $content; 

        curl_close($ch);

    }

    function przetwarzanie() {
      global $wynik, $Operatorzy, $koszt_wysylki;

      $wynik = array();
      $koszt_wysylki = 0;

      if ( $this->grupa != '' && $_SESSION['gosc'] == '1' ) {
          return;
      }

      // ustalenie czy przesylka zawiera sie w dopuszczalnej wartości zamowienia
      if ( $this->max_wartosc != '0' && $this->max_wartosc != '' && $this->wartosc_zamowienia > $this->max_wartosc ) {
          return;
      }
      
      // ustalenie czy przesylka zawiera sie w minimalne wartości zamowienia
      if ( $this->min_wartosc != '0' && $this->min_wartosc != '' && $this->wartosc_zamowienia < $this->min_wartosc ) {
          return;
      }         

      // ustalenie czy waga zamowienia przekracza min wage
      if ( $this->waga_zamowienia < (float)$this->min_waga ) {
           return;
      }
      
      // jezeli ilosc produktow jest wieksza niz maksymalna
      if ( $this->ilosc_produktow > $this->max_ilosc_prod ) {
           return;
      }      
      

      // ustalenie czy klient nalezy do grupy dla ktorej dostepna jest wysylka
      if ( $this->grupa != '' ) {

            if ( isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0' ) {

                $tablica_grup = explode(';', (string)$this->grupa);
                if ( !in_array((string)$_SESSION['customers_groups_id'], $tablica_grup) ) {
                    return;
                }
                unset($tablica_grup);

            }
      }
      
      // ustalenie czy klient nalezy do grupy ktora nie jest dostepna dla tej wysylki
      if ( $this->grupa_wylacz != '' ) {

            if ( isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0' ) {

                $tablica_grup = explode(';', (string)$this->grupa_wylacz);
                if ( in_array((string)$_SESSION['customers_groups_id'], $tablica_grup) ) {
                    return;
                }
                unset($tablica_grup);

            }
      }      

      // sprawdzenie czy dostawa jest dostepna dla wszystkich produktow w koszyku
      if ( $this->produktId == '' && $this->produktWaga == '' && $this->produktCena == '' ) {
           if ( isset($_SESSION['koszyk']) ) {
               foreach ( $_SESSION['koszyk'] as $rekord ) {
                // sprawdza czy jest indywidualny koszt wysylki
                if ( isset($rekord['koszt_wysylki']) && $rekord['koszt_wysylki'] > 0 ) {
                    return;
                }
                // sprawdza czy sa ustawione indywidualne metody wysylki
                if ( isset($rekord['wysylki']) && $rekord['wysylki'] != '' ) {
                  $dostepne = explode(';', (string)$rekord['wysylki']);
                  if (!in_array((string)$this->id, $dostepne) ) {
                    return;
                  }
                }
                // sprawdza czy jest gabaryt
                if ( $this->gabaryt == '0' ) {
                    if ( isset($rekord['gabaryt']) && $rekord['gabaryt'] == '1' ) {
                        return;
                    }
                }
              }
           }
      } else {
          // sprawdza czy jest indywidualny koszt wysylki
          if ( $this->produktKosztWysylki > 0 ) {
              return;
          }
          if ( $this->produktWysylki != '' ) {
              $dostepne = explode(';', (string)$this->produktWysylki);
              if (!in_array((string)$this->id, $dostepne) ) {
                return;
              }
          }
          if ( $this->gabaryt == '0' ) {
              if ( $this->produktGabaryt == '1' ) {
                  return;
              }
          }
      }

      // jezeli laczna waga zamowienia przekracza maksymalna wage to wylacza wysylke
      if ( $this->waga_zamowienia > $this->max_waga ) {
        return;
      } 

      if ( isset($_SESSION['rodzajDostawyKoszyk']) && isset($_SESSION['rodzajDostawyKoszyk']['wysylka_bliskapaczka']) ) {
          $this->wyswietl = true;
          $koszt_wysylki = $_SESSION['rodzajDostawyKoszyk']['wysylka_bliskapaczka']['koszt'];
      } else {
          if ( is_array($Operatorzy) && count($Operatorzy) > 0 ) {
              foreach ( $Operatorzy as $Operator ) {
                if ( isset($Operator->price->gross) ) {
                    if ( $Operator->price->gross < $this->koszt ) {
                        $this->koszt = $Operator->price->gross;
                    }
                }
              }
              $this->wyswietl = true;
              $koszt_wysylki = $this->koszt;
          } else {
              $this->wyswietl = false;
              $koszt_wysylki = $this->koszt;
          }
      }

      // sprawdzi czy w koszyku nie ma produktu z wykluczona darmowa wysylka
      $wykluczona_darmowa = false;
      if ( isset($_SESSION['koszyk']) ) {
          foreach ( $_SESSION['koszyk'] as $rekord ) {
              //
              if ( isset($rekord['wykluczona_darmowa_wysylka']) && $rekord['wykluczona_darmowa_wysylka'] == 'tak' ) {
                   $wykluczona_darmowa = true;
              }
              //
          }
      }
      
      if ( $this->produktId != '' ) {
          //
          if ( $this->wykluczonaDarmowaWysylka == 'tak' ) {
               $wykluczona_darmowa = true;
          }
          if ( $this->darmowa_promocje == 'nie' && $this->produktPromocja == '1' ) {
               $wykluczona_darmowa = true;
          }
          //
      }      
      
      if ( $wykluczona_darmowa == true ) {
           $this->darmowa = 0;
      }      

      // jezeli jest darmowa wysylka
      if ( (($this->darmowa != '0' && $this->darmowa != '') || ($this->darmowa_waga != '0' && $this->darmowa_waga != '')) && (( (($this->darmowa_promocje == 'nie') ? $this->wartosc_zamowienia_bez_promocji : $this->wartosc_zamowienia) >= $this->darmowa ) || ( $this->waga_zamowienia < (float)$this->darmowa_waga )) && $wykluczona_darmowa == false ) {
           //
           $brak_darmowej = false;
           if ( (float)$this->darmowa_waga > 0 ) {
                //
                if ( $this->waga_zamowienia > (float)$this->darmowa_waga ) {
                     $brak_darmowej = true;
                } else {
                    $this->darmowa = 0;
                }
                //
           }       
           //
           if ( $brak_darmowej == false ) {
                //
                if ( $this->zawszePlatna == 'nie' || ( $this->zawszePlatna == 'tak' && (($this->darmowa_promocje == 'nie') ? $this->wartosc_zamowienia_bez_promocji : $this->wartosc_zamowienia) >= $this->darmowa ) ) {
                     $koszt_wysylki = 0;
                }
                //
           }
           //
           unset($brak_darmowej);
           //
      }

      if ( $this->wyswietl ) {

        $vat_stawka = '';
        $vat_id = '';
        $wynik = array();
        $vat_tb = explode('|', (string)$this->stawka_vat);
        if ( count($vat_tb) == 2 ) {
            //
            $vat_id = $vat_tb[1];
            $vat_stawka = $vat_tb[0];
            //
          } else {
            //
            $vat_tb = Funkcje::domyslnyPodatekVat();
            $vat_id = $vat_tb['id'];
            $vat_stawka = $vat_tb['stawka'];        
            //
        }
        unset($vat_tb);      
        
        // dzien darmowej dostawy
        if ( DZIEN_DARMOWEJ_DOSTAWY == 'tak' && (( DZIEN_DARMOWEJ_DOSTAWY_KRAJE == 'domyślny' && isset($_SESSION['krajDostawy']) && isset($_SESSION['krajDostawyDomyslny']) && $_SESSION['krajDostawy']['id'] == $_SESSION['krajDostawyDomyslny']['id'] ) || DZIEN_DARMOWEJ_DOSTAWY_KRAJE == 'wszystkie kraje' ) ) {
            $koszt_wysylki = 0;
        }    
        
        if ( isset($_SESSION['netto']) && $_SESSION['netto'] == 'tak' ) {
             //
             // obliczy netto wysylki
             if ( $koszt_wysylki > 0 ) {
                  $koszt_wysylki = $koszt_wysylki / ((100 + $vat_stawka) / 100);
             }
             //
             $vat_id = $_SESSION['vat_zwolniony_id'];
             $vat_stawka = $_SESSION['vat_zwolniony_wartosc'];
             //
        }

        $wynik = array('id' => $this->id,
                       'klasa' => $this->klasa,
                       'text' => $this->tytul,
                       'wartosc' => $koszt_wysylki,
                       'vat_id' => $vat_id,
                       'vat_stawka' => $vat_stawka,     
                       'kod_gtu' => $this->kod_gtu,
                       'dostepne_platnosci' => $this->platnosci,
                       'objasnienie' => $this->objasnienie,
                       'wysylka_free' => $this->darmowa,
                       'free_promocje' => $this->darmowa_promocje,
                       'wykluczona_darmowa' => $this->zawszePlatna,
                       'ikona' => $this->ikona);
                       

      }

      return $wynik;
    }

    public static function potwierdzenie( $koszyk = true ) {
        global $Tlumaczenie, $Operatorzy, $wynik, $koszt_wysylki;

        if ( WLACZENIE_SSL == 'tak' ) {
            $adres = ADRES_URL_SKLEPU_SSL . '/inne/zmiana_lokalizacji_dostawy.php';
          } else {
            $adres = 'inne/zmiana_lokalizacji_dostawy.php';
        }

        $zapytanie_api = "SELECT modul_id, kod, wartosc FROM modules_shipping_params WHERE modul_id = (SELECT id FROM modules_shipping WHERE klasa = 'wysylka_bliskapaczka')";
        $sql_api = $GLOBALS['db']->open_query($zapytanie_api);

        while ($info_api = $sql_api->fetch_assoc()) {
            if ( $info_api['kod'] == 'WYSYLKA_KLUCZ_API' ) {
                $klucz_api = $info_api['wartosc'];
                $modul_id = $info_api['modul_id'];
            }  
            if ( $info_api['kod'] == 'WYSYLKA_KLUCZ_API_BLISKAPACZKA' ) {
                $klucz_api_bliskapaczka = $info_api['wartosc'];
            }  
        }
        $GLOBALS['db']->close_query($sql_api);         
        unset($zapytanie_api, $info_api);   

        $OperatorzyTablica = '';

        if ($Operatorzy !== false) {
            $OperatorzyTablica = '[';
            foreach ( $Operatorzy as $Operator ) {
                if ( isset($Operator->price->gross) ) {
                    $OperatorzyTablica .= "{operator: '".$Operator->operatorName."', price: ".( $koszt_wysylki > 0 ? $Operator->price->gross : 0 ) ."},";
                }
                unset($Operator);
            }
            $OperatorzyTablica = substr((string)$OperatorzyTablica, 0, -1);
            $OperatorzyTablica .= ']';
        }

        $tekst = '';
        $ex = pathinfo($_SERVER['PHP_SELF']);
        
        if ( basename($_SERVER['PHP_SELF'],'.'.$ex['extension']) == 'koszyk' ) {
          
            $tekst = '<script type="text/javascript" src="https://widget.bliskapaczka.pl/v8.1/main.js"></script>';
            $tekst .= '<link rel="stylesheet" href="https://widget.bliskapaczka.pl/v8.1/main.css" />';
        
        }
        
        $tekst .= '<script>
                    var myModal;
                    $(document).ready(function() {
                     $("#WidgetButtonBliskaPaczka").click(function(e) {
                        e.preventDefault();

                        myModal = new jBox("Modal", {
                        constructOnInit: true,
                        responsiveWidth: true,
                        responsiveHeight: true,
                            ajax: {
                                url: "inne/mapy_koszyk.php?tok=' . Sesje::Token() . '",
                                reload: "strict",
                                type: "POST",
                                data: {
                                  koszyk: ' . (($koszyk == true) ? '"tak"' : '"nie"') . ',
                                  adres: "' . $adres . '",
                                  modul: "bliskapaczka",
                                  klucz: "' . $klucz_api . '",
                                  operatorzy: "' . base64_encode((string)$OperatorzyTablica) . '",
                                  modul_id: "' . $modul_id . '",
                                  plik: "' . basename($_SERVER['PHP_SELF'],'.'.$ex['extension']) . '",
                                },
                                setContent: false,
                                success: function (response) {
                                  this.setContent(response);
                                },
                                error: function () {
                                  this.setContent("Nie mozna zaladowac mapy");
                                }
                              },
                              onCloseComplete: function(){
                                myModal.destroy();
                              }
                            });
                            myModal.open();
                          
                      });
                    });

                   </script>'; 

        if ( basename($_SERVER['PHP_SELF'],'.'.$ex['extension']) != 'koszyk' ) {         
             $tekst .= '<div class="ListaWyboru"><div id="ListaOpcjiWysylki">';
        } else {
             $tekst .= '<div class="WyborPunktuKoszyk"><div>';
        }
        
        if ( basename($_SERVER['PHP_SELF'],'.'.$ex['extension']) == 'koszyk' ) {
             $tekst .= '<div class="WybranyPunktMapyKoszyk" id="WybranyPunktBliskaPaczka" style="border:0;' . ((!isset($_SESSION['rodzajDostawyKoszyk']['wysylka_bliskapaczka']['opis'])) ? 'display:none' : '') . '">'.(isset($_SESSION['rodzajDostawyKoszyk']['wysylka_bliskapaczka']['opis']) ? $_SESSION['rodzajDostawyKoszyk']['wysylka_bliskapaczka']['opis'] : '').'</div>';
        }
        
        if ( basename($_SERVER['PHP_SELF'],'.'.$ex['extension']) == 'koszyk' ) {
            $tekst .= '<span class="przycisk" id="WidgetButtonBliskaPaczka" style="font-style:normal">'.$Tlumaczenie['POCZTAPOLSKA_WYBIERZ_PUNKT'].'</span><br />';
        }

        if ( basename($_SERVER['PHP_SELF'],'.'.$ex['extension']) != 'koszyk' ) {
            $tekst .= '<input type="text" size="40" class="WybranyPunktMapyBliskaPaczka" id="punktBliskaPaczka" value="'.(isset($_SESSION['rodzajDostawy']['opis']) ? $_SESSION['rodzajDostawy']['opis'] : '').'" name="lokalizacjaRuch" readonly="readonly" id="wybor_paczki" />';
            $tekst .= '<input type="hidden" id="ShippingDestinationCode" value="" name="ShippingDestinationCode" />';
            $tekst .= '<input type="hidden" value="" name="OpisPunktuOdbioru" id="OpisPunktuOdbioru" />';
        }
        
        $tekst .= '</div></div>';

        $tekst .= '<style>
                       #BPWidget .bp-filters .bp-filters-content .bp-filters-wrapper .bp-filter .filter-checkbox-content img { height: 100% !important; }
                      #BPWidget .bp-footer .bp-footer-content .bp-footer-logo { width:88px !important; }
                      #BPWidget .bp-pos-info .bp-pos-info-content .bp-pos-info-element .bp-point-desc .bp-point-desc-address-row .bp-point-desc-address-row-brand img { max-width: 88px !important; }
                      .pac-container { display:none !important; }
                      #WynikMapBliskapaczka { height:80vh; width:80vw; }
                      #punktBliskaPaczka { width:100%; padding:10px; margin:10px 0px 0px 0px; font-size:110%; font-weight:bold;
                        -webkit-background-clip:content-box; -moz-background-clip:content-box; background-clip:content-box;  
                        -webkit-box-sizing:border-box; -moz-box-sizing:border-box; box-sizing:border-box;  
                      }                        
                   </style>';

        unset($OperatorzyTablica, $wynik);

        return $tekst;         

    }
    
  }
}
?>