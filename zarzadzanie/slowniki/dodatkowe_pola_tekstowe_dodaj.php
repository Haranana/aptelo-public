<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        $pola = array(
                array('products_text_fields_type',$filtr->process($_POST['typ_pola'])),
                array('products_text_fields_order',(int)$_POST['sort']),
                array('products_text_fields_required',$filtr->process($_POST['wymagalnosc_pola'])),
                array('products_text_fields_status','1'));
        
        if ( $_POST['typ_pola'] == '2' ) {
            //
            $pola[] = array('products_text_fields_file_type',$filtr->process($_POST['formaty']));
            $pola[] = array('products_text_fields_file_size',(int)$_POST['rozmiar']);
            //
        }
        
        $sql = $db->insert_query('products_text_fields' , $pola);
        $id_dodanej_pozycji = $db->last_id_query();
        
        unset($pola);
        
        $ile_jezykow = Funkcje::TablicaJezykow();
        for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {

            //
            if (!empty($_POST['nazwa_'.$w])) {
              $nazwa = $filtr->process($_POST['nazwa_'.$w]);
            } else {
              $nazwa = $filtr->process($_POST['nazwa_0']);
            }

            $pola = array(
                    array('products_text_fields_description',$filtr->process($_POST['edytor_'.$w])),
                    array('products_text_fields_id',$id_dodanej_pozycji),
                    array('languages_id',$ile_jezykow[$w]['id']),
                    array('products_text_fields_name',$nazwa),
                    array('products_text_fields_default_text',$filtr->process($_POST['domyslny_'.$w])));
                    
            $sql = $db->insert_query('products_text_fields_info' , $pola);
            unset($pola);
        }
        //
        if (isset($id_dodanej_pozycji) && $id_dodanej_pozycji > 0) {
            Funkcje::PrzekierowanieURL('dodatkowe_pola_tekstowe.php?id_poz='.$id_dodanej_pozycji);
        } else {
            Funkcje::PrzekierowanieURL('dodatkowe_pola_tekstowe.php');
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

          <form action="slowniki/dodatkowe_pola_tekstowe_dodaj.php" method="post" id="slownikForm" class="cmxform">          

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
                              <label class="required" for="nazwa_0">Nazwa:</label>
                              <input type="text" name="nazwa_<?php echo $w; ?>" size="45" value="" id="nazwa_0" />
                             <?php } else { ?>
                              <label for="nazwa_<?php echo $w; ?>">Nazwa:</label>   
                              <input type="text" name="nazwa_<?php echo $w; ?>" size="45" value="" id="nazwa_<?php echo $w; ?>" />
                             <?php } ?>
                          </p> 
                          
                          <p>
                              <label for="domyslny_<?php echo $w; ?>">Domyślny tekst:</label>   
                              <input type="text" name="domyslny_<?php echo $w; ?>" id="domyslny_<?php echo $w; ?>" class="domyslne" size="50" value="" /><em class="TipIkona"><b>Domyślny tekst wyświetlany w polu - kasowany automatycznie po kliknięciu przez klienta w pole tekstowe - nie dotyczy opcji wgrywania plików oraz daty</b></em>
                          </p>                           
                    
                          <div class="edytor">
                            <textarea cols="50" rows="10" id="edytor_<?php echo $w; ?>" name="edytor_<?php echo $w; ?>"></textarea>
                          </div>                          

                      </div>
                      
                    <?php                    
                    }                    
                    ?>                      
                </div>                
            
                <p>
                  <label>Typ pola:</label>
                  <input type="radio" value="0" name="typ_pola" id="pole_input" checked="checked" onclick="$('#plik').slideUp()" /><label class="OpisFor" for="pole_input">Input<em class="TipIkona"><b>Pole tekstowe stworzone za pomocą znacznika INPUT pozwala na wpisanie tylko jednego wiersza tekstu</b></em></label>
                  <input type="radio" value="1" name="typ_pola" id="pole_textarea" onclick="$('#plik').slideUp()"  /><label class="OpisFor" for="pole_textarea">Textarea<em class="TipIkona"><b>Pole tekstowe stworzone za pomocą znacznika TEXTAREA pozwala na wpisanie wielu wierszy tekstu</b></em></label>
                  <input type="radio" value="2" name="typ_pola" id="pole_plik" onclick="$('#plik').slideDown()" /> <label class="OpisFor" for="pole_plik">Wgrywanie pliku<em class="TipIkona"><b>Pole z możliwością wgrania pliku</b></em></label>
                  <input type="radio" value="3" name="typ_pola" id="pole_data" onclick="$('#plik').slideUp()" /> <label class="OpisFor" for="pole_data">Data<em class="TipIkona"><b>Pole z możliwością wyboru daty z kalendarza</b></em></label>
                </p> 

                <p>
                  <label>Wymagane:</label>
                  <input type="radio" value="1" name="wymagalnosc_pola" id="wymagane_tak" /> <label class="OpisFor" for="wymagane_tak">tak<em class="TipIkona"><b>Wypełnienie pola nie będzie wymagane podczas dodawania do koszyka produktu</b></em></label>
                  <input type="radio" value="0" name="wymagalnosc_pola" id="wymagane_nie" checked="checked" /> <label class="OpisFor" for="wymagane_nie">nie<em class="TipIkona"><b>Wypełnienie pola nie będzie wymagane podczas dodawania do koszyka produktu</b></em></label>
                </p> 

                <p>
                  <label for="sort">Kolejność wyświetlania:</label>
                  <input type="text" class="calkowita" name="sort" id="sort" value="" size="5" />
                </p>         

                <div id="plik" style="display:none">
                
                    <p>
                      <label for="formaty">Dopuszczalne formaty plików:</label>
                      <input type="text" name="formaty" id="formaty" value="" size="50" /><em class="TipIkona"><b>Będzie można wgrać / dołączyć do produktu tylko pliki w podanych formatach - każdy format musi być rozdzielony przecinkiem np: jpg,png,gif</b></em>
                    </p>

                    <p>
                      <label for="rozmiar">Maksymalny rozmiar pliku:</label>
                      <input type="text" name="rozmiar" id="rozmiar" class="calkowita" value="" size="5" /><em class="TipIkona"><b>Maksymalny rozmiar pliku jaki będzie można wgrać / dołączyć do produktu - w MB</b></em>
                    </p>                     
                
                </div>

                <script>
                gold_tabs('0','edytor_',150);
                </script>                 
                
            </div>

            <div class="przyciski_dolne">
              <input type="submit" class="przyciskNon" value="Zapisz dane" />
              <button type="button" class="przyciskNon" onclick="cofnij('dodatkowe_pola_tekstowe','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','slowniki');">Powrót</button>   
            </div>            

          </div>                      
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}