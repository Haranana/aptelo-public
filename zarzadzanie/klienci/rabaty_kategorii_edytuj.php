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
                array('discount_categories_id',implode(',', (array)$_POST['id_kat'])));

        if (isset($_POST["tryb_rabat"]) && (int)$_POST["tryb_rabat"] == 1) {
            $pola[] = array('discount_groups_id',',' . implode(',', (array)$_POST["grupa_klientow"]) . ',');
        }
        //
        $db->update_query('discount_categories' , $pola, " discount_id = '".(int)$_POST["id"]."'");	

        unset($pola);
        //
        Funkcje::PrzekierowanieURL('rabaty_kategorii.php?id_poz='.(int)$_POST["id"]);
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Edycja pozycji</div>
    <div id="cont">
          
          <script>
          $(document).ready(function() {

            $("#eForm").validate({
              rules: {
                nazwa: {
                  required: true
                },
                rabat: { required: true, range: [-100, 0], number: true },
                id_kategorii: {
                  required: function(element) {
                    if ($("#id_kategorii").val() == '') {
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
                id_kategorii: {
                  required: "Nie została wybrana kategoria."
                }  
              }
            });
            
            $('.pkc td').find('input').click( function() {
                if ( $('#id_kategorii').length ) {
                     $('#id_kategorii').val( $(this).val() );
                     //
                     var checked = [];
                     $("input[name='id_kat[]']:checked").each( function() {
                         checked.push(parseInt($(this).val()));
                     });
                     if ( checked.length == 0 ) {
                          $('#id_kategorii').val('');
                     }                     
                }
            });
          });                          
          </script>        

          <form action="klienci/rabaty_kategorii_edytuj.php" method="post" id="eForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Edycja danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select * from discount_categories where discount_id = '" . (int)$_GET['id_poz'] . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
            
                $info = $sql->fetch_assoc();
                ?>            
            
                <div class="pozycja_edytowana">
                
                    <div class="info_content">
                
                        <input type="hidden" name="akcja" value="zapisz" />
                        
                        <input type="hidden" name="id" value="<?php echo (int)$_GET['id_poz']; ?>" />

                        <p>
                          <label class="required" for="nazwa">Nazwa:</label>
                          <input type="text" name="nazwa" id="nazwa" value="<?php echo $info['discount_name']; ?>" size="53" />
                        </p>   

                        <p>
                          <label class="required" for="rabat">Rabat [%]:</label>
                          <input type="text" name="rabat" id="rabat" value="<?php echo $info['discount_discount']; ?>" size="5" /><em class="TipIkona"><b>liczba z zakresu od -100 do 0</b></em>
                        </p>
                        
                        <p>
                          <label>Kategorie do jakich będzie przypisany rabat:</label>
                        </p>
                        
                        <div class="WybieranieKategoriiProducenta">
                        
                            <div id="drzewo" style="margin:0px;">   

                                <?php
                                $KategorieRabaty = explode(',', (string)$info['discount_categories_id']);
                                
                                if ( count($KategorieRabaty) > 10 || KATEGORIE_LISTING_EDYCJA == 'wszystkie' ) {
                                    //
                                    echo '<ul id="drzewoKategorii">';
                                    foreach(Kategorie::DrzewoKategoriiZarzadzanie() as $IdKategorii => $Tablica) {
                                        //
                                        echo Kategorie::WyswietlDrzewoKategoriiCheckbox($IdKategorii, $Tablica, $KategorieRabaty);
                                        //
                                    }    
                                    echo '</ul>';
                                    //
                                } else {
                                    //
                                    echo '<table class="pkc">';
                                    //
                                    $tablica_kat = Kategorie::DrzewoKategorii('0', '', '', '', false, true);
                                    for ($w = 0, $c = count($tablica_kat); $w < $c; $w++) {
                                        $podkategorie = false;
                                        if ($tablica_kat[$w]['podkategorie'] == 'true') { $podkategorie = true; }
                                        //
                                        $check = '';
                                        if ( in_array((string)$tablica_kat[$w]['id'], $KategorieRabaty) ) {
                                            $check = 'checked="checked"';
                                        }
                                        //                                
                                        echo '<tr>
                                                <td class="lfp"><input type="checkbox" value="'.$tablica_kat[$w]['id'].'" name="id_kat[]" id="id_kat_' . $tablica_kat[$w]['id'] . '" '.$check.' /> <label class="OpisFor" for="id_kat_' . $tablica_kat[$w]['id'] . '">'.$tablica_kat[$w]['text'].(($tablica_kat[$w]['status'] == 0) ? '<div class="wylKat TipChmurka"><b>Kategoria jest nieaktywna</b></div>' : '').'</label></td>
                                                <td class="rgp" '.(($podkategorie) ? 'id="img_'.$tablica_kat[$w]['id'].'"' : '').'>'.(($podkategorie) ? '<img src="obrazki/rozwin.png" alt="Rozwiń" onclick="podkat(\''.$tablica_kat[$w]['id'].'\',\'\',\'checkbox\')" />' : '').'</td>
                                              </tr>
                                              '.(($podkategorie) ? '<tr><td colspan="2"><div id="p_'.$tablica_kat[$w]['id'].'"></div></td></tr>' : '').'';
                                    }
                                    echo '</table>';
                                    unset($tablica_kat,$podkategorie);                                
                                    //
                                    foreach ( $KategorieRabaty as $kategoria ) {
                                    
                                        $sciezka = Kategorie::SciezkaKategoriiId($kategoria);
                                        $cSciezka = explode("_", (string)$sciezka);                    
                                        if (count($cSciezka) > 1) {
                                            //
                                            $ostatnie = strRpos($sciezka,'_');
                                            $analiza_sciezki = str_replace("_", ",", substr((string)$sciezka, 0, (int)$ostatnie));
                                            ?>
                                            
                                            <script>          
                                            podkat('<?php echo $analiza_sciezki; ?>', '<?php echo $cSciezka[count($cSciezka)-1]; ?>','checkbox','<?php echo implode(',', (array)$KategorieRabaty); ?>');
                                            </script>
                                            
                                        <?php
                                        unset($sciezka,$cSciezka);
                                        }
                                  
                                    }                             
                                }
                                unset($KategorieRabaty);
                                ?>                        

                            </div>
                            
                        </div>
                        
                        <p>
                          <input type="hidden" name="id_kategorii" id="id_kategorii" value="<?php echo $info['discount_categories_id']; ?>" />
                        </p>                          

                        <?php if ( count(explode(',', (string)$info['discount_groups_id'])) > 0 && strpos((string)$info['discount_groups_id'], ',') > - 1 ) { ?>
                        
                            <script>
                            $(document).ready(function() {
                                $('.NazwaGrupyKlientow').rules( "add", {  
                                    required: true, messages: { required: "Wybierz minimum jedną grupę klientów." }
                                });            
                            });
                            </script>            

                            <input type="hidden" name="tryb_rabat" value="1" />                        

                            <?php $TablicaGrupKlientow = Klienci::ListaGrupKlientow(false); ?>
                            
                            <table class="WyborCheckbox GrupyBlad">
                                <tr>
                                    <td><label>Grupa/grupy klientów:</label></td>
                                    <td>
                                        <?php                        
                                        foreach ( $TablicaGrupKlientow as $GrupaKlienta ) {
                                            $zaznacz = '';
                                            if ( strpos((string)$info['discount_groups_id'], ',' . (string)$GrupaKlienta['id'] . ',') > -1 ) {
                                                 $zaznacz = 'checked="checked"';
                                            }
                                            echo '<input type="checkbox" value="' . $GrupaKlienta['id'] . '" name="grupa_klientow[]" class="NazwaGrupyKlientow" id="grupa_klientow_' . $GrupaKlienta['id'] . '" ' . $zaznacz . ' /><label class="OpisFor" for="grupa_klientow_' . $GrupaKlienta['id'] . '">' . $GrupaKlienta['text'] . '</label><br />';
                                            unset($zaznacz);
                                        }              
                                        ?>
                                    </td>
                                </tr>
                            </table>   

                            <?php unset($TablicaGrupKlientow); ?>

                        <?php } else { ?>
                        
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
                                          if ( $klient['id'] == $info['discount_customers_id'] ) {
                                              //
                                              echo '<tr class="pozycja_off">';
                                              echo '<td><input type="radio" name="klient" id="klient_id_'.$klient['id'].'" value="' . $klient['id'] . '" checked="checked" disabled="disabled" /><label class="OpisForPustyLabel" for="klient_id_'.$klient['id'].'"></label></td>';
                                              echo '<td>' . $klient['id'] . '</td>';
                                              echo '<td>' . $klient['nazwa'] . '<br />' . $klient['adres'] . '</td>';
                                              
                                              if ( !empty($klient['firma']) ) {
                                                   echo '<td><span class="Firma">' . $klient['firma'] . '</span>' . ((!empty($klient['nip'])) ? 'NIP:&nbsp;' . $klient['nip'] : '') . '</td>';
                                                 } else{
                                                   echo '<td></td>';
                                              }
                                              
                                              echo '<td><span class="MalyMail">' . $klient['email'] . '</span></td>';
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
                        
                        </div>

                        <?php } ?>

                    </div>
                    
                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Zapisz dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('rabaty_kategorii','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','klienci');">Powrót</button>   
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