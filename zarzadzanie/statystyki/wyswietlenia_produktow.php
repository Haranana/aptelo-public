<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_GET['akcja']) && $_GET['akcja'] == 'usun') {
        //			
        if (isset($_GET['id']) && (int)$_GET['id'] > 0) {
            //
            $id = (int)$_GET['id'];
            $pola = array(array('products_viewed','0'));
            $sql = $db->update_query('products_description' , $pola, " products_id = '".$id."'");
            unset($pola);  
            //
        }    
        //
        Funkcje::PrzekierowanieURL('wyswietlenia_produktow.php' . Funkcje::Zwroc_Get(array('akcja','zakres','id','x','y')));
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Raporty</div>
    <div id="cont">

          <div class="poleForm">
            <div class="naglowek">Najczęściej wyświetlane produkty</div>

                <div class="pozycja_edytowana">  

                    <span class="maleInfo">Raport prezentuje 100 najczęściej wyświetlanych w sklepie produktów</span>
                    
                    <div id="wyborJezyka">
                        <table>
                            <tr>
                                <td><span>Pokaż statystyki dla języka:</span></td>
                                <?php
                                $jezyki = "SELECT * FROM languages WHERE status = '1' ORDER BY sort_order";                        
                                $sql = $db->open_query($jezyki);
                                while ($Lang = $sql->fetch_assoc()) {
                                    //
                                    $klasaCSS = 'class="nieaktywny"';
                                    if ((isset($_GET['jezyk']) && (int)$_GET['jezyk'] == $Lang['languages_id']) || (!isset($_GET['jezyk']) && $Lang['languages_id'] == '1')) {
                                        $klasaCSS = 'class="aktywny"';
                                    }
                                    //
                                    echo '<td><a '.$klasaCSS.' href="statystyki/wyswietlenia_produktow.php?jezyk='.$Lang['languages_id'].'"><img src="../' . KATALOG_ZDJEC . '/'.$Lang['image'].'" alt="'.$Lang['name'].'" /></a></td>';
                                }
                                $db->close_query($sql);
                                unset($jezyki, $sql);                        
                                ?>
                            </tr>
                        </table>
                    </div>
                    
                    <?php
                    // get jezyka
                    $IdJezyka = 1; // domyslnie jezyk id 1
                    if (isset($_GET['jezyk']) && (int)$_GET['jezyk']) {
                        $IdJezyka = (int)$_GET['jezyk'];
                    }
                    //
                    $zapytanie = "select p.products_id, pd.products_name, pd.products_viewed from products p, products_description pd where p.products_id = pd.products_id and pd.language_id = '".$IdJezyka."' and pd.products_viewed > 0 order by pd.products_viewed desc, pd.products_name DESC limit 100";
                    $sql = $db->open_query($zapytanie);
                    
                    if ((int)$db->ile_rekordow($sql) > 0) {
                        ?>
                        
                        <div class="WykresStatystki">
                        
                            <h3>
                                <?php
                                echo '20 najczęściej oglądanych produktów';
                                ?>
                            </h3>                    
                        
                            <div class="SzerokiWykres"><canvas id="ogladane_produkty" width="800" height="230"></canvas></div>
                            
                        </div>  
                        
                        <?php
                        include('statystyki/wyswietlenia_produktow_wykres.php');
                        ?>                        
                        
                        <br />
                        
                        <div style="margin:0px 0px 15px 10px">
                            <a class="usun" href="statystyki/wyswietlenia_produktow_usun.php">wyzeruj wszystkie statystyki</a>
                        </div>    
                        
                        <div class="RamkaStatystyki">
                        
                            <table class="TabelaStatystyki">

                            <tr class="TyNaglowek">
                                <td>Lp</td>
                                <td>Nazwa produktu</td>
                                <td style="text-align:center">Ilość wyświetleń</td>
                                <td>&nbsp;</td>
                            </tr>                  
                            
                            <?php
                            $poKolei = 1;
                            while ($info = $sql->fetch_assoc()) {
                            
                                echo '<tr>';
                                
                                echo '<td class="poKolei">' . $poKolei . '</td>'; 
                                
                                // jezeli nie ma nazwy 
                                if ($info['products_name'] == '') {
                                     echo '<td class="linkProd">-- brak nazwy --</td>';
                                   } else {
                                     echo '<td class="linkProd"><a href="produkty/produkty_edytuj.php?id_poz=' . $info['products_id'] . '" ' . (($poKolei < 11) ? 'style="font-weight:bold"' : ''). '>' . $info['products_name'] . '</a></td>';
                                }
                                
                                if ($info['products_viewed'] == 1) {
                                    $TrescZam = ' <span>raz</span>';
                                }
                                if ($info['products_viewed'] > 1) {
                                    $TrescZam = ' <span>razy</span>';
                                }                            
                                echo '<td class="wynikStat">' . $info['products_viewed'] . $TrescZam . '</td>';
                                echo '<td class="WyczyscStat"><a href="statystyki/wyswietlenia_produktow.php?akcja=usun&id='.$info['products_id'].'"><img src="obrazki/kasuj.png" alt="Skasuj" /></a></td>';
                                echo '</tr>';
                                
                                $poKolei++;
                            
                            }            
                            $db->close_query($sql);
                            unset($poKolei);
                            ?>
                            
                            </table>
                            
                        </div>

                        <script src="statystyki/wyswietlenia_produktow_wykres.js"></script>                       

                        <?php
                        
                    } else {
                        //
                        echo '<div style="margin:10px">Brak statystyk ...</div>';
                        //
                    }
                    ?>
                    
                     

                </div>

          </div>                      

    </div>    
    
    <?php
    include('stopka.inc.php');

}