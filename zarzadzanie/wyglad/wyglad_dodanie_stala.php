<?php
chdir('../');             

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone && Sesje::TokenSpr()) {

    if ($_GET['p'] == 'lista') {
    
        $sameLinki = false;
        $textLinki = '';
    
        // pobieranie pol jakie sa w stalej w bazie do sprawdzenia czy takie pole nie jest dodane
        $sqlp = $db->open_query("select distinct * from settings where code = '" . strtoupper((string)$_GET['div']) . "'");
        $infc = $sqlp->fetch_assoc();
        $ciagStalej = ','.$infc['value'];
        $tmpCiag = explode(',', (string)$ciagStalej);
        $db->close_query($sqlp); 
        unset($infc);     

        $zmiennaDoSprawdzaniaCzyCosZostalo = 0;

        // pobieranie stron informacyjnych
        $sqls = $db->open_query("select p.pages_id, pd.pages_title from pages p, pages_description pd where p.pages_id = pd.pages_id and pages_modul = '0' and language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'");

        $tablica = array();
        $tablica[] = array('id' => 0, 'text' => '... wybierz stronę informacyjną ...');
        while ($infs = $sqls->fetch_assoc()) { 
            if (!in_array('strona;'.$infs['pages_id'], $tmpCiag)) {
                $tablica[] = array('id' => $infs['pages_id'], 'text' => $infs['pages_title'] . ((!empty($infs['link'])) ? ' ( link zewnętrzny )' : '' ));
            }
        }
        $db->close_query($sqls); 
        unset($zapytanie_tmp, $infs);    
        //      
        if (count($tablica) > 1) {
            $textLinki .= '<div style="padding:5px">';
            $textLinki .= Funkcje::RozwijaneMenu('strony', $tablica, '', ' onchange="wybierz_stala(this.value,\'strona\',\''.$_GET['div'].'\')" style="width:430px; max-width:430px;"');
            $textLinki .= '</div>';
            unset($tablica);
            //
            $zmiennaDoSprawdzaniaCzyCosZostalo++;
            $sameLinki = true;
        }
        
        // pobieranie galerii
        $sqls = $db->open_query("select distinct id_gallery, gallery_name from gallery_description pd where pd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'");
        
        $tablica = array();
        $tablica[] = array('id' => 0, 'text' => '... wybierz galerię ...');
        while ($infs = $sqls->fetch_assoc()) { 
            if (!in_array('galeria;'.$infs['id_gallery'], $tmpCiag)) {
                $tablica[] = array('id' => $infs['id_gallery'], 'text' => $infs['gallery_name']);
            }
        }
        $db->close_query($sqls); 
        unset($zapytanie_tmp, $infs);    
        //      
        if (count($tablica) > 1) {
            $textLinki .= '<div style="padding:5px">';
            $textLinki .= Funkcje::RozwijaneMenu('galerie', $tablica, '', ' onchange="wybierz_stala(this.value,\'galeria\',\''.$_GET['div'].'\')" style="width:430px; max-width:430px;"');
            $textLinki .= '</div>';
            unset($tablica);
            //
            $zmiennaDoSprawdzaniaCzyCosZostalo++;
            $sameLinki = true;
        }            

        // pobieranie formularza
        $sqls = $db->open_query("select distinct id_form, form_name from form_description pd where pd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'");
        
        $tablica = array();
        $tablica[] = array('id' => 0, 'text' => '... wybierz formularz ...');
        while ($infs = $sqls->fetch_assoc()) { 
            if (!in_array('formularz;'.$infs['id_form'], $tmpCiag)) {
                $tablica[] = array('id' => $infs['id_form'], 'text' => $infs['form_name']);
            }
        }
        $db->close_query($sqls); 
        unset($zapytanie_tmp, $infs);    
        //      
        if (count($tablica) > 1) {
            $textLinki .= '<div style="padding:5px">';
            $textLinki .= Funkcje::RozwijaneMenu('formularze', $tablica, '', ' onchange="wybierz_stala(this.value,\'formularz\',\''.$_GET['div'].'\')" style="width:430px; max-width:430px;"');
            $textLinki .= '</div>';        
            //
            $zmiennaDoSprawdzaniaCzyCosZostalo++;
            $sameLinki = true;
        }   
        
        // pobieranie nazw kategorii aktualnosci
        $sqls = $db->open_query("select distinct categories_id,	categories_name from newsdesk_categories_description pd where pd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'");
        
        $tablica = array();
        $tablica[] = array('id' => 0, 'text' => '... wybierz kategorię aktualności ...');
        while ($infs = $sqls->fetch_assoc()) { 
            if (!in_array('kategoria;'.$infs['categories_id'], $tmpCiag)) {
                //
                $nazwa_kat = $infs['categories_name'];
                foreach ( BoxyModuly::TablicaKategoriiAktualnosci() as $tmp ) {
                    //
                    if ( $infs['categories_id'] == $tmp['categories_id'] && $tmp['parent_id'] > 0 ) {
                         //
                         foreach ( BoxyModuly::TablicaKategoriiAktualnosci() as $tmp_parent ) {
                              //
                              if ( $tmp['parent_id'] == $tmp_parent['categories_id'] ) {
                                   //
                                   $nazwa_kat = $tmp_parent['categories_name'] . ' / ' . $nazwa_kat;
                                   //
                              }
                              //
                         }
                         //
                    } 
                    //
                }                                  
                $tablica[] = array('id' => $infs['categories_id'], 'text' => $nazwa_kat);
                unset($nazwa_kat);
                //
            }
        }
        $db->close_query($sqls); 
        unset($zapytanie_tmp, $infs);    
        //      
        if (count($tablica) > 1) {
            //
            // sortowanie tablicy po nazwach
            $tablica_tmp = array();
            foreach ($tablica as $klucz => $wartosc) {
                $tablica_tmp[$klucz] = $wartosc['text'];
            }
            array_multisort($tablica_tmp, SORT_ASC, $tablica);
            //
            $textLinki .= '<div style="padding:5px">';
            $textLinki .= Funkcje::RozwijaneMenu('kategoria', $tablica, '', ' onchange="wybierz_stala(this.value,\'kategoria\',\''.$_GET['div'].'\')" style="width:430px; max-width:430px;"');
            $textLinki .= '</div>';        
            //
            $zmiennaDoSprawdzaniaCzyCosZostalo++;
            $sameLinki = true;
        }           
        
        // pobieranie nazw aktualnosci
        $sqls = $db->open_query("select distinct newsdesk_id,	newsdesk_article_name from newsdesk_description pd where pd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'");
        
        $tablica = array();
        $tablica[] = array('id' => 0, 'text' => '... wybierz aktualności ...');
        while ($infs = $sqls->fetch_assoc()) { 
            if (!in_array('artykul;'.$infs['newsdesk_id'], $tmpCiag)) {
                $tablica[] = array('id' => $infs['newsdesk_id'], 'text' => $infs['newsdesk_article_name']);
            }
        }
        $db->close_query($sqls); 
        unset($zapytanie_tmp, $infs);    
        //      
        if (count($tablica) > 1) {
            $textLinki .= '<div style="padding:5px">';
            $textLinki .= Funkcje::RozwijaneMenu('artykul', $tablica, '', ' onchange="wybierz_stala(this.value,\'artykul\',\''.$_GET['div'].'\')" style="width:430px; max-width:430px;"');
            $textLinki .= '</div>';        
            //
            $zmiennaDoSprawdzaniaCzyCosZostalo++;
            $sameLinki = true;
        } 
        
        // pobieranie nazw kategorii produktow
        $sqls = $db->open_query("select distinct c.categories_id, cd.categories_name from categories_description cd, categories c where c.categories_id = cd.categories_id and c.parent_id = '0' and cd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'");
        
        $tablica = array();
        $tablica[] = array('id' => 0, 'text' => '... wybierz kategorię produktów ...');
        while ($infs = $sqls->fetch_assoc()) { 
            if (!in_array('kategproduktow;'.$infs['categories_id'], $tmpCiag)) {
                $tablica[] = array('id' => $infs['categories_id'], 'text' => $infs['categories_name']);
            }
        }
        $db->close_query($sqls); 
        unset($zapytanie_tmp, $infs);    
        //      
        if (count($tablica) > 1) {
            $textLinki .= '<div style="padding:5px">';
            $textLinki .= Funkcje::RozwijaneMenu('kategproduktow', $tablica, '', ' onchange="wybierz_stala(this.value,\'kategproduktow\',\''.$_GET['div'].'\')" style="width:430px; max-width:430px;"');
            $textLinki .= '</div>';        
            //
            $zmiennaDoSprawdzaniaCzyCosZostalo++;
            $sameLinki = true;
        }              
                
        if ( $sameLinki == true ) {
            //
            $textLinki = '<strong class="OknaRozwijane" style="margin-top:3px; margin-bottom:3px"><span id="okno_1" class="RozwinOkno">Pojedyncze linki menu</span></strong><div id="tresc_okno_1" style="display:none">' . $textLinki . '</div>';
            //
        }
        
        unset($sameLinki);
        
        // tylko dla gornego menu
        
        $textOkna = '';
        
        if ( $_GET['div'] == 'gorne_menu' ) {
        
            $sameOkna = false;

            // pobieranie nazw grup stron informacyjnych
            $sqls = $db->open_query("select pg.pages_group_id,
                                            pg.pages_group_code,
                                            pg.pages_group_title,
                                            pgd.pages_group_name
                                       from pages_group pg left join pages_group_description pgd on pg.pages_group_id = pgd.pages_group_id and pgd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'");
            
            $tablica = array();
            $tablica[] = array('id' => 0, 'text' => '... wybierz grupę stron informacyjnych ...');
            while ($infs = $sqls->fetch_assoc()) { 
                if (!in_array('grupainfo;'.$infs['pages_group_id'], $tmpCiag)) {
                    $tablica[] = array('id' => $infs['pages_group_id'], 'text' => $infs['pages_group_code'] . ' - ' . $infs['pages_group_name']);
                }
            }
            $db->close_query($sqls); 
            unset($zapytanie_tmp, $infs);    
            //      
            if (count($tablica) > 1) {
                $textOkna .= '<div style="padding:5px">';
                $textOkna .= Funkcje::RozwijaneMenu('grupainfo', $tablica, '', ' onchange="wybierz_stala(this.value,\'grupainfo\',\''.$_GET['div'].'\')" style="width:430px; max-width:430px;"');
                $textOkna .= '</div>';        
                //
                $zmiennaDoSprawdzaniaCzyCosZostalo++;
                $sameOkna = true;
            }   

            // pobieranie nazw kategorii aktualnosci
            $sqls = $db->open_query("select distinct categories_id,	categories_name from newsdesk_categories_description pd where pd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'");
            
            $tablica = array();
            $tablica[] = array('id' => 0, 'text' => '... wybierz kategorię aktualności ...');
            while ($infs = $sqls->fetch_assoc()) { 
                if (!in_array('artkategorie;'.$infs['categories_id'], $tmpCiag)) {
                    //
                    $nazwa_kat = $infs['categories_name'];
                    foreach ( BoxyModuly::TablicaKategoriiAktualnosci() as $tmp ) {
                        //
                        if ( $infs['categories_id'] == $tmp['categories_id'] && $tmp['parent_id'] > 0 ) {
                             //
                             foreach ( BoxyModuly::TablicaKategoriiAktualnosci() as $tmp_parent ) {
                                  //
                                  if ( $tmp['parent_id'] == $tmp_parent['categories_id'] ) {
                                       //
                                       $nazwa_kat = $tmp_parent['categories_name'] . ' / ' . $nazwa_kat;
                                       //
                                  }
                                  //
                             }
                             //
                        } 
                        //
                    }                                  
                    $tablica[] = array('id' => $infs['categories_id'], 'text' => $nazwa_kat);
                    unset($nazwa_kat);
                    //                  
                }
            }
            $db->close_query($sqls); 
            unset($zapytanie_tmp, $infs);    
            //      
            if (count($tablica) > 1) {
                //
                // sortowanie tablicy po nazwach
                $tablica_tmp = array();
                foreach ($tablica as $klucz => $wartosc) {
                    $tablica_tmp[$klucz] = $wartosc['text'];
                }
                array_multisort($tablica_tmp, SORT_ASC, $tablica);
                //              
                $textOkna .= '<div style="padding:5px">';
                $textOkna .= Funkcje::RozwijaneMenu('artkategorie', $tablica, '', ' onchange="wybierz_stala(this.value,\'artkategorie\',\''.$_GET['div'].'\')" style="width:430px; max-width:430px;"');
                $textOkna .= '</div>';        
                //
                $zmiennaDoSprawdzaniaCzyCosZostalo++;
                $sameOkna = true;
            }    

            // pobieranie nazw kategorii produktow
            $sqls = $db->open_query("select distinct c.categories_id, cd.categories_name from categories_description cd, categories c where c.categories_id = cd.categories_id and c.parent_id = '0' and cd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'");
            
            $tablica = array();
            $tablica[] = array('id' => 0, 'text' => '... wybierz kategorię produktów ...');
            while ($infs = $sqls->fetch_assoc()) { 
                if (!in_array('prodkategorie;'.$infs['categories_id'], $tmpCiag)) {
                    $tablica[] = array('id' => $infs['categories_id'], 'text' => $infs['categories_name']);
                }
            }
            $db->close_query($sqls); 
            unset($zapytanie_tmp, $infs);    
            //      
            if (count($tablica) > 1) {
                $textOkna .= '<div style="padding:5px">';
                $textOkna .= Funkcje::RozwijaneMenu('prodkategorie', $tablica, '', ' onchange="wybierz_stala(this.value,\'prodkategorie\',\''.$_GET['div'].'\')" style="width:430px; max-width:430px;"');
                $textOkna .= '</div>';        
                //
                $zmiennaDoSprawdzaniaCzyCosZostalo++;
                $sameOkna = true;
            }
            
            if ( Wyglad::TypSzablonu() == true ) {

                // pobieranie nazw dowolnych tresci
                $sqls = $db->open_query("select ac.id_any_content,
                                               acd.any_content_name
                                          from any_content ac left join any_content_description acd on ac.id_any_content = acd.id_any_content and acd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'");
                
                $tablica = array();
                $tablica[] = array('id' => 0, 'text' => '... wybierz dowolną treść ...');
                while ($infs = $sqls->fetch_assoc()) { 
                    if (!in_array('dowolnatresc;'.$infs['id_any_content'], $tmpCiag)) {
                        $tablica[] = array('id' => $infs['id_any_content'], 'text' => $infs['any_content_name']);
                    }
                }
                $db->close_query($sqls); 
                unset($zapytanie_tmp, $infs);    
            
            }
            
            if (count($tablica) > 1) {
                $textOkna .= '<div style="padding:5px">';
                $textOkna .= Funkcje::RozwijaneMenu('dowolnatresc', $tablica, '', ' onchange="wybierz_stala(this.value,\'dowolnatresc\',\''.$_GET['div'].'\')" style="width:430px; max-width:430px;"');
                $textOkna .= '</div>';        
                //
                $zmiennaDoSprawdzaniaCzyCosZostalo++;
                $sameOkna = true;
            }               

            if ( $sameOkna == true ) {
                //  
                $textOkna = '<strong class="OknaRozwijane" style="margin-top:3px; margin-bottom:3px"><span class="RozwinOkno" id="okno_2">W postaci rozwijanych okien</span></strong><div id="tresc_okno_2" style="display:none">' . $textOkna . '</div>';
                //
            }
            
            unset($sameOkna);
                
        }
        
        if ($zmiennaDoSprawdzaniaCzyCosZostalo == 0) {
            $textLinki .= '<div style="padding:5px">Brak danych do dodania ...</div>';
        }  
        
        $textLinkBezposredni = '<div id="DaneLinkuZew">';
        $textLinkBezposredni .= '<strong class="OknaRozwijane" style="margin-top:3px; margin-bottom:3px"><span class="RozwinOkno" id="okno_3">Bezpośredni link do innej strony</span></strong>';
        $textLinkBezposredni .= '<div id="tresc_okno_3" style="display:none">';
        $textLinkBezposredni .= '<input type="text" value="" size="20" style="width:408px; max-width:408px; padding:8px 10px 8px 10px" id="linkbezposredni" name="linkbezposredni" placeholder="... wpisz bezpośredni link do strony www ..." />'; 
        $textLinkBezposredni .= '<div style="text-align:left; width:430px; max-width:430px; padding-top:8px; margin:0px auto"><input type="checkbox" value="1" name="nowa_strona" id="nowa_strona" /><label class="OpisFor" for="nowa_strona">otwieranie na nowej karcie przeglądarki</label></div>';        
        $textLinkBezposredni .= '<table style="width:430px; max-width:430px; margin-top:6px;">
                                      <tr>
                                          <td style="text-align:left"><ul>';
                                                                              
                                          foreach (Funkcje::TablicaJezykow() as $PoleJezyka) {
                                              $textLinkBezposredni .= '<li><img src="../' . KATALOG_ZDJEC . '/' . $PoleJezyka['foto'] . '" alt="" /><input type="text" size="20" style="width:220px" value="" name="jezyk_' . $PoleJezyka['id'] . '" id="jezyk_' . $PoleJezyka['id'] . '" placeholder="... nazwa w języku: ' . strtolower((string)$PoleJezyka['text']) . ' ..." /></li>';
                                          } 

                                          $textLinkBezposredni .= '</ul></td style="text-align:center">
                                          <td style="width:150px">                                              
                                              <button type="button" class="przyciskNon" style="padding:8px" onclick="wybierz_stala_inne(\''.$_GET['div'].'\',\'linkbezposredni\',\'DaneLinkuZew\')" >Dodaj link</button>
                                          </td>
                                      </tr>
                                 </table>';
        $textLinkBezposredni .= '</div></div>'; 

        $textMenuWszystkieKategorie = '';
        $textMenuWszyscyProducenci = '';
        $textPozycjaBanery = '';
        
        if ( $_GET['div'] == 'gorne_menu' ) {
          
            if ( strpos((string)GORNE_MENU, 'linkwszystkiekategorie') === false ) {
        
                $textMenuWszystkieKategorie = '<div id="DaneWszystkieKategorie">';
                $textMenuWszystkieKategorie .= '<strong class="OknaRozwijane" style="margin-top:3px; margin-bottom:3px"><span class="RozwinOkno" id="okno_4">Menu kategorii sklepu</span></strong>';
                $textMenuWszystkieKategorie .= '<div id="tresc_okno_4" style="display:none">';       
                $textMenuWszystkieKategorie .= '<table style="width:430px; max-width:430px; margin-top:6px;">
                                               <tr><td colspan="2"><div class="maleInfo">Wpisz jak ma się nazywać pozycja w menu np OFERTA, KATEGORIE</div></td></tr>
                                               <tr>
                                                  <td style="text-align:left"><ul>';
                                                                                      
                                                  foreach (Funkcje::TablicaJezykow() as $PoleJezyka) {
                                                      $textMenuWszystkieKategorie .= '<li><img src="../' . KATALOG_ZDJEC . '/' . $PoleJezyka['foto'] . '" alt="" /><input type="text" size="20" style="width:220px" value="" name="menu_kategorie_jezyk_' . $PoleJezyka['id'] . '" id="menu_kategorie_jezyk_' . $PoleJezyka['id'] . '" placeholder="... nazwa w języku: ' . strtolower((string)$PoleJezyka['text']) . ' ..." /></li>';
                                                  } 

                                                  $textMenuWszystkieKategorie .= '</ul></td style="text-align:center">
                                                  <td style="width:150px">                                              
                                                      <button type="button" class="przyciskNon" style="padding:8px" onclick="wybierz_stala_inne(\''.$_GET['div'].'\',\'linkwszystkiekategorie\',\'DaneWszystkieKategorie\')" >Dodaj pozycję</button>
                                                  </td>
                                               </tr>
                                         </table>';
                $textMenuWszystkieKategorie .= '</div></div>'; 
                
            }

            if ( strpos((string)GORNE_MENU, 'linkwszyscyproducenci') === false ) {
        
                $textMenuWszyscyProducenci = '<div id="DaneWszyscyProducenci">';
                $textMenuWszyscyProducenci .= '<strong class="OknaRozwijane" style="margin-top:3px; margin-bottom:3px"><span class="RozwinOkno" id="okno_5">Menu producentów w sklepie</span></strong>';
                $textMenuWszyscyProducenci .= '<div id="tresc_okno_5" style="display:none">';       
                $textMenuWszyscyProducenci .= '<table style="width:430px; max-width:430px; margin-top:6px;">
                                               <tr><td colspan="2"><div class="maleInfo">Wpisz jak ma się nazywać pozycja w menu np PRODUCENCI, MARKI</div></td></tr>
                                               <tr>
                                                  <td style="text-align:left"><ul>';
                                                                                      
                                                  foreach (Funkcje::TablicaJezykow() as $PoleJezyka) {
                                                      $textMenuWszyscyProducenci .= '<li><img src="../' . KATALOG_ZDJEC . '/' . $PoleJezyka['foto'] . '" alt="" /><input type="text" size="20" style="width:220px" value="" name="menu_producenci_jezyk_' . $PoleJezyka['id'] . '" id="menu_producenci_jezyk_' . $PoleJezyka['id'] . '" placeholder="... nazwa w języku: ' . strtolower((string)$PoleJezyka['text']) . ' ..." /></li>';
                                                  } 

                                                  $textMenuWszyscyProducenci .= '</ul></td style="text-align:center">
                                                  <td style="width:150px">                                              
                                                      <button type="button" class="przyciskNon" style="padding:8px" onclick="wybierz_stala_inne(\''.$_GET['div'].'\',\'linkwszyscyproducenci\',\'DaneWszyscyProducenci\')" >Dodaj pozycję</button>
                                                  </td>
                                               </tr>
                                         </table>';
                $textMenuWszyscyProducenci .= '</div></div>'; 
                
            }           

            if ( Wyglad::TypSzablonu() == true ) {
        
                $textPozycjaBanery = '<div id="DanePozycjaDowolnyTekst">';
                $textPozycjaBanery .= '<strong class="OknaRozwijane" style="margin-top:3px; margin-bottom:3px"><span class="RozwinOkno" id="okno_3a">Pozycja z bannerami graficznymi</span></strong>';
                $textPozycjaBanery .= '<div id="tresc_okno_3a" style="display:none">';
                $textPozycjaBanery .= '<table style="width:430px; max-width:430px; margin-top:6px;">
                                              <tr>
                                                  <td style="text-align:left"><ul>';
                                                                                      
                                                  foreach (Funkcje::TablicaJezykow() as $PoleJezyka) {
                                                      $textPozycjaBanery .= '<li><img src="../' . KATALOG_ZDJEC . '/' . $PoleJezyka['foto'] . '" alt="" /><input type="text" size="20" style="width:220px" value="" name="jezyk_bannery_' . $PoleJezyka['id'] . '" id="jezyk_bannery_' . $PoleJezyka['id'] . '" placeholder="... nazwa w języku: ' . strtolower((string)$PoleJezyka['text']) . ' ..." /></li>';
                                                  } 

                                                  $textPozycjaBanery .= '</ul></td style="text-align:center">
                                                  <td style="width:150px">                                              
                                                      <button type="button" class="przyciskNon" style="padding:8px" onclick="wybierz_stala_inne(\''.$_GET['div'].'\',\'pozycjabannery\',\'DanePozycjaBanery\')" >Dodaj pozycję</button>
                                                  </td>
                                              </tr>
                                         </table>';
                $textPozycjaBanery .= '</div></div>';  

            }                
        
        }            

        echo $textLinki . $textOkna . $textLinkBezposredni . $textPozycjaBanery . $textMenuWszystkieKategorie . $textMenuWszyscyProducenci;

        unset($textLinki, $textOkna, $tablica, $zmiennaDoSprawdzaniaCzyCosZostalo, $textMenuWszystkieKategorie, $textMenuWszyscyProducenci);        
    }
    
    if ($_GET['p'] == 'dodaj') {
    
        $nazwaDowyswietlania = '';
        $edycjaElementu = '';    
        
        $konfig_menu = array();
        
        if ( strpos((string)MENU_PODKATEGORIE, '{') > -1 ) {
             //
             $podTmp = @unserialize(MENU_PODKATEGORIE);
             //
             if ( is_array($podTmp) ) {
                  //
                  $konfig_menu = $podTmp;
                  //
             }
             //
             unset($podTmp);
             //
        }        
    
        switch ($_GET['rodzaj']) {
            case "strona":
                $sqls = $db->open_query("select * from pages p, pages_description pd where p.pages_id = pd.pages_id and pd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."' and p.pages_id = '".(int)$_GET['id']."'");
                $infs = $sqls->fetch_assoc();
                
                if ( $_GET['div'] != 'gorne_menu' ) {                  
                     $nazwaDowyswietlania = '<span class="StronaInfo">'.$infs['pages_title'].((!empty($infs['link'])) ? ' <span>( link zewnętrzny poprzez stronę informacyjną: '.$infs['link'].' )</span>' : '<span>( link do strony informacyjnej )</span>' ).'</span>';
                } else {
                     $nazwaDowyswietlania = Wyglad::KonfiguracjaPozycjiGornegoMenu($konfig_menu, $infs['pages_title'], '( link do strony informacyjnej )', $infs['pages_id'], 'strona', 'StronaInfo');
                     $nazwaDowyswietlania .= '<script>zmienMenuPodkat(\'' . $infs['pages_id'] . '_strona\')</script>';
                }
                
                $edycjaElementu = '<a class="TipChmurka" href="strony_informacyjne/strony_informacyjne_edytuj.php?id_poz=' . $infs['pages_id'] . '&amp;zakladka=3"><b>Edytuj pozycję</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>';
                $idDoDiva = (int)$_GET['id'].'strona';
                $db->close_query($sqls); 
                unset($infs);                                
                break; 
            case "galeria":
                $sqls = $db->open_query("select * from gallery p, gallery_description pd where p.id_gallery = pd.id_gallery and pd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."' and p.id_gallery = '".(int)$_GET['id']."'");
                $infs = $sqls->fetch_assoc();
                
                if ( $_GET['div'] != 'gorne_menu' ) {                  
                     $nazwaDowyswietlania = '<span class="Galeria">'.$infs['gallery_name'].'<span>( link do galerii )</span></span>';
                } else {
                     $nazwaDowyswietlania = Wyglad::KonfiguracjaPozycjiGornegoMenu($konfig_menu, $infs['gallery_name'], '( link do galerii )', $infs['id_gallery'], 'galeria', 'Galeria');
                     $nazwaDowyswietlania .= '<script>zmienMenuPodkat(\'' . $infs['id_gallery'] . '_galeria\')</script>';
                }                

                $edycjaElementu = '<a class="TipChmurka" href="galerie/galerie_edytuj.php?id_poz=' . $infs['id_gallery'] . '&amp;zakladka=3"><b>Edytuj pozycję</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>';
                $idDoDiva = (int)$_GET['id'].'galeria';
                $db->close_query($sqls); 
                unset($infs);                                
                break; 
            case "formularz":
                $sqls = $db->open_query("select * from form p, form_description pd where p.id_form = pd.id_form and pd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."' and p.id_form = '".(int)$_GET['id']."'");
                $infs = $sqls->fetch_assoc();
                
                if ( $_GET['div'] != 'gorne_menu' ) {                  
                     $nazwaDowyswietlania = '<span class="Formularz">'.$infs['form_name'].'<span>( link do formularza )</span></span>';
                } else {
                     $nazwaDowyswietlania = Wyglad::KonfiguracjaPozycjiGornegoMenu($konfig_menu, $infs['form_name'], '( link do formularza )', $infs['id_form'], 'formularz', 'Formularz');
                     $nazwaDowyswietlania .= '<script>zmienMenuPodkat(\'' . $infs['id_form'] . '_formularz\')</script>';
                }                  
                
                $edycjaElementu = '<a class="TipChmurka" href="formularze/formularze_edytuj.php?id_poz=' . $infs['id_form'] . '&amp;zakladka=3"><b>Edytuj pozycję</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>';
                $idDoDiva = (int)$_GET['id'].'formularz';
                $db->close_query($sqls); 
                unset($infs);                                
                break; 
            case "kategoria":
                $sqls = $db->open_query("select * from newsdesk_categories n, newsdesk_categories_description nd where n.categories_id = nd.categories_id and nd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."' and n.categories_id = '".(int)$_GET['id']."'");
                $infs = $sqls->fetch_assoc();
                //
                $nazwa_kat = $infs['categories_name'];
                //
                if ( $infs['parent_id'] > 0 ) {
                     //
                     foreach ( BoxyModuly::TablicaKategoriiAktualnosci() as $tmp ) {
                          //
                          if ( $infs['parent_id'] == $tmp['categories_id'] ) {
                               //
                               $nazwa_kat = $tmp['categories_name'] . ' / ' . $nazwa_kat;
                               //
                          }
                          //
                     } 
                     //
                }                                               
                //
                
                if ( $_GET['div'] != 'gorne_menu' ) {                  
                     $nazwaDowyswietlania = '<span class="ArtykulKategoria">'.$nazwa_kat.'<span>( link do kategorii aktualności )</span></span>';
                } else {
                     $nazwaDowyswietlania = Wyglad::KonfiguracjaPozycjiGornegoMenu($konfig_menu, $nazwa_kat, '( link do kategorii aktualności )', $infs['categories_id'], 'kategoria', 'ArtykulKategoria');
                     $nazwaDowyswietlania .= '<script>zmienMenuPodkat(\'' . $infs['categories_id'] . '_kategoria\')</script>';
                }                             
                
                $edycjaElementu = '<a class="TipChmurka" href="aktualnosci/aktualnosci_kategorie_edytuj.php?kat_id=' . $infs['categories_id'] . '&amp;zakladka=3"><b>Edytuj pozycję</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>';
                $idDoDiva = (int)$_GET['id'].'kategoria';
                $db->close_query($sqls); 
                unset($infs, $nazwa_kat);                                
                break; 
            case "artykul":
                $sqls = $db->open_query("select * from newsdesk n, newsdesk_description nd where n.newsdesk_id = nd.newsdesk_id and nd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."' and n.newsdesk_id = '".(int)$_GET['id']."'");
                $infs = $sqls->fetch_assoc();
                
                if ( $_GET['div'] != 'gorne_menu' ) {                  
                     $nazwaDowyswietlania = '<span class="Artykul">'.$infs['newsdesk_article_name'].'<span>( link do aktualności )</span></span>';
                } else {
                     $nazwaDowyswietlania = Wyglad::KonfiguracjaPozycjiGornegoMenu($konfig_menu, $infs['newsdesk_article_name'], '( link do aktualności )', $infs['newsdesk_id'], 'artykul', 'Artykul');
                     $nazwaDowyswietlania .= '<script>zmienMenuPodkat(\'' . $infs['newsdesk_id'] . '_artykul\')</script>';
                }                  
                
                $edycjaElementu = '<a class="TipChmurka" href="aktualnosci/aktualnosci_edytuj.php?id_poz=' . $infs['newsdesk_id'] . '&amp;zakladka=3"><b>Edytuj pozycję</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>';
                $idDoDiva = (int)$_GET['id'].'artykul';
                $db->close_query($sqls); 
                unset($infs);                                
                break; 
            case "kategproduktow":
                $sqls = $db->open_query("select * from categories c, categories_description cd where c.categories_id = cd.categories_id and c.parent_id = '0' and cd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."' and c.categories_id = '".(int)$_GET['id']."'");
                $infs = $sqls->fetch_assoc();
                
                if ( $_GET['div'] != 'gorne_menu' ) {                  
                     $nazwaDowyswietlania = '<span class="ProduktKategoria">'.$infs['categories_name'].'<span>( link do kategorii produktów )</span></span>';
                } else {
                     $nazwaDowyswietlania = Wyglad::KonfiguracjaPozycjiGornegoMenu($konfig_menu, $infs['categories_name'], '( link do kategorii produktów )', $infs['categories_id'], 'kategproduktow', 'ProduktKategoria');
                     $nazwaDowyswietlania .= '<script>zmienMenuPodkat(\'' . $infs['categories_id'] . '_kategproduktow\')</script>';
                }                         

                $edycjaElementu = '<a class="TipChmurka" href="kategorie/kategorie_edytuj.php?id_poz=' . $infs['categories_id'] . '&amp;zakladka=3"><b>Edytuj pozycję</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>';
                $idDoDiva = (int)$_GET['id'].'kategproduktow';
                $db->close_query($sqls); 
                unset($infs);                                
                break;                   
            case "grupainfo":
                $sqls = $db->open_query("select pg.pages_group_id,
                                                pg.pages_group_code,
                                                pg.pages_group_title,
                                                pgd.pages_group_name
                                           from pages_group pg left join pages_group_description pgd on pg.pages_group_id = pgd.pages_group_id and pgd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'
                                          where pg.pages_group_id  = '".(int)$_GET['id']."'");
                
                $infs = $sqls->fetch_assoc();
                $nazwaDowyswietlania = Wyglad::KonfiguracjaPozycjiGornegoMenu($konfig_menu, $infs['pages_group_name'], '( okno rozwijane stron informacyjnych z grupy: ' . $infs['pages_group_code'] . ' )', $infs['pages_group_id'], 'grupainfo');
                $nazwaDowyswietlania .= '<script>zmienMenuPodkat(\'' . $infs['pages_group_id'] . '_grupainfo\')</script>';
                $edycjaElementu = '<a class="TipChmurka" href="strony_informacyjne/strony_informacyjne_grupy_edytuj.php?id_poz=' . $infs['pages_group_id'] . '&amp;zakladka=3"><b>Edytuj pozycję</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>';
                $idDoDiva = (int)$_GET['id'].'grupainfo';
                $db->close_query($sqls); 
                unset($infs);                                
                break; 
            case "artkategorie":
                $sqls = $db->open_query("select * from newsdesk_categories n, newsdesk_categories_description nd where n.categories_id = nd.categories_id and nd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."' and n.categories_id = '".(int)$_GET['id']."'");
                $infs = $sqls->fetch_assoc();
                //
                $nazwa_kat = $infs['categories_name'];
                //
                if ( $infs['parent_id'] > 0 ) {
                     //
                     foreach ( BoxyModuly::TablicaKategoriiAktualnosci() as $tmp ) {
                          //
                          if ( $infs['parent_id'] == $tmp['categories_id'] ) {
                               //
                               $nazwa_kat = $tmp['categories_name'] . ' / ' . $nazwa_kat;
                               //
                          }
                          //
                     } 
                     //
                }      
                //
                $nazwaDowyswietlania = Wyglad::KonfiguracjaPozycjiGornegoMenu($konfig_menu, $nazwa_kat, '( okno rozwijane z artykułami z kategorii aktualności: ' . $nazwa_kat . ' )', $infs['categories_id'], 'artkategorie'); 
                $nazwaDowyswietlania .= '<script>zmienMenuPodkat(\'' . $infs['categories_id'] . '_artkategorie\')</script>';
                $edycjaElementu = '<a class="TipChmurka" href="aktualnosci/aktualnosci_kategorie_edytuj.php?kat_id=' . $infs['categories_id'] . '&amp;zakladka=3"><b>Edytuj pozycję</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>';
                $idDoDiva = (int)$_GET['id'].'artkategorie';
                $db->close_query($sqls); 
                unset($infs,$nazwa_kat);                                
                break; 
            case "prodkategorie":
                $sqls = $db->open_query("select * from categories c, categories_description cd where c.categories_id = cd.categories_id and c.parent_id = '0' and cd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."' and c.categories_id = '".(int)$_GET['id']."'");
                $infs = $sqls->fetch_assoc();
                $nazwaDowyswietlania = Wyglad::KonfiguracjaPozycjiGornegoMenu($konfig_menu, $infs['categories_name'], '( okno rozwijane z podkategoriami z kategorii produktów: ' . $infs['categories_name'] . ' )', $infs['categories_id'], 'katprod');
                $nazwaDowyswietlania .= '<script>zmienMenuPodkat(\'' . $infs['categories_id'] . '_katprod\')</script>';
                $edycjaElementu = '<a class="TipChmurka" href="kategorie/kategorie_edytuj.php?id_poz=' . $infs['categories_id'] . '&amp;zakladka=3"><b>Edytuj pozycję</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>';
                $idDoDiva = (int)$_GET['id'].'prodkategorie';
                $db->close_query($sqls); 
                unset($infs);                                
                break; 
            case "linkbezposredni":
                //
                parse_str($_GET['id'], $PostGet);
                //
                if ( !empty($PostGet['linkbezposredni']) && !empty($PostGet['jezyk_' . $_SESSION['domyslny_jezyk']['id']]) ) {
                     //
                     foreach (Funkcje::TablicaJezykow() as $PoleJezyka) {
                        if ( empty($PostGet['jezyk_' . $PoleJezyka['id']]) ) { 
                             $PostGet['jezyk_' . $PoleJezyka['id']] = $PostGet['jezyk_' . $_SESSION['domyslny_jezyk']['id']];
                        }
                     }
                     //
                     $idDoDiva = rand(1,1000).'linkbezposredniadreslinku'.str_replace('linkbezposredni', '', str_replace(array('/','='), array('ukosnik','rowna'), base64_encode( serialize($PostGet) )));
                     
                     if ( $_GET['div'] != 'gorne_menu' ) {                  
                          $nazwaDowyswietlania = '<span class="LinkZew">' . $PostGet['jezyk_' . $_SESSION['domyslny_jezyk']['id']] . '<span>( link zewnętrzny bezpośredni: ' . $PostGet['linkbezposredni'] . ' )</span></span>';
                     } else {
                          $nazwaDowyswietlania = Wyglad::KonfiguracjaPozycjiGornegoMenu($konfig_menu, $PostGet['jezyk_' . $_SESSION['domyslny_jezyk']['id']], '( link zewnętrzny bezpośredni: ' . $PostGet['linkbezposredni'] . ' )', $idDoDiva, 'linkbezposredni', 'LinkZew');
                          $nazwaDowyswietlania .= '<script>zmienMenuPodkat(\'' . base64_encode( serialize($PostGet) ) . '_linkbezposredni\')</script>';
                     }                           
                       
                     $edycjaElementu = '<em class="TipChmurka" style="float:right;" onclick="inne_edytuj(\'' . $idDoDiva . '\',\''.$_GET['div'].'\',\'linkbezposredni\')"><b>Edytuj pozycję</b><img src="obrazki/edytuj.png" alt="Edytuj" /></em>';                
                     //
                } else {
                     //
                     $idDoDiva = '';
                     //
                }
                unset($PostGet);
                break; 
            case "dowolnatresc":
                $sqls = $db->open_query("select ac.id_any_content,
                                               acd.any_content_name
                                          from any_content ac left join any_content_description acd on ac.id_any_content = acd.id_any_content and acd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'
                                         where ac.id_any_content = '".(int)$_GET['id']."'");
                
                $infs = $sqls->fetch_assoc();
                $nazwaDowyswietlania = Wyglad::KonfiguracjaPozycjiGornegoMenu($konfig_menu, $infs['any_content_name'], '( okno rozwijane z dowolną treścią '. ((Wyglad::TypSzablonu() == true) ? '' : ' - dostępne tylko dla szablonów V2) ') . ')',  $infs['id_any_content'], 'dowolnatresc');
                $nazwaDowyswietlania .= '<script>zmienMenuPodkat(\'' . $infs['id_any_content'] . '_dowolnatresc\')</script>';
                $edycjaElementu = '<a class="TipChmurka" href="dowolne_tresci/dowolne_tresci_edytuj.php?id_poz=' . $infs['id_any_content'] . '&amp;zakladka=3"><b>Edytuj pozycję</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>';
                $idDoDiva = (int)$_GET['id'].'dowolnatresc';
                $db->close_query($sqls); 
                unset($infs);                                
                break;                 
            case "pozycjabannery":
                //
                parse_str($_GET['id'], $PostGet);
                //
                if ( !empty($PostGet['jezyk_bannery_' . $_SESSION['domyslny_jezyk']['id']]) ) {
                     //
                     foreach (Funkcje::TablicaJezykow() as $PoleJezyka) {
                        if ( empty($PostGet['jezyk_bannery_' . $PoleJezyka['id']]) ) { 
                             $PostGet['jezyk_bannery_' . $PoleJezyka['id']] = $PostGet['jezyk_bannery_' . $_SESSION['domyslny_jezyk']['id']];
                        }
                     }
                     //
                     $idDoDiva = rand(1,1000).'pozycjabannerytylkografiki'.str_replace('pozycjabannery', '', str_replace(array('/','='), array('ukosnik','rowna'), base64_encode( serialize($PostGet) )));
                     $nazwaDowyswietlania = Wyglad::KonfiguracjaPozycjiGornegoMenu($konfig_menu, $PostGet['jezyk_bannery_' . $_SESSION['domyslny_jezyk']['id']], '( pozycja z bannerami '. ((Wyglad::TypSzablonu() == true) ? '' : ' - dostępne tylko dla szablonów V2) ') . ')', $idDoDiva, 'pozycjabannery', 'PozycjaGrafiki');
                     $nazwaDowyswietlania .= '<script>zmienMenuPodkat(\'' . base64_encode( serialize($PostGet) ) . '_pozycjabannery\')</script>';
                     $edycjaElementu = '<em class="TipChmurka" style="float:right;" onclick="inne_edytuj(\'' . $idDoDiva . '\',\''.$_GET['div'].'\',\'pozycjabannery\')"><b>Edytuj pozycję</b><img src="obrazki/edytuj.png" alt="Edytuj" /></em>';                
                     //
                } else {
                     //
                     $idDoDiva = '';
                     //
                }
                unset($PostGet);
                break;  
            case "linkwszystkiekategorie":
                //
                parse_str($_GET['id'], $PostGet);
                //
                if ( !empty($PostGet['menu_kategorie_jezyk_' . $_SESSION['domyslny_jezyk']['id']]) ) {
                     //
                     foreach (Funkcje::TablicaJezykow() as $PoleJezyka) {
                        if ( empty($PostGet['menu_kategorie_jezyk_' . $PoleJezyka['id']]) ) { 
                             $PostGet['menu_kategorie_jezyk_' . $PoleJezyka['id']] = $PostGet['menu_kategorie_jezyk_' . $_SESSION['domyslny_jezyk']['id']];
                        }
                     }
                     //
                     $idDoDiva = 'linkwszystkiekategorienazwapozycji'.str_replace('linkwszystkiekategorie', '', str_replace(array('/','='), array('ukosnik','rowna'), base64_encode( serialize($PostGet) )));
                     $nazwaDowyswietlania = Wyglad::KonfiguracjaPozycjiGornegoMenu($konfig_menu, $PostGet['menu_kategorie_jezyk_' . $_SESSION['domyslny_jezyk']['id']], '( link do wszystkich kategorii sklepu )', '99999998', 'katprod');
                     $nazwaDowyswietlania .= '<script>zmienMenuPodkat(\'99999998_katprod\')</script>';
                     $edycjaElementu = '<em class="TipChmurka" style="float:right;" onclick="inne_edytuj(\'' . $idDoDiva . '\',\''.$_GET['div'].'\',\'linkwszystkiekategorie\')"><b>Edytuj pozycję</b><img src="obrazki/edytuj.png" alt="Edytuj" /></em>';                
                     //
                } else {
                     //
                     $idDoDiva = '';
                     //
                }
                unset($PostGet);
                break;  
            case "linkwszyscyproducenci":
                //
                parse_str($_GET['id'], $PostGet);
                //
                if ( !empty($PostGet['menu_producenci_jezyk_' . $_SESSION['domyslny_jezyk']['id']]) ) {
                     //
                     foreach (Funkcje::TablicaJezykow() as $PoleJezyka) {
                        if ( empty($PostGet['menu_producenci_jezyk_' . $PoleJezyka['id']]) ) { 
                             $PostGet['menu_producenci_jezyk_' . $PoleJezyka['id']] = $PostGet['menu_producenci_jezyk_' . $_SESSION['domyslny_jezyk']['id']];
                        }
                     }
                     //
                     $idDoDiva = 'linkwszyscyproducencinazwapozycji'.str_replace('linkwszyscyproducenci', '', str_replace(array('/','='), array('ukosnik','rowna'), base64_encode( serialize($PostGet) )));  
                     $nazwaDowyswietlania = Wyglad::KonfiguracjaPozycjiGornegoMenu($konfig_menu, $PostGet['menu_producenci_jezyk_' . $_SESSION['domyslny_jezyk']['id']], '( link do wszystkich producentów w sklepie )', '99999999', 'producenci');
                     $nazwaDowyswietlania .= '<script>zmienMenuPodkat(\'99999999_producenci\')</script>';
                     $edycjaElementu = '<em class="TipChmurka" style="float:right;" onclick="inne_edytuj(\'' . $idDoDiva . '\',\''.$_GET['div'].'\',\'linkwszyscyproducenci\')"><b>Edytuj pozycję</b><img src="obrazki/edytuj.png" alt="Edytuj" /></em>';                
                     //
                } else {
                     //
                     $idDoDiva = '';
                     //
                }
                unset($PostGet);
                break;                       
        }
        
        if ( $idDoDiva != '' ) {
        ?>
        
            <div class="Stala" id="<?php echo $_GET['div']; ?>_<?php echo $idDoDiva; ?>">
                <?php echo $nazwaDowyswietlania; ?>
                <em class="TipChmurka" style="float:right;"><b>Skasuj</b><img class="Skasuj" onclick="ssk('<?php echo $idDoDiva; ?>','<?php echo $_GET['div']; ?>')" src="obrazki/kasuj.png" alt="Skasuj" /></em>
                <?php echo $edycjaElementu; ?>
                <em class="TipChmurka" style="float:right;"><b>W dół</b><img class="Dol" onclick="przesun('<?php echo $idDoDiva; ?>','gorne_menu','dol')" src="obrazki/strzalka_dol.png" alt="W dół" /></em>
                <em class="TipChmurka" style="float:right;"><b>W górę</b><img class="Gora" onclick="przesun('<?php echo $idDoDiva; ?>','gorne_menu','gora')" src="obrazki/strzalka_gora.png" alt="W górę" /></em>                                                            
            </div>     

        <?php
        }
        
    }    

    if ($_GET['p'] == 'edytuj_kategorie') {
    
        $rozk = explode('nazwapozycji', (string)$_GET['id']);
        $link_rozk = base64_decode(str_replace(array('ukosnik','rowna'), array('/','='), (string)$rozk[1]));
        $tab_linku = unserialize($link_rozk);    

        $textMenuWszystkieKategorie = '<div style="padding:0px 5px 0px 5px" id="DaneWszystkieKategorie">';
        $textMenuWszystkieKategorie .= '<strong class="OknaRozwijane" style="margin-top:3px; margin-bottom:3px">Menu kategorii sklepu - edycja</strong>';
        $textMenuWszystkieKategorie .= '<table style="width:430px; max-width:430px; margin-top:6px;">
                                       <tr><td colspan="2"><div class="maleInfo">Wpisz jak ma się nazywać pozycja w menu np OFERTA, KATEGORIE</div></td></tr>
                                       <tr>
                                          <td style="text-align:left"><ul>';
                                                                              
                                          foreach (Funkcje::TablicaJezykow() as $PoleJezyka) {
                                              $textMenuWszystkieKategorie .= '<li><img src="../' . KATALOG_ZDJEC . '/' . $PoleJezyka['foto'] . '" alt="" /><input type="text" value="' . ((isset($tab_linku['menu_kategorie_jezyk_' . $PoleJezyka['id']])) ? $tab_linku['menu_kategorie_jezyk_' . $PoleJezyka['id']] : '') . '" size="20" style="width:220px" name="menu_kategorie_jezyk_' . $PoleJezyka['id'] . '" id="menu_kategorie_jezyk_' . $PoleJezyka['id'] . '" /></li>';
                                          } 

                                          $textMenuWszystkieKategorie .= '</ul></td style="text-align:center">
                                          <td style="width:150px">                                              
                                              <button type="button" class="przyciskNon" style="padding:8px" onclick="wybierz_stala_inne_aktualizacja(\''.$_GET['div'].'\',\'DaneWszystkieKategorie\')" >Zapisz dane</button>
                                          </td>
                                       </tr>
                                 </table>';
        $textMenuWszystkieKategorie .= '</div>'; 
        
        echo $textMenuWszystkieKategorie;
        
        unset($rozk, $link_rozk, $tab_linku, $textMenuWszystkieKategorie);

    }  

    if ($_GET['p'] == 'edytuj_producenci') {
    
        $rozk = explode('nazwapozycji', (string)$_GET['id']);
        $link_rozk = base64_decode(str_replace(array('ukosnik','rowna'), array('/','='), (string)$rozk[1]));
        $tab_linku = unserialize($link_rozk);    

        $textMenuWszystkieKategorie = '<div style="padding:0px 5px 0px 5px" id="DaneWszyscyProducenci">';
        $textMenuWszystkieKategorie .= '<strong class="OknaRozwijane" style="margin-top:3px; margin-bottom:3px">Menu producentów w sklepie - edycja</strong>';
        $textMenuWszystkieKategorie .= '<table style="width:430px; max-width:430px; margin-top:6px;">
                                       <tr><td colspan="2"><div class="maleInfo">Wpisz jak ma się nazywać pozycja w menu np PRODUCENCI, MARKI</div></td></tr>
                                       <tr>
                                          <td style="text-align:left"><ul>';
                                                                              
                                          foreach (Funkcje::TablicaJezykow() as $PoleJezyka) {
                                              $textMenuWszystkieKategorie .= '<li><img src="../' . KATALOG_ZDJEC . '/' . $PoleJezyka['foto'] . '" alt="" /><input type="text" value="' . ((isset($tab_linku['menu_producenci_jezyk_' . $PoleJezyka['id']])) ? $tab_linku['menu_producenci_jezyk_' . $PoleJezyka['id']] : '') . '" size="20" style="width:220px" name="menu_producenci_jezyk_' . $PoleJezyka['id'] . '" id="menu_producenci_jezyk_' . $PoleJezyka['id'] . '" /></li>';
                                          } 

                                          $textMenuWszystkieKategorie .= '</ul></td style="text-align:center">
                                          <td style="width:150px">                                              
                                              <button type="button" class="przyciskNon" style="padding:8px" onclick="wybierz_stala_inne_aktualizacja(\''.$_GET['div'].'\',\'DaneWszyscyProducenci\')" >Zapisz dane</button>
                                          </td>
                                       </tr>
                                 </table>';
        $textMenuWszystkieKategorie .= '</div>'; 
        
        echo $textMenuWszystkieKategorie;
        
        unset($rozk, $link_rozk, $tab_linku, $textMenuWszystkieKategorie);

    }      

    if ($_GET['p'] == 'edytuj') {
    
        $rozk = explode('adreslinku', (string)$_GET['id']);
        $link_rozk = base64_decode(str_replace(array('ukosnik','rowna'), array('/','='), (string)$rozk[1]));
        $tab_linku = unserialize($link_rozk);    

        $textLinkBezposredni = '<div style="padding:0px 5px 0px 5px" id="DaneLinkuZew">';
        $textLinkBezposredni .= '<strong class="OknaRozwijane" style="margin-top:3px; margin-bottom:3px">Bezpośredni link do innej strony - edycja</strong>';
        $textLinkBezposredni .= '<input type="text" size="20" style="width:408px; max-width:408px; padding:8px 10px 8px 10px" id="linkbezposredni" name="linkbezposredni" value="' . $tab_linku['linkbezposredni'] . '" />'; 
        $textLinkBezposredni .= '<input type="hidden" value="' . str_replace('linkbezposredni', '', (string)$rozk[0]) . '" name="id_linku" />';
        $textLinkBezposredni .= '<div style="text-align:left; width:430px; max-width:430px; padding-top:8px; margin:0px auto"><input type="checkbox" value="1" ' . ((isset($tab_linku['nowa_strona'])) ? 'checked="checked"' : '') . ' name="nowa_strona" id="nowa_strona" /><label class="OpisFor" for="nowa_strona">otwieranie na nowej karcie przeglądarki</label></div>';        
        $textLinkBezposredni .= '<table style="width:430px; max-width:430px; margin-top:6px;">
                                      <tr>
                                          <td style="text-align:left"><ul>';
                                                                              
                                          foreach (Funkcje::TablicaJezykow() as $PoleJezyka) {
                                              $textLinkBezposredni .= '<li><img src="../' . KATALOG_ZDJEC . '/' . $PoleJezyka['foto'] . '" alt="" /><input type="text" value="' . ((isset($tab_linku['jezyk_' . $PoleJezyka['id']])) ? $tab_linku['jezyk_' . $PoleJezyka['id']] : '') . '" size="20" style="width:220px" name="jezyk_' . $PoleJezyka['id'] . '" id="jezyk_' . $PoleJezyka['id'] . '" /></li>';
                                          } 

                                          $textLinkBezposredni .= '</ul></td style="text-align:center">
                                          <td style="width:150px">                                              
                                              <button type="button" class="przyciskNon" style="padding:8px" onclick="wybierz_stala_inne_aktualizacja(\''.$_GET['div'].'\',\'DaneLinkuZew\')" >Zapisz dane</button>
                                          </td>
                                      </tr>
                                 </table>';
        $textLinkBezposredni .= '</div>'; 
        
        echo $textLinkBezposredni;
        
        unset($rozk, $link_rozk, $tab_linku, $textLinkBezposredni);

    }    

    if ($_GET['p'] == 'edytuj_bannery') {
    
        $rozk = explode('tylkografiki', (string)$_GET['id']);
        $link_rozk = base64_decode(str_replace(array('ukosnik','rowna'), array('/','='), (string)$rozk[1]));
        $tab_linku = unserialize($link_rozk);    

        $textPozycjaBanery = '<div style="padding:0px 5px 0px 5px" id="DanePozycjaBanery">';
        $textPozycjaBanery .= '<strong class="OknaRozwijane" style="margin-top:3px; margin-bottom:3px">Pozycja z bannerami - edycja</strong>';
        $textPozycjaBanery .= '<input type="hidden" value="' . str_replace('pozycjabannery', '', (string)$rozk[0]) . '" name="id_linku" />';
        $textPozycjaBanery .= '<table style="width:430px; max-width:430px; margin-top:6px;">
                                      <tr>
                                          <td style="text-align:left"><ul>';
                                                                              
                                          foreach (Funkcje::TablicaJezykow() as $PoleJezyka) {
                                              $textPozycjaBanery .= '<li><img src="../' . KATALOG_ZDJEC . '/' . $PoleJezyka['foto'] . '" alt="" /><input type="text" value="' . ((isset($tab_linku['jezyk_bannery_' . $PoleJezyka['id']])) ? $tab_linku['jezyk_bannery_' . $PoleJezyka['id']] : '') . '" size="20" style="width:220px" name="jezyk_bannery_' . $PoleJezyka['id'] . '" id="jezyk_bannery_' . $PoleJezyka['id'] . '" /></li>';
                                          } 

                                          $textPozycjaBanery .= '</ul></td style="text-align:center">
                                          <td style="width:150px">                                              
                                              <button type="button" class="przyciskNon" style="padding:8px" onclick="wybierz_stala_inne_aktualizacja(\''.$_GET['div'].'\',\'DanePozycjaBanery\')" >Zapisz dane</button>
                                          </td>
                                      </tr>
                                 </table>';
        $textPozycjaBanery .= '</div>'; 
        
        echo $textPozycjaBanery;
        
        unset($rozk, $link_rozk, $tab_linku, $textPozycjaBanery);

    }  
    
    if ($_GET['p'] == 'aktualizacja_linku') { 

        parse_str($_GET['id'], $PostGet);
        //
        if ( !empty($PostGet['linkbezposredni']) && !empty($PostGet['jezyk_' . $_SESSION['domyslny_jezyk']['id']]) ) {
             //
             foreach (Funkcje::TablicaJezykow() as $PoleJezyka) {
                if ( empty($PostGet['jezyk_' . $PoleJezyka['id']]) ) { 
                     $PostGet['jezyk_' . $PoleJezyka['id']] = $PostGet['jezyk_' . $_SESSION['domyslny_jezyk']['id']];
                }
             }
             //        
             $idDoDiva = $PostGet['id_linku'].'linkbezposredniadreslinku'.str_replace('linkbezposredni', '', str_replace(array('/','='), array('ukosnik','rowna'), base64_encode( serialize($PostGet) )));    
        } else {
             $idDoDiva = '';
        }
    
        $zapytanie = "select * from settings where code = '" . strtoupper((string)$_GET['div']) . "'";
        $sql = $db->open_query($zapytanie);
        $dane_menu = $sql->fetch_assoc();
        
        $tablica_linkow = explode(',', (string)$dane_menu['value']);
        $tablica_nowych_linkow = array();
        
        $nowy_klucz = '';
        
        foreach ( $tablica_linkow as $link ) {
            //
            $tmp = explode(';', (string)$link);
            if ( $tmp[0] != 'linkbezposredni' ) {
                 //
                 $tablica_nowych_linkow[] = $link;
                 //
            } else {
                 //
                 if ( strpos((string)$tmp[1], (string)$PostGet['id_linku'].'linkbezposredniadreslinku') > -1 ) {
                      //
                      if ( !empty($idDoDiva) ) {
                           $tablica_nowych_linkow[] = 'linkbezposredni;' . $idDoDiva;
                           $nowy_klucz = $idDoDiva.'|linkbezposredni';
                      } else {
                           $tablica_nowych_linkow[] = $link;
                      }
                      //
                 } else {
                      //
                      $tablica_nowych_linkow[] = $link;
                      //
                 }
                 //
            }
            //          
        }
        
        $db->close_query($sql);

        $pola = array(array('value', implode(',', (array)$tablica_nowych_linkow)));   
        
        $db->update_query('settings', $pola, " code = '" . strtoupper((string)$_GET['div']) . "'");	        
        
        // aktualizacja nazwy w ustawieniach konfiguracyjnych menu gornego
        
        if ( strpos((string)MENU_PODKATEGORIE, '{') > -1 ) {
             //
             $pod_tmp = @unserialize(MENU_PODKATEGORIE);
             $wynik_tp = array();
             //
             if ( is_array($pod_tmp) ) {
                  //
                  foreach ( $pod_tmp as $klucz => $wartosc ) {
                      //
                      if ( strpos((string)$klucz, $PostGet['id_linku'].'linkbezposredniadreslinku') > -1 ) {
                           //
                           $wynik_tmp[$nowy_klucz] = $wartosc;
                           //
                      } else {
                           //
                           $wynik_tmp[$klucz] = $wartosc;
                           //
                      }
                      //
                  }
                  //
             }
             //
             unset($podTmp);
             //
        }                 
            
        $wynik_tmp = serialize($wynik_tmp);

        $pola = array(
                array('value',$wynik_tmp));

        $sql = $db->update_query('settings', $pola, " code = 'MENU_PODKATEGORIE'");	
        
        unset($pola, $PostGet, $zapytanie, $tablica_linkow, $tablica_nowych_linkow);
    
    }
    
    if ($_GET['p'] == 'aktualizacja_bannery') { 

        parse_str($_GET['id'], $PostGet);
        //
        if ( !empty($PostGet['jezyk_bannery_' . $_SESSION['domyslny_jezyk']['id']]) ) {
             //
             foreach (Funkcje::TablicaJezykow() as $PoleJezyka) {
                if ( empty($PostGet['jezyk_bannery_' . $PoleJezyka['id']]) ) { 
                     $PostGet['jezyk_bannery_' . $PoleJezyka['id']] = $PostGet['jezyk_bannery_' . $_SESSION['domyslny_jezyk']['id']];
                }
             }
             //        
             $idDoDiva = $PostGet['id_linku'].'pozycjabannerytylkografiki'.str_replace('pozycjabannery', '', str_replace(array('/','='), array('ukosnik','rowna'), base64_encode( serialize($PostGet) )));    
        } else {
             $idDoDiva = '';
        }
    
        $zapytanie = "select * from settings where code = '" . strtoupper((string)$_GET['div']) . "'";
        $sql = $db->open_query($zapytanie);
        $dane_menu = $sql->fetch_assoc();
        
        $tablica_linkow = explode(',', (string)$dane_menu['value']);
        $tablica_nowych_linkow = array();
        
        $nowy_klucz = '';
        
        foreach ( $tablica_linkow as $link ) {
            //
            $tmp = explode(';', (string)$link);
            if ( $tmp[0] != 'pozycjabannery' ) {
                 //
                 $tablica_nowych_linkow[] = $link;
                 //
            } else {
                 //
                 if ( strpos((string)$tmp[1], (string)$PostGet['id_linku'].'pozycjabannerytylkografiki') > -1 ) {
                      //
                      if ( !empty($idDoDiva) ) {
                           $tablica_nowych_linkow[] = 'pozycjabannery;' . $idDoDiva;
                           $nowy_klucz = $idDoDiva.'|pozycjabannery';
                      } else {
                           $tablica_nowych_linkow[] = $link;
                      }
                      //
                 } else {
                      //
                      $tablica_nowych_linkow[] = $link;
                      //
                 }
                 //
            }
            //          
        }
        
        $db->close_query($sql);

        $pola = array(array('value', implode(',', (array)$tablica_nowych_linkow)));   
        
        $db->update_query('settings', $pola, " code = '" . strtoupper((string)$_GET['div']) . "'");	        
        
        // aktualizacja nazwy w ustawieniach konfiguracyjnych menu gornego
        
        if ( strpos((string)MENU_PODKATEGORIE, '{') > -1 ) {
             //
             $pod_tmp = @unserialize(MENU_PODKATEGORIE);
             $wynik_tp = array();
             //
             if ( is_array($pod_tmp) ) {
                  //
                  foreach ( $pod_tmp as $klucz => $wartosc ) {
                      //
                      if ( strpos((string)$klucz, $PostGet['id_linku'].'pozycjabannerytylkografiki') > -1 ) {
                           //
                           $wynik_tmp[$nowy_klucz] = $wartosc;
                           //
                      } else {
                           //
                           $wynik_tmp[$klucz] = $wartosc;
                           //
                      }
                      //
                  }
                  //
             }
             //
             unset($podTmp);
             //
        }                 
            
        $wynik_tmp = serialize($wynik_tmp);

        $pola = array(
                array('value',$wynik_tmp));

        $sql = $db->update_query('settings', $pola, " code = 'MENU_PODKATEGORIE'");	
        
        unset($pola, $PostGet, $zapytanie, $tablica_linkow, $tablica_nowych_linkow);
    
    }    
    
    if ($_GET['p'] == 'aktualizacja_kategorie') { 

        parse_str($_GET['id'], $PostGet);
        //
        if ( !empty($PostGet['menu_kategorie_jezyk_' . $_SESSION['domyslny_jezyk']['id']]) ) {
             //
             foreach (Funkcje::TablicaJezykow() as $PoleJezyka) {
                if ( empty($PostGet['menu_kategorie_jezyk_' . $PoleJezyka['id']]) ) { 
                     $PostGet['menu_kategorie_jezyk_' . $PoleJezyka['id']] = $PostGet['menu_kategorie_jezyk_' . $_SESSION['domyslny_jezyk']['id']];
                }
             }
             //        
             $idDoDiva = 'linkwszystkiekategorienazwapozycji'.str_replace('linkwszystkiekategorie', '', str_replace(array('/','='), array('ukosnik','rowna'), base64_encode( serialize($PostGet) )));    
        } else {
             $idDoDiva = '';
        }

        $tablica_linkow = explode(',', (string)GORNE_MENU);
        $tablica_nowych_linkow = array();
        
        $nowy_klucz = '';
        
        foreach ( $tablica_linkow as $link ) {
            //
            $tmp = explode(';', (string)$link);
            if ( $tmp[0] != 'linkwszystkiekategorie' ) {
                 //
                 $tablica_nowych_linkow[] = $link;
                 //
            } else {
                 //
                 if ( strpos((string)$tmp[1], 'linkwszystkiekategorienazwapozycji') > -1 ) {
                      //
                      if ( !empty($idDoDiva) ) {
                           $tablica_nowych_linkow[] = 'linkwszystkiekategorie;' . $idDoDiva;
                           $nowy_klucz = '99999998|katprod';
                      } else {
                           $tablica_nowych_linkow[] = $link;
                      }
                      //
                 } else {
                      //
                      $tablica_nowych_linkow[] = $link;
                      //
                 }
                 //
            }
            //          
        }
        
        $pola = array(array('value', implode(',', (array)$tablica_nowych_linkow)));   

        $db->update_query('settings', $pola, " code = 'GORNE_MENU'");	        
        
        // aktualizacja nazwy w ustawieniach konfiguracyjnych menu gornego
        
        if ( strpos((string)MENU_PODKATEGORIE, '{') > -1 ) {
             //
             $pod_tmp = @unserialize(MENU_PODKATEGORIE);
             $wynik_tp = array();
             //
             if ( is_array($pod_tmp) ) {
                  //
                  foreach ( $pod_tmp as $klucz => $wartosc ) {
                      //
                      if ( strpos((string)$klucz, '99999998|katprod') > -1 ) {
                           //
                           $wynik_tmp[$nowy_klucz] = $wartosc;
                           //
                      } else {
                           //
                           $wynik_tmp[$klucz] = $wartosc;
                           //
                      }
                      //
                  }
                  //
             }
             //
             unset($podTmp);
             //
        }                 
            
        $wynik_tmp = serialize($wynik_tmp);

        $pola = array(
                array('value',$wynik_tmp));

        $sql = $db->update_query('settings', $pola, " code = 'MENU_PODKATEGORIE'");	
        
        unset($pola, $PostGet, $zapytanie, $tablica_linkow, $tablica_nowych_linkow);
    
    }  

    if ($_GET['p'] == 'aktualizacja_producenci') { 

        parse_str($_GET['id'], $PostGet);
        //
        if ( !empty($PostGet['menu_producenci_jezyk_' . $_SESSION['domyslny_jezyk']['id']]) ) {
             //
             foreach (Funkcje::TablicaJezykow() as $PoleJezyka) {
                if ( empty($PostGet['menu_producenci_jezyk_' . $PoleJezyka['id']]) ) { 
                     $PostGet['menu_producenci_jezyk_' . $PoleJezyka['id']] = $PostGet['menu_producenci_jezyk_' . $_SESSION['domyslny_jezyk']['id']];
                }
             }
             //        
             $idDoDiva = 'linkwszyscyproducencinazwapozycji'.str_replace('linkwszyscyproducenci', '', str_replace(array('/','='), array('ukosnik','rowna'), base64_encode( serialize($PostGet) )));    
        } else {
             $idDoDiva = '';
        }

        $tablica_linkow = explode(',',(string)GORNE_MENU);
        $tablica_nowych_linkow = array();
        
        $nowy_klucz = '';
        
        foreach ( $tablica_linkow as $link ) {
            //
            $tmp = explode(';', (string)$link);
            if ( $tmp[0] != 'linkwszyscyproducenci' ) {
                 //
                 $tablica_nowych_linkow[] = $link;
                 //
            } else {
                 //
                 if ( strpos((string)$tmp[1], 'linkwszyscyproducencinazwapozycji') > -1 ) {
                      //
                      if ( !empty($idDoDiva) ) {
                           $tablica_nowych_linkow[] = 'linkwszyscyproducenci;' . $idDoDiva;
                           $nowy_klucz = '99999999|producenci';
                      } else {
                           $tablica_nowych_linkow[] = $link;
                      }
                      //
                 } else {
                      //
                      $tablica_nowych_linkow[] = $link;
                      //
                 }
                 //
            }
            //          
        }
        
        $pola = array(array('value', implode(',', (array)$tablica_nowych_linkow)));   

        $db->update_query('settings', $pola, " code = 'GORNE_MENU'");	        
        
        // aktualizacja nazwy w ustawieniach konfiguracyjnych menu gornego
        
        if ( strpos((string)MENU_PODKATEGORIE, '{') > -1 ) {
             //
             $pod_tmp = @unserialize(MENU_PODKATEGORIE);
             $wynik_tp = array();
             //
             if ( is_array($pod_tmp) ) {
                  //
                  foreach ( $pod_tmp as $klucz => $wartosc ) {
                      //
                      if ( strpos((string)$klucz, '99999999|producenci') > -1 ) {
                           //
                           $wynik_tmp[$nowy_klucz] = $wartosc;
                           //
                      } else {
                           //
                           $wynik_tmp[$klucz] = $wartosc;
                           //
                      }
                      //
                  }
                  //
             }
             //
             unset($podTmp);
             //
        }                 
            
        $wynik_tmp = serialize($wynik_tmp);

        $pola = array(
                array('value',$wynik_tmp));

        $sql = $db->update_query('settings', $pola, " code = 'MENU_PODKATEGORIE'");	
        
        unset($pola, $PostGet, $zapytanie, $tablica_linkow, $tablica_nowych_linkow);
    
    }    
    
}
?>
