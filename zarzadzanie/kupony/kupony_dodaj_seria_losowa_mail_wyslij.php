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
    
    <div id="naglowek_cont">Wysyłanie maili z kodami kuponów rabatowych</div>
    <div id="cont">
          
          <form action="kupony/kupony_dodaj_seria_losowa_mail_wyslij.php" method="post" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Wysyłanie informacji mailem</div>
            
            <?php
            // dane odebrane postem
            $prefix = $filtr->process($_POST["pref"]);
            $opis = $filtr->process($_POST["opis"]);
            $rodzaj = $filtr->process($_POST["rodzaj"]);               
            $minwartosc = (float)$_POST["wartosc"];
            $minilosc = (float)$_POST["ilosc"];
            $maxwartosc = (float)$_POST["wartosc_max"];
            $maxilosc = (float)$_POST["ilosc_max"];
            $promocje = (int)$_POST["promocja"];
            $widoczny = (int)$_POST["widoczny"];
            $rodzaj_waluta = $filtr->process($_POST["rodzaj_waluta"]);
            $uzycie_kuponu = (int)$_POST["uzycie_kuponu"];
            $pierwsze_zakupy = (int)$_POST["pierwsze_zakupy"];
            $newsletter_id = (int)$_POST["newsletter"];
            $kraj_id = ((isset($_POST["kraj"])) ? implode(',', (array)$_POST["kraj"]) : '');
            $grupa_klientow = ((isset($_POST["grupa_klientow"])) ? implode(',', (array)$_POST["grupa_klientow"]) : '');
            $modul_platnosci = ((isset($_POST['modul_platnosci'])) ? implode(',', (array)$_POST['modul_platnosci']) : '');
            
            $rodzajwartosc = '';
            
            if ($filtr->process($_POST["rodzaj"]) == 'fixed') {
                $rodzajwartosc = $filtr->process($_POST["rabat_kwota"]);
            }
            
            if ($filtr->process($_POST["rodzaj"]) == 'percent') {
                $rodzajwartosc = $filtr->process($_POST["rabat_procent"]);
            }        
            
            if ($filtr->process($_POST["rodzaj"]) == 'shipping' && isset($_POST["id_wysylki"])) {
                $idwysylki = implode(',', (array)$filtr->process($_POST["id_wysylki"]));
            } else {
                $idwysylki = '';
            }            
            
            $datapoczatkowa = '0000-00-00';  
            
            if (!empty($_POST['data_od'])) {
                $datapoczatkowa = date('Y-m-d', FunkcjeWlasnePHP::my_strtotime($filtr->process($_POST['data_od'])));          
            }  
            
            $datakoncowa = '0000-00-00';   

            if (!empty($_POST['data_do'])) {
                $datakoncowa = date('Y-m-d', FunkcjeWlasnePHP::my_strtotime($filtr->process($_POST['data_do'])));         
            }   
            
            $warunek = '';
            $warunek_id = '';
            
            if ($_POST['warunek'] == 'kategoria' && isset($_POST['id_kat']) && count($_POST['id_kat']) > 0) {
                $warunek = 'kategoria'; 
                //
                $tablica_kat = $_POST['id_kat'];
                $lista = '';
                for ($q = 0, $c = count($tablica_kat); $q < $c; $q++) {
                    //
                    $lista .= $tablica_kat[$q] . ',';
                    //
                } 
                $lista = substr((string)$lista, 0, -1);
                //
                $warunek_id = $lista; 
                unset($tablica_kat, $lista);
            }
            
            if ($_POST['warunek'] == 'producent' && isset($_POST['id_producent']) && count($_POST['id_producent']) > 0) {
                $warunek = 'producent'; 
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
                $warunek_id = $lista; 
                unset($tablica_producent, $lista);
            }  
        
            if ($_POST['warunek'] == 'kategoria_producent' && isset($_POST['id_producent']) && count($_POST['id_producent']) > 0 && isset($_POST['id_kat']) && count($_POST['id_kat']) > 0) {
                $warunek ='kategorie_producenci';
                //
                $tablica_producent = $_POST['id_producent'];
                $lista_producent = array();
                for ($q = 0, $c = count($tablica_producent); $q < $c; $q++) {
                    //
                    $lista_producent[] = $tablica_producent[$q];
                    //
                } 
                //
                $tablica_kat = $_POST['id_kat'];
                $lista_kategoria = array();
                for ($q = 0, $c = count($tablica_kat); $q < $c; $q++) {
                    //
                    $lista_kategoria[] = $tablica_kat[$q];
                    //
                } 
                $lista = implode(',', $lista_producent) . '#' . implode(',', $lista_kategoria);
                //
                $warunek_id = $lista; 
                unset($tablica_producent, $tablica_kat, $lista_producent, $lista_kategoria);
            }    
        
            if ($_POST['warunek'] == 'produkt' && isset($_POST['id_produkt']) && count($_POST['id_produkt']) > 0) {
                $warunek = 'produkt'; 
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
                $warunek_id = $lista; 
                unset($tablica_produkt, $lista);
            }     

            $tylko_produkty = 'nie';
            $tylko_produkty_id = '';
    
            if (isset($_POST['tylko_do_produktu']) && $_POST['tylko_do_produktu'] == 'tak' && isset($_POST['id_produkt_powiazany']) && count($_POST['id_produkt_powiazany']) > 0) {
                $tylko_produkty = 'tak';
                //
                $tablica_produkt = $_POST['id_produkt_powiazany'];
                $lista = '';
                for ($q = 0, $c = count($tablica_produkt); $q < $c; $q++) {
                    //
                    $lista .= $tablica_produkt[$q] . ',';
                    //
                } 
                $lista = substr((string)$lista, 0, -1);
                //
                $tylko_produkty_id = $lista;
                unset($tablica_produkt, $lista);
            }    
            
            $zapytanie = "select * from newsletters where newsletters_id = '" .$newsletter_id . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
            
                $pola = array(array('date_sent','now()'));
                $db->update_query('newsletters' , $pola, " newsletters_id = '" . $newsletter_id . "'");
                unset($pola);            
            
                $info = $sql->fetch_assoc();
                ?>            
                
                <script>
                var ogolny_limit = <?php echo count(Newsletter::AdresyEmailNewslettera($info['newsletters_id'])); ?>;
                //
                function wyslij_mail_rabatowy(id, limit, wskaznik) {

                    if ($('#import').css('display') == 'none') {
                        $('#import').slideDown("fast");
                        $('#przyciski').slideUp("fast");
                    }
                    
                    $.post( "ajax/wyslij_kupon_rabatowy.php?tok=<?php echo Sesje::Token(); ?>", 
                          { 
                            id: <?php echo $info['newsletters_id']; ?>,
                            prefix: '<?php echo $prefix; ?>',
                            opis: '<?php echo $opis; ?>',
                            rodzaj: '<?php echo $rodzaj; ?>', 
                            kraj: '<?php echo $kraj_id; ?>', 
                            grupaklientow: '<?php echo $grupa_klientow; ?>',
                            modulplatnosci: '<?php echo $modul_platnosci; ?>',
                            idwysylki: '<?php echo $idwysylki; ?>',
                            minwartosc: '<?php echo $minwartosc; ?>',
                            minilosc: '<?php echo $minilosc; ?>',
                            maxwartosc: '<?php echo $maxwartosc; ?>',
                            maxilosc: '<?php echo $maxilosc; ?>',                            
                            promocje: '<?php echo $promocje; ?>',
                            widoczny: '<?php echo $widoczny; ?>',
                            rodzajwaluta: '<?php echo $rodzaj_waluta; ?>',
                            uzycie_kuponu: <?php echo $uzycie_kuponu; ?>,
                            pierwsze_zakupy: <?php echo $pierwsze_zakupy; ?>,
                            rodzajwartosc: '<?php echo $rodzajwartosc; ?>',
                            datapoczatkowa: '<?php echo $datapoczatkowa; ?>',
                            datakoncowa: '<?php echo $datakoncowa; ?>',
                            warunek: '<?php echo $warunek; ?>',
                            warunekid: '<?php echo $warunek_id; ?>',
                            tylkoprodukty: '<?php echo $tylko_produkty; ?>',
                            tylkoproduktyid: '<?php echo $tylko_produkty_id; ?>',                            
                            limit: limit,
                            wskaznik: wskaznik
                          },
                          function(data) {
                          
                             if ( data != '' ) {
                                  $('#blad').html(data);
                             }                          
                          
                             procent = parseInt(((limit + wskaznik) / ogolny_limit) * 100);
                             if ( procent > 100 ) {
                                  procent = 100;
                             }
                             //
                             var ile_mail = (limit + wskaznik);
                             if ( (limit + wskaznik) > ogolny_limit ) {
                                  ile_mail = ogolny_limit;
                             }
                             //
                             $('#procent').html('Stopień realizacji: <span>' + procent + '%</span><br />Wysłano maili: <span>' + ile_mail + '</span>');
                             
                             $('#suwak_aktywny').css('width' , (procent * 5) + 5 + 'px');
                             
                             if (ogolny_limit - wskaznik > limit) {
                               
                                wyslij_mail_rabatowy(<?php echo $info['newsletters_id']; ?>, limit + wskaznik, wskaznik);
                                
                               } else {
                               
                                if ( $('#blad').html() != '' ) {
                                     
                                     $('#p_wyslij').css('display','none');
                                     $('#postep').css('display','none');
                                     $('#suwak').slideUp("fast");
                                     $('#procent').slideUp("fast");
                                     $('#blad').slideDown("fast");
                                     $('#przyciski').slideDown("fast");
                                     
                                   } else { 
                                   
                                    $('#p_wyslij').css('display','none');
                                    $('#postep').css('display','none');
                                    $('#suwak').slideUp("fast");
                                    $('#wynik_dzialania').slideDown("fast");
                                    $('#przyciski').slideDown("fast");
                                    
                                }                               
                               
                             }                                

                          }                          
                    );
                    
                }; 
                </script>                
            
                <div class="pozycja_edytowana">
                
                    <div id="KuponMail">
                    
                        <div>
                            Tytuł wiadomości: <span><?php echo $info['title']; ?></span>
                        </div>
                        
                        <div>
                            Odbiorcy maili: 
                            <?php
                            switch ($info['destination']) {
                                case "1":
                                    $doKogo = 'Do wszystkich zarejestrowanych klientów sklepu';
                                    break; 
                                case "2":
                                    $doKogo = 'Tylko zarejestrowani klienci którzy wyrazili zgodę na newsletter';
                                    break;                          
                                case "3":
                                    $doKogo = 'Tylko klienci którzy zapisali się do newslettera, a nie są klientami sklepu';
                                    break;
                                case "4":
                                    $doKogo = 'Do wszystkich którzy zapisali się do newslettera';
                                    break;                        
                                case "5":
                                    $doKogo = 'Mailing';
                                    break;     
                                case "6":
                                    $doKogo = 'Tylko do określonej grupy klientów';
                                    break;         
                                case "7":
                                    $doKogo = 'tylko klienci z porzuconymi koszykami';
                                    break;                                     
                            }                         
                            
                            ?>
                            <span><?php echo $doKogo; ?></span>
                        </div>
                        
                        <div>
                            Ilość maili do wysłania: <span><?php echo count(Newsletter::AdresyEmailNewslettera($info['newsletters_id'])); ?></span>
                        </div>
                        
                        <div>
                            Prefix kuponu rabatowego: <span><?php echo $prefix; ?></span>
                        </div>                        
                        
                    </div>
                
                    <div id="import" style="display:none">
                    
                        <div id="postep">Postęp wysyłania ...</div>
                    
                        <div id="suwak">
                            <div style="margin:1px;overflow:hidden">
                                <div id="suwak_aktywny"></div>
                            </div>
                        </div>
                        
                        <div id="procent">Stopień realizacji: <span>0%</span><br />Wysłano maili: <span>0</span></div> 

                        <div id="blad" style="display:none"></div>

                        <div class="cl"></div>
                    
                    </div>   
                    
                    <div id="wynik_dzialania" style="display:none">
                        Maile zostały wysłany do klientów ...
                    </div>
                    
                </div>
                
                <div class="przyciski_dolne" id="przyciski">
                  <button type="button" id="p_wyslij" class="przyciskNon" onclick="wyslij_mail_rabatowy(<?php echo $info['newsletters_id']; ?>,0,<?php echo (int)NEWSLETTER_WSKAZNIK; ?>)">Wyślij wiadmości</button> 
                  <button type="button" class="przyciskNon" onclick="cofnij('kupony','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>');">Powrót</button> 
                </div>

            <?php
            } else {
            
                echo '<div class="pozycja_edytowana">Brak danych do wyświetlenia</div>';
            
            }
            
            unset($prefix, $opis, $rodzaj, $minwartosc, $minilosc, $promocje, $widoczny, $newsletter_id, $rodzajwartosc, $datapoczatkowa, $datakoncowa, $warunek, $warunek_id);      
            
            $db->close_query($sql);
            ?>

          </div>                      
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}