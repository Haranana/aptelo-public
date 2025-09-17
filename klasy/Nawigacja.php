<?php

class Nawigacja {
  public $_sciezka;

  public function __construct() {
    $this->reset();
  }

  public function reset() {
    $this->_sciezka = array();
  }

  public function dodaj($tytul, $link = '', $unshift = 0) {
      switch ($unshift) {
       case 0:
        $this->_sciezka[] = array('tytul' => $tytul, 'link' => $link);
        break;
       case 1:
        array_unshift($this->_sciezka, array('tytul' => $tytul, 'link' => $link));
        break;
      }      
    }

  public function sciezka($separator = ' - ') {
    $tekst = '';

    for ( $i=0, $n=count($this->_sciezka); $i < $n; $i++ ) {
      if (isset($this->_sciezka[$i]['link']) && $this->_sciezka[$i]['link'] != '' ) {

        if ( $i == '0' ) {
          $tekst .= '<span itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><a href="' . $this->_sciezka[$i]['link'] . '" class="NawigacjaLink" itemprop="item"><span itemprop="name">' . $this->_sciezka[$i]['tytul'] . '</span></a><meta itemprop="position" content="' . ($i + 1) . '" /></span>';
        } else {
          if ( $i < $n-1 ) {
            $tekst .= '<span itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><a href="' . $this->_sciezka[$i]['link'] . '" class="NawigacjaLink" itemprop="item"><span itemprop="name">' . $this->_sciezka[$i]['tytul'] . '</span></a><meta itemprop="position" content="' . ($i + 1) . '" /></span>';
          } else {
            $tekst .= '<span itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem" class="OstatniaNawigacja"><span itemprop="name">' . $this->_sciezka[$i]['tytul'] . '</span><meta itemprop="position" content="' . ($i + 1) . '" /></span>';
          }
        }

      } else {
        $tekst .= '<span class="OstatniaNawigacja"><span itemprop="name">' . $this->_sciezka[$i]['tytul'] . '</span><meta itemprop="position" content="' . ($i + 1) . '" /></span>';
      }

      if (($i+1) < $n) $tekst .= '<span class="Nawigacja">' . $separator . '</span>';

    }

    return $tekst;
  }
}

?>
