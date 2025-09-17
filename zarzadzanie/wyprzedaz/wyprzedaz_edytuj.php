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
        
        // ceny dla pozostalych poziomow cen
        for ($x = 2; $x <= ILOSC_CEN; $x++) {
          
            // cena poprzednia
            if ( (isset($_POST['cena_poprzednia_'.$x]) && (float)$_POST['cena_poprzednia_'.$x] > 0) ) {
                //
                $pola[] = array('products_old_price_'.$x,(float)$_POST['cena_poprzednia_'.$x]);             
                //
              } else {
                //
                $pola[] = array('products_old_price_'.$x,'0');
                //
            }
            //
        }            

        $sql = $db->update_query('products', $pola, 'products_id = ' . (int)$id_edytowanej_pozycji);
        
        unset($pola, $tablicaVat);
        
        Funkcje::PrzekierowanieURL('wyprzedaz.php?id_poz='.(int)$id_edytowanej_pozycji);
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>

    <div id="naglowek_cont">Edycja pozycji</div>
    <div id="cont">

          <form action="wyprzedaz/wyprzedaz_edytuj.php" method="post" id="poForm" class="cmxform"> 
          
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
                    
                    <input type="hidden" id="rodzaj_modulu" value="wyprzedaz" />
                    
                    <input type="hidden" name="id_produkt" value="<?php echo $info['products_id']; ?>" />
                    
                    <div class="info_content">

                    <p>
                      <label for="cena_poprzednia">Cena poprzednia:</label>
                      <input type="text" name="cena_poprzednia" class="kropka" id="cena_poprzednia" value="<?php echo ((Funkcje::czyNiePuste($info['products_old_price'])) ? $info['products_old_price'] : ''); ?>" size="20" />
                      <em class="TipIkona"><b>Cena będzie wyświetlana jako przekreślona</b></em>
                    </p> 

                    <?php for ($x = 2; $x <= ILOSC_CEN; $x++) { ?>     

                    <hr style="color:#82b4cd;border-top: 1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:98%;" />
                    
                    <p>
                      <label for="cena_poprzednia_<?php echo $x; ?>">Cena poprzednia dla ceny nr <?php echo $x; ?>:</label>
                      <input type="text" name="cena_poprzednia_<?php echo $x; ?>" class="kropka" id="cena_poprzednia_<?php echo $x; ?>" value="<?php echo ((Funkcje::czyNiePuste($info['products_old_price_'.$x])) ? $info['products_old_price_'.$x] : ''); ?>" size="20" />
                      <em class="TipIkona"><b>Cena będzie wyświetlana jako przekreślona</b></em>                      
                    </p> 

                    <?php } ?>
                    
                    <?php if ( $info['options_type'] == 'ceny' ) { ?>
                    
                    <div class="InformacjaCenyCechy">
                        
                        <b>Ten produkt posiada przypisane na stałe ceny do kombinacji cech !!</b> <br /> W celu wprowadzenia cen dla poszczególnych kombinacji cech (cen produktu oraz ceny poprzedniej) należy przejść do edycji produktu <br />
                        
                        <a class="przyciskNon" style="display:inline-block; margin-left:0px;" href="/zarzadzanie/produkty/produkty_edytuj.php?id_poz=<?php echo $info['products_id']; ?>&zakladka=5">Przejdź do edycji produktu (zakładka Cechy produktu)</a>
                        
                    </div>

                    <?php } ?>
                   
                    </div>
                    
                </div>
                
                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Zapisz dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('wyprzedaz','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>');">Powrót</button>     
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