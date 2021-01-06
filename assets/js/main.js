jQuery( function ( $ ) {
	'use strict';

	/***********************************************
  * IMPORT CONTENT
  ***********************************************/

	$( '.ss_importer-import-data' ).on( 'click', function (e) {

		e.preventDefault();

		var selectedContent = $( this ).val(),
		$demosWrapper   		= $( this ).closest( '.ss_importer-wrapper-inner' );

		startImport( selectedContent );

	});

	/**
	* Start the import process by clearing the logs, hiding the demos and preparing the data
	* which will then be passed to the main ajax function
	*/
	function startImport( selectedContent ){

		// Clear the log and reset all states.
		$( '.ss_importer-wrapper' ).removeClass('loading complete');
		$( '.postprocess-import-logs' ).removeClass('danger success');
		$( '.postprocess-import-logs' ).html('');
		$( '.postprocess-error-logs' ).html('');

		$(".ss_importer-wrapper-inner").fadeOut();

		// Prepare data for the AJAX call
		var data = new FormData();
		data.append( 'action', 'ss_importer_pre_processing' );
		data.append( 'security', ss_importer.ajax_nonce );
		data.append( 'selectedDemo', selectedContent );

		if ( $('#ss_importer-content-file').length ) {
			data.append( 'content_file', $('#ss_importer-content-file')[0].files[0] );
		}
		if ( $('#ss_importer-widget-file').length ) {
			data.append( 'widget_file', $('#ss_importer-widget-file')[0].files[0] );
		}
		if ( $('#ss_importer-redux-file').length ) {
			data.append( 'redux_file', $('#ss_importer-redux-file')[0].files[0] );
			data.append( 'redux_opt_name', $('#ss_importer-redux-opt-name').val() );
		}

		// Run the main Ajax function
		doAjax( data );

	}

	/**
	* Main ajax function
	*/
	function doAjax( data ){

		$.ajax({
			method:      'POST',
			url:         ajaxurl,
			data:        data,
			contentType: false,
			processData: false,
			beforeSend:  function() {
				$( '.ss_importer-wrapper' ).addClass('loading');
			}
		})
		.done( function(response){

			if( response.status == 'heartbeat' ){

				doAjax( data );

				return true;

			}else if( response.status == 'complete' ){

				if( response.errors != '' && response.errors != undefined || response.errors != null ){
					$( '<ul class="ss_importer-notice-wrap postprocess-error-logs warning"></ul>' ).insertBefore('.postprocess-import-logs').append( '<li>' + response.errors + '</li>' );
				}

				$( '.postprocess-import-logs' ).addClass('success').append( '<li>' + response.message + '</li>' );
				$( '.ss_importer-wrapper' ).removeClass('loading').addClass('complete');

				$('.ss_importer-state.ss_importer-complete').fadeIn(500);

				return true;

			}else if( response.status == 'error' ){

				$(".ss_importer-wrapper-inner").fadeIn();

				$( '.postprocess-import-logs' ).addClass('danger').append( '<li>Error: ' + response.message + '</li>' );
				$( '.ss_importer-wrapper' ).removeClass('loading').addClass('complete');

			}

		})
		.fail( function( error ) {

			$( '.postprocess-import-logs' ).addClass('danger').append( '<li>Error: ' + error.status + ' (' + error.statusText + ')' + '</li>' );
			$( '.ss_importer-wrapper' ).removeClass('loading').addClass('complete');

		});

	}

	/**
	* Logic to jump from one step to another (Example: Moving from content import to widget import)
	*/
	function nextStep(){
		return true;
	}

	/***********************************************
  * TABS BEHAVIOR
  ***********************************************/
	(function () {

		$(".sigma_importer-tabs-item").on('click', function(e){
			e.preventDefault();
			$(".sigma_importer-tabs-item").removeClass('active');
			$(this).addClass('active');

			var category = $(this).data('category');

			filterDemos(category);
		});

		/**
	  * Filter demos based the the category clicked.
	  */
		function filterDemos( category ){

			var $demos = $(".ss_importer-demo");

			if( category === '*' ){
				return $demos.show();
			}

			var filter = category ? '[data-categories*="' + category + '"]' : '';
			$demos.hide().filter( filter ).show();

		}

	}());

	/***********************************************
  * SYSTEM STATUS AND MISC
  ***********************************************/
	$(".trigger-system-status").on('click', function(){
		$(".ss_importer-system-status").toggleClass('open');
	});

	// Try again
	$(".refresh-trigger").on('click', function(e){
		e.preventDefault();

		location.reload();
		return false;
	})

});
