<?php
// dodatkowy warunek dla tylko zalogowanych
$warunekTmp = " and p.poll_login = '0'";

if ( isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0' ) {
    $warunekTmp = "";
}

$zapytanie = "SELECT * FROM poll p INNER JOIN poll_description pd ON p.id_poll = pd.id_poll AND pd.language_id = '" . (int)$_SESSION['domyslnyJezyk']['id'] . "' WHERE p.poll_status = '1' " . $warunekTmp . " AND p.id_poll = '" . (int)(int)$Konfiguracja['grupa_ankiety'] . "'";
$sql = $GLOBALS['db']->open_query($zapytanie);

if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) { 

        $infa = $sql->fetch_assoc();

        // szukanie pozycji ankiety
        $zapytanie_pozycje = "SELECT id_poll_unique, poll_field FROM poll_field WHERE id_poll = '" . $infa['id_poll'] . "' AND language_id = '" . (int)$_SESSION['domyslnyJezyk']['id'] . "' ORDER BY poll_field_sort";    
        $sqlp = $GLOBALS['db']->open_query($zapytanie_pozycje);
                
        // jezeli jest wiecej niz 1 pytanie
        if ((int)$GLOBALS['db']->ile_rekordow($sqlp) > 1) { 
        
            echo '<div class="AnkietaKreator">';
            
                echo '<div class="ElementOknoRamka">';
                
                    echo '<form action="' . Seo::link_SEO($infa['poll_name'], $infa['id_poll'], 'ankieta') . '" method="post" class="cmxform" id="ankieta-' . $infa['id_poll'] . '">';
                    
                        if ( $Konfiguracja['tytul_ankiety'] == 'tak' ) {
                             //
                             echo '<h4 class="AnkietaTytul">' . $infa['poll_name'] . '</h4>';
                             //
                        }
                        
                        echo '<div class="PytaniaKontener">';
                        
                            echo '<ul class="PytaniaAnkieta">';

                            while ($pozycje = $sqlp->fetch_assoc()) {
                                //
                                echo '<li><label for="' . $pozycje['id_poll_unique'] . '"><input type="radio" name="ankieta" value="' . $pozycje['id_poll_unique'] . '" id="' . $pozycje['id_poll_unique'] . '" /><b>' . $pozycje['poll_field'] . '</b><span class="radio" id="radio_' . $pozycje['id_poll_unique'] . '"></span></label></li>';
                                //
                            }

                            echo '</ul>';
                            
                        echo '</div>';
                        
                        echo '<div class="BladAnkiety" id="BladAnkiety-' . $infa['id_poll'] . '" style="display:none"><span>{__TLUMACZ:BLAD_ZAZNACZ_JEDNA_OPCJE}</span></div>';    
                        
                        echo '<input type="hidden" value="' . $infa['id_poll'] . '" name="id" />';
                        
                        echo '<div class="AnkietaPrzyciski">';
                        
                            echo '<input type="submit" class="przycisk" value="{__TLUMACZ:PRZYCISK_ZAGLOSUJ}" />';
                            
                            echo '<a class="przycisk WynikiAnkieta" href="' . Seo::link_SEO($infa['poll_name'], $infa['id_poll'], 'ankieta') . '">{__TLUMACZ:ZOBACZ_WYNIKI_ANKIETY}</a>';
                            
                        echo '</div>';
                    
                    echo '</form>';

                echo '</div>';

            echo '</div>';
            
            ?>
            
            <script>
            $(document).ready(function() {
                $('.PytaniaKontener input:radio').click(function() {
                    $('#BladAnkiety-<?php echo $infa['id_poll']; ?>').hide();
                });

                $("#ankieta-<?php echo $infa['id_poll']; ?>").validate({
                  rules: {
                    ankieta: { required: true }                   
                  },   
                  errorPlacement: function() {
                    $('#BladAnkiety-<?php echo $infa['id_poll']; ?>').show();
                  },      
                  submitHandler: function() {
                    PreloadWlacz();
                    var sear = $('#ankieta-<?php echo $infa['id_poll']; ?>').serialize(); 
                    $.post("inne/do_ankiety.php?tok=<?php echo (string)Sesje::Token(); ?>", { data: sear }, function(data) { 
                        PreloadWylaczSzybko(); 
                        var myModal = new jBox('Modal',{ 
                            content : '<div class="message">'+data+'</div>',
                            closeButton: false,
                            closeOnEsc: false,
                            closeOnMouseleave: false,
                            closeOnClick: false,
                            onOpen: function () {
                                const popupElement = this.wrapper;
                                setTimeout(function () {
                                    initFocusTrap(popupElement);
                                }, 50);
                            } 
                        });
                        myModal.open();
                    });
                    return false;
                  }       
                });
            });
            </script>           
            
            <?php

        }
        
        $GLOBALS['db']->close_query($sqlp); 
        
        unset($zapytanie_pozycje); 
        
}

$GLOBALS['db']->close_query($sql); 
unset($warunekTmp, $zapytanie);
?>