<?php
chdir('../../../../');

require_once('ustawienia/init.php');

$_POST['info'] = '695';
$_POST['status'] = 'anulowanie';
$_POST['secret'] = '39e2db97da14472bb7a548eaf1fa533952f46080';

$e = array();

$zapytanie = "SELECT kod, wartosc FROM modules_payment_params WHERE kod LIKE '%_ILEASING_%'";
$sql = $db->open_query($zapytanie);

while ($info = $sql->fetch_assoc()) {
    if (!defined($info['kod'])) {
        define($info['kod'], $info['wartosc']);
    }
}
$GLOBALS['db']->close_query($sql);
unset($zapytanie, $info, $sql);

if ( !isset($_POST['info']) || !isset($_POST['secret']) ) {
    die('ERROR: EMPTY PARAMETERS'); //-- brak wszystkich parametrow
}

$signature = sha1(PLATNOSC_ILEASING_KEY.$_POST['info'].$_POST['status']);

if ( $_POST['secret'] != $signature ) {
    $e[]=1;
}


if ( count($e) > 0 ) {

    print "PROBLEM: $e[0]";
    exit;

} else {

    $komentarz = 'Status wniosku: ' . $_POST['status'] . '<br />';
    $komentarz .= 'Data modyfikacji: ' . date("d-m-Y H:i:s") . '<br />';

    $zapytanie = "SELECT orders_id FROM orders WHERE orders_id = '" . $_POST['info'] . "' ORDER BY date_purchased DESC LIMIT 1";
    $sql = $db->open_query($zapytanie);

    if ($GLOBALS['db']->ile_rekordow($sql) > 0 ) {

        $info = $sql->fetch_assoc();

        if ( PLATNOSC_ILEASING_STATUS_ZAMOWIENIA > 0 ) {
            $status_zamowienia_id = PLATNOSC_ILEASING_STATUS_ZAMOWIENIA;
        } else {
            $status_zamowienia_id = Funkcje::PokazDomyslnyStatusZamowienia();
        }

        $pola = array(
                array('orders_id',(int)$info['orders_id']),
                array('orders_status_id',(int)$status_zamowienia_id),
                array('date_added','now()'),
                array('customer_notified','0'),
                array('customer_notified_sms','0'),
                array('comments',$komentarz),
                array('transaction_id',''),
                array('transaction_date',date('Y-m-d H:i:s',time())),
                array('transaction_status',$_POST['status'])
        );
        $GLOBALS['db']->insert_query('orders_status_history' , $pola);
        unset($pola);

        // zmina statusu zamowienia
        $pola = array(
                array('orders_status',(int)$status_zamowienia_id),
                array('payment_method_array','#'),
        );
        $GLOBALS['db']->update_query('orders' , $pola, "orders_id = '" . (int)$info['orders_id'] . "'");
        unset($pola);
            
        $platnoscZakonczona = true;


    } 
    $GLOBALS['db']->close_query($sql);
    
    unset($zapytanie, $info, $sql);

    echo 'OK';

}
?>