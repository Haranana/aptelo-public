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

    <div id="naglowek_cont">Produkt dnia</div>
    
    <div class="WyborMiesiaca">
    
        <form class="cmxform" id="form_miesiac" action="produkt_dnia/produkt_dnia.php">
    
            <strong>Wybierz miesiąc oraz rok</strong> &nbsp;
            
            <?php
            $aktualny_miesiac = date('n', time());
            $aktualny_rok = date('Y', time());
            
            if ( isset($_GET['miesiac']) && (int)$_GET['miesiac'] > 0 && (int)$_GET['miesiac'] < 13 ) {
                 $aktualny_miesiac = (int)$_GET['miesiac'];
            }
            if ( isset($_GET['rok']) && (int)$_GET['rok'] > 0 && (int)$_GET['rok'] > (int)date('Y', time()) - 1 ) {
                 $aktualny_rok = (int)$_GET['rok'];
            }            
            ?>
            
            <select name="miesiac" onchange="$('#form_miesiac').submit()">
                <option value="1" <?php echo (($aktualny_miesiac == 1) ? ' selected="selected"' : ''); ?>>styczeń</option>
                <option value="2" <?php echo (($aktualny_miesiac == 2) ? ' selected="selected"' : ''); ?>>luty</option>
                <option value="3" <?php echo (($aktualny_miesiac == 3) ? ' selected="selected"' : ''); ?>>marzec</option>
                <option value="4" <?php echo (($aktualny_miesiac == 4) ? ' selected="selected"' : ''); ?>>kwiecień</option>
                <option value="5" <?php echo (($aktualny_miesiac == 5) ? ' selected="selected"' : ''); ?>>maj</option>
                <option value="6" <?php echo (($aktualny_miesiac == 6) ? ' selected="selected"' : ''); ?>>czerwiec</option>
                <option value="7" <?php echo (($aktualny_miesiac == 7) ? ' selected="selected"' : ''); ?>>lipiec</option>
                <option value="8" <?php echo (($aktualny_miesiac == 8) ? ' selected="selected"' : ''); ?>>sierpień</option>
                <option value="9" <?php echo (($aktualny_miesiac == 9) ? ' selected="selected"' : ''); ?>>wrzesień</option>
                <option value="10" <?php echo (($aktualny_miesiac == 10) ? ' selected="selected"' : ''); ?>>październik</option>
                <option value="11" <?php echo (($aktualny_miesiac == 11) ? ' selected="selected"' : ''); ?>>listopad</option>
                <option value="12" <?php echo (($aktualny_miesiac == 12) ? ' selected="selected"' : ''); ?>>grudzień</option>
            </select>
            
            &nbsp;
            
            <select name="rok" onchange="$('#form_miesiac').submit()">
                <?php
                for ($x = 0; $x < 5; $x++) {
                    echo '<option value="' . ((int)date('Y', time()) + $x) . '" ' . ((((int)date('Y', time()) + $x) == $aktualny_rok) ? 'selected="selected"' : '') . '>' . ((int)date('Y', time()) + $x) . '</option>';
                }
                ?>
            </select>            
            
        </form>
    
    </div>
    
    <script>
    $(document).ready(function() {
        //
        $('.EdycjaProduktu').click(function() {
           //
           $('#NazwaDnia b').html( $(this).attr('data-dzien') );
           $('#wybrana_data').val( $(this).attr('data-dzien') );
           $('#WyborProduktu').stop().slideDown();
           //
           $.scrollTo('#naglowek_cont',400);
           //
        });
        //
        $('#WyborZamknij').click(function() {
           //
           $('#WyborProduktu').stop().slideUp();
           //
           $('#ProduktWybrany').html('');
           $('#ProduktyLista').html('');
           $('#SzukanieProduktu').show();
           $('#szukany').val('');           
           //
        });
        //
    });
    
    function WyswietlSzukaneProdukty() {
        //
        var data_wybrana = $('#wybrana_data').val();
        //
        if ( $('#ProduktyLista').html() != '' || $('#szukany').val() != '' ) {
             var fraza = $('#szukany').val();
             if ( fraza.length < 2 ) {
                 $.colorbox( { html:'<div id="PopUpInfo">Minimalna ilość znaków do wyszukiwania to 2</div>', initialWidth:50, initialHeight:50, maxWidth:'90%', maxHeight:'90%' } );
                 return false;
             }
        
             if ($('#ProduktyLista').length) {
                //
                $('#ProduktyLista').css('display','block');
                $('#ProduktyLista').html('<img src="obrazki/_loader.gif">');
                $.get("ajax/lista_produktow_produkt_dnia.php", 
                    { fraza: $('#szukany').val(), wybrana_data: data_wybrana, tok: $('#tok').val() },
                    function(data) { 
                        $('#ProduktyLista').css('display','none');
                        $('#ProduktyLista').html(data);
                        $('#ProduktyLista').css('display','block');    
                        pokazChmurki();
                });    
                //
             }
        }
        //
    };
    
    function DodajDoDnia(id_produktu, data_wybrana) {
        //
        if ($('#ProduktWybrany').length) {
           //
           $('#ProduktWybrany').css('display','block');
           $('#ProduktWybrany').html('<img src="obrazki/_loader.gif">');
           $.get("ajax/lista_produktow_produkt_dnia.php", 
               { id_produktu: id_produktu, wybrana_data: data_wybrana, tok: $('#tok').val() },
               function(data) { 
                   $('#SzukanieProduktu').hide();
                   $('#ProduktyLista').hide();
                   //
                   $('#ProduktWybrany').css('display','none');
                   $('#ProduktWybrany').html(data);
                   $('#ProduktWybrany').css('display','block');    
                   pokazChmurki();
                   //
                   $(".calkowitaPelna").change(	
                       function () {
                           if (isNaN($(this).val())) {
                               $(this).val('1');
                              } else {
                               if ( isNaN(parseInt($(this).val())) ) {
                                   $(this).val('1');
                                 } else {
                                   $(this).val( parseInt($(this).val()) );
                               }
                           }
                       }
                   );                     
           });    
           //
        }
        //
    };    
    
    function ZapiszProduktDnia(id_produktu) {
        //
        var wybrana_data = $('#wybrana_data').val();
        var rabat = $('#rabat_procentowy').val();
        var ilosc = $('#dostepna_ilosc').val();
        //
        $('#' + wybrana_data + ' span').html('<img style="margin-top:15px" src="obrazki/_loader.gif">');
        $.get("ajax/produkt_dnia_wybor.php", 
            { id_produktu: id_produktu, wybrana_data: wybrana_data, rabat: rabat, ilosc: ilosc, tok: $('#tok').val() },
            function(data) { 
                $('#' + wybrana_data + ' span').css('display','none').css('margin','0px').removeClass('BrakProduktu');
                $('#' + wybrana_data + ' span').html(data);
                $('#' + wybrana_data + ' span').css('display','block');  
                $('#' + wybrana_data + ' .UsuniecieProduktu').show();                
                //
                $('#ProduktWybrany').html('');
                $('#ProduktyLista').html('');
                $('#WyborProduktu').hide();
                $('#SzukanieProduktu').show();
                $('#szukany').val('');
        });          
        //
    }
    
    function UsunProduktDnia(data_produktu) {
        //
        $('#' + data_produktu + ' span').html('<img style="margin-top:15px" src="obrazki/_loader.gif">');
        $.get("ajax/produkt_dnia_wybor.php", 
            { wybrana_data: data_produktu, kasuj: 'tak', tok: $('#tok').val() },
            function(data) { 
                $('#' + data_produktu + ' span').css('display','none').css('margin-top','10px').addClass('BrakProduktu');
                $('#' + data_produktu + ' span').html(data);
                $('#' + data_produktu + ' span').css('display','block');    
                //
                $('#' + data_produktu + ' .UsuniecieProduktu').hide();
                //
        });          
        //
    }    
    </script>    
    
    <div id="WyborProduktu">
    
        <div id="NazwaDnia">Wybór produktu na dzień: <b>11-09-2017</b></div>
        
        <div id="WyborZamknij"></div>
    
        <form class="cmxform" id="form_produkt" action="produkt_dnia/produkt_dnia.php">
        
            <div id="ProduktWybrany"></div>
        
            <input type="hidden" id="wybrana_data" value="" />
        
            <div id="SzukanieProduktu">
                <div>Wyszukaj produkt: <input type="text" id="szukany" size="25" value="" /><em class="TipIkona"><b>Wpisz nazwę produktu, nr katalogowy lub kod producenta</b></em></div><span onclick="WyswietlSzukaneProdukty()"></span>
                <div class="cl"></div>
            </div>  

            <div id="ProduktyLista"></div>  

        </form>
        
    </div>
    
    <div class="RamkaNaglowek">
    
        <div>Niedziela</div>
        <div>Poniedziałek</div>
        <div>Wtorek</div>
        <div>Środa</div>
        <div>Czwartek</div>
        <div>Piątek</div>
        <div>Sobota</div>
    
    </div>
    
    <div class="RamkaKalendarz">
    
        <?php
        $tablica = array();        
        
        $zapytanie = 'SELECT DISTINCT
                             p.products_id, 
                             p.products_image,
                             pd.products_name,
                             py.date_day,
                             py.products_discount
                        FROM products_day py, products p, products_description pd, products_jm pj
                       WHERE py.products_id = p.products_id AND pd.products_id = p.products_id
                         AND pd.language_id = "' . (int)$_SESSION['domyslny_jezyk']['id'] . '"';       
                         
        $sql = $db->open_query($zapytanie);
        
        while ( $info = $sql->fetch_assoc() ) {
            //
            $tablica[ date('d-m-Y', FunkcjeWlasnePHP::my_strtotime($info['date_day'])) ] = $info;
            //
        }
        
        $GLOBALS['db']->close_query($sql); 
        unset($info, $zapytanie);           
        
        $miesiac = '';
        if ( $aktualny_miesiac == 1 ) { $miesiac = 'styczeń'; }
        if ( $aktualny_miesiac == 2 ) { $miesiac = 'luty'; }
        if ( $aktualny_miesiac == 3 ) { $miesiac = 'marzec'; }
        if ( $aktualny_miesiac == 4 ) { $miesiac = 'kwiecień'; }
        if ( $aktualny_miesiac == 5 ) { $miesiac = 'maj'; }
        if ( $aktualny_miesiac == 6 ) { $miesiac = 'czerwiec'; }
        if ( $aktualny_miesiac == 7 ) { $miesiac = 'lipiec'; }
        if ( $aktualny_miesiac == 8 ) { $miesiac = 'sierpień'; }
        if ( $aktualny_miesiac == 9 ) { $miesiac = 'wrzesień'; }
        if ( $aktualny_miesiac == 10 ) { $miesiac = 'październik'; }
        if ( $aktualny_miesiac == 11 ) { $miesiac = 'listopad'; }
        if ( $aktualny_miesiac == 12 ) { $miesiac = 'grudzień'; }
        
        // jaki jest dzien dla pierwszego dnia tygodnia
        $pierwszy = date('N', FunkcjeWlasnePHP::my_strtotime('01-' . $aktualny_miesiac . '-' . $aktualny_rok));
        
        $suma_dni = 0;
        
        if ( $pierwszy < 7 ) {
          
            for ( $x = 0; $x < $pierwszy; $x++ ) {
                  //
                  echo '<div class="PoleDnia Pusty"><b class="NrDnia">&nbsp;</b><small class="NazwaMiesiaca">&nbsp;</small><div class="DaneProduktu"></div></div>';
                  $suma_dni++;
                  //
            }
            
        }
        
        for ( $x = 1; $x <= date('t', FunkcjeWlasnePHP::my_strtotime('01-' . $aktualny_miesiac . '-' . $aktualny_rok) ); $x++ ) {
              //
              $data_dnia = date('d-m-Y', FunkcjeWlasnePHP::my_strtotime($x . '-' . $aktualny_miesiac . '-' . $aktualny_rok));
              //
              echo '<div class="PoleDnia" id="' . $data_dnia . '">
                        <b class="NrDnia' . ((date('N', FunkcjeWlasnePHP::my_strtotime($x . '-' . $aktualny_miesiac . '-' . $aktualny_rok)) == 7) ? ' Czerwony' : '') . '">' . $x . '</b>
                        <small class="NazwaMiesiaca">' . $miesiac . '</small>
                        <div class="DaneProduktu">
                            <div class="EdycjaProduktu" data-dzien="' . $data_dnia . '"></div>
                            <div class="UsuniecieProduktu" ' . ((!isset($tablica[$data_dnia])) ? 'style="display:none"' : '') . ' onclick="UsunProduktDnia(\'' . $data_dnia . '\')"></div>';
                            
                            if ( !isset($tablica[$data_dnia]) ) {
                                 //
                                 echo '<span class="BrakProduktu">Nie <br /> wybrano <br /> produktu</span>';
                                 //
                            } else {
                                 //
                                 echo '<span><table class="TabelaProduktInfo"><tr>';
                                 
                                 if ( !empty($tablica[$data_dnia]['products_image']) ) {
                                      //
                                      echo '<td><div id="zoom'.rand(1,99999).'" class="imgzoom" onmouseover="ZoomIn(this,event)" onmouseout="ZoomOut(this)">';
                                      echo '<div class="zoom" id="duze_foto_' . $tablica[$data_dnia]['products_id'] . '">' . Funkcje::pokazObrazek($tablica[$data_dnia]['products_image'], $tablica[$data_dnia]['products_name'], '250', '250') . '</div>';
                                      echo Funkcje::pokazObrazek($tablica[$data_dnia]['products_image'], $tablica[$data_dnia]['products_name'], '40', '40');
                                      echo '</div></td>';
                                      //
                                 }

                                 echo '<td>';
                                 
                                     echo '<div class="NazwaPrd" title="' . $tablica[$data_dnia]['products_name'] . '"><a href="/zarzadzanie/produkty/produkty_edytuj.php?id_poz=' . $tablica[$data_dnia]['products_id'] . '">' . $tablica[$data_dnia]['products_name'] . '</a></div>';

                                     echo 'Rabat: <b>' . $tablica[$data_dnia]['products_discount'] . '%</b>';
                                     
                                 echo '</td>';                                 
                                 
                                 echo '</tr></table></span>';
                                 //
                            }     
                                
                        echo '</div>
                    </div>';
              $suma_dni++;
              //
        }
        
        if ( $suma_dni / 7 != (int)($suma_dni / 7) ) {
             //
             for ( $x = 0; $x < $suma_dni - ((int)($suma_dni / 7) * 7); $x++ ) {
                   //
                   echo '<div class="PoleDnia Pusty"><b class="NrDnia">&nbsp;</b><small class="NazwaMiesiaca">&nbsp;</small><div class="DaneProduktu"></div></div>';
                   $suma_dni++;
                   //
             }             
             //
        }
        ?>
        
        <div class="cl"></div>
    
    </div>
   
    <?php include('stopka.inc.php'); ?>

<?php } ?>
