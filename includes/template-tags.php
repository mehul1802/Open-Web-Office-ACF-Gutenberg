<?php
/**
 * Custom template tags for this theme.
 *
 * @package WordPress
 * @subpackage Open_Web_Office
 * @since Open Web Office 1.0
 */

/**
 * Table of Contents:
 * Logo & Description
 * Comments
 * Post Meta
 * Menus
 * Classes
 * Archives
 * Miscellaneous
 */

/**
 * Logo & Description
 */
/**
 * Displays the site logo, either text or image.
 *
 * @param array   $args Arguments for displaying the site logo either as an image or text.
 * @param boolean $echo Echo or return the HTML.
 *
 * @return string $html Compiled HTML based on our arguments.
 */
function openweboffice_site_logo( $args = array(), $echo = true ) {
	$logo       = get_custom_logo();
	$site_title = get_bloginfo( 'name' );
	$contents   = '';
	$classname  = '';

	$defaults = array(
		'logo'        => '%1$s<span class="screen-reader-text">%2$s</span>',
		'logo_class'  => 'site-logo',
		'title'       => '<a href="%1$s">%2$s</a>',
		'title_class' => 'site-title',
		'home_wrap'   => '<h1 class="%1$s">%2$s</h1>',
		'single_wrap' => '<div class="%1$s faux-heading">%2$s</div>',
		'condition'   => ( is_front_page() || is_home() ) && ! is_page(),
	);

	$args = wp_parse_args( $args, $defaults );

	/**
	 * Filters the arguments for `openweboffice_site_logo()`.
	 *
	 * @param array  $args     Parsed arguments.
	 * @param array  $defaults Function's default arguments.
	 */
	$args = apply_filters( 'openweboffice_site_logo_args', $args, $defaults );

	if ( has_custom_logo() ) {
		$contents  = sprintf( $args['logo'], $logo, esc_html( $site_title ) );
		$classname = $args['logo_class'];
	} else {
		$contents  = sprintf( $args['title'], esc_url( get_home_url( null, '/' ) ), esc_html( $site_title ) );
		$classname = $args['title_class'];
	}

	$wrap = $args['condition'] ? 'home_wrap' : 'single_wrap';

	$html = sprintf( $args[ $wrap ], $classname, $contents );

	/**
	 * Filters the arguments for `openweboffice_site_logo()`.
	 *
	 * @param string $html      Compiled html based on our arguments.
	 * @param array  $args      Parsed arguments.
	 * @param string $classname Class name based on current view, home or single.
	 * @param string $contents  HTML for site title or logo.
	 */
	$html = apply_filters( 'openweboffice_site_logo', $html, $args, $classname, $contents );

	if ( ! $echo ) {
		return $html;
	}

	echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

}

/**
 * Displays the site description.
 *
 * @param boolean $echo Echo or return the html.
 *
 * @return string $html The HTML to display.
 */
function openweboffice_site_description( $echo = true ) {
	$description = get_bloginfo( 'description' );

	if ( ! $description ) {
		return;
	}

	$wrapper = '<div class="site-description">%s</div><!-- .site-description -->';

	$html = sprintf( $wrapper, esc_html( $description ) );

	/**
	 * Filters the html for the site description.
	 *
	 * @since Open Web Office 1.0
	 *
	 * @param string $html         The HTML to display.
	 * @param string $description  Site description via `bloginfo()`.
	 * @param string $wrapper      The format used in case you want to reuse it in a `sprintf()`.
	 */
	$html = apply_filters( 'openweboffice_site_description', $html, $description, $wrapper );

	if ( ! $echo ) {
		return $html;
	}

	echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Comments
 */
/**
 * Check if the specified comment is written by the author of the post commented on.
 *
 * @param object $comment Comment data.
 *
 * @return bool
 */
function openweboffice_is_comment_by_post_author( $comment = null ) {

	if ( is_object( $comment ) && $comment->user_id > 0 ) {

		$user = get_userdata( $comment->user_id );
		$post = get_post( $comment->comment_post_ID );

		if ( ! empty( $user ) && ! empty( $post ) ) {

			return $comment->user_id === $post->post_author;

		}
	}
	return false;

}

/**
 * Filter comment reply link to not JS scroll.
 * Filter the comment reply link to add a class indicating it should not use JS slow-scroll, as it
 * makes it scroll to the wrong position on the page.
 *
 * @param string $link Link to the top of the page.
 *
 * @return string $link Link to the top of the page.
 */
function openweboffice_filter_comment_reply_link( $link ) {

	$link = str_replace( 'class=\'', 'class=\'do-not-scroll ', $link );
	return $link;

}

add_filter( 'comment_reply_link', 'openweboffice_filter_comment_reply_link' );


/**
 * Menus
 */
/**
 * Filter Classes of wp_list_pages items to match menu items.
 * Filter the class applied to wp_list_pages() items with children to match the menu class, to simplify.
 * styling of sub levels in the fallback. Only applied if the match_menu_classes argument is set.
 *
 * @param array  $css_class CSS Class names.
 * @param string $item Comment.
 * @param int    $depth Depth of the current comment.
 * @param array  $args An array of arguments.
 * @param string $current_page Whether or not the item is the current item.
 *
 * @return array $css_class CSS Class names.
 */
function openweboffice_filter_wp_list_pages_item_classes( $css_class, $item, $depth, $args, $current_page ) {

	// Only apply to wp_list_pages() calls with match_menu_classes set to true.
	$match_menu_classes = isset( $args['match_menu_classes'] );

	if ( ! $match_menu_classes ) {
		return $css_class;
	}

	// Add current menu item class.
	if ( in_array( 'current_page_item', $css_class, true ) ) {
		$css_class[] = 'current-menu-item';
	}

	// Add menu item has children class.
	if ( in_array( 'page_item_has_children', $css_class, true ) ) {
		$css_class[] = 'menu-item-has-children';
	}

	return $css_class;

}

add_filter( 'page_css_class', 'openweboffice_filter_wp_list_pages_item_classes', 10, 5 );

/**
 * Add a Sub Nav Toggle to the Expanded Menu and Mobile Menu.
 *
 * @param stdClass $args An array of arguments.
 * @param string   $item Menu item.
 * @param int      $depth Depth of the current menu item.
 *
 * @return stdClass $args An object of wp_nav_menu() arguments.
 */
function openweboffice_add_sub_toggles_to_main_menu( $args, $item, $depth ) {

	// Add sub menu toggles to the Expanded Menu with toggles.
	if ( isset( $args->show_toggles ) && $args->show_toggles ) {

		// Wrap the menu item link contents in a div, used for positioning.
		$args->before = '<div class="ancestor-wrapper">';
		$args->after  = '';

		// Add a toggle to items with children.
		if ( in_array( 'menu-item-has-children', $item->classes, true ) ) {

			$toggle_target_string = '.menu-modal .menu-item-' . $item->ID . ' > .sub-menu';
			$toggle_duration      = openweboffice_toggle_duration();

			// Add the sub menu toggle.
			$args->after .= '<button class="toggle sub-menu-toggle fill-children-current-color" data-toggle-target="' . $toggle_target_string . '" data-toggle-type="slidetoggle" data-toggle-duration="' . absint( $toggle_duration ) . '" aria-expanded="false"><span class="screen-reader-text">' . __( 'Show sub menu', 'open-web-office-acf-gutenberg' ) . '</span>' . openweboffice_get_theme_svg( 'chevron-down' ) . '</button>';

		}

		// Close the wrapper.
		$args->after .= '</div><!-- .ancestor-wrapper -->';

		// Add sub menu icons to the primary menu without toggles.
	} elseif ( 'primary' === $args->theme_location ) {
		if ( in_array( 'menu-item-has-children', $item->classes, true ) ) {
			$args->after = '<span class="icon"></span>';
		} else {
			$args->after = '';
		}
	}

	return $args;

}

add_filter( 'nav_menu_item_args', 'openweboffice_add_sub_toggles_to_main_menu', 10, 3 );

/**
 * Display SVG icons in social links menu.
 *
 * @param  string  $item_output The menu item output.
 * @param  WP_Post $item        Menu item object.
 * @param  int     $depth       Depth of the menu.
 * @param  array   $args        wp_nav_menu() arguments.
 * @return string  $item_output The menu item output with social icon.
 */
function openweboffice_nav_menu_social_icons( $item_output, $item, $depth, $args ) {
	// Change SVG icon inside social links menu if there is supported URL.
	if ( 'social' === $args->theme_location ) {
		$svg = openweboffice_SVG_Icons::get_social_link_svg( $item->url );
		if ( empty( $svg ) ) {
			$svg = openweboffice_get_theme_svg( 'link' );
		}
		$item_output = str_replace( $args->link_after, '</span>' . $svg, $item_output );
	}

	return $item_output;
}

add_filter( 'walker_nav_menu_start_el', 'openweboffice_nav_menu_social_icons', 10, 4 );

/**
 * Classes
 */
/**
 * Add No-JS Class.
 * If we're missing JavaScript support, the HTML element will have a no-js class.
 */
function openweboffice_no_js_class() {

	?>
	<script>document.documentElement.className = document.documentElement.className.replace( 'no-js', 'js' );</script>
	<?php

}

add_action( 'wp_head', 'openweboffice_no_js_class' );

/**
 * Add conditional body classes.
 *
 * @param array $classes Classes added to the body tag.
 *
 * @return array $classes Classes added to the body tag.
 */
function openweboffice_body_classes( $classes ) {

	global $post;
	$post_type = isset( $post ) ? $post->post_type : false;

	// Check whether we're singular.
	if ( is_singular() ) {
		$classes[] = 'singular';
	}

	// Check whether the current page should have an overlay header.
	if ( is_page_template( array( 'templates/template-cover.php' ) ) ) {
		$classes[] = 'overlay-header';
	}

	// Check whether the current page has full-width content.
	if ( is_page_template( array( 'templates/template-full-width.php' ) ) ) {
		$classes[] = 'has-full-width-content';
	}

	// Check for enabled search.
	if ( true === get_theme_mod( 'enable_header_search', true ) ) {
		$classes[] = 'enable-search-modal';
	}

	// Check for post thumbnail.
	if ( is_singular() && has_post_thumbnail() ) {
		$classes[] = 'has-post-thumbnail';
	} elseif ( is_singular() ) {
		$classes[] = 'missing-post-thumbnail';
	}

	// Check whether we're in the customizer preview.
	if ( is_customize_preview() ) {
		$classes[] = 'customizer-preview';
	}

	// Check if posts have single pagination.
	if ( is_single() && ( get_next_post() || get_previous_post() ) ) {
		$classes[] = 'has-single-pagination';
	} else {
		$classes[] = 'has-no-pagination';
	}

	// Check if we're showing comments.
	if ( $post && ( ( 'post' === $post_type || comments_open() || get_comments_number() ) && ! post_password_required() ) ) {
		$classes[] = 'showing-comments';
	} else {
		$classes[] = 'not-showing-comments';
	}

	// Check if avatars are visible.
	$classes[] = get_option( 'show_avatars' ) ? 'show-avatars' : 'hide-avatars';

	// Slim page template class names (class = name - file suffix).
	if ( is_page_template() ) {
		$classes[] = basename( get_page_template_slug(), '.php' );
	}

	// Check for the elements output in the top part of the footer.
	$has_footer_menu = has_nav_menu( 'footer' );
	$has_social_menu = has_nav_menu( 'social' );
	$has_sidebar_1   = is_active_sidebar( 'sidebar-1' );
	$has_sidebar_2   = is_active_sidebar( 'sidebar-2' );

	// Add a class indicating whether those elements are output.
	if ( $has_footer_menu || $has_social_menu || $has_sidebar_1 || $has_sidebar_2 ) {
		$classes[] = 'footer-top-visible';
	} else {
		$classes[] = 'footer-top-hidden';
	}

	// Get header/footer background color.
	$header_footer_background = get_theme_mod( 'header_footer_background_color', '#ffffff' );
	$header_footer_background = strtolower( '#' . ltrim( $header_footer_background, '#' ) );

	// Get content background color.
	$background_color = get_theme_mod( 'background_color', 'f5efe0' );
	$background_color = strtolower( '#' . ltrim( $background_color, '#' ) );

	// Add extra class if main background and header/footer background are the same color.
	if ( $background_color === $header_footer_background ) {
		$classes[] = 'reduced-spacing';
	}

	return $classes;

}

add_filter( 'body_class', 'openweboffice_body_classes' );

/**
 * Archives
 */
/**
 * Filters the archive title and styles the word before the first colon.
 *
 * @param string $title Current archive title.
 *
 * @return string $title Current archive title.
 */
function openweboffice_get_the_archive_title( $title ) {

	$regex = apply_filters(
		'openweboffice_get_the_archive_title_regex',
		array(
			'pattern'     => '/(\A[^\:]+\:)/',
			'replacement' => '<span class="color-accent">$1</span>',
		)
	);

	if ( empty( $regex ) ) {

		return $title;

	}

	return preg_replace( $regex['pattern'], $regex['replacement'], $title );

}

add_filter( 'get_the_archive_title', 'openweboffice_get_the_archive_title' );

/**
 * Miscellaneous
 */
/**
 * Toggle animation duration in milliseconds.
 *
 * @return integer Duration in milliseconds
 */
function openweboffice_toggle_duration() {
	/**
	 * Filters the animation duration/speed used usually for submenu toggles.
	 *
	 * @since Open Web Office 1.0
	 *
	 * @param integer $duration Duration in milliseconds.
	 */
	$duration = apply_filters( 'openweboffice_toggle_duration', 250 );

	return $duration;
}

/**
 * Get unique ID.
 *
 * This is a PHP implementation of Underscore's uniqueId method. A static variable
 * contains an integer that is incremented with each call. This number is returned
 * with the optional prefix. As such the returned value is not universally unique,
 * but it is unique across the life of the PHP process.
 *
 * @see openweboffice_unique_id() Themes requiring WordPress 5.0.3 and greater should use this instead.
 *
 * @staticvar int $id_counter
 *
 * @param string $prefix Prefix for the returned ID.
 * @return string Unique ID.
 */
function openweboffice_unique_id( $prefix = '' ) {
	static $id_counter = 0;
	if ( function_exists( 'wp_unique_id' ) ) {
		return wp_unique_id( $prefix );
	}
	return $prefix . (string) ++$id_counter;
}
