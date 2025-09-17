<?php
if ( NEWSLETTER_WLACZONY == 'tak' ) {

    //
    echo '<div class="BoxNewsletter">';

    echo '<p class="NewsletterOpis">{__TLUMACZ:INFO_NEWSLETTER}</p>';

    echo '<form action="/" onsubmit="return sprNewsletter(this, \'box\')" method="post" class="cmxform" id="newsletter">';

    echo '<p class="PoleAdresu">';

        echo '<input type="text" name="email" id="emailNewsletter" value="{__TLUMACZ:TWOJ_ADRES_EMAIL}" />';
        
    echo '</p>';

    echo '<p class="PoleZgod" style="text-align:left">';

        if ( NEWSLETTER_ZGODA_MARKETING_WYSWIETLAJ == 'tak' ) {
             //
             echo '<small style="display:flex"><label for="zgoda_newsletter_marketing_box">{__TLUMACZ:NEWSLETTER_ZGODA_MARKETING}<input type="checkbox" name="zgoda_newsletter_marketing_box" id="zgoda_newsletter_marketing_box" value="1" /><span class="check" id="_check_zgoda_newsletter_marketing_box"></span></label></small>';
             //
        } else {
             //
             echo '<input style="display:none" type="checkbox" name="zgoda_newsletter_marketing_box" id="zgoda_newsletter_marketing_box" checked="checked" value="1" />';
             //
        }

        if ( NEWSLETTER_ZGODA_HANDLOWE_WYSWIETLAJ == 'tak' ) {
             //
             echo '<small style="display:flex"><label for="zgoda_newsletter_info_handlowa_box">{__TLUMACZ:NEWSLETTER_ZGODA_INFO_HANDLOWA}<input type="checkbox" name="zgoda_newsletter_info_handlowa_box" id="zgoda_newsletter_info_handlowa_box" value="1" /><span class="check" id="check_zgoda_newsletter_info_handlowa_box"></span></label></small>'; 
             //
        } else {
             //
             echo '<input style="display:none" type="checkbox" name="zgoda_newsletter_info_handlowa_box" id="zgoda_newsletter_info_handlowa_box" checked="checked" value="1" />';
             //
        }    

    echo '</p>';

    echo '<span id="BladDanychNewsletterBox" style="text-align:left;display:none"><label class="error">{__TLUMACZ:NEWSLETTER_BRAK_ZGODY}</label></span>';

    echo '<div>';

        echo '<input type="submit" id="submitNewsletter" class="przyciskWylaczony" value="{__TLUMACZ:PRZYCISK_ZAPISZ}" disabled="disabled" /> &nbsp;';
        
        echo '<input type="button" id="submitUnsubscribeNewsletter" class="przyciskWylaczony" onclick="wypiszNewsletter()" value="{__TLUMACZ:PRZYCISK_WYPISZ}" disabled="disabled" />';
        
    echo '</div>';

    echo '</form>';
     
    echo '</div>';

    //
    
}
?>