$(document).ready(function(){

    $('.ObrazekPole').attr('autocomplete', 'off');

    $('.ObrazekPole').bind('blur',	
      function () {
        var idt = $(this).attr("id");
        var idt_tab = idt.split('_');
        var idp = idt_tab[ idt_tab.length - 1];
        usun_slownik(idp);
        pokaz_obrazek_ajax(idt, $(this).val());
      }
    ); 
    
    $('.UsunZdjeciePola').click(function() {
        //
        var atr = $(this).attr('data-foto');
        $('#' + atr).val('');
        //
        var idt_tab = atr.split('_');
        var idp = idt_tab[ idt_tab.length - 1];
        usun_slownik(idp);            
        //
        $('#div' + atr).slideUp('fast');            
        //
    });        

});

function dodaj_dodatkowe_pole(id) {
    //
    if ( $('#pole_nazwa_' + $('#id_dod_pola_' + id).val()).length == 0 ) {
        //
        $.get('ajax/lista_dodatkowych_pol.php?tok=' + $('#tok').val(), { id_jezyka: id, id: $('#id_dod_pola_' + id).val(), katalog: katalogZdjec }, function(data) {
            //
            $('#nowe_pola_' + id).append(data);
            //
            pokazChmurki(); 
            $('.ObrazekPole').attr('autocomplete', 'off');
            //
            $('.ObrazekPole').bind('blur',	
              function () {
                var idt = $(this).attr("id");
                var idt_tab = idt.split('_');
                var idp = idt_tab[ idt_tab.length - 1];
                usun_slownik(idp);
                pokaz_obrazek_ajax(idt, $(this).val());
              }
            );
            //                
            $('.UsunZdjeciePola').click(function() {
                //
                var atr = $(this).attr('data');
                $('#' + atr).val('');
                //
                var idt_tab = atr.split('_');
                var idp = idt_tab[ idt_tab.length - 1];
                usun_slownik(idp);            
                //
                $('#div' + atr).slideUp('fast');            
                //
            }); 
            //
            $(".kropka").change(		
              function () {
                var type = this.type;
                var tag = this.tagName.toLowerCase();
                if (type == 'text' && tag != 'textarea' && tag != 'radio' && tag != 'checkbox') {
                    //
                    zamien_krp($(this),'0.00');
                    //
                }
              }
            );
            //
        });
        //
    }
    //
    if ( $('.pole_dodatkowe').length > 0 ) {
         $('#brak_pol_' + id).show();
       } else {
         $('#brak_pol_' + id).hide();
    }
    //
}
function usun_pole(id) {
    $('.tip-twitter').css({'visibility':'hidden'});
    $('#pole_nazwa_' + id).remove();
    //
    if ( $('.pole_dodatkowe_' + id).length > 0 ) {
         $('#brak_pol_' + id).hide();
       } else {
         $('#brak_pol_' + id).show();
    }        
    //
}    
function pokaz_slownik(id) {
    //
    if ( $('#slownik_' + id).html() == '' ) {
        $.get('ajax/slownik_dodatkowych_pol.php?tok=' + $('#tok').val(), { id: id }, function(data) {
            //
            $('#slownik_' + id).hide();
            $('#slownik_' + id).html(data);
            $('#slownik_' + id).slideDown('fast');
            //
            usun_slownik(id);
            //
        });
    } else {
        $('#slownik_' + id).slideUp('fast', function() {
            $('#slownik_' + id).html('')
        });
    }
    //
}
function usun_slownik(id) {
    //
    if ( $('#dodatkowe_pole_slownik_' + id).length ) {
         $('#dodatkowe_pole_slownik_' + id).find('option').removeAttr('selected');
    }
    //
}
function zmien_input(id) {
    //
    if ( $('#foto_pole_' + id).length ) {
         $('#foto_pole_' + id).val( $('#dodatkowe_pole_slownik_' + id + ' option:selected').text() );
    }
    //
    if ( $('#divfoto_pole_' + id).length ) {
        //
        pokaz_obrazek_ajax('foto_pole_' + id, $('#foto_pole_' + id).val());
        //
    }
    //
}    
function dodaj_nowe_pole(id_jezyka) {
    for ( x = 0; x < 20; x++ ) {          
          //
          if ( $('#OknoDodawaniaNowegoPola_' + x).length ) {
               $('#OknoDodawaniaNowegoPola_' + x).hide();
               $('#OknoDodawaniaNowegoPola_' + x).html('');
          }
          //
    }
    //
    $('#OknoDodawaniaNowegoPola_' + id_jezyka).html('<div style="margin:10px"><img src="obrazki/_loader.gif"></div>');
    //
    $.get('ajax/dodaj_pole_opisowe.php',
          { id_jezyka: id_jezyka, tok: $('#tok').val() }, 
          function(data) { 
            $('#OknoDodawaniaNowegoPola_' + id_jezyka).hide();
            $('#OknoDodawaniaNowegoPola_' + id_jezyka).html(data); 
            $('#OknoDodawaniaNowegoPola_' + id_jezyka).slideDown();           
          });    
}  
function zapisz_nowe_pole(id_jezyka) {
    $('#ZapiszPole').html('<div style="margin:10px"><img src="obrazki/_loader.gif"></div>');
    //
    var sear = $('#OknoDodawaniaPola_' + id_jezyka).find('input').serialize(); 
    $.get('ajax/dodaj_pole_opisowe.php',
          { data: sear, tok: $('#tok').val() }, 
          function(data) { 
              for ( x = 0; x < 20; x++ ) {          
                    //
                    if ( $('#OknoDodawaniaNowegoPola_' + x).length ) {
                         $('#OknoDodawaniaNowegoPola_' + x).hide();
                         $('#OknoDodawaniaNowegoPola_' + x).html('');
                    }
                    //
              }
              $('#WyborPola_' + id_jezyka).html(data);
              //
              var ile_war = $('#id_dod_pola_' + id_jezyka).find('option').length;
              if (ile_war == 0) {
                  $('#id_dod_pola_' + id_jezyka).attr('disabled','disabled');
                  $('.InfoPoleDodaj').hide();
              } else {
                  $('.InfoPoleDodaj').show();
              }              
          });    
} 
function zamknij_nowe_pole(id_jezyka) {
    //
    $('#OknoDodawaniaNowegoPola_' + id_jezyka).slideUp( function() { 
        for ( x = 0; x < 20; x++ ) {          
              //
              if ( $('#OknoDodawaniaNowegoPola_' + x).length ) {
                   $('#OknoDodawaniaNowegoPola_' + x).hide();
                   $('#OknoDodawaniaNowegoPola_' + x).html('');
              }
              //
        }
    });
    //
} 
function dodaj_nowe_pole_tekstowe(id_produktu) {
    $('#OknoDodawaniaNowegoPolaTekstowego').html('<div style="margin:10px"><img src="obrazki/_loader.gif"></div>');
    //
    $.get('ajax/dodaj_pole_tekstowe.php',
          { id_produktu: id_produktu, tok: $('#tok').val() }, 
          function(data) { 
            $('#OknoDodawaniaNowegoPolaTekstowego').hide();
            $('#OknoDodawaniaNowegoPolaTekstowego').html(data); 
            $('#OknoDodawaniaNowegoPolaTekstowego').slideDown();           
          });    
} 
function zamknij_nowe_pole_tekstowe() {
    //
    $('#OknoDodawaniaNowegoPolaTekstowego').slideUp( function() { $('#OknoDodawaniaNowegoPolaTekstowego').html('') });
    //
}
function zapisz_nowe_pole_tekstowe() {
    $('#ZapiszPoleTekstowe').html('<div style="margin:10px"><img src="obrazki/_loader.gif"></div>');
    //
    var sear = $('#OknoDodawaniaPolaTekstowego').find('input').serialize(); 
    $.get('ajax/dodaj_pole_tekstowe.php',
          { data: sear, tok: $('#tok').val() }, 
          function(data) { 
              //
              $('#PolaTekstoweLista').html(data);
              $('#OknoDodawaniaNowegoPolaTekstowego').hide();
              $('#OknoDodawaniaNowegoPolaTekstowego').html('');
              //             
          });    
} 