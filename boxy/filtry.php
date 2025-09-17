<?php
// /* dodatkowe ustawienia konfiguracyjne */
//

if ( FILTRY_POLOZENIE == 'box' ) {

  echo '<form id="filtrBox" action="{__AKTUALNY_LINK}" method="post" class="cmxform">
            <div id="filtryBox"></div>
        </form>';
        
  echo '<script>$(document).ready(function() { filtryBox() })</script>';

}

?>