CKEDITOR.plugins.add( 'responsivearea', {
    icons: 'responsivearea',
    init: function( editor ) {
        editor.responsivearea_path = this.path;
        
        editor.addCommand( 'responsivearea', new CKEDITOR.dialogCommand( 'responsiveareaDialog' ) );
        editor.ui.addButton( 'responsivearea', {
            label: 'Wstaw responsywne bloki',
            command: 'responsivearea',
            toolbar: 'insert'
        });

        CKEDITOR.dialog.add( 'responsiveareaDialog', this.path + 'dialogs/responsivearea.js' );
    }
});