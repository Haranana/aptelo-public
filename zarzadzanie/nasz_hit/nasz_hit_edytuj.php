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
        $pola[] = array('star_status','1');
        if (!empty($_POST['data_nasz_hit_od'])) {
            $pola[] = array('star_date',date('Y-m-d', FunkcjeWlasnePHP::my_strtotime($filtr->process($_POST['data_nasz_hit_od']))));
          } else {
            $pola[] = array('star_date','0000-00-00');            
        }
        if (!empty($_POST['data_nasz_hit_do'])) {
            $pola[] = array('star_date_end',date('Y-m-d', FunkcjeWlasnePHP::my_strtotime($filtr->process($_POST['data_nasz_hit_do']))));
          } else {
            $pola[] = array('star_date_end','0000-00-00');            
        }
        //	
        $sql = $db->update_query('products', $pola, 'products_id = ' . (int)$id_edytowanej_pozycji);
        
        unset($pola);
        
        Funkcje::PrzekierowanieURL('nasz_hit.php?id_poz='.(int)$id_edytowanej_pozycji);
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>

    <div id="naglowek_cont">Edycja pozycji</div>
    <div id="cont">

          <form action="nasz_hit/nasz_hit_edytuj.php" method="post" id="poForm" class="cmxform"> 
          
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
                    
                    <input type="hidden" id="rodzaj_modulu" value="nasz_hit" />
                    
                    <input type="hidden" name="id_produkt" value="<?php echo (int)$info['products_id']; ?>" />
                    
                    <div class="info_content">

                    <script>
                    $(document).ready(function() {
                    
                        $('input.datepicker').Zebra_DatePicker({
                           format: 'd-m-Y',
                           inside: false,
                           readonly_element: true
                        });                
                    
                    });
                    </script>  

                    <p>
                        <label for="data_nasz_hit_od">Data rozpoczęcia:</label>
                        <input type="text" id="data_nasz_hit_od" name="data_nasz_hit_od" value="<?php echo ((Funkcje::czyNiePuste($info['star_date'])) ? date('d-m-Y',FunkcjeWlasnePHP::my_strtotime($info['star_date'])) : ''); ?>" size="20"  class="datepicker" />                                 
                    </p>
                    
                    <p>
                        <label for="data_nasz_hit_do">Data zakończenia:</label>
                        <input type="text" id="data_nasz_hit_do" name="data_nasz_hit_do" value="<?php echo ((Funkcje::czyNiePuste($info['star_date_end'])) ? date('d-m-Y',FunkcjeWlasnePHP::my_strtotime($info['star_date_end'])) : ''); ?>" size="20" class="datepicker" />                                    
                    </p> 
                    
                    </div>
                    
                </div>
                
                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Zapisz dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('nasz_hit','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>');">Powrót</button>     
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