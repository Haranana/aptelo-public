<?php

chdir('../');

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

$TablicaTransakcji = array();

$zapytanieTR = "SELECT orders_id, allegro_transaction_id, buyer_name FROM allegro_transactions WHERE auction_seller = '".$_SESSION['domyslny_uzytkownik_allegro']."' AND (orders_id = '' OR orders_id = '0')";

$sqlTR = $db->open_query($zapytanieTR);

if ((int)$db->ile_rekordow($sqlTR) > 0) {

    while ($infoTR = $sqlTR->fetch_assoc()) {

            $TablicaAukcji2 = array();

            $zapytanieTMP = "SELECT auction_id, transaction_id FROM allegro_auctions_sold WHERE transaction_id = '".$infoTR['allegro_transaction_id']."'";
            $sqlTMP = $db->open_query($zapytanieTMP);
            while ($infoTMP = $sqlTMP->fetch_assoc()) {

                $TablicaAukcji2[] = $infoTMP['auction_id'];

            }
            $db->close_query($sqlTMP);
            unset($zapytanieTMP, $infoTMP);

            sort($TablicaAukcji2);

            $SzukaneAukcje = implode(',', (array)$TablicaAukcji2).','.$infoTR['buyer_name'];

            $TablicaTransakcji[$infoTR['allegro_transaction_id']] = $SzukaneAukcje;

            unset($TablicaAukcji2);

    }

    $zapytanie = "SELECT orders_id, comments FROM orders_status_history WHERE comments LIKE '%Dotyczy aukcji%'";
    $sql = $db->open_query($zapytanie);

    while ($info = $sql->fetch_assoc()) {

        $TablicaAukcji1 = array();
        $Komentarz = preg_replace("/\Nick[^)]+/", '', (string)$info['comments']);

        $AukcjeZamowienia = preg_replace("/[^0-9,]/", '', $Komentarz);

        $Nick = explode(':', strip_tags((string)$info['comments']));

        $TablicaAukcji1 = explode(',', (string)$AukcjeZamowienia);
        sort($TablicaAukcji1);

        $szukaneAukcje = implode(',', (array)$TablicaAukcji1).','.trim(str_replace('Informacja od kupującego', '', (string)$Nick['2']));

        $key = array_search($szukaneAukcje, $TablicaTransakcji);
        if (false !== $key) {

            $pola = array(
                    array('orders_id',$info['orders_id'])
            );

            $db->update_query('allegro_transactions' , $pola, " allegro_transaction_id = '".(int)$key."'");

        }

        unset($TablicaAukcji1);

    }
    $db->close_query($sql);
    unset($zapytanie, $info);
}

$db->close_query($sqlTR);
unset($zapytanieTR, $infoTR);

$pola = array(
        array('migracja','0')
);

$db->update_query('allegro_users' , $pola, " allegro_user_id = '".(int)$_SESSION['domyslny_uzytkownik_allegro']."'");

Funkcje::PrzekierowanieURL('allegro_sprzedaz.php');

?>