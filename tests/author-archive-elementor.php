<?php

declare( strict_types=1 );

namespace ElementorPro\Modules\ThemeBuilder {
    final class Module {
        public static $instance;

        public static function instance() {
            return self::$instance;
        }
    }
}

namespace {
    define( 'ABSPATH', __DIR__ );

    function add_filter() {}
    function add_action() {}

    require dirname( __DIR__ ) . '/includes/frontend/author-archive.php';

    final class SFPF_Test_Conditions_Manager {
        public array $documents;
        public string $requested_location = '';

        public function __construct( array $documents ) {
            $this->documents = $documents;
        }

        public function get_documents_for_location( string $location ): array {
            $this->requested_location = $location;
            return $this->documents;
        }
    }

    final class SFPF_Test_Theme_Builder_Module {
        private $conditions_manager;

        public function __construct( $conditions_manager ) {
            $this->conditions_manager = $conditions_manager;
        }

        public function get_conditions_manager() {
            return $this->conditions_manager;
        }
    }

    $failures = [];
    $assert   = static function ( bool $condition, string $message ) use ( &$failures ): void {
        if ( ! $condition ) {
            $failures[] = $message;
        }
    };

    $matching_manager = new SFPF_Test_Conditions_Manager( [ 261076 => new \stdClass() ] );
    \ElementorPro\Modules\ThemeBuilder\Module::$instance = new SFPF_Test_Theme_Builder_Module( $matching_manager );
    $assert(
        true === \sfpf_person_website\sfpf_author_archive_has_elementor_template(),
        'A matching Elementor archive was not detected.'
    );
    $assert( 'archive' === $matching_manager->requested_location, 'Elementor was queried for the wrong location.' );

    $empty_manager = new SFPF_Test_Conditions_Manager( [] );
    \ElementorPro\Modules\ThemeBuilder\Module::$instance = new SFPF_Test_Theme_Builder_Module( $empty_manager );
    $assert(
        false === \sfpf_person_website\sfpf_author_archive_has_elementor_template(),
        'An empty Elementor archive result should preserve the SFPF fallback.'
    );

    \ElementorPro\Modules\ThemeBuilder\Module::$instance = new \stdClass();
    $assert(
        false === \sfpf_person_website\sfpf_author_archive_has_elementor_template(),
        'A Theme Builder module without a conditions manager should preserve the SFPF fallback.'
    );

    $throwing_manager = new class() {
        public function get_documents_for_location(): array {
            throw new \RuntimeException( 'Condition resolution failed.' );
        }
    };
    \ElementorPro\Modules\ThemeBuilder\Module::$instance = new SFPF_Test_Theme_Builder_Module( $throwing_manager );
    $assert(
        false === \sfpf_person_website\sfpf_author_archive_has_elementor_template(),
        'A Theme Builder exception should preserve the SFPF fallback.'
    );

    if ( [] !== $failures ) {
        foreach ( $failures as $failure ) {
            fwrite( STDERR, 'FAIL: ' . $failure . PHP_EOL );
        }
        exit( 1 );
    }

    echo 'PASS: SFPF author archives defer only when Elementor Theme Builder has a matching archive.' . PHP_EOL;
}
