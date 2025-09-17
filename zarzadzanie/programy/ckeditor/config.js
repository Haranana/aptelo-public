/**
 * @license Copyright (c) 2003-2021, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

CKEDITOR.editorConfig = function( config ) {

	config.language = 'pl';
	config.enterMode = CKEDITOR.ENTER_BR;
	config.shiftEnterMode = CKEDITOR.ENTER_P;
	config.entities_latin = false;
	config.toolbar = "cms";
	config.disableNativeSpellChecker = false;
	config.removePlugins = 'scayt';
  config.allowedContent = true;

  config.extraPlugins = 'lineheight,responsivearea,youtube2,filmmp4,playermp3,emoji';
  config.line_height="1;1.5;1.8;2;2.3;2.5;2.8;3" ;
  
  config.font_names = 'Arial;Verdana;Tahoma;Georgia;Times New Roman';

  config.autoGrow_maxHeight = 600;
  config.image_prefillDimensions = false;

  config.coreStyles_underline = {
    element: 'span',
    attributes: { 'style': 'text-decoration: underline;' }
  };
  
  config.format_tags = 'p;h1;h2;h3;h4;h5;h6';
  
	config.toolbar_cms =
	[
		['Source'],
		['Undo','Redo','Cut','Replace','Copy','Paste','PasteText','RemoveFormat','ShowBlocks'],
		['Bold','Italic','Underline','Strike'],
		['NumberedList','BulletedList'],
		['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
		['Link','Unlink','Subscript','Superscript','Blockquote','Indent','Outdent','CreateDiv'],
		['Format','Font','FontSize','lineheight'],
		['TextColor','BGColor'],
    ['Image','youtube2','filmmp4','playermp3','wenzgmap','Table','HorizontalRule','SpecialChar','responsivearea','emoji'],
    ['About']
	];

};

CKEDITOR.on('instanceReady', function(evt) {
    var editor = evt.editor;
    editor.balloonToolbars.create({
      buttons: 'Link,Unlink,Image',
      widgets: 'image'
    });
});

CKEDITOR.on('dialogDefinition', function(ev) {
    var dialogName = ev.data.name;
    var dialogDefinition = ev.data.definition;

    if (dialogName == "table" || dialogName == "tableProperties") {
        dialogDefinition.removeContents('advanced');
        var infoTab = dialogDefinition.getContents( 'info' );
        txtWidth = infoTab.get( 'txtWidth' );
        txtWidth['default'] = "100%";
        infoTab.remove( 'txtCellPad' );
        infoTab.remove( 'txtCellSpace' );
        infoTab.remove( 'txtCaption' );
        infoTab.remove( 'txtSummary' );        
        infoTab.remove( 'txtHeight' );
        infoTab.remove( 'selHeaders' );
    }
    
    if (dialogName == "cellProperties") {
        var infoTab = dialogDefinition.getContents( 'info' );
        infoTab.remove( 'wordWrap' );
        infoTab.remove( 'cellType' );
        infoTab.remove( 'colSpan' );
        infoTab.remove( 'rowSpan' );
        infoTab.remove( 'height' );
    }    

    if (dialogName == 'link') {
      var infoTab = dialogDefinition.getContents( 'target' );
      var targetField = infoTab.get( 'linkTargetType' );
      targetField.items = targetField.items.filter(function(x) { return x[1] == '_blank' || x[1] == 'notSet' ; });
    }

    if (dialogName == 'creatediv' || dialogName == 'editdiv') {
        var infoTab = dialogDefinition.getContents( 'advanced' );
        infoTab.remove( 'lang' );
        infoTab.remove( 'title' );
        infoTab.remove( 'dir' );
    }

});