<?php

/**
 * Age Calculator — page content (see FTH_Tool_Content)
 */

if (!defined('ABSPATH')) {
    exit;
}

return array(

    'seo_title'       => __('Age Calculator – Exact Age in Years, Months and Days', 'freetoolshub-tools'),

    'seo_description' => __('Calculate your exact age in years, months and days from your date of birth. See total weeks and days, your next birthday, and your age on any date.', 'freetoolshub-tools'),

    'intro' => array(
        __('Find out exactly how old you are, or how old someone was or will be on any date. Enter a date of birth and the Age Calculator shows the age in years, months and days.', 'freetoolshub-tools'),
        __('You also get the total number of months, weeks and days, the day of the week you were born and a countdown to your next birthday. Useful for forms, eligibility checks, anniversaries and birthday planning.', 'freetoolshub-tools'),
    ),

    'features' => array(
        __('Exact age in years, months and days', 'freetoolshub-tools'),
        __('Age on any past or future date', 'freetoolshub-tools'),
        __('Total months, weeks and days lived', 'freetoolshub-tools'),
        __('Next birthday countdown and the day of the week you were born', 'freetoolshub-tools'),
        __('Correct handling of leap years and 29 February birthdays', 'freetoolshub-tools'),
    ),

    'steps' => array(
        __('Enter the date of birth.', 'freetoolshub-tools'),
        __('Leave "Age on this date" as today, or choose another date.', 'freetoolshub-tools'),
        __('Read the age in years, months and days, plus the totals and next birthday below.', 'freetoolshub-tools'),
        __('Click Reset to start again.', 'freetoolshub-tools'),
    ),

    'faqs' => array(
        array(
            'question' => __('How is age calculated?', 'freetoolshub-tools'),
            'answer'   => __('Age is counted in complete years and months from the date of birth, then the remaining days. If a month is too short for the birth date (for example, someone born on the 31st), the last day of that month counts as the monthly anniversary.', 'freetoolshub-tools'),
        ),
        array(
            'question' => __('Can I calculate age on a past or future date?', 'freetoolshub-tools'),
            'answer'   => __('Yes. Change "Age on this date" to any date to see how old someone was, or will be, on that day.', 'freetoolshub-tools'),
        ),
        array(
            'question' => __('How are birthdays on 29 February handled?', 'freetoolshub-tools'),
            'answer'   => __('In years without a 29 February, the birthday is counted on 28 February. Note that some countries use 1 March for legal purposes.', 'freetoolshub-tools'),
        ),
        array(
            'question' => __('Why do total weeks show extra days?', 'freetoolshub-tools'),
            'answer'   => __('Total weeks counts complete seven-day weeks. Any remaining days are shown separately, for example "1,902 weeks and 6 days".', 'freetoolshub-tools'),
        ),
        array(
            'question' => __('Is my date of birth stored?', 'freetoolshub-tools'),
            'answer'   => __('No. The calculation happens in your browser, and your date of birth is never sent to our server.', 'freetoolshub-tools'),
        ),
    ),

    'app_category' => 'UtilitiesApplication',
);
