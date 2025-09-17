CKEDITOR.plugins.add( 'playermp3', {
    icons: 'playermp3',
    init: function( editor ) {
        editor.playermp3_path = this.path;
        
        editor.addCommand( 'playermp3', new CKEDITOR.dialogCommand( 'playermp3Dialog' ) );
        editor.ui.addButton( 'playermp3', {
            label: 'Wstaw plik audio w formacie Mp3',
            command: 'playermp3',
            toolbar: 'insert'
        });

        CKEDITOR.dialog.add( 'playermp3Dialog', this.path + 'dialogs/playermp3.js' );
    }
});