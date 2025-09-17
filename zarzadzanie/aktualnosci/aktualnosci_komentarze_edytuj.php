<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        $id_edytowanej_pozycji = (int)$_POST['id'];
        //
        $pola = array(array('nick',$filtr->process($_POST['nick'])),
                      array('email',$filtr->process($_POST['email'])),
                      array('telefon',$filtr->process($_POST['telefon'])),
                      array('comments',$filtr->process(strip_tags((string)$_POST['komentarz']))),
                      array('date_added',date('Y-m-d H:i', FunkcjeWlasnePHP::my_strtotime($filtr->process($_POST['data_dodania'])))));
        //	
        $sql = $db->update_query('newsdesk_comments', $pola, 'newsdesk_comments_id = ' . (int)$id_edytowanej_pozycji);
        unset($pola);        
        
        Funkcje::PrzekierowanieURL('aktualnosci_komentarze.php?id_poz='.$id_edytowanej_pozycji.(((int)$_POST['art_id'] > 0) ? '&art_id='.(int)$_POST['art_id'] : ''));
    }   

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>

    <div id="naglowek_cont">Edycja pozycji</div>
    <div id="cont">

          <form action="aktualnosci/aktualnosci_komentarze_edytuj.php" method="post" id="aktualnosciForm" class="cmxform"> 
          
          <div class="poleForm">
            <div class="naglowek">Edycja danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select distinct * from newsdesk_comments where newsdesk_comments_id = '" . (int)$_GET['id_poz'] . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {

                $info = $sql->fetch_assoc();  
                ?>            
            
                <div class="pozycja_edytowana">    

                    <input type="hidden" name="akcja" value="zapisz" />

                    <input type="hidden" name="id" value="<?php echo $info['newsdesk_comments_id']; ?>" />
                    
                    <input type="hidden" name="art_id" value="<?php echo ((isset($_GET['art_id'])) ? (int)$_GET['art_id'] : ''); ?>" />
                    
                    <div class="info_content">

                    <script>
                    $(document).ready(function() {
                    
                        $("#aktualnosciForm").validate({
                          rules: {
                            nick: {
                              required: true
                            },
                            komentarz: {
                              required: true
                            },
                            data_dodania: {
                              required: true
                            }                            
                          },
                          messages: {
                            nick: {
                              required: "Pole jest wymagane."
                            },
                            komentarz: {
                              required: "Pole jest wymagane."
                            },
                            data_dodania: {
                              required: "Pole jest wymagane."
                            }                                 
                          }
                        });                

                        $('input.datepicker').Zebra_DatePicker({
                           format: 'd-m-Y H:i',
                           inside: false,
                           readonly_element: true,
                           show_clear_date: false
                        });                 
                    
                    });
                    </script>  
                    
                    <p>
                        <label class="required" for="wystawiajacy">Nick:</label>
                        <input type="text" name="nick" id="nick" value="<?php echo $info['nick']; ?>" size="30" />                                        
                    </p>      

                    <p>
                        <label for="wystawiajacy">Email:</label>
                        <input type="text" name="email" id="email" value="<?php echo $info['email']; ?>" size="30" />                                        
                    </p>  

                    <p>
                        <label for="wystawiajacy">Telefon:</label>
                        <input type="text" name="telefon" id="telefon" value="<?php echo $info['telefon']; ?>" size="30" />                                        
                    </p>                      
                
                    <p>
                        <label class="required" for="data_dodania">Data dodania:</label>
                        <input type="text" name="data_dodania" id="data_dodania" value="<?php echo ((Funkcje::czyNiePuste($info['date_added'])) ? date('d-m-Y H:i',FunkcjeWlasnePHP::my_strtotime($info['date_added'])) : ''); ?>" size="20" class="datepicker" />                                        
                    </p>

                    <p>
                        <label class="required" for="komentarz">Komentarz:</label>
                        <textarea name="komentarz" id="komentarz" rows="10" cols="50"><?php echo $info['comments']; ?></textarea><em class="TipIkona"><b>Komentarz - bez tagów HTML</b></em>
                    </p>

                    </div>
                    
                </div>
                
                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Zapisz dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('aktualnosci_komentarze','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz','art_id')); ?>','aktualnosci');">Powrót</button>     
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