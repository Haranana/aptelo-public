<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        $pola = array(
                array('gift_status','1'),
                array('gift_value_of',(float)$_POST['input_od']),
                array('gift_value_for',(float)$_POST['input_do']),
                array('gift_value_exclusion',(int)$_POST['suma_warunkow']),
                array('gift_products_id',(int)$_POST['id_prod']),
                array('gift_min_quantity',(int)$_POST['ilosc']),
                array('gift_only_one',(int)$_POST['jeden']),
                array('customers_group_id',((isset($_POST['grupa_klientow'])) ? implode(',', (array)$_POST['grupa_klientow']) : '')));
                
        // jezeli gratis bedzie z cena
        if ((int)$_POST['tryb_cena'] == 1) {
            //  
            $pola[] = array('gift_price',(float)$_POST['cena']);
            //
        }    

        if ($_POST['warunek'] == 'kategoria' && isset($_POST['id_kateg']) && count($_POST['id_kateg']) > 0) {
            $pola[] = array('gift_exclusion','kategorie'); 
            //
            $tablica_kat = $_POST['id_kateg'];
            $lista = '';
            for ($q = 0, $c = count($tablica_kat); $q < $c; $q++) {
                //
                $lista .= $tablica_kat[$q] . ',';
                //
            } 
            $lista = substr((string)$lista, 0, -1);
            //
            $pola[] = array('gift_exclusion_id',$lista); 
            unset($tablica_kat, $lista);
        }
        
        if ($_POST['warunek'] == 'producent' && isset($_POST['id_producent']) && count($_POST['id_producent']) > 0) {
            $pola[] = array('gift_exclusion','producenci'); 
            //
            $tablica_producent = $_POST['id_producent'];
            $lista = '';
            for ($q = 0, $c = count($tablica_producent); $q < $c; $q++) {
                //
                $lista .= $tablica_producent[$q] . ',';
                //
            } 
            $lista = substr((string)$lista, 0, -1);
            //
            $pola[] = array('gift_exclusion_id',$lista); 
            unset($tablica_producent, $lista);
        }  
        
        if ($_POST['warunek'] == 'kategoria_producent' && isset($_POST['id_kateg']) && count($_POST['id_kateg']) > 0 && isset($_POST['id_producent']) && count($_POST['id_producent']) > 0) {
            $pola[] = array('gift_exclusion','kategorie_producenci'); 
            //
            $tablica_kat = $_POST['id_kateg'];
            $lista = '';
            for ($q = 0, $c = count($tablica_kat); $q < $c; $q++) {
                //
                $lista .= $tablica_kat[$q] . ',';
                //
            } 
            $lista = substr((string)$lista, 0, -1);
            //
            //
            $tablica_producent = $_POST['id_producent'];
            $lista .= '|';
            for ($q = 0, $c = count($tablica_producent); $q < $c; $q++) {
                //
                $lista .= $tablica_producent[$q] . ',';
                //
            } 
            $lista = substr((string)$lista, 0, -1);
            //            
            $pola[] = array('gift_exclusion_id',$lista); 
            unset($tablica_kat, $lista);
        }        

        if ($_POST['warunek'] == 'produkt' && isset($_POST['id_produkt']) && count($_POST['id_produkt']) > 0) {
            $pola[] = array('gift_exclusion','produkty'); 
            //
            $tablica_produkt = $_POST['id_produkt'];
            $lista = '';
            for ($q = 0, $c = count($tablica_produkt); $q < $c; $q++) {
                //
                $lista .= $tablica_produkt[$q] . ',';
                //
            } 
            $lista = substr((string)$lista, 0, -1);
            //
            $pola[] = array('gift_exclusion_id',$lista); 
            unset($tablica_produkt, $lista);
        }           
                
        $sql = $db->insert_query('products_gift' , $pola);
        $id_dodanej_pozycji = $db->last_id_query();
        
        unset($pola);

        if (isset($id_dodanej_pozycji) && $id_dodanej_pozycji > 0) {
            Funkcje::PrzekierowanieURL('gratisy.php?id_poz='.$id_dodanej_pozycji);
        } else {
            Funkcje::PrzekierowanieURL('gratisy.php');
        }
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Dodawanie pozycji</div>
    <div id="cont">
          
          <script>
          $(document).ready(function() {
            $("#gratisyForm").validate({
              rules: {
                id_prod: {
                  required: function(element) {
                    if ($("#id_prod").val() == '') {
                        return true;
                      } else {
                        return false;
                    }
                  }
                },            
                cena: {
                  required: function(element) {
                    if ($("#kwota_gratis").css('display') == 'block') {
                        return true;
                      } else {
                        return false;
                    }
                  },
                  range: [0.01, 1000000],
                  number: true                  
                },   
                ilosc: {
                  range: [0, 100000],
                  number: true
                },                
                input_od: {
                  required: true,
                  range: [1, 1000000],
                  number: true
                },  
                input_do: {
                  required: true,
                  range: [1, 1000000],
                  number: true
                }                
              },
              messages: {
                id_prod: {
                  required: "Nie został wybrany produkt.",
                },            
                cena: {
                  required: "Pole jest wymagane.",
                },
                ilosc: {
                  range: "Wartość musi być wieksza od 0."
                },                  
                input_od: {
                  required: "Pole jest wymagane.",
                },
                input_do: {
                  required: "Pole jest wymagane.",
                }                
              }
            });
          });
          
          function anuluj_minus(elem) {
            if ($(elem).val() < 0) {
                $(elem).val( $(elem).val() * -1 );
            }
          }
          
          function warun(elem) {
             $('#Kategorie').css('display','none').css({'margin-bottom' : '0px'});
             $('#Producenci').css('display','none');
             $('#Produkty').css('display','none');
             //
             if ( elem != 'Produkty' ) {
                  if ( elem != 'KategorieProducent' ) {
                       $('#' + elem).slideDown();
                  } else {                       
                       $('#Kategorie').css({'margin-bottom' : '15px'}).slideDown();
                       $('#Producenci').slideDown();
                  }
                } else {
                  lista_produktow();
             }
          }      

          function lista_produktow() {
            //
            $('#Produkty').show(); 
            $('#Produkty').html('<img src="obrazki/_loader_small.gif">');
            $.get("ajax/lista_produktow_kupony.php",
                { tok: '<?php echo Sesje::Token(); ?>', poczatek: 0 },
                function(data) { 
                    $('#Produkty').hide();
                    $('#Produkty').html(data);     
                    $('#Produkty').slideDown();                    
            }); 
          }   
          
          function doczytaj(poczatek) {
            //
            $('#ekr_preloader').css('display','block');
            $.get("ajax/lista_produktow_kupony.php",            
                { tok: '<?php echo Sesje::Token(); ?>', poczatek: poczatek },
                function(data) { 
                    $('#ekr_preloader').css('display','none');
                    $('#Doczytaj').remove();                    
                    $('#Produkty').append(data);
            }); 
          }            

          // uzywane do generowania drzewa kategorii
          function podkat_gratisy(id) {
              //
              $('#pp_'+id).html('<img src="obrazki/_loader_small.gif">');
              $.get("ajax/drzewo_podkategorie_gratisy.php",
                  { pole: id, tok: $('#tok').val() },
                  function(data) { 
                      $('#pp_'+id).css('display','none');
                      $('#pp_'+id).html(data);
                      $('#pp_'+id).css('padding-left','15px');
                      $('#pp_'+id).css('display','block');                                                           
                      //
                      $('#imgp_'+id).html('<img src="obrazki/zwin.png" onclick="podkat_gratisy_off('+ "'" + id + "'" + ')" alt="Zwiń" />'); 
                      //
                      pokazChmurki();
                      //
              });
          }
          function podkat_gratisy_off(id, typ) {
              //
              $('#pp_'+id).css('display','none');
              $('#pp_'+id).css('padding','0px');
              $('#imgp_'+id).html('<img src="obrazki/rozwin.png" onclick="podkat_gratisy('+ "'" + id + "'" + ')" alt="Rozwiń" />'); 
          }          
          </script>     

          <form action="gratisy/gratisy_dodaj.php" method="post" id="gratisyForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Dodawanie danych</div>
            
            <div class="pozycja_edytowana">
            
                <div class="info_content">
            
                <input type="hidden" name="akcja" value="zapisz" />
                
                <p>
                  <label>Produkt który będzie gratisem:</label>
                </p>
                
                <div class="WybieranieKategorii">

                    <div class="GlownyListing">

                        <div class="GlownyListingKategorieEdycja"> 
                        
                            <div id="fraza">
                                <div>Wyszukaj produkt: <input type="text" size="15" value="" id="szukany" /><em class="TipIkona"><b>Wpisz nazwę produktu lub kod producenta</b></em></div><span onclick="fraza_produkty()"></span>
                            </div>
                            
                            <div id="drzewo" style="margin:0px">
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
                                unset($podkategorie);   
                                ?>            
                            </div>   
                            
                        </div>
                        
                        <div style="GlownyListingProduktyEdycja">  
                            
                            <input type="hidden" id="rodzaj_modulu" value="gratisy" />
                            <div id="wynik_produktow_gratisy" class="WynikProduktowGratisy"></div>                     
                            
                        </div>
                        
                    </div>
                    
                </div>

                <p class="errorRwd">
                    <input type="hidden" name="id_prod" id="id_prod" value="" />
                </p>
                
                <p>
                  <label>Czy gratis będzie dodawany za darmo czy będzie miał cenę ?</label>
                  <input type="radio" value="1" name="tryb_cena" id="tryb_cena" onclick="$('#kwota_gratis').slideDown()" checked="checked" /> <label class="OpisFor" for="tryb_cena">będzie miał cenę<em class="TipIkona"><b>Umożliwia przypisanie gratisowi ceny - np 1 zł</b></em></label>
                  <input type="radio" value="2" name="tryb_cena" id="tryb_gratis" onclick="$('#kwota_gratis').slideUp()" /> <label class="OpisFor" for="tryb_gratis">będzie darmowy<em class="TipIkona"><b>Gratis będzie dodawany do zamówienia za darmo</b></em></label>
                </p>                 
                
                <div id="kwota_gratis">
                
                    <p>
                        <label class="required" for="cena">Cena brutto:</label>           
                        <input type="text" name="cena" id="cena" size="15" value="1.00" /><em class="TipIkona"><b>Wartość musi być większa od 0.01</b></em>
                    </p>  
                    
                </div>
                
                <div class="RamkaWarunki">
                
                    <b>Dodatkowe warunki wyświetlania gratisu</b>           

                    <p>
                      <label>Czy klient będzie mógł wybrać tylko ten gratis ?</label>
                      <input type="radio" value="1" name="jeden" id="jeden_tak" /><label class="OpisFor" for="jeden_tak">tak<em class="TipIkona"><b>Jeżeli klient wybierze ten gratis inne gratisy nie będą dostępne</b></em></label>
                      <input type="radio" value="0" name="jeden" id="jeden_nie" checked="checked" /><label class="OpisFor" for="jeden_nie">nie<em class="TipIkona"><b>Wybór tego gratisu nie wpływa na wybór innych gratisów</b></em></label>           
                    </p>                     

                    <table style="margin:10px 10px 10px 0px">
                        <tr>
                            <td><label>Dostępny dla grupy klientów:</label></td>
                            <td>
                                <?php                        
                                $TablicaGrupKlientow = Klienci::ListaGrupKlientow(true, 'Klienci bez rejestracji konta' );
                                foreach ( $TablicaGrupKlientow as $GrupaKlienta ) {
                                    echo '<input type="checkbox" value="' . $GrupaKlienta['id'] . '" name="grupa_klientow[]" id="grupa_klientow_' . $GrupaKlienta['id'] . '" /> <label class="OpisFor" for="grupa_klientow_' . $GrupaKlienta['id'] . '">' . $GrupaKlienta['text'] . '</label><br />';
                                }               
                                unset($TablicaGrupKlientow);
                                ?>
                            </td>
                        </tr>
                    </table> 
                    
                    <div class="ostrzezenie odlegloscRwd" style="margin-bottom:10px">Jeżeli nie zostanie wybrana żadna grupa klientów to gratis będzie dostępny dla wszystkich klientów.</div>
                
                    <p>
                        <label class="required" for="input_od">Dostępny od kwoty:</label>           
                        <input onchange="anuluj_minus(this)" type="text" name="input_od" id="input_od" size="15" value="" /><em class="TipIkona"><b>Poziom kwotowy od jakiego będzie przyznawany gratis</b></em>
                    </p>
                    
                    <p>
                        <label class="required" for="input_do">Dostępny do kwoty:</label>           
                        <input onchange="anuluj_minus(this)" type="text" name="input_do" id="input_do" size="15" value="" /><em class="TipIkona"><b>Poziom kwotowy do jakiego będzie przyznawany gratis</b></em>
                    </p>

                    <p>
                      <label>W/w kwota obliczana dla:</label>
                      <input type="radio" value="0" name="suma_warunkow" id="suma_wszystkie" checked="checked" /><label class="OpisFor" for="suma_wszystkie">suma wszystkich produktów koszyka</label>
                      <input type="radio" value="1" name="suma_warunkow" id="suma_wybrane" /><label class="OpisFor" for="suma_wybrane">suma produktów wg dodatkowych warunków gratisu</label>
                    </p>      

                    <span class="maleInfo odlegloscRwd">Suma wg warunków - jeżeli są wybrane dodatkowe warunki dostępności gratisu (kategoria, producent, produkt) to są sumowane produkty tylko wg wybranych warunków.</span>

                    <p>
                      <label for="ilosc">Minimalna ilość produktów:</label>
                      <input class="kropkaPusta" type="text" name="ilosc" id="ilosc" value="" size="3" /><em class="TipIkona"><b>Ilość produktów w koszyku od jakiej będzie wyświetlany gratis</b></em>
                    </p>                                                 
                    
                    <span class="maleInfo odlegloscRwd">Jeżeli zostaną wybrane dodatkowe warunki dostępności gratisu (kategoria, producent, produkt) to ilość produktów będzie obliczana dla n/w warunków.</span>
                                                
                    <p>
                      <label>Dostępny tylko dla:</label>
                      <input type="radio" value="kategoria" name="warunek" id="warunek_kategorie" onclick="warun('Kategorie')" checked="checked" /><label class="OpisFor" for="warunek_kategorie">wybranych kategorii<em class="TipIkona"><b>Gratis będzie dostępny tylko jeżeli w koszyku będą produkty z określnych kategorii</b></em></label>
                      <input type="radio" value="kategoria_producent" name="warunek" id="warunek_kategorie_producenci" onclick="warun('KategorieProducent')" /><label class="OpisFor" for="warunek_kategorie_producenci">wybranych kategorii i producentów<em class="TipIkona"><b>Gratis będzie dostępny tylko jeżeli w koszyku będą produkty z określnych kategorii oraz producentów</b></em></label>
                      <input type="radio" value="producent" name="warunek" id="warunek_producenci" onclick="warun('Producenci')" /><label class="OpisFor" for="warunek_producenci">wybranych producentów<em class="TipIkona"><b>Gratis będzie dostępny tylko jeżeli w koszyku będą produkty z określnych producentów</b></em></label>
                      <input type="radio" value="produkt" name="warunek" id="warunek_produkty" onclick="warun('Produkty')" /><label class="OpisFor" for="warunek_produkty">wybranych produktów<em class="TipIkona"><b>Gratis będzie dostępny tylko jeżeli w koszyku będą określone produkty</b></em></label>
                    </p>                        
                    
                    <div id="Kategorie">
                    
                        <div id="drzewo_kategorii" style="margin:0px">
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
                                        <td class="lfp"><input type="checkbox" value="'.$tablica_kat[$w]['id'].'" name="id_kateg[]" id="kateg_nr_'.$tablica_kat[$w]['id'].'" /> <label class="OpisFor" for="kateg_nr_'.$tablica_kat[$w]['id'].'">'.$tablica_kat[$w]['text'].(($tablica_kat[$w]['status'] == 0) ? '<div class="wylKat TipChmurka"><b>Kategoria do której należy produkt jest wyłączona</b>' : '').'</label></td>
                                        <td class="rgp" '.(($podkategorie) ? 'id="imgp_'.$tablica_kat[$w]['id'].'"' : '').'>'.(($podkategorie) ? '<img src="obrazki/rozwin.png" alt="Rozwiń" onclick="podkat_gratisy(\''.$tablica_kat[$w]['id'].'\')" />' : '').'</td>
                                      </tr>
                                      '.(($podkategorie) ? '<tr><td colspan="2"><div id="pp_'.$tablica_kat[$w]['id'].'"></div></td></tr>' : '').'';
                            }
                            if ( count($tablica_kat) == 0 ) {
                                 echo '<tr><td colspan="9" style="padding:10px">Brak wyników do wyświetlania</td></tr>';
                            }                                                                
                            echo '</table>';
                            unset($podkategorie); 
                            ?>            
                        </div> 
                        
                    </div>
                    
                    <div id="Producenci" style="display:none">
                    
                        <div id="DrzewoProducentow">
                        
                            <?php
                            $Prd = Funkcje::TablicaProducenci();

                            for ($b = 0, $c = count($Prd); $b < $c; $b++) {
                                //
                                echo '<input type="checkbox" value="'.$Prd[$b]['id'].'" name="id_producent[]" id="id_producent_'.$Prd[$b]['id'].'" /> <label class="OpisFor" for="id_producent_'.$Prd[$b]['id'].'">'.$Prd[$b]['text'] . '</label><br />';
                            }
                            
                            if ( count($Prd) == 0 ) {
                                 echo '<div style="padding:10px">Brak wyników do wyświetlania</div>';
                            }                                

                            unset($Prd);
                            ?>
                            
                        </div>
                            
                    </div>
                    
                    <div id="Produkty" style="display:none"></div>

                    <span class="ostrzezenie odlegloscRwd" style="margin-top:10px">
                        Jeżeli nie zostanie wybrana żadna kategoria, producent czy produkt - gratis będzie dostępny tylko w oparciu o wartość kwotową koszyka.
                    </span>                 

                </div>
                
                </div>

            </div>

            <div class="przyciski_dolne">
              <?php
              if ( count($tablica_kat) > 0 ) {
              ?>
              <input type="submit" class="przyciskNon" value="Zapisz dane" />
              <?php
              }
              ?>
              <button type="button" class="przyciskNon" onclick="cofnij('gratisy','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','gratisy');">Powrót</button>   
            </div>            

          </div>                      
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}
