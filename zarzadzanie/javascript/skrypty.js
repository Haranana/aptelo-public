var szerokoscEkranu = 0;

$(window).load(function() {
    //
    szerokoscEkranu = $(window).width();
    //
});    

$(document).ready(function() {
  
    $('body').find('input').on('blur', function() {
       //
       var typ = $(this).attr('type').toLowerCase();
       if (typ == 'text' || typ == 'password' ) {
          $(this).val( $.trim($(this).val()) );
       }
       //
    });      
  
    MaleRwd();
    
    $(window).resize(function() {
      MaleRwd();
    });
    
    $("input").keypress(function (evt) {
    var charCode = evt.charCode || evt.keyCode;
    if (charCode  == 13) {
      return false;
    }
    });

	  blinkText('#ModulyAlert');
    $("form input:radio").css('border','0px');
    $("form input:checkbox").css('border','0px');
    $("form input:image").css('border','0px');
    $("form input:image").css('padding','0px');
    $("form input:image").css({borderRadius: 0});
    $("form select").css('height','auto');  
    $("form select").css('padding','8px 5px 8px 5px');    
	  $(".przyciskNon").css({ padding:"6px" });
    $(".przyciskNon").css({borderRadius: '4px'});    
    $(".przyciskBut").css({borderRadius: '4px'});
    $(".color").css("box-shadow", "none");   

    // pokazywanie strony zawsze od gory
    $('body').scrollTo(0);

    if ( $(window).width() > $('#StrGlowna').width() + 100 ) {
         $('#Pomoc').show();
         $('#PomocOnline').show();
         if ( $('#StrGlowna').width() > 800 ) {
              $('#ZakladkiBoczne').show();
         }
         //
         $('#Help').hide();
         //
         $('#DoDolu').show();    
       } else {
         $('#Pomoc').hide();
         $('#PomocOnline').hide();
         $('#ZakladkiBoczne').hide();
         //
         $('#Help').show();
         //
         $('#DoDolu').hide();          
    }    

    // zakladka pomocy
    $(window).resize(function() {
           
        if ( $(window).width() < $('#StrGlowna').width() + 100 ) {
             $('#Pomoc').hide();
             $('#PomocOnline').hide();
             $('#ZakladkiBoczne').hide();
             //
             $('#Help').show();
             //
           } else {
             $('#Pomoc').fadeIn();
             $('#PomocOnline').fadeIn();
             if ( $('#StrGlowna').width() > 800 ) {
                  $('#ZakladkiBoczne').fadeIn();
             }             
             //
             $('#Help').hide();
             //
        }
        //
            
        StrzalkiNawigacja();
        
    });    
    
    $(window).scroll( function() {
      
        StrzalkiNawigacja();
        
    });     
    
    function StrzalkiNawigacja() {

        if ( $(window).width() > 1120 ) {
            if ($(this).scrollTop() > 100 ) {
                $('#DoGory').show();
            } else {
                $('#DoGory').hide();
            }
            if ($(document).height() - $(this).scrollTop() == $(window).height()) {
                $('#DoDolu').hide();
            } else {
                $('#DoDolu').show();
            }
        } else {
            $('#DoGory').hide();
            $('#DoDolu').hide();
        }    
     
    }

    $.ZaladujObrazki();    
    
    // otwieranie linkow w nowej karcie
    $('.blank').attr('target', '_blank');
    
    $('#DoGory').click(function () {
      $.scrollTo(0,400);
    });
    $('#DoDolu').click(function () {
      $.scrollTo('100%',400);
    });       
    
    $(".cmxform").submit(function() {
      Formularz = $(this);
      if (Formularz.attr("method") == 'get') {
          //
          Formularz.find('input').each(function() {
              if ($(this).val() == '') {
                $(this).attr('disabled', true);
              }      
          });              
          //
          Formularz.find('select').each(function() {
              if ($(this).val() == '0') {
                $(this).attr('disabled', true);
              }
          });
      }
    });   
    
    $('#SzukanieNaglowek').hover(function() {
        $('#OpcjeSzukania').stop().fadeIn('fast');
    },function() {
        $('#OpcjeSzukania').hide();
    });

    $(".Menu").hover( function(){    
        //
        $('.PodMenu').css('display','none');
        //
        if ($(this).find('.PodMenu').css('display') == 'none') {
              
            $(this).find('.PodMenu').stop().slideDown(200, function() {
                $(".MenuRoz").css('display','none');
                $(this).find('.PodMenu').css('overflow','visible');
            });
            
        }
        },
        //
    function(){ 
        //
        if ($(this).find('.PodMenu').css('display') == 'block') { 
        
            $(".MenuRoz").css('display','none');
            $(this).find('.PodMenu').css('display','none');
            
        }     
        $('.PodMenu').css('display','none');
        //
    });   

    $('.GlownyListingKategorie .RozwinKategorie').off('click').click(function() {
        //
        if ( $('#StrGlowna').width() < 1000 ) {
            //
            if ($('.OknoKategorii').css('display') != 'none') { 
                //
                $('.OknoKategorii').slideUp();
                //
              } else {
                //
                $('.OknoKategorii').slideDown();
                //
            }     
            //
        }
        //      
    });

    if ( Mobil() == 'tak' || $('#StrGlowna').width() < 800 ) {
         //
         $('#GorneMenu').hide();
         $('#GorneMenuRwd').hide();
         $('#GorneMenuRwdKlik').show();
         //
    }
    if ( Mobil() == 'nie' && $('#StrGlowna').width() > 800 ) {
         //
         $('#GorneMenu').show();
         $('#GorneMenuRwdKlik').hide();
         $('#GorneMenuRwd').hide();
         //
    }
    
    $(window).resize(function() {
      
        if (szerokoscEkranu != $(window).width()) {
            //
            szerokoscEkranu = $(window).width();
            //
            if ( $('#StrGlowna').width() > 800 && Mobil() == 'nie' ) {
                 //
                 $('#GorneMenu').show();
                 $('#GorneMenuRwd').hide();
                 $('#GorneMenuRwdKlik').hide();
                 $('.MenuRoz').hide();
                 $('.OknoKategorii').show();
                 //
              } else {
                 //
                 $('#GorneMenu').hide();
                 $('#GorneMenuRwd').hide();
                 $('#GorneMenuRwdKlik').show();
                 $('.MenuRoz').show();
                 $('.OknoKategorii').hide();
                 //
            }
            //
            $('.PodMenu').hide();    
            //
        }
    
    });    

    // rozwijanie dla malych rozdzielczosci
    $('#GorneMenuRwdKlik').off('click').click( function(){
        //
        if ($('#GorneMenuRwd').css('display') == 'block') {
            //
            $('#GorneMenuRwd').stop().slideUp(200);
            //
        } else {
            //
            $('#GorneMenuRwd').stop().slideDown(200);
            
            //
        }
        //
    });
    
    $(".MenuRwd span").off('click').click( function(){      
        //
        var ido = $(this).attr("data-id");
        $('.Menu').each(function() {
            //
            if ( $(this).find('span').attr("data-id") != ido ) {
                 $(this).find('.PodMenuRwd').css('display','none');
            }
            //
        })
        //

        if ($("#rp"+ido).css('display') == 'none') {        
            $("#rp"+ido).stop().slideDown(200, function() {
                $("#rp"+ido).css('overflow','visible');
            });
        } else {        
            $("#rp"+ido).stop().slideUp(200, function() {
                $("#rp"+ido).css('display','none');
            });
        } 
        //
    });    

    $(".PodMenu li").hover(
    function(){      
        //
        if ($(this).find('ul').css('display') == 'none') {        
            $(this).find('ul').stop().slideDown(200);
        }
        //
    },
    function(){ 
        //   
        if ($(this).find('ul').css('display') == 'block') {          
            $(this).find('ul').css('display','none');
        }  
        //
    });   
    
    $(".RozwinMenu").click( function(){      
        $(this).find('ul').stop().slideDown(200);   
    });     
    
    if ( Mobil() == 'tak' ) {
         $('#WylogujRwd').show();
    }
    
    $("#Konto").hover(
    function(){   
        if ( Mobil() == 'nie' ) {
             $("#Panel").stop().fadeIn('fast');
        }
    },
    function(){ 
        if ( Mobil() == 'nie' ) {
             $("#Panel").hide();    
        }
    });    
    
    pokazChmurki();

    function textZastap(input){
          var originalvalue = input.val();
          input.focus( function(){
              if( $.trim(input.val()) == originalvalue ){ input.val(''); input.css( { color:'#000' } ) }
          });
          input.blur( function(){
              if( $.trim(input.val()) == '' ){ input.val(originalvalue); input.css( { color:'#a3a3a3' } ) }
          });
    }
      
    $('.obrazek').attr('autocomplete', 'off');
    $('.obrazek').bind('change',	
      function () {
        var id = $(this).attr("id");
        pokaz_obrazek_ajax(id, $(this).val());
      }
    );
    
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
    
    $(".PlatnoscKoszt").change(		
      function () {
        var type = this.type;
        var tag = this.tagName.toLowerCase();
        var wart = $(this).val();
        if (type == 'text' && tag != 'textarea' && tag != 'radio' && tag != 'checkbox') {
            //
            if (wart == 0) {
                $(this).val('0');
            } else {
                wart = wart.replace(/\s/g, '');
                wart = wart.replace(",",".")
                $(this).val(wart);
            }
            //
        }
      }
    ); 

    $(".Waga").change(		
      function () {
        // zamiana przecinkow na kropki
        var wart = $(this).val();
        regexp = eval("/,/g");
        wart = parseFloat(wart.replace(regexp,"."));                
        if (!isNaN(wart)) {
            if (wart == 0) {
                $(this).val('0');
              } else {
                $(this).val(wart.toFixed(4));
            }
          } else {
            $(this).val('');
        }
      }
    );     
    
    $(".kropkaPusta").change(		
      function () {
        var type = this.type;
        var tag = this.tagName.toLowerCase();
        if (type == 'text' && tag != 'textarea' && tag != 'radio' && tag != 'checkbox') {
            //
            zamien_krp($(this),'');
            //
        }
      }
    ); 

    $(".kropkaPustaZero").change(		
      function () {
        var type = this.type;
        var tag = this.tagName.toLowerCase();
        if (type == 'text' && tag != 'textarea' && tag != 'radio' && tag != 'checkbox') {
            //
            if ($(this).val() != '') {
                zamien_krp($(this), '0.00');
            }
            //
        }
      }
    ); 

    $(".calkowita").change(	
        function () {
            if (isNaN($(this).val())) {
                $(this).val('');
               } else {
                if ( isNaN(parseInt($(this).val())) ) {
                    $(this).val('');
                  } else {
                    $(this).val( parseInt($(this).val()) );
                }
            }        
        }
    );
    

    $(".zero").change(
        function () {
			var val = parseInt($(this).val());
			if( isNaN(val))
				val = 0;
			$(this).val(val);
        }
    );
    
    $(".ulamek").change(	
        function () {
            var wart = $(this).val();
            regexp = eval("/,/g");
            wart = wart.replace(/\s/g, '');
            wart = wart.replace(regexp,".");         
            if (isNaN(wart) || wart == '') {
                $(this).val('0.1');
               } else {
                wart = parseFloat(wart);
                if (wart > 1 || wart == 1) {
                    wart = '1.0';
                }
                $(this).val( wart );
            }        
        }
    );    

	// ponowne przeliczanie cen przy edycji produktu i zmianie stawki vat
    $("#vat").change(		
      function () {
          //
          var wartosc_vat_tab = $(this).val();
          var tab_vat = wartosc_vat_tab.split("|");
          var wartosc_vat = tab_vat[0];
                  
          for (x = 1; x <= 10; x++) {           
              if ($('#cena_' + x).val() != '') {
                  //
                  // zamiana przecinkow na kropki
                  var cena_netto = $('#cena_' + x).val();
                  if ($('#cena_' + x).length > 0) {
                      cena_netto = cena_netto.replace(',','.');
                  }
                  //
                  if (parseFloat(cena_netto) == 0 || isNaN(cena_netto)) {
                      var cena_netto = '';
                      var cena_brutto = '';   
                      var podatek_vat = '';              
                    } else {            
                      $('#cena_' + x).val( cena_netto );
                      var cena_netto = format_zl( cena_netto );
                      // cena brutto do 2 miejsc po przecinku
                      var cena_brutto = format_zl( parseIntMain(( cena_netto * (1 + (wartosc_vat / 100))) * 100 ) / 100 );
                      var podatek_vat = format_zl( cena_brutto - cena_netto );
                  }
                  $('#cena_' + x).val( cena_netto );
                  $('#v_at_' + x).val( podatek_vat );
                  $('#brut_' + x).val( cena_brutto );
              }
          }
          //
          if ( $(this).attr('data-id') == 'vat_cechy' ) {
            
               var cena_netto_podstawa = $('#cena_1_podstawa').val();
               var cena_brutto_podstawa = format_zl( parseIntMain(( cena_netto_podstawa * (1 + (wartosc_vat / 100))) * 100 ) / 100 );
          
               $('#brut_1_podstawa').val( cena_brutto_podstawa );
               
               // aktualizowanie cech
               $('#CechyProduktuEdycja .oblicz').each(function() {
                 
                   var cena_brutto_cecha_podstawa = format_zl( parseIntMain(( $(this).val() * (1 + (wartosc_vat / 100))) * 100 ) / 100 );
                   var id_cech = $(this).attr('id').split("_");
                   
                   $('#cecha_brut_' + id_cech[2]).val( cena_brutto_cecha_podstawa );                 
                 
               });
               
               sumaCech();
                  
          }
          
      }
    );      

    $(".oblicz").change(		
      function () {
          //
          var id = $(this).attr("id");
          var oblicz_id = TylkoLiczba(id);
          var wartosc_vat_tab = $('#vat').val();
          var tab_vat = wartosc_vat_tab.split("|");
          var wartosc_vat = tab_vat[0];
          //
          // zamiana przecinkow na kropki
          var cena_netto = $(this).val();
          cena_netto = cena_netto.replace(',','.');
          //
          if (parseFloat(cena_netto) == 0 || isNaN(cena_netto)) {
              if ( id.indexOf("cecha") > -1) {
                  var cena_netto = 0;
                  var cena_brutto = 0;   
                } else {
                  var cena_netto = '';
                  var cena_brutto = '';   
              }
              var podatek_vat = '';              
            } else {            
              $(this).val( cena_netto );
              var cena_netto = format_zl( cena_netto );
              // cena brutto do 2 miejsc po przecinku
              var cena_brutto = format_zl( parseIntMain(( cena_netto * (1 + (wartosc_vat / 100))) * 100 ) / 100 );
              var podatek_vat = format_zl( cena_brutto - cena_netto );
          }
          
          if ( id.indexOf("cecha") > -1) {
              $('#cecha_cena_' + TylkoLiczba(id) ).val( cena_netto );
              $('#cecha_brut_' + TylkoLiczba(id) ).val( cena_brutto );
              cenaMnoznik();
              sumaCech();
            } else {
              if ( id.indexOf("podstawa") > -1) {
                  $('#cena_1_podstawa').val( cena_netto );
                  $('#brut_1_podstawa').val( cena_brutto );
                  //
                  cenaMnoznik();
                  sumaCech();
                  //
                } else {
                  $('#cena_' + oblicz_id).val( cena_netto );
                  $('#v_at_' + oblicz_id).val( podatek_vat );
                  $('#brut_' + oblicz_id).val( cena_brutto );
              }
          }
      }
    );   

    $(".oblicz_brutto").change(		
      function () {
          var id = $(this).attr("id");
          var oblicz_id = TylkoLiczba(id);
          var wartosc_vat_tab = $('#vat').val();
          var tab_vat = wartosc_vat_tab.split("|");
          var wartosc_vat = tab_vat[0];
          //
          // zamiana przecinkow na kropki
          var cena_brutto = $(this).val();
          cena_brutto = cena_brutto.replace(/\s/g, '');
          cena_brutto = cena_brutto.replace(',','.');
          //
          if (parseFloat(cena_brutto) == 0 || isNaN(cena_brutto)) {
              if ( id.indexOf("cecha") > -1) {
                  var cena_netto = 0;
                  var cena_brutto = 0;   
                } else {
                  var cena_netto = '';
                  var cena_brutto = '';   
              }
              var podatek_vat = '';              
            } else {
              $(this).val( cena_brutto );
              var cena_netto = format_zl( cena_brutto / (1 + (wartosc_vat / 100)) );
              var cena_brutto = format_zl( cena_brutto );
              var podatek_vat = format_zl( cena_brutto - cena_netto );
          }
          //
          if ( id.indexOf("cecha") > -1) {
              $('#cecha_cena_' + TylkoLiczba(id) ).val( cena_netto );
              $('#cecha_brut_' + TylkoLiczba(id) ).val( cena_brutto );
              cenaMnoznik();
              sumaCech();
            } else {
              if ( id.indexOf("podstawa") > -1) {
                  $('#cena_1_podstawa').val( cena_netto );
                  $('#brut_1_podstawa').val( cena_brutto );
                  //
                  cenaMnoznik();
                  sumaCech();
                  //                  
                } else {
                  $('#cena_' + oblicz_id).val( cena_netto );
                  $('#v_at_' + oblicz_id).val( podatek_vat );
                  $('#brut_' + oblicz_id).val( cena_brutto );
              }            
          }          
      }
    );    
    
    var config = {
        filebrowserBrowseUrl : '/zarzadzanie/przegladarka.php?typ=ckedit&tok=' + $('#tok').val(),
        filebrowserImageBrowseUrl : '/zarzadzanie/przegladarka.php?typ=ckedit&tok=' + $('#tok').val(),
        filebrowserFlashBrowseUrl : '/zarzadzanie/przegladarka.php?typ=ckedit&tok=' + $('#tok').val(),
        filebrowserWindowWidth : '740',
        filebrowserWindowHeight : '580',
        filebrowserWindowFeatures : 'menubar=no,toolbar=no,minimizable=no,resizable=no,scrollbars=no' 
    };
    $('.wysiwyg').ckeditor(config);    
    
    textZastap($('#pole_wyszukiwania_produkt'));    
    textZastap($('#pole_wyszukiwania_zamowienie'));
    textZastap($('#pole_wyszukiwania_klient'));
    
    if ( $('#filtryPanelu').val() == 'tak' ) {
    
        if ( !$('#ukryjFiltry').length ) {

            // ukrywanie filtrow
            if ( $('#wyszukaj').length ) {
                if ( !$('#wyszukaj_ikona').length ) {
                     $("#naglowek_cont").after('<div id="ukryjFiltry"><span class="RozwinFiltr">pokaż filtry</span></div>');
                     $("#wyszukaj").hide();
                   } else {
                     $("#naglowek_cont").after('<div id="ukryjFiltry"><span class="ZwinFiltr">ukryj filtry</span></div>');
                }
            }
            
        }    
           
        $('#ukryjFiltry span').click( function() {
            if ( $("#wyszukaj").css('display') == 'none' ) {
                 $("#wyszukaj").slideDown();
                 $(this).html('ukryj filtry');
                 $(this).removeClass('RozwinFiltr').addClass('ZwinFiltr');
               } else {
                 $("#wyszukaj").slideUp();
                 $(this).html('pokaż filtry');
                 $(this).removeClass('ZwinFiltr').addClass('RozwinFiltr');
            }
        });
        
    }  
    
    usunPlikZdjecie();
    
    // ukrywanie licznikow meta tagow
    if ( $('#LicznikMeta').html() == 'nie' ) {
         $('.LicznikMeta').hide();
    }
    
    PrzeskalujColorbox();
      
    // formatowanie kodu pocztowego
    $('#postcode', '#allegroForm')

    .keydown(function (e) {
        var key = e.which || e.charCode || e.keyCode || 0;
        $phone = $(this);

        // Don't let them remove the starting '('
        if ($phone.val().length === 1 && (key === 8 || key === 46)) {
            $phone.val(''); 
            return false;
        } 
        // Reset if they highlight and type over first char.
        else if ($phone.val().charAt(0) !== '') {
            $phone.val(''+$phone.val()); 
        }

        // Auto-format- do not expose the mask as the user begins to type
        if (key !== 8 && key !== 9) {
            if ($phone.val().length === 2) {
                $phone.val($phone.val() + '-');
            }
        }

        // Allow numeric (and tab, backspace, delete) keys only
        return (key == 8 || 
                key == 9 ||
                key == 46 ||
                (key >= 48 && key <= 57) ||
                (key >= 96 && key <= 105)); 
    })

    .bind('focus click', function () {
        $phone = $(this);

        if ($phone.val().length === 0) {
            $phone.val('');
        }
        else {
            var val = $phone.val();
            $phone.val('').val(val); // Ensure cursor remains at the end
        }
    })

    .blur(function () {
        $phone = $(this);

        if ($phone.val() === '(') {
            $phone.val('');
        }
    });

});

$.validator.setDefaults({ 
    ignore: []
});  

function usunPlikZdjecie() {
    // usuwanie zdjec
    $('.usun_zdjecie').click(function() {
        //
        var atr = $(this).attr('data-foto');
        $('#' + atr).val('');
        $('#div' + atr).slideUp('fast');            
        //
    });
    
    $('.usun_plik').click(function() {
        //
        var atr = $(this).attr('data-plik');
        $('#' + atr).val('');            
        //
    });  
}

function pokazChmurki() {
    //
    $('.TipIkona, .TipChmurka').attr('aria-haspopup','true');
    //
    $('.TipIkona, .TipChmurka').hover(function(event) {
        //
        if ( $('#StrGlowna').width() > 800 && Mobil() == 'nie' ) {
            //
            //$('#wynik_zapytania').css('overflow','visible');
            //
            // ukrywa inne chmurki
            $('.TipIkona').find('b').hide();    
            $('.TipChmurka').find('b').hide(); 
            //
            // zmieni z-index
            $(this).css({ 'z-index' : 1000 });
            $(this).find('b').css({ 'z-index' : 1001 });
            //
            if ( $(this).find('b').length ) {
                 chmurka = $(this).find('b');
              } else {
                 chmurka = $(this).find('.Chmura');
            }
            // usuwa kropke na koncu tekstu
            var tekst_chmurki = chmurka.html();
            if ( tekst_chmurki.substr(-1) == '.' ) {
                 chmurka.html( tekst_chmurki.substr(0, tekst_chmurki.length - 1) );
            }
            chmurka.show();
            //
            // sprawdza czy tekst w chmurce nie jest dlugi - jezeli tak to usuwa nowrap
            if ( chmurka.outerWidth() > 380 ) {
                 //
                 chmurka.css({ 'white-space' : 'normal', 'min-width' : 350 });
                 //
            }
            //
            if ( chmurka.outerHeight() > 350 ) {
                 //
                 chmurka.css({ 'min-width' : 750 });
                 //              
            }
            //        
            var szerokoscTip = chmurka.outerWidth();
            var wysokoscTip = chmurka.outerHeight() + 10;
            var marginesGoraPoczatek = 0;
            var marginesGoraKoniec = 0;
            //
            // jezeli wysokosc chmurki jest wieksza od odleglosci od gory ekranu to wyswieli ja ponizej
            if ( pozycjaMyszki(event)[1] < wysokoscTip + 20 ) {
                 //
                 marginesGoraPoczatek = $('.TipIkona').outerHeight() + 20;
                 marginesGoraKoniec = $('.TipIkona').outerHeight() + 10;
                 //
               } else {
                 //
                 marginesGoraPoczatek = (wysokoscTip + 10) * -1;
                 marginesGoraKoniec = wysokoscTip * -1;
                 //
            }
            chmurka.css({ 'margin-top' : marginesGoraPoczatek });
            //
            // ustawia domyslny margines
            chmurka.css({ 'margin-left' : 0 });
            //
            // sprawdza czy chmurka nie wyjdzie za ekran po lewej
            if ( pozycjaMyszki(event)[0] > (szerokoscTip / 2) ) {
                 //
                 chmurka.css({ 'margin-left' : (szerokoscTip / 2) * -1 });
                 //
            }
            // sprawdza czy chmurka nie wyjdzie za ekran po prawej
            if ( $(window).width() - pozycjaMyszki(event)[0] < (szerokoscTip / 2) || $(this).find('b').hasClass('Lewe') ) {
                 //
                 chmurka.css({ 'margin-left' : (szerokoscTip - 10) * -1 });
                 //
            }          
            //
            chmurka.css({ 'opacity' : 0 });
            //
            setTimeout(function(){ chmurka.stop().animate({ 'opacity' : 1, 'margin-top' : marginesGoraKoniec }, 300) }, 500 );
            //
        }
        //
    },function() {
        //
        if ( $('#StrGlowna').width() > 800 && Mobil() == 'nie' ) {
            //
            if ( $(this).find('b').length ) {
                 $(this).find('b').hide();
                 $(this).find('b').css({ 'margin-top' : -10000, 'margin-left' : -10000, 'z-index' : 999 });
              } else {
                 $(this).find('.Chmura').hide();
                 $(this).find('.Chmura').css({ 'margin-top' : -10000, 'margin-left' : -10000, 'z-index' : 999 });
            }            
            //
            // zmieni z-index
            $(this).css({ 'z-index' : 998 });           
            //
            //$('#wynik_zapytania').css('overflow','hidden');
            //
        }
        //
    });  
    //      
}

function parseIntMain(num) {    
    return +(Math.round(num + "e+2")  + "e-2");
}

function format_zl(n,sep) {
    var minus = false;
    if (n < 0) {
        minus = true;
        n = n * -1;
    }
    var p,l,r = Math.round(n * 100)+'';
    while (r.length < 3) { r='0'+r; }
    l = r.length;
    r = r.substring(0,l-2) + "." + r.substring(l-2,l);
    if (sep!=undefined) {
        r=r.split('.');
        if (minus == true) {
            return '-' + (r[0].split('').reverse().join('').replace(/\d{3}/g,'$&'+sep).split('').reverse().join('') + '.' + r[1]);
          } else {
            return (r[0].split('').reverse().join('').replace(/\d{3}/g,'$&'+sep).split('').reverse().join('') + '.' + r[1]);
        }
    } else {
        if (minus == true) {
            return '-' + r;
          } else {
            return r;
        }
    }
}

// zmienia przecinek na kropke
function zamien_krp(wartosc, wstaw, calkowita) {
  
    var klasa = wartosc.attr('class');
    //
    if (wstaw == undefined) {
        wstaw = '';
    }
    zero = 1;
    if (klasa.indexOf('UlamekParametry') > -1 || klasa.indexOf('CalkowitaParametry') > -1) {
        zero = 0;
    }
    //
    var wart = $(wartosc).val();
    wart = wart.replace(/\s/g, '');
    regexp = eval("/,/g");
    wart = format_zl( wart.replace(regexp,".") );

    if (!isNaN(wart)) {
        if (wart == 0) {
            if ( zero == 0 ) {
                 $(wartosc).val(0);
            } else {
                 $(wartosc).val(wstaw);
            }
          } else {
            $(wartosc).val(wart);
        }
      } else {
        $(wartosc).val('');
    } 
    //
    if ( calkowita != undefined ) {
        if ( calkowita == 1 ) {
             $(wartosc).val( parseInt($(wartosc).val()) );
        }
    }
} 

// wyswietla obrazek ajaxem
function pokaz_obrazek_ajax(id, wartosc) {    
    //
    if ($("#"+id).val() != '') { 
        //
        if ( $("#fo"+id).length ) {
             $("#fo"+id + " .zdjecie_tbl").html('<img src="obrazki/_loader_small.gif" alt="" />');
             $("#div"+id).css('display','block');
             //
             $.get('ajax/obraz.php', { tok: $('#tok').val(), foto: wartosc }, function(data) {
                 if (data != '') {
                     $("#fo"+id).html(data);
                 } else {
                     $("#div"+id).css('display','none');
                 }
             });
        }
        //
    }
}

// zakladki poziome  
function gold_tabs(id, id_ed1, wysokosc, tryb, id_info_zakladka, id_info_zakladka_dwa) {
    //
    szerokosc = '98%';
    //
    if (wysokosc == undefined) {   
        wysokosc = 300;
    }    
    if (tryb == undefined || tryb == '') {   
        tryb = 'normalny';
    }
    //
    id_start = parseInt(id) - 10;
    id_koniec = parseInt(id) + 10;
    for (x = id_start; x < id_koniec; x++) {
        if ($('#info_tab_id_'+x)) { 
            $('#info_tab_id_'+x).css('display','none'); 
        }
        if ($("#link_"+x)) {
            $("#link_"+x).removeClass('a_href_info_tab_wlaczona');   
        }                                
    }       
    $("#link_"+id).addClass('a_href_info_tab_wlaczona');
    //
    // wlaczanie edytorow ckeditor
    if ($('#'+id_ed1 + id).length) {
        //
        for(var i in CKEDITOR.instances) {
          if (CKEDITOR.instances[CKEDITOR.instances[i].name]) {
            CKEDITOR.instances[CKEDITOR.instances[i].name].destroy();
          }
        }     
        //
        ckedit(id_ed1 + id, szerokosc, wysokosc, tryb);
    } 
    //
    if ( id_info_zakladka != undefined ) {
         //
         if ($('#'+id_info_zakladka + id).length) {
            //
            ckedit(id_info_zakladka + id, szerokosc, wysokosc, tryb);
            //
         }          
         //
    }
    if ( id_info_zakladka_dwa != undefined ) {
         //
         if ($('#'+id_info_zakladka_dwa + id).length) {
            //
            ckedit(id_info_zakladka_dwa + id, szerokosc, wysokosc, tryb);
            //
         }          
         //
    }    
    //
    $('#info_tab_id_'+id).fadeIn(); 
}

// zakladki pionowe    
function gold_tabs_horiz(id, id_zakladki_jezykowej, pole) {
    //
    for (x=0; x<50; x++) {
        if ($('#zakl_id_'+x)) { 
            $('#zakl_id_'+x).css('display','none'); 
        }
        if ($("#zakl_link_"+x)) {
            $("#zakl_link_"+x).removeClass('a_href_info_zakl_wlaczona');   
        }                                
    }
    if ( pole == undefined && id_zakladki_jezykowej == undefined ) {
        $('#zakl_id_'+id).fadeIn();
      } else {
        $('#zakl_id_'+id).css('display','block');   
    }
    $("#zakl_link_"+id).addClass('a_href_info_zakl_wlaczona');        
    //
    // jezeli ma wlaczyc zakladki jezykowe
    if ( id_zakladki_jezykowej != undefined ) {
        if ($('#info_tab_id_'+id_zakladki_jezykowej).length) {
            gold_tabs(id_zakladki_jezykowej, pole,400);
        }
    }
    //
}

// wlacz edytor ck
function ckedit(id, szerokosc, wysokosc, tryb, zrodlo) {
    if (tryb == undefined || tryb == '') {   
        tryb = 'normalny';
    }
    if ( zrodlo == undefined ) {
         przycisk = '';
      } else {
         przycisk = 'Source';
    }

    if (tryb == 'fullpage') {
      CKEDITOR.replace( id, {
          filebrowserBrowseUrl : '/zarzadzanie/przegladarka.php?typ=ckedit&tok=' + $('#tok').val(),
          filebrowserImageBrowseUrl : '/zarzadzanie/przegladarka.php?typ=ckedit&tok=' + $('#tok').val(),
          filebrowserFlashBrowseUrl : '/zarzadzanie/przegladarka.php?typ=ckedit&tok=' + $('#tok').val(),
          width: szerokosc,
          height: wysokosc,
          filebrowserWindowWidth : '740',
          filebrowserWindowHeight : '580',
          autoGrow_minHeight : wysokosc,
          filebrowserWindowFeatures : 'menubar=no,toolbar=no,minimizable=no,resizable=no,scrollbars=no',
          fullPage : true,
          extraPlugins : 'docprops',
          removeButtons: przycisk
        }
      );
    } else if (tryb == 'normalny') {
      CKEDITOR.replace( id, {
          filebrowserBrowseUrl : '/zarzadzanie/przegladarka.php?typ=ckedit&tok=' + $('#tok').val(),
          filebrowserImageBrowseUrl : '/zarzadzanie/przegladarka.php?typ=ckedit&tok=' + $('#tok').val(),
          filebrowserFlashBrowseUrl : '/zarzadzanie/przegladarka.php?typ=ckedit&tok=' + $('#tok').val(),
          width: szerokosc,
          height: wysokosc,
          filebrowserWindowWidth : '740',
          filebrowserWindowHeight : '580',
          autoGrow_minHeight : wysokosc,
          filebrowserWindowFeatures : 'menubar=no,toolbar=no,minimizable=no,resizable=no,scrollbars=no',
          removeButtons: przycisk
        }
      );
    }
}

// do zdjec
function openFileBrowser(id, stala, katalog, produkt){
    if (stala == undefined) {
        stala = '';
    }
    if (produkt == undefined) {
        produkt = '';
    }    
    przegladarka( katalog, id, 'strona', stala, produkt );
}

// do plikow elektronicznych
function openFileAllBrowser(id){
    przegladarka( 'pobieranie', id, 'strona', '' );
}

// do programu partnerskiego
function openFilePPBrowser(id){
    przegladarka( 'partnerzy_bannery', id, 'strona', '' );
}

// funkcja zmieniajaca sortowanie - uniwersalna
function cofnij(katalog, zmienne_do_przekazania, kat) {
    if (kat != undefined) {
        document.location.href = '/zarzadzanie/' + kat + '/' + katalog + '.php' + zmienne_do_przekazania; 
      } else {
        document.location.href = '/zarzadzanie/' + katalog + '/' + katalog + '.php' + zmienne_do_przekazania; 
    }
}

// uzywane do generowania drzewa kategorii
function podkat(id, wybrane, typ, zaznacz, plik, katalog) {
    //
    if (typ == undefined) {
        typ = 'no';
    }
    if (zaznacz == undefined) {
        zaznacz = '0';
    } 
    if (plik == undefined) {
        plik = '';
    }    
    if (katalog == undefined) {
        katalog = plik;
    }    
    if ( $('#glownaKategoria').length ) {
         czy_glowna = 'tak';
         id_glowna = parseInt($('#glownaKategoria').val());
       } else {
         czy_glowna = 'nie';
         id_glowna = 0;
    }
    //
    var tablica = id.split(',');
    //
    id = tablica[0];
    //
    $('#p_'+id).html('<img src="obrazki/_loader_small.gif">');
    $.get("ajax/drzewo_podkategorie.php",
        { pole: id, id: wybrane, typ: typ, zaznacz: zaznacz, plik: plik, katalog:katalog, glowna:czy_glowna, id_glowna:id_glowna, tok: $('#tok').val() },
        function(data) { 
            $('#p_'+id).css('display','none');
            $('#p_'+id).html(data);
            $('#p_'+id).css('padding-left','15px');
            $('#p_'+id).css('display','block');                                                           
            //
            if (tablica.length > 1) {
                tablica.shift();
                var str = tablica.join(",");
                podkat(str, wybrane, typ, zaznacz, plik);
            }
            // 
            $('.pkc td').find('input').click( function() {
                if ( $('#id_kategorii').length ) {
                     $('#id_kategorii').val( $(this).val() );
                     //
                     var checked = [];
                     // dla rabatow producenta i kategorii (wybor radio)
                     if ( $('#rpk').length ) {
                         $("input[name='id_kat']:checked").each( function() {
                             checked.push(parseInt($(this).val()));
                         });  
                     } else {                         
                         $("input[name='id_kat[]']:checked").each( function() {
                             checked.push(parseInt($(this).val()));
                         });
                     }
                     if ( checked.length == 0 ) {
                          $('#id_kategorii').val('');
                     }                      
                } else {  
                     if ( $('#drzewo_akcesoria_dodatkowe_kategoria').length ) {
                          lista_akcja_kategoria( $(this).val() );
                          $('#ButZapis').show();
                     }
                }
            });           
            //
            $('#img_'+id).html('<img src="obrazki/zwin.png" onclick="podkat_off('+ "'" + id + "','" + typ + "'" + ')" alt="Zwiń" />'); 
            //
            pokazChmurki();
            //
    });
}
function podkat_off(id, typ) {
    //
    if (typ == undefined) {
        typ = 'no';
    }
    //
    $('#p_'+id).css('display','none');
    $('#p_'+id).css('padding','0px');
    $('#img_'+id).html('<img src="obrazki/rozwin.png" onclick="podkat('+ "'" + id + "','','" + typ + "'" + ')" alt="Rozwiń" />'); 
}

// uzywane przy dodawaniu hitu, promocji itd
function podkat_produkty(pole) { 
    // jezeli tylko lista - uzywane np do podobnych
    if ($('#wynik_produktow_lista').length) {
        //
        //$('#formi').css('display','none');
        //
        $('#wynik_produktow_lista').css('display','block');
        $('#wynik_produktow_lista').html('<img src="obrazki/_loader_small.gif">');
        $.get("ajax/lista_produktow.php", 
            { rodzaj: "lista", id_kat: pole, id_prod: $('#id_wybrany_produkt').val(), id_pozostale: $('#jakie_id').val(), modul: $('#rodzaj_modulu').val(), tok: $('#tok').val() },
            function(data) { 
                $('#wynik_produktow_lista').css('display','none');
                $('#wynik_produktow_lista').html(data);
                $('#wynik_produktow_lista').css('display','block');   
                pokazChmurki();
        });    
        //
    } else {
        //
        if ( $('#rodzaj_modulu').length ) {
            //
            // do jakiego modulu bedzie wykorzystany
            var pol = $('#rodzaj_modulu').val();
            //        
            if ($('#wynik_produktow_' + pol).length) {
                //
                if ($('#formi').length) {
                    $('#formi').css('display','none');
                }
                //
                if ($('#ButZapis').length) {
                    $('#ButZapis').hide();
                }            
                if ($('#szukany').length) {
                    $('#szukany').val('');
                }
                if ($('#id_prod').length) {
                    $('#id_prod').val('');
                }            
                if ($('#WyborCechy').length) {
                    $('#WyborCechy').html('');
                }
                var id_klienta = 0;
                if ($('#id_klienta').length) {
                    id_klienta = $('#id_klienta').val();
                }
                //
                $('#wynik_produktow_' + pol).css('display','block');
                $('#wynik_produktow_' + pol).html('<img src="obrazki/_loader_small.gif">');
                $.get("ajax/lista_produktow.php",
                    { rodzaj: pol, id_kat: pole, id_klienta: id_klienta, tok: $('#tok').val() },
                    function(data) { 
                        $('#wynik_produktow_' + pol).css('display','none');
                        $('#wynik_produktow_' + pol).html(data);
                        $('#wynik_produktow_' + pol).css('display','block'); 
                        pokazChmurki();
                });        
            }
            //
        }
        //
    }
}

// uzywane przy dodawaniu hitu, promocji itd
function fraza_produkty() { 
    //
    // jezeli tylko lista - uzywane np do podobnych
    var fraza = $('#szukany').val();
    if ( fraza.length < 2 ) {
         $.colorbox( { html:'<div id="PopUpInfo">Minimalna ilość znaków do wyszukiwania to 2</div>', initialWidth:50, initialHeight:50, maxWidth:'90%', maxHeight:'90%' } );
         return false;
    }
    if ($('#wynik_produktow_lista').length) {
        //
        $('#wynik_produktow_lista').css('display','block');
        $('#wynik_produktow_lista').html('<img src="obrazki/_loader_small.gif">');
        $.get("ajax/lista_produktow.php", 
            { rodzaj: "lista", fraza: $('#szukany').val(), id_prod: $('#id_wybrany_produkt').val(), id_pozostale: $('#jakie_id').val(), modul: $('#rodzaj_modulu').val(), tok: $('#tok').val() },
            function(data) { 
                $('#wynik_produktow_lista').css('display','none');
                $('#wynik_produktow_lista').html(data);
                $('#wynik_produktow_lista').css('display','block');    
                pokazChmurki();
        });    
        //
    } else {
        //
        // do jakiego modulu bedzie wykorzystany
        var pol = $('#rodzaj_modulu').val();
        //        
        if ($('#wynik_produktow_' + pol).length && $('#szukany').length) {
            //
            if ($('#szukany').val().length < 2) {
                //
                $.colorbox( { html:'<div id="PopUpInfo">Minimalna ilość znaków w polu wyszukiwania to: 2</div>', initialWidth:50, initialHeight:50, maxWidth:'90%', maxHeight:'90%' } );
                //
            } else {
                //
                if ($('#ButZapis').length) {
                    $('#ButZapis').hide();
                }               
                if ($('#formi').length) {
                    $('#formi').css('display','none');
                }
                var id_klienta = 0;
                if ($('#id_klienta').length) {
                    id_klienta = $('#id_klienta').val();
                }                
                //
                // wylacza checboxy w kategoriach
                $('#drzewo input').prop('checked', false); 
                $('#id_prod').val('');
                //
                $('#wynik_produktow_' + pol).css('display','block');
                $('#wynik_produktow_' + pol).html('<img src="obrazki/_loader_small.gif">');
                $.get("ajax/lista_produktow.php",
                    { rodzaj: pol, fraza: $('#szukany').val(), id_klienta: id_klienta, tok: $('#tok').val() },
                    function(data) { 
                        $('#wynik_produktow_' + pol).css('display','none');
                        $('#wynik_produktow_' + pol).html(data);
                        $('#wynik_produktow_' + pol).css('display','block');                                                           
                        pokazChmurki();
                }); 
            }
        }
        //
    }
}

// uzywane przy podobnych i akcesoriach - do chowania listy produktow przy dodawaniu
function lista_akcja(id, rodzaj) {
    $('#drzewo_' + rodzaj).remove();
    $('#wynik_produktow_' + rodzaj).remove();
    $('#formi').stop().slideDown('fast');
    //
    $('#wybrany_produkt').html('<img src="obrazki/_loader_small.gif">');
    $.get("ajax/lista_pojedynczy_produkt.php",
        { id: id, tok: $('#tok').val() },
        function(data) { 
            $('#wybrany_produkt').html(data); 
            //
            $('#lista_do_wyboru').html('<img src="obrazki/_loader_small.gif">');
            $.get("ajax/lista_produktow_do_wyboru.php",
                { tok: $('#tok').val() },
                function(data) { 
                    $('#lista_do_wyboru').html(data);
                    pokazChmurki();
            });             
            pokazChmurki();
    }); 
}

// uzywane przy akcesoriach - dla kategorii
function lista_akcja_kategoria(id) {
    $('#drzewo_akcesoria_dodatkowe_kategoria').remove();
    $('#formi').stop().slideDown('fast');
    //
    $('#wybrany_produkt').html('<img src="obrazki/_loader_small.gif">');
    $.get("ajax/lista_pojedyncza_kategoria.php",
        { id: id, tok: $('#tok').val() },
        function(data) { 
            $('#wybrana_kategoria').html(data); 
            //
            $('#lista_do_wyboru').html('<img src="obrazki/_loader_small.gif">');
            $.get("ajax/lista_produktow_do_wyboru.php",
                { tok: $('#tok').val() },
                function(data) { 
                    $('#lista_do_wyboru').html(data);
                    pokazChmurki();
            });             
            pokazChmurki();
    }); 
}

function dodaj_do_listy(id, akcja) {
    //
    if ( id == '0' ) {
         $(".TabelaProdukt").find("input[name='id_produkt']").each(function() {
            if ( $(this).prop('checked') ) {
                 wstaw_id($(this).val(), akcja);
            }
         });
      } else {
         wstaw_id(id, akcja);
    }
    //
    // do jakiego modulu bedzie wykorzystany
    var modul = $('#rodzaj_modulu').val();
    //
    $('#wybrane_produkty').html('<img src="obrazki/_loader_small.gif">');
    //
    var id_glowne = 0;
    if ( $('#id_glowne').length ) {
         id_glowne = $('#id_glowne').val();
    }
    //    
    $.get("ajax/lista_produktow_kasuj.php",
        { id: $('#jakie_id').val(), id_glowne: id_glowne, modul: modul, tok: $('#tok').val() },
        function(data) { 
            $('#wybrane_produkty').css('display','none');
            $('#wybrane_produkty').html(data);   
            $('#wybrane_produkty').stop().slideDown('fast');
            //
            if (akcja == '1') {
                $('#lista_do_wyboru').html('<img src="obrazki/_loader_small.gif">');
                $.get("ajax/lista_produktow_do_wyboru.php",
                    { tok: $('#tok').val() }, 
                    function(data) { 
                        $('#lista_do_wyboru').html(data);                                                          
                        pokazChmurki();
                });              
            }
            pokazChmurki();
    });
}

// uzywane w w/w funkcji
function wstaw_id(id, akcja) {
    var aktualne_stany = $('#jakie_id').val();
    if (id != '') {
        if (aktualne_stany.indexOf(',' + id + ',') > -1) {
            // usunie tylko przy akcji = 0
            if ( akcja != '0' ) {
                aktualne_stany = aktualne_stany.replace(',' + id + ',' , ',');
                if (aktualne_stany.substr(0,1) != ',') {
                    aktualne_stany = ',' + aktualne_stany;
                }
                aktualne_stany = aktualne_stany.replace(',,' , ',');
                $('#jakie_id').val( aktualne_stany );
            }
        } else {
            if (aktualne_stany.substr(aktualne_stany.length - 1,1) != ',') {
                aktualne_stany = aktualne_stany + ',';
            }    
            $('#jakie_id').val( aktualne_stany  + id + ',' );
        }
    }
}    
            
// funkcja zlicza ilosc zaznaczonch checkbox
function akcja(akcja) {
    checked_box = new Array(); 
    // tworzy tablice z zaznaczonymi id checkbox
    if (akcja == 0) {
        var sel = 0;
        $("#wynik_zapytania").find("input[name='opcja[]']").each( function(){ 
        
            if ( $(this).attr("disabled") != 'disabled' ) {
                if (this.checked) { checked_box[sel] = this.value; sel++; } 
            }
            
        } );
    }
    // zaznacza wszystkie checkboxy i tworzy tablice z zaznaczonymi id checkbox
    if (akcja == 1) {
        var sel = 0;
        $("#wynik_zapytania").find("input[name='opcja[]']").each( function(){ 

            if ( $(this).attr("disabled") != 'disabled' ) {
                $(this).prop("checked",true); 
                if (this.checked) { checked_box[sel] = this.value; sel++; } 
            }
            
        } );
    }  
    // odznacza wszystkie checkboxy
    if (akcja == 2) {
        var sel = 0;
        $("#wynik_zapytania").find("input[name='opcja[]']").each( function(){ $(this).prop("checked",false); if (this.checked) { checked_box[sel] = this.value; sel++; } } );
    }       
}  

// formatowanie wyniku kalendarza
function zwroc_pelna_date(newDate) {
    dzien = newDate.getDate();
    if (dzien < 10) { dzien = '0' + dzien; }
    miesiac = (newDate.getMonth() + 1);
    if (miesiac < 10) { miesiac = '0' + miesiac; }
    //
    return dzien  + "-" + miesiac + "-" + newDate.getFullYear();
}

// obliczanie ilosci znakow do wpisania w polu textarea
function licznik_znakow(pole,licza,maxlimit) {
 if (pole.value.length > maxlimit) {
    pole.value = pole.value.substring(0, maxlimit);
   } else {
    $('#' + licza).html(maxlimit - pole.value.length);
 }
}

function licznik_znakow_meta(pole,licza) {
 $('#' + licza).html(pole.value.length);
}

// uzywane przy dodawaniu produktow do zamowienia
function produkt_akcja(id, rodzaj, id_klienta) {
    //
    $('#ButZapis').show();
    $('#dodajProdukt').hide();
    //
    $('#drzewo_' + rodzaj).remove();
    $('#wynik_produktow_' + rodzaj).remove();
    $('#formi').stop().slideDown('fast');
    //
    $('#wybrany_produkt').html('<img src="obrazki/_loader_small.gif">');
    $.get("ajax/zamowienie_produkt_dodaj.php",
        { id: id, id_klienta: id_klienta, tok: $('#tok').val() },
        function(data) { 
            $('#wybrany_produkt').html(data); 
            //
    }); 
}

function wyswietlCechy(pole) {
  sumaCech();
}

function wyswietlCechyCeny(id_produktu, poziom_cen, id_waluty) {
  //
  var ciag = '';
  $('.cechaProduktu').each(function() {
      //
      var id_cechy = TylkoLiczba( $(this).attr('id') );
      //
      var tab_wartosci = $(this).val();
      var tab_tmp = tab_wartosci.split(';');
      var id_wartosci = tab_tmp[0];
      //
      ciag += id_cechy + '-' + id_wartosci + ','
      //
  });
  //
  $('#ekr_preloader').css('display','block');
  $.get("ajax/zamowienie_produkt_dodaj.php",
      { cechy: ciag, id_produktu: id_produktu, poziom_cen: poziom_cen, id_waluty: id_waluty, tok: $('#tok').val() },
      function(data) { 
          $('#ekr_preloader').css('display','none');
          //
          if ( parseInt(data.netto) == 0 && parseInt(data.brutto) == 0 ) {
               data.netto = $('#zrodlo_netto').val();
               data.brutto = $('#zrodlo_brutto').val();
          }
          //
          $('#cena_1_podstawa').val( data.netto );
          $('#brut_1_podstawa').val( data.brutto );
          //
          var wartoscNetto = roundLiczba( parseFloat($('#cena_1_podstawa').val()), 2);
          var wartoscBrutto = roundLiczba( parseFloat($('#brut_1_podstawa').val()), 2);

          $('#cena_1').val( wartoscNetto );
          $('#brut_1').val( wartoscBrutto );
          $('#v_at_1').val( roundLiczba( wartoscBrutto - wartoscNetto, 2) );
          //
  }, "json");    
  //
}

function sumaCech() {
  
  $('#cena_1_podstawa_mnoznik').val( roundLiczba($('#cena_1_podstawa').val(), 2) );
  $('#brut_1_podstawa_mnoznik').val( roundLiczba($('#brut_1_podstawa').val(), 2) );
  //
  var CenaMnoznikBrutto = 0;
  var CenaMnoznikNetto = 0;
  //
  $('#CechyProduktu select').each(function() {
      var warMno = $(this).val().split(';');
      if ( warMno[5] == 'mnoznik' ) {
           CenaMnoznikNetto += $('#cena_1_podstawa').val() * warMno[4];
           CenaMnoznikBrutto += $('#brut_1_podstawa').val() * warMno[4];      
      }
  }); 

  if ( CenaMnoznikBrutto > 0 ) {
       $('#cena_1_podstawa_mnoznik').val( roundLiczba(CenaMnoznikNetto,2) );
       $('#brut_1_podstawa_mnoznik').val( roundLiczba(CenaMnoznikBrutto,2) );    
  }
  
  $('#CechyProduktu select').each(function() {
      var vale = $(this).val();
      var wartosc = vale.split(';');
      
      cenaNetto = wartosc[3];
      cenaBrutto = wartosc[4];
      
      var wartosc_vat_tab = $('#vat').val();
      var tab_vat = wartosc_vat_tab.split("|");
      var wartosc_vat = tab_vat[0];  
      cenaBrutto = roundLiczba(cenaNetto * ((100 + parseInt(wartosc_vat)) / 100), 2);      
      
      cechaTyp = wartosc[5];

      if ( cechaTyp == 'kwota' ) {
          $("#cecha_cena_" + wartosc[1]).val(cenaNetto);
          $("#cecha_brut_" + wartosc[1]).val(cenaBrutto);
      }
      if ( cechaTyp == 'procent' ) {
          $("#cecha_cena_" + wartosc[1]).val( roundLiczba($('#cena_1_podstawa').val() * (cenaNetto / 100),2) );
          $("#cecha_brut_" + wartosc[1]).val( roundLiczba($('#brut_1_podstawa').val() * (cenaBrutto / 100),2) );
      }
      if ( cechaTyp == 'mnoznik' ) {
          $("#cecha_cena_" + wartosc[1]).val( roundLiczba($('#cena_1_podstawa').val() * wartosc[4],2) );
          $("#cecha_brut_" + wartosc[1]).val( roundLiczba($('#brut_1_podstawa').val() * wartosc[4],2) ); 
      }      
      //
      $('#cecha_prefix_' + wartosc[1]).val( wartosc[2] );
      //
  });

  var sumaNetto = 0;
  var sumaBrutto = 0;
  
  $('.cechaProduktu').each( function() {
      var id = TylkoLiczba( $(this).attr('id') );
      if ( $('#cecha_prefix_' + id).val() == '-' ) {
          sumaNetto -= parseFloat($('#cecha_cena_' + id).val());
          sumaBrutto -= parseFloat($('#cecha_brut_' + id).val());
      }
      if ( $('#cecha_prefix_' + id).val() == '+' ) {
          sumaNetto += parseFloat($('#cecha_cena_' + id).val());
          sumaBrutto += parseFloat($('#cecha_brut_' + id).val());
      }
  });
  
  var wartoscNetto = roundLiczba( parseFloat($('#cena_1_podstawa_mnoznik').val()) + sumaNetto, 2);
  var wartoscBrutto = roundLiczba( parseFloat($('#brut_1_podstawa_mnoznik').val()) + sumaBrutto, 2);

  $('#cena_1').val( wartoscNetto );
  $('#brut_1').val( wartoscBrutto );
  $('#v_at_1').val( roundLiczba( wartoscBrutto - wartoscNetto, 2) );
 
}

function cenaMnoznik() {
  if ( $('#cena_1_podstawa_mnoznik').length ) {
      $('#cena_1_podstawa_mnoznik').val(roundLiczba( parseFloat($('#cena_1_podstawa').val()), 2));
      $('#brut_1_podstawa_mnoznik').val(roundLiczba( parseFloat($('#brut_1_podstawa').val()), 2));
  }
}

function przeliczCechy() {

  sumaCech();
  
}

// wycina z ciagu tylko liczbe
function TylkoLiczba(str){
    objReg = /^\d+(\.\d+)?$/;
    var arr = str.split("");
    var str_return = "";
    for ( var i = 0; i < arr.length; i++ ){
        if ( arr[i].match(objReg) ) { str_return += arr[i]; }
    }
    delete arr;
    return str_return;
}

// licznik do wygasniecia sesji
function odliczaj(o,sek,un){
    //
    var d = new Date();
    var sekundy = parseInt(d.getTime() / 1000);
    //
    if ( sekundy - un > 60 ) {
        koniecCzasu();       
    }
    //
    var min = parseInt(sek / 60);
    var sekun = sek - (min * 60);
    $("#" + o).html(min + " min, " + sekun + " s");
    if(sek > 0) { 
        setTimeout(function(){odliczaj(o,--sek,sekundy)},1e3) ;
      } else {
        koniecCzasu();
    }
}

function createCookie(name,value,days) {
    if (days) {
        var date = new Date();
        date.setTime(date.getTime()+(days*24*60*60*1000));
        var expires = "; expires="+date.toGMTString();
    }
    else var expires = "";
    document.cookie = name+"="+value+expires+"; path=/";
}

function wersja( nr ) {
    $('#ekr_preloader').css('display','block');
    $.post("ajax/wersja.php",
        function(data) { 
          $('#ekr_preloader').css('display','none');
          var tekst = '';
          if ( data.length < 5 ) {
              tekst = 'Nie można w tej chwili sprawdzić aktualizacji sklepu ...';
          } else {
              if ( parseFloat(data) == parseFloat(nr) ) {
                  tekst = 'Wersja sklepu jest aktualna, nie ma nowszej wersji oprogramowania.';
                } else {
                  tekst = 'Jest dostępna nowsza wersja sklepu oznaczona jako: <b>' + data + '</b> <br /><br />Aktualizacje do sklepu można pobrać na stronie www.shopgold.pl';
              }
          }
          $.colorbox( { html:"<div id='PopUpInfo'>" + tekst + "</div>", initialWidth:50, initialHeight:50, maxWidth:'90%', maxHeight:'90%' } );            
        }           
    );  
}

function usunKod() {
    createCookie("kod","tak");
    window.location.reload();
}

function rozwinKod() {
    if ( $('#usunKod').css('display') == 'none' ) {
         $('.UsunKod').hide();
         $('#usunKod').fadeIn();
    }
}

function roundLiczba(liczba,poprzecinku) {

    if ( poprzecinku == 2) {
         liczba = Math.round(liczba * 100) / 100
    }
    
    var wynik;
    var wart = ''+liczba+'';
    
    var wynikCiag;
    regexp = eval("/,/g");
    wart = wart.replace(/\s/g, '');
    wart = parseFloat(wart.replace(regexp,"."));                
    if (isNaN(wart)) {
        liczba = 0;
    } else {
        liczba = wart;
    }    

    poprzecinku = Number(poprzecinku);
    if (poprzecinku < 1) {
      wynik = (Math.round(liczba)).toString();
    } else {
      var liczbaCiag = liczba.toString();
      if (liczbaCiag.lastIndexOf(".") == -1) {
        liczbaCiag += ".";
      }
      var cutoff = liczbaCiag.lastIndexOf(".") + poprzecinku;
      var d1 = Number(liczbaCiag.substring(cutoff,cutoff+1));
      var d2 = Number(liczbaCiag.substring(cutoff+1,cutoff+2));
      if (d2 >= 5) {
        if (d1 == 9 && cutoff > 0) {
          while (cutoff > 0 && (d1 == 9 || isNaN(d1))) {
            if (d1 != ".") {
              cutoff -= 1;
              d1 = Number(liczbaCiag.substring(cutoff,cutoff+1));
            } else {
              cutoff -= 1;
            }
          }
        }
        d1 += 1;
      } 
      if (d1 == 10) {
        liczbaCiag = liczbaCiag.substring(0, liczbaCiag.lastIndexOf("."));
        var roundedNum = Number(liczbaCiag) + 1;
        wynik = roundedNum.toString() + '.';
      } else {
        wynik = liczbaCiag.substring(0,cutoff) + d1.toString();
      }
    }
    if (wynik.lastIndexOf(".") == -1) {
      wynik += ".";
    }
    var decs = (wynik.substring(wynik.lastIndexOf(".")+1)).length;
    for(var i=0;i<poprzecinku-decs;i++) wynik += "0";
    return wynik;
}

// funkcja do powiekszania zdjec w listingu
function ZoomIn(th,evt) {
    //
    if ( $('#StrGlowna').width() > 800 ) {
        //
        //$('#wynik_zapytania').css('overflow','visible');
        //
        $(".imgzoom .zoom").css('display','none'); 
        WysEkranu = $(window).height();
        Pozycja = pozycjaMyszki(evt)[1];    
        $("#" + $(th).attr("id") + " .zoom").css('display','block');
        WysDiva = $("#" + $(th).attr("id") + " .zoom").height();
        //
        if (parseInt(WysEkranu) < parseInt(Pozycja) + parseInt(WysDiva)) {
            IlePodniesc = (parseInt(Pozycja) + parseInt(WysDiva) - parseInt(WysEkranu)) + 50;
            $("#" + $(th).attr("id") + " .zoom").stop().animate( { 'margin-top' : '-' + IlePodniesc + 'px' } );
        }
        //
    }
    //
}
function ZoomOut(th) {
    //
    if ( $('#StrGlowna').width() > 800 ) {
        //  
        $(".imgzoom .zoom").css('display','none');  
        $(".imgzoom .zoom").css('margin-top','-20px'); 
        //
        //$('#wynik_zapytania').css('overflow','hidden');
        //
    }
    //
}; 

// funkcja wyswietlania szczegolow w info zamowienia i allegro
function PodgladIn(th,evt,modul) {
    //
    if ( $('#StrGlowna').width() > 800 ) {
        //    
        //$('#wynik_zapytania').css('overflow','visible');
        //
        $(".zmzoom .podglad_zoom").css('display','none'); 
        var nrId = $(th).attr("id");
        WysEkranu = $(window).height();
        Pozycja = pozycjaMyszki(evt)[1];
        
        $("#" + nrId + " .podglad_zoom").css('display','block');
        $("#" + nrId + " .okno_zoom").html('<img src="obrazki/_loader_small.gif">');
        $.post("ajax/" + modul + "_szczegoly.php?tok=" + $('#tok').val(),
            { id: nrId },
            function(data) { 
                $("#" + nrId + " .podglad_zoom").html(data); 
                WysDiva = $("#" + nrId + " .podglad_zoom").height();
                //
                if (parseInt(WysEkranu) < parseInt(Pozycja) + parseInt(WysDiva)) {
                    IlePodniesc = (parseInt(Pozycja) + parseInt(WysDiva) - parseInt(WysEkranu)) + 50;
                    $("#" + nrId + " .podglad_zoom").stop().animate( { 'margin-top' : '-' + IlePodniesc + 'px' }, 200 );
                }
            }           
        );   
        //
    }
    //
}
function PodgladOut(th,modul) {
    //
    if ( $('#StrGlowna').width() > 800 ) {
        //    
        var nrId = $(th).attr("id");
        $(".zmzoom_" + modul + " .podglad_zoom").css('display','none');   
        $("#" + nrId + " .podglad_zoom").css('margin-top','-20px');   
        //
        //$('#wynik_zapytania').css('overflow','hidden');
        //
    }
    //
}; 

// funkcja wyswietlania info o aukcji allegro w listingu produktow
function PodgladAllegro() {
    // 
    $('.InfoAllegro').hover(function() {
        //
        if ( $('#StrGlowna').width() > 800 ) {
            //
            //$('#wynik_zapytania').css('overflow','visible');
            //    
            $('.InfoAllegro').find('div').hide();       
            //
            var id_allegro = $(this).attr('id');
            $('#' + id_allegro).find('div').show();
            $('#' + id_allegro).find('div').html('<img src="obrazki/_loader_small.gif" alt="" />');
            //
            $.post("ajax/produkt_aukcja_info.php?tok=" + $('#tok').val(),
                { id: id_allegro },
                function(data) {             
                    $('#' + id_allegro).find('div').html(data);       
                }           
            ); 
            //
        }
        //
    }, function() {
        //
        if ( $('#StrGlowna').width() > 800 ) {
            //
            $('.InfoAllegro').find('div').hide();
            $(this).find('div').html(''); 
            //
            //$('#wynik_zapytania').css('overflow','hidden');
            //
        }
        //
    });
    //        
}

function pozycjaMyszki(e) {
    var pozX = 0;
    var pozY = 0;
    if (!e) var e = window.event;
    if (e.pageX || e.pageY) {
         pozX = e.pageX;
         pozY = e.pageY;
    } else if (e.clientX || e.clientY) {
         pozX = e.clientX + document.body.scrollLeft;
         pozY = e.clientY + document.body.scrollTop;
    }
    return [pozX,pozY - $(window).scrollTop()]
}

// migajacy tekst
function blinkText(selector){
    $(selector).fadeOut('slow', function(){
        $(this).fadeIn('slow', function(){
            blinkText(this);
        });
    });
}

function PrzeskalujColorbox() {
    //
    // zmiana wielkosci okna colorbox po przekalowaniu
    // tworzy zmienna do przechowywania czasu
    var czasResize;

    // jezeli jest zmiana wielkosci ekranu
    $(window).resize(function() {

        if (czasResize) {  
            clearTimeout(czasResize) 
        };
        // tworzy zmienna czasu z opoznieniem
        czasResize = setTimeout(function() {
            //
            if ( $('.GoogleShop').length ) {   
                // jezeli colorbox jest widoczny
                if ($('#cboxOverlay').is(':visible')) {  
                    // przeskalowuje okno
                    $.colorbox.resize({ width: $('.GoogleShop').width() + 70 });
                    //
                    $.colorbox.resize();
                    //
                }              
            } else {
                // sprawdza szerokosc strony
                var mojaSzerokosc = ($('#StrGlowna').width() * 0.9), procentowaSzerokosc = .90;    
                // jezeli colorbox jest widoczny
                if ($('#cboxOverlay').is(':visible')) {  
                    // przeskalowuje okno
                    $.colorbox.resize({ width: ( $(window).width() > ( mojaSzerokosc+20) )? mojaSzerokosc : Math.round( $(window).width() * procentowaSzerokosc ) });
                    //
                    $.colorbox.resize();
                    //
                }
            }
            //
        }, 300) ;   

    });

}

// przeskalowuje sklep przy rozdzielczosci mniejszej od 760px
function MaleRwd() {

    if ( $(window).width() < $('#StronaPanel').width() ) {
      
        var przelicznik = ($(window).width() / $('#StronaPanel').outerWidth());
        var przelicznik_aktywny = przelicznik * 0.96;

        $('#StronaPanel').css({
                '-webkit-transform': 'scale(' + przelicznik_aktywny + ')',
                '-moz-transform': 'scale(' + przelicznik_aktywny + ')',
                '-ms-transform': 'scale(' + przelicznik_aktywny + ')',
                '-o-transform': 'scale(' + przelicznik_aktywny + ')',
                'transform': 'scale(' + przelicznik_aktywny + ')'
        });
        
        var marginesLewy = $(window).width() - parseInt($('#StronaPanel').outerWidth() * przelicznik_aktywny);
        
        $('#StronaPanel').css({ 'left' : parseInt((marginesLewy / 2) * 0.98) });
        
        // okno wygladu
        
        if ( $('#edytuj_okno').length ) {

            $('#edytuj_okno').css({
                    '-webkit-transform': 'scale(' + przelicznik_aktywny + ')',
                    '-moz-transform': 'scale(' + przelicznik_aktywny + ')',
                    '-ms-transform': 'scale(' + przelicznik_aktywny + ')',
                    '-o-transform': 'scale(' + przelicznik_aktywny + ')',
                    'transform': 'scale(' + przelicznik_aktywny + ')'
            });
            
            var marginesLewy = $(window).width() - parseInt($('#edytuj_okno').outerWidth() * przelicznik_aktywny);
            
            $('#edytuj_okno').css({ 'margin' : '0px', 'left' : parseInt((marginesLewy / 2) * 0.98) });
            
        }
        
        // przegladarka plikow
        
        if ( $('#ListPlikstrona').length ) {

            $('#ListPlikstrona').css({
                    '-webkit-transform': 'scale(' + przelicznik_aktywny + ')',
                    '-moz-transform': 'scale(' + przelicznik_aktywny + ')',
                    '-ms-transform': 'scale(' + przelicznik_aktywny + ')',
                    '-o-transform': 'scale(' + przelicznik_aktywny + ')',
                    'transform': 'scale(' + przelicznik_aktywny + ')'
            });
            
            var marginesLewy = $(window).width() - parseInt($('#ListPlikstrona').outerWidth() * przelicznik_aktywny);
            
            $('#ListPlikstrona').css({ 'margin' : '0px', 'left' : parseInt((marginesLewy / 2) * 0.98) });
            
        }        

    } else {
     
        przelicznik_aktywny = 1;
        $('#StronaPanel, #edytuj_okno, #ListPlikstrona').css({
                '-webkit-transform': 'scale(' + przelicznik_aktywny + ')',
                '-moz-transform': 'scale(' + przelicznik_aktywny + ')',
                '-ms-transform': 'scale(' + przelicznik_aktywny + ')',
                '-o-transform': 'scale(' + przelicznik_aktywny + ')',
                'transform': 'scale(' + przelicznik_aktywny + ')'
        });   

        $('#StronaPanel').css({ 'left' : 0 });   
        
        if ( $('#edytuj_okno').length ) {
             $('#edytuj_okno').css({ 'margin' : '0px auto', 'left' : 0 });   
        }
        
        if ( $('#ListPlikstrona').length ) {
             $('#ListPlikstrona').css({ 'margin' : '0px auto', 'left' : 0 });   
        }        
      
    } 
  
}
 
function Mobil() {
    return $('#Mobilny').html();    
}

$.validator.setDefaults({
    errorPlacement: function (error, element) {
        if (element.is(":checkbox")) {
            // Obsługa checkboxów: dodaj błąd na końcu najbliższego <td>
            element.closest("td").append(error);
        } else {
            // Sprawdź, czy istnieje <em> w <td>
            var emElement = element.closest("td").find("em");
            if (emElement.length) {
                // Jeśli <em> istnieje w <td>, wstaw błąd za nim
                emElement.after(error);
            } else if (element.closest("p").length) {
                // Sprawdź, czy istnieje <em> w <p>
                var emInP = element.closest("p").find("em");
                if (emInP.length) {
                    emInP.after(error); // Wstaw błąd za <em> w <p>
                } else {
                    // Jeśli brak <em>, wstaw błąd na końcu <p>
                    element.closest("p").append(error);
                }
            } else if (element.closest("td").length) {
                // Jeśli brak <em>, ale element jest w <td>, wstaw błąd na końcu <td>
                element.closest("td").append(error);
            } else {
                // W pozostałych przypadkach, wstaw błąd zaraz za elementem
                element.after(error);
            }
        }
    },
    highlight: function (element, errorClass, validClass) {
        $(element).addClass(errorClass).removeClass(validClass);
    },
    unhighlight: function (element, errorClass, validClass) {
        $(element).removeClass(errorClass).addClass(validClass);
    }
});