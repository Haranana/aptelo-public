<?php

class Reklamacje {

  // funkcja wyswietlajaca status reklamacji
  public static function pokazNazweStatusuReklamacji( $status_id, $jezyk = '1') {

    $wynik = '';
    
    $zapytanie = "SELECT s.complaints_status_id, s.complaints_status_color, sd.complaints_status_name FROM complaints_status s LEFT JOIN complaints_status_description sd ON sd.complaints_status_id = s.complaints_status_id WHERE s.complaints_status_id = '".$status_id."' AND sd.language_id = '".$jezyk."'";
    $sql = $GLOBALS['db']->open_query($zapytanie);
    
    if ( (int)$GLOBALS['db']->ile_rekordow($sql) > 0 ) {

        while($nazwa_statusu = $sql->fetch_assoc()) {
          $wynik = $nazwa_statusu['complaints_status_name'];
        }
        
    }
    
    $GLOBALS['db']->close_query($sql);  
    unset($zapytanie);
    
    return $wynik;
    
  }   
  
  // funkcjazwracajaca domyslny status reklamacji
  public static function domyslnyStatusReklamacji() {

    $wynik = '';
    
    $zapytanie = "SELECT s.complaints_status_id FROM complaints_status s WHERE s.complaints_status_default = '1'";
    $sql = $GLOBALS['db']->open_query($zapytanie);
    
    if ( (int)$GLOBALS['db']->ile_rekordow($sql) > 0 ) {

        while($id_statusu = $sql->fetch_assoc()) {
          $wynik = $id_statusu['complaints_status_id'];
        }
        
    }
    
    $GLOBALS['db']->close_query($sql);  
    unset($zapytanie);
    
    return $wynik;
    
  }   
  

  // funkcja generujaca unikalny nr reklamacji
  public static function UtworzIdReklamacji($dlugosc) {
    $ciag = '';
    while (strlen((string)$ciag) < $dlugosc) {
      $char = chr(rand(0,255));
      if (preg_match('/^[a-z0-9]$/i', $char)) {
        $ciag .= $char;
      }
    }
    return strtoupper((string)$ciag);
  }  

} 

?>