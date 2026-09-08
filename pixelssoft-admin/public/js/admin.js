(function () {
    function activate(root, name) {
        root.querySelectorAll('.ui-tab').forEach(function (tab) {
            var on = tab.getAttribute('data-tab') === name;
            tab.classList.toggle('is-active', on);
            tab.setAttribute('aria-selected', on ? 'true' : 'false');
        });
        root.querySelectorAll('.ui-tab-panel').forEach(function (panel) {
            panel.classList.toggle('is-active', panel.getAttribute('data-panel') === name);
        });
        try {
            if (history.replaceState) {
                history.replaceState(null, '', '#' + name);
            } else {
                location.hash = name;
            }
        } catch (e) {}
    }

    function initTabs(root) {
        var tabs = root.querySelectorAll('.ui-tab');
        if (!tabs.length) return;

        var initial = (location.hash || '').replace(/^#/, '');
        var names = Array.prototype.map.call(tabs, function (t) { return t.getAttribute('data-tab'); });
        var current = root.querySelector('.ui-tab.is-active');
        if (!initial || names.indexOf(initial) === -1) {
            initial = (current && current.getAttribute('data-tab')) || names[0];
        }
        activate(root, initial);

        tabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                activate(root, tab.getAttribute('data-tab'));
            });
        });
    }

    document.querySelectorAll('[data-ui-tabs]').forEach(initTabs);
})();
