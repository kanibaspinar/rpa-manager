(function () {
    function updateResult(data) {
        var el = document.getElementById('rpa-devices-result');
        if (!el) return;
        try {
            el.textContent = JSON.stringify(data, null, 2);
        } catch (e) {
            el.textContent = String(data || '');
        }
    }

    function sendRequest(payload) {
        if (!window.RPA_MANAGER_DEVICES_API_URL) {
            updateResult({ success: false, message: 'Devices API URL is not configured.' });
            return Promise.resolve();
        }
        return fetch(window.RPA_MANAGER_DEVICES_API_URL, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
        }).then(function (r) { return r.json(); });
    }

    /* ── Searchable user picker ── */
    function initUserPicker() {
        var users    = window.RPA_USERS || [];
        var hidden   = document.getElementById('rpa-admin-user');
        var input    = document.getElementById('rpa-user-search');
        var dropdown = document.getElementById('rpa-user-dropdown');
        if (!hidden || !input || !dropdown) return;

        var activeIdx = -1;

        function label(u) {
            var s = '#' + u.id + ' — ' + u.username;
            if (u.email) s += ' (' + u.email + ')';
            return s;
        }

        function highlight(text, q) {
            if (!q) return text;
            var esc = q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            return text.replace(new RegExp('(' + esc + ')', 'gi'), '<strong>$1</strong>');
        }

        function render(q) {
            q = (q || '').toLowerCase();
            var matches = users.filter(function (u) {
                if (!q) return true;
                return u.username.toLowerCase().indexOf(q) !== -1
                    || u.email.toLowerCase().indexOf(q) !== -1
                    || String(u.id).indexOf(q) !== -1;
            });

            if (matches.length === 0) {
                dropdown.innerHTML = '<div style="padding:10px 12px; color:#999; font-size:12px;">No users found</div>';
                dropdown.style.display = 'block';
                activeIdx = -1;
                return;
            }

            var html = '';
            matches.forEach(function (u, i) {
                html += '<div class="rpa-user-opt" data-id="' + u.id + '" data-index="' + i + '"'
                    + ' style="padding:8px 12px; cursor:pointer; font-size:13px; transition:background .1s;">'
                    + highlight(label(u), q)
                    + '</div>';
            });
            dropdown.innerHTML = html;
            dropdown.style.display = 'block';
            activeIdx = -1;

            var opts = dropdown.querySelectorAll('.rpa-user-opt');
            opts.forEach(function (opt) {
                opt.addEventListener('mouseenter', function () {
                    clearActive(opts);
                    this.style.background = 'rgba(59,130,246,.1)';
                    activeIdx = parseInt(this.getAttribute('data-index'), 10);
                });
                opt.addEventListener('mouseleave', function () {
                    this.style.background = '';
                });
                opt.addEventListener('mousedown', function (e) {
                    e.preventDefault(); // keep focus on input
                    selectUser(this.getAttribute('data-id'));
                });
            });
        }

        function clearActive(opts) {
            (opts || dropdown.querySelectorAll('.rpa-user-opt')).forEach(function (o) {
                o.style.background = '';
            });
        }

        function setActive(opts, idx) {
            clearActive(opts);
            if (opts[idx]) {
                opts[idx].style.background = 'rgba(59,130,246,.1)';
                opts[idx].scrollIntoView({ block: 'nearest' });
            }
        }

        function selectUser(id) {
            id = parseInt(id, 10);
            hidden.value = id;
            var u = users.find(function (u) { return u.id === id; });
            if (u) {
                input.value = label(u);
            }
            dropdown.style.display = 'none';
            input.style.borderColor = 'rgba(59,130,246,.5)';
        }

        function clearSelection() {
            hidden.value = '';
            input.style.borderColor = 'rgba(0,0,0,.15)';
        }

        input.addEventListener('focus', function () {
            if (hidden.value) {
                input.select();
            }
            render(input.value && !hidden.value ? input.value : '');
        });

        input.addEventListener('input', function () {
            clearSelection();
            render(this.value);
        });

        input.addEventListener('keydown', function (e) {
            var opts = dropdown.querySelectorAll('.rpa-user-opt');
            if (!opts.length) return;

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                activeIdx = activeIdx < opts.length - 1 ? activeIdx + 1 : 0;
                setActive(opts, activeIdx);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                activeIdx = activeIdx > 0 ? activeIdx - 1 : opts.length - 1;
                setActive(opts, activeIdx);
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (activeIdx >= 0 && opts[activeIdx]) {
                    selectUser(opts[activeIdx].getAttribute('data-id'));
                }
            } else if (e.key === 'Escape') {
                dropdown.style.display = 'none';
            }
        });

        document.addEventListener('click', function (e) {
            if (!e.target.closest('#rpa-user-picker')) {
                dropdown.style.display = 'none';
            }
        });
    }

    function getSelectedUserId() {
        var el = document.getElementById('rpa-admin-user');
        return el ? parseInt(el.value, 10) || 0 : 0;
    }

    document.addEventListener('DOMContentLoaded', function () {
        initUserPicker();

        var btnUserDevs  = document.getElementById('btn-rpa-list-user-devices');
        var btnAvail     = document.getElementById('btn-rpa-list-available');
        var btnAssign    = document.getElementById('btn-rpa-assign');
        var btnCreate    = document.getElementById('btn-rpa-create-device');
        var btnDelete    = document.getElementById('btn-rpa-delete-device');

        if (btnUserDevs) {
            btnUserDevs.addEventListener('click', function () {
                var uid = getSelectedUserId();
                if (!uid) {
                    alert(window.RPA_MANAGER_I18N && RPA_MANAGER_I18N.select_user || 'Please select a user first.');
                    return;
                }
                sendRequest({ action: 'list_user_devices', user_id: uid })
                    .then(updateResult)
                    .catch(function (e) { updateResult({ success: false, message: e.message || String(e) }); });
            });
        }

        if (btnAvail) {
            btnAvail.addEventListener('click', function () {
                sendRequest({ action: 'list_available' })
                    .then(updateResult)
                    .catch(function (e) { updateResult({ success: false, message: e.message || String(e) }); });
            });
        }

        if (btnAssign) {
            btnAssign.addEventListener('click', function () {
                var uid = getSelectedUserId();
                if (!uid) {
                    alert(window.RPA_MANAGER_I18N && RPA_MANAGER_I18N.select_user || 'Please select a user first.');
                    return;
                }
                var countInput = document.getElementById('rpa-assign-count');
                var count = countInput && countInput.value ? parseInt(countInput.value, 10) : 1;
                if (!count || count < 1) count = 1;
                sendRequest({ action: 'assign_devices', user_id: uid, count: count })
                    .then(updateResult)
                    .catch(function (e) { updateResult({ success: false, message: e.message || String(e) }); });
            });
        }

        if (btnCreate) {
            btnCreate.addEventListener('click', function () {
                var idEl   = document.getElementById('rpa-create-device-id');
                var nameEl = document.getElementById('rpa-create-device-name');
                var devId  = idEl && idEl.value ? idEl.value.trim() : '';
                var name   = nameEl && nameEl.value ? nameEl.value.trim() : '';
                if (!devId || !name) {
                    alert(window.RPA_MANAGER_I18N && RPA_MANAGER_I18N.fill_device || 'Please fill device_id and Name.');
                    return;
                }
                sendRequest({ action: 'create_device', device_id: devId, name: name })
                    .then(updateResult)
                    .catch(function (e) { updateResult({ success: false, message: e.message || String(e) }); });
            });
        }

        if (btnDelete) {
            btnDelete.addEventListener('click', function () {
                var idEl  = document.getElementById('rpa-delete-device-id');
                var devId = idEl && idEl.value ? idEl.value.trim() : '';
                if (!devId) {
                    alert(window.RPA_MANAGER_I18N && RPA_MANAGER_I18N.enter_device || 'Please enter device_id to delete.');
                    return;
                }
                var confirmMsg = window.RPA_MANAGER_I18N && RPA_MANAGER_I18N.confirm_delete
                    ? RPA_MANAGER_I18N.confirm_delete
                    : 'Are you sure you want to delete/unassign this device?';
                if (!confirm(confirmMsg)) {
                    return;
                }
                sendRequest({ action: 'delete_device', device_id: devId })
                    .then(updateResult)
                    .catch(function (e) { updateResult({ success: false, message: e.message || String(e) }); });
            });
        }
    });
})();
