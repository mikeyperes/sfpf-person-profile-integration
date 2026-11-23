# Field-key migration

SFPF 4.0.14 uses field identifiers in the form `field_sfpf_<type>_<field>` and groups in the form `group_sfpf_<type>`. The migration updates exact hidden references in WordPress metadata and options. It preserves stored names, values, nested repeater entries, REST field names and schema output. The old identifiers stay active until migration commits.

For each site, use Backup Manager to create and verify the plugin archive and database dump. Then run:

```sh
wp sfpf field-keys --dry-run
wp sfpf field-keys --backup-verified
wp sfpf field-keys --backup-verified
```

The dry run copies only the candidate reference rows to temporary tables and applies the same update there. Compare the first live report with the dry-run report. The second live run must report no reference changes. Use a quiet maintenance window for the switch so a request cannot save a reference during it. The migration uses a transaction and invalidates the affected caches after commit. A multisite installation requires an inventory and backup covering its shared user metadata before migration.
