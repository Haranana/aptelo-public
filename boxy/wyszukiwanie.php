<?php

//
echo '<div class="BoxWyszukiwania">';

echo '<form onsubmit="return sprSzukaj(this,\'InBoxSzukaj\')" action="szukaj.html" method="post" class="cmxform" id="WyszukiwanieBox">';

echo '<p class="PoleFrazy">';

    echo '<input type="text" name="szukaj" id="InBoxSzukaj" value="{__TLUMACZ:WPISZ_SZUKANA_FRAZE}" />';
    echo '<input type="hidden" name="postget" value="tak" />';
    
echo '</p>';

echo '<div>';

    echo '<input type="submit" id="submitSzukaj" class="przyciskWylaczony" value="{__TLUMACZ:PRZYCISK_SZUKAJ}" />';

echo '</div>';

echo '</form>';
 
echo '</div>';

echo '<a class="SzukZaawansowane" href="wyszukiwanie-zaawansowane.html">{__TLUMACZ:WYSZUKIWANIE_ZAAWANSOWANE}</a>';

//
    
?>