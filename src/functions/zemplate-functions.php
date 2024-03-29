<?php
//======================================================================
// Let's get this party started
//======================================================================
function zemplate_setup() {
	// clean up WP_HEAD
	remove_action( 'wp_head', 'rsd_link' ); // EditURI link
	remove_action( 'wp_head', 'wlwmanifest_link' ); // windows live writer
	remove_action( 'wp_head', 'parent_post_rel_link', 10, 0 ); // previous link
	remove_action( 'wp_head', 'start_post_rel_link', 10, 0 ); // start link
	remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0 ); // links for adjacent posts
	remove_action( 'wp_head', 'wp_generator' ); // WP version

	// emojis
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );

	// Adds RSS feed links to <head> for posts and comments.
	add_theme_support( 'automatic-feed-links' );

	add_theme_support( 'custom-logo' );
	add_theme_support( 'title-tag' );

	// Tell WP to use more semantic markup wherever it can
	add_theme_support( 'html5', array( 'comment-list', 'comment-form', 'search-form', 'gallery', 'caption' ) );

	// Default Images: add support to your custom post types as in the example
	// but you can also add them for any core post types, or types registered elsewhere
	// by pasting this line and updating the post-type first argument
	add_post_type_support( 'post', 'zen-default-image' );

	add_theme_support( 'post-thumbnails' );
	set_post_thumbnail_size( 1280, 960 ); // reasonable height, reasonable crop

	// Make some opinionated changes to the default sizes
	update_option( 'thumbnail_size_w', 240 );
	update_option( 'thumbnail_size_h', 240 );
	update_option( 'thumbnail_crop', 0 );
	update_option( 'medium_size_w', 480 );
	update_option( 'medium_size_h', 480 );
	// remove_image_size( 'medium_large' ); // this doesn't work because medium_large is fun <-true as of WP 4.8.1
	update_option( 'medium_large_size_w', 0 ); // this does work
	update_option( 'medium_large_size_h', 0 ); // this does work
	update_option( 'large_size_w', 960 );
	update_option( 'large_size_h', 960 );

	add_image_size( 'hero', 1920, 9999 ); // unlimited height, no crop
}
add_action('after_setup_theme', 'zemplate_setup');

// remove WP version from RSS
add_filter( 'the_generator', '__return_empty_string' );

//======================================================================
// Menus
//======================================================================
function zen_setup_menus(){
	register_nav_menu('utility-nav', 'Utility Nav');
	register_nav_menu('header-menu', 'Main Menu');
	register_nav_menu('footer-legal', 'Footer - Legal');
}
add_action('after_setup_theme', 'zen_setup_menus');

//======================================================================
// Custom Post Types
//======================================================================
function zen_custom_post_types(){
	foreach (glob(__DIR__.'/post-types/*.php') as $post_type){
		require_once($post_type);
	}
}
add_action('init', 'zen_custom_post_types');

//======================================================================
// SVGs
//======================================================================
// Inline an SVG if it's in our assets folder
function zen_svg_icon( $slug, $class = 'icon' ) {
	$svg = get_template_directory() . '/lib/assets/' . $slug .'.svg';
	if ( is_file( $svg ) ){
		return str_replace( '<svg', '<svg class="' . $class . '"', file_get_contents( $svg ) );
	}
}

// Have an attachment ID that could be anything, but want to inline it if it's an SVG? No problem.
function zen_inline_if_svg( $att_id, $size = 'medium', $attr = '' ){
	if ( !$att_id ) { return ''; }

	$mime = get_post_mime_type( $att_id );
	if ($mime && $mime === 'image/svg+xml'){
		// NOTE: you are using something like this: https://wordpress.org/plugins/safe-svg/ right??
		return file_get_contents( get_attached_file( $att_id ) );
	}

	return wp_get_attachment_image( $att_id, $size, false, $attr );
}

//======================================================================
// bg-srcset
//======================================================================
function zen_bg_srcset($img_id, $largest_mobile_size = 'medium', $max = 'large') {
	$src = wp_get_attachment_image_url($img_id, $largest_mobile_size);
	if (!$src){ return; }

	$atts = 'style="background-image: url('.$src.');" '.
			'data-bg-srcset="'.wp_get_attachment_image_srcset($img_id, $max).'"';

	return $atts;
}

//======================================================================
// Automagic social media icons
//======================================================================
// grab more from here: https://simpleicons.org/
function get_social_icon($url){
	$found = '';

	$known = array(
		'behance.net' => 'behance',
		'codepen.io' => 'codepen',
		'dribbble.com' => 'dribbble',
		'facebook.com' => 'facebook',
		'github.com' => 'github',
		'instagram.com' => 'instagram',
		'keybase.io' => 'keybase', // a guy can hope
		'linkedin.com' => 'linkedin',
		'pinterest.com' => 'pinterest',
		'twitter.com' => 'twitter',
		'vimeo.com' => 'vimeo',
		'youtube.com' => 'youtube',
	);

	$fallbacks = array('message-circle', 'star');

	foreach ($known as $search => $slug){
		if (stripos($url, $search) !== false){
			$found = $slug;
			break;
		}
	}

	if (!$found){
		$found = 'fallback-'.$fallbacks[array_rand($fallbacks)];
	}

	return zen_svg_icon('social-'.$found, 'big icon');
}

//======================================================================
// oEmbed additional arguments
//======================================================================
function zen_oembed_args($provider, $url, $args){
	if (strpos($url, 'vimeo.com') !== false){
		// https://developer.vimeo.com/api/oembed/videos#table-2

		// $provider = add_query_arg('portrait', 'false', $provider); // don't have one set and want to hide the little face?
		// $provider = add_query_arg('color', 'ff6c2f', $provider); // get your branding on
		$provider = add_query_arg('responsive', 'true', $provider); // do want
	}
	if (strpos($url, 'youtube.com') !== false){
		// https://developers.google.com/youtube/player_parameters#Parameters

		$provider = add_query_arg('rel', '0', $provider); // forces related videos to come from the same channel
		$provider = add_query_arg('modestbranding', '1', $provider); // sure, why not?
	}

	// this is a little bit stupid because we're expecting someone to modify the raw embed URL with these params, buuuut...

	if (strpos($url, 'autoplay=1') !== false){
		$provider = add_query_arg('autoplay', '1', $provider);
		$provider = add_query_arg('muted', '1', $provider); // not a thing on YouTube, but should be discarded safely
	}

	if (strpos($url, 'loop=1') !== false){
		$provider = add_query_arg('loop', '1', $provider);
	}

	return $provider;
}
add_filter('oembed_fetch_url', 'zen_oembed_args', 10, 3);

//======================================================================
// Revision Control
//======================================================================
// Maybe we don't keep _everything_ yeah?
function zen_store_fewer_revisions( $num, $post ) { return 3; }
add_filter( 'wp_revisions_to_keep', 'zen_store_fewer_revisions', 10, 2 );

//======================================================================
// Template check
//======================================================================
// Add a bar at the bottom of the page that shows the template being used
function show_template() {
	global $template;
	echo '<div style="position:fixed;bottom:0;left:0;background-color:rgba(255,108,47,.9);color:#fff;padding:.5em;font-size:.8em;">'.$template.'</div>';
}
if (isset($_GET['template'])){add_action('wp_footer', 'show_template');}

//======================================================================
// Performance check
//======================================================================
// Add a bar at the bottom of the page that shows resource usage
function show_perf_stats() {
	echo '<div style="position:fixed;right:0;bottom:0;background-color:rgba(255,108,47,.9);color:#fff;padding:.5em;font-size:.8em;">'.
		'<strong>Memory:</strong> '.number_format( memory_get_peak_usage() / 1024 / 1024, 2 ) . 'MB '.
		'<strong>DB Queries:</strong> '.get_num_queries().
	'</div>';
}
if (isset($_GET['perf'])){add_action('wp_footer', 'show_perf_stats');}

//======================================================================
// Pretty-print objects and arrays
//======================================================================
// Glorified var_dump for easier debugging of objects and arrays
// Pass as many comma-separated variables into this as you'd like
function zen_debug() {
	$debuggables = func_get_args();
	foreach ($debuggables as $foo){
		echo '<pre class="debug">'; var_dump($foo); echo '</pre>';
	}
}

//======================================================================
// I like ellipses more than most, but I don't love the excerpt
//======================================================================
function replace_ellipsis($content) {
	return str_replace(' [&hellip;]','&hellip;',$content);
}
add_filter('get_the_excerpt', 'replace_ellipsis');

//======================================================================
// Halt the main query in the case of an empty search
//======================================================================
// https://wordpress.stackexchange.com/a/216734
function empty_search_halt($search, \WP_Query $q){
	if (!is_admin() && empty($search) && $q->is_search() && $q->is_main_query()){
		$search .= " AND 0=1";
	}

	return $search;
}
add_filter('posts_search', 'empty_search_halt', 10, 2);

//======================================================================
// Search results sorted by post type
//======================================================================
function order_search_by_posttype( $orderby ){
	if( ! is_admin() && is_search() ){
		global $wpdb;
		$orderby = $wpdb->prefix . 'posts.post_type ASC, ' . $wpdb->prefix . 'posts.post_title ASC';
	}
	return $orderby;
}
add_filter( 'posts_orderby', 'order_search_by_posttype', 10, 1 );

//======================================================================
// Post Type labels for search results
//======================================================================
function zen_friendlier_post_type($post_type){
	$friendlier = array(
		// 'singular_name' => 'Something Friendlier',
		'Post' => 'Blog Post',
	);

	if (isset($friendlier[$post_type])){
		return $friendlier[$post_type];
	}

	return $post_type;
}

//======================================================================
// Theme Options in the Customizer
//======================================================================
function zen_theme_options($wp_customize){
	$wp_customize->add_section('zen_additional_scripts', array(
		'title'       => __('Additional Scripts', 'zemplate'),
		'description' => __('<p>Here you can paste some content to appear at the end of the <code>&lt;head&gt;</code> or <code>&lt;body&gt;</code> tags.</p><p>Usually this is for third-party scripts, like analytics or some widgety plugin (live chat, etc).</p><p><strong>Keep in mind:</strong> Whatever you paste here will be stored in the database and rendered to the page without modification. Be careful and make sure you know what you\'re doing....</p>', 'zemplate'),
		'priority'    => 300, // default is 160, "Additional CSS" is last at 200
	));

	$wp_customize->add_setting('zen_additional_scripts_head', array(
		'default'   => '',
		'type'      => 'theme_mod',
		'transport' => 'postMessage', // we're not using this, but the alternative is refreshing while typing and that's rough (especially when the change is unlikely to render)
	));
	$wp_customize->add_setting('zen_additional_scripts_body', array(
		'default'   => '',
		'type'      => 'theme_mod',
		'transport' => 'postMessage', // we're not using this, but the alternative is refreshing while typing and that's rough (especially when the change is unlikely to render)
	));

	$wp_customize->add_control('zen_additional_scripts_head', array(
		'type' => 'textarea',
		'label'      => __('Before closing HEAD', 'zemplate'),
		'section'    => 'zen_additional_scripts',
		'description' => esc_html__('This will load on every page before the content has a chance to display, so performance will take a hit. If it can go before the closing &lt;body&gt; without critically breaking, it should.'),
		'priority' => 10,
		'input_attrs' => array(
			'rows' => 12, // maybe this will be supported someday
			'placeholder' => __('<!-- Go really easy here -->'),
		),
	));
	$wp_customize->add_control('zen_additional_scripts_body', array(
		'type' => 'textarea',
		'label'      => __('Before closing BODY', 'zemplate'),
		'section'    => 'zen_additional_scripts',
		'description' => __('<p>This will still impact performance, so you shouldn\'t go wild, but at least there will be some page loaded and available before the scripts start clogging things up.</p><p>You might be looking for:</p><p><ul><li><a href="https://developers.google.com/tag-manager/quickstart">Google Tag Manager</a></li><li><a href="https://developers.google.com/analytics/devguides/collection/analyticsjs/#alternative_async_tracking_snippet">Analytics Only</a></li></ul></p>'),
		'priority' => 20,
		'input_attrs' => array(
			'rows' => 12, // wishful thinking
			'placeholder' => __('<!-- Go fairly easy here -->'),
		),
	));

	zen_default_images_register($wp_customize);
}
add_action('customize_register', 'zen_theme_options');

// Default Images
function zen_default_images_register(&$wp_customize){
	$post_types = get_post_types_by_support('zen-default-image');
	if (!$post_types){ return; }

	$wp_customize->add_section('zen_default_images', array(
		'title'       => __('Default Images', 'zemplate'),
		'description' => __('<p>Here you can define some images to act as fallbacks.</p>', 'zemplate'),
		'priority'    => 310, // default is 160, "Additional CSS" is last at 200
	));

	foreach ((array)$post_types as $post_type){
		$slug = zen_get_default_image_slug($post_type);

		$obj = get_post_type_object($post_type);
		if (!$obj || !isset($obj->labels)) { continue; }
		$name = esc_html($obj->labels->singular_name);

		$wp_customize->add_setting($slug, array(
			'default'   => '',
			'type'      => 'theme_mod',
			'transport' => 'postMessage', // we're not using this, but the alternative is refreshing while typing and that's rough (especially when the change is unlikely to render)
		));
		$wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, $slug, array(
			'mime_type' => 'image',
			'label'      => __($name . ' :: Featured Image', 'zemplate'),
			'section'    => 'zen_default_images',
			'description' => esc_html__('This image will display as the featured image for any posts that don\'t have one of their own.'),
			'priority' => 10,
		)));
	}
}

function zen_get_default_image_slug($post_type = 'post'){
	return 'zen_default_images_'.str_replace('-', '_', $post_type);
}

// try this wherever you need it: zen_get_default_image_id(get_post_type())
function zen_get_default_image_id($post_type = 'post'){
	return get_theme_mod(zen_get_default_image_slug($post_type));
}

//======================================================================
// Main Nav Walker
//======================================================================
// Add depth and sub-menu classes
function zen_nav_submenu_classes( $classes, $item ) {
	if ($item->menu_item_parent){
		$classes[] = 'sub-menu-item';
	} else{
		$classes[] = 'main-menu-item';
	}
	return $classes;
}
// add_filter( 'nav_menu_css_class', 'zen_nav_submenu_classes', 10, 2);

// NOTE: The above filter is light and easy, but only hits the LIs. CSS needs to be *-menu-item > a {} to target the links
// NOTE: The below walker is less light, but hits the LIs and As, so CSS can be flat and target the anchor classes, and we can remove anchors from items without links (plus it's extensible to get nuttier)

class zen_nav_submenu_maker extends Walker_Nav_Menu {
	// call this by adding:
	// 'walker'          => new zen_nav_submenu_maker(true),
	// to the wp_nav_menu() $args
	// param when you call it adds (or not) toggles (eg for mobile accordioning)
	var $toggles;
	function __construct( $toggles = false ) {
		$this->toggles = $toggles;
	}

	/**
	 * Starts the list before the elements are added.
	 *
	 * Adds classes to the unordered list sub-menus.
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int    $depth  Depth of menu item. Used for padding.
	 * @param array  $args   An array of arguments. @see wp_nav_menu()
	 */
	function start_lvl( &$output, $depth = 0, $args = array() ) {
		$indent = ( $depth > 0  ? str_repeat( "\t", $depth ) : '' ); // code indent
		$classes = array(
			'sub-menu',
			'menu-depth-' . ( $depth + 1)
		);

		$output .= "\n" . $indent . '<ul class="' . implode( ' ', $classes ) . '">' . "\n";
	}

	/**
	 * Start the element output.
	 *
	 * Adds main/sub-classes to the list items and links.
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $item   Menu item data object.
	 * @param int    $depth  Depth of menu item. Used for padding.
	 * @param array  $args   An array of arguments. @see wp_nav_menu()
	 * @param int    $id     Current item ID.
	 */
	function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
		$indent = ( $depth > 0 ? str_repeat( "\t", $depth ) : '' ); // code indent

		$needs_toggle = false;
		if ($this->toggles && $args->walker->has_children){
			$needs_toggle = true;
		}

		// Passed classes
		$classes = empty( $item->classes ) ? array() : (array) $item->classes;

		// Depth-dependent classes
		if ($depth === 0){
			$classes[] = 'main-menu-item';
		} else {
			$classes[] = 'sub-menu-item';
		}
		$classes[] = 'menu-item-depth-' . $depth;

		$class_names = esc_attr( implode( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item ) ) );

		// Open element
		$el_id = '';
		if ( isset( $item->ID ) && $item->ID ){
			$el_id = ' id="nav-menu-item-'. $item->ID . '"';
		}
		$output .= $indent . '<li'. $el_id . ' class="' . $class_names . '">';

		if ( !empty( $item->url ) ){
			// Link attributes
			$attributes  = !empty( $item->attr_title ) ? ' title="'  . esc_attr( $item->attr_title ) .'"' : '';
			$attributes .= !empty( $item->target )     ? ' target="' . esc_attr( $item->target     ) .'"' : '';
			$attributes .= !empty( $item->xfn )        ? ' rel="'    . esc_attr( $item->xfn        ) .'"' : '';
			$attributes .= ' href="'   . esc_attr( $item->url        ) .'"';
			$attributes .= ' class="menu-link ' . ( $depth > 0 ? 'sub-menu-link' : 'main-menu-link' ) . '"';

			// Build link output and pass through the proper filter
			$item_output = sprintf( '%1$s<a%2$s>%3$s%4$s%5$s</a>%6$s',
				$args->before,
				$attributes,
				$args->link_before,
				@apply_filters( 'the_title', $item->title, $item->ID ),
				$args->link_after,
				$args->after
			);
		} else {
			// Build "link" output sans anchor and pass through the proper filter
			$item_output = sprintf( '%1$s%2$s%3$s%4$s%5$s',
				$args->before,
				$args->link_before,
				@apply_filters( 'the_title', $item->title, $item->ID ),
				$args->link_after,
				$args->after
			);
		}

		if ( $needs_toggle ){
			$item_output .= '<input id="nt-'.$item->ID.'" type="checkbox" class="nav-toggler" /><label for="nt-'.$item->ID.'" class="nav-toggler" aria-label="Toggle submenu" tabindex="0"></label>';
		}

		$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
	}
}

//======================================================================
// Social Nav Walker - replace social media links with their icons
//======================================================================
// call this by adding:
// 'walker'          => new zen_nav_social_icons(),
// to the wp_nav_menu() $args
class zen_nav_social_icons extends Walker_Nav_Menu {
	function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
		$indent = ( $depth > 0 ? str_repeat( "\t", $depth ) : '' ); // code indent

		// Passed classes
		$classes = empty( $item->classes ) ? array() : (array) $item->classes;

		// Depth-dependent classes
		if ($depth === 0){
			$classes[] = 'main-menu-item';
		} else {
			$classes[] = 'sub-menu-item';
		}
		$classes[] = 'menu-item-depth-' . $depth;

		$class_names = esc_attr( implode( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item ) ) );

		// Open element
		$el_id = '';
		if ( isset( $item->ID ) && $item->ID ){
			$el_id = ' id="nav-menu-item-'. $item->ID . '"';
		}
		$output .= $indent . '<li'. $el_id . ' class="' . $class_names . '">';

		if ( !empty( $item->url ) ){
			// Link attributes
			$attributes  = !empty( $item->attr_title ) ? ' title="'  . esc_attr( $item->attr_title ) .'"' : ' title="Follow ' . get_bloginfo('name') . ' on ' . esc_attr($item->title) . '"';
			$attributes .= !empty( $item->target )     ? ' target="' . esc_attr( $item->target     ) .'"' : '';
			$attributes .= !empty( $item->xfn )        ? ' rel="'    . esc_attr( $item->xfn        ) .'"' : '';
			$attributes .= ' href="'   . esc_attr( $item->url        ) .'"';
			$attributes .= ' class="menu-link ' . ( $depth > 0 ? 'sub-menu-link' : 'main-menu-link' ) . '"';

			// Build link output and pass through the proper filter
			$item_output = sprintf( '%1$s<a%2$s>%3$s%4$s%5$s</a>%6$s',
				$args->before,
				$attributes,
				$args->link_before,
				get_social_icon($item->url),
				$args->link_after,
				$args->after
			);
		} else {
			// Build "link" output sans anchor and pass through the proper filter
			$item_output = sprintf( '%1$s%2$s%3$s%4$s%5$s',
				$args->before,
				$args->link_before,
				@apply_filters( 'the_title', $item->title, $item->ID ),
				$args->link_after,
				$args->after
			);
		}

		$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
	}
}

//======================================================================
// Footer Legal Copyright Injector
//======================================================================
// Insert some useful static bits into the legal nav
add_filter( 'wp_nav_menu_objects', 'zen_append_footer_legal', 10, 2 );
function zen_append_footer_legal( $items, $args ) {
	// only do this if it's the right nav
	// NOTE: this slug needs to match what was set above in line 57
	if ( $args->theme_location !== 'footer-legal' ) {return $items;}

	// create copyright item
	$copyright = array(
		'title'            => '&copy; '.date('Y').' '.get_bloginfo('name'),
		'menu_item_parent' => 0,
		'ID'               => '',
		'db_id'            => '',
		'url'              => '',
	);

	// create agency link
	$website = array(
		'title'            => 'Website by Zenman',
		'menu_item_parent' => 0,
		'ID'               => '',
		'db_id'            => '',
		// 'target'         => '_blank',
		'xfn'              => 'noopener',
		'url'              => 'https://www.zenman.com/',
		'classes'          => array( 'by-zenman' )
	);

	// insert them into menu items
	// array_unshift($items, (object)$website); // at beginning
	// array_unshift($items, (object)$copyright); // at beginning
	$items[] = (object)$copyright; // at end
	$items[] = (object)$website; // at end

	return $items;
}

function zen_custom_admin_footer() {
	_e( '<span id="footer-thankyou">Made with <svg id="zenman" title=":: zen ::" style="width: 1em; height: 1em; margin: 0 .125em -.125em;" viewBox="0 0 24 24" fill="#ff6c2f"><path d="M0 20.6c0 4.5 5.9 2.3 11.9 0C6 18.3 0 16.1 0 20.6m24 0c0 4.5-5.9 2.3-11.9 0 5.9-2.3 11.9-4.5 11.9 0M12 8.1c-12.2 0-1 11.2 0 12.2 1-1 12.2-12.2 0-12.2m0-7.35a2.6 3.6 0 1 0 .001 0"/></svg> by <a href="https://www.zenman.com/" target="_blank" rel="noopener">Zenman</a></span>.', 'zemplate' );
}
add_filter( 'admin_footer_text', 'zen_custom_admin_footer' );

function zen_remove_menus(){
	// remove_menu_page( 'edit-comments.php' ); // Comments
	// remove_menu_page( 'themes.php' ); // Appearance
	// remove_menu_page( 'plugins.php' ); // Plugins
	// remove_menu_page( 'tools.php' ); // Tools
	// remove_menu_page( 'edit.php?post_type=acf-field-group' ); // ACF
	// remove_menu_page( 'admin.php?page=WP-Optimize' ); // WP-Optimize

	// remove_submenu_page( 'index.php', 'update-core.php' ); // WP Updates
}
add_action( 'admin_menu', 'zen_remove_menus' );

function zen_disable_default_dashboard_widgets() {
	global $wp_meta_boxes;
	// unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_right_now']);    // Right Now Widget
	unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_activity']);        // Activity Widget
	unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_recent_comments']); // Comments Widget
	unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_incoming_links']);  // Incoming Links Widget
	unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_plugins']);         // Plugins Widget
	unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_quick_press']);       // Quick Press Widget
	unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_recent_drafts']);     // Recent Drafts Widget
	unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_primary']);           //
	unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_secondary']);         //
	// remove plugin dashboard boxes
	unset($wp_meta_boxes['dashboard']['normal']['core']['yoast_db_widget']);           // Yoast's SEO Plugin Widget
	// unset($wp_meta_boxes['dashboard']['normal']['core']['rg_forms_dashboard']);        // Gravity Forms Plugin Widget
	// unset($wp_meta_boxes['dashboard']['normal']['core']['bbp-dashboard-right-now']);   // bbPress Plugin Widget
}
add_action( 'wp_dashboard_setup', 'zen_disable_default_dashboard_widgets' );

// change the login css to be a little less default
function zen_login_css() {
	wp_enqueue_style( 'zen_login_css', get_template_directory_uri() . '/src/login.css', false );
}
add_action( 'login_enqueue_scripts', 'zen_login_css', 10 );

// change the logo link from wordpress.org to this site
function zen_login_url() {  return home_url(); }
add_filter( 'login_headerurl', 'zen_login_url' );

// change the alt text on the logo to show this site name
function zen_login_title() { return get_option( 'blogname' ); }
add_filter( 'login_headertext', 'zen_login_title' );
