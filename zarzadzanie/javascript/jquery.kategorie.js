(function($) {
 
  $.fn.catSelect = function(options){
    var defaults = {
      urlSub: '',
      urlParent: '',
      size: 10,
      type: 'get',
      container: $(this),
      input: null,
      start: 0
    };
 
    $.fn.catSelect.settings = $.extend(defaults, options);
    $.fn.catSelect.reset();
 
    $.fn.catSelect.settings.input.keyup(function(){
      $.fn.catSelect.selectById(this.value);
    });
 
    if($.fn.catSelect.settings.start != 0){
      $.fn.catSelect.selectById($.fn.catSelect.settings.start);
    } else {
      $.fn.catSelect.onChange();
    }
  }
 
  $.fn.catSelect.cache = [];
  $.fn.catSelect.pCache = [];
  $.fn.catSelect.obj = null;
 
  $.fn.catSelect.reset = function(){
    $.fn.catSelect.settings.container.html('');
    $.fn.catSelect.obj = $(document.createElement('select'));
  }
 
  $.fn.catSelect.onChange = function(){
    var id = $.fn.catSelect.obj.val() || 0;
    if($.fn.catSelect.cache[id]){
      $.fn.catSelect.onSuccess($.fn.catSelect.cache[id]);
    } else {
      $.fn.catSelect.get(id);      
    }
  }
 
  $.fn.catSelect.selectById = function(id){
    $.fn.catSelect.reset();
    if($.fn.catSelect.pCache[id]){
      $.fn.catSelect.selectByIdOnSuccess($.fn.catSelect.pCache[id]);
    } else {
      $.ajax({
        url: $.fn.catSelect.settings.urlParent,
        type: $.fn.catSelect.settings.type,
        data: 'id='+id,
        dataType: 'json',
        success: function(data){
          $.fn.catSelect.selectByIdOnSuccess(data);
          $.fn.catSelect.pCache[id] = data;
        }
      }); 
    }
  }
 
  $.fn.catSelect.selectByIdOnSuccess = function(data){
    for(i in data){
      $.fn.catSelect.cache[data[i].id] = data[i].data;
    }
    $.fn.catSelect.onChange()
    for(var i=1,s=null;i<data.length;i++){
      s = $.fn.catSelect.settings.container.children();
      $(s[s.length-1]).val(data[i].id).change();
    }
  }
 
  $.fn.catSelect.onSuccess = function(data){
    $.fn.catSelect.obj.nextAll('.catSelect').remove();
 
    if(data.length > 0){
      var select = $(document.createElement('select'))
                      .attr('size', $.fn.catSelect.settings.size)
                      .addClass('catSelect')
                      .change(function(){
                        $.fn.catSelect.obj = $(this);
                        $.fn.catSelect.onChange();
                      });
 
      for(i in data){
        select.append('<option value="'+data[i].id+'">'+data[i].name+'</option>')
      }
 
      $.fn.catSelect.settings.container.append(select);
      select.val(0);
	  //$('.catSelect').wrap('<div style="padding:5px; float:left; overflow:hidden;" />');
    } else {
      $.fn.catSelect.settings.input.val($.fn.catSelect.obj.val());
    }
 
    if($.fn.catSelect.settings.columns != 0){
      var cols = $.fn.catSelect.settings.container.children('select.catSelect').show();
      if(cols.length > $.fn.catSelect.settings.columns){
        for(var i=0, c=cols.length-$.fn.catSelect.settings.columns; i<c ; i++){
          $(cols[i]).hide();
        }
      }
    }
  }
 
  $.fn.catSelect.get = function(id){
    $.ajax({
      url: $.fn.catSelect.settings.urlSub,
      type: $.fn.catSelect.settings.type,
      data: 'cat_id='+id,
      dataType: 'json',
      success: function(data){
        $.fn.catSelect.onSuccess(data);
        $.fn.catSelect.cache[id] = data;
      }
    });
  }
 
 
})(jQuery)