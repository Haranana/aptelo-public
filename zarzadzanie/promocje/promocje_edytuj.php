<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        $id_edytowanej_pozycji = (int)$_POST['id_produkt'];
        //
        $pola = array();
        $pola[] = array('products_old_price',(float)$_POST['cena_poprzednia']);
        
        // nowa cena produktu
        
        // pobieranie informacji o vat - tworzy tablice ze stawkami
        $zapytanie_vat = "select distinct * from tax_rates order by tax_rate desc";
        $sqls = $db->open_query($zapytanie_vat);
        //
        $tablicaVat = array();
        while ($infs = $sqls->fetch_assoc()) { 
            $tablicaVat[$infs['tax_rates_id']] = $infs['tax_rate'];
        }
        $db->close_query($sqls);
        unset($zapytanie_vat, $infs);  
        //                             
        $wartosc = (float)$_POST['cena_brutto'];
        $netto = round(($wartosc / (1 + ($tablicaVat[(int)$_POST['stawka_vat']]/100))), 2);
        $podatek = $wartosc - $netto;
        //
        $pola[] = array('products_price_tax',$wartosc);
        $pola[] = array('products_price',$netto);
        $pola[] = array('products_tax',$podatek);
        //
        unset($wartosc, $netto, $podatek);       

        // ceny dla pozostalych poziomow cen
        for ($x = 2; $x <= ILOSC_CEN; $x++) {
            // cena poprzednia
            if ( (isset($_POST['cena_poprzednia_'.$x]) && (float)$_POST['cena_poprzednia_'.$x] > 0) && (isset($_POST['cena_brutto_'.$x]) && (float)$_POST['cena_brutto_'.$x] > 0) ) {
                //
                $pola[] = array('products_old_price_'.$x,(float)$_POST['cena_poprzednia_'.$x]);
                //
                $wartosc = (float)$_POST['cena_brutto_'.$x];
                $netto = round(($wartosc / (1 + ($tablicaVat[(int)$_POST['stawka_vat']]/100))), 2);
                $podatek = $wartosc - $netto;    
                //
                $pola[] = array('products_price_tax_'.$x,$wartosc);
                $pola[] = array('products_price_'.$x,$netto);
                $pola[] = array('products_tax_'.$x,$podatek);
                //    
                unset($wartosc, $netto, $podatek); 
                //                
                //
              } else {
                //
                $pola[] = array('products_old_price_'.$x,'0');
                //
            }
            //
        }            
        
        $pola[] = array('specials_status','1');
        
        if (!empty($_POST['data_promocja_od'])) {
            $pola[] = array('specials_date',date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_POST['data_promocja_od']))));
          } else {
            $pola[] = array('specials_date','0000-00-00');            
        }
        if (!empty($_POST['data_promocja_do'])) {
            $pola[] = array('specials_date_end',date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_POST['data_promocja_do']))));
          } else {
            $pola[] = array('specials_date_end','0000-00-00');
        }
        //	
        $sql = $db->update_query('products', $pola, 'products_id = ' . $id_edytowanej_pozycji);
        
        unset($pola, $tablicaVat);
        
        Funkcje::PrzekierowanieURL('promocje.php?id_poz='.$id_edytowanej_pozycji);
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>

    <div id="naglowek_cont">Edycja pozycji</div>
    <div id="cont">

          <form action="promocje/promocje_edytuj.php" method="post" id="poForm" class="cmxform"> 
          
          <div class="poleForm">
            <div class="naglowek">Edycja danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select distinct * from products where products_id = '".(int)$_GET['id_poz']."'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {

                $info = $sql->fetch_assoc();  
                ?>            
            
                <div class="pozycja_edytowana">    
                
                    <input type="hidden" name="akcja" value="zapisz" />
                    
                    <input type="hidden" id="rodzaj_modulu" value="promocje" />
                    
                    <input type="hidden" name="id_produkt" value="<?php echo $info['products_id']; ?>" />
                    
                    <div class="info_content">

                    <script>
                    $(document).ready(function() {
                    $("#poForm").validate({
                      rules: {
                        cena_poprzednia: {
                          required: true, range: [0.01, 1000000], number: true 
                        },
                        cena_brutto: {
                          required: true, range: [0.01, 1000000], number: true 
                        }           
                      },
                      messages: {
                        cena_poprzednia: {
                          required: "Pole jest wymagane.",
                          range: "Niepoprawna wartość ceny."
                        },
                        cena_brutto: {
                          required: "Pole jest wymagane.",
                          range: "Niepoprawna wartość ceny."
                        }                
                      }
                    });
                    
                    $('input.datepicker').Zebra_DatePicker({
                       format: 'd-m-Y H:i',
                       inside: false,
                       readonly_element: true,
                       enabled_minutes: [00, 10, 20, 30, 40, 50]
                    });                
                    
                    });
                    </script>  

                    <p>
                      <label class="required" for="cena_poprzednia">Cena poprzednia:</label>
                      <input type="text" name="cena_poprzednia" class="kropka" id="cena_poprzednia" value="<?php echo ((Funkcje::czyNiePuste($info['products_old_price'])) ? $info['products_old_price'] : ''); ?>" size="20" />
                      <em class="TipIkona"><b>Cena będzie wyświetlana jako przekreślona</b></em>
                    </p> 

                    <p>
                      <label class="required" for="cena_brutto">Nowa cena brutto:</label>
                      <input type="text" name="cena_brutto" class="kropka" id="cena_brutto" value="<?php echo ((Funkcje::czyNiePuste($info['products_price_tax'])) ? $info['products_price_tax'] : ''); ?>" size="20" /> 
                      <em class="TipIkona"><b>Nowa cena produktu brutto - cena zostanie zapisana tylko jeżeli zostanie wypełniona cena poprzednia</b></em>
                      <input type="hidden" name="stawka_vat" value="<?php echo $info['products_tax_class_id']; ?>" />
                    </p>     

                    <?php for ($x = 2; $x <= ILOSC_CEN; $x++) { ?>     

                    <hr style="color:#82b4cd;border-top: 1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:98%;" />
                    
                    <p>
                      <label for="cena_poprzednia_<?php echo $x; ?>">Cena poprzednia dla ceny nr <?php echo $x; ?>:</label>
                      <input type="text" name="cena_poprzednia_<?php echo $x; ?>" class="kropka" id="cena_poprzednia_<?php echo $x; ?>" value="<?php echo ((Funkcje::czyNiePuste($info['products_old_price_'.$x])) ? $info['products_old_price_'.$x] : ''); ?>" size="20" />
                      <em class="TipIkona"><b>Cena będzie wyświetlana jako przekreślona</b></em>                      
                    </p> 

                    <p>
                      <label for="cena_brutto_<?php echo $x; ?>">Nowa cena brutto nr <?php echo $x; ?>:</label>                      
                      <input type="text" name="cena_brutto_<?php echo $x; ?>" class="kropka" id="cena_brutto_<?php echo $x; ?>" value="<?php echo ((Funkcje::czyNiePuste($info['products_price_tax_'.$x])) ? $info['products_price_tax_'.$x] : ''); ?>" size="20" /> 
                      <em class="TipIkona"><b>Nowa cena produktu brutto - cena zostanie zapisana tylko jeżeli zostanie wypełniona cena poprzednia</b></em>
                    </p>

                    <?php } ?>
                    
                    <?php if ( $info['options_type'] == 'ceny' ) { ?>
                    
                    <div class="InformacjaCenyCechy">
                        
                        <b>Ten produkt posiada przypisane na stałe ceny do kombinacji cech !!</b> <br /> W celu wprowadzenia cen dla poszczególnych kombinacji cech (cen produktu oraz ceny poprzedniej) należy przejść do edycji produktu <br />
                        
                        <a class="przyciskNon" style="display:inline-block; margin-left:0px;" href="/zarzadzanie/produkty/produkty_edytuj.php?id_poz=<?php echo $info['products_id']; ?>&zakladka=5">Przejdź do edycji produktu (zakładka Cechy produktu)</a>
                        
                    </div>

                    <?php } ?>
                    
                    <hr style="color:#82b4cd;border-top: 1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:98%;" />
                
                    <p>
                        <label for="data_promocja_od">Data rozpoczęcia:</label>
                        <input type="text" id="data_promocja_od" name="data_promocja_od" value="<?php echo ((Funkcje::czyNiePuste($info['specials_date'])) ? date('d-m-Y H:i',FunkcjeWlasnePHP::my_strtotime($info['specials_date'])) : ''); ?>" size="20"  class="datepicker" />
                    </p>
                    
                    <p>
                        <label for="data_promocja_do">Data zakończenia:</label>
                        <input type="text" id="data_promocja_do" name="data_promocja_do" value="<?php echo ((Funkcje::czyNiePuste($info['specials_date_end'])) ? date('d-m-Y H:i',FunkcjeWlasnePHP::my_strtotime($info['specials_date_end'])) : ''); ?>" size="20" class="datepicker" />
                    </p>

                    </div>
                    
                </div>
                
                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Zapisz dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('promocje','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>');">Powrót</button>     
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
    
} ?>