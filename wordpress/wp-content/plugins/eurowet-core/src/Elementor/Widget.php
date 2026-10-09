<?php
/**
 * Generic Elementor widget wrapping one ew_render() component.
 *
 * @package Eurowet\Core
 */

declare(strict_types=1);

namespace Eurowet\Core\Elementor;

defined( 'ABSPATH' ) || exit;

final class Widget extends \Elementor\Widget_Base {

	private string $component = 'finder';

	public function __construct( $data = array(), $args = null ) {
		if ( is_array( $args ) && isset( $args['ew_component'] ) ) {
			$this->component = (string) $args['ew_component'];
		} elseif ( isset( $data['widgetType'] ) && 0 === strpos( (string) $data['widgetType'], 'ew-' ) ) {
			$this->component = substr( (string) $data['widgetType'], 3 );
		}
		parent::__construct( $data, $args );
	}

	public function get_name() {
		return 'ew-' . $this->component;
	}

	public function get_title() {
		return 'Eurowet: ' . ( Module::WIDGETS[ $this->component ][0] ?? $this->component );
	}

	public function get_icon() {
		return Module::WIDGETS[ $this->component ][1] ?? 'eicon-apps';
	}

	public function get_categories() {
		return array( 'eurowet' );
	}

	protected function register_controls() {
		$this->start_controls_section( 'ew', array( 'label' => 'Eurowet' ) );
		foreach ( Module::WIDGETS[ $this->component ][2] ?? array() as $name => $c ) {
			$type = array( 'text' => \Elementor\Controls_Manager::TEXT, 'number' => \Elementor\Controls_Manager::NUMBER, 'select' => \Elementor\Controls_Manager::SELECT, 'switcher' => \Elementor\Controls_Manager::SWITCHER )[ $c[1] ] ?? \Elementor\Controls_Manager::TEXT;
			$args = array( 'label' => $c[0], 'type' => $type );
			if ( isset( $c[2] ) ) {
				$args['options'] = $c[2];
				$args['default'] = (string) array_key_first( $c[2] );
			}
			$this->add_control( $name, $args );
		}
		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$args     = array();
		foreach ( array_keys( Module::WIDGETS[ $this->component ][2] ?? array() ) as $k ) {
			if ( isset( $settings[ $k ] ) && '' !== $settings[ $k ] ) {
				$args[ $k ] = $settings[ $k ];
			}
		}
		echo ew_render( $this->component, $args ); // phpcs:ignore WordPress.Security.EscapeOutput -- component escapes.
	}
}
