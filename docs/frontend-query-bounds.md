# Frontend Query Bounds

SFPF public loops and Person-to-Organization lookups use explicit positive
limits. No public shortcode or Person schema query requests
`posts_per_page=-1`.

## Defaults

| Context | Default | Hard maximum | Behavior |
| --- | ---: | ---: | --- |
| `loop` | 12 posts | 48 posts | Omitted `rows` uses 12. `columns * rows` is clamped to 48. |
| `founder_organizations` | 20 IDs per query | 100 IDs total | Organization IDs are paged in batches, never loaded all at once. |

The Organization lookup follows the canonical SMC Organization Profile ACF
model: the Organization field `founder_users` contains related WordPress user
IDs. The legacy single-user `founder` field remains a query fallback. The
initial ID-only query calculates the relationship count; later batches skip
that count. This preserves configured relationships up to the documented hard
maximum without an unbounded post-object or all-ID query.

Sites that predate those relationship fields may still rely on the historical
Organization inventory output. Only when both relationship queries are empty,
SFPF falls back to the same counted, batched, hard-capped published inventory.
The `sfpf_founder_organizations_allow_inventory_fallback` filter can return
`false` once a site has completed relationship migration. Fallback use fires:

```php
do_action( 'sfpf_founder_organizations_inventory_fallback', $user_id, $total, $hard_max );
```

When the relationship count exceeds the hard maximum, SFPF returns the first
bounded set in stable date/ID order and fires:

```php
do_action( 'sfpf_founder_organizations_truncated', $user_id, $total, $hard_max );
```

## Filters

All values are normalized to positive integers, and every default is clamped
to its hard maximum.

```php
add_filter( 'sfpf_loop_default_limit', static fn (): int => 18 );
add_filter( 'sfpf_loop_hard_max', static fn (): int => 60 );

add_filter( 'sfpf_founder_organizations_default_limit', static fn (): int => 25 );
add_filter( 'sfpf_founder_organizations_hard_max', static fn (): int => 125 );
add_filter( 'sfpf_founder_organizations_allow_inventory_fallback', '__return_false' );
```

The shared `sfpf_frontend_query_default_limit` and
`sfpf_frontend_query_hard_max` filters receive the current value and context
name, so a site can centralize its policy before applying context filters.
