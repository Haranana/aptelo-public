<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        $id_dodawanej_pozycji = (int)$_POST['id_produkt'];
        //
        $pola = array();
        $pola[] = array('star_status','1');
        if (!empty($_POST['data_nasz_hit_od'])) {
            $pola[] = array('star_date',date('Y-m-d', FunkcjeWlasnePHP::my_strtotime($filtr->process($_POST['data_nasz_hit_od']))));
          } else {
            $pola[] = array('star_date','0000-00-00');            
        }
        if (!empty($_POST['data_nasz_hit_do'])) {
            $pola[] = array('star_date_end',date('Y-m-d', FunkcjeWlasnePHP::my_strtotime($filtr->process($_POST['data_nasz_hit_do']))));
          } else {
            $pola[] = array('star_date_end','0000-00-00');            
        }
        //	
        $sql = $db->update_query('products', $pola, 'products_id = ' . (int)$id_dodawanej_pozycji);
        
        unset($pola);
        
        Funkcje::PrzekierowanieURL('nasz_hit.php?id_poz='.(int)$id_dodawanej_pozycji);
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Dodawanie pozycji</div>
    <div id="cont">

          <form action="nasz_hit/nasz_hit_dodaj.php" method="post" id="poForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Dodawanie danych</div>
            
            <div class="pozycja_edytowana">
            
                <input type="hidden" name="akcja" value="zapisz" />
                
                <input type="hidden" id="rodzaj_modulu" value="nasz_hit" />

                <script>
                $(document).ready(function() {

                    $('input.datepicker').Zebra_DatePicker({
                       format: 'd-m-Y',
                       inside: false,
                       readonly_element: true
                    });                
                
                });
                </script>      

                <div class="GlownyListing">

                    <?php
                    $plik = 'nasz_hit.php';
                    if ( isset($_SESSION['filtry'][$plik]['kategoria_id']) ) {
                         $_GET['kategoria_id'] = $_SESSION['filtry'][$plik]['kategoria_id'];
                    }
                    unset($plik);
                    ?>                        
                
                    <?php if (!isset($_GET['kategoria_id'])) { ?>
            
                    <div class="GlownyListingKategorieEdycja">       

                        <p style="font-weight:bold">
                        Wyszukaj produkt lub wybierz kategorię z której chcesz wybrać produkt do utworzenia hitu
                        </p>
                        
                        <div style="margin-left:10px;margin-top:7px;" id="fraza">
                            <div>Wyszukaj produkt: <input type="text" size="15" value="" id="szukany" /><em class="TipIkona"><b>Wpisz nazwę produktu lub kod producenta</b></em></div> <span onclick="fraza_produkty()"></span>
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
                                        <td class="lfp"><input type="radio" onclick="podkat_produkty(this.value)" value="'.$tablica_kat[$w]['id'].'" name="id_kat" id="kat_nr_'.$tablica_kat[$w]['id'].'" /> <label class="OpisFor" for="kat_nr_'.$tablica_kat[$w]['id'].'">'.$tablica_kat[$w]['text'].(($tablica_kat[$w]['status'] == 0) ? '<em class="TipChmurka"><b>Kategoria jest nieaktywna</b><span class="wylKat"></span></em>' : '').'</label></td>
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

                        <div id="wynik_produktow_nasz_hit" class="WynikProduktowNaszHit" style="display:none"></div>     

                        <div class="info_content" style="padding-left:5px">                
                        
                            <div id="formi" style="display:none">
                            
                                <span class="WynikNaglowekDodanie">Ustaw parametry dodawanego hitu</span>
                            
                                <p>
                                    <label for="data_nasz_hit_od">Data rozpoczęcia:</label>
                                    <input type="text" name="data_nasz_hit_od" id="data_nasz_hit_od" value="" size="20"  class="datepicker" />                                        
                                </p>
                                
                                <p>
                                    <label for="data_nasz_hit_do">Data zakończenia:</label>
                                    <input type="text" name="data_nasz_hit_do" id="data_nasz_hit_do" value="" size="20" class="datepicker" />                                            
                                </p>

                            </div>

                        </div>
                        
                    </div>
                    
                </div>
                
            </div>

            <div class="przyciski_dolne">
              <input type="submit" class="przyciskNon" value="Zapisz dane" id="ButZapis" style="display:none" />
              <button type="button" class="przyciskNon" onclick="cofnij('nasz_hit','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','nasz_hit');">Powrót</button>   
            </div>      

            <?php if (isset($_GET['kategoria_id']) && (int)$_GET['kategoria_id'] > 0) { ?>
            
            <script>          
            podkat_produkty(<?php echo (int)$_GET['kategoria_id']; ?>);
            </script>       

            <?php } ?>            

          </div>                      
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}
