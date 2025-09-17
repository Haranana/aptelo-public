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
                array('complaints_rand_id',$filtr->process($_POST["id_reklamacji"])),
                array('complaints_customers_orders_id',$filtr->process($_POST["nr_zamowienia"])),
                array('complaints_subject',$filtr->process($_POST["tytul"])),
                array('complaints_date_created','now()'),
                array('complaints_date_modified','now()'),
                array('complaints_date_end',date("Y-m-d H:i:s", time() + (REKLAMACJA_CZAS_ROZPATRZENIA * 86400))),
                array('complaints_service',$filtr->process($_POST["opiekun_id"])),
                array('complaints_adminnotes',$filtr->process($_POST["uwagi"])),
                array('complaints_status_id',$filtr->process($_POST["status_id"])));
                
        if ((int)$_POST["rodzaj_klienta"] == 1) {
            //
            // jezeli jest klient z bazy
            $zapytanie_klient = "select customers_default_address_id, customers_firstname, customers_lastname, customers_email_address, customers_telephone from customers where customers_id = '" . (int)$_POST["id_klienta"] . "'";
            $sql = $db->open_query($zapytanie_klient);     
            $klient = $sql->fetch_assoc();
            //
            $pola[] = array('complaints_customers_id',(int)$_POST["id_klienta"]);
            $pola[] = array('complaints_customers_name',$klient['customers_firstname'] . ' ' . $klient['customers_lastname']);
            $pola[] = array('complaints_customers_address','');
            $pola[] = array('complaints_customers_email',$klient['customers_email_address']);
            $pola[] = array('complaints_customers_telephone',$klient['customers_telephone']);
            //
            $db->close_query($sql);
            unset($zapytanie_klient, $klient);
            //
          } else {
            //
            // jezeli klient nie jest z bazy
            $pola[] = array('complaints_customers_id','0');
            $pola[] = array('complaints_customers_name',$filtr->process($_POST["dane_klienta_nazwa"]));
            $pola[] = array('complaints_customers_address',$filtr->process($_POST["dane_klienta_adres"]));
            $pola[] = array('complaints_customers_email',$filtr->process($_POST["email_klienta"]));
            $pola[] = array('complaints_customers_telephone',$filtr->process($_POST["telefon_klienta"]));
            //
        }
        //			
        $db->insert_query('complaints' , $pola);	
        $id_dodanej_pozycji = $db->last_id_query();
        
        unset($pola);
        
        $pola = array(
                array('complaints_id',$id_dodanej_pozycji),
                array('complaints_status_id',$filtr->process($_POST["status_id"])),
                array('date_added','now()'),
                array('comments',$filtr->process($_POST["wiadomosc"]))
                );

        $db->insert_query('complaints_status_history' , $pola);	
        unset($pola);                
        
        //
        if (isset($id_dodanej_pozycji) && $id_dodanej_pozycji > 0) {
            Funkcje::PrzekierowanieURL('reklamacje.php?id_poz='.$id_dodanej_pozycji);
        } else {
            Funkcje::PrzekierowanieURL('reklamacje.php');
        }
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Dodawanie pozycji</div>
    <div id="cont">
          
          <script>
          $(document).ready(function() {
            $("#reklamForm").validate({
              rules: {
                tytul: {
                  required: true
                }, 
                nr_zamowienia: {
                  required: true,
                  range: [1, 1000000],
                  number: true
                },
                id_klienta: {
                  required: function(element) {
                    if ($("#id_klienta").val() == '' && $('#tryb_1').prop('checked')) {
                        return true;
                      } else {
                        return false;
                    }
                  }
                }             
              },
              messages: {
                tytul: {
                  required: "Pole jest wymagane."
                },
                nr_zamowienia: {
                  required: "Pole jest wymagane."
                },
                id_klienta: {
                  required: "Nie został wybrany klient."
                }               
              }
            });
            
            ckedit('wiadomosc','99%','200');     
          });
          
          function wybierz_klienta(id) {
            if (id == 1) {
                $('#klient_z_bazy').slideDown('fast');
                $('#klient_z_poza_bazy').slideUp('fast');
                $('#nr_zamowienia').val('');
            }
            if (id == 2) {
                $('#klient_z_poza_bazy').slideDown('fast'); 
                $('#klient_z_bazy').slideUp('fast');
                $('#nr_zamowienia').val('99999999');
            }                                      
          }   
          
          function fraza_klienci() { 
              //
              $('#id_klienta').val('');
              //          
              if ( $('#szukany_klient').val().trim() == '' ) {
                  //
                  $.colorbox( { html:'<div id="PopUpInfo">Nie została podana szukana wartość.</div>', initialWidth:50, initialHeight:50, maxWidth:'90%', maxHeight:'90%' } );
                  //
              } else {
                  //
                  $('#TabelaKlientow').hide();
                  //
                  $('#TabelaKlientow').html('<img src="obrazki/_loader_small.gif">');
                  $.get("ajax/lista_klientow.php", 
                      { fraza: $('#szukany_klient').val(), tok: $('#tok').val(), rabat: 'tak' },
                      function(data) { 
                          $('#TabelaKlientow').css('display','none');
                          $('#TabelaKlientow').html(data);
                          $('#TabelaKlientow').css('display','block'); 
                          //
                          pokazChmurki();
                  });    
                  //
              }
          }                  
          </script>     
          
          <form action="reklamacje/reklamacje_dodaj.php" method="post" id="reklamForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Dodawanie danych</div>
            
            <div class="pozycja_edytowana">
            
                <div class="info_content">
            
                <input type="hidden" name="akcja" value="zapisz" />
                
                <?php
                $Id_Reklamacji = Reklamacje::UtworzIdReklamacji(15);
                ?>
                
                <input type="hidden" name="id_reklamacji" value="<?php echo $Id_Reklamacji; ?>" />
                
                <p>
                  <label class="required" for="id_tmp">Nr zgłoszenia:</label>
                  <input type="text" name="id_tmp" id="id_tmp" size="25" value="<?php echo $Id_Reklamacji; ?>" disabled="disabled" />     
                </p>
                
                <p>
                  <label class="required" for="tytul">Tytuł reklamacji:</label>
                  <input type="text" name="tytul" id="tytul" size="75" value="" />     
                </p>

                <p>
                  <label>Rodzaj klienta:</label>
                  <input type="radio" value="1" name="rodzaj_klienta" id="tryb_1" onclick="wybierz_klienta(1)" checked="checked" /><label class="OpisFor" for="tryb_1">z bazy sklepu</label>
                  <input type="radio" value="2" name="rodzaj_klienta" id="tryb_2" onclick="wybierz_klienta(2)" /><label class="OpisFor" for="tryb_2">z poza bazy sklepu</label>             
                </p>                

                <div id="klient_z_bazy">
                
                    <div class="TabelaSklepu">
                    
                        <div class="LabelTabela"><label>Klient:</label></div>

                        <div class="TabelaKlientow">
                        
                            <div id="fraza">
                                <div>Wyszukaj klienta: <input type="text" size="15" value="" id="szukany_klient" /><em class="TipIkona"><b>Wpisz nazwisko imię, klienta, nazwę firmy, NIP lub adres email</b></em></div> <span onclick="fraza_klienci()" ></span>
                            </div>                              
                                                    
                            <?php $tablica_klientow = Klienci::ListaKlientow( false ); ?>
                            
                            <div class="ObramowanieTabeli ListaKlientow">
                            
                            <div id="TabelaKlientow">
                            
                            <?php if ( count($tablica_klientow) < 1000 ) { ?>                                
                            
                              <table class="listing_tbl">
                              
                                <tr class="div_naglowek">
                                  <td>Wybierz</td>
                                  <td>ID</td>
                                  <td>Dane klienta</td>
                                  <td>Firma</td>
                                  <td>Kontakt</td>
                                </tr>           

                                <?php
                                foreach ( $tablica_klientow as $klient) {
                                    //
                                    echo '<tr class="pozycja_off">';
                                    echo '<td><input type="radio" name="klient_id_'.$klient['id'].'" onclick="$(\'#id_klienta\').val(this.value)" id="klient_id_'.$klient['id'].'" value="' . $klient['id'] . '" /><label class="OpisForPustyLabel" for="klient_id_'.$klient['id'].'"></label></td>';
                                    echo '<td>' . $klient['id'] . '</td>';
                                    echo '<td>' . $klient['nazwa'] . '<br />' . $klient['adres'] . '</td>';
                                    
                                    if ( !empty($klient['firma']) ) {
                                         echo '<td><span class="Firma">' . $klient['firma'] . '</span>' . ((!empty($klient['nip'])) ? 'NIP:&nbsp;' . $klient['nip'] : '') . '</td>';
                                       } else{
                                         echo '<td></td>';
                                    }
                                    
                                    echo '<td><span class="MalyMail">' . $klient['email'] . '</span>' . ((!empty($klient['telefon'])) ? '<br /><span class="MalyTelefon">' . $klient['telefon'] . '</span>' : '') . '</td>';
                                    echo '</tr>';
                                    //
                                }
                                ?>
                                
                              </table>
                              
                            <?php } else { ?>
                            
                              <span class="maleInfo" style="font-weight:normal">Wyszukaj klienta przy użyciu wyszukiwarki</span>
                            
                            <?php } ?>
                            
                            </div>
                            
                            </div>
                            
                            <?php
                            unset($tablica_klientow);
                            ?>          
                            
                        </div>

                    </div>
                    
                    <p class="errorRwd">
                      <input type="hidden" name="id_klienta" id="id_klienta" value="" />
                    </p>                   

                </div>
                
                <div id="klient_z_poza_bazy" style="display:none">
                  <p>
                    <label for="dane_klienta_nazwa">Imię i nazwisko:</label>
                    <input type="text" name="dane_klienta_nazwa" id="dane_klienta_nazwa" size="55" value="" />     
                  </p>                
                  <p>
                    <label for="dane_klienta_adres">Adres klienta:</label>
                    <textarea name="dane_klienta_adres" id="dane_klienta_adres" rows="5" cols="80"></textarea>
                  </p>
                  <p>
                    <label for="email_klienta">Adres email:</label>
                    <input type="text" name="email_klienta" id="email_klienta" size="35" value="" />     
                  </p>
                  <p>
                    <label for="telefon_klienta">Numer telefonu:</label>
                    <input type="text" name="telefon_klienta" id="telefon_klienta" size="20" value="" />     
                  </p>                      
                </div>

                <p>
                  <label class="required" for="nr_zamowienia">Nr zamówienia:</label>
                  <input type="text" name="nr_zamowienia" id="nr_zamowienia" class="calkowita" size="15" value="" /> 
                </p>
                
                <p>
                  <label for="opiekun_id">Opiekun reklamacji:</label>
                  <?php
                  // pobieranie informacji od uzytkownikach
                  $lista_uzytkownikow = Array();
                  $zapytanie_uzytkownicy = "select distinct * from admin order by admin_lastname, admin_firstname";
                  $sql_uzytkownicy = $db->open_query($zapytanie_uzytkownicy);
                  //
                  $lista_uzytkownikow[] = array('id' => 0, 'text' => 'Nie przypisany ...');
                  //
                  while ($uzytkownicy = $sql_uzytkownicy->fetch_assoc()) {
                    $lista_uzytkownikow[] = array('id' => $uzytkownicy['admin_id'], 'text' => $uzytkownicy['admin_firstname'] . ' ' . $uzytkownicy['admin_lastname']);
                  }
                  $db->close_query($sql_uzytkownicy); 
                  unset($zapytanie_uzytkownicy, $uzytkownicy);    
                  //                                   
                  echo Funkcje::RozwijaneMenu('opiekun_id', $lista_uzytkownikow, '', 'style="width:200px;" id="opiekun_id"'); 
                  unset($lista_uzytkownikow);
                  ?>
                </p>           

                <p>
                  <label for="uwagi">Uwagi do reklamacji:</label>
                  <textarea type="text" name="uwagi" cols="100" rows="10" id="uwagi"></textarea>
                </p>                   
                
                <p>
                  <label class="required" for="status_id">Status reklamacji:</label>
                  <?php echo Funkcje::RozwijaneMenu('status_id', Reklamacje::ListaStatusowReklamacji( false ), '', 'style="width:300px;" id="status_id"'); ?>
                </p>

                <p>
                  <label for="wiadomosc">Opis reklamacji:</label>
                  <textarea id="wiadomosc" name="wiadomosc" cols="90" rows="5"></textarea>
                </p>                 

                </div>
                
            </div>

            <div class="przyciski_dolne">
              <input type="submit" class="przyciskNon" value="Zapisz dane" />
              <button type="button" class="przyciskNon" onclick="cofnij('reklamacje','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','reklamacje');">Powrót</button>   
            </div>            

          </div>                      
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}
