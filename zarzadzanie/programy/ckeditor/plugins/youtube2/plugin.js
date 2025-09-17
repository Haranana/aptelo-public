CKEDITOR.plugins.add( 'youtube2', {
    icons: 'youtube2',
    init: function( editor ) {
        editor.youtube2_path = this.path;
        
        editor.addCommand( 'youtube2', new CKEDITOR.dialogCommand( 'youtube2Dialog' ) );
        editor.ui.addButton( 'youtube2', {
            label: 'Wstaw film Youtube',
            command: 'youtube2',
            toolbar: 'insert'
        });

        CKEDITOR.dialog.add( 'youtube2Dialog', this.path + 'dialogs/youtube2.js' );
    }
});