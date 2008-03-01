Event.observe( window, "load", function() {
    var height = document.viewport.getHeight() - 160;
    
    $$( 'iframe.tab-content.' ).each( function(el) {
        el.setStyle( {
            'height': height + 'px',
        } );
    } );
} );