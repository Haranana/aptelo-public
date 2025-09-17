<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        // kasuje rekordy w tablicy
        $db->delete_query('products_warranty_description' , " products_warranty_id = '".(int)$_POST["id"]."'");        
        
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
                    array('products_warranty_id',(int)$_POST["id"]),
                    array('products_warranty_name',$filtr->process($_POST['nazwa_'.$w])),
                    array('language_id',(int)$ile_jezykow[$w]['id']),
                    array('products_warranty_link',$filtr->process($_POST['link_'.$w]))); 
            $db->insert_query('products_warranty_description' , $pola);
            unset($pola);
            //           
        }              
        //
        Funkcje::PrzekierowanieURL('gwarancje.php?id_poz='.(int)$_POST["id"]);
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Edycja pozycji</div>
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

          <form action="slowniki/gwarancje_edytuj.php" method="post" id="slownikForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Edycja danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select * from products_warranty where products_warranty_id = '" . (int)$_GET['id_poz'] . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
                ?>
            
                <div class="pozycja_edytowana">
                
                    <input type="hidden" name="akcja" value="zapisz" />
                
                    <input type="hidden" name="id" value="<?php echo (int)$_GET['id_poz']; ?>" />
                    
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
                        
                            // pobieranie danych jezykowych
                            $zapytanie_jezyk = "select distinct * from products_warranty_description where products_warranty_id = '".(int)$_GET['id_poz']."' and language_id = '" .$ile_jezykow[$w]['id']."'";
                            $sqls = $db->open_query($zapytanie_jezyk);
                            $nazwa = $sqls->fetch_assoc();   
                            
                            ?>
                            
                            <div id="info_tab_id_<?php echo $w; ?>" style="display:none;">
                            
                                <p>
                                   <?php if ($w == '0') { ?>
                                    <label class="required" for="nazwa_0">Nazwa:</label>
                                    <input type="text" name="nazwa_<?php echo $w; ?>" size="45" value="<?php echo (isset($nazwa['products_warranty_name']) ? $nazwa['products_warranty_name'] : ''); ?>" id="nazwa_0" />
                                   <?php } else { ?>
                                    <label for="nazwa_<?php echo $w; ?>">Nazwa:</label>   
                                    <input type="text" name="nazwa_<?php echo $w; ?>" id="nazwa_<?php echo $w; ?>" size="45" value="<?php echo (isset($nazwa['products_warranty_name']) ? $nazwa['products_warranty_name'] : ''); ?>" />
                                   <?php } ?>
                                </p>

                                <p>
                                  <label for="url_<?php echo $w; ?>">Adres URL lub nazwa pliku:</label>
                                  <input type="text" name="link_<?php echo $w; ?>" id="url_<?php echo $w; ?>" ondblclick="openFileAllBrowser('url_<?php echo $w; ?>')" value="<?php echo (isset($nazwa['products_warranty_link']) ? $nazwa['products_warranty_link'] : ''); ?>" size="75" autocomplete="off" /><em class="TipIkona"><b>Wpisz adres www jeżeli pole ma być linkiem lub kliknij dwukrotnie w to pole żeby otworzyć okno przeglądarki plików i wskazać plik do którego ma prowadzić link</b></em>
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
                                  $sqlu = $db->open_query($zapytanie_tmp);
                                  //
                                  $tablica_info = '';
                                  //
                                  $tablica = array();
                                  $tablica[] = array('id' => 0, 'text' => '--- wybierz ---');
                                  while ($infs = $sqlu->fetch_assoc()) { 
                                      $tablica[] = array('id' => $infs['pages_id'], 'text' => $infs['pages_title']);
                                      $tablica_info .= 'tablica_info[' . $infs['pages_id'] . '] = "' . str_replace(ADRES_URL_SKLEPU . '/', '', Seo::link_SEO( $infs['pages_title'], $infs['pages_id'], 'strona_informacyjna', '', false )) . '";' . "\r\n";
                                  }
                                  $db->close_query($sqlu); 
                                  unset($zapytanie_tmp, $infs);    
                                  //                          
                                  echo Funkcje::RozwijaneMenu('stronainfo', $tablica, '', ' onchange="wstaw_link(this.value,\'' . $w . '\')" id="stronainfo"'); 
                                  unset($tablica);
                                  ?>
                                </p>                                
                                            
                            </div>
                            <?php                    
                            $db->close_query($sqls);
                            unset($zapytanie_jezyk);
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

            <?php
            
            $db->close_query($sql);          
            
            } else {
            
                echo '<div class="pozycja_edytowana">Brak danych do wyświetlenia</div>';
            
            }
            ?>

          </div>                      
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}
