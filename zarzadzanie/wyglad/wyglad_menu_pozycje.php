<?php
$nazwaDowyswietlania = '';
$edycjaElementu = '';

$strona = explode(';', (string)$pozycje_menu[$x]);
                                            
switch ($strona[0]) {
    case "strona":
        $sqls = $db->open_query("select * from pages p, pages_description pd where p.pages_id = pd.pages_id and pd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."' and p.pages_id = '".(int)$strona[1]."'");
        $infs = $sqls->fetch_assoc();
        $nazwaDowyswietlania = '<span class="StronaInfo">'.$infs['pages_title'].((!empty($infs['link'])) ? ' <span>( link zewnętrzny poprzez stronę informacyjną: '.$infs['link'].' )</span>' : '<span>( link do strony informacyjnej )</span>' ).'</span>';
        $edycjaElementu = '<a class="TipChmurka" href="strony_informacyjne/strony_informacyjne_edytuj.php?id_poz=' . $infs['pages_id'] . '&amp;zakladka=' . $nr_zakladki . '"><b>Edytuj pozycję</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>';
        $idDoDiva = $strona[1].'strona';
        $db->close_query($sqls); 
        unset($infs); 
        break;
    case "galeria":
        $sqls = $db->open_query("select * from gallery p, gallery_description pd where p.id_gallery = pd.id_gallery and pd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."' and p.id_gallery = '".(int)$strona[1]."'");
        $infs = $sqls->fetch_assoc();
        $nazwaDowyswietlania = '<span class="Galeria">'.$infs['gallery_name'].'<span>( link do galerii )</span></span>';
        $edycjaElementu = '<a class="TipChmurka" href="galerie/galerie_edytuj.php?id_poz=' . $infs['id_gallery'] . '&amp;zakladka=' . $nr_zakladki . '"><b>Edytuj pozycję</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>';
        $idDoDiva = $strona[1].'galeria';
        $db->close_query($sqls); 
        unset($infs); 
        break; 
    case "formularz":
        $sqls = $db->open_query("select * from form p, form_description pd where p.id_form = pd.id_form and pd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."' and p.id_form = '".(int)$strona[1]."'");
        $infs = $sqls->fetch_assoc();
        $nazwaDowyswietlania = '<span class="Formularz">'.$infs['form_name'].'<span>( link do formularza )</span></span>';
        $edycjaElementu = '<a class="TipChmurka" href="formularze/formularze_edytuj.php?id_poz=' . $infs['id_form'] . '&amp;zakladka=' . $nr_zakladki . '"><b>Edytuj pozycję</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>';
        $idDoDiva = $strona[1].'formularz';
        $db->close_query($sqls); 
        unset($infs); 
        break; 
    case "kategoria":
        $sqls = $db->open_query("select * from newsdesk_categories n, newsdesk_categories_description nd where n.categories_id = nd.categories_id and nd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."' and n.categories_id = '".(int)$strona[1]."'");
        $infs = $sqls->fetch_assoc();
        $nazwaDowyswietlania = '<span class="ArtykulKategoria">'.$infs['categories_name'].'<span>( link do kategorii aktualności )</span></span>';
        $edycjaElementu = '<a class="TipChmurka" href="aktualnosci/aktualnosci_kategorie_edytuj.php?kat_id=' . $infs['categories_id'] . '&amp;zakladka=' . $nr_zakladki . '"><b>Edytuj pozycję</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>';
        $idDoDiva = $strona[1].'kategoria';
        $db->close_query($sqls); 
        unset($infs); 
        break; 
    case "artykul":
        $sqls = $db->open_query("select * from newsdesk n, newsdesk_description nd where n.newsdesk_id = nd.newsdesk_id and nd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."' and n.newsdesk_id = '".(int)$strona[1]."'");
        $infs = $sqls->fetch_assoc();
        $nazwaDowyswietlania = '<span class="Artykul">'.$infs['newsdesk_article_name'].'<span>( link do aktualności )</span></span>';
        $edycjaElementu = '<a class="TipChmurka" href="aktualnosci/aktualnosci_edytuj.php?id_poz=' . $infs['newsdesk_id'] . '&amp;zakladka=' . $nr_zakladki . '"><b>Edytuj pozycję</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>';
        $idDoDiva = $strona[1].'artykul';
        $db->close_query($sqls); 
        unset($infs); 
        break; 
    case "kategproduktow":
        $sqls = $db->open_query("select * from categories c, categories_description cd where c.categories_id = cd.categories_id and c.parent_id = '0' and cd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."' and c.categories_id = '".(int)$strona[1]."'");
        $infs = $sqls->fetch_assoc();
        $nazwaDowyswietlania = '<span class="ProduktKategoria">'.$infs['categories_name'].'<span>( link do kategorii produktów )</span></span>';
        $edycjaElementu = '<a class="TipChmurka" href="kategorie/kategorie_edytuj.php?id_poz=' . $infs['categories_id'] . '&amp;zakladka=' . $nr_zakladki . '"><b>Edytuj pozycję</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>';
        $idDoDiva = $strona[1].'kategproduktow';
        $db->close_query($sqls); 
        unset($infs); 
        break;  
    case "linkbezposredni":
        $rozk = explode('adreslinku', (string)$strona[1]);
        $link_rozk = base64_decode(str_replace(array('ukosnik','rowna'), array('/','='), (string)$rozk[1]));
        $tab_linku = unserialize($link_rozk);
        $nazwaDowyswietlania = '<span class="LinkZew">' . $tab_linku['jezyk_' . $_SESSION['domyslny_jezyk']['id']] . '<span>( link zewnętrzny bezpośredni: ' . $tab_linku['linkbezposredni'] . ' )</span></span>';
        //
        $zwrot_menu = '';
        if ( $nr_zakladki == 6 ) {
             $zwrot_menu = 'dolne_menu';
        }
        if ( $nr_zakladki == 7 ) {
             $zwrot_menu = 'stopka_pierwsza';
        }
        if ( $nr_zakladki == 8 ) {
             $zwrot_menu = 'stopka_druga';
        }
        if ( $nr_zakladki == 9 ) {
             $zwrot_menu = 'stopka_trzecia';
        }
        if ( $nr_zakladki == 10 ) {
             $zwrot_menu = 'stopka_czwarta';
        }
        if ( $nr_zakladki == 11 ) {
             $zwrot_menu = 'stopka_piata';
        }
        if ( $nr_zakladki == 17 ) {
             $zwrot_menu = 'szybkie_menu';
        }        
        //
        $edycjaElementu = '<em class="TipChmurka" style="float:right;" onclick="inne_edytuj(\'' . $strona[1] . '\',\'' . $zwrot_menu . '\',\'linkbezposredni\')"><b>Edytuj pozycję</b><img src="obrazki/edytuj.png" alt="Edytuj" /></em>';
        $idDoDiva = $strona[1];
        unset($rozk, $tab_linku, $link_rozk);
        break;                 
}

?>