/**
 * FreeToolsHub — Percentage Calculator
 *
 * Three calculators that update as the visitor types; all math runs in the browser.
 * Supports multiple instances on one page. Depends on assets/js/fth-tools.js (window.FTH).
 */
(function () {
    'use strict';

    var MAX_DECIMALS = 6;

    var i18n = window.fthPercentageCalculator || {
        increase: 'increase',
        decrease: 'decrease',
        noChange: 'No change',
        divideByZero: 'The total can\'t be 0 — you can\'t divide by zero.',
        changeFromZero: 'Percentage change from 0 is undefined. Enter a non-zero original value.',
        tooLarge: 'The result is too large to calculate.',
        copied: 'Result copied!',
        copyFailed: 'Could not copy — please copy manually.',
        cleared: 'Cleared.'
    };

    /**
     * Format a number for display: strips floating-point noise (0.1 + 0.2),
     * up to 6 decimals, with the visitor's locale separators.
     */
    function formatNumber(value) {
        var clean = Number(value.toPrecision(12));

        if (clean === 0) {
            clean = 0; // avoid "-0"
        }

        return clean.toLocaleString(undefined, { maximumFractionDigits: MAX_DECIMALS });
    }

    /**
     * Each calculator takes its numeric inputs and returns
     * { result, formula } or { error }.
     */
    var calculators = {

        // What is P% of N?
        of: function (f) {
            var percent = f['of-percent'];
            var number = f['of-number'];
            var result = (number * percent) / 100;

            if (!isFinite(result)) {
                return { error: i18n.tooLarge };
            }

            return {
                result: formatNumber(result),
                formula: formatNumber(percent) + '% × ' + formatNumber(number) + ' = ' + formatNumber(result)
            };
        },

        // PART is what % of TOTAL?
        what: function (f) {
            var part = f['what-part'];
            var total = f['what-total'];

            if (total === 0) {
                return { error: i18n.divideByZero };
            }

            var result = (part / total) * 100;

            if (!isFinite(result)) {
                return { error: i18n.tooLarge };
            }

            return {
                result: formatNumber(result) + '%',
                formula: formatNumber(part) + ' ÷ ' + formatNumber(total) + ' × 100 = ' + formatNumber(result) + '%'
            };
        },

        // Percentage change from FROM to TO (relative to |FROM| so negatives work as expected).
        change: function (f) {
            var from = f['change-from'];
            var to = f['change-to'];

            if (from === 0) {
                return { error: i18n.changeFromZero };
            }

            var change = ((to - from) / Math.abs(from)) * 100;

            if (!isFinite(change)) {
                return { error: i18n.tooLarge };
            }

            var rounded = Number(change.toPrecision(12));
            var result = rounded === 0
                ? i18n.noChange
                : formatNumber(Math.abs(rounded)) + '% ' + (rounded > 0 ? i18n.increase : i18n.decrease);

            return {
                result: result,
                trend: rounded > 0 ? 'up' : rounded < 0 ? 'down' : '',
                formula: '(' + formatNumber(to) + ' − ' + formatNumber(from) + ') ÷ ' + formatNumber(Math.abs(from)) +
                    ' × 100 = ' + (rounded > 0 ? '+' : '') + formatNumber(rounded) + '%'
            };
        }
    };

    function initCard(card) {
        var calc = calculators[card.getAttribute('data-pc-calc')];
        var inputs = card.querySelectorAll('[data-pc-field]');
        var resultEl = card.querySelector('[data-pc-result]');
        var formulaEl = card.querySelector('[data-pc-formula]');
        var copyButton = card.querySelector('[data-pc-copy]');
        var clearButton = card.querySelector('[data-pc-clear]');
        var message = card.querySelector('[data-pc-message]');
        var messageTimer;

        function showMessage(text) {
            message.textContent = text;
            clearTimeout(messageTimer);
            messageTimer = setTimeout(function () {
                message.textContent = '';
            }, 2500);
        }

        function update() {
            var values = {};
            var complete = true;
            var anyFilled = false;

            inputs.forEach(function (input) {
                // "badInput" catches half-typed numbers like "1e" that report an empty value.
                if (input.value !== '' || input.validity.badInput) {
                    anyFilled = true;
                }

                if (input.value === '') {
                    complete = false;
                }

                values[input.getAttribute('data-pc-field')] = parseFloat(input.value);
            });

            clearButton.disabled = !anyFilled;
            card.classList.remove('is-error', 'is-up', 'is-down');

            if (!complete) {
                resultEl.textContent = '—';
                formulaEl.textContent = '';
                copyButton.disabled = true;
                return;
            }

            var output = calc(values);

            if (output.error) {
                card.classList.add('is-error');
                resultEl.textContent = '—';
                formulaEl.textContent = output.error;
                copyButton.disabled = true;
                return;
            }

            if (output.trend) {
                card.classList.add(output.trend === 'up' ? 'is-up' : 'is-down');
            }

            resultEl.textContent = output.result;
            formulaEl.textContent = output.formula;
            copyButton.disabled = false;
        }

        inputs.forEach(function (input) {
            input.addEventListener('input', update);
        });

        copyButton.addEventListener('click', function () {
            FTH.copyText(resultEl.textContent).then(function () {
                showMessage(i18n.copied);
            }, function () {
                showMessage(i18n.copyFailed);
            });
        });

        clearButton.addEventListener('click', function () {
            inputs.forEach(function (input) {
                input.value = '';
            });

            update();
            inputs[0].focus();
            showMessage(i18n.cleared);
        });

        // Browsers may restore form values on back/forward navigation.
        update();
    }

    FTH.onReady(function () {
        document.querySelectorAll('[data-fth-percentage-calculator] [data-pc-calc]').forEach(initCard);
    });

    // Exposed for testing / reuse.
    FTH.percentage = calculators;
})();
