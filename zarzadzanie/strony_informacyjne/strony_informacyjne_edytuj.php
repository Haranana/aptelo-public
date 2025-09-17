<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
    
        $id_strony = (int)$_POST['id'];
        
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
                array('link',(((int)$_POST['link'] == 0) ? '' : $plik)),
                array('pages_group',$filtr->process($_POST['grupa'])),
                array('pages_script', (int)$_POST['strona_skrypt']),
                array('nofollow',(int)$_POST['nofollow']),
                array('pages_more',(int)$_POST['rozwiniecie']),
                array('sort_order',( $_POST['sort'] == '' ? '1' : (int)$_POST['sort'])),
                array('pages_customers_group_id',((isset($_POST['grupa_klientow'])) ? implode(',', (array)$_POST['grupa_klientow']) : 0))
        );

        $sql = $db->update_query('pages' , $pola, " pages_id = '".$id_strony."'");        
        unset($pola, $plik);
        
        // kasuje rekordy w tablicy
        $db->delete_query('pages_description' , " pages_id = '".$id_strony."'");         
        
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
                        array('pages_id',$id_strony),
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
                        array('pages_id',$id_strony),
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

        if ( isset($_POST['powrot']) && (int)$_POST['powrot'] == 1 ) {
            //            
            Funkcje::PrzekierowanieURL('strony_informacyjne_edytuj.php?id_poz=' . (int)$id_strony . ((isset($_POST['zakladka']) && (int)$_POST['zakladka'] > 0) ? '&zakladka='.(int)$_POST['zakladka'] : ''));
            //
          } else {        
            //
            if ( isset($_POST['zakladka']) && (int)$_POST['zakladka'] > 0 ) {
              
                Funkcje::PrzekierowanieURL('/zarzadzanie/wyglad/wyglad.php?zakladka='.(int)$_POST['zakladka']);
              
              } else {
              
                Funkcje::PrzekierowanieURL('strony_informacyjne.php?id_poz='.$id_strony);
                
            }   
            //
        }

    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>

    <div id="naglowek_cont">Edycja pozycji</div>
    <div id="cont">

          <form action="strony_informacyjne/strony_informacyjne_edytuj.php" method="post" id="poForm" class="cmxform"> 
          
          <div class="poleForm">
            <div class="naglowek">Edycja danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select distinct * from pages where pages_id = '".(int)$_GET['id_poz']."'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {

                $info = $sql->fetch_assoc();  
                ?>             
            
                <input type="hidden" name="akcja" value="zapisz" />
                
                <input type="hidden" name="id" value="<?php echo $info['pages_id']; ?>" />
                
                <?php if (isset($_GET['zakladka']) && (int)$_GET['zakladka'] > 0 ) { ?>
                <input type="hidden" name="zakladka" value="<?php echo (int)$_GET['zakladka']; ?>" />
                <?php } ?>                

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
                        <?php if ($info['pages_modul'] == 0) { ?>
                        <a href="javascript:gold_tabs_horiz('2','200')" class="a_href_info_zakl" id="zakl_link_2">Pozycjonowanie</a>
                        <?php } ?>
                        
                    </div>

                    <div id="PrawaStrona">
                    
                        <div id="zakl_id_0" style="display:none;">

                            <p>
                              <label>Grupa:</label>             
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
                              
                              echo Funkcje::RozwijaneMenu('grupa', $tablica, $info['pages_group'], 'style="width:400px" '); 
                              ?><em class="TipIkona"><b>Wybierz grupę, jeżeli strona my być wyświetlana w niestandardowym miejscu. W innym przypadku można ją przypisać w definiowaniu wyglądu sklepu</b></em>
                            </p>

                            <p <?php echo (($info['pages_modul'] != 0) ? 'style="display:none"' : ''); ?>>
                                <label>Kolejność wyswietlania:</label>
                                <input type="text" name="sort" size="5" value="<?php echo $info['sort_order']; ?>" id="sort" />
                            </p>
                            
                            <table class="WyborCheckbox">
                                <tr>
                                    <td><label>Widoczna dla grupy klientów:</label></td>
                                    <td>
                                        <?php                        
                                        $TablicaGrupKlientow = Klienci::ListaGrupKlientow(false);
                                        foreach ( $TablicaGrupKlientow as $GrupaKlienta ) {
                                            echo '<input type="checkbox" value="' . $GrupaKlienta['id'] . '" name="grupa_klientow[]" id="grupa_klientow_' . $GrupaKlienta['id'] . '" ' . ((in_array((string)$GrupaKlienta['id'], explode(',', (string)$info['pages_customers_group_id']))) ? 'checked="checked" ' : '') . ' /> <label class="OpisFor" for="grupa_klientow_' . $GrupaKlienta['id'] . '">' . $GrupaKlienta['text'] . '</label><br />';
                                        }               
                                        unset($TablicaGrupKlientow);
                                        ?>
                                    </td>
                                </tr>
                            </table> 
                        
                            <div class="ostrzezenie" style="margin:0px 15px 10px 25px">Jeżeli nie zostanie wybrana żadna grupa klientów to strona będzie widoczna dla wszystkich klientów.</div>                         

                            <p <?php echo (($info['pages_modul'] != 0) ? 'style="display:none"' : ''); ?>>
                                <label>Czy strona będzie linkiem:</label>
                                <input type="radio" value="1" name="link" id="link_zew_tak" onclick="zmien_link(1)" <?php echo ((!empty($info['link'])) ? 'checked="checked"' : ''); ?> /> <label class="OpisFor" for="link_zew_tak">tak<em class="TipIkona"><b>Strona nie będzie zawierała żadnej treści tylko będzie linkiem do innej strony</b></em></label>
                                <input type="radio" value="0" name="link" id="link_zew_nie" onclick="zmien_link(0)" <?php echo ((empty($info['link'])) ? 'checked="checked"' : ''); ?> /> <label class="OpisFor" for="link_zew_nie">nie<em class="TipIkona"><b>Strona będzie zawierała treść</b></em></label>
                            </p>   

                            <div id="link_adres" style="display:none">
                            
                                <p>
                                    <label>Link wewnętrzny sklepu:</label>
                                    <?php
                                    $tablica = array();
                                    $tablica[] = array('id' => 0, 'text' => '... wybierz plik ...');
                                    $Bylo = false;
                                    //
                                    $linia = file("strony_informacyjne/pliki_wewnetrzne.ddt");
                                    for ($i = 0; $i < count($linia); $i++) {        
                                        $wartosc = explode(';', (string)$linia[$i]);
                                        //
                                        if (isset($wartosc) && count($wartosc) > 1) {
                                            //
                                            $tablica[] = array('id' => trim((string)$wartosc[1]), 'text' => trim((string)$wartosc[0]));
                                            //
                                            if (trim((string)$wartosc[1]) == $info['link']) {
                                                $Bylo = true;
                                            }
                                        }
                                    }   
                                    echo Funkcje::RozwijaneMenu('plik', $tablica, $info['link'], ' onchange="linkz(2)" id="plik2" style="width:330px"');
                                    ?>
                                </p>
                                
                                <p>
                                    <label>Link zewnętrzny z http:</label>
                                    <input type="text" name="link_tresc" onchange="linkz(1)" id="plik1" value="<?php echo (($Bylo == false) ? $info['link'] : ''); ?>" size="90" />
                                </p>  
                                
                            </div>
                            
                            <p>
                                <label>Stosować przy tej stronie atrybut <b>noindex</b> ?</label>
                                <input type="radio" value="1" name="nofollow" id="nofollow_tak" <?php echo ((!empty($info['nofollow'])) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="nofollow_tak">tak<em class="TipIkona"><b>Strona NIE będzie indeksowana przez wyszukiwarki</b></em></label>
                                <input type="radio" value="0" name="nofollow" id="nofollow_nie" <?php echo ((empty($info['nofollow'])) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="nofollow_nie">nie<em class="TipIkona"><b>Strona będzie indeksowana przez wyszukiwarki</b></em></label>
                            </p>     

                            <p id="forma_kodu">
                                <label>Czy strona będzie w formie kodu ?<em class="TipIkona"><b>Strona nie będzie w formie tekstu wpisywanego w edytorze, tylko jej treść będzie generowana przez skrypt - np regulamin z zewnętrznych serwisów</b></em></label>
                                <input type="radio" value="1" name="strona_skrypt" onclick="zmien_tryb_edytora(0)" id="strona_skrypt_tak" <?php echo (($info['pages_script'] == 1) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="strona_skrypt_tak">tak</label>
                                <input type="radio" value="0" name="strona_skrypt" onclick="zmien_tryb_edytora(1)" id="strona_skrypt_nie" <?php echo (($info['pages_script'] == 0) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="strona_skrypt_nie">nie</label>
                            </p>                               

                            <p id="rozwijanie">
                                <label>Czy pełny tekst ma być rozwijany w module ?<em class="TipIkona"><b>Jeżeli strona będzie modułem i zostanie zaznaczona opcja TAK, to pod skróconym tekstem nie będzie odnośnika do calego artykułu tylko tekst można będzie rozwinąć na stronie</b></em></label>
                                <input type="radio" value="1" name="rozwiniecie" id="rozwijanie_tak" <?php echo (($info['pages_more'] == 1) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="rozwijanie_tak">tak</label>
                                <input type="radio" value="0" name="rozwiniecie" id="rozwijanie_nie" <?php echo (($info['pages_more'] == 0) ? 'checked="checked"' : ''); ?> /><label class="OpisFor" for="rozwijanie_nie">nie</label>
                            </p>        

                            <div id="foto_strona">

                                <p>
                                  <label for="foto">Ścieżka zdjęcia:</label>           
                                  <input type="text" name="zdjecie" size="95" value="<?php echo $info['pages_image']; ?>" class="obrazek" ondblclick="openFileBrowser('foto','','<?php echo KATALOG_ZDJEC; ?>')" id="foto" autocomplete="off" /><em class="TipIkona"><b>Kliknij dwukrotnie w pole obok żeby otworzyć okno przeglądarki zdjęć</b></em>
                                  <span class="usun_zdjecie TipChmurka" data-foto="foto"><b>Kliknij w ikonę żeby usunąć przypisane zdjęcie</b></span>
                                  <span class="PrzegladarkaZdjec TipChmurka" onclick="openFileBrowser('foto','','<?php echo KATALOG_ZDJEC; ?>')"><b>Kliknij żeby otworzyć okno przeglądarki zdjęć</b></span>
                                </p>      

                                <div id="divfoto" style="padding-left:10px;display:none">
                                    <label>Zdjęcie:</label>
                                    <span id="fofoto">
                                        <span class="zdjecie_tbl">
                                            <img src="obrazki/_loader_small.gif" alt="" />
                                        </span>
                                    </span> 
                                    
                                    <?php if (!empty($info['pages_image'])) { ?>
                                    
                                    <script>            
                                    pokaz_obrazek_ajax('foto', '<?php echo $info['pages_image']; ?>')
                                    </script>
                                    
                                    <?php } ?> 
                                    
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
                                
                                    // pobieranie danych jezykowych
                                    $zapytanie_jezyk = "select distinct * from pages_description where pages_id = '".(int)$_GET['id_poz']."' and language_id = '" .$ile_jezykow[$w]['id']."'";
                                    $sqls = $db->open_query($zapytanie_jezyk);
                                    $nazwa = $sqls->fetch_assoc();   
                                    
                                    ?>
                                    
                                    <div id="info_tab_id_<?php echo $w; ?>" style="display:none;">
                                    
                                        <p>
                                           <?php if ($w == '0') { ?>
                                            <label class="required">Tytuł strony:</label>
                                            <input type="text" name="nazwa_<?php echo $w; ?>" size="65" value="<?php echo (isset($nazwa['pages_title']) ? Funkcje::formatujTekstInput($nazwa['pages_title']) : ''); ?>" id="nazwa_0" />
                                           <?php } else { ?>
                                            <label>Tytuł strony:</label>   
                                            <input type="text" name="nazwa_<?php echo $w; ?>" size="65" value="<?php echo (isset($nazwa['pages_title']) ? Funkcje::formatujTekstInput($nazwa['pages_title']) : ''); ?>" />
                                           <?php } ?>
                                        </p>        
                                          
                                        <div id="edytor">

                                            <div id="edytor_tekst" <?php echo (($info['pages_script'] == 1) ? 'style="display:none"' : ''); ?>>
                                              <div class="edytor">
                                                <textarea cols="50" rows="30" id="opis_<?php echo $w; ?>" name="opis_<?php echo $w; ?>"><?php echo (isset($nazwa['pages_text']) ? $nazwa['pages_text'] : ''); ?></textarea>
                                              </div>      
                                            </div>
                                            
                                            <div id="edytor_skrypt" class="EdytorSkrypt" <?php echo (($info['pages_script'] == 1) ? 'style="display:block"' : ''); ?>>
                                              <div class="maleInfo" style="margin-left:0px">W oknie poniżej wklej kod, który ma być wyświetlany na stronie</div>
                                              <textarea cols="100" rows="20" id="opis_script_<?php echo $w; ?>" name="opis_script_<?php echo $w; ?>"><?php echo (isset($nazwa['pages_text']) ? $nazwa['pages_text'] : ''); ?></textarea>
                                            </div>  

                                        </div>

                                    </div>
                                    <?php 

                                    $db->close_query($sqls);      
                                    unset($nazwa, $zapytanie_jezyk);
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
                                
                                    // pobieranie danych jezykowych
                                    $zapytanie_jezyk = "select distinct * from pages_description where pages_id = '".(int)$_GET['id_poz']."' and language_id = '" .$ile_jezykow[$w]['id']."'";
                                    $sqls = $db->open_query($zapytanie_jezyk);
                                    $nazwa = $sqls->fetch_assoc();   
                                    
                                    ?>
                                    
                                    <div id="info_tab_id_<?php echo ($w + 100); ?>" style="display:none;">
                                    
                                        <div class="edytor">
                                          <textarea cols="50" rows="30" id="opis_krotki_<?php echo ($w + 100); ?>" name="opis_krotki_<?php echo $w; ?>"><?php echo (isset($nazwa['pages_short_text']) ? $nazwa['pages_short_text'] : ''); ?></textarea>
                                        </div>                            

                                    </div>
                                    <?php    

                                    $db->close_query($sqls);      
                                    unset($nazwa, $zapytanie_jezyk);                                    
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
                                
                                    // pobieranie danych jezykowych
                                    $zapytanie_jezyk = "select distinct * from pages_description where pages_id = '".(int)$_GET['id_poz']."' and language_id = '" .$ile_jezykow[$w]['id']."'";
                                    $sqls = $db->open_query($zapytanie_jezyk);
                                    $nazwa = $sqls->fetch_assoc();   
                                    
                                    ?>
                                    
                                    <div id="info_tab_id_<?php echo ($w + 200); ?>" style="display:none;">                        

                                        <p>
                                          <label>Meta Tagi - Tytuł:</label>
                                          <textarea name="tytul_meta_<?php echo $w; ?>" onkeyup="licznik_znakow_meta(this,'iloscZnakowNazwa_<?php echo $w; ?>')" rows="4" cols="70"><?php echo (isset($nazwa['meta_title_tag']) ? $nazwa['meta_title_tag'] : ''); ?></textarea>
                                        </p> 
                                        
                                        <p class="LicznikMeta">
                                          <label></label>
                                          Ilość wpisanych znaków: <span class="iloscZnakow" id="iloscZnakowNazwa_<?php echo $w; ?>"><?php echo (isset($nazwa['meta_title_tag']) ? strlen(mb_convert_encoding((string)$nazwa['meta_title_tag'], 'ISO-8859-1', 'UTF-8')) : ''); ?></span>
                                          zalecana maksymalna ilość: <span class="iloscZnakow" id="iloscZnakowNazwa_<?php echo $w; ?>Max"><?php echo DLUGOSC_META_NAZWA; ?></span>
                                        </p>                                        
                                        
                                        <p>
                                          <label>Meta Tagi - Opis:</label>
                                          <textarea name="opis_meta_<?php echo $w; ?>" onkeyup="licznik_znakow_meta(this,'iloscZnakowOpis_<?php echo $w; ?>')" rows="4" cols="70"><?php echo (isset($nazwa['meta_desc_tag']) ? $nazwa['meta_desc_tag'] : ''); ?></textarea>
                                        </p>   
                                        
                                        <p class="LicznikMeta">
                                          <label></label>
                                          Ilość wpisanych znaków: <span class="iloscZnakow" id="iloscZnakowOpis_<?php echo $w; ?>"><?php echo (isset($nazwa['meta_desc_tag']) ? strlen(mb_convert_encoding((string)$nazwa['meta_desc_tag'], 'ISO-8859-1', 'UTF-8')) : ''); ?></span>
                                          zalecana maksymalna ilość: <span class="iloscZnakow" id="iloscZnakowOpis_<?php echo $w; ?>Max"><?php echo DLUGOSC_META_OPIS; ?></span>
                                        </p>                                          
                                        
                                        <p>
                                          <label>Meta Tagi - Słowa kluczowe:</label>
                                          <textarea name="slowa_meta_<?php echo $w; ?>" onkeyup="licznik_znakow_meta(this,'iloscZnakowSlowa_<?php echo $w; ?>')" rows="4" cols="70"><?php echo (isset($nazwa['meta_keywords_tag']) ? $nazwa['meta_keywords_tag'] : ''); ?></textarea>
                                        </p>   

                                        <p class="LicznikMeta">
                                          <label></label>
                                          Ilość wpisanych znaków: <span class="iloscZnakow" id="iloscZnakowSlowa_<?php echo $w; ?>"><?php echo (isset($nazwa['meta_keywords_tag']) ? strlen(mb_convert_encoding((string)$nazwa['meta_keywords_tag'], 'ISO-8859-1', 'UTF-8')) : ''); ?></span>
                                          zalecana maksymalna ilość: <span class="iloscZnakow" id="iloscZnakowSlowa_<?php echo $w; ?>Max"><?php echo DLUGOSC_META_SLOWA; ?></span>
                                        </p>                                          
                            
                                    </div>
                                    <?php                    

                                    $db->close_query($sqls);      
                                    unset($nazwa, $zapytanie_jezyk);                                    
                                }            
                                ?>                      
                            </div>
                            
                        </div>                        
                        
                    </div>
                
                </div>
                
                <script>
                gold_tabs_horiz('0','0','opis_');
                
                <?php 
                if (!empty($info['link'])) {
                    echo 'zmien_link(1);';
                } else {
                    echo 'zmien_link(0);';
                } ?>
                </script>            
            
                <?php 

            } else {
            
                echo '<div class="pozycja_edytowana">Brak danych do wyświetlenia</div>';
            
            }
            ?>                    
            
          </div>
          
          <?php if ((int)$db->ile_rekordow($sql) > 0) { ?>
          
          <br />
          
          <div class="przyciski_dolne">
          
              <input type="hidden" name="powrot" id="powrot" value="0" />
              <input type="submit" class="przyciskNon" value="Zapisz dane" />
              <input type="submit" class="przyciskNon" value="Zapisz dane i pozostań w edycji" onclick="$('#powrot').val(1)" />

              <?php 
              // jezeli jest get zakladka wraca do ustawien wygladu
              if (isset($_GET['zakladka']) ) { ?>
              
              <button type="button" class="przyciskNon" onclick="cofnij('wyglad','<?php echo Funkcje::Zwroc_Wybrane_Get(array('zakladka')); ?>','wyglad');">Powrót</button> 
              
              <?php } else { ?>
              
              <button type="button" class="przyciskNon" onclick="cofnij('strony_informacyjne','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','strony_informacyjne');">Powrót</button> 
              
              <?php } ?>            

          </div>    

          <?php }
          
          $db->close_query($sql);
          unset($info);          
          
          ?>

          </form>

    </div>
    
    <?php
    include('stopka.inc.php');    
    
} ?>
