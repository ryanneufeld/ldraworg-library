$( function() {
	$('.ui.sidebar').sidebar('attach events', '#menubutton', 'show');
	$('.ui.checkbox').checkbox();
	$('.ui.dropdown').dropdown();
	$('.search.dropdown').dropdown();
	$('.ui.accordion').accordion();
	$('table.sortable').tablesort();
	$('.suffixmenu .item').tab();
	$('.ui.menu > .ui.dropdown').dropdown({on: 'hover', });
	$('.ui.sitesearch').each(
		function (){
		$(this).search({
			apiSettings: {
			url: 'https://www.ldraw.org/common/php/unified_search.php?q={query}&sites=main'
			},
			minCharacters: 3,
			type: 'category'
		})
		}
	);

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

	$('.feed.image').visibility(
		{
		type:'image'
		}
	);

});
