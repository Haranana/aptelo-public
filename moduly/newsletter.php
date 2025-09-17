<?php
if ( NEWSLETTER_WLACZONY == 'tak' ) {

    echo '<div class="ModulNewsletterKontener">';
    
        echo '<div class="ModulNewsletter">';

        echo '<strong>{__TLUMACZ:NAGLOWEK_NEWSLETTER}</strong>';

        echo '<form action="/" onsubmit="return sprNewsletter(this, \'modul\')" method="post" class="cmxform" id="newsletterModul">';

            echo '<p>';

                echo '<span class="PoleOpisNewsletterModul"><label for="emailNewsletterModul" style="margin:0;padding:0;line-height:normal">{__TLUMACZ:INFO_NEWSLETTER}</label></span>';

                echo '<span class="PoleZgodNewsletterModul">';

                    echo '<input type="text" name="email" id="emailNewsletterModul" value="{__TLUMACZ:TWOJ_ADRES_EMAIL}" />';

                    if ( NEWSLETTER_ZGODA_MARKETING_WYSWIETLAJ == 'tak' ) {
                         //
                         echo '<small style="display:flex"><label style="padding-left:35px" for="zgoda_newsletter_marketing_modul">{__TLUMACZ:NEWSLETTER_ZGODA_MARKETING}<input type="checkbox" name="zgoda_newsletter_marketing_modul" id="zgoda_newsletter_marketing_modul" value="1" /><span class="check" id="check_zgoda_newsletter_marketing_modul"></span></label></small>';
                         //
                    } else {
                         //
                         echo '<input style="display:none" type="checkbox" name="zgoda_newsletter_marketing_modul" id="zgoda_newsletter_marketing_modul" checked="checked" value="1" />';
                         //
                    }
                    
                    if ( NEWSLETTER_ZGODA_HANDLOWE_WYSWIETLAJ == 'tak' ) {
                         //
                         echo '<small style="display:flex"><label style="padding-left:35px" for="zgoda_newsletter_info_handlowa_modul">{__TLUMACZ:NEWSLETTER_ZGODA_INFO_HANDLOWA}<input type="checkbox" name="zgoda_newsletter_info_handlowa_modul" id="zgoda_newsletter_info_handlowa_modul" value="1" /><span class="check" id="check_zgoda_newsletter_info_handlowa_modul"></span></label></small>';
                         //
                    } else {
                         //
                         echo '<input style="display:none" type="checkbox" name="zgoda_newsletter_info_handlowa_modul" id="zgoda_newsletter_info_handlowa_modul" checked="checked" value="1" />';
                         //
                    }       

                    echo '<span id="BladDanychNewsletterModul" style="text-align:left;margin-left:0px;display:none"><label class="error">{__TLUMACZ:NEWSLETTER_BRAK_ZGODY}</label></span>';
                    
                echo '</span>';

                echo '<span class="PolePrzyciskowNewsletterModul">';

                    echo '<input type="submit" id="submitNewsletterModul" class="przyciskWylaczony" value="{__TLUMACZ:PRZYCISK_ZAPISZ}" disabled="disabled" /> &nbsp;';
                    
                    echo '<input type="button" id="submitUnsubscribeNewsletterModul" class="przyciskWylaczony" onclick="wypiszNewsletter(\'newsletterModul\')" value="{__TLUMACZ:PRZYCISK_WYPISZ}"  disabled="disabled" />';
                    
                echo '</span>';

            echo '</p>';

        echo '</form>';
         
        echo '</div>';
    
    echo '</div>';

}
?>