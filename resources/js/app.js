import $ from 'jquery';
window.$ = window.jQuery = $;
//require('../semantic/dist/semantic.js');
require('../semantic/dist/components/api.js');
require('../semantic/dist/components/transition.js');
require('../semantic/dist/components/visibility.js');
require('../semantic/dist/components/checkbox.js');
require('../semantic/dist/components/dropdown.js');
require('../semantic/dist/components/accordion.js');
require('../semantic/dist/components/calendar.js');
require('../semantic/dist/components/tab.js');
require('../semantic/dist/components/search.js');
require('./tablesort.js');

let cal_options = {
	type: 'datetime',
	formatter: {
		datetime: 'YYYY-MM-DD HH:mm:ss'
	},
	disableMinute: true,
};

let quick_search_options = {
	preserveHTML : false,  
	apiSettings: {
		url: '/api/search/quicksearch?s={query}',
	},
	minCharacters: 3,
	type: 'category',
};

let fomantic_init = function() {
	$('.ui.checkbox').checkbox();
	$('.dropdown').dropdown();
	$('.ui.accordion').accordion();
	$('table.sortable').tablesort();
	$('.ui.calendar').calendar(cal_options);
	$('.suffixmenu .item').tab();
	$('.dashboardmenu .item').tab();
	$('.summarymenu .item').tab();
	$('.ui.menu > .ui.dropdown').dropdown({on: 'hover', });
	$('.ui.ptsearch').search(quick_search_options);
};

document.addEventListener('DOMContentLoaded', fomantic_init, false);
window.addEventListener('jquery', fomantic_init);

