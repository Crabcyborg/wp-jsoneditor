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
				response && editor.set(JSON.parse(decodeURIComponent(response.replace(/\+/g, ' '))));
			}
		));

		$(document).on('click', '#jsoneditor-save', () => $.post(
			ajaxurl, { prefix: prefix(), key: key(), json: encodeURIComponent(JSON.stringify(editor.get())), action: 'jsoneditor_action_save' },
			response => {

			}
		));
	});
})();