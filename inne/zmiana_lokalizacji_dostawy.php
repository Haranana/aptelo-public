<?php
chdir('../'); 
//
// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

if (isset($_POST['value'])) {

    if (Sesje::TokenSpr()) {
      
        // ustawienia globalne
        
        if ( (int)$_POST['koszyk'] == 0 ) {
    
            if ( isset($_SESSION['rodzajDostawy']['opis']) ) {
                unset($_SESSION['rodzajDostawy']['opis']);
            }
            $_SESSION['rodzajDostawy']['opis'] = $filtr->process($_POST['value']);
            
            if ( isset($_SESSION['rodzajDostawy']['opispunkt']) ) {
                unset($_SESSION['rodzajDostawy']['opispunkt']);
            }
            if ( isset($_POST['punktopis']) ) {
                $_SESSION['rodzajDostawy']['opispunkt'] = $filtr->process($_POST['punktopis']);        
            }

            if ( isset($_POST['punktodbioru']) ) {
                if ( isset($_SESSION['rodzajDostawy']['punktodbioru']) ) {
                    unset($_SESSION['rodzajDostawy']['punktodbioru']);
                }
                $_SESSION['rodzajDostawy']['punktodbioru'] = $filtr->process($_POST['punktodbioru']);
            }
            
        }
        
        // ustawienia koszyka

        if ( isset($_SESSION['rodzajDostawyKoszyk'][$_POST['rodzaj']]['opis']) ) {
            unset($_SESSION['rodzajDostawyKoszyk'][$_POST['rodzaj']]['opis']);
        }
        $_SESSION['rodzajDostawyKoszyk'][$_POST['rodzaj']]['opis'] = $filtr->process($_POST['value']);
       
        if ( isset($_SESSION['rodzajDostawyKoszyk'][$_POST['rodzaj']]['opispunkt']) ) {
            unset($_SESSION['rodzajDostawyKoszyk'][$_POST['rodzaj']]['opispunkt']);
        }
        if ( isset($_POST['punktopis']) ) {
            $_SESSION['rodzajDostawyKoszyk'][$_POST['rodzaj']]['opispunkt'] = $filtr->process($_POST['punktopis']);        
        }
 
        if ( isset($_POST['punktodbioru']) ) {
            if ( isset($_SESSION['rodzajDostawyKoszyk'][$_POST['rodzaj']]['punktodbioru']) ) {
                unset($_SESSION['rodzajDostawyKoszyk'][$_POST['rodzaj']]['punktodbioru']);
            }
            $_SESSION['rodzajDostawyKoszyk'][$_POST['rodzaj']]['punktodbioru'] = $filtr->process($_POST['punktodbioru']);
        }          

        if ( isset($_SESSION['rodzajDostawyKoszyk'][$_POST['rodzaj']]['koszt']) ) {
            unset($_SESSION['rodzajDostawyKoszyk'][$_POST['rodzaj']]['koszt']);
        }
        if ( isset($_POST['koszt']) ) {
            $_SESSION['rodzajDostawyKoszyk'][$_POST['rodzaj']]['koszt'] = $filtr->process($_POST['koszt']);
        }
    }
    
}

?>