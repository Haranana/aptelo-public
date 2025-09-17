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
                array('pages_group_code',$filtr->process($_POST['kod'])),
                array('pages_group_title',$filtr->process($_POST['opis'])));
             
        $sql = $db->insert_query('pages_group' , $pola);
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
                    array('pages_group_id',$id_dodanej_pozycji),
                    array('language_id',$ile_jezykow[$w]['id']),
                    array('pages_group_name',$filtr->process($_POST['nazwa_'.$w])));                        
            $sql = $db->insert_query('pages_group_description' , $pola);
            unset($pola);
            
        }         

        //
        if (isset($id_dodanej_pozycji) && $id_dodanej_pozycji > 0) {
            Funkcje::PrzekierowanieURL('strony_informacyjne_grupy.php?id_poz='.$id_dodanej_pozycji);
        } else {
            Funkcje::PrzekierowanieURL('strony_informacyjne_grupy.php');
        }
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Dodawanie pozycji</div>
    <div id="cont">
          
          <script type="text/javascript" src="javascript/jquery.bestupper.min.js"></script>        

          <script>
          $(document).ready(function() {
             $('.bestupper').bestupper();
          });

          function updateKey() {
              var key=$("#kod").val();
              key=key.replace(" ","_");
              $("#kod").val(key);
          }

          $(document).ready(function() {

            $("#wygladForm").validate({
              rules: {
                kod: {
                  required: true,
                  remote: "ajax/sprawdz_czy_zmienna_grupy_stron.php"
                },
                opis: {
                  required: true,
                },
                nazwa_0: {
                  required: true
                }                   
              },
              messages: {
                kod: {
                  required: "Pole jest wymagane.",
                  remote: "Grupa o takiej nazwie juz istnieje."
                },
                opis: {
                  required: "Pole jest wymagane.",
                },
                nazwa_0: {
                  required: "Pole jest wymagane."
                }                    
              }
            }); 
          });        
          </script>     

          <form action="strony_informacyjne/strony_informacyjne_grupy_dodaj.php" method="post" id="wygladForm" class="cmxform">          

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
                                <label class="required" for="nazwa_0">Nazwa:</label>
                                <input type="text" name="nazwa_<?php echo $w; ?>" size="45" value="" id="nazwa_0" /><em class="TipIkona"><b>Nazwa wyświetlana jeżeli grupa zostanie wybrana do wyświetlania w górnym menu</b></em>
                               <?php } else { ?>
                                <label for="nazwa_<?php echo $w; ?>">Nazwa:</label>   
                                <input type="text" name="nazwa_<?php echo $w; ?>" id="nazwa_<?php echo $w; ?>" size="45" value="" /><em class="TipIkona"><b>Nazwa wyświetlana jeżeli grupa zostanie wybrana do wyświetlania w górnym menu</b></em>
                               <?php } ?>
                            </p> 
                                        
                        </div>
                    <?php                    
                    }                    
                    ?>     
                    
                </div>                

                <p>
                    <label class="required" for="kod">Kod grupy:</label>
                    <input type="text" name="kod" id="kod" value="" size="40" class="bestupper" onkeyup="updateKey();" /><em class="TipIkona"><b>Kod grupy stron jaki będzie używany w szablonach - nie może zawierać spacji i polskich znaków - musi być unikalny - np STRONY_INFORMACYJNE_STOPKA</b></em>
                </p>
                
                <p>
                    <label class="required" for="opis">Opis grupy:</label>
                    <input type="text" name="opis" id="opis" value="" size="80" /><em class="TipIkona"><b>Opis będzie wyświetlany przy dodawaniu nowych stron informacyjnych</b></em>
                </p>
                
                <script>
                gold_tabs('0');
                </script>                  

            </div>

            <div class="przyciski_dolne">
              <input type="submit" class="przyciskNon" value="Zapisz dane" />
              <button type="button" class="przyciskNon" onclick="cofnij('strony_informacyjne_grupy','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','strony_informacyjne');">Powrót</button>   
            </div>            

          </div>                      
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}
