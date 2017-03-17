<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\Module\AlternativeLanguageTitleInAdminBar;

use Inpsyde\MultilingualPress\Module\ActivationAwareModuleServiceProvider;
use Inpsyde\MultilingualPress\Module\ActivationAwareness;
use Inpsyde\MultilingualPress\Module\Module;
use Inpsyde\MultilingualPress\Module\ModuleManager;
use Inpsyde\MultilingualPress\Service\Container;

/**
 * Module service provider.
 *
 * @package Inpsyde\MultilingualPress\Module\AlternativeLanguageTitleInAdminBar
 * @since   3.0.0
 */
final class ServiceProvider implements ActivationAwareModuleServiceProvider {

	use ActivationAwareness;

	/**
	 * Registers the provided services on the given container.
	 *
	 * @since 3.0.0
	 *
	 * @param Container $container Container object.
	 *
	 * @return void
	 */
	public function register( Container $container ) {

		$container['multilingualpress.alternative_language_title_customizer'] = function ( Container $container ) {

			return new AdminBarCustomizer(
				$container['multilingualpress.alternative_language_titles']
			);
		};

		$container['multilingualpress.alternative_language_titles'] = function ( Container $container ) {

			return new AlternativeLanguageTitles(
				$container['multilingualpress.site_settings_repository']
			);
		};
	}

	/**
	 * Bootstraps the registered services.
	 *
	 * @since 3.0.0
	 *
	 * @param Container $container Container object.
	 *
	 * @return void
	 */
	public function bootstrap( Container $container ) {

		add_action(
			'mlp_blogs_save_fields',
			[ $container['multilingualpress.alternative_language_titles'], 'update' ]
		);

		$this->on_activation( function () use ( $container ) {

			$customizer = $container['multilingualpress.alternative_language_title_customizer'];

			add_filter( 'admin_bar_menu', [ $customizer, 'replace_site_nodes' ], 11 );

			if ( ! is_network_admin() ) {
				add_filter( 'admin_bar_menu', [ $customizer, 'replace_site_name' ], 31 );
			}
		} );
	}

	/**
	 * Registers the module at the module manager.
	 *
	 * @since 3.0.0
	 *
	 * @param ModuleManager $module_manager Module manager object.
	 *
	 * @return bool Whether or not the module was registered successfully AND has been activated.
	 */
	public function register_module( ModuleManager $module_manager ): bool {

		return $module_manager->register_module( new Module( 'alternative_language_title', [
			'description' => __(
				'Show sites with their alternative language title in the admin bar.',
				'multilingual-press'
			),
			'name'        => __( 'Alternative Language Title', 'multilingual-press' ),
			'active'      => false,
		] ) );
	}
}