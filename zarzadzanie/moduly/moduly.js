// zmienia pola zaleznie od rodzaju wybranej metody naliczania
function zmien_pola() {
  $("#WYSYLKA_RODZAJ_OPLATY").bind("change", function () {
    if ($(this).val() == "1") {
      $("#kosztyCena").slideUp();
      $("#kosztyWaga").slideUp();
      $("#kosztySztuki").slideUp();
      $("#kosztyGabaryt").slideUp();
      $("#kosztyStale").slideDown();
    }
    else if ($(this).val() == "2") {
      $("#kosztyCena").slideUp();
      $("#kosztySztuki").slideUp();
      $("#kosztyStale").slideUp();
      $("#kosztyGabaryt").slideUp();
      $("#kosztyWaga").slideDown();
    }
    else if($(this).val() == "3") {
      $("#kosztyWaga").slideUp();
      $("#kosztySztuki").slideUp();
      $("#kosztyStale").slideUp();
      $("#kosztyGabaryt").slideUp();
      $("#kosztyCena").slideDown();
    }
    else if($(this).val() == "4") {
      $("#kosztyCena").slideUp();
      $("#kosztyWaga").slideUp();
      $("#kosztyStale").slideUp();
      $("#kosztyGabaryt").slideUp();
      $("#kosztySztuki").slideDown();
    }
    else if($(this).val() == "5") {
      $("#kosztyCena").slideUp();
      $("#kosztyWaga").slideUp();
      $("#kosztyStale").slideUp();
      $("#kosztySztuki").slideUp();
      $("#kosztyGabaryt").slideDown();
    }
  });
}   

// dodaje pola na dodatkowy przedzial kosztow
function dodaj_pozycje(idParent, idChild, typ, prefix, sufix) {

  var id = $('#'+idParent).children().length;
  var id1 = $('#'+idParent).children().length-1;
  var pole = '#'+idChild+''+id1+'';
  var klasa = 'kropka';
  if (idParent == 'kosztyWaga') {
      klasa = 'Waga';
  }
  var tresc = '<div class="odlegloscRwdTab" style="padding-bottom:6px" id="'+idChild+''+id+'">'+prefix+' &nbsp; <input class="'+klasa+'" type="text" size="10" name="parametry_'+idChild+'_przedzial[]" value="0" /> '+typ+' &nbsp; <input class="kropka" type="text" name="parametry_'+idChild+'_wartosc[]" value="0" /> '+sufix+' </div>';

  $(tresc).insertAfter(pole);
  $('.usun').slideDown();
  
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
    
} 

// usuwa pola na dodatkowy przedzial kosztow
function usun_pozycje(idParent, idChild) {
  var id = $('#'+idParent).children().length-1;
  var pole = '#'+idChild+''+id+'';
  if (id > 1)
  {
    $(pole).remove();
  }
}

// dodaje pola na kolejny punkt odbioru osobistego
function dodaj_punkt(idParent, idChild) {

     var NumerPola = $('#'+idParent).children().length;
     NumerPola = parseFloat(NumerPola) + 1;

     var pole = '#'+idChild+'_'+$('#'+idParent).children().length+'';

     var tresc = '<p id="WYSYLKA_ODBIOR_OSOBISTY_PUNKT_'+NumerPola+'"><label for="PARAMETRY[WYSYLKA_ODBIOR_OSOBISTY_PUNKT_'+NumerPola+']">Punkt odbioru:</label><textarea cols="80" rows="3" name="PARAMETRY[WYSYLKA_ODBIOR_OSOBISTY_PUNKT_'+NumerPola+']" id="PARAMETRY[WYSYLKA_ODBIOR_OSOBISTY_PUNKT_'+NumerPola+']"></textarea></p>';

     $(tresc).insertAfter(pole);

} 

// usuwa pola na dodatkowy punkt odbioru osobistego
function usun_punkt(idParent, idChild) {

  var id = $('#'+idParent).children().length;
  var pole = '#'+idChild+'_'+id+'';
  if (id > 1)
  {
    $(pole).remove();
  }
}

function updateKeySkrypt() {
  var key=$("#skrypt").val();
  key=key.replace(" ","_");
  $("#skrypt").val(key);
}

function updateKeyKlasa() {
  var key=$("#klasa").val();
  key=key.replace(" ","_");
  $("#klasa").val(key);
}

