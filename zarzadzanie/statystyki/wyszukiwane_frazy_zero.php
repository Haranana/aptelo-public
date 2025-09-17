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
            $db->delete_query('customers_searches_zero' , " search_id = '".$id."'"); 
            //
        }    
        //
        Funkcje::PrzekierowanieURL('wyszukiwane_frazy_zero.php' . Funkcje::Zwroc_Get(array('akcja','zakres','id','x','y')));
    }
    
    if (isset($_GET['akcja']) && $_GET['akcja'] == 'usun_wszystko') {
        //			
        $db->open_query('TRUNCATE customers_searches_zero');  
        //
        Funkcje::PrzekierowanieURL('wyszukiwane_frazy_zero.php' . Funkcje::Zwroc_Get(array('akcja','zakres','id','x','y')));
    }    

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Raporty</div>
    <div id="cont">

          <div class="poleForm">
            <div class="naglowek">Wyszukiwane frazy z wynikiem ZERO</div>

                <div class="pozycja_edytowana">  

                    <span class="maleInfo">Raport prezentuje wyszukiwane frazy w sklepie dla których nie było wyników</span>
                    
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
                                    echo '<td><a '.$klasaCSS.' href="statystyki/wyszukiwane_frazy_zero.php?jezyk='.$Lang['languages_id'].'"><img src="../' . KATALOG_ZDJEC . '/'.$Lang['image'].'" alt="'.$Lang['name'].'" /></a></td>';
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
                    $zapytanie = "select * from customers_searches_zero where language_id = '".$IdJezyka."' order by freq desc";
                    $sql = $db->open_query($zapytanie);
                    
                    if ((int)$db->ile_rekordow($sql) > 0) {
                        ?>
                        
                        <div style="margin:0px 0px 15px 10px">
                            <a class="usun" href="statystyki/wyszukiwane_frazy_zero.php?akcja=usun_wszystko">usuń wszystkie dane</a>
                        </div>
                        
                        <div class="RamkaStatystyki">
                        
                            <table class="TabelaStatystyki">

                            <tr class="TyNaglowek">
                                <td>Lp</td>
                                <td>Fraza</td>
                                <td style="text-align:center">Ilość wyszukań</td>
                                <td>&nbsp;</td>
                            </tr>                  
                            
                            <?php
                            $poKolei = 1;
                            while ($info = $sql->fetch_assoc()) {
                            
                                echo '<tr>';
                                
                                echo '<td class="poKolei">' . $poKolei . '</td>'; 
                                
                                echo '<td class="linkProd">' . $info['search_key'] . '</td>';
                                
                                if ($info['freq'] == 1) {
                                    $TrescZam = ' <span>raz</span>';
                                }
                                if ($info['freq'] > 1) {
                                    $TrescZam = ' <span>razy</span>';
                                }                            
                                echo '<td class="wynikStat">' . $info['freq'] . $TrescZam . '</td>';
                                echo '<td class="WyczyscStat"><a class="TipChmurka" href="statystyki/wyszukiwane_frazy_zero.php?akcja=usun&id='.$info['search_id'].'"><b>Usuń tą frazę</b><img src="obrazki/kasuj.png" alt="Skasuj" /></a></td>';
                                echo '</tr>';
                                
                                $poKolei++;
                            
                            }            
                            $db->close_query($sql);
                            unset($poKolei);
                            ?>
                            
                            </table>
                            
                        </div>

                        <?php
                        
                    } else {
                        //
                        echo '<div style="margin:10px;padding:10px" class="RamkaStatystyki">Brak statystyk ...</div>';
                        //
                    }
                    ?>
                    
                     

                </div>

          </div>                      

    </div>    
    
    <?php
    include('stopka.inc.php');

}