<?php
chdir('../');     

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone && Sesje::TokenSpr()) {

    $zmienneAktualizacji = array();
    
    if ( isset($_POST['box']) ) {
         $zmienneAktualizacji = $_POST['box'];
    }
    
    // jezeli bedzie kasowane
    if (isset($_POST['skasuj']) && (int)$_POST['skasuj'] == '1') {
        $pola = array(
                array('box_status','0'));
        
        $sql = $db->update_query('theme_box', $pola, " box_id = '".(int)$_POST['idbox']."'");	
        unset($pola);
    }    
    
    if ( is_array($zmienneAktualizacji) ) {
      
        $sort = 1;
        
        foreach ($zmienneAktualizacji as $idBoxu) {
        
            $pola = array(
                    array('box_status','1'),
                    array('box_sort',$sort),
                    array('box_column',$filtr->process($_POST['kolumna'])));
            
            $sql = $db->update_query('theme_box', $pola, " box_id = '".(int)$idBoxu."'");	
            unset($pola);

            $sort++;
        
        }
        
    }
        
    // sprawdza czy sa w bazie boxy niewlaczone
    $sqls = $db->open_query("select * from theme_box where box_status = '0'");
    if ((int)$db->ile_rekordow($sqls) > 0) {
        echo '1';
      } else {
        echo '0';
    }
    
}
?>