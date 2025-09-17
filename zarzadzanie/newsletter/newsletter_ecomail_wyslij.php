<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone && isset($_POST['akcja']) && $_POST['akcja'] == 'wyslij' && isset($_POST['lista']) && trim((string)$_POST['lista']) != '' && INTEGRACJA_ECOMAIL_WLACZONY == 'tak' ) {

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Wysyłanie danych do systemu Ecomail</div>
    <div id="cont">
          
          <form action="newsletter/newsletter_ecomail_wyslij.php" method="post" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Wysyłanie danych</div>
            
            <?php
            // domyslnie wszyscy klienci
            $zapytanie = "select subscribers_email_address from subscribers";
            $Tryb = 'wszyscy';
            //
            if ( isset($_POST['tryb']) && $_POST['tryb'] == 'zapisani' ) {
                $zapytanie = "select subscribers_email_address from subscribers where customers_newsletter = '1'";
                $Tryb = 'zapisani';
            }
            
            $sql = $db->open_query($zapytanie);
            $IloscPozycji = (int)$db->ile_rekordow($sql);
            
            // jezeli tylko maila z newslettera
            if ( substr((string)$_POST['tryb'], 0, 5) == 'id___' ) {
                //
                $IloscPozycji = count(Newsletter::AdresyEmailNewslettera( (int)str_replace('id___', '', (string)$_POST['tryb']) ));
                $Tryb = $_POST['tryb'];
                //
            }
            
            if ( $IloscPozycji > 0) {
                ?>            
                
                <script>
                var ogolny_limit = <?php echo $IloscPozycji; ?>;
                //
                function wyslij_ecomail(tryb, limit, suma) {

                    if ($('#import').css('display') == 'none') {
                        $('#import').slideDown("fast");
                        $('#przyciski').slideUp("fast");
                    }
                    
                    $.post( "ajax/wyslij_ecomail.php?tok=<?php echo Sesje::Token(); ?>", 
                          { 
                            tryb: '<?php echo $Tryb; ?>',
                            lista: '<?php echo $filtr->process($_POST['lista']);?>',
                            limit: limit,
                            suma: suma
                          },
                          function(data) {

                             procent = parseInt(((limit + 1) / ogolny_limit) * 100);
                             if ( procent > 100 ) {
                                  procent = 100;
                             }

                             $('#procent').html('Stopień realizacji: <span>' + procent + '%</span><br />Przesłano do systemu Ecomail maili: <span>' + data + '</span>');
                             
                             $('#suwak_aktywny').css('width' , (procent * 5) + 5 + 'px');
                             
                             if (ogolny_limit-1 > limit) {
                               
                                 wyslij_ecomail('<?php echo $Tryb; ?>', limit + 20, data);
                                  
                               } else {

                                 $('#postep').css('display','none');
                                 $('#suwak').slideUp("fast");
                                 $('#wynik_dzialania').slideDown("fast");
                                 $('#przyciski').slideDown("fast");

                             }                                

                          }                          
                    );
                    
                }; 
                </script>                
            
                <div class="pozycja_edytowana">

                    <div id="import" style="display:none">
                    
                        <div id="postep">Postęp wysyłania ...</div>
                    
                        <div id="suwak">
                            <div style="margin:1px;overflow:hidden">
                                <div id="suwak_aktywny"></div>
                            </div>
                        </div>
                        
                        <div id="procent">Stopień realizacji: <span>0%</span><br />Przesłano maili: <span>0</span></div> 

                        <div class="cl"></div>

                    </div>   
                    
                    <div id="wynik_dzialania" style="display:none">
                        Dane zostały przesłane do Ecomail ...
                    </div>
                    
                    <div class="przyciski_dolne" id="przyciski">
                      <button type="button" class="przyciskNon" onclick="cofnij('<?php echo ((substr((string)$_POST['tryb'], 0, 5) == 'id___') ? 'newsletter' : 'newsletter_subskrybenci'); ?>','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','newsletter');">Powrót</button> 
                    </div>                    
                    
                </div>
                
                <script>
                wyslij_ecomail('<?php echo $Tryb; ?>',0,0)
                </script>
                
            <?php
            } else {
            
                echo '<div class="pozycja_edytowana">Brak danych do wyświetlenia</div>';
            
            }
            
            $db->close_query($sql);
            unset($IloscPozycji);
            ?>

          </div>                      
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}
?>