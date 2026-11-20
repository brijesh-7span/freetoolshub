/**
 * FreeToolsHub — Word Counter
 *
 * Runs entirely in the browser; supports multiple instances on one page.
 */
(function () {
    'use strict';

    var WORDS_PER_MINUTE = 200;

    // Letters/numbers, allowing inner apostrophes and hyphens: "don't", "well-known".
    var WORD_REGEX = /[\p{L}\p{N}]+(?:['’\-][\p{L}\p{N}]+)*/gu;

    // A sentence ends with . ! ? … followed by whitespace or the end of the text,
    // so decimals like "3.14" don't split a sentence.
    var SENTENCE_END_REGEX = /[.!?…]+(?=\s|$)/;

    // Counts user-perceived characters ("👋🏽" = 1) where supported.
    var graphemeSegmenter = window.Intl && Intl.Segmenter
        ? new Intl.Segmenter(undefined, { granularity: 'grapheme' })
        : null;
    var HAS_WORD_REGEX = /[\p{L}\p{N}]/u;

    var i18n = window.fthWordCounter || {
        sec: 'sec',
        min: 'min'
    };

    function countCharacters(text) {
        if (graphemeSegmenter) {
            return Array.from(graphemeSegmenter.segment(text)).length;
        }

        // Fallback: code points (handles surrogate pairs, not combined emoji).
        return Array.from(text).length;
    }

    function countWords(text) {
        var matches = text.match(WORD_REGEX);
        return matches ? matches.length : 0;
    }

    function countSentences(text) {
        return text.split(SENTENCE_END_REGEX).filter(function (part) {
            return HAS_WORD_REGEX.test(part);
        }).length;
    }

    function countParagraphs(text) {
        return text.split(/\n/).filter(function (line) {
            return line.trim() !== '';
        }).length;
    }

    function formatReadingTime(words) {
        if (words === 0) {
            return '0 ' + i18n.sec;
        }

        var seconds = Math.ceil((words / WORDS_PER_MINUTE) * 60);

        if (seconds < 60) {
            return seconds + ' ' + i18n.sec;
        }

        var minutes = Math.floor(seconds / 60);
        var rest = seconds % 60;

        return rest ? minutes + ' ' + i18n.min + ' ' + rest + ' ' + i18n.sec : minutes + ' ' + i18n.min;
    }

    function analyze(text) {
        var words = countWords(text);

        return {
            words: words,
            characters: countCharacters(text),
            'characters-no-spaces': countCharacters(text.replace(/\s/g, '')),
            sentences: countSentences(text),
            paragraphs: countParagraphs(text),
            'reading-time': formatReadingTime(words)
        };
    }

    function init(root) {
        var input = root.querySelector('[data-wc-input]');
        var statEls = root.querySelectorAll('[data-stat]');

        if (!input) {
            return;
        }

        FTH.bindTextTool(root, input, function (text) {
            var stats = analyze(text);

            statEls.forEach(function (el) {
                var value = stats[el.getAttribute('data-stat')];
                el.textContent = typeof value === 'number' ? value.toLocaleString() : value;
            });
        });
    }

    FTH.onReady(function () {
        document.querySelectorAll('[data-fth-word-counter]').forEach(init);
    });
})();
