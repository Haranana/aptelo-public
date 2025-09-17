<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        $pola = array(array('delivery_company_link',$filtr->process($_POST['link'])));
        $db->update_query('delivery_company' , $pola, " delivery_company_id = '" . (int)$_POST["id"] . "'"); 
        unset($pola);        
        //
        // kasuje rekordy w tablicy
        $db->delete_query('delivery_company_description' , " delivery_company_id = '" . (int)$_POST["id"] . "'");        
        
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
                    array('delivery_company_id',$filtr->process($_POST["id"])),
                    array('delivery_company_name',$filtr->process($_POST['nazwa_'.$w])),
                    array('language_id',$ile_jezykow[$w]['id'])); 
            $db->insert_query('delivery_company_description' , $pola);
            unset($pola);
            //           
        }              
        //
        Funkcje::PrzekierowanieURL('firmy_wysylkowe.php?id_poz='.(int)$_POST["id"]);
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
                nazwa_0: {
                  required: true
                }                
              },
              messages: {
                nazwa_0: {
                  required: "Pole jest wymagane."
                }               
              }
            });
          });          
          </script>         

          <form action="slowniki/firmy_wysylkowe_edytuj.php" method="post" id="slownikForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Edycja danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select * from delivery_company where delivery_company_id = '" . (int)$_GET['id_poz'] . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
                ?>
            
                <div class="pozycja_edytowana">
                
                    <input type="hidden" name="akcja" value="zapisz" />
                
                    <input type="hidden" name="id" value="<?php echo (int)$_GET['id_poz']; ?>" />
                    
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
                            $zapytanie = "select distinct * from delivery_company dc, delivery_company_description dcd where dc.delivery_company_id = dcd.delivery_company_id and dc.delivery_company_id = '" . (int)$_GET['id_poz'] . "' and language_id = '" . $ile_jezykow[$w]['id'] . "'";
                            $sqls = $db->open_query($zapytanie);
                            $nazwa = $sqls->fetch_assoc();   
                            
                            ?>
                            
                            <div id="info_tab_id_<?php echo $w; ?>" style="display:none;">
                            
                                <p>
                                   <?php if ($w == '0') { ?>
                                    <label class="required" for="nazwa_0">Nazwa przewoźnika:</label>
                                    <input type="text" name="nazwa_<?php echo $w; ?>" size="45" value="<?php echo (isset($nazwa['delivery_company_name']) ? $nazwa['delivery_company_name'] : ''); ?>" id="nazwa_0" />
                                   <?php } else { ?>
                                    <label for="nazwa_<?php echo $w; ?>">Nazwa przewoźnika:</label>   
                                    <input type="text" name="nazwa_<?php echo $w; ?>" id="nazwa_<?php echo $w; ?>" size="45" value="<?php echo (isset($nazwa['delivery_company_name']) ? $nazwa['delivery_company_name'] : ''); ?>" />
                                   <?php } ?>
                                </p>

                            </div>
                            <?php                    
                            $db->close_query($sqls);
                            unset($zapytanie);
                        }                    
                        ?>                      
                    </div>
                    
                    <p>
                      <label for="url">Adres URL do strony śledzenia:</label>
                      <input type="text" name="link" id="url" value="<?php echo $nazwa['delivery_company_link']; ?>" size="75" />
                    </p>   
                    
                    <span class="maleInfo" style="margin-left:25px">Jeżeli w linku ma być zawarty nr przesyłki należy dodać znacznik {NR_PRZESYLKI} - np https://inpost.pl/sledzenie-przesylek?number={NR_PRZESYLKI}</span>
                    
                    <script>
                    gold_tabs('0');                  
                    </script>  
                  
                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Zapisz dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('firmy_wysylkowe','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','slowniki');">Powrót</button>           
                </div>                 

            <?php
            
            $db->close_query($sql);          
            
            } else {
            
                echo '<div class="pozycja_edytowana">Brak danych do wyświetlenia</div>';
            
            }
            ?>

          </div>                      
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}
