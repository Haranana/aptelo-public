<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Generowanie mapy strony XML</div>
    <div id="cont">

          <div class="poleForm">
            <div class="naglowek">Plik mapy strony</div>

                <div class="pozycja_edytowana">  

                    <input type="hidden" id="kategorie" value="<?php echo ((isset($_POST['kategorie'])) ? '1' : '0'); ?>" />
                    <input type="hidden" id="strony_info" value="<?php echo ((isset($_POST['strony_info'])) ? '1' : '0'); ?>" />
                    <input type="hidden" id="ankiety" value="<?php echo ((isset($_POST['ankiety'])) ? '1' : '0'); ?>" />
                    <input type="hidden" id="galerie" value="<?php echo ((isset($_POST['galerie'])) ? '1' : '0'); ?>" />
                    <input type="hidden" id="formularze" value="<?php echo ((isset($_POST['formularze'])) ? '1' : '0'); ?>" />
                    <input type="hidden" id="producenci" value="<?php echo ((isset($_POST['producenci'])) ? '1' : '0'); ?>" />
                    <input type="hidden" id="aktualnosci" value="<?php echo ((isset($_POST['aktualnosci'])) ? '1' : '0'); ?>" />
                    <input type="hidden" id="recenzje" value="<?php echo ((isset($_POST['recenzje'])) ? '1' : '0'); ?>" />
                    
                    <input type="hidden" id="kategorie_zdjecia" value="<?php echo ((isset($_POST['kategorie_zdjecia'])) ? '1' : '0'); ?>" />
                    <input type="hidden" id="produkty_zdjecia" value="<?php echo ((isset($_POST['produkty_zdjecia'])) ? '1' : '0'); ?>" />
                    <input type="hidden" id="aktualnosci_zdjecia" value="<?php echo ((isset($_POST['aktualnosci_zdjecia'])) ? '1' : '0'); ?>" />
                    <input type="hidden" id="producenci_zdjecia" value="<?php echo ((isset($_POST['producenci_zdjecia'])) ? '1' : '0'); ?>" />

                    <input type="hidden" id="changefreq" value="<?php echo ((isset($_POST['changefreq'])) ? '1' : '0'); ?>" />
                    <input type="hidden" id="priority" value="<?php echo ((isset($_POST['priority'])) ? '1' : '0'); ?>" />
                    
                    <input type="hidden" id="index" value="<?php echo $_POST['index']; ?>" />    
                    <?php
                    $_POST['jezyk'] = $_SESSION['domyslny_jezyk']['id'];
                    ?>
                    <input type="hidden" id="jezyk" value="<?php echo (int)$_POST['jezyk']; ?>" />
                    
                    <?php
                    $jezyki = "SELECT code FROM languages WHERE languages_id = '".(int)$_POST['jezyk']."'";
                    $sql = $db->open_query($jezyki);
                    $KodJezyka = $sql->fetch_assoc();
                    $db->close_query($sql);                    
                    ?>                     
                    <input type="hidden" id="jezyk_kod" value="<?php echo $KodJezyka['code']; ?>" />
                    <?php
                    $plikDoZapisu = '../sitemap.xml';
                    unset($KodJezyka, $jezyki);
                    ?>                  

                    <input type="hidden" id="plik" value="<?php echo $plikDoZapisu; ?>" />                    
                    
                    <input type="hidden" id="priorytet_produkty" value="<?php echo $filtr->process($_POST['priorytet_produkty']); ?>" />
                    <input type="hidden" id="priorytet_kategorie" value="<?php echo $filtr->process($_POST['priorytet_kategorie']); ?>" />
                    <input type="hidden" id="priorytet_strony_info" value="<?php echo $filtr->process($_POST['priorytet_strony_info']); ?>" />
                    <input type="hidden" id="priorytet_ankiety" value="<?php echo $filtr->process($_POST['priorytet_ankiety']); ?>" />
                    <input type="hidden" id="priorytet_galerie" value="<?php echo $filtr->process($_POST['priorytet_galerie']); ?>" />
                    <input type="hidden" id="priorytet_formularze" value="<?php echo $filtr->process($_POST['priorytet_formularze']); ?>" />
                    <input type="hidden" id="priorytet_producenci" value="<?php echo $filtr->process($_POST['priorytet_producenci']); ?>" />
                    <input type="hidden" id="priorytet_aktualnosci" value="<?php echo $filtr->process($_POST['priorytet_aktualnosci']); ?>" />
                    <input type="hidden" id="priorytet_recenzje" value="<?php echo $filtr->process($_POST['priorytet_recenzje']); ?>" />
                    
                    <input type="hidden" id="automat" value="<?php echo ((isset($_POST['automat'])) ? '1' : '0'); ?>" />
                    <input type="hidden" id="obrazki" value="<?php echo ((isset($_POST['obrazki'])) ? '1' : '0'); ?>" />
                    
                    <?php
                    $sql_ilosc = $db->open_query("select distinct p.products_id
                                                    from products p
                                               left join products_to_categories ptc on ptc.products_id = p.products_id
                                              right join categories c ON c.categories_id = ptc.categories_id AND c.categories_status = '1'                             
                                                   where p.products_status = '1' and (p.customers_group_id = '0' or p.customers_group_id = '')");
                    ?>

                    <div id="import">
                    
                        <div id="postep">Postęp zapisu ...</div>
                    
                        <div id="suwak">
                            <div style="margin:1px;overflow:hidden">
                                <div id="suwak_aktywny"></div>
                            </div>
                        </div>

                        <div id="procent"></div>  

                    </div>   
                    
                    <div id="wynik_dzialania" style="display:none">
                        Dane zostały zapisane w pliku <span><?php echo str_replace('../', '', (string)$plikDoZapisu); ?></span>
                    </div>                             

                    <script>
                    var ilosc_linii = <?php echo (int)$db->ile_rekordow($sql_ilosc) - 1; ?>;                    
                    //
                    function generuj_xml(limit) {

                        $.post( "pozycjonowanie/mapa_strony_akcja.php?tok=<?php echo Sesje::Token(); ?>", 
                              { 
                                plik: $('#plik').val(),
                                kategorie: $('#kategorie').val(),
                                strony_info: $('#strony_info').val(),
                                ankiety: $('#ankiety').val(),
                                galerie: $('#galerie').val(),
                                formularze: $('#formularze').val(),
                                producenci: $('#producenci').val(),
                                aktualnosci: $('#aktualnosci').val(),
                                recenzje: $('#recenzje').val(),
                                index: $('#index').val(),
                                jezyk: $('#jezyk').val(),
                                jezyk_kod: $('#jezyk_kod').val(),
                                priorytet_produkty: $('#priorytet_produkty').val(),
                                priorytet_kategorie: $('#priorytet_kategorie').val(),
                                priorytet_strony_info: $('#priorytet_strony_info').val(),
                                priorytet_ankiety: $('#priorytet_ankiety').val(),
                                priorytet_galerie: $('#priorytet_galerie').val(),
                                priorytet_formularze: $('#priorytet_formularze').val(),
                                priorytet_producenci: $('#priorytet_producenci').val(),
                                priorytet_aktualnosci: $('#priorytet_aktualnosci').val(),
                                priorytet_recenzje: $('#priorytet_recenzje').val(),
                                automat: $('#automat').val(),
                                kategorie_zdjecia: $('#kategorie_zdjecia').val(),
                                produkty_zdjecia: $('#produkty_zdjecia').val(),
                                aktualnosci_zdjecia: $('#aktualnosci_zdjecia').val(),
                                producenci_zdjecia: $('#producenci_zdjecia').val(),
                                changefreq: $('#changefreq').val(),
                                priority: $('#priority').val(),
                                limit: limit,
                                limit_max: ilosc_linii
                              },
                              function(data) {

                                 if (ilosc_linii == 1) {
                                     procent = 100;
                                   } else {
                                     procent = parseInt((limit / (ilosc_linii - 1)) * 100);
                                     if (procent > 100) {
                                         procent = 100;
                                     }
                                 }

                                 $('#procent').html('Stopień realizacji: <span>' + procent + '%</span>');
                                 
                                 $('#suwak_aktywny').css('width' , (procent * 5) + 5 + 'px');

                                 if (ilosc_linii > limit) {
                                    generuj_xml(limit + 100);
                                   } else {
                                    //
                                    if ( ilosc_linii > 19999 ) {
                                         //
                                         $('#procent').html('Dzielę plik na części ...');
                                         //
                                         $.post( "pozycjonowanie/mapa_strony_podziel.php?tok=<?php echo Sesje::Token(); ?>", { podziel: 'tak', plik: $('#plik').val() },
                                            function(data) {
                                              //
                                              $('#procent').css('display','none');
                                              $('#postep').css('display','none');
                                              $('#suwak').slideUp("fast");
                                              $('#wynik_dzialania').slideDown("fast");
                                              $('#przyciski').slideDown("fast");
                                            }
                                         );
                                         //
                                    } else {
                                         //
                                         $('#postep').css('display','none');
                                         $('#suwak').slideUp("fast");
                                         $('#wynik_dzialania').slideDown("fast");
                                         $('#przyciski').slideDown("fast");                                         
                                         //
                                    }
                                    //
                                 } 
                              }                          
                        );
                        
                    }; 
                    </script>   
                    
                    <div class="przyciski_dolne" id="przyciski" style="padding-left:0px; display:none">
                      <button type="button" class="przyciskNon" onclick="cofnij('mapa_strony','<?php echo Funkcje::Zwroc_Get(array('x','y')); ?>','pozycjonowanie');">Powrót</button> 
                    </div>    

                    <script>                   
                    // sprawdza czy wogole jest cos do exportu
                    generuj_xml(0);
                    </script> 

                </div>
           
          </div>                      

    </div>    
    
    <?php
    include('stopka.inc.php');

}