<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone && Sesje::TokenSpr()) {

    if (isset($_GET['pole'])) {
        $id_poz = (int)$_GET['pole'];
       } else {
        $id_poz = 0;
    }
    
    if (isset($_GET['sciezka'])) {
        $sciezka = $_GET['sciezka'];
       } else {
        $sciezka = 0;
    }
    
    if ($id_poz > 0) {

        $tablica_kat = Kategorie::DrzewoKategorii($id_poz, '&nbsp;&nbsp;', '', '', false, false, $sciezka . $id_poz);
        
        $listing_danych = new Listing();
        
        $tekst = '';

        for ($w = 0, $c = count($tablica_kat); $w < $c; $w++) {        

            if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $tablica_kat[$w]['id']) {
                $tekst .= '<tr class="pozycja_on" id="p_'.$tablica_kat[$w]['id'].'" data-sciezka="' . $sciezka . $id_poz . '">';
            } else {
                $tekst .= '<tr class="pozycja_off" id="p_'.$tablica_kat[$w]['id'].'" data-sciezka="' . $sciezka . $id_poz . '">';
            }

            $tablica = array();   
            
            $tablica[] = array($tablica_kat[$w]['id'] . '<input type="hidden" name="id[]" value="'.$tablica_kat[$w]['id'].'" />','center', '', 'class="CssId"');    

            $tablica[] = array(Funkcje::pokazObrazek($tablica_kat[$w]['image'], $tablica_kat[$w]['text'], '40', '40'),'center', '', 'class="CssZdjecie"'); 
            
            // ikonka            
            $tablica[] = array(((file_exists('../' . KATALOG_ZDJEC . '/' . $tablica_kat[$w]['ikona']) && !empty($tablica_kat[$w]['ikona'])) ? '<img src="/' . KATALOG_ZDJEC . '/' . $tablica_kat[$w]['ikona'] . '" alt="" />' : '-'),'center', '', 'class="CssIkona"');
                        
            $podkategorie = false;
            if ($tablica_kat[$w]['podkategorie'] == 'true') { $podkategorie = true; }            
            if ($podkategorie) {
                $tgm = '<img src="obrazki/rozwin.png" alt="Rozwiń" onclick="podkategorie(\''.$tablica_kat[$w]['id'].'\',false,\'' . $sciezka . $id_poz . '_\')" />';
             } else {
                $tgm = '-';
            }
            $tablica[] = array($tgm,'center', '',' id="img_'.$tablica_kat[$w]['id'].'" class="ImgCursor CssRozwin"');            

            $tablica[] = array(Kategorie::KategorieOdleglosc($tablica_kat[$w]['text'], $tablica_kat[$w]['path']), '', '', 'class="CssNazwa"');      

            // ile produktow dla kategorii
            $kategorie = $db->open_query("select COUNT('products_id') as ile_pozycji from products_to_categories where categories_id = '".$tablica_kat[$w]['id']."'");
            $infs = $kategorie->fetch_assoc();
            if ((int)$infs['ile_pozycji'] > 0) {
               $ile_produktow = $infs['ile_pozycji'];
            } else {
               $ile_produktow = '-';
            }
            $db->close_query($kategorie);

            $tablica[] = array($ile_produktow,'center', '', 'class="CssIlosc"'); 

            // ile aktywnych produktow dla kategorii
            $kategorie = $db->open_query("select COUNT('products_id') as ile_pozycji from products p, products_to_categories ptc where ptc.categories_id = '".$tablica_kat[$w]['id']."' and p.products_id = ptc.products_id and p.products_status = '1'");
            $infs = $kategorie->fetch_assoc();
            if ((int)$infs['ile_pozycji'] > 0) {
               $ile_produktow_aktywnych = $infs['ile_pozycji'];
            } else {
               $ile_produktow_aktywnych = '-';
            }         
            $db->close_query($kategorie);     

            $tablica[] = array($ile_produktow_aktywnych,'center', '', 'class="CssAktywne"');         
            
            unset($kategorie, $ile_produktow_aktywnych, $infs);            
            
            // sort
            $tablica[] = array('<input type="text" name="sort_'.$tablica_kat[$w]['id'].'" value="'.$tablica_kat[$w]['sort'].'" class="sort_prod" />','center', '', 'class="CssSort"'); 
            
            // aktywana czy nieaktywna
            if ($tablica_kat[$w]['status'] == '1') { $obraz = 'aktywny_on.png'; $alt = 'Ta kategoria jest aktywna'; } else { $obraz = 'aktywny_off.png'; $alt = 'Ta kategoria jest nieaktywna'; }              
            $tablica[] = array('<a class="TipChmurka" href="kategorie/kategorie_status.php?id_poz='.$tablica_kat[$w]['id'].'"><b>'.$alt.'</b><img src="obrazki/'.$obraz.'" alt="'.$alt.'" /></a>','center', '', 'class="CssStatus"');   
            
            // widoczna czy niewidoczna
            if ($tablica_kat[$w]['widocznosc'] == '1') { $obraz = 'aktywny_on.png'; $alt = 'Ta kategoria jest widoczna w liście kategorii'; } else { $obraz = 'aktywny_off.png'; $alt = 'Ta kategoria jest niewidoczna w liście kategorii'; }              
            $tablica[] = array('<a class="TipChmurka" href="kategorie/kategorie_widocznosc.php?id_poz='.$tablica_kat[$w]['id'].'"><b>'.$alt.'</b><img src="obrazki/'.$obraz.'" alt="'.$alt.'" /></a>','center', '', 'class="CssWidocznosc"');      
            
            // filtry
            if ($tablica_kat[$w]['filtry'] == '1') { $obraz = 'aktywny_on.png'; $alt = 'W tej kategorii są wyświetlane filtry'; } else { $obraz = 'aktywny_off.png'; $alt = 'W tej kategorii nie są wyświetalne filtry'; }              
            $tablica[] = array('<a class="TipChmurka" href="kategorie/kategorie_filtry.php?id_poz='.$tablica_kat[$w]['id'].'"><b>'.$alt.'</b><img src="obrazki/'.$obraz.'" alt="'.$alt.'" /></a>', 'center', '', 'class="CssFiltry"');                    
                        
            // kolor      
            if ( $tablica_kat[$w]['kolor_status'] == 1 ) {
                $tablica[] = array('<em class="TipChmurka"><b>W takim kolorze będzie wyświetlana nazwa w boxie kategorii</b><span class="KategorieKolor" style="background:#'.$tablica_kat[$w]['kolor'].'">&nbsp;</span></em>','center', '', 'class="CssKolor"');                    
              } else {
                $tablica[] = array('-','center', '', 'class="CssKolor"');                    
            }     

            // kolor tla  
            if ( $tablica_kat[$w]['kolor_tla_status'] == 1 ) {
                $tablica[] = array('<em class="TipChmurka"><b>W takim kolorze będzie wyświetlane tło nazwy kategorii w boxie kategorii</b><span class="KategorieKolor" style="background:#'.$tablica_kat[$w]['kolor_tla'].'">&nbsp;</span></em>','center', '', 'class="CssKolorTla"');                
              } else {
                $tablica[] = array('-','center', '', 'class="CssKolorTla"');              
            }              
            
            $tekst .= $listing_danych->pozycje($tablica);
            
            // zmienne do przekazania
            $zmienne_do_przekazania = '?id_poz='.$tablica_kat[$w]['id'];             
    
            $tekst .= '<td class="rg_right" style="width:10%">';
            
            $tekst .= '<a class="TipChmurka" href="kategorie/kategorie_duplikuj.php'.$zmienne_do_przekazania.'"><b>Duplikuj</b><img src="obrazki/duplikuj.png" alt="Duplikuj" /></a>';                   
            
            if ( (int)$ile_produktow > 0 ) {
                 $tekst .= '<a class="TipChmurka" href="kategorie/kategorie_przenies_produkty.php'.$zmienne_do_przekazania.'"><b>Przenieś produkty do innej kategorii</b><img src="obrazki/przenies_produkty.png" alt="Przenieś produkty do innej kategorii" /></a>';
            }
        
            $tekst .= '<a class="TipChmurka" href="kategorie/kategorie_przenies.php'.$zmienne_do_przekazania.'"><b>Przenieś</b><img src="obrazki/przenies.png" alt="Przenieś" /></a><br /><br />';
            $tekst .= '<a class="TipChmurka" href="kategorie/kategorie_edytuj.php'.$zmienne_do_przekazania.'"><b>Edytuj</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>'; 
            $tekst .= '<a class="TipChmurka" href="kategorie/kategorie_usun.php'.$zmienne_do_przekazania.'"><b>Skasuj</b><img src="obrazki/kasuj.png" alt="Skasuj" /></a>'; 
            $tekst .= '</td></tr>'; 

            if ($podkategorie) { 
                //$tekst .= '<tr><td colspan="13" class="PodkategorieRozwiniete"><div id="p_'.$tablica_kat[$w]['id'].'"></div></td></tr>';
            }            
            
            unset($tablica, $ile_produktow, $podkategorie);
        }
        //$tekst .= '</table>';
        //
        echo $tekst;
        //          
                        
    }
}
?>
