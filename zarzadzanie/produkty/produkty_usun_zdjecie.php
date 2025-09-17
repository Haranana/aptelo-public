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
        if ( isset($_POST['plik']) && isset($_POST['id_produktu']) ) {        
            //
            if (is_writeable('../' . KATALOG_ZDJEC . '/' . $_POST['plik'])) {
                if ( $db->spr_plik() == false ) {
                     unlink('../' . KATALOG_ZDJEC . '/' . $_POST['plik']);
                }
            }
            //
            // kasuje rekordy w tablicy
            $pola = array(array('products_image',''),
                          array('products_image_description',''));
                          
            $db->update_query('products', $pola, " products_id = '" . (int)$_POST['id_produktu'] . "' and products_image = '" . $filtr->process($_POST['plik']) . "'");                     
            $db->delete_query('additional_images' , " products_id = '" . (int)$_POST['id_produktu'] . "' and popup_images = '" . $filtr->process($_POST['plik']) . "'");             
            //
            // miniaturki
            if (isset($_POST['dodatkowy_plik'])) {
                //
                foreach ( $_POST['dodatkowy_plik'] as $DodatkowyPlik ) {
                    //
                    if (is_writeable('../' . KATALOG_ZDJEC . '/' . $DodatkowyPlik)) {
                        if ( $db->spr_plik() == false ) {
                             unlink('../' . KATALOG_ZDJEC . '/' . $DodatkowyPlik);
                        }
                    }
                    //
                }
                //
            }
            //
            Funkcje::PrzekierowanieURL('/zarzadzanie/produkty/produkty_edytuj.php?id_poz=' . (int)$_POST['id_produktu'] . '&zakladka=3');              
            //
        }
    }  

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Kasowanie pliku zdjęcia przypisanego do produktu</div>
    <div id="cont">
          
          <form action="produkty/produkty_usun_zdjecie.php" method="post" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Usuwanie plików zdjęć przypisanych do produktów</div>
            
            <?php
            $_GET['id_poz'] = (int)$_POST['id_produktu'];
            $_GET['zakladka'] = 3;
                    
            if ($_POST['zdjecie'] && !empty($_POST['zdjecie'])) {
            
                // sprawdza czy mozna skasowac plik 
                if (sprDirFile($_POST['zdjecie'])) {
                  
                    $info = pathinfo($_POST['zdjecie']);

                    $TablicaPlikow = array();
                    
                    // katalogi gdzie moga byc zdjecia
                    $Katalogi = array('mini', 'watermark', 'allegro_mini');
                    
                    foreach ($Katalogi as $Katalog) {
                    
                        $dir = '../' . KATALOG_ZDJEC . '/' . $info['dirname'] . '/' . $Katalog;
                        
                        if (is_dir($dir)) {
                          
                            if ($dh = opendir($dir)) {
                              
                                while (($file = readdir($dh)) !== false) {
                                  
                                    if ($file != '.' && $file != '..' && !is_dir($dir . $file)) {
                                        //
                                        if ( strpos((string)$file, 'px_' . $info["basename"] ) > -1 ) {
                                             //
                                             $TablicaPlikow[] = $info['dirname'] . '/' . $Katalog . '/' . $file;
                                             //
                                        }
                                        //
                                        if ( strpos((string)$file, 'px_' . md5($info["basename"]) . '.' . $info["extension"] ) > -1 ) {
                                             //
                                             $TablicaPlikow[] = $info['dirname'] . '/' . $Katalog . '/' . $file;
                                             //
                                        }                                    
                                        //
                                    }                                        
                                }

                            }
                            
                            closedir($dh);
                            
                        }  
                        
                        unset($dir);
                        
                    }
                    ?>
                
                    <div class="pozycja_edytowana">
                    
                        <input type="hidden" name="akcja" value="zapisz" />
                        <input type="hidden" name="plik" value="<?php echo $filtr->process($_POST['zdjecie']); ?>" />
                        <input type="hidden" name="id_produktu" value="<?php echo (int)$_POST['id_produktu']; ?>" />
                        
                        <?php
                        foreach ($TablicaPlikow as $Plik) {
                             //
                             echo '<input type="hidden" value="' . str_replace('./', '', (string)$Plik) . '" name="dodatkowy_plik[]" />';
                             //
                        }
                        ?>

                        <p>
                        
                          Czy skasować zdjęcie z produktu oraz fizycznie plik z serwera - <b><?php echo $_POST['zdjecie']; ?></b> (oraz zdjęcia miniaturek) ?                    

                        </p>   
                     
                    </div>

                    <div class="przyciski_dolne">
                      <input type="submit" class="przyciskNon" value="Usuń" />
                      <button type="button" class="przyciskNon" onclick="cofnij('produkty_edytuj','<?php echo Funkcje::Zwroc_Get(array('x','y')); ?>','produkty');">Powrót</button>    
                    </div>
                    
                    <?php

                } else {
                
                    echo '<div class="pozycja_edytowana"><p>Nie można usunąć pliku ...</p></div>';
                    
                    ?>
                    
                    <div class="przyciski_dolne">
                      <input type="submit" class="przyciskNon" value="Usuń" />
                      <button type="button" class="przyciskNon" onclick="cofnij('produkty_edytuj','<?php echo Funkcje::Zwroc_Get(array('x','y')); ?>','produkty');">Powrót</button>     
                    </div>                
                    
                    <?php
                
                }
                ?>                
                
            <?php
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