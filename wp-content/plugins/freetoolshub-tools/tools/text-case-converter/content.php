<?php

/**
 * Text Case Converter — page content (see FTH_Tool_Content)
 */

if (!defined('ABSPATH')) {
    exit;
}

return array(

    'seo_title'       => __('Text Case Converter – Uppercase, Lowercase & Title Case', 'freetoolshub-tools'),

    'seo_description' => __('Convert text to UPPERCASE, lowercase, Title Case or Sentence case in one click. Free online case converter with copy and undo, no sign-up needed.', 'freetoolshub-tools'),

    'intro' => array(
        __('Typed a heading with Caps Lock on, or pasted text with messy capitalisation? The Text Case Converter changes the case of any text in one click, so you never have to retype it.', 'freetoolshub-tools'),
        __('Choose UPPERCASE, lowercase, Title Case or Sentence case, then copy the result into your document, email or website. Conversions run in your browser and can be undone with Ctrl+Z.', 'freetoolshub-tools'),
    ),

    'features' => array(
        __('Four case styles: UPPERCASE, lowercase, Title Case and Sentence case', 'freetoolshub-tools'),
        __('Smart Title Case that keeps short words such as "and", "of" and "the" lowercase', 'freetoolshub-tools'),
        __('Sentence case that capitalises each new sentence and the word "I"', 'freetoolshub-tools'),
        __('Works with accented and non-English letters', 'freetoolshub-tools'),
        __('Undo with Ctrl+Z, plus Copy and Clear buttons', 'freetoolshub-tools'),
    ),

    'steps' => array(
        __('Type or paste your text into the box.', 'freetoolshub-tools'),
        __('Click UPPERCASE, lowercase, Title Case or Sentence case.', 'freetoolshub-tools'),
        __('Click Copy Text to copy the converted text.', 'freetoolshub-tools'),
        __('Changed your mind? Press Ctrl+Z (Cmd+Z on Mac) to undo the conversion.', 'freetoolshub-tools'),
    ),

    'faqs' => array(
        array(
            'question' => __('What is Title Case?', 'freetoolshub-tools'),
            'answer'   => __('Title Case capitalises the first letter of each main word, as in book and article titles. Short words such as "a", "and", "of", "the" and "to" stay lowercase unless they are the first or last word or follow a colon or full stop.', 'freetoolshub-tools'),
        ),
        array(
            'question' => __('What is Sentence case?', 'freetoolshub-tools'),
            'answer'   => __('Sentence case capitalises only the first letter of each sentence and the word "I", like normal writing. Everything else becomes lowercase, so you may need to re-capitalise names and brands afterwards.', 'freetoolshub-tools'),
        ),
        array(
            'question' => __('Can I undo a conversion?', 'freetoolshub-tools'),
            'answer'   => __('Yes. Click in the text box and press Ctrl+Z (Cmd+Z on a Mac) to undo the last conversion in most browsers.', 'freetoolshub-tools'),
        ),
        array(
            'question' => __('Does it work with accented letters and other languages?', 'freetoolshub-tools'),
            'answer'   => __('Yes. Letters such as é, ü and ç are converted correctly. The Title Case rules for short words are based on English.', 'freetoolshub-tools'),
        ),
        array(
            'question' => __('Is my text stored anywhere?', 'freetoolshub-tools'),
            'answer'   => __('No. Your text is converted in your browser and is never sent to our server.', 'freetoolshub-tools'),
        ),
    ),

    'app_category' => 'UtilitiesApplication',
);
