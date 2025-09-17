<?php

if ( isset($pobierzFunkcje) ) {

    if ( $this->infoSql['products_set'] == 0 ) {

        $this->producent = array('id'        => $this->info['id_producenta'],
                                 'nazwa'     => $this->info['nazwa_producenta'],
                                 'link'      => '<a href="' . Seo::link_SEO( $this->info['nazwa_producenta'], $this->info['id_producenta'], 'producent' ) . '">' . $this->info['nazwa_producenta'] . '</a>',
                                 'foto'      => Funkcje::pokazObrazek($this->info['foto_producenta'], $this->info['nazwa_producenta'], $szerokoscImg, $wysokoscImg, array(), '', 'maly', true, false, false),
                                 'foto_link' => '<a href="' . Seo::link_SEO( $this->info['nazwa_producenta'], $this->info['id_producenta'], 'producent' ) . '">' . Funkcje::pokazObrazek($this->info['foto_producenta'], $this->info['nazwa_producenta'], $szerokoscImg, $wysokoscImg, array(), ' style="min-width:' . $szerokoscImg . 'px;min-height:' . $wysokoscImg . 'px"', 'maly', true, false, false) . '</a>');
    } else {
        $this->producent = array('id'        => '',
                                 'nazwa'     => '',
                                 'link'      => '',
                                 'foto'      => '',
                                 'foto_link' => '');
    }

}
       
?>