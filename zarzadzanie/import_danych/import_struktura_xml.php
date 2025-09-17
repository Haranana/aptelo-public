<?php
// stworzenie tablicy z definicjami
$TablicaDane = array();

if (isset($dane_produktow->Produkt[(int)$_POST['limit']])) {

    foreach ($dane_produktow->Produkt[(int)$_POST['limit']]->children() as $child) {

        //
        $ByloDodanie = false;
        //

        // jezeli sa zdjecia dodatkowe
        if ($child->getName() == 'Zdjecia_dodatkowe') {
            $ileDodZdjec = count($dane_produktow->Produkt[(int)$_POST['limit']]->Zdjecia_dodatkowe->children());
            //
            for ($r = 0; $r < $ileDodZdjec; $r++) {
                //
                if ( isset($dane_produktow->Produkt[(int)$_POST['limit']]->Zdjecia_dodatkowe->Zdjecie[$r]->Zdjecie_link) ) {
                     //
                     $TablicaDane['Zdjecie_dodatkowe_' . ($r + 1)] = $dane_produktow->Produkt[(int)$_POST['limit']]->Zdjecia_dodatkowe->Zdjecie[$r]->Zdjecie_link;
                     //
                     if ( $dane_produktow->Produkt[(int)$_POST['limit']]->Zdjecia_dodatkowe->Zdjecie[$r]->Zdjecie_opis ) {                
                          $TablicaDane['Zdjecie_dodatkowe_opis_' . ($r + 1)] = $dane_produktow->Produkt[(int)$_POST['limit']]->Zdjecia_dodatkowe->Zdjecie[$r]->Zdjecie_opis;
                     }
                     //
                }
            }
            $ByloDodanie = true;
        }
        
        // jezeli sa kategorie
        if ($child->getName() == 'Kategoria') {
            //
            if (isset($dane_produktow->Produkt[(int)$_POST['limit']]->Kategoria)) {
                //
                $ZawartoscKat = explode('/', (string)$dane_produktow->Produkt[(int)$_POST['limit']]->Kategoria);
                for ($p = 0, $c = count($ZawartoscKat); $p < $c; $p++) {
                    //
                    $TablicaDane['Kategoria_'.($p + 1).'_nazwa'] = $ZawartoscKat[$p];                
                    //
                }
                //
                unset($ZawartoscKat);
                //
            }
            //

            $ByloDodanie = true;  
            //
        } 

        // jezeli sa dodatkowe zakladki
        if ($child->getName() == 'Dodatkowe_zakladki') {
            //
            for ($s = 0; $s < 4; $s++) {
                //
                if (isset($dane_produktow->Produkt[(int)$_POST['limit']]->Dodatkowe_zakladki->Dodatkowa_zakladka[$s])) {
                    //
                    $ZawartoscZakladki = $dane_produktow->Produkt[(int)$_POST['limit']]->Dodatkowe_zakladki->Dodatkowa_zakladka[$s];
                    foreach ($ZawartoscZakladki->children() as $ZakTr) {
                        if ($ZakTr->getName() == 'Nazwa') {
                            $TablicaDane['Dodatkowa_zakladka_'.($s + 1).'_nazwa'] = $ZakTr;
                        }
                        if ($ZakTr->getName() == 'Opis') {
                            $TablicaDane['Dodatkowa_zakladka_'.($s + 1).'_opis'] = $ZakTr;
                        }                
                    }
                    unset($ZawartoscZakladki);
                    //
                }
                //
            }
            $ByloDodanie = true;        
        }  

        // jezeli sa dodatkowe linki
        if ($child->getName() == 'Linki') {
            //
            for ($s = 0; $s < 4; $s++) {
                //
                if (isset($dane_produktow->Produkt[(int)$_POST['limit']]->Linki->Link[$s])) {
                    //
                    $ZawartoscLinkow = $dane_produktow->Produkt[(int)$_POST['limit']]->Linki->Link[$s];
                    foreach ($ZawartoscLinkow->children() as $ZakTr) {
                        if ($ZakTr->getName() == 'Nazwa') {
                            $TablicaDane['Link_'.($s + 1).'_nazwa'] = $ZakTr;
                        }
                        if ($ZakTr->getName() == 'Opis') {
                            $TablicaDane['Link_'.($s + 1).'_opis'] = $ZakTr;
                        }                        
                        if ($ZakTr->getName() == 'Url') {
                            $TablicaDane['Link_'.($s + 1).'_url'] = $ZakTr;
                        }                
                    }
                    unset($ZawartoscLinkow);
                    //
                }
                //
            }
            $ByloDodanie = true;        
        }    

        // jezeli sa pliki
        if ($child->getName() == 'Pliki') {
            //
            for ($s = 0; $s < 5; $s++) {
                //
                if (isset($dane_produktow->Produkt[(int)$_POST['limit']]->Pliki->Plik[$s])) {
                    //
                    $ZawartoscPliki = $dane_produktow->Produkt[(int)$_POST['limit']]->Pliki->Plik[$s];
                    foreach ($ZawartoscPliki->children() as $ZakTr) {
                        if ($ZakTr->getName() == 'Nazwa') {
                            $TablicaDane['Plik_'.($s + 1).'_nazwa'] = $ZakTr;
                        }
                        if ($ZakTr->getName() == 'Opis') {
                            $TablicaDane['Plik_'.($s + 1).'_opis'] = $ZakTr;
                        }
                        if ($ZakTr->getName() == 'Plik') {
                            $TablicaDane['Plik_'.($s + 1).'_plik'] = $ZakTr;
                        }   
                        if ($ZakTr->getName() == 'Logowanie') {
                            $TablicaDane['Plik_'.($s + 1).'_logowanie'] = $ZakTr;
                        }                     
                    }
                    unset($ZawartoscPliki);
                    //
                }
                //
            }
            $ByloDodanie = true;  
            //
        }    
        
        // jezeli sa pliki elektroniczne
        if ($child->getName() == 'Pliki_elektroniczne') {
            //
            for ($s = 0; $s < 101; $s++) {
                //
                if (isset($dane_produktow->Produkt[(int)$_POST['limit']]->Pliki_elektroniczne->Plik_elektroniczny[$s])) {
                    //
                    $ZawartoscPliki = $dane_produktow->Produkt[(int)$_POST['limit']]->Pliki_elektroniczne->Plik_elektroniczny[$s];
                    foreach ($ZawartoscPliki->children() as $ZakTr) {
                        if ($ZakTr->getName() == 'Nazwa') {
                            $TablicaDane['Plik_elektroniczny_'.($s + 1).'_nazwa'] = $ZakTr;
                        }
                        if ($ZakTr->getName() == 'Plik') {
                            $TablicaDane['Plik_elektroniczny_'.($s + 1).'_plik'] = $ZakTr;
                        }                       
                    }
                    unset($ZawartoscPliki);
                    //
                }
                //
            }
            $ByloDodanie = true;  
            //
        }          
        
        // jezeli sa filmy youtube
        if ($child->getName() == 'Filmy_youtube') {
            //
            for ($s = 0; $s < 4; $s++) {
                //
                if (isset($dane_produktow->Produkt[(int)$_POST['limit']]->Filmy_youtube->Youtube[$s])) {
                    //
                    $ZawartoscFilmow = $dane_produktow->Produkt[(int)$_POST['limit']]->Filmy_youtube->Youtube[$s];
                    foreach ($ZawartoscFilmow->children() as $ZakTr) {
                        if ($ZakTr->getName() == 'Nazwa') {
                            $TablicaDane['Youtube_'.($s + 1).'_nazwa'] = $ZakTr;
                        }
                        if ($ZakTr->getName() == 'Url') {
                            $TablicaDane['Youtube_'.($s + 1).'_url'] = $ZakTr;
                        } 
                        if ($ZakTr->getName() == 'Opis') {
                            $TablicaDane['Youtube_'.($s + 1).'_opis'] = $ZakTr;
                        } 
                        if ($ZakTr->getName() == 'Szerokosc') {
                            $TablicaDane['Youtube_'.($s + 1).'_szerokosc'] = $ZakTr;
                        }
                        if ($ZakTr->getName() == 'Wysokosc') {
                            $TablicaDane['Youtube_'.($s + 1).'_wysokosc'] = $ZakTr;
                        }                        
                    }
                    unset($ZawartoscFilmow);
                    //
                }
                //
            }
            $ByloDodanie = true;        
        }        
        
        // jezeli sa filmy flv
        if ($child->getName() == 'Filmy') {
            //
            for ($s = 0; $s < 4; $s++) {
                //
                if (isset($dane_produktow->Produkt[(int)$_POST['limit']]->Filmy->Film[$s])) {
                    //
                    $ZawartoscFilmow = $dane_produktow->Produkt[(int)$_POST['limit']]->Filmy->Film[$s];
                    foreach ($ZawartoscFilmow->children() as $ZakTr) {
                        if ($ZakTr->getName() == 'Nazwa') {
                            $TablicaDane['Film_'.($s + 1).'_nazwa'] = $ZakTr;
                        }
                        if ($ZakTr->getName() == 'Plik') {
                            $TablicaDane['Film_'.($s + 1).'_plik'] = $ZakTr;
                        } 
                        if ($ZakTr->getName() == 'Opis') {
                            $TablicaDane['Film_'.($s + 1).'_opis'] = $ZakTr;
                        } 
                        if ($ZakTr->getName() == 'Pelen_ekran') {
                            $TablicaDane['Film_'.($s + 1).'_ekran'] = $ZakTr;
                        }                        
                        if ($ZakTr->getName() == 'Szerokosc') {
                            $TablicaDane['Film_'.($s + 1).'_szerokosc'] = $ZakTr;
                        }
                        if ($ZakTr->getName() == 'Wysokosc') {
                            $TablicaDane['Film_'.($s + 1).'_wysokosc'] = $ZakTr;
                        }                        
                    }
                    unset($ZawartoscFilmow);
                    //
                }
                //
            }
            $ByloDodanie = true;        
        }            
        
        // jezeli sa mp3
        if ($child->getName() == 'Pliki_mp3') {
            //
            for ($s = 0; $s < 16; $s++) {
                //
                if (isset($dane_produktow->Produkt[(int)$_POST['limit']]->Pliki_mp3->Plik_mp3[$s])) {
                    //
                    $ZawartoscPliki = $dane_produktow->Produkt[(int)$_POST['limit']]->Pliki_mp3->Plik_mp3[$s];
                    foreach ($ZawartoscPliki->children() as $ZakTr) {
                        if ($ZakTr->getName() == 'Nazwa') {
                            $TablicaDane['Nazwa_mp3_'.($s + 1)] = $ZakTr;
                        }
                        if ($ZakTr->getName() == 'Plik') {
                            $TablicaDane['Plik_mp3_'.($s + 1)] = $ZakTr;
                        }                     
                    }
                    unset($ZawartoscPliki);
                    //
                }
                //
            }
            $ByloDodanie = true;  
            //
        }          
        
        // jezeli sa dodatkowe pola
        if ($child->getName() == 'Dodatkowe_pola') {
            //
            for ($s = 0; $s < 100; $s++) {
                //
                if (isset($dane_produktow->Produkt[(int)$_POST['limit']]->Dodatkowe_pola->Dodatkowe_pole[$s])) {
                    //
                    $ZawartoscPol = $dane_produktow->Produkt[(int)$_POST['limit']]->Dodatkowe_pola->Dodatkowe_pole[$s];
                    foreach ($ZawartoscPol->children() as $ZakTr) {
                        if ($ZakTr->getName() == 'Nazwa') {
                            $TablicaDane['Dodatkowe_pole_'.($s + 1).'_nazwa'] = $ZakTr;
                        }
                        if ($ZakTr->getName() == 'Wartosc') {
                            $TablicaDane['Dodatkowe_pole_'.($s + 1).'_wartosc'] = $ZakTr;
                        }
                        if ($ZakTr->getName() == 'Wartosc_2') {
                            $TablicaDane['Dodatkowe_pole_'.($s + 1).'_wartosc_2'] = $ZakTr;
                        }
                        if ($ZakTr->getName() == 'Wartosc_3') {
                            $TablicaDane['Dodatkowe_pole_'.($s + 1).'_wartosc_3'] = $ZakTr;
                        }                        
                        if ($ZakTr->getName() == 'Link') {
                            $TablicaDane['Dodatkowe_pole_'.($s + 1).'_link'] = $ZakTr;
                        }                         
                    }
                    unset($ZawartoscPol);
                    //
                }
                //
            }
            $ByloDodanie = true;        
        }

        // jezeli sa cechy
        if ($child->getName() == 'Cechy') {
            //
            for ($s = 0; $s < 100; $s++) {
                //
                if (isset($dane_produktow->Produkt[(int)$_POST['limit']]->Cechy->Cecha[$s])) {
                    //
                    $ZawartoscCecha = $dane_produktow->Produkt[(int)$_POST['limit']]->Cechy->Cecha[$s];
                    foreach ($ZawartoscCecha->children() as $ZakTr) {
                        if ($ZakTr->getName() == 'Nazwa') {
                            $TablicaDane['Cecha_nazwa_'.($s + 1)] = $ZakTr;
                        }
                        if ($ZakTr->getName() == 'Wartosc') {
                            $TablicaDane['Cecha_wartosc_'.($s + 1)] = $ZakTr;
                        } 
                        if ($ZakTr->getName() == 'Cena') {
                            $TablicaDane['Cecha_cena_'.($s + 1)] = $ZakTr;
                        }
                        if ($ZakTr->getName() == 'Waga') {
                            $TablicaDane['Cecha_waga_'.($s + 1)] = $ZakTr;
                        } 
                        if ($ZakTr->getName() == 'Foto') {
                            $TablicaDane['Cecha_foto_'.($s + 1)] = $ZakTr;
                        }  
                        if ($ZakTr->getName() == 'Domyslna') {
                            $TablicaDane['Cecha_domyslna_'.($s + 1)] = $ZakTr;
                        }                              
                    }
                    unset($ZawartoscCecha);
                    //
                }
                //
            }
            $ByloDodanie = true;        
        }     
        
        // dodatkowe akcesoria
        if ($child->getName() == 'Akcesoria') {
            //
            for ($s = 0; $s < 50; $s++) {
                //
                if (isset($dane_produktow->Produkt[(int)$_POST['limit']]->Akcesoria->Akcesoria_nr_katalogowy[$s])) {
                    //
                    $TablicaDane['Akcesoria_' . ($s + 1) . '_nr_katalogowy'] = $dane_produktow->Produkt[(int)$_POST['limit']]->Akcesoria->Akcesoria_nr_katalogowy[$s];
                    //
                }
                //
            }
            $ByloDodanie = true;        
        }    

        // allegro
        if ($child->getName() == 'Allegro') {
            //
            if (isset($dane_produktow->Produkt[(int)$_POST['limit']]->Allegro->Allegro_id_kategoria)) {
                //
                $TablicaDane['Allegro_id_kategoria'] = $dane_produktow->Produkt[(int)$_POST['limit']]->Allegro->Allegro_id_kategoria;
                //
            }
            if (isset($dane_produktow->Produkt[(int)$_POST['limit']]->Allegro->Allegro_nazwa_produktu)) {
                //
                $TablicaDane['Allegro_nazwa_produktu'] = $dane_produktow->Produkt[(int)$_POST['limit']]->Allegro->Allegro_nazwa_produktu;
                //
            }
            if (isset($dane_produktow->Produkt[(int)$_POST['limit']]->Allegro->Allegro_zdjecie)) {
                //
                $TablicaDane['Allegro_zdjecie'] = $dane_produktow->Produkt[(int)$_POST['limit']]->Allegro->Allegro_zdjecie;
                //
            } 
            if (isset($dane_produktow->Produkt[(int)$_POST['limit']]->Allegro->Allegro_cena)) {
                //
                $TablicaDane['Allegro_cena'] = $dane_produktow->Produkt[(int)$_POST['limit']]->Allegro->Allegro_cena;
                //
            }  
            if (isset($dane_produktow->Produkt[(int)$_POST['limit']]->Allegro->Allegro_waga)) {
                //
                $TablicaDane['Allegro_waga'] = $dane_produktow->Produkt[(int)$_POST['limit']]->Allegro->Allegro_waga;
                //
            }                   
            //
            $ByloDodanie = true;        
        }          

        // gpsr
        if ($child->getName() == 'Dane_GPSR') {
            //
            if (isset($dane_produktow->Produkt[(int)$_POST['limit']]->Dane_GPSR->Producent_nazwa)) {
                //
                $TablicaDane['Producent_nazwa'] = $dane_produktow->Produkt[(int)$_POST['limit']]->Dane_GPSR->Producent_nazwa;
                //
            }
            if (isset($dane_produktow->Produkt[(int)$_POST['limit']]->Dane_GPSR->Producent_ulica)) {
                //
                $TablicaDane['Producent_ulica'] = $dane_produktow->Produkt[(int)$_POST['limit']]->Dane_GPSR->Producent_ulica;
                //
            }
            if (isset($dane_produktow->Produkt[(int)$_POST['limit']]->Dane_GPSR->Producent_kod_pocztowy)) {
                //
                $TablicaDane['Producent_kod_pocztowy'] = $dane_produktow->Produkt[(int)$_POST['limit']]->Dane_GPSR->Producent_kod_pocztowy;
                //
            }
            if (isset($dane_produktow->Produkt[(int)$_POST['limit']]->Dane_GPSR->Producent_miasto)) {
                //
                $TablicaDane['Producent_miasto'] = $dane_produktow->Produkt[(int)$_POST['limit']]->Dane_GPSR->Producent_miasto;
                //
            }
            if (isset($dane_produktow->Produkt[(int)$_POST['limit']]->Dane_GPSR->Producent_kraj)) {
                //
                $TablicaDane['Producent_kraj'] = $dane_produktow->Produkt[(int)$_POST['limit']]->Dane_GPSR->Producent_kraj;
                //
            }
            if (isset($dane_produktow->Produkt[(int)$_POST['limit']]->Dane_GPSR->Producent_email)) {
                //
                $TablicaDane['Producent_email'] = $dane_produktow->Produkt[(int)$_POST['limit']]->Dane_GPSR->Producent_email;
                //
            }
            if (isset($dane_produktow->Produkt[(int)$_POST['limit']]->Dane_GPSR->Producent_telefon)) {
                //
                $TablicaDane['Producent_telefon'] = $dane_produktow->Produkt[(int)$_POST['limit']]->Dane_GPSR->Producent_telefon;
                //
            }            
            //
            if (isset($dane_produktow->Produkt[(int)$_POST['limit']]->Dane_GPSR->Importer_nazwa)) {
                //
                $TablicaDane['Importer_nazwa'] = $dane_produktow->Produkt[(int)$_POST['limit']]->Dane_GPSR->Importer_nazwa;
                //
            }
            if (isset($dane_produktow->Produkt[(int)$_POST['limit']]->Dane_GPSR->Importer_ulica)) {
                //
                $TablicaDane['Importer_ulica'] = $dane_produktow->Produkt[(int)$_POST['limit']]->Dane_GPSR->Importer_ulica;
                //
            }
            if (isset($dane_produktow->Produkt[(int)$_POST['limit']]->Dane_GPSR->Importer_kod_pocztowy)) {
                //
                $TablicaDane['Importer_kod_pocztowy'] = $dane_produktow->Produkt[(int)$_POST['limit']]->Dane_GPSR->Importer_kod_pocztowy;
                //
            }
            if (isset($dane_produktow->Produkt[(int)$_POST['limit']]->Dane_GPSR->Importer_miasto)) {
                //
                $TablicaDane['Importer_miasto'] = $dane_produktow->Produkt[(int)$_POST['limit']]->Dane_GPSR->Importer_miasto;
                //
            }
            if (isset($dane_produktow->Produkt[(int)$_POST['limit']]->Dane_GPSR->Importer_kraj)) {
                //
                $TablicaDane['Importer_kraj'] = $dane_produktow->Produkt[(int)$_POST['limit']]->Dane_GPSR->Importer_kraj;
                //
            }
            if (isset($dane_produktow->Produkt[(int)$_POST['limit']]->Dane_GPSR->Importer_email)) {
                //
                $TablicaDane['Importer_email'] = $dane_produktow->Produkt[(int)$_POST['limit']]->Dane_GPSR->Importer_email;
                //
            }
            if (isset($dane_produktow->Produkt[(int)$_POST['limit']]->Dane_GPSR->Importer_telefon)) {
                //
                $TablicaDane['Importer_telefon'] = $dane_produktow->Produkt[(int)$_POST['limit']]->Dane_GPSR->Importer_telefon;
                //
            }            
            //
            $ByloDodanie = true;        
        }     
        
        // jezeli jest to standardowe pole 
        if ($ByloDodanie == false) {
        
            if ( trim((string)$child->getName()) == 'Nazwa_produktu' ) {
                 $TablicaDane['Nazwa_produktu_struktura'] = trim((string)$child); 
            }
        
            $TablicaDane[trim((string)$child->getName())] = trim((string)$child);
        }

    }

}
/*
echo count($TablicaDane) . '<br>';

for ($q = 0; $q < count($TablicaDef); $q++) {
    echo $TablicaDef[$q] . ' - ' . $TablicaDane[$q] .  "<br />";
}
*/

?>