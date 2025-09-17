<?php

class Stronicowanie {

    static public function PokazStrony($SqlIlosc, $LinkDoPrzenoszenia, $IloscNaStronie = 0, $OstatniaStrona = 'nie') {  
        //
        if ( is_int($SqlIlosc) ) {
             $IleProduktow = $SqlIlosc;
          } else {
             $IleProduktow = (int)$GLOBALS['db']->ile_rekordow($SqlIlosc);
        }
        
        // zabezpieczenie zeby nie mozna bylo wyswietlic wiecej niz ilosc na stronie x 3
        if ( $_SESSION['listing_produktow'] > LISTING_PRODUKTOW_NA_STRONIE * 3 ) {
             $_SESSION['listing_produktow'] = LISTING_PRODUKTOW_NA_STRONIE;
        }        
        //

        $Odejmij = 0;
        
        if ($OstatniaStrona == 'tak') {
             $Odejmij = 1;
        }
        
        $IleStron = $IleProduktow / ((($IloscNaStronie > 0) ? $IloscNaStronie : $_SESSION['listing_produktow']) - $Odejmij);
        //
        if ($IleStron != (int)$IleStron) {
            $IleStron = (int)$IleStron + 1;
        }
        //
        if (!isset($_GET['s']) || $_GET['s'] <= 0) {
            $_GET['s'] = 1;
        }        
        // jezeli ktos wpisze z reki strone
        if ((int)$_GET['s'] > $IleStron) {
            //
            // $_GET['s'] = $IleStron;
            //
            $adresUrl = trim(Funkcje::RequestURI(), '/');
            $paramUrl = explode('/', (string)$adresUrl);
            //
            foreach ($paramUrl as $Klucz => $Wartosc) {
                //
                $tabWar = explode('=', (string)$Wartosc);
                if (count($tabWar) > 1) {
                    //
                    if ( $tabWar[0] == 's' ) {
                         Funkcje::PrzekierowanieURL($paramUrl[0]);
                    }
                    //
                }
                unset($tabWar);
                //
            }   
            //
        }
        //
        $LewoPrawo = 2;
        $AktualnaStrona = (int)$_GET['s'];
        //
        // poczatek stron
        if ($AktualnaStrona - $LewoPrawo <= 0) {
            $PoczatekStron = 1;
          } else {
            $PoczatekStron = $AktualnaStrona - $LewoPrawo;
        }
        //
        // koniec stron
        if ($AktualnaStrona + $LewoPrawo > $IleStron) {
            $KoniecStron = $IleStron;
          } else {
            $KoniecStron = $AktualnaStrona + $LewoPrawo;
        }    
        //
        $DoWyniku = '';
        for ($st = $PoczatekStron; $st <= $KoniecStron; $st++) {
            //
            $Css = '';
            // jezeli jest aktualnie wyswietlana strona
            if ($st == $AktualnaStrona) {
                $Css = ' class="Aktywna"';
            }
            $DoWyniku .= '<a' . $Css . ' aria-label="' . $GLOBALS['tlumacz']['LISTING_STRONA'] . ': ' . $st . '" href="' . $LinkDoPrzenoszenia . str_replace('\%', '[proc]', Funkcje::Zwroc_Get(array('s','id','idkat','idproducent','idkatart'), false, '/')) . (($st > 1) ? '/s=' . $st : '') . '">' . $st . '</a>';
        }
        //
        // jezeli pierwsza pozycja jest wieksza od 1
        if ($PoczatekStron > 1) {
            $DoWyniku = '<a href="' . $LinkDoPrzenoszenia . str_replace('\%', '[proc]', Funkcje::Zwroc_Get(array('s','id','idkat','idproducent','idkatart'), false, '/')) . '">1</a> ... ' . $DoWyniku;
        }
        // jezeli ostatnia strona jest mniejsza od maksymalnej ilosci stron
        if ($KoniecStron < $IleStron) {
            $DoWyniku = $DoWyniku . ' ... <a aria-label="' . $GLOBALS['tlumacz']['LISTING_STRONA'] . ': ' . $IleStron . '" href="' . $LinkDoPrzenoszenia . str_replace('\%', '[proc]', Funkcje::Zwroc_Get(array('s','id','idkat','idproducent','idkatart'), false, '/')) . '/s=' . $IleStron . '">' . $IleStron . '</a>';
        }  
        // znaczniki rel prev i next
        $LinkPrev = '';
        $LinkNext = '';
        $SamLinkNext = '';
        
        if ( $IleStron > 1 ) {            
             //
             if ( $AktualnaStrona < $IleStron ) {
                  $LinkNext = '    <link rel="next" href="' . ADRES_URL_SKLEPU . '/' . $LinkDoPrzenoszenia . str_replace('\%', '[proc]', Funkcje::Zwroc_Get(array('s','id','idkat','idproducent','idkatart'), false, '/')) . (($AktualnaStrona + 1 != 1) ? '/s=' . ($AktualnaStrona + 1) : '') . '" />';
                  
                  if ($OstatniaStrona == 'tak') {
                      $SamLinkNext = $LinkDoPrzenoszenia . str_replace('\%', '[proc]', Funkcje::Zwroc_Get(array('s','id','idkat','idproducent','idkatart'), false, '/')) . (($AktualnaStrona + 1 != 1) ? '/s=' . ($AktualnaStrona + 1) : '');
                  }
                  
             }
             if ( $AktualnaStrona > 1 ) {
                  $LinkPrev = '    <link rel="prev" href="' . ADRES_URL_SKLEPU . '/' . $LinkDoPrzenoszenia . str_replace('\%', '[proc]', Funkcje::Zwroc_Get(array('s','id','idkat','idproducent','idkatart'), false, '/')) . (($AktualnaStrona - 1 != 1) ? '/s=' . ($AktualnaStrona - 1) : '') . '" />';
             }             
             //
        }
        //
        return array( str_replace("\'", "'", (string)$DoWyniku), ($AktualnaStrona - 1) * (($IloscNaStronie > 0) ? $IloscNaStronie : $_SESSION['listing_produktow']), $LinkPrev, $LinkNext, $SamLinkNext );
    }
    
}

?>