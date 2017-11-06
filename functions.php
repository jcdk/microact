<?php
load_theme_textdomain( 'ipin', get_template_directory() . '/languages' );

if ( !function_exists( 'optionsframework_init' ) ) {
	define( 'OPTIONS_FRAMEWORK_DIRECTORY', get_template_directory_uri() . '/inc/' );
	require_once dirname( __FILE__ ) . '/inc/options-framework.php';
}

add_editor_style();

if (!isset($content_width))
	$content_width = 505;

register_nav_menus(array('top_nav' => __('Top Navigation', 'ipin')));

register_sidebar(array('name' => 'sidebar-left', 'before_widget' => '', 'after_widget' => '', 'before_title' => '<h4>', 'after_title' => '</h4>'));
register_sidebar(array('name' => 'sidebar-right', 'before_widget' => '', 'after_widget' => '', 'before_title' => '<h4>', 'after_title' => '</h4>'));

add_theme_support('automatic-feed-links');
add_theme_support('post-thumbnails');
add_theme_support('custom-background', array(
	'default-color' => 'f2f2f2',
));

function ipin_scripts() {
	wp_enqueue_script('ipin_bootstrap', get_template_directory_uri() . '/js/bootstrap.min.js', array('jquery'), null, true);
	wp_enqueue_script('ipin_custom', get_template_directory_uri() . '/js/ipin.custom.js', array('jquery'), null, true);

	if (is_singular() && comments_open() && get_option( 'thread_comments' )) {
		wp_enqueue_script('comment-reply');
	}
		
	if (!is_singular()) {
		wp_enqueue_script('ipin_masonry', get_template_directory_uri() . '/js/jquery.masonry.min.js', array('jquery'), null, false);
		wp_enqueue_script('ipin_imagesloaded', get_template_directory_uri() . '/js/jquery.imagesloaded.min.js', array('jquery'), null, false);
		wp_enqueue_script('ipin_infinitescroll', get_template_directory_uri() . '/js/jquery.infinitescroll.min.js', array('jquery'), null, false);
	}

	$translation_array = array(
		'__allitemsloaded' => __('All items loaded', 'ipin'),
		'stylesheet_directory_uri' => get_template_directory_uri()
	);
	wp_localize_script('ipin_custom', 'obj_ipin', $translation_array);
}
add_action('wp_enqueue_scripts', 'ipin_scripts');


/**
 * From Roots Theme http://rootstheme.com
 * Cleaner walker for wp_nav_menu()
 *
 * Walker_Nav_Menu (WordPress default) example output:
 *   <li id="menu-item-8" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-8"><a href="/">Home</a></li>
 *   <li id="menu-item-9" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-9"><a href="/sample-page/">Sample Page</a></l
 *
 * Roots_Nav_Walker example output:
 *   <li class="menu-home"><a href="/">Home</a></li>
 *   <li class="menu-sample-page"><a href="/sample-page/">Sample Page</a></li>
 */
function is_element_empty($element) {
  $element = trim($element);
  return empty($element) ? false : true;
}
 
class Roots_Nav_Walker extends Walker_Nav_Menu {
  function check_current($classes) {
    return preg_match('/(current[-_])|active|dropdown/', $classes);
  }

  function start_lvl(&$output, $depth = 0, $args = array()) {
    $output .= "\n<ul class=\"dropdown-menu\">\n";
  }

  function start_el(&$output, $item, $depth = 0, $args = array(), $id = 0) {
    $item_html = '';
    parent::start_el($item_html, $item, $depth, $args);

    if ($item->is_dropdown && ($depth === 0)) {
      $item_html = str_replace('<a', '<a class="dropdown-toggle" data-toggle="dropdown" data-target="#"', $item_html);
      $item_html = str_replace('</a>', ' <b class="caret"></b></a>', $item_html);
    }
    elseif (stristr($item_html, 'li class="divider')) {
      $item_html = preg_replace('/<a[^>]*>.*?<\/a>/iU', '', $item_html);
    }
    elseif (stristr($item_html, 'li class="dropdown-header')) {
      $item_html = preg_replace('/<a[^>]*>(.*)<\/a>/iU', '$1', $item_html);
    }

    $item_html = apply_filters('roots_wp_nav_menu_item', $item_html);
    $output .= $item_html;
  }

  function display_element($element, &$children_elements, $max_depth, $depth = 0, $args, &$output) {
    $element->is_dropdown = ((!empty($children_elements[$element->ID]) && (($depth + 1) < $max_depth || ($max_depth === 0))));

    if ($element->is_dropdown) {
      $element->classes[] = 'dropdown';
    }

    parent::display_element($element, $children_elements, $max_depth, $depth, $args, $output);
  }
}

/**
 * Remove the id="" on nav menu items
 * Return 'menu-slug' for nav menu classes
 */
function roots_nav_menu_css_class($classes, $item) {
  $slug = sanitize_title($item->title);
  $classes = preg_replace('/(current(-menu-|[-_]page[-_])(item|parent|ancestor))/', 'active', $classes);
  $classes = preg_replace('/^((menu|page)[-_\w+]+)+/', '', $classes);

  $classes[] = 'menu-' . $slug;

  $classes = array_unique($classes);

  return array_filter($classes, 'is_element_empty');
}
add_filter('nav_menu_css_class', 'roots_nav_menu_css_class', 10, 2);
add_filter('nav_menu_item_id', '__return_null');

/**
 * Clean up wp_nav_menu_args
 *
 * Remove the container
 * Use Roots_Nav_Walker() by default
 */
function roots_nav_menu_args($args = '') {
  $roots_nav_menu_args['container'] = false;

  if (!$args['items_wrap']) {
    $roots_nav_menu_args['items_wrap'] = '<ul class="%2$s">%3$s</ul>';
  }

  if (current_theme_supports('bootstrap-top-navbar') && !$args['depth']) {
    $roots_nav_menu_args['depth'] = 2;
  }

  if (!$args['walker']) {
    $roots_nav_menu_args['walker'] = new Roots_Nav_Walker();
  }

  return array_merge($args, $roots_nav_menu_args);
}
add_filter('wp_nav_menu_args', 'roots_nav_menu_args');


//Relative date modified from wp-includes/formatting.php
function ipin_human_time_diff( $from, $to = '' ) {
	if ( empty($to) )
		$to = time();
	$diff = (int) abs($to - $from);
	if ($diff <= 3600) {
		$mins = round($diff / 60);
		if ($mins <= 1) {
			$mins = 1;
		}

		if ($mins == 1) {
			$since = sprintf(__('%s min ago', 'ipin'), $mins);
		} else {
			$since = sprintf(__('%s mins ago', 'ipin'), $mins);
		}
	} else if (($diff <= 86400) && ($diff > 3600)) {
		$hours = round($diff / 3600);
		if ($hours <= 1) {
			$hours = 1;
		}
		
		if ($hours == 1) {
			$since = sprintf(__('%s hour ago', 'ipin'), $hours);
		} else {
			$since = sprintf(__('%s hours ago', 'ipin'), $hours);
		}
	} else if ($diff >= 86400 && $diff <= 31536000) {
		$days = round($diff / 86400);
		if ($days <= 1) {
			$days = 1;
		}

		if ($days == 1) {
			$since = sprintf(__('%s day ago', 'ipin'), $days);
		} else {
			$since = sprintf(__('%s days ago', 'ipin'), $days);
		}
	} else {
		$since = get_the_date();
	}
	return $since;
}


//Comments
function ipin_comment($comment, $args, $depth) {
	$GLOBALS['comment'] = $comment;
	?>
	<li <?php comment_class(); ?> id="comment-<?php comment_ID() ?>">

		<?php if ('1' == $show_avatars = get_option('show_avatars')) { ?>
		<div class="comment-avatar"><?php echo get_avatar(get_comment_author_email(), '32'); ?></div>
		<?php } ?>

		<div class="pull-right"><?php comment_reply_link(array('reply_text' => __('Reply', 'ipin'), 'depth' => $depth, 'max_depth'=> $args['max_depth'])) ?></div>
		
		<div class="comment-content<?php if ($show_avatars == '1') { echo ' comment-content-with-avatar'; } ?>">
			
			<strong><span <?php comment_class(); ?>><?php comment_author_link() ?></span></strong> / <?php comment_date('j M Y g:ia'); ?> <a href="#comment-<?php comment_ID() ?>" title="<?php esc_attr_e('Comment Permalink', 'ipin'); ?>">#</a> <?php edit_comment_link('e','',''); ?>
			<?php if ($comment->comment_approved == '0') : ?>
			<br /><em><?php _e('Your comment is awaiting moderation.', 'ipin'); ?></em>
			<?php endif; ?>
	
			<?php comment_text() ?>
        </div>
	<?php
}

function ipin_commentform_format($arg) {
	$commenter = wp_get_current_commenter();
	$req = get_option( 'require_name_email' );
	$aria_req = ( $req ? " aria-required='true'" : '' );
	$arg['author'] = '<div class="commentform-input pull-left"><label>' . __('Name (Required)', 'ipin') . '</label> <input class="form-control commentform-field" id="author" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '"' . $aria_req . ' /></div>';
	$arg['email'] = '<div class="commentform-input pull-left"><label>' . __('Email (Required)', 'ipin') . '</label> <input class="form-control commentform-field" id="email" name="email" type="text" value="' . esc_attr( $commenter['comment_author_email'] ) . '"' . $aria_req . ' /></div>';
	$arg['url'] = '<div class="commentform-input pull-left"><label>' . __('Website', 'ipin') . '</label> <input class="form-control commentform-field" id="url" name="url" type="text" value="' . esc_attr( $commenter['comment_author_url'] ) . '"' . $aria_req . ' /></div>';
    return $arg;
}
add_filter('comment_form_default_fields', 'ipin_commentform_format');


//Feed content for posts
function ipin_feed_content($content) {
	global $post;
	
	$imgsrc = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'medium');
	if ($imgsrc[0] != '') {
		$content_before = '<p><a href="' . get_permalink($post->ID) . '"><img src="' . $imgsrc[0] . '" alt="" /></a></p>';
	}
	
	return ($content_before . $content);
}
add_filter('the_excerpt_rss', 'ipin_feed_content');
add_filter('the_content_feed', 'ipin_feed_content');
?>