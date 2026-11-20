/**
 * FreeToolsHub — JSON Formatter
 *
 * Validates, formats and minifies JSON in the browser; nothing is uploaded.
 *
 * Uses a small built-in parser instead of JSON.parse/JSON.stringify so that:
 * - numbers and strings are kept exactly as written (no precision loss on big IDs
 *   like 12345678901234567890, escapes like é stay as-is),
 * - errors report the same message and exact line/column in every browser.
 *
 * Supports multiple instances on one page. Depends on assets/js/fth-tools.js (window.FTH).
 */
(function () {
    'use strict';

    var VALIDATE_DELAY = 300;

    var i18n = window.fthJsonFormatter || {
        valid: 'Valid JSON',
        invalid: 'Invalid JSON',
        empty: 'Paste or type JSON to validate it.',
        location: 'Line %1$s, column %2$s',
        formatted: 'JSON formatted.',
        minified: 'JSON minified.',
        tooDeep: 'This JSON is nested too deeply to process.',
        types: { object: 'Object', array: 'Array', string: 'String', number: 'Number', literal: 'Value' },
        items: '%s items',
        keys: '%s keys',
        errors: {
            expectedValue: 'Expected a value (object, array, string, number, true, false or null).',
            expectedKey: 'Expected a property name in double quotes.',
            expectedColon: 'Expected \':\' after the property name.',
            expectedCommaOrBrace: 'Expected \',\' or \'}\' after the property value.',
            expectedCommaOrBracket: 'Expected \',\' or \']\' after the array item.',
            trailingComma: 'Trailing commas are not allowed in JSON.',
            singleQuotes: 'Strings must use double quotes ("), not single quotes (\').',
            comment: 'Comments are not allowed in JSON.',
            unterminatedString: 'Unterminated string — missing closing double quote.',
            controlChar: 'Line breaks and tabs inside strings must be escaped (\\n, \\t).',
            invalidEscape: 'Invalid escape sequence in string.',
            invalidNumber: 'Invalid number.',
            unexpectedToken: 'Unexpected text — did you mean true, false or null?',
            extraData: 'Unexpected content after the end of the JSON.'
        }
    };

    var NUMBER_REGEX = /-?(?:0|[1-9]\d*)(?:\.\d+)?(?:[eE][+-]?\d+)?/y;
    var LITERAL_REGEX = /true|false|null/y;
    var WHITESPACE_REGEX = /[ \t\n\r]*/y;

    function ParseError(key, pos) {
        this.key = key;
        this.pos = pos;
    }

    /**
     * Parse JSON text into a tree that keeps the original text of every string and number.
     * Nodes: { type: 'object', entries: [[keyRaw, node]] } | { type: 'array', items: [node] }
     *        | { type: 'string'|'number'|'literal', raw: '...' }
     * Throws ParseError on invalid input.
     */
    function parse(text) {
        var pos = 0;

        function skipWhitespace() {
            WHITESPACE_REGEX.lastIndex = pos;
            WHITESPACE_REGEX.exec(text);
            pos = WHITESPACE_REGEX.lastIndex;
        }

        // Explain common mistakes instead of a generic "unexpected character".
        function fail(key) {
            var ch = text[pos];

            if (ch === '\'') {
                key = 'singleQuotes';
            } else if (ch === '/' && (text[pos + 1] === '/' || text[pos + 1] === '*')) {
                key = 'comment';
            }

            throw new ParseError(key, pos);
        }

        function parseString() {
            var start = pos;

            pos++; // opening quote

            while (pos < text.length) {
                var ch = text[pos];

                if (ch === '"') {
                    pos++;
                    return { type: 'string', raw: text.slice(start, pos) };
                }

                if (ch === '\\') {
                    var next = text[pos + 1];

                    if (next === 'u') {
                        if (!/^[0-9a-fA-F]{4}$/.test(text.substr(pos + 2, 4))) {
                            throw new ParseError('invalidEscape', pos);
                        }
                        pos += 6;
                    } else if (next !== undefined && '"\\/bfnrt'.indexOf(next) !== -1) {
                        pos += 2;
                    } else {
                        throw new ParseError('invalidEscape', pos);
                    }
                    continue;
                }

                if (ch < ' ') {
                    throw new ParseError(ch === '\n' || ch === '\r' ? 'unterminatedString' : 'controlChar', pos);
                }

                pos++;
            }

            throw new ParseError('unterminatedString', start);
        }

        function parseValue() {
            skipWhitespace();

            var ch = text[pos];

            if (ch === '{') {
                return parseObject();
            }

            if (ch === '[') {
                return parseArray();
            }

            if (ch === '"') {
                return parseString();
            }

            if (ch === '-' || (ch >= '0' && ch <= '9')) {
                NUMBER_REGEX.lastIndex = pos;
                var number = NUMBER_REGEX.exec(text);

                // Reject leftovers like "01", "1." or "-" that the regex only partly matched.
                if (!number || /[0-9.eE+\-]/.test(text[NUMBER_REGEX.lastIndex] || '')) {
                    throw new ParseError('invalidNumber', pos);
                }

                pos = NUMBER_REGEX.lastIndex;
                return { type: 'number', raw: number[0] };
            }

            LITERAL_REGEX.lastIndex = pos;
            var literal = LITERAL_REGEX.exec(text);

            if (literal && !/[A-Za-z0-9_]/.test(text[LITERAL_REGEX.lastIndex] || '')) {
                pos = LITERAL_REGEX.lastIndex;
                return { type: 'literal', raw: literal[0] };
            }

            fail(/[A-Za-z]/.test(ch || '') ? 'unexpectedToken' : 'expectedValue');
        }

        function parseObject() {
            var node = { type: 'object', entries: [] };

            pos++; // {
            skipWhitespace();

            if (text[pos] === '}') {
                pos++;
                return node;
            }

            for (;;) {
                skipWhitespace();

                if (text[pos] !== '"') {
                    fail(text[pos] === '}' && node.entries.length ? 'trailingComma' : 'expectedKey');
                }

                var key = parseString().raw;

                skipWhitespace();

                if (text[pos] !== ':') {
                    fail('expectedColon');
                }

                pos++;
                node.entries.push([key, parseValue()]);
                skipWhitespace();

                if (text[pos] === ',') {
                    pos++;
                    continue;
                }

                if (text[pos] === '}') {
                    pos++;
                    return node;
                }

                fail('expectedCommaOrBrace');
            }
        }

        function parseArray() {
            var node = { type: 'array', items: [] };

            pos++; // [
            skipWhitespace();

            if (text[pos] === ']') {
                pos++;
                return node;
            }

            for (;;) {
                skipWhitespace();

                if (text[pos] === ']' && node.items.length) {
                    fail('trailingComma');
                }

                node.items.push(parseValue());
                skipWhitespace();

                if (text[pos] === ',') {
                    pos++;
                    continue;
                }

                if (text[pos] === ']') {
                    pos++;
                    return node;
                }

                fail('expectedCommaOrBracket');
            }
        }

        // Ignore a leading byte order mark (common in files saved on Windows).
        if (text.charCodeAt(0) === 0xFEFF) {
            pos = 1;
        }

        var root = parseValue();

        skipWhitespace();

        if (pos < text.length) {
            fail('extraData');
        }

        return root;
    }

    /**
     * Serialize a parsed tree. indent = '' produces minified output.
     */
    function stringify(root, indent) {
        var out = [];
        var pretty = indent !== '';
        var colon = pretty ? ': ' : ':';

        function write(node, depth) {
            if (node.type === 'object' || node.type === 'array') {
                var isObject = node.type === 'object';
                var children = isObject ? node.entries : node.items;

                if (!children.length) {
                    out.push(isObject ? '{}' : '[]');
                    return;
                }

                var innerPad = pretty ? '\n' + indent.repeat(depth + 1) : '';

                out.push(isObject ? '{' : '[');

                children.forEach(function (child, i) {
                    out.push((i ? ',' : '') + innerPad);

                    if (isObject) {
                        out.push(child[0] + colon);
                        write(child[1], depth + 1);
                    } else {
                        write(child, depth + 1);
                    }
                });

                out.push((pretty ? '\n' + indent.repeat(depth) : '') + (isObject ? '}' : ']'));
                return;
            }

            out.push(node.raw);
        }

        write(root, 0);

        return out.join('');
    }

    function getLineColumn(text, pos) {
        var before = text.slice(0, pos);
        var lines = before.split('\n');

        return { line: lines.length, column: lines[lines.length - 1].length + 1 };
    }

    function formatBytes(bytes) {
        return bytes < 1024 ? bytes + ' B' : (bytes / 1024).toFixed(1) + ' KB';
    }

    /**
     * Validate text. Returns { ok: true, root } or { ok: false, message, pos }.
     */
    function validate(text) {
        try {
            return { ok: true, root: parse(text) };
        } catch (e) {
            if (e instanceof ParseError) {
                return { ok: false, message: i18n.errors[e.key], pos: e.pos };
            }

            if (e instanceof RangeError) {
                return { ok: false, message: i18n.tooDeep, pos: 0 };
            }

            throw e;
        }
    }

    function describe(root, text) {
        var type = i18n.types[root.type];
        var detail = '';

        if (root.type === 'object') {
            detail = ' · ' + i18n.keys.replace('%s', root.entries.length);
        } else if (root.type === 'array') {
            detail = ' · ' + i18n.items.replace('%s', root.items.length);
        }

        return type + detail + ' · ' + formatBytes(new Blob([text]).size);
    }

    function init(root) {
        var input = root.querySelector('[data-json-input]');
        var formatButton = root.querySelector('[data-json-format]');
        var minifyButton = root.querySelector('[data-json-minify]');
        var indentSelect = root.querySelector('[data-json-indent]');
        var status = root.querySelector('[data-json-status]');
        var statusTitle = root.querySelector('[data-json-status-title]');
        var statusDetail = root.querySelector('[data-json-status-detail]');
        var validateTimer;

        if (!input) {
            return;
        }

        function getIndent() {
            var value = indentSelect.value;

            return value === 'tab' ? '\t' : ' '.repeat(parseInt(value, 10) || 2);
        }

        function showStatus(result) {
            var text = input.value;

            status.classList.remove('is-valid', 'is-invalid');

            if (text.trim() === '') {
                statusTitle.textContent = '';
                statusDetail.textContent = i18n.empty;
                return;
            }

            if (result.ok) {
                status.classList.add('is-valid');
                statusTitle.textContent = '✓ ' + i18n.valid;
                statusDetail.textContent = describe(result.root, text);
                return;
            }

            var location = getLineColumn(text, result.pos);

            status.classList.add('is-invalid');
            statusTitle.textContent = '✗ ' + i18n.invalid;
            statusDetail.textContent = i18n.location.replace('%1$s', location.line).replace('%2$s', location.column) +
                ' — ' + result.message;
        }

        // Select the character where the error is so it's easy to find.
        function highlightError(pos) {
            input.focus();
            input.setSelectionRange(pos, Math.min(pos + 1, input.value.length));
        }

        function runValidation() {
            var result = validate(input.value);

            showStatus(result);

            return result;
        }

        function transform(indent, doneMessage) {
            clearTimeout(validateTimer);

            var result = runValidation();

            if (!result.ok) {
                highlightError(result.pos);
                return;
            }

            FTH.setText(input, stringify(result.root, indent));
            clearTimeout(validateTimer); // setText fires an input event; validate once, now.
            runValidation();
            tool.update();
            tool.showMessage(doneMessage);
        }

        var tool = FTH.bindTextTool(root, input, function (text) {
            var isEmpty = text.trim() === '';

            formatButton.disabled = isEmpty;
            minifyButton.disabled = isEmpty;

            clearTimeout(validateTimer);
            validateTimer = setTimeout(runValidation, isEmpty ? 0 : VALIDATE_DELAY);
        });

        formatButton.addEventListener('click', function () {
            transform(getIndent(), i18n.formatted);
        });

        minifyButton.addEventListener('click', function () {
            transform('', i18n.minified);
        });

        indentSelect.addEventListener('change', function () {
            // Re-format with the new indent if the text is already formatted (multi-line).
            if (input.value.indexOf('\n') !== -1 && validate(input.value).ok) {
                transform(getIndent(), i18n.formatted);
            }
        });
    }

    FTH.onReady(function () {
        document.querySelectorAll('[data-fth-json-formatter]').forEach(init);
    });

    // Exposed for testing / reuse.
    FTH.json = { parse: parse, stringify: stringify, validate: validate };
})();
