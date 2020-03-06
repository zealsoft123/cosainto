require('./bootstrap');

$(document).ready(function(){
	$('input#transactions').change(function( e ){
		$('button.upload-csv').click();
	});

	$('input#document').change(function( e ){
		$('button.upload-document').click();
	});

	if( window.location.href.indexOf('dashboard') > -1 ) {
		setInterval(function(){
			$.get("/transaction/last_updated", function( data ){
				if( data.trim() != 'false' ) {
					$('.alert.additional-data').show();
				}
			});
		}, 31000);
	}

	if(document.querySelector('table.transactions')){
		new Tablesort(document.querySelector('table.transactions'));
	}
});
