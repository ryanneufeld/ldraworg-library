import $ from 'jquery';
window.$ = window.jQuery = $;

require ('../semantic/dist/components/visibility.js');
require ('../semantic/dist/components/modal.js');
require ('../semantic/dist/components/search.js');
require ('../semantic/dist/components/checkbox.js');
require ('../semantic/dist/components/dropdown.js');
require ('../semantic/dist/components/sidebar.js');
require ('../semantic/dist/components/accordion.js');
require ('./tablesort.js');

$(document).ready( function() {
	$('.ui.login.modal').modal();
	$('.ui.sidebar').sidebar('attach events', '#menubutton', 'show');
	$('.ui.checkbox').checkbox();
	$('.ui.dropdown').dropdown();
	$('.search.dropdown').dropdown();
	$('.ui.accordion').accordion();
	$('table.sortable').tablesort();

	$('.ui.menu > .ui.dropdown').dropdown({on: 'hover', });

	$('.ui.sitesearch').each(
		function (){ 
		$(this).search({
			apiSettings: {
			url: '/common/php/unified_search.php?q={query}&sites=main'
			},
			minCharacters: 3,
			type: 'category'
		})
		}
	);

	$('.ui.ptsearch').each(
		function (){ 
		$(this).search({
			apiSettings: {
			url: '/common/php/unified_search.php?q={query}&sites=pt'
			},
			minCharacters: 3,
			type: 'category'
		})
		}
	);

	$('.feed.image').visibility(
		{
		type:'image'
		}
	);


});
