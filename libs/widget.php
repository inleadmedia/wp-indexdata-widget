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
      'name'    => 'URL to complete.js',
      'default' => '//mkws.indexdata.com/mkws-complete.js'
    ),
    'init_script_src' => array(
      'name'    => 'URL to widget.js',
      'default' => '',
    ),
    'styling_css'     => array(
      'name'    => 'Styling CSS-file path',
      'default' => ''
    ),
    'type'      => array(
      'name'    => 'Search by author',
      'default' => ''
    ),
  );

  public function __construct()
  {
    $this->_fields['styling_css']['default'] = 'http://storage.easyting.dk/no-blogs/style.css';
    $this->_fields['init_script_src']['default'] = 'http://storage.easyting.dk/no-blogs/mkws-widget-wimp.js';

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

    // Do not access widget if we're not on the post's page,
    // or somehow there is no post, or post has no query-meta.
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
    $blogname = get_bloginfo('name');

    // @todo: include script once per page.
    echo '
      <script src="' . $instance['main_script_src'] . '"></script>
      <script src="http://storage.easyting.dk/no-blogs/' . urlencode(strtolower(str_replace(' ', '_', $blogname))) . '.js" type="text/javascript"></script>
      <script src="' . $instance['init_script_src'] . '"></script>
      <link rel="stylesheet" type="text/css" href="' . $instance['styling_css'] . '">';
    //Define when it's an author search or main search.
    if ($instance['type'] == 'on') {
      echo '<div class="mkws-indexdata-artist-block" autosearch="au=' . $indexDataValue . '"></div>
        <div class="mkws-indexdata-artist-block-more">
        <a href="http://musikkhylla.no/search/meta/' . $indexDataValue . '?query=au%3D' . $indexDataValue . '" target="_blank">Klikk her for flere treff</a></div>';

    } else {
    
      echo '<div class="fullwidth">
        <div id="indexdata_query_block" targetfilter="categories=il_releases" autosearch="' . $indexDataValue . '" class="mkwsRecords" maxRecs="5" perpage="10"></div>
      </div>
      ';
    }
    echo $args['after_widget'];
    self::$instance = TRUE;
  }

  public function form($instance)
  {
    foreach ($this->_fields as $fieldName => $fieldOptions) {
      $value = array_key_exists($fieldName, $instance) ? $instance[$fieldName] : $fieldOptions['default'];
      if ($fieldName != 'type') {
        echo '<p>
          <label for="' . $this->get_field_id($fieldName) . '">' . $fieldOptions['name'] . '</label>
          <input class="widefat" id="' . $this->get_field_id($fieldName) . '" name="' . $this->get_field_name($fieldName) . '" type="text" value="' . esc_attr($value) . '"/>
        </p>';
      } else {
        echo '<p>
          <label for="' . $this->get_field_id($fieldName) . '">' . $fieldOptions['name'] . '</label>
          <input class="widefat" id="' . $this->get_field_id($fieldName) . '" name="' . $this->get_field_name($fieldName) . '" type="checkbox" ' . ($value=='on' ? 'checked' : '') .'/>
</p>';
      }
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
