<?php

if(!class_exists('wysylka_standard')) {
  class wysylka_standard {

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
    public $OsobnePaczki;
    public $laczna_ilosc_paczek_wg_wagi;
    public $laczna_ilosc_paczek_wg_szt;
    public $laczna_waga_zamowienia;

    // class constructor
    function __construct( $parametry = array(), $kraj = '', $idProduktu = '', $WagaProduktu = '', $CenaProduktu = '', $WysylkiProduktu = '', $GabarytProduktu = '', $KosztWysylkiProduktu = '0', $WykluczonaDarmowaWysylka = 'nie', $ProduktWPromocji = '0' ) {
      global $zamowienie;

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
        $this->ilosc_paczek_wg_wagi = 1;
        $this->ilosc_paczek_wg_szt  = 1;

        $this->gabaryt          = $this->paramatery['parametry']['WYSYLKA_GABARYT'];
        $this->stawka_vat       = $this->paramatery['parametry']['WYSYLKA_STAWKA_VAT'];
        $this->kod_gtu          = $this->paramatery['parametry']['WYSYLKA_KOD_GTU'];
        $this->pkwiu            = $this->paramatery['parametry']['WYSYLKA_PKWIU'];
        $this->max_waga         = $this->paramatery['parametry']['WYSYLKA_MAKSYMALNA_WAGA'];
        $this->min_waga         = $this->paramatery['parametry']['WYSYLKA_MINIMALNA_WAGA'];
        $this->max_waga_tryb    = $this->paramatery['parametry']['WYSYLKA_MAKSYMALNA_WAGA_TRYB'];
        $this->max_ilosc_prod   = $this->paramatery['parametry']['WYSYLKA_MAKSYMALNA_ILOSC_W_PACZCE'];
        $this->max_wartosc      = $GLOBALS['waluty']->PokazCeneBezSymbolu((float)$this->paramatery['parametry']['WYSYLKA_MAKSYMALNA_WARTOSC'],'',true);        
        $this->min_wartosc      = $GLOBALS['waluty']->PokazCeneBezSymbolu((float)$this->paramatery['parametry']['WYSYLKA_MINIMALNA_WARTOSC'],'',true);        
        $this->darmowa          = $GLOBALS['waluty']->PokazCeneBezSymbolu((float)$this->paramatery['parametry']['WYSYLKA_DARMOWA_WYSYLKA'],'',true);
        $this->darmowa_paczki   = $this->paramatery['parametry']['WYSYLKA_DARMOWA_WYSYLKA_ILE_PACZEK'];
        $this->darmowa_waga     = $this->paramatery['parametry']['WYSYLKA_DARMOWA_WYSYLKA_WAGA'];
        $this->darmowa_promocje = $this->paramatery['parametry']['WYSYLKA_DARMOWA_PROMOCJE'];
        $this->rodzaj_oplaty    = $this->paramatery['parametry']['WYSYLKA_RODZAJ_OPLATY'];
        $this->kraje            = $this->paramatery['parametry']['WYSYLKA_KRAJE_DOSTAWY'];
        $this->koszty           = $this->paramatery['parametry']['WYSYLKA_KOSZT_WYSYLKI'];
        $this->platnosci        = $this->paramatery['parametry']['WYSYLKA_DOSTEPNE_PLATNOSCI'];
        $this->grupa            = $this->paramatery['parametry']['WYSYLKA_GRUPA_KLIENTOW'];
        $this->grupa_wylacz     = $this->paramatery['parametry']['WYSYLKA_GRUPA_KLIENTOW_WYLACZENIE'];
        $this->ikona            = $this->paramatery['parametry']['WYSYLKA_IKONA'];
        $this->waga_wolumetr    = $this->paramatery['parametry']['WYSYLKA_WAGA_WOLUMETRYCZNA'];
        
        $this->waga_zamowienia    = $this->paramatery['waga_zamowienia'];
        $this->ilosc_produktow    = $this->paramatery['ilosc_produktow'];
        $this->wartosc_zamowienia = $this->paramatery['wartosc_zamowienia'];        
        $this->wartosc_zamowienia_bez_promocji = $this->paramatery['wartosc_zamowienia_bez_promocji'];

        $this->OsobnePaczki     = false;
        $this->laczna_ilosc_paczek_wg_wagi = 0;
        $this->laczna_ilosc_paczek_wg_szt  = 0;
        $this->laczna_waga_zamowienia    = $this->paramatery['waga_zamowienia'];

        unset($Tlumaczenie);

        //Zrobione i sprawdzone opcje
        // - maksymalna waga przesylki ( obejmuje produkty w koszyku, ktore nie maja pojedynczej paczki )
        // - maksymalna ilość produktów w paczce ( obejmuje produkty w koszyku, ktore nie maja pojedynczej paczki )
        // - darmowa wysylka od kwoty - dla calosci koszyka
        // - darmowa wysylka do wagi - dla calosci koszyka
        // - darmowa w zalaznosci od ilosci paczek - dla calosci koszyka
    }

    function przetwarzanie() {

      $wynik = array();
      $koszt_wysylki = 0;
      $KosztOsobnychPaczek = 0;
      
      $waga_wolumetryczna = 0;

      // waga wolumetryczna
      if ( $this->produktId != '' ) {
          //
          if ( (int)$this->waga_wolumetr > 0 ) {
                //
                $Produkt = new Produkt($this->produktId);
                //
                if ( (int)$Produkt->info['waga_szerokosc'] > 0 && (int)$Produkt->info['waga_wysokosc'] > 0 && (int)$Produkt->info['waga_dlugosc'] > 0 ) {
                     //
                     $waga_wolumetryczna += (((int)$Produkt->info['waga_szerokosc'] * (int)$Produkt->info['waga_wysokosc'] * (int)$Produkt->info['waga_dlugosc']) / (int)$this->waga_wolumetr);
                     //
                } else {
                     //
                     $waga_wolumetryczna += (float)$Produkt->info['waga'];
                     //
                }
                //
                unset($Produkt);
                //
          }
          //            
      } else {
          //
          if ( (int)$this->waga_wolumetr > 0 ) {
                //
                if ( isset($_SESSION['koszyk']) ) {
                     //
                     foreach ( $_SESSION['koszyk'] as $rekord ) {
                         //
                         if ( isset($rekord['waga_szerokosc']) && isset($rekord['waga_wysokosc']) && isset($rekord['waga_dlugosc']) ) {
                              //
                              if ( (int)$rekord['waga_szerokosc'] > 0 && (int)$rekord['waga_wysokosc'] > 0 && (int)$rekord['waga_dlugosc'] > 0 ) {
                                   //
                                   $waga_wolumetryczna += (((int)$rekord['waga_szerokosc'] * (int)$rekord['waga_wysokosc'] * (int)$rekord['waga_dlugosc']) / (int)$this->waga_wolumetr) * (float)$rekord['ilosc'];
                                   //
                              } else {
                                   //
                                   $waga_wolumetryczna += (float)$rekord['waga'] * (float)$rekord['ilosc'];
                                   //
                              }
                              //
                         }
                         //
                     }
                     //
                }
                //
          }          
          //
      }
      
      if ( $waga_wolumetryczna > 0 ) {
           //
           $this->waga_zamowienia = $waga_wolumetryczna;
           //
      }      

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

      $tablica_kosztow = preg_split("/[:;]/" , (string)$this->koszty);

      // podzial na osobne paczki - jezeli produkt ma taka opcje
      if ( $this->produktId == '' && $this->produktWaga == '' && $this->produktCena == '' ) {
        
           $KosztWysylkiJednegoProduktu = 0;
           $IloscPozycji = 0;

           if ( isset($_SESSION['koszyk']) ) {

               $IloscPozycji = count($_SESSION['koszyk']);
                //
                foreach ( $_SESSION['koszyk'] as $rekord ) {
                   //
                   if ( isset($rekord['osobna_paczka']) && (int)$rekord['osobna_paczka'] == 1 ) {
                        //
                        $this->OsobnePaczki = true;
                        // waga zamowienia bez produktow pojedynczych
                        $this->waga_zamowienia -= ($rekord['waga'] * (float)$rekord['ilosc']);
                        $this->wartosc_zamowienia -= ($rekord['cena_brutto'] * (float)$rekord['ilosc']);

                        $this->ilosc_produktow = $this->ilosc_produktow - $rekord['ilosc'];

                        // koszt wysylki jednego produktu
                        $KosztWysylkiJednegoProduktu = $this->CenaPrzesylki($this->rodzaj_oplaty, $tablica_kosztow, 'produkt', ($rekord['waga'] * (float)$rekord['ilosc']), ($rekord['cena_brutto'] * (float)$rekord['ilosc']) );

                        // laczny koszt wysylki pojedynczych produktow
                        if ( $koszt_wysylki !== false ) {
                            $KosztOsobnychPaczek = $KosztOsobnychPaczek + ( $KosztWysylkiJednegoProduktu * ceil((float)$rekord['ilosc'] / (int)$rekord['osobna_paczka_ilosc']) );

                            $this->laczna_ilosc_paczek_wg_szt = $this->laczna_ilosc_paczek_wg_szt + ceil((float)$rekord['ilosc'] / (int)$rekord['osobna_paczka_ilosc']);
                            $this->laczna_ilosc_paczek_wg_wagi = $this->laczna_ilosc_paczek_wg_wagi + ceil((float)$rekord['ilosc'] / (int)$rekord['osobna_paczka_ilosc']);
                        }

                        $IloscPozycji--;
                        unset($KosztWysylkiJednegoProduktu);
                   }
                   //
                }
                //
           }

           $this->OsobnePaczki = false;
      }

      // jezeli ilosc produktow jest wieksza niz maksymalna
      if ( $this->ilosc_produktow > $this->max_ilosc_prod ) {
           $this->ilosc_paczek_wg_szt = ceil($this->ilosc_produktow/$this->max_ilosc_prod);
      }

      if ( $this->produktId != '' ) {
        $koszt_wysylki = $this->CenaPrzesylki($this->rodzaj_oplaty, $tablica_kosztow, 'produkt', $this->waga_zamowienia, $this->produktCena );
      } else {
        if ( $this->ilosc_produktow > 0 ) {
           $koszt_wysylki = $this->CenaPrzesylki($this->rodzaj_oplaty, $tablica_kosztow, 'zamowienie', $this->waga_zamowienia, $this->wartosc_zamowienia );
        }
      }

      if ( $koszt_wysylki === false ) {
          return;
      }

      // sprawdza czy jest wiecej paczek niz 1
      if ( $this->ilosc_paczek_wg_wagi > 1 || $this->ilosc_paczek_wg_szt > 1 ) {
           //
           if ( $this->ilosc_paczek_wg_wagi > $this->ilosc_paczek_wg_szt ) {
                $koszt_wysylki = $this->ilosc_paczek_wg_wagi * $koszt_wysylki;
            } else {
                $koszt_wysylki = $this->ilosc_paczek_wg_szt * $koszt_wysylki;
           }
           //
      }

      if ( $KosztOsobnychPaczek > 0 && $IloscPozycji > 0 ) {
        $koszt_wysylki = $koszt_wysylki + (float)$KosztOsobnychPaczek;
      }
      if ( $KosztOsobnychPaczek > 0 && $IloscPozycji == 0 ) {
        $koszt_wysylki = (float)$KosztOsobnychPaczek;
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
           $this->darmowa_waga = 0;
      }

      if ( $this->ilosc_produktow > 0 ) {
        $this->laczna_ilosc_paczek_wg_szt += $this->ilosc_paczek_wg_szt;
        $this->laczna_ilosc_paczek_wg_wagi += $this->ilosc_paczek_wg_wagi;
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
                    if ( ( $this->laczna_ilosc_paczek_wg_wagi == 1 && $this->laczna_ilosc_paczek_wg_szt == 1 && $this->darmowa_paczki == 'nie' ) || $this->darmowa_paczki == 'tak' ) {
                        $koszt_wysylki = 0;
                    }
                }
                //
           }
           //
           unset($brak_darmowej);
           //

      }
      
      if ( $this->wyswietl ) {
      
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

    function potwierdzenie() {
        return;
    }
    
    function przeliczPaczki() {

        // jezeli laczna waga zamowienia przekracza maksymalna wage to robi wieksza ilosc paczek
        if ( $this->waga_zamowienia > $this->max_waga ) {
            if ( $this->max_waga_tryb == 'paczki' ) {
              //
              $this->ilosc_paczek_wg_wagi = ceil($this->waga_zamowienia/$this->max_waga);
              return true;
              //
            } else {
              //
              if ( $this->OsobnePaczki == true ) {
                $this->ilosc_paczek_wg_wagi = ceil($this->waga_zamowienia/$this->max_waga);
                return true;
              } else {
                return false;
              }
              //
            }
        }  
        
        return true;

    }

    function CenaPrzesylki( $RodzajOplaty, $tablica_kosztow, $SposobLiczenia, $Waga, $WartoscPozycji ) {

      switch ($RodzajOplaty) {

          // jezeli jest stala oplata
          case '1':
            $koszt_wysylki = $tablica_kosztow[count($tablica_kosztow)-1];
            
            // jezeli laczna waga zamowienia przekracza maksymalna wage to robi wieksza ilosc paczek
            $dostepnosc = $this->przeliczPaczki();

            if ( $dostepnosc == false ) {
                return false;
            }            

            $this->wyswietl = true;
            break;

          // jezeli oplata jest wg wagi zamowienia
          case '2':
            $dostepnosc = false;

            // jezeli laczna waga zamowienia przekracza maksymalna wage to robi wieksza ilosc paczek
            $waga_zamowienia = $this->waga_zamowienia;
            if ( $SposobLiczenia == 'zamowienie' ) {
                if ( $this->waga_zamowienia > $this->max_waga ) {
                    if ( $this->max_waga_tryb == 'paczki' ) {
                      $this->ilosc_paczek_wg_wagi = ceil($this->waga_zamowienia/$this->max_waga);
                      $waga_zamowienia = $this->max_waga;
                    } else {
                      if ( $this->OsobnePaczki == true ) {
                        $this->ilosc_paczek_wg_wagi = ceil($this->waga_zamowienia/$this->max_waga);
                        $waga_zamowienia = $this->max_waga;
                      } else {
                        return false;
                      }
                    }
                } else {
                    $waga_zamowienia = $this->waga_zamowienia;
                }
            } else {
                $waga_zamowienia = $Waga;
                if ( $waga_zamowienia > $this->max_waga ) {
                    if ( $this->max_waga_tryb == 'paczki' ) {
                      $this->ilosc_paczek_wg_wagi = ceil($this->waga_zamowienia/$this->max_waga);
                      $waga_zamowienia = $this->max_waga;
                    }
                }
            }

            for ($i = 0, $c = count($tablica_kosztow); $i < $c; $i+=2) {
              if ( $waga_zamowienia <= $tablica_kosztow[$i] ) {
                $koszt_wysylki = $tablica_kosztow[$i+1];
                $dostepnosc = true;
                break;
              }
            }

            if ( $dostepnosc == false ) {
                return false;
            }

            $this->wyswietl = true;
            break;

          // jezeli oplata jest wg wartosci zamowienia
          case '3':
            $dostepnosc = false;
            for ($i = 0, $c = count($tablica_kosztow); $i < $c; $i+=2) {
              if ( $WartoscPozycji <= $GLOBALS['waluty']->PokazCeneBezSymbolu((float)$tablica_kosztow[$i],'',true) ) {
                $koszt_wysylki = $tablica_kosztow[$i+1];
                $dostepnosc = true;
                break;
              }
            }
            
            if ( $dostepnosc == false ) {
                return false;
            }

            // jezeli laczna waga zamowienia przekracza maksymalna wage to robi wieksza ilosc paczek
            $dostepnosc = $this->przeliczPaczki();        
            
            if ( $dostepnosc == false ) {
                return false;
            }            

            $this->wyswietl = true;
            break;

          // jezeli oplata jest wg ilosci produktow w zamowieniu
          case '4':
            $dostepnosc = false;

            $ilosc_produktow = $this->ilosc_produktow;
            if ( $this->OsobnePaczki == true ) {
                $ilosc_produktow = 1;
            }

            for ($i = 0, $c = count($tablica_kosztow); $i < $c; $i+=2) {
              if ( $ilosc_produktow <= $tablica_kosztow[$i] ) {
                $koszt_wysylki = $tablica_kosztow[$i+1];
                $dostepnosc = true;
                break;
              }
            }
            
            if ( $dostepnosc == false ) {
                return false;
            }

            // jezeli laczna waga zamowienia przekracza maksymalna wage to robi wieksza ilosc paczek
            $dostepnosc = $this->przeliczPaczki();
            
            if ( $dostepnosc == false ) {
                return false;
            }                 
            
            $this->wyswietl = true;
            break;

      }
      
      return $koszt_wysylki;
    }

  }
}
?>