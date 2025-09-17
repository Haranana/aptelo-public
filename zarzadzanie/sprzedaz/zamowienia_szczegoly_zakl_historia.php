<?php
if ( isset($toks) ) {
?>

    <div id="zakl_id_3" style="display:none;" class="pozycja_edytowana">

        <div class="ObramowanieTabeli">
        
          <table class="listing_tbl" id="InfoTabelaHistoria">
          
            <tr class="div_naglowek">
              <td>Data dodania</td>
              <td>Mail do klienta</td>
              <?php if ( SMS_WLACZONE == 'tak' && SMS_ZMIANA_STATUSU_ZAMOWIENIA == 'tak' ) { ?><td style="text-align:center">SMS do klienta</td><?php } ?>
              <td>Status</td>
              <td style="width:50%">Komentarze</td>
              <?php if ( $_SESSION['grupaID'] == '1' ) { ?>
              <td>Akcja</td>
              <?php } ?>
              <?php if ( ZAMOWIENIE_ADMIN_STATUS == 'tak' ) { ?>
              <td>Zmiana</td>
              <?php } ?>
            </tr>
            
            <?php 
            $tablica_admin = array();
            
            $zapytanie_tmp = "select distinct * from admin";
            $sqls = $db->open_query($zapytanie_tmp);
            //
            if ((int)$db->ile_rekordow($sqls) > 0) {
                //
                while ($infs = $sqls->fetch_assoc()) {
                      $tablica_admin[ $infs['admin_id'] ] = $infs['admin_firstname'] . ' ' . $infs['admin_lastname'];
                }
                //
            }
            unset($zapytanie_tmp, $infs);  
            $db->close_query($sqls);
            //             
            
            if ( isset($zamowienie->statusy) && count($zamowienie->statusy) > 0 ) {
            
              foreach ( $zamowienie->statusy as $status ) {
                ?>
                <tr class="pozycja_off">
                
                  <td style="white-space:nowrap;"><?php echo date('d-m-Y H:i', FunkcjeWlasnePHP::my_strtotime($zamowienie->statusy[$status['zamowienie_status_id']]['data_dodania'])); ?></td>
                  <td><img src="obrazki/<?php echo ( $zamowienie->statusy[$status['zamowienie_status_id']]['powiadomienie_mail'] == '1' ? 'tak.png' : 'tak_off.png' ); ?>" alt="" /></td>
                  <?php if ( SMS_WLACZONE == 'tak' && SMS_ZMIANA_STATUSU_ZAMOWIENIA == 'tak' ) { ?><td style="text-align:center"><img src="obrazki/<?php echo ( $zamowienie->statusy[$status['zamowienie_status_id']]['powiadomienie_sms'] == '1' ? 'tak.png' : 'tak_off.png' ); ?>" alt="" /></td><?php } ?>
                  <td><?php echo Sprzedaz::pokazNazweStatusuZamowienia($zamowienie->statusy[$status['zamowienie_status_id']]['status_id'], $zamowienie->klient['jezyk']); ?></td>
                  <td style="text-align:left">
                      <div style="overflow:auto; max-width:400px">
                           <?php echo $zamowienie->statusy[$status['zamowienie_status_id']]['komentarz']; ?>
                      </div>
                  </td>
                  <?php if ( $_SESSION['grupaID'] == '1' ) { ?>
                  <td>
                  <?php
                  if ( isset($zamowienie->statusy) && count($zamowienie->statusy) > 1 ) {
                    echo '<a class="TipChmurka" href="sprzedaz/zamowienia_historia_usun.php?id_poz='.$_GET['id_poz'].'&amp;status_id='.$status['zamowienie_status_id'].'&amp;zakladka=3"><b>Skasuj</b><img src="obrazki/kasuj.png" alt="Skasuj" /></a>';
                  } else {
                    echo '<em class="TipChmurka"><b>Opcja niedostępna</b><img src="obrazki/kasuj_off.png" alt="Opcja niedostępna" /></em>';
                  }
                  ?>
                  </td>
                  <?php } ?>
                  
                  <?php if ( ZAMOWIENIE_ADMIN_STATUS == 'tak' ) { ?>
                  <td>
                  <?php
                  if ( (int)$zamowienie->statusy[$status['zamowienie_status_id']]['admin_id'] > 0 ) {
                        if ( isset($tablica_admin[$zamowienie->statusy[$status['zamowienie_status_id']]['admin_id']]) ) {
                             echo $tablica_admin[$zamowienie->statusy[$status['zamowienie_status_id']]['admin_id']];
                        }
                  }
                  ?>
                  </td>
                  <?php } ?>
                  
                </tr>
                <?php
              }
            } 
            ?>
            
          </table>
          
        </div>

        <div class="pozycja_edytowana" style="margin-top:20px;">
        
            <div class="info_content">
                
              <?php
              // paypo
              if ( $zamowienie->info['platnosc_klasa'] == 'platnosc_paypo' ) {
                   //
                   $zapytaniePayPo = "select paypo_order_status, paypo_order_value, paypo_order_new_value from orders where orders_id = '" . (int)$zamowienie->info['id_zamowienia'] . "'";
                   $sqlPayPo = $db->open_query($zapytaniePayPo);       
                   //
                   $infoPayPo = $sqlPayPo->fetch_assoc();  
                   //
                   if ( $infoPayPo['paypo_order_status'] != '' ) {
                     
                       echo '<div class="StatusPayPo">';
                       //
                       echo '<p><a class="przyciskNon"href="sprzedaz/zamowienia_status_paypo.php?id_poz='.(int)$zamowienie->info['id_zamowienia'].'">Sprawdź status zamówienia w systemie PayPo</a></p>';                
                       //
                       if ( $infoPayPo['paypo_order_status'] != 'NEW' ) {
                            //
                            if ( ($infoPayPo['paypo_order_status'] == 'ACCEPTED' || $infoPayPo['paypo_order_status'] != 'CANCELED') && ( $infoPayPo['paypo_order_new_value'] > 0 ) ) { 
                                 //
                                 echo '<div class="ZmianStatusPayPo">Zmiana statusu zamówienia PayPo</div>';
                                 //
                            }
                            //
                            echo '<p>';
                            
                            if ( $infoPayPo['paypo_order_status'] == 'ACCEPTED' && $infoPayPo['paypo_order_new_value'] > 0 ) {
                                 //
                                 echo '<a class="przyciskNon" href="sprzedaz/zamowienia_zmiana_statusu_paypo.php?id_poz='.(int)$zamowienie->info['id_zamowienia'].'&tryb=wyslane">Potwierdzenie wysłania zamówienia</a><em class="TipIkona"><b>Zmiana statusu na COMPLETED</b></em>';
                            }
                            if ( $infoPayPo['paypo_order_status'] == 'PENDING' || $infoPayPo['paypo_order_status'] == 'ACCEPTED') {
                                 echo '<a class="przyciskNon" href="sprzedaz/zamowienia_zmiana_statusu_paypo.php?id_poz='.(int)$zamowienie->info['id_zamowienia'].'&tryb=anulowane">Anulowanie zamówienia</a><em class="TipIkona"><b>Zmiana statusu na CANCELED</b></em>';
                                 //
                            }
                            
                            if ( $infoPayPo['paypo_order_status'] != 'CANCELED' && ( $infoPayPo['paypo_order_status'] == 'ACCEPTED' || $infoPayPo['paypo_order_status'] == 'COMPLETED' ) ) {
                                 //
                                 if ( $infoPayPo['paypo_order_value'] == $infoPayPo['paypo_order_new_value'] ) {
                                    echo '<a class="przyciskNon" href="sprzedaz/zamowienia_zmiana_statusu_paypo.php?id_poz='.(int)$zamowienie->info['id_zamowienia'].'&tryb=zwrot_caly">Zwrot całego zamówienia</a><em class="TipIkona"><b>Przesłanie całej wartości zamówienia do zwrotu</b></em>';
                                 }
                                 if ( $infoPayPo['paypo_order_new_value'] > 0 ) {
                                        echo '<a class="przyciskNon" href="sprzedaz/zamowienia_zmiana_statusu_paypo.php?id_poz='.(int)$zamowienie->info['id_zamowienia'].'&tryb=zwrot">Zwrot częściowy zamówienia</a><em class="TipIkona"><b>Przesłanie różnicy początkowej i aktualnej wartości zamówienia do zwrotu</b></em>';
                                 }
                                 // 
                            }
                            
                            echo '</p>';                
                            //
                       }
                       //
                       echo '</div><div class="PayPoLinia"></div>';
                       //

                       if ( isset($_SESSION['info_paypo']) ) {
                            //
                            ?>
                            <script>
                            $(document).ready(function() {
                                $.colorbox( { html:"<?php echo $_SESSION['info_paypo']; ?>", maxWidth:"90%", maxHeight:"90%", open:true, initialWidth:50, initialHeight:50, speed: 200, overlayClose:false, escKey:false, onLoad: function() {
                                    $("#cboxClose").show();
                                }});
                            });
                            </script>                             
                            <?php
                            //
                            unset($_SESSION['info_paypo']);
                            //
                       }
                       //
                       
                   }
                   
                   $db->close_query($sqlPayPo);
                   unset($zapytaniePayPo, $infoPayPo);                     
                   //                     
              }
              ?>
                
              <?php
              if ( ZAPLACONE_LISTING == 'tak' ) {
                  echo '<div class="StatusPlatnosci"><span>Status płatności:</span>';
                     if ($zamowienie->info['czy_zaplacone'] == '1') { $tgh = '<span><a href="sprzedaz/zamowienia_zaplacone.php?id_poz='.(int)$zamowienie->info['id_zamowienia'].'&zakladka=3" class="Zaplacone">zapłacone</a>'; } else { $tgh = '<a href="sprzedaz/zamowienia_zaplacone.php?id_poz='.(int)$zamowienie->info['id_zamowienia'].'&zakladka=3" class="Niezaplacone">niezapłacone</a>'; }   
                     echo $tgh;
                  echo '</div>';
              }
              ?>
              
              <form action="sprzedaz/zamowienia_szczegoly.php" method="post" id="zamowieniaUwagiForm" class="cmxform" enctype="multipart/form-data">

                <div>
                    <input type="hidden" name="akcja" value="zapisz" />
                    <input type="hidden" name="id" value="<?php echo (int)$_GET['id_poz']; ?>" />
                    <input type="hidden" name="nazwa_klienta" value="<?php echo $zamowienie->klient['nazwa']; ?>" />
                    <input type="hidden" name="telefon_klienta" value="<?php echo $zamowienie->klient['telefon']; ?>" />
                    <input type="hidden" name="zakladka" value="3" />
                </div>

                <p id="wersja">
                  <label>W jakim języku wysłać email:</label>
                  <?php
                  echo Funkcje::RadioListaJezykow('onclick="UkryjZapiszKomentarz(0)"');
                  ?>
                </p>
                
                <script>
                function UkryjZapiszKomentarz(id) {
                    if (parseInt(id) > 0) {
                        $('#przyciski').slideDown('fast');     
                    } else {
                        $('#przyciski').slideUp('fast');
                        $("#komentarz_tresc").val('');
                    }   
                    //
                    $('#LadujKomentarz').fadeIn('fast');
                    $.post('sprzedaz/standardowe_komentarze.php', { jezyk: 1, id: id, nazwy: 'tak', id_zamowienia: '<?php echo $zamowienie->info['id_zamowienia']; ?>' }, function(data){
                      $("#komentarz").html(data);
                      $('#LadujKomentarz').fadeOut('fast');
                      $("#komentarz_tresc").val('');
                    });                   
                }   
                function ZmienKomentarz(id) {
                    var jezyk = $("input[name='jezyk']:checked").val();
                    $('#LadujKomentarz').fadeIn('fast');
                    $.post('sprzedaz/standardowe_komentarze.php', { jezyk: jezyk, id: id, nazwy: 'nie', id_zamowienia: '<?php echo $zamowienie->info['id_zamowienia']; ?>' }, function(data){
                      $("#komentarz_tresc").val(data);
                      $('#LadujKomentarz').fadeOut('fast');
                    });                 
                }
                
                $(document).ready(function() {
                
                    $("input[name=jezyk]").change(function(){
                      $("#status option:first").prop("selected",true); 
                      $('#komentarz').html('<option selected="selected" value="0">--- najpierw wybierz status zamówienia ---</option>');
                      $("#komentarz_tresc").val('');
                    });

                    $('#upload').MultiFile({
                      max: <?php echo EMAIL_ILOSC_ZALACZNIKOW; ?>,
                      accept:'<?php echo EMAIL_DOZWOLONE_ZALACZNIKI; ?>',
                      STRING: {
                       denied:'Nie można przesłać pliku w tym formacie $ext!',
                       duplicate:'Taki plik jest już dodany:\n$file!',
                       selected:'Wybrany plik: $file'
                      }
                    });

                    $(document).on('change','#status',function(){
                        var elemSelect = $(this);
                        elemSelect.css('color', '#' + $(this).find('option:selected').attr('data-kolor'));
                    });

                
                });
                </script>
                
                <p>
                  <label for="status">Nowy status zamówienia:</label>
                  <?php
                  $tablica = Sprzedaz::ListaStatusowZamowien(true, '--- wybierz z listy ---');
                  echo Funkcje::RozwijaneMenu('status', $tablica,'','id="status" onchange="UkryjZapiszKomentarz(this.value)" style="width:350px;"'); ?>
                </p>
                <p>
                  <label for="komentarz">Standardowy komentarz:</label>
                  <?php
                  $tablica = array();
                  $tablica[] = array('id' => '0', 'text' => '--- najpierw wybierz status zamówienia ---');
                  echo Funkcje::RozwijaneMenu('status_komentarz', $tablica,'','id="komentarz" onchange="ZmienKomentarz(this.value)" style="width:350px;"'); ?>                  
                </p>
                
                <div id="LadujKomentarz"><img src="obrazki/_loader_small.gif" alt="" /></div>

                <p>
                  <label for="info_mail">Poinformuj klienta e-mail:</label>
                  <input type="checkbox" <?php echo ((($zamowienie->info['zrodlo'] == '3' && ZAMOWIENIA_ALLEGRO_STATUSY == 'tak') || $zamowienie->info['zrodlo'] != '3') ? 'checked="checked"' : ''); ?> value="1" name="info_mail" id="info_mail" />
                  <label class="OpisForPustyLabel" for="info_mail"></label>
                  <em class="TipIkona"><b>Informacja o zmianie statusu zostanie przeslana do klienta</b></em>
                </p>

                <?php if ( SMS_WLACZONE == 'tak' && SMS_ZMIANA_STATUSU_ZAMOWIENIA == 'tak' ) { ?>
                <p>
                  <label for="info_sms">Poinformuj klienta SMS:</label>
                  <?php if ( Klienci::CzyNumerGSM($zamowienie->klient['telefon']) ) { ?>
                    <input type="checkbox" value="1" name="info_sms" id="info_sms" />
                  <?php } else { ?>
                    <input type="checkbox" value="1" name="info_sms" id="info_sms" disabled="disabled" />
                  <?php } ?>
                  <label class="OpisForPustyLabel" for="info_sms"></label>
                  <em class="TipIkona"><b>Wysłanie powiadomienia SMS do klienta o zmianie statusu</b></em>                  
                </p>
                <?php } ?>

                <p>
                  <label for="dolacz_komentarz">Dołącz komentarz do maila:</label>
                  <input type="checkbox" <?php echo ((($zamowienie->info['zrodlo'] == '3' && ZAMOWIENIA_ALLEGRO_STATUSY == 'tak') || $zamowienie->info['zrodlo'] != '3') ? 'checked="checked"' : ''); ?> value="1" name="dolacz_komentarz" id="dolacz_komentarz" />
                  <label class="OpisForPustyLabel" for="dolacz_komentarz"></label>
                  <em class="TipIkona"><b>Informacja komentarza zostanie dołączona do maila z powiadomieniem do klienta</b></em>                   
                </p>
                
                <p>
                  <label for="rodzaj_tresci_mail">Rodzaj maila do wysłania wiadomości:</label>
                  <?php
                  $tablica = array(array('id' => 0, 'text' => 'Standardowy mail o zmianie statusu zamówienia'),
                                   array('id' => 1, 'text' => 'Mail o indywidualnej treści'));
                  echo Funkcje::RozwijaneMenu('rodzaj_tresci_mail', $tablica, 0, 'id="rodzaj_tresci_mail"');
                  unset($tablica);
                  ?>
                </p>
                
                <script>
                $(document).ready(function() {
                
                    $("#rodzaj_tresci_mail").change(function(){
                      if ( $(this).val() == 0 ) { 
                           $('#IndywidualnyEmail').stop().slideUp();
                      } else {
                           $('#IndywidualnyEmail').stop().slideDown();
                      }
                    });
                    
                    $("#zamowieniaUwagiForm").validate({
                      rules: {
                        temat: {required: function() {var wynik = true; if ( $("#rodzaj_tresci_mail", "#zamowieniaUwagiForm").val() == 0 ) { wynik = false; } return wynik; }},
                        nadawca_email: {email: true, required: function() {var wynik = true; if ( $("#rodzaj_tresci_mail", "#zamowieniaUwagiForm").val() == 0 ) { wynik = false; } return wynik; }},
                        nadawca_nazwa: {required: function() {var wynik = true; if ( $("#rodzaj_tresci_mail", "#zamowieniaUwagiForm").val() == 0 ) { wynik = false; } return wynik; }},
                        komentarz: {required: function() {var wynik = true; if ( $("#rodzaj_tresci_mail", "#zamowieniaUwagiForm").val() == 0 ) { wynik = false; } return wynik; }}
                      }
                    });                    

                });
                </script>                
                
                <div id="IndywidualnyEmail" style="display:none">
                
                    <p>
                        <label for="szablon">Szablon emaila:</label>
                        <?php
                        $tablica = Funkcje::ListaSzablonowEmail(false);
                        echo Funkcje::RozwijaneMenu('szablon', $tablica, '', '', '', '', 'szablon' ); 
                        unset($tablica);
                        ?>
                    </p>
                    
                    <p>
                      <label class="required" for="temat">Temat:</label>
                      <input type="text" name="temat" id="temat" size="83" value="" />
                    </p>  

                    <p>
                      <label class="required" for="nadawca_email">Nadawca email:</label>
                      <input type="text" name="nadawca_email" id="nadawca_email" size="83" value="<?php echo INFO_EMAIL_SKLEPU; ?>" />
                    </p>     

                    <p>
                      <label class="required" for="nadawca_nazwa">Nadawca nazwa:</label>
                      <input type="text" name="nadawca_nazwa" id="nadawca_nazwa" size="83" value="<?php echo INFO_NAZWA_SKLEPU; ?>" />
                    </p>                         

                </div>
                
                <div class="RamkaPunkty">

                    <?php
                    if ( SYSTEM_PUNKTOW_STATUS == 'tak' ) {
                    
                        $zapytaniePkt = "select unique_id, points from customers_points where customers_id = '" . $zamowienie->klient['id'] . "' and orders_id = '" . $zamowienie->info['id_zamowienia'] . "' and points > 0 and points_status != '2' and points_status != '4' and points_type = 'SP'";
                        $sqlp = $db->open_query($zapytaniePkt);       
                        
                        if ((int)$db->ile_rekordow($sqlp) > 0) {
                        
                            $info = $sqlp->fetch_assoc();
                            ?>
                            
                            <div>

                            <p class="Punkty">
                                <label for="zmiana_punktow">Zmień status punktów:</label>
                                <input type="checkbox" value="1" name="punkty" id="zmiana_punktow" />
                                <label class="OpisForPustyLabel" for="zmiana_punktow"></label>
                                <em class="TipIkona"><b>Zostanie zmieniony statusów punktów które zostały naliczone klientowi przy złożeniu zamówienia</b></em>                                     
                            </p>     
                            
                            <p>
                                <label for="status_punktow">Nowy status punktów:</label>
                                <?php        
                                echo Funkcje::RozwijaneMenu('status_punktow', Klienci::ListaStatusowPunktow(false), 2, 'id="status_punktow"');
                                ?>                        
                            </p>      

                            <p>
                                <label for="punkty_dodaj">Zmiana punktów klienta:</label>
                                <input type="radio" value="1" name="tryb" id="punkty_dodaj" checked="checked" /><label class="OpisFor" for="punkty_dodaj">dodaj<em class="TipIkona"><b>Ogólna ilość punktów klienta zostanie zmieniona</b></em></label>    
                                <input type="radio" value="2" name="tryb" id="punkty_zostaw" /><label class="OpisFor" for="punkty_zostaw">nie dodawaj<em class="TipIkona"><b>Ogólna ilość punktów klienta pozostanie bez zmian</b></em></label>  
                            </p>

                            <p>
                                <label for="ilosc_punktow">Ilość punktów do zatwierdzenia:</label>
                                <input type="text" name="ilosc_punktow" id="ilosc_punktow" value="<?php echo $info['points']; ?>" size="5" />
                                <input type="hidden" name="pkt_id" value="<?php echo $info['unique_id']; ?>" />
                            </p>

                            </div>

                        <?php
                        }
                        
                        $db->close_query($sqlp);
                        unset($zapytaniePkt, $info);
                        
                    }

                    // program partnerski
                    if ( SYSTEM_PUNKTOW_STATUS == 'tak' && PP_STATUS == 'tak' ) {
                    
                        $zapytaniePkt = "select unique_id, customers_id, points from customers_points where orders_id = '" . $zamowienie->info['id_zamowienia'] . "' and points > 0 and points_status != '2' and points_status != '4' and points_type = 'PP'";
                        $sqlp = $db->open_query($zapytaniePkt);       
                        
                        if ((int)$db->ile_rekordow($sqlp) > 0) {
                        
                            $info = $sqlp->fetch_assoc();
                            ?>
                            
                            <div>

                            <p class="punkty">
                                <label for="punkty_pp">Dodaj i zmień status punktów z Programu Partnerskiego <br /><a target="_blank" href="klienci/klienci_edytuj.php?id_poz=<?php echo $info['customers_id']; ?>">[szczegóły klienta]</a>:</label>
                                <input type="checkbox" value="1" name="punkty_pp" id="punkty_pp" />
                                <label class="OpisForPustyLabel" for="punkty_pp"></label>
                                <em class="TipIkona"><b>Zostanie zmieniony statusów punktów które zostały naliczone z Programu Partnerskiego</b></em>                               
                                <input type="hidden" name="klient_pp" value="<?php echo $info['customers_id']; ?>" />
                            </p>  

                            <p>
                                <label for="status_punktow_pp">Nowy status punktów:</label>
                                <?php        
                                echo Funkcje::RozwijaneMenu('status_punktow_pp', Klienci::ListaStatusowPunktow(false), 2, 'id="status_punktow_pp"');
                                ?>                        
                            </p>  

                            <p>
                                <label for="ilosc_punktow_pp">Ilość punktów do zatwierdzenia:</label>
                                <input type="text" name="ilosc_punktow_pp" id="ilosc_punktow_pp" value="<?php echo $info['points']; ?>" size="5" />
                                <input type="hidden" name="pkt_id_pp" value="<?php echo $info['unique_id']; ?>" />
                            </p>
                            
                            </div>

                        <?php
                        }
                        
                        $db->close_query($sqlp);
                        unset($zapytaniePkt, $info);
                        
                    }
                    ?>  

                </div>
                <?php 
                if ( $zamowienie->dostawy_link_sledzenia != '' ) {
                    echo '<p style="padding:15px 0px 10px 10px">';
                    echo '<label>Śledzenie przesyłki:</label>';
                    if ( INTEGRACJA_BLISKAPACZKA_WLACZONY == 'tak' && $dostawa['rodzaj_przesylki'] == 'BLISKAPACZKA' && $LinkSledzenia != '' ) {
                        echo '<a href="' . $LinkSledzenia . '" target="_blank">' . $dostawa['inne_informacje'] . '</a>';
                    } else {
                        echo $zamowienie->dostawy_link_sledzenia;
                    }
                    echo '</p>';
                }

                ?>

                <p style="padding:15px 0px 0px 10px" class="ZalacznikHistoria">
                  <label for="upload">Załączniki:</label>
                  <input type="file" name="file[]" id="upload" size="23" />
                </p>
                
                <div class="maleInfo odlegloscRwdEdytor">Dozwolne formaty plików: <?php echo implode(', ', explode('|', (string)EMAIL_DOZWOLONE_ZALACZNIKI)); ?></div> 
                
                <p>
                  <label for="komentarz_tresc" style="width:auto">Komentarz / treść maila (w przypadku wyboru maila z indywidualną treścią)</label>
                  <textarea cols="100" rows="10" name="komentarz" class="wysiwyg" id="komentarz_tresc"></textarea>
                  <label for="komentarz_tresc" generated="true" style="margin:5px 0px !important; display:none" class="error"></label>
                </p>

                <div class="przyciski_dolne" id="przyciski" style="display:none">
                  <input type="submit" class="przyciskNon" value="Zapisz dane" />
                  <input type="hidden" name="powrot" id="powrot" value="0" />
                  <input type="submit" class="przyciskNon" value="Zapisz dane i wróć do listy zamówień" onclick="$('#powrot').val(1)" />
                </div>

              </form>

            </div>
         
        </div>
        
    </div>
    
<?php
}
?>    