$(document).ready(function() {

  $('input').click(function(){
    $(this).select();
  });
  
  $('body').on('click', '.UsunPozycjeListy', function() {
    var row = $(this).parents('.item-row');
    $(this).parents('.item-row').remove();
    if ($(".UsunPozycjeListy").length < 2) $(".UsunPozycjeListy").hide();
  });
  
});
