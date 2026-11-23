<?php

if (!defined('ABSPATH')) {
    exit;
}

class FTH_Shortcodes
{

    /**
     * Tool identifier (_fth_tool_slug) => shortcode tag.
     *
     * Each tool lives in tools/{identifier}/ with {identifier}.php, .js and .css.
     */
    const TOOLS = array(
        'word-counter'              => 'fth_word_counter',
        'text-case-converter'       => 'fth_text_case_converter',
        'image-resizer'             => 'fth_image_resizer',
        'image-compressor'          => 'fth_image_compressor',
        'qr-generator'              => 'fth_qr_generator',
        'json-formatter'            => 'fth_json_formatter',
        'percentage-calculator'     => 'fth_percentage_calculator',
        'age-calculator'            => 'fth_age_calculator',
        'money-transfer-calculator' => 'fth_money_transfer_calculator',
    );

    /**
     * Tools that use the shared image helpers (assets/js/fth-image.js, assets/css/fth-image.css)
     */
    const IMAGE_TOOLS = array(
        'image-resizer',
        'image-compressor',
        'qr-generator',
    );

    /**
     * Extra script dependencies per tool (registered in register_assets())
     */
    const EXTRA_SCRIPT_DEPS = array(
        'qr-generator' => array('fth-qrcode-lib'),
    );

    public function __construct()
    {

        add_action('wp_enqueue_scripts', array($this, 'register_assets'));

        foreach (self::TOOLS as $tool_slug => $tag) {
            add_shortcode($tag, function () use ($tool_slug) {
                return $this->render_tool($tool_slug);
            });
        }
    }


    /**
     * Get the shortcode tag for a tool identifier, or '' if none exists
     */
    public static function get_tool_shortcode($tool_slug)
    {

        return isset(self::TOOLS[$tool_slug]) ? self::TOOLS[$tool_slug] : '';
    }


    /**
     * Register shared + per-tool assets (enqueued only when a tool is rendered)
     */
    public function register_assets()
    {

        wp_register_style(
            'fth-tools',
            FTH_PLUGIN_URL . 'assets/css/fth-tools.css',
            array(),
            fth_plugin_asset_version('assets/css/fth-tools.css')
        );

        wp_register_script(
            'fth-tools',
            FTH_PLUGIN_URL . 'assets/js/fth-tools.js',
            array(),
            fth_plugin_asset_version('assets/js/fth-tools.js'),
            array('in_footer' => true, 'strategy' => 'defer')
        );

        wp_localize_script('fth-tools', 'fthToolsI18n', array(
            'copied'     => __('Text copied to clipboard!', 'freetoolshub-tools'),
            'copyFailed' => __('Could not copy — please copy manually.', 'freetoolshub-tools'),
            'cleared'    => __('Text cleared.', 'freetoolshub-tools'),
        ));

        wp_register_style(
            'fth-image',
            FTH_PLUGIN_URL . 'assets/css/fth-image.css',
            array('fth-tools'),
            fth_plugin_asset_version('assets/css/fth-image.css')
        );

        wp_register_script(
            'fth-image',
            FTH_PLUGIN_URL . 'assets/js/fth-image.js',
            array('fth-tools'),
            fth_plugin_asset_version('assets/js/fth-image.js'),
            array('in_footer' => true, 'strategy' => 'defer')
        );

        wp_localize_script('fth-image', 'fthImageI18n', array(
            'invalidType'  => __('Please choose a JPG, PNG or WebP image.', 'freetoolshub-tools'),
            'tooLarge'     => __('This file is larger than 25 MB. Please choose a smaller image.', 'freetoolshub-tools'),
            'readError'    => __('Sorry, this image could not be read. It may be damaged or in an unsupported format.', 'freetoolshub-tools'),
            'processError' => __('This image is too large for your browser to process. Try smaller dimensions.', 'freetoolshub-tools'),
            'webpFallback' => __('Your browser can\'t create WebP images, so PNG was used instead.', 'freetoolshub-tools'),
        ));

        // Third-party: qrcode-generator 1.4.4 by Kazuhiko Arase (MIT), bundled locally
        wp_register_script(
            'fth-qrcode-lib',
            FTH_PLUGIN_URL . 'assets/vendor/qrcode-generator/qrcode.js',
            array(),
            '1.4.4',
            array('in_footer' => true, 'strategy' => 'defer')
        );

        foreach (array_keys(self::TOOLS) as $tool_slug) {

            $dependency = in_array($tool_slug, self::IMAGE_TOOLS, true) ? 'fth-image' : 'fth-tools';

            $script_deps = isset(self::EXTRA_SCRIPT_DEPS[$tool_slug])
                ? array_merge(array($dependency), self::EXTRA_SCRIPT_DEPS[$tool_slug])
                : array($dependency);

            wp_register_style(
                'fth-' . $tool_slug,
                FTH_PLUGIN_URL . 'tools/' . $tool_slug . '/' . $tool_slug . '.css',
                array($dependency),
                fth_plugin_asset_version('tools/' . $tool_slug . '/' . $tool_slug . '.css')
            );

            wp_register_script(
                'fth-' . $tool_slug,
                FTH_PLUGIN_URL . 'tools/' . $tool_slug . '/' . $tool_slug . '.js',
                $script_deps,
                fth_plugin_asset_version('tools/' . $tool_slug . '/' . $tool_slug . '.js'),
                array('in_footer' => true, 'strategy' => 'defer')
            );
        }

        wp_localize_script('fth-word-counter', 'fthWordCounter', array(
            'sec' => __('sec', 'freetoolshub-tools'),
            'min' => __('min', 'freetoolshub-tools'),
        ));

        wp_localize_script('fth-text-case-converter', 'fthTextCase', array(
            'converted' => array(
                'upper'    => __('Converted to UPPERCASE.', 'freetoolshub-tools'),
                'lower'    => __('Converted to lowercase.', 'freetoolshub-tools'),
                'title'    => __('Converted to Title Case.', 'freetoolshub-tools'),
                'sentence' => __('Converted to Sentence case.', 'freetoolshub-tools'),
            ),
        ));

        wp_localize_script('fth-image-resizer', 'fthImageResizer', array(
            'invalidSize' => __('Enter a width and height between 1 and 10000 pixels.', 'freetoolshub-tools'),
            'smaller'     => __('smaller', 'freetoolshub-tools'),
            'larger'      => __('larger', 'freetoolshub-tools'),
            'ready'       => __('Resized image is ready to download.', 'freetoolshub-tools'),
        ));

        wp_localize_script('fth-age-calculator', 'fthAgeCalculator', array(
            /* translators: %s: number of years */
            'year'           => array(__('%s year', 'freetoolshub-tools'), __('%s years', 'freetoolshub-tools')),
            /* translators: %s: number of months */
            'month'          => array(__('%s month', 'freetoolshub-tools'), __('%s months', 'freetoolshub-tools')),
            /* translators: %s: number of days */
            'day'            => array(__('%s day', 'freetoolshub-tools'), __('%s days', 'freetoolshub-tools')),
            /* translators: %s: number of weeks */
            'week'           => array(__('%s week', 'freetoolshub-tools'), __('%s weeks', 'freetoolshub-tools')),
            'and'            => __('and', 'freetoolshub-tools'),
            /* translators: %s: age, e.g. "25 years, 3 months and 12 days" */
            'summaryPresent' => __('You are %s old.', 'freetoolshub-tools'),
            /* translators: 1: age, 2: date */
            'summaryPast'    => __('On %2$s, you were %1$s old.', 'freetoolshub-tools'),
            /* translators: 1: age, 2: date */
            'summaryFuture'  => __('On %2$s, you will be %1$s old.', 'freetoolshub-tools'),
            /* translators: %s: age in years */
            'birthdayToday'  => __('Happy birthday! 🎉 You turn %s today.', 'freetoolshub-tools'),
            /* translators: %s: age in years on the selected date */
            'birthdayOnDate' => __('That\'s your birthday 🎉 (turning %s)', 'freetoolshub-tools'),
            /* translators: 1: days until birthday, 2: birthday date, 3: age turning */
            'nextBirthday'   => __('In %1$s — %2$s (turning %3$s)', 'freetoolshub-tools'),
            'invalidDate'    => __('Please enter a valid date.', 'freetoolshub-tools'),
            'futureBirth'    => __('The date of birth must be on or before the "age on" date.', 'freetoolshub-tools'),
        ));

        wp_localize_script('fth-percentage-calculator', 'fthPercentageCalculator', array(
            'increase'       => __('increase', 'freetoolshub-tools'),
            'decrease'       => __('decrease', 'freetoolshub-tools'),
            'noChange'       => __('No change', 'freetoolshub-tools'),
            'divideByZero'   => __('The total can\'t be 0 — you can\'t divide by zero.', 'freetoolshub-tools'),
            'changeFromZero' => __('Percentage change from 0 is undefined. Enter a non-zero original value.', 'freetoolshub-tools'),
            'tooLarge'       => __('The result is too large to calculate.', 'freetoolshub-tools'),
            'copied'         => __('Result copied!', 'freetoolshub-tools'),
            'copyFailed'     => __('Could not copy — please copy manually.', 'freetoolshub-tools'),
            'cleared'        => __('Cleared.', 'freetoolshub-tools'),
        ));

        wp_localize_script('fth-money-transfer-calculator', 'fthMoneyTransfer', array(
            'amountInvalid'   => __('Enter the amount you are sending.', 'freetoolshub-tools'),
            'rateInvalid'     => __('Enter the exchange rate this provider offers.', 'freetoolshub-tools'),
            'percentInvalid'  => __('The percentage fee must be between 0 and 100.', 'freetoolshub-tools'),
            'feesTooHigh'     => __('The fees are more than the amount being sent.', 'freetoolshub-tools'),
            'nothingLeft'     => __('The receiving fee is more than the converted amount.', 'freetoolshub-tools'),
            /* translators: %s: percentage, e.g. 0.5% */
            'betterThanMid'   => __('%s better than mid-market', 'freetoolshub-tools'),
            /* translators: 1: provider name, 2: extra amount received, 3: amount paid */
            'best'            => __('%1$s is the better deal: the recipient gets %2$s more for every %3$s you pay.', 'freetoolshub-tools'),
            'tie'             => __('Both providers give the recipient the same amount.', 'freetoolshub-tools'),
            'fetching'        => __('Getting today\'s rate…', 'freetoolshub-tools'),
            /* translators: %s: date of the rate */
            'fetched'         => __('European Central Bank reference rate for %s.', 'freetoolshub-tools'),
            'pegged'          => __('Official fixed rate.', 'freetoolshub-tools'),
            'fetchFailed'     => __('Couldn\'t load today\'s rate. Check your connection, or type the rate in yourself.', 'freetoolshub-tools'),
            /* translators: %s: currency code */
            'noLiveRate'      => __('A live rate isn\'t available for %s. Type the rate in yourself — you can find it on Google or XE.com.', 'freetoolshub-tools'),
            /* translators: %s: currency pair, e.g. USD → INR */
            'currencyChanged' => __('Currencies changed, so the rates were cleared. Enter the rates for %s.', 'freetoolshub-tools'),
            'copied'          => __('Results copied!', 'freetoolshub-tools'),
            'copyFailed'      => __('Could not copy — please copy manually.', 'freetoolshub-tools'),
            'cleared'         => __('Cleared.', 'freetoolshub-tools'),
            'summaryPaid'     => __('You pay', 'freetoolshub-tools'),
            'summaryGets'     => __('Recipient gets', 'freetoolshub-tools'),
            'summaryCost'     => __('Total cost', 'freetoolshub-tools'),
        ));

        wp_localize_script('fth-json-formatter', 'fthJsonFormatter', array(
            'valid'     => __('Valid JSON', 'freetoolshub-tools'),
            'invalid'   => __('Invalid JSON', 'freetoolshub-tools'),
            'empty'     => __('Paste or type JSON to validate it.', 'freetoolshub-tools'),
            /* translators: 1: line number, 2: column number */
            'location'  => __('Line %1$s, column %2$s', 'freetoolshub-tools'),
            'formatted' => __('JSON formatted.', 'freetoolshub-tools'),
            'minified'  => __('JSON minified.', 'freetoolshub-tools'),
            'tooDeep'   => __('This JSON is nested too deeply to process.', 'freetoolshub-tools'),
            'types'     => array(
                'object'  => __('Object', 'freetoolshub-tools'),
                'array'   => __('Array', 'freetoolshub-tools'),
                'string'  => __('String', 'freetoolshub-tools'),
                'number'  => __('Number', 'freetoolshub-tools'),
                'literal' => __('Value', 'freetoolshub-tools'),
            ),
            /* translators: %s: number of array items */
            'items'     => __('%s items', 'freetoolshub-tools'),
            /* translators: %s: number of object keys */
            'keys'      => __('%s keys', 'freetoolshub-tools'),
            'errors'    => array(
                'expectedValue'          => __('Expected a value (object, array, string, number, true, false or null).', 'freetoolshub-tools'),
                'expectedKey'            => __('Expected a property name in double quotes.', 'freetoolshub-tools'),
                'expectedColon'          => __('Expected \':\' after the property name.', 'freetoolshub-tools'),
                'expectedCommaOrBrace'   => __('Expected \',\' or \'}\' after the property value.', 'freetoolshub-tools'),
                'expectedCommaOrBracket' => __('Expected \',\' or \']\' after the array item.', 'freetoolshub-tools'),
                'trailingComma'          => __('Trailing commas are not allowed in JSON.', 'freetoolshub-tools'),
                'singleQuotes'           => __('Strings must use double quotes ("), not single quotes (\').', 'freetoolshub-tools'),
                'comment'                => __('Comments are not allowed in JSON.', 'freetoolshub-tools'),
                'unterminatedString'     => __('Unterminated string — missing closing double quote.', 'freetoolshub-tools'),
                'controlChar'            => __('Line breaks and tabs inside strings must be escaped (\n, \t).', 'freetoolshub-tools'),
                'invalidEscape'          => __('Invalid escape sequence in string.', 'freetoolshub-tools'),
                'invalidNumber'          => __('Invalid number.', 'freetoolshub-tools'),
                'unexpectedToken'        => __('Unexpected text — did you mean true, false or null?', 'freetoolshub-tools'),
                'extraData'              => __('Unexpected content after the end of the JSON.', 'freetoolshub-tools'),
            ),
        ));

        wp_localize_script('fth-qr-generator', 'fthQrGenerator', array(
            'tooLong'      => __('This text is too long for a QR code. Please shorten it.', 'freetoolshub-tools'),
            /* translators: %s: QR code size, e.g. 128px */
            'tooDense'     => __('This text is too long for a %s QR code. Choose a larger size or shorten the text.', 'freetoolshub-tools'),
            'smallModules' => __('This QR code is very dense at this size and may be hard to scan. Try a larger size.', 'freetoolshub-tools'),
            'lowContrast'  => __('Low contrast between the colors — this QR code may not scan. Use a darker foreground or lighter background.', 'freetoolshub-tools'),
            'inverted'     => __('Light-on-dark QR codes can\'t be read by some scanners. A dark foreground on a light background works best.', 'freetoolshub-tools'),
            'ready'        => __('QR code ready — download it as PNG.', 'freetoolshub-tools'),
            'cleared'      => __('Cleared. Enter new text to generate another QR code.', 'freetoolshub-tools'),
        ));

        wp_localize_script('fth-image-compressor', 'fthImageCompressor', array(
            'saved'      => __('You saved', 'freetoolshub-tools'),
            'increased'  => __('Size increase', 'freetoolshub-tools'),
            /* translators: %s: image format, e.g. WebP */
            'autoFormat' => __('Auto will save this image as %s.', 'freetoolshub-tools'),
            'ready'      => __('Compressed image is ready to download.', 'freetoolshub-tools'),
            'notSmaller' => __('This image is already well optimized — the compressed version isn\'t smaller. Try a lower quality, a smaller max width or WebP.', 'freetoolshub-tools'),
        ));

        $this->enqueue_styles_for_current_page();
    }


    /**
     * Load tool stylesheets in <head> on pages that show a tool,
     * avoiding a flash of unstyled content.
     */
    private function enqueue_styles_for_current_page()
    {

        if (!is_singular()) {
            return;
        }

        $post = get_queried_object();

        $current_tool = get_post_meta($post->ID, '_fth_tool_slug', true);

        foreach (self::TOOLS as $tool_slug => $tag) {

            if ($tool_slug === $current_tool || has_shortcode($post->post_content, $tag)) {
                wp_enqueue_style('fth-' . $tool_slug);
            }
        }
    }


    /**
     * Render a tool's markup and enqueue its assets
     */
    private function render_tool($tool_slug)
    {

        wp_enqueue_style('fth-' . $tool_slug);
        wp_enqueue_script('fth-' . $tool_slug);

        // Unique ID prefix per instance (for label/input pairs)
        $fth_id          = wp_unique_id('fth-' . $tool_slug . '-');
        $fth_textarea_id = $fth_id . '-input';

        ob_start();

        include FTH_PLUGIN_PATH . 'tools/' . $tool_slug . '/' . $tool_slug . '.php';

        return ob_get_clean();
    }
}
