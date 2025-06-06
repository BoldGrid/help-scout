<?php


if ( ! function_exists( 'sa_get_truncate' ) ) :
	/**
 * Truncate a string, strip tags and append a more link
 * @param string  $text           string to truncate
 * @param integer $excerpt_length output length
 * @param boolean $more_link      add a more link
 * @return string                  truncated string w or w/o more link
 */
	function sa_get_truncate( $text, $excerpt_length = 44, $more_link = false ) {

		$text = strip_shortcodes( $text );

		$text = apply_filters( 'the_excerpt', $text );
		$text = str_replace( ']]>', ']]&gt;', $text );
		$text = wp_strip_all_tags( $text );

		$words = explode( ' ', $text, $excerpt_length + 1 );
		if ( count( $words ) > $excerpt_length ) {
			array_pop( $words );
			$text = implode( ' ', $words );
			$text = rtrim( $text );
			$text .= '&hellip;';
		}
		if ( $more_link ) {
			$text = $text.' '.'<a href="'.$more_link.'" class="more">&nbsp;&raquo;</a>';
		}
		return apply_filters( 'sa_get_truncate', $text, $excerpt_length, $more_link );
	}
endif;

if ( ! function_exists( 'hsd_get_status_label' ) ) :
	/**
 * Return the label for a status. Used for element class.
 * @param  string $status
 * @return string
 */
	function hsd_get_status_label( $status = '' ) {
		// match up labels with bootstrap
		switch ( $status ) {
			case 'closed':
				$label = __( 'closed', 'help-scout' );
				break;
			case 'pending':
				$label = __( 'pending', 'help-scout' );
				break;
			case 'active':
			default:
				$label = __( 'active', 'help-scout' );
				break;
		}
		return $label;
	}
endif;

if ( ! function_exists( 'hsd_status_label' ) ) :
	/**
 * Return the label for a status. Used for element class.
 * @param  string $status
 * @return string
 */
	function hsd_status_label( $status = '' ) {
		echo esc_attr( hsd_get_status_label( $status ) );
	}
endif;

if ( ! function_exists( 'hsd_get_waiting_since' ) ) :
	/**
	 * Get the days since based on today
	 * @param  integer $number
	 * @return string
	 */
	function hsd_get_waiting_since( $last_updated = 0 ) {
		$days_since = hsd_get_days_ago( $last_updated );

		$waiting = '';
		if ( 0 === $days_since ) {
			$waiting = date_i18n( get_option( 'time_format' ), $last_updated );
		} elseif ( 2 > $days_since ) {
			$waiting = sprintf( __( 'yesterday', 'help-scout' ), $days_since );
		} elseif ( 7 > $days_since ) {
			$waiting = sprintf(
				// translators: 1: number of days ago
				__( '%1$s days ago', 'help-scout' ),
				$days_since
			);
		} else {
			$waiting = date_i18n( get_option( 'date_format' ), $last_updated );
		}

		return apply_filters( 'hsd_get_waiting_since', $waiting );
	}

endif;

if ( ! function_exists( 'hsd_get_days_ago' ) ) :
	/**
	 * Get the days since based on today
	 * @param  integer $number
	 * @return string
	 */
	function hsd_get_days_ago( $last_updated = 0 ) {
		$time_between_update = current_time( 'timestamp' ) - $last_updated;
		$days_since = round( $time_between_update / DAY_IN_SECONDS );
		return (int) apply_filters( 'hsd_get_days_ago', $days_since );
	}

endif;

if ( ! function_exists( 'hsd_get_status_class' ) ) :
	/**
 * Return the label for a status. Used for element class.
 * @param  string $status
 * @return string
 */
	function hsd_get_status_class( $status = '' ) {
		// match up labels with bootstrap
		switch ( $status ) {
			case 'active':
				$label = __( 'primary', 'help-scout' );
				break;
			case 'closed':
				$label = __( 'success', 'help-scout' );
				break;
			case 'pending':
				$label = __( 'warning', 'help-scout' );
				break;
			default:
				$label = __( 'default', 'help-scout' );
				break;
		}
		return $label;
	}
endif;

if ( ! function_exists( 'hsd_status_class' ) ) :
	/**
 * Return the label for a status. Used for element class.
 * @param  string $status
 * @return string
 */
	function hsd_status_class( $status = '' ) {
		echo esc_attr( hsd_get_status_class( $status ) );
	}
endif;

/////////////////////
// Developer Tools //
/////////////////////

if ( ! function_exists( 'prp' ) ) {
	/**
	 * print_r with a <pre> wrap
	 * @param array $array
	 * @return
	 */
	function prp( $array ) {
		echo '<pre style="white-space:pre-wrap;">';
		print_r( $array );
		echo '</pre>';
	}
}

if ( ! function_exists( 'pp' ) ) {
	/**
	 * more elegant way to print_r an array
	 * @return string
	 */
	function pp() {
		$msg = _v_build_message( func_get_args() );
		echo '<pre style="white-space:pre-wrap; text-align: left; ' .
			'font: normal normal 11px/1.4 menlo, monaco, monospaced; ' .
			'background: white; color: black; padding: 5px;">' . esc_html( $msg ) . '</pre>';
	}
	/**
	 * more elegant way to display a var dump
	 * @return string
	 */
	function dp() {
		$msg = _v_build_message( func_get_args(), 'var_dump' );
		echo '<pre style="white-space:pre-wrap;; text-align: left; ' .
			'font: normal normal 11px/1.4 menlo, monaco, monospaced; ' .
			'background: white; color: black; padding: 5px;">' . esc_html( $msg ) . '</pre>';
	}

	/**
	 * simple error logging function
	 * @return [type] [description]
	 */
	function ep() {
		$msg = _v_build_message( func_get_args() );
		error_log( '**: '.$msg );
	}

	/**
	 * utility for ep, pp, dp
	 * @param array $vars
	 * @param string $func function
	 * @param string $sep  seperator
	 * @return void|string
	 */
	function _v_build_message( $vars, $func = 'print_r', $sep = ', ' ) {
		$msgs = array();

		if ( ! empty( $vars ) ) {
			foreach ( $vars as $var ) {
				if ( is_bool( $var ) ) {
					$msgs[] = ( $var ? 'true' : 'false' );
				} elseif ( is_scalar( $var ) ) {
					$msgs[] = $var;
				} else {
					switch ( $func ) {
						case 'print_r':
						case 'var_export':
							$msgs[] = $func( $var, true );
						break;
						case 'var_dump':
							ob_start();
							var_dump( $var );
							$msgs[] = ob_get_clean();
						break;
					}
				}
			}
		}

		return implode( $sep, $msgs );
	}
}
