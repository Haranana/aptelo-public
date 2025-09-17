<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        $id_grupy = (int)$_POST['id'];
        //
        $pola = array(
                array('pages_group_title',$filtr->process($_POST['opis'])));
                
        //
        $db->update_query('pages_group' , $pola, " pages_group_id = '".$id_grupy."'");	
        unset($pola);
        
        // kasuje rekordy w tablicy
        $db->delete_query('pages_group_description' , " pages_group_id = '".$id_grupy."'");              
        
        $ile_jezykow = Funkcje::TablicaJezykow();
        for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
            //
            // jezeli nazwa w innym jezyku nie jest wypelniona
            if ( $w > 0 ) {
                if (empty($_POST['nazwa_'.$w])) {
                    $_POST['nazwa_'.$w] = $_POST['nazwa_0'];
                }
            }
            //        
            $pola = array(
                    array('pages_group_id',$id_grupy),
                    array('language_id',$ile_jezykow[$w]['id']),
                    array('pages_group_name',$filtr->process($_POST['nazwa_'.$w])));                        
            $sql = $db->insert_query('pages_group_description' , $pola);
            unset($pola);
            
        }     
        unset($ile_jezykow);
        
        if ( isset($_POST['zakladka']) && (int)$_POST['zakladka'] > 0 ) {
          
            Funkcje::PrzekierowanieURL('/zarzadzanie/wyglad/wyglad.php?zakladka='.(int)$_POST['zakladka']);
          
          } else {
          
            Funkcje::PrzekierowanieURL('strony_informacyjne_grupy.php?id_poz='.(int)$_POST["id"]);
            
        }   
        
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Edycja pozycji</div>
    <div id="cont">
          
          <form action="strony_informacyjne/strony_informacyjne_grupy_edytuj.php" method="post" id="wygladForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Edycja danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "SELECT * FROM pages_group WHERE pages_group_id = '" . (int)$_GET['id_poz'] . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
            
                $info = $sql->fetch_assoc();
                ?>
                
                <script>
                $(document).ready(function() {
                    $("#wygladForm").validate({
                      rules: {
                        opis: {
                          required: true
                        },                       
                        nazwa_0: {
                          required: true
                        }                
                      },
                      messages: {      
                        opis: {
                          required: "Pole jest wymagane."
                        },                     
                        nazwa_0: {
                          required: "Pole jest wymagane."
                        }               
                      }
                    });
                });
                </script>                   
            
                <div class="pozycja_edytowana">
                
                    <input type="hidden" name="akcja" value="zapisz" />
                
                    <input type="hidden" name="id" value="<?php echo (int)$_GET['id_poz']; ?>" />
                    
                    <?php if (isset($_GET['zakladka']) && (int)$_GET['zakladka'] > 0 ) { ?>
                    <input type="hidden" name="zakladka" value="<?php echo (int)$_GET['zakladka']; ?>" />
                    <?php } ?>                     
                    
                    <?php $ile_jezykow = Funkcje::TablicaJezykow(); ?>
                    
                    <div class="info_tab">
                    <?php
                    for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                        echo '<span id="link_'.$w.'" class="a_href_info_tab" onclick="gold_tabs(\''.$w.'\')">'.$ile_jezykow[$w]['text'].'</span>';
                    }                    
                    ?>                   
                    </div>    

                    <div style="clear:both"></div>
                    
                    <div class="info_tab_content">
                    
                        <?php
                        for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                        
                            // pobieranie danych jezykowych
                            $zapytanie_jezyk = "select distinct * from pages_group_description where pages_group_id = '".(int)$_GET['id_poz']."' and language_id = '" .$ile_jezykow[$w]['id']."'";
                            $sqls = $db->open_query($zapytanie_jezyk);
                            $nazwa = $sqls->fetch_assoc();   
                            
                            ?>
                            
                            <div id="info_tab_id_<?php echo $w; ?>" style="display:none;">
                            
                                <p>
                                   <?php if ($w == '0') { ?>
                                    <label class="required" for="nazwa_0">Nazwa:</label>
                                    <input type="text" name="nazwa_<?php echo $w; ?>" size="45" value="<?php echo (isset($nazwa['pages_group_name']) ? $nazwa['pages_group_name'] : ''); ?>" id="nazwa_0" /><em class="TipIkona"><b>Nazwa wyświetlana jeżeli grupa zostanie wybrana do wyświetlania w górnym menu</b></em>
                                   <?php } else { ?>
                                    <label for="nazwa_<?php echo $w; ?>">Nazwa:</label>   
                                    <input type="text" name="nazwa_<?php echo $w; ?>" id="nazwa_<?php echo $w; ?>" size="45" value="<?php echo (isset($nazwa['pages_group_name']) ? $nazwa['pages_group_name'] : ''); ?>" /><em class="TipIkona"><b>Nazwa wyświetlana jeżeli grupa zostanie wybrana do wyświetlania w górnym menu</b></em>
                                   <?php } ?>
                                </p> 
                                            
                            </div>
                            <?php                    
                            $db->close_query($sqls);
                            unset($zapytanie_jezyk, $nazwa);
                        }                    
                        ?>         
                        
                    </div>                        

                    <p>
                        <label class="required" for="kod">Kod grupy:</label>
                        <input type="text" name="kod" id="kod" value="<?php echo $info['pages_group_code']; ?>" size="40" disabled="disabled" /><em class="TipIkona"><b>Kod grupy stron jaki będzie używany w szablonach - nie może zawierać spacji i polskich znaków - musi być unikalny - np STRONY_INFORMACYJNE_STOPKA</b></em>
                    </p>
                    
                    <p>
                        <label class="required" for="opis">Opis grupy:</label>
                        <input type="text" name="opis" id="opis" value="<?php echo $info['pages_group_title']; ?>" size="80" /><em class="TipIkona"><b>Opis będzie wyświetlany przy dodawaniu nowych stron informacyjnych</b></em>
                    </p>
                    
                    <script>
                    gold_tabs('0');
                    </script>                      

                </div>

                <div class="przyciski_dolne">
                
                  <input type="submit" class="przyciskNon" value="Zapisz dane" />
                  
                  <?php 
                  // jezeli jest get zakladka wraca do ustawien wygladu
                  if (isset($_GET['zakladka']) ) { ?>
                  
                  <button type="button" class="przyciskNon" onclick="cofnij('wyglad','<?php echo Funkcje::Zwroc_Wybrane_Get(array('zakladka')); ?>','wyglad');">Powrót</button> 
                  
                  <?php } else { ?>
                  
                  <button type="button" class="przyciskNon" onclick="cofnij('strony_informacyjne_grupy','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','strony_informacyjne');">Powrót</button>  
                  
                  <?php } ?>
   
                </div>                 

                <?php

                unset($info);            
            
            } else {
            
                echo '<div class="pozycja_edytowana">Brak danych do wyświetlenia</div>';
            
            }
            
            $db->close_query($sql);
            unset($zapytanie);                    
            
            ?>

          </div>                      
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}
