<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone && Sesje::TokenSpr()) {

    if ($_GET['p'] == 'lista') {

        $warunek = ' and (p.modul_v2 = 0 or p.modul_v2 = 2)';
        
        if ( Wyglad::TypSzablonu() == true ) {
             //
             $warunek = ' and (p.modul_v2 = 1 or p.modul_v2 = 2)';
             //
        }
        
        if ( isset($_SESSION['programista']) && $_SESSION['programista'] == '1' ) {
             //
             $warunek = ' and (p.modul_v2 = 0 or p.modul_v2 = 1 or p.modul_v2 = 2)';
             //
        }
    
        // pobieranie modulow wylaczonych
        $sqls = $db->open_query("select * from theme_modules p, theme_modules_description pd where p.modul_id = pd.modul_id and language_id = '" . (int)$_SESSION['domyslny_jezyk']['id'] . "' " . $warunek . " and p.modul_status = '0' order by pd.modul_title");
        
        $tablica = array();
        $tablica[] = array('id' => 0, 'text' => '... wybierz moduł ...');
        while ($infs = $sqls->fetch_assoc()) { 
            $tablica[] = array('id' => $infs['modul_id'], 'text' => $infs['modul_title'] . ' - ' . substr((string)$infs['modul_description'], 0, 110 ) . ((strlen((string)$infs['modul_description']) > 100) ? ' ...' : ''));
        }
        $db->close_query($sqls); 
        unset($zapytanie_tmp, $infs);    
        //      
        if (count($tablica) > 1) {        
            echo '<div style="padding:10px">';
            echo Funkcje::RozwijaneMenu('moduly', $tablica, '', ' onchange="wybierz_modul(this.value,\'' . $filtr->process($_GET['typ']) .'\')" style="width:430px"');
            echo '</div>';
            unset($tablica);
          } else { 
            echo '<div style="padding:10px">Brak danych do dodania ...</div>';
        }
        unset($tablica);        
        
    }
    
    if ($_GET['p'] == 'dodaj') {
    
        $sqls = $db->open_query("select * from theme_modules p, theme_modules_description pd where p.modul_id = pd.modul_id and p.modul_id = '".(int)$_GET['id']."' and pd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'");
        $infs = $sqls->fetch_assoc();

        ?>
        <div class="Box" id="modul_<?php echo (int)$_GET['id']; ?>">
            <em class="TipChmurka" style="float:right;"><b>Skasuj</b><img class="Skasuj" onclick="msk(<?php echo $infs['modul_id']; ?>,'<?php echo $filtr->process($_GET['typ']); ?>')" src="obrazki/kasuj.png" alt="Skasuj" /></em>
            <?php if ($infs['modul_type'] != 'kreator') { ?>                                                        
            <a class="TipChmurka" href="wyglad/srodek_edytuj.php?id_poz=<?php echo $infs['modul_id']; ?>&amp;zakladka=5"><b>Edytuj konfigurację modułu</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>
            <?php } else { ?>
            <a class="TipChmurka" href="wyglad/srodek_kreator_modulow.php?id_poz=<?php echo $infs['modul_id']; ?>&amp;zakladka=5"><b>Edytuj konfigurację modułu</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>                                                 
            <?php } ?>            
            <em class="TipChmurka" style="float:right;"><b>W dół</b><img class="Dol" onclick="przesun_bm('modul_<?php echo $infs['modul_id']; ?>','srodek_<?php echo $filtr->process($_GET['typ']); ?>','<?php echo $filtr->process($_GET['typ']); ?>','dol')" src="obrazki/strzalka_dol.png" alt="W dół" /></em>
            <em class="TipChmurka" style="float:right;"><b>W górę</b><img class="Gora" onclick="przesun_bm('modul_<?php echo $infs['modul_id']; ?>','srodek_<?php echo $filtr->process($_GET['typ']); ?>','<?php echo $filtr->process($_GET['typ']); ?>','gora')" src="obrazki/strzalka_gora.png" alt="W górę" /></em>                                                                                                            
            <?php
            if ($infs['modul_type'] == 'plik') { 
              echo '<span class="iplik">'.$infs['modul_title'].'<br /><strong>' . $infs['modul_description'] . BoxyModuly::PolozenieModulu($infs['modul_localization']) . '</strong></span>'; 
            }
            if ($infs['modul_type'] == 'java') { 
              echo '<span class="ikodjava">'.$infs['modul_title'].'<br /><strong>' . $infs['modul_description'] . BoxyModuly::PolozenieModulu($infs['modul_localization']) . '</strong></span>';               
            }
            if ($infs['modul_type'] == 'strona') { 
              echo '<span class="istrona">'.$infs['modul_title'].'<br /><strong>' . $infs['modul_description'] . BoxyModuly::PolozenieModulu($infs['modul_localization']) . '</strong></span>';   
            } 
            if ($infs['modul_type'] == 'txt') { 
              echo '<span class="itxt">'.$infs['modul_title'].'<br /><strong>' . $infs['modul_description'] . BoxyModuly::PolozenieModulu($infs['modul_localization']) . '</strong></span>';   
            } 
            if ($infs['modul_type'] == 'kreator') { 
              echo '<span class="ikreator">'.$infs['modul_title'].'<br /><strong>' . $infs['modul_description'] . BoxyModuly::PolozenieModulu($infs['modul_localization']) . Wyglad::AktywnyKreator() . '</strong></span>';   
            }               
            ?>            
        </div>                        
        <?php  

        $db->close_query($sqls); 
        unset($infs);    
        //      
    }    
    
}
?>
