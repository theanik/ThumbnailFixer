# ThumbnailFixer for Omeka Classic

A simple yet powerful plugin that automatically generates missing thumbnail images for PDF files in Omeka Classic.

## Overview

ThumbnailFixer solves a common issue in Omeka Classic where PDF uploads sometimes fail to generate derivative images (thumbnails, square thumbnails, and fullsize previews). This plugin automatically detects and regenerates these missing images from the first page of PDF files.

## The Problem

When uploading PDF files to Omeka Classic, the system occasionally fails to generate the derivative images needed for item previews. This results in broken or missing thumbnails in your collections, affecting the user experience and visual presentation of your digital archive.

## The Solution

ThumbnailFixer scans your Omeka database for PDF files missing derivative images and automatically generates:
- **Thumbnails** - Small preview images for gallery views
- **Square Thumbnails** - Uniform square images for grid layouts
- **Fullsize Images** - Larger preview images for detailed viewing

All images are generated from the first page of the PDF document.

## How It Works

The plugin operates exclusively on PDF files by:
1. Querying the `omeka_files` table in your database
2. Identifying records where `has_derivative_image = 0`
3. Processing these files to generate missing derivative images

**Pro Tip:** To regenerate thumbnails for all PDF files (even those with existing derivatives), manually set `has_derivative_image = 0` for all PDF records in your database before running the plugin.

## Requirements

### System Requirements
- Omeka Classic (tested on version 2.x and 3.x)
- PHP 5.6 or higher (PHP 7.x+ recommended)
- Proper file permissions for the `files` directory

### Required PHP Dependencies

This plugin requires the following system packages to process PDF files:

- **ImageMagick** - For image processing and conversion
- **Ghostscript** - For rendering PDF pages to images

#### Installing Dependencies

**Ubuntu/Debian:**
```bash
sudo apt-get update
sudo apt-get install imagemagick ghostscript
```

**CentOS/RHEL:**
```bash
sudo yum install ImageMagick ghostscript
```

**macOS (using Homebrew):**
```bash
brew install imagemagick ghostscript
```

**Windows:**
- Download and install [ImageMagick](https://imagemagick.org/script/download.php#windows)
- Download and install [Ghostscript](https://www.ghostscript.com/download/gsdnld.html)


**Docker**
Please find the fully working Dockerfile with `imagemagick` and  `ghostscript`
```bash
FROM php:7.4-apache

# Set environment variables to avoid some interactive install issues
ENV DEBIAN_FRONTEND=noninteractive

# Update, install dependencies, and clean up
RUN apt-get update && apt-get install -y --no-install-recommends \
    unzip \
    libzip-dev \
    libjpeg-dev \
    libpng-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    imagemagick \
    ghostscript \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        exif \
        mbstring \
        mysqli \
        pdo \
        pdo_mysql \
        zip \
        gd \
    && a2enmod rewrite \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Overwrite policy.xml with safe PDF/PS/EPS/XPS rules
RUN cat > /etc/ImageMagick-6/policy.xml <<'EOF'
<?xml version="1.0" encoding="UTF-8"?>
<policymap>
  <policy domain="resource" name="memory" value="256MiB"/>
  <policy domain="resource" name="map" value="512MiB"/>
  <policy domain="resource" name="width" value="16KP"/>
  <policy domain="resource" name="height" value="16KP"/>
  <policy domain="resource" name="area" value="128MP"/>
  <policy domain="resource" name="disk" value="1GiB"/>

  <!-- allow PDF + Ghostscript formats -->
  <policy domain="coder" rights="read|write" pattern="PS" />
  <policy domain="coder" rights="read|write" pattern="PS2" />
  <policy domain="coder" rights="read|write" pattern="PS3" />
  <policy domain="coder" rights="read|write" pattern="EPS" />
  <policy domain="coder" rights="read|write" pattern="PDF" />
  <policy domain="coder" rights="read|write" pattern="XPS" />

  <!-- allow ghostscript module access -->
  <policy domain="module" rights="read|write" pattern="PS" />
  <policy domain="module" rights="read|write" pattern="PDF" />
  <policy domain="module" rights="read|write" pattern="EPS" />
  <policy domain="module" rights="read|write" pattern="XPS" />
</policymap>
EOF

WORKDIR /var/www/html
```

#### Verifying Installation

After installation, verify that the dependencies are properly installed:
```bash
# Check ImageMagick
convert -version

# Check Ghostscript
gs -version
```

Both commands should return version information if installed correctly.

## Installation

1. **Install system dependencies** (see Requirements section above)
2. Download the plugin files
3. Upload the `ThumbnailFixer` folder to your Omeka `plugins` directory
4. Log in to your Omeka admin panel
5. Navigate to **Settings → Plugins**
6. Click **Install** next to ThumbnailFixer
7. Activate the plugin

## Usage

Using ThumbnailFixer is incredibly simple:

1. Log in to your Omeka admin panel
2. Look for **Thumbnail Fixer** in your admin sidebar
3. Click on it to access the processing page
4. Click the process button and watch as the plugin fixes your thumbnails
5. You'll see a count of processed files when complete

That's it! The plugin handles everything else automatically.

## Troubleshooting

### Thumbnails still not generating?

If thumbnails aren't being created, check the following:

1. **Verify dependencies are installed:**
```bash
   convert -version
   gs -version
```

2. **Check PHP permissions:** Ensure PHP can execute ImageMagick and Ghostscript commands

3. **Check file permissions:** Verify the `files` directory and subdirectories are writable by your web server

4. **Review Omeka error logs:** Check `application/logs` for any error messages

5. **Ghostscript policy file:** Some systems have restrictive ImageMagick security policies. If you encounter permission errors, you may need to edit `/etc/ImageMagick-6/policy.xml` (or similar path) and adjust PDF permissions:
```xml
   <policy domain="coder" rights="read|write" pattern="PDF" />
```

## Technical Details

- **Target Files:** PDF documents only
- **Database Table:** `omeka_files`
- **Processing Criteria:** `has_derivative_image = 0`
- **Image Source:** First page of PDF document
- **Image Processing:** ImageMagick with Ghostscript backend

## Support

Need help or have questions? Feel free to reach out:

📧 **Email:** anwar.anik09@gmail.com

For bug reports or feature requests, please include:
- Your Omeka version
- PHP version
- ImageMagick version (`convert -version`)
- Ghostscript version (`gs -version`)
- Details about the issue you're experiencing
- Any relevant error messages

## Important Warnings

⚠️ **Use at your own risk.**

Before using this plugin:
- **Backup your database** - Always create a complete database backup
- **Backup your files** - Make a copy of your `files` directory
- **Test in a development environment** - If possible, test on a non-production instance first
- **Monitor the process** - Check your archives regularly after processing
- **Keep backups accessible** - Ensure you can roll back changes if needed

This plugin modifies your database and file system. While designed to be safe, unforeseen issues can occur depending on your server configuration and Omeka setup.

For any technical consultancy: anwar.anik09@gmail.com

## Contributing

Contributions, issues, and feature requests are welcome! This is an open-source project, and community involvement helps make it better for everyone.

## License

This plugin is free to use and distribute. Please maintain attribution to the original author.

## Changelog

### Version 1.0.0
- Initial release
- Automatic thumbnail generation for PDF files
- Admin interface for processing
- Database query optimization
- Support for ImageMagick and Ghostscript processing

---

**Made with ❤️ for the Omeka community**

*If this plugin helps your project, consider sharing it with others who might benefit!*