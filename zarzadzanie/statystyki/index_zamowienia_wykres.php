<?php
if ( isset($prot) ) {
      
    if ($prot->wyswietlStrone && empty($GLOBALS['uprawnieniaZakladki']['zakladkaZamowienia'])) {
    ?>

        <div class="WykresGlowna">
            <canvas id="canvas_zamowienia_wartosc" height="220" width="400"></canvas>
        </div>  

        <div class="WykresGlowna">
            <canvas id="canvas_zamowienia_ilosc" height="220" width="400"></canvas>
        </div>    

        <?php
        include("statystyki/index_zamowienia_wykres_dzienny.php");
        ?>    

    <?php

    } else {

        echo '<div class="ModulyOstrzezenie">Nie posiadasz uprawień do przeglądania tego elementu.</div>';

    }

}
?>