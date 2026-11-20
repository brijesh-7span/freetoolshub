/**
 * FreeToolsHub — Image Compressor
 *
 * Re-encodes images in the browser with <canvas> to reduce file size; nothing is uploaded.
 * Supports multiple instances on one page.
 * Depends on assets/js/fth-tools.js and assets/js/fth-image.js (window.FTH.image).
 */
(function () {
    'use strict';

    var RENDER_DELAY = 250;

    var Img = FTH.image;

    var i18n = window.fthImageCompressor || {
        saved: 'You saved',
        increased: 'Size increase',
        autoFormat: 'Auto will save this image as %s.',
        ready: 'Compressed image is ready to download.',
        notSmaller: 'This image is already well optimized — the compressed version isn\'t smaller. Try a lower quality, a smaller max width or WebP.'
    };

    /**
     * Whether an image has any transparent pixels (checked on a small copy for speed).
     */
    function hasTransparency(img) {
        var scale = Math.min(1, 256 / Math.max(img.naturalWidth, img.naturalHeight));
        var width = Math.max(1, Math.round(img.naturalWidth * scale));
        var height = Math.max(1, Math.round(img.naturalHeight * scale));
        var data;

        try {
            data = Img.drawToCanvas(img, width, height, false)
                .getContext('2d')
                .getImageData(0, 0, width, height).data;
        } catch (e) {
            return true; // Play safe: keep a format that supports transparency.
        }

        for (var i = 3; i < data.length; i += 4) {
            if (data[i] < 255) {
                return true;
            }
        }

        return false;
    }

    function init(root) {
        var dropzone = root.querySelector('[data-img-dropzone]');
        var fileInput = root.querySelector('[data-img-file]');
        var uploadMessage = root.querySelector('[data-img-upload-message]');
        var workspace = root.querySelector('[data-img-workspace]');
        var originalImg = root.querySelector('[data-img-original]');
        var originalMeta = root.querySelector('[data-img-original-meta]');
        var resultImg = root.querySelector('[data-img-result]');
        var resultLoading = root.querySelector('[data-img-result-loading]');
        var resultMeta = root.querySelector('[data-img-result-meta]');
        var formatSelect = root.querySelector('[data-img-format]');
        var formatHint = root.querySelector('[data-ic-format-hint]');
        var maxWidthSelect = root.querySelector('[data-ic-max-width]');
        var qualityField = root.querySelector('[data-img-quality-field]');
        var qualityInput = root.querySelector('[data-img-quality]');
        var qualityValue = root.querySelector('[data-img-quality-value]');
        var qualityHint = root.querySelector('[data-img-quality-hint]');
        var downloadLink = root.querySelector('[data-img-download]');
        var resetButton = root.querySelector('[data-img-reset]');
        var message = root.querySelector('[data-img-message]');
        var originalSizeEl = root.querySelector('[data-ic-original-size]');
        var compressedSizeEl = root.querySelector('[data-ic-compressed-size]');
        var savedCard = root.querySelector('[data-ic-saved-card]');
        var savedLabel = root.querySelector('[data-ic-saved-label]');
        var savedEl = root.querySelector('[data-ic-saved]');
        var barOriginal = root.querySelector('[data-ic-bar-original]');
        var barCompressed = root.querySelector('[data-ic-bar-compressed]');

        var state = {
            file: null,
            type: '',
            img: null,
            transparent: false,
            originalUrl: '',
            resultUrl: '',
            renderTimer: null,
            renderToken: 0
        };

        function revokeUrls() {
            if (state.originalUrl) {
                URL.revokeObjectURL(state.originalUrl);
                state.originalUrl = '';
            }

            if (state.resultUrl) {
                URL.revokeObjectURL(state.resultUrl);
                state.resultUrl = '';
            }
        }

        /**
         * "Auto": keep JPG as JPG; turn PNG/WebP into WebP (keeps transparency).
         * Without WebP support, use JPG for opaque images and PNG for transparent ones.
         */
        function getAutoType() {
            if (state.type === 'image/jpeg') {
                return 'image/jpeg';
            }

            if (Img.canEncode('image/webp')) {
                return 'image/webp';
            }

            return state.transparent ? 'image/png' : 'image/jpeg';
        }

        function getOutputType() {
            return formatSelect.value === 'auto' ? getAutoType() : formatSelect.value;
        }

        function getDimensions() {
            var width = state.img.naturalWidth;
            var height = state.img.naturalHeight;
            var maxWidth = parseInt(maxWidthSelect.value, 10);

            // Only ever shrink, never enlarge.
            if (maxWidth && width > maxWidth) {
                height = Math.max(1, Math.round(height * (maxWidth / width)));
                width = maxWidth;
            }

            return { width: width, height: height };
        }

        function updateSettingsState() {
            var outputType = getOutputType();
            var isPng = outputType === 'image/png';

            qualityInput.disabled = isPng;
            qualityHint.hidden = !isPng;
            qualityField.classList.toggle('is-disabled', isPng);
            qualityValue.textContent = qualityInput.value + '%';

            formatHint.textContent = formatSelect.value === 'auto'
                ? i18n.autoFormat.replace('%s', Img.LABELS[outputType])
                : '';
        }

        function resetSummary() {
            compressedSizeEl.textContent = '—';
            savedEl.textContent = '—';
            savedLabel.textContent = i18n.saved;
            savedCard.classList.remove('is-negative');
            barCompressed.style.width = '0';
        }

        function showSummary(originalSize, compressedSize) {
            var diff = originalSize - compressedSize;
            var percent = Math.round((Math.abs(diff) / originalSize) * 100);
            var isSmaller = diff > 0;

            originalSizeEl.textContent = Img.formatBytes(originalSize);
            compressedSizeEl.textContent = Img.formatBytes(compressedSize);

            savedLabel.textContent = isSmaller ? i18n.saved : i18n.increased;
            savedEl.textContent = (isSmaller ? '' : '+') + percent + '% (' + Img.formatBytes(Math.abs(diff)) + ')';
            savedCard.classList.toggle('is-negative', !isSmaller);

            barOriginal.style.width = '100%';
            barCompressed.style.width = Math.min(100, (compressedSize / originalSize) * 100) + '%';
        }

        function render() {
            var token = ++state.renderToken;

            if (!state.img) {
                return;
            }

            var dimensions = getDimensions();
            var requestedType = getOutputType();
            var canvas;

            try {
                canvas = Img.drawToCanvas(state.img, dimensions.width, dimensions.height, requestedType === 'image/jpeg');
            } catch (e) {
                resultLoading.hidden = true;
                Img.setMessage(message, Img.i18n.processError, true);
                return;
            }

            Img.canvasToBlob(canvas, requestedType, qualityInput.value / 100).then(function (blob) {
                // A newer render has started — ignore this result.
                if (token !== state.renderToken) {
                    return;
                }

                resultLoading.hidden = true;

                if (state.resultUrl) {
                    URL.revokeObjectURL(state.resultUrl);
                }

                state.resultUrl = URL.createObjectURL(blob);

                // Browsers that can't encode the requested format return PNG.
                var outputType = blob.type || 'image/png';

                resultImg.src = state.resultUrl;
                resultImg.hidden = false;
                resultMeta.textContent = dimensions.width + ' × ' + dimensions.height + ' px · ' +
                    Img.formatBytes(blob.size) + ' · ' + (Img.LABELS[outputType] || outputType);

                showSummary(state.file.size, blob.size);

                Img.setDownload(
                    downloadLink,
                    state.resultUrl,
                    Img.getBaseName(state.file) + '-compressed.' + (Img.EXTENSIONS[outputType] || 'png')
                );

                if (requestedType === 'image/webp' && outputType !== 'image/webp') {
                    Img.setMessage(message, Img.i18n.webpFallback);
                } else if (blob.size >= state.file.size) {
                    Img.setMessage(message, i18n.notSmaller, true);
                } else {
                    Img.setMessage(message, i18n.ready);
                }
            }, function () {
                if (token === state.renderToken) {
                    resultLoading.hidden = true;
                    Img.setMessage(message, Img.i18n.processError, true);
                }
            });
        }

        function scheduleRender() {
            clearTimeout(state.renderTimer);
            resultLoading.hidden = false;
            Img.setDownload(downloadLink, null);
            state.renderTimer = setTimeout(render, RENDER_DELAY);
        }

        function loadFile(file) {
            Img.setMessage(uploadMessage, '');

            Img.loadImage(file).then(function (loaded) {
                var img = loaded.img;

                revokeUrls();

                state.file = file;
                state.type = loaded.type;
                state.img = img;
                state.transparent = loaded.type !== 'image/jpeg' && hasTransparency(img);
                state.originalUrl = loaded.url;

                originalImg.src = loaded.url;
                originalMeta.textContent = img.naturalWidth + ' × ' + img.naturalHeight + ' px · ' +
                    Img.formatBytes(file.size) + ' · ' + Img.LABELS[loaded.type];
                originalSizeEl.textContent = Img.formatBytes(file.size);

                resultImg.hidden = true;
                resultMeta.textContent = '';
                resetSummary();
                Img.setMessage(message, '');

                dropzone.hidden = true;
                workspace.hidden = false;

                updateSettingsState();
                scheduleRender();
            }, function (error) {
                Img.setMessage(uploadMessage, error.message, true);
            });
        }

        function reset() {
            clearTimeout(state.renderTimer);
            state.renderToken++;
            revokeUrls();

            state.file = null;
            state.img = null;

            fileInput.value = '';
            originalImg.removeAttribute('src');
            resultImg.removeAttribute('src');
            resultImg.hidden = true;
            resetSummary();
            Img.setDownload(downloadLink, null);
            Img.setMessage(message, '');
            Img.setMessage(uploadMessage, '');

            workspace.hidden = true;
            dropzone.hidden = false;
            fileInput.focus();
        }


        // Upload
        Img.bindDropzone(dropzone, fileInput, loadFile);

        // Settings
        qualityInput.addEventListener('input', function () {
            qualityValue.textContent = qualityInput.value + '%';
            scheduleRender();
        });

        formatSelect.addEventListener('change', function () {
            updateSettingsState();
            scheduleRender();
        });

        maxWidthSelect.addEventListener('change', scheduleRender);

        // Actions
        resetButton.addEventListener('click', reset);

        Img.setDownload(downloadLink, null);
    }

    FTH.onReady(function () {
        document.querySelectorAll('[data-fth-image-compressor]').forEach(init);
    });
})();
