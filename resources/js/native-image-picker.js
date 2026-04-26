import { Camera, On, Events } from '../../vendor/nativephp/mobile/resources/dist/native.js';

const LOG = (...args) => console.log('[native-picker]', ...args);
const ERR = (...args) => console.error('[native-picker]', ...args);

// NativePHP for Android exposes a JavaScript bridge via addJavascriptInterface.
// `window.AndroidPOST` and `window.AndroidBridge` are present only inside the
// NativePHP WebView, never in a regular browser, so this is a reliable sync check.
function isNativeAvailable() {
    if (typeof window === 'undefined') return false;
    return typeof window.AndroidPOST !== 'undefined' || typeof window.AndroidBridge !== 'undefined';
}

const pendingByPickerId = new Map();
let listenerInstalled = false;

function installMediaSelectedListener() {
    if (listenerInstalled) return;
    listenerInstalled = true;

    On(Events.Gallery.MediaSelected, (payload) => {
        LOG('MediaSelected event:', payload);

        const id = payload?.id;
        const resolver = id ? pendingByPickerId.get(id) : null;
        if (!resolver) {
            ERR('received MediaSelected with unknown id:', id);
            return;
        }
        pendingByPickerId.delete(id);

        if (!payload.success) {
            const reason = payload.cancelled ? 'cancelled' : (payload.error || 'unknown');
            LOG('picker did not return a file:', reason);
            resolver.reject(new Error(reason));
            return;
        }

        const file = (payload.files && payload.files[0]) || null;
        if (!file || !file.path) {
            ERR('MediaSelected returned no file path:', payload);
            resolver.reject(new Error('no file in payload'));
            return;
        }
        resolver.resolve(file);
    });

    LOG('installed MediaSelected listener');
}

async function pickImageNative() {
    installMediaSelectedListener();

    const id = 'picker_' + Date.now() + '_' + Math.random().toString(36).slice(2, 8);
    LOG('launching native gallery picker, id =', id);

    return new Promise((resolve, reject) => {
        pendingByPickerId.set(id, { resolve, reject });
        Camera.pickImages().images().id(id).then(
            () => LOG('Camera.pickImages bridge call dispatched, awaiting MediaSelected event'),
            (err) => {
                ERR('Camera.pickImages bridge call failed:', err);
                pendingByPickerId.delete(id);
                reject(err);
            }
        );
    });
}

/**
 * Wire a "tap to pick" image control.
 *
 * Expects the element tree:
 *   <label data-native-picker data-path-input="..." data-preview="...">
 *     <input type="file" name="image">
 *     <input type="hidden" name="image_native_path">
 *     ...
 *   </label>
 *
 * On Android (NativePHP), tapping the label calls the native gallery picker
 * and writes the selected file's path into the hidden input.
 * On the web, the regular <input type="file"> is used.
 */
export function setupNativePicker(label) {
    if (!label || label.__nativePickerWired) return;
    label.__nativePickerWired = true;

    const fileInput = label.querySelector('input[type="file"]');
    const pathInput = label.querySelector('input[type="hidden"][data-native-path]');
    if (!fileInput) {
        ERR('setupNativePicker: no <input type="file"> inside label', label);
        return;
    }

    label.addEventListener('click', (event) => {
        if (!isNativeAvailable()) {
            LOG('not on NativePHP — letting <input type="file"> handle the tap');
            return;
        }

        // Stop the underlying <input type="file"> click that the WebView ignores.
        event.preventDefault();
        event.stopPropagation();
        LOG('intercepted click — opening native gallery');

        pickImageNative().then((file) => {
            LOG('picked file:', file);

            if (pathInput) {
                pathInput.value = file.path;
                LOG('wrote path into hidden input:', file.path);
            } else {
                ERR('no hidden [data-native-path] input found; backend will not see the path');
            }

            label.dispatchEvent(new CustomEvent('native-picker-selected', {
                detail: { path: file.path, mimeType: file.mimeType },
                bubbles: true,
            }));
        }).catch((err) => {
            LOG('native pick aborted:', err?.message || err);
            label.dispatchEvent(new CustomEvent('native-picker-cancelled', { bubbles: true }));
        });
    }, true); // capture so we get the click before <input type="file">
}

export function setupAllNativePickers(root = document) {
    root.querySelectorAll('label[data-native-picker]').forEach(setupNativePicker);
}

if (typeof document !== 'undefined') {
    LOG('module loaded; native bridge available?', isNativeAvailable());
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => setupAllNativePickers());
    } else {
        setupAllNativePickers();
    }
    document.addEventListener('alpine:initialized', () => setupAllNativePickers());
}
