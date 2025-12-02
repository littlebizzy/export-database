jQuery(document).ready(function($){

    // start export
    $('#expdbs-export').click(function(){
        $('#expdbs-status').html('exporting...');
        $.post(expdbs_ajax.ajax_url, {
            action: 'expdbs_export',
            nonce: expdbs_ajax.nonce,
            compress: $('#expdbs-compress').is(':checked') ? 1 : 0
        }, function(resp){
            if (!resp || !resp.success) {
                alert('export failed');
                return;
            }
            $('#expdbs-status').html('export completed');
            location.reload();
        });
    });

    // delete file
    $('.expdbs-delete').click(function(){
        if (!confirm('delete this file?')) return;
        var file = $(this).data('file');
        $.post(expdbs_ajax.ajax_url, {
            action: 'expdbs_delete',
            nonce: expdbs_ajax.nonce,
            file: file
        }, function(resp){
            if (resp && resp.success) location.reload();
            else alert('unable to delete file');
        });
    });

});
