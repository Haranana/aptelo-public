CKEDITOR.plugins.add( 'filmmp4', {
    icons: 'filmmp4',
    init: function( editor ) {
        editor.filmmp4_path = this.path;
        
        editor.addCommand( 'filmmp4', new CKEDITOR.dialogCommand( 'filmmp4Dialog' ) );
        editor.ui.addButton( 'filmmp4', {
            label: 'Wstaw film w formacie MP4',
            command: 'filmmp4',
            toolbar: 'insert'
        });

        CKEDITOR.dialog.add( 'filmmp4Dialog', this.path + 'dialogs/filmmp4.js' );
    }
});