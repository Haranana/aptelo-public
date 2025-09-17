CKEDITOR.dialog.add( 'playermp3Dialog', function( editor ) {
  return {
    title: 'Wstaw plik audio w formacie Mp3',
    minWidth: 400,
    minHeight: 75,
    contents: [
      {
        id: 'sp0',
        label: 'Basic Settings',
        elements:[
          {
            type:'text',
            id:'src0',
            label:'Wybierz plik audio Mp3',
            validate: function() {
              if(this.getValue()) {
                var g = this.getValue();
                if(g == '') return alert('Wybierz plik audio'), !1
              } else return alert('Wybierz plik audio'), !1
            }          
          },{
            type:'button',
            id:'bro',
            style:'display:inline-block;margin-top:5px;',
            filebrowser:
              {
              action:'Browse',
              target:'src0',
              url:editor.config.filebrowserAudioBrowseUrl||editor.config.filebrowserBrowseUrl,
              onSelect:function(fileUrl,data){
                this.getDialog().getContentElement('sp0','src0').setValue(fileUrl);
                return false;
                }
              },
            label:'Wybierz plik'
          }
        ]
      }
    ],
    onOk: function () {
      var c1 = this.getValueOf("sp0", "src0").trim();
      
      var urly = c1;

      var tpl = '';

          tpl += '<div class="Mp3-player-kont ready-player-mp3" style="margin:20px 0 20px 0;position:relative;width:100%">';
      
          tpl += '<audio style="width:100%" controls="true" crossorigin="crossorigin" preload="none"><source src="' + urly + '" type="audio/mpeg"></audio>';
          
          tpl += '</div>&nbsp;';

      //editor.insertHtml(tpl);    
      tpl = CKEDITOR.dom.element.createFromHtml(tpl);
      this.getParentEditor().insertElement(tpl)        

    }
  };
});
