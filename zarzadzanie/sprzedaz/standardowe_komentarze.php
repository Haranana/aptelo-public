<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

if ( isset($_POST['nazwy']) && $_POST['nazwy'] == 'tak' ) {

    if ( isset($_POST['jezyk']) && (int)$_POST['jezyk'] > 0 && isset($_POST['id']) && (int)$_POST['id'] > 0 ) {

        $zapytanie = "SELECT soc.comments_id, soc.comments_id, soc.comments_name
                        FROM standard_order_comments soc 
                       WHERE soc.status_id = '" . (int)$_POST['id'] . "'
                    ORDER BY soc.sort_order";
        
        $sql = $db->open_query($zapytanie);
        
        echo '<option value="0">--- wybierz z listy ---</option>';
        
        while ($info = $sql->fetch_assoc()) {
        
          echo '<option value="' . $info['comments_id'] . '">' . $info['comments_name'] . '</option>';

        }

        $db->close_query($sql);
        unset($zapytanie, $info);
        
    } else {
    
        echo '<option selected="selected" value="0">--- najpierw wybierz status zamówienia ---</option>';
    
    }
    
}

if ( isset($_POST['nazwy']) && $_POST['nazwy'] == 'nie' ) {

    if ( isset($_POST['jezyk']) && (int)$_POST['jezyk'] > 0 && isset($_POST['id']) && (int)$_POST['id'] > 0 ) {

        $zapytanie = "SELECT socd.comments_id, socd.comments_text 
                        FROM standard_order_comments_description socd
                       WHERE socd.languages_id = '" . (int)$_POST['jezyk'] . "' and socd.comments_id = '" . (int)$_POST['id'] . "'";
        
        $sql = $db->open_query($zapytanie);
        $info = $sql->fetch_assoc();
        
        // nr dokumentu kuriera
        if ( strpos((string)$info['comments_text'], '{' ) > -1 && !isset($_POST['tryb']) ) {
             //
             if ( (int)$_POST['id_zamowienia'] > 0 ) {
                 //
                 $zamowienie = new Zamowienie( (int)$_POST['id_zamowienia'] );
                 //
                 $i18n = new Translator($db, $zamowienie->klient['jezyk']);
                 $GLOBALS['tlumacz'] = $i18n->tlumacz( array('ZAMOWIENIE_REALIZACJA'), null, true );               
                 //
                 // nr przesylki
                 define('NR_PRZESYLKI', (($zamowienie->dostawy_nr_przesylki != '') ? $zamowienie->dostawy_nr_przesylki : ''));
                 
                 // link sledzenia
                 define('LINK_SLEDZENIA_PRZESYLKI', (($zamowienie->dostawy_link_sledzenia != '') ? $zamowienie->dostawy_link_sledzenia : ''));  
            
                 // wartosc zamowienia
                 define('WARTOSC_ZAMOWIENIA', $zamowienie->info['wartosc_zamowienia']);
                 
                 // nr zamowienia
                 define('NUMER_ZAMOWIENIA', $zamowienie->info['id_zamowienia']);                 

                 // ilosc punktow
                 define('ILOSC_PUNKTOW', $zamowienie->ilosc_punktow);

                 // dokument sprzedazy
                 define('DOKUMENT_SPRZEDAZY', $zamowienie->info['dokument_zakupu_nazwa']);
                 
                 // forma platnosci
                 define('FORMA_PLATNOSCI', $zamowienie->info['metoda_platnosci']);
                    
                 // forma wysylki
                 define('FORMA_WYSYLKI', $zamowienie->info['wysylka_modul']);

                 // link plikow elektronicznych
                 define('LINK_PLIKOW_ELEKTRONICZNYCH', '<a style="text-decoration:underline;word-break:break-word" href="' . ADRES_URL_SKLEPU . '/' . $zamowienie->sprzedaz_online_link . '">' . $GLOBALS['tlumacz']['POBRANIE_PLIKOW_ZAMOWIENIA_LINK'] . '</a>');
                 
                 // lista produktow
                 $lista_produktow = array();                 
                 foreach ( $zamowienie->produkty as $tmp ) {
                   
                    $lista_produktow[] = $tmp['nazwa'];
                    
                 }
                 
                 define('LISTA_PRODUKTOW', implode('<br />', (array)$lista_produktow));                 

                 unset($zamowienie, $lista_produktow);
                 
                 $info['comments_text'] = str_replace('{KUPON_RABATOWY_PO_ZAMOWIENIU}','##KUPON_RABATOWY_PO_ZAMOWIENIU##', (string)$info['comments_text']);
                 
                 $info['comments_text'] = Funkcje::parsujZmienne($info['comments_text']);
                 $info['comments_text'] = preg_replace("{(<br[\\s]*(>|\/>)\s*){2,}}i", "<br /><br />", (string)$info['comments_text']);           

                 $info['comments_text'] = str_replace('##KUPON_RABATOWY_PO_ZAMOWIENIU##','{KUPON_RABATOWY_PO_ZAMOWIENIU}', (string)$info['comments_text']);
                 
             } else {
               
                 define('NR_PRZESYLKI', '');
                 define('WARTOSC_ZAMOWIENIA', '');
                 define('ILOSC_PUNKTOW', '');
                 define('DOKUMENT_SPRZEDAZY', '');
                 define('FORMA_PLATNOSCI', '');
                 define('FORMA_WYSYLKI', '');
                 define('LINK_PLIKOW_ELEKTRONICZNYCH', '');          
                 define('LISTA_PRODUKTOW', '');
               
             }
             //
        }        
                 
        echo $info['comments_text'];

        $db->close_query($sql);
        unset($zapytanie, $info);
        
    }
    
}    
?>
