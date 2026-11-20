<?php

/**
 * Percentage Calculator — page content (see FTH_Tool_Content)
 */

if (!defined('ABSPATH')) {
    exit;
}

return array(

    'seo_title'       => __('Percentage Calculator – Percent of a Number & % Change', 'freetoolshub-tools'),

    'seo_description' => __('Free percentage calculator. Find X% of a number, what percent one number is of another, and the percentage increase or decrease, with working shown.', 'freetoolshub-tools'),

    'intro' => array(
        __('Work out discounts, tips, tax, test scores, price rises and growth in seconds. The Percentage Calculator includes the three calculations people need most, and each one updates as you type.', 'freetoolshub-tools'),
        __('Every answer shows the formula used, so you can check the working or learn how to do it yourself.', 'freetoolshub-tools'),
    ),

    'features' => array(
        __('Percentage of a number: what is 20% of 500?', 'freetoolshub-tools'),
        __('Find the percentage: 50 is what percent of 200?', 'freetoolshub-tools'),
        __('Percentage change: the increase or decrease from 100 to 150', 'freetoolshub-tools'),
        __('Shows the formula for every result', 'freetoolshub-tools'),
        __('Copy Result and Clear buttons for each calculator', 'freetoolshub-tools'),
    ),

    'steps' => array(
        __('Pick the calculator that matches your question.', 'freetoolshub-tools'),
        __('Enter your numbers. The result appears as you type.', 'freetoolshub-tools'),
        __('Read the result and the formula underneath it.', 'freetoolshub-tools'),
        __('Click Copy Result to copy the answer, or Clear to start again.', 'freetoolshub-tools'),
    ),

    'faqs' => array(
        array(
            'question' => __('How do I calculate a percentage of a number?', 'freetoolshub-tools'),
            'answer'   => __('Multiply the number by the percentage and divide by 100. For example, 20% of 500 is 500 × 20 ÷ 100 = 100.', 'freetoolshub-tools'),
        ),
        array(
            'question' => __('How do I work out what percentage one number is of another?', 'freetoolshub-tools'),
            'answer'   => __('Divide the part by the total and multiply by 100. For example, 50 out of 200 is 50 ÷ 200 × 100 = 25%.', 'freetoolshub-tools'),
        ),
        array(
            'question' => __('How is percentage change calculated?', 'freetoolshub-tools'),
            'answer'   => __('Subtract the original value from the new value, divide by the original value and multiply by 100. From 100 to 150 is (150 − 100) ÷ 100 × 100 = a 50% increase. A negative result means a decrease.', 'freetoolshub-tools'),
        ),
        array(
            'question' => __('Why can\'t I calculate a percentage change from 0?', 'freetoolshub-tools'),
            'answer'   => __('Percentage change divides by the original value, and dividing by zero is undefined. A change from 0 cannot be expressed as a percentage.', 'freetoolshub-tools'),
        ),
        array(
            'question' => __('What is the difference between percentage change and percentage points?', 'freetoolshub-tools'),
            'answer'   => __('Percentage change compares two values relative to the first one. Percentage points are the simple difference between two percentages. For example, a rate rising from 10% to 15% is a rise of 5 percentage points, which is a 50% increase.', 'freetoolshub-tools'),
        ),
    ),

    'app_category' => 'UtilitiesApplication',
);
