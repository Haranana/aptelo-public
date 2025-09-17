<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    // wczytanie naglowka HTML
    include('naglowek.inc.php');

    ?>

    <div id="naglowek_cont">Automatyczne wypełnianie pól META</div>
    <div id="cont">

      <form action="pozycjonowanie/meta_tagi_automat_obsluga.php" method="post" id="pozycjonowanieForm" class="cmxform">
        <div class="poleForm">
            <div class="naglowek">Parametry wypełniania</div>

            <div class="pozycja_edytowana">
            
              <div class="info_content">

              <div class="ostrzezenie" style="margin:10px;">UWAGA: opcja wykonuje działania na bazie danych. Upewnij się, że posiadasz aktualną kopię na wypadek wystąpienia problemów.</div>

              <input type="hidden" name="akcja" value="aktualizuj" />

              <p id="wersja">
                <label>W jakim języku aktualizować dane:</label>
                <?php echo Funkcje::RadioListaJezykow(); ?>
              </p>

              <p>
                <label>Zakres modyfikacji:</label>
                <input type="radio" id="rekordy_wszystkie" value="0" name="zakres" onclick="$('#drzewo').slideUp()" checked="checked" /><label class="OpisFor" for="rekordy_wszystkie">wszystkie rekordy<em class="TipIkona"><b>Aktualizacja pól META dla wszystkich produktów w sklepie</b></em></label>
                <input type="radio" id="kategorie_zaznaczone" value="1" name="zakres" onclick="$('#drzewo').slideDown()" /><label class="OpisFor" for="kategorie_zaznaczone">tylko zaznaczone kategorie<em class="TipIkona"><b>Aktualizacja pól META tylko dla produktów z zaznaczonych kategorii</b></em></label>
              </p> 

              <div id="drzewo" style="display:none;margin:10px;width:95%;max-width:650px">
              
                <p class="WybraneKategorie">Zaznacz wybrane kategorie</p>                           

                <?php
                //
                echo '<table class="pkc">';
                //
                $tablica_kat = Kategorie::DrzewoKategorii('0', '', '', '', false, true);
                for ($w = 0, $c = count($tablica_kat); $w < $c; $w++) {
                  $podkategorie = false;
                  if ($tablica_kat[$w]['podkategorie'] == 'true') { $podkategorie = true; }
                  $check = '';
                  //
                  echo '<tr>
                          <td class="lfp"><input type="checkbox" value="'.$tablica_kat[$w]['id'].'" name="id_kat[]" '.$check.' id="kat_nr_'.$tablica_kat[$w]['id'].'"  /><label class="OpisFor" for="kat_nr_'.$tablica_kat[$w]['id'].'"> '.$tablica_kat[$w]['text'].(($tablica_kat[$w]['status'] == 0) ? '<div class="wylKat TipChmurka"><b>Kategoria jest nieaktywna</b></div>' : '').'</label></td>
                          <td class="rgp" '.(($podkategorie) ? 'id="img_'.$tablica_kat[$w]['id'].'"' : '').'>'.(($podkategorie) ? '<img src="obrazki/rozwin.png" alt="Rozwiń" onclick="podkat(\''.$tablica_kat[$w]['id'].'\',\'\',\'checkbox\')" />' : '').'</td>
                        </tr>
                        '.(($podkategorie) ? '<tr><td colspan="2"><div id="p_'.$tablica_kat[$w]['id'].'"></div></td></tr>' : '').'';
                }
                echo '</table>';
                unset($tablica_kat,$podkategorie);
                ?>
              </div>

              <p>
                <label>Metoda wypełniania danych:</label>
                <input type="radio" value="0" name="sposob" id="sposob_nazwa" onclick="$('#danewlasne').slideUp();$('#objasnienia').slideUp()" checked="checked" /><label class="OpisFor" for="sposob_nazwa">nazwa i opis<em class="TipIkona"><b>Sekcja META zostanie wypełniona nazwą oraz danymi zawartymi w opisie kategorii, producenta lub produktu</b></em></label>
                <input type="radio" value="1" name="sposob" id="sposob_wartosci" onclick="$('#danewlasne').slideDown();$('#objasnienia').slideDown()" /><label class="OpisFor" for="sposob_wartosci">zdefiniowane wartości<em class="TipIkona"><b>Sekcja meta zostanie wypełniona zdefiniowanymi poniżej danymi</b></em></label>
              </p> 

              <div id="danewlasne" style="display:none;" >
              
                <hr style="color:#82b4cd;border-top: 1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:98%;" />
                
                <p><b>KATEGORIE</b></p>

                <p>
                  <label for="tytul_kat">Tytuł kategorii:</label>
                  <input type="text" name="tytul_kat" id="tytul_kat" value="{NAZWA_KATEGORII}" size="103" />
                </p>
                <p>
                  <label for="opis_kat">Opis kategorii:</label>
                  <textarea cols="100" rows="3" name="opis_kat" id="opis_kat">{DUZE_NAZWA_KATEGORII} {OPIS_KATEGORII}.</textarea>
                </p>
                <p>
                  <label for="slowa_kat">Słowa kluczowe dla kategorii:</label>
                  <textarea cols="100" rows="3" name="slowa_kat" id="slowa_kat">{DUZE_NAZWA_KATEGORII}, {NAZWA_KATEGORII}, {MALE_NAZWA_KATEGORII}</textarea>
                </p>

                <hr style="color:#82b4cd;border-top: 1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:98%;" />
                
                <p><b>PRODUCENCI</b></p>

                <p>
                  <label for="tytul_producent">Tytuł producenta:</label>
                  <input type="text" name="tytul_producent" id="tytul_producent" value="{NAZWA_PRODUCENTA}" size="103" />
                </p>
                
                <p>
                  <label for="opis_producent">Opis producenta:</label>
                  <textarea cols="100" rows="3" name="opis_producent" id="opis_producent">{DUZE_NAZWA_PRODUCENTA} {OPIS_PRODUCENTA}</textarea>
                </p>
                
                <p>
                  <label for="slowa_producent">Słowa kluczowe dla producenta:</label>
                  <textarea cols="100" rows="3" name="slowa_producent" id="slowa_producent">{DUZE_NAZWA_PRODUCENTA}, {NAZWA_PRODUCENTA}, {MALE_NAZWA_PRODUCENTA}</textarea>
                </p>

                <hr style="color:#82b4cd;border-top: 1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:98%;" />
                
                <p><b>PRODUKTY</b></p>

                <p>
                  <label for="tytul_produkt">Tytuł produktu:</label>
                  <input type="text" name="tytul_produkt" id="tytul_produkt" value="{NAZWA_PRODUKTU}" size="103" />
                </p>
                
                <p>
                  <label for="opis_produkt">Opis produktu:</label>
                  <textarea cols="100" rows="3" name="opis_produkt" id="opis_produkt">{DUZE_NAZWA_PRODUKTU} najlepszy produkt na świecie. {OPIS_PRODUKTU}</textarea>
                </p>
                
                <p>
                  <label for="slowa_produkt">Słowa kluczowe dla produktu:</label>
                  <textarea cols="100" rows="3" name="slowa_produkt" id="slowa_produkt">{NAZWA_PRODUKTU}, {NAZWA_KATEGORII}, {NAZWA_PRODUCENTA}, {DUZE_NAZWA_PRODUKTU}, {MALE_NAZWA_PRODUKTU}</textarea>
                </p>
                
                <hr style="color:#82b4cd;border-top: 1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:98%;" />
                
              </div>

              <p>
                <label>Warunki wypełniania - kategorie:</label>
                <input type="radio" value="0" name="warunek_kat" id="warunek_kat1" /><label class="OpisFor" for="warunek_kat1">wszystkie<em class="TipIkona"><b>Aktualizacja wszystkich pól META</b></em></label>
                <input type="radio" value="1" name="warunek_kat" id="warunek_kat2" /><label class="OpisFor" for="warunek_kat2">tylko puste<em class="TipIkona"><b>Aktualizacja tylko pustych pól META</b></em></label>
                <input type="radio" value="2" name="warunek_kat" id="warunek_kat3" /><label class="OpisFor" for="warunek_kat3">wyczyść<em class="TipIkona"><b>Usunięcie wpisów z pól META</b></em></label>
                <input type="radio" value="3" name="warunek_kat" id="warunek_kat4" checked="checked" /><label class="OpisFor" for="warunek_kat4">bez zmian<em class="TipIkona"><b>Żadne pola nie zostaną zmodyfikowane</b></em></label>
              </p> 

              <p>
                <label>Warunki wypełniania - producenci:</label>
                <input type="radio" value="0" name="warunek_producent" id="warunek_producent1" /><label class="OpisFor" for="warunek_producent1">wszystkie<em class="TipIkona"><b>Aktualizacja wszystkich pól META</b></em></label>
                <input type="radio" value="1" name="warunek_producent" id="warunek_producent2" /><label class="OpisFor" for="warunek_producent2">tylko puste<em class="TipIkona"><b>Aktualizacja tylko pustych pól META</b></em></label>
                <input type="radio" value="2" name="warunek_producent" id="warunek_producent3" /><label class="OpisFor" for="warunek_producent3">wyczyść<em class="TipIkona"><b>Usunięcie wpisów z pól META</b></em></label>
                <input type="radio" value="3" name="warunek_producent" id="warunek_producent4" checked="checked" /><label class="OpisFor" for="warunek_producent4">bez zmian<em class="TipIkona"><b>Żadne pola nie zostaną zmodyfikowane</b></em></label>

              <p>
                <label>Warunki wypełniania - produkty:</label>
                <input type="radio" value="0" name="warunek_produkt" id="warunek_produkt1" /><label class="OpisFor" for="warunek_produkt1">wszystkie<em class="TipIkona"><b>Aktualizacja wszystkich pól META</b></em></label>
                <input type="radio" value="1" name="warunek_produkt" id="warunek_produkt2" /><label class="OpisFor" for="warunek_produkt2">tylko puste<em class="TipIkona"><b>Aktualizacja tylko pustych pól META</b></em></label>
                <input type="radio" value="2" name="warunek_produkt" id="warunek_produkt3" /><label class="OpisFor" for="warunek_produkt3">wyczyść<em class="TipIkona"><b>Usunięcie wpisów z pól META</b></em></label>
                <input type="radio" value="3" name="warunek_produkt" id="warunek_produkt4" checked="checked" /><label class="OpisFor" for="warunek_produkt4">bez zmian<em class="TipIkona"><b>Żadne pola nie zostaną zmodyfikowane</b></em></label>
              </p> 

              </div>

            </div>
            
            <div class="przyciski_dolne">
                <input type="submit" class="przyciskNon" value="Aktualizuj dane" />
            </div>            
        </div>
      </form>

    </div>

    <div class="objasnienia" id="objasnienia" style="display:none;">
    
      <div class="objasnieniaTytul">Znaczniki, które możesz użyć:</div>
      
      <div class="objasnieniaTresc">

        <ul class="mcol">
          <li><b>{NAZWA_KATEGORII}</b> - Nazwa kategorii (przy produktach - domyślnej dla produktu)</li>
          <li><b>{SCIEZKA_KATEGORII}</b> - Ścieżka kategorii produktu (przy produktach - domyślnej dla produktu) w postaci: Nazwa - Nazwa - Nazwa </li>
          <li><b>{DUZE_NAZWA_KATEGORII}</b> - Nazwa kategorii pisana dużymi literami (przy produktach - domyślnej dla produktu)</li>
          <li><b>{MALE_NAZWA_KATEGORII}</b> - Nazwa kategorii pisana małymi literami (przy produktach - domyślnej dla produktu)</li>
          <li><b>{Z_DUZEJ_NAZWA_KATEGORII}</b> - Nazwa kategorii pisana z dużej litery (przy produktach - domyślnej dla produktu)</li>
          <li><b>{OPIS_KATEGORII}</b> - Opis kategorii (przy wypełnianiu meta produktów niedostępne, tekst zostanie przycięty do 255 znaków)</li>
        </ul>
        
        <ul class="mcol">          
          <li><b>{NAZWA_PRODUCENTA}</b> - Nazwa producenta</li>
          <li><b>{DUZE_NAZWA_PRODUCENTA}</b> - Nazwa producenta pisana dużymi literami</li>
          <li><b>{MALE_NAZWA_PRODUCENTA}</b> - Nazwa producenta pisana małymi literami</li>
          <li><b>{Z_DUZEJ_NAZWA_PRODUCENTA}</b> - Nazwa producenta pisana z dużej litery</li>
          <li><b>{OPIS_PRODUCENTA}</b> - Opis producenta (przy wypełnianiu meta produktów niedostępne, tekst zostanie przycięty do 255 znaków)</li>
        </ul>
        
        <ul class="mcol">
          <li><b>{NAZWA_PRODUKTU}</b> - Nazwa produktu</li>
          <li><b>{DUZE_NAZWA_PRODUKTU}</b> - Nazwa produktu pisana dużymi literami</li>
          <li><b>{MALE_NAZWA_PRODUKTU}</b> - Nazwa produktu pisana małymi literami</li>
          <li><b>{Z_DUZEJ_NAZWA_PRODUKTU}</b> - Nazwa produktu pisana z dużej litery</li>
          <li><b>{OPIS_PRODUKTU}</b> - Opis produktu (przy wypełnianiu meta kategorii i producentów niedostępne, tekst zostanie przycięty do 255 znaków)</li>
          <li><b>{NR_KATALOGOWY}</b> - Numer katalogowy produktu
          <li><b>{KOD_PRODUCENTA}</b> - Kod producenta
          <li><b>{KOD_EAN}</b> - Kod EAN          
        </ul>
        
      </div>
      
    </div>

    <?php
    include('stopka.inc.php');    
    
} ?>
