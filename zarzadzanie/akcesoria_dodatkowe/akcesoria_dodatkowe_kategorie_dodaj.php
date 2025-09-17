<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        $glowne_id = (int)$_POST['id_wybrana_kategoria'];
        $saPodobne = false;
        //
        if (isset($_POST['id_produktow'])) {
            if (count($_POST['id_produktow']) > 0) {
                //                
                foreach ($_POST['id_produktow'] as $pole) {
                    //
                    $pola = array(array('pacc_products_id_master',(int)$glowne_id),
                                  array('pacc_products_id_slave',$pole),
                                  array('pacc_type','kategoria'),
                                  array('pacc_sort_order', ((isset($_POST['sort_' . $pole])) ? (int)$_POST['sort_' . $pole] : 0)));
                    //	
                    $sql = $db->insert_query('products_accesories', $pola);
                    $saPodobne = true;
                    //
                    unset($pola);  
                }
                //
            }
        }    

        if ($saPodobne == false) {
            Funkcje::PrzekierowanieURL('akcesoria_dodatkowe_kategorie.php');
          } else {
            Funkcje::PrzekierowanieURL('akcesoria_dodatkowe_kategorie.php?id_poz='.(int)$glowne_id);
        }         

    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Dodawanie pozycji</div>
    <div id="cont">

          <form action="akcesoria_dodatkowe/akcesoria_dodatkowe_kategorie_dodaj.php" method="post" id="akcesoria_dodatkoweForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Dodawanie danych</div>
            
            <div class="pozycja_edytowana">
            
                <input type="hidden" name="akcja" value="zapisz" />
                
                <input type="hidden" id="rodzaj_modulu" value="akcesoria_dodatkowe" />

                <div class="GlownyListing">

                    <div class="GlownyListingKategorieEdycja" id="drzewo_akcesoria_dodatkowe_kategoria">
                        
                        <p style="font-weight:bold">
                        Wybierz kategorię do której chcesz przypisać akcesoria dodatkowe
                        </p>
                        
                        <script>
                        $(document).ready(function() {

                          $('.pkc td').find('input').click( function() {
                              lista_akcja_kategoria( $(this).val() );
                              $('#ButZapis').show();
                          });    
                          
                        }); 
                        </script>

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
                                        <td class="lfp"><input type="radio" value="'.$tablica_kat[$w]['id'].'" name="id_kat" id="id_kat_' . $tablica_kat[$w]['id'] . '" /> <label class="OpisFor" for="id_kat_' . $tablica_kat[$w]['id'] . '">'.$tablica_kat[$w]['text'].(($tablica_kat[$w]['status'] == 0) ? '<div class="wylKat TipChmurka"><b>Kategoria jest nieaktywna</b></div>' : '').'</label></td>
                                        <td class="rgp" '.(($podkategorie) ? 'id="img_'.$tablica_kat[$w]['id'].'"' : '').'>'.(($podkategorie) ? '<img src="obrazki/rozwin.png" alt="Rozwiń" onclick="podkat(\''.$tablica_kat[$w]['id'].'\',\'\',\'radio_akcesoria\')" />' : '').'</td>
                                      </tr>
                                      '.(($podkategorie) ? '<tr><td colspan="2"><div id="p_'.$tablica_kat[$w]['id'].'"></div></td></tr>' : '').'';
                            }
                            echo '</table>';
                            unset($tablica_kat,$podkategorie);   
                            ?> 
                            
                        </div>                        
  
                    </div>

                    <div style="GlownyListingProduktyEdycja">                               

                        <div id="wynik_produktow_akcesoria_dodatkowe" class="WynikProduktowAkcesoriaDodatkowe" style="display:none"></div>     

                        <div id="formi" style="display:none">
                        
                            <div id="wybrana_kategoria" class="WybranaKategoria"></div>
                            
                            <input type="hidden" value="," id="jakie_id" />
                            
                            <div id="wybrane_produkty"></div>
                            
                            <div id="lista_do_wyboru"></div>

                        </div>
                        
                    </div>
                    
                </div>
                
            </div>

            <div class="przyciski_dolne">
              <input type="submit" class="przyciskNon" value="Zapisz dane" id="ButZapis" style="display:none" />
              <button type="button" class="przyciskNon" onclick="cofnij('akcesoria_dodatkowe_kategorie','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','akcesoria_dodatkowe');">Powrót</button>   
            </div> 

          </div>                      
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}
