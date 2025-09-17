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
                array('cp_price',(float)$_POST["cena_1"]),
                array('cp_price_tax',(float)$_POST["brut_1"]),
                array('cp_tax',(float)$_POST["v_at_1"]),
                array('cp_products_id',(int)$_POST["id_prod"]));
                   
        if ((int)$_POST["tryb_rabat"] == 1) {
            $pola[] = array('cp_groups_id',(int)$_POST["grupa_klientow"]);
          } else {
            $pola[] = array('cp_customers_id',(int)$_POST["id_klienta"]);
        }
        //			

        $db->insert_query('customers_price', $pola);	
        $id_dodanej_pozycji = $db->last_id_query();    

        unset($pola);
                
        //
        if ( isset($_POST['id_klient']) ) {
             //
             Funkcje::PrzekierowanieURL('klienci_edytuj.php?id_poz='.(int)$_POST["id_klient"].'&zakladka=11');
             //
          } else {
             //
             if (isset($id_dodanej_pozycji) && $id_dodanej_pozycji > 0) {
                 Funkcje::PrzekierowanieURL('indywidualne_ceny_produktow.php?id_poz='.$id_dodanej_pozycji);
             } else {
                 Funkcje::PrzekierowanieURL('indywidualne_ceny_produktow.php');
             }
             //
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
                cena_1: { required: true, number: true, min: 0.01 },
                brut_1: { required: true, number: true, min: 0.01 },
                id_prod: {
                  required: function(element) {
                    if ($("#id_prod").val() == '') {
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
                id_prod: {
                  required: "Nie został wybrany produkt."
                },
                id_klienta: {
                  required: "Nie został wybrany klient."
                }                 
              }
            });
          });
          
          function pokaz_zakres_cen(id) {
              //
              var vat = new Array();
              <?php
              $sql = $db->open_query("select * from tax_rates");
              while ($vat = $sql->fetch_assoc()) {
                  echo 'vat[' . $vat['tax_rates_id'] . '] = ' . $vat['tax_rate'] . ';' . "\n";
              }
              $db->close_query($sql);  
              ?>
              //
              var id_vat = $('#produkt_id_' + id).attr('data-vat');
              $('#vat').val( vat[id_vat] );
              //
              $('#cena_1').val('');
              $('#v_at_1').val('');
              $('#brut_1').val('');
              //
              if ( $('#zakres_cen').css('display') == 'none' ) {
                   $('#zakres_cen').slideDown();
              }
          }
           
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

          <form action="klienci/indywidualne_ceny_produktow_dodaj.php" method="post" id="eForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Dodawanie danych</div>
            
            <div class="pozycja_edytowana">
            
                <div class="info_content">
            
                    <input type="hidden" name="akcja" value="zapisz" />
                    
                    <?php
                    if ( isset($_GET['id_klient']) && (int)$_GET['id_klient'] ) {
                         echo '<input type="hidden" name="id_klient" value="' . (int)$_GET['id_klient'] . '" />';
                    }    
                    ?>

                    <p>
                      <label for="szukany">Produkt do jakiego będzie przypisana nowa cena:</label>
                    </p>
                    
                    <div class="WybieranieProduktow">

                        <div class="GlownyListing">

                            <div class="GlownyListingKategorieEdycja">
                        
                                <div id="fraza">
                                    <div>Wyszukaj produkt: <input type="text" size="15" value="" id="szukany" /></div> <span onclick="fraza_produkty()"></span>
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
                            
                            <div style="GlownyListingProduktyEdycja">  
                                
                                <input type="hidden" id="rodzaj_modulu" value="indywidualne_ceny" />
                                <div id="wynik_produktow_indywidualne_ceny" class="WynikProduktowRabatProdukty"></div> 
                                
                            </div>
                            
                        </div>
                        
                    </div>
 
                    <p class="errorRwd">
                      <input type="hidden" name="id_prod" id="id_prod" value="" />
                    </p>       

                    <div id="zakres_cen" style="display:none">

                        <p>
                          <label class="required" for="cena_1">Cena netto:</label>
                          <input type="text" class="oblicz" name="cena_1" id="cena_1" value="" size="9" />
                          <input type="hidden" id="vat" value="" />
                        </p>
                        
                        <input type="hidden" name="v_at_1" id="v_at_1" value="" />
                        
                        <p>
                          <label class="required" for="brut_1">Cena brutto:</label>
                          <input type="text" class="oblicz_brutto min" name="brut_1" id="brut_1" value="" size="9" />
                        </p>                    
                        
                    </div>

                    <?php if ( isset($_GET['id_klient']) && (int)$_GET['id_klient'] > 0 ) { ?>

                        <div class="TabelaSklepu">
                        
                            <div class="LabelTabela"><label>Klient:</label></div>
                            
                            <div class="TabelaKlientow">                        
                        
                                <?php
                                $tablica_klientow = Klienci::ListaKlientow( false );
                                ?>
                                <div class="ObramowanieTabeli ListaKlientow">
                                
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
                                        if ( $klient['id'] == (int)$_GET['id_klient'] ) {
                                            //
                                            echo '<tr class="pozycja_off">';
                                            echo '<td><input type="radio" name="klient_id_'.$klient['id'].'" onclick="$(\'#id_klienta\').val(this.value)" id="klient_id_'.$klient['id'].'" value="' . $klient['id'] . '" ' . ((isset($_GET['id_klient']) && $_GET['id_klient'] == $klient['id']) ? 'checked="checked"' : '') . ' /><label class="OpisForPustyLabel" for="klient_id_'.$klient['id'].'"></label></td>';
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
                                        //
                                    }
                                    ?>
                                    
                                  </table>
                                  
                                </div>  
                                <?php
                                unset($tablica_klientow);
                                ?>
                                
                            </div>
                            
                            <input type="hidden" name="id_klienta" id="id_klienta" value="<?php echo ((isset($_GET['id_klient'])) ? (int)$_GET['id_klient'] : ''); ?>" />
                            
                            <input type="radio" value="2" name="tryb_rabat" checked="checked" />         

                        </div>   
                    
                    <?php } else { ?>
                    
                        <p>
                          <label>Cena przypisana do:</label>
                          <input type="radio" value="1" name="tryb_rabat" id="tryb_1" onclick="wybierz_zakres_rabatu(1)" checked="checked" /> <label class="OpisFor" for="tryb_1">grupy klientów</label>
                          <input type="radio" value="2" name="tryb_rabat" id="tryb_2" onclick="wybierz_zakres_rabatu(2)" /> <label class="OpisFor" for="tryb_2">indywidualnego klienta</label>         
                        </p>

                        <p id="zakres_grupa">
                          <label for="grupa_klientow">Grupa klientów:</label>
                          <?php
                          $tablica = Klienci::ListaGrupKlientow(false);                                        
                          echo Funkcje::RozwijaneMenu('grupa_klientow', $tablica, '', 'id="grupa_klientow"');
                          unset($tablica);
                          ?>
                        </p>
                        
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
                                          echo '<td><input type="radio" name="klient" value="' . $klient['id'] . '" onclick="$(\'#id_klienta\').val(this.value)" id="klient_id_'.$klient['id'].'" /><label class="OpisForPustyLabel" for="klient_id_'.$klient['id'].'"></label></td>';
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

                    <?php } ?>

                </div>
                
            </div>

            <div class="przyciski_dolne">
              <input type="submit" class="przyciskNon" value="Zapisz dane" />

              <?php if ( isset($_GET['id_klient']) && (int)$_GET['id_klient'] ) { ?>
              
                  <button type="button" class="przyciskNon" onclick="cofnij('klienci_edytuj','?id_poz=<?php echo (int)$_GET['id_klient']; ?>&zakladka=11','klienci');">Powrót</button>   
              
              <?php } else { ?>
              
                  <button type="button" class="przyciskNon" onclick="cofnij('indywidualne_ceny_produktow','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','klienci');">Powrót</button>   
                  
              <?php } ?>              
              
            </div>            

          </div>                      
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}