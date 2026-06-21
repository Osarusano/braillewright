<?php
/**
 * Braillewright self-update channel.
 *
 * Braillewright is given away free OUTSIDE the WordPress.org directory
 * (community-maintained by Top Tech Tidbits), so it ships its own update checker
 * — the GPL "Plugin Update Checker" library in lib/ — pointed at a self-hosted
 * JSON manifest. When a newer version is published there, every site running
 * Braillewright is offered the update through the normal WordPress update UI.
 *
 * Per the project's accessibility + security commitment, auto-updates are forced
 * ON for this theme (the auto_update_theme filter below) and a transparent notice
 * tells the site owner. This is GPL software on the owner's own server, so it is
 * not an absolute lock — a developer can change this file — but it is the default
 * for everyone who installs the theme as shipped.
 */

defined( 'ABSPATH' ) OR exit;

require_once trailingslashit( get_template_directory() ) . 'lib/plugin-update-checker/plugin-update-checker.php';

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

/*
 * Build the update checker. The manifest URL is filterable so a staging/dev site
 * can point at a test endpoint via the 'braillewright_update_manifest_url' filter
 * without editing this file.
 */
PucFactory::buildUpdateChecker(
	apply_filters(
		'braillewright_update_manifest_url',
		'https://toptechtidbits.com/wp-content/uploads/braillewright/details.json'
	),
	__FILE__,        // Any file in the theme dir; PUC detects the theme + reads style.css.
	'braillewright'
);

/*
 * Force automatic updates ON for Braillewright, overriding the per-theme toggle,
 * so accessibility + security fixes reach every site without manual action.
 */
function braillewright_force_auto_update( $update, $item ) {
	$slug = '';
	if ( isset( $item->theme ) ) {
		$slug = $item->theme;
	} elseif ( isset( $item->slug ) ) {
		$slug = $item->slug;
	}
	return ( 'braillewright' === $slug ) ? true : $update;
}
add_filter( 'auto_update_theme', 'braillewright_force_auto_update', 10, 2 );

/*
 * Transparent notice: WordPress does not reflect a forced auto-update in the UI,
 * so tell the site owner about the policy on theme-related admin screens.
 */
function braillewright_auto_update_notice() {
	if ( ! function_exists( 'get_current_screen' ) ) {
		return;
	}
	$screen = get_current_screen();
	if ( ! $screen || ! in_array( $screen->id, array( 'themes', 'dashboard', 'update-core' ), true ) ) {
		return;
	}
	echo '<div class="notice notice-info"><p>'
		. esc_html__( 'This theme keeps itself updated for your security and ongoing accessibility.', 'braillewright' )
		. '</p></div>';
}
add_action( 'admin_notices', 'braillewright_auto_update_notice' );
