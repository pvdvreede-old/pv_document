<?php
/*
  Plugin Name: Document
  Plugin URI: http://todo.com
  Description: A brief description of the Plugin.
  Version: 0.5
  Author: Paul Van de Vreede
  Author URI: http://www.vdvreede.net
  License: A "Slug" license name e.g. GPL2
 */

add_action('init', 'register_document_type');
add_action('add_meta_boxes', 'add_document_meta_box');
add_action('save_post', 'save_document_data');

add_filter('the_content', 'format_content');


/**
 * Function to create the document post type and its corresponding
 * categories.
 */
function register_document_type() {

    register_post_type('document', array(
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
        'public' => true
    )
    );   
    
    register_taxonomy('doc_categories', 'document', array(
        'labels' => array(
            'label' => 'Categories',
            'labels' => array(
                'singular_name' => 'Categories'
            ),
            'public' => true
            
        )
    ));
}

function add_document_meta_box() {   
    add_meta_box('pv_document_items', 'Add Documents', 'render_document_meta_box', 'document');
}


function render_document_meta_box($post) {
  
  // TODO: This should be all attachments, possibly filtered by type
  $attachments = get_post_attachments($post->ID);
  
  // Use nonce for verification
  wp_nonce_field( plugin_basename( __FILE__ ), 'pv_document_noncename' );
  
  $output = '<select name="document_attachment">';
  foreach ($attachments as $attachment) {
      $ouput .= '<option value="'.$attachment->ID.'">'.$attachment->post_name'.</option>';
  }
  $output .= '</select>';
  
  echo $output;
}

function save_document_data($post_id) {
    
  // verify if this is an auto save routine. 
  // If it is our form has not been submitted, so we dont want to do anything
  if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
      return;  
      
  if ( !wp_verify_nonce( $_POST['pv_document_noncename'], plugin_basename( __FILE__ ) ) )
      return;
  
  // only operate when its a document type
  if ($_POST['post_type'] != 'document')
      return;
      
  if ( !current_user_can( 'edit_post', $post_id ) )
      return;
        
  $attachment_id = $_POST['document_attachment'];
  
  $attachment = array();
  $attachment['ID'] = $attachment_id;
  $attachment['parent_post'] = $post_id;
  
  wp_update_post($attachment);
}

function format_content($content) {
  global $post;
  
  if ($post->post_type != 'document')
    return $content;
  
  $attachments = get_post_attachments($post->ID);
  
  if (count($attachments) < 1) {    
      $content .= '<p>There are no documents to download.</p>';
      return $content;
  }
  
  foreach ($attachments as $attachment) {
      
    $content .= '<p>$attachment->post_name</p>';
   
  }
  
  return $content;
}


function get_post_attachments($post_id) {
  return get_posts(array(
      'post_type' => 'attachment',
      'post_parent' => $post_id,
      'numberposts' => -1
      ));    
}