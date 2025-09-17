<?php
if ( isset($toks) ) {
?>    
    
    <div id="zakl_id_7" style="display:none;" class="pozycja_edytowana">
    
      <?php if ( $zamowienie->sprzedaz_online_kody == true ) { ?>
      
      <div class="ObramowanieTabeli">
      
        <table class="listing_tbl">
        
          <tr class="div_naglowek NaglowekCentruj">
            <td>Link do pobrania plików</td>
          </tr>    
          
          <tr class="pozycja_off">
              <td style="text-align:center">              
                  <textarea cols="80" style="width:95%" rows="2"><?php echo ADRES_URL_SKLEPU . '/' . $zamowienie->sprzedaz_online_link; ?></textarea>
                  <span class="maleInfo" style="display:inline-block">w/w link prowadzi bezpośrednio do strony z której klient może pobrać zakupione kody licencyjne, link można wysłać klientowi osobnym mailem</span>
              </td>
          </tr>
          
        </table>
        
      </div>
      
      <br />

      <div class="ObramowanieTabeli">

        <table class="listing_tbl">
        
          <tr class="div_naglowek NaglowekCentruj">
            <td>Produkty i przypisane klucze elektroniczne</td>
          </tr>    
          
          <tr class="pozycja_off">
          
              <table class="KluczeElektroniczne">
              
              <?php foreach ( $zamowienie->sprzedaz_online_kody_lista as $produkt ) { ?>
              
                  <tr class="pozycja_off">
                      <td>
                          <?php echo '<span class="LinkProduktu">' . $produkt['nazwa_produktu'] . '</span>'; ?>
                      </td>
                      <td>
                          <?php
                          if ( $produkt['kody'] != '' ) {
                               echo $produkt['kody'];
                             } else {
                               echo '<span style="color:#ccc">--- nie przypisany ---</span>';
                          }  
                          ?>                          
                      </td>
                  </tr>

              <?php } ?>
              
              </table>

          </tr>
          
        </table>
        
      </div>
      
      <?php } ?>
      
      <?php if ( $zamowienie->sprzedaz_online_automater == true && INTEGRACJA_AUTOMATER_WLACZONY == 'tak' ) { ?>
      
          <div class="ObramowanieTabeli">
          
            <table class="listing_tbl">
            
              <tr class="div_naglowek NaglowekCentruj">
                <td>Integracja z Automater.pl</td>
              </tr>    
              
              <tr class="pozycja_off">
                  <td style="text-align:center">              
                      Cart id: <?php echo $zamowienie->info['automater_id_cart']; ?>
                      
                      <?php if ( $zamowienie->info['automater_wyslane'] == 1 ) { ?>
                      <div style="color:green; margin-top:8px"><small>Cart id został przekazany do Automater w celu wysłania kodów<small></div>
                      <?php } ?>
                  </td>
              </tr>
              
            </table>
            
          </div>  
          
          <br />

          <div class="ObramowanieTabeli">
          
            <table class="listing_tbl">
            
              <tr class="div_naglowek NaglowekCentruj">
                <td>Nazwa produktu w sklepie</td>
                <td>Nazwa produktu a Automater</td>
                <td>ID produktu a Automater</td>
              </tr>    
              
              <?php 
              $produktyAutomater = Automater::ListaProduktow();
              
              foreach ( $zamowienie->produkty as $produkt ) {
                
                  if ( $produkt['automater_id'] > 0 ) { ?>
              
                  <tr class="pozycja_off">
                      <td style="text-align:center">
                          <?php echo '<span class="LinkProduktu">' . $produkt['nazwa'] . '</span>'; ?>
                      </td>
                      <td style="text-align:center">
                          <?php
                          foreach ( $produktyAutomater as $tmp ) {
                              //
                              if ( $tmp['id'] == $produkt['automater_id'] ) {
                                   //
                                   echo $tmp['nazwa'];
                                   //
                              }
                              //
                          }
                          ?>
                      </td>                      
                      <td style="text-align:center">
                          <?php echo $produkt['automater_id']; ?>                        
                      </td>
                  </tr>

                  <?php
                  }
                  
              } 
              
              unset($produktyAutomater);
              ?>

            </table>
            
          </div>           
      
      <?php } ?>
      
    </div> 
    
<?php
}
?>        