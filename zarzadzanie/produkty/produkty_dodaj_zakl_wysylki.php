<?php if ($prot->wyswietlStrone) { ?>

<div id="zakl_id_15" style="display:none;">

    <div class="info_content">

        <script type="text/javascript" src="javascript/jquery.multi-select.js"></script>
        <script type="text/javascript" src="javascript/jquery.application.js"></script>
        
        <div class="CechyNaglowek">Wybierz wysyłki dostępne dla tego produktu</div>
        
        <div class="CechyInfo">
            <div class="ostrzezenie">
                Jeżeli nie zostanie wybrana żadna wysyłka będzie to równoznaczne z dostępności wszystkich aktywnych w sklepie metod wysyłki
            </div>
        </div>
        
        <?php
        echo '<p>';
        
        echo '<label>Dostępne metody wysyłki:</label>';

        $wszystkie_wysylki_tmp = Array();
        $tablica_wysylek = array();
        $wszystkie_wysylki_tmp = Moduly::TablicaWysylekId();
        if ( isset($prod['shipping_method']) ) {
          $tablica_wysylek = explode(';', (string)$prod['shipping_method']);
        }

        echo '<select name="metody_wysylki[]" multiple="multiple" id="multipleHeaders">';
        foreach ( $wszystkie_wysylki_tmp as $value ) {
          $wybrany = '';
          if ( count($tablica_wysylek) > 0 ) {
            if ( in_array((string)$value['id'], $tablica_wysylek ) ) {
              $wybrany = 'selected="selected"';
            }
          }
          echo '<option value="'.$value['id'].'" '.$wybrany.'>'.$value['text'].'</option>';
        }
        echo '</select>';
        
        echo '</p>';
        ?>

    </div>

</div> 

<?php } ?>