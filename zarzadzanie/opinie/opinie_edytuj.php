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
                      array('customers_email',$filtr->process($_POST['email'])),
                      array('orders_id',(int)$_POST['nr_zamowienia']),
                      array('handling_rating',(int)$_POST['jakosc']),
                      array('lead_time_rating',(int)$_POST['czas']),
                      array('price_rating',(int)$_POST['ceny']),
                      array('quality_products_rating',(int)$_POST['produkty']),
                      array('recommending',(int)$_POST['polecanie']),
                      array('products_approved',(int)$_POST['produkty_zgoda']),
                      array('comments',$filtr->process($_POST['komentarz'])),
                      array('date_added',((empty($_POST['data_dodania'])) ? 'now()' : date('Y-m-d H:i', FunkcjeWlasnePHP::my_strtotime($filtr->process($_POST['data_dodania']))))));
                      
        // srednia ocena
        $Ocena = (((int)$_POST['jakosc'] + (int)$_POST['czas'] + (int)$_POST['ceny'] + (int)$_POST['produkty']) * 5) / 20;
        $pola[] = array('average_rating', $Ocena);
        //	
        $sql = $db->update_query('reviews_shop', $pola, 'reviews_shop_id = ' . (int)$id_edytowanej_pozycji);
        unset($pola);        

        Funkcje::PrzekierowanieURL('opinie.php?id_poz='.(int)$id_edytowanej_pozycji);
    }   

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>

    <div id="naglowek_cont">Edycja pozycji</div>
    <div id="cont">

          <form action="opinie/opinie_edytuj.php" method="post" id="opinieForm" class="cmxform"> 
          
          <div class="poleForm">
            <div class="naglowek">Edycja danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select distinct * from reviews_shop where reviews_shop_id = '".(int)$_GET['id_poz']."'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {

                $info = $sql->fetch_assoc();  
                ?>            
            
                <div class="pozycja_edytowana">    
                
                    <input type="hidden" name="akcja" value="zapisz" />

                    <input type="hidden" name="id" value="<?php echo $info['reviews_shop_id']; ?>" />
                    
                    <div class="info_content">

                    <script>
                    $(document).ready(function() {
                    
                        $("#opinieForm").validate({
                          rules: {
                            wystawiajacy: {
                              required: true
                            },
                            email: {
                              required: true,
                              email: true
                            }                     
                          },
                          messages: {
                            wystawiajacy: {
                              required: "Pole jest wymagane."
                            },
                            email: {
                              required: "Pole jest wymagane.",
                              email: "Wpisano niepoprawny adres e-mail."
                            }                       
                          }
                        });               

                        $('input.datepicker').Zebra_DatePicker({
                           format: 'd-m-Y',
                           inside: false,
                           readonly_element: true
                        });                 
                    
                    });
                    </script>  
                    
                    <p>
                        <label class="required" for="wystawiajacy">Nazwa opiniującego:</label>
                        <input type="text" name="wystawiajacy" id="wystawiajacy" value="<?php echo $info['customers_name']; ?>" size="40" />                                        
                    </p>  

                    <p>
                        <label class="required" for="email">Adres email:</label>
                        <input type="text" name="email" id="email" value="<?php echo $info['customers_email']; ?>" size="40" />                                        
                    </p>  

                    <p>
                        <label for="nr_zamowienia">Nr zamówienia:</label>
                        <input type="text" name="nr_zamowienia" id="nr_zamowienia" value="<?php echo $info['orders_id']; ?>" size="10" />                                        
                    </p>                       
                
                    <p>
                        <label for="data_dodania">Data dodania:</label>
                        <input type="text" name="data_dodania" id="data_dodania" value="<?php echo ((Funkcje::czyNiePuste($info['date_added'])) ? date('d-m-Y H:i',FunkcjeWlasnePHP::my_strtotime($info['date_added'])) : ''); ?>" size="20" class="datepicker" />                                        
                    </p>

                    <p>
                      <label>Czy klient poleca zakupy w sklepie ?</label>
                      <input type="radio" name="polecanie" value="1" id="polecanie_tak" <?php echo (($info['recommending'] == '1' || empty($info['recommending'])) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="polecanie_tak">tak</label>
                      <input type="radio" name="polecanie" value="0" id="polecanie_nie" <?php echo (($info['recommending'] == '0') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="polecanie_nie">nie</label>
                    </p>  

                    <p>
                      <label>Czy klient wyraża zgadę na udostępnianie informacji jakie produkty zakupił ?</label>
                      <input type="radio" name="produkty_zgoda" value="1" id="produkty_tak" <?php echo (($info['products_approved'] == '1' || empty($info['products_approved'])) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="produkty_tak">tak</label>
                      <input type="radio" name="produkty_zgoda" value="0" id="produkty_nie" <?php echo (($info['products_approved'] == '0') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="produkty_nie">nie</label>
                    </p>                       

                    <table>
                        <tr>
                            <td class="OcenaTabela"><label>Oceny:</label></td>
                            <td>
                              <table class="TabelaEdycja">
                                  <tr>
                                      <td>
                                        <strong>Jakość obsługi:</strong>
                                        <input type="radio" value="1" name="jakosc" id="jakosc_1" <?php echo (($info['handling_rating'] == '1') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="jakosc_1"><img alt="Ocena 1/5" src="obrazki/recenzje/star_1.png" /></label><br />
                                        <input type="radio" value="2" name="jakosc" id="jakosc_2" <?php echo (($info['handling_rating'] == '2') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="jakosc_2"><img alt="Ocena 2/5" src="obrazki/recenzje/star_2.png" /></label><br />
                                        <input type="radio" value="3" name="jakosc" id="jakosc_3" <?php echo (($info['handling_rating'] == '3') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="jakosc_3"><img alt="Ocena 3/5" src="obrazki/recenzje/star_3.png" /></label><br />
                                        <input type="radio" value="4" name="jakosc" id="jakosc_4" <?php echo (($info['handling_rating'] == '4') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="jakosc_4"><img alt="Ocena 4/5" src="obrazki/recenzje/star_4.png" /></label><br />
                                        <input type="radio" value="5" name="jakosc" id="jakosc_5" <?php echo (($info['handling_rating'] == '5') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="jakosc_5"><img alt="Ocena 5/5" src="obrazki/recenzje/star_5.png" /></label>
                                      </td>

                                      <td>
                                        <strong>Czas realizacji:</strong>
                                        <input type="radio" value="1" name="czas" id="czas_1" <?php echo (($info['lead_time_rating'] == '1') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="czas_1"><img alt="Ocena 1/5" src="obrazki/recenzje/star_1.png" /></label><br />
                                        <input type="radio" value="2" name="czas" id="czas_2" <?php echo (($info['lead_time_rating'] == '2') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="czas_2"><img alt="Ocena 2/5" src="obrazki/recenzje/star_2.png" /></label><br />
                                        <input type="radio" value="3" name="czas" id="czas_3" <?php echo (($info['lead_time_rating'] == '3') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="czas_3"><img alt="Ocena 3/5" src="obrazki/recenzje/star_3.png" /></label><br />
                                        <input type="radio" value="4" name="czas" id="czas_4" <?php echo (($info['lead_time_rating'] == '4') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="czas_4"><img alt="Ocena 4/5" src="obrazki/recenzje/star_4.png" /></label><br />
                                        <input type="radio" value="5" name="czas" id="czas_5" <?php echo (($info['lead_time_rating'] == '5') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="czas_5"><img alt="Ocena 5/5" src="obrazki/recenzje/star_5.png" /></label>
                                      </td>

                                      <td>
                                        <strong>Ceny produktów:</strong>
                                        <input type="radio" value="1" name="ceny" id="ceny_1" <?php echo (($info['price_rating'] == '1') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="ceny_1"><img alt="Ocena 1/5" src="obrazki/recenzje/star_1.png" /></label><br />
                                        <input type="radio" value="2" name="ceny" id="ceny_2" <?php echo (($info['price_rating'] == '2') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="ceny_2"><img alt="Ocena 2/5" src="obrazki/recenzje/star_2.png" /></label><br />
                                        <input type="radio" value="3" name="ceny" id="ceny_3" <?php echo (($info['price_rating'] == '3') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="ceny_3"><img alt="Ocena 3/5" src="obrazki/recenzje/star_3.png" /></label><br />
                                        <input type="radio" value="4" name="ceny" id="ceny_4" <?php echo (($info['price_rating'] == '4') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="ceny_4"><img alt="Ocena 4/5" src="obrazki/recenzje/star_4.png" /></label><br />
                                        <input type="radio" value="5" name="ceny" id="ceny_5" <?php echo (($info['price_rating'] == '5') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="ceny_5"><img alt="Ocena 5/5" src="obrazki/recenzje/star_5.png" /></label>
                                      </td>

                                      <td>
                                        <strong>Jakość produktów:</strong>
                                        <input type="radio" value="1" name="produkty" id="produkty_1" <?php echo (($info['quality_products_rating'] == '1') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="produkty_1"><img alt="Ocena 1/5" src="obrazki/recenzje/star_1.png" /></label><br />
                                        <input type="radio" value="2" name="produkty" id="produkty_2" <?php echo (($info['quality_products_rating'] == '2') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="produkty_2"><img alt="Ocena 2/5" src="obrazki/recenzje/star_2.png" /></label><br />
                                        <input type="radio" value="3" name="produkty" id="produkty_3" <?php echo (($info['quality_products_rating'] == '3') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="produkty_3"><img alt="Ocena 3/5" src="obrazki/recenzje/star_3.png" /></label><br />
                                        <input type="radio" value="4" name="produkty" id="produkty_4" <?php echo (($info['quality_products_rating'] == '4') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="produkty_4"><img alt="Ocena 4/5" src="obrazki/recenzje/star_4.png" /></label><br />
                                        <input type="radio" value="5" name="produkty" id="produkty_5" <?php echo (($info['quality_products_rating'] == '5') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="produkty_5"><img alt="Ocena 5/5" src="obrazki/recenzje/star_5.png" /></label>
                                      </td>
                                  </tr>
                              </table>  
                            </td>
                        </tr>
                    </table>    

                    <p>
                        <label for="komentarz">Komentarz:</label>
                        <textarea name="komentarz" id="komentarz" rows="10" cols="50"><?php echo $info['comments']; ?></textarea><em class="TipIkona"><b>Treść opinii - bez tagów HTML</b></em>
                    </p>
 
                    </div>
                    
                </div>
                
                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Zapisz dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('opinie','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>');">Powrót</button>     
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