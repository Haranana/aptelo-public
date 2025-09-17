<?php
$liczba_linii = 0;

if ( isset($dane_produktow->group) ) {
     $liczba_linii = sizeof($dane_produktow->group->o);
}

if ( isset($dane_produktow->o) ) {
     $liczba_linii = sizeof($dane_produktow->o);
}
?>