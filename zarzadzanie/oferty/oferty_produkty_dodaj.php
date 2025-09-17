<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if ( ( !isset($_GET['oferta_id']) || (int)$_GET['oferta_id'] == 0 ) && !isset($_POST['akcja']) ) {
         Funkcje::PrzekierowanieURL('oferty.php');
    }

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        $pola = array(array('offers_id',(int)$_POST['id_oferty']),
                      array('products_id',(int)$_POST['id_produktu']),
                      array('products_name',$filtr->process($_POST['nazwa'])),
                      array('products_link',$filtr->process($_POST['link'])),
                      array('products_price',(float)$_POST['cena_podstawa']),
                      array('products_price_tax',(float)$_POST['brut_podstawa']),
                      array('products_quantity',(float)$_POST['ilosc']),
                      array('products_model',$filtr->process($_POST['nr_katalogowy'])),
                      array('products_man_code',$filtr->process($_POST['kod_producenta'])),
                      array('sort',(int)$_POST['sort']),
                      array('products_description',$filtr->process($_POST['edytor'])));
                      
        if ( (int)$_POST['zdjecie'] == 1 ) {
            //
            if ( $_POST['foto_produktu'] == 'inne_zdjecie' ) {
                 //
                 $pola[] = array('products_image', $filtr->process($_POST['foto_inne']));
                 //
              } else {
                 //
                 $pola[] = array('products_image', $filtr->process($_POST['foto_produktu']));
                 //
            }
            //
        }
        //	
        $sql = $db->insert_query('offers_products', $pola);
        $id_dodanej_pozycji = $db->last_id_query();
        
        unset($pola);
        
        Funkcje::PrzekierowanieURL('oferty_produkty.php?oferta_id='.(int)$_POST['id_oferty'].'&id_poz='.$id_dodanej_pozycji);
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Dodawanie pozycji</div>
    <div id="cont">

          <form action="oferty/oferty_produkty_dodaj.php" method="post" id="poForm" class="cmxform">    

          <script>        
          function funkcja_produktu(id) {
              $('#WyborProduktu').html('<img style="margin-left:10px" src="obrazki/_loader_small.gif">');
              //
              $.get("ajax/oferta.php", 
                  { id: id, tok: $('#tok').val() },
                  function(data) { 
                      $('#WyborProduktu').hide();
                      $('#WyborProduktu').html(data);                                                           
                      $('#WyborProduktu').slideDown();
              });                
          }    
          </script>
          
          <div class="poleForm">
            <div class="naglowek">Dodawanie danych</div>
            
            <div class="pozycja_edytowana">
            
                <div class="info_content">
            
                    <input type="hidden" name="akcja" value="zapisz" />
                    
                    <input type="hidden" id="rodzaj_modulu" value="oferty" />
                    
                    <input type="hidden" name="id_oferty" value="<?php echo (int)$_GET['oferta_id']; ?>" />
                    
                    <div id="WyborProduktu">
                    
                        <div class="GlownyListing">

                            <div class="GlownyListingKategorieEdycja">             

                                <p style="font-weight:bold">
                                Wyszukaj produkt lub wybierz kategorię z której<br /> chcesz wybrać produkt do utworzenia oferty
                                </p>
                                
                                <div style="margin-left:10px;margin-top:7px;" id="fraza">
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
                                                <td class="lfp"><input type="radio" onclick="podkat_produkty(this.value)" value="'.$tablica_kat[$w]['id'].'" name="id_kat" id="kat_nr_'.$tablica_kat[$w]['id'].'" /> <label class="OpisFor" for="kat_nr_'.$tablica_kat[$w]['id'].'">'.$tablica_kat[$w]['text'].(($tablica_kat[$w]['status'] == 0) ? '<div class="wylKat TipChmurka"><b>Kategoria jest nieaktywna</b></div>' : '').'</label></td>
                                                <td class="rgp" '.(($podkategorie) ? 'id="img_'.$tablica_kat[$w]['id'].'"' : '').'>'.(($podkategorie) ? '<img src="obrazki/rozwin.png" alt="Rozwiń" title="Rozwiń" onclick="podkat(\''.$tablica_kat[$w]['id'].'\',\'\',\'radio\')" />' : '').'</td>
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
                            
                            <div style="GlownyListingProduktyEdycja">                            

                                    <div id="wynik_produktow_oferty" class="WynikProduktowOferty" style="display:none"></div>     

                            </div>
                            
                        </div>
                        
                    </div>
                
                </div>
                
            </div>

            <div class="przyciski_dolne">
              <input type="submit" class="przyciskNon" value="Zapisz dane" id="ButZapis" style="display:none" />
              <button type="button" class="przyciskNon" onclick="cofnij('oferty_produkty','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz','oferta_id')); ?>','oferty');">Powrót</button>   
            </div>      

          </div>                      
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}
