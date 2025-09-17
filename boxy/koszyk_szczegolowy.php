<?php
// /* dodatkowe ustawienia konfiguracyjne */
//
// {{BOX_KOSZYK_SZCZEGOLOWY_ROZMIAR_IMG;Rozmiar zdjęcia produktu w pixelach;40;40,50,70,90,100}}
//

if ( defined('BOX_KOSZYK_SZCZEGOLOWY_ROZMIAR_IMG') ) {
   $RozmiarImg = (int)BOX_KOSZYK_SZCZEGOLOWY_ROZMIAR_IMG;
 } else {
   $RozmiarImg = 40;
}

echo '<div class="BoxKoszykSzczegoly">';

if ( $GLOBALS['koszykKlienta']->KoszykIloscProduktow() > 0 ) {
    //
    echo '<ul>';
    //
    foreach ($_SESSION['koszyk'] AS $TablicaZawartosci) {
        //
        $Produkt = new Produkt( Funkcje::SamoIdProduktuBezCech( $TablicaZawartosci['id'] ), $RozmiarImg, $RozmiarImg );
        //
        // sprawdza czy produkt nie zostal wylaczony - jezeli tak usunie go z koszyka
        if ( !$Produkt->CzyJestProdukt ) {
             $GLOBALS['koszykKlienta']->PrzeliczKoszyk();
             Funkcje::PrzekierowanieURL($_SERVER['REQUEST_URI']);
        }
        //         
        echo '<li>';
            //
            echo '<p class="Img" style="width:' . $RozmiarImg . 'px"><a href="' . $Produkt->info['adres_seo'] . '">' . Funkcje::pokazObrazek($TablicaZawartosci['zdjecie'], $Produkt->info['nazwa'], $RozmiarImg, $RozmiarImg, array(), ' class="Zdjecie"') . '</a></p>';
            //
            $CenaProduktowBrutto = $TablicaZawartosci['ilosc'] * $TablicaZawartosci['cena_brutto'];
            $CenaProduktowNetto = $TablicaZawartosci['ilosc'] * $TablicaZawartosci['cena_netto'];
            //
            // okresla czy ilosc jest ulamkowa zeby pozniej odpowiednio sformatowac wynik
            $Przecinek = 2;
            // jezeli sa wartosci calkowite to dla pewnosci zrobi int
            if ( $Produkt->info['jednostka_miary_typ'] == '1' ) {
                $Przecinek = 0;
            }                           
            //
            echo '<p class="PrdDane" style="width:calc(100% - ' . ($RozmiarImg + 20) . 'px)">' . number_format( $TablicaZawartosci['ilosc'], $Przecinek, '.', '' )  . ' x ' . $Produkt->info['link'] . '<br />' . $GLOBALS['waluty']->PokazCene($CenaProduktowBrutto, $CenaProduktowNetto, 0, $_SESSION['domyslnaWaluta']['id'], CENY_BRUTTO_NETTO, false) . '</p>';
            //
            unset($Przecinek, $CenaProduktowBrutto, $CenaProduktowNetto);
            //
        echo '</li>';
        //
        unset($Produkt);
        //
    }
    //
    echo '</ul>';
    //
    $ZawartoscKoszyka = $GLOBALS['koszykKlienta']->ZawartoscKoszyka();
    //
    echo '<div class="Podsumowanie">';
    
        echo '<div>{__TLUMACZ:KOSZYK_DO_ZAPLATY}</div>';
        echo '<div>' . $GLOBALS['waluty']->PokazCene($ZawartoscKoszyka['brutto'], $ZawartoscKoszyka['netto'], 0, $_SESSION['domyslnaWaluta']['id'], CENY_BRUTTO_NETTO, false) . '</div>';  
        
    echo '</div>';
    //
    unset($ZawartoscKoszyka);
    //
    echo '<div class="PrzyciskKoszyk">';
    
        echo '<a class="przycisk" href="' . Seo::link_SEO( 'koszyk.php', '', 'inna' ) . '">{__TLUMACZ:PRZYCISK_PRZEJDZ_DO_KOSZYKA}</a>';
    
    echo '</div>';
    //
} else {
    //
    echo '<span class="PustyKoszyk">{__TLUMACZ:KOSZYK_JEST_PUSTY}</span>';
    //
}

echo '</div>';
//
unset($RozmiarImg);

?>