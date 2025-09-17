<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        if ($_POST["domyslny"] == '1') {
            $pola = array(array('languages_default','0'));
            $db->update_query('languages' , $pola);	
            //
            $_POST["status"] = '1';

            unset($_SESSION['domyslny_jezyk']);

            $jezyk = array('id' => (int)$_POST["id"],
                           'nazwa' => $filtr->process($_POST["nazwa"]),
                           'kod' => $filtr->process($_POST["kod"]),
                           'waluta' => $filtr->process($_POST["domyslna_waluta"]));

            $_SESSION['domyslny_jezyk'] = $jezyk;

            unset($_SESSION['domyslna_waluta']);

            $zapytanie = "select * from currencies where currencies_id = '" . $filtr->process($_POST["domyslna_waluta"]) . "'";
            $sql = $db->open_query($zapytanie);
            $wynik = $sql->fetch_assoc();
            //
            $waluta = array('id' => $wynik['currencies_id'],
                                     'nazwa' => $wynik['title'],
                                     'kod' => $wynik['code'],
                                     'symbol' => $wynik['symbol'],
                                     'separator' => $wynik['decimal_point'],
                                     'przelicznik' => (( $wynik['value'] == 0 ) ? 1 : $wynik['value']),
                                     'marza' => $wynik['currencies_marza']);

            $_SESSION['domyslna_waluta'] = $waluta;
            $db->close_query($sql);
            unset($zapytanie, $wynik);
            //
        }
        //
        $pola = array(
                array('name',$filtr->process($_POST["nazwa"])),
                array('code',$filtr->process($_POST["kod"])),
                array('image',$filtr->process($_POST["zdjecie"])),
                array('sort_order',$filtr->process($_POST["sort"])),
                array('currencies_default',$filtr->process($_POST["domyslna_waluta"])),
                array('languages_default',$filtr->process($_POST["domyslny"])),
                array('status',$filtr->process($_POST["status"]))
                );
        //			
        $db->update_query('languages' , $pola, " languages_id = '".(int)$_POST["id"]."'");	
        unset($pola);       
        //
        Funkcje::PrzekierowanieURL('jezyki.php?id_poz='.(int)$_POST["id"]);
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Edycja pozycji</div>
    <div id="cont">
          
          <?php
          $tabAktualne = array();
          //
          $zapytanie = "select languages_id, name, code from languages where languages_id != '" . (int)$_GET['id_poz'] . "'";
          $sql = $db->open_query($zapytanie);         
          while ($wynik = $sql->fetch_assoc()) {
              $tabAktualne[] = $wynik['code'];
          }
          $db->close_query($sql);
          unset($wynik,$zapytanie);          
          ?>
          
          <script>
          jQuery.validator.addMethod("notEqual", function(value, element, params) {
            pod = params.split('|');
            wynik = true;
            for (x = 0; x < pod.length; x++) {
                 if ( value == pod[x] ) {
                      wynik = false;
                 }
            }
            return wynik;
          }, "Taka wartość jest już używana."); 
          
          $(document).ready(function() {
            $("#slownikForm").validate({
              rules: {
                nazwa: {
                  required: true
                },
                kod: {
                  required: true,
                  notEqual: "<?php echo implode('|', (array)$tabAktualne); ?>"
                }               
              },
              messages: {
                nazwa: {
                  required: "Pole jest wymagane."
                },
                kod: {
                  required: "Pole jest wymagane."
                }                  
              }
            });
          });
          </script>   

          <?php
          unset($tabAktualne);
          ?>

          <form action="slowniki/jezyki_edytuj.php" method="post" id="slownikForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Edycja danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select * from languages where languages_id = '" . (int)$_GET['id_poz'] . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
            
                $info = $sql->fetch_assoc();
                ?>
            
                <div class="pozycja_edytowana">
                
                    <div class="info_content">

                    <input type="hidden" name="akcja" value="zapisz" />
                
                    <input type="hidden" name="id" value="<?php echo (int)$_GET['id_poz']; ?>" />
                    
                    <p>
                      <label class="required" for="nazwa">Nazwa:</label>
                      <?php if ( $info['languages_id'] != '1' ) { ?>
                         <input type="text" name="nazwa" id="nazwa" value="<?php echo $info['name']; ?>" size="35" />
                        <?php } else { ?>
                         <input type="text" name="nazwa_id" id="nazwa" value="<?php echo $info['name']; ?>" size="35" disabled="disabled" />
                         <input type="hidden" name="nazwa" value="<?php echo $info['name']; ?>" />
                      <?php } ?>
                    </p>
                    
                    <p>
                      <label class="required" for="kod">Kod:</label>
                      <?php if ( $info['languages_id'] != '1' ) { ?>
                         <input type="text" name="kod" id="kod" value="<?php echo $info['code']; ?>" size="5" />
                        <?php } else { ?>
                         <input type="text" name="kod_id" id="kod" value="<?php echo $info['code']; ?>" size="5"  disabled="disabled" />
                         <input type="hidden" name="kod" value="<?php echo $info['code']; ?>" />
                      <?php } ?>
                    </p>                    

                    <p>
                      <label for="foto">Ikona:</label>           
                      <input type="text" name="zdjecie" size="95" value="<?php echo $info['image']; ?>" class="obrazek" ondblclick="openFileBrowser('foto','','<?php echo KATALOG_ZDJEC; ?>')" id="foto" autocomplete="off" /><em class="TipIkona"><b>Kliknij dwukrotnie w pole obok żeby otworzyć okno przeglądarki zdjęć</b></em>
                      <span class="PrzegladarkaZdjec TipChmurka" onclick="openFileBrowser('foto','','<?php echo KATALOG_ZDJEC; ?>')"><b>Kliknij żeby otworzyć okno przeglądarki zdjęć</b></span>
                    </p>      

                    <div id="divfoto" style="padding-left:10px;display:none">
                      <label>Ikona:</label>
                      <span id="fofoto">
                          <span class="zdjecie_tbl">
                              <img src="obrazki/_loader_small.gif" alt="" />
                          </span>
                      </span> 
                      
                      <?php if (!empty($info['image'])) { ?>
                      <script>          
                      pokaz_obrazek_ajax('foto', '<?php echo $info['image']; ?>')
                      </script>
                      <?php } ?>  
                        
                    </div>                

                    <p>
                      <label for="sort">Kolejność wyświetlania:</label>
                      <input type="text" name="sort" id="sort" value="<?php echo $info['sort_order']; ?>" size="5" />
                    </p>           

                    <p>
                      <label for="domyslna_waluta">Domyślna waluta języka:</label>
                      <?php
                      $tablica = array();
                      $zap = "select * from currencies";
                      $sqls = $db->open_query($zap);
                      
                      while ($nazwa = $sqls->fetch_assoc()) {
                          $tablica[] = array('id' => $nazwa['currencies_id'], 'text' => $nazwa['title']);
                      }

                      echo Funkcje::RozwijaneMenu('domyslna_waluta', $tablica, $info['currencies_default'], 'id="domyslna_waluta"');
                      unset($tablica);
                      ?>                         
                    </p>  

                    <?php if ($info['languages_default'] == '0') { ?>                    
                   
                    <p>
                      <label>Czy język jest domyślnym:</label>
                      <input type="radio" value="0" name="domyslny" id="domyslny_nie" checked="checked" /><label class="OpisFor" for="domyslny_nie">nie</label>
                      <input type="radio" value="1" name="domyslny" id="domyslny_tak" /><label class="OpisFor" for="domyslny_tak">tak</label>                     
                    </p>

                    <p>
                      <label>Status:</label>
                      <input type="radio" value="1" name="status" id="status_tak" <?php echo (($info['status'] == '1') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="status_tak">włączony</label>
                      <input type="radio" value="0" name="status" id="status_nie" <?php echo (($info['status'] == '0') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="status_nie">wyłączony</label>       
                    </p>                     
                    
                    <?php } else { ?>
                    
                    <input type="hidden" name="domyslny" value="1" />
                    
                    <input type="hidden" name="status" value="1" />
                    
                    <?php } ?>  

                    <p style="padding-top:10px">
                        <span style="color:#ff0000">UWAGA !! Zmiana języka na domyślny lub zamiana domyślnej waluty dla języka będzie wymagała aktualizacji kursów walut w menu Słowniki / Waluty</span>
                    </p>                    
                    
                    </div>

                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Zapisz dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('jezyki','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','slowniki');">Powrót</button>           
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

}