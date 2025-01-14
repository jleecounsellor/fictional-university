<?php

// This is for single pages 

    get_header(); 
    while(have_posts()) {
        the_post(); 
        pageBanner(); ?>
        


      <div class="container container--narrow page-section">
    <?php 
        $hasParent = wp_get_post_parent_id(get_the_ID()); 
        if ($hasParent) { ?>          
            <div class="metabox metabox--position-up metabox--with-home-link">
                <p>
                <a class="metabox__blog-home-link" href="<?php echo get_permalink($hasParent); ?>"><i class="fa fa-home" aria-hidden="true"></i> 
                Back to <?php echo get_the_title($hasParent); ?></a> <span class="metabox__main"><?php the_title(); ?></span>
                </p>
            </div>
        <?php

        } 
    ?> 
      <?php  
      $isParent = get_pages(array(
        'child_of' => get_the_ID()
      )); 

      if($hasParent or $isParent) { ?>

      <div class="page-links">
        <h2 class="page-links__title"><a href="<?php echo get_permalink($hasParent); ?>"><?php echo get_the_title($hasParent); ?></a></h2>
        <ul class="min-list">
            <?php
              if ($hasParent) {
                $findChildrenOf = $hasParent;

              } else {
                $findChildrenOf = get_the_ID(); 
              }

                wp_list_pages(array(
                  'title_li' => NULL,
                  'child_of' => $findChildrenOf,
                )); 
             ?>
        </ul>
      </div>
      <?php } ?>

      <div class="generic-content">
        <?php get_search_form(); ?>
      </div>
    </div>


      
    <?php
    }
    get_footer(); 
?>