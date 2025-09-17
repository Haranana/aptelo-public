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
        
        $pola = array(array('status','1'),
                      array('banners_title',$filtr->process($_POST['nazwa'])),
                      array('banners_group',$filtr->process($_POST['grupa'])),
                      array('languages_id',$filtr->process($_POST["jezyk"])),                      
                      array('date_added','now()'));
                      
        // jezeli banner to kod html
        if ($_POST['tryb'] == 'html') {
            $pola[] = array('banners_html_text',$filtr->process($_POST['text_html']));
        }   

        // jezeli banner to obraz
        if ($_POST['tryb'] == 'obraz') {
            $pola[] = array('banners_url',$filtr->process($_POST['adres']));
            $pola[] = array('banners_url_blank',(int)$_POST['nowe_okno']);
            $pola[] = array('banners_image',$filtr->process($_POST['zdjecie']));
            $pola[] = array('banners_image_text',$filtr->process($_POST['text']));
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
        
        $pola[] = array('banners_customers_group_id',((isset($_POST['grupa_klientow'])) ? implode(',', (array)$_POST['grupa_klientow']) : 0));
        
        $sql = $db->insert_query('banners', $pola);
        $id_dodanej_pozycji = $db->last_id_query();
        unset($pola); 

        if ( isset($_SESSION['filtry']['bannery_zarzadzanie.php']['grupa']) && ( $_SESSION['filtry']['bannery_zarzadzanie.php']['grupa'] != $filtr->process($_POST['grupa']) ) ) {
          
             unset($_SESSION['filtry']['bannery_zarzadzanie.php']);
             $_SESSION['filtry']['bannery_zarzadzanie.php']['grupa'] = $filtr->process($_POST['grupa']);
             
        }        

        if (isset($id_dodanej_pozycji) && $id_dodanej_pozycji > 0) {
            if ( isset($_POST['powrot']) && (int)$_POST['powrot'] == 1 ) {
                //            
                Funkcje::PrzekierowanieURL('bannery_zarzadzanie_edytuj.php?id_poz='.$id_dodanej_pozycji . Funkcje::Zwroc_Get(array('id_poz','x','y'),true));
                //
              } else {        
                //
                Funkcje::PrzekierowanieURL('bannery_zarzadzanie.php?id_poz='.$id_dodanej_pozycji . Funkcje::Zwroc_Get(array('id_poz','x','y'),true));
                //
            }            
        } else {
            Funkcje::PrzekierowanieURL('bannery_zarzadzanie.php');
        }

    }   

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Dodawanie pozycji</div>
    <div id="cont">

          <form action="wyglad/bannery_zarzadzanie_dodaj.php<?php echo ((isset($_GET['grupa'])) ? '?grupa='.$_GET['grupa'] : ''); ?>" method="post" id="ppForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Dodawanie danych</div>
            
            <div class="pozycja_edytowana">
            
                <div class="info_content">
            
                <input type="hidden" name="akcja" value="zapisz" />
                
                <script type="text/javascript" src="javascript/jquery.bestupper.min.js"></script>
                
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
                  <input type="text" name="nazwa" id="nazwa" value="" size="50" /><em class="TipIkona"><b>Nazwa banneru - tekst wyświetlany po najechaniu kursorem myszy na obrazek banneru</b></em>
                </p> 

                <p>
                  <label for="grupa">Grupa:</label>             
                  <?php
                  $zapytanie_tmp = "select distinct * from banners_group order by banners_group_code asc";
                  $sqls = $db->open_query($zapytanie_tmp);
                  //
                  $tablica = array();
                  while ($infs = $sqls->fetch_assoc()) { 
                    $tablica[] = array('id' => $infs['banners_group_code'], 'text' => $infs['banners_group_code'] . ' - ' . $infs['banners_group_title']);
                  }
                  $db->close_query($sqls); 
                  unset($zapytanie_tmp, $infs);                   
                  
                  echo Funkcje::RozwijaneMenu('grupa', $tablica, '', 'style="width:400px" id="grupa"'); 
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
                  echo Funkcje::RozwijaneMenu('jezyk',$tablica_jezykow,0, 'id="jezyk"');
                  ?>                  
                </p>            

                <table class="WyborCheckbox">
                    <tr>
                        <td><label>Widoczna dla grupy klientów:</label></td>
                        <td>
                            <?php                        
                            $TablicaGrupKlientow = Klienci::ListaGrupKlientow(false);
                            foreach ( $TablicaGrupKlientow as $GrupaKlienta ) {
                                echo '<input type="checkbox" value="' . $GrupaKlienta['id'] . '" name="grupa_klientow[]" id="grupa_klientow_' . $GrupaKlienta['id'] . '" /><label class="OpisFor" for="grupa_klientow_' . $GrupaKlienta['id'] . '">' . $GrupaKlienta['text'] . '</label><br />';
                            }               
                            unset($TablicaGrupKlientow);
                            ?>
                        </td>
                    </tr>
                </table> 
                
                <div class="ostrzezenie" style="margin:0px 15px 10px 10px">Jeżeli nie zostanie wybrana żadna grupa klientów to banner będzie widoczny dla wszystkich klientów.</div>
                                                
                <p>
                  <label>Banner będzie obrazkiem czy będzie to kod HTML ?</label>
                  <input type="radio" value="obraz" name="tryb" id="forma_obrazek" onclick="zmien_tryb(0)" checked="checked" /><label class="OpisFor" for="forma_obrazek">w formie obrazka<em class="TipIkona"><b>Banner będzie obrazkiem statycznym</b></em></label>
                  <input type="radio" value="html" name="tryb" id="forma_kod" onclick="zmien_tryb(1)" /><label class="OpisFor" for="forma_kod">jako kod HTML<em class="TipIkona"><b>Banner będzie generowany przez kod HTML</b></em></label>
                </p>                  
    
                <div id="tryb_0">
                    <p>
                      <label for="adres">Adres URL:</label>
                      <input type="text" name="adres" id="adres" value="" size="50" /><em class="TipIkona"><b>Adres strony do jakiej ma kierować banner</b></em>
                    </p>    
                    
                    <p>
                      <label>Czy link ma być otwierany w nowym oknie ?</label>
                      <input type="radio" value="0" name="nowe_okno" id="nowe_okno_nie" checked="checked" /><label class="OpisFor" for="nowe_okno_nie">nie</label>
                      <input type="radio" value="1" name="nowe_okno" id="nowe_okno_tak" /><label class="OpisFor" for="nowe_okno_tak">tak</label>
                    </p>                        

                    <p>
                      <label>Rodzaj banneru:</label>
                      <input type="radio" value="grafika" name="rodzaj_banneru" id="rodzaj_banneru_grafika" onclick="film_grafika('grafika')" checked="checked" /><label class="OpisFor" for="rodzaj_banneru_grafika">grafika (jpg, png, gif, webp)</label>
                      <input type="radio" value="film" name="rodzaj_banneru" id="rodzaj_banneru_film" onclick="film_grafika('film')" /><label class="OpisFor" for="rodzaj_banneru_film">film (mp4)</label>
                    </p> 
                        
                    <p>
                      <label class="required" for="foto">Ścieżka obrazka:</label>           
                      <input type="text" name="zdjecie" size="95" value="" ondblclick="openFileBrowser('foto','','<?php echo KATALOG_ZDJEC; ?>')" id="foto" autocomplete="off" /><em class="TipIkona"><b>Kliknij dwukrotnie w pole obok żeby otworzyć okno przeglądarki zdjęć</b></em>
                      <span class="PrzegladarkaZdjec TipChmurka" onclick="openFileBrowser('foto','','<?php echo KATALOG_ZDJEC; ?>')"><b>Kliknij żeby otworzyć okno przeglądarki zdjęć</b></span>
                    </p> 

                    <div id="UstawieniaFilmu" style="display:none">
                    
                        <p>
                            <label for="film_maksymalna_szerokosc">Maksymalna szerokość filmu w px:</label>
                            <input type="text" name="film_maksymalna_szerokosc" size="10" value="" class="calkowita" />
                        </p>  

                        <p>
                            <label for="film_maksymalna_wysokosc">Maksymalna wysokość filmu w px:</label>
                            <input type="text" name="film_maksymalna_wysokosc" size="10" value="" class="calkowita" />
                        </p>   

                        <p>
                          <label>Czy wyświetlać przyciski kontrolne (play / stop / dźwięk) ?</label>
                          <input type="radio" value="nie" name="film_nawigacja" id="film_nawigacja_nie" checked="checked" /><label class="OpisFor" for="film_nawigacja_nie">nie</label>
                          <input type="radio" value="tak" name="film_nawigacja" id="film_nawigacja_tak" /><label class="OpisFor" for="film_nawigacja_tak">tak</label>
                        </p>   

                        <p>
                          <label>Czy wyciszyć dźwięk ?</label>
                          <input type="radio" value="nie" name="film_dzwiek" id="film_dzwiek_nie" /><label class="OpisFor" for="film_dzwiek_nie">nie</label>
                          <input type="radio" value="tak" name="film_dzwiek" id="film_dzwiek_tak" checked="checked" /><label class="OpisFor" for="film_dzwiek_tak">tak</label>
                        </p>  

                        <p>
                          <label>Czy uruchomić film od razu po wyświetlaniu strony ?</label>
                          <input type="radio" value="nie" name="film_autostart" id="film_autostart_nie" /><label class="OpisFor" for="film_autostart_nie">nie</label>
                          <input type="radio" value="tak" name="film_autostart" id="film_autostart_tak" checked="checked" /><label class="OpisFor" for="film_autostart_tak">tak</label>
                        </p>                              
                    
                        <p>
                          <label>Czy film wyświetlać w zapętleniu ?</label>
                          <input type="radio" value="nie" name="film_zapetlenie" id="film_zapetlenie_nie" /><label class="OpisFor" for="film_zapetlenie_nie">nie</label>
                          <input type="radio" value="tak" name="film_zapetlenie" id="film_zapetlenie_tak" checked="checked" /><label class="OpisFor" for="film_zapetlenie_tak">tak</label>
                        </p>  
                        
                    </div> 

                    <div id="UstawieniaGrafiki">

                        <p>
                            <label for="text">Dodatkowy tekst:</label>
                            <textarea cols="62" rows="4" name="text" id="text"></textarea><em class="TipIkona"><b>Tekst który może się wyświetlać na bannerze - opcja używana przy animowanym module bannerów</b></em> 
                        </p> 
                        
                    </div>
                    
                </div>

                <div id="tryb_1" style="display:none">
                
                    <p>
                        <label for="text_html">Wstaw kod:</label>
                        <textarea cols="120" rows="15" name="text_html" id="text_html"></textarea>
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
                    <input type="text" name="data_banneru_od" id="data_banneru_od" value="" size="20"  class="datepicker" />
                </p>
                
                <p>
                    <label for="data_banneru_do">Data zakończenia:</label>
                    <input type="text" name="data_banneru_do" id="data_banneru_do" value="" size="20" class="datepicker" />
                </p>                
                
                <p>
                    <label>Wyświetlany tylko dla kategorii: <em class="TipIkona"><b>Banner będzie wyświetlany tylko w określonych kategoriach produktów - dla modułów bannerów widocznych na podstronach sklepu</b></em></label>
                </p>                 

                <div id="drzewo" class="OknoKategorie">
                    <?php
                    echo '<table class="pkc">';
                    //
                    $tablica_kat = Kategorie::DrzewoKategorii('0', '', '', '', false, true);
                    for ($w = 0, $c = count($tablica_kat); $w < $c; $w++) {
                        $podkategorie = false;
                        if ($tablica_kat[$w]['podkategorie'] == 'true') { $podkategorie = true; }
                        //                             
                        echo '<tr>
                                <td class="lfp"><input type="checkbox" value="'.$tablica_kat[$w]['id'].'" name="id_kat[]" id="id_kat_' . $tablica_kat[$w]['id'] . '" /> <label class="OpisFor" for="id_kat_' . $tablica_kat[$w]['id'] . '">'.$tablica_kat[$w]['text'].(($tablica_kat[$w]['status'] == 0) ? '<span class="wylKat TipChmurka"><b>Kategoria jest nieaktywna</b></span>' : '').'</label></td>
                                <td class="rgp" '.(($podkategorie) ? 'id="img_'.$tablica_kat[$w]['id'].'"' : '').'>'.(($podkategorie) ? '<img src="obrazki/rozwin.png" alt="Rozwiń" onclick="podkat(\''.$tablica_kat[$w]['id'].'\',\'\',\'checkbox\')" />' : '').'</td>
                              </tr>
                              '.(($podkategorie) ? '<tr><td colspan="2"><div id="p_'.$tablica_kat[$w]['id'].'"></div></td></tr>' : '').'';
                    }
                    echo '</table>';
                    unset($tablica_kat);                                
                    //
                    ?>      
                </div>                

                </div>             
               
            </div>

            <div class="przyciski_dolne">
              <?php 
              if (count($tablica) > 0) { 
              ?>
              <input type="hidden" name="powrot" id="powrot" value="0" />
              <input type="submit" class="przyciskNon" value="Zapisz dane" />
              <input type="submit" class="przyciskNon" value="Zapisz dane i pozostań w edycji" onclick="$('#powrot').val(1)" />
              <?php 
              } 
              unset($tablica);
              ?>
              <button type="button" class="przyciskNon" onclick="cofnij('bannery_zarzadzanie','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','wyglad');">Powrót</button>   
            </div>            

          </div>                      
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}
?>