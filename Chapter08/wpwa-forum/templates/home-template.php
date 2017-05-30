<?php get_header(); ?>

<?php echo do_shortcode('[wpwaf_forums_list]'); ?>

<?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('Home Widgets') ) : 
endif;

?>

<?php get_footer(); ?>
