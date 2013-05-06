/**
 * User: mytory
 * Date: 13. 5. 2
 * Time: 오후 4:19
 */
jQuery(document).ready(function($){
    $('form').attr('enctype', 'multipart/form-data');
    $('.mytory-attachment-add-file-field').click(function(){
        var html = $('#mytory_attachment_template').html();
        $('.add-form-standard-line').before(html);
    });
    $('#mytory-attachment').on('click', '.mytory-attachment-remove-field', function(){
        $(this).parent('.mytory-attachment-one-field').remove();
    });
});

