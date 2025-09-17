<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone && Sesje::TokenSpr()) {

    if ($_GET['p'] == 'lista') {
      
        $warunek = ' and (p.box_v2 = 0 or p.box_v2 = 2)';
        
        if ( Wyglad::TypSzablonu() == true ) {
             //
             $warunek = ' and (p.box_v2 = 1 or p.box_v2 = 2)';
             //
        }      

        // pobieranie boxow wylaczonych
        $sqls = $db->open_query("select * from theme_box p, theme_box_description pd where p.box_id = pd.box_id and language_id = '" . (int)$_SESSION['domyslny_jezyk']['id'] . "' " . $warunek . " and p.box_status = '0' order by pd.box_title");
        
        $tablica = array();
        $tablica[] = array('id' => 0, 'text' => '... wybierz box ...');
        while ($infs = $sqls->fetch_assoc()) { 
            $tablica[] = array('id' => $infs['box_id'], 'text' => $infs['box_title'] . ' - ' . substr((string)$infs['box_description'], 0, 110 ) . ((strlen((string)$infs['box_description']) > 100) ? ' ...' : ''));
        }
        $db->close_query($sqls); 
        unset($zapytanie_tmp, $infs);    
        //      
        if (count($tablica) > 1) {
            echo '<div style="padding:10px">';
            echo Funkcje::RozwijaneMenu('boxy', $tablica, '', ' onchange="wybierz_box(this.value, \''.$filtr->process($_GET['kolumna']).'\')" style="width:430px"');
            echo '</div>';            
          } else { 
            echo '<div style="padding:10px">Brak danych do dodania ...</div>';
        }
        unset($tablica);
        
    }
    
    if ($_GET['p'] == 'dodaj') {
    
        $sqls = $db->open_query("select * from theme_box p, theme_box_description pd where p.box_id = pd.box_id and p.box_id = '".(int)$_GET['id']."' and pd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'");
        $infs = $sqls->fetch_assoc();
        
        if ($_GET['kolumna'] == 'lewa') {
        
        // dla lewej kolumny
        ?>
        <div class="Box" id="box_<?php echo (int)$_GET['id']; ?>">
            <em class="TipChmurka" style="float:right;"><b>Przenieś do prawej kolumny</b><img class="Strzalka" onclick="ple(<?php echo (int)$_GET['id']; ?>)" src="obrazki/strzalka_prawa.png" alt="Przenieś do prawej kolumny" /></em>
            <em class="TipChmurka" style="float:right;"><b>Skasuj</b><img class="Skasuj" onclick="psk(<?php echo (int)$_GET['id']; ?>)" src="obrazki/kasuj.png" alt="Skasuj" /></em>
            <a class="TipChmurka" href="wyglad/boxy_edytuj.php?id_poz=<?php echo (int)$_GET['id']; ?>&amp;zakladka=4"><b>Edytuj konfigurację boxu</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>
            <em class="TipChmurka" style="float:right;"><b>W dół</b><img class="Dol" onclick="przesun_bm('box_<?php echo $infs['box_id']; ?>','lewa','lewa','dol')" src="obrazki/strzalka_dol.png" alt="W dół" /></em>
            <em class="TipChmurka" style="float:right;"><b>W górę</b><img class="Gora" onclick="przesun_bm('box_<?php echo $infs['box_id']; ?>','lewa','lewa','gora')" src="obrazki/strzalka_gora.png" alt="W górę" /></em>                                                                                                                        
            <?php
            // plik php czy strona informacyjna
            if ($infs['box_type'] == 'plik') { 
              echo '<span class="iplik">'.$infs['box_title'].'<br /><strong>' . $infs['box_description'] . BoxyModuly::PolozenieBoxu($infs['box_localization']) . '</strong></span>'; 
            }
            if ($infs['box_type'] == 'java') { 
              echo '<span class="ikodjava">'.$infs['box_title'].'<br /><strong>' . $infs['box_description'] . BoxyModuly::PolozenieBoxu($infs['box_localization']) . '</strong></span>';               
            }
            if ($infs['box_type'] == 'strona') { 
              echo '<span class="istrona">'.$infs['box_title'].'<br /><strong>' . $infs['box_description'] . BoxyModuly::PolozenieBoxu($infs['box_localization']) . '</strong></span>';   
            }    
            if ($infs['box_type'] == 'txt') { 
              echo '<span class="itxt">'.$infs['box_title'].'<br /><strong>' . $infs['box_description'] . BoxyModuly::PolozenieBoxu($infs['box_localization']) . '</strong></span>';   
            }                 
            ?>            
        </div>                        
        <?php  
        
        }
        
        if ($_GET['kolumna'] == 'prawa') {

        // dla prawej kolumny
        ?>
        <div class="Box" id="box_<?php echo (int)$_GET['id']; ?>" style="text-align:right">
            <em class="TipChmurka" style="float:left;"><b>Przenieś do lewej kolumny</b><img class="Strzalka" onclick="ple(<?php echo $infs['box_id']; ?>)" src="obrazki/strzalka_lewa.png" alt="Przenieś do lewej kolumny" /></em>
            <em class="TipChmurka" style="float:left;"><b>Skasuj</b><img class="Skasuj" onclick="psk(<?php echo $infs['box_id']; ?>)" src="obrazki/kasuj.png" alt="Skasuj" /></em>
            <a class="TipChmurka" href="wyglad/boxy_edytuj.php?id_poz=<?php echo $infs['box_id']; ?>&amp;zakladka=4"><b>Edytuj konfigurację boxu</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>
            <em class="TipChmurka" style="float:left;"><b>W dół</b><img class="Dol" onclick="przesun_bm('box_<?php echo $infs['box_id']; ?>','prawa','prawa','dol')" src="obrazki/strzalka_dol.png" alt="W dół" /></em>
            <em class="TipChmurka" style="float:left;"><b>W górę</b><img class="Gora" onclick="przesun_bm('box_<?php echo $infs['box_id']; ?>','prawa','prawa','gora')" src="obrazki/strzalka_gora.png" alt="W górę" /></em>                                                                                                                                    
            <?php
            // plik php czy strona informacyjna
            if ($infs['box_type'] == 'plik') { 
              echo '<span class="rplik">'.$infs['box_title'].'<br /><strong>' . $infs['box_description'] . '</strong></span>'; 
            }
            if ($infs['box_type'] == 'java') { 
              echo '<span class="rkodjava">'.$infs['box_title'].'<br /><strong>' . $infs['box_description'] . '</strong></span>';               
            }
            if ($infs['box_type'] == 'strona') { 
              echo '<span class="rstrona">'.$infs['box_title'].'<br /><strong>' . $infs['box_description'] . '</strong></span>';   
            }                                               
            ?>            
        </div>                        
        <?php   

        }

        $db->close_query($sqls); 
        unset($infs);    
        //      
        
    }    
    
}
?>
