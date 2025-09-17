<?php

class Jezyki {
  
    public $tablicaJezyka;

    public function __construct($lng = '') {
        //
        $this->tablicaJezyka = array();
        //
        $zapytanie = "select languages_id, name, code, currencies_default, languages_default, image from languages";
        $sql = $GLOBALS['db']->open_query($zapytanie);
        
        if ( $lng == '' ) {
        
            while ( $wynik = $sql->fetch_assoc() ) {
                //
                // ustawia domyslny jezyk
                if ( $wynik['languages_default'] == 1 ) {
                     //
                     $this->tablicaJezyka = array('id' => $wynik['languages_id'],
                                                  'nazwa' => $wynik['name'],
                                                  'kod' => $wynik['code'],
                                                  'waluta' => $wynik['currencies_default'],
                                                  'ikona' => $wynik['image']);
                     //
                     break;
                }
                //
            }
        
        } else { 
        
            while ( $wynik = $sql->fetch_assoc() ) {
                //
                // ustawia domyslny jezyk
                if ( $lng == $wynik['languages_id'] ) {
                     //
                     $this->tablicaJezyka = array('id' => $wynik['languages_id'],
                                                  'nazwa' => $wynik['name'],
                                                  'kod' => $wynik['code'],
                                                  'waluta' => $wynik['currencies_default'],
                                                  'ikona' => $wynik['image']); 
                     break;
                     //
                }
                //
            } 

        }

        $GLOBALS['db']->close_query($sql);
        unset($zapytanie, $wynik); 
    }

}
?>