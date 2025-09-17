<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

        if ( $_SESSION['domyslny_uzytkownik_allegro'] ) {

            $blad = false;

            $AllegroRest = new AllegroRest( array('allegro_user' => $_SESSION['domyslny_uzytkownik_allegro']) );

            $zapytanie = "SELECT * FROM allegro_auctions WHERE allegro_id = '" . (int)$_GET['id_poz'] . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
            
                $Modification = time();

                $info = $sql->fetch_assoc();

                $wynik = $AllegroRest->commandRequest('sale/offer-publication-commands/'.$info['auction_uuid'], '', 'tasks' );

                if ( is_object($wynik) && $wynik->tasks['0']->status == 'SUCCESS' ) {

                    $PrzetwarzanaAukcja = $AllegroRest->AukcjaSzczegoly($info['auction_id']);


                    if ( isset($PrzetwarzanaAukcja) && count($PrzetwarzanaAukcja) > 0 ) {

                        $pola = array(
                                array('auction_status',$PrzetwarzanaAukcja->publication->status),
                                array('auction_hits',$PrzetwarzanaAukcja->stats->visitsCount),
                                array('products_sold',$PrzetwarzanaAukcja->stock->sold),
                                array('auction_watching',$PrzetwarzanaAukcja->stats->watchersCount)
                            );

                        if ( $PrzetwarzanaAukcja->publication->status != 'ACTIVATING' ) {
                            $pola[] = array('auction_uuid','');
                        }

                        if ( $PrzetwarzanaAukcja->publication->endedAt != '' ) {
                            $pola[] = array('products_date_end',date('Y-m-d G:i:s',FunkcjeWlasnePHP::my_strtotime($PrzetwarzanaAukcja->publication->endedAt)));
                            $pola[] = array('auction_date_end',date('Y-m-d G:i:s',FunkcjeWlasnePHP::my_strtotime($PrzetwarzanaAukcja->publication->endedAt)));
                        }
                        if ( $PrzetwarzanaAukcja->publication->endingAt != '' ) {
                            $pola[] = array('products_date_end',date('Y-m-d G:i:s',FunkcjeWlasnePHP::my_strtotime($PrzetwarzanaAukcja->publication->endingAt)));
                            $pola[] = array('auction_date_end',date('Y-m-d G:i:s',FunkcjeWlasnePHP::my_strtotime($PrzetwarzanaAukcja->publication->endingAt)));
                        }
                        
                        // jezeli jest z opoznionym zaplonem
                        if ( isset($PrzetwarzanaAukcja->publication->startedAt) && !empty($PrzetwarzanaAukcja->publication->startedAt) ) {
                             //
                             $pola[] = array('products_date_start',date('Y-m-d G:i:s',FunkcjeWlasnePHP::my_strtotime($PrzetwarzanaAukcja->publication->startedAt)));
                             $pola[] = array('auction_date_start',date('Y-m-d G:i:s',FunkcjeWlasnePHP::my_strtotime($PrzetwarzanaAukcja->publication->startedAt)));
                             //
                        } else if ( isset($PrzetwarzanaAukcja->publication->startingAt) && !empty($PrzetwarzanaAukcja->publication->startingAt) ) {
                             //
                             $pola[] = array('products_date_start',date('Y-m-d G:i:s',FunkcjeWlasnePHP::my_strtotime($PrzetwarzanaAukcja->publication->startingAt)));
                             $pola[] = array('auction_date_start',date('Y-m-d G:i:s',FunkcjeWlasnePHP::my_strtotime($PrzetwarzanaAukcja->publication->startingAt)));
                             //
                        }
                        
                        $db->update_query('allegro_auctions' , $pola, " allegro_id = '".(int)$_GET['id_poz']."'");

                        unset($pola);
                    }

                } else {

                    $blad = true;

                }

            }

            $db->close_query($sql);
            unset($zapytanie);

            if ( $blad == false ) {
                Funkcje::PrzekierowanieURL('allegro_aukcje.php?id_poz='.(int)$_POST["id"].'');
            }
            
        }
          

}

?>