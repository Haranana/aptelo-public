// ## wczytywanie ajax

function zmiana_grafika(akcja) {
    //
    if ( akcja == 'sharpen' ) {
         akcja = 'sharpen-' + $('.suwakWartosc-Wyostrzenie b').text();
    }
    if ( akcja == 'brighten' ) {
         akcja = 'brighten-' + $('.suwakWartosc-Rozjasnienie b').text();
    }    
    if ( akcja == 'darken' ) {
         akcja = 'darken-' + $('.suwakWartosc-Przyciemnienie b').text();
    }      
    //
    $('#ekr_preloader').css('display','block');
    $.post("narzedzia/przegladarka_obrazek.php",
        { zmiana: 'tak', 
          zrodlo: $('#plik_zrodlowy').val(), 
          foto: $('#zrodloImgKont img').attr('src'), 
          topX: topX, 
          topY: topY, 
          bottomX: bottomX, 
          bottomY: bottomY,
          szerokosc: $('#szerokosc_img').val(),
          wysokosc: $('#wysokosc_img').val(),
          grubosc_ramki: $('#grubosc_ramki').val(),
          kolor_ramki: $('#kolor_ramki').val(),
          margines_szerokosc: $('#margines_szerokosc').val(),
          kolor_tla: $('#kolor_tla').val(),          
          foto_znakwodny: $('#foto_znakwodny').val(),
          polozenie_znakwodny: $('#polozenie_znakwodny').val(),
          przezroczystosc_znakwodny: $('#przezroczystosc_znakwodny').val(),
          x_znakwodny: $('#x_znakwodny').val(),
          y_znakwodny: $('#y_znakwodny').val(),
          szerokosc_znakwodny: $('#szerokosc_znakwodny').val(),
          format: $('input[name="format"]:checked').val(),
          kompresja: $('#kompresja option:selected').val(),
          nazwa_koncowa: $('#nazwa_koncowa').val(),
          katalog_sciezka: $('#katalog_sciezka').val(),
          akcja: akcja 
        },
        function(data) { 
          $('#ekr_preloader').css('display','none');
          if ( data != 'powrot' ) {
               //
               $('#zrodloImgKont').html(data);
               $('#plikZrodloImg').on('load', function() {
                  // resetowanie zaznaczenia
                  resetuj_wycinanie();
                  funkcja_zaznaczenia();
                  resetuj_wytnij();
                  rozmiar_grafiki(); 
                  //
                  $('.OknoPopupEdytora').hide();  
                  //
               });
          } else {
               document.location = 'narzedzia/przegladarka.php' + $('#powrot').val();
          }
        }           
    );  
}

// ## wycinanie tekstu

topX = 0;
topY = 0;
bottomX = 0;
bottomY = 0;
ing = false;

// funkcja do resetowania zaznaczenia
function resetuj_wycinanie() {
    $('#obszarZaznacznie').css({
        width: 0,
        height: 0,
        display: 'none'
    });
    $('#rozmiarObrazka').text('');
    $('#cursorPolozenie').text('');
    $('#cursorAkcja').text('');
}

function funkcja_zaznaczenia() {
  
    var startX, startY, jestKlikniecieWytnij = false;
    
    $('#akcjeWytnij').show(); 
    $('#cursorPolozenie').text('0 x 0 px');
    $('#cursorAkcja').text('Wybierz punkt początkowy i zaznacz myszką obszar wycięcia');

    $('#akcjeWytnij').hover(function() {
        $('#cursorPolozenie').show();
        if ( ing == false ) {
             $('#cursorAkcja').show();
        }
    }, function() {
        $('#cursorPolozenie').hide();
        $('#cursorAkcja').hide();
    });

    // rozpoczęcie zaznaczania na `mousedown`
    $('#akcjeWytnij').on('mousedown', function(e) {
        e.preventDefault();
        jestKlikniecieWytnij = true;

        startX = e.offsetX;
        startY = e.offsetY;

        $('#obszarZaznacznie').css({
            left: startX + 'px',
            top: startY + 'px',
            width: 0,
            height: 0,
            display: 'block'
        });

        $('#rozmiarObrazka').text('');
    });

    // aktualizacja zaznaczenia na `mousemove`
    $('#akcjeWytnij').on('mousemove', function(e) {
        if (jestKlikniecieWytnij) {
            var endX = e.offsetX;
            var endY = e.offsetY;

            var width = endX - startX;
            var height = endY - startY;

            $('#obszarZaznacznie').css({
                width: Math.abs(width) + 'px',
                height: Math.abs(height) + 'px',
                left: (width < 0 ? endX : startX) + 'px',
                top: (height < 0 ? endY : startY) + 'px'
            });

            // Przeliczenie na oryginalny rozmiar obrazka
            var skalaRozmiaru = przelicz_rozmiar();
            var realWidth = Math.abs(width) / skalaRozmiaru;
            var realHeight = Math.abs(height) / skalaRozmiaru;

            $('#rozmiarObrazka').text(Math.round(realWidth) + 'px x ' + Math.round(realHeight) + 'px');
        }
    });

    // koniec zaznaczania na `mouseup`
    $(document).on('mouseup', function(e) {
        if (jestKlikniecieWytnij) {
            jestKlikniecieWytnij = false;

            // Ustalenie końcowego punktu
            var endX = e.offsetX;
            var endY = e.offsetY;

            var skalaRozmiaru = przelicz_rozmiar();

            // Przelicz współrzędne
            var left = Math.min(startX, endX) / skalaRozmiaru;
            var top = Math.min(startY, endY) / skalaRozmiaru;
            var right = Math.max(startX, endX) / skalaRozmiaru;
            var bottom = Math.max(startY, endY) / skalaRozmiaru;

            // dane wynikowe
            topX = parseInt(left);
            topY = parseInt(top);
            bottomX = parseInt(right);
            bottomY = parseInt(bottom);
            
        }
    });
        
    // obsługuje ruch myszy, aby wyświetlić współrzędne kursora
    $('#akcjeWytnij').on('mousemove', function(e) {
        var skalaRozmiaru = przelicz_rozmiar();
        $('#cursorPolozenie').text(Math.round(e.offsetX / skalaRozmiaru) + ' x ' + Math.round(e.offsetY / skalaRozmiaru) + ' px');
    }); 
    
}

// oblicz skalowanie obrazka
function przelicz_rozmiar() {
    var plikZrodloImg = $('#plikZrodloImg');
    var oryginalnaSzerokosc = plikZrodloImg[0].naturalWidth;; // oryginalna szerokość obrazka
    var wyswietlanaSzerokosc = $('#plikZrodloImg').width(); // wyświetlana szerokość obrazka
    return wyswietlanaSzerokosc / oryginalnaSzerokosc;
}

// sprawdzenie, czy punkt jest wewnątrz obrazu
function sprawdz_obszar_img(x, y) {
    return x >= 0 && y >= 0 && x <= $('#plikZrodloImg').width() && y <= $('#plikZrodloImg').height();
}

// wyjscie z trybu wycinania
function resetuj_wytnij() {
    //
    $('.NieWytnij').show();
    $('.TylkoWytnij').hide();
    $('#fotoKontener').css({'cursor':'auto'});   
    //
    $('#akcjeWytnij, #cursorPolozenie, #cursorAkcja').hide();      
    //
}

// suwak
function funkcja_suwaka(id) {

    var minValue = 0;
    var maxValue = 100;
    
    var jestKlikniecieSuwak = false;

    $('.suwakCursor-' + id).on('mousedown', function() {
        jestKlikniecieSuwak = true;
    });
    
    $(document).on('mousemove', function(e) {
        if (jestKlikniecieSuwak) {
            var sliderOffset = $('.suwakEdytor-' + id).offset();
            var sliderWidth = $('.suwakEdytor-' + id).width();
            var mouseX = e.pageX - sliderOffset.left;
            
            // Zapobiegaj przekraczaniu wartości minimalnych i maksymalnych
            if (mouseX < 0) {
                mouseX = 0;
            } else if (mouseX > sliderWidth) {
                mouseX = sliderWidth;
            }
            
            // Ustawienie pozycji uchwytu
            $('.suwakCursor-' + id).css('left', mouseX - $('.suwakCursor-' + id).width() / 2 + 'px');
            
            // Obliczenie wartości z zakresu
            var value = Math.round((mouseX / sliderWidth) * (maxValue - minValue) + minValue);
            
            // Wyświetlanie wartości
            $('.suwakWartosc-'  + id + ' b').text(value);

        }
    });
    
    $(document).on('mouseup', function() {
        jestKlikniecieSuwak = false;
    });    
    //
}

// rozmiar grafiki
function rozmiar_grafiki() {
    //
    var plikZrodloImg = $('#plikZrodloImg');
    var szerokosc = plikZrodloImg[0].naturalWidth;; // oryginalna szerokość obrazka
    var wysokosc = plikZrodloImg[0].naturalHeight;; // oryginalna wysokość obrazka  
    //
    $('.ImgSzerokosc').text(szerokosc + 'px');
    $('.ImgWysokosc').text(wysokosc + 'px');
    //
    $('.InputImgSzerokosc').val(szerokosc);
    $('.InputImgSzerokosc').attr('max',szerokosc);
    //
    $('.InputImgWysokosc').val(wysokosc);
    $('.InputImgWysokosc').attr('max',wysokosc);
    //
    $('.InputImgSzerokoscOrg').val(szerokosc);
    $('.InputImgWysokoscOrg').val(wysokosc);
    //
}
    
$(document).ready(function() {
  
    $('.NawigacjaEdytoraTlo > div > a').click(function() {
        $('.OknoPopupEdytora').hide();        
        $(this).parent().find('.OknoPopupEdytora').show();
    });
    
    $('.ZamknijPop').click(function() {
        $('.OknoPopupEdytora').hide();  
    });

    // funkcja do obliczania nowego rozmiaru z zachowaniem proporcji
    function ustal_proporcje(wartosc) {

        var originalWidth = parseInt($('.InputImgSzerokoscOrg').val());
        var originalHeight = parseInt($('.InputImgWysokoscOrg').val());

        var width = parseFloat($('.InputImgSzerokosc').val());
        var height = parseFloat($('.InputImgWysokosc').val());

        if (wartosc === 'width') {
            // oblicz wysokość na podstawie szerokości
            height = (width / originalWidth) * originalHeight;
            $('.InputImgWysokosc').val(Math.round(height));
        } else if (wartosc === 'height') {
            // oblicz szerokość na podstawie wysokości
            width = (height / originalHeight) * originalWidth;
            $('.InputImgSzerokosc').val(Math.round(width));
        }
    }

    // nasłuchuj zmian w szerokości
    $('.InputImgSzerokosc').on('input', function() {
        if ( $('#proporcje').prop('checked') == true ) {
             ustal_proporcje('width'); 
        }
    });

    // nasłuchuj zmian w wysokości
    $('.InputImgWysokosc').on('input', function() {
        if ( $('#proporcje').prop('checked') == true ) {
             ustal_proporcje('height');
        }
    });

    // nasłuchuj zmian w szerokości
    $('#proporcje').on('input', function() {
        if ( $('#proporcje').prop('checked') == true ) {
             ustal_proporcje('width'); 
        }
    });
    
    rozmiar_grafiki()
  
    $('#ikonaWytnij').click(function() {  
         //
         $('.NieWytnij').hide();
         $('.TylkoWytnij').show();
         $('#fotoKontener').css({'cursor':'crosshair'});
         funkcja_zaznaczenia();
         //
    });
    
    $('#zamknijWytnij').click(function() {  
         //
         resetuj_wytnij(); 
         //
    });
    
    funkcja_suwaka('Wyostrzenie');
    funkcja_suwaka('Rozjasnienie');
    funkcja_suwaka('Przyciemnienie');
    
    $('.SuwakWartosci').hover(function() {
        //
        $('.suwakCursor').css('left', 0);
        $('.suwakWartosc b').text('0');    
        //
    });
        
    
});