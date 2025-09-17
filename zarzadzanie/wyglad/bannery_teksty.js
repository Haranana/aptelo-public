function ZmienCien(nr, wartosc) {
    if ( wartosc == 'tak' ) {
         $('#cien_linia_' + nr).stop().slideDown();
    } else {
         $('#cien_linia_' + nr).stop().slideUp();
    }
}
function PodmienGrafike() {
    if ( $('#forma_obrazek').prop('checked') == true ) {

         $.post("wyglad/bannery_plik.php", { plik : $('#foto').val() }, function(data) { 

            if ( parseInt(data) > 0 ) {
              
                 $('.RozwinTeksty').show();
              
                 if ( $('#foto').val() != $('.GrafikaImg').attr('data-plik') ) {                               
                      $('.GrafikaFoto').html('<img src="../' + katalog_zdjec + '/' + $('#foto').val() + '" alt="Podgląd" />');
                      $('.GrafikaImg').attr('data-plik',$('#foto').val());
                 }    
                 
                 // czy rozwinac 
                 var rozw = $('.RozwinTeksty').attr('data-id');
                 //
                 if ( rozw == 'rozwin' ) {
                     //
                     $('#GrafikiTeksty').show();
                     $('.RozwinTeksty').hide();
                     //
                 } else {
                     //
                     $('#GrafikiTeksty').hide();
                     $('.RozwinTeksty').show();
                     //
                 }                
                 
                 // sprawdzi czy jest cookie skalowania
                 skaluj = 1;
                 if (document.cookie != "") { 
                     var cookies=document.cookie.split("; "); 
                     for ( i = 0; i < cookies.length; i++ ) { 
                         var nazwaCookie=cookies[i].split("=")[0]; 
                         var wartoscCookie=cookies[i].split("=")[1];
                         if ( nazwaCookie === 'skaluj' ) {
                             skaluj = parseInt(unescape(wartoscCookie));
                         }
                     }
                 }                 

                 var przelicznik_grafika = ($('.GrafikaTekstNaglowek').outerWidth() / parseInt(data));
                 
                 if ( przelicznik_grafika < 1 && skaluj == 1 ) {
                   
                     $('.GrafikaPodgladKontener').removeClass('GrafikaPodgladNieSkaluj').addClass('GrafikaPodgladSkaluj');
                     
                     $('.GrafikaPodgladKontener').css({ 'height' : ($('.GrafikaFoto').outerHeight() * przelicznik_grafika) + 'px' });
                   
                     $('.GrafikaOdstep').css({ 
                            'width':parseInt(data) + 'px',
                            'transform-origin':'top left',
                            '-webkit-transform': 'scale(' + przelicznik_grafika + ')',
                            '-moz-transform': 'scale(' + przelicznik_grafika + ')',
                            'transform': 'scale(' + przelicznik_grafika + ')'
                     });

                 } else {
                   
                     $('.GrafikaPodgladKontener').removeClass('GrafikaPodgladSkaluj').addClass('GrafikaPodgladNieSkaluj');
                     
                     $('.GrafikaPodgladKontener').css({ 'height' : 'auto' });

                     $('.GrafikaOdstep').css({
                            'width':'auto',
                            '-webkit-transform': 'scale(1)',
                            '-moz-transform': 'scale(1)',
                            'transform': 'scale(1)'
                     });

                 }                   
              
            } else {
              
                 $('#GrafikiTeksty').hide();
                 $('.RozwinTeksty').hide();
                 
            }
            
         });
         
    }                      
    clearTimeout(czasGrafika);
    czasGrafika = setTimeout(function(){ PodmienGrafike(); }, 1000);  
}
function PokazPrzykladTekstu() {
    //
    for ( r = 1; r < 4; r++ ) {
          linia = $('#linia_' + r).val();
          linia = linia.replace(/\n/g, "<br />");  
          //
          linia = linia.replace(/(<([^>]+)>)/ig,"");          
          //
          $('#Linia-' + r).html(linia);
          //
          // szerokosc
          $('#Linia-' + r).css({ 'display' : $('#rozmiar_linia_' + r).val() });
          //
          // rozmiar czcionki
          $('#Linia-' + r).css({ 'font-size' : $('#rozmiar_czcionki_1200_linia_' + r).val() + '%' });
          //
          // czcionka
          if ( $('#czcionka_linia_' + r).val() != '' ) {
               $('#Linia-' + r).css({ 'font-family' : $('#czcionka_linia_' + r).val() });
          }
          //
          // grubosc
          if ( $('#czcionka_grubosc_linia_' + r).val() != '' ) {
               $('#Linia-' + r).css({ 'font-weight' : $('#czcionka_grubosc_linia_' + r).val() });
          }
          //          
          // pochylenie
          if ( $('#czcionka_pochylenie_linia_' + r).val() != '' ) {
               $('#Linia-' + r).css({ 'font-syle' : $('#czcionka_pochylenie_linia_' + r).val() });
          }           
          //
          // kolor czcionki
          if ( $('#czcionka_kolor_linia_' + r).val() != '' ) {
               $('#Linia-' + r).css({ 'color' : '#' + $('#czcionka_kolor_linia_' + r).val() });
          }                              
          //
          // cien tekstu
          $('#Linia-' + r).css({ 'text-shadow' : 'none' });
          if ( $('#czcionka_cien_linia_' + r).val() == 'tak' ) {
               if ( $('#czcionka_cien_kolor_linia_' + r).val() != '' ) {
                    var cien = $('#czcionka_cien_poziomy_linia_' + r).val() + 'px ' + $('#czcionka_cien_pion_linia_' + r).val() + 'px ' + $('#czcionka_cien_rozmycie_linia_' + r).val() + 'px #' + $('#czcionka_cien_kolor_linia_' + r).val();
                    $('#Linia-' + r).css({ 'text-shadow' : cien });
               }
          }
          //
          // odstep linii
          $('#Linia-' + r).css({ 'line-height' : $('#odstep_linii_linia_' + r).val() });
          //
          //
          // kolor tla
          if ( parseInt($('#przezroczystosc_tla_linia_' + r).val()) == 100 ) {
               //
               if ( $('#kolor_tla_linia_' + r).val() != '' ) {
                    $('#Linia-' + r).css({ 'background' : '#' + $('#kolor_tla_linia_' + r).val() });
               } else {
                    $('#Linia-' + r).css({ 'background' : '' });
               }
               //
          } else {
               //
               if ( $('#kolor_tla_linia_' + r).val() != '' ) {                               
                    var color = '#' + $('#kolor_tla_linia_' + r).val();
                    var rgbaCol = 'rgba(' + parseInt(color.slice(-6,-4),16)
                        + ',' + parseInt(color.slice(-4,-2),16)
                        + ',' + parseInt(color.slice(-2),16)
                        +',' + (parseInt($('#przezroczystosc_tla_linia_' + r).val()) / 100) + ')';
                    $('#Linia-' + r).css('background-color', rgbaCol);
               } else {
                    $('#Linia-' + r).css({ 'background' : '' });
               }
               //
          }
          //
          // odstep tla
          if ( $('#odstep_linia_tla_' + r).val() != '' ) {
               $('#Linia-' + r).css({ 'padding' : $('#odstep_tla_linia_' + r).val() + 'px' });
          } 
          //
          //
          // ramka tla
          if ( parseInt($('#grubosc_ramki_linia_' + r).val()) > 0 && $('#kolor_ramki_tla_linia_' + r).val() != '' ) {
               $('#Linia-' + r).css({ 'border' : $('#grubosc_ramki_linia_' + r).val() + 'px solid #' + $('#kolor_ramki_tla_linia_' + r).val() });
          } else {
               $('#Linia-' + r).css({ 'border' : '' });
          }
          //                              
    }
    //
    // odstep linii nr 2
    if ( $('#Linia-2').html() != '' ) {
         $('#Linia-2').css({ 'margin-top' : $('#odstep_gorny_linia_2').val() + 'px' });
    }
    //
    // odstep linii nr 3
    if ( $('#Linia-3').html() != '' ) {
         $('#Linia-3').css({ 'margin-top' : $('#odstep_gorny_linia_3').val() + 'px' });
    }    
    //
    // wyrownanie tekstu
    $('#DaneTekstu').css({ 'text-align' : $('#wyrownanie_tekstu').val() });
    //
    // szerokosc tla
    $('.DaneTekstuKontener').css({ 'width' : $('#szerokosc_tla_pc').val() + '%' });
    //                        
    // kolor tla
    if ( parseInt($('#przezroczystosc_tla').val()) == 100 ) {
         //
         if ( $('#kolor_tla').val() != '' ) {
              $('#DaneTekstu').css({ 'background-color' : '#' + $('#kolor_tla').val() });
         } else {
              $('#DaneTekstu').css({ 'background-color' : '' });
         }
         //
    } else {
         //
         if ( $('#kolor_tla').val() != '' ) {                               
              var color = '#' + $('#kolor_tla').val();
              var rgbaCol = 'rgba(' + parseInt(color.slice(-6,-4),16)
                  + ',' + parseInt(color.slice(-4,-2),16)
                  + ',' + parseInt(color.slice(-2),16)
                  +',' + (parseInt($('#przezroczystosc_tla').val()) / 100) + ')';
              $('#DaneTekstu').css('background-color', rgbaCol);
         } else {
              $('#DaneTekstu').css({ 'background-color' : '' });
         }
         //
    }
    //
    // odstep tla
    if ( $('#odstep_tla').val() != '' ) {
         $('#DaneTekstu').css({ 'padding' : $('#odstep_tla').val() + 'px' });
    } 
    //
    // ramka tla
    if ( parseInt($('#grubosc_ramki').val()) > 0 && $('#kolor_ramki_tla').val() != '' ) {
         $('#DaneTekstu').css({ 'border' : $('#grubosc_ramki').val() + 'px solid #' + $('#kolor_ramki_tla').val() });
    } else {
         $('#DaneTekstu').css({ 'border' : '' });
    }
    //                      
    //
    // polozenie
    var pozycja = $('input[name="txt_polozenie_tekstu"]:checked').val();
    pozycja_tab = pozycja.split(';');
    //
    $('.DaneTekstuKontener').css('-webkit-transform', 'translate(0%,0%)');
    $('.DaneTekstuKontener').css('transform', 'translate(0%,0%)');
    $('.DaneTekstuKontener').css('left', 'auto');
    $('.DaneTekstuKontener').css('right', 'auto');
    $('.DaneTekstuKontener').css('top', 'auto');
    $('.DaneTekstuKontener').css('bottom', 'auto');
    //                        
    for ( w = 0; w < pozycja_tab.length; w++ ) {
          css_tmp = pozycja_tab[w];
          css_tb = css_tmp.split(':');
          //
          $('.DaneTekstuKontener').css(css_tb[0] , css_tb[1]);
          //
    }
    //
    // marginesy
    if ( parseInt($('#margines_gorny').val()) > 0 ) {                      
         $('.DaneTekstuKontener').css('margin-top', $('#margines_gorny').val() + 'px');
    } else {
         $('.DaneTekstuKontener').css('margin-top', '');
    }
    if ( parseInt($('#margines_dolny').val()) > 0 ) {                          
         $('.DaneTekstuKontener').css('margin-bottom', $('#margines_dolny').val() + 'px');
    } else {
         $('.DaneTekstuKontener').css('margin-bottom', '');
    }
    if ( parseInt($('#margines_lewy').val()) > 0 ) {                          
         $('.DaneTekstuKontener').css('margin-left', $('#margines_lewy').val() + 'px');
    } else {
         $('.DaneTekstuKontener').css('margin-left', '');
    }
    if ( parseInt($('#margines_prawy').val()) > 0 ) {                          
         $('.DaneTekstuKontener').css('margin-right', $('#margines_prawy').val() + 'px');
    } else {
         $('.DaneTekstuKontener').css('margin-right', '');
    }                       
    //
}
$(document).ready(function() {
    if ( $('#forma_obrazek').prop('checked') == true ) {
         czasGrafika = setTimeout(function(){ PodmienGrafike(); }, 1000);                          
    }
    //
    $('.RozwinTeksty').click(function() {
       $('#GrafikiTeksty').show();
       $(this).attr('data-id','rozwin');
       $(this).hide();
    });
    //
    $('.ZmianaPola').on("keyup",function() {
       PokazPrzykladTekstu();
    });  
    $('.ZmianaInput').on("click",function() {
       PokazPrzykladTekstu();
    });                          
    $('.ZmianaWybor').on("change",function() {
       PokazPrzykladTekstu();
    });                           
    PokazPrzykladTekstu();
})

function WyswietlAnimacje(nr) {
    //
    // usuniecie animacji
    $('#Linia-1').removeAttr('class');
    $('#Linia-2').removeAttr('class');
    $('#Linia-3').removeAttr('class');
    $('#DaneTekstu').removeAttr('class');
    //
    $('#DaneTekstu').css({ 'transition' : 'none' });
    $('#Linia-1').css({ 'transition' : 'none' });
    $('#Linia-2').css({ 'transition' : 'none' });
    $('#Linia-3').css({ 'transition' : 'none' });
    //
    if ( parseInt(nr) > 0 ) {
         //
         $('#DaneTekstu').addClass('Animacja-' + nr + '-DaneTekstu-Normal');
         $('#Linia-1').addClass('Animacja-' + nr + '-Linia-1-Normal');
         $('#Linia-2').addClass('Animacja-' + nr + '-Linia-2-Normal');
         $('#Linia-3').addClass('Animacja-' + nr + '-Linia-3-Normal');    
         //
         $('#Linia-1, #Linia-2, #Linia-3').addClass('Animacja-' + nr + '-Wspolny-Normal');    
         //
         setTimeout(function(){
             $('#DaneTekstu').css({ 'transition-duration' : '0.4s', 'transition-property' : 'all', 'transform-origin' : 'center', 'transition-delay' : '0s' });
             $('#Linia-1').css({ 'transition-duration' : '0.5s', 'transition-property' : 'all', 'transform-origin' : 'center', 'transition-delay' : '0.5s' });
             $('#Linia-2').css({ 'transition-duration' : '0.5s', 'transition-property' : 'all', 'transform-origin' : 'center', 'transition-delay' : '0.9s' });
             $('#Linia-3').css({ 'transition-duration' : '0.5s', 'transition-property' : 'all', 'transform-origin' : 'center', 'transition-delay' : '1.3s' });       
         }, 500);
          //
         setTimeout(function(){ $('#DaneTekstu').addClass('Animacja-' + nr + '-DaneTekstu-Animacja') }, 1000);
         setTimeout(function(){ $('#Linia-1').addClass('Animacja-' + nr + '-Linia-1-Animacja') }, 1000);
         setTimeout(function(){ $('#Linia-2').addClass('Animacja-' + nr + '-Linia-2-Animacja') }, 1000);
         setTimeout(function(){ $('#Linia-3').addClass('Animacja-' + nr + '-Linia-3-Animacja') }, 1000);
         //
         setTimeout(function(){ $('#Linia-1, #Linia-2, #Linia-3').addClass('Animacja-' + nr + '-Wspolny-Animacja') }, 1000); 
         //
    }
    //
}

function WyswietlEfektHover(nr) {
    //
    // usuniecie animacji
    $('.GrafikaImg').removeAttr('class').addClass('GrafikaImg');
    $('.GrafikaImg').css({ 'transition' : 'none' });
    //
    if ( parseInt(nr) > 0 ) {
         //
         $('.GrafikaImg').addClass('Efekt-' + nr);
         //
    }
    //
}

