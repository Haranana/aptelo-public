<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
    
        // jaki link zew
        $plik = '';
        if (!empty($_POST['link_tresc'])) {
            $plik = $filtr->process($_POST['link_tresc']);
        }
        if (!empty($_POST['plik'])) {
            $plik = $filtr->process($_POST['plik']);
        }    
    
        $pola = array(
                array('pages_image',$filtr->process($_POST['zdjecie'])),
                array('status','1'),
                array('pages_group',$filtr->process($_POST['grupa'])),
                array('pages_script', (int)$_POST['strona_skrypt']),
                array('link',(((int)$_POST['link'] == 0) ? '' : $plik)),
                array('nofollow',(int)$_POST['nofollow']),
                array('pages_more',(int)$_POST['rozwiniecie']),
                array('sort_order',( $_POST['sort'] == '' ? '1' : (int)$_POST['sort'])),
                array('pages_customers_group_id',((isset($_POST['grupa_klientow'])) ? implode(',', (array)$_POST['grupa_klientow']) : 0))
        );
   
        $sql = $db->insert_query('pages' , $pola);
        $id_dodanej_pozycji = $db->last_id_query();
        
        unset($pola, $plik);
        
        if ((int)$_POST['link'] == 0) {
        
            $ile_jezykow = Funkcje::TablicaJezykow();
            for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                //
                // jezeli nazwa w innym jezyku nie jest wypelniona
                if ( $w > 0 ) {
                    if (empty($_POST['nazwa_'.$w])) {
                        $_POST['nazwa_'.$w] = $_POST['nazwa_0'];
                    }
                }
                //            
                $pola = array(
                        array('pages_id',$id_dodanej_pozycji),
                        array('language_id',$ile_jezykow[$w]['id']),
                        array('pages_title',$filtr->process($_POST['nazwa_'.$w])),
                        array('pages_short_text',$filtr->process($_POST['opis_krotki_'.$w])),
                        array('pages_text', (($_POST['strona_skrypt'] == '1') ? $_POST['opis_script_'.$w] : $filtr->process($_POST['opis_'.$w]))),                     
                        array('meta_title_tag',$filtr->process($_POST['tytul_meta_'.$w])),      
                        array('meta_desc_tag',$filtr->process($_POST['opis_meta_'.$w])),
                        array('meta_keywords_tag',$filtr->process($_POST['slowa_meta_'.$w])));           
                $sql = $db->insert_query('pages_description' , $pola);
                unset($pola);
                
            }

            unset($ile_jezykow);

        } else {
        
            $ile_jezykow = Funkcje::TablicaJezykow();
            for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                //
                // jezeli nazwa w innym jezyku nie jest wypelniona
                if ( $w > 0 ) {
                    if (empty($_POST['nazwa_'.$w])) {
                        $_POST['nazwa_'.$w] = $_POST['nazwa_0'];
                    }
                }
                //            
                $pola = array(
                        array('pages_id',$id_dodanej_pozycji),
                        array('language_id',$ile_jezykow[$w]['id']),
                        array('pages_title',$filtr->process($_POST['nazwa_'.$w])));           
                $sql = $db->insert_query('pages_description' , $pola);
                unset($pola);
                
            }

            unset($ile_jezykow);        
        
        }
        
        if ( isset($_GET['grupa']) && ( $_GET['grupa'] != $filtr->process($_POST['grupa']) ) ) {
             $_GET['grupa'] = $filtr->process($_POST['grupa']);
        }      

        if (isset($id_dodanej_pozycji) && $id_dodanej_pozycji > 0) {
          
            if ( isset($_POST['powrot']) && (int)$_POST['powrot'] == 1 ) {
                //            
                Funkcje::PrzekierowanieURL('strony_informacyjne_edytuj.php?id_poz='.$id_dodanej_pozycji);
                //
              } else {
                //
                Funkcje::PrzekierowanieURL('strony_informacyjne.php?id_poz='.$id_dodanej_pozycji);
                //
            }             

        } else {
          
            Funkcje::PrzekierowanieURL('strony_informacyjne.php');
            
        }        

    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>

    <div id="naglowek_cont">Dodawanie pozycji</div>
    <div id="cont">

          <form action="strony_informacyjne/strony_informacyjne_dodaj.php" method="post" id="poForm" class="cmxform"> 
          
          <div class="poleForm">
            <div class="naglowek">Dodawanie danych</div>
            
                <input type="hidden" name="akcja" value="zapisz" />

                <?php $ile_jezykow = Funkcje::TablicaJezykow(); ?>

                <script>
                $(document).ready(function() {
                    $("#poForm").validate({
                      rules: {
                        nazwa_0: {
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
                
                function zmien_link(id) {
                    if (id == 1) {
                        $('#zakl_link_1').css('display','none'); 
                        $('#zakl_link_2').css('display','none'); 
                        $('.edytor').slideUp('fast'); 
                        $('#edytor').slideUp('fast'); 
                        $('#link_adres').slideDown();
                        $('#forma_kodu').slideUp('fast'); 
                        $('#foto_strona').slideUp('fast'); 
                       } else { 
                        $('#zakl_link_1').css('display','block');
                        $('#zakl_link_2').css('display','block');
                        $('#link_adres').slideUp();                          
                        $('.edytor').slideDown(); 
                        $('#edytor').slideDown(); 
                        $('#forma_kodu').slideDown(); 
                        if ( $('#strona_skrypt_tak').prop('checked') == true ) {
                             $('#foto_strona').slideUp('fast'); 
                        } else {
                            $('#foto_strona').slideDown();
                        }
                    }     
                }     

                function linkz(pole) {
                    if (pole == 1) {
                        $('#plik2').val(0);
                    }
                    if (pole == 2) {
                        $('#plik1').val('');
                    }
                }      
                function zmien_rwd(id) {
                    if (id == 0) {
                        $('#wyglad_rwd').slideUp();
                       } else {
                        $('#wyglad_rwd').slideDown();
                    }
                }  
                
                function zmien_tryb_edytora(id) {
                    if (id == 0) {
                        $('#edytor_tekst').hide();
                        $('#rozwijanie').hide();
                        $('#edytor_skrypt').slideDown();
                        $('#foto_strona').slideUp('fast'); 
                       } else {
                        $('#edytor_tekst').slideDown();
                        $('#rozwijanie').slideDown();
                        $('#edytor_skrypt').hide();
                        $('#foto_strona').slideDown(); 
                    }
                }                     
                </script> 
                
                <div id="ZakladkiEdycji">
                
                    <div id="LeweZakladki">
                    
                        <a href="javascript:gold_tabs_horiz('0','0','opis_')" class="a_href_info_zakl" id="zakl_link_0">Podstawowe dane</a>   
                        <a href="javascript:gold_tabs_horiz('1','100','opis_krotki_')" class="a_href_info_zakl" id="zakl_link_1">Tekst skrócony</a>
                        <a href="javascript:gold_tabs_horiz('2','200')" class="a_href_info_zakl" id="zakl_link_2">Pozycjonowanie</a>
                        
                    </div>

                    <div id="PrawaStrona">
                    
                        <div id="zakl_id_0" style="display:none;">

                            <p>
                              <label for="grupa">Grupa:</label>             
                              <?php
                              $zapytanie_tmp = "SELECT * FROM pages_group ORDER BY pages_group_code ASC";
                              $sqls = $db->open_query($zapytanie_tmp);
                              //
                              $tablica = array();
                              $tablica[] = array('id' => '', 'text' => 'Strona nie przypisana do żadnej grupy');
                              while ($infs = $sqls->fetch_assoc()) { 
                                $tablica[] = array('id' => $infs['pages_group_code'], 'text' => $infs['pages_group_code'] . ' - ' . $infs['pages_group_title']);
                              }
                              $db->close_query($sqls); 
                              unset($zapytanie_tmp, $infs);                   
                              
                              echo Funkcje::RozwijaneMenu('grupa', $tablica, '', 'id="grupa" style="width:400px" '); 
                              ?><em class="TipIkona"><b>Wybierz grupę, jeżeli strona my być wyświetlana w niestandardowym miejscu. W innym przypadku można ją przypisać w definiowaniu wyglądu sklepu</b></em>
                            </p>
                            
                            <div id="link_sort">

                                <p>
                                    <label for="sort">Kolejność wyswietlania:</label>
                                    <input type="text" name="sort" size="5" value="1" id="sort" />
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
                                
                                <div class="ostrzezenie" style="margin:0px 15px 10px 25px">Jeżeli nie zostanie wybrana żadna grupa klientów to strona będzie widoczna dla wszystkich klientów.</div>
                                
                            </div>
                            
                            <div id="link_zew">

                                <p>
                                    <label>Czy strona będzie linkiem zewnętrznym:</label>
                                    <input type="radio" value="1" name="link" id="link_zew_tak" onclick="zmien_link(1)" /><label class="OpisFor" for="link_zew_tak">tak<em class="TipIkona"><b>Strona nie będzie zawierała żadnej treści tylko będzie linkiem do innej strony</b></em></label>
                                    <input type="radio" value="0" name="link" id="link_zew_nie" onclick="zmien_link(0)" checked="checked" /><label class="OpisFor" for="link_zew_nie">nie<em class="TipIkona"><b>Strona będzie zawierała treść</b></em></label>
                                </p> 
                                
                            </div>

                            <div id="link_adres" style="display:none">
                            
                                <p>
                                    <label for="plik2">Link wewnętrzny sklepu:</label>
                                    <?php
                                    $tablica = array();
                                    $tablica[] = array('id' => 0, 'text' => '... wybierz plik ...');
                                    //
                                    $linia = file("strony_informacyjne/pliki_wewnetrzne.ddt");
                                    for ($i = 0; $i < count($linia); $i++) {        
                                        $wartosc = explode(';', (string)$linia[$i]);
                                        //
                                        if (isset($wartosc) && count($wartosc) > 1) {
                                            //
                                            $tablica[] = array('id' => trim((string)$wartosc[1]), 'text' => trim((string)$wartosc[0]));
                                            //
                                        }
                                    }   
                                    echo Funkcje::RozwijaneMenu('plik', $tablica, '', ' onchange="linkz(2)" id="plik2" style="width:330px"');
                                    ?>
                                </p>
                                
                                <p>
                                    <label for="plik1">Link zewnętrzny z http:</label>
                                    <input type="text" name="link_tresc" onchange="linkz(1)" id="plik1" value="" size="90" />
                                </p>  
                                
                            </div>                            

                            <p>
                                <label>Stosować przy tej stronie atrybut <b>noindex</b> ?</label>
                                <input type="radio" value="1" name="nofollow" id="nofollow_tak" /><label class="OpisFor" for="nofollow_tak">tak<em class="TipIkona"><b>Strona NIE będzie indeksowana przez wyszukiwarki</b></em></label>
                                <input type="radio" value="0" name="nofollow" id="nofollow_nie" checked="checked" /><label class="OpisFor" for="nofollow_nie">nie<em class="TipIkona"><b>Strona będzie indeksowana przez wyszukiwarki</b></em></label>
                            </p>    

                            <p id="forma_kodu">
                                <label>Czy strona będzie w formie kodu ?<em class="TipIkona"><b>Strona nie będzie w formie tekstu wpisywanego w edytorze, tylko jej treść będzie generowana przez skrypt - np regulamin z zewnętrznych serwisów</b></em></label>
                                <input type="radio" value="1" name="strona_skrypt" onclick="zmien_tryb_edytora(0)" id="strona_skrypt_tak" /><label class="OpisFor" for="strona_skrypt_tak">tak</label>
                                <input type="radio" value="0" name="strona_skrypt" onclick="zmien_tryb_edytora(1)" id="strona_skrypt_nie" checked="checked" /><label class="OpisFor" for="strona_skrypt_nie">nie</label>
                            </p>                             
                            
                            <p id="rozwijanie">
                                <label>Czy pełny tekst ma być rozwijany w module ?<em class="TipIkona"><b>Jeżeli strona będzie modułem i zostanie zaznaczona opcja TAK, to pod skróconym tekstem nie będzie odnośnika do calego artykułu tylko tekst można będzie rozwinąć na stronie</b></em></label>
                                <input type="radio" value="1" name="rozwiniecie" id="rozwijanie_tak" /><label class="OpisFor" for="rozwijanie_tak">tak</label>
                                <input type="radio" value="0" name="rozwiniecie" id="rozwijanie_nie" checked="checked" /><label class="OpisFor" for="rozwijanie_nie">nie</label>
                            </p>                               
                            
                            <div id="foto_strona">

                                <p>
                                  <label for="foto">Ścieżka zdjęcia:</label>           
                                  <input type="text" name="zdjecie" size="95" value="" class="obrazek" ondblclick="openFileBrowser('foto','','<?php echo KATALOG_ZDJEC; ?>')" id="foto" autocomplete="off" /><em class="TipIkona"><b>Kliknij dwukrotnie w pole obok żeby otworzyć okno przeglądarki zdjęć</b></em>
                                  <span class="usun_zdjecie TipChmurka" data-foto="foto"><b>Kliknij w ikonę żeby usunąć przypisane zdjęcie</b></span>
                                  <span class="PrzegladarkaZdjec TipChmurka" onclick="openFileBrowser('foto','','<?php echo KATALOG_ZDJEC; ?>')"><b>Kliknij żeby otworzyć okno przeglądarki zdjęć</b></span>
                                </p>      

                                <div id="divfoto" style="padding-left:10px; display:none">
                                    <label>Zdjęcie:</label>
                                    <span id="fofoto">
                                        <span class="zdjecie_tbl">
                                            <img src="obrazki/_loader_small.gif" alt="" />
                                        </span>
                                    </span>  
                                </div>
                                
                                <div class="maleInfo" style="margin:0px 15px 10px 25px">Zdjęcie jest wykorzystywane w szablonach v2 w module wyświetlającym strony z określonej grupy stron.</div>
                            
                            </div>

                            <div class="info_tab">
                            <?php
                            for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                                echo '<span id="link_'.$w.'" class="a_href_info_tab" onclick="gold_tabs(\''.$w.'\',\'opis_\',400)">'.$ile_jezykow[$w]['text'].'</span>';
                            }                    
                            ?>                   
                            </div>
                            
                            <div style="clear:both"></div>
                            
                            <div class="info_tab_content">
                                <?php
                                for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                                    ?>
                                    
                                    <div id="info_tab_id_<?php echo $w; ?>" style="display:none;">
                                    
                                        <p>
                                           <?php if ($w == '0') { ?>
                                            <label class="required tytul_zmiana" for="nazwa_0">Tytuł strony:</label>
                                            <input type="text" name="nazwa_<?php echo $w; ?>" size="65" value="" id="nazwa_0" />
                                           <?php } else { ?>
                                            <label class="tytul_zmiana" for="nazwa_<?php echo $w; ?>">Tytuł strony:</label>   
                                            <input type="text" name="nazwa_<?php echo $w; ?>" id="nazwa_<?php echo $w; ?>" size="65" value="" />
                                           <?php } ?>
                                        </p>      

                                        <div id="edytor">
                                    
                                            <div id="edytor_tekst">
                                              <div class="edytor">
                                                <textarea cols="50" rows="30" id="opis_<?php echo $w; ?>" name="opis_<?php echo $w; ?>"></textarea>
                                              </div>      
                                            </div>
                                            
                                            <div id="edytor_skrypt" class="EdytorSkrypt">
                                              <div class="maleInfo" style="margin-left:0px">W oknie poniżej wklej kod, który ma być wyświetlany na stronie</div>
                                              <textarea cols="100" rows="20" id="opis_script_<?php echo $w; ?>" name="opis_script_<?php echo $w; ?>"></textarea>
                                            </div>        

                                        </div>

                                    </div>
                                    <?php                    
                                }                    
                                ?>                      
                            </div>
                            
                        </div>
                            
                        <div id="zakl_id_1" style="display:none;">

                            <div class="info_tab">
                            <?php
                            for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                                echo '<span id="link_'.($w+100).'" class="a_href_info_tab" onclick="gold_tabs(\''.($w+100).'\',\'opis_krotki_\',400)">'.$ile_jezykow[$w]['text'].'</span>';
                            }                    
                            ?>                   
                            </div>
                            
                            <div style="clear:both"></div>
                            
                            <div class="info_tab_content">
                                <?php
                                for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                                    ?>
                                    
                                    <div id="info_tab_id_<?php echo ($w + 100); ?>" style="display:none;">
                                    
                                        <div class="edytor">
                                          <textarea cols="50" rows="30" id="opis_krotki_<?php echo ($w + 100); ?>" name="opis_krotki_<?php echo $w; ?>"></textarea>
                                        </div>                            

                                    </div>
                                    <?php                    
                                }                    
                                ?>                      
                            </div>
                            
                        </div>                        
                        
                        <div id="zakl_id_2" style="display:none;">
                        
                            <div class="info_tab">
                            <?php
                            for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                                echo '<span id="link_'.($w+200).'" class="a_href_info_tab" onclick="gold_tabs(\''.($w+200).'\')">'.$ile_jezykow[$w]['text'].'</span>';
                            }                    
                            ?>                   
                            </div>
                            
                            <div style="clear:both"></div>
                            
                            <div class="info_tab_content">
                                <?php
                                for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                                    ?>
                                    
                                    <div id="info_tab_id_<?php echo ($w + 200); ?>" style="display:none;">                        

                                        <p>
                                          <label for="tytul_meta_<?php echo $w; ?>">Meta Tagi - Tytuł:</label>
                                          <textarea name="tytul_meta_<?php echo $w; ?>" id="tytul_meta_<?php echo $w; ?>" onkeyup="licznik_znakow_meta(this,'iloscZnakowNazwa_<?php echo $w; ?>')" rows="4" cols="70"></textarea>
                                        </p> 
                                        
                                        <p class="LicznikMeta">
                                          <label></label>
                                          Ilość wpisanych znaków: <span class="iloscZnakow" id="iloscZnakowNazwa_<?php echo $w; ?>">0</span>
                                          zalecana maksymalna ilość: <span class="iloscZnakow" id="iloscZnakowNazwa_<?php echo $w; ?>Max"><?php echo DLUGOSC_META_NAZWA; ?></span>
                                        </p>                                           
                                        
                                        <p>
                                          <label for="opis_meta_<?php echo $w; ?>">Meta Tagi - Opis:</label>
                                          <textarea name="opis_meta_<?php echo $w; ?>" id="opis_meta_<?php echo $w; ?>" onkeyup="licznik_znakow_meta(this,'iloscZnakowOpis_<?php echo $w; ?>')" rows="4" cols="70"></textarea>
                                        </p> 

                                        <p class="LicznikMeta">
                                          <label></label>
                                          Ilość wpisanych znaków: <span class="iloscZnakow" id="iloscZnakowOpis_<?php echo $w; ?>">0</span>
                                          zalecana maksymalna ilość: <span class="iloscZnakow" id="iloscZnakowOpis_<?php echo $w; ?>Max"><?php echo DLUGOSC_META_OPIS; ?></span>
                                        </p>                                          
                                        
                                        <p>
                                          <label for="slowa_meta_<?php echo $w; ?>">Meta Tagi - Słowa kluczowe:</label>
                                          <textarea name="slowa_meta_<?php echo $w; ?>" id="slowa_meta_<?php echo $w; ?>" onkeyup="licznik_znakow_meta(this,'iloscZnakowSlowa_<?php echo $w; ?>')" rows="4" cols="70"></textarea>
                                        </p>    
                                        
                                        <p class="LicznikMeta">
                                          <label></label>
                                          Ilość wpisanych znaków: <span class="iloscZnakow" id="iloscZnakowSlowa_<?php echo $w; ?>">0</span>
                                          zalecana maksymalna ilość: <span class="iloscZnakow" id="iloscZnakowSlowa_<?php echo $w; ?>Max"><?php echo DLUGOSC_META_SLOWA; ?></span>
                                        </p>                                           
                            
                                    </div>
                                    <?php                    
                                }                    
                                ?>                      
                            </div>
                            
                        </div>                        
                        
                    </div>
                
                </div>
                
                <script>
                gold_tabs_horiz('0','0','opis_');
                </script>            
            
          </div>
          
          <br />
             
          <div class="przyciski_dolne">
              <input type="hidden" name="powrot" id="powrot" value="0" />
              <input type="submit" class="przyciskNon" value="Zapisz dane" />
              <input type="submit" class="przyciskNon" value="Zapisz dane i pozostań w edycji" onclick="$('#powrot').val(1)" />  
              <button type="button" class="przyciskNon" onclick="cofnij('strony_informacyjne','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>');">Powrót</button>    
          </div>           

          </form>

    </div>
    
    <?php
    include('stopka.inc.php');    
    
} ?>
