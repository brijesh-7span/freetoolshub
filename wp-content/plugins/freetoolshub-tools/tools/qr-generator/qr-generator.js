/**
 * FreeToolsHub — QR Code Generator
 *
 * Generates QR codes in the browser with <canvas>; nothing is sent to the server.
 * Supports multiple instances on one page.
 * Depends on assets/js/fth-tools.js, assets/js/fth-image.js and the bundled
 * qrcode-generator library (assets/vendor/qrcode-generator, global `qrcode`).
 */
(function () {
    'use strict';

    var QUIET_ZONE = 4;          // Blank border (in modules) required by the QR spec.
    var ERROR_CORRECTION = 'M';  // Recovers ~15% damage; good balance of size and robustness.
    var MIN_MODULE_PX = 2;       // Below this, phone cameras struggle to read the code.
    var MIN_CONTRAST = 3;
    var RENDER_DELAY = 150;

    var DEFAULT_FG = '#000000';
    var DEFAULT_BG = '#ffffff';

    var Img = FTH.image;

    var i18n = window.fthQrGenerator || {
        tooLong: 'This text is too long for a QR code. Please shorten it.',
        tooDense: 'This text is too long for a %s QR code. Choose a larger size or shorten the text.',
        smallModules: 'This QR code is very dense at this size and may be hard to scan. Try a larger size.',
        lowContrast: 'Low contrast between the colors — this QR code may not scan. Use a darker foreground or lighter background.',
        inverted: 'Light-on-dark QR codes can\'t be read by some scanners. A dark foreground on a light background works best.',
        ready: 'QR code ready — download it as PNG.',
        cleared: 'Cleared. Enter new text to generate another QR code.'
    };

    // Encode text as UTF-8 so non-English characters and emoji work.
    qrcode.stringToBytes = qrcode.stringToBytesFuncs['UTF-8'];

    function createQr(text) {
        var qr = qrcode(0, ERROR_CORRECTION); // 0 = pick the smallest version that fits

        qr.addData(text, 'Byte');
        qr.make();

        return qr;
    }

    /**
     * Draw the QR code onto the canvas at exactly size × size pixels.
     * Modules use whole pixels so edges stay crisp; leftover space becomes extra margin.
     * Returns the module size in px (0 if it doesn't fit).
     */
    function drawQr(canvas, qr, size, fg, bg) {
        var count = qr.getModuleCount();
        var moduleSize = Math.floor(size / (count + QUIET_ZONE * 2));

        if (moduleSize < 1) {
            return 0;
        }

        var offset = Math.floor((size - moduleSize * count) / 2);
        var ctx = canvas.getContext('2d');

        canvas.width = size;
        canvas.height = size;

        ctx.fillStyle = bg;
        ctx.fillRect(0, 0, size, size);
        ctx.fillStyle = fg;

        for (var row = 0; row < count; row++) {
            for (var col = 0; col < count; col++) {
                if (qr.isDark(row, col)) {
                    ctx.fillRect(offset + col * moduleSize, offset + row * moduleSize, moduleSize, moduleSize);
                }
            }
        }

        return moduleSize;
    }

    function luminance(hex) {
        var rgb = [1, 3, 5].map(function (i) {
            var channel = parseInt(hex.substr(i, 2), 16) / 255;
            return channel <= 0.03928 ? channel / 12.92 : Math.pow((channel + 0.055) / 1.055, 2.4);
        });

        return 0.2126 * rgb[0] + 0.7152 * rgb[1] + 0.0722 * rgb[2];
    }

    /**
     * Warn about color combinations that commonly fail to scan.
     */
    function getColorWarning(fg, bg) {
        var fgLum = luminance(fg);
        var bgLum = luminance(bg);
        var ratio = (Math.max(fgLum, bgLum) + 0.05) / (Math.min(fgLum, bgLum) + 0.05);

        if (ratio < MIN_CONTRAST) {
            return i18n.lowContrast;
        }

        if (fgLum > bgLum) {
            return i18n.inverted;
        }

        return '';
    }

    function init(root) {
        var input = root.querySelector('[data-qr-input]');
        var counter = root.querySelector('[data-qr-counter]');
        var sizeInputs = root.querySelectorAll('[data-qr-size]');
        var fgInput = root.querySelector('[data-qr-fg]');
        var bgInput = root.querySelector('[data-qr-bg]');
        var fgValue = root.querySelector('[data-qr-fg-value]');
        var bgValue = root.querySelector('[data-qr-bg-value]');
        var resetColorsButton = root.querySelector('[data-qr-reset-colors]');
        var contrastMessage = root.querySelector('[data-qr-contrast]');
        var canvas = root.querySelector('[data-qr-canvas]');
        var placeholder = root.querySelector('[data-qr-placeholder]');
        var meta = root.querySelector('[data-qr-meta]');
        var downloadLink = root.querySelector('[data-qr-download]');
        var clearButton = root.querySelector('[data-qr-clear]');
        var message = root.querySelector('[data-qr-message]');

        var maxLength = parseInt(input.getAttribute('maxlength'), 10);
        var state = {
            downloadUrl: '',
            renderTimer: null,
            renderToken: 0
        };

        function getSize() {
            for (var i = 0; i < sizeInputs.length; i++) {
                if (sizeInputs[i].checked) {
                    return parseInt(sizeInputs[i].value, 10);
                }
            }

            return 256;
        }

        function clearDownload() {
            if (state.downloadUrl) {
                URL.revokeObjectURL(state.downloadUrl);
                state.downloadUrl = '';
            }

            Img.setDownload(downloadLink, null);
        }

        function showEmpty() {
            canvas.hidden = true;
            placeholder.hidden = false;
            meta.textContent = '';
            clearDownload();
        }

        function render() {
            var token = ++state.renderToken;
            var text = input.value;
            var size = getSize();
            var fg = fgInput.value;
            var bg = bgInput.value;
            var qr;

            Img.setMessage(contrastMessage, getColorWarning(fg, bg), true);

            if (text.trim() === '') {
                showEmpty();
                Img.setMessage(message, '');
                return;
            }

            try {
                qr = createQr(text);
            } catch (e) {
                showEmpty();
                Img.setMessage(message, i18n.tooLong, true);
                return;
            }

            var moduleSize = drawQr(canvas, qr, size, fg, bg);

            if (!moduleSize) {
                showEmpty();
                Img.setMessage(message, i18n.tooDense.replace('%s', size + 'px'), true);
                return;
            }

            canvas.hidden = false;
            placeholder.hidden = true;
            meta.textContent = size + ' × ' + size + ' px · PNG';

            Img.setMessage(message, moduleSize < MIN_MODULE_PX ? i18n.smallModules : i18n.ready, moduleSize < MIN_MODULE_PX);

            Img.canvasToBlob(canvas, 'image/png').then(function (blob) {
                // A newer render has started — ignore this result.
                if (token !== state.renderToken) {
                    return;
                }

                clearDownload();
                state.downloadUrl = URL.createObjectURL(blob);
                Img.setDownload(downloadLink, state.downloadUrl, 'qr-code-' + size + 'px.png');
            });
        }

        function scheduleRender() {
            clearTimeout(state.renderTimer);
            state.renderTimer = setTimeout(render, RENDER_DELAY);
        }

        function updateInputState() {
            counter.textContent = input.value.length + ' / ' + maxLength;
            clearButton.disabled = input.value.length === 0;
        }

        function updateColorLabels() {
            fgValue.textContent = fgInput.value.toUpperCase();
            bgValue.textContent = bgInput.value.toUpperCase();
        }


        // Text: generate as the visitor types
        input.addEventListener('input', function () {
            updateInputState();
            clearDownload();
            scheduleRender();
        });

        // Size + colors: redraw immediately
        sizeInputs.forEach(function (radio) {
            radio.addEventListener('change', render);
        });

        [fgInput, bgInput].forEach(function (colorInput) {
            colorInput.addEventListener('input', function () {
                updateColorLabels();
                render();
            });
        });

        resetColorsButton.addEventListener('click', function () {
            fgInput.value = DEFAULT_FG;
            bgInput.value = DEFAULT_BG;
            updateColorLabels();
            render();
        });

        // Clear
        clearButton.addEventListener('click', function () {
            FTH.setText(input, '');
            // setText fires an input event; cancel its delayed render so it can't wipe the message below.
            clearTimeout(state.renderTimer);
            updateInputState();
            render();
            input.focus();
            Img.setMessage(message, i18n.cleared);
        });

        // Browsers may restore form values on back/forward navigation.
        updateInputState();
        updateColorLabels();
        render();
    }

    FTH.onReady(function () {
        document.querySelectorAll('[data-fth-qr-generator]').forEach(init);
    });
})();
