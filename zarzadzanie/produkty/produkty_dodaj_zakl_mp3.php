<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone && isset($_GET['id_produktu']) && (int)$_GET['id_produktu'] >= 0 && Sesje::TokenSpr()) { 

    $id_produktu = (int)$_GET['id_produktu'];
 
    ?>

    <script>      
    $(document).ready(function(){
        pokazChmurki();    
    });    
    
    function dodaj_mp3() {
        ile_pol_mp3 = parseInt($("#ile_pol_mp3").val()) + 1;
        //
        $('#mp3t').append('<tr id="mp3_'+ile_pol_mp3+'" class="PozycjaMp3"></tr>');
        //
        $.get('ajax/dodaj_mp3.php', { id: ile_pol_mp3, katalog: '<?php echo KATALOG_ZDJEC; ?>' }, function(data) {
            $('#mp3_'+ile_pol_mp3).html(data);
            $("#ile_pol_mp3").val(ile_pol_mp3);
            $("#brak_mp3").hide();
        });
    } 
    
    function usun_mp3(id) {
        $('#mp3_' + id).remove();
        //
        if ( $('.PozycjaMp3').length == 0 ) {
             $("#brak_mp3").show();
        } else {
             $("#brak_mp3").hide();
        }          
    }
    </script>

    <div class="info_content">
        
        <div class="RamkaFoto">
        
            <table class="TabelaMp3" id="mp3t" style="margin-bottom:5px">
                <tr class="TabelaMp3_naglowek">
                    <td style="width:45%"><span>Nazwa pliku</span></td>
                    <td style="width:53%"><span>Tytuł utworu</span></td>
                    <td style="width:2%"><span>Usuń</span></td>
                </tr>

                <?php
                //
                // pobieranie danych o plikach mp3
                $zapytanie_mp3 = "select distinct * from products_mp3 where products_id = '".$id_produktu."'";
                $sqls = $db->open_query($zapytanie_mp3);
                
                $ile_mp3 = (int)$db->ile_rekordow($sqls);
                
                $nr_utworu = 1;
                
                if ( $ile_mp3 > 0 ) {
                    //
                    while ($utwor = $sqls->fetch_assoc()) {
                        ?>
                        
                        <tr id="mp3_<?php echo $nr_utworu; ?>" class="PozycjaMp3">    
                            <td>                              
                                <input type="text" name="utwor_mp3_<?php echo $nr_utworu; ?>" value="<?php echo $utwor['products_mp3_file']; ?>" ondblclick="openFileBrowser('plik_mp3_<?php echo $nr_utworu; ?>','','<?php echo KATALOG_ZDJEC; ?>')" id="plik_mp3_<?php echo $nr_utworu; ?>" autocomplete="off" />                 
                                <em class="TipIkona"><b>Kliknij dwukrotnie w pole obok żeby otworzyć okno przeglądarki zdjęć</b></em>
                                <em class="PrzegladarkaZdjec TipChmurka" onclick="openFileBrowser('plik_mp3_<?php echo $nr_utworu; ?>','','<?php echo KATALOG_ZDJEC; ?>')"><b>Kliknij żeby otworzyć okno przeglądarki zdjęć</b></em>
                            </td> 
                            <td>                              
                                <input type="text" name="nazwa_mp3_<?php echo $nr_utworu; ?>" value="<?php echo $utwor['products_mp3_name']; ?>" />                 
                            </td> 
                            <td>
                                <em class="TipChmurka"><b>Skasuj</b><img onclick="usun_mp3('<?php echo $nr_utworu; ?>')" style="cursor:pointer" src="obrazki/kasuj.png" alt="Skasuj" /></em>
                            </td>
                        </tr>

                        <?php
                        
                        $nr_utworu++;

                    }
                    //
                    unset($nr_utworu);    
                }
                
                //
                $db->close_query($sqls); 
                unset($zapytanie_mp3);                
                ?>
                
                <tr id="brak_mp3" <?php echo (($ile_mp3 > 0) ? 'style="display:none"' : ''); ?>>
                    <td colspan="3"><div class="maleInfo" style="display:inline-block">Brak przypisanych utworów mp3 do produktu ...</div></td>
                </tr>                    

            </table>
            
        </div>
        
        <input value="<?php echo $ile_mp3; ?>" type="hidden" name="ile_pol_mp3" id="ile_pol_mp3" />
        
        <div style="padding:10px;padding-top:20px;">
            <span class="dodaj" onclick="dodaj_mp3()" style="cursor:pointer">dodaj kolejny utwór</span>
        </div>  

        <?php
        unset($ile_mp3);
        ?>
        
    </div>
        
<?php } ?>
