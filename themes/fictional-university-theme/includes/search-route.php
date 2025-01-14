<?php 

    function universityRegisterSearch() {
        // will give us this route: wp-json/university/v1/search
        register_rest_route('university/v1', 'search', array(
            //another way of saying GET that is better for all browsers
            'methods' => WP_REST_SERVER::READABLE,
            //function below that returns what we'll query from the db
            'callback' => 'universitySearchResults'
        ));
    }

    add_action('rest_api_init', 'universityRegisterSearch'); 

    //WP will take care of converting this syntax from php to JSON for us - so we can write associative arrays, etc. 
    function universitySearchResults($data) {
        //grab the post type, and allow you to search terms like "meowsalot" 
        //sanitize data to secure from malicious users - so they can't insert their own code
        $mainQuery = new WP_Query(array(
            'post_type' => array('post', 'page', 'professor', 'program', 'campus', 'event'),
            's' => sanitize_text_field($data['term'])
        )); 
        //if we wanted to return an array of object posts: return $professors->posts; 
        //We want to grab from the posts, because we only need specific data from them 
        $results = array(
            'generalInfo' => array(),
            'professors' => array(),
            'programs' => array(),
            'events' => array(),
            'campuses' => array()
        );

        //this is where you're building out the array for each post
        while($mainQuery->have_posts()) {
            $mainQuery->the_post(); 
            
            if(get_post_type() == 'post' OR get_post_type() == 'page'){
                array_push($results['generalInfo'], array(
                    'title' => get_the_title(),
                    'permalink' => get_the_permalink(),
                    'postType' => get_post_type(),
                    'authorName' => get_the_author()
                ));
            }
            //zero means get the current posts thumbnail
            if(get_post_type() == 'professor'){
                array_push($results['professors'], array(
                    'title' => get_the_title(),
                    'permalink' => get_the_permalink(),
                    'image' => get_the_post_thumbnail_url(0, 'professorLandscape')
                ));
            }
            if(get_post_type() == 'program'){
                //retreive the custom field array tied to this post type to link campuses to programs
                //always remember which direction info is flowing - program post types are mapped to related campuses, not other way around
                $relatedCampuses = get_field('related_campus'); 

                if($relatedCampuses) {
                    foreach($relatedCampuses as $campus){
                        array_push($results['campuses'], array(
                            'title' => get_the_title($campus),
                            'permalink' => get_the_permalink($campus)
                        ));
                    }
                }

                array_push($results['programs'], array(
                    'title' => get_the_title(),
                    'permalink' => get_the_permalink(),
                    'id' => get_the_id()
                ));
            }
            if(get_post_type() == 'event'){
                $eventDate = new DateTime(get_field('event_date'));
                $description = null; 
                if (has_excerpt()){
                    $description = get_the_excerpt(); 
                } else {
                    $description = wp_trim_words(get_the_content(), 18);
                }

                array_push($results['events'], array(
                    'title' => get_the_title(),
                    'permalink' => get_the_permalink(),
                    'month' => $eventDate->format('M'),
                    'day' => $eventDate->format('d'),
                    'description' => $description
                ));
            }
            if(get_post_type() == 'campus'){
                array_push($results['campuses'], array(
                    'title' => get_the_title(),
                    'permalink' => get_the_permalink()
                ));
            }

        }

        if ($results['programs']){
            $programsMetaQuery = array('relation' => 'OR');

            foreach($results['programs'] as $item) {
                array_push($programsMetaQuery, array(
                    'key' => 'related_programs',
                    'compare' => 'LIKE',
                    'value' => '"' . $item['id'] . '"' 
                ));
            }

            //search for related professors or events (Custom field) that are linked to programs
            $programRelationshipQuery = new WP_Query(array(
                'post_type' => array('professor', 'event'),
                'meta_query' => $programsMetaQuery
            ));

            while($programRelationshipQuery->have_posts()) {
                $programRelationshipQuery->the_post(); 
    
                if(get_post_type() == 'professor'){
                    array_push($results['professors'], array(
                        'title' => get_the_title(),
                        'permalink' => get_the_permalink(),
                        'image' => get_the_post_thumbnail_url(0, 'professorLandscape')
                    ));
                }
                if(get_post_type() == 'event'){
                    $eventDate = new DateTime(get_field('event_date'));
                    $description = null; 
                    if (has_excerpt()){
                        $description = get_the_excerpt(); 
                    } else {
                        $description = wp_trim_words(get_the_content(), 18);
                    }
    
                    array_push($results['events'], array(
                        'title' => get_the_title(),
                        'permalink' => get_the_permalink(),
                        'month' => $eventDate->format('M'),
                        'day' => $eventDate->format('d'),
                        'description' => $description
                    ));
                }
            }
    
            //remove duplicates since we're running multiple queries for this professors array
            //This added a value of 0 and 2 to the professors, we removed by using array_values
            $results['professors'] = array_values(array_unique($results['professors'], SORT_REGULAR));
            $results['events'] = array_values(array_unique($results['events'], SORT_REGULAR));
        }

        return $results; 
    }

?>