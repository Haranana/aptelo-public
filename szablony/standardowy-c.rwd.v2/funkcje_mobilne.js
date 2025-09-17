function PreloaderKreator(id, przeskok) {
    //
    var element = '#ModulLadowanie-' + id;
    //
    if ( $(element).offset().top < $(window).height() + $(window).scrollTop() + przeskok ) {
         //
         var czy_preloader = $(element).attr('data-preload');
         //
         if ( czy_preloader == 'false' ) {
              //
              $(element).attr('data-preload','true');
              //
              $.post("inne/kreator_modul.php", { id: id, pobierz: 1 }, function(data) { 
                 // 
                 $(element).html(data);
                 //
                 setTimeout(function() { 
                     //
                     if ( $(element + ' .slick-track').length ) {
                          //
                          $(element + ' .slick-track .slick-slide').css({ 'height' : 'auto' });
                          //
                          $(element + ' .slick-track').each(function() {
                              //
                              var max_wysokosc = 0;
                              //
                              $(this).find('.slick-slide').each(function() {
                                  //
                                  if ( $(this).outerHeight() > max_wysokosc ) {
                                       max_wysokosc = $(this).outerHeight();
                                  }
                                  //
                              });
                              //
                               $(this).find('.slick-slide').css({ 'height' : max_wysokosc }); 
                              //
                          });
                     }
                     //
                 }, 400);
                 //
              });
              // 
         }
         //
    }
    //
}

function AnimujTekst( grupa ) {
  
    $('.' + grupa + ' .slick-slide').each(function() {

        var id_bannera = $(this).find('.GrafikiAnimacjaTekstu').attr('data-id');
        var animacja = $(this).find('.GrafikiAnimacjaTekstu').attr('data-animacja');
        
        if ( parseInt(animacja) > 0 ) {
          
            if ( id_bannera != undefined ) {
              
                var id_kontenera = '.GrafikaDaneTekstu-' + id_bannera;
                var id_linia_1 = '.Linia-1-' + id_bannera;
                var id_linia_2 = '.Linia-2-' + id_bannera;
                var id_linia_3 = '.Linia-3-' + id_bannera;

                $(id_kontenera).css({ 'transition' : 'none' }).removeClass('Animacja-' + animacja + '-Wspolny-Normal').removeClass('Animacja-' + animacja + '-Wspolny-Animacja').removeClass('Animacja-' + animacja + '-DaneTekstu-Normal').removeClass('Animacja-' + animacja + '-DaneTekstu-Animacja');
                $(id_linia_1).css({ 'transition' : 'none' }).removeClass('Animacja-' + animacja + '-Wspolny-Normal').removeClass('Animacja-' + animacja + '-Wspolny-Animacja').removeClass('Animacja-' + animacja + '-Linia-1-Normal').removeClass('Animacja-' + animacja + '-Linia-1-Animacja');
                $(id_linia_2).css({ 'transition' : 'none' }).removeClass('Animacja-' + animacja + '-Wspolny-Normal').removeClass('Animacja-' + animacja + '-Wspolny-Animacja').removeClass('Animacja-' + animacja + '-Linia-2-Normal').removeClass('Animacja-' + animacja + '-Linia-2-Animacja');
                $(id_linia_3).css({ 'transition' : 'none' }).removeClass('Animacja-' + animacja + '-Wspolny-Normal').removeClass('Animacja-' + animacja + '-Wspolny-Animacja').removeClass('Animacja-' + animacja + '-Linia-3-Normal').removeClass('Animacja-' + animacja + '-Linia-3-Animacja');

                $(id_kontenera).addClass('Animacja-' + animacja + '-DaneTekstu-Normal');
                $(id_linia_1).addClass('Animacja-' + animacja + '-Linia-1-Normal');
                $(id_linia_2).addClass('Animacja-' + animacja + '-Linia-2-Normal');
                $(id_linia_3).addClass('Animacja-' + animacja + '-Linia-3-Normal');

                $(id_kontenera).addClass('Animacja-' + animacja + '-Wspolny-Normal');
                $(id_linia_1).addClass('Animacja-' + animacja + '-Wspolny-Normal');
                $(id_linia_2).addClass('Animacja-' + animacja + '-Wspolny-Normal');
                $(id_linia_3).addClass('Animacja-' + animacja + '-Wspolny-Normal');

                setTimeout(function(){
                   $(id_kontenera).css({ 'transition-duration' : '0.4s', 'transition-property' : 'all', 'transform-origin' : 'center' });
                   $(id_linia_1).css({ 'transition-duration' : '0.4s', 'transition-property' : 'all', 'transform-origin' : 'center' });
                   $(id_linia_2).css({ 'transition-duration' : '0.4s', 'transition-property' : 'all', 'transform-origin' : 'center' });
                   $(id_linia_3).css({ 'transition-duration' : '0.4s', 'transition-property' : 'all', 'transform-origin' : 'center' });
                   
                   $(id_kontenera).css({ 'transition-delay' : '0s' });
                   $(id_linia_1).css({ 'transition-delay' : '0.5s' });
                   $(id_linia_2).css({ 'transition-delay' : '0.9s' });
                   $(id_linia_3).css({ 'transition-delay' : '1.3s' });       
                }, 500);

                setTimeout(function(){ $(id_kontenera).addClass('Animacja-' + animacja + '-DaneTekstu-Animacja') }, 500);
                setTimeout(function(){ $(id_linia_1).addClass('Animacja-' + animacja + '-Linia-1-Animacja') }, 500);
                setTimeout(function(){ $(id_linia_2).addClass('Animacja-' + animacja + '-Linia-2-Animacja') }, 500);
                setTimeout(function(){ $(id_linia_3).addClass('Animacja-' + animacja + '-Linia-3-Animacja') }, 500);

                setTimeout(function(){ $(id_kontenera).addClass('Animacja-' + animacja + '-Wspolny-Animacja') }, 500); 
                setTimeout(function(){ $(id_linia_1).addClass('Animacja-' + animacja + '-Wspolny-Animacja') }, 500); 
                setTimeout(function(){ $(id_linia_2).addClass('Animacja-' + animacja + '-Wspolny-Animacja') }, 500); 
                setTimeout(function(){ $(id_linia_3).addClass('Animacja-' + animacja + '-Wspolny-Animacja') }, 500); 
                
            }
            
        }
    
    });
    
}

var szerokoscEkranu = 0;

$(window).load(function() {
    //
    szerokoscEkranu = $(window).width();
    //
});

$(document).ready(function() {
  
    $('.PozycjaMenuPreloader').hover(function() {
      
        var nr_menu = parseInt($(this).attr('data-id'));
        $.post('../inne/do_wyglad_menu.php', { id: nr_menu }, function(data) { $('#OknoMenu-' + nr_menu).html(data); });    
        
    });
    
    $('.PozycjaMenuPreloader').on("keydown", function(event) {
      
        if (event.key === " ") {
            event.preventDefault(); 
            $(this).trigger("mouseenter");
        }
        
    });
  
    $(window).on("resize", function() {
      
        if (szerokoscEkranu != $(window).width()) {
            //
            $(".PozycjaMenuPreloader").blur();
            //
        }      
        
    }); 
  
    $('.GlowneGorneMenu > li').hover(function() {

       if ( $(this).find('ul').length > 0 ) {

            var szerokosc = $(this).find('ul:first').outerWidth();
            var $elem = $(this).find('ul:first');
            var lewa = $elem.position().left;
            if (lewa + szerokosc > $('.GorneMenuKontener .Strona').outerWidth()) {
                $(this).find('ul:first').css({ 'left' : 'unset', 'right' : 0 });
            }

       }

    });
    
    // plus / minus na karcie produktu
    if ( $("#PrzyciskKupowania input[name=ilosc]").length ) {
      
         var input = $("#PrzyciskKupowania input[name=ilosc]");
         var min = input.attr("min");
         var przyrost = parseFloat(input.attr("step"));

         if ( przyrost == 0 ) { 
              //
              przyrost = 1; 
              //
         }
        
         if ( min < przyrost ) { 
              //
              min = przyrost; 
              //
        }

        $('.minus').click(function () {
            //
            var licznikIlosci = parseFloat(input.val()) - przyrost;
            licznikIlosci = licznikIlosci < min ? min : licznikIlosci;
            input.val(licznikIlosci);
            input.change();
            return false;
            //
        });

        $('.plus').click(function () {
            //
            var licznikIlosci = parseFloat(input.val()) + przyrost;
            input.val(licznikIlosci);
            input.change();
            return false;
            //
        });
        
    }

    // po zmianie wielkosci ekranu wywola ponownie funkcje
    $(window).resize(function() {

        if (szerokoscEkranu != $(window).width()) {
            //
            szerokoscEkranu = $(window).width();
            //
        }

    });  
    // 

});
