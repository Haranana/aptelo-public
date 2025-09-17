<?php
chdir('../');     

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone && Sesje::TokenSpr() && isset($_POST['pola'])) {
  
    // rozdziela serializowane dane z ajaxa na tablice POST
    parse_str($_POST['pola'], $PostTablica);
    unset($_POST['pola']);
    $_POST = $PostTablica;

    if ( isset($_POST['pozycje_menu']) ) {
         //
         $TablicaTmp = array();
         //
         foreach ( $_POST['pozycje_menu'] as $Tmp ) {
              //
              $Podziel = explode('|', (string)$Tmp);
              $PodzialDodatkowy = explode('_', (string)$Podziel[0]);
              //
              $Tmp = $PodzialDodatkowy[0] . '|' . $Podziel[1];
              //
              // flagi graficzne
              if ( isset($_POST['flaga_pozycji_' . $Podziel[0]]) ) {
                   //
                   $TablicaTmp[ $Tmp ]['flaga_pozycji'] = $_POST['flaga_pozycji_' . $Podziel[0]];
                   //
              }                  
              if ( isset($_POST['kolor_flaga_pozycji_' . $Podziel[0]]) ) {
                   //
                   $TablicaTmp[ $Tmp ]['kolor_flaga_pozycji'] = $_POST['kolor_flaga_pozycji_' . $Podziel[0]];
                   //
              } 
              if ( isset($_POST['kolor_tla_flaga_pozycji_' . $Podziel[0]]) ) {
                   //
                   $TablicaTmp[ $Tmp ]['kolor_tla_flaga_pozycji'] = $_POST['kolor_tla_flaga_pozycji_' . $Podziel[0]];
                   //
              } 
              if ( isset($_POST['nazwa_flaga_pozycji_' . $Podziel[0]]) ) {
                   //
                   $NazwyFlag = array();
                   //
                   foreach ( $_POST['nazwa_flaga_pozycji_' . $Podziel[0]] as $id => $wartosc ) {
                       //
                       $NazwyFlag[$id] = $wartosc;
                       //
                   }
                   //
                   $TablicaTmp[ $Tmp ]['nazwa_flaga_pozycji'] = $NazwyFlag;
                   //
                   unset($NazwyFlag);
                   //
              }               
              // czy wyswietlac grafiki kategorii
              if ( isset($_POST['grafika_kategorie_' . $Podziel[0]]) ) {
                   //
                   $TablicaTmp[ $Tmp ]['grafika_kategorie'] = 'tak';
                   //
                } else {
                   //
                   $TablicaTmp[ $Tmp ]['grafika_kategorie'] = 'nie';
                   //              
              }              
              // rodzaj grafiki kategorii
              if ( isset($_POST['rodzaj_grafika_kategorie_' . $Podziel[0]]) ) {
                   //
                   $TablicaTmp[ $Tmp ]['rodzaj_grafika_kategorie'] = $_POST['rodzaj_grafika_kategorie_' . $Podziel[0]];
                   //
              } 
              // rozmiar grafiki kategorii
              if ( isset($_POST['rozmiar_grafika_kategorie_' . $Podziel[0]]) ) {
                   //
                   $TablicaTmp[ $Tmp ]['rozmiar_grafika_kategorie'] = $_POST['rozmiar_grafika_kategorie_' . $Podziel[0]];
                   //
              }               
              // miejsce wyswietlania grafiki kategorii
              if ( isset($_POST['miejsce_grafika_kategorie_' . $Podziel[0]]) ) {
                   //
                   $TablicaTmp[ $Tmp ]['miejsce_grafika_kategorie'] = $_POST['miejsce_grafika_kategorie_' . $Podziel[0]];
                   //
              } 
              // mobile grafiki kategorii
              if ( isset($_POST['mobile_grafika_kategorie_' . $Podziel[0]]) ) {
                   //
                   $TablicaTmp[ $Tmp ]['mobile_grafika_kategorie'] = $_POST['mobile_grafika_kategorie_' . $Podziel[0]];
                   //
              }               
              // czy wyswietlac podkategorie
              if ( isset($_POST['podkategorie_' . $Podziel[0]]) ) {
                   //
                   $TablicaTmp[ $Tmp ]['podkategorie'] = 'tak';
                   //
                } else {
                   //
                   $TablicaTmp[ $Tmp ]['podkategorie'] = 'nie';
                   //              
              }
              // glebokosc drzewa kategorii
              if ( isset($_POST['glebokosc_drzewa_' . $Podziel[0]]) ) {
                   //
                   $TablicaTmp[ $Tmp ]['glebokosc_drzewa'] = $_POST['glebokosc_drzewa_' . $Podziel[0]];
                   //
              }  
              // rodzaj koloru linku
              if ( isset($_POST['kolor_pozycji_rodzaj_' . $Podziel[0]]) ) {
                   //
                   $TablicaTmp[ $Tmp ]['kolor_pozycji_rodzaj'] = $_POST['kolor_pozycji_rodzaj_' . $Podziel[0]];
                   //
              }                 
              // kolor linku
              if ( isset($_POST['kolor_pozycji_kolor_' . $Podziel[0]]) ) {
                   //
                   $TablicaTmp[ $Tmp ]['kolor_pozycji_kolor'] = $_POST['kolor_pozycji_kolor_' . $Podziel[0]];
                   //
              }      
              // rodzaj koloru tla linku
              if ( isset($_POST['kolor_tla_rodzaj_' . $Podziel[0]]) ) {
                   //
                   $TablicaTmp[ $Tmp ]['kolor_tla_rodzaj'] = $_POST['kolor_tla_rodzaj_' . $Podziel[0]];
                   //
              }                 
              // kolor tla linku
              if ( isset($_POST['kolor_tla_kolor_' . $Podziel[0]]) ) {
                   //
                   $TablicaTmp[ $Tmp ]['kolor_tla_kolor'] = $_POST['kolor_tla_kolor_' . $Podziel[0]];
                   //
              } 
              // szerokosc menu
              if ( isset($_POST['szerokosc_menu_' . $Podziel[0]]) ) {
                   //
                   $TablicaTmp[ $Tmp ]['szerokosc'] = $_POST['szerokosc_menu_' . $Podziel[0]];
                   //
              } 
              // efekt menu
              if ( isset($_POST['efekt_menu_' . $Podziel[0]]) ) {
                   //
                   $TablicaTmp[ $Tmp ]['efekt_menu'] = $_POST['efekt_menu_' . $Podziel[0]];
                   //
              }               
              // ilosc kolumn
              if ( isset($_POST['ilosc_kolumn_' . $Podziel[0]]) ) {
                   //
                   $TablicaTmp[ $Tmp ]['ilosc_kolumn'] = $_POST['ilosc_kolumn_' . $Podziel[0]];
                   //
              }  
              // wysokosc kolumn
              if ( isset($_POST['wysokosc_kolumn_' . $Podziel[0]]) ) {
                   //
                   $TablicaTmp[ $Tmp ]['wysokosc_kolumn'] = $_POST['wysokosc_kolumn_' . $Podziel[0]];
                   //
              }                    
              // grupa bannerow
              if ( isset($_POST['grupa_bannerow_' . $Podziel[0]]) ) {
                   //
                   $TablicaTmp[ $Tmp ]['grupa_bannerow'] = $_POST['grupa_bannerow_' . $Podziel[0]];
                   //
              }
              // ilosc bannerow
              if ( isset($_POST['ilosc_bannerow_' . $Podziel[0]]) ) {
                   //
                   $TablicaTmp[ $Tmp ]['ilosc_bannerow'] = $_POST['ilosc_bannerow_' . $Podziel[0]];
                   //
              } 
              // tekst bannerow
              if ( isset($_POST['tekst_bannery_' . $Podziel[0]]) ) {
                   //
                   $TablicaTmp[ $Tmp ]['tekst_bannery'] = $_POST['tekst_bannery_' . $Podziel[0]];
                   //
              }               
              // polozenie bannerow
              if ( isset($_POST['polozenie_bannerow_' . $Podziel[0]]) ) {
                   //
                   $TablicaTmp[ $Tmp ]['polozenie_bannerow'] = $_POST['polozenie_bannerow_' . $Podziel[0]];
                   //
              }  
              // ukrywnie bannerow w wersji mobilnej
              if ( isset($_POST['mobile_bannery_' . $Podziel[0]]) ) {
                   //
                   $TablicaTmp[ $Tmp ]['mobile_bannery'] = $_POST['mobile_bannery_' . $Podziel[0]];
                   //
              }                
              // ikonka menu
              if ( isset($_POST['menu_ikonka_' . $Podziel[0]]) ) {
                   //
                   $TablicaTmp[ $Tmp ]['menu_ikonka'] = $_POST['menu_ikonka_' . $Podziel[0]];
                   //
              }                  
              //
              // kategorie aktualnosci
              //
              // ilosc pozycji w menu rozwijanym
              if ( isset($_POST['ile_artykulow_kategorii_' . $Podziel[0]]) ) {
                   //
                   $TablicaTmp[ $Tmp ]['ile_artykulow_kategorii'] = $_POST['ile_artykulow_kategorii_' . $Podziel[0]];
                   //
              }   
              //
              // preload dla menu rozwijanego
              if ( isset($_POST['rodzaj_wczytanie_' . $Podziel[0]]) ) {
                   //
                   $TablicaTmp[ $Tmp ]['rodzaj_wczytanie'] = $_POST['rodzaj_wczytanie_' . $Podziel[0]];
                   //
              }   
              //   
              // czy wyswietlac ikony aktualnosci
              if ( isset($_POST['ikony_aktualnosci_' . $Podziel[0]]) ) {
                   //
                   $TablicaTmp[ $Tmp ]['ikony_aktualnosci'] = 'tak';
                   //
                } else {
                   //
                   $TablicaTmp[ $Tmp ]['ikony_aktualnosci'] = 'nie';
                   //              
              }              
              // rozmiar ikony aktualnosci
              if ( isset($_POST['rozmiar_ikony_aktualnosci_' . $Podziel[0]]) ) {
                   //
                   $TablicaTmp[ $Tmp ]['rozmiar_ikony_aktualnosci'] = $_POST['rozmiar_ikony_aktualnosci_' . $Podziel[0]];
                   //
              }               
              // miejsce wyswietlania ikony aktualnosci
              if ( isset($_POST['miejsce_ikony_aktualnosci_' . $Podziel[0]]) ) {
                   //
                   $TablicaTmp[ $Tmp ]['miejsce_ikony_aktualnosci'] = $_POST['miejsce_ikony_aktualnosci_' . $Podziel[0]];
                   //
              } 
              // mobile ikony aktualnosci
              if ( isset($_POST['mobile_ikony_aktualnosci_' . $Podziel[0]]) ) {
                   //
                   $TablicaTmp[ $Tmp ]['mobile_ikony_aktualnosci'] = $_POST['mobile_ikony_aktualnosci_' . $Podziel[0]];
                   //
              }  
              //
         }

         //
         $TablicaTmp = serialize($TablicaTmp);
         //

         $pola = array(
                 array('value',$TablicaTmp));

         $sql = $db->update_query('settings', $pola, " code = 'MENU_PODKATEGORIE'");	
         unset($pola); 
         //
    }

}
?>