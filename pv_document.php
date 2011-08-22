<?php

/*
Plugin Name: pv_Document
Plugin URI: http://www.vdvreede.net
Description: Adds document post type to wordpress for a document library.
Version: 0.5
Author: Paul Van de Vreede
Author URI: http://www.vdvreede.net
License: GPL2
*/

add_action('init', 'pvd_register_document_type');
add_action('add_meta_boxes', 'pvd_add_document_meta_box');
add_action('save_post', 'pvd_save_document_data');

add_filter('the_content', 'pvd_format_content');
add_filter('post_mime_types', 'pvd_add_mime_type_filter');
add_filter('posts_where', 'pvd_where_add_documents' );

/**
 * Function to create the document post type and its corresponding
 * categories.
 */
function pvd_register_document_type() {

    register_post_type('pv_document', array(
        'labels' => array(
            'name' => 'Documents',
            'singular_name' => _x('Document', 'post type singular name'),
            'add_new' => _x('Add New', 'document item'),
            'add_new_item' => __('Add New Document'),
            'edit_item' => __('Edit Document'),
            'new_item' => __('New Document'),
            'view_item' => __('View Document'),
            'search_items' => __('Search Documents'),
            'not_found' => __('Nothing found'),
            'not_found_in_trash' => __('Nothing found in Trash'),
            'parent_item_colon' => ''
        ),
        'public' => true,
        'taxonomies' => array('category', 'post_tag'),
        'menu_position' => 5
    ));
}

function pvd_add_document_meta_box() {
    add_meta_box('pv_document_items', 'Add Attachment', 'render_document_meta_box', 'pv_document');
}

function pvd_render_document_meta_box($post) {

    if ($post->post_type != 'pv_document')
            return;
    
    $current_attachment = pvd_get_post_attachments($post->ID);
    
    $attachments = pvd_get_post_attachments();
    
    if (count($attachments) > 0) {   
        // Use nonce for verification
        wp_nonce_field(plugin_basename(__FILE__), 'pv_document_noncename');
    
        $output = '<select name="pv_document_attachment">';
        $output .= '<option value="0">Select an attachment to link...</option>';
        foreach ($attachments as $attachment) {
            if (count($current_attachment) < 1 || $current_attachment[0]->ID != $attachment->ID)
                $output .= '<option value="' . $attachment->ID . '">' . $attachment->post_name . '</option>';
            else
                $output .= '<option value="' . $attachment->ID . '" selected>' . $attachment->post_name . '</option>';
        }
    
        $output .= '</select>';
    } else {
        $output = '<p>There are no documents currently in the media library to attach.</p>';       
    }
    
    $output .= '<p>To add documents to the library <a href="/wp-admin/media-new.php">click here</a>.</p>';
    
    echo $output;
}

function pvd_save_document_data($post_id) {

    // verify if this is an auto save routine. 
    // If it is our form has not been submitted, so we dont want to do anything
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return;

    if (!wp_verify_nonce($_POST['pv_document_noncename'], plugin_basename(__FILE__)))
        return;

    // only operate when its a document type
    if ($_POST['post_type'] != 'pv_document')
        return;

    if (!current_user_can('edit_post', $post_id))
        return;

    $attachment_id = $_POST['pv_document_attachment'];

    // If no attachment was selected then exit and let the document save
    if ($attachment_id == '0')
        return;

    $attachment = array();
    $attachment['ID'] = $attachment_id;
    $attachment['post_parent'] = $post_id;
 
    wp_update_post($attachment);
}

function pvd_format_content($content) {
    global $post;

    if ($post->post_type != 'pv_document')
        return $content;

    // only render the download links on a single page view - not in the feed.
    if (!is_single())
        return $content;

    $attachments = pvd_get_post_attachments($post->ID);

    if (count($attachments) < 1) {
        $content .= '<p>There are no documents to download.</p>';
        return $content;
    }

    foreach ($attachments as $attachment) {
        $content .= '<p>' . $attachment->post_name . '</p>';
    }

    return $content;
}

function pvd_add_mime_type_filter($post_mime_types) {
    $post_mime_types['application/pdf'] = array('PDF', 'Manage PDF', 'PDF (%s)');
    
    $post_mime_types['application/vnd.openxmlformats-officedocument.wordprocessingml.document'] = array('Word', 'Manage Word', 'Word (%s)');
    $post_mime_types['application/msword'] = array('Word', 'Manage Word', 'Word (%s)');
    
    $post_mime_types['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'] = array('Spreadsheet', 'Manage Spreadsheet', 'Spreadsheets (%s)');
    // TODO: Add more mime types for other doc types eg, Word, spreadsheet

    return $post_mime_types;
}

function pvd_where_add_documents( $where ) {
    
    $where = str_replace("AND wp_posts.post_type = 'post'", "AND wp_posts.post_type IN ('pv_document', 'post')", $where);
    
    return $where;
}

function pvd_get_post_attachments($post_id = null) {
    $args = array(
        'post_type' => 'attachment',
        'numberposts' => -1,
        'post_status' => null
    );

    if ($post_id != null)
        $args['post_parent'] = $post_id;

    return get_posts($args);
}