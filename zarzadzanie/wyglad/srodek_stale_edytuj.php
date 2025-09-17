<?php
chdir('../');  

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        $id_modu = (int)$_POST['id'];
        //
        $pola = array(
                array('modul_status',$filtr->process($_POST['status'])),
                array('modul_file',$filtr->process($_POST['plik'])),
                array('modul_title',$filtr->process($_POST['nazwa'])),
                array('modul_description',$filtr->process($_POST['opis'])));

        $sql = $db->update_query('theme_modules_fixed' , $pola, " modul_id = '".$id_modu."'");
        unset($pola);
        //
        foreach ( $_POST as $Pole => $Wartosc ) {
            //            
            if ( strpos((string)$Pole, 'KONFIGURACJA__') > -1 ) {
                //
                $pola = array();
                $pola[] = array('modul_settings_value', $Wartosc);
                $sql = $db->update_query('theme_modules_fixed_settings' , $pola, " modul_settings_code = '".str_replace('KONFIGURACJA__', '', (string)$Pole)."'");
                unset($pola);                
                //
            }
            //
        }

        if ( isset($_POST['id_kat']) && count($_POST['id_kat']) > 0 ) {
             //
             $kategorie = implode(',', (array)$_POST['id_kat']);
             $pola = array();
             $pola[] = array('modul_settings_value', $kategorie);
             $sql = $db->update_query('theme_modules_fixed_settings' , $pola, " modul_settings_code = 'WYSWIETLANIE_W_KATEGORIACH' AND modul_id = '" . $id_modu . "'");
             unset($pola);
             //
        }
        if ( !isset($_POST['id_kat']) && $id_modu == '3' ) {
             //
             $pola = array();
             $pola[] = array('modul_settings_value', '0');
             $sql = $db->update_query('theme_modules_fixed_settings' , $pola, " modul_settings_code = 'WYSWIETLANIE_W_KATEGORIACH' AND modul_id = '" . $id_modu . "'");
             unset($pola);
             //
        }
        if ( isset($_POST['edytor_0']) ) {
             //
             $db->delete_query('theme_modules_fixed_settings' , " modul_settings_code = 'EXIT_POPUP_TRESC_WIADOMOSCI'");    
             //
             $ile_jezykow = Funkcje::TablicaJezykow();
             //
             for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {             
                  //
                  $pola = array(
                          array('modul_id',$id_modu),
                          array('modul_settings_code','EXIT_POPUP_TRESC_WIADOMOSCI'),
                          array('modul_settings_value',$filtr->process($_POST['edytor_'.$w])),
                          array('modul_settings_description','Treść wiadomości wyświetlanej w oknie PopUp'),
                          array('modul_settings_value_limit',''),
                          array('language_id',(int)$ile_jezykow[$w]['id']));

                  $sql = $db->insert_query('theme_modules_fixed_settings' , $pola);
                  unset($pola);
                  //
             }
             unset($ile_jezykow);
             //       
        }

        if ( isset($_COOKIE['newsletterPopup']) && $_POST['plik'] == 'newsletter_popup.php' ) {
             unset($_COOKIE['newsletterPopup']);
             setcookie('newsletterPopup', null, -1, '/');
        }
        //        
        Funkcje::PrzekierowanieURL('srodek_stale.php?id_poz='.(int)$_POST["id"]);
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Edycja pozycji</div>
    <div id="cont">        

          <form action="wyglad/srodek_stale_edytuj.php" method="post" id="srodekForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Edycja danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select * from theme_modules_fixed where modul_id = '" . (int)$_GET['id_poz'] . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
            
                $info = $sql->fetch_assoc();
                ?>
                
                <script>
                $(document).ready(function() {
                    $("#srodekForm").validate({
                      rules: {
                        nazwa: {
                          required: true
                        },
                        plik: {
                          required: true
                        }                     
                      },
                      messages: {
                        nazwa: {
                          required: "Pole jest wymagane."
                        }                         
                      }
                    });
                });            
                </script>                  
            
                <div class="pozycja_edytowana">

                    <div class="info_content">
                
                    <input type="hidden" name="akcja" value="zapisz" />
                    
                    <input type="hidden" name="id" value="<?php echo (int)$_GET['id_poz']; ?>" />
        
                    <p>
                        <label class="required" for="nazwa">Nazwa modułu:</label>
                        <input type="text" name="nazwa" size="45" value="<?php echo $info['modul_title']; ?>" id="nazwa" />
                    </p>
                    
                    <p>
                        <label for="opis">Opis modułu:</label>
                        <textarea name="opis" id="opis" rows="5" cols="70"><?php echo $info['modul_description']; ?></textarea><em class="TipIkona"><b>Opis co będzie wyświetlał moduł - informacja tylko dla administratora sklepu</b></em>
                    </p> 

                    <p>
                        <label class="required" for="plik">Nazwa pliku modułu:</label>
                        <input type="text" name="plik" id="plik" value="<?php echo $info['modul_file']; ?>" size="40" /><em class="TipIkona"><b>Nazwa pliku definiującego wygląd modułu (pliki muszą znajdować się w katalogu /moduly_stale</b></em>
                    </p>    

                    <p>
                        <label>Czy moduł ma być włączony ?</label>
                        <input type="radio" value="1" name="status" id="wlaczony_tak" <?php echo (($info['modul_status'] == 1) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="wlaczony_tak">tak</label>
                        <input type="radio" value="0" name="status" id="wlaczony_nie" <?php echo (($info['modul_status'] == 0) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="wlaczony_nie">nie</label>
                    </p> 
                    
                    <?php
                    // naglowek dla cookie z integracjami
                    if ( $info['modul_file'] == 'informacja_o_cookie_integracje.php' ) {
                         //
                         echo '<div class="ModulyStaleNaglowek">Wybierz rodzaj cookies dla poszczególnych integracji</div>';
                         //
                    }
                    
                    // dodatkowa konfiguracja modulu
                    $zapytanieKonfiguracja = "select * from theme_modules_fixed_settings where modul_id = '" . $info['modul_id'] . "' order by modul_settings_code";
                    $sqlKonfiguracja = $db->open_query($zapytanieKonfiguracja);
                    
                    if ((int)$db->ile_rekordow($sqlKonfiguracja) > 0) {
                    
                        $Nr = 1;

                        while ( $infp = $sqlKonfiguracja->fetch_assoc() ) {
                        
                            if ( $info['modul_file'] != 'blokada_18_plus.php' ) {
                              
                                if ( $infp['modul_settings_code'] != 'EXIT_POPUP_TRESC_WIADOMOSCI' ) {
                              
                                     echo '<p>';
                                     echo '<label for="konf_' . $infp['modul_settings_code'] . '">' . html_entity_decode($infp['modul_settings_description'], ENT_QUOTES, 'UTF-8') . '</label> ';
                                    
                                     // jezeli jest pole wyboru
                                     if ( strpos((string)$infp['modul_settings_value_limit'], '/') > 0 ) {
                                         //
                                         $Pola = explode('/', (string)$infp['modul_settings_value_limit']);
                                         //
                                         foreach ($Pola as $Pole) {
                                             echo '<input type="radio" name="KONFIGURACJA__' . $infp['modul_settings_code'] . '" id="konf_' . $Nr . '" value="' . $Pole . '" ' . (($Pole == $infp['modul_settings_value']) ? 'checked="checked"' : '') . '/><label class="OpisFor" for="konf_' . $Nr . '">' . $Pole . '</label>';
                                             $Nr++;
                                         }                                
                                         unset($Pola);
                                         //
                                     } else {
                                         //
                                         if ( strpos((string)$infp['modul_settings_code'], 'TEKST_') > -1 ) {
                                              //
                                              echo '<textarea name="KONFIGURACJA__' . $infp['modul_settings_code'] . '" id="konf_' . $infp['modul_settings_code'] . '" rows="5" cols="50">' . $infp['modul_settings_value'] . '</textarea>';
                                              //
                                           } else {
                                              //
                                              echo '<input type="text" name="KONFIGURACJA__' . $infp['modul_settings_code'] . '" id="konf_' . $infp['modul_settings_code'] . '" size="30" value="' . $infp['modul_settings_value'] . '" />';
                                              //
                                           }
                                         //                        
                                     }
                                     
                                     echo '</p>';
                                
                                }
                            
                            }

                            if ( $info['modul_file'] == 'blokada_18_plus.php' ) {
                        
                                echo '<p><label>'.$infp['modul_settings_description'].':</label></p>' . "\n";
                                
                                echo '<div class="WybieranieKategorii"><div id="drzewo" style="margin:0px;"><table class="pkc">' . "\n";
                                //
                                $tablica_kat = Kategorie::DrzewoKategorii('0', '', '', '', false, true);
                                
                                for ($w = 0, $c = count($tablica_kat); $w < $c; $w++) {
                                  
                                    $podkategorie = false;
                                    if ($tablica_kat[$w]['podkategorie'] == 'true') { $podkategorie = true; }
                                    //
                                    $check = '';
                                    if ( in_array((string)$tablica_kat[$w]['id'], explode(',', (string)$infp['modul_settings_value'])) ) {
                                        $check = 'checked="checked"';
                                    }
                                    //  
                                    echo '<tr>
                                            <td class="lfp"><input type="checkbox" value="' . $tablica_kat[$w]['id'] . '" name="id_kat[]" id="kat_nr_' . $tablica_kat[$w]['id'] . '" ' . $check . ' /> <label class="OpisFor" for="kat_nr_' . $tablica_kat[$w]['id'] . '">' . $tablica_kat[$w]['text'] . '</label></td>
                                            <td class="rgp" ' . (($podkategorie) ? 'id="img_' . $tablica_kat[$w]['id'] . '"' : '') . '>' . (($podkategorie) ? '<img src="obrazki/rozwin.png" alt="Rozwiń" onclick="podkat(\'' . $tablica_kat[$w]['id'] . '\',\'\',\'checkbox\')" />' : '') . '</td>
                                          </tr>
                                          ' . (($podkategorie) ? '<tr><td colspan="2"><div id="p_' . $tablica_kat[$w]['id'] . '"></div></td></tr>' : '');
                                          
                                }
                                
                                echo '</table></div>' . "\n";
                                
                                unset($tablica_kat,$podkategorie);

                                if ( $infp['modul_settings_value'] != '' ) {
                                  
                                    $przypisane_kategorie = $infp['modul_settings_value'];
                                    $kate = explode(',', (string)$przypisane_kategorie);

                                    foreach ( $kate as $val ) {

                                          $sciezka = Kategorie::SciezkaKategoriiId($val, 'categories');
                                          $cSciezka = explode("_", (string)$sciezka);  
                                          
                                          if (count($cSciezka) > 1) {
                                              //
                                              $ostatnie = strRpos($sciezka,'_');
                                              $analiza_sciezki = str_replace("_", ",", substr((string)$sciezka, 0, (int)$ostatnie));
                                              ?>
                                              
                                              <script>       
                                              podkat('<?php echo $analiza_sciezki; ?>', '<?php echo $cSciezka[count($cSciezka)-1]; ?>','checkbox','<?php echo $przypisane_kategorie; ?>');
                                              </script>
                                              
                                          <?php
                                          unset($sciezka,$cSciezka);
                                          }

                                    }

                                    unset($przypisane_kategorie, $kate);  
                                  
                                }
                                
                                echo '</div></div>';
                    
                            }

                        }

                        unset($Nr);   

                        if ( $info['modul_file'] == 'exit_popup.php' ) { ?>
                            
                             <?php $ile_jezykow = Funkcje::TablicaJezykow(); ?>

                             <div class="info_tab">
                             <?php
                             for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                                  echo '<span id="link_'.$w.'" class="a_href_info_tab" onclick="gold_tabs(\''.$w.'\',\'edytor_\', 400)">'.$ile_jezykow[$w]['text'].'</span>';
                             }                                        
                             ?>                                     
                             </div>
                             
                             <div style="clear:both"></div>
                             
                             <div class="info_tab_content">
                             
                                 <?php
                                 for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                                 
                                     // pobieranie danych jezykowych
                                     $zapytanie_jezyk = "select * from theme_modules_fixed_settings where modul_id = '" . $info['modul_id'] . "' and modul_settings_code = 'EXIT_POPUP_TRESC_WIADOMOSCI' and language_id = " . $ile_jezykow[$w]['id'];
                                     $sqls = $db->open_query($zapytanie_jezyk);
                                     $opis = $sqls->fetch_assoc();     
                                     ?>
                                     
                                     <div id="info_tab_id_<?php echo $w; ?>" style="display:none;">
                                     
                                         <p><label style="width:auto">Treść wiadomości wyświetlanej w oknie PopUp:</label></p> 
                                     
                                         <div class="edytor" style="margin-top:5px">
                                             <textarea cols="50" rows="30" id="edytor_<?php echo $w; ?>" name="edytor_<?php echo $w; ?>"><?php echo (isset($opis['modul_settings_value']) ? $opis['modul_settings_value'] : ''); ?></textarea>
                                         </div>     

                                     </div>
                                     
                                     <?php                                        
                                     $db->close_query($sqls);
                                     unset($opis); 
                                   
                                 }                                        
                                 ?>    
                                     
                             </div>
                            
                             <script>
                             gold_tabs('0','edytor_', 400);
                             </script> 
 
                             <?php    

                        }                             
                        
                    }  

                    $db->close_query($sqlKonfiguracja);
                    unset($infp, $zapytanieKonfiguracja);                     

                    ?>

                    </div>
                
                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Zapisz dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('srodek_stale','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','wyglad');">Powrót</button>           
                </div>                 

            <?php
            
            $db->close_query($sql);
            unset($info, $zapytanie);            
            
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
