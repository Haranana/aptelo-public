<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

        //
        $pola = array(
                array('status_id',(int)$_POST["status_id"]),
                array('comments_name',$filtr->process($_POST["nazwa"])),
                array('sort_order',(int)$_POST["sort"])
        );
        //	
        $db->insert_query('standard_order_comments' , $pola);
        $id_dodanej_pozycji = $db->last_id_query();

        unset($pola);

        $ile_jezykow = Funkcje::TablicaJezykow();
        for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
            //
            $pola = array(
                    array('comments_id',$id_dodanej_pozycji),
                    array('comments_text',$filtr->process($_POST['edytor_'.$w])),
                    array('languages_id',(int)$ile_jezykow[$w]['id']));        
            //
            $sql = $db->insert_query('standard_order_comments_description' , $pola);  
            unset($pola);
            //
        }

        unset($ile_jezykow);    

        //
        Funkcje::PrzekierowanieURL('zamowienia_statusy_komentarze.php?id_poz='.(int)$id_dodanej_pozycji.'&status_id='.(int)$_POST["status_id"]);
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    
    if ( !isset($_GET['status_id']) ) {
         $_GET['status_id'] = 0;
    }     
    ?>
    
    <div id="naglowek_cont">Dodanie komentarza zamówienia dla statusu - <?php echo Sprzedaz::pokazNazweStatusuZamowienia((int)$_GET['status_id'], $_SESSION['domyslny_jezyk']['id']); ?></div>
    <div id="cont">
          
          <script>
          $(document).ready(function() {
            $("#slownikForm").validate({
              rules: {
                nazwa: {
                  required: true
                },
                sort: {
                  required: true,
                  range: [0, 999],
                  number: true
                },
                messages: {
                  nazwa: {
                    required: "Pole jest wymagane."
                  },               
                  sort: {
                    required: "Pole jest wymagane.",
                    range: "Pole musi być liczbą większą od 0.",
                    number: "Pole musi być liczbą."
                  } 
                }
              }
            });
          });
          </script>     

          <form action="sprzedaz/zamowienia_statusy_komentarze_dodaj.php" method="post" id="slownikForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Dodawanie danych</div>
            
                <div class="pozycja_edytowana">
                
                    <input type="hidden" name="akcja" value="zapisz" />
                    <input type="hidden" name="status_id" value="<?php echo ((isset($_GET["status_id"])) ? (int)$_GET["status_id"] : '0'); ?>" />

                    <?php $ile_jezykow = Funkcje::TablicaJezykow(); ?>
                    
                    <div class="info_tab">
                    <?php
                    for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                        echo '<span id="link_'.$w.'" class="a_href_info_tab" onclick="gold_tabs(\''.$w.'\',\'edytor_\')">'.$ile_jezykow[$w]['text'].'</span>';
                    }                    
                    ?>                   
                    </div>
                    
                    <div style="clear:both"></div>
                    
                    <div class="info_tab_content">
                        <?php
                        for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                        
                            ?>
                            
                            <div id="info_tab_id_<?php echo $w; ?>" style="display:none;">
                            
                                <div class="edytor">
                                  <textarea cols="50" rows="30" id="edytor_<?php echo $w; ?>" name="edytor_<?php echo $w; ?>"></textarea>
                                </div>   
                                            
                            </div>
                            <?php                    
                        }                    
                        ?>                      
                    </div>
                    
                    <script>
                    gold_tabs('0','edytor_');
                    </script> 

                    <p>
                        <label class="required" for="nazwa">Nazwa:</label>
                        <input type="text" name="nazwa" size="60" value="" id="nazwa" /><em class="TipIkona"><b>Nazwa wyświetlana na liście wyboru komentarzy</b></em>
                    </p>

                    <p>
                        <label class="required" for="sort">Kolejność wyświetlnia:</label>
                        <input type="text" name="sort" id="sort" size="8" value="" /><em class="TipIkona"><b>Kolejność wyświetlania na liście wyboru komentarzy</b></em>
                    </p>

                </div>

            <div class="przyciski_dolne">
                <input type="submit" class="przyciskNon" value="Zapisz dane" />
                <button type="button" class="przyciskNon" onclick="cofnij('zamowienia_statusy_komentarze','<?php echo Funkcje::Zwroc_Get(array('x','y')); ?>','sprzedaz');">Powrót</button>   
            </div>            

          </div>                      
          </form>

    </div>    
    
    <div class="objasnienia">
    
        <div class="objasnieniaTytul">Znaczniki, które możesz użyć w treści wiadomości:</div>
        
        <div class="objasnieniaTresc">

            <ul class="mcol">

                <li><b>{NR_PRZESYLKI}</b> - Numer dokumentu przewozowego firmy kurierskiej</li>
                <li><b>{LINK_SLEDZENIA_PRZESYLKI}</b> - Link do strony śledzenia przesyłki</li>
                <li><b>{WARTOSC_ZAMOWIENIA}</b> - Wartość zamówienia</li>
                <li><b>{NUMER_ZAMOWIENIA}</b> - Numer zamówienia</li>
                <li><b>{ILOSC_PUNKTOW}</b> - Ilość punktów za zamówienie</li>
                <li><b>{DOKUMENT_SPRZEDAZY}</b> - Dokument sprzedaży do zamówienia: paragon lub faktura</li>
                <li><b>{FORMA_PLATNOSCI}</b> - Wybrana przez klienta forma płatności za zamówienie</li>
                <li><b>{FORMA_WYSYLKI}</b> - Wybrana przez klienta forma wysyłki zamówienia</li>
                <li><b>{LINK_PLIKOW_ELEKTRONICZNYCH}</b> - Link do pobrania plików elektronicznych lub kodów licencyjnych (bez znacznika linku &lt;a href ... - używane tylko przy sprzedaży produktów online</li>
                <li><b>{KUPON_RABATOWY_PO_ZAMOWIENIU}</b> - Kod kuponu rabatowego który zostanie wygenerowany po zmianie statusu zamówienia - wg ustawień z menu Sprzedaż / Zamówienia / Konfiguracja kuponów rabatowych przy zmianie statusu zamówienia</li>
                <li><b>{LISTA_PRODUKTOW}</b> - Lista zakupionych produktów (lista - jeden pod drugim)</li>
                
            </ul>

        </div> 
        
    </div>    
    
    <?php
    include('stopka.inc.php');

}
