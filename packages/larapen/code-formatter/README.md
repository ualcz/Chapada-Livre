# CodeFormatter

A comprehensive, reusable PHP library for formatting and minifying code in multiple languages including CSS, JavaScript, JSON, and HTML.

## Features

- ✅ **Multiple Format Support**: CSS, JavaScript, JSON, HTML
- ✅ **Pretty Print**: Format code with proper indentation and spacing
- ✅ **Minify**: Compress code by removing unnecessary whitespace and comments
- ✅ **Validation**: Basic syntax validation for each format
- ✅ **Customizable**: Extensive formatting options for each format
- ✅ **Extensible**: Easy to add custom formatters
- ✅ **Laravel Compatible**: Works seamlessly with Laravel projects
- ✅ **Standalone**: Can be used in any PHP project

## Requirements

- PHP 8.1 or higher

## Installation

### Via Composer (Recommended)

```bash
composer require mayeulak/code-formatter
```

### Manual Installation

1. Copy the `CodeFormatter` directory to your project
2. Include the autoloader or manually require the files

```php
require_once 'path/to/CodeFormatter/src/CodeFormatter.php';
require_once 'path/to/CodeFormatter/src/helpers.php';
```

## Quick Start

### Object-Oriented Style

```php
use CodeFormatter\CodeFormatter;

// Format CSS
$css = "
.main-logo{width:auto;height:40px;}
@media(min-width:1200px){.container{max-width:1200px;}}
";

$formatted = CodeFormatter::prettyPrint($css, 'css');
echo $formatted;

// Minify CSS
$minified = CodeFormatter::minify($css, 'css');
echo $minified;
```

### Procedural Style (Using Helpers)

```php
require 'vendor/autoload.php'; // or manual include

// Format CSS
$formatted = format_css($css);
$minified = minify_css($css);

// Format JavaScript
$formatted = format_js($javascript);
$minified = minify_js($javascript);

// Format JSON
$formatted = format_json($json);
$minified = minify_json($json);

// Format HTML
$formatted = format_html($html);
$minified = minify_html($html);
```

## Usage Examples

### CSS Formatting

```php
use CodeFormatter\CodeFormatter;

$css = ".main-logo{width:auto;height:40px;max-width:430px!important;}";

// Pretty print with options
$formatted = CodeFormatter::prettyPrint($css, 'css', [
    'indent_size' => 2,
    'newline_between_rules' => true,
    'space_before_brace' => true,
    'sort_properties' => true,
]);

// Output:
// .main-logo {
//   height: 40px;
//   max-width: 430px !important;
//   width: auto;
// }

// Minify
$minified = CodeFormatter::minify($css, 'css');
// Output: .main-logo{width:auto;height:40px;max-width:430px!important}
```

### JavaScript Formatting

```php
$js = "function hello(){console.log('Hello World');return true;}";

// Pretty print
$formatted = CodeFormatter::prettyPrint($js, 'js', [
    'indent_size' => 4,
    'preserve_comments' => true,
]);

// Minify
$minified = CodeFormatter::minify($js, 'js');
```

### JSON Formatting

```php
$json = '{"name":"John","age":30,"city":"New York"}';

// Pretty print
$formatted = CodeFormatter::prettyPrint($json, 'json', [
    'indent_size' => 2,
    'sort_keys' => true,
]);

// Output:
// {
//   "age": 30,
//   "city": "New York",
//   "name": "John"
// }

// Minify
$minified = CodeFormatter::minify($json, 'json');
```

### HTML Formatting

```php
$html = '<div><p>Hello</p><span>World</span></div>';

// Pretty print
$formatted = CodeFormatter::prettyPrint($html, 'html', [
    'indent_size' => 4,
    'preserve_comments' => true,
]);

// Minify
$minified = CodeFormatter::minify($html, 'html');
```

### File Operations

```php
// Format a file
$formatted = CodeFormatter::formatFile('/path/to/styles.css');

// Minify a file
$minified = CodeFormatter::minifyFile('/path/to/script.js');

// Format and save
CodeFormatter::formatFileAndSave('/path/to/input.css', '/path/to/output.css');

// Minify and save (overwrite original)
CodeFormatter::minifyFileAndSave('/path/to/script.js');
```

### Code Validation

```php
$isValid = CodeFormatter::validate($css, 'css');

if ($isValid) {
    echo "Valid CSS!";
}
```

## Formatting Options

### CSS Options

```php
[
    'indent_size' => 4,              // Number of spaces for indentation
    'preserve_comments' => true,      // Keep comments in output
    'newline_between_rules' => true, // Add blank line between CSS rules
    'space_before_brace' => true,    // Add space before opening brace
    'space_after_colon' => true,     // Add space after property colon
    'lowercase_selectors' => false,   // Convert selectors to lowercase
    'sort_properties' => false,       // Sort CSS properties alphabetically
    'compress_colors' => false,       // Compress color values (#ffffff -> #fff)
]
```

### JavaScript Options

```php
[
    'indent_size' => 4,              // Number of spaces for indentation
    'preserve_comments' => true,      // Keep comments in output
    'newline_before_brace' => false, // Allman style braces
    'space_before_paren' => true,    // Space before parentheses
    'semicolons' => true,            // Add missing semicolons
    'single_quotes' => false,        // Convert to single quotes
]
```

### JSON Options

```php
[
    'indent_size' => 4,      // Number of spaces for indentation
    'sort_keys' => false,    // Sort object keys alphabetically
    'ensure_ascii' => false, // Escape unicode characters
]
```

### HTML Options

```php
[
    'indent_size' => 4,              // Number of spaces for indentation
    'preserve_comments' => true,      // Keep comments in output
    'inline_tags' => [...],          // Tags to keep inline
    'self_closing_tags' => [...],    // Self-closing tag list
]
```

## Laravel Integration

### As a Service

Create a service provider:

```php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use CodeFormatter\CodeFormatter;

class CodeFormatterServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('code.formatter', function () {
            return new CodeFormatter();
        });
    }
}
```

Use in your controllers:

```php
use CodeFormatter\CodeFormatter;

class AssetController extends Controller
{
    public function minifyCss(Request $request)
    {
        $css = $request->input('css');
        $minified = CodeFormatter::minify($css, 'css');
        
        return response()->json(['minified' => $minified]);
    }
}
```

### As a Blade Directive

Register in `AppServiceProvider`:

```php
use Illuminate\Support\Facades\Blade;
use CodeFormatter\CodeFormatter;

public function boot()
{
    Blade::directive('formatCss', function ($expression) {
        return "<?php echo CodeFormatter::prettyPrint($expression, 'css'); ?>";
    });
    
    Blade::directive('minifyCss', function ($expression) {
        return "<?php echo CodeFormatter::minify($expression, 'css'); ?>";
    });
}
```

Use in Blade templates:

```blade
<style>
@formatCss($cssCode)
</style>
```

## Adding Custom Formatters

You can easily extend the library with custom formatters:

```php
use CodeFormatter\Formatters\BaseFormatter;

class XmlFormatter extends BaseFormatter
{
    public function prettyPrint(string $code, array $options = []): string
    {
        // Your implementation
    }
    
    public function minify(string $code, array $options = []): string
    {
        // Your implementation
    }
    
    public function getSupportedExtensions(): array
    {
        return ['xml'];
    }
}

// Register your formatter
CodeFormatter::register('xml', new XmlFormatter());

// Use it
$formatted = CodeFormatter::prettyPrint($xml, 'xml');
```

## Helper Functions Reference

```php
// Generic functions
format_code($code, $type, $options = [])
minify_code($code, $type, $options = [])
validate_code($code, $type)

// Format-specific functions
format_css($css, $options = [])
minify_css($css, $options = [])

format_js($js, $options = [])
minify_js($js, $options = [])

format_json($json, $options = [])
minify_json($json, $options = [])

format_html($html, $options = [])
minify_html($html, $options = [])
```

## API Reference

### CodeFormatter Class

#### Static Methods

- `prettyPrint(string $code, string $type, array $options = []): string`
- `minify(string $code, string $type, array $options = []): string`
- `formatFile(string $filePath, array $options = []): string`
- `minifyFile(string $filePath, array $options = []): string`
- `formatFileAndSave(string $input, ?string $output = null, array $options = []): bool`
- `minifyFileAndSave(string $input, ?string $output = null, array $options = []): bool`
- `validate(string $code, string $type): bool`
- `register(string $type, FormatterInterface $formatter): void`
- `getFormatter(string $type): FormatterInterface`
- `getFormatterByExtension(string $filename): FormatterInterface`
- `getSupportedTypes(): array`

## Performance Tips

1. **Reuse formatters**: Get the formatter once and reuse it for multiple operations
2. **Batch operations**: Process multiple files in a loop rather than separate calls
3. **Disable comments**: Set `preserve_comments => false` for faster minification
4. **File operations**: Use `formatFileAndSave()` for direct file-to-file operations

## License

MIT License - feel free to use in any project

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## Support

For issues, questions, or contributions, please open an issue on GitHub.
