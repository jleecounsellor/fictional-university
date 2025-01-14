<?php
/*
    plugin Name: Our First Unique Plugin
    Description: This will record stats of posts
    Version: 1.0
    Author: Jamie
    Author URI: https://counsellorj.herokuapp.com/
    Text Domain: wcpdomain
    Domain Path: /languages
*/

    class WordCountAndTimePlugin {
         // action and filters go here
        function __construct() {
            //adding to the admin settings menu, function
            add_action('admin_menu', array($this, 'adminPage'));
            //adding new lines into the DB under wp_options
            add_action('admin_init', array($this, 'settings'));
            //filtering of post with 'ifWrap' function
            add_filter('the_content', array($this, 'ifWrap'));
            //translate to language
            add_action('init', array($this, 'languages')); 
        }

        function languages() {
            load_plugin_textdomain('wcpdomain', false, dirname(plugin_basename(__FILE__)) . '/languages');
        }

        function ifWrap($content) {
            //if any of our checkboxes are checked, manipulate content on page
            if (is_main_query() AND is_single() AND (get_option('wcp_wordcount', '1') OR get_option('wcp_charcount', '1') OR get_option('wcp_readtime', '1'))) {
                //call and execute this method right now - whereas above, we are just passing a reference of the method for WP to call when needed
                return $this->createHTML($content); 
            }
            return $content; 
        }

        //this is the HTML for the plugin update in our posts
        function createHTML($content) {
            //items in Database, and default values of 'Post Statistics' and '0' if nothing is selected
            $html = '<h3>' . esc_html(get_option('wcp_headline', 'Post Statistics')) . '</h3><p>'; 

            // get word count once because both wordcount and read time will need it
            if (get_option('wcp_wordcount', '1') OR get_option('wcp_readtime', '1')){
                $wordCount = str_word_count(strip_tags($content)); 
            }

            if (get_option('wcp_wordcount', '1')) {
                //to make it translatable __()
                $html .= esc_html__('This post has', 'wcpdomain') . ' ' . $wordCount . ' ' . __('words', 'wcpdomain') . '.<br>'; 
            }
            if (get_option('wcp_charcount', '1')) {
                $html .= 'This post has ' . strlen(strip_tags($content)) . ' characters.<br>'; 
            }
            if (get_option('wcp_readtime', '1')) {
                $html .= 'This post will take about ' . round($wordCount/225) . ' minute(s) to read.<br>'; 
            }

            $html .= '</p>';  


            //either at the beginning of the post or end, depending on what was chosen
            if (get_option('wcp_location', '0') == '0') {
                return $html . $content; 
            }
            return $content . $html;
        }

        function settings(){ 
            //section name, subtitle (optional), subhead (optional), page slug (copied from below)
            add_settings_section('wcp_first_section', null, null, 'word-count-settings-page'); 

            //build HTML in field: name of setting to tie it to (copied from below), HTML label, function output HTML
            //slug (copied from below), section to add field to
            add_settings_field('wcp_location', 'Display Location', array($this, 'locationHTML'), 'word-count-settings-page', 'wcp_first_section'); 

            //store value in database: name of group, name for this setting, name in database
            register_setting('wordcountplugin', 'wcp_location', array(
                'sanitize_callback' => array($this, 'sanitizeLocation'),
                'default' => '0'
            )); 

            //second field, just copied the above two lines for first field and changed them
            add_settings_field('wcp_headline', 'Headline Text', array($this, 'headlineHTML'), 'word-count-settings-page', 'wcp_first_section'); 
            register_setting('wordcountplugin', 'wcp_headline', array(
                'sanitize_callback' => 'sanitize_text_field',
                'default' => 'Post Statistics'
            ));

            //third field
            add_settings_field('wcp_wordcount', 'Word Count', array($this, 'checkboxHTML'), 'word-count-settings-page', 'wcp_first_section', array('theName' => 'wcp_wordcount')); 
            register_setting('wordcountplugin', 'wcp_wordcount', array(
                'sanitize_callback' => 'sanitize_text_field',
                'default' => '1'
            ));

            //fourth field
            add_settings_field('wcp_charcount', 'Character Count', array($this, 'checkboxHTML'), 'word-count-settings-page', 'wcp_first_section', array('theName' => 'wcp_charcount')); 
            register_setting('wordcountplugin', 'wcp_charcount', array(
                'sanitize_callback' => 'sanitize_text_field',
                'default' => '1'
            ));

             //fifth field
            add_settings_field('wcp_readtime', 'Read Time', array($this, 'checkboxHTML'), 'word-count-settings-page', 'wcp_first_section', array('theName' => 'wcp_readtime')); 
            register_setting('wordcountplugin', 'wcp_readtime', array(
                'sanitize_callback' => 'sanitize_text_field',
                'default' => '1'
            ));

        }

        //sanitize value so the user can't add anything in that we don't want them to
        function sanitizeLocation($input) {
            if ($input != '0' AND $input != '1'){
                //name of option, slug, message displayed
                add_settings_error('wcp_location', 'wcp_location_error', 'Display location must be either beginning or end.'); 
                //return old value
                return get_option('wcp_location'); 
            }
            return $input;
        }

        //sections 3-5
        function checkboxHTML($args) { ?>
            <input type="checkbox" name="<?php echo $args['theName'] ?>" value="1" <?php checked(get_option($args['theName']), '1') ?>>
        <?php }


        //function 2 from above 
        function headlineHTML() { ?>
            <input type="text" name="wcp_headline" value="<?php echo esc_attr(get_option('wcp_headline')) ?>">
        <?php }

        //function 1 from above - checking in database (already loaded into memory) if selected, if so, keep it selected on the settings page
        function locationHTML() { ?>
            <select name="wcp_location">
                <option value="0" <?php selected(get_option('wcp_location'), '0') ?>>Beginning of post</option>
                <option value="1" <?php selected(get_option('wcp_location'), '1') ?>>End of post</option>
            </select>
        <?php }

        function adminPage() {
            //five arguments: doc title, settings menu title, user permissions, slug, function to output html content on new page
            //dynamic and translatable "Word Count" settings title
            add_options_page('Word Count Settings', __('Word Count', 'wcpdomain'), 'manage_options', 'word-count-settings-page', array($this, 'settingsHTML')); 
        }
    
        function settingsHTML() { ?>
            <div class="wrap">
                <h1>Word Count Settings</h1>
                <form action="options.php" method="POST">
                    <?php  
                        settings_fields('wordcountplugin');
                        do_settings_sections('word-count-settings-page'); 
                        submit_button(); 
                    ?>
                </form>
            </div>
        <?php }

    }

    //instantiate the class object
    $wordCountAndTimePlugin = new WordCountAndTimePlugin(); 



?>