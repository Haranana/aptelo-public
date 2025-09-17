<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

        $zapytanie = "SELECT MAX(page_id) AS id FROM headertags";
        $sql = $db->open_query($zapytanie);
        $info = $sql->fetch_assoc();   

        $id_dodanej_pozycji = $info['id'] + 1;
        $db->close_query($sql);
        unset($zapytanie, $info);

        $ile_jezykow = Funkcje::TablicaJezykow();
        for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
            //        
            $pola = array(
                    array('page_id',$id_dodanej_pozycji),
                    array('page_name',$filtr->process($_POST["skrypt"])),
                    array('page_title',( $_POST['tytul_'.$w] == '' ? $filtr->process($_POST['tytul_0']) : $filtr->process($_POST['tytul_'.$w]))),
                    array('page_description',( $_POST['opis_'.$w] == '' ? $filtr->process($_POST['opis_0']) : $filtr->process($_POST['opis_'.$w]))),
                    array('page_keywords',( $_POST['slowa_'.$w] == '' ? $filtr->process($_POST['slowa_0']) : $filtr->process($_POST['slowa_'.$w]))),
                    array('append_default',$filtr->process($_POST['domyslne_'.$w])),
                    array('sortorder',(int)$_POST['sortowanie_'.$w]),
                    array('language_id',$ile_jezykow[$w]['id']));

            $db->insert_query('headertags' , $pola);
            unset($pola);
            //          
        }              
        //

        Funkcje::PrzekierowanieURL('meta_tagi_podstrony.php?id_poz='.$id_dodanej_pozycji);
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Dodanie pozycji</div>
    <div id="cont">
          
        <script>
        $(document).ready(function() {
          $.validator.addMethod("valueNotEquals", function (value, element, arg) {
            return arg != value;
          }, "Wybierz opcję");

          $("#metaForm").validate({
            rules: {
              skrypt: { required: true, valueNotEquals: "0" }
            },
            messages: {
              skrypt: {
                valueNotEquals: "Brak plików dla których można zdefiniować dane"
              }
            }
          });
          
          $('#skrypt').change(function() {
            if ( $(this).val() == 'produkt.php' ) {
                 $('#objasnienia_produkt').slideDown();
              } else {
                 $('#objasnienia_produkt').slideUp();
            }
            if ( $(this).val() == 'listing.php' ) {
                 $('#objasnienia_listing').slideDown();
              } else {
                 $('#objasnienia_listing').slideUp();
            }
            if ( $(this).val() == 'recenzja.php' ) {
                 $('#objasnienia_recenzja').slideDown();
              } else {
                 $('#objasnienia_recenzja').slideUp();
            }   
            if ( $(this).val() == 'napisz_recenzje.php' ) {
                 $('#objasnienia_recenzja_napisz').slideDown();
              } else {
                 $('#objasnienia_recenzja_napisz').slideUp();
            }             
          })          
        });
        </script>         

        <form action="pozycjonowanie/meta_tagi_podstrony_dodaj.php" method="post" id="metaForm" class="cmxform">          

        <div class="poleForm">
          <div class="naglowek">Dodanie danych</div>
          
              <div class="pozycja_edytowana">
              
                  <input type="hidden" name="akcja" value="zapisz" />
              
                  <?php $ile_jezykow = Funkcje::TablicaJezykow(); ?>
                  
                  <div class="info_tab">
                  <?php
                  for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                      echo '<span id="link_'.$w.'" class="a_href_info_tab" onclick="gold_tabs(\''.$w.'\')">'.$ile_jezykow[$w]['text'].'</span>';
                  }                    
                  ?>                   
                  </div>
                  
                  <div style="clear:both"></div>
                  
                  <div class="info_tab_content">
                      <?php
                      for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                      
                          ?>
                          
                          <div id="info_tab_id_<?php echo $w; ?>" style="display:none;">
                          
                              <p>
                                 <?php if ($w == '0') { ?>
                                  <label class="required" for="tytul_0">Tytuł strony:</label>
                                  <input type="text" name="tytul_<?php echo $w; ?>" size="120" onkeyup="licznik_znakow_meta(this,'iloscZnakowNazwa_<?php echo $w; ?>')" value="" id="tytul_0" class="required" />
                                 <?php } else { ?>
                                  <label for="tytul_<?php echo $w; ?>">Tytuł strony:</label>   
                                  <input type="text" name="tytul_<?php echo $w; ?>" id="tytul_<?php echo $w; ?>" size="120" onkeyup="licznik_znakow_meta(this,'iloscZnakowNazwa_<?php echo $w; ?>')" value="" />
                                 <?php } ?>
                              </p> 
                              
                              <p class="LicznikMeta">
                                <label></label>
                                Ilość wpisanych znaków: <span class="iloscZnakow" id="iloscZnakowNazwa_<?php echo $w; ?>">0</span>
                                zalecana maksymalna ilość: <span class="iloscZnakow" id="iloscZnakowNazwa_<?php echo $w; ?>Max"><?php echo DLUGOSC_META_NAZWA; ?></span>
                              </p>                                   

                              <p>
                                <label for="opis_<?php echo $w; ?>">Opis strony:</label>   
                                <textarea name="opis_<?php echo $w; ?>" id="opis_<?php echo $w; ?>" onkeyup="licznik_znakow_meta(this,'iloscZnakowOpis_<?php echo $w; ?>')" cols="117" rows="3"></textarea>
                              </p> 
                              
                              <p class="LicznikMeta">
                                <label></label>
                                Ilość wpisanych znaków: <span class="iloscZnakow" id="iloscZnakowOpis_<?php echo $w; ?>">0</span>
                                zalecana maksymalna ilość: <span class="iloscZnakow" id="iloscZnakowOpis_<?php echo $w; ?>Max"><?php echo DLUGOSC_META_OPIS; ?></span>
                              </p>                                  

                              <p>
                                <label for="slowa_<?php echo $w; ?>">Słowa kluczowe:</label>   
                                <textarea name="slowa_<?php echo $w; ?>" id="slowa_<?php echo $w; ?>" onkeyup="licznik_znakow_meta(this,'iloscZnakowSlowa_<?php echo $w; ?>')" cols="117" rows="3"></textarea>
                              </p> 
                              
                              <p class="LicznikMeta">
                                <label></label>
                                Ilość wpisanych znaków: <span class="iloscZnakow" id="iloscZnakowSlowa_<?php echo $w; ?>">0</span>
                                zalecana maksymalna ilość: <span class="iloscZnakow" id="iloscZnakowSlowa_<?php echo $w; ?>Max"><?php echo DLUGOSC_META_SLOWA; ?></span>
                              </p>                                  

                              <p>
                                <label>Czy dołączać wartości domyślne:</label>
                                <input type="radio" name="domyslne_<?php echo $w; ?>" id="domyslne_tak_<?php echo $w; ?>" value="1" checked="checked" /><label class="OpisFor" for="domyslne_tak_<?php echo $w; ?>">tak<em class="TipIkona"><b>Do sekcji META będą dołączone wartości domyślne ustawione dla serwisu</b></em></label>
                                <input type="radio" name="domyslne_<?php echo $w; ?>" id="domyslne_nie_<?php echo $w; ?>" value="0" /><label class="OpisFor" for="domyslne_nie_<?php echo $w; ?>">nie<em class="TipIkona"><b>Do sekcji META nie będą dołączone wartości domyślne ustawione dla serwisu</b></em></label>
                              </p>

                              <p>
                                <label>Jak dołączać wartości domyślne:</label>
                                <input type="radio" name="sortowanie_<?php echo $w; ?>" id="sortowanie_start_<?php echo $w; ?>" value="0" checked="checked" /><label class="OpisFor" for="sortowanie_start_<?php echo $w; ?>">początek<em class="TipIkona"><b>Wartości domyślne ustawione dla serwisu dołączone na początku</b></em></label>
                                <input type="radio" name="sortowanie_<?php echo $w; ?>" id="sortowanie_koniec_<?php echo $w; ?>" value="1" /><label class="OpisFor" for="sortowanie_koniec_<?php echo $w; ?>">koniec<em class="TipIkona"><b>Wartości domyślne ustawione dla serwisu dołączone po wartościach indywidualnych</b></em></label>
                              </p>

                          </div>
                          <?php                    
                      }                    
                      ?>                      
                  </div>
                  
                  <script>
                  gold_tabs('0');
                  </script>  
                  
                  <p>
                    <label class="required" for="skrypt">Nazwa skryptu:</label>
                    <?php
                    $tablica_plikow = Funkcje::ListaPlikow( '', false, 
                    array('koniec.php',
                          'blad.php',
                          'start.php',
                          'ankieta.php',
                          'formularz.php',
                          'galeria.php',
                          'kategoria_artykulow.php',
                          'platnosc_koniec.php',
                          'listing.php',
                          'listing_dol.php',
                          'listing_gora.php',
                          'index.php',
                          'partner.php',
                          'strona_informacyjna.php',
                          'reklama.php',
                          'pp_bannery.php',
                          'allegro.php',
                          'adres_dostawy_edycja.php',
                          'porownywarka_xml.php',
                          'allegro_synchronizacja.php'
                    ));
                    
                    $tablica_tmp = array();
                    
                    foreach ( $tablica_plikow as $plik ) {
                        //
                        $tablica_tmp[] = array('id' => $plik['id'], 'text' => MetaTagi::NazwaPodstrony($plik['id']));
                        //
                    }
                    
                    $tablica_plikow = $tablica_tmp;

                    $tablica_plikow[] = array('id' => 'listing.php', 'text' => 'listing.php - tylko w przypadku listingu produktów z kategorii');
                    
                    sort($tablica_plikow);
                    
                    unset($tablica_tmp);
                      
                    echo Funkcje::RozwijaneMenu('skrypt', $tablica_plikow,'', 'id="skrypt" style="max-width:500px;"');
                    ?><em class="TipIkona"><b>Nazwa skryptu generującego stronę, dla której są definiowane META TAGI</b></em>
                  </p>
              </div>

              <div class="przyciski_dolne">
                <input type="submit" class="przyciskNon" value="Zapisz dane" />
                <button type="button" class="przyciskNon" onclick="cofnij('meta_tagi_podstrony','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','pozycjonowanie');">Powrót</button>           
              </div>                 
        </div>                      
        </form>

    </div>   

    <div class="objasnienia" id="objasnienia_produkt" style="display:none">
    
      <div class="objasnieniaTytul">Znaczniki, które możesz użyć:</div>
      
      <div class="objasnieniaTresc">

        <ul class="mcol">
          <li><b>{NAZWA_PRODUKTU}</b> - Nazwa produktu</li>
          <li><b>{DUZE_NAZWA_PRODUKTU}</b> - Nazwa produktu pisana dużymi literami</li>
          <li><b>{MALE_NAZWA_PRODUKTU}</b> - Nazwa produktu pisana małymi literami</li>
          <li><b>{Z_DUZEJ_NAZWA_PRODUKTU}</b> - Nazwa produktu pisana z dużej litery</li>
          <li><b>{OPIS_PRODUKTU}</b> - Opis skrócony produktu (tekst zostanie przycięty do 255 znaków)</li>
          <li><b>{NR_KATALOGOWY}</b> - Numer katalogowy produktu</li>
          <li><b>{KOD_PRODUCENTA}</b> - Kod producenta</li>
          <li><b>{KOD_EAN}</b> - Kod EAN</li>                  
        </ul>
        
        <ul class="mcol">        
          <li><b>{NAZWA_KATEGORII}</b> - Nazwa kategorii (domyślnej dla produktu)</li>
          <li><b>{DUZE_NAZWA_KATEGORII}</b> - Nazwa kategorii pisana dużymi literami (domyślnej dla produktu)</li>
          <li><b>{MALE_NAZWA_KATEGORII}</b> - Nazwa kategorii pisana małymi literami (domyślnej dla produktu)</li>
          <li><b>{Z_DUZEJ_NAZWA_KATEGORII}</b> - Nazwa kategorii pisana z dużej litery (domyślnej dla produktu)</li>
          <li><b>{SCIEZKA_KATEGORII}</b> - Ścieżka kategorii produktu (domyślnej dla produktu) w postaci: Nazwa - Nazwa - Nazwa </li>
        </ul>
        
        <ul class="mcol">            
          <li><b>{NAZWA_PRODUCENTA}</b> - Nazwa producenta</li>
          <li><b>{DUZE_NAZWA_PRODUCENTA}</b> - Nazwa producenta pisana dużymi literami</li>
          <li><b>{MALE_NAZWA_PRODUCENTA}</b> - Nazwa producenta pisana małymi literami</li>
          <li><b>{Z_DUZEJ_NAZWA_PRODUCENTA}</b> - Nazwa producenta pisana z dużej litery</li>
        </ul>
        
      </div>
      
    </div>  
    
    <div class="objasnienia" id="objasnienia_listing" style="display:none">
    
      <div class="objasnieniaTytul">Znaczniki, które możesz użyć (będą użyte tylko przy listingu produktów z kategorii):</div>
      
      <div class="objasnieniaTresc">

        <ul class="mcol">          
          <li><b>{NAZWA_KATEGORII}</b> - Nazwa kategorii</li>
          <li><b>{DUZE_NAZWA_KATEGORII}</b> - Nazwa kategorii pisana dużymi literami</li>
          <li><b>{MALE_NAZWA_KATEGORII}</b> - Nazwa kategorii pisana małymi literami</li>
          <li><b>{Z_DUZEJ_NAZWA_KATEGORII}</b> - Nazwa kategorii pisana z dużej litery</li>
          <li><b>{SCIEZKA_KATEGORII}</b> - Ścieżka kategorii produktu w postaci: Nazwa - Nazwa - Nazwa </li>
          <li><b>{OPIS_KATEGORII}</b> - Opis kategorii (tekst zostanie przycięty do 255 znaków)</li>     
        </ul>
        
      </div>
      
    </div>     

    <div class="objasnienia" id="objasnienia_recenzja" style="display:none">
    
      <div class="objasnieniaTytul">Znaczniki, które możesz użyć:</div>
      
      <div class="objasnieniaTresc">
      
        <ul class="mcol">          
          <li><b>{AUTOR_RECENZJI}</b> - Autor napisanej recenzji produktu</li>
          <li><b>{DATA_RECENZJI}</b> - Data napisania recenzji produktu</li>
          <li><b>{TRESC_RECENZJI}</b> - Treść napisanej recenzji produktu</li>
        </ul>
        
        <ul class="mcol">            
          <li><b>{NAZWA_PRODUKTU}</b> - Nazwa produktu</li>
          <li><b>{DUZE_NAZWA_PRODUKTU}</b> - Nazwa produktu pisana dużymi literami</li>
          <li><b>{MALE_NAZWA_PRODUKTU}</b> - Nazwa produktu pisana małymi literami</li>
          <li><b>{Z_DUZEJ_NAZWA_PRODUKTU}</b> - Nazwa produktu pisana z dużej litery</li>
          <li><b>{OPIS_PRODUKTU}</b> - Opis skrócony produktu (tekst zostanie przycięty do 255 znaków)</li>
          <li><b>{NR_KATALOGOWY}</b> - Numer katalogowy produktu</li>
          <li><b>{KOD_PRODUCENTA}</b> - Kod producenta</li>
          <li><b>{KOD_EAN}</b> - Kod EAN</li>        
        </ul>
        
        <ul class="mcol">            
          <li><b>{NAZWA_KATEGORII}</b> - Nazwa kategorii (domyślnej dla produktu)</li>
          <li><b>{DUZE_NAZWA_KATEGORII}</b> - Nazwa kategorii pisana dużymi literami (domyślnej dla produktu)</li>
          <li><b>{MALE_NAZWA_KATEGORII}</b> - Nazwa kategorii pisana małymi literami (domyślnej dla produktu)</li>
          <li><b>{Z_DUZEJ_NAZWA_KATEGORII}</b> - Nazwa kategorii pisana z dużej litery (domyślnej dla produktu)</li>
          <li><b>{SCIEZKA_KATEGORII}</b> - Ścieżka kategorii produktu (domyślnej dla produktu) w postaci: Nazwa - Nazwa - Nazwa </li>
        </ul>
        
        <ul class="mcol">            
          <li><b>{NAZWA_PRODUCENTA}</b> - Nazwa producenta</li>
          <li><b>{DUZE_NAZWA_PRODUCENTA}</b> - Nazwa producenta pisana dużymi literami</li>
          <li><b>{MALE_NAZWA_PRODUCENTA}</b> - Nazwa producenta pisana małymi literami</li>
          <li><b>{Z_DUZEJ_NAZWA_PRODUCENTA}</b> - Nazwa producenta pisana z dużej litery</li>          
        </ul>
        
      </div>
      
    </div>   

    <div class="objasnienia" id="objasnienia_recenzja_napisz" style="display:none">
    
      <div class="objasnieniaTytul">Znaczniki, które możesz użyć:</div>
      
      <div class="objasnieniaTresc">
      
        <ul class="mcol">          
          <li><b>{NAZWA_PRODUKTU}</b> - Nazwa produktu</li>
          <li><b>{DUZE_NAZWA_PRODUKTU}</b> - Nazwa produktu pisana dużymi literami</li>
          <li><b>{MALE_NAZWA_PRODUKTU}</b> - Nazwa produktu pisana małymi literami</li>
          <li><b>{Z_DUZEJ_NAZWA_PRODUKTU}</b> - Nazwa produktu pisana z dużej litery</li>
          <li><b>{OPIS_PRODUKTU}</b> - Opis skrócony produktu (tekst zostanie przycięty do 255 znaków)</li>
          <li><b>{NR_KATALOGOWY}</b> - Numer katalogowy produktu</li>
          <li><b>{KOD_PRODUCENTA}</b> - Kod producenta</li>
          <li><b>{KOD_EAN}</b> - Kod EAN</li>        
        </ul>
        
        <ul class="mcol">            
          <li><b>{NAZWA_KATEGORII}</b> - Nazwa kategorii (domyślnej dla produktu)</li>
          <li><b>{DUZE_NAZWA_KATEGORII}</b> - Nazwa kategorii pisana dużymi literami (domyślnej dla produktu)</li>
          <li><b>{MALE_NAZWA_KATEGORII}</b> - Nazwa kategorii pisana małymi literami (domyślnej dla produktu)</li>
          <li><b>{Z_DUZEJ_NAZWA_KATEGORII}</b> - Nazwa kategorii pisana z dużej litery (domyślnej dla produktu)</li>
          <li><b>{SCIEZKA_KATEGORII}</b> - Ścieżka kategorii produktu (domyślnej dla produktu) w postaci: Nazwa - Nazwa - Nazwa </li>
        </ul>
        
        <ul class="mcol">            
          <li><b>{NAZWA_PRODUCENTA}</b> - Nazwa producenta</li>
          <li><b>{DUZE_NAZWA_PRODUCENTA}</b> - Nazwa producenta pisana dużymi literami</li>
          <li><b>{MALE_NAZWA_PRODUCENTA}</b> - Nazwa producenta pisana małymi literami</li>
          <li><b>{Z_DUZEJ_NAZWA_PRODUCENTA}</b> - Nazwa producenta pisana z dużej litery</li>          
        </ul>
        
      </div>
      
    </div>        
    
    <div class="objasnienia" id="objasnienia_artykul" style="display:none">
    
      <div class="objasnieniaTytul">Znaczniki, które możesz użyć:</div>
      
      <div class="objasnieniaTresc">
      
        <ul class="mcol">          
          <li><b>{TYTUL_ARTYKULU}</b> - Tytuł artykułu</li>
          <li><b>{DUZE_TYTUL_ARTYKULU}</b> - Tytuł artykułu pisany dużymi literami</li>
          <li><b>{MALE_TYTUL_ARTYKULU}</b> - Tytuł artykułu pisany małymi literami</li>
          <li><b>{Z_DUZEJ_TYTUL_ARTYKULU}</b> - Tytuł artykułu pisany z dużej litery</li>
          <li><b>{TRESC_ARTYKULU}</b> - Opis skrócony artykułu (tekst zostanie przycięty do 255 znaków)</li>      
        </ul>
        
        <ul class="mcol">            
          <li><b>{NAZWA_KATEGORII}</b> - Nazwa kategorii do jakiej należy artykuł</li>
          <li><b>{DUZE_NAZWA_KATEGORII}</b> - Nazwa kategorii do jakiej należy artykuł pisana dużymi literami (domyślnej dla produktu)</li>
          <li><b>{MALE_NAZWA_KATEGORII}</b> - Nazwa kategorii do jakiej należy artykuł pisana małymi literami (domyślnej dla produktu)</li>
          <li><b>{Z_DUZEJ_NAZWA_KATEGORII}</b> - Nazwa kategorii do jakiej należy artykuł pisana z dużej litery (domyślnej dla produktu)</li>     
        </ul>
        
      </div>
      
    </div>     
    
    <?php
    include('stopka.inc.php');

}
