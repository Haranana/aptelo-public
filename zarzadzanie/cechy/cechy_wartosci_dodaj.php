<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        // okreslanie kolejnego nr ID
        $zapytanie_cechy = "select max(products_options_values_id) + 1 as next_id from products_options_values";
        $sqls = $db->open_query($zapytanie_cechy);
        $wynik = $sqls->fetch_assoc();    
        $kolejne_id = $wynik['next_id'];
        //
        if ( empty($kolejne_id) ) {
             $kolejne_id = 1;
        }
        //
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
                    array('products_options_values_id',(int)$kolejne_id),
                    array('products_options_values_name',$filtr->process($_POST['nazwa_'.$w])),
                    array('language_id',$ile_jezykow[$w]['id']),
                    array('products_options_values_thumbnail',$filtr->process($_POST['zdjecie'])),
                    array('global_options_values_price_tax',(float)$_POST['cena_brutto']),
                    array('global_price_prefix',(((float)$_POST['cena_brutto'] > 0) ? $filtr->process($_POST['prefix']) : '')),
                    array('global_options_values_weight',(float)$_POST['waga']),
                    array('products_options_values_status',(int)$_POST['status']));
                    
            $sql = $db->insert_query('products_options_values' , $pola);
            unset($pola);
        }    

        $pola = array(
                array('products_options_id',(int)$_POST['id_cechy']),
                array('products_options_values_id',(int)$kolejne_id),
                array('products_options_values_sort_order',(int)$_POST['sort']));
        $sql = $db->insert_query('products_options_values_to_products_options', $pola);
        unset($pola);
        
        Funkcje::PrzekierowanieURL('cechy.php?id_cechy=' . (int)$_POST['id_cechy']);
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Dodawanie pozycji</div>
    <div id="cont">
          
          <script>
          $(document).ready(function() {
            $("#cechyForm").validate({
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
          </script>     

          <form action="cechy/cechy_wartosci_dodaj.php" method="post" id="cechyForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Dodawanie danych</div>
            
            <div class="pozycja_edytowana">
            
                <?php
                if ( isset($_GET['id_cechy']) || (int)$_GET['id_cechy'] > 0 ) { 
                ?>
            
                <input type="hidden" name="akcja" value="zapisz" />
                
                <input type="hidden" name="id_cechy" value="<?php echo (int)$_GET['id_cechy']; ?>" />
                
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
                                <label class="required" for="nazwa_0">Nazwa wartości cechy:</label>
                                <input type="text" name="nazwa_<?php echo $w; ?>" size="45" value="" id="nazwa_0" />
                               <?php } else { ?>
                                <label for="nazwa_<?php echo $w; ?>">Nazwa wartości cechy:</label>   
                                <input type="text" name="nazwa_<?php echo $w; ?>" size="45" value="" id="nazwa_<?php echo $w; ?>" />
                               <?php } ?>
                            </p> 
                                        
                        </div>
                        <?php                    
                    }                    
                    ?>                      
                </div>
                
                <script>
                gold_tabs('0');
                </script>  

                <?php
                // sprawdza czy dana cecha ma obsluge obrazkow do cech
                $zapytanie_cechy_img = "select distinct products_options_id, products_options_images_enabled from products_options where language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."' and products_options_id = '".(int)$_GET['id_cechy']."'";
                $sqls_img = $db->open_query($zapytanie_cechy_img);
                $img = $sqls_img->fetch_assoc(); 

                if ($img['products_options_images_enabled'] == 'true') {
                ?> 
                
                <p>
                  <label for="foto">Ścieżka zdjęcia:</label>           
                  <input type="text" name="zdjecie" size="95" value="" class="obrazek" ondblclick="openFileBrowser('foto','','<?php echo KATALOG_ZDJEC; ?>')" id="foto" autocomplete="off" />                 
                  <em class="TipIkona"><b>Kliknij dwukrotnie w pole obok żeby otworzyć okno przeglądarki zdjęć</b></em>
                  <span class="usun_zdjecie TipChmurka" data-foto="foto"><b>Usuń przypisane zdjęcie</b></span>
                  <span class="PrzegladarkaZdjec TipChmurka" onclick="openFileBrowser('foto','','<?php echo KATALOG_ZDJEC; ?>')"><b>Kliknij żeby otworzyć okno przeglądarki zdjęć</b></span>
                </p>      

                <div id="divfoto" style="padding-left:10px; display:none">
                    <label>Zdjęcie:</label>
                    <span id="fofoto">
                        <span class="zdjecie_tbl">
                            <img src="obrazki/_loader_small.gif" alt="" />
                        </span>
                    </span> 
                </div>                  

                <?php } else { ?>
                
                <input type="hidden" name="zdjecie" value="" /> 
                
                <?php }
                
                $db->close_query($sqls_img);
                unset($zapytanie_cechy_img, $img);

                ?>                

                <p>
                  <label for="sort">Kolejność wyświetlania:</label>
                  <input type="text" class="calkowita" name="sort" size="5" value="" id="sort" />
                </p>  

                <p>
                  <label for="sort">Wartość cechy:</label>
                  <input type="text" class="kropkaPustaZero" name="cena_brutto" size="15" value="" id="cena_brutto" /><em class="TipIkona"><b>Cena brutto / wartość procentowa lub współczynnik do mnożenia - w zależności od ustawienia rodzaju cechy</b></em>
                </p>                  
                
                <p>
                  <label for="sort">Prefix:</label>                
                  <?php
                  $tablica = array(array('id' => '+', 'text' => '+'),
                                   array('id' => '-', 'text' => '-'),
                                   array('id' => '*', 'text' => '*'));                        
                  echo Funkcje::RozwijaneMenu('prefix', $tablica, '+', ''); 
                  unset($tablica, $wybierz);         
                  ?>
                </p>

                <p>
                  <label for="sort">Waga cechy:</label>
                  <input type="text" class="Waga" name="waga" size="10" value="" id="waga" /> kg <em class="TipIkona"><b>Waga w kg np: 1 = 1kg, 0.2 = 200 gram - separatorem dziesiętnym jest kropka</b></em>
                </p> 
                
                <div class="maleInfo" style="margin-left:20px">
                    Pozycje: wartość cechy, prefix oraz waga cechy umożliwiają ustawienie domyślnej wartości która będzie wyświetlana dla produktów z przypisaną daną wartością cechy (tylko jeżeli dany produkt nie będzie miał indywidualnych wartości dla tej cechy).
                </div>                  

                <p>
                  <label>Czy wartość cechy ma być wyświetlana w sklepie:</label>
                  <input type="radio" value="0" name="status" id="status_nie" /> <label class="OpisFor" for="status_nie">nie</label>
                  <input type="radio" value="1" name="status" id="status_tak" checked="checked" /> <label class="OpisFor" for="status_tak">tak</label>
                </p>                 

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Zapisz dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('cechy','<?php echo Funkcje::Zwroc_Get(array('x','y')); ?>','cechy');">Powrót</button>   
                </div> 
            
            <?php
            
            } else {
            
                echo '<div class="pozycja_edytowana">Brak danych do wyświetlenia</div>';
            
            }
            ?>     
   
            </div>            

          </div>                      
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}