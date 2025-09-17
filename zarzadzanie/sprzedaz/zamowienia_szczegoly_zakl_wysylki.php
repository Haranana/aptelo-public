<?php
if ( isset($toks) ) {
    if ( INTEGRACJA_KURIER_INPOST_SHIPX_WLACZONY == 'tak' ) {
        $JestInpost = array_search('SHIPX', array_column($zamowienie->dostawy, 'inne_informacje'));
        if (false !== $JestInpost) {

            $api = 'InPostShipX';
            $apiKurierInpost = new InPostShipX();

            $StatusyInpost = $apiKurierInpost->GetRequest('/v1/statuses', '');

            $NadaniaInpost = $apiKurierInpost->SposobNadaniaTablica();
        }
    }

    ?>

    <div id="zakl_id_1" style="display:none;" class="pozycja_edytowana">
        <?php
        $zapytanie = "SELECT * FROM settings WHERE type = 'wysylki' ";
        $sql = $db->open_query($zapytanie);

        $parametr_kurierzy = array();

        if ( $db->ile_rekordow($sql) > 0 ) {
          while ($info = $sql->fetch_assoc()) {
            $parametr_kurierzy[$info['code']] = array($info['value'], $info['limit_values'], $info['description']);
          }
        }
        $db->close_query($sql);
        unset($zapytanie);
        ?>
        <div class="ObramowanieTabeli">
        
          <table class="listing_tbl">
          
            <tr class="div_naglowek NaglowekCentruj">
              <td>Firma</td>
              <td>Numer<br />dokumentu</td>
              <td>Data<br />utworzenia</td>
              <td>Ilość<br />paczek</td>
              <td>Status</td>
              <td>Data<br />aktualizacji</td>
              <?php
              if ( $zamowienie->info['zrodlo'] == '3' ) {
                echo '<td>Wysłana do<br />Allegro</td>';
              }
              ?>
              <td></td>
            </tr>
                  
            <?php 
            if ( isset($zamowienie->dostawy) && count($zamowienie->dostawy) > 0) {

              foreach ( $zamowienie->dostawy as $dostawa ) {
                $status = '-';
                $status = $dostawa['status_przesylki'];
                $kodTrackingowy = '';

                if ( $dostawa['reczna'] == 'nie' ) {
                  
                    if ( INTEGRACJA_KURIER_INPOST_SHIPX_WLACZONY == 'tak' ) {

                        if ( strtolower((string)$dostawa['rodzaj_przesylki']) == strtolower((string)'INPOST') && $dostawa['inne_informacje'] == 'SHIPX' ) {

                            $dostawa['rodzaj_przesylki'] = $dostawa['rodzaj_przesylki'] .' ('. $dostawa['komentarz'] . ')';
                                    $dostawa['rodzaj_przesylki'] .= '<br />'.$dostawa['inne_informacje_1'].( isset($NadaniaInpost[$dostawa['inne_informacje_1']]) ? '<em class="TipIkona"><b>'.$NadaniaInpost[$dostawa['inne_informacje_1']].'</b></em>' : '' ); 

                            if ( isset($StatusyInpost->items) && count((array)$StatusyInpost->items) > 0 ) {
                                foreach ( $StatusyInpost->items as $Rekord ) {
                                    if ( $Rekord->name == $dostawa['status_przesylki'] ) {
                                        $status =  $Rekord->title . '<em class="TipIkona"><b>'.$Rekord->description.'</b></em>';
                                    }
                                }
                            }
                        }
                    }

                    if ( strpos((string)$dostawa['rodzaj_przesylki'], 'SendIt') !== false ) {
                        $status = Funkcje::PokazStatusSendit($dostawa['status_przesylki']);
                        if ( $dostawa['komentarz'] != '' ) {
                            $kodTrackingowyTMP = unserialize($dostawa['komentarz']);
                            $kodTrackingowy = $kodTrackingowyTMP[0];
                        }
                    }

                    if ( strpos((string)$dostawa['rodzaj_przesylki'], 'PACZKA W RUCHU') !== false || strpos((string)$dostawa['rodzaj_przesylki'], 'ORLEN PACZKA') !== false ) {
                        $status = Funkcje::PokazStatusRuch($dostawa['status_przesylki']);
                    }

                    if ( strtolower((string)$dostawa['rodzaj_przesylki']) == strtolower((string)'Elektroniczny Nadawca') ) {
                        if ( $status == '0' ) {
                            $status = 'W buforze';
                        }
                    }

                    if ( stripos((string)$dostawa['rodzaj_przesylki'], 'DPD') !== false ) {
                        if ( $status == '1' ) {
                            $status = 'Utworzona';
                        } elseif ( $status == '2' ) {
                            $status = 'Etykieta wydrukowana';
                        } elseif ( $status == '3' ) {
                            $status = 'Protokół wydrukowany';
                        } elseif ( $status == '999' ) {
                            $status = 'Kurier zamówiony';
                        }
                    }
                    if ( stripos((string)$dostawa['rodzaj_przesylki'], 'GLS') !== false ) {
                        if ( $status == '1' || $status == '11' ) {
                            $status = 'W przygotowalni';
                        }
                        if ( $status == '2' || $status == '21' ) {
                            $status = 'Potwierdzona';
                        }
                        if ( $status == '9999' ) {
                            $status = 'Brak w bazie GLS';
                        }
                    }
                    if ( stripos((string)$dostawa['rodzaj_przesylki'], 'KurierInpost') !== false ) {
                        $status = InPostKurierApi::inpost_status_nazwa($dostawa['status_przesylki']);
                    }

                    if ( INTEGRACJA_BLISKAPACZKA_WLACZONY == 'tak' && $dostawa['rodzaj_przesylki'] == 'BLISKAPACZKA' ) {
                        $Statusy = BliskapaczkaApi::bliskapaczka_status_array();
                        if ( isset($Statusy[$status]) ) {
                            $status = $Statusy[$status];
                        }
                        unset($statusy);
                    }

                    if ( INTEGRACJA_GEIS_WLACZONY == 'tak' && $dostawa['rodzaj_przesylki'] == 'GEIS' ) {

                        $TablicaStatusow = array();

                        $apiGeis = new GeisApi();

                        $ListaStatusow = $apiGeis->doStatusList();
                        if ( isset($ListaStatusow) && count((array)$ListaStatusow) > 0 ) {
                            foreach ( $ListaStatusow->StatusListResult->ResponseObject->Status as $Status ) {
                                $TablicaStatusow[trim($Status->Code)] = $Status->Description;
                            }
                        }
                        if ( $status != 'Inserted' ) {
                            if ( isset($TablicaStatusow[$status]) ) {
                                $status = $TablicaStatusow[$status];
                            }
                        }
                        unset($ListaStatusow, $TablicaStatusow);
                    }

                }
                ?>
                <tr class="pozycja_off NaglowekCentruj">
                
                  <td><?php echo $dostawa['rodzaj_przesylki'] . ( $dostawa['rodzaj_przesylki'] == 'BLISKAPACZKA' || $dostawa['rodzaj_przesylki'] == 'APACZKA' || $dostawa['rodzaj_przesylki'] == 'FURGONETKA' ? ' ('.$dostawa['komentarz'].')' : '' ); ?></td>
                  <td>
                  <?php 
                    echo str_replace(',', '<br />', (string)$dostawa['numer_przesylki']);

                    if ( stripos((string)$dostawa['rodzaj_przesylki'], 'BLISKAPACZKA') !== false ) {
                        if ( $dostawa['inne_informacje'] != '' ) {
                            echo '<br />';
                            echo '(' . $dostawa['inne_informacje'] . ')';
                        }

                    }

                    echo ( isset($kodTrackingowy) && $kodTrackingowy != '' ? '<br />'.Funkcje::PodzielNazwe($kodTrackingowy, 15) : '' ); 

                    if ( stripos((string)$dostawa['rodzaj_przesylki'], 'KurierInpost') !== false ) {

                        if ($handle = opendir(KATALOG_SKLEPU . 'zarzadzanie/tmp/inPost/'.$dostawa['numer_przesylki'])) {
                            echo '<br />';
                            while (false !== ($entry = readdir($handle))) {
                                if ($entry != "." && $entry != "..") {

                                    echo '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_kurier_inpost_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=pobierz&amp;przesylka='.$dostawa['numer_przesylki'].'&amp;paczka='.$entry.'" ><b>Pobierz plik PDF</b><img src="obrazki/etykieta_pdf.png" alt="Pobierz plik PDF" /></a>';

                                }
                            }
                            closedir($handle);
                        }

                    }
                    if ( stripos((string)$dostawa['rodzaj_przesylki'], 'PACZKA W RUCHU') !== false || stripos((string)$dostawa['rodzaj_przesylki'], 'ORLEN PACZKA') !== false ) {
                        if (is_dir(KATALOG_SKLEPU . 'zarzadzanie/tmp/RUCH/'.$dostawa['numer_przesylki'])) {
                            if ($handle = opendir(KATALOG_SKLEPU . 'zarzadzanie/tmp/RUCH/'.$dostawa['numer_przesylki'])) {
                                echo '<br />';
                                while (false !== ($entry = readdir($handle))) {
                                    if ($entry != "." && $entry != "..") {

                                        echo '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_paczka_ruch_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=pobierz&amp;przesylka='.$dostawa['numer_przesylki'].'&amp;paczka='.$entry.'" ><b>Pobierz plik etykiety PDF</b><img src="obrazki/etykieta_pdf.png" alt="Pobierz plik etykiety PDF" /></a>';

                                    }
                                }
                                closedir($handle);
                            }
                        }
                    }
                  ?>
                  </td>
                  <td><?php echo date('d-m-Y H:i:s', FunkcjeWlasnePHP::my_strtotime($dostawa['data_utworzenia'])); ?></td>
                  <td><?php echo (($dostawa['reczna'] == 'nie' ) ? $dostawa['ilosc_paczek'] : '-'); ?></td>
                  <td><?php echo $status; ?></td>
                  <td><?php echo (($dostawa['reczna'] == 'nie' ) ? date('d-m-Y H:i:s', FunkcjeWlasnePHP::my_strtotime($dostawa['data_aktualizacji'])) : '-'); ?></td>
                  <?php
                  if ( $zamowienie->info['zrodlo'] == '3' ) {
                      echo '<td><img src="obrazki/'.( $dostawa['wysylka_allegro'] == '1' ? 'tak.png' : 'tak_off.png' ).'" alt="" /></td>';
                  }
                  ?>
                  <td class="rg_right IkonyPionowo">
                  <?php
                  if ($dostawa['reczna'] == 'nie' ) {
                    
                    if ( strpos((string)$dostawa['rodzaj_przesylki'], 'SendIt') !== false ) {
                      echo '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_sendit_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=status&amp;przesylka='.$dostawa['numer_przesylki'].'"><b>Pobierz status przesyłki</b><img src="obrazki/przesylka_tracking.png" alt="Pobierz status przesyłki" /></a>';
                      if ( $dostawa['status_przesylki'] > 2 && $dostawa['status_przesylki'] <= 10 ) {
                        echo '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_sendit_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=etykieta&amp;przesylka='.$dostawa['numer_przesylki'].'" ><b>Pobierz etykietę</b><img src="obrazki/etykieta_pdf.png" alt="Pobierz etykietę" /></a>';
                      }

                      if ( $dostawa['status_przesylki'] > 10 ) {
                        echo '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_sendit_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=protokol&amp;przesylka='.$dostawa['numer_przesylki'].'" ><b>Pobierz protokół</b><img src="obrazki/zamowienie_pdf.png" alt="Pobierz protokół" /></a>';
                      }
                    }

                    if ( strtolower((string)$dostawa['rodzaj_przesylki']) == strtolower((string)'KurJerzy') ) {
                      echo '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_kurjerzy_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=etykieta&amp;przesylka='.$dostawa['numer_przesylki'].'" ><b>Pobierz etykietę</b><img src="obrazki/etykieta_pdf.png" alt="Pobierz etykietę" /></a>';
                      echo '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_kurjerzy_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=status&amp;przesylka='.$dostawa['numer_przesylki'].'"><b>Pobierz informacje trackingowe</b><img src="obrazki/przesylka_tracking.png" alt="Pobierz informacje trackingowe" /></a>';
                    }

                    if ( strtolower((string)$dostawa['rodzaj_przesylki']) == strtolower((string)'InPost') && $dostawa['inne_informacje'] != 'SHIPX' ) {
                      $refresh = false;

                      if ( $dostawa['status_przesylki'] == 'Created' ) {
                        echo '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_inpost_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=usun&amp;przesylka='.$dostawa['numer_przesylki'].'" ><b>Usuń paczkę</b><img src="obrazki/kasuj.png" alt="Usuń paczkę" /></a>';
                      }

                      echo '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_inpost_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=etykieta&amp;przesylka='.$dostawa['numer_przesylki'].'"><b>Wygeneruj etykietę</b><img src="obrazki/etykieta_pdf.png" alt="Wygeneruj etykietę" /></a>';

                      //if ( $dostawa['status_przesylki'] != 'Created' ) {
                        echo '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_inpost_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=status&amp;przesylka='.$dostawa['numer_przesylki'].'" ><b>Pobierz informacje trackingowe</b><img src="obrazki/przesylka_tracking.png" alt="Pobierz informacje trackingowe" /></a>';
                      //}
                      if ( $dostawa['status_przesylki'] != 'Created' && $dostawa['komentarz'] == '' ) {
                        $refresh = true;
                        echo '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_inpost_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=potwierdzenie&amp;przesylka='.$dostawa['numer_przesylki'].'" ' . ( $refresh == true ? 'class="download"' : '' ) . '><b>Potwierdzenie nadania paczki</b><img src="obrazki/faktura.png" alt="Potwierdzenie nadania paczki" /></a>';
                      }
                    }

                    if ( strtolower((string)$dostawa['rodzaj_przesylki']) == strtolower((string)'Siodemka') ) {
                      echo '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_siodemka_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=etykieta&amp;przesylka='.$dostawa['numer_przesylki'].'" ><b>Pobierz etykietę</b><img src="obrazki/etykieta_pdf.png" alt="Pobierz etykietę" /></a>';
                      echo '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_siodemka_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=status&amp;przesylka='.$dostawa['numer_przesylki'].'"><b>Pobierz informacje trackingowe</b><img src="obrazki/przesylka_tracking.png" alt="Pobierz informacje trackingowe" /></a>';
                      echo '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_siodemka_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=list&amp;przesylka='.$dostawa['numer_przesylki'].'" ><b>Potwierdzenie nadania paczki</b><img src="obrazki/faktura.png" alt="Potwierdzenie nadania paczki" /></a>';
                    }

                    if ( strtolower((string)$dostawa['rodzaj_przesylki']) == strtolower((string)'DHL') ) {
                      if ( $dostawa['protokol'] == '' ) {
                        echo '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_dhl_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=usun&amp;przesylka='.$dostawa['numer_przesylki'].'" ><b>Usuń przesyłkę w DHL</b><img src="obrazki/kasuj.png" alt="Usuń przesyłkę w DHL" /></a>';
                      }
                      echo '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_dhl_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=etykieta&amp;przesylka='.$dostawa['numer_przesylki'].'" ><b>Pobierz etykietę</b><img src="obrazki/etykieta_pdf.png" alt="Pobierz etykietę" /></a>';
                      echo '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_dhl_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=tracking&amp;przesylka='.$dostawa['numer_przesylki'].'"><b>Pobierz historię procesu doręczania</b><img src="obrazki/przesylka_tracking.png" alt="Pobierz historię procesu doręczania" /></a>';
                      echo '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_dhl_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=drop&amp;przesylka='.$dostawa['numer_przesylki'].'" ><b>Usuń informację z bazy</b><img src="obrazki/smietnik.png" alt="Usuń informację z bazy" /></a>';
                    }

                    if ( strtolower((string)$dostawa['rodzaj_przesylki']) == strtolower((string)'Elektroniczny Nadawca') ) {
                      $danePrzesylki = explode(':', (string)$dostawa['komentarz']);

                      if ( $dostawa['status_przesylki'] == '0' ) {
                        echo '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_enadawca_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=etykieta&amp;przesylka='.$danePrzesylki[0].'" ><b>Pobierz etykietę</b><img src="obrazki/etykieta_pdf.png" alt="Pobierz etykietę" /></a>';

                        echo '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_enadawca_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=pobranie&amp;przesylka='.$danePrzesylki[0].'" ><b>Pobierz blankiet pobrania</b><img src="obrazki/proforma_pdf.png" alt="Pobierz blankiet pobrania" /></a>';

                        echo '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_enadawca_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=clearbuforGuid&amp;przesylka='.$danePrzesylki[0].'&amp;przesylkaId='.$dostawa['id_przesylki'].'" ><b>Usuń z bufora</b><img src="obrazki/kasuj.png" alt="Usuń z bufora" /></a>';
                      }

                    }

                    if ( strtolower((string)$dostawa['rodzaj_przesylki']) == strtolower((string)'Kex') ) {
                      if ( $status == 'OAK' || $status == 'WPR' ) {
                        echo '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_kex_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=anuluj&amp;przesylka='.$dostawa['numer_przesylki'].'" ><b>Anuluj przesyłkę</b><img src="obrazki/kasuj.png" alt="Anuluj przesyłkę" /></a>';
                      }
                      echo '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_kex_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=status&amp;przesylka='.$dostawa['numer_przesylki'].'" ><b>Pobierz status przesyłki</b><img src="obrazki/przesylka_tracking.png" alt="Pobierz status przesyłki" /></a><br />';
                      if ( $status == 'OAK' || $status == 'WPR' ) {
                          echo '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_kex_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=etykieta&amp;przesylka='.$dostawa['numer_przesylki'].'" ><b>Pobierz etykietę</b><img src="obrazki/etykieta_pdf.png" alt="Pobierz etykietę" /></a>';
                      }
                      if ( $status != 'OAK' ) {
                        echo '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_kex_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=list&amp;przesylka='.$dostawa['numer_przesylki'].'" ><b>Pobierz list przewozowy</b><img src="obrazki/zamowienie_pdf.png" alt="Pobierz list przewozowy" /></a>';
                      }
                    }

                    if ( $dostawa['rodzaj_przesylki'] == 'FURGONETKA' ) {

                      if ( $dostawa['status_przesylki'] == 'waiting' ) {
                            echo '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_furgonetka_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=szczegoly&amp;przesylka='.$dostawa['inne_informacje'].'" ><b>Pokaż szczegóły</b><img src="obrazki/przesylka_tracking.png" alt="Pokaż szczegóły" /></a>';
                            echo '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_furgonetka_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=usun&amp;przesylka='.$dostawa['inne_informacje'].'" ><b>Usuń przesyłkę z koszyka do wysłania</b><img src="obrazki/kasuj.png" alt="Usuń przesyłkę z koszyka do wysłania" /></a>';

                            if ( $dostawa['uuid_order'] == '' ) {
                              echo '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_furgonetka_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=zamow&amp;przesylka='.$dostawa['inne_informacje'].'" ><b>Zamów paczkę bez podjazdu kuriera</b><img src="obrazki/przesylka_dodaj.png" alt="Zamów paczkę bez podjazdu kuriera" /></a>';
                            } else {
                              echo '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_furgonetka_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=StatusZamowienia&amp;przesylka='.$dostawa['inne_informacje'].'&amp;uuid='.$dostawa['uuid_order'].'" ><b>Status zamówienia<br />UUID : '.$dostawa['uuid_order'].'</b><img src="obrazki/allegro_trwa.png" alt="Status zamówienia" /></a>';
                            }

                            

                      
                      } elseif ( $dostawa['status_przesylki'] == 'ordered' ) {

                          echo '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_furgonetka_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=szczegoly&amp;przesylka='.$dostawa['inne_informacje'].'" ><b>Pokaż szczegóły</b><img src="obrazki/przesylka_tracking.png" alt="Pokaż szczegóły" /></a>';

                          if ( $dostawa['uuid_cancel'] == '' && $dostawa['uuid_order'] == '' ) {
                            echo '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_furgonetka_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=anuluj&amp;przesylka='.$dostawa['inne_informacje'].'" ><b>Anuluj z zamówionych</b><img src="obrazki/powrot.png" alt="Anuluj z zamówionych" /></a>';
                          } elseif ( $dostawa['uuid_cancel'] != '' && $dostawa['uuid_order'] == '' ) {
                            echo '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_furgonetka_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=StatusAnulowania&amp;przesylka='.$dostawa['inne_informacje'].'&amp;uuid='.$dostawa['uuid_cancel'].'" ><b>Sprawdź status anulowania</b><img src="obrazki/allegro_czeka.png" alt="Sprawdź status anulowania" /></a>';
                          }

                          if ( $dostawa['uuid_order'] != '' ) {
                              echo '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_furgonetka_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=StatusZamowienia&amp;przesylka='.$dostawa['inne_informacje'].'&amp;uuid='.$dostawa['uuid_order'].'" ><b>Status zamówienia<br />UUID : '.$dostawa['uuid_order'].'</b><img src="obrazki/allegro_trwa.png" alt="Status zamówienia" /></a>';
                          }

                          if ( $dostawa['uuid_cancel'] == '' ) {
                              echo '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_furgonetka_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=etykieta&amp;przesylka='.$dostawa['inne_informacje'].'" ><b>Pobierz etykietę</b><img src="obrazki/etykieta_pdf.png" alt="Pobierz etykietę" /></a>';

                              if ( $dostawa['status_odbioru'] == 'D2D' || $dostawa['status_odbioru'] == 'D2P' || $dostawa['status_odbioru'] == 'P2D' ) {
                                echo '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_furgonetka_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=protokol&amp;przesylka='.$dostawa['inne_informacje'].'" ><b>Pobierz protokół</b><img src="obrazki/pdf_manifest.png" alt="Pobierz protokół" /></a>';
                              }
                          }
                          
                      } elseif ( $dostawa['status_przesylki'] == 'cancelled' ) {

                            echo '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_furgonetka_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=szczegoly&amp;przesylka='.$dostawa['inne_informacje'].'" ><b>Pokaż szczegóły</b><img src="obrazki/przesylka_tracking.png" alt="Pokaż szczegóły" /></a>';
                            echo '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_furgonetka_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=usunBaza&amp;przesylka='.$dostawa['inne_informacje'].'" ><b>Usuń przesyłkę z bazy sklepu</b><img src="obrazki/kasuj.png" alt="Usuń przesyłkę z bazy sklepu" /></a>';

                      } else {

                          echo '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_furgonetka_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=etykieta&amp;przesylka='.$dostawa['inne_informacje'].'" ><b>Pobierz etykietę</b><img src="obrazki/etykieta_pdf.png" alt="Pobierz etykietę" /></a>';
                          echo '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_furgonetka_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=tracking&amp;przesylka='.$dostawa['inne_informacje'].'&amp;serwis='.$dostawa['komentarz'].'" ><b>Pokaż szczegóły</b><img src="obrazki/przesylka_tracking.png" alt="Pokaż szczegóły" /></a>';
                      }
                    }

                    if ( strtolower((string)$dostawa['rodzaj_przesylki']) == strtolower((string)'KurierInpost') ) {
                      $refresh = false;

                      if ( $dostawa['status_przesylki'] == 'PPN' ||  $dostawa['status_przesylki'] == 'ZWK' ) {
                        echo '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_kurier_inpost_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=usun&amp;przesylka='.$dostawa['numer_przesylki'].'" ><b>Usuń paczkę</b><img src="obrazki/kasuj.png" alt="Usuń paczkę" /></a>';
                      }

                      //echo '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_kurier_inpost_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=etykieta&amp;przesylka='.$dostawa['numer_przesylki'].'"><b>Wygeneruj etykietę</b><img src="obrazki/etykieta_pdf.png" alt="Wygeneruj etykietę" /></a>';

                      if ( $dostawa['status_przesylki'] == 'PPN' ) {
                        echo '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_kurier_inpost_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=zamowienie&amp;przesylka='.$dostawa['numer_przesylki'].'&amp;waga='.$dostawa['waga_przesylki'].'" ><b>Zamów kuriera</b><img src="obrazki/przesylka_dodaj.png" alt="Zamów kuriera" /></a>';
                      }

                      if ( $dostawa['status_przesylki'] == 'PPN' ||  $dostawa['status_przesylki'] == 'ZWK' ) {
                        echo '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_kurier_inpost_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=manifest&amp;przesylka='.$dostawa['numer_przesylki'].'" ><b>Wygeneruj Manifest</b><img src="obrazki/pdf_manifest.png" alt="Wygeneruj Manifest" /></a>';
                      }

                      if ( $dostawa['status_przesylki'] == 'PPN' ) {
                        echo '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_kurier_inpost_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=status&amp;przesylka='.$dostawa['numer_przesylki'].'" ><b>Pobierz informacje trackingowe</b><img src="obrazki/przesylka_tracking.png" alt="Pobierz informacje trackingowe" /></a>';
                      }
                    }

                    if ( strtolower((string)$dostawa['rodzaj_przesylki']) == strtolower((string)'DPD') ) {
                          echo '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_dpd_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=etykieta&amp;przesylka='.$dostawa['komentarz'].'&amp;destination='.$dostawa['kraj_dostawy'].'" ><b>Pobierz etykietę</b><img src="obrazki/etykieta_pdf.png" alt="Pobierz etykietę" /></a>';
                          if ( $dostawa['status_przesylki'] > 0 ) {
                            echo '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_dpd_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=protokol&amp;przesylka='.$dostawa['komentarz'].'&amp;fid='.$dostawa['inne_informacje'].'" ><b>Pobierz protokół</b><img src="obrazki/proforma_pdf.png" alt="Pobierz protokół" /></a>';
                          }
                          if ( $dostawa['status_przesylki'] != 999 ) {
                            echo '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_dpd_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=usun&amp;przesylka='.$dostawa['komentarz'].'" ><b>Usuń przesyłkę</b><img src="obrazki/kasuj.png" alt="Usuń przesyłkę" /></a>';
                          } else {
                            echo '<img src="obrazki/kasuj_off.png" alt="Usuń przesyłkę" />';
                          }
                          /*
                          if ( $dostawa['status_przesylki'] > 1 ) {
                            echo '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_dpd_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=zamowienie&amp;przesylka='.$dostawa['komentarz'].'" ><b>Zamów kuriera</b><img src="obrazki/przesylka_dodaj.png" alt="Zamów kuriera" /></a>';
                          }
                          */
                    }

                    if ( strtolower((string)$dostawa['rodzaj_przesylki']) == strtolower((string)'GLS') ) {
                          if ( $dostawa['status_przesylki'] == '1' || $dostawa['status_przesylki'] == '11' ) {
                            echo '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_gls_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=usun&amp;przesylka='.$dostawa['komentarz'].'" ><b>Usuń przesyłkę z przechowalni</b><img src="obrazki/kasuj.png" alt="Usuń przesyłkę z przechowalni" /></a>';
                            echo '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_gls_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=etykieta&amp;przesylka='.$dostawa['komentarz'].'" ><b>Pobierz etykietę</b><img src="obrazki/etykieta_pdf.png" alt="Pobierz etykietę" /></a>';
                            echo '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_gls_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=potwierdzenie&amp;przesylka='.$dostawa['komentarz'].'" ><b>Utwórz potwierdzenie nadania</b><img src="obrazki/przesylka_dodaj.png" alt="Utwórz potwierdzenie nadania" /></a>';
                          }

                          if ( $dostawa['status_przesylki'] == '2' || $dostawa['status_przesylki'] == '21' ) {
                            echo '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_gls_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=etykietaNumer&amp;przesylka='.$dostawa['numer_przesylki'].'" ><b>Pobierz etykietę</b><img src="obrazki/etykieta_pdf.png" alt="Pobierz etykietę" /></a>';
                            echo '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_gls_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=potwierdzenieDruk&amp;przesylka='.$dostawa['protokol'].'" ><b>Pobierz protokół</b><img src="obrazki/proforma_pdf.png" alt="Pobierz protokół" /></a>';
                          }
                          if ( $dostawa['status_przesylki'] == '9999' ) {
                            echo '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_gls_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=usunbaza&amp;przesylka='.$dostawa['komentarz'].'" ><b>Usuń przesyłkę z bazy sklepu</b><img src="obrazki/kasuj.png" alt="Usuń przesyłkę z bazy sklepu" /></a>';
                          }

                   }

                    if ( strtolower((string)$dostawa['rodzaj_przesylki']) == strtolower((string)'PACZKA W RUCHU') || strtolower((string)$dostawa['rodzaj_przesylki']) == strtolower((string)'ORLEN PACZKA') ) {
                          if ( $dostawa['status_przesylki'] != '201' ) {
                              echo '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_paczka_ruch_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=etykieta&amp;przesylka='.$dostawa['numer_przesylki'].'" ><b>Pobierz duplikat etykiety</b><img src="obrazki/etykieta_pdf.png" alt="Pobierz duplikat etykiety" /></a>';
                              echo '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_paczka_ruch_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=protokol&amp;przesylka='.$dostawa['numer_przesylki'].'" ><b>Pobierz protokół</b><img src="obrazki/proforma_pdf.png" alt="Pobierz protokół" /></a>';
                              echo '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_paczka_ruch_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=tracking&amp;przesylka='.$dostawa['numer_przesylki'].'" ><b>Sprawdź status przesyłki</b><img src="obrazki/przesylka_tracking.png" alt="Sprawdź status przesyłki" /></a>';
                          }
                          
                          if ( $dostawa['status_przesylki'] == '200' ) {
                            echo '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_paczka_ruch_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=usun&amp;przesylka='.$dostawa['numer_przesylki'].'" ><b>Anuluj przesyłkę</b><img src="obrazki/przesylka_anuluj.png" alt="Anuluj przesyłkę" /></a>';
                          }
                          if ( $dostawa['status_przesylki'] == '201' ) {
                            echo '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_paczka_ruch_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=drop&amp;przesylka='.$dostawa['numer_przesylki'].'" ><b>Usuń informację z bazy</b><img src="obrazki/smietnik.png" alt="Usuń informację z bazy" /></a>';
                          }

                    }

                    if ( $dostawa['rodzaj_przesylki'] == 'APACZKA' ) {
                          if ( $dostawa['status_przesylki'] == 'NEW' ) {
                            echo '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_apaczka_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=anuluj&amp;przesylka='.$dostawa['inne_informacje'].'" ><b>Anuluj zlecenie</b><img src="obrazki/kasuj.png" alt="Anuluj zlecenie" /></a>';
                          }
                          if ( $dostawa['status_przesylki'] != 'CANCELLED' ) {
                              echo '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_apaczka_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=etykieta&amp;przesylka='.$dostawa['inne_informacje'].'" ><b>Pobierz etykietę i list przewozowy</b><img src="obrazki/etykieta_pdf.png" alt="Pobierz etykietę i list przewozowy" /></a>';
                              echo '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_apaczka_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=potwierdzenie&amp;przesylka='.$dostawa['inne_informacje'].'" ><b>Pobierz potwierdzenie nadania</b><img src="obrazki/proforma_pdf.png" alt="Pobierz potwierdzenie nadania" /></a>';
                          }
                          echo '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_apaczka_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=usun&amp;przesylka='.$dostawa['inne_informacje'].'" ><b>Usuń z bazy sklepu</b><img src="obrazki/smietnik.png" alt="Usuń z bazy sklepu" /></a>';

                    }

                    if ( INTEGRACJA_KURIER_INPOST_SHIPX_WLACZONY == 'tak' ) {
                        if ( stripos((string)$dostawa['rodzaj_przesylki'], 'INPOST') !== false  && $dostawa['inne_informacje'] == 'SHIPX' ) {
                            $StatusPozycja = 0;
                            if ( isset($StatusyInpost->items) && count((array)$StatusyInpost->items) ) {
                                $StatusPozycja = array_search($dostawa['status_przesylki'], array_column($StatusyInpost->items, 'name'));
                            }
                            if ( $StatusPozycja > 0 && $StatusPozycja < 2 ) {
                                echo '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_inpost_shipx_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=usun&amp;przesylka='.$dostawa['protokol'].'" ><b>Anuluj przesyłkę</b><img src="obrazki/kasuj.png" alt="Anuluj przesyłkę" /></a>';
                            }
                            if ( $StatusPozycja > 2 ) {
                                echo '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_inpost_shipx_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=etykieta&amp;przesylka='.$dostawa['protokol'].'" ><b>Drukuj etykietę</b><img src="obrazki/etykieta_pdf.png" alt="Drukuj etykietę" /></a>';
                            }

                            if ( $dostawa['inne_informacje_1'] != 'parcel_locker' && $StatusPozycja > 2 ) {
                                echo '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_inpost_shipx_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=potwierdzenie&amp;przesylka='.$dostawa['protokol'].'" ><b>Drukuj potwierdzenie odbioru</b><img src="obrazki/proforma_pdf.png" alt="Drukuj potwierdzenie odbioru" /></a>';
                            }
                            echo '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_inpost_shipx_akcja.php?id_poz='.(int)$dostawa['id_przesylki'].'&amp;zakladka=1&amp;akcja=status&amp;przesylka='.$dostawa['protokol'] . ( $dostawa['protokol_id'] != '' ? '&amp;zlecenie='.$dostawa['protokol_id'] : '' ) . '" ><b>Sprawdź status przesyłki</b><img src="obrazki/przesylka_tracking.png" alt="Sprawdź status przesyłki" /></a>';

                            echo '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_inpost_shipx_akcja.php?id_poz='.(int)$dostawa['id_przesylki'].'&amp;zakladka=1&amp;akcja=usun_baza&amp;przesylka='.$dostawa['protokol'].'" ><b>Usuń informacje z bazy sklepu</b><img src="obrazki/kasuj_dysk.png" alt="Usuń informacje z bazy sklepu" /></a>';

                        }
                    }

                    if ( INTEGRACJA_BLISKAPACZKA_WLACZONY == 'tak' && $dostawa['rodzaj_przesylki'] == 'BLISKAPACZKA' ) {

                        $apiBliskaPaczka = new BliskapaczkaApi();
                        $LinkSledzenia = '';

                        $wynikBliskaPaczka = $apiBliskaPaczka->commandGet('v2/order/'.$dostawa['numer_przesylki']);

                        if ( isset($wynikBliskaPaczka) && $wynikBliskaPaczka->changes ) {

                            if ( $wynikBliskaPaczka->trackingNumber != '' ) {
                                $LinkSledzenia = Funkcje::LinkSledzeniaWysylki($wynikBliskaPaczka->operatorName, $wynikBliskaPaczka->trackingNumber);
                            }

                            $AktualnyStatus = end($wynikBliskaPaczka->changes);

                            $pola = array();

                            $pola = array(
                                          array('orders_shipping_status',$AktualnyStatus->status),
                                          array('orders_shipping_date_modified',date('Y-m-d G:i:s', FunkcjeWlasnePHP::my_strtotime($AktualnyStatus->dateTime))),
                                          array('orders_shipping_misc',$wynikBliskaPaczka->trackingNumber),
                                          array('orders_shipping_link',$LinkSledzenia)
                            );

                            $db->update_query('orders_shipping' , $pola, " orders_shipping_id = '".(int)$dostawa['id_przesylki']."'");
                            unset($pola);

                        }

                        if ( $dostawa['status_przesylki'] == 'READY_TO_SEND' || $dostawa['status_przesylki'] == 'SAVED' || $dostawa['status_przesylki'] == 'WAITING_FOR_PAYMENT' || $dostawa['status_przesylki'] == 'PAYMENT_CONFIRMED' || $dostawa['status_przesylki'] == 'PAYMENT_REJECTED' || $dostawa['status_przesylki'] == 'PROCESSING' || $dostawa['status_przesylki'] == 'ERROR') {
                            echo '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_bliskapaczka_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=usun&amp;przesylka='.$dostawa['numer_przesylki'].'" ><b>Anuluj przesyłkę</b><img src="obrazki/kasuj.png" alt="Anuluj przesyłkę" /></a>';
                        }
                        echo '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_bliskapaczka_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=status&amp;przesylka='.$dostawa['numer_przesylki'].'" ><b>Sprawdź status</b><img src="obrazki/przesylka_tracking.png" alt="Sprawdź status" /></a>';

                        if ( $dostawa['status_przesylki'] != 'NEW' && $dostawa['status_przesylki'] != 'SAVED' && $dostawa['status_przesylki'] != 'WAITING_FOR_PAYMENT' && $dostawa['status_przesylki'] != 'PAYMENT_CONFIRMED' && $dostawa['status_przesylki'] != 'PAYMENT_REJECTED' && $dostawa['status_przesylki'] != 'PROCESSING' && $dostawa['status_przesylki'] != 'ERROR'  && $dostawa['status_przesylki'] != 'PAYMENT_CANCELLATION_ERROR'  && $dostawa['status_przesylki'] != 'ADVISING'  && $dostawa['status_przesylki'] != 'CANCELED') {
                            echo '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_bliskapaczka_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=etykieta&amp;przesylka='.$dostawa['numer_przesylki'].'" ><b>Pobierz etykietę</b><img src="obrazki/etykieta_pdf.png" alt="Pobierz etykietę" /></a>';
                        }

                        if ( ($dostawa['status_odbioru'] == 'D2D' || $dostawa['status_odbioru'] == 'D2P') && $dostawa['status_przesylki'] == 'READY_TO_SEND' ) {
                            echo '<br />';
                            echo '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_bliskapaczka_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=potwierdzenie&amp;przesylka='.$dostawa['numer_przesylki'].'" ><b>Protokół odbioru</b><img src="obrazki/proforma_pdf.png" alt="Protokół odbioru" /></a>';
                            //echo '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_bliskapaczka_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=zamowienie&amp;przesylka='.$dostawa['numer_przesylki'].'" ><b>Zamów kuriera</b><img src="obrazki/przesylka_dodaj.png" alt="Zamów kuriera" /></a>';
                        }
                        if ( $dostawa['status_przesylki'] == 'ERROR' ) {
                            echo '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_bliskapaczka_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=ponow&amp;przesylka='.$dostawa['numer_przesylki'].'" ><b>Ponów zamówienie</b><img src="obrazki/blad.png" alt="Ponów zamówienie" /></a>';
                        }


                    }

                    if ( INTEGRACJA_GEIS_WLACZONY == 'tak' && strtolower((string)$dostawa['rodzaj_przesylki']) == strtolower('GEIS') ) {
                          if ( $dostawa['status_przesylki'] != 'Anulowana' ) {
                            echo '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_geis_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=usun&amp;przesylka='.$dostawa['numer_przesylki'].'" ><b>Usuń przesyłkę z GEIS</b><img src="obrazki/kasuj.png" alt="Usuń przesyłkę z GEIS" /></a>';
                          }
                          echo '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_geis_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=status&amp;przesylka='.$dostawa['numer_przesylki'].'" ><b>Sprawdź status przesyłki</b><img src="obrazki/przesylka_tracking.png" alt="Sprawdź status przesyłki" /></a>';
                          if ( $dostawa['komentarz'] == 'export' ) {
                                echo '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_geis_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=etykieta&amp;przesylka='.$dostawa['numer_przesylki'].'" ><b>Pobierz etykietę</b><img src="obrazki/etykieta_pdf.png" alt="Pobierz etykietę" /></a>';
                          }
                          echo '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_geis_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=usunbaza&amp;przesylka='.$dostawa['numer_przesylki'].'" ><b>Usuń przesyłkę z bazy sklepu</b><img src="obrazki/kasuj.png" alt="Usuń przesyłkę z bazy sklepu" /></a>';

                   }

                  } else {
                   
                    echo '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_reczna_usun.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;&amp;przesylka='.$dostawa['id_przesylki'].'" ><b>Usuń wysyłkę</b><img src="obrazki/delete.png" alt="Usuń wysyłkę" /></a>';
                    
                  }

                  if ( $zamowienie->info['zrodlo'] == '3' ) {
                    if ( $dostawa['wysylka_allegro'] == '0' ) {
                        echo '<a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_do_allegro.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;&amp;przesylka='.$dostawa['id_przesylki'].'" ><b>Wyślij numer do Allegro</b><img src="obrazki/allegro_lapka.png" alt="Wyślij numer do Allegro" /></a>';
                    } else {
                        echo '<img src="obrazki/allegro_lapka_off.png" alt="Wyślij numer do Allegro" />';
                    }
                  }

                  ?>

                  </td>
                  
                </tr>
                <?php
              }

            } else {
              ?>
              <tr class="pozycja_brak_danych">
                <td style="text-align:left" colspan="7">Brak pozycji do wyświetlenia</td>
              </tr>
              <?php
            } ?>
          </table>
          
        </div>

        <div style="margin-top:20px;">
        
            <div class="ObramowanieTabeli" style="display:inline-block">
            
              <table class="listing_tbl">
              
                <tr class="div_naglowek">
                  <td>Utwórz wysyłkę</td>
                </tr>
                
              </table>
              
              <?php
              $integracje = false;
              if ($parametr_kurierzy['INTEGRACJA_SENDIT_WLACZONY']['0'] == 'tak') $integracje = true;
              if ($parametr_kurierzy['INTEGRACJA_POCZTA_EN_WLACZONY']['0'] == 'tak') $integracje = true;
              if ($parametr_kurierzy['INTEGRACJA_KURJERZY_WLACZONY']['0'] == 'tak') $integracje = true;
              if ($parametr_kurierzy['INTEGRACJA_SIODEMKA_WLACZONY']['0'] == 'tak') $integracje = true;
              if ($parametr_kurierzy['INTEGRACJA_INPOST_WLACZONY']['0'] == 'tak') $integracje = true;
              if ($parametr_kurierzy['INTEGRACJA_DHL_WLACZONY']['0'] == 'tak') $integracje = true;
              if ($parametr_kurierzy['INTEGRACJA_FURGONETKA_WLACZONY']['0'] == 'tak') $integracje = true;
              if ($parametr_kurierzy['INTEGRACJA_KURIER_INPOST_WLACZONY']['0'] == 'tak') $integracje = true;
              if ($parametr_kurierzy['INTEGRACJA_DPD_WLACZONY']['0'] == 'tak') $integracje = true;
              if ($parametr_kurierzy['INTEGRACJA_PACZKARUCH_WLACZONY']['0'] == 'tak') $integracje = true;
              if ($parametr_kurierzy['INTEGRACJA_GLS_WLACZONY']['0'] == 'tak') $integracje = true;
              if ($parametr_kurierzy['INTEGRACJA_APACZKAV2_WLACZONY']['0'] == 'tak') $integracje = true;
              if ($parametr_kurierzy['INTEGRACJA_KURIER_INPOST_SHIPX_WLACZONY']['0'] == 'tak') $integracje = true;
              if ($parametr_kurierzy['INTEGRACJA_KURIER_INPOST_SHIPX_WLACZONY']['0'] == 'tak') $integracje = true;
              if ($parametr_kurierzy['INTEGRACJA_BLISKAPACZKA_WLACZONY']['0'] == 'tak') $integracje = true;

              if ( !$integracje ) {?>
              <div class="maleInfo" style="margin:10px">Brak włączonych modułów integracji z firmami kurierskimi</div>
              <?php } ?>

              <div class="IkonyWysylek">
              
                  <?php if ( isset($parametr_kurierzy['INTEGRACJA_BLISKAPACZKA_WLACZONY']['0']) && $parametr_kurierzy['INTEGRACJA_BLISKAPACZKA_WLACZONY']['0'] == 'tak' ) { ?>
                  <div>
                      <a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_bliskapaczka.php?id_poz=<?php echo (int)$_GET['id_poz']; ?>&amp;zakladka=1"><b>Utwórz przesyłkę Bliskapaczka</b><img src="obrazki/logo/logo_bliskapaczka_min.png" alt="Utwórz przesyłkę Bliskapaczka" /></a>
                  </div>
                  <?php } ?>

                  <?php if ( isset($parametr_kurierzy['INTEGRACJA_SENDIT_WLACZONY']['0']) && $parametr_kurierzy['INTEGRACJA_SENDIT_WLACZONY']['0'] == 'tak' ) { ?>
                  <div>
                      <a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_sendit.php?id_poz=<?php echo (int)$_GET['id_poz']; ?>&amp;zakladka=1"><b>Utwórz przesyłkę SendIt</b><img src="obrazki/logo/logo_sendit_min.png" alt="Utwórz przesyłkę SendIt" /></a>
                  </div>
                  <?php } ?>

                  <?php if ( isset($parametr_kurierzy['INTEGRACJA_POCZTA_EN_WLACZONY']['0']) && $parametr_kurierzy['INTEGRACJA_POCZTA_EN_WLACZONY']['0'] == 'tak' ) { ?>
                  <div>
                      <a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_enadawca.php?id_poz=<?php echo (int)$_GET['id_poz']; ?>&amp;zakladka=1"><b>Utwórz przesyłkę do Elektronicznego nadawcy</b><img src="obrazki/logo/logo_en_min.png" alt="Utwórz przesyłkę do Elektronicznego nadawcy" /></a>
                  </div>
                  <?php } ?>

                  <?php if ( isset($parametr_kurierzy['INTEGRACJA_FURGONETKA_WLACZONY']['0']) && $parametr_kurierzy['INTEGRACJA_FURGONETKA_WLACZONY']['0'] == 'tak' ) { ?>
                  <div>
                      <a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_furgonetka.php?id_poz=<?php echo (int)$_GET['id_poz']; ?>&amp;zakladka=1"><b>Utwórz przesyłkę do serwisu FURGONETKA</b><img src="obrazki/logo/logo_furgonetka_min.png" alt="Utwórz przesyłkę do serwisu FURGONETKA" /></a>
                  </div>
                  <?php } ?>

                  <?php if ( isset($parametr_kurierzy['INTEGRACJA_KURJERZY_WLACZONY']['0']) && $parametr_kurierzy['INTEGRACJA_KURJERZY_WLACZONY']['0'] == 'tak' ) { ?>
                  <div>
                      <a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_kurjerzy.php?id_poz=<?php echo (int)$_GET['id_poz']; ?>&amp;zakladka=1"><b>Utwórz przesyłkę KurJerzy.pl</b><img src="obrazki/logo/logo_kurjerzy_min.png" alt="Utwórz przesyłkę KurJerzy.pl" /></a>
                  </div>
                  <?php } ?>

                  <?php if ( isset($parametr_kurierzy['INTEGRACJA_SIODEMKA_WLACZONY']['0']) && $parametr_kurierzy['INTEGRACJA_SIODEMKA_WLACZONY']['0'] == 'tak' ) { ?>
                  <div>
                      <a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_siodemka.php?id_poz=<?php echo (int)$_GET['id_poz']; ?>&amp;zakladka=1"><b>Utwórz przesyłkę Siódemka</b><img src="obrazki/logo/logo_siodemka_min.png" alt="Utwórz przesyłkę Siódemka" /></a>
                  </div>
                  <?php } ?>

                  <?php if ( isset($parametr_kurierzy['INTEGRACJA_INPOST_WLACZONY']['0']) && $parametr_kurierzy['INTEGRACJA_INPOST_WLACZONY']['0'] == 'tak' ) { ?>
                  <div>
                      <a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_inpost.php?id_poz=<?php echo (int)$_GET['id_poz']; ?>&amp;zakladka=1"><b>Utwórz przesyłkę Paczkomaty InPost</b><img src="obrazki/logo/logo_inpost_min.png" alt="Utwórz przesyłkę Paczkomaty InPost" /></a>
                  </div>
                  <?php } ?>

                  <?php if ( isset($parametr_kurierzy['INTEGRACJA_KURIER_INPOST_WLACZONY']['0']) && $parametr_kurierzy['INTEGRACJA_KURIER_INPOST_WLACZONY']['0'] == 'tak' ) { ?>
                  <div>
                      <a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_kurier_inpost.php?id_poz=<?php echo (int)$_GET['id_poz']; ?>&amp;zakladka=1"><b>Utwórz przesyłkę Kurier InPost</b><img src="obrazki/logo/logo_kurier_inpost_min.png" class="toolTipTop" alt="Utwórz przesyłkę Kurier InPost" /></a>
                  </div>
                  <?php } ?>

                  <?php if ( isset($parametr_kurierzy['INTEGRACJA_KURIER_INPOST_SHIPX_WLACZONY']['0']) && $parametr_kurierzy['INTEGRACJA_KURIER_INPOST_SHIPX_WLACZONY']['0'] == 'tak' ) { ?>
                  <div>
                      <a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_inpost_shipx.php?id_poz=<?php echo (int)$_GET['id_poz']; ?>&amp;zakladka=1"><b>Utwórz przesyłkę InPost</b><img src="obrazki/logo/logo_kurier_inpost_min.png" class="toolTipTop" alt="Utwórz przesyłkę Kurier InPost" /></a>
                  </div>
                  <?php } ?>

                  <?php if ( isset($parametr_kurierzy['INTEGRACJA_DHL_WLACZONY']['0']) && $parametr_kurierzy['INTEGRACJA_DHL_WLACZONY']['0'] == 'tak' ) { ?>
                  <div>
                      <a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_dhl.php?id_poz=<?php echo (int)$_GET['id_poz']; ?>&amp;zakladka=1"><b>Utwórz przesyłkę DHL</b><img src="obrazki/logo/logo_dhl_min.png"  alt="Utwórz przesyłkę DHL" /></a>
                  </div>
                  <?php } ?>

                  <?php if ( isset($parametr_kurierzy['INTEGRACJA_DPD_WLACZONY']['0']) && $parametr_kurierzy['INTEGRACJA_DPD_WLACZONY']['0'] == 'tak' ) { ?>
                  <div>
                      <a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_dpd.php?id_poz=<?php echo (int)$_GET['id_poz']; ?>&amp;zakladka=1"><b>Utwórz przesyłkę DPD</b><img src="obrazki/logo/logo_dpd_min.png"  alt="Utwórz przesyłkę DPD" /></a>
                  </div>
                  <?php } ?>

                  <?php if ( isset($parametr_kurierzy['INTEGRACJA_GLS_WLACZONY']['0']) && $parametr_kurierzy['INTEGRACJA_GLS_WLACZONY']['0'] == 'tak' ) { ?>
                  <div>
                      <a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_gls.php?id_poz=<?php echo (int)$_GET['id_poz']; ?>&amp;zakladka=1"><b>Utwórz przesyłkę GLS</b><img src="obrazki/logo/logo_gls_min.png"  alt="Utwórz przesyłkę GLS" /></a>
                  </div>
                  <?php } ?>

                  <?php if ( isset($parametr_kurierzy['INTEGRACJA_PACZKARUCH_WLACZONY']['0']) && $parametr_kurierzy['INTEGRACJA_PACZKARUCH_WLACZONY']['0'] == 'tak' ) { ?>
                  <div>
                      <a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_paczka_ruch.php?id_poz=<?php echo (int)$_GET['id_poz']; ?>&amp;zakladka=1"><b>Utwórz przesyłkę Orlen Paczka</b><img src="obrazki/logo/logo_paczkaruch_min.png"  alt="Utwórz przesyłkę Orlen Paczka" /></a>
                  </div>
                  <?php } ?>

                  <?php if ( isset($parametr_kurierzy['INTEGRACJA_APACZKAV2_WLACZONY']['0']) && $parametr_kurierzy['INTEGRACJA_APACZKAV2_WLACZONY']['0'] == 'tak' ) { ?>
                  <div>
                      <a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_apaczka.php?id_poz=<?php echo (int)$_GET['id_poz']; ?>&amp;zakladka=1"><b>Utwórz przesyłkę w serwisie apaczka</b><img src="obrazki/logo/logo_apaczka_min.png"  alt="Utwórz przesyłkę w serwisie apaczka" /></a>
                  </div>
                  <?php } ?>

                  <?php if ( isset($parametr_kurierzy['INTEGRACJA_GEIS_WLACZONY']['0']) && $parametr_kurierzy['INTEGRACJA_GEIS_WLACZONY']['0'] == 'tak' && strtolower($zamowienie->dostawa['kraj']) == 'polska' ) { ?>
                  <div>
                      <a class="TipChmurka" href="sprzedaz/zamowienia_wysylka_geis.php?id_poz=<?php echo (int)$_GET['id_poz']; ?>&amp;zakladka=1"><b>Utwórz przesyłkę w serwisie GEIS</b><img src="obrazki/logo/logo_geis_min.png"  alt="Utwórz przesyłkę w serwisie GEIS" /></a>
                  </div>
                  <?php } ?>

              </div>   

              <div class="cl"></div>
              
            </div>
            
        </div>
        
        <div style="margin-top:20px;">
        
            <div class="ObramowanieTabeli" style="display:inline-block">
            
              <table class="listing_tbl">
              
                <tr class="div_naglowek">
                  <td>Dodaj ręcznie nr przesyłki</td>
                </tr>
                
              </table>
              
              <script>
              $(document).ready(function() {
                $("#wysylkaForm").validate({
                  rules: {
                    nr_przesylki: {
                      required: true
                    }                
                  },
                  messages: {
                    nr_przesylki: {
                      required: "Pole jest wymagane."
                    }               
                  }
                });
              });          
              </script>                 
        
              <form action="sprzedaz/zamowienia_szczegoly.php" method="post" id="wysylkaForm" class="cmxform"> 
              
                  <input type="hidden" name="akcja" value="zapisz_wysylke" />
                  <input type="hidden" name="id" value="<?php echo (int)$_GET['id_poz']; ?>" />
              
                  <p>
                    <label for="nr_przesylki">Nr przesyłki:</label>
                    <input type="text" name="nr_przesylki" id="nr_przesylki" value="" size="50" />
                  </p>     

                  <p>
                    <label for="firma_wysylkowa">Firma wysyłkowa:</label>
                    <?php
                    $zapytanie_tmp = "select * from delivery_company dc, delivery_company_description dcd where dc.delivery_company_id = dcd.delivery_company_id and dcd.language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'";
                    $sqls = $db->open_query($zapytanie_tmp);
                    //
                    $tablica = array();
                    while ($infs = $sqls->fetch_assoc()) { 
                        $tablica[] = array('id' => $infs['delivery_company_id'], 'text' => $infs['delivery_company_name']);
                    }
                    $db->close_query($sqls); 
                    unset($zapytanie_tmp, $infs);    
                    //                          
                    echo Funkcje::RozwijaneMenu('firma_wysylkowa', $tablica, '', ' id="firma_wysylkowa"'); 
                    unset($tablica);
                    ?>
                  </p>  

                  <div class="przyciski_dolne">
                      <input type="submit" class="przyciskNon" value="Zapisz dane" />
                  </div>

              </form>
              
            </div>
        
        </div>

    </div>
    
<?php
}
?>    