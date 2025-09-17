<?php
//
define('POKAZ_ILOSC_ZAPYTAN', false);
//
require_once('ustawienia/ustawienia_db.php');
//      
include 'klasy/Bazadanych.php';
$db = new Bazadanych();

if ( isset($_GET['aukcja']) ) {

    $podziel = explode(',', (string)$_GET['aukcja']);
    
    if ( count($podziel) == 2 && (int)$podziel[0] > 0 && strpos((string)$podziel[1],'.') == false ) {
    
        define('KATALOG_ZDJEC', $podziel[1]);
    
        // bedzie pokazywalo aukcje ktore maja date zakonczenia wieksza niz dzisiejsza + 1 dzien
        $data = date('Y-m-d H:i:s', time() + 86400);

        // szuka aukcji
        // and a.auction_date_end > "' . $data . '" 
        $zapytanie = 'select a.auction_id, a.products_name, a.products_id, a.auction_date_end, a.products_buy_now_price, 
                         p.products_image as zdjecie_oryginalne,
                         ap.products_image_allegro as zdjecie_allegro
                    from allegro_auctions a
               left join products p on a.products_id = p.products_id
               left join products_allegro_info ap on a.products_id = ap.products_id
                   where a.synchronization = 0
                order by a.allegro_id desc limit ' . ((int)$podziel[0] - 1) . ',1';

        $sql = $db->open_query($zapytanie); 
        
        if ((int)$db->ile_rekordow($sql) > 0) {
        
            $info = $sql->fetch_assoc();
            
            $plik_aukcji = KATALOG_ZDJEC . '/allegro_foto/' . 'aukcja_nr_' . $info['auction_id']  . '.png';

            if ( file_exists( $plik_aukcji ) ) {
                //
                // jezeli czas utworzenia pliku jest starszy niz 12 godzin
                if ( time() - filectime( $plik_aukcji ) > (3600 * 12) ) {
                    //
                    zdjecieAukcjiAllegro( $info );
                    //     
                    // jezeli nie jest starszy to odczytuje zapisany plik
                  } else { 
                    //
                    $fp = fopen($plik_aukcji, 'rb');
                    //
                    header("Content-Type: image/png");
                    header("Content-Length: " . filesize($plik_aukcji));
                    fpassthru($fp);
                    exit;            
                    //
                }
                //
            } else {
                //
                zdjecieAukcjiAllegro( $info );
                //
            }
            
            unset($plik_aukcji);
            
        } else {
        
            // jezeli nie ma aukcji
            
            $plik_aukcji = KATALOG_ZDJEC . '/allegro_foto/brak_aukcji.png';

            if ( file_exists( $plik_aukcji ) ) {
                //
                    $fp = fopen($plik_aukcji, 'rb');
                    //
                    header("Content-Type: image/png");
                    header("Content-Length: " . filesize($plik_aukcji));
                    fpassthru($fp);
                    exit;            
                    //
            }
            
            unset($plik_aukcji);

        }

        $db->close_query($sql);
        unset($zapytanie, $info, $data); 
        
    }
    
} 

if ( isset($_GET['link']) && (int)$_GET['link'] > 0 ) {

    // bedzie pokazywalo aukcje ktore maja date zakonczenia wieksza niz dzisiejsza + 1 dzien
    $data = date('Y-m-d H:i:s', time() + 86400);

    // szuka aukcji
    // and a.auction_date_end > "' . $data . '" 
    $zapytanie = 'select a.auction_id, a.products_name, a.products_id, a.auction_date_end, a.products_buy_now_price, 
                         p.products_image as zdjecie_oryginal,
                         ap.products_image_allegro as zdjecie_allegro
                    from allegro_auctions a
               left join products p on a.products_id = p.products_id
               left join products_allegro_info ap on a.products_id = ap.products_id
                order by a.allegro_id desc limit ' . ((int)$_GET['link'] - 1) . ',1';
                   
    $sql = $db->open_query($zapytanie); 

    if ((int)$db->ile_rekordow($sql) > 0) {
    
        include 'klasy/Seo.php';
    
        $info = $sql->fetch_assoc();
        // allegro.pl
        header("Location: http://allegro.pl/" . Seo::link_SEO($info['products_name'] . ' i' . $info['auction_id'],'','inna'));
        
        exit();
        
    } else {
    
        header("Location: http://allegro.pl");
    
    }
    
    $db->close_query($sql);
    unset($zapytanie, $info, $data);     

}

function przeskalujZdjecie( $zdjecie, $szerokosc, $wysokosc ) {
    //
    // sprawdza czy jest zdjecie
    if ( file_exists($zdjecie) ) {
        //
        // pobiera rozszerzenie pliku
        $rozszerzenie = pathinfo($zdjecie);
        //
        // w zaleznosci od rozszerzenia tworzy nowy obraz
        switch ( strtolower((string)$rozszerzenie['extension']) ) {
            case 'jpg':    
              $zrodlo = @imagecreatefromjpeg($zdjecie);
              break;
            case 'png':    
              $zrodlo = @imagecreatefrompng($zdjecie);
              break;
            case 'gif':    
              $zrodlo = @imagecreatefromgif($zdjecie);         
              break;
            default:
              $zrodlo = false;
        }
        //
        unset($rozszerzenie);
        //
        // jezeli poprawnie utworzylo obrazek
        if ( $zrodlo ) {
            //
            // wielkosc oryginalnego obrazka
            $nowaSzerokosc = imagesx($zrodlo);
            $nowaWysokosc = imagesy($zrodlo);
            //
            // skalowanie wysokosci miniatury
            if ($nowaWysokosc > $wysokosc) {
                $nowaSzerokosc = ($wysokosc / $nowaWysokosc) * $nowaSzerokosc;
                $nowaWysokosc = $wysokosc;
            }
            // skalowanie szerokosci miniatury
            if ($nowaSzerokosc > $szerokosc) {
                $nowaWysokosc = ($szerokosc / $nowaSzerokosc) * $nowaWysokosc;
                $nowaSzerokosc = $szerokosc;
            }
            //
            // tworzenie nowego zdjecia
            $noweZdjecie = imagecreatetruecolor($nowaSzerokosc, $nowaWysokosc);
            //
            // zmiana wielkosci obrazka
            imagecopyresampled($noweZdjecie, $zrodlo, 0, 0, 0, 0, $nowaSzerokosc, $nowaWysokosc, imagesx($zrodlo), imagesy($zrodlo));
            //
            // nazwa tmp zdjecia
            $losowa_nazwa = KATALOG_ZDJEC . '/allegro_foto/' . 'tmp_' . rand(100000000,999999999) . '.png';
            //
            // zapisuje zdjecie w formacie png
            imagepng($noweZdjecie, $losowa_nazwa, 1);
            chmod($losowa_nazwa, 0777);
            // zwraca nazwe utworzonego obrazka
            return $losowa_nazwa;
            //
        }
        //
    }
    //
}

function zdjecieAukcjiAllegro( $info ) {

    // parametry aukcji
    $nr_aukcji = $info['auction_id'];
    
    if ( !empty($info['zdjecie_allegro']) ) {
         $zdjecie = KATALOG_SKLEPU . '/' . KATALOG_ZDJEC . '/' . $info['zdjecie_allegro'];
       } else {
         $zdjecie = KATALOG_SKLEPU . '/' . KATALOG_ZDJEC . '/' . $info['zdjecie_oryginalne'];
    }
    
    $tytul = $info['products_name'];
    $cena = str_replace('.', ',', (string)$info['products_buy_now_price']) . ' zł';
    if ( FunkcjeWlasnePHP::my_strtotime($info['auction_date_end']) > 0 ) {
        $dni_do_konca = ceil( ( FunkcjeWlasnePHP::my_strtotime($info['auction_date_end']) - time() ) / 86400 );
    } else {
        $dni_do_konca = '';
    }

    // tworzy puste zdjecie o rozmiarach 250 x 110px
    $wynikowe_zdjecie = imagecreatetruecolor(250, 110);
    
    // definicje kolorow do czcionki czy tla
    $bialy = imagecolorallocate($wynikowe_zdjecie, 0xFF, 0xFF, 0xFF);
    $czarny = imagecolorallocate($wynikowe_zdjecie, 0x00, 0x00, 0x00);
    $czerwony = imagecolorallocate($wynikowe_zdjecie, 0xFF, 0x00, 0x00);
    $pomarancz = imagecolorallocate($wynikowe_zdjecie, 0xFF, 0x59, 0x00);
    $szary = imagecolorallocate($wynikowe_zdjecie, 0xE9, 0xE9, 0xE9);
    $grafit = imagecolorallocate($wynikowe_zdjecie, 0x94, 0x94, 0x94);
    
    // wypelnia puste zdjecie kolorem bialym
    imagefilledrectangle($wynikowe_zdjecie, 0, 0, 249, 109, $bialy);

    // czcionka uzywana przy tworzeniu napisow w obrazku
    $czcionka = 'programy/font/Roboto.ttf';

    // dzieli ciag na czesci po 20 znakow
    $ciag = wordwrap( $tytul, 20, "<br />", true);
    // sprawdza ile jest wierszy
    $linie = explode('<br />', (string)$ciag);
    $ile_linii = count($linie);
    
    // tytul aukcji
    $dodanie_od_gory = 10;
    
    for ( $r = 1; $r <= $ile_linii; $r++ ) {
        //
        // dodaje tekst do obrazka
        // 10 - rozmiar czcionki
        // 0 - margines od gory
        // 105 - margines od lewej - miniaturka zdjecia jest na 95 - wiec bedzie je dzielilo 15px
        imagettftext($wynikowe_zdjecie, 10, 0, 105, ($r * 15) + $dodanie_od_gory, $czarny, $czcionka, $linie[ $r - 1 ]);        
        //
    }

    // definiowanie ceny
    $ciag = wordwrap('Cena: ' . $cena, 20, "<br />", true);
    $tekst = str_replace('<br />', "\n", (string)$ciag);

    imagettftext($wynikowe_zdjecie, 12, 0, 105, ($ile_linii * 15) + 35, $czerwony, $czcionka, $tekst);
    
    // miniaturka zdjecia

    // skaluje zdjecie do rozmiaru 90 x 90px
    $miniaturka = przeskalujZdjecie($zdjecie,90,90);    
    // jezeli zostanie utworzona miniaturka to doda ja do zdjecia glownego
    if ( !empty($miniaturka) ) {
        // tworzy zdjecie produktu z miniaturki
        $zdjecie_produktu = imagecreatefrompng( $miniaturka );
        // laczy obrazki - zdjecie produktu oraz obraz z napisami
        imagecopy($wynikowe_zdjecie, $zdjecie_produktu, 5, 5, 0, 0, imagesx($zdjecie_produktu), imagesy($zdjecie_produktu));
    }
    
    // tworzy tymczasowe zdjecie z napisami i zdjeciem produktu
    $losowa_nazwa_info = KATALOG_ZDJEC . '/allegro_foto/' . 'tmp_info_' . rand(100000000,999999999) . '.png';
    imagepng($wynikowe_zdjecie, $losowa_nazwa_info ,1);    
   
    imagedestroy($wynikowe_zdjecie);
    
    // jezeli bylo utworzone zdjecie produktu usuwa je z serwera
    if ( !empty($miniaturka) ) {
        imagedestroy($zdjecie_produktu);
        unlink($miniaturka);
    }
    
    // tworzy ostatnie puste zdjecie 250 x 150px
    $wynikowe_zdjecie = imagecreatetruecolor(250, 150);
    
    // wypelnia kolorem pomarczonym
    imagefilledrectangle($wynikowe_zdjecie, 0, 0, 249, 149, $szary);

    // tekst kup teraz
    $tekst = 'KUP TERAZ';
    $tb = imagettfbbox(13, 0, $czcionka, $tekst);
    
    // ustala margines dla wycentrowania tekstu
    $x = ceil((250 - $tb[2]) / 2);
    $height = abs($tb[5] - $tb[1]);
    $y = $height/2;
    
    // 13 - rozmiar tekstu
    // $x - margines od lewej krawedzi
    // 123 + $y - polozenie od gory - 123 bo wysokosc zdjecia to 95 + margines - $y - wspolrzedne tekstu sa podawane od dolnego marginesu tekstu
    imagettftext($wynikowe_zdjecie, 13, 0, $x, 123 + $y, $pomarancz, $czcionka, $tekst);

    // tekst z iloscia dni do konca aukcji
    
    if ( $dni_do_konca != '' ) {
        if ( $dni_do_konca > 0 ) {
            $tekst = 'do końca: ' . $dni_do_konca . ' dni';
          } else {
            $tekst = 'aukcja zakończona';
        }
    } else {
        $tekst = 'do wyczerpania';
    }
    $tb = imagettfbbox(8, 0, $czcionka, $tekst);
    $x = ceil((250 - $tb[2]) / 2);
    $height = abs($tb[5] - $tb[1]);
    $y = $height/2;
    imagettftext($wynikowe_zdjecie, 8, 0, $x, 139 + $y, $grafit, $czcionka, $tekst);

    // tworzy puste zdjecie na podstawie przygotowanego wczesniej tmp (zdjecie + napisy)
    $zdjecie_produktu = imagecreatefrompng($losowa_nazwa_info);

    // laczy zdjecie z napisami z drugim z KUP TERAZ
    imagecopy($wynikowe_zdjecie, $zdjecie_produktu, 0, 0, 0, 0, imagesx($zdjecie_produktu), imagesy($zdjecie_produktu));
    
    imagepng($wynikowe_zdjecie, KATALOG_ZDJEC . '/allegro_foto/' . 'aukcja_nr_' . $nr_aukcji . '.png', 1);

    // jezeli ma je tylko wyswietlic
    header('Content-type: image/png');
    
    // zapisuje gotowe zdjecie png
    //imagepng($wynikowe_zdjecie, KATALOG_ZDJEC . '/allegro_foto/' . 'aukcja_nr_' . $nr_aukcji . '.png', 1);
    imagepng($wynikowe_zdjecie);

    // usuwa tymczasowe zdjecia
    unlink($losowa_nazwa_info);
    unset($losowa_nazwa_info);
    
    // zwraca nazwe utworzonego zdjecia
    //return KATALOG_ZDJEC . '/allegro_foto/' . 'aukcja_nr_' . $nr_aukcji . '.png';
    
}    

?>
