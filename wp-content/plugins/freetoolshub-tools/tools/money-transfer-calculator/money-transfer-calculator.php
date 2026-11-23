<?php

/**
 * International Money Transfer Cost Calculator — markup for the [fth_money_transfer_calculator] shortcode
 *
 * @var string $fth_id Unique ID prefix for this instance.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Currencies offered in the dropdowns (most-used for transfers first).
 * Live reference rates are available for the codes listed in money-transfer-calculator.js.
 */
$fth_mt_currencies = array(
    'INR' => __('Indian Rupee', 'freetoolshub-tools'),
    'USD' => __('US Dollar', 'freetoolshub-tools'),
    'GBP' => __('British Pound', 'freetoolshub-tools'),
    'EUR' => __('Euro', 'freetoolshub-tools'),
    'CAD' => __('Canadian Dollar', 'freetoolshub-tools'),
    'AUD' => __('Australian Dollar', 'freetoolshub-tools'),
    'AED' => __('UAE Dirham', 'freetoolshub-tools'),
    'SGD' => __('Singapore Dollar', 'freetoolshub-tools'),
    'NZD' => __('New Zealand Dollar', 'freetoolshub-tools'),
    'SAR' => __('Saudi Riyal', 'freetoolshub-tools'),
    'QAR' => __('Qatari Riyal', 'freetoolshub-tools'),
    'CHF' => __('Swiss Franc', 'freetoolshub-tools'),
    'JPY' => __('Japanese Yen', 'freetoolshub-tools'),
    'CNY' => __('Chinese Yuan', 'freetoolshub-tools'),
    'HKD' => __('Hong Kong Dollar', 'freetoolshub-tools'),
    'MYR' => __('Malaysian Ringgit', 'freetoolshub-tools'),
    'PHP' => __('Philippine Peso', 'freetoolshub-tools'),
    'THB' => __('Thai Baht', 'freetoolshub-tools'),
    'IDR' => __('Indonesian Rupiah', 'freetoolshub-tools'),
    'KRW' => __('South Korean Won', 'freetoolshub-tools'),
    'ZAR' => __('South African Rand', 'freetoolshub-tools'),
    'SEK' => __('Swedish Krona', 'freetoolshub-tools'),
    'NOK' => __('Norwegian Krone', 'freetoolshub-tools'),
    'DKK' => __('Danish Krone', 'freetoolshub-tools'),
    'PLN' => __('Polish Zloty', 'freetoolshub-tools'),
    'MXN' => __('Mexican Peso', 'freetoolshub-tools'),
    'BRL' => __('Brazilian Real', 'freetoolshub-tools'),
    'TRY' => __('Turkish Lira', 'freetoolshub-tools'),
    'PKR' => __('Pakistani Rupee', 'freetoolshub-tools'),
    'BDT' => __('Bangladeshi Taka', 'freetoolshub-tools'),
    'NPR' => __('Nepalese Rupee', 'freetoolshub-tools'),
    'LKR' => __('Sri Lankan Rupee', 'freetoolshub-tools'),
);

/**
 * Render a currency <select>.
 */
$fth_mt_currency_select = function ($name, $selected) use ($fth_id, $fth_mt_currencies) {
?>
    <select id="<?php echo esc_attr($fth_id . '-' . $name); ?>" class="fth-mt-input fth-mt-select" data-mt-<?php echo esc_attr($name); ?>>
        <?php foreach ($fth_mt_currencies as $code => $label) : ?>
            <option value="<?php echo esc_attr($code); ?>" <?php selected($code, $selected); ?>>
                <?php echo esc_html($code . ' – ' . $label); ?>
            </option>
        <?php endforeach; ?>
    </select>
<?php
};

/**
 * Render a number input with an optional unit shown inside it.
 * $unit is 'from' / 'to' (currency code, updated by JS) or a literal string such as '%'.
 */
$fth_mt_number = function ($id, $field, $placeholder, $unit = '') {
?>
    <span class="fth-mt-number">
        <input
            type="number"
            id="<?php echo esc_attr($id); ?>"
            class="fth-mt-input"
            min="0"
            step="any"
            inputmode="decimal"
            placeholder="<?php echo esc_attr($placeholder); ?>"
            data-mt-field="<?php echo esc_attr($field); ?>">
        <?php if ($unit === 'from' || $unit === 'to') : ?>
            <span class="fth-mt-unit" data-mt-code="<?php echo esc_attr($unit); ?>" aria-hidden="true"></span>
        <?php elseif ($unit !== '') : ?>
            <span class="fth-mt-unit" aria-hidden="true"><?php echo esc_html($unit); ?></span>
        <?php endif; ?>
    </span>
<?php
};

/**
 * Render one provider card (inputs + results).
 */
$fth_mt_provider = function ($key, $default_name) use ($fth_id, $fth_mt_number) {
    $pid = $fth_id . '-' . $key;
?>
    <section class="fth-mt-card fth-mt-provider" data-mt-provider="<?php echo esc_attr($key); ?>" aria-labelledby="<?php echo esc_attr($pid . '-title'); ?>">

        <div class="fth-mt-provider-head">
            <h2 class="fth-mt-title" id="<?php echo esc_attr($pid . '-title'); ?>" data-mt-provider-title><?php echo esc_html($default_name); ?></h2>
            <span class="fth-mt-badge" data-mt-badge hidden><?php esc_html_e('Best deal', 'freetoolshub-tools'); ?></span>
        </div>

        <div class="fth-mt-field">
            <label class="fth-mt-label" for="<?php echo esc_attr($pid . '-name'); ?>"><?php esc_html_e('Provider name', 'freetoolshub-tools'); ?></label>
            <input
                type="text"
                id="<?php echo esc_attr($pid . '-name'); ?>"
                class="fth-mt-input"
                maxlength="40"
                placeholder="<?php echo esc_attr($key === 'a' ? __('e.g. My bank', 'freetoolshub-tools') : __('e.g. Online transfer service', 'freetoolshub-tools')); ?>"
                data-mt-name
                data-mt-default-name="<?php echo esc_attr($default_name); ?>">
        </div>

        <div class="fth-mt-field">
            <label class="fth-mt-label" for="<?php echo esc_attr($pid . '-rate'); ?>"><?php esc_html_e('Exchange rate offered', 'freetoolshub-tools'); ?></label>
            <span class="fth-mt-rate-row">
                <span class="fth-mt-rate-prefix">1 <span data-mt-code="from"></span> =</span>
                <?php $fth_mt_number($pid . '-rate', 'rate', '', 'to'); ?>
            </span>
        </div>

        <div class="fth-mt-field-pair">
            <div class="fth-mt-field">
                <label class="fth-mt-label" for="<?php echo esc_attr($pid . '-fixed-fee'); ?>"><?php esc_html_e('Transfer fee', 'freetoolshub-tools'); ?></label>
                <?php $fth_mt_number($pid . '-fixed-fee', 'fixedFee', '0', 'from'); ?>
            </div>
            <div class="fth-mt-field">
                <label class="fth-mt-label" for="<?php echo esc_attr($pid . '-percent-fee'); ?>"><?php esc_html_e('Percentage fee', 'freetoolshub-tools'); ?></label>
                <?php $fth_mt_number($pid . '-percent-fee', 'percentFee', '0', '%'); ?>
            </div>
        </div>

        <div class="fth-mt-field">
            <label class="fth-mt-label" for="<?php echo esc_attr($pid . '-fee-mode'); ?>"><?php esc_html_e('How are fees paid?', 'freetoolshub-tools'); ?></label>
            <select id="<?php echo esc_attr($pid . '-fee-mode'); ?>" class="fth-mt-input fth-mt-select" data-mt-fee-mode>
                <option value="deduct"><?php esc_html_e('Taken from the amount I send', 'freetoolshub-tools'); ?></option>
                <option value="add"><?php esc_html_e('Added on top — I pay extra', 'freetoolshub-tools'); ?></option>
            </select>
        </div>

        <div class="fth-mt-field">
            <label class="fth-mt-label" for="<?php echo esc_attr($pid . '-receive-fee'); ?>">
                <?php esc_html_e('Receiving bank fee', 'freetoolshub-tools'); ?>
                <span class="fth-mt-optional"><?php esc_html_e('(optional)', 'freetoolshub-tools'); ?></span>
            </label>
            <?php $fth_mt_number($pid . '-receive-fee', 'receiveFee', '0', 'to'); ?>
        </div>

        <div class="fth-mt-result" role="status" aria-live="polite">
            <span class="fth-mt-result-label"><?php esc_html_e('Recipient gets', 'freetoolshub-tools'); ?></span>
            <output class="fth-mt-result-value" data-mt-received>—</output>
            <span class="fth-mt-error" data-mt-error></span>

            <dl class="fth-mt-breakdown" data-mt-breakdown hidden>
                <div>
                    <dt><?php esc_html_e('Fees', 'freetoolshub-tools'); ?></dt>
                    <dd data-mt-out="fees"></dd>
                </div>
                <div>
                    <dt><?php esc_html_e('Amount converted', 'freetoolshub-tools'); ?></dt>
                    <dd data-mt-out="converted"></dd>
                </div>
                <div>
                    <dt><?php esc_html_e('You pay in total', 'freetoolshub-tools'); ?></dt>
                    <dd data-mt-out="paid"></dd>
                </div>
                <div data-mt-needs-mid>
                    <dt><?php esc_html_e('Exchange rate markup', 'freetoolshub-tools'); ?></dt>
                    <dd data-mt-out="markup"></dd>
                </div>
                <div class="fth-mt-total-cost" data-mt-needs-mid>
                    <dt><?php esc_html_e('Total cost of transfer', 'freetoolshub-tools'); ?></dt>
                    <dd data-mt-out="cost"></dd>
                </div>
                <div>
                    <dt><?php esc_html_e('Effective rate', 'freetoolshub-tools'); ?></dt>
                    <dd data-mt-out="effective"></dd>
                </div>
            </dl>
        </div>

    </section>
<?php
};

?>

<div class="fth-tool fth-money-transfer" data-fth-money-transfer>

    <!-- Transfer details -->
    <section class="fth-mt-card fth-mt-details" aria-labelledby="<?php echo esc_attr($fth_id . '-details-title'); ?>">

        <h2 class="fth-mt-title" id="<?php echo esc_attr($fth_id . '-details-title'); ?>">
            <?php esc_html_e('Transfer details', 'freetoolshub-tools'); ?>
        </h2>

        <div class="fth-mt-currency-row">

            <div class="fth-mt-field fth-mt-field-amount">
                <label class="fth-mt-label" for="<?php echo esc_attr($fth_id . '-amount'); ?>"><?php esc_html_e('You send', 'freetoolshub-tools'); ?></label>
                <?php $fth_mt_number($fth_id . '-amount', 'amount', '1000', 'from'); ?>
            </div>

            <div class="fth-mt-field">
                <label class="fth-mt-label" for="<?php echo esc_attr($fth_id . '-from'); ?>"><?php esc_html_e('From currency', 'freetoolshub-tools'); ?></label>
                <?php $fth_mt_currency_select('from', 'USD'); ?>
            </div>

            <button type="button" class="fth-mt-swap" data-mt-swap aria-label="<?php esc_attr_e('Swap currencies', 'freetoolshub-tools'); ?>" title="<?php esc_attr_e('Swap currencies', 'freetoolshub-tools'); ?>">
                <span aria-hidden="true">⇄</span>
            </button>

            <div class="fth-mt-field">
                <label class="fth-mt-label" for="<?php echo esc_attr($fth_id . '-to'); ?>"><?php esc_html_e('To currency', 'freetoolshub-tools'); ?></label>
                <?php $fth_mt_currency_select('to', 'INR'); ?>
            </div>

        </div>

        <div class="fth-mt-field fth-mt-mid">
            <label class="fth-mt-label" for="<?php echo esc_attr($fth_id . '-mid'); ?>">
                <?php esc_html_e('Mid-market rate', 'freetoolshub-tools'); ?>
                <span class="fth-mt-optional"><?php esc_html_e('(optional — shows hidden exchange-rate costs)', 'freetoolshub-tools'); ?></span>
            </label>
            <span class="fth-mt-rate-row">
                <span class="fth-mt-rate-prefix">1 <span data-mt-code="from"></span> =</span>
                <?php $fth_mt_number($fth_id . '-mid', 'mid', '', 'to'); ?>
                <button type="button" class="fth-btn fth-btn-secondary fth-mt-fetch" data-mt-fetch>
                    <?php esc_html_e('Get today\'s rate', 'freetoolshub-tools'); ?>
                </button>
            </span>
            <span class="fth-mt-hint" data-mt-rate-status role="status" aria-live="polite"></span>
        </div>

    </section>


    <!-- Providers -->
    <div class="fth-mt-providers">
        <?php
        $fth_mt_provider('a', __('Provider A', 'freetoolshub-tools'));
        $fth_mt_provider('b', __('Provider B', 'freetoolshub-tools'));
        ?>
    </div>


    <!-- Comparison -->
    <div class="fth-mt-compare" data-mt-compare role="status" aria-live="polite" hidden></div>

    <div class="fth-tool-actions">
        <button type="button" class="fth-btn fth-btn-primary" data-mt-copy disabled>
            <?php esc_html_e('Copy Results', 'freetoolshub-tools'); ?>
        </button>
        <button type="button" class="fth-btn fth-btn-secondary" data-mt-clear>
            <?php esc_html_e('Clear', 'freetoolshub-tools'); ?>
        </button>
        <span class="fth-tool-message" data-mt-message role="status" aria-live="polite"></span>
    </div>

    <p class="fth-tool-note">
        <?php esc_html_e('Results update as you type and are calculated in your browser. "Get today\'s rate" loads the latest European Central Bank reference rate from frankfurter.dev (updated once each working day). Providers\' live rates change throughout the day, so check their current quote before you send.', 'freetoolshub-tools'); ?>
    </p>

</div>
