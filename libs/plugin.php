<?php
namespace WordpressIndexData;

class Plugin
{
	protected function __construct()
	{
		add_action('widgets_init', array($this, 'loadWidget'));
		if (is_admin()) {
			add_action('wp_ajax_indexdata_get_artists', array($this, 'getArtists'));
			add_action('load-post.php', array($this, 'addMeta'));
			add_action('load-post-new.php', array($this, 'addMeta'));
		}
		else
		{
			add_action('dynamic_sidebar_after', array($this, 'showArtistsBlock'));
		}
	}

	static public function init()
	{
		static $instance;
		if ( ! is_object($instance)) {
			$className = get_called_class();
			$instance  = new $className();
		}
		return $instance;
	}

	public function addMeta()
	{
		wp_enqueue_script('jquery');
		wp_enqueue_script('suggest');

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

		// Use get_post_meta to retrieve an existing value from the database.
		$value = get_post_meta($post->ID, 'indexdata_artist', true);

		// Display the form, using the current value.
		echo '<br/><br/>';
		echo '<label for="indexdata_artist">Artist</label> ';
		echo '<input type="text" id="indexdata_artist" name="indexdata_artist"';
		echo ' value="' . esc_attr($value) . '" size="25" />';
		echo "<script type=\"text/javascript\">
				jQuery(function ($) {
					$('#indexdata_artist').suggest(ajaxurl+'?action=indexdata_get_artists');
				});
		</script>";
	}

	public function getArtists()
	{
		global $wpdb;

		$query = isset($_GET['q']) ? $_GET['q'] : '';

		$result = $wpdb->get_results( "SELECT * FROM $wpdb->postmeta WHERE meta_key = 'indexdata_artist' AND meta_value LIKE '$query%' GROUP BY meta_value", ARRAY_A );
		foreach($result as $row)
		{
			echo $row['meta_value']."\n\r";
		}
		die;
	}

	public function showArtistsBlock()
	{
		static $artistsBlockStatus;
		if ($artistsBlockStatus)
		{
			return;
		}
		if ( ! is_single() || ! is_object($post = $GLOBALS['post']) || ! ($indexDataValue = get_post_meta($post->ID,
				'indexdata_artist', true))
		) {
			return;
		}
		echo '<div class="widget"><h4><span>Artist treff @ Musikkhylla.no</span></h4><div class="mkws-indexdata-artist-block" autosearch="au=' . $indexDataValue . '"></div><div class="mkws-indexdata-artist-block-more"><a href="http://musikkhylla.no/search/meta/' . $indexDataValue . '?query=au%3D' . $indexDataValue . '" target="_blank">Klikk her for flere treff</a></div></div>';
		$artistsBlockStatus = 1;
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
		update_post_meta($post_id, 'indexdata_artist', sanitize_text_field($_POST['indexdata_artist']));
	}
}
