import dialog from './dialog';
import dropdown from './dropdown';
import select from './select';
import combobox from './combobox';

/** Registers the Alpine components used by the Blade UI kit (`resources/views/components/ui`). */
export default function registerUi(Alpine) {
    Alpine.data('uiDialog', dialog);
    Alpine.data('uiDropdown', dropdown);
    Alpine.data('uiSelect', select);
    Alpine.data('uiCombobox', combobox);
}
