<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        
        // dodawanie nowej grupy        
        if ( (int)$_POST['jest_nowa_grupa'] == 1 ) {
        
            $pola = array(
                    array('banners_group_code',$filtr->process($_POST['kod'])),
                    array('banners_group_title',$filtr->process($_POST['opis'])));
                 
            $sql = $db->insert_query('banners_group' , $pola);
            
            unset($pola); 

            $_POST['grupa'] = $_POST['kod'];           

        }        
        
        $pola = array(array('banners_title',$filtr->process($_POST['nazwa'])),
                      array('banners_group',$filtr->process($_POST['grupa'])),
                      array('languages_id',$filtr->process($_POST["jezyk"]))
         );
                      
        // jezeli banner to kod html
        if ($_POST['tryb'] == 'html') {
            $pola[] = array('banners_html_text',$filtr->process($_POST['text_html']));
            $pola[] = array('banners_url','');
            $pola[] = array('banners_image','');
            $pola[] = array('banners_image_text','');            
        }   

        // jezeli banner to obraz
        if ($_POST['tryb'] == 'obraz') {
            $pola[] = array('banners_url',$filtr->process($_POST['adres']));
            $pola[] = array('banners_url_blank',(int)$_POST['nowe_okno']);
            $pola[] = array('banners_image',$filtr->process($_POST['zdjecie']));
            $pola[] = array('banners_image_text',$filtr->process($_POST['text']));
            $pola[] = array('banners_html_text','');
            //
            if ( $_POST['rodzaj_banneru'] == 'film' ) {
                 $pola[] = array('banners_type','film');
                 $pola[] = array('banners_mp4_width',(int)$_POST['film_maksymalna_szerokosc']);
                 $pola[] = array('banners_mp4_height',(int)$_POST['film_maksymalna_wysokosc']);
                 $pola[] = array('banners_mp4_controls',(($_POST['film_nawigacja'] == 'nie') ? 0 : 1));
                 $pola[] = array('banners_mp4_mute',(($_POST['film_dzwiek'] == 'nie') ? 0 : 1));
                 $pola[] = array('banners_mp4_autoplay',(($_POST['film_autostart'] == 'nie') ? 0 : 1));
                 $pola[] = array('banners_mp4_loop',(($_POST['film_zapetlenie'] == 'nie') ? 0 : 1));
                 //
            } else {
                 //
                 $pola[] = array('banners_type','grafika');
                 $pola[] = array('banners_mp4_width',0);
                 $pola[] = array('banners_mp4_height',0);                 
                 //
            }
        } else {
            $pola[] = array('banners_type','grafika');
            $pola[] = array('banners_mp4_width',0);
            $pola[] = array('banners_mp4_height',0);                                    
        }
        
        if ( isset($_POST['id_kat']) && count($_POST['id_kat']) > 0 ) {        
            $pola[] = array('only_categories_id', implode(',', (array)$_POST['id_kat']));
          } else {
            $pola[] = array('only_categories_id', '');
        }
        
        if (!empty($_POST['data_banneru_od'])) {
            $pola[] = array('banners_date',date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_POST['data_banneru_od']))));
          } else {
            $pola[] = array('banners_date','0000-00-00');            
        }
        if (!empty($_POST['data_banneru_do'])) {
            $pola[] = array('banners_date_end',date('Y-m-d H:i:s', FunkcjeWlasnePHP::my_strtotime($filtr->process($_POST['data_banneru_do']))));
          } else {
            $pola[] = array('banners_date_end','0000-00-00');            
        }        
        
        $dane_txt = array();
        foreach ( $_POST as $klucz => $wartosc ) {
             //
             if ( strpos((string)$klucz, 'txt_') > -1 ) {
                  //
                  $dane_txt[ $klucz ] = $wartosc;
                  //
             }
             //
        }      

        $pola[] = array('banners_text_config', serialize($dane_txt));
        $pola[] = array('banners_customers_group_id',((isset($_POST['grupa_klientow'])) ? implode(',', (array)$_POST['grupa_klientow']) : 0));
        
        $db->update_query('banners' , $pola, " banners_id = '".(int)$_POST["id"]."'");	
        unset($pola);  

        if ( isset($_SESSION['filtry']['bannery_zarzadzanie.php']['grupa']) && ( $_SESSION['filtry']['bannery_zarzadzanie.php']['grupa'] != $filtr->process($_POST['grupa']) ) ) {
          
             unset($_SESSION['filtry']['bannery_zarzadzanie.php']);
             $_SESSION['filtry']['bannery_zarzadzanie.php']['grupa'] = $filtr->process($_POST['grupa']);
             
        }

        if ( isset($_POST['powrot']) && (int)$_POST['powrot'] == 1 ) {
            //            
            Funkcje::PrzekierowanieURL('bannery_zarzadzanie_edytuj.php?id_poz='.(int)$_POST["id"].Funkcje::Zwroc_Get(array('id_poz','x','y'),true));  
            //
          } else {        
            //
            Funkcje::PrzekierowanieURL('bannery_zarzadzanie.php?id_poz='.(int)$_POST["id"].Funkcje::Zwroc_Get(array('id_poz','x','y'),true));  
            //
        }

    }   

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Edycja pozycji</div>
    <div id="cont">

          <form action="wyglad/bannery_zarzadzanie_edytuj.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="ppForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Edycja danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select * from banners where banners_id = '" . (int)$_GET['id_poz'] . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
            
                $info = $sql->fetch_assoc();
                
                $konfig = array();
                $unser = @unserialize($info['banners_text_config']);
                //
                if ( is_array($unser) ) {
                     //
                     $byla_tablica = true;
                     //
                     foreach ( $unser as $klucz => $wartosc ) {
                        //
                        $konfig[ $klucz ] = $wartosc;
                        //
                     }
                     //
                }
                //
                unset($unser);
                //                
                ?>            
            
                <div class="pozycja_edytowana">
                
                    <div class="info_content">
                
                    <input type="hidden" name="akcja" value="zapisz" />
                    
                    <input type="hidden" name="id" value="<?php echo (int)$_GET['id_poz']; ?>" />
                    
                    <script type="text/javascript" src="javascript/jquery.bestupper.min.js"></script>
                    
                    <?php if ( $info['banners_group'] != 'POPUP' && Wyglad::TypSzablonu() == true ) { ?>
                    <script type="text/javascript" src="wyglad/bannery_teksty.js"></script>
                    <?php } ?>

                    <script>
                    var katalog_zdjec = '<?php echo KATALOG_ZDJEC; ?>';
                    </script>
                    
                    <script type="text/javascript" src="programy/jscolor/jscolor.js"></script>
                
                    <script>
                    $(document).ready(function() {
                        $('.bestupper').bestupper();
                          
                        $("#ppForm").validate({
                          rules: {
                            nazwa: {
                              required: true
                            },
                            zdjecie: {
                              required: function() {var wynik = true; if ( $("input[name='tryb']:checked", "#ppForm").val() == "html" ) { wynik = false; } return wynik; }
                            },
                            kod: {
                              required: function() {var wynik = true; if ( $('#nowa_grupa').css('display') == 'none' ) { wynik = false; } return wynik; },
                              remote: "ajax/sprawdz_czy_zmienna_grupy_bannerow.php"
                            },
                            opis: {
                              required: function() {var wynik = true; if ( $('#nowa_grupa').css('display') == 'none' ) { wynik = false; } return wynik; }
                            }
                          },
                          messages: {
                            nazwa: {
                              required: "Pole jest wymagane."
                            },
                            kod: {
                              remote: "Grupa o takiej nazwie już istnieje."
                            }
                          }
                        });
                        
                        $('#dodaj_grupe').click(function(){
                          
                          if ( $('#nowa_grupa').css('display') == 'none' ) {
                               $('#nowa_grupa').slideDown();
                               $(this).html('anuluj dodanie nowej grupy').removeClass('dodaj').addClass('usun');
                               $('#jest_nowa_grupa').val(1);
                            } else {
                               $('#nowa_grupa').slideUp();
                               $(this).html('dodaj nową grupę').removeClass('usun').addClass('dodaj');
                               $('#jest_nowa_grupa').val(0);
                          }
                            
                        });
                        
                        $('input.datepicker').Zebra_DatePicker({
                           format: 'd-m-Y H:i',
                           inside: false,
                           readonly_element: true
                        });                                
                    });       

                    function zmien_tryb(id) {
                        if ($('#tryb_' + id).css('display') == 'none') {
                            $('#tryb_0').css('display','none'); 
                            $('#tryb_1').css('display','none');
                            //
                            $('#tryb_' + id).slideDown();
                        }
                    }   

                    function film_grafika(tryb) {
                        if (tryb == 'film') {
                            $('#UstawieniaFilmu').stop().slideDown(); 
                            $('#UstawieniaGrafiki').stop().slideUp();                         
                        }
                        if (tryb == 'grafika') {
                            $('#UstawieniaFilmu').stop().slideUp();
                            $('#UstawieniaGrafiki').stop().slideDown();                          
                        }                        
                    }                     
                    </script>  

                    <p>
                      <label class="required" for="nazwa">Nazwa banneru:</label>
                      <input type="text" name="nazwa" id="nazwa" value="<?php echo $info['banners_title']; ?>" size="50" /><em class="TipIkona"><b>Nazwa banneru - tekst wyświetlany po najechaniu kursorem myszy na obrazek banneru</b></em>
                    </p> 

                    <p>
                      <label for="grupa">Grupa:</label>             
                      <?php
                      $zapytanie_tmp = "select distinct * from banners_group order by banners_group_code asc";
                      $sqls = $db->open_query($zapytanie_tmp);
                      //
                      $tablica_grup = array();
                      while ($infs = $sqls->fetch_assoc()) { 
                        $tablica_grup[] = array('id' => $infs['banners_group_code'], 'text' => $infs['banners_group_code'] . ' - ' . $infs['banners_group_title']);
                      }
                      $db->close_query($sqls); 
                      unset($zapytanie_tmp, $infs);                   
                      
                      echo Funkcje::RozwijaneMenu('grupa', $tablica_grup, $info['banners_group'], 'style="width:400px" id="grupa"'); 
                      ?>
                    </p>
                    
                    <p>
                      <label></label>
                      <span id="dodaj_grupe" class="dodaj" style="cursor:pointer">dodaj nową grupę</span>
                      <input type="hidden" name="jest_nowa_grupa" id="jest_nowa_grupa" value="0" />
                    </p>
                    
                    <div id="nowa_grupa" style="display:none">
                    
                        <hr style="color:#82b4cd;border-top: 1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:99%;" />
                    
                        <p>
                            <label class="required" for="kod">Kod grupy:</label>
                            <input type="text" name="kod" id="kod" value="" size="40" class="bestupper" onkeyup="updateKey();" /><em class="TipIkona"><b>Kod banneru jaki będzie używany w szablonach - nie może zawierać spacji i polskich znaków - musi być unikalny - np BANNERY_ANIMACJA</b></em>
                        </p>
                        
                        <p>
                            <label class="required" for="opis">Opis grupy:</label>
                            <input type="text" name="opis" id="opis" value="" size="80" /><em class="TipIkona"><b>Opis będzie wyświetlany przy dodawaniu nowych bannerów</b></em>
                        </p>                    
                        
                        <hr style="color:#82b4cd;border-top: 1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:99%;" />
                    
                    </div>
                    
                    <p>
                      <label for="jezyk">Dostępny dla wersji językowej:</label>
                      <?php
                      $tablica_jezykow = Funkcje::TablicaJezykow(true);                 
                      echo Funkcje::RozwijaneMenu('jezyk',$tablica_jezykow,$info['languages_id'],  'id="jezyk"');
                      ?>                  
                    </p>                    
                    
                    <table class="WyborCheckbox">
                        <tr>
                            <td><label>Widoczny dla grupy klientów:</label></td>
                            <td>
                                <?php                        
                                $TablicaGrupKlientow = Klienci::ListaGrupKlientow(false);
                                foreach ( $TablicaGrupKlientow as $GrupaKlienta ) {
                                    echo '<input type="checkbox" value="' . $GrupaKlienta['id'] . '" name="grupa_klientow[]" id="grupa_klientow_' . $GrupaKlienta['id'] . '" ' . ((in_array((string)$GrupaKlienta['id'], explode(',', (string)$info['banners_customers_group_id']))) ? 'checked="checked" ' : '') . ' /> <label class="OpisFor" for="grupa_klientow_' . $GrupaKlienta['id'] . '">' . $GrupaKlienta['text'] . '</label><br />';
                                }               
                                unset($TablicaGrupKlientow);
                                ?>
                            </td>
                        </tr>
                    </table>    

                    <div class="ostrzezenie" style="margin:0px 15px 10px 10px">Jeżeli nie zostanie wybrana żadna grupa klientów to banner będzie widoczny dla wszystkich klientów.</div> 

                    <p>
                      <label>Banner będzie obrazkiem czy będzie to kod HTML ?</label>
                      <input type="radio" value="obraz" name="tryb" id="forma_obrazek" onclick="zmien_tryb(0)" <?php echo ((empty($info['banners_html_text'])) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="forma_obrazek">w formie obrazka<em class="TipIkona"><b>Banner będzie obrazkiem statycznym</b></em></label>
                      <input type="radio" value="html" name="tryb" id="forma_kod" onclick="zmien_tryb(1)" <?php echo ((empty($info['banners_html_text'])) ? '' : 'checked="checked"'); ?> /><label class="OpisFor" for="forma_kod">jako kod HTML<em class="TipIkona"><b>Banner będzie generowany przez kod HTML</b></em></label>
                    </p>                  
        
                    <div id="tryb_0" <?php echo ((empty($info['banners_html_text'])) ? '' : 'style="display:none"'); ?>>
                        <p>
                          <label for="adres">Adres URL:</label>
                          <input type="text" name="adres" id="adres" value="<?php echo $info['banners_url']; ?>" size="50" /><em class="TipIkona"><b>Adres strony do jakiej ma kierować banner</b></em>
                        </p>
                        
                        <p>
                          <label>Czy link ma być otwierany w nowym oknie ?</label>
                          <input type="radio" value="0" name="nowe_okno" id="nowe_okno_nie" <?php echo (($info['banners_url_blank'] == '0') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="nowe_okno_nie">nie</label>
                          <input type="radio" value="1" name="nowe_okno" id="nowe_okno_tak" <?php echo (($info['banners_url_blank'] == '1') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="nowe_okno_tak">tak</label>
                        </p>      

                        <p>
                          <label>Rodzaj banneru:</label>
                          <input type="radio" value="grafika" name="rodzaj_banneru" id="rodzaj_banneru_grafika" onclick="film_grafika('grafika')" <?php echo (($info['banners_type'] == 'grafika') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="rodzaj_banneru_grafika">grafika (jpg, png, gif, webp)</label>
                          <input type="radio" value="film" name="rodzaj_banneru" id="rodzaj_banneru_film" onclick="film_grafika('film')" <?php echo (($info['banners_type'] == 'film') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="rodzaj_banneru_film">film (mp4)</label>
                        </p>                           

                        <p>
                          <label class="required" for="foto">Ścieżka obrazka / filmu mp4:</label>           
                          <input type="text" name="zdjecie" size="95" value="<?php echo $info['banners_image']; ?>" ondblclick="openFileBrowser('foto','','<?php echo KATALOG_ZDJEC; ?>')" id="foto" autocomplete="off" /><em class="TipIkona"><b>Kliknij dwukrotnie w pole obok żeby otworzyć okno przeglądarki zdjęć</b></em>
                          <span class="PrzegladarkaZdjec TipChmurka" onclick="openFileBrowser('foto','','<?php echo KATALOG_ZDJEC; ?>')"><b>Kliknij żeby otworzyć okno przeglądarki zdjęć</b></span>
                        </p>   

                        <div id="UstawieniaFilmu" <?php echo (($info['banners_type'] == 'film') ? '' : 'style="display:none"'); ?>>
                        
                            <p>
                                <label for="film_maksymalna_szerokosc">Maksymalna szerokość filmu w px:</label>
                                <input type="text" name="film_maksymalna_szerokosc" size="10" value="<?php echo (((int)$info['banners_mp4_width'] > 0) ? (int)$info['banners_mp4_width'] : ''); ?>" class="calkowita" />
                            </p>  

                            <p>
                                <label for="film_maksymalna_wysokosc">Maksymalna wysokość filmu w px:</label>
                                <input type="text" name="film_maksymalna_wysokosc" size="10" value="<?php echo (((int)$info['banners_mp4_height'] > 0) ? (int)$info['banners_mp4_height'] : ''); ?>" class="calkowita" />
                            </p>   

                            <p>
                              <label>Czy wyświetlać przyciski kontrolne (play / stop / dźwięk) ?</label>
                              <input type="radio" value="nie" name="film_nawigacja" id="film_nawigacja_nie" <?php echo (($info['banners_mp4_controls'] == '0') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="film_nawigacja_nie">nie</label>
                              <input type="radio" value="tak" name="film_nawigacja" id="film_nawigacja_tak" <?php echo (($info['banners_mp4_controls'] == '1') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="film_nawigacja_tak">tak</label>
                            </p>   

                            <p>
                              <label>Czy wyciszyć dźwięk ?</label>
                              <input type="radio" value="nie" name="film_dzwiek" id="film_dzwiek_nie" <?php echo (($info['banners_mp4_mute'] == '0') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="film_dzwiek_nie">nie</label>
                              <input type="radio" value="tak" name="film_dzwiek" id="film_dzwiek_tak" <?php echo (($info['banners_mp4_mute'] == '1') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="film_dzwiek_tak">tak</label>
                            </p>  

                            <p>
                              <label>Czy uruchomić film od razu po wyświetlaniu strony ?</label>
                              <input type="radio" value="nie" name="film_autostart" id="film_autostart_nie" <?php echo (($info['banners_mp4_autoplay'] == '0') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="film_autostart_nie">nie</label>
                              <input type="radio" value="tak" name="film_autostart" id="film_autostart_tak" <?php echo (($info['banners_mp4_autoplay'] == '1') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="film_autostart_tak">tak</label>
                            </p>                              
                        
                            <p>
                              <label>Czy film wyświetlać w zapętleniu ?</label>
                              <input type="radio" value="nie" name="film_zapetlenie" id="film_zapetlenie_nie" <?php echo (($info['banners_mp4_loop'] == '0') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="film_zapetlenie_nie">nie</label>
                              <input type="radio" value="tak" name="film_zapetlenie" id="film_zapetlenie_tak" <?php echo (($info['banners_mp4_loop'] == '1') ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="film_zapetlenie_tak">tak</label>
                            </p>  
                            
                        </div>       

                        <div id="UstawieniaGrafiki" <?php echo (($info['banners_type'] == 'grafika') ? '' : 'style="display:none"'); ?>>

                            <p>
                                <label for="text">Dodatkowy tekst:</label>
                                <textarea cols="62" rows="4" name="text" id="text"><?php echo $info['banners_image_text']; ?></textarea><em class="TipIkona"><b>Tekst który może się wyświetlać na bannerze - opcja używana przy niektórych animowanyych modułach bannerów</b></em>
                            </p>
                              
                        </div>
                        
                    </div>

                    <div id="tryb_1" <?php echo ((empty($info['banners_html_text'])) ? 'style="display:none"' : ''); ?>>
                    
                        <p>
                            <label for="text_html">Wstaw kod:</label>
                            <textarea cols="120" rows="15" name="text_html" id="text_html"><?php echo $info['banners_html_text']; ?></textarea>
                        </p>
                        
                        <script>
                        $(document).ready(function() {
                            $('.WlaczEdytor').click(function() {
                                //
                                $(this).hide();
                                $('.WylaczEdytor').show();
                                //
                                ckedit('text_html','99%','200');
                                //   
                            });
                            $('.WylaczEdytor').click(function() {
                                //
                                $(this).hide();
                                $('.WlaczEdytor').show();
                                //
                                for (instance in CKEDITOR.instances) {
                                  if (CKEDITOR.instances.hasOwnProperty(instance)) {
                                      if (CKEDITOR.instances[instance].name == 'text_html') {
                                          CKEDITOR.instances[instance].updateElement();
                                          CKEDITOR.instances[instance].destroy();
                                      }
                                  }
                                }  
                                //   
                            });                            
                        });
                        </script>
                        
                        <span class="przyciskNon WylaczEdytor" style="margin:10px;display:none">Wyłącz edytor</span>
 
                        <p>
                            <label></label>
                            <span class="przyciskNon WlaczEdytor" style="margin:0 0 10px 0">Włącz edytor</span>                            
                        </p>

                    </div>    
                                        
                    <p>
                        <label for="data_banneru_od">Data rozpoczęcia:</label>
                        <input type="text" id="data_banneru_od" name="data_banneru_od" value="<?php echo ((Funkcje::czyNiePuste($info['banners_date'])) ? date('d-m-Y H:i',FunkcjeWlasnePHP::my_strtotime($info['banners_date'])) : ''); ?>" size="20"  class="datepicker" />
                    </p>
                    
                    <p>
                        <label for="data_banneru_do">Data zakończenia:</label>
                        <input type="text" id="data_banneru_do" name="data_banneru_do" value="<?php echo ((Funkcje::czyNiePuste($info['banners_date_end'])) ? date('d-m-Y H:i',FunkcjeWlasnePHP::my_strtotime($info['banners_date_end'])) : ''); ?>" size="20" class="datepicker" />
                    </p>               
                    
                    <p>
                        <label>Wyświetlany tylko dla kategorii: <em class="TipIkona"><b>Banner będzie wyświetlany tylko w określonych kategoriach produktów - dla modułów bannerów widocznych na podstronach sklepu</b></em></label>
                    </p>                    

                    <div id="drzewo" class="OknoKategorie">
                    
                        <?php
                        $KategorieWarunek = explode(',', (string)$info['only_categories_id']);
                        
                        if ( count($KategorieWarunek) > 10 || KATEGORIE_LISTING_EDYCJA == 'wszystkie' ) {
                            //
                            echo '<ul id="drzewoKategorii">';
                            foreach(Kategorie::DrzewoKategoriiZarzadzanie() as $IdKategorii => $Tablica) {
                                //
                                echo Kategorie::WyswietlDrzewoKategoriiCheckbox($IdKategorii, $Tablica, $KategorieWarunek);
                                //
                            }    
                            echo '</ul>';
                            //
                        } else {
                            //
                            echo '<table class="pkc">';
                            //
                            $tablica_kat = Kategorie::DrzewoKategorii('0', '', '', '', false, true);
                            for ($w = 0, $c = count($tablica_kat); $w < $c; $w++) {
                                $podkategorie = false;
                                if ($tablica_kat[$w]['podkategorie'] == 'true') { $podkategorie = true; }
                                //
                                $check = '';
                                if ( in_array((string)$tablica_kat[$w]['id'], $KategorieWarunek) ) {
                                    $check = 'checked="checked"';
                                }
                                //                                
                                echo '<tr>
                                        <td class="lfp"><input type="checkbox" value="'.$tablica_kat[$w]['id'].'" name="id_kat[]" id="id_kat_' . $tablica_kat[$w]['id'] . '" '.$check.' /> <label class="OpisFor" for="id_kat_' . $tablica_kat[$w]['id'] . '">'.$tablica_kat[$w]['text'].(($tablica_kat[$w]['status'] == 0) ? '<span class="wylKat TipChmurka"><b>Kategoria jest nieaktywna</b></span>' : '').'</label></td>
                                        <td class="rgp" '.(($podkategorie) ? 'id="img_'.$tablica_kat[$w]['id'].'"' : '').'>'.(($podkategorie) ? '<img src="obrazki/rozwin.png" alt="Rozwiń" onclick="podkat(\''.$tablica_kat[$w]['id'].'\',\'\',\'checkbox\')" />' : '').'</td>
                                      </tr>
                                      '.(($podkategorie) ? '<tr><td colspan="2"><div id="p_'.$tablica_kat[$w]['id'].'"></div></td></tr>' : '').'';
                            }
                            echo '</table>';
                            unset($tablica_kat,$podkategorie);                                
                            //
                            foreach ( $KategorieWarunek as $kategoria ) {
                            
                                $sciezka = Kategorie::SciezkaKategoriiId($kategoria);
                                $cSciezka = explode("_", (string)$sciezka);                    
                                if (count($cSciezka) > 1) {
                                    //
                                    $ostatnie = strRpos($sciezka,'_');
                                    $analiza_sciezki = str_replace("_", ",", substr((string)$sciezka, 0, (int)$ostatnie));
                                    ?>
                                    
                                    <script>          
                                    podkat('<?php echo $analiza_sciezki; ?>', '<?php echo $cSciezka[count($cSciezka)-1]; ?>','checkbox','<?php echo implode(',', (array)$KategorieWarunek); ?>');
                                    </script>
                                    
                                <?php
                                unset($sciezka,$cSciezka);
                                }
                          
                            }                             
                        }
                        unset($KategorieWarunek);
                        ?>      
                                
                    </div>                      

                    </div>             
                   
                </div>

                <?php if ( $info['banners_group'] != 'POPUP' && Wyglad::TypSzablonu() == true ) { ?>

                <div class="RozwinTeksty" data-id="zwin"><span>Rozwiń konfigurację nakładania tekstów na bannery</span></div>

                <div id="GrafikiTeksty" style="display:none">
                
                    <div class="GrafikaTekstNaglowek">Nakładanie tekstów na bannery / podgląd grafiki</div>
                    
                    <?php
                    $plikPodgladu = 'domyslny.webp';
                    
                    if ( !empty($info['banners_image']) ) {
                    
                          if ( file_exists('../' . KATALOG_ZDJEC . '/' . $info['banners_image']) ) {

                               $plikPodgladu = $info['banners_image'];

                          }
                          
                    }
                    ?>
                    
                    <div class="GrafikaPodgladKontener">
                    
                        <div class="GrafikaPodgladKontenerStale">
                    
                            <div class="GrafikaPodglad GrafikaPodgladSkaluj">
                           
                                  <div class="GrafikaOdstep">
                                  
                                      <div class="GrafikaImg<?php echo ((isset($konfig['txt_rodzaj_efektu'])) ? ' Efekt_' . $konfig['txt_rodzaj_efektu'] : ''); ?>" data-plik="<?php echo $plikPodgladu; ?>">
                                      
                                          <div class="DaneTekstuKontener">
                                          
                                              <div id="DaneTekstu">
                                              
                                                  <div id="Linia-1"></div>
                                                  <div style="clear:both"></div>
                                                  
                                                  <div id="Linia-2"></div>
                                                  <div style="clear:both"></div>
                                                  
                                                  <div id="Linia-3"></div>
                                                  <div style="clear:both"></div>
                                                  
                                              </div>
                                              
                                          </div>
                                          
                                          <div class="GrafikaFoto"><img src="<?php echo '../' . KATALOG_ZDJEC . '/' . $plikPodgladu; ?>" alt="Podgląd" /></div>
                                          
                                      </div>
                                      
                                  </div>

                            </div>
                            
                        </div>
                        
                    </div>
                    
                    <div class="PrzyciskiSkalowania">
                    
                        <span class="przyciskNon" onclick="createCookie('skaluj',0,1); PodmienGrafike()">Oryginalna wielkość grafiki</span>
                        <span class="przyciskNon" onclick="createCookie('skaluj',1,1); PodmienGrafike()">Przeskalowana do szerokości ekranu</span>
                        
                    </div>
                    
                    <div class="TytulDzialu" style="margin:0px">Ustawienia efektu po najechaniu na link banneru</div>

                    <div class="UstawieniaTekstuWspolne">
                    
                        <p>
                          <label for="rodzaj_efektu">Rodzaj efektu:</label>             
                          <?php
                          $tablica = array( array('id' => '0', 'text' => '-- bez efektu --'),
                                            array('id' => '1', 'text' => 'przyciemnienie'),
                                            array('id' => '2', 'text' => 'przyciemnienie od lewej do prawej'),
                                            array('id' => '3', 'text' => 'przyciemnienie od prawej do lewej'),
                                            array('id' => '4', 'text' => 'przyciemnienie od środka na boki'),
                                            array('id' => '5', 'text' => 'przyciemnienie powiększenie od środka'),
                                            array('id' => '6', 'text' => 'rozjaśnienie'),
                                            array('id' => '7', 'text' => 'rozjaśnienie od lewej do prawej'),
                                            array('id' => '8', 'text' => 'rozjaśnienie od prawej do lewej'),
                                            array('id' => '9', 'text' => 'rozjaśnienie od środka na boki'),
                                            array('id' => '10', 'text' => 'rozjaśnienie powiększenie od środka'),
                                            array('id' => '11', 'text' => 'powiększenie grafiki'),
                                            array('id' => '12', 'text' => 'powiększenie i przekręcenie grafiki'),
                                            array('id' => '13', 'text' => 'rozmycie grafiki'),
                                            array('id' => '14', 'text' => 'zamiana grafiki na tryb czarno-biały'),
                                            array('id' => '15', 'text' => 'zamiana grafiki na tryb sepia'),
                                            array('id' => '16', 'text' => 'błysk od lewej do prawej'),
                                            array('id' => '17', 'text' => 'rozszerzające się białe ramki'),
                                            );
                                            
                          echo Funkcje::RozwijaneMenu('txt_rodzaj_efektu', $tablica, ((isset($konfig['txt_rodzaj_efektu'])) ? $konfig['txt_rodzaj_efektu'] : ''), ' id="rodzaj_efektu" onchange="WyswietlEfektHover(this.value)"');
                          unset($tablica);
                          ?>
                        </p>     

                    </div>                    
                    
                    <div class="TytulDzialu" style="margin:0px">Ustawienia animacji tekstu</div>

                    <div class="UstawieniaTekstuWspolne">
                    
                        <p>
                          <label for="rodzaj_animacji">Sposób animacji tekstu:</label>             
                          <?php
                          $tablica = array( array('id' => '0', 'text' => '-- bez animacji --'),
                                            array('id' => '1', 'text' => 'animacja nr 1'),
                                            array('id' => '2', 'text' => 'animacja nr 2'),
                                            array('id' => '3', 'text' => 'animacja nr 3'),
                                            array('id' => '4', 'text' => 'animacja nr 4'),
                                            array('id' => '5', 'text' => 'animacja nr 5'),
                                            array('id' => '6', 'text' => 'animacja nr 6'),
                                            array('id' => '7', 'text' => 'animacja nr 7'),
                                            array('id' => '8', 'text' => 'animacja nr 8'),
                                            array('id' => '9', 'text' => 'animacja nr 9'),
                                            array('id' => '10', 'text' => 'animacja nr 10'),
                                            array('id' => '11', 'text' => 'animacja nr 11'),
                                            array('id' => '12', 'text' => 'animacja nr 12'),
                                            array('id' => '13', 'text' => 'animacja nr 13'),
                                            array('id' => '14', 'text' => 'animacja nr 14'),
                                            array('id' => '15', 'text' => 'animacja nr 15') );
                                            
                          echo Funkcje::RozwijaneMenu('txt_rodzaj_animacji', $tablica, ((isset($konfig['txt_rodzaj_animacji'])) ? $konfig['txt_rodzaj_animacji'] : ''), ' id="rodzaj_animacji" onchange="WyswietlAnimacje(this.value)"');
                          unset($tablica);
                          ?>
                        </p>     

                    </div>
                    
                    <div class="TekstPodzialLinie">
                    
                        <?php for ( $c = 1; $c < 4; $c++ ) { ?>
                    
                        <div class="PodzialJednaLinia JednaLinia-<?php echo $c; ?>">
                        
                            <div class="PodzialJednaLiniaOdstep">
                              
                                <div class="TytulDzialu">Linia tekstu nr <?php echo $c; ?></div>
                                
                                <div class="DaneLinia">
                            
                                    <p>
                                      <label for="tekst_linia_<?php echo $c; ?>">Tekst:<em class="TipIkona"><b>Tekst na bannerze - linia nr <?php echo $c; ?> - tylko sam tekst - bez kodu HTML</b></em></label>
                                      <textarea name="txt_linia_<?php echo $c; ?>" id="linia_<?php echo $c; ?>" class="ZmianaPola" style="width:100%" rows="2" cols="10" /><?php echo ((isset($konfig['txt_linia_' . $c])) ? $konfig['txt_linia_' . $c] : ''); ?></textarea>
                                    </p> 
                                    
                                    <p>
                                      <label for="rozmiar_linia_<?php echo $c; ?>">Szerokość wyświetlanego tekstu:</label>             
                                      <?php
                                      $tablica = array( array('id' => 'block', 'text' => '100% szerokości'),
                                                        array('id' => 'inline-block', 'text' => 'szerokość tekstu') );
                                                        
                                      echo Funkcje::RozwijaneMenu('txt_rozmiar_linia_' . $c, $tablica, ((isset($konfig['txt_rozmiar_linia_' . $c])) ? $konfig['txt_rozmiar_linia_' . $c] : 'block'), ' id="rozmiar_linia_' . $c . '" class="ZmianaWybor"');
                                      unset($tablica);
                                      ?>
                                      <em class="TipIkona"><b>Opcja widoczna po dodaniu tła do tekstu. Jeżeli zostanie wybrana opcja 100% szerokości to tło będzie widoczne na całą szerokość okna napisu</b></em>
                                    </p> 
                                    
                                    <p>
                                      <label for="czcionka_linia_<?php echo $c; ?>">Czcionka:</label>             
                                      <?php
                                      $tablica = array( array('id' => '', 'text' => '-- domyślna szablonu --'),
                                                        array('id' => 'Arial', 'text' => 'Arial'),
                                                        array('id' => 'Tahoma', 'text' => 'Tahoma'),
                                                        array('id' => 'Verdana', 'text' => 'Verdana'),
                                                        array('id' => 'Times New Roman', 'text' => 'Times New Roman'),
                                                        array('id' => 'Georgia', 'text' => 'Georgia') );
                                                        
                                      echo Funkcje::RozwijaneMenu('txt_czcionka_linia_' . $c, $tablica, ((isset($konfig['txt_czcionka_linia_' . $c])) ? $konfig['txt_czcionka_linia_' . $c] : ''), ' id="czcionka_linia_' . $c . '" class="ZmianaWybor"');
                                      unset($tablica);
                                      ?>
                                    </p>    

                                    <p>
                                      <label for="odstep_linii_linia_<?php echo $c; ?>">Odstęp pomiędzy wierszami tekstu:</label>             
                                      <?php
                                      $tablica = array();
                                      for ( $t = 0; $t < 21; $t++ ) {
                                            $tablica[] = array('id' => ((10 + $t) / 10), 'text' => number_format(((10 + $t) / 10), 2, '.', ''));
                                      }
                                      echo Funkcje::RozwijaneMenu('txt_odstep_linii_linia_' . $c, $tablica, ((isset($konfig['txt_odstep_linii_linia_' . $c])) ? $konfig['txt_odstep_linii_linia_' . $c] : '1.5'), ' id="odstep_linii_linia_' . $c . '" class="ZmianaWybor"');
                                      unset($tablica);
                                      ?>
                                    </p>
                                    
                                    <?php if ( $c == 2 ) { ?>
                                    
                                        <p>
                                          <label for="odstep_gorny_linia_2">Górny odstęp linii nr 2 od linii nr 1:</label>             
                                          <?php
                                          $tablica = array();
                                          for ( $t = 0; $t < 31; $t++ ) {
                                                $tablica[] = array('id' => $t, 'text' => $t . 'px');
                                          }
                                          echo Funkcje::RozwijaneMenu('txt_odstep_gorny_linia_2', $tablica, ((isset($konfig['txt_odstep_gorny_linia_2'])) ? $konfig['txt_odstep_gorny_linia_2'] : 0), ' id="odstep_gorny_linia_2" class="ZmianaWybor"');
                                          unset($tablica);
                                          ?>
                                        </p>
                                    
                                    <?php } ?>

                                    <?php if ( $c == 3 ) { ?>
                                    
                                        <p>
                                          <label for="odstep_gorny_linia_2">Górny odstęp linii nr 3 od linii nr 2:</label>             
                                          <?php
                                          $tablica = array();
                                          for ( $t = 0; $t < 31; $t++ ) {
                                                $tablica[] = array('id' => $t, 'text' => $t . 'px');
                                          }
                                          echo Funkcje::RozwijaneMenu('txt_odstep_gorny_linia_3', $tablica, ((isset($konfig['txt_odstep_gorny_linia_3'])) ? $konfig['txt_odstep_gorny_linia_3'] : 0), ' id="odstep_gorny_linia_3" class="ZmianaWybor"');
                                          unset($tablica);
                                          ?>
                                        </p>
                                    
                                    <?php } ?>                                    
                                        
                                    <p>
                                      <label for="czcionka_kolor_linia_<?php echo $c; ?>">Kolor czcionki:</label>
                                      <input name="txt_czcionka_kolor_linia_<?php echo $c; ?>" class="color {required:false}" id="czcionka_kolor_linia_<?php echo $c; ?>" style="-moz-box-shadow:none" value="<?php echo ((isset($konfig['txt_czcionka_kolor_linia_' . $c])) ? $konfig['txt_czcionka_kolor_linia_' . $c] : '#FFFFFF'); ?>" size="8" onchange="PokazPrzykladTekstu()" />                    
                                    </p> 
                                    
                                    <p>
                                      <label for="czcionka_grubosc_linia_<?php echo $c; ?>">Grubość czcionki:</label>             
                                      <?php
                                      $tablica = array( array('id' => 'normal', 'text' => 'normalna'),
                                                        array('id' => 'light', 'text' => 'light'),
                                                        array('id' => 'medium', 'text' => 'pogrubiona (semi-bold)'),
                                                        array('id' => 'bold', 'text' => 'gruba (bold)') );
                                                        
                                      echo Funkcje::RozwijaneMenu('txt_czcionka_grubosc_linia_' . $c, $tablica, ((isset($konfig['txt_czcionka_grubosc_linia_' . $c])) ? $konfig['txt_czcionka_grubosc_linia_' . $c] : ''), ' id="czcionka_grubosc_linia_' . $c . '" class="ZmianaWybor"');                                      
                                      unset($tablica);
                                      ?>
                                      <em class="TipIkona"><b>Wartość "light" oraz "podgrubiona" jest zależna od użytej w szablonie czcionki i nie zawsze będzie widoczna</b></em>
                                    </p>  

                                    <p>
                                      <label for="czcionka_pochylenie_linia_<?php echo $c; ?>">Pochylenie czcionki:</label>             
                                      <?php
                                      $tablica = array( array('id' => 'normal', 'text' => 'normalna'),
                                                        array('id' => 'italic', 'text' => 'pochylona') );
                                                        
                                      echo Funkcje::RozwijaneMenu('txt_czcionka_pochylenie_linia_' . $c, $tablica, ((isset($konfig['txt_czcionka_pochylenie_linia_' . $c])) ? $konfig['txt_czcionka_pochylenie_linia_' . $c] : ''), ' id="czcionka_pochylenie_linia_' . $c . '" class="ZmianaWybor"');                                      
                                      unset($tablica);
                                      ?>
                                    </p>                                      
                                    
                                    <p>
                                      <label for="czcionka_cien_linia_<?php echo $c; ?>">Czy wyświetlać cień tekstu ?</label>             
                                      <?php
                                      $tablica = array( array('id' => 'nie', 'text' => 'nie'),
                                                        array('id' => 'tak', 'text' => 'tak') );
                                                        
                                      echo Funkcje::RozwijaneMenu('txt_czcionka_cien_linia_' . $c, $tablica, ((isset($konfig['txt_czcionka_cien_linia_' . $c])) ? $konfig['txt_czcionka_cien_linia_' . $c] : ''), ' id="czcionka_cien_linia_' . $c . '" onchange="ZmienCien(' . $c . ', this.value)"');
                                      unset($tablica);
                                      ?>
                                    </p> 
                                    
                                    <div id="cien_linia_<?php echo $c; ?>" <?php echo ((isset($konfig['txt_czcionka_cien_linia_' . $c]) && $konfig['txt_czcionka_cien_linia_' . $c] == 'tak') ? '' : 'style="display:none"'); ?>>

                                        <p>
                                          <label for="czcionka_cien_poziomy_linia_<?php echo $c; ?>">Wielkość cienia z poziomie:</label>             
                                          <?php
                                          $tablica = array();
                                          for ( $t = 0; $t < 11; $t++ ) {
                                                $tablica[] = array('id' => $t, 'text' => $t . 'px');
                                          }
                                          echo Funkcje::RozwijaneMenu('txt_czcionka_cien_poziomy_linia_' . $c, $tablica, ((isset($konfig['txt_czcionka_cien_poziomy_linia_' . $c])) ? $konfig['txt_czcionka_cien_poziomy_linia_' . $c] : ''), ' id="czcionka_cien_poziomy_linia_' . $c . '" class="ZmianaWybor"');
                                          unset($tablica);
                                          ?>
                                        </p>

                                        <p>
                                          <label for="czcionka_cien_pion_linia_<?php echo $c; ?>">Wielkość cienia z pionie:</label>             
                                          <?php
                                          $tablica = array();
                                          for ( $t = 0; $t < 11; $t++ ) {
                                                $tablica[] = array('id' => $t, 'text' => $t . 'px');
                                          }
                                          echo Funkcje::RozwijaneMenu('txt_czcionka_cien_pion_linia_' . $c, $tablica, ((isset($konfig['txt_czcionka_cien_pion_linia_' . $c])) ? $konfig['txt_czcionka_cien_pion_linia_' . $c] : ''), ' id="czcionka_cien_pion_linia_' . $c . '" class="ZmianaWybor"');
                                          unset($tablica);
                                          ?>
                                        </p>  

                                        <p>
                                          <label for="czcionka_cien_rozmycie_linia_<?php echo $c; ?>">Rozmycie cienia tekstu:</label>             
                                          <?php
                                          $tablica = array();
                                          for ( $t = 0; $t < 11; $t++ ) {
                                                $tablica[] = array('id' => $t, 'text' => $t . 'px');
                                          }
                                          echo Funkcje::RozwijaneMenu('txt_czcionka_cien_rozmycie_linia_' . $c, $tablica, ((isset($konfig['txt_czcionka_cien_rozmycie_linia_' . $c])) ? $konfig['txt_czcionka_cien_rozmycie_linia_' . $c] : ''), ' id="czcionka_cien_rozmycie_linia_' . $c . '" class="ZmianaWybor"');
                                          unset($tablica);
                                          ?>
                                        </p>       

                                        <p>
                                          <label for="czcionka_cien_kolor_linia_<?php echo $c; ?>">Kolor cienia tekstu:</label>
                                          <input name="txt_czcionka_cien_kolor_linia_<?php echo $c; ?>" class="color {required:false}" id="czcionka_cien_kolor_linia_<?php echo $c; ?>" style="-moz-box-shadow:none" value="<?php echo ((isset($konfig['txt_czcionka_cien_kolor_linia_' . $c])) ? $konfig['txt_czcionka_cien_kolor_linia_' . $c] : '#FFFFFF'); ?>" size="8" onchange="PokazPrzykladTekstu()" />                    
                                        </p>    

                                    </div>

                                    <p>
                                      <label for="kolor_tla_linia_<?php echo $c; ?>">Kolor tła:</label>
                                      <input name="txt_kolor_tla_linia_<?php echo $c; ?>" class="color {required:false}" id="kolor_tla_linia_<?php echo $c; ?>" style="-moz-box-shadow:none" value="<?php echo ((isset($konfig['txt_kolor_tla_linia_' . $c])) ? $konfig['txt_kolor_tla_linia_' . $c] : ''); ?>" size="8" onchange="PokazPrzykladTekstu()" />                    
                                      <em class="TipChmurka"><b>Usuń kolor</b><img onclick="$('#kolor_tla_linia_<?php echo $c; ?>').val('');$('#kolor_tla_linia_<?php echo $c; ?>').css('background-color','rgb(255, 255, 255)');PokazPrzykladTekstu()" style="cursor:pointer;" src="obrazki/kasuj.png" alt="Skasuj" /></em>
                                    </p>                                     
                                    
                                    <p>
                                      <label for="przezroczystosc_tla_linia_<?php echo $c; ?>">Przeźroczystość tła tekstu:</label>             
                                      <?php
                                      $tablica = array();
                                      for ( $t = 1; $t < 11; $t++ ) {
                                            $tablica[] = array('id' => ($t * 10), 'text' => ($t * 10) . '%');
                                      }
                                      echo Funkcje::RozwijaneMenu('txt_przezroczystosc_tla_linia_' . $c, $tablica, ((isset($konfig['txt_przezroczystosc_tla_linia_' . $c])) ? $konfig['txt_przezroczystosc_tla_linia_' . $c] : ''), ' id="przezroczystosc_tla_linia_' . $c . '" class="ZmianaWybor"');
                                      unset($tablica);
                                      ?>
                                    </p>                     

                                    <p>
                                      <label for="odstep_tla_linia_<?php echo $c; ?>">Odstęp tła od tekstu:</label>             
                                      <?php
                                      $tablica = array();
                                      for ( $t = 0; $t < 30; $t++ ) {
                                            $tablica[] = array('id' => $t, 'text' => $t . ' px');
                                      }
                                      echo Funkcje::RozwijaneMenu('txt_odstep_tla_linia_' . $c, $tablica, ((isset($konfig['txt_odstep_tla_linia_' . $c])) ? $konfig['txt_odstep_tla_linia_' . $c] : ''), ' id="odstep_tla_linia_' . $c . '" class="ZmianaWybor"');
                                      unset($tablica);
                                      ?>
                                    </p>         

                                    <p>
                                      <label for="grubosc_ramki_tla_linia_<?php echo $c; ?>">Grubość ramki tła:</label>             
                                      <?php
                                      $tablica = array();
                                      for ( $t = 0; $t < 10; $t++ ) {
                                            $tablica[] = array('id' => $t, 'text' => $t . ' px');
                                      }
                                      echo Funkcje::RozwijaneMenu('txt_grubosc_ramki_linia_' . $c, $tablica, ((isset($konfig['txt_grubosc_ramki_linia_' . $c])) ? $konfig['txt_grubosc_ramki_linia_' . $c] : ''), ' id="grubosc_ramki_linia_' . $c . '" class="ZmianaWybor"');
                                      unset($tablica);
                                      ?>
                                    </p>     
                                    
                                    <p>
                                      <label for="kolor_ramki_tla_linia_<?php echo $c; ?>">Kolor ramki tła:</label>
                                      <input name="txt_kolor_ramki_tla_linia_<?php echo $c; ?>" class="color {required:false}" id="kolor_ramki_tla_linia_<?php echo $c; ?>" style="-moz-box-shadow:none" value="<?php echo ((isset($konfig['txt_kolor_ramki_tla_linia_' . $c . ''])) ? $konfig['txt_kolor_ramki_tla_linia_' . $c . ''] : '#FFFFFF'); ?>" size="8" onchange="PokazPrzykladTekstu()" />                    
                                    </p>                                    
                                    
                                    <p>
                                      <label for="mobile_tekst_linia_<?php echo $c; ?>">Czy wyświetlać tą linię tekstu przy małych rozdzielczościach - <b>poniżej 1024px</b> ?</label>             
                                      <?php
                                      $tablica = array( array('id' => 'tak', 'text' => 'tak'),
                                                        array('id' => 'nie', 'text' => 'nie') );
                                                        
                                      echo Funkcje::RozwijaneMenu('txt_mobile_tekst_linia_' . $c, $tablica, ((isset($konfig['txt_mobile_tekst_linia_' . $c])) ? $konfig['txt_mobile_tekst_linia_' . $c] : 'tak'), ' id="mobile_tekst_linia_' . $c . '"');
                                      unset($tablica);
                                      ?>
                                    </p>
                                    
                                    <div class="PodTytulGrafiki">Wielkość czcionki tekstu dla poszczególnych rozdzielczości</div>

                                    <?php
                                    $rozdzielczosci = array(1200,1024,800,480,300);
                                    //
                                    for ( $tr = 0; $tr < count($rozdzielczosci); $tr++ ) { ?>
                                 
                                      <p>
                                        <label for="rozmiar_czcionki_<?php echo $rozdzielczosci[$tr]; ?>_linia_<?php echo $c; ?>">od <?php echo $rozdzielczosci[$tr]; ?>px<?php echo (($tr > 0) ?' do ' . $rozdzielczosci[$tr - 1] . 'px' : ''); ?>:</label>             
                                        <?php
                                        $tablica = array();
                                        for ( $t = 0; $t < 30; $t++ ) {
                                              $tablica[] = array('id' => (100 + ($t * 10)), 'text' => (100 + ($t * 10)) . '%');
                                        }
                                        echo Funkcje::RozwijaneMenu('txt_rozmiar_czcionki_' . $rozdzielczosci[$tr] . '_linia_' . $c, $tablica, ((isset($konfig['txt_rozmiar_czcionki_' . $rozdzielczosci[$tr] . '_linia_' . $c])) ? $konfig['txt_rozmiar_czcionki_' . $rozdzielczosci[$tr] . '_linia_' . $c] : ''), ' id="rozmiar_czcionki_' . $rozdzielczosci[$tr] . '_linia_' . $c . '" class="ZmianaWybor"');
                                        unset($tablica);
                                        ?>
                                      </p>
                                      
                                    <?php 
                                    } 
                                    //
                                    unset($rozdzielczosci);
                                    ?>                                    
                                
                                </div>

                            </div>
                            
                        </div>
                        
                        <?php } ?>
                        
                        <div class="cl"></div>
                        
                    </div>

                    <div class="TytulDzialu" style="margin:0px">Ustawienia ogólne tekstu</div>

                    <div class="UstawieniaTekstuWspolne">
                    
                        <p>
                          <label for="wyrownanie_tekstu">Wyrównanie tekstu w poziomie:</label>             
                          <?php
                          $tablica = array( array('id' => 'left', 'text' => 'do lewej strony'),
                                            array('id' => 'center', 'text' => 'wyśrodkowane'),
                                            array('id' => 'right', 'text' => 'do prawej strony'),
                                            array('id' => 'justify', 'text' => 'wyjustowane') );
                                            
                          echo Funkcje::RozwijaneMenu('txt_wyrownanie_tekstu', $tablica, ((isset($konfig['txt_wyrownanie_tekstu'])) ? $konfig['txt_wyrownanie_tekstu'] : ''), ' id="wyrownanie_tekstu" class="ZmianaWybor"');
                          unset($tablica);
                          ?>
                        </p>  

                        <p>
                          <label for="szerokosc_tla_pc">Szerokość pola tekstu dla rozdzielczości <b>powyżej 1024px</b>:</label>             
                          <?php
                          $tablica = array();
                          for ( $t = 3; $t < 11; $t++ ) {
                                $tablica[] = array('id' => ($t * 10), 'text' => ($t * 10) . '%');
                          }
                          echo Funkcje::RozwijaneMenu('txt_szerokosc_tla_pc', $tablica, ((isset($konfig['txt_szerokosc_tla_pc'])) ? $konfig['txt_szerokosc_tla_pc'] : ''), ' id="szerokosc_tla_pc" class="ZmianaWybor"');
                          unset($tablica);
                          ?>
                        </p>

                        <p>
                          <label for="szerokosc_tla_mobile">Szerokość pola tekstu dla rozdzielczości <b>poniżej 1024px</b>:</label>             
                          <?php
                          $tablica = array();
                          for ( $t = 3; $t < 11; $t++ ) {
                                $tablica[] = array('id' => ($t * 10), 'text' => ($t * 10) . '%');
                          }
                          echo Funkcje::RozwijaneMenu('txt_szerokosc_tla_mobile', $tablica, ((isset($konfig['txt_szerokosc_tla_mobile'])) ? $konfig['txt_szerokosc_tla_mobile'] : ''), ' id="szerokosc_tla_mobile"');
                          unset($tablica);
                          ?>
                        </p>                         

                        <p>
                          <label for="kolor_tla">Kolor tła:</label>
                          <input name="txt_kolor_tla" class="color {required:false}" id="kolor_tla" style="-moz-box-shadow:none" value="<?php echo ((isset($konfig['txt_kolor_tla'])) ? $konfig['txt_kolor_tla'] : ''); ?>" size="8" onchange="PokazPrzykladTekstu()" />                    
                          <em class="TipChmurka"><b>Usuń kolor</b><img onclick="$('#kolor_tla').val('');$('#kolor_tla').css('background-color','rgb(255, 255, 255)');PokazPrzykladTekstu()" style="cursor:pointer;" src="obrazki/kasuj.png" alt="Skasuj" /></em>
                        </p> 

                        <p>
                          <label for="przezroczystosc_tla">Przeźroczystość tła tekstu:</label>             
                          <?php
                          $tablica = array();
                          for ( $t = 1; $t < 11; $t++ ) {
                                $tablica[] = array('id' => ($t * 10), 'text' => ($t * 10) . '%');
                          }
                          echo Funkcje::RozwijaneMenu('txt_przezroczystosc_tla', $tablica, ((isset($konfig['txt_przezroczystosc_tla'])) ? $konfig['txt_przezroczystosc_tla'] : ''), ' id="przezroczystosc_tla" class="ZmianaWybor"');
                          unset($tablica);
                          ?>
                        </p>                     
                        
                        <p>
                          <label for="odstep_tla">Odstęp tła od tekstu:</label>             
                          <?php
                          $tablica = array();
                          for ( $t = 0; $t < 30; $t++ ) {
                                $tablica[] = array('id' => $t, 'text' => $t . ' px');
                          }
                          echo Funkcje::RozwijaneMenu('txt_odstep_tla', $tablica, ((isset($konfig['txt_odstep_tla'])) ? $konfig['txt_odstep_tla'] : ''), ' id="odstep_tla" class="ZmianaWybor"');
                          unset($tablica);
                          ?>
                        </p>         

                        <p>
                          <label for="grubosc_ramki_tla">Grubość ramki tła:</label>             
                          <?php
                          $tablica = array();
                          for ( $t = 0; $t < 10; $t++ ) {
                                $tablica[] = array('id' => $t, 'text' => $t . ' px');
                          }
                          echo Funkcje::RozwijaneMenu('txt_grubosc_ramki', $tablica, ((isset($konfig['txt_grubosc_ramki'])) ? $konfig['txt_grubosc_ramki'] : ''), ' id="grubosc_ramki" class="ZmianaWybor"');
                          unset($tablica);
                          ?>
                        </p>     
                        
                        <p>
                          <label for="kolor_ramki_tla">Kolor ramki tła:</label>
                          <input name="txt_kolor_ramki_tla" class="color {required:false}" id="kolor_ramki_tla" style="-moz-box-shadow:none" value="<?php echo ((isset($konfig['txt_kolor_ramki_tla'])) ? $konfig['txt_kolor_ramki_tla'] : '#FFFFFF'); ?>" size="8" onchange="PokazPrzykladTekstu()" />                    
                        </p>                        

                        <div>
                          <label>Położenie tekstu:</label>             
                          <div class="WyborGraficzny">
                          
                              <div class="GrafikaTlo">
                              
                                  <div style="top:10px;left:10px">
                                      <input class="ZmianaInput" type="radio" name="txt_polozenie_tekstu" id="polozenie_tekstu_1" value="top:0px;left:0px" <?php echo ((!isset($konfig['txt_polozenie_tekstu']) || (isset($konfig['txt_polozenie_tekstu']) && $konfig['txt_polozenie_tekstu'] == 'top:0px;left:0px')) ? 'checked="checked"' : ''); ?> /><label class="OpisForPustyLabel" for="polozenie_tekstu_1"></label>
                                  </div>
                                  <div style="top:10px;left:50%;transform:translate(-50%, 0%);">
                                      <input class="ZmianaInput" type="radio" name="txt_polozenie_tekstu" id="polozenie_tekstu_2" value="top:0px;left:50%;transform:translate(-50%,0%)" <?php echo ((isset($konfig['txt_polozenie_tekstu']) && $konfig['txt_polozenie_tekstu'] == 'top:0px;left:50%;transform:translate(-50%,0%)') ? 'checked="checked"' : ''); ?> /><label class="OpisForPustyLabel" for="polozenie_tekstu_2"></label>
                                  </div>
                                  <div style="top:10px;right:10px">
                                      <input class="ZmianaInput" type="radio" name="txt_polozenie_tekstu" id="polozenie_tekstu_3" value="top:0px;right:0px" <?php echo ((isset($konfig['txt_polozenie_tekstu']) && $konfig['txt_polozenie_tekstu'] == 'top:0px;right:0px') ? 'checked="checked"' : ''); ?> /><label class="OpisForPustyLabel" for="polozenie_tekstu_3"></label>
                                  </div>
                                  
                                  <div style="top:50%;left:10px;transform:translate(0%,-50%)">
                                      <input class="ZmianaInput" type="radio" name="txt_polozenie_tekstu" id="polozenie_tekstu_4" value="top:50%;left:0px;transform:translate(0%,-50%)" <?php echo ((isset($konfig['txt_polozenie_tekstu']) && $konfig['txt_polozenie_tekstu'] == 'top:50%;left:0px;transform:translate(0%,-50%)') ? 'checked="checked"' : ''); ?> /><label class="OpisForPustyLabel" for="polozenie_tekstu_4"></label>
                                  </div>       
                                  <div style="top:50%;left:50%;transform:translate(-50%,-50%)">
                                      <input class="ZmianaInput" type="radio" name="txt_polozenie_tekstu" id="polozenie_tekstu_5" value="top:50%;left:50%;transform:translate(-50%,-50%)" <?php echo ((isset($konfig['txt_polozenie_tekstu']) && $konfig['txt_polozenie_tekstu'] == 'top:50%;left:50%;transform:translate(-50%,-50%)') ? 'checked="checked"' : ''); ?> /><label class="OpisForPustyLabel" for="polozenie_tekstu_5"></label>
                                  </div>    
                                  <div style="top:50%;right:10px;transform:translate(0%,-50%)">
                                      <input class="ZmianaInput" type="radio" name="txt_polozenie_tekstu" id="polozenie_tekstu_6" value="top:50%;right:0px;transform:translate(0%,-50%)" <?php echo ((isset($konfig['txt_polozenie_tekstu']) && $konfig['txt_polozenie_tekstu'] == 'top:50%;right:0px;transform:translate(0%,-50%)') ? 'checked="checked"' : ''); ?> /><label class="OpisForPustyLabel" for="polozenie_tekstu_6"></label>
                                  </div>

                                  <div style="bottom:10px;left:10px">
                                      <input class="ZmianaInput" type="radio" name="txt_polozenie_tekstu" id="polozenie_tekstu_7" value="bottom:0px;left:0px" <?php echo ((isset($konfig['txt_polozenie_tekstu']) && $konfig['txt_polozenie_tekstu'] == 'bottom:0px;left:0px') ? 'checked="checked"' : ''); ?> /><label class="OpisForPustyLabel" for="polozenie_tekstu_7"></label>
                                  </div>
                                  <div style="bottom:10px;left:50%;transform:translate(-50%, 0%);">
                                      <input class="ZmianaInput" type="radio" name="txt_polozenie_tekstu" id="polozenie_tekstu_8" value="bottom:0px;left:50%;transform:translate(-50%,0%)" <?php echo ((isset($konfig['txt_polozenie_tekstu']) && $konfig['txt_polozenie_tekstu'] == 'bottom:0px;left:50%;transform:translate(-50%,0%)') ? 'checked="checked"' : ''); ?> /><label class="OpisForPustyLabel" for="polozenie_tekstu_8"></label>
                                  </div>
                                  <div style="bottom:10px;right:10px">
                                      <input class="ZmianaInput" type="radio" name="txt_polozenie_tekstu" id="polozenie_tekstu_9" value="bottom:0px;right:0px" <?php echo ((isset($konfig['txt_polozenie_tekstu']) && $konfig['txt_polozenie_tekstu'] == 'bottom:0px;right:0px') ? 'checked="checked"' : ''); ?> /><label class="OpisForPustyLabel" for="polozenie_tekstu_9"></label>
                                  </div>                              
                                  
                              </div>
                          
                          </div>
                        </div>
                        
                        <table class="MarginesyOdstep" style="margin-left:0px">
                            <tr><td><label>Dodatkowe marginesy pola z tekstem:</label></td>
                            <td>
                                <div class="TloOdstep">
                                    <div class="MargGora"><input type="number" min="0" max="150" name="txt_margines_gorny" id="margines_gorny" class="zero ZmianaWybor" value="<?php echo ((isset($konfig['txt_margines_gorny'])) ? (int)$konfig['txt_margines_gorny'] : '0'); ?>" size="4" /> px</div>
                                    <div class="MargDol"><input type="number" min="0" max="150" name="txt_margines_dolny" id="margines_dolny" class="zero ZmianaWybor" value="<?php echo ((isset($konfig['txt_margines_dolny'])) ? (int)$konfig['txt_margines_dolny'] : '0'); ?>" size="4" /> px</div>
                                    <div class="MargLewy"><input type="number" min="0" max="150" name="txt_margines_lewy" id="margines_lewy" class="zero ZmianaWybor" value="<?php echo ((isset($konfig['txt_margines_lewy'])) ? (int)$konfig['txt_margines_lewy'] : '0'); ?>" size="4" /> px</div>
                                    <div class="MargPrawy"><input type="number" min="0" max="150" name="txt_margines_prawy" id="margines_prawy" class="zero ZmianaWybor" value="<?php echo ((isset($konfig['txt_margines_prawy'])) ? (int)$konfig['txt_margines_prawy'] : '0'); ?>" size="4" /> px</div>
                                </div>
                            </td></tr>
                        </table>    

                        <p>
                          <label for="mobile_caly_tekst">Czy wyświetlać cały blok tekstu przy małych rozdzielczościach - <b>poniżej 1024px</b> ?</label>             
                          <?php
                          $tablica = array( array('id' => 'tak', 'text' => 'tak'),
                                            array('id' => 'nie', 'text' => 'nie') );
                                            
                          echo Funkcje::RozwijaneMenu('txt_mobile_caly_tekst', $tablica, ((isset($konfig['txt_mobile_caly_tekst'])) ? $konfig['txt_mobile_caly_tekst'] : 'tak'), ' id="txt_mobile_caly_tekst"');
                          unset($tablica);
                          ?>
                        </p>                        
                        
                    </div>

                </div>
                
                <?php } ?>

                <div class="przyciski_dolne">
                  <?php 
                  if (count($tablica_grup) > 0) { 
                  ?>
                  <input type="hidden" name="powrot" id="powrot" value="0" />
                  <input type="submit" class="przyciskNon" value="Zapisz dane" />
                  <input type="submit" class="przyciskNon" value="Zapisz dane i pozostań w edycji" onclick="$('#powrot').val(1)" />
                  <?php 
                  } 
                  unset($tablica_grup);
                  ?>
                  <button type="button" class="przyciskNon" onclick="cofnij('bannery_zarzadzanie','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','wyglad');">Powrót</button>   
                </div>            

                <?php

                unset($info);            
            
            } else {
            
                echo '<div class="pozycja_edytowana">Brak danych do wyświetlenia</div>';
            
            }
            
            $db->close_query($sql);
            unset($zapytanie);                    
            
            ?>              

            </div>                      
            </form>         
            
    </div>    
    
    <?php
    include('stopka.inc.php');

}
