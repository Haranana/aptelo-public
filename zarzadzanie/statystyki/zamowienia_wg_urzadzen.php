<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Raporty</div>
    <div id="cont">

          <div class="poleForm">
            <div class="naglowek">Zamówienia wg urządzeń z jakich został dokonany zakup</div>

                <div class="pozycja_edytowana">  

                    <span class="maleInfo">Zamówienia wg urządzeń z jakich został dokonany zakup</span>
                    
                    <?php
                    Listing::postGet(basename($_SERVER['SCRIPT_NAME']));
                    ?>                      
                    
                    <form action="statystyki/zamowienia_wg_urzadzen.php" method="post" id="statForm" class="cmxform">
                    
                    <script>
                    $(document).ready(function() {
                      $('input.datepicker').Zebra_DatePicker({
                        format: 'd-m-Y',
                        inside: false,
                        readonly_element: false
                      });                
                    });
                    </script>                    
                    
                    <div id="zakresDat" style="margin-top:10px">
                        <span>Przedział czasowy wyników od:</span>
                        <input type="text" id="data_od" name="data_od" value="<?php echo ((isset($_GET['data_od'])) ? $filtr->process($_GET['data_od']) : ''); ?>" size="10" class="datepicker" />&nbsp;do&nbsp;
                        <input type="text" id="data_do" name="data_do" value="<?php echo ((isset($_GET['data_do'])) ? $filtr->process($_GET['data_do']) : ''); ?>" size="10" class="datepicker" />
                    </div>    

                    <div class="wyszukaj_przycisk"><input type="image" alt="Szukaj" src="obrazki/ok.png" /></div>
                    
                    <?php
                    if ( Listing::wylaczFiltr(basename($_SERVER['SCRIPT_NAME'])) == true ) {
                      echo '<div id="wyszukaj_ikona"><a href="statystyki/zamowienia_wg_urzadzen.php?filtr=nie"><img src="obrazki/reset_szukaj.png" alt="Anuluj wyszukiwanie" /></a></div>';
                    }
                    ?>                     

                    <div class="cl"></div>
                    
                    </form>
                    
                    <div class="WykresStatystki">
                        <div><canvas id="typy_zamowien" width="370" height="370"></canvas></div>
                    </div>                

                    <?php
                    $SumaBylo = false;
                    //
                    $warunki_szukania = '';
                    if ( isset($_GET['data_od']) && $_GET['data_od'] != '' ) {
                        $szukana_wartosc = date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_GET['data_od'] . ' 00:00:00')));
                        $warunki_szukania .= " and date_purchased >= '".$szukana_wartosc."'";
                    }

                    if ( isset($_GET['data_do']) && $_GET['data_do'] != '' ) {
                        $szukana_wartosc = date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_GET['data_do'] . ' 23:59:59')));
                        $warunki_szukania .= " and date_purchased <= '".$szukana_wartosc."'";                       
                    }
                        
                    $ZrodloTelefon = 0;
                    $ZrodloKomputer = 0;
                    $ZrodloTablet = 0;
                    //
                    $zapytanie = "select device from orders where device != '' " . $warunki_szukania;
                    $sql = $db->open_query($zapytanie);
                    //
                    if ($db->ile_rekordow($sql) > 0) {
                        //
                        while ($info = $sql->fetch_assoc()) {
                            //
                            if ( strpos($info['device'], 'laptop') ) {
                                 $ZrodloKomputer++;
                            }
                            if ( strpos($info['device'], 'phone') ) {
                                 $ZrodloTelefon++;
                            }
                            if ( strpos($info['device'], 'ablet') ) {
                                 $ZrodloTablet++;
                            }                            
                            //
                        }
                        //
                    }
                    //
                    $db->close_query($sql);                    
                    //
                    if ($ZrodloTelefon > 0 || $ZrodloKomputer > 0 || $ZrodloTablet > 0) {                    
                    
                        $Wynik = '<div class="RamkaStatystyki"><table class="TabelaStatystyki">';
                        
                        $Wynik .= '<tr class="TyNaglowek">';
                        $Wynik .= '<td>Typ zamówienia</td>';
                        $Wynik .= '<td align="center">Ilość zamówień</td>';
                        $Wynik .= '</tr>';                        

                        $Wynik .= '<tr>';
                        $Wynik .= '<td class="linkProd">Komputer stacjonarny / laptop</td>';
                        $Wynik .= '<td class="wynikStat">' . $ZrodloKomputer;
                        
                        $TrescZam = '';
                        if ($ZrodloKomputer == 1) {
                            $TrescZam = ' <span>zamówienie</span>';
                        }
                        if ($ZrodloKomputer > 1 && $ZrodloKomputer < 5) {
                            $TrescZam = ' <span>zamówienia</span>';
                        }
                        if ($ZrodloKomputer > 4 || $ZrodloKomputer == 0) {
                            $TrescZam = ' <span>zamówień</span>';
                        }                                     
                        
                        $Wynik .= $TrescZam . '</td>';
                        $Wynik .= '</tr>';

                        $Wynik .= '<tr>';
                        $Wynik .= '<td class="linkProd">Smartphone</td>';
                        $Wynik .= '<td class="wynikStat">' . $ZrodloTelefon;
                        
                        $TrescZam = '';
                        if ($ZrodloTelefon == 1) {
                            $TrescZam = ' <span>zamówienie</span>';
                        }
                        if ($ZrodloTelefon > 1 && $ZrodloTelefon < 5) {
                            $TrescZam = ' <span>zamówienia</span>';
                        }
                        if ($ZrodloTelefon > 4 || $ZrodloTelefon == 0) {
                            $TrescZam = ' <span>zamówień</span>';
                        }                                     
                        
                        $Wynik .= $TrescZam . '</td>';
                        $Wynik .= '</tr>';                        
                        
                        $Wynik .= '<tr>';
                        $Wynik .= '<td class="linkProd">Tablet</td>';
                        $Wynik .= '<td class="wynikStat">' . $ZrodloTablet;
                        
                        $TrescZam = '';
                        if ($ZrodloTablet == 1) {
                            $TrescZam = ' <span>zamówienie</span>';
                        }
                        if ($ZrodloTablet > 1 && $ZrodloTablet < 5) {
                            $TrescZam = ' <span>zamówienia</span>';
                        }
                        if ($ZrodloTablet > 4 || $ZrodloTablet == 0) {
                            $TrescZam = ' <span>zamówień</span>';
                        }                                     
                        
                        $Wynik .= $TrescZam . '</td>';
                        $Wynik .= '</tr>';   

                        $Wynik .= '</table></div>';
                        
                        echo $Wynik;
                        
                        unset($Wynik, $TabWynik, $TrescZam);
                        
                        include('statystyki/zamowienia_wg_urzadzen_wykres.php');
                        ?>
                        
                        <script src="statystyki/zamowienia_wg_urzadzen.js"></script>                      

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