/**
 * SliderPrzenikanie
 * wersja 1.0
 * www.shopgold.pl
 
 * slider NIE jest dostepny na licencji MIT lub GNU - wszelkie prawa autorskie posiada Studio Komputerowe Kamelia-Net Jacek Krysiak 
 * zabrania sie uzywania poza oprogramowaniem sklepu shopGold
 
  * ---- konfiguracja modulu ----
 - czas - czas pomiedzy animacjami (w milisekundach)
 - nawigacja - czy wyswietlac nawigacje animacji
 - strzalki - czy wyswietlac strzalki nawigacyjne
 - kropki - czy wyswietlac kropki nawigacyjne
 
**/

(function($) {

    var opcje;

    $.fn.BanneryPrzenikanie = function( opcje ) {
        return this.each(function() {   
            $.BanneryPrzenikanie( opcje );
        });
    };

    $.BanneryPrzenikanie = function( opcje ) {
    
        var SzerNadrzedna = 0;
        var WysokoscImg = 0;    

        // jezeli jest resize ekranu to zresetuje modul i ustawi na nowo parametry
        $(window).resize(function() {            
            //
            clearTimeout(PonowanaAnimacjaPrzenikanie);            
            //
            BanneryPrzenikaniePrzelicz();                          
            //
            PonowanaAnimacjaPrzenikanie = setTimeout(function(){ $.BanneryPrzenikanie.przenikajBannery( SzerNadrzedna, opcje ) }, parseInt(opcje.czas) * 1000 ); 
            //
        });          

        function BanneryPrzenikaniePrzelicz() {
            //
            SzerNadrzedna = $('#AnimacjaPrzenikanieKontener').parent().width();
            //
            $('#AnimacjaPrzenikanieKontener').css({ width: SzerNadrzedna });
            $('#BanneryAnimacjaPrzenikanie li').css({ width: SzerNadrzedna });            
            //            
            $('#BanneryAnimacjaPrzenikanie li').stop(true, true);
            $('#BanneryAnimacjaPrzenikanie li').fadeIn(30).show();
            
            WysokoscImg = 0;
            //
            $('#BanneryAnimacjaPrzenikanie li').each(function() {
                //
                if ( $(this).height() > WysokoscImg ) {
                     WysokoscImg = $(this).height();
                }
                //
            });
            
            $('#BanneryAnimacjaPrzenikanie li').hide();
            $('#BanneryAnimacjaPrzenikanie li:first').show();
            $('#BanneryAnimacjaPrzenikanie li:first span').css({ opacity: 0.7, display: 'block' });
            
            $('#BanneryAnimacjaPrzenikaniePrzyciski span').removeClass('On');
            $('#BanneryAnimacjaPrzenikaniePrzyciski span:first').addClass('On');
            
            $('#BanneryAnimacjaPrzenikanieNumer').html('0');
            //
            if ( WysokoscImg > 30 ) {
                //
                $('#AnimacjaPrzenikanieKontener').css({ height: WysokoscImg });
                //
              } else {
                //
                $('.ModulRwdUkryj').each(function() {
                    //
                    if ( $(this).find( '#BanneryAnimacjaPrzenikanie' ).length ) {
                        //
                        if ( $(this).css('display') != 'none' ) {
                              window.location.reload();
                        }
                        //
                    }
                    //
                });
                //
            }
        }

        setTimeout( function(){ BanneryPrzenikaniePrzelicz() }, 300);
        
        if ( opcje.nawigacja == 'nie' ) {

            $('#BanneryAnimacjaPrzenikanieLewaStrzalka').css({ 'display': 'none' });
            $('#BanneryAnimacjaPrzenikaniePrawaStrzalka').css({ 'display': 'none' });
            $('#BanneryAnimacjaPrzenikaniePrzyciski').css({ 'display': 'none' });
        }
        
        if ( opcje.strzalki == 'nie' ) {
        
            $('#BanneryAnimacjaPrzenikanieLewaStrzalka').css({ 'display': 'none' });
            $('#BanneryAnimacjaPrzenikaniePrawaStrzalka').css({ 'display': 'none' });
            
        }
        
        if ( opcje.kropki == 'nie' ) {
        
            $('#BanneryAnimacjaPrzenikaniePrzyciski').css({ 'display': 'none' });
            
        }        

        $('#BanneryAnimacjaPrzenikanieLewaStrzalka').off('click').click(function() {
            //
            clearTimeout(PonowanaAnimacjaPrzenikanie);
            //
            $('#fadnr' + parseInt($('#BanneryAnimacjaPrzenikanieNumer').html())).fadeOut(500);
            //
            var idr = parseInt($('#BanneryAnimacjaPrzenikanieNumer').html()) - 1;
            if (idr == $('#BanneryAnimacjaPrzenikanie li').length - 1) {
                idr = 0;
            }      
            if (idr < 0) {
                idr = $('#BanneryAnimacjaPrzenikanie li').length - 1;
            }        
            
            $('#BanneryAnimacjaPrzenikanie li').find('span').css({ 'display':'none' , 'opacity':'0' });
            
            $('#fadnr' + idr).fadeIn(500, function() {            
                $('#fadnr' + idr).find('span').css('display','block');
                $('#fadnr' + idr).find('span').fadeTo(500 , 0.7);                  
            });
            
            $('#BanneryAnimacjaPrzenikanieNumer').html(idr);
            $.BanneryPrzenikanie.nawigacja();
            
            PonowanaAnimacjaPrzenikanie = setTimeout(function(){ $.BanneryPrzenikanie.przenikajBannery( SzerNadrzedna, opcje ) }, parseInt(opcje.czas) * 1000 ); 
        
        });
        
        $('#BanneryAnimacjaPrzenikaniePrawaStrzalka').off('click').click(function() {
            //
            clearTimeout(PonowanaAnimacjaPrzenikanie);
            //
            $('#fadnr' + parseInt($('#BanneryAnimacjaPrzenikanieNumer').html())).fadeOut(500);
            //        
            var idr = parseInt($('#BanneryAnimacjaPrzenikanieNumer').html()) + 1;
            if (idr > $('#BanneryAnimacjaPrzenikanie li').length - 1) {
                idr = 0;
            }    
            
            $('#BanneryAnimacjaPrzenikanie li').find('span').css({ 'display':'none' , 'opacity':'0' });
            
            $('#fadnr' + idr).fadeIn(500, function() {            
                $('#fadnr' + idr).find('span').css('display','block');
                $('#fadnr' + idr).find('span').fadeTo(500 , 0.7);                  
            }); 
            //
            $('#BanneryAnimacjaPrzenikanieNumer').html(idr);
            $.BanneryPrzenikanie.nawigacja();

            PonowanaAnimacjaPrzenikanie = setTimeout(function(){ $.BanneryPrzenikanie.przenikajBannery( SzerNadrzedna, opcje ) }, parseInt(opcje.czas) * 1000 ); 
        
        });  
        
        $('#BanneryAnimacjaPrzenikaniePrzyciski span').off('click').click(function() {
            //
            clearTimeout(PonowanaAnimacjaPrzenikanie);
            //
            var idr = parseInt($(this).html()); 
            if ( idr != parseInt($('#BanneryAnimacjaPrzenikanieNumer').html()) ) {
                //
                $('#fadnr' + parseInt($('#BanneryAnimacjaPrzenikanieNumer').html())).fadeOut(500);
                //                                      
                $('#BanneryAnimacjaPrzenikanie li').find('span').css({ 'display':'none' , 'opacity':'0' });
                
                $('#fadnr' + idr).fadeIn(500, function() {            
                    $('#fadnr' + idr).find('span').css('display','block');
                    $('#fadnr' + idr).find('span').fadeTo(500 , 0.7);                  
                });
                //
                $('#BanneryAnimacjaPrzenikanieNumer').html(idr);              
                $.BanneryPrzenikanie.nawigacja(); 
            }
            //
            PonowanaAnimacjaPrzenikanie = setTimeout(function(){ $.BanneryPrzenikanie.przenikajBannery( SzerNadrzedna, opcje ) }, parseInt(opcje.czas) * 1000 ); 
                    
        });

        PonowanaAnimacjaPrzenikanie = setTimeout(function(){ $.BanneryPrzenikanie.przenikajBannery( SzerNadrzedna, opcje ) }, parseInt(opcje.czas) * 1000 );  
        
        $('#AnimacjaPrzenikanieKontener, #BanneryAnimacjaPrzenikanieLewaStrzalka, #BanneryAnimacjaPrzenikaniePrawaStrzalka').hover(
        function() {
            clearTimeout(PonowanaAnimacjaPrzenikanie);
        },
        function() {
            PonowanaAnimacjaPrzenikanie = setTimeout(function(){ $.BanneryPrzenikanie.przenikajBannery( SzerNadrzedna, opcje ) }, parseInt(opcje.czas) * 1000 );      
        });   

    };
    
    $.BanneryPrzenikanie.przenikajBannery = function( SzerNadrzedna, opcje ) {
        //
        clearTimeout(PonowanaAnimacjaPrzenikanie);
        //
        var idr = parseInt($('#BanneryAnimacjaPrzenikanieNumer').html()) + 1;
        
        $('#fadnr' + parseInt($('#BanneryAnimacjaPrzenikanieNumer').html())).fadeOut(500);

        if (idr == $('#BanneryAnimacjaPrzenikanie li').length) {
            idr = 0;
        }

        $('#BanneryAnimacjaPrzenikanie li').find('span').css({ 'display':'none' , 'opacity':'0' });
        
        $('#fadnr' + idr).fadeIn(500, function() {            
            $('#fadnr' + idr).find('span').css('display','block');
            $('#fadnr' + idr).find('span').fadeTo(500 , 0.7);                  
        });          
        //
        $('#BanneryAnimacjaPrzenikanieNumer').html(idr);
        $.BanneryPrzenikanie.nawigacja();        

        PonowanaAnimacjaPrzenikanie = setTimeout(function(){ $.BanneryPrzenikanie.przenikajBannery( SzerNadrzedna, opcje ) }, parseInt(opcje.czas) * 1000 );  
    }    
    
    $.BanneryPrzenikanie.nawigacja = function() {
    
        var ide = parseInt($('#BanneryAnimacjaPrzenikanieNumer').html());
        
        $('#BanneryAnimacjaPrzenikaniePrzyciski span').removeClass('On');
        
        $('#BanneryAnimacjaPrzenikaniePrzyciski span').each(function() {
            //
            if ( parseInt($(this).html()) == ide ) {
                 $(this).addClass('On');
            }
            //
        })
    
    }

})(jQuery);
