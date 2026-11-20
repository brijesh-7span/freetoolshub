/**
 * FreeToolsHub — shared helpers for text tools
 *
 * Exposes window.FTH for individual tool scripts.
 */
(function () {
    'use strict';

    var i18n = window.fthToolsI18n || {
        copied: 'Text copied to clipboard!',
        copyFailed: 'Could not copy — please copy manually.',
        cleared: 'Text cleared.'
    };

    function onReady(callback) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', callback);
        } else {
            callback();
        }
    }

    function copyText(text) {
        if (navigator.clipboard && window.isSecureContext) {
            // The Clipboard API can be refused (permissions, embedded browsers) — fall back if so.
            return navigator.clipboard.writeText(text).catch(function () {
                return legacyCopy(text);
            });
        }

        // Non-HTTPS pages have no Clipboard API.
        return legacyCopy(text);
    }

    function legacyCopy(text) {
        return new Promise(function (resolve, reject) {
            var helper = document.createElement('textarea');
            helper.value = text;
            helper.setAttribute('readonly', '');
            helper.style.position = 'fixed';
            helper.style.opacity = '0';
            document.body.appendChild(helper);
            helper.select();

            var ok = false;
            try {
                ok = document.execCommand('copy');
            } catch (e) {
                ok = false;
            }

            document.body.removeChild(helper);
            ok ? resolve() : reject();
        });
    }

    /**
     * Replace a textarea's text while keeping the browser's undo history
     * (Ctrl+Z) where supported.
     */
    function setText(textarea, value) {
        textarea.focus();
        textarea.select();

        var inserted = false;
        try {
            inserted = value === ''
                ? document.execCommand('delete')
                : document.execCommand('insertText', false, value);
        } catch (e) {
            inserted = false;
        }

        if (!inserted || textarea.value !== value) {
            textarea.value = value;
        }

        textarea.setSelectionRange(0, 0);
        textarea.scrollTop = 0;
    }

    /**
     * Wire up the Copy / Clear buttons and status message of a text tool.
     *
     * @param {HTMLElement} root     Tool wrapper containing [data-fth-copy], [data-fth-clear], [data-fth-message].
     * @param {HTMLTextAreaElement} input
     * @param {Function} [onChange]  Called after the text changes (typing, clearing, programmatic updates).
     * @return {Object} { update(), showMessage(text) }
     */
    function bindTextTool(root, input, onChange) {
        var copyBtn = root.querySelector('[data-fth-copy]');
        var clearBtn = root.querySelector('[data-fth-clear]');
        var message = root.querySelector('[data-fth-message]');
        var messageTimer;

        function showMessage(text) {
            if (!message) {
                return;
            }

            message.textContent = text;
            clearTimeout(messageTimer);
            messageTimer = setTimeout(function () {
                message.textContent = '';
            }, 2500);
        }

        function update() {
            var isEmpty = input.value.length === 0;

            if (copyBtn) {
                copyBtn.disabled = isEmpty;
            }

            if (clearBtn) {
                clearBtn.disabled = isEmpty;
            }

            if (onChange) {
                onChange(input.value);
            }
        }

        input.addEventListener('input', update);

        if (copyBtn) {
            copyBtn.addEventListener('click', function () {
                copyText(input.value).then(function () {
                    showMessage(i18n.copied);
                }, function () {
                    input.select();
                    showMessage(i18n.copyFailed);
                });
            });
        }

        if (clearBtn) {
            clearBtn.addEventListener('click', function () {
                setText(input, '');
                update();
                input.focus();
                showMessage(i18n.cleared);
            });
        }

        // Browsers may restore textarea content on back/forward navigation.
        update();

        return {
            update: update,
            showMessage: showMessage
        };
    }

    window.FTH = {
        onReady: onReady,
        copyText: copyText,
        setText: setText,
        bindTextTool: bindTextTool
    };
})();
