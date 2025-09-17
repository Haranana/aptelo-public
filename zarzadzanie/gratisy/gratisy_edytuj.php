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
        $pola = array(
                array('gift_value_of',(float)$_POST['input_od']),
                array('gift_value_for',(float)$_POST['input_do']),
                array('gift_value_exclusion',(int)$_POST['suma_warunkow']),
                array('gift_min_quantity',(int)$_POST['ilosc']),
                array('gift_only_one',(int)$_POST['jeden']),
                array('customers_group_id',((isset($_POST['grupa_klientow'])) ? implode(',', (array)$_POST['grupa_klientow']) : '')));
                
        // jezeli gratis bedzie z cena
        if ((int)$_POST['tryb_cena'] == 1) {
            //  
            $pola[] = array('gift_price',(float)$_POST['cena']);
            //
          } else {
            //
            $pola[] = array('gift_price',0);
            //
        }  
        //	
        $sql = $db->update_query('products_gift', $pola, 'id_gift = ' . $id_edytowanej_pozycji);
        unset($pola);        
        
        Funkcje::PrzekierowanieURL('gratisy.php?id_poz='.$id_edytowanej_pozycji);
    }   

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>

    <div id="naglowek_cont">Edycja pozycji</div>
    <div id="cont">

          <form action="gratisy/gratisy_edytuj.php" method="post" id="gratisyForm" class="cmxform"> 
          
          <div class="poleForm">
            <div class="naglowek">Edycja danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select distinct * from products_gift where id_gift = '".(int)$_GET['id_poz']."'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {

                $info = $sql->fetch_assoc();  
                ?>            
            
                <div class="pozycja_edytowana">    
                
                    <div class="info_content">
                
                    <input type="hidden" name="akcja" value="zapisz" />

                    <input type="hidden" name="id" value="<?php echo $info['id_gift']; ?>" />

                    <script>
                    $(document).ready(function() {
                    $("#gratisyForm").validate({
                      rules: {          
                        cena: {
                          required: function(element) {
                            if ($("#kwota_gratis").css('display') == 'block') {
                                return true;
                              } else {
                                return false;
                            }
                          },
                          range: [0.01, 1000000],
                          number: true                  
                        },  
                        ilosc: {
                          range: [0, 100000],
                          number: true
                        },                        
                        input_od: {
                          required: true,
                          range: [1, 1000000],
                          number: true
                        },  
                        input_do: {
                          required: true,
                          range: [1, 1000000],
                          number: true
                        }                
                      },
                      messages: {           
                        cena: {
                          required: "Pole jest wymagane.",
                        },
                        ilosc: {
                          range: "Wartość musi być wieksza od 0."
                        },                        
                        input_od: {
                          required: "Pole jest wymagane.",
                        },
                        input_do: {
                          required: "Pole jest wymagane.",
                        }                
                      }
                    });
                    });
                    
                    function anuluj_minus(elem) {
                        if ($(elem).val() < 0) {
                            $(elem).val( $(elem).val() * -1 );
                        }
                    }                        
                    </script>  
                    
                    <p>
                      <label for="nazwa">Produkt:</label>
                      <?php
                      //
                      $produkt_nazwa = $db->open_query("select distinct products_name from products_description where products_id = '".(int)$info['gift_products_id']."' and language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'");
                      $nazwa = $produkt_nazwa->fetch_assoc();
                      //
                      $db->close_query($produkt_nazwa);    
                      unset($produkt_nazwa);
                      //
                      ?>
                      <input type="text" name="nazwa" id="nazwa" value="<?php echo $nazwa['products_name']; ?>" size="83" disabled="disabled" />
                    </p> 

                    <p>
                      <label>Czy gratis będzie dodawany za darmo czy będzie miał cenę ?</label>
                      <input type="radio" value="1" name="tryb_cena" id="tryb_cena" onclick="$('#kwota_gratis').slideDown()" <?php echo (($info['gift_price'] > 0) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="tryb_cena">będzie miał cenę<em class="TipIkona"><b>Umożliwia przypisanie gratisowi ceny - np 1 zł</b></em></label>
                      <input type="radio" value="2" name="tryb_cena" id="tryb_gratis" onclick="$('#kwota_gratis').slideUp()" <?php echo (($info['gift_price'] == 0) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="tryb_gratis">będzie darmowy<em class="TipIkona"><b>Gratis będzie dodawany do zamówienia za darmo</b></em></label>           
                    </p>
                    
                    <div id="kwota_gratis" <?php echo (($info['gift_price'] > 0) ? '' : 'style="display:none"'); ?>>
                    
                        <p>
                            <label class="required" id="cena">Cena brutto:</label>           
                            <input type="text" name="cena" id="cena" size="15" value="<?php echo (($info['gift_price'] == 0) ? 1 : $info['gift_price']); ?>" /><em class="TipIkona"><b>Wartość musi być większa od 0.01</b></em>
                        </p>  
                        
                    </div>                    

                    <div class="RamkaWarunki">
                    
                        <b>Dodatkowe warunki wyświetlania gratisu</b>

                        <p>
                          <label>Czy klient będzie mógł wybrać tylko ten gratis ?</label>
                          <input type="radio" value="1" name="jeden" id="jeden_tak" <?php echo (($info['gift_only_one'] == 1) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="jeden_tak">tak<em class="TipIkona"><b>Jeżeli klient wybierze ten gratis inne gratisy nie będą dostępne</b></em></label>
                          <input type="radio" value="0" name="jeden" id="jeden_nie" <?php echo (($info['gift_only_one'] == 0) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="jeden_nie">nie<em class="TipIkona"><b>Wybór tego gratisu nie wpływa na wybór innych gratisów</b></em></label>           
                        </p>                        

                        <table style="margin:10px 10px 10px 0px">
                            <tr>
                                <td><label>Dostępny dla grupy klientów:</label></td>
                                <td>
                                    <?php                        
                                    $TablicaGrupKlientow = Klienci::ListaGrupKlientow(true, 'Klienci bez rejestracji konta' );
                                    foreach ( $TablicaGrupKlientow as $GrupaKlienta ) {
                                        echo '<input type="checkbox" value="' . $GrupaKlienta['id'] . '" name="grupa_klientow[]" id="grupa_klientow_' . $GrupaKlienta['id'] . '" ' . ((in_array((string)$GrupaKlienta['id'], explode(',', (string)$info['customers_group_id']))) ? 'checked="checked" ' : '') . '/> <label class="OpisFor" for="grupa_klientow_' . $GrupaKlienta['id'] . '">' . $GrupaKlienta['text'] . '</label><br />';
                                    }               
                                    unset($TablicaGrupKlientow);
                                    ?>
                                </td>
                            </tr>
                        </table> 
                        
                        <div class="ostrzezenie odlegloscRwd" style="margin-bottom:10px">Jeżeli nie zostanie wybrana żadna grupa klientów to gratis będzie dostępny dla wszystkich klientów.</div>

                        <p>
                            <label class="required" for="input_od">Dostępny od kwoty:</label>           
                            <input onchange="anuluj_minus(this)" type="text" name="input_od" id="input_od" size="15" value="<?php echo $info['gift_value_of']; ?>" /><em class="TipIkona"><b>Poziom kwotowy od jakiego będzie przyznawany gratis</b></em>
                        </p>
                        
                        <p>
                            <label class="required" for="input_do">Dostępny do kwoty:</label>           
                            <input onchange="anuluj_minus(this)" type="text" name="input_do" id="input_do" size="15" value="<?php echo $info['gift_value_for']; ?>" /><em class="TipIkona"><b>Poziom kwotowy do jakiego będzie przyznawany gratis</b></em>
                        </p>  
                        
                        <p>
                          <label>W/w kwota obliczana dla:</label>
                          <input type="radio" value="0" name="suma_warunkow" id="suma_wszystkie" <?php echo (((int)$info['gift_value_exclusion'] == 0) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="suma_wszystkie">suma wszystkich produktów koszyka</label>
                          <input type="radio" value="1" name="suma_warunkow" id="suma_wybrane" <?php echo (((int)$info['gift_value_exclusion'] == 1) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="suma_wybrane">suma produktów wg dodatkowych warunków gratisu</label>
                        </p>      

                        <span class="maleInfo odlegloscRwd">Suma wg warunków - jeżeli są wybrane dodatkowe warunki dostępności gratisu (kategoria, producent, produkt) to są sumowane produkty tylko wg wybranych warunków.</span>

                        <p>
                          <label for="ilosc">Minimalna ilość produktów:</label>
                          <input class="kropkaPusta" type="text" name="ilosc" id="ilosc" value="<?php echo (($info['gift_min_quantity'] == 0) ? '' : $info['gift_min_quantity']); ?>" size="3" /><em class="TipIkona"><b>Ilość produktów w koszyku od jakiej będzie wyświetlany gratis</b></em>
                        </p>                           
                        
                        <span class="maleInfo odlegloscRwd">Jeżeli są wybrane dodatkowe warunki dostępności gratisu (kategoria, producent, produkt) to ilość produktów będzie obliczana dla n/w warunków.</span>
                        
                        <p>
                          <label>Dostępny tylko dla:</label>
                          <?php
                          $warunki = '<strong>brak</strong>';
                          if ( $info['gift_exclusion'] == 'kategorie' && $info['gift_exclusion_id'] != '' ) {
                               $warunki = '<strong>wybranych kategorii</strong>';
                               //
                               $warunki .= '<span id="ListaWarunkow">';
                               //
                               $kategoria_nazwa = $db->open_query("select distinct categories_id, categories_name from categories_description where categories_id in (".$info['gift_exclusion_id'].") and language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'");
                               while ($nazwa = $kategoria_nazwa->fetch_assoc()) {
                                      $warunki .= '&raquo ' . $nazwa['categories_name'] . '<br />';                               
                               }
                               $db->close_query($kategoria_nazwa);    
                               unset($kategoria_nazwa, $nazwa);                               
                               //
                               $warunki .= '</span>';
                               //
                          }
                          if ( $info['gift_exclusion'] == 'producenci' && $info['gift_exclusion_id'] != '' ) {
                               $warunki = '<strong>wybranych producentów</strong>';
                               //
                               $warunki .= '<span id="ListaWarunkow">';
                               //
                               $producent_nazwa = $db->open_query("select distinct manufacturers_name from manufacturers where manufacturers_id in (".$info['gift_exclusion_id'].")");
                               while ($nazwa = $producent_nazwa->fetch_assoc()) {
                                      $warunki .= '&raquo ' . $nazwa['manufacturers_name'] . '<br />';                               
                               }
                               $db->close_query($producent_nazwa);    
                               unset($producent_nazwa, $nazwa);                               
                               //
                               $warunki .= '</span>';
                               //                               
                          }  
                          if ( $info['gift_exclusion'] == 'kategorie_producenci' && $info['gift_exclusion_id'] != '' ) {
                               //
                               $podzial = explode('|', (string)$info['gift_exclusion_id']);
                               //
                               $warunki = '<strong>wybranych kategorii</strong>';
                               //
                               $warunki .= '<span id="ListaWarunkow">';
                               //
                               $kategoria_nazwa = $db->open_query("select distinct categories_id, categories_name from categories_description where categories_id in (".$podzial[0].") and language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'");
                               while ($nazwa = $kategoria_nazwa->fetch_assoc()) {
                                      $warunki .= '&raquo ' . $nazwa['categories_name'] . '<br />';                               
                               }
                               $db->close_query($kategoria_nazwa);    
                               unset($kategoria_nazwa, $nazwa);                               
                               //
                               $warunki .= '</span>';
                               //
                               $warunki .= '<label>Dostępny tylko dla:</label><strong>oraz wybranych producentów</strong>';
                               //
                               $warunki .= '<span id="ListaWarunkow">';
                               //
                               $producent_nazwa = $db->open_query("select distinct manufacturers_name from manufacturers where manufacturers_id in (".$podzial[1].")");
                               while ($nazwa = $producent_nazwa->fetch_assoc()) {
                                      $warunki .= '&raquo ' . $nazwa['manufacturers_name'] . '<br />';                               
                               }
                               $db->close_query($producent_nazwa);    
                               unset($producent_nazwa, $nazwa);                               
                               //
                               $warunki .= '</span>';
                               //                               
                          }                            
                          if ( $info['gift_exclusion'] == 'produkty' && $info['gift_exclusion_id'] != '' ) {
                               $warunki = '<strong>wybranych produktów</strong>';
                               //
                               $warunki .= '<span id="ListaWarunkow">';
                               //
                               $produkt_nazwa = $db->open_query("select distinct products_id, products_name from products_description where products_id in (".$info['gift_exclusion_id'].") and language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'");
                               while ($nazwa = $produkt_nazwa->fetch_assoc()) {
                                      $warunki .= '&raquo ' . $nazwa['products_name'] . '<br />';                               
                               }
                               $db->close_query($produkt_nazwa);    
                               unset($produkt_nazwa, $nazwa);                               
                               //
                               $warunki .= '</span>';
                               //                               
                          }    
                          echo $warunki;
                          unset($warunki);
                          ?>
                        </p>                          

                    </div>
                    
                    </div>

                </div>
                
                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Zapisz dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('gratisy','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>');">Powrót</button>     
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