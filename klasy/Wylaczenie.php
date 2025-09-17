<?php

class Wylaczenie {

    public static function WylaczSklep() {

        $WylaczSklep = true;
        // jezeli jest mozliwe dzialanie dla okreslonego ip
        if ( INFO_WYLACZ_SKLEP_IP != '' ) {
              //
              $WylaczSklep = true;
              //
              $DozwoloneIp = explode(',', (string)INFO_WYLACZ_SKLEP_IP);
              if ( in_array($_SERVER['REMOTE_ADDR'], $DozwoloneIp) ) {
                   $WylaczSklep = false;
              }
              unset($DozwoloneIp);
              //
        }
        //
        if ( $WylaczSklep == true ) {
             // domyslne meta tagi
             $Meta = MetaTagi::ZwrocMetaTagi();
             // obsluga pliku wylaczenia
             //
             if (file_exists('szablony/'.DOMYSLNY_SZABLON.'/tresc/wylaczony_sklep.tp')) {
                 //
                 $tpl = new Szablony('szablony/'.DOMYSLNY_SZABLON.'/tresc/wylaczony_sklep.tp');
                 //
             } else {
                 //
                 $tpl = new Szablony('szablony/__tresc/wylaczony_sklep.tp');
                 //
             }

             $IloscSekund = '';

             if ( INFO_WYLACZ_SKLEP_DATA != '' ) {
                $IloscSekund = (FunkcjeWlasnePHP::my_strtotime(INFO_WYLACZ_SKLEP_DATA) - time());
             }

             $tpl->dodaj('__META_TYTUL', $Meta['tytul']);
             $tpl->dodaj('__META_SLOWA_KLUCZOWE', $Meta['slowa']);
             $tpl->dodaj('__META_OPIS', $Meta['opis']);
             $tpl->dodaj('__TEKST_WYLACZENIA', nl2br(INFO_WYLACZ_SKLEP_INFO));
             $tpl->dodaj('__DATA_WLACZENIA', $IloscSekund);
             echo $tpl->uruchom();
             unset($Meta);
             //
             exit;
        }
        
        unset($WylaczSklep);
            
    }

}
?>