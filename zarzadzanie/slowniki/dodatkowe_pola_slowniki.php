<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //        
        // kasuje rekordy w tablicy
        $db->delete_query('products_extra_fields_book' , " products_extra_fields_id = '".(int)$_POST['id']."'");    
        //
        foreach ( $_POST['nazwa'] as $pole ) {
            //
            if ( $filtr->process($pole) != '' ) {
                //
                $pola = array(
                        array('products_extra_fields_id',(int)$_POST['id']),
                        array('products_extra_fields_book_text',$filtr->process($pole))
                        );
                //
                $db->insert_query('products_extra_fields_book' , $pola);
                //
                unset($pola);
                //
            }
            //
        }
        //	
        Funkcje::PrzekierowanieURL('dodatkowe_pola.php?id_poz='.(int)$_POST['id']);
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <script>                        
    function dodaj_pole(zdjecie) {
        var ile_pol = parseInt($("#ile_pol").val()) + 1;
        //
        $.get('ajax/dodaj_pole_slownik.php', { id: ile_pol, format: parseInt($("#format").val()), zdjecie: zdjecie, katalog: '<?php echo KATALOG_ZDJEC; ?>' }, function(data) {
            $('#pola_slowniki').append(data);
            $("#ile_pol").val(ile_pol);
            //
            $(".kropka").change(		
              function () {
                var type = this.type;
                var tag = this.tagName.toLowerCase();
                if (type == 'text' && tag != 'textarea' && tag != 'radio' && tag != 'checkbox') {
                    //
                    zamien_krp($(this),'0.00');
                    //
                }
              }
            );
            //
            if ( zdjecie == 1 ) {
                //
                $('.obrazek').bind('focus',
                  function () {
                    var id = $(this).attr("id");
                        pokaz_obrazek_ajax(id, $(this).val());
                  }
                );
                //
            }
            //
            pokazChmurki();            
        });
        //
    } 
    function usun_pole(id) {
        $('.tip-twitter').css({'visibility':'hidden'});
        $('#pole_' + id).remove();
        //
        if ( $('#divfoto_' + id).length ) {
             $('#divfoto_' + id).remove();
        }
        //
    }
    </script>
    
    <div id="naglowek_cont">Słowniki dodatkowych pól</div>
    
    <div id="cont">
          
        <form action="slowniki/dodatkowe_pola_slowniki.php" method="post" id="slownikForm" class="cmxform">          

        <div class="poleForm">
          
          <?php
          if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
          }      

          $licznik_pol = 1;
          $zapytanieTmp = "select pe.products_extra_fields_name from products_extra_fields pe where pe.products_extra_fields_id = '" . (int)$_GET['id_poz'] . "'";
          $sqlTmp = $db->open_query($zapytanieTmp);
          $infoTmp = $sqlTmp->fetch_assoc();
          $NazwaPola = $infoTmp['products_extra_fields_name'];
          $db->close_query($sqlTmp);
          unset($zapytanieTmp, $infoTmp);
          ?>
          
          <div class="naglowek">Dodawanie / edycja danych (<?php echo $NazwaPola; ?>)</div>
          <div class="pozycja_edytowana">
          
              <div class="info_content">

                  <input type="hidden" name="akcja" value="zapisz" />
                  
                  <input type="hidden" name="id" value="<?php echo (int)$_GET['id_poz']; ?>" />
              
                  <div id="pola_slowniki">
                  
                      <?php
                      $zapytanie = "select pb.products_extra_fields_book_id, 
                                           pb.products_extra_fields_book_text,
                                           pe.products_extra_fields_image,
                                           pe.products_extra_fields_number
                                      from products_extra_fields pe 
                                 left join products_extra_fields_book pb on pb.products_extra_fields_id = pe.products_extra_fields_id
                                     where pe.products_extra_fields_id = '" . (int)$_GET['id_poz'] . "'";
                                           
                      $sql = $db->open_query($zapytanie);

                      $pola_zdjecie = 0;
                      
                      if ((int)$db->ile_rekordow($sql) > 0) {
                        
                          $e = 0;

                          while ($info = $sql->fetch_assoc()) {
                            
                          if ( $e == 0 ) {
                               //
                               echo '<input type="hidden" id="format" value="' . $info['products_extra_fields_number'] . '" />';
                               $e++;
                               //
                          }
                      
                          // jezeli nie jest obrazkowy
                          if ( $info['products_extra_fields_image'] == 0 ) {
                              ?>              
                      
                              <p id="pole_<?php echo $licznik_pol; ?>">
                                <label for="wartosc_<?php echo $licznik_pol; ?>">Wartość pola:</label>
                                
                                <?php if ( $info['products_extra_fields_number'] == '0' ) { ?>
                                
                                <input type="text" size="80" id="wartosc_<?php echo $licznik_pol; ?>" name="nazwa[]" value="<?php echo Funkcje::formatujTekstInput($info['products_extra_fields_book_text']); ?>" />
                                
                                <?php } else { ?>
                                
                                <input type="text" size="20" id="wartosc_<?php echo $licznik_pol; ?>" class="kropka" name="nazwa[]" value="<?php echo number_format((float)$info['products_extra_fields_book_text'],2,'.',''); ?>" />
                                
                                <?php } ?>
                                
                                <em class="TipChmurka"><b>Usuń pole słownika</b><span class="usun_pole" onclick="usun_pole(<?php echo $licznik_pol; ?>)"></span></em>
                              </p>
                              
                              <?php
                              
                            } else { 
                              
                              $pola_zdjecie = 1;
                            
                              ?>              
                      
                              <p id="pole_<?php echo $licznik_pol; ?>">
                                <label for="foto_<?php echo $licznik_pol; ?>">Zdjęcie pola:</label>
                                <input type="text" name="nazwa[]" id="foto_<?php echo $licznik_pol; ?>" class="obrazek" ondblclick="openFileBrowser('foto_<?php echo $licznik_pol; ?>','','<?php echo KATALOG_ZDJEC; ?>')" value="<?php echo $info['products_extra_fields_book_text']; ?>" size="105" autocomplete="off" /><em class="TipIkona"><b>Kliknij dwukrotnie w pole obok żeby otworzyć okno przeglądarki zdjęć</b></em>
                                <em class="TipChmurka"><b>Usuń pole słownika</b><span class="usun_pole" onclick="usun_pole(<?php echo $licznik_pol; ?>)"></span></em>
                                <span class="PrzegladarkaZdjec TipChmurka" onclick="openFileBrowser('foto_<?php echo $licznik_pol; ?>','','<?php echo KATALOG_ZDJEC; ?>')"><b>Kliknij żeby otworzyć okno przeglądarki zdjęć</b></span>
                              </p>
                              
                              <div id="divfoto_<?php echo $licznik_pol; ?>" style="padding-left:10px; display:none">
                                <label>&nbsp;</label>
                                <span id="fofoto_<?php echo $licznik_pol; ?>">
                                    <span class="zdjecie_tbl">
                                        <img src="obrazki/_loader_small.gif" alt="" />
                                    </span>
                                </span> 

                                <?php if (!empty($info['products_extra_fields_book_text'])) { ?>
                                
                                <script>       
                                pokaz_obrazek_ajax('foto_<?php echo $licznik_pol; ?>', '<?php echo $info['products_extra_fields_book_text']; ?>')
                                </script>  
   
                                <?php } ?>
                            
                              </div>
                              
                              <?php
                          }
                              
                          $licznik_pol++;
                          
                          }

                      }
                      $db->close_query($sql);
                      ?>

                  </div>
                  
                  <div style="padding:10px;padding-top:20px;">
                      <span class="dodaj" onclick="dodaj_pole(<?php echo $pola_zdjecie; ?>)" style="cursor:pointer">dodaj kolejną pozycję</span>
                  </div> 

              </div>
              
          </div>
          
          <input value="<?php echo $licznik_pol; ?>" type="hidden" name="ile_pol" id="ile_pol" />

          <?php
          unset($info, $pola_zdjecie, $pola_zdjecie);
          ?>                                

          <div class="przyciski_dolne">
            <input type="submit" class="przyciskNon" value="Zapisz dane" />
            <button type="button" class="przyciskNon" onclick="cofnij('dodatkowe_pola','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','slowniki');">Powrót</button>   
          </div>            

        </div>                      
        </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}