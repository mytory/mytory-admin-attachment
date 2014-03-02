<?php
/*
Plugin Name: Mytory Admin Attachment
Description: Add attachment box to edit page.
Author: Ahn, Hyoung-woo
Version: 1.0
Author URI: http://mytory.net
*/
/**
 * 플러그인 활성화할 때 post와 page에 이걸 적용하라고 시키기.
 */
function mytory_attachment_activate() {
    $apply_post_type = get_option('mytory-attachment-apply-post-type');
    if( ! $apply_post_type){
        add_option('mytory-attachment-apply-post-type', 'post,page');
    }
}
register_activation_hook( __FILE__, 'mytory_attachment_activate' );

//===== 설정 페이지 =====
add_action('admin_menu', 'mytory_attachment_menu');
function mytory_attachment_menu(){
    add_options_page( 'Mytory Admin Attachment', 'Mytory Admin Attachment', 'add_users', 'mytory-attachment', 'mytory_attachment_setting');
}

function mytory_attachment_setting(){
    include dirname(__FILE__) . '/page-setting.php';
}
//===== 플러그인용 js, css 로드 =====
/**
 * 플러그인용 js, css 로드하는 함수
 * @param $hook
 */
function mytory_attachment_enqueue() {
    wp_enqueue_script( 'mytory-attachment-script', plugins_url('/mytory-attachment.js', __FILE__) );
    //  wp_enqueue_style( 'mytory-attachment-css' );
}
add_action( 'admin_enqueue_scripts', 'mytory_attachment_enqueue' );

//===== 글쓰기 페이지에서 첨부파일 박스 =====
add_action( 'add_meta_boxes', 'register_mytory_attachment_metabox' );
add_action( 'save_post', 'mytory_attachment_save' );

/**
 * 첨부파일 박스를 등록
 */
function register_mytory_attachment_metabox() {
    $screens_string = get_option('mytory-attachment-apply-post-type');
    $screens = explode(',', $screens_string);
    foreach ($screens as $screen) {
        add_meta_box(
            'mytory-attachment',
            '첨부파일',
            'mytory_attachment_metabox_inner',
            $screen
        );
    }
}

/**
 * @param $post
 * 첨부파일 입력 박스 출력
 */
function mytory_attachment_metabox_inner( $post ) {
    wp_nonce_field( plugin_basename( __FILE__ ), 'mytory_attchment_nonce' );
    ?>
    <p><input type="button" value="첨부파일 추가" class="mytory-attachment-add-file-field button"/></p>
    <?
    $args = array(
        'post_type' => 'attachment',
        'numberposts' => null,
        'post_status' => null,
        'post_parent' => $post->ID
    );
    $attachments = get_posts($args);
    foreach ($attachments as $attachment) {
        $id = $attachment->ID;
        ?>
        <p>
            <input type="checkbox" name="delete_mytory_attachment[]"
                   id="delete_mytory_attachment_<?=$id?>"
                   value="<?=$id?>"/>
            <label for="delete_mytory_attachment_<?=$id?>">삭제</label>
            |
            기존 파일 :
            <a href="<?=wp_get_attachment_url($id)?>"><?=$attachment->post_title?></a>
            |
            <label for="mytory_attachment_change<?=$id?>">파일 교체 : </label>
            <input type="file" name="mytory_attachment_change[]" id="mytory_attachment_change_<?=$id?>"/>
            <input type="hidden" name="mytory_attchment_change_original_ids[]" value="<?=$id?>"/>
        </p>
    <?
    }
    ?>
    <script type="mytory_attachment_template" id="mytory_attachment_template">
        <p class="mytory-attachment-one-field">
            <label>파일 업로드 :
                <input type="file" name="mytory_attachment[]"/>
            </label>
            <input type="button" value="제거" class="button mytory-attachment-remove-field"/>
        </p>
    </script>
    <div class="add-form-standard-line"></div>
<?
}

/**
 * 첨부파일 세이브 함수
 * @param $post_id
 */
function mytory_attachment_save( $post_id ) {
    header('Content-Type: text/html; charset=utf-8');

    if (isset($_POST['post_type']) && 'page' == $_POST['post_type'] ) {
        if ( ! current_user_can( 'edit_page', $post_id ) )
            return;
    } else {
        if ( ! current_user_can( 'edit_post', $post_id ) )
            return;
    }

    if ( ! isset( $_POST['mytory_attchment_nonce'] ) || ! wp_verify_nonce( $_POST['mytory_attchment_nonce'], plugin_basename( __FILE__ ) ) ){
        return;
    }

//    echo var_dump(empty($_FILES['mytory_attachment']['name'][0]));
//    echo var_dump(empty($_FILES['mytory_attachment_change']['name'][0]));
//    printr2($_FILES);

    // 첨부파일 등록
    if( ! empty($_FILES['mytory_attachment']['name'][0])){
        mytory_attachment_insert($_FILES['mytory_attachment'], $post_id);
    }

    // 첨부파일 교체
    if( ! empty($_FILES['mytory_attachment_change']['name'][0])){
        mytory_attachment_change($_FILES['mytory_attachment_change'], $post_id, $_POST['mytory_attchment_change_original_ids']);
    }

    // 첨부파일 삭제
    if(isset($_POST['delete_mytory_attachment'])){
        foreach ($_POST['delete_mytory_attachment'] as $attach_id) {
            wp_delete_attachment($attach_id);
        }
    }
}

//===== 각종 함수 =====

function mytory_attachment_insert($files, $post_id){
    $post_id = wp_get_post_parent_id($post_id);
    $wp_upload_dir = wp_upload_dir();
    foreach ($files['tmp_name'] as $key => $tmp_name) {
        if(empty($tmp_name)){
            continue;
        }
        $original_name = $files['name'][$key];
        $filedata = array(
            'name' => $original_name,
            'type' => $files['type'][$key],
            'tmp_name' => $tmp_name,
            'error' => $files['error'][$key],
            'size' => $files['size'][$key],
        );
        $첨부파일정보 = wp_handle_upload($filedata, array('test_form' => FALSE ));

        $attachment = array(
            'guid' => $첨부파일정보['url'],
            'post_mime_type' => $첨부파일정보['type'],
            'post_title' => $original_name,
            'post_content' => '',
            'post_status' => 'inherit',
        );

        $attach_id = wp_insert_attachment( $attachment, $첨부파일정보['file'], $post_id);

        if(strstr($filedata['type'], 'image')){
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            $attach_data = wp_generate_attachment_metadata( $attach_id, $첨부파일정보['file'] );
            wp_update_attachment_metadata( $attach_id, $attach_data );
        }
//        add_post_meta($post_id, 'mytory_admin_attachment_id', $attach_id);
    }
}

/**
 * 첨부파일 교체 함수
 * @param $change_files $_FILES['mytory_attachment_change']
 * @param $change_attach_ids $_POST['mytory_attchment_change_original_ids']
 */
function mytory_attachment_change($change_files, $post_id, $change_attach_ids){
    $wp_upload_dir = wp_upload_dir();
    foreach ($change_files['tmp_name'] as $key => $tmp_name) {
        if(empty($tmp_name)){
            continue;
        }

        $original_name = $change_files['name'][$key];
        $filedata = array(
            'name' => $original_name,
            'type' => $change_files['type'][$key],
            'tmp_name' => $tmp_name,
            'error' => $change_files['error'][$key],
            'size' => $change_files['size'][$key],
        );
        $첨부파일정보 = wp_handle_upload($filedata, array('test_form' => FALSE ));

        $attachment = array(
            'ID' => $change_attach_ids[$key],
            'guid' => $wp_upload_dir['baseurl'] . _wp_relative_upload_path( $첨부파일정보['file'] ),
            'post_mime_type' => $첨부파일정보['type'],
            'post_title' => $original_name,
            'post_content' => '',
            'post_status' => 'inherit',
        );

        $attach_id = wp_insert_attachment( $attachment, $첨부파일정보['file'], $post_id);

        if(strstr($filedata['type'], 'image')){
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            $attach_data = wp_generate_attachment_metadata( $attach_id, $첨부파일정보['file'] );
            wp_update_attachment_metadata( $attach_id, $attach_data );
        }
    }
}

function mytory_attachment_list($post_id = NULL){
    global $post;
    if( ! $post_id){
        $post_id = $post->ID;
    }
    $args = array(
        'post_type' => 'attachment',
        'numberposts' => null,
        'post_status' => null,
        'post_parent' => $post_id,
    );
    $attachments = get_posts($args);
    if(count($attachments) > 0){?>
        <div class="ma-list" style="margin-bottom: 20px;">
            첨부파일 :
                <? foreach ($attachments as $attachment) { ?>
                    <span class="ma-list__span" style="margin-right: 20px"><a class="ma-list__a" href="<?=wp_get_attachment_url($attachment->ID)?>"><?=$attachment->post_title?></a></span>
                <?}?>
        </div>
    <?}?>
<?
}

function mytory_attachment_count($post_id = NULL){
    global $post;
    if( ! $post_id){
        $post_id = $post->ID;
    }
    $args = array(
        'post_type' => 'attachment',
        'numberposts' => null,
        'post_status' => null,
        'post_parent' => $post_id,
    );
    $attachments = get_posts($args);
    return count($attachments);
}
