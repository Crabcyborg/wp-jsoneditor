(function() {
	const $ = jQuery;
	const prefix = () => $('#jsoneditor-prefix').val().trim();
	const key = () => $('#jsoneditor-key').val().trim();

	/**
	 * 
	 * @param {string} type 
	 * @param {object} response 
	 * @param {function|undefined} handleSuccess
	 */
	const jsoneditor_status = (type, response, handleSuccess) => {
		const status = $(`#jsoneditor-${type}-status`).removeClass();
		const success = response && response.result;
		const color = success ? 'green' : 'red';

		if(success) {
			typeof handleSuccess === 'function' && handleSuccess();
			status.html('success');
		} else {
			status.html('error'+(response && response.error ? ': '+response.error : ''));
		}

		status.css({color});
		setTimeout(() => status.html(''), success ? 1000 : 10000);
	};

	$(document).ready(function() {
		const target = $('#jsoneditor');
		const editor = new JSONEditor(target[0], {});

		$(document).on('click', '#jsoneditor-load', () => $.post(
			ajaxurl, { prefix: prefix(), key: key(), action: 'jsoneditor_action_load' },
			response => jsoneditor_status('load', response, () => editor.set(response.payload)),
			'json'
		));

		$(document).on('click', '#jsoneditor-save', () => $.post(
			ajaxurl, { prefix: prefix(), key: key(), json: editor.get(), action: 'jsoneditor_action_save' },
			response => jsoneditor_status('save', response),
			'json'
		));
	});
})();