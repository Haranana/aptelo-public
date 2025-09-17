<?php
// /* dodatkowe ustawienia konfiguracyjne */
//
// {{MODUL_PRODUKTY_OKNA_ILOSC_PRODUKTOW;Ilość wyświetlanych na stronie jednorazowo produktów;4;2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20}}
// {{MODUL_PRODUKTY_OKNA_ILOSC_KOLUMN;W ilu kolumnach mają być wyświetlane produkty;2;1,2,3,4,5}}
// {{MODUL_PRODUKTY_OKNA_KUPOWANIE;Czy wyświetać możliwość zakupu produktu;tak;tak,nie}}
// {{MODUL_PRODUKTY_OKNA_OPIS;Czy wyświetać opis produktu;tak;tak,nie}}
// {{MODUL_PRODUKTY_OKNA_SORTOWANIE;Sposób sortowania produktów;nazwa;nazwa,cena,data dodania}}
//

if ( isset($_POST['nr']) && (int)$_POST['nr'] > 0 ) {
    //
    chdir('../');
    require_once('ustawienia/init.php');
    //
}

// zmienne bez definicji
$LimitZapytania = 4;
$IloscKolumn = 2;
$MoznaKupic = 'tak';
$WyswietlOpis = 'tak';
$Sortowanie = 'pd.products_name';

if ( defined('MODUL_PRODUKTY_OKNA_ILOSC_PRODUKTOW') ) {
   $LimitZapytania = (int)MODUL_PRODUKTY_OKNA_ILOSC_PRODUKTOW;
}
if ( defined('MODUL_PRODUKTY_OKNA_ILOSC_KOLUMN') ) {
   $IloscKolumn = (int)MODUL_PRODUKTY_OKNA_ILOSC_KOLUMN;
}
if ( defined('MODUL_PRODUKTY_OKNA_KUPOWANIE') ) {
   $MoznaKupic = MODUL_PRODUKTY_OKNA_KUPOWANIE;
}
if ( defined('MODUL_PRODUKTY_OKNA_OPIS') ) {
   $WyswietlOpis = MODUL_PRODUKTY_OKNA_OPIS;
}

// sortowanie
if ( defined('MODUL_PRODUKTY_OKNA_SORTOWANIE') ) {
    switch (MODUL_PRODUKTY_OKNA_SORTOWANIE) {
        case 'nazwa':
            $Sortowanie = "pd.products_name";
            break;
        case 'cena':
            $Sortowanie = "cena";
            break;
        case 'data dodania':
            $Sortowanie = "p.products_date_added desc";
            break;
    }
}

// poczatek zapytania
$Poczatek = 0;
$Nr = 1;
if ( isset($_POST['nr']) && (int)$_POST['nr'] > 0 ) {
     $Nr = (int)$_POST['nr'];
     $Poczatek = ((int)$_POST['nr'] - 1) * $LimitZapytania;
}

// suma wszystkich produktow
$WszystkieProdukty = count(Produkty::ProduktyModulowe(1000000, 'produkty'));

// wyswietlane produkty
$sql_produkty = $GLOBALS['db']->open_query( Produkty::SqlProduktyProste( $Sortowanie, $Poczatek . ',' . $LimitZapytania ) );
$IloscProduktow = (int)$GLOBALS['db']->ile_rekordow($sql_produkty);

if ($IloscProduktow > 0) {

    // jezeli jest to glowne uruchomienie
    if ( !isset($_POST['nr']) ) {
         echo '<div id="LadowanieWszystkie"></div>';         
         echo '<div id="WszystkieProduktyGlowne">';
    }
    
    echo '<div id="WszystkieProdukty">';

        echo '<div class="OknaRwd Kol-' . $IloscKolumn . '">';

        while ($Prd = $sql_produkty->fetch_assoc()) {

            echo '<article class="ProduktProsty OknoRwd">';

                $Produkt = new Produkt( $Prd['products_id'], '', '', '', ((!isset($_POST['nr'])) ? true : false) );
                //              
                echo '<div class="Foto">'.$Produkt->fotoGlowne['zdjecie_link_ikony'].'</div>';
                //
                echo '<h3>' . $Produkt->info['link'] . '</h3>' . $Produkt->info['cena'];
                
                if ( $MoznaKupic == 'tak' ) {
              
                  // elementy kupowania
                  $Produkt->ProduktKupowanie();                  
              
                  echo '<div class="Zakup">';
                  
                      // jezeli jest aktywne kupowanie produktow
                      if ($Produkt->zakupy['mozliwe_kupowanie'] == 'tak' || $Produkt->zakupy['pokaz_koszyk'] == 'tak') {
                          //
                          if ( $Produkt->zakupy['ma_pola_tekstowe'] == '0' && $Produkt->zakupy['ma_cechy'] == '0' ) {
                              echo $Produkt->zakupy['input_ilosci'] . '<em>' . $Produkt->zakupy['jednostka_miary'] . '</em> ' . $Produkt->zakupy['przycisk_kup'];
                          } else {
                              echo $Produkt->zakupy['przycisk_szczegoly'];
                          }
                          //
                      }            
                      
                  echo '</div>'; 

                }  
                
                if ( $WyswietlOpis == 'tak' ) {
                     echo '<div class="Opis LiniaGorna">' . $Produkt->info['opis_krotki'] . '</div>';
                }
                
                //
                unset($Produkt);

            echo '</article>';

        }

        echo '</div>';
        //
        echo '<div class="cl"></div>';

        // generowanie przyciskow
        echo '<div class="ModulPrzyciski">';
        
        $IlePrzedPo = 3;
        $BylaKropka = false;
        for ( $f = 1; $f <= ceil($WszystkieProdukty / $LimitZapytania); $f++ ) {
        
             $PokazPrzycisk = false;
             if ( $f == 1 || $f == ceil($WszystkieProdukty / $LimitZapytania) ) {
                  $PokazPrzycisk = true;
             }
             if ( $f >= $Nr - $IlePrzedPo && $f <= $Nr + $IlePrzedPo ) {
                  $PokazPrzycisk = true;
             }
             
             if ( $PokazPrzycisk == true ) {
                  echo '<b id="alp' . $f . '" ' . (($f == $Nr) ? 'class="On"' : '') . '>' . $f . '</b>';
                  $BylaKropka = false;
                } else if ( $BylaKropka == false ) {
                  echo '<span>...</span>';
                  $BylaKropka = true;
             }
             unset($PokazPrzycisk);
             
        } 
        unset($IlePrzedPo, $BylaKropka);
        
        echo '</div>';
    
    echo '</div>';
    
    // jezeli jest to glowne uruchomienie
    if ( !isset($_POST['nr']) ) {
         echo '</div>';
    }

    echo '<script type="text/javascript">';
    if ( !isset($_POST['nr']) ) {
         //
         echo '$(window).load(function(){ $.WszystkieProdukty(); });';
         //
       } else {
         //
         echo '$.WszystkieProdukty();';
         //
    }
    echo '</script>';         
    
}

$GLOBALS['db']->close_query($sql_produkty); 

unset($Nr, $Poczatek, $IloscProduktow, $Prd, $IloscKolumn, $LimitZapytania, $MoznaKupic, $WyswietlOpis);
?>