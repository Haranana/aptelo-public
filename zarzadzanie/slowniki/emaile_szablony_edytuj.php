<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

        if ($_POST["domyslny"] == '1') {
                $pola = array(array('template_default','0'));
                $db->update_query('email_templates' , $pola);	
                //
        }
        //
        $pola = array(
                    array('template_name',$filtr->process($_POST["nazwa"])),
                    array('template_default',$filtr->process($_POST["domyslny"])));
                    
        //			
        $db->update_query('email_templates' , $pola, " template_id = '".(int)$_POST["id"]."'");	
        unset($pola);             

        //
        $db->delete_query('email_templates_description', "template_id = '".(int)$_POST["id"]."'");

        $ile_jezykow = Funkcje::TablicaJezykow();
        for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                //
                if (!empty($_POST['edytor_'.$w])) {
                        $pola = array(
                                    array('template_id',(int)$_POST["id"]),
                                    array('description',$filtr->process($_POST['edytor_'.$w])),
                                    //array('description',$_POST['edytor_'.$w]),
                                    array('language_id',$ile_jezykow[$w]['id'])
                         );
                } else {
                        $pola = array(
                                    array('template_id',(int)$_POST["id"]),
                                    array('description',$filtr->process($_POST['edytor_0'])),
                                    //array('description',$_POST['edytor_0']),
                                    array('language_id',$ile_jezykow[$w]['id'])
                         );
                }
                $sql = $db->insert_query('email_templates_description' , $pola);
                unset($pola);
        }                
        //
        Funkcje::PrzekierowanieURL('emaile_szablony.php?id_poz='.(int)$_POST["id"]);
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Edycja pozycji</div>
    <div id="cont">
                
    <script>
    $(document).ready(function() {
      $("#slownikForm").validate({
          rules: {
              nazwa: {
                  required: true,
              }                            
          }
      });
    });
    </script>         
                
    <div class="poleForm">
        <div class="naglowek">Edycja danych</div>                    

        <?php

        if ( !isset($_GET['id_poz']) ) {
             $_GET['id_poz'] = 0;
        }        

        $zapytanie = "SELECT s.template_id, s.template_name, s.template_default, sz.description FROM email_templates s LEFT JOIN email_templates_description sz ON sz.template_id AND sz.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."' WHERE s.template_id = '" . (int)$_GET['id_poz'] . "'";

        $sql = $db->open_query($zapytanie);

        $pokazObjasnienia = false;

        if ((int)$db->ile_rekordow($sql) > 0) {

            $info = $sql->fetch_assoc();

            $pokazObjasnienia = true;
            ?>
            
            <form action="slowniki/emaile_szablony_edytuj.php" method="post" id="slownikForm" class="cmxform">   

            <div class="pozycja_edytowana">
            
                <input type="hidden" name="akcja" value="zapisz" />
                
                <input type="hidden" name="id" value="<?php echo (int)$_GET['id_poz']; ?>" />

                <?php $ile_jezykow = Funkcje::TablicaJezykow(); ?>

                <div class="info_tab">
                <?php
                for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                     echo '<span id="link_'.$w.'" class="a_href_info_tab" onclick="gold_tabs(\''.$w.'\',\'edytor_\', 400, \'fullpage\')">'.$ile_jezykow[$w]['text'].'</span>';
                }                                        
                ?>                                     
                </div>
                
                <div style="clear:both"></div>
                
                <div class="info_tab_content">
                
                    <?php
                    for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                    
                        // pobieranie danych jezykowych
                        $zapytanie_jezyk = "select distinct * from email_templates_description where template_id = '".(int)$_GET['id_poz']."' and language_id = '" .$ile_jezykow[$w]['id']."'";
                        $sqls = $db->open_query($zapytanie_jezyk);
                        $nazwa = $sqls->fetch_assoc();     
                        ?>
                        
                        <div id="info_tab_id_<?php echo $w; ?>" style="display:none;">
                        
                                <div class="edytor">
                                    <textarea cols="50" rows="30" id="edytor_<?php echo $w; ?>" name="edytor_<?php echo $w; ?>"><?php echo (isset($nazwa['description']) ? $nazwa['description'] : ''); ?></textarea>
                                </div>     
                                                        
                                <?php
                                $db->close_query($sqls);
                                unset($nazwa); 
                                ?>

                        </div>
                        <?php                                        
                    }                                        
                    ?>    
                        
                </div>
                
                <script>
                gold_tabs('0','edytor_', 400, 'fullpage');
                </script> 

                <p>
                    <label class="required" for="nazwa">Nazwa szablonu:</label>
                    <input type="text" name="nazwa" size="60" value="<?php echo $info['template_name']; ?>" id="nazwa" />
                </p>

                <?php if ($info['template_default'] == '0') { ?>                                        
             
                <p>
                    <label>Czy szablon jest domyślnym:</label>
                    <input type="radio" value="0" name="domyslny" id="domyslny_nie" checked="checked" /><label class="OpisFor" for="domyslny_nie">nie<em class="TipIkona"><b>W oparciu o szablon domyślny będą generowne wszystkiem maile wysyłane ze sklepu</b></em></label>
                    <input type="radio" value="1" name="domyslny" id="domyslny_tak" /><label class="OpisFor" for="domyslny_tak">tak<em class="TipIkona"><b>W oparciu o szablon domyślny będą generowne wszystkiem maile wysyłane ze sklepu</b></em></label>                                            
                </p>

                <?php } else { ?>
                
                <p>
                    <label>Czy szablon jest domyślnym:</label>
                    <input type="radio" value="0" name="domyslny" id="domyslny_nie" /><label class="OpisFor" for="domyslny_nie">nie<em class="TipIkona"><b>W oparciu o szablon domyślny będą generowne wszystkiem maile wysyłane ze sklepu</b></em></label>
                    <input type="radio" value="1" name="domyslny" id="domyslny_tak" checked="checked" /><label class="OpisFor" for="domyslny_tak">tak<em class="TipIkona"><b>W oparciu o szablon domyślny będą generowne wszystkiem maile wysyłane ze sklepu</b></em></label>                                           
                </p>
                
                <?php } ?>    
            </div>

            <div class="przyciski_dolne">
                <input type="submit" class="przyciskNon" value="Zapisz dane" />
                <button type="button" class="przyciskNon" onclick="cofnij('emaile_szablony','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','slowniki');">Powrót</button>     
            </div>

            </form>            

        <?php

        $db->close_query($sql);
        unset($info);                        
                    
        } else {

            echo '<div class="pozycja_edytowana">Brak danych do wyświetlenia</div>';

        }
        ?>

    </div>                                            

    <?php
    if ( $pokazObjasnienia == true ) {
    ?>

    <div class="objasnienia">
        <div class="objasnieniaTytul">Znaczniki, które możesz użyć w tym e-mailu:</div>
        <div class="objasnieniaTresc">

        <div style="padding-bottom:10px;font-weight:bold;">Treść wiadomości</div>
            <ul class="mcol">
                <li><b>{CONTENT}</b> - Zawartość maila</li>
                <li><b>{ADRES_URL_SKLEPU}</b> - Adres internetowy sklepu</li>
            </ul>

            <div style="padding-bottom:10px;font-weight:bold;">Dane sklepu</div>
            <ul class="mcol">
                <?php
                $zapytanie = "SELECT * FROM settings WHERE type = 'sklep' OR type = 'firma' ORDER BY type, sort";
                $sql = $db->open_query($zapytanie);

                while ($info = $sql->fetch_assoc()) {
                    echo '<li><b>{'.$info['code'].'}</b> - '.$info['description'].'</li>';
                }
                $db->close_query($sql);
                unset($zapytanie,$info);

                ?>
            </ul>
        </div>
    </div>

    <?php
    }
    unset($pokazObjasnienia);
    ?>

    </div>    
    
    <?php
    include('stopka.inc.php');

}
