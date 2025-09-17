<?php
$TablicaProducenci = Producenci::TablicaProducenci();

$IloscCalkowita = 0;
$Logotypy = '';

foreach ($TablicaProducenci as $IdProducenta => $TablicaWartosci) {
    //
    if ( !empty($TablicaWartosci['Foto']) ) {

        //
        // ************************ wyglad kategorii - poczatek **************************
        //
        $Logotypy .= '<li>';
        $Logotypy .= '<a href="' . Seo::link_SEO( $TablicaWartosci['Nazwa'], $IdProducenta, 'producent' ) . '">' . Funkcje::pokazObrazek($TablicaWartosci['Foto'], $TablicaWartosci['Nazwa'], 70, 70, array(), 'class="Reload"', 'maly', true, true, false) . '</a>';
        $Logotypy .= '</li>';
        //
        // ************************ wyglad kategorii - koniec **************************
        //
        $IloscCalkowita++;
        //
    }
  
}

if ($IloscCalkowita > 0 && $SzerokoscSrodek > 0) { 

    echo '<div id="ProducenciAnimacjaStrzalkaLewa"></div>';
    
    echo '<div id="ProducenciAnimacjaStrzalkaPrawa"></div>';

    echo '<div id="ProducenciAnimacja">';
    
    echo '<div id="ProducenciAnimacjaSrodek"><ul>';

    echo $Logotypy;
    
    echo '</ul></div>';
    
    echo '</div>';
    
    echo Wyglad::PrzegladarkaJavaScript( "$.ProducenciAnimacje(" . ($IloscCalkowita + 1) . ",70);" );  
    
}

unset($TablicaProducenci, $IloscCalkowita, $Logotypy);
?>