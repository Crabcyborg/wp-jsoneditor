(function() {
	const $ = jQuery;
	const prefix = () => $('#jsoneditor-prefix').val().trim();
	const key = () => $('#jsoneditor-key').val().trim();

	$(document).ready(function() {
		const target = $('#jsoneditor');
		const editor = new JSONEditor(target[0], {});

		$(document).on('click', '#jsoneditor-load', () => $.post(
			ajaxurl, { prefix: prefix(), key: key(), action: 'jsoneditor_action_load' },
			response => {
				var status = $('#jsoneditor-load-status').removeClass();
				
				if(response && response.result) {
					editor.set(response.payload);
					status.html('success');
				} else {
					status.html('error'+(response && response.error ? ': '+response.error : ''));
				}

				status.css({color: status.html() === 'success' ? 'green' : 'red'});
				setTimeout(() => status.html(''), 1000);
			},
			'json'
		));

		$(document).on('click', '#jsoneditor-save', () => $.post(
			ajaxurl, { prefix: prefix(), key: key(), json: editor.get(), action: 'jsoneditor_action_save' },
			response => {
				var status = $('#jsoneditor-save-status').removeClass();
				status.html(response && response.result ? 'success' : 'error'+(response && response.error ? ': '+response.error : ''));
				status.css({color: status.html() === 'success' ? 'green' : 'red'});
				setTimeout(() => status.html(''), 1000);
			},
			'json'
		));
	});
})();