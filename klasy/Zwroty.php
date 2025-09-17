<?php

class Zwroty {

  // funkcja wyswietlajaca status zwrotu
  public static function pokazNazweStatusuZwrotu( $status_id, $jezyk = '1') {

    $wynik = '';
    
    $zapytanie = "SELECT s.return_status_id, s.return_status_color, sd.return_status_name FROM return_status s LEFT JOIN return_status_description sd ON sd.return_status_id = s.return_status_id WHERE s.return_status_id = '".$status_id."' AND sd.language_id = '".$jezyk."'";
    $sql = $GLOBALS['db']->open_query($zapytanie);
    
    if ( (int)$GLOBALS['db']->ile_rekordow($sql) > 0 ) {

        while($nazwa_statusu = $sql->fetch_assoc()) {
          $wynik = $nazwa_statusu['return_status_name'];
        }
        
    }
    
    $GLOBALS['db']->close_query($sql);  
    unset($zapytanie);
    
    return $wynik;
    
  }   
  
  // funkcjazwracajaca domyslny status zwrotu
  public static function domyslnyStatusZwrotu() {

    $wynik = '';
    
    $zapytanie = "SELECT s.return_status_id FROM return_status s WHERE s.return_status_default = '1'";
    $sql = $GLOBALS['db']->open_query($zapytanie);
    
    if ( (int)$GLOBALS['db']->ile_rekordow($sql) > 0 ) {

        while($id_statusu = $sql->fetch_assoc()) {
          $wynik = $id_statusu['return_status_id'];
        }
        
    }
    
    $GLOBALS['db']->close_query($sql);  
    unset($zapytanie);
    
    return $wynik;
    
  }   
  
  // funkcja generujaca unikalny nr zwrotu
  public static function UtworzIdZwrotu($dlugosc) {
    $ciag = 'Z-';
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