<?php

// This file is auto-generated and is for apps only. Bundles SHOULD NOT rely on its content.

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

/**
 * This class provides array-shapes for configuring the services and bundles of an application.
 *
 * Services declared with the config() method below are autowired and autoconfigured by default.
 *
 * This is for apps only. Bundles SHOULD NOT use it.
 *
 * Example:
 *
 *     ```php
 *     // config/services.php
 *     namespace Symfony\Component\DependencyInjection\Loader\Configurator;
 *
 *     return App::config([
 *         'services' => [
 *             'App\\' => [
 *                 'resource' => '../src/',
 *             ],
 *         ],
 *     ]);
 *     ```
 *
 * @psalm-type ImportsConfig = list<string|array{
 *     resource: string,
 *     type?: string|null,
 *     ignore_errors?: bool,
 * }>
 * @psalm-type ParametersConfig = array<string, scalar|\UnitEnum|array<scalar|\UnitEnum|array<mixed>|\Symfony\Component\Config\Loader\ParamConfigurator|null>|\Symfony\Component\Config\Loader\ParamConfigurator|null>
 * @psalm-type ArgumentsType = list<mixed>|array<string, mixed>
 * @psalm-type CallType = array<string, ArgumentsType>|array{0:string, 1?:ArgumentsType, 2?:bool}|array{method:string, arguments?:ArgumentsType, returns_clone?:bool}
 * @psalm-type TagsType = list<string|array<string, array<string, mixed>>> // arrays inside the list must have only one element, with the tag name as the key
 * @psalm-type CallbackType = string|array{0:string|ReferenceConfigurator,1:string}|\Closure|ReferenceConfigurator|ExpressionConfigurator
 * @psalm-type DeprecationType = array{package: string, version: string, message?: string}
 * @psalm-type DefaultsType = array{
 *     public?: bool,
 *     tags?: TagsType,
 *     resource_tags?: TagsType,
 *     autowire?: bool,
 *     autoconfigure?: bool,
 *     bind?: array<string, mixed>,
 * }
 * @psalm-type InstanceofType = array{
 *     shared?: bool,
 *     lazy?: bool|string,
 *     public?: bool,
 *     properties?: array<string, mixed>,
 *     configurator?: CallbackType,
 *     calls?: list<CallType>,
 *     tags?: TagsType,
 *     resource_tags?: TagsType,
 *     autowire?: bool,
 *     bind?: array<string, mixed>,
 *     constructor?: string,
 * }
 * @psalm-type DefinitionType = array{
 *     class?: string,
 *     file?: string,
 *     parent?: string,
 *     shared?: bool,
 *     synthetic?: bool,
 *     lazy?: bool|string,
 *     public?: bool,
 *     abstract?: bool,
 *     deprecated?: DeprecationType,
 *     factory?: CallbackType,
 *     configurator?: CallbackType,
 *     arguments?: ArgumentsType,
 *     properties?: array<string, mixed>,
 *     calls?: list<CallType>,
 *     tags?: TagsType,
 *     resource_tags?: TagsType,
 *     decorates?: string,
 *     decoration_inner_name?: string,
 *     decoration_priority?: int,
 *     decoration_on_invalid?: 'exception'|'ignore'|null,
 *     autowire?: bool,
 *     autoconfigure?: bool,
 *     bind?: array<string, mixed>,
 *     constructor?: string,
 *     from_callable?: CallbackType,
 * }
 * @psalm-type AliasType = string|array{
 *     alias: string,
 *     public?: bool,
 *     deprecated?: DeprecationType,
 * }
 * @psalm-type PrototypeType = array{
 *     resource: string,
 *     namespace?: string,
 *     exclude?: string|list<string>,
 *     parent?: string,
 *     shared?: bool,
 *     lazy?: bool|string,
 *     public?: bool,
 *     abstract?: bool,
 *     deprecated?: DeprecationType,
 *     factory?: CallbackType,
 *     arguments?: ArgumentsType,
 *     properties?: array<string, mixed>,
 *     configurator?: CallbackType,
 *     calls?: list<CallType>,
 *     tags?: TagsType,
 *     resource_tags?: TagsType,
 *     autowire?: bool,
 *     autoconfigure?: bool,
 *     bind?: array<string, mixed>,
 *     constructor?: string,
 * }
 * @psalm-type StackType = array{
 *     stack: list<DefinitionType|AliasType|PrototypeType|array<class-string, ArgumentsType|null>>,
 *     public?: bool,
 *     deprecated?: DeprecationType,
 * }
 * @psalm-type ServicesConfig = array{
 *     _defaults?: DefaultsType,
 *     _instanceof?: InstanceofType,
 *     ...<string, DefinitionType|AliasType|PrototypeType|StackType|ArgumentsType|null>
 * }
 * @psalm-type ExtensionType = array<string, mixed>
 * @psalm-type FrameworkConfig = array{
 *     secret?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *     http_method_override?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Set true to enable support for the '_method' request parameter to determine the intended HTTP method on POST requests. // Default: false
 *     allowed_http_method_override?: list<string|\Symfony\Component\Config\Loader\ParamConfigurator>|null,
 *     trust_x_sendfile_type_header?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Set true to enable support for xsendfile in binary file responses. // Default: "%env(bool:default::SYMFONY_TRUST_X_SENDFILE_TYPE_HEADER)%"
 *     ide?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *     test?: bool|\Symfony\Component\Config\Loader\ParamConfigurator,
 *     default_locale?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "en"
 *     set_locale_from_accept_language?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Whether to use the Accept-Language HTTP header to set the Request locale (only when the "_locale" request attribute is not passed). // Default: false
 *     set_content_language_from_locale?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Whether to set the Content-Language HTTP header on the Response using the Request locale. // Default: false
 *     enabled_locales?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *     trusted_hosts?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *     trusted_proxies?: mixed, // Default: ["%env(default::SYMFONY_TRUSTED_PROXIES)%"]
 *     trusted_headers?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *     error_controller?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "error_controller"
 *     handle_all_throwables?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // HttpKernel will handle all kinds of \Throwable. // Default: true
 *     csrf_protection?: bool|array{
 *         enabled?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *         stateless_token_ids?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         check_header?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Whether to check the CSRF token in a header in addition to a cookie when using stateless protection. // Default: false
 *         cookie_name?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The name of the cookie to use when using stateless protection. // Default: "csrf-token"
 *     },
 *     form?: bool|array{ // Form configuration
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *         csrf_protection?: bool|array{
 *             enabled?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *             token_id?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *             field_name?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "_token"
 *             field_attr?: array<string, scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         },
 *     },
 *     http_cache?: bool|array{ // HTTP cache configuration
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *         debug?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: "%kernel.debug%"
 *         trace_level?: "none"|"short"|"full"|\Symfony\Component\Config\Loader\ParamConfigurator,
 *         trace_header?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *         default_ttl?: int|\Symfony\Component\Config\Loader\ParamConfigurator,
 *         private_headers?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         skip_response_headers?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         allow_reload?: bool|\Symfony\Component\Config\Loader\ParamConfigurator,
 *         allow_revalidate?: bool|\Symfony\Component\Config\Loader\ParamConfigurator,
 *         stale_while_revalidate?: int|\Symfony\Component\Config\Loader\ParamConfigurator,
 *         stale_if_error?: int|\Symfony\Component\Config\Loader\ParamConfigurator,
 *         terminate_on_cache_hit?: bool|\Symfony\Component\Config\Loader\ParamConfigurator,
 *     },
 *     esi?: bool|array{ // ESI configuration
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *     },
 *     ssi?: bool|array{ // SSI configuration
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *     },
 *     fragments?: bool|array{ // Fragments configuration
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *         hinclude_default_template?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *         path?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "/_fragment"
 *     },
 *     profiler?: bool|array{ // Profiler configuration
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *         collect?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *         collect_parameter?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The name of the parameter to use to enable or disable collection on a per request basis. // Default: null
 *         only_exceptions?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *         only_main_requests?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *         dsn?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "file:%kernel.cache_dir%/profiler"
 *         collect_serializer_data?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Enables the serializer data collector and profiler panel. // Default: false
 *     },
 *     workflows?: bool|array{
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *         workflows?: array<string, array{ // Default: []
 *             audit_trail?: bool|array{
 *                 enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *             },
 *             type?: "workflow"|"state_machine"|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: "state_machine"
 *             marking_store?: array{
 *                 type?: "method"|\Symfony\Component\Config\Loader\ParamConfigurator,
 *                 property?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *                 service?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             },
 *             supports?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *             definition_validators?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *             support_strategy?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             initial_marking?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *             events_to_dispatch?: list<string|\Symfony\Component\Config\Loader\ParamConfigurator>|null,
 *             places?: list<array{ // Default: []
 *                 name: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *                 metadata?: list<mixed>,
 *             }>,
 *             transitions: list<array{ // Default: []
 *                 name: string|\Symfony\Component\Config\Loader\ParamConfigurator,
 *                 guard?: string|\Symfony\Component\Config\Loader\ParamConfigurator, // An expression to block the transition.
 *                 from?: list<array{ // Default: []
 *                     place: string|\Symfony\Component\Config\Loader\ParamConfigurator,
 *                     weight?: int|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: 1
 *                 }>,
 *                 to?: list<array{ // Default: []
 *                     place: string|\Symfony\Component\Config\Loader\ParamConfigurator,
 *                     weight?: int|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: 1
 *                 }>,
 *                 weight?: int|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: 1
 *                 metadata?: list<mixed>,
 *             }>,
 *             metadata?: list<mixed>,
 *         }>,
 *     },
 *     router?: bool|array{ // Router configuration
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *         resource: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *         type?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *         cache_dir?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Deprecated: Setting the "framework.router.cache_dir.cache_dir" configuration option is deprecated. It will be removed in version 8.0. // Default: "%kernel.build_dir%"
 *         default_uri?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The default URI used to generate URLs in a non-HTTP context. // Default: null
 *         http_port?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: 80
 *         https_port?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: 443
 *         strict_requirements?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // set to true to throw an exception when a parameter does not match the requirements set to false to disable exceptions when a parameter does not match the requirements (and return null instead) set to null to disable parameter checks against requirements 'true' is the preferred configuration in development mode, while 'false' or 'null' might be preferred in production // Default: true
 *         utf8?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *     },
 *     session?: bool|array{ // Session configuration
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *         storage_factory_id?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "session.storage.factory.native"
 *         handler_id?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Defaults to using the native session handler, or to the native *file* session handler if "save_path" is not null.
 *         name?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *         cookie_lifetime?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *         cookie_path?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *         cookie_domain?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *         cookie_secure?: true|false|"auto"|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: "auto"
 *         cookie_httponly?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *         cookie_samesite?: null|"lax"|"strict"|"none"|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: "lax"
 *         use_cookies?: bool|\Symfony\Component\Config\Loader\ParamConfigurator,
 *         gc_divisor?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *         gc_probability?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *         gc_maxlifetime?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *         save_path?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Defaults to "%kernel.cache_dir%/sessions" if the "handler_id" option is not null.
 *         metadata_update_threshold?: int|\Symfony\Component\Config\Loader\ParamConfigurator, // Seconds to wait between 2 session metadata updates. // Default: 0
 *         sid_length?: int|\Symfony\Component\Config\Loader\ParamConfigurator, // Deprecated: Setting the "framework.session.sid_length.sid_length" configuration option is deprecated. It will be removed in version 8.0. No alternative is provided as PHP 8.4 has deprecated the related option.
 *         sid_bits_per_character?: int|\Symfony\Component\Config\Loader\ParamConfigurator, // Deprecated: Setting the "framework.session.sid_bits_per_character.sid_bits_per_character" configuration option is deprecated. It will be removed in version 8.0. No alternative is provided as PHP 8.4 has deprecated the related option.
 *     },
 *     request?: bool|array{ // Request configuration
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *         formats?: array<string, string|list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>>,
 *     },
 *     assets?: bool|array{ // Assets configuration
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *         strict_mode?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Throw an exception if an entry is missing from the manifest.json. // Default: false
 *         version_strategy?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *         version?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *         version_format?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "%%s?%%s"
 *         json_manifest_path?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *         base_path?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: ""
 *         base_urls?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         packages?: array<string, array{ // Default: []
 *             strict_mode?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Throw an exception if an entry is missing from the manifest.json. // Default: false
 *             version_strategy?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *             version?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             version_format?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *             json_manifest_path?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *             base_path?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: ""
 *             base_urls?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         }>,
 *     },
 *     asset_mapper?: bool|array{ // Asset Mapper configuration
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *         paths?: array<string, scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         excluded_patterns?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         exclude_dotfiles?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // If true, any files starting with "." will be excluded from the asset mapper. // Default: true
 *         server?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // If true, a "dev server" will return the assets from the public directory (true in "debug" mode only by default). // Default: false
 *         public_prefix?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The public path where the assets will be written to (and served from when "server" is true). // Default: "/assets/"
 *         missing_import_mode?: "strict"|"warn"|"ignore"|\Symfony\Component\Config\Loader\ParamConfigurator, // Behavior if an asset cannot be found when imported from JavaScript or CSS files - e.g. "import './non-existent.js'". "strict" means an exception is thrown, "warn" means a warning is logged, "ignore" means the import is left as-is. // Default: "warn"
 *         extensions?: array<string, scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         importmap_path?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The path of the importmap.php file. // Default: "%kernel.project_dir%/importmap.php"
 *         importmap_polyfill?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The importmap name that will be used to load the polyfill. Set to false to disable. // Default: "es-module-shims"
 *         importmap_script_attributes?: array<string, scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         vendor_dir?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The directory to store JavaScript vendors. // Default: "%kernel.project_dir%/assets/vendor"
 *         precompress?: bool|array{ // Precompress assets with Brotli, Zstandard and gzip.
 *             enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *             formats?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *             extensions?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         },
 *     },
 *     translator?: bool|array{ // Translator configuration
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *         fallbacks?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         logging?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *         formatter?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "translator.formatter.default"
 *         cache_dir?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "%kernel.cache_dir%/translations"
 *         default_path?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The default path used to load translations. // Default: "%kernel.project_dir%/translations"
 *         paths?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         pseudo_localization?: bool|array{
 *             enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *             accents?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *             expansion_factor?: float|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: 1.0
 *             brackets?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *             parse_html?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *             localizable_html_attributes?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         },
 *         providers?: array<string, array{ // Default: []
 *             dsn?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             domains?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *             locales?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         }>,
 *         globals?: array<string, string|array{ // Default: []
 *             value?: mixed,
 *             message?: string|\Symfony\Component\Config\Loader\ParamConfigurator,
 *             parameters?: array<string, scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *             domain?: string|\Symfony\Component\Config\Loader\ParamConfigurator,
 *         }>,
 *     },
 *     validation?: bool|array{ // Validation configuration
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *         cache?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Deprecated: Setting the "framework.validation.cache.cache" configuration option is deprecated. It will be removed in version 8.0.
 *         enable_attributes?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *         static_method?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         translation_domain?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "validators"
 *         email_validation_mode?: "html5"|"html5-allow-no-tld"|"strict"|"loose"|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: "html5"
 *         mapping?: array{
 *             paths?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         },
 *         not_compromised_password?: bool|array{
 *             enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // When disabled, compromised passwords will be accepted as valid. // Default: true
 *             endpoint?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // API endpoint for the NotCompromisedPassword Validator. // Default: null
 *         },
 *         disable_translation?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *         auto_mapping?: array<string, array{ // Default: []
 *             services?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         }>,
 *     },
 *     annotations?: bool|array{
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *     },
 *     serializer?: bool|array{ // Serializer configuration
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *         enable_attributes?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *         name_converter?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *         circular_reference_handler?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *         max_depth_handler?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *         mapping?: array{
 *             paths?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         },
 *         default_context?: list<mixed>,
 *         named_serializers?: array<string, array{ // Default: []
 *             name_converter?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             default_context?: list<mixed>,
 *             include_built_in_normalizers?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Whether to include the built-in normalizers // Default: true
 *             include_built_in_encoders?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Whether to include the built-in encoders // Default: true
 *         }>,
 *     },
 *     property_access?: bool|array{ // Property access configuration
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *         magic_call?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *         magic_get?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *         magic_set?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *         throw_exception_on_invalid_index?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *         throw_exception_on_invalid_property_path?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *     },
 *     type_info?: bool|array{ // Type info configuration
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *         aliases?: array<string, scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *     },
 *     property_info?: bool|array{ // Property info configuration
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *         with_constructor_extractor?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Registers the constructor extractor.
 *     },
 *     cache?: array{ // Cache configuration
 *         prefix_seed?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Used to namespace cache keys when using several apps with the same shared backend. // Default: "_%kernel.project_dir%.%kernel.container_class%"
 *         app?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // App related cache pools configuration. // Default: "cache.adapter.filesystem"
 *         system?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // System related cache pools configuration. // Default: "cache.adapter.system"
 *         directory?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "%kernel.share_dir%/pools/app"
 *         default_psr6_provider?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *         default_redis_provider?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "redis://localhost"
 *         default_valkey_provider?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "valkey://localhost"
 *         default_memcached_provider?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "memcached://localhost"
 *         default_doctrine_dbal_provider?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "database_connection"
 *         default_pdo_provider?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *         pools?: array<string, array{ // Default: []
 *             adapters?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *             tags?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *             public?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *             default_lifetime?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default lifetime of the pool.
 *             provider?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Overwrite the setting from the default provider for this adapter.
 *             early_expiration_message_bus?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             clearer?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *         }>,
 *     },
 *     php_errors?: array{ // PHP errors handling configuration
 *         log?: mixed, // Use the application logger instead of the PHP logger for logging PHP errors. // Default: true
 *         throw?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Throw PHP errors as \ErrorException instances. // Default: false
 *     },
 *     exceptions?: array<string, array{ // Default: []
 *         log_level?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The level of log message. Null to let Symfony decide. // Default: null
 *         status_code?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The status code of the response. Null or 0 to let Symfony decide. // Default: null
 *         log_channel?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The channel of log message. Null to let Symfony decide. // Default: null
 *     }>,
 *     web_link?: bool|array{ // Web links configuration
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *     },
 *     lock?: bool|string|array{ // Lock configuration
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *         resources?: array<string, string|list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>>,
 *     },
 *     semaphore?: bool|string|array{ // Semaphore configuration
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *         resources?: array<string, scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *     },
 *     messenger?: bool|array{ // Messenger configuration
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *         routing?: array<string, array{ // Default: []
 *             senders?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         }>,
 *         serializer?: array{
 *             default_serializer?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Service id to use as the default serializer for the transports. // Default: "messenger.transport.native_php_serializer"
 *             symfony_serializer?: array{
 *                 format?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Serialization format for the messenger.transport.symfony_serializer service (which is not the serializer used by default). // Default: "json"
 *                 context?: array<string, mixed>,
 *             },
 *         },
 *         transports?: array<string, string|array{ // Default: []
 *             dsn?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             serializer?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Service id of a custom serializer to use. // Default: null
 *             options?: list<mixed>,
 *             failure_transport?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Transport name to send failed messages to (after all retries have failed). // Default: null
 *             retry_strategy?: string|array{
 *                 service?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Service id to override the retry strategy entirely. // Default: null
 *                 max_retries?: int|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: 3
 *                 delay?: int|\Symfony\Component\Config\Loader\ParamConfigurator, // Time in ms to delay (or the initial value when multiplier is used). // Default: 1000
 *                 multiplier?: float|\Symfony\Component\Config\Loader\ParamConfigurator, // If greater than 1, delay will grow exponentially for each retry: this delay = (delay * (multiple ^ retries)). // Default: 2
 *                 max_delay?: int|\Symfony\Component\Config\Loader\ParamConfigurator, // Max time in ms that a retry should ever be delayed (0 = infinite). // Default: 0
 *                 jitter?: float|\Symfony\Component\Config\Loader\ParamConfigurator, // Randomness to apply to the delay (between 0 and 1). // Default: 0.1
 *             },
 *             rate_limiter?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Rate limiter name to use when processing messages. // Default: null
 *         }>,
 *         failure_transport?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Transport name to send failed messages to (after all retries have failed). // Default: null
 *         stop_worker_on_signals?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         default_bus?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *         buses?: array<string, array{ // Default: {"messenger.bus.default":{"default_middleware":{"enabled":true,"allow_no_handlers":false,"allow_no_senders":true},"middleware":[]}}
 *             default_middleware?: bool|string|array{
 *                 enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *                 allow_no_handlers?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *                 allow_no_senders?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *             },
 *             middleware?: list<string|array{ // Default: []
 *                 id: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *                 arguments?: list<mixed>,
 *             }>,
 *         }>,
 *     },
 *     scheduler?: bool|array{ // Scheduler configuration
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *     },
 *     disallow_search_engine_index?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Enabled by default when debug is enabled. // Default: false
 *     http_client?: bool|array{ // HTTP Client configuration
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *         max_host_connections?: int|\Symfony\Component\Config\Loader\ParamConfigurator, // The maximum number of connections to a single host.
 *         default_options?: array{
 *             headers?: array<string, mixed>,
 *             vars?: list<mixed>,
 *             max_redirects?: int|\Symfony\Component\Config\Loader\ParamConfigurator, // The maximum number of redirects to follow.
 *             http_version?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The default HTTP version, typically 1.1 or 2.0, leave to null for the best version.
 *             resolve?: array<string, scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *             proxy?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The URL of the proxy to pass requests through or null for automatic detection.
 *             no_proxy?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // A comma separated list of hosts that do not require a proxy to be reached.
 *             timeout?: float|\Symfony\Component\Config\Loader\ParamConfigurator, // The idle timeout, defaults to the "default_socket_timeout" ini parameter.
 *             max_duration?: float|\Symfony\Component\Config\Loader\ParamConfigurator, // The maximum execution time for the request+response as a whole.
 *             bindto?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // A network interface name, IP address, a host name or a UNIX socket to bind to.
 *             verify_peer?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Indicates if the peer should be verified in a TLS context.
 *             verify_host?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Indicates if the host should exist as a certificate common name.
 *             cafile?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // A certificate authority file.
 *             capath?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // A directory that contains multiple certificate authority files.
 *             local_cert?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // A PEM formatted certificate file.
 *             local_pk?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // A private key file.
 *             passphrase?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The passphrase used to encrypt the "local_pk" file.
 *             ciphers?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // A list of TLS ciphers separated by colons, commas or spaces (e.g. "RC3-SHA:TLS13-AES-128-GCM-SHA256"...)
 *             peer_fingerprint?: array{ // Associative array: hashing algorithm => hash(es).
 *                 sha1?: mixed,
 *                 pin-sha256?: mixed,
 *                 md5?: mixed,
 *             },
 *             crypto_method?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The minimum version of TLS to accept; must be one of STREAM_CRYPTO_METHOD_TLSv*_CLIENT constants.
 *             extra?: list<mixed>,
 *             rate_limiter?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Rate limiter name to use for throttling requests. // Default: null
 *             caching?: bool|array{ // Caching configuration.
 *                 enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *                 cache_pool?: string|\Symfony\Component\Config\Loader\ParamConfigurator, // The taggable cache pool to use for storing the responses. // Default: "cache.http_client"
 *                 shared?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Indicates whether the cache is shared (public) or private. // Default: true
 *                 max_ttl?: int|\Symfony\Component\Config\Loader\ParamConfigurator, // The maximum TTL (in seconds) allowed for cached responses. Null means no cap. // Default: null
 *             },
 *             retry_failed?: bool|array{
 *                 enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *                 retry_strategy?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // service id to override the retry strategy. // Default: null
 *                 http_codes?: array<string, array{ // Default: []
 *                     code?: int|\Symfony\Component\Config\Loader\ParamConfigurator,
 *                     methods?: list<string|\Symfony\Component\Config\Loader\ParamConfigurator>,
 *                 }>,
 *                 max_retries?: int|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: 3
 *                 delay?: int|\Symfony\Component\Config\Loader\ParamConfigurator, // Time in ms to delay (or the initial value when multiplier is used). // Default: 1000
 *                 multiplier?: float|\Symfony\Component\Config\Loader\ParamConfigurator, // If greater than 1, delay will grow exponentially for each retry: delay * (multiple ^ retries). // Default: 2
 *                 max_delay?: int|\Symfony\Component\Config\Loader\ParamConfigurator, // Max time in ms that a retry should ever be delayed (0 = infinite). // Default: 0
 *                 jitter?: float|\Symfony\Component\Config\Loader\ParamConfigurator, // Randomness in percent (between 0 and 1) to apply to the delay. // Default: 0.1
 *             },
 *         },
 *         mock_response_factory?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The id of the service that should generate mock responses. It should be either an invokable or an iterable.
 *         scoped_clients?: array<string, string|array{ // Default: []
 *             scope?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The regular expression that the request URL must match before adding the other options. When none is provided, the base URI is used instead.
 *             base_uri?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The URI to resolve relative URLs, following rules in RFC 3985, section 2.
 *             auth_basic?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // An HTTP Basic authentication "username:password".
 *             auth_bearer?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // A token enabling HTTP Bearer authorization.
 *             auth_ntlm?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // A "username:password" pair to use Microsoft NTLM authentication (requires the cURL extension).
 *             query?: array<string, scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *             headers?: array<string, mixed>,
 *             max_redirects?: int|\Symfony\Component\Config\Loader\ParamConfigurator, // The maximum number of redirects to follow.
 *             http_version?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The default HTTP version, typically 1.1 or 2.0, leave to null for the best version.
 *             resolve?: array<string, scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *             proxy?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The URL of the proxy to pass requests through or null for automatic detection.
 *             no_proxy?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // A comma separated list of hosts that do not require a proxy to be reached.
 *             timeout?: float|\Symfony\Component\Config\Loader\ParamConfigurator, // The idle timeout, defaults to the "default_socket_timeout" ini parameter.
 *             max_duration?: float|\Symfony\Component\Config\Loader\ParamConfigurator, // The maximum execution time for the request+response as a whole.
 *             bindto?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // A network interface name, IP address, a host name or a UNIX socket to bind to.
 *             verify_peer?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Indicates if the peer should be verified in a TLS context.
 *             verify_host?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Indicates if the host should exist as a certificate common name.
 *             cafile?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // A certificate authority file.
 *             capath?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // A directory that contains multiple certificate authority files.
 *             local_cert?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // A PEM formatted certificate file.
 *             local_pk?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // A private key file.
 *             passphrase?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The passphrase used to encrypt the "local_pk" file.
 *             ciphers?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // A list of TLS ciphers separated by colons, commas or spaces (e.g. "RC3-SHA:TLS13-AES-128-GCM-SHA256"...).
 *             peer_fingerprint?: array{ // Associative array: hashing algorithm => hash(es).
 *                 sha1?: mixed,
 *                 pin-sha256?: mixed,
 *                 md5?: mixed,
 *             },
 *             crypto_method?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The minimum version of TLS to accept; must be one of STREAM_CRYPTO_METHOD_TLSv*_CLIENT constants.
 *             extra?: list<mixed>,
 *             rate_limiter?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Rate limiter name to use for throttling requests. // Default: null
 *             caching?: bool|array{ // Caching configuration.
 *                 enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *                 cache_pool?: string|\Symfony\Component\Config\Loader\ParamConfigurator, // The taggable cache pool to use for storing the responses. // Default: "cache.http_client"
 *                 shared?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Indicates whether the cache is shared (public) or private. // Default: true
 *                 max_ttl?: int|\Symfony\Component\Config\Loader\ParamConfigurator, // The maximum TTL (in seconds) allowed for cached responses. Null means no cap. // Default: null
 *             },
 *             retry_failed?: bool|array{
 *                 enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *                 retry_strategy?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // service id to override the retry strategy. // Default: null
 *                 http_codes?: array<string, array{ // Default: []
 *                     code?: int|\Symfony\Component\Config\Loader\ParamConfigurator,
 *                     methods?: list<string|\Symfony\Component\Config\Loader\ParamConfigurator>,
 *                 }>,
 *                 max_retries?: int|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: 3
 *                 delay?: int|\Symfony\Component\Config\Loader\ParamConfigurator, // Time in ms to delay (or the initial value when multiplier is used). // Default: 1000
 *                 multiplier?: float|\Symfony\Component\Config\Loader\ParamConfigurator, // If greater than 1, delay will grow exponentially for each retry: delay * (multiple ^ retries). // Default: 2
 *                 max_delay?: int|\Symfony\Component\Config\Loader\ParamConfigurator, // Max time in ms that a retry should ever be delayed (0 = infinite). // Default: 0
 *                 jitter?: float|\Symfony\Component\Config\Loader\ParamConfigurator, // Randomness in percent (between 0 and 1) to apply to the delay. // Default: 0.1
 *             },
 *         }>,
 *     },
 *     mailer?: bool|array{ // Mailer configuration
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *         message_bus?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The message bus to use. Defaults to the default bus if the Messenger component is installed. // Default: null
 *         dsn?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *         transports?: array<string, scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         envelope?: array{ // Mailer Envelope configuration
 *             sender?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             recipients?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *             allowed_recipients?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         },
 *         headers?: array<string, string|array{ // Default: []
 *             value?: mixed,
 *         }>,
 *         dkim_signer?: bool|array{ // DKIM signer configuration
 *             enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *             key?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Key content, or path to key (in PEM format with the `file://` prefix) // Default: ""
 *             domain?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: ""
 *             select?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: ""
 *             passphrase?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The private key passphrase // Default: ""
 *             options?: array<string, mixed>,
 *         },
 *         smime_signer?: bool|array{ // S/MIME signer configuration
 *             enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *             key?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Path to key (in PEM format) // Default: ""
 *             certificate?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Path to certificate (in PEM format without the `file://` prefix) // Default: ""
 *             passphrase?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The private key passphrase // Default: null
 *             extra_certificates?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *             sign_options?: int|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: null
 *         },
 *         smime_encrypter?: bool|array{ // S/MIME encrypter configuration
 *             enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *             repository?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // S/MIME certificate repository service. This service shall implement the `Symfony\Component\Mailer\EventListener\SmimeCertificateRepositoryInterface`. // Default: ""
 *             cipher?: int|\Symfony\Component\Config\Loader\ParamConfigurator, // A set of algorithms used to encrypt the message // Default: null
 *         },
 *     },
 *     secrets?: bool|array{
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *         vault_directory?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "%kernel.project_dir%/config/secrets/%kernel.runtime_environment%"
 *         local_dotenv_file?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "%kernel.project_dir%/.env.%kernel.runtime_environment%.local"
 *         decryption_env_var?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "base64:default::SYMFONY_DECRYPTION_SECRET"
 *     },
 *     notifier?: bool|array{ // Notifier configuration
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *         message_bus?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The message bus to use. Defaults to the default bus if the Messenger component is installed. // Default: null
 *         chatter_transports?: array<string, scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         texter_transports?: array<string, scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         notification_on_failed_messages?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *         channel_policy?: array<string, string|list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>>,
 *         admin_recipients?: list<array{ // Default: []
 *             email?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             phone?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: ""
 *         }>,
 *     },
 *     rate_limiter?: bool|array{ // Rate limiter configuration
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *         limiters?: array<string, array{ // Default: []
 *             lock_factory?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The service ID of the lock factory used by this limiter (or null to disable locking). // Default: "auto"
 *             cache_pool?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The cache pool to use for storing the current limiter state. // Default: "cache.rate_limiter"
 *             storage_service?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The service ID of a custom storage implementation, this precedes any configured "cache_pool". // Default: null
 *             policy: "fixed_window"|"token_bucket"|"sliding_window"|"compound"|"no_limit"|\Symfony\Component\Config\Loader\ParamConfigurator, // The algorithm to be used by this limiter.
 *             limiters?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *             limit?: int|\Symfony\Component\Config\Loader\ParamConfigurator, // The maximum allowed hits in a fixed interval or burst.
 *             interval?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Configures the fixed interval if "policy" is set to "fixed_window" or "sliding_window". The value must be a number followed by "second", "minute", "hour", "day", "week" or "month" (or their plural equivalent).
 *             rate?: array{ // Configures the fill rate if "policy" is set to "token_bucket".
 *                 interval?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Configures the rate interval. The value must be a number followed by "second", "minute", "hour", "day", "week" or "month" (or their plural equivalent).
 *                 amount?: int|\Symfony\Component\Config\Loader\ParamConfigurator, // Amount of tokens to add each interval. // Default: 1
 *             },
 *         }>,
 *     },
 *     uid?: bool|array{ // Uid configuration
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *         default_uuid_version?: 7|6|4|1|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: 7
 *         name_based_uuid_version?: 5|3|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: 5
 *         name_based_uuid_namespace?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *         time_based_uuid_version?: 7|6|1|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: 7
 *         time_based_uuid_node?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *     },
 *     html_sanitizer?: bool|array{ // HtmlSanitizer configuration
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *         sanitizers?: array<string, array{ // Default: []
 *             allow_safe_elements?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Allows "safe" elements and attributes. // Default: false
 *             allow_static_elements?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Allows all static elements and attributes from the W3C Sanitizer API standard. // Default: false
 *             allow_elements?: array<string, mixed>,
 *             block_elements?: list<string|\Symfony\Component\Config\Loader\ParamConfigurator>,
 *             drop_elements?: list<string|\Symfony\Component\Config\Loader\ParamConfigurator>,
 *             allow_attributes?: array<string, mixed>,
 *             drop_attributes?: array<string, mixed>,
 *             force_attributes?: array<string, array<string, string|\Symfony\Component\Config\Loader\ParamConfigurator>>,
 *             force_https_urls?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Transforms URLs using the HTTP scheme to use the HTTPS scheme instead. // Default: false
 *             allowed_link_schemes?: list<string|\Symfony\Component\Config\Loader\ParamConfigurator>,
 *             allowed_link_hosts?: list<string|\Symfony\Component\Config\Loader\ParamConfigurator>|null,
 *             allow_relative_links?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Allows relative URLs to be used in links href attributes. // Default: false
 *             allowed_media_schemes?: list<string|\Symfony\Component\Config\Loader\ParamConfigurator>,
 *             allowed_media_hosts?: list<string|\Symfony\Component\Config\Loader\ParamConfigurator>|null,
 *             allow_relative_medias?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Allows relative URLs to be used in media source attributes (img, audio, video, ...). // Default: false
 *             with_attribute_sanitizers?: list<string|\Symfony\Component\Config\Loader\ParamConfigurator>,
 *             without_attribute_sanitizers?: list<string|\Symfony\Component\Config\Loader\ParamConfigurator>,
 *             max_input_length?: int|\Symfony\Component\Config\Loader\ParamConfigurator, // The maximum length allowed for the sanitized input. // Default: 0
 *         }>,
 *     },
 *     webhook?: bool|array{ // Webhook configuration
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *         message_bus?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The message bus to use. // Default: "messenger.default_bus"
 *         routing?: array<string, array{ // Default: []
 *             service: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             secret?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: ""
 *         }>,
 *     },
 *     remote-event?: bool|array{ // RemoteEvent configuration
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *     },
 *     json_streamer?: bool|array{ // JSON streamer configuration
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *     },
 * }
 * @psalm-type DoctrineConfig = array{
 *     dbal?: array{
 *         default_connection?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *         types?: array<string, string|array{ // Default: []
 *             class: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             commented?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Deprecated: The doctrine-bundle type commenting features were removed; the corresponding config parameter was deprecated in 2.0 and will be dropped in 3.0.
 *         }>,
 *         driver_schemes?: array<string, scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         connections?: array<string, array{ // Default: []
 *             url?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // A URL with connection information; any parameter value parsed from this string will override explicitly set parameters
 *             dbname?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             host?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Defaults to "localhost" at runtime.
 *             port?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Defaults to null at runtime.
 *             user?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Defaults to "root" at runtime.
 *             password?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Defaults to null at runtime.
 *             override_url?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Deprecated: The "doctrine.dbal.override_url" configuration key is deprecated.
 *             dbname_suffix?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Adds the given suffix to the configured database name, this option has no effects for the SQLite platform
 *             application_name?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             charset?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             path?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             memory?: bool|\Symfony\Component\Config\Loader\ParamConfigurator,
 *             unix_socket?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The unix socket to use for MySQL
 *             persistent?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // True to use as persistent connection for the ibm_db2 driver
 *             protocol?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The protocol to use for the ibm_db2 driver (default to TCPIP if omitted)
 *             service?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // True to use SERVICE_NAME as connection parameter instead of SID for Oracle
 *             servicename?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Overrules dbname parameter if given and used as SERVICE_NAME or SID connection parameter for Oracle depending on the service parameter.
 *             sessionMode?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The session mode to use for the oci8 driver
 *             server?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The name of a running database server to connect to for SQL Anywhere.
 *             default_dbname?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Override the default database (postgres) to connect to for PostgreSQL connexion.
 *             sslmode?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Determines whether or with what priority a SSL TCP/IP connection will be negotiated with the server for PostgreSQL.
 *             sslrootcert?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The name of a file containing SSL certificate authority (CA) certificate(s). If the file exists, the server's certificate will be verified to be signed by one of these authorities.
 *             sslcert?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The path to the SSL client certificate file for PostgreSQL.
 *             sslkey?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The path to the SSL client key file for PostgreSQL.
 *             sslcrl?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The file name of the SSL certificate revocation list for PostgreSQL.
 *             pooled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // True to use a pooled server with the oci8/pdo_oracle driver
 *             MultipleActiveResultSets?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Configuring MultipleActiveResultSets for the pdo_sqlsrv driver
 *             use_savepoints?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Use savepoints for nested transactions
 *             instancename?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Optional parameter, complete whether to add the INSTANCE_NAME parameter in the connection. It is generally used to connect to an Oracle RAC server to select the name of a particular instance.
 *             connectstring?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Complete Easy Connect connection descriptor, see https://docs.oracle.com/database/121/NETAG/naming.htm.When using this option, you will still need to provide the user and password parameters, but the other parameters will no longer be used. Note that when using this parameter, the getHost and getPort methods from Doctrine\DBAL\Connection will no longer function as expected.
 *             driver?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "pdo_mysql"
 *             platform_service?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Deprecated: The "platform_service" configuration key is deprecated since doctrine-bundle 2.9. DBAL 4 will not support setting a custom platform via connection params anymore.
 *             auto_commit?: bool|\Symfony\Component\Config\Loader\ParamConfigurator,
 *             schema_filter?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             logging?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *             profiling?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *             profiling_collect_backtrace?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Enables collecting backtraces when profiling is enabled // Default: false
 *             profiling_collect_schema_errors?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Enables collecting schema errors when profiling is enabled // Default: true
 *             disable_type_comments?: bool|\Symfony\Component\Config\Loader\ParamConfigurator,
 *             server_version?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             idle_connection_ttl?: int|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: 600
 *             driver_class?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             wrapper_class?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             keep_slave?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Deprecated: The "keep_slave" configuration key is deprecated since doctrine-bundle 2.2. Use the "keep_replica" configuration key instead.
 *             keep_replica?: bool|\Symfony\Component\Config\Loader\ParamConfigurator,
 *             options?: array<string, mixed>,
 *             mapping_types?: array<string, scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *             default_table_options?: array<string, scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *             schema_manager_factory?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "doctrine.dbal.default_schema_manager_factory"
 *             result_cache?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             slaves?: array<string, array{ // Default: []
 *                 url?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // A URL with connection information; any parameter value parsed from this string will override explicitly set parameters
 *                 dbname?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *                 host?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Defaults to "localhost" at runtime.
 *                 port?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Defaults to null at runtime.
 *                 user?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Defaults to "root" at runtime.
 *                 password?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Defaults to null at runtime.
 *                 override_url?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Deprecated: The "doctrine.dbal.override_url" configuration key is deprecated.
 *                 dbname_suffix?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Adds the given suffix to the configured database name, this option has no effects for the SQLite platform
 *                 application_name?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *                 charset?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *                 path?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *                 memory?: bool|\Symfony\Component\Config\Loader\ParamConfigurator,
 *                 unix_socket?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The unix socket to use for MySQL
 *                 persistent?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // True to use as persistent connection for the ibm_db2 driver
 *                 protocol?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The protocol to use for the ibm_db2 driver (default to TCPIP if omitted)
 *                 service?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // True to use SERVICE_NAME as connection parameter instead of SID for Oracle
 *                 servicename?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Overrules dbname parameter if given and used as SERVICE_NAME or SID connection parameter for Oracle depending on the service parameter.
 *                 sessionMode?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The session mode to use for the oci8 driver
 *                 server?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The name of a running database server to connect to for SQL Anywhere.
 *                 default_dbname?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Override the default database (postgres) to connect to for PostgreSQL connexion.
 *                 sslmode?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Determines whether or with what priority a SSL TCP/IP connection will be negotiated with the server for PostgreSQL.
 *                 sslrootcert?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The name of a file containing SSL certificate authority (CA) certificate(s). If the file exists, the server's certificate will be verified to be signed by one of these authorities.
 *                 sslcert?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The path to the SSL client certificate file for PostgreSQL.
 *                 sslkey?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The path to the SSL client key file for PostgreSQL.
 *                 sslcrl?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The file name of the SSL certificate revocation list for PostgreSQL.
 *                 pooled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // True to use a pooled server with the oci8/pdo_oracle driver
 *                 MultipleActiveResultSets?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Configuring MultipleActiveResultSets for the pdo_sqlsrv driver
 *                 use_savepoints?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Use savepoints for nested transactions
 *                 instancename?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Optional parameter, complete whether to add the INSTANCE_NAME parameter in the connection. It is generally used to connect to an Oracle RAC server to select the name of a particular instance.
 *                 connectstring?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Complete Easy Connect connection descriptor, see https://docs.oracle.com/database/121/NETAG/naming.htm.When using this option, you will still need to provide the user and password parameters, but the other parameters will no longer be used. Note that when using this parameter, the getHost and getPort methods from Doctrine\DBAL\Connection will no longer function as expected.
 *             }>,
 *             replicas?: array<string, array{ // Default: []
 *                 url?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // A URL with connection information; any parameter value parsed from this string will override explicitly set parameters
 *                 dbname?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *                 host?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Defaults to "localhost" at runtime.
 *                 port?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Defaults to null at runtime.
 *                 user?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Defaults to "root" at runtime.
 *                 password?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Defaults to null at runtime.
 *                 override_url?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Deprecated: The "doctrine.dbal.override_url" configuration key is deprecated.
 *                 dbname_suffix?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Adds the given suffix to the configured database name, this option has no effects for the SQLite platform
 *                 application_name?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *                 charset?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *                 path?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *                 memory?: bool|\Symfony\Component\Config\Loader\ParamConfigurator,
 *                 unix_socket?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The unix socket to use for MySQL
 *                 persistent?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // True to use as persistent connection for the ibm_db2 driver
 *                 protocol?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The protocol to use for the ibm_db2 driver (default to TCPIP if omitted)
 *                 service?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // True to use SERVICE_NAME as connection parameter instead of SID for Oracle
 *                 servicename?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Overrules dbname parameter if given and used as SERVICE_NAME or SID connection parameter for Oracle depending on the service parameter.
 *                 sessionMode?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The session mode to use for the oci8 driver
 *                 server?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The name of a running database server to connect to for SQL Anywhere.
 *                 default_dbname?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Override the default database (postgres) to connect to for PostgreSQL connexion.
 *                 sslmode?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Determines whether or with what priority a SSL TCP/IP connection will be negotiated with the server for PostgreSQL.
 *                 sslrootcert?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The name of a file containing SSL certificate authority (CA) certificate(s). If the file exists, the server's certificate will be verified to be signed by one of these authorities.
 *                 sslcert?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The path to the SSL client certificate file for PostgreSQL.
 *                 sslkey?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The path to the SSL client key file for PostgreSQL.
 *                 sslcrl?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The file name of the SSL certificate revocation list for PostgreSQL.
 *                 pooled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // True to use a pooled server with the oci8/pdo_oracle driver
 *                 MultipleActiveResultSets?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Configuring MultipleActiveResultSets for the pdo_sqlsrv driver
 *                 use_savepoints?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Use savepoints for nested transactions
 *                 instancename?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Optional parameter, complete whether to add the INSTANCE_NAME parameter in the connection. It is generally used to connect to an Oracle RAC server to select the name of a particular instance.
 *                 connectstring?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Complete Easy Connect connection descriptor, see https://docs.oracle.com/database/121/NETAG/naming.htm.When using this option, you will still need to provide the user and password parameters, but the other parameters will no longer be used. Note that when using this parameter, the getHost and getPort methods from Doctrine\DBAL\Connection will no longer function as expected.
 *             }>,
 *         }>,
 *     },
 *     orm?: array{
 *         default_entity_manager?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *         auto_generate_proxy_classes?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Auto generate mode possible values are: "NEVER", "ALWAYS", "FILE_NOT_EXISTS", "EVAL", "FILE_NOT_EXISTS_OR_CHANGED", this option is ignored when the "enable_native_lazy_objects" option is true // Default: false
 *         enable_lazy_ghost_objects?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Enables the new implementation of proxies based on lazy ghosts instead of using the legacy implementation // Default: true
 *         enable_native_lazy_objects?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Enables the new native implementation of PHP lazy objects instead of generated proxies // Default: false
 *         proxy_dir?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Configures the path where generated proxy classes are saved when using non-native lazy objects, this option is ignored when the "enable_native_lazy_objects" option is true // Default: "%kernel.build_dir%/doctrine/orm/Proxies"
 *         proxy_namespace?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Defines the root namespace for generated proxy classes when using non-native lazy objects, this option is ignored when the "enable_native_lazy_objects" option is true // Default: "Proxies"
 *         controller_resolver?: bool|array{
 *             enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *             auto_mapping?: bool|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Set to false to disable using route placeholders as lookup criteria when the primary key doesn't match the argument name // Default: null
 *             evict_cache?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Set to true to fetch the entity from the database instead of using the cache, if any // Default: false
 *         },
 *         entity_managers?: array<string, array{ // Default: []
 *             query_cache_driver?: string|array{
 *                 type?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *                 id?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *                 pool?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             },
 *             metadata_cache_driver?: string|array{
 *                 type?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *                 id?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *                 pool?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             },
 *             result_cache_driver?: string|array{
 *                 type?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *                 id?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *                 pool?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             },
 *             entity_listeners?: array{
 *                 entities?: array<string, array{ // Default: []
 *                     listeners?: array<string, array{ // Default: []
 *                         events?: list<array{ // Default: []
 *                             type?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *                             method?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *                         }>,
 *                     }>,
 *                 }>,
 *             },
 *             connection?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             class_metadata_factory_name?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "Doctrine\\ORM\\Mapping\\ClassMetadataFactory"
 *             default_repository_class?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "Doctrine\\ORM\\EntityRepository"
 *             auto_mapping?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: false
 *             naming_strategy?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "doctrine.orm.naming_strategy.default"
 *             quote_strategy?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "doctrine.orm.quote_strategy.default"
 *             typed_field_mapper?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "doctrine.orm.typed_field_mapper.default"
 *             entity_listener_resolver?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *             fetch_mode_subselect_batch_size?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             repository_factory?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "doctrine.orm.container_repository_factory"
 *             schema_ignore_classes?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *             report_fields_where_declared?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Set to "true" to opt-in to the new mapping driver mode that was added in Doctrine ORM 2.16 and will be mandatory in ORM 3.0. See https://github.com/doctrine/orm/pull/10455. // Default: true
 *             validate_xml_mapping?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Set to "true" to opt-in to the new mapping driver mode that was added in Doctrine ORM 2.14. See https://github.com/doctrine/orm/pull/6728. // Default: false
 *             second_level_cache?: array{
 *                 region_cache_driver?: string|array{
 *                     type?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *                     id?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *                     pool?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *                 },
 *                 region_lock_lifetime?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: 60
 *                 log_enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *                 region_lifetime?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: 3600
 *                 enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *                 factory?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *                 regions?: array<string, array{ // Default: []
 *                     cache_driver?: string|array{
 *                         type?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *                         id?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *                         pool?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *                     },
 *                     lock_path?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "%kernel.cache_dir%/doctrine/orm/slc/filelock"
 *                     lock_lifetime?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: 60
 *                     type?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "default"
 *                     lifetime?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: 0
 *                     service?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *                     name?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *                 }>,
 *                 loggers?: array<string, array{ // Default: []
 *                     name?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *                     service?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *                 }>,
 *             },
 *             hydrators?: array<string, scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *             mappings?: array<string, bool|string|array{ // Default: []
 *                 mapping?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: true
 *                 type?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *                 dir?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *                 alias?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *                 prefix?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *                 is_bundle?: bool|\Symfony\Component\Config\Loader\ParamConfigurator,
 *             }>,
 *             dql?: array{
 *                 string_functions?: array<string, scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *                 numeric_functions?: array<string, scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *                 datetime_functions?: array<string, scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *             },
 *             filters?: array<string, string|array{ // Default: []
 *                 class: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *                 enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *                 parameters?: array<string, mixed>,
 *             }>,
 *             identity_generation_preferences?: array<string, scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         }>,
 *         resolve_target_entities?: array<string, scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *     },
 * }
 * @psalm-type DoctrineMigrationsConfig = array{
 *     enable_service_migrations?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Whether to enable fetching migrations from the service container. // Default: false
 *     migrations_paths?: array<string, scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *     services?: array<string, scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *     factories?: array<string, scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *     storage?: array{ // Storage to use for migration status metadata.
 *         table_storage?: array{ // The default metadata storage, implemented as a table in the database.
 *             table_name?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *             version_column_name?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *             version_column_length?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *             executed_at_column_name?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *             execution_time_column_name?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *         },
 *     },
 *     migrations?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *     connection?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Connection name to use for the migrations database. // Default: null
 *     em?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Entity manager name to use for the migrations database (available when doctrine/orm is installed). // Default: null
 *     all_or_nothing?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Run all migrations in a transaction. // Default: false
 *     check_database_platform?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Adds an extra check in the generated migrations to allow execution only on the same platform as they were initially generated on. // Default: true
 *     custom_template?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Custom template path for generated migration classes. // Default: null
 *     organize_migrations?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Organize migrations mode. Possible values are: "BY_YEAR", "BY_YEAR_AND_MONTH", false // Default: false
 *     enable_profiler?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Whether or not to enable the profiler collector to calculate and visualize migration status. This adds some queries overhead. // Default: false
 *     transactional?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Whether or not to wrap migrations in a single transaction. // Default: true
 * }
 * @psalm-type NelmioCorsConfig = array{
 *     defaults?: array{
 *         allow_credentials?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *         allow_origin?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         allow_headers?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         allow_methods?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         allow_private_network?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *         expose_headers?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         max_age?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: 0
 *         hosts?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         origin_regex?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *         forced_allow_origin_value?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *         skip_same_as_origin?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *     },
 *     paths?: array<string, array{ // Default: []
 *         allow_credentials?: bool|\Symfony\Component\Config\Loader\ParamConfigurator,
 *         allow_origin?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         allow_headers?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         allow_methods?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         allow_private_network?: bool|\Symfony\Component\Config\Loader\ParamConfigurator,
 *         expose_headers?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         max_age?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: 0
 *         hosts?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         origin_regex?: bool|\Symfony\Component\Config\Loader\ParamConfigurator,
 *         forced_allow_origin_value?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *         skip_same_as_origin?: bool|\Symfony\Component\Config\Loader\ParamConfigurator,
 *     }>,
 * }
 * @psalm-type SecurityConfig = array{
 *     access_denied_url?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *     session_fixation_strategy?: "none"|"migrate"|"invalidate"|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: "migrate"
 *     hide_user_not_found?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Deprecated: The "hide_user_not_found" option is deprecated and will be removed in 8.0. Use the "expose_security_errors" option instead.
 *     expose_security_errors?: \Symfony\Component\Security\Http\Authentication\ExposeSecurityLevel::None|\Symfony\Component\Security\Http\Authentication\ExposeSecurityLevel::AccountStatus|\Symfony\Component\Security\Http\Authentication\ExposeSecurityLevel::All|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: "none"
 *     erase_credentials?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *     access_decision_manager?: array{
 *         strategy?: "affirmative"|"consensus"|"unanimous"|"priority"|\Symfony\Component\Config\Loader\ParamConfigurator,
 *         service?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *         strategy_service?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *         allow_if_all_abstain?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *         allow_if_equal_granted_denied?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *     },
 *     password_hashers?: array<string, string|array{ // Default: []
 *         algorithm?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *         migrate_from?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         hash_algorithm?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Name of hashing algorithm for PBKDF2 (i.e. sha256, sha512, etc..) See hash_algos() for a list of supported algorithms. // Default: "sha512"
 *         key_length?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: 40
 *         ignore_case?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *         encode_as_base64?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *         iterations?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: 5000
 *         cost?: int|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: null
 *         memory_cost?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *         time_cost?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *         id?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *     }>,
 *     providers?: array<string, array{ // Default: []
 *         id?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *         chain?: array{
 *             providers?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         },
 *         entity?: array{
 *             class: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The full entity class name of your user class.
 *             property?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *             manager_name?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *         },
 *         memory?: array{
 *             users?: array<string, array{ // Default: []
 *                 password?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *                 roles?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *             }>,
 *         },
 *         ldap?: array{
 *             service: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             base_dn: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             search_dn?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *             search_password?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *             extra_fields?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *             default_roles?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *             role_fetcher?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *             uid_key?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "sAMAccountName"
 *             filter?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "({uid_key}={user_identifier})"
 *             password_attribute?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *         },
 *         lexik_jwt?: array{
 *             class?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "Lexik\\Bundle\\JWTAuthenticationBundle\\Security\\User\\JWTUser"
 *         },
 *     }>,
 *     firewalls: array<string, array{ // Default: []
 *         pattern?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *         host?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *         methods?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         security?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *         user_checker?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The UserChecker to use when authenticating users in this firewall. // Default: "security.user_checker"
 *         request_matcher?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *         access_denied_url?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *         access_denied_handler?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *         entry_point?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // An enabled authenticator name or a service id that implements "Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface".
 *         provider?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *         stateless?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *         lazy?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *         context?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *         logout?: array{
 *             enable_csrf?: bool|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *             csrf_token_id?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "logout"
 *             csrf_parameter?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "_csrf_token"
 *             csrf_token_manager?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             path?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "/logout"
 *             target?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "/"
 *             invalidate_session?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *             clear_site_data?: list<"*"|"cache"|"cookies"|"storage"|"executionContexts"|\Symfony\Component\Config\Loader\ParamConfigurator>,
 *             delete_cookies?: array<string, array{ // Default: []
 *                 path?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *                 domain?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *                 secure?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: false
 *                 samesite?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *                 partitioned?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: false
 *             }>,
 *         },
 *         switch_user?: array{
 *             provider?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             parameter?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "_switch_user"
 *             role?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "ROLE_ALLOWED_TO_SWITCH"
 *             target_route?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *         },
 *         required_badges?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         custom_authenticators?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         login_throttling?: array{
 *             limiter?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // A service id implementing "Symfony\Component\HttpFoundation\RateLimiter\RequestRateLimiterInterface".
 *             max_attempts?: int|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: 5
 *             interval?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "1 minute"
 *             lock_factory?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The service ID of the lock factory used by the login rate limiter (or null to disable locking). // Default: null
 *             cache_pool?: string|\Symfony\Component\Config\Loader\ParamConfigurator, // The cache pool to use for storing the limiter state // Default: "cache.rate_limiter"
 *             storage_service?: string|\Symfony\Component\Config\Loader\ParamConfigurator, // The service ID of a custom storage implementation, this precedes any configured "cache_pool" // Default: null
 *         },
 *         x509?: array{
 *             provider?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             user?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "SSL_CLIENT_S_DN_Email"
 *             credentials?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "SSL_CLIENT_S_DN"
 *             user_identifier?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "emailAddress"
 *         },
 *         remote_user?: array{
 *             provider?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             user?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "REMOTE_USER"
 *         },
 *         jwt?: array{
 *             provider?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *             authenticator?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "lexik_jwt_authentication.security.jwt_authenticator"
 *         },
 *         login_link?: array{
 *             check_route: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Route that will validate the login link - e.g. "app_login_link_verify".
 *             check_post_only?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // If true, only HTTP POST requests to "check_route" will be handled by the authenticator. // Default: false
 *             signature_properties: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *             lifetime?: int|\Symfony\Component\Config\Loader\ParamConfigurator, // The lifetime of the login link in seconds. // Default: 600
 *             max_uses?: int|\Symfony\Component\Config\Loader\ParamConfigurator, // Max number of times a login link can be used - null means unlimited within lifetime. // Default: null
 *             used_link_cache?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Cache service id used to expired links of max_uses is set.
 *             success_handler?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // A service id that implements Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface.
 *             failure_handler?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // A service id that implements Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface.
 *             provider?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The user provider to load users from.
 *             secret?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "%kernel.secret%"
 *             always_use_default_target_path?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *             default_target_path?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "/"
 *             login_path?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "/login"
 *             target_path_parameter?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "_target_path"
 *             use_referer?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *             failure_path?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *             failure_forward?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *             failure_path_parameter?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "_failure_path"
 *         },
 *         form_login?: array{
 *             provider?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             remember_me?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *             success_handler?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             failure_handler?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             check_path?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "/login_check"
 *             use_forward?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *             login_path?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "/login"
 *             username_parameter?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "_username"
 *             password_parameter?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "_password"
 *             csrf_parameter?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "_csrf_token"
 *             csrf_token_id?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "authenticate"
 *             enable_csrf?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *             post_only?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *             form_only?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *             always_use_default_target_path?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *             default_target_path?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "/"
 *             target_path_parameter?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "_target_path"
 *             use_referer?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *             failure_path?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *             failure_forward?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *             failure_path_parameter?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "_failure_path"
 *         },
 *         form_login_ldap?: array{
 *             provider?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             remember_me?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *             success_handler?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             failure_handler?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             check_path?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "/login_check"
 *             use_forward?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *             login_path?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "/login"
 *             username_parameter?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "_username"
 *             password_parameter?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "_password"
 *             csrf_parameter?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "_csrf_token"
 *             csrf_token_id?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "authenticate"
 *             enable_csrf?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *             post_only?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *             form_only?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *             always_use_default_target_path?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *             default_target_path?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "/"
 *             target_path_parameter?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "_target_path"
 *             use_referer?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *             failure_path?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *             failure_forward?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *             failure_path_parameter?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "_failure_path"
 *             service?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "ldap"
 *             dn_string?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "{user_identifier}"
 *             query_string?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             search_dn?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: ""
 *             search_password?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: ""
 *         },
 *         json_login?: array{
 *             provider?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             remember_me?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *             success_handler?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             failure_handler?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             check_path?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "/login_check"
 *             use_forward?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *             login_path?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "/login"
 *             username_path?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "username"
 *             password_path?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "password"
 *         },
 *         json_login_ldap?: array{
 *             provider?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             remember_me?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *             success_handler?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             failure_handler?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             check_path?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "/login_check"
 *             use_forward?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *             login_path?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "/login"
 *             username_path?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "username"
 *             password_path?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "password"
 *             service?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "ldap"
 *             dn_string?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "{user_identifier}"
 *             query_string?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             search_dn?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: ""
 *             search_password?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: ""
 *         },
 *         access_token?: array{
 *             provider?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             remember_me?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *             success_handler?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             failure_handler?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             realm?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *             token_extractors?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *             token_handler: string|array{
 *                 id?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *                 oidc_user_info?: string|array{
 *                     base_uri: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Base URI of the userinfo endpoint on the OIDC server, or the OIDC server URI to use the discovery (require "discovery" to be configured).
 *                     discovery?: array{ // Enable the OIDC discovery.
 *                         cache?: array{
 *                             id: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Cache service id to use to cache the OIDC discovery configuration.
 *                         },
 *                     },
 *                     claim?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Claim which contains the user identifier (e.g. sub, email, etc.). // Default: "sub"
 *                     client?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // HttpClient service id to use to call the OIDC server.
 *                 },
 *                 oidc?: array{
 *                     discovery?: array{ // Enable the OIDC discovery.
 *                         base_uri: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *                         cache?: array{
 *                             id: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Cache service id to use to cache the OIDC discovery configuration.
 *                         },
 *                     },
 *                     claim?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Claim which contains the user identifier (e.g.: sub, email..). // Default: "sub"
 *                     audience: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Audience set in the token, for validation purpose.
 *                     issuers: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *                     algorithm?: array<mixed>,
 *                     algorithms: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *                     key?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Deprecated: The "key" option is deprecated and will be removed in 8.0. Use the "keyset" option instead. // JSON-encoded JWK used to sign the token (must contain a "kty" key).
 *                     keyset?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // JSON-encoded JWKSet used to sign the token (must contain a list of valid public keys).
 *                     encryption?: bool|array{
 *                         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *                         enforce?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // When enabled, the token shall be encrypted. // Default: false
 *                         algorithms: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *                         keyset: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // JSON-encoded JWKSet used to decrypt the token (must contain a list of valid private keys).
 *                     },
 *                 },
 *                 cas?: array{
 *                     validation_url: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // CAS server validation URL
 *                     prefix?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // CAS prefix // Default: "cas"
 *                     http_client?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // HTTP Client service // Default: null
 *                 },
 *                 oauth2?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             },
 *         },
 *         http_basic?: array{
 *             provider?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             realm?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "Secured Area"
 *         },
 *         http_basic_ldap?: array{
 *             provider?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             realm?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "Secured Area"
 *             service?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "ldap"
 *             dn_string?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "{user_identifier}"
 *             query_string?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             search_dn?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: ""
 *             search_password?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: ""
 *         },
 *         remember_me?: array{
 *             secret?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "%kernel.secret%"
 *             service?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null,
 *             user_providers?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *             catch_exceptions?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *             signature_properties?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *             token_provider?: string|array{
 *                 service?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The service ID of a custom remember-me token provider.
 *                 doctrine?: bool|array{
 *                     enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *                     connection?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *                 },
 *             },
 *             token_verifier?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The service ID of a custom rememberme token verifier.
 *             name?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "REMEMBERME"
 *             lifetime?: int|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: 31536000
 *             path?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "/"
 *             domain?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *             secure?: true|false|"auto"|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *             httponly?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *             samesite?: null|"lax"|"strict"|"none"|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: null
 *             always_remember_me?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *             remember_me_parameter?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "_remember_me"
 *         },
 *     }>,
 *     access_control?: list<array{ // Default: []
 *         request_matcher?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *         requires_channel?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *         path?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Use the urldecoded format. // Default: null
 *         host?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *         port?: int|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: null
 *         ips?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         attributes?: array<string, scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         route?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *         methods?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         allow_if?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *         roles?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *     }>,
 *     role_hierarchy?: array<string, string|list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>>,
 * }
 * @psalm-type LexikJwtAuthenticationConfig = array{
 *     public_key?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The key used to sign tokens (useless for HMAC). If not set, the key will be automatically computed from the secret key. // Default: null
 *     additional_public_keys?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *     secret_key?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The key used to sign tokens. It can be a raw secret (for HMAC), a raw RSA/ECDSA key or the path to a file itself being plaintext or PEM. // Default: null
 *     pass_phrase?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The key passphrase (useless for HMAC) // Default: ""
 *     token_ttl?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: 3600
 *     allow_no_expiration?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Allow tokens without "exp" claim (i.e. indefinitely valid, no lifetime) to be considered valid. Caution: usage of this should be rare. // Default: false
 *     clock_skew?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: 0
 *     encoder?: array{
 *         service?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "lexik_jwt_authentication.encoder.lcobucci"
 *         signature_algorithm?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "RS256"
 *     },
 *     user_id_claim?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "username"
 *     token_extractors?: array{
 *         authorization_header?: bool|array{
 *             enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: true
 *             prefix?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "Bearer"
 *             name?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "Authorization"
 *         },
 *         cookie?: bool|array{
 *             enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *             name?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "BEARER"
 *         },
 *         query_parameter?: bool|array{
 *             enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *             name?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "bearer"
 *         },
 *         split_cookie?: bool|array{
 *             enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *             cookies?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *         },
 *     },
 *     remove_token_from_body_when_cookies_used?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: true
 *     set_cookies?: array<string, array{ // Default: []
 *         lifetime?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The cookie lifetime. If null, the "token_ttl" option value will be used // Default: null
 *         samesite?: "none"|"lax"|"strict"|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: "lax"
 *         path?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: "/"
 *         domain?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: null
 *         secure?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: true
 *         httpOnly?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: true
 *         partitioned?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Default: false
 *         split?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *     }>,
 *     api_platform?: bool|array{ // API Platform compatibility: add check_path in OpenAPI documentation.
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *         check_path?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The login check path to add in OpenAPI. // Default: null
 *         username_path?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The path to the username in the JSON body. // Default: null
 *         password_path?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The path to the password in the JSON body. // Default: null
 *     },
 *     access_token_issuance?: bool|array{
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *         signature?: array{
 *             algorithm: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The algorithm use to sign the access tokens.
 *             key: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The signature key. It shall be JWK encoded.
 *         },
 *         encryption?: bool|array{
 *             enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *             key_encryption_algorithm: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The key encryption algorithm is used to encrypt the token.
 *             content_encryption_algorithm: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The key encryption algorithm is used to encrypt the token.
 *             key: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The encryption key. It shall be JWK encoded.
 *         },
 *     },
 *     access_token_verification?: bool|array{
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *         signature?: array{
 *             header_checkers?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *             claim_checkers?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *             mandatory_claims?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *             allowed_algorithms?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *             keyset: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The signature keyset. It shall be JWKSet encoded.
 *         },
 *         encryption?: bool|array{
 *             enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *             continue_on_decryption_failure?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // If enable, non-encrypted tokens or tokens that failed during decryption or verification processes are accepted. // Default: false
 *             header_checkers?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *             allowed_key_encryption_algorithms?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *             allowed_content_encryption_algorithms?: list<scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null>,
 *             keyset: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // The encryption keyset. It shall be JWKSet encoded.
 *         },
 *     },
 *     blocklist_token?: bool|array{
 *         enabled?: bool|\Symfony\Component\Config\Loader\ParamConfigurator, // Default: false
 *         cache?: scalar|\Symfony\Component\Config\Loader\ParamConfigurator|null, // Storage to track blocked tokens // Default: "cache.app"
 *     },
 * }
 * @psalm-type ConfigType = array{
 *     imports?: ImportsConfig,
 *     parameters?: ParametersConfig,
 *     services?: ServicesConfig,
 *     framework?: FrameworkConfig,
 *     doctrine?: DoctrineConfig,
 *     doctrine_migrations?: DoctrineMigrationsConfig,
 *     nelmio_cors?: NelmioCorsConfig,
 *     security?: SecurityConfig,
 *     lexik_jwt_authentication?: LexikJwtAuthenticationConfig,
 *     "when@prod"?: array{
 *         imports?: ImportsConfig,
 *         parameters?: ParametersConfig,
 *         services?: ServicesConfig,
 *         framework?: FrameworkConfig,
 *         doctrine?: DoctrineConfig,
 *         doctrine_migrations?: DoctrineMigrationsConfig,
 *         nelmio_cors?: NelmioCorsConfig,
 *         security?: SecurityConfig,
 *         lexik_jwt_authentication?: LexikJwtAuthenticationConfig,
 *     },
 *     "when@test"?: array{
 *         imports?: ImportsConfig,
 *         parameters?: ParametersConfig,
 *         services?: ServicesConfig,
 *         framework?: FrameworkConfig,
 *         doctrine?: DoctrineConfig,
 *         doctrine_migrations?: DoctrineMigrationsConfig,
 *         nelmio_cors?: NelmioCorsConfig,
 *         security?: SecurityConfig,
 *         lexik_jwt_authentication?: LexikJwtAuthenticationConfig,
 *     },
 *     ...<string, ExtensionType|array{ // extra keys must follow the when@%env% pattern or match an extension alias
 *         imports?: ImportsConfig,
 *         parameters?: ParametersConfig,
 *         services?: ServicesConfig,
 *         ...<string, ExtensionType>,
 *     }>
 * }
 */
final class App
{
    /**
     * @param ConfigType $config
     *
     * @psalm-return ConfigType
     */
    public static function config(array $config): array
    {
        return AppReference::config($config);
    }
}

namespace Symfony\Component\Routing\Loader\Configurator;

/**
 * This class provides array-shapes for configuring the routes of an application.
 *
 * Example:
 *
 *     ```php
 *     // config/routes.php
 *     namespace Symfony\Component\Routing\Loader\Configurator;
 *
 *     return Routes::config([
 *         'controllers' => [
 *             'resource' => 'routing.controllers',
 *         ],
 *     ]);
 *     ```
 *
 * @psalm-type RouteConfig = array{
 *     path: string|array<string,string>,
 *     controller?: string,
 *     methods?: string|list<string>,
 *     requirements?: array<string,string>,
 *     defaults?: array<string,mixed>,
 *     options?: array<string,mixed>,
 *     host?: string|array<string,string>,
 *     schemes?: string|list<string>,
 *     condition?: string,
 *     locale?: string,
 *     format?: string,
 *     utf8?: bool,
 *     stateless?: bool,
 * }
 * @psalm-type ImportConfig = array{
 *     resource: string,
 *     type?: string,
 *     exclude?: string|list<string>,
 *     prefix?: string|array<string,string>,
 *     name_prefix?: string,
 *     trailing_slash_on_root?: bool,
 *     controller?: string,
 *     methods?: string|list<string>,
 *     requirements?: array<string,string>,
 *     defaults?: array<string,mixed>,
 *     options?: array<string,mixed>,
 *     host?: string|array<string,string>,
 *     schemes?: string|list<string>,
 *     condition?: string,
 *     locale?: string,
 *     format?: string,
 *     utf8?: bool,
 *     stateless?: bool,
 * }
 * @psalm-type AliasConfig = array{
 *     alias: string,
 *     deprecated?: array{package:string, version:string, message?:string},
 * }
 * @psalm-type RoutesConfig = array{
 *     "when@prod"?: array<string, RouteConfig|ImportConfig|AliasConfig>,
 *     "when@test"?: array<string, RouteConfig|ImportConfig|AliasConfig>,
 *     ...<string, RouteConfig|ImportConfig|AliasConfig>
 * }
 */
final class Routes
{
    /**
     * @param RoutesConfig $config
     *
     * @psalm-return RoutesConfig
     */
    public static function config(array $config): array
    {
        return $config;
    }
}
