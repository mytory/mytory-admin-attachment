<?
if(!empty($_POST)){
    $applys_string = implode(',', $_POST['mytory_attachment_apply_post_type']);

    $mytory_attachment_apply_post_type = get_option('mytory-attachment-apply-post-type');

    if( ! $mytory_attachment_apply_post_type){
        add_option('mytory-attachment-apply-post-type', $applys_string);
    }else{
        update_option('mytory-attachment-apply-post-type', $applys_string);
    }
    $mytory_attachment_save_result = true;
}

$mytory_attachment_apply_post_type = get_option('mytory-attachment-apply-post-type');
$applys = explode(',', $mytory_attachment_apply_post_type);
$post_checked = '';
$page_checked = '';
if(in_array('post', $applys)){
    $post_checked = ' checked ';
}
if(in_array('page', $applys)){
    $page_checked = ' checked ';
}
foreach ($applys as $key => $value) {
    if($value == 'post' || $value == 'page'){
        unset($applys[$key]);
    }
}
?>
<div id="icon-options-general" class="icon32"><br></div>
<h2>Mytory Admin Attachment 세팅</h2>
<?
if(isset($mytory_attachment_save_result) AND $mytory_attachment_save_result == true){
    ?>
    <div id="setting-error-settings_updated" class="updated settings-error">
        <p><strong>설정을 저장했습니다.</strong></p>
    </div>
    <?
}
?>
<form method="post">
    <table class="form-table">
        <tr>
            <th>적용할 post_type</th>
            <td>
                <label>
                    <input <?=$post_checked?> type="checkbox" name="mytory_attachment_apply_post_type[]" value="post"/>
                    Post
                </label>
                <br>
                <label>
                    <input <?=$page_checked?> type="checkbox" name="mytory_attachment_apply_post_type[]" value="page"/>
                    Page
                </label>
            </td>
        </tr>
        <tr>
            <th>적용할 custom post type</th>
            <td>
                <input class="widefat" type="text" name="mytory_attachment_apply_post_type[]" value="<?=implode(',', $applys)?>"/>
                <br>
                여러 개인 경우 쉼표로 구분.
            </td>
        </tr>
    </table>
    <p>
        <input type="submit" value="저장" class="button button-primary"/>
    </p>
</form>