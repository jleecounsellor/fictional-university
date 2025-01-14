<?php
/*
    plugin Name: Add Sentence to Post
    Description: This will add a sentence to the end of a post
    Version: 1.0
    Author: Jamie
    Author URI: https://counsellorj.herokuapp.com/
*/

    function addToEndOfPost($content) {
        //only if you're on a single post page, and is only part of the main query
       if(is_single() && is_main_query()) {
        return $content . '<p>My name is Jamie</p>';
       } 
       return $content; 
    }
    //what you want to change, and a function
    add_filter('the_content', 'addToEndOfPost'); 

?>