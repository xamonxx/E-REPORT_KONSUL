import Alpine from 'alpinejs';
import flatpickr from 'flatpickr';
import { Indonesian } from 'flatpickr/dist/l10n/id.js';
import Swal from 'sweetalert2';
import 'flatpickr/dist/flatpickr.css';

window.Alpine = Alpine;
window.Swal = Swal;

window.initDatePickers = function initDatePickers(scope = document) {
    scope.querySelectorAll('[data-datepicker]').forEach((input) => {
        if (input._flatpickr) {
            return;
        }

        flatpickr(input, {
            locale: Indonesian,
            altInput: true,
            altFormat: 'd/m/Y',
            dateFormat: 'Y-m-d',
            disableMobile: true,
            monthSelectorType: 'static',
            prevArrow:
                '<svg viewBox="0 0 24 24" class="h-4 w-4"><path d="M15 18l-6-6 6-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            nextArrow:
                '<svg viewBox="0 0 24 24" class="h-4 w-4"><path d="M9 6l6 6-6 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            onReady: (_selectedDates, _dateStr, instance) => {
                instance.altInput.classList.add('date-picker-input');
                instance.altInput.placeholder = input.dataset.datepickerPlaceholder || 'Pilih tanggal';
            },
        });
    });
};

window.setDatePickerValue = function setDatePickerValue(id, value = '') {
    const input = document.getElementById(id);
    if (!input || !input._flatpickr) {
        return;
    }

    input._flatpickr.setDate(value || '', false, 'Y-m-d');
};

window.appShell = function appShell(defaultSidebarOpen) {
    return {
        isMobile: window.innerWidth < 1024,
        sidebarOpen: window.innerWidth >= 1024 ? defaultSidebarOpen : false,
        init() {
            this.$watch('sidebarOpen', (value) => {
                if (!this.isMobile) {
                    document.cookie = `sidebar_open=${value}; path=/; max-age=31536000`;
                }
            });

            window.setTimeout(() => {
                document.querySelectorAll('.toast-container').forEach((element) => element.remove());
            }, 3000);
        },
        handleResize() {
            this.isMobile = window.innerWidth < 1024;
            if (this.isMobile) {
                this.sidebarOpen = false;
            }
        },
    };
};

window.notificationBadge = function notificationBadge(initialCount, apiUrl, csrfToken) {
    return {
        badgeCount: initialCount,
        startPolling() {
            window.setInterval(() => {
                fetch(apiUrl, {
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                })
                    .then((response) => (response.ok ? response.json() : Promise.reject(response)))
                    .then((data) => {
                        this.badgeCount = data.total || 0;
                    })
                    .catch((error) => console.error('Polling error:', error));
            }, 15000);
        },
    };
};

window.consultationsPage = function consultationsPage(config) {
    return {
        showImportModal: config.showImportModal,
        showCreateModal: config.showCreateModal,
        showEditModal: false,
        editData: {},
        init() {
            document.querySelectorAll('.btn-edit').forEach((button) => {
                button.addEventListener('click', () => {
                    this.editData = {
                        id: button.getAttribute('data-id'),
                        consultation_id: button.getAttribute('data-consultation-id'),
                        client_name: button.getAttribute('data-name'),
                        phone: button.getAttribute('data-phone'),
                        province: button.getAttribute('data-province'),
                        city: button.getAttribute('data-city'),
                        district: button.getAttribute('data-district'),
                        address: button.getAttribute('data-address'),
                        account_id: button.getAttribute('data-account'),
                        needs_category_id: button.getAttribute('data-category'),
                        status_category_id: button.getAttribute('data-status'),
                        consultation_date: button.getAttribute('data-date'),
                        notes: button.getAttribute('data-notes'),
                    };
                    this.showEditModal = true;
                });
            });
        },
    };
};

window.modalCityAutoFill = function modalCityAutoFill(initialCity = '', initialProvince = '') {
    return {
        city: initialCity,
        province: initialProvince,
        loading: false,
        suggestions: [],
        showSuggestions: false,
        mapping: null,
        async getMapping() {
            if (this.mapping) {
                return this.mapping;
            }

            this.loading = true;
            try {
                const response = await fetch(document.body.dataset.citiesUrl);
                this.mapping = await response.json();
            } catch (error) {
                this.mapping = {};
            } finally {
                this.loading = false;
            }

            return this.mapping;
        },
        async onCityInput() {
            const value = this.city.trim().toLowerCase();
            if (value.length < 2) {
                this.suggestions = [];
                this.showSuggestions = false;
                return;
            }

            const mapping = await this.getMapping();
            this.suggestions = Object.entries(mapping)
                .filter(([city]) => city.toLowerCase().includes(value))
                .slice(0, 8)
                .map(([city, province]) => ({ city, province }));
            this.showSuggestions = this.suggestions.length > 0;
        },
        selectCity(item) {
            this.city = item.city;
            this.province = item.province;
            this.showSuggestions = false;
            this.suggestions = [];
        },
    };
};

window.cityAutoFill = function cityAutoFill(initialCity = '', initialProvince = '') {
    return window.modalCityAutoFill(initialCity, initialProvince);
};

window.searchableOptions = function searchableOptions(options = []) {
    const normalizedOptions = options.map((option) => {
        if (typeof option === 'string') {
            return {
                value: option,
                label: option,
            };
        }

        return {
            value: String(option?.value ?? option?.id ?? ''),
            label: String(option?.label ?? option?.name ?? option?.value ?? option?.id ?? ''),
        };
    });

    return {
        open: false,
        search: '',
        options: normalizedOptions,
        openPanel() {
            this.open = true;
            this.search = '';
            this.$nextTick(() => this.$refs.searchInput?.focus());
        },
        close() {
            this.open = false;
            this.search = '';
        },
        toggle() {
            if (this.open) {
                this.close();
                return;
            }

            this.openPanel();
        },
        filteredOptions() {
            const keyword = this.search.trim().toLowerCase();

            if (keyword === '') {
                return this.options;
            }

            return this.options.filter((option) => option.label.toLowerCase().includes(keyword));
        },
    };
};

window.searchableSelect = function searchableSelect(options = [], initialValue = '', onChangeHandler = null) {
    return {
        ...window.searchableOptions(options),
        selected: String(initialValue ?? ''),
        selectedLabel(placeholder = 'Pilih opsi...') {
            const current = this.options.find((option) => option.value === String(this.selected ?? ''));
            return current?.label ?? placeholder;
        },
        setSelected(value) {
            this.selected = String(value ?? '');

            if (onChangeHandler && typeof window[onChangeHandler] === 'function') {
                window[onChangeHandler](this.selected);
            }

            this.close();
        },
        clear() {
            this.setSelected('');
        },
    };
};

window.updatePreviewId = function updatePreviewId(accountId) {
    const target = document.getElementById('preview-consultation-id');
    if (!accountId || !target) {
        return;
    }

    target.style.opacity = '0.5';
    fetch(`${document.body.dataset.previewConsultationIdUrl}?account_id=${accountId}`, {
        headers: { Accept: 'application/json' },
    })
        .then((response) => response.json())
        .then((data) => {
            target.textContent = data.id;
            target.style.opacity = '1';
        })
        .catch(() => {
            target.style.opacity = '1';
        });
};

window.syncAnalyticsPeriodType = function syncAnalyticsPeriodType(periodType) {
    window.dispatchEvent(
        new CustomEvent('analytics-period-type', {
            detail: String(periodType ?? 'monthly'),
        })
    );
};

window.buildConsultationUpdateUrl = function buildConsultationUpdateUrl(id) {
    if (!id) {
        return '#';
    }

    return `${document.body.dataset.consultationsBaseUrl}/${id}`;
};

window.confirmDelete = function confirmDelete(formId, clientName) {
    Swal.fire({
        title: 'Hapus data konsultasi?',
        text: `Data lead atas nama '${clientName}' akan terhapus secara permanen dari sistem!`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#9f403d',
        cancelButtonColor: '#737c7f',
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal',
        customClass: {
            popup: 'rounded-2xl shadow-2xl',
            title: 'text-xl font-headline font-bold text-on-surface',
            confirmButton: 'bg-error hover:bg-error-dim rounded-xl px-8 py-3 font-bold',
            cancelButton: 'bg-outline hover:bg-outline-variant rounded-xl px-8 py-3 font-bold',
        },
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById(formId)?.submit();
        }
    });
};

window.masterDataPage = function masterDataPage(config) {
    return {
        activeTab: config.initialTab,
        showEditUserModal: config.showEditUserModal,
        editUser: {
            id: config.editUser.id ?? '',
            name: config.editUser.name ?? '',
            email: config.editUser.email ?? '',
            role: config.editUser.role ?? 'admin',
            account_id: config.editUser.account_id ?? '',
        },
        init() {
            const picker = document.getElementById('statusColorPicker');
            const text = document.getElementById('statusColorText');

            if (picker && text) {
                text.value = picker.value.toUpperCase();
                picker.addEventListener('input', (event) => {
                    text.value = event.target.value.toUpperCase();
                });
            }

            this.toggleAccountField();

            if (this.showEditUserModal) {
                document.body.classList.add('overflow-hidden');
            }
        },
        toggleAccountField() {
            const role = document.getElementById('roleSelect');
            const field = document.getElementById('accountField');

            if (!role || !field) {
                return;
            }

            field.style.visibility = role.value === 'super_admin' ? 'hidden' : 'visible';
            field.style.opacity = role.value === 'super_admin' ? '0' : '1';
        },
        openEditUser(payload) {
            this.editUser = {
                id: payload.id ?? '',
                name: payload.name ?? '',
                email: payload.email ?? '',
                role: payload.role ?? 'admin',
                account_id: payload.account_id ?? '',
            };
            this.activeTab = 'users';
            this.showEditUserModal = true;

            document.body.classList.add('overflow-hidden');
        },
        closeEditUserModal() {
            this.showEditUserModal = false;
            document.body.classList.remove('overflow-hidden');
        },
        buildUserUpdateUrl(id) {
            if (!id) {
                return '#';
            }

            return `${document.body.dataset.masterDataUsersBaseUrl}/${id}`;
        },
    };
};

window.toggleCatEdit = function toggleCatEdit(id) {
    document.querySelectorAll(`.cat-display-${id}`).forEach((element) => element.classList.toggle('hidden'));
    document.querySelectorAll(`.cat-edit-${id}`).forEach((element) => element.classList.toggle('hidden'));
};

window.toggleStatusEdit = function toggleStatusEdit(id) {
    document.querySelectorAll(`.status-display-${id}`).forEach((element) => element.classList.toggle('hidden'));
    document.querySelectorAll(`.status-edit-${id}`).forEach((element) => element.classList.toggle('hidden'));
};

window.promptResetPassword = function promptResetPassword(userId, userName) {
    Swal.fire({
        title: 'Reset Password',
        text: `Masukkan password baru untuk ${userName}`,
        input: 'password',
        inputAttributes: {
            autocapitalize: 'off',
            autocorrect: 'off',
        },
        showCancelButton: true,
        confirmButtonText: 'Simpan Password',
        cancelButtonText: 'Batal',
        showLoaderOnConfirm: true,
        customClass: {
            popup: 'rounded-2xl shadow-xl',
            input: 'bg-surface-container-low border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/20',
            confirmButton: 'bg-primary rounded-xl px-6 py-2.5 text-sm font-bold',
            cancelButton: 'bg-outline-variant/30 rounded-xl px-6 py-2.5 text-sm font-bold',
        },
        preConfirm: (newPassword) => {
            if (!newPassword || newPassword.length < 6) {
                Swal.showValidationMessage('Password minimal 6 karakter');
                return false;
            }

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/master-data/users/${userId}/reset-password`;

            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = document.querySelector('meta[name="csrf-token"]').content;

            const method = document.createElement('input');
            method.type = 'hidden';
            method.name = '_method';
            method.value = 'PUT';

            const passInput = document.createElement('input');
            passInput.type = 'hidden';
            passInput.name = 'password';
            passInput.value = newPassword;

            form.append(csrf, method, passInput);
            document.body.appendChild(form);
            form.submit();
            return true;
        },
    });
};

window.loginPage = function loginPage(config) {
    return {
        showBugModal: false,
        bugMessage: '',
        waNumber: config.waNumber,
        submitBugReport() {
            if (this.bugMessage.trim() === '') {
                window.alert('Isi pesan keluhan terlebih dahulu!');
                return;
            }

            const text = encodeURIComponent(
                `Halo Tim Database, saya ingin melaporkan bug/error di aplikasi E-REPORT:\n\n${this.bugMessage}`
            );
            window.open(`https://api.whatsapp.com/send?phone=${this.waNumber}&text=${text}`, '_blank');
            this.showBugModal = false;
            this.bugMessage = '';
        },
    };
};

window.settingsPage = function settingsPage(initialColor) {
    const normalizeHex = (value) => {
        const hex = String(value ?? '')
            .trim()
            .toUpperCase();

        return /^#[0-9A-F]{6}$/.test(hex) ? hex : '#D97706';
    };

    const hexToRgb = (hex) => [
        parseInt(hex.slice(1, 3), 16),
        parseInt(hex.slice(3, 5), 16),
        parseInt(hex.slice(5, 7), 16),
    ];

    const mix = (base, target, amount) =>
        base.map((channel, index) => Math.round(channel * (1 - amount) + target[index] * amount));

    const contrastColor = (rgb) => {
        const luminance = (0.299 * rgb[0] + 0.587 * rgb[1] + 0.114 * rgb[2]) / 255;
        return luminance > 0.62 ? [43, 52, 55] : [255, 255, 255];
    };

    const toRgbString = (rgb) => rgb.join(' ');

    return {
        themeColor: normalizeHex(initialColor),
        presets: ['#D97706', '#C2410C', '#0F766E', '#2563EB', '#BE185D', '#4F46E5'],
        applyPreset(color) {
            this.themeColor = normalizeHex(color);
        },
        previewVariables() {
            const color = normalizeHex(this.themeColor);
            const primary = hexToRgb(color);

            return [
                `--color-primary-rgb: ${toRgbString(primary)}`,
                `--color-primary-dim-rgb: ${toRgbString(mix(primary, [0, 0, 0], 0.18))}`,
                `--color-primary-container-rgb: ${toRgbString(mix(primary, [255, 255, 255], 0.82))}`,
                `--color-primary-fixed-rgb: ${toRgbString(mix(primary, [255, 255, 255], 0.82))}`,
                `--color-primary-fixed-dim-rgb: ${toRgbString(mix(primary, [255, 255, 255], 0.68))}`,
                `--color-on-primary-rgb: ${toRgbString(contrastColor(primary))}`,
                `--color-on-primary-container-rgb: ${toRgbString(mix(primary, [0, 0, 0], 0.6))}`,
                `--color-on-primary-fixed-rgb: ${toRgbString(mix(primary, [0, 0, 0], 0.72))}`,
                `--color-on-primary-fixed-variant-rgb: ${toRgbString(mix(primary, [0, 0, 0], 0.24))}`,
                `--color-inverse-primary-rgb: ${toRgbString(mix(primary, [255, 255, 255], 0.45))}`,
                `--color-surface-tint-rgb: ${toRgbString(primary)}`,
                `--color-primary-hex: ${color}`,
            ].join('; ');
        },
    };
};

Alpine.start();
window.initDatePickers();
