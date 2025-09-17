<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        $pola = array();
        //	
        $db->insert_query('products_warranty' , $pola);	
        $id_dodanej_pozycji = $db->last_id_query();
        
        unset($pola);
        
        $ile_jezykow = Funkcje::TablicaJezykow();
        for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
            //
            // jezeli nazwa w innym jezyku nie jest wypelniona
            if ( $w > 0 ) {
                if (empty($_POST['nazwa_'.$w])) {
                    $_POST['nazwa_'.$w] = $_POST['nazwa_0'];
                }
            }
            //      
            $pola = array(
                    array('products_warranty_id',$id_dodanej_pozycji),
                    array('language_id',$ile_jezykow[$w]['id']),
                    array('products_warranty_name',$filtr->process($_POST['nazwa_'.$w])),
                    array('products_warranty_link',$filtr->process($_POST['link_'.$w])));           
            $sql = $db->insert_query('products_warranty_description' , $pola);
            unset($pola);
        }        
        //
        if (isset($id_dodanej_pozycji) && $id_dodanej_pozycji > 0) {
            Funkcje::PrzekierowanieURL('gwarancje.php?id_poz='.$id_dodanej_pozycji);
        } else {
            Funkcje::PrzekierowanieURL('gwarancje.php');
        }
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Dodawanie pozycji</div>
    <div id="cont">
          
          <script>
          $(document).ready(function() {
            $("#slownikForm").validate({
              rules: {
                nazwa_0: {
                  required: true
                }                
              },
              messages: {
                nazwa_0: {
                  required: "Pole jest wymagane."
                }               
              }
            });
          });
          
          function wstaw_link(id, jezyk) {
            $('#url_' + jezyk).val( tablica_info[id] );              
          }           
          </script>     

          <form action="slowniki/gwarancje_dodaj.php" method="post" id="slownikForm" class="cmxform">          

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
                                <label class="required" for="nazwa_0">Nazwa:</label>
                                <input type="text" name="nazwa_<?php echo $w; ?>" size="55" value="" id="nazwa_0" />
                               <?php } else { ?>
                                <label for="nazwa_<?php echo $w; ?>">Nazwa:</label>   
                                <input type="text" name="nazwa_<?php echo $w; ?>" size="55" value="" id="nazwa_<?php echo $w; ?>" />
                               <?php } ?>
                            </p>

                            <p>
                              <label for="url_<?php echo $w; ?>">Adres URL lub nazwa pliku:</label>
                              <input type="text" name="link_<?php echo $w; ?>" id="url_<?php echo $w; ?>" ondblclick="openFileAllBrowser('url_<?php echo $w; ?>')" value="" size="75" autocomplete="off" /><em class="TipIkona"><b>Wpisz adres www jeżeli pole ma być linkiem lub kliknij dwukrotnie w to pole żeby otworzyć okno przeglądarki plików i wskazać plik do którego ma prowadzić link</b></em>
                            </p>   
                            
                            <span class="maleInfo" style="margin-left:10px">Jeżeli link ma prowadzić do strony spoza sklepu musi zaczynać się od http:// ...</span>

                            <br />
                            
                            <hr style="color:#82b4cd;border-top: 1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:98%;" />   
                            
                            <br />
                            
                            <p>
                              <label for="stronainfo">Wstaw adres URL do strony informacyjnej:</label>
                              <?php
                              // pobieranie danych o stronach informacyjnych
                              $zapytanie_tmp = "select distinct * from pages p, pages_description pd where p.pages_id = pd.pages_id and pd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."' and p.link = '' and p.pages_modul = '0'";
                              $sqls = $db->open_query($zapytanie_tmp);
                              //
                              $tablica_info = '';
                              //
                              $tablica = array();
                              $tablica[] = array('id' => 0, 'text' => '--- wybierz ---');
                              while ($infs = $sqls->fetch_assoc()) { 
                                  $tablica[] = array('id' => $infs['pages_id'], 'text' => $infs['pages_title']);
                                  $tablica_info .= 'tablica_info[' . $infs['pages_id'] . '] = "' . str_replace(ADRES_URL_SKLEPU . '/', '', Seo::link_SEO( $infs['pages_title'], $infs['pages_id'], 'strona_informacyjna', '', false )) . '";' . "\r\n";
                              }
                              $db->close_query($sqls); 
                              unset($zapytanie_tmp, $infs);    
                              //                          
                              echo Funkcje::RozwijaneMenu('stronainfo', $tablica, '', ' onchange="wstaw_link(this.value,\'' . $w . '\')" id="stronainfo"'); 
                              unset($tablica);
                              ?>
                            </p>
           
                        </div>
                        <?php                    
                    }                    
                    ?>                      
                </div>
                
                <script>
                gold_tabs('0');
                var tablica_info = new Array();
                <?php echo $tablica_info; ?>  
                </script>                    
                
            </div>

            <div class="przyciski_dolne">
              <input type="submit" class="przyciskNon" value="Zapisz dane" />
              <button type="button" class="przyciskNon" onclick="cofnij('gwarancje','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','slowniki');">Powrót</button>   
            </div>            

          </div>                      
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}
