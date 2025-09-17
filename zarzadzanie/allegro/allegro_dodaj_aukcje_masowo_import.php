<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

  $wynik = '';

  if ( Funkcje::SprawdzAktywneAllegro() && isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz' ) {

    $AllegroRest = new AllegroRest( array('allegro_user' => $_SESSION['domyslny_uzytkownik_allegro']) );

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    <div id="naglowek_cont">Pobieranie danych aukcji z Allegro</div>
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
                  Dane w sklepie zostały przetworzone ...
                </div>
                
                <?php
                $tablicaSklep = array();
                $tablicaAllegro = array();
                
                $zapytanie_aukcje = "SELECT allegro_id, auction_id, external_id FROM allegro_auctions WHERE auction_seller = '" . $_SESSION['domyslny_uzytkownik_allegro'] . "'";
                $sql_aukcje = $db->open_query($zapytanie_aukcje);

                while ( $info_aukcje = $sql_aukcje->fetch_assoc() ) {
                    $tablicaSklep[] = "'" . $info_aukcje['auction_id'] . "'";
                }
                
                $db->close_query($sql_aukcje);
                unset($zapytanie_aukcje, $info_aukcje);                
                
                $ilosc_aukcji = $AllegroRest->IloscWystawionychAllegro();
                $przebiegi = $ilosc_aukcji / 500;
                
                for ( $i = 0, $c = ceil($przebiegi); $i < $c; $i++ ) {
                  
                    $offset = 500 * $i;

                    $tablicaTmpAllegro = $AllegroRest->TablicaWszystkichAukcjiAllegro( 500, $offset );
                    
                    if ( isset($tablicaTmpAllegro) && count($tablicaTmpAllegro) < 1 ) {
                        break;
                    }

                    foreach ( $tablicaTmpAllegro as $tablicaTmpAllegroTmp ) {
                      
                        if ( isset($tablicaTmpAllegroTmp->publication->status) && $tablicaTmpAllegroTmp->publication->status == 'ACTIVE' ) {
                             $tablicaAllegro[] = "'" . $tablicaTmpAllegroTmp->id . "'";
                        }
                        
                    }

                }
                
                $zlaczona = array_diff($tablicaAllegro, $tablicaSklep);
                
                if ( count($tablicaSklep) > 0 && count($tablicaAllegro) == 0 ) {
                     $zlaczona = $tablicaSklep;
                }
                if ( count($tablicaSklep) == 0 && count($tablicaAllegro) > 0 ) {
                     $zlaczona = $tablicaAllegro;
                }
                ?>

                <script>
                var aukcje = new Array(<?php echo implode(',', $zlaczona); ?>);

                function import_danych(offset) {
                  
                  $.post( "allegro/allegro_dodaj_aukcje_masowo_akcja.php?tok=<?php echo Sesje::Token(); ?>", 
                    { 
                      id_aukcji: aukcje[offset],
                    },
                    function(data) {

                      procent = parseInt(((offset + 1) / aukcje.length) * 100);
                      if (procent > 100) {
                          procent = 100;
                      }

                      $('#procent').html('Stopień realizacji: <span id="ile_procent">' + procent + '%</span><br />Przetworzono: <span id="licz_produkty">' + (offset + 1) + '</span>');    

                      $('#suwak_aktywny').css('width' , (procent * 5) + 5 + 'px');

                      if (aukcje.length - 1 > offset) {
                        import_danych(offset + 1);
                      } else {
                        $('#ile_procent').html('100%');
                        $('#licz_produkty').html(aukcje.length);
                        $('#postep').css('display','none');
                        $('#suwak').slideUp("fast");
                        $('#zaimportowano').slideDown("fast");
                        $('#przyciski').slideDown("fast");
                      }   
                      if (data != '') {
                        $('#wynik').html( $('#wynik').html() + data );
                      }
                      
                    }
                  );
                }; 
                //
                import_danych(0);              
                </script> 

                <div class="przyciski_dolne" id="przyciski" style="padding-left:0px; display:none">
                  <button type="button" class="przyciskNon" onclick="cofnij('allegro_aukcje','<?php echo Funkcje::Zwroc_Get(array('x','y')); ?>','allegro');">Powrót</button> 
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