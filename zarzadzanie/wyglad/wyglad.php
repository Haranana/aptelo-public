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
    
    <script type="text/javascript" src="javascript/jquery-ui.js"></script>
    <script type="text/javascript" src="wyglad/wyglad.js"></script>
    <script type="text/javascript" src="programy/jscolor/jscolor.js"></script>

    <script> 
    $(function() {     
        // kolejnosc dla lewej kolumny
        $("#wyglad_lewa").sortable({ 
            opacity: 0.6, 
            cursor: 'move', 
            update: function() {
                var order = $(this).sortable("serialize"); 
                $.post("wyglad/wyglad_serialize_box.php?tok=<?php echo Sesje::Token(); ?>", order + '&kolumna=lewa');                    															 
            }								  
        });	
        $("#wyglad_lewa").disableSelection();
        //
        // kolejnosc dla prawej kolumny
        $("#wyglad_prawa").sortable({ 
            opacity: 0.6, 
            cursor: 'move', 
            update: function() {
                var order = $(this).sortable("serialize"); 
                $.post("wyglad/wyglad_serialize_box.php?tok=<?php echo Sesje::Token(); ?>", order + '&kolumna=prawa'); 															 
            }								  
        });	
        $("#wyglad_prawa").disableSelection(); 
        //
        // kolejnosc dla srodkowej kolumny
        $("#wyglad_srodek_srodek").sortable({ 
            opacity: 0.6, 
            cursor: 'move', 
            update: function() {
                var order = $(this).sortable("serialize"); 
                $.post("wyglad/wyglad_serialize_modul.php?tok=<?php echo Sesje::Token(); ?>", order + '&typ=srodek'); 															 
            }								  
        });	
        $("#wyglad_srodek_srodek").disableSelection();
        //
        // kolejnosc dla srodkowej kolumny - czesc gorna
        $("#wyglad_srodek_gora").sortable({ 
            opacity: 0.6, 
            cursor: 'move', 
            update: function() {
                var order = $(this).sortable("serialize"); 
                $.post("wyglad/wyglad_serialize_modul.php?tok=<?php echo Sesje::Token(); ?>", order + '&typ=gora'); 															 
            }								  
        });	
        $("#wyglad_srodek_gora").disableSelection();        
        //
        // kolejnosc dla srodkowej kolumny - czesc dolna
        $("#wyglad_srodek_dol").sortable({ 
            opacity: 0.6, 
            cursor: 'move', 
            update: function() {
                var order = $(this).sortable("serialize"); 
                $.post("wyglad/wyglad_serialize_modul.php?tok=<?php echo Sesje::Token(); ?>", order + '&typ=dol'); 															 
            }								  
        });	
        $("#wyglad_srodek_dol").disableSelection();        
        //        
        // kolejnosc dla gornego menu
        $("#wyglad_gorne_menu").sortable({ 
            opacity: 0.6, 
            cursor: 'move', 
            update: function() {
                var order = $(this).sortable("serialize"); 
                $.post("wyglad/wyglad_serialize_stala.php?tok=<?php echo Sesje::Token(); ?>", order + '&typ=gorne_menu&stala=GORNE_MENU');														 
            }								  
        });	
        $("#wyglad_gorne_menu").disableSelection();   
        //
        // kolejnosc dla dolnego menu
        $("#wyglad_dolne_menu").sortable({ 
            opacity: 0.6, 
            cursor: 'move', 
            update: function() {
                var order = $(this).sortable("serialize"); 
                $.post("wyglad/wyglad_serialize_stala.php?tok=<?php echo Sesje::Token(); ?>", order + '&typ=dolne_menu&stala=DOLNE_MENU');														 
            }								  
        });	
        $("#wyglad_dolne_menu").disableSelection(); 
        //
        // kolejnosc dla szybkiego menu
        $("#wyglad_szybkie_menu").sortable({ 
            opacity: 0.6, 
            cursor: 'move', 
            update: function() {
                var order = $(this).sortable("serialize"); 
                $.post("wyglad/wyglad_serialize_stala.php?tok=<?php echo Sesje::Token(); ?>", order + '&typ=szybkie_menu&stala=SZYBKIE_MENU');														 
            }								  
        });	
        $("#wyglad_szybkie_menu").disableSelection(); 
        //        
        // kolejnosc pierwszej kolmny stopki
        $("#wyglad_stopka_pierwsza").sortable({ 
            opacity: 0.6, 
            cursor: 'move', 
            update: function() {
                var order = $(this).sortable("serialize"); 
                $.post("wyglad/wyglad_serialize_stala.php?tok=<?php echo Sesje::Token(); ?>", order + '&typ=stopka_pierwsza&stala=STOPKA_PIERWSZA');														 
            }								  
        });	
        $("#wyglad_stopka_pierwsza").disableSelection();
        //
        // kolejnosc drugiej kolmny stopki
        $("#wyglad_stopka_druga").sortable({ 
            opacity: 0.6, 
            cursor: 'move', 
            update: function() {
                var order = $(this).sortable("serialize"); 
                $.post("wyglad/wyglad_serialize_stala.php?tok=<?php echo Sesje::Token(); ?>", order + '&typ=stopka_druga&stala=STOPKA_DRUGA');														 
            }								  
        });	
        $("#wyglad_stopka_druga").disableSelection();   
        //
        // kolejnosc trzeciej kolmny stopki
        $("#wyglad_stopka_trzecia").sortable({ 
            opacity: 0.6, 
            cursor: 'move', 
            update: function() {
                var order = $(this).sortable("serialize"); 
                $.post("wyglad/wyglad_serialize_stala.php?tok=<?php echo Sesje::Token(); ?>", order + '&typ=stopka_trzecia&stala=STOPKA_TRZECIA');														 
            }								  
        });	
        $("#wyglad_stopka_trzecia").disableSelection();  
        //
        // kolejnosc czwartej kolmny stopki
        $("#wyglad_stopka_czwarta").sortable({ 
            opacity: 0.6, 
            cursor: 'move', 
            update: function() {
                var order = $(this).sortable("serialize"); 
                $.post("wyglad/wyglad_serialize_stala.php?tok=<?php echo Sesje::Token(); ?>", order + '&typ=stopka_czwarta&stala=STOPKA_CZWARTA');														 
            }								  
        });	
        $("#wyglad_stopka_czwarta").disableSelection();  
        //
        // kolejnosc piatej kolmny stopki
        $("#wyglad_stopka_piata").sortable({ 
            opacity: 0.6, 
            cursor: 'move', 
            update: function() {
                var order = $(this).sortable("serialize"); 
                $.post("wyglad/wyglad_serialize_stala.php?tok=<?php echo Sesje::Token(); ?>", order + '&typ=stopka_piata&stala=STOPKA_PIATA');														 
            }								  
        });	
        $("#wyglad_stopka_piata").disableSelection(); 
    });     
    function infoSzablon(katalog, post) {
        if ( post == 1 ) {
             $.post("wyglad/wyglad_szablon_konfig.php?tok=<?php echo Sesje::Token(); ?>", { dane: $('#Konfig_' + katalog).html() }, function(data) { document.location = '/zarzadzanie/wyglad/wyglad.php' } );		
        }
    }
    function wybierzSzablon(katalog,katalog_sklep) {
        $.colorbox( { html:'<div id="PopUpInfo" style="text-align:center"><b>Czy na pewno chcesz zmienić domyślny szablon ?</b> <br /><br /> Zmiana szablonu spowoduje ustawienia domyślnych ustawień wyglądu dla wybranego szablonu (logo, szerokość sklepu, etc). <br /><br /><div id="PopUpPrzyciski"><span onclick="infoSzablon(\'' + katalog + '\',1);zmienGet(\'' + katalog_sklep + '\',\'DOMYSLNY_SZABLON\')" class="przycisk">TAK - potwierdzam zmianę</span></div></div></div>'});
    }    
    </script>     
    
    <div id="naglowek_cont">Definiowanie ustawień wyglądu sklepu</div>
    
    <div id="infoAjax">wszystkie zmiany są zapisywane w czasie rzeczywistym bezpośrednio do bazy sklepu</div>
    
    <div id="cont">

          <form action="wyglad/wyglad.php" method="post" id="poForm" class="cmxform" enctype="multipart/form-data"> 
          
          <div class="poleForm">
            <div class="naglowek">Ustawienia wyglądu sklepu</div>
            
                <div id="ZakladkiEdycji">
                
                    <div id="LeweZakladki">
                        <a href="javascript:gold_tabs_horiz('0')" class="a_href_info_zakl" id="zakl_link_0">Podstawowe dane</a>   
                        <a href="javascript:gold_tabs_horiz('1')" class="a_href_info_zakl" id="zakl_link_1">Tło sklepu</a>
                        <?php if ( Wyglad::TypSzablonu() == true ) { ?>
                        <a href="javascript:gold_tabs_horiz('15')" class="a_href_info_zakl" id="zakl_link_15">Opis nad nagłówkiem</a>
                        <?php } ?>                                                
                        <a href="javascript:gold_tabs_horiz('2')" class="a_href_info_zakl" id="zakl_link_2">Nagłówek sklepu</a>
                        <?php if ( Wyglad::TypSzablonu() == true ) { ?>
                        <a href="javascript:gold_tabs_horiz('17')" class="a_href_info_zakl" id="zakl_link_17">Szybkie linki</a>
                        <?php } ?>
                        <a href="javascript:gold_tabs_horiz('3')" class="a_href_info_zakl" id="zakl_link_3">Górne menu</a>
                        <a href="javascript:gold_tabs_horiz('4')" class="a_href_info_zakl" id="zakl_link_4">Boxy w kolumnach</a>
                        <a href="javascript:gold_tabs_horiz('5')" class="a_href_info_zakl" id="zakl_link_5">Moduły środkowe</a>
                        <a href="javascript:gold_tabs_horiz('6')" class="a_href_info_zakl" id="zakl_link_6">Dolne menu</a>
                        <a href="javascript:gold_tabs_horiz('7')" class="a_href_info_zakl" id="zakl_link_7">Stopka pierwsza kolumna</a>
                        <a href="javascript:gold_tabs_horiz('8')" class="a_href_info_zakl" id="zakl_link_8">Stopka druga kolumna</a>
                        <a href="javascript:gold_tabs_horiz('9')" class="a_href_info_zakl" id="zakl_link_9">Stopka trzecia kolumna</a>
                        <a href="javascript:gold_tabs_horiz('10')" class="a_href_info_zakl" id="zakl_link_10">Stopka czwarta kolumna</a>
                        <a href="javascript:gold_tabs_horiz('11')" class="a_href_info_zakl" id="zakl_link_11">Stopka piąta kolumna</a>                        
                        <?php if ( Wyglad::TypSzablonu() == true ) { ?>
                        <a href="javascript:gold_tabs_horiz('12')" class="a_href_info_zakl" id="zakl_link_12">Bannery stopki</a>
                        <a href="javascript:gold_tabs_horiz('13')" class="a_href_info_zakl" id="zakl_link_13">Dolny opis stopki</a>
                        <a href="javascript:gold_tabs_horiz('14')" class="a_href_info_zakl" id="zakl_link_14">Dane kontaktowe w stopce</a>                        
                        <?php } ?>
                        <a href="javascript:gold_tabs_horiz('16')" class="a_href_info_zakl" id="zakl_link_16">Dodatkowy kod CSS</a>
                    </div>
 
                    <div id="PrawaStrona">
                    
                        <div id="zakl_id_0" style="display:none">
                        
                            <script>
                            function rodzajSzerokosc(ile) {
                              $('#szerokosc').val(ile);
                              zmienGet(ile,'SZEROKOSC_SKLEPU');
                              $('#jds').html($("input[name=szerokosc_jednostka]:checked").val());
                            }
                            
                            function ustawSzerokosc(wartosc) {
                              $('#blad_szerokosc').html('').hide();
                              //
                              var rodzaj = $("input[name=szerokosc_jednostka]:checked").val();
                              if ( $("input[name=szerokosc_jednostka]:checked").val() == 'px' ) {
                                   if ( parseInt(wartosc) < 500 || parseInt(wartosc) > 1900 ) {
                                        $('#blad_szerokosc').html('Minimalna wartość to 500px, maksymalna to 1900px').show();
                                        setTimeout(function(){ $('#blad_szerokosc').html('').hide(); }, 5000);
                                        wartosc = 1200;
                                   }
                              }
                              if ( $("input[name=szerokosc_jednostka]:checked").val() == 'procent' ) {
                              if ( parseInt(wartosc) < 50 || parseInt(wartosc) > 100 ) {
                                        $('#blad_szerokosc').html('Minimalna wartość to 50%, maksymalna to 100%').show();
                                        setTimeout(function(){ $('#blad_szerokosc').html('').hide(); }, 5000);
                                        wartosc = 90;
                                   }
                              }
                              zmienGet(wartosc,'SZEROKOSC_SKLEPU');
                              $('#szerokosc').val(wartosc);
                            }                     
                            </script>
                            
                            <div class="naglowek" style="margin:10px 20px 15px 20px">Ustawienia podstawowe wyglądu</div>
                            
                            <?php if ( Wyglad::TypSzablonu() == true ) { ?>
                            
                            <p>
                                <label for="szerokosc">Maksymalna szerokość sklepu dla wersji PC:</label>
                                <input type="text" id="szerokosc" name="szerokosc" onchange="ustawSzerokosc(this.value)" value="<?php echo SZEROKOSC_SKLEPU; ?>" size="5" /> &nbsp; <span id="jds"><?php echo SZEROKOSC_SKLEPU_JEDNOSTKA; ?></span>
                                <label id="blad_szerokosc" style="display:none" class="error"></label>
                            </p> 

                            <p>
                                <label>Szerokość sklepu w jednostkach:</label>
                                <input type="radio" value="px" name="szerokosc_jednostka" id="szerokosc_jednostka_piksel" onchange="rodzajSzerokosc(1200); zmienGet(this.value,'SZEROKOSC_SKLEPU_JEDNOSTKA')" <?php echo ((SZEROKOSC_SKLEPU_JEDNOSTKA == 'px') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="szerokosc_jednostka_piksel">w pikselach</label>
                                <input type="radio" value="procent" name="szerokosc_jednostka" id="szerokosc_jednostka_procent" onchange="rodzajSzerokosc(90); zmienGet(this.value,'SZEROKOSC_SKLEPU_JEDNOSTKA')" <?php echo ((SZEROKOSC_SKLEPU_JEDNOSTKA == 'procent') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="szerokosc_jednostka_procent">w procentach</label>
                            </p>                  

                            <?php } ?>
                            
                            <p>
                                <label for="szerokosc_lewa">Szerokość lewej kolumny:</label>
                                <input type="text" name="szerokosc_lewa" id="szerokosc_lewa" value="<?php echo SZEROKOSC_LEWEJ_KOLUMNY; ?>" size="5" /> &nbsp;px
                            </p>
                            
                            <script>
                            $(document).ready(function() {
                                $('#szerokosc_lewa').change(function() {
                                    if ( parseInt($(this).val()) < 150 ) {
                                        alert('Minimalna wartość to 150 px');
                                    } else {
                                        zmienGet($(this).val(),'SZEROKOSC_LEWEJ_KOLUMNY');
                                    }
                                }); 
                            }); 
                            </script>                               

                            <p>
                                <label for="szerokosc_prawa">Szerokość prawej kolumny:</label>
                                <input type="text" name="szerokosc_prawa" id="szerokosc_prawa" value="<?php echo SZEROKOSC_PRAWEJ_KOLUMNY; ?>" size="5" /> &nbsp;px
                            </p> 
                            
                            <script>
                            $(document).ready(function() {
                                $('#szerokosc_prawa').change(function() {
                                    if ( parseInt($(this).val()) < 150 ) {
                                        alert('Minimalna wartość to 150 px');
                                    } else {
                                        zmienGet($(this).val(),'SZEROKOSC_PRAWEJ_KOLUMNY');
                                    }
                                }); 
                            }); 
                            </script>                             
                            
                            <table id="WyborFavicon">
                              <tr>
                                <td><label>Ikonka w pasku przeglądarki:</label></td>
                                <td>
                                  
                                  <input type="file" name="WybranyPlik" id="WybranyPlik" />
                                  <label class="przyciskNon" for="WybranyPlik">Wybierz plik ikony</label>

                                  <?php if (file_exists('../favicon.ico')) { ?>
                                  <span id="UsunIkonke" class="TipChmurka"><b>Usuń ikonkę</b></span>
                                  <?php } ?>
                                  
                                  <span id="favicon">
                                    <?php if (file_exists('../favicon.ico')) { ?>
                                        <img src="../favicon.ico" alt="" />
                                    <?php } ?>
                                  </span> 
                                  
                                </td>
                              </tr>
                            </table>    

                            <div id="ladowanie" style="display:none;margin-left:25px"><img src="obrazki/_loader.gif" alt="przetwarzanie..." /></div>
                            
                            <p>
                                <label>Włączona lewa kolumna:</label>
                                <input type="radio" value="tak" name="lewa_kol" id="lewa_kolumna_tak" onchange="zmienGet(this.value,'CZY_WLACZONA_LEWA_KOLUMNA')" <?php echo ((CZY_WLACZONA_LEWA_KOLUMNA == 'tak') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="lewa_kolumna_tak">tak</label>
                                <input type="radio" value="nie" name="lewa_kol" id="lewa_kolumna_nie" onchange="zmienGet(this.value,'CZY_WLACZONA_LEWA_KOLUMNA')" <?php echo ((CZY_WLACZONA_LEWA_KOLUMNA == 'nie') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="lewa_kolumna_nie">nie</label>
                            </p>  
                            
                            <p>
                                <label>Czy lewa kolumna z boxami ma się wyświetlać tylko na podstronach (nie będzie widoczna na stronie głównej) ?</label>
                                <input type="radio" value="tak" name="lewa_kol_wszedzie" id="lewa_kolumna_wszedzie_tak" onchange="zmienGet(this.value,'CZY_WLACZONA_LEWA_WSZEDZIE')" <?php echo ((CZY_WLACZONA_LEWA_WSZEDZIE == 'tak') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="lewa_kolumna_wszedzie_tak">tak</label>
                                <input type="radio" value="nie" name="lewa_kol_wszedzie" id="lewa_kolumna_wszedzie_nie" onchange="zmienGet(this.value,'CZY_WLACZONA_LEWA_WSZEDZIE')" <?php echo ((CZY_WLACZONA_LEWA_WSZEDZIE == 'nie') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="lewa_kolumna_wszedzie_nie">nie</label>
                            </p>                            

                            <p>
                                <label>Włączona prawa kolumna:</label>
                                <input type="radio" value="tak" name="prawa_kol" id="prawa_kolumna_tak" onchange="zmienGet(this.value,'CZY_WLACZONA_PRAWA_KOLUMNA')" <?php echo ((CZY_WLACZONA_PRAWA_KOLUMNA == 'tak') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="prawa_kolumna_tak">tak</label>
                                <input type="radio" value="nie" name="prawa_kol" id="prawa_kolumna_nie" onchange="zmienGet(this.value,'CZY_WLACZONA_PRAWA_KOLUMNA')" <?php echo ((CZY_WLACZONA_PRAWA_KOLUMNA == 'nie') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="prawa_kolumna_nie">nie</label>
                            </p>

                            <p>
                                <label>Czy prawa kolumna z boxami ma się wyświetlać tylko na podstronach (nie będzie widoczna na stronie głównej) ?</label>
                                <input type="radio" value="tak" name="prawa_kol_wszedzie" id="prawa_kolumna_wszedzie_tak" onchange="zmienGet(this.value,'CZY_WLACZONA_PRAWA_WSZEDZIE')" <?php echo ((CZY_WLACZONA_PRAWA_WSZEDZIE == 'tak') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="prawa_kolumna_wszedzie_tak">tak</label>
                                <input type="radio" value="nie" name="prawa_kol_wszedzie" id="prawa_kolumna_wszedzie_nie" onchange="zmienGet(this.value,'CZY_WLACZONA_PRAWA_WSZEDZIE')" <?php echo ((CZY_WLACZONA_PRAWA_WSZEDZIE == 'nie') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="prawa_kolumna_wszedzie_nie">nie</label>
                            </p> 

                            <table class="WyborStrony">
                                <tr><td><label>Wybierz strony na których mają się NIE wyświetlać kolumny z boxami:<em class="TipIkona"><b>Wybór stron umożliwia wyłączenie kolumn z boxami na wybranych podstronach np logowania dzięki na urządzeniach mobilnych strona jest czytelniejsza</b></em></label></td>
                                <td>
                                    <div>
                                    <?php
                                    $ZapisaneStrony = explode(';', (string)STRONY_KOLUMNY_BOX);
                                    //
                                    foreach ( Funkcje::TablicaPodstronSklepu() as $Strona => $Nazwa ) {
                                        //
                                        echo '<input type="checkbox" value="' . $Strona . '" name="strony[]" id="' . $Strona . '" ' . ((in_array((string)$Strona, $ZapisaneStrony)) ? 'checked="checked"' : '') . ' /><label class="OpisFor" for="' . $Strona . '">' . $Nazwa . '</label><br />';
                                        //
                                    }
                                    //
                                    unset($ZapisaneStrony);
                                    ?>
                                    </div>
                                </td></tr>
                            </table>
                            
                            <br /><br />

                            <?php
                            $dir = opendir('../szablony/');

                            $nrSzablonu = 1;
                            $szablony = array();
                            
                            while (false !== ($katalog = readdir($dir))) {
                              
                                if (!is_file($katalog) && $katalog != '.' && $katalog != '..' && is_dir('../szablony/' . $katalog . '/_podglad')) {
                                  
                                    if ( strpos((string)$katalog, '.rwd.v') > -1 ) {
                                         $szablony[] = $katalog;
                                    }
                                  
                                }
                              
                            }
                            
                            closedir($dir);
                            
                            if ( count($szablony) > 0 ) {
                                                            
                                echo '<div class="DomyslnySzablon"><span>Szablony sklepu V2</span></div>';

                                echo '<div class="SzablonyWybor">';

                                natcasesort($szablony);
                                                   
                                foreach ( $szablony as $katalog ) {
                                    
                                    $img = '../szablony/' . $katalog . '/_podglad/screen.jpg';
                                    if (file_exists($img)) {
                                        echo '<div class="PodgladSzablonu" title="Ustaw szablon jako domyślny" onclick="wybierzSzablon(\''. str_replace('.', '_', (string)$katalog) .'\',\'' . $katalog . '\')"><div class="ImgPodgladSzablonu"><img src="' . $img . '" alt="' . $katalog . '" />';
                                      } else {
                                        echo '<div class="PodgladSzablonu" title="Ustaw szablon jako domyślny" onclick="wybierzSzablon(\''. str_replace('.', '_', (string)$katalog) .'\',\'' . $katalog . '\')"><div class="ImgPodgladSzablonu"><div class="PodgladBrak">Brak podglądu ...</div>';
                                    }
                                    unset($img);
                                    
                                    // plik opisu szablonu
                                    $opis = '../szablony/' . $katalog . '/_podglad/opis.tpo';
                                    if (file_exists($opis)) {
                                        echo '<div class="OpisSzablonu"><strong>Opis szablonu</strong>';
                                        echo nl2br(file_get_contents($opis)); 
                                        echo '</div>';
                                    }
                                    unset($opis);

                                    // dodatkowa konfiguracja
                                    $konfg = '../szablony/' . $katalog . '/_podglad/konfiguracja.dat';
                                    echo '<div style="display:none" id="Konfig_' . str_replace('.', '_', (string)$katalog) . '">';
                                    if (file_exists($konfg)) {
                                        echo strip_tags(file_get_contents((string)$konfg)); 
                                    }
                                    echo '</div>';
                                    unset($konfg);  
                                    
                                    echo '<div class="NazwaSzablonu' . ((DOMYSLNY_SZABLON == $katalog) ? ' WybranySzablon' : '') . '"><b>' . $katalog . '</b></div>';

                                    echo '</div>';

                                    echo '</div>';
                                    
                                    $nrSzablonu++;

                                }
                                
                                unset($szablony);
                                
                                echo '</div>';
                                
                            }
                            ?>
                            
                            <div class="cl"></div>

                            <?php
                            $dir = opendir('../szablony/');

                            $nrSzablonu = 1;
                            $szablony = array();
                            
                            while (false !== ($katalog = readdir($dir))) {
                              
                                if (!is_file($katalog) && $katalog != '.' && $katalog != '..' && is_dir('../szablony/' . $katalog . '/_podglad')) {
                                  
                                    if ( strpos((string)$katalog, '.rwd.v') == false ) {
                                         $szablony[] = $katalog;
                                    }
                                  
                                }
                              
                            }
                            
                            closedir($dir);
                            
                            if ( count($szablony) > 0 ) {
                                                            
                                echo '<div class="DomyslnySzablon"><span>Szablony sklepu (poprzednia wersja)</span></div>';

                                echo '<div class="SzablonyWybor">';

                                natcasesort($szablony);
                                                   
                                foreach ( $szablony as $katalog ) {
                                    
                                    $img = '../szablony/' . $katalog . '/_podglad/screen.jpg';
                                    if (file_exists($img)) {
                                        echo '<div class="PodgladSzablonu" title="Ustaw szablon jako domyślny" onclick="wybierzSzablon(\''. str_replace('.', '_', (string)$katalog) .'\',\'' . $katalog . '\')"><div class="ImgPodgladSzablonu"><img src="' . $img . '" alt="' . $katalog . '" />';
                                      } else {
                                        echo '<div class="PodgladSzablonu" title="Ustaw szablon jako domyślny" onclick="wybierzSzablon(\''. str_replace('.', '_', (string)$katalog) .'\',\'' . $katalog . '\')"><div class="ImgPodgladSzablonu"><div class="PodgladBrak">Brak podglądu ...</div>';
                                    }
                                    unset($img);
                                    
                                    // plik opisu szablonu
                                    $opis = '../szablony/' . $katalog . '/_podglad/opis.tpo';
                                    if (file_exists($opis)) {
                                        echo '<div class="OpisSzablonu"><strong>Opis szablonu</strong>';
                                        echo nl2br(file_get_contents($opis)); 
                                        echo '</div>';
                                    }
                                    unset($opis);

                                    // dodatkowa konfiguracja
                                    $konfg = '../szablony/' . $katalog . '/_podglad/konfiguracja.dat';
                                    echo '<div style="display:none" id="Konfig_' . str_replace('.', '_', (string)$katalog) . '">';
                                    if (file_exists($konfg)) {
                                        echo strip_tags(file_get_contents((string)$konfg)); 
                                    }
                                    echo '</div>';
                                    unset($konfg);  
                                    
                                    echo '<div class="NazwaSzablonu' . ((DOMYSLNY_SZABLON == $katalog) ? ' WybranySzablon' : '') . '"><b>' . $katalog . '</b></div>';

                                    echo '</div>';

                                    echo '</div>';
                                    
                                    $nrSzablonu++;

                                }
                                
                                unset($szablony);
                                
                                echo '</div>';
                                
                            }
                            ?>             

                            <div class="cl"></div>
                            
                        </div>
                            

                        <div id="zakl_id_1" style="display:none;">
                        
                            <div class="naglowek" style="margin:10px 20px 15px 20px">Ustawienia tła sklepu</div>

                            <p>
                                <label>Tło zewnętrzne sklepu:</label>
                                <input type="radio" value="1" name="tlo_sklepu" id="tlo_sklepu_jednolity" onclick="zmien_tlo(1)" <?php echo ((TLO_SKLEPU_RODZAJ == 'kolor') ? 'checked="checked"' : ''); ?> /> <label class="OpisFor" for="tlo_sklepu_jednolity">jednolity kolor</label>
                                <input type="radio" value="0" name="tlo_sklepu" id="tlo_sklepu_obrazek" onclick="zmien_tlo(2)" <?php echo ((TLO_SKLEPU_RODZAJ == 'obraz') ? 'checked="checked"' : ''); ?> /> <label class="OpisFor" for="tlo_sklepu_obrazek">tło obrazkowe</label>
                            </p>
                            
                            <div id="tlo_1" <?php echo ((TLO_SKLEPU_RODZAJ == 'kolor') ? '' : 'style="display:none"'); ?>>
                            
                                <p>
                                  <label for="color">Kolor:</label>
                                  <input name="kolor" class="color {required:false}" id="color" style="-moz-box-shadow:none" value="<?php echo TLO_SKLEPU; ?>" onchange="zmienGet(this.value,'TLO_SKLEPU')" size="8" />                    
                                </p>
                                
                            </div>
                            
                            <div id="tlo_2" <?php echo ((TLO_SKLEPU_RODZAJ == 'obraz') ? '' : 'style="display:none"'); ?>>
                            
                                <p>
                                  <label for="foto">Ścieżka zdjęcia:</label>           
                                  <input type="text" name="zdjecie" size="95" value="<?php echo TLO_SKLEPU; ?>" class="obrazek" ondblclick="openFileBrowser('foto','TLO_SKLEPU','<?php echo KATALOG_ZDJEC; ?>')" id="foto" autocomplete="off" /><em class="TipIkona"><b>Kliknij dwukrotnie w pole obok żeby otworzyć okno przeglądarki zdjęć</b></em>
                                  <span class="PrzegladarkaZdjec TipChmurka" onclick="openFileBrowser('foto','TLO_SKLEPU','<?php echo KATALOG_ZDJEC; ?>')"><b>Kliknij żeby otworzyć okno przeglądarki zdjęć</b></span>
                                </p> 
                                
                                <p>
                                  <label for="tlo_sklepu_powtarzanie">Powtarzanie i wyrównanie tła:</label>
                                  <?php
                                  $tablica = array( array('id' => 'no-repeat center center', 'text' => 'bez powtarzania wyśrodkowane (w pionie i poziomie)'),
                                                    array('id' => 'no-repeat left center', 'text' => 'bez powtarzania wyrównane do lewej krawędzi w poziomie, wyśrodkowe w pionie'),
                                                    array('id' => 'no-repeat right center', 'text' => 'bez powtarzania wyrównane do prawej krawędzi w poziomie, wyśrodkowe w pionie'),
                                                    array('id' => 'no-repeat top center', 'text' => 'bez powtarzania wyśrodkowe w poziomie, wyrównane do górnej krawędzi w pionie'),
                                                    array('id' => 'no-repeat bottom center', 'text' => 'bez powtarzania wyśrodkowe w poziomie, wyrównane do dolnej krawędzi w pionie'),
                                                    array('id' => 'repeat-x', 'text' => 'w poziomie'),
                                                    array('id' => 'repeat-y', 'text' => 'w pionie'),
                                                    array('id' => 'repeat', 'text' => 'w poziomie i pionie') );
                                                    
                                  echo Funkcje::RozwijaneMenu('tlo_sklepu_powtarzanie', $tablica, TLO_SKLEPU_POWTARZANIE, ' id="tlo_sklepu_powtarzanie" onchange="zmienGet(this.value,\'TLO_SKLEPU_POWTARZANIE\')"');
                                  unset($tablica);
                                  ?>                              
                                </p>        

                                <p>
                                  <label for="tlo_sklepu_przewijanie">Sposób wyświetlania tła:</label>
                                  <?php
                                  $tablica = array( array('id' => 'scroll', 'text' => 'tła przewijane razem z oknem przeglądarki'),
                                                    array('id' => 'fixed', 'text' => 'tło nieruchome względem okna przeglądarki') );
                                                    
                                  echo Funkcje::RozwijaneMenu('tlo_sklepu_przewijanie', $tablica, TLO_SKLEPU_FIXED, ' id="tlo_sklepu_przewijanie" onchange="zmienGet(this.value,\'TLO_SKLEPU_FIXED\')"');
                                  unset($tablica);
                                  ?>                              
                                </p>                                 

                            </div>
                            
                        </div>   


                        <div id="zakl_id_2" style="display:none;">
                        
                            <div class="naglowek" style="margin:10px 20px 15px 20px">Ustawienia nagłówka sklepu</div>

                            <p>
                                <label>Nagłówek sklepu:</label>
                                <input type="radio" value="1" name="naglowek_sklepu" id="naglowek_kod" onclick="zmien_naglowek(1)" <?php echo ((NAGLOWEK_RODZAJ == 'kod') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="naglowek_kod">jako kod</label>
                                <input type="radio" value="0" name="naglowek_sklepu" id="naglowek_obrazek" onclick="zmien_naglowek(2)" <?php echo ((NAGLOWEK_RODZAJ == 'obraz') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="naglowek_obrazek">obrazek</label>
                            </p>
                            
                            <div id="naglowek_1" <?php echo ((NAGLOWEK_RODZAJ == 'kod') ? '' : 'style="display:none"'); ?>>
                                <p>
                                  <label for="kod_naglowek">Kod:</label>
                                  <textarea name="kod_naglowek" id="kod_naglowek" onchange="zmienGet(this.value,'NAGLOWEK')" rows="15" cols="90"><?php echo NAGLOWEK; ?></textarea>
                                </p>
                            </div>
                            
                            <div id="naglowek_2" <?php echo ((NAGLOWEK_RODZAJ == 'obraz') ? '' : 'style="display:none"'); ?>>
                            
                                <div class="naglowek" style="margin:10px 20px 15px 20px">Nagłówek dla dużych rozdzielczości ekranu (wersja na komputer)</div>
                                
                                <p>
                                  <label for="foto_naglowek">Ścieżka zdjęcia:</label>           
                                  <input type="text" name="zdjecie_naglowek" size="95" value="<?php echo NAGLOWEK; ?>" class="obrazek" ondblclick="openFileBrowser('foto_naglowek','NAGLOWEK','<?php echo KATALOG_ZDJEC; ?>')" id="foto_naglowek" autocomplete="off" /><em class="TipIkona"><b>Kliknij dwukrotnie w pole obok żeby otworzyć okno przeglądarki zdjęć</b></em>
                                  <span class="UsunPole TipChmurka" style="margin-top:-3px" onclick="usunNaglowek(1)"><b>Kliknij w ikonę żeby usunąć przypisane zdjęcie</b></span>
                                  <span class="PrzegladarkaZdjec TipChmurka" style="margin-top:-3px" onclick="openFileBrowser('foto_naglowek','NAGLOWEK','<?php echo KATALOG_ZDJEC; ?>')"><b>Kliknij żeby otworzyć okno przeglądarki zdjęć</b></span>
                                </p>      
                                
                                <div class="maleInfo" style="margin-left:25px">Powyższy nagłówek będzie wyświetlany zarówno dla szablonów standardowych jak i RWD</div>
                                
                                <div class="naglowek" style="margin:10px 20px 15px 20px">Nagłówek dla małych rozdzielczości ekranu (wersja na urządzenia mobilne)</div>

                                <p>
                                  <label for="foto_naglowek">Ścieżka zdjęcia:</label>           
                                  <input type="text" name="zdjecie_naglowek_rwd_mobilny" size="95" value="<?php echo NAGLOWEK_RWD_MOBILNY; ?>" class="obrazek" ondblclick="openFileBrowser('foto_naglowek_rwd_mobilny','NAGLOWEK_RWD_MOBILNY','<?php echo KATALOG_ZDJEC; ?>')" id="foto_naglowek_rwd_mobilny" autocomplete="off" /><em class="TipIkona"><b>Kliknij dwukrotnie w pole obok żeby otworzyć okno przeglądarki zdjęć</b></em>
                                  <span class="UsunPole TipChmurka" style="margin-top:-3px" onclick="usunNaglowek(2)"><b>Kliknij w ikonę żeby usunąć przypisane zdjęcie</b></span>
                                  <span class="PrzegladarkaZdjec TipChmurka" style="margin-top:-3px" onclick="openFileBrowser('foto_naglowek_rwd_mobilny','NAGLOWEK_RWD_MOBILNY','<?php echo KATALOG_ZDJEC; ?>')"><b>Kliknij żeby otworzyć okno przeglądarki zdjęć</b></span>
                                </p>                                  
                                
                                <div class="maleInfo" style="margin-left:25px">Powyższy nagłówek będzie wyświetlany tylko dla szablonów RWD przy małych rozdzielczościach ekranu - zalecamy użycie pliku w formacie PNG z przeźroczystym tłem</div>
                                
                                <?php if ( Wyglad::TypSzablonu() == true ) { ?>
                                     
                                    <div class="naglowek" style="margin:10px 20px 15px 20px">Nagłówek dla ciemnej wersji (kontrastu)</div>

                                    <p>
                                      <label for="foto_naglowek_rwd_kontrast">Ścieżka zdjęcia:</label>           
                                      <input type="text" name="zdjecie_naglowek_rwd_kontrast" size="95" value="<?php echo NAGLOWEK_RWD_KONTRAST; ?>" class="obrazek" ondblclick="openFileBrowser('foto_naglowek_rwd_kontrast','NAGLOWEK_RWD_KONTRAST','<?php echo KATALOG_ZDJEC; ?>')" id="foto_naglowek_rwd_kontrast" autocomplete="off" /><em class="TipIkona"><b>Kliknij dwukrotnie w pole obok żeby otworzyć okno przeglądarki zdjęć</b></em>
                                      <span class="UsunPole TipChmurka" style="margin-top:-3px" onclick="usunNaglowek(3)"><b>Kliknij w ikonę żeby usunąć przypisane zdjęcie</b></span>
                                      <span class="PrzegladarkaZdjec TipChmurka" style="margin-top:-3px" onclick="openFileBrowser('foto_naglowek_rwd_kontrast','NAGLOWEK_RWD_KONTRAST','<?php echo KATALOG_ZDJEC; ?>')"><b>Kliknij żeby otworzyć okno przeglądarki zdjęć</b></span>
                                    </p>                                  
                                    
                                    <div class="maleInfo" style="margin-left:25px">Nagłówek będzie wyświetlany tylko przy przełączeniu na tryb ciemny - zalecamy użycie pliku w formacie PNG z przeźroczystym tłem</div>
  
                                <?php } ?>
                                
                                <?php if ( Wyglad::TypSzablonu() == true ) { ?>
                                  
                                    <br />
                                    
                                    <div class="naglowek" style="margin:10px 20px 15px 20px">Dodatkowe ustawienia nagłówka <small>(w części szablonów mogą być niedostępne)</small></div>
                                    
                                    <p>
                                      <label>Czy w nagłówku wyświetlać ikonę porównywania produktów ?</label>
                                      <input type="radio" value="tak" name="naglowek_porownywarka" id="naglowek_porownywarka_tak" onchange="zmienGet(this.value,'NAGLOWEK_POROWNYWARKA')" <?php echo ((NAGLOWEK_POROWNYWARKA == 'tak') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="naglowek_porownywarka_tak">tak</label>
                                      <input type="radio" value="nie" name="naglowek_porownywarka" id="naglowek_porownywarka_nie" onchange="zmienGet(this.value,'NAGLOWEK_POROWNYWARKA')" <?php echo ((NAGLOWEK_POROWNYWARKA == 'nie') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="naglowek_porownywarka_nie">nie</label>
                                    </p> 

                                    <p>
                                      <label>Czy w nagłówku wyświetlać dane kontaktowe ?</label>
                                      <input type="radio" value="tak" name="naglowek_kontakt" id="naglowek_kontakt_tak" onchange="zmienGet(this.value,'NAGLOWEK_KONTAKT')" <?php echo ((NAGLOWEK_KONTAKT == 'tak') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="naglowek_kontakt_tak">tak</label>
                                      <input type="radio" value="nie" name="naglowek_kontakt" id="naglowek_kontakt_nie" onchange="zmienGet(this.value,'NAGLOWEK_KONTAKT')" <?php echo ((NAGLOWEK_KONTAKT == 'nie') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="naglowek_kontakt_nie">nie</label>
                                    </p>                                      
                                
                                    <p>
                                      <label>Czy w nagłówku wyświetlać zmianę języka ?</label>
                                      <input type="radio" value="tak" name="naglowek_jezyk" id="naglowek_jezyk_tak" onchange="zmienGet(this.value,'NAGLOWEK_JEZYK')" <?php echo ((NAGLOWEK_JEZYK == 'tak') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="naglowek_jezyk_tak">tak</label>
                                      <input type="radio" value="nie" name="naglowek_jezyk" id="naglowek_jezyk_nie" onchange="zmienGet(this.value,'NAGLOWEK_JEZYK')" <?php echo ((NAGLOWEK_JEZYK == 'nie') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="naglowek_jezyk_nie">nie</label>
                                    </p>  
                                    
                                    <p>
                                      <label>Czy w nagłówku wyświetlać zmianę waluty ?</label>
                                      <input type="radio" value="tak" name="naglowek_waluta" id="naglowek_waluta_tak" onchange="zmienGet(this.value,'NAGLOWEK_WALUTA')" <?php echo ((NAGLOWEK_WALUTA == 'tak') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="naglowek_waluta_tak">tak</label>
                                      <input type="radio" value="nie" name="naglowek_waluta" id="naglowek_waluta_nie" onchange="zmienGet(this.value,'NAGLOWEK_WALUTA')" <?php echo ((NAGLOWEK_WALUTA == 'nie') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="naglowek_waluta_nie">nie</label>
                                    </p> 
                                    
                                    <p>
                                      <label>Czy w nagłówku wyświetlać ikonki portali społecznościowych ?</label>
                                      <input type="radio" value="tak" name="naglowek_portale" id="naglowek_portale_tak" onchange="zmienGet(this.value,'NAGLOWEK_PORTALE')" <?php echo ((NAGLOWEK_PORTALE == 'tak') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="naglowek_portale_tak">tak</label>
                                      <input type="radio" value="nie" name="naglowek_portale" id="naglowek_portale_nie" onchange="zmienGet(this.value,'NAGLOWEK_PORTALE')" <?php echo ((NAGLOWEK_PORTALE == 'nie') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="naglowek_portale_nie">nie</label>
                                    </p>                                     
                                    
                                    <p>
                                      <label>Czy ikonki portali społecznościowych wyświetlać w wersji mobilnej ?</label>
                                      <input type="radio" value="tak" name="naglowek_portale_mobile" id="naglowek_portale_mobile_tak" onchange="zmienGet(this.value,'NAGLOWEK_PORTALE_MOBILE')" <?php echo ((NAGLOWEK_PORTALE_MOBILE == 'tak') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="naglowek_portale_mobile_tak">tak</label>
                                      <input type="radio" value="nie" name="naglowek_portale_mobile" id="naglowek_portale_mobile_nie" onchange="zmienGet(this.value,'NAGLOWEK_PORTALE_MOBILE')" <?php echo ((NAGLOWEK_PORTALE_MOBILE == 'nie') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="naglowek_portale_mobile_nie">nie</label>
                                    </p>   
                                    
                                <?php } ?>
                                
                            </div>
                            
                        </div>         

                        
                        <div id="zakl_id_3" style="display:none;">
                        
                            <div class="WygladTabela">
                            
                                <div class="naglowek">Linki w górnym menu</div>

                                <div id="wyglad_gorne_menu" class="WygladGorneMenu">
                                
                                    <?php
                                    if (GORNE_MENU != '') {
                                        //
                                        $pozycje_menu = explode(',', (string)GORNE_MENU);
                                        
                                        $konfig_menu = array();
                                        
                                        if ( strpos((string)MENU_PODKATEGORIE, '{') > -1 ) {
                                             //
                                             $podTmp = @unserialize(MENU_PODKATEGORIE);
                                             //
                                             if ( is_array($podTmp) ) {
                                                  //
                                                  $konfig_menu = $podTmp;
                                                  //
                                             }
                                             //
                                             unset($podTmp);
                                             //
                                        }

                                        for ($x = 0, $c = count($pozycje_menu); $x < $c; $x++) {
                                        
                                            $nazwaDowyswietlania = '';
                                            $edycjaElementu = '';
                                        
                                            $strona = explode(';', (string)$pozycje_menu[$x]);

                                            switch ($strona[0]) {
                                                case "strona":
                                                    $sqls = $db->open_query("select * from pages p, pages_description pd where p.pages_id = pd.pages_id and pd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."' and p.pages_id = '".(int)$strona[1]."'");
                                                    if ((int)$db->ile_rekordow($sqls) > 0) { 
                                                        $infs = $sqls->fetch_assoc();
                                                        $nazwaDowyswietlania = Wyglad::KonfiguracjaPozycjiGornegoMenu($konfig_menu, $infs['pages_title'], '( link do strony informacyjnej )', $infs['pages_id'], 'strona', 'StronaInfo');
                                                        $edycjaElementu = '<a class="TipChmurka" href="strony_informacyjne/strony_informacyjne_edytuj.php?id_poz=' . $infs['pages_id'] . '&amp;zakladka=3"><b>Edytuj pozycję</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>';
                                                        $idDoDiva = $strona[1].'strona';
                                                        unset($infs);
                                                    }
                                                    $db->close_query($sqls);                                                     
                                                    break;
                                                case "galeria":
                                                    $sqls = $db->open_query("select * from gallery p, gallery_description pd where p.id_gallery = pd.id_gallery and pd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."' and p.id_gallery = '".(int)$strona[1]."'");
                                                    if ((int)$db->ile_rekordow($sqls) > 0) { 
                                                        $infs = $sqls->fetch_assoc();
                                                        $nazwaDowyswietlania = Wyglad::KonfiguracjaPozycjiGornegoMenu($konfig_menu, $infs['gallery_name'], '( link do galerii )', $infs['id_gallery'], 'galeria', 'Galeria');
                                                        $edycjaElementu = '<a class="TipChmurka" href="galerie/galerie_edytuj.php?id_poz=' . $infs['id_gallery'] . '&amp;zakladka=3"><b>Edytuj pozycję</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>';
                                                        $idDoDiva = $strona[1].'galeria';
                                                        unset($infs);
                                                    }
                                                    $db->close_query($sqls);                                                     
                                                    break; 
                                                case "formularz":
                                                    $sqls = $db->open_query("select * from form p, form_description pd where p.id_form = pd.id_form and pd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."' and p.id_form = '".(int)$strona[1]."'");
                                                    if ((int)$db->ile_rekordow($sqls) > 0) { 
                                                        $infs = $sqls->fetch_assoc();
                                                        $nazwaDowyswietlania = Wyglad::KonfiguracjaPozycjiGornegoMenu($konfig_menu, $infs['form_name'], '( link do formularza )', $infs['id_form'], 'formularz', 'Formularz');
                                                        $edycjaElementu = '<a class="TipChmurka" href="formularze/formularze_edytuj.php?id_poz=' . $infs['id_form'] . '&amp;zakladka=3"><b>Edytuj pozycję</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>';
                                                        $idDoDiva = $strona[1].'formularz';
                                                        unset($infs);
                                                    }
                                                    $db->close_query($sqls);                                                     
                                                    break; 
                                                case "kategoria":
                                                    $sqls = $db->open_query("select * from newsdesk_categories n, newsdesk_categories_description nd where n.categories_id = nd.categories_id and nd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."' and n.categories_id = '".(int)$strona[1]."'");
                                                    if ((int)$db->ile_rekordow($sqls) > 0) { 
                                                        $infs = $sqls->fetch_assoc();
                                                        //
                                                        $nazwa_kat = $infs['categories_name'];
                                                        //
                                                        if ( $infs['parent_id'] > 0 ) {
                                                             //
                                                             foreach ( BoxyModuly::TablicaKategoriiAktualnosci() as $tmp ) {
                                                                  //
                                                                  if ( $infs['parent_id'] == $tmp['categories_id'] ) {
                                                                       //
                                                                       $nazwa_kat = $tmp['categories_name'] . ' / ' . $nazwa_kat;
                                                                       //
                                                                  }
                                                                  //
                                                             } 
                                                             //
                                                        } 
                                                        //                                   
                                                        $nazwaDowyswietlania = Wyglad::KonfiguracjaPozycjiGornegoMenu($konfig_menu, $nazwa_kat, '( link do kategorii aktualności )', $infs['categories_id'], 'kategoria', 'ArtykulKategoria');                                                    
                                                        $edycjaElementu = '<a class="TipChmurka" href="aktualnosci/aktualnosci_kategorie_edytuj.php?kat_id=' . $infs['categories_id'] . '&amp;zakladka=3"><b>Edytuj pozycję</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>';
                                                        unset($nazwa_kat);
                                                        $idDoDiva = $strona[1].'kategoria';
                                                        unset($infs);
                                                    }
                                                    $db->close_query($sqls);                                                     
                                                    break; 
                                                case "artykul":
                                                    $sqls = $db->open_query("select * from newsdesk n, newsdesk_description nd where n.newsdesk_id = nd.newsdesk_id and nd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."' and n.newsdesk_id = '".(int)$strona[1]."'");
                                                    if ((int)$db->ile_rekordow($sqls) > 0) { 
                                                        $infs = $sqls->fetch_assoc();
                                                        $nazwaDowyswietlania = Wyglad::KonfiguracjaPozycjiGornegoMenu($konfig_menu, $infs['newsdesk_article_name'], '( link do aktualności )', $infs['newsdesk_id'], 'artykul', 'Artykul');  
                                                        $edycjaElementu = '<a class="TipChmurka" href="aktualnosci/aktualnosci_edytuj.php?id_poz=' . $infs['newsdesk_id'] . '&amp;zakladka=3"><b>Edytuj pozycję</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>';
                                                        $idDoDiva = $strona[1].'artykul';
                                                        unset($infs);
                                                    }
                                                    $db->close_query($sqls);                                                     
                                                    break; 
                                                case "kategproduktow":
                                                    $sqls = $db->open_query("select * from categories c, categories_description cd where c.categories_id = cd.categories_id and c.parent_id = '0' and cd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."' and c.categories_id = '".(int)$strona[1]."'");
                                                    if ((int)$db->ile_rekordow($sqls) > 0) { 
                                                        $infs = $sqls->fetch_assoc();
                                                        $nazwaDowyswietlania = Wyglad::KonfiguracjaPozycjiGornegoMenu($konfig_menu, $infs['categories_name'], '(  link do kategorii produktów )', $infs['categories_id'], 'kategproduktow', 'ProduktKategoria');  
                                                        $edycjaElementu = '<a class="TipChmurka" href="kategorie/kategorie_edytuj.php?id_poz=' . $infs['categories_id'] . '&amp;zakladka=3"><b>Edytuj pozycję</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>';
                                                        $idDoDiva = $strona[1].'kategproduktow';
                                                        unset($infs);
                                                    }
                                                    $db->close_query($sqls);                                                     
                                                    break;                                                     
                                                case "grupainfo":
                                                    $sqls = $db->open_query("select pg.pages_group_id,
                                                                                    pg.pages_group_code,
                                                                                    pg.pages_group_title,
                                                                                    pgd.pages_group_name
                                                                               from pages_group pg left join pages_group_description pgd on pg.pages_group_id = pgd.pages_group_id and pgd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'
                                                                              where pg.pages_group_id  = '".(int)$strona[1]."'");                                                    
                                                    if ((int)$db->ile_rekordow($sqls) > 0) { 
                                                        $infs = $sqls->fetch_assoc();
                                                        $nazwaDowyswietlania = Wyglad::KonfiguracjaPozycjiGornegoMenu($konfig_menu, $infs['pages_group_name'], '( okno rozwijane stron informacyjnych z grupy: ' . $infs['pages_group_code'] . ' )', (int)$strona[1], 'grupainfo');
                                                        $edycjaElementu = '<a class="TipChmurka" href="strony_informacyjne/strony_informacyjne_grupy_edytuj.php?id_poz=' . $infs['pages_group_id'] . '&amp;zakladka=3"><b>Edytuj pozycję</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>';
                                                        $idDoDiva = $strona[1].'grupainfo';
                                                        unset($infs);                                                    
                                                    }
                                                    $db->close_query($sqls); 
                                                    break;                                                    
                                                case "artkategorie":
                                                    $sqls = $db->open_query("select * from newsdesk_categories n, newsdesk_categories_description nd where n.categories_id = nd.categories_id and nd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."' and n.categories_id = '".(int)$strona[1]."'");
                                                    if ((int)$db->ile_rekordow($sqls) > 0) { 
                                                        $infs = $sqls->fetch_assoc();
                                                        //
                                                        $nazwa_kat = $infs['categories_name'];
                                                        //
                                                        if ( $infs['parent_id'] > 0 ) {
                                                             //
                                                             foreach ( BoxyModuly::TablicaKategoriiAktualnosci() as $tmp ) {
                                                                  //
                                                                  if ( $infs['parent_id'] == $tmp['categories_id'] ) {
                                                                       //
                                                                       $nazwa_kat = $tmp['categories_name'] . ' / ' . $nazwa_kat;
                                                                       //
                                                                  }
                                                                  //
                                                             } 
                                                             //
                                                        } 
                                                        //
                                                        $nazwaDowyswietlania = Wyglad::KonfiguracjaPozycjiGornegoMenu($konfig_menu, $nazwa_kat, '( okno rozwijane z artykułami z kategorii aktualności: ' . $nazwa_kat . ' )', (int)$strona[1], 'artkategorie');
                                                        unset($nazwa_kat);
                                                        $edycjaElementu = '<a class="TipChmurka" href="aktualnosci/aktualnosci_kategorie_edytuj.php?kat_id=' . $infs['categories_id'] . '&amp;zakladka=3"><b>Edytuj pozycję</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>';
                                                        $idDoDiva = $strona[1].'artkategorie';                                                        
                                                        unset($infs);
                                                    }
                                                    $db->close_query($sqls); 
                                                    break; 
                                                case "prodkategorie":
                                                    $sqls = $db->open_query("select * from categories c, categories_description cd where c.categories_id = cd.categories_id and c.parent_id = '0' and cd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."' and c.categories_id = '".(int)$strona[1]."'");
                                                    if ((int)$db->ile_rekordow($sqls) > 0) { 
                                                        $infs = $sqls->fetch_assoc();                                                    
                                                        $nazwaDowyswietlania = Wyglad::KonfiguracjaPozycjiGornegoMenu($konfig_menu, $infs['categories_name'], '( okno rozwijane z podkategoriami z kategorii produktów: ' . $infs['categories_name'] . ' )', $infs['categories_id'], 'katprod');
                                                        $edycjaElementu = '<a class="TipChmurka" href="kategorie/kategorie_edytuj.php?id_poz=' . $infs['categories_id'] . '&amp;zakladka=3"><b>Edytuj pozycję</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>';
                                                        $idDoDiva = (int)$strona[1].'prodkategorie';
                                                        unset($infs);
                                                    }
                                                    $db->close_query($sqls);                                                     
                                                    break;    
                                                case "linkbezposredni":
                                                    $rozk = explode('adreslinku', (string)$strona[1]);
                                                    $link_rozk = base64_decode(str_replace(array('ukosnik','rowna'), array('/','='), (string)$rozk[1]));
                                                    $tab_linku = unserialize($link_rozk);
                                                    $nazwaDowyswietlania = Wyglad::KonfiguracjaPozycjiGornegoMenu($konfig_menu, $tab_linku['jezyk_' . $_SESSION['domyslny_jezyk']['id']], '( link zewnętrzny bezpośredni: ' . $tab_linku['linkbezposredni'] . ' )', $strona[1], 'linkbezposredni', 'LinkZew');
                                                    $edycjaElementu = '<em class="TipChmurka" style="float:right;" onclick="inne_edytuj(\'' . $strona[1] . '\',\'gorne_menu\',\'linkbezposredni\')"><b>Edytuj pozycję</b><img src="obrazki/edytuj.png" alt="Edytuj" /></em>';
                                                    $idDoDiva = $strona[1];
                                                    unset($rozk, $tab_linku, $link_rozk);
                                                    break;
                                                case "pozycjabannery":
                                                    $rozk = explode('tylkografiki', (string)$strona[1]);
                                                    $link_rozk = base64_decode(str_replace(array('ukosnik','rowna'), array('/','='), (string)$rozk[1]));
                                                    $tab_linku = unserialize($link_rozk);
                                                    $nazwaDowyswietlania = Wyglad::KonfiguracjaPozycjiGornegoMenu($konfig_menu, $tab_linku['jezyk_bannery_' . $_SESSION['domyslny_jezyk']['id']], '( pozycja z bannerami '. ((Wyglad::TypSzablonu() == true) ? '' : ' - dostępne tylko dla szablonów V2) ') . ')', $strona[1], 'pozycjabannery', 'PozycjaGrafiki');
                                                    $edycjaElementu = '<em class="TipChmurka" style="float:right;" onclick="inne_edytuj(\'' . $strona[1] . '\',\'gorne_menu\',\'pozycjabannery\')"><b>Edytuj pozycję</b><img src="obrazki/edytuj.png" alt="Edytuj" /></em>';
                                                    $idDoDiva = $strona[1];
                                                    unset($rozk, $tab_linku, $link_rozk);
                                                    break;       
                                                case "dowolnatresc":
                                                    $sqls = $db->open_query("select ac.id_any_content,
                                                                                    acd.any_content_name
                                                                               from any_content ac left join any_content_description acd on ac.id_any_content = acd.id_any_content and acd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'
                                                                              where ac.id_any_content = '".(int)$strona[1]."'");                                                    
                                                    if ((int)$db->ile_rekordow($sqls) > 0) { 
                                                        $infs = $sqls->fetch_assoc();
                                                        $nazwaDowyswietlania = Wyglad::KonfiguracjaPozycjiGornegoMenu($konfig_menu, $infs['any_content_name'], '( okno rozwijane z dowolną treścią '. ((Wyglad::TypSzablonu() == true) ? '' : ' - dostępne tylko dla szablonów V2) ') . ')', (int)$strona[1], 'dowolnatresc');
                                                        $edycjaElementu = '<a class="TipChmurka" href="dowolne_tresci/dowolne_tresci_edytuj.php?id_poz=' . $infs['id_any_content'] . '&amp;zakladka=3"><b>Edytuj pozycję</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>';
                                                        $idDoDiva = $strona[1].'dowolnatresc';
                                                        unset($infs);                                                    
                                                    }
                                                    $db->close_query($sqls); 
                                                    break;                                                      
                                                case "linkwszystkiekategorie":
                                                    $rozk = explode('nazwapozycji', (string)$strona[1]);
                                                    $link_rozk = base64_decode(str_replace(array('ukosnik','rowna'), array('/','='), (string)$rozk[1]));
                                                    $tab_linku = unserialize($link_rozk);
                                                    $nazwaDowyswietlania = Wyglad::KonfiguracjaPozycjiGornegoMenu($konfig_menu, $tab_linku['menu_kategorie_jezyk_' . $_SESSION['domyslny_jezyk']['id']], '( link do wszystkich kategorii sklepu )', '99999998', 'katprod');
                                                    $edycjaElementu = '<em class="TipChmurka" style="float:right;" onclick="inne_edytuj(\'' . $strona[1] . '\',\'gorne_menu\',\'linkwszystkiekategorie\')"><b>Edytuj pozycję</b><img src="obrazki/edytuj.png" alt="Edytuj" /></em>';
                                                    $idDoDiva = $strona[1];
                                                    unset($rozk, $tab_linku, $link_rozk);
                                                    break; 
                                                case "linkwszyscyproducenci":
                                                    $rozk = explode('nazwapozycji', (string)$strona[1]);
                                                    $link_rozk = base64_decode(str_replace(array('ukosnik','rowna'), array('/','='), (string)$rozk[1]));
                                                    $tab_linku = unserialize($link_rozk);
                                                    $nazwaDowyswietlania = Wyglad::KonfiguracjaPozycjiGornegoMenu($konfig_menu, $tab_linku['menu_producenci_jezyk_' . $_SESSION['domyslny_jezyk']['id']], '( link do wszystkich producentów w sklepie )', '99999999', 'producenci');
                                                    $edycjaElementu = '<em class="TipChmurka" style="float:right;" onclick="inne_edytuj(\'' . $strona[1] . '\',\'gorne_menu\',\'linkwszyscyproducenci\')"><b>Edytuj pozycję</b><img src="obrazki/edytuj.png" alt="Edytuj" /></em>';
                                                    $idDoDiva = $strona[1];
                                                    unset($rozk, $tab_linku, $link_rozk);
                                                    break;                                                     
                                            }
                                            ?>
                                            
                                            <?php if ( $nazwaDowyswietlania != '' ) { ?>
                                            <div class="Stala" id="gorne_menu_<?php echo $idDoDiva; ?>">
                                                <?php echo $nazwaDowyswietlania; ?>
                                                <em class="TipChmurka" style="float:right;"><b>Skasuj</b><img class="Skasuj" onclick="ssk('<?php echo $idDoDiva; ?>','gorne_menu')" src="obrazki/kasuj.png" alt="Skasuj" /></em>
                                                <?php echo $edycjaElementu; ?>
                                                <em class="TipChmurka" style="float:right;"><b>W dół</b><img class="Dol" onclick="przesun('<?php echo $idDoDiva; ?>','gorne_menu','dol')" src="obrazki/strzalka_dol.png" alt="W dół" /></em>
                                                <em class="TipChmurka" style="float:right;"><b>W górę</b><img class="Gora" onclick="przesun('<?php echo $idDoDiva; ?>','gorne_menu','gora')" src="obrazki/strzalka_gora.png" alt="W górę" /></em>                                                
                                            </div>                        
                                            <?php } ?>
                                            <?php
                                            
                                            unset($nazwaDowyswietlania, $idDoDiva, $edycjaElementu); 
                                        }
                                        //
                                    } else {
                                        
                                      echo '<p style="padding:10px">Brak pozycji ...</p>';
                                      
                                    }
                                    ?>
                                    
                                </div>
                                
                                <div class="DodajGorneMenu">
                                    <span class="dodaj" onclick="dodaj_stala('gorne_menu')" style="cursor:pointer">dodaj nową pozycję menu</span>
                                </div>

                                <div class="Legenda">
                                    <span class="Rozwijane"> menu rozwijane</span>
                                    <span class="StronaInfo"> strona informacyjna</span>
                                    <span class="LinkZew"> link zewnętrzny</span>
                                    <span class="PozycjaGrafiki"> pozycja z bannerami</span>
                                    <span class="Galeria"> galeria</span>
                                    <span class="Formularz"> formularz</span>
                                    <span class="ArtykulKategoria"> kategoria artykułów</span>
                                    <span class="ProduktKategoria"> kategoria produktów</span>
                                    <span class="Artykul"> artykuł</span>                                   
                                </div> 

                            </div>
                            
                        </div>                         
                        
                        
                        <div id="zakl_id_4" style="display:none;">
                        
                            <div class="WygladTabela">
                            
                                <div class="InfoKolumnaLewa">
                                
                                    <div class="naglowek">Boxy w lewej kolumnie</div>

                                    <div id="wyglad_lewa" class="WygladLeweBoxy">

                                        <?php
                                        // pobieranie boxow do lewej kolumny
                                        $warunek = ' and (p.box_v2 = 0 or p.box_v2 = 2)';
                                        
                                        if ( Wyglad::TypSzablonu() == true ) {
                                             //
                                             $warunek = ' and (p.box_v2 = 1 or p.box_v2 = 2)';
                                             //
                                        }  
                                        
                                        if ( isset($_SESSION['programista']) && $_SESSION['programista'] == '1' ) {
                                             //
                                             $warunek = ' and (p.box_v2 = 0 or p.box_v2 = 1 or p.box_v2 = 2)';
                                             //
                                        }                                         
                                    
                                        $boxy = $db->open_query("select * from theme_box p, theme_box_description pd where p.box_id = pd.box_id and pd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."' " . $warunek . " and p.box_column = 'lewa' and p.box_status = '1' order by p.box_sort, pd.box_title");
                                        
                                        unset($warunek);
                                        
                                        if ((int)$db->ile_rekordow($boxy) == 0) { 
                                        
                                            echo '<p style="padding:10px">Brak pozycji ...</p>';
                                        
                                        } else {
                                    
                                            while ($info = $boxy->fetch_assoc()) {
                                                ?>
                                                <div class="Box" id="box_<?php echo $info['box_id']; ?>">
                                                    <em class="TipChmurka" style="float:right"><b>Przenieś do prawej kolumny</b><img class="Strzalka" onclick="ple(<?php echo $info['box_id']; ?>,'lewa')" src="obrazki/strzalka_prawa.png" alt="Przenieś do prawej kolumny" /></em>
                                                    <em class="TipChmurka" style="float:right"><b>Skasuj</b><img class="Skasuj" onclick="psk(<?php echo $info['box_id']; ?>)" src="obrazki/kasuj.png" alt="Skasuj" /></em>
                                                    <a class="TipChmurka" style="float:right" href="wyglad/boxy_edytuj.php?id_poz=<?php echo $info['box_id']; ?>&amp;zakladka=4"><b>Edytuj konfigurację boxu</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>                                                
                                                    <em class="TipChmurka" style="float:right;"><b>W dół</b><img class="Dol" onclick="przesun_bm('box_<?php echo $info['box_id']; ?>','lewa','lewa','dol')" src="obrazki/strzalka_dol.png" alt="W dół" /></em>
                                                    <em class="TipChmurka" style="float:right;"><b>W górę</b><img class="Gora" onclick="przesun_bm('box_<?php echo $info['box_id']; ?>','lewa','lewa','gora')" src="obrazki/strzalka_gora.png" alt="W górę" /></em>                                                                                                                                                                
                                                    <?php
                                                    // plik php czy strona informacyjna
                                                    if ($info['box_type'] == 'plik') { 
                                                      echo '<span class="iplik">'.$info['box_title'].'<br /><strong>' . $info['box_description'] . BoxyModuly::PolozenieBoxu($info['box_localization']) . '</strong></span>'; 
                                                    }
                                                    if ($info['box_type'] == 'java') { 
                                                      echo '<span class="ikodjava">'.$info['box_title'].'<br /><strong>' . $info['box_description'] . BoxyModuly::PolozenieBoxu($info['box_localization']) . '</strong></span>';               
                                                    }
                                                    if ($info['box_type'] == 'strona') { 
                                                      echo '<span class="istrona">'.$info['box_title'].'<br /><strong>' . $info['box_description'] . BoxyModuly::PolozenieBoxu($info['box_localization']) . '</strong></span>';   
                                                    }     
                                                    if ($info['box_type'] == 'txt') { 
                                                      echo '<span class="itxt">'.$info['box_title'].'<br /><strong>' . $info['box_description'] . BoxyModuly::PolozenieBoxu($info['box_localization']) . '</strong></span>';   
                                                    }                                                        
                                                    ?>
                                                </div>                        
                                                <?php
                                            }
                                            
                                            unset($info);
                                            
                                        }
                                        
                                        $db->close_query($boxy);                                        
                                        ?>
                                        
                                    </div>
                                    
                                    <div class="DodajBox">
                                        <span class="dodaj" onclick="dodaj_box('lewa','dodaj_box_lewa')" style="cursor:pointer">dodaj nowy box</span><span id="dodaj_box_lewa"></span>
                                    </div>                         

                                </div>
                                
                                <div class="InfoKolumnaPrawa">
                                
                                    <div class="naglowek">Boxy w prawej kolumnie</div>
                                
                                    <div id="wyglad_prawa" class="WygladPraweBoxy">

                                        <?php
                                        // pobieranie boxow do lewej kolumny
                                        $warunek = ' and (p.box_v2 = 0 or p.box_v2 = 2)';
                                        
                                        if ( Wyglad::TypSzablonu() == true ) {
                                             //
                                             $warunek = ' and (p.box_v2 = 1 or p.box_v2 = 2)';
                                             //
                                        } 
                                        
                                        if ( isset($_SESSION['programista']) && $_SESSION['programista'] == '1' ) {
                                             //
                                             $warunek = ' and (p.box_v2 = 0 or p.box_v2 = 1 or p.box_v2 = 2)';
                                             //
                                        }                                                                                 
                                        
                                        $boxy = $db->open_query("select * from theme_box p, theme_box_description pd where p.box_id = pd.box_id and pd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."' " . $warunek . " and p.box_column = 'prawa' and p.box_status = '1' order by p.box_sort, pd.box_title");
                                        
                                        unset($warunek);
                                        
                                        if ((int)$db->ile_rekordow($boxy) == 0) { 
                                        
                                            echo '<p style="padding:10px">Brak pozycji ...</p>';
                                        
                                        } else {
                                                                            
                                            while ($info = $boxy->fetch_assoc()) {
                                                ?>
                                                <div class="Box" id="box_<?php echo $info['box_id']; ?>" style="text-align:right">
                                                    <em class="TipChmurka" style="float:left"><b>Przenieś do lewej kolumny</b><img class="Strzalka" onclick="ple(<?php echo $info['box_id']; ?>,'prawa')" src="obrazki/strzalka_lewa.png" alt="Przenieś do lewej kolumny" /></em>
                                                    <em class="TipChmurka" style="float:left"><b>Skasuj</b><img class="Skasuj" onclick="psk(<?php echo $info['box_id']; ?>)" src="obrazki/kasuj.png" alt="Skasuj" /></em>
                                                    <a class="TipChmurka" style="float:left" href="wyglad/boxy_edytuj.php?id_poz=<?php echo $info['box_id']; ?>&amp;zakladka=4"><b>Edytuj konfigurację boxu</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>
                                                    <em class="TipChmurka" style="float:left;"><b>W dół</b><img class="Dol" onclick="przesun_bm('box_<?php echo $info['box_id']; ?>','prawa','prawa','dol')" src="obrazki/strzalka_dol.png" alt="W dół" /></em>
                                                    <em class="TipChmurka" style="float:left;"><b>W górę</b><img class="Gora" onclick="przesun_bm('box_<?php echo $info['box_id']; ?>','prawa','prawa','gora')" src="obrazki/strzalka_gora.png" alt="W górę" /></em>                                                                                                                                                                
                                                    <?php
                                                    // plik php czy strona informacyjna
                                                    if ($info['box_type'] == 'plik') { 
                                                      echo '<span class="rplik">'.$info['box_title'].'<br /><strong>' . $info['box_description'] . BoxyModuly::PolozenieBoxu($info['box_localization']) . '</strong></span>'; 
                                                    }
                                                    if ($info['box_type'] == 'java') { 
                                                      echo '<span class="rkodjava">'.$info['box_title'].'<br /><strong>' . $info['box_description'] . BoxyModuly::PolozenieBoxu($info['box_localization']) . '</strong></span>';               
                                                    }
                                                    if ($info['box_type'] == 'strona') { 
                                                      echo '<span class="rstrona">'.$info['box_title'].'<br /><strong>' . $info['box_description'] . BoxyModuly::PolozenieBoxu($info['box_localization']) . '</strong></span>';   
                                                    }
                                                    if ($info['box_type'] == 'txt') { 
                                                      echo '<span class="rtxt">'.$info['box_title'].'<br /><strong>' . $info['box_description'] . BoxyModuly::PolozenieBoxu($info['box_localization']) . '</strong></span>';   
                                                    }                                                    
                                                    ?>
                                                </div>                        
                                                <?php
                                            }
                                            
                                            unset($info);
                                            
                                        }
                                        
                                        $db->close_query($boxy);                                        
                                        ?>                    

                                    </div>
                                    
                                    <div class="DodajBox">
                                        <span class="dodaj" onclick="dodaj_box('prawa','dodaj_box_prawa')" style="cursor:pointer">dodaj nowy box</span><span id="dodaj_box_prawa"></span>
                                    </div>                         
                                    
                                </div>
                                
                                <div class="cl"></div>
                                
                                <div class="Legenda">                                    
                                    <span class="Txt"> box zawiera dowolny tekst</span>
                                    <span class="Plik"> box jest plikiem php</span>
                                    <span class="Strona"> box wyświetla zawartość strony informacyjnej</span>
                                    <span class="KodJava"> box wyświetla wynik działania skryptu</span>
                                </div>                                
                                
                            </div>
                            
                        </div>     

                        
                        <div id="zakl_id_5" style="display:none;">
                        
                            <div class="WygladTabela">
                            
                                <div class="naglowek">Moduły wyświetlane nad częścią główną sklepu</div>

                                <div id="wyglad_srodek_gora" class="WygladSrodekGora">

                                    <?php
                                    // pobieranie modulow
                                    $warunek = ' and (p.modul_v2 = 0 or p.modul_v2 = 2)';
                                    
                                    if ( Wyglad::TypSzablonu() == true ) {
                                         //
                                         $warunek = ' and (p.modul_v2 = 1 or p.modul_v2 = 2)';
                                         //
                                    }  
                                    
                                    if ( isset($_SESSION['programista']) && $_SESSION['programista'] == '1' ) {
                                         //
                                         $warunek = ' and (p.modul_v2 = 0 or p.modul_v2 = 1 or p.modul_v2 = 2)';
                                         //
                                    }                                    
                                    
                                    $moduly = $db->open_query("select * from theme_modules p, theme_modules_description pd where p.modul_id = pd.modul_id and pd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."' " . $warunek . " and p.modul_status = '1' and p.modul_position = 'gora' order by p.modul_sort, pd.modul_title");
                                    
                                    unset($warunek);
                                    
                                    if ((int)$db->ile_rekordow($moduly) == 0) { 
                                    
                                        echo '<p style="padding:10px">Brak pozycji ...</p>';
                                    
                                    } else {
                                    
                                        while ($info = $moduly->fetch_assoc()) {
                                            ?>
                                            <div class="Box" id="modul_<?php echo $info['modul_id']; ?>">
                                                <em class="TipChmurka" style="float:right;"><b>Skasuj</b><img class="Skasuj" onclick="msk(<?php echo $info['modul_id']; ?>,'gora')" src="obrazki/kasuj.png" alt="Skasuj" /></em>
                                                <?php if ($info['modul_type'] != 'kreator') { ?>                                                        
                                                <a class="TipChmurka" href="wyglad/srodek_edytuj.php?id_poz=<?php echo $info['modul_id']; ?>&amp;zakladka=5"><b>Edytuj konfigurację modułu</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>                                                    
                                                <?php } else { ?>
                                                <a class="TipChmurka" href="wyglad/srodek_kreator_modulow.php?id_poz=<?php echo $info['modul_id']; ?>&amp;zakladka=5"><b>Edytuj konfigurację modułu</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>                                                    
                                                <?php } ?>
                                                <em class="TipChmurka" style="float:right;"><b>W dół</b><img class="Dol" onclick="przesun_bm('modul_<?php echo $info['modul_id']; ?>','srodek_gora','gora','dol')" src="obrazki/strzalka_dol.png" alt="W dół" /></em>
                                                <em class="TipChmurka" style="float:right;"><b>W górę</b><img class="Gora" onclick="przesun_bm('modul_<?php echo $info['modul_id']; ?>','srodek_gora','gora','gora')" src="obrazki/strzalka_gora.png" alt="W górę" /></em>                                                                                                            
                                                <?php
                                                // plik php czy strona informacyjna
                                                if ($info['modul_type'] == 'plik') { 
                                                  echo '<span class="iplik">'.$info['modul_title'].'<br /><strong>' . $info['modul_description'] . BoxyModuly::PolozenieModulu($info['modul_localization']) . '</strong></span>'; 
                                                }
                                                if ($info['modul_type'] == 'java') { 
                                                  echo '<span class="ikodjava">'.$info['modul_title'].'<br /><strong>' . $info['modul_description'] . BoxyModuly::PolozenieModulu($info['modul_localization']) . '</strong></span>';               
                                                }
                                                if ($info['modul_type'] == 'strona') { 
                                                  echo '<span class="istrona">'.$info['modul_title'].'<br /><strong>' . $info['modul_description'] . BoxyModuly::PolozenieModulu($info['modul_localization']) . '</strong></span>';   
                                                } 
                                                if ($info['modul_type'] == 'txt') { 
                                                  echo '<span class="itxt">'.$info['modul_title'].'<br /><strong>' . $info['modul_description'] . BoxyModuly::PolozenieModulu($info['modul_localization']) . '</strong></span>';   
                                                } 
                                                if ($info['modul_type'] == 'kreator') { 
                                                  echo '<span class="ikreator">'.$info['modul_title'].'<br /><strong>' . $info['modul_description'] . BoxyModuly::PolozenieModulu($info['modul_localization']) . Wyglad::AktywnyKreator() . '</strong></span>';   
                                                }                                                  
                                                ?>
                                            </div>                        
                                            <?php
                                        }
                                        
                                        unset($info);
                                        
                                    }
                                    
                                    $db->close_query($moduly);                                    
                                    ?>
                                    
                                </div>
                                
                                <div class="DodajModul">
                                    <span class="dodaj" onclick="dodaj_modul('gora','dodaj_modul_gora')" style="cursor:pointer">dodaj nowy moduł</span><span id="dodaj_modul_gora"></span>
                                </div>   

                                <br />
                            
                                <div class="naglowek">Moduły w części głównej sklepu</div>
                                
                                <div class="WygladCzescSrodkowa">
                                
                                    <div class="WygladKolumnaBoxu">
                                    
                                        <div class="WygladSrodekBoxy" style="margin-right:20px"></div>
                                        
                                    </div>
                                    
                                    <div class="WygladKolumnaSrodek">

                                        <div id="wyglad_srodek_srodek" class="WygladSrodekSrodek">

                                            <?php
                                            // pobieranie modulow
                                            $warunek = ' and (p.modul_v2 = 0 or p.modul_v2 = 2)';
                                            
                                            if ( Wyglad::TypSzablonu() == true ) {
                                                 //
                                                 $warunek = ' and (p.modul_v2 = 1 or p.modul_v2 = 2)';
                                                 //
                                            }             

                                            if ( isset($_SESSION['programista']) && $_SESSION['programista'] == '1' ) {
                                                 //
                                                 $warunek = ' and (p.modul_v2 = 0 or p.modul_v2 = 1 or p.modul_v2 = 2)';
                                                 //
                                            }                                            
                                            
                                            $moduly = $db->open_query("select * from theme_modules p, theme_modules_description pd where p.modul_id = pd.modul_id and pd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."' " . $warunek . " and p.modul_status = '1' and p.modul_position = 'srodek' order by p.modul_sort, pd.modul_title");
                                            
                                            unset($warunek);
                                            
                                            if ((int)$db->ile_rekordow($moduly) == 0) { 
                                            
                                                echo '<p style="padding:10px">Brak pozycji ...</p>';
                                            
                                            } else {
                                            
                                                while ($info = $moduly->fetch_assoc()) {
                                                    ?>
                                                    <div class="Box" id="modul_<?php echo $info['modul_id']; ?>">
                                                        <em class="TipChmurka" style="float:right;"><b>Skasuj</b><img class="Skasuj" onclick="msk(<?php echo $info['modul_id']; ?>,'srodek')" src="obrazki/kasuj.png" alt="Skasuj" /></em>
                                                        <?php if ($info['modul_type'] != 'kreator') { ?>                                                        
                                                        <a class="TipChmurka" href="wyglad/srodek_edytuj.php?id_poz=<?php echo $info['modul_id']; ?>&amp;zakladka=5"><b>Edytuj konfigurację modułu</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>                                                    
                                                        <?php } else { ?>
                                                        <a class="TipChmurka" href="wyglad/srodek_kreator_modulow.php?id_poz=<?php echo $info['modul_id']; ?>&amp;zakladka=5"><b>Edytuj konfigurację modułu</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>                                                    
                                                        <?php } ?>
                                                        <em class="TipChmurka" style="float:right;"><b>W dół</b><img class="Dol" onclick="przesun_bm('modul_<?php echo $info['modul_id']; ?>','srodek_srodek','srodek','dol')" src="obrazki/strzalka_dol.png" alt="W dół" /></em>
                                                        <em class="TipChmurka" style="float:right;"><b>W górę</b><img class="Gora" onclick="przesun_bm('modul_<?php echo $info['modul_id']; ?>','srodek_srodek','srodek','gora')" src="obrazki/strzalka_gora.png" alt="W górę" /></em>                                                            
                                                        <?php
                                                        // plik php czy strona informacyjna
                                                        if ($info['modul_type'] == 'plik') { 
                                                          echo '<span class="iplik">'.$info['modul_title'].'<br /><strong>' . $info['modul_description'] . BoxyModuly::PolozenieModulu($info['modul_localization']) . '</strong></span>'; 
                                                        }
                                                        if ($info['modul_type'] == 'java') { 
                                                          echo '<span class="ikodjava">'.$info['modul_title'].'<br /><strong>' . $info['modul_description'] . BoxyModuly::PolozenieModulu($info['modul_localization']) . '</strong></span>';               
                                                        }
                                                        if ($info['modul_type'] == 'strona') { 
                                                          echo '<span class="istrona">'.$info['modul_title'].'<br /><strong>' . $info['modul_description'] . BoxyModuly::PolozenieModulu($info['modul_localization']) . '</strong></span>';   
                                                        }  
                                                        if ($info['modul_type'] == 'txt') { 
                                                          echo '<span class="itxt">'.$info['modul_title'].'<br /><strong>' . $info['modul_description'] . BoxyModuly::PolozenieModulu($info['modul_localization']) . '</strong></span>';   
                                                        }       
                                                        if ($info['modul_type'] == 'kreator') { 
                                                          echo '<span class="ikreator">'.$info['modul_title'].'<br /><strong>' . $info['modul_description'] . BoxyModuly::PolozenieModulu($info['modul_localization']) . Wyglad::AktywnyKreator() . '</strong></span>';   
                                                        }                                                         
                                                        ?>
                                                    </div>                        
                                                    <?php
                                                }
                                                
                                                unset($info);
                                                
                                            }
                                            
                                            $db->close_query($moduly);                                            
                                            ?>
                                            
                                        </div>
                                        
                                        <div class="DodajModul">
                                            <span class="dodaj" onclick="dodaj_modul('srodek','dodaj_modul_srodek')" style="cursor:pointer">dodaj nowy moduł</span><span id="dodaj_modul_srodek"></span>
                                        </div>                                        
                                        
                                    </div>
                                    
                                    <div class="WygladKolumnaBoxu">
                                    
                                        <div class="WygladSrodekBoxy" style="margin-left:20px"></div>
                                        
                                    </div>                              
                                
                                </div>
                                
                                <br />
                                
                                <div class="naglowek">Moduły wyświetlane pod częścią główną sklepu</div>

                                <div id="wyglad_srodek_dol" class="WygladSrodekDol">

                                    <?php
                                    // pobieranie modulow
                                    $warunek = ' and (p.modul_v2 = 0 or p.modul_v2 = 2)';
                                    
                                    if ( Wyglad::TypSzablonu() == true ) {
                                         //
                                         $warunek = ' and (p.modul_v2 = 1 or p.modul_v2 = 2)';
                                         //
                                    }  
                                    
                                    if ( isset($_SESSION['programista']) && $_SESSION['programista'] == '1' ) {
                                         //
                                         $warunek = ' and (p.modul_v2 = 0 or p.modul_v2 = 1 or p.modul_v2 = 2)';
                                         //
                                    }                                    
                                            
                                    $moduly = $db->open_query("select * from theme_modules p, theme_modules_description pd where p.modul_id = pd.modul_id and pd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."' " . $warunek . " and p.modul_status = '1' and p.modul_position = 'dol' order by p.modul_sort, pd.modul_title");
                                    
                                    unset($warunek);
                                    
                                    if ((int)$db->ile_rekordow($moduly) == 0) { 
                                    
                                        echo '<p style="padding:10px">Brak pozycji ...</p>';
                                    
                                    } else {
                                    
                                        while ($info = $moduly->fetch_assoc()) {
                                            ?>
                                            <div class="Box" id="modul_<?php echo $info['modul_id']; ?>">
                                                <em class="TipChmurka" style="float:right;"><b>Skasuj</b><img class="Skasuj" onclick="msk(<?php echo $info['modul_id']; ?>,'dol')" src="obrazki/kasuj.png" alt="Skasuj" /></em>
                                                <?php if ($info['modul_type'] != 'kreator') { ?>                                                        
                                                <a class="TipChmurka" href="wyglad/srodek_edytuj.php?id_poz=<?php echo $info['modul_id']; ?>&amp;zakladka=5"><b>Edytuj konfigurację modułu</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>                                                    
                                                <?php } else { ?>
                                                <a class="TipChmurka" href="wyglad/srodek_kreator_modulow.php?id_poz=<?php echo $info['modul_id']; ?>&amp;zakladka=5"><b>Edytuj konfigurację modułu</b><img src="obrazki/edytuj.png" alt="Edytuj" /></a>                                                    
                                                <?php } ?>                                                
                                                <em class="TipChmurka" style="float:right;"><b>W dół</b><img class="Dol" onclick="przesun_bm('modul_<?php echo $info['modul_id']; ?>','srodek_dol','dol','dol')" src="obrazki/strzalka_dol.png" alt="W dół" /></em>
                                                <em class="TipChmurka" style="float:right;"><b>W górę</b><img class="Gora" onclick="przesun_bm('modul_<?php echo $info['modul_id']; ?>','srodek_dol','dol','gora')" src="obrazki/strzalka_gora.png" alt="W górę" /></em>                                                                                                                                                            
                                                <?php
                                                // plik php czy strona informacyjna
                                                if ($info['modul_type'] == 'plik') { 
                                                  echo '<span class="iplik">'.$info['modul_title'].'<br /><strong>' . $info['modul_description'] . BoxyModuly::PolozenieModulu($info['modul_localization']) . '</strong></span>'; 
                                                }
                                                if ($info['modul_type'] == 'java') { 
                                                  echo '<span class="ikodjava">'.$info['modul_title'].'<br /><strong>' . $info['modul_description'] . BoxyModuly::PolozenieModulu($info['modul_localization']) . '</strong></span>';               
                                                }
                                                if ($info['modul_type'] == 'strona') { 
                                                  echo '<span class="istrona">'.$info['modul_title'].'<br /><strong>' . $info['modul_description'] . BoxyModuly::PolozenieModulu($info['modul_localization']) . '</strong></span>';   
                                                }   
                                                if ($info['modul_type'] == 'txt') { 
                                                  echo '<span class="itxt">'.$info['modul_title'].'<br /><strong>' . $info['modul_description'] . BoxyModuly::PolozenieModulu($info['modul_localization']) . '</strong></span>';   
                                                } 
                                                if ($info['modul_type'] == 'kreator') { 
                                                  echo '<span class="ikreator">'.$info['modul_title'].'<br /><strong>' . $info['modul_description'] . BoxyModuly::PolozenieModulu($info['modul_localization']) . Wyglad::AktywnyKreator() . '</strong></span>';   
                                                }                                                 
                                                ?>
                                            </div>                        
                                            <?php
                                        }
                                        
                                        unset($info);
                                        
                                    }
                                    
                                    $db->close_query($moduly);                                   
                                    ?>
                                    
                                </div>
                                
                                <div class="DodajModul">
                                    <span class="dodaj" onclick="dodaj_modul('dol','dodaj_modul_dol')" style="cursor:pointer">dodaj nowy moduł</span><span id="dodaj_modul_dol"></span>
                                </div>                                   

                                <div class="Legenda">
                                    <span class="Kreator">moduł wygenerowany w kreatorze modułów</span>
                                    <span class="Txt"> moduł zawiera dowolny tekst</span>
                                    <span class="Plik"> moduł jest plikiem php</span>
                                    <span class="Strona"> moduł wyświetla zawartość strony informacyjnej</span>
                                    <span class="KodJava"> moduł wyświetla wynik działania skryptu</span>
                                </div>                                 

                            </div>
                            
                        </div>        


                        <div id="zakl_id_6" style="display:none;">
                        
                            <div class="WygladTabela">
                            
                                <div class="naglowek">Linki w dolnym menu</div>

                                <div id="wyglad_dolne_menu" class="WygladDolneMenu">
                                
                                    <?php
                                    $pozycje_menu = explode(',', (string)DOLNE_MENU);
                                    //
                                    if (DOLNE_MENU != '') {
                                        //
                                        for ($x = 0, $c = count($pozycje_menu); $x < $c; $x++) {
                                        
                                            $nr_zakladki = 6;
                                            include('wyglad_menu_pozycje.php');                                                
                                            ?>
                                            
                                            <div class="Stala" id="dolne_menu_<?php echo $idDoDiva; ?>">
                                                <?php echo $nazwaDowyswietlania; ?>
                                                <em class="TipChmurka" style="float:right;"><b>Skasuj</b><img class="Skasuj" onclick="ssk('<?php echo $idDoDiva; ?>','dolne_menu')" src="obrazki/kasuj.png" alt="Skasuj" /></em>
                                                <?php echo $edycjaElementu; ?>
                                                <em class="TipChmurka" style="float:right;"><b>W dół</b><img class="Dol" onclick="przesun('<?php echo $idDoDiva; ?>','dolne_menu','dol')" src="obrazki/strzalka_dol.png" alt="W dół" /></em>
                                                <em class="TipChmurka" style="float:right;"><b>W górę</b><img class="Gora" onclick="przesun('<?php echo $idDoDiva; ?>','dolne_menu','gora')" src="obrazki/strzalka_gora.png" alt="W górę" /></em>                                                                                                
                                            </div>                        
                                            <?php
                                            
                                            unset($nazwaDowyswietlania, $idDoDiva, $edycjaElementu, $nr_zakladki); 
                                        }
                                        //
                                    } else {
                                        
                                      echo '<p style="padding:10px">Brak pozycji ...</p>';
                                      
                                    }
                                    ?>
                                    
                                </div>
                                
                                <div class="DodajDolneMenu">
                                    <span class="dodaj" onclick="dodaj_stala('dolne_menu')" style="cursor:pointer">dodaj nową pozycję menu</span>
                                </div> 

                                <div class="Legenda">
                                    <span class="StronaInfo"> strona informacyjna</span>
                                    <span class="LinkZew"> link zewnętrzny</span>
                                    <span class="Galeria"> galeria</span>
                                    <span class="Formularz"> formularz</span>
                                    <span class="ArtykulKategoria"> kategoria artykułów</span>
                                    <span class="ProduktKategoria"> kategoria produktów</span>
                                    <span class="Artykul"> artykuł</span>         
                                </div>                                 

                            </div>
                            
                        </div>
                        
                        <script>
                        function ckeditStopka(id, zmienna, jezyk) {
                              var editor = CKEDITOR.replace( id, {
                                  filebrowserBrowseUrl : '/zarzadzanie/przegladarka.php?typ=ckedit&tok=' + $('#tok').val(),
                                  filebrowserImageBrowseUrl : '/zarzadzanie/przegladarka.php?typ=ckedit&tok=' + $('#tok').val(),
                                  filebrowserFlashBrowseUrl : '/zarzadzanie/przegladarka.php?typ=ckedit&tok=' + $('#tok').val(),
                                  width: '99%',
                                  height: '150',
                                  filebrowserWindowWidth : '740',
                                  filebrowserWindowHeight : '580',
                                  autoGrow_minHeight : '150',
                                  filebrowserWindowFeatures : 'menubar=no,toolbar=no,minimizable=no,resizable=no,scrollbars=no',
                                  removeButtons: ''
                                }
                              );
                              
                              editor.on( 'change', function( evt ) {  
                                  $.post("wyglad/wyglad_zapisz_stala_jezykowa.php?tok=" + $('#tok').val(), { wart: evt.editor.getData(), stala: zmienna, jezyk: jezyk }, function(data) { });
                              });      

                              editor.on( 'blur', function( evt ) {  
                                  $('#ekr_preloader').css('display','block');
                                  $('#ekr_preloader').stop().fadeOut();
                              });    
                              
                        }    
                        </script>

                        <?php $ile_jezykow = Funkcje::TablicaJezykow(); ?>
                        
                        <div id="zakl_id_7" style="display:none;">
                        
                            <div class="naglowek" style="margin:10px 20px 15px 20px">Ustawienia pierwszej kolumny stopki</div>
                        
                            <?php if ( Wyglad::TypSzablonu() == true ) { ?>
                                                
                            <p>
                                <label>Czy kolumna pierwsza ma być wyświetlana ?</label>
                                <input type="radio" value="tak" name="stopka_kolumna_pierwsza" id="stopka_kolumna_pierwsza_tak" onchange="zmienGet(this.value,'STOPKA_KOLUMNA_PIERWSZA_WLACZONA')" <?php echo ((STOPKA_KOLUMNA_PIERWSZA_WLACZONA == 'tak') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="stopka_kolumna_pierwsza_tak">tak</label>
                                <input type="radio" value="nie" name="stopka_kolumna_pierwsza" id="stopka_kolumna_pierwsza_nie" onchange="zmienGet(this.value,'STOPKA_KOLUMNA_PIERWSZA_WLACZONA')" <?php echo ((STOPKA_KOLUMNA_PIERWSZA_WLACZONA == 'nie') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="stopka_kolumna_pierwsza_nie">nie</label>
                            </p>          

                            <?php } ?>
                        
                            <div class="WygladTabela" style="margin-top:0px">

                                <div class="info_tab">
                                <?php
                                for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                                    echo '<span id="link_'.$w.'" class="a_href_info_tab" onclick="gold_tabs(\''.$w.'\')">'.$ile_jezykow[$w]['text'].'</span>';
                                }                    
                                ?>                   
                                </div>
                                
                                <div style="clear:both"></div>
                                
                                <div class="info_tab_content" style="margin-left:0px; margin-right:0px">
                                    
                                    <?php for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) { ?> 

                                        <div id="info_tab_id_<?php echo $w; ?>" style="display:none;">
                                        
                                            <?php
                                            // pobieranie danych jezykowych
                                            $zapytanie_jezyk = "select distinct * from translate_constant w left join translate_value t on w.translate_constant_id = t.translate_constant_id  where w.translate_constant = 'STOPKA_NAGLOWEK_PIERWSZA' and t.language_id = '" .$ile_jezykow[$w]['id']."'";
                                            $sqls = $db->open_query($zapytanie_jezyk);
                                            $nazwa = $sqls->fetch_assoc();                                              
                                            ?>
                                            
                                            <p>
                                                <label for="nazwa_<?php echo $w; ?>">Nazwa nagłówka stopki:</label>   
                                                <input type="text" onchange="zmienGetJezyk(this.value,'STOPKA_NAGLOWEK_PIERWSZA',<?php echo $ile_jezykow[$w]['id']; ?>)" name="nazwa_<?php echo $w; ?>" id="nazwa_<?php echo $w; ?>" size="45" value="<?php echo $nazwa['translate_value']; ?>" /><em class="TipIkona"><b>Tekst wyświetlany w nagłówku kolumny w stopce</b></em>
                                            </p> 
                                            
                                            <?php
                                            $db->close_query($sqls);
                                            unset($nazwa);
                                            
                                            // pobieranie danych jezykowych
                                            $zapytanie_jezyk = "select distinct * from translate_constant w left join translate_value t on w.translate_constant_id = t.translate_constant_id  where w.translate_constant = 'STOPKA_TEKST_PIERWSZA' and t.language_id = '" .$ile_jezykow[$w]['id']."'";
                                            $sqls = $db->open_query($zapytanie_jezyk);
                                            $tekst = $sqls->fetch_assoc();
                                            ?>
                                            
                                            <p>
                                                <label for="tekst_<?php echo $w; ?>">Tekst nad linkami stopki:</label>   
                                                <textarea onchange="zmienGetJezyk(this.value,'STOPKA_TEKST_PIERWSZA',<?php echo $ile_jezykow[$w]['id']; ?>)" name="tekst_<?php echo $w; ?>" id="tekst_<?php echo $w; ?>_<?php echo $ile_jezykow[$w]['id']; ?>" cols="80" rows="5"><?php echo $tekst['translate_value']; ?></textarea>                                             
                                            </p> 
                                            
                                            <script>
                                            ckeditStopka('tekst_<?php echo $w; ?>_<?php echo $ile_jezykow[$w]['id']; ?>','STOPKA_TEKST_PIERWSZA',<?php echo $ile_jezykow[$w]['id']; ?>);
                                            </script>
                                            
                                            <?php
                                            $db->close_query($sqls);
                                            unset($tekst);
                                            ?>
                                                        
                                        </div>
                                        <?php  


                                    }                    
                                    ?>                      
                                </div> 
                                
                                <div class="naglowek" style="margin-top:20px">Linki w pierwszej kolumnie stopki</div>
                                
                                <div id="wyglad_stopka_pierwsza" class="WygladStopka">
                                
                                    <?php
                                    $pozycje_menu = explode(',', (string)STOPKA_PIERWSZA);
                                    //
                                    if (STOPKA_PIERWSZA != '') {
                                        //
                                        for ($x = 0, $c = count($pozycje_menu); $x < $c; $x++) {
                                        
                                            $nr_zakladki = 7;
                                            include('wyglad_menu_pozycje.php');                                                
                                            ?>
                                            
                                            <div class="Stala" id="stopka_pierwsza_<?php echo $idDoDiva; ?>">
                                                <?php echo $nazwaDowyswietlania; ?>
                                                <em class="TipChmurka" style="float:right;"><b>Skasuj</b><img class="Skasuj" onclick="ssk('<?php echo $idDoDiva; ?>','stopka_pierwsza')" src="obrazki/kasuj.png" alt="Skasuj" /></em>
                                                <?php echo $edycjaElementu; ?>
                                                <em class="TipChmurka" style="float:right;"><b>W dół</b><img class="Dol" onclick="przesun('<?php echo $idDoDiva; ?>','stopka_pierwsza','dol')" src="obrazki/strzalka_dol.png" alt="W dół" /></em>
                                                <em class="TipChmurka" style="float:right;"><b>W górę</b><img class="Gora" onclick="przesun('<?php echo $idDoDiva; ?>','stopka_pierwsza','gora')" src="obrazki/strzalka_gora.png" alt="W górę" /></em>                                                                                                
                                            </div>                        
                                            <?php
                                            
                                            unset($nazwaDowyswietlania, $idDoDiva, $edycjaElementu, $nr_zakladki); 
                                        }
                                        //
                                    } else {
                                        
                                      echo '<p style="padding:10px">Brak pozycji ...</p>';
                                      
                                    }
                                    ?>
                                    
                                </div>
                                
                                <div class="DodajStopka">
                                    <span class="dodaj" onclick="dodaj_stala('stopka_pierwsza')" style="cursor:pointer">dodaj nową pozycję do kolumny</span>
                                </div> 

                                <div class="Legenda">
                                    <span class="StronaInfo"> strona informacyjna</span>
                                    <span class="LinkZew"> link zewnętrzny</span>
                                    <span class="Galeria"> galeria</span>
                                    <span class="Formularz"> formularz</span>
                                    <span class="ArtykulKategoria"> kategoria artykułów</span>
                                    <span class="ProduktKategoria"> kategoria produktów</span>
                                    <span class="Artykul"> artykuł</span>         
                                </div>                                 
                            
                            </div>
                            
                            <script>
                            gold_tabs('0');
                            </script>                             
                            
                        </div>   


                        <div id="zakl_id_8" style="display:none;">
                        
                            <div class="naglowek" style="margin:10px 20px 15px 20px">Ustawienia drugiej kolumny stopki</div>
                        
                            <?php if ( Wyglad::TypSzablonu() == true ) { ?>
                                                
                            <p>
                                <label>Czy kolumna druga ma być wyświetlana ?</label>
                                <input type="radio" value="tak" name="stopka_kolumna_druga" id="stopka_kolumna_druga_tak" onchange="zmienGet(this.value,'STOPKA_KOLUMNA_DRUGA_WLACZONA')" <?php echo ((STOPKA_KOLUMNA_DRUGA_WLACZONA == 'tak') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="stopka_kolumna_druga_tak">tak</label>
                                <input type="radio" value="nie" name="stopka_kolumna_druga" id="stopka_kolumna_druga_nie" onchange="zmienGet(this.value,'STOPKA_KOLUMNA_DRUGA_WLACZONA')" <?php echo ((STOPKA_KOLUMNA_DRUGA_WLACZONA == 'nie') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="stopka_kolumna_druga_nie">nie</label>
                            </p>          

                            <?php } ?>                        
                        
                            <div class="WygladTabela" style="margin-top:0px">
                        
                                <div class="info_tab">
                                <?php
                                for ($w = 100, $cw = count($ile_jezykow); $w < $cw + 100; $w++) {
                                    echo '<span id="link_'.$w.'" class="a_href_info_tab" onclick="gold_tabs(\''.$w.'\')">'.$ile_jezykow[$w-100]['text'].'</span>';
                                }                    
                                ?>                   
                                </div>
                                
                                <div style="clear:both"></div>
                                
                                <div class="info_tab_content" style="margin-left:0px; margin-right:0px">
                                
                                    <?php for ($w = 100, $cw = count($ile_jezykow); $w < $cw + 100; $w++) { ?> 

                                        <div id="info_tab_id_<?php echo $w; ?>" style="display:none;">
                                        
                                            <?php
                                            // pobieranie danych jezykowych
                                            $zapytanie_jezyk = "select distinct * from translate_constant w left join translate_value t on w.translate_constant_id = t.translate_constant_id  where w.translate_constant = 'STOPKA_NAGLOWEK_DRUGA' and t.language_id = '" .$ile_jezykow[$w-100]['id']."'";
                                            $sqls = $db->open_query($zapytanie_jezyk);
                                            $nazwa = $sqls->fetch_assoc();   
                                            ?>
                                        
                                            <p>
                                                <label for="nazwa_<?php echo $w; ?>">Nazwa nagłówka stopki:</label>   
                                                <input type="text" onchange="zmienGetJezyk(this.value,'STOPKA_NAGLOWEK_DRUGA',<?php echo $ile_jezykow[$w-100]['id']; ?>)" name="nazwa_<?php echo $w; ?>" id="nazwa_<?php echo $w; ?>" size="45" value="<?php echo $nazwa['translate_value']; ?>" /><em class="TipIkona"><b>Tekst wyświetlany w nagłówku kolumny w stopce</b></em>
                                            </p>

                                            <?php
                                            $db->close_query($sqls);
                                            unset($nazwa);  

                                            // pobieranie danych jezykowych
                                            $zapytanie_jezyk = "select distinct * from translate_constant w left join translate_value t on w.translate_constant_id = t.translate_constant_id  where w.translate_constant = 'STOPKA_TEKST_DRUGA' and t.language_id = '" .$ile_jezykow[$w-100]['id']."'";
                                            $sqls = $db->open_query($zapytanie_jezyk);
                                            $tekst = $sqls->fetch_assoc();   
                                            ?>                                            

                                            <p>
                                                <label for="tekst_<?php echo $w; ?>">Tekst nad linkami stopki:</label>   
                                                <textarea name="tekst_<?php echo $w; ?>" id="tekst_<?php echo $w; ?>_<?php echo $ile_jezykow[$w-100]['id']; ?>" cols="80" rows="5"><?php echo $tekst['translate_value']; ?></textarea>
                                            </p>       

                                            <script>
                                            ckeditStopka('tekst_<?php echo $w; ?>_<?php echo $ile_jezykow[$w-100]['id']; ?>','STOPKA_TEKST_DRUGA',<?php echo $ile_jezykow[$w-100]['id']; ?>);
                                            </script>                                            

                                            <?php
                                            $db->close_query($sqls);
                                            unset($tekst);  
                                            ?>
                                                        
                                        </div>
                                        
                                        <?php  
                                    }                    
                                    ?>                      
                                </div> 
                                 
                                <div class="naglowek" style="margin-top:20px">Linki w drugiej kolumnie stopki</div>
                                
                                <div id="wyglad_stopka_druga" class="WygladStopka">
                                
                                    <?php
                                    $pozycje_menu = explode(',', (string)STOPKA_DRUGA);
                                    //
                                    if (STOPKA_DRUGA != '') {
                                        //
                                        for ($x = 0, $c = count($pozycje_menu); $x < $c; $x++) {
                                        
                                            $nr_zakladki = 8;
                                            include('wyglad_menu_pozycje.php');                                                
                                            ?>
                                            
                                            <div class="Stala" id="stopka_druga_<?php echo $idDoDiva; ?>">
                                                <?php echo $nazwaDowyswietlania; ?>
                                                <em class="TipChmurka" style="float:right;"><b>Skasuj</b><img class="Skasuj" onclick="ssk('<?php echo $idDoDiva; ?>','stopka_druga')" src="obrazki/kasuj.png" alt="Skasuj" /></em>
                                                <?php echo $edycjaElementu; ?>
                                                <em class="TipChmurka" style="float:right;"><b>W dół</b><img class="Dol" onclick="przesun('<?php echo $idDoDiva; ?>','stopka_druga','dol')" src="obrazki/strzalka_dol.png" alt="W dół" /></em>
                                                <em class="TipChmurka" style="float:right;"><b>W górę</b><img class="Gora" onclick="przesun('<?php echo $idDoDiva; ?>','stopka_druga','gora')" src="obrazki/strzalka_gora.png" alt="W górę" /></em>                                                                                                
                                            </div>                        
                                            <?php
                                            
                                            unset($nazwaDowyswietlania, $idDoDiva, $edycjaElementu, $nr_zakladki); 
                                        }
                                        //
                                    } else {
                                        
                                      echo '<p style="padding:10px">Brak pozycji ...</p>';
                                      
                                    }
                                    ?>
                                    
                                </div>
                                
                                <div class="DodajStopka">
                                    <span class="dodaj" onclick="dodaj_stala('stopka_druga')" style="cursor:pointer">dodaj nową pozycję do kolumny</span>
                                </div>   

                                <div class="Legenda">
                                    <span class="StronaInfo"> strona informacyjna</span>
                                    <span class="LinkZew"> link zewnętrzny</span>
                                    <span class="Galeria"> galeria</span>
                                    <span class="Formularz"> formularz</span>
                                    <span class="ArtykulKategoria"> kategoria artykułów</span>
                                    <span class="ProduktKategoria"> kategoria produktów</span>
                                    <span class="Artykul"> artykuł</span>         
                                </div>                                 
                            
                            </div>
                            
                            <script>
                            gold_tabs('100');
                            </script>                             
                            
                        </div>    


                        <div id="zakl_id_9" style="display:none;">
                        
                            <div class="naglowek" style="margin:10px 20px 15px 20px">Ustawienia trzeciej kolumny stopki</div>
                        
                            <?php if ( Wyglad::TypSzablonu() == true ) { ?>
                                                
                            <p>
                                <label>Czy kolumna trzecia ma być wyświetlana ?</label>
                                <input type="radio" value="tak" name="stopka_kolumna_trzecia" id="stopka_kolumna_trzecia_tak" onchange="zmienGet(this.value,'STOPKA_KOLUMNA_TRZECIA_WLACZONA')" <?php echo ((STOPKA_KOLUMNA_TRZECIA_WLACZONA == 'tak') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="stopka_kolumna_trzecia_tak">tak</label>
                                <input type="radio" value="nie" name="stopka_kolumna_trzecia" id="stopka_kolumna_trzecia_nie" onchange="zmienGet(this.value,'STOPKA_KOLUMNA_TRZECIA_WLACZONA')" <?php echo ((STOPKA_KOLUMNA_TRZECIA_WLACZONA == 'nie') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="stopka_kolumna_trzecia_nie">nie</label>
                            </p>          

                            <?php } ?>                          
                        
                            <div class="WygladTabela" style="margin-top:0px">
                        
                                <div class="info_tab">
                                <?php
                                for ($w = 200, $cw = count($ile_jezykow); $w < $cw + 200; $w++) {
                                    echo '<span id="link_'.$w.'" class="a_href_info_tab" onclick="gold_tabs(\''.$w.'\')">'.$ile_jezykow[$w-200]['text'].'</span>';
                                }                    
                                ?>                   
                                </div>
                                
                                <div style="clear:both"></div>
                                
                                <div class="info_tab_content" style="margin-left:0px; margin-right:0px">
                                
                                    <?php for ($w = 200, $cw = count($ile_jezykow); $w < $cw + 200; $w++) { ?> 

                                        <div id="info_tab_id_<?php echo $w; ?>" style="display:none;">
                                        
                                            <?php
                                            // pobieranie danych jezykowych
                                            $zapytanie_jezyk = "select distinct * from translate_constant w left join translate_value t on w.translate_constant_id = t.translate_constant_id where w.translate_constant = 'STOPKA_NAGLOWEK_TRZECIA' and t.language_id = '" .$ile_jezykow[$w-200]['id']."'";
                                            $sqls = $db->open_query($zapytanie_jezyk);
                                            $nazwa = $sqls->fetch_assoc();                                            
                                            ?>
                                            
                                            <p>
                                                <label for="nazwa_<?php echo $w; ?>">Nazwa nagłówka stopki:</label>   
                                                <input type="text" onchange="zmienGetJezyk(this.value,'STOPKA_NAGLOWEK_TRZECIA',<?php echo $ile_jezykow[$w-200]['id']; ?>)" name="nazwa_<?php echo $w; ?>" id="nazwa_<?php echo $w; ?>" size="45" value="<?php echo $nazwa['translate_value']; ?>" /><em class="TipIkona"><b>Tekst wyświetlany w nagłówku kolumny w stopce</b></em>
                                            </p> 
                                            
                                            <?php
                                            $db->close_query($sqls);
                                            unset($nazwa);                                            
                                            
                                            // pobieranie danych jezykowych
                                            $zapytanie_jezyk = "select distinct * from translate_constant w left join translate_value t on w.translate_constant_id = t.translate_constant_id where w.translate_constant = 'STOPKA_TEKST_TRZECIA' and t.language_id = '" .$ile_jezykow[$w-200]['id']."'";
                                            $sqls = $db->open_query($zapytanie_jezyk);
                                            $tekst = $sqls->fetch_assoc();                                            
                                            ?>                                            
                                            
                                            <p>
                                                <label for="tekst_<?php echo $w; ?>">Tekst nad linkami stopki:</label>   
                                                <textarea name="tekst_<?php echo $w; ?>" id="tekst_<?php echo $w; ?>_<?php echo $ile_jezykow[$w-200]['id']; ?>" cols="80" rows="5"><?php echo $tekst['translate_value']; ?></textarea>
                                            </p>   
                                            
                                            <script>
                                            ckeditStopka('tekst_<?php echo $w; ?>_<?php echo $ile_jezykow[$w-200]['id']; ?>','STOPKA_TEKST_TRZECIA',<?php echo $ile_jezykow[$w-200]['id']; ?>);
                                            </script>  
                                            
                                            <?php
                                            $db->close_query($sqls);
                                            unset($tekst);                                            
                                            ?>                                            
                                                        
                                        </div>
                                        
                                        <?php  
                                    }                    
                                    ?>                      
                                </div> 
                                
                                <div class="naglowek" style="margin-top:20px">Linki w trzeciej kolumnie stopki</div>
                                
                                <div id="wyglad_stopka_trzecia" class="WygladStopka">
                                
                                    <?php
                                    $pozycje_menu = explode(',', (string)STOPKA_TRZECIA);
                                    //
                                    if (STOPKA_TRZECIA != '') {
                                        //
                                        for ($x = 0, $c = count($pozycje_menu); $x < $c; $x++) {
                                        
                                            $nr_zakladki = 9;
                                            include('wyglad_menu_pozycje.php');                                              
                                            ?>
                                            
                                            <div class="Stala" id="stopka_trzecia_<?php echo $idDoDiva; ?>">
                                                <?php echo $nazwaDowyswietlania; ?>
                                                <em class="TipChmurka" style="float:right;"><b>Skasuj</b><img class="Skasuj" onclick="ssk('<?php echo $idDoDiva; ?>','stopka_trzecia')" src="obrazki/kasuj.png" alt="Skasuj" /></em>
                                                <?php echo $edycjaElementu; ?>
                                                <em class="TipChmurka" style="float:right;"><b>W dół</b><img class="Dol" onclick="przesun('<?php echo $idDoDiva; ?>','stopka_trzecia','dol')" src="obrazki/strzalka_dol.png" alt="W dół" /></em>
                                                <em class="TipChmurka" style="float:right;"><b>W górę</b><img class="Gora" onclick="przesun('<?php echo $idDoDiva; ?>','stopka_trzecia','gora')" src="obrazki/strzalka_gora.png" alt="W górę" /></em>                                                                                                
                                            </div>                        
                                            <?php
                                            
                                            unset($nazwaDowyswietlania, $idDoDiva, $edycjaElementu, $nr_zakladki); 
                                        }
                                        //
                                    } else {
                                        
                                      echo '<p style="padding:10px">Brak pozycji ...</p>';
                                      
                                    }
                                    ?>
                                    
                                </div>
                                
                                <div class="DodajStopka">
                                    <span class="dodaj" onclick="dodaj_stala('stopka_trzecia')" style="cursor:pointer">dodaj nową pozycję do kolumny</span>
                                </div>       

                                <div class="Legenda">
                                    <span class="StronaInfo"> strona informacyjna</span>
                                    <span class="LinkZew"> link zewnętrzny</span>
                                    <span class="Galeria"> galeria</span>
                                    <span class="Formularz"> formularz</span>
                                    <span class="ArtykulKategoria"> kategoria artykułów</span>
                                    <span class="ProduktKategoria"> kategoria produktów</span>
                                    <span class="Artykul"> artykuł</span>         
                                </div>                                 
                            
                            </div>
                            
                            <script>
                            gold_tabs('200');
                            </script>                             
                            
                        </div>                         


                        <div id="zakl_id_10" style="display:none;">
                        
                            <div class="naglowek" style="margin:10px 20px 15px 20px">Ustawienia czwartej kolumny stopki</div>
                        
                            <?php if ( Wyglad::TypSzablonu() == true ) { ?>
                                                
                            <p>
                                <label>Czy kolumna czwarta ma być wyświetlana ?</label>
                                <input type="radio" value="tak" name="stopka_kolumna_czwarta" id="stopka_kolumna_czwarta_tak" onchange="zmienGet(this.value,'STOPKA_KOLUMNA_CZWARTA_WLACZONA')" <?php echo ((STOPKA_KOLUMNA_CZWARTA_WLACZONA == 'tak') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="stopka_kolumna_czwarta_tak">tak</label>
                                <input type="radio" value="nie" name="stopka_kolumna_czwarta" id="stopka_kolumna_czwarta_nie" onchange="zmienGet(this.value,'STOPKA_KOLUMNA_CZWARTA_WLACZONA')" <?php echo ((STOPKA_KOLUMNA_CZWARTA_WLACZONA == 'nie') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="stopka_kolumna_czwarta_nie">nie</label>
                            </p>          

                            <?php } ?> 
                            
                            <div class="WygladTabela" style="margin-top:0px">
                        
                                <div class="info_tab">
                                <?php
                                for ($w = 300, $cw = count($ile_jezykow); $w < $cw + 300; $w++) {
                                    echo '<span id="link_'.$w.'" class="a_href_info_tab" onclick="gold_tabs(\''.$w.'\')">'.$ile_jezykow[$w-300]['text'].'</span>';
                                }                    
                                ?>                   
                                </div>
                                
                                <div style="clear:both"></div>
                                
                                <div class="info_tab_content" style="margin-left:0px; margin-right:0px">
                                
                                    <?php for ($w = 300, $cw = count($ile_jezykow); $w < $cw + 300; $w++) { ?> 

                                        <div id="info_tab_id_<?php echo $w; ?>" style="display:none;">
                                        
                                            <?php
                                            // pobieranie danych jezykowych
                                            $zapytanie_jezyk = "select distinct * from translate_constant w left join translate_value t on w.translate_constant_id = t.translate_constant_id where w.translate_constant = 'STOPKA_NAGLOWEK_CZWARTA' and t.language_id = '" .$ile_jezykow[$w-300]['id']."'";
                                            $sqls = $db->open_query($zapytanie_jezyk);
                                            $nazwa = $sqls->fetch_assoc();    
                                            ?>
                                        
                                            <p>
                                                <label for="nazwa_<?php echo $w; ?>">Nazwa nagłówka stopki:</label>   
                                                <input type="text" onchange="zmienGetJezyk(this.value,'STOPKA_NAGLOWEK_CZWARTA',<?php echo $ile_jezykow[$w-300]['id']; ?>)" name="nazwa_<?php echo $w; ?>" id="nazwa_<?php echo $w; ?>" size="45" value="<?php echo $nazwa['translate_value']; ?>" /><em class="TipIkona"><b>Tekst wyświetlany w nagłówku kolumny w stopce</b></em>
                                            </p>

                                            <?php
                                            $db->close_query($sqls);
                                            unset($nazwa);
                                        
                                            // pobieranie danych jezykowych
                                            $zapytanie_jezyk = "select distinct * from translate_constant w left join translate_value t on w.translate_constant_id = t.translate_constant_id where w.translate_constant = 'STOPKA_TEKST_CZWARTA' and t.language_id = '" .$ile_jezykow[$w-300]['id']."'";
                                            $sqls = $db->open_query($zapytanie_jezyk);
                                            $tekst = $sqls->fetch_assoc();    
                                            ?>
                                            
                                            <p>
                                                <label for="tekst_<?php echo $w; ?>">Tekst nad linkami stopki:</label>   
                                                <textarea name="tekst_<?php echo $w; ?>" id="tekst_<?php echo $w; ?>_<?php echo $ile_jezykow[$w-300]['id']; ?>" cols="80" rows="5"><?php echo $tekst['translate_value']; ?></textarea>
                                            </p> 

                                            <script>
                                            ckeditStopka('tekst_<?php echo $w; ?>_<?php echo $ile_jezykow[$w-300]['id']; ?>','STOPKA_TEKST_CZWARTA',<?php echo $ile_jezykow[$w-300]['id']; ?>);
                                            </script>   
                                            
                                            <?php
                                            $db->close_query($sqls);
                                            unset($tekst);
                                            ?>
                                                        
                                        </div>
                                        
                                        <?php  
                                    }                    
                                    ?>                      
                                </div> 
                                
                                <div class="naglowek" style="margin-top:20px">Linki w czwartej kolumnie stopki</div>
                                
                                <div id="wyglad_stopka_czwarta" class="WygladStopka">
                                
                                    <?php
                                    $pozycje_menu = explode(',', (string)STOPKA_CZWARTA);
                                    //
                                    if (STOPKA_CZWARTA != '') {
                                        //
                                        for ($x = 0, $c = count($pozycje_menu); $x < $c; $x++) {
                                        
                                            $nr_zakladki = 10;
                                            include('wyglad_menu_pozycje.php');                                                
                                            ?>
                                            
                                            <div class="Stala" id="stopka_czwarta_<?php echo $idDoDiva; ?>">
                                                <?php echo $nazwaDowyswietlania; ?>
                                                <em class="TipChmurka" style="float:right;"><b>Skasuj</b><img class="Skasuj" onclick="ssk('<?php echo $idDoDiva; ?>','stopka_czwarta')" src="obrazki/kasuj.png" alt="Skasuj" /></em>
                                                <?php echo $edycjaElementu; ?>
                                                <em class="TipChmurka" style="float:right;"><b>W dół</b><img class="Dol" onclick="przesun('<?php echo $idDoDiva; ?>','stopka_czwarta','dol')" src="obrazki/strzalka_dol.png" alt="W dół" /></em>
                                                <em class="TipChmurka" style="float:right;"><b>W górę</b><img class="Gora" onclick="przesun('<?php echo $idDoDiva; ?>','stopka_czwarta','gora')" src="obrazki/strzalka_gora.png" alt="W górę" /></em>                                                                                                
                                            </div>                        
                                            <?php
                                            
                                            unset($nazwaDowyswietlania, $idDoDiva, $edycjaElementu, $nr_zakladki); 
                                        }
                                        //
                                    } else {
                                        
                                      echo '<p style="padding:10px">Brak pozycji ...</p>';
                                      
                                    }
                                    ?>
                                    
                                </div>
                                
                                <div class="DodajStopka">
                                    <span class="dodaj" onclick="dodaj_stala('stopka_czwarta')" style="cursor:pointer">dodaj nową pozycję do kolumny</span>
                                </div>

                                <div class="Legenda">
                                    <span class="StronaInfo"> strona informacyjna</span>
                                    <span class="LinkZew"> link zewnętrzny</span>
                                    <span class="Galeria"> galeria</span>
                                    <span class="Formularz"> formularz</span>
                                    <span class="ArtykulKategoria"> kategoria artykułów</span>
                                    <span class="ProduktKategoria"> kategoria produktów</span>
                                    <span class="Artykul"> artykuł</span>         
                                </div>                                 
                            
                            </div>
                            
                            <script>
                            gold_tabs('300');
                            </script>                             
                            
                        </div> 


                        <div id="zakl_id_11" style="display:none;">
                        
                            <div class="naglowek" style="margin:10px 20px 15px 20px">Ustawienia piątej kolumny stopki</div>
                        
                            <?php if ( Wyglad::TypSzablonu() == true ) { ?>
                                                
                            <p>
                                <label>Czy kolumna piąta ma być wyświetlana ?</label>
                                <input type="radio" value="tak" name="stopka_kolumna_piata" id="stopka_kolumna_piata_tak" onchange="zmienGet(this.value,'STOPKA_KOLUMNA_PIATA_WLACZONA')" <?php echo ((STOPKA_KOLUMNA_PIATA_WLACZONA == 'tak') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="stopka_kolumna_piata_tak">tak</label>
                                <input type="radio" value="nie" name="stopka_kolumna_piata" id="stopka_kolumna_piata_nie" onchange="zmienGet(this.value,'STOPKA_KOLUMNA_PIATA_WLACZONA')" <?php echo ((STOPKA_KOLUMNA_PIATA_WLACZONA == 'nie') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="stopka_kolumna_piata_nie">nie</label>
                            </p>          

                            <?php } ?> 
                            
                            <div class="WygladTabela" style="margin-top:0px">
                        
                                <div class="info_tab">
                                <?php
                                for ($w = 400, $cw = count($ile_jezykow); $w < $cw + 400; $w++) {
                                    echo '<span id="link_'.$w.'" class="a_href_info_tab" onclick="gold_tabs(\''.$w.'\')">'.$ile_jezykow[$w-400]['text'].'</span>';
                                }                    
                                ?>                   
                                </div>
                                
                                <div style="clear:both"></div>
                                
                                <div class="info_tab_content" style="margin-left:0px; margin-right:0px">
                                
                                    <?php for ($w = 400, $cw = count($ile_jezykow); $w < $cw + 400; $w++) { ?> 

                                        <div id="info_tab_id_<?php echo $w; ?>" style="display:none;">
                                        
                                            <?php                                    
                                            // pobieranie danych jezykowych
                                            $zapytanie_jezyk = "select distinct * from translate_constant w left join translate_value t on w.translate_constant_id = t.translate_constant_id where w.translate_constant = 'STOPKA_NAGLOWEK_PIATA' and t.language_id = '" .$ile_jezykow[$w-400]['id']."'";
                                            $sqls = $db->open_query($zapytanie_jezyk);
                                            $nazwa = $sqls->fetch_assoc();                                             
                                            ?>
                                            
                                            <p>
                                                <label for="nazwa_<?php echo $w; ?>">Nazwa nagłówka stopki:</label>   
                                                <input type="text" onchange="zmienGetJezyk(this.value,'STOPKA_NAGLOWEK_PIATA',<?php echo $ile_jezykow[$w-400]['id']; ?>)" name="nazwa_<?php echo $w; ?>" id="nazwa_<?php echo $w; ?>" size="45" value="<?php echo $nazwa['translate_value']; ?>" /><em class="TipIkona"><b>Tekst wyświetlany w nagłówku kolumny w stopce</b></em>
                                            </p> 
                                            
                                            <?php
                                            $db->close_query($sqls);
                                            unset($nazwa);                                            
                                            
                                            // pobieranie danych jezykowych
                                            $zapytanie_jezyk = "select distinct * from translate_constant w left join translate_value t on w.translate_constant_id = t.translate_constant_id where w.translate_constant = 'STOPKA_TEKST_PIATA' and t.language_id = '" .$ile_jezykow[$w-400]['id']."'";
                                            $sqls = $db->open_query($zapytanie_jezyk);
                                            $tekst = $sqls->fetch_assoc();                                             
                                            ?>                                            
                                            
                                            <p>
                                                <label for="tekst_<?php echo $w; ?>">Tekst nad linkami stopki:</label>   
                                                <textarea name="tekst_<?php echo $w; ?>" id="tekst_<?php echo $w; ?>_<?php echo $ile_jezykow[$w-400]['id']; ?>" cols="80" rows="5"><?php echo $tekst['translate_value']; ?></textarea>
                                            </p>  
                                            
                                            <script>
                                            ckeditStopka('tekst_<?php echo $w; ?>_<?php echo $ile_jezykow[$w-400]['id']; ?>','STOPKA_TEKST_PIATA',<?php echo $ile_jezykow[$w-400]['id']; ?>);
                                            </script>                                               

                                            <?php
                                            $db->close_query($sqls);
                                            unset($tekst); 
                                            ?>
                                                        
                                        </div>
                                        
                                        <?php  
                                    }                    
                                    ?>                      
                                </div> 
                                
                                <div class="naglowek" style="margin-top:20px">Linki w piątej kolumnie stopki</div>
                                
                                <div id="wyglad_stopka_piata" class="WygladStopka">
                                
                                    <?php
                                    $pozycje_menu = explode(',', (string)STOPKA_PIATA);
                                    //
                                    if (STOPKA_PIATA != '') {
                                        //
                                        for ($x = 0, $c = count($pozycje_menu); $x < $c; $x++) {
                                        
                                            $nr_zakladki = 11;
                                            include('wyglad_menu_pozycje.php');
                                            ?>
                                            
                                            <div class="Stala" id="stopka_piata_<?php echo $idDoDiva; ?>">
                                                <?php echo $nazwaDowyswietlania; ?>
                                                <em class="TipChmurka" style="float:right;"><b>Skasuj</b><img class="Skasuj" onclick="ssk('<?php echo $idDoDiva; ?>','stopka_piata')" src="obrazki/kasuj.png" alt="Skasuj" /></em>
                                                <?php echo $edycjaElementu; ?>
                                                <em class="TipChmurka" style="float:right;"><b>W dół</b><img class="Dol" onclick="przesun('<?php echo $idDoDiva; ?>','stopka_piata','dol')" src="obrazki/strzalka_dol.png" alt="W dół" /></em>
                                                <em class="TipChmurka" style="float:right;"><b>W górę</b><img class="Gora" onclick="przesun('<?php echo $idDoDiva; ?>','stopka_piata','gora')" src="obrazki/strzalka_gora.png" alt="W górę" /></em>                                                                                                
                                            </div>                        
                                            <?php
                                            
                                            unset($nazwaDowyswietlania, $idDoDiva, $edycjaElementu, $nr_zakladki); 
                                        }
                                        //
                                    } else {
                                        
                                      echo '<p style="padding:10px">Brak pozycji ...</p>';
                                      
                                    }
                                    ?>
                                    
                                </div>
                                
                                <div class="DodajStopka">
                                    <span class="dodaj" onclick="dodaj_stala('stopka_piata')" style="cursor:pointer">dodaj nową pozycję do kolumny</span>
                                </div>       

                                <div class="Legenda">
                                    <span class="StronaInfo"> strona informacyjna</span>
                                    <span class="LinkZew"> link zewnętrzny</span>
                                    <span class="Galeria"> galeria</span>
                                    <span class="Formularz"> formularz</span>
                                    <span class="ArtykulKategoria"> kategoria artykułów</span>
                                    <span class="ProduktKategoria"> kategoria produktów</span>
                                    <span class="Artykul"> artykuł</span>         
                                </div>                                 
                            
                            </div>
                            
                            <script>
                            gold_tabs('400');
                            </script>                             
                            
                        </div>


                        <?php if ( Wyglad::TypSzablonu() == true ) { ?>

                        <div id="zakl_id_12" style="display:none;">
                        
                            <div class="naglowek" style="margin:10px 20px 15px 20px">Bannery wyświetlane w stopce sklepu</div>

                            <p>
                                <label>Czy wyświetlać bannery w stopce ?</label>
                                <input type="radio" value="tak" name="stopka_bannery" id="stopka_bannery_tak" onclick="$('#stopka_bannery').stop().slideDown()" onchange="zmienGet(this.value,'STOPKA_BANNERY')" <?php echo ((STOPKA_BANNERY == 'tak') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="stopka_bannery_tak">tak</label>
                                <input type="radio" value="nie" name="stopka_bannery" id="stopka_bannery_nie" onclick="$('#stopka_bannery').stop().slideUp()" onchange="zmienGet(this.value,'STOPKA_BANNERY')" <?php echo ((STOPKA_BANNERY == 'nie') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="stopka_bannery_nie">nie</label>
                            </p> 
                            
                            <div id="stopka_bannery" <?php echo ((STOPKA_BANNERY == 'tak') ? '' : 'style="display:none"'); ?>>
                            
                                <p>
                                  <label for="stopka_bannery_grupa">Wyświetlaj bannery z grupy:</label>
                                  <?php echo Funkcje::RozwijaneMenu('stopka_bannery_grupa', BoxyModuly::ListaGrupBannerow(true), STOPKA_BANNERY_GRUPA, ' id="stopka_bannery_grupa" onchange="zmienGet(this.value,\'STOPKA_BANNERY_GRUPA\')"');  ?>
                                </p>
                                
                            </div>
                            
                        </div> 
                        
                        <div id="zakl_id_13" style="display:none;">
                        
                            <div class="naglowek" style="margin:10px 20px 15px 20px">Tekst pod linkami stopki</div>
                        
                            <div class="WygladTabela" style="margin-top:0px">

                                <div class="info_tab">
                                <?php
                                for ($w = 500, $cw = count($ile_jezykow); $w < $cw + 500; $w++) {
                                    echo '<span id="link_'.$w.'" class="a_href_info_tab" onclick="gold_tabs(\''.$w.'\')">'.$ile_jezykow[$w-500]['text'].'</span>';
                                }                    
                                ?>                   
                                </div>
                                
                                <div style="clear:both"></div>
                                
                                <div class="info_tab_content" style="margin-left:0px; margin-right:0px">
                                
                                    <?php for ($w = 500, $cw = count($ile_jezykow); $w < $cw + 500; $w++) { ?> 

                                        <div id="info_tab_id_<?php echo $w; ?>" style="display:none;">
                                        
                                            <?php                                    
                                            // pobieranie danych jezykowych
                                            $zapytanie_jezyk = "select distinct * from translate_constant w left join translate_value t on w.translate_constant_id = t.translate_constant_id where w.translate_constant = 'OPIS_TEKST_STOPKA' and t.language_id = '" .$ile_jezykow[$w-500]['id']."'";
                                            $sqls = $db->open_query($zapytanie_jezyk);
                                            $tekst = $sqls->fetch_assoc();                                             
                                            ?>                                            
                                            
                                            <p>  
                                                <textarea name="tekst_<?php echo $w; ?>" id="tekst_<?php echo $w; ?>_<?php echo $ile_jezykow[$w-500]['id']; ?>" cols="80" rows="5"><?php echo $tekst['translate_value']; ?></textarea>
                                            </p>  
                                            
                                            <script>
                                            ckeditStopka('tekst_<?php echo $w; ?>_<?php echo $ile_jezykow[$w-500]['id']; ?>','OPIS_TEKST_STOPKA',<?php echo $ile_jezykow[$w-500]['id']; ?>);
                                            </script>                                               

                                            <?php
                                            $db->close_query($sqls);
                                            unset($tekst); 
                                            ?>
                                                        
                                        </div>
                                        
                                        <?php  
                                    }                    
                                    ?>                      
                                </div>
                            
                            </div> 
                            
                            <script>
                            gold_tabs('500');
                            </script>  
                            
                        </div>   

                        <div id="zakl_id_14" style="display:none;">
                        
                            <div class="naglowek" style="margin:10px 20px 15px 20px">Dane kontaktowe w stopce</div>
                        
                            <div class="maleInfo" style="margin:0px 0px 10px 25px">Wyświetlane dane kontaktowe pobierane są z danych wpisanych w menu Konfiguracja / Dane teleadresowe oraz Konfiguracja / Komunikacja / Ustawienia e-mail (dla adresu e-mail)</div>

                            <p>
                                <label>Czy wyświetlać dane kontaktowe w stopce ?</label>
                                <input type="radio" value="tak" name="stopka_dane_kontaktowe" id="stopka_dane_kontaktowe_tak" onchange="zmienGet(this.value,'STOPKA_DANE_KONTAKTOWE_STATUS')" <?php echo ((STOPKA_DANE_KONTAKTOWE_STATUS == 'tak') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="stopka_dane_kontaktowe_tak">tak</label>
                                <input type="radio" value="nie" name="stopka_dane_kontaktowe" id="stopka_dane_kontaktowe_nie" onchange="zmienGet(this.value,'STOPKA_DANE_KONTAKTOWE_STATUS')" <?php echo ((STOPKA_DANE_KONTAKTOWE_STATUS == 'nie') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="stopka_dane_kontaktowe_nie">nie</label>
                            </p> 
                           
                            <p>
                                <label>Czy generować dane strukturalne na podstawie danych firmy, dane definiowane w menu Dane teleadresowe (tylko jeżeli są wyświetlane dane dane podmiotu prowadzącego sklep) ?</label>
                                <input type="radio" value="tak" name="stopka_dane_strukturalne" id="stopka_dane_strukturalne_tak" onchange="zmienGet(this.value,'STOPKA_DANE_STRUKTURALNE_STATUS')" <?php echo ((STOPKA_DANE_STRUKTURALNE_STATUS == 'tak') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="stopka_dane_strukturalne_tak">tak</label>
                                <input type="radio" value="nie" name="stopka_dane_strukturalne" id="stopka_dane_strukturalne_nie" onchange="zmienGet(this.value,'STOPKA_DANE_STRUKTURALNE_STATUS')" <?php echo ((STOPKA_DANE_STRUKTURALNE_STATUS == 'nie') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="stopka_dane_strukturalne_nie">nie</label>
                            </p> 
                            
                            <p>
                              <label for="stopka_dane_kontaktowe_kolumna">W której kolumnie wyświetlać dane kontaktowe ?</label>                              
                              <?php 
                              $tablica = array( array('id' => 'pierwsza', 'text' => 'pierwsza kolumna'),
                                                array('id' => 'druga', 'text' => 'druga kolumna'),
                                                array('id' => 'trzecia', 'text' => 'trzecia kolumna'),
                                                array('id' => 'czwarta', 'text' => 'czwarta kolumna'),
                                                array('id' => 'piata', 'text' => 'piąta kolumna') );  
                                                
                              echo Funkcje::RozwijaneMenu('stopka_dane_kontaktowe_kolumna', $tablica, STOPKA_DANE_KONTAKTOWE_KOLUMNA, ' id="stopka_dane_kontaktowe_kolumna" onchange="zmienGet(this.value,\'STOPKA_DANE_KONTAKTOWE_KOLUMNA\')"'); 
                              unset($tablica);
                              ?>
                            </p>
                            
                            <p>
                                <label>Czy wyświetlać dane podmiotu prowadzącego sklep ?</label>
                                <input type="radio" value="tak" name="stopka_dane_kontaktowe_firma" id="stopka_dane_kontaktowe_firma_tak" onchange="zmienGet(this.value,'STOPKA_DANE_KONTAKTOWE_FIRMA')" <?php echo ((STOPKA_DANE_KONTAKTOWE_FIRMA == 'tak') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="stopka_dane_kontaktowe_firma_tak">tak</label>
                                <input type="radio" value="nie" name="stopka_dane_kontaktowe_firma" id="stopka_dane_kontaktowe_firma_nie" onchange="zmienGet(this.value,'STOPKA_DANE_KONTAKTOWE_FIRMA')" <?php echo ((STOPKA_DANE_KONTAKTOWE_FIRMA == 'nie') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="stopka_dane_kontaktowe_firma_nie">nie</label>
                            </p>                              
                            
                            <p>
                                <label>Czy wyświetlać numer NIP ?</label>
                                <input type="radio" value="tak" name="stopka_dane_kontaktowe_nip" id="stopka_dane_kontaktowe_nip_tak" onchange="zmienGet(this.value,'STOPKA_DANE_KONTAKTOWE_NIP')" <?php echo ((STOPKA_DANE_KONTAKTOWE_NIP == 'tak') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="stopka_dane_kontaktowe_nip_tak">tak</label>
                                <input type="radio" value="nie" name="stopka_dane_kontaktowe_nip" id="stopka_dane_kontaktowe_nip_nie" onchange="zmienGet(this.value,'STOPKA_DANE_KONTAKTOWE_NIP')" <?php echo ((STOPKA_DANE_KONTAKTOWE_NIP == 'nie') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="stopka_dane_kontaktowe_nip_nie">nie</label>
                            </p>   

                            <p>
                                <label>Czy wyświetlać numer REGON ?</label>
                                <input type="radio" value="tak" name="stopka_dane_kontaktowe_regon" id="stopka_dane_kontaktowe_regon_tak" onchange="zmienGet(this.value,'STOPKA_DANE_KONTAKTOWE_REGON')" <?php echo ((STOPKA_DANE_KONTAKTOWE_REGON == 'tak') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="stopka_dane_kontaktowe_regon_tak">tak</label>
                                <input type="radio" value="nie" name="stopka_dane_kontaktowe_regon" id="stopka_dane_kontaktowe_regon_nie" onchange="zmienGet(this.value,'STOPKA_DANE_KONTAKTOWE_REGON')" <?php echo ((STOPKA_DANE_KONTAKTOWE_REGON == 'nie') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="stopka_dane_kontaktowe_regon_nie">nie</label>
                            </p> 

                            <p>
                                <label>Czy wyświetlać numer BDO ?</label>
                                <input type="radio" value="tak" name="stopka_dane_kontaktowe_bdo" id="stopka_dane_kontaktowe_bdo_tak" onchange="zmienGet(this.value,'STOPKA_DANE_KONTAKTOWE_BDO')" <?php echo ((STOPKA_DANE_KONTAKTOWE_BDO == 'tak') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="stopka_dane_kontaktowe_bdo_tak">tak</label>
                                <input type="radio" value="nie" name="stopka_dane_kontaktowe_bdo" id="stopka_dane_kontaktowe_bdo_nie" onchange="zmienGet(this.value,'STOPKA_DANE_KONTAKTOWE_BDO')" <?php echo ((STOPKA_DANE_KONTAKTOWE_BDO == 'nie') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="stopka_dane_kontaktowe_bdo_nie">nie</label>
                            </p>                             
                            
                            <p>
                                <label>Czy wyświetlać nazwę banku i numer konta ?</label>
                                <input type="radio" value="tak" name="stopka_dane_kontaktowe_konto" id="stopka_dane_kontaktowe_konto_tak" onchange="zmienGet(this.value,'STOPKA_DANE_KONTAKTOWE_KONTO')" <?php echo ((STOPKA_DANE_KONTAKTOWE_KONTO == 'tak') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="stopka_dane_kontaktowe_konto_tak">tak</label>
                                <input type="radio" value="nie" name="stopka_dane_kontaktowe_konto" id="stopka_dane_kontaktowe_konto_nie" onchange="zmienGet(this.value,'STOPKA_DANE_KONTAKTOWE_KONTO')" <?php echo ((STOPKA_DANE_KONTAKTOWE_KONTO == 'nie') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="stopka_dane_kontaktowe_konto_nie">nie</label>
                            </p>                                                         
                            
                            <p>
                                <label>Czy wyświetlać logo firmy ?</label>
                                <input type="radio" value="tak" name="stopka_dane_kontaktowe_logo" id="stopka_dane_kontaktowe_logo_tak" onchange="zmienGet(this.value,'STOPKA_DANE_KONTAKTOWE_LOGO')" <?php echo ((STOPKA_DANE_KONTAKTOWE_LOGO == 'tak') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="stopka_dane_kontaktowe_logo_tak">tak</label>
                                <input type="radio" value="nie" name="stopka_dane_kontaktowe_logo" id="stopka_dane_kontaktowe_logo_nie" onchange="zmienGet(this.value,'STOPKA_DANE_KONTAKTOWE_LOGO')" <?php echo ((STOPKA_DANE_KONTAKTOWE_LOGO == 'nie') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="stopka_dane_kontaktowe_logo_nie">nie</label>
                            </p>    

                            <p>
                                <label>Czy wyświetlać kod QR firmy ?</label>
                                <input type="radio" value="tak" name="stopka_dane_kontaktowe_kod_qr" id="stopka_dane_kontaktowe_kod_qr_tak" onchange="zmienGet(this.value,'STOPKA_DANE_KONTAKTOWE_KOD_QR')" <?php echo ((STOPKA_DANE_KONTAKTOWE_KOD_QR == 'tak') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="stopka_dane_kontaktowe_kod_qr_tak">tak</label>
                                <input type="radio" value="nie" name="stopka_dane_kontaktowe_kod_qr" id="stopka_dane_kontaktowe_kod_qr_nie" onchange="zmienGet(this.value,'STOPKA_DANE_KONTAKTOWE_KOD_QR')" <?php echo ((STOPKA_DANE_KONTAKTOWE_KOD_QR == 'nie') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="stopka_dane_kontaktowe_kod_qr_nie">nie</label>
                            </p> 

                            <p>
                                <label>Czy wyświetlać numer telefonu nr 1 ?</label>
                                <input type="radio" value="tak" name="stopka_dane_kontaktowe_telefon_1" id="stopka_dane_kontaktowe_telefon_1_tak" onchange="zmienGet(this.value,'STOPKA_DANE_KONTAKTOWE_TELEFON_1')" <?php echo ((STOPKA_DANE_KONTAKTOWE_TELEFON_1 == 'tak') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="stopka_dane_kontaktowe_telefon_1_tak">tak</label>
                                <input type="radio" value="nie" name="stopka_dane_kontaktowe_telefon_1" id="stopka_dane_kontaktowe_telefon_1_nie" onchange="zmienGet(this.value,'STOPKA_DANE_KONTAKTOWE_TELEFON_1')" <?php echo ((STOPKA_DANE_KONTAKTOWE_TELEFON_1 == 'nie') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="stopka_dane_kontaktowe_telefon_1_nie">nie</label>
                            </p>  

                            <p>
                                <label>Czy wyświetlać numer telefonu nr 2 ?</label>
                                <input type="radio" value="tak" name="stopka_dane_kontaktowe_telefon_2" id="stopka_dane_kontaktowe_telefon_2_tak" onchange="zmienGet(this.value,'STOPKA_DANE_KONTAKTOWE_TELEFON_2')" <?php echo ((STOPKA_DANE_KONTAKTOWE_TELEFON_2 == 'tak') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="stopka_dane_kontaktowe_telefon_2_tak">tak</label>
                                <input type="radio" value="nie" name="stopka_dane_kontaktowe_telefon_2" id="stopka_dane_kontaktowe_telefon_2_nie" onchange="zmienGet(this.value,'STOPKA_DANE_KONTAKTOWE_TELEFON_2')" <?php echo ((STOPKA_DANE_KONTAKTOWE_TELEFON_2 == 'nie') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="stopka_dane_kontaktowe_telefon_2_nie">nie</label>
                            </p>  

                            <p>
                                <label>Czy wyświetlać numer telefonu nr 3 ?</label>
                                <input type="radio" value="tak" name="stopka_dane_kontaktowe_telefon_3" id="stopka_dane_kontaktowe_telefon_3_tak" onchange="zmienGet(this.value,'STOPKA_DANE_KONTAKTOWE_TELEFON_3')" <?php echo ((STOPKA_DANE_KONTAKTOWE_TELEFON_3 == 'tak') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="stopka_dane_kontaktowe_telefon_3_tak">tak</label>
                                <input type="radio" value="nie" name="stopka_dane_kontaktowe_telefon_3" id="stopka_dane_kontaktowe_telefon_3_nie" onchange="zmienGet(this.value,'STOPKA_DANE_KONTAKTOWE_TELEFON_3')" <?php echo ((STOPKA_DANE_KONTAKTOWE_TELEFON_3 == 'nie') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="stopka_dane_kontaktowe_telefon_3_nie">nie</label>
                            </p> 

                            <p>
                                <label>Czy wyświetlać numer FAX-u ?</label>
                                <input type="radio" value="tak" name="stopka_dane_kontaktowe_fax" id="stopka_dane_kontaktowe_fax_tak" onchange="zmienGet(this.value,'STOPKA_DANE_KONTAKTOWE_FAX')" <?php echo ((STOPKA_DANE_KONTAKTOWE_FAX == 'tak') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="stopka_dane_kontaktowe_fax_tak">tak</label>
                                <input type="radio" value="nie" name="stopka_dane_kontaktowe_fax" id="stopka_dane_kontaktowe_fax_nie" onchange="zmienGet(this.value,'STOPKA_DANE_KONTAKTOWE_FAX')" <?php echo ((STOPKA_DANE_KONTAKTOWE_FAX == 'nie') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="stopka_dane_kontaktowe_fax_nie">nie</label>
                            </p> 
                            
                            <p>
                                <label>Czy wyświetlać adres e-mail ?</label>
                                <input type="radio" value="tak" name="stopka_dane_kontaktowe_email" id="stopka_dane_kontaktowe_email_tak" onchange="zmienGet(this.value,'STOPKA_DANE_KONTAKTOWE_EMAIL')" <?php echo ((STOPKA_DANE_KONTAKTOWE_EMAIL == 'tak') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="stopka_dane_kontaktowe_email_tak">tak</label>
                                <input type="radio" value="nie" name="stopka_dane_kontaktowe_email" id="stopka_dane_kontaktowe_email_nie" onchange="zmienGet(this.value,'STOPKA_DANE_KONTAKTOWE_EMAIL')" <?php echo ((STOPKA_DANE_KONTAKTOWE_EMAIL == 'nie') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="stopka_dane_kontaktowe_email_nie">nie</label>
                            </p>
                            
                            <p>
                                <label>W jakiej formie wyświetlać adres e-mail ?</label>
                                <input type="radio" value="mailto" name="stopka_dane_kontaktowe_email_forma" id="stopka_dane_kontaktowe_email_forma_mailto" onchange="zmienGet(this.value,'STOPKA_DANE_KONTAKTOWE_EMAIL_FORMA')" <?php echo ((STOPKA_DANE_KONTAKTOWE_EMAIL_FORMA == 'mailto') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="stopka_dane_kontaktowe_email_forma_mailto">bezpośredni odnośnik do adresu e-mail</label>
                                <input type="radio" value="formularz" name="stopka_dane_kontaktowe_email_forma" id="stopka_dane_kontaktowe_email_forma_formularz" onchange="zmienGet(this.value,'STOPKA_DANE_KONTAKTOWE_EMAIL_FORMA')" <?php echo ((STOPKA_DANE_KONTAKTOWE_EMAIL_FORMA == 'formularz') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="stopka_dane_kontaktowe_email_forma_formularz">link do formularza kontaktowego</label>
                            </p>                            

                            <p>
                                <label>Czy wyświetlać numer Gadu Gadu ?</label>
                                <input type="radio" value="tak" name="stopka_dane_kontaktowe_gg" id="stopka_dane_kontaktowe_gg_tak" onchange="zmienGet(this.value,'STOPKA_DANE_KONTAKTOWE_GG')" <?php echo ((STOPKA_DANE_KONTAKTOWE_GG == 'tak') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="stopka_dane_kontaktowe_gg_tak">tak</label>
                                <input type="radio" value="nie" name="stopka_dane_kontaktowe_gg" id="stopka_dane_kontaktowe_gg_nie" onchange="zmienGet(this.value,'STOPKA_DANE_KONTAKTOWE_GG')" <?php echo ((STOPKA_DANE_KONTAKTOWE_GG == 'nie') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="stopka_dane_kontaktowe_gg_nie">nie</label>
                            </p>                               

                            <p>
                                <label>Czy wyświetlać godziny działania sklepu ?</label>
                                <input type="radio" value="tak" name="stopka_dane_kontaktowe_godziny" id="stopka_dane_kontaktowe_godziny_tak" onchange="zmienGet(this.value,'STOPKA_DANE_KONTAKTOWE_GODZINY')" <?php echo ((STOPKA_DANE_KONTAKTOWE_GODZINY == 'tak') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="stopka_dane_kontaktowe_godziny_tak">tak</label>
                                <input type="radio" value="nie" name="stopka_dane_kontaktowe_godziny" id="stopka_dane_kontaktowe_godziny_nie" onchange="zmienGet(this.value,'STOPKA_DANE_KONTAKTOWE_GODZINY')" <?php echo ((STOPKA_DANE_KONTAKTOWE_GODZINY == 'nie') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="stopka_dane_kontaktowe_godziny_nie">nie</label>
                            </p>                              
                            
                            <p>
                                <label>Czy wyświetlać ikonki portali społecznościowych ?</label>
                                <input type="radio" value="tak" name="stopka_portale" id="stopka_portale_tak" onchange="zmienGet(this.value,'STOPKA_PORTALE')" <?php echo ((STOPKA_PORTALE == 'tak') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="stopka_portale_tak">tak</label>
                                <input type="radio" value="nie" name="stopka_portale" id="stopka_portale_nie" onchange="zmienGet(this.value,'STOPKA_PORTALE')" <?php echo ((STOPKA_PORTALE == 'nie') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="stopka_portale_nie">nie</label>
                            </p>   
                            
                        </div>   

                        <div id="zakl_id_15" style="display:none;">
                        
                            <div class="naglowek" style="margin:10px 20px 15px 20px">Informacje wyświetlane nad nagłówkiem sklepu</div>
                        
                            <p>
                                <label>Czy wyświetlać informacje nad nagłówkiem ?</label>
                                <input type="radio" value="tak" name="info_nad_naglowkiem" id="info_nad_naglowkiem_tak" onchange="zmienGet(this.value,'INFO_NAD_NAGLOWKIEM')" <?php echo ((INFO_NAD_NAGLOWKIEM == 'tak') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="info_nad_naglowkiem_tak">tak</label>
                                <input type="radio" value="nie" name="info_nad_naglowkiem" id="info_nad_naglowkiem_nie" onchange="zmienGet(this.value,'INFO_NAD_NAGLOWKIEM')" <?php echo ((INFO_NAD_NAGLOWKIEM == 'nie') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="info_nad_naglowkiem_nie">nie</label>
                            </p>    

                            <p>
                                <label>Czy wyświetlać informacje w wersji mobilnej ?</label>
                                <input type="radio" value="tak" name="info_nad_naglowkiem_mobile" id="info_nad_naglowkiem_mobile_tak" onchange="zmienGet(this.value,'INFO_NAD_NAGLOWKIEM_MOBILE')" <?php echo ((INFO_NAD_NAGLOWKIEM_MOBILE == 'tak') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="info_nad_naglowkiem_mobile_tak">tak</label>
                                <input type="radio" value="nie" name="info_nad_naglowkiem_mobile" id="info_nad_naglowkiem_mobile_nie" onchange="zmienGet(this.value,'INFO_NAD_NAGLOWKIEM_MOBILE')" <?php echo ((INFO_NAD_NAGLOWKIEM_MOBILE == 'nie') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="info_nad_naglowkiem_mobile_nie">nie</label>
                            </p>                             
                        
                            <div class="WygladTabela" style="margin-top:0px">

                                <div class="info_tab">
                                <?php
                                for ($w = 600, $cw = count($ile_jezykow); $w < $cw + 600; $w++) {
                                    echo '<span id="link_'.$w.'" class="a_href_info_tab" onclick="gold_tabs(\''.$w.'\')">'.$ile_jezykow[$w-600]['text'].'</span>';
                                }                    
                                ?>                   
                                </div>
                                
                                <div style="clear:both"></div>
                                
                                <div class="info_tab_content" style="margin-left:0px; margin-right:0px">
                                
                                    <?php for ($w = 600, $cw = count($ile_jezykow); $w < $cw + 600; $w++) { ?> 

                                        <div id="info_tab_id_<?php echo $w; ?>" style="display:none;">
                                        
                                            <?php                                    
                                            // pobieranie danych jezykowych
                                            $zapytanie_jezyk = "select distinct * from translate_constant w left join translate_value t on w.translate_constant_id = t.translate_constant_id where w.translate_constant = 'OPIS_NAD_NAGLOWKIEM' and t.language_id = '" .$ile_jezykow[$w-600]['id']."'";
                                            $sqls = $db->open_query($zapytanie_jezyk);
                                            $tekst = $sqls->fetch_assoc();                                             
                                            ?>                                            
                                            
                                            <p>  
                                                <textarea name="tekst_<?php echo $w; ?>" id="tekst_<?php echo $w; ?>_<?php echo $ile_jezykow[$w-600]['id']; ?>" cols="80" rows="5"><?php echo $tekst['translate_value']; ?></textarea>
                                            </p>  
                                            
                                            <script>
                                            ckeditStopka('tekst_<?php echo $w; ?>_<?php echo $ile_jezykow[$w-600]['id']; ?>','OPIS_NAD_NAGLOWKIEM',<?php echo $ile_jezykow[$w-600]['id']; ?>);
                                            </script>                                               

                                            <?php
                                            $db->close_query($sqls);
                                            unset($tekst); 
                                            ?>
                                                        
                                        </div>
                                        
                                        <?php  
                                    }                    
                                    ?>                      
                                </div>
                            
                            </div> 
                            
                            <script>
                            gold_tabs('600');
                            </script>  
                            
                        </div>                          
                        
                        <?php } ?>
                        
                        <div id="zakl_id_16" style="display:none;">
                        
                            <div class="naglowek" style="margin:10px 20px 15px 20px">Dodatkowy kod CSS wyglądu</div>
                            
                            <div class="maleInfo" style="margin-left:25px">Dodatkowy kod CSS wyświetlany w aktualnie ustawionym jako domyślny szablonie. Wpisany kod będzie umieszczony pomiędzy znacznikami &lt;style&gt;.....&lt;/style&gt; w nagłówku strony</div>
                        
                            <div style="margin:10px 20px 0px 20px">  
                                <textarea name="dodatkowy_css" id="dodatkowy_css" style="width:99%;font-family:Courier" onchange="zmienGet(this.value,'WYGLAD_DODATKOWY_CSS')" cols="80" rows="15"><?php echo WYGLAD_DODATKOWY_CSS; ?></textarea>
                            </div>  

                        </div>                        
                        
                        
                        <?php if ( Wyglad::TypSzablonu() == true ) { ?>
                        
                        <div id="zakl_id_17" style="display:none;">
                        
                            <div class="naglowek" style="margin:10px 20px 15px 20px">Szybkie linki w nagłówku</div>
                        
                            <p>
                                <label>Czy wyświetlać szybkie linki w wersji mobilnej ?</label>
                                <input type="radio" value="tak" name="szybkie_menu_mobile" id="szybkie_menu_mobile_tak" onchange="zmienGet(this.value,'SZYBKIE_MENU_MOBILE')" <?php echo ((SZYBKIE_MENU_MOBILE == 'tak') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="szybkie_menu_mobile_tak">tak</label>
                                <input type="radio" value="nie" name="szybkie_menu_mobile" id="szybkie_menu_mobile_nie" onchange="zmienGet(this.value,'SZYBKIE_MENU_MOBILE')" <?php echo ((SZYBKIE_MENU_MOBILE == 'nie') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="szybkie_menu_mobile_nie">nie</label>
                            </p>         
                            
                            <div class="WygladTabela">
                            
                                <div id="wyglad_szybkie_menu" class="WygladSzybkieLinki">
                                
                                    <?php
                                    $pozycje_menu = explode(',', (string)SZYBKIE_MENU);
                                    //
                                    if (SZYBKIE_MENU != '') {
                                        //
                                        for ($x = 0, $c = count($pozycje_menu); $x < $c; $x++) {
                                        
                                            $nr_zakladki = 17;
                                            include('wyglad_menu_pozycje.php');                                                
                                            ?>
                                            
                                            <div class="Stala" id="szybkie_menu_<?php echo $idDoDiva; ?>">
                                                <?php echo $nazwaDowyswietlania; ?>
                                                <em class="TipChmurka" style="float:right;"><b>Skasuj</b><img class="Skasuj" onclick="ssk('<?php echo $idDoDiva; ?>','szybkie_menu')" src="obrazki/kasuj.png" alt="Skasuj" /></em>
                                                <?php echo $edycjaElementu; ?>
                                                <em class="TipChmurka" style="float:right;"><b>W dół</b><img class="Dol" onclick="przesun('<?php echo $idDoDiva; ?>','szybkie_menu','dol')" src="obrazki/strzalka_dol.png" alt="W dół" /></em>
                                                <em class="TipChmurka" style="float:right;"><b>W górę</b><img class="Gora" onclick="przesun('<?php echo $idDoDiva; ?>','szybkie_menu','gora')" src="obrazki/strzalka_gora.png" alt="W górę" /></em>                                                                                                
                                            </div>                        
                                            <?php
                                            
                                            unset($nazwaDowyswietlania, $idDoDiva, $edycjaElementu, $nr_zakladki); 
                                        }
                                        //
                                    } else {
                                        
                                      echo '<p style="padding:10px">Brak pozycji ...</p>';
                                      
                                    }
                                    ?>
                                    
                                </div>
                                
                                <div class="DodajSzybkieLinki">
                                    <span class="dodaj" onclick="dodaj_stala('szybkie_menu')" style="cursor:pointer">dodaj nową pozycję</span>
                                </div> 

                                <div class="Legenda">
                                    <span class="StronaInfo"> strona informacyjna</span>
                                    <span class="LinkZew"> link zewnętrzny</span>
                                    <span class="Galeria"> galeria</span>
                                    <span class="Formularz"> formularz</span>
                                    <span class="ArtykulKategoria"> kategoria artykułów</span>
                                    <span class="ProduktKategoria"> kategoria produktów</span>
                                    <span class="Artykul"> artykuł</span>         
                                </div>                                 

                            </div>
                            
                        </div>   
                        
                        <?php } ?>
                        
                    </div>
                        
                </div>
                
                <script>
                infoSzablon('<?php echo str_replace('.', '_', DOMYSLNY_SZABLON); ?>',0);
                <?php
                $zakladka = '0';
                if (isset($_GET['zakladka'])) $zakladka = (int)$_GET['zakladka'];
                ?>
                gold_tabs_horiz(<?php echo $zakladka; ?>);
                </script>            
            
          </div>

          </form>

    </div>    
    
    <div id="WygladPop">

        <form action="wyglad/wyglad.php" method="post" class="cmxform">
        
            <div id="ekr_edit" class="EkranEdycjiWygladu">
            
                <div id="edit_tlo" class="TloEdycjiWygladu"></div>
                
                <div id="edytuj_stale" class="EdytujWygladStale">
                
                    <div id="edytuj_okno" class="OknoEdycjiWygladu">
                    
                        <img class="ZamknijBox" onclick="zamknij_edycje()" src="obrazki/zamknij.png" alt="Zamknij okno" />
                        
                        <div id="glowne_okno_edycji"></div>
                        
                    </div>
                    
                </div>
                
            </div>

        </form>
        
    </div>
                
    <?php include('stopka.inc.php'); 

}
?>
