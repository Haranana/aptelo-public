<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

  $wynik = '';

  if ( Funkcje::SprawdzAktywneAllegro() ) {

    $AllegroRest = new AllegroRest( array('allegro_user' => $_SESSION['domyslny_uzytkownik_allegro']) );

    /*
    //oznaczenie aukcji do archiwum
    $AukcjeArchiwum = time() - 2592000;

    $zapytanie = "
        SELECT allegro_id, auction_id, auction_last_modification 
        FROM allegro_auctions 
        WHERE auction_seller = '".$_SESSION['domyslny_uzytkownik_allegro']."' AND archiwum_allegro != '1' AND (auction_last_modification < '".$AukcjeArchiwum."' OR auction_uuid != '' )
    ";

    $sql = $db->open_query($zapytanie);

    if ( $db->ile_rekordow($sql) > 0 ) {

        while ($info = $sql->fetch_assoc()) {

            $DaneWejsciowe = $info['auction_id']; 
            $PrzetwarzanaAukcja = $AllegroRest->commandRequest('sale/offers', $DaneWejsciowe, '' );
                  
            if ( isset($PrzetwarzanaAukcja->errors) ) {

                $val = 'NOT_FOUND';
                foreach($PrzetwarzanaAukcja->errors as $obj) {
                    if ($val == $obj->code) {

                        $pola = array(
                                array('auction_uuid',''),
                                array('archiwum_allegro','1'),
                                array('auction_status','NOT_FOUND')
                        );
                        $db->update_query('allegro_auctions' , $pola, " auction_id = '".$info['auction_id']."'");

                    }
                }

            }

            if ( isset($PrzetwarzanaAukcja->publication->status) && $PrzetwarzanaAukcja->publication->status == 'ARCHIVED' ) {

                $pola = array(
                        array('auction_uuid',''),
                        array('auction_date_end',date('Y-m-d G:i:s',FunkcjeWlasnePHP::my_strtotime($PrzetwarzanaAukcja->publication->endingAt))),
                        array('auction_status',$PrzetwarzanaAukcja->publication->status),
                        array('allegro_ended_by',$PrzetwarzanaAukcja->publication->endedBy),
                        array('archiwum_allegro','1')
                );
                $db->update_query('allegro_auctions' , $pola, " auction_id = '".$info['auction_id']."'");

            }

            unset($DaneWejsciowe, $PrzetwarzanaAukcja);

       }
       
    }
    $db->close_query($sql);
    unset($zapytanie, $info);
    */

    // aktualizowanie danych o promocjach - rabaty od ilosci i zestawy
    $Promocje = array();

    $DanePromocji = $AllegroRest->commandGet('sale/loyalty/promotions?user.id=' . $AllegroRest->ParametryPolaczenia['UserId'] . '&limit=1000&promotionType=MULTIPACK');

    if ( is_object($DanePromocji) ) {
        if ( !isset($DanePromocji->errors) && ( isset($DanePromocji->promotions) && count($DanePromocji->promotions) > 0 ) ) {

            $pola = array(
                    array('allegro_benefits',''),
                    array('allegro_benefits_quantity',0),
                    array('allegro_benefits_discount',0),
                    array('allegro_benefits_status',0));

            $db->update_query('allegro_auctions' , $pola, " allegro_id > 0 AND auction_seller = '" . (int)$_SESSION['domyslny_uzytkownik_allegro'] . "'");             
            unset($pola);        
                
            $db->delete_query('allegro_benefits_set' , " allegro_benefits_set_auction_seller = '" . (int)$_SESSION['domyslny_uzytkownik_allegro'] . "'");                   
      
            // aktualizowanie rabatow
            
            for ( $x = 0; $x < count($DanePromocji->promotions); $x++ ) {
                  //
                  if ( $DanePromocji->promotions[$x]->benefits[0]->specification->type == 'UNIT_PERCENTAGE_DISCOUNT' ) {
                       //
                       $Promocje[$x]['id'] = $DanePromocji->promotions[$x]->id;
                       $Promocje[$x]['procent'] = $DanePromocji->promotions[$x]->benefits[0]->specification->configuration->percentage;
                       $Promocje[$x]['ilosc'] = $DanePromocji->promotions[$x]->benefits[0]->specification->trigger->forEachQuantity;
                       $Promocje[$x]['status'] = $DanePromocji->promotions[$x]->status;
                       //
                       $nr_aukcji = array();
                       //
                       foreach ( $DanePromocji->promotions[$x]->offerCriteria[0]->offers as $aukcja ) {
                           //
                           $nr_aukcji[] = $aukcja->id;
                           //
                       }
                       //
                       $Promocje[$x]['nr_aukcji'] = implode(',', (array)$nr_aukcji);
                       //
                  }
                  //
            }    

            foreach ( $Promocje as $Aukcja ) {
                //
                $Tmp = explode(',', (string)$Aukcja['nr_aukcji']);
                //
                foreach ( $Tmp as $TmpAukcja ) {
                    //
                    $pola = array(
                            array('allegro_benefits',$Aukcja['id']),
                            array('allegro_benefits_quantity',$Aukcja['ilosc']),
                            array('allegro_benefits_discount',$Aukcja['procent']),
                            array('allegro_benefits_status', (($Aukcja['status'] == 'ACTIVE') ? '1' : '0')));

                    $db->update_query('allegro_auctions' , $pola, " auction_id = '" . $TmpAukcja . "'");             
                    unset($pola);                 
                    //
                }
                //
            }
            
            // aktualizowanie zestawow
            
            $u = 0;
        
            for ( $x = 0; $x < count($DanePromocji->promotions); $x++ ) {
                  //
                  if ( $DanePromocji->promotions[$x]->benefits[0]->specification->type == 'ORDER_FIXED_DISCOUNT' && $DanePromocji->promotions[$x]->status == 'ACTIVE' ) {
                       //
                       foreach ( $DanePromocji->promotions[$x]->offerCriteria[0]->offers as $aukcja ) {
                           //
                           $Zestawy[ $u ]['id'] = $aukcja->id;
                           $Zestawy[ $u ]['ilosc'] = $aukcja->quantity;
                           $Zestawy[ $u ]['widoczny'] = $aukcja->promotionEntryPoint;
                           $Zestawy[ $u ]['id_zestawu'] = $DanePromocji->promotions[$x]->id;
                           $Zestawy[ $u ]['kwota'] = $DanePromocji->promotions[$x]->benefits[0]->specification->value->amount;  
                           //
                           $u++;
                           //
                       }
                       //
                  }
                  //
            }      
            
            unset($u);

            if ( isset($Zestawy) && count($Zestawy) > 0 ) {
                foreach ( $Zestawy as $Zestaw ) {
                    //
                    $pola = array(
                            array('allegro_benefits_set_auction_id',$Zestaw['id']),
                            array('allegro_benefits_set_auction_quantity',(int)$Zestaw['ilosc']),
                            array('allegro_benefits_set_auction_view',$Zestaw['widoczny']),
                            array('allegro_benefits_set_amount',(float)$Zestaw['kwota']),
                            array('allegro_benefits_set_id_set',$Zestaw['id_zestawu']),
                            array('allegro_benefits_set_auction_seller',(int)$_SESSION['domyslny_uzytkownik_allegro']));

                    $db->insert_query('allegro_benefits_set' , $pola);             
                    unset($pola);                 
                    //
                }        
            }
        }
    }
    
    unset($Promocje, $DanePromocji);

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    <div id="naglowek_cont">Synchronizacja Allegro</div>
    <div id="cont">

      <div class="poleForm">

          <div class="naglowek">Pobieranie informacji z serwisu Allegro</div>

              <div class="pozycja_edytowana">    

                <div id="import">
                      
                  <div id="postep">Postęp importu ...</div>
                      
                  <div id="suwak">
                    <div style="margin:1px;overflow:hidden">
                      <div id="suwak_aktywny"></div>
                    </div>
                  </div>
                          
                  <div id="procent"></div>  

                  <div id="wynik" class="ListaAukcji" style="margin-top:10px;"></div>
                  
                </div>   
                      
                <div id="zaimportowano" style="display:none">
                  Dane w sklepie zostały zaktualizowane ...
                </div>
                
                <?php
                //$TablicaWszystkichAukcji = $AllegroRest->TablicaWszystkichAukcjiSklep($_SESSION['domyslny_uzytkownik_allegro']);
                //if ( isset($TablicaWszystkichAukcji) && is_array($TablicaWszystkichAukcji) ) {
                //    $ilosc_rekordow = count($TablicaWszystkichAukcji);
                //} else {
                //    $ilosc_rekordow = $AllegroRest->IloscWystawionychAllegro();
                //}
                $ilosc_rekordow = $AllegroRest->IloscWystawionychAllegro();
                $liczba_linii = $ilosc_rekordow;
                ?>

                <script>
                //
                var ilosc_rekordow = <?php echo $ilosc_rekordow; ?>;
                var ilosc_linii = <?php echo $liczba_linii; ?>;
                var licznik_rekordow = 0;
                //

                function import_danych(offset, limit) {
                  
                  $.post( "allegro/allegro_import_aukcji.php?tok=<?php echo Sesje::Token(); ?>", 
                    { 
                      offset           : offset,
                      limit            : 100,
                      synch_zero       : '<?php echo ((isset($_POST['stan_zero'])) ? (int)$_POST['stan_zero'] : '0'); ?>',
                      synch_promowanie : '<?php echo ((isset($_POST['stan_promowanie'])) ? (int)$_POST['stan_promowanie'] : '0'); ?>',
                      synch_prowizja   : '<?php echo ((isset($_POST['stan_prowizja'])) ? (int)$_POST['stan_prowizja'] : '0'); ?>'
                    },
                    function(data) {

                      if (ilosc_linii <= 100) {
                        procent = 100;
                      } else {
                        procent = parseInt((offset / (ilosc_linii - 1)) * 100);
                        if (procent > 100) {
                          procent = 100;
                        }
                      }

                      $('#procent').html('Stopień realizacji: <span id="ile_procent">' + procent + '%</span><br />Przetworzono: <span id="licz_produkty">' + licznik_rekordow + '</span>');    

                      $('#suwak_aktywny').css('width' , (procent * 5) + 5 + 'px');

                      if (ilosc_linii - 100 > offset) {
                        import_danych(offset + 100 , limit);
                      } else {
                        $('#ile_procent').html('100%');
                        $('#postep').css('display','none');
                        $('#suwak').slideUp("fast");
                        $('#zaimportowano').slideDown("fast");
                        $('#przyciski').slideDown("fast");
                      }   
                      if (data != '') {
                        var znacznik_ilosci = 'rek_';
                        var lastIndex = data.lastIndexOf(znacznik_ilosci);
                        var last_line;
                        last_line = parseInt(data.substr(lastIndex + znacznik_ilosci.length));

                        licznik_rekordow = licznik_rekordow + last_line;
                        $('#licz_produkty').html(licznik_rekordow);

                        data = data.substring(0, lastIndex);

                        $('#wynik').html( $('#wynik').html() + data );

                      }
                      
                    }
                  );
                }; 
                //
                import_danych(0, 0);              
                </script> 
                
                <?php
                $Znacznik = time();
                $pola = array(
                        array('value',$Znacznik));
                        
                $db->update_query('allegro_connect' , $pola, " params = 'CONF_LAST_SYNCHRONIZATION'");
                
                if ( isset($_POST['powrot']) && $_POST['powrot'] != '' ) {
                
                  $powrot = $_POST['powrot'];
                  
                } else {
                
                  $powrot = 'allegro_aukcje';
                  
                }
                unset($Znacznik,$pola);
                ?>
                
                <div class="przyciski_dolne" id="przyciski" style="padding-left:0px; display:none">
                  <button type="button" class="przyciskNon" onclick="cofnij('<?php echo $powrot; ?>','<?php echo Funkcje::Zwroc_Get(array('x','y','sprzedaz','wybrane')); ?>','allegro');">Powrót</button> 
                </div>                    

              </div>

          </div>                      
      
      </div>
    </div>
    
    <?php
    include('stopka.inc.php');
    
  } else {
  
    Funkcje::PrzekierowanieURL('index.php');
    
  }


}

?>