import $ from 'jquery';
global.$ = global.jQuery = $;

import '../semantic/dist/components/api.js';
import '../semantic/dist/components/transition.js';
import '../semantic/dist/components/visibility.js';
import '../semantic/dist/components/dimmer.js';
import '../semantic/dist/components/popup.js';
import '../semantic/dist/components/modal.js';
import '../semantic/dist/components/checkbox.js';
import '../semantic/dist/components/dropdown.js';
import '../semantic/dist/components/accordion.js';
import '../semantic/dist/components/calendar.js';
import '../semantic/dist/components/tab.js';
import '../semantic/dist/components/search.js';
import './tablesort.js';

import Chart from 'chart.js/auto';
window.Chart = Chart;

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
	$('.ui.radio.checkbox').checkbox();
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

