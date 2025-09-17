<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

      $lista_kategorii = '';
      if ( $_POST['typ'] == '2' && isset($_POST['id_kat']) ) {
        $lista_kategorii = implode(',', (array)$_POST['id_kat'] );
      }
      
      $lista_producentow = '';
      if ( $_POST['typ'] == '3'&& isset($_POST['id_producent']) ) {
        $lista_producentow = implode(',', (array)$_POST['id_producent'] );
      }      

      $lista_pol = '';
      if ( $_POST['format'] == '1' ) {
        if ( isset($_POST['id_extra_field']) && count($_POST['id_extra_field']) > 0 ) {
          foreach ( $_POST['id_extra_field'] as $val ) {
            if ( $_POST['desc_extra_field'][$val] != '' ) {
              $lista_pol .= $val . ':' . $filtr->process($_POST['desc_extra_field'][$val]) . ',';
            }
          }

        }
        $lista_pol = substr((string)$lista_pol, 0, -1);
      }
      
      $lista_cech = '';
      if ( $_POST['format'] == '1' ) {
        if ( isset($_POST['id_attributes']) && count($_POST['id_attributes']) > 0 ) {
          foreach ( $_POST['id_attributes'] as $val ) {
            if ( $_POST['desc_attributes'][$val] != '' ) {
              $lista_cech .= $val . ':' . $filtr->process($_POST['desc_attributes'][$val]) . ',';
            }
          }

        }
        $lista_cech = substr((string)$lista_cech, 0, -1);
      }   

      $lista_rodzajow = '';
      if ( isset($_POST['rodzaj_produktu'])  ) {
        $lista_rodzajow = implode(',', (array)$_POST['rodzaj_produktu'] );
      }

      $lista_dostepnosci = '';
      if ( isset($_POST['dostepnosc_produktu'])  ) {
        $lista_dostepnosci = implode(',', (array)$_POST['dostepnosc_produktu'] );
      }

      $pola = array(
              array('comparisons_description',$filtr->process($_POST['opis'])),
              array('comparisons_file_export',$filtr->process($_POST['nazwa_pliku'])),
              array('comparisons_availability',$filtr->process($_POST['dostepnosc'])),
              array('comparisons_conditions',((isset($_POST['stan'])) ? $filtr->process($_POST['stan']) : '1')),
              array('comparisons_products_type',$lista_rodzajow),
              array('comparisions_availability_id',$lista_dostepnosci),
              array('comparisons_export_type',(int)$_POST['typ']),
              array('comparisons_export_quantity',(int)$_POST['stan_magazynu']),
              array('comparisons_export_min_quantity',(int)$_POST['min_ilosc']),
              array('comparisons_categories',$lista_kategorii),
              array('comparisons_manufacturers',$lista_producentow),
              array('comparisons_extra_fields',$lista_pol),
              array('comparisons_attributes',$lista_cech),
              array('comparisions_discount',(float)$_POST['narzut']),
              array('comparisons_name_info',(int)$_POST['dodatkowa_nazwa']),
              array('comparisons_price_level',(int)$_POST['cena']),
              array('comparisions_text_search',$filtr->process($_POST['fraza'])),
              array('comparisions_products_id_private',$filtr->process($_POST['id_zewnetrzne'])),
              array('comparisons_shipping_type',(int)$_POST['wysylka']),
              array('comparisons_default_shipping_cost',(float)$_POST['koszt_wysylki']),
              array('comparisons_default_shipping_name',$_POST['nazwa_wysylki']),
              array('comparisons_products_day',(int)$_POST['produkt_dnia']),
              array('comparisions_ceneo_kup_teraz',(int)$_POST['ceneo_kup_teraz']),
              array('comparisons_pickup',$filtr->process($_POST['odbior_osobisty_czas'])),
              array('comparisons_all_products',$filtr->process($_POST['wszystkie_produkty'])),
              array('comparisions_cron',(int)$_POST['cron_status']),
              array('comparisions_cron_token',(((int)$_POST['cron_status'] == 1) ? $filtr->process($_POST['cron_token']) : '')),
              array('comparisons_description_html',(int)$_POST['usuwanie_html']),
              array('comparisons_language_id',(int)$_POST['jezyk'])
      );

      $sql = $db->update_query('comparisons', $pola, " comparisons_id = '".(int)$_POST["id"]."'");	
      unset($pola);

      Funkcje::PrzekierowanieURL('porownywarki.php?id_poz='.(int)$_POST["id"]);

    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>

    <div id="naglowek_cont">Konfiguracja parametrów eksportu dla porównywarek</div>
    <div id="cont">
     
      <script>
      $(document).ready(function() {
        
          jQuery.validator.addMethod("lettersonly", function(value, element) {
            return this.optional(element) || /^[a-z]+$/i.test(value);
          }, "Można wprowadzić tylko litery");         
        
          $('#cron_token').on("keyup",function() {
             $('#SciezkaCron').html( $('#SciezkaCron').attr('data-link') + '&token=' + $(this).val() );
          });
          
          $("#porownywarkiForm").validate({
            rules: {
              cron_token: {required: function() {var wynik = true; if ( $("input[name='cron_status']:checked", "#porownywarkiForm").val() == "0" ) { wynik = false; } return wynik; }, lettersonly: true }
            }
          });                      
      });                  
      function AktywnyCron(tryb) {
          if ( tryb == 1 ) {
               $('.EksportCronInfo').slideDown();
            } else {
               $('.EksportCronInfo').slideUp();
          }
      };
      </script>   
                
      <form action="porownywarki/porownywarki_edytuj.php" method="post" id="porownywarkiForm" class="cmxform">
        <div class="poleForm">

            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            } 

            $zapytanie = "SELECT * FROM comparisons WHERE comparisons_id = '".(int)$_GET['id_poz']."'";
            $sql = $db->open_query($zapytanie);
            
            if ( $db->ile_rekordow($sql) > 0 )  {
                
                $info = $sql->fetch_assoc();

                // fragment do mapowania kategorii dla Google Shopping
                if ( strpos((string)$info['comparisons_plugin'], 'googleshopping') > -1 || strpos((string)$info['comparisons_plugin'], 'facebook_reklamy') > -1 ) {

                    if ( $info['comparisons_conditions'] == '0' ) {
                        $info['comparisons_conditions'] = '1';
                    }
                    
                    $zapytanie_jezyk = "select languages_id, name, code, currencies_default from languages where languages_id = " . $info['comparisons_language_id'];
                    $sql_jezyk = $db->open_query($zapytanie_jezyk);
                    //
                    if ( $db->ile_rekordow($sql_jezyk) > 0 )  {
                         //
                         $wynik_jezyk = $sql_jezyk->fetch_assoc();
                         //
                         if ( $wynik_jezyk['code'] != 'en' ) {
                              //
                              $czesc_1 = $wynik_jezyk['code'];
                              $czesc_2 = $wynik_jezyk['code'];
                              //
                              // czechy
                              if ( $wynik_jezyk['code'] == 'CZ' ) {
                                   $czesc_1 = 'cs';
                              }
                              // dania
                              if ( $wynik_jezyk['code'] == 'DK' ) {
                                   $czesc_1 = 'da';
                              } 
                              //
                              $kod_jezyka = strtolower($czesc_1) . '-'. strtoupper($czesc_2);
                              //
                              unset($czesc_1, $czesc_2);
                              //
                         } else {
                              //
                              $kod_jezyka = 'en-GB';
                              //
                         }
                         //
                         unset($wynik_jezyk);
                         //
                    } else {
                         //
                         $kod_jezyka = 'en-GB';
                         //
                    }
                    //
                    $db->close_query($sql_jezyk);
                    unset($zapytanie_jezyk); 
                    
                    $url = 'http://www.google.com/basepages/producttype/taxonomy.'.$kod_jezyka.'.txt';
                    
                    $czyJestPlikZdalny = Funkcje::remoteFileExists($url);
                    
                    if (!$czyJestPlikZdalny) {
                        $url = 'http://www.google.com/basepages/producttype/taxonomy.en-GB.txt';
                    }
                    
                    // lokalny plik kategorii
                    $path = 'cache/taxonomy.'.$kod_jezyka.'.txt';

                    // pobiera date modyfikacji pliku z kategoriami
                    if (file_exists($path)) {
                        $dataPliku = filemtime($path);

                        // jezeli plik jest starszy niz 1 dzien, to pobiera nowy
                        if ( time() - $dataPliku > 3600 * 24 ) { 

                            $blad = '';

                            $czyJestPlikZdalny = Funkcje::remoteFileExists($url);

                            if ($czyJestPlikZdalny) {
                                if (is_writeable($path)) {

                                    $fp = fopen($path, 'w');
                                     
                                    $ch = curl_init($url);
                                    curl_setopt($ch, CURLOPT_FILE, $fp);
                                     
                                    $data = curl_exec($ch);
                                     
                                    curl_close($ch);
                                    fclose($fp);
                                } else {
                                    $blad = 'brak praw do zapisu do pliku kategorii Google : ' . $path;
                                }
                            }
                        }

                    } else {

                        $blad = '';

                        $czyJestPlikZdalny = Funkcje::remoteFileExists($url);

                        if ($czyJestPlikZdalny) {

                            if (is_writeable('cache')) {

                                $fp = fopen($path, 'w');
                                     
                                $ch = curl_init($url);
                                curl_setopt($ch, CURLOPT_FILE, $fp);
                                     
                                $data = curl_exec($ch);
                                     
                                curl_close($ch);
                                fclose($fp);
                                
                            } else {
                              
                                $blad = 'brak praw do zapisu do pliku kategorii Google : ' . $path;
                                
                            }
                        }

                    }

                    // zapytanie sql wyciągające categorie i tworzace tablice
                    $zapytanie_kategorie = "SELECT cd.categories_name, c.categories_id, c.parent_id, ga.categories_google 
                                              FROM categories AS c
                                              LEFT JOIN categories_description AS cd ON cd.categories_id=c.categories_id
                                              LEFT JOIN google_categories ga ON ga.categories_id = c.categories_id AND ga.categories_language_id = '" . $info['comparisons_language_id'] . "'
                                              WHERE cd.language_id= '" . $_SESSION['domyslny_jezyk']['id'] . "'
                                              ORDER BY c.sort_order";

                    $sql_kategorie = $db->open_query($zapytanie_kategorie);

                    while ($info_kategorie = $sql_kategorie->fetch_assoc()) {
                        $tablica_kategorii[$info_kategorie['categories_id']] = array ('id' => $info_kategorie['categories_id'], 'nadrzedna' => $info_kategorie['parent_id'], 'nazwa_kategorii' => $info_kategorie['categories_name'], 'kategoria_google' => $info_kategorie['categories_google']);
                    }

                    $db->close_query($sql_kategorie);
                    unset($zapytanie_kategorie, $info_kategorie);    

                    //przeksztalcenie tablicy kategorii na tablice z pokategoriami
                    $tree = Kategorie::kategorieNaDrzewo($tablica_kategorii);
                    unset($tablica_kategorii);    
                }

                // podziel nazwe plugin
                $podzial = explode('__', (string)$info['comparisons_plugin']);
                $nazwa_plugin = $podzial[0];

                $tablicaDostepnosci = array();
                $tablicaDostepnosci = Porownywarki::TablicaDostepnosciNiezdefiniowanych($nazwa_plugin);
                if ( strpos((string)$info['comparisons_plugin'], 'starcode') > -1 ) {
                    $tablicaDostepnosci = Porownywarki::TablicaDostepnosciNiezdefiniowanych('nokaut');
                }
                if ( strpos((string)$info['comparisons_plugin'], 'favi') > -1 ) {
                    $tablicaDostepnosci = Porownywarki::TablicaDostepnosciNiezdefiniowanych('ceneo');
                }                
                ?>
                
                <div class="naglowek">Edycja danych <?php echo $info['comparisons_name']; ?></div>

                <div class="pozycja_edytowana">
                
                  <div class="info_content">

                  <?php echo (( isset($blad) && $blad != '') ? '<div class="ostrzezenie" style="margin:10px 0 10px 10px;">' . $blad .'</div>' : '' ); ?>

                  <input type="hidden" name="akcja" value="zapisz" />
                  <input type="hidden" name="id" value="<?php echo (int)$_GET['id_poz']; ?>" />
                  
                  <script>
                  $(document).ready(function() {
                      $('#nazwa_pliku').rules( "add", {
                          required: true, messages: { required: "Pole jest wymagane." } 
                      });
                  });
                  </script>    

                  <p>
                    <label class="required" for="nazwa_pliku">Nazwa pliku wynikowego (bez rozszerzenia):</label>
                    <input type="text" size="50" name="nazwa_pliku" id="nazwa_pliku" value="<?php echo ((empty($info['comparisons_file_export'])) ? $info['comparisons_plugin'] : $info['comparisons_file_export']); ?>" />
                  </p>   
                  
                  <p>
                    <label for="opis">Opis porównywarki:</label>
                    <textarea rows="2" cols="80" style="max-width:90%" name="opis" id="opis"><?php echo $info['comparisons_description']; ?></textarea>                    
                    <div class="maleInfo">Maksymalna ilość znaków opisu: 250</div>
                  </p>   

                  <div class="EksportCron">
                  
                      <p>
                        <label>Czy umożliwić generowania poprzez zewnętrzny link:</label>
                        <input type="radio" value="0" name="cron_status" onclick="AktywnyCron(0)" id="cron_status_nie" <?php echo ($info['comparisions_cron'] == '0' ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="cron_status_nie">nie</label>
                        <input type="radio" value="1" name="cron_status" onclick="AktywnyCron(1)" id="cron_status_tak" <?php echo ($info['comparisions_cron'] == '1' ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="cron_status_tak">tak</label>
                      </p>        

                      <div class="EksportCronInfo" <?php echo ($info['comparisions_cron'] == '0' ? ' style="display:none"' : ''); ?>>
                      
                          <p>
                            <label>Token pliku:</label>
                            <input type="text" value="<?php echo $info['comparisions_cron_token']; ?>" name="cron_token" id="cron_token" size="15" /> <em class="TipIkona"><b>Token (ciąg znaków z liter) do zabezpieczenia generowania pliku przez osoby nieupoważnione</b></em>
                          </p>   

                          <p>
                            <label>Ścieżka pliku generowania pliku poza sklepem:</label>
                            <span id="SciezkaCron" data-link="<?php echo ADRES_URL_SKLEPU . '/porownywarka_xml.php?plugin=' . $info['comparisons_plugin']; ?>"><?php echo ADRES_URL_SKLEPU . '/porownywarka_xml.php?plugin=' . $info['comparisons_plugin'] . '&token=' . $info['comparisions_cron_token']; ?></span>
                          </p>   

                          <div class="maleInfo">
                              Podany powyżej link można użyć do generowania pliku dla porównywarki poza sklepem - bezpośrednio z poziomu przeglądarki. Można go również użyć do cyklicznego wykonywania w zadaniach CRON na serwerze. 
                              Podanego skryptu <b>nie</b> można dodać do Harmonogramu zadań w sklepie (menu Narzędzia) ponieważ spowoduje to zablokowanie działania sklepu.
                           </div>
                      
                      </div>
                  
                  </div>
                  
                  <p>
                    <label>Język w jakim mają być eksportowane dane:</label>
                    <?php
                    $sqli = $db->open_query("select languages_id, name, code, image from languages where status = '1' order by languages_default desc, sort_order");      
                    while ($lang = $sqli->fetch_assoc()) {
                        //
                        echo '<input type="radio" value="' . $lang['languages_id'] . '" id="for_jezyk_' . $lang['languages_id'] . '" name="jezyk" ' . ( $info['comparisons_language_id'] == $lang['languages_id'] ? 'checked="checked"' : '' ) . ' /><label class="OpisFor" for="for_jezyk_' . $lang['languages_id'] . '">' . $lang['name'] . '</label>';
                        //
                    }
                    //
                    $db->close_query($sqli);
                    ?>
                  </p>

                  <?php
                  if ( count($tablicaDostepnosci) > 0 ) {
                      ?>
                      <p>
                        <label>Domyślna dostępność:</label>
                        <?php
                        echo Funkcje::RozwijaneMenu('dostepnosc', $tablicaDostepnosci, $info['comparisons_availability'], 'style="width:300px;"');
                        unset($tablicaDostepnosci);
                        ?><em class="TipIkona"><b>Dostępność produktu - jeżeli nie została zdefiniowana bezpośrednio dla towaru. Musi być zgodna ze specyfikacją porównywarki</b></em>
                      </p>
                      <?php
                  } else {
                    echo '<input type="hidden" name="dostepnosc" value="1" />';
                  }
                  ?>
                  
                  <?php if ( strpos((string)$info['comparisons_plugin'], 'ceneo') > -1 ) { ?>
                  
                  <p>
                    <label>Dodaj do eksportowanych produktów opcję "Ceneo Kup teraz":</label>
                    <input type="radio" value="0" name="ceneo_kup_teraz" id="ceneo_kup_teraz_nie" <?php echo ($info['comparisions_ceneo_kup_teraz'] == '0' ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="ceneo_kup_teraz_nie">nie<em class="TipIkona"><b>Eksportowane będą produkty nie będą miały dodatkowe znacznika Ceneo (basket) opcji Kup Teraz</b></em></label>
                    <input type="radio" value="1" name="ceneo_kup_teraz" id="ceneo_kup_teraz_tak" <?php echo ($info['comparisions_ceneo_kup_teraz'] == '1' ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="ceneo_kup_teraz_tak">tak<em class="TipIkona"><b>Eksportowane będą produkty będą miały dodatkowy znacznik Ceneo (basket) opcji Kup Teraz</b></em></label>
                    <input type="radio" value="2" name="ceneo_kup_teraz" id="ceneo_kup_teraz_wybrane" <?php echo ($info['comparisions_ceneo_kup_teraz'] == '2' ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="ceneo_kup_teraz_wybrane">tylko zaznaczone<em class="TipIkona"><b>Tylko oznaczone produkty eksportowane będą znacznik Ceneo (basket) opcji Kup Teraz</b></em></label>
                  </p>                  
                  
                  <?php } else { ?>
                  
                    <input type="hidden" value="0" name="ceneo_kup_teraz" />
                  
                  <?php } ?>                  
                  
                  <?php if ( strpos((string)$info['comparisons_plugin'], 'googleshopping') > -1 ) { ?>
                  
                  <p>
                    <label for="odbior_osobisty_czas">W jakim czasie produkt będzie dostępny do odbioru w sklepie (przy możliwości odbioru osobistego) ?</label>
                    <select name="odbior_osobisty_czas" id="odbior_osobisty_czas">
                        <option value="same_day" <?php echo (((isset($info['comparisons_pickup']) && $info['comparisons_pickup'] == 'same_day') || !isset($info['comparisons_pickup']) || empty($info['comparisons_pickup'])) ? 'selected="selected"' : ''); ?>>same_day - tego samego dnia</option>
                        <option value="next_day" <?php echo ((isset($info['comparisons_pickup']) && $info['comparisons_pickup'] == 'next_day') ? 'selected="selected"' : ''); ?>>next_day - następnego dnia po zakupie</option>
                        <option value="2-day" <?php echo ((isset($info['comparisons_pickup']) && $info['comparisons_pickup'] == '2-day') ? 'selected="selected"' : ''); ?>>2-day - do odbioru w ciągu 2 dni</option>
                        <option value="3-day" <?php echo ((isset($info['comparisons_pickup']) && $info['comparisons_pickup'] == '3-day') ? 'selected="selected"' : ''); ?>>3-day - do odbioru w ciągu 3 dni</option>
                        <option value="4-day" <?php echo ((isset($info['comparisons_pickup']) && $info['comparisons_pickup'] == '4-day') ? 'selected="selected"' : ''); ?>>4-day - do odbioru w ciągu 4 dni</option>
                        <option value="5-day" <?php echo ((isset($info['comparisons_pickup']) && $info['comparisons_pickup'] == '5-day') ? 'selected="selected"' : ''); ?>>5-day - do odbioru w ciągu 5 dni</option>
                        <option value="6-day" <?php echo ((isset($info['comparisons_pickup']) && $info['comparisons_pickup'] == '6-day') ? 'selected="selected"' : ''); ?>>6-day - do odbioru w ciągu 6 dni</option>
                        <option value="multi-week" <?php echo ((isset($info['comparisons_pickup']) && $info['comparisons_pickup'] == 'multi-week') ? 'selected="selected"' : ''); ?>>multi-week - do odbioru za co najmniej tydzień</option>
                    </select>
                  </p>                  
                  
                  <?php } else { ?>
                  
                    <input type="hidden" value="same_day" name="odbior_osobisty_czas" />
                  
                  <?php } ?>

                  <p>
                    <label>Domyślny stan produktu:</label>
                    <input type="radio" value="1" name="stan" id="stan_nowy" <?php echo (($info['comparisons_conditions'] == '1') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="stan_nowy">nowy<em class="TipIkona"><b>Produkt jest nowy i fabrycznie zapakowany</b></em></label>
                    <input type="radio" value="2" name="stan" id="stan_uzywany" <?php echo (($info['comparisons_conditions'] == '2') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="stan_uzywany">używany<em class="TipIkona"><b>Produkt był używany, nie ma fabrycznego opakowania,itp.</b></em></label>
                    <input type="radio" value="3" name="stan" id="stan_odnowiony" <?php echo (($info['comparisons_conditions'] == '3') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="stan_odnowiony">odnowiony<em class="TipIkona"><b>Produkt był używany ale jest po regeneracji, np. cartridże do drukarek</b></em></label>
                  </p>

                  <p>
                    <label>Czy eksportować tylko produkty ze stanem więszym od 0:</label>
                    <input type="radio" value="0" name="stan_magazynu" id="export_nie" onclick="$('#eksport_min').stop().slideUp()" <?php echo ($info['comparisons_export_quantity'] == '0' ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="export_nie">nie<em class="TipIkona"><b>Eksportowane będą produkty niezależnie od ilości w magazynie</b></em></label>
                    <input type="radio" value="1" name="stan_magazynu" id="export_tak" onclick="$('#eksport_min').stop().slideDown()" <?php echo ($info['comparisons_export_quantity'] == '1' ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="export_tak">tak<em class="TipIkona"><b>Eksportowane będą tylko produkty, których stan magazynowy jest większy od 0</b></em></label>
                  </p>       

                  <div id="eksport_min" <?php echo (((int)$info['comparisons_export_quantity'] == 0) ? 'style="display:none"' : ''); ?>>

                      <p>
                        <label>Eksportować produkty tylko z ilością równą lub powyżej:</label>
                        <input type="text" value="<?php echo (($info['comparisons_export_min_quantity'] > 0) ? $info['comparisons_export_min_quantity'] : ''); ?>" name="min_ilosc" size="5" class="calkowita" />
                      </p>  
                  
                  </div>
                  
                  <p>
                    <label>Czy przy eksporcie dodawać <b>Dodatkową nazwę</b> produktu:</label>
                    <input type="radio" value="0" name="dodatkowa_nazwa" id="dod_nazwa_nie" <?php echo ($info['comparisons_name_info'] == '0' ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="dod_nazwa_nie">nie<em class="TipIkona"><b>Dodatkowa nazwa produktu nie będzie dodawana</b></em></label>
                    <input type="radio" value="1" name="dodatkowa_nazwa" id="dod_nazwa_tak" <?php echo ($info['comparisons_name_info'] == '1' ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="dod_nazwa_tak">tak<em class="TipIkona"><b>Do nazw produktów będzie dodawana Dodatkowa nazwa produktu zdefiniowana podczas edycji produktu</b></em></label>
                  </p>  

                  <p>
                    <label>Czy przy eksporcie kosztów wysyłki uwzględniać tylko Door To Door:</label>
                    <input type="radio" value="0" name="wysylka" id="wysylka_nie" <?php echo ($info['comparisons_shipping_type'] == '0' ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="wysylka_nie">nie<em class="TipIkona"><b>Koszt będzie ustalany na podstawie wszystkich dostępnych dla danego produktu form dostawy</b></em></label>
                    <input type="radio" value="1" name="wysylka" id="wysylka_tak" <?php echo ($info['comparisons_shipping_type'] == '1' ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="wysylka_tak">tak<em class="TipIkona"><b>Koszt będzie ustalany tylko na podstawie wysyłek Door To Door</b></em></label>
                  </p>  

                  <p>
                    <label>Domyślny koszt wysyłki:</label>
                    <input type="text" value="<?php echo (($info['comparisons_default_shipping_cost'] != 0) ? $info['comparisons_default_shipping_cost'] : ''); ?>" name="koszt_wysylki" size="6" class="kropka" /><em class="TipIkona"><b>Koszt wysyłki wstawiany w przypadku, gdy produkt nie ma dostępnej żadnej wysyłki</b></em>
                  </p>  

                  <p>
                    <label>Domyślna nazwa wysyłki:</label>
                    <input type="text" value="<?php echo (($info['comparisons_default_shipping_name'] != '') ? $info['comparisons_default_shipping_name'] : ''); ?>" name="nazwa_wysylki" size="40" /><em class="TipIkona"><b>Domyślna nazwa wysyłki wstawiana w przypadku, gdy produkt nie ma dostępnej żadnej wysyłki</b></em>
                  </p>  

                  <p>
                    <label>Rabat / narzut dla cen produktów przy eksporcie do porównywarki:</label>
                    <input type="text" value="<?php echo (($info['comparisions_discount'] != 0) ? $info['comparisions_discount'] : ''); ?>" name="narzut" size="5" class="kropka" /> % <em class="TipIkona"><b>Wartość procentowa (ujemna dla rabatu / dodatnia dla narzutu)</b></em>
                  </p>  

                  <p>
                    <label>Jeżeli produkt jest ustawiony jako produkt dnia eksportować cenę produktu dnia ?</label>
                    <input type="radio" value="0" name="produkt_dnia" id="produkt_dnia_nie" <?php echo ($info['comparisons_products_day'] == '0' ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="produkt_dnia_nie">nie<em class="TipIkona"><b>Będzie eksportowana standardowa cena produktu</b></em></label>
                    <input type="radio" value="1" name="produkt_dnia" id="produkt_dnia_tak" <?php echo ($info['comparisons_products_day'] == '1' ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="produkt_dnia_tak">tak<em class="TipIkona"><b>Będzie eksportowana cena ustawiona jako cena dnia</b></em></label>
                  </p>                    

                  <p>
                    <label>Czy eksportować wszystkie produkty (również przypisane do grup klientów) ?</label>
                    <input type="radio" value="0" name="wszystkie_produkty" id="wszystkie_produkty_nie" <?php echo ($info['comparisons_all_products'] == '0' ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="wszystkie_produkty_nie">nie<em class="TipIkona"><b>Będą eksportowane tylko produkty, które nie są przypisane do grup klientów</b></em></label>
                    <input type="radio" value="1" name="wszystkie_produkty" id="wszystkie_produkty_tak" <?php echo ($info['comparisons_all_products'] == '1' ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="wszystkie_produkty_tak">tak<em class="TipIkona"><b>Będą eksportowanie wszystkie produkty (przypisane i nie przypisane do grup klientów)</b></em></label>
                  </p> 
                  
                  <p>
                    <label>Eksportuj cenę nr:</label>
                    <?php
                    $tablica = array();
                    for ($x = 1; $x <= ILOSC_CEN; $x++) {
                      $tablica[] = array('id' => $x, 'text' => 'Cena nr ' . $x);
                    }
                    ?>                                          
                    <?php echo Funkcje::RozwijaneMenu('cena', $tablica, $info['comparisons_price_level'], 'style="width:100px;" id="cena"'); ?>
                  </p>                           

                  <p>
                    <label for="fraza_porownywarki">Eksportuj produkty z frazą w nazwie:</label>
                    <input type="text" value="<?php echo $info['comparisions_text_search']; ?>" name="fraza" id="fraza_porownywarki" size="40" /> <em class="TipIkona"><b>Fraza będzie wyszukiwana w nazwach produktów</b></em>
                  </p>  

                  <p>
                    <label for="id_zewnetrzne">Eksportuj produkty z frazą w Id zewnętrzne:</label>
                    <input type="text" value="<?php echo $info['comparisions_products_id_private']; ?>" name="id_zewnetrzne" id="id_zewnetrzne" size="40" /> <em class="TipIkona"><b>Fraza będzie wyszukiwana w pole ID zewnętrzne w danych produktów</b></em>
                  </p>                     
                  
                  <p>
                    <label>Czy usuwać w opisu produktu kod HTML ?</label>
                    <input type="radio" value="0" name="usuwanie_html" id="usuwanie_html_nie" <?php echo ($info['comparisons_description_html'] == '0' ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="usuwanie_html_nie">nie<em class="TipIkona"><b>Z opisu produktu nie będzie usuwany kod HTML</b></em></label>
                    <input type="radio" value="1" name="usuwanie_html" id="usuwanie_html_tak" <?php echo ($info['comparisons_description_html'] == '1' ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="usuwanie_html_tak">tak<em class="TipIkona"><b>Z opisu produktu będzie usuwany kod HTML</b></em></label>
                  </p>   
                  
                  <?php
                  $TablicaRodzajow = Produkty::TablicaRodzajProduktow();
                  ?>
                  <table class="WyborCheckbox">
                    <tr>
                        <td><label>Jakie rodzaje produktów eksportować:</label></td>
                        <td>
                            <?php                        
                            foreach ( $TablicaRodzajow as $Rodzaj ) {
                                echo '<input type="checkbox" value="' . $Rodzaj['id'] . '" name="rodzaj_produktu[]" id="rodzaj_produktu_' . $Rodzaj['id'] . '" ' . ((in_array((string)$Rodzaj['id'], explode(',', (string)$info['comparisons_products_type']))) ? 'checked="checked" ' : '') . ' /><label class="OpisFor" for="rodzaj_produktu_' . $Rodzaj['id'] . '">' . $Rodzaj['text'] . '</label><br />';
                            }              
                            ?>
                        </td>
                    </tr>
                    <tr><td></td><td><div class="maleInfo" style="margin-left:0px;">Jeżeli nie zostanie wybrany żaden rodzaj to będą eksportowane wszystkie produkty</div></td></tr>
                  </table>
                  
                  <?php
                  $TablicaRodzajow = Produkty::TablicaDostepnosci();
                  ?>
                  <table class="WyborCheckbox">
                    <tr>
                        <td><label>Eksportować tylko produkty z określonymi dostępnościami:</label></td>
                        <td>
                            <?php                        
                            foreach ( $TablicaRodzajow as $Rodzaj ) {
                                echo '<input type="checkbox" value="' . $Rodzaj['id'] . '" name="dostepnosc_produktu[]" id="dostepnosc_produktu_' . $Rodzaj['id'] . '" ' . ((in_array((string)$Rodzaj['id'], explode(',', (string)$info['comparisions_availability_id']))) ? 'checked="checked" ' : '') . ' /><label class="OpisFor" for="dostepnosc_produktu_' . $Rodzaj['id'] . '">' . $Rodzaj['text'] . '</label><br />';
                            }              
                            ?>
                        </td>
                    </tr>
                    <tr><td></td><td><div class="maleInfo" style="margin-left:0px;">Jeżeli nie zostanie wybrana żadna dostępność to będą eksportowane wszystkie produkty</div></td></tr>
                  </table>                  

                  <script>
                  function typ_export(nr) {
                      if ( nr == 0 || nr == 1 ) {
                           $('#drzewo_kategorii').slideUp();
                           $('#drzewo').slideUp();
                      }
                      if ( nr == 2 ) {
                           $('#drzewo').slideUp( function() {
                              $('#drzewo_kategorii').slideDown();                       
                           });
                      }
                      if ( nr == 3 ) {
                           $('#drzewo_kategorii').slideUp( function() {
                              $('#drzewo').slideDown();                           
                           });
                      }                      
                  }
                  </script>

                  <div style="margin:3px 10px 4px 10px">
                  
                      <table>
                        <tr>
                            <td><label>Typ eksportu:</label></td>
                            <td style="padding:3px">
                                <input type="radio" value="0" name="typ" id="typ_wszystkie" onclick="typ_export(0)" <?php echo (($info['comparisons_export_type'] == '0') ? 'checked="checked"' : ''); ?> /> <label class="OpisFor" for="typ_wszystkie">wszystkie produkty<em class="TipIkona"><b>Typ eksportu - wszystkie produkty - eksport wszystkich produktów z bazy danych</b></em></label> <br />
                                <input type="radio" value="1" name="typ" id="typ_produkty" onclick="typ_export(1)" <?php echo (($info['comparisons_export_type'] == '1') ? 'checked="checked"' : ''); ?> /> <label class="OpisFor" for="typ_produkty">tylko zaznaczone produkty<em class="TipIkona"><b>Typ eksportu - tylko zaznaczone produkty - eksport produktów z zaznaczoną opcją Do porownywarek</b></em></label> <br />
                                <input type="radio" value="2" name="typ" id="typ_kategorie" onclick="typ_export(2)" <?php echo (($info['comparisons_export_type'] == '2') ? 'checked="checked"' : ''); ?> /> <label class="OpisFor" for="typ_kategorie">tylko zaznaczone kategorie<em class="TipIkona"><b>Typ eksportu - tylko zaznaczone kategorie - eksport produktów z wybranych kategorii</b></em></label> <br />
                                <input type="radio" value="3" name="typ" id="typ_producenci" onclick="typ_export(3)" <?php echo (($info['comparisons_export_type'] == '3') ? 'checked="checked"' : ''); ?> /> <label class="OpisFor" for="typ_producenci">tylko wybrani producenci<em class="TipIkona"><b>Typ eksportu - tylko wybrani producenci - eksport produktów z wybranych producentów</b></em></label> <br />
                            </td>
                        </tr>
                      </table> 
                      
                  </div>
                  
                  <div id="drzewo_kategorii" <?php echo ( $info['comparisons_export_type'] != '2' ? 'style="display:none;margin:10px;width:95%;max-width:650px"' : 'style="margin:10px;width:95%;max-width:650px"' ); ?> >
                  
                    <p class="NaglowekKategorie">Kategorie eksportowane do porównywarki</p>                           

                    <?php
                    $przypisane_kategorie = explode(',', (string)$info['comparisons_categories']);
                    //
                    if ( ( count($przypisane_kategorie) > 10 || KATEGORIE_LISTING_EDYCJA == 'wszystkie' ) && $info['comparisons_export_type'] == '2' ) {
                        //
                        echo '<ul id="drzewoKategorii">';
                        foreach(Kategorie::DrzewoKategoriiZarzadzanie() as $IdKategorii => $Tablica) {
                            //
                            echo Kategorie::WyswietlDrzewoKategoriiCheckbox($IdKategorii, $Tablica, $przypisane_kategorie);
                            //
                        }    
                        echo '</ul>';
                        //
                    } else {
                        //
                        echo '<table class="pkc">';
                        //
                        $tablica_kat = Kategorie::DrzewoKategorii('0', '', '', '', false, true);
                        for ($w = 0, $c = count($tablica_kat); $w < $c; $w++) {
                            $podkategorie = false;
                            if ($tablica_kat[$w]['podkategorie'] == 'true') { $podkategorie = true; }
                            //
                            $check = '';
                            if ( in_array((string)$tablica_kat[$w]['id'], $przypisane_kategorie) ) {
                                $check = 'checked="checked"';
                            }
                            //  
                            echo '<tr>
                                    <td class="lfp"><input type="checkbox" value="'.$tablica_kat[$w]['id'].'" name="id_kat[]" id="kat_nr_'.$tablica_kat[$w]['id'].'" '.$check.' /> <label class="OpisFor" for="kat_nr_'.$tablica_kat[$w]['id'].'">'.$tablica_kat[$w]['text'].(($tablica_kat[$w]['status'] == 0) ? '<em class="TipChmurka"><b>Kategoria jest nieaktywna</b><span class="wylKat"></span></em>' : '').'</label></td>
                                    <td class="rgp" '.(($podkategorie) ? 'id="img_'.$tablica_kat[$w]['id'].'"' : '').'>'.(($podkategorie) ? '<img src="obrazki/rozwin.png" alt="Rozwiń" onclick="podkat(\''.$tablica_kat[$w]['id'].'\',\'\',\'checkbox\')" />' : '').'</td>
                                  </tr>
                                  '.(($podkategorie) ? '<tr><td colspan="2"><div id="p_'.$tablica_kat[$w]['id'].'"></div></td></tr>' : '').'';
                                  
                            unset($check);
                        }
                        echo '</table>';
                        unset($tablica_kat,$podkategorie);

                        if ( count($przypisane_kategorie) > 0 ) {

                          foreach ( $przypisane_kategorie as $val ) {
                              
                              $sciezka = Kategorie::SciezkaKategoriiId($val, 'categories');
                              $cSciezka = explode("_", (string)$sciezka);                    
                              if (count($cSciezka) > 1) {
                                  //
                                  $ostatnie = strRpos($sciezka,'_');
                                  $analiza_sciezki = str_replace("_", ",", substr((string)$sciezka, 0, (int)$ostatnie));
                                  ?>
                                  
                                  <script>          
                                  podkat('<?php echo $analiza_sciezki; ?>', '<?php echo $cSciezka[count($cSciezka)-1]; ?>','checkbox','<?php echo $info['comparisons_categories']; ?>');
                                  </script>
                                  
                              <?php
                              unset($sciezka,$cSciezka);
                              }
                            
                          } 

                        }
                    }
                    unset($KategorieRabaty, $przypisani_producenci);
                    ?>
                  </div>
                  
                  <div id="drzewo" <?php echo ( $info['comparisons_export_type'] != '3' ? 'style="display:none;margin:10px;width:95%;max-width:650px"' : 'style="margin:10px;width:95%;max-width:650px"' ); ?> >

                      <p class="NaglowekKategorie">Producenci eksportowani do porównywarki</p>

                      <?php
                      $przypisani_producenci = explode(',', (string)$info['comparisons_manufacturers']);
                      //
                      $tablica_prod = Funkcje::TablicaProducenci();
                      //
                      if (count($tablica_prod) > 0) {
                          //
                          echo '<table class="pkc">';
                          //
                          for ($b = 0, $c = count($tablica_prod); $b < $c; $b++) {
                              //
                              $check = '';
                              if ( in_array((string)$tablica_prod[$b]['id'], $przypisani_producenci) ) {
                                  $check = 'checked="checked"';
                              }                              
                              //
                              echo '<tr>                                
                                      <td class="lfp">
                                          <input type="checkbox" value="'.$tablica_prod[$b]['id'].'" name="id_producent[]" id="id_producent_'.$tablica_prod[$b]['id'].'" ' . $check . ' /> <label class="OpisFor" for="id_producent_' . $tablica_prod[$b]['id'].'">' . $tablica_prod[$b]['text'] . '</label>
                                      </td>                                
                                    </tr>';
                                    
                              unset($chec);
                          }
                          echo '</table>';
                          //
                      }
                      unset($tablica_prod, $przypisani_producenci);
                      ?> 

                  </div>      

                  <?php if ( strpos((string)$info['comparisons_plugin'], 'facebook_reklamy') === false ) { ?>

                  <p>
                    <label>Format pliku:</label>
                    <input type="radio" value="0" name="format" id="format_standardowy" onclick="$('#pola').slideUp();$('#cechy').slideUp()" <?php echo ($info['comparisons_extra_fields'] == '' && $info['comparisons_attributes'] == '' ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="format_standardowy">standardowy<em class="TipIkona"><b>Format eksportowanego pliku - standardowy plik porównywarki</b></em></label>
                    <input type="radio" value="1" name="format" id="format_wlasny" onclick="$('#pola').slideDown();$('#cechy').slideDown()" <?php echo ($info['comparisons_extra_fields'] != '' || $info['comparisons_attributes'] != '' ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="format_wlasny">własny<em class="TipIkona"><b>Format eksportowanego pliku - uwzględnia dodatkowe pola i cechy do produktów</b></em></label>
                  </p> 

                  <div id="pola" class="DodatkowePola" <?php echo ( ($info['comparisons_extra_fields'] == '' && $info['comparisons_attributes'] == '') ? 'style="display:none;margin:10px;width:95%;max-width:650px"' : 'style="margin:10px;width:95%;max-width:650px"' ); ?> >
                  
                    <p class="NaglowekKategorie">Dodatkowe pola eksportowane do porównywarki</p>                           

                    <?php
                    $zapytanie_pola = "SELECT * FROM products_extra_fields ORDER BY products_extra_fields_order";
                    $sql_pola = $db->open_query($zapytanie_pola);
                    
                    if ( $db->ile_rekordow($sql_pola) > 0 )  {

                        $tablica_pol = array();
                        
                        if ( $info['comparisons_extra_fields'] != '' ) {
                          $dodatkowe_pola_tablica = explode(',', (string)$info['comparisons_extra_fields']);
                          for ( $i = 0, $c = count($dodatkowe_pola_tablica); $i < $c; $i++ ) {
                            $podtablica = explode(':', (string)$dodatkowe_pola_tablica[$i]);
                            $tablica_pol[$podtablica['0']] = $podtablica['1'];
                          }
                        }
                        
                        echo '<table class="pkc">';
                        
                        while ( $info_pola = $sql_pola->fetch_assoc() ) {
                          $check = '';
                          $wartosc = '';
                          if ( isset($tablica_pol[$info_pola['products_extra_fields_id']]) ) {
                            $check = 'checked="checked"';
                            $wartosc = $tablica_pol[$info_pola['products_extra_fields_id']];
                          }
                          echo '<tr>
                                  <td><input type="checkbox" value="'.$info_pola['products_extra_fields_id'].'" name="id_extra_field['.$info_pola['products_extra_fields_id'].']" id="pole_'.$info_pola['products_extra_fields_id'].'" '.$check.' /> <label class="OpisFor" for="pole_'.$info_pola['products_extra_fields_id'].'">'.$info_pola['products_extra_fields_name'].'</label></td><td style="text-align:right;white-space:nowrap"><input type="text" name="desc_extra_field['.$info_pola['products_extra_fields_id'].']"  value="'.$wartosc.'" size="50" /><em class="TipIkona"><b>Nazwa pola w generowanym pliku XML - powinna być zgodna z dokumentacją integracji danej porównywarki.<br />W razie wątpliwości należy się zwrócić do obsługi serwisu porównywarki</b></em></td>
                                </tr>';
                        }
                        
                        echo '</table>';
                        
                    } else {
                      
                      echo '<div class="ostrzezenie" style="margin-left:15px">Nie ma zdefiniowanych dodatkowych pól do produktów - nie można dodać własnych pól do pliku XML</div>';
                      
                    }
                    $db->close_query($sql_pola);
                    unset($zapytanie_pola, $info_pola);

                    ?>
                  </div>
                  
                  <div id="cechy" class="DodatkowePola" <?php echo ( ($info['comparisons_extra_fields'] == '' && $info['comparisons_attributes'] == '') ? 'style="display:none;margin:10px;width:95%;max-width:650px"' : 'style="margin:10px;width:95%;max-width:650px"' ); ?> >
                  
                    <p class="NaglowekKategorie">Cechy produktów eksportowane do porównywarki</p>                           

                    <?php
                    $zapytanie_cechy = "SELECT * FROM products_options where language_id = '" . $_SESSION['domyslny_jezyk']['id'] . "' ORDER BY products_options_sort_order";
                    $sql_cechy = $db->open_query($zapytanie_cechy);
                    
                    if ( $db->ile_rekordow($sql_cechy) > 0 )  {

                        $tablica_cech = array();
                        
                        if ( $info['comparisons_attributes'] != '' ) {
                          $cechy_tablica = explode(',', (string)$info['comparisons_attributes']);
                          for ( $i = 0, $c = count($cechy_tablica); $i < $c; $i++ ) {
                            $podtablica = explode(':', (string)$cechy_tablica[$i]);
                            $tablica_cech[$podtablica['0']] = $podtablica['1'];
                          }
                        }
                        
                        echo '<table class="pkc">';
                        
                        while ( $info_cechy = $sql_cechy->fetch_assoc() ) {
                          $check = '';
                          $wartosc = '';
                          if ( isset($tablica_cech[$info_cechy['products_options_id']]) ) {
                            $check = 'checked="checked"';
                            $wartosc = $tablica_cech[$info_cechy['products_options_id']];
                          }
                          echo '<tr>
                                  <td><input type="checkbox" value="'.$info_cechy['products_options_id'].'" name="id_attributes['.$info_cechy['products_options_id'].']" id="cecha_'.$info_cechy['products_options_id'].'" '.$check.' /> <label class="OpisFor" for="cecha_'.$info_cechy['products_options_id'].'">'.$info_cechy['products_options_name'].'</label></td><td style="text-align:right;white-space:nowrap"><input type="text" name="desc_attributes['.$info_cechy['products_options_id'].']"  value="'.$wartosc.'" size="50" /><em class="TipIkona"><b>Nazwa cechy w generowanym pliku XML - powinna być zgodna z dokumentacją integracji danej porównywarki.<br />W razie wątpliwości należy się zwrócić do obsługi serwisu porównywarki</b></em></td>
                                </tr>';
                        }
                        
                        echo '</table>';
                        
                    } else {
                      
                      echo '<div class="ostrzezenie" style="margin-left:15px">Nie ma zdefiniowanych cechy do produktów - nie można dodać własnych cech do pliku XML</div>';
                      
                    }
                    $db->close_query($sql_cechy);
                    unset($zapytanie_cechy, $info_cechy);

                    ?>
                  </div>  

                  <?php } else { ?>
                  
                  <input type="hidden" value="0" name="format" />
                  
                  <?php } ?>
                  
                  <?php
                  // mapowanie kategorii Google Shopping
                  if ( strpos((string)$info['comparisons_plugin'], 'googleshopping') > -1 || strpos((string)$info['comparisons_plugin'], 'facebook_reklamy') > -1 ) {
                      ?>
                      <script>
                      function mapuj_kategorie(id_kategorii) {
                          $(function() {
                              var id = id_kategorii;
                              $.colorbox({
                                  title: "Przypisanie kategorii Google zakupy do kategorii sklepu",
                                  ajax:true,
                                  data: true,
                                  scrolling: false,
                                  overlayClose: false,
                                  initialWidth:50,
                                  initialHeight:50,
                                  href:"ajax/google_mapowanie_kategorii.php?id="+id+'&jezyk=<?php echo $info['comparisons_language_id']; ?>',
                              });

                          });
                      }
                      function mapuj_kategorie_usun(id) {
                          var id = id;
                          $('#ekr_preloader').css('display','block');
                          $.post('ajax/google_zapisanie_kategorii.php', { akcja: "usun", id: id, jezyk: '<?php echo $info['comparisons_language_id']; ?>' }, 
                              function(data) {}
                          );
                          $("#wartosc_" + id).html('');
                          $('#ekr_preloader').fadeOut();
                          $("#usun_" + id).hide();
                      }
                      </script>

                      <div style="padding:3px 10px 4px">

                        <div class="poleForm">
                        
                            <?php if ( strpos((string)$info['comparisons_plugin'], 'googleshopping') > -1 ) { ?>

                            <div class="naglowek">
                                Kategorie sklepu -> Google shopping
                            </div>
                            
                            <div class="ostrzezenie" style="margin:10px">Żeby sklep wyeksportował dane do porównywarki MUSZĄ być powiązane kategorie sklepu z kategoriami porównywarki</div>
                            
                            <?php } else { ?>
                            
                            <div class="naglowek">
                                Połączenie kategorii sklepu z kategoriami Google
                            </div>
                            
                            <div class="ostrzezenie" style="margin:10px">W przypadku reklam dynamicznych Facebook konieczne jest połączenie kategorii sklepu z kategoriami Google (z systematyki produktu Google). Produkty bez przypisanych kategorii będą wyeksportowane do pliku CSV - mogą zostać jednak odrzucone prze FB.</div>                            
                            
                            <?php } ?>

                            <div id="Mapowanie">
                                <?php
                                echo '<table>';
                                echo Porownywarki::TablicaNaWierszeGoogle($tree);
                                echo '</table>';
                                ?>
                            </div>

                        </div>
                      </div>

                      <?php
                  }
                  ?>

                  </div>

                </div>
                
                <div class="przyciski_dolne">
                    <input type="submit" class="przyciskNon" value="Zapisz dane" />
                    <button type="button" class="przyciskNon" onclick="cofnij('porownywarki','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','porownywarki');">Powrót</button> 
                </div>            
            
            <?php 
            
            $db->close_query($sql);
            unset($zapytanie, $info);
                    
            } else {
            
                echo '<div class="naglowek">Edycja danych</div><div class="pozycja_edytowana">Brak danych do wyświetlenia</div>';
            
            }
            ?>
        </div>
      </form>
    </div>

    
    <?php
    include('stopka.inc.php');    
    
} 


?>
