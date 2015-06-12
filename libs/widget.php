<?php
namespace WordpressIndexData;

use WP_Widget;

// Creating the widget
class Widget extends \WP_Widget
{
	static $instance = false;
	protected $_fields = array(
		'title'           => array(
			'name'    => 'Title',
			'default' => 'New title'
		),
		'main_script_src' => array(
			'name'    => 'Url to complete.js',
			'default' => '//mkws.indexdata.com/mkws-complete.js'
		),
		'init_script_src' => array(
			'name'    => 'URL to widget.js',
			'default' => '//example.indexdata.com/mkws-widget-wimp.js'
		),
		'styling_css'     => array(
			'name'    => 'Styling CSS-file path',
			'default' => ''
		),
	);

	public function __construct()
	{
		$this->_fields['styling_css']['default'] = INDEXDATA_PLUGIN_URI . 'assets/style.css';
		$this->_fields['init_script_src']['default'] = INDEXDATA_PLUGIN_URI . 'assets/mkws-widget-wimp.js';

		parent::__construct(
			'indexdata_widget',
			__('IndexData Widget', INDEXDATA_LANG_NAMESPACE),
			array(
				'description' => __('IndexData widget', INDEXDATA_LANG_NAMESPACE)
			)
		);
	}

	public function widget($args, $instance)
	{
		if ( ! self::$instance) {
			//Don't access widget if we're not on the post's page, or somehow there is no post, or post has no query-meta.
			if ( ! is_single() || ! is_object($post = $GLOBALS['post']) || ! ($indexDataValue = get_post_meta($post->ID,
					'indexdata_query', true))
			) {
				return;
			}
			$title = apply_filters('widget_title', $instance['title']);

			echo $args['before_widget'];
			if ( ! empty($title)) {
				echo $args['before_title'] . $title . $args['after_title'];
			}

			//@todo: include script once per page
			echo '
				<script src="' . $instance['main_script_src'] . '"></script>
				<script type="text/javascript">
					jQuery(\'.widget_indexdata_widget\').insertBefore(jQuery(\'.authorarea\').prev());
				</script>
				<script src="' . $instance['init_script_src'] . '"></script>
				<link rel="stylesheet" type="text/css" href="' . $instance['styling_css'] . '">
				<div class="mkws-wimp" id="indexdata_query_block" autosearch="' . $indexDataValue . '"></div>
				';
			echo $args['after_widget'];
			self::$instance = TRUE;
		}
	}

	public function form($instance)
	{
		foreach ($this->_fields as $fieldName => $fieldOptions) {
			$value = array_key_exists($fieldName, $instance) ? $instance[$fieldName] : $fieldOptions['default'];
			echo '<p>
					<label for="' . $this->get_field_id($fieldName) . '">' . $fieldOptions['name'] . '</label>
					<input class="widefat" id="' . $this->get_field_id($fieldName) . '" name="' . $this->get_field_name($fieldName) . '" type="text" value="' . esc_attr($value) . '"/>
				</p>';
		}
	}

	public function update($new_instance, $old_instance)
	{
		$instance = $old_instance;
		foreach ($this->_fields as $fieldName => $fieldOptions) {
			$instance[$fieldName] = ! empty($new_instance[$fieldName]) ? strip_tags($new_instance[$fieldName]) : $fieldOptions['default'];
		}
		return $instance;
	}
}