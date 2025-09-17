<?php

if(!class_exists('platnosc_comfino')) {
  class platnosc_comfino {

    public $paramatery;
    public $klasa;
    public $tytul;
    public $objasnienie;
    public $kolejnosc;
    public $ikona;
    public $wyswietl;
    public $id;
    public $wysylka_id;
    public $koszty;
    public $koszty_minimum;
    public $wartosc_od;
    public $wartosc_do;
    public $darmowa;
    public $tekst_info;
    public $txtguzik;
    public $punkty;
    public $domyslnyProdukt;
    public $api_key;
    public $api_url;

    // class constructor
    function __construct( $parametry = array() ) {
      global $zamowienie, $Tlumaczenie;
      
        $Tlumaczenie          = $GLOBALS['tlumacz'];
        $this->paramatery     = $parametry;

        $this->klasa          = $this->paramatery['klasa'];
        $this->tytul          = $Tlumaczenie['PLATNOSC_'.$this->paramatery['id'].'_TYTUL'];
        $this->objasnienie    = ( isset($Tlumaczenie['PLATNOSC_'.$this->paramatery['id'].'_OBJASNIENIE']) ? $Tlumaczenie['PLATNOSC_'.$this->paramatery['id'].'_OBJASNIENIE'] : '' );
        $this->kolejnosc      = $this->paramatery['sortowanie'];
        $this->wyswietl       = false;
        $this->id             = $this->paramatery['id'];
        $this->wysylka_id     = $this->paramatery['wysylka_id'];

        $this->koszty         = $this->paramatery['parametry']['PLATNOSC_KOSZT'];
        $this->koszty_minimum = $GLOBALS['waluty']->PokazCeneBezSymbolu($this->paramatery['parametry']['PLATNOSC_KOSZT_MINIMUM'],'',true);

        $this->wartosc_od      = $GLOBALS['waluty']->PokazCeneBezSymbolu($this->paramatery['parametry']['PLATNOSC_WARTOSC_ZAMOWIENIA_MIN'],'',true);
        $this->wartosc_do      = $GLOBALS['waluty']->PokazCeneBezSymbolu($this->paramatery['parametry']['PLATNOSC_WARTOSC_ZAMOWIENIA_MAX'],'',true);
        $this->darmowa         = $GLOBALS['waluty']->PokazCeneBezSymbolu($this->paramatery['parametry']['PLATNOSC_DARMOWA_PLATNOSC'],'',true);

        $this->tekst_info      = $Tlumaczenie['PLATNOSC_'.$this->paramatery['id'].'_TEKST'];

        $this->txtguzik        = $Tlumaczenie['PRZYCISK_POWROT_DO_SKLEPU'];
        
        $this->ikona           = $this->paramatery['parametry']['PLATNOSC_IKONA'];
        $this->punkty          = $this->paramatery['parametry']['STATUS_PUNKTY'];
        
        $this->domyslnyProdukt = $this->paramatery['parametry']['PLATNOSC_COMFINO_PLATNOSC_TYP'];

        $this->api_key        = $this->paramatery['parametry']['PLATNOSC_COMFINO_KLUCZ'];

        if ( $this->paramatery['parametry']['PLATNOSC_COMFINO_SANDBOX'] == '1' ) {
            $this->api_url = 'https://api-ecommerce.craty.pl/v1/';
            //$this->api_url = 'https://api-ecommerce.ecraty.pl/v1/';
        } else {
            $this->api_url = 'https://api-ecommerce.comfino.pl/v1/';
        }
        
        unset($Tlumaczenie);

    }

    function przetwarzanie( $id_zamowienia = 0 ) {

      $wynik = array();
      
      $wartosc_zamowienia = 0;
      $wartosc_produktow = 0;
      $znizki_koszyka = 0;

      if ( $id_zamowienia == 0 ) {
        
          $this->wyswietl = false;
        
          // ustalenie wartosci zamowienia
          foreach ( $_SESSION['koszyk'] as $rekord ) {
            $wartosc_zamowienia += $rekord['cena_brutto']*$rekord['ilosc'];
            $wartosc_produktow += $rekord['cena_brutto']*$rekord['ilosc'];
          }
          if ( is_numeric($_SESSION['rodzajDostawy']['wysylka_koszt']) ) {
               $wartosc_zamowienia += $GLOBALS['waluty']->PokazCeneBezSymbolu($_SESSION['rodzajDostawy']['wysylka_koszt'], '' ,true);
          }
          
          $podsumowanieTmp = new Podsumowanie();
          $wartosc_zamowienia_koncowa = 0;
          
          foreach ( $podsumowanieTmp->podsumowanie as $podsum ) {
              //
              if ( isset($podsum['klasa']) && $podsum['klasa'] == 'ot_total' ) {
                   $wartosc_zamowienia_koncowa = (float)$podsum['wartosc'];
              }
              if ( isset($podsum['prefix']) && $podsum['prefix'] == '0' ) {
                   $znizki_koszyka = $znizki_koszyka + (float)$podsum['wartosc'];
              }
              //
          }

          // sprawdzenie czy dana platnosc jest dostepna dla wybranego rodzaju dostawy
          $tablica_wysylek = explode(';', (string)$_SESSION['rodzajDostawy']['dostepne_platnosci']);

          if ( in_array( $this->id, $tablica_wysylek ) ) {

            // sprawdzenie czy wartosc zamowienia miesci sie w dopuszczalnym zakresie dla danej platnosci
            if ( Funkcje::czyWartoscJestwZakresie($wartosc_zamowienia_koncowa, $this->wartosc_do, $this->wartosc_od) && $wartosc_zamowienia_koncowa > 0 ) {
              $this->wyswietl = true;
            }

          }
          if ( $this->wyswietl ) {
            // jezeli koszt platnosci jest okreslony wzorem, to oblicza wartosc
            if ( !is_numeric($this->koszty) && $this->koszty != '' ) {
              $koszt_platnosci = str_replace( 'x', (($wartosc_zamowienia / $_SESSION['domyslnaWaluta']['przelicznik']) / ((100 + $_SESSION['domyslnaWaluta']['marza']) / 100)), (string)$this->koszty);
              $koszt_platnosci = Funkcje::obliczWzor($koszt_platnosci);
              if ( $GLOBALS['waluty']->PokazCeneBezSymbolu($koszt_platnosci,'',true) < $this->koszty_minimum ) {
                $koszt_platnosci = $this->koszty_minimum;
              }
            } else {
              $koszt_platnosci = $this->koszty;
            }
            
          }

          // darmowy koszt
          if ( $wartosc_produktow >= $this->darmowa && (float)$this->darmowa > 0 ) {
               $koszt_platnosci = 0;
          }  
          
      } else {
          
          $koszt_platnosci = 0;
          $this->wyswietl = true;
            
      }
      
      if ( isset($_SESSION['netto']) && $_SESSION['netto'] == 'tak' && isset($_SESSION['rodzajDostawy']['wysylka_id']) && isset($koszt_platnosci) ) {
           //
           $zapytanie_vat_wysylki = "SELECT wartosc FROM modules_shipping_params WHERE kod = 'WYSYLKA_STAWKA_VAT' and modul_id = '" . $_SESSION['rodzajDostawy']['wysylka_id'] . "'";
           $sql_wysylki = $GLOBALS['db']->open_query($zapytanie_vat_wysylki);
           $vat_wysylka = $sql_wysylki->fetch_assoc();  

           $wartosc_vat = explode('|', (string)$vat_wysylka['wartosc']);

           // obliczy netto platnosci
           if ( isset($wartosc_vat[0]) && (float)$wartosc_vat[0] > 0 ) {
                $koszt_platnosci = $koszt_platnosci / ((100 + (float)$wartosc_vat[0]) / 100);
           }
           //
           $GLOBALS['db']->close_query($sql_wysylki);         
           unset($zapytanie_vat_wysylki, $vat_wysylka);           
           //
      }      
 
      if ( $this->wyswietl == true && $wartosc_zamowienia > 0 ) {


        $loanAmount = round(($wartosc_zamowienia - $znizki_koszyka) * 100);
        $KanalyPlatnosciJS = '';
        $KanalyPlatnosci = '';
        $domyslnyKanal = $this->domyslnyProdukt;
        $DostepneProdukty = array();

        if ( !isset($_SESSION['KanalyPlatnosciComfino']) ) {
            $Wynik = $this->getFinancialProducts( (int)$loanAmount );
            $_SESSION['KanalyPlatnosciComfino'] = $Wynik;
            $DostepneProdukty = json_decode( $Wynik, TRUE );
        } else {
            $DostepneProdukty = json_decode( $_SESSION['KanalyPlatnosciComfino'], TRUE );
        }

        if ( isset($DostepneProdukty['message']) && $DostepneProdukty['message'] != '' ) {
            return;
        }

        if ( isset($DostepneProdukty) && count($DostepneProdukty) > 0 ) {

            $TablicaKanalow = array();
            foreach ( $DostepneProdukty as $wiersz ) {
                if ( isset($wiersz['type']) ) {
                $TablicaKanalow[] = $wiersz['type'];
                }
            }

            if ( isset($_SESSION['rodzajPlatnosci']) && !isset($_SESSION['rodzajPlatnosci']['platnosc_kanal']) ) {
                $_SESSION['rodzajPlatnosci']['platnosc_kanal'] = $this->domyslnyProdukt;
            }
            if ( isset($_SESSION['rodzajPlatnosci']['platnosc_kanal']) && $_SESSION['rodzajPlatnosci']['platnosc_kanal'] != '' ) {
                $domyslnyKanal = $_SESSION['rodzajPlatnosci']['platnosc_kanal'];
            }

            if ( count($TablicaKanalow) > 0 && !in_array($domyslnyKanal, $TablicaKanalow) ) {
                $domyslnyKanal = $TablicaKanalow['0'];
            }

            $KanalyPlatnosciJS = '<script>
                $(document).ready(function() {

                    $("body").on("change", "input:radio[name=\'PlatnosciComfino\']", function() {
                        PreloadWlacz();
                        $.ajax({
                          type: "POST",
                          data: "data=" + $(this).val(),
                          url: "inne/zmiana_kanalu_platnosci.php?tok='.(string)Sesje::Token().'",
                          dataType : "json",
                          success: function(json){
                              PreloadWylaczSzybko();
                          }
                        });
                    });

                    if( $("#rodzaj_platnosci_'.$this->id.'").is(":checked") ){
                         $(".PlatnosciComfino").show();
                    } else {
                         $(".PlatnosciComfino").hide();
                    }
                    $("input:radio[name=rodzaj_platnosci]").change(function() {
                        if (this.value == "'.$this->id.'") {
                            $(".PlatnosciComfino").slideDown();
                        } else {
                            $(".PlatnosciComfino").slideUp();
                        }
                    });
                });

            </script>';
            if ( !isset($DostepneProdukty['message']) ) {

                foreach ( $DostepneProdukty as $row ) {

                    $KanalyPlatnosci .= '<div class="PlatnosciComfino ListaTbl"><div class="ListaRadio" style="padding-left:35px;">';
                    $KanalyPlatnosci .= '<label data-id="' . $this->id . '_' . $row['type'] . '" for="' . $this->id . '_' . $row['type'] . '_' . '" title="">';
                    $KanalyPlatnosci .= '     <span class="RodzajNazwa">'.$row['name'].'</span>';
                    $KanalyPlatnosci .= '     <input data-id="' . $this->id . '_' . $row['type'] . '" type="radio" id="' . $this->id . '_' . $row['type'] . '_' . '" name="PlatnosciComfino" value="' . $row['type'] . '" ' .( $row['type'] == $domyslnyKanal ? 'checked="checked"' : '' ). ' />';

                    $KanalyPlatnosci .= '     <span class="InfoTip InfoTipBezGrafiki rodzaj_platnosci" id="InfoTip_rodzaj_platnosci_' . $row['type'].'" data-tip-id="rodzaj_platnosci_' . $row['type'].'">';
                    $KanalyPlatnosci .= '         <span id="tip_rodzaj_platnosci_' . $row['type'].'" style="display:none;">'.$row['description'].'</span>';
                    $KanalyPlatnosci .= '     </span><span class="ObrazekLogo"><svg width="60px" height="30px" viewBox="0 0 60 30">'.$row['icon'].'</svg></span>';

                    $KanalyPlatnosci .= '     <span class="radio" id="radio_' . $row['type'] . '_' . $this->paramatery['klasa'] . '"></span>';
                    $KanalyPlatnosci .= '</label>';
                    $KanalyPlatnosci .= '</div></div>';

                }

            }

        }

        $wynik = array('id' => $this->id,
                         'klasa' => $this->klasa,
                         'text' => $this->tytul,
                         'wartosc' => $koszt_platnosci,
                         'objasnienie' => $this->objasnienie,
                         'klasa' => $this->klasa,
                         'ikona' => $this->ikona,
                         'punkty' => $this->punkty,
                         'kanaly_platnosci_tekst' => $KanalyPlatnosciJS . $KanalyPlatnosci,
                         'kanal_platnosci' => $domyslnyKanal
        );
  
      }
      return $wynik;
      
    }

    function potwierdzenie() {

        $tekst = '';

        $tekst .= '
                  <div id="PlatnoscText">'.$this->tekst_info.'</div>
                  <div><textarea name="platnosc_info" id="platnoscInfo" style="display:none;" >'.$this->tekst_info.'</textarea></div>';


        if ( isset($_SESSION['rodzajPlatnosci']['opis']) ) {
            unset($_SESSION['rodzajPlatnosci']['opis']);
        }
        $_SESSION['rodzajPlatnosci']['opis'] = $this->tekst_info;

        return $tekst;
    }

    function podsumowanie( $id_zamowienia = 0 ) {

        $tekst = '';

        if ( $id_zamowienia == 0 ) {          
            $zamowienie = new Zamowienie((int)$_SESSION['zamowienie_id']);
          } else {
            $zamowienie = new Zamowienie($id_zamowienia);
        }

            $zamowienie->info['wartosc_zamowienia_val'] = (float)$zamowienie->info['wartosc_zamowienia_val'];

            if ( $zamowienie->info['waluta'] == 'PLN' ) {

                $kwota = number_format($zamowienie->info['wartosc_zamowienia_val'] * 100, 0, "", "");

            } else {

                $przelicznikWaluty = $GLOBALS['waluty']->waluty[$zamowienie->info['waluta']]['przelicznik'];
                $kwota = number_format(($zamowienie->info['wartosc_zamowienia_val'] / $przelicznikWaluty) * 100, 0, "", "");

            }

            $adres_klienta  = Funkcje::PrzeksztalcAdres($zamowienie->klient['ulica']);

            if ( isset($adres_klienta['dom']) && $adres_klienta['dom'] != '' ) {
                $adres_klienta_local  = Funkcje::PrzeksztalcAdresDomu($adres_klienta['dom']);
            }

            $parameters['logged'] = true;
            if (isset($_SESSION['gosc']) && $_SESSION['gosc'] == '0' && $id_zamowienia == 0) {
                $parameters['logged'] = false;
            }

            $comfinoType = "null";

            if ( isset($_SESSION["rodzajPlatnosci"]['platnosc_kanal']) ) {
                $comfinoType = $_SESSION["rodzajPlatnosci"]['platnosc_kanal'];
            }

            $comfinoTerm = "0";

            $returnUrl             = ( WLACZENIE_SSL == 'tak' ? ADRES_URL_SKLEPU_SSL : ADRES_URL_SKLEPU ) . '/platnosc_koniec.php?typ=comfino&zamowienie_id=' . (($id_zamowienia == 0) ? (int)$_SESSION['zamowienie_id'] : $id_zamowienia);
            $notifyUrl             = ( WLACZENIE_SSL == 'tak' ? ADRES_URL_SKLEPU_SSL : ADRES_URL_SKLEPU ) . '/moduly/platnosc/raporty/comfino/raport.php';

            $parameters['rodzaj_platnosci'] = 'comfino';

            $parameters['comfinoType']      = $comfinoType;
            $parameters['comfinoTerm']      = $comfinoTerm;

            $parameters['orderId']         = (($id_zamowienia == 0) ? (int)$_SESSION['zamowienie_id'] : $id_zamowienia); 

            $parameters['amount']           = intval($kwota);
            $parameters["deliveryCost"]     = 0;
            $kosztDostawy = 0;

            if ( isset( $_SESSION['podsumowanieZamowienia']['ot_shipping']["wartosc"] ) ) {
              $kosztDostawy = $kosztDostawy + $_SESSION['podsumowanieZamowienia']['ot_shipping']["wartosc"];
            }

            $parameters["deliveryCost"] = intval( $kosztDostawy * 100 );

            
            $PodzielImieNazwisko = explode(' ', preg_replace('/\s+/', ' ', $zamowienie->klient['nazwa']));
            
            $parameters['firstName']       = (($id_zamowienia == 0) ? trim($_SESSION['adresDostawy']['imie']) : trim($PodzielImieNazwisko[0]));
            $parameters['lastName']        = (($id_zamowienia == 0) ? trim($_SESSION['adresDostawy']['nazwisko']) : trim($PodzielImieNazwisko[count($PodzielImieNazwisko)-1]));
            
            unset($PodzielImieNazwisko);
            
            $parameters['street']           = trim($adres_klienta['ulica']);
            $parameters['buildingNumber']   = trim($adres_klienta_local['dom']);
            $parameters['apartmentNumber']  = trim($adres_klienta_local['mieszkanie']);
            $parameters['city']             = trim($zamowienie->platnik['miasto']);
            $parameters['postalCode']       = trim($zamowienie->platnik['kod_pocztowy']);
            $parameters['countryCode']      = Funkcje::kodISOKrajuDostawy( (($id_zamowienia == 0) ? $_SESSION['adresDostawy']['panstwo'] : $zamowienie->platnik['kraj']) );
            $parameters['email']            = trim($zamowienie->klient['adres_email']);
            $parameters['phoneNumber']      = trim($zamowienie->klient['telefon']);
            $parameters['ip']        = $_SERVER["REMOTE_ADDR"];

            $data = array(
                "notifyUrl" => $notifyUrl,
                "returnUrl" => $returnUrl,
                "orderId" => (string)(($id_zamowienia == 0) ? (int)$_SESSION['zamowienie_id'] : $id_zamowienia),
                "draft" => false,
                "loanParameters" => array(
                    "amount" => (int)$kwota,
                    "term" => (int)$parameters['comfinoTerm'],
                    "type" => $parameters['comfinoType']
                ),
                "cart" => array(
                    "totalAmount" => (int)$kwota,
                    "deliveryCost" => $parameters["deliveryCost"])
                );

            $data["cart"]["products"] = array();
            foreach ( $_SESSION["koszyk"] as $produkt ) {
                $produkty = array(
                            "name" => $produkt["nazwa"],
                            "quantity" => round($produkt["ilosc"],0),
                            "price" => intval(number_format($produkt["cena_brutto"], 2, ".", "") * 100),
                            "photoUrl" => ADRES_URL_SKLEPU . "/". KATALOG_ZDJEC . "/" . $produkt["zdjecie"],
                            "ean" => (string)$produkt["ean"],
                            "externalId" => (string)$produkt["id"],
                            "category" => null
                            );
                array_push($data["cart"]["products"], $produkty);
            }

            if ( isset( $_SESSION['podsumowanieZamowienia']['ot_payment']["wartosc"] ) ) {
                $platnosc = array(
                            "name" => 'Dopłata rodzaj płatności',
                            "quantity" => 1,
                            "price" => intval(number_format($_SESSION['podsumowanieZamowienia']['ot_payment']["wartosc"], 2, ".", "") * 100),
                            "category" => 'ADDITIONAL_FEE'
                            );
                array_push($data["cart"]["products"], $platnosc);

            }

            if ( isset( $_SESSION['podsumowanieZamowienia']['ot_discount_coupon']["wartosc"] ) ) {
                $rabat = array(
                         "name" => ''.$_SESSION['podsumowanieZamowienia']['ot_discount_coupon']["text"].'',
                         "quantity" => 1,
                         "price" => intval(number_format((0 - $_SESSION['podsumowanieZamowienia']['ot_discount_coupon']["wartosc"]), 2, ".", "") * 100),
                         "category" => 'DISCOUNT'
                         );
                array_push($data["cart"]["products"], $rabat);
                unset($rabat);
            }
            if ( isset( $_SESSION['podsumowanieZamowienia']['ot_redemptions']["wartosc"] ) ) {
                $rabat = array(
                         "name" => ''.$_SESSION['podsumowanieZamowienia']['ot_redemptions']["text"].'',
                         "quantity" => 1,
                         "price" => intval(number_format((0 - $_SESSION['podsumowanieZamowienia']['ot_redemptions']["wartosc"]), 2, ".", "") * 100),
                         "category" => 'DISCOUNT'
                         );
                array_push($data["cart"]["products"], $rabat);
                unset($rabat);
            }
            if ( isset( $_SESSION['podsumowanieZamowienia']['ot_loyalty_discount']["wartosc"] ) ) {
                $rabat = array(
                         "name" => ''.$_SESSION['podsumowanieZamowienia']['ot_loyalty_discount']["text"].'',
                         "quantity" => 1,
                         "price" => intval(number_format((0 - $_SESSION['podsumowanieZamowienia']['ot_loyalty_discount']["wartosc"]), 2, ".", "") * 100),
                         "category" => 'DISCOUNT'
                         );
                array_push($data["cart"]["products"], $rabat);
                unset($rabat);
            }
            if ( isset( $_SESSION['podsumowanieZamowienia']['ot_shopping_discount']["wartosc"] ) ) {
                $rabat = array(
                         "name" => ''.$_SESSION['podsumowanieZamowienia']['ot_shopping_discount']["text"].'',
                         "quantity" => 1,
                         "price" => intval(number_format((0 - $_SESSION['podsumowanieZamowienia']['ot_shopping_discount']["wartosc"]), 2, ".", "") * 100),
                         "category" => 'DISCOUNT'
                         );
                array_push($data["cart"]["products"], $rabat);
                unset($rabat);
            }

            $data["customer"] = array(
                        "firstName" => $parameters['firstName'],
                        "lastName" => $parameters['lastName'],
                        "taxId" =>null,
                        "email" => $parameters['email'],
                        "phoneNumber" => $parameters['phoneNumber'],
                        "ip" => $parameters['ip'],
                        "regular" => false,
                        "logged" =>$parameters['logged'],
                        "address" => array(
                           "street" =>$parameters['street'],
                           "buildingNumber" => $parameters['buildingNumber'],
                           "apartmentNumber" => $parameters['apartmentNumber'],
                           "postalCode" => $parameters['postalCode'],
                           "city" => $parameters['city'],
                           "countryCode" => $parameters['countryCode']
                        )
            );
            $data["seller"] = array(
                "taxId" => ""
            );

            $comfinioResp = $this->addOrderComfinio(json_encode($data));

            if ( isset($comfinioResp['errors']) ) {
                $tekst .= '<div style="text-align:center;padding:5px;">';
                foreach ( $comfinioResp['errors'] as $key => $value ) {
                    $tekst .= $key . ' : ' . $value . '<br />';
                }
                $tekst .= '</div>';
                return $tekst;
            }

            if ( isset($comfinioResp['status']) && ( isset($comfinioResp['status']) && $comfinioResp['status'] == 'CREATED' ) ) {

                $linkToComfinio = "";
                if ( isset( $comfinioResp["applicationUrl"] ) ) {
                    $linkToComfinio = $comfinioResp["applicationUrl"];
                }

                $parameters['linkToComfinio'] = $linkToComfinio;

                $parametry                      = serialize($parameters);

                $tekst .= '<div style="text-align:center;padding:5px;">';
                $tekst .= '   <a href="'. $linkToComfinio .'" class="przyciskZaplac" type="submit" id="submitButton" style="display:inline-block;">{__TLUMACZ:PRZEJDZ_DO_PLATNOSCI}</a><br /><br />';

                if (isset($_SESSION['gosc']) && $_SESSION['gosc'] == '0' && $id_zamowienia == 0) {
                    $tekst .= '   {__TLUMACZ:ZAPLAC_W_HISTORII_ZAMOWIENIA}';
                }
                $tekst .= '</div>';

                $pola = array(
                        array('payment_method_array',$parametry),
                );

                $GLOBALS['db']->update_query('orders' , $pola, "orders_id = '" . (($id_zamowienia == 0) ? (int)$_SESSION['zamowienie_id'] : $id_zamowienia) . "'");
                unset($pola);
            }



        return $tekst;
    }


    function addOrderComfinio( $jsonNody = "" ) {

      $url = $this->api_url . "orders";

      $headers = array(
        "Content-Type: application/json",
        "Api-Key: " . $this->api_key,
      );

      $ch = curl_init();

      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonNody);
      curl_setopt($ch, CURLOPT_TIMEOUT, 5);

      $Wynik = curl_exec($ch);

      curl_close($ch);

      return json_decode( $Wynik, TRUE );
    }

    function getFinancialProducts($loanAmount = 0) {
 
      $url = $this->api_url . "financial-products?loanAmount=" . $loanAmount;

      $headers = array(
        "Content-Type: application/json",
        "Api-Key: " . $this->api_key,
      );

      $ch = curl_init();

      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_TIMEOUT, 5);

      $Wynik = curl_exec($ch);

      curl_close($ch);

      return $Wynik;

    }


  }
}
?>