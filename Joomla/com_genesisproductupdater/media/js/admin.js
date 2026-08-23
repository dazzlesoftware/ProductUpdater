(function () {
    'use strict';
    const platformFields = {
        joomla: ['version', 'target_version', 'tag', 'is_current', 'download_url', 'sha512', 'info_url', 'info_title', 'changelog_mode', 'changelog_url', 'release_date', 'requires', 'tested', 'requires_php'],
        wordpress: ['version', 'tag', 'is_current', 'download_url', 'info_url', 'changelog_mode', 'changelog_url', 'release_date', 'requires', 'tested', 'requires_php'],
        mobile: ['version', 'is_current', 'changelog_mode', 'changelog_url', 'build_number', 'url_ios', 'url_android', 'force_update', 'release_notes'],
        fab: ['version', 'is_current', 'changelog_mode', 'changelog_url', 'url_fab', 'release_notes']
    };
    const platformTypes = {
        joomla: ['template', 'component', 'module', 'plugin', 'library', 'package', 'file', 'language', 'custom'],
        wordpress: ['wordpress-plugin', 'wordpress-theme', 'custom'],
        mobile: ['mobile-app', 'custom'],
        fab: ['fab-2d-assets', 'fab-3d-models', 'fab-animations', 'fab-audio', 'fab-environments', 'fab-game-templates', 'fab-tools-plugins', 'fab-ui', 'custom']
    };
    function fieldName(input) {
        const matches = input && input.name ? Array.from(input.name.matchAll(/\[([^\]]+)\]/g)) : [];
        return matches.length ? matches[matches.length - 1][1] : '';
    }
    function update() {
        const platform = document.getElementById('jform_platform');
        const type = document.getElementById('jform_type');
        const subform = document.querySelector('joomla-field-subform[name="jform[versions]"]') || document.querySelector('#jform_versions joomla-field-subform');
        if (!platform) return;
        const slug = String(platform.value || 'joomla').toLowerCase();
        const visibleFields = platformFields[slug] || platformFields.joomla;
        if (type) {
            Array.from(type.options).forEach(function (option) { option.hidden = !(platformTypes[slug] || []).includes(option.value); });
            if (!type.value || (type.selectedOptions[0] && type.selectedOptions[0].hidden)) {
                const next = Array.from(type.options).find(function (option) { return !option.hidden; });
                type.value = next ? next.value : '';
            }
        }
        if (!subform) return;
        subform.querySelectorAll('tbody tr, .subform-repeatable-group').forEach(function (row) {
            const cells = Array.from(row.children).filter(function (cell) { return cell.tagName === 'TD'; });
            cells.forEach(function (cell, index) {
                const name = fieldName(cell.querySelector('[name]'));
                if (!name) return;
                let visible = visibleFields.includes(name);
                if (name === 'changelog_url') {
                    const mode = row.querySelector('[name$="[changelog_mode]"]');
                    visible = visible && mode && mode.value === 'custom';
                }
                cell.hidden = !visible;
                cell.style.display = visible ? '' : 'none';
                cell.querySelectorAll('input,select,textarea,button').forEach(function (control) { control.disabled = !visible; });
                const table = row.closest('table');
                const header = table ? table.querySelectorAll('thead th')[index] : null;
                if (header) {
                    header.hidden = !visible;
                    header.style.display = visible ? '' : 'none';
                }
            });
        });
    }
    function start() {
        update();
        document.addEventListener('change', function (event) {
            if (event.target.matches('#jform_platform, [name$="[changelog_mode]"]')) update();
        });
        document.addEventListener('subform-row-add', update);
        const subform = document.querySelector('joomla-field-subform[name="jform[versions]"]') || document.querySelector('#jform_versions joomla-field-subform');
        if (subform) new MutationObserver(update).observe(subform, {childList: true, subtree: true});
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start, {once: true});
    else start();
}());
