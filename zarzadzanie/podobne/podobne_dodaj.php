<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        $glowne_id = (int)$_POST['id_wybrany_produkt'];
        $saPodobne = false;
        //
        if ( !isset($_POST['krzyzowo_wszystkie']) || $_POST['krzyzowo_wszystkie'] != 1 ) {
          
            if (isset($_POST['id_produktow'])) {
              
                if (count($_POST['id_produktow']) > 0) {
                    //                
                    foreach ($_POST['id_produktow'] as $pole) {
                        //
                        $pola = array(array('pop_products_id_master',(int)$glowne_id),
                                      array('pop_products_id_slave',(int)$pole));
                        //	
                        $sql = $db->insert_query('products_options_products', $pola);
                        $saPodobne = true;
                        //
                        unset($pola);  
                    }
                    //
                }
                
            }                
            
            if (isset($_POST['id_produktow'])) {
              
                if (count($_POST['id_produktow']) > 0 && isset($_POST['krzyzowo'])) {
                    //
                    foreach ($_POST['krzyzowo'] as $pole) {
                        //
                        // sprawdza czy juz nie ma takiego rekordu
                        $zapytanie = "select distinct * from products_options_products where pop_products_id_master = '".(int)$pole."' and pop_products_id_slave = '".(int)$glowne_id."'";
                        $sqls = $db->open_query($zapytanie);  
                        //
                        if ((int)$db->ile_rekordow($sqls) == 0) {
                            //
                            $pola = array(array('pop_products_id_master',(int)$pole),
                                          array('pop_products_id_slave',(int)$glowne_id));
                            //	
                            $sql = $db->insert_query('products_options_products', $pola);
                            //
                            unset($pola);
                            //
                        }
                        //
                        $db->close_query($sqls);
                        unset($zapytanie);            
                    }        
                    //
                }
                
            } 
        
        } else {
          
            if (isset($_POST['id_produktow'])) {
              
                if (count($_POST['id_produktow']) > 0) {
                    //
                    $tablicaWszystkich = array();
                    // dodaje glowne id
                    $tablicaWszystkich[] = $glowne_id;
                    // dodaje pozostale
                    foreach ($_POST['id_produktow'] as $pole) {
                        //
                        $tablicaWszystkich[] = $pole;
                        //
                    }
                    //        
                    foreach ($tablicaWszystkich as $pozycja) {
                        //
                        // podpetla
                        foreach ($tablicaWszystkich as $podpozycja) {
                            //
                            if ( $pozycja != $podpozycja) {
                              
                                // sprawdza czy juz nie ma takiego rekordu
                                $zapytanie = "select distinct * from products_options_products where pop_products_id_master = '".(int)$pozycja."' and pop_products_id_slave = '".$podpozycja."'";
                                $sqls = $db->open_query($zapytanie);  
                                //
                                if ((int)$db->ile_rekordow($sqls) == 0) {        
                                    //
                                    $pola = array(array('pop_products_id_master',(int)$pozycja),
                                                  array('pop_products_id_slave',(int)$podpozycja));
                                    //	
                                    $sql = $db->insert_query('products_options_products', $pola);
                                    $saPodobne = true;
                                    //
                                    unset($pola);  
                                    //
                                }
                                //
                                $db->close_query($sqls);
                                unset($zapytanie); 
                                
                            }
                            //
                        }
                        //
                    }
                    //
                    unset($tablicaWszystkich);
                    //
                }
                
            }            
          
        }        
        
        if ( isset($_POST['powrot_id']) ) {
             Funkcje::PrzekierowanieURL('/zarzadzanie/produkty/produkty_edytuj.php?id_poz='.(int)$_POST['powrot_id']);
        } else {
             if ($saPodobne == false) {
                 Funkcje::PrzekierowanieURL('podobne.php');
               } else {
                 Funkcje::PrzekierowanieURL('podobne.php?id_poz='.(int)$glowne_id);
             }
        }        
     
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Dodawanie pozycji</div>
    <div id="cont">

          <form action="podobne/podobne_dodaj.php" method="post" id="podobneForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Dodawanie danych</div>
            
            <div class="pozycja_edytowana">
            
                <input type="hidden" name="akcja" value="zapisz" />
                
                <input type="hidden" id="rodzaj_modulu" value="podobne" />
                
                <?php if ( isset($_GET['edycja']) ) { ?>
                
                <input type="hidden" name="powrot_id" value="<?php echo (int)$_GET['id_poz']; ?>" />
                
                <?php } ?>                
                
                <div class="GlownyListing">

                    <?php
                    $plik = 'podobne.php';
                    if ( isset($_SESSION['filtry'][$plik]['kategoria_id']) ) {
                         $_GET['kategoria_id'] = $_SESSION['filtry'][$plik]['kategoria_id'];
                    }
                    unset($plik);
                    ?>                        

                    <?php if (!isset($_GET['kategoria_id'])) { ?>
            
                    <div class="GlownyListingKategorieEdycja" id="drzewo_podobne">

                        <p style="font-weight:bold">
                        Wyszukaj produkt lub wybierz kategorię z której chcesz wybrać produkt do produktów podobnych
                        </p>
                        
                        <div style="margin-left:10px;margin-top:7px;" id="fraza">
                            <div>Wyszukaj produkt: <input type="text" size="15" value="" id="szukany" /><em class="TipIkona"><b>Wpisz nazwę produktu lub kod producenta</b></em></div> <span onclick="fraza_produkty()" ></span>
                        </div>                                 
                        
                        <div id="drzewo" style="margin-left:10px;margin-top:7px">
                            <?php
                            //
                            echo '<table class="pkc">';
                            //
                            $tablica_kat = Kategorie::DrzewoKategorii('0', '', '', '', false, true);
                            for ($w = 0, $c = count($tablica_kat); $w < $c; $w++) {
                                $podkategorie = false;
                                if ($tablica_kat[$w]['podkategorie'] == 'true') { $podkategorie = true; }
                                //
                                echo '<tr>
                                        <td class="lfp"><input type="radio" onclick="podkat_produkty(this.value)" value="'.$tablica_kat[$w]['id'].'" name="id_kat" id="kat_nr_'.$tablica_kat[$w]['id'].'" /> <label class="OpisFor" for="kat_nr_'.$tablica_kat[$w]['id'].'">'.$tablica_kat[$w]['text'].(($tablica_kat[$w]['status'] == 0) ? '<div class="wylKat TipChmurka"><b>Kategoria jest nieaktywna</b></div>' : '').'</label></td>
                                        <td class="rgp" '.(($podkategorie) ? 'id="img_'.$tablica_kat[$w]['id'].'"' : '').'>'.(($podkategorie) ? '<img src="obrazki/rozwin.png" alt="Rozwiń" onclick="podkat(\''.$tablica_kat[$w]['id'].'\',\'\',\'radio\')" />' : '').'</td>
                                      </tr>
                                      '.(($podkategorie) ? '<tr><td colspan="2"><div id="p_'.$tablica_kat[$w]['id'].'"></div></td></tr>' : '').'';
                            }
                            if ( count($tablica_kat) == 0 ) {
                                 echo '<tr><td colspan="9" style="padding:10px">Brak wyników do wyświetlania</td></tr>';
                            }                                
                            echo '</table>';
                            unset($tablica_kat,$podkategorie);
                            ?> 
                        </div>

                    </div>
                    
                    <?php } ?>   
                    
                    <div style="GlownyListingProduktyEdycja">                                     

                        <div id="wynik_produktow_podobne" class="WynikProduktowPodobne" style="display:none"></div>     

                        <div id="formi" style="display:none">
                        
                            <div id="wybrany_produkt" class="WybranyProdukt"></div>
                            
                            <input type="hidden" value="," id="jakie_id" />
                            
                            <div id="wybrane_produkty"></div>
                            
                            <div style="margin:10px 10px 20px 10px">
                            
                                <input type="checkbox" name="krzyzowo_wszystkie" id="krzyzowo_wszystkie" value="1" /><label class="OpisFor" for="krzyzowo_wszystkie">krzyżowo wszystkie produkty A do B,C, B do A,C, C do A,B itd</label> 
                                
                            </div>                            
                            
                            <div id="lista_do_wyboru"></div>
                            
                        </div>
                        
                    </div>
                    
                </div>
                
            </div>

            <div class="przyciski_dolne">
            
              <input type="submit" class="przyciskNon" value="Zapisz dane" id="ButZapis" style="display:none" />
              
              <?php if ( isset($_GET['edycja']) ) { ?>
                 <button type="button" class="przyciskNon" onclick="cofnij('produkty_edytuj','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','produkty');">Powrót</button>     
              <?php } else { ?>
                 <button type="button" class="przyciskNon" onclick="cofnij('podobne','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','podobne');">Powrót</button> 
              <?php } ?>              

            </div>

            <?php if (isset($_GET['kategoria_id']) && (int)$_GET['kategoria_id'] > 0) { ?>
            
            <script>           
            podkat_produkty(<?php echo (int)$_GET['kategoria_id']; ?>);
            </script>       

            <?php } ?>       
            
            <?php if ( isset($_GET['edycja']) ) { ?>

            <script>
            lista_akcja(<?php echo $_GET['id_poz']; ?>,'podobne');
            $('#ButZapis').css('display','inline-block');
            </script>
            
            <?php } ?>

          </div>                      
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}
