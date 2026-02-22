<?php
namespace sfpf_person_website;

/**
 * User Schema.org Structured Data ACF Fields
 * 
 * Adds Schema.org structured data fields to WordPress user profiles.
 * Supports both Person and Organization entity types with conditional fields.
 * 
 * FIELDS:
 *   - entity_type (button_group): Person or Organization
 *   - biography (wysiwyg): Full biography (Person + Organization)
 *   - biography_short (wysiwyg): Short biography (Person + Organization)
 *   - mission_statement (wysiwyg): Mission statement (Person + Organization)
 *   - education (repeater): college, wiki_url, year, designation, major (Person only)
 *   - inception_date (text): Founding date (Organization only)
 *   - headquarters (group): location, wiki_url (Organization only)
 *   - sameas (textarea): Schema.org sameAs URLs, one per line
 *
 * SHORTCODES:
 *   [founder id="entity_type"] / [company id="entity_type"]
 *   [founder id="education"] / [founder id="education" format="json"]
 *   [founder id="education" index="0" field="college"]
 *   [company id="inception_date"]
 *   [company id="headquarters_location"] / [company id="headquarters_wiki"]
 *   [founder id="sameas"] / [founder id="sameas" format="json"] / [founder id="sameas" format="ul"]
 *
 * @package sfpf_person_website
 * @since 1.2.0
 */

defined('ABSPATH') || exit;

/**
 * Register ACF User fields: Schema.org Structured Data
 */
function register_user_schema_acf_fields() {
    if (!function_exists('acf_add_local_field_group')) {
        return;
    }

    acf_add_local_field_group([
        'key'                   => 'group_sfpf_user_schema_structures',
        'title'                 => 'Schema.org Structured Data',
        'fields'                => [
            
            // ═══════════════════════════════════════════════════════════════
            // ENTITY TYPE TOGGLE
            // ═══════════════════════════════════════════════════════════════
            [
                'key'               => 'field_sfpf_entity_type',
                'label'             => 'Entity Type',
                'name'              => 'entity_type',
                'type'              => 'button_group',
                'instructions'      => 'Shortcode: <code>[founder id="entity_type"]</code> or <code>[company id="entity_type"]</code>',
                'required'          => 0,
                'conditional_logic' => 0,
                'wrapper'           => ['width' => '100'],
                'choices'           => [
                    'none'         => '⊘ None',
                    'person'       => '👤 Person',
                    'organization' => '🏢 Organization',
                ],
                'default_value'     => 'none',
                'return_format'     => 'value',
                'allow_null'        => 0,
                'layout'            => 'horizontal',
            ],
            
            // ═══════════════════════════════════════════════════════════════
            // GOOGLE KNOWLEDGE GRAPH ID (person OR organization)
            // ═══════════════════════════════════════════════════════════════
            [
                'key'               => 'field_sfpf_knowledge_graph_id',
                'label'             => 'Google Knowledge Graph ID',
                'name'              => 'knowledge_graph_id',
                'type'              => 'text',
                'instructions'      => 'Enter the KGMID (e.g., <code>/g/11gyz2y3lp</code>). If you paste the full Google URL, the ID will be extracted automatically.<br>
<code>[founder id="knowledge_graph_id"]</code> — Raw KGMID<br>
<code>[founder action="display_knowledge_panel"]</code> — Link to Knowledge Panel<br>
<span id="sfpf-kgid-link-display">Enter a KGMID above to see the full Knowledge Panel URL.</span>',
                'required'          => 0,
                'conditional_logic' => [
                    [
                        [
                            'field'    => 'field_sfpf_entity_type',
                            'operator' => '==',
                            'value'    => 'person',
                        ],
                    ],
                    [
                        [
                            'field'    => 'field_sfpf_entity_type',
                            'operator' => '==',
                            'value'    => 'organization',
                        ],
                    ],
                ],
                'wrapper'           => ['width' => '100'],
                'placeholder'       => '/g/11gyz2y3lp',
            ],
            
            // ═══════════════════════════════════════════════════════════════
            // PERSON-ONLY FIELD: additionalName
            // ═══════════════════════════════════════════════════════════════
            [
                'key'               => 'field_sfpf_additional_name',
                'label'             => 'Additional Name',
                'name'              => 'additional_name',
                'type'              => 'text',
                'instructions'      => 'Middle name, nickname, or other additional name. <code>[founder id="additional_name"]</code>',
                'required'          => 0,
                'conditional_logic' => [
                    [
                        [
                            'field'    => 'field_sfpf_entity_type',
                            'operator' => '==',
                            'value'    => 'person',
                        ],
                    ],
                ],
                'wrapper'           => ['width' => '100'],
                'placeholder'       => 'e.g., Mike, Jr., III',
            ],
            
            // ═══════════════════════════════════════════════════════════════
            // SHARED FIELD: alternateNames (person OR organization)
            // ═══════════════════════════════════════════════════════════════
            [
                'key'               => 'field_sfpf_alternate_names',
                'label'             => 'Alternate Names',
                'name'              => 'alternate_names',
                'type'              => 'repeater',
                'instructions'      => 'Other names this person/organization is known by.<br>
<code>[founder id="alternate_names"]</code> or <code>[company id="alternate_names"]</code> — Text list<br>
<code>[founder id="alternate_names" format="json"]</code> — JSON array',
                'required'          => 0,
                'conditional_logic' => [
                    [
                        [
                            'field'    => 'field_sfpf_entity_type',
                            'operator' => '==',
                            'value'    => 'person',
                        ],
                    ],
                    [
                        [
                            'field'    => 'field_sfpf_entity_type',
                            'operator' => '==',
                            'value'    => 'organization',
                        ],
                    ],
                ],
                'wrapper'           => [],
                'layout'            => 'table',
                'pagination'        => 0,
                'min'               => 0,
                'max'               => 10,
                'collapsed'         => '',
                'button_label'      => 'Add Alternate Name',
                'rows_per_page'     => 20,
                'sub_fields'        => [
                    [
                        'key'               => 'field_sfpf_alt_name_value',
                        'label'             => 'Name',
                        'name'              => 'name',
                        'type'              => 'text',
                        'required'          => 0,
                        'wrapper'           => ['width' => '100'],
                        'placeholder'       => 'Alternate name or alias',
                    ],
                ],
            ],
            
            // ═══════════════════════════════════════════════════════════════
            // PERSON FIELD: Location Born
            // ═══════════════════════════════════════════════════════════════
            [
                'key'               => 'field_sfpf_location_born',
                'label'             => 'Location Born',
                'name'              => 'location_born',
                'type'              => 'group',
                'instructions'      => 'Birthplace location for schema.org birthPlace.<br>
<code>[founder action="display_location_born"]</code> — Linked display (default)<br>
<code>[founder action="display_location_born" format="text"]</code> — Plain text, no link<br>
<code>[founder action="display_location_born" format="inline"]</code> — Inline span<br>
<code>[founder id="location_born_location"]</code> — Raw location text<br>
<code>[founder id="location_born_url"]</code> — Raw Wikipedia URL',
                'required'          => 0,
                'conditional_logic' => [
                    [
                        [
                            'field'    => 'field_sfpf_entity_type',
                            'operator' => '==',
                            'value'    => 'person',
                        ],
                    ],
                ],
                'layout'            => 'block',
                'sub_fields'        => [
                    [
                        'key'               => 'field_sfpf_location_born_location',
                        'label'             => 'Location',
                        'name'              => 'location',
                        'type'              => 'text',
                        'required'          => 0,
                        'wrapper'           => ['width' => '50'],
                        'placeholder'       => 'e.g., Chicago, Illinois',
                    ],
                    [
                        'key'               => 'field_sfpf_location_born_wikipedia',
                        'label'             => 'Wikipedia URL',
                        'name'              => 'wikipedia_url',
                        'type'              => 'url',
                        'required'          => 0,
                        'wrapper'           => ['width' => '50'],
                        'placeholder'       => 'https://en.wikipedia.org/wiki/Chicago',
                    ],
                ],
            ],
            
            // ═══════════════════════════════════════════════════════════════
            // PERSON FIELD: Birth Date
            // ═══════════════════════════════════════════════════════════════
            [
                'key'               => 'field_sfpf_birth_date',
                'label'             => 'Birth Date',
                'name'              => 'birth_date',
                'type'              => 'text',
                'instructions'      => 'ISO format preferred (YYYY-MM-DD). <code>[founder id="birth_date"]</code>',
                'required'          => 0,
                'conditional_logic' => [
                    [
                        [
                            'field'    => 'field_sfpf_entity_type',
                            'operator' => '==',
                            'value'    => 'person',
                        ],
                    ],
                ],
                'wrapper'           => ['width' => '50'],
                'placeholder'       => '1990-01-13',
            ],
            
            // ═══════════════════════════════════════════════════════════════
            // PERSON FIELD: Nationality (repeater)
            // ═══════════════════════════════════════════════════════════════
            [
                'key'               => 'field_sfpf_nationality',
                'label'             => 'Nationality',
                'name'              => 'nationality',
                'type'              => 'repeater',
                'instructions'      => '<code>[founder id="nationality"]</code> — Comma-separated list<br>
<code>[founder id="nationality" format="json"]</code> — JSON array<br>
<code>[founder action="display_nationality"]</code> — Formatted display',
                'required'          => 0,
                'conditional_logic' => [
                    [
                        [
                            'field'    => 'field_sfpf_entity_type',
                            'operator' => '==',
                            'value'    => 'person',
                        ],
                    ],
                ],
                'wrapper'           => ['width' => '50'],
                'layout'            => 'table',
                'min'               => 0,
                'max'               => 10,
                'button_label'      => 'Add Nationality',
                'sub_fields'        => [
                    [
                        'key'               => 'field_sfpf_nationality_value',
                        'label'             => 'Nationality',
                        'name'              => 'value',
                        'type'              => 'text',
                        'required'          => 0,
                        'wrapper'           => ['width' => '100'],
                        'placeholder'       => 'American',
                    ],
                ],
            ],
            
            // ═══════════════════════════════════════════════════════════════
            // SHARED FIELD: Knowledge Graph Images (person OR organization)
            // ═══════════════════════════════════════════════════════════════
            [
                'key'               => 'field_sfpf_knowledge_graph_images',
                'label'             => 'Knowledge Graph Images',
                'name'              => 'knowledge_graph_images',
                'type'              => 'gallery',
                'instructions'      => 'Images used for the knowledge graph panel. Thumbnails shown below are for display purposes only - full images are stored.<br>
<code>[founder id="knowledge_graph_images"]</code> — Image URLs<br>
<code>[founder id="knowledge_graph_images" format="json"]</code> — Full image data',
                'required'          => 0,
                'conditional_logic' => [
                    [
                        [
                            'field'    => 'field_sfpf_entity_type',
                            'operator' => '==',
                            'value'    => 'person',
                        ],
                    ],
                    [
                        [
                            'field'    => 'field_sfpf_entity_type',
                            'operator' => '==',
                            'value'    => 'organization',
                        ],
                    ],
                ],
                'wrapper'           => [],
                'return_format'     => 'array',
                'library'           => 'all',
                'min'               => 0,
                'max'               => 10,
                'min_width'         => '',
                'min_height'        => '',
                'min_size'          => '',
                'max_width'         => '',
                'max_height'        => '',
                'max_size'          => '',
                'mime_types'        => 'jpg,jpeg,png,webp',
                'insert'            => 'append',
                'preview_size'      => 'thumbnail',
            ],
            
            // ═══════════════════════════════════════════════════════════════
            // SHARED CONTENT FIELDS (shown when entity_type = person OR organization)
            // ═══════════════════════════════════════════════════════════════
            [
                'key'               => 'field_sfpf_person_title',
                'label'             => 'Title',
                'name'              => 'title',
                'type'              => 'wysiwyg',
                'instructions'      => 'Professional title or role. Supports basic HTML.<br><code>[founder id="title"]</code> or <code>[company id="title"]</code>',
                'required'          => 0,
                'conditional_logic' => [
                    [
                        [
                            'field'    => 'field_sfpf_entity_type',
                            'operator' => '==',
                            'value'    => 'person',
                        ],
                    ],
                ],
                'wrapper'           => ['width' => '100'],
                'tabs'              => 'all',
                'toolbar'           => 'basic',
                'media_upload'      => 0,
                'delay'             => 1,
            ],
            [
                'key'               => 'field_sfpf_biography',
                'label'             => 'Biography',
                'name'              => 'biography',
                'type'              => 'wysiwyg',
                'instructions'      => 'Full biography text. <code>[founder id="biography"]</code> or <code>[company id="biography"]</code>',
                'required'          => 0,
                'conditional_logic' => [
                    [
                        [
                            'field'    => 'field_sfpf_entity_type',
                            'operator' => '==',
                            'value'    => 'person',
                        ],
                    ],
                    [
                        [
                            'field'    => 'field_sfpf_entity_type',
                            'operator' => '==',
                            'value'    => 'organization',
                        ],
                    ],
                ],
                'wrapper'           => [],
                'tabs'              => 'all',
                'toolbar'           => 'full',
                'media_upload'      => 1,
                'delay'             => 1,
            ],
            [
                'key'               => 'field_sfpf_biography_short',
                'label'             => 'Biography (Short)',
                'name'              => 'biography_short',
                'type'              => 'wysiwyg',
                'instructions'      => 'Short biography for summaries. <code>[founder id="biography_short"]</code> or <code>[company id="biography_short"]</code>',
                'required'          => 0,
                'conditional_logic' => [
                    [
                        [
                            'field'    => 'field_sfpf_entity_type',
                            'operator' => '==',
                            'value'    => 'person',
                        ],
                    ],
                    [
                        [
                            'field'    => 'field_sfpf_entity_type',
                            'operator' => '==',
                            'value'    => 'organization',
                        ],
                    ],
                ],
                'wrapper'           => [],
                'tabs'              => 'all',
                'toolbar'           => 'basic',
                'media_upload'      => 0,
                'delay'             => 1,
            ],
            [
                'key'               => 'field_sfpf_mission_statement',
                'label'             => 'Mission Statement',
                'name'              => 'mission_statement',
                'type'              => 'wysiwyg',
                'instructions'      => 'Mission statement or organizational purpose. <code>[founder id="mission_statement"]</code> or <code>[company id="mission_statement"]</code>',
                'required'          => 0,
                'conditional_logic' => [
                    [
                        [
                            'field'    => 'field_sfpf_entity_type',
                            'operator' => '==',
                            'value'    => 'person',
                        ],
                    ],
                    [
                        [
                            'field'    => 'field_sfpf_entity_type',
                            'operator' => '==',
                            'value'    => 'organization',
                        ],
                    ],
                ],
                'wrapper'           => [],
                'tabs'              => 'all',
                'toolbar'           => 'full',
                'media_upload'      => 1,
                'delay'             => 1,
            ],
            
            // ═══════════════════════════════════════════════════════════════
            // PERSON-ONLY FIELDS (shown when entity_type = 'person')
            // ═══════════════════════════════════════════════════════════════
            [
                'key'               => 'field_sfpf_professions_repeater',
                'label'             => 'Professions',
                'name'              => 'professions',
                'type'              => 'repeater',
                'instructions'      => 'List of professions/roles.<br>
<code>[founder id="professions"]</code> — Text list<br>
<code>[founder action="display_professions_with_summary"]</code> — With links and content',
                'required'          => 0,
                'conditional_logic' => [
                    [
                        [
                            'field'    => 'field_sfpf_entity_type',
                            'operator' => '==',
                            'value'    => 'person',
                        ],
                    ],
                ],
                'wrapper'           => [],
                'layout'            => 'block',
                'pagination'        => 0,
                'min'               => 0,
                'max'               => 20,
                'collapsed'         => 'field_sfpf_profession_name',
                'button_label'      => 'Add Profession',
                'rows_per_page'     => 20,
                'sub_fields'        => [
                    [
                        'key'               => 'field_sfpf_profession_name',
                        'label'             => 'Profession Name',
                        'name'              => 'name',
                        'type'              => 'text',
                        'required'          => 0,
                        'wrapper'           => ['width' => '50'],
                        'placeholder'       => 'e.g., Entrepreneur, Author',
                    ],
                    [
                        'key'               => 'field_sfpf_profession_page',
                        'label'             => 'Linked Page',
                        'name'              => 'page',
                        'type'              => 'post_object',
                        'required'          => 0,
                        'wrapper'           => ['width' => '50'],
                        'post_type'         => ['page'],
                        'return_format'     => 'id',
                        'allow_null'        => 1,
                    ],
                    [
                        'key'               => 'field_sfpf_profession_summary',
                        'label'             => 'Summary',
                        'name'              => 'summary',
                        'type'              => 'wysiwyg',
                        'required'          => 0,
                        'wrapper'           => ['width' => '100'],
                        'tabs'              => 'all',
                        'toolbar'           => 'basic',
                        'media_upload'      => 0,
                        'delay'             => 1,
                    ],
                ],
            ],
            [
                'key'               => 'field_sfpf_education_repeater',
                'label'             => 'Education History',
                'name'              => 'education',
                'type'              => 'repeater',
                'instructions'      => '<code>[founder id="education"]</code> — HTML list &nbsp;|&nbsp; <code>[founder id="education" format="json"]</code> — JSON<br>
<code>[founder action="display_education"]</code> — Full display with links<br>
<strong>Fields:</strong> college, wiki_url, year, designation, major',
                'required'          => 0,
                'conditional_logic' => [
                    [
                        [
                            'field'    => 'field_sfpf_entity_type',
                            'operator' => '==',
                            'value'    => 'person',
                        ],
                    ],
                ],
                'wrapper'           => [],
                'layout'            => 'block',
                'pagination'        => 0,
                'min'               => 0,
                'max'               => 10,
                'collapsed'         => 'field_sfpf_edu_college',
                'button_label'      => 'Add Education',
                'rows_per_page'     => 20,
                'sub_fields'        => [
                    [
                        'key'               => 'field_sfpf_edu_college',
                        'label'             => 'College / University',
                        'name'              => 'college',
                        'type'              => 'text',
                        'required'          => 0,
                        'wrapper'           => ['width' => '50'],
                        'placeholder'       => 'Harvard University',
                    ],
                    [
                        'key'               => 'field_sfpf_edu_wiki_url',
                        'label'             => 'Wikipedia URL',
                        'name'              => 'wiki_url',
                        'type'              => 'url',
                        'required'          => 0,
                        'wrapper'           => ['width' => '50'],
                        'placeholder'       => 'https://en.wikipedia.org/wiki/...',
                    ],
                    [
                        'key'               => 'field_sfpf_edu_year',
                        'label'             => 'Year',
                        'name'              => 'year',
                        'type'              => 'text',
                        'required'          => 0,
                        'wrapper'           => ['width' => '33'],
                        'placeholder'       => '2015 or 2011-2015',
                    ],
                    [
                        'key'               => 'field_sfpf_edu_designation',
                        'label'             => 'Degree',
                        'name'              => 'designation',
                        'type'              => 'text',
                        'required'          => 0,
                        'wrapper'           => ['width' => '33'],
                        'placeholder'       => 'B.S., M.A., Ph.D.',
                    ],
                    [
                        'key'               => 'field_sfpf_edu_major',
                        'label'             => 'Major / Field',
                        'name'              => 'major',
                        'type'              => 'text',
                        'required'          => 0,
                        'wrapper'           => ['width' => '34'],
                        'placeholder'       => 'Computer Science',
                    ],
                ],
            ],
            
            // ═══════════════════════════════════════════════════════════════
            // ORGANIZATION FIELDS (shown when entity_type = 'organization')
            // ═══════════════════════════════════════════════════════════════
            [
                'key'               => 'field_sfpf_inception_date',
                'label'             => 'Inception Date',
                'name'              => 'inception_date',
                'type'              => 'text',
                'instructions'      => 'When founded. Shortcode: <code>[company id="inception_date"]</code>',
                'required'          => 0,
                'conditional_logic' => [
                    [
                        [
                            'field'    => 'field_sfpf_entity_type',
                            'operator' => '==',
                            'value'    => 'organization',
                        ],
                    ],
                ],
                'wrapper'           => ['width' => '50'],
                'placeholder'       => '2015 or January 1, 2015',
            ],
            
            [
                'key'               => 'field_sfpf_headquarters_group',
                'label'             => 'Headquarters',
                'name'              => 'headquarters',
                'type'              => 'group',
                'instructions'      => '<code>[company id="headquarters_location"]</code> &nbsp;|&nbsp; <code>[company id="headquarters_wiki"]</code>',
                'required'          => 0,
                'conditional_logic' => [
                    [
                        [
                            'field'    => 'field_sfpf_entity_type',
                            'operator' => '==',
                            'value'    => 'organization',
                        ],
                    ],
                ],
                'wrapper'           => [],
                'layout'            => 'block',
                'sub_fields'        => [
                    [
                        'key'               => 'field_sfpf_hq_location',
                        'label'             => 'Location',
                        'name'              => 'location',
                        'type'              => 'text',
                        'required'          => 0,
                        'wrapper'           => ['width' => '50'],
                        'placeholder'       => 'Miami, Florida',
                    ],
                    [
                        'key'               => 'field_sfpf_hq_wiki',
                        'label'             => 'Wikipedia URL',
                        'name'              => 'wiki_url',
                        'type'              => 'url',
                        'required'          => 0,
                        'wrapper'           => ['width' => '50'],
                        'placeholder'       => 'https://en.wikipedia.org/wiki/Miami',
                    ],
                ],
            ],
            
            // ═══════════════════════════════════════════════════════════════
            // SHARED FIELDS (both Person and Organization)
            // ═══════════════════════════════════════════════════════════════
            [
                'key'               => 'field_sfpf_sameas',
                'label'             => 'SameAs URLs',
                'name'              => 'sameas',
                'type'              => 'textarea',
                'instructions'      => 'One URL per line. Used in JSON-LD structured data.<br>
<code>[founder id="sameas"]</code> or <code>[company id="sameas"]</code> — Text<br>
<code>[founder id="sameas" format="json"]</code> — JSON &nbsp;|&nbsp; <code>[founder id="sameas" format="ul"]</code> — HTML list',
                'required'          => 0,
                'conditional_logic' => 0,
                'wrapper'           => [],
                'default_value'     => '',
                'rows'              => 4,
                'placeholder'       => "https://linkedin.com/in/name\nhttps://twitter.com/handle",
                'new_lines'         => '',
            ],
            [
                'key'               => 'field_sfpf_articles_import',
                'label'             => 'Bulk Import Articles',
                'name'              => '',
                'type'              => 'message',
                'instructions'      => '',
                'required'          => 0,
                'conditional_logic' => 0,
                'wrapper'           => [],
                'message'           => '<div id="sfpf-articles-import-area">
<p style="margin:0 0 8px;font-size:12px;color:#666;">Paste article URLs below — plain URLs, comma-separated, or even raw HTML (it will extract <code>href</code> values and link text automatically). Click <strong>Process &amp; Import</strong> to sanitize, fetch titles, extract sources, detect duplicates, and append to the Articles repeater.</p>
<textarea id="sfpf-articles-bulk-input" rows="5" style="width:100%;font-family:monospace;font-size:12px;" placeholder="Paste URLs, HTML, or mixed content:&#10;https://forbes.com/article-1&#10;https://nytimes.com/article-2&#10;Or paste raw HTML with &lt;a href=&quot;...&quot;&gt; tags"></textarea>
<div style="margin-top:8px;display:flex;gap:8px;align-items:center;">
<button type="button" id="sfpf-process-articles" class="button button-primary">⚡ Process &amp; Import</button>
<button type="button" id="sfpf-remove-all-articles" class="button" style="color:#dc2626;border-color:#dc2626;">🗑 Remove All Articles</button>
<span id="sfpf-articles-spinner" class="spinner" style="display:none;visibility:hidden;float:none;margin:0;"></span>
</div>
<div id="sfpf-articles-report" style="display:none;margin-top:12px;background:#1e293b;border:1px solid #334155;border-radius:8px;font-size:12px;font-family:\'SF Mono\',Menlo,Monaco,monospace;line-height:1.6;overflow:hidden;flex-direction:column;max-height:500px;">
<div id="sfpf-articles-report-header" style="padding:12px 16px;border-bottom:1px solid #334155;flex-shrink:0;"></div>
<div id="sfpf-articles-report-body" style="padding:16px;overflow-y:auto;flex:1;color:#e2e8f0;white-space:pre-wrap;"></div>
<div id="sfpf-articles-report-footer" style="padding:12px 16px;border-top:1px solid #334155;flex-shrink:0;"></div>
</div>
</div>',
                'new_lines'         => '',
                'esc_html'          => 0,
            ],
            [
                'key'               => 'field_sfpf_articles',
                'label'             => 'Articles',
                'name'              => 'articles',
                'type'              => 'repeater',
                'instructions'      => '<code>[founder action="display_articles"]</code> — Title list with source badges (default)<br>
<code>[founder action="display_articles" format="titled"]</code> — Titles as links (new tab)<br>
<code>[founder action="display_articles" format="cards"]</code> — Cards with title, source, URL<br>
<code>[founder action="display_articles" format="sources"]</code> — Grouped by source domain<br>
<code>[founder action="display_articles" format="compact"]</code> — One-line per article: title — source<br>
<code>[founder id="articles" format="json"]</code> — JSON array of all articles',
                'required'          => 0,
                'conditional_logic' => 0,
                'wrapper'           => [],
                'layout'            => 'table',
                'pagination'        => 0,
                'min'               => 0,
                'max'               => 100,
                'collapsed'         => 'field_sfpf_article_title',
                'button_label'      => 'Add Article',
                'rows_per_page'     => 20,
                'sub_fields'        => [
                    [
                        'key'               => 'field_sfpf_article_title',
                        'label'             => 'Title',
                        'name'              => 'title',
                        'type'              => 'text',
                        'required'          => 0,
                        'wrapper'           => ['width' => '45'],
                        'placeholder'       => 'Article title (auto-fetched on import)',
                    ],
                    [
                        'key'               => 'field_sfpf_article_source',
                        'label'             => 'Source',
                        'name'              => 'source',
                        'type'              => 'text',
                        'required'          => 0,
                        'wrapper'           => ['width' => '20'],
                        'placeholder'       => 'forbes.com',
                    ],
                    [
                        'key'               => 'field_sfpf_article_url',
                        'label'             => 'URL',
                        'name'              => 'url',
                        'type'              => 'url',
                        'required'          => 0,
                        'wrapper'           => ['width' => '35'],
                        'placeholder'       => 'https://forbes.com/article-slug',
                    ],
                ],
            ],
            
        ],

        'location'              => [
            [
                [
                    'param'     => 'user_form',
                    'operator'  => '==',
                    'value'     => 'all',
                ],
            ],
        ],

        'menu_order'            => 5,
        'position'              => 'normal',
        'style'                 => 'seamless',
        'label_placement'       => 'top',
        'instruction_placement' => 'field',
        'hide_on_screen'        => '',
        'active'                => true,
        'description'           => 'Configure Schema.org structured data for this user profile.',
        'show_in_rest'          => 0,
    ]);
    
    // Add admin CSS for better styling
    add_action('admin_head', __NAMESPACE__ . '\\user_schema_admin_styles');
}

/**
 * Add admin styles for user schema fields
 */
function user_schema_admin_styles() {
    $screen = get_current_screen();
    if (!$screen || ($screen->base !== 'user-edit' && $screen->base !== 'profile')) {
        return;
    }
    ?>
    <style>
        /* Main wrapper styling */
        #acf-group_sfpf_user_schema_structures {
            background: #fff !important;
            border: 1px solid #c3c4c7 !important;
            border-radius: 8px !important;
            margin: 20px 0 !important;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05) !important;
        }
        
        /* Header styling */
        #acf-group_sfpf_user_schema_structures > .postbox-header,
        #acf-group_sfpf_user_schema_structures > h2 {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
            color: #fff !important;
            padding: 12px 15px !important;
            margin: 0 !important;
            border-radius: 8px 8px 0 0 !important;
        }
        
        #acf-group_sfpf_user_schema_structures > .postbox-header .hndle,
        #acf-group_sfpf_user_schema_structures > h2 {
            color: #fff !important;
            font-weight: 600 !important;
        }
        
        /* Content area - white background */
        #acf-group_sfpf_user_schema_structures > .inside,
        #acf-group_sfpf_user_schema_structures .acf-fields {
            background: #fff !important;
            padding: 15px !important;
        }
        
        /* Field labels */
        #acf-group_sfpf_user_schema_structures .acf-label label {
            font-weight: 600 !important;
            color: #1e1e1e !important;
        }
        
        /* Instructions - clean style */
        #acf-group_sfpf_user_schema_structures .acf-field .description,
        #acf-group_sfpf_user_schema_structures .acf-field .acf-instructions {
            background: transparent !important;
            padding: 5px 0 !important;
            color: #666 !important;
            font-size: 12px !important;
        }
        
        #acf-group_sfpf_user_schema_structures .acf-field .acf-instructions code {
            background: #e8f4fc !important;
            color: #0073aa !important;
            padding: 2px 6px !important;
            border-radius: 3px !important;
            font-size: 11px !important;
        }
        
        /* Repeater styling */
        #acf-group_sfpf_user_schema_structures .acf-repeater .acf-row {
            background: #fff !important;
            border: 1px solid #e5e7eb !important;
            border-radius: 6px !important;
            margin-bottom: 10px !important;
        }
        
        #acf-group_sfpf_user_schema_structures .acf-repeater .acf-row .acf-fields {
            padding: 10px !important;
        }
        
        /* Button group styling */
        #acf-group_sfpf_user_schema_structures .acf-button-group label {
            border-radius: 4px !important;
        }
        
        /* Input fields */
        #acf-group_sfpf_user_schema_structures input[type="text"],
        #acf-group_sfpf_user_schema_structures input[type="url"],
        #acf-group_sfpf_user_schema_structures textarea {
            border-radius: 4px !important;
        }
    </style>
    <?php
}

// ═══════════════════════════════════════════════════════════════════════════
// SHORTCODE HELPER FUNCTIONS
// ═══════════════════════════════════════════════════════════════════════════

/**
 * Render education repeater content
 * 
 * @param array  $atts     Shortcode attributes
 * @param string $user_key ACF user key (e.g., 'user_123')
 * @return string
 */
function sfpf_render_education_shortcode($atts, $user_key) {
    $format = isset($atts['format']) ? strtolower(trim($atts['format'])) : 'html';
    $index  = isset($atts['index']) ? (int)$atts['index'] : null;
    $field  = isset($atts['field']) ? sanitize_key($atts['field']) : null;
    
    $education = get_field('education', $user_key);
    
    if (empty($education) || !is_array($education)) {
        return '';
    }
    
    // If specific index requested
    if ($index !== null) {
        if (!isset($education[$index])) {
            return '';
        }
        $entry = $education[$index];
        
        // If specific field from that index
        if ($field && isset($entry[$field])) {
            $val = $entry[$field];
            if ($field === 'wiki_url' && $val) {
                return esc_url($val);
            }
            return esc_html($val);
        }
        
        // Return single entry as HTML
        return sfpf_format_education_entry_html($entry);
    }
    
    // If specific field requested from ALL entries (comma-separated)
    if ($field && $index === null) {
        $values = [];
        foreach ($education as $entry) {
            if (isset($entry[$field]) && $entry[$field] !== '') {
                $values[] = $field === 'wiki_url' ? esc_url($entry[$field]) : esc_html($entry[$field]);
            }
        }
        return implode(', ', $values);
    }
    
    // Format: JSON
    if ($format === 'json') {
        return wp_json_encode($education);
    }
    
    // Default: HTML output
    $output = '<div class="founder-education">';
    foreach ($education as $i => $entry) {
        $output .= sfpf_format_education_entry_html($entry, $i);
    }
    $output .= '</div>';
    
    return $output;
}

/**
 * Format a single education entry as HTML
 *
 * @param array $entry Education entry data
 * @param int   $index Optional index for CSS class
 * @return string
 */
function sfpf_format_education_entry_html($entry, $index = 0) {
    $college     = isset($entry['college']) ? esc_html($entry['college']) : '';
    $wiki_url    = isset($entry['wiki_url']) ? esc_url($entry['wiki_url']) : '';
    $year        = isset($entry['year']) ? esc_html($entry['year']) : '';
    $designation = isset($entry['designation']) ? esc_html($entry['designation']) : '';
    $major       = isset($entry['major']) ? esc_html($entry['major']) : '';
    
    if (empty($college) && empty($designation) && empty($major)) {
        return '';
    }
    
    $html = '<div class="founder-education education-item">';
    
    if ($college) {
        $html .= '<div class="college">';
        if ($wiki_url) {
            $html .= '<a href="' . $wiki_url . '" target="_blank" rel="noopener">' . $college . '</a>';
        } else {
            $html .= $college;
        }
        $html .= '</div>';
    }
    
    if ($designation || $major) {
        $html .= '<div class="degree">';
        if ($designation) {
            $html .= '<span class="designation">' . $designation . '</span>';
        }
        if ($designation && $major) {
            $html .= ' in ';
        }
        if ($major) {
            $html .= '<span class="major">' . $major . '</span>';
        }
        $html .= '</div>';
    }
    
    if ($year) {
        $html .= '<div class="year">' . $year . '</div>';
    }
    
    $html .= '</div>';
    
    return $html;
}

/**
 * Render sameAs content
 * 
 * @param array  $atts     Shortcode attributes
 * @param string $user_key ACF user key (e.g., 'user_123')
 * @return string
 */
function sfpf_render_sameas_shortcode($atts, $user_key) {
    $format = isset($atts['format']) ? strtolower(trim($atts['format'])) : 'text';
    
    $sameas = get_field('sameas', $user_key);
    
    if (empty($sameas) || !is_string($sameas)) {
        return '';
    }
    
    // Split by newlines and filter empty
    $urls = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $sameas)));
    
    if (empty($urls)) {
        return '';
    }
    
    // Format: JSON
    if ($format === 'json') {
        return wp_json_encode(array_values($urls));
    }
    
    // Format: UL (unordered list)
    if ($format === 'ul') {
        $output = '<ul class="founder-sameas">';
        foreach ($urls as $url) {
            $output .= '<li class="sameas-item"><a href="' . esc_url($url) . '" target="_blank" rel="noopener">' . esc_html($url) . '</a></li>';
        }
        $output .= '</ul>';
        return $output;
    }
    
    // Default: text (newline separated)
    return implode("\n", array_map('esc_url', $urls));
}
