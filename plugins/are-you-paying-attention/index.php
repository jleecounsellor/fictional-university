<?php
/**
 * Plugin Name:       Are You Paying Attention Quiz
 * Description:       Give you readers a multiple choice question. 
 * Version:           1.0
 * Author:            Jamie
 * Author URI:        https://counsellorj.herokuapp.com/
 */

 if(! defined('ABSPATH')) exit; //prevents from trigging code by visiting this file

 class AreYouPayingAttention {
    function __construct() {
        add_action('enqueue_block_editor_assets', array($this, 'adminAssets'));
    }

    //create plugin as a block using the test.js file - "wp-blocks' dependency used in JS file
    function adminAssets() {
        wp_enqueue_script('ournewblocktype', plugin_dir_url(__FILE__) . 'test.js', array('wp-blocks', 'wp-element')); 
    }
 }

 $areYouPayingAttention = new AreYouPayingAttention(); 

 ?>