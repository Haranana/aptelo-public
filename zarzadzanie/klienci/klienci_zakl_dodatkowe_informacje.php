<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone && isset($_GET['id_klienta']) && (int)$_GET['id_klienta'] > 0 && Sesje::TokenSpr()) {   

    $ile_jezykow = Funkcje::TablicaJezykow();
    //
    $licznik_pol = 1;
    ?>
    
    <script>
    function dodaj_pole_informacji() {
        ile_pol = parseInt($("#ile_pol").val()) + 1;
        aktualny_jezyk = parseInt($("#aktualny_jezyk").val());
        //
        $.get('ajax/dodaj_dodatkowe_informacje_klienta.php', { id: ile_pol, jezyk: aktualny_jezyk }, function(data) {
            $('#info_tab_id_' + aktualny_jezyk).append(data);
            $("#ile_pol").val(ile_pol);
            //
            pokazChmurki(); 
            //
        });
    } 
    
    function zmien_id_jezyka(id) {
        $('#aktualny_jezyk').val(id);
    }
    </script>
    
    <div class="info_tab">
    
        <?php
        for ($w = 1, $c = count($ile_jezykow); $w <= $c; $w++) {
            echo '<span id="link_' . $w . '" class="a_href_info_tab" onclick="gold_tabs(\'' . $w . '\');zmien_id_jezyka(' . $w . ')">' . $ile_jezykow[$w - 1]['text'] . '</span>';
        }                    
        ?>   
        
    </div>
    
    <div style="clear:both"></div>
    
    <div class="info_tab_content">
    
        <?php for ($w = 1, $c = count($ile_jezykow); $w <= $c; $w++) { ?>
            
            <div id="info_tab_id_<?php echo $w; ?>" style="display:none;">
            
                <?php
                $zapytanie = "SELECT cafd.customers_account_fields_name, cafd.customers_account_fields_text, caf.customers_account_fields_type FROM customers_account_fields caf, customers_account_fields_description cafd WHERE caf.customers_id = '" . (int)$_GET['id_klienta'] . "' AND caf.customers_account_fields_id = cafd.customers_account_fields_id AND cafd.language_id = '" . $ile_jezykow[$w - 1]['id'] . "'";
                $sql = $db->open_query($zapytanie);
                
                while ( $info = $sql->fetch_assoc() ) {
                    ?>
            
                    <p>
                        <label for="tytul_<?php echo $licznik_pol + ($w * 100); ?>">Tytuł:</label>   
                        <input type="text" name="tytul_<?php echo $licznik_pol + ($w * 100); ?>" id="tytul_<?php echo $licznik_pol + ($w * 100); ?>" size="45" value="<?php echo $info['customers_account_fields_name']; ?>" />
                    </p> 
                    
                    <p>
                        <label for="wartosc_<?php echo $licznik_pol + ($w * 100); ?>">Wartość:</label>   
                        <textarea rows="5" cols="90" name="wartosc_<?php echo $licznik_pol + ($w * 100); ?>" id="wartosc_<?php echo $licznik_pol + ($w * 100); ?>"><?php echo $info['customers_account_fields_text']; ?></textarea>
                    </p> 

                    <p>
                      <label>Rodzaj wyświetlanej informacji</label>
                      <input type="radio" value="0" name="rodzaj_<?php echo $licznik_pol + ($w * 100); ?>" id="rodzaj_<?php echo $licznik_pol + ($w * 100); ?>_text" <?php echo (($info['customers_account_fields_type'] == 0) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="rodzaj_<?php echo $licznik_pol + ($w * 100); ?>_text"> tekst <em class="TipIkona"><b>Informacja będzie wyświetlana w formie tekstu</b></em></label> 
                      <input type="radio" value="1" name="rodzaj_<?php echo $licznik_pol + ($w * 100); ?>" id="rodzaj_<?php echo $licznik_pol + ($w * 100); ?>_link" <?php echo (($info['customers_account_fields_type'] == 1) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="rodzaj_<?php echo $licznik_pol + ($w * 100); ?>_link">link <em class="TipIkona"><b>Informacja będzie wyświetlana w formie linku - w polu wartość trzeba podać adres linku z http://</b></em></label>               
                      <input type="radio" value="2" name="rodzaj_<?php echo $licznik_pol + ($w * 100); ?>" id="rodzaj_<?php echo $licznik_pol + ($w * 100); ?>_plik" <?php echo (($info['customers_account_fields_type'] == 2) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="rodzaj_<?php echo $licznik_pol + ($w * 100); ?>_plik"> link do pliku <em class="TipIkona"><b>Kliknij dwukrotnie w pole obok żeby otworzyć okno przeglądarki zdjęć</b></em> <span class="PrzegladarkaZdjec TipChmurka" onclick="openFileAllBrowser('wartosc_<?php echo $licznik_pol + ($w * 100); ?>')"><b>Kliknij żeby otworzyć okno przeglądarki zdjęć</b></span></label>
                    </p> 

                    <?php
                    
                    $licznik_pol++;
                }
                
                $db->close_query($sql);
                unset($zapytanie);                 
                ?>
                
                <!-- dodaje pusty rekord -->
                
                <p>
                    <label for="tytul_<?php echo $licznik_pol + ($w * 100); ?>">Tytuł:</label>   
                    <input type="text" name="tytul_<?php echo $licznik_pol + ($w * 100); ?>" id="tytul_<?php echo $licznik_pol + ($w * 100); ?>" size="45" value="" />
                </p> 
                
                <p>
                    <label for="wartosc_<?php echo $licznik_pol + ($w * 100); ?>">Wartość:</label>   
                    <textarea rows="5" cols="90" name="wartosc_<?php echo $licznik_pol + ($w * 100); ?>" id="wartosc_<?php echo $licznik_pol + ($w * 100); ?>"></textarea>
                </p> 

                <p>
                  <label>Rodzaj wyświetlanej informacji</label>
                  <input type="radio" value="0" name="rodzaj_<?php echo $licznik_pol + ($w * 100); ?>" id="rodzaj_<?php echo $licznik_pol + ($w * 100); ?>_text" checked="checked" /><label class="OpisFor" for="rodzaj_<?php echo $licznik_pol + ($w * 100); ?>_text"> tekst <em class="TipIkona"><b>Informacja będzie wyświetlana w formie tekstu</b></em></label> 
                  <input type="radio" value="1" name="rodzaj_<?php echo $licznik_pol + ($w * 100); ?>" id="rodzaj_<?php echo $licznik_pol + ($w * 100); ?>_link" /><label class="OpisFor" for="rodzaj_<?php echo $licznik_pol + ($w * 100); ?>_link">link <em class="TipIkona"><b>Informacja będzie wyświetlana w formie linku - w polu wartość trzeba podać adres linku z http://</b></em></label>               
                  <input type="radio" value="2" name="rodzaj_<?php echo $licznik_pol + ($w * 100); ?>" id="rodzaj_<?php echo $licznik_pol + ($w * 100); ?>_plik" /><label class="OpisFor" for="rodzaj_<?php echo $licznik_pol + ($w * 100); ?>_plik"> link do pliku <em class="TipIkona"><b>Kliknij dwukrotnie w pole obok żeby otworzyć okno przeglądarki zdjęć</b></em> <span class="PrzegladarkaZdjec TipChmurka" onclick="openFileAllBrowser('wartosc_<?php echo $licznik_pol + ($w * 100); ?>')"><b>Kliknij żeby otworzyć okno przeglądarki zdjęć</b></span></label>
                </p>  

                <?php $licznik_pol++; ?>
                            
            </div>
            
        <?php } ?>   
        
    </div>   
    
    <input value="1" type="hidden" id="aktualny_jezyk" />
    
    <input value="<?php echo $licznik_pol; ?>" type="hidden" name="ile_pol" id="ile_pol" />
    
    <div style="padding:10px">
        <span class="dodaj" onclick="dodaj_pole_informacji()" style="cursor:pointer">dodaj kolejną pozycję</span>
    </div>      
    
    <script>
    gold_tabs('1');
    </script>       
    
    <?php
    
    unset($ile_jezykow, $licznik_pol);
    
}
?>