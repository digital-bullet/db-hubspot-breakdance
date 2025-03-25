# DB HubSpot for Breakdance

A clean HubSpot integration for Breakdance Forms in WordPress.

![GPLv3 License](https://img.shields.io/badge/license-GPLv3-blue)
![WordPress](https://img.shields.io/badge/WordPress-6.0%2B-blue)
![PHP](https://img.shields.io/badge/PHP-7.4%2B-blue)

## Description

DB HubSpot for Breakdance allows WordPress site owners to selectively integrate Breakdance form submissions with HubSpot CRM using the official HubSpot API, without requiring Zapier or other third-party services.

### Key Features

- **Direct HubSpot API Integration**: Connect your forms using a Private App Token
- **No Third-Party Services**: No Zapier or other intermediaries required
- **Selective Form Integration**: Choose which forms send data to HubSpot
- **Field Mapping**: Map Breakdance form fields to HubSpot contact properties
- **Simple Setup**: Easy-to-use admin interface

## Requirements

- WordPress 6.0 or higher
- PHP 7.4 or higher
- Breakdance Builder
- HubSpot account with Private App token

## Installation

1. Upload the `db-hubspot-breakdance` directory to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Settings > DB HubSpot to configure the plugin

## Setup

### 1. Create a HubSpot Private App

1. Log in to your HubSpot account
2. Go to Settings > Integrations > Private Apps
3. Click "Create private app"
4. Name your app (e.g., "WordPress Form Integration")
5. Add scopes:
   - `crm.objects.contacts.write`
   - `crm.objects.contacts.read`
6. Create the app and copy your token

### 2. Configure the Plugin

1. Go to WordPress Admin > Settings > DB HubSpot
2. Paste your Private App Token
3. Click "Test Connection" to verify
4. Select which Breakdance forms to integrate with HubSpot
5. Map your form fields to HubSpot properties using JSON format
6. Save settings

### 3. Field Mapping

Define your field mappings in the plugin settings using JSON like this:

```json
{
  "name": "firstname",
  "email": "email",
  "company": "company",
  "message": "message"
}
```

The left side is the field name in your Breakdance form, and the right side is the HubSpot property name.


## How It Works

When a user submits a selected Breakdance form, the plugin captures the form data, maps it according to your defined field mapping, and sends it to HubSpot via their official API. If successful, the contact is created or updated in your HubSpot CRM.


## Known Limitations

Form Detection in Breakdance

Due to how Breakdance handles forms on the frontend (as block-based structures rather than centrally registered post objects), automatically detecting available forms across a site is currently unreliable.

As a result, selective form integration is still experimental and may not list all forms as expected. If you have insight into how to better detect or register Breakdance forms, feel free to open an issue or submit a pull request — collaboration is welcome!


## Frequently Asked Questions

### Which HubSpot properties can I map to?

You can map to any standard or custom contact properties in your HubSpot account.

### Is an email field required?

Yes, HubSpot requires an email field to create or update a contact.

### Can I map multiple form fields to one HubSpot property?

No, the mapping is one-to-one. Each form field maps to a single HubSpot property.

## Troubleshooting

### Form submissions aren't reaching HubSpot

1. Check that your Private App Token is correct
2. Verify the form is selected in the plugin settings
3. Ensure your field mapping includes an email field
4. Check that field names match exactly (case-sensitive)

## Support

For support, please contact Digital Bullet via our website.

## License

This plugin is licensed under the GNU General Public License v3. See the [LICENSE](./LICENSE) file for more details.



---

Built with ❤️ by Real Jay Cole at [Digital Bullet](https://digitalbullet.ca). Contributions, ideas, and feedback are always welcome!
