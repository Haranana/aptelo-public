<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    $komunikat = '';
    $api = 'Kex';
    $apiKurier = new KexApi();
    $iloscPaczek = 0;
    $parcel = array();

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
    
      $weight_total = $_POST['waga_zamowienia'];

//      echo '<pre>';
//      echo print_r($_POST);
//      echo '</pre>';

      $wysylkaZamowienie = '';

      $wysylkaZamowienie .= "<Dane>";
      $wysylkaZamowienie .= "   <NazwaMetody>DodajPrzesylki</NazwaMetody>";
      $wysylkaZamowienie .= "   <Parametry>";
      $wysylkaZamowienie .= "       <Przesylka>";
      $wysylkaZamowienie .= "           <USLUGA>".$_POST['usluga']."</USLUGA>";
      $wysylkaZamowienie .= "           <ZLECENIODAWCA>".$_POST['zleceniodawca']."</ZLECENIODAWCA>";
      $wysylkaZamowienie .= "           <PLATNIK>".$_POST['platnik']."</PLATNIK>";
      $wysylkaZamowienie .= "           <N_CK>".$_POST['zleceniodawca']."</N_CK>";
      $wysylkaZamowienie .= "           <N_OS_NADAJACA>".$apiKurier->polaczenie['INTEGRACJA_KEX_OSOBA_NADAJACA']."</N_OS_NADAJACA>";
      $wysylkaZamowienie .= "           <N_TEL_ST>".$apiKurier->polaczenie['INTEGRACJA_KEX_TELEFON_STACJONARNY']."</N_TEL_ST>";
      $wysylkaZamowienie .= "           <N_TEL_GSM>".$apiKurier->polaczenie['INTEGRACJA_KEX_TELEFON_GSM']."</N_TEL_GSM>";
      $wysylkaZamowienie .= "           <N_EMAIL>".$apiKurier->polaczenie['INTEGRACJA_KEX_ADRES_EMAIL']."</N_EMAIL>";

      $wysylkaZamowienie .= "           <DATA_N>".$_POST['data_n']."</DATA_N>";

      $wysylkaZamowienie .= "           <O_NAZWA>".$_POST['adresat_nazwa']."</O_NAZWA>";
      $wysylkaZamowienie .= "           <O_ULICA>".$_POST['adresat_ulica']."</O_ULICA>";
      $wysylkaZamowienie .= "           <O_MIEJSCOWOSC>".$_POST['adresat_miasto']."</O_MIEJSCOWOSC>";
      $wysylkaZamowienie .= "           <O_KOD_POCZTOWY>".$_POST['adresat_kod_pocztowy']."</O_KOD_POCZTOWY>";
      $wysylkaZamowienie .= "           <O_NR_DOMU>".$_POST['adresat_dom']."</O_NR_DOMU>";
      $wysylkaZamowienie .= "           <O_NR_LOK>".$_POST['adresat_lokal']."</O_NR_LOK>";
      $wysylkaZamowienie .= "           <O_OS_PRYW>".( $_POST['adresat_firma'] == '1' ? 'N' : 'T' )."</O_OS_PRYW>";
      $wysylkaZamowienie .= "           <O_NIP>".$_POST['adresat_nip']."</O_NIP>";
      $wysylkaZamowienie .= "           <O_EMAIL>".$_POST['adresat_mail']."</O_EMAIL>";

      if ( Klienci::CzyNumerGSM($_POST['adresat_telefon']) ) {
        $wysylkaZamowienie .= "         <O_TEL_GSM>".$_POST['adresat_telefon']."</O_TEL_GSM>";
      } else {
        $wysylkaZamowienie .= "         <O_TEL_ST>".$_POST['adresat_telefon']."</O_TEL_ST>";
      }

      if ( $_POST['usluga'] == 'E' ) {
          if ( $_POST['E_0'] != '' ) {
            $wysylkaZamowienie .= "         <E_0>".$_POST['E_0']."</E_0>";
            $iloscPaczek = $iloscPaczek + $_POST['E_0'];
          }
          if ( $_POST['E_1'] != '' ) {
            $wysylkaZamowienie .= "         <E_1>".$_POST['E_1']."</E_1>";
            $iloscPaczek = $iloscPaczek + $_POST['E_1'];
          }
          if ( $_POST['E_5'] != '' ) {
            $wysylkaZamowienie .= "         <E_5>".$_POST['E_5']."</E_5>";
            $iloscPaczek = $iloscPaczek + $_POST['E_5'];
          }
          if ( $_POST['E_10'] != '' ) {
            $wysylkaZamowienie .= "         <E_10>".$_POST['E_10']."</E_10>";
            $iloscPaczek = $iloscPaczek + $_POST['E_10'];
          }
          if ( $_POST['E_15'] != '' ) {
            $wysylkaZamowienie .= "         <E_15>".$_POST['E_15']."</E_15>";
            $iloscPaczek = $iloscPaczek + $_POST['E_15'];
          }
          if ( $_POST['E_20'] != '' ) {
            $wysylkaZamowienie .= "         <E_20>".$_POST['E_20']."</E_20>";
            $iloscPaczek = $iloscPaczek + $_POST['E_20'];
          }
          if ( $_POST['E_30'] != '' ) {
            $wysylkaZamowienie .= "         <E_30>".$_POST['E_30']."</E_30>";
            $iloscPaczek = $iloscPaczek + $_POST['E_30'];
          }
      }

      if ( $_POST['usluga'] == 'L' ) {
          if ( $_POST['L_60'] != '' ) {
            $wysylkaZamowienie .= "         <L_40>".$_POST['L_40']."</L_40>";
            $iloscPaczek = $iloscPaczek + $_POST['L_40'];
          }
          if ( $_POST['L_60'] != '' ) {
            $wysylkaZamowienie .= "         <L_60>".$_POST['L_60']."</L_60>";
            $iloscPaczek = $iloscPaczek + $_POST['L_60'];
          }
          if ( $_POST['L_80'] != '' ) {
            $wysylkaZamowienie .= "         <L_80>".$_POST['L_80']."</L_80>";
            $iloscPaczek = $iloscPaczek + $_POST['L_80'];
          }
          if ( $_POST['L_100'] != '' ) {
            $wysylkaZamowienie .= "         <L_100>".$_POST['L_100']."</L_100>";
            $iloscPaczek = $iloscPaczek + $_POST['L_100'];
          }
          if ( $_POST['L_150'] != '' ) {
            $wysylkaZamowienie .= "         <L_150>".$_POST['L_150']."</L_150>";
            $iloscPaczek = $iloscPaczek + $_POST['L_150'];
          }
          if ( $_POST['L_200'] != '' ) {
            $wysylkaZamowienie .= "         <L_200>".$_POST['L_200']."</L_200>";
            $iloscPaczek = $iloscPaczek + $_POST['L_200'];
          }
          if ( $_POST['L_250'] != '' ) {
            $wysylkaZamowienie .= "         <L_250>".$_POST['L_250']."</L_250>";
            $iloscPaczek = $iloscPaczek + $_POST['L_250'];
          }
      }

      if ( isset($_POST['u_ubezp']) && $_POST['u_ubezp'] == '1' ) {
        $wysylkaZamowienie .= "         <U_UBEZP>T</U_UBEZP>";
        $wysylkaZamowienie .= "         <U_WART_UBEZP>".number_format($_POST['u_wart_ubezp'], 2, ',', '')."</U_WART_UBEZP>";
      }

      if ( isset($_POST['pobranie']) && $_POST['pobranie'] == '1' ) {
        $wysylkaZamowienie .= "         <U_POBRANIE>".$_POST['u_pobranie']."</U_POBRANIE>";
        $wysylkaZamowienie .= "         <U_WART_POBRANIA>".number_format($_POST['u_wart_pobrania'], 2, ',', '')."</U_WART_POBRANIA>";
        $wysylkaZamowienie .= "         <U_RACH_POBRANIA>".str_replace(" ", "", (string)$_POST['u_rach_pobrania'])."</U_RACH_POBRANIA>";
      }

      if ( isset($_POST['u_nad_awizo']) && count($_POST['u_nad_awizo']) > 0 ) {
        foreach ( $_POST['u_nad_awizo'] as $val ) {
            if ( $val == 'T' ) {
                $wysylkaZamowienie .= "         <U_NAD_AW_TEL>T</U_NAD_AW_TEL>";
            }
            if ( $val == 'S' ) {
                $wysylkaZamowienie .= "         <U_NAD_AW_SMS>T</U_NAD_AW_SMS>";
            }
            if ( $val == 'E' ) {
                $wysylkaZamowienie .= "         <U_NAD_AW_MAIL>T</U_NAD_AW_MAIL>";
            }
        }
      }

      if ( isset($_POST['u_dost_awizo']) && count($_POST['u_dost_awizo']) > 0 ) {
        foreach ( $_POST['u_dost_awizo'] as $val ) {
            if ( $val == 'T' ) {
                $wysylkaZamowienie .= "         <U_DOST_AW_TEL>T</U_DOST_AW_TEL>";
            }
            if ( $val == 'S' ) {
                $wysylkaZamowienie .= "         <U_DOST_AW_SMS>T</U_DOST_AW_SMS>";
            }
            if ( $val == 'E' ) {
                $wysylkaZamowienie .= "         <U_DOST_AW_MAIL>T</U_DOST_AW_MAIL>";
            }
        }
      }

      if ( isset($_POST['u_dost_potw']) && count($_POST['u_dost_potw']) > 0 ) {
        foreach ( $_POST['u_dost_potw'] as $val ) {
            if ( $val == 'S' ) {
                $wysylkaZamowienie .= "         <U_DOST_POTW_SMS>T</U_DOST_POTW_SMS>";
            }
            if ( $val == 'E' ) {
                $wysylkaZamowienie .= "         <U_DOST_POTW_MAIL>T</U_DOST_POTW_MAIL>";
            }
        }
      }

      if ( isset($_POST['u_nad_17']) ) {
          $wysylkaZamowienie .= "         <U_NAD_17>T</U_NAD_17>";
      }

      if ( isset($_POST['u_szpo']) ) {
          $wysylkaZamowienie .= "         <U_SZPO>T</U_SZPO>";
      }

      if ( isset($_POST['u_eplus']) ) {
          $wysylkaZamowienie .= "         <U_EPLUS>T</U_EPLUS>";
      }

      if ( isset($_POST['u_adr']) ) {
          $wysylkaZamowienie .= "         <U_ADR>T</U_ADR>";
      }

      if ( isset($_POST['u_dost_drw']) ) {
          $wysylkaZamowienie .= "         <U_DOST_DRW>T</U_DOST_DRW>";
          $wysylkaZamowienie .= "         <O_OS_NADAJACA>".$_POST['o_os_nadajaca']."</O_OS_NADAJACA>";
      }

      if ( $_POST['opis'] != '' ) {
        $wysylkaZamowienie .= "         <OPIS>".$_POST['opis']."</OPIS>";
      }
      if ( $_POST['uwagi'] != '' ) {
        $wysylkaZamowienie .= "         <UWAGI>".$_POST['uwagi']."</UWAGI>";
      }

      if ( $apiKurier->polaczenie['INTEGRACJA_KEX_POWIADOM_EMAIL'] == 'tak' ) {
        $wysylkaZamowienie .= "         <EMAIL_DLA_WPR>".INFO_EMAIL_SKLEPU."</EMAIL_DLA_WPR>";
      }

      if ( isset($_POST['niestandard']) && $_POST['niestandard'] = '1' ) {
        $wysylkaZamowienie .= "         <ILOSC_NIESTANDARD>".$_POST['ilosc_niestandard']."</ILOSC_NIESTANDARD>";
        $wysylkaZamowienie .= "         <NIEST_WYSOKOSC>".$_POST['niest_wysokosc']."</NIEST_WYSOKOSC>";
        $wysylkaZamowienie .= "         <NIEST_DLUGOSC>".$_POST['niest_dlugosc']."</NIEST_DLUGOSC>";
        $wysylkaZamowienie .= "         <NIEST_SZEROKOSC>".$_POST['niest_szerokosc']."</NIEST_SZEROKOSC>";
      }

      $wysylkaZamowienie .= "       </Przesylka>";
      $wysylkaZamowienie .= "   </Parametry>";
      $wysylkaZamowienie .= "</Dane>";

      $noweZamowienie = $apiKurier->DodajPrzesylke( $wysylkaZamowienie );

      if ( is_object($noweZamowienie) ) {

          $numerPrzesylki = $noweZamowienie->Wyniki->Przesylka->NumerPrzesylki;

          $pola = array(
                  array('orders_id',$filtr->process($_POST["id"])),
                  array('orders_shipping_type',$api),
                  array('orders_shipping_number',$numerPrzesylki),
                  array('orders_shipping_weight',$weight_total),
                  array('orders_parcels_quantity',$iloscPaczek),
                  array('orders_shipping_status','OAK'),
                  array('orders_shipping_date_created', 'now()'),
                  array('orders_shipping_date_modified', 'now()'),
                  array('orders_shipping_comments', ''),
          );

          $db->insert_query('orders_shipping' , $pola);
          unset($pola);

          Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz='.(int)$_POST["id"].'&zakladka='.$filtr->process($_POST["zakladka"]));
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

              $("#apiForm").validate({
                  rules: {
                      ubezpieczenie_wartosc:{ required: { depends: function(){ return $('#ubezpieczenie').is(':checked') } } },
                      pobranie_wartosc:{ required: { depends: function(){ return $('#pobranie').is(':checked') } } }
                  }
              });

              $('input.datepicker').Zebra_DatePicker({
                 format: 'Y-m-d',
                 inside: false,
                 readonly_element: false
              });

              $('#ubezpieczenie').change(function() {
                  $("#ubezpieczenie_wartosc").val(($(this).is(':checked')) ? $("#wartosc_zamowienia_val").val() : "");
              });

              $('#pobranie').change(function() {
                  $("#pobranie_wartosc").val(($(this).is(':checked')) ? $("#wartosc_zamowienia_val").val() : "");
                  if ( $(this).is(':checked') ) {
                      $("#rachunek_bankowy_akapit").slideDown();
                      $("#pobranie_tryb_akapit").slideDown();
                  } else {
                      $("#rachunek_bankowy_akapit").slideUp();
                      $("#pobranie_tryb_akapit").slideUp();
                  }
              });

              $('#u_dost_drw').change(function() {
                  if ( $(this).is(':checked') ) {
                      $("#odbiorcaImieNazwisko").slideDown();
                  } else {
                      $("#odbiorcaImieNazwisko").slideUp();
                  }
              });

              $('#niestandard').change(function() {
                  //var E0 = $("input[name=E_0]").val();
                  //var E1 = $("input[name=E_1]").val();
                  //var E5 = $("input[name=E_5]").val();
                  //var E10 = $("input[name=E_10]").val();
                  //var E15 = $("input[name=E_15]").val();
                  //var E20 = $("input[name=E_20]").val();
                  //var E30 = $("input[name=E_30]").val();
                  if ( $(this).is(':checked') ) {
                      $("#niestandard_akapit").slideDown();
                  } else {
                      $("#niestandard_akapit").slideUp();
                  }
              });

              $('input[type="radio"][name="usluga"]').click(function() {
                  if ($(this).val() == "L") {
                      $('input[type="checkbox"][name="u_szpo"]').attr('disabled', 'disabled');
                      $('input[type="checkbox"][name="u_szpo"]').removeAttr('checked');
                      $("#przesylkaStd").slideUp();
                      $("#przesylkaLtL").slideDown();
                  } else {
                      $('input[type="checkbox"][name="u_szpo"]').removeAttr('disabled');
                      $("#przesylkaLtL").slideUp();
                      $("#przesylkaStd").slideDown();
                  }
              });

          });
          </script>

          <?php
          $zamowienie     = new Zamowienie((int)$_GET['id_poz']);
          $waga_produktow = $zamowienie->waga_produktow;
          $wymiary        = array();

          $adres_klienta  = Funkcje::PrzeksztalcAdres($zamowienie->dostawa['ulica']);
          $adres_klienta_local  = Funkcje::PrzeksztalcAdresDomu($adres_klienta['dom']);

          ?>


          <form action="sprzedaz/zamowienia_wysylka_kex.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="apiForm" class="cmxform">

            <div>
                <input type="hidden" name="akcja" value="zapisz" />
                <input type="hidden" name="id" value="<?php echo $_GET['id_poz']; ?>" />
                <input type="hidden" name="zakladka" value="<?php echo $_GET['zakladka']; ?>" />
                <input type="hidden" name="zleceniodawca" value="<?php echo $apiKurier->polaczenie['INTEGRACJA_KEX_KLIENT_CK']; ?>" />
                <input type="hidden" id="wartosc_zamowienia_val" name="wartosc_zamowienia_val" value="<?php echo $zamowienie->info['wartosc_zamowienia_val']; ?>" />
            </div>
            
            <div class="TabelaWysylek">

              <div class="OknoPrzesylki">

                  <div class="poleForm">

                      <div class="naglowek">Informacje o przesyłce</div>

                      <p>
                          <label>Usługa:</label>
                          <?php
                          echo Konfiguracja::Dopuszczalne_Wartosci_Auto('E,L', ( isset($_POST['usluga']) ? $_POST['usluga'] : $apiKurier->polaczenie['INTEGRACJA_KEX_RODZAJ_USLUGI']), 'usluga', '', 'Express,LTL', '2' );
                          ?>
                      </p>
                      <p>
                          <label>Płatnik</label>
                          <?php
                          echo Konfiguracja::Dopuszczalne_Wartosci_Auto('GN,GO,ZL,ST', ( isset($_POST['platnik']) ? $_POST['platnik'] : $apiKurier->polaczenie['INTEGRACJA_KEX_PLATNIK']), 'platnik', '', 'got. nadawca,got. odbiorca,wg umowy,str. trzecia', '2');
                          ?>
                      </p> 
                      <p>
                          <label>Data nadania:</label>
                          <input type="text" id="data_n" name="data_n" value="<?php echo ( isset($_POST['data_n']) ? $_POST['data_n'] : date('Y-m-d',time())); ?>" size="20" class="datepicker" />
                      </p> 
                  </div>

                  <div class="poleForm">

                      <div class="naglowek">Szczegóły przesyłki</div>

                      <div style="margin-top:5px;" id="przesylkaStd" <?php echo ( $apiKurier->polaczenie['INTEGRACJA_KEX_RODZAJ_USLUGI'] == 'L' ? 'style="display:none;"' : '' ); ?>>
                          <table style="width:100%;">
                              <tr>
                                  <th style="background:#e7e7e7;padding:5px;width:120px;">Waga [kg]:</th>
                                  <th style="background:#e7e7e7;padding:5px;text-align:center;">koperta</th>
                                  <th style="background:#e7e7e7;padding:5px;text-align:center;">do 1</th>
                                  <th style="background:#e7e7e7;padding:5px;text-align:center;">do 5</th>
                                  <th style="background:#e7e7e7;padding:5px;text-align:center;">do 10</th>
                                  <th style="background:#e7e7e7;padding:5px;text-align:center;">do 15</th>
                                  <th style="background:#e7e7e7;padding:5px;text-align:center;">do 20</th>
                                  <th style="background:#e7e7e7;padding:5px;text-align:center;">do 30</th>
                              </tr>
                              <tr>
                                  <th style="background:#e7e7e7;padding:5px;width:120px;">Liczba sztuk:</th>
                                  <td style="padding:5px;text-align:center;">
                                      <input type="text" style="width:auto;text-align:center;" value="<?php echo ( isset($_POST['E_0']) ? $_POST['E_0'] : '' ); ?>" name="E_0" size="2">
                                  </td>
                                  <td style="padding:5px;text-align:center;">
                                      <input type="text" style="width:auto;text-align:center;" value="<?php echo ( isset($_POST['E_1']) ? $_POST['E_1'] : ( $waga_produktow > 0 && $waga_produktow <= 1 ? '1' : '' ) ); ?>" name="E_1" size="2">
                                  </td>
                                  <td style="padding:5px;text-align:center;">
                                      <input type="text" style="width:auto;text-align:center;" value="<?php echo ( isset($_POST['E_5']) ? $_POST['E_5'] : ( $waga_produktow > 1 && $waga_produktow <= 5 ? '1' : '' ) ); ?>" name="E_5" size="2">
                                  </td>
                                  <td style="padding:5px;text-align:center;">
                                      <input type="text" style="width:auto;text-align:center;" value="<?php echo ( isset($_POST['E_10']) ? $_POST['E_10'] : ( $waga_produktow > 5 && $waga_produktow <= 10 ? '1' : '' ) ); ?>" name="E_10" size="2">
                                  </td>
                                  <td style="padding:5px;text-align:center;">
                                      <input type="text" style="width:auto;text-align:center;" value="<?php echo ( isset($_POST['E_15']) ? $_POST['E_15'] : ( $waga_produktow > 10 && $waga_produktow <= 15 ? '1' : '' ) ); ?>" name="E_15" size="2">
                                  </td>
                                  <td style="padding:5px;text-align:center;">
                                      <input type="text" style="width:auto;text-align:center;" value="<?php echo ( isset($_POST['E_20']) ? $_POST['E_20'] : ( $waga_produktow > 15 && $waga_produktow <= 20 ? '1' : '' ) ); ?>" name="E_20" size="2">
                                  </td>
                                  <td style="padding:5px;text-align:center;">
                                      <input type="text" style="width:auto;text-align:center;" value="<?php echo ( isset($_POST['E_30']) ? $_POST['E_30'] : ( $waga_produktow > 20 && $waga_produktow <= 30 ? '1' : '' ) ); ?>" name="E_30" size="2">
                                  </td>
                              </tr>
                          </table>
                      </div>

                      <div id="przesylkaLtL" <?php echo ( $apiKurier->polaczenie['INTEGRACJA_KEX_RODZAJ_USLUGI'] == 'E' ? 'style="display:none;"' : '' ); ?> >
                          <table style="width:99%;">
                              <tr>
                                  <th style="background:#e7e7e7;padding:5px;width:120px;">Waga [kg]:</th>
                                  <th style="background:#e7e7e7;padding:5px;text-align:center;">do 40</th>
                                  <th style="background:#e7e7e7;padding:5px;text-align:center;">do 60</th>
                                  <th style="background:#e7e7e7;padding:5px;text-align:center;">do 80</th>
                                  <th style="background:#e7e7e7;padding:5px;text-align:center;">do 100</th>
                                  <th style="background:#e7e7e7;padding:5px;text-align:center;">do 150</th>
                                  <th style="background:#e7e7e7;padding:5px;text-align:center;">do 200</th>
                                  <th style="background:#e7e7e7;padding:5px;text-align:center;">do 250</th>
                              </tr>
                              <tr>
                                  <th style="background:#e7e7e7;padding:5px;width:120px;">Liczba sztuk:</th>
                                  <td style="padding:5px;text-align:center;">
                                      <input type="text" style="width:auto;text-align:center;" value="<?php echo ( isset($_POST['L_40']) ? $_POST['L_40'] : ( $waga_produktow > 30 && $waga_produktow <= 40 ? '1' : '' ) ); ?>" name="L_40" size="2">
                                  </td>
                                  <td style="padding:5px;text-align:center;">
                                      <input type="text" style="width:auto;text-align:center;" value="<?php echo ( isset($_POST['L_60']) ? $_POST['L_60'] : ( $waga_produktow > 40 && $waga_produktow <= 60 ? '1' : '' ) ); ?>" name="L_60" size="2">
                                  </td>
                                  <td style="padding:5px;text-align:center;">
                                      <input type="text" style="width:auto;text-align:center;" value="<?php echo ( isset($_POST['L_80']) ? $_POST['L_80'] : ( $waga_produktow > 60 && $waga_produktow <= 80 ? '1' : '' ) ); ?>" name="L_80" size="2">
                                  </td>
                                  <td style="padding:5px;text-align:center;">
                                      <input type="text" style="width:auto;text-align:center;" value="<?php echo ( isset($_POST['L_100']) ? $_POST['L_100'] : ( $waga_produktow > 80 && $waga_produktow <= 100 ? '1' : '' ) ); ?>" name="L_100" size="2">
                                  </td>
                                  <td style="padding:5px;text-align:center;">
                                      <input type="text" style="width:auto;text-align:center;" value="<?php echo ( isset($_POST['L_150']) ? $_POST['L_150'] : ( $waga_produktow > 100 && $waga_produktow <= 150 ? '1' : '' ) ); ?>" name="L_150" size="2">
                                  </td>
                                  <td style="padding:5px;text-align:center;">
                                      <input type="text" style="width:auto;text-align:center;" value="<?php echo ( isset($_POST['L_200']) ? $_POST['L_200'] : ( $waga_produktow > 150 && $waga_produktow <= 200 ? '1' : '' ) ); ?>" name="L_200" size="2">
                                  </td>
                                  <td style="padding:5px;text-align:center;">
                                      <input type="text" style="width:auto;text-align:center;" value="<?php echo ( isset($_POST['L_250']) ? $_POST['L_250'] : ( $waga_produktow > 200 && $waga_produktow <= 250 ? '1' : '' ) ); ?>" name="L_250" size="2">
                                  </td>
                              </tr>
                          </table>
                      </div>

                      <p>
                          <label>Opis przesyłki:</label>
                          <?php
                          echo Konfiguracja::Dopuszczalne_Wartosci_Auto('', ( isset($_POST['opis']) ? $_POST['opis'] : $apiKurier->polaczenie['INTEGRACJA_KEX_ZAWARTOSC']), 'opis', '', '', '1');
                          ?>
                      </p> 
                      <p>
                          <label for="uwagi">Uwagi:</label>
                          <textarea name="uwagi" id="uwagi" rows="3" cols="57"><?php echo ( isset($_POST['uwagi']) ? $_POST['uwagi'] : 'Zamówienie nr: ' . $_GET['id_poz']); ?></textarea>
                      </p>
                  </div>

                  <div class="poleForm">

                      <div class="naglowek">Informacje dodatkowe</div>

                      <p>
                          <label for="ubezpieczenie">Ubezpieczenie: Wartość [PLN]:</label>
                          <input id="ubezpieczenie" value="1" type="checkbox" name="u_ubezp" style="margin-right:20px;" <?php echo ( isset($_POST['u_ubezp']) ? 'checked="checked"' : '' ); ?>><label class="OpisForPustyLabel" style="margin-right:10px;" for="ubezpieczenie"></label> 
                          <input type="text" size="20" name="u_wart_ubezp" id="ubezpieczenie_wartosc" value="<?php echo ( isset($_POST['u_wart_ubezp']) ? $_POST['u_wart_ubezp'] : '' ); ?>" />
                      </p> 

                      <p>
                          <label for="pobranie">Pobranie: Wartość [PLN]:</label>
                          <input id="pobranie" value="1" type="checkbox" name="pobranie" style="margin-right:20px;" <?php echo ( isset($_POST['pobranie']) ? 'checked="checked"' : '' ); ?>><label class="OpisForPustyLabel" style="margin-right:10px;" for="pobranie"></label>
                          <input type="text" size="20" name="u_wart_pobrania" id="pobranie_wartosc" value="<?php echo ( isset($_POST['u_wart_pobrania']) ? $_POST['u_wart_pobrania'] : '' ); ?>" />
                      </p>

                      <p id="rachunek_bankowy_akapit" <?php echo ( isset($_POST['pobranie']) ? '' : 'style="display:none;"' ); ?>>
                          <label for="integracja_kex_numer_konta">Rachunek bankowy pobrania:</label>
                          <input type="text" size="46" name="u_rach_pobrania" id="integracja_kex_numer_konta" value="<?php echo ( isset($_POST['u_rach_pobrania']) ? $_POST['u_rach_pobrania'] : $apiKurier->polaczenie['INTEGRACJA_KEX_NUMER_KONTA'] ); ?>" />
                      </p> 

                      <p id="pobranie_tryb_akapit" <?php echo ( isset($_POST['pobranie']) ? '' : 'style="display:none;"' ); ?>>
                          <label>Rodzaj pobrania:</label>
                          <input type="radio" checked="checked" value="T" name="u_pobranie" id="pobranie_s"><label class="OpisFor" for="pobranie_s">Standard</label>
                          <input type="radio" <?php echo ($_POST['u_pobranie'] == 'E' ? 'checked="checked"' : '' ); ?>value="E" name="u_pobranie" id="pobranie_e"><label class="OpisFor" for="pobranie_e">Express</label>
                          <input type="radio" <?php echo ($_POST['u_pobranie'] == 'S' ? 'checked="checked"' : '' ); ?>value="S" name="u_pobranie" id="pobranie_se"><label class="OpisFor" for="pobranie_se">SuperExpress</label>
                      </p> 
                  </div>

                  <div class="poleForm">

                      <div class="naglowek">Inne</div>

                      <p>
                          <label for="u_eplus">Express Plus:</label>
                          <input id="u_eplus" value="1" type="checkbox" name="u_eplus" style="margin-right:20px;" <?php echo ( isset($_POST['u_eplus']) ? 'checked="checked"' : '' ); ?>><label class="OpisForPustyLabel" for="u_eplus"></label>
                      </p>

                      <p>
                          <label for="u_szpo">Sprawdzenie zawartości przed odbiorem:</label>
                          <input id="u_szpo" value="1" type="checkbox" name="u_szpo" style="margin-right:20px;" <?php echo ( isset($_POST['u_szpo']) ? 'checked="checked"' : '' ); ?>><label class="OpisForPustyLabel" for="u_szpo"></label>
                      </p>

                      <p>
                          <label for="niestandard">Dopłata za opakowanie niestandardowe / gabaryt / dłużycę:</label>
                          <input id="niestandard" value="1" type="checkbox" name="niestandard" style="margin-right:20px;" <?php echo ( isset($_POST['niestandard']) ? 'checked="checked"' : '' ); ?>><label class="OpisForPustyLabel" for="niestandard"></label>
                      </p> 

                      <p id="niestandard_akapit" <?php echo ( isset($_POST['ilosc_niestandard']) ? '' : 'style="display:none;"' ); ?> >
                          <label for="ilosc_niestandard">Ilość:</label>
                          <input type="text" size="4" name="ilosc_niestandard" id="ilosc_niestandard" value="<?php echo ( isset($_POST['ilosc_niestandard']) ? $_POST['ilosc_niestandard'] : '' ); ?>" style="margin-right:20px;" />
                          wys.<input type="text" size="8" name="niest_wysokosc" id="niest_wysokosc" value="<?php echo ( isset($_POST['niest_wysokosc']) ? $_POST['niest_wysokosc'] : '' ); ?>" />
                          dł.<input type="text" size="8" name="niest_dlugosc" id="niest_dlugosc" value="<?php echo ( isset($_POST['niest_dlugosc']) ? $_POST['niest_dlugosc'] : '' ); ?>" />
                          szer.<input type="text" size="8" name="niest_szerokosc" id="niest_szerokosc" value="<?php echo ( isset($_POST['niest_szerokosc']) ? $_POST['niest_szerokosc'] : '' ); ?>" />
                      </p> 

                      <p>
                          <label for="u_adr">Przesyłka ADR:</label>
                          <input id="u_adr" value="1" type="checkbox" name="u_adr" style="margin-right:20px;" <?php echo ( isset($_POST['u_adr']) ? 'checked="checked"' : '' ); ?>><label class="OpisForPustyLabel" for="u_adr"></label>
                      </p>

                  </div>

                  <div class="poleForm">

                      <div class="naglowek">Odbiór</div>

                      <p>
                          <label for="u_nad_17">Odbiór w godzinach 17-22:</label>
                          <input id="u_nad_17" type="checkbox" name="u_nad_17" <?php echo ( isset($_POST['u_nad_17']) ? 'checked="checked"' : '' ); ?>><label class="OpisForPustyLabel" for="u_nad_17"></label>
                      </p> 

                      <p>
                          <label>Awizacja odbioru:</label>
                          <?php
                          $wybrane = '';
                          if ( isset($_POST['u_nad_awizo']) ) {
                              $wybrane = implode(';', (array)$_POST['u_nad_awizo']);
                          }
                          echo Konfiguracja::Dopuszczalne_Wartosci_Auto('S,E,T', ( isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz' ? $wybrane : $apiKurier->polaczenie['INTEGRACJA_KEX_ODBIOR_POWIADOMIENIE']), 'u_nad_awizo', '', 'SMS,E-mail,Telefon', '5');
                          unset($wybrane);
                          ?>
                      </p> 
                  </div>

                  <div class="poleForm">

                      <div class="naglowek">Dostawa</div>

                      <p>
                          <label>Awizacja dostawy:</label>
                          <?php
                          $wybrane = '';
                          if ( isset($_POST['u_dost_awizo']) ) {
                              $wybrane = implode(';', (array)$_POST['u_dost_awizo']);
                          }
                          echo Konfiguracja::Dopuszczalne_Wartosci_Auto('S,E,T', ( isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz' ? $wybrane : $apiKurier->polaczenie['INTEGRACJA_KEX_DOSTAWA_POWIADOMIENIE']), 'u_dost_awizo', '', 'SMS,E-mail,Telefon', '5');
                          unset($wybrane);
                          ?>
                      </p> 

                      <p>
                          <label for="u_dost_drw">Dostawa do rąk własnych:</label>
                          <input id="u_dost_drw" type="checkbox" name="u_dost_drw" <?php echo ( isset($_POST['u_dost_drw']) ? 'checked="checked"' : '' ); ?>><label class="OpisForPustyLabel" for="u_dost_drw"></label>
                      </p> 

                      <p id="odbiorcaImieNazwisko" <?php echo ( !isset($_POST['u_dost_drw']) ? 'style="display:none;"' : '' ); ?> >
                          <label for="o_os_nadajaca">Imię i nazwisko odbiorcy:</label>
                          <input id="o_os_nadajaca" type="text" value="<?php echo ( isset($_POST['o_os_nadajaca']) ? $_POST['o_os_nadajaca'] : '' ); ?>" name="o_os_nadajaca" size="46">
                      </p>

                      <p>
                          <label>Potwierdzenie dostawy:</label>
                          <?php
                          $wybrane = '';
                          if ( isset($_POST['u_dost_potw']) ) {
                              $wybrane = implode(';', (array)$_POST['u_dost_potw']);
                          }
                          echo Konfiguracja::Dopuszczalne_Wartosci_Auto('S,E', ( isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz' ? $wybrane : $apiKurier->polaczenie['INTEGRACJA_KEX_DOSTAWA_POTWIERDZENIE']), 'u_dost_potw', '', 'SMS,E-mail', '5');
                          unset($wybrane);
                          ?>
                       </p> 
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

                      <div class="naglowek">Informacje o odbiorcy</div>
                  
                      <p>
                          <label for="adresat_nazwa">Nazwa odbiorcy:</label>
                          <input type="text" size="40" name="adresat_nazwa" id="adresat_nazwa" value="<?php echo ( $zamowienie->dostawa['firma'] != '' ? Funkcje::formatujTekstInput($zamowienie->dostawa['firma']) : $zamowienie->dostawa['nazwa']); ?>" class="klient" />
                      </p> 

                      <p>
                          <label for="adresat_ulica">Ulica:</label>
                          <input type="text" size="40" name="adresat_ulica" id="adresat_ulica" value="<?php echo $adres_klienta['ulica']; ?>" class="klient" />
                      </p> 

                      <p>
                          <label for="adresat_dom">Numer domu:</label>
                          <input type="text" size="40" name="adresat_dom" id="adresat_dom" value="<?php echo $adres_klienta_local['dom']; ?>"  class="klient" />
                      </p> 

                      <p>
                          <label for="adresat_lokal">Numer lokalu:</label>
                          <input type="text" size="40" name="adresat_lokal" id="adresat_lokal" value="<?php echo $adres_klienta_local['mieszkanie']; ?>"  class="klient" />
                      </p> 

                      <p>
                          <label for="adresat_kod_pocztowy">Kod pocztowy:</label>
                          <input type="text" size="40" name="adresat_kod_pocztowy" id="adresat_kod_pocztowy" value="<?php echo $zamowienie->dostawa['kod_pocztowy']; ?>"  class="klient" />
                      </p> 

                      <p>
                          <label for="adresat_miasto">Miejscowość:</label>
                          <input type="text" size="40" name="adresat_miasto" id="adresat_miasto" value="<?php echo $zamowienie->dostawa['miasto']; ?>"  class="klient" />
                       </p> 

                       <p>
                          <label for="adresat_telefon">Numer telefonu:</label>
                          <?php 
                          if ( $zamowienie->dostawa['telefon'] != '' ) {
                             $NumerTelefonu = $zamowienie->dostawa['telefon'];
                          } else {
                             $NumerTelefonu = $zamowienie->klient['telefon'];
                          }
                          ?>

                          <input type="text" size="40" name="adresat_telefon" id="adresat_telefon" value="<?php echo $NumerTelefonu; ?>"  class="klient" />
                       </p> 

                       <p>
                          <label for="adresat_mail">Adres e-mail:</label>
                          <input type="text" size="40" name="adresat_mail" id="adresat_mail" value="<?php echo $zamowienie->klient['adres_email']; ?>"  class="klient" />
                       </p> 

                       <p>
                          <label>Czy odbiorcą jest firma:</label>
                          <input type="radio" <?php echo ( $zamowienie->dostawa['firma'] != '' ? 'checked="checked"' : '' ); ?> value="1" name="adresat_firma" id="adresat_firma_1" style="border: 0px none;"><label class="OpisFor" for="adresat_firma_1">tak</label>
                          <input type="radio" <?php echo ( $zamowienie->dostawa['firma'] == '' ? 'checked="checked"' : '' ); ?> value="0" name="adresat_firma" id="adresat_firma_0" style="border: 0px none;"><label class="OpisFor" for="adresat_firma_0">nie</label>
                       </p> 

                       <p>
                          <label for="adresat_nip">NIP:</label>
                          <input type="text" size="40" name="adresat_nip" id="adresat_nip" value="<?php echo ( $zamowienie->platnik['nip'] != '' ? $zamowienie->platnik['nip'] : ''); ?>" class="klient" />
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
