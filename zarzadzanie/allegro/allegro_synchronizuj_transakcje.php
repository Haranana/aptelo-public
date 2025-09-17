<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

  $wynik = '';

  if ( Funkcje::SprawdzAktywneAllegro() ) {

    $AllegroRest = new AllegroRest( array('allegro_user' => $_SESSION['domyslny_uzytkownik_allegro']) );

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    <div id="naglowek_cont">Synchronizacja transakcji w Allegro</div>
    <div id="cont">

      <div class="poleForm">

          <div class="naglowek">Pobieranie informacji z serwisu Allegro</div>

              <div class="pozycja_edytowana">    

                <div id="import">
                      
                  <div id="postep">Postęp importu ...</div>
                      
                  <div id="suwak">
                    <div style="margin:1px;overflow:hidden">
                      <div id="suwak_aktywny"></div>
                    </div>
                  </div>
                          
                  <div id="procent"></div>  

                  <div id="wynik" class="ListaAukcji" style="margin-top:10px;"></div>
                  
                </div>   
                      
                <div id="zaimportowano" style="display:none">
                  Dane w sklepie zostały zaktualizowane ...
                </div>
                
                <?php 
                $limit = 1000;
                if ( $AllegroRest->polaczenie['CONF_LAST_EVENT_ID'] != '' ) {

                    $ilosc_rekordow = $AllegroRest->IloscZdarzenAllegro();

                } else {

                    $zapytanie = "SELECT allegro_transaction_id, post_buy_form_created_date FROM allegro_transactions ORDER BY post_buy_form_created_date DESC LIMIT 1";

                    $sql = $db->open_query($zapytanie);

                    if ((int)$db->ile_rekordow($sql) > 0) {
                        while ( $info = $sql->fetch_assoc() ) {
                           $ilosc_rekordow = $AllegroRest->IloscZdarzenAllegro();
                        }
                    } else {
                        $ilosc_rekordow = $AllegroRest->IloscTransakcjiAllegro();
                        if ( $ilosc_rekordow > 10000 ) {
                            $ilosc_rekordow = 9999;
                        }
                        $limit = 100;
                    }

                    $db->close_query($sql);
                    unset($zapytanie, $info);

                }
                $liczba_linii = $ilosc_rekordow;

                
                ?>

                <script>
                //
                var ilosc_rekordow = <?php echo (int)$ilosc_rekordow; ?>;
                var ilosc_linii = <?php echo (int)$liczba_linii; ?>;
                var licznik_rekordow = 0;
                var limit_init = <?php echo (int)$limit; ?>;

                function import_danych(offset, limit) {

                  $.post( "allegro/allegro_import_transakcji.php?tok=<?php echo Sesje::Token(); ?>", 
                    { 
                      offset       : offset,
                      limit        : limit_init,
                      <?php if ( isset($_GET['sprzedaz']) ) { ?>
                      sprzedaz     : 1,
                      <?php }
                      if ( isset($_GET['wybrane']) ) { ?>
                      wybrane      : '<?php echo serialize($aukcje_tab); ?>',
                      <?php } ?>                      
                    },
                    function(data) {

                      if (ilosc_linii <= limit_init) {
                        procent = 100;
                      } else {
                        procent = parseInt((offset / (ilosc_linii - 1)) * limit_init);
                        if (procent > 100) {
                          procent = 100;
                        }
                      }

                      $('#procent').html('Stopień realizacji: <span id="ile_procent">' + procent + '%</span><br />Dodano / Zaktualizowano transakcji: <span id="licz_produkty">' + licznik_rekordow + '</span>');    

                      $('#suwak_aktywny').css('width' , (procent * 5) + 5 + 'px');

                      if (ilosc_linii - 100 > offset) {
                        setTimeout(() => import_danych(offset + limit_init , limit), 50);  // 50 ms pauzy
                      } else {
                        $('#ile_procent').html('100%');
                        $('#postep').css('display','none');
                        $('#suwak').slideUp("fast");
                        $('#zaimportowano').slideDown("fast");
                        $('#przyciski').slideDown("fast");
                      }   
                      if (data != '') {
                        var znacznik_ilosci = 'rek_';
                        var lastIndex = data.lastIndexOf(znacznik_ilosci);
                        var last_line;
                        last_line = parseInt(data.substr(lastIndex + znacznik_ilosci.length));

                        licznik_rekordow = licznik_rekordow + last_line;
                        $('#licz_produkty').html(licznik_rekordow);

                        data = data.substring(0, lastIndex);
                        $('#wynik').append(data);
                      }
                      
                    }
                  );
                }; 
                //
                import_danych(0, 0);              
                </script> 
                
                <?php

                if ( !isset($_GET['sprzedaz']) ) {
                  
                    $Znacznik = time();
                    $pola = array(
                            array('value',$Znacznik));
                            
                    $db->update_query('allegro_connect' , $pola, " params = 'CONF_LAST_SYNCHRONIZATION_ORDERS'");
                    
                    if ( isset($_POST['powrot']) && $_POST['powrot'] != '' ) {
                    
                      $powrot = $_POST['powrot'];
                      
                    } else {
                    
                      $powrot = 'allegro_aukcje';
                      
                    }
                    unset($Znacznik,$pola);
                    
                } else {
                  
                    $powrot = 'allegro_sprzedaz';
                  
                }
                ?>
                
                <div class="przyciski_dolne" id="przyciski" style="padding-left:0px; display:none">
                  <button type="button" class="przyciskNon" onclick="cofnij('<?php echo $powrot; ?>','<?php echo Funkcje::Zwroc_Get(array('x','y','sprzedaz','wybrane')); ?>','allegro');">Powrót</button> 
                </div>                    

              </div>

          </div>                      
      
      </div>
    </div>
    
    <?php
    include('stopka.inc.php');
    
  } else {
  
    Funkcje::PrzekierowanieURL('index.php');
    
  }


}

?>