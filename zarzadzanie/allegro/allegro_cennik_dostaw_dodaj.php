<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {
  
    if ( Funkcje::SprawdzAktywneAllegro() ) {

         $AllegroRest = new AllegroRest( array('allegro_user' => $_SESSION['domyslny_uzytkownik_allegro']) );
         
    } else {
    
         Funkcje::PrzekierowanieURL('allegro_cennik_dostaw.php');
    
    }

    $KomunikatBledow = '';

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //

        unset($_POST['akcja']);
        $DaneDoWyslania = new stdClass();

        $DaneDoWyslania->name = $_POST['name'];
        //
        $DaneDoWyslania->rates = array();

        foreach($_POST as $key=>$value) {

            if ( $key != 'name' ) {
                if ( $value['first-item'] != '' ) {

                    $PojedynczaWysylka = new stdClass();

                    $PojedynczaWysylkaId = new stdClass();
                    $PierwszaWysylka = new stdClass();
                    $DrugaWysylka = new stdClass();

                    $PojedynczaWysylkaId->id = $key;

                    $PierwszaWysylka->amount = $value['first-item'];
                    $PierwszaWysylka->currency = 'PLN';

                    $DrugaWysylka->amount = $value['second-item'];
                    $DrugaWysylka->currency = 'PLN';

                    $PojedynczaWysylka->deliveryMethod = $PojedynczaWysylkaId;

                    $PojedynczaWysylka->maxQuantityPerPackage = round($value['max-quantity'],0);

                    $PojedynczaWysylka->firstItemRate = $PierwszaWysylka;
                    $PojedynczaWysylka->nextItemRate = $DrugaWysylka;


                    $PojedynczaWysylka->shippingTime = null;

                    $DaneDoWyslania->rates[] = $PojedynczaWysylka;

                    unset($PojedynczaWysylkaId, $PojedynczaWysylka, $PierwszaWysylka, $DrugaWysylka );

                }
            }

        }

        $wynik = $AllegroRest->commandPost('sale/shipping-rates', $DaneDoWyslania );

        if ( isset($wynik->errors) && count($wynik->errors) > 0 ) {
            foreach ( $wynik->errors as $blad ) {
                $KomunikatBledow .= $blad->code . ' - ' . $blad->userMessage . '<br />';
            }
        } else {
            Funkcje::PrzekierowanieURL('allegro_cennik_dostaw.php');
        }
    }

    $DostepneWysylki = $AllegroRest->commandRequest('sale/delivery-methods', '', '' );

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <?php
    if ( isset($_POST) && $KomunikatBledow != '' ) {
        ?>
        <script>
                $(document).ready(function() {
                    $.colorbox( { html:'<div id="PopUpInfo"><div class="Tytul">Błąd</div><?php echo $KomunikatBledow; ?></div>', transition: 'none',initialWidth:50, initialHeight:50, maxWidth:'90%', maxHeight:'90%' } );
                });
        </script> 
        <?php
    }
    ?>
    <script>
        $(document).ready(function() {
            $("#allegroForm").validate({
              rules: {
                name: {
                  required: true,
                }
              },
              messages: {
                name: {
                  required: "Pole jest wymagane."
                }
              }
            });
          });
    </script>        

    <script>
        function zmien_pola(pole, ID) {
            var Idpola = ID;
            var Wartoscpola = $(pole).val();
            if ( Wartoscpola >= 0 ) {
                $("#max-quantity-" + Idpola).val(1);
                $("#second-item-" + Idpola).val(0);
            }
            if ( Wartoscpola == '' ) {
                $("#max-quantity-" + Idpola).val('');
                $("#second-item-" + Idpola).val('');
            }
        }
    </script> 

    <div id="naglowek_cont">Dodawanie pozycji</div>
    <div id="cont">
          
          <form action="allegro/allegro_cennik_dostaw_dodaj.php" method="post" id="allegroForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Tworzenie nowego cennika dostaw w Allegro</div>
            
                <div class="pozycja_edytowana">
                
                    <div class="info_content">
                
                    <input type="hidden" name="akcja" value="zapisz" />
                
                    <p>
                      <label class="required" for="name">Nazwa opisowa:</label>
                      <input type="text" name="name" size="53" value="<?php echo ( isset($_POST['name']) ? $_POST['name'] : '' ); ?>" id="name" class="required" />
                    </p>

                  <div class="ObramowanieForm" style="margin-top:10px;">

                    <table>
                    
                      <tr class="div_naglowek KoszyWysylkiAllegro">
                        <td style="text-align:left;padding-left:10px;">Koszty dostawy</td>
                        <td>Pierwsza sztuka</td>
                        <td>Maksymalnie w paczce</td>
                        <td>Kolejna sztuka</td>
                      </tr>

                    <?php

                    foreach ( $DostepneWysylki->deliveryMethods as $Wysylka ) {

                        echo '<tr class="PozycjaAllegroForm KoszyWysylkiAllegro">';

                            $wartoscPierwsza = '';
                            $wartoscDruga = '';
                            $wartoscIlosc = '';


                            if ( isset($Wysylka->id) && isset($_POST) && array_key_exists($Wysylka->id, $_POST) ) {
                                $wartoscPierwsza = $_POST[$Wysylka->id]['first-item'];
                            }

                            if ( isset($Wysylka->id) && isset($_POST) && array_key_exists($Wysylka->id, $_POST) ) {
                                 $wartoscDruga = $_POST[$Wysylka->id]['second-item'];
                            }

                            if ( isset($Wysylka->id) && isset($_POST) && array_key_exists($Wysylka->id, $_POST) ) {
                                    $wartoscIlosc = $_POST[$Wysylka->id]['max-quantity'];
                            }


                            echo '<td><label">'.$Wysylka->name.'</label></td>';
                            echo '<td><input class="kropkaPustaZero" type="text" value="' . $wartoscPierwsza . '" size="20" name="' . $Wysylka->id . '[first-item]" id="first-item-'.$Wysylka->id.'" onchange="zmien_pola(this, \''.$Wysylka->id.'\')"></td>';
                            echo '<td><input class="calkowita" type="text" value="' . $wartoscIlosc . '" size="20" name="' . $Wysylka->id . '[max-quantity]" id="max-quantity-'.$Wysylka->id.'"></td>';
                            echo '<td><input class="kropkaPustaZero" type="text" value="' . $wartoscDruga . '" size="20" name="' . $Wysylka->id . '[second-item]" id="second-item-'.$Wysylka->id.'"></td>';

                        echo '</tr>';
                    }

                    ?>
                    </table>

                  </div>               

                    </div>
                    
                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Zapisz dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('allegro_cennik_dostaw','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','allegro');">Powrót</button>           
                </div>                 


          </div>                      
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}
