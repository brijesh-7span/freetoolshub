<?php

/**
 * JSON Formatter — page content (see FTH_Tool_Content)
 */

if (!defined('ABSPATH')) {
    exit;
}

return array(

    'seo_title'       => __('JSON Formatter & Validator – Beautify and Minify JSON', 'freetoolshub-tools'),

    'seo_description' => __('Format, validate and minify JSON online. Find errors by exact line and column, choose your indentation and copy the result. Numbers stay exact.', 'freetoolshub-tools'),

    'intro' => array(
        __('Paste messy or minified JSON and turn it into clean, readable, properly indented code, or minify it into a single line to save space.', 'freetoolshub-tools'),
        __('The validator checks your JSON as you type and explains problems in plain English with the exact line and column, such as trailing commas, single quotes or missing brackets. Your data never leaves your browser.', 'freetoolshub-tools'),
    ),

    'features' => array(
        __('Live JSON validation with exact line and column for errors', 'freetoolshub-tools'),
        __('Format (beautify) with 2 spaces, 4 spaces or tabs', 'freetoolshub-tools'),
        __('Minify JSON into a single compact line', 'freetoolshub-tools'),
        __('Keeps large numbers and text exactly as written, with no rounding', 'freetoolshub-tools'),
        __('One-click Copy and Clear buttons', 'freetoolshub-tools'),
    ),

    'steps' => array(
        __('Paste or type your JSON into the box.', 'freetoolshub-tools'),
        __('Check the status bar: it shows "Valid JSON" or the line and column of the first error.', 'freetoolshub-tools'),
        __('Click Format to make the JSON readable, or Minify to make it compact. Use the Indent menu to choose spaces or tabs.', 'freetoolshub-tools'),
        __('Click Copy to copy the result.', 'freetoolshub-tools'),
    ),

    'faqs' => array(
        array(
            'question' => __('What does a JSON formatter do?', 'freetoolshub-tools'),
            'answer'   => __('It adds line breaks and indentation so JSON is easy to read (formatting or "beautifying"), or removes all unnecessary spaces to make it as small as possible (minifying). The data itself does not change.', 'freetoolshub-tools'),
        ),
        array(
            'question' => __('Why is my JSON invalid?', 'freetoolshub-tools'),
            'answer'   => __('Common causes are a trailing comma after the last item, single quotes instead of double quotes, property names without quotes, comments, and values such as undefined or NaN. The validator shows the exact line and column of the first problem.', 'freetoolshub-tools'),
        ),
        array(
            'question' => __('Will formatting change my numbers?', 'freetoolshub-tools'),
            'answer'   => __('No. Many formatters convert numbers through JavaScript, which rounds very large values such as 12345678901234567890. This tool keeps every number and string exactly as you wrote it.', 'freetoolshub-tools'),
        ),
        array(
            'question' => __('Is my JSON sent to a server?', 'freetoolshub-tools'),
            'answer'   => __('No. Validation and formatting run entirely in your browser, so API keys, tokens and other private data stay on your device.', 'freetoolshub-tools'),
        ),
        array(
            'question' => __('Can I format large JSON files?', 'freetoolshub-tools'),
            'answer'   => __('Yes. JSON of a few megabytes usually formats in a second or two. Very large files depend on your device\'s speed and memory.', 'freetoolshub-tools'),
        ),
    ),

    'app_category' => 'DeveloperApplication',
);
