( function() {

    tinymce.PluginManager.add( 'kashing_tinymce', function( editor, url ) {

        // Add a button that opens a window

        editor.addButton( 'kashing_add_form', {

            text: 'Add Kashing Form',
            icon: false,
            tooltip: 'Add a Kashing Form to your page.',
            onclick: function() {

                // Open window

                editor.windowManager.open( {
                    title: 'Add Kashing Form:',
                    width: 320,
                    height: 240,
                    body: [{
                        type: 'listbox',
                        name: 'title',
                        label: 'Title',
                        values : [
                            { text: 'Test1', value: '1' },
                            { text: 'Test2', value: '5' },
                            { text: 'Test3', value: '7' }
                        ]
                    },],
                    onsubmit: function( e ) {

                        // Insert content when the window form is submitted

                        editor.insertContent( '[kashing_form form_id="' + e.data.title + '"]' );
                    }

                } );
            }

        } );

    } );

} )();