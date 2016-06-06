<?php
/*
Plugin Name: Comment Multimedia Link
Plugin URI: https://github.com/killoctal/comment-multimedia-link
Description: Plugin for WordPress for add a multimedia link in comment field. Plugin "WP YouTube Lyte" is required.
Version: 0.1.1
Author: Gabriel Schlozer
*/


function is_valid_youtube_link($url)
{
	$parsed = parse_url($url);
	return preg_match('#^(www\.)?(youtube\.com|youtu\.be)$#ei', $parsed['host']);
}

// Source: http://wpengineer.com/2214/adding-input-fields-to-the-comment-form/
add_filter('comment_form_submit_field', 'cml_add_multimedialink_field');
function cml_add_multimedialink_field($submit_field )
{
	$commenter = wp_get_current_commenter();
	$req = get_option('require_multimedialink');
    $aria_req = ($req ? ' aria-required="true"' : '');
	
	$submit_field =
		'<div class="form-group comment-form-multimedialink">'
			.'<label for="multimedialink">'.__('YouTube link').'</label>'
			.'<input type="text" id="multimedialink" name="multimedialink"'.$aria_req.' class="form-control"/>'
		.'</div>'
		.$submit_field;
		
	return $submit_field;
}


add_action( 'comment_post', 'cml_save_comment_meta_data' );
function cml_save_comment_meta_data($comment_id) {
    add_comment_meta($comment_id, 'multimedialink', $_POST['multimedialink']);
}


add_filter( 'preprocess_comment', 'cml_verify_comment_meta_data' );
function cml_verify_comment_meta_data($commentdata)
{
	$req = get_option('require_multimedialink');
    if ($req && !isset($_POST['multimedialink']))
	{
        wp_die(__('Error: please fill the required field').': '.__('YouTube link'));
	}
	else if (filter_var($_POST['multimedialink'], FILTER_VALIDATE_URL) === FALSE || !is_valid_youtube_link($_POST['multimedialink']))
	{
		wp_die(__('Error: please set a valid youtube URL.'));
	}
	
    return $commentdata;
}


add_filter( 'comment_text', 'cml_attach_multimedialink' );
function cml_attach_multimedialink($text)
{
	$multimedialink = get_comment_meta(get_comment_ID(), 'multimedialink', true);
	if ($multimedialink)
	{
		$text .= do_shortcode('[lyte id="'.esc_attr($multimedialink).'" audio="true"]');
	}
	return $text;
}
