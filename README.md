# MB_AdminReindex

Adds two actions below **Invalidate index** in **System > Tools > Index Management**:

- **Reindex** rebuilds the selected indexers using Magento's native dependency order. Invalid prerequisites
  and dependent indexers are included automatically.
- **Reindex ALL** rebuilds every configured indexer and does not require a row selection.

Both endpoints accept POST requests only, use Magento form-key validation, and require Magento's native `Magento_Indexer::index` ACL resource. Running indexers are skipped. Shared indexes are rebuilt only once per request and their related indexer states are synchronized through Magento's native processor. A failure in one indexer is logged and does not prevent the remaining indexers from running.

## Compatibility

- Magento Open Source / Adobe Commerce 2.4.7-p3
- PHP 8.1, 8.2, or 8.3

## Installation

Copy the module to `app/code/MB/AdminReindex`, then run:

```bash
bin/magento module:enable MB_AdminReindex
bin/magento setup:upgrade
bin/magento cache:clean
```

In production mode, also deploy Admin static content according to the deployment process used by the project.

Full Access administrators can use the actions automatically. For restricted Admin roles, grant **System > Tools > Index Management**. There is no separate module-specific permission.

## Operational note

Admin reindexing runs synchronously in the web request. Large catalogs can exceed PHP or web-server timeouts. For large or production catalogs, prefer:

```bash
bin/magento indexer:reindex
```
