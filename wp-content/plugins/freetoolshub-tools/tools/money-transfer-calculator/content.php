<?php

/**
 * International Money Transfer Cost Calculator — page content (see FTH_Tool_Content)
 */

if (!defined('ABSPATH')) {
    exit;
}

return array(

    'seo_title'       => __('Money Transfer Calculator – Fees, Exchange Rates & Amount Received', 'freetoolshub-tools'),

    'seo_description' => __('Free international money transfer calculator. See exactly how much the recipient gets after fees and exchange-rate markups, and compare two providers side by side.', 'freetoolshub-tools'),

    'intro' => array(
        __('A low transfer fee doesn\'t always mean a cheap transfer. Many banks and transfer services also give you a worse exchange rate than the real one and keep the difference, so the recipient gets less than you expect.', 'freetoolshub-tools'),
        __('The International Money Transfer Cost Calculator shows what really arrives. Enter the amount, the exchange rate and the fees for up to two providers. It works out the final amount received, the hidden exchange-rate markup and the total cost, then tells you which provider is the better deal.', 'freetoolshub-tools'),
    ),

    'features' => array(
        __('Supports INR, USD, GBP, EUR, CAD, AUD, AED, SGD and 25+ more currencies', 'freetoolshub-tools'),
        __('Fixed fees, percentage fees and receiving bank fees', 'freetoolshub-tools'),
        __('Fees taken from the amount sent, or added on top', 'freetoolshub-tools'),
        __('Shows the hidden exchange-rate markup against the mid-market rate', 'freetoolshub-tools'),
        __('One-click reference rate from the European Central Bank', 'freetoolshub-tools'),
        __('Compares two providers side by side and highlights the better deal', 'freetoolshub-tools'),
        __('Copy Results to share or save the comparison', 'freetoolshub-tools'),
    ),

    'steps' => array(
        __('Enter the amount you\'re sending and choose the sending and receiving currencies.', 'freetoolshub-tools'),
        __('Optional: click Get today\'s rate (or type the mid-market rate) to see how much each provider\'s exchange rate costs you.', 'freetoolshub-tools'),
        __('For each provider, enter the exchange rate they offer and their fees. Choose whether the fee is taken from your amount or added on top.', 'freetoolshub-tools'),
        __('Read what the recipient gets, the total cost and which provider is the better deal. Click Copy Results to save it.', 'freetoolshub-tools'),
    ),

    'faqs' => array(
        array(
            'question' => __('How do I calculate how much money the recipient will receive?', 'freetoolshub-tools'),
            'answer'   => __('Take off the transfer fees, multiply what\'s left by the exchange rate, then subtract any receiving bank fee. For example, sending $1,000 with a $10 fee at 1 USD = 83 INR gives (1,000 − 10) × 83 = ₹82,170.', 'freetoolshub-tools'),
        ),
        array(
            'question' => __('What is the mid-market exchange rate?', 'freetoolshub-tools'),
            'answer'   => __('The mid-market rate is the midpoint between the buying and selling price of a currency, the "real" rate you see on Google or XE. Banks and transfer services usually give you a lower rate and keep the difference as a hidden fee.', 'freetoolshub-tools'),
        ),
        array(
            'question' => __('What is an exchange rate markup?', 'freetoolshub-tools'),
            'answer'   => __('It\'s the gap between the mid-market rate and the rate a provider offers you. If the mid-market rate is 83.00 and a bank gives you 81.34, the markup is 2%, which costs you $20 on a $1,000 transfer, even if the bank says the transfer is "fee-free".', 'freetoolshub-tools'),
        ),
        array(
            'question' => __('How do I compare two money transfer providers?', 'freetoolshub-tools'),
            'answer'   => __('Enter each provider\'s exchange rate and fees in the two provider cards. The calculator compares how much the recipient gets for every unit you pay and marks the better deal. A provider with a higher fee can still be cheaper if its exchange rate is better.', 'freetoolshub-tools'),
        ),
        array(
            'question' => __('Where does the "Get today\'s rate" figure come from?', 'freetoolshub-tools'),
            'answer'   => __('It\'s the European Central Bank\'s reference rate, loaded from frankfurter.dev and updated once each working day. The AED, SAR and QAR rates are based on their official fixed rates to the US dollar, and NPR on its fixed rate to the Indian rupee. For other currencies, type in the rate yourself.', 'freetoolshub-tools'),
        ),
        array(
            'question' => __('Is the amount shown exactly what will arrive?', 'freetoolshub-tools'),
            'answer'   => __('It\'s accurate for the rate and fees you enter. Providers change their rates through the day, and some transfers pass through intermediary banks that take an extra fee. Check the provider\'s quote just before you send.', 'freetoolshub-tools'),
        ),
    ),

    'app_category' => 'FinanceApplication',
);
