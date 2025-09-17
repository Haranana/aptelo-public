<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    $wynik  = '';
    $system = ( isset($_POST['system']) ? $_POST['system'] : '' );

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

      reset($_POST);
      foreach ( $_POST as $key => $value ) {
        if ( $key != 'akcja' && $key != 'id_kat' ) {
          $pola = array(
                  array('value',$filtr->process($value))
          );
          $db->update_query('settings' , $pola, " code = '".strtoupper((string)$key)."'");	
          unset($pola);
        }
      }
      
      if ( isset($_POST['id_kat']) && count($_POST['id_kat']) > 0 ) {
           //
           $kategorie = implode(',', (array)$_POST['id_kat']);
           $pola = array();
           $pola[] = array('value', $kategorie);
           $sql = $db->update_query('settings' , $pola, " code = 'INTEGRACJA_EASYPROTECT_KATEGORIE'");
           unset($pola);
           //
      }      

      $wynik = '<div id="'.$system.'" class="maleSukces" style="margin-left:20px;margin-top:10px;">dane zostały zmienione</div>';

    }

    $zapytanie = "SELECT * FROM settings WHERE type = 'ksiegowosc' ORDER BY sort ";
    $sql = $db->open_query($zapytanie);

    $parametr = array();

    if ( $db->ile_rekordow($sql) > 0 ) {
      while ($info = $sql->fetch_assoc()) {
        $parametr[$info['code']] = array($info['value'], $info['limit_values'], $info['description'], $info['form_field_type']);
      }
    }
    $db->close_query($sql);
    unset($zapytanie, $info);

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>

    <div id="naglowek_cont">Konfiguracja parametrów systemów finansowo-księgowych</div>
    <div id="cont">

      <div class="poleForm">
        <div class="naglowek">Edycja danych</div>
        
        <div class="SledzenieNaglowki">
        
            <div class="SledzenieOkno">
            
                <div class="SledzenieDiv" data-id="fakturowniaForm">
                    <div class="Foto"><img src="obrazki/logo/logo_fakturownia.png" alt="" /></div>
                    <span>Fakturownia.pl <br /> obsługa faktur online</span>
                </div>
              
            </div>
            
            <div class="SledzenieOkno">
            
                <div class="SledzenieDiv" data-id="ceidgForm">
                    <div class="Foto"><img src="obrazki/logo/logo_ceidg.jpg" alt="" /></div>
                    <span>CEIDG <br /> pobieranie danych firm</span>
                </div>
              
            </div>        

            <div class="SledzenieOkno">
            
                <div class="SledzenieDiv" data-id="automaterForm">
                    <div class="Foto"><img src="obrazki/logo/logo_automater.png" alt="" /></div>
                    <span>Automater.pl <br /> automatyzacja wysyłki cyfrowych towarów</span>
                </div>
              
            </div>                
            
            <div class="SledzenieOkno">
            
                <div class="SledzenieDiv" data-id="easyprotectForm">
                    <div class="Foto"><img src="obrazki/logo/easyprotect.png" alt="" /></div>
                    <span>EasyProtect &copy; <br /> przedłużona gwarancja</span>
                </div>
              
            </div>               

            <div class="SledzenieOkno">
            
                <div class="SledzenieDiv" data-id="openaiForm">
                    <div class="Foto"><img src="obrazki/logo/logo_openai.png" alt="" /></div>
                    <span>Open AI <br /> opisy przy pomocy sztucznej inteligencji</span>
                </div>
              
            </div> 
            
        </div>
          
        <div class="cl"></div>        

        <div class="pozycja_edytowana">  

          <script>
          $(document).ready(function() {
            
            $('.SledzenieOkno .SledzenieDiv').click(function() { 
               //
               var ido = $(this).attr('data-id');
               //
               $('.SledzenieOkno .SledzenieDiv').css({ 'opacity' : 0.5 }).removeClass('OknoAktywne');
               $(this).css({ 'opacity' : 1 }).addClass('OknoAktywne');
               //
               $('.Sledzenie form').hide();
               $('#' + ido).slideDown();
               //
               $.scrollTo('#' + ido,400);
               //
            });            
            
            $("#fakturowniaForm").validate({
              rules: {
                integracja_fakturownia_api: {required: function() {var wynik = true; if ( $("input[name='integracja_fakturownia_wlaczony']:checked", "#fakturowniaForm").val() == "nie" ) { wynik = false; } return wynik; }},
                integracja_fakturownia_url: {required: function() {var wynik = true; if ( $("input[name='integracja_fakturownia_wlaczony']:checked", "#fakturowniaForm").val() == "nie" ) { wynik = false; } return wynik; }}
              }
            });      

            $("#ceidgForm").validate({
              rules: {
                integracja_ceidg_api: {required: function() {var wynik = true; if ( $("input[name='integracja_ceidg_wlaczony']:checked", "#ceidgForm").val() == "nie" ) { wynik = false; } return wynik; }}
              }
            });               

            $("#easyprotectForm").validate({
              rules: {
                integracja_easyprotect_api: {required: function() {var wynik = true; if ( $("input[name='integracja_easyprotect_wlaczony']:checked", "#easyprotectForm").val() == "nie" ) { wynik = false; } return wynik; }}
              }
            });              
            
            $("#openaiForm").validate({
              rules: {
                integracja_openai_api: {required: function() {var wynik = true; if ( $("input[name='integracja_openai_wlaczony']:checked", "#openaiForm").val() == "nie" ) { wynik = false; } return wynik; }}
              }
            }); 
            
            <?php if ( $system != '' ) { ?>
            
            $('#<?php echo $system; ?>Form').show();
            $('.SledzenieOkno .SledzenieDiv').css({ 'opacity' : 0.5 }).removeClass('OknoAktywne');
            
            $('.SledzenieOkno .SledzenieDiv').each(function() {
               //
               var ido = $(this).attr('data-id');
               //
               if ( ido == '<?php echo $system; ?>Form' ) {
                    $(this).css({ 'opacity' : 1 }).addClass('OknoAktywne');
               }
               //
            }); 
            
            $.scrollTo('#<?php echo $system; ?>Form',400);

            setTimeout(function() {
              $('#<?php echo $system; ?>').fadeOut();
            }, 3000);
            
            <?php } ?>
          });
          </script>  
          
          <!-- fakturownia -->
          <div class="Sledzenie SledzenieUkryte">
          
            <form action="integracje/konfiguracja_ksiegowe.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="fakturowniaForm" class="cmxform"> 
            
            <div>
              <input type="hidden" name="akcja" value="zapisz" />
              <input type="hidden" name="system" value="fakturownia" />
            </div>
            
            <div class="ObramowanieForm">
            
              <table>
              
                <tr class="DivNaglowek">
                  <td colspan="2">Fakturownia.pl</td>
                </tr>
                
                <tr><td colspan="2" class="SledzenieOpis">
                  <div>
                      Fakturownia to program do faktur, który pozwoli zaoszczędzić Twój cenny czas. Jest zaprojektowana w taki sposób, aby zapewnić najbardziej optymalne i efektywne użytkowanie. Wysyłanie faktur jeszcze nigdy nie było tak szybkie i intuicyjne. <br /><br />
                      <span class="maleInfo">
                          Integracja obejmuje wystawienia faktur VAT / korygujących na podstawie zamówień sklepu bezpośrednio w serwisie Fakturownia. Integracja nie obejmuje obsługi magazynu produktów. 
                          Do poprawnego działania integracji w konfiguracji ustawień Fakturownia (menu Ustawienia / Ustawienia konta w serwisie Fakturownia) należy w sposobie wyliczania faktur ustawić "wyliczenia zgodnie z kasą fiskalną" (sumowanie od cen brutto) oraz
                          w opcji obliczania rabatu ustawić obliczanie rabatu "kwotowo". Zalecamy także wyłączenie automatycznego dodawania produktów do bazy z faktur.
                      </span>
                  </div>
                  <img src="obrazki/logo/logo_fakturownia.png" alt="" />
                </td></tr>
                
                <tr class="SledzeniePozycja">
                  <td>
                    <label>Włącz moduł Fakturownia.pl:</label>
                  </td>
                  <td>
                    <?php
                    echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_FAKTUROWNIA_WLACZONY']['1'], $parametr['INTEGRACJA_FAKTUROWNIA_WLACZONY']['0'], 'integracja_fakturownia_wlaczony', $parametr['INTEGRACJA_FAKTUROWNIA_WLACZONY']['2'], '', $parametr['INTEGRACJA_FAKTUROWNIA_WLACZONY']['3'] );
                    ?>
                  </td>
                </tr>
                
                <tr class="SledzeniePozycja">
                  <td>
                    <label class="required" for="integracja_fakturownia_api">Kod autoryzacyjny API: <em class="TipIkona"><b>Kod do pobrania w panelu Fakturowania.pl - menu Ustawienia / Ustawienia konta</b></em></label>
                  </td>
                  <td>
                    <?php
                    echo '<input type="text" name="integracja_fakturownia_api" id="integracja_fakturownia_api" value="'.$parametr['INTEGRACJA_FAKTUROWNIA_API']['0'].'" size="53" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_FAKTUROWNIA_API']['2'].'</b></em>';
                    ?>
                  </td>
                </tr>
                
                <tr class="SledzeniePozycja">
                  <td>
                    <label class="required" for="integracja_fakturownia_url">Adres konta: <em class="TipIkona"><b>Kod do pobrania w panelu Fakturowania.pl - menu Ustawienia / Ustawienia konta</b></em></label>
                  </td>
                  <td>
                    <?php
                    echo '<input type="text" name="integracja_fakturownia_url" id="integracja_fakturownia_url" value="'.$parametr['INTEGRACJA_FAKTUROWNIA_URL']['0'].'" size="53" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_FAKTUROWNIA_URL']['2'].'</b></em>';
                    ?>
                    <span class="maleInfo">Sam adres - bez http - np mojsklep.fakturownia.pl</span>
                  </td>
                </tr>
                
                <tr class="SledzeniePozycja">
                  <td>
                    <label for="integracja_fakturownia_dzial">Id działu firmy (department_id):</label>
                  </td>
                  <td>
                    <?php
                    echo '<input type="text" name="integracja_fakturownia_dzial" id="integracja_fakturownia_dzial" value="'.$parametr['INTEGRACJA_FAKTUROWNIA_DZIAL']['0'].'" size="53" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_FAKTUROWNIA_DZIAL']['2'].'</b></em>';
                    ?>
                    <span class="maleInfo">W fakturownia.pl menu Ustawienia > Dane firmy należy kliknąć na firmę/dział i ID działu pojawi się w URL - pole nieobowiązkowe do integracji</span>
                  </td>
                </tr>                

                <tr>
                  <td colspan="2">
                    <div class="przyciski_dolne">
                      <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'fakturownia' ? $wynik : '' ); ?>
                    </div>
                  </td>
                </tr>
                
              </table>
              
            </div>

            </form>
            
          </div>
          
          <!-- ceidg -->
          <div class="Sledzenie SledzenieUkryte">
          
            <form action="integracje/konfiguracja_ksiegowe.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="ceidgForm" class="cmxform"> 
            
            <div>
              <input type="hidden" name="akcja" value="zapisz" />
              <input type="hidden" name="system" value="ceidg" />
            </div>
            
            <div class="ObramowanieForm">
            
              <table>
              
                <tr class="DivNaglowek">
                  <td colspan="2">CEIDG</td>
                </tr>
                
                <tr><td colspan="2" class="SledzenieOpis">
                  <div>
                      CEIDG - CENTRALNA EWIDENCJA I INFORMACJA O DZIAŁALNOŚCI GOSPODARCZEJ
                      <span class="maleInfo">
                          Integracja pozwala na pobieranie danych o firmie na podstawie nr NIP podczas dodawania nowego klienta oraz dodawania nowego zamówienia (ręcznie z poziomu panelu zarządzania sklepu). Dane są pobierane z bazy CEIDG (nie są pobierane dane spółek z KRS).
                      </span>
                  </div>
                  <img src="obrazki/logo/logo_ceidg.jpg" alt="" />
                </td></tr>
                
                <tr class="SledzeniePozycja">
                  <td>
                    <label>Włącz moduł CEIDG:</label>
                  </td>
                  <td>
                    <?php
                    echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_CEIDG_WLACZONY']['1'], $parametr['INTEGRACJA_CEIDG_WLACZONY']['0'], 'integracja_ceidg_wlaczony', $parametr['INTEGRACJA_CEIDG_WLACZONY']['2'], '', $parametr['INTEGRACJA_CEIDG_WLACZONY']['3'] );
                    ?>
                  </td>
                </tr>
                
                <tr class="SledzeniePozycja">
                  <td>
                    <label class="required" for="integracja_ceidg_api">Klucz dostępu:</label>
                  </td>
                  <td>
                    <?php
                    echo '<input type="text" name="integracja_ceidg_api" id="integracja_ceidg_api" value="'.$parametr['INTEGRACJA_CEIDG_API']['0'].'" size="53" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_CEIDG_API']['2'].'</b></em>';
                    ?>
                    <span class="maleInfo">Klucz do pobrania na stronie: https://dane.biznes.gov.pl/ - wymaga rejestracji w serwisie</span>
                  </td>
                </tr>
                
                <tr>
                  <td colspan="2">
                    <div class="przyciski_dolne">
                      <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'ceidg' ? $wynik : '' ); ?>
                    </div>
                  </td>
                </tr>
                
              </table>
              
            </div>

            </form>
            
          </div>     

          <!-- automater -->
          <div class="Sledzenie SledzenieUkryte">
          
            <form action="integracje/konfiguracja_ksiegowe.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="automaterForm" class="cmxform"> 
            
            <div>
              <input type="hidden" name="akcja" value="zapisz" />
              <input type="hidden" name="system" value="automater" />
            </div>
            
            <div class="ObramowanieForm">
            
              <table>
              
                <tr class="DivNaglowek">
                  <td colspan="2">Automater</td>
                </tr>
                
                <tr><td colspan="2" class="SledzenieOpis">
                  <div>
                      Automater - niezawodny system do automatyzacji sprzedaży i wysyłki cyfrowych towarów na Allegro, eBay i w sklepie online.
                      <span class="maleInfo">
                          Dzięki automater możesz zautomatyzować sprzedaż i wysyłanie kodów Kupującym, jeśli zintegrujesz Automater z platformą sprzedażową. 
                      </span>
                  </div>
                  <img src="obrazki/logo/logo_automater.png" alt="" />
                </td></tr>
                
                <tr class="SledzeniePozycja">
                  <td>
                    <label>Włącz moduł Automater:</label>
                  </td>
                  <td>
                    <?php
                    echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_AUTOMATER_WLACZONY']['1'], $parametr['INTEGRACJA_AUTOMATER_WLACZONY']['0'], 'integracja_automater_wlaczony', $parametr['INTEGRACJA_AUTOMATER_WLACZONY']['2'], '', $parametr['INTEGRACJA_AUTOMATER_WLACZONY']['3'] );
                    ?>
                  </td>
                </tr>
                
                <tr class="SledzeniePozycja">
                  <td>
                    <label class="required" for="integracja_automater_api">API key: <em class="TipIkona"><b>Kod do pobrania w panelu automater w danych ustawień konta</b></em></label>
                  </td>
                  <td>
                    <?php
                    echo '<input type="text" name="integracja_automater_api" id="integracja_automater_api" value="'.$parametr['INTEGRACJA_AUTOMATER_API']['0'].'" size="53" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_AUTOMATER_API']['2'].'</b></em>';
                    ?>
                  </td>
                </tr>
                
                <tr class="SledzeniePozycja">
                  <td>
                    <label class="required" for="integracja_automater_api_secret">API secret: <em class="TipIkona"><b>Kod do pobrania w panelu automater w danych ustawień konta</b></em></label>
                  </td>
                  <td>
                    <?php
                    echo '<input type="text" name="integracja_automater_api_secret" id="integracja_automater_api_secret" value="'.$parametr['INTEGRACJA_AUTOMATER_API_SECRET']['0'].'" size="53" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_AUTOMATER_API_SECRET']['2'].'</b></em>';
                    ?>
                  </td>
                </tr>                
                
                <tr>
                  <td colspan="2">
                    <div class="przyciski_dolne">
                      <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'automater' ? $wynik : '' ); ?>
                    </div>
                  </td>
                </tr>
                
              </table>
              
            </div>

            </form>
            
          </div>             
        
          <!-- easyprotect -->
          <div class="Sledzenie SledzenieUkryte">
          
            <form action="integracje/konfiguracja_ksiegowe.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="easyprotectForm" class="cmxform"> 
            
            <div>
              <input type="hidden" name="akcja" value="zapisz" />
              <input type="hidden" name="system" value="easyprotect" />
            </div>
            
            <div class="ObramowanieForm">
            
              <table>
              
                <tr class="DivNaglowek">
                  <td colspan="2">EasyProtect &copy;</td>
                </tr>
                
                <tr><td colspan="2" class="SledzenieOpis">
                  <div>
                      EasyProtect &copy; to przedłużona ochrona serwisowa dedykowana dla większości sprzętu elektronicznego, komputerowego, sprzętu ogólnego użytku domowego. Ochrona jest udzielana w zakresie tożsamym z gwarancją producenta sprzętu. 
                      <span class="maleInfo">
                          Integracja jest dostępna tylko dla produktów w walucie PLN których wartość przekracza 200 zł i jest mniejsza niż 21999 zł brutto. Nie jest także dostępna dla zestawów produktów. Dostępna jest tylko dla produktów ze stawką VAT 23% (ponieważ jest dodawana jako element produktu - stawka VAT musi być taka sama zarówno produktu jak i usługi ochrony).
                      </span>
                  </div>
                  <img src="obrazki/logo/easyprotect.png" alt="" />
                </td></tr>
                
                <tr class="SledzeniePozycja">
                  <td>
                    <label>Włącz moduł EasyProtect &copy;:</label>
                  </td>
                  <td>
                    <?php
                    echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_EASYPROTECT_WLACZONY']['1'], $parametr['INTEGRACJA_EASYPROTECT_WLACZONY']['0'], 'integracja_easyprotect_wlaczony', $parametr['INTEGRACJA_EASYPROTECT_WLACZONY']['2'], '', $parametr['INTEGRACJA_EASYPROTECT_WLACZONY']['3'] );
                    ?>
                  </td>
                </tr>
                
                <tr class="SledzeniePozycja">
                  <td>
                    <label class="required" for="integracja_easyprotect_api">Klucz API: <em class="TipIkona"><b>Kod otrzymywany od EasyProtect &copy;</b></em></label>
                  </td>
                  <td>
                    <?php
                    echo '<input type="text" name="integracja_easyprotect_api" id="integracja_easyprotect_api" value="'.$parametr['INTEGRACJA_EASYPROTECT_API']['0'].'" size="53" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_EASYPROTECT_API']['2'].'</b></em>';
                    ?>
                  </td>
                </tr>
                
                <tr class="SledzeniePozycja">
                  <td>
                    <label>Czy integracja ma być dostępna dla wszystkich produktów czy tylko wybranych kategorii ?</label>
                  </td>
                  <td id="WyborZakresu">
                    <?php
                    echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_EASYPROTECT_PRODUKTY']['1'], $parametr['INTEGRACJA_EASYPROTECT_PRODUKTY']['0'], 'integracja_easyprotect_produkty', $parametr['INTEGRACJA_EASYPROTECT_PRODUKTY']['2'], '', $parametr['INTEGRACJA_EASYPROTECT_PRODUKTY']['3'] );
                    ?>
                    <script>
                    $(document).ready(function() {
            
                        $('#WyborZakresu input').click(function() { 
                            if ( $(this).val() == 'wszystkie' ) {
                                 $('#ZakresEasyProtect').hide();
                            }
                            if ( $(this).val() == 'kategorie' ) {
                                 $('#ZakresEasyProtect').show();
                            }                            
                        });
                    
                    });
                    </script>
                  </td>
                </tr>                
                
                <tr class="SledzeniePozycja" id="ZakresEasyProtect" <?php echo (($parametr['INTEGRACJA_EASYPROTECT_PRODUKTY']['0'] == 'kategorie') ? '' : 'style="display:none"'); ?>>
                  <td>
                    <label>Kategorie sklepu dla jakich będzie dostępna integracja:</label>
                  </td>                
                  <td>
                    <?php
                    echo '<div class="WybieranieKategorii"><div id="drzewo" style="margin:0px;"><table class="pkc">' . "\n";
                    //
                    $tablica_kat = Kategorie::DrzewoKategorii('0', '', '', '', false, true);
                    
                    for ($w = 0, $c = count($tablica_kat); $w < $c; $w++) {
                      
                        $podkategorie = false;
                        if ($tablica_kat[$w]['podkategorie'] == 'true') { $podkategorie = true; }
                        //
                        $check = '';
                        if ( in_array((string)$tablica_kat[$w]['id'], explode(',', (string)$parametr['INTEGRACJA_EASYPROTECT_KATEGORIE']['0'])) ) {
                            $check = 'checked="checked"';
                        }
                        //  
                        echo '<tr>
                                <td class="lfp"><input type="checkbox" value="' . $tablica_kat[$w]['id'] . '" name="id_kat[]" id="kat_nr_' . $tablica_kat[$w]['id'] . '" ' . $check . ' /> <label class="OpisFor" for="kat_nr_' . $tablica_kat[$w]['id'] . '">' . $tablica_kat[$w]['text'] . '</label></td>
                                <td class="rgp" ' . (($podkategorie) ? 'id="img_' . $tablica_kat[$w]['id'] . '"' : '') . '>' . (($podkategorie) ? '<img src="obrazki/rozwin.png" alt="Rozwiń" onclick="podkat(\'' . $tablica_kat[$w]['id'] . '\',\'\',\'checkbox\')" />' : '') . '</td>
                              </tr>
                              ' . (($podkategorie) ? '<tr><td colspan="2"><div id="p_' . $tablica_kat[$w]['id'] . '"></div></td></tr>' : '');
                              
                    }
                    
                    echo '</table></div>' . "\n";
                    
                    unset($tablica_kat,$podkategorie);

                    if ( $parametr['INTEGRACJA_EASYPROTECT_KATEGORIE']['0'] != '' ) {
                      
                        $przypisane_kategorie = $parametr['INTEGRACJA_EASYPROTECT_KATEGORIE']['0'];
                        $kate = explode(',', (string)$przypisane_kategorie);

                        foreach ( $kate as $val ) {

                              $sciezka = Kategorie::SciezkaKategoriiId($val, 'categories');
                              $cSciezka = explode("_", (string)$sciezka);  
                              
                              if (count($cSciezka) > 1) {
                                  //
                                  $ostatnie = strRpos($sciezka,'_');
                                  $analiza_sciezki = str_replace("_", ",", substr((string)$sciezka, 0, (int)$ostatnie));
                                  ?>
                                  
                                  <script>       
                                  podkat('<?php echo $analiza_sciezki; ?>', '<?php echo $cSciezka[count($cSciezka)-1]; ?>','checkbox','<?php echo $przypisane_kategorie; ?>');
                                  </script>
                                  
                              <?php
                              unset($sciezka,$cSciezka);
                              }

                        }

                        unset($przypisane_kategorie, $kate);  
                      
                    }
                    
                    echo '</div></div>';
                    ?>
                  </td>
                </tr>
                
                <tr class="SledzeniePozycja">
                  <td>
                    <label>Czy wysyłać do EasyProtect mail z informacją o zakupionej ochronie serwisowej ?</label>
                  </td>
                  <td>
                    <?php
                    echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_EASYPROTECT_MAIL']['1'], $parametr['INTEGRACJA_EASYPROTECT_MAIL']['0'], 'integracja_easyprotect_mail', $parametr['INTEGRACJA_EASYPROTECT_MAIL']['2'], '', $parametr['INTEGRACJA_EASYPROTECT_MAIL']['3'] );
                    ?>
                    <span class="maleInfo">Wysyłany mail zawiera nr zamówienia, nazwę sklepu oraz informacje o zakupionej ochronie. Nie zawiera żadnych danych osobowych.</span>
                  </td>
                </tr>                         
                
                <tr>
                  <td colspan="2">
                    <div class="przyciski_dolne">
                      <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'easyprotect' ? $wynik : '' ); ?>
                    </div>
                  </td>
                </tr>
                
              </table>
              
            </div>

            </form>
            
          </div>     

          <!-- open ai -->
          <div class="Sledzenie SledzenieUkryte">
          
            <form action="integracje/konfiguracja_ksiegowe.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="openaiForm" class="cmxform"> 
            
            <div>
              <input type="hidden" name="akcja" value="zapisz" />
              <input type="hidden" name="system" value="openai" />
            </div>
            
            <div class="ObramowanieForm">
            
              <table>
              
                <tr class="DivNaglowek">
                  <td colspan="2">Open AI</td>
                </tr>
                
                <tr><td colspan="2" class="SledzenieOpis">
                  <div>
                      Open AI - opisy przy pomocy sztucznej inteligencji
                      <span class="maleInfo">
                          Integracja pozwala tworzenie opisów produktów przy pomocy sztucznej inteligencji Open AI. Generator opisów produktów na podstawie nazwy lub opisu produktu.
                      </span>
                  </div>
                  <img src="obrazki/logo/logo_openai.png" alt="" />
                </td></tr>
                
                <tr class="SledzeniePozycja">
                  <td>
                    <label>Włącz moduł Open AI:</label>
                  </td>
                  <td>
                    <?php
                    echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_OPENAI_WLACZONY']['1'], $parametr['INTEGRACJA_OPENAI_WLACZONY']['0'], 'integracja_openai_wlaczony', $parametr['INTEGRACJA_OPENAI_WLACZONY']['2'], '', $parametr['INTEGRACJA_OPENAI_WLACZONY']['3'] );
                    ?>
                  </td>
                </tr>
                
                <tr class="SledzeniePozycja">
                  <td>
                    <label class="required" for="integracja_openai_api">Klucz dostępu:</label>
                  </td>
                  <td>
                    <?php
                    echo '<input type="text" name="integracja_openai_api" id="integracja_openai_api" value="'.$parametr['INTEGRACJA_OPENAI_API']['0'].'" size="83" /><em class="TipIkona"><b>'. $parametr['INTEGRACJA_OPENAI_API']['2'].'</b></em>';
                    ?>
                    <span class="maleInfo">Klucz do wygenerowania na stronie: https://platform.openai.com/account/api-keys - wymaga rejestracji w serwisie</span>
                  </td>
                </tr>
                
                <tr class="SledzeniePozycja">
                  <td>
                    <label for="integracja_openai_ilosc_znakow">Domyślna ilość znaków wygenerowanego tekstu:</label>
                  </td>
                  <td>
                    <?php
                    //
                    $tablica_list = array(100,200,300,400,500,600,700,800,900,1000,1200,1400,1500,1600,1800,2000,2300,2500,2800,3000,3500,4000);
                    //
                    echo '<select name="integracja_openai_ilosc_znakow" id="integracja_openai_ilosc_znakow">';
                    //
                    foreach ( $tablica_list as $lista ) {
                       //
                       echo '<option value="' . $lista . '" ' . (($parametr['INTEGRACJA_OPENAI_ILOSC_ZNAKOW']['0'] == $lista) ? 'selected="selected"' : '') . '>' . $lista . '</option>';
                       //
                    }
                    //
                    echo '</select>';
                    //
                    ?>
                  </td>
                </tr>                   
                
                <tr class="SledzeniePozycja">
                  <td>
                    <label class="required" for="integracja_openai_zadania">Dodatkowe zadania dla Open AI:</label>
                  </td>
                  <td>
                    <?php
                    echo '<textarea name="integracja_openai_zadania" id="integracja_openai_zadania" rows="5" cols="90">'.$parametr['INTEGRACJA_OPENAI_ZADANIA']['0'].'</textarea>';
                    ?>
                    <div class="maleInfo">
                        <b>Każde zadanie trzeba wpisać w osobnej linii.</b><br /><br />
                        W treści zadań można użyć 2 znaczników {NAZWA_PRODUKTU} - gdzie podstawiona zostanie nazwa produktu oraz {OPIS_PRODUKTU} gdzie podstawiony zostanie opis - np.<br /><br />
                        <i>Podaj listę podobnych produktów dla produktu o nazwie: {NAZWA_PRODUKTU}</i>
                        
                        <div style="height:1px;border-top:1px solid #dbdbdb;padding-top:10px;margin-top:10px;"></div>
                        
                        Domyślne zadania jakie można wybrać podczas generowania opisu: <br /><br />
                        
                        1) Stwórz opis produktu wg podanej nazwy produktu<br />
                        2) Stwórz opis produktu wg podanej nazwy i opisu<br />
                        3) Napisz alternatywny opis produktu wg podanej nazwy i opisu<br />
                        4) Napisz alternatywny (bardziej rozbudowany) opis produktu wg podanej nazwy i opisu<br />
                        5) Wymień w punktach zalety produktu wg podanej nazwy i opisu<br />
                        6) Wymień w punktach (wraz z opisami) zalety produktu wg podanej nazwy i opisu
                    </div>
                  </td>
                </tr>
                
                <tr>
                  <td colspan="2">
                    <div class="przyciski_dolne">
                      <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'openai' ? $wynik : '' ); ?>
                    </div>
                  </td>
                </tr>
                
              </table>
              
            </div>

            </form>
            
          </div>           
          
        </div>
      </div>
    </div>

    
    <?php
    include('stopka.inc.php');    
    
} ?>
