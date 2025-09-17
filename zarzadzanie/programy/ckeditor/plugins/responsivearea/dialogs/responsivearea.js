CKEDITOR.dialog.add( 'responsiveareaDialog', function( editor ) {
  return {
    title: 'Responsywne kolumny',
    minWidth: 300,
    minHeight: 200,
    contents: [
      {
        id: 'tab-basic',
        label: 'Basic Settings',
        elements: [
          {
            type: 'radio',
            id: 'layout',
            className : 'ResponsiveAreaCss',
            label: 'Wybierz żądany układ<br />',
            items: [
              ['1 kolumna<small>100%</small><img src="' + editor.responsivearea_path + '/images/1_100.png" />', '1_100'],
              ['2 kolumny<small>po 50%</small><img src="' + editor.responsivearea_path + '/images/2_50_50.png" />', '2_50_50'],
              ['2 kolumny<small>75% 25%</small><img src="' + editor.responsivearea_path + '/images/2_75_25.png" />', '2_75_25'],
              ['2 kolumny<small>25% 75%</small><img src="' + editor.responsivearea_path + '/images/2_25_75.png" />', '2_25_75'],
              ['2 kolumny<small>33% 66%</small><img src="' + editor.responsivearea_path + '/images/2_33_66.png" />', '2_33_66'],
              ['2 kolumny<small>66% 33%</small><img src="' + editor.responsivearea_path + '/images/2_66_33.png" />', '2_66_33'],
              ['3 kolumny<small>po 33%</small><img src="' + editor.responsivearea_path + '/images/3_33_34_33.png" />', '3_33_34_33'],
              ['3 kolumny<small>25% 50% 25%</small><img src="' + editor.responsivearea_path + '/images/3_25_50_25.png" />', '3_25_50_25'],
              ['3 kolumny<small>25% 25% 50%</small><img src="' + editor.responsivearea_path + '/images/3_25_25_50.png" />', '3_25_25_50'],
              ['3 kolumny<small>50% 25% 25%</small><img src="' + editor.responsivearea_path + '/images/3_50_25_25.png" />', '3_50_25_25'],
              ['4 kolumny<small>po 25%</small><img src="' + editor.responsivearea_path + '/images/4_25_25_25_25.png" />', '4_25_25_25_25'],
              ['5 kolumn<small>po 20%</small><img src="' + editor.responsivearea_path + '/images/5_20_20_20_20_20.png" />', '5_20_20_20_20_20']
            ],
            default: '2_50_50'
          },
          {
            type: 'select',
            id: 'align',
            label: 'Wyrównanie w blokach w pionie',
            items: [
              ['do góry', "gora"],
              ['do środka', "srodek"],
              ['do dolu', "dol"],
            ],
            style: 'display: block',
            default: 'gora'
          }          
        ]
      }
    ],
    onOk: function () {
      var dialog = this;
      var mode = dialog.getValueOf('tab-basic', 'layout');
      var aligns = dialog.getValueOf('tab-basic', 'align');
      var tpl = responsiveness_get_template(mode, aligns);
      if (tpl !== "") {
          //editor.insertHtml(tpl);
          tpl = CKEDITOR.dom.element.createFromHtml(tpl);
          this.getParentEditor().insertElement(tpl)           
      }
    }
  };
});

function responsiveness_get_template(tpl, aligns) {
  'use strict';
  var grid = "";
  var align = 'WyrownanieGora';
  
  if ( aligns == 'gora' ) {
       align = 'WyrownanieGora';
  }
  if ( aligns == 'srodek' ) {
       align = 'WyrownanieSrodek';
  }
  if ( aligns == 'dol' ) {
       align = 'WyrownanieDol';
  }
  
  switch (tpl) {
    case '1_100':
      grid = '<div class="EdytorKolumny EdytorKolumny-1 ' + align + '">';
      grid += '<div class="EdytorKolumna EdytorKolumna-1 EdytorKolumna-100"></div>';
      grid += '</div><br />';
      break;

    case '2_50_50':
      grid = '<div class="EdytorKolumny EdytorKolumny-2 ' + align + '">';
      grid += '<div class="EdytorKolumna EdytorKolumna-1 EdytorKolumna-50"></div>';
      grid += '<div class="EdytorKolumna EdytorKolumna-2 EdytorKolumna-50"></div>';
      grid += '</div><br />';
      break;

    case '2_75_25':
      grid = '<div class="EdytorKolumny EdytorKolumny-2 ' + align + '">';
      grid += '<div class="EdytorKolumna EdytorKolumna-1 EdytorKolumna-75"></div>';
      grid += '<div class="EdytorKolumna EdytorKolumna-2 EdytorKolumna-25"></div>';
      grid += '</div><br />';
      break;

    case '2_25_75':
      grid = '<div class="EdytorKolumny EdytorKolumny-2 ' + align + '">';
      grid += '<div class="EdytorKolumna EdytorKolumna-1 EdytorKolumna-25"></div>';
      grid += '<div class="EdytorKolumna EdytorKolumna-2 EdytorKolumna-75"></div>';
      grid += '</div><br />';
      break;

    case '2_33_66':
      grid = '<div class="EdytorKolumny EdytorKolumny-2 ' + align + '">';
      grid += '<div class="EdytorKolumna EdytorKolumna-1 EdytorKolumna-33"></div>';
      grid += '<div class="EdytorKolumna EdytorKolumna-2 EdytorKolumna-66"></div>';
      grid += '</div><br />';
      break;

    case '2_66_33':
      grid = '<div class="EdytorKolumny EdytorKolumny-2 ' + align + '">';
      grid += '<div class="EdytorKolumna EdytorKolumna-1 EdytorKolumna-66"></div>';
      grid += '<div class="EdytorKolumna EdytorKolumna-2 EdytorKolumna-33"></div>';
      grid += '</div><br />';
      break;

    case '3_33_34_33':
      grid = '<div class="EdytorKolumny EdytorKolumny-3 ' + align + '">';
      grid += '<div class="EdytorKolumna EdytorKolumna-1 EdytorKolumna-33"></div>';
      grid += '<div class="EdytorKolumna EdytorKolumna-2 EdytorKolumna-33"></div>';
      grid += '<div class="EdytorKolumna EdytorKolumna-3 EdytorKolumna-33"></div>';
      grid += '</div><br />';
      break;

    case '3_25_50_25':
      grid = '<div class="EdytorKolumny EdytorKolumny-3 ' + align + '">';
      grid += '<div class="EdytorKolumna EdytorKolumna-1 EdytorKolumna-25"></div>';
      grid += '<div class="EdytorKolumna EdytorKolumna-2 EdytorKolumna-50"></div>';
      grid += '<div class="EdytorKolumna EdytorKolumna-3 EdytorKolumna-25"></div>';
      grid += '</div><br />';
      break;

    case '3_25_25_50':
      grid = '<div class="EdytorKolumny EdytorKolumny-3 ' + align + '">';
      grid += '<div class="EdytorKolumna EdytorKolumna-1 EdytorKolumna-25"></div>';      
      grid += '<div class="EdytorKolumna EdytorKolumna-2 EdytorKolumna-25"></div>';
      grid += '<div class="EdytorKolumna EdytorKolumna-3 EdytorKolumna-50"></div>';
      grid += '</div><br />';
      break;

    case '3_50_25_25':
      grid = '<div class="EdytorKolumny EdytorKolumny-3 ' + align + '">';
      grid += '<div class="EdytorKolumna EdytorKolumna-1 EdytorKolumna-50"></div>';
      grid += '<div class="EdytorKolumna EdytorKolumna-2 EdytorKolumna-25"></div>';      
      grid += '<div class="EdytorKolumna EdytorKolumna-3 EdytorKolumna-25"></div>';      
      grid += '</div><br />';
      break;

    case '4_25_25_25_25':
      grid = '<div class="EdytorKolumny EdytorKolumny-4 ' + align + '">';
      grid += '<div class="EdytorKolumna EdytorKolumna-1 EdytorKolumna-25"></div>';      
      grid += '<div class="EdytorKolumna EdytorKolumna-2 EdytorKolumna-25"></div>';      
      grid += '<div class="EdytorKolumna EdytorKolumna-3 EdytorKolumna-25"></div>';      
      grid += '<div class="EdytorKolumna EdytorKolumna-4 EdytorKolumna-25"></div>';      
      grid += '</div><br />';
      break;

    case '5_20_20_20_20_20':
      grid = '<div class="EdytorKolumny EdytorKolumny-5 ' + align + '">';
      grid += '<div class="EdytorKolumna EdytorKolumna-1 EdytorKolumna-20"></div>';      
      grid += '<div class="EdytorKolumna EdytorKolumna-2 EdytorKolumna-20"></div>';      
      grid += '<div class="EdytorKolumna EdytorKolumna-3 EdytorKolumna-20"></div>';      
      grid += '<div class="EdytorKolumna EdytorKolumna-4 EdytorKolumna-20"></div>';      
      grid += '<div class="EdytorKolumna EdytorKolumna-5 EdytorKolumna-20"></div>';      
      grid += '</div><br />';
      break;

  }
  return grid;
}