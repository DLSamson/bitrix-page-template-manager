# Bitrix Page Template Manager

[![Tests](https://github.com/DLSamson/bitrix-page-template-manager/actions/workflows/tests.yml/badge.svg)](https://github.com/DLSamson/bitrix-page-template-manager/actions/workflows/tests.yml)
[![codecov](https://codecov.io/gh/DLSamson/bitrix-page-template-manager/branch/main/graph/badge.svg)](https://codecov.io/gh/DLSamson/bitrix-page-template-manager)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D7.4-8892BF.svg)](https://php.net/)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

It is a convenient package for Bitrix CMS that allows developers to easily create and manage page templates inside a single website template.
With this tool, you can quickly develop reusable templates that can be used for various pages inside one site template of your web application.

## What problem this package is trying to solve?

Page Template Manager is designed to solve the problem of the lack of a built-in mechanism in Bitrix for defining different types of pages within a single template. Because of this, developers have to perform a lot of if-else checks both in the code and in the site configuration to determine the current page and connect the appropriate template, which leads to cumbersome and difficult to maintain code.

The package offers a simple solution — the ability to specify the URLs of the pages and their corresponding templates in the configuration. PageTemplateManager automatically detects the current page and connects the desired template, eliminating the need to write a lot of if-else checks and making the code cleaner and more modular.

---

## Installation

> in progress, otherwise git clone and include yourself
<!--
```bash
composer require dlsamson/bitrix-page-template-manager
```
-->


Include the autoloader in your project:

```php
require_once 'pathToVendor/autoload.php';
```

## 🚀 Quick Start

Here's everything you need to transform your messy Bitrix template into clean, organized code:

### Basic Setup

**Step 1:** Create your template structure:

```
/local/templates/main/
└── templates/            ← Your page templates folder
    ├── header.php                    ← Your default main Bitrix header template
    ├── footer.php                    ← Your default main Bitrix footer template
    ├── catalog/
    │   ├── list.header.php           ← Your catalog list page Bitrix header template
    │   └── list.footer.php           ← Your catalog list page Bitrix header template
    └── blog/
        ├── post.header.php           ← Your blog post page Bitrix header template
        └── post.footer.php           ← Your blog post page Bitrix footer template
```

**Step 2:** Initialize in your Bitrix template:

```php
<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

use PageTemplateManager\Templater;
use PageTemplateManager\Manager;

// Setup Templater
$templater = new Templater(__DIR__ . '/templates');

// Configure Manager with URL patterns
Manager::enableSingletonPattern(
    $APPLICATION->GetCurDir(),
    $templater,
    [
        ['name' => null, 'urls' => [
            '/'                                // Will include header.php or footer.php template
        ]],
        ['name' => 'catalog.list', 'urls' => [
            '/catalog/'                        // Will include catalog/list.header.php or catalog/list.footer.php template
        ]],
        ['name' => 'blog.post', 'urls' => [
            '/blog/(.+)                        // Will include blog/post.header.php or blog/post.footer.php template
        ']],
    ]
);
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php $APPLICATION->ShowTitle() ?></title>
</head>
<body>
    <?php Manager::autoDetectHeaderTemplate() ?>
    {%PAGE_CONTENT%}
    <?php Manager::autoDetectFooterTemplate()  ?>
</body>
</html>
```

### Before vs After

**Before** (the painful way):
```php
<?php if ($APPLICATION->GetCurDir() == '/'): ?>
    <!-- Homepage layout -->
<?php elseif (preg_match('#^/catalog#', $APPLICATION->GetCurDir())): ?>
    <!-- Catalog layout -->
<?php elseif (preg_match('#^/blog#', $APPLICATION->GetCurDir())): ?>
    <!-- Blog layout -->
<?php else: ?>
    <!-- Default layout -->
<?php endif; ?>
```

**After** (the beautiful way):
```php
<?php Manager::autoDetectHeaderTemplate() ?>
    {%PAGE_CONTENT%}
<?php Manager::autoDetectFooterTemplate() ?>
```

Clean. Simple. Beautiful.

---

## Core Concepts

The package consists of two main classes:

**Templater** — responsible for loading template files. Can work with any types of templates (header, footer, sidebar, or your custom types).

**Manager** — auto-detection layer. Analyzes the current URL and configuration to automatically determine which template should be loaded.

Both classes support Singleton pattern for convenient use throughout your application.

---

## Basic Usage (Templater)

### Simple Template Loading

The Templater class provides a simple and intuitive API for managing page templates.
The main class you'll interact with is the Templater class, which can be used either as an object or via a singleton pattern.

First, specify the directory where your page templates are located:

```php
use PageTemplateManager\Templater;

$templateDir = __DIR__ . '/templates';
```

#### Using the Templater as an Object
Create an instance of the Templater class and pass the template directory path:

```php
$templater = new Templater($templateDir);
```

You can then load the header and footer templates for your pages:

```php
$templater->loadHeaderTemplate('content');
$templater->loadFooterTemplate('content');
```

The `loadHeaderTemplate` and `loadFooterTemplate` methods expect the base name of the template files (without the .php extension).
For example, if your header template is named content.header.php, you would pass 'content' as the argument.

#### Using the Singleton Pattern
Alternatively, you can use the Templater class via the singleton pattern:

```php
Templater::enableSingletonPattern($templateDir);

Templater::loadHeaderTemplate('content');
Templater::loadFooterTemplate('content');
```

This allows you to access the Templater methods statically without creating an instance.

If you want to disable the singleton pattern after enabling it, you can use the disableSingletonPattern method:
```php
Templater::disableSingletonPattern();
```

The package assumes that your template files are named using the following convention:

```text
templateDir/
├── content.header.php  ← These will be included
└── content.footer.php  ←
```

### Directory Structure with Subdirectories

You can create as many subfolders as you want. Just separate the folder name from the template name with dots:

```php
Templater::loadHeaderTemplate('services.list');
Templater::loadSidebarTemplate('services.list');
Templater::loadFooterTemplate('services.list');
```

This will look for files in the following structure:

```text
templateDir/
├── services/
│   ├── list.header.php   ← These will be included
│   ├── list.sidebar.php  ←
│   └── list.footer.php   ←
├── content.header.php
└── content.footer.php
```

### Custom Template Types

While you follow the `load{Type}Template` pattern when calling a method, you can name your type however you want.
Template type names are automatically converted to camelCase format.

```php
Templater::loadSubFooterTemplate('list');
Templater::loadWhatEverTemplate('content');
Templater::loadNavigationTemplate('main');
```

This creates the following file structure:

```text
templateDir/
├── list.subFooter.php      ← This will be included
├── content.whatEver.php    ←
├── main.navigation.php     ←
└── services/
    ├── list.header.php
    └── list.footer.php
```

### Basic Templates Without Names

If you want to use a template without specifying a name, you can call the method without arguments:

```php
Templater::loadHeaderTemplate();
Templater::loadFooterTemplate();
```

This will look for files:

```text
templateDir/
├── header.php  ← These will be included
└── footer.php  ←
```

> Note: Basic templates without names work only in the root directory, not in subdirectories.

---

## Advanced Usage (Manager)

The Manager class is where the real magic happens. It automatically detects which template should be loaded based on the current page URL and your configuration.

### Basic Setup

```php
use PageTemplateManager\Templater;
use PageTemplateManager\Manager;

// Create Templater instance
$templater = new Templater(__DIR__ . '/templates');

// Enable Manager with Singleton pattern
Manager::enableSingletonPattern(
    $APPLICATION->GetCurDir(),  // Current URL
    $templater,                  // Templater instance
    [                            // Configuration array
        [
            'name' => 'content.sidebar',
            'urls' => [
                '/services/',
                '/about/',
            ],
        ],
        [
            'name' => 'content.index',
            'urls' => [
                '/',
                '/main/',
            ],
        ],
    ]
);
```

### Auto-Detection Methods

Manager provides magic methods that follow the pattern `autoDetect{Type}Template()`:

```php
// In your header.php
<?php Manager::autoDetectHeaderTemplate() ?>

// In your footer.php  
<?php Manager::autoDetectFooterTemplate() ?>

// Custom types work too
<?php Manager::autoDetectSidebarTemplate() ?>
<?php Manager::autoDetectNavigationTemplate() ?>
```

The Manager will:
1. Check the current URL against all patterns in your config
2. Find the matching template name
3. Call the corresponding method on Templater with that name

### Configuration Structure

The configuration is an array of template definitions. Each definition contains:

**name** — the template name that will be passed to Templater (supports dot notation for subdirectories)

**urls** — array of URL patterns (regular expressions) that should match this template

```php
[
    [
        'name' => 'content.sidebar',      // Will load templates like: content/sidebar.header.php
        'urls' => [
            '/services/',                  // Exact match
            '/about/',
            '/contacts/',
        ],
    ],
    [
        'name' => 'blog.post',             // Will load templates like: blog/post.header.php
        'urls' => [
            '/blog/(.+)',                  // Regex pattern: matches /blog/anything
        ],
    ],
    [
        'name' => null,                    // Will load basic templates like: header.php, footer.php
        'urls' => [
            '/',                           // Homepage
        ],
    ],
]
```

### URL Patterns and Regex

The `urls` array accepts regular expression patterns. Each pattern is automatically wrapped with `^` and `$` anchors.

**Simple patterns:**

```php
'urls' => [
    '/services/',           // Matches exactly: /services/
    '/about/',              // Matches exactly: /about/
]
```

**Regex patterns:**

```php
'urls' => [
    '/blog/(.+)',                    // Matches: /blog/post-1, /blog/post-2, etc.
    '/product/[0-9]+/',              // Matches: /product/123/, /product/456/
    '/repair(_|-)(.+)',              // Matches: /repair-something, /repair_something
    '/(.+)(\_|\-)models',            // Matches: /car-models, /car_models
    '/services(.*)',                 // Matches: /services, /services/, /services/repair
]
```

**Character classes:**

```php
'urls' => [
    '/section[0-9]+/',               // Matches: /section1/, /section42/
    '/page-[a-z]+/',                 // Matches: /page-about/, /page-contact/
    '/item[A-Z]{2}[0-9]{3}/',       // Matches: /itemAB123/, /itemXY999/
]
```

### Pattern Priority and Matching Order

**Important:** The configuration array is processed in **reverse order**. This means that the **last** matching pattern wins.

```php
[
    [
        'name' => 'generic',
        'urls' => ['/services(.*)'],     // Matches /services/anything
    ],
    [
        'name' => 'specific',  
        'urls' => ['/services/repair/'],  // Matches /services/repair/ specifically
    ],
]
```

For URL `/services/repair/`:
- Both patterns would match
- But 'specific' is last, so it will be used
- Template `specific.header.php` will be loaded

**Pro tip:** Place more generic patterns first, more specific patterns last.

---

## Passing Variables to Templates

### Global Variables

You can pass variables that will be available in **all** templates:

```php
$templater = new Templater(__DIR__ . '/templates', [
    'siteName' => 'My Awesome Site',
    'currentYear' => date('Y'),
    'config' => $appConfig,
]);
```

These variables will be automatically extracted in every template:

```php
<!-- In any template file -->
<footer>
    <p>&copy; <?= $currentYear ?> <?= $siteName ?></p>
</footer>
```

### Local Variables (Per Template)

You can also pass variables to specific template calls:

```php
// With Templater
$templater->loadHeaderTemplate('content', [
    'pageTitle' => 'Welcome!',
    'showBanner' => true,
]);

// With Manager
Manager::autoDetectHeaderTemplate([
    'pageTitle' => 'Services',
    'breadcrumbs' => $breadcrumbsArray,
]);
```

In the template:

```php
<!-- content.header.php -->
<?php if ($showBanner): ?>
    <div class="banner"><?= $pageTitle ?></div>
<?php endif; ?>
```

### Bitrix Global Variables

The package automatically makes Bitrix global variables available in all templates:

- `$APPLICATION` — Bitrix Application object
- `$USER` — Current user object
- `$DB` — Database connection object

```php
<!-- In any template -->
<title><?php $APPLICATION->ShowTitle() ?></title>

<?php if ($USER->IsAuthorized()): ?>
    <p>Welcome, <?= $USER->GetFullName() ?>!</p>
<?php endif; ?>
```

---

## Real-World Example

Let's look at a complete real-world setup for a Bitrix website with different page layouts.

### Project Structure

```text
/local/templates/main/
├── header.php              ← Main Bitrix template header
├── footer.php              ← Main Bitrix template footer
├── bootstrap.php           ← Initialization file
├── templates/              ← Your page templates
│   ├── content/
│   │   ├── sidebar.header.php
│   │   ├── sidebar.footer.php
│   │   ├── index.header.php
│   │   └── index.footer.php
│   ├── blog/
│   │   ├── post.header.php
│   │   ├── post.sidebar.php
│   │   └── post.footer.php
│   ├── header.php          ← Default header
│   └── footer.php          ← Default footer
└── ...
```

### bootstrap.php (Initialization)

```php
<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

use PageTemplateManager\Templater;
use PageTemplateManager\Manager;

// Initialize Templater with global variables
$templater = new Templater(__DIR__ . '/templates', [
    'siteName' => 'My Company',
    'currentYear' => date('Y'),
    'isProduction' => SITE_ENV === 'production',
]);

// Configure Manager with URL patterns
Manager::enableSingletonPattern(
    $APPLICATION->GetCurDir(),
    $templater,
    [
        // Pages with sidebar layout
        [
            'name' => 'content.sidebar',
            'urls' => [
                '/info/',
                '/info/payment/',
                '/info/reviews/',
                '/info/guarantee/',
                '/info/corp(.*)',                   // Corporate pages
                '/repair_services_for_(.+)',        // Service pages
                '/services(.*)',                     // All service subpages
                '/(.+)(\_|\-)models',               // Model catalogs
                '/repair(_|\-)(.+)',                // Repair pages
            ],
        ],
        
        // Pages with index layout (no sidebar, full width)
        [
            'name' => 'content.index',
            'urls' => [
                '/products(\_|\-)(.+)/',           // Parts pages
                '/our_works(.*)',                    // Portfolio
            ],
        ],
        
        // Blog with special layout
        [
            'name' => 'blog.post',
            'urls' => [
                '/blog/[0-9]+/',                    // Individual posts
            ],
        ],
        
        // Default layout for everything else
        [
            'name' => null,                          // Will use basic templates
            'urls' => [
                '/',                                 // Homepage
                '/about/',
                '/contacts/',
            ],
        ],
    ]
);
```

### header.php (Main Bitrix Template)

```php
<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use PageTemplateManager\Manager;

require_once __DIR__ . '/bootstrap.php';
?>
<!DOCTYPE html>
<html lang="<?= LANGUAGE_ID ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php $APPLICATION->ShowTitle() ?></title>
    <?php $APPLICATION->ShowHead() ?>
</head>
<body>
    <?php $APPLICATION->ShowPanel() ?>
    
    <!-- Auto-detect and load appropriate header template -->
    <?php Manager::autoDetectHeaderTemplate([
        'breadcrumbs' => getBreadcrumbs(), // Your custom function
    ]) ?>
```

### footer.php (Main Bitrix Template)

```php
<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use PageTemplateManager\Manager;
?>
    
    <!-- Auto-detect and load appropriate footer template -->
    <?php Manager::autoDetectFooterTemplate() ?>
    
</body>
</html>
```

### templates/content/sidebar.header.php (Page Template)

```php
<div class="layout-with-sidebar">
    <aside class="sidebar">
        <?php $APPLICATION->IncludeComponent(
            "bitrix:menu",
            "sidebar",
            [
                "ROOT_MENU_TYPE" => "left",
                "MAX_LEVEL" => 1,
            ]
        ); ?>
    </aside>
    
    <main class="content">
        <h1><?php $APPLICATION->ShowTitle(false) ?></h1>
        
        <?php if (isset($breadcrumbs) && !empty($breadcrumbs)): ?>
            <nav class="breadcrumbs">
                <?php foreach ($breadcrumbs as $crumb): ?>
                    <a href="<?= $crumb['link'] ?>"><?= $crumb['title'] ?></a>
                <?php endforeach; ?>
            </nav>
        <?php endif; ?>
        
        <!-- Page content will be here -->
```

### templates/content/sidebar.footer.php (Page Template)

```php
    </main> <!-- Close .content -->
</div> <!-- Close .layout-with-sidebar -->

<footer class="site-footer">
    <p>&copy; <?= $currentYear ?> <?= $siteName ?>. All rights reserved.</p>
</footer>
```

### templates/content/index.header.php (Full Width Layout)

```php
<div class="layout-full-width">
    <main class="content">
        <h1><?php $APPLICATION->ShowTitle(false) ?></h1>
        <!-- Page content will be here -->
```

### templates/content/index.footer.php

```php
    </main>
</div>

<footer class="site-footer">
    <p>&copy; <?= $currentYear ?> <?= $siteName ?>. All rights reserved.</p>
</footer>
```

### How It Works

1. User opens `/services/repair/` page
2. Bitrix loads your main template `header.php`
3. `bootstrap.php` initializes Manager with configuration
4. `Manager::autoDetectHeaderTemplate()` is called
5. Manager checks URL `/services/repair/` against all patterns
6. Pattern `/services(.*)` matches → template name `content.sidebar` is selected
7. Manager calls `Templater::loadHeaderTemplate('content.sidebar')`
8. File `templates/content/sidebar.header.php` is included
9. Same process happens for footer

If the URL doesn't match any pattern, `resolveTemplateName()` returns `null`, and basic templates (`header.php`, `footer.php`) will be loaded.

---

## Configuration Options

### Loading Config from File

Instead of passing config array directly, you can load it from a PHP file:

```php
Manager::enableSingletonPattern(
    $APPLICATION->GetCurDir(),
    $templater,
    __DIR__ . '/config/templates.php'  // Path to config file
);
```

**config/templates.php:**

```php
<?php
return [
    [
        'name' => 'content.sidebar',
        'urls' => [
            '/services/',
            '/about/',
        ],
    ],
    [
        'name' => 'content.index',
        'urls' => [
            '/',
        ],
    ],
];
```

### Working Without Configuration

You can use Manager without configuration if you want:

```php
Manager::enableSingletonPattern(
    $APPLICATION->GetCurDir(),
    $templater,
    []  // Empty config
);

// Will fall back to basic templates or throw exception if not found
Manager::autoDetectHeaderTemplate();
```

---

## Error Handling

### TemplateFileNotFoundException

Thrown when a template file cannot be found:

```php
try {
    Templater::loadHeaderTemplate('nonexistent');
} catch (\PageTemplateManager\Exceptions\TemplateFileNotFoundException $e) {
    // Log error or show fallback
    error_log($e->getMessage());
    
    // Load default template as fallback
    Templater::loadHeaderTemplate();
}
```

### BadMethodCallException

Thrown when method name doesn't match the required pattern:

```php
// This will throw BadMethodCallException
Templater::loadSomethingWrong();  // Doesn't match load{Type}Template pattern

// This is correct
Templater::loadSomethingTemplate();  // Matches pattern
```

### InvalidArgumentException

Thrown when invalid parameters are passed:

```php
// Wrong type for config parameter
new Manager($url, $templater, "not-a-path-or-array");  // Throws exception

// Wrong type for values parameter
Templater::loadHeaderTemplate('content', "not-an-array");  // Throws exception
```

---

## API Reference

### Templater Class

#### Constructor

```php
public function __construct(string $templateDir, array $globalVariablesToPass = [])
```

**Parameters:**
- `$templateDir` — path to the directory containing template files
- `$globalVariablesToPass` — array of variables to be available in all templates

#### Magic Method: load{Type}Template

```php
public function __call($name, $arguments)
```

Pattern: `load{Type}Template(?string $name = '', ?array $values = [])`

**Parameters:**
- `$name` — template name (supports dot notation for subdirectories)
- `$values` — array of variables to pass to this specific template

**Examples:**

```php
$templater->loadHeaderTemplate();
$templater->loadHeaderTemplate('content');
$templater->loadHeaderTemplate('content', ['title' => 'Welcome']);
$templater->loadSidebarTemplate('blog.post');
$templater->loadCustomTypeTemplate('my.template', ['data' => $data]);
```

#### loadTemplate

```php
public function loadTemplate(string $name, string $type, array $values = []) : void
```

Low-level method for loading templates. Usually called by magic method.

**Parameters:**
- `$name` — template name
- `$type` — template type (camelCase format)
- `$values` — variables to pass

#### Singleton Methods

```php
public static function enableSingletonPattern(string $templateDir, array $globalVariablesToPass = [])
public static function disableSingletonPattern()
```

---

### Manager Class

#### Constructor

```php
public function __construct(string $currentUrl, Templater $templater, $config = [])
```

**Parameters:**
- `$currentUrl` — current page URL (usually `$APPLICATION->GetCurDir()`)
- `$templater` — Templater instance
- `$config` — array of template configurations OR path to PHP file returning array

#### Magic Method: autoDetect{Type}Template

```php
public function __call($name, $arguments)
```

Pattern: `autoDetect{Type}Template(?array $valuesToPass = [])`

**Parameters:**
- `$valuesToPass` — array of variables to pass to the template

**Examples:**

```php
Manager::autoDetectHeaderTemplate();
Manager::autoDetectFooterTemplate(['year' => 2024]);
Manager::autoDetectSidebarTemplate();
Manager::autoDetectNavigationTemplate(['items' => $menu]);
```

#### Singleton Methods

```php
public static function enableSingletonPattern(string $currentUrl, Templater $templater, $config = [])
public static function disableSingletonPattern()
```

---

## Best Practices

### Organize Your Templates Logically

```text
templates/
├── layouts/              ← Different page layouts
│   ├── sidebar.header.php
│   ├── sidebar.footer.php
│   ├── fullwidth.header.php
│   └── fullwidth.footer.php
├── sections/             ← Reusable sections
│   ├── navigation.php
│   ├── breadcrumbs.php
│   └── widgets.php
└── pages/                ← Page-specific templates
    ├── home.header.php
    ├── blog.header.php
    └── product.header.php
```

### Use Descriptive Template Names

```php
// Good
Templater::loadHeaderTemplate('blog.post');
Templater::loadHeaderTemplate('catalog.category');

// Less clear
Templater::loadHeaderTemplate('type1');
Templater::loadHeaderTemplate('layout2');
```

### Keep Configuration Readable

```php
// Add comments to explain patterns
[
    'name' => 'content.sidebar',
    'urls' => [
        '/services/',                    // Services landing page
        '/services/repair/',             // Repair service page
        '/repair_services_for_(.+)',     // Dynamic repair pages
    ],
]
```

### Handle Missing Templates Gracefully

```php
try {
    Manager::autoDetectHeaderTemplate();
} catch (TemplateFileNotFoundException $e) {
    // Log for debugging
    error_log('Template not found: ' . $e->getMessage());
}
```

### Use Global Variables for Site-Wide Data

```php
// Good - data available everywhere
$templater = new Templater($dir, [
    'siteName' => $config['site_name'],
    'contactPhone' => $config['phone'],
    'socialLinks' => $config['social'],
]);

// Less efficient - passing same data to every template  
Templater::loadHeaderTemplate('content', [
    'siteName' => $config['site_name'],  // Repeated everywhere
]);
```

---

## Troubleshooting

### Template Not Found Error

**Problem:** `TemplateFileNotFoundException: Template file with name X and type Y not found`

**Solutions:**
- Check that the file exists at the correct path
- Verify file naming: `{name}.{type}.php` (e.g., `content.header.php`)
- For subdirectories, check folder structure matches dot notation
- Ensure file permissions are correct

### Pattern Not Matching

**Problem:** Wrong template is loaded or default template is used instead of expected one

**Solutions:**
- Test your regex pattern separately
- Remember that patterns are wrapped with `^` and `$`
- Check pattern order (last matching pattern wins)
- Enable debug mode to see which pattern matches:

### Variables Not Available in Template

**Problem:** Variable shows as undefined in template

**Solutions:**
- Check if variable name is correctly spelled
- Ensure variable is passed either as global or local
- Verify `extract` function is working (check PHP error_reporting level)

### Singleton Pattern Conflicts

**Problem:** Using both object and singleton causes issues

**Solutions:**
- Choose one approach and stick to it throughout the project
- Disable singleton if you need multiple instances:

```php
Manager::disableSingletonPattern();
$manager1 = new Manager($url1, $templater1, $config1);
$manager2 = new Manager($url2, $templater2, $config2);
```

---

## Performance Considerations

### Caching Considerations

The package doesn't cache template paths or URL matching results. For high-traffic sites, consider:

- Using PHP opcache (enabled by default in modern PHP)
- Keeping config arrays reasonable in size
- Avoiding overly complex regex patterns

### Impact on Page Load

- Template file includes are standard PHP `require` calls (fast)
- URL matching happens per every method call
- Minimal overhead compared to manual if-else chains

---

## TODO

- [x] Implement Manager class to auto-detect templates based on URL
- [x] Add Real-use examples
- [ ] Submit package to packagist
- [ ] Create docs page on GitHub Pages
- [ ] Add translation for Russian Language
- [ ] Make CLI command to easily generate new templates (if in demand)
- [ ] Add caching layer for URL pattern matching (if needed for performance)
- [ ] Template inheritance system (parent/child templates)

---

## Requirements

- PHP 7.4 or higher
- Bitrix CMS (for real-world usage with \$APPLICATION, \$USER, etc.)
- illuminate/support package (for Str helper)

---

## Contributing

Found a bug or have a feature request? Feel free to open an issue or submit a pull request.

---

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
