jQuery(function($) {
	var messageBlock = $('#connection-error-message');
	$("#custom-top-post-form").submit(function (event) { 	
	event.preventDefault();
	messageBlock.hide();	
		var path = $("input[name='path'").val();	
		var serialized = $( '#custom-top-post-form' ).serialize();
		/* start for file uploading*/
		const url = GAAjax.ajaxurl; 
		
		const files = document.querySelector('[type=file]').files;
		const formData = new FormData();

		for (let i = 0; i < files.length; i++) {
			let file = files[i];

			formData.append('files[]', file);
		}
		
		fetch(url+'?action=ga_save_settings&nonce=GAAjax.nonce', {
			method: 'POST',
			body: formData
		}).then(response => {
			console.log(response);
		});	
		/* end for file uploading*/
		$.ajax({
			type:'POST',
			/* url:'admin-ajax.php', */
			url : GAAjax.ajaxurl,
			data: serialized,
			action: "ga_save_settings",
			success : function( response1 ) {
				var returnedData = JSON.parse(response1);
				messageBlock.show();
				if ('error' === returnedData.type) {
					messageBlock.css('color', 'red');
				}

				if ('success' === returnedData.type) {
					messageBlock.css('color', 'green');
					messageBlock.html(returnedData.message);
				}
			},
		})
		
	});
	$("#file").change(function(event){	
		var fileName = event.target.files[0].name;
		$("#pfile").val(fileName);
	});
});

jQuery(function($) {
	var messageBlock = $('#connection-error-message-sync');
	$("#custom-top-post-sync-form").submit(function (event) { 	
		event.preventDefault();
		messageBlock.hide();	
		
		var serialized = $( '#custom-top-post-sync-form' ).serialize();
		
		$.ajax({
			type:'POST',
			url:'admin-ajax.php',		 
			data: serialized,
			success : function( response1 ) {
				var returnedData = JSON.parse(response1);
				messageBlock.show();
				if ('error' === returnedData.type) {
					messageBlock.css('color', 'red');
				}

				if ('success' === returnedData.type) {
					messageBlock.css('color', 'green');
					messageBlock.html(returnedData.message);
				}
			},
		})		
	});
});