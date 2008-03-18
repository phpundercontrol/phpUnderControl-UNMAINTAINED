Event.observe( window, "load", function() {
    var height = document.viewport.getHeight() - 173;
    
    $$( 'iframe.tab-content.' ).each( function(el) {
        el.setStyle( {
            'height': height + 'px',
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
    //alert('Scheduling build for ' + projectName);
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