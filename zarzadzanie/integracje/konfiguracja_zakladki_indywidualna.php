<div class="Sledzenie SledzenieUkryte">

  <form action="integracje/konfiguracja_zakladki.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="<?php echo $nazwa; ?>Form" class="cmxform">
  
    <div>
        <input type="hidden" name="akcja" value="zapisz" />
        <input type="hidden" name="system" value="<?php echo $nazwa; ?>" />
    </div>
    
    <div class="ObramowanieForm">
    
        <table>
        
          <tr class="DivNaglowek">
            <td style="text-align:left" colspan="2">Indywidualna zakładka definiowana przez użytkownika</td>
          </tr>
          
          <tr><td colspan="2" class="SledzenieOpis">
            <div>Wyświetla wysuwaną zakładkę zdefiniowaną przez administratora sklepu.</div>
          </td></tr>                  
        
          <tr class="SledzeniePozycja">
            <td>
              <label>Włącz zakładkę Indywidualną nr <?php echo $nr; ?>:</label>
            </td>
            <td>
              <?php
              echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['ZAKLADKA_' . strtoupper((string)$nazwa) . '_WLACZONA']['1'], $parametr['ZAKLADKA_' . strtoupper((string)$nazwa) . '_WLACZONA']['0'], 'zakladka_' . $nazwa . '_wlaczona', $parametr['ZAKLADKA_' . strtoupper((string)$nazwa) . '_WLACZONA']['2'], '', $parametr['ZAKLADKA_' . strtoupper((string)$nazwa) . '_WLACZONA']['3'] );
              ?>
            </td>
          </tr>
          
          <tr class="SledzeniePozycja">
            <td>
              <label class="required" for="zakladka_<?php echo $nazwa; ?>_ikona">Obrazek wysuwanej zakładki:</label>
            </td>
            <td>
              <?php
              echo '<input type="text" id="zakladka_' . $nazwa . '_ikona" name="zakladka_' . $nazwa . '_ikona" value="'.$parametr['ZAKLADKA_' . strtoupper((string)$nazwa) . '_IKONA']['0'].'" size="53" class="obrazek" ondblclick="openFileBrowser(\'zakladka_' . $nazwa . '_ikona\',\'\',\'' . KATALOG_ZDJEC . '\')" autocomplete="off" /><em class="TipIkona"><b>Kliknij dwukrotnie w pole obok żeby otworzyć okno przeglądarki zdjęć</b></em>';                        
              ?>
              <span class="usun_zdjecie TipChmurka" data-foto="zakladka_<?php echo $nazwa; ?>_ikona"><b>Kliknij w ikonę żeby usunąć przypisane zdjęcie</b></span>
              <span class="PrzegladarkaZdjec TipChmurka" onclick="openFileBrowser('zakladka_<?php echo $nazwa; ?>_ikona','','<?php echo KATALOG_ZDJEC; ?>')"><b>Kliknij żeby otworzyć okno przeglądarki zdjęć</b></span>
              
              <div id="divzakladka_<?php echo $nazwa; ?>_ikona" style="padding-top:10px;display:none">
                <span id="fozakladka_<?php echo $nazwa; ?>_ikona">
                    <span class="zdjecie_tbl">
                        <img src="obrazki/_loader_small.gif" alt="" />
                    </span>
                </span> 

                <?php if (!empty($parametr['ZAKLADKA_' . strtoupper((string)$nazwa) . '_IKONA']['0'])) { ?>
                
                <script>          
                pokaz_obrazek_ajax('zakladka_<?php echo $nazwa; ?>_ikona', '<?php echo $parametr['ZAKLADKA_' . strtoupper((string)$nazwa) . '_IKONA']['0']; ?>')
                </script> 
                
                <?php } ?>   
                
              </div> 
          
            </td>
          </tr>  

          <tr class="SledzeniePozycja">
            <td>
              <label>Treść wysuwanej zakładki:</label>
            </td>
            <td>
            
              <script>
              $(document).ready(function() {
                  ckedit('zakladka_<?php echo $nazwa; ?>_tresc','95%','150','','Source');
              });
              </script>                       
            
              <?php
              echo '<textarea name="zakladka_' . $nazwa . '_tresc" id="zakladka_' . $nazwa . '_tresc" cols="70" rows="5">'.$parametr['ZAKLADKA_' . strtoupper((string)$nazwa) . '_TRESC']['0'].'</textarea>';                        
              ?>
              
              <span class="maleInfo">W treści zakładki nie można umieszczać kodu języka Javascript. Dozwolony jest wyłącznie kod Html.</span>
              
            </td>
          </tr> 

          <tr class="SledzeniePozycja">
            <td>
              <label class="required" for="zakladka_<?php echo $nazwa; ?>_szerokosc">Szerokość pola z treścią zakładki:</label>
            </td>
            <td>
              <?php
              echo '<input type="text" id="zakladka_' . $nazwa . '_szerokosc" name="zakladka_' . $nazwa . '_szerokosc" value="'.$parametr['ZAKLADKA_' . strtoupper((string)$nazwa) . '_SZEROKOSC']['0'].'" size="5" /> px';                        
              ?>
              <label class="error" style="display:none" for="zakladka_<?php echo $nazwa; ?>_szerokosc">Pole jest wymagane. Wartość może być tylko jako liczba całkowita.</label>
            </td>
          </tr>                     

          <tr class="SledzeniePozycja">
            <td>
              <label for="zakladka_<?php echo $nazwa; ?>_jezyk">Widoczna dla wersji językowej:</label>
            </td>
            <td>
              <?php
              $tablica_jezykow = Funkcje::TablicaJezykow(true);                 
              echo Funkcje::RozwijaneMenu('zakladka_' . $nazwa . '_jezyk',$tablica_jezykow,$parametr['ZAKLADKA_' . strtoupper((string)$nazwa) . '_JEZYK']['0'], 'id="zakladka_' . $nazwa . '_jezyk"');
              unset($tablica_jezykow);
              ?>                         
            </td>
          </tr>                       
          
          <tr class="SledzeniePozycja">
            <td>
              <label>Strona po której ma się wyświetlać zakładka:</label>
            </td>
            <td>
              <?php
              echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['ZAKLADKA_' . strtoupper((string)$nazwa) . '_STRONA']['1'], $parametr['ZAKLADKA_' . strtoupper((string)$nazwa) . '_STRONA']['0'], 'zakladka_' . $nazwa . '_strona', $parametr['ZAKLADKA_' . strtoupper((string)$nazwa) . '_STRONA']['2'], '', $parametr['ZAKLADKA_' . strtoupper((string)$nazwa) . '_STRONA']['3'] );
              ?>
            </td>
          </tr>   
          
          <tr class="SledzeniePozycja">
            <td>
              <label for="zakladka_<?php echo $nazwa;?>_sort">Kolejność wyświetlania na stronie:</label>
            </td>
            <td>
              <?php
              echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['ZAKLADKA_' . strtoupper((string)$nazwa) . '_SORT']['1'], $parametr['ZAKLADKA_' . strtoupper((string)$nazwa) . '_SORT']['0'], 'zakladka_' . $nazwa . '_sort', $parametr['ZAKLADKA_' . strtoupper((string)$nazwa) . '_SORT']['2'], '', $parametr['ZAKLADKA_' . strtoupper((string)$nazwa) . '_SORT']['3'], '', '', 'id="zakladka_' . $nazwa . '_sort"' );
              ?>
            </td>
          </tr>                    

          <tr>
            <td colspan="2">
              <div class="przyciski_dolne">
                <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == $nazwa ? $wynik : '' ); ?>
              </div>
            </td>
          </tr>
        </table>

    </div>
  </form>
  
</div> 