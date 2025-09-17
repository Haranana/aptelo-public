<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    $wynik = '';

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

      reset($_POST);
      foreach($_POST as $key => $value) {
        if ( $key != 'akcja' && $key != 'ilosc_znakow' ) {
          $pola = array(
                  array('value',$value)
          );
          $db->update_query('allegro_connect' , $pola, " params = '".strtoupper((string)$key)."'");	
        }
      }
      unset($pola);

      $wynik = '<div class="maleSukces" style="margin-left:20px;margin-top:10px;">dane zostały zmienione</div>';

    }

    $AllegroRest = new AllegroRest();

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>

    <div id="naglowek_cont">Konfiguracja parametrów obsługi aukcji Allegro</div>
    <div id="cont">

      <div class="poleForm">
        <div class="naglowek">Edycja danych</div>

        <div class="pozycja_edytowana">  

        <script>
        $(document).ready(function() {
          setTimeout(function() {
              $('.maleSukces').fadeOut();
          }, 3000);
        });
        </script>

        <form action="allegro/konfiguracja_polaczenia.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="allegroForm" class="cmxform"> 
        
          <input type="hidden" name="akcja" value="zapisz" />
          
          <div class="info_content">
          
            <div class="ObramowanieForm" style="margin-top:10px;">
            
              <table>
              
                <tr class="div_naglowek">
                  <td colspan="2" style="padding-left:10px;">Parametry logowania</td>
                </tr>

                <tr class="PozycjaAllegroForm">
                  <td>
                    <em class="TipIkona"><b>Serwer jaki będzie obsługiwał wystawianie aukcji Allegro.</b></em>
                    <label for="conf_orders_status">Domyślny serwer Allegro:</label>
                  </td>
                  <?php
                  $tablica = array(
                             array('id' => '1', 'text' => 'Serwer Allegro PL')
                  );
                  ?>
                  <td>
                    <?php echo Funkcje::RozwijaneMenu('conf_country', $tablica, $AllegroRest->polaczenie['CONF_COUNTRY'], ' style="width: 320px;" id="conf_country"'); ?> 
                  </td>                          
                </tr>

                <tr class="PozycjaAllegroForm">
                  <td>
                    <em class="TipIkona"><b>Czy moduł wystawiania aukcji ma działać w trybie testowym ?</b></em>
                    <label>Tryb testowy SANDBOX:</label>
                  </td>
                  <td>
                    <input type="radio" id="testowy_tak" <?php echo ( $AllegroRest->polaczenie['CONF_SANDBOX'] == 'tak' ? 'checked="checked"' : '' ); ?> value="tak" name="conf_sandbox"><label class="OpisFor" for="testowy_tak">tak<em class="TipIkona"><b>Sandbox to zamknięte, odizolowane środowisko deweloperskie, pozwalające na bezpieczne testowanie aplikacji działających w oparciu o WebAPI platformy Allegro.</b></em></label>
                    <input type="radio" id="testowy_nie" <?php echo ( $AllegroRest->polaczenie['CONF_SANDBOX'] == 'nie' ? 'checked="checked"' : '' ); ?> value="nie" name="conf_sandbox"><label class="OpisFor" for="testowy_nie">nie<em class="TipIkona"><b>Aukcje będą wystawiane na rzeczywistym serwerze Allegro i od razu widoczne w serwisie aukcyjnym.</b></em></label>
                  </td>
                </tr>

              </table>
              
            </div>
            
            <div class="ObramowanieForm" style="margin-top:10px;">
              
              <table style="margin-top:2px;">
              
                <tr class="div_naglowek">
                  <td colspan="2" style="padding-left:10px;">Parametry obsługi aukcji</td>
                </tr>
                
                <tr class="PozycjaAllegroForm">
                  <td>
                    <em class="TipIkona"><b>Status jaki otrzyma zamówienie utworzone na podstawie aukcji Allegro</b></em>
                    <label for="conf_orders_status">Status zamówienia:</label>
                  </td>
                  <?php
                  $tablica = Sprzedaz::ListaStatusowZamowien(false, '--- Wybierz z listy ---');
                  ?>
                  <td>
                    <?php echo Funkcje::RozwijaneMenu('conf_orders_status', $tablica, $AllegroRest->polaczenie['CONF_ORDERS_STATUS'], ' style="width: 320px;" id="conf_orders_status"'); ?> 
                  </td>                          
                </tr>

                <?php

                // domyslne zaznaczenie
                echo '<tr class="PozycjaAllegroForm"><td>';
                echo '<em class="TipIkona"><b>Nazwa produktu jaka ma być użyta do tworzenia zamówienia dla produktu z allegro</b></em>';
                echo '<label for="nazwa_tak">Nazwa produktu dla zamówienia</label></td>';
                    
                echo '<td>
                <input type="radio" ' . ($AllegroRest->polaczenie['CONF_INVOICE_PRODUCTS_NAME'] == 'tak' ? 'checked="checked"' : '' ) . ' value="tak" name="conf_invoice_products_name" id="nazwa_tak"><label for="nazwa_tak" class="OpisFor">Nazwa produktu dla allegro <em class="TipIkona"><b>Przy tworzeniu zamówienia na podstawie aukcji zostanie wstawiona nazwa produktu jak w allegro</b></em></label> <br />
                <input type="radio" ' . ($AllegroRest->polaczenie['CONF_INVOICE_PRODUCTS_NAME'] == 'nie' ? 'checked="checked"' : '' ) . ' value="nie" name="conf_invoice_products_name" id="nazwa_nie"><label for="nazwa_nie" class="OpisFor">Nazwa produktu w sklepie <em class="TipIkona"><b>Przy tworzeniu zamówienia na podstawie aukcji zostanie wstawiona nazwa produktu jaka jest w sklepie</b></em></label></td>';

                ?>

                <tr>
                  <td colspan="2">
                    <div class="przyciski_dolne">
                      <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo $wynik; ?>
                    </div>
                  </td>
                </tr>
                
              </table>
              
            </div>
            
          </div>
          
        </form>

        </div>

      </div>
      
    </div>
    
    <?php
    include('stopka.inc.php');    
    
} 
?>
