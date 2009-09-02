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

    $$(headingSelector).each(collapseTestSuite);
} );

function callServer( url ) {
    document.getElementById('serverData').innerHTML = '<iframe src="' + url + '" width="0" height="0" frameborder="0"></iframe>';
}

function checkIframe(stylesheetURL) {
  if (top != self) {//We are being framed!

    //For Internet Explorer
    if (document.createStyleSheet) {
        document.createStyleSheet(stylesheetURL);
    }
    else { //Non-ie browsers

      var styles = "@import url('" + stylesheetURL + "');";

      var newSS = document.createElement('link');

      newSS.rel = 'stylesheet';

      newSS.href = 'data:text/css,' + escape(styles);

      document.getElementsByTagName("head")[0].appendChild(newSS);
    }
  }
}

function over(elem) {
    elem.className = 'mouseover';
}
function out(elem) {
    elem.className = '';
}

function getLinkRootLocation() {
    var location = $$('div#container > h1 > a')[0].getAttribute('href');
    return location.substring(0, location.indexOf('/index'));
}

var headingSelector = "th[colspan=4]";

var scrolledToFailure = false;

var untilNextHeading = function(heading, callback) {
    var dataRow = heading.parentNode;
    while ((dataRow = dataRow.next()) && dataRow.select(headingSelector).length == 0) {
        var td = dataRow.select('td')[0];
        if (dataRow.className === "" && $(td).getAttribute('colspan') == 5) {
            break;
        }
        callback(dataRow);
    }
}

var getExpanderEventSource = function(evt) {
    try {
        var element = Event.element(evt);
        if (element.nodeName == 'IMG') {
            element = element.parentNode;
        }
        return element;
    } catch (e) {
        return evt;
    }
}

var collapseTestSuite = function(heading) {
    heading = getExpanderEventSource(heading);
    var signalAttached = false;
    var shouldCollapse = true;

    untilNextHeading(heading, function(tr) {
        var test = tr.select('td')[0];

        if (test.hasClassName('error') || test.hasClassName('failure')) {
            shouldCollapse = false;
            if (scrolledToFailure === false) {
                scrolledToFailure = true;
                heading.scrollTo();
            }
        }
    });

    if (!shouldCollapse) {
        expandTestSuite(heading);
        return false;
    }

    untilNextHeading(heading, function(tr) {
        tr.hide();

        if (!signalAttached) {
            heading.parentNode.setStyle({cursor: 'pointer'});
            changeExpanderImage(heading, 'collapsed');
            heading.parentNode.onclick = expandTestSuite;
        }
    });
    return false;
}

var changeExpanderImage = function(heading, iconName) {
    var icon = heading.select('img');
    var imagePath = getLinkRootLocation() + '/images/php-under-control/' + iconName + '.png';
    if (icon.length > 0) {
        $(icon[0]).setAttribute('src', imagePath);
    } else {
        heading.update('<img style="display:inline" src="' + imagePath + '" alt=""/>' + heading.innerHTML);
    }
}

var expandTestSuite = function(heading) {
    heading = getExpanderEventSource(heading);
    var signalAttached = false;
    untilNextHeading(heading, function(tr) {
        tr.show();

        if (!signalAttached) {
            heading.parentNode.onclick = collapseTestSuite;
            changeExpanderImage(heading, 'expanded');
        }
    });
    return false;
}