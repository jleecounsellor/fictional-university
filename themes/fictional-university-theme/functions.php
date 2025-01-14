<?php

    //Our own REST API route for custom fields
    require get_theme_file_path('/includes/search-route.php');

    //Additional REST parameter since author isn't automatic - this will add it into the REST API
    function university_custom_rest() {
        register_rest_field('post', 'authorName', array(
            'get_callback' => function() {return get_the_author();}
        )); 
    }
    add_action('rest_api_init', 'university_custom_rest');


    //Reusable function with customizable data (args) for pageBanner
    function pageBanner($args = NULL) {
        if (!isset($args['title'])) {
            $args['title'] = get_the_title(); 
        }
        if (!isset($args['subtitle'])) {
            $args['subtitle'] = get_field('page_banner_subtitle'); 
        }
        if (!isset($args['photo'])) {
            if(get_field('page_banner_background_image') AND !is_archive() AND !is_home()) {
                $args['photo'] = get_field('page_banner_background_image') ['sizes']['pageBanner'];
            } else {
                $args['photo'] = get_theme_file_uri('/images/ocean.jpg');
            }
        }
        ?>
        <div class="page-banner">
          <div class="page-banner__bg-image" style="background-image: url(<?php echo $args['photo']; ?>;"></div>
          <div class="page-banner__content container container--narrow">
            <h1 class="page-banner__title"><?php echo $args['title']; ?></h1>
            <div class="page-banner__intro">
              <p><?php echo $args['subtitle'];  ?></p>
            </div>
          </div>
        </div>
        <?php
    }

    //External files are added here, along with the root_url that we can now use in our php templates
    function university_files() {
        wp_enqueue_script('googleMap', '//maps.googleapis.com/maps/api/js?key=AIzaSyDyCUYRn-YafH5IczpRHjINdOJ5vliMsB8', NULL, '1.0', true);

        //does the file have any dependencies? If not, use NULL in place of the array, version of script, do you want it to load at the bottom? (true)
        wp_enqueue_script('university_main_script', get_theme_file_uri('/build/index.js'), array('jquery'), '1.0', true);
        wp_enqueue_style("google-fonts", "//fonts.googleapis.com/css?family=Roboto+Condensed:300,300i,400,400i,700,700i|Roboto:100,300,400,400i,700,700i"); 
        wp_enqueue_style('font-awesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');
        wp_enqueue_style('university_main_styles', get_theme_file_uri('/build/style-index.css'));
        wp_enqueue_style('university_extra_styles', get_theme_file_uri('/build/index.css'));

        //takes three args, name of js file you need to make flexible, variable name, associative array of data you need to make available
        wp_localize_script('university_main_script', 'universityData', array(
            'root_url' => get_site_url()
        ));

    }
    //WP calls this function when it wants to, which is why we didn't add the () after the function
    add_action('wp_enqueue_scripts', 'university_files');


    //Adds new features that aren't automatic, as well as image sizes
    function university_features() {
        //Adds a title to tab for each page
        add_theme_support('title-tag'); 

        //Add thumbnails or featured images to posts
        add_theme_support('post-thumbnails');

        //Sizes for new images we want uploaded
        add_image_size('professorLandscape', 400, 260, true); 
        add_image_size('professorPortrait', 480, 650, true); 
        add_image_size('pageBanner', 1500, 350, true); 
    }
    add_action('after_setup_theme', 'university_features');


    //Adjusts the default main query, the $query var lets us update that object on the frontend of our website
    function university_adjust_queries($query) {

        if (!is_admin() AND is_post_type_archive('campus') AND $query->is_main_query()){
            $query->set('posts_per_page', -1);
        }

        if (!is_admin() AND is_post_type_archive('program') AND $query->is_main_query()){
            $query->set('orderby', 'title');
            $query->set('order', 'ASC');
            $query->set('posts_per_page', -1);
        }


        if (!is_admin() AND is_post_type_archive('event') AND $query->is_main_query()){
            $today = date('Ymd');
            $query->set('meta_key', 'event_date');
            $query->set('orderby', 'meta_value_num');
            $query->set('order', 'ASC');
            $query->set('meta_query', array(
                array(
                    'key' => 'event_date',
                    'compare' => '>=',
                    'value' =>  $today,
                    'type' => 'numeric'
                )
            ));
        }

    }
    add_action('pre_get_posts', 'university_adjust_queries');


    //API key for google maps that was applied in the custom field for campuses
    function universityMapKey($api) {
        $api['key'] = 'AIzaSyDyCUYRn-YafH5IczpRHjINdOJ5vliMsB8';
        return $api; 
    }
    add_filter('acf/fields/google_map/api', 'universityMapKey');


    //Redirect subscriber accounts out of admin and onto homepage
    function redirectSubscribers() {
        $ourCurrentUser = wp_get_current_user(); 
        //if only one role and that role is of a subscriber
        if(count($ourCurrentUser->roles) == 1 AND $ourCurrentUser->roles[0] == 'subscriber' ) {
            wp_redirect(site_url('/'));
            exit; 
        }
    }
    add_action('admin_init', 'redirectSubscribers'); 


        //Remove admin bar for subscribers
        function noSubsAdminBar() {
            $ourCurrentUser = wp_get_current_user(); 
            //if only one role and that role is of a subscriber
            if(count($ourCurrentUser->roles) == 1 AND $ourCurrentUser->roles[0] == 'subscriber' ) {
                show_admin_bar(false);
            }
        }
    add_action('wp_loaded', 'noSubsAdminBar'); 

?>