<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {
  ?>

    <script>
    $(document).ready(function() {
      for ( var i in CKEDITOR.instances ){
         CKEDITOR.remove(CKEDITOR.instances[i])
         break;
      }
      var config = {
        filebrowserBrowseUrl : 'przegladarka.php?typ=ckedit&tok=<?php echo Sesje::Token(); ?>',
        filebrowserImageBrowseUrl : 'przegladarka.php?typ=ckedit&tok=<?php echo Sesje::Token(); ?>',
        filebrowserFlashBrowseUrl : 'przegladarka.php?typ=ckedit&tok=<?php echo Sesje::Token(); ?>',
        filebrowserWindowWidth : '990',
        filebrowserWindowHeight : '580',
        filebrowserWindowFeatures : 'menubar=no,toolbar=no,minimizable=no,resizable=no,scrollbars=no' 
      };
      $('textarea.wysiwyg').ckeditor(config);

    });
    </script>        
    
    <div class="EdycjaOdstep">

        <div class="pozycja_edytowana">

          <div class="info_content">

            <p id="wersja">
              <label>W jakim języku wysłać email:</label>
              <?php
              echo Funkcje::RadioListaJezykow();
              ?>
            </p>

            <p>
              <label>Nowy status zamówienia:</label>
              <?php
              $tablica = Sprzedaz::ListaStatusowZamowien(true, '--- wybierz z listy ---');
              echo Funkcje::RozwijaneMenu('status', $tablica,'','id="status" onchange="UkryjZapiszKomentarz(this.value)" style="width:350px;"'); ?>
            </p>

            <p>
              <label>Standardowy komentarz:</label>
              <?php
              $tablica = array();
              $tablica[] = array('id' => '0', 'text' => '--- najpierw wybierz status zamówienia ---');
              echo Funkcje::RozwijaneMenu('status_komentarz', $tablica,'','id="komentarz" onchange="ZmienKomentarz(this.value)" style="width:350px;"'); ?>                  
            </p>
            
            <div id="LadujKomentarz"><img src="obrazki/_loader_small.gif" alt="" /></div>        

            <p>
              <label>Poinformuj klienta e-mail:</label>
              <input type="checkbox" checked="checked" value="1" name="info_mail" id="info_mail" />
              <label class="OpisForPustyLabel" for="info_mail"></label>
              <em class="TipIkona"><b>Informacja o zmianie statusu zostanie przesłana do klienta</b></em>
            </p>

            <?php if ( SMS_WLACZONE == 'tak' && SMS_ZMIANA_STATUSU_ZAMOWIENIA == 'tak' ) { ?>
              <p>
                <label>Poinformuj klienta SMS:</label>
                <input type="checkbox" value="1" name="info_sms" id="info_sms" />
                <label class="OpisForPustyLabel" for="info_sms"></label>
                <em class="TipIkona"><b>Wysłanie powiadomienia SMS do klienta o zmianie statusu - tylko jeżeli jest podany poprawny numer GSM</b></em>
              </p>
            <?php } ?>

            <p>
              <label>Dołącz komentarz do maila:</label>
              <input type="checkbox" checked="checked" value="1" name="dolacz_komentarz" id="dolacz_komentarz" />
              <label class="OpisForPustyLabel" for="dolacz_komentarz"></label>
              <em class="TipIkona"><b>Informacja komentarza zostanie dołączona do maila z powiadomieniem do klienta</b></em>              
            </p>
            
            <?php if ( SYSTEM_PUNKTOW_STATUS == 'tak' ) { ?>
            
            <p class="Punkty">
              <label>Zatwierdź punkty:</label>
              <input type="checkbox" value="1" name="zatwierdz_punkty" id="zatwierdz_punkty" />
              <label class="OpisForPustyLabel" for="zatwierdz_punkty"></label>
              <em class="TipIkona"><b>Zostaną zatwierdzone i dodane do konta klienta punkty (bez punktów Programu Partnerskiego)</b></em>                   
            </p>        
            
            <?php } ?>

            <p>
              <label>Komentarz:</label>
              <textarea cols="100" rows="10" name="komentarz" class="wysiwyg" id="komentarz_tresc"></textarea>
            </p>
            
            <span class="maleInfo">
                Jeżeli komentarz zawiera znaczniki w postaci {...} zostaną pod nie podstawione odpowiednie wartości podczas wysyłania wiadomości i zapisu zmiany statusu dla zamówienia.
            </span>

            <script>
            function UkryjZapiszKomentarz(id) {
                if (parseInt(id)== 0) {
                    $("#komentarz_tresc").val('');
                }   
                //
                $('#LadujKomentarz').fadeIn('fast');
                $.post('sprzedaz/standardowe_komentarze.php', { jezyk: 1, id: id, nazwy: 'tak', tryb: 'multi' }, function(data){
                  $("#komentarz").html(data);
                  $('#LadujKomentarz').fadeOut('fast');
                  $("#komentarz_tresc").val('');
                });                   
            }   
            function ZmienKomentarz(id) {
                var jezyk = $("input[name='jezyk']:checked").val();
                $('#LadujKomentarz').fadeIn('fast');
                $.post('sprzedaz/standardowe_komentarze.php', { jezyk: jezyk, id: id, nazwy: 'nie', tryb: 'multi' }, function(data){
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
            
            });
            </script>
            
            <p>
              <label>Dodaj uwagi do zamówienia:</label>
              <input type="checkbox" value="1" name="zmiana_uwagi" id="zmiana_uwagi" />
              <label class="OpisForPustyLabel" for="zmiana_uwagi"></label>                          
            </p>   
            
            <script>
            $(document).ready(function() {
            
                $("#zmiana_uwagi").change(function(){
                  if ( $(this).prop('checked') == true ) {
                       $('#uwagi_zamowienie').stop().slideDown();
                  } else {
                       $('#uwagi_zamowienie').stop().slideUp();
                  }
                });                
            
            });
            </script>            

            <div id="uwagi_zamowienie" style="display:none">
            
                <p>
                  <label>Sposób dodania:</label>
                  <input type="radio" name="dodanie_uwag" value="1" id="dodanie_uwag_tak" checked="checked" /><label class="OpisFor" for="dodanie_uwag_tak">dodaj do uwag (wstawiając datę dodania)</label>
                  <input type="radio" name="dodanie_uwag" value="0" id="dodanie_uwag_nie" /><label class="OpisFor" for="dodanie_uwag_nie">nadpisz istniejące uwagi</label>                                    
                </p>  

                <p>
                  <label for="tresc_uwag">Treść uwag:</label>
                  <textarea name="tresc_uwag" cols="50" rows="3"></textarea>                  
                </p>      

            </div>

          </div>

        </div>
        
    </div>

  <?php
  }
?>