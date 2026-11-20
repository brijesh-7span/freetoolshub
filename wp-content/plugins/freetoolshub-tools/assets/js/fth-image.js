/**
 * FreeToolsHub — shared helpers for image tools
 *
 * Exposes window.FTH.image. Everything runs in the browser; nothing is uploaded.
 * Depends on assets/js/fth-tools.js (window.FTH).
 */
(function () {
    'use strict';

    var MAX_FILE_SIZE = 25 * 1024 * 1024;

    var ACCEPTED_TYPES = ['image/jpeg', 'image/png', 'image/webp'];
    var ACCEPTED_EXT = /\.(jpe?g|png|webp)$/i;

    var EXTENSIONS = {
        'image/jpeg': 'jpg',
        'image/png': 'png',
        'image/webp': 'webp'
    };

    var LABELS = {
        'image/jpeg': 'JPG',
        'image/png': 'PNG',
        'image/webp': 'WebP'
    };

    var i18n = window.fthImageI18n || {
        invalidType: 'Please choose a JPG, PNG or WebP image.',
        tooLarge: 'This file is larger than 25 MB. Please choose a smaller image.',
        readError: 'Sorry, this image could not be read. It may be damaged or in an unsupported format.',
        processError: 'This image is too large for your browser to process. Try smaller dimensions.',
        webpFallback: 'Your browser can\'t create WebP images, so PNG was used instead.'
    };

    var encodeSupport = {};

    function formatBytes(bytes) {
        if (bytes < 1024) {
            return bytes + ' B';
        }

        if (bytes < 1024 * 1024) {
            return (bytes / 1024).toFixed(1) + ' KB';
        }

        return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
    }

    function getFileType(file) {
        if (ACCEPTED_TYPES.indexOf(file.type) !== -1) {
            return file.type;
        }

        // Some systems report an empty MIME type; fall back to the extension.
        var match = file.name.match(ACCEPTED_EXT);

        if (!match) {
            return '';
        }

        var ext = match[1].toLowerCase();

        return ext === 'png' ? 'image/png' : ext === 'webp' ? 'image/webp' : 'image/jpeg';
    }

    function getBaseName(file) {
        return file.name.replace(/\.[^.]+$/, '') || 'image';
    }

    /**
     * Whether this browser's canvas can encode the given type (e.g. older Safari can't do WebP).
     */
    function canEncode(type) {
        if (!(type in encodeSupport)) {
            var canvas = document.createElement('canvas');
            canvas.width = canvas.height = 1;
            encodeSupport[type] = canvas.toDataURL(type).indexOf('data:' + type) === 0;
        }

        return encodeSupport[type];
    }

    function createCanvas(width, height) {
        var canvas = document.createElement('canvas');
        canvas.width = width;
        canvas.height = height;

        var ctx = canvas.getContext('2d');

        if (!ctx) {
            throw new Error('Canvas too large');
        }

        ctx.imageSmoothingEnabled = true;
        ctx.imageSmoothingQuality = 'high';

        return { canvas: canvas, ctx: ctx };
    }

    /**
     * Draw an image onto a new canvas at the given size. Large reductions are
     * done in halving steps, which gives sharper, less jagged results.
     *
     * @param {boolean} fillWhite Paint a white background (for JPG, which has no transparency).
     */
    function drawToCanvas(img, width, height, fillWhite) {
        var source = img;
        var currentWidth = img.naturalWidth;
        var currentHeight = img.naturalHeight;

        while (currentWidth / 2 >= width && currentHeight / 2 >= height) {
            currentWidth = Math.round(currentWidth / 2);
            currentHeight = Math.round(currentHeight / 2);

            var step = createCanvas(currentWidth, currentHeight);
            step.ctx.drawImage(source, 0, 0, currentWidth, currentHeight);
            source = step.canvas;
        }

        var target = createCanvas(width, height);

        if (fillWhite) {
            target.ctx.fillStyle = '#ffffff';
            target.ctx.fillRect(0, 0, width, height);
        }

        target.ctx.drawImage(source, 0, 0, width, height);

        return target.canvas;
    }

    /**
     * Promise wrapper for canvas.toBlob(). Rejects if the browser can't encode.
     * Quality is ignored for PNG.
     */
    function canvasToBlob(canvas, type, quality) {
        return new Promise(function (resolve, reject) {
            canvas.toBlob(function (blob) {
                blob ? resolve(blob) : reject(new Error('Encoding failed'));
            }, type, type === 'image/png' ? undefined : quality);
        });
    }

    /**
     * Validate and decode an image file.
     * Resolves { img, url, type }; rejects with an Error whose message is a user-facing string.
     * The caller owns `url` and should revoke it with URL.revokeObjectURL() when done.
     */
    function loadImage(file) {
        return new Promise(function (resolve, reject) {
            var type = getFileType(file);

            if (!type) {
                reject(new Error(i18n.invalidType));
                return;
            }

            if (file.size > MAX_FILE_SIZE) {
                reject(new Error(i18n.tooLarge));
                return;
            }

            var url = URL.createObjectURL(file);
            var img = new Image();

            img.onload = function () {
                resolve({ img: img, url: url, type: type });
            };

            img.onerror = function () {
                URL.revokeObjectURL(url);
                reject(new Error(i18n.readError));
            };

            img.src = url;
        });
    }

    /**
     * Wire up a drop zone + hidden file input. Calls onFile(file) for the chosen/dropped file.
     */
    function bindDropzone(dropzone, fileInput, onFile) {
        fileInput.addEventListener('change', function () {
            if (fileInput.files.length) {
                onFile(fileInput.files[0]);
            }
        });

        ['dragenter', 'dragover'].forEach(function (eventName) {
            dropzone.addEventListener(eventName, function (e) {
                e.preventDefault();
                dropzone.classList.add('is-dragover');
            });
        });

        ['dragleave', 'drop'].forEach(function (eventName) {
            dropzone.addEventListener(eventName, function (e) {
                e.preventDefault();
                dropzone.classList.remove('is-dragover');
            });
        });

        dropzone.addEventListener('drop', function (e) {
            if (e.dataTransfer && e.dataTransfer.files.length) {
                onFile(e.dataTransfer.files[0]);
            }
        });
    }

    /**
     * Show a status message; isError switches it to the error style.
     */
    function setMessage(el, text, isError) {
        el.textContent = text || '';
        el.classList.toggle('is-error', !!isError);
    }

    /**
     * Enable/disable an <a download> button.
     */
    function setDownload(link, url, filename) {
        if (url) {
            link.href = url;
            link.download = filename;
            link.setAttribute('aria-disabled', 'false');
        } else {
            link.removeAttribute('href');
            link.setAttribute('aria-disabled', 'true');
        }
    }

    // Block clicks on disabled download links (keyboard users can still reach them).
    document.addEventListener('click', function (e) {
        var link = e.target.closest && e.target.closest('a.fth-btn[aria-disabled="true"]');

        if (link) {
            e.preventDefault();
        }
    });

    FTH.image = {
        EXTENSIONS: EXTENSIONS,
        LABELS: LABELS,
        i18n: i18n,
        formatBytes: formatBytes,
        getBaseName: getBaseName,
        canEncode: canEncode,
        drawToCanvas: drawToCanvas,
        canvasToBlob: canvasToBlob,
        loadImage: loadImage,
        bindDropzone: bindDropzone,
        setMessage: setMessage,
        setDownload: setDownload
    };
})();
