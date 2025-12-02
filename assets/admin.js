jQuery(document).ready(function ($) {

	$('#expdbs-export').click(function () {

		$('.expdbs-view').hide();
		$('#expdbs-init').show();

		ajax_submit({
			'start': true,
			'done': false,
			'action': 'expdbs_start',
			'compress': $("#expdbs-compress").is(':checked') ? 1 : 0,
			'nonce': $(this).attr('data-nonce')
		});

		return false;
	});


	function ajax_submit(data) {

		$.post(get_ajax_url(), data, function (e) {

			if (typeof e.status === 'undefined') {

				alert('Unknown error');

			} else if (e.status === 'error') {

				alert(e.reason);

			} else if (e.status === 'ok') {

				if (e.data.is_done) {

					$('#expdbs-gen').hide();
					$('#expdbs-comp').hide();
					$('#expdbs-done').show();

					data['done'] = true;
					data['action'] = 'expdbs_download';

					$('<form action="' + get_ajax_url() + '" method="post">' +
						'<input type="hidden" name="key" value="' + data['key'] + '" />' +
						'<input type="hidden" name="action" value="expdbs_download" />' +
						'<input type="hidden" name="nonce" value="' + data['nonce'] + '" />' +
						'</form>').appendTo('body').submit();

				} else if (!data['done']) {

					if (data['start']) {

						$('#expdbs-init').hide();
						$('#expdbs-gen-percent').html('0');
						$('#expdbs-gen').show();

						data['start'] = false;
						data['key'] = e.data.key;

					} else if (e.data.compressing) {

						$('#expdbs-gen').hide();
						$('#expdbs-comp').show();

					} else {

						$('#expdbs-gen-percent').html(e.data.percent);
					}

					data['action'] = e.data.compressing ? 'expdbs_compress' : 'expdbs_export';
					ajax_submit(data);
				}
			}

		}).fail(function () {
			alert('Server communication error.\nPlease try again.');
		});
	}


	function get_ajax_url() {
		return ajaxurl + '?_=' + new Date().getTime();
	}

});
