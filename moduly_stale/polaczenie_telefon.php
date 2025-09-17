<?php
if (isset($_POST['data'])) {
    //
    chdir('../'); 
    
    // wczytanie ustawien inicjujacych system
    require_once('ustawienia/init.php');  

}    

$zapytanie = "select tmfd.modul_settings_code, tmfd.modul_settings_value from theme_modules_fixed tmf, theme_modules_fixed_settings tmfd where tmf.modul_id = tmfd.modul_id and tmf.modul_file = 'polaczenie_telefon.php'";
$sqlPopup = $GLOBALS['db']->open_query($zapytanie);
while ( $info = $sqlPopup->fetch_assoc() ) {
    //
    if ( !defined($info['modul_settings_code']) ) {
         define( $info['modul_settings_code'], $info['modul_settings_value'] );
    }
    //
}    
$GLOBALS['db']->close_query($sqlPopup);
unset($info, $zapytanie);

$GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('MODULY_STALE') ), $GLOBALS['tlumacz'] );
?>

<?php if ( !isset($_POST['data']) ) {

    $KodJs = "<script>
              $(document).ready(function() {
                  //
                  $('#TelefonIkona').click(function() {
                      //
                      $('#TelefonIkona').hide();
                      //
                      PreloadWlacz();
                      $.post(\"moduly_stale/polaczenie_telefon.php?tok=" . Sesje::Token() . "\", { data: 'sluchawka' }, function(data) { 
                          //
                          PreloadWylaczSzybko(); 
                          //
                          if ( $('#TloKontaktSluchawka').length ) {
                               //
                               $('#TloKontaktSluchawka').stop().fadeIn();
                               //
                          } else {
                               //
                               $('body').append(data);
                               $('#TloKontaktSluchawka').stop().fadeIn();
                               //
                          }
                          //
                      });                 
                      //
                  });
                  //
              });
              </script>"; 

    echo jsMin::minify($KodJs);
    
    unset($KodJs);
    
    $SluchawkaCss = '';

    if ( SLUCHAWKA_IKONA_POLOZENIE == 'górny prawy narożnik' ) {
         $SluchawkaCss = ' class="SluchawkaGornyPrawy"';
    }
    if ( SLUCHAWKA_IKONA_POLOZENIE == 'górny lewy narożnik' ) {
         $SluchawkaCss = ' class="SluchawkaGornyLewy"';
    }
    if ( SLUCHAWKA_IKONA_POLOZENIE == 'dolny prawy narożnik' ) {
         $SluchawkaCss = ' class="SluchawkaDolnyPrawy"';
    }
    if ( SLUCHAWKA_IKONA_POLOZENIE == 'dolny lewy narożnik' ) {
         $SluchawkaCss = ' class="SluchawkaDolnyLewy"';
    }    
    ?>

    <div id="TelefonIkona"<?php echo $SluchawkaCss; ?>></div>
    
    <?php unset($SluchawkaCss); ?>    
    
<?php } ?>

<?php 
if ( isset($_POST['data']) && $_POST['data'] == 'wyslij_sluchawka' && isset($_POST['tel']) ) {

     if (Sesje::TokenSpr()) {
       
        $wiadomosc = $GLOBALS['tlumacz']['SLUCHAWKA_WIADOMOSC_SMS_MAIL'] . ' ' . $_POST['tel'];
        
        if ( SLUCHAWKA_IKONA_DO_WYSYLAJ == 'tylko SMS' || SLUCHAWKA_IKONA_DO_WYSYLAJ == 'SMS i email' ) {
          
             if ( SMS_WLACZONE == 'tak' ) {

                  if ( Funkcje::CzyNumerGSM(SLUCHAWKA_IKONA_DO_TELEFON_ODBIORCA) ) {

                       $adresat = SLUCHAWKA_IKONA_DO_TELEFON_ODBIORCA;                      
                       SmsApi::wyslijSms($adresat, $wiadomosc);
                       unset($adresat);
                      
                  }

             }
             
        }
        
        if ( SLUCHAWKA_IKONA_DO_WYSYLAJ == 'tylko email' || SLUCHAWKA_IKONA_DO_WYSYLAJ == 'SMS i email' ) {
        
             if ( Funkcje::CzyPoprawnyMail(SLUCHAWKA_IKONA_DO_MAIL_ODBIORCA) ) {
            
                  $email = new Mailing;
                  $email->wyslijEmail(INFO_EMAIL_SKLEPU,INFO_EMAIL_SKLEPU,SLUCHAWKA_IKONA_DO_MAIL_ODBIORCA, SLUCHAWKA_IKONA_DO_MAIL_ODBIORCA, '', $wiadomosc, $wiadomosc, '1', (int)$_SESSION['domyslnyJezyk']['id'], array());
                  unset($email);

             }
             
        }

     }
    
}
?>

<?php if ( isset($_POST['data'])  && $_POST['data'] == 'sluchawka' && !isset($_POST['tel']) ) { ?>

    <?php if (Sesje::TokenSpr()) { 

        $KodJs = "<script>
                  $(document).ready(function() {
                      //
                      $('.KontaktSluchawkaPrzycisk span').click(function() {
                          //
                          infoSms = setTimeout(function(){ $('#BladDanychSluchawka').hide() }, 2000);
                          //
                          var telefon = $('#sluchawka_telefon').val()
                          var telefonNum = telefon.replace(/[^\d]/g, '');
                          //
                          if ( telefonNum.length > 6 && telefonNum.length < 13 ) {
                               //
                               $('#BladDanychSluchawka').hide();
                               //
                               $('#WyslanieDanychSluchawka').show();
                               $('.KontaktSluchawkaPrzycisk').hide();
                               //
                               $.post(\"moduly_stale/polaczenie_telefon.php?tok=" . Sesje::Token() . "\", { data: 'wyslij_sluchawka', tel: $('#sluchawka_telefon').val() });                   
                               //
                               $('#TelefonIkona').hide();
                               setTimeout(function(){ $('#TloKontaktSluchawka').stop().fadeOut(function() { $('#TloKontaktSluchawka').remove(), 400 }); }, 4000);                 
                               //
                           } else {
                               //
                               clearTimeout(infoSms);
                               //
                               $('#BladDanychSluchawka').show();
                               infoSms = setTimeout(function(){ $('#BladDanychSluchawka').hide() }, 2000);
                               //
                          }
                          //
                      });
                      //
                      $('#KontaktSluchawkaZamknij').click(function() {
                          //
                          $('#TloKontaktSluchawka').stop().fadeOut();
                          $('#TelefonIkona').stop().fadeIn();
                          //
                      });
                      //
                  });
                  </script>";
                  
        echo jsMin::minify($KodJs);
    
        unset($KodJs);    
        ?>
        
        <div id="TloKontaktSluchawka">

            <div class="KontaktSluchawkaOkno">
            
                <div class="KontaktSluchawkaOknoKont cmxform">
                
                    <div id="KontaktSluchawkaZamknij"><span id="span_<?php echo uniqid(); ?>"></span></div>
                
                    <div class="NaglowekKontaktSluchawka">
                    
                        <?php echo $GLOBALS['tlumacz']['SLUCHAWKA_NAGLOWEK']; ?>
                        
                    </div>
                    
                    <div class="NaglowekMniejszyKontaktSluchawka">
                    
                        <?php echo $GLOBALS['tlumacz']['SLUCHAWKA_OPIS']; ?>
                        
                    </div>         

                    <div class="KontaktSluchawkaPole">
                    
                        <input type="text" size="10" id="sluchawka_telefon" placeholder="<?php echo $GLOBALS['tlumacz']['SLUCHAWKA_TEKST_POLA']; ?>" />
                        
                    </div>
                    
                    <div id="BladDanychSluchawka"><span><?php echo $GLOBALS['tlumacz']['SLUCHAWKA_BLAD']; ?></span></div>
                    
                    <div id="WyslanieDanychSluchawka"><?php echo $GLOBALS['tlumacz']['SLUCHAWKA_WYSLANO']; ?></div>
                    
                    <div class="KontaktSluchawkaPrzycisk">
                    
                        <span class="przycisk"><?php echo $GLOBALS['tlumacz']['SLUCHAWKA_PRZYCISK']; ?></span>
                        
                    </div>     

                    <small><?php echo $GLOBALS['tlumacz']['SLUCHAWKA_MALY_OPIS']; ?></small>
                      
                </div>
                
            </div>
            
        </div>  
        
    <?php } ?>
    
<?php } ?>