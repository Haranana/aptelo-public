<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

  if ( isset($_POST) && count($_POST) > 0 ) {

      $kategorie_do_eksportu = '';
      if ( isset($_POST['id_kat']) && is_array($_POST['id_kat']) ) {
        $kategorie_do_eksportu = implode(',', (array)$_POST['id_kat']);
      }
      $zapytanie_produkty = "
                            SELECT DISTINCT
                                      pd.products_name,
                                      p.products_id,
                                      c.categories_id
                            FROM products p FORCE INDEX (idx_products_status)
                            LEFT JOIN products_description pd ON p.products_id = pd.products_id AND pd.language_id = '".$_POST['jezyk']."'
                            LEFT JOIN products_to_categories p2c ON p2c.products_id = p.products_id
                            JOIN categories c ON c.categories_id = p2c.categories_id
      ";

      if ( $_POST['zakres'] == '1' && $kategorie_do_eksportu != '' ) {
        $zapytanie_produkty .= " AND p2c.categories_id IN (".$kategorie_do_eksportu.")";
      }

      $zapytanie_produkty .= " GROUP BY p.products_id";

      $sql_ilosc = $db->open_query($zapytanie_produkty);

      $ilosc_rekordow = (int)$db->ile_rekordow($sql_ilosc);

      $liczba_linii = $ilosc_rekordow;
      if ( $ilosc_rekordow <= 100 ) {
        $limit        = '5';
      } elseif ( $ilosc_rekordow > 100 && $ilosc_rekordow <= 1000) {
        $limit        = '50';
      } elseif ( $ilosc_rekordow > 1000 && $ilosc_rekordow <= 10000) {
        $limit        = '500';
      } elseif ( $ilosc_rekordow > 10000) {
        $limit        = '1000';
      }

      $dane = serialize($_POST);

      // wczytanie naglowka HTML
      include('naglowek.inc.php');
      ?>
        
      <div id="naglowek_cont">Aktualizacja danych META</div>
      <div id="cont">

        <div class="poleForm">
          <div class="naglowek">Aktualizacja danych META</div>

          <div class="pozycja_edytowana">

            <div id="import">

              <div id="postep">Postęp importu ...</div>

              <div id="suwak">
                <div style="margin:1px;overflow:hidden">
                  <div id="suwak_aktywny"></div>
                </div>
              </div>

              <div id="procent"></div>  
            </div>   

            <div id="zaimportowano" style="display:none">
              Dane zostały zaktualizowane
            </div>

            <script>
            //
            var ilosc_rekordow   = <?php echo $ilosc_rekordow; ?>;
            var ilosc_linii      = <?php echo $liczba_linii; ?>;
            var licznik_rekordow = 0;
            var limit            = <?php echo $limit; ?>;
            var dane             = '<?php echo $dane; ?>';
            //

            function import_danych(offset) {

              $.post( "pozycjonowanie/meta_tagi_automat_wypelnij.php?tok=<?php echo Sesje::Token(); ?>", 
                    { 
                    offset         : offset,
                    limit          : limit,
                    limit_max      : ilosc_rekordow,
                    ilosc_rekordow : ilosc_rekordow,
                    dane           : dane,
                    },
                    function(data) {
                        if (ilosc_linii <= 1) {
                          procent = 100;
                        } else {
                          procent = parseInt((offset / (ilosc_linii - 1)) * 100);
                          if (procent > 100) {
                            procent = 100;
                          }
                        }

                        $('#procent').html('Stopień realizacji: <span>' + procent + '%</span><br />Przetworzono: <span id="licz_produkty">' + licznik_rekordow + '</span>');    

                        $('#suwak_aktywny').css('width' , (procent * 5) + 5 + 'px');

                        if (ilosc_linii - 1 > offset) {
                          import_danych(offset + limit);
                        } else {
                          $('#postep').css('display','none');
                          $('#suwak').slideUp("fast");
                          $('#zaimportowano').slideDown("fast");
                          $('#przyciski').slideDown("fast");
                        }   
                        if (data != '') {
                          licznik_rekordow = licznik_rekordow + limit;
                          if (licznik_rekordow > ilosc_rekordow ) {
                            licznik_rekordow = ilosc_rekordow;
                          }
                          $('#licz_produkty').html(licznik_rekordow);
                        }
                    }
                    );
            }; 
            //
            import_danych(0);              
            </script>

            <div class="przyciski_dolne" id="przyciski" style="padding-left:0px; display:none">
              <button type="button" class="przyciskNon" onclick="cofnij('meta_tagi_automat','<?php echo Funkcje::Zwroc_Get(array('x','y')); ?>','pozycjonowanie');">Powrót</button> 
            </div>                    

          </div>
        </div>                      

      </div> 
    
    <?php
  } else {
    Funkcje::PrzekierowanieURL('meta_tagi_automat.php');
  }

  include('stopka.inc.php');

}