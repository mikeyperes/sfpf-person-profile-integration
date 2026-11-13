<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Admin\Tabs;

use Hexa\PluginCore\ContentTypes\ContentTypeRenderer;
use Hexa\PluginCore\FieldStructures\AcfFieldGroupRenderer;
use SFPF\PersonProfile\ContentTypes\ContentTypeCatalog;

defined( 'ABSPATH' ) || exit;

/**
 * Content types and field groups through Hexa WP Core's renderers. One class
 * serves the main Content Types tab and the Awards and Exhibitions tabs,
 * which each show one type.
 */
final class ContentTypesTab implements Tab {
    /** @param list<string> $only @param list<string> $except */
    public function __construct(
        private readonly string $id = 'content-types',
        private readonly string $label = 'Content Types',
        private readonly array $only = [],
        private readonly array $except = [ 'award', 'exhibition' ],
        private readonly string $description = 'The content types a person website can use: organizations, books, press releases, interviews, contributing profiles and quotes. Each is off until turned on here; its fields and schema come with it. Awards and Exhibitions have their own tabs.'
    ) {
    }

    public function id(): string {
        return $this->id;
    }

    public function label(): string {
        return $this->label;
    }

    public function group(): string {
        return 'Content';
    }

    public function render(): void {
        echo ( new ContentTypeRenderer() )->render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Core renderer escapes.
            ContentTypeCatalog::view( $this->only, $this->except ),
            [ 'title' => $this->label, 'description' => $this->description, 'persist_prefix' => 'sfpf-' . $this->id ]
        );
        if ( 'content-types' === $this->id ) {
            echo ( new AcfFieldGroupRenderer() )->render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Core renderer escapes.
                ContentTypeCatalog::field_groups(),
                [ 'title' => 'Person Fields', 'description' => 'The person\'s own fields on their WordPress user. On from the start; turning them off hides them on the profile screen but keeps the data.', 'persist_prefix' => 'sfpf-field-groups' ]
            );
        }
    }
}
