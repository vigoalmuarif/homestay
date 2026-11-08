import { createPopper } from "@popperjs/core";
import Alpine from "alpinejs";
import flatpickr from "flatpickr";
import "flatpickr/dist/flatpickr.min.css";
import $ from "jquery";
import select2 from "select2";
import "select2/dist/css/select2.min.css";
import "./bootstrap";
import initCharts from "./components/chart";
import registerUi from "./ui";

window.$ = window.jQuery = $;
select2(window, $);

// Defensive patch for Select2 4.1.0 in strict-mode ES module bundling
if ($.fn.select2 && $.fn.select2.amd) {
    $.fn.select2.amd.require(['select2/data/select'], function (SelectAdapter) {
        if (SelectAdapter && SelectAdapter.prototype && SelectAdapter.prototype._normalizeItem) {
            const origSelectNormalize = SelectAdapter.prototype._normalizeItem;
            SelectAdapter.prototype._normalizeItem = function (item) {
                if (!item) return item;

                if (typeof item !== 'object') {
                    item = { id: item, text: item };
                }

                // Provide a safe fallback context with generateResultId if 'this' is undefined or missing container
                const safeCtx = (this && this.container) ? this : {
                    container: { id: 's2-' + Math.random().toString(36).slice(2, 8) },
                    generateResultId: function (container, data) {
                        return (container ? container.id : 's2') + '-result-' + (data.id != null ? data.id : Math.random().toString(36).slice(2, 8));
                    }
                };

                const normalized = origSelectNormalize.call(safeCtx, item);

                // Always ensure _resultId is generated for selectable items
                if (normalized && normalized.id != null && !normalized._resultId) {
                    normalized._resultId = safeCtx.generateResultId(safeCtx.container, normalized);
                }

                return normalized;
            };
        }
    });
}

/**
 * TailAdmin Select2 Initializer & Reusable Template Engine
 */
window.initTailAdminSelect2 = function (selector, customOptions = {}) {
    const $ = window.jQuery;
    if (!$) return null;
    const $el = typeof selector === 'string' ? $(selector) : selector;
    if (!$el || !$el.length) return null;

    // Check if Select2 was already initialized on this element
    const isInitialized = $el.hasClass('select2-hidden-accessible');
    if (isInitialized) {
        if (customOptions.reinit || customOptions.destroy) {
            try {
                $el.select2('destroy');
            } catch (e) {
                // Ignore destroy errors if DOM state was detached
            }
        } else {
            return $el;
        }
    }

    const placeholder = customOptions.placeholder
        || $el.data('placeholder')
        || $el.attr('placeholder')
        || '-- Pilih Opsi --';

    let ajaxUrl = customOptions.ajaxUrl
        || $el.data('ajaxUrl')
        || $el.data('ajax-url')
        || $el.attr('data-ajax-url');

    // Normalize URL to relative path or same-origin to prevent CORS / host mismatch
    if (ajaxUrl && typeof ajaxUrl === 'string') {
        try {
            if (ajaxUrl.startsWith('http://') || ajaxUrl.startsWith('https://')) {
                const parsed = new URL(ajaxUrl, window.location.origin);
                ajaxUrl = parsed.pathname + parsed.search;
            }
        } catch (e) {
            // Keep original if URL parsing fails
        }
    }

    const dropdownParentVal = customOptions.dropdownParent
        || $el.data('dropdownParent')
        || $el.data('dropdown-parent')
        || $el.attr('data-dropdown-parent');

    const defaults = {
        width: '100%',
        allowClear: customOptions.allowClear !== undefined ? customOptions.allowClear : true,
        placeholder: placeholder,
        escapeMarkup: function (markup) { return markup; },
        templateResult: function (item) {
            if (!item.id) return item.text;

            const badge = item.badge || item.category;
            const subtitle = item.subtitle || (item.has_stock ? 'Stok: ' + item.stock_quantity : '');
            const price = item.price ? 'Rp ' + Number(item.price).toLocaleString('id-ID') : (item.trailingText || '');

            const badgeHtml = badge ? `<span class="rounded bg-gray-100 dark:bg-gray-800 px-1.5 py-0.5 text-[11px] font-medium text-gray-600 dark:text-gray-300 uppercase">${badge}</span>` : '';
            const subtitleHtml = subtitle ? `<span class="text-[11px] text-gray-400 ms-1.5">(${subtitle})</span>` : '';
            const priceHtml = price ? `<span class="text-xs font-semibold text-brand-600 dark:text-brand-400 shrink-0 ms-2">${price}</span>` : '';

            return $(
                `<div class="flex items-center justify-between py-1">` +
                    `<div class="flex items-center gap-2 truncate">` +
                        badgeHtml +
                        `<span class="font-medium text-gray-800 dark:text-white/90 truncate">${item.name || item.text}</span>` +
                        subtitleHtml +
                    `</div>` +
                    priceHtml +
                `</div>`
            );
        },
        templateSelection: function (item) {
            if (!item.id) return item.text;
            if (item.name && item.price) {
                return `${item.name} - Rp ${Number(item.price).toLocaleString('id-ID')}`;
            }
            return item.name || item.text;
        }
    };

    if (ajaxUrl) {
        defaults.ajax = {
            url: ajaxUrl,
            dataType: 'json',
            delay: 250,
            data: function (params) {
                const base = {
                    q: params.term || '',
                    format: 'select2'
                };
                if (typeof customOptions.ajaxParams === 'function') {
                    return Object.assign(base, customOptions.ajaxParams(params));
                }
                if (typeof customOptions.ajaxParams === 'object' && customOptions.ajaxParams !== null) {
                    return Object.assign(base, customOptions.ajaxParams);
                }
                return base;
            },
            processResults: function (data) {
                return {
                    results: data.results || (Array.isArray(data) ? data : [])
                };
            },
            cache: true
        };
        defaults.minimumInputLength = 0;
    }

    if (dropdownParentVal) {
        let $parent = null;
        try {
            $parent = typeof dropdownParentVal === 'string' ? $(dropdownParentVal) : $(dropdownParentVal);
        } catch (e) {
            $parent = null;
        }

        if ($parent && $parent.length && $parent[0]) {
            defaults.dropdownParent = $parent;
        } else {
            const $closestDialog = $el.closest('[data-slot="dialog-content"], [role="dialog"]');
            if ($closestDialog.length && $closestDialog[0]) {
                defaults.dropdownParent = $closestDialog;
            } else {
                defaults.dropdownParent = $(document.body);
            }
        }
    }

    // Merge options cleanly: shallow copy for options, with combined ajax object
    const finalOptions = Object.assign({}, defaults, customOptions);
    if (defaults.ajax || customOptions.ajax) {
        finalOptions.ajax = Object.assign({}, defaults.ajax, customOptions.ajax);
    }

    $el.select2(finalOptions);

    // Auto-focus search field and trigger initial query on open
    $el.off('select2:open.tailadmin').on('select2:open.tailadmin', function () {
        setTimeout(() => {
            const container = defaults.dropdownParent ? $(defaults.dropdownParent) : $(document.body);
            const searchField = container.find('.select2-search__field');
            if (searchField.length) {
                searchField[0].focus();
                // If search field is empty, trigger input to load initial items
                if (!searchField.val()) {
                    searchField.trigger('input');
                }
            }
        }, 30);
    });

    return $el;
};

if (window.jQuery) {
    window.jQuery.fn.tailAdminSelect2 = function (options = {}) {
        return this.each(function () {
            window.initTailAdminSelect2($(this), options);
        });
    };
}

window.Alpine = Alpine;
window.createPopper = createPopper;
window.flatpickr = flatpickr;

window.showUiDialog = function (dialogIdOrEl) {
    const el = typeof dialogIdOrEl === 'string' ? document.getElementById(dialogIdOrEl) : dialogIdOrEl;
    if (el && window.Alpine) {
        try {
            const data = window.Alpine.$data(el);
            if (data && typeof data.show === 'function') {
                data.show();
            }
        } catch (e) {
            console.warn('[TailAdmin] Failed to open dialog:', dialogIdOrEl, e);
        }
    }
};

window.closeUiDialog = function (dialogIdOrEl) {
    const el = typeof dialogIdOrEl === 'string' ? document.getElementById(dialogIdOrEl) : dialogIdOrEl;
    if (el && window.Alpine) {
        try {
            const data = window.Alpine.$data(el);
            if (data && typeof data.close === 'function') {
                data.close();
            }
        } catch (e) {
            console.warn('[TailAdmin] Failed to close dialog:', dialogIdOrEl, e);
        }
    }
};

// Register Alpine.js components before initializing
Alpine.data("dropdown", () => ({
    open: false,
    toggle() {
        this.open = !this.open;
        if (this.open) this.position();
    },
    position() {
        this.$nextTick(() => {
            const button = this.$el;
            const dropdown = this.$refs.dropdown;
            const rect = button.getBoundingClientRect();

            // Apply initial styles to ensure measurement is possible
            dropdown.style.position = "fixed";
            dropdown.style.zIndex = "999";

            const isRtl = document.documentElement.getAttribute("dir") === "rtl";
            if (isRtl) {
                dropdown.style.left = `${rect.left}px`;
                dropdown.style.right = "auto";
            } else {
                dropdown.style.right = `${window.innerWidth - rect.right - 13}px`;
                dropdown.style.left = "auto";
            }

            const dropdownHeight = Math.max(dropdown.offsetHeight, 0);
            const spaceBelow = window.innerHeight - rect.bottom;
            const spaceAbove = rect.top;

            if (spaceBelow < dropdownHeight && spaceAbove > spaceBelow) {
                dropdown.style.top = `${rect.top - dropdownHeight}px`;
            } else {
                dropdown.style.top = `${rect.bottom}px`;
            }
        });
    },
    init() {
        this.$watch("open", (value) => {
            if (value) this.position();
        });
    },
}));

registerUi(Alpine);

// Free ships English only: choosing a language only switches the layout direction (RTL for Arabic).
Alpine.store("direction", {
    languages: [
        { id: "en", name: "English", shortName: "English", flag: "flag-us.svg" },
        { id: "ar", name: "Arabic (Saudi)", shortName: "Arabic", flag: "flag-sa.svg", badge: "RTL" },
        { id: "es", name: "Español", shortName: "Español", flag: "flag-es.svg" },
        { id: "de", name: "Deutsch", shortName: "Deutsch", flag: "flag-de.svg" },
    ],
    locale: localStorage.getItem("dir") === "rtl" ? "ar" : "en",
    get current() {
        return this.languages.find((l) => l.id === this.locale) || this.languages[0];
    },
    select(id) {
        this.locale = id;
        const dir = id === "ar" ? "rtl" : "ltr";
        localStorage.setItem("dir", dir);
        document.documentElement.setAttribute("dir", dir);
    },
});

// Global reactive booking store for room filtering & pricing
Alpine.store("booking", {
    roomTypeId: "",
    roomId: "",
    roomPrice: 0,
    availableRooms: [],
    isRoomsLoaded: false,
    isRoomAvailable(roomId, roomTypeId) {
        if (this.roomTypeId && String(this.roomTypeId) !== String(roomTypeId)) {
            return false;
        }
        if (this.isRoomsLoaded) {
            return this.availableRooms.some((r) => String(r.id) === String(roomId));
        }
        return true;
    },
});

Alpine.start();


// Initialize components on DOM ready
document.addEventListener("DOMContentLoaded", () => {
    // Map imports
    if (document.querySelector("#mapOne")) {
        import("./components/maps/world-map").then((module) => module.initMap());
    }
    // Chart imports
    initCharts();

    // Calendar init
    if (document.querySelector("#calendar")) {
        import("./components/calendar/init").then((module) => module.calendarInit());
    }

    // copy button
    const copyInput = document.getElementById("copy-input");
    if (copyInput) {
        // Select the copy button and input field
        const copyButton = document.getElementById("copy-button");
        const copyText = document.getElementById("copy-text");
        const websiteInput = document.getElementById("website-input");

        // Event listener for the copy button
        copyButton.addEventListener("click", () => {
            // Copy the input value to the clipboard
            navigator.clipboard.writeText(websiteInput.value).then(() => {
                // Change the text to "Copied"
                copyText.textContent = "Copied";

                // Reset the text back to "Copy" after 2 seconds
                setTimeout(() => {
                    copyText.textContent = "Copy";
                }, 2000);
            });
        });
    }

    // Auto-init Select2 elements configured via data-tailadmin-select2="true"
    if (window.jQuery) {
        window.jQuery('select[data-tailadmin-select2="true"], select[data-select2="true"]').each(function () {
            const $el = window.jQuery(this);
            // Only auto-init elements that are already visible in the DOM
            if (!$el.hasClass('select2-hidden-accessible') && $el.is(':visible')) {
                $el.tailAdminSelect2();
            }
        });
    }
});
