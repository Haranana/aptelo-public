<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //

        // dodanie rekordu tablicy modulow
        $zapytanie = "SELECT * FROM modules_shipping WHERE id = '" . (int)$_POST['id'] . "'";
        $sql = $db->open_query($zapytanie);

        if ((int)$db->ile_rekordow($sql) > 0) {
          $info = $sql->fetch_assoc();

          $pola = array(
                  array('nazwa',$info["nazwa"] . ' (kopia)'),
                  array('skrypt',$info["skrypt"]),
                  array('klasa',$info["klasa"]),
                  array('sortowanie',$info["sortowanie"]),
                  array('status',$info["status"]),
                  array('integracja',$info["integracja"]));
                  
          $db->insert_query('modules_shipping' , $pola);
          $id_dodanego_modulu = $db->last_id_query();
          unset($pola);

        } else {
        
            Funkcje::PrzekierowanieURL('wysylka.php');
            
        }

        $db->close_query($sql);
        unset($zapytanie, $info);


        // dodanie rekordu tablicy modulow parametrow
        $zapytanie_params = "SELECT * FROM modules_shipping_params WHERE modul_id = '" . (int)$_POST['id'] . "'";
        $sql_params = $db->open_query($zapytanie_params);
        if ((int)$db->ile_rekordow($sql_params) > 0) {
        
          while ( $info_params = $sql_params->fetch_assoc() ) {
          
            $pola = array(
                    array('modul_id',$id_dodanego_modulu),
                    array('nazwa',$info_params["nazwa"]),
                    array('kod',$info_params["kod"]),
                    array('wartosc',$info_params["wartosc"]),
                    array('sortowanie',$info_params["sortowanie"]));
                    
            $db->insert_query('modules_shipping_params' , $pola);
            unset($pola);
          }
          
        }

        $db->close_query($sql_params);
        unset($zapytanie_params, $info_params);

        // #######################
        // dodanie tlumaczen
        $pola = array(
            array('translate_constant','WYSYLKA_'.(int)$id_dodanego_modulu.'_TYTUL'),
            array('section_id', '4'));
            
        $db->insert_query('translate_constant' , $pola);
        $id_dodanego_wyrazenia = $db->last_id_query();
        unset($pola);

        $zapytanie_lang = "SELECT * FROM translate_value WHERE translate_constant_id = '" . (int)$_POST['id_tlumaczenia'] . "'";
        $sql_lang = $db->open_query($zapytanie_lang);

        if ((int)$db->ile_rekordow($sql_lang) > 0) {
        
          while ( $info_lang = $sql_lang->fetch_assoc() ) {
          
            $pola = array(
                    array('translate_value',$info_lang['translate_value']),
                    array('translate_constant_id',(int)$id_dodanego_wyrazenia),
                    array('language_id',$info_lang['language_id']));
                    
            $sql = $db->insert_query('translate_value' , $pola);
            unset($pola);
          }
          
        }
        $db->close_query($sql_lang);
        unset($zapytanie_lang, $info_lang, $id_dodanego_wyrazenia);

        $pola = array(
                array('translate_constant','WYSYLKA_'.(int)$id_dodanego_modulu.'_OBJASNIENIE'),
                array('section_id', '4'));
                
        $db->insert_query('translate_constant' , $pola);
        $id_dodanego_wyrazenia = $db->last_id_query();
        unset($pola);

        $zapytanie_lang = "SELECT * FROM translate_value WHERE translate_constant_id = '" . (int)$_POST['id_objasnienia'] . "'";
        $sql_lang = $db->open_query($zapytanie_lang);

        if ((int)$db->ile_rekordow($sql_lang) > 0) {
        
          while ( $info_lang = $sql_lang->fetch_assoc() ) {
          
            $pola = array(
                    array('translate_value',$info_lang['translate_value']),
                    array('translate_constant_id',(int)$id_dodanego_wyrazenia),
                    array('language_id',$info_lang['language_id']));
                    
            $sql = $db->insert_query('translate_value' , $pola);
            unset($pola);
            
          }
          
        }
        $db->close_query($sql_lang);
        unset($zapytanie_lang, $info_lang, $id_dodanego_wyrazenia);

        $pola = array(
                array('translate_constant','WYSYLKA_'.(int)$id_dodanego_modulu.'_INFORMACJA'),
                array('section_id', '4'));
                
        $db->insert_query('translate_constant' , $pola);
        $id_dodanego_wyrazenia = $db->last_id_query();
        unset($pola);

        $zapytanie_lang = "SELECT * FROM translate_value WHERE translate_constant_id = '" . (int)$_POST['id_informacji'] . "'";
        $sql_lang = $db->open_query($zapytanie_lang);
        
        if ((int)$db->ile_rekordow($sql_lang) > 0) {
        
          while ( $info_lang = $sql_lang->fetch_assoc() ) {
          
            $pola = array(
                    array('translate_value',$info_lang['translate_value']),
                    array('translate_constant_id',(int)$id_dodanego_wyrazenia),
                    array('language_id',(int)$info_lang['language_id'])
            );
            $sql = $db->insert_query('translate_value' , $pola);
            unset($pola);
            
          }
          
        }
        $db->close_query($sql_lang);
        unset($zapytanie_lang, $info_lang, $id_dodanego_wyrazenia);

        //
        Funkcje::PrzekierowanieURL('wysylka.php');
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Kopiowanie pozycji</div>
    <div id="cont">
          
          <form action="moduly/wysylka_duplikuj.php" method="post" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Kopiowanie danych</div>
            
            <?php

            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }

            $zapytanie = "SELECT * FROM modules_shipping WHERE id = '" . (int)$_GET['id_poz'] . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
              
                $info = $sql->fetch_assoc();
                
                if ( $info['klasa'] != 'wysylka_odbior_osobisty' && $info['klasa'] != 'wysylka_inpost_eko' && $info['klasa'] != 'wysylka_inpost' && $info['klasa'] != 'wysylka_inpost_kurier' && $info['klasa'] != 'wysylka_indywidualna' && $info['klasa'] != 'wysylka_paczkaruch' && $info['klasa'] != 'wysylka_pocztapunkt' ) {   

                    $tlumaczenie_z = 'WYSYLKA_' . (int)$_GET['id_poz'] . '_TYTUL';
                    $objasnienie_z = 'WYSYLKA_' . (int)$_GET['id_poz'] . '_OBJASNIENIE';
                    $informacja_z  = 'WYSYLKA_' . (int)$_GET['id_poz'] . '_INFORMACJA';

                    $zapytanie_jezyk = "SELECT DISTINCT * FROM translate_constant WHERE translate_constant = '".$tlumaczenie_z."'";
                    $sqls = $db->open_query($zapytanie_jezyk);
                    $nazwa = $sqls->fetch_assoc();   
                    $db->close_query($sqls);
                    unset($zapytanie_jezyk);

                    $zapytanie_jezyk = "SELECT DISTINCT * FROM translate_constant WHERE translate_constant = '".$objasnienie_z."'";
                    $sqls = $db->open_query($zapytanie_jezyk);
                    $objasnienie = $sqls->fetch_assoc();   
                    $db->close_query($sqls);
                    unset($zapytanie_jezyk);

                    $zapytanie_jezyk = "SELECT DISTINCT * FROM translate_constant WHERE translate_constant = '".$informacja_z."'";
                    $sqls = $db->open_query($zapytanie_jezyk);
                    $informacja = $sqls->fetch_assoc();   
                    $db->close_query($sqls);
                    unset($zapytanie_jezyk);
                    ?> 
                
                    <div class="pozycja_edytowana">
                    
                        <input type="hidden" name="akcja" value="zapisz" />
                    
                        <input type="hidden" name="id" value="<?php echo (int)$_GET['id_poz']; ?>" />
                        <input type="hidden" name="klasa" value="<?php echo $info['klasa']; ?>" />
                        <input type="hidden" name="id_tlumaczenia" value="<?php echo $nazwa['translate_constant_id']; ?>" />
                        <input type="hidden" name="id_objasnienia" value="<?php echo $objasnienie['translate_constant_id']; ?>" />
                        <input type="hidden" name="id_informacji" value="<?php echo $informacja['translate_constant_id']; ?>" />

                        <p>
                          Czy skopiować moduł wysyłki <?php echo $info['nazwa']; ?> ?
                        </p>   
                     
                    </div>

                    <div class="przyciski_dolne">
                      <input type="submit" class="przyciskNon" value="Skopiuj dane" />
                      <button type="button" class="przyciskNon" onclick="cofnij('wysylka','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','moduly');">Powrót</button> 
                    </div>

                    <?php
                    unset($nazwa, $objasnienie, $informacja, $tlumaczenie_z, $objasnienie_z, $informacja_z);
                    
                } else {
                 
                    echo '<div class="pozycja_edytowana">Brak danych do wyświetlenia</div>';
                  
                }

            } else {
            
                echo '<div class="pozycja_edytowana">Brak danych do wyświetlenia</div>';
            
            }

            $db->close_query($sql);
            unset($zapytanie, $info);

            ?>

          </div>                      
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}