# External Media Prefetcher for Magento 2

A Magento 2 module that automatically fetches missing media files from an external source.

## Description

This module solves the common problem of missing media files in development or staging environments.
When a media file is requested but not found locally, the module automatically attempts to download it
from a configured external URL (such as your production site).

## Requirements

- Magento 2.4.x
- PHP 7.4 or higher

## Installation

### Using Composer (recommended)

```bash
composer require hryvinskyi/magento2-external-media-prefetcher
bin/magento module:enable Hryvinskyi_ExternalMediaPrefetcher
bin/magento setup:upgrade
```

### Manual Installation

1. Create the following directory in your Magento installation: `app/code/Hryvinskyi/ExternalMediaPrefetcher`
2. Copy the module files to this directory
3. Enable the module and update the database:
   ```bash
   bin/magento module:enable Hryvinskyi_ExternalMediaPrefetcher
   bin/magento setup:upgrade
   ```

## Configuration

Modify your staging or local environment's `app/etc/env.php` file
and add this configuration under the `system` key:
```php
    'system' => [
        'default' => [
            'external_media_prefetcher' => [
                'general' => [
                    'enabled' => 1,
                    'external_media_url' => 'https://www.example.com/media/'
                ]
            ]
        ]
    ]
```

- **external_media_prefetcher/general/enabled**: Set to `1` to enable the module or `0` to disable it.
- **external_media_prefetcher/general/external_media_url**: Set the base URL where media files should be fetched from (e.g., `https://www.example.com/media/`)

You can also set this configuration through cli commands:

```bash
bin/magento config:set external_media_prefetcher/general/enabled 1 --lock-env
bin/magento config:set external_media_prefetcher/general/external_media_url https://www.example.com/media/ --lock-env
```

## How It Works

When a media file is requested but not found locally:

1. The module intercepts the request through a plugin on `Magento\MediaStorage\Model\File\Storage\Synchronization`
2. It cleans the path if it contains cache information
3. It constructs the external URL by combining the configured external media URL with the requested file path
4. It creates the necessary directory structure locally
5. It downloads the file from the external source and saves it to the local filesystem

## Why it's Useful

- **Development and Staging**: Easily sync media files from production to development or staging environments.
- **Missing Files**: Automatically fetch missing files without manual intervention.
- **Efficiency**: Saves time and effort in managing media files across different environments.

## License

[MIT](LICENSE)

## Author

Volodymyr Hryvinskyi  
Email: volodymyr@hryvinskyi.com  
GitHub: https://github.com/hryvinskyi