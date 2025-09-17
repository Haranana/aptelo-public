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
            $sql = $db->delete_query('basket_save' , "	basket_id = '" . (int)$_GET['id'] . "'"); 
            $sql = $db->delete_query('basket_save_products' , "	basket_id = '" . (int)$_GET['id'] . "'"); 
            //
        }    
        //
        Funkcje::PrzekierowanieURL('zapisane_koszyki.php');
    } 

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'usuniecie_daty') {
        //			
        if ( isset($_POST['data_od']) && $_POST['data_od'] != '' ) {
            //
            $data_od = FunkcjeWlasnePHP::my_strtotime($filtr->process($_POST['data_od']));
            //
            $zapytanie = "SELECT DISTINCT basket_id FROM basket_save WHERE basket_code <= '" . $data_od . "'";
            $sql = $db->open_query($zapytanie);
            //
            while ($info = $sql->fetch_assoc()) {
                //
                $db->delete_query('basket_save' , "	basket_id = '" . (int)$info['basket_id'] . "'"); 
                $db->delete_query('basket_save_products' , "	basket_id = '" . (int)$info['basket_id'] . "'"); 
                //
            }
            //
            $db->close_query($sql);
            unset($zapytanie);                                                     
            //
        }    
        //
        Funkcje::PrzekierowanieURL('zapisane_koszyki.php');
    }      

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Zapisane koszyki</div>
    <div id="cont">

          <div class="poleForm">
            <div class="naglowek">Zapisane koszyki</div>

                <div class="pozycja_edytowana">  
                
                    <span class="maleInfo">Raport prezentuje zapisane koszyki klientów</span>
                    
                    <?php if ( !isset($_GET['nazwa']) ) { ?>
                    
                    <form action="statystyki/zapisane_koszyki.php" method="post" id="statForm" class="cmxform">

                    <script>
                    $(document).ready(function() {
                      $('input.datepicker').Zebra_DatePicker({
                        format: 'd-m-Y',
                        inside: false,
                        readonly_element: true
                      });                
                    });                   
                    </script>                    
                    
                    <div id="zakresDat" style="margin-top:10px">
                        <span>Usuń koszyki starsze niż:</span>
                        <input type="hidden" name="akcja" value="usuniecie_daty" />
                        <input type="text" id="data_od" name="data_od" value="" size="10" class="datepicker" /> 
                    </div>     

                    <div class="wyszukaj_przycisk" style="margin-right:40px;"><input type="image" alt="Szukaj" src="obrazki/ok.png" /></div>
                               
                    <div class="cl"></div>
                    
                    </form>
                    
                    <?php } ?>
                    
                    <form action="statystyki/zapisane_koszyki.php" method="get" id="statFormNazwa" class="cmxform">
                    
                    <div id="nazwaKoszyka">
                        <span>Nazwa lub ID koszyka:</span>
                        <input type="text" id="nazwa" name="nazwa" value="<?php echo (( isset($_GET['nazwa']) && trim((string)$_GET['nazwa']) != '' ) ? trim((string)$_GET['nazwa']) : ''); ?>" size="30" /> 
                    </div>    
                    
                    <div class="wyszukaj_przycisk"><input type="image" alt="Szukaj" src="obrazki/ok.png" /></div>
                    
                    <?php
                    if ( isset($_GET['nazwa']) && trim((string)$_GET['nazwa']) != '' ) {
                      echo '<div id="wyszukaj_ikona" style="margin-left:20px"><a href="statystyki/zapisane_koszyki.php?filtr=nie"><img src="obrazki/reset_szukaj.png" alt="Anuluj wyszukiwanie" /></a></div>';
                    }
                    ?>                    
                               
                    <div class="cl"></div>
                    
                    <script>
                    function podgladZapisanegoKoszyka(id_koszyka) {
                        $.colorbox( { href:"ajax/koszyk_zapisany_klienta.php?id_koszyka=" + id_koszyka, maxHeight:'90%', open:true, initialWidth:50, initialHeight:50, onComplete : function() { $(this).colorbox.resize(); } } ); 
                    }                        
                    </script>
                    
                    <?php
                    // ile na stronie
                    if ( !isset($_GET['nazwa']) ) {
                         $IleNaStronie = 50;
                    } else {
                         $IleNaStronie = 5000;
                    }
                    
                    echo '<div id="tabWynik">';
                    
                    echo '<div class="RamkaStatystyki"><table class="TabelaStatystyki">';
                          
                    if ( !isset($_GET['nazwa']) ) {
                    
                          $PoczatekLimit = 0;
                          if (isset($_GET['str']) && (int)$_GET['str'] > 0) {
                              $PoczatekLimit = (int)$_GET['str'] * $IleNaStronie;
                          }

                          $zapytanie = 'SELECT DISTINCT bs.*, c.customers_firstname, c.customers_lastname FROM basket_save bs 
                                              LEFT JOIN customers c ON bs.customers_id = c.customers_id
                                               ORDER BY bs.basket_code desc';                    
                                               
                          $sql = $db->open_query($zapytanie);
                          $OgolnaIlosc = (int)$db->ile_rekordow($sql);
                          
                          $db->close_query($sql);
                          unset($zapytanie);                                         

                          $zapytanie = 'SELECT DISTINCT bs.*, c.customers_firstname, c.customers_lastname FROM basket_save bs 
                                               LEFT JOIN customers c ON bs.customers_id = c.customers_id
                                               ORDER BY bs.basket_code desc LIMIT ' . $PoczatekLimit . ',' . $IleNaStronie;                      
          
                    } else {
                      
                          $zapytanie = 'SELECT DISTINCT bs.*, c.customers_firstname, c.customers_lastname FROM basket_save bs 
                                               LEFT JOIN customers c ON bs.customers_id = c.customers_id
                                               WHERE basket_name like \'%'.$filtr->process($_GET['nazwa']).'%\' or basket_code like \'%'.$filtr->process($_GET['nazwa']).'%\'
                                               ORDER BY bs.basket_code desc';                        
                      
                    }
                    
                    $sql = $db->open_query($zapytanie);

                    if ((int)$db->ile_rekordow($sql) > 0) {

                        echo '<tr class="TyNaglowek">';
                        echo '<td style="text-align:center">Klient</td>';
                        echo '<td style="text-align:center">Nazwa koszyka</td>';
                        echo '<td>Link do koszyka</td>';
                        echo '<td style="text-align:center">Data utworzenia</td>';
                        echo '<td></td>';
                        echo '</tr>';                      

                        while ($info = $sql->fetch_assoc()) {
                            //
                            $DaneKlienta = '<span style="color:#999">-- niezalogowany --</span>';
                            if ( $info['customers_lastname'] != '' && $info['customers_firstname'] != '' ) {
                                 //
                                 $DaneKlienta = '<a href="klienci/klienci_edytuj.php?id_poz=' . $info['customers_id'] . '">' . $info['customers_firstname'] . ' ' . $info['customers_lastname'] . '</a> ';
                                 //
                            }
                            //
                            echo '<tr>';
                            echo '<td class="linkProd" style="width:auto">' . $DaneKlienta . '</td>';
                            echo '<td class="inne">' . (($info['basket_name'] != '') ? $info['basket_name'] : '-') . '</td>';
                            echo '<td class="linkProd" style="width:60%"><a target="_blank" href="' . ADRES_URL_SKLEPU . '/koszyk.html/koszyk=' . $info['basket_id'] . '-' . $info['basket_code'] . '">' . ADRES_URL_SKLEPU . '/koszyk.html/koszyk=' . $info['basket_id'] . '-' . $info['basket_code'] . '</a></td>';
                            echo '<td class="inne" style="white-space:nowrap">' . date('d-m-Y H:i',$info['basket_code']) . '</td>';
                            echo '<td class="inne InneIkony">
                                      <em class="TipChmurka"><b>Pokaz zawartość zapisanego koszyka</b><img onclick="podgladZapisanegoKoszyka(\'' . (int)$info['basket_id'] . '\')" style="cursor:pointer;" src="obrazki/zobacz.png" alt="" /></em>
                                      <a class="TipChmurka" href="statystyki/zapisane_koszyki.php?akcja=usun&id=' . (int)$info['basket_id'] . '"><b>Usuń koszyk</b><img src="obrazki/kasuj.png" alt="Usuń" /></a>
                                  </td>'; 
                            echo '</tr>';
                            //
                            unset($DaneKlienta);
                            //
                        }

                        unset($info);

                      } else {
                      
                        echo '<tr><td style="padding:10px; border:0px;">Brak wyników ...</td></tr>';
                     
                    }

                    echo '</table></div>';   

                    if ((int)$db->ile_rekordow($sql) > 0 && !isset($_GET['nazwa'])) {
                    ?>
                    <div id="DolneStrony">
                        <?php
                        $limit = $OgolnaIlosc / $IleNaStronie;
                        if ($limit < ($OgolnaIlosc / $IleNaStronie)) {
                            $limit++;
                        }
                        //
                        for ($c = 0; $c < $limit; $c++) {
                            //
                            $Rozszerzenie = 'Nieaktywny';
                            if ((!isset($_GET['str']) || (int)$_GET['str'] == 0) && $c == 0) {
                                $Rozszerzenie = 'Aktywny';
                            }
                            if (isset($_GET['str']) && (int)$_GET['str'] == $c) {
                                $Rozszerzenie = 'Aktywny';
                            }
                            //
                            if ($c == 0) {
                                echo '<a class="Przycisk' . $Rozszerzenie . '" href="statystyki/zapisane_koszyki.php">1</a>';
                              } else {
                                echo '<a class="Przycisk' . $Rozszerzenie . '" href="statystyki/zapisane_koszyki.php?str='.$c.'">' . ($c + 1) . '</a>';
                            }
                        }
                        //
                        echo '<span id="IleStrona">Wyświetlanie: ' . ($PoczatekLimit + 1) . ' do ' . (($PoczatekLimit + $IleNaStronie) > $OgolnaIlosc ? $OgolnaIlosc : ($PoczatekLimit + $IleNaStronie)) . ' z ' . $OgolnaIlosc . '</span>';
                        ?>
                    </div> 
                    <?php
                    }
                    
                    unset($zapytanie);
                    $db->close_query($sql);
                    ?>

                    </div>

                </div>

          </div>                      

    </div>    
    
    <?php
    include('stopka.inc.php');

}