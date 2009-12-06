Event.observe( window, "load", function() {
    if (Prototype.Browser.Opera) {
        var height = document.documentElement.clientHeight - 173;
    } else {
        var height = document.viewport.getHeight() - 173;
    }

    $$( 'iframe.tab-content.' ).each( function(el) {
        el.setStyle( {
            'height': height + 'px'
        } );
    } );

    if ( $( 'dashboard' ) ) {
        new Ajax.PeriodicalUpdater(
            'dashboard',
            'dashboard.jsp', {
                method: 'get',
                frequency: 5
            }
        );
        new Ajax.PeriodicalUpdater(
            'servertime',
            'servertime.jsp', {
                method: 'get',
                frequency: 60
            }
        );
    }
} );

function callServer( url ) {
    document.getElementById('serverData').innerHTML = '<iframe src="' + url + '" width="0" height="0" frameborder="0"></iframe>';
}

function over(elem) {
    elem.className = 'mouseover';
}
function out(elem) {
    elem.className = '';
}

function getLinkRootLocation() {
    var location = $$('#phpUnderControlHeader a')[0].getAttribute('href');
    return location.substring(0, location.indexOf('/index'));
}
