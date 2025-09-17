<?php
$liczba_linii = 0;

if ( isset($dane_produktow->offers->offer) ) {
     $liczba_linii = sizeof($dane_produktow->offers->offer);
}
?>