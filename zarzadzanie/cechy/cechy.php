<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    
    if ( isset($_GET['sort']) ) {
         $_SESSION['filtry'][ 'cechy.php' ]['sort'] = $filtr->process($_GET['sort']);   
    }
    
    ?>
    
    <div id="caly_listing">

        <div id="ajax"></div>

        <div id="naglowek_cont">Zarządzenie cechami produktów</div>
        
        <div id="sortowanie">
            <span>Sortowanie: </span>
            <a id="sort_a1" class="sortowanie" href="cechy/cechy.php?sort=sort_a1">nazwy rosnąco</a>
            <a id="sort_a2" class="sortowanie" href="cechy/cechy.php?sort=sort_a2">nazwy malejąco</a>     
            <a id="sort_a3" class="sortowanie" href="cechy/cechy.php?sort=sort_a3">kolejność rosnąco</a>
            <a id="sort_a4" class="sortowanie" href="cechy/cechy.php?sort=sort_a4">kolejność malejąco</a>                      
        </div>    
        
        <div id="IdWartosci"></div>
        
        <div class="GlownyListing">

            <div class="CechDodaj">
            
                <div>
                    <a class="dodaj" href="cechy/cechy_nazwy_dodaj.php">dodaj nową cechę</a>
                </div>            
            
            </div>
            
            <div class="CechDodaj">
            
                <div id="cechy_wartosci_dodawanie" style="display:none">
                   <a class="dodaj" href="javascript:dodaj_wartosc()">dodaj nową wartość cechy</a> 
                </div>
                
            </div>
            
        </div>
            
        <div class="GlownyListing">
            
            <div class="CechDodaj" id="cechy_nazwy">
            
                <script>
                $("#cechy_nazwy").html('<img src="obrazki/_loader.gif" />');
                $.get('cechy/cechy_nazwy.php?tok=<?php echo Sesje::Token(); ?>', function(data) { 
                     $('#cechy_nazwy').html(data); <?php echo ((isset($_GET['id_cechy']) && (int)$_GET['id_cechy'] > 0) ? 'pokaz_wartosci_cechy("'.$filtr->process($_GET['id_cechy']).'");' : ''); ?>  
                     pokazChmurki();                            
                });                       
                </script>          
                
            </div>
            
            <div class="CechDodaj" id="cechy_wartosci_td">
            
                <div id="cechy_wartosci">
                    <div id="BrakCech">Nie wybrano cechy ...</div>                
                </div>
                
            </div>

        </div>
        
    </div>        

    <?php include('stopka.inc.php'); ?>

<?php } ?>