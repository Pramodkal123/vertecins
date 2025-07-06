<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package kortez_insurance
 */

?>
	</div><!-- #content -->

	<footer id="colophon" class="site-footer">
		<div class="container">
			<?php if ( is_active_sidebar( 'footer-sidebar-1' ) || is_active_sidebar( 'footer-sidebar-2' ) || is_active_sidebar( 'footer-sidebar-3' ) ) : ?>
				<div id="footer-blocks" class="footer-column-3 clear">
					<?php
					if ( is_active_sidebar( 'footer-sidebar-1' ) ) {
						?>
						<div class="column">
							<?php dynamic_sidebar( 'footer-sidebar-1' ); ?>
						</div>
						<?php
					}
					if ( is_active_sidebar( 'footer-sidebar-2' ) ) {
						?>
						<div class="column">
							<?php dynamic_sidebar( 'footer-sidebar-2' ); ?>
						</div>
						<?php 
					}

					if ( is_active_sidebar( 'footer-sidebar-3' ) ) {
						?>
						<div class="column">
							<?php dynamic_sidebar( 'footer-sidebar-3' ); ?>
						</div>
						<?php 
					}
					?>
				</div><!-- .container -->
			<?php endif; ?>

		<?php
		$footer_copyright_text = get_theme_mod('footer_copyright_text', 'Copyright © 2024 Kortez Insurance. All Rights Reserved.');
		if ( ! empty( $footer_copyright_text ) ) { ?>
			<div class="site-info">
				<?php echo $footer_copyright_text; ?>
			</div><!-- .site-info -->
		<?php } ?>
		</div><!-- .container -->
	</footer><!-- #colophon -->
</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
