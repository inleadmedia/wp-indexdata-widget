<?php
namespace WordpressIndexData;

class Plugin
{
	protected function __construct()
	{
		add_action('widgets_init', array($this, 'loadWidget'));
		if (is_admin()) {
			add_action('load-post.php', array($this, 'addMeta'));
			add_action('load-post-new.php', array($this, 'addMeta'));
		}
	}

	static public function init()
		{
			static $instance;
			if (!is_object($instance)){
				$className = get_called_class();
				$instance = new $className();
			}
			return $instance;
		}

	public function addMeta()
	{
		add_action('add_meta_boxes', array($this, 'addMetaBox'));
		add_action('save_post', array($this, 'postMetaSave'));
	}


	public function addMetaBox()
	{
		add_meta_box('indexdata', 'Search string for Musikkhylla.no', array($this, 'renderMeta'), 'post', 'side');

	}

	public function loadWidget()
	{
		register_widget('WordpressIndexData\Widget');
	}

	public function renderMeta($post)
	{
		// Add an nonce field so we can check for it later.
		wp_nonce_field('indexdata_box', 'indexdata_box_nonce');

		// Use get_post_meta to retrieve an existing value from the database.
		$value = get_post_meta($post->ID, 'indexdata_query', true);

		// Display the form, using the current value.
		echo '<label for="indexdata_query">Type in a keyword to show related items</label> ';
		echo '<input type="text" id="indexdata_query" name="indexdata_query"';
		echo ' value="' . esc_attr($value) . '" size="25" />';
	}

	/**
	 * Called when each post is saved
	 *
	 * @param $post_id
	 *
	 * @return mixed
	 */
	public function postMetaSave($post_id)
	{
		/*
		 * We need to verify this came from the our screen and with proper authorization,
		 * because save_post can be triggered at other times.
		 */

		// Check if our nonce is set.
		if ( ! isset($_POST['indexdata_box_nonce'])) {
			return $post_id;
		}

		$nonce = $_POST['indexdata_box_nonce'];

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce($nonce, 'indexdata_box')) {
			return $post_id;
		}

		// If this is an autosave, our form has not been submitted,
		//     so we don't want to do anything.
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return $post_id;
		}

		// Check the user's permissions.
		if ( ! current_user_can('edit_post', $post_id)) {
			return $post_id;
		}

		/* OK, its safe for us to save the data now. */

		// Update the meta field.
		update_post_meta($post_id, 'indexdata_query', sanitize_text_field($_POST['indexdata_query']));
	}
}
