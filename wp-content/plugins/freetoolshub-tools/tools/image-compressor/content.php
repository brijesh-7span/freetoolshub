<?php

/**
 * Image Compressor — page content (see FTH_Tool_Content)
 */

if (!defined('ABSPATH')) {
    exit;
}

return array(

    'seo_title'       => __('Image Compressor – Reduce JPG, PNG & WebP File Size', 'freetoolshub-tools'),

    'seo_description' => __('Compress JPG, PNG and WebP images online for free. Adjust quality, compare original and compressed size, and download smaller images. No upload.', 'freetoolshub-tools'),

    'intro' => array(
        __('Large images slow down websites and are too big for many email attachments and upload forms. The Image Compressor reduces the file size of JPG, PNG and WebP images while keeping them looking good.', 'freetoolshub-tools'),
        __('Compare the original and compressed images side by side, see exactly how much space you saved, then download the result. Images are compressed in your browser and never uploaded.', 'freetoolshub-tools'),
    ),

    'features' => array(
        __('Adjustable compression quality from 10% to 100%', 'freetoolshub-tools'),
        __('Auto format: keeps JPG photos as JPG and converts PNG and WebP to smaller WebP files', 'freetoolshub-tools'),
        __('Optional maximum width (800 to 3840 pixels) for even smaller files', 'freetoolshub-tools'),
        __('Size comparison showing exactly how much you saved', 'freetoolshub-tools'),
        __('Removes hidden metadata such as camera details and GPS location', 'freetoolshub-tools'),
    ),

    'steps' => array(
        __('Drag and drop an image onto the upload area, or click to choose one (JPG, PNG or WebP, up to 25 MB).', 'freetoolshub-tools'),
        __('Adjust the compression quality. 75% is a good balance between size and quality.', 'freetoolshub-tools'),
        __('Optionally choose an output format and a maximum width.', 'freetoolshub-tools'),
        __('Compare the original and compressed sizes and previews.', 'freetoolshub-tools'),
        __('Click Download Compressed Image.', 'freetoolshub-tools'),
    ),

    'faqs' => array(
        array(
            'question' => __('How much smaller will my image get?', 'freetoolshub-tools'),
            'answer'   => __('It depends on the image. Large photos saved as PNG often shrink by 70% or more, while JPG files that are already compressed may only shrink a little. The tool shows the exact saving before you download.', 'freetoolshub-tools'),
        ),
        array(
            'question' => __('Which quality setting should I use?', 'freetoolshub-tools'),
            'answer'   => __('75% is a good starting point for photos on websites: the difference is hard to see and the file is much smaller. Lower it for smaller files, or raise it if you notice blur or blocky areas.', 'freetoolshub-tools'),
        ),
        array(
            'question' => __('Why is the compressed image bigger than the original?', 'freetoolshub-tools'),
            'answer'   => __('Some images are already heavily compressed, and re-saving them at a higher quality can make them larger. Try a lower quality, WebP format or a smaller maximum width, or keep your original file.', 'freetoolshub-tools'),
        ),
        array(
            'question' => __('What does the "Auto" format option do?', 'freetoolshub-tools'),
            'answer'   => __('Auto keeps JPG photos as JPG and converts PNG and WebP images to WebP, which is usually much smaller and keeps transparent backgrounds. If your browser cannot create WebP files, it uses JPG or PNG instead.', 'freetoolshub-tools'),
        ),
        array(
            'question' => __('Are my images uploaded to your server?', 'freetoolshub-tools'),
            'answer'   => __('No. Compression happens in your browser, so your images never leave your device.', 'freetoolshub-tools'),
        ),
    ),

    'app_category' => 'MultimediaApplication',
);
