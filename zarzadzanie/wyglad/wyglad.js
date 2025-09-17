$(document).ready(function() {
  
    if ( $('#WygladPop').length ) {
         $('#WygladPop').insertBefore('#ekr_preloader');
    }
    
    $(window).resize(function() {
      
        if ( $('#edytuj_stale').length ) {
          
            var margines = $(window).height() - $('#edytuj_okno').height() - 50;
            //
            if ( margines < 10 ) {
                 margines = 40;
            }
            if ( $('#StrGlowna').width() < 900 ) {
                 margines = 80;
            }        
            //        
            $('#edytuj_stale').css({ 'top' : margines / 2 });
            
            if ( $('#edytuj_okno').outerHeight() > $(window).height() ) {
                 $('#edytuj_okno').css({ 'max-height' : $(window).height() - 100, 'overflow-y' : 'scroll' });
              } else {
                 $('#edytuj_okno').css({ 'max-height' : 'none', 'overflow-y' : 'auto' });
              }
            
        }
        
    });
    
    // ikonka favicon
    $('#WybranyPlik').on("change", function() {
      
        $('#ladowanie').show();
      
        var form = $("#poForm");
        
        var formdata = false;
        if (window.FormData){
            formdata = new FormData(form[0]);
        }       

        if ( formdata != false ) {
        
            $.ajax({
                //
                url: 'ajax/ajax_plik_ikona.php?tok=' + $('#tok').val(), 
                type: 'POST',
                contentType: false,
                data: formdata,
                processData: false,
                cache: false,
                dataType: 'json'
                //
            }).done(function(wiadomosc) {
                //
                $('#ladowanie').hide();
                //
                if ( wiadomosc.blad != '' ) {
                    //
                    alert(wiadomosc.blad);
                    //
                } else {
                    //
                    window.location.reload();
                    //
                }
                //
            });
            
        } else {
         
            alert('Wystąpił bład. Twoja przeglądarka nie obsługuje funkcji wgrywania plików.');

        }
        
    });  
    
    $('#UsunIkonke').click(function() {
      
        $.post("ajax/ajax_plik_ikona.php?tok=" + $('#tok').val() + '&usun=tak', function(data){ window.location.reload(); });
      
    });
    
    // strony dla boxow
    $('.WyborStrony input').change(function() {
        //
        var strony = '';
        //
        $('.WyborStrony input').each(function() {
            //
            if ( $(this).prop('checked') ) {
                //
                strony = $(this).val() + ';' + strony;
                //
            }
            //
        });
        //
        $('#ekr_preloader').css('display','block');
        $.post("wyglad/wyglad_dodanie_strony.php?tok=" + $('#tok').val(), { dane : strony }, function(data) {  $('#ekr_preloader').fadeOut(); });
        //
    });    
  
});

// przenoszenie do lewej i prawej kolumny
function ple(id) {
    //
    var idDiv = $("#box_" + id).parent().attr('id');
    $("#box_" + id).css('display','none');
    $('.TipChmurka b').hide();
    //
    if (idDiv == 'wyglad_lewa') {
        $("#box_" + id).appendTo("#wyglad_prawa");
        $("#box_" + id + " em").css('float','left');
        $("#box_" + id + " a").css('float','left');
        $("#box_" + id).css('text-align','right');
        $("#box_" + id + " .Strzalka").attr('src','obrazki/strzalka_lewa.png');
        $("#box_" + id + " .Strzalka").attr('alt','Przenieś do lewej kolumny');
        $("#box_" + id + " em b").html('Przenieś do lewej kolumny');
        
        $("#box_" + id + " span").css('backgroundPosition','right');
        $("#box_" + id + " span").css('paddingLeft','2px');
        $("#box_" + id + " span").css('paddingRight','25px');

        if ($("#wyglad_lewa").html().trim() == '') {
            $("#wyglad_lewa").html('<p style="padding:10px">Brak pozycji ...</p>');
        }        
        if ($("#wyglad_prawa p").length > 0) {
            $("#wyglad_prawa p").remove();
        }
            
    }
    if (idDiv == 'wyglad_prawa') {
        $("#box_" + id).appendTo("#wyglad_lewa");
        $("#box_" + id + " em").css('float','right');
        $("#box_" + id + " a").css('float','right');
        $("#box_" + id).css('text-align','left');
        $("#box_" + id + " .Strzalka").attr('src','obrazki/strzalka_prawa.png');
        $("#box_" + id + " .Strzalka").attr('alt','Przenieś do prawej kolumny');
        $("#box_" + id + " em b").html('Przenieś do prawej kolumny');
        
        $("#box_" + id + " span").css('backgroundPosition','left');
        $("#box_" + id + " span").css('paddingLeft','25px');
        $("#box_" + id + " span").css('paddingRight','2px'); 

        if ($("#wyglad_prawa").html().trim() == '') {
            $("#wyglad_prawa").html('<p style="padding:10px">Brak pozycji ...</p>');
        }        
        if ($("#wyglad_lewa p").length > 0) {
            $("#wyglad_lewa p").remove();
        }
        
    }            
    //
    $("#box_" + id).fadeIn('slow');
    //
    var order = $("#wyglad_lewa").sortable("serialize"); 
    $.post("wyglad/wyglad_serialize_box.php?tok=" + $('#tok').val(), order + '&kolumna=lewa');
    var order = $("#wyglad_prawa").sortable("serialize"); 
    $.post("wyglad/wyglad_serialize_box.php?tok=" + $('#tok').val(), order + '&kolumna=prawa'); 
    //
}

// kasowanie boxu
function psk(id) {
    $('.TipChmurka b').hide();
    //
    $("#box_" + id).remove();
    var order = $("#wyglad_lewa").sortable("serialize"); 
    $.post("wyglad/wyglad_serialize_box.php?tok=" + $('#tok').val(), order + '&kolumna=lewa&skasuj=1&idbox=' + id, function(data) {
        if ($("#wyglad_lewa").html().trim() == '') {
            $("#wyglad_lewa").html('<p style="padding:10px">Brak pozycji ...</p>');
        }		
    });    
    var order = $("#wyglad_prawa").sortable("serialize"); 
    $.post("wyglad/wyglad_serialize_box.php?tok=" + $('#tok').val(), order + '&kolumna=prawa&skasuj=1&idbox=' + id, function(data) {
        if ($("#wyglad_prawa").html().trim() == '') {
            $("#wyglad_prawa").html('<p style="padding:10px">Brak pozycji ...</p>');
        }	
    }); 
}

// kasowanie modulu
function msk(id, typ) {
    $('.TipChmurka b').hide();
    //
    $("#modul_" + id).remove();
    var order = $("#wyglad_srodek_" + typ).sortable("serialize"); 
    $.post("wyglad/wyglad_serialize_modul.php?tok=" + $('#tok').val(), order + '&skasuj=1&idmodul=' + id + '&typ=' + typ, function(data) {
        if ($("#wyglad_srodek_" + typ).html().trim() == '') {
            $("#wyglad_srodek_" + typ).html('<p style="padding:10px">Brak pozycji ...</p>');
        }	
    });    
}

// zmiana kolejnosci - reczna strzalkami
function przesun(id, modul, kierunek) {
    //
    var pozycja = 0;
    var ktora_pozycja = 0;
    var tmp = new Array();
    var order = $("#wyglad_" + modul).find('.Stala').each(function() {
        //
        tmp[pozycja] = $(this).attr('id');
        //
        if ( $(this).attr('id') == modul + '_' + id ) {
            //            
            ktora_pozycja = pozycja;
            //
        }
        //
        pozycja++;
        //
    });
    //
    if ( kierunek == 'dol' ) {
         $('#' + tmp[ ktora_pozycja ]).insertAfter('#' + tmp[ ktora_pozycja + 1 ]);
    }
    if ( kierunek == 'gora' ) {
         $('#' + tmp[ ktora_pozycja ]).insertBefore('#' + tmp[ ktora_pozycja - 1 ]);
    }    
    $('#' + tmp[ ktora_pozycja ]).css({ 'opacity' : 0, backgroundColor : '#adadad' });
    $('#' + tmp[ ktora_pozycja ]).animate({ 'opacity' : 1, backgroundColor : '#ffffff' }, 400);
    //
    $('.TipChmurka').find('b').hide(); 
    //
    var order = $("#wyglad_" + modul).sortable("serialize"); 
    $.post("wyglad/wyglad_serialize_stala.php?tok=" + $('#tok').val(), order + '&typ=' + modul + '&stala=' + modul.toUpperCase());	       
    //
}

// zmiana kolejnosci - reczna strzalkami
function przesun_bm(id, modul, wyglad, kierunek) {
    //
    var pozycja = 0;
    var ktora_pozycja = 0;
    var tmp = new Array();
    //
    // sprawdzi czy box nie zostal przeniesiony do innej kolumny
    if ( wyglad == 'lewa' || wyglad == 'prawa' ) {
         //
         var idDiv = $("#" + id).parent().attr('id');
         //
         if (idDiv == 'wyglad_lewa') {
            //
            wyglad = 'lewa';
            modul = 'lewa';            
            //
          } else {
            //
            wyglad = 'prawa';
            modul = 'prawa';            
            //           
         }
         //
    }
    //
    var order = $("#wyglad_" + modul).find('.Box').each(function() {
        //
        tmp[pozycja] = $(this).attr('id');
        //
        if ( $(this).attr('id') == id ) {
            //            
            ktora_pozycja = pozycja;
            //
        }
        //
        pozycja++;
        //
    });
    //
    if ( kierunek == 'dol' ) {
         $('#' + tmp[ ktora_pozycja ]).insertAfter('#' + tmp[ ktora_pozycja + 1 ]);
    }
    if ( kierunek == 'gora' ) {
         $('#' + tmp[ ktora_pozycja ]).insertBefore('#' + tmp[ ktora_pozycja - 1 ]);
    }    
    $('#' + tmp[ ktora_pozycja ]).css({ 'opacity' : 0, backgroundColor : '#adadad' });
    $('#' + tmp[ ktora_pozycja ]).animate({ 'opacity' : 1, backgroundColor : '#ffffff' }, 400);
    //
    $('.TipChmurka').find('b').hide(); 
    //
    var order = $("#wyglad_" + modul).sortable("serialize"); 
    
    if ( wyglad == 'lewa' || wyglad == 'prawa' ) {
         $.post("wyglad/wyglad_serialize_box.php?tok=" + $('#tok').val(), order + '&kolumna=' + modul); 
      } else {
         $.post("wyglad/wyglad_serialize_modul.php?tok=" + $('#tok').val(), order + '&typ=' + wyglad);	       
    }
    //
}

// kasowanie stalej
function ssk(id, div) {
    $('.TipChmurka b').hide();
    //
    $("#" + div + "_" + id).remove();
    var order = $("#wyglad_" + div).sortable("serialize"); 
    $.post("wyglad/wyglad_serialize_stala.php?tok=" + $('#tok').val(), order + '&skasuj=1&idmodul=' + id + '&typ=' + div + '&stala=' + div.toUpperCase(),
        function(data) {
            if ($("#wyglad_" + div).html().trim() == '') {
                $("#wyglad_" + div).html('<p style="padding:10px">Brak pozycji ...</p>');
            } else {
                var sear = $('#poForm').serialize(); 
                $('#ekr_preloader').css('display','block');
                $.post("wyglad/wyglad_zapisz_ustawienia_menu.php?tok=" + $('#tok').val(), { pola: sear }, function(data) { $('#ekr_preloader').fadeOut(); });              
            }
        }
    );    
}

// zamkniecie okna edycji
function zamknij_edycje() {
    $('#ekr_edit').fadeOut( function(data) { $('#glowne_okno_edycji').html(''); } );
}

// dodawanie boxu do kolumny
function dodaj_box(kolumna, id_loadera) {
    $('#' + id_loadera).html('<img src="obrazki/_loader_small.gif">');
    $.get('wyglad/wyglad_dodanie_boxu.php', { tok: $('#tok').val(), p: 'lista', kolumna: kolumna }, function(data) {
        $('#' + id_loadera).html('');
        $('#ekr_edit').css('display','none');
        $('#glowne_okno_edycji').html(data);
        //
        $('#ekr_edit').show();
        $('#ekr_edit').css({'visibility':'hidden'});
        var margines = $(window).height() - $('#edytuj_okno').height() - 50;
        //
        if ( margines < 10 ) {
             margines = 40;
        }
        if ( $('#StrGlowna').width() < 900 ) {
             margines = 80;
        }        
        //
        $('#edytuj_stale').css({ 'top' : margines / 2 });
        
        if ( $('#edytuj_okno').outerHeight() > $(window).height() ) {
             $('#edytuj_okno').css({ 'max-height' : $(window).height() - 100, 'overflow-y' : 'scroll' });
        }
        
        $('#ekr_edit').css({'visibility':'visible'});
        $('#ekr_edit').hide();
        //
        $('#ekr_edit').fadeIn();
    });
}  

// dodawanie modulu
function dodaj_modul(typ, id_loadera) {
    $('#' + id_loadera).html('<img src="obrazki/_loader_small.gif">');
    $.get('wyglad/wyglad_dodanie_modulu.php', { tok: $('#tok').val(), p: 'lista', typ: typ }, function(data) {
        $('#' + id_loadera).html('');
        $('#ekr_edit').css('display','none');
        $('#glowne_okno_edycji').html(data);
        //
        $('#ekr_edit').show();
        $('#ekr_edit').css({'visibility':'hidden'});
        var margines = $(window).height() - $('#edytuj_okno').height() - 50;
        //
        if ( margines < 10 ) {
             margines = 40;
        }
        if ( $('#StrGlowna').width() < 900 ) {
             margines = 80;
        }        
        //        
        $('#edytuj_stale').css({ 'top' : margines / 2 });
        
        if ( $('#edytuj_okno').outerHeight() > $(window).height() ) {
             $('#edytuj_okno').css({ 'max-height' : $(window).height() - 100, 'overflow-y' : 'scroll' });
        }
        
        $('#ekr_edit').css({'visibility':'visible'});
        $('#ekr_edit').hide();
        //        
        $('#ekr_edit').fadeIn();
    });
}

// dodawanie modulu
function dodaj_stala(div) {
    $.get('wyglad/wyglad_dodanie_stala.php', { tok: $('#tok').val(), p: 'lista', div: div }, function(data) {
        $('#ekr_edit').css('display','none');
        $('#glowne_okno_edycji').html(data);
        //
        $('#ekr_edit').show();
        $('#ekr_edit').css({'visibility':'hidden'});
        var margines = $(window).height() - $('#edytuj_okno').height() - 50;
        //
        if ( margines < 10 ) {
             margines = 40;
        }
        if ( $('#StrGlowna').width() < 900 ) {
             margines = 80;
        }        
        //        
        $('#edytuj_stale').css({ 'top' : margines / 2 });
        
        if ( $('#edytuj_okno').outerHeight() > $(window).height() ) {
             $('#edytuj_okno').css({ 'max-height' : $(window).height() - 100, 'overflow-y' : 'scroll' });
        }
        
        $('#glowne_okno_edycji strong span').click(function() {
           //
           if ( $(this).attr('class') == 'ZwinOkno' ) {
                $('#tresc_' + $(this).attr('id')).stop().slideUp( function() { przeskal() } );
                $(this).removeClass('ZwinOkno').addClass('RozwinOkno');
           } else {
                $('#tresc_' + $(this).attr('id')).stop().slideDown( function() { przeskal() } );
                $(this).removeClass('RozwinOkno').addClass('ZwinOkno');             
           }
           //

        });
        
        function przeskal() {
            //
            var margines = $(window).height() - $('#edytuj_okno').height() - 50;
            //
            if ( margines < 10 ) {
                 margines = 40;
            }
            if ( $('#StrGlowna').width() < 900 ) {
                 margines = 80;
            }        
            //        
            $('#edytuj_stale').stop().animate({ 'top' : margines / 2 }, 200);
           
            if ( $('#edytuj_okno').outerHeight() > $(window).height() ) {
                 $('#edytuj_okno').css({ 'max-height' : $(window).height() - 100, 'overflow-y' : 'scroll' });
            }           
            //            
        }
        
        $('#ekr_edit').css({'visibility':'visible'});
        $('#ekr_edit').hide();
        //        
        $('#ekr_edit').fadeIn();
    });
}

function wybierz_box(id, kolumna) {
    //
    $.get('wyglad/wyglad_dodanie_boxu.php', { tok: $('#tok').val(), p: 'dodaj', id: id, kolumna: kolumna }, function(data) {
        //
        if ($("#wyglad_" + kolumna + " p").length > 0) {
            $("#wyglad_" + kolumna + " p").remove();
        }
        //    
        $("#wyglad_" + kolumna).prepend(data);
        $('#ekr_edit').fadeOut();
        //
        var order = $("#wyglad_" + kolumna).sortable("serialize"); 
        $.post("wyglad/wyglad_serialize_box.php?tok=" + $('#tok').val(), order + '&kolumna=' + kolumna);         
        //
        pokazChmurki();
    });    
}

function wybierz_modul(id, typ) {
    //
    $.get('wyglad/wyglad_dodanie_modulu.php', { tok: $('#tok').val(), p: 'dodaj', id: id, typ: typ }, function(data) {
        //
        if ($("#wyglad_srodek_" + typ + " p").length > 0) {
            $("#wyglad_srodek_" + typ + " p").remove();
        }
        //      
        $("#wyglad_srodek_" + typ).prepend(data);
        $('#ekr_edit').fadeOut();
        //
        var order = $("#wyglad_srodek_" + typ).sortable("serialize"); 
        $.post("wyglad/wyglad_serialize_modul.php?tok=" + $('#tok').val(), order + '&typ=' + typ);         
        //
        pokazChmurki();
    });    
}

function edytuj_modul(id, typ) {
    //
    $.get('wyglad/wyglad_edycja_modulu.php', { tok: $('#tok').val(), id: id, typ: typ }, function(data) {
        //
        $('#ekr_edit').css('display','none');
        $('#glowne_okno_edycji').html(data);
        //
        $('#ekr_edit').show();
        $('#ekr_edit').css({'visibility':'hidden'});
        var margines = $(window).height() - $('#edytuj_okno').height() - 50;
        //
        if ( margines < 10 ) {
             margines = 40;
        }
        if ( $('#StrGlowna').width() < 900 ) {
             margines = 80;
        }
        //        
        $('#edytuj_stale').css({ 'top' : margines / 2 });
        
        if ( $('#edytuj_okno').outerHeight() > $(window).height() ) {
             $('#edytuj_okno').css({ 'max-height' : $(window).height() - 100, 'overflow-y' : 'scroll' });
        }
        
        $('#ekr_edit').css({'visibility':'visible'});
        $('#ekr_edit').hide();
        //        
        $('#ekr_edit').fadeIn();      
        //
        pokazChmurki();
    });    
} 

function wybierz_stala(id, rodzaj, div) {
    //
    $.get('wyglad/wyglad_dodanie_stala.php', { tok: $('#tok').val(), p: 'dodaj', id: id, rodzaj: rodzaj, div: div }, function(data) {
        //
        if ($("#wyglad_" + div + " p").length > 0) {
            $("#wyglad_" + div + " p").remove();
        }
        //
        $("#wyglad_" + div).prepend(data);
        $('#ekr_edit').fadeOut();
        //
        var order = $("#wyglad_" + div).sortable("serialize"); 
        $.post("wyglad/wyglad_serialize_stala.php?tok=" + $('#tok').val(), order + '&typ=' + div + '&stala=' + div.toUpperCase());	       
        //
        pokazChmurki();
    });    
} 

function wybierz_stala_inne(div, rodzaj, formularz) {
    //
    var dane_linku = $("#" + formularz).find("input").serialize();
    $.get('wyglad/wyglad_dodanie_stala.php', { tok: $('#tok').val(), p: 'dodaj', id: dane_linku, rodzaj: rodzaj, div: div }, function(data) {
        //
        if ($("#wyglad_" + div + " p").length > 0) {
            $("#wyglad_" + div + " p").remove();
        }
        //
        $("#wyglad_" + div).prepend(data);
        $('#ekr_edit').fadeOut();
        //
        var order = $("#wyglad_" + div).sortable("serialize"); 
        $.post("wyglad/wyglad_serialize_stala.php?tok=" + $('#tok').val(), order + '&typ=' + div + '&stala=' + div.toUpperCase());	       
        //
        pokazChmurki();
    });    

} 

function wybierz_stala_inne_aktualizacja(div, formularz) {
    //
    if ( formularz == 'DaneLinkuZew' ) {
         p = 'aktualizacja_linku';
    }
    if ( formularz == 'DanePozycjaBanery' ) {
         p = 'aktualizacja_bannery';
    }    
    if ( formularz == 'DaneWszystkieKategorie' ) {
         p = 'aktualizacja_kategorie';
    }  
    if ( formularz == 'DaneWszyscyProducenci' ) {
         p = 'aktualizacja_producenci';
    }      
    //
    var dane_linku = $("#" + formularz).find("input").serialize();
    $.get('wyglad/wyglad_dodanie_stala.php', { tok: $('#tok').val(), p: p, id: dane_linku, div: div }, function(data) {
        //
        var nr_zakladki = 3;
        if ( div == "dolne_menu" ) {
             nr_zakladki = 6;
        }
        if ( div == "stopka_pierwsza" ) {
             nr_zakladki = 7;
        }   
        if ( div == "stopka_druga" ) {
             nr_zakladki = 8;
        }      
        if ( div == "stopka_trzecia" ) {
             nr_zakladki = 9;
        }      
        if ( div == "stopka_czwarta" ) {
             nr_zakladki = 10;
        }      
        if ( div == "stopka_piata" ) {
             nr_zakladki = 11;
        }         
        if ( div == "szybkie_menu" ) {
             nr_zakladki = 17;
        }            
        document.location.href = '/zarzadzanie/wyglad/wyglad.php?zakladka=' + nr_zakladki; 
        //
    });    

} 

function inne_edytuj(ciag, div, rodzaj) {
    //
    if ( rodzaj == 'linkbezposredni' ) {
         p = 'edytuj';
    }
    if ( rodzaj == 'pozycjabannery' ) {
         p = 'edytuj_bannery';
    }    
    if ( rodzaj == 'linkwszystkiekategorie' ) {
         p = 'edytuj_kategorie';
    }    
    if ( rodzaj == 'linkwszyscyproducenci' ) {
         p = 'edytuj_producenci';
    }    
    //
    $.get('wyglad/wyglad_dodanie_stala.php', { tok: $('#tok').val(), p: p, id: ciag, rodzaj: rodzaj, div: div }, function(data) {
        $('#ekr_edit').css('display','none');
        $('#glowne_okno_edycji').html(data);
        //
        $('#ekr_edit').show();
        $('#ekr_edit').css({'visibility':'hidden'});
        var margines = $(window).height() - $('#edytuj_okno').height() - 50;
        //
        if ( margines < 10 ) {
             margines = 40;
        }
        if ( $('#StrGlowna').width() < 900 ) {
             margines = 80;
        }        
        //        
        $('#edytuj_stale').css({ 'top' : margines / 2 });
        
        if ( $('#edytuj_okno').outerHeight() > $(window).height() ) {
             $('#edytuj_okno').css({ 'max-height' : $(window).height() - 100, 'overflow-y' : 'scroll' });
        }
        
        $('#ekr_edit').css({'visibility':'visible'});
        $('#ekr_edit').hide();
        //        
        $('#ekr_edit').fadeIn();
    });

}

// chowa lub pokazuje opcje tla sklepu  
function zmien_tlo(id) {
    if (id == 1) {
        $('#tlo_2').slideUp();
        $('#tlo_1').slideDown();
        $('#foto').val('');
        $('#color').val('');
        zmienGet('kolor','TLO_SKLEPU_RODZAJ');
      } else {
        $('#tlo_1').slideUp();
        $('#tlo_2').slideDown(); 
        $('#color').val('');   
        $('#foto').val('');
        zmienGet('obraz','TLO_SKLEPU_RODZAJ');        
    }
    zmienGet('','TLO_SKLEPU');   
} 

// chowa lub pokazuje opcje naglowka
function zmien_naglowek(id) {
    //
    if (id == 1) {
        $('#naglowek_2').stop().slideUp();
        $('#naglowek_1').stop().slideDown();
        $('#foto_naglowek').val('');
        $('#foto_naglowek_rwd_mobilny').val('');
        $('#foto_naglowek_rwd_kontrast').val('');
        $('#kod_naglowek').val('');
        zmienGet('kod','NAGLOWEK_RODZAJ');
      } else {
        $('#naglowek_1').stop().slideUp();
        $('#naglowek_2').stop().slideDown(); 
        $('#kod_naglowek').val('');   
        $('#foto_naglowek').val('');
        $('#foto_naglowek_rwd_mobilny').val('');
        $('#foto_naglowek_rwd_kontrast').val('');
        zmienGet('obraz','NAGLOWEK_RODZAJ');
    }
    zmienGet('','NAGLOWEK');   
    zmienGet('','NAGLOWEK_RWD_MOBILNY');
    zmienGet('','NAGLOWEK_RWD_KONTRAST');
    //
}    

// zmiana wartosci post
function zmienGet(wart, stala) {
    $('#ekr_preloader').css('display','block');
    $.post("wyglad/wyglad_zapisz_stala.php?tok=" + $('#tok').val(), { wart: wart, stala: stala }, function(data) {  $('#ekr_preloader').fadeOut(); });
    //
    if ( stala == 'STOPKA_BANNERY' ) {
         $('#stopka_bannery_grupa').prop("selectedIndex", 0);
    }
}

// zmiana wartosci post z id jezyka
function zmienGetJezyk(wart, stala, jezyk) {
    $('#ekr_preloader').css('display','block');
    $.post("wyglad/wyglad_zapisz_stala_jezykowa.php?tok=" + $('#tok').val(), { wart: wart, stala: stala, jezyk: jezyk }, function(data) { $('#ekr_preloader').fadeOut(); });
}

// zmiana wartosci menu podkategorii
function zmienMenuPodkat(id) {
    //
    // jaka szerokosc
    var typ = $('input[name="szerokosc_menu_' + id + '"]:checked').val();
    //
    if ( typ == 'szerokie' || typ == '30procent' || typ == '50procent' || typ == '70procent' ) {
         $('#Kolumny_' + id).stop().slideDown();
         $('#Bannery_' + id).stop().slideDown();
         $('#BanneryIlosc_' + id).stop().slideDown();
         $('#BanneryPolozenie_' + id).stop().slideDown();
         $('#BanneryMobile_' + id).stop().slideDown();
         //
         if ( $('#GlebokoscDrzewa_' + id).length ) {
              if (parseInt($('#GlebokoscDrzewa_' + id).find('select').val()) > 2) {
                  $('#GlebokoscDrzewa_' + id).find('select').prop("selectedIndex", 0);
              }
              $('#GlebokoscDrzewa_' + id).find('select').find('option').each(function() {                  
                  if ( parseInt($(this).val()) > 2 ) {
                       $(this).attr('disabled','disable');
                  }
              });
         }
         //          
    } else {
         $('#Kolumny_' + id).stop().slideUp();
         $('#Bannery_' + id).stop().slideUp();
         $('#BanneryIlosc_' + id).stop().slideUp();
         $('#BanneryPolozenie_' + id).stop().slideUp();
         $('#BanneryMobile_' + id).stop().slideUp();
         //
         if ( $('#GlebokoscDrzewa_' + id).length ) {
              $('#GlebokoscDrzewa_' + id).find('select').find('option').each(function() {
                  if ( parseInt($(this).val()) > 2 ) {
                       $(this).prop('disabled',false);
                  }
              });
         }
         // 
    }
    
    // flagi graficzne
    if ( $('input[name="flaga_pozycji_' + id + '"]:checked').val() == 'tak' ) {
         $('#FlagiGraficzne_' + id).stop().slideDown();
    } else {
         $('#FlagiGraficzne_' + id).stop().slideUp();
    }      
    
    // grafiki kategorii
    if ( $('#GrafikiKategoriiUstawienia_' + id).length ) {
         if ( $('input[name="grafika_kategorie_' + id + '"]').prop('checked') == true ) {
              $('#GrafikiKategoriiUstawienia_' + id).stop().slideDown();
         } else {
              $('#GrafikiKategoriiUstawienia_' + id).stop().slideUp();
         }      
    }
    
    // ikony aktualnosci
    if ( $('#IkonyAktualnosciUstawienia_' + id).length ) {
         if ( $('input[name="ikony_aktualnosci_' + id + '"]').prop('checked') == true ) {           
              $('#IkonyAktualnosciUstawienia_' + id).stop().slideDown();
         } else {
              $('#IkonyAktualnosciUstawienia_' + id).stop().slideUp();
         }      
    }    
    
    // czy wyswietlane podkategorie - glebokosc drzewa
    if ( $('#GlebokoscDrzewa_' + id).length ) {
         if ( $('input[name="podkategorie_' + id + '"]').prop('checked') == true ) {
              $('#GlebokoscDrzewa_' + id).stop().slideDown();
              if ( typ == 'szerokie' ) {
                   $('#WysokoscKolumny_' + id).stop().slideDown();
              } else {
                   $('#WysokoscKolumny_' + id).stop().slideUp();
              }
         } else {
              $('#GlebokoscDrzewa_' + id).stop().slideUp();
              $('#WysokoscKolumny_' + id).stop().slideUp();
         }      
    }
    
    // kolor pozycji
    var kolor = $('input[name="kolor_pozycji_rodzaj_' + id + '"]:checked').val();
    //
    if ( $('#KolorPozycji_' + id).length ) {
         if ( kolor == 'inny' ) {
              $('#KolorPozycji_' + id).stop().slideDown();
         } else {
              $('#KolorPozycji_' + id).stop().slideUp();
         }      
    }    
    
    // kolor tla pozycji
    var kolor = $('input[name="kolor_tla_rodzaj_' + id + '"]:checked').val();
    //
    if ( $('#KolorTla_' + id).length ) {
         if ( kolor == 'inny' ) {
              $('#KolorTla_' + id).stop().slideDown();
         } else {
              $('#KolorTla_' + id).stop().slideUp();
         }      
    }     
    
    var sear = $('#poForm').serialize(); 
    $('#ekr_preloader').css('display','block');
    $.post("wyglad/wyglad_zapisz_ustawienia_menu.php?tok=" + $('#tok').val(), { pola: sear }, function(data) { $('#ekr_preloader').fadeOut(); });
}

function pokazKonfigMenu(elem) {
    var it = elem.attr('data-id');
    if ( $('#KonfigMenu_' + it).css('display') == 'none' ) {
         $('#KonfigMenu_' + it).stop().slideDown();
         elem.html('ukryj ustawienia pozycji menu');
         $("#wyglad_gorne_menu").sortable("disable");
         $("#wyglad_gorne_menu > div").css({ 'cursor' : 'default' });
    } else {
         $('#KonfigMenu_' + it).stop().slideUp();
         elem.html('wyświetl ustawienia pozycji menu');
         $("#wyglad_gorne_menu").sortable("enable");
         $("#wyglad_gorne_menu > div").css({ 'cursor' : 'move' });
    }
}

function usunNaglowek(id) {
  if ( id == 1 ) {
       $('#foto_naglowek').val('');
       zmienGet('','NAGLOWEK'); 
  }
  if ( id == 2 ) {
       $('#foto_naglowek_rwd_mobilny').val('');
       zmienGet('','NAGLOWEK_RWD_MOBILNY');
  }
  if ( id == 3 ) {
       $('#foto_naglowek_rwd_kontrast').val('');
       zmienGet('','NAGLOWEK_RWD_KONTRAST');
  }  
}  
  
function usunMenuIkonka(id) {
  $('#menu_ikonka_' + id).val('');
  zmienMenuPodkat(id);
}  
