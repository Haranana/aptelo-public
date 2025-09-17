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
    
    <div id="caly_listing">
    
        <div id="ajax"></div>
    
        <div id="naglowek_cont">Kategorie</div>
        
        <?php 
        if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] > 0) {
            $sciezka = Kategorie::SciezkaKategoriiId((int)$_GET['id_poz'], 'categories');
            $cSciezka = explode("_", (string)$sciezka);
           } else {
            $cSciezka = array();
        }
        ?>
        
        <script>    
        $.ajaxSetup({
            async: true
        });
        
        function podkategorie(id,spr,sciezka) {
          
            $('#p_'+id).after('<tr id="LadowanieKategorii"><td colspan="12"><img src="obrazki/_loader.gif"></td></tr>');
            
            $.get("kategorie/podkategorie.php", { pole: id, sciezka: sciezka, id_poz: <?php echo ((count($cSciezka) > 1) ? (int)$cSciezka[count($cSciezka)-1] : "'0'"); ?>, rozwin: spr, tok: '<?php echo Sesje::Token(); ?>' }, 
                function(data) { 
                
                    $('#LadowanieKategorii').remove();
                    $('#p_'+id).after(data);
                    //
                    if ( spr == true ) {
                        wskaznik++;
                        if ( wskaznik < powrot.length - 1 ) {
                             podkategorie(powrot[wskaznik],spr,sciezka + powrot[wskaznik - 1] + '_');
                        }
                    }
                    //
                    $('#img_'+id).html('<img src="obrazki/zwin.png" onclick="podkategorie_off('+ "'" + id + "'" +',\'' + sciezka + '\')" alt="Zwiń" />');
                    //
                    // usuniecie podswietlania pozycji w listingu po edycji
                    $(".pozycja_on").hover(
                    function(){      
                        $(this).removeClass('pozycja_on').addClass('pozycja_off');
                    });                     
                    //
                    <?php 
                    if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] > 0) {                 
                        if (count($cSciezka) > 1) {
                        ?>    
                        if (spr == true && $('#img_'+<?php echo $cSciezka[count($cSciezka)-1]; ?>).length) {
                            $.scrollTo('#img_'+<?php echo $cSciezka[count($cSciezka)-1]; ?>, 200);
                        }
                        <?php
                        }
                    } ?>  
                    
                    pokazChmurki();                       

            });
            
        }
        function podkategorie_off(id,sciezka) {
            //
            $('.listing_tbl tr').each(function() {
                //
                sciezka_kategorii = $(this).attr('data-sciezka');
                if ( sciezka_kategorii ) {
                    //
                    tb_sciezka = sciezka_kategorii.split('_');
                    //
                    for ( x = 0; x < tb_sciezka.length; x++ ) {
                        //
                        if ( tb_sciezka[x] == id ) {
                            //
                            $(this).hide();
                            //
                        }
                        //
                    }
                    //
                    delete td_sciezka;
                    //
                }
                delete sciezka_kategorii;
                //
            });
            //
            $('#img_'+id).html('<img src="obrazki/rozwin.png" onclick="podkategorie('+ "'" + id + "'" +',\'false\',\'' + sciezka + '\')" alt="Rozwiń" />'); 
        }
        </script>         
        
        <?php
        // przycisk dodania nowegj kategorii
        ?>
        <div id="PozycjeIkon">
            <div>
                <a class="dodaj" href="kategorie/kategorie_dodaj.php">dodaj nową kategorię</a>
            </div>
            <div>
                <a class="usun" href="kategorie/kategorie_usun_wszystkie.php">usuń wszystkie kategorie</a>
            </div>              
        </div>

        <div id="IloscKat">Ilość wszystkich kategorii: 
        <?php
        $zapytanie = "select categories_id from categories";
        $sql = $db->open_query($zapytanie);
        $ile_pozycji = (int)$db->ile_rekordow($sql); // ile jest wszystkich kategorii
        $db->close_query($sql); 
        echo '<span>'.$ile_pozycji.'</span>';
        unset($ile_pozycji);
        // ilosc aktywnych
        $zapytanie = "select categories_id from categories where categories_status = '1'";
        $sql = $db->open_query($zapytanie);
        $ile_pozycji = (int)$db->ile_rekordow($sql); // ile jest wszystkich kategorii
        $db->close_query($sql); 
        echo ' , w tym aktywnych: <span>'.$ile_pozycji.'</span>';
        unset($ile_pozycji);       
        // ilosc nieaktywnych
        $zapytanie = "select categories_id from categories where categories_status = '0'";
        $sql = $db->open_query($zapytanie);
        $ile_pozycji = (int)$db->ile_rekordow($sql); // ile jest wszystkich kategorii
        $db->close_query($sql); 
        echo ' , w tym nieaktywnych: <span>'.$ile_pozycji.'</span>';
        unset($ile_pozycji);         
        ?>
        </div>        
        
        <div class="cl"></div>
        
        <form action="kategorie/kategorie_akcja.php<?php echo Funkcje::Zwroc_Get(array('id_poz','zakres')); ?>" method="post" class="cmxform">
        
        <div id="wynik_zapytania"></div>
        
        <script>        
        $('#ekr_preloader').css('display','block');
        $.get("kategorie/kategorie_lista.php", 
            { id_poz: <?php echo ((isset($_GET['id_poz'])) ? (int)$_GET['id_poz'] : 0); ?>, tok: '<?php echo Sesje::Token(); ?>' },
            function(data) { 
                $('#wynik_zapytania').html(data);
                $('#ekr_preloader').delay(100).fadeOut('fast');
                
                // usuniecie podswietlania pozycji w listingu po edycji
                $(".pozycja_on").hover(
                function(){      
                    $(this).removeClass('pozycja_on').addClass('pozycja_off');
                });                 
                
                <?php 
                if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] > 0) {               
                    if (count($cSciezka) > 1) { ?> 
                    powrot = new Array(<?php echo implode(',', (array)$cSciezka); ?>);
                    wskaznik = 0;
                    podkategorie('<?php echo $cSciezka[0]; ?>',true, '');
                    <?php } else { ?>
                    $.scrollTo('#img_<?php echo (int)$_GET['id_poz']; ?>', 200);
                    <?php
                    }
                }
                ?>   
                if (data.indexOf("pozycja_off") > 0) {
                    $('#ZapiszKategorie').show();
                }
                
                pokazChmurki();                    

        });     
        </script> 
        
        <div id="ZapiszKategorie"><input type="submit" style="float:right" class="przyciskNon" value="Zapisz zmiany" /></div>
        
        <div class="cl"></div>

        </form>
        
    </div>     

    <?php include('stopka.inc.php'); ?>

<?php } ?>
