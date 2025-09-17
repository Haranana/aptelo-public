<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
    
        if ( isset($_POST['email_1']) && $_POST['email_1'] != '') {
        
            $zapytanie_tresc = "SELECT t.sender_name, t.email_var_id, t.sender_email, t.dw, t.template_id, t.email_file, tz.email_title, tz.description, tz.description_sms FROM email_text t LEFT JOIN email_text_description tz ON tz.email_text_id = t.email_text_id AND tz.language_id = '".(int)$_POST['jezyk']."' WHERE t.email_var_id = 'EMAIL_ZAMOWIENIE'";
            $sql = $db->open_query($zapytanie_tresc);
            $tresc = $sql->fetch_assoc();    
        
            $email = new Mailing;
            
            if ( $tresc['email_file'] != '' ) {
              $tablicaZalacznikow = explode(';', (string)$tresc['email_file']);
            } else {
              $tablicaZalacznikow = array();
            }

            $nadawca_email = Funkcje::parsujZmienne($tresc['sender_email']);
            $nadawca_nazwa = Funkcje::parsujZmienne($tresc['sender_name']); 

            $adresat_email = $filtr->process($_POST['email_1']);
            $adresat_nazwa = $filtr->process($_POST['adresat_nazwa']);
            
            $kopia_maila = array();
            for ( $t = 2; $t < 6; $t++ ) {
                //
                if ( isset($_POST['email_' . $t]) && $_POST['email_' . $t] != '') {
                     $kopia_maila[] = $filtr->process($_POST['email_' . $t]);
                }
                //
            }

            $temat           = $filtr->process($_POST['temat']);
            $tekst           = $filtr->process($_POST['wiadomosc']);
            $zalaczniki      = $tablicaZalacznikow;
            $szablon         = $tresc['template_id'];
            $jezyk           = $_POST['jezyk'];  

            $email->wyslijEmail($nadawca_email, $nadawca_nazwa, $adresat_email, $adresat_nazwa, implode(',', (array)$kopia_maila), $temat, $tekst, $szablon, $jezyk, $zalaczniki);

            $db->close_query($sql);
            unset($tresc, $zapytanie_tresc, $nadawca_email, $nadawca_nazwa, $adresat_email, $kopia_maila, $adresat_nazwa, $temat, $tekst, $szablon, $jezyk);           

        }

        Funkcje::PrzekierowanieURL('zamowienia_wyslij_email.php?id_poz=' . (int)$_POST["id"] . '&wyslano');
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Wysłanie wiadomości e-mail zamówienia do klienta</div>
    <div id="cont">
    
        <?php
        if ( isset($_GET['wyslano']) ) {
        ?>
          
            <div class="poleForm">
        
                <div class="naglowek">Wysyłanie wiadomości ze szczegółami zamówienia</div>

                <div class="pozycja_edytowana">

                  <div class="MailWyslano">
                      Mail został wysłany ...
                  </div>    
                  
                  <div class="przyciski_dolne">
                    <button type="button" class="przyciskNon" onclick="cofnij('zamowienia','<?php echo Funkcje::Zwroc_Get(array('x','y','wyslano')); ?>','sprzedaz');">Powrót</button> 
                  </div>

                </div>     

            </div>
            
        <?php
        
        } else {

            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
              
            $zapytanie = "select * from orders where orders_id = '" . (int)$_GET['id_poz'] . "'";
            $sql = $db->open_query($zapytanie);

            if ((int)$db->ile_rekordow($sql) > 0) {
            ?>
              
              <form action="sprzedaz/zamowienia_wyslij_email.php" method="post" id="emailForm" class="cmxform">    

                <script>           
                $(document).ready(function(){
                    ckedit('wiadomosc','99%','1000');
                    
                    // Skrypt do walidacji formularza
                    $("#emailForm").validate({
                      rules: {
                        temat: { required: true},
                        email_1: { required: true, email: true},
                        email_2: { email: true},
                        email_3: { email: true},
                        email_4: { email: true},
                        email_5: { email: true},
                      }
                    });                    
                });
                </script>               

                <div class="poleForm">

                  <div class="naglowek">Wysyłanie wiadomości ze szczegółami zamówienia</div>

                  <div class="pozycja_edytowana">

                    <div class="info_content">

                      <input type="hidden" name="akcja" value="zapisz" />

                      <input type="hidden" name="id" value="<?php echo (int)$_GET['id_poz']; ?>" />

                      <?php
                      $zamowienie = new Zamowienie((int)$_GET['id_poz']);
                      
                      $zapytanie_tresc = "SELECT t.sender_name, t.email_var_id, t.sender_email, t.dw, t.template_id, tz.email_title, tz.description, tz.description_sms FROM email_text t LEFT JOIN email_text_description tz ON tz.email_text_id = t.email_text_id AND tz.language_id = '".$zamowienie->klient['jezyk']."' WHERE t.email_var_id = 'EMAIL_ZAMOWIENIE'";
                      $sql_tresc = $db->open_query($zapytanie_tresc);
                      $tresc = $sql_tresc->fetch_assoc();                      
                      ?>            

                      <p>
                        <label class="required" for="temat">Temat:</label>
                        <input type="text" name="temat" id="temat" size="83" value="<?php echo str_replace('{NUMER_ZAMOWIENIA}', (string)$zamowienie->info['id_zamowienia'], (string)$tresc['email_title']); ?>" />
                        <input type="hidden" name="adresat_nazwa" value="<?php echo $zamowienie->klient['nazwa']; ?>" />
                      </p>       

                      <br />
                      
                      <table class="WyslijMail">
                          <tr>
                              <td><label for="email_1">Wyślij na maile:</label></td>
                              <td>
                                <input type="text" size="35" name="email_1" id="email_1" value="<?php echo $zamowienie->klient['adres_email']; ?>" /> <br />
                                <input type="text" size="35" name="email_2" id="email_2" value="<?php echo INFO_EMAIL_SKLEPU; ?>" /> <br />
                                <input type="text" size="35" name="email_3" id="email_3" value="" /> <br />
                                <input type="text" size="35" name="email_4" id="email_4" value="" /> <br />
                                <input type="text" size="35" name="email_5" id="email_5" value="" />
                              </td>
                          </tr>
                      </table>  
                      
                      <?php
                      //
                      $tekst = $tresc['description'];
                      //
                      $db->close_query($sql_tresc);
                      unset($zapytanie_tresc);  
                      //
                      $i18n = new Translator($db, $zamowienie->klient['jezyk']);
                      $GLOBALS['tlumacz'] = $i18n->tlumacz( array('ZAMOWIENIE_REALIZACJA','PRODUKT','WYGLAD'), null, true );
                      //
                      // podmiana danych
                      define('NUMER_ZAMOWIENIA', $zamowienie->info['id_zamowienia']);
                      
                      // hash
                      $hashKod = '';
                      if ( STR_PANELU_KLIENTA_BEZ_LOGOWANIA == 'tak' ) {
                           $hashKod = '/zamowienie=' . hash("sha1", $zamowienie->info['id_zamowienia'] . ';' . $zamowienie->info['data_zamowienia'] . ';' . $zamowienie->klient['adres_email'] . ';' . $zamowienie->klient['id']);
                      }
                      
                      if ( $zamowienie->klient['gosc'] == '0' || STR_PANELU_KLIENTA_BEZ_LOGOWANIA == 'tak' ) {
                          define('LINK', '<a style="word-break:break-word" href="' . Seo::link_SEO('zamowienia_szczegoly.php',$zamowienie->info['id_zamowienia'],'zamowienie','',true) . $hashKod . '">' . Seo::link_SEO('zamowienia_szczegoly.php',$zamowienie->info['id_zamowienia'],'zamowienie','',true) . $hashKod . '</a>'); 
                      } else {
                          define('LINK', $GLOBALS['tlumacz']['BRAK_DOSTEPU_DO_HISTORII']); 
                      }
                
                      define('IMIE_NAZWISKO_KUPUJACEGO', $zamowienie->klient['nazwa']);
                      define('EMAIL_KUPUJACEGO', $zamowienie->klient['adres_email']);
                      define('TELEFON_KUPUJACEGO', $zamowienie->klient['telefon']);
                      define('TELEFON_DOSTAWY', $zamowienie->dostawa['telefon']);
                      define('DATA_ZAMOWIENIA', date('d-m-Y H:i', FunkcjeWlasnePHP::my_strtotime($zamowienie->info['data_zamowienia'])));
                      define('DOKUMENT_SPRZEDAZY', $zamowienie->info['dokument_zakupu_nazwa']);
                      define('FORMA_PLATNOSCI', $zamowienie->info['metoda_platnosci']);
                      define('WAGA_PRODUKTOW', number_format($zamowienie->waga_produktow, 3, ',', ''));
                      
                      if ( !empty($zamowienie->info['platnosci_info']) ) {
                            define('OPIS_FORMY_PLATNOSCI', '<br />' . $zamowienie->info['platnosci_info']);
                          } else {
                            define('OPIS_FORMY_PLATNOSCI', '');
                      }
                      
                      define('FORMA_WYSYLKI', $zamowienie->info['wysylka_modul']);    

                      if ( !empty($zamowienie->info['wysylka_info']) ) {
                            define('OPIS_FORMY_WYSYLKI', '<br />' . $zamowienie->info['wysylka_info']);
                          } else {
                            define('OPIS_FORMY_WYSYLKI', '');
                      }                      
                      //
                      // generowanie listy produktow
                      
                      $lista_produktow_pozycje = array();
                      $lista_produktow = '<table style="width:100%;border-collapse: collapse; border-spacing:0;">';
                      
                      $id_produktow_zamowienia = array();

                      foreach ($zamowienie->produkty AS $produkt) {
                          //
                          $id_produktow_zamowienia[] = $produkt['id_produktu'];
                          //
                          $jakie_cechy = '';
                          if ( isset($produkt['attributes']) ) {
                              //
                              foreach ( $produkt['attributes'] As $cecha_produktu ) {
                                  //
                                  $jakie_cechy .= '<br />' . $cecha_produktu['cecha'] . ': ' . $cecha_produktu['wartosc'];
                                  //
                              }
                              //
                          }                        
                          //
                          // czy produkt ma komentarz
                          $komentarz_produktu = '';
                          if ( $produkt['komentarz'] != '' ) {
                              //
                              $komentarz_produktu = '<br />' . $GLOBALS['tlumacz']['KOMENTARZ_PRODUKTU'] . ' ' . $produkt['komentarz'];
                              //
                          }
                          // czy sa pola tekstowe
                          $pola_tekstowe = '';
                          if ( $produkt['pola_txt'] != '' ) {
                              //
                              $tbl_pol_txt = Funkcje::serialCiag($produkt['pola_txt']);
                              foreach ( $tbl_pol_txt as $wartosc_txt ) {
                                  //
                                  // jezeli pole to plik
                                  if ( $wartosc_txt['typ'] == 'plik' ) {
                                      $pola_tekstowe .= '<br />' . $wartosc_txt['nazwa'] . ': <a style="word-break:break-word" href="' . ADRES_URL_SKLEPU . '/inne/wgranie.php?src=' . base64_encode(str_replace('.', ';', (string)$wartosc_txt['tekst'])) . '">' . $GLOBALS['tlumacz']['WGRYWANIE_PLIKU_PLIK'] . '</a>';
                                    } else {
                                      $pola_tekstowe .= '<br />' . $wartosc_txt['nazwa'] . ': ' . $wartosc_txt['tekst'];
                                  }            
                              }
                              unset($tbl_pol_txt);
                              //
                          }    
                          //   

                          // producent produktu
                          $producent_produktu = '';
                          if ( !empty($produkt['producent']) && ZAMOWIENIE_POKAZ_PRODUCENT == 'tak' ) {
                             $producent_produktu = '<br />' .  $GLOBALS['tlumacz']['PRODUCENT'] . ': ' . $produkt['producent'];
                          }

                          // czas wysylki
                          $czas_wysylki = '';
                          if ( !empty($produkt['czas_wysylki']) && ZAMOWIENIE_POKAZ_CZAS_WYSYLKI == 'tak' ) {
                             $czas_wysylki = '<br />' .  $GLOBALS['tlumacz']['CZAS_WYSYLKI'] . ': ' . $produkt['czas_wysylki'];
                          }

                          // stan produktu
                          $stan_produktu = '';
                          if ( !empty($produkt['stan']) && ZAMOWIENIE_POKAZ_STAN_PRODUKTU == 'tak' ) {
                              $stan_produktu = '<br />' .  $GLOBALS['tlumacz']['STAN_PRODUKTU'] . ': ' . $produkt['stan'];
                          }

                          // gwarancja
                          $gwarancja_produktu = '';
                          if ( !empty($produkt['gwarancja']) && ZAMOWIENIE_POKAZ_GWARANCJA == 'tak') {
                              $gwarancja_produktu = '<br />' .  $GLOBALS['tlumacz']['GWARANCJA'] . ': ' . $produkt['gwarancja'];
                          }

                          $produkt_nazwa_tmp = $produkt['link_z_domena'] . $jakie_cechy . $pola_tekstowe . $komentarz_produktu . $producent_produktu . $czas_wysylki . $stan_produktu . $gwarancja_produktu;
                          
                          $lista_produktow .= '<tr>';
                          $lista_produktow .= '<td style="width:50%;padding:5px">' . $produkt_nazwa_tmp . '</td>';
                          $lista_produktow .= '<td style="width:15%;padding:5px;text-align:center">' . $produkt['model'] . '</td>';
                          $lista_produktow .= '<td style="width:15%;padding:5px;text-align:center">';
                          
                          $cena_tmp = '';
                          
                          if ( $produkt['cena_punkty'] > 0 ) {
                               $cena_tmp .= $produkt['cena_punkty'] . ' ' . $GLOBALS['tlumacz']['PUNKTOW'] . ' + '; 
                          }
                          
                          $cena_tmp .= $waluty->FormatujCene($produkt['cena_koncowa_brutto'], false, $zamowienie->info['waluta']);
                          
                          $lista_produktow .= $cena_tmp . '</td>';
                          
                          // jednostka miary
                          $jm_produktu = '';
                          $sqls = $db->open_query("select products_jm_name from products_jm_description where products_jm_id = '" . $produkt['jm'] . "' and language_id = '" . $zamowienie->klient['jezyk'] . "'");                            
                          if ( $db->ile_rekordow($sqls) > 0 ) {
                               //
                               $jm = $sqls->fetch_assoc();
                               if ( !empty($jm['products_jm_name']) ) {
                                    $jm_produktu = $jm['products_jm_name'];
                               }
                               unset($jm);
                               //
                          }
                          $db->close_query($sqls); 
                          
                          $lista_produktow .= '<td style="width:5%;padding:5px;text-align:center">' . $produkt['ilosc'] . ' ' . $jm_produktu . '</td>'; 

                          $lista_produktow .= '<td style="width:15%;padding:5px;text-align:center">';
                          
                          $wartosc_tmp = '';

                          if ( $produkt['cena_punkty'] > 0 ) {
                               $wartosc_tmp .= ($produkt['cena_punkty'] * $produkt['ilosc']) . ' ' . $GLOBALS['tlumacz']['PUNKTOW'] . ' + '; 
                          }                          
                          
                          $wartosc_tmp .= $waluty->FormatujCene($produkt['cena_koncowa_brutto'] * $produkt['ilosc'], false, $zamowienie->info['waluta']);

                          $lista_produktow .= $wartosc_tmp . '</td>';
                          
                          $lista_produktow .= '</tr>';
                          
                          // lista pojedynczych produktow
                          $tekst_produkty = substr($tekst, strpos($tekst, '{ZAMOWIONE_PRODUKTY_START}'), strlen($tekst));
                          $tekst_produkty = substr($tekst_produkty, 0, strpos($tekst_produkty, '{ZAMOWIONE_PRODUKTY_KONIEC}') + strlen('{ZAMOWIONE_PRODUKTY_KONIEC}'));
                          
                          $tekst_produkty = str_replace('{ZAMOWIONE_PRODUKTY_START}', '<div style="word-break:break-word">', $tekst_produkty);
                          $tekst_produkty = str_replace('{ZAMOWIONE_PRODUKTY_KONIEC}', '</div>', $tekst_produkty);            
                          $tekst_produkty = str_replace('{NAZWA_PRODUKTU}', $produkt_nazwa_tmp, $tekst_produkty);
                          $tekst_produkty = str_replace('{PRODUKT_NR_KATALOGOWY}', (string)$produkt['model'], $tekst_produkty);
                          $tekst_produkty = str_replace('{PRODUKT_KOD_EAN}', (string)$produkt['ean'], $tekst_produkty);
                          $tekst_produkty = str_replace('{PRODUKT_CENA_JEDNOSTKOWA_BRUTTO}', $cena_tmp, $tekst_produkty);
                          $tekst_produkty = str_replace('{PRODUKT_WARTOSC_BRUTTO}', $wartosc_tmp, $tekst_produkty);
                          $tekst_produkty = str_replace('{PRODUKT_KUPIONA_ILOSC}', $produkt['ilosc'] . ' ' . $jm_produktu, $tekst_produkty);
                          
                          $lista_produktow_pozycje[] = $tekst_produkty;
            
                          unset($jakie_cechy, $komentarz_produktu, $pola_tekstowe, $cena_tmp, $jm_produktu, $wartosc_tmp);
                          //    
                      } 

                      $lista_produktow .= '</table>';
                      
                      // pozycje pojedyncze
                      $lista_produktow_pojedyncze = '';
                      //
                      if ( strpos($tekst, '{ZAMOWIONE_PRODUKTY_START}') > -1 ) {
                           //
                           $tekst = str_replace('{ZAMOWIONE_PRODUKTY_START}', '{LISTA_PRODUKTOW_POJEDYNCZE}{ZAMOWIONE_PRODUKTY_START}', $tekst);
                           //
                           $tekst = preg_replace("/{ZAMOWIONE_PRODUKTY_START}.*{ZAMOWIONE_PRODUKTY_KONIEC}/si", ' ', $tekst);
                           //
                           $lista_produktow_pojedyncze = implode('', $lista_produktow_pozycje);
                           //
                      }

                      define('LISTA_PRODUKTOW_POJEDYNCZE', $lista_produktow_pojedyncze);                       

                      define('LISTA_PRODUKTOW', $lista_produktow); 
                      unset($lista_produktow);    

                      // podsumowanie zamowienia
                      $podsumowanie_tekst = '';
                      $koncowa_wartosc_zamowienia = 0;
                      foreach ( $zamowienie->podsumowanie as $podsuma ) {
                          //
                          if ( $podsuma['klasa'] != 'ot_total' ) {
                               $podsumowanie_tekst .= $podsuma['tytul'] . ': ' . $waluty->FormatujCene($podsuma['wartosc'], false, $zamowienie->info['waluta']) . '<br />';
                             } else {
                               $podsumowanie_tekst .= '<span style="font-size:120%;font-weight:bold">' . $podsuma['tytul'] . ': <span style="font-size:140%">' . $waluty->FormatujCene($podsuma['wartosc'], false, $zamowienie->info['waluta']) . '</span></span><br />';
                               $koncowa_wartosc_zamowienia = $podsuma['wartosc'];
                          }
                          //
                      }
                      define('MODULY_PODSUMOWANIA', $podsumowanie_tekst); 
                      unset($podsumowanie_tekst);
                      
                      // komentarz do zamowienia
                      if (isset($zamowienie->statusy) && count($zamowienie->statusy) > 0) {
                           //
                           $koment = '';
                           foreach ($zamowienie->statusy as $komentarz) {
                              $koment = $komentarz['komentarz'];
                              break;
                           }
                           //
                           if ( !empty($koment) != '' ) {
                               define('KOMENTARZ_DO_ZAMOWIENIA', $GLOBALS['tlumacz']['KOMENTARZ_DO_ZAMOWIENIA'] . '<br />' . nl2br($koment) . '<br />'); 
                           }
                           //
                           unset($koment);
                           //
                         } else {
                           define('KOMENTARZ_DO_ZAMOWIENIA', '');
                      }                    
                      
                      // adres zamawiajacego
                      $dane_do_faktury = '';
                      $dane_do_faktury .= $zamowienie->platnik['nazwa'];
                      if ( trim((string)$dane_do_faktury) != '' ) {
                         $dane_do_faktury .= '<br />';
                      }                    
                      if ( $zamowienie->platnik['firma'] != '' ) {
                          //
                          $dane_do_faktury .= $zamowienie->platnik['firma'] . '<br />';
                          $dane_do_faktury .= $zamowienie->platnik['nip'] . '<br />';
                          //
                      }
                      $dane_do_faktury .= $zamowienie->platnik['ulica'] . '<br />';
                      $dane_do_faktury .= $zamowienie->platnik['kod_pocztowy'] . ' ' . $zamowienie->platnik['miasto'] . '<br />';
                      if ( KLIENT_POKAZ_WOJEWODZTWO == 'tak' ) {
                          //
                          $dane_do_faktury .= $zamowienie->platnik['wojewodztwo'] . '<br />';
                          //
                      }
                      $dane_do_faktury .= $zamowienie->platnik['kraj']; 
                      define('ADRES_ZAMAWIAJACEGO', $dane_do_faktury); 
                      unset($dane_do_faktury);
                      
                      // adres dostawy
                      $dane_do_wysylki = '';
                      $dane_do_wysylki .= $zamowienie->dostawa['nazwa'];
                      if ( trim((string)$dane_do_wysylki) != '' ) {
                         $dane_do_wysylki .= '<br />';
                      }                    
                      if ( $zamowienie->dostawa['firma'] != '' ) {
                          //
                          $dane_do_wysylki .= $zamowienie->dostawa['firma'] . '<br />';
                          //
                      }
                      $dane_do_wysylki .= $zamowienie->dostawa['ulica'] . '<br />';
                      $dane_do_wysylki .= $zamowienie->dostawa['kod_pocztowy'] . ' ' . $zamowienie->dostawa['miasto'] . '<br />';
                      if ( KLIENT_POKAZ_WOJEWODZTWO == 'tak' ) {
                          //
                          $dane_do_wysylki .= $zamowienie->dostawa['wojewodztwo'] . '<br />';
                          //
                      }
                      $dane_do_wysylki .= $zamowienie->dostawa['kraj'];

                      define('ADRES_DOSTAWY', $dane_do_wysylki); 
                      unset($dane_do_wysylki);       

                      // sprzedaz elektroniczna - generowanie linku do pobrania - sprawdza czy sa w zamowieniu pliki ktore maja sprzedaz elektroniczna
                      if ( $zamowienie->sprzedaz_online == true || $zamowienie->sprzedaz_online_kody == true ) {
                           //
                           define('LINK_PLIKOW_ELEKTRONICZNYCH', '<br /><b>' . $GLOBALS['tlumacz']['POBRANIE_PLIKOW_ZAMOWIENIA'] . ' <a style="text-decoration:underline;word-break:break-word" href="' . ADRES_URL_SKLEPU . '/' . $zamowienie->sprzedaz_online_link . $hashKod . '">' . $GLOBALS['tlumacz']['POBRANIE_PLIKOW_ZAMOWIENIA_LINK'] . '</a></b><br />'); 
                           //
                         } else {
                           //
                           define('LINK_PLIKOW_ELEKTRONICZNYCH', ''); 
                           //
                      }

                      // usuwa znaczniki dla allegro
                      if ( $zamowienie->info['zrodlo'] == '3' ) {
                           $tekst = str_replace("\n", "", (string)$tekst);
                           $tekst = preg_replace('/'.preg_quote('{POMIN_ALLEGRO_START}').'[\s\S]+?'.preg_quote('{POMIN_ALLEGRO_KONIEC}').'/', '', (string)$tekst);
                      }

                      //
                      $tekst = Funkcje::parsujZmienne($tekst);
                      $tekst = preg_replace("{(<br[\\s]*(>|\/>)\s*){2,}}i", "<br /><br />", (string)$tekst);                    
                      //

                      ?>

                      <p>
                        <label>Treść wiadomości:</label>
                        <textarea id="wiadomosc" name="wiadomosc" cols="150" rows="10"><?php echo $tekst; ?></textarea>
                      </p>

                    </div>

                    <div class="przyciski_dolne">
                      <input type="hidden" name="jezyk" value="<?php echo $zamowienie->klient['jezyk']; ?>" />

                      <input type="submit" class="przyciskNon" value="Wyślij wiadomość e-mail" />
                      <button type="button" class="przyciskNon" onclick="cofnij('zamowienia','<?php echo Funkcje::Zwroc_Get(array('x','y')); ?>','sprzedaz');">Powrót</button> 
                    </div>

                  </div>

                </div>

              </form>
              
              <?php
              
            } else {
            
                echo '<div class="poleForm">
                        <div class="naglowek">Wysyłanie wiadomości</div>
                        <div class="pozycja_edytowana">Brak danych do wyświetlenia</div>
                      </div>';

            }
            
            $db->close_query($sql);
            unset($zapytanie);
        
        }
        ?>

    </div>
    
    <?php
    include('stopka.inc.php');

}
