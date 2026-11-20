/**
 * FreeToolsHub — Text Case Converter
 *
 * Runs entirely in the browser; supports multiple instances on one page.
 * Depends on assets/js/fth-tools.js (window.FTH).
 */
(function () {
    'use strict';

    // Letters/numbers with inner apostrophes ("don't"); hyphens split words ("Well-Known").
    var WORD_REGEX = /[\p{L}\p{N}]+(?:['’][\p{L}\p{N}]+)*/gu;

    // Kept lowercase in Title Case unless first, last or after : . ! ?
    var MINOR_WORDS = /^(a|an|and|as|at|but|by|for|in|nor|of|off|on|or|per|so|the|to|up|via|vs|yet)$/;

    // Start of text or line, or after sentence-ending punctuation — skipping
    // opening quotes / brackets — followed by the first letter.
    var SENTENCE_START_REGEX = /(^|[.!?…]\s+|\n\s*)(["'“‘(\[]*)(\p{L})/gu;

    // Standalone pronoun "i" (also in "i'm", "i've", "i'll", "i'd").
    var PRONOUN_I_REGEX = /(^|[^\p{L}\p{N}])i(?![\p{L}\p{N}])/gu;

    var i18n = window.fthTextCase || {
        converted: {
            upper: 'Converted to UPPERCASE.',
            lower: 'Converted to lowercase.',
            title: 'Converted to Title Case.',
            sentence: 'Converted to Sentence case.'
        }
    };

    function capitalize(word) {
        return word.charAt(0).toUpperCase() + word.slice(1);
    }

    function toTitleCase(text) {
        return text.split('\n').map(function (line) {
            var lower = line.toLowerCase();
            var total = (lower.match(WORD_REGEX) || []).length;
            var index = 0;
            var prevEnd = 0;

            return lower.replace(WORD_REGEX, function (word, offset) {
                var isEdge = index === 0 || index === total - 1;
                var afterBreak = /[:.!?…]/.test(lower.slice(prevEnd, offset));

                index++;
                prevEnd = offset + word.length;

                return !isEdge && !afterBreak && MINOR_WORDS.test(word) ? word : capitalize(word);
            });
        }).join('\n');
    }

    function toSentenceCase(text) {
        return text
            .toLowerCase()
            .replace(SENTENCE_START_REGEX, function (match, before, quote, letter) {
                return before + quote + letter.toUpperCase();
            })
            .replace(PRONOUN_I_REGEX, '$1I');
    }

    var converters = {
        upper: function (text) {
            return text.toUpperCase();
        },
        lower: function (text) {
            return text.toLowerCase();
        },
        title: toTitleCase,
        sentence: toSentenceCase
    };

    function init(root) {
        var input = root.querySelector('[data-tc-input]');
        var caseButtons = root.querySelectorAll('[data-case]');
        var lastConverted = null;

        if (!input) {
            return;
        }

        function setActive(activeCase) {
            caseButtons.forEach(function (btn) {
                btn.setAttribute('aria-pressed', btn.getAttribute('data-case') === activeCase ? 'true' : 'false');
            });
        }

        var tool = FTH.bindTextTool(root, input, function (text) {
            caseButtons.forEach(function (btn) {
                btn.disabled = text.length === 0;
            });

            // Typing after a conversion means the text no longer matches the active case.
            if (text !== lastConverted) {
                setActive(null);
            }
        });

        caseButtons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                var caseName = btn.getAttribute('data-case');

                lastConverted = converters[caseName](input.value);

                FTH.setText(input, lastConverted);
                tool.update();
                setActive(caseName);
                tool.showMessage(i18n.converted[caseName]);
            });
        });
    }

    FTH.onReady(function () {
        document.querySelectorAll('[data-fth-text-case]').forEach(init);
    });

    // Exposed for testing / reuse.
    window.FTH.textCase = converters;
})();
