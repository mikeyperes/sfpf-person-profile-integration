<?php

declare( strict_types=1 );

namespace SFPF\PersonProfile\Migrations;

use Hexa\PluginCore\Fields\Field;
use SFPF\PersonProfile\Person\Links;
use SFPF\PersonProfile\Person\PersonFields;
use SFPF\PersonProfile\Person\PersonResolver;

defined( 'ABSPATH' ) || exit;

/** Older profiles kept articles as one link per line; they become rows with a title, source and link. */
final class ArticlesRepeaterMigration implements Migration {
    public function id(): string {
        return 'articles-repeater';
    }

    public function migrate(): bool {
        if ( get_option( 'sfpf_articles_migration_done', false ) ) {
            return true;
        }
        $user_id = PersonResolver::user_id();
        $value   = $user_id > 0 ? Field::get( PersonFields::ARTICLES, 'user_' . $user_id ) : null;
        if ( is_string( $value ) && '' !== trim( $value ) ) {
            $rows = Links::rows( $value );
            if ( [] !== $rows ) {
                Field::update( PersonFields::ARTICLES, $rows, 'user_' . $user_id );
            }
        }
        update_option( 'sfpf_articles_migration_done', true, false );
        return true;
    }
}
