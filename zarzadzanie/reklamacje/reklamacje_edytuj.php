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
                array('complaints_customers_orders_id',$filtr->process($_POST["nr_zamowienia"])),
                array('complaints_subject',$filtr->process($_POST["tytul"])),
                array('complaints_date_modified','now()'),                
                array('complaints_service',$filtr->process($_POST["opiekun_id"])),
                array('complaints_adminnotes',$filtr->process($_POST["uwagi"])));
                
        if ( Funkcje::czyNiePuste($_POST['data_rozpatrzenia']) ) {
             $pola[] = array('complaints_date_end',date("Y-m-d H:i:s", FunkcjeWlasnePHP::my_strtotime($filtr->process($_POST['data_rozpatrzenia']))));               
        }
                
        if ((int)$_POST["rodzaj_klienta"] == 1) {
            // jezeli jest klient z bazy
            $zapytanieKlient = "select customers_default_address_id, customers_firstname, customers_lastname, customers_email_address, customers_telephone from customers where customers_id = '" . $filtr->process($_POST["id_klienta"]) . "'";
            $sql = $db->open_query($zapytanieKlient);     
            $klient = $sql->fetch_assoc();
            //
            $pola[] = array('complaints_customers_id',$filtr->process($_POST["id_klienta"]));
            $pola[] = array('complaints_customers_name',$klient['customers_firstname'] . ' ' . $klient['customers_lastname']);
            $pola[] = array('complaints_customers_address','');
            $pola[] = array('complaints_customers_email',$klient['customers_email_address']);
            $pola[] = array('complaints_customers_telephone',$klient['customers_telephone']);
            //
            $db->close_query($sql);
            unset($zapytanieKlient, $klient);
            //
          } else {
            // jezeli klient nie jest z bazy
            $pola[] = array('complaints_customers_id','0');
            $pola[] = array('complaints_customers_name',$filtr->process($_POST["dane_klienta_nazwa"]));
            $pola[] = array('complaints_customers_address',$filtr->process($_POST["dane_klienta_adres"]));
            $pola[] = array('complaints_customers_email',$filtr->process($_POST["email_klienta"]));
            $pola[] = array('complaints_customers_telephone',$filtr->process($_POST["telefon_klienta"]));
            //
        }
        //			
        $db->update_query('complaints' , $pola, " complaints_id = '".(int)$_POST["id"]."'");		
        unset($pola);
        
        //
        Funkcje::PrzekierowanieURL('reklamacje_szczegoly.php?id_poz='.(int)$_POST["id"].'&zakladka='.(int)$_POST["zakladka"]);
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Edycja pozycji</div>
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
            
            $('input.datepicker').Zebra_DatePicker({
               format: 'd-m-Y H:i',
               inside: false,
               readonly_element: true,
               show_clear_date: false
            });             
       
          });

          function wybierz_klienta(id) {
            if (id == 1) {
                $('#klient_z_bazy').slideDown('fast');
                $('#klient_z_poza_bazy').slideUp('fast');      
            }
            if (id == 2) {
                $('#klient_z_poza_bazy').slideDown('fast'); 
                $('#klient_z_bazy').slideUp('fast');   
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

          <form action="reklamacje/reklamacje_edytuj.php" method="post" id="reklamForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Edycja danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            } 
            if ( !isset($_GET['zakladka']) ) {
                 $_GET['zakladka'] = '0';
            }            
            
            $zapytanie = "select * from complaints where complaints_id = '" . (int)$_GET['id_poz'] . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
            
                $info = $sql->fetch_assoc();
                ?>
            
                <div class="pozycja_edytowana">
                
                    <div class="info_content">

                    <input type="hidden" name="akcja" value="zapisz" />
                
                    <input type="hidden" name="id" value="<?php echo (int)$_GET['id_poz']; ?>" />
                    <input type="hidden" name="zakladka" value="<?php echo (int)$_GET['zakladka']; ?>" />
                    
                    <p>
                      <label class="required" for="id_tmp">Nr zgłoszenia:</label>
                      <input type="text" name="id_tmp" size="25" value="<?php echo $info['complaints_rand_id']; ?>" disabled="disabled" />     
                    </p>
                    
                    <p>
                      <label class="required" for="tytul">Tytuł reklamacji:</label>
                      <input type="text" name="tytul" id="tytul" size="75" value="<?php echo $info['complaints_subject']; ?>" />     
                    </p>

                    <p>
                      <label>Rodzaj klienta:</label>
                      <input type="radio" value="1" name="rodzaj_klienta" id="tryb_1" onclick="wybierz_klienta(1)" <?php echo (($info['complaints_customers_id'] > 0) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="tryb_1">z bazy sklepu</label>
                      <input type="radio" value="2" name="rodzaj_klienta" id="tryb_2" onclick="wybierz_klienta(2)" <?php echo (($info['complaints_customers_id'] == 0) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="tryb_2">z poza bazy sklepu</label>      
                    </p>                

                    <div id="klient_z_bazy" <?php echo (($info['complaints_customers_id'] > 0) ? '' : 'style="display:none"'); ?>>
                        
                        <div class="TabelaSklepu">
                        
                            <div class="LabelTabela"><label>Klient:</label></div>
                            
                            <div class="TabelaKlientow">
                            
                                <div id="fraza">
                                    <div>Wyszukaj klienta: <input type="text" size="15" value="" id="szukany_klient" /><em class="TipIkona"><b>Wpisz nazwisko imię, klienta, nazwę firmy, NIP lub adres email</b></em></div> <span onclick="fraza_klienci()" ></span>
                                </div>                               
                            
                                <?php $tablica_klientow = Klienci::ListaKlientow( false ); ?>
                                
                                <div class="ObramowanieTabeli ListaKlientow">
                                
                                <div id="TabelaKlientow">
                                
                                <?php if ( $info['complaints_customers_id'] > 0 ) { ?>
                                
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
                                        $zaznacz_input = '';
                                        if ( $klient['id'] == $info['complaints_customers_id'] ) {
                                             $zaznacz_input = 'checked="checked"';
                                        }
                                        //
                                        
                                        if ( count($tablica_klientow) < 1000 || $zaznacz_input != '' ) {
                                        
                                            echo '<tr class="pozycja_off"' . (($klient['id'] == $info['complaints_customers_id']) ? ' id="wybrany"' : '') . '>';
                                            echo '<td><input type="radio" name="klient_id_'.$klient['id'].'" onclick="$(\'#id_klienta\').val(this.value)" value="' . $klient['id'] . '" ' . $zaznacz_input . ' id="klient_id_'.$klient['id'].'" /><label class="OpisForPustyLabel" for="klient_id_'.$klient['id'].'"></label></td>';
                                            echo '<td>' . $klient['id'] . '</td>';
                                            echo '<td>' . $klient['nazwa'] . '<br />' . $klient['adres'] . '</td>';
                                            
                                            if ( !empty($klient['firma']) ) {
                                                 echo '<td><span class="Firma">' . $klient['firma'] . '</span>' . ((!empty($klient['nip'])) ? 'NIP:&nbsp;' . $klient['nip'] : '') . '</td>';
                                               } else{
                                                 echo '<td></td>';
                                            }
                                            
                                            echo '<td><span class="MalyMail">' . $klient['email'] . '</span>' . ((!empty($klient['telefon'])) ? '<br /><span class="MalyTelefon">' . $klient['telefon'] . '</span>' : '') . '</td>';
                                            echo '</tr>';
                                            
                                        }
                                        //
                                        unset($zaznacz_input);
                                        //
                                    }
                                    ?>
                                    
                                  </table>

                                  <?php if ($info['complaints_customers_id'] > 0) { ?>

                                  <script>  
                                  $('.ListaKlientow').scrollTop($('#wybrany').position().top);
                                  </script>    
                                  
                                  <?php } ?>
                                  
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
                          <input type="hidden" name="id_klienta" id="id_klienta" value="<?php echo $info['complaints_customers_id']; ?>" />
                        </p>                         
                        
                    </div>
                    
                    <div id="klient_z_poza_bazy" <?php echo (($info['complaints_customers_id'] == 0) ? '' : 'style="display:none"'); ?>>
                    
                      <p>
                        <label for="dane_klienta_nazwa">Imię i nazwisko:</label>
                        <input type="text" name="dane_klienta_nazwa" id="dane_klienta_nazwa" size="55" value="<?php echo $info['complaints_customers_name']; ?>" />     
                      </p> 
                      
                      <p>
                        <label for="dane_klienta_adres">Adres klienta:</label>
                        <textarea name="dane_klienta_adres" id="dane_klienta_adres" rows="5" cols="80"><?php echo $info['complaints_customers_address']; ?></textarea>
                      </p>
                      
                      <p>
                        <label for="email_klienta">Adres email:</label>
                        <input type="text" name="email_klienta" id="email_klienta" size="35" value="<?php echo $info['complaints_customers_email']; ?>" />     
                      </p>    
                      
                      <p>
                        <label for="telefon_klienta">Numer telefonu:</label>
                        <input type="text" name="telefon_klienta" id="telefon_klienta" size="20" value="<?php echo $info['complaints_customers_telephone']; ?>" />     
                      </p>  
                      
                    </div>

                    <p>
                      <label class="required" for="nr_zamowienia">Nr zamówienia:</label>
                      <input type="text" name="nr_zamowienia" id="nr_zamowienia" class="calkowita" size="15" value="<?php echo $info['complaints_customers_orders_id']; ?>" /> 
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
                      echo Funkcje::RozwijaneMenu('opiekun_id', $lista_uzytkownikow, $info['complaints_service'], 'style="width:200px;" id="opiekun_id"'); 
                      unset($lista_uzytkownikow);
                      ?>
                    </p>  
                    
                    <p>
                        <label for="data_promocja_od">Data rozpatrzenia:</label>
                        <input type="text" id="data_rozpatrzenia" name="data_rozpatrzenia" value="<?php echo ((Funkcje::czyNiePuste($info['complaints_date_end'])) ? date('d-m-Y H:i',FunkcjeWlasnePHP::my_strtotime($info['complaints_date_end'])) : ''); ?>" size="20"  class="datepicker" />
                    </p>         

                    <p>
                      <label for="uwagi">Uwagi do reklamacji:</label>
                      <textarea type="text" name="uwagi" cols="100" rows="10" id="uwagi"><?php echo $info['complaints_adminnotes']; ?></textarea>
                    </p>                       

                    </div>

                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Zapisz dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('reklamacje_szczegoly','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','reklamacje');">Powrót</button>           
                </div>                 

            <?php
            
            $db->close_query($sql);
            unset($info);            
            
            } else {
            
                echo '<div class="pozycja_edytowana">Brak danych do wyświetlenia</div>';
            
            }
            ?>

          </div>                      
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}
