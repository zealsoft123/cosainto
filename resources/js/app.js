require('./bootstrap');

$(document).ready(function(){
	$('input#transactions').change(function( e ){
		$('button.upload-csv').click();
	});

	if( window.location.href.indexOf('dashboard') > -1 ) {
		setInterval(function(){
			$.get("/transaction/hash", function( data ){
				var current_hash = $('table.transactions').data('transactions-hash');

				if( current_hash !== data.trim() ) {
					$('.alert.additional-data').show();
				}
			});
		}, 1000);
	}

	new Tablesort(document.querySelector('table.transactions'));
});
