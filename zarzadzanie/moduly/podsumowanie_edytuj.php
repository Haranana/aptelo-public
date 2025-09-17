<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

        if ( isset($_POST['PARAMETRY']) && $filtr->process($_POST['klasa']) == 'ot_loyalty_discount' ) {
            if (!isset($_POST['PARAMETRY']['STALI_KLIENCI_GRUPA_KLIENTOW']))
              $_POST['PARAMETRY']['STALI_KLIENCI_GRUPA_KLIENTOW'] = array();
        }
        
        if ( isset($_POST['PARAMETRY']) ) {
            if (!isset($_POST['PARAMETRY']['ZNIZKI_KOSZYKA_GRUPA_KLIENTOW']))
              $_POST['PARAMETRY']['ZNIZKI_KOSZYKA_GRUPA_KLIENTOW'] = array();
        }        

        //
        // aktualizacja zapisu w tablicy modulow
        $pola = array(
                array('nazwa',$filtr->process($_POST["nazwa"])),
                array('sortowanie',$filtr->process($_POST["sort"])),
                array('status',$filtr->process($_POST["status"])),
                array('prefix',$filtr->process($_POST["prefix"]))
        );
        //
        $pola[] = array('skrypt',$filtr->process($_POST["skrypt"]));
        $pola[] = array('klasa',$filtr->process($_POST["klasa"]));

        $db->update_query('modules_total' , $pola, " id = '".(int)$_POST["id"]."'");	
        unset($pola);
        
        // aktualizacja tlumaczen
        $db->delete_query('translate_constant', "translate_constant='".strtoupper((string)$filtr->process($_POST['klasa']))."_TYTUL'");
        $pola = array(
                array('translate_constant',strtoupper((string)$filtr->process($_POST['klasa'])).'_TYTUL'),
                array('section_id', '3')
                );
        $db->insert_query('translate_constant' , $pola);
        $id_dodanego_wyrazenia = $db->last_id_query();
        unset($pola);

        $db->delete_query('translate_value', "translate_constant_id = '".(int)$_POST["id_tlumaczenia"]."'");

        $ile_jezykow = Funkcje::TablicaJezykow();
        for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
            //
            if (!empty($_POST['nazwa_'.$w])) {
                $pola = array(
                        array('translate_value',$filtr->process($_POST['nazwa_'.$w])),
                        array('translate_constant_id',(int)$id_dodanego_wyrazenia),
                        array('language_id',$ile_jezykow[$w]['id'])
                 );
            } else {
                $pola = array(
                        array('translate_value',$filtr->process($_POST['nazwa_0'])),
                        array('translate_constant_id',(int)$id_dodanego_wyrazenia),
                        array('language_id',$ile_jezykow[$w]['id'])
                 );
            }
            $sql = $db->insert_query('translate_value' , $pola);
            unset($pola);
        }        

        if ( isset($_POST['PARAMETRY']) && count($_POST['PARAMETRY']) > 0 ) {
        
            reset($_POST['PARAMETRY']);
            foreach ( $_POST['PARAMETRY'] as $key => $value ) {
            
              if (is_array($value)) $value = implode(";", (array)$value);$value = str_replace("\r\n",", ", (string)$value);
              
              if ( $key == 'ZNIZKI_KOSZYKA_GRUPA_KLIENTOW' ) {
                  $pola = array(
                          array('wartosc',$filtr->process($value))
                  );
              } else {
                  $pola = array(
                          array('wartosc',($value != '0' ? $filtr->process($value) : ''))
                  );
              }              
              $db->update_query('modules_total_params' , $pola, " modul_id = '".(int)$_POST["id"]."' AND kod = '".$key."'");	
              unset($pola);
              
            }
            
        }

        if ( isset($_POST['parametry_prog_przedzial']) && isset($_POST['parametry_prog_wartosc']) ) {
        
            $progi_znizek = array_map("Moduly::PolaczWartosciTablic", $_POST['parametry_prog_przedzial'], $_POST['parametry_prog_wartosc']);
            $pola = array(
                    array('wartosc',implode(";", (array)$progi_znizek))
            );
            
            if ( $filtr->process($_POST["klasa"]) == 'ot_loyalty_discount' ) {
                 $db->update_query('modules_total_params' , $pola, " modul_id = '".(int)$_POST["id"]."' AND kod = 'STALI_KLIENCI_PROGI_ZNIZEK'");	
            }
            if ( $filtr->process($_POST["klasa"]) == 'ot_shopping_discount' ) {
                 $db->update_query('modules_total_params' , $pola, " modul_id = '".(int)$_POST["id"]."' AND kod = 'ZNIZKI_KOSZYKA_PROGI_ZNIZEK'");	
            }            
            unset($pola);
            
        }

        //
       Funkcje::PrzekierowanieURL('podsumowanie.php?id_poz='.(int)$_POST["id"]);
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Edycja pozycji</div>
    <div id="cont">
          
          <script type="text/javascript" src="javascript/jquery.bestupper.min.js"></script>        
          <script type="text/javascript" src="javascript/jquery.multi-select.js"></script>
          <script type="text/javascript" src="javascript/jquery.application.js"></script>
          <script type="text/javascript" src="moduly/moduly.js"></script>

          <script>
          $(document).ready(function() {
             $('.bestupper').bestupper();

            $("#modulyForm").validate({
              rules: {
                nazwa_0: {
                  required: true
                },
                skrypt: {
                  required: true
                },
                klasa: {
                  required: true
                },
                sort: {
                  required: true
                }
              },
              messages: {
                nazwa_0: {
                  required: "Pole jest wymagane."
                }               
              }
            });
          });
          </script>        

          <form action="moduly/podsumowanie_edytuj.php" method="post" id="modulyForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Edycja danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }

            $zapytanie = "SELECT * FROM modules_total WHERE id = '" . (int)$_GET['id_poz'] . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
            
                $info = $sql->fetch_assoc();
                ?>
            
                <div class="pozycja_edytowana">

                    <input type="hidden" name="akcja" value="zapisz" />
                
                    <input type="hidden" name="id" value="<?php echo (int)$_GET['id_poz']; ?>" />
                    
                    <p>
                      <label class="required" for="nazwa">Nazwa modułu:</label>
                      <input type="text" name="nazwa" size="73" value="<?php echo $info['nazwa']; ?>" id="nazwa" /><em class="TipIkona"><b>Robocza nazwa widoczna w panelu administracyjnym sklepu</b></em>
                    </p>

                <?php $ile_jezykow = Funkcje::TablicaJezykow(); ?>
                
                <div class="info_tab">
                
                <?php
                for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                    echo '<span id="link_'.$w.'" class="a_href_info_tab" onclick="gold_tabs(\''.$w.'\')">'.$ile_jezykow[$w]['text'].'</span>';
                }                    
                ?>     
                
                </div>
                
                <div style="clear:both"></div>
                
                <div class="info_tab_content">
                
                  <?php
                  $tlumaczenie = strtoupper((string)$info['klasa']) . '_TYTUL';

                  for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                        
                    // pobieranie danych jezykowych
                    $zapytanie_jezyk = "SELECT DISTINCT * FROM translate_constant w LEFT JOIN translate_value t ON w.translate_constant_id = t.translate_constant_id  WHERE translate_constant = '".$tlumaczenie."' and language_id = '" .$ile_jezykow[$w]['id']."'";
                    $sqls = $db->open_query($zapytanie_jezyk);
                    $nazwa = $sqls->fetch_assoc();   
                    if ( count((array)$nazwa) > 0 ) {
                        $id_tlumaczenia = $nazwa['translate_constant_id'];
                    }
                    ?>
                    <div id="info_tab_id_<?php echo $w; ?>" style="display:none;">
                    
                        <p>
                           <?php if ($w == '0') { ?>
                            <label class="required" for="nazwa_0">Treść wyświetlana w sklepie:</label>
                            <textarea cols="80" rows="3" name="nazwa_<?php echo $w; ?>" id="nazwa_0"><?php echo (isset($nazwa['translate_value']) ? $nazwa['translate_value'] : ''); ?></textarea>
                           <?php } else { ?>
                            <label for="nazwa_<?php echo $w; ?>">Treść wyświetlana w sklepie:</label>
                            <textarea cols="80" rows="3" name="nazwa_<?php echo $w; ?>" id="nazwa_<?php echo $w; ?>"><?php echo (isset($nazwa['translate_value']) ? $nazwa['translate_value'] : ''); ?></textarea>
                           <?php } ?>
                        </p> 
                                    
                    </div>
                    <?php
                    $db->close_query($sqls);
                    unset($zapytanie_jezyk);
                    
                   }
                   ?>
                   
                </div>
                
                <input type="hidden" name="id_tlumaczenia" value="<?php echo (isset($id_tlumaczenia) ? $id_tlumaczenia : ''); ?>" />

                <p>
                  <label class="required" for="sort">Kolejność wyswietlania:</label>
                  <input type="text" name="sort" size="5" value="<?php echo $info['sortowanie']; ?>" id="sort" class="bestupper" /><em class="TipIkona"><b>Kolejność wyswietlania określa jednocześnie w jakiej kolejności dany moduł będzie liczony do podsumowania</b></em>
                </p>
                
                <?php if ( $info['klasa'] != 'ot_total' && $info['klasa'] != 'ot_subtotal' ) { ?>
                
                  <p>
                    <label>Status:</label>
                    <input type="radio" value="1" name="status" id="status_tak" <?php echo (($info['status'] == '1') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="status_tak">włączony<em class="TipIkona"><b>Czy moduł ma być wliczany do wartości zamówienia</b></em></label>
                    <input type="radio" value="0" name="status" id="status_nie" <?php echo (($info['status'] == '0') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="status_nie">wyłączony<em class="TipIkona"><b>Czy moduł ma być wliczany do wartości zamówienia</b></em></label>        
                  </p>
                  
                <?php } else { ?>
                
                  <input type="hidden" name="status" id="status" value="1" />
                  
                <?php } ?>
                
                <p>
                  <label>Wartość zamówienia:</label>  
                  
                  <?php if ( $info['klasa'] != 'ot_total' ) { ?>
                  <input type="radio" value="1" name="prefix" id="prefix_plus" <?php echo (($info['prefix'] == '1') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="prefix_plus">zwiększa<em class="TipIkona"><b>Czy moduł ma być dodawany przy wyliczaniu wartości zamówienia</b></em></label>
                  <?php } ?>
                  
                  <?php if ( $info['klasa'] != 'ot_package' && $info['klasa'] != 'ot_total' ) { ?>
                        <input type="radio" value="0" name="prefix" id="prefix_minus" <?php echo (($info['prefix'] == '0') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="prefix_minus">zmniejsza<em class="TipIkona"><b>Czy moduł ma być odejmowany przy wyliczaniu wartości zamówienia</b></em></label>
                  <?php } ?>
            
                  <?php if ( $info['klasa'] != 'ot_package' ) { ?>
                  <input type="radio" value="9" name="prefix" id="prefix_brak" <?php echo (($info['prefix'] == '9') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="prefix_brak">brak<em class="TipIkona"><b>Czy moduł mie ma wpływu na wyliczanie wartości zamówienia</b></em></label>
                  <?php } ?>
                  
                </p>                     

                <?php

                $zapytanie_parametry = "SELECT * FROM modules_total_params WHERE modul_id = '" . (int)$_GET['id_poz'] . "' ORDER BY sortowanie";
                $sql_parametry = $db->open_query($zapytanie_parametry);
                
                $nrPola = 1;
                            
                if ((int)$db->ile_rekordow($sql_parametry) > 0) {
                
                  while ( $info_parametry = $sql_parametry->fetch_assoc() ) {

                    if ( $info_parametry['kod'] == 'STALI_KLIENCI_OKRES_NALICZANIA_ZAMOWIEN' ) {
                    
                      echo '<p>' . "\n";
                      echo '<label>'.$info_parametry['nazwa'].':</label>' . "\n";
                      echo '<input type="radio" value="3" id="pole_' . $nrPola . '_1" name="PARAMETRY['.$info_parametry['kod'].']" '.(($info_parametry['wartosc'] == '3') ? 'checked="checked"' : '').' /><label class="OpisFor" for="pole_' . $nrPola . '_1">kwartalnie<em class="TipIkona"><b>Zliczane są zamówienia z ostatniego kwartału</b></em></label>' . "\n";
                      echo '<input type="radio" value="4" id="pole_' . $nrPola . '_4" name="PARAMETRY['.$info_parametry['kod'].']" '.(($info_parametry['wartosc'] == '4') ? 'checked="checked"' : '').' /><label class="OpisFor" for="pole_' . $nrPola . '_4">pół roku<em class="TipIkona"><b>Zliczane są zamówienia z ostatniego półrocza</b></em></label>' . "\n";
                      echo '<input type="radio" value="1" id="pole_' . $nrPola . '_2" name="PARAMETRY['.$info_parametry['kod'].']" '.(($info_parametry['wartosc'] == '1') ? 'checked="checked"' : '').' /><label class="OpisFor" for="pole_' . $nrPola . '_2">rocznie<em class="TipIkona"><b>Zliczane są zamówienia z ostatniego roku</b></em></label>' . "\n";                      
                      echo '<input type="radio" value="99" id="pole_' . $nrPola . '_3" name="PARAMETRY['.$info_parametry['kod'].']" '.(($info_parametry['wartosc'] == '99') ? 'checked="checked"' : '').' /><label class="OpisFor" for="pole_' . $nrPola . '_3">wszystkie<em class="TipIkona"><b>Zliczane są wszystkie zamówienia klienta</b></em></label>' . "\n";
                      echo '</p>' . "\n";
                      
                      $nrPola++;
                      
                    }
                    
                    if ( $info_parametry['kod'] == 'STALI_KLIENCI_STATUS_ZAMOWIEN' ) {
                    
                      echo '<p>' . "\n";
                      echo '<label for="'.$info_parametry['kod'].'">'.$info_parametry['nazwa'].':</label>' . "\n";
                      $tablica_statusow = Sprzedaz::ListaStatusowZamowien( false );
                      echo Funkcje::RozwijaneMenu('PARAMETRY['.$info_parametry['kod'].']', $tablica_statusow, $info_parametry['wartosc'], ' id="'.$info_parametry['kod'].'"') . "\n";
                      echo '<em class="TipIkona"><b>Status zamówień, ktróre będą uwzględniane do wartości dokonanych dotychczas zakupów</b></em>' . "\n";
                      echo '</p>' . "\n";
                      
                    }
                    
                    if ( $info_parametry['kod'] == 'ZNIZKI_KOSZYKA_PROMOCJE' || $info_parametry['kod'] == 'STALI_KLIENCI_PROMOCJE' ) {
                    
                      echo '<p>' . "\n";
                      echo '<label>'.$info_parametry['nazwa'].'</label>' . "\n";
                      echo '<input type="radio" value="tak" id="pole_' . $nrPola . '_tak" name="PARAMETRY['.$info_parametry['kod'].']" '.(($info_parametry['wartosc'] == 'tak') ? 'checked="checked"' : '').' /><label class="OpisFor" for="pole_' . $nrPola . '_tak">tak</label>' . "\n";
                      echo '<input type="radio" value="nie" id="pole_' . $nrPola . '_nie" name="PARAMETRY['.$info_parametry['kod'].']" '.(($info_parametry['wartosc'] == 'nie') ? 'checked="checked"' : '').' /><label class="OpisFor" for="pole_' . $nrPola . '_nie">nie</label>' . "\n";
                      echo '</p>' . "\n";
                      
                      $nrPola++;
                      
                    }    

                    if ( $info_parametry['kod'] == 'ZNIZKI_KOSZYKA_KUPON' || $info_parametry['kod'] == 'STALI_KLIENCI_KUPON' ) {
                    
                      echo '<p>' . "\n";
                      echo '<label>'.$info_parametry['nazwa'].'</label>' . "\n";
                      echo '<input type="radio" value="tak" id="pole_' . $nrPola . '_tak" name="PARAMETRY['.$info_parametry['kod'].']" '.(($info_parametry['wartosc'] == 'tak') ? 'checked="checked"' : '').' /><label class="OpisFor" for="pole_' . $nrPola . '_tak">tak</label>' . "\n";
                      echo '<input type="radio" value="nie" id="pole_' . $nrPola . '_nie" name="PARAMETRY['.$info_parametry['kod'].']" '.(($info_parametry['wartosc'] == 'nie') ? 'checked="checked"' : '').' /><label class="OpisFor" for="pole_' . $nrPola . '_nie">nie</label>' . "\n";
                      echo '</p>' . "\n";
                      
                      $nrPola++;
                      
                    }
                    
                    if ( $info_parametry['kod'] == 'ZNIZKI_KOSZYKA_SPOSOB' ) {
                    
                      echo '<p>' . "\n";
                      echo '<label>'.$info_parametry['nazwa'].'</label>' . "\n";
                      echo '<input type="radio" value="kwota" id="pole_' . $nrPola . '_wartosc" name="PARAMETRY['.$info_parametry['kod'].']" '.(($info_parametry['wartosc'] == 'kwota') ? 'checked="checked"' : '').' /><label class="OpisFor" for="pole_' . $nrPola . '_wartosc">wartość produktów<em class="TipIkona"><b>Zniżka zależna od wartości produktów w koszyku</b></em></label>' . "\n";
                      echo '<input type="radio" value="ilosc" id="pole_' . $nrPola . '_ilosc" name="PARAMETRY['.$info_parametry['kod'].']" '.(($info_parametry['wartosc'] == 'ilosc') ? 'checked="checked"' : '').' /><label class="OpisFor" for="pole_' . $nrPola . '_ilosc">ilość produktów<em class="TipIkona"><b>Zniżka zależna od ilości produktów w koszyku</b></em></label>' . "\n";
                      echo '</p>' . "\n";
                      
                      $nrPola++;
                      
                    }                        
                    
                    if ( $info_parametry['kod'] == 'STALI_KLIENCI_PROGI_ZNIZEK' || $info_parametry['kod'] == 'ZNIZKI_KOSZYKA_PROGI_ZNIZEK' ) {
                    
                      $tablica_progow = explode(';', (string)$info_parametry['wartosc']);

                      echo '<p>' . "\n";
                      echo '<label>'.$info_parametry['nazwa'].':</label>' . "\n";
                      echo '</p>' . "\n";

                      echo '<div id="progiPrzedzial" style="margin-top:-25px;margin-bottom:10px;" >' . "\n";
                      
                      $symbol = $_SESSION['domyslna_waluta']['symbol'];
                      if ( $info_parametry['kod'] == 'ZNIZKI_KOSZYKA_PROGI_ZNIZEK' ) {
                          $symbol .= ' / szt.';
                      }
                      
                      for ( $i = 0, $c = count($tablica_progow); $i < $c; $i++ ) {
                        $idDiv = $i+1;
                        $koszt = explode(':', (string)$tablica_progow[$i]);
                        echo '<div class="odlegloscRwdTab" style="padding-bottom:6px" id="prog'.$idDiv.'">powyżej &nbsp; <input class="kropka" type="text" size="10" name="parametry_prog_przedzial[]" value="' . number_format((float)$koszt['0'],2,'.','') . '" /> ' . $symbol . ' &nbsp; ';
                        echo '<input class="kropka" type="text" name="parametry_prog_wartosc[]" value="' . number_format((float)$koszt['1'],2,'.','') . '" /> %</div>' . "\n";
                      }
                      
                      echo '<div class="odlegloscRwdTab" style="padding-top:10px">' . "\n";
                      echo '<span class="dodaj" onclick="dodaj_pozycje(\'progiPrzedzial\',\'prog\', \'' . $symbol . '\', \'powyżej\', \'%\')" style="cursor:pointer">dodaj pozycję</span> &nbsp; &nbsp; <span class="usun" onclick="usun_pozycje(\'progiPrzedzial\',\'prog\')" style="cursor:pointer; '.(count($tablica_progow) > 1 ? '' : 'display:none;').'">usuń pozycję</span>' . "\n";
                      echo '</div>' . "\n";
                      
                      echo '</div>' . "\n";
                      
                      unset($symbol);
                      
                    }
                    
                    if ( $info_parametry['kod'] == 'STALI_KLIENCI_GRUPA_KLIENTOW'  || $info_parametry['kod'] == 'ZNIZKI_KOSZYKA_GRUPA_KLIENTOW' ) {
                    
                      $tablica_grup = explode(';', (string)$info_parametry['wartosc']);
                      
                      if ( $info_parametry['kod'] == 'ZNIZKI_KOSZYKA_GRUPA_KLIENTOW' ) {
                           $tablica_tmp = Klienci::ListaGrupKlientow(true, 'Klienci bez rejestracji konta' );
                      } else {
                           $tablica_tmp = Klienci::ListaGrupKlientow(false);
                      }
                      
                      echo '<p>' . "\n";
                      echo '<label>'.$info_parametry['nazwa'].':</label>' . "\n";
                      echo '<select name="PARAMETRY['.$info_parametry['kod'].'][]" multiple="multiple" id="multipleHeaders">' . "\n";
                      foreach ( $tablica_tmp as $rekord ) {
                        $wybrany = '';
                        if ( in_array((string)$rekord['id'], $tablica_grup ) ) {
                          $wybrany = 'selected="selected"';
                        }
                        echo '<option value="'.$rekord['id'].'" '.$wybrany.'>'.$rekord['text'].'</option>' . "\n";
                      }
                      echo '</select>' . "\n";
                      echo '</p>' . "\n";
                      
                      echo '<div class="ostrzezenie odlegloscRwdTab BrakMarginesuRwd" style="margin-top:10px; margin-bottom:10px">Jeżeli nie zostanie wybrana żadna grupa klientów to moduł będzie aktywny dla wszystkich klientów.</div>' . "\n";
                      
                      unset($tablica_grup, $wybrany);  
                        
                    }
                    
                    if ( $info_parametry['kod'] == 'OPAKOWANIE_OZDOBNE_KOSZT' ) {
                    
                      echo '<p>' . "\n";
                      echo '<label for="pole_' . $nrPola . '_wartosc">'.$info_parametry['nazwa'].':</label>' . "\n";
                      echo '<input type="text" class="kropka" value="'.number_format((float)$info_parametry['wartosc'], 2, '.', '').'" id="pole_' . $nrPola . '_wartosc" name="PARAMETRY['.$info_parametry['kod'].']" /><em class="TipIkona"><b>Wartość opakowania ozdobnego</b></em></label>' . "\n";
                      echo '</p>' . "\n";
                      
                    } 

                    if ( $info_parametry['kod'] == 'OPAKOWANIE_OZDOBNE_STAWKA_VAT' ) {
                    
                      echo '<p>' . "\n";
                      echo '<label for="vat">Podatek VAT:</label>' . "\n";
                      
                      $vat = Produkty::TablicaStawekVat('', true, true);
                      $domyslny_vat = $vat[1];
                      $podzial_vat = explode('|', (string)$info_parametry['wartosc']);
                      
                      if (count($podzial_vat) == 2) {
                          //
                          $domyslny_vat = $info_parametry['wartosc'];
                          //
                      }            

                      echo Funkcje::RozwijaneMenu('PARAMETRY['.$info_parametry['kod'].']', $vat[0], $domyslny_vat, 'id="vat"') . "\n";                          
                      echo '</p>' . "\n";
                      
                    }         

                    if ( strpos($info_parametry['kod'],'_TEXTAREA') > -1 ) {
                    
                      echo '<p>' . "\n";
                      echo '<label for="pole_' . $nrPola . '_wartosc">'.$info_parametry['nazwa'].':</label>' . "\n";
                      echo '<textarea cols="50" rows="5" id="pole_' . $nrPola . '_wartosc" name="PARAMETRY['.$info_parametry['kod'].']">' . $info_parametry['wartosc'] . '</textarea>' . "\n";
                      echo '</p>' . "\n";
                      
                      $nrPola++;
                      
                    }   

                    if ( strpos($info_parametry['kod'],'_INPUT') > -1 ) {
                    
                      echo '<p>' . "\n";
                      echo '<label for="pole_' . $nrPola . '_wartosc">'.$info_parametry['nazwa'].':</label>' . "\n";
                      echo '<input size="30" id="pole_' . $nrPola . '_wartosc" name="PARAMETRY['.$info_parametry['kod'].']" value="' . $info_parametry['wartosc'] . '" />' . "\n";
                      echo '</p>' . "\n";
                      
                      $nrPola++;
                      
                    }     

                    if ( strpos($info_parametry['kod'],'_INPUT_CALKOWITA') > -1 ) {
                    
                      echo '<p>' . "\n";
                      echo '<label for="pole_' . $nrPola . '_wartosc">'.$info_parametry['nazwa'].':</label>' . "\n";
                      echo '<input class="calkowita" size="30" id="pole_' . $nrPola . '_wartosc" name="PARAMETRY['.$info_parametry['kod'].']" value="' . $info_parametry['wartosc'] . '" />' . "\n";
                      echo '</p>' . "\n";
                      
                      $nrPola++;
                      
                    }       

                  }
                  
                  $nrPola++;

                }

                $db->close_query($sql_parametry);
                unset($zapytanie_parametry, $info_parametry, $nrPola);

                if ( isset($_SESSION['programista']) && $_SESSION['programista'] == '1' ) { 
                  ?>

                  <hr style="color:#82b4cd;border-top: 1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:96%;" />
                  
                  <p>
                      <label class="required">Skrypt:</label>   
                      <input type="text" name="skrypt" id="skrypt" size="53" value="<?php echo $info['skrypt']; ?>" onkeyup="updateKeySkrypt();" <?php echo ( isset($_SESSION['programista']) && $_SESSION['programista'] == '1' ? '' : 'readonly="readonly"' ); ?> /><em class="TipIkona"><b>Nazwa skryptu realizującego funkcje modułu</b></em>
                  </p>

                  <p>
                      <label class="required">Nazwa klasy:</label>   
                      <input type="text" name="klasa" id="klasa" size="53" value="<?php echo $info['klasa']; ?>" onkeyup="updateKeyKlasa();" <?php echo ( isset($_SESSION['programista']) && $_SESSION['programista'] == '1' ? '' : 'readonly="readonly"' ); ?> /><em class="TipIkona"><b>Nazwa klasy realizującej funkcje modułu</b></em>
                  </p>

                <?php } else { ?>
                
                  <input type="hidden" name="skrypt" id="skrypt" value="<?php echo $info['skrypt']; ?>" />
                  <input type="hidden" name="klasa" id="klasa" value="<?php echo $info['klasa']; ?>" />
                  
                <?php } ?>

                <script>
                gold_tabs('0');
                </script>  
                    
                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Zapisz dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('podsumowanie','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','moduly');">Powrót</button>           
                </div>                 

            <?php
            
            $db->close_query($sql);
            unset($info);            
            
            } else {
            
                echo '<div class="pozycja_edytowana">Brak danych do wyświetlenia</div>';
            
            }
            ?>

          </div>                      
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}
?>