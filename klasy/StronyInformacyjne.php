<?php
 
class StronyInformacyjne {

    // zwraca tablice ze stronami info
    public static function TablicaStronInfo() {
        //
        $WynikCache = $GLOBALS['cache']->odczytaj('StronyInfo_' . $_SESSION['domyslnyJezyk']['kod'], CACHE_STRONY_INFO, true);
        //
        $TablicaStron = array();
        //
        if ( !$WynikCache ) {
            //
            // dodatkowy warunek dla grup klientow
            $warunekTmp = " and (p.pages_customers_group_id = '0'";
            if ( isset($_SESSION['customers_groups_id']) && (int)$_SESSION['customers_groups_id'] > 0 ) {
                $warunekTmp .= " or find_in_set(" . (int)$_SESSION['customers_groups_id'] . ", p.pages_customers_group_id)";
            }
            $warunekTmp .= ") "; 
            //
            $zapytanie = " SELECT distinct p.pages_id,
                                  p.pages_image,
                                  p.sort_order,
                                  p.pages_group,
                                  p.link,
                                  p.nofollow,
                                  p.status,
                                  p.pages_modul,
                                  p.pages_customers_group_id,
                                  pd.pages_title, 
                                  pd.pages_short_text, 
                                  pd.pages_text,
                                  pd.meta_title_tag,
                                  pd.meta_desc_tag,
                                  pd.meta_keywords_tag
                             FROM pages p
                       INNER JOIN pages_description pd ON p.pages_id = pd.pages_id
                            WHERE p.status = '1'
                                  AND pd.language_id = '" . (int)$_SESSION['domyslnyJezyk']['id'] . "'
                                  " . $warunekTmp . "
                         ORDER BY p.sort_order";

            unset($warunekTmp);
                                          
            $_sql = $GLOBALS['db']->open_query($zapytanie);
            //
            $TablicaStron = array();

            if ((int)$GLOBALS['db']->ile_rekordow($_sql) > 0) {
                //
                while ($_info = $_sql->fetch_assoc()) {
                    //
                    // miejsce wyswietlania strony - box lub modul
                    $Wyswietlanie = '';
                    if ($_info['pages_modul'] == 1) { $Wyswietlanie = 'modul'; }
                    if ($_info['pages_modul'] == 2) { $Wyswietlanie = 'box'; }
                    //
                    $TablicaStron[] = array('id' => $_info['pages_id'],
                                            'foto_strony' => $_info['pages_image'],
                                            'sortowanie' => $_info['sort_order'],
                                            'tytul' => $_info['pages_title'],
                                            'grupa' => $_info['pages_group'],
                                            'link' => '<a ' . (($_info['nofollow'] == 1) ? 'rel="nofollow" ' : '') . 'href="' . (($_info['link'] != '') ? $_info['link'] : Seo::link_SEO( $_info['pages_title'], $_info['pages_id'], 'strona_informacyjna' )) . '">' . $_info['pages_title'] . '</a>',
                                            'seo' => (($_info['link'] != '') ? $_info['link'] : Seo::link_SEO( $_info['pages_title'], $_info['pages_id'], 'strona_informacyjna' )),
                                            'nofollow' => $_info['nofollow'],
                                            'wyswietlanie' => $Wyswietlanie,
                                            'url' => $_info['link'],
                                            'opis_krotki' => '<div class="FormatEdytor">' . $_info['pages_short_text'] . '</div>',
                                            'opis' => '<div class="FormatEdytor">' . $_info['pages_text'] . '</div>',
                                            'meta_tytul' => $_info['meta_title_tag'],
                                            'meta_opis' => $_info['meta_desc_tag'],
                                            'meta_slowa' => $_info['meta_keywords_tag']);                                            
                    //
                    unset($Wyswietlanie);
                    //
                }
                unset($_info);
                //
            }
            
            $GLOBALS['db']->close_query($_sql); 
            unset($zapytanie);  

            $GLOBALS['cache']->zapisz('StronyInfo_' . $_SESSION['domyslnyJezyk']['kod'], $TablicaStron, CACHE_STRONY_INFO, true);
            //
        } else { 
            //
            if (count($WynikCache)) {
                $TablicaStron = $WynikCache;
            }
            //
        }
        
        return $TablicaStron;
        
    }
    
    // zwraca tablice ze stronami info z danej grupy
    public static function TablicaStronInfoGrupa( $grupa = '' ) {
    
        $TablicaStronGrupa = array();
        
        $TablicaStron = StronyInformacyjne::TablicaStronInfo();
        
        if ( count($TablicaStron) > 0 ) {
        
            foreach ( $TablicaStron as $Strona ) {
                //
                if ( ($Strona['grupa'] == $grupa && $Strona['wyswietlanie'] == '') || ($Strona['grupa'] == $grupa && strlen((string)$Strona['opis']) > 10) ) {
                     $TablicaStronGrupa[] = $Strona;
                }
                //
            }
        
        }
        
        unset($TablicaStron);
        
        return $TablicaStronGrupa;

    }
    
    // zwraca tablice z danymi strony o konkretnym ID
    public static function StronaInfoId( $id ) {
        //
        $WynikStrona = '';
        
        $TablicaStron = StronyInformacyjne::TablicaStronInfo();
        
        if ( count($TablicaStron) > 0 ) {
        
            foreach ( $TablicaStron as $Strona ) {
                //
                if ( ($Strona['id'] == $id && $Strona['wyswietlanie'] == '') || ($Strona['id'] == $id && strlen((string)$Strona['opis']) > 10) ) {
                     $WynikStrona = $Strona;
                     break;
                }
                //
            }
            
        }
        
        unset($TablicaStron);
        
        return $WynikStrona;
        
    }
    
    // zwraca tablice z danymi strony o konkretnej nazwie
    public static function StronaInfoNazwa( $nazwa ) {
        //
        $WynikStrona = '';
        
        $TablicaStron = StronyInformacyjne::TablicaStronInfo();
        
        if ( count($TablicaStron) > 0 ) {
        
            foreach ( $TablicaStron as $Strona ) {
                //
                if ( strtolower((string)$Strona['tytul']) == strtolower((string)$nazwa) && $Strona['wyswietlanie'] == '' ) {
                     $WynikStrona = $Strona;
                     break;
                }
                //
            }
        
        }
        
        unset($TablicaStron);
        
        return $WynikStrona;
        
    }    
    
    // zwraca tablice z grupami stron informacyjnych
    public static function TablicaGrupInfo() {
        //
        $WynikCache = $GLOBALS['cache']->odczytaj('StronyInfoGrupy_' . $_SESSION['domyslnyJezyk']['kod'], CACHE_STRONY_INFO, true);
        //    
        $TablicaGrup = array();
        
        if ( !$WynikCache ) {
        
            $zapytanie = "select pg.pages_group_id,
                                 pg.pages_group_code,
                                 pg.pages_group_title,
                                 pgd.pages_group_name
                            from pages_group pg left join pages_group_description pgd on pg.pages_group_id = pgd.pages_group_id and pgd.language_id = '".(int)$_SESSION['domyslnyJezyk']['id']."'";
                                      
            $_sqlg = $GLOBALS['db']->open_query($zapytanie);
            
            if ((int)$GLOBALS['db']->ile_rekordow($_sqlg) > 0) {
            
                while ($_infg = $_sqlg->fetch_assoc()) {
                    //
                    $TablicaGrup[$_infg['pages_group_id']] = array('id' => $_infg['pages_group_id'],
                                                                   'kod' => $_infg['pages_group_code'],
                                                                   'nazwa' => $_infg['pages_group_name']);                                            
                    //
                }
                unset($_infg);
                //
                
            }

            $GLOBALS['db']->close_query($_sqlg); 
            unset($zapytanie); 
            
            $GLOBALS['cache']->zapisz('StronyInfoGrupy_' . $_SESSION['domyslnyJezyk']['kod'], $TablicaGrup, CACHE_STRONY_INFO, true);
            //
        } else { 
            //
            if (count($WynikCache)) {
                $TablicaGrup = $WynikCache;
            }
            //
        }            

        return $TablicaGrup;
        
    }
    
}
?>