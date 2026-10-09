<?php
/**
 * Typed meta access and sanitizers.
 *
 * @package Eurowet\Core
 */

declare(strict_types=1);

namespace Eurowet\Core\Data;

use Eurowet\Core\Reps\Voivodeships;

defined( 'ABSPATH' ) || exit;

/**
 * Typed get/set for post and term meta registered in Registry.
 *
 * Reading never throws and always returns the declared PHP type:
 * str/text/html/url/email => string, int => int, bool => bool, date => 'Y-m-d'|'' , ids => list<int>, json => array.
 */
final class Meta {

	/**
	 * Typed read according to Registry::META for the post's type.
	 *
	 * When the key is missing: $default (if not null) → Registry::defaultValue() → empty value of the type.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $key     Meta key.
	 * @param mixed  $default Default when the key is not stored.
	 * @return mixed
	 */
	public static function get( int $post_id, string $key, $default = null ) {
		$post_type = $post_id > 0 ? get_post_type( $post_id ) : false;
		if ( ! $post_type ) {
			return $default;
		}
		$type = Registry::type( $post_type, $key );
		if ( ! metadata_exists( 'post', $post_id, $key ) ) {
			if ( null !== $default ) {
				return $default;
			}
			$registered = Registry::defaultValue( $post_type, $key );
			if ( null !== $registered ) {
				return $registered;
			}
			return null === $type ? null : self::emptyValue( $type );
		}
		$raw = get_post_meta( $post_id, $key, true );
		return null === $type ? $raw : self::cast( $type, $raw );
	}

	/**
	 * Sanitizes and stores a value (deletes the key for empty strings/arrays).
	 *
	 * @param int    $post_id Post ID.
	 * @param string $key     Meta key.
	 * @param mixed  $value   Raw value.
	 */
	public static function set( int $post_id, string $key, $value ): bool {
		$post_type = get_post_type( $post_id );
		if ( ! $post_type ) {
			return false;
		}
		$clean = self::sanitize( $post_type, $key, $value );
		if ( '' === $clean || array() === $clean ) {
			delete_post_meta( $post_id, $key );
			return true;
		}
		// update_metadata() unslashes its input; slash first so backslashes in content survive.
		return false !== update_post_meta( $post_id, $key, wp_slash( $clean ) );
	}

	/**
	 * Deletes a key.
	 */
	public static function delete( int $post_id, string $key ): bool {
		return delete_post_meta( $post_id, $key );
	}

	/**
	 * Single-line string.
	 */
	public static function str( int $post_id, string $key, string $default = '' ): string {
		$value = self::raw( $post_id, $key );
		return null === $value ? $default : (string) self::cast( 'str', $value );
	}

	/**
	 * HTML string (as stored, already kses-filtered on save).
	 */
	public static function html( int $post_id, string $key, string $default = '' ): string {
		$value = self::raw( $post_id, $key );
		return null === $value ? $default : (string) self::cast( 'html', $value );
	}

	public static function int( int $post_id, string $key, int $default = 0 ): int {
		$value = self::raw( $post_id, $key );
		return null === $value ? $default : (int) self::cast( 'int', $value );
	}

	public static function bool( int $post_id, string $key, bool $default = false ): bool {
		$value = self::raw( $post_id, $key );
		return null === $value ? $default : (bool) self::cast( 'bool', $value );
	}

	/**
	 * Date as Y-m-d or '' when not set/invalid.
	 */
	public static function date( int $post_id, string $key, string $default = '' ): string {
		$value = self::raw( $post_id, $key );
		return null === $value ? $default : (string) self::cast( 'date', $value );
	}

	/**
	 * List of positive post IDs, order preserved.
	 *
	 * @return list<int>
	 */
	public static function ids( int $post_id, string $key ): array {
		$value = self::raw( $post_id, $key );
		return null === $value ? array() : self::cast( 'ids', $value );
	}

	/**
	 * Structured array.
	 *
	 * @return array<int|string, mixed>
	 */
	public static function json( int $post_id, string $key ): array {
		$value = self::raw( $post_id, $key );
		return null === $value ? array() : self::cast( 'json', $value );
	}

	/**
	 * Raw stored value or null when missing.
	 *
	 * @return mixed
	 */
	private static function raw( int $post_id, string $key ) {
		if ( $post_id <= 0 || ! metadata_exists( 'post', $post_id, $key ) ) {
			return null;
		}
		return get_post_meta( $post_id, $key, true );
	}

	/**
	 * Typed term meta read (Registry::TERM_META).
	 *
	 * @param mixed $default Default when missing.
	 * @return mixed
	 */
	public static function term( int $term_id, string $key, $default = null ) {
		$term = $term_id > 0 ? get_term( $term_id ) : null;
		if ( ! $term instanceof \WP_Term ) {
			return $default;
		}
		$type = Registry::TERM_META[ $term->taxonomy ][ $key ] ?? null;
		if ( ! metadata_exists( 'term', $term_id, $key ) ) {
			return $default ?? ( null === $type ? null : self::emptyValue( $type ) );
		}
		$raw = get_term_meta( $term_id, $key, true );
		return null === $type ? $raw : self::cast( $type, $raw );
	}

	/**
	 * Sanitizes and stores term meta (deletes on empty string).
	 *
	 * @param mixed $value Raw value.
	 */
	public static function setTerm( int $term_id, string $key, $value ): bool {
		$term = get_term( $term_id );
		if ( ! $term instanceof \WP_Term ) {
			return false;
		}
		$clean = self::sanitizeTerm( $term->taxonomy, $key, $value );
		if ( '' === $clean ) {
			delete_term_meta( $term_id, $key );
			return true;
		}
		return false !== update_term_meta( $term_id, $key, wp_slash( $clean ) );
	}

	/**
	 * Empty value of a storage type.
	 *
	 * @return mixed
	 */
	public static function emptyValue( string $type ) {
		switch ( $type ) {
			case 'int':
				return 0;
			case 'bool':
				return false;
			case 'ids':
			case 'json':
				return array();
			default:
				return '';
		}
	}

	/**
	 * Casts a stored value to the PHP type of a storage type (read side, tolerant).
	 *
	 * @param mixed $value Stored value.
	 * @return mixed
	 */
	public static function cast( string $type, $value ) {
		switch ( $type ) {
			case 'int':
				return is_numeric( $value ) ? (int) $value : 0;
			case 'bool':
				return in_array( $value, array( true, 1, '1', 'true', 'yes', 'on' ), true );
			case 'date':
				return self::sanitizeDate( $value );
			case 'ids':
				return self::sanitizeIds( $value );
			case 'json':
				if ( is_string( $value ) && '' !== $value ) {
					$decoded = json_decode( $value, true );
					return is_array( $decoded ) ? $decoded : array();
				}
				return is_array( $value ) ? $value : array();
			default:
				return is_scalar( $value ) ? (string) $value : '';
		}
	}

	/**
	 * Sanitizes a post meta value according to the registry (used as register_post_meta sanitize_callback).
	 *
	 * @param mixed $value Raw value.
	 * @return mixed Sanitized value ('' / [] mean "empty").
	 */
	public static function sanitize( string $post_type, string $key, $value ) {
		$type = Registry::type( $post_type, $key );
		if ( null === $type ) {
			return is_scalar( $value ) ? sanitize_text_field( (string) $value ) : '';
		}

		switch ( $type ) {
			case 'int':
				$int = self::sanitizeInt( $value );
				if ( isset( Registry::RANGES[ $post_type ][ $key ] ) ) {
					[ $min, $max ] = Registry::RANGES[ $post_type ][ $key ];
					$int           = max( $min, min( $max, $int ) );
				}
				return $int;
			case 'bool':
				return self::sanitizeBool( $value );
			case 'date':
				return self::sanitizeDate( $value );
			case 'ids':
				return self::sanitizeIds( $value );
			case 'json':
				return self::sanitizeShape( (string) Registry::shape( $post_type, $key ), $value );
			case 'html':
				return self::sanitizeHtml( $value );
			case 'text':
				return self::sanitizeText( $value );
			case 'url':
				return self::sanitizeUrl( $value );
			case 'email':
				return self::sanitizeEmail( $value );
			default:
				$str = self::sanitizeStr( $value );
				if ( isset( Registry::ENUMS[ $post_type ][ $key ] ) && '' !== $str && ! in_array( $str, Registry::ENUMS[ $post_type ][ $key ], true ) ) {
					$fallback = Registry::DEFAULTS[ $post_type ][ $key ] ?? '';
					return is_string( $fallback ) ? $fallback : '';
				}
				return $str;
		}
	}

	/**
	 * Sanitizes a term meta value (register_term_meta sanitize_callback).
	 *
	 * @param mixed $value Raw value.
	 * @return mixed
	 */
	public static function sanitizeTerm( string $taxonomy, string $key, $value ) {
		$type = Registry::TERM_META[ $taxonomy ][ $key ] ?? 'str';
		switch ( $type ) {
			case 'int':
				return self::sanitizeInt( $value );
			case 'html':
				return self::sanitizeHtml( $value );
			default:
				$str = self::sanitizeStr( $value );
				if ( isset( Registry::TERM_ENUMS[ $taxonomy ][ $key ] ) && '' !== $str && ! in_array( $str, Registry::TERM_ENUMS[ $taxonomy ][ $key ], true ) ) {
					return '';
				}
				if ( 'species_slug' === $key ) {
					return sanitize_title( $str );
				}
				return $str;
		}
	}

	/**
	 * @param mixed $value Raw value.
	 */
	public static function sanitizeStr( $value ): string {
		return is_scalar( $value ) ? sanitize_text_field( (string) $value ) : '';
	}

	/**
	 * Multi-line plain text.
	 *
	 * @param mixed $value Raw value.
	 */
	public static function sanitizeText( $value ): string {
		return is_scalar( $value ) ? sanitize_textarea_field( (string) $value ) : '';
	}

	/**
	 * @param mixed $value Raw value.
	 */
	public static function sanitizeHtml( $value ): string {
		return is_scalar( $value ) ? trim( wp_kses_post( (string) $value ) ) : '';
	}

	/**
	 * @param mixed $value Raw value.
	 */
	public static function sanitizeUrl( $value ): string {
		return is_scalar( $value ) ? esc_url_raw( trim( (string) $value ) ) : '';
	}

	/**
	 * @param mixed $value Raw value.
	 */
	public static function sanitizeEmail( $value ): string {
		return is_scalar( $value ) ? sanitize_email( (string) $value ) : '';
	}

	/**
	 * @param mixed $value Raw value.
	 */
	public static function sanitizeInt( $value ): int {
		if ( is_bool( $value ) ) {
			return (int) $value;
		}
		return is_numeric( $value ) ? (int) round( (float) $value ) : 0;
	}

	/**
	 * Booleans are stored as '1' or ''.
	 *
	 * @param mixed $value Raw value.
	 */
	public static function sanitizeBool( $value ): string {
		return in_array( $value, array( true, 1, '1', 'true', 'yes', 'on' ), true ) ? '1' : '';
	}

	/**
	 * Accepts Y-m-d (or a longer ISO/MySQL datetime) and returns Y-m-d or ''.
	 *
	 * @param mixed $value Raw value.
	 */
	public static function sanitizeDate( $value ): string {
		if ( ! is_string( $value ) || '' === trim( $value ) ) {
			return '';
		}
		$value = substr( trim( $value ), 0, 10 );
		$date  = \DateTimeImmutable::createFromFormat( '!Y-m-d', $value );
		return ( $date && $date->format( 'Y-m-d' ) === $value ) ? $value : '';
	}

	/**
	 * List of unique positive integers, order preserved. Accepts arrays or comma-separated strings.
	 *
	 * @param mixed $value Raw value.
	 * @return list<int>
	 */
	public static function sanitizeIds( $value ): array {
		if ( is_string( $value ) ) {
			$value = '' === trim( $value ) ? array() : preg_split( '/[\s,]+/', $value );
		} elseif ( is_int( $value ) ) {
			$value = array( $value );
		}
		if ( ! is_array( $value ) ) {
			return array();
		}
		$ids = array();
		foreach ( $value as $id ) {
			if ( is_numeric( $id ) && (int) $id > 0 ) {
				$ids[ (int) $id ] = true;
			}
		}
		return array_keys( $ids );
	}

	/**
	 * Sanitizes a structured value against a shape (Registry::SHAPES). Accepts arrays or JSON strings.
	 *
	 * @param mixed $value Raw value.
	 * @return array<int|string, mixed>
	 */
	public static function sanitizeShape( string $shape_name, $value ): array {
		if ( is_string( $value ) ) {
			$decoded = json_decode( $value, true );
			$value   = is_array( $decoded ) ? $decoded : array();
		}
		if ( ! is_array( $value ) ) {
			return array();
		}
		$shape = Registry::SHAPES[ $shape_name ] ?? null;
		if ( null === $shape ) {
			return array();
		}

		if ( false === $shape['list'] ) {
			return self::sanitizeRow( $shape, $value ) ?? array();
		}

		if ( isset( $shape['item'] ) ) {
			$out  = array();
			$seen = array();
			foreach ( $value as $item ) {
				if ( ! is_scalar( $item ) ) {
					continue;
				}
				$clean = self::sanitizeField( (string) $shape['item'], $item, array() );
				if ( '' === $clean ) {
					continue;
				}
				$fingerprint = function_exists( 'mb_strtolower' ) ? mb_strtolower( (string) $clean ) : strtolower( (string) $clean );
				if ( isset( $seen[ $fingerprint ] ) ) {
					continue;
				}
				$seen[ $fingerprint ] = true;
				$out[]                = $clean;
			}
			return $out;
		}

		$rows = array();
		foreach ( $value as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$clean = self::sanitizeRow( $shape, $row );
			if ( null !== $clean ) {
				$rows[] = $clean;
			}
		}
		return $rows;
	}

	/**
	 * Sanitizes one row; returns null when the row is empty or misses a required field.
	 *
	 * @param array<string, mixed>     $shape Shape definition.
	 * @param array<int|string, mixed> $row   Raw row.
	 * @return array<string, mixed>|null
	 */
	private static function sanitizeRow( array $shape, array $row ): ?array {
		$clean     = array();
		$non_empty = false;
		foreach ( $shape['fields'] as $field => $field_type ) {
			$value           = self::sanitizeField( (string) $field_type, $row[ $field ] ?? '', (array) ( $shape['enums'][ $field ] ?? array() ), (string) ( $shape['defaults'][ $field ] ?? '' ) );
			$clean[ $field ] = $value;
			if ( '' !== $value && 0 !== $value ) {
				$non_empty = true;
			}
		}
		if ( ! $non_empty ) {
			return null;
		}
		foreach ( (array) ( $shape['required'] ?? array() ) as $required ) {
			if ( '' === $clean[ $required ] || 0 === $clean[ $required ] ) {
				return null;
			}
		}
		return $clean;
	}

	/**
	 * Sanitizes a single field of a shape.
	 *
	 * @param mixed    $value    Raw value.
	 * @param string[] $enum     Allowed values for 'enum'.
	 * @param string   $fallback Value for an invalid enum ('' = first allowed value).
	 * @return int|string
	 */
	private static function sanitizeField( string $type, $value, array $enum, string $fallback = '' ) {
		if ( ! is_scalar( $value ) ) {
			return 'int' === $type ? 0 : '';
		}
		switch ( $type ) {
			case 'int':
				return max( 0, self::sanitizeInt( $value ) );
			case 'ref':
				// A post ID or a slug (importer keys); numbers are normalised to int.
				return is_numeric( $value ) ? max( 0, (int) $value ) : sanitize_title( (string) $value );
			case 'enum':
				$str = self::sanitizeStr( $value );
				if ( in_array( $str, $enum, true ) ) {
					return $str;
				}
				return '' !== $fallback ? $fallback : ( $enum[0] ?? '' );
			case 'voivodeship':
				$slug = sanitize_title( (string) $value );
				return Voivodeships::isValid( $slug ) ? $slug : '';
			case 'html':
				return self::sanitizeHtml( $value );
			case 'text':
				return self::sanitizeText( $value );
			case 'url':
				return self::sanitizeUrl( $value );
			default:
				return self::sanitizeStr( $value );
		}
	}
}
