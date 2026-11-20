<?php

/**
 * Image Resizer — page content (see FTH_Tool_Content)
 */

if (!defined('ABSPATH')) {
    exit;
}

return array(

    'seo_title'       => __('Image Resizer – Resize JPG, PNG & WebP Images Online', 'freetoolshub-tools'),

    'seo_description' => __('Resize JPG, PNG and WebP images to exact pixel sizes for free. Keep the aspect ratio, choose format and quality, and download instantly. No upload.', 'freetoolshub-tools'),

    'intro' => array(
        __('Need a photo at an exact size for a website, profile picture, online form or social media post? The Image Resizer changes the width and height of JPG, PNG and WebP images in seconds.', 'freetoolshub-tools'),
        __('Your image is resized in your browser, so it is never uploaded to a server. That makes it fast, and your photos stay private.', 'freetoolshub-tools'),
    ),

    'features' => array(
        __('Resize to any width and height from 1 to 10,000 pixels', 'freetoolshub-tools'),
        __('Maintain aspect ratio so images are never stretched', 'freetoolshub-tools'),
        __('Quick 25%, 50%, 75% and 100% size presets', 'freetoolshub-tools'),
        __('Save as JPG, PNG or WebP with adjustable quality', 'freetoolshub-tools'),
        __('Side-by-side preview showing the new size and file size', 'freetoolshub-tools'),
    ),

    'steps' => array(
        __('Drag and drop an image onto the upload area, or click to choose one (JPG, PNG or WebP, up to 25 MB).', 'freetoolshub-tools'),
        __('Enter a new width or height, or pick a percentage. Keep "Maintain aspect ratio" ticked to avoid stretching.', 'freetoolshub-tools'),
        __('Choose the output format and, for JPG or WebP, the quality.', 'freetoolshub-tools'),
        __('Check the preview, then click Download Image.', 'freetoolshub-tools'),
    ),

    'faqs' => array(
        array(
            'question' => __('Are my images uploaded to your server?', 'freetoolshub-tools'),
            'answer'   => __('No. Images are resized in your browser using HTML canvas and never leave your device.', 'freetoolshub-tools'),
        ),
        array(
            'question' => __('What is the maximum file size and image size?', 'freetoolshub-tools'),
            'answer'   => __('You can open images up to 25 MB and resize them to between 1 and 10,000 pixels wide or tall. Very large sizes may be limited by your browser\'s memory, especially on phones.', 'freetoolshub-tools'),
        ),
        array(
            'question' => __('Will resizing reduce image quality?', 'freetoolshub-tools'),
            'answer'   => __('Making an image smaller keeps it sharp. Making it larger than the original can look soft, because no new detail can be added. For JPG and WebP, a higher quality setting looks better but creates a larger file.', 'freetoolshub-tools'),
        ),
        array(
            'question' => __('Why did my transparent background turn white?', 'freetoolshub-tools'),
            'answer'   => __('JPG does not support transparency, so transparent areas are filled with white. Choose PNG or WebP to keep a transparent background.', 'freetoolshub-tools'),
        ),
        array(
            'question' => __('What does "Maintain aspect ratio" do?', 'freetoolshub-tools'),
            'answer'   => __('It keeps the width and height in the same proportion as the original. When you change one value, the other updates automatically so the image is not stretched or squashed.', 'freetoolshub-tools'),
        ),
    ),

    'app_category' => 'MultimediaApplication',
);
