<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    $wynik  = '';
    $system = ( isset($_POST['system']) ? $_POST['system'] : '' );

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

      reset($_POST);
      //
      foreach ( $_POST as $key => $value ) {
        if ( $key != 'akcja' ) {
          $pola = array(
                  array('value',$filtr->process($value))
          );
          $db->update_query('settings' , $pola, " code = '".strtoupper((string)$key)."'");	
          //       
          unset($pola);
        }
      }
      
      $wynik = '<div id="'.$system.'" class="maleSukces" style="margin-left:20px;margin-top:10px;">dane zostały zmienione</div>';

    }

    $zapytanie = "SELECT * FROM settings WHERE type = 'sledzenie' ORDER BY sort ";
    $sql = $db->open_query($zapytanie);

    $parametr = array();

    if ( $db->ile_rekordow($sql) > 0 ) {
      while ($info = $sql->fetch_assoc()) {
        $parametr[$info['code']] = array($info['value'], $info['limit_values'], $info['description'], $info['form_field_type']);
      }
    }
    $db->close_query($sql);
    unset($zapytanie, $info);

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>

    <div id="naglowek_cont">Konfiguracja parametrów firm wysyłkowych</div>
    <div id="cont">

      <div class="poleForm">
        <div class="naglowek">Edycja danych</div>
          
        <div class="SledzenieNaglowki">
        
            <div class="SledzenieOkno">
                <a href="integracje/konfiguracja_wysylki_apaczka.php">
                    <div class="SledzenieDiv">
                        <div class="Foto"><img src="obrazki/logo/logo_apaczka.png" alt="" /></div>
                        <span>Apaczka V2</span>
                    </div>
                </a>
            </div>

            <div class="SledzenieOkno">
                <a href="integracje/konfiguracja_wysylki_bliskapaczka.php">
                    <div class="SledzenieDiv">
                        <div class="Foto"><img src="obrazki/logo/logo_bliskapaczka.png" alt="" /></div>
                        <span>Bliskapaczka</span>
                    </div>
                </a>
            </div>     

            <div class="SledzenieOkno">
                <a href="integracje/konfiguracja_wysylki_dhl.php">
                    <div class="SledzenieDiv">
                        <div class="Foto"><img src="obrazki/logo/logo_dhl.png" alt="" /></div>
                        <span>DHL</span>
                    </div>
                </a>
            </div>     

            <div class="SledzenieOkno">
                <a href="integracje/konfiguracja_wysylki_dpd.php">
                    <div class="SledzenieDiv">
                        <div class="Foto"><img src="obrazki/logo/logo_dpd.png" alt="" /></div>
                        <span>DPD</span>
                    </div>
                </a>
            </div>     

            <div class="SledzenieOkno">
                <a href="integracje/konfiguracja_wysylki_en.php">
                    <div class="SledzenieDiv">
                        <div class="Foto"><img src="obrazki/logo/logo_en.png" alt="" /></div>
                        <span>Elektroniczny Nadawca</span>
                    </div>
                </a>
            </div>     

            <div class="SledzenieOkno">
                <a href="integracje/konfiguracja_wysylki_furgonetka.php">
                    <div class="SledzenieDiv">
                        <div class="Foto"><img src="obrazki/logo/logo_furgonetka.png" alt="" /></div>
                        <span>Furgonetka</span>
                    </div>
                </a>
            </div>     

            <div class="SledzenieOkno">
                <a href="integracje/konfiguracja_wysylki_gls.php">
                    <div class="SledzenieDiv">
                        <div class="Foto"><img src="obrazki/logo/logo_gls.png" alt="" /></div>
                        <span>GLS</span>
                    </div>
                </a>
            </div>     

            <div class="SledzenieOkno">
                <a href="integracje/konfiguracja_wysylki_inpost.php">
                    <div class="SledzenieDiv">
                        <div class="Foto"><img src="obrazki/logo/logo_inpost.png" alt="" /></div>
                        <span>InPost paczkomaty ( przestarzała )</span>
                    </div>
                </a>
            </div>     

            <div class="SledzenieOkno">
                <a href="integracje/konfiguracja_wysylki_kurier_inpost.php">
                    <div class="SledzenieDiv">
                        <div class="Foto"><img src="obrazki/logo/logo_kurier_inpost.png" alt="" /></div>
                        <span>InPost kurier ( przestarzała )</span>
                    </div>
                </a>
            </div>     

            <div class="SledzenieOkno">
                <a href="integracje/konfiguracja_wysylki_inpost_shipx.php">
                    <div class="SledzenieDiv">
                        <div class="Foto"><img src="obrazki/logo/logo_kurier_inpost.png" alt="" /></div>
                        <span>InPost</span>
                    </div>
                </a>
            </div>     

            <div class="SledzenieOkno">
                <a href="integracje/konfiguracja_wysylki_kurjerzy.php">
                    <div class="SledzenieDiv">
                        <div class="Foto"><img src="obrazki/logo/logo_kurjerzy.png" alt="" /></div>
                        <span>KurJerzy</span>
                    </div>
                </a>
            </div>     
            
            <div class="SledzenieOkno">
                <a href="integracje/konfiguracja_wysylki_paczka_w_ruchu.php">
                    <div class="SledzenieDiv">
                        <div class="Foto"><img src="obrazki/logo/logo_paczkaruch.png" alt="" /></div>
                        <span>Orlen paczka</span>
                    </div>
                </a>
            </div>     
            
            <div class="SledzenieOkno">
                <a href="integracje/konfiguracja_wysylki_sendit.php">
                    <div class="SledzenieDiv">
                        <div class="Foto"><img src="obrazki/logo/logo_sendit.png" alt="" /></div>
                        <span>SendIt</span>
                    </div>
                </a>
            </div>     
            
            <div class="SledzenieOkno">
                <a href="integracje/konfiguracja_wysylki_geis.php">
                    <div class="SledzenieDiv">
                        <div class="Foto"><img src="obrazki/logo/logo_geis.png" alt="" /></div>
                        <span>GEIS</span>
                    </div>
                </a>
            </div>     

        </div>
          
        <div class="cl"></div>

      </div>
    </div>

    
    <?php
    include('stopka.inc.php');    
    
} ?>
