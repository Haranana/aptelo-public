<?php
chdir('../'); 
 
// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        // okreslanie kolejnego nr ID
        $zapytanie_cechy = "select max(products_options_id) + 1 as next_id from products_options";
        $sqls = $db->open_query($zapytanie_cechy);
        $wynik = $sqls->fetch_assoc();    
        $kolejne_id = $wynik['next_id'];
        //
        if ( (int)$kolejne_id == 0 ) {
             $kolejne_id = 1;
        }
        //
        // jezeli cecha ma byc obrazkowa nie moze byc w formie selecta
        if ($_POST['obrazek'] == 'true') {
            $_POST['typ'] = 'radio';
        }
        if ($_POST['typ'] == 'lista') {
            $_POST['obrazek'] = 'false';
        }
        //
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
                    array('products_options_id',$kolejne_id),
                    array('products_options_name',$filtr->process($_POST['nazwa_'.$w])),
                    array('products_options_description',$filtr->process($_POST['edytor_'.$w])),
                    array('language_id',$ile_jezykow[$w]['id']),
                    array('products_options_sort_order',(int)$_POST['sort']),
                    array('products_options_images_enabled',$filtr->process($_POST['obrazek'])),
                    array('products_options_filter',$filtr->process($_POST['filtr'])),
                    array('products_options_type',$filtr->process($_POST['typ'])));
                    
            $pola[] = array('products_options_value',$filtr->process($_POST['rodzaj']));
                    
            $sql = $db->insert_query('products_options' , $pola);
            unset($pola);
        }      
        
        Funkcje::PrzekierowanieURL('cechy.php?id_cechy='.$kolejne_id);
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Dodawanie pozycji</div>
    <div id="cont">
          
          <script>
          $(document).ready(function() {
            $("#cechyForm").validate({
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

          <form action="cechy/cechy_nazwy_dodaj.php" method="post" id="cechyForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Dodawanie danych</div>
            
            <div class="pozycja_edytowana">
            
                <input type="hidden" name="akcja" value="zapisz" />
                
                <?php $ile_jezykow = Funkcje::TablicaJezykow(); ?>
                
                <div class="info_tab">
                <?php
                for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                    echo '<span id="link_'.$w.'" class="a_href_info_tab" onclick="gold_tabs(\''.$w.'\',\'edytor_\',150)">'.$ile_jezykow[$w]['text'].'</span>';
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
                                <label class="required" for="nazwa_0">Nazwa cechy:</label>
                                <input type="text" name="nazwa_<?php echo $w; ?>" size="45" value="" id="nazwa_0" />
                               <?php } else { ?>
                                <label for="nazwa_<?php echo $w; ?>">Nazwa cechy:</label>   
                                <input type="text" name="nazwa_<?php echo $w; ?>" size="45" value="" id="nazwa_<?php echo $w; ?>" />
                               <?php } ?>
                            </p> 
                            
                            <div class="edytor">
                              <textarea cols="50" rows="10" id="edytor_<?php echo $w; ?>" name="edytor_<?php echo $w; ?>"></textarea>
                            </div>                              
                                        
                        </div>
                        <?php                    
                    }                    
                    ?>                      
                </div>
                
                <script>
                gold_tabs('0','edytor_',150);
                </script>    

                <p>
                  <label for="sort">Kolejność wyświetlania:</label>
                  <input type="text" class="calkowita" name="sort" size="5" value="" id="sort" />
                </p>   

                <div id="typ_obrazek" style="display:none">                

                    <p>
                      <label>Czy cecha ma być wyświetlana w formie obrazków:</label>
                      <input type="radio" value="false" onclick="$('#filtr').slideDown()" name="obrazek" id="obrazek_nie" checked="checked" /> <label class="OpisFor" for="obrazek_nie">nie</label>
                      <input type="radio" value="true" onclick="$('#filtr').slideUp()" name="obrazek" id="obrazek_tak" /> <label class="OpisFor" for="obrazek_tak">tak</label>
                    </p>
                    
                </div>
                
                <p>
                  <label>Czy cecha ma być wyświetlana w filtrach w listingu produktów:</label>
                  <input type="radio" value="0" name="filtr" id="filtr_nie" checked="checked" /> <label class="OpisFor" for="filtr_nie">nie</label>
                  <input type="radio" value="1" name="filtr" id="filtr_tak" /> <label class="OpisFor" for="filtr_tak">tak</label>
                </p>                

                <div id="filtr">                

                    <p>
                      <label>Rodzaj wyświetlania cechy:</label>
                      <input type="radio" value="lista" onclick="$('#typ_obrazek').slideUp()" name="typ" id="typ_drop" checked="checked" /><label class="OpisFor" for="typ_drop">Drop down menu<em class="TipIkona"><b>Pole listy rozwijanej</b></em></label>
                      <input type="radio" value="radio" onclick="$('#typ_obrazek').slideDown()" name="typ" id="tyb_radio" /><label class="OpisFor" for="tyb_radio">Radio Button<em class="TipIkona"><b>Pole jednokrotnego wyboru</b></em></label>
                    </p> 

                </div>
            
                <p>
                  <label>Rodzaj wartości cechy:</label>
                  <input type="radio" value="kwota" name="rodzaj" id="rodzaj_kwota" checked="checked" /> <label class="OpisFor" for="rodzaj_kwota">kwota<em class="TipIkona"><b>Wartość cechy będzie w formie kwotowej - będzie dodawana lub odejmowana od ceny podstawowej produktu</b></em></label>
                  <input type="radio" value="procent" name="rodzaj" id="rodzaj_procent" /> <label class="OpisFor" for="rodzaj_procent">procent<em class="TipIkona"><b>Wartość cechy będzie w formie procentowej - będzie dodawana lub odejmowana od ceny podstawowej produktu i obliczona procentowo od ceny podstawowej</b></em></label>
                </p>           
                
            </div>

            <div class="przyciski_dolne">
              <input type="submit" class="przyciskNon" value="Zapisz dane" />
              <button type="button" class="przyciskNon" onclick="cofnij('cechy','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','cechy');">Powrót</button>   
            </div>            

          </div>                      
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}