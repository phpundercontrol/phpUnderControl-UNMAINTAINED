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

    var scrollToFailure = true;

    $$( '#phpUnitDetails tbody tr td:first-child' ).each( function( td ) { td.parentNode.hide() } );
    $$( '#phpUnitDetails tbody tr > td.failure' ).each(
        function( td )
        {
            td.parentNode.parentNode.select( 'tr' ).each(
                function ( tr )
                {
                    tr.show();
                    if ( scrollToFailure === true )
                    {
                        scrollToFailure = false;
                        tr.scrollTo();
                    }
                }
            );
        }
    );
    $$( '#phpUnitDetails tbody tr.phpUnitTestSuite th.phpUnitTestSuiteName' ).each(
        function( th ) {
            var iconName = 'expanded';
            if ( th.parentNode.parentNode.select( 'tr td.failure' ).length === 0 )
            {
                iconName = 'collapsed';
            }

            var imagePath = getLinkRootLocation() + '/images/php-under-control/' + iconName + '.png';
            th.update( '<img style="display:inline" src="' + imagePath + '" alt=""/>' + th.innerHTML );
            th.setStyle( {cursor: 'pointer'} );

            var testSuiteIcon = th.firstDescendant();
            var testSuite     = th.parentNode.parentNode;

            Event.observe( th, 'click', function() {
                testSuite.select( 'td:first-child' ).each(
                    function( td )
                    {
                        var parentRow = td.parentNode;
                        if ( parentRow.visible() )
                        {
                            testSuiteIcon.setAttribute( 'src', getLinkRootLocation() + '/images/php-under-control/collapsed.png' );
                            parentRow.hide();
                        }
                        else
                        {
                            testSuiteIcon.setAttribute( 'src', getLinkRootLocation() + '/images/php-under-control/expanded.png' );
                            parentRow.show();
                        }
                    }
                );
            } );
        }
    );
    //$$(headingSelector).each(collapseTestSuite);
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
