<?php

function filter_convertImagesToWebp( $metadata, $attachment_id ) {

	$wp_dir = wp_upload_dir();
	$wp_upload_path = $wp_dir['basedir'];
	$org_url = $wp_upload_path  . '/' . $metadata['file'];

	convertImagesToWebp($org_url);

	foreach($metadata['sizes'] as $size){
		$size_url = $wp_upload_path . $wp_dir['subdir'] . '/' . $size['file'];
		convertImagesToWebp($size_url);
	}

    return $metadata; 
}; 
add_filter( 'wp_generate_attachment_metadata', 'filter_convertImagesToWebp', 10, 2 ); 

function action_delete_attachment( $post_id ) {

	$wp_dir = wp_upload_dir();
	$wp_upload_path = $wp_dir['basedir'];
	$path = get_attached_file($post_id);
	removeWebpImage($path);
	
	$metadata = wp_get_attachment_metadata($post_id);
	foreach($metadata['sizes'] as $size){
		$path = $wp_upload_path . $wp_dir['subdir'] . '/' . $size['file'];
		removeWebpImage($path);
	}

}; 

add_action( 'delete_attachment', 'action_delete_attachment', 50, 1 );