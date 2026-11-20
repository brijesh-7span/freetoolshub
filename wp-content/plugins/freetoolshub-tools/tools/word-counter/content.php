<?php

/**
 * Word Counter — page content (see FTH_Tool_Content)
 */

if (!defined('ABSPATH')) {
    exit;
}

return array(

    'seo_title'       => __('Word Counter – Count Words, Characters & Sentences', 'freetoolshub-tools'),

    'seo_description' => __('Free online word counter. Count words, characters with and without spaces, sentences and paragraphs as you type, and see your reading time.', 'freetoolshub-tools'),

    'intro' => array(
        __('The Word Counter shows how long your text is while you type. Paste an essay, blog post, assignment or social media caption and instantly see the number of words, characters, sentences and paragraphs, plus an estimated reading time.', 'freetoolshub-tools'),
        __('It is ideal for staying within word limits for school work, job applications, SEO meta descriptions and posts with character limits. Everything is counted in your browser, so your text is never uploaded or stored.', 'freetoolshub-tools'),
    ),

    'features' => array(
        __('Live word count that updates as you type or paste', 'freetoolshub-tools'),
        __('Character count with and without spaces', 'freetoolshub-tools'),
        __('Sentence and paragraph count', 'freetoolshub-tools'),
        __('Estimated reading time', 'freetoolshub-tools'),
        __('One-click Copy and Clear buttons', 'freetoolshub-tools'),
    ),

    'steps' => array(
        __('Type or paste your text into the box.', 'freetoolshub-tools'),
        __('Watch the word, character, sentence and paragraph counts update as you type.', 'freetoolshub-tools'),
        __('Check the reading time to see how long your text takes to read.', 'freetoolshub-tools'),
        __('Click Copy Text to copy your text, or Clear Text to start again.', 'freetoolshub-tools'),
    ),

    'faqs' => array(
        array(
            'question' => __('How are words counted?', 'freetoolshub-tools'),
            'answer'   => __('A word is any group of letters or numbers. Contractions such as "don\'t" and hyphenated words such as "well-known" count as one word, and punctuation on its own is not counted.', 'freetoolshub-tools'),
        ),
        array(
            'question' => __('What is the difference between characters with and without spaces?', 'freetoolshub-tools'),
            'answer'   => __('"Characters" counts every letter, number, space, punctuation mark and emoji. "Characters (no spaces)" leaves out spaces, tabs and line breaks, which some forms and platforms use for their limits.', 'freetoolshub-tools'),
        ),
        array(
            'question' => __('How is reading time calculated?', 'freetoolshub-tools'),
            'answer'   => __('Reading time assumes an average reading speed of 200 words per minute. Real reading speed varies from person to person and with how technical the text is.', 'freetoolshub-tools'),
        ),
        array(
            'question' => __('How are sentences and paragraphs counted?', 'freetoolshub-tools'),
            'answer'   => __('A sentence ends with a full stop, question mark, exclamation mark or ellipsis followed by a space or the end of the text, so numbers like 3.14 are not split. Each line that contains text counts as a paragraph.', 'freetoolshub-tools'),
        ),
        array(
            'question' => __('Is my text saved or uploaded?', 'freetoolshub-tools'),
            'answer'   => __('No. Counting happens entirely in your browser. Your text is not sent to our server or stored anywhere.', 'freetoolshub-tools'),
        ),
    ),

    'app_category' => 'UtilitiesApplication',
);
