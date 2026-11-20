/**
 * FreeToolsHub — Image Resizer
 *
 * Resizes images in the browser with <canvas>; nothing is uploaded.
 * Supports multiple instances on one page.
 * Depends on assets/js/fth-tools.js and assets/js/fth-image.js (window.FTH.image).
 */
(function () {
    'use strict';

    var MAX_DIMENSION = 10000;
    var RENDER_DELAY = 250;

    var Img = FTH.image;

    var i18n = window.fthImageResizer || {
        invalidSize: 'Enter a width and height between 1 and 10000 pixels.',
        smaller: 'smaller',
        larger: 'larger',
        ready: 'Resized image is ready to download.'
    };

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
        var widthInput = root.querySelector('[data-ir-width]');
        var heightInput = root.querySelector('[data-ir-height]');
        var lockInput = root.querySelector('[data-ir-lock]');
        var presetButtons = root.querySelectorAll('[data-ir-preset]');
        var formatSelect = root.querySelector('[data-img-format]');
        var qualityField = root.querySelector('[data-img-quality-field]');
        var qualityInput = root.querySelector('[data-img-quality]');
        var qualityValue = root.querySelector('[data-img-quality-value]');
        var qualityHint = root.querySelector('[data-img-quality-hint]');
        var downloadLink = root.querySelector('[data-img-download]');
        var resetButton = root.querySelector('[data-img-reset]');
        var message = root.querySelector('[data-img-message]');

        var state = {
            file: null,
            type: '',
            img: null,
            ratio: 1,
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

        function getOutputType() {
            var value = formatSelect.value;

            return value === 'original' ? state.type : value;
        }

        function getDimensions() {
            var width = parseInt(widthInput.value, 10);
            var height = parseInt(heightInput.value, 10);

            if (
                !(width >= 1 && width <= MAX_DIMENSION) ||
                !(height >= 1 && height <= MAX_DIMENSION)
            ) {
                return null;
            }

            return { width: width, height: height };
        }

        function updateQualityState() {
            var isPng = getOutputType() === 'image/png';

            qualityInput.disabled = isPng;
            qualityHint.hidden = !isPng;
            qualityField.classList.toggle('is-disabled', isPng);
            qualityValue.textContent = qualityInput.value + '%';
        }

        function render() {
            var dimensions = getDimensions();
            var token = ++state.renderToken;

            if (!state.img) {
                return;
            }

            if (!dimensions) {
                resultLoading.hidden = true;
                resultImg.hidden = true;
                resultMeta.textContent = '';
                Img.setDownload(downloadLink, null);
                Img.setMessage(message, i18n.invalidSize, true);
                return;
            }

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
                var change = Math.round(((blob.size - state.file.size) / state.file.size) * 100);
                var changeText = change === 0
                    ? ''
                    : ' · ' + Math.abs(change) + '% ' + (change < 0 ? i18n.smaller : i18n.larger);

                resultImg.src = state.resultUrl;
                resultImg.hidden = false;
                resultMeta.textContent = dimensions.width + ' × ' + dimensions.height + ' px · ' +
                    Img.formatBytes(blob.size) + ' · ' + (Img.LABELS[outputType] || outputType) + changeText;

                Img.setDownload(
                    downloadLink,
                    state.resultUrl,
                    Img.getBaseName(state.file) + '-' + dimensions.width + 'x' + dimensions.height +
                        '.' + (Img.EXTENSIONS[outputType] || 'png')
                );

                Img.setMessage(
                    message,
                    requestedType === 'image/webp' && outputType !== 'image/webp' ? Img.i18n.webpFallback : i18n.ready
                );
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
                state.ratio = img.naturalWidth / img.naturalHeight;
                state.originalUrl = loaded.url;

                originalImg.src = loaded.url;
                originalMeta.textContent = img.naturalWidth + ' × ' + img.naturalHeight + ' px · ' +
                    Img.formatBytes(file.size) + ' · ' + Img.LABELS[loaded.type];

                widthInput.value = Math.min(img.naturalWidth, MAX_DIMENSION);
                heightInput.value = Math.min(img.naturalHeight, MAX_DIMENSION);

                resultImg.hidden = true;
                resultMeta.textContent = '';
                Img.setMessage(message, '');

                dropzone.hidden = true;
                workspace.hidden = false;

                updateQualityState();
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
            Img.setDownload(downloadLink, null);
            Img.setMessage(message, '');
            Img.setMessage(uploadMessage, '');

            workspace.hidden = true;
            dropzone.hidden = false;
            fileInput.focus();
        }


        // Upload
        Img.bindDropzone(dropzone, fileInput, loadFile);


        // Dimensions
        widthInput.addEventListener('input', function () {
            var width = parseInt(widthInput.value, 10);

            if (lockInput.checked && width > 0) {
                heightInput.value = Math.max(1, Math.round(width / state.ratio));
            }

            scheduleRender();
        });

        heightInput.addEventListener('input', function () {
            var height = parseInt(heightInput.value, 10);

            if (lockInput.checked && height > 0) {
                widthInput.value = Math.max(1, Math.round(height * state.ratio));
            }

            scheduleRender();
        });

        lockInput.addEventListener('change', function () {
            var width = parseInt(widthInput.value, 10);

            if (lockInput.checked && width > 0) {
                heightInput.value = Math.max(1, Math.round(width / state.ratio));
                scheduleRender();
            }
        });

        presetButtons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                var percent = parseInt(btn.getAttribute('data-ir-preset'), 10) / 100;

                widthInput.value = Math.max(1, Math.round(state.img.naturalWidth * percent));
                heightInput.value = Math.max(1, Math.round(state.img.naturalHeight * percent));
                scheduleRender();
            });
        });


        // Output
        formatSelect.addEventListener('change', function () {
            updateQualityState();
            scheduleRender();
        });

        qualityInput.addEventListener('input', function () {
            qualityValue.textContent = qualityInput.value + '%';
            scheduleRender();
        });


        // Actions
        resetButton.addEventListener('click', reset);

        Img.setDownload(downloadLink, null);
    }

    FTH.onReady(function () {
        document.querySelectorAll('[data-fth-image-resizer]').forEach(init);
    });
})();
