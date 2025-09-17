<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        // czysci najpierw rekord
        $pola = array(
                array('templates_id','1'),
                array('language_id','1'),
                array('title',''),
                array('content',''),
                array('destination',''),
                array('order_date_start','0000-00-00'),
                array('order_date_end','0000-00-00'),
                array('order_status','1'),
                array('order_min','0.00'),
                array('order_max','0.00'),        
                array('customers_newsletter_group','')
        );    
        $db->update_query('newsletters' , $pola, " newsletters_id = '".(int)$_POST["id"]."'");
        unset($pola);
        
        //
        $pola = array(
                array('templates_id',(int)$_POST['szablon']),
                array('language_id',(int)$_POST['jezyk']),
                array('title',$filtr->process($_POST['temat'])),
                array('content',$filtr->process($_POST['wiadomosc'])),
                array('destination',$filtr->process($_POST['odbiorcy']))
        );
        
        if ((int)$_POST['odbiorcy'] == 1 || (int)$_POST['odbiorcy'] == 2 || (int)$_POST['odbiorcy'] == 6) {
            //
            if (!empty($_POST['data_od'])) {
                $pola[] = array('order_date_start',date('Y-m-d', FunkcjeWlasnePHP::my_strtotime($filtr->process($_POST['data_od']))));
            }
            if (!empty($_POST['data_do'])) {
                $pola[] = array('order_date_end',date('Y-m-d', FunkcjeWlasnePHP::my_strtotime($filtr->process($_POST['data_do']))));
            }
            $pola[] = array('order_status',$filtr->process($_POST['status']));
            $pola[] = array('order_min',(float)$_POST['wartosc_od']);
            $pola[] = array('order_max',(float)$_POST['wartosc_do']);
            //
        }
        
        if ((int)$_POST['odbiorcy'] == 7 || (int)$_POST['odbiorcy'] == 8 || (int)$_POST['odbiorcy'] == 9) {
            //
            if (!empty($_POST['data_porzucenia_od'])) {
                $pola[] = array('basket_date_start',date('Y-m-d', FunkcjeWlasnePHP::my_strtotime($filtr->process($_POST['data_porzucenia_od']))));
            }
            if (!empty($_POST['data_porzucenia_do'])) {
                $pola[] = array('basket_date_end',date('Y-m-d', FunkcjeWlasnePHP::my_strtotime($filtr->process($_POST['data_porzucenia_do']))));
            }
            //
        }          
        
        if ((int)$_POST['odbiorcy'] == 2) {
        
            if ( isset($_POST['newsletter_grupa']) ) {
                 $grupyNewslettera = ',' . implode(',', (array)$filtr->process($_POST['newsletter_grupa'])) . ',';
            }        
            $pola[] = array('customers_newsletter_group',$grupyNewslettera);
            unset($grupyNewslettera);
            
        }
        
        if ((int)$_POST['odbiorcy'] == 3) {
        
            if (!empty($_POST['data_aktywacji'])) {
                $pola[] = array('activation',date('Y-m-d', FunkcjeWlasnePHP::my_strtotime($filtr->process($_POST['data_aktywacji']))));
            }
            
        }          
        
        // jezeli wybrano odbiorcow dla okreslonej grupy klientow
        if ((int)$_POST['odbiorcy'] == 6) {
            //
            $pola[] = array('customers_group_id',$filtr->process($_POST['grupa_klientow']));
            //
        }        
        
        // jezeli wybrano odbiorcow dla okreslonej grupy klientow
        if ((int)$_POST['odbiorcy'] == 1 || (int)$_POST['odbiorcy'] == 2) {
            //
            $pola[] = array('customers_group_id',(int)$_POST['grupa_klientow_rejestracja']);
            //
        }           
        
        $db->update_query('newsletters' , $pola, " newsletters_id = '".(int)$_POST["id"]."'");
        unset($pola);         
        
        //
        Funkcje::PrzekierowanieURL('newsletter.php?id_poz='.(int)$_POST["id"]);
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Edycja pozycji</div>
    <div id="cont">
          
          <script>
          $(document).ready(function() {
            $("#newsForm").validate({
              rules: {
                temat: {
                  required: true
                }            
              },
              messages: {
                temat: {
                  required: "Pole jest wymagane."
                }      
              }
            });

            ckedit('wiadomosc','99%','500');
                
            $('input.datepicker').Zebra_DatePicker({
               format: 'd-m-Y',
               inside: false,
               readonly_element: true
            });              
            
          });
          
          function zmien_divy(id) {
            if (parseInt(id) == 1 || parseInt(id) == 2) {
                $('#warunki').slideDown();
                $('#grupa_klientow').slideUp();
                $('#grupa_klientow_rejestracja').slideDown();
                $('#warunki_box').slideUp();
                $('#warunki_porzucenia').slideUp();
            }
            if (parseInt(id) == 6) {
                $('#warunki').slideDown();
                $('#grupa_klientow').slideDown();
                $('#grupa_klientow_rejestracja').slideUp();
                $('#warunki_box').slideUp();
                $('#warunki_porzucenia').slideUp();
            }
            if (parseInt(id) == 3) {
                $('#warunki').slideUp();
                $('#grupa_klientow').slideUp();
                $('#grupa_klientow_rejestracja').slideUp();
                $('#warunki_box').slideDown();
                $('#warunki_porzucenia').slideUp();
            } 
            if (parseInt(id) == 4) {
                $('#warunki').slideUp();
                $('#grupa_klientow').slideUp();
                $('#grupa_klientow_rejestracja').slideUp();
                $('#warunki_box').slideUp();
                $('#warunki_porzucenia').slideUp();
            }       
            if (parseInt(id) == 7 || parseInt(id) == 8 || parseInt(id) == 9) {
                $('#warunki').slideUp();
                $('#grupa_klientow').slideUp();
                $('#grupa_klientow_rejestracja').slideUp();
                $('#warunki_box').slideUp();
                $('#warunki_porzucenia').slideDown();
            }          
            if (parseInt(id) == 5) {
                $('#warunki').slideUp();
                $('#grupa_klientow').slideUp();
                $('#grupa_klientow_rejestracja').slideUp();
                $('#warunki_box').slideUp();
                $('#warunki_porzucenia').slideUp();
            }
            if (parseInt(id) == 2) {
                $('#grupy_newslettera').slideDown();
              } else {
                $('#grupy_newslettera').slideUp();
            }        
          }        
          </script>        

          <div class="poleForm">
            <div class="naglowek">Edycja danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $pokazObjasnienia = false;
            
            $zapytanie = "select * from newsletters where newsletters_id = '" . (int)$_GET['id_poz'] . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
            
                $info = $sql->fetch_assoc();
                
                $pokazObjasnienia = true;
                ?>
                
                <form action="newsletter/newsletter_edytuj.php" method="post" id="newsForm" class="cmxform">          
            
                <div class="pozycja_edytowana">
                    
                    <div class="info_content">
                
                    <input type="hidden" name="akcja" value="zapisz" />
                
                    <input type="hidden" name="id" value="<?php echo (int)$_GET['id_poz']; ?>" />
                    
                    <p>
                        <label for="szablon">Szablon emaila:</label>
                        <?php
                        $tablica = Funkcje::ListaSzablonowEmail(false);
                        echo Funkcje::RozwijaneMenu('szablon', $tablica, $info['templates_id'], 'id="szablon"'); ?>
                    </p>

                    <p id="wersja">
                      <label>Wersja językowa szablonu:</label>
                      <?php
                      $jezyki = "SELECT * FROM languages WHERE status = '1' ORDER BY sort_order";
                      $sqlr = $db->open_query($jezyki);
                      while ($wartosciJezykow = $sqlr->fetch_assoc()) {
                         echo '<input type="radio" value="'.$wartosciJezykow['languages_id'].'" name="jezyk" id="for_jezyk_'.$wartosciJezykow['languages_id'].'" '.( $info['language_id'] == $wartosciJezykow['languages_id'] ? 'checked="checked"' : '' ).' /> <label class="OpisFor" for="for_jezyk_'.$wartosciJezykow['languages_id'].'">'.$wartosciJezykow['name'] . '</label>';
                      }
                      $db->close_query($sqlr);
                      unset($jezyki);
                      ?>
                    </p>   
                    
                    <hr style="color:#82b4cd;border-top: 1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:98%;" />

                    <p>
                        <label for="odbiorcy">Odbiorcy newslettera:</label>
                        <?php
                        $tab_tmp = array();
                        $tab_tmp[] = array('id' => 1, 'text' => 'do wszystkich zarejestrowanych klientów sklepu');      
                        $tab_tmp[] = array('id' => 2, 'text' => 'tylko zarejestrowani klienci którzy wyrazili zgodę na newsletter');   
                        $tab_tmp[] = array('id' => 3, 'text' => 'tylko klienci którzy zapisali się do newslettera, a nie są klientami sklepu');   
                        $tab_tmp[] = array('id' => 4, 'text' => 'do wszystkich którzy zapisali się do newslettera');
                        $tab_tmp[] = array('id' => 5, 'text' => 'mailing');   
                        $tab_tmp[] = array('id' => 6, 'text' => 'tylko do określonej grupy klientów');   
                        $tab_tmp[] = array('id' => 7, 'text' => 'tylko zarejestrowani klienci z porzuconymi koszykami');
                        $tab_tmp[] = array('id' => 8, 'text' => 'tylko klienci bez rejestracji z porzuconymi koszykami');
                        $tab_tmp[] = array('id' => 9, 'text' => 'wszyscy klienci z porzuconymi koszykami (z kontem oraz bez rejestracji)');
                        //
                        echo Funkcje::RozwijaneMenu('odbiorcy', $tab_tmp, $info['destination'], ' onclick="zmien_divy(this.value)" id="odbiorcy"'); 
                        //
                        unset($tab_tmp);
                        ?>
                    </p>        

                    <div id="warunki" <?php echo (($info['destination'] == 1 || $info['destination'] == 2 || $info['destination'] == 6) ? '' : 'style="display:none"'); ?>>
                        <p>
                          <label for="data_od">Data zamówienia od:</label>
                          <input type="text" name="data_od" id="data_od" value="<?php echo ((Funkcje::czyNiePuste($info['order_date_start'])) ? date('d-m-Y',FunkcjeWlasnePHP::my_strtotime($info['order_date_start'])) : ''); ?>" size="20" class="datepicker" />      
                          do: <input type="text" name="data_do" value="<?php echo ((Funkcje::czyNiePuste($info['order_date_end'])) ? date('d-m-Y',FunkcjeWlasnePHP::my_strtotime($info['order_date_end'])) : ''); ?>" size="20" class="datepicker" />      
                        </p>
                        
                        <p>
                          <label for="order_status">Status zamówienia:</label>
                          <?php
                          $tablica = Sprzedaz::ListaStatusowZamowien(true, '--- wybierz z listy ---');
                          echo Funkcje::RozwijaneMenu('status', $tablica,$info['order_status'],'style="width: 350px;" id="order_status"'); ?>
                        </p> 

                        <p>
                          <label for="wartosc_od">Wartość zamówienia od:</label>
                          <input type="text" name="wartosc_od" id="wartosc_od" class="kropka" value="<?php echo (($info['order_min'] > 0) ? $info['order_min'] : ''); ?>" size="20" />      
                          do: <input type="text" name="wartosc_do" class="kropka" value="<?php echo (($info['order_max'] > 0) ? $info['order_max'] : ''); ?>" size="20" />      
                        </p>                    
                    </div>
                    
                    <div id="warunki_porzucenia" <?php echo (($info['destination'] == 7 || $info['destination'] == 8 || $info['destination'] == 9) ? '' : 'style="display:none"'); ?>>
                        <p>
                          <label for="data_porzucenia_od">Data porzucenia koszyka od:</label>
                          <input type="text" name="data_porzucenia_od" value="<?php echo ((Funkcje::czyNiePuste($info['basket_date_start'])) ? date('d-m-Y',FunkcjeWlasnePHP::my_strtotime($info['basket_date_start'])) : ''); ?>" size="20" class="datepicker" />      
                          do: <input type="text" name="data_porzucenia_do" id="data_porzucenia_do" value="<?php echo ((Funkcje::czyNiePuste($info['basket_date_end'])) ? date('d-m-Y',FunkcjeWlasnePHP::my_strtotime($info['basket_date_end'])) : ''); ?>" size="20" class="datepicker" />      
                        </p>            
                    </div>                    
                    
                    <div id="warunki_box" <?php echo (($info['destination'] != 3) ? 'style="display:none"' : ''); ?>>
                        <p>
                          <label for="data_aktywacji">Data aktywacji od:</label>
                          <input type="text" name="data_aktywacji" id="data_aktywacji" value="<?php echo ((Funkcje::czyNiePuste($info['activation'])) ? date('d-m-Y',FunkcjeWlasnePHP::my_strtotime($info['activation'])) : ''); ?>" size="20" class="datepicker" />   
                        </p>                  
                    </div>        

                    <div id="grupa_klientow" <?php echo (($info['destination'] == 6) ? '' : 'style="display:none"'); ?>>
                        <p>
                          <label for="gr_klientow">Tylko do grupy klientów:</label>
                          <?php
                          $tablica = array();                  
                          $zapytanie_grupa = "SELECT customers_groups_id, customers_groups_name FROM customers_groups";
                          $sqls = $db->open_query($zapytanie_grupa);
                          //
                          while($nazwa_grupy = $sqls->fetch_assoc()) {
                              if ( (int)$nazwa_grupy['customers_groups_id'] == 1 ) {                      
                                    $tablica[] = array('id' => '998', 'text' => 'Domyślna (klienci z rejestracją i bez rejestracji)');
                                    $tablica[] = array('id' => '999', 'text' => 'Domyślna (tylko klienci z rejestracją)');
                              } else {
                                    $tablica[] = array('id' => $nazwa_grupy['customers_groups_id'], 'text' => $nazwa_grupy['customers_groups_name']);
                              }
                          }
                          $db->close_query($sqls);
                          unset($zapytanie_grupa);
                          //
                          echo Funkcje::RozwijaneMenu('grupa_klientow', $tablica, $info['customers_group_id'], 'id="gr_klientow"'); 
                          ?>
                        </p>                    
                    </div>

                    <div id="grupa_klientow_rejestracja" <?php echo (($info['destination'] == 1 || $info['destination'] == 2) ? '' : 'style="display:none"'); ?>>
                        <p>
                          <label for="gr_klientow_rejestracja">Tylko do grupy klientów:</label>
                          <?php
                          $tablica = Klienci::ListaGrupKlientow(true);                                        
                          echo Funkcje::RozwijaneMenu('grupa_klientow_rejestracja', $tablica, $info['customers_group_id'], 'id="gr_klientow_rejestracja"'); 
                          ?>
                        </p>                    
                    </div>
                    
                    <?php
                    $TablicaGrup = Newsletter::GrupyNewslettera();
                    if ( count($TablicaGrup) > 0 ) {
                    ?>
                    <div id="grupy_newslettera" class="GrupyNewslettera" <?php echo (($info['destination'] == 2) ? '' : 'style="display:none"'); ?>>
                        <table>
                            <tr>
                                <td><label>Tylko klienci przypisani <br /> do grupy:</label></td>   
                                <td>
                                
                                <span class="maleInfo" style="margin-left:2px">Jeżeli nie będzie zaznaczona żadna grupa domyślnie zostaną wybrane wszystkie grupy</span>
                                
                                <?php
                                foreach ($TablicaGrup as $Grupa) {
                                    //
                                    echo '<input type="checkbox" value="' . $Grupa['id'] . '" name="newsletter_grupa[]" id="newsletter_grupa_'.$Grupa['id'].'" ' . ((in_array((string)$Grupa['id'], explode(',', (string)$info['customers_newsletter_group']))) ? 'checked="checked"' : '') . ' /><label class="OpisFor" for="newsletter_grupa_'.$Grupa['id'].'">' . $Grupa['text'] . '</label><br />';
                                    //
                                }
                                ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <?php
                    unset($TablicaGrup);
                    }
                    ?>            
                    
                    <hr style="color:#82b4cd;border-top: 1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:98%;" />                 

                    <p>
                      <label class="required" for="temat">Tytuł newslettera:</label>
                      <input type="text" name="temat" id="temat" size="83" value="<?php echo $info['title']; ?>" />
                    </p>
                    
                    <p>
                      <label>Treść newslettera:</label>
                      <textarea id="wiadomosc" name="wiadomosc" cols="90" rows="10"><?php echo $info['content']; ?></textarea>
                    </p>  

                    </div>
                 
                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Zapisz dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('newsletter','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>');">Powrót</button>           
                </div>   

                </form>                

            <?php

            } else {
            
                echo '<div class="pozycja_edytowana">Brak danych do wyświetlenia</div>';
            
            }
            ?>

          </div>      

          <?php
          if ( $pokazObjasnienia == true ) {
          ?>
          
          <div class="objasnienia">
        
            <div class="objasnieniaTytul">Znaczniki, które możesz użyć w treści wiadomości:</div>
            <div class="objasnieniaTresc">

                <div style="padding-bottom:10px;font-weight:bold;">Treść wiadomości</div>
                
                <ul class="mcol">
                  <li><b>{LINK} dowolny tekst {/LINK}</b> - Link umożliwiający wypisanie się z newslettera (tekst pomiędzy znacznikami zostanie przekształcony na link)</li>
                  <li><b>{ADRES_URL_SKLEPU}</b> - Adres internetowy sklepu</li>
                </ul>

                <div style="padding-bottom:10px;font-weight:bold;">Dane sklepu</div>
                <ul class="mcol">
                  <?php
                  $zapytanieDef = "SELECT * FROM settings WHERE type = 'firma' OR type = 'sklep' ORDER BY type, sort";

                  $sqlDef = $db->open_query($zapytanieDef);

                  while ($infoDef = $sqlDef->fetch_assoc()) {
                    echo '<li><b>{'.$infoDef['code'].'}</b> - '.$infoDef['description'].'</li>';
                  }
                  $db->close_query($sqlDef);
                  unset($zapytanieDef,$infoDef);

                  ?>
                </ul>
                
                <div style="padding-bottom:10px;font-weight:bold;">Dane produktów</div>
                
                <ul class="mcol">
                  <li><b>{PRODUKT_X:NAZWA_PRODUKTU}</b> - Nazwa produktu o id X</li>
                  <li><b>{PRODUKT_X:LINK_NAZWA_PRODUKTU}</b> - Nazwa i link do produktu o id X</li>
                  <li><b>{PRODUKT_X:NR_KATALOGOWY}</b> - Nr katalogowy produktu o id X</li>
                  <li><b>{PRODUKT_X:KOD_PRODUCENTA}</b> - Kod producenta produktu o id X</li>
                  <li><b>{PRODUKT_X:KOD_EAN}</b> - Kod EAN produktu o id X</li>
                  <li><b>{PRODUKT_X:ZDJECIE_GLOWNE}</b> - Zdjęcie główne produktu o id X od szerokości 200px</li>
                  <li><b>{PRODUKT_X:ZDJECIE_GLOWNE:XXXpx}</b> - Zdjęcie główne produktu o id X od szerokości XXXpx (XXX - trzeba zastąpić wartością liczbową np 300 - gdzie będzie to 300px - podanie szerokości jako parametru jest wymagane !)</li>
                  <li><b>{PRODUKT_X:OPIS}</b> - Opis produktu o id X</li>
                  <li><b>{PRODUKT_X:OPIS_KROTKI}</b> - Opis krótki produktu o id X</li>
                </ul>                

                <div style="padding-bottom:10px;font-weight:bold;">Dane produktów w koszyku (tylko dla newslettera porzuconych koszyków)</div>
                
                <ul class="mcol">
                  <li><b>{LISTA_PRODUKTOW}</b> - Lista produktów w pozostałych w koszyku (lista - jeden pod drugim)</li>
                  <li><b>{LISTA_PRODUKTOW_LINKI}</b> - Lista produktów w pozostałych w koszyku w formie aktywnych linków do produktów (lista - jeden pod drugim)</li>
                </ul>  

                <div style="padding-bottom:10px;font-weight:bold;">Kod kuponu rabatowego (tylko dla newslettera wysyłanego przy generowaniu i wysyłaniu kuponów rabatowych w menu Asortyment / Kupony rabatowe)</div>
                
                <ul class="mcol">
                  <li><b>{KUPON_RABATOWY}</b> - Kod kuponu rabatowego - w miejsce tego znacznika będzie podstawiany wygenerowany kod kuponu rabatowego</li>
                </ul>                 
     
            </div>
          </div> 

          <?php
          }
          ?>

          <?php
          $db->close_query($sql);
          unset($info); 
          ?>

    </div>    
    
    <?php
    include('stopka.inc.php');

}