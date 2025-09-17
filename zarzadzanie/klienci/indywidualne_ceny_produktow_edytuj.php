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
                array('cp_tax',(float)$_POST["v_at_1"]));

        //	
        $db->update_query('customers_price' , $pola, " cp_id = '".(int)$_POST["id"]."'");	

        unset($pola);
        //
        if ( isset($_POST['id_klient']) ) {
             //
             Funkcje::PrzekierowanieURL('klienci_edytuj.php?id_poz='.(int)$_POST["id_klient"].'&zakladka=11');
             //
          } else {
             //
             Funkcje::PrzekierowanieURL('indywidualne_ceny_produktow.php?id_poz='.(int)$_POST["id"]);
             //
        }
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
                cena_1: { required: true, number: true, min: 0.01 },
                brut_1: { required: true, number: true, min: 0.01 }
              }
            });
          });                          
          </script>        

          <form action="klienci/indywidualne_ceny_produktow_edytuj.php" method="post" id="eForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Edycja danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select * from customers_price where cp_id = '" . (int)$_GET['id_poz'] . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
            
                $info = $sql->fetch_assoc();
                ?>            
            
                <div class="pozycja_edytowana">
                
                    <div class="info_content">
                
                        <input type="hidden" name="akcja" value="zapisz" />
                        
                        <input type="hidden" name="id" value="<?php echo (int)$_GET['id_poz']; ?>" />

                        <?php
                        if ( isset($_GET['id_klient']) && (int)$_GET['id_klient'] ) {
                             echo '<input type="hidden" name="id_klient" value="' . (int)$_GET['id_klient'] . '" />';
                        }
                        //
                        $produkt_nazwa = $db->open_query("select distinct p.products_id, pd.products_name, p.products_tax_class_id, tx.tax_rate
                                                                     from products p
                                                           left join products_description pd on p.products_id = pd.products_id and pd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'
                                                           left join tax_rates tx on p.products_tax_class_id = tx.tax_rates_id 
                                                               where p.products_id = '".(int)$info['cp_products_id']."'");
                        $produkt = $produkt_nazwa->fetch_assoc();
                        //
                        $db->close_query($produkt_nazwa);    
                        unset($produkt_nazwa);
                        //
                        ?>                        

                        <p>
                          <label class="required" for="cena_1">Cena netto:</label>
                          <input type="text" class="oblicz" name="cena_1" id="cena_1" value="<?php echo $info['cp_price']; ?>" size="9" />
                          <input type="hidden" id="vat" value="<?php echo $produkt['tax_rate']; ?>" />
                        </p>
                        
                        <input type="hidden" name="v_at_1" id="v_at_1" value="<?php echo $info['cp_tax']; ?>" />
                        
                        <p>
                          <label class="required" for="brut_1">Cena brutto:</label>
                          <input type="text" class="oblicz_brutto min" name="brut_1" id="brut_1" value="<?php echo $info['cp_price_tax']; ?>" size="9" />
                        </p>                                
                        
                        <p>
                          <label for="nazwa_prod">Produkt:</label>
                          <input type="text" name="nazwa_prod" id="nazwa_prod" value="<?php echo $produkt['products_name']; ?>" size="83" disabled="disabled" />
                        </p> 
                        
                        <?php
                        unset($produkt);
                        ?>

                        <?php if ($info['cp_groups_id'] > 0) { ?>

                        <p>
                          <label for="grupa_klientow">Grupa klientów:</label>
                          <?php
                          $tablica = Klienci::ListaGrupKlientow(false);                                        
                          echo Funkcje::RozwijaneMenu('grupa_klientow', $tablica, $info['cp_groups_id'], 'disabled="disabled" id="grupa_klientow"');
                          unset($tablica);
                          ?>
                        </p>
                        
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
                                        if ( $klient['id'] == $info['cp_customers_id'] ) {
                                            //
                                            echo '<tr class="pozycja_off">';
                                            echo '<td><input type="radio" name="klient" id="klient" value="' . $klient['id'] . '" checked="checked" disabled="disabled" /><label class="OpisForPustyLabel" for="klient"></label></td>';
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
                  
                  <?php if ( isset($_GET['id_klient']) && (int)$_GET['id_klient'] ) { ?>
                  
                      <button type="button" class="przyciskNon" onclick="cofnij('klienci_edytuj','?id_poz=<?php echo (int)$_GET['id_klient']; ?>&zakladka=11','klienci');">Powrót</button>   
                  
                  <?php } else { ?>
                  
                      <button type="button" class="przyciskNon" onclick="cofnij('indywidualne_ceny_produktow','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','klienci');">Powrót</button>   
                      
                  <?php } ?>
                  
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