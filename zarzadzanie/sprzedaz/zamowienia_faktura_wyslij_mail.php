<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
    
        if ( isset($_POST['adresat']) && $_POST['adresat'] != '') {
        
            $zapytanie_tresc = "SELECT t.sender_name, t.email_var_id, t.sender_email, t.dw, t.template_id, t.email_file, tz.email_title, tz.description, tz.description_sms FROM email_text t LEFT JOIN email_text_description tz ON tz.email_text_id = t.email_text_id AND tz.language_id = '" . (int)$_POST['jezyk'] . "' WHERE t.email_var_id = 'EMAIL_WYGENEROWANA_FAKTURA'";
            $sql = $db->open_query($zapytanie_tresc);
            $tresc = $sql->fetch_assoc();    
        
            $email = new Mailing;
            
            $nadawca_email = Funkcje::parsujZmienne($tresc['sender_email']);
            $nadawca_nazwa = Funkcje::parsujZmienne($tresc['sender_name']); 

            $adresat_email = $filtr->process($_POST['adresat']);
            $adresat_nazwa = $filtr->process($_POST['adresat_nazwa']);
            
            $cc              = $filtr->process($_POST["cc"]);

            $temat           = $filtr->process($_POST['temat']);
            $tekst           = $filtr->process($_POST['wiadomosc']);
            $zalaczniki      = array();
            $szablon         = $tresc['template_id'];
            $jezyk           = $_POST['jezyk'];  
            
            // generowanie faktury
            
            require_once('../tcpdf/config/lang/pol.php');
            require_once('../tcpdf/tcpdf.php');            
            
            $i18n = new Translator($db, (int)$_POST['jezyk']);
            $tlumacz = $i18n->tlumacz( array('WYGLAD', 'KLIENCI', 'KLIENCI_PANEL', 'PRODUKT', 'FAKTURA') );

            class MYPDF extends TCPDF {
              public function Footer() {
                global $tlumacz;
                $this->SetY(-15);
                $this->SetFont('helvetica', 'I', 6);
                $this->Cell(0, 0, $tlumacz['WYGENEROWANO_W_PROGRAMIE'], 'T', false, 'L', 0, '', 0, false, 'T', 'M');
              }
            }

            $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

            $pdf->SetCreator('shopGold');
            $pdf->SetAuthor('shopGold');
            $pdf->SetTitle($tlumacz['FAKTURA']);
            $pdf->SetSubject($tlumacz['FAKTURA']);
            $pdf->SetKeywords($tlumacz['FAKTURA']);

            if (PDF_PLIK_NAGLOWKA != '' && file_exists(KATALOG_SKLEPU . KATALOG_ZDJEC . '/'.PDF_PLIK_NAGLOWKA)) {
              $plik_naglowka = PDF_PLIK_NAGLOWKA;
              $szerokosc_pliku_naglowka = PDF_PLIK_NAGLOWKA_SZEROKOSC;
            } else {
              $plik_naglowka = '';
              $szerokosc_pliku_naglowka = '';
            }

            $daneFirmy = explode(PHP_EOL, (string)PDF_DANE_FIRMY);
            $pozostaleDaneFirmy = '';
            for ( $y = 1; $y < count($daneFirmy); $y++ ) {  
                $pozostaleDaneFirmy .= $daneFirmy[$y] . "\n";
            }
            $pdf->SetHeaderData($plik_naglowka, $szerokosc_pliku_naglowka, trim((string)$daneFirmy[0]), $pozostaleDaneFirmy);
            unset($daneFirmy, $pozostaleDaneFirmy);  

            $pdf->SetFont('dejavusans', '', 6);

            $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', '6'));
            $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

            $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

            $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

            // ---------------------------------------------------------

            $pdf->AddPage();

            $pdf->SetFont('dejavusans', '', 8);

            $text = PDFFaktura::WydrukFakturyPDF((int)$_GET['id_poz'], (int)$_GET['id'], '2', '');
            
            // zamiana https na http
            $text = str_replace('src="https', 'src="http', (string)$text);  
            
            $pdf->writeHTML($text, true, false, false, false, '');

            $plik = KATALOG_SKLEPU . 'zarzadzanie/tmp/faktura-vat-zamowienie-nr-' . (int)$_GET['id_poz'] . '-uniq-' . time() . '.pdf';
            
            $pdf->Output($plik, 'F');

            $zalaczniki['faktura'] = $plik;

            $email->wyslijEmail($nadawca_email, $nadawca_nazwa, $adresat_email, $adresat_nazwa, $cc, $temat, $tekst, $szablon, $jezyk, $zalaczniki);
            
            array_map('unlink', glob(KATALOG_SKLEPU . "zarzadzanie/tmp/faktura-vat-zamowienie-nr-*"));
            
            $pola = array(array('invoices_date_send', 'now()'));
            $db->update_query('invoices' , $pola, "invoices_id = '" . (int)$_GET['id'] . "'");
            unset($pola);            

            $db->close_query($sql);
            unset($tresc, $zapytanie_tresc, $nadawca_email, $nadawca_nazwa, $adresat_email, $cc, $adresat_nazwa, $temat, $tekst, $szablon, $jezyk);           

        }

        Funkcje::PrzekierowanieURL('zamowienia_faktura_wyslij_mail.php?id_poz=' . (int)$_GET["id_poz"] . '&wyslano');
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Wysłanie wiadomości e-mail z fakturą VAT do klienta</div>
    <div id="cont">
    
        <?php
        if ( isset($_GET['wyslano']) ) {
        ?>
          
            <div class="poleForm">
        
                <div class="naglowek">Wysyłanie wiadomości z fakturą VAT</div>

                <div class="pozycja_edytowana">

                  <div class="MailWyslano">
                      Mail został wysłany ...
                  </div>    
                  
                  <div class="przyciski_dolne">
                    <button type="button" class="przyciskNon" onclick="cofnij('zamowienia_szczegoly','?id_poz=<?php echo $_GET['id_poz']; ?>','sprzedaz');">Powrót</button>
                  </div>

                </div>     

            </div>
            
        <?php
        
        } else {    

            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            if ( !isset($_GET['jezyk']) ) {
                 $_GET['jezyk'] = $_SESSION['domyslny_jezyk']['id'];
            }
            
            $zapytanie = "select * from orders where orders_id = '" . (int)$_GET['id_poz'] . "'";
            $sql = $db->open_query($zapytanie);

            if ((int)$db->ile_rekordow($sql) > 0 && (int)$_GET['id'] > 0) {

                $info = $sql->fetch_assoc();
                ?>  
                
                <form action="sprzedaz/zamowienia_faktura_wyslij_mail.php?id_poz=<?php echo $_GET['id_poz']; ?>&id=<?php echo $_GET['id']; ?>" method="post" id="emailForm" class="cmxform">          

                  <div class="poleForm">

                    <div class="naglowek">Wysłanie wiadomości e-mail z fakturą VAT do klienta</div>
                    
                    <div class="pozycja_edytowana">

                        <div class="info_content">

                          <input type="hidden" name="akcja" value="zapisz" />
                          <input type="hidden" id="jezyk" name="jezyk" value="<?php echo (int)$_GET['jezyk']; ?>" />

                          <?php
                          $zapytanieTresc = "select * from email_text et, email_text_description etd where et.email_var_id = 'EMAIL_WYGENEROWANA_FAKTURA' and et.email_text_id = etd.email_text_id and etd.language_id = '" . (int)$_GET['jezyk'] . "'";
                          $sqlTresc = $db->open_query($zapytanieTresc);
                          //
                          $inft = $sqlTresc->fetch_assoc();
                          ?>
                          
                          <input type="hidden" name="szablon" value="<?php echo $inft['template_id']; ?>" />

                          <p class="JezykiMailaFaktura">
                            <label>Wersja językowa szablonu:</label>
                            <?php
                            $sqlJezykow = $db->open_query("SELECT * FROM languages WHERE status = '1' ORDER BY sort_order");
                            while ($infe = $sqlJezykow->fetch_assoc()) {
                                echo '<a ' . (($infe['languages_id'] == (int)$_GET['jezyk']) ? 'class="AktywnyJezyk"' : '') . ' href="sprzedaz/zamowienia_faktura_wyslij_mail.php?id_poz=' . (int)$_GET['id_poz'] . '&id=' . (int)$_GET['id'] . '&jezyk=' . $infe['languages_id'] . '">' . $infe['name'] . '</a>';
                            }
                            $db->close_query($sqlJezykow);
                            unset($infe);
                            ?>
                          </p>

                          <p>
                            <label class="required" for="temat">Temat:</label>
                            <input type="text" name="temat" id="temat" size="83" value="<?php echo str_replace('{NUMER_ZAMOWIENIA}', (string)$_GET['id_poz'], (string)$inft['email_title']); ?>" />
                          </p>

                          <p>
                            <label for="adresat">Wyślij na maila:</label>
                            <input type="text" name="adresat" id="adresat" size="83" value="<?php echo $info['customers_email_address']; ?>" />
                            <input type="hidden" name="adresat_nazwa" value="<?php echo (($info['customers_company'] != '') ? $info['customers_company'] : $info['customers_name']); ?>" />
                          </p>
                          
                          <p>
                            <label for="cc">Do wiadomości:</label>
                            <input type="text" name="cc" id="cc" size="83" value="<?php echo $inft['dw']; ?>" /><em class="TipIkona"><b>Rozdzielone przecinkami adresy e-mail na które ma zostać przesłana kopia wiadomości</b></em>
                          </p>                        

                          <?php
                          //
                          $tekst = $inft['description'];
                          //
                          $i18n = new Translator($db, (int)$_GET['jezyk']);
                          $GLOBALS['tlumacz'] = $i18n->tlumacz( array('ZAMOWIENIE_REALIZACJA','PRODUKT','WYGLAD'), null, true );
                          //
                          // podmiana danych
                          define('NUMER_ZAMOWIENIA', (int)$_GET['id_poz']);
                          define('LINK', 'link.html'); 
                          //
                          $tekst = Funkcje::parsujZmienne($tekst);
                          $tekst = preg_replace("{(<br[\\s]*(>|\/>)\s*){2,}}i", "<br /><br />", (string)$tekst);                    
                          //
                          ?>
                        
                          <p>
                            <label>Treść wiadomości:<em class="TipIkona"><b>Wpisz tylko treść wiadomości - pozostałe elementy zostaną dołączone z domyślnego szablonu wiadomości email</b></em></label>
                            <textarea id="wiadomosc" name="wiadomosc" class="wysiwyg" cols="150" rows="10"><?php echo $tekst; ?></textarea>
                          </p>

                          <?php
                          $db->close_query($sqlTresc);
                          unset($zapytanieTresc, $inft);                        
                          ?>
                          
                        </div>

                        <div class="przyciski_dolne">
                          <input id="form_submit" type="submit" class="przyciskNon" value="Wyślij wiadomość e-mail" />
                          <button type="button" class="przyciskNon" onclick="cofnij('zamowienia_szczegoly','?id_poz=<?php echo $_GET['id_poz']; ?>','sprzedaz');">Powrót</button> 
                        </div>

                    </div>
                    
                  </div>

                </form>
                
                <?php
                
              } else {
              
                  echo '<div class="poleForm">
                          <div class="naglowek">Wysyłanie wiadomości</div>
                          <div class="pozycja_edytowana">Brak danych do wyświetlenia</div>
                        </div>';

              }
              
              $db->close_query($sql);
              unset($zapytanie, $info);
              
        }
        ?>

    </div>
    
    <?php
    include('stopka.inc.php');

}
