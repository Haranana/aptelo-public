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

        $db->insert_query('delivery_company' , $pola);	
        $id_dodanej_pozycji = $db->last_id_query();
        
        unset($pola);
        
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
                    array('delivery_company_id',$id_dodanej_pozycji),
                    array('language_id',$ile_jezykow[$w]['id']),
                    array('delivery_company_name',$filtr->process($_POST['nazwa_'.$w])));
                    
            $sql = $db->insert_query('delivery_company_description' , $pola);
            unset($pola);
        }        
        //
        if (isset($id_dodanej_pozycji) && $id_dodanej_pozycji > 0) {
            Funkcje::PrzekierowanieURL('firmy_wysylkowe.php?id_poz='.$id_dodanej_pozycji);
        } else {
            Funkcje::PrzekierowanieURL('firmy_wysylkowe.php');
        }
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Dodawanie pozycji</div>
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

          <form action="slowniki/firmy_wysylkowe_dodaj.php" method="post" id="slownikForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Dodawanie danych</div>
            
            <div class="pozycja_edytowana">
            
                <input type="hidden" name="akcja" value="zapisz" />
                
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
                        ?>
                        
                        <div id="info_tab_id_<?php echo $w; ?>" style="display:none;">
                        
                            <p>
                               <?php if ($w == '0') { ?>
                                <label class="required" for="nazwa_0">Nazwa przewoźnika:</label>
                                <input type="text" name="nazwa_<?php echo $w; ?>" size="55" value="" id="nazwa_0" />
                               <?php } else { ?>
                                <label for="nazwa_<?php echo $w; ?>">Nazwa przewoźnika:</label>   
                                <input type="text" name="nazwa_<?php echo $w; ?>" size="55" value="" id="nazwa_<?php echo $w; ?>" />
                               <?php } ?>
                            </p>

                        </div>
                        <?php                    
                    }                    
                    ?>                      
                </div>
                
                <p>
                  <label for="url">Adres URL do strony śledzenia:</label>
                  <input type="text" name="link" id="url" value="" size="75" />
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

          </div>                      
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}
