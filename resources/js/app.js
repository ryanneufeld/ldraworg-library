let cal_options = {
	type: 'datetime',
	formatter: {
		datetime: 'YYYY-MM-DD HH:mm:ss'
	},
	disableMinute: true,
};

let fomantic_init = function() {
	$('.ui.checkbox').checkbox();
	$('.dropdown').dropdown();
//	$('.search.dropdown').dropdown();
//	$('.clearable.dropdown').dropdown();
	$('.ui.accordion').accordion();
	$('table.sortable').tablesort();
	$('.ui.calendar').calendar(cal_options);
	$('.suffixmenu .item').tab();
	$('.dashboardmenu .item').tab();
	$('.ui.menu > .ui.dropdown').dropdown({on: 'hover', });
	$('.ui.ptsearch').each(
		function (){ 
		$(this).search({
		preserveHTML : false,  
		apiSettings: {
			url: '/api/search/quicksearch?s={query}',
		},
		minCharacters: 3,
		type: 'category'
		})
		}
	);
};

document.addEventListener('DOMContentLoaded', fomantic_init, false);
window.addEventListener('jquery', fomantic_init);

