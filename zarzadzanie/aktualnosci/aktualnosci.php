<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {
  
    // tablica kategorii
    $tablica_kategorie = array();
    
    $sqls = $db->open_query('select distinct * from newsdesk_categories n, newsdesk_categories_description nd where n.categories_id = nd.categories_id and nd.language_id = "'.(int)$_SESSION['domyslny_jezyk']['id'].'" order by n.sort_order, nd.categories_name ');  
    
    if ((int)$db->ile_rekordow($sqls) > 0) {
        //
        while ($kategorie = $sqls->fetch_assoc()) {
            //
            $tablica_kategorie[$kategorie['categories_id']] = $kategorie;
            //
        }
        //
    }
    
    $db->close_query($sqls);

    Listing::postGet(basename($_SERVER['SCRIPT_NAME']));

    $warunki_szukania = '';
    // jezeli jest szukanie
    if (isset($_GET['szukaj']) && !empty($_GET['szukaj'])) {
        $szukana_wartosc = $filtr->process($_GET['szukaj']);
        $warunki_szukania = " and nd.newsdesk_article_name like '%".$szukana_wartosc."%'";
        unset($szukana_wartosc);
    }
    
    // jezeli jest wybrana kategoria
    if (isset($_GET['kategoria_id']) && (int)$_GET['kategoria_id'] > 0) {
        $id_kategorii = (int)$_GET['kategoria_id'];
        $warunki_szukania .= " and cd.categories_id = '".$id_kategorii."'";
        unset($id_kategorii);
    }
    
    // jezeli jest wybrana grupa klienta
    if (isset($_GET['klienci']) && (int)$_GET['klienci'] > 0) {
        $id_klienta = (int)$_GET['klienci'];
        $warunki_szukania .= " and find_in_set(" . $id_klienta . ", n.newsdesk_customers_group_id) ";        
        unset($id_klienta);
    }          

    if ( $warunki_szukania != '' ) {
      $warunki_szukania = preg_replace('/and/i', 'WHERE', $warunki_szukania, 1);
    }
    
    $zapytanie = 'SELECT DISTINCT
                         n.newsdesk_id,
                         n.newsdesk_image,
                         n.newsdesk_icon,
                         n.newsdesk_date_added,
                         n.newsdesk_author,
                         n.newsdesk_status,
                         n.newsdesk_comments_status,
                         n.newsdesk_customers_group_id,
                         nd.newsdesk_id,
                         nd.language_id,
                         nd.newsdesk_article_name,
                         nd.newsdesk_article_viewed,
                         (SELECT count(nc.newsdesk_comments_id) FROM newsdesk_comments nc WHERE n.newsdesk_id = nc.newsdesk_id) as ilosc_komentarzy
                  FROM newsdesk n
                         '.((isset($_GET['kategoria_id'])) ? 'LEFT JOIN newsdesk_to_categories cd ON n.newsdesk_id = cd.newsdesk_id' : '').'
                         LEFT JOIN newsdesk_description nd ON n.newsdesk_id = nd.newsdesk_id
                         AND nd.language_id = "'.(int)$_SESSION['domyslny_jezyk']['id'].'" ' . $warunki_szukania; 

    $sql = $db->open_query($zapytanie);

    // tworzenie paska do nastepnych okien - obliczanie ile bedzie podstron
    $ile_pozycji = (int)$db->ile_rekordow($sql); // ile jest wszystkich produktow
    $ile_licznika = ($ile_pozycji / ILOSC_WYNIKOW_NA_STRONIE);
    if ($ile_licznika == (int)$ile_licznika) { $ile_licznika = (int)$ile_licznika; } else { $ile_licznika = (int)$ile_licznika+1; }

    $db->close_query($sql);
    
    // jezeli jest sortowanie
    if (isset($_GET['sort'])) {
        switch ($_GET['sort']) {
            case "sort_a5":
                $sortowanie = 'nd.newsdesk_article_name asc';
                break;
            case "sort_a2":
                $sortowanie = 'nd.newsdesk_article_name desc';
                break;
            case "sort_a3":
                $sortowanie = 'n.newsdesk_status desc, nd.newsdesk_article_name';
                break;  
            case "sort_a4":
                $sortowanie = 'n.newsdesk_status asc, nd.newsdesk_article_name';
                break;                        
            case "sort_a1":
                $sortowanie = 'n.newsdesk_date_added desc, nd.newsdesk_article_name';
                break;
            case "sort_a6":
                $sortowanie = 'n.newsdesk_date_added asc, nd.newsdesk_article_name';
                break;                                                  
        }            
    } else { $sortowanie = 'n.newsdesk_date_added desc, nd.newsdesk_article_name'; }
    
    $zapytanie .= " order by ".$sortowanie;    

    // ******************************************************************************************************************************************************************
    // obsluga listingu AJAX
    if (isset($_GET['parametr'])) {

        if ($ile_pozycji > 0) {
        
            $zapytanie .= " limit ".$_GET['parametr'];   

            $sql = $db->open_query($zapytanie);
            
            $listing_danych = new Listing();
            
            $tablica_naglowek = array(
                                array('Akcja','center'),
                                array('ID','center'),
                                array('Zdjęcie','center'),
                                array('Ikona','center', '', 'class="ListingSchowaj"'),
                                array('Tytuł artykułu'),
                                array('Data dodania','center'),
                                array('Ilość wyświetleń<br />(dla domyślnego języka)','center', '', 'class="ListingSchowaj"'),
                                array('Grupa klientów', 'center'),
                                array('Komentarze', 'center'),
                                array('Pozwól na komentarze', 'center'),
                                array('Status','center'));
            
            echo $listing_danych->naglowek($tablica_naglowek);

            $tekst = '';
            while ($info = $sql->fetch_assoc()) {
                  
                  if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $info['newsdesk_id']) {
                     $tekst .= '<tr class="pozycja_on" id="sk_'.$info['newsdesk_id'].'">';
                   } else {
                     $tekst .= '<tr class="pozycja_off" id="sk_'.$info['newsdesk_id'].'">';
                  } 

                  $tablica = array();

                  $tablica[] = array('<input type="checkbox" style="border:0px" name="opcja[]" id="opcja_'.$info['newsdesk_id'].'" value="'.$info['newsdesk_id'].'" /><label class="OpisForPustyLabel" for="opcja_'.$info['newsdesk_id'].'"></label><input type="hidden" name="id[]" value="'.$info['newsdesk_id'].'" />','center');
                  
                  $tablica[] = array($info['newsdesk_id'],'center');

                  if ( !empty($info['newsdesk_image']) ) {
                       //
                       $tgm = '<div id="zoom'.rand(1,99999).'" class="imgzoom" onmouseover="ZoomIn(this,event)" onmouseout="ZoomOut(this)">';
                       $tgm .= '<div class="zoom" id="duze_foto_' . $info['newsdesk_id'] . '">' . Funkcje::pokazObrazek($info['newsdesk_image'], $info['newsdesk_article_name'], '250', '250') . '</div>';
                       $tgm .= '<div id="male_foto_' . $info['newsdesk_id'] . '">' . Funkcje::pokazObrazek($info['newsdesk_image'], $info['newsdesk_article_name'], '40', '40', ' class="Reload"', true) . '</div>';
                       $tgm .= '</div>';
                       //
                     } else { 
                       //
                       $tgm = '-';
                       //
                  }
                  
                  $tablica[] = array($tgm, 'center');    

                  // ikonka            
                  $tablica[] = array(((file_exists('../' . KATALOG_ZDJEC . '/' . $info['newsdesk_icon']) && !empty($info['newsdesk_icon'])) ? Funkcje::pokazObrazek($info['newsdesk_icon'], $info['newsdesk_article_name'], '40', '40', ' class="Reload"', true) : '-'),'center', '', 'class="ListingSchowaj"');
            
                  // tytul artykulu i kategoria do jakiej jest przypisany
                  $doJakiejKategoriiPrzypisany = '<span class="MaleInfoKat">';
                  $kategorie = $db->open_query("select distinct categories_id from newsdesk_to_categories where newsdesk_id = '".(int)$info['newsdesk_id']."'");
                  $id_kategorii = $kategorie->fetch_assoc();
                  //
                  // okreslenie nazwy kategorii
                  if (isset($id_kategorii['categories_id']) && (int)$id_kategorii['categories_id'] == '0') {
                      $doJakiejKategoriiPrzypisany .= 'Bez kategorii, ';
                    } else {
                      $kategoria_nazwa = $db->open_query("select distinct c.parent_id, c.categories_id, cd.categories_id, cd.categories_name from newsdesk_categories c, newsdesk_categories_description cd where c.categories_id = cd.categories_id and cd.categories_id = '".(int)$id_kategorii['categories_id']."' and cd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'");
                      $nazwa = $kategoria_nazwa->fetch_assoc();
                      //
                      // czy jest podkategoria
                      $nazwa_kat = '<span style="color:#ff0000">'.$nazwa['categories_name'].'</span>';
                      foreach ( $tablica_kategorie as $kategoria ) {
                          //
                          if ( $kategoria['categories_id'] == $nazwa['parent_id'] ) {
                               $nazwa_kat = $kategoria['categories_name'] . ' / ' . '<span style="color:#ff0000">'.$nazwa['categories_name'].'</span>';                                
                          } 
                          //
                      }                               
                      //
                      $doJakiejKategoriiPrzypisany .= $nazwa_kat.', ';
                      $db->close_query($kategoria_nazwa);
                      unset($nazwa_kat);
                  }
   
                  $doJakiejKategoriiPrzypisany = substr((string)$doJakiejKategoriiPrzypisany,0,-2);
                  $doJakiejKategoriiPrzypisany .= '</span>';
                  
                  $db->close_query($kategorie);
                  unset($kategorie);
                  
                  $autor = ((Funkcje::czyNiePuste($info['newsdesk_author'])) ? '<div class="Autor">Autor: '. $info['newsdesk_author'] . '</div>' : '');
                  
                  $tgm = '<b>'.$info['newsdesk_article_name'].'</b>' . $autor . $doJakiejKategoriiPrzypisany;
                  $tablica[] = array($tgm);
                  
                  unset($doJakiejKategoriiPrzypisany, $autor);
                  
                  // data dodania recenzji
                  $tablica[] = array(((Funkcje::czyNiePuste($info['newsdesk_date_added'])) ? date('d-m-Y',FunkcjeWlasnePHP::my_strtotime($info['newsdesk_date_added'])) : '-'),'center');
                  
                  $tablica[] = array($info['newsdesk_article_viewed'],'center', '', 'class="ListingSchowaj"');
                  
                  $tgm = '';
                  $tabGrup = explode(',', (string)$info['newsdesk_customers_group_id']);
                  if ( count($tabGrup) > 0 && $info['newsdesk_customers_group_id'] != 0 ) {
                       foreach ( $tabGrup as $idGrupy ) {
                          $tgm .= '<span class="GrupaKlientow">' . Klienci::pokazNazweGrupyKlientow($idGrupy) . '</span><br />';
                       }
                  }      
                  $tablica[] = array( (($tgm != '') ? $tgm : '-'),'center');
                  unset($tabGrup, $tgm);     

                  $tablica[] = array((($info['ilosc_komentarzy'] > 0) ? '<a class="TipChmurka" href="aktualnosci/aktualnosci_komentarze.php?art_id='.$info['newsdesk_id'].'"><b>Komentarze</b><img src="obrazki/opinia.png" alt="Komentarze" /></a><br />(' . $info['ilosc_komentarzy'] . ')' : '-'),'center');                  
                                      
                  // zmienne do przekazania
                  $zmienne_do_przekazania = '?id_poz='.$info['newsdesk_id'];      
                        
                  // zgoda na komentarze
                  if ($info['newsdesk_comments_status'] == '1') { $obraz = 'aktywny_on.png'; $alt = 'W tym artykule można dodać komentarze'; } else { $obraz = 'aktywny_off.png'; $alt = 'W tym artykule nie można dodawać komentarzy'; }               
                  $tablica[] = array('<a class="TipChmurka" href="aktualnosci/aktualnosci_status_komentarze.php'.$zmienne_do_przekazania.'"><b>'.$alt.'</b><img src="obrazki/'.$obraz.'" alt="'.$alt.'" /></a>','center');                    

                  // aktywany czy nieaktywny
                  $tablica[] = array('<input type="checkbox" style="border:0px" name="status_'.$info['newsdesk_id'].'" id="status_'.$info['newsdesk_id'].'" value="1" '.(($info['newsdesk_status'] == '1') ? 'checked="checked"' : '').' /><label class="OpisForPustyLabel" for="status_'.$info['newsdesk_id'].'"></label>','center');                                     

                  $tekst .= $listing_danych->pozycje($tablica);
              
                  $tekst .= '<td class="rg_right" IkonyPionowo">';
                  $tekst .= '<a class="TipChmurka" href="aktualnosci/aktualnosci_edytuj.php'.$zmienne_do_przekazania.'"><b>Edytuj</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>'; 
                  $tekst .= '<a class="TipChmurka" href="aktualnosci/aktualnosci_usun.php'.$zmienne_do_przekazania.'"><b>Skasuj</b><img src="obrazki/kasuj.png" alt="Skasuj" /></a>';
                  $tekst .= '<a class="TipChmurka" target="_blank" href="' . Seo::link_SEO( $info['newsdesk_article_name'], $info['newsdesk_id'], 'aktualnosc', '', false ) . '"><b>Zobacz w sklepie</b><img src="obrazki/zobacz.png" alt="Zobacz w sklepie" /></a>';
                  $tekst .= '</td></tr>';                  

                  unset($tablica);
            } 
            $tekst .= '</table>';
            
            //
            echo $tekst;
            //
            $db->close_query($sql);
            unset($listing_danych,$tekst,$tablica,$tablica_naglowek);   
             
        }
    }  

    // ******************************************************************************************************************************************************************
    // wyswietlanie listingu
    if (!isset($_GET['parametr'])) { 

        // wczytanie naglowka HTML
        include('naglowek.inc.php');
        ?>

        <div id="caly_listing">
        
            <div id="ajax"></div>
        
            <div id="naglowek_cont">Aktualności</div>
            
            <div id="wyszukaj">
                <form action="aktualnosci/aktualnosci.php" method="post" id="poForm" class="cmxform"> 
                
                <div id="wyszukaj_text">
                    <span>Wyszukaj artykuł:</span>
                    <input type="text" name="szukaj" id="szukaj" value="<?php echo ((isset($_GET['szukaj'])) ? Funkcje::formatujTekstInput($filtr->process($_GET['szukaj'])) : ''); ?>" size="30" />
                </div>  
                
                <div class="wyszukaj_select">
                    <span>Grupa klientów:</span>
                    <?php                         
                    echo Funkcje::RozwijaneMenu('klienci', Klienci::ListaGrupKlientow(true), ((isset($_GET['klienci'])) ? $filtr->process($_GET['klienci']) : '')); 
                    unset($tablica);
                    ?>
                </div>                 
                
                <?php 
                // tworzy ukryte pola hidden do wyszukiwania - filtra 
                if (isset($_GET['kategoria_id'])) { 
                    echo '<div><input type="hidden" name="kategoria_id" value="'.(int)$_GET['kategoria_id'].'" /></div>';
                }   
                if (isset($_GET['sort'])) { 
                    echo '<div><input type="hidden" name="sort" value="'.$filtr->process($_GET['sort']).'" /></div>';
                }                
                ?>
                
                <div class="wyszukaj_przycisk"><input type="image" alt="Szukaj" src="obrazki/ok.png" /></div>
                </form>
                
                <?php
                if ( Listing::wylaczFiltr(basename($_SERVER['SCRIPT_NAME'])) == true ) {
                  echo '<div id="wyszukaj_ikona"><a href="aktualnosci/aktualnosci.php?filtr=nie"><img src="obrazki/reset_szukaj.png" alt="Anuluj wyszukiwanie" /></a></div>';
                }
                ?>                 
                
                <div style="clear:both"></div>
            </div>        
            
            <form action="aktualnosci/aktualnosci_akcja.php" method="post" class="cmxform">
            
            <div id="sortowanie">
            
                <span>Sortowanie: </span>
                
                <a id="sort_a5" class="sortowanie" href="aktualnosci/aktualnosci.php?sort=sort_a5">nazwy rosnąco</a>
                <a id="sort_a2" class="sortowanie" href="aktualnosci/aktualnosci.php?sort=sort_a2">nazwy malejąco</a>
                <a id="sort_a3" class="sortowanie" href="aktualnosci/aktualnosci.php?sort=sort_a3">aktywne</a>
                <a id="sort_a4" class="sortowanie" href="aktualnosci/aktualnosci.php?sort=sort_a4">nieaktywne</a>
                <a id="sort_a1" class="sortowanie" href="aktualnosci/aktualnosci.php?sort=sort_a1">daty dodania rosnąco</a>
                <a id="sort_a6" class="sortowanie" href="aktualnosci/aktualnosci.php?sort=sort_a6">daty dodania malejąco</a>  
                
            </div>        
            
            <div style="clear:both;"></div>               

            <?php
            // przycisk dodania nowego artykulu
            ?>
            <div id="PozycjeIkon">
                <div>
                    <a class="dodaj" href="aktualnosci/aktualnosci_dodaj.php">dodaj nowy artykuł</a>
                </div>         
            </div>
            
            <div style="clear:both;"></div>            

            <div class="GlownyListing">

                <div class="GlownyListingKategorie">
                    
                    <div class="OknoKategoriiKontener">
                    
                        <div class="OknoNaglowek"><span class="RozwinKategorie">Kategorie</span></div>
                        
                            <div class="OknoKategorii" id="ListaKategorii">
                        
                                <table>
                                
                                <?php
                                if ( count($tablica_kategorie) ) {
                                     //
                                     foreach ( $tablica_kategorie as $kategorie ) {
                                          //
                                          if ( $kategorie['parent_id'] == 0 ) {
                                               //
                                               echo '<tr>';
                                               echo '<td style="width:50px;padding-top:8px;padding-bottom:8px" align="center">'.Funkcje::pokazObrazek($kategorie['categories_image'], $kategorie['categories_name'], '40', '40').'</td>';
                                               //
                                               $AktywnaKategoria = '';
                                               if (isset($_GET['kategoria_id'])) {
                                                   if ((int)$_GET['kategoria_id'] == $kategorie['categories_id']) {
                                                       $AktywnaKategoria = 'style="color:#ff0000"';
                                                   }
                                                }
                                               //
                                               echo '<td><a '.$AktywnaKategoria . ' href="aktualnosci/aktualnosci.php?kategoria_id='.$kategorie['categories_id'].'">'.$kategorie['categories_name'].'</a></td>'; 
                                               echo '<td style="text-align:right;white-space: nowrap;">';
                                               echo '   <a class="TipChmurka" href="aktualnosci/aktualnosci_kategorie_edytuj.php?kat_id='.$kategorie['categories_id'].'"><b>Edytuj</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>'; 
                                               echo '   <a class="TipChmurka" href="aktualnosci/aktualnosci_kategorie_usun.php?kat_id='.$kategorie['categories_id'].'"><b>Skasuj</b><img src="obrazki/kasuj.png" alt="Skasuj" /></a>';                                    
                                               echo '</td>';
                                               echo '</tr>';                                          
                                               //
                                               // czy ma podkategorie
                                               foreach ( $tablica_kategorie as $podkategorie ) {
                                               
                                                    if ( $podkategorie['parent_id'] == $kategorie['categories_id'] ) {
                                                          
                                                         echo '<tr><td colspan="3" style="padding:1px 0px 1px 40px">';
                                                         
                                                         echo '<table><tr><td style="width:50px" align="center">'.Funkcje::pokazObrazek($podkategorie['categories_image'], $podkategorie['categories_name'], '40', '40').'</td>';
                                                         //
                                                         $AktywnaKategoria = '';
                                                         if (isset($_GET['kategoria_id'])) {
                                                             if ((int)$_GET['kategoria_id'] == $podkategorie['categories_id']) {
                                                                 $AktywnaKategoria = 'style="color:#ff0000"';
                                                             }
                                                         }
                                                         //
                                                         echo '<td><a '.$AktywnaKategoria . ' href="aktualnosci/aktualnosci.php?kategoria_id='.$podkategorie['categories_id'].'">'.$podkategorie['categories_name'].'</a></td>'; 
                                                         echo '<td style="text-align:right;white-space: nowrap;">';
                                                         echo '   <a class="TipChmurka" href="aktualnosci/aktualnosci_kategorie_edytuj.php?kat_id='.$podkategorie['categories_id'].'"><b>Edytuj</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>'; 
                                                         echo '   <a class="TipChmurka" href="aktualnosci/aktualnosci_kategorie_usun.php?kat_id='.$podkategorie['categories_id'].'"><b>Skasuj</b><img src="obrazki/kasuj.png" alt="Skasuj" /></a>';                                    
                                                         echo '</td>';
                                                         echo '</tr></table>';
                                                         
                                                         echo '</td></tr>';
                                                          
                                                    } 
                                               }                                               
                                          }
                                          //
                                     }
                                     //
                                  } else { 
                                    //
                                    echo '<tr><td class="BrakKategorii">Brak kategorii</td></tr>';
                                    //
                                }

                                ?>
                                </table>
                                
                            </div> 
                            
                        </div>
                             
                        <div class="DodajKategorie">
                            <a class="dodaj" href="aktualnosci/aktualnosci_kategorie_dodaj.php">dodaj nową kategorię</a>
                        </div>                         
                        
                    </div>
                    
                    <div style="GlownyListingProdukty">
                    
                        <div id="wynik_zapytania" class="WynikZapytania"></div>
                        <div id="aktualna_pozycja">1</div>

                        <div id="akcja" class="AkcjaOdstep">
                            <div class="lf"><img src="obrazki/strzalka.png" alt="" /></div>
                            <div class="lf" style="padding-right:20px">
                                <span onclick="akcja(1)">zaznacz wszystkie</span>
                                <span onclick="akcja(2)">odznacz wszystkie</span>
                            </div>
               
                            <div id="akc">
                            
                                Wykonaj akcje: 
                                
                                <select name="akcja_dolna" id="akcja_dolna">
                                    <option value="0"></option>
                                    <option value="1">zmień status zaznaczonych na nieaktywne</option>
                                    <option value="2">zmień status zaznaczonych na aktywne</option>
                                    <option value="3">usuń zaznaczone aktualności</option>
                                </select>
                                
                            </div>
                            
                            <div style="clear:both;"></div>
                            
                        </div>                          
                        
                        <div id="dolny_pasek_stron" class="AkcjaOdstep"></div>
                        <div id="pokaz_ile_pozycji" class="AkcjaOdstep"></div>
                        <div id="ile_rekordow"><?php echo $ile_pozycji; ?></div>
                        
                    </div>
                    
                </div>
                
                <?php if ($ile_pozycji > 0) { ?>
                <div id="zapis"><input type="submit" class="przyciskBut" value="Zapisz zmiany" /></div>
                <?php } ?>                
                
            </table>
            
            </form>
            
            <script>
            $("#wynik_zapytania").html('<div style="padding:10px">Trwa ładowanie danych ...</div>');
            var skocz = '<?php echo ((isset($_GET['id_poz']) && (int)$_GET['id_poz'] > 0) ? (int)$_GET['id_poz'] : ''); ?>';
            <?php Listing::pokazAjax('aktualnosci/aktualnosci.php', $zapytanie, $ile_licznika, $ile_pozycji, 'newsdesk_id'); ?>
            </script>                

        </div>     

        <?php include('stopka.inc.php'); ?>

    <?php 
    } 
    
}
?>
