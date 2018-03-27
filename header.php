<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta http-equiv="x-ua-Compatible" content="ie=edge" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />

	<?php // use this wonderful thing: https://realfavicongenerator.net/ ?>
	<link rel="apple-touch-icon" sizes="180x180" href="<?php echo get_stylesheet_directory_uri(); ?>/apple-touch-icon.png">
	<link rel="icon" type="image/png" sizes="32x32" href="<?php echo get_stylesheet_directory_uri(); ?>/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="<?php echo get_stylesheet_directory_uri(); ?>/favicon-16x16.png">
	<link rel="manifest" href="<?php echo get_stylesheet_directory_uri(); ?>/manifest.json">
	<link rel="mask-icon" href="<?php echo get_stylesheet_directory_uri(); ?>/safari-pinned-tab.svg" color="#f79122">
	<link rel="shortcut icon" href="<?php echo get_stylesheet_directory_uri(); ?>/favicon.ico">
	<meta name="msapplication-config" content="<?php echo get_stylesheet_directory_uri(); ?>/browserconfig.xml">
	<meta name="theme-color" content="#fff">

	<?php wp_head(); ?>
																				<?php
																					// if ($extra_header_scripts = cmb2_get_option( 'code-injections', 'codoorways_scripts_header' )){
																					// 	echo $extra_header_scripts;
																					// }
																				?>
</head>

<body <?php body_class(); ?>>
	<header class="main">
		<?php
			$utlity_nav = array(
				'theme_location'  => 'utilty-nav',
				'container'       => 'nav',
				'container_class' => 'utility-nav',
				'menu_class'      => 'menu margins-off',
				'depth'           => 1,
				'fallback_cb'     => false,
			);
			wp_nav_menu($utlity_nav);
		?>
		<div class="content-width">
			<?php
				$logo_content = get_bloginfo('name');
				$custom_logo = get_theme_mod( 'custom_logo' );
				if ($custom_logo){
					$logo_content = zen_inline_if_svg($custom_logo, 'large', array('alt' => esc_attr(get_bloginfo('name'))));
				}
			?>
			<a id="logo" href="<?php echo home_url(); ?>" rel="nofollow" itemscope itemtype="http://schema.org/Organization"><?php echo $logo_content; ?></a>
			<input id="mobile-nav-hamburger" class="hidden" type="checkbox" /><label class="hamburger" for="mobile-nav-hamburger"><span></span></label>
			<?php
				$nav = array(
					'theme_location'  => 'header-menu',
					'container'       => 'nav',
					'container_class' => 'header-nav',
					'menu_class'      => 'menu margins-off',
					'depth'           => 2,
					'walker'          => new zen_nav_submenu_maker(true),
					'fallback_cb'     => false,
				);
				wp_nav_menu($nav);
			?>
		</div>
	</header>
