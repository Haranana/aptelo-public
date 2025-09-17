<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        $id_edytowanej_pozycji = (int)$_POST['id'];
        //
        $pola = array(array('customers_name',$filtr->process($_POST['wystawiajacy'])),
                      array('reviews_rating',$filtr->process($_POST['ocena'])),
                      array('date_added',((empty($_POST['data_dodania'])) ? 'now()' : date('Y-m-d H:i', FunkcjeWlasnePHP::my_strtotime($filtr->process($_POST['data_dodania']))))));

        //	
        $sql = $db->update_query('reviews', $pola, 'reviews_id = ' . (int)$id_edytowanej_pozycji);
        unset($pola);        
        
        $pola = array(
                array('reviews_id',(int)$id_edytowanej_pozycji),
                array('languages_id',(int)$_POST['jezyk']),
                array('reviews_text',$filtr->process($_POST['tresc_recenzji'])));          
        $sql = $db->update_query('reviews_description' , $pola, 'reviews_id = ' . (int)$id_edytowanej_pozycji);
        unset($pola);
        
        if ( isset($_POST['zakladka']) && isset($_POST['produkt']) ) {
        
             Funkcje::PrzekierowanieURL('/zarzadzanie/produkty/produkty_edytuj.php?id_poz=' . (int)$_POST['produkt'] . '&zakladka=' . (int)$_POST['zakladka']);
             
        } else {
          
             Funkcje::PrzekierowanieURL('recenzje.php?id_poz='.(int)$id_edytowanej_pozycji);
             
        }

    }   

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>

    <div id="naglowek_cont">Edycja pozycji</div>
    <div id="cont">

          <form action="recenzje/recenzje_edytuj.php" method="post" id="recenzjeForm" class="cmxform"> 
          
          <div class="poleForm">
            <div class="naglowek">Edycja danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select distinct * from reviews r, reviews_description rd where r.reviews_id = rd.reviews_id and r.reviews_id = '".(int)$_GET['id_poz']."'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {

                $info = $sql->fetch_assoc();  
                ?>            
            
                <div class="pozycja_edytowana">    

                    <input type="hidden" name="akcja" value="zapisz" />

                    <input type="hidden" name="id" value="<?php echo $info['reviews_id']; ?>" />
                    
                    <?php if ( isset($_GET['zakladka']) ) { ?>
                    
                    <input type="hidden" name="zakladka" value="<?php echo (int)$_GET['zakladka']; ?>" />
                    
                    <?php } ?>
                    
                    <?php if ( isset($_GET['produkt']) ) { ?>
                    
                    <input type="hidden" name="produkt" value="<?php echo (int)$_GET['produkt']; ?>" />
                    
                    <?php } ?>                    
                    
                    <div class="info_content">

                    <script>
                    $(document).ready(function() {
                    
                        $("#recenzjeForm").validate({
                          rules: {
                            wystawiajacy: {
                              required: true
                            },
                            tresc_recenzji: {
                              required: true
                            }                     
                          },
                          messages: {
                            wystawiajacy: {
                              required: "Pole jest wymagane."
                            },
                            tresc_recenzji: {
                              required: "Pole jest wymagane."
                            }                       
                          }
                        });                

                        $('input.datepicker').Zebra_DatePicker({
                           format: 'd-m-Y H:i',
                           inside: false,
                           readonly_element: true
                        });                 
                    
                    });
                    </script>  
                    
                    <p>
                        <label class="required" for="wystawiajacy">Nazwa opiniującego:</label>
                        <input type="text" name="wystawiajacy" id="wystawiajacy" value="<?php echo $info['customers_name']; ?>" size="30" />                                        
                    </p>                
                
                    <p>
                        <label class="required" for="data_dodania">Data dodania:</label>
                        <input type="text" name="data_dodania" id="data_dodania" value="<?php echo ((Funkcje::czyNiePuste($info['date_added'])) ? date('d-m-Y H:i',FunkcjeWlasnePHP::my_strtotime($info['date_added'])) : ''); ?>" size="20" class="datepicker" />                                        
                    </p>
                    
                    <p>
                        <label for="jezyk">Język recenzji:</label>
                        <?php
                        $tablica_jezykow = Funkcje::TablicaJezykow();                 
                        echo Funkcje::RozwijaneMenu('jezyk',$tablica_jezykow,$info['languages_id'], 'id="jezyk"');
                        ?>                                   
                    </p>                    
                    
                    <p>
                        <label class="required" for="tresc_recenzji">Opinia:</label>
                        <textarea name="tresc_recenzji" id="tresc_recenzji" rows="10" cols="50"><?php echo $info['reviews_text']; ?></textarea><em class="TipIkona"><b>Treść recenzji - bez tagów HTML</b></em>
                    </p>
                    
                    <table>
                        <tr>
                            <td class="OcenaTabela"><label>Ocena:</label></td>
                            <td>
                              <input type="radio" value="1" name="ocena" id="gwiazdka_1" <?php echo (($info['reviews_rating'] == '1') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="gwiazdka_1"><img alt="Ocena 1/5" src="obrazki/recenzje/star_1.png" /></label><br />
                              <input type="radio" value="2" name="ocena" id="gwiazdka_2" <?php echo (($info['reviews_rating'] == '2') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="gwiazdka_2"><img alt="Ocena 2/5" src="obrazki/recenzje/star_2.png" /></label><br />
                              <input type="radio" value="3" name="ocena" id="gwiazdka_3" <?php echo (($info['reviews_rating'] == '3') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="gwiazdka_3"><img alt="Ocena 3/5" src="obrazki/recenzje/star_3.png" /></label><br />
                              <input type="radio" value="4" name="ocena" id="gwiazdka_4" <?php echo (($info['reviews_rating'] == '4') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="gwiazdka_4"><img alt="Ocena 4/5" src="obrazki/recenzje/star_4.png" /></label><br />
                              <input type="radio" value="5" name="ocena" id="gwiazdka_5" <?php echo (($info['reviews_rating'] == '5') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="gwiazdka_5"><img alt="Ocena 5/5" src="obrazki/recenzje/star_5.png" /></label>
                            </td>
                        </tr>
                    </table>                     

                    </div>
                    
                </div>
                
                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Zapisz dane" />
                  <?php if ( isset($_GET['zakladka']) && isset($_GET['produkt']) ) { ?>
                  <button type="button" class="przyciskNon" onclick="cofnij('produkty_edytuj','?id_poz=<?php echo (int)$_GET['produkt']; ?>&zakladka=<?php echo (int)$_GET['zakladka']; ?>','produkty');">Powrót</button>    
                  <?php } else { ?>
                  <button type="button" class="przyciskNon" onclick="cofnij('recenzje','');">Powrót</button>      
                  <?php } ?>
                </div>

            <?php 
            $db->close_query($sql);
            unset($info);

            } else {
            
                echo '<div class="pozycja_edytowana">Brak danych do wyświetlenia</div>';
            
            }
            ?>                    
            
          </div>

          </form>

    </div>
    
    <?php
    include('stopka.inc.php');    
    
} ?>