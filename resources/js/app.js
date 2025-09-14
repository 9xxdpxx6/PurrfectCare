import './bootstrap';
import * as bootstrap from 'bootstrap';
import TomSelect from 'tom-select';
import AirDatepicker from 'air-datepicker';
import localeRu from 'air-datepicker/locale/ru.js';

window.bootstrap = bootstrap;

window.createTomSelect = function (selector, options = {}) {
    const defaultTomSelectOptions = {
        create: false,
        plugins: ['remove_button'],
        allowEmptyOption: true,
        placeholder: 'Выберите значение...',
        maxOptions: 30,
        persist: false,
        onFocus() {
            if (this.getValue() === "") {
                this.clear();
            }
        },
        onItemAdd() {
            this.control_input.value = '';
            this.lastQuery = null;
            this.refreshOptions(false);

            if (this.items.length > 0) {
                this.settings.placeholder = '';
                this.refreshState();
            }
        },
        onChange() {
            if (this.items.length === 0) {
                this.settings.placeholder = options.placeholder || 'Выберите значение...';
            } else {
                this.settings.placeholder = '';
            }
            this.refreshState();
        },
        onBlur() {
            if (this.items.length > 0) {
                this.settings.placeholder = '';
                this.refreshState();
            }
        }
    };

    return new TomSelect(selector, { ...defaultTomSelectOptions, ...options });
};

window.createDatepicker = function (selector, options = {}) {
    const defaultDatepickerOptions = {
        autoClose: true,
        dateFormat: 'dd.MM.yyyy',
        locale: localeRu,
    };

    return new AirDatepicker(selector, { ...defaultDatepickerOptions, ...options });
};

// Инициализация Bootstrap tooltips
document.addEventListener('DOMContentLoaded', function() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
