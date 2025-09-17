<?php
chdir('../'); 

function removeDir($sciezka) { 
    $katalog = new DirectoryIterator($sciezka); 
    foreach ($katalog as $plik) { 
        if ($plik->isFile() || $plik->isLink()) { 
            unlink($plik->getPathName()); 
        } elseif (!$plik->isDot() && $plik->isDir()) { 
            removeDir($plik->getPathName()); 
        } 
    } 
    rmdir($sciezka); 
}

function sprDirFile($sciezka) { 
    $blad = true;
    //
    if (!is_writeable( '../' . KATALOG_ZDJEC . '/' . $sciezka )) {
        $blad = false;
    }
    //
    return $blad; 
}

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //			
        Produkty::SkasujProdukt($filtr->process($_POST["id"]));     
        //
        if ( isset($_POST['usun_zdjecia']) && $_POST['usun_zdjecia'] == '1' && isset($_POST['zdjecie']) && count($_POST['zdjecie']) > 0 ) {
             //
             foreach ( $_POST['zdjecie'] as $zdjecie ) {
                //
                if (is_writeable('../' . KATALOG_ZDJEC . '/' . $zdjecie)) {
                    unlink('../' . KATALOG_ZDJEC . '/' . $zdjecie);
                }
                //
            }
            //
        }
        //
        if ( !isset($_GET['zestaw']) ) {  
        
             Funkcje::PrzekierowanieURL('produkty.php');
             
        } else {
          
            Funkcje::PrzekierowanieURL('zestawy_produktow.php');
            
        }
    }

    // sprawdzenie czy produkt nie jest zestawem
    $zestaw = false;
    if ( isset($_GET['zestaw']) ) {
         $zestaw = true;
    } 
    
    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Kasowanie pozycji</div>
    <div id="cont">
          
          <form action="produkty/produkty_usun.php<?php echo (($zestaw) ? '?zestaw' : ''); ?>" method="post" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Usuwanie danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select * from products where products_id = '" . (int)$_GET['id_poz'] . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
                ?>            
            
                <div class="pozycja_edytowana">
                
                    <input type="hidden" name="akcja" value="zapisz" />
                
                    <input type="hidden" name="id" value="<?php echo (int)$_GET['id_poz']; ?>" />
                    
                    <?php
                    // sprawdzi czy produkt nie jest w zestawach
                    //
                    $jest_w_zestawie = false;
                    //
                    $zapytanie_zestaw = 'SELECT products_set_products, products_id FROM products WHERE products_set = 1';        
                    $sql_zestaw = $db->open_query($zapytanie_zestaw);
                    //
                    if ( (int)$db->ile_rekordow($sql_zestaw) > 0 ) {
                         //
                         while ($info = $sql_zestaw->fetch_assoc()) {
                              //
                              $id_produktow = unserialize($info['products_set_products']);
                              foreach ( $id_produktow as $id => $dane ) {
                                  //
                                  if ( (int)$_GET['id_poz'] == $id ) {
                                        $jest_w_zestawie = true;
                                  }
                                  //
                              }
                              unset($id_produktow);
                              //
                         }
                         //
                    }
                    $db->close_query($sql_zestaw);
                    unset($zapytanie_zestaw);
                    ?>
                    
                    <?php if ( $jest_w_zestawie == false ) { ?>
                    
                    <p>
                      Czy skasować <?php echo ((isset($_GET['zestaw'])) ? 'zestaw' : 'produkt'); ?> ?                    
                    </p>   

                    <script>
                    $(document).ready(function() {
                        $(".ZdjecieProduktu").colorbox({ maxWidth:'90%', maxHeight:'90%' });
                    });
                    </script>

                    <?php
                    $zdjecia = array();
                    //
                    $zapytanie_zdjecia = 'SELECT products_image FROM products WHERE products_id = ' . (int)$_GET['id_poz'];  
                    $sql_zdjecia = $db->open_query($zapytanie_zdjecia);
                    //
                    if ( (int)$db->ile_rekordow($sql_zdjecia) > 0 ) {
                          //
                          $info = $sql_zdjecia->fetch_assoc();
                          if ( !empty($info['products_image']) ) {
                               $zdjecia[] = $info['products_image'];
                          }
                          //
                    }
                    //
                    $db->close_query($sql_zdjecia);
                    unset($zapytanie_zdjecia);      
                    //
                    $zapytanie_zdjecia = 'SELECT popup_images FROM additional_images WHERE products_id = ' . (int)$_GET['id_poz'];  
                    $sql_zdjecia = $db->open_query($zapytanie_zdjecia);
                    //
                    if ( (int)$db->ile_rekordow($sql_zdjecia) > 0 ) {
                          //
                          while ($info = $sql_zdjecia->fetch_assoc()) {
                              if ( !empty($info['popup_images']) ) {
                                   $zdjecia[] = $info['popup_images'];
                              }
                          }
                          //
                    }
                    //
                    $db->close_query($sql_zdjecia);
                    unset($zapytanie_zdjecia); 
                    //
                    if ( isset($zdjecia) && count($zdjecia) > 0 ) {
                    
                        echo '<br />';
                        
                        echo '<p>
                          <input type="checkbox" name="usun_zdjecia" id="usun_zdjecia" value="1" /> <label class="OpisFor" for="usun_zdjecia"> czy usunąć także zdjęcia przypisane do produktu (zdjęcia zostaną usunięte z serwera) ?</label>
                        </p>';   
                        
                        //
                        echo '<ul style="margin:5px 10px 10px 10px">';
                        //
                        $zdjecia_input = array();
                        //
                            
                        // katalogi gdzie moga byc zdjecia
                        $katalogi = array('mini', 'watermark', 'allegro_mini');
                        $tablica_plikow = array();               
                        //
                        foreach ( $zdjecia as $zdjecie ) {
                            //
                            echo '<li class="PoleObrazek">zdjęcie: <a href="../' . KATALOG_ZDJEC . '/' . $zdjecie . '" class="ZdjecieProduktu"><b>' . $zdjecie . '</b></a></li>';
                            //
                            $tablica_plikow[] = $zdjecie;
                            //
                            
                            $info = pathinfo($zdjecie);

                            foreach ($katalogi as $katalog) {
                            
                                $dir = '../' . KATALOG_ZDJEC . '/' . $info['dirname'] . '/' . $katalog;
                                
                                if (is_dir($dir)) {
                                  
                                    if ($dh = opendir($dir)) {
                                      
                                        while (($file = readdir($dh)) !== false) {
                                          
                                            if ($file != '.' && $file != '..' && !is_dir($dir . $file)) {
                                                //
                                                if ( strpos((string)$file, 'px_' . $info["basename"] ) > -1 ) {
                                                     //
                                                     $tablica_plikow[] = $info['dirname'] . '/' . $katalog . '/' . $file;
                                                     //
                                                }
                                                //
                                                if ( strpos((string)$file, 'px_' . md5($info["basename"]) . '.' . $info["extension"] ) > -1 ) {
                                                     //
                                                     $tablica_plikow[] = $info['dirname'] . '/' . $katalog . '/' . $file;
                                                     //
                                                }                                    
                                                //
                                            }                                        
                                        }

                                    }
                                    
                                    closedir($dh);
                                    
                                }  
                                
                                unset($dir);                        
                                //
                            }
                        }
                        //
                        echo '</ul>';
                        //
                        foreach ( $tablica_plikow as $plik ) {
                            //
                            echo '<input type="hidden" value="' . $plik . '" name="zdjecie[]" />';
                            //
                        }
                        //
                    }
                    unset($zdjecia);
                    ?>
                    
                    <?php } else { ?>

                    <p>
                        Usuwany produkt jest dodany do zestawu produktów. <br /><br />
                        <span class="ostrzezenie">Nie można usunąć produktu dopóki nie zostanie usunięty z zestawu produktów.</span>
                    </p>
                    
                    <?php } ?>                 
                 
                </div>

                <div class="przyciski_dolne">
                  <?php if ( $jest_w_zestawie == false ) { ?>
                  <input type="submit" class="przyciskNon" value="Usuń dane" />
                  <?php } ?>
                  <button type="button" class="przyciskNon" onclick="cofnij('<?php echo (($zestaw) ? 'zestawy_produktow' : 'produkty'); ?>','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','produkty');">Powrót</button>   
                </div>

            <?php
            } else {
            
                echo '<div class="pozycja_edytowana">Brak danych do wyświetlenia</div>';
            
            }
            $db->close_query($sql);
            unset($zapytanie);               
            ?>

          </div>  
          
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}