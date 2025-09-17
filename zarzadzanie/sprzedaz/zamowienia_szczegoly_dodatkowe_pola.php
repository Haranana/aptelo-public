<?php
if ( (int)$db->ile_rekordow($sql_pola) > 0  ) {
?>

<div class="ObramowanieTabeli" style="margin-top:10px;">

    <table class="listing_tbl ListaDodatkowa">

        <tr class="div_naglowek">
          <td colspan="2">Dodatkowe informacje do zamówienia</td>
        </tr>
        
        <?php
        while ( $dodatkowePola = $sql_pola->fetch_assoc() ) {
          $wartoscPola = '';

          echo '<tr><td>' . $dodatkowePola['fields_name'] . ':</td>';
      
          $wartosc_zapytanie = "
          SELECT ote.value FROM orders_to_extra_fields ote 
          WHERE orders_id = '" . (int)$_GET['id_poz'] . "' 
          AND fields_id = '" . $dodatkowePola['fields_id'] . "'
          ";
          $wartosc_info = $db->open_query($wartosc_zapytanie);
          
          $wartosc = $wartosc_info->fetch_assoc();
          
          $db->close_query($wartosc_info);
          
          echo '<td class="InfoNormal">';
          
          unset($wartosc_zapytanie);     
          
          $Edytuj = true;

          if ( $dodatkowePola['fields_input_type'] == '5' ) {

            $Edytuj = false;

            if ( isset($wartosc['value']) && $wartosc['value'] != '' ) {
                $NazwaPliku = pathinfo($wartosc['value'],PATHINFO_FILENAME);
                $RozszerzeniePliku = pathinfo($wartosc['value'],PATHINFO_EXTENSION);

                $TablicaTmp = explode('_', $NazwaPliku);

                $NazwaOryginalnaOdkodowana = base64_decode(str_replace(['-','_'], ['+','/'], ( isset($TablicaTmp['1']) ? $TablicaTmp['1'] : $NazwaPliku ) )).'.'.$RozszerzeniePliku;

                $wartoscPola = '<a href="'.ADRES_URL_SKLEPU . '/' . $wartosc['value'].'" target="_blank">'.$NazwaOryginalnaOdkodowana.'</a>';

                unset($TablicaTmp);
            }

          } else {
            if ( isset($wartosc['value']) ) {
                $wartoscPola = FunkcjeWlasnePHP::my_htmlentities($wartosc['value']);
            }
          }

          echo nl2br('<span id="fields_' . $dodatkowePola['fields_id'] . '" class="ZamowieniePole">' . ( isset($wartosc['value']) && $wartosc['value'] != '' ? $wartoscPola : '--- brak danych ---' ). '</span>');

          if ( $Edytuj == true ) {
            echo '<span class="EdytujDodPole"><em class="TipChmurka"><b>Edytuj dane</b><img src="obrazki/edytuj.png" alt="Edytuj dane" onclick="edytuj_dod_pole(' . $dodatkowePola['fields_id'] . ')" /></em></span>';
          }

          echo '</td></tr>';

          unset($wartoscPola, $Edytuj);

        }
        ?>
 
    </table>
  
</div>

<?php

}

$db->close_query($sql_pola);
unset($dodatkowe_pola_zamowienia);
?>