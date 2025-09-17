CKEDITOR.plugins.add( 'emoji', {
    icons: 'emoji',
    init: function( editor ) {
        editor.emoji_path = this.path;
        
        editor.addCommand( 'emoji', new CKEDITOR.dialogCommand( 'emojiDialog' ) );
        editor.ui.addButton( 'emoji', {
            label: 'Wstaw emoji',
            command: 'emoji',
            toolbar: 'insert'
        });

        CKEDITOR.dialog.add( 'emojiDialog', this.path + 'dialogs/emoji.js' );
    }
});