<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

        $AllegroRest = new AllegroRest( array('allegro_user' => $_SESSION['domyslny_uzytkownik_allegro']) );

        // wczytanie naglowka HTML
        include('naglowek.inc.php');
        ?>

        <div id="caly_listing">
        
            <div id="ajax"></div>
            
            <div id="naglowek_cont">Zdefiniowane cenniki dostaw w Allegro</div>     

            <div class="poleForm cmxform" style="margin-bottom:10px">
              <div class="naglowek">Ustawienia konfiguracji połączenia z Allegro</div>

              <div class="pozycja_edytowana">
              
                <?php require_once('allegro_naglowek.php'); ?>
                
              </div>          
            </div>
            
            <?php if ( Funkcje::SprawdzAktywneAllegro() ) { ?>
        
            <div id="PozycjeIkon">
                <div>
                    <a class="dodaj" href="allegro/allegro_cennik_dostaw_dodaj.php">dodaj nową pozycję</a>
                </div>            
            </div>
            
            <div style="clear:both;"></div>

            <?php
            $AllegroRest = new AllegroRest( array('allegro_user' => $_SESSION['domyslny_uzytkownik_allegro']) );

            $DaneWejsciowe = array( 'seller.id' => $AllegroRest->ParametryPolaczenia['UserId'] ); 
            $wynik = $AllegroRest->commandRequest('sale/shipping-rates', $DaneWejsciowe, '' );
            ?>

            <?php if ( isset($wynik->shippingRates) && count($wynik->shippingRates) > 0 ) { ?>

                <div id="wynik_zapytania">

                    <table class="listing_tbl">
                        <tr class="div_naglowek">
                            <td style="text-align:left">ID cennika w Allegro</td> 
                            <td style="text-align:left">Nazwa cennika</td><td class="rg_right"></td>
                        </tr>

                        <?php
                        foreach ( $wynik->shippingRates as $TablicaWysylek ) {
                            echo '<tr class="pozycja_off" id="'.$TablicaWysylek->id.'">';
                            echo '<td style="text-align:left">'.$TablicaWysylek->id.'</td>';
                            echo '<td style="text-align:left">'.$TablicaWysylek->name.'</td>';
                            echo '<td class="rg_right"><a class="TipChmurka" href="allegro/allegro_cennik_dostaw_edytuj.php?id_poz='.$TablicaWysylek->id.'"><b>Edytuj</b><img src="obrazki/edytuj.png" alt="Edytuj"></a></td>';
                            echo '</tr>';
                        }
                        ?>

                    </table>

                </div>

            <?php } else { ?>

                <div id="wynik_zapytania">
                <div  style="padding:10px">Brak zdefiniowanych cenników w Allegro</div>
                </div>

            <?php } ?>

            <div class="cl"></div>
            
            <?php } ?>
            
        </div>
                    
        <?php include('stopka.inc.php'); ?>

    <?php

}
?>