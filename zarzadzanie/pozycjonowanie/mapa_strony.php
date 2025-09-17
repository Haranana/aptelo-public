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

    <div id="naglowek_cont">Generowanie mapy strony XML</div>
    <div id="cont">
    
          <div class="poleForm">
            <div class="naglowek">Generowanie mapy strony</div>
            
                <form action="pozycjonowanie/mapa_strony_utworz.php" method="post" class="cmxform">   

                <div class="poleForm" id="daneXML">
                    <div class="naglowek">Dane o mapie witryny</div>

                    <div id="daneXMLPlik">
                    
                        <?php
                        if (file_exists('../sitemap.xml')) { 
                        ?>
                                
                            <span>Nazwa pliku:</span> <a href="<?php echo ADRES_URL_SKLEPU; ?>/sitemap.xml">sitemap.xml</a> <br />
                            <span>Rozmiar pliku:</span> <?php echo filesize('../sitemap.xml'); ?> bajtów<br />
                            <span>Data utworzenia:</span> <?php echo date('d-m-Y H:i',filemtime('../sitemap.xml')); ?> <br />
                                        
                            <?php
                            $zapis = 'tak';
                            if (!is_writeable('../sitemap.xml')) {
                                $zapis = 'nie';
                            }
                            ?>
                                        
                            <span>Możliwy zapis:</span> <?php echo $zapis; ?>
                            
                            <?php if ($zapis == 'nie') { ?>
                                <div class="ostrzezenie">
                                    UWAGA !! Plik sitemap.xml nie ma uprawnień do zapisu i nie będzie można zapisać danych wynikowych.
                                </div>                        
                            <?php }
                                
                        } else {
                        
                            ?>
                            
                            <div class="ostrzezenie">
                                UWAGA !! Plik sitemap.xml nie istnieje !! Sklep spróbuje utworzyć plik i zapisać w nim dane.
                            </div>                                     
                            
                            <?php
                        
                        }
                        ?>
                        
                        <br />
                        
                        <span class="maleInfo" style="margin-left:0px">Plik XML z mapą strony zapisywany jest w katalogu <b>głównym sklepu</b></span>
                    
                    </div>
                    
                </div>
            
                <div class="TabelSeo">
                    
                    <div class="OknoSeo"> 
                    
                        <div style="font-size:120%;font-weight:bold;padding-bottom:15px;color:#3f5d6b">Zakres generowanych danych</div>

                        <ul>
                            <li><input type="checkbox" value="1" name="kategorie" id="kategorie" checked="checked" /> <label class="OpisFor" for="kategorie">czy uwzględniać <strong>kategorie</strong> przy generowaniu mapy ? </label></li>
                            <li><input type="checkbox" value="1" name="strony_info" id="strony_info" checked="checked" /> <label class="OpisFor" for="strony_info">czy uwzględniać <strong>strony informacyjne</strong> przy generowaniu mapy ? </label></li>
                            <li><input type="checkbox" value="1" name="ankiety" id="ankiety" checked="checked" /> <label class="OpisFor" for="ankiety">czy uwzględniać <strong>ankiety</strong> przy generowaniu mapy ? </label></li>
                            <li><input type="checkbox" value="1" name="galerie" id="galerie" checked="checked" /> <label class="OpisFor" for="galerie">czy uwzględniać <strong>galerie</strong> przy generowaniu mapy ? </label></li>
                            <li><input type="checkbox" value="1" name="formularze" id="formularze" checked="checked" /> <label class="OpisFor" for="formularze">czy uwzględniać <strong>formularze</strong> przy generowaniu mapy ? </label></li>
                            <li><input type="checkbox" value="1" name="producenci" id="producenci" checked="checked" /> <label class="OpisFor" for="producenci">czy uwzględniać <strong>producentów</strong> przy generowaniu mapy ? </label></li>
                            <li><input type="checkbox" value="1" name="aktualnosci" id="aktualnosci" checked="checked" /> <label class="OpisFor" for="aktualnosci">czy uwzględniać <strong>aktualności</strong> przy generowaniu mapy ? </label></li>
                            <li><input type="checkbox" value="1" name="recenzje" id="recenzje" checked="checked" /> <label class="OpisFor" for="recenzje">czy uwzględniać <strong>recenzje</strong> przy generowaniu mapy ? </label></li>
                        </ul>
                        
                        <div style="font-size:120%;font-weight:bold;padding:15px 0 15px 0;color:#3f5d6b">Dodatkowe informacje o grafikach</div>
                        
                        <ul>
                            <li><input type="checkbox" value="1" name="kategorie_zdjecia" id="kategorie_zdjecia" checked="checked" /> <label class="OpisFor" for="kategorie_zdjecia">czy uwzględniać grafiki <strong>kategorii</strong> przy generowaniu mapy ? </label></li>
                            <li><input type="checkbox" value="1" name="produkty_zdjecia" id="produkty_zdjecia" checked="checked" /> <label class="OpisFor" for="produkty_zdjecia">czy uwzględniać grafiki <strong>produktów</strong> przy generowaniu mapy ? </label></li>
                            <li><input type="checkbox" value="1" name="aktualnosci_zdjecia" id="aktualnosci_zdjecia" checked="checked" /> <label class="OpisFor" for="aktualnosci_zdjecia">czy uwzględniać grafiki <strong>aktualności</strong> przy generowaniu mapy ? </label></li>
                            <li><input type="checkbox" value="1" name="producenci_zdjecia" id="producenci_zdjecia" checked="checked" /> <label class="OpisFor" for="producenci_zdjecia">czy uwzględniać grafiki <strong>producentów</strong> przy generowaniu mapy ? </label></li>
                        </ul>                        
                        
                        <div style="padding-top:20px;font-size:120%;font-weight:bold;color:#3f5d6b">Częstotliwość zmiany danych na stronie</div>
                        
                        <div class="maleInfo" style="margin:5px 0 10px 0">Ten parametr jest ignorowany przez wyszukiwarkę Google</div>
                        
                        <div style="padding-bottom:5px">                        
                            <input type="checkbox" value="1" name="changefreq" id="changefreq" /> <label class="OpisFor" for="changefreq">czy generować dane o częstotoliwości zmiany strony ?</label>
                        </div>
                        
                        <script>
                        $(document).ready(function() {                        
                          $('#changefreq').click(function() {
                              if ( $(this).prop('checked') == true ) {
                                   $('.KontChangefreq').stop().slideDown();
                              } else {
                                   $('.KontChangefreq').stop().slideUp();
                              }
                          });
                        });
                        </script>
                        
                        <div class="KontChangefreq" style="display:none">

                            <label style="display:block;padding:10px 0 10px 0;width:auto">Jak często zmienia się zawartość strony ? </label>
                            
                            <select name="index">
                                <option value="always">always – nieustająco</option>
                                <option value="hourly">hourly – co godzinę</option>
                                <option value="daily">daily – raz na dzień</option>
                                <option value="weekly" selected="selected">weekly – co tydzień</option>
                                <option value="monthly">monthly - raz w miesiącu</option>
                                <option value="yearly">yearly – raz na rok</option>
                                <option value="never">never - nigdy się nie zmienia</option>
                            </select>
                            
                        </div>

                    </div>
                    
                    <div class="OknoSeo">
                    
                        <div style="font-size:120%;font-weight:bold;color:#3f5d6b">Ustawienia priorytetów dla podstron sklepu</div>
                        
                        <div class="maleInfo" style="margin:5px 0 5px 0">Ten parametr jest ignorowany przez wyszukiwarkę Google</div>
                        
                        <div style="padding-bottom:15px">                        
                            <input type="checkbox" value="1" name="priority" id="priority" /> <label class="OpisFor" for="priority">czy generować dane o priorytetach dla podstron sklepu ?</label>
                        </div>                    
                        
                        <script>
                        $(document).ready(function() {                        
                          $('#priority').click(function() {
                              if ( $(this).prop('checked') == true ) {
                                   $('.KontPriority').stop().slideDown();
                              } else {
                                   $('.KontPriority').stop().slideUp();
                              }
                          });
                        });
                        </script>
                        
                        <div class="KontPriority" style="display:none">

                            <ul>
                                <li><input type="text" value="0.8" class="ulamek" name="priorytet_produkty" size="4" /> priorytet dla <strong>produktów</strong></li>
                            </ul>
                            
                            <ul>                        
                                <li><input type="checkbox" value="1" name="automat" id="automat" checked="checked" /> <label class="OpisFor" for="automat">czy sklep sam ma obliczyć priorytet dla produktów w zależności od wielkości sprzedaży</label></li>
                            </ul>
                            
                            <br />
                            
                            <ul style="padding-bottom:15px"> 
                                <li><input type="text" value="0.9" class="ulamek" name="priorytet_kategorie" size="4" /> priorytet dla <strong>kategorii</strong></li>  
                                <li><input type="text" value="0.7" class="ulamek" name="priorytet_strony_info" size="4" /> priorytet dla <strong>stron informacyjnych</strong></li>     
                                <li><input type="text" value="0.6" class="ulamek" name="priorytet_ankiety" size="4" /> priorytet dla <strong>ankiet</strong></li>    
                                <li><input type="text" value="0.7" class="ulamek" name="priorytet_galerie" size="4" /> priorytet dla <strong>galerii</strong></li>    
                                <li><input type="text" value="0.7" class="ulamek" name="priorytet_formularze" size="4" /> priorytet dla <strong>formularzy</strong></li>     
                                <li><input type="text" value="0.6" class="ulamek" name="priorytet_producenci" size="4" /> priorytet dla <strong>producentów</strong></li>      
                                <li><input type="text" value="0.5" class="ulamek" name="priorytet_aktualnosci" size="4" /> priorytet dla <strong>aktualności</strong></li>     
                                <li><input type="text" value="0.5" class="ulamek" name="priorytet_recenzje" size="4" /> priorytet dla <strong>recenzji</strong></li>    
                            </ul>
                        
                        </div>
                        
                    </div>

                </div>
                
                <div style="padding:0 0px 30px 30px">
                
                    <input type="submit" class="przyciskBut" style="margin-left:0px" value="Generuj plik XML" />
                    
                </div>
                
                </form>
                
          </div>

    </div>
    
    <?php
    include('stopka.inc.php');    
    
} ?>