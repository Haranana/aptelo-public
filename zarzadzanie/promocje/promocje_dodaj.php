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
        $pola[] = array('products_old_price',(float)$_POST['cena_poprzednia']);
        
        // nowa cena produktu
        
        // pobieranie informacji o vat - tworzy tablice ze stawkami
        $zapytanie_vat = "select distinct * from tax_rates order by tax_rate desc";
        $sqls = $db->open_query($zapytanie_vat);
        //
        $tablicaVat = array();
        while ($infs = $sqls->fetch_assoc()) { 
            $tablicaVat[$infs['tax_rates_id']] = $infs['tax_rate'];
        }
        $db->close_query($sqls);
        unset($zapytanie_vat, $infs);  
        //                             
        $wartosc = (float)$_POST['cena_brutto'];
        $netto = round(($wartosc / (1 + ($tablicaVat[(int)$_POST['stawka_vat']]/100))), 2);
        $podatek = $wartosc - $netto;
        //
        $pola[] = array('products_price_tax',$wartosc);
        $pola[] = array('products_price',$netto);
        $pola[] = array('products_tax',$podatek);
        //
        unset($wartosc, $netto, $podatek);
        
        // ceny dla pozostalych poziomow cen
        for ( $x = 2; $x <= ILOSC_CEN; $x++ ) {
              //
              // cena poprzednia
              if ( (isset($_POST['cena_poprzednia_'.$x]) && (float)$_POST['cena_poprzednia_'.$x] > 0) && (isset($_POST['cena_brutto_'.$x]) && (float)$_POST['cena_brutto_'.$x] > 0) ) {
                  //
                  $pola[] = array('products_old_price_'.$x,(float)$_POST['cena_poprzednia_'.$x]);
                  //
                  $wartosc = (float)$_POST['cena_brutto_'.$x];
                  $netto = round(($wartosc / (1 + ($tablicaVat[(int)$_POST['stawka_vat']]/100))), 2);
                  $podatek = $wartosc - $netto;    
                  //
                  $pola[] = array('products_price_tax_'.$x,$wartosc);
                  $pola[] = array('products_price_'.$x,$netto);
                  $pola[] = array('products_tax_'.$x,$podatek);
                  //    
                  unset($wartosc, $netto, $podatek); 
                  //                
                  //
                } else {
                  //
                  $pola[] = array('products_old_price_'.$x,'0');
                  //
              }
              //
        }         
                        
        $pola[] = array('specials_status','1');
        
        if (!empty($_POST['data_promocja_od'])) {
            $pola[] = array('specials_date',date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_POST['data_promocja_od']))));
          } else {
            $pola[] = array('specials_date','0000-00-00');            
        }
        if (!empty($_POST['data_promocja_do'])) {
            $pola[] = array('specials_date_end',date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_POST['data_promocja_do']))));
          } else {
            $pola[] = array('specials_date_end','0000-00-00');            
        }
        //	
        $sql = $db->update_query('products', $pola, 'products_id = ' . $id_dodawanej_pozycji);
        
        unset($pola, $tablicaVat);
        
        Funkcje::PrzekierowanieURL('promocje.php?id_poz='.$id_dodawanej_pozycji);
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Dodawanie pozycji</div>
    <div id="cont">

          <form action="promocje/promocje_dodaj.php" method="post" id="poForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Dodawanie danych</div>
            
            <div class="pozycja_edytowana">
            
                <input type="hidden" name="akcja" value="zapisz" />
                
                <input type="hidden" id="rodzaj_modulu" value="promocje" />
                
                <script>
                $(document).ready(function() {
                  $("#poForm").validate({
                    rules: {
                      cena_poprzednia: {
                        required: true
                      },
                      cena_brutto: {
                        required: true
                      }                       
                    },
                    messages: {
                      cena_poprzednia: {
                        required: "Pole jest wymagane."
                      },
                      cena_brutto: {
                        required: "Pole jest wymagane."
                      }                        
                    }
                  });
                  
                  $('input.datepicker').Zebra_DatePicker({
                     format: 'd-m-Y H:i',
                     inside: false,
                     readonly_element: true,
                     enabled_minutes: [00, 10, 20, 30, 40, 50]
                  });                
                });
                
                function promocja(id) {
                  $('#formi').slideDown('fast');
                  $('#ButZapis').css('display','inline-block');
                  //
                  $('#danePromocji').html('<img style="margin-left:10px" src="obrazki/_loader_small.gif">');
                  //

                  $.get("ajax/promocja.php", 
                      { id: id, tok: $('#tok').val() },
                      function(data) { 
                          $('#danePromocji').hide();
                          $('#danePromocji').html(data);                                                           
                          $('#danePromocji').slideDown();
                          //
                          pokazChmurki();
                          //
                  });                   
                }


                </script>

                <div class="GlownyListing">
                        
                    <?php
                    $plik = 'promocje.php';
                    if ( isset($_SESSION['filtry'][$plik]['kategoria_id']) ) {
                         $_GET['kategoria_id'] = $_SESSION['filtry'][$plik]['kategoria_id'];
                    }
                    unset($plik);
                    ?>                        
        
                    <?php if (!isset($_GET['kategoria_id'])) { ?>

                    <div class="GlownyListingKategorieEdycja">  

                        <p style="font-weight:bold">
                        Wyszukaj produkt lub wybierz kategorię z której chcesz wybrać produkt do utworzenia promocji
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

                        <div id="wynik_produktow_promocje" class="WynikProduktowPromocje" style="display:none"></div> 
                        
                        <div class="info_content" style="padding-left:5px">                                 
                        
                            <div id="formi" style="display:none">
                            
                                <span class="WynikNaglowekDodanie">Ustaw parametry dodawanej promocji</span>
                                
                                <div id="danePromocji"></div>

                                <p>
                                    <label for="data_promocja_od">Data rozpoczęcia:</label>
                                    <input type="text" name="data_promocja_od" id="data_promocja_od" value="" size="20"  class="datepicker" />
                                </p>
                                
                                <p>
                                    <label for="data_promocja_do">Data zakończenia:</label>
                                    <input type="text" name="data_promocja_do" id="data_promocja_do" value="" size="20" class="datepicker" />
                                </p>

                            </div>

                        </div>
                        
                    </div>
                    
                </div>
                
            </div>

            <div class="przyciski_dolne">
              <input type="submit" class="przyciskNon" value="Zapisz dane" id="ButZapis" style="display:none" />
              <button type="button" class="przyciskNon" onclick="cofnij('promocje','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','promocje');">Powrót</button>   
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
