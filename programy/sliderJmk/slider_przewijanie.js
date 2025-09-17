/**
 * SliderPrzewijany
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

    $.fn.BanneryPrzewijane = function( opcje ) {
        return this.each(function() {   
            $.BanneryPrzewijane( opcje );
        });
    };

    $.BanneryPrzewijane = function( opcje ) {
    
        var SzerNadrzedna = 0;
        var WysokoscImg = 0;   
        
        // jezeli jest resize ekranu to zresetuje modul i ustawi na nowo parametry
        $(window).resize(function() {            
            //
            clearTimeout(PonowanaAnimacjaPrzewijana);            
            //
            BanneryPrzewijanaPrzelicz();                          
            //
            PonowanaAnimacjaPrzewijana = setTimeout(function(){ $.BanneryPrzewijane.przewinBannery( SzerNadrzedna, opcje ) }, parseInt(opcje.czas) * 1000 );  
            //
        });          
        
        function BanneryPrzewijanaPrzelicz() {
            //
            SzerNadrzedna = $('#AnimacjaPrzewijanaKontener').parent().width();
            //
            $('#AnimacjaPrzewijanaKontener').css({ width: SzerNadrzedna });
            $('#BanneryAnimacjaPrzewijana').stop().css({ marginLeft: 0 });
            $('#BanneryAnimacjaPrzewijana li').css({ width: SzerNadrzedna });            
            //                        
            WysokoscImg = 0;
            //
            $('#BanneryAnimacjaPrzewijana li').each(function() {
                //
                if ( $(this).height() > WysokoscImg ) {
                     WysokoscImg = $(this).height();
                }
                //
            });
            
            $('#BanneryAnimacjaPrzewijana li:first span').css({ opacity: 0.7, display: 'block' });

            $('#BanneryAnimacjaPrzewijanaPrzyciski span').removeClass('On');
            $('#BanneryAnimacjaPrzewijanaPrzyciski span:first').addClass('On');
            
            $('#BanneryAnimacjaPrzewijanaNumer').html('0');
            //
            if ( WysokoscImg > 30 ) {
                //            
                $('#AnimacjaPrzewijanaKontener').css({ height: WysokoscImg });
                //
              } else {
                //
                $('.ModulRwdUkryj').each(function() {
                    //
                    if ( $(this).find( '#BanneryAnimacjaPrzewijana' ).length ) {
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
        
        setTimeout( function(){ BanneryPrzewijanaPrzelicz() }, 300);
        
        if ( opcje.nawigacja == 'nie' ) {

            $('#BanneryAnimacjaPrzewijanaLewaStrzalka').css({ 'display': 'none' });
            $('#BanneryAnimacjaPrzewijanaPrawaStrzalka').css({ 'display': 'none' });
            $('#BanneryAnimacjaPrzewijanaPrzyciski').css({ 'display': 'none' });
        }
        
        if ( opcje.strzalki == 'nie' ) {
        
            $('#BanneryAnimacjaPrzewijanaLewaStrzalka').css({ 'display': 'none' });
            $('#BanneryAnimacjaPrzewijanaPrawaStrzalka').css({ 'display': 'none' });
            
        }
        
        if ( opcje.kropki == 'nie' ) {
        
            $('#BanneryAnimacjaPrzewijanaPrzyciski').css({ 'display': 'none' });
            
        }        

        $('#BanneryAnimacjaPrzewijanaLewaStrzalka').off('click').click(function() {
            //
            clearTimeout(PonowanaAnimacjaPrzewijana);
            //        
            var idr = parseInt($('#BanneryAnimacjaPrzewijanaNumer').html()) - 1;
            if (idr == $('#BanneryAnimacjaPrzewijana li').length - 1) {
                idr = 0;
            }      
            if (idr < 0) {
                idr = $('#BanneryAnimacjaPrzewijana li').length - 1;
            }        
            
            $('#BanneryAnimacjaPrzewijana li').find('span').css({ 'display':'none' , 'opacity':'0' });
            
            $('#BanneryAnimacjaPrzewijana').stop().animate({ 'margin-left' : (SzerNadrzedna * parseInt(idr)) * -1 }, 900, 'SzybkoOut', function() {
                $('#scrlnr' + idr).find('span').css('display','block');
                $('#scrlnr' + idr).find('span').fadeTo(500 , 0.7);             
            });
            
            $('#BanneryAnimacjaPrzewijanaNumer').html(idr);                
            $.BanneryPrzewijane.nawigacja();

            PonowanaAnimacjaPrzewijana = setTimeout(function(){ $.BanneryPrzewijane.przewinBannery( SzerNadrzedna, opcje ) }, parseInt(opcje.czas) * 1000 );             
            
        });
        
        $('#BanneryAnimacjaPrzewijanaPrawaStrzalka').off('click').click(function() {
            //
            clearTimeout(PonowanaAnimacjaPrzewijana);
            //        
            var idr = parseInt($('#BanneryAnimacjaPrzewijanaNumer').html()) + 1;
            if (idr > $('#BanneryAnimacjaPrzewijana li').length - 1) {
                idr = 0;
            }                       
            
            $('#BanneryAnimacjaPrzewijana li').find('span').css({ 'display':'none' , 'opacity':'0' });
            
            $('#BanneryAnimacjaPrzewijana').stop().animate({ 'margin-left' : (SzerNadrzedna * parseInt(idr)) * -1 }, 900, 'SzybkoOut', function() {
                $('#scrlnr' + idr).find('span').css('display','block');
                $('#scrlnr' + idr).find('span').fadeTo(500 , 0.7);             
            });
            
            $('#BanneryAnimacjaPrzewijanaNumer').html(idr);                
            $.BanneryPrzewijane.nawigacja();         

            PonowanaAnimacjaPrzewijana = setTimeout(function(){ $.BanneryPrzewijane.przewinBannery( SzerNadrzedna, opcje ) }, parseInt(opcje.czas) * 1000 ); 
            
        });  
        
        $('#BanneryAnimacjaPrzewijanaPrzyciski span').off('click').click(function() {
            //
            clearTimeout(PonowanaAnimacjaPrzewijana);
            //            
            var idr = parseInt($(this).html());       

            $('#BanneryAnimacjaPrzewijana li').find('span').css({ 'display':'none' , 'opacity':'0' });
            
            $('#BanneryAnimacjaPrzewijana').stop().animate({ 'margin-left' : (SzerNadrzedna * parseInt(idr)) * -1 }, 900, 'SzybkoOut', function() {
                $('#scrlnr' + idr).find('span').css('display','block');
                $('#scrlnr' + idr).find('span').fadeTo(500 , 0.7);             
            });
            
            $('#BanneryAnimacjaPrzewijanaNumer').html(idr);                
            $.BanneryPrzewijane.nawigacja();          
            
            PonowanaAnimacjaPrzewijana = setTimeout(function(){ $.BanneryPrzewijane.przewinBannery( SzerNadrzedna, opcje ) }, parseInt(opcje.czas) * 1000 ); 
            
        });

        PonowanaAnimacjaPrzewijana = setTimeout(function(){ $.BanneryPrzewijane.przewinBannery( SzerNadrzedna, opcje ) }, parseInt(opcje.czas) * 1000 );  
        
        $('#AnimacjaPrzewijanaKontener, #BanneryAnimacjaPrzewijanaLewaStrzalka, #BanneryAnimacjaPrzewijanaPrawaStrzalka').hover(
        function() {
            clearTimeout(PonowanaAnimacjaPrzewijana);
        },
        function() {
            PonowanaAnimacjaPrzewijana = setTimeout(function(){ $.BanneryPrzewijane.przewinBannery( SzerNadrzedna, opcje ) }, parseInt(opcje.czas) * 1000 );      
        });   
        
        delete SzerNadrzedna;
        
    };
    
    $.BanneryPrzewijane.przewinBannery = function( SzerNadrzedna, opcje ) {
        //
        clearTimeout(PonowanaAnimacjaPrzewijana);
        //
        var idr = parseInt($('#BanneryAnimacjaPrzewijanaNumer').html()) + 1;
        
        if (idr == $('#BanneryAnimacjaPrzewijana li').length) {
            idr = 0;
        }
        
        $('#BanneryAnimacjaPrzewijana li').find('span').css({ 'display':'none' , 'opacity':'0' });
        
        $('#BanneryAnimacjaPrzewijana').stop().animate({ 'margin-left' : (SzerNadrzedna * parseInt(idr)) * -1 }, 900, 'SzybkoOut', function() {
            $('#scrlnr' + idr).find('span').css('display','block');
            $('#scrlnr' + idr).find('span').fadeTo(500 , 0.7);             
        });
        
        $('#BanneryAnimacjaPrzewijanaNumer').html(idr);                
        $.BanneryPrzewijane.nawigacja();    

        PonowanaAnimacjaPrzewijana = setTimeout(function(){ $.BanneryPrzewijane.przewinBannery( SzerNadrzedna, opcje ) }, parseInt(opcje.czas) * 1000 );  
    }    
    
    $.BanneryPrzewijane.nawigacja = function() {
    
        var ide = parseInt($('#BanneryAnimacjaPrzewijanaNumer').html());
        
        $('#BanneryAnimacjaPrzewijanaPrzyciski span').removeClass('On');
        
        $('#BanneryAnimacjaPrzewijanaPrzyciski span').each(function() {
            //
            if ( parseInt($(this).html()) == ide ) {
                 $(this).addClass('On');
            }
            //
        })
    
    }

})(jQuery);
