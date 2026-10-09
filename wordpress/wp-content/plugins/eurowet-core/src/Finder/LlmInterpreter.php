<?php
/**
 * Optional AI intent interpreter (Claude Messages API). Used ONLY when the local engine is not confident.
 * It receives the closed list of need slugs and may answer with exactly one of them or "none" —
 * structured output (json_schema enum) makes any other answer impossible, and the slug is validated again.
 * Products are never chosen by the model; they come from verified need relations.
 *
 * Raw HTTP via wp_remote_post on purpose: a distributable WordPress plugin must not ship Composer runtime
 * dependencies (the PHP SDK pulls an HTTP client stack that conflicts with other plugins' bundled copies).
 *
 * @package Eurowet\Core
 */

declare(strict_types=1);

namespace Eurowet\Core\Finder;

defined( 'ABSPATH' ) || exit;

final class LlmInterpreter {

	private const ENDPOINT = 'https://api.anthropic.com/v1/messages';

	public static function enabled(): bool {
		return (bool) ew_get_option( 'finder_llm_enabled', false ) && '' !== (string) ew_get_option( 'finder_llm_api_key', '' );
	}

	/**
	 * @param array<int, array<string, mixed>> $needs Need records (slug, title, synonyms).
	 * @return array{slug: ?string, species: ?string}|null null on error/unavailable
	 */
	public static function interpret( string $query, array $needs, string $lang ): ?array {
		if ( ! self::enabled() || ! $needs ) {
			return null;
		}
		$normalized = Normalizer::fold( $query );
		$key        = 'ew_llm_' . md5( $lang . '|' . $normalized );
		$cached     = get_transient( $key );
		if ( is_array( $cached ) ) {
			return $cached;
		}
		$slugs   = array_values( array_map( static fn( $n ) => (string) $n['slug'], $needs ) );
		$catalog = array();
		foreach ( $needs as $n ) {
			$catalog[] = $n['slug'] . ' — ' . $n['title'] . ( $n['synonyms'] ? ' (' . implode( ', ', array_slice( (array) $n['synonyms'], 0, 12 ) ) . ')' : '' );
		}
		$schema = array(
			'type'                 => 'object',
			'properties'           => array(
				'need'    => array( 'type' => 'string', 'enum' => array_merge( $slugs, array( 'none' ) ) ),
				'species' => array( 'type' => 'string', 'enum' => array( 'pies', 'kot', 'male-ssaki', 'fretka', 'ptaki-ozdobne', 'golebie', 'kon', 'zwierzeta-gospodarskie', 'unknown' ) ),
			),
			'required'             => array( 'need', 'species' ),
			'additionalProperties' => false,
		);
		$body   = array(
			'model'         => (string) ew_get_option( 'finder_llm_model', 'claude-opus-5-5' ),
			'max_tokens'    => 2048,
			'fallbacks'     => 'default',
			'output_config' => array(
				'effort' => 'low',
				'format' => array( 'type' => 'json_schema', 'schema' => $schema ),
			),
			'system'        => 'You map a pet owner\'s search query to ONE care need from a fixed list for a veterinary care brand website. '
				. 'Answer "none" if no listed need clearly matches, if the query is about something else, or if it describes an emergency. '
				. 'Never guess. Need list:' . "\n" . implode( "\n", $catalog ),
			'messages'      => array( array( 'role' => 'user', 'content' => mb_substr( $query, 0, 200 ) ) ),
		);
		$res = wp_remote_post(
			self::ENDPOINT,
			array(
				'timeout' => 12,
				'headers' => array(
					'x-api-key'         => (string) ew_get_option( 'finder_llm_api_key', '' ),
					'anthropic-version' => '2023-06-01',
					'anthropic-beta'    => 'server-side-fallback-2026-07-01',
					'content-type'      => 'application/json',
				),
				'body'    => wp_json_encode( $body ),
			)
		);
		if ( is_wp_error( $res ) || 200 !== (int) wp_remote_retrieve_response_code( $res ) ) {
			return null;
		}
		$data = json_decode( (string) wp_remote_retrieve_body( $res ), true );
		if ( ! is_array( $data ) || 'refusal' === ( $data['stop_reason'] ?? '' ) ) {
			$result = array( 'slug' => null, 'species' => null );
			set_transient( $key, $result, DAY_IN_SECONDS );
			return $result;
		}
		$text = '';
		foreach ( (array) ( $data['content'] ?? array() ) as $block ) {
			if ( is_array( $block ) && 'text' === ( $block['type'] ?? '' ) ) {
				$text .= (string) $block['text'];
			}
		}
		$parsed  = json_decode( $text, true );
		$slug    = is_array( $parsed ) ? (string) ( $parsed['need'] ?? 'none' ) : 'none';
		$species = is_array( $parsed ) ? (string) ( $parsed['species'] ?? 'unknown' ) : 'unknown';
		$result  = array(
			'slug'    => in_array( $slug, $slugs, true ) ? $slug : null, // closed set — anything else is rejected
			'species' => 'unknown' === $species ? null : $species,
		);
		set_transient( $key, $result, 30 * DAY_IN_SECONDS );
		return $result;
	}
}
