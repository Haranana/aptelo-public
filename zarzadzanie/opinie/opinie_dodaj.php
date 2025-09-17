<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        $pola = array(array('customers_name',$filtr->process($_POST['wystawiajacy'])),
                      array('customers_email',$filtr->process($_POST['email'])),
                      array('orders_id',(int)$_POST['nr_zamowienia']),
                      array('handling_rating',(int)$_POST['jakosc']),
                      array('lead_time_rating',(int)$_POST['czas']),
                      array('price_rating',(int)$_POST['ceny']),
                      array('quality_products_rating',(int)$_POST['produkty']),
                      array('recommending',(int)$_POST['polecanie']),
                      array('comments',$filtr->process($_POST['komentarz'])),
                      array('date_added',((empty($_POST['data_dodania'])) ? 'now()' : date('Y-m-d H:i', FunkcjeWlasnePHP::my_strtotime($filtr->process($_POST['data_dodania']))))),
                      array('approved','1'));

        // srednia ocena
        $Ocena = ((((int)$_POST['jakosc'] + (int)$_POST['czas'] + (int)$_POST['ceny'] + (int)$_POST['produkty']) * 5) / 20);
        $pola[] = array('average_rating', (float)$Ocena);
        //	        
        $sql = $db->insert_query('reviews_shop', $pola);
        $id_dodanej_pozycji = $db->last_id_query();
        unset($pola);        

        Funkcje::PrzekierowanieURL('opinie.php?id_poz='.$id_dodanej_pozycji);
    }   

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>

    <div id="naglowek_cont">Dodawanie pozycji</div>
    <div id="cont">

          <form action="opinie/opinie_dodaj.php" method="post" id="opinieForm" class="cmxform"> 
          
          <div class="poleForm">
            <div class="naglowek">Dodawanie pozycji</div>
            
            <div class="pozycja_edytowana">    
            
                <input type="hidden" name="akcja" value="zapisz" />

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
                       readonly_element: true,
                       show_clear_date: false
                    });                 
                
                });
                </script>  
                
                <p>
                    <label class="required" for="wystawiajacy">Nazwa opiniującego:</label>
                    <input type="text" name="wystawiajacy" id="wystawiajacy" value="" size="40" />                                        
                </p>  

                <p>
                    <label class="required" for="email">Adres email:</label>
                    <input type="text" name="email" id="email" value="" size="40" />                                        
                </p>  

                <p>
                    <label for="nr_zamowienia">Nr zamówienia:</label>
                    <input type="text" name="nr_zamowienia" id="nr_zamowienia" value="" size="10" />                                        
                </p>                       
            
                <p>
                    <label for="data_dodania">Data dodania:</label>
                    <input type="text" name="data_dodania" id="data_dodania" value="<?php echo date('d-m-Y H:i',time()); ?>" size="20" class="datepicker" />                                        
                </p>

                <p>
                  <label>Czy klient poleca zakupy w sklepie ?</label>
                  <input type="radio" name="polecanie" value="1" id="polecanie_tak" checked="checked" /><label class="OpisFor" for="polecanie_tak">tak</label>
                  <input type="radio" name="polecanie" value="0" id="polecanie_nie" /><label class="OpisFor" for="polecanie_nie">nie</label>
                </p>                    

                <table>
                    <tr>
                        <td class="OcenaTabela"><label>Oceny:</label></td>
                        <td>
                          <table class="TabelaEdycja">
                              <tr>
                                  <td>
                                    <strong>Jakość obsługi:</strong>
                                    <input type="radio" value="1" name="jakosc" id="jakosc_1" checked="checked" /><label class="OpisFor" for="jakosc_1"><img alt="Ocena 1/5" src="obrazki/recenzje/star_1.png" /></label><br />
                                    <input type="radio" value="2" name="jakosc" id="jakosc_2" /><label class="OpisFor" for="jakosc_2"><img alt="Ocena 2/5" src="obrazki/recenzje/star_2.png" /></label><br />
                                    <input type="radio" value="3" name="jakosc" id="jakosc_3" /><label class="OpisFor" for="jakosc_3"><img alt="Ocena 3/5" src="obrazki/recenzje/star_3.png" /></label><br />
                                    <input type="radio" value="4" name="jakosc" id="jakosc_4" /><label class="OpisFor" for="jakosc_4"><img alt="Ocena 4/5" src="obrazki/recenzje/star_4.png" /></label><br />
                                    <input type="radio" value="5" name="jakosc" id="jakosc_5" /><label class="OpisFor" for="jakosc_5"><img alt="Ocena 5/5" src="obrazki/recenzje/star_5.png" /></label>
                                  </td>

                                  <td>
                                    <strong>Czas realizacji:</strong>
                                    <input type="radio" value="1" name="czas" id="czas_1" checked="checked" /><label class="OpisFor" for="czas_1"><img alt="Ocena 1/5" src="obrazki/recenzje/star_1.png" /></label><br />
                                    <input type="radio" value="2" name="czas" id="czas_2" /><label class="OpisFor" for="czas_2"><img alt="Ocena 2/5" src="obrazki/recenzje/star_2.png" /></label><br />
                                    <input type="radio" value="3" name="czas" id="czas_3" /><label class="OpisFor" for="czas_3"><img alt="Ocena 3/5" src="obrazki/recenzje/star_3.png" /></label><br />
                                    <input type="radio" value="4" name="czas" id="czas_4" /><label class="OpisFor" for="czas_4"><img alt="Ocena 4/5" src="obrazki/recenzje/star_4.png" /></label><br />
                                    <input type="radio" value="5" name="czas" id="czas_5" /><label class="OpisFor" for="czas_5"><img alt="Ocena 5/5" src="obrazki/recenzje/star_5.png" /></label>
                                  </td>

                                  <td>
                                    <strong>Ceny produktów:</strong>
                                    <input type="radio" value="1" name="ceny" id="ceny_1" checked="checked" /><label class="OpisFor" for="ceny_1"><img alt="Ocena 1/5" src="obrazki/recenzje/star_1.png" /></label><br />
                                    <input type="radio" value="2" name="ceny" id="ceny_2" /><label class="OpisFor" for="ceny_2"><img alt="Ocena 2/5" src="obrazki/recenzje/star_2.png" /></label><br />
                                    <input type="radio" value="3" name="ceny" id="ceny_3" /><label class="OpisFor" for="ceny_3"><img alt="Ocena 3/5" src="obrazki/recenzje/star_3.png" /></label><br />
                                    <input type="radio" value="4" name="ceny" id="ceny_4" /><label class="OpisFor" for="ceny_4"><img alt="Ocena 4/5" src="obrazki/recenzje/star_4.png" /></label><br />
                                    <input type="radio" value="5" name="ceny" id="ceny_5" /><label class="OpisFor" for="ceny_5"><img alt="Ocena 5/5" src="obrazki/recenzje/star_5.png" /></label>
                                  </td>

                                  <td>
                                    <strong>Jakość produktów:</strong>
                                    <input type="radio" value="1" name="produkty" id="produkty_1" checked="checked" /><label class="OpisFor" for="produkty_1"><img alt="Ocena 1/5" src="obrazki/recenzje/star_1.png" /></label><br />
                                    <input type="radio" value="2" name="produkty" id="produkty_2" /><label class="OpisFor" for="produkty_2"><img alt="Ocena 2/5" src="obrazki/recenzje/star_2.png" /></label><br />
                                    <input type="radio" value="3" name="produkty" id="produkty_3" /><label class="OpisFor" for="produkty_3"><img alt="Ocena 3/5" src="obrazki/recenzje/star_3.png" /></label><br />
                                    <input type="radio" value="4" name="produkty" id="produkty_4" /><label class="OpisFor" for="produkty_4"><img alt="Ocena 4/5" src="obrazki/recenzje/star_4.png" /></label><br />
                                    <input type="radio" value="5" name="produkty" id="produkty_5" /><label class="OpisFor" for="produkty_5"><img alt="Ocena 5/5" src="obrazki/recenzje/star_5.png" /></label>
                                  </td>
                              </tr>
                          </table>  
                        </td>
                    </tr>
                </table>    

                <p>
                    <label for="komentarz">Komentarz:</label>
                    <textarea name="komentarz" id="komentarz" rows="10" cols="50"></textarea><em class="TipIkona"><b>Treść opinii - bez tagów HTML</b></em>
                </p>

                </div>
                
            </div>
            
            <div class="przyciski_dolne">
              <input type="submit" class="przyciskNon" value="Zapisz dane" />
              <button type="button" class="przyciskNon" onclick="cofnij('opinie','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>');">Powrót</button>     
            </div>

          </div>

          </form>

    </div>
    
    <?php
    include('stopka.inc.php');    
    
} ?>