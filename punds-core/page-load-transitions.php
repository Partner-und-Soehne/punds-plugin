<?php
/**
 * PSLK: Cross-Document View Transitions
 *
 * Aktiviert native Seitenübergänge (View Transitions API) für interne
 * Navigation. Kein JS, kein Router – der Browser snapshottet die alte und
 * neue Seite und animiert dazwischen. Browser ohne Support navigieren
 * klassisch (Progressive Enhancement, nichts bricht).
 *
 * WICHTIG: Die At-Rule muss auf der ausgehenden UND der eingehenden Seite
 * vorhanden sein. Ein Gating auf eine einzelne Seite funktioniert nicht.
 *
 * Support (Stand 2026): Chromium ab 126, Safari ab 18.2.
 * Firefox: nur Same-Document, Cross-Document noch nicht.
 *
 * Einbinden in der Haupt-Plugin-Datei:
 *   require_once plugin_dir_path( __FILE__ ) . 'includes/pslk-view-transitions.php';
 *
 * @package PS_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PSLK: Prüft, ob die Transitions ausgegeben werden sollen.
 *
 * Über den Filter 'pslk/view_transitions/enabled' lässt sich das Verhalten
 * beim Testen einschränken, z. B. in der functions.php oder hier direkt:
 *
 *   // Nur eingeloggte User sehen die Transition:
 *   add_filter( 'pslk/view_transitions/enabled', function ( $enabled ) {
 *       return $enabled && is_user_logged_in();
 *   } );
 *
 *   // Oder nur auf bestimmten Testseiten (min. 2 IDs, sonst wirkungslos):
 *   add_filter( 'pslk/view_transitions/enabled', function ( $enabled ) {
 *       return $enabled && is_page( array( 1234, 5678 ) );
 *   } );
 *
 * @return bool
 */
function pslk_vt_is_active() {

	// Nicht im Backend, in Feeds, REST oder AJAX.
	if ( is_admin() || is_feed() || wp_doing_ajax() ) {
		return false;
	}

	if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
		return false;
	}

	// PSLK: Im Cornerstone-Editor/Preview deaktivieren. Cornerstone rendert
	// das Frontend in einem iFrame und evaluiert teilweise doppelt – die
	// Transition würde dort nur störend flackern.
	// Hinweis: Parameternamen ggf. an die eingesetzte Cornerstone-Version
	// anpassen (DevTools > Network > Request-URL des Preview-iFrames).
	foreach ( array_keys( $_GET ) as $param ) { // phpcs:ignore WordPress.Security.NonceVerification
		if ( 0 === strpos( $param, 'cs-' ) || 0 === strpos( $param, 'cornerstone' ) ) {
			return false;
		}
	}

	/**
	 * Filtert, ob die View Transitions ausgegeben werden.
	 *
	 * @param bool $enabled Standard: true.
	 */
	return (bool) apply_filters( 'pslk/view_transitions/enabled', true );
}

/**
 * PSLK: Gibt das View-Transition-CSS inline im <head> aus.
 *
 * Inline und mit data-wpr-nocssinline, damit WP Rocket ("Remove Unused CSS"
 * bzw. "CSS-Bereitstellung optimieren") die At-Rule nicht entfernt oder zu
 * spät nachlädt. Das ist die häufigste Ursache dafür, dass die Transition
 * scheinbar gar nicht greift.
 *
 * @return void
 */
function pslk_vt_print_styles() {

	if ( ! pslk_vt_is_active() ) {
		return;
	}

	/**
	 * Filtert Timing und Versatz der Transition.
	 *
	 * @param array $config {
	 *     @type int    $out_duration Dauer des Ausblendens in ms.
	 *     @type int    $in_duration  Dauer des Einblendens in ms.
	 *     @type string $easing       CSS timing-function.
	 *     @type int    $offset       Vertikaler Versatz in px.
	 * }
	 */
	$config = apply_filters(
		'pslk/view_transitions/config',
		array(
			'out_duration' => 200,
			'in_duration'  => 260,
			'easing'       => 'cubic-bezier(.4, 0, .2, 1)',
			'offset'       => 10,
		)
	);

	$out    = absint( $config['out_duration'] );
	$in     = absint( $config['in_duration'] );
	$easing = $config['easing'];
	$offset = absint( $config['offset'] );

	?>
<style id="pslk-view-transitions" data-wpr-nocssinline>
/* PSLK: Opt-in für Cross-Document View Transitions */
@view-transition {
	navigation: auto;
}

::view-transition-old(root) {
	animation: <?php echo $out; ?>ms <?php echo esc_html( $easing ); ?> both pslk-vt-out;
}

::view-transition-new(root) {
	animation: <?php echo $in; ?>ms <?php echo esc_html( $easing ); ?> both pslk-vt-in;
}

@keyframes pslk-vt-out {
	to {
		opacity: 0;
		transform: translateY(-<?php echo $offset; ?>px);
	}
}

@keyframes pslk-vt-in {
	from {
		opacity: 0;
		transform: translateY(<?php echo $offset; ?>px);
	}
}

/* PSLK: Sticky Header / Bar aus dem Root-Snapshot herausnehmen, sonst
   wandert er mit dem Content mit. Selektor an das jeweilige Cornerstone-
   Setup anpassen und erst aktivieren, wenn der Grundeffekt sauber läuft.
   Der Name muss pro Dokument eindeutig sein.

.x-masthead {
	view-transition-name: pslk-masthead;
}
*/

/* PSLK: Barrierefreiheit – Nutzer mit reduzierter Bewegungspräferenz
   bekommen einen harten Seitenwechsel. */
@media (prefers-reduced-motion: reduce) {
	@view-transition {
		navigation: none;
	}
}
</style>
	<?php
}
add_action( 'wp_head', 'pslk_vt_print_styles', 5 );