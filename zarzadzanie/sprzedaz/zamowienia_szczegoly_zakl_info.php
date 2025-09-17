<?php
if ( isset($toks) ) {
?>

    <div id="zakl_id_0" style="display:none;" class="pozycja_edytowana">
    
        <?php if ( $zamowienie->info['czarna_lista'] == 2 ) { ?>
    
        <div class="ObramowanieTabeli" style="margin-bottom:10px;">
        
          <table class="listing_tbl" id="InfoTabelaPodsumowanie">
          
            <tr class="div_naglowek NaglowekCzarnaLista">
              <td>
                  Informacje o zakwalifikowaniu zamówienia jako "podejrzane"
              </td>
            </tr>
            
            <tr>
              <td class="InfoCzarnaLista">
              
                  <span>Parametry na podstawie jakich zamówienie zostało uznane jako podejrzane:</span>
              
                  <ul>
              
                  <?php
                  $TablicaParametrow = Klienci::SprawdzZamowienieCzarnaLista($zamowienie->info['id_zamowienia']);
                  //
                  if ( $TablicaParametrow != false ) {
                    
                      foreach ( $TablicaParametrow['lista'] as $klucz => $parametr ) {
                          echo '<li>' . ucfirst(str_replace('_', ' ' , (string)$klucz)) . ': <b>' . $parametr . '</b></li>';
                      }
                      ?>
                      
                      </ul>
                      
                      <br />
                      
                      <a class="przyciskNon" target="_blank" style="margin-left:0px" href="klienci/klienci_edytuj.php?id_poz=<?php echo $TablicaParametrow['id']; ?>">Przejdź do klienta z czarnej listy</a>
                      
                      <a class="przyciskNon" href="sprzedaz/zamowienia_czarna_lista.php?id_poz=<?php echo $zamowienie->info['id_zamowienia']; ?>">Usuń status zamówienia jako "Czarna lista"</a>
                  
                  <?php } else { ?>
                  
                      Nie znaleziono parametrów - klient został usunięty z czarnej listy klientów.
                      
                      <br /><br />
                      
                      <a class="przyciskNon" style="margin-left:0px" href="sprzedaz/zamowienia_czarna_lista.php?id_poz=<?php echo $zamowienie->info['id_zamowienia']; ?>">Usuń status zamówienia jako "Czarna lista"</a>
                  
                  <?php } ?>

              </td>
            </tr>
            
          </table>
          
        </div>    

        <?php } ?>
    
        <div class="PodzialPodsumowania">
        
            <div class="ObramowanieTabeli" style="border:2px solid #49b9cd">
        
                <table class="listing_tbl ListaDodatkowa" id="InfoTabelaPodsumowanie">
                
                  <tr class="div_naglowek">
                    <td colspan="2">
                    <div class="lf TytulSuma">Podsumowanie</div>
                    <div class="LinkEdycjiZamowienia"><a href="sprzedaz/zamowienia_podsumowanie_edytuj.php?id_poz=<?php echo (int)$_GET['id_poz']; ?>&amp;zakladka=0">edytuj</a></div>
                    </td>
                  </tr>
                  
                  <?php
                  for ($i = 0, $n = count($zamowienie->podsumowanie); $i < $n; $i++) {
                    ?>
                    <tr>
                      <td class="InfoSpan"><?php echo $zamowienie->podsumowanie[$i]['tytul']; ?></td>
                      <td style="text-align:right;width:30%;color:#079ad7">
                          <?php 
                          if ( $zamowienie->podsumowanie[$i]['prefix'] == '0' ) {
                              echo '<span style="color:red">- ';
                          }
                          if ( $zamowienie->podsumowanie[$i]['klasa'] == 'ot_total' ) {
                               echo '<span class="SumaCalkowitaZam">';
                          }                          
                          echo $waluty->FormatujCene($zamowienie->podsumowanie[$i]['wartosc'], false, $zamowienie->info['waluta']);
                          if ( $zamowienie->podsumowanie[$i]['prefix'] == '0' || $zamowienie->podsumowanie[$i]['klasa'] == 'ot_total' ) {
                              echo '</span>';
                          }
                          ?>
                      </td>
                    </tr>
                  <?php } ?>
                  
                </table>
            
            </div>

        </div>
        
        <div class="PodzialWysylkiPlatnosci">
        
            <div class="ObramowanieTabeli">
            
              <table class="listing_tbl ListaDodatkowa">
              
                <tr class="div_naglowek">
                  <td colspan="2">Płatność i dostawa</td>
                </tr>
                
                <tr>
                  <?php
                  //utworzenie tablicy parametrow
                  $lista = array();
                  $sql_platnosci = $db->open_query("SELECT id, nazwa, klasa FROM modules_payment WHERE status = '1' order by sortowanie");
                  $tlumacz = $i18n->tlumacz('PLATNOSCI');
                  //
                  while ($platnosci = $sql_platnosci->fetch_assoc()) {
                      //
                      $lista[$tlumacz['PLATNOSC_'.$platnosci['id'].'_TYTUL'].'|'.$platnosci['klasa'].'|'.$platnosci['id']] = $tlumacz['PLATNOSC_'.$platnosci['id'].'_TYTUL'] . ' (' . $platnosci['nazwa'] . ')';
                      //
                  }
                  unset($tlumacz);
                  //
                  $db->close_query($sql_platnosci);

                  $platnosciLista = json_encode($lista);
                  $platnosciLista = strip_tags((string)$platnosciLista);
                  $platnosciLista = str_replace('\r\n', ' ', (string)$platnosciLista);
                  $platnosciLista = str_replace('"', '%22', (string)$platnosciLista);
                  ?>
                  <td>Forma płatności:</td>
                  <td class="InfoSpan"><span class="editSelPlatnosc" id="payment_method"><?php echo $zamowienie->info['metoda_platnosci']; ?></span>
                  <?php echo "<span class=\"EdytujPlatnosc\"><em class=\"TipChmurka\"><b>Edytuj dane</b><img src=\"obrazki/edytuj.png\" alt=\"Edytuj dane\" onclick=\"edytuj_platnosc('Platnosc','".$platnosciLista."')\" /></em></span>"; 
                  unset($platnosciLista,$lista);
                  ?>
                  <div class="cl"></div>
                  </td>
                </tr>

                <tr>
                  <td>Forma dostawy:</td>
                  <td class="InfoSpan"><span class="editSelWysylka" id="shipping_module"><?php echo $zamowienie->info['wysylka_modul'] . ( $zamowienie->info['wysylka_info'] != '' ? ': ' . $zamowienie->info['wysylka_info'] : '' ) . ( $zamowienie->info['wysylka_punkt_odbioru'] != '' ? '; ' . $zamowienie->info['wysylka_punkt_odbioru'] : '' ); ?></span>
                  <?php echo '<span class="EdytujWysylke"><a href="sprzedaz/zamowienia_wysylka_edytuj.php?id_poz='.(int)$_GET['id_poz'].'&zakladka=0"><em class="TipChmurka"><b>Edytuj dane</b><img src="obrazki/edytuj.png" alt="Edytuj dane" /></em></a></span>'; 
                  //unset($wysylkiLista,$lista);                  
                  ?>
                  <div class="cl"></div>
                  </td>
                </tr>
              </table>
              
            </div>        
      
        </div>

        <?php if ( $zamowienie->zwrot == true ) { ?>
        
        <div class="cl"></div>

        <div class="ObramowanieTabeli" style="margin:10px 0px 10px 0px;border:2px solid #ff0000;padding:12px">
        
            <?php
            $zapytanie_zwrot = "select * from return_list where return_customers_orders_id = '" . $zamowienie->info['id_zamowienia'] . "'";
            $sql_zwrot = $db->open_query($zapytanie_zwrot);
              
            if ((int)$db->ile_rekordow($sql_zwrot) > 0) {
               
                  $infr = $sql_zwrot->fetch_assoc();
                  
                  echo '<div style="color:#ff0000;font-size:120%;margin-bottom:5px">ZWROT DO ZAMÓWIENIA: <a href="zwroty/zwroty_szczegoly.php?id_poz=' . $infr['return_id'] . '&zamowienie=' . $zamowienie->info['id_zamowienia'] . '">' . $infr['return_rand_id'] . '</a></div>';
                  
                  echo '<div style="font-weight:normal">Status zwrotu: <b>' . Zwroty::pokazNazweStatusuzwrotu($infr['return_status_id'], $_SESSION['domyslny_jezyk']['id']) . '</b></div>';
                    
                  unset($infr);
                  
            }
              
            $db->close_query($sql_zwrot);               
            ?>

        </div>
        
        <?php } ?>
        
        <div class="cl"></div>

        <div class="ObramowanieTabeli" style="margin:10px 0px 10px 0px">
        
          <table class="listing_tbl" id="InfoTabela">
          
            <?php if ( $zamowienie->info['czarna_lista'] != 2 ) { ?>
            
            <tr>
              <td class="PodzialPoza" colspan="3" style="padding:10px">
                <a class="przyciskNon" style="margin:0px" href="sprzedaz/zamowienia_czarna_lista_sprawdz.php?id_poz=<?php echo $zamowienie->info['id_zamowienia']; ?>">Sprawdź czy klient nie jest na "Czarnej liście"</a>
              </td>
              <td class="PodzialPoza PodzialPozaIkony" style="text-align:right;padding:10px">
                <a class="TipChmurka" href="sprzedaz/zamowienia_wz_pdf.php?id_poz=<?php echo $_GET['id_poz']; ?>"><b>Wygeneruj dokument Wydania z magazynu</b><img src="obrazki/pdf_2.png" alt="Wygeneruj dokument Wydania z magazynu" /></a>
                <a class="TipChmurka" href="sprzedaz/zamowienia_zamowienie_pdf.php?id_poz=<?php echo $_GET['id_poz']; ?>"><b>Wygeneruj zamówienie PDF</b><img src="obrazki/zamowienie_pdf.png" alt="Wygeneruj zamówienie PDF" /></a>
                <a class="TipChmurka" href="sprzedaz/zamowienia_faktura_proforma.php?id_poz=<?php echo $_GET['id_poz']; ?>&amp;zakladka=0"><b>Wygeneruj fakturę proforma</b><img src="obrazki/faktura_pdf.png" alt="Wygeneruj fakturę proforma" /></a>
                <?php
                if ( $zamowienie->info['data_vat_proforma'] > 0 ) {
                    echo '<span class="VatProforma">Proforma pobrana przez klienta: ' .  date('d-m-Y H:i', $zamowienie->info['data_vat_proforma']) . '</span>';;
                }
                ?>              
              </td>
            </tr>
            
            <?php } ?>
          
            <tr>
              <td class="OpisTabeli">Data zamówienia:</td><td class="WartoscTabeli"><?php echo date('d-m-Y H:i:s', FunkcjeWlasnePHP::my_strtotime($zamowienie->info['data_zamowienia'])); ?></td>
              <td class="OpisTabeli">Data ostatniej modyfikacji:</td><td class="WartoscTabeli"><?php echo date('d-m-Y H:i:s', FunkcjeWlasnePHP::my_strtotime($zamowienie->info['data_modyfikacji'])); ?></td>
            </tr>
            
            <tr>
              <td class="OpisTabeli">Klient:</td><td class="WartoscTabeli InfoNormal"><span class="InfoBold" id="customers_name"><?php echo $zamowienie->klient['nazwa']; ?></span>
              <?php echo "<span class=\"EdytujPole\"><em class=\"TipChmurka\"><b>Edytuj dane</b><img src=\"obrazki/edytuj.png\" alt=\"Edytuj dane\" onclick=\"edytuj_pole('customers_name','text')\" /></em></span>"; ?>             
              <div class="cl"></div>
              </td>
              <td class="OpisTabeli ZamowienieWyslijSmsMail">
                  <div>
                  Adres e-mail: 
                  <a class="TipChmurka" href="klienci/klienci_wyslij_email.php?zamowienie=<?php echo $zamowienie->info['id_zamowienia']; ?>"><b>Wyślij mail do klienta</b><img src="obrazki/wyslij_mail.png" alt="Wyślij mail" /></a>
                  </div>
              </td>
              <td class="WartoscTabeli InfoNormal"><span class="InfoBold" id="customers_email_address"><?php echo $zamowienie->klient['adres_email']; ?></span>
              <?php echo "<span class=\"EdytujPole\"><em class=\"TipChmurka\"><b>Edytuj dane</b><img src=\"obrazki/edytuj.png\" alt=\"Edytuj dane\" onclick=\"edytuj_pole('customers_email_address','text')\" /></em></span>"; ?>
              <div class="cl"></div>
              </td>
            </tr>  

            <tr>
              <td class="OpisTabeli">Telefon:</td><td class="WartoscTabeli InfoNormal"><span class="InfoBold" id="customers_telephone"><?php echo $zamowienie->klient['telefon']; ?></span>
              <?php echo "<span class=\"EdytujPole\"><em class=\"TipChmurka\"><b>Edytuj dane</b><img src=\"obrazki/edytuj.png\" alt=\"Edytuj dane\" onclick=\"edytuj_pole('customers_telephone','text')\" /></em></span>"; ?>
              <div class="cl"></div>
              </td>
              <?php
              // pobieranie informacji od uzytkownikach
              $lista_uzytkownikow = array();
              $zapytanie_uzytkownicy = "SELECT * FROM admin ORDER BY admin_lastname";
              $sql_uzytkownicy = $db->open_query($zapytanie_uzytkownicy);
              //
              $lista_uzytkownikow['0'] = 'Nie przypisane ...';
              while ($uzytkownicy = $sql_uzytkownicy->fetch_assoc()) { 
                $lista_uzytkownikow[$uzytkownicy['admin_id']] = $uzytkownicy['admin_firstname'] . ' ' . $uzytkownicy['admin_lastname'];
              }
              $db->close_query($sql_uzytkownicy); 
              unset($zapytanie_uzytkownicy, $uzytkownicy);    
              //                                   
              ?>
              <td class="OpisTabeli">Opiekun zamówienia:</td>
              <td class="WartoscTabeli InfoNormal"><span class="editSelOpiekun InfoBold" id="service"><?php echo ( $zamowienie->info['opiekun'] == '0' ? 'nie przypisany' : System::PokazAdmina($zamowienie->info['opiekun']) ); ?></span>
              <?php echo "<span class=\"EdytujOpiekuna\"><em class=\"TipChmurka\"><b>Edytuj dane</b><img src=\"obrazki/edytuj.png\" alt=\"Edytuj dane\" onclick=\"edytuj_opiekuna('Opiekun','".str_replace('"', '%22', (string)json_encode($lista_uzytkownikow))."')\" /></em></span>"; ?>
              <div class="cl"></div>
              </td>            
            </tr>

            <tr>
              <?php              
              //utworzenie tablicy parametrow
              $lista_dok = Array();
              $lista_dok['0'] = 'paragon';
              $lista_dok['1'] = 'faktura';
              ?>
              <td class="OpisTabeli">Dokument sprzedaży:</td>
              <?php if ( KOSZYK_WYBOR_DOKUMENTU_SPRZEDAZY == 'tak' ) { ?>
              <td class="WartoscTabeli InfoNormal"><span class="editSelDokument" id="invoice_dokument"><strong><?php echo ( $zamowienie->info['dokument_zakupu'] == '1' ? 'faktura' : 'paragon' ); ?></strong></span>
              <?php echo "<span class=\"EdytujDokument\"><em class=\"TipChmurka\"><b>Edytuj dane</b><img src=\"obrazki/edytuj.png\" alt=\"Edytuj dane\" onclick=\"edytuj_dokument('Dokument','".str_replace('"', '%22', (string)json_encode($lista_dok))."')\" /></em></span>"; ?>
              <div class="cl"></div>
              </td>
              <?php } else { ?>
              <td class="WartoscTabeli">-</td>
              <?php } ?>
              <td class="OpisTabeli">Waga produktów:</td>
              <td class="WartoscTabeli">
                <?php
                echo number_format($zamowienie->waga_produktow, 3, ',', '') . ' kg';
                ?>
              </td>              
            </tr>

            <tr>
              <td class="OpisTabeli">Uwagi klienta do zamówienia:</td><td colspan="3" class="WartoscTabeli InfoNormal"><span id="comments" class="KomentarzZamowienia"><?php echo Sprzedaz::pokazKomentarzZamowienia($_GET['id_poz']); ?></span>
              <?php echo "<span class=\"EdytujPole\"><em class=\"TipChmurka\"><b>Edytuj dane</b><img src=\"obrazki/edytuj.png\" alt=\"Edytuj dane\" onclick=\"edytuj_pole('comments','textarea')\" /></em></span>"; ?>
              <div class="cl"></div>
              </td>
            </tr>     

            <tr>
              <td class="OpisTabeli">Faktura VAT:</td>
              <td class="WartoscTabeli DaneFakturaParagon InfoNormal">
              
                <?php
                $tresc_faktura = '';
                $sql_faktura = $db->open_query("SELECT * FROM invoices WHERE orders_id = '".(int)$_GET['id_poz']."' AND invoices_type = '2'");
                
                if ((int)$db->ile_rekordow($sql_faktura) > 0) {
                
                  $info_faktura = $sql_faktura->fetch_assoc();
                  $tresc_faktura .= '<table><tr><td>';
                  $tresc_faktura .= '<b>' . NUMER_FAKTURY_PREFIX . str_pad($info_faktura['invoices_nr'], FAKTURA_NUMER_ZERA_WIODACE, 0, STR_PAD_LEFT) . FunkcjeWlasnePHP::my_strftime((string)NUMER_FAKTURY_SUFFIX, FunkcjeWlasnePHP::my_strtotime($info_faktura['invoices_date_generated'])) . '</b><br />';

                  $tresc_faktura .= 'Data utworzenia: ' . date('d-m-Y', FunkcjeWlasnePHP::my_strtotime($info_faktura['invoices_date_generated'])) . '<br />';
                  $tresc_faktura .= 'Data płatności: ' . date('d-m-Y', FunkcjeWlasnePHP::my_strtotime($info_faktura['invoices_date_payment']));
                  
                  $tresc_faktura .= '<div class="FakturaMail"><a href="sprzedaz/zamowienia_faktura_wyslij_mail.php?id_poz=' . $_GET['id_poz'] . '&amp;id='.$info_faktura['invoices_id'].'">wyślij fakturę na maila';
                  
                  if ( Funkcje::czyNiePuste($info_faktura['invoices_date_send']) ) {
                       $tresc_faktura .= '<small>wysłano: ' . date('d-m-Y H:i:s', FunkcjeWlasnePHP::my_strtotime($info_faktura['invoices_date_send'])) . '</small>';
                  }
                  
                  $tresc_faktura .= '</a></div>';
                  
                  if ( INTEGRACJA_FAKTUROWNIA_WLACZONY == 'tak' ) {
                    
                      $tresc_faktura .= '<div class="Fakturownia">';
                      if ( empty($info_faktura['fakturownia_id'])) {
                           //
                           $tresc_faktura .= '<a href="sprzedaz/zamowienia_faktura_fakturownia.php?id_poz=' . $_GET['id_poz'] . '&typ=1">utworz fakturę w Fakturownia.pl</a>';
                           //
                      } else {
                           //
                           $fakturownia = new Fakturownia((int)$_GET['id_poz']);
                           $faktura_dane = $fakturownia->CzyJestFaktura();

                           //
                           if ( isset($faktura_dane->{'id'}) ) {
                                //
                                $tresc_faktura .= '<a href="sprzedaz/zamowienia_faktura_fakturownia.php?id_poz=' . $_GET['id_poz'] . '&typ=2">pobierz fakturę PDF</a>';
                                $tresc_faktura .= '<a href="sprzedaz/zamowienia_faktura_fakturownia.php?id_poz=' . $_GET['id_poz'] . '&typ=3">wyślij fakturę PDF do klienta na adres ' . $zamowienie->klient['adres_email'] . '</a>';
                                $tresc_faktura .= '<a href="sprzedaz/zamowienia_faktura_fakturownia.php?id_poz=' . $_GET['id_poz'] . '&typ=4">usuń fakturę z Fakturownia.pl</a>';
                                $tresc_faktura .= '<a href="sprzedaz/zamowienia_faktura_fakturownia.php?id_poz=' . $_GET['id_poz'] . '&typ=5">utwórz fakturę korygującą</a>';
                                
                                if ( !empty($info_faktura['fakturownia_correct_id'])) {
                                     //
                                     $tresc_faktura .= '<a href="sprzedaz/zamowienia_faktura_fakturownia.php?id_poz=' . $_GET['id_poz'] . '&id=' . $info_faktura['fakturownia_correct_id'] . '&typ=6">pobierz fakturę korygującą PDF</a>';
                                     $tresc_faktura .= '<a target="_blank" href="https://' . INTEGRACJA_FAKTUROWNIA_URL . '/invoices/' . $info_faktura['fakturownia_correct_id'] . '/edit">edytuj fakturę korygującą <em class="TipIkona"><b>Wymaga zalogowania w Fakturownia.pl</b></em></a>';
                                     //
                                }
                                
                                if ( isset($faktura_dane->{'status'}) ) {
                                     //
                                     $status_faktury = '';
                                     switch ($faktura_dane->{'status'}) {
                                        case 'issued':
                                            $status_faktury = '<b style="color:#ff0000">WYSTAWIONA</b>';
                                            break;
                                        case 'sent':
                                            $status_faktury = '<b style="color:#ff0000">WYSŁANA</b>';
                                            break;
                                        case 'paid':
                                            $status_faktury = '<b style="color:#049923">OPŁACONA</b>';
                                            break; 
                                        case 'partial':
                                            $status_faktury = '<b style="color:#ff0000">CZĘŚCIOWO OPŁACONA</b>';
                                            break;   
                                        case 'rejected':
                                            $status_faktury = '<b style="color:#ff0000">ODRZUCONA</b>';
                                            break;                                                     
                                     }                                     
                                     //
                                }
                                
                                $tresc_faktura .= '<br /><span style="color:#1481b4">Aktualny status faktury w Fakturownia.pl:</span> ' . $status_faktury . '';
                                unset($status_faktury);
                                
                                $tresc_faktura .= '<br /><a href="sprzedaz/zamowienia_faktura_fakturownia.php?id_poz=' . $_GET['id_poz'] . '&typ=wystawiona">zmień status na <b>niezapłacona</b> w Fakturownia.pl</a>';
                                $tresc_faktura .= '<a href="sprzedaz/zamowienia_faktura_fakturownia.php?id_poz=' . $_GET['id_poz'] . '&typ=oplacona">zmień status na <b>zapłacona</b> w Fakturownia.pl</a>';
                                //
                           } else {
                                //
                                $tresc_faktura .= '<a href="sprzedaz/zamowienia_faktura_fakturownia.php?id_poz=' . $_GET['id_poz'] . '&typ=1">utworz fakturę w Fakturownia.pl</a>';
                                //
                           }
                           //
                           unset($fakturownia, $faktura_dane);
                           //
                      }
                      $tresc_faktura .= '</td>';

                  }
                  
                  $tresc_faktura .= '<td class="IkoDiv">';
                  
                  if ( $info_faktura['invoices_payment_status'] == '0' && FunkcjeWlasnePHP::my_strtotime($info_faktura['invoices_date_payment']) > time() ) {
                    $tresc_faktura .= '<em class="TipChmurka"><b>Fatura nieopłacona</b><img src="obrazki/uwaga.png" alt="Fatura nieopłacona" /></em>';
                  } elseif ( $info_faktura['invoices_payment_status'] == '0' && FunkcjeWlasnePHP::my_strtotime($info_faktura['invoices_date_payment']) <= time() ) {
                    $tresc_faktura .= '<em class="TipChmurka"><b>Fatura przeterminowana</b><img src="obrazki/blad.png" alt="Fatura przeterminowana" /></em>';
                  } else {
                    $tresc_faktura .= '<em class="TipChmurka"><b>Fatura opłacona</b><img src="obrazki/tak.png" alt="Fatura opłacona" /></em>';
                  }
                  
                  $tresc_faktura .= '<a class="TipChmurka" href="sprzedaz/zamowienia_faktura_pdf.php?id_poz='.$_GET['id_poz'].'&amp;id='.$info_faktura['invoices_id'].'&amp;zakladka=0&amp;jezyk='.$info_faktura['invoices_language_id'].'"><b>Wydrukuj fakturę VAT</b><img src="obrazki/pdf.png" alt="Wydrukuj fakturę VAT" /></a>';
                  $tresc_faktura .= '<a class="TipChmurka" href="sprzedaz/zamowienia_faktura_edytuj.php?id_poz='.$_GET['id_poz'].'&amp;id='.$info_faktura['invoices_id'].'&amp;zakladka=0"><b>Edytuj fakturę VAT</b><img src="obrazki/edytuj.png" alt="Edytuj fakturę VAT" /></a>';

                  $tresc_faktura .= '</td></tr></table>';
                  
                } else {
                
                  $tresc_faktura = '<a class="TipChmurka" href="sprzedaz/zamowienia_faktura_generuj.php?id_poz='.$_GET['id_poz'].'&amp;zakladka=0"><b>Wygeneruj fakturę VAT</b><img src="obrazki/faktura.png" alt="Wygeneruj fakturę VAT" /></a>';
                }
                
                $db->close_query($sql_faktura);
                echo $tresc_faktura;
                ?>
                
                <div class="cl"></div>
                
              </td>

              <td class="OpisTabeli">Paragon:</td>
              <td class="WartoscTabeli DaneFakturaParagon InfoNormal">
              
                <?php
                $tresc_paragon = '';
                $sql_paragon = $db->open_query("SELECT * FROM receipts WHERE orders_id = '".$_GET['id_poz']."'");
                
                if ((int)$db->ile_rekordow($sql_paragon) > 0) {
                
                  $info_paragon = $sql_paragon->fetch_assoc();
                  $tresc_paragon .= '<table><tr><td>';
                  $tresc_paragon .= '<b>' . NUMER_PARAGONU_PREFIX . str_pad($info_paragon['receipts_nr'], FAKTURA_NUMER_ZERA_WIODACE, 0, STR_PAD_LEFT) . FunkcjeWlasnePHP::my_strftime((string)NUMER_PARAGONU_SUFFIX, FunkcjeWlasnePHP::my_strtotime($info_paragon['receipts_date_generated'])) . '</b><br />';
                  $tresc_paragon .= 'Data utworzenia: ' . date('d-m-Y', FunkcjeWlasnePHP::my_strtotime($info_paragon['receipts_date_generated']));
                  
                  $tresc_paragon .= '<div class="FakturaMail"><a href="sprzedaz/zamowienia_paragon_wyslij_mail.php?id_poz=' . $_GET['id_poz'] . '&amp;id='.$info_paragon['receipts_id'].'">wyślij paragon na maila';
                  
                  if ( Funkcje::czyNiePuste($info_paragon['receipts_date_send']) ) {
                       $tresc_paragon .= '<small>wysłano: ' . date('d-m-Y H:i:s', FunkcjeWlasnePHP::my_strtotime($info_paragon['receipts_date_send'])) . '</small>';
                  }
                  
                  $tresc_paragon .= '</a></div>';

                  if ( INTEGRACJA_FAKTUROWNIA_WLACZONY == 'tak' ) {
                    
                      $tresc_paragon .= '<div class="Fakturownia">';
                      if ( empty($info_paragon['fakturownia_id'])) {
                           //
                           $tresc_paragon .= '<a href="sprzedaz/zamowienia_paragon_fakturownia.php?id_poz=' . $_GET['id_poz'] . '&typ=1">utworz paragon w Fakturownia.pl</a>';
                           //
                      } else {
                           //
                           $fakturownia = new Fakturownia((int)$_GET['id_poz'], false);
                           $paragon_dane = $fakturownia->CzyJestFaktura();

                           //
                           if ( isset($paragon_dane->{'id'}) ) {
                                //
                                $tresc_paragon .= '<a href="sprzedaz/zamowienia_paragon_fakturownia.php?id_poz=' . $_GET['id_poz'] . '&typ=2">pobierz paragon PDF</a>';
                                $tresc_paragon .= '<a href="sprzedaz/zamowienia_paragon_fakturownia.php?id_poz=' . $_GET['id_poz'] . '&typ=4">usuń paragon z Fakturownia.pl</a>';
                                
                                if ( isset($paragon_dane->{'status'}) ) {
                                     //
                                     $status_faktury = '';
                                     switch ($paragon_dane->{'status'}) {
                                        case 'issued':
                                            $status_faktury = '<b style="color:#ff0000">WYSTAWIONY</b>';
                                            break;
                                        case 'sent':
                                            $status_faktury = '<b style="color:#ff0000">WYSŁANY</b>';
                                            break;
                                        case 'paid':
                                            $status_faktury = '<b style="color:#049923">OPŁACONY</b>';
                                            break; 
                                        case 'partial':
                                            $status_faktury = '<b style="color:#ff0000">CZĘŚCIOWO OPŁACONY</b>';
                                            break;   
                                        case 'rejected':
                                            $status_faktury = '<b style="color:#ff0000">ODRZUCONY</b>';
                                            break;                                                     
                                     }                                     
                                     //
                                }
                                
                                $tresc_paragon .= '<br /><span style="color:#1481b4">Aktualny status paragonu w Fakturownia.pl:</span> ' . $status_faktury . '';
                                unset($status_faktury);
                                
                                $tresc_paragon .= '<br /><a href="sprzedaz/zamowienia_paragon_fakturownia.php?id_poz=' . $_GET['id_poz'] . '&typ=wystawiona">zmień status na <b>niezapłacony</b> w Fakturownia.pl</a>';
                                $tresc_paragon .= '<a href="sprzedaz/zamowienia_paragon_fakturownia.php?id_poz=' . $_GET['id_poz'] . '&typ=oplacona">zmień status na <b>zapłacony</b> w Fakturownia.pl</a>';
                                //
                           } else {
                                //
                                $tresc_paragon .= '<a href="sprzedaz/zamowienia_paragon_fakturownia.php?id_poz=' . $_GET['id_poz'] . '&typ=1">utworz paragon w Fakturownia.pl</a>';
                                //
                           }
                           //
                           unset($fakturownia, $paragon_dane);
                           //
                      }
                      $tresc_paragon .= '</td>';

                  }                  
                  
                  $tresc_paragon .= '<td class="IkoDiv">';
                  
                  $tresc_paragon .= '<a class="TipChmurka" href="sprzedaz/zamowienia_paragon_pdf.php?id_poz='.$_GET['id_poz'].'&amp;id='.$info_paragon['receipts_id'].'&amp;zakladka=0&amp;jezyk='.$info_paragon['receipts_language_id'].'"><b>Wydrukuj paragon</b><img src="obrazki/pdf.png" alt="Wydrukuj paragon" /></a>';
                  $tresc_paragon .= '<a class="TipChmurka" href="sprzedaz/zamowienia_paragon_edytuj.php?id_poz='.$_GET['id_poz'].'&amp;id='.$info_paragon['receipts_id'].'&amp;zakladka=0"><b>Edytuj paragon</b><img src="obrazki/edytuj.png" alt="Edytuj paragon" /></a>';

                  $tresc_paragon .= '</td></tr></table>';
                  
                } else {
                
                  $tresc_paragon = '<a class="TipChmurka" href="sprzedaz/zamowienia_paragon_generuj.php?id_poz='.$_GET['id_poz'].'&amp;zakladka=0"><b>Wygeneruj paragon</b><img src="obrazki/faktura.png" alt="Wygeneruj paragon" /></a>';
                }
                
                $db->close_query($sql_paragon);
                echo $tresc_paragon;
                ?>
                
                <div class="cl"></div>
                
              </td>
            </tr>              

          </table>

        </div>
        
        <?php if ( ZAMOWIENIA_PLANOWANA_DATA_WYSYLKI == 'tak' ) { ?>
        
        <div class="ObramowanieTabeli" style="margin:0px 0px 10px 0px">
        
          <form action="sprzedaz/zamowienia_szczegoly.php" method="post" class="cmxform"> 
          
          <input type="hidden" value="<?php echo $zamowienie->info['id_zamowienia']; ?>" name="id_data_wysylki" />

          <table class="listing_tbl" id="InfoWysylka">        
        
            <tr>
              <td class="OpisTabeli" style="white-space:nowrap">Planowany termin wysyłki:</td>
              <td class="WartoscTabeli" style="width:80%">
                  <input type="text" name="data_wysylki" value="<?php echo ((!Funkcje::CzyNiePuste($zamowienie->data_wysylki)) ? '--- nie wybrano --' : date('d-m-Y', FunkcjeWlasnePHP::my_strtotime($zamowienie->data_wysylki))); ?>" size="10" style="width:100px !important" class="datepicker" />
                  <script>
                  $(document).ready(function() {
                    $('input.datepicker').Zebra_DatePicker({
                       format: 'd-m-Y',
                       inside: false,
                       readonly_element: true
                    });                
                  });  
                  </script>        
                  <input type="submit" class="przyciskNon" style="width:auto !important;margin-top:0px" value="Zapisz datę" />
              </td>
            </tr>  
          
          </table>
          
          </form>
          
        </div>
        
        <?php } ?>

        <div class="TabeleWysylkaPlatnosc">
        
            <div class="DaneWysylka">

                <div class="ObramowanieTabeli">
                
                  <table class="listing_tbl" id="InfoTabelaWysylka">
                  
                    <tr class="div_naglowek">
                      <td>
                      <div class="lf">Dane do wysyłki</div>
                      <div class="LinkEdycjiZamowienia"><a href="sprzedaz/zamowienia_adres_edytuj.php?id_poz=<?php echo (int)$_GET['id_poz']; ?>&amp;typ=dostawa&amp;zakladka=0">edytuj</a></div>
                      </td>
                    </tr>
                    
                    <tr>
                      <td>
                    
                        <ul>
                        
                        <?php if ( $zamowienie->dostawa['firma'] != '' ) { ?>
                        
                            <li><span class="FirmaNazwa"><?php echo $zamowienie->dostawa['firma']; ?></span></li> 

                            <?php if ( $zamowienie->dostawa['nip'] != '' ) { ?>
                                <li>NIP: <?php echo $zamowienie->dostawa['nip']; ?></li>
                            <?php } ?>

                        <?php } ?>
                        
                        <li><?php echo $zamowienie->dostawa['nazwa']; ?></li>
                        
                        <?php if ( $zamowienie->dostawa['pesel'] != '' ) { ?>
                        <li>PESEL: <?php echo $zamowienie->dostawa['pesel']; ?></li>
                        <?php } ?>
                        
                        <li><?php echo $zamowienie->dostawa['ulica']; ?></li>
                        
                        <li><?php echo $zamowienie->dostawa['kod_pocztowy']; ?> <?php echo $zamowienie->dostawa['miasto']; ?></li>
                      
                        <?php if ( KLIENT_POKAZ_WOJEWODZTWO == 'tak' ) { ?>
                        <li><?php echo $zamowienie->dostawa['wojewodztwo']; ?></li>
                        <?php } ?>
                        
                        <li><?php echo $zamowienie->dostawa['kraj']; ?></li>
                        
                        <?php if ( !empty($zamowienie->dostawa['telefon']) ) { ?>                    
                        <li>Telefon: <?php echo $zamowienie->dostawa['telefon']; ?></li>         
                        <?php } ?>

                        </ul>
                                        
                      </td>
                    </tr>  

                  </table>
                  
                </div>
                    
            </div>
            
            <div class="DanePlatnosc">

                <div class="ObramowanieTabeli">
                
                  <table class="listing_tbl" id="InfoTabelaPlatnik">
                  
                    <tr class="div_naglowek">
                      <td>
                      <div class="lf">Dane płatnika</div>
                      <div class="LinkEdycjiZamowienia"><a href="sprzedaz/zamowienia_adres_edytuj.php?id_poz=<?php echo (int)$_GET['id_poz']; ?>&amp;typ=platnik&amp;zakladka=0">edytuj</a></div>
                      </td>
                    </tr>
                    
                    <tr>
                      <td>
                          
                        <ul>
                        
                          <?php if ( $zamowienie->platnik['firma'] != '' ) { ?>
                          
                              <li><span class="FirmaNazwa"><?php echo $zamowienie->platnik['firma']; ?></span></li>

                          <?php } ?>                    
                        
                          <?php if ( $zamowienie->platnik['nip'] != '' ) { ?>
                              <li <?php echo ((trim((string)$zamowienie->platnik['firma']) == '') ? 'style="color:#ff0000;font-weight:bold"' : ''); ?>>NIP: <?php echo $zamowienie->platnik['nip']; ?></li>
                          <?php } ?>

                          <?php if ( trim((string)$zamowienie->platnik['nazwa']) != '' ) { ?>
                          <li><?php echo $zamowienie->platnik['nazwa']; ?></li>
                          <?php } ?>
                        
                          <?php if ( $zamowienie->platnik['pesel'] != '' ) { ?>
                          <li>PESEL: <?php echo $zamowienie->platnik['pesel']; ?></li>
                          <?php } ?>
                          
                          <li><?php echo $zamowienie->platnik['ulica']; ?></li>
                          
                          <li><?php echo $zamowienie->platnik['kod_pocztowy']; ?> <?php echo $zamowienie->platnik['miasto']; ?></li>
                          
                          <?php if ( KLIENT_POKAZ_WOJEWODZTWO == 'tak' ) { ?>
                          <li><?php echo $zamowienie->platnik['wojewodztwo']; ?></li>
                          <?php } ?>                      
                        
                          <li><?php echo $zamowienie->platnik['kraj']; ?></li>
                          
                        </ul>
                        
                     </td>
                    </tr>

                  </table>
                  
                </div>
            
            </div>
            
        </div>

        <div class="cl"></div>
        
        <?php
        
        // dodatkowe pola zamowien
        $dodatkowe_pola_zamowienia = "SELECT oe.fields_id, oe.fields_input_type, oe.fields_required_status, oei.fields_input_value, oei.fields_name, oe.fields_status, oe.fields_input_type 
                                        FROM orders_extra_fields oe, orders_extra_fields_info oei 
                                       WHERE oe.fields_status = '1' AND oei.fields_id = oe.fields_id AND oei.languages_id = '" . $_SESSION['domyslny_jezyk']['id'] . "' ORDER BY oe.fields_order";

        $sql_pola = $db->open_query($dodatkowe_pola_zamowienia);

        include('zamowienia_szczegoly_dodatkowe_pola.php');
        
        if ( PRODUKTY_SZCZEGOLY_ZAMOWIENIA != 'dodatkowa zakładka' ) {
        
             // produkty zamowienia
             include('zamowienia_szczegoly_zakl_produkty.php');

        }        
        
        if ( isset($_SESSION['fakturownia']) ) {
             //
             $InfoFakturownia = '';
             //
             if ( isset($_SESSION['fakturownia']['wyslana']) ) {
                  //
                  if ( $_SESSION['fakturownia']['wyslana'] == true ) {
                       //
                       $InfoFakturownia = 'Faktura została wysłana do klienta';
                       //
                  } else {
                       //
                       $InfoFakturownia .= '<span style="color:#ff0000">UWAGA !!<br />Podczas wysyłania faktury wystąpił błąd - faktura nie została wysłana</span>';
                       if ( isset($_SESSION['fakturownia']['wiadomosc']) ) {
                            $InfoFakturownia .= '<br /><br />Komunikat z Fakturownia.pl: ' . $_SESSION['fakturownia']['wiadomosc'];
                       }
                       //
                  }
             }
             
             if ( isset($_SESSION['fakturownia']['usunieta']) ) {
                  //
                  if ( $_SESSION['fakturownia']['usunieta'] == true ) {
                       //
                       if ( !isset($_SESSION['fakturownia_typ']) || $_SESSION['fakturownia_typ'] == 'faktura' ) {
                            $InfoFakturownia = 'Faktura została usunięta w serwisie Fakturowanie.pl';
                       } else {
                            $InfoFakturownia = 'Paragon został usunięty w serwisie Fakturowanie.pl';
                       }
                       //
                  } else {
                       //
                       $InfoFakturownia .= '<span style="color:#ff0000">UWAGA !!<br />Podczas usuwania ' . (( !isset($_SESSION['fakturownia_typ']) || $_SESSION['fakturownia_typ'] == 'faktura' ) ? 'faktury' : 'paragonu') . ' wystąpił błąd</span>';
                       //
                  }
             }  

             if ( isset($_SESSION['fakturownia']['status']) ) {
                  //
                  if ( $_SESSION['fakturownia']['status'] == true ) {
                       //
                       $InfoFakturownia = 'Status ' . (( !isset($_SESSION['fakturownia_typ']) || $_SESSION['fakturownia_typ'] == 'faktura' ) ? 'faktury' : 'paragonu') . ' został zmieniony w serwisie Fakturowanie.pl';
                       //
                  } else {
                       //
                       $InfoFakturownia .= '<span style="color:#ff0000">UWAGA !!<br />Podczas zmiany statusu ' . (( !isset($_SESSION['fakturownia_typ']) || $_SESSION['fakturownia_typ'] == 'faktura' ) ? 'faktury' : 'paragonu') . ' wystąpił błąd</span>';
                       //
                  }
             }              
             
             if ( isset($_SESSION['fakturownia']['dodanie']) ) {
                  //
                  if ( !isset($_SESSION['fakturownia_typ']) || $_SESSION['fakturownia_typ'] == 'faktura' ) {
                       $InfoFakturownia = 'W serwisie Fakturownia.pl została utworzona faktura o numerze ' . $_SESSION['fakturownia']['numer_faktury'];
                  } else {
                       $InfoFakturownia = 'W serwisie Fakturownia.pl został utworzony paragon o numerze ' . $_SESSION['fakturownia']['numer_faktury'];
                  }
                  //
                  if ( isset($_SESSION['fakturownia']['blad']) ) {
                       //
                       $InfoFakturownia .= '<br /><br /><span style="color:#ff0000">UWAGA !!<br />Podczas tworzenia ' . (( !isset($_SESSION['fakturownia_typ']) || $_SESSION['fakturownia_typ'] == 'faktura' ) ? 'faktury' : 'paragonu') . ' wystąpił błąd - wartość faktury/paragonu w systemie Fakturowania różni się od wartości zamówienia - sprawdź bezpośrednio w serwisie Fakturownia różnice kwot</span>';
                       //
                  }
                  //
             }
             
             if ( isset($_SESSION['fakturownia']['korekta']) ) {
                  //
                  if ( isset($_SESSION['fakturownia']['brak_korekty']) ) {
                       //
                       $InfoFakturownia = 'Brak zmienionych danych do utworzenia faktury korygującej';
                       //
                  } else {
                       //
                       $InfoFakturownia = 'W serwisie Fakturownia.pl została utworzona faktura korygująca o numerze ' . $_SESSION['fakturownia']['numer_faktury'] . '<br /><br /><span style="color:#ff0000">UWAGA !!<br />Faktura korygująca została utworzona na podstawie różnicy pomiędzy fakturą zapisaną w serwisie Fakturownia a danymi w sklepie. Przed wysłaniem faktury klientowi należy sprawdzić poprawność wygenerowanych danych.</span>';
                       //
                  }
                  //
             }        

             if ( isset($_SESSION['fakturownia']['blad_api']) ) {
                  //
                  $InfoFakturownia = '<span style="color:#ff0000">UWAGA !!<br />Podczas komunikacji wystąpił bład.</span> <br /><br />Komunikat z serwisu: ' . $_SESSION['fakturownia']['blad_api'] . '';
                  //
             }             
             
             if ( $InfoFakturownia != '') {
                 //
                 ?>
                 <script>
                 $(document).ready(function() {
                     $.colorbox( { html:'<div id="PopUpInfo"><?php echo $InfoFakturownia; ?></div>', initialWidth:50, initialHeight:50, maxWidth:'90%', maxHeight:'90%' } );
                 });
                 </script>
                 <?php
                 //
             }
             //
             unset($_SESSION['fakturownia'], $_SESSION['fakturownia_typ']);
             //
        }
        ?>
        
        <div class="ObramowanieTabeli" style="margin:30px 0px 10px 0px">
        
          <table class="listing_tbl InfoTabelaStat">
          
            <tr class="div_naglowek">
              <td colspan="2">Informacje analityczne</td>
            </tr>          
          
            <tr class="PozycjaStat">
              <td>Adres IP klienta:</td><td style="word-break:break-word"><?php echo $zamowienie->info['adres_ip']; ?></td>
            </tr>
            <tr class="PozycjaStat">
              <td>Skąd trafił klient:</td><td style="word-break:break-word"><?php echo $zamowienie->info['referer']; ?></td>
            </tr>          
            <tr class="PozycjaStat">
              <td>Urządzenie z jakiego został dokonany zakup:</td><td style="word-break:break-word"><?php echo (($zamowienie->info['urzadzenie'] != '') ? $zamowienie->info['urzadzenie'] : '<i>-- brak danych --</i>'); ?></td>
            </tr>
            
            <?php
            $zapytanie_koszyk_id = "select basket_save_id from orders where orders_id = '" . $zamowienie->info['id_zamowienia'] . "'";
            $sql_koszyk_id = $db->open_query($zapytanie_koszyk_id);
              
            if ((int)$db->ile_rekordow($sql_koszyk_id) > 0) {
               
                  $infr = $sql_koszyk_id->fetch_assoc();
                  $podziel_id = explode('-', (string)$infr['basket_save_id']);
                  
                  if ( isset($podziel_id[1]) && $podziel_id[1] != '' ) {
                      ?>
                      
                      <tr class="PozycjaStat">
                        <td>Zamówienie z zapisanego koszyka:</td>
                        <td style="word-break:break-word">Id koszyka: &nbsp; <a href="statystyki/zapisane_koszyki.php?nazwa=<?php echo $podziel_id[1]; ?>" style="font-weight:bold;text-decoration:underline"><?php echo $infr['basket_save_id']; ?></a></td>
                      </tr>

                      <?php
                  }
                  unset($infr);
                  
            }
              
            $db->close_query($sql_koszyk_id);               
            ?>            

          </table>
          
        </div>

    </div>
    
    <?php if ( isset($_GET['produkt']) ) { ?>
    
    <script>
    $(document).ready(function() {
        $.scrollTo('.ZakupioneProdukty',400);
    });
    </script>
    
    <?php } ?>
    
<?php
}
?>        