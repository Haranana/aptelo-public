<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

if (Sesje::TokenSpr() && OPINIE_STATUS == 'tak') { ?>

    <?php
    $zapytanie = "SELECT * FROM reviews_shop WHERE approved = '1'";
    $sql = $GLOBALS['db']->open_query($zapytanie); 

    $SredniaOcena = 0;
    $SredniaJakosc = 0;
    $SredniCzas = 0;
    $CenyOcena = 0;
    $JakoscOcena = 0;
    
    $SumaGlosow = 0;
    $IluPoleca = 0;
    
    if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) { 

        while ( $info = $sql->fetch_assoc() ) {

            $SredniaJakosc += $info['handling_rating'];
            $SredniCzas += $info['lead_time_rating'];
            $CenyOcena += $info['price_rating'];
            $JakoscOcena += $info['quality_products_rating'];
            
            $SumaGlosow++;
            
            if ( $info['recommending'] == 1 ) {
                 $IluPoleca++;
            }

        }
        
        unset($info);
        
    }
    
    if ( $SumaGlosow > 0 ) {
         $SredniaOcena = number_format(round((5 * (($SredniaJakosc + $SredniCzas + $CenyOcena + $JakoscOcena) / (20 * $SumaGlosow))),1), 1, ',', ' ');
      } else {
         $SredniaOcena = 0;
    }

    $GLOBALS['db']->close_query($sql); 
    unset($zapytanie);
    
    if ( $SumaGlosow > 0 ) {
    
    ?>

    <div class="Podzial">
         <div class="Wiersz">
             <div><strong><?php echo $GLOBALS['tlumacz']['NAGLOWEK_OPINIE']; ?></strong></div>
             <div><span><?php echo $GLOBALS['tlumacz']['ILOSC_OPINII']; ?><b><?php echo $SumaGlosow; ?></b></span></div>
         </div>
    </div>
    
    <div class="SredniaOcenaSklepuRamka">
    
        <div class="Podzial SredniaOcenaSklepu">
            <div class="Wiersz">
                <div><?php echo $GLOBALS['tlumacz']['SREDNIA_OCENA_OPINII']; ?></div> 
                <div><b><?php echo $SredniaOcena; ?></b></div>
            </div>
        </div>
        
    </div>
    
    <ul class="Oceny">
        <?php if ( Wyglad::TypSzablonu() == true ) { ?>
            <li class="Ocena"><?php echo $GLOBALS['tlumacz']['OCENA_JAKOSC_OBSLUGI']; ?><span class="Gwiazdki Gwiazdka_<?php echo ($SumaGlosow > 0 ? round(($SredniaJakosc / $SumaGlosow),0) : '1'); ?>" id="radio_<?php echo uniqid(); ?>" style="--ocena: <?php echo ($SumaGlosow > 0 ? round(($SredniaJakosc / $SumaGlosow),0) : '0'); ?>;"></span></li>
            <li class="Ocena"><?php echo $GLOBALS['tlumacz']['OCENA_CZAS_REALIZACJI']; ?><span class="Gwiazdki Gwiazdka_<?php echo ($SumaGlosow > 0 ? round(($SredniCzas / $SumaGlosow),0) : '1'); ?>" id="radio_<?php echo uniqid(); ?>" style="--ocena: <?php echo ($SumaGlosow > 0 ? round(($SredniCzas / $SumaGlosow),0) : '0'); ?>;"></span></li>
            <li class="Ocena"><?php echo $GLOBALS['tlumacz']['OCENA_CENY']; ?><span class="Gwiazdki Gwiazdka_<?php echo ($SumaGlosow > 0 ? round(($CenyOcena / $SumaGlosow),0) : '1'); ?>" id="radio_<?php echo uniqid(); ?>" style="--ocena: <?php echo ($SumaGlosow > 0 ? round(($CenyOcena / $SumaGlosow),0) : '0'); ?>;"></span></li>
            <li class="Ocena"><?php echo $GLOBALS['tlumacz']['OCENA_PRODUKTOW']; ?><span class="Gwiazdki Gwiazdka_<?php echo ($SumaGlosow > 0 ? round(($JakoscOcena / $SumaGlosow),0) : '1'); ?>" id="radio_<?php echo uniqid(); ?>" style="--ocena: <?php echo ($SumaGlosow > 0 ? round(($JakoscOcena / $SumaGlosow),0) : '0'); ?>;"></span></li>
        <?php } else { ?>
            <li class="Ocena_<?php echo ($SumaGlosow > 0 ? round(($SredniaJakosc / $SumaGlosow),0) : '0'); ?>"><?php echo $GLOBALS['tlumacz']['OCENA_JAKOSC_OBSLUGI']; ?></li>
            <li class="Ocena_<?php echo ($SumaGlosow > 0 ? round(($SredniCzas / $SumaGlosow),0) : '0'); ?>"><?php echo $GLOBALS['tlumacz']['OCENA_CZAS_REALIZACJI']; ?></li>
            <li class="Ocena_<?php echo ($SumaGlosow > 0 ? round(($CenyOcena / $SumaGlosow),0) : '0'); ?>"><?php echo $GLOBALS['tlumacz']['OCENA_CENY']; ?></li>
            <li class="Ocena_<?php echo ($SumaGlosow > 0 ? round(($JakoscOcena / $SumaGlosow),0) : '0'); ?>"><?php echo $GLOBALS['tlumacz']['OCENA_PRODUKTOW']; ?></li>
        <?php } ?>
    </ul>
    
    <div class="IluPoleca">
        <b><?php echo (($SumaGlosow > 0) ? round((($IluPoleca / $SumaGlosow) * 100),0) : 0); ?>%</b>
        <?php echo $GLOBALS['tlumacz']['ILOSC_KLIENTOW_POLECA']; ?>
    </div>
    
    <div class="WszystkieOpinie">
        <a href="opinie-o-sklepie.html"><?php echo $GLOBALS['tlumacz']['ZOBACZ_OPINIE']; ?></a>
    </div>
    
    <?php } else { ?>
    
    <div class="BrakOpiniiZakladka">
    
        <?php echo $GLOBALS['tlumacz']['BLAD_BRAK_OPINII']; ?>
        
    </div>
    
    <?php } ?>
    
    <?php
    unset($SredniaOcena, $SredniaJakosc, $SredniCzas, $CenyOcena, $JakoscOcena, $SumaGlosow, $IluPoleca);
    ?>

<?php } ?>