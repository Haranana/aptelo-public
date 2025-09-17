<?php

if ( isset($pobierzFunkcje) ) {
  
    if ( empty($tablica_produktu) ) {
      
         $dane_produktu = array( 'cena_netto' => $this->infoSql['products_price'],
                                 'cena_brutto' => $this->infoSql['products_price_tax'],
                                 'dane_serialize' => $this->infoSql['products_set_products'],
                                 'waluta_produktu' => $this->infoSql['products_currencies_id'] );

    } else {
      
         $dane_produktu = array( 'cena_netto' => $tablica_produktu['cena_netto'],
                                 'cena_brutto' => $tablica_produktu['cena_brutto'],
                                 'dane_serialize' => $tablica_produktu['dane_serialize'],
                                 'waluta_produktu' => $tablica_produktu['waluta_produktu'] );
                                 
    }

    $TablicaProduktowZestawu = array();
    //
    $IdProduktowZestawu = unserialize($dane_produktu['dane_serialize']);

    $SumaTaniej = 0;

    // rabat glowny zestawu
    $RabatZestawu = 0;
    if ( RABATY_ZESTAWY == 'tak' ) {
         //
         $CenaRabatyTmp = $this->CenaProduktuPoRabatach( $dane_produktu['cena_netto'], $dane_produktu['cena_brutto'] );
         $RabatZestawu = $CenaRabatyTmp['rabat'];
         unset($CenaRabatyTmp);
         //
    }
    //
    $IloscProduktowZestawu = 0;
    //
    foreach ( $IdProduktowZestawu as $IdTmp => $DaneTmp ) {
        //
        if ( empty($tablica_produktu) ) { 
        
             $ProduktTmp = new Produkt( $IdTmp, 40, 40, '', false, true );
             
        } else {
          
             $ProduktTmp = new Produkt( $IdTmp, 150, 150, '', false, true );
          
        }
        
        if ($ProduktTmp->CzyJestProdukt == true) {
          
            $IloscProduktowZestawu++;

            // sprawdza czy mozna kupowac przy dostepnosci produktu
            $Kupowanie = 'tak';
            //
            if ( $ProduktTmp->info['id_dostepnosci'] > 0 ) {
                //
                // jezeli jest automatyczna dostepnosc
                if ( $ProduktTmp->info['id_dostepnosci'] == '99999' ) {
                     //
                     $Automatyczna = $this->PokazIdDostepnosciAutomatycznych( $ProduktTmp->info['ilosc'] );
                     if ( $Automatyczna != '0' ) {
                          $Kupowanie = $GLOBALS['dostepnosci'][ $Automatyczna ]['kupowanie'];
                     }
                     //
                   } else {
                     //
                     $Kupowanie = 'nie';
                     if ( isset($GLOBALS['dostepnosci'][ $ProduktTmp->info['id_dostepnosci'] ]) ) {
                          $Kupowanie = $GLOBALS['dostepnosci'][ $ProduktTmp->info['id_dostepnosci'] ]['kupowanie'];
                     }
                     //
                }  
                //
            }     
            
            if ( $Kupowanie == 'tak' ) {
                 //
                 $Kupowanie = $ProduktTmp->info['status_kupowania'];
                 //
            }
            //
            // dane podstawowe produktu
            $TablicaProduktowZestawu[$IdTmp] = array('id' => $ProduktTmp->info['id'],
                                                     'nazwa' => $ProduktTmp->info['nazwa'],
                                                     'adres_seo' => $ProduktTmp->info['adres_seo'],
                                                     'link' => $ProduktTmp->info['link'],
                                                     'cena' => $ProduktTmp->info['cena_brutto'],
                                                     'foto' => $ProduktTmp->fotoGlowne['zdjecie_link'],
                                                     'zdjecie' => $ProduktTmp->fotoGlowne['plik_zdjecia'],
                                                     'status_kupowania' => $Kupowanie);
            
            $CenaBruttoPrzedObnizka = $ProduktTmp->infoSql['products_price_tax'];
            
            // jezeli zestaw ma rabat
            if ( $RabatZestawu > 0 ) {
                 $CenaBruttoPrzedObnizka = $ProduktTmp->infoSql['products_price_tax'] * ((100 - (float)$RabatZestawu) / 100);
            }
            //
            
            // obliczanie rabatu dla produktu zestawu
            if ( (float)$DaneTmp['rabat_procent'] > 0 ) {
                 //
                 $ProduktTmp->infoSql['products_price_tax'] = $ProduktTmp->infoSql['products_price_tax'] * ((100 - (float)$DaneTmp['rabat_procent']) / 100);             
                 //
            }
            if ( (float)$DaneTmp['rabat_kwota'] > 0 ) {
                 //
                 $ProduktTmp->infoSql['products_price_tax'] = $ProduktTmp->infoSql['products_price_tax'] - (float)$DaneTmp['rabat_kwota'];
                 //
            }    
            //
            // jezeli zestaw ma rabat
            if ( $RabatZestawu > 0 ) {
                 $ProduktTmp->infoSql['products_price_tax'] = $ProduktTmp->infoSql['products_price_tax'] * ((100 - (float)$RabatZestawu) / 100);
            }
            //
            
            // zaokraglanie przy malych kwotach do 2 miejsc po przecinku
            $ProduktTmp->infoSql['products_price_tax'] = round($ProduktTmp->infoSql['products_price_tax'],2);
            
            // obliczanie ceny netto
            $ProduktTmp->infoSql['products_price'] = round(($ProduktTmp->infoSql['products_price_tax'] / (1 + (Funkcje::StawkaPodatekVat( $ProduktTmp->infoSql['products_tax_class_id'] )/100))), 2);
            
            $DaneTmp['cena_netto'] = $ProduktTmp->infoSql['products_price'];
            $DaneTmp['cena_brutto'] = $ProduktTmp->infoSql['products_price_tax'];                 
            
            //
            $TmpKwoty = $GLOBALS['waluty']->FormatujCene( $DaneTmp['cena_brutto'], $DaneTmp['cena_netto'], 0, $dane_produktu['waluta_produktu'], false );             
            //
            $TablicaProduktowZestawu[$IdTmp]['cena_brutto'] = $DaneTmp['cena_brutto']; // $TmpKwoty['brutto'];
            $TablicaProduktowZestawu[$IdTmp]['cena_netto'] = $DaneTmp['cena_netto']; // $TmpKwoty['netto'];
            //
            // taniej o
            $TmpTaniej = $GLOBALS['waluty']->FormatujCene( $CenaBruttoPrzedObnizka, 0, 0, $dane_produktu['waluta_produktu'], false );
            $TablicaProduktowZestawu[$IdTmp]['taniej'] = ($TmpTaniej['brutto'] - $TmpKwoty['brutto']) * $DaneTmp['rabat_ilosc'];
            //
            unset($TmpKwoty, $TmpTaniej, $CenaRabatZestaw);
            //        
            
            // vat
            $TablicaProduktowZestawu[$IdTmp]['vat'] = $TablicaProduktowZestawu[$IdTmp]['cena_brutto'] - $TablicaProduktowZestawu[$IdTmp]['cena_netto'];
            
            // ilosc w zestawie
            $TablicaProduktowZestawu[$IdTmp]['ilosc'] = $DaneTmp['rabat_ilosc'];
            
            // ilosc produktu w magazynie
            $TablicaProduktowZestawu[$IdTmp]['ilosc_magazyn'] = $ProduktTmp->info['ilosc'];        
            
            // jm produktu
            $TablicaProduktowZestawu[$IdTmp]['jednostka_miary'] = $ProduktTmp->info['jednostka_miary'];  

            // suma taniej
            $SumaTaniej += $TablicaProduktowZestawu[$IdTmp]['taniej'];
            
            //
            unset($ProduktTmp, $CenaBruttoPrzedObnizka);
            //
        }
        //
    }  
    //
    $tablica_koncowa = array();
    //
    if ( count($IdProduktowZestawu) == $IloscProduktowZestawu ) {
         //
         if ( empty($tablica_produktu) ) {
              //
              $this->zestawTaniej = $GLOBALS['waluty']->WyswietlFormatCeny( $SumaTaniej, $_SESSION['domyslnaWaluta']['id'], true, false );
              //
              $this->zestawProdukty = $TablicaProduktowZestawu;
              //
         } else {
              //
              $tablica_koncowa = array('taniej_wartosc' => $SumaTaniej,
                                       'taniej_kwota' => $GLOBALS['waluty']->WyswietlFormatCeny( $SumaTaniej, $_SESSION['domyslnaWaluta']['id'], true, false ), 
                                       'produkty' => $TablicaProduktowZestawu);
              //
         }
         //
    }

}
       
?>