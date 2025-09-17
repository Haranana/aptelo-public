function zmien_ceche(sort) {
    if (sort == undefined) {
        sort = '0';
    }  
    $('#OknoDodawaniaNowejCechy').html('');
    var id = $("#id_cecha").val();
    $("#cech_wartosc").html('<img src="obrazki/_loader_small.gif">');
    $.get('ajax/zmien_cechy.php',
         { id: id, sort: sort, tok: $('#tok').val() }, function(data) { 
              $('#cech_wartosc').css('display','none'); 
              $('#cech_wartosc').html(data); 
              $('#cech_wartosc').fadeIn();
              var ile_war = $('#id_wartosc').find('option').length;
              if (ile_war == 0) {
                  $('#id_wartosc').attr('disabled','disabled');
                  $('.InfoCechyDodaj').hide();
                  $('.DodanieCechy').show();
              } else {
                  $('.InfoCechyDodaj').show();
              }
         });
}
function typ_cechy(rodzaj) {
    $.get('ajax/typ_cechy.php',
     { id_unikalne: $("#id_unikalne").val(), rodzaj: rodzaj, tok: $('#tok').val() }, function(data) { 
        //
        if ( rodzaj == 'cechy' ) {
             if ( $('#dodawanie').length ) {
                  lista_cech('dodaj','nie');
             } else {
                  lista_cech('wyswietl','nie');
             }
        } else {
             if ( $('#dodawanie').length ) {
                  lista_cech('dodaj','tak');
             } else {
                  lista_cech('wyswietl','tak')
             }
        }
        //
     });
}
function lista_cech(akcja, kombinacje) {
    if (akcja == undefined) {
        akcja = 'dodaj';
    }
    if ($('#dodawanie').length) {
        akcja = 'dodaj';
    }
    if (kombinacje == undefined) {
        if ( $('#rodzajCechyCecha').prop('checked') ) {
             kombinacje = 'nie';
             $('#Kombinacje').show();
           } else {
             kombinacje = 'tak';
             $('#Kombinacje').hide();
        }
    }    
    //
    duplikowanie = 'nie';
    if ($('#duplikowanie').length) {
        duplikowanie = 'tak';
    }
    //
    if ( $('#rodzajCechyCecha').prop('checked') ) {
         rodzaj_cechy = 'cechy';
       } else {
         rodzaj_cechy = 'ceny';
    }
    //
    //
    $('#ekr_preloader').css('display','block');
    $("#dodaj_ceche").css('display','none');
    //
    $.ajax({ type :"post", 
             data : { id_cechy: $("#id_cecha").val(), id_wartosc: $("#id_wartosc").val(), id_unikalne: $("#id_unikalne").val(), akcja: akcja, kombinacje: kombinacje, rodzaj_cechy: rodzaj_cechy, duplikowanie: duplikowanie },
             url : "ajax/lista_cech.php?tok=" + $('#tok').val()
          }).done(function( data ) { 
             $('#ListaCechProduktu').css('display','none'); $('#ListaCechProduktu').html(data); $('#ListaCechProduktu').fadeIn(); $("#dodaj_ceche").css('display','block'); $('#ekr_preloader').delay(100).fadeOut('fast'); pokazChmurki();
                //
                if ( $('#ListaCechProduktu').html() != '' ) {
                     $('.CechyInfo').show();
                  } else {
                     $('.CechyInfo').hide();
                }
                //
                $('.RozwinFoto').click(function() {
                   idnr = $(this).attr('id');
                   //
                   if ( $('#tbl_' + idnr).css('display') == 'none' ) {
                        $('#tbl_' + idnr).slideDown();
                      } else {
                        $('#tbl_' + idnr).slideUp();
                   }
                   delete idnr;
                   //
                });
                //
             }           
    );     
}    
function zapisz_obraz_cechy(id_produktu, kombinacja_cech, id) {
    var wartosc = $("#zdjecie_cechy_" + id).val();
    $("#zapis_obrazka_" + id).html('<img src="obrazki/_loader_small.gif">');
    $.post("ajax/zapisz_obrazek_cechy.php?tok=" + $('#tok').val(),
         { id_produktu: id_produktu, kombinacja_cech:kombinacja_cech, wartosc: wartosc }, function(data) { $("#zapis_obrazka_" + id).html(''); $('#tbl_foto_cechy_' + id).slideUp(); $('#foto_cechy_' + id).html(data); });
}
function usun_obraz_cechy(id_produktu, kombinacja_cech, id) {
    $("#zapis_obrazka_" + id).html('<img src="obrazki/_loader_small.gif">');
    $.post("ajax/zapisz_obrazek_cechy.php?tok=" + $('#tok').val(),
         { id_produktu: id_produktu, kombinacja_cech:kombinacja_cech, wartosc: '' }, function(data) { $("#zdjecie_cechy_" + id).val(''); $("#zapis_obrazka_" + id).html(''); $('#tbl_foto_cechy_' + id).slideUp(); $('#foto_cechy_' + id).html(data); });
}
function zapisz_obraz_jednej_cechy(cecha) {
    var wartosc = $("#zdjecie_jednej_cechy_" + cecha).val();
    $("#zapis_obrazka_jednej_cechy_" + cecha).html('<img src="obrazki/_loader_small.gif">');
    $.post("ajax/zapisz_obrazek_jednej_cechy.php?tok=" + $('#tok').val(),
         { cecha: cecha, wartosc: wartosc }, function(data) { $("#zapis_obrazka_jednej_cechy_" + cecha).html(''); $('#tbl_foto_jednej_cechy_' + cecha).slideUp(); $('#foto_jednej_cechy_' + cecha).html(data); });
}
function usun_obraz_jednej_cechy(cecha) {
    $("#zapis_obrazka_jednej_cechy_" + cecha).html('<img src="obrazki/_loader_small.gif">');
    $.post("ajax/zapisz_obrazek_jednej_cechy.php?tok=" + $('#tok').val(),
         { cecha: cecha, wartosc: '' }, function(data) { $("#zdjecie_jednej_cechy_" + cecha).val(''); $("#zapis_obrazka_jednej_cechy_" + cecha).html(''); $('#tbl_foto_jednej_cechy_' + cecha).slideUp(); $('#foto_jednej_cechy_' + cecha).html(data); });
}
function zamien_w_cechach(wartosc, waga) {
    var wart = $(wartosc).val();
    regexp = eval("/,/g");
    if (waga == 'waga') {
        wart = wart.replace(regexp,".");
    } else {
        wart = format_zl( wart.replace(regexp,".") );
    }
    if (!isNaN(wart)) {      
        if (wart == 0) {
            $(wartosc).val('');
          } else {
            $(wartosc).val(wart);
        }
      } else {
        $(wartosc).val('');
    }    
}
function ajax_cecha_kwota(elem,id_prod,typ,nr_ceny,id_cech,id) {
    if (typ == 'netto') {
        var wartosc_vat = $('#vat').val();
        $('#netto_' + nr_ceny + '_' + id).html('<img src="obrazki/_loader_small.gif">');
        $('#brutto_' + nr_ceny + '_' + id).html('<img src="obrazki/_loader_small.gif">');
        $.get('ajax/zmien_ceny_cechy.php',
            { id_prod: id_prod, id_cech: id_cech, nr_ceny: nr_ceny, id: id, cena_netto: elem, typ: typ, vat: wartosc_vat, tok: $('#tok').val() }, function(data) { $('#netto_' + nr_ceny + '_' + id).html(data.netto); $('#brutto_' + nr_ceny + '_' + id).html(data.brutto); }, "json");     
    }   
    if (typ == 'brutto') {
        var wartosc_vat = $('#vat').val();
        $('#netto_' + nr_ceny + '_' + id).html('<img src="obrazki/_loader_small.gif">');
        $('#brutto_' + nr_ceny + '_' + id).html('<img src="obrazki/_loader_small.gif">');
        $.get('ajax/zmien_ceny_cechy.php',
            { id_prod: id_prod, id_cech: id_cech, nr_ceny: nr_ceny, id: id, cena_brutto: elem, typ: typ, vat: wartosc_vat, tok: $('#tok').val() }, function(data) { $('#netto_' + nr_ceny + '_' + id).html(data.netto); $('#brutto_' + nr_ceny + '_' + id).html(data.brutto); }, "json");     
    }
    if (typ == 'katalogowa') {
        $('#katalogowa_' + nr_ceny + '_' + id).html('<img src="obrazki/_loader_small.gif">');
        $.get('ajax/zmien_ceny_cechy.php',
            { id_prod: id_prod, id_cech: id_cech, nr_ceny: nr_ceny, id: id, cena_katalogowa: elem, typ: typ, vat: 0, tok: $('#tok').val() }, function(data) { $('#katalogowa_' + nr_ceny + '_' + id).html(data.katalogowa); }, "json");     
    }   
    if (typ == 'poprzednia') {
        $('#poprzednia_' + nr_ceny + '_' + id).html('<img src="obrazki/_loader_small.gif">');
        $.get('ajax/zmien_ceny_cechy.php',
            { id_prod: id_prod, id_cech: id_cech, nr_ceny: nr_ceny, id: id, cena_poprzednia: elem, typ: typ, vat: 0, tok: $('#tok').val() }, function(data) { $('#poprzednia_' + nr_ceny + '_' + id).html(data.katalogowa); }, "json");     
    }     
}
function ajax_cecha(typ,wartosc,id_cech,rodzaj) {
    wartosc = wartosc.replace(/\s/g, '');
    wartosc = wartosc.replace(',','.');
    if (rodzaj == undefined) {
        rodzaj = 'kwota';
    }
    if (typ == 'cena_netto') {
        var wartosc_vat = $('#vat').val();
        $('#td_cena_' + id_cech).html('<img src="obrazki/_loader_small.gif">');
        $.get('ajax/zmien_input_cechy.php',
            { cena_netto: wartosc, id: id_cech, rodzaj: rodzaj, vat: wartosc_vat, tok: $('#tok').val() }, function(data) { $('#td_cena_' + id_cech).html(data); });        
    }
    if (typ == 'cena_brutto') {
        var wartosc_vat = $('#vat').val();
        $('#td_cena_' + id_cech).html('<img src="obrazki/_loader_small.gif">');
        $.get('ajax/zmien_input_cechy.php',
            { cena_brutto: wartosc, id: id_cech, rodzaj: rodzaj, vat: wartosc_vat, tok: $('#tok').val() }, function(data) { $('#td_cena_' + id_cech).html(data); });        
    }    
    if (typ == 'waga') {
        $('#td_waga_' + id_cech).html('<img src="obrazki/_loader_small.gif">');
        $.get('ajax/zmien_input_cechy.php',
            { waga: wartosc, id: id_cech, tok: $('#tok').val() }, function(data) { $('#td_waga_' + id_cech).html(data); });        
    }  
    if (typ == 'domyslna') {
        $('#td_domyslna_' + id_cech).html('<img src="obrazki/_loader_small.gif">');
        $.get('ajax/zmien_input_cechy.php',
            { domyslna: wartosc, id: id_cech, tok: $('#tok').val() }, function(data) { $('#td_domyslna_' + id_cech).html(data); });        
    }       
    if (typ == 'prefix') {
        $('#td_prefix_' + id_cech).html('<img src="obrazki/_loader_small.gif">');
        $.get('ajax/zmien_input_cechy.php', { prefix: wartosc, rodzaj: rodzaj, id: id_cech, tok: $('#tok').val() }, function(data) { 
            $('#td_prefix_' + id_cech).html(data); 
            $('#td_cena_' + id_cech).html('<img src="obrazki/_loader_small.gif">');
            var prf = $('#td_prefix_' + id_cech).find('select').val();
            if (prf == 'mnoznik') {
                $.get('ajax/zmien_input_cechy.php', { prefix_input: 'mnoznik', id: id_cech, rodzaj: rodzaj, tok: $('#tok').val() }, function(data) { $('#td_cena_' + id_cech).html(data); });
            } else {
                $.get('ajax/zmien_input_cechy.php', { prefix_input: 'kwota', id: id_cech, rodzaj: rodzaj, tok: $('#tok').val() }, function(data) { $('#td_cena_' + id_cech).html(data); });              
            }
        });        
    }        
}
function ajax_cecha_magazyn(wartosc,id_prod,id_magazyn,id_wroc, typ) {
    $('#' + typ + '_' + id_wroc).html('<img src="obrazki/_loader_small.gif">');
    //
    $.post("ajax/zmien_magazyn_cechy.php?tok=" + $('#tok').val(), 
        { id_prod: id_prod, 
          ilosc: wartosc,
          magazyn: id_magazyn,
          id_wroc: id_wroc,
          typ: typ
        },
        function(data) { $('#' + typ + '_' + id_wroc).html(data); }           
    );           
}    
function ajax_cecha_skasuj(typ,wartosc) {
    $('#td_skasuj_' + wartosc).html('<img src="obrazki/_loader_small.gif">');
    $.get('ajax/skasuj_cechy.php',
          { typ: typ, id: wartosc, tok: $('#tok').val() }, 
          function(data) { $('#td_skasuj_' + wartosc).html(data); 
            //
            if ( $('#rodzajCechyCecha').prop('checked') ) {
                 rodzaj_cechy = 'cechy';
                 kombinacje = 'nie';
                 $('#Kombinacje').show();
               } else {
                 rodzaj_cechy = 'ceny';
                 kombinacje = 'tak';
                 $('#Kombinacje').hide();
            }
            //
            duplikowanie = 'nie';
            if ($('#duplikowanie').length) {
                duplikowanie = 'tak';
            }
            //
            $.ajax({ type :"post", 
                     data : { id_cechy: 0, id_wartosc: 0, id_unikalne: $("#id_unikalne").val(), akcja: '', kombinacje: kombinacje, rodzaj_cechy: rodzaj_cechy, duplikowanie: duplikowanie },
                     url : "ajax/lista_cech.php?tok=" + $('#tok').val()
                  }).done(function( data ) { 
                     $('#ListaCechProduktu').css('display','none'); $('#ListaCechProduktu').html(data); $('#ListaCechProduktu').fadeIn(); $("#dodaj_ceche").css('display','block'); 
                        if ( $('#ListaCechProduktu').html() != '' ) {
                             $('.CechyInfo').show();
                          } else {
                             $('.CechyInfo').hide();
                        }                     
                     }           
            ); 
            //
          });
}    
function dodaj_nowa_wartosc() {
    $('#OknoDodawaniaNowejCechy').html('<div style="margin:10px"><img src="obrazki/_loader.gif"></div>');
    var id_ce = $('#id_cecha').val();
    //
    $.get('ajax/dodaj_ceche_wartosc.php',
          { id: id_ce, tok: $('#tok').val() }, 
          function(data) { 
            $('#OknoDodawaniaNowejCechy').hide();
            $('#OknoDodawaniaNowejCechy').html(data); 
            $('#OknoDodawaniaNowejCechy').slideDown();
          });    
}  
function zapisz_nowa_wartosc() {
    $('#ZapiszCeche').html('<div style="margin:10px"><img src="obrazki/_loader.gif"></div>');
    //
    var sear = $('#OknoDodawaniaCechy').find('input').serialize(); 
    $.get('ajax/dodaj_ceche_wartosc.php',
          { data: sear, tok: $('#tok').val() }, 
          function(data) { 
              zmien_ceche(data);
          });    
}  
function dodaj_nowa_ceche() {
    $('#OknoDodawaniaNowejCechy').html('<div style="margin:10px"><img src="obrazki/_loader.gif"></div>');
    //
    $.get('ajax/dodaj_ceche.php',
          { tok: $('#tok').val() }, 
          function(data) { 
            $('#OknoDodawaniaNowejCechy').hide();
            $('#OknoDodawaniaNowejCechy').html(data); 
            $('#OknoDodawaniaNowejCechy').slideDown();
          });    
}  
function zapisz_nowa_ceche() {
    $('#ZapiszCeche').html('<div style="margin:10px"><img src="obrazki/_loader.gif"></div>');
    //
    var sear = $('#OknoDodawaniaCechy').find('input').serialize(); 
    $.get('ajax/dodaj_ceche.php',
          { data: sear, tok: $('#tok').val() }, 
          function(data) {  
              $('#cech_nazwy').html(data);
              zmien_ceche();
          });    
} 
function zamknij_nowa_cecha() {
    //
    $('#OknoDodawaniaNowejCechy').slideUp( function() { $('#OknoDodawaniaNowejCechy').html('') });
    //
}  

function dodaj_kombinacje_cech() {
  //
  var ciag_cech = '';
  //
  $('.WyborCechStock select').each(function() {
      //
      ciag_cech = ciag_cech + ',' + $(this).val();
      //
  });  
  //
  $.get('ajax/dodaj_kombinacje_cech.php',
        { id_unikalne: $("#id_unikalne").val(), data: ciag_cech, tok: $('#tok').val() }, 
        function(data) {  
            //
            if ( data == 'BLAD' ) {
                 //
                 $.colorbox( { html:'<div id="PopUpInfo">Wybrana kombinacja cech jest już dodana.</div>', initialWidth:50, initialHeight:50, maxWidth:'90%', maxHeight:'90%' } );
                 return false;
                 //
            }
            //
            if ( $('#dodawanie').length ) {
                 lista_cech('dodaj','tak');
            } else {
                 lista_cech('wyswietl','tak');
            }
            //
        });   
  //
}

function usun_kombinacje_cech(id_unikalne, ciag_cech) {
  //
  $.get('ajax/usun_kombinacje_cech.php',
        { id_unikalne: id_unikalne, data: ciag_cech, tok: $('#tok').val() }, 
        function(data) {  
            //
            if ( $('#dodawanie').length ) {
                 lista_cech('dodaj','tak');
            } else {
                 lista_cech('wyswietl','tak');
            }
            //
        });   
  //
}
