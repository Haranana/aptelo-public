<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //    
        // cechy produktu
        $cechyProduktu = array();
        //
        if ( isset($_POST['cecha']) ) {
             //
             foreach ( $_POST['cecha'] as $id => $wartosc ) {
                 //
                 $cechyProduktu[$id] = $id . '-' . $wartosc;
                 //              
             }
             //
             ksort($cechyProduktu);
             //             
        }
        //
        $Produkt = new Produkt((int)$_POST['id_prod']);
        //     
        $pola = array(
                array('products_id',(int)$_POST['id_prod']),
                array('products_name',$Produkt->info['nazwa']),
                array('products_stock_attributes', implode('x', (array)$cechyProduktu)));
     
        $db->update_query('allegro_auctions' , $pola, 'allegro_id = ' . (int)$_POST['id_aukcji']);	
        
        Funkcje::PrzekierowanieURL('allegro_aukcje.php?id_poz=' . (int)$_POST['id_aukcji']);
        
    }
    
    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Dodawanie pozycji</div>
    
    <div id="cont">
    
        <script>
        $(document).ready(function() {
          
          $("#eForm").validate({
            rules: {
              id_prod: {
                required: function(element) {
                  if ($("#id_prod").val() == '') {
                      return true;
                    } else {
                      return false;
                  }
                }
              }         
            },
            messages: {
              id_prod: {
                required: "Nie został wybrany produkt."
              }            
            }
          });
                      
        });   
        
        function pokaz_allegro_cechy(id) {
          
          $('#WyborCechy').html('<div style="margin:10px"><img src="obrazki/_loader_small.gif" alt="" /></div>');
          
          $.post("ajax/allegro_cechy_produktu.php?tok=" + $('#tok').val(),
              { id_produktu: id },
              function(data) { 
                  $('#WyborCechy').hide();
                  $('#WyborCechy').html(data);
                  $('#WyborCechy').slideDown();
                  $('#przypisz_produkt').show();
              }           
          );  

        }  
        </script>
    
        <form action="allegro/allegro_przypisz_produkt.php" method="post" id="eForm" class="cmxform">          

        <div class="poleForm">
        
          <div class="naglowek">Zmiana danych</div>
          
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "SELECT * FROM allegro_auctions WHERE allegro_id = '" . (int)$_GET['id_poz'] . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
            
                $info = $sql->fetch_assoc();
                
                ?>
                
                <div class="pozycja_edytowana">
                
                    <input type="hidden" name="akcja" value="zapisz" />
                    <input type="hidden" name="id_aukcji" value="<?php echo $info['allegro_id']; ?>" />

                    <div id="DaneProduktu">

                        <div class="GlownyListing">

                            <div class="GlownyListingKategorieEdycja">
                                      
                                <p style="font-weight:bold">Przypisanie produktu w sklepie do aukcji Allegro</p>                  

                                <div id="fraza" style="margin-left:10px;margin-top:7px">
                                    <div>Wyszukaj produkt: <input type="text" size="15" value="" id="szukany" /></div> <span onclick="fraza_produkty()"></span>
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
                                                <td class="lfp"><input type="radio" onclick="podkat_produkty(this.value)" value="'.$tablica_kat[$w]['id'].'" name="id_kat" id="id_kat_' . $tablica_kat[$w]['id'] . '" /><label class="OpisFor" for="id_kat_' . $tablica_kat[$w]['id'] . '">'.$tablica_kat[$w]['text'].'</label></td>
                                                <td class="rgp" '.(($podkategorie) ? 'id="img_'.$tablica_kat[$w]['id'].'"' : '').'>'.(($podkategorie) ? '<img src="obrazki/rozwin.png" alt="Rozwiń" onclick="podkat(\''.$tablica_kat[$w]['id'].'\',\'\',\'radio\')" />' : '').'</td>
                                              </tr>
                                              '.(($podkategorie) ? '<tr><td colspan="2"><div id="p_'.$tablica_kat[$w]['id'].'"></div></td></tr>' : '').'';
                                    }
                                    echo '</table>';
                                    unset($tablica_kat,$podkategorie);   
                                    ?>            
                                </div>
                                
                            </div>
                            
                            <div class="GlownyListingProduktyEdycja">  
                                
                                <input type="hidden" id="rodzaj_modulu" value="allegro_produkty" />
                                <div id="wynik_produktow_allegro_produkty" class="WynikProduktowAllegroProdukty"></div> 

                            </div>
                            
                        </div>

                        <p class="errorRwd">
                          <input type="hidden" name="id_prod" id="id_prod" value="" />
                          <label for="id_prod" generated="true" class="error" style="display:none;margin-left:10px !important">Nie został wybrany produkt.</label>
                        </p>  

                        <div id="WyborCechy"></div>
                        
                    </div>

                </div>
                
                <div class="przyciski_dolne">
                  <input type="submit" id="przypisz_produkt" style="display:none" class="przyciskNon" value="Zapisz dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('allegro_aukcje','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','allegro');">Powrót</button>   
                </div> 
                
                <?php
                unset($info);
        
            } else {
            
                echo '<div class="pozycja_edytowana">Brak danych do wyświetlenia</div>';
            
            }
            
            $db->close_query($sql);
            unset($zapytanie);            
            ?>
            
        </div>
    
    </div>
     
    <?php
    include('stopka.inc.php');
    
}
?>