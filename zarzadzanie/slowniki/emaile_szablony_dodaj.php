<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {


        if ($_POST["domyslny"] == '1') {
            $pola = array(array('template_default','0'));
            $db->update_query('email_templates' , $pola);	
            unset($pola);
            //
        }
        //
        $pola = array(
                array('template_name',$filtr->process($_POST["nazwa"])),
                array('template_default',$filtr->process($_POST["domyslny"])));
        //			
        $db->insert_query('email_templates' , $pola);
        $id_dodanej_pozycji = $db->last_id_query();
        unset($pola);       

        //
        $ile_jezykow = Funkcje::TablicaJezykow();
        for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
            //
            if (!empty($_POST['edytor_'.$w])) {
                $pola = array(
                        array('template_id',(int)$id_dodanej_pozycji),
                        array('description',$filtr->process($_POST['edytor_'.$w])),
                        array('language_id',$ile_jezykow[$w]['id'])
                 );
            } else {
                $pola = array(
                        array('template_id',(int)$id_dodanej_pozycji),
                        array('description',$filtr->process($_POST['edytor_0'])),
                        array('language_id',$ile_jezykow[$w]['id'])
                 );
            }
            $sql = $db->insert_query('email_templates_description' , $pola);
            unset($pola);
        }        
        //
        if (isset($id_dodanej_pozycji) && $id_dodanej_pozycji > 0) {
            Funkcje::PrzekierowanieURL('emaile_szablony.php?id_poz='.$id_dodanej_pozycji);
        } else {
            Funkcje::PrzekierowanieURL('emaile_szablony.php');
        }
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Dodawanie danych</div>
    <div id="cont">

          <script>
          $(document).ready(function() {
            $("#slownikForm").validate({
              rules: {
                nazwa: {
                  required: true
                }              
              }
            });
          });
          </script>     

          <form action="slowniki/emaile_szablony_dodaj.php" method="post" id="slownikForm" class="cmxform">          

              <div class="poleForm">
                <div class="naglowek">Dodawanie danych</div>

                  <div class="pozycja_edytowana">
                  
                      <input type="hidden" name="akcja" value="zapisz" />
                      

                      <?php $ile_jezykow = Funkcje::TablicaJezykow(); ?>

                          <div class="info_tab">
                          <?php
                          for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                              echo '<span id="link_'.$w.'" class="a_href_info_tab" onclick="gold_tabs(\''.$w.'\',\'edytor_\',400, \'fullpage\')">'.$ile_jezykow[$w]['text'].'</span>';
                          }                    
                          ?>                   
                          </div>
                          
                          <div style="clear:both"></div>
                          
                          <div class="info_tab_content">
                              <?php
                              for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                              
                                  ?>
                                  
                                  <div id="info_tab_id_<?php echo $w; ?>" style="display:none;">
                                  
                                      <div class="edytor">
                                        <textarea cols="50" rows="30" id="edytor_<?php echo $w; ?>" name="edytor_<?php echo $w; ?>"></textarea>
                                      </div>   
                                                  
                                  </div>
                                  <?php                    
                              }                    
                              ?>                      
                          </div>
                          
                          <script>
                          gold_tabs('0','edytor_', 400, 'fullpage');
                          </script> 

                          <p>
                            <label class="required" for="nazwa">Nazwa szablonu:</label>
                            <input type="text" name="nazwa" size="60" value="" id="nazwa" />
                          </p>

                          <p>
                            <label>Czy szablon jest domyślnym:</label>
                            <input type="radio" value="0" name="domyslny" id="domyslny_nie" checked="checked" /><label class="OpisFor" for="domyslny_nie">nie<em class="TipIkona"><b>W oparciu o szablon domyślny będą generowne wszystkiem maile wysyłane ze sklepu</b></em></label>
                            <input type="radio" value="1" name="domyslny" id="domyslny_tak" /><label class="OpisFor" for="domyslny_tak">tak<em class="TipIkona"><b>W oparciu o szablon domyślny będą generowne wszystkiem maile wysyłane ze sklepu</b></em></label>
                          </p>

                  </div>

                  <div class="przyciski_dolne">
                    <input type="submit" class="przyciskNon" value="Zapisz dane" />
                    <button type="button" class="przyciskNon" onclick="cofnij('emaile_szablony','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','slowniki');">Powrót</button>   
                  </div>            

              </div>                      
          </form>


          <div class="objasnienia">
              <div class="objasnieniaTytul">Znaczniki, które możesz użyć w tym e-mailu:</div>
              <div class="objasnieniaTresc">
              
                <div style="padding-bottom:10px;font-weight:bold;">Treść wiadomości</div>
                <ul class="mcol">
                  <li><b>{CONTENT}</b> - Zawartość maila</li>
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
              </div>
          </div>

    </div>    
    
    <?php
    include('stopka.inc.php');

}
