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
    
    register_taxonomy('DocumentCategories', 'document', array(
        'labels' => array(
            'label' => 'Categories',
            'labels' => array(
                'singular_name' => 'Categories'
            ),
            'public' => true
            
        )
    ));
}


function format_content($content) {
  global $post;
  
  if ($post->post_type != 'document')
    return $content;
  
  return $content;
}