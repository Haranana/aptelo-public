<?php
chdir('../');           

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php'); 

if (Sesje::TokenSpr() && isset($_POST['data']) && $_POST['data'] == 'dokument') {  

    $GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('ZAMOWIENIE_REALIZACJA') ), $GLOBALS['tlumacz'] );

    ?>
    <script>
    $(document).ready(function() {
        //
        $("input:radio[name='dokument_popup']").click(function() {
            //
            var wartoscPola = $(this).val();
            //
            $("input[name='dokument'][value='" + wartoscPola + "']").prop('checked', true);
            
            if ( wartoscPola == 1 ) {
               $('.OknoFaktura').show();
               $('.OknoNipParagon').hide();               
            } else {
               $('.OknoFaktura').hide(); 
               $('.OknoNipParagon').show(); 
            }
            
            $.scrollTo('.DaneFaktura',400);

            //
            PreloadWlacz();
            $.post("inne/zmiana_dokumentu_zakupu.php?tok=<?php echo Sesje::Token(); ?>", { value: wartoscPola }, function(data) { PreloadWylaczSzybko(); $('.jBox-wrapper').remove(); $('.jBox-overlay').remove(); $('body').removeClass('unscrollable'); });            
            //
        });
        //
    });
    </script>
    <?php
        
    echo '<div id="PopUpInfo" class="cmxform PopUpDokumentSprzedazy" aria-live="assertive" aria-atomic="true">';
        
        echo $GLOBALS['tlumacz']['WYBIERZ_DOKUMENT_SPRZEDAZY'] . '<br /><br />';
        
        echo '<label for="wybor_faktura" style="display:inline-block"><input type="radio" name="dokument_popup" id="wybor_faktura" value="1" /><span class="radio" id="radio_wybor_faktura"></span>' . $GLOBALS['tlumacz']['DOKUMENT_SPRZEDAZY_FAKTURA'] . '</label> &nbsp; &nbsp; '; 
        echo '<label for="wybor_paragon" style="display:inline-block"><input type="radio" name="dokument_popup" id="wybor_paragon" value="0" /><span class="radio" id="radio_wybor_paragon"></span>' . $GLOBALS['tlumacz']['DOKUMENT_SPRZEDAZY_PARAGON'] . '</label>';  

    echo '</div>';
    
}    
?>