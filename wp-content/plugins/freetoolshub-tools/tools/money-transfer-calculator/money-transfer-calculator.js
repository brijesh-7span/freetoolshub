/**
 * FreeToolsHub — International Money Transfer Cost Calculator
 *
 * Works out what the recipient actually receives from each of two providers after
 * fees and the exchange rate, and how much of that rate is hidden markup compared
 * with the mid-market rate. All math runs in the browser; the optional "Get today's
 * rate" button is the only network request (ECB reference rates via frankfurter.dev).
 * Supports multiple instances on one page. Depends on assets/js/fth-tools.js (window.FTH).
 */
(function () {
    'use strict';

    var RATE_API = 'https://api.frankfurter.dev/v1/latest';
    var FETCH_TIMEOUT = 8000;

    // Currencies with an ECB reference rate (available from frankfurter.dev).
    var LIVE = ['AUD', 'BRL', 'CAD', 'CHF', 'CNY', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'IDR', 'ILS', 'INR', 'ISK',
        'JPY', 'KRW', 'MXN', 'MYR', 'NOK', 'NZD', 'PHP', 'PLN', 'RON', 'SEK', 'SGD', 'THB', 'TRY', 'USD', 'ZAR'];

    // Currencies fixed to another one at an official rate: 1 base = rate units.
    var PEGS = {
        AED: { base: 'USD', rate: 3.6725 },
        SAR: { base: 'USD', rate: 3.75 },
        QAR: { base: 'USD', rate: 3.64 },
        NPR: { base: 'INR', rate: 1.6 }
    };

    var i18n = window.fthMoneyTransfer || {
        amountInvalid: 'Enter the amount you are sending.',
        rateInvalid: 'Enter the exchange rate this provider offers.',
        percentInvalid: 'The percentage fee must be between 0 and 100.',
        feesTooHigh: 'The fees are more than the amount being sent.',
        nothingLeft: 'The receiving fee is more than the converted amount.',
        betterThanMid: '%s better than mid-market',
        best: '%1$s is the better deal: the recipient gets %2$s more for every %3$s you pay.',
        tie: 'Both providers give the recipient the same amount.',
        fetching: 'Getting today\'s rate…',
        fetched: 'European Central Bank reference rate for %s.',
        pegged: 'Official fixed rate.',
        fetchFailed: 'Couldn\'t load today\'s rate. Check your connection, or type the rate in yourself.',
        noLiveRate: 'A live rate isn\'t available for %s. Type the rate in yourself — you can find it on Google or XE.com.',
        currencyChanged: 'Currencies changed, so the rates were cleared. Enter the rates for %s.',
        copied: 'Results copied!',
        copyFailed: 'Could not copy — please copy manually.',
        cleared: 'Cleared.',
        summaryPaid: 'You pay',
        summaryGets: 'Recipient gets',
        summaryCost: 'Total cost'
    };

    var rateCache = {};

    function sprintf(template) {
        var args = Array.prototype.slice.call(arguments, 1);
        var next = 0;

        return template.replace(/%(?:(\d+)\$)?s/g, function (match, position) {
            return position ? args[position - 1] : args[next++];
        });
    }

    function formatMoney(value, currency) {
        try {
            return new Intl.NumberFormat(undefined, { style: 'currency', currency: currency }).format(value);
        } catch (e) {
            return value.toFixed(2) + ' ' + currency;
        }
    }

    function formatRate(value) {
        return Number(value.toPrecision(10)).toLocaleString(undefined, {
            maximumFractionDigits: value < 1 ? 6 : 4
        });
    }

    function formatPercent(value) {
        return Number(value.toFixed(2)).toLocaleString(undefined, { maximumFractionDigits: 2 }) + '%';
    }


    /* ----------------------------------------------------------------
     * Calculation
     * ------------------------------------------------------------- */

    /**
     * @param {Object} input amount, rate, fixedFee, percentFee, feeMode ('deduct'|'add'),
     *                       receiveFee, mid (optional, 0 or NaN when unknown)
     * @return {Object} { error } or the breakdown in the sending (from) and receiving (to) currencies.
     */
    function calculate(input) {
        var amount = input.amount;
        var rate = input.rate;
        var fixedFee = input.fixedFee || 0;
        var percentFee = input.percentFee || 0;
        var receiveFee = input.receiveFee || 0;
        var mid = input.mid > 0 ? input.mid : 0;

        if (!(amount > 0)) {
            return { error: 'amountInvalid' };
        }

        if (!(rate > 0)) {
            return { error: 'rateInvalid' };
        }

        if (percentFee < 0 || percentFee > 100) {
            return { error: 'percentInvalid' };
        }

        var fees = Math.max(fixedFee, 0) + amount * percentFee / 100;
        var converted = input.feeMode === 'add' ? amount : amount - fees;
        var paid = input.feeMode === 'add' ? amount + fees : amount;

        if (converted <= 0) {
            return { error: 'feesTooHigh' };
        }

        var received = converted * rate - Math.max(receiveFee, 0);

        if (received <= 0) {
            return { error: 'nothingLeft' };
        }

        var result = {
            fees: fees,
            converted: converted,
            paid: paid,
            received: received,
            effectiveRate: received / paid
        };

        if (mid) {
            // Cost of the rate being worse than mid-market, in the sending currency.
            result.markupPercent = (mid - rate) / mid * 100;
            result.markupCost = converted * (mid - rate) / mid;
            // Everything lost compared with converting the full payment at mid-market.
            result.totalCost = paid - received / mid;
            result.totalCostPercent = result.totalCost / paid * 100;
        }

        return result;
    }

    /**
     * Compare two results. Better deal = more received per unit paid.
     * @return {Object|null} { winner: 'a'|'b'|null, extra } where extra is the additional amount
     *                       received for every `amount` paid.
     */
    function compare(a, b, amount) {
        if (!a || !b || a.error || b.error) {
            return null;
        }

        var diff = (b.effectiveRate - a.effectiveRate) * amount;

        if (Math.abs(diff) < 0.005) {
            return { winner: null, extra: 0 };
        }

        return { winner: diff > 0 ? 'b' : 'a', extra: Math.abs(diff) };
    }


    /* ----------------------------------------------------------------
     * Reference rates
     * ------------------------------------------------------------- */

    function resolveCurrency(code) {
        if (PEGS[code]) {
            return { base: PEGS[code].base, factor: PEGS[code].rate, pegged: true };
        }

        return LIVE.indexOf(code) !== -1 ? { base: code, factor: 1, pegged: false } : null;
    }

    /**
     * Resolve to { rate, date, pegged } for 1 FROM in TO.
     */
    function getReferenceRate(from, to) {
        var a = resolveCurrency(from);
        var b = resolveCurrency(to);

        if (!a || !b) {
            return Promise.reject({ unsupported: !a ? from : to });
        }

        var ratio = b.factor / a.factor;

        if (a.base === b.base) {
            return Promise.resolve({ rate: ratio, date: '', pegged: true });
        }

        var key = a.base + '-' + b.base;

        if (!rateCache[key]) {
            var controller = window.AbortController ? new AbortController() : null;
            var timer = controller && setTimeout(function () {
                controller.abort();
            }, FETCH_TIMEOUT);

            rateCache[key] = fetch(RATE_API + '?base=' + a.base + '&symbols=' + b.base, controller ? { signal: controller.signal } : {})
                .then(function (response) {
                    clearTimeout(timer);

                    if (!response.ok) {
                        throw new Error('HTTP ' + response.status);
                    }

                    return response.json();
                })
                .then(function (data) {
                    var value = data && data.rates && data.rates[b.base];

                    if (!(value > 0)) {
                        throw new Error('Missing rate');
                    }

                    return { rate: value, date: data.date || '' };
                })
                .catch(function (error) {
                    delete rateCache[key]; // let the visitor retry
                    throw error;
                });
        }

        return rateCache[key].then(function (data) {
            return { rate: data.rate * ratio, date: data.date, pegged: a.pegged || b.pegged };
        });
    }


    /* ----------------------------------------------------------------
     * UI
     * ------------------------------------------------------------- */

    function init(root) {
        var amountInput = root.querySelector('.fth-mt-details [data-mt-field="amount"]');
        var midInput = root.querySelector('.fth-mt-details [data-mt-field="mid"]');
        var fromSelect = root.querySelector('[data-mt-from]');
        var toSelect = root.querySelector('[data-mt-to]');
        var fetchButton = root.querySelector('[data-mt-fetch]');
        var rateStatus = root.querySelector('[data-mt-rate-status]');
        var compareBox = root.querySelector('[data-mt-compare]');
        var copyButton = root.querySelector('[data-mt-copy]');
        var clearButton = root.querySelector('[data-mt-clear]');
        var message = root.querySelector('[data-mt-message]');
        var messageTimer;
        var fetchToken = 0;
        var lastResults = {};

        var providers = Array.prototype.map.call(root.querySelectorAll('[data-mt-provider]'), function (card) {
            var fields = {};

            card.querySelectorAll('[data-mt-field]').forEach(function (input) {
                fields[input.getAttribute('data-mt-field')] = input;
            });

            return {
                key: card.getAttribute('data-mt-provider'),
                card: card,
                fields: fields,
                name: card.querySelector('[data-mt-name]'),
                title: card.querySelector('[data-mt-provider-title]'),
                feeMode: card.querySelector('[data-mt-fee-mode]'),
                badge: card.querySelector('[data-mt-badge]'),
                received: card.querySelector('[data-mt-received]'),
                error: card.querySelector('[data-mt-error]'),
                breakdown: card.querySelector('[data-mt-breakdown]'),
                outputs: card.querySelectorAll('[data-mt-out]'),
                needsMid: card.querySelectorAll('[data-mt-needs-mid]')
            };
        });

        function number(input) {
            return input.value === '' ? NaN : parseFloat(input.value);
        }

        function showMessage(text, isError) {
            message.textContent = text;
            message.classList.toggle('is-error', !!isError);
            clearTimeout(messageTimer);
            messageTimer = setTimeout(function () {
                message.textContent = '';
            }, 3000);
        }

        function providerName(provider) {
            return provider.name.value.trim() || provider.name.getAttribute('data-mt-default-name');
        }

        function updateCodes() {
            root.querySelectorAll('[data-mt-code]').forEach(function (el) {
                el.textContent = el.getAttribute('data-mt-code') === 'from' ? fromSelect.value : toSelect.value;
            });

            // Suggest the mid-market rate as each provider's rate.
            providers.forEach(function (provider) {
                provider.fields.rate.placeholder = midInput.value || '';
            });
        }

        function setOutput(provider, name, text) {
            provider.outputs.forEach(function (el) {
                if (el.getAttribute('data-mt-out') === name) {
                    el.textContent = text;
                }
            });
        }

        function renderProvider(provider, amount, mid) {
            var from = fromSelect.value;
            var to = toSelect.value;
            var rate = number(provider.fields.rate);

            provider.title.textContent = providerName(provider);
            provider.card.classList.remove('is-error');
            provider.error.textContent = '';

            // Nothing to show until the key fields are filled.
            if (!(amount > 0) || provider.fields.rate.value === '') {
                provider.received.textContent = '—';
                provider.breakdown.hidden = true;
                return null;
            }

            var result = calculate({
                amount: amount,
                rate: rate,
                fixedFee: number(provider.fields.fixedFee),
                percentFee: number(provider.fields.percentFee),
                receiveFee: number(provider.fields.receiveFee),
                feeMode: provider.feeMode.value,
                mid: mid
            });

            if (result.error) {
                provider.card.classList.add('is-error');
                provider.received.textContent = '—';
                provider.error.textContent = i18n[result.error];
                provider.breakdown.hidden = true;
                return result;
            }

            provider.received.textContent = formatMoney(result.received, to);
            provider.breakdown.hidden = false;

            setOutput(provider, 'fees', formatMoney(result.fees, from));
            setOutput(provider, 'converted', formatMoney(result.converted, from));
            setOutput(provider, 'paid', formatMoney(result.paid, from));
            setOutput(provider, 'effective', '1 ' + from + ' = ' + formatRate(result.effectiveRate) + ' ' + to);

            provider.needsMid.forEach(function (row) {
                row.hidden = !mid;
            });

            if (mid) {
                setOutput(provider, 'markup', result.markupPercent < 0
                    ? sprintf(i18n.betterThanMid, formatPercent(-result.markupPercent))
                    : formatPercent(result.markupPercent) + ' (' + formatMoney(result.markupCost, from) + ')');
                setOutput(provider, 'cost', formatMoney(result.totalCost, from) + ' (' + formatPercent(result.totalCostPercent) + ')');
            }

            return result;
        }

        function update() {
            var amount = number(amountInput);
            var mid = number(midInput);

            updateCodes();

            lastResults = {};

            providers.forEach(function (provider) {
                lastResults[provider.key] = renderProvider(provider, amount, mid > 0 ? mid : 0);
                provider.card.classList.remove('is-best');
                provider.badge.hidden = true;
            });

            var outcome = compare(lastResults.a, lastResults.b, amount);

            if (outcome) {
                compareBox.hidden = false;

                if (outcome.winner) {
                    var winner = providers.filter(function (p) {
                        return p.key === outcome.winner;
                    })[0];

                    winner.card.classList.add('is-best');
                    winner.badge.hidden = false;
                    compareBox.textContent = sprintf(i18n.best, providerName(winner),
                        formatMoney(outcome.extra, toSelect.value), formatMoney(amount, fromSelect.value));
                } else {
                    compareBox.textContent = i18n.tie;
                }
            } else {
                compareBox.hidden = true;
                compareBox.textContent = '';
            }

            copyButton.disabled = !providers.some(function (provider) {
                var result = lastResults[provider.key];
                return result && !result.error;
            });
        }

        function summaryText() {
            var from = fromSelect.value;
            var to = toSelect.value;
            var lines = [];

            providers.forEach(function (provider) {
                var result = lastResults[provider.key];

                if (!result || result.error) {
                    return;
                }

                lines.push(providerName(provider) + ' (1 ' + from + ' = ' + formatRate(number(provider.fields.rate)) + ' ' + to + ')');
                lines.push('  ' + i18n.summaryPaid + ': ' + formatMoney(result.paid, from));
                lines.push('  ' + i18n.summaryGets + ': ' + formatMoney(result.received, to));

                if (result.totalCost !== undefined) {
                    lines.push('  ' + i18n.summaryCost + ': ' + formatMoney(result.totalCost, from) + ' (' + formatPercent(result.totalCostPercent) + ')');
                }
            });

            if (!compareBox.hidden) {
                lines.push('', compareBox.textContent);
            }

            return lines.join('\n');
        }

        function clearRates() {
            midInput.value = '';
            providers.forEach(function (provider) {
                provider.fields.rate.value = '';
            });
        }

        // Reference rate

        fetchButton.addEventListener('click', function () {
            var token = ++fetchToken;
            var from = fromSelect.value;
            var to = toSelect.value;

            fetchButton.disabled = true;
            rateStatus.classList.remove('is-error');
            rateStatus.textContent = i18n.fetching;

            getReferenceRate(from, to).then(function (data) {
                if (token !== fetchToken) {
                    return;
                }

                midInput.value = Number(data.rate.toPrecision(8));
                rateStatus.textContent = data.date
                    ? sprintf(i18n.fetched, new Date(data.date + 'T12:00:00').toLocaleDateString(undefined, { day: 'numeric', month: 'short', year: 'numeric' }))
                    : i18n.pegged;
                update();
            }, function (error) {
                if (token !== fetchToken) {
                    return;
                }

                rateStatus.classList.add('is-error');
                rateStatus.textContent = error && error.unsupported
                    ? sprintf(i18n.noLiveRate, error.unsupported)
                    : i18n.fetchFailed;
            }).then(function () {
                fetchButton.disabled = false;
            });
        });

        // Currencies

        function onCurrencyChange() {
            fetchToken++; // ignore a rate still loading for the old pair
            fetchButton.disabled = false;
            clearRates();
            rateStatus.classList.remove('is-error');
            rateStatus.textContent = sprintf(i18n.currencyChanged, fromSelect.value + ' → ' + toSelect.value);
            update();
        }

        fromSelect.addEventListener('change', onCurrencyChange);
        toSelect.addEventListener('change', onCurrencyChange);

        root.querySelector('[data-mt-swap]').addEventListener('click', function () {
            var from = fromSelect.value;

            fromSelect.value = toSelect.value;
            toSelect.value = from;
            fetchToken++;
            fetchButton.disabled = false;
            rateStatus.textContent = '';

            // A rate the other way round is simply its inverse.
            [midInput].concat(providers.map(function (p) {
                return p.fields.rate;
            })).forEach(function (input) {
                var value = number(input);
                input.value = value > 0 ? Number((1 / value).toPrecision(8)) : '';
            });

            update();
        });

        // Inputs

        root.addEventListener('input', update);
        root.addEventListener('change', function (event) {
            if (event.target.matches('[data-mt-fee-mode]')) {
                update();
            }
        });

        copyButton.addEventListener('click', function () {
            FTH.copyText(summaryText()).then(function () {
                showMessage(i18n.copied);
            }, function () {
                showMessage(i18n.copyFailed, true);
            });
        });

        clearButton.addEventListener('click', function () {
            fetchToken++;
            fetchButton.disabled = false;
            amountInput.value = '';
            clearRates();
            rateStatus.textContent = '';

            providers.forEach(function (provider) {
                provider.name.value = '';
                provider.feeMode.value = 'deduct';
                ['fixedFee', 'percentFee', 'receiveFee'].forEach(function (name) {
                    provider.fields[name].value = '';
                });
            });

            update();
            amountInput.focus();
            showMessage(i18n.cleared);
        });

        // Browsers may restore form values on back/forward navigation.
        update();
    }

    FTH.onReady(function () {
        document.querySelectorAll('[data-fth-money-transfer]').forEach(init);
    });

    // Exposed for testing / reuse.
    FTH.moneyTransfer = {
        calculate: calculate,
        compare: compare,
        getReferenceRate: getReferenceRate
    };
})();
