<?php
if (isset($_POST['data'])) {
    //
    chdir('../'); 
    
    // wczytanie ustawien inicjujacych system
    require_once('ustawienia/init.php');  

}    

$zapytanie = "select tmfd.modul_settings_code, tmfd.modul_settings_value from theme_modules_fixed tmf, theme_modules_fixed_settings tmfd where tmf.modul_id = tmfd.modul_id and tmf.modul_file = 'wcag.php'";
$sqlPopup = $GLOBALS['db']->open_query($zapytanie);
while ( $info = $sqlPopup->fetch_assoc() ) {
    //
    if ( !defined($info['modul_settings_code']) ) {
         define( $info['modul_settings_code'], $info['modul_settings_value'] );
    }
    //
}    
$GLOBALS['db']->close_query($sqlPopup);
unset($info, $zapytanie);

$GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('MODULY_STALE') ), $GLOBALS['tlumacz'] );
?>

<script src="moduly_stale/wcag/wcag.js"></script>  

<!--- css dla kontrastu --->
<?php if ( strpos((string)DOMYSLNY_SZABLON, '.rwd.v') > -1 ) { ?>

    <?php if ( isset($_COOKIE['wcagk']) && $_COOKIE['wcagk'] == '1' && WCAG_KONTRAST == 'tak' ) { ?>
    <link rel="stylesheet" type="text/css" href="moduly_stale/wcag/css/wcag_v2.css" />
    <script>
    $(document).ready(function() { UstawKontrast(); });
    </script>
    <?php } ?>

<?php } ?>

<!--- ustawienia okna wcag --->
<link rel="stylesheet" type="text/css" href="moduly_stale/wcag/css/wcag_nawigacja.css" />

<!--- dodatkowe css dla poszczegolnych opcji wcag --->

<!--- wielkosc czcionki --->
<?php if (strpos((string)DOMYSLNY_SZABLON, '.rwd.v') > -1) { ?>

    <?php if ( isset($_COOKIE['wcagf']) && WCAG_WIELKOSC_TEKSTU == 'tak' ) { ?>
    <?php if ( $_COOKIE['wcagf'] == '1.5' ) { ?> <style>body { font-size:110%; }</style> <?php } ?>
    <?php if ( $_COOKIE['wcagf'] == '2' ) { ?> <style>body { font-size:130%; }</style> <?php } ?>
    <?php } ?>

<?php } ?>

<!--- odstep pomiedzy liniami --->
<?php if ( isset($_COOKIE['wcagl']) && WCAG_WYSOKOSC_LINII == 'tak' ) { ?>
<?php if ( $_COOKIE['wcagl'] == '1.5' ) { ?> <style>body, body * { line-height:1.5 !important; }</style> <?php } ?>
<?php if ( $_COOKIE['wcagl'] == '2' ) { ?> <style>body, body * { line-height:2 !important; }</style> <?php } ?>
<?php } ?>

<!--- odstep pomiedzy literami --->
<?php if ( isset($_COOKIE['wcago']) && WCAG_ODSTEP_LITER == 'tak' ) { ?>
<?php if ( $_COOKIE['wcago'] == '1.3' ) { ?> <style>body, body * { letter-spacing:1.3px !important; }</style> <?php } ?>
<?php if ( $_COOKIE['wcago'] == '1.8' ) { ?> <style>body, body * { letter-spacing:1.8px !important; }</style> <?php } ?>
<?php } ?>

<!--- rozmiar kursora --->
<?php if ( isset($_COOKIE['wcagc']) && WCAG_KURSOR == 'tak' ) { ?>
<!--- duzy kursor --->
<?php if ( $_COOKIE['wcagc'] == '1' ) { ?> <style>body, body * { cursor:url('../moduly_stale/wcag/img/kursor.png'), auto; }</style> <?php } ?>
<!--- pasek z ADHD --->
<?php if ( $_COOKIE['wcagc'] == '2' ) { ?> <style>.overlay { pointer-events:none; position:fixed; left:0; width:100%; background:rgba(0,0,0,0.6) !important; pointer-events:auto; z-index:22199999; } body.Kontrast .overlay { background:rgba(255,255,255,0.6) !important; } #overlay-top { top:0; border-bottom:2px solid #000; } #overlay-bottom { bottom:0; border-top:2px solid #000; #adhd-bar-grabber { position:fixed; left:0; width:100vw; background:transparent; z-index:999999; touch-action:none; }}</style> <?php } ?>
<?php } ?>

<!--- tryb szarosci --->
<?php if ( isset($_COOKIE['wcags']) && WCAG_SKALA_SZAROSCI == 'tak' ) { ?>
<?php if ( $_COOKIE['wcags'] == '1' ) { ?> <style>html { filter:grayscale(100%); }</style> <?php } ?>
<?php } ?>

<!--- ukrycie grafik --->
<?php if ( isset($_COOKIE['wcagi']) && WCAG_UKRYCIE_OBRAZOW == 'tak' ) { ?>
<?php if ( $_COOKIE['wcagi'] == '1' ) { ?> <style>img:not(.PasekDostepnosci *):not(.IkonaDostepnosci) { opacity:0 !important; pointer-events:none; } *:not(.PasekDostepnosci *):not(.IkonaDostepnosci), *:not(.PasekDostepnosci *)::before, *:not(.PasekDostepnosci *)::after { background-image:none !important; }</style> <?php } ?>
<?php } ?>

<!--- czytelna czcionka --->
<?php if ( isset($_COOKIE['wcagcz']) && WCAG_CZCIONKA == 'tak' ) { ?>
<?php if ( $_COOKIE['wcagcz'] == '1' ) { ?> <style>body, body * { font-family:Arial,sans-serif !important; }</style> <?php } ?>

<!--- czcionka dla dyslektykow --->
<?php if ( $_COOKIE['wcagcz'] == '2' ) { ?> 
<style>
@font-face {
  font-display: swap; 
  font-family: 'OpenDyslexic';
  font-style: normal;
  font-weight: 400;
  src: url('moduly_stale/wcag/font/OpenDyslexic-Regular.woff2') format('woff2'), 
       url('moduly_stale/wcag/font/OpenDyslexic-Regular.ttf') format('truetype');
}
@font-face {
  font-display: swap; 
  font-family: 'OpenDyslexic';
  font-style: normal;
  font-weight: 600;
  src: url('moduly_stale/wcag/font/OpenDyslexic-Bold.woff2') format('woff2'),
       url('moduly_stale/wcag/font/OpenDyslexic-Bold.ttf') format('truetype'); 
}
@font-face {
  font-display: swap; 
  font-family: 'OpenDyslexic';
  font-style: normal;
  font-weight: 700;
  src: url('moduly_stale/wcag/font/OpenDyslexic-Bold.woff2') format('woff2'),
       url('moduly_stale/wcag/font/OpenDyslexic-Bold.ttf') format('truetype'); 
}
* { font-family: "OpenDyslexic" !important; }
</style>
<?php } ?>    
<?php } ?>

<!--- wylaczenie animacji --->
<?php if ( isset($_COOKIE['wcaga']) && WCAG_ANIMACJE == 'tak' ) { ?>
<?php if ( $_COOKIE['wcaga'] == '1' ) { ?> <style>html.reduce-motion *, html.reduce-motion *::before, html.reduce-motion *::after { animation:none !important;transition: none !important; scroll-behavior:auto !important; }</style> <?php } ?>
<?php } ?>

<!--- wyrownanie tekstu --->
<?php if ( isset($_COOKIE['wcagw']) && WCAG_WYROWNANIE_TEKSTU == 'tak' ) { ?>
<?php if ( $_COOKIE['wcagw'] == '1' ) { ?> <style>* { text-align:left !important; }</style> <?php } ?>
<?php if ( $_COOKIE['wcagw'] == '2' ) { ?> <style>* { text-align:right !important; }</style> <?php } ?>
<?php if ( $_COOKIE['wcagw'] == '3' ) { ?> <style>* { text-align:center !important; }</style> <?php } ?>
<?php } ?>    

<!--- nasycenie --->
<?php if ( isset($_COOKIE['wcagn']) && WCAG_NASYCENIE == 'tak' ) { ?>
<?php if ( $_COOKIE['wcagn'] == '1' ) { ?> <style>.CalaStrona { filter:saturate(150%) !important; }</style> <?php } ?>
<?php if ( $_COOKIE['wcagn'] == '2' ) { ?> <style>.CalaStrona { filter:saturate(250%) !important; }</style>  <?php } ?>
<?php } ?>   

<!--- odnosniki --->
<?php if ( isset($_COOKIE['wcagod']) && WCAG_ODNOSNIKI == 'tak' ) { ?>
<?php if ( $_COOKIE['wcagod'] == '1' ) { ?> <style>a { text-decoration:underline !important; text-decoration-skip-ink:auto !important; }</style> <?php } ?>
<?php } ?> 

<!--- lektor --->
<?php if ( isset($_COOKIE['wcagle']) && WCAG_LEKTOR == 'tak' ) { ?>
<?php if ( $_COOKIE['wcagle'] == '1' ) { ?> <style>div.CzytanieHover { padding-bottom:25px; } a, a *, .DoKoszyka { cursor:pointer !important; }</style> <?php } ?>
<?php } ?> 

<!--- ikona wcag --->
<div class="IkonaDostepnosci" title="{__TLUMACZ:WCAG_USTAWIENIA_DOSTEPNOSCI}" aria-label="{__TLUMACZ:WCAG_USTAWIENIA_DOSTEPNOSCI}" tabindex="0" role="button" onclick="WlaczOknoDostepnosci()"></div>

<!--- wyglad okna wcag --->
<div class="PasekDostepnosci"<?php echo ((isset($_COOKIE['wcag']) && (int)$_COOKIE['wcag'] == 1) ? 'style="display:block"' : ''); ?>>

    <div class="NawigacjaPasekDostepnosci">
    
        <div class="ZamknijDostepnosc">
          
            <span title="Zamknij okno" aria-label="Zamknij okno" tabindex="0" role="button" onclick="WylaczOknoDostepnosci()">{__TLUMACZ:WCAG_ZAMKNIJ}</span>
        
        </div>
    
    </div>

    <div class="PasekDostepnosciWybor">
    
        <?php if (strpos((string)DOMYSLNY_SZABLON, '.rwd.v') > -1) { ?>
        
            <?php if ( WCAG_KONTRAST == 'tak' ) { ?>

                <div class="ZmianaKontrastu PoleKontrastu cmxform<?php echo ((isset($_COOKIE['wcagk']) && $_COOKIE['wcagk'] == '1') ? ' Aktywny' : ''); ?>">
                
                    <div class="DostepnoscNaglowek" aria-label="{__TLUMACZ:WCAG_KONTRAST}">
                    
                        {__TLUMACZ:WCAG_KONTRAST}
                        
                    </div>
                    
                    <div>
                    
                        <select onchange="zmianaKontrastu(this.value)" aria-label="{__TLUMACZ:WCAG_KONTRAST}">
                            <option value="0" <?php echo ((!isset($_COOKIE['wcagk']) || (isset($_COOKIE['wcagk']) && $_COOKIE['wcagk'] == '0')) ? 'selected="selected"' : ''); ?>>{__TLUMACZ:WCAG_DOMYSLNY}</option>
                            <option value="1" <?php echo ((isset($_COOKIE['wcagk']) && $_COOKIE['wcagk'] == '1') ? 'selected="selected"' : ''); ?>>{__TLUMACZ:WCAG_WYSOKI_KONTRAST}</option>
                        </select>  

                    </div>

                </div>
                
            <?php } ?>
            
            <?php if ( WCAG_WIELKOSC_TEKSTU == 'tak' ) { ?>
                
                <div class="ZmianaCzcionki PoleKontrastu cmxform<?php echo ((isset($_COOKIE['wcagf']) && $_COOKIE['wcagf'] != '0') ? ' Aktywny' : ''); ?>">
                
                    <div class="DostepnoscNaglowek" aria-label="{__TLUMACZ:WCAG_WIELKOSC_TEKSTU}">
                    
                        {__TLUMACZ:WCAG_WIELKOSC_TEKSTU}
                        
                    </div>
                    
                    <div>
                    
                        <select onchange="rozmiarFont(this.value)" aria-label="{__TLUMACZ:WCAG_WIELKOSC_TEKSTU}">
                            <option value="0" <?php echo ((!isset($_COOKIE['wcagf']) || (isset($_COOKIE['wcagf']) && $_COOKIE['wcagf'] == '0')) ? 'selected="selected"' : ''); ?>>{__TLUMACZ:WCAG_DOMYSLNA}</option>
                            <option value="1.5" <?php echo ((isset($_COOKIE['wcagf']) && $_COOKIE['wcagf'] == '1.5') ? 'selected="selected"' : ''); ?>>x 1.5</option>
                            <option value="2" <?php echo ((isset($_COOKIE['wcagf']) && $_COOKIE['wcagf'] == '2') ? 'selected="selected"' : ''); ?>>x 2.0</option>
                        </select>  

                    </div>

                </div>
                
            <?php } ?>
            
        <?php } ?>
        
        <?php if ( WCAG_WYSOKOSC_LINII == 'tak' ) { ?>
        
            <div class="ZmianaInterlinia PoleKontrastu cmxform<?php echo ((isset($_COOKIE['wcagl']) && $_COOKIE['wcagl'] != '0') ? ' Aktywny' : ''); ?>">
            
                <div class="DostepnoscNaglowek" aria-label="{__TLUMACZ:WCAG_WYSOKOSC_LINII}">
                
                    {__TLUMACZ:WCAG_WYSOKOSC_LINII}
                    
                </div>
                
                <div>

                    <select onchange="rozmiarInterlinia(this.value)" aria-label="{__TLUMACZ:WCAG_WYSOKOSC_LINII}">
                        <option value="0" <?php echo ((!isset($_COOKIE['wcagl']) || (isset($_COOKIE['wcagl']) && $_COOKIE['wcagl'] == '0')) ? 'selected="selected"' : ''); ?>>{__TLUMACZ:WCAG_DOMYSLNA}</option>
                        <option value="1.5" <?php echo ((isset($_COOKIE['wcagl']) && $_COOKIE['wcagl'] == '1.5') ? 'selected="selected"' : ''); ?>>x 1.5</option>
                        <option value="2" <?php echo ((isset($_COOKIE['wcagl']) && $_COOKIE['wcagl'] == '2') ? 'selected="selected"' : ''); ?>>x 2.0</option>
                    </select>
                    
                </div>
            
            </div>
            
        <?php } ?>
        
        <?php if ( WCAG_ODSTEP_LITER == 'tak' ) { ?>

            <div class="ZmianaOdstepliter PoleKontrastu cmxform<?php echo ((isset($_COOKIE['wcago']) && $_COOKIE['wcago'] != '0') ? ' Aktywny' : ''); ?>">
            
                <div class="DostepnoscNaglowek" aria-label="{__TLUMACZ:WCAG_ODSTEP_LITER}">
                
                    {__TLUMACZ:WCAG_ODSTEP_LITER}
                    
                </div>
                
                <div>
                
                    <select onchange="rozmiarOdstepliter(this.value)" aria-label="{__TLUMACZ:WCAG_ODSTEP_LITER}">
                        <option value="0" <?php echo ((!isset($_COOKIE['wcago']) || (isset($_COOKIE['wcago']) && $_COOKIE['wcago'] == '0')) ? 'selected="selected"' : ''); ?>>{__TLUMACZ:WCAG_DOMYSLNY}</option>
                        <option value="1.3" <?php echo ((isset($_COOKIE['wcago']) && $_COOKIE['wcago'] == '1.3') ? 'selected="selected"' : ''); ?>>x 1.3</option>
                        <option value="1.8" <?php echo ((isset($_COOKIE['wcago']) && $_COOKIE['wcago'] == '1.8') ? 'selected="selected"' : ''); ?>>x 1.8</option>
                    </select>
                    
                </div>
            
            </div>
        
        <?php } ?>
        
        <?php if ( WCAG_KURSOR == 'tak' ) { ?>
        
            <div class="ZmianaKursor PoleKontrastu cmxform<?php echo ((isset($_COOKIE['wcagc']) && $_COOKIE['wcagc'] != '0') ? ' Aktywny' : ''); ?>">
            
                <div class="DostepnoscNaglowek" aria-label="{__TLUMACZ:WCAG_KURSOR}">
                
                    {__TLUMACZ:WCAG_KURSOR}
                    
                </div>
                
                <div>
                
                    <select onchange="rozmiarKursor(this.value)" aria-label="{__TLUMACZ:WCAG_KURSOR}">
                        <option value="0" <?php echo ((!isset($_COOKIE['wcagc']) || (isset($_COOKIE['wcagc']) && $_COOKIE['wcagc'] == '0')) ? 'selected="selected"' : ''); ?>>{__TLUMACZ:WCAG_KURSOR_NORMALNY}</option>
                        <option value="1" <?php echo ((isset($_COOKIE['wcagc']) && $_COOKIE['wcagc'] == '1') ? 'selected="selected"' : ''); ?>>{__TLUMACZ:WCAG_KURSOR_DUZY}</option>
                        <option value="2" <?php echo ((isset($_COOKIE['wcagc']) && $_COOKIE['wcagc'] == '2') ? 'selected="selected"' : ''); ?>>{__TLUMACZ:WCAG_KURSOR_ADHD}</option>
                    </select>
                    
                </div>
            
            </div>           

        <?php } ?>
        
        <?php if ( WCAG_SKALA_SZAROSCI == 'tak' ) { ?>
        
            <div class="ZmianaSzarosci PoleKontrastu cmxform<?php echo ((isset($_COOKIE['wcags']) && $_COOKIE['wcags'] != '0') ? ' Aktywny' : ''); ?>">
            
                <div class="DostepnoscNaglowek" aria-label="{__TLUMACZ:WCAG_SKALA_SZAROSCI}">
                
                    {__TLUMACZ:WCAG_SKALA_SZAROSCI}
                    
                </div>
                
                <div>
                
                    <select onchange="zmianaSzarosci(this.value)" aria-label="{__TLUMACZ:WCAG_SKALA_SZAROSCI}">
                        <option value="0" <?php echo ((!isset($_COOKIE['wcags']) || (isset($_COOKIE['wcags']) && $_COOKIE['wcags'] == '0')) ? 'selected="selected"' : ''); ?>>{__TLUMACZ:WCAG_NORMALNA}</option>
                        <option value="1" <?php echo ((isset($_COOKIE['wcags']) && $_COOKIE['wcags'] == '1') ? 'selected="selected"' : ''); ?>>{__TLUMACZ:WCAG_TRYB_SZAROSCI}</option>
                    </select>
                    
                </div>
            
            </div>   
            
        <?php } ?>
        
        <?php if ( WCAG_UKRYCIE_OBRAZOW == 'tak' ) { ?>
        
            <div class="ZmianaObrazki PoleKontrastu cmxform<?php echo ((isset($_COOKIE['wcagi']) && $_COOKIE['wcagi'] != '0') ? ' Aktywny' : ''); ?>">
            
                <div class="DostepnoscNaglowek" aria-label="{__TLUMACZ:WCAG_UKRYJ_OBRAZY}">
                
                    {__TLUMACZ:WCAG_UKRYJ_OBRAZY}
                    
                </div>
                
                <div>
                
                    <select onchange="zmianaObrazki(this.value)" aria-label="{__TLUMACZ:WCAG_UKRYJ_OBRAZY}">
                        <option value="0" <?php echo ((!isset($_COOKIE['wcagi']) || (isset($_COOKIE['wcagi']) && $_COOKIE['wcagi'] == '0')) ? 'selected="selected"' : ''); ?>>{__TLUMACZ:WCAG_OBRAZY_WIDOCZNE}</option>
                        <option value="1" <?php echo ((isset($_COOKIE['wcagi']) && $_COOKIE['wcagi'] == '1') ? 'selected="selected"' : ''); ?>>{__TLUMACZ:WCAG_UKRYJ_OBRAZY_2}</option>
                    </select>
                    
                </div>
            
            </div>    

        <?php } ?>
        
        <?php if ( WCAG_CZCIONKA == 'tak' ) { ?>
        
        <div class="ZmianaRodzajuCzcionki PoleKontrastu cmxform<?php echo ((isset($_COOKIE['wcagcz']) && $_COOKIE['wcagcz'] != '0') ? ' Aktywny' : ''); ?>">
        
            <div class="DostepnoscNaglowek" aria-label="{__TLUMACZ:WCAG_CZCIONKA}">
            
                {__TLUMACZ:WCAG_CZCIONKA}
                
            </div>
            
            <div>
            
                <select onchange="zmianaRodzajuCzcionki(this.value)" aria-label="{__TLUMACZ:WCAG_CZCIONKA}">
                    <option value="0" <?php echo ((!isset($_COOKIE['wcagcz']) || (isset($_COOKIE['wcagcz']) && $_COOKIE['wcagcz'] == '0')) ? 'selected="selected"' : ''); ?>>{__TLUMACZ:WCAG_DOMYSLNA}</option>
                    <option value="1" <?php echo ((isset($_COOKIE['wcagcz']) && $_COOKIE['wcagcz'] == '1') ? 'selected="selected"' : ''); ?>>{__TLUMACZ:WCAG_CZYTELNA}</option>
                    <option value="2" <?php echo ((isset($_COOKIE['wcagcz']) && $_COOKIE['wcagcz'] == '2') ? 'selected="selected"' : ''); ?>>{__TLUMACZ:WCAG_DLA_DYSLEKTYKOW}</option>
                </select>
                
            </div>
        
        </div>  
        
        <?php } ?>
        
        <?php if ( WCAG_ANIMACJE == 'tak' ) { ?>
        
            <div class="ZmianaAnimacji PoleKontrastu cmxform<?php echo ((isset($_COOKIE['wcaga']) && $_COOKIE['wcaga'] != '0') ? ' Aktywny' : ''); ?>">
            
                <div class="DostepnoscNaglowek" aria-label="{__TLUMACZ:WCAG_ANIMACJE}">
                
                    {__TLUMACZ:WCAG_ANIMACJE}
                    
                </div>
                
                <div>
                
                    <select onchange="zmianaAnimacji(this.value)" aria-label="{__TLUMACZ:WCAG_ANIMACJE}">
                        <option value="0" <?php echo ((!isset($_COOKIE['wcaga']) || (isset($_COOKIE['wcaga']) && $_COOKIE['wcaga'] == '0')) ? 'selected="selected"' : ''); ?>>{__TLUMACZ:WCAG_WLACZONE}</option>
                        <option value="1" <?php echo ((isset($_COOKIE['wcaga']) && $_COOKIE['wcaga'] == '1') ? 'selected="selected"' : ''); ?>>{__TLUMACZ:WCAG_WYLACZ_ANIMACJE}</option>
                    </select>
                    
                </div>
            
            </div>     
            
        <?php } ?>
        
        <?php if ( WCAG_WYROWNANIE_TEKSTU == 'tak' ) { ?>

            <div class="ZmianaWyrownanieTekstu PoleKontrastu cmxform<?php echo ((isset($_COOKIE['wcagw']) && $_COOKIE['wcagw'] != '0') ? ' Aktywny' : ''); ?>">
            
                <div class="DostepnoscNaglowek" aria-label="{__TLUMACZ:WCAG_WYROWNANIE_TEKSTU}">
                
                    {__TLUMACZ:WCAG_WYROWNANIE_TEKSTU}
                    
                </div>
                
                <div>
                
                    <select onchange="zmianaWyrownanieTekstu(this.value)" aria-label="{__TLUMACZ:WCAG_WYROWNANIE_TEKSTU}">
                        <option value="0" <?php echo ((!isset($_COOKIE['wcagw']) || (isset($_COOKIE['wcagw']) && $_COOKIE['wcagw'] == '0')) ? 'selected="selected"' : ''); ?>>{__TLUMACZ:WCAG_DOMYSLNE}</option>
                        <option value="1" <?php echo ((isset($_COOKIE['wcagw']) && $_COOKIE['wcagw'] == '1') ? 'selected="selected"' : ''); ?>>{__TLUMACZ:WCAG_DO_LEWEJ}</option>
                        <option value="2" <?php echo ((isset($_COOKIE['wcagw']) && $_COOKIE['wcagw'] == '2') ? 'selected="selected"' : ''); ?>>{__TLUMACZ:WCAG_DO_PRAWEJ}</option>
                        <option value="3" <?php echo ((isset($_COOKIE['wcagw']) && $_COOKIE['wcagw'] == '3') ? 'selected="selected"' : ''); ?>>{__TLUMACZ:WCAG_DO_SRODKA}</option>
                    </select>
                    
                </div>
            
            </div>      
            
        <?php } ?>

        <?php if (strpos((string)DOMYSLNY_SZABLON, '.rwd.v') > -1) { ?>     

            <?php if ( WCAG_NASYCENIE == 'tak' ) { ?>

                <div class="ZmianaNascenie PoleKontrastu cmxform<?php echo ((isset($_COOKIE['wcagn']) && $_COOKIE['wcagn'] != '0') ? ' Aktywny' : ''); ?>">
                
                    <div class="DostepnoscNaglowek" aria-label="{__TLUMACZ:WCAG_NASYCENIE}">
                    
                        {__TLUMACZ:WCAG_NASYCENIE}
                        
                    </div>
                    
                    <div>
                    
                        <select onchange="zmianaNasycenie(this.value)" aria-label="{__TLUMACZ:WCAG_NASYCENIE}">
                            <option value="0" <?php echo ((!isset($_COOKIE['wcagn']) || (isset($_COOKIE['wcagn']) && $_COOKIE['wcagn'] == '0')) ? 'selected="selected"' : ''); ?>>{__TLUMACZ:WCAG_DOMYSLNE}</option>
                            <option value="1" <?php echo ((isset($_COOKIE['wcagn']) && $_COOKIE['wcagn'] == '1') ? 'selected="selected"' : ''); ?>>{__TLUMACZ:WCAG_NASYCENIE_SREDNIE}</option>
                            <option value="2" <?php echo ((isset($_COOKIE['wcagn']) && $_COOKIE['wcagn'] == '2') ? 'selected="selected"' : ''); ?>>{__TLUMACZ:WCAG_NASYCENIE_WYSOKIE}</option>
                        </select>
                        
                    </div>
                
                </div>         
                
            <?php } ?>
        
        <?php } ?>
        
        <?php if ( WCAG_ODNOSNIKI == 'tak' ) { ?>

            <div class="ZmianaOdnosniki PoleKontrastu cmxform<?php echo ((isset($_COOKIE['wcagod']) && $_COOKIE['wcagod'] != '0') ? ' Aktywny' : ''); ?>">
            
                <div class="DostepnoscNaglowek" aria-label="{__TLUMACZ:WCAG_ODNOSNIKI}">
                
                    {__TLUMACZ:WCAG_ODNOSNIKI}
                    
                </div>
                
                <div>
                
                    <select onchange="zmianaOdnosniki(this.value)" aria-label="{__TLUMACZ:WCAG_ODNOSNIKI}">
                        <option value="0" <?php echo ((!isset($_COOKIE['wcagod']) || (isset($_COOKIE['wcagod']) && $_COOKIE['wcagod'] == '0')) ? 'selected="selected"' : ''); ?>>{__TLUMACZ:WCAG_DOMYSLNE}</option>
                        <option value="1" <?php echo ((isset($_COOKIE['wcagod']) && $_COOKIE['wcagod'] == '1') ? 'selected="selected"' : ''); ?>>{__TLUMACZ:WCAG_PODKRESL}</option>
                    </select>
                    
                </div>
            
            </div>    
        
        <?php } ?>
        
        <?php if ( WCAG_LEKTOR == 'tak' ) { ?>

            <div class="ZmianaCzytnikEkranu PoleKontrastu cmxform<?php echo ((isset($_COOKIE['wcagle']) && $_COOKIE['wcagle'] != '0') ? ' Aktywny' : ''); ?>">
            
                <div class="DostepnoscNaglowek" aria-label="{__TLUMACZ:CZYTNIK_EKRANU}">
                
                    {__TLUMACZ:WCAG_CZYTNIK_EKRANU}
                    
                </div>
                
                <div>
                
                    <select onchange="zmianaCzytnikEkranu(this.value)" aria-label="{__TLUMACZ:CZYTNIK_EKRANU}">
                        <option value="0" <?php echo ((!isset($_COOKIE['wcagle']) || (isset($_COOKIE['wcagle']) && $_COOKIE['wcagle'] == '0')) ? 'selected="selected"' : ''); ?>>{__TLUMACZ:WCAG_WYLACZONY}</option>
                        <option value="1" <?php echo ((isset($_COOKIE['wcagle']) && $_COOKIE['wcagle'] == '1') ? 'selected="selected"' : ''); ?>>{__TLUMACZ:WCAG_WLACZONY}</option>
                    </select>
                    
                </div>
            
            </div>     

        <?php } ?>
        
    </div>
  
    <div class="ResetPasekDostepnosci">
    
        <span aria-label="{__TLUMACZ:WCAG_RESET}" tabindex="0" role="button" onclick="ResetOknoDostepnosci()">{__TLUMACZ:WCAG_RESET}</span>
        
    </div>
    
</div>