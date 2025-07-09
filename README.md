![Powerpack Video module](https://weave-hk-github.b-cdn.net/weave/plugin-header.png)

# PowerPack Video Module - Screen Recording Services Extension

An extension for the PowerPack Video module that adds support for three popular screen recording services: **Zight**, **Komodo Decks**, and **Supercut**.

Used in-house at Weave Digit Studio & HumanKind Funeral Websites to added screen recordings for client training videos.

## 📋 Overview

This module extends the existing PowerPack Video module for Beaver Builder to support embedding screen recordings from 3 screen recorders in lightboxes:

- **Zight** (CloudApp) - `share.zight.com` 
- **Komodo Decks** - `komododecks.com` 
- **Supercut** - `supercut.ai`

## ✨ Features

- **Full Lightbox Support** - All services work seamlessly with PowerPack's lightbox functionality
- **Custom Overlay Images** - Support for custom overlay images with play button styling
- **URL Validation** - Robust URL validation and video ID extraction
- **Multiple Domain Support** - Supports both primary and vanity domains for each service
- **Backward Compatibility** - Maintains full compatibility with existing video services (YouTube, Vimeo, etc.)
- **Error Handling** - Graceful handling of invalid URLs and edge cases

## 🛠️ Installation

This module uses the **theme override** method, which is the recommended approach for customising PowerPack modules.

### Step 1: Download the Module
1. Download or clone this repository

### Step 2: Install in Your Theme
1. Navigate to your active theme directory: `wp-content/themes/your-theme-name/`
2. Create the following folder structure if it doesn't exist:
   ```
   your-theme-name/
   └── bbpowerpack/
       └── modules/
   ```
3. Copy the entire `pp-video` folder into `your-theme-name/bbpowerpack/modules/`

### Final Directory Structure
```
wp-content/themes/your-theme-name/
└── bbpowerpack/
    └── modules/
        └── pp-video/
            ├── pp-video.php
            ├── css/
            ├── includes/
            └── js/
```

## 🚀 Usage

1. **Add Video Module** - Add the PowerPack Video module to your Beaver Builder layout
2. **Select Service** - Choose from the new video type options:
   - Zight
   - Komodo Decks  
   - Supercut
3. **Enter URL** - Paste your screen recording URL
4. **Configure Settings** - Set up lightbox, overlay, and other options as normal

### Supported URL Formats

#### Zight
- `https://share.zight.com/{video-id}`

#### Komodo Decks
- `https://komododecks.com/recordings/{video-id}`

#### Supercut
- `https://supercut.ai/share/{workspace}/{video-id}`

## 🔧 Technical Details

### How It Works
This extension uses PowerPack's module override system to extend the Video module with:

1. **New Video Types** - Added dropdown options for the three services
2. **URL Fields** - Dedicated URL input fields for each service
3. **Regex Validation** - Pattern matching for URL validation and video ID extraction
4. **Embed Generation** - Custom embed URL patterns for each service
5. **JavaScript Integration** - Updated settings to handle the new video types

### Key Files Modified
- `pp-video.php` - Main module file with new video types and embed logic
- `includes/frontend.php` - Frontend rendering logic
- `includes/settings.php` - Module settings configuration
- `js/settings.js` - JavaScript for admin interface behaviour

## 🔗 Official Documentation

For more information about customising PowerPack modules:
- [PowerPack Module Customisation Guide](https://wpbeaveraddons.com/docs/powerpack/development/customize-powerpack-module/)
- [Beaver Builder Module Override Documentation](https://docs.wpbeaverbuilder.com/beaver-builder/developer/custom-modules/override-an-existing-module/)

## 📋 Requirements

- WordPress 5.0+
- Beaver Builder 2.0+
- [PowerPack for Beaver Builder](https://wpbeaveraddons.com/)
- PHP 7.4+

## 🐛 Troubleshooting

### Module Not Appearing
1. Ensure the folder structure is correct
2. Check that your theme supports PowerPack overrides
3. Clear any caching plugins
4. Verify PowerPack is activated

### Videos Not Embedding
1. Verify the URL format matches the supported patterns
2. Check that the video is publicly accessible
3. Ensure the video ID is correct in the URL

## 🤝 Contributing

Feel free to submit issues, feature requests, or pull requests to improve this extension.

## 📄 License

This project is licensed under the GPL v2 or later - same as WordPress.

## 🙏 Acknowledgments

- Built as a module override for [PowerPack for Beaver Builder](https://wpbeaveraddons.com/)
- Uses the official PowerPack module override system
- Follows WordPress and Beaver Builder coding standards I think

---

**Note**: This is a community-created extension and is not officially supported by the PowerPack team. For official PowerPack support, please visit their [documentation](https://wpbeaveraddons.com/docs/). 
