<?php
/*
Plugin Name: Fetch SPAD
Plugin URI: https://wordpress.org/plugins/fetch-spad/
Description: This is a plugin that fetches A Spiritual Principle A Day and puts it on your site Simply add [spad] shortcode to your page. Fetch SPAD Widget can be added to your sidebar or footer as well.
Version: 1.0.0
Install: Drop this directory into the "wp-content/plugins/" directory and activate it.
*/
/* Disallow direct access to the plugin file */
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    die('Sorry, but you cannot access this page directly.');
}

require_once('admin/spad-dashboard.php');

// create admin menu settings page
add_action('admin_menu', 'spad_options_menu');
function spad_options_menu()
{
    add_options_page('Fetch SPAD Plugin Settings', 'Fetch SPAD', 'manage_options', 'spad-plugin', 'fetch_spad_plugin_page');
}

// add settings link to plugins page
function spad_add_settings_link($links)
{
    $settings_link = '<a href="options-general.php?page=spad-plugin">' . __('Settings') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}
$plugin = plugin_basename(__FILE__);
add_filter("plugin_action_links_$plugin", 'spad_add_settings_link');
add_action( 'wp_enqueue_scripts', 'spad_assets' );

function spad_assets() {
    wp_enqueue_style("spadcss", plugin_dir_url(__FILE__) . "css/spad.css", false, filemtime(plugin_dir_path(__FILE__) . "css/spad.css"), false);
}

function spad_func($atts = [])
{
    $args = shortcode_atts(
        array(
            'timezone'  =>  '',
        ),
        $atts
    );

    $spad_timezone = (!empty($args['timezone']) ? sanitize_text_field(strtolower($args['timezone'])) : get_option('spad_timezone'));
    $spad_base_url = "https://spiritualprinciplea.day";

    if (isset($spad_timezone) && !empty($spad_timezone)) {
        $spad_url = "$spad_base_url/?tz=$spad_timezone";
    } else {
        $spad_url = "$spad_base_url/";
    }

    $spad_get = wp_remote_get($spad_url);
    $spad_content_header = wp_remote_retrieve_header($spad_get, 'content-type');
    $spad_body = wp_remote_retrieve_body($spad_get);

    $content = '';
    $d1 = new DOMDocument;
    $spad = new DOMDocument;
    libxml_use_internal_errors(true);
    $d1->loadHTML(mb_convert_encoding($spad_body, 'HTML-ENTITIES', "UTF-8"));
    libxml_clear_errors();
    libxml_use_internal_errors(false);
    $xpath = new DOMXpath($d1);
    $body = $xpath->query("//*[@id='spad-container']");
    foreach ($body as $child) {
        $spad->appendChild($spad->importNode($child, true));
    }
    $content .= $spad->saveHTML();

    $content .= "<style type='text/css'>" . get_option('custom_css_spad') . "</style>";
    return $content;
}

// create [spad] shortcode
add_shortcode('spad', 'spad_func');

/** START Fetch SPAD Widget **/
// register SPAD_Widget
add_action('widgets_init', function () {
    register_widget('SPAD_Widget');
});
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
// phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps
class SPAD_Widget extends WP_Widget
{
// phpcs:enable PSR1.Classes.ClassDeclaration.MissingNamespace
// phpcs:enable Squiz.Classes.ValidClassName.NotCamelCaps
    /**
     * Sets up a new Fetch SPAD widget instance.
     *
     */
    public function __construct()
    {
        $widget_ops = array(
            'classname' => 'SPAD_widget',
            'description' => 'Displays the Spiritual Principle A Day',
        );
        parent::__construct('SPAD_widget', 'Fetch SPAD', $widget_ops);
    }

    /**
    * Outputs the content for the current Fetch SPAD widget instance.
    *
    *
    * @spad_func gets and parses the spad
    *
    * @param array $args     Display arguments including 'before_title', 'after_title',
    *                        'before_widget', and 'after_widget'.
    * @param array $instance Settings for the current Area Meetings Dropdown widget instance.
    */

    public function widget($args, $instance)
    {
        echo $args['before_widget'];
        if (! empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }
        echo spad_func($atts);
        echo $args['after_widget'];
    }

    /**
     * Outputs the settings form for the Fetch SPAD widget.
     * @param $instance
     */
    public function form($instance)
    {
        $title = ! empty($instance['title']) ? $instance['title'] : esc_html__('Title', 'text_domain');
        ?>
        <p>
        <label for="<?php echo esc_attr($this->get_field_id('title')); ?>">
        <?php esc_attr_e('Title:', 'text_domain'); ?>
        </label>

        <input
            class="widefat"
            id="<?php echo esc_attr($this->get_field_id('title')); ?>"
            name="<?php echo esc_attr($this->get_field_name('title')); ?>"
            type="text"
            value="<?php echo esc_attr($title); ?>">
        </p>
        <?php
    }

    /**
    * Handles updating settings for the current Fetch SPAD widget instance.
    *
    * @param array $new_instance New settings for this instance as input by the user via
    *                            WP_Widget::form().
    * @param array $old_instance Old settings for this instance.
    * @return array Updated settings to save.
    */
    public function update($new_instance, $old_instance)
    {
        $instance = array();
        $instance['title'] = ( ! empty($new_instance['title']) ) ? strip_tags($new_instance['title']) : '';
        return $instance;
    }
}
/** END Fetch SPAD Widget **/
?>
