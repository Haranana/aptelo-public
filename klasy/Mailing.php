<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Mailing {

  public $email;

  /*
  **** klasa do obslugi wysylania emaili
  */

  public function __construct() {

    require_once('PHPMailer/Exception.php');
    require_once('PHPMailer/PHPMailer.php');
    require_once('PHPMailer/SMTP.php');

    require_once('inne/simple_html_dom.php');
    $this->email = @new PHPMailer(true);

    if ( EMAIL_SPOSOB_WYSLANIA == 'smtp' ) {
        $this->email->SMTPOptions = array(
                                    'ssl' => array(
                                    'verify_peer' => false,
                                    'verify_peer_name' => false,
                                    'allow_self_signed' => true
                                    )
        );
        $this->email->IsSMTP();                                                                            // czy jest mozliowsc SMTP
        $this->email->Host       = EMAIL_ADRES_SERWERA_SMTP;                                               // SMTP server
        $this->email->SMTPDebug  = 0;                                                                      // wlacz SMTP debug
                                                                                                           // 1 = bledy i wiadomosci
                                                                                                           // 2 = tylko wiadomosci
        if ( EMAIL_AUTENTYKACJA_SERWERA_SMTP == 'tak' ) {
            $this->email->SMTPAuth   = true;                                                               // wlacz SMTP authentication
            $this->email->Port       = EMAIL_PORT_SERWERA_SMTP;                                            // port SMTP na serwerze Gmaila
            $this->email->Username   = EMAIL_LOGIN_SERWERA_SMTP;                                           // SMTP uzytkownik
            $this->email->Password   = EMAIL_HASLO_SERWERA_SMTP;                                           // SMTP haslo
        }

        if ( EMAIL_SERWER_SMTP_SSL == 'tak' ) {
            $this->email->SMTPSecure = 'tls';
        }
    }
    $this->email->CharSet    = "UTF-8";
    $this->email->Encoding    = '8bit';
    $this->email->XMailer =  ' ';

  }

  public function wyslijEmail($nadawca_email, $nadawca_nazwa, $adresat_email, $adresat_nazwa, $cc, $temat, $tekst, $szablon, $jezyk, $zalaczniki, $odpowiedz_email = '', $odpowiedz_nazwa = '', $zalacznikiCiag = array()) {

    try {

      $komunikat = '';

      $this->email->SetFrom($nadawca_email, $nadawca_nazwa);

      if ( $odpowiedz_email != '' &&  $odpowiedz_nazwa != '' ) {
        $this->email->AddReplyTo($odpowiedz_email, $odpowiedz_nazwa);
      } else {
        $this->email->AddReplyTo($nadawca_email, $nadawca_nazwa);
      }

      $this->email->AddAddress($adresat_email, $adresat_nazwa);

      if($cc != "") {
        $ccOdbiorcy = explode(",", $cc); 
        foreach($ccOdbiorcy as $ccOdbiorca) { 
          $this->email->AddBCC($ccOdbiorca);  
        } 
      }

      $this->email->Subject = $temat;

      if ( count($zalaczniki) > 0 ) {

        if ( isset($zalaczniki['file']) ) {
        
            foreach(array_keys($zalaczniki['file']['name']) as $key) {
            
               $source = $zalaczniki['file']['tmp_name'][$key];
               $filename = $zalaczniki['file']['name'][$key];
               $this->email->AddAttachment($source, $filename);
               
            }
            
        } else {
        
            foreach($zalaczniki as $zalacznik) {
            
               // jezeli pliki sklepu
               if ( !is_array($zalacznik) ) {
                   $filename = KATALOG_SKLEPU . '' . $zalacznik;
                   if (file_exists($filename)) {
                        $this->email->AddAttachment($filename);
                   }
               }
               
               // jezeli pliki formularz
               if ( is_array($zalacznik) ) {
                   if ( isset($zalacznik['tmp_name']) && $zalacznik['name'] ) {
                       $source = $zalacznik['tmp_name'];
                       $filename = $zalacznik['name'];
                       $this->email->AddAttachment($source, $filename);
                   }
               }
               
            }
            
        }
        
      }
      
      if ( count($zalacznikiCiag) > 0 ) {
        for ($v = 0; $v < count($zalacznikiCiag); $v++ ) {
           $this->email->AddStringAttachment($zalacznikiCiag[$v]['ciag'], $zalacznikiCiag[$v]['plik'], "base64", $zalacznikiCiag[$v]['typ']);
        }
      }

      if ( $tekst != '' ) {

        $tekst = $this->PodstawSzablon($tekst, $jezyk, $szablon);

        // zamiana adresu strony na klikalny link w tekscie
        $tekst = $this->UtworzLinkAdresu($tekst);

        // generowanie zalacznikow do maila
        $tekst = $this->UtworzAdresObrazow($tekst);

      } else {

        $tekst = '<br />';

      }

      $this->email->MsgHTML($tekst);

      $this->email->Send();

      $komunikat  = 'Wiadomość została wysłana !!!<br />';
      $komunikat .= 'Adresat wiadomości: <b>' . $adresat_nazwa . '</b><br />';
      $komunikat .= 'Adres email       : <b>' . $adresat_email . '</b><br />';

    }
    
    catch (phpmailerException $e) {
      $komunikat   = '<span class="czerwony">Wiadomość nie została wysłana !!!</span><br />';
      $komunikat  .= $e->errorMessage();
    } catch (Exception $e) {
      $komunikat   = '<span class="czerwony">Wiadomość nie została wysłana !!!</span><br />';
      $komunikat  .= $e->getMessage(); 
    }

    return $komunikat;
  }

  // funkcja zamieniajaca link na podstac klikalna ************************************************************
  public function UtworzLinkAdresu($tekst) {
      $tekst = preg_replace("/(^|[\n >])([\w]*?)((ht|f)tp(s)?:\/\/[\w]+[^ \,\"\n\r\t<]*)/is", "$1$2<a href=\"$3\" >$3</a>", (string)$tekst);
      $tekst = preg_replace("/(^|[\n >])([\w]*?)((www|ftp)\.[^ \,\"\t\n\r<]*)/is", "$1$2<a href=\"http://$3\" >$3</a>", (string)$tekst);
      $tekst = preg_replace("/(^|[\n >])([a-z0-9&\-_\.]+?)@([\w\-]+\.([\w\-\.]+)+)/i", "$1<a href=\"mailto:$2@$3\">$2@$3</a>", (string)$tekst);
      return $tekst;
  }

  // funkcja podstawiajaca szablon emaila ********************************************************
  public function PodstawSzablon($tekst, $jezyk, $szablon = '1') {
      
      $html = '';

      $zapytanie = "select s.template_id, sz.description from email_templates s LEFT JOIN email_templates_description sz ON sz.template_id = s.template_id AND sz.language_id = '".$jezyk."' WHERE s.template_id = '".$szablon."'";
      $sql = $GLOBALS['db']->open_query($zapytanie);

      if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
          //
          $info = $sql->fetch_assoc();
          $html = $info['description'];
          unset($info);
          //
      }
      $GLOBALS['db']->close_query($sql);

      $html = str_replace('{CONTENT}',(string)$tekst, (string)$html);
      unset($info,$zapytanie);

      $html = str_replace('{ADRES_URL_SKLEPU}', (string)ADRES_URL_SKLEPU, (string)$html);

      $zmienne = "SELECT * FROM settings WHERE type = 'firma' OR type = 'kontakt' OR type = 'sklep' OR type = 'email'";
      $sql = $GLOBALS['db']->open_query($zmienne);
      
      if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
        
          while ($info = $sql->fetch_assoc()) {
              //
              $ciag_zamieniany = '{'.$info['code'].'}';
              $ciag_wstawiany = $info['value'];
              $html = str_replace($ciag_zamieniany, (string)$ciag_wstawiany, (string)$html);
              //
          }
          
          unset($info);
          
      }
      
      $GLOBALS['db']->close_query($sql);
      unset($zmienne);

      // generowanie zalacznikow do maila
      //$html = $this->UtworzAdresObrazow($html);

      return $html;
  }

  // funkcja zamieniajaca linki do obrazkow na sciezke ********************************************************
  public function UtworzSciezkeObrazow($tekst) {

      $adres_url = '(^\/' . KATALOG_ZDJEC . '|^'.str_replace('/','\/',ADRES_URL_SKLEPU).'\/' . KATALOG_ZDJEC . ')';
      $sciezka = KATALOG_SKLEPU . KATALOG_ZDJEC;

      $html = str_get_html($tekst);
      if ( empty($html) ) {
          return $html;
      } else {
          foreach($html->find('img') as $element) {
            $sciezka_obrazka = preg_replace('#'.$adres_url.'#i', $sciezka, (string)$element->src);
            if ( is_file($sciezka_obrazka) ) {
              $element->src = $sciezka_obrazka;
            }
          }
      }

      return $html;
  }


  // funkcja zamieniajaca sciezki do obrazkow na linki ********************************************************
  public function UtworzAdresObrazow($tekst) {

      $sciezka = '(^\/' . KATALOG_ZDJEC . '|^'.str_replace('/','\/',KATALOG_SKLEPU). KATALOG_ZDJEC . ')';
      $adres_url = KATALOG_SKLEPU . KATALOG_ZDJEC;
      $licznik = 1;

      $html = str_get_html($tekst);

      if ( empty($html) ) {

          return $html;

      } else {

          foreach($html->find('img') as $element) {

            if ( isset($element->src) ) {

                if ( strpos((string)$element->src,'http') === false && strpos((string)$element->src,'data:') === false ) {
                    $id = 'img-'.$licznik;
                    $sciezka_obrazka = preg_replace('#'.$sciezka.'#i', $adres_url, $element->src);
                    if ( is_file($sciezka_obrazka) && filesize($sciezka_obrazka) > 0 ) {
                        $alt = substr($element->src, strrpos($element->src, "/") + 1);
                        $alt = substr($alt, 0, strrpos($alt, "."));
                        $alt = urlencode($alt);
                        $id = $alt.'-'.$licznik;
                        $element->src = 'cid:'.$id;
                        $this->email->AddEmbeddedImage($sciezka_obrazka, $id, $alt, 'base64');
                    }

                } else {
                    $sciezka_obrazka = preg_replace('#'.$sciezka.'#i', $adres_url, $element->src);
                    if ( is_file($sciezka_obrazka) ) {
                        $element->src = $sciezka_obrazka;
                    }
                }

                $licznik++;
            }
          }
      }

      return $html;
  }

}