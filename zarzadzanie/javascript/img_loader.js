(function($) {

    $.ZaladujObrazki = function() {

        // pobiera wysokosc ekranu
        ct = screen.height;
        
        // ukrywa ikony na obrazkach
        $(".Ikona").hide();
        
        $.PokazWidoczneObrazki( ct + $(window).scrollTop() );
        
        // po przesunieciu ekranu
        $(window).scroll( function(){
            //
            // aktualnosc wysokosc strony po przesunieciu
            ak = ct + $(this).scrollTop();
            //
            $.PokazWidoczneObrazki( ak );
            
        });  

    }
    
    $.PokazWidoczneObrazki = function( ctd ) {
    
        // szuka wszystkich obrazkow z klasa Reload
        $(".Reload").each(function() {
        
            // polozenie obrazka od gory
            pol = $(this).offset().top;
            
            // jezeli polozenie obrazka od gory jest mniejsze niz wysokosc ekranu to pokaze obrazek
            if ( pol < ctd ) {
            
                // jezeli obrazek ma atrybut src-oryginal
                if ( $(this).attr('src-original') != null ) {
                
                     $(this).hide();
                     
                     // funkcja ladowania oryginalnego obrazu
                     $.PokazObrazek(this);

                }
                 
            }
            
            delete pol;
            
        });    
    
    }
    
    $.PokazObrazek = function( foto ) {
    
        // pobiera atrybut
        atr = $(foto).attr('src-original');
        
        // podmienia obrazek
        $(foto).attr('src',atr);

        $(foto).show();
        
        // usuwa atrybut 
        $(foto).removeAttr('src-original');

        delete atr;
    
    }
    
})(jQuery);