(function( $ ) {
	'use strict';

	$(document).ready(function(){


		// ===== Manual import actions
		const importButtons = document.querySelectorAll('.do-ajax');

		importButtons.forEach( function( importButton ) {
			importButton.addEventListener('click', function(e){
				e.preventDefault();

				const loader  = document.querySelector('.loading-overlay');
				const runType = this.getAttribute('data-run-type');
				const table   = this.getAttribute('data-table');

				if ( runType ) {
					var queryString = `run_type=${runType}`;
				} else if ( table ) {
					var queryString = `table=${table}`;
				}

				$.ajax({
					url: window.location.origin + '/wp-content/plugins/yotpo-reviews/admin/class-yotpo-reviews-ajax.php?' + queryString,
					beforeSend: function(xhr){
						console.log('before', xhr);
						loader.classList.add('loading');
					},
					complete: function (jqXHR) {
						console.log('complete', jqXHR);
						var response = JSON.parse(jqXHR.responseText);
						loader.classList.remove('loading');

						if ( response.ajax_type !== 'clear' ) {
							setTimeout(function(){ location.reload(true); }, 3000);
						}
					},
					success: function(response){
						var response = JSON.parse(response);

						console.log('success', response);

						if ( response.ajax_type == 'clear' ) {
							const tableCols = document.querySelectorAll('td.column-columnname');
							tableCols.forEach( function( tableCol ) {
								tableCol.style.display = 'none';
							});
						}

						const noticeContainer = document.getElementById('up-wrap');
						noticeContainer.innerHTML = `<div class="notice notice-success is-dismissible"><p>${response.ajax_msg}</p></div>`;
					},
					error: function(xhr, status, error){
						console.error('error', xhr, status, error);
						noticeContainer.innerHTML = `<div class="notice notice-error is-dismissible"><p>${error}</p></div>`;
					}
				});
			});
		});



		// ===== Product identifier reset
		const radios = document.querySelectorAll('.identifiers'),
			  enable = document.querySelector('.enable-radios');

		if ( enable !== null ) {
			enable.addEventListener('click', function(e) {
				e.preventDefault();
				for(var i = 0; i < radios.length; i++) {
					radios[i].disabled = false;
				}
			});
		}


	});

})( jQuery );
