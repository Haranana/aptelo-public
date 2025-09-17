(function($) {
    $.fn.delayKeyup = function(callback, ms){
            $(this).keyup(function( event ){
                var srcEl = event.currentTarget;
                if( srcEl.delayTimer )
                    clearTimeout (srcEl.delayTimer );
                srcEl.delayTimer = setTimeout(function(){ callback( $(srcEl) ); }, ms);
            });
            $(this).click(function( event ){
                var srcEl = event.currentTarget;
                if( srcEl.delayTimer )
                    clearTimeout (srcEl.delayTimer );
                srcEl.delayTimer = setTimeout(function(){ callback( $(srcEl) ); }, ms);
            });
        return $(this);
    };
})(jQuery);

(function($) { 

    $.AutoUzupelnienie = function( nazwaSzukania, nazwaDiv, nazwaSkryptu, iloscWynikow, szerokoscPola ) {
      
        $(window).resize(function() {
            $('#' + nazwaDiv).hide();
        })
      
        // tylko dla wiekszych rozdzielczosci
        if ( $('#StrGlowna').width() > 800 ) {

            nazwaPolaSzukania = nazwaSzukania;
            maksymalnaIloscWynikow = iloscWynikow;
            nazwaOkna = nazwaDiv;
            
            // w sekundach
            //var czasZamknieciaOkna = 40;
            var oknoPodpowiedzi = null;

            $("#" + nazwaPolaSzukania).attr("autocomplete", "off");

            $('html').click(function(e) {
              if ((e.target.id != nazwaOkna && $(e.target).parents('#' + nazwaOkna).length == 0) && (e.target.id != nazwaPolaSzukania && $(e.target).parents('#' + nazwaPolaSzukania).length == 0)) {
                $('#' + nazwaOkna).remove();
              }
            });     

            //$("#" + nazwaPolaSzukania).bind('keyup click', 
            $("#" + nazwaPolaSzukania).delayKeyup(
                function () {
                    //
                    nazwaPolaSzukania = nazwaSzukania;
                    nazwaOkna = nazwaDiv;            
                    //
                    // pozycja pola szukania
                    var pole = $("#" + nazwaPolaSzukania);
                    
                    // minimalna ilosc znakow w szukaniu to 2
                    if ( pole.val().length > 1 ) {
                    
                        var pozycja = pole.offset();
                        var wysokoscInput = pole.outerHeight();
                        //
                        $('#' + nazwaOkna).remove();
                        $('body').append('<div id="' + nazwaOkna + '" style="display:none"></div>');
                        
                        // loader
                        $('#' + nazwaOkna).fadeIn('fast');
                        $('#' + nazwaOkna).html('<div style="padding:12px"><img src="obrazki/_loader.gif" alt="" /></div>')
                        
                        $('#' + nazwaOkna).css( { width: szerokoscPola, position: 'absolute', top: (pozycja.top + wysokoscInput + 8), left: pozycja.left } );
                        //
                        
                        //$('#' + nazwaOkna).hover(
                        //function(){      
                        //    clearTimeout(oknoPodpowiedzi);
                        //    },
                        //function(){ 
                        //    oknoPodpowiedzi = setTimeout('$.skasujPodpowiedz()', czasZamknieciaOkna * 1000);     
                        //});             
                        
                        $.post(nazwaSkryptu, { pole: pole.val(), limit: maksymalnaIloscWynikow }, 
                            function(data) { 
                            
                                if ( data != '' ) {
                                     $('#' + nazwaOkna).html(''); 
                                     $('#' + nazwaOkna).fadeIn('fast');
                                     $('#' + nazwaOkna).html(data); 
                                     //
                                     //oknoPodpowiedzi = setTimeout('$.skasujPodpowiedz()', czasZamknieciaOkna * 1000);
                                     //
                                  } else {
                                     $('#' + nazwaOkna).remove();
                                }
                                
                            } 
                        );
                        //
                        
                    }
                    
                }, 500
            );
            
            $.pobierzAutoodpowiedz = function( id ) {
                //
                var wartoscPobrana = $('#auto_' + id).val();
                $("#" + nazwaPolaSzukania).val( wartoscPobrana );
                //
                $('#' + nazwaOkna).remove();
                //
            }

            $.pobierzAutoodpowiedzEN = function( id ) {
                //
                var wartoscPobrana = $('#auto_' + id).val();
                var wartoscPobranaId = $('#id_' + id).val();
                $("#" + nazwaPolaSzukania).val( wartoscPobrana );
                $("#" + nazwaPolaSzukania + "_id").val( wartoscPobranaId );
                //
                $('#' + nazwaOkna).remove();
                //
            }

            //$.skasujPodpowiedz = function() {
            //    $('#' + nazwaOkna).remove();
            //    clearTimeout(oknoPodpowiedzi);
            //}        
            
        }
     
    };

})(jQuery);    