/**
 * FreeToolsHub — Age Calculator
 *
 * Calculates exact age in years, months and days in the browser.
 * Dates are handled as plain calendar dates (UTC) so time zones and
 * daylight-saving changes can never shift the result by a day.
 *
 * Supports multiple instances on one page. Depends on assets/js/fth-tools.js (window.FTH).
 */
(function () {
    'use strict';

    var DAY_MS = 24 * 60 * 60 * 1000;

    var i18n = window.fthAgeCalculator || {
        year: ['%s year', '%s years'],
        month: ['%s month', '%s months'],
        day: ['%s day', '%s days'],
        week: ['%s week', '%s weeks'],
        and: 'and',
        summaryPresent: 'You are %s old.',
        summaryPast: 'On %2$s, you were %1$s old.',
        summaryFuture: 'On %2$s, you will be %1$s old.',
        birthdayToday: 'Happy birthday! 🎉 You turn %s today.',
        birthdayOnDate: 'That\'s your birthday 🎉 (turning %s)',
        nextBirthday: 'In %1$s — %2$s (turning %3$s)',
        invalidDate: 'Please enter a valid date.',
        futureBirth: 'The date of birth must be on or before the "age on" date.'
    };

    /* ---------- Date helpers (plain calendar dates) ---------- */

    function parseDate(value) {
        var match = /^(\d{4,})-(\d{2})-(\d{2})$/.exec(value || '');

        if (!match) {
            return null;
        }

        var date = { y: +match[1], m: +match[2], d: +match[3] };

        // Reject impossible dates like 2023-02-30.
        if (date.m < 1 || date.m > 12 || date.d < 1 || date.d > daysInMonth(date.y, date.m)) {
            return null;
        }

        return date;
    }

    function toInputValue(date) {
        return String(date.y).padStart(4, '0') + '-' + String(date.m).padStart(2, '0') + '-' + String(date.d).padStart(2, '0');
    }

    function today() {
        var now = new Date();

        return { y: now.getFullYear(), m: now.getMonth() + 1, d: now.getDate() };
    }

    function daysInMonth(year, month) {
        return new Date(Date.UTC(year, month, 0)).getUTCDate();
    }

    function dayNumber(date) {
        var utc = new Date(0);

        utc.setUTCFullYear(date.y, date.m - 1, date.d); // setUTCFullYear handles years < 100 correctly

        return Math.round(utc.getTime() / DAY_MS);
    }

    function compare(a, b) {
        return dayNumber(a) - dayNumber(b);
    }

    /**
     * Add whole months, clamping to the end of shorter months
     * (Jan 31 + 1 month = Feb 28/29; a Feb 29 birthday falls on Feb 28 in common years).
     */
    function addMonths(date, months) {
        var total = date.y * 12 + (date.m - 1) + months;
        var year = Math.floor(total / 12);
        var month = (total % 12) + 1;

        return { y: year, m: month, d: Math.min(date.d, daysInMonth(year, month)) };
    }

    /**
     * Exact age between two dates as { years, months, days, totalMonths, totalDays }.
     */
    function calculateAge(birth, target) {
        var totalMonths = (target.y - birth.y) * 12 + (target.m - birth.m);

        if (compare(addMonths(birth, totalMonths), target) > 0) {
            totalMonths--;
        }

        return {
            years: Math.floor(totalMonths / 12),
            months: totalMonths % 12,
            days: compare(target, addMonths(birth, totalMonths)),
            totalMonths: totalMonths,
            totalDays: compare(target, birth)
        };
    }

    /**
     * Next birthday on or after the target date.
     */
    function nextBirthday(birth, target) {
        var age = calculateAge(birth, target);
        var next = addMonths(birth, age.years * 12);

        if (compare(next, target) < 0 || (compare(next, target) === 0 && age.years === 0)) {
            next = addMonths(birth, (age.years + 1) * 12);
        }

        return {
            date: next,
            turning: next.y - birth.y,
            daysAway: compare(next, target)
        };
    }

    /* ---------- Formatting ---------- */

    function plural(forms, count) {
        return forms[count === 1 ? 0 : 1].replace('%s', count.toLocaleString());
    }

    function formatDate(date, options) {
        var utc = new Date(0);

        utc.setUTCFullYear(date.y, date.m - 1, date.d);

        return new Intl.DateTimeFormat(undefined, Object.assign({ timeZone: 'UTC' }, options)).format(utc);
    }

    function formatAge(age) {
        return plural(i18n.year, age.years) + ', ' + plural(i18n.month, age.months) + ' ' + i18n.and + ' ' + plural(i18n.day, age.days);
    }

    function fill(template, values) {
        return values.reduce(function (text, value, i) {
            return text.replace('%' + (i + 1) + '$s', value);
        }, template);
    }

    /* ---------- UI ---------- */

    function init(root) {
        var dobInput = root.querySelector('[data-age-dob]');
        var targetInput = root.querySelector('[data-age-target]');
        var todayButton = root.querySelector('[data-age-today]');
        var resetButton = root.querySelector('[data-age-reset]');
        var placeholder = root.querySelector('[data-age-placeholder]');
        var results = root.querySelector('[data-age-results]');
        var message = root.querySelector('[data-age-message]');
        var yearsEl = root.querySelector('[data-age-years]');
        var monthsEl = root.querySelector('[data-age-months]');
        var daysEl = root.querySelector('[data-age-days]');
        var summaryEl = root.querySelector('[data-age-summary]');
        var totalMonthsEl = root.querySelector('[data-age-total-months]');
        var totalWeeksEl = root.querySelector('[data-age-total-weeks]');
        var totalDaysEl = root.querySelector('[data-age-total-days]');
        var nextBirthdayEl = root.querySelector('[data-age-next-birthday]');
        var bornOnEl = root.querySelector('[data-age-born-on]');

        function setTargetToday() {
            targetInput.value = toInputValue(today());
        }

        function showError(text) {
            message.textContent = text;
            results.hidden = true;
            placeholder.hidden = true;
        }

        function update() {
            var birth = parseDate(dobInput.value);
            var target = parseDate(targetInput.value);

            message.textContent = '';
            resetButton.disabled = !dobInput.value && targetInput.value === toInputValue(today());
            todayButton.disabled = targetInput.value === toInputValue(today());

            if (!dobInput.value) {
                results.hidden = true;
                placeholder.hidden = false;
                return;
            }

            // An empty "age on" date means today.
            if (!targetInput.value) {
                target = today();
            }

            if (!birth || !target) {
                showError(i18n.invalidDate);
                return;
            }

            if (compare(birth, target) > 0) {
                showError(i18n.futureBirth);
                return;
            }

            var age = calculateAge(birth, target);
            var next = nextBirthday(birth, target);
            var ageText = formatAge(age);
            var targetVsToday = compare(target, today());

            yearsEl.textContent = age.years.toLocaleString();
            monthsEl.textContent = age.months;
            daysEl.textContent = age.days;

            if (targetVsToday === 0) {
                summaryEl.textContent = i18n.summaryPresent.replace('%s', ageText);
            } else {
                summaryEl.textContent = fill(
                    targetVsToday < 0 ? i18n.summaryPast : i18n.summaryFuture,
                    [ageText, formatDate(target, { dateStyle: 'long' })]
                );
            }

            totalMonthsEl.textContent = plural(i18n.month, age.totalMonths) +
                (age.days ? ' ' + i18n.and + ' ' + plural(i18n.day, age.days) : '');
            totalWeeksEl.textContent = plural(i18n.week, Math.floor(age.totalDays / 7)) +
                (age.totalDays % 7 ? ' ' + i18n.and + ' ' + plural(i18n.day, age.totalDays % 7) : '');
            totalDaysEl.textContent = plural(i18n.day, age.totalDays);

            if (next.daysAway === 0) {
                nextBirthdayEl.textContent = (targetVsToday === 0 ? i18n.birthdayToday : i18n.birthdayOnDate)
                    .replace('%s', next.turning);
            } else {
                nextBirthdayEl.textContent = fill(i18n.nextBirthday, [
                    plural(i18n.day, next.daysAway),
                    formatDate(next.date, { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }),
                    next.turning
                ]);
            }

            bornOnEl.textContent = formatDate(birth, { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });

            placeholder.hidden = true;
            results.hidden = false;
        }

        // Birth dates can't be in the future.
        dobInput.max = toInputValue(today());

        if (!targetInput.value) {
            setTargetToday();
        }

        dobInput.addEventListener('input', update);
        dobInput.addEventListener('change', update);
        targetInput.addEventListener('input', update);
        targetInput.addEventListener('change', update);

        todayButton.addEventListener('click', function () {
            setTargetToday();
            update();
            targetInput.focus();
        });

        resetButton.addEventListener('click', function () {
            dobInput.value = '';
            setTargetToday();
            update();
            dobInput.focus();
        });

        update();
    }

    FTH.onReady(function () {
        document.querySelectorAll('[data-fth-age-calculator]').forEach(init);
    });

    // Exposed for testing / reuse.
    FTH.age = { calculateAge: calculateAge, nextBirthday: nextBirthday, parseDate: parseDate };
})();
