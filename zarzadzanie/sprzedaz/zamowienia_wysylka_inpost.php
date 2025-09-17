<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    $api = 'Paczkomaty InPost';
    $apiKurier = new InPostApi();

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

      $wysylkaZamowienie = array();

      $adres_nadawcy  = Funkcje::PrzeksztalcAdresDomu($apiKurier->polaczenie['INTEGRACJA_INPOST_NADAWCA_DOM']);

      $sposobNadania = '1';
      if ( $apiKurier->polaczenie['INTEGRACJA_INPOST_NADAWANIE'] == 'tak' ) {
        $sposobNadania = '0';
      }

      for ( $i = 0, $c = count($_POST['parcel']['typ']); $i < $c; $i++ ) {
        $numer_tmp = $_POST['id'].$i;
        $wysylkaZamowienie[$numer_tmp] = array(
                                              'adreseeEmail'              => $_POST['adresat_mail'],
                                              'senderEmail'               => $apiKurier->polaczenie['INTEGRACJA_INPOST_LOGIN_EMAIL'],
                                              'phoneNum'                  => $_POST['adresat_telefon_gsm'],
                                              'boxMachineName'            => $_POST['paczkomat_preferowany'],
                                              'alternativeBoxMachineName' => '',
                                              'senderBoxMachineName'      => ( $apiKurier->polaczenie['INTEGRACJA_INPOST_NADAWANIE'] == 'tak' ? '' : $apiKurier->polaczenie['INTEGRACJA_INPOST_PACZKOMAT'] ),
                                              'packType'                  => $_POST['parcel']['typ'][$i],
                                              'insuranceAmount'           => $_POST['parcel']['ubezpieczenie'][$i],
                                              'onDeliveryAmount'          => $_POST['parcel']['pobranie'][$i],
                                              'customerRef'               => $_POST['komentarz']);
                                              
        if ( $apiKurier->polaczenie['INTEGRACJA_INPOST_NADAWCA_ETYKIETA'] == 'tak' ) {
          $wysylkaZamowienie[$numer_tmp]['senderAddress'] = array(
                                                                  'name'          => $apiKurier->polaczenie['INTEGRACJA_INPOST_NADAWCA_IMIE'],
                                                                  'surName'       => $apiKurier->polaczenie['INTEGRACJA_INPOST_NADAWCA_NAZWISKO'],
                                                                  'email'         => $apiKurier->polaczenie['INTEGRACJA_INPOST_NADAWCA_EMAIL'],
                                                                  'phoneNum'      => $apiKurier->polaczenie['INTEGRACJA_INPOST_NADAWCA_TELEFON'],
                                                                  'street'        => $apiKurier->polaczenie['INTEGRACJA_INPOST_NADAWCA_ULICA'],
                                                                  'buildingNo'    => $adres_nadawcy['dom'],
                                                                  'flatNo'        => $adres_nadawcy['mieszkanie'],
                                                                  'town'          => $apiKurier->polaczenie['INTEGRACJA_INPOST_NADAWCA_MIASTO'],
                                                                  'zipCode'       => $apiKurier->polaczenie['INTEGRACJA_INPOST_NADAWCA_KOD_POCZTOWY'],
                                                                  'province'      => '');
        }
      }

      $noweZamowienie = $apiKurier->inpost_send_packs( $apiKurier->polaczenie['INTEGRACJA_INPOST_LOGIN_EMAIL'], $apiKurier->polaczenie['INTEGRACJA_INPOST_LOGIN_HASLO'], $wysylkaZamowienie, '0', $sposobNadania );

      if ( is_array( $noweZamowienie ) ) {
        if (isset($noweZamowienie['error'])) {

            $komunikat = $noweZamowienie['error']['message'];

        } else {

            for ( $i = 0, $c = count($_POST['parcel']['typ']); $i < $c; $i++ ) {
              $numer_tmp = $_POST['id'].$i;
              if ( isset($noweZamowienie[$numer_tmp]['error_message']) ) {
                $komunikat = $noweZamowienie[$numer_tmp]['error_message'];
              } else {
                $pola = array(
                        array('orders_id',$filtr->process($_POST["id"])),
                        array('orders_shipping_type','InPost'),
                        array('orders_shipping_number',$noweZamowienie[$numer_tmp]['packcode']),
                        array('orders_shipping_weight',''),
                        array('orders_parcels_quantity','1'),
                        array('orders_shipping_status','Created'),
                        array('orders_shipping_date_created', 'now()'),
                        array('orders_shipping_date_modified', 'now()'),
                        array('orders_shipping_comments', ( isset($noweZamowienie[$numer_tmp]['customerdeliveringcode']) ? $noweZamowienie[$numer_tmp]['customerdeliveringcode'] : '' ) ),
                );

                $db->insert_query('orders_shipping' , $pola);
                unset($pola);
                Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz='.(int)$_POST["id"].'&zakladka='.$filtr->process($_POST["zakladka"]));

              }
            }
        }
      }

    }


    // wczytanie naglowka HTML
    include('naglowek.inc.php');

    if ( isset($komunikat) && $komunikat != '' ) {
      echo Okienka::pokazOkno('Błąd', $komunikat);
    }
    ?> 

    <div id="naglowek_cont">Tworzenie wysyłki</div>
    <div id="cont">
    
    <?php
    if ( !isset($_GET['id_poz']) ) {
         $_GET['id_poz'] = 0;
    }     
    if ( !isset($_GET['zakladka']) ) {
         $_GET['zakladka'] = '0';
    }      
    
    if ( (int)$_GET['id_poz'] == 0 ) {
    ?>
      
      <div class="poleForm"><div class="naglowek">Wysyłka</div>
        <div class="pozycja_edytowana">Brak danych do wyświetlenia</div>
      </div>      
      
    <?php
    } else {
    ?>       

      <div class="poleForm">
        <div class="naglowek">Wysyłka za pośrednictwem firmy <?php echo $api; ?> - zamówienie numer : <?php echo $_GET['id_poz']; ?></div>

        <div class="pozycja_edytowana">  

          <script type="text/javascript" src="javascript/jquery.chained.remote.js"></script>        

          <script>
          $(document).ready(function() {
            $.validator.addMethod("valueNotEquals", function (value, element, arg) {
              return arg != value;
            }, "Wybierz opcję");

            $("#apiForm").validate({
              rules: {
                adresat_telefon_gsm: { required: true, remote: "ajax/sprawdz_poprawnosc_gsm.php" }, 
                ubezpieczenie: { digits: true, },
                typ_wysylki  : { required: true, valueNotEquals: "0" }
              },
              messages: {
                adresat_telefon_gsm: {
                  remote: "Niepoprawny numer telefonu gsm"
                }
              }
            });
          });
          </script>

          <?php
          $tablica_wysylek = $apiKurier->inpost_post_parcel_array(false);

          $tekst = '<select style="width:250px;" name="parcel[typ][]" class="valid">';
          foreach ( $tablica_wysylek as $produkt ) {
            $tekst .= '<option value="'.$produkt['id'].'" '.( $produkt['id'] == $apiKurier->polaczenie['INTEGRACJA_INPOST_WYMIARY'] ? 'selected="selected"' : '' ) .'>'.$produkt['text'].'</option>';
          }
          $tekst .= '</select>';

          if ( isset($_POST['id']) ) $_GET['id_poz'] = $_POST['id'];
          $zamowienie = new Zamowienie((int)$_GET['id_poz']);
          $waga_produktow = $zamowienie->waga_produktow;

          $adres_klienta  = Funkcje::PrzeksztalcAdres($zamowienie->dostawa['ulica']);

          ?>

          <script>
          $(document).ready(function() {

            $("#addrow").click(function() {
              
              $(".item-row:last").after('<tr class="item-row"><td style="text-align:center"><div class="UsunKontener"><a class="UsunPozycjeListy TipChmurka" href="javascript:void(0)"><b>Skasuj</b><img style="cursor:pointer" src="obrazki/kasuj.png" alt="Skasuj" /></a></div></td><td class="Paczka" style="padding-top:10px; padding-bottom:8px;"><?php echo $tekst; ?></td><td class="Paczka"><input type="text" value="" size="10" name="parcel[pobranie][]" class="kropkaPusta" /></td><td class="Paczka"><input type="text" value="" size="10" name="parcel[ubezpieczenie][]" class="kropkaPusta" /></td></tr>');
              
              $(".kropkaPustaZero").change(		
                  function () {
                    var type = this.type;
                    var tag = this.tagName.toLowerCase();
                    if (type == 'text' && tag != 'textarea' && tag != 'radio' && tag != 'checkbox') {
                        //
                        if ($(this).val() != '') {
                            zamien_krp($(this), '0.00');
                        }
                        //
                    }
                  }
              ); 
              $(".kropkaPusta").change(		
                  function () {
                    var type = this.type;
                    var tag = this.tagName.toLowerCase();
                    if (type == 'text' && tag != 'textarea' && tag != 'radio' && tag != 'checkbox') {
                        //
                        zamien_krp($(this),'');
                        //
                    }
                  }
              ); 

              pokazChmurki();
              
              if ($(".UsunPozycjeListy").length > 1) $(".UsunPozycjeListy").show();
              
            });

            $('body').on('click', '.UsunPozycjeListy', function() {
              var row = $(this).parents('.item-row');
              $(this).parents('.item-row').remove();
              if ($(".UsunPozycjeListy").length < 2) $(".UsunPozycjeListy").hide();
            });
            
          });
          </script>

          <script async src="https://geowidget.easypack24.net/js/sdk-for-javascript.js"></script>
          <link rel="stylesheet" href="https://geowidget.easypack24.net/css/easypack.css"/>

          <script type="text/javascript">
            window.easyPackAsyncInit = function () {
                easyPack.init({
                    defaultLocale: 'pl',
                    mapType: 'osm',
                    searchType: 'osm',
                    points: {
                        types: ['parcel_locker', 'pop']
                    },
                    map: {
                        initialTypes: ['parcel_locker', 'pop']
                    }
                });

                window.onload = function() {
                    var szerokosc = 600;
                    var wysokosc = 600;
                    var button = document.getElementById("WidgetButton");
                    var szerokoscEkranu = $("#StrGlowna").outerWidth();

                    if ( szerokoscEkranu < 440 ) {
                        szerokosc = 320;
                        wysokosc = 380;
                    }

                    button.onclick = function() {

                        var map1 = easyPack.modalMap(function(point) {

                            $("#widget-modal").unwrap();
                            $("#widget-modal").remove();

                            $("#PaczkomatWybrany").val(point.address["line1"] + ", " + point.address["line2"]);
                            $("#paczkomat_preferowany").val(point.name);

                            }, {width: szerokosc, height: wysokosc });
                            map1.searchPlace("<?php echo $zamowienie->dostawa['miasto'] .' '.$adres_klienta['ulica']; ?>");
                    }
                }

          };
          </script>




          <form action="sprzedaz/zamowienia_wysylka_inpost.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="apiForm" class="cmxform"> 
          
            <div>
                <input type="hidden" name="akcja" value="zapisz" />
                <input type="hidden" name="id" value="<?php echo $_GET['id_poz']; ?>" />
                <input type="hidden" name="zakladka" value="<?php echo $_GET['zakladka']; ?>" />
                <input type="hidden" name="inne_dane" value="<?php echo $apiKurier->polaczenie['INTEGRACJA_INPOST_NADAWCA_ETYKIETA']; ?>" />
            </div>
            
            <div class="TabelaWysylek">

                <div class="OknoPrzesylki">

                    <div class="poleForm">

                        <div class="naglowek">Informacje o przesyłce</div>

                        <p>
                            <label class="required" for="adresat_mail">Adres e-mail odbiorcy:</label>
                            <input type="text" size="40" name="adresat_mail" id="adresat_mail" value="<?php echo ( isset($_POST['adresat_mail']) ? $_POST['adresat_mail'] : $zamowienie->klient['adres_email'] ); ?>" class="required" />
                        </p> 
                        <p>
                            <label class="required" for="adresat_telefon_gsm">Numer telefonu komórkowego:</label>
                            <?php 
                            if ( $zamowienie->dostawa['telefon'] != '' ) {
                                $NumerTelefonu = $zamowienie->dostawa['telefon'];
                            } else {
                                $NumerTelefonu = $zamowienie->klient['telefon'];
                            }
                            ?>

                            <input type="text" size="40" name="adresat_telefon_gsm" id="adresat_telefon_gsm" value="<?php echo ( isset($_POST['adresat_telefon_gsm']) ? $_POST['adresat_telefon_gsm'] : ( Klienci::CzyNumerGSM($NumerTelefonu) ? $NumerTelefonu : '' ) ); ?>" />
                        </p> 

                        <p>
                            <label for="paczkomat_preferowany">Preferowany paczkomat:</label>
                            <?php 
                            echo '<input type="text" class="przyciskPaczkomatu" id="WidgetButton" value="Zmień paczkomat" readonly="readonly" />';

                            echo '<input type="text" size="40" id="PaczkomatWybrany" value="'.$zamowienie->info['wysylka_info'].'" name="lokalizacjaPaczkomat" readonly="readonly" id="wybor_paczkomatu" />';

                            echo '<input type="text" id="paczkomat_preferowany" value="'.$zamowienie->info['wysylka_punkt_odbioru'].'" name="paczkomat_preferowany" readonly="readonly"  style="margin-left:10px;" size="15" />';


//                            echo $paczkomat_domyslny;
                            ?>
                        </p>
<!-- 
                        <p>
                            <label for="paczkomat_alternatywny">Alternatywny paczkomat:</label>
                            <?php 
                            //echo $paczkomat_alternatywny;
                            ?>
                        </p>

 --> 
                        <p>
                            <label for="komentarz">Dodatkowe info na etykiecie:</label>
                            <textarea cols="40" rows="2" name="komentarz" id="komentarz"><?php echo ( isset($_POST['komentarz']) ? $_POST['komentarz'] : 'Zamówienie numer: ' . $_GET['id_poz'] ); ?></textarea>
                        </p>
                      
                        <div class="ostrzezenie" style="margin-left:10px;">Dopuszczalna maksymalna waga paczki wynosi 25 kilogramów. <br />Waga zamówionych produktów wyznaczona na podstawie wagi produktów w zamówieniu (zaokrąglenie do góry do najbliższej liczby naturalnej): <b><?php echo ceil($waga_produktow); ?> kg</b></div>

                    </div>

                    <div class="poleForm">

                        <div class="naglowek">Informacje o paczkach</div>

                        <table class="listing_tbl">
                            <tr>
                              <td style="width:50px"></td>
                              <td class="Paczka" style="padding-top:8px;">Typ paczki</td>
                              <td class="Paczka">Kwota pobrania</td>
                              <td class="Paczka">Kwota ubezpieczenia</td>
                            </tr>

                            <tr class="item-row">
                              <td style="text-align:center"><div class="UsunKontener"><a class="UsunPozycjeListy TipChmurka" href="javascript:void(0)"><b>Skasuj</b><img style="cursor:pointer" src="obrazki/kasuj.png" alt="Skasuj" /></a></div></td>
                              <td class="Paczka" style="padding-top:10px; padding-bottom:8px;">
                                <?php
                                $tablica = $apiKurier->inpost_post_parcel_array(false);
                                echo Funkcje::RozwijaneMenu('parcel[typ][]', $tablica, $apiKurier->polaczenie['INTEGRACJA_INPOST_WYMIARY'], 'style="width:250px;"');
                                unset($tablica);
                                ?>
                              </td>
                              <td class="Paczka"><input type="text" value="" size="10" name="parcel[pobranie][]" class="kropkaPusta" /></td>
                              <td class="Paczka"><input type="text" value="" size="10" name="parcel[ubezpieczenie][]" class="kropkaPusta" /></td>
                            </tr>

                            <tr id="hiderow">
                              <td colspan="4" style="padding-left:10px;padding-top:10px;padding-bottom:10px;"><a id="addrow" href="javascript:void(0)" class="dodaj">dodaj paczkę</a></td>
                            </tr>
                        </table>

                    </div>

                 </div>

                 <div class="OknoDodatkowe">

                        <div class="poleForm">

                            <div class="naglowek">Informacje</div>

                            <p>
                                <label class="readonly">Forma dostawy w zamówieniu:</label>
                                <input type="text" size="34" name="sposob_dostawy" value="<?php echo $zamowienie->info['wysylka_modul']; ?>" readonly="readonly" class="readonly" />
                            </p> 
                            <?php
                            if ( $zamowienie->info['wysylka_info'] != '' ) {
                                ?>
                                <p>
                                    <label class="readonly">Punkt odbioru:</label>
                                    <textarea cols="30" rows="2" name="punkt_odbioru" id="punkt_odbioru"  readonly="readonly" class="readonly"><?php echo $zamowienie->info['wysylka_info']; ?></textarea>
                                </p>
                                <?php
                            }
                            ?>
                            <p>
                                <label class="readonly">Forma płatności w zamówieniu:</label>
                                <input type="text" size="34" name="sposob_zaplaty" value="<?php echo $zamowienie->info['metoda_platnosci']; ?>" readonly="readonly" class="readonly" />
                            </p> 
                            <p>
                                <label class="readonly">Wartość zamówienia:</label>
                                <input type="text" name="wartosc_zamowienia" value="<?php echo $waluty->FormatujCene($zamowienie->info['wartosc_zamowienia_val'], false, $zamowienie->info['waluta']); ?>" readonly="readonly" class="readonly" />
                            </p> 
                            <p>
                                <label class="readonly">Waga produktów:</label>
                                <input type="text" name="waga_zamowienia" value="<?php echo $waga_produktow; ?>" readonly="readonly" class="readonly" />
                            </p> 
                        </div>

                        <div class="poleForm">

                            <div class="naglowek">Informacje</div>

                            <p>
                                <label for="adresat">Adresat:</label>
                                <input type="text" size="40" name="adresat" id="adresat" value="<?php echo ( $zamowienie->dostawa['firma'] != '' ? Funkcje::formatujTekstInput($zamowienie->dostawa['firma']) : $zamowienie->dostawa['nazwa']); ?>" class="klient" />
                            </p> 
                            <p>
                                <label for="adresat_ulica">Ulica:</label>
                                <input type="text" size="40" name="adresat_ulica" id="adresat_ulica" value="<?php echo $adres_klienta['ulica']; ?>" class="klient" />
                            </p> 
                            <p>
                                <label for="adresat_dom">Numer domu / Lokalu:</label>
                                <input type="text" size="40" name="adresat_dom" id="adresat_dom" value="<?php echo $adres_klienta['dom']; ?>" class="klient" />
                            </p> 
                            <p>
                                <label for="adresat_kod_pocztowy">Kod pocztowy:</label>
                                <input type="text" size="40" name="adresat_kod_pocztowy" id="adresat_kod_pocztowy" value="<?php echo $zamowienie->dostawa['kod_pocztowy']; ?>" class="klient" />
                            </p> 
                            <p>
                                <label for="adresat_miasto">Miejscowość:</label>
                                <input type="text" size="40" name="adresat_miasto" id="adresat_miasto" value="<?php echo $zamowienie->dostawa['miasto']; ?>" class="klient" />
                            </p> 
                            <p>
                                <label for="adresat_telefon">Numer telefonu:</label>
                                <input type="text" size="40" name="adresat_telefon" id="adresat_telefon" value="<?php echo $NumerTelefonu; ?>" class="klient" />
                            </p> 

                        </div>
                      
                 </div>
                  
            </div>

            <div class="przyciski_dolne">
              <input type="submit" class="przyciskNon" value="Utwórz przesyłkę" />
              <button type="button" class="przyciskNon" onclick="cofnij('zamowienia_szczegoly','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz','zakladka')); ?>','sprzedaz');">Powrót</button>           
            </div>
          </form>

        </div>
      </div>

    <?php } ?>
    
    </div> 
    
    <?php
    include('stopka.inc.php');    
    
} ?>
