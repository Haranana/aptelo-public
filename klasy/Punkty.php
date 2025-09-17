<?php

class Punkty {

  public $suma;
  public $suma_punktow_klienta;
  public $wartosc;
  public $wartosc_maksymalna_punkty;
  public $wartosc_maksymalna_kwota;

  public function __construct( $id_klienta, $minus_produkty_pkt = false ) {

    // suma punktow
    $this->suma = '';
    // suma punktow klienta z bazy
    $this->suma_punktow_klienta = '';    
    // wartosc punktow
    $this->wartosc = '';
    // maksymalna wartosc punktow
    $this->wartosc_maksymalna_punkty = '';
    // maksymalna wartosc punktow
    $this->wartosc_maksymalna_kwota = '';

    $this->zapytanie($id_klienta, $minus_produkty_pkt);

  }

  function zapytanie($id_klienta, $minus_produkty_pkt) {

    $zapytanie = "SELECT customers_shopping_points FROM customers WHERE customers_id = '" . (int)$id_klienta . "' LIMIT 1";
    $sql = $GLOBALS['db']->open_query($zapytanie); 
    
    if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {

        $info = $sql->fetch_assoc();
        $this->suma = $info['customers_shopping_points'];
        $this->suma_punktow_klienta = $info['customers_shopping_points'];

        // suma musi sie zmniejszyc 
        if ( $minus_produkty_pkt == true ) {
             //
             $this->suma -= $GLOBALS['koszykKlienta']->KoszykWartoscProduktowZaPunkty();
             //
        }

        $this->wartosc = $GLOBALS['waluty']->PokazCeneBezSymbolu((float)$this->suma / (float)SYSTEM_PUNKTOW_WARTOSC_PRZY_KUPOWANIU,'',true);

        $this->wartosc_maksymalna_punkty = (float)$this->suma;
        if ( (float)$this->suma > (float)SYSTEM_PUNKTOW_MAX_ZAMOWIENIA ) {
          $this->wartosc_maksymalna_punkty = (float)SYSTEM_PUNKTOW_MAX_ZAMOWIENIA;
        }
        $this->wartosc_maksymalna_kwota = $GLOBALS['waluty']->PokazCeneBezSymbolu((float)$this->wartosc_maksymalna_punkty / (float)SYSTEM_PUNKTOW_WARTOSC_PRZY_KUPOWANIU,'',true);
        
        unset($info);

    }
    
    $GLOBALS['db']->close_query($sql);
    unset($zapytanie);

  }

  
  public static function PunktyAktywneDlaKlienta( $id = 0 ) {
    
    $aktywne = false;
    
    if ( $id == 0 ) {
    
        // jezeli klient jest zalogowany
        if ( isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0' && isset($_SESSION['customers_groups_id']) && (int)$_SESSION['customers_groups_id'] > 0 ) {
          
            if (in_array((int)$_SESSION['customers_groups_id'], explode(',', (string)SYSTEM_PUNKTOW_GRUPY_KLIENTOW)) || SYSTEM_PUNKTOW_GRUPY_KLIENTOW == '') {
             
                $aktywne = true;
                
            }
          
        } else {
         
            if ( SYSTEM_PUNKTOW_GRUPY_KLIENTOW == '' ) {
              
                 $aktywne = true;
                 
            }
          
        }
        
    } else {
      
        if ( SYSTEM_PUNKTOW_GRUPY_KLIENTOW != '' ) {
      
            // okresli grupe klienta
            $zapytanie = "SELECT c.customers_id, 
                                 cg.customers_groups_id
                            FROM customers c 
                       LEFT JOIN customers_groups cg ON c.customers_groups_id = cg.customers_groups_id
                           WHERE c.customers_id = '" . $id . "' and c.customers_guest_account = '0'";

            $sql = $GLOBALS['db']->open_query($zapytanie);   
            $info = $sql->fetch_assoc();
            
            if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
                //
                if (in_array($info['customers_groups_id'], explode(',', (string)SYSTEM_PUNKTOW_GRUPY_KLIENTOW)) || SYSTEM_PUNKTOW_GRUPY_KLIENTOW == '') {
                 
                    $aktywne = true;
                    
                }         
                //
            }
    
            $GLOBALS['db']->close_query($sql);
            unset($zapytanie, $info); 
            
        } else {
          
             $aktywne = true;
             
        }        
 
      
    }
    
    return $aktywne;
    
  }
  

  public static function ListaPunktow($id_klienta) {
    global $i18n;
    
    // statusy punktow
    $zapytanie = "SELECT points_status_id, points_status_name FROM customers_points_status_description WHERE language_id = '".(int)$_SESSION['domyslnyJezyk']['id']."'";
    $sql = $GLOBALS['db']->open_query($zapytanie); 
    
    $tablicaStatusow = array();
    
    if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
      
        while ( $info = $sql->fetch_assoc() ) {
            $tablicaStatusow[$info['points_status_id']] = $info['points_status_name'];
        }
        
    }    
    
    $GLOBALS['db']->close_query($sql);
    unset($zapytanie, $info);    

    $lista = array();

    $zapytanie = "SELECT cp.unique_id, cp.orders_id, cp.points_comment, cp.points, cp.date_added AS data_dodania, cp.reviews_id, cp.date_confirm, cp.points_status, cp.points_type, o.date_purchased, o.orders_status, osd.orders_status_name, r.date_added, ci.customers_info_date_account_created
                  FROM customers_points cp
                  LEFT JOIN customers_info ci ON ci.customers_info_id = cp.customers_id
                  LEFT JOIN orders o ON o.orders_id = cp.orders_id
                  LEFT JOIN orders_status_description osd ON osd.orders_status_id = o.orders_status AND osd.language_id = '" . (int)$_SESSION['domyslnyJezyk']['id'] . "'
                  LEFT JOIN reviews r ON r.reviews_id = cp.reviews_id
                  WHERE cp.customers_id = '" . (int)$id_klienta . "' ORDER BY cp.date_added DESC";

    $sql = $GLOBALS['db']->open_query($zapytanie); 
    
    if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
    
      $GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('SYSTEM_PUNKTOW') ), $GLOBALS['tlumacz'] );
    
      while ( $info = $sql->fetch_assoc() ) {

        $lista[$info['unique_id']] = array('id_punktow' => $info['unique_id'],
                                           'id_zamowienia' => $info['orders_id'],
                                           'id_recenzji' => $info['reviews_id'],
                                           'komentarz' => (( isset($GLOBALS['tlumacz']['PUNKTY_' . $info['points_type']]) ) ? $GLOBALS['tlumacz']['PUNKTY_' . $info['points_type']] : $info['points_comment']),
                                           'data_dodania' => date('d-m-Y', FunkcjeWlasnePHP::my_strtotime($info['data_dodania'])),
                                           'data_zatwierdzenia' => ( $info['date_confirm'] != '0000-00-00 00:00:00' ? date('d-m-Y', FunkcjeWlasnePHP::my_strtotime($info['date_confirm'])) : '---'),
                                           'data_zamowienia' => ($info['date_purchased'] != '' ? date('d-m-Y', FunkcjeWlasnePHP::my_strtotime($info['date_purchased'])) : ''),
                                           'data_recenzji' => date('d-m-Y', FunkcjeWlasnePHP::my_strtotime($info['date_added'])),
                                           'data_rejestracji' => date('d-m-Y', FunkcjeWlasnePHP::my_strtotime($info['customers_info_date_account_created'])),
                                           'status' => ((isset($tablicaStatusow[$info['points_status']])) ? $tablicaStatusow[$info['points_status']] : ''),
                                           'status_zamowienia' => $info['orders_status_name'],
                                           'typ' => $info['points_type'],
                                           'ilosc_punktow' => $info['points']);

      }
      
    }
    $GLOBALS['db']->close_query($sql);
    unset($zapytanie, $info, $tablicaStatusow);

    return($lista);

  }

} 

?>