// przegladarka plikow
function przegladarka( katalog, input, edytor, stala, produkt, pamietaj, sortowanie, wyswietlanie, licznik ) {
    //
    $('#Przegladarka').css('display','none');
    $('#ekr_preloader').css('display','block');
    //
    if (pamietaj == undefined) {
        pamietaj = '';
    }
    if (sortowanie == undefined) {
        sortowanie = 'nazwa_rosnaco';
    } 
    if (wyswietlanie == undefined) {
        wyswietlanie = $('#PrzegladarkaWyglad').html();
    }  
    if (licznik == undefined) {
        licznik = 500;
    }       
    //
    $.post('../zarzadzanie/przegladarka.php?typ=' + edytor + '&tok=' + $('#tok').val(), { folder: katalog, pole: input, stala: stala, produkt: produkt, pamietaj: pamietaj, sortowanie: sortowanie, wyswietlanie: wyswietlanie, licznik: licznik }, function(data) {
        //
        $('#ekr_preloader').css('display','none');
        //
        $('#ListPlik' + edytor).html( data );
        $('#Przegladarka').css('display','block');
        //
        pokazChmurki(); 
        //
        masowoProdukt();
        //
    });
}     

function widokPrzegladarka( widok ) {
    //
    if ( widok == 'okna' ) {
         $('.WidokOkno').addClass('WidokWl');
         $('.WidokLista').removeClass('WidokWl');
    } else {
         $('.WidokOkno').removeClass('WidokWl');
         $('.WidokLista').addClass('WidokWl');
    }
    //    
}

function przegladarkaZamknij( edytor ) {
    //
    $('#Przegladarka').css('display','none');
    $('#ListPlik' + edytor).html('');
    //
}

function przegladarkaFolder( katalog, input, edytor, stala, produkt, pamietaj, sortowanie, wyswietlanie, licznik ) {
    //
    $('#Przegladarka').css('display','none');
    $('#ekr_preloader').css('display','block');
    //
    if (pamietaj == undefined) {
        pamietaj = '';
    }
    if (sortowanie == undefined) {
        sortowanie = 'nazwa_rosnaco';
    } 
    if (wyswietlanie == undefined) {
        wyswietlanie = $('#PrzegladarkaWyglad').html();
    }       
    if (licznik == undefined) {
        licznik = 500;
    }      
    //
    $.post('../zarzadzanie/przegladarka.php?typ=' + edytor + '&tok=' + $('#tok').val(), { folder: katalog, pole: input, stala: stala, akcja: 'f', nowy: $('#nowyfolder').val(), produkt: produkt, pamietaj: pamietaj, sortowanie: sortowanie, wyswietlanie: wyswietlanie, licznik: licznik }, function(data) {
        //
        $('#ekr_preloader').css('display','none');
        //
        $('#Przegladarka').fadeIn('fast', function() { $('#ListPlik' + edytor).html( data ); masowoProdukt(); } );
        //
        pokazChmurki();
        //
    });
}

function przegladarkaSzukaj( katalog, input, edytor, stala, produkt, pamietaj, sortowanie, wyswietlanie, licznik ) {
    //
    var ciagSzukania = $('#szukanafraza').val();
    ciagSzukania = ciagSzukania.replace(/\%/g,'');
    //
    if (ciagSzukania.length < 2) {
        //
        $.colorbox( { html:'<div id="PopUpInfo">Minimalna ilość znaków do wyszukiwania to 2</div>', initialWidth:50, initialHeight:50, maxWidth:'90%', maxHeight:'90%' } );
        return false;
        //
    } else {
        //
        $('#Przegladarka').css('display','none');
        $('#ekr_preloader').css('display','block');
        //
        if (pamietaj == undefined) {
            pamietaj = '';
        }
        if (sortowanie == undefined) {
            sortowanie = 'nazwa_rosnaco';
        } 
        if (wyswietlanie == undefined) {
            wyswietlanie = $('#PrzegladarkaWyglad').html();
        }    
        if (licznik == undefined) {
            licznik = 500;
        }     
        //
        $.post('../zarzadzanie/przegladarka.php?typ=' + edytor + '&tok=' + $('#tok').val(), { folder: katalog, pole: input, stala: stala, akcja: 's', szukaj: ciagSzukania, produkt: produkt, pamietaj: pamietaj, sortowanie: sortowanie, wyswietlanie: wyswietlanie, licznik: licznik }, function(data) {
            //
            $('#ekr_preloader').css('display','none'); 
            //
            $('#Przegladarka').fadeIn('fast', function() { $('#ListPlik' + edytor).html( data ); masowoProdukt(); } );
            //
            pokazChmurki();
            //
        });
        //
    }
    //
}

function getUrlParam(paramName) {
    var reParam = new RegExp('(?:[\?&]|&amp;)' + paramName + '=([^&]+)', 'i') ;
    var match = window.location.search.match(reParam) ;
    return (match && match.length > 1) ? match[1] : '' ;
}

function wstaw_obrazek( wartosc, akcja, stala, katalog ) {
    //
    if ( akcja == 'strona') {
        //
        var inp = $('#pole').val();
        $('#' + inp).val ( wartosc );
        $('#' + inp).focus();
        przegladarkaZamknij();
        pokaz_obrazek_ajax( inp, wartosc );
        //
    }
    if ( akcja == 'ckedit') {
        //
        var funcNum = getUrlParam('CKEditorFuncNum');
        var fileUrl = '/' + katalog + '/' + wartosc;
        window.opener.CKEDITOR.tools.callFunction(funcNum, fileUrl);    
        window.close();
        //
    }
    //
    if ( stala != '' ) {
        $('#ekr_preloader').css('display','block');
        $.post("wyglad/wyglad_zapisz_stala.php?tok=" + $('#tok').val(), { wart: wartosc, stala: stala }, function(data) {  $('#ekr_preloader').fadeOut(); });
    }
    //
    var allegro_foto = $('#pole').val();
    if (allegro_foto.substr(0,9) == 'opis_img_') {
        $('#kont_' + $('#pole').val() + ' img').attr('src','/' + katalog + '/' + wartosc);
    } 
    // zapis w ustawieniach menu - ikonka
    if ($('#' + inp).attr('class') == 'IkonkaMenu') {
        var tid = $('#' + inp).attr('data-id');
        zmienMenuPodkat(tid);
    }
}
 
// wyswietla obrazek ajaxem
function podgladObrazka(wartosc) {    
    //
    $("#podglad").html('<img src="obrazki/_loader_small.gif">');
    //
    $.get('ajax/obraz.php', { tok: $('#tok').val(), foto: wartosc, sz: '150', wy: '150', info: 'tak', sciezka: 'tak', zoom: 'tak' }, function(data) {
        if (data != '') {
            $("#podglad").html(data);
            $('.FotoPrzegladarka').colorbox({
                maxWidth: '100%',
                maxHeight: '100%'
            });         
        } else {
            $("#podglad").html('... brak podglądu ...');
        }
    });
}

function masowoProdukt() {
    //
    $('.zaznaczMasowo').click(function() {

       if ( $('input[name="zaznacz_masowo[]"]:checked').length > 0 ) {
          //
          $('#WybierzZdjecia').fadeIn();
          //
        } else {
          //
          $('#WybierzZdjecia').fadeOut();
          //
       }
       
    });   
    //
    $('#WybierzZdjecia').click(function() {
        //
        przegladarkaZamknij();
        //
        var pliki_do_wgrania = '';
        var suma_do_wgrania = 0;
        $('input[name="zaznacz_masowo[]"]:checked').each(function() {
            //
            pliki_do_wgrania += $(this).val() + ';';
            suma_do_wgrania++;
            //
        });
        //
        $.get('ajax/dodaj_zdjecia_wiele.php', { pliki: pliki_do_wgrania, id: parseInt($("#ile_pol").val()), katalog: $('#katalog_glowny').val() }, function(data) {
           $("#ile_pol").val( parseInt($("#ile_pol").val()) + suma_do_wgrania );
           if ( $('#wyniki .TabelaFoto').length ) {
                $('#wyniki .TabelaFoto:last').after(data);
           } else {
                $('#wyniki').html(data);
           }
           //
           pokazChmurki();    
           //
           var t = 1;
           $('#wyniki .TabelaFoto').each(function() {
              // input sortowanie
              $(this).find('.SortZdjecie').val(t);
              //
              t++;
           });               
           //
        });                                                  
        //
    });
    //
}   