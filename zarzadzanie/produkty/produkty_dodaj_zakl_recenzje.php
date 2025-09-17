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
    
    <div class="info_tab_content">
    
        <?php
        for ($w = 0; $w < $jezyk_szt; $w++) {  
            ?>   
            
            <div id="info_tab_id_<?php echo $w + $liczba; ?>" style="display:none;">

                <?php
                if ($id_produktu > 0) {   
                
                    // pobieranie danych jezykowych
                    $zapytanie_tmp = "select distinct * from reviews r, reviews_description rd where r.reviews_id = rd.reviews_id and r.products_id = '".$id_produktu."' and rd.languages_id = '" .$ile_jezykow[$w]['id']."'";
                    $sqls = $db->open_query($zapytanie_tmp);
                    //
                    if ((int)$db->ile_rekordow($sqls) > 0) {
                        //
                        if ( $w == 0 && INTEGRACJA_OPENAI_WLACZONY == 'tak' ) { ?>
                        
                        <div class="OknoAIRecenzje" style="margin-bottom:20px">
                        
                            <div class="RozwinAI RecenzjeAI">Podsumowanie recenzji przez AI</div>

                        </div>
                        
                        <script>
                        $(document).ready(function() {
                          
                           $('.RecenzjeAI').click(function() {
                               //
                               $('#ekr_preloader').css('display','block');
                               //
                               $.get('ajax/skryptai_recenzje.php', { tok: $('#tok').val(), id: <?php echo $id_produktu; ?>, nr_edytora: '<?php echo $w + $liczba; ?>' }, function(data) {
                                   //
                                   $('#ekr_preloader').css('display','none');
                                   $(".OknoAIRecenzje").html(data);
                                   $(".OknoAIRecenzje").hide().stop().slideDown();                           
                                   //
                               });
                               //
                           });     
                           
                        });                  
                        </script>
                        
                        <?php 
                        }
                        
                        if ( $w == 0 ) { 
                             //
                             // pobieranie danych jezykowych
                             $zapytanie_tmpr = "select distinct products_review_summary from products_description where products_id = '" . $id_produktu . "' and language_id = '" . $ile_jezykow[$w]['id'] . "'";
                             $sqlr = $db->open_query($zapytanie_tmpr);
                             $infg = $sqlr->fetch_assoc();
                             //                             
                             echo '<div style="margin:10px"><div style="font-weight:bold;padding-bottom:10px;color:#1e749d">Podsumowanie recenzji produktu</div><textarea style="width:calc(100% - 22px)" rows="8" cols="50" id="podsumowanie_recenzji" name="podsumowanie_recenzji">' . ((isset($infg['products_review_summary'])) ? $infg['products_review_summary'] : '') . '</textarea></div>';
                             //
                             $db->close_query($sqlr); 
                             unset($zapytanie_tmpr, $infg);   
                             //
                        }
                        //
                        $l = 1;
                        //
                        while ($infr = $sqls->fetch_assoc()) {
                        //
                        ?>
                        
                        <div class="NaglowekRecenzja">Recenzja nr <span><?php echo $l; ?></span></div>
                        
                        <div class="RecenzjaProduktu">
                
                            <div class="DataDodania">Data dodania: <b><?php echo date('d-m-Y H:i',FunkcjeWlasnePHP::my_strtotime($infr['date_added'])); ?></b></div>
                    
                            <div class="Wystawil">Wystawił: <b><?php echo $infr['customers_name']; ?></b></div>
                    
                            <div class="Ocena">Ocena: <b><img src="obrazki/recenzje/star_<?php echo $infr['reviews_rating']; ?>.png" alt="Ocena <?php echo $infr['reviews_rating']; ?>/5" /></b></div>
                    
                            <div class="Komentarz"><?php echo $infr['reviews_text']; ?></div>
                            
                            <?php
                            // komentarz do opinii
                            if ( !empty($infr['comments_answers']) ) {
                                  echo '<div class="RecenzjaOdpowiedz">Odpowiedź: <br /> ' . $infr['comments_answers'] . '<a href="recenzje/recenzje_usun.php?id_poz='.$infr['reviews_id'] .'&odpowiedz=tak&zakladka=27&produkt='.$id_produktu.'" class="OdpowiedzSkasuj TipChmurka"><b>Skasuj odpowiedź</b><img src="obrazki/kasuj.png" alt="Skasuj" /></a></div>';
                            }
                            ?>
                            
                            <div class="PrzyciskiRecenzje">
                            
                                <?php
                                echo '<div class="NapiszOdpowiedz"><a href="recenzje/recenzje_odpowiedz.php?id_poz='.$infr['reviews_id'].'&odpowiedz=tak&zakladka=27&produkt='.$id_produktu.'">odpowiedz na recenzję</a></div>';
                                
                                echo '<div class="UsunRecenzje"><a href="recenzje/recenzje_usun.php?id_poz='.$infr['reviews_id'].'&zakladka=27&produkt='.$id_produktu.'">usuń recenzję</a></div>';
                                
                                echo '<div class="EdytujRecenzje"><a href="recenzje/recenzje_edytuj.php?id_poz='.$infr['reviews_id'].'&zakladka=27&produkt='.$id_produktu.'">edytuj recenzję</a></div>';
                                ?>
                                
                            </div>
                            
                            <?php
                            // potwierdzona zakupem                            
                            if ( (int)$infr['reviews_confirm'] == 1 ) {
                                  echo '<div style="margin-top:10px"><a class="RecenzjaZakup" href="recenzje/recenzje_zakup.php?id_poz='.$infr['reviews_id'].'&zakladka=27&produkt='.$id_produktu.'">recenzja potwierdzona zakupem</a></div>';
                            } else {
                                  echo '<div style="margin-top:10px"><a class="RecenzjaBezZakupu" href="recenzje/recenzje_zakup.php?id_poz='.$infr['reviews_id'].'&zakladka=27&produkt='.$id_produktu.'">recenzja niepotwierdzona zakupem</a></div>';
                            }
                            ?>
                        
                        </div>
                        
                        <?php
                        $l++;
                        }
                        
                    } else {
                      
                        echo '<div class="maleInfo">Brak recenzji ...</div>';
                        
                    }
                    
                } else {
                  
                    echo '<div class="maleInfo">Brak recenzji ...</div>';
                  
                }

                if ($id_produktu > 0) {  
                    $db->close_query($sqls); 
                    unset($zapytanie_tmp);
                }
                ?>
                
            </div>
            <?php 
        }                    
        ?>                      
    </div>
    
    <script>
    gold_tabs('<?php echo (int)$_GET['id_tab']; ?>', '');
    </script>      

<?php } ?>