# RPA Manager

A plugin for managing real-phone RPA (Real Phone Automation) devices and Instagram accounts. Provides a centralized admin interface for device configuration and a user-facing dashboard for account-to-device assignments.

## Features

**Admin**
- Configure primary and secondary farm API URLs
- List, create, and delete devices on the farm
- Assign/unassign devices to users

**Users**
- View assigned devices with live status (battery, RAM, Instagram version)
- Assign Instagram accounts to devices
- Remove accounts from devices
- Trigger account login on a device
- View live device screen (dual-farm support)

## Requirements

- PHP 7+
- MySQL / MariaDB
- cURL extension enabled
- Parent app with plugin architecture (routes, events, DB abstraction, auth)

## Installation

1. Copy the `rpa-manager/` folder into your app's `plugins/` directory.
2. Activate the plugin through the admin panel.
   On activation the plugin will:
   - Create the required database tables (`np_rpa`, `{prefix}rpa`, `{prefix}rpa_manager_settings`, `{prefix}rpa_assigned_devices`)
   - Copy core model/controller files into the app's shared directories
3. Open **Admin → RPA Manager → Settings** (`/e/rpa-manager/settings`) and enter:
   - **Farm API URL** – primary device farm endpoint
   - **Farm API URL 2** – backup farm endpoint (optional)
   - **Screen Base URL / Screen Base URL 2** – base URLs for live screen links

## Configuration

All settings are stored in the `{prefix}rpa_manager_settings` table as key-value pairs.

| Key | Description |
|-----|-------------|
| `farm_api_url` | Primary farm API base URL |
| `farm_api_url_2` | Secondary / backup farm API base URL |
| `screen_base_url` | Base URL for live screen on primary farm |
| `screen_base_url_2` | Base URL for live screen on secondary farm |

## Routes

| Method | Path | Controller | Description |
|--------|------|------------|-------------|
| GET/POST | `/e/rpa-manager/settings/` | `SettingsController` | Admin settings page |
| GET/POST | `/e/rpa-manager/devices-api/` | `AdminDevicesController` | Admin device management API (JSON) |
| GET/POST | `/realphone/` | `RealPhoneController` | User-facing Real Phone Manager |

## Admin Device API Actions

Send `action` as a POST/GET parameter to `/e/rpa-manager/devices-api/`.

| Action | Description |
|--------|-------------|
| `list_available` | List all available devices on the farm |
| `list_user_devices` | List devices assigned to a user |
| `assign_devices` | Assign N devices to a user |
| `create_device` | Create a new device on the farm |
| `delete_device` | Unassign / delete a device |

## User Device API Actions

Send `action` as a POST parameter to `/realphone/`.

| Action | Description |
|--------|-------------|
| `get-user-devices` | Fetch devices assigned to the current user |
| `assign-account` | Assign an Instagram account to a device |
| `remove-account` | Remove an account from a device |
| `login-account` | Trigger account login on a device |

## Database Tables

| Table | Description |
|-------|-------------|
| `np_rpa` | Legacy RPA account statistics (backwards compatibility) |
| `{prefix}rpa` | Primary RPA account statistics |
| `{prefix}rpa_manager_settings` | Plugin key-value settings |
| `{prefix}rpa_assigned_devices` | Device-to-user assignment records |

## Project Structure

```
rpa-manager/
├── assets/
│   ├── css/realphone.css                          # UI animations
│   └── js/rpa-manager-settings.js                 # Admin settings JS
├── controllers/
│   ├── AdminDevicesController.php                 # Admin device API
│   └── SettingsController.php                     # Admin settings page
├── storage/
│   ├── controllers/RealPhoneController.php        # User device controller
│   ├── models/
│   │   ├── RpaModel.php                           # Single RPA account model
│   │   └── RpasModel.php                          # RPA accounts list model
│   └── views/
│       ├── realphone.php                          # Real Phone Manager view
│       └── fragments/realphone.fragment.php       # Device card component
├── views/
│   ├── settings.php                               # Admin settings view
│   └── fragments/
│       ├── navigation.fragment.php                # Admin nav item
│       └── navigation-realphone.fragment.php      # User nav item
├── config.php                                     # Plugin metadata
└── rpa-manager.php                                # Plugin bootstrap & hooks
```

## Security Notes

- All admin endpoints require admin authentication.
- User endpoints verify the authenticated user owns the requested device.
- Passwords are encrypted with Defuse PHP Encryption before storage.
- Direct file access is blocked: files check for `APP_VERSION` constant.

## License
Do you need coaching for how to run automation and monetize it? Contact : [Kani Baspinar](https://t.me/kanibaspinar)
Proprietary — © [Kani Baspinar](https://hypervoter.com)
