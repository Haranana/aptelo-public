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
    
    <div id="naglowek_cont">Wysyłanie newslettera</div>
    <div id="cont">
          
          <form action="newsletter/newsletter_wyslij.php" method="post" class="cmxform" id="newsWyslij">          

          <div class="poleForm">
            <div class="naglowek">Wysyłanie newslettera</div>
            
            <?php
            $zapytanie = "select * from newsletters where newsletters_id = '" . (int)$_GET['id_poz'] . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
            
                if ( !isset($_GET['test']) ) {
                    //
                    $pola = array(array('date_sent','now()'));
                    $db->update_query('newsletters' , $pola, " newsletters_id = '" . (int)$_GET['id_poz'] . "'");
                    unset($pola);            
                    //
                }
                
                $info = $sql->fetch_assoc();
                
                if ( !isset($_GET['test']) ) {

                    $AdresyDoWyslania = Newsletter::AdresyEmailNewslettera($info['newsletters_id']);
                    
                    $IloscDoWyslania = count($AdresyDoWyslania);
                    
                    echo '<script>var adresy = new Array(';
                    
                    $AdresyJs = '';
                    
                    for ($x = 0; $x < $IloscDoWyslania; $x++ ) {
                      
                          $AdresyJs .= "'" . $AdresyDoWyslania[$x] . "',";
                          
                    }
                    
                    echo substr((string)$AdresyJs, 0, -1);
                    
                    echo ');';
                    
                    echo '</script>';
                    
                } else {
                  
                    $IloscDoWyslania = 1;
                    
                    echo '<script>var adresy = new Array(' . "'" . INFO_EMAIL_SKLEPU . "'" . ');</script>';               
                  
                }
                ?>
                
                <input type="hidden" name="ogolny_limit" id="ogolny_limit" value="<?php echo $IloscDoWyslania; ?>" />
                <input type="hidden" name="wskaznik" id="wskaznik" value="<?php echo (int)NEWSLETTER_WSKAZNIK; ?>" />

                <script>
                //
                function wyslij_newsletter(id) {

                    if ($('#import').css('display') == 'none') {
                        $('#import').slideDown("fast");
                        $('#przyciski').slideUp("fast");
                    }
                    
                    $('.ZakresWysylaniaNewslettera').stop().slideUp();
                    
                    wskaznik = parseInt($('#wskaznik').val());
                    
                    var adresy_tmp_tablica = new Array();

                    for (s = 0; s < adresy.length; s++ ) {
                         //
                         if ( s >= parseInt($('#zakres_od').val()) - 1 && s <= parseInt($('#zakres_do').val()) - 1 ) {
                              //
                              adresy_tmp_tablica.push(adresy[s]);
                              //
                         }
                         //
                    }
                    
                    var adresy_koncowa = new Array();
                    var nr = 0;
                    var adresy_tmp = '';

                    var i, j, tmp_tablica, przeskok = wskaznik;
                    
                    for ( i = 0, j = adresy_tmp_tablica.length; i < j; i += przeskok ) {
                      
                        tmp_tablica = adresy_tmp_tablica.slice(i,i+przeskok);
      
                        for ( x = 0; x < tmp_tablica.length; x++ ) {
                              //
                              adresy_tmp = adresy_tmp + tmp_tablica[x] + ';';
                              //
                        }                      

                        adresy_koncowa[nr] = adresy_tmp;                        
                        adresy_tmp = '';              
                        nr++;
                        
                    }
                    
                    wyslij_newsletter_maile(id, adresy_koncowa, 0, 0);
                    
                }
                
                function wyslij_newsletter_maile(id, tablica, nr, wyslane) {

                    tmpa = tablica[nr];
                    
                    $.post( "ajax/wyslij_newsletter.php?tok=<?php echo Sesje::Token(); ?>", 
                          { 
                            id: <?php echo $info['newsletters_id']; ?>,
                            <?php echo ((isset($_GET['test'])) ? 'test: 0,' : ''); ?>
                            adresy: tmpa,
                            lp: (parseInt($('#zakres_od').val()) + wyslane)
                          },
                          function(data) {

                             if ( data != '' ) {
                                  $('#blad').html( $('#blad').html() + data );
                             }

                             procent = parseInt(((nr + 1) / tablica.length) * 100);
                             if ( procent > 100 ) {
                                  procent = 100;
                             }
                             //
                             var ile_mail = (nr + 1) * parseInt($('#wskaznik').val());
                             if ( ile_mail > parseInt($('#ogolny_limit').val()) ) {
                                  ile_mail = $('#ogolny_limit').val();
                             }                             
                             //
                             $('#procent').html('Stopień realizacji: <span>' + procent + '%</span><br />Wysłano maili: <span>' + ile_mail + '</span>');
                             
                             $('#suwak_aktywny').css('width' , (procent * 5) + 5 + 'px');
                             
                             if (tablica.length > nr + 1) {
                                
                                wyslij_newsletter_maile(id, tablica, nr + 1, ile_mail);
                                
                               } else {

                                if ( $('#blad').html() != '' ) {
                                     
                                     $('#p_wyslij').css('display','none');
                                     $('#postep').css('display','none');
                                     $('#suwak').slideUp("fast");
                                     $('#procent').slideUp("fast");
                                     $('#przyciski').slideDown("fast");
                                     
                                   } else { 
                                   
                                    $('#p_wyslij').css('display','none');
                                    $('#postep').css('display','none');
                                    $('#suwak').slideUp("fast");
                                    $('#wynik_dzialania').slideDown("fast");
                                    $('#przyciski').slideDown("fast");
                                    
                                }
                             }   

                          }
                              
                    );

                }; 
                </script>                
            
                <div class="pozycja_edytowana">
                
                    <div id="DaneNewslettera">
                    
                        <div>
                            Tytuł newslettera: <span><?php echo $info['title']; ?></span>
                        </div>
                        
                        <?php
                        if ( !isset($_GET['test']) ) {
                        ?>
                        
                        <div>
                            Odbiorcy newslettera: 
                            <?php
                            switch ($info['destination']) {
                                case "1":
                                    $doKogo = 'Do wszystkich zarejestrowanych klientów sklepu';
                                    break; 
                                case "2":
                                    $doKogo = 'Tylko zarejestrowani klienci którzy wyrazili zgodę na newsletter';
                                    break;                          
                                case "3":
                                    $doKogo = 'Tylko klienci którzy zapisali się do newslettera, a nie są klientami sklepu';
                                    break;
                                case "4":
                                    $doKogo = 'Do wszystkich którzy zapisali się do newslettera';
                                    break;                        
                                case "5":
                                    $doKogo = 'Mailing';
                                    break;     
                                case "6":
                                    $doKogo = 'Tylko do określonej grupy klientów';
                                    break; 
                                case "7":
                                    $doKogo = 'Tylko zarejestrowani klienci z porzuconymi koszykami';
                                    break;   
                                case "8":
                                    $doKogo = 'Tylko klienci bez rejestracji z porzuconymi koszykami';
                                    break;   
                                case "9":
                                    $doKogo = 'Wszyscy klienci z porzuconymi koszykami (z kontem oraz bez rejestracji)';
                                    break;                                       
                            }                         
                            
                            ?>
                            <span><?php echo $doKogo; ?></span>
                        </div>

                        <div>
                            Ilość maili do wysłania: <span id="IleDoWyslania"><?php echo $IloscDoWyslania; ?></span>
                        </div>
                        
                        <?php } else { ?>
                        
                        <div>
                            Odbiorcy newslettera: <span>TRYB TESTOWY: Wiadomość zostanie wysłana na adres właściciela sklepu ...</span>
                        </div>                        
                        
                        <?php } ?>
                        
                        <?php
                        if ( !isset($_GET['test']) ) {
                        ?>     

                        <script>
                        $(document).ready(function() {
                          $('#zakres_od').change(function() {
                              //
                              var min = parseInt($(this).attr('data-min'));
                              var max = parseInt($(this).attr('data-max'));
                              //
                              if (isNaN($(this).val())) {
                                  $(this).val(min);
                              } else {
                                  $(this).val(parseInt($(this).val()));
                              }
                              //
                              if ( parseInt($(this).val()) < min ) {
                                   $(this).val(min);
                              }
                              if ( parseInt($(this).val()) > max ) {
                                   $(this).val(max);
                              }
                              //
                              if ( parseInt($('#zakres_od').val()) > parseInt($('#zakres_do').val()) ) {
                                   $(this).val(min);
                              }
                              //
                              $('#ogolny_limit').val( (parseInt($('#zakres_do').val()) - parseInt($('#zakres_od').val())) + 1 );
                              $('#IleDoWyslania').html( $('#ogolny_limit').val() );
                              //
                          })                          
                          $('#zakres_do').change(function() {
                              //
                              var min = parseInt($(this).attr('data-min'));
                              var max = parseInt($(this).attr('data-max'));
                              //
                              if (isNaN($(this).val())) {
                                  $(this).val(max);
                              } else {
                                  $(this).val(parseInt($(this).val()));
                              }
                              //
                              if ( parseInt($(this).val()) < min ) {
                                   $(this).val(min);
                              }
                              if ( parseInt($(this).val()) > max ) {
                                   $(this).val(max);
                              }
                              //
                              if ( parseInt($('#zakres_do').val()) < parseInt($('#zakres_od').val()) ) {
                                   $(this).val(max);
                              }
                              //
                              $('#ogolny_limit').val( (parseInt($('#zakres_do').val()) - parseInt($('#zakres_od').val())) + 1 );
                              $('#IleDoWyslania').html( $('#ogolny_limit').val() );
                              //
                          })
                        });
                        </script>
                        
                        <div class="ZakresWysylaniaNewslettera">
                          Zakres wysyłania maili:
                          <input type="text" data-min="1" data-max="<?php echo $IloscDoWyslania; ?>" name="zakres_od" id="zakres_od" value="1" size="5" />      
                          do: <input type="text" data-min="1" data-max="<?php echo $IloscDoWyslania; ?>" name="zakres_do" id="zakres_do" value="<?php echo $IloscDoWyslania; ?>" size="5" />      
                        </div>    

                        <?php } else { ?>
                        
                          <input type="hidden" name="zakres_od" id="zakres_od" value="1" /> 
                          <input type="hidden" name="zakres_do" id="zakres_do" value="1" />   
                          
                        <?php } ?>
                        
                    </div>
                
                    <div id="import" style="display:none">
                    
                        <div id="postep">Postęp wysyłania ...</div>
                    
                        <div id="suwak">
                            <div style="margin:1px;overflow:hidden">
                                <div id="suwak_aktywny"></div>
                            </div>
                        </div>
                        
                        <div id="procent">Stopień realizacji: <span>0%</span><br />Wysłano maili: <span>0</span></div> 

                        <ul id="blad"></ul>

                        <div class="cl"></div>
                    
                    </div>   
                    
                    <div id="wynik_dzialania" style="display:none">
                        Newsletter został wysłany do klientów ...
                    </div>
                    
                </div>
                
                <div class="przyciski_dolne" id="przyciski">
                  <button type="button" id="p_wyslij" class="przyciskNon" onclick="wyslij_newsletter(<?php echo $info['newsletters_id']; ?>)">Wyślij newsletter</button> 
                  <button type="button" class="przyciskNon" onclick="cofnij('newsletter','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>');">Powrót</button> 
                </div>

            <?php
            } else {
            
                echo '<div class="pozycja_edytowana">Brak danych do wyświetlenia</div>';
            
            }
            
            $db->close_query($sql);
            ?>

          </div>                      
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}