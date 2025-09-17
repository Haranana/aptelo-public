<?php
// czy jest zapytanie
if ($IloscProduktow > 0) { 

    echo '<div class="ListingListaKontener">';

    while ($info = $sql->fetch_assoc()) {
        
        $Produkt = new Produkt( $info['products_id'] );
        
        // elementy kupowania
        $Produkt->ProduktKupowanie(); 
            
        echo '<div id="prd-' . rand(1,1000) . '-' . $info['products_id'] . '" class="Lista LiniaDolna' . (($Produkt->zakupy['mozliwe_kupowanie'] == 'tak' || $Produkt->zakupy['pokaz_koszyk'] == 'tak') ? '' : ' ProduktBezZakupu') . (($GLOBALS['koszykKlienta']->SprawdzCzyDodanyDoKoszyka($Produkt->info['id'])) ? ' ProduktDodanyDoKoszyka' : '') . '">';

            // dodatkowa tablica do integracji - NIE USUWAC
            $WyswietlaneProdukty[ $info['products_id'] ] = $Produkt;
                
            // producent
            if ( LISTING_PRODUCENT == 'tak' ) {
                 $Produkt->ProduktProducent();
            }

            // dostepnosc produktu
            if ( LISTING_DOSTEPNOSC == 'tak' ) {
                 $Produkt->ProduktDostepnosc();
            }            
     
            echo '<div class="ProdCena LiniaPrawa">';
            
                echo '<h3>' . $Produkt->info['link'] . '</h3>';
                
                echo '<div class="ProduktCena">' . $Produkt->info['cena'] . '</div>';
                
                $ListaPol = '';

                // czy jest producent
                if ( LISTING_PRODUCENT == 'tak' && !empty($Produkt->producent['nazwa'])) {
                    $ListaPol .= '<li>{__TLUMACZ:PRODUCENT}: <b>' . $Produkt->producent['link'] . '</b></li>';
                }                
                
                // czy numer katalogowy
                if ( LISTING_NR_KATALOGOWY == 'tak' && !empty($Produkt->info['nr_katalogowy'])) {
                    $ListaPol .= '<li>{__TLUMACZ:NUMER_KATALOGOWY}: <b> ' . $Produkt->info['nr_katalogowy'] . '</b></li>';
                }         

                // czy jest dostepnosc produktu
                if ( LISTING_DOSTEPNOSC == 'tak' ) {
                    //
                    if ( !empty($Produkt->dostepnosc['dostepnosc']) ) {
                        //
                        // jezeli dostepnosc jest obrazkiem wyswietli tylko obrazek
                        if ( $Produkt->dostepnosc['obrazek'] == 'tak' ) {
                            //
                            $ListaPol .= '<li>' . $Produkt->dostepnosc['dostepnosc'] . '</b>';
                          } else {
                            $ListaPol .= '<li>{__TLUMACZ:DOSTEPNOSC}: <b> ' . $Produkt->dostepnosc['dostepnosc'] . '</b></li>';
                            //
                        }
                    }            
                    //
                }     

                // czy jest stan magazynowy produktu
                if ( LISTING_STAN_MAGAZYNOWY == 'tak' && MAGAZYN_SPRAWDZ_STANY == 'tak' && ( $Produkt->info['kontrola_magazynu'] > 0 || LISTING_STAN_MAGAZYNOWY_BRAK_KONTROLI == 'tak' ) ) {
                    $ListaPol .= '<li>{__TLUMACZ:STAN_MAGAZYNOWY}: ' . ( KARTA_PRODUKTU_MAGAZYN_FORMA == 'liczba' ? '<b>'.$Produkt->zakupy['ilosc_magazyn_jm'].'</b>' : Produkty::PokazPasekMagazynu($Produkt->info['ilosc'], $Produkt->info['alarm_magazyn']) ) . '</li>';
                }            
                
                if ( $ListaPol != '' ) {
                     //
                     echo '<ul class="ListaOpisowa">' . $ListaPol . '</ul>';                
                     //
                }
                
                unset($ListaPol);         

                echo '<div class="Opis">' . $Produkt->info['opis_krotki'] . '</div>'; 
                
                // data dostepnosci
                if ( !empty($Produkt->info['data_dostepnosci']) ) {
                    echo '<div class="DataDostepnosci">{__TLUMACZ:DOSTEPNY_OD_DNIA} <b>' . $Produkt->info['data_dostepnosci'] . '</b></div>';
                }                

                if ( LISTING_ILOSC_KUPIONYCH == 'tak' ) {
                  
                     // pobierze dane o zakupach produktu
                     $Produkt->ProduktZakupy();
                     
                     if ( $Produkt->iloscKupionych > 0 ) {
                           
                         echo '<div class="Rg ListinIloscKupionych" style="padding-top:15px">';
                         
                            echo '<b>' . $Produkt->iloscKupionych . '</b> ' . ( $Produkt->iloscKupionych > 1 ? '{__TLUMACZ:PRODUKT_KUPILO}' : '{__TLUMACZ:PRODUKT_KUPIL}' );
                            
                         echo '</div>';
                         
                         echo '<div class="cl"></div>';
                     
                     }
                     
                }       

            echo '</div>';
            
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
                } else {
                    //
                    echo $Produkt->info['zapytanie_o_produkt'];
                    //
                }

                echo '<div class="cl"></div>';

                // jezeli jest wlaczona porownywarka produktow
                if (LISTING_POROWNYWARKA_PRODUKTOW == 'tak' && isset($_SESSION['produktyPorownania'])) {
                    //
                    // jezeli produkt byl dodany do porownania
                    if (in_array($Produkt->info['id'], (array)$_SESSION['produktyPorownania'])) {
                        echo '<span onclick="Porownaj(' . $Produkt->info['id'] . ',\'wy\')" id="id' . $Produkt->info['id'] . '" class="PorownajWlaczone ToolTip" title="{__TLUMACZ:LISTING_DODANY_DO_POROWNANIA}">{__TLUMACZ:LISTING_DODAJ_DO_POROWNANIA}</span>';
                      } else {
                        echo '<span onclick="Porownaj(' . $Produkt->info['id'] . ',\'wl\')" id="id' . $Produkt->info['id'] . '" class="Porownaj ToolTip" title="{__TLUMACZ:LISTING_DODAJ_DO_POROWNANIA}">{__TLUMACZ:LISTING_DODAJ_DO_POROWNANIA}</span>';
                    }
                    //
                }
                
                // jezeli jest aktywne dodawanie do schowka
                if (PRODUKT_SCHOWEK_STATUS == 'tak') {
                    //
                    if ($GLOBALS['schowekKlienta']->SprawdzCzyDodanyDoSchowka($Produkt->info['id'])) {                                    
                        echo '<span onclick="DoSchowka(' . $Produkt->info['id'] . ')" class="Schowek SchowekDodany ToolTip" title="{__TLUMACZ:LISTING_PRODUKT_DODANY_DO_SCHOWKA}">{__TLUMACZ:LISTING_PRODUKT_DODANY_DO_SCHOWKA}</span>';
                    } else {
                        echo '<span onclick="DoSchowka(' . $Produkt->info['id'] . ')" class="Schowek ToolTip" title="{__TLUMACZ:LISTING_DODAJ_DO_SCHOWKA}">{__TLUMACZ:LISTING_DODAJ_DO_SCHOWKA}</span>';
                    }                        
                    //
                }
            
            echo '</div>'; 

        echo '</div>';
        
        unset($Produkt);
            
    }
    
    // wyswietlanie okna przejscia do kolejnej strony
    
    if ( LISTING_PRODUKTOW_OSTATNIA_POZYCJA == 'tak' ) {
         //
         if ( $KolejnaStronaOkno != '' ) {
              //
              echo '<div class="Wiersz WierszListaKolejnaStrona">
                       <a href="' . $KolejnaStronaOkno . '">
                           <span>
                               <b>{__TLUMACZ:KOLEJNA_STRONA_LISTING}</b>
                           </span>
                       </a>
                   </div>';
              //
         }      
         //
    }
    
    echo '</div>';
    
    unset($info);
      
} else {

    $KategoriaOstatnia = '';
    
    if ( isset($LicznikPodkategorii) ) {
      
        if ( $LicznikPodkategorii == 0 ) {
             $KategoriaOstatnia = 'data-kategoria-ostatnia="tak" ';
        } else {
             $KategoriaOstatnia = 'data-kategoria-ostatnia="nie" ';
        }
        
    }
    
    if ( isset($_GET) && count($_GET) > 1 ) {
         $KategoriaOstatnia = 'data-kategoria-ostatnia="tak" ';
    }

    echo '<div id="BrakProduktow" class="Informacja" ' . $KategoriaOstatnia . '>{__TLUMACZ:BLAD_BRAK_PRODUKTOW}</div>';
  
}

$GLOBALS['db']->close_query($sql); 

unset($IloscProduktow, $zapytanie);  
?>