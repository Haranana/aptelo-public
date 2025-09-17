<?php
chdir('../');            

if (isset($_POST['id']) || isset($_POST['idwiele'])) {

    // wczytanie ustawien inicjujacych system
    require_once('ustawienia/init.php');
    
    if (Sesje::TokenSpr()) {
      
        $TablicaAktualizacji = array();
        
        $TablicaWyniku = array();
        
        // dla pojedynczego produktu
        if ( isset($_POST['id']) && isset($_POST['ilosc']) ) {

            $TablicaAktualizacji[] = array( 'id' => $filtr->process($_POST['id']),
                                            'ilosc' => (float)$_POST['ilosc'],
                                            'id_inputa' => '' );
            
        }
        
        // dla wielu produktow
        if ( isset($_POST['idwiele']) && !isset($_POST['ilosci']) ) {

              foreach ( $_POST['idwiele'] as $TablicaWieluId ) {
                
                  $TablicaAktualizacji[] = array( 'id' => $filtr->process($TablicaWieluId[0]),
                                                  'ilosc' => (float)$TablicaWieluId[1],
                                                  'id_inputa' => $TablicaWieluId[2] );                
                
              }

        }
 
        foreach ( $TablicaAktualizacji as $PozycjaAktualizowana) {
        
            $id = $PozycjaAktualizowana['id'];
            $IloscDoDodaniaDoKoszyka = $PozycjaAktualizowana['ilosc'];        

            //
            $Produkt = new Produkt( (int)Funkcje::SamoIdProduktuBezCech($id) );
        
            if ($Produkt->CzyJestProdukt == false) {
                exit;
            }
            
            $Komunikat = '';
            
            if ( isset($_POST['idwiele']) && !isset($_POST['ilosci']) ) {
              
                 $_POST['cechy'] = '';
                 
            }            
            
            // jezeli jest przeliczanie koszyka i id ma w sobie cechy
            if ( strpos((string)$id, "x") > -1 ) {
                // wyciaga same cechy z produktu
                $_POST['cechy'] = substr((string)$id, strpos((string)$id, "x"), strlen((string)$id) );
            } else if (isset($_POST['cechy'])) {
                $id = $id . $filtr->process($_POST['cechy']);
            }
            
            if ( !empty($_POST['cechy']) && MAGAZYN_SPRAWDZ_STANY == 'tak' && MAGAZYN_SPRZEDAJ_MIMO_BRAKU == 'nie' && CECHY_MAGAZYN == 'tak' && $Produkt->info['kontrola_magazynu'] == 1 ) {
                $Produkt->ProduktKupowanie( $filtr->process($_POST['cechy']) ); 
              } else {
                $Produkt->ProduktKupowanie();
            }

            // okresla czy ilosc jest ulamkowa zeby pozniej odpowiednio sformatowac wynik
            $Przecinek = 2;
            // jezeli sa wartosci calkowite to dla pewnosci zrobi int
            if ( $Produkt->info['jednostka_miary_typ'] == '1' ) {
                $Przecinek = 0;
            }
            //        

            $BylBlad = false;
            
            $DoInputaIlosci = '';
            
            // sprawdzi czy w koszyku nie ma juz takiego produktu - jezeli jest doda do liczby z inputa
            //
            $SumaWszystkichKoszyka = 0;
            $IleJestKoszykuAktualnegoProduktu = 0;
            //
            $CzyJestProduktGratisy = false;
            //
            // szuka w koszyku produktow o takim samym id glownym
            foreach ( $_SESSION['koszyk'] As $TablicaWartosci ) {
                //
                if ( (int)Funkcje::SamoIdProduktuBezCech($id) == (int)Funkcje::SamoIdProduktuBezCech($TablicaWartosci['id']) ) {
                    //
                    // sumuje produkty jezeli maja cechy lub sa gratisami
                    if ( $id == $TablicaWartosci['id'] || $id . '-gratis' == $TablicaWartosci['id'] || KOSZYK_SPOSOB_DODAWANIA == 'tak' ) {
                      
                        if ( KOSZYK_SPOSOB_DODAWANIA == 'tak' ) {
                            //
                            $TabUnikalnyId = explode('U', (string)$id);
                            $TabUnikalnyTablicaWartosciId = explode('U', (string)$TablicaWartosci['id']);                           
                            //
                            if ( $TabUnikalnyId[0] == $TabUnikalnyTablicaWartosciId[0] ) {
                                 $IleJestKoszykuAktualnegoProduktu += $TablicaWartosci['ilosc'];
                            }
                            //
                            unset($TabUnikalnyId, $TabUnikalnyTablicaWartosciId);
                            //
                          } else {
                            $IleJestKoszykuAktualnegoProduktu = $TablicaWartosci['ilosc'];
                        }
                        //
                        if ( $id . '-gratis' == $TablicaWartosci['id'] ) {
                            $CzyJestProduktGratisy = true;
                        }
                    }
                    $SumaWszystkichKoszyka += $TablicaWartosci['ilosc'];
                }
                //
            }     
            //        

            // jezeli jest wpisane wiecej niz w magazynie
            if ( MAGAZYN_SPRAWDZ_STANY == 'tak' && MAGAZYN_SPRZEDAJ_MIMO_BRAKU == 'nie' && $Produkt->info['kontrola_magazynu'] == 1 ) {
                //
                $IloscDoSprawdzenia = $IloscDoDodaniaDoKoszyka;
                
                // jezeli jest akcja dodawania to musi ustalic przed dodaniem jaka jest calkowita liczba produktow z inputa + koszyk
                if (isset($_POST['akcja']) && $_POST['akcja'] == 'dodaj' && (($_POST['cechy'] != '' && CECHY_MAGAZYN == 'tak') || empty($_POST['cechy']))) {
                    $IloscDoSprawdzenia = $IloscDoDodaniaDoKoszyka + $IleJestKoszykuAktualnegoProduktu;
                }
                if (($_POST['cechy'] != '' && CECHY_MAGAZYN == 'nie') || $CzyJestProduktGratisy == true) {
                    //
                    if ( $_POST['akcja'] == 'dodaj' ) {
                        $IloscDoSprawdzenia = $IloscDoDodaniaDoKoszyka + $SumaWszystkichKoszyka;
                      } else {
                        $IloscDoSprawdzenia = $IloscDoDodaniaDoKoszyka + ($SumaWszystkichKoszyka - $IleJestKoszykuAktualnegoProduktu); 
                    }
                    //
                }

                if ( KOSZYK_SPOSOB_DODAWANIA == 'tak' ) {
                     //
                     $IloscDoSprawdzenia = $IleJestKoszykuAktualnegoProduktu + $IloscDoDodaniaDoKoszyka;
                     //
                     foreach ( $_SESSION['koszyk'] As $TablicaWartosci ) {
                        
                        if ( $id == $TablicaWartosci['id'] ) {
                             $IloscDoSprawdzenia -= $TablicaWartosci['ilosc'];
                        }
                        
                     }
                     //                     
                }                   
                
                //
                if ( $IloscDoSprawdzenia > $Produkt->zakupy['ilosc_magazyn'] ) {   
                    //
                    $IleMoznaDodac = 0;
                    //
                    // info ze produkt juz byl dodany do koszyka
                    if ( $IleJestKoszykuAktualnegoProduktu > 0 && CECHY_MAGAZYN == 'tak') {
                        $Komunikat .= $GLOBALS['tlumacz']['BYL_DODANY_DO_KOSZYKA'] . ' <b>' . number_format( $IleJestKoszykuAktualnegoProduktu, $Przecinek, '.', '' ) . '</b> ' .  $Produkt->info['jednostka_miary'] . '. '; 
                        $IleMoznaDodac = $Produkt->zakupy['ilosc_magazyn'] - $IleJestKoszykuAktualnegoProduktu;
                    }
                    if ( $SumaWszystkichKoszyka > 0 && CECHY_MAGAZYN == 'nie') {
                        $Komunikat .= $GLOBALS['tlumacz']['BYL_DODANY_DO_KOSZYKA'] . ' <b>' . number_format( $SumaWszystkichKoszyka, $Przecinek, '.', '' ) . '</b> ' .  $Produkt->info['jednostka_miary'] . '. '; 
                        $IleMoznaDodac = $Produkt->zakupy['ilosc_magazyn'] - $SumaWszystkichKoszyka;
                    }                
                    //
                    // info ze brakuje stanu magazynowego
                    $Komunikat .= $GLOBALS['tlumacz']['BRAK_STANU_MAGAZYNOWEGO'] . ' ' . $GLOBALS['tlumacz']['MAKSYMALNA_ILOSC'] . ' <b>' . number_format( $Produkt->zakupy['ilosc_magazyn'], $Przecinek, '.', '' ) . '</b> ' .  $Produkt->info['jednostka_miary'] . '. ';
                    //
                    if ($IleMoznaDodac < 0) {
                        $IleMoznaDodac = 0;
                    }
                    //
                    if ( ( $IleJestKoszykuAktualnegoProduktu > 0 && CECHY_MAGAZYN == 'tak' ) || ( $SumaWszystkichKoszyka > 0 && CECHY_MAGAZYN == 'nie') ) {
                        // info ile mozna dodac jeszcze do koszyka
                        $Komunikat .= $GLOBALS['tlumacz']['MAKSYMALNA_ILOSC_Z_KOSZYKIEM'] . ' <b>' . number_format( $IleMoznaDodac, $Przecinek, '.', '' ) . '</b> ' .  $Produkt->info['jednostka_miary'];  
                        //
                    }
                    //
                    unset($IleMoznaDodac);
                    //
                    $BylBlad = true;
                    //                 
                }
                //
                unset($IloscDoSprawdzenia);
                //
            }             

            // jezeli jest wartosc maksymalna
            if ( ( ( (isset($_POST['akcja']) && $_POST['akcja'] == 'przelicz') ? 0 : $SumaWszystkichKoszyka ) + (float)$PozycjaAktualizowana['ilosc']) > $Produkt->zakupy['maksymalna_ilosc'] && $Produkt->zakupy['maksymalna_ilosc'] > 0 && $BylBlad == false ) {
                //
                // jezeli w koszyku sa jakies produkty to wyswietli ile mozna jeszcze dodac
                if ( $SumaWszystkichKoszyka > 0 ) {
                    //
                    $IleMoznaDodac = $Produkt->zakupy['maksymalna_ilosc'] - $SumaWszystkichKoszyka;
                    if ($IleMoznaDodac < 0) {
                        $IleMoznaDodac = 0;
                    }
                    // info ile maksymalnie mozna dodac do koszyka
                    $Komunikat .= $GLOBALS['tlumacz']['MAKSYMALNA_ILOSC'] . ' <b>' . number_format( $Produkt->zakupy['maksymalna_ilosc'], $Przecinek, '.', '' ) . '</b> ' .  $Produkt->info['jednostka_miary'] . '. '; 
                    //
                    // info ile mozna dodac jeszcze do koszyka
                    $Komunikat .= $GLOBALS['tlumacz']['MAKSYMALNA_ILOSC_Z_KOSZYKIEM'] . ' <b>' . number_format( $IleMoznaDodac, $Przecinek, '.', '' ) . '</b> ' .  $Produkt->info['jednostka_miary'];  
                    //
                    unset($IleMoznaDodac);
                    //
                  } else {
                    //
                    // info ile maksymalnie mozna dodac do koszyka
                    $Komunikat .= $GLOBALS['tlumacz']['MAKSYMALNA_ILOSC'] . ' <b>' . number_format( $Produkt->zakupy['maksymalna_ilosc'], $Przecinek, '.', '' ) . '</b> ' .  $Produkt->info['jednostka_miary'];  
                    //
                }
                $BylBlad = true;
                //
            }           

            // jezeli na stanie jest mniej niz wartosc minimalna
            if ( $Produkt->info['kontrola_magazynu'] == 1 ) {
              
                if ( MAGAZYN_SPRAWDZ_STANY == 'tak' && MAGAZYN_SPRZEDAJ_MIMO_BRAKU == 'nie' && $Produkt->zakupy['ilosc_magazyn'] < $Produkt->zakupy['minimalna_ilosc'] && $BylBlad == false ) {
                    //
                    // info ze brakuje stanu magazynowego
                    //$Komunikat .= $GLOBALS['tlumacz']['BRAK_STANU_MAGAZYNOWEGO'] . ' '; 
                    $Komunikat .= $GLOBALS['tlumacz']['MINIMALNA_ILOSC'] . ' <b>' . $Produkt->zakupy['minimalna_ilosc'] . '</b> ' .  $Produkt->info['jednostka_miary'];
                    $BylBlad = true;
                    //        
                }  

            }

            // jezeli klient dodaje do koszyka mniej niz jest minimum
            if ( $IloscDoDodaniaDoKoszyka < $Produkt->zakupy['minimalna_ilosc'] && $Produkt->zakupy['minimalna_ilosc'] > 0 && $BylBlad == false ) {
                //
                // info o minimalnej ilosci produktu
                $Komunikat .= $GLOBALS['tlumacz']['MINIMALNA_ILOSC'] . ' <b>' . $Produkt->zakupy['minimalna_ilosc'] . '</b> ' .  $Produkt->info['jednostka_miary']; 
                $BylBlad = true;
                //
            }
            
            // jezeli jest przyrost ilosci
            if ( $Produkt->zakupy['przyrost_ilosci'] > 0 && $BylBlad == false ) {
                //
                $Przyrost = $Produkt->zakupy['przyrost_ilosci'];
                //
                if ( (int)(round((($IloscDoDodaniaDoKoszyka / $Przyrost) * 100), 2) / 100) != (round((($IloscDoDodaniaDoKoszyka / $Przyrost) * 100), 2) / 100) ) {
                    //
                    $Komunikat .= $GLOBALS['tlumacz']['WIELOKROTNOSC'] . ' <b>' . number_format( $Produkt->zakupy['przyrost_ilosci'], $Przecinek, '.', '' ) . '</b> ' .  $Produkt->info['jednostka_miary']; 
                    $DoInputaIlosci = number_format( (int)(((float)$PozycjaAktualizowana['ilosc'] / $Przyrost) * $Przyrost), $Przecinek, '.', '' );
                    $BylBlad = true;
                    //
                }
                //
            }

            $DoInputaIlosci = '';
            
            // jezeli byl jakis komunikat
            if ( $BylBlad == true && $DoInputaIlosci == '' ) {
                //
                // jezeli jest juz produkt w koszyku pobierze jego ilosc
                $DoInputaIlosci = 1;
                //
                if ( isset($_SESSION['koszyk'][$id]) && (isset($_POST['akcja']) && $_POST['akcja'] == 'przelicz') ) {
                    $DoInputaIlosci = number_format( $_SESSION['koszyk'][$id]['ilosc'], $Przecinek, '.', '' );
                } else if ( $Produkt->zakupy['minimalna_ilosc'] > 0 ) {
                    $DoInputaIlosci = number_format( $Produkt->zakupy['minimalna_ilosc'], $Przecinek, '.', '' );
                }
                //
            }  
                
            // dodatkowe zabezpieczenie zeby nie wstawilo w inputa mniej niz wartosc minimalna
            if ( $DoInputaIlosci < $Produkt->zakupy['minimalna_ilosc'] && $Produkt->zakupy['minimalna_ilosc'] > 0 && $DoInputaIlosci != '' ) {
                $DoInputaIlosci = number_format( $Produkt->zakupy['minimalna_ilosc'], $Przecinek, '.', '' );
            }
            
            $TablicaWyniku[] = array("komunikat" => $Komunikat, "ilosc" => $DoInputaIlosci, "nazwa" => $Produkt->info['nazwa'], "id_inputa" => $PozycjaAktualizowana['id_inputa'] );
            
            unset($SumaWszystkichKoszyka, $Przecinek, $Produkt, $Komunikat, $DoInputaIlosci, $BylBlad, $BylBlad, $IloscDoDodaniaDoKoszyka, $IleJestKoszykuAktualnegoProduktu);
            
        }
        
        echo json_encode( $TablicaWyniku );

        //
        unset($TablicaAktualizacji, $TablicaWyniku);

        // jezeli sa pkt w koszyku to je usunie
        if ( isset($_SESSION['punktyKlienta']) ) {
             unset($_SESSION['punktyKlienta']);
        }        
        //
 
    }
    
}
?>