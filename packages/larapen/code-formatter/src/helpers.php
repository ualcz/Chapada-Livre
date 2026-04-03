<?php

/**
 * Helper functions for CodeFormatter
 * Provides procedural-style API for convenience
 */

use CodeFormatter\CodeFormatter;

if (!function_exists('format_code')) {
    /**
     * Format code with pretty print
     *
     * @param string $code Code to format
     * @param string $type Format type (css, js, json, html)
     * @param array $options Formatting options
     * @return string Formatted code
     */
    function format_code(string $code, string $type, array $options = []): string
    {
        return CodeFormatter::prettyPrint($code, $type, $options);
    }
}

if (!function_exists('minify_code')) {
    /**
     * Minify code
     *
     * @param string $code Code to minify
     * @param string $type Format type (css, js, json, html)
     * @param array $options Minification options
     * @return string Minified code
     */
    function minify_code(string $code, string $type, array $options = []): string
    {
        return CodeFormatter::minify($code, $type, $options);
    }
}

if (!function_exists('format_css')) {
    /**
     * Format CSS code
     *
     * @param string $css CSS code to format
     * @param array $options Formatting options
     * @return string Formatted CSS
     */
    function format_css(string $css, array $options = []): string
    {
        return CodeFormatter::prettyPrint($css, 'css', $options);
    }
}

if (!function_exists('minify_css')) {
    /**
     * Minify CSS code
     *
     * @param string $css CSS code to minify
     * @param array $options Minification options
     * @return string Minified CSS
     */
    function minify_css(string $css, array $options = []): string
    {
        return CodeFormatter::minify($css, 'css', $options);
    }
}

if (!function_exists('format_js')) {
    /**
     * Format JavaScript code
     *
     * @param string $js JavaScript code to format
     * @param array $options Formatting options
     * @return string Formatted JavaScript
     */
    function format_js(string $js, array $options = []): string
    {
        return CodeFormatter::prettyPrint($js, 'js', $options);
    }
}

if (!function_exists('minify_js')) {
    /**
     * Minify JavaScript code
     *
     * @param string $js JavaScript code to minify
     * @param array $options Minification options
     * @return string Minified JavaScript
     */
    function minify_js(string $js, array $options = []): string
    {
        return CodeFormatter::minify($js, 'js', $options);
    }
}

if (!function_exists('format_json')) {
    /**
     * Format JSON code
     *
     * @param string $json JSON code to format
     * @param array $options Formatting options
     * @return string Formatted JSON
     */
    function format_json(string $json, array $options = []): string
    {
        return CodeFormatter::prettyPrint($json, 'json', $options);
    }
}

if (!function_exists('minify_json')) {
    /**
     * Minify JSON code
     *
     * @param string $json JSON code to minify
     * @param array $options Minification options
     * @return string Minified JSON
     */
    function minify_json(string $json, array $options = []): string
    {
        return CodeFormatter::minify($json, 'json', $options);
    }
}

if (!function_exists('format_html')) {
    /**
     * Format HTML code
     *
     * @param string $html HTML code to format
     * @param array $options Formatting options
     * @return string Formatted HTML
     */
    function format_html(string $html, array $options = []): string
    {
        return CodeFormatter::prettyPrint($html, 'html', $options);
    }
}

if (!function_exists('minify_html')) {
    /**
     * Minify HTML code
     *
     * @param string $html HTML code to minify
     * @param array $options Minification options
     * @return string Minified HTML
     */
    function minify_html(string $html, array $options = []): string
    {
        return CodeFormatter::minify($html, 'html', $options);
    }
}

if (!function_exists('validate_code')) {
    /**
     * Validate code
     *
     * @param string $code Code to validate
     * @param string $type Format type
     * @return bool True if valid
     */
    function validate_code(string $code, string $type): bool
    {
        return CodeFormatter::validate($code, $type);
    }
}
