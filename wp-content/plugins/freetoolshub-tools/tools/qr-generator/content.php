<?php

/**
 * QR Code Generator — page content (see FTH_Tool_Content)
 */

if (!defined('ABSPATH')) {
    exit;
}

return array(

    'seo_title'       => __('QR Code Generator – Create Free QR Codes Online', 'freetoolshub-tools'),

    'seo_description' => __('Create free QR codes for links and text. Choose the size and colors, preview instantly and download a PNG. No sign-up, and the codes never expire.', 'freetoolshub-tools'),

    'intro' => array(
        __('Turn any website address or text into a QR code that people can scan with their phone camera. Pick a size and colors, then download your QR code as a PNG image for flyers, menus, business cards, packaging or slides.', 'freetoolshub-tools'),
        __('These are static QR codes: the content is stored inside the code itself, so they keep working forever and do not depend on our website.', 'freetoolshub-tools'),
    ),

    'features' => array(
        __('QR codes for website links, plain text and any language, including emoji', 'freetoolshub-tools'),
        __('Three sizes: 128, 256 and 384 pixels', 'freetoolshub-tools'),
        __('Custom foreground and background colors with a contrast warning', 'freetoolshub-tools'),
        __('Instant preview that updates as you type', 'freetoolshub-tools'),
        __('Free PNG download that never expires', 'freetoolshub-tools'),
    ),

    'steps' => array(
        __('Enter a website address (for example https://example.com) or any text.', 'freetoolshub-tools'),
        __('Choose a size: 128, 256 or 384 pixels.', 'freetoolshub-tools'),
        __('Optionally change the foreground and background colors.', 'freetoolshub-tools'),
        __('Scan the preview with your phone to test it, then click Download PNG.', 'freetoolshub-tools'),
    ),

    'faqs' => array(
        array(
            'question' => __('Do the QR codes expire?', 'freetoolshub-tools'),
            'answer'   => __('No. The content is encoded directly in the QR code, so it keeps working forever and does not rely on FreeToolsHub. To change what a code contains, create a new one.', 'freetoolshub-tools'),
        ),
        array(
            'question' => __('Can I use the QR codes for my business?', 'freetoolshub-tools'),
            'answer'   => __('Yes. You can use the QR codes you create anywhere, including flyers, menus, packaging, business cards and websites.', 'freetoolshub-tools'),
        ),
        array(
            'question' => __('Which size should I choose?', 'freetoolshub-tools'),
            'answer'   => __('Use 128 pixels for small on-screen use, 256 pixels for most purposes and 384 pixels for print or long text. Longer content makes the code more detailed, so choose a larger size if it is hard to scan.', 'freetoolshub-tools'),
        ),
        array(
            'question' => __('Why won\'t my colored QR code scan?', 'freetoolshub-tools'),
            'answer'   => __('Scanners need strong contrast. Use a dark foreground on a light background, because light-on-dark and low-contrast colors may not scan. The tool warns you when your colors could cause problems.', 'freetoolshub-tools'),
        ),
        array(
            'question' => __('How much text can a QR code hold?', 'freetoolshub-tools'),
            'answer'   => __('This tool accepts up to 2,000 characters. Shorter content creates a simpler code that is easier to scan, so use a short link where possible.', 'freetoolshub-tools'),
        ),
    ),

    'app_category' => 'UtilitiesApplication',
);
