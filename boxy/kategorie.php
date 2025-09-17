<?php
// /* dodatkowe ustawienia konfiguracyjne */
//
// {{BOX_KATEGORIE_NOWOSCI;Czy oprócz kategorii produktów wyświetlać na końcu boxu link do nowości;tak;tak,nie}}
// {{BOX_KATEGORIE_PROMOCJE;Czy oprócz kategorii produktów wyświetlać na końcu boxu link do promocji;tak;tak,nie}}
// {{BOX_KATEGORIE_POLECANE;Czy oprócz kategorii produktów wyświetlać na końcu boxu link do produktów polecanych;tak;tak,nie}}
// {{BOX_KATEGORIE_HITY;Czy oprócz kategorii produktów wyświetlać na końcu boxu link do hitów;tak;tak,nie}}
// {{BOX_KATEGORIE_IKONKI;Czy wyświetlać ikonki przypisane do kategorii ?;nie;tak,nie}}
//

if ( defined('BOX_KATEGORIE_NOWOSCI') ) {
   $DodatkowoNowosci = BOX_KATEGORIE_NOWOSCI;
 } else {
   $DodatkowoNowosci = 'nie';
}
if ( defined('BOX_KATEGORIE_PROMOCJE') ) {
   $DodatkowoPromocje = BOX_KATEGORIE_PROMOCJE;
 } else {
   $DodatkowoPromocje = 'nie';
}
if ( defined('BOX_KATEGORIE_POLECANE') ) {
   $DodatkowoPolecane = BOX_KATEGORIE_POLECANE;
 } else {
   $DodatkowoPolecane = 'nie';
}
if ( defined('BOX_KATEGORIE_HITY') ) {
   $DodatkowoHity = BOX_KATEGORIE_HITY;
 } else {
   $DodatkowoHity = 'nie';
}
if ( defined('BOX_KATEGORIE_IKONKI') ) {
   $WyswietlajIkonki = BOX_KATEGORIE_IKONKI;
 } else {
   $WyswietlajIkonki = 'nie';
}

echo '<ul>';

$PodzialGet = array();
$GlebokoscDrzewa = 0;

if (isset($_GET['idkat'])) {

    $PodzialGet = explode('_', (string)$_GET['idkat']);
    $GlebokoscDrzewa = count($PodzialGet);

}

foreach(Kategorie::DrzewoKategorii() as $IdKategorii => $Tablica) {
    //
	  echo Kategorie::WyswietlKategorie($IdKategorii, $Tablica, $PodzialGet, $GlebokoscDrzewa, '', 'Aktywna', '_', '', '', array(), $WyswietlajIkonki);
    //
}

if ( $DodatkowoNowosci == 'tak' ) {
    //
    echo '<li><a ' . (($_SERVER['REQUEST_URI'] == '/nowosci.html') ? 'class="Aktywna"' : '') . ' href="nowosci.html">{__TLUMACZ:NAGLOWEK_NOWOSCI}';
    
    // ilosc nowosci
    if (LISTING_ILOSC_PRODUKTOW == 'tak') {
        echo '<em>(' . count(Produkty::ProduktyModulowe(99999, 'nowosci')) . ')</em>';
    }
    
    echo '</a></li>';
    //
}
if ( $DodatkowoPromocje == 'tak' ) {
    //
    echo '<li><a ' . (($_SERVER['REQUEST_URI'] == '/promocje.html') ? 'class="Aktywna"' : '') . ' href="promocje.html">{__TLUMACZ:NAGLOWEK_PROMOCJE}';
    
    // ilosc promocji
    if (LISTING_ILOSC_PRODUKTOW == 'tak') {
        echo '<em>(' . count(Produkty::ProduktyModulowe(99999, 'promocje')) . ')</em>';
    }    
    
    echo '</a></li>';
    //
}
if ( $DodatkowoPolecane == 'tak' ) {
    //
    echo '<li><a ' . (($_SERVER['REQUEST_URI'] == '/polecane.html') ? 'class="Aktywna"' : '') . ' href="polecane.html">{__TLUMACZ:NAGLOWEK_POLECANE}';
    
    // ilosc polecanych
    if (LISTING_ILOSC_PRODUKTOW == 'tak') {
        echo '<em>(' . count(Produkty::ProduktyModulowe(99999, 'polecane')) . ')</em>';
    }    
    
    echo '</a></li>';
    //
}
if ( $DodatkowoHity == 'tak' ) {
    //
    echo '<li><a ' . (($_SERVER['REQUEST_URI'] == '/hity.html') ? 'class="Aktywna"' : '') . ' href="hity.html">{__TLUMACZ:NAGLOWEK_HITY}';
    
    // ilosc hitow
    if (LISTING_ILOSC_PRODUKTOW == 'tak') {
        echo '<em>(' . count(Produkty::ProduktyModulowe(99999, 'hity')) . ')</em>';
    }    
    
    echo '</a></li>';
    //
}

echo '</ul>';

unset($DodatkowoNowosci, $DodatkowoPromocje, $DodatkowoPolecane, $DodatkowoHity, $GlebokoscDrzewa, $PodzialGet, $WyswietlajIkonki); 

?>