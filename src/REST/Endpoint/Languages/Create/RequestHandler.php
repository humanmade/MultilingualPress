<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\MultilingualPress\REST\Endpoint\Languages\Create;

use Inpsyde\MultilingualPress\API\Languages as API;
use Inpsyde\MultilingualPress\Common\Type\Language;
use Inpsyde\MultilingualPress\Common\Type\NullLanguage;
use Inpsyde\MultilingualPress\Database\Table\LanguagesTable;
use Inpsyde\MultilingualPress\Factory\RESTResponseFactory;
use Inpsyde\MultilingualPress\REST\Common\Endpoint;
use Inpsyde\MultilingualPress\REST\Common\Request\FieldProcessor;
use Inpsyde\MultilingualPress\REST\Endpoint\Languages\Formatter;
use Inpsyde\MultilingualPress\REST\Endpoint\Languages\Schema;

/**
 * Request handler for creating languages.
 *
 * @package Inpsyde\MultilingualPress\REST\Endpoint\Languages\Create
 * @since   3.0.0
 */
final class RequestHandler implements Endpoint\RequestHandler {

	/**
	 * @var API
	 */
	private $api;

	/**
	 * @var FieldProcessor
	 */
	private $field_processor;

	/**
	 * @var Formatter
	 */
	private $formatter;

	/**
	 * @var string
	 */
	private $object_type;

	/**
	 * @var RESTResponseFactory
	 */
	private $response_factory;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param API                 $api              Content relations API object.
	 * @param Formatter           $formatter        Response data formatter object.
	 * @param Schema              $schema           Endpoint schema object.
	 * @param FieldProcessor      $field_processor  Request data field processor object.
	 * @param RESTResponseFactory $response_factory REST response factory object.
	 */
	public function __construct(
		API $api,
		Formatter $formatter,
		Schema $schema,
		FieldProcessor $field_processor,
		RESTResponseFactory $response_factory
	) {

		$this->api = $api;

		$this->formatter = $formatter;

		$this->object_type = $schema->title();

		$this->field_processor = $field_processor;

		$this->response_factory = $response_factory;
	}

	/**
	 * Handles the given request object and returns the according response object.
	 *
	 * @since 3.0.0
	 *
	 * @param \WP_REST_Request $request Request object.
	 *
	 * @return \WP_REST_Response Response object.
	 */
	public function handle_request( \WP_REST_Request $request ): \WP_REST_Response {

		$id = $this->api->insert_language( $request->get_params() );
		if ( ! $id ) {
			return $this->create_error_response( $request );
		}

		$data = $this->formatter->format(
			$this->get_languages( $id ),
			(string) ( $request['context'] ?? 'view' )
		);

		$data = $this->field_processor->add_fields_to_object( $data, $request, $this->object_type );

		return $this->response_factory->create( [ $data ] );
	}

	/**
	 * Creates an error response.
	 *
	 * @param \WP_REST_Request $request Request object.
	 *
	 * @return \WP_REST_Response Response object.
	 */
	private function create_error_response( \WP_REST_Request $request ): \WP_REST_Response {

		return $this->response_factory->create( [
			[
				'code'    => 'could_not_create',
				'message' => __( 'The language could not be created.', 'multilingualpress' ),
				'data'    => $request->get_params(),
			],
			400,
		] );
	}

	/**
	 * Returns an array containing the language with the given ID, or an empty array if the langauge does not exist.
	 *
	 * @param int $id Language ID.
	 *
	 * @return Language[] An array of language objects..
	 */
	private function get_languages( int $id ): array {

		$language = $this->api->get_language_by( LanguagesTable::COLUMN_ID, $id );
		if ( $language instanceof NullLanguage ) {
			return [];
		}

		return [
			$language,
		];
	}
}