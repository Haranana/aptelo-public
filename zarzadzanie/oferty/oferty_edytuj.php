<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
    
        $id_oferty = (int)$_POST['id'];
    
        $pola = array(
                array('offers_nr',$filtr->process($_POST['numer'])),
                array('offers_name',$filtr->process($_POST['nazwa'])),
                array('offers_customer',$filtr->process($_POST['adresat'])),
                array('offers_date',date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_POST['data_oferty'])))),
                array('offers_comments',$filtr->process($_POST['komentarz'])),
                array('offers_products_page',(int)$_POST['produkt_strona']),
                array('offers_summary',(int)$_POST['podsumowanie']),
                array('offers_image_size',$filtr->process($_POST['rozmiar_zdjec'])));
        
        $sql = $db->update_query('offers' , $pola, " offers_id = '".(int)$id_oferty."'");
        unset($pola);
        
        Funkcje::PrzekierowanieURL('oferty.php?id_poz='.(int)$id_oferty);
        
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>

    <div id="naglowek_cont">Edycja pozycji</div>
    <div id="cont">

          <form action="oferty/oferty_edytuj.php" method="post" id="poForm" class="cmxform"> 
          
          <div class="poleForm">
            <div class="naglowek">Edycja danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select distinct * from offers where offers_id = '".(int)$_GET['id_poz']."'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {

                $info = $sql->fetch_assoc();  
                ?>            
            
                <div class="pozycja_edytowana"> 

                    <div class="info_content">
                    
                        <input type="hidden" name="akcja" value="zapisz" />
                        
                        <input type="hidden" name="id" value="<?php echo (int)$info['offers_id']; ?>" />

                        <?php $ile_jezykow = Funkcje::TablicaJezykow(); ?>

                        <script>
                        $(document).ready(function() {
                            $("#poForm").validate({
                              rules: {
                                numer: {
                                  required: true
                                },
                                nazwa: {
                                  required: true
                                },
                                data_oferty: {
                                  required: true
                                },
                                rozmiar_zdjec: {
                                  range: [50, 400],
                                  number: true              
                                }                                
                              },
                              messages: {
                                numer: {
                                  required: "Pole jest wymagane."
                                },
                                nazwa: {
                                  required: "Pole jest wymagane."
                                },
                                data_oferty: {
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
                          <label class="required" for="numer">Numer oferty:</label>
                          <input type="text" name="numer" size="20" value="<?php echo $info['offers_nr']; ?>" id="numer" />
                        </p>   

                        <p>
                          <label class="required" for="nazwa">Tytuł oferty:</label>
                          <input type="text" name="nazwa" size="90" value="<?php echo $info['offers_name']; ?>" id="nazwa" />
                        </p>

                        <p>
                          <label class="required" for="data_oferty">Data utworzenia oferty:</label>
                          <input type="text" name="data_oferty" size="25" value="<?php echo date('d-m-Y H:i',FunkcjeWlasnePHP::my_strtotime($info['offers_date'])); ?>" id="data_oferty" class="datepicker" />
                        </p>        

                        <p>
                          <label>Czy każdy produkt ma być na osobnej stronie dokumentu PDF ?</label>           
                          <input type="radio" name="produkt_strona" value="1" id="opcja_1" <?php echo (($info['offers_products_page'] == '1') ? 'checked="checked"' : ''); ?> /> <label class="OpisFor" for="opcja_1">tak</label>
                          <input type="radio" name="produkt_strona" value="0" id="opcja_0" <?php echo (($info['offers_products_page'] == '0') ? 'checked="checked"' : ''); ?> /> <label class="OpisFor" for="opcja_0">nie</label>
                        </p>  
                        
                        <p>
                          <label>Czy ma być widoczne podsumowanie oferty (wartość brutto i netto) ?</label>           
                          <input type="radio" name="podsumowanie" value="1" id="podsumowanie_tak" <?php echo (($info['offers_summary'] == '1') ? 'checked="checked"' : ''); ?> /> <label class="OpisFor" for="podsumowanie_tak">tak</label>
                          <input type="radio" name="podsumowanie" value="0" id="podsumowanie_nie" <?php echo (($info['offers_summary'] == '0') ? 'checked="checked"' : ''); ?> /> <label class="OpisFor" for="podsumowanie_nie">nie<em class="TipIkona"><b>Dane będą widoczne tylko jeżeli wszystkie produkty w ofercie będą miały uzupełnioną cenę brutto lub netto oraz podaną ilość produktów</b></em></label>
                        </p>                         

                        <p>
                          <label class="required" for="rozmiar_zdjec">Szerokość / wysokość zdjęć w pikselach:</label>
                          <input type="text" name="rozmiar_zdjec" size="10" value="<?php echo $info['offers_image_size']; ?>" id="rozmiar_zdjec" />
                        </p>                         
                        
                        <p>
                          <label for="adresat">Adresat oferty:</label>
                          <textarea cols="80" rows="5" name="adresat" id="adresat"><?php echo $info['offers_customer']; ?></textarea>
                        </p>                         
                        
                        <p>
                          <label>Komentarz do oferty:</label>
                          <textarea cols="50" rows="20" id="komentarz" name="komentarz"><?php echo $info['offers_comments']; ?></textarea>
                        </p>        

                        <script>    
                        ckedit('komentarz','99%','500'); 
                        </script>                         

                    </div>

                </div>
                
                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Zapisz dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('oferty','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>');">Powrót</button>     
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