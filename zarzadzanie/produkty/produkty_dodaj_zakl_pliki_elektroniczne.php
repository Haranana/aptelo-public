<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone && isset($_GET['id_produktu']) && (int)$_GET['id_produktu'] >= 0 && Sesje::TokenSpr()) { 

    $id_produktu = (int)$_GET['id_produktu'];
    
    $ile_jezykow = Funkcje::TablicaJezykow();
    $jezyk_szt = count($ile_jezykow);
 
    ?>
    <div class="info_tab" style="padding-top:0px">
    <?php
    $licznik_zakladek = (int)$_GET['id_tab'];
    $liczba = $licznik_zakladek;
    for ($w = 0; $w < $jezyk_szt; $w++) {
        echo '<span id="link_'.$licznik_zakladek.'" class="a_href_info_tab" onclick="gold_tabs(\''.$licznik_zakladek.'\')">'.$ile_jezykow[$w]['text'].'</span>';
        $licznik_zakladek++;
    }                      
    ?>                   
    </div>
    
    <div style="clear:both"></div>
    
    <script>
    $(document).ready(function(){
        pokazChmurki();    
    });
    
    function dodaj_plik_elektroniczny(id_jezyk) {
        //
        $('#BrakPlikowElektronicznych_' + id_jezyk).hide();
        //
        ile_plikow = parseInt($("#ile_plikow_"+id_jezyk).val()) + 1;
        //
        $('#pliki_elektroniczne_'+id_jezyk).append('<div id="div_plik_elektroniczny_'+ile_plikow+'_'+id_jezyk+'" style="padding-bottom:8px" class="PolePlikowElektronicznych"></div>');
        $('#div_plik_elektroniczny_'+ile_plikow+'_'+id_jezyk).css('display','none');
        //
        $.get('ajax/dodaj_plik_elektroniczny.php?tok=<?php echo Sesje::Token(); ?>', { ilosc: ile_plikow, id: id_jezyk }, function(data) {
            $('#div_plik_elektroniczny_'+ile_plikow+'_'+id_jezyk).html(data);
            $('#div_plik_elektroniczny_'+ile_plikow+'_'+id_jezyk).find('.NaglowekLinki span').html( $('#pliki_elektroniczne_'+id_jezyk).find('.PolePlikowElektronicznych').length );
            $("#ile_plikow_"+id_jezyk).val(ile_plikow);
            
            $('#div_plik_elektroniczny_'+ile_plikow+'_'+id_jezyk).slideDown("fast");
        });
    }    
    
    function usun_plik_elektroniczny(id, id_jezyk) {
        $('#div_' + id).remove();
        //
        ir = 1;
        $('#pliki_elektroniczne_'+id_jezyk).find('.PolePlikowElektronicznych').each(function() {
            $(this).find('.NaglowekLinki span').html(ir);
            ir++;
        });
        if ( $('#pliki_elektroniczne_'+id_jezyk).find('.PolePlikowElektronicznych').length == 0 ) {
             $('#BrakPlikowElektronicznych_' + id_jezyk).show();
        } else {
             $('#BrakPlikowElektronicznych_' + id_jezyk).hide();
        }        
    }       
    </script>     
    
    <div class="info_tab_content">
    
        <?php
        for ($w = 0; $w < $jezyk_szt; $w++) {
            ?>
            
            <div id="info_tab_id_<?php echo $w + $liczba; ?>" style="display:none;">
            
            <div id="pliki_elektroniczne_<?php echo $w; ?>">
                
                <?php
                $przypisane_pliki = array();
                
                if ($id_produktu > 0) {
                    // pobieranie danych jezykowych
                    $zapytanie_tmp = "select distinct * from products_file_shopping where products_id = '".$id_produktu."' and language_id = '" .$ile_jezykow[$w]['id']."'";
                    $sqls = $db->open_query($zapytanie_tmp);
                    while ($plik = $sqls->fetch_assoc()) {
                        //
                        $przypisane_pliki[ $plik['products_file_shopping_unique_id'] ] = array( 'id' => $plik['products_file_shopping_unique_id'],
                                                                                                'plik' => $plik['products_file_shopping'],
                                                                                                'nazwa' => $plik['products_file_shopping_name'] );
                        //
                    } 
                    //
                    $db->close_query($sqls); 
                    unset($zapytanie_tmp, $plik);
                    //
                }

                if ( count($przypisane_pliki) > 0 ) {
                    //
                    $l = 1;
                    foreach ( $przypisane_pliki as $plik ) {
                      //
                      ?>
                      
                      <div id="div_plik_elektroniczny_<?php echo $l; ?>_<?php echo $w; ?>" style="padding-bottom:8px" class="PolePlikowElektronicznych">
                      
                          <div class="NaglowekLinki">Plik elektroniczny nr <span><?php echo $l; ?></span></div>
                          
                          <p>
                            <label for="plik_elektroniczny_nazwa_<?php echo $l; ?>_<?php echo $w; ?>">Nazwa do wyświetlania:</label>
                            <input type="text" name="plik_elektroniczny_nazwa_<?php echo $l; ?>_<?php echo $w; ?>" id="plik_elektroniczny_nazwa_<?php echo $l; ?>_<?php echo $w; ?>" size="80" value="<?php echo $plik['nazwa']; ?>" />
                          </p> 

                          <p>
                            <label for="plik_elektroniczny_<?php echo $l; ?>_<?php echo $w; ?>">Plik:</label>
                            <input type="text" name="plik_elektroniczny_<?php echo $l; ?>_<?php echo $w; ?>" size="80" ondblclick="openFileAllBrowser('plik_elektroniczny_<?php echo $l; ?>_<?php echo $w; ?>')" id="plik_elektroniczny_<?php echo $l; ?>_<?php echo $w; ?>" value="<?php echo $plik['plik']; ?>" autocomplete="off" />
                            <em class="TipIkona"><b>Kliknij dwukrotnie w pole obok żeby otworzyć okno przeglądarki zdjęć</b></em>
                            <span class="usun_plik TipIkona" onclick="usun_plik_elektroniczny('plik_elektroniczny_<?php echo $l; ?>_<?php echo $w; ?>','<?php echo $w; ?>')" data-plik="plik_elektroniczny_<?php echo $l; ?>_<?php echo $w; ?>"><b>Usuń przypisany plik</b></span>
                          </p>  

                      </div>
                      
                      <?php
                      //   
                      $l++;
                    }
                    //
                    unset($l);
                    //
                } ?>

                <div class="maleInfo" id="BrakPlikowElektronicznych_<?php echo $w; ?>" <?php echo ((count($przypisane_pliki) > 0) ? 'style="display:none"' : ''); ?>>Brak przypisanych plików elektronicznych do produktu ...</div>    

            </div>

            <input value="<?php echo count($przypisane_pliki); ?>" type="hidden" name="ile_plikow_<?php echo $w; ?>" id="ile_plikow_<?php echo $w; ?>" />
            
            <div style="padding:10px">
                <span class="dodaj" onclick="dodaj_plik_elektroniczny(<?php echo $w; ?>)" style="cursor:pointer">dodaj plik do pobrania</span>
            </div>   
            
            </div>
            <?php  
            //
            unset($przypisane_pliki);
        }   
        ?>       

    </div>   
    
    <script>
    gold_tabs('<?php echo (int)$_GET['id_tab']; ?>');
    </script>    

    <br />
    
    <div class="info_content">
    
        <script>
        $(document).ready(function() {
          
            $('#WybranyPlikWczytania').on("change", function() {

                var form = $("#poForm");
                
                var formdata = false;
                if (window.FormData){
                    formdata = new FormData(form[0]);
                }       

                if ( formdata != false ) {
                
                    $.ajax({
                        //
                        url: 'ajax/ajax_plik_wczytaj.php?tok=<?php echo Sesje::Token(); ?>', 
                        type: 'POST',
                        contentType: false,
                        data: formdata,
                        processData: false,
                        cache: false,
                        dataType: 'json'
                        //
                    }).done(function(wiadomosc) {
                        //
                        if ( wiadomosc.blad != '' ) {
                            //
                            alert(wiadomosc.blad);
                            //
                        } else {
                            //
                            $('#kody_cyfrowe').val(wiadomosc.dane);
                            //
                        }
                        //
                    });
                    
                } else {
                 
                    alert('Wystąpił bład. Twoja przeglądarka nie obsługuje funkcji wgrywania plików.');

                }
                
            });  
            
        });
        </script>         
    
        <div class="CechyNaglowek">Sprzedaż elektroniczna kluczy licencyjnych</div>
        
        <div class="CechyInfo">
            <div class="ostrzezenie">
                Każdy dodany kod musi być umieszczony w nowym wierszu (osobnej linii).
            </div>
        </div>            
        
        <?php
        $kody = '';
        $id_automater = 0;
        //
        if ($id_produktu > 0) {
            $zapytanie_tmp = "select distinct products_code_shopping, automater_products_id from products where products_id = '".$id_produktu."'";
            $sqls = $db->open_query($zapytanie_tmp);
            $kod_tmp = $sqls->fetch_assoc();          
            //
            $id_automater = $kod_tmp['automater_products_id'];
            $kody = $kod_tmp['products_code_shopping'];
            //
            $db->close_query($sqls);
            unset($zapytanie_tmp, $kod_tmp);
            //            
        }
        ?>
        
        <textarea name="kody_cyfrowe" id="kody_cyfrowe" style="margin-left:10px" cols="80" rows="20"><?php echo $kody; ?></textarea>
        
        <p style="padding:12px;">
          <label for="upload">Wczytaj kodu z pliku:</label>
          <input type="file" name="plik_kody" id="WybranyPlikWczytania" size="53" />
        </p>

        <div style="padding-left:12px;">
        
            <span class="maleInfo" style="margin-left:0px">Maksymalna wielkość pliku do wczytania: <?php echo Funkcje::MaxUpload(); ?> Mb</span>

        </div>

        <?php unset($kody); ?>
        
        <?php if ( INTEGRACJA_AUTOMATER_WLACZONY == 'tak' ) { ?>
        
        <br /><br />
        
        <div class="CechyNaglowek">Integracja z Automater.pl - powiązanie produktów</div>
        
        <div style="padding-left:10px;">
        
            <?php 
            $produktyAutomater = Automater::ListaProduktow();
            
            if ( count($produktyAutomater) > 0 ) {
            
                 echo '<select name="produkt_automater">';
                 
                 echo '<option value="0"' . (($tmp['id'] == $id_automater) ? ' selected="selected"' : '') . '>--- nie powiązany ---</option>';
                 
                 foreach ( $produktyAutomater as $tmp ) {
                   
                    echo '<option value="' . $tmp['id'] . '"' . (($tmp['id'] == $id_automater) ? ' selected="selected"' : '') . '>' . $tmp['nazwa'] . ' (id: ' . $tmp['id'] . ')</option>';
                   
                 }
                 
                 echo '</select>';
            
            } else {
             
                echo '<span style="color:#ff0000">Brak zdefiniowanych produktów w Automater</span>';
              
            }
            
            ?>
        
        </div>
        
        <?php } ?>
        
    </div>

<?php } ?>