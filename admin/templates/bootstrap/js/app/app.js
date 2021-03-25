
// import 'bootstrap'
// import 'bootstrap-select'

import './polyfills.js'

import './plugins/tabdrop.js'

import './snippets/selectall.js'
import './snippets/form-counter.js'

import './components/accordions.js'
import './components/charts.js'
import './components/forms.js'
//import 'shop/js/components/icons'
import './components/setup-assistant.js'

import './views/dashboard.js'
import './views/sidebar.js'
import './views/topbar.js'

/* init plugins */

$('[data-toggle="tooltip"]').tooltip({
	placement: 'auto'
});
$('[data-toggle="popover"]').popover();

$('.nav-tabs').tabdrop();

/* events */

$(window).on('load', () => {
	$('#page-wrapper').removeClass('hidden disable-transitions')
	$('html').addClass('ready')
	$('body > .spinner').remove()

	document.dispatchEvent(new CustomEvent('ready', {
		detail: {
			jquery : $
		}
	}))
})
