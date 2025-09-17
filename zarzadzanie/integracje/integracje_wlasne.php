<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja_head']) && $_POST['akcja_head'] == 'zapisz') {

       $pola = array(array('value',base64_encode((string)$_POST['kod_head'])));
       $db->update_query('settings' , $pola, " code = 'KOD_HEAD'");	
       unset($pola);

       $wynik = '<div class="maleSukces" style="margin-left:20px;margin-top:10px;">dane zostały zapisane</div>';

    }
    
    if (isset($_POST['akcja_head_analityczne']) && $_POST['akcja_head_analityczne'] == 'zapisz') {

       $pola = array(array('value',base64_encode((string)$_POST['kod_head_analityczne'])));
       $db->update_query('settings' , $pola, " code = 'KOD_HEAD_ANALITYCZNE'");	
       unset($pola);

       $wynik = '<div class="maleSukces" style="margin-left:20px;margin-top:10px;">dane zostały zapisane</div>';

    }    
    
    if (isset($_POST['akcja_head_reklamowe']) && $_POST['akcja_head_reklamowe'] == 'zapisz') {

       $pola = array(array('value',base64_encode((string)$_POST['kod_head_reklamowe'])));
       $db->update_query('settings' , $pola, " code = 'KOD_HEAD_REKLAMOWE'");	
       unset($pola);

       $wynik = '<div class="maleSukces" style="margin-left:20px;margin-top:10px;">dane zostały zapisane</div>';

    }     

    if (isset($_POST['akcja_head_funkcjonalne']) && $_POST['akcja_head_funkcjonalne'] == 'zapisz') {

       $pola = array(array('value',base64_encode((string)$_POST['kod_head_funkcjonalne'])));
       $db->update_query('settings' , $pola, " code = 'KOD_HEAD_FUNKCJONALNE'");	
       unset($pola);

       $wynik = '<div class="maleSukces" style="margin-left:20px;margin-top:10px;">dane zostały zapisane</div>';

    }     
    
    if (isset($_POST['akcja_body']) && $_POST['akcja_body'] == 'zapisz') {

       $pola = array(array('value',base64_encode((string)$_POST['kod_body'])));
       $db->update_query('settings' , $pola, " code = 'KOD_BODY'");	
       unset($pola);

       $wynik = '<div class="maleSukces" style="margin-left:20px;margin-top:10px;">dane zostały zapisane</div>';

    }    
    
    if (isset($_POST['akcja_podsumowanie_zamowienia']) && $_POST['akcja_podsumowanie_zamowienia'] == 'zapisz') {

       $pola = array(array('value',base64_encode((string)$_POST['kod_podsumowanie_zamowienia'])));
       $db->update_query('settings' , $pola, " code = 'KOD_BODY_PODSUMOWANIE_ZAMOWIENIA'");	
       unset($pola);

       $wynik = '<div class="maleSukces" style="margin-left:20px;margin-top:10px;">dane zostały zapisane</div>';

    }        
    
    $zapytanie = "SELECT * FROM settings WHERE type = 'brak' AND (code = 'KOD_BODY' OR code = 'KOD_HEAD_ANALITYCZNE' OR code = 'KOD_HEAD_REKLAMOWE' OR code = 'KOD_HEAD_FUNKCJONALNE' OR code = 'KOD_HEAD' OR code = 'KOD_BODY_PODSUMOWANIE_ZAMOWIENIA')";
    $sql = $db->open_query($zapytanie);

    $parametr = array();

    while ($info = $sql->fetch_assoc()) {
      $parametr[$info['code']] = base64_decode((string)$info['value']);
    }
      
    $db->close_query($sql);
    unset($zapytanie, $info);

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>

    <div id="naglowek_cont">Konfiguracja integracji własnych - możliwość dodania kodu serwisów zewnętrznych, np czat, statystyki</div>
    <div id="cont">

      <div class="poleForm">
        <div class="naglowek">Edycja danych</div>

        <div class="pozycja_edytowana">  

            <form action="integracje/integracje_wlasne.php" id="HeadForm" method="post" class="cmxform"> 
            
                <input type="hidden" name="akcja_head" value="zapisz" />
                
                <div class="ObramowanieForm">
                
                  <table>
                  
                    <tr class="DivNaglowek">
                      <td>Kod w nagłówku strony w sekcji &lt;head&gt; (wyświetlany zawsze)</td>
                    </tr>

                    <tr>
                      <td class="IntegracjaKod">
                        <textarea name="kod_head" id="kod_head" rows="15" cols="70"><?php echo $parametr['KOD_HEAD']; ?></textarea>
                      </td>
                    </tr>
                    
                    <tr>
                      <td>
                        <div class="przyciski_dolne">
                          <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ((isset($_POST['akcja_head'])) ? $wynik : '' ); ?>
                        </div>
                      </td>
                    </tr>
                    
                  </table>
                  
                </div>

            </form>
            
            <form action="integracje/integracje_wlasne.php" id="HeadFormAnalityczne" method="post" class="cmxform"> 
            
                <input type="hidden" name="akcja_head_analityczne" value="zapisz" />
                
                <div class="ObramowanieForm">
                
                  <table>
                  
                    <tr class="DivNaglowek">
                      <td>Kod w nagłówku strony w sekcji &lt;head&gt; <b style="color:#ff0000">(wyświetlany TYLKO jeżeli klient zatwierdzi zgodę na cookies analityczne)</b></td>
                    </tr>

                    <tr>
                      <td class="IntegracjaKod">
                        <textarea name="kod_head_analityczne" id="kod_head_analityczne" rows="10" cols="70"><?php echo $parametr['KOD_HEAD_ANALITYCZNE']; ?></textarea>
                      </td>
                    </tr>
                    
                    <tr>
                      <td>
                        <div class="przyciski_dolne">
                          <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ((isset($_POST['akcja_head_analityczne'])) ? $wynik : '' ); ?>
                        </div>
                      </td>
                    </tr>
                    
                  </table>
                  
                </div>

            </form>  

            <form action="integracje/integracje_wlasne.php" id="HeadFormMarketingowe" method="post" class="cmxform"> 
            
                <input type="hidden" name="akcja_head_reklamowe" value="zapisz" />
                
                <div class="ObramowanieForm">
                
                  <table>
                  
                    <tr class="DivNaglowek">
                      <td>Kod w nagłówku strony w sekcji &lt;head&gt; <b style="color:#ff0000">(wyświetlany TYLKO jeżeli klient zatwierdzi zgodę na cookies reklamowe)</b></td>
                    </tr>

                    <tr>
                      <td class="IntegracjaKod">
                        <textarea name="kod_head_reklamowe" id="kod_head_reklamowe" rows="10" cols="70"><?php echo $parametr['KOD_HEAD_REKLAMOWE']; ?></textarea>
                      </td>
                    </tr>
                    
                    <tr>
                      <td>
                        <div class="przyciski_dolne">
                          <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ((isset($_POST['akcja_head_reklamowe'])) ? $wynik : '' ); ?>
                        </div>
                      </td>
                    </tr>
                    
                  </table>
                  
                </div>

            </form> 

            <form action="integracje/integracje_wlasne.php" id="HeadFormFunkcjonalne" method="post" class="cmxform"> 
            
                <input type="hidden" name="akcja_head_funkcjonalne" value="zapisz" />
                
                <div class="ObramowanieForm">
                
                  <table>
                  
                    <tr class="DivNaglowek">
                      <td>Kod w nagłówku strony w sekcji &lt;head&gt; <b style="color:#ff0000">(wyświetlany TYLKO jeżeli klient zatwierdzi zgodę na cookies funkcjonalne)</b></td>
                    </tr>

                    <tr>
                      <td class="IntegracjaKod">
                        <textarea name="kod_head_funkcjonalne" id="kod_head_funkcjonalne" rows="10" cols="70"><?php echo $parametr['KOD_HEAD_FUNKCJONALNE']; ?></textarea>
                      </td>
                    </tr>
                    
                    <tr>
                      <td>
                        <div class="przyciski_dolne">
                          <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ((isset($_POST['akcja_head_funkcjonalne'])) ? $wynik : '' ); ?>
                        </div>
                      </td>
                    </tr>
                    
                  </table>
                  
                </div>

            </form>              
            
            <form action="integracje/integracje_wlasne.php" id="BodyForm" method="post" class="cmxform"> 
            
                <input type="hidden" name="akcja_body" value="zapisz" />
                
                <div class="ObramowanieForm">
                
                  <table>
                  
                    <tr class="DivNaglowek">
                      <td>Kod w stopce strony przed zamknięciem znacznika &lt;/body&gt;</td>
                    </tr>

                    <tr>
                      <td class="IntegracjaKod">
                        <textarea name="kod_body" id="kod_body" rows="15" cols="70"><?php echo $parametr['KOD_BODY']; ?></textarea>
                      </td>
                    </tr>
                    
                    <tr>
                      <td>
                        <div class="przyciski_dolne">
                          <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ((isset($_POST['akcja_body'])) ? $wynik : '' ); ?>
                        </div>
                      </td>
                    </tr>
                    
                  </table>
                  
                </div>

            </form>          

            <form action="integracje/integracje_wlasne.php" id="PodsumowanieZamowieniaForm" method="post" class="cmxform"> 
            
                <input type="hidden" name="akcja_podsumowanie_zamowienia" value="zapisz" />
                
                <div class="ObramowanieForm">
                
                  <table>
                  
                    <tr class="DivNaglowek">
                      <td>Kod na stronie podsumowania zamówienia (strona po złożeniu przez klienta zamówienia)</td>
                    </tr>

                    <tr>
                      <td class="IntegracjaKod">
                        <textarea name="kod_podsumowanie_zamowienia" id="kod_podsumowanie_zamowienia" rows="15" cols="70"><?php echo $parametr['KOD_BODY_PODSUMOWANIE_ZAMOWIENIA']; ?></textarea>
                      </td>
                    </tr>
                    
                    <tr>
                      <td>
                        <div class="przyciski_dolne">
                          <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ((isset($_POST['akcja_podsumowanie_zamowienia'])) ? $wynik : '' ); ?>
                        </div>
                      </td>
                    </tr>
                    
                  </table>
                                  
                  <div class="objasnienia" style="margin:15px">
                  
                      <div class="objasnieniaTytul">Znaczniki, które możesz użyć w kodzie na stronie podsumowania zamówienia:</div>
                      <div class="objasnieniaTresc">

                      <ul class="mcol">
                          <li><b>{NUMER_ZAMOWIENIA}</b> - Numer zamówienia (w postaci numerycznej)</li>
                          <li><b>{EMAIL_KUPUJACEGO}</b> - Adres email osoby kupującej</li>
                          <li><b>{IMIE_KUPUJACEGO}</b> - Imię osoby kupującej</li>
                          <li><b>{NAZWISKO_KUPUJACEGO}</b> - Nazwisko osoby kupującej</li>
                          <li><b>{DATA_ZAMOWIENIA}</b> - Data złożenia zamówienia (w formacie dzien-miesiąc-rok np 12-04-2021)</li>
                          <li><b>{LISTA_PRODUKTOW_ID_PRZECINEK}</b> - Lista produktów (id produktów) rozdzielone przecinkami np 1,2,3,4,5</li>
                          <li><b>{LISTA_PRODUKTOW_ID_SREDNIK}</b> - Lista produktów (id produktów) rozdzielone średnikami np 1;2;3;4;5</li>
                          <li><b>{WARTOSC_ZAMOWIENIA}</b> - Wartość zamówienia brutto w formie numerycznej np 999.49 (separator dzisiętny: kropka)</li>
                          <li><b>{KOSZT_PRZESYLKI}</b> - Koszt przesyłki brutto w formie numerycznej np 999.49 (separator dzisiętny: kropka)</li>
                          <li><b>{KUPON_RABATOWY_NAZWA}</b> - Nazwa kuponu rabatowego jeżeli był użyty w formie tekstowej</li>
                          <li><b>{KUPON_RABATOWY_WARTOSC}</b> - Wartość kuponu rabatowego jeżeli był użyty w formie numerycznej np 999.49 (separator dzisiętny: kropka)</li>
                          <li><b>{WALUTA_ZAMOWIENIA}</b> - Kod waluty w jakiej zostało złożone zamówienie np PLN</li>
                          <li><b>{FORMA_PLATNOSCI}</b> - Nazwa formy płatności w formie tekstowej</li>
                          <li><b>{FORMA_WYSYLKI}</b> - Nazwa formy wysyłki w formie tekstowej</li>
                          <li><b>{TIMESTAMP}</b> - Unixowy znacznik czasu</li>
                          <li><b>{SESSION_ID}</b> - Identyfikator sesji klienta</li>
                      </ul>
                      
                      </div>

                  </div>                

                </div>

            </form>             
            
          </div>
          
      </div>
      
    </div>
    
    <?php if ( isset($_POST['akcja_head']) || isset($_POST['akcja_body']) ) { ?>

    <script>
    $(document).ready(function() {
      
        <?php if ( isset($_POST['akcja_head']) ) { ?>
          
        $.scrollTo('#HeadForm',400);
        setTimeout(function() { $('#HeadForm .maleSukces').fadeOut(); }, 3000);
          
        <?php } ?>
        
        <?php if ( isset($_POST['akcja_body']) ) { ?>
          
        $.scrollTo('#BodyForm',400);
        setTimeout(function() { $('#BodyForm .maleSukces').fadeOut(); }, 3000);
          
        <?php } ?> 

    });
    </script>     
    
    <?php } ?>
    
    <?php
    include('stopka.inc.php');    
    
} ?>
