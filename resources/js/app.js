import $ from 'jquery';
window.$ = window.jQuery = $;
require('../semantic/dist/semantic.js');
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
	$('.ui.menu > .ui.dropdown').dropdown({on: 'hover', });
	$('.ui.ptsearch').search(quick_search_options);
};

document.addEventListener('DOMContentLoaded', fomantic_init, false);
window.addEventListener('jquery', fomantic_init);

