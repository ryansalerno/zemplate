	<footer class="main">
		<div class="content-width">
			<?php
				$footer_menu_args = array(
					'theme_location'  => 'footer-legal',
					'container'       => 'nav',
					'container_class' => 'footer',
					'menu_class'      => 'menu margins-off',
					'fallback_cb'     => false,
				);
				wp_nav_menu($footer_menu_args);
			?>
		</div>
	</footer>

	<?php wp_footer(); ?>

</body>
</html>
