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
    
    <div id="naglowek_cont">Eksport zamówień do plików CSV</div>
    <div id="cont">

        <script>
        $(document).ready(function() {
            $('input.datepicker').Zebra_DatePicker({
               format: 'd-m-Y',
               inside: false,
               readonly_element: true
            });        

        });
        </script>

          <div class="poleForm">
            <div class="naglowek">Obsługa eksportu zamówień - zaufane.pl</div>

                <div class="pozycja_edytowana">  
                
                    <form action="import_danych/obsluga_zamowien_export_zaufane.php" method="post" class="cmxform" id="exportForm">

                        <input type="hidden" name="akcja" value="export" />    

                        <div class="info_content">
                                    
                            <table class="WyborCheckbox">
                                <tr><td><label for="status">Status zamówień:</label></td>
                                <td>
                                <?php
                                $tablica = Sprzedaz::ListaStatusowZamowien(false, '');
                                $tablicaStatusow = Sprzedaz::ListaStatusowZamowien(false, '');
                                foreach ( $tablicaStatusow as $tablicaStatus ) {
                                    echo '<input type="checkbox" value="' . $tablicaStatus['id'] . '" name="status[]" id="status_' . $tablicaStatus['id'] . '" /><label class="OpisFor" for="status_' . $tablicaStatus['id'] . '">' . $tablicaStatus['text'] . '</label><br />';
                                }              
                                ?>
                                </td>
                                </tr>
                            </table> 
                             
                            <p>
                                <label for="zamowienie_start">Data początkowa:</label>
                                <input type="text" name="zamowienie_start" id="zamowienie_start" value="" size="15" class="datepicker" />
                            </p> 
                            
                            <p>
                                <label for="zamowienie_koniec">Data końcowa:</label>
                                <input type="text" name="zamowienie_koniec" id="zamowienie_koniec" value="" size="15" class="datepicker" />
                            </p>           

                        </div>

                        <div class="przyciski_dolne" style="padding-left:0px">
                            <input type="submit" class="przyciskNon" value="Eksportuj dane CSV" />
                        </div>
                            
                    </form>

                </div>

          </div>

    </div>    
    
    <?php
    include('stopka.inc.php');

}