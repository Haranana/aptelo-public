<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        //
        $pola = array(
                array('templates_id',(int)$_POST['szablon']),
                array('language_id',(int)$_POST['jezyk']),
                array('title',$_POST['temat']),
                array('content',$_POST['wiadomosc']),
                array('destination',(int)$_POST['odbiorcy']),
                array('date_added','now()')
        );
        
        if ((int)$_POST['odbiorcy'] == 1 || (int)$_POST['odbiorcy'] == 2 || (int)$_POST['odbiorcy'] == 6) {
            //
            if (!empty($_POST['data_od'])) {
                $pola[] = array('order_date_start',date('Y-m-d', FunkcjeWlasnePHP::my_strtotime($filtr->process($_POST['data_od']))));
            }
            if (!empty($_POST['data_do'])) {
                $pola[] = array('order_date_end',date('Y-m-d', FunkcjeWlasnePHP::my_strtotime($filtr->process($_POST['data_do']))));
            }
            $pola[] = array('order_status',(int)$_POST['status']);
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
            $pola[] = array('customers_group_id',(int)$_POST['grupa_klientow']);
            //
        }

        // jezeli wybrano odbiorcow dla okreslonej grupy klientow
        if ((int)$_POST['odbiorcy'] == 1 || (int)$_POST['odbiorcy'] == 2) {
            //
            $pola[] = array('customers_group_id',(int)$_POST['grupa_klientow_rejestracja']);
            //
        }           
        
        $sql = $db->insert_query('newsletters' , $pola);
        unset($pola);         
     
        $id_dodanej_pozycji = $db->last_id_query();
 
        //
        if (isset($id_dodanej_pozycji) && $id_dodanej_pozycji > 0) {
            Funkcje::PrzekierowanieURL('newsletter.php?id_poz='.$id_dodanej_pozycji);
        } else {
            Funkcje::PrzekierowanieURL('newsletter.php');
        }
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Definiowanie nowego newslettera</div>
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
        <div class="naglowek">Dodawanie danych</div>
        
        <form action="newsletter/newsletter_dodaj.php" method="post" id="newsForm" class="cmxform">   
        
        <div class="pozycja_edytowana">
        
            <div class="info_content">
        
            <input type="hidden" name="akcja" value="zapisz" />
            
            <p>
                <label for="szablon">Szablon emaila:</label>
                <?php
                $tablica = Funkcje::ListaSzablonowEmail(false);
                echo Funkcje::RozwijaneMenu('szablon', $tablica, '', 'id="szablon"' ); ?>
            </p>

            <p id="wersja">
              <label>Wersja językowa szablonu:</label>
              <?php
              echo Funkcje::RadioListaJezykow();
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
                echo Funkcje::RozwijaneMenu('odbiorcy', $tab_tmp, '', ' onclick="zmien_divy(this.value)" id="odbiorcy"'); 
                //
                unset($tab_tmp);
                ?>
            </p>        

            <div id="warunki">
                <p>
                  <label for="data_od">Data zamówienia od:</label>
                  <input type="text" name="data_od" value="" size="20" class="datepicker" />      
                  do: <input type="text" name="data_do" id="data_do" value="" size="20" class="datepicker" />      
                </p>
                
                <p>
                  <label for="status">Status zamówienia:</label>
                  <?php
                  $tablica = Sprzedaz::ListaStatusowZamowien(true, '--- wybierz z listy ---');
                  echo Funkcje::RozwijaneMenu('status', $tablica,'','style="width: 350px;" id="status"'); ?>
                </p> 

                <p>
                  <label for="wartosc_od">Wartość zamówienia od:</label>
                  <input type="text" name="wartosc_od" id="wartosc_od" class="kropka" value="" size="20" />      
                  do: <input type="text" name="wartosc_do" class="kropka" value="" size="20" />      
                </p>                    
            </div>
            
            <div id="warunki_porzucenia" style="display:none">
                <p>
                  <label for="data_porzucenia_od">Data porzucenia koszyka od:</label>
                  <input type="text" name="data_porzucenia_od" value="" size="20" class="datepicker" />      
                  do: <input type="text" name="data_porzucenia_do" id="data_porzucenia_do" value="" size="20" class="datepicker" />      
                </p>            
            </div>
            
            <div id="warunki_box" style="display:none">
                <p>
                  <label for="data_aktywacji">Data aktywacji od:</label>
                  <input type="text" name="data_aktywacji" id="data_aktywacji" value="" size="20" class="datepicker" />   
                </p>                  
            </div>            
            
            <div id="grupa_klientow" style="display:none">
                <p>
                  <label for="gr_klientow">Tylko do grupy klientów:</label>
                  <?php
                  $tablica = array();                  
                  $zapytanie = "SELECT customers_groups_id, customers_groups_name FROM customers_groups";
                  $sql = $db->open_query($zapytanie);
                  //
                  while($nazwa_grupy = $sql->fetch_assoc()) {
                      if ( (int)$nazwa_grupy['customers_groups_id'] == 1 ) {                      
                            $tablica[] = array('id' => '998', 'text' => 'Domyślna (klienci z rejestracją i bez rejestracji)');
                            $tablica[] = array('id' => '999', 'text' => 'Domyślna (tylko klienci z rejestracją)');
                      } else {
                            $tablica[] = array('id' => $nazwa_grupy['customers_groups_id'], 'text' => $nazwa_grupy['customers_groups_name']);
                      }
                  }
                  $db->close_query($sql);
                  unset($zapytanie);
                  //
                  echo Funkcje::RozwijaneMenu('grupa_klientow', $tablica, '', 'id="gr_klientow"'); 
                  ?>
                </p>                    
            </div>
            
            <div id="grupa_klientow_rejestracja">
                <p>
                  <label for="gr_klientow_rejestracja">Tylko do grupy klientów:</label>
                  <?php
                  $tablica = Klienci::ListaGrupKlientow(true);                                        
                  echo Funkcje::RozwijaneMenu('grupa_klientow_rejestracja', $tablica, '', 'id="gr_klientow_rejestracja"'); 
                  ?>
                </p>                    
            </div>
            
            <?php
            $TablicaGrup = Newsletter::GrupyNewslettera();
            if ( count($TablicaGrup) > 0 ) {
            ?>
            <div id="grupy_newslettera" class="GrupyNewslettera" style="display:none">
                <table>
                    <tr>
                        <td><label>Tylko klienci przypisani <br /> do grupy:</label></td>   
                        <td>
                        
                        <span class="maleInfo" style="margin-left:2px">Jeżeli nie będzie zaznaczona żadna grupa domyślnie zostaną wybrane wszystkie grupy</span>
                        
                        <?php
                        foreach ($TablicaGrup as $Grupa) {
                            //
                            echo '<input type="checkbox" value="' . $Grupa['id'] . '" name="newsletter_grupa[]" id="newsletter_grupa_'.$Grupa['id'].'" /><label class="OpisFor" for="newsletter_grupa_'.$Grupa['id'].'">' . $Grupa['text'] . '</label><br />';
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
              <input type="text" name="temat" id="temat" size="83" value="" />
            </p>
            
            <p>
              <label>Treść newslettera:</label>
              <textarea id="wiadomosc" name="wiadomosc" cols="90" rows="10"></textarea>
            </p>       

            </div>
            
        </div>

        <div class="przyciski_dolne">
          <input type="submit" class="przyciskNon" value="Zapisz dane" />
          <button type="button" class="przyciskNon" onclick="cofnij('newsletter','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>');">Powrót</button>   
        </div>

        </form>            

      </div> 

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
              $zapytanie = "SELECT * FROM settings WHERE type = 'firma' OR type = 'sklep' ORDER BY type, sort";

              $sql = $db->open_query($zapytanie);

              while ($info = $sql->fetch_assoc()) {
                echo '<li><b>{'.$info['code'].'}</b> - '.$info['description'].'</li>';
              }
              $db->close_query($sql);
              unset($zapytanie,$info);

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

    </div>    
    
    <?php
    include('stopka.inc.php');

}