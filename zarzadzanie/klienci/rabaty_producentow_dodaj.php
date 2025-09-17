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
                array('discount_name',$filtr->process($_POST["nazwa"])),
                array('discount_discount',(float)$_POST["rabat"]),
                array('discount_manufacturers_id',implode(',', (array)$_POST['id_producent'])));
                
        if ((int)$_POST["tryb_rabat"] == 1) {
            $pola[] = array('discount_groups_id',',' . implode(',', (array)$_POST["grupa_klientow"]) . ',');
          } else {
            $pola[] = array('discount_customers_id',(int)$_POST["id_klienta"]);
        }
        //			
        $db->insert_query('discount_manufacturers' , $pola);	
        $id_dodanej_pozycji = $db->last_id_query();
        
        unset($pola);
        //
        if (isset($id_dodanej_pozycji) && $id_dodanej_pozycji > 0) {
            Funkcje::PrzekierowanieURL('rabaty_producentow.php?id_poz='.$id_dodanej_pozycji);
        } else {
            Funkcje::PrzekierowanieURL('rabaty_producentow.php');
        }
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
                nazwa: {
                  required: true
                },
                rabat: { required: true, range: [-100, 0], number: true },
                id_producenta: {
                  required: function(element) {
                    if ($("#id_producenta").val() == '') {
                        return true;
                      } else {
                        return false;
                    }
                  }
                },
                'grupa_klientow[]': { 
                  required: function(element) {
                    if ($('#tryb_1').prop('checked')) {
                        return true;
                      } else {
                        return false;
                    }
                  }                
                },
                id_klienta: {
                  required: function(element) {
                    if ($("#id_klienta").val() == '' && $('#tryb_2').prop('checked')) {
                        return true;
                      } else {
                        return false;
                    }
                  }
                }                    
              },
              messages: {
                nazwa: {
                  required: "Pole jest wymagane."
                },
                id_producenta: {
                  required: "Nie został wybrany producent."
                },
                id_klienta: {
                  required: "Nie został wybrany klient."
                },
                'grupa_klientow[]': {
                  required: "Wybierz minimum jedną grupę klientów."
                }                  
              }
            });
            
            $('.pkc td').find('input').click( function() {
               $('#id_producenta').val( $(this).val() );
               //
               var checked = [];
               $("input[name='id_producent[]']:checked").each( function() {
                   checked.push(parseInt($(this).val()));
               });
               if ( checked.length == 0 ) {
                    $('#id_producenta').val('');
               }                     
            });            
          });
           
          function wybierz_zakres_rabatu(id) {
            if (id == 1) {
                $('#zakres_grupa').slideDown('fast');
                $('#zakres_klient').hide();        
            }
            if (id == 2) {
                $('#zakres_klient').slideDown('fast'); 
                $('#zakres_grupa').hide();    
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

          <form action="klienci/rabaty_producentow_dodaj.php" method="post" id="eForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Dodawanie danych</div>
            
            <div class="pozycja_edytowana">
            
                <div class="info_content">
            
                <input type="hidden" name="akcja" value="zapisz" />

                    <p>
                      <label class="required" for="nazwa">Nazwa:</label>
                      <input type="text" name="nazwa" id="nazwa" value="" size="53" />
                    </p>   

                    <p>
                      <label class="required" for="rabat">Rabat [%]:</label>
                      <input type="text" name="rabat" id="rabat" value="" size="5" /><em class="TipIkona"><b>liczba z zakresu od -100 do 0</b></em>
                    </p>
                    
                    <p>
                      <label>Producenci do jakich będzie przypisany rabat:</label>
                    </p>
                    
                    <div class="WybieranieKategoriiProducenta">
                    
                        <div id="drzewo" style="margin:0px">
                        
                          <?php
                          $Prd = Funkcje::TablicaProducenci();
                          //
                          if (count($Prd) > 0) {
                              //
                              echo '<table class="pkc">';
                              //
                              for ($b = 0, $c = count($Prd); $b < $c; $b++) {
                                  echo '<tr>                                
                                          <td class="lfp">
                                              <input type="checkbox" value="'.$Prd[$b]['id'].'" name="id_producent[]" id="id_producent_' . $Prd[$b]['id'] . '" /> <label class="OpisFor" for="id_producent_' . $Prd[$b]['id'] . '">'.$Prd[$b]['text'].'</label>
                                          </td>                                
                                        </tr>';
                              }
                              echo '</table>';
                              //
                          }
                          unset($Prd);
                          ?>
                          
                        </div> 

                    </div>

                    <p>
                      <input type="hidden" name="id_producenta" id="id_producenta" value="" />
                    </p>                      
                    
                    <p>
                      <label>Rabat przypisany do:</label>
                      <input type="radio" value="1" name="tryb_rabat" id="tryb_1" onclick="wybierz_zakres_rabatu(1)" checked="checked" /><label class="OpisFor" for="tryb_1">grupy klientów</label>
                      <input type="radio" value="2" name="tryb_rabat" id="tryb_2" onclick="wybierz_zakres_rabatu(2)" /><label class="OpisFor" for="tryb_2">indywidualnego klienta</label> 
                    </p>

                    <div id="zakres_grupa">

                      <?php $TablicaGrupKlientow = Klienci::ListaGrupKlientow(false); ?>
                      
                      <table class="WyborCheckbox GrupyBlad">
                          <tr>
                              <td><label>Grupa/grupy klientów:</label></td>
                              <td>
                                  <?php                        
                                  foreach ( $TablicaGrupKlientow as $GrupaKlienta ) {
                                      echo '<input type="checkbox" value="' . $GrupaKlienta['id'] . '" name="grupa_klientow[]" id="grupa_klientow_' . $GrupaKlienta['id'] . '" /><label class="OpisFor" for="grupa_klientow_' . $GrupaKlienta['id'] . '">' . $GrupaKlienta['text'] . '</label><br />';
                                  }              
                                  ?>
                              </td>
                          </tr>
                      </table>   

                      <?php unset($TablicaGrupKlientow); ?>

                    </div>
                    
                    <div id="zakres_klient" style="display:none">
                    
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

                </div>
                
            </div>

            <div class="przyciski_dolne">
              <input type="submit" class="przyciskNon" value="Zapisz dane" />
              <button type="button" class="przyciskNon" onclick="cofnij('rabaty_producentow','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','klienci');">Powrót</button>   
            </div>            

          </div>                      
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}