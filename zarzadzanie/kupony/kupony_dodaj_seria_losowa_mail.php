<?php
function los($dlugosc = 1) {
    $tabelka = '1234567890QWERTYUIOPASDFGHJKKLZXCVBNM';
    $id = '';
    for ($i=0; $i<=$dlugosc; $i++)
    {
        $id .= $tabelka[rand()%(strlen((string)$tabelka))];
    } 
    return $id;
}

chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Dodawanie pozycji</div>
    <div id="cont">
          
          <script>
          $(document).ready(function() {
            $("#kuponyForm").validate({
              rules: {
                pref: {
                  required: true,
                },             
                rabat_kwota: {
                  range: [0.01, 100000],
                  number: true,
                  required: function(element) {
                    if ($("#rodzaj_kwota").css('display') == 'block') {
                        return true;
                      } else {
                        return false;
                    }
                  },                  
                },
                rabat_procent: {
                  range: [0.01, 100],
                  number: true,
                  required: function(element) {
                    if ($("#rodzaj_procent").css('display') == 'block') {
                        return true;
                      } else {
                        return false;
                    }
                  }, 
                },
                ilosc: {
                  range: [1, 100000],
                  number: true,
                },
                wartosc: {
                  range: [1, 100000],
                  number: true,
                },
                ilosc_max: {
                  range: [0, 100000],
                  number: true
                },
                wartosc_max: {
                  range: [1, 100000],
                  number: true
                },                 
                ilosc_kuponow: {
                  range: [1, 100000],
                  number: true,
                  required: true,
                }                
              },
              messages: {
                pref: {
                  required: "Pole jest wymagane.",
                },
                ilosc_generowana: {
                  required: "Pole jest wymagane.",
                  range: "Wartość musi być wieksza lub równa 1.",
                },      
                liczba_znakow: {
                  required: "Pole jest wymagane.",
                  range: "Wartość musi być wieksza lub równa 1.",
                },                
                rabat_kwota: {
                  required: "Pole jest wymagane.",
                  range: "Wartość musi być wieksza lub równa 0.01",
                },
                rabat_procent: {
                  required: "Pole jest wymagane.",
                  range: "Wartość musi być wieksza lub równa 0.01",
                },              
                ilosc: {
                  range: "Wartość musi być wieksza lub równa 1.",
                },
                wartosc: {
                  range: "Wartość musi być wieksza lub równa 1.",
                },
                ilosc_max: {
                  range: "Wartość musi być wieksza lub równa 1."
                },
                wartosc_max: {
                  range: "Wartość musi być wieksza lub równa 1."
                },                  
                ilosc_kuponow: {
                  required: "Pole jest wymagane.",
                  range: "Wartość musi być wieksza lub równa 1.",
                }                 
              }
            });
            
            $('input.datepicker').Zebra_DatePicker({
               format: 'd-m-Y',
               inside: false,
               readonly_element: false
            });             
            
          });
          
          function rodzaj_rabat(elem) {
             $('#rodzaj_kwota').slideUp();
             $('#rodzaj_procent').slideUp();
             $('#rodzaj_wysylka').slideUp();
             //
             if (elem != '') {
                $('#rodzaj_' + elem).slideDown();
             }
          }         
          
          function warun(elem) {
             $('#Kategorie').css('display','none');
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

          function lista_produktow_powiazanych(akcja) {
            //
            if ( akcja == 0 ) {
                 $('#ProduktyPowiazane').html('');
                 $('#ProduktyPowiazane').slideUp();    
            } else {
                $('#ProduktyPowiazane').show(); 
                $('#ProduktyPowiazane').html('<img src="obrazki/_loader_small.gif">');
                $.get("ajax/lista_produktow_powiazanych_kupony.php",
                    { tok: '<?php echo Sesje::Token(); ?>' },
                    function(data) { 
                        $('#ProduktyPowiazane').hide();
                        $('#ProduktyPowiazane').html(data);     
                        $('#ProduktyPowiazane').slideDown();                    
                });
            }                
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
          </script>        

          <form action="kupony/kupony_dodaj_seria_losowa_mail_wyslij.php" method="post" id="kuponyForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Dodawanie danych</div>
            
            <div class="pozycja_edytowana">
            
                <div class="info_content">
            
                    <input type="hidden" name="akcja" value="zapisz" />
                
                    <strong class="NaglowekEdycji">Dane dotyczące generowania kuponu</strong>

                    <p>
                      <label class="required" for="pref">Prefix do kodu kuponu:</label>
                      <input type="text" name="pref" id="pref" value="" size="10" /><em class="TipIkona"><b>Dowolny ciąg znaków ktory będzie na początku nazwy kuponów</b></em>
                    </p> 

                    <p>
                      <label for="opis">Opis kuponu:</label>
                      <input type="text" name="opis" id="opis" value="" size="50" /><em class="TipIkona"><b>Opis kuponu - widoczny tylko dla administratora sklepu</b></em>
                    </p>
                    
                    <p>
                      <label>Rodzaj rabatu:</label>
                      <input type="radio" value="fixed" name="rodzaj" id="rodzaj_kwotowy" onclick="rodzaj_rabat('kwota')" checked="checked" /><label class="OpisFor" for="rodzaj_kwotowy">kwotowy<em class="TipIkona"><b>Rabat jest stały kwotowy</b></em></label>
                      <input type="radio" value="percent" name="rodzaj" id="rodzaj_procentowy" onclick="rodzaj_rabat('procent')" /><label class="OpisFor" for="rodzaj_procentowy">procentowy<em class="TipIkona"><b>Rabat obliczany jest procentowo od wartości zamówienia</b></em></label>
                      <input type="radio" value="shipping" name="rodzaj" id="rodzaj_wysylki" onclick="rodzaj_rabat('wysylka')" /> <label class="OpisFor" for="rodzaj_wysylki">darmowa wysyłka<em class="TipIkona"><b>Rabat równy kosztom wysyłki - umożliwia darmową wysyłkę</b></em></label>
                    </p>
                    
                    <div id="rodzaj_kwota">
                      <p>
                          <label class="required" for="rabat_kwota">Wartość rabatu:</label>
                          <input type="text" name="rabat_kwota" id="rabat_kwota" value="" size="10" /><em class="TipIkona"><b>Wartość kwotowa powyżej 0.01</b></em>
                      </p>
                    </div>
                    
                    <div id="rodzaj_wysylka" style="display:none;margin:10px">
                    
                        <?php
                        $ListaWysylek = array();
                        $sql_wysylki = $db->open_query("SELECT id, nazwa, klasa FROM modules_shipping WHERE status = '1' order by sortowanie");
                        //
                        $i18n = new Translator($db, $_SESSION['domyslny_jezyk']['id']);
                        $tlumacz = $i18n->tlumacz('WYSYLKI');
                        //
                        while ($wysylki = $sql_wysylki->fetch_assoc()) {
                               //
                               $ListaWysylek[$wysylki['id']] = $tlumacz['WYSYLKA_'.$wysylki['id'].'_TYTUL'] . ' (' . $wysylki['nazwa'] . ')';
                               //
                        }
                        //
                        unset($tlumacz);
                        $db->close_query($sql_wysylki);
                        ?>
                        
                        <table>
                            <tr>
                                <td><label>Dostępna tylko dla wysyłek:</label></td>
                                <td style="padding-left:4px">
                                    <?php                        
                                    foreach ( $ListaWysylek as $Klucz => $TmpWysylka ) {
                                        echo '<input type="checkbox" value="' . $Klucz . '" name="id_wysylki[]" id="id_wysylki_' . $Klucz . '" /> <label class="OpisFor" for="id_wysylki_' . $Klucz . '">' . $TmpWysylka . '</label><br />';
                                    }               
                                    unset($ListaWysylek);
                                    ?>
                                </td>
                            </tr>
                        </table>                         
                        
                        <div class="maleInfo odlegloscRwd" style="margin-bottom:10px">Jeżeli nie zostanie wybrana żadna forma wysyłki to kupon będzie dostępny dla wszystkich wysyłek.</div>
                        
                    </div>                    
                    
                    <div id="rodzaj_procent" style="display:none">
                      <p>
                          <label class="required" for="rabat_procent">Wartość rabatu (w %):</label>
                          <input type="text" name="rabat_procent" id="rabat_procent" value="" size="3" /><em class="TipIkona"><b>Wartość procentowa od 0.01 do 100</b></em>
                      </p>
                    </div>
                    
                    <p>
                        <label for="data_od">Data rozpoczęcia:</label>
                        <input type="text" name="data_od" id="data_od" value="" size="20" class="datepicker" />                                        
                    </p>

                    <p>
                        <label for="data_do">Data zakończenia:</label>
                        <input type="text" name="data_do" id="data_do" value="" size="20" class="datepicker" />                                        
                    </p>

                    <div class="RamkaWarunki">
                    
                        <b>Dodatkowe warunki użycia kuponu</b>
                        
                        <table style="margin:10px 0 10px 0">
                            <tr>
                                <td><label>Dostępny tylko dla wysyłek do kraju:</label></td>
                                <td style="padding-left:4px">
                                    <div style="max-height:200px;width:500px;overflow-y:auto">
                                        <?php
                                        $KrajeTablica = array();
                                        $KrajeTablica[] = array('id' => '0', 'text' => '-- wszystkie --');
                                        //
                                        $sqlKraje = $db->open_query("SELECT * FROM countries s, countries_description sd WHERE s.countries_id = sd.countries_id AND sd.language_id = '" . $_SESSION['domyslny_jezyk']['id'] . "' order by sd.countries_name");
                                        while ($infk = $sqlKraje->fetch_assoc()) {
                                            //
                                            echo '<input type="checkbox" value="' . $infk['countries_id'] . '" name="kraj[]" id="kraj_' . $infk['countries_id'] . '" /> <label class="OpisFor" for="kraj_' . $infk['countries_id'] . '">' . $infk['countries_name'] . '</label><br />';
                                            //
                                        }
                                        $db->close_query($sqlKraje);
                                        //
                                        unset($KrajeTablica);
                                        ?>
                                    </div>
                                </td>
                            </tr>
                        </table>  

                        <div class="ostrzezenie odlegloscRwd" style="margin-bottom:10px">Jeżeli nie zostanie wybrany żaden kraj to kupon będzie dostępny dla wszystkich państw.</div>
                           
                        <table style="margin:10px 0 10px 0">
                            <tr>
                                <td><label>Dostępny dla form płatności:</label></td>
                                <td style="padding-left:4px">
                                    <div style="max-height:200px;width:500px;overflow-y:auto">
                                        <?php                        
                                        $TablicaModulowPlatnosci = Moduly::TablicaPlatnosciId(false);
                                        foreach ( $TablicaModulowPlatnosci as $ModulPlatnosci ) {
                                            echo '<input type="checkbox" value="' . $ModulPlatnosci['id'] . '" name="modul_platnosci[]" id="modul_platnosci_' . $ModulPlatnosci['id'] . '" /> <label class="OpisFor" for="modul_platnosci_' . $ModulPlatnosci['id'] . '">' . $ModulPlatnosci['text'] . '</label><br />';
                                        }               
                                        unset($TablicaModulowPlatnosci);
                                        ?>
                                    </div>
                                </td>
                            </tr>
                        </table>       

                        <div class="ostrzezenie odlegloscRwd" style="margin-bottom:10px">Jeżeli nie zostanie wybrana żadna forma płatności to kupon będzie dostępny dla wszystkich dostępnych płatności.</div>
                                                
                        <table style="margin:10px 0 10px 0">
                            <tr>
                                <td><label>Dostępny dla grupy klientów:</label></td>
                                <td style="padding-left:4px">
                                    <div style="max-height:200px;width:500px;overflow-y:auto">
                                        <?php                        
                                        $TablicaGrupKlientow = Klienci::ListaGrupKlientow(true, 'Klienci bez rejestracji konta' );
                                        foreach ( $TablicaGrupKlientow as $GrupaKlienta ) {
                                            echo '<input type="checkbox" value="' . $GrupaKlienta['id'] . '" name="grupa_klientow[]" id="grupa_klientow_' . $GrupaKlienta['id'] . '" /> <label class="OpisFor" for="grupa_klientow_' . $GrupaKlienta['id'] . '">' . $GrupaKlienta['text'] . '</label><br />';
                                        }               
                                        unset($TablicaGrupKlientow);
                                        ?>
                                    </div>
                                </td>
                            </tr>
                        </table> 
                        
                        <div class="ostrzezenie odlegloscRwd" style="margin-bottom:10px">Jeżeli nie zostanie wybrana żadna grupa klientów to kupon będzie dostępny dla wszystkich klientów.</div>                                                   
                        
                        <p>
                          <label for="rodzaj_waluta">Dostępny tylko dla waluty:</label>
                          <?php
                          $tablica_walut = array();
                          $tablica_walut[] = array('id' => '',
                                                   'text' => '-- dowolna --');
                                                       
                          $zapytanie = "select currencies_id, title, code, currencies_marza, value from currencies";
                       
                          $sqlw = $db->open_query($zapytanie);   
                          while ($wynik = $sqlw->fetch_assoc()) {
                              $tablica_walut[] = array('id' => $wynik['code'],
                                                       'text' => $wynik['title']);
                          }
                          $db->close_query($sqlw); 
                          unset($zapytanie);      

                          echo Funkcje::RozwijaneMenu('rodzaj_waluta', $tablica_walut, '','style="width:200px" id="rodzaj_waluta"');
                          unset($tablica_walut);
                          ?>
                        </p>                       
                        
                        <p>
                          <label>Czy wyświetlać kupon na karcie produktu ?</label>
                          <input type="radio" value="0" name="widoczny" id="widoczny_tak" checked="checked" /><label class="OpisFor" for="widoczny_tak">tak<em class="TipIkona"><b>Czy kupon ma być widoczny dla wszystkich klientów na karcie produktu ?</b></em></label>
                          <input type="radio" value="1" name="widoczny" id="widoczny_nie" /><label class="OpisFor" for="widoczny_nie">nie<em class="TipIkona"><b>Czy kupon ma być widoczny dla wszystkich klientów na karcie produktu ?</b></em></label>
                        </p>                          

                        <p>
                          <label for="ilosc">Minimalna ilość produktów:</label>
                          <input class="kropkaPusta" type="text" name="ilosc" id="ilosc" value="" size="3" /><em class="TipIkona"><b>Ilość produktów w koszyku od jakiej będzie można zrealizować kupon</b></em>
                        </p> 
                        
                        <p>
                          <label for="ilosc_max">Maksymalna ilość produktów:</label>
                          <input class="kropkaPusta" type="text" name="ilosc_max" id="ilosc_max" value="" size="3" /><em class="TipIkona"><b>Maksymalna ilość produktów w koszyku do jakiej będzie można zrealizować kupon</b></em>
                        </p>                          

                        <p>
                          <label for="wartosc">Minimalna wartość zamówienia:</label>
                          <input class="kropkaPusta" type="text" name="wartosc" id="wartosc" value="" size="10" /><em class="TipIkona"><b>Wartość zamówienia od jakiej będzie można zrealizować kupon</b></em>
                        </p>  
                        
                        <p>
                          <label for="wartosc_max">Maksymalna wartość zamówienia:</label>
                          <input class="kropkaPusta" type="text" name="wartosc_max" id="wartosc_max" value="" size="10" /><em class="TipIkona"><b>Maksymalna wartość zamówienia do jakiej będzie można zrealizować kupon</b></em>
                        </p>    
                        
                        <p>
                          <label>Produkty promocyjne:</label>
                          <input type="radio" value="1" name="promocja" id="promocja_tak" checked="checked" /><label class="OpisFor" for="promocja_tak">tak<em class="TipIkona"><b>Czy kuponem mają być objęte produkty promocyjne ?</b></em></label>
                          <input type="radio" value="0" name="promocja" id="promocja_nie" /><label class="OpisFor" for="promocja_nie">nie<em class="TipIkona"><b>Czy kuponem mają być objęte produkty promocyjne ?</b></em></label>
                        </p>                         
                                                                
                        <p>
                          <label>Czy kupon przez jednego klienta może być użyty tylko 1 raz ?</label>
                          <input type="radio" value="1" name="uzycie_kuponu" id="uzycie_kuponu_tak" checked="checked" /><label class="OpisFor" for="uzycie_kuponu_tak">tak<em class="TipIkona"><b>Klient będzie mógł tylko raz użyć kupon (sprawdzany adres email i nr telefonu)</b></em></label>
                          <input type="radio" value="0" name="uzycie_kuponu" id="uzycie_kuponu_nie" /><label class="OpisFor" for="uzycie_kuponu_nie">nie<em class="TipIkona"><b>Klient może wiele razu użyć kupon</b></em></label>
                        </p>        

                        <p>
                          <label>Czy kupon może być użyty tylko na pierwsze zakupy klienta ?</label>
                          <input type="radio" value="1" name="pierwsze_zakupy" id="pierwsze_zakupy_tak" /><label class="OpisFor" for="pierwsze_zakupy_tak">tak<em class="TipIkona"><b>Klient będzie mógł tylko na pierwsze zakupy w sklepie (sprawdzany adres email i nr telefonu)</b></em></label>
                          <input type="radio" value="0" name="pierwsze_zakupy" id="pierwsze_zakupy_nie" checked="checked" /><label class="OpisFor" for="pierwsze_zakupy_nie">nie<em class="TipIkona"><b>Klient może wiele razu użyć kupon</b></em></label>
                        </p>                          
                    
                        <span class="maleInfo odlegloscRwd">Jeżeli zostaną wybrane dodatkowe warunki dostępności gratisu (kategoria, producent, produkt) to ilość produktów będzie obliczana dla n/w warunków.</span>
                                                    
                        <p>
                          <label>Dostępny tylko dla:</label>
                          <input type="radio" value="kategoria" name="warunek" id="warunek_kategorie" onclick="warun('Kategorie')" checked="checked" /><label class="OpisFor" for="warunek_kategorie">wybranych kategorii<em class="TipIkona"><b>Gratis będzie dostępny tylko jeżeli w koszyku będą produkty z określnych kategorii</b></em></label>
                          <input type="radio" value="kategoria_producent" name="warunek" id="warunek_kategorie_producenci" onclick="warun('KategorieProducent')" /><label class="OpisFor" for="warunek_kategorie_producenci">wybranych kategorii i producentów<em class="TipIkona"><b>Kupon będzie można wykorzystać tylko w określnych kategoriach i dla określonych producentów</b></em></label>
                          <input type="radio" value="producent" name="warunek" id="warunek_producenci" onclick="warun('Producenci')" /><label class="OpisFor" for="warunek_producenci">wybranych producentów<em class="TipIkona"><b>Gratis będzie dostępny tylko jeżeli w koszyku będą produkty z określnych producentów</b></em></label>
                          <input type="radio" value="produkt" name="warunek" id="warunek_produkty" onclick="warun('Produkty')" /><label class="OpisFor" for="warunek_produkty">wybranych produktów<em class="TipIkona"><b>Gratis będzie dostępny tylko jeżeli w koszyku będą określone produkty</b></em></label>
                        </p>                           

                        <div id="Kategorie">
                        
                            <div id="drzewo" style="margin:0px;">
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
                                            <td class="lfp"><input type="checkbox" value="'.$tablica_kat[$w]['id'].'" name="id_kat[]" id="kateg_nr_'.$tablica_kat[$w]['id'].'" /> <label class="OpisFor" for="kateg_nr_'.$tablica_kat[$w]['id'].'">'.$tablica_kat[$w]['text'].(($tablica_kat[$w]['status'] == 0) ? '<div class="wylKat TipChmurka"><b>Kategoria jest nieaktywna</b></div>' : '').'</label></td>
                                            <td class="rgp" '.(($podkategorie) ? 'id="img_'.$tablica_kat[$w]['id'].'"' : '').'>'.(($podkategorie) ? '<img src="obrazki/rozwin.png" alt="Rozwiń" onclick="podkat(\''.$tablica_kat[$w]['id'].'\',\'\',\'checkbox\')" />' : '').'</td>
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
                        
                        <div id="Producenci" style="display:none">
                        
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
                        
                        <div id="Produkty" style="display:none"></div>

                        <span class="ostrzezenie odlegloscRwd" style="margin-top:10px;margin-bottom:10px">
                            Jeżeli nie zostanie wybrana żadna kategoria, producent czy produkt - kupon będzie aktywny dla wszystkich kategorii, producentów i produktów.
                        </span>                         

                        <p>
                          <label>Dostępny tylko w powiązaniu z innym produktem ?</label>
                          <input type="radio" value="nie" name="tylko_do_produktu" id="tylko_do_produktu_nie" onclick="lista_produktow_powiazanych(0)" checked="checked" /> <label class="OpisFor" for="tylko_do_produktu_nie">nie</label>
                          <input type="radio" value="tak" name="tylko_do_produktu" id="tylko_do_produktu_tak" onclick="lista_produktow_powiazanych(1)" /> <label class="OpisFor" for="tylko_do_produktu_tak">tak<em class="TipIkona"><b>Kupon będzie można aktywować tylko jeżeli do koszyka będzie dodany określony produkt</b></em></label>
                        </p>
                        
                        <div id="ProduktyPowiazane" style="display:none"></div>

                    </div>

                    <strong class="NaglowekEdycji">Dane maila</strong>
                
                    <p>
                      <label for="newsletter">Newsletter który ma być użyty do wysłania maili:</label>
                      <?php
                      $zapytanie = 'SELECT DISTINCT newsletters_id, title FROM newsletters order by title'; 
                      $sql = $db->open_query($zapytanie);
    
                      $tablica = array();
                      while ($info = $sql->fetch_assoc()) {
                             $tablica[] = array('id' => $info['newsletters_id'],
                                                'text' => $info['title']);
                      }

                      echo Funkcje::RozwijaneMenu('newsletter', $tablica, '', 'style="width:300px" id="newsletter"'); 
       
                      $db->close_query($sql);
                      unset($tablica,$zapytanie, $info);                      
                      ?>
                    </p>                         

                </div>
             
            </div>

            <div class="przyciski_dolne">
              <input type="submit" class="przyciskNon" value="Przejdź dalej" />
              <button type="button" class="przyciskNon" onclick="cofnij('kupony','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','kupony');">Powrót</button>   
            </div>            

          </div>                      
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}