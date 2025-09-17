<?php
chdir('../');

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone && Sesje::TokenSpr() && Funkcje::SprawdzAktywneAllegro()) {

    $wynikDoAjaxa = '';
    $TablicaAukcji = array();
    $IloscPrzetworzonych = 0;
    $Modification = time();

    $AllegroRest = new AllegroRest( array('allegro_user' => $_SESSION['domyslny_uzytkownik_allegro']) );

    $TablicaTransakcjiAllegro = $AllegroRest->TablicaZdarzenAllegro('READY_FOR_PROCESSING,FILLED_IN,BUYER_CANCELLED,AUTO_CANCELLED');

    if ( isset($TablicaTransakcjiAllegro) && count((array)$TablicaTransakcjiAllegro) ) {

        $TablicaAukcjiSklep = $AllegroRest->TablicaWszystkichAukcjiSklep();

        $TablicaTransakcjiSklep = $AllegroRest->TablicaWszystkichTransakcjiSklep();

        foreach ( $TablicaTransakcjiAllegro as $PrzetwarzanaTransakcja ) {

            $Dodaj = false;

            //if ( $PrzetwarzanaTransakcja->status != 'READY_FOR_PROCESSING'  ) {

                if ( !in_array((string)$PrzetwarzanaTransakcja->id, (array)$TablicaTransakcjiSklep ) ) {

                    $DataUtworzenia = '';
                    $WartoscTowaru = 0;
                    $IloscTowaru = 0;

                    foreach ( $PrzetwarzanaTransakcja->lineItems as $Aukcja ) {

                        $DataUtworzenia = FunkcjeWlasnePHP::my_strtotime($Aukcja->boughtAt);
                        $WartoscTowaru = $WartoscTowaru + ($Aukcja->price->amount * $Aukcja->quantity);
                        $IloscTowaru = $IloscTowaru + $Aukcja->quantity;

                        if ( in_array((string)$Aukcja->offer->id, (array)$TablicaAukcjiSklep) ) {
                            $Dodaj = true;
                        }

                    }

                    if ( $Dodaj ) {
                                
                        // waluta z aukcji
                        $waluta_aukcja = ((isset($PrzetwarzanaTransakcja->summary->totalToPay->currency) ? (string)$PrzetwarzanaTransakcja->summary->totalToPay->currency : $_SESSION['domyslna_waluta']['kod']));
                        
                        $zapytanie_tmp = "select currencies_id from currencies where code = '" . $waluta_aukcja . "'";
                        $sql_tmp = $db->open_query($zapytanie_tmp);
                        
                        if ((int)$db->ile_rekordow($sql_tmp) == 0) {                                
                        
                            $waluta_aukcja = $_SESSION['domyslna_waluta']['kod'];
                            
                        }
                        
                        $db->close_query($sql_tmp);
                        unset($zapytanie_tmp);                                            
                                
                        $pola = array(
                                array('transaction_id',$PrzetwarzanaTransakcja->id),
                                array('auction_seller',(int)$_SESSION['domyslny_uzytkownik_allegro']),

                                array('buyer_id',$PrzetwarzanaTransakcja->buyer->id),
                                array('buyer_email_address',$PrzetwarzanaTransakcja->buyer->email),
                                array('buyer_name',$PrzetwarzanaTransakcja->buyer->login),
                                array('buyer_phone',$PrzetwarzanaTransakcja->buyer->phoneNumber),
                                array('buyer_guest',$PrzetwarzanaTransakcja->buyer->guest),

                                array('post_buy_form_created_date',$DataUtworzenia),

                                array('post_buy_form_it_amount',(float)$WartoscTowaru),
                                array('post_buy_form_it_quantity',(float)$IloscTowaru),

                                array('post_buy_form_invoice_option',$PrzetwarzanaTransakcja->invoice->required),

                                array('post_buy_form_amount',( isset($PrzetwarzanaTransakcja->payment->paidAmount->amount) ? (float)$PrzetwarzanaTransakcja->payment->paidAmount->amount : '')),

                                array('post_buy_form_currency',$waluta_aukcja),
                                array('post_buy_form_pay_id',$PrzetwarzanaTransakcja->payment->id),
                                array('post_buy_form_pay_type',$PrzetwarzanaTransakcja->payment->type),
                                array('post_buy_form_pay_provider',( isset($PrzetwarzanaTransakcja->payment->provider) ? $PrzetwarzanaTransakcja->payment->provider : '')),

                                array('post_buy_form_payment_amount',( isset($PrzetwarzanaTransakcja->payment->paidAmount->amount) ? (float)$PrzetwarzanaTransakcja->payment->paidAmount->amount : '')),

                                array('post_buy_form_pay_status',$PrzetwarzanaTransakcja->status),

                                array('shipping_post_buy_form_adr_country',( isset($PrzetwarzanaTransakcja->delivery->address->countryCode) ? $PrzetwarzanaTransakcja->delivery->address->countryCode : $PrzetwarzanaTransakcja->buyer->address->countryCode )),
                                array('shipping_post_buy_form_adr_street',( isset($PrzetwarzanaTransakcja->delivery->address->street) ? $PrzetwarzanaTransakcja->delivery->address->street : $PrzetwarzanaTransakcja->buyer->address->street ) ),
                                array('shipping_post_buy_form_adr_postcode',( isset($PrzetwarzanaTransakcja->delivery->address->zipCode) ? $PrzetwarzanaTransakcja->delivery->address->zipCode : $PrzetwarzanaTransakcja->buyer->address->postCode ) ),
                                array('shipping_post_buy_form_adr_city', ( isset($PrzetwarzanaTransakcja->delivery->address->city) ? $PrzetwarzanaTransakcja->delivery->address->city : $PrzetwarzanaTransakcja->buyer->address->city ) ),
                                array('shipping_post_buy_form_adr_full_name',( isset($PrzetwarzanaTransakcja->delivery->address->firstName) ? $PrzetwarzanaTransakcja->delivery->address->firstName . ' ' . $PrzetwarzanaTransakcja->delivery->address->lastName : $PrzetwarzanaTransakcja->buyer->firstName . ' ' . $PrzetwarzanaTransakcja->buyer->lastName )),
                                array('shipping_post_buy_form_adr_phone',( isset($PrzetwarzanaTransakcja->delivery->address->phoneNumber) ? $PrzetwarzanaTransakcja->delivery->address->phoneNumber : $PrzetwarzanaTransakcja->buyer->phoneNumber ) ),
                                array('shipping_post_buy_form_adr_company',( isset($PrzetwarzanaTransakcja->delivery->address->companyName) ? $PrzetwarzanaTransakcja->delivery->address->companyName : $PrzetwarzanaTransakcja->buyer->companyName ) ),

                                array('post_buy_form_postage_amount',(float)$PrzetwarzanaTransakcja->delivery->cost->amount),
                                array('post_buy_form_shipment_id',$PrzetwarzanaTransakcja->delivery->method->name),

                                array('post_buy_form_msg_to_seller',$PrzetwarzanaTransakcja->messageToSeller),

                                array('transaction_last_modification', ( $PrzetwarzanaTransakcja->updatedAt != '' ? FunkcjeWlasnePHP::my_strtotime($PrzetwarzanaTransakcja->updatedAt) : $Modification ))
                        );

                        if ( isset($PrzetwarzanaTransakcja->invoice->address) ) {
                                                $pola[] = array('billing_post_buy_form_adr_street',$PrzetwarzanaTransakcja->invoice->address->street);
                                                $pola[] = array('billing_post_buy_form_adr_postcode',$PrzetwarzanaTransakcja->invoice->address->zipCode);
                                                $pola[] = array('billing_post_buy_form_adr_city',$PrzetwarzanaTransakcja->invoice->address->city);
                                                $pola[] = array('billing_post_buy_form_adr_country',$PrzetwarzanaTransakcja->invoice->address->countryCode);
                        }

                        if ( isset($PrzetwarzanaTransakcja->invoice->address->company) ) {
                                                $pola[] = array('billing_post_buy_form_adr_company',$PrzetwarzanaTransakcja->invoice->address->company->name);
                                                $pola[] = array('billing_post_buy_form_adr_nip',$PrzetwarzanaTransakcja->invoice->address->company->taxId);
                        }
                        if ( isset($PrzetwarzanaTransakcja->invoice->address->naturalPerson) ) {
                                                $pola[] = array('billing_post_buy_form_adr_full_name',$PrzetwarzanaTransakcja->invoice->address->naturalPerson->firstName . ' ' . $PrzetwarzanaTransakcja->invoice->address->naturalPerson->lastName);
                        }

                        if ( isset($PrzetwarzanaTransakcja->delivery->pickupPoint) ) {
                                                $pola[] = array('post_buy_form_shipping_destinationcode',$PrzetwarzanaTransakcja->delivery->pickupPoint->id);
                        }
                        if ( isset($PrzetwarzanaTransakcja->delivery->pickupPoint) ) {
                                                $pola[] = array('post_buy_form_shipment_info',$PrzetwarzanaTransakcja->delivery->pickupPoint->address->street . ', ' . $PrzetwarzanaTransakcja->delivery->pickupPoint->address->zipCode . ', ' . $PrzetwarzanaTransakcja->delivery->pickupPoint->address->city);
                        }

                        $id_dodanej_pozycji = $db->insert_query('allegro_transactions' , $pola, '', false, true);

                        foreach ( $PrzetwarzanaTransakcja->lineItems as $Aukcja ) {

                            $pola_produkt = array(
                                            array('auction_id',$Aukcja->offer->id),
                                            array('transaction_id',$id_dodanej_pozycji),
                                            array('buyer_id',$PrzetwarzanaTransakcja->buyer->id),
                                            array('auction_product_local_id', ( isset($Aukcja->offer->external->id) ? $Aukcja->offer->external->id : '' ) ),
                                            array('auction_product_name',$Aukcja->offer->name),
                                            array('auction_quantity',(int)$Aukcja->quantity),
                                            array('auction_price',(float)$Aukcja->price->amount),
                                            array('auction_postbuy_forms',$PrzetwarzanaTransakcja->id),
                                            array('auction_lineitem_id',$Aukcja->id),
                                            array('auction_buy_date',FunkcjeWlasnePHP::my_strtotime($Aukcja->boughtAt)),
                                            array('date_last_modified',$Modification)
                            );

                            $db->insert_query('allegro_auctions_sold' , $pola_produkt, '', false, true);
                            unset($pola_produkt);

                        }

                        //$wynikDoAjaxa .= $PrzetwarzanaTransakcja->id . ' - transakcja została przetworzona<br />';

                        $IloscPrzetworzonych++;

                        unset($pola);

                    }

                } else {

                    if ( $PrzetwarzanaTransakcja->status == 'READY_FOR_PROCESSING'  ) {

                        $zapytanie = "SELECT orders_id, allegro_transaction_id, transaction_id, post_buy_form_pay_status FROM allegro_transactions WHERE transaction_id = '".$PrzetwarzanaTransakcja->id."' AND  post_buy_form_pay_status != 'READY_FOR_PROCESSING'";
                        $sql = $db->open_query($zapytanie);

                        if ( (int)$db->ile_rekordow($sql) > 0 ) {

                            $info = $sql->fetch_assoc();

                            $pola = array(

                                    array('post_buy_form_invoice_option',$PrzetwarzanaTransakcja->invoice->required),

                                    array('post_buy_form_amount',(float)$PrzetwarzanaTransakcja->payment->paidAmount->amount),
                                    array('post_buy_form_pay_id',$PrzetwarzanaTransakcja->payment->id),
                                    array('post_buy_form_pay_type',$PrzetwarzanaTransakcja->payment->type),
                                    array('post_buy_form_pay_provider',( isset($PrzetwarzanaTransakcja->payment->provider) ? $PrzetwarzanaTransakcja->payment->provider : '')),

                                    array('post_buy_form_payment_amount',(float)$PrzetwarzanaTransakcja->payment->paidAmount->amount),

                                    array('post_buy_form_pay_status',$PrzetwarzanaTransakcja->status),

                                    array('shipping_post_buy_form_adr_country',( isset($PrzetwarzanaTransakcja->delivery->address->countryCode) ? $PrzetwarzanaTransakcja->delivery->address->countryCode : $PrzetwarzanaTransakcja->buyer->address->countryCode )),
                                    array('shipping_post_buy_form_adr_street',( isset($PrzetwarzanaTransakcja->delivery->address->street) ? $PrzetwarzanaTransakcja->delivery->address->street : $PrzetwarzanaTransakcja->buyer->address->street ) ),
                                    array('shipping_post_buy_form_adr_postcode',( isset($PrzetwarzanaTransakcja->delivery->address->zipCode) ? $PrzetwarzanaTransakcja->delivery->address->zipCode : $PrzetwarzanaTransakcja->buyer->address->postCode ) ),
                                    array('shipping_post_buy_form_adr_city', ( isset($PrzetwarzanaTransakcja->delivery->address->city) ? $PrzetwarzanaTransakcja->delivery->address->city : $PrzetwarzanaTransakcja->buyer->address->city ) ),
                                    array('shipping_post_buy_form_adr_full_name',( isset($PrzetwarzanaTransakcja->delivery->address->firstName) ? $PrzetwarzanaTransakcja->delivery->address->firstName . ' ' . $PrzetwarzanaTransakcja->delivery->address->lastName : $PrzetwarzanaTransakcja->buyer->firstName . ' ' . $PrzetwarzanaTransakcja->buyer->lastName )),
                                    array('shipping_post_buy_form_adr_phone',( isset($PrzetwarzanaTransakcja->delivery->address->phoneNumber) ? $PrzetwarzanaTransakcja->delivery->address->phoneNumber : $PrzetwarzanaTransakcja->buyer->phoneNumber ) ),
                                    array('shipping_post_buy_form_adr_company',( isset($PrzetwarzanaTransakcja->delivery->address->companyName) ? $PrzetwarzanaTransakcja->delivery->address->companyName : $PrzetwarzanaTransakcja->buyer->companyName ) ),

                                    array('post_buy_form_postage_amount',(float)$PrzetwarzanaTransakcja->delivery->cost->amount),
                                    array('post_buy_form_shipment_id',$PrzetwarzanaTransakcja->delivery->method->name),

                                    array('post_buy_form_msg_to_seller',$PrzetwarzanaTransakcja->messageToSeller),

                                    array('transaction_last_modification', ( $PrzetwarzanaTransakcja->updatedAt != '' ? FunkcjeWlasnePHP::my_strtotime($PrzetwarzanaTransakcja->updatedAt) : $Modification ))
                            );

                            if ( isset($PrzetwarzanaTransakcja->invoice->address) ) {
                                                    $pola[] = array('billing_post_buy_form_adr_street',$PrzetwarzanaTransakcja->invoice->address->street);
                                                    $pola[] = array('billing_post_buy_form_adr_postcode',$PrzetwarzanaTransakcja->invoice->address->zipCode);
                                                    $pola[] = array('billing_post_buy_form_adr_city',$PrzetwarzanaTransakcja->invoice->address->city);
                                                    $pola[] = array('billing_post_buy_form_adr_country',$PrzetwarzanaTransakcja->invoice->address->countryCode);
                            }

                            if ( isset($PrzetwarzanaTransakcja->invoice->address->company) ) {
                                                    $pola[] = array('billing_post_buy_form_adr_company',$PrzetwarzanaTransakcja->invoice->address->company->name);
                                                    $pola[] = array('billing_post_buy_form_adr_nip',$PrzetwarzanaTransakcja->invoice->address->company->taxId);
                            }
                            if ( isset($PrzetwarzanaTransakcja->invoice->address->naturalPerson) ) {
                                                    $pola[] = array('billing_post_buy_form_adr_full_name',$PrzetwarzanaTransakcja->invoice->address->naturalPerson->firstName . ' ' . $PrzetwarzanaTransakcja->invoice->address->naturalPerson->lastName);
                            }

                            if ( isset($PrzetwarzanaTransakcja->delivery->pickupPoint) ) {
                                                    $pola[] = array('post_buy_form_shipping_destinationcode',$PrzetwarzanaTransakcja->delivery->pickupPoint->id);
                            } else {
                                                    $pola[] = array('post_buy_form_shipping_destinationcode','');
                            }
                            if ( isset($PrzetwarzanaTransakcja->delivery->pickupPoint) ) {
                                                    $pola[] = array('post_buy_form_shipment_info',$PrzetwarzanaTransakcja->delivery->pickupPoint->address->street . ', ' . $PrzetwarzanaTransakcja->delivery->pickupPoint->address->zipCode . ', ' . $PrzetwarzanaTransakcja->delivery->pickupPoint->address->city);
                            } else {
                                                    $pola[] = array('post_buy_form_shipment_info','');
                            }

                            $db->update_query('allegro_transactions' , $pola, " allegro_transaction_id = '".(int)$info['allegro_transaction_id']."'");

                            $IloscPrzetworzonych++;

                            unset($pola);

                        }

                        $db->close_query($sql);
                        unset($info, $zapytanie);            

                    }
                    //$wynikDoAjaxa .= $PrzetwarzanaTransakcja->id . ' - ' .$PrzetwarzanaTransakcja->status.'<br />';
                }

            //}

        }

    }

    $wynikDoAjaxa .= 'rek_'.$IloscPrzetworzonych;

    echo $wynikDoAjaxa;
    unset($wynikDoAjaxa);

}
?>