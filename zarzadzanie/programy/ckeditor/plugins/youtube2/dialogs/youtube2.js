CKEDITOR.dialog.add( 'youtube2Dialog', function( editor ) {
  return {
    title: 'Wstaw film z serwisu YouTube',
    minWidth: 400,
    minHeight: 75,
    contents: [
      {
        id: 'tab-basic',
        label: 'Basic Settings',
        elements: [
          { type: "text", 
            id: "urlYt", 
            label: "Wprowadź adres URL z serwisu YouTube (cały link z https://youtube.com.....",
            validate: function() {
              if(this.isEnabled())
                if(this.getValue()) {
                  if(h = ytVidId2(this.getValue()), 0 === this.getValue().length || !1 === h) return alert('Wprowadź poprawny adresu filmu'), !1
                } else return alert('Wprowadź poprawny adresu filmu'), !1
            }
          },
          { type: "text", 
            id: "widthYt", 
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
            id: "heihgtYt", 
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
          { type: "checkbox", id: "rwdYt", label: "Wygląd responsywny (pomiń szerokość i wysokość - film dopasuje się do ekranu)", default: 1 },
          { type: "checkbox", id: "imgYt", label: "Tylko obrazek filmu i link do YouTube", default: 0 }   
        ]
      }
    ],
    onOk: function () {
      var c1 = this.getValueOf("tab-basic", "urlYt").trim();
      var c2 = this.getValueOf("tab-basic", "widthYt").trim();
      var c3 = this.getValueOf("tab-basic", "heihgtYt").trim();
      var c4 = this.getContentElement("tab-basic", "rwdYt").getValue();
      var c5 = this.getContentElement("tab-basic", "imgYt").getValue();
      
      var urly = getId2(c1);
      
      var tpl = '<div class="youtube-embed-wrapper-center" style="text-align:center;margin:20px 0 20px 0;position:relative"><iframe allowfullscreen="" frameborder="0" height="' + c3 + '" width="' + c2 + '" src="https://www.youtube.com/embed/' + urly + '"></iframe></div>';
      
      if ( c4 == true ) {          
           tpl = '<div class="youtube-embed-wrapper" style="margin:20px 0 20px 0;position:relative;padding-bottom:56.25%;height:0;overflow:hidden"><iframe allowfullscreen="" frameborder="0" height="' + c3 + '" width="100%" style="position:absolute;top:0;left:0;width:100%;height:100%" src="https://www.youtube.com/embed/' + urly + '"></iframe></div>';
      }
      
      if ( c5 == true ) {
           tpl = '<div class="youtube-embed-wrapper-center" style="text-align:center;margin:20px 0 20px 0;position:relative"><a href="' + c1 + '"><img height="' + c3 + '" width="' + c2 + '" src="https://img.youtube.com/vi/' + urly + '/sddefault.jpg" /></a></div>';
           
           if ( c4 == true ) {   
                tpl = '<div class="youtube-embed-wrapper" style="text-align:center;margin:20px 0 20px 0;position:relative"><a style="display:block;width:100%" href="' + c1 + '"><img style="width:100%;height:auto" height="' + c3 + '" width="' + c2 + '" src="https://img.youtube.com/vi/' + urly + '/sddefault.jpg" /></a></div>';
           }
      }
      
      //editor.insertHtml(tpl);    
      tpl = CKEDITOR.dom.element.createFromHtml(tpl);
      this.getParentEditor().insertElement(tpl)      

    }
  };
});

function ytVidId2(a) {
  return a.match(/(youtu.*be.*)\/(watch\?v=|embed\/|v|shorts|)(.*?((?=[&#?])|$))/) ? RegExp.$1 : !1
}

function getId2(url) {
  const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|shorts\/|watch\?v=|&v=)([^#&?]*).*/;
  const match = url.match(regExp);

  return (match && match[2].length === 11)
    ? match[2]
    : null;
}