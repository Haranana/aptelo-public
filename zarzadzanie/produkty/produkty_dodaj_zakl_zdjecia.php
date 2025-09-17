<?php if ($prot->wyswietlStrone) { ?>

<div id="zakl_id_3" style="display:none;">

    <script type="text/javascript" src="javascript/jquery-ui.js"></script>

    <script>                      
    function dodaj_zdjecie() {
        var ile_pol = parseInt($("#ile_pol").val()) + 1;
        //
        $.get('ajax/dodaj_zdjecie.php', { id: ile_pol, katalog: '<?php echo KATALOG_ZDJEC; ?>' }, function(data) {
            if ( $('#wyniki .TabelaFoto').length ) {
                 $('#wyniki .TabelaFoto:last').after('<div class="TabelaFoto TabelaFotoPozycja" id="wyniki'+ile_pol+'" style="display:none">' + data + '</div>');
            } else {
                 $('#wyniki').html('<div class="TabelaFoto TabelaFotoPozycja" id="wyniki'+ile_pol+'" style="display:none">' + data + '</div>');
            }
            $('#wyniki' + ile_pol).stop().slideDown(300);
            $("#ile_pol").val(ile_pol);
            //
            var t = 1;
            $('#wyniki .TabelaFoto').each(function() {
               // input sortowanie
               $(this).find('.SortZdjecie').val(t);
               //
               t++;
            });            
            //
            pokazChmurki();            
        });
    } 
    function usun_zdjecie(id) {
        $('#wyniki' + id).removeClass('TabelaFotoPozycja').stop().slideUp(300, function() { 
          //
          $('#wyniki' + id).remove();
          //
          var t = 1;
          $('#wyniki .TabelaFoto').each(function() {
             // input sortowanie
             $(this).find('.SortZdjecie').val(t);
             //
             t++;
          });
          //
        })
        //
    }
    function usun_zdjecie_serwer(id) {
        //
        var url = '/zarzadzanie/produkty/produkty_usun_zdjecie.php';
        var form = $('<form action="' + url + '" method="post"><input type="text" name="zdjecie" value="' + $('#foto_' + id).val() + '" /><input type="text" name="id_produktu" value="<?php echo $id_produktu; ?>" /></form>');
        $('body').append(form);
        $(form).submit();
        //
    }   

    $(document).ready(function() {
      
        $('.PlikZdjeciaWczytaj').on("change", function() {
          
            var form = $("#poForm");
            var nr = $(this).attr('data-nr'); 
            
            var formdata = false;
            if (window.FormData){
                formdata = new FormData(form[0]);
            }       

            if ( formdata != false ) {
              
                $('#ekr_preloader').css('display','block');
                
                $.ajax({
                    //
                    url: 'ajax/ajax_zdjecie_wgraj.php?tok=<?php echo Sesje::Token(); ?>&nr=' + nr, 
                    type: 'POST',
                    contentType: false,
                    data: formdata,
                    processData: false,
                    cache: false,
                    dataType: 'json'
                    //
                }).done(function(wiadomosc) {
                    //
                    $('#ekr_preloader').css('display','none');
                    //
                    if ( wiadomosc.blad != '' ) {
                        //
                        alert(wiadomosc.blad);
                        //
                    } else {
                        //
                        $('#foto_' + nr).val(wiadomosc.zdjecie);
                        pokaz_obrazek_ajax('foto_' + nr, wiadomosc.zdjecie);
                        //
                    }
                    //
                });
                
            } else {
             
                alert('Wystąpił bład. Twoja przeglądarka nie obsługuje funkcji wgrywania plików.');

            }
            
        });  
   
        $("#wyniki").sortable({ 
            opacity: 0.6, 
            cursor: 'move', 
            update: function() {
                var t = 1;
                $('#wyniki .TabelaFoto').each(function() {
                   var idt = $(this).attr('id');
                   // input sortowanie
                   $(this).find('.SortZdjecie').attr('name', 'sort_' + t);
                   $(this).find('.SortZdjecie').val(t);
                   $(this).find('.SortZdjecie').attr('id', 'foto_sort_' + t);
                   // podglad zdjecia
                   $(this).find('.DivFoto').attr('id', 'divfoto_' + t);                   
                   $(this).find('.FoFoto').attr('id', 'fofoto_' + t);  
                   // input zdjecie
                   $(this).find('.InputZdjecie').attr('id', 'foto_' + t); 
                   $(this).find('.InputZdjecie').attr('name', 'zdjecie_' + t); 
                   // alt zdjecia
                   $(this).find('.AltZdjecie').attr('name', 'alt_' + t);                   
                   // wczytanie zdjecia
                   $(this).find('.PlikZdjeciaWczytaj').attr('name', 'plik_zdjecie_' + t);                   
                   // id tr
                   $(this).attr('id', 'wyniki' + t);
                   // do js
                   $(this).find('.DataNr').attr('data-nr', t); 
                   //
                   t++;
                });
            }								  
        });	
        $("#wyniki").disableSelection();
    });
    </script>

    <div class="info_content">
    
        <div class="ostrzezenie" style="margin:8px">Pierwsze zdjęcie na liście zostanie ustawione jako zdjęcie główne.</div>
        
        <br /><br />
        
        <div class="RamkaFoto">
        
            <div class="TabelaFoto TabelaFotoNaglowek">
 
                <div class="PozycjaFoto PozycjaKol1"><span>Sort</span></div>
                <div class="PozycjaFoto PozycjaKol2"><span>Zdjęcie</span></div>
                <div class="PozycjaFoto PozycjaKol3"><span>Ścieżka zdjęcia</span></div>
                <div class="PozycjaFoto PozycjaKol4"><span>Opis (znacznik alt i title)</span></div>
                <div class="PozycjaFoto PozycjaKol5"><span>Usuń</span></div>
                
                <div class="cl"></div>
                
            </div>
            
            <div id="wyniki">
            
            <div class="TabelaFoto TabelaFotoPozycja" id="wyniki1">
            
                <div class="PozycjaFoto PozycjaKol1">                              
                    <input type="text" name="sort_1" size="2" value="1" class="SortZdjecie" />     
                    <em class="TipIkona"><b>Kolejność wyświetlania zdjęć na karcie produktu</b></em>
                </div>                 
                <div class="PozycjaFoto PozycjaKol2">
                
                    <div class="DivFoto" id="divfoto_1" style="padding:5px;display:none">
                        <div class="FoFoto" id="fofoto_1" style="display:inline-block;vertical-align:middle;">
                            <img src="obrazki/_loader_small.gif" alt="" />
                        </div>
                    </div>
                    
                    <?php if (!empty($prod['products_image'])) { ?>
                    <script>          
                    pokaz_obrazek_ajax('foto_1', '<?php echo $prod['products_image']; ?>')
                    </script>                          
                    <?php } ?>
                    
                </div>
                <div class="PozycjaFoto PozycjaKol3">
                
                    <div class="PozycjaPodKol3">
                    
                        <input type="text" name="zdjecie_1" value="<?php echo ((isset($prod['products_image'])) ? $prod['products_image'] : ''); ?>" class="obrazek InputZdjecie DataNr" data-nr="1" ondblclick="openFileBrowser('foto_' + $(this).attr('data-nr'),'','<?php echo KATALOG_ZDJEC; ?>','produkt')" id="foto_1" autocomplete="off" />                 
                        <em class="TipIkona"><b>Kliknij dwukrotnie w pole obok żeby otworzyć okno przeglądarki zdjęć</b></em>
                        <em class="PrzegladarkaZdjec TipChmurka DataNr" data-nr="1" onclick="openFileBrowser('foto_' + $(this).attr('data-nr'),'','<?php echo KATALOG_ZDJEC; ?>','produkt')"><b>Kliknij żeby otworzyć okno przeglądarki zdjęć</b></em>
                        
                        <div style="margin-top:8px;">
                             <input type="file" data-nr="1" name="plik_zdjecie_1" class="PlikZdjeciaWczytaj DataNr" size="20" style="max-width:60%" />
                        </div>  
                    
                    </div>
                    
                </div> 
                <div class="PozycjaFoto PozycjaKol4">
                    <input class="AltZdjecie" type="text" name="alt_1" value="<?php echo ((isset($prod['products_image_description'])) ? $prod['products_image_description'] : ''); ?>" />                 
                </div> 
                <div class="PozycjaFoto PozycjaKol5">
                    <em class="TipChmurka"><b>Kliknij w ikonę żeby usunąć przypisane zdjęcie</b><img class="DataNr" data-nr="1" onclick="usun_zdjecie($(this).attr('data-nr'))" style="cursor:pointer" src="obrazki/kasuj.png" alt="Skasuj" /></em> 
                    
                    <?php if ( isset($_GET['id_poz']) && (int)$_GET['id_poz'] > 0 ) { ?>                        
                    <br /><br /><em class="TipChmurka" style="margin-top:5px"><b>Kliknij w ikonę żeby usunąć przypisane zdjęcie z produktu oraz Z SERWERA</b><img onclick="usun_zdjecie_serwer('1','<?php echo $id_produktu; ?>')" style="cursor:pointer;margin-left:4px" src="obrazki/kasuj_dysk.png" alt="Skasuj" /></em>
                    <?php } ?>
                    
                </div>
                
                <div class="cl"></div>
                
            </div>

            <?php
            $ile_dodatkowych_zdjec = 0;
            //
            if ($id_produktu > 0) {
                //
                $ktoreZdjecie = 2;
                //
                // pobieranie danych o dodatkowych zdjeciach produktu
                $zapytanie_zdjecie = "select distinct * from additional_images where products_id = '".$id_produktu."' order by sort_order";
                $sqls = $db->open_query($zapytanie_zdjecie);
                //
                while ($zdjecie = $sqls->fetch_assoc()) {
                    ?>
                    
                    <div class="TabelaFoto TabelaFotoPozycja" id="wyniki<?php echo $ktoreZdjecie; ?>">  
                   
                        <div class="PozycjaFoto PozycjaKol1">                           
                            <input type="text" name="sort_<?php echo $ktoreZdjecie; ?>" size="2" style="width:30px" value="<?php echo $zdjecie['sort_order']; ?>" class="SortZdjecie" />                 
                            <em class="TipIkona"><b>Kolejność wyświetlania zdjęć na karcie produktu</b></em>
                        </div>                        
                        <div class="PozycjaFoto PozycjaKol2"> 
                        
                            <div class="DivFoto" id="divfoto_<?php echo $ktoreZdjecie; ?>" style="padding:5px;display:none">
                                <div class="FoFoto" id="fofoto_<?php echo $ktoreZdjecie; ?>" style="display:inline-block;vertical-align:middle;">
                                    <img src="obrazki/_loader_small.gif" alt="" />
                                </div>
                            </div>
                            
                            <script>          
                            pokaz_obrazek_ajax('foto_<?php echo $ktoreZdjecie; ?>', '<?php echo $zdjecie['popup_images']; ?>')
                            </script>
                            
                        </div>
                        
                        <div class="PozycjaFoto PozycjaKol3">     

                            <div class="PozycjaPodKol3">
                            
                                <input type="text" name="zdjecie_<?php echo $ktoreZdjecie; ?>" value="<?php echo $zdjecie['popup_images']; ?>" class="obrazek InputZdjecie DataNr" data-nr="<?php echo $ktoreZdjecie; ?>" ondblclick="openFileBrowser('foto_' + $(this).attr('data-nr'),'','<?php echo KATALOG_ZDJEC; ?>','produkt')" id="foto_<?php echo $ktoreZdjecie; ?>" autocomplete="off" />                 
                                <em class="TipIkona"><b>Kliknij dwukrotnie w pole obok żeby otworzyć okno przeglądarki zdjęć</b></em>
                                <em class="PrzegladarkaZdjec TipChmurka DataNr" data-nr="<?php echo $ktoreZdjecie; ?>" onclick="openFileBrowser('foto_' + $(this).attr('data-nr'),'','<?php echo KATALOG_ZDJEC; ?>','produkt')"><b>Kliknij żeby otworzyć okno przeglądarki zdjęć</b></em>
                                
                                <div style="margin-top:8px;">
                                     <input type="file" data-nr="<?php echo $ktoreZdjecie; ?>" name="plik_zdjecie_<?php echo $ktoreZdjecie; ?>" class="PlikZdjeciaWczytaj DataNr" size="20" style="max-width:60%" />
                                </div>

                            </div>
                    
                        </div>
                        
                        <div class="PozycjaFoto PozycjaKol4">                           
                            <input class="AltZdjecie" type="text" name="alt_<?php echo $ktoreZdjecie; ?>" value="<?php echo $zdjecie['images_description']; ?>" />                 
                        </div>
                        
                        <div class="PozycjaFoto PozycjaKol5">  
                            <em class="TipChmurka"><b>Kliknij w ikonę żeby usunąć przypisane zdjęcie</b><img class="DataNr" data-nr="<?php echo $ktoreZdjecie; ?>" onclick="usun_zdjecie($(this).attr('data-nr'))" style="cursor:pointer" src="obrazki/kasuj.png" alt="Skasuj" /></em> <br />
                            
                            <?php if ( isset($_GET['id_poz']) && (int)$_GET['id_poz'] > 0 ) { ?>                        
                            <br /><em class="TipChmurka" style="margin-top:5px"><b>Kliknij w ikonę żeby usunąć przypisane zdjęcie z produktu oraz Z SERWERA</b><img onclick="usun_zdjecie_serwer('<?php echo $ktoreZdjecie; ?>','<?php echo $id_produktu; ?>')" style="cursor:pointer;margin-left:4px" src="obrazki/kasuj_dysk.png" alt="Skasuj" /></em>
                            <?php } ?>                                

                        </div>
                        
                        <div class="cl"></div>
                            
                    </div>

                    <?php
                    
                    $ktoreZdjecie++;
                    
                }
                //
                $ile_dodatkowych_zdjec = (int)$db->ile_rekordow($sqls);
                //
                $db->close_query($sqls); 
                unset($zapytanie_zdjecie, $zapytanie_zdjecie, $ktoreZdjecie);
            }
            ?>
            
            </div>
            
        </div>
        
        <input value="<?php echo ($ile_dodatkowych_zdjec + 1); ?>" type="hidden" name="ile_pol" id="ile_pol" />
        
        <div style="padding:10px;padding-top:20px">
            <span class="dodaj" onclick="dodaj_zdjecie()" style="cursor:pointer">dodaj kolejne zdjęcie</span>
            <span class="dodaj" onclick="openFileBrowser('','','<?php echo KATALOG_ZDJEC; ?>','produkt')" style="cursor:pointer;margin-left:30px;">otwórz przeglądarkę zdjęć</span>
        </div>  
        
        <?php
        unset($ile_dodatkowych_zdjec);
        ?>
        
    </div>
        
</div>

<?php } ?>
