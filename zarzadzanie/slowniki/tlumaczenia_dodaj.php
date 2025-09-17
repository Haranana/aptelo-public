<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

        if ( isset($_POST["sekcja"]) ) {
          $id_dodanej_sekcji = $_POST["sekcja"];
        }

        //
        if ( isset($_POST["sekcja_nowa"]) && $_POST["sekcja_nowa"] != '') {
            $pola = array(array('section_name',$filtr->process($_POST['sekcja_nowa'])));
            $db->insert_query('translate_section' , $pola);
            $id_dodanej_sekcji = $db->last_id_query();
            unset($pola);
        }

        $pola = array(
            array('translate_constant',$filtr->process($_POST['zmienna'])),
            array('section_id',$id_dodanej_sekcji)
            );
        $db->insert_query('translate_constant' , $pola);
        $id_dodanego_wyrazenia = $db->last_id_query();
        unset($pola);

        //
        $ile_jezykow = Funkcje::TablicaJezykow();
        for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
            //
            if (!empty($_POST['nazwa_'.$w])) {
                $pola = array(
                        array('translate_value',$filtr->process($_POST['nazwa_'.$w])),
                        array('translate_constant_id',$id_dodanego_wyrazenia),
                        array('language_id',$ile_jezykow[$w]['id'])
                 );
            } else {
                $pola = array(
                        array('translate_value',$filtr->process($_POST['nazwa_0'])),
                        array('translate_constant_id',$id_dodanego_wyrazenia),
                        array('language_id',$ile_jezykow[$w]['id'])
                 );
            }
            $sql = $db->insert_query('translate_value' , $pola);
            unset($pola);
        }        
        //
        if (isset($id_dodanego_wyrazenia) && $id_dodanego_wyrazenia > 0) {
            Funkcje::PrzekierowanieURL('tlumaczenia.php?id_poz='.$id_dodanego_wyrazenia);
        } else {
            Funkcje::PrzekierowanieURL('tlumaczenia.php');
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

            $("#slownikForm").validate({
              rules: {
                nazwa_0: {
                  required: true
                },              
                zmienna: {
                  required: true,
                  remote: "ajax/sprawdz_czy_zmienna_tlumaczenia.php"
                },              
                sekcja_nowa: {
                  remote: "ajax/sprawdz_czy_sekcja_tlumaczenia.php"
                }              
              },
              messages: {
                zmienna: {
                  required: "Pole jest wymagane.",
                  remote: "Zmienna o takiej nazwie już istniej.e"
                },
                sekcja_nowa: {
                  remote: "Sekcja o takiej nazwie już istnieje - wybierz ją z listy."
                }
              }
            });
          });

          function updateKey() {
              var key=$("#zmienna").val();
              key=key.replace(" ","_");
              $("#zmienna").val(key);
          }
          </script>     

          <form action="slowniki/tlumaczenia_dodaj.php" method="post" id="slownikForm" class="cmxform">          

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
                                <label class="required" for="nazwa_0">Treść:</label>
                                <textarea cols="120" rows="10" name="nazwa_<?php echo $w; ?>" id="nazwa_0"></textarea>
                               <?php } else { ?>
                                <label for="nazwa_<?php echo $w; ?>">Treść:</label>   
                                <textarea cols="120" rows="10" name="nazwa_<?php echo $w; ?>" id="nazwa_<?php echo $w; ?>"></textarea>
                               <?php } ?>
                            </p> 
                                        
                        </div>
                        <?php                    
                    }                    
                    ?>                      
                </div>
                
                <p>
                  <label class="required" for="zmienna">Nazwa zmiennej:</label>   
                  <input type="text" name="zmienna" id="zmienna" size="53" value="" class="bestupper" onkeyup="updateKey();" /><em class="TipIkona"><b>Nazwa zmiennej, która w sklepie będzie zastępowana przetłumaczonym tekstem</b></em>
                </p>

                <p>
                    <label for="sekcja">Sekcja:</label>
                    <?php  $tablica = Tlumaczenia::ListaSekcjiTlumaczen(false);
                    //
                    $filtr = '';
                    if ( isset($_GET['szukaj_sekcja']) && $_GET['szukaj_sekcja'] != '0' ) {
                        $filtr = (int)$_GET['szukaj_sekcja'];
                    }                 
                    //
                    echo Funkcje::RozwijaneMenu('sekcja', $tablica, $filtr, 'id="sekcja"'); 
                    ?>
                    &nbsp;<input type="text" name="sekcja_nowa" id="sekcja_nowa" size="40" value="" class="bestupper" /><em class="TipIkona"><b>Jeżeli sekcji nie ma na liście możesz dopisać nową</b></em>
                </p>
                
                <script>
                gold_tabs('0');
                </script>                    
               
            </div>

            <div class="przyciski_dolne">
              <input type="submit" class="przyciskNon" value="Zapisz dane" />
              <button type="button" class="przyciskNon" onclick="cofnij('tlumaczenia','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','slowniki');">Powrót</button>   
            </div>            

          </div>                      
          </form>
          
          <div class="objasnienia">
              <div class="objasnieniaTytul">Znaczniki, które możesz użyć przy definiowaniu tekstu:</div>
              <div class="objasnieniaTresc">
                  <ul class="mcol">
                        <li><b>{__LINK:<span style="color:#ff0000">tutaj-link.html</span>} tutaj tekst {/__LINK}</b> - tworzy link wybranej strony w sklepie - np {__LINK:regulamin-pm-5.html}regulamin{/__LINK} utworzy ze słowa "regulamin" link do strony regulamin-pm-5.html</li>
                  </ul>
              </div> 
          </div>     

    </div>    
    
    <?php
    include('stopka.inc.php');

}
