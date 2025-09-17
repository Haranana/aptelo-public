<?php

$TablicaKategoriiArtykulow = Aktualnosci::TablicaKategorieAktualnosci();

if (count($TablicaKategoriiArtykulow) > 0) {
    //
    echo '<ul class="Lista BezLinii">';
    //
    foreach ( $TablicaKategoriiArtykulow as $Kategoria ) {
        //
        $AktywnaKategoria = '';
        //
        if ( $Kategoria['parent'] == '0' ) {
             //
             if ( isset($_GET['idkatart']) && $Kategoria['id'] == (int)$_GET['idkatart'] ) {
                  //
                  $AktywnaKategoria = 'style="font-weight:bold" class="Aktywna"';
                  //
             }
             //
             echo '<li><div><a ' . $AktywnaKategoria . ' href="' . $Kategoria['seo'] . '">' . $Kategoria['nazwa'] . '</a></div>';
             //
             // podkategorie jezeli sa
             $TablicaPodkategorii = array();
             //
             foreach ( $TablicaKategoriiArtykulow as $PodKategoria ) {
                  //                  
                  if ( $PodKategoria['parent'] == $Kategoria['id'] ) {
                       //
                       $AktywnaPodKategoria = '';
                       //
                       if ( isset($_GET['idkatart']) && $PodKategoria['id'] == (int)$_GET['idkatart'] ) {
                            //
                            $AktywnaPodKategoria = 'style="font-weight:bold" class="Aktywna"';
                            //
                       }
                       //
                       $TablicaPodkategorii[] = '<li><div><a ' . $AktywnaPodKategoria . ' href="' . $PodKategoria['seo'] . '">' . $PodKategoria['nazwa'] . '</a></div></li>';
                       //
                       unset($AktywnaPodKategoria);
                       //
                  }
                  //
             }
             //
             if ( count($TablicaPodkategorii) > 0 ) {
                  //
                  echo '<ul>' . implode('', (array)$TablicaPodkategorii) . '</ul>';
                  //
             }
             //
             unset($TablicaPodkategorii);
             //
             echo '</li>';
             //
        }
        //
        unset($AktywnaKategoria);
        //
    }
    //
    echo '</ul>';
    //
}

unset($TablicaKategoriiArtykulow);

?>