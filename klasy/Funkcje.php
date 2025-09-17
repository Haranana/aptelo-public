<?php

class Funkcje {

  public static function czyNiePuste($wartosc) {
      if (is_array($wartosc)) {
        if (count($wartosc) > 0) {
          return true;
        } else {
          return false;
        }
      } else {
        if ( (is_string($wartosc) || is_int($wartosc)) && ($wartosc != '') && ($wartosc != 'NULL') && (strlen(trim((string)$wartosc)) > 0) && ($wartosc!='0000-00-00 00:00:00') && ($wartosc!='0000-00-00') && ($wartosc!='0.00')  && ($wartosc!='0.000')) {
          return true;
        } else {
          return false;
        }
      }
  }

  // Zapisanie i wyswietlanie zapytan do bazy danych - tylko dla celow programistycznych
  public static function pokazZapytania() {
    $time_start = explode(' ', (string)PAGE_PARSE_START_TIME);
    $time_end = explode(' ', microtime());
    $parse_time = number_format((($time_end[1] + $time_end[0]) - ($time_start[1] + $time_start[0])), 3, '.', '');
    $ciag = '<div style="text-align: center;" class="tekst10">Czas przetwarzania strony: <b>' . $parse_time . ' s</b><br />Ilość zapytań: <b>' . $GLOBALS['zapytaniaIlosc'] . ' </b></div>';
    if (WYSWIETL_ZAPYTANIA) {
      $ciag .= '<b>Wykonane zapytania do bazy:</b> ';
      $ciag .= Funkcje::drukujTablice($GLOBALS['zapytaniaTresc'], false, true);
      $ciag .= '</div>';
    }
    return $ciag;
  }

  
  // funkcja generujaca i wyswietlajaca miniaturki zdjec
  public static function pokazObrazek( $plik_zdjecia, $alt = '', $szerokosc = '', $wysokosc = '', $ikony = array(), $parametr = '', $wielkosc = 'maly', $skaluj = true, $ladowanie = false, $znakWodny = false ) {
    
    // jezeli jest wylaczona obsluga preloadera obrazkow
    if ( PRELOAD_OBRAZKOW == 'nie' ) {
         $ladowanie = false;
    }
    
    if ( TEKST_COPYRIGHT_POKAZ == 'tak' || OBRAZ_COPYRIGHT_POKAZ == 'tak' ) {
        $znakWodny = true;
    } else {
        $znakWodny = false;
    }

    $katalog_zdjec      = KATALOG_ZDJEC;
    $katalog_miniaturek = 'mini';
    $prefix_miniaturek  = 'px_';

    if ( $znakWodny ) {
      $katalog_miniaturek = 'watermark';
      $prefix_miniaturek  = 'wpx_';
    }

    // Sprawdza czy przekazana zmienna z plikiem nie jest pusta
    if ( ($plik_zdjecia == '') || ($plik_zdjecia == 'NULL') || (strlen(trim((string)$plik_zdjecia)) == 0) || pathinfo($plik_zdjecia, PATHINFO_EXTENSION) == 'swf' ) {
         //
         return '';
         //
    }

    // Sprawdza czy przekazana zmienna z plikiem zawiera adres URL
    $czy_jest_url = strpos((string)$plik_zdjecia, 'http');
    if ($czy_jest_url !== false) {
        //
        $adres_zdjecia =  preg_replace("/((http|https|ftp):\/\/)?([^\/]+)(.*)/si", "$4", (string)$plik_zdjecia);
        $plik_zdjecia = str_replace($katalog_zdjec, '', (string)$adres_zdjecia);
        //
    }

    $znaczki = array("%5B", "%5D", "%20");
    $nawiasy = array("[", "]", " ");
    $plik_zdjecia = str_replace($znaczki, $nawiasy, (string)$plik_zdjecia);

    $sciezka_bezwgledna_do_pliku = KATALOG_SKLEPU . $katalog_zdjec . '/' . $plik_zdjecia;

    // Sprawdza czy istnieje na serwerze plik przekazany do funkcji
    if ( is_file($sciezka_bezwgledna_do_pliku) && filesize($sciezka_bezwgledna_do_pliku) > 0 ) {
        //
        $plik_zdjecia = $plik_zdjecia;
        //
    } else {
        //
        if ( POKAZ_DOMYSLNY_OBRAZEK == 'tak' ) {
            //
            if ( is_file(KATALOG_SKLEPU . $katalog_zdjec . '/domyslny.webp') && filesize(KATALOG_SKLEPU . $katalog_zdjec . '/domyslny.webp') > 0 ) {
                $plik_zdjecia_domyslny = $szerokosc . $prefix_miniaturek . "domyslny.webp";
            } else {
                return;
            }
            //
        } else {
            //
            return '';
            //
        }
        //
    }

    //$sciezka_bezwgledna_do_pliku = (($katalog_zdjec != '') ? KATALOG_SKLEPU : substr((string)KATALOG_SKLEPU,0,-1)) . $katalog_zdjec . '/' . $plik_zdjecia;
    $sciezka_wgledna_do_pliku = dirname($katalog_zdjec . '/' . $plik_zdjecia);

    //sprawdza czy przekazana zmienna z plikiem nie jest plikiem SVG
    if ( strtolower(pathinfo($plik_zdjecia, PATHINFO_EXTENSION)) == 'svg' ) {
      return '<img src="/' . $katalog_zdjec. '/' . $plik_zdjecia .'" alt="'.$alt.'" style="width:'.$szerokosc.'px; height:auto;" />';
    }

    // Pobranie danych o skladowych elementach sciezki do pliku
    $info = pathinfo($sciezka_bezwgledna_do_pliku);
    
    if ( !isset($info['extension']) ) {
         //
         $roz = explode('.', (string)$sciezka_bezwgledna_do_pliku);
         $info['extension'] = $roz[ count($roz) - 1];
         //
    }       

    // jezeli plik nie jest w formacie WEBP, to bedzie tworzyl miniaturke w takim formacie
    if ( MINIATURKI_WEBP == 'tak' ) {
        if( function_exists('imagewebp') ) {
            if ( $info["extension"] != 'webp' ) {
                $info["basename"] = str_replace($info["extension"], 'webp', (string)$info["basename"]);
                $info["extension"] = 'webp';
            }
        }
    }

    // Jezeli jest znak wodny koduje nazwa zdjecia
    if ( $znakWodny == true && isset($info["extension"]) ) {
        //
        $nazwa_pliku_miniaturki = $szerokosc . $prefix_miniaturek . md5($info["basename"]) . '.' . $info["extension"];
        //
    } else {
        //
        $nazwa_pliku_miniaturki = $szerokosc . $prefix_miniaturek . $info["basename"];
        //
    }
    
    // Jezeli sa ikony na obrazku
    $Ikona = '';
    //
    $Ikona = Funkcje::WyswietlIkony($ikony);
    //        
    if ( $szerokosc < 100 || $wysokosc < 100 ) {
         $Ikona = '';
    }
    //

    if ( is_file($info['dirname'] . '/' . $katalog_miniaturek . '/' . $nazwa_pliku_miniaturki) ) {

      $miniaturka =  $sciezka_wgledna_do_pliku . '/' . $katalog_miniaturek . '/' . $nazwa_pliku_miniaturki;
      //  title="'.$alt.'"
      
      // preloader
      if ( $ladowanie == true ) {

           if ( $Ikona != '' ) {

                return '<span class="ZdjecieIkony">' . $Ikona . '<img data-src-original="' . $miniaturka .'" width="' . $szerokosc . '" ' . (($skaluj == true) ? 'height="' . $wysokosc . '"' : '') . ' src="' . KATALOG_ZDJEC . '/loader.gif" ' . $parametr . ' alt="' . $alt . '" title="' . $alt . '" /></span>';
                
           } else {
             
                return '<img data-src-original="' . $miniaturka .'" width="' . $szerokosc . '" ' . (($skaluj == true) ? 'height="' . $wysokosc . '"' : '') . ' src="' . KATALOG_ZDJEC . '/loader.gif" ' . $parametr . ' alt="' . $alt . '" title="' . $alt . '" />';
                
           }
           
      } else {
           
           if ( $Ikona != '' ) {
         
                return '<span class="ZdjecieIkony">' . $Ikona . '<img src="' . $miniaturka . '" width="' . $szerokosc . '" ' . (($skaluj == true) ? 'height="' . $wysokosc . '"' : '') . ' ' . $parametr . ' alt="' . $alt . '" title="' . $alt . '" /></span>';
                
           } else {
                return '<img src="' . $miniaturka . '" width="' . $szerokosc . '" ' . (($skaluj == true) ? 'height="' . $wysokosc . '"' : '') . ' ' . $parametr . ' alt="' . $alt . '" title="' . $alt . '" />';
                
           }
           
      }

    } else { 

      // Tablica przedrostkow plikow zaleznych od ustawionych zabezpieczen
      $tablica_przedrostkow = array();
      $tablica_przedrostkow[]  = 'wpx_';
      $tablica_przedrostkow[]  = 'px_';

      // Sprawdza czy istnieje katalog na miniaturki - jesli nie to go tworzy
      if (is_dir($info['dirname'] . '/' . $katalog_miniaturek) == false) {
          //
          $old_mask = umask(0);
          mkdir($info['dirname'] . '/' . $katalog_miniaturek, 0777, true);
          umask($old_mask);
          //
      }

      // Usuwa miniaturki, ktore nie spelniaja aktualnych warunkow zabezpieczenia
      for ( $i = 0, $c = count($tablica_przedrostkow); $i < $c; $i++ ) {
          //
          if ( $tablica_przedrostkow[$i] != $prefix_miniaturek ) {
              if ( is_file($info['dirname'] . '/' . $katalog_miniaturek . '/' . $szerokosc.$tablica_przedrostkow[$i].$info["basename"]) ) {
                @unlink($info['dirname'] . '/' . $katalog_miniaturek . '/' . $szerokosc.$tablica_przedrostkow[$i].$info["basename"]);
              }
          }
          //
      }

      // Generowanie miniaturki
      $file = $sciezka_bezwgledna_do_pliku;
      if ( !is_file($sciezka_bezwgledna_do_pliku) ) {
        $file = KATALOG_SKLEPU . $katalog_zdjec . '/domyslny.webp';
        $nazwa_pliku_miniaturki = $plik_zdjecia_domyslny;
      }
      $plik_do_zpiasania = KATALOG_SKLEPU . $sciezka_wgledna_do_pliku . '/' . $katalog_miniaturek . '/' . $nazwa_pliku_miniaturki;

      $plik_watermark = KATALOG_SKLEPU . KATALOG_ZDJEC . '/' . OBRAZ_COPYRIGHT_DUZY;

      $image_info = getimagesize($file);

      if ( $image_info === false ) {
        $Mime = '';
      } else {
        $Mime = $image_info['mime'];
      }

      // Jezeli na serwerze jest obsluga WEBP to ustawi do zapisu format WEBP
      if ( MINIATURKI_WEBP == 'tak' ) {
          if( function_exists('imagewebp') ) {
              if ( $Mime != 'image/webp' ) {
                  $Mime = 'image/webp';
              }
          }
      }

      $color_tla = '#'.MINIATURKA_KOLOR_TLA;
      if ( $image_info === false ) {
        $file = KATALOG_SKLEPU . $katalog_zdjec . '/domyslny.webp';
        $color_tla = '#FFFFFF';
      } else {
          if ( MINIATURKA_KOLOR_TLA == '' ) {
            if ( $image_info['2'] == '18' || $image_info['2'] == '3' ) {
                $color_tla = 'transparent';
            } else {
                $color_tla = '#FFFFFF';
            }
          }
      }

      try {
          $tlo_obrazka = new SimpleImage($file);
          $tlo_obrazka->autoOrient()->bestFit($szerokosc, $wysokosc);

          $obrazek = new SimpleImage();
          $obrazek->fromNew($szerokosc, $wysokosc, $color_tla);
          $obrazek->overlay($tlo_obrazka);

          if ( $znakWodny == true && $wielkosc != 'maly' && $wielkosc != 'sredni' && OBRAZ_COPYRIGHT_POKAZ == 'tak' ) {

            $przeroczystosc = OBRAZ_COPYRIGHT_OPACITY / 100;
            $plik_znaku = new SimpleImage($plik_watermark);
            $plik_znaku->bestFit($szerokosc, $wysokosc);

            $obrazek->overlay($plik_znaku, OBRAZ_COPYRIGHT_POZYCJA, $przeroczystosc);
          }

          if ( $znakWodny == true && $wielkosc != 'maly' && $wielkosc != 'sredni' && TEKST_COPYRIGHT_POKAZ == 'tak' ) {
             $obrazek->text(TEKST_COPYRIGHT_TRESC, array(
                        'fontFile' => KATALOG_SKLEPU . 'programy/font/'.TEKST_COPYRIGHT_FONT,
                        'size' => TEKST_COPYRIGHT_FONT_ROZMIAR,
                        'color' => '#'.TEKST_COPYRIGHT_FONT_KOLOR,
                        'anchor' => TEKST_COPYRIGHT_POZYCJA));
          }

          $obrazek->toFile($plik_do_zpiasania, $Mime, MINIATURKI_KOMPRESJA);

      } catch(Exception $err) {
          echo $err->getMessage();
      }

      $miniaturka =  '/' . $sciezka_wgledna_do_pliku . '/' . $katalog_miniaturek . '/' . $nazwa_pliku_miniaturki;
      // preloader
      if ( $ladowanie == true ) {
        if ( trim((string)$Ikona) != '' ) {
           return '<span class="ZdjecieIkony">' . $Ikona . '<img data-src-original="' . $miniaturka .'" src="' . KATALOG_ZDJEC . '/loader.gif" ' . $parametr . ' width="' . $szerokosc . '" ' . (($skaluj == true) ? 'height="' . $wysokosc . '"' : '') . ' alt="'.$alt.'" title="' . $alt . '" /></span>';
        } else {
           return '<img data-src-original="' . $miniaturka .'" src="' . KATALOG_ZDJEC . '/loader.gif" ' . $parametr . ' width="' . $szerokosc . '" ' . (($skaluj == true) ? 'height="' . $wysokosc . '"' : '') . ' alt="'.$alt.'" title="' . $alt . '" />';
        }
      } else {
         
        if ( trim((string)$Ikona) != '' ) {
           return '<span class="ZdjecieIkony">' . $Ikona . '<img src="' . $miniaturka .'" ' . $parametr . ' width="' . $szerokosc . '" ' . (($skaluj == true) ? 'height="' . $wysokosc . '"' : '') . ' alt="'.$alt.'" title="' . $alt . '" /></span>';
        } else {
           return '<img src="' . $miniaturka .'" ' . $parametr . ' width="' . $szerokosc . '" ' . (($skaluj == true) ? 'height="' . $wysokosc . '"' : '') . ' alt="'.$alt.'" title="' . $alt . '" />';
        }
           
      }      

    }

    unset($katalog_miniaturek, $prefix_miniaturek, $Ikona, $ikony);


  }  

  // funkcja generujaca i wyswietlajaca zdjecia duze ze znakiem wodnym
  public static function pokazObrazekWatermark( $plik_zdjecia) {

    if ( TEKST_COPYRIGHT_POKAZ == 'tak' || OBRAZ_COPYRIGHT_POKAZ == 'tak' ) {
        $znakWodny = true;
    }

    $katalog_zdjec      = KATALOG_ZDJEC;
    $katalog_miniaturek = 'mini';
    $prefix_miniaturek  = 'px_';

    if ( $znakWodny ) {
      $katalog_miniaturek = 'watermark';
      $prefix_miniaturek  = 'wpx_';
    }

    // Sprawdza czy przekazana zmienna z plikiem nie jest pusta
    if ( ($plik_zdjecia == '') || ($plik_zdjecia == 'NULL') || (strlen(trim((string)$plik_zdjecia)) == 0) || pathinfo($plik_zdjecia, PATHINFO_EXTENSION) == 'swf' ) {
      return '';
    }

    // Sprawdza czy przekazana zmienna z plikiem zawiera adres URL
    $czy_jest_url = strpos((string)$plik_zdjecia, 'http');
    if ($czy_jest_url !== false) {
      $adres_zdjecia =  preg_replace("/((http|https|ftp):\/\/)?([^\/]+)(.*)/si", "$4", (string)$plik_zdjecia);
      $plik_zdjecia = str_replace($katalog_zdjec, '', (string)$adres_zdjecia);
    }

    $znaczki = array("%5B", "%5D", "%20");
    $nawiasy = array("[", "]", " ");
    $plik_zdjecia = str_replace($znaczki, $nawiasy, (string)$plik_zdjecia);

    $sciezka_bezwgledna_do_pliku = KATALOG_SKLEPU . $katalog_zdjec . '/' . $plik_zdjecia;

    // Sprawdza czy istnieje na serwerze plik przekazany do funkcji
    if ( is_file($sciezka_bezwgledna_do_pliku) ) {
      $plik_zdjecia = $plik_zdjecia;
    } else {
        return '/' . $katalog_zdjec . '/domyslny.webp';
    }
    $sciezka_bezwgledna_do_pliku = KATALOG_SKLEPU . $katalog_zdjec . '/' . $plik_zdjecia;
    $sciezka_wgledna_do_pliku = dirname($katalog_zdjec . '/' . $plik_zdjecia);

    // Pobranie danych o skladowych elementach sciezki do pliku
    $info = pathinfo($sciezka_bezwgledna_do_pliku);

    list($szerokosc, $wysokosc, $typ, $atrybuty) = getimagesize($sciezka_bezwgledna_do_pliku);

    $nazwa_pliku_miniaturki = md5($info["basename"]).'.'.$info["extension"];
    
    if ( is_file($info['dirname'] . '/' . $katalog_miniaturek . '/' . $prefix_miniaturek . $nazwa_pliku_miniaturki) ) {

      $miniaturka =  $sciezka_wgledna_do_pliku . '/' . $katalog_miniaturek . '/' . $prefix_miniaturek . $nazwa_pliku_miniaturki;
      
      return $miniaturka;
           
    } else {

      // Tablica przedrostkow plikow zaleznych od ustawionych zabezpieczen
      $tablica_przedrostkow = array();
      $tablica_przedrostkow[]  = 'wpx_';
      $tablica_przedrostkow[]  = 'px_';

      // Sprawdza czy istnieje katalog na miniaturki - jesli nie to go tworzy
      if (is_dir($info['dirname'] . '/' . $katalog_miniaturek) == false) {
        $old_mask = umask(0);
        mkdir($info['dirname'] . '/' . $katalog_miniaturek, 0777, true);
        umask($old_mask);
      }
      // Usuwa miniaturki, ktore nie spelniaja aktualnych warunkow zabezpieczenia
      for ( $i = 0, $c = count($tablica_przedrostkow); $i < $c; $i++ ) {
        if ( $tablica_przedrostkow[$i] != $prefix_miniaturek ) {
          if ( is_file($info['dirname'] . '/' . $katalog_miniaturek . '/' . $tablica_przedrostkow[$i].$nazwa_pliku_miniaturki) ) {
            @unlink($info['dirname'] . '/' . $katalog_miniaturek . '/' . $tablica_przedrostkow[$i].$nazwa_pliku_miniaturki);
          }
        }
      }

      // Generowanie miniaturki
      $file = $sciezka_bezwgledna_do_pliku;

      if ( !is_file($sciezka_bezwgledna_do_pliku) ) {
        $file = KATALOG_SKLEPU . $katalog_zdjec . '/domyslny.webp';
      }

      $plik_do_zpiasania = KATALOG_SKLEPU . $sciezka_wgledna_do_pliku . '/' . $katalog_miniaturek . '/' . $prefix_miniaturek . $nazwa_pliku_miniaturki;
      $plik_watermark = KATALOG_SKLEPU . KATALOG_ZDJEC . '/' . OBRAZ_COPYRIGHT_DUZY;

      $image_info = getimagesize($file);

      if ( $image_info === false ) {
        $Mime = '';
      } else {
        $Mime = $image_info['mime'];
      }


      try {

          $obrazek = new SimpleImage($file);
          $obrazek->autoOrient();

          if ( $znakWodny == true && OBRAZ_COPYRIGHT_POKAZ == 'tak' ) {

            $przeroczystosc = OBRAZ_COPYRIGHT_OPACITY / 100;
            $plik_znaku = new SimpleImage($plik_watermark);
            $plik_znaku->autoOrient()->bestFit($szerokosc, $wysokosc);

            $obrazek->overlay($plik_znaku, OBRAZ_COPYRIGHT_POZYCJA, $przeroczystosc);
          }

          if ( $znakWodny == true && TEKST_COPYRIGHT_POKAZ == 'tak' ) {
            if (  TEKST_COPYRIGHT_FONT != '' ) {
                $FontTTF = TEKST_COPYRIGHT_FONT;
                if ( !is_file(KATALOG_SKLEPU . 'programy/font/'.TEKST_COPYRIGHT_FONT) ) {
                    $FontTTF = 'Roboto.ttf';
                }
            } else {
                $FontTTF = 'Roboto.ttf';
            }

            $obrazek->text(TEKST_COPYRIGHT_TRESC, array(
                            'fontFile' => KATALOG_SKLEPU . 'programy/font/'.$FontTTF,
                            'size' => TEKST_COPYRIGHT_FONT_ROZMIAR,
                            'color' => '#'.TEKST_COPYRIGHT_FONT_KOLOR,
                            'anchor' => TEKST_COPYRIGHT_POZYCJA));

          }

          $obrazek->toFile($plik_do_zpiasania, $Mime);

      } catch(Exception $err) {
          echo $err->getMessage();
      }

      $miniaturka =  '/' . $sciezka_wgledna_do_pliku . '/' . $katalog_miniaturek . '/' . $prefix_miniaturek . $nazwa_pliku_miniaturki;

      return $miniaturka;
           
    }

  }  

  // funkcja generujaca i wyswietlajaca miniaturki zdjec wykadrowane do podanych wymiarow - do galerii i aktualnosci
  public static function pokazObrazekKadrowany( $plik_zdjecia, $alt = '', $szerokosc = '', $wysokosc = '' ) {
    
    $katalog_zdjec      = KATALOG_ZDJEC;
    $katalog_miniaturek = 'crop';
    $prefix_miniaturek  = 'px_';

    // Sprawdza czy przekazana zmienna z plikiem nie jest pusta
    if ( ($plik_zdjecia == '') || ($plik_zdjecia == 'NULL') || (strlen(trim((string)$plik_zdjecia)) == 0) || pathinfo($plik_zdjecia, PATHINFO_EXTENSION) == 'swf' ) {
         //
         return '';
         //
    }

    // Sprawdza czy przekazana zmienna z plikiem zawiera adres URL
    $czy_jest_url = strpos((string)$plik_zdjecia, 'http');
    if ($czy_jest_url !== false) {
        //
        $adres_zdjecia =  preg_replace("/((http|https|ftp):\/\/)?([^\/]+)(.*)/si", "$4", (string)$plik_zdjecia);
        $plik_zdjecia = str_replace($katalog_zdjec, '', (string)$adres_zdjecia);
        //
    }

    $znaczki = array("%5B", "%5D", "%20");
    $nawiasy = array("[", "]", " ");
    $plik_zdjecia = str_replace($znaczki, $nawiasy, (string)$plik_zdjecia);

    $sciezka_bezwgledna_do_pliku = KATALOG_SKLEPU . $katalog_zdjec . '/' . $plik_zdjecia;
    $file = $sciezka_bezwgledna_do_pliku;

    // Sprawdza czy istnieje na serwerze plik przekazany do funkcji
    if ( is_file($sciezka_bezwgledna_do_pliku) && filesize($sciezka_bezwgledna_do_pliku) > 0 ) {
        //
        $plik_zdjecia = $plik_zdjecia;
        //
    } else {
        //
        return '';
    }
    
    $sciezka_wgledna_do_pliku = dirname($katalog_zdjec . '/' . $plik_zdjecia);

    // Pobranie danych o skladowych elementach sciezki do pliku
    $info = pathinfo($sciezka_bezwgledna_do_pliku);
    $image_info = getimagesize($file);

    if ( $image_info === false ) {
        $Mime = '';
    } else {
        $Mime = $image_info['mime'];
    }

    // jezeli plik nie jest w formacie WEBP, to bedzie tworzyl miniaturke w takim formacie
    if ( MINIATURKI_WEBP == 'tak' ) {
        if( function_exists('imagewebp') ) {
            if ( $info["extension"] != 'webp' ) {
                $info["basename"] = str_replace($info["extension"], 'webp', (string)$info["basename"]);
            }
        }
    }

    $nazwa_pliku_miniaturki = $szerokosc . $prefix_miniaturek . $info["basename"];
    
    if ( is_file($info['dirname'] . '/' . $katalog_miniaturek . '/' . $nazwa_pliku_miniaturki) ) {

      $miniaturka =  $sciezka_wgledna_do_pliku . '/' . $katalog_miniaturek . '/' . $nazwa_pliku_miniaturki;
      
      return '<img src="' . $miniaturka . '" width="' . ( $image_info['0'] < $szerokosc ? $image_info['0'] : $szerokosc ) . '" ' . (((int)$wysokosc > 0) ? 'height="' . ( $image_info['1'] < $wysokosc ? $image_info['1'] : $wysokosc ) . '"' : '') . ' alt="' . $alt . '" title="' . $alt . '" />';

    } else { 

      // Tablica przedrostkow plikow zaleznych od ustawionych zabezpieczen
      $tablica_przedrostkow = array();
      $tablica_przedrostkow[]  = 'px_';

      // Sprawdza czy istnieje katalog na miniaturki - jesli nie to go tworzy
      if (is_dir($info['dirname'] . '/' . $katalog_miniaturek) == false) {
          //
          $old_mask = umask(0);
          mkdir($info['dirname'] . '/' . $katalog_miniaturek, 0777, true);
          umask($old_mask);
          //
      }

      // Usuwa miniaturki, ktore nie spelniaja aktualnych warunkow zabezpieczenia
      for ( $i = 0, $c = count($tablica_przedrostkow); $i < $c; $i++ ) {
          //
          if ( $tablica_przedrostkow[$i] != $prefix_miniaturek ) {
              if ( is_file($info['dirname'] . '/' . $katalog_miniaturek . '/' . $szerokosc.$tablica_przedrostkow[$i].$info["basename"]) ) {
                @unlink($info['dirname'] . '/' . $katalog_miniaturek . '/' . $szerokosc.$tablica_przedrostkow[$i].$info["basename"]);
              }
          }
          //
      }

      // Generowanie miniaturki

      if ( !is_file($sciezka_bezwgledna_do_pliku) ) {
        return '';
      }

      $plik_do_zpiasania = KATALOG_SKLEPU . $sciezka_wgledna_do_pliku . '/' . $katalog_miniaturek . '/' . $nazwa_pliku_miniaturki;

      // Jezeli na serwerze jest obsluga WEBP to ustawi do zapisu format WEBP
      if ( MINIATURKI_WEBP == 'tak' ) {
          if( function_exists('imagewebp') ) {
              if ( $Mime != 'image/webp' ) {
                  $Mime = 'image/webp';
              }
          }
      }

      try {

          $obrazek = new SimpleImage($file);

          if ( $image_info['0'] > $szerokosc || $image_info['1'] > $wysokosc ) {
              $obrazek->fromFile($file)
                      ->autoOrient()                       
                      ->thumbnail($szerokosc, $wysokosc, "center");
          } else {
              $obrazek->fromFile($file)
                      ->autoOrient()                       
                      ->bestfit($szerokosc, $wysokosc);
          }
          $obrazek->toFile($plik_do_zpiasania, $Mime, MINIATURKI_KOMPRESJA);

      } catch(Exception $err) {
          echo $err->getMessage();
      }

      $miniaturka =  '/' . $sciezka_wgledna_do_pliku . '/' . $katalog_miniaturek . '/' . $nazwa_pliku_miniaturki;
      return '<img src="' . $miniaturka . '" width="' . ( $image_info['0'] < $szerokosc ? $image_info['0'] : $szerokosc ) . '" ' . (((int)$wysokosc > 0) ? 'height="' . ( $image_info['1'] < $wysokosc ? $image_info['1'] : $wysokosc ) . '"' : '') . ' alt="' . $alt . '" title="' . $alt . '" />';

    }

    unset($katalog_miniaturek, $prefix_miniaturek);


  }  


  // funkcja przycinajaca tekst do okreslonej ilosci znakow
  public static function przytnijTekst($tekst, $dlugosc = 250, $zakonczenie = '&#8230;') {

    if ( mb_strlen((string)$tekst) < $dlugosc ) {
      return $tekst;
    }

    $tekst = str_replace('&nbsp;', ' ', (string)$tekst);
    $tekst = str_replace(array("\r\n", "\r", "\n"), ' ', (string)$tekst);

    if (mb_strlen((string)$tekst) <= $dlugosc) {
      return $tekst;
    }

    $wynik = "";
    foreach (explode(' ', trim((string)$tekst)) as $val) {
      $wynik .= $val.' ';

      if (mb_strlen((string)$wynik) >= $dlugosc) {
        $wynik = trim((string)$wynik);
        return (mb_strlen((string)$wynik) == mb_strlen((string)$tekst)) ? $wynik : $wynik.$zakonczenie;
      }       
    }
  }

  // funkcja losujaca elementy z tablicy
  public static function wylosujElementyTablicyJakoTekst($tablicaWejsciowa, $LimitZapytania = 1) {

    $wynik = array();
    //$tablicaWejsciowa = array_unique($tablicaWejsciowa);

    if ( count($tablicaWejsciowa) < $LimitZapytania ) {
      $LimitZapytania = count($tablicaWejsciowa);
    }

    //srand ((float) microtime() * 10000000);
    $LosowaTablica = array_rand($tablicaWejsciowa, $LimitZapytania);

    if ( is_array($LosowaTablica) && count($LosowaTablica) > 1 ) {
      foreach ( $LosowaTablica as $val) {
        $wynik[] = $tablicaWejsciowa[$val];
      }
    } else {
      $wynik[] = $tablicaWejsciowa[$LosowaTablica];
    }
    
    shuffle($wynik);

    return implode(',', (array)$wynik);

  }

  // funkcja losujaca elementy z tablicy
  public static function wylosujElementyTablicyJakoTablica($tablicaWejsciowa, $LimitZapytania = 1) {

    $wynik = array();
    //$tablicaWejsciowa = array_unique($tablicaWejsciowa);

    if ( count($tablicaWejsciowa) < $LimitZapytania ) {
      $LimitZapytania = count($tablicaWejsciowa);
    }

    //srand ((float) microtime() * 10000000);
    $LosowaTablica = array_rand($tablicaWejsciowa, $LimitZapytania);

    if ( is_array($LosowaTablica) && count($LosowaTablica) > 1 ) {
      foreach ( $LosowaTablica as $val) {
        $wynik[] = $tablicaWejsciowa[$val];
      }
    } else {
      $wynik[] = $tablicaWejsciowa[$LosowaTablica];
    }

    shuffle($wynik);

    return $wynik;

  }
  
  // funkcja generujaca rozwijane menu SELECT
  public static function RozwijaneMenu($nazwa, $wartosc, $default = '', $parametry = '') {
    $wynik = '<select name="' . $nazwa . '"';

    if (Funkcje::czyNiePuste($parametry)) $wynik .= ' ' . $parametry;

    $wynik .= '>';

    if (empty($default) && ( (isset($_GET[$nazwa]) && is_string($_GET[$nazwa])) || (isset($_POST[$nazwa]) && is_string($_POST[$nazwa])) ) ) {
      if (isset($_GET[$nazwa]) && is_string($_GET[$nazwa])) {
        $default = stripslashes((string)$_GET[$nazwa]);
      } elseif (isset($_POST[$nazwa]) && is_string($_POST[$nazwa])) {
        $default = stripslashes((string)$_POST[$nazwa]);
      }
    }

    for ($i = 0, $n = count($wartosc); $i < $n; $i++) {
      $ciag_tekstu = $wartosc[$i]['text'];
      
      $wynik .= '<option value="' . $wartosc[$i]['id'] . '"';
      if ($default == '') {
          if ($wartosc[$i]['id'] == '0') {
              $wynik .= ' selected="selected"';
          }
        } else {      
          if ($default == $wartosc[$i]['id']) {
            $wynik .= ' selected="selected"';
          }
      }

      $wynik .= '>' . $ciag_tekstu . '</option>';
    }
    $wynik .= '</select>';

    return $wynik;
  }  
  
  // funkcja podstawiajaca wartosci pod zmienne w szablonach maili
  public static function parsujZmienne($tekst){

    $szukanyCiag          = "/\{([a-zA-Z0-9_]+)\}/i";
    $funkcjaZamiany       = 'Funkcje::podstawZmienne';

    return preg_replace_callback($szukanyCiag, $funkcjaZamiany, (string)$tekst);
  }

  public static function podstawZmienne($matches) {
      if ( defined($matches[1]) ) {
           return constant($matches[1]);
      } else {
           return '';
      }
  }

  // funkcja kodujaca haslo
  public static function zakodujHaslo($tekst) {
    $haslo = '';
    for ($i=0; $i<10; $i++) {
      $haslo .= Funkcje::losowaWartosc();
    }
    $salt = substr(md5($haslo), 0, 2);
    $haslo = md5($salt . $tekst) . ':' . $salt;
    return $haslo;
  }

  // funkcja zwaracjaca losowa wartosc liczbowa
  public static function losowaWartosc($min = null, $max = null) {
    static $seeded;

    if (!isset($seeded)) {
      mt_srand( intval(microtime(true) * 1000000) );
      $seeded = true;
    }
    if (isset($min) && isset($max)) {
      if ($min >= $max) {
        return $min;
      } else {
        return mt_rand($min, $max);
      }
    } else {
      return mt_rand();
    }
  }

  // funkcja generujaca losowe haslo
  public static function generujHaslo() {

    $losoweMale = substr(str_shuffle("abcdefghijkmnopqrstuvwxyz"), 0, 4);
    $losoweDuze = substr(str_shuffle("ABCDEFGHIJKLMNPQRSTUVWXYZ"), 0, 4);
    $losoweCyfry = substr(str_shuffle("23456789"), 0, 1);
    $losoweZnaki = substr(str_shuffle("@#$%"), 0, 1);

    $tekst = str_shuffle($losoweMale.$losoweDuze.$losoweCyfry.$losoweZnaki);

    return $tekst;
  }

  // funkcja losowo mieszajaca tablice
  public static function wymieszajTablice($list) { 
    if (!is_array($list)) return $list; 

    $klucze = array_keys($list); 
    shuffle($klucze); 
    $random = array(); 
    foreach ($klucze as $klucz) { 
      $random[] = $list[$klucz]; 
    }
    return $random; 
  } 
  
  // ile razy wystapil element w tablicy
  public static function arrayIloscWystapien($zwrot, $tablica){
    $zwrot_array = array();
    //
    for ($i = 0, $x = count($tablica); $i < $x; $i++) {
        //
        if ($tablica[$i] == $zwrot) {
            $zwrot_array[] = $tablica[$i];
        }
        //
    }
    $iloscWystapien = count($zwrot_array);
    //
    return $iloscWystapien;
  }
 
  public static function drukujTablice($tablica, $exit = false, $echo = false) {
    ob_start();
    if ( count($tablica) > 0 ) {
      echo "<pre>";
      print_r ($tablica);
      echo "</pre>";
    } else {
      echo 'tablica jest pusta';
    }
    $wynik = ob_get_contents();
    ob_end_clean();        
    //
    if ( $echo == false) {
         echo $wynik;
      } else {
         return $wynik;
    }
    //
    if ($exit) exit();
  }

  // zapisanie i wyswietlanie czasu przetwarzania strony - tylko dla celow programistycznych
  public static function pokazSledzenie() {
    echo '<div style="text-align: left;" class="tekst10"><hr />';
    echo '<b>Tablica SESSION:</b> ';
    Funkcje::drukujTablice($_SESSION);
    echo '<hr />';
    echo '<b>Tablica COOKIE:</b> ';
    Funkcje::drukujTablice($_COOKIE);
    echo '<hr />';
    echo '<b>Tablica POST:</b> ';
    Funkcje::drukujTablice($_POST);
    echo '<hr />';
    echo '<b>Tablica GET:</b> ';
    Funkcje::drukujTablice($_GET);
    echo '<hr />';
  }

  // wygenerowanie header location dla polaczenia SSL
  public static function PrzekierowanieSSL( $adres, $Naglowek301 = false ) {

    if ( $Naglowek301 ) {
        header('HTTP/1.1 301 Moved Permanently');
    }

    //zawsze kieruje po https jesli jest logowanie
    if ( $adres == 'logowanie.html' && WLACZENIE_SSL == 'tak' ) { 
        session_write_close();
        header("Location: ".ADRES_URL_SKLEPU_SSL."/".$adres);
        exit();
    }

    //jesli jest podsumowanie zamowienia
    if ( $adres == '/zamowienie-podsumowanie.html' && WLACZENIE_SSL == 'tak' ) { 
        session_write_close();
        header("Location: ".ADRES_URL_SKLEPU_SSL.$adres);
        exit();
    }

    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != '') {
        session_write_close();
        header("Location: ".ADRES_URL_SKLEPU_SSL."/".$adres);
        exit();
    } else {
        session_write_close();
        header("Location: ".ADRES_URL_SKLEPU."/".$adres);
        exit();
    }
  
    return;
  }
  
  // wygenerowanie header location
  public static function PrzekierowanieURL( $adres, $Naglowek301 = false ) {
    //
    
    session_write_close();
    if ( strpos((string)$adres, 'brak-strony.html') > -1 || $Naglowek301 ) {
      header('HTTP/1.1 301 Moved Permanently');
    }

    if ( $adres == '' || $adres == '/' ) {
         header("Location: ".ADRES_URL_SKLEPU);
       } else {
         header("Location: ".ADRES_URL_SKLEPU."/".$adres);
    }
    exit();    

    //
  }  
  
  // zastepuje funkcje in_array - umozliwia szukanie wartosci tablicy w innej tablicy
  public static function SzukajwTablicy($Szukana, $Przeszukiwana) {
    //
    if ( empty($Szukana) ) {
        return false;
    }
    //
    $Znalezionych = 0;
    //
    foreach ($Szukana as $WartoscSzukana) {
        //
        if (in_array($WartoscSzukana, (array)$Przeszukiwana)) {
            $Znalezionych++;
        }
        //
    }
    //
    if (count($Szukana) == $Znalezionych) {
        return true;
      } else {
        return false;
    }
  }
  
  // czysci tablice wielowymiarowa z duplikatow
  public static function CzyscTabliceUnikalne($tablica) {
    //
    $wynik = array_map("unserialize", array_unique(array_map("serialize", $tablica)));
    //
    foreach ($wynik as $klucz => $wartosc) {
        if ( is_array($wartosc) ) {
            $wynik[$klucz] = Funkcje::CzyscTabliceUnikalne($wartosc);
        }
    }
    //
    return $wynik;
  }
  
  // funkcja zwraca aktualny link przegladarki
  public static function RequestURI() {
    if(!isset($_SERVER['REQUEST_URI'])) {
        $_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME'];
        if($_SERVER['QUERY_STRING']) {
            $_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING'];
        }
    }
    return $_SERVER['REQUEST_URI'];
  }  

  // zwraca get z linku
  public static function Zwroc_Get($tablica = '', $dodawanie = false, $separator = '') {
    global $filtr;
    //
    if ($separator == '') {
        $separator = '&';
        $znak = '?';
        if ($dodawanie == true) {
          $znak = '&';
        }
      } else {
        $znak = $separator;
    }
    
    if ($tablica == '') $tablica = array();
    //
    $wynik = '';
    reset($_GET);
    foreach($_GET as $key => $value) {
      //
      $klucz = $filtr->process($key, true);
      $wartosc = $filtr->process($value, true);
      //
      if ( $klucz == 'szukaj' ) {
           $wartosc = str_replace('/', '[back]', (string)$wartosc);
      }
      //
      if (!in_array($klucz, $tablica) && !empty($klucz) && !empty($wartosc)) { $wynik .= $klucz . '=' . $wartosc . $separator; }
    }
    if (!empty($wynik)) {
      $wynik = $znak.$wynik;
      $wynik = substr((string)$wynik,0,strlen((string)$wynik)-1);
    }
    return $wynik;
  }    
  
  // funkcja wyswietlajaca status zamowienia klienta
  public static function pokazNazweStatusuZamowienia( $status_id, $jezyk = '1') {

    $wynik = '';
    
    $zapytanie = "SELECT s.orders_status_id, s.orders_status_color, sd.orders_status_name FROM orders_status s LEFT JOIN orders_status_description sd ON sd.orders_status_id = s.orders_status_id WHERE s.orders_status_id = '".$status_id."' AND sd.language_id = '".$jezyk."'";
    $sql = $GLOBALS['db']->open_query($zapytanie);
    
    if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {

        while($nazwa_statusu = $sql->fetch_assoc()) {
            $wynik = '<span>'.$nazwa_statusu['orders_status_name'].'</span>';
        }
        
    }
    
    $GLOBALS['db']->close_query($sql);  
    unset($zapytanie);
    
    return $wynik;
  } 

  // funkcja wyswietlajaca imie i nazwisko opiekuna zamowienia
  public static function PokazOpiekuna($id) {

    $zapytanie = "SELECT * FROM admin WHERE admin_id = '".$id."'";
    $sql = $GLOBALS['db']->open_query($zapytanie);
    
    if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
      
        while ($info = $sql->fetch_assoc()) { 
          $wynik = $info['admin_firstname'] . ' ' . $info['admin_lastname'];
        }
      
    } else {
      
        $wynik = $GLOBALS['tlumacz']['OPIEKUN_BRAK'];
      
    }
    
    $GLOBALS['db']->close_query($sql); 
    unset($zapytanie);    

    return $wynik;
  }  

  // funkcja generujaca numer faktury VAT
  public static function WygenerujNumerFaktury( $typ ) {

    $numer_faktury = '1';

    $zapytanie = "SELECT MAX(invoices_nr) AS numerek
                  FROM invoices
                  WHERE invoices_type = '".$typ."' AND YEAR(invoices_date_generated) = '".ROK_KSIEGOWY_FAKTUROWANIA."'";
                  
    $sql = $GLOBALS['db']->open_query($zapytanie);

    if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
      
        while ($info = $sql->fetch_assoc()) {
          $numer_faktury = $info['numerek'] + 1;
        }
      
    }

    $GLOBALS['db']->close_query($sql);  
    unset($zapytanie);
    
    return $numer_faktury;
  } 

  // funkcja zwraca domyslna jednostke miary produktow ustawiona w sklepie
  public static function domyslnaJednostkaMiary() {
    
    $wynik = '';
    
    if ( isset($GLOBALS['jednostkiMiary'][0]) ) {
         $wynik = $GLOBALS['jednostkiMiary'][0]['id'];
    }
    
    return $wynik;
  }

  // funkcja zwraca domyslny podatek VAT ustawiony w sklepie
  public static function domyslnyPodatekVat() {

    $wynik = array();
    
    // cache zapytania
    $WynikCache = $GLOBALS['cache']->odczytaj('StawkaVatDomyslna', CACHE_INNE);      

    if ( !$WynikCache && !is_array($WynikCache) ) { 
    
        $sql = $GLOBALS['db']->open_query("SELECT tax_rates_id, tax_rate FROM tax_rates WHERE tax_default = '1'");  
        
        if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
          
            $tax = $sql->fetch_assoc();
            
            $wynik = array('id' => $tax['tax_rates_id'],
                           'stawka' =>  $tax['tax_rate']);
                           
        }
        
        $GLOBALS['db']->close_query($sql);
        
        $GLOBALS['cache']->zapisz('StawkaVatDomyslna', $wynik, CACHE_INNE);
        
      } else {
     
        $wynik = $WynikCache;
    
    }    

    return $wynik;
  }
  
  // funkcja zwraca stawke podatku VAT na podstawie ID
  public static function StawkaPodatekVat($id = 1, $tablica = false) {
  
    $stawkiVat = array();
    
    if ( $tablica == false ) {
    
        // cache zapytania
        $WynikCache = $GLOBALS['cache']->odczytaj('StawkiVat', CACHE_INNE);   
        
        if ( !$WynikCache && !is_array($WynikCache) ) {    

            $sql = $GLOBALS['db']->open_query("SELECT tax_rates_id, tax_rate, tax_description FROM tax_rates");  
            
            if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
              
                while ($tax = $sql->fetch_assoc()) {
                       $stawkiVat[$tax['tax_rates_id']] = $tax['tax_rate'];
                }
                
            }
            
            $GLOBALS['db']->close_query($sql);
            
            $GLOBALS['cache']->zapisz('StawkiVat', $stawkiVat, CACHE_INNE);
            
          } else {
         
            $stawkiVat = $WynikCache;
        
        }  

        return ((isset($stawkiVat[$id])) ? $stawkiVat[$id] : 0);

    }

    if ( $tablica == true ) {
    
        // cache zapytania
        $WynikCache = $GLOBALS['cache']->odczytaj('StawkiVatTablica', CACHE_INNE);   
        
        if ( !$WynikCache && !is_array($WynikCache) ) {    

            $sql = $GLOBALS['db']->open_query("SELECT tax_rates_id, tax_rate, tax_description FROM tax_rates");  
            
            if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
              
                while ($tax = $sql->fetch_assoc()) {
                       $stawkiVat[$tax['tax_rates_id']] = array($tax['tax_rates_id'], $tax['tax_rate'], $tax['tax_description']);
                }
                
            }
            
            $GLOBALS['db']->close_query($sql);
            
            $GLOBALS['cache']->zapisz('StawkiVatTablica', $stawkiVat, CACHE_INNE);
            
          } else {
         
            $stawkiVat = $WynikCache;
        
        }  

        return $stawkiVat;

    }    
    
  }  
  
  // sprawdza czy podana wartosc jest adresem email
  public static function CzyPoprawnyMail($email){
    return preg_match("/^[_a-z0-9+-]+(\.[_a-z0-9+-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/i", $email);
  }  

  // funkcja zwraca id produktu z ciagu z cechami - w postaci 1x1-1x2-2
  public static function SamoIdProduktuBezCech( $id ) {

    // dzieli na tablice
    $TabCechy = explode('x', (string)$id);
    $ZwrocId = (int)$TabCechy[0];
    //
    if ( KOSZYK_SPOSOB_DODAWANIA == 'tak' ) {
         //
         $TabUnikalny = explode('U', (string)$ZwrocId);
         $ZwrocId = $TabUnikalny[0];
         //
    }
    
    return $ZwrocId;

  }   
  
  // funkcja zwraca w postaci tablicy cechy produktu - w postaci 1x1-1x2-2
  public static function CechyProduktuPoId( $id, $tylko_ilosc = false ) {
    //
    
    if ( strpos((string)$id, 'U') > -1 ) {

        $id = substr((string)$id, 0, strpos((string)$id, 'U'));
    
    }      
    
    // dzieli na tablice
    $TabCechy = explode('x', (string)$id);
    $TablicaWynikow = array();

    if ( $tylko_ilosc == false ) {
    
        // zaczynam od 1 - pomijam pierwsza wartosc bo to id produktu
        for ($r = 1, $c = count($TabCechy); $r < $c; $r++) {
            //
            $CechyWart = explode('-', (string)$TabCechy[$r]);
            //
            $TablicaWynikow[] = array('nazwa_cechy' => $GLOBALS['NazwyCech'][ $CechyWart[0] ]['nazwa'],
                                      'wartosc_cechy' => $GLOBALS['WartosciCech'][ $CechyWart[1] ]['nazwa'],
                                      'id_cechy' => $CechyWart[0]);
            //
        }   
        
      } else {
      
        // jezeli ma tylko policzyc ilosc cech
      
        // zaczynam od 1 - pomijam pierwsza wartosc bo to id produktu
        for ($r = 1, $c = count($TabCechy); $r < $c; $r++) {
            //
            $CechyWart = explode('-', (string)$TabCechy[$r]);
            //
            $TablicaWynikow[] = array('cecha' => $CechyWart[0], 'wartosc' => $CechyWart[1]);
            //
        }  
      
    }

    return $TablicaWynikow;
    
  }  
  
  // funkcja tworzy tablice globalne z nazwami cech i wartosciami
  public static function TabliceCech() {
    //
    if (!isset($GLOBALS['NazwyCech'])) {
        //
        // nazwy cech
        $NazwyCech = array();
        //
        $WynikCache = $GLOBALS['cache']->odczytaj('NazwyCech_' . $_SESSION['domyslnyJezyk']['kod'], CACHE_INNE);
        
        if ( !$WynikCache && !is_array($WynikCache) ) {
            //
            $zapytanie = "SELECT products_options_id, products_options_type, products_options_value, products_options_name, products_options_description, products_options_images_enabled, products_options_sort_order FROM products_options WHERE language_id = '" . (int)$_SESSION['domyslnyJezyk']['id'] . "'";
            $sql = $GLOBALS['db']->open_query($zapytanie);
            //
            if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
                //
                while ($info = $sql->fetch_assoc()) {
                    //
                    $NazwyCech[$info['products_options_id']] = array('id'     => $info['products_options_id'],
                                                                     'typ'    => $info['products_options_type'],
                                                                     'rodzaj' => $info['products_options_value'],
                                                                     'nazwa'  => $info['products_options_name'],
                                                                     'opis'   => $info['products_options_description'],
                                                                     'sort'   => $info['products_options_sort_order']);
                    
                    //
                    if ( $info['products_options_images_enabled'] == 'true' ) {
                       //
                       $NazwyCech[$info['products_options_id']]['typ'] = 'foto';
                       //
                    }
                    //
                }
                //
                unset($info);
                //
            }
            //
            $GLOBALS['db']->close_query($sql);
            unset($zapytanie);
            //
            $GLOBALS['cache']->zapisz('NazwyCech_' . $_SESSION['domyslnyJezyk']['kod'], $NazwyCech, CACHE_INNE);
            //
          } else { 
            //
            $NazwyCech = $WynikCache;
            //
        }
          
        $GLOBALS['NazwyCech'] = $NazwyCech;
        
        //
    }
    
    if (!isset($GLOBALS['WartosciCech'])) {
        //
        // wartosci cech
        $WartosciCech = array();
        
        $WynikCache = $GLOBALS['cache']->odczytaj('WartosciCech_' . $_SESSION['domyslnyJezyk']['kod'], CACHE_INNE);
        
        if ( !$WynikCache && !is_array($WynikCache) ) {      
            //
            $zapytanie = "SELECT products_options_values_id, products_options_values_name, products_options_values_thumbnail, products_options_values_status, global_options_values_price_tax, global_price_prefix, global_options_values_weight FROM products_options_values WHERE language_id = '" . (int)$_SESSION['domyslnyJezyk']['id'] . "'";
            $sql = $GLOBALS['db']->open_query($zapytanie);
            //
            if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
                //
                while ($info = $sql->fetch_assoc()) {
                    //
                    $WartosciCech[$info['products_options_values_id']] = array('id'      => $info['products_options_values_id'],
                                                                               'nazwa'   => $info['products_options_values_name'],
                                                                               'foto'    => $info['products_options_values_thumbnail'],
                                                                               'wartosc' => $info['global_options_values_price_tax'],
                                                                               'prefix'  => $info['global_price_prefix'],
                                                                               'waga'    => $info['global_options_values_weight'],
                                                                               'status'  => (($info['products_options_values_status'] == 1) ? 'tak' : 'nie'));      
                }
                //
                unset($info);
                //
            }
            //
            $GLOBALS['db']->close_query($sql);
            unset($zapytanie); 
            //
            $GLOBALS['cache']->zapisz('WartosciCech_' . $_SESSION['domyslnyJezyk']['kod'], $WartosciCech, CACHE_INNE);
            //
          } else { 
            //
            $WartosciCech = $WynikCache;
            //
        }
            
        $GLOBALS['WartosciCech'] = $WartosciCech;
           
        //
    }
    //
  }
  
  // funkcja zwraca ilosc ogolna pol opisowych 
  public static function OgolnaIloscDodatkowychPol() {
    //
    $WynikCache = $GLOBALS['cache']->odczytaj('DodatkowePola_' . $_SESSION['domyslnyJezyk']['kod'], CACHE_INNE);
    
    if ( !$WynikCache ) {
        //
        $zapytanie = "SELECT DISTINCT products_extra_fields_id 
                                 FROM products_extra_fields 
                                WHERE products_extra_fields_status = '1' AND products_extra_fields_view = '1' AND (languages_id = '0' OR languages_id = '" . (int)$_SESSION['domyslnyJezyk']['id'] . "')";

        $sql = $GLOBALS['db']->open_query($zapytanie);
        //
        // obejscie zeby 0 nie bylo tozsame z false
        $zapisz = (int)$GLOBALS['db']->ile_rekordow($sql);
        if ( $zapisz == 0 ) {
             $zapisz = 'xx';
        }
        //
        $GLOBALS['cache']->zapisz('DodatkowePola_' . $_SESSION['domyslnyJezyk']['kod'], $zapisz, CACHE_INNE);
        $IloscPol = (int)$GLOBALS['db']->ile_rekordow($sql);
        //
        $GLOBALS['db']->close_query($sql);
        //
        unset($zapisz, $zapytanie);
        //
    } else {
        $IloscPol = (int)$WynikCache;
    }  

    return $IloscPol;
    
  }  
  
  // funkcja zwraca ilosc ogolna pol tekstowych
  public static function OgolnaIloscDodatkowychPolTekstowych() {
    //
    $WynikCache = $GLOBALS['cache']->odczytaj('DodatkoweTekstowe_' . $_SESSION['domyslnyJezyk']['kod'], CACHE_INNE);
    
    if ( !$WynikCache ) {
        //
        $zapytanie = "SELECT DISTINCT products_text_fields_id 
                                 FROM products_text_fields
                                WHERE products_text_fields_status = '1'";

        $sql = $GLOBALS['db']->open_query($zapytanie);
        //
        // obejscie zeby 0 nie bylo tozsame z false
        $zapisz = (int)$GLOBALS['db']->ile_rekordow($sql);
        if ( $zapisz == 0 ) {
             $zapisz = 'xx';
        }
        //
        $GLOBALS['cache']->zapisz('DodatkoweTekstowe_' . $_SESSION['domyslnyJezyk']['kod'], $zapisz, CACHE_INNE);
        $IloscPol = (int)$GLOBALS['db']->ile_rekordow($sql);
        //
        $GLOBALS['db']->close_query($sql);
        //
        unset($zapisz, $zapytanie);
        //
    } else {
        $IloscPol = (int)$WynikCache;
    }  

    return $IloscPol;
    
  }  
  
  // funkcja generujaca pola RADIO
  public static function ListaRadio($nazwa, $wartosc, $default = '', $parametry = '') {

    $wynik = '';

    $i = 1;
    foreach ( $wartosc as $rekord ) {

      if ( $rekord['id'] != '0' ) {
        $ciag_tekstu = $rekord['text'];
        
        $wynik .= '<input type="radio" id="'.$nazwa.'_' . $rekord['id'] . '" name="' . $nazwa . '" value="' . $rekord['id'] . '"';
        if ($default == '' && $i == '1') {
            $wynik .= ' checked="checked"';
        } else {      
            if ($default == $rekord['id']) {
              $wynik .= ' checked="checked"';
            }
        }

        if (Funkcje::czyNiePuste($parametry)) $wynik .= ' ' . $parametry . ' ';

        $wynik .= ' />'.$ciag_tekstu.'<br />';
      } else {
        $wynik .= '---';
      }
      $i++;
    }

    return $wynik;
  }  
  
  // funkcja obliczajaca wynik z dowolnego wzoru matematycznego
  public static function obliczWzor( $mathString ) {
    $mathString = trim((string)$mathString);
    $mathString = preg_replace('/[^0-9\+\-\*\.\/\(\) ]/i', '', (string)$mathString); 
    if ( is_numeric($mathString) ) {
        return $mathString;
    } else {
        if ( substr($mathString,0,1) != '*' ) {
             $compute = eval('return ' . $mathString . ';'); 
             return 0 + round((float)$compute, 2);
        }
    }

    return 0;

  }
  
  public static function Sg( $tekst ) {
    return base64_decode((string)$tekst);
  }

  // funkcja sprawdzajaca czy podana wartosc jest w zadanym zakresie liczb
  public static function czyWartoscJestwZakresie($wartosc, $maximum, $minimum) {

    if ( is_numeric($minimum) && $minimum != '0' ) {
      if ( $wartosc <= $minimum ) return false;
    }
    if ( is_numeric($maximum) && $maximum != '0' ) {
      if ( $wartosc >= $maximum ) return false;
    }
    return true;
  }

  // funkcja generujaca pola RADIO w koszyku
  public static function ListaRadioKoszyk($nazwa, $wartosc, $default = '', $parametry = '') {

    $wynik = '';

    $i = 1;
    $wybrany = 1;
    foreach ( $wartosc as $rekord ) {

      if ( $rekord['id'] != '0' ) {
      
        $wynik .= '<div class="ListaTbl">';

            $ciag_tekstu = $rekord['text'];

            if ( Wyglad::TypSzablonu() == true ) {

                $wynik .= '
                <div class="ListaRadio">';
                    $wynik .= '<label tabindex="0" data-id="' . $rekord['id'] . '" for="' . $nazwa . '_' . $rekord['id'] . '" aria-label="' . str_replace('"','', $ciag_tekstu) . '" title="' . str_replace('"','', $ciag_tekstu) . '"><span class="RodzajNazwa">' . $ciag_tekstu . '</span>';
                    $wynik .= '<input data-id="' . $rekord['id'] . '" type="radio" id="' . $nazwa . '_' . $rekord['id'] . '" wysylka="' . $rekord['klasa'] . '" name="' . $nazwa . '" value="' . $rekord['id'] . '"';
                        if ($default == '' && $i == '1') {
                            $wynik .= ' checked="checked"';
                            $wybrany = $rekord['id'];
                        } else {      
                            if ($default == $rekord['id']) {
                              $wynik .= ' checked="checked"';
                              $wybrany = $rekord['id'];
                            }
                        }
                    $wynik .= ' />';

                    if ( $rekord['objasnienie'] != '' ) {
                      
                      if ( file_exists('szablony/' . DOMYSLNY_SZABLON . '/obrazki/nawigacja/info_tip.png') ) {
                           $wynik .= '<span class="InfoTip '.$nazwa.'" id="InfoTip_'.$nazwa . '_' . $rekord['id'].'" data-tip-id="'.$nazwa . '_' . $rekord['id'].'"><img src="szablony/' . DOMYSLNY_SZABLON . '/obrazki/nawigacja/info_tip.png" alt="Info" />';
                      } else {
                           $wynik .= '<span class="InfoTip InfoTipBezGrafiki '.$nazwa.'" id="InfoTip_'.$nazwa . '_' . $rekord['id'].'" data-tip-id="'.$nazwa . '_' . $rekord['id'].'">';
                      }
                      
                      $wynik .= '<span id="tip_'.$nazwa . '_' . $rekord['id'].'" style="display:none;">'.$rekord['objasnienie'].'</span></span>';
                      
                    }
                    $wynik .= '<span class="ObrazekLogo" id="obrazek_' . $nazwa . '_' . $rekord['id'] . '">';
                    if ( isset($rekord['ikona']) && $rekord['ikona'] ) {
                         $wynik .= '<img src="' . KATALOG_ZDJEC . '/'. $rekord['ikona'] . '" alt="" />';
                    }
                    $wynik .= '</span>';
                    $wynik .= '<span class="radio" id="radio_' . $nazwa . '_' . $rekord['id'] . '"></span>';
                    $wynik .= '</label>';

            } else {

                $wynik .= '
                <div class="ListaRadio">
                    <input data-id="' . $rekord['id'] . '" type="radio" id="' . $nazwa . '_' . $rekord['id'] . '" wysylka="' . $rekord['klasa'] . '" name="' . $nazwa . '" value="' . $rekord['id'] . '"';
                    if ($default == '' && $i == '1') {
                        $wynik .= ' checked="checked"';
                        $wybrany = $rekord['id'];
                    } else {      
                        if ($default == $rekord['id']) {
                          $wynik .= ' checked="checked"';
                          $wybrany = $rekord['id'];
                        }
                    }
                $wynik .= ' /></div>';

            }

            if (Funkcje::czyNiePuste($parametry)) $wynik .= ' ' . $parametry . ' ';

            if ( Wyglad::TypSzablonu() != true ) {
                $wynik .= '<div class="ListaOpis"><label tabindex="0" data-id="' . $rekord['id'] . '" for="' . $nazwa . '_' . $rekord['id'] . '" aria-label="' . str_replace('"','', $ciag_tekstu) . '" title="' . str_replace('"','', $ciag_tekstu) . '">' . $ciag_tekstu;
                if ( isset($rekord['ikona']) && $rekord['ikona'] ) {
                     $wynik .= '<img src="' . KATALOG_ZDJEC . '/'. $rekord['ikona'] . '" alt="Info" />';
                }
                $wynik .= '</label>';
                if ( $rekord['objasnienie'] != '' ) {

                  if ( file_exists('szablony/' . DOMYSLNY_SZABLON . '/obrazki/nawigacja/info_tip.png') ) {
                       $wynik .= '<div class="InfoTip '.$nazwa.'" id="InfoTip_'.$nazwa . '_' . $rekord['id'].'" data-tip-id="'.$nazwa . '_' . $rekord['id'].'"><img src="szablony/' . DOMYSLNY_SZABLON . '/obrazki/nawigacja/info_tip.png" alt="Info" />';
                  } else {
                       $wynik .= '<div class="InfoTip InfoTipBezGrafiki '.$nazwa.'" id="InfoTip_'.$nazwa . '_' . $rekord['id'].'" data-tip-id="'.$nazwa . '_' . $rekord['id'].'">';
                  }                  
                  
                  $wynik .= '<span id="tip_'.$nazwa . '_' . $rekord['id'].'" style="display:none;">'.$rekord['objasnienie'].'</span></div>';
                  
                }
            }
            
            if ( $nazwa == 'rodzaj_wysylki' ) {
                if ( PUNKTY_ODBIORU_STR_KOSZYKA == 'tak' ) {
                 //
                     //
                     if ( $rekord['klasa'] == 'wysylka_inpost' ) {
                          //
                          $wynik .= '<div class="WyborMapy" id="Wysylka' . $rekord['id'] . '">' . wysylka_inpost::potwierdzenie( true ) . '</div>';
                          //
                     }
                     if ( $rekord['klasa'] == 'wysylka_inpost_eko' ) {
                          //
                          $wynik .= '<div class="WyborMapy" id="Wysylka' . $rekord['id'] . '">' . wysylka_inpost_eko::potwierdzenie( true ) . '</div>';
                          //
                     }                     
                     if ( $rekord['klasa'] == 'wysylka_inpost_weekend' ) {
                          //
                          $wynik .= '<div class="WyborMapy" id="Wysylka' . $rekord['id'] . '">' . wysylka_inpost_weekend::potwierdzenie( true ) . '</div>';
                          //
                     }
                     if ( $rekord['klasa'] == 'wysylka_inpost_international' ) {
                          //
                          $wynik .= '<div class="WyborMapy" id="Wysylka' . $rekord['id'] . '">' . wysylka_inpost_international::potwierdzenie( true ) . '</div>';
                          //
                     }
                     if ( $rekord['klasa'] == 'wysylka_dhlparcelshop' ) {
                          //
                          $wynik .= '<div class="WyborMapy" id="Wysylka' . $rekord['id'] . '">' . wysylka_dhlparcelshop::potwierdzenie( true ) . '</div>';
                          //
                     }           
                     if ( $rekord['klasa'] == 'wysylka_dpdpickup' ) {
                          //
                          $wynik .= '<div class="WyborMapy" id="Wysylka' . $rekord['id'] . '">' . wysylka_dpdpickup::potwierdzenie( true ) . '</div>';
                          //
                     }           
                     if ( $rekord['klasa'] == 'wysylka_pocztapunkt' ) {
                          //
                          $wynik .= '<div class="WyborMapy" id="Wysylka' . $rekord['id'] . '">' . wysylka_pocztapunkt::potwierdzenie( true ) . '</div>';
                          //
                     } 
                     if ( $rekord['klasa'] == 'wysylka_paczkaruch' ) {
                          //
                          $wynik .= '<div class="WyborMapy" id="Wysylka' . $rekord['id'] . '">' . wysylka_paczkaruch::potwierdzenie( true ) . '</div>';
                          //
                     }                   
                     if ( $rekord['klasa'] == 'wysylka_glspickup' ) {
                          //
                          $wynik .= '<div class="WyborMapy" id="Wysylka' . $rekord['id'] . '">' . wysylka_glspickup::potwierdzenie( true ) . '</div>';
                          //
                     }                   
                     //
                }
                if ( $rekord['klasa'] == 'wysylka_bliskapaczka' ) {
                          //
                          $wynik .= '<div class="WyborMapy" id="Wysylka' . $rekord['id'] . '">' . wysylka_bliskapaczka::potwierdzenie( true ) . '</div>';
                          //
                }                   
            }        

            $wynik .= '</div>';
            
            // Ceny wysylek i platnosci
            $wynik .= '<div class="ListaCena" id="CenaWysylki' . $rekord['id'] . '">';
            if ( $rekord['wartosc'] > 0 ) {
              $wynik .= $GLOBALS['waluty']->WyswietlFormatCeny($GLOBALS['waluty']->PokazCeneBezSymbolu($rekord['wartosc'],'',true), $_SESSION['domyslnaWaluta']['id'], true, false);
            } else if ( KOSZT_WYSYLKI_PLATNOSCI_ZERO == 'tak' ) {
              $wynik .= ((CENY_MIEJSCA_PO_PRZECINKU == '0') ? '0 ' : '0,00 ') . $_SESSION['domyslnaWaluta']['symbol'];
            }          
            $wynik .= '</div>';

        $wynik .= '</div>';

        if ( isset($rekord['kanaly_platnosci_tekst']) && $rekord['kanaly_platnosci_tekst'] != '') {
            $wynik .= $rekord['kanaly_platnosci_tekst'];
        }

      } else {
      
        if ( $nazwa == 'rodzaj_wysylki' ) {
             //
             $wynik .= '<div class="cmxform" style="margin-left:5px"><label class="error" style="margin:0px">' . $GLOBALS['tlumacz']['BRAK_WSPOLNEJ_WYSYLKI'] . '</label></div>';
             //
          } else if ( $nazwa == 'rodzaj_platnosci' ) {
             //
             $wynik .= '<div class="cmxform" style="margin-left:5px"><label class="error" style="margin:0px">' . $GLOBALS['tlumacz']['BRAK_WSPOLNEJ_PLATNOSCI'] . '</label></div>';
             //
          } else {
             //
             $wynik .= '<div class="cmxform" style="margin-left:5px">---</div>';
             //
        }
        
      }
      
      $i++;
    }
    
    if ( $nazwa == 'rodzaj_wysylki' ) {
      
          $wynik .= "<script>
                     $(document).ready(function() {
                        if ( $('#Wysylka" . $wybrany . "').length ) {
                             $('#Wysylka" . $wybrany . "').find('.WyborPunktuKoszyk').show();
                        }
                     })
                     </script>";
    }

    return $wynik;
  }  
  
  // funkcja sprawdza czy jest wlaczona platnosc po podaniu nazwy klasy - uzywane do niestandardowych platnosci
  public static function  CzyJestWlaczonaPlatnosc($id, $array) {

    if ( isset($array) && !isset($array['0']) ) {
       foreach ($array as $key => $val) {
           if ($val['klasa'] === $id) {
               return $key;
           }
       }
    } else {
      return null;
    }

   return null;
  }
  
  // funkcja do pobierania pliku na karcie produktu
  public static function pobierzPlik($file) {

    if (!is_file($file)) { die("<b>404 Nie ma takiego pliku !</b>"); }

    if ( POBIERANIE_PLIKOW_FUNKCJA == 'wariant A' ) {

        $filename = basename($file);
         
        header('Content-Type: application/force-download');
        header('Content-Length: ' . filesize($file));
        header('Content-Disposition: attachment; filename="'.$filename.'"');
         
        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Cache-Control: private', false);
         
        header('Content-Transfer-Encoding: binary'); 

        $bitrate = 1024; 
         
        $chunkSize = $bitrate * 1024;
         
        $handle = fopen($file, 'rb');
         
        while (!feof($handle)) {
            echo fread($handle, $chunkSize);
         
            ob_flush();
         
            flush();
        }
         
        fclose($handle);
        
        return;
        
    } else {

        $len = filesize($file);
        $filename = basename($file);
        $file_extension = strtolower(substr(strrchr($filename,"."),1));
          
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
         
        header("Content-Type: application/force-download");
         
        $header = "Content-Disposition: attachment; filename=" . $filename . ";";
        header( $header );
        header("Content-Transfer-Encoding: binary");
        header("Content-Length: " . $len);
        
        if (ob_get_level()) {
            ob_end_clean();
        }        
        
        @readfile($file);
        
        return;        
    
    }
    
  }

  // zamienia kropke w liczbie na domyslny separator waluty - uzywane przy pdf
  public static function KropkaPrzecinek( $wartosc, $zera = false ) {
    global $waluty, $zamowienie;
    
    if ( $zera == false ) {
         return str_replace( '.', (string)$waluty->waluty[$zamowienie->info['waluta']]['separator'], (string)$wartosc );
    } else {
         return number_format( $wartosc, 2, $waluty->waluty[$zamowienie->info['waluta']]['separator'], '' );
    }

  }  

  // sprawdzi czy sa jakies hity, polecane z datami - produkty ktore trzeba aktualizowac raz na dzien
  public static function AktualizacjaProduktowJednodniowych( $sql ) {
    //
    $WynikAktualizacji = 0;

    $IloscHitow = 0;
    $IloscPolecanych = 0;
    $IloscOczekiwanych = 0;
    
    if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
    
        while ($info = $sql->fetch_assoc()) {
            //
            if ( Funkcje::czyNiePuste($info['star_date']) || Funkcje::czyNiePuste($info['star_date_end']) ) {
                $IloscHitow++;
            }  
            if ( Funkcje::czyNiePuste($info['featured_date']) || Funkcje::czyNiePuste($info['featured_date']) ) {
                $IloscPolecanych++;
            }
            if ( Funkcje::czyNiePuste($info['products_date_available']) || Funkcje::czyNiePuste($info['products_date_available_end']) ) {
                $IloscOczekiwanych++;
            } 
            //
        }
        
    }
    
    $dataRok = date('Y-m-d');

    // Wylacza lub wlacza nasze hity
    if ( $IloscHitow > 0 ) {
        // wlacza
        $GLOBALS['db']->open_query("UPDATE products SET star_status = '1' WHERE star_status = '0' AND ((star_date < '" . $dataRok . "' AND star_date != '0000-00-00') OR (star_date_end > '" . $dataRok . "' AND star_date_end != '0000-00-00'))");
        $WynikAktualizacji += $GLOBALS['db']->last_query_effect();
        // wylacza
        $GLOBALS['db']->open_query("UPDATE products SET star_status = '0' WHERE star_status = '1' AND ((star_date > '" . $dataRok . "' AND star_date != '0000-00-00') OR (star_date_end < '" . $dataRok . "' AND star_date_end != '0000-00-00'))");
        $WynikAktualizacji += $GLOBALS['db']->last_query_effect();        
    }
    
    // Wylacza lub wlacza polecane
    if ( $IloscPolecanych > 0 ) {
        // wlacza
        $GLOBALS['db']->open_query("UPDATE products SET featured_status = '1' WHERE featured_status = '0' AND ((featured_date < '" . $dataRok . "' AND featured_date != '0000-00-00') OR (featured_date_end > '" . $dataRok . "' AND featured_date_end != '0000-00-00'))");
        $WynikAktualizacji += $GLOBALS['db']->last_query_effect();
        // wylacza
        $GLOBALS['db']->open_query("UPDATE products SET featured_status = '0' WHERE featured_status = '1' AND ((featured_date > '" . $dataRok . "' AND featured_date != '0000-00-00') OR (featured_date_end < '" . $dataRok . "' AND featured_date_end != '0000-00-00'))");
        $WynikAktualizacji += $GLOBALS['db']->last_query_effect();        
    }
    
    // Wylaczenie produktow z data dostepnosci od 
    if ( $IloscOczekiwanych > 0 ) {
        // products_date_added = '" . $dataRok . " 00:00:00', 
        //$GLOBALS['db']->open_query("UPDATE products SET products_date_available = '0000-00-00' WHERE products_status = '1' AND products_date_available <= '" . $dataRok . "' AND products_date_available != '0000-00-00'");
        //$WynikAktualizacji += $GLOBALS['db']->last_query_effect();
        
        // wylacza produkt jezeli data dostepnosci jest wieksza
        $GLOBALS['db']->open_query("UPDATE products SET products_status = '1' WHERE products_status = '0' AND products_date_available < '" . $dataRok . "' AND products_date_available != '0000-00-00' AND products_date_available_status = 1");
        $WynikAktualizacji += $GLOBALS['db']->last_query_effect();           
        // wylacza produkt jezeli data dostepnosci jest wieksza
        $GLOBALS['db']->open_query("UPDATE products SET products_status = '0' WHERE products_status = '1' AND products_date_available > '" . $dataRok . "' AND products_date_available != '0000-00-00' AND products_date_available_status = 1");
        $WynikAktualizacji += $GLOBALS['db']->last_query_effect();
        // wylacza produkt jezeli data dostepnosci zakonczenia jest mniejsza
        $GLOBALS['db']->open_query("UPDATE products SET products_status = '0' WHERE products_status = '1' AND products_date_available_end < '" . $dataRok . "' AND products_date_available_end != '0000-00-00'");
        $WynikAktualizacji += $GLOBALS['db']->last_query_effect();        
    }
    
    // Ustawienia nowosci jezeli sa automatyczne wg dat
    if ( NOWOSCI_USTAWIENIA == 'automatycznie wg daty dodania' ) {
        //
        $GLOBALS['db']->open_query("UPDATE products SET new_status = '0' WHERE new_status = '1'");
        $GLOBALS['db']->open_query("UPDATE products SET new_status = '1' WHERE new_status = '0' AND DATE_SUB(CURDATE(),INTERVAL " . NOWOSCI_ILOSC_DNI . " DAY) <= products_date_added");
        $WynikAktualizacji += $GLOBALS['db']->last_query_effect();        
        //
    }
    
    // jezeli byly zaktualizowane jakies produkty to skasuje cache produktow
    if ( $WynikAktualizacji > 0 ) {
        $GLOBALS['cache']->UsunCacheProduktow();
    }
    
    unset( $dataRok, $info, $WynikAktualizacji, $IloscHitow, $IloscPolecanych, $IloscOczekiwanych);    
    //
  }
  
  public static function WlasnyCron( $iloscMiniut, $aktualnyCron ) {
    //
    $przelicznikSekund = $iloscMiniut * 60;
    $noweSekundy = 0;
    $aktualneSekundy = time();
    if ( ((int)($aktualnyCron / $przelicznikSekund) * $przelicznikSekund) < $aktualneSekundy ) {
          //
          // jezeli czas jest wiecej niz co godzine przelicznik musi byc na godziny
          if ( $przelicznikSekund > 3600 ) {
               //
               $noweSekundy = ((int)($aktualneSekundy / 3600) * 3600) + $przelicznikSekund;
               //
             } else {
               //
               $noweSekundy = ((int)($aktualneSekundy / $przelicznikSekund) * $przelicznikSekund) + $przelicznikSekund;
               //
          }
          //
    }   
    //
    return $noweSekundy;
    //
  }
  
  // funkcja zarzadzania cronami  
  public static function ZarzadzanieCronami() {

    $definicje = array();
    $definicje[1] = array( 'status' => CRON_1_STATUS, 'sekundy' => CRON_1_SEKUNDY, 'godziny' => CRON_1_ILOSC_GODZIN, 'skrypt' => CRON_1_SKRYPT );
    $definicje[2] = array( 'status' => CRON_2_STATUS, 'sekundy' => CRON_2_SEKUNDY, 'godziny' => CRON_2_ILOSC_GODZIN, 'skrypt' => CRON_2_SKRYPT );
    $definicje[3] = array( 'status' => CRON_3_STATUS, 'sekundy' => CRON_3_SEKUNDY, 'godziny' => CRON_3_ILOSC_GODZIN, 'skrypt' => CRON_3_SKRYPT );
    $definicje[4] = array( 'status' => CRON_4_STATUS, 'sekundy' => CRON_4_SEKUNDY, 'godziny' => CRON_4_ILOSC_GODZIN, 'skrypt' => CRON_4_SKRYPT );

    for ( $b = 1; $b < 5; $b++ ) {
    
        if ( $definicje[$b]['status'] == 'tak' ) {
            
            $Czas = Funkcje::WlasnyCron( $definicje[$b]['godziny'] * 60, $definicje[$b]['sekundy'] );
            if ( $Czas > 0 ) {
            
                $c = curl_init( ADRES_URL_SKLEPU . '/harmonogram/' . $definicje[$b]['skrypt'] );
                curl_setopt( $c, CURLOPT_FOLLOWLOCATION, false );
                curl_setopt( $c, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt( $c, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt( $c, CURLOPT_HEADER, false );
                curl_setopt( $c, CURLOPT_RETURNTRANSFER, 1 );
                curl_exec( $c );
                curl_close( $c );    

                $GLOBALS['db']->open_query("UPDATE settings SET value = '" . $Czas . "' WHERE code = 'CRON_" . $b . "_SEKUNDY'");  

                unset($c);
                
            }
            unset($Czas);

        }
    
    }
    
    unset($definicje);
  
  }

  // funkcja zwracajaca domyslny status zamowienia
  public static function PokazDomyslnyStatusZamowienia( $modul_platnosci = '' ) {

    $wynik = '-';
    
    // domyslny status dla konkretnego modulu platnosci
    if ( $modul_platnosci != '' ) {
     
        $zapytanie = "SELECT wartosc FROM modules_payment_params WHERE kod = 'PLATNOSC_" . strtoupper(str_replace('platnosc_', '', (string)$modul_platnosci)) . "_STATUS_ZAMOWIENIA_START'";
        $sql = $GLOBALS['db']->open_query($zapytanie);

        if ( (int)$GLOBALS['db']->ile_rekordow($sql) > 0  ) {
          
             $domyslny_status = $sql->fetch_assoc();
             if ( $domyslny_status > 0 ) {
                  $wynik = $domyslny_status['wartosc'];
             }
             
        }
        
        $GLOBALS['db']->close_query($sql);
        unset($zapytanie);         
      
    }
    
    // domyslny modul platnosci dla calego sklepu
    if ( $modul_platnosci == '' || $wynik == '-' || $wynik == '0' ) {
      
        $zapytanie = "SELECT orders_status_id FROM orders_status WHERE orders_status_default = '1'";
        $sql = $GLOBALS['db']->open_query($zapytanie);
        
        if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {

            while ($domyslny_status = $sql->fetch_assoc()) {
                $wynik = $domyslny_status['orders_status_id'];
            }
            
        }
        
        $GLOBALS['db']->close_query($sql);
        unset($zapytanie);
        
    }

    return $wynik;
  }  

  // funkcja zapisujaca XML do tablicy
  public static function Xml2Array($xml,$main_heading = '') {
  
    $deXml = simplexml_load_string($xml);
    $deJson = json_encode($deXml);
    $xml_array = json_decode($deJson,true);
    if (! empty($main_heading)) {
        $returned = $xml_array[$main_heading];
        return $returned;
    } else {
        return $xml_array;
    }
    
  }

  // funkcja zwracajaca kod ISO kraju o podanym id
  public static function kodISOKrajuDostawy( $kraj_id ) {

    $zapytanie = "SELECT countries_iso_code_2 FROM countries WHERE countries_id = '" . (int)$kraj_id . "'";
    $sql = $GLOBALS['db']->open_query($zapytanie);

    if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
         //
         $info = $sql->fetch_assoc();
         return $info['countries_iso_code_2'];
         //
    } else {
         //
         return ((isset($_SESSION['krajDostawy']['kod'])) ? $_SESSION['krajDostawy']['kod'] : '');
         //
    }

  }

  // funkcja zwracajaca ID kraju o podanym kodzie
  public static function IdKrajuDostawy( $kraj_id ) {

    $zapytanie = "SELECT countries_id FROM countries WHERE countries_iso_code_2 = '" . (string)$kraj_id . "'";
    $sql = $GLOBALS['db']->open_query($zapytanie);

    if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
         //
         $info = $sql->fetch_assoc();
         return $info['countries_id'];
         //
    } else {
         //
         return '170';
         //
    }

  }


  // funkcja zwracajaca tablice aktywnych systemow ratalnych
  public static function AktywneSystemyRatalne() {

    $SystemyRatalne = array();

    // cache zapytania    
    $WynikCache = $GLOBALS['cache']->odczytaj('SystemyRatalne', CACHE_INNE);    
    
    if ( !$WynikCache && !is_array($WynikCache) ) {
          //
          $zapSystemyRatalne = "SELECT p.id, p.klasa, pp.kod, pp.wartosc FROM modules_payment p
                                LEFT JOIN modules_payment_params pp ON p.id = pp.modul_id WHERE (p.status = '1' AND (p.klasa = 'platnosc_santander' OR p.klasa = 'platnosc_lukas' OR p.klasa = 'platnosc_mbank' OR p.klasa = 'platnosc_lukas' OR p.klasa = 'platnosc_payu' OR p.klasa = 'platnosc_payu_rest' OR p.klasa = 'platnosc_bgz' OR p.klasa = 'platnosc_ileasing' OR p.klasa = 'platnosc_iraty' OR p.klasa = 'platnosc_comfino' OR p.klasa = 'platnosc_transferuj' OR p.klasa = 'platnosc_tpay' OR p.klasa = 'platnosc_leaselink')) OR p.klasa = 'platnosc_paypo'";

          $sql = $GLOBALS['db']->open_query($zapSystemyRatalne);
          
          if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
              //
              while ($info = $sql->fetch_assoc()) {
                  $SystemyRatalne[$info['klasa']][$info['kod']] = $info['wartosc'];      
              }
              //
              unset($info);
              //
          }
          if ( isset($SystemyRatalne['platnosc_payu']) && $SystemyRatalne['platnosc_payu']['PLATNOSC_PAYU_RATY_WLACZONE'] == 'nie' && $SystemyRatalne['platnosc_payu']['PLATNOSC_PAYU_RATY_KALKULATOR'] == 'nie' ) {
               unset($SystemyRatalne['platnosc_payu']);
          }
          if ( isset($SystemyRatalne['platnosc_payu_rest']) && $SystemyRatalne['platnosc_payu_rest']['PLATNOSC_PAYU_REST_RATY_WLACZONE'] == 'nie' && $SystemyRatalne['platnosc_payu_rest']['PLATNOSC_PAYU_REST_RATY_KALKULATOR'] == 'nie' ) {
               unset($SystemyRatalne['platnosc_payu_rest']);
          }
          if ( isset($SystemyRatalne['platnosc_transferuj']) && $SystemyRatalne['platnosc_transferuj']['PLATNOSC_TPAY_RATY_KALKULATOR'] == 'nie' ) {
               unset($SystemyRatalne['platnosc_transferuj']);
          }          
          if ( isset($SystemyRatalne['platnosc_tpay']) && $SystemyRatalne['platnosc_tpay']['PLATNOSC_TPAY_REST_RATY_KALKULATOR'] == 'nie' ) {
               unset($SystemyRatalne['platnosc_tpay']);
          }          
          if ( isset($SystemyRatalne['platnosc_paypo']) && $SystemyRatalne['platnosc_paypo']['PLATNOSC_PAYPO_GRAFIKA'] == 'nie' ) {
               unset($SystemyRatalne['platnosc_paypo']);
          }        
          //
          $GLOBALS['db']->close_query($sql);
          //
          $GLOBALS['cache']->zapisz('SystemyRatalne', $SystemyRatalne, CACHE_INNE);  
          //        
          unset($zapSystemyRatalne, $info, $sql);    
          //
    } else {
          //
          $SystemyRatalne = $WynikCache;
          //
    }
    
    unset($WynikCache, $zapSystemyRatalne);    

    return $SystemyRatalne; 

  }


  // funkcja zwracajaca kod wojewodztwa
  public static function kodWojewodztwa($panstwo_id, $wojewodztwo_id, $kod_domyslny) {

    $zapytanie = "SELECT zone_code FROM zones WHERE zone_id = '" . (int)$wojewodztwo_id . "'";
    $sql = $GLOBALS['db']->open_query($zapytanie);

    if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
         //
         $info = $sql->fetch_assoc();
         //
         $GLOBALS['db']->close_query($sql);
         //
         return $info['zone_code'];
         //
    } else {
         //
         $GLOBALS['db']->close_query($sql);
         //
         return $kod_domyslny;
         //
    }
    
  }

  // sprawdza czy katalog jest pusty
  public static function czyFolderJestPusty( $folderName ){
    //
    $files = array ();
    
    if ( $handle = opendir ( $folderName ) ) {
      
        while ( false !== ( $file = readdir ( $handle ) ) ) {
            if ( $file != "." && $file != ".." && $file != "js" && $file != "index.php" ) {
                $files [] = $file;
            }
        }
        
        closedir ( $handle );
        
    }
    
    unset($folderName);
    return ( count ( $files ) > 0 ) ? true: false;
    //
  }

  // dzieli ciag uzywany w polach tekstowych produktow
  public static function serialCiag( $ciag ) {
    //
    $ciag = str_replace('{#{', '', stripslashes((string)$ciag));
    $PodzialGlowny = explode('}#}', (string)$ciag);
    $TablicaWynikowa = array();
    //
    foreach ( $PodzialGlowny as $Pole ) {
        //
        $PodPodzial = explode('|*|', (string)$Pole);
        if ( count($PodPodzial) == 3 ) {
            $TablicaWynikowa[] = array( 'nazwa' => $PodPodzial[0],
                                        'tekst' => $PodPodzial[1],
                                        'typ'   => $PodPodzial[2] );
        }
        unset($PodPodzial);
        //
    }
    //
    unset($ciag, $PodzialGlowny);
    //
    return $TablicaWynikowa;
  }

  // funkcja rozbijająca adres na elementy *************************************************************
  public static function PrzeksztalcAdres( $adres ){

    $number = '';
    $adres = str_replace(',', '', (string)$adres);
    $adres = str_replace('"', '', (string)$adres);
    $adres = str_replace('*', '', (string)$adres);

    $wzorzec        = '#([\p{L}\w\-.\' ]+) ([0-9a-zA-Z/.\' ]+)$#u';

    $adres = trim((string)$adres);
    $adres = str_replace(' m ', 'm', (string)$adres);
    $adres = str_replace(' / ', '/', (string)$adres);

    $matchResult    = preg_match($wzorzec, $adres, $aMatch);

    $ulica         = (isset($aMatch[1])) ? $aMatch[1] : '';

    $numer_domu           = (isset($aMatch[2])) ? $aMatch[2] : '';

    if ( $numer_domu != '' ) {
        //format Ulica XX
        if ( preg_match('#([0-9]{1,3})$#', $numer_domu) ) {
            $number = $numer_domu;
        }
        //format Ulica XXa
        if ( preg_match('#([0-9]{1,3}[a-zA-Z]{1})$#', $numer_domu) ) {
            $number = $numer_domu;
        }
        //format Ulica XXa/XX
        if ( preg_match('#([0-9]{1,3}[a-zA-Z]{1}/[0-9]{1,3})$#', $numer_domu) ) {
            $number = $numer_domu;
        }
        //format Ulica XX/XX
        if ( preg_match('#([0-9]{1,3}/[0-9]{1,3})$#', $numer_domu) ) {
            $number = $numer_domu;
        }
        //format Ulica XX/XX cos dalej
        if ( preg_match('#^([0-9]{1,3}/[0-9]{1,3})#', $numer_domu) ) {
            $number = $numer_domu;
        }
    }

    return array('ulica'=> $ulica,
                 'dom'=> $number
                );

  }

  // funkcja rozbijająca adres na elementy *************************************************************
  public static function PrzeksztalcAdresDomu( $adres ){

    if ( $adres != '' ) {

        $adres = trim((string)$adres);

        //format Ulica XX
        if ( preg_match('#([0-9]{1,3})$#', $adres) ) {
            $adres_klienta = explode('/', (string)$adres );
        }
        //format Ulica XXa
        if ( preg_match('#([0-9]{1,3}[a-zA-Z]{1})$#', $adres) ) {
            $adres_klienta = explode('/', (string)$adres );
        }

        //format Ulica XXa/XX
        if ( preg_match('#([0-9]{1,3}[a-zA-Z]{1}/[0-9]{1,3})$#', $adres) ) {
            $adres_klienta = explode('/', (string)$adres );
        }
        //format Ulica XX/XX
        if ( preg_match('#([0-9]{1,3}/[0-9]{1,3})$#', $adres) ) {
            $adres_klienta = explode('/', (string)$adres );
        }
        //format Ulica XX/XX cos dalej
        if ( preg_match('#^([0-9]{1,3}/[0-9]{1,3})#', $adres) ) {
            $adres_klienta = explode('/', (string)$adres );
        }

        //format Ulica XXa m XX
        if ( preg_match('#([0-9]{1,3}[a-zA-Z]{1}m[0-9]{1,3})$#', $adres) ) {
            $adres_klienta = explode('m', (string)$adres );
        }
        //format Ulica XX m XX
        if ( preg_match('#([0-9]{1,3}m[0-9]{1,3})$#', $adres) ) {
            $adres_klienta = explode('m', (string)$adres );
        }
        //format Ulica XX m XX cos dalej
        if ( preg_match('#^([0-9]{1,3}m[0-9]{1,3})#', $adres) ) {
            $adres_klienta = explode('m', (string)$adres );
        }

        if (count($adres_klienta) > 1 ) {
          $numer_domu = array_reverse($adres_klienta);
          unset($adres_klienta[count($adres_klienta) - 1]);//usuwam ostatni element
          $adres_klienta = implode(' ', (array)$adres_klienta);
        } else {
          $adres_klienta = implode(' ', (array)$adres_klienta);
          $numer_domu[0] = '';
        }


        return array('dom'=> $adres_klienta,
                     'mieszkanie'=> ( isset($numer_domu[0]) ? $numer_domu[0] : '' )
                    );
    } else {

        return array('dom'=> '',
                     'mieszkanie'=> ''
                    );

    }

  }

  // funkcja rozbijająca adres na elementy *************************************************************
  public static function rozbijAdres($adres) {
    $adres = trim($adres);
    $adres = preg_replace('/\s+/', ' ', $adres);
    $adres = str_ireplace(['\\', 'lokal', 'lok.', 'mieszkanie'], ['/', 'm', 'm', 'm'], $adres);
    $adres = preg_replace('/^(ul\.?|ulica)\s+/iu', '', $adres);

    $pattern = '/
        ^(?P<ulica>[\p{L}\p{N}\s\.\-\'’]+?)       # Nazwa ulicy: litery, cyfry, spacje, znaki
        \s+
        (?P<nr_domu>\d{1,4}[a-zA-Z]?)             # Numer domu
        (?:\s*(?:\/|m|l\.?)\s*(?P<nr_mieszkania>\d{1,4}))?   # Numer mieszkania
        $/xu';

    if (preg_match($pattern, $adres, $matches)) {
        return [
            'ulica' => trim($matches['ulica']),
            'numer_domu' => $matches['nr_domu'],
            'numer_mieszkania' => isset($matches['nr_mieszkania']) ? $matches['nr_mieszkania'] : null
        ];
    } else {
        return null;
    }
  }

  // sprawdza czy wpisano poprawnie nr telefonu komorkowego
  public static function CzyNumerGSM($numer) {
    $wynik = false;

    $numer_telefonu = preg_replace('/\W/', '', (string)$numer); //zostawiamy same cyfry

    //sprawdzamy czy sa tylko cyfry
    if (!is_numeric($numer_telefonu)) {
      $wynik = false;
    }

    $telefony = '';
    $table_telefony = preg_split("/[,]/" , (string)SMS_PRZEDROSTKI);
    $size = count($table_telefony);
    for ($i=0, $n=$size; $i<$n; $i++) {
      $telefony .= $table_telefony[$i].'[0-9]';
      if ( $i<$n-1 ) {
        $telefony .= '|';
      }
    }

    if (preg_match("/^0?(48)?.?(".$telefony.")[0-9]{6}$/i",$numer_telefonu)) {
      $wynik = true;
    }
    return $wynik;
  }  

  // usuniecie wszelkiego formatowania z tekstu opisu i przyciecie do 255 znakow
  public static function UsunFormatowanie($tekst) {

      $wynik = $tekst;

      $wynik = stripslashes((string)$wynik);
      $wynik = strip_tags((string)$wynik,'<br>');

      $wynik = preg_replace('/<br[^>]*>/', ' ', (string)$wynik);
      $wynik = preg_replace('/[\ ]+/', ' ', (string)$wynik);

      $patterns = array("/\s+/", "/\s([?.!])/");
      $replacer = array(" ","$1");

      $wynik = preg_replace( $patterns, $replacer, (string)$wynik );

      $wynik = substr((string)$wynik, 0, strrpos(substr((string)$wynik, 0, 255), " "));

      return $wynik;
  }  
  
  // funkcja potrzebna do stworzenia kombinacji cech
  public static function Permutations(array $tablica, $inb = false) {
    
    switch (count($tablica)) {
        case 1:
            return $tablica[0];
            break;
        case 0:
            throw new InvalidArgumentException('Requires at least one array');
            break;
    }

    $klucz = array_keys($tablica);

    $a = array_shift($tablica);
    $k = array_shift($klucz);

    $b = Funkcje::Permutations($tablica, 'recursing');

    $return = array();
    foreach ($a as $v) {
        if ($v) {
            foreach ($b as $v2) {
                if (!is_array($v2)) $v2 = array($v2);
                if ($inb == 'recursing') {
                    $return[] = array_merge(array($v), (array) $v2);
                } else {
                    $return[] = array($k => $v) + array_combine($klucz, $v2);
                }
            }
        }
    }

    return $return;
    
  }    
  
  // funkcja do wyrazenia ragularnego do pl znakow przy wyszukiwaniu
  public static function ZamienPlZnaki($str){
    $ciag_pierwotny = array('e', 'o', 'a', 's', 'l', 'z', 'c', 'n', 'E', 'O', 'A', 'S', 'L', 'Z', 'C', 'N');
    $wynik = array('(e|ę)', '(o|ó)', '(a|ą)', '(s|ś)', '(l|ł)', '(z|ż|ź)', '(c|ć)', '(n|ń)', '(E|Ę)', '(O|Ó)', '(A|Ą)', '(S|Ś)', '(L|Ł)', '(Z|Ż|Ź)', '(C|Ć)', '(N|Ń)');
    return str_replace($ciag_pierwotny, $wynik, (string)$str);
  }  
  
  // zastepuje strpos
  public static function PregPos( $subject, $regex ) {
    if ( preg_match( '/^(.*?)'.$regex.'/', $subject, $matches ) ) {
        return mb_strlen((string)$matches[ 1 ]);
    }
    return false;
  }

    // funkcja do odmiany przez przypadki osob w zaleznosci od ilosci
  public static function OdmianaPrzypadkowOsoby($ile) {

    $r1 = round($ile, 0, PHP_ROUND_HALF_UP) % 100;
    if ($r1 == 1 && $ile < 100) {
        $osoba = $GLOBALS['tlumacz']['ODMIANA_OSOBA']; 
        $kupno = $GLOBALS['tlumacz']['ODMIANA_KUPILA'];
    } else {
        $r2=$r1 % 10;
        if (($r2 > 1 && $r2 < 5) && ($r1 < 12 || $r1 > 14)) {
            $osoba = $GLOBALS['tlumacz']['ODMIANA_OSOBY']; 
            $kupno = $GLOBALS['tlumacz']['ODMIANA_KUPILY'];
        } else {
            $osoba = $GLOBALS['tlumacz']['ODMIANA_OSOB']; 
            $kupno = $GLOBALS['tlumacz']['ODMIANA_KUPILO'];
        }
    }
    return array($osoba,$kupno);
  }

    // funkcja do odmiany przez przypadki produktow w zaleznosci od ilosci
  public static function OdmianaPrzypadkowProdukty($ile) {

    $r1 = round($ile, 0, PHP_ROUND_HALF_UP) % 100;
    if ($r1 == 1 && $ile < 100) {
        $produkty = $GLOBALS['tlumacz']['ODMIANA_PRODUKT'];
    } else {
        $r2=$r1 % 10;
        if (($r2 > 1 && $r2 < 5) && ($r1 < 12 || $r1 > 14)) {
            $produkty = $GLOBALS['tlumacz']['ODMIANA_PRODUKTY'];
        } else {
            $produkty = $GLOBALS['tlumacz']['ODMIANA_PRODUKTOW'];
        }
    }
    return array($produkty);
  }

  public static function CzySaParametry( $TablicaGet = array() ) {

    $SaParametry = false;

    if ( isset($TablicaGet) && count($TablicaGet) > 0 ) {
    
        foreach ($TablicaGet as $key => $value) {
          if ( $key == 'ceno' ) {
              $SaParametry = true;
              break;
          }
          if ( $key == 'cend' ) {
              $SaParametry = true;
              break;
          }
          if ( $key == 'producent' ) {
              $SaParametry = true;
              break;
          }
          if ( $key == 'nowosci' ) {
              $SaParametry = true;
              break;
          }
          if ( $key == 'promocje' ) {
              $SaParametry = true;
              break;
          }
          if ( $key == 'dostepnosc' ) {
              $SaParametry = true;
              break;
          }
          if ( $key == 'wysylka' ) {
              $SaParametry = true;
              break;
          }          
          if ( $key == 'kategoria' ) {
              $SaParametry = true;
              break;
          }
          if (preg_match("/p([0-9])$/", $key)) {
              $SaParametry = true;
              break;
          }
          if (preg_match("/c([0-9])$/", $key)) {
              $SaParametry = true;
              break;
          }
        }

    }

    return $SaParametry;

  }
  
  public static function TrimBr( $ciag = '' ) {
    
     $ciag = preg_replace('/^\s*(?:<br\s*\/?>\s*)*/i', '', (string)$ciag);
     $ciag = preg_replace('/\s*(?:<br\s*\/?>\s*)*$/i', '', (string)$ciag);
     
     return $ciag;
    
  }

  public static function WyswietlIkony($ikony) {
    $Ikona = '';
    if (count($ikony) > 0 && IKONY_NA_ZDJECIACH == 'tak') {
        //
        $TablicaIkon = array();
        $TablicaIkonSortowania = explode(',',(string) IKONY_NA_ZDJECIACH_SORTOWANIE);
        $TablicaSort = array();
        //
        foreach ( $TablicaIkonSortowania as $Tmp ) {
            //
            $TmpPodzial = explode(':', (string)$Tmp);
            //
            if ( count($TmpPodzial) == 2 ) {
                 //
                 $TablicaSort[ $TmpPodzial[0] ] = $TmpPodzial[1];
                 //
            }
            //
            unset($TmpPodzial);
            //
        }
        //
        unset($TablicaIkonSortowania);
        //
        if ($ikony['nowosc'] == '1' && IKONY_NA_ZDJECIACH_NOWOSCI == 'tak') {
            $TablicaIkon[ ((isset($TablicaSort['nowosc'])) ? (int)$TablicaSort['nowosc'] : '') ] = '<span class="IkonaNowosc Ikona"><b>' . $GLOBALS['tlumacz']['IKONKA_ZDJECIE_NOWOSC'] . '</b></span>';
        }
        if ($ikony['promocja'] == '1' && IKONY_NA_ZDJECIACH_PROMOCJE == 'tak') {            
            //
            $ProcentPromocja = '';
            if ( IKONY_NA_ZDJECIACH_PROMOCJE_PLUS_PROCENT == 'tak' ) {
                 $ProcentPromocja = ' <span>-' . $ikony['promocja_procent'] . '%</span>';
            }
            $TablicaIkon[ ((isset($TablicaSort['promocja'])) ? (int)$TablicaSort['promocja'] : '') ] = '<span class="IkonaPromocja Ikona"><b>' . $GLOBALS['tlumacz']['IKONKA_ZDJECIE_PROMOCJA'] . $ProcentPromocja . '</b></span>';
            unset($ProcentPromocja);
            //
        }
        if ($ikony['promocja'] == '1' && IKONY_NA_ZDJECIACH_PROMOCJE_PROCENT == 'tak' && isset($ikony['promocja_procent']) && $ikony['promocja_procent'] > 0) {
            $TablicaIkon[ ((isset($TablicaSort['promocja_ikona'])) ? (int)$TablicaSort['promocja_ikona'] : '') ] = '<span class="IkonaPromocjaProcent Ikona"><b>-' . $ikony['promocja_procent'] . '%</b></span>';
        }              
        if ($ikony['polecany'] == '1' && IKONY_NA_ZDJECIACH_POLECANE == 'tak') {
            $TablicaIkon[ ((isset($TablicaSort['polecany'])) ? (int)$TablicaSort['polecany'] : '') ] = '<span class="IkonaPolecany Ikona"><b>' . $GLOBALS['tlumacz']['IKONKA_ZDJECIE_POLECANY'] . '</b></span>';
        }        
        if ($ikony['hit'] == '1' && IKONY_NA_ZDJECIACH_NASZ_HIT == 'tak') {
            $TablicaIkon[ ((isset($TablicaSort['hit'])) ? (int)$TablicaSort['hit'] : '') ] = '<span class="IkonaHit Ikona"><b>' . $GLOBALS['tlumacz']['IKONKA_ZDJECIE_HIT'] . '</b></span>';
        }
        if ($ikony['darmowa_dostawa'] == '1' && IKONY_NA_ZDJECIACH_DOSTAWA == 'tak') {
            $TablicaIkon[ ((isset($TablicaSort['wysylka_gratis'])) ? (int)$TablicaSort['wysylka_gratis'] : '') ] = '<span class="IkonaDostawa Ikona"><b>' . $GLOBALS['tlumacz']['IKONKA_ZDJECIE_DARMOWA_DOSTAWA'] . '</b></span>';
        }
        if ($ikony['wyprzedaz'] == '1' && IKONY_NA_ZDJECIACH_WYPRZEDAZ == 'tak') {
            $TablicaIkon[ ((isset($TablicaSort['wyprzedaz'])) ? (int)$TablicaSort['wyprzedaz'] : '') ] = '<span class="IkonaWyprzedaz Ikona"><b>' . $GLOBALS['tlumacz']['IKONKA_ZDJECIE_WYPRZEDAZ'] . '</b></span>';
        }                
        if ($ikony['rabat'] == '1' && IKONY_NA_ZDJECIACH_RABAT == 'tak') {
            $TablicaIkon[ ((isset($TablicaSort['rabat'])) ? (int)$TablicaSort['rabat'] : '') ] = '<span class="IkonaRabat Ikona"><b>' . $GLOBALS['tlumacz']['IKONKA_ZDJECIE_RABAT'] . ' ' . (($ikony['rabat_wartosc'] != '0') ? '<span>' . $ikony['rabat_wartosc'] . '%</span>' : '') . '</b></span>';
        } 
        if ($ikony['cena_specjalna'] == '1' && IKONY_NA_ZDJECIACH_CENA_SPECJALNA == 'tak') {
            $TablicaIkon[ ((isset($TablicaSort['cena_specjalna'])) ? (int)$TablicaSort['cena_specjalna'] : '') ] = '<span class="IkonaCenaSpecjalna Ikona"><b>' . $GLOBALS['tlumacz']['IKONKA_ZDJECIE_CENA_SPECJALNA'] . '</b></span>';
        }   

        $TablicaOpcje = array(array('nr' => 1, 'aktywne' => IKONY_NA_ZDJECIACH_DODATKOWA_1, 'nazwa' => IKONY_NA_ZDJECIACH_DODATKOWA_NAZWA_1, 'kolor' => IKONY_NA_ZDJECIACH_DODATKOWA_KOLOR_1),
                              array('nr' => 2, 'aktywne' => IKONY_NA_ZDJECIACH_DODATKOWA_2, 'nazwa' => IKONY_NA_ZDJECIACH_DODATKOWA_NAZWA_2, 'kolor' => IKONY_NA_ZDJECIACH_DODATKOWA_KOLOR_2),
                              array('nr' => 3, 'aktywne' => IKONY_NA_ZDJECIACH_DODATKOWA_3, 'nazwa' => IKONY_NA_ZDJECIACH_DODATKOWA_NAZWA_3, 'kolor' => IKONY_NA_ZDJECIACH_DODATKOWA_KOLOR_3),
                              array('nr' => 4, 'aktywne' => IKONY_NA_ZDJECIACH_DODATKOWA_4, 'nazwa' => IKONY_NA_ZDJECIACH_DODATKOWA_NAZWA_4, 'kolor' => IKONY_NA_ZDJECIACH_DODATKOWA_KOLOR_4),
                              array('nr' => 5, 'aktywne' => IKONY_NA_ZDJECIACH_DODATKOWA_5, 'nazwa' => IKONY_NA_ZDJECIACH_DODATKOWA_NAZWA_5, 'kolor' => IKONY_NA_ZDJECIACH_DODATKOWA_KOLOR_5));
    
        foreach ( $TablicaOpcje as $Tmp ) {
            //
            if ( $Tmp['aktywne'] == 'tak' ) {
                 //
                 if ( $ikony['ikona_' . $Tmp['nr']] == '1' ) {
                      //
                      if ( isset($GLOBALS['nazwy_ikonek'][$Tmp['nr']][$_SESSION['domyslnyJezyk']['id']]) ) {
                           $TablicaIkon[ ((isset($TablicaSort['ikona_' . $Tmp['nr']])) ? (int)$TablicaSort['ikona_' . $Tmp['nr']] : '') ] = '<span class="Ikona_' . $Tmp['nr'] . ' Ikona"><b style="background-color:#' . $Tmp['kolor'] . '">' . $GLOBALS['nazwy_ikonek'][$Tmp['nr']][$_SESSION['domyslnyJezyk']['id']] . '</b></span>';
                      }
                      //
                 }           
                 //
            }
            //
        }
        
        ksort($TablicaIkon);

        if ( IKONY_NA_ZDJECIACH_ILOSC == 'jedna' ) {
             //
             $PierwszaIkona = '';
             foreach ( $TablicaIkon as $Tmp ) {
                 $PierwszaIkona = $Tmp;
                 break;
             }
             //
             $Ikona = $PierwszaIkona;
             //
             unset($PierwszaIkona);
             //
        } else {
             //
             $Ikona = implode(' ', (array)$TablicaIkon); 
             //
        }
        //
        if ( trim((string)$Ikona) != '' ) {
            $Ikona = '<span class="IkonkiProduktu">' . $Ikona . '</span>';
        }
        //
        
        return $Ikona;
    }

  }

  // wgrywanie plikow: recenzje/reklamacj
  public static function WgrajPlik( $plik, $dlugosc = 80, $prefix = '' ) {
      //
      $ciag = (string)$prefix;
      $blad = false;
      //
      while (strlen((string)$ciag) < $dlugosc) {
          //
          $char = chr(rand(0,255));
          if (preg_match('/^[a-z0-9]$/i', $char)) {
              $ciag .= $char;
          }
          //
      }
      //
      if (is_array($plik) && count($plik) > 0) {
          //
          if (!empty($plik['error'])) {
          
              $blad = 1;
              
          } else if (empty($plik['tmp_name']) || $plik['tmp_name'] == 'none') {
          
              $blad = 2;                
              
          } else if ( @filesize($plik['tmp_name']) > 3145728) {
          
              $blad = true;                

          }
          
          if ($blad == false) {
              //
              $ex = pathinfo($plik['name']);
              $rozszerzenie = strtolower((string)$ex['extension']);
              //
              $moznaWgrac = false;
              //
              if ( $rozszerzenie == 'jpg' || $rozszerzenie == 'jpeg' || $rozszerzenie == 'png' || $rozszerzenie == 'webp' ) {
                   $moznaWgrac = true;
              }
              //
              if ($moznaWgrac == true) {                  
                  //                     
                  if ( file_exists(KATALOG_SKLEPU . '/grafiki_inne/' . $ciag . '.' . $rozszerzenie) ) {
                       
                       $blad = true;
                       
                  } else {
                    
                       $mime_typ = '';
      
                       $info = getimagesize($plik['tmp_name']);
                       $mime = ((isset($info['mime'])) ? $info['mime'] : '');

                       switch ($mime) {
                                case 'image/jpeg':
                                      $mime_typ = 'jpeg';
                                      break;
                                case 'image/png':
                                      $mime_typ = 'png';
                                      break;
                                case 'image/webp':
                                      $mime_typ = 'webp';
                                      break;
                       }
                  
                       if ( $mime_typ != '' ) {
                  
                            move_uploaded_file($plik['tmp_name'], KATALOG_SKLEPU . '/grafiki_inne/' . $ciag . '.' . $rozszerzenie);
                       
                            $ciag = $ciag . '.' . $rozszerzenie;
                        
                            return $ciag;
                            
                       } else {
                         
                            return '';
                            
                       }
                       
                  }
                  //
                } else {
                  //
                  $blad = true;                            
                  //
              }
              
          }
          
          return '';

      }   
      //
      return '';
      //
  }

  // generowanie ciagu w formacie UUID
  public static function UUIDv4() {

    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
    // 32 bits for "time_low"
    mt_rand(0, 0xffff), mt_rand(0, 0xffff),

    // 16 bits for "time_mid"
    mt_rand(0, 0xffff),

    // 16 bits for "time_hi_and_version",
    // four most significant bits holds version number 4
    mt_rand(0, 0x0fff) | 0x4000,

    // 16 bits, 8 bits for "clk_seq_hi_res",
    // 8 bits for "clk_seq_low",
    // two most significant bits holds zero and one for variant DCE1.1
    mt_rand(0, 0x3fff) | 0x8000,

    // 48 bits for "node"
    mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
  }
  
  // tablica pastw wg iso
  public static function KrajeIso() {
    
      $kraje = array(
          "Afganistan" => "AF",
          "Albania" => "AL",
          "Algieria" => "DZ",
          "Andora" => "AD",
          "Angola" => "AO",
          "Antigua i Barbuda" => "AG",
          "Arabia Saudyjska" => "SA",
          "Argentyna" => "AR",
          "Armenia" => "AM",
          "Australia" => "AU",
          "Austria" => "AT",
          "Azerbejdżan" => "AZ",
          "Bahamy" => "BS",
          "Bahrajn" => "BH",
          "Bangladesz" => "BD",
          "Barbados" => "BB",
          "Belgia" => "BE",
          "Belize" => "BZ",
          "Benin" => "BJ",
          "Bhutan" => "BT",
          "Białoruś" => "BY",
          "Boliwia" => "BO",
          "Bośnia i Hercegowina" => "BA",
          "Botswana" => "BW",
          "Brazylia" => "BR",
          "Brunei" => "BN",
          "Bułgaria" => "BG",
          "Burkina Faso" => "BF",
          "Burundi" => "BI",
          "Chile" => "CL",
          "Chiny" => "CN",
          "Chorwacja" => "HR",
          "Cypr" => "CY",
          "Czarnogóra" => "ME",
          "Czechy" => "CZ",
          "Dania" => "DK",
          "Demokratyczna Republika Konga" => "CD",
          "Dominika" => "DM",
          "Dominikana" => "DO",
          "Dżibuti" => "DJ",
          "Egipt" => "EG",
          "Ekwador" => "EC",
          "Erytrea" => "ER",
          "Estonia" => "EE",
          "Eswatini" => "SZ",
          "Etiopia" => "ET",
          "Fidżi" => "FJ",
          "Filipiny" => "PH",
          "Finlandia" => "FI",
          "Francja" => "FR",
          "Gabon" => "GA",
          "Gambia" => "GM",
          "Ghana" => "GH",
          "Grecja" => "GR",
          "Grenada" => "GD",
          "Gruzja" => "GE",
          "Gujana" => "GY",
          "Guernsey" => "GG",
          "Gwatemala" => "GT",
          "Gwinea" => "GN",
          "Gwinea Bissau" => "GW",
          "Gwinea Równikowa" => "GQ",
          "Haiti" => "HT",
          "Hiszpania" => "ES",
          "Holandia" => "NL",
          "Honduras" => "HN",
          "Indie" => "IN",
          "Indonezja" => "ID",
          "Irak" => "IQ",
          "Iran" => "IR",
          "Irlandia" => "IE",
          "Islandia" => "IS",
          "Izrael" => "IL",
          "Jamajka" => "JM",
          "Japonia" => "JP",
          "Jemen" => "YE",
          "Jordania" => "JO",
          "Kambodża" => "KH",
          "Kamerun" => "CM",
          "Kanada" => "CA",
          "Katar" => "QA",
          "Kazachstan" => "KZ",
          "Kenia" => "KE",
          "Kirgistan" => "KG",
          "Kiribati" => "KI",
          "Kolumbia" => "CO",
          "Komory" => "KM",
          "Kongo" => "CG",
          "Korea Południowa" => "KR",
          "Korea Północna" => "KP",
          "Kostaryka" => "CR",
          "Kuba" => "CU",
          "Kuwejt" => "KW",
          "Laos" => "LA",
          "Lesotho" => "LS",
          "Liban" => "LB",
          "Liberia" => "LR",
          "Libia" => "LY",
          "Liechtenstein" => "LI",
          "Litwa" => "LT",
          "Luksemburg" => "LU",
          "Łotwa" => "LV",
          "Macedonia Północna" => "MK",
          "Madagaskar" => "MG",
          "Malawi" => "MW",
          "Malediwy" => "MV",
          "Malezja" => "MY",
          "Mali" => "ML",
          "Malta" => "MT",
          "Maroko" => "MA",
          "Mauretania" => "MR",
          "Mauritius" => "MU",
          "Meksyk" => "MX",
          "Mikronezja" => "FM",
          "Mołdawia" => "MD",
          "Monako" => "MC",
          "Mongolia" => "MN",
          "Mozambik" => "MZ",
          "Myanmar" => "MM",
          "Namibia" => "NA",
          "Nauru" => "NR",
          "Nepal" => "NP",
          "Niemcy" => "DE",
          "Niger" => "NE",
          "Nigeria" => "NG",
          "Nikaragua" => "NI",
          "Norwegia" => "NO",
          "Nowa Zelandia" => "NZ",
          "Oman" => "OM",
          "Pakistan" => "PK",
          "Palau" => "PW",
          "Panama" => "PA",
          "Papua-Nowa Gwinea" => "PG",
          "Paragwaj" => "PY",
          "Peru" => "PE",
          "Polska" => "PL",
          "Portugalia" => "PT",
          "Republika Czeska" => "CZ",
          "Republika Południowej Afryki" => "ZA",
          "Republika Środkowoafrykańska" => "CF",
          "Republika Zielonego Przylądka" => "CV",
          "Rosja" => "RU",
          "Rumunia" => "RO",
          "Rwanda" => "RW",
          "Saint Kitts i Nevis" => "KN",
          "Saint Lucia" => "LC",
          "Saint Vincent i Grenadyny" => "VC",
          "Salwador" => "SV",
          "Samoa" => "WS",
          "San Marino" => "SM",
          "Senegal" => "SN",
          "Serbia" => "RS",
          "Seszele" => "SC",
          "Sierra Leone" => "SL",
          "Singapur" => "SG",
          "Słowacja" => "SK",
          "Słowenia" => "SI",
          "Somalia" => "SO",
          "Sri Lanka" => "LK",
          "Stany Zjednoczone" => "US",
          "Suazi" => "SZ",
          "Sudan" => "SD",
          "Sudan Południowy" => "SS",
          "Surinam" => "SR",
          "Syria" => "SY",
          "Szwajcaria" => "CH",
          "Szwecja" => "SE",
          "Tadżykistan" => "TJ",
          "Tajlandia" => "TH",
          "Tanzania" => "TZ",
          "Tajwan" => "TW",
          "Timor Wschodni" => "TL",
          "Togo" => "TG",
          "Tonga" => "TO",
          "Trynidad i Tobago" => "TT",
          "Tunezja" => "TN",
          "Turcja" => "TR",
          "Turkmenistan" => "TM",
          "Tuvalu" => "TV",
          "Uganda" => "UG",
          "Ukraina" => "UA",
          "Urugwaj" => "UY",
          "Uzbekistan" => "UZ",
          "Vanuatu" => "VU",
          "Watykan" => "VA",
          "Wenezuela" => "VE",
          "Węgry" => "HU",
          "Wielka Brytania" => "GB",
          "Wietnam" => "VN",
          "Włochy" => "IT",
          "Wybrzeże Kości Słoniowej" => "CI",
          "Wyspy Marshalla" => "MH",
          "Wyspy Salomona" => "SB",
          "Wyspy Świętego Tomasza i Książęca" => "ST",
          "Zambia" => "ZM",
          "Zimbabwe" => "ZW",
          "Zjednoczone Emiraty Arabskie" => "AE"
      );
    
      return $kraje;
      
  }
  
  
}
?>