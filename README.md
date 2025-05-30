# AI Admin Assistant

A WordPress plugin that brings Claude AI's capabilities directly into your dashboard for intelligent content management, SEO optimization, and technical assistance.

## Overview

AI Admin Assistant revolutionizes WordPress content management by integrating Claude AI technology seamlessly into your admin interface. Whether you're writing blog posts, optimizing content for SEO, or managing technical aspects of your site, this plugin provides intelligent assistance every step of the way.

## Features

- **Seamless Integration**: Works directly within your WordPress dashboard
- **Time-Saving**: Generate ideas, draft content, and solve problems faster
- **Versatile**: Helps with content creation, technical issues, and site optimization
- **Security-Focused**: Secure API key storage and role-based access control

## Requirements

- WordPress 6.0 or higher
- PHP 7.4 or higher
- Claude AI API key (obtain from [Anthropic Console](https://console.anthropic.com/))
- Stable internet connection

## Installation

### Method 1: Manual Installation
1. Download the plugin files
2. Upload the `ai-assistant` folder to `/wp-content/plugins/`
3. Activate the plugin through the WordPress 'Plugins' menu

### Method 2: WordPress Plugin Repository
1. Install directly from the WordPress plugin repository
2. Activate through the WordPress admin interface

## Configuration

1. After activation, navigate to **AI Assistant > Settings**
2. Enter your Claude API key
3. Configure access permissions as needed
4. Save your settings

## Getting Started

1. Access the AI Assistant from the main WordPress menu
2. Start with a test query to verify your API connection
3. Explore the available features and commands
4. Begin using AI assistance for your content and technical needs

## Third-Party Dependencies

### JavaScript Libraries

- **Marked.js (v4.0.2)**: Markdown parsing library
  - Repository: [markedjs/marked](https://github.com/markedjs/marked)
  - License: MIT License

- **Highlight.js (v11.5.1)**: Code syntax highlighting library
  - Repository: [highlightjs/highlight.js](https://github.com/highlightjs/highlight.js)
  - License: BSD 3-Clause License

*Minified versions are included in `/assets/js/`. Source files are available in their respective GitHub repositories.*

## External Services

### Claude AI API Integration

- **Service**: Anthropic Claude AI
- **Purpose**: AI-powered assistance within WordPress admin
- **Data Handling**: 
  - User queries are sent to Claude AI API
  - No personal identifying information is transmitted
  - Data is sent only when users interact with the AI Assistant interface
- **Legal Information**:
  - [Terms of Service](https://www.anthropic.com/terms)
  - [Privacy Policy](https://www.anthropic.com/privacy)

**Important**: You must obtain your own API key from Anthropic to use this plugin.

## Development

This plugin uses a straightforward development approach with manual minification for optimal performance. No complex build process is required.

## License

This plugin is licensed under the GPLv2 or later.
- License: [GPL v2 or later](https://www.gnu.org/licenses/gpl-2.0.html)

## Contributors

- openzenta

## Support

For support and questions, please refer to the plugin documentation or create an issue in this repository.

---

**Tags**: ai, claude, artificial intelligence, content generation, wordpress ai  
**Stable Version**: 1.0.0  
**Tested up to**: WordPress 6.8.1
