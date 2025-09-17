CKEDITOR.dialog.add( 'filmmp4Dialog', function( editor ) {
  return {
    title: 'Wstaw film w formacie MP4',
    minWidth: 400,
    minHeight: 75,
    contents: [
      {
        id: 'tab-basic',
        label: 'Basic Settings',
        elements: [
          { type: "text", 
            id: "urlMp4", 
            label: "Wprowadź adres URL do filmu mp4 (cały link z https://adres-strony.pl/.....",
            validate: function() {
              if(this.getValue()) {
                var g = this.getValue();
                if(g == '') return alert('Wprowadź adresu filmu'), !1
              } else return alert('Wprowadź adresu filmu'), !1
            }
          },
          { type: "text", 
            id: "widthMp4", 
            label: "Szerokość filmu w px", 
            style: "width:100px;", 
            default: '600',
            validate: function() {
              if(this.getValue()) {
                var g = Number(this.getValue());
                if(isNaN(g)) return alert('Podaj szerokość filmu'), !1
              } else return alert('Podaj szerokość filmu'), !1
            }
          },
          { type: "text", 
            id: "heihgtMp4", 
            label: "Wysokość filmu w px", 
            style: "width:100px", 
            default: '200',
            validate: function() {
              if(this.getValue()) {
                var g = Number(this.getValue());
                if(isNaN(g)) return alert('Podaj wysokość filmu'), !1
              } else return alert('Podaj wysokość filmu'), !1
            }            
          },
          { type: "checkbox", id: "centrujMp4", label: "Czy wyśrodkować film na stronie ?", default: 0 },
          { type: "checkbox", id: "nawigacjaMp4", label: "Czy wyświetlać przyciski kontrolne (play / stop / dźwięk) ?", default: 1 },
          { type: "checkbox", id: "dzwiekMp4", label: "Czy wyciszyć dźwięk ?", default: 1 },
          { type: "checkbox", id: "autostartMp4", label: "Czy uruchomić film od razu po wyświetlaniu strony ?", default: 1 },
          { type: "checkbox", id: "loopMp4", label: "Czy film wyświetlać w zapętleniu ?", default: 1 },
        ]
      }
    ],
    onOk: function () {
      var c1 = this.getValueOf("tab-basic", "urlMp4").trim();
      var c2 = this.getValueOf("tab-basic", "widthMp4").trim();
      var c3 = this.getValueOf("tab-basic", "heihgtMp4").trim();
      var c4 = this.getContentElement("tab-basic", "nawigacjaMp4").getValue();
      var c5 = this.getContentElement("tab-basic", "autostartMp4").getValue();
      var c6 = this.getContentElement("tab-basic", "loopMp4").getValue();
      var c7 = this.getContentElement("tab-basic", "dzwiekMp4").getValue();
      var c8 = this.getContentElement("tab-basic", "centrujMp4").getValue();
      
      var urly = c1;
      
      var max_width = '';
      if ( c2 > 0 ) {
           max_width = ';max-width:' + c2 + 'px';
      }
      var max_height = '';
      if ( c3 > 0 ) {
           max_height = ';max-height:' + c3 + 'px';
      }
      var nawigacja = '';
      if ( c4 == true ) {
           nawigacja = ' controls';
      }
      var autostart = '';
      if ( c5 == true ) {
           autostart = ' autoplay';
      }
      var loop = ' loop="false"';
      if ( c6 == true ) {
           loop = ' loop="true"';
      }
      var dzwiek = '';
      if ( c7 == true ) {
           dzwiek = ' muted';
      }
      
      var tpl = '';
      
          if ( c8 == true ) {
               tpl += '<div class="mp4-edytor-center" style="display:flex;justify-content:center">';
          }
      
          tpl += '<div class="mp4-edytor-kont" style="text-align:center;margin:20px 0 20px 0;position:relative' + max_width + max_height + '">';
      
          tpl += '<video' + nawigacja + autostart + loop + dzwiek + ' style="width:100%;height:auto"><source src="' + urly + '" type="video/mp4"></video>';
        
          tpl += '</div>';
          
          if ( c8 == true ) {
               tpl += '</div">';
          }          

      //editor.insertHtml(tpl);    
      tpl = CKEDITOR.dom.element.createFromHtml(tpl);
      this.getParentEditor().insertElement(tpl)        

    }
  };
});
