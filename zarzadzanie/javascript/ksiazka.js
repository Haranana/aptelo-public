$(document).ready(function() {

  $('input').click(function(){
    $(this).select();
  });

  $("#addrow").click(function(){

    var wiersz = $("#licznik").val();
    var wiersz_nastepny = parseFloat(wiersz) + parseFloat(1);
    
    $(".item-row:last").after('<tr class="item-row FakturaProduktKsiazka"><td class="FakturaProdukt"><div class="UsunKontener"><a class="UsunPozycjeListy TipChmurka" href="javascript:void(0)"><b>Skasuj</b><img style="cursor:pointer" src="obrazki/kasuj.png" alt="Skasuj" /></a></div></td><td class="FakturaProdukt"></td><td class="FakturaProdukt"></td><td class="FakturaProdukt"><textarea cols="35" rows="2" name="wiersz['+wiersz+'][adresat]"></textarea></td><td class="FakturaProdukt"><textarea cols="35" rows="2" name="wiersz['+wiersz+'][adres_dostawy]"></textarea></td><td class="FakturaProdukt"><input type="text" class="kropka" name="wiersz['+wiersz+'][wartosc]" size="10" value="" style="text-align:right;" /></td><td class="FakturaProdukt"><input type="checkbox" id="ekon_'+wiersz+'" name="wiersz['+wiersz+'][rodzaj_wysylki]" value="0" /> <label class="OpisFor" for="ekon_'+wiersz+'">EKON</label> <input type="checkbox" id="prior_'+wiersz+'" name="wiersz['+wiersz+'][rodzaj_wysylki]" value="1" checked="checked" /> <label class="OpisFor" for="prior_'+wiersz+'">PRIOR</label></td><td class="FakturaProdukt"><input type="checkbox" name="wiersz['+wiersz+'][pobranie]" id="pobranie_'+wiersz+'" value="1" /><label class="OpisForPustyLabel" for="pobranie_'+wiersz+'"></label></td><td class="FakturaProdukt"><input type="checkbox" name="wiersz['+wiersz+'][wartosciowa]" id="wartosciowa_'+wiersz+'" value="1" /><label class="OpisForPustyLabel" for="wartosciowa_'+wiersz+'"></label></td></tr>');
    
    pokazChmurki();
    
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

    $('#licznik').val( wiersz_nastepny );
    if ($(".UsunPozycjeListy").length > 1) $(".UsunPozycjeListy").show();
    
  });
  
  $('body').on('click', '.UsunPozycjeListy', function() {
    var row = $(this).parents('.item-row');
    $(this).parents('.item-row').remove();
    if ($(".UsunPozycjeListy").length < 2) $(".UsunPozycjeListy").hide();
  });
  
});
