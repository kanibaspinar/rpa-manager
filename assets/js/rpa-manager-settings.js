(function () {
    'use strict';

    var I18N       = window.RPA_MANAGER_I18N || {};
    var NODES_API  = window.RPA_FARM_NODES_API || '';
    var DEVICES_API = window.RPA_MANAGER_DEVICES_API_URL || '';

    // ─────────────────────────────────────────────────────────────────────────
    // In-memory state
    // ─────────────────────────────────────────────────────────────────────────
    var farmNodes = (window.RPA_FARM_NODES_INIT || []).slice();

    // ─────────────────────────────────────────────────────────────────────────
    // Tab system
    // ─────────────────────────────────────────────────────────────────────────
    function initTabs() {
        var btns   = document.querySelectorAll('.rpa-tab-btn');
        var panels = document.querySelectorAll('.rpa-tab-panel');

        btns.forEach(function (btn) {
            btn.addEventListener('click', function () {
                var target = this.getAttribute('data-tab');
                btns.forEach(function (b) { b.classList.remove('active'); });
                panels.forEach(function (p) { p.classList.remove('active'); });
                this.classList.add('active');
                var panel = document.getElementById('rpa-tab-' + target);
                if (panel) panel.classList.add('active');
            });
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HTTP helpers
    // ─────────────────────────────────────────────────────────────────────────
    function post(apiUrl, payload) {
        return fetch(apiUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
        }).then(function (r) { return r.json(); });
    }

    function setSpinner(id, on) {
        var el = document.getElementById(id);
        if (el) el.style.display = on ? 'inline-block' : 'none';
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Farm Nodes tab
    // ─────────────────────────────────────────────────────────────────────────
    function renderNodesTable() {
        var tbody   = document.getElementById('rpa-nodes-tbody');
        var empty   = document.getElementById('rpa-nodes-empty');
        var table   = document.getElementById('rpa-nodes-table');
        if (!tbody) return;

        tbody.innerHTML = '';

        if (farmNodes.length === 0) {
            if (table)  table.style.display  = 'none';
            if (empty)  empty.style.display  = 'block';
            syncNodeSelectDropdown();
            return;
        }

        if (table)  table.style.display  = '';
        if (empty)  empty.style.display  = 'none';

        farmNodes.forEach(function (node) {
            var tr = document.createElement('tr');
            tr.id  = 'rpa-node-row-' + node.id;
            tr.innerHTML = buildNodeRow(node);
            tbody.appendChild(tr);
        });

        // Bind edit / delete / toggle buttons
        tbody.querySelectorAll('[data-rpa-edit]').forEach(function (btn) {
            btn.addEventListener('click', function () { startEditNode(parseInt(this.getAttribute('data-rpa-edit'), 10)); });
        });
        tbody.querySelectorAll('[data-rpa-toggle]').forEach(function (btn) {
            btn.addEventListener('click', function () { toggleNodeActive(parseInt(this.getAttribute('data-rpa-toggle'), 10)); });
        });
        tbody.querySelectorAll('[data-rpa-delete]').forEach(function (btn) {
            btn.addEventListener('click', function () { deleteNode(parseInt(this.getAttribute('data-rpa-delete'), 10)); });
        });

        syncNodeSelectDropdown();
    }

    function buildNodeRow(node) {
        var statusHtml = node.is_active
            ? '<span class="rpa-status-badge rpa-status-active"><span class="mdi mdi-circle" style="font-size:8px;"></span> Active</span>'
            : '<span class="rpa-status-badge rpa-status-inactive"><span class="mdi mdi-circle-outline" style="font-size:8px;"></span> Inactive</span>';

        var toggleTitle = node.is_active ? 'Deactivate' : 'Activate';
        var toggleIcon  = node.is_active ? 'mdi-toggle-switch' : 'mdi-toggle-switch-off-outline';

        var screenHtml = node.screen_url
            ? '<div style="font-size:10px; color:var(--dashboard-text-secondary,#6D7784); margin-top:2px;">'
              + '<span class="mdi mdi-monitor-screenshot" style="font-size:11px;"></span> ' + esc(node.screen_url) + '</div>'
            : '<div style="font-size:10px; color:rgba(0,0,0,.3); margin-top:2px; font-style:italic;">no screen url</div>';

        return '<td style="color:var(--dashboard-text-secondary,#6D7784); font-size:12px;">#' + node.id + '</td>'
            + '<td><strong style="font-size:13px;">' + esc(node.name) + '</strong></td>'
            + '<td><div class="rpa-node-url">' + esc(node.url) + '</div>' + screenHtml + '</td>'
            + '<td>' + statusHtml + '</td>'
            + '<td><div class="rpa-node-actions">'
            + '<button class="rpa-btn-icon primary" title="Edit" data-rpa-edit="' + node.id + '"><span class="mdi mdi-pencil-outline"></span></button>'
            + '<button class="rpa-btn-icon" title="' + toggleTitle + '" data-rpa-toggle="' + node.id + '"><span class="mdi ' + toggleIcon + '"></span></button>'
            + '<button class="rpa-btn-icon danger" title="Delete" data-rpa-delete="' + node.id + '"><span class="mdi mdi-delete-outline"></span></button>'
            + '</div></td>';
    }

    function startEditNode(nodeId) {
        var node = farmNodes.find(function (n) { return n.id === nodeId; });
        if (!node) return;

        // Cancel any other open edit rows
        document.querySelectorAll('tr.editing').forEach(function (tr) {
            var id = parseInt(tr.id.replace('rpa-node-row-', ''), 10);
            var n  = farmNodes.find(function (nn) { return nn.id === id; });
            if (n) tr.innerHTML = buildNodeRow(n);
            tr.classList.remove('editing');
        });

        var tr = document.getElementById('rpa-node-row-' + nodeId);
        if (!tr) return;
        tr.classList.add('editing');
        tr.className = tr.className + ' rpa-edit-row';

        tr.innerHTML = '<td colspan="5">'
            + '<div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">'
            + '<div style="flex:1; min-width:120px;">'
            + '<label style="font-size:11px; font-weight:600; display:block; margin-bottom:3px;">Name</label>'
            + '<input type="text" id="rpa-edit-name-' + nodeId + '" value="' + esc(node.name) + '" style="width:100%;">'
            + '</div>'
            + '<div style="flex:2; min-width:180px;">'
            + '<label style="font-size:11px; font-weight:600; display:block; margin-bottom:3px;">API URL</label>'
            + '<input type="url" id="rpa-edit-url-' + nodeId + '" value="' + esc(node.url) + '" style="width:100%;">'
            + '</div>'
            + '<div style="flex:2; min-width:180px;">'
            + '<label style="font-size:11px; font-weight:600; display:block; margin-bottom:3px;">Screen URL <span style="font-weight:400; opacity:.6;">(optional)</span></label>'
            + '<input type="url" id="rpa-edit-screen-url-' + nodeId + '" value="' + esc(node.screen_url || '') + '" placeholder="https://screens.example.com" style="width:100%;">'
            + '</div>'
            + '<div style="display:flex; gap:6px; align-self:flex-end; padding-bottom:1px;">'
            + '<button class="dashboard-button" id="rpa-edit-save-' + nodeId + '" style="font-size:12px; padding:7px 12px;">'
            + '<span class="mdi mdi-content-save-outline"></span> Save'
            + '</button>'
            + '<button class="dashboard-button dashboard-button--secondary" id="rpa-edit-cancel-' + nodeId + '" style="font-size:12px; padding:7px 12px;">Cancel</button>'
            + '</div>'
            + '</div>'
            + '<div id="rpa-edit-msg-' + nodeId + '" style="font-size:12px; margin-top:6px;"></div>'
            + '</td>';

        var saveBtn = document.getElementById('rpa-edit-save-' + nodeId);
        if (saveBtn) {
            saveBtn.addEventListener('click', function () { saveEditNode(nodeId); });
        }
        var cancelBtn = document.getElementById('rpa-edit-cancel-' + nodeId);
        if (cancelBtn) {
            cancelBtn.addEventListener('click', function () {
                var t = document.getElementById('rpa-node-row-' + nodeId);
                if (t) { t.innerHTML = buildNodeRow(node); t.className = t.className.replace('rpa-edit-row', '').replace('editing', '').trim(); }
                rebindTableButtons();
            });
        }
    }

    function saveEditNode(nodeId) {
        var nameEl      = document.getElementById('rpa-edit-name-' + nodeId);
        var urlEl       = document.getElementById('rpa-edit-url-' + nodeId);
        var screenUrlEl = document.getElementById('rpa-edit-screen-url-' + nodeId);
        var msgEl       = document.getElementById('rpa-edit-msg-' + nodeId);

        var name      = nameEl      ? nameEl.value.trim()      : '';
        var url       = urlEl       ? urlEl.value.trim()       : '';
        var screenUrl = screenUrlEl ? screenUrlEl.value.trim() : '';

        if (!name) { if (msgEl) msgEl.innerHTML = '<span style="color:#DC2626;">Name is required.</span>'; return; }
        if (!url)  { if (msgEl) msgEl.innerHTML = '<span style="color:#DC2626;">URL is required.</span>'; return; }

        var saveBtn = document.getElementById('rpa-edit-save-' + nodeId);
        if (saveBtn) saveBtn.disabled = true;

        post(NODES_API, { action: 'update', id: nodeId, name: name, url: url, screen_url: screenUrl })
            .then(function (resp) {
                if (saveBtn) saveBtn.disabled = false;
                if (resp && resp.success && resp.node) {
                    var idx = farmNodes.findIndex(function (n) { return n.id === nodeId; });
                    if (idx !== -1) farmNodes[idx] = resp.node;
                    renderNodesTable();
                } else {
                    if (msgEl) msgEl.innerHTML = '<span style="color:#DC2626;">' + esc((resp && resp.message) || 'Error') + '</span>';
                }
            })
            .catch(function (e) {
                if (saveBtn) saveBtn.disabled = false;
                if (msgEl) msgEl.innerHTML = '<span style="color:#DC2626;">' + esc(e.message || String(e)) + '</span>';
            });
    }

    function toggleNodeActive(nodeId) {
        var node = farmNodes.find(function (n) { return n.id === nodeId; });
        if (!node) return;

        post(NODES_API, { action: 'update', id: nodeId, name: node.name, url: node.url, screen_url: node.screen_url || '', is_active: node.is_active ? 0 : 1 })
            .then(function (resp) {
                if (resp && resp.success && resp.node) {
                    var idx = farmNodes.findIndex(function (n) { return n.id === nodeId; });
                    if (idx !== -1) farmNodes[idx] = resp.node;
                    renderNodesTable();
                }
            });
    }

    function deleteNode(nodeId) {
        var msg = (I18N && I18N.confirm_delete_node) ? I18N.confirm_delete_node : 'Delete this farm node?';
        if (!confirm(msg)) return;

        post(NODES_API, { action: 'delete', id: nodeId })
            .then(function (resp) {
                if (resp && resp.success) {
                    farmNodes = farmNodes.filter(function (n) { return n.id !== nodeId; });
                    renderNodesTable();
                } else {
                    alert((resp && resp.message) || 'Error deleting node.');
                }
            });
    }

    function rebindTableButtons() {
        var tbody = document.getElementById('rpa-nodes-tbody');
        if (!tbody) return;
        tbody.querySelectorAll('[data-rpa-edit]').forEach(function (btn) {
            btn.addEventListener('click', function () { startEditNode(parseInt(this.getAttribute('data-rpa-edit'), 10)); });
        });
        tbody.querySelectorAll('[data-rpa-toggle]').forEach(function (btn) {
            btn.addEventListener('click', function () { toggleNodeActive(parseInt(this.getAttribute('data-rpa-toggle'), 10)); });
        });
        tbody.querySelectorAll('[data-rpa-delete]').forEach(function (btn) {
            btn.addEventListener('click', function () { deleteNode(parseInt(this.getAttribute('data-rpa-delete'), 10)); });
        });
    }

    function initAddNodeForm() {
        var btnShow   = document.getElementById('btn-show-add-node');
        var btnCancel = document.getElementById('btn-cancel-add-node');
        var btnSave   = document.getElementById('btn-save-new-node');
        var form      = document.getElementById('rpa-add-node-form');

        if (btnShow) {
            btnShow.addEventListener('click', function () {
                if (form) {
                    form.classList.toggle('show');
                    if (form.classList.contains('show')) {
                        var nameEl = document.getElementById('rpa-new-node-name');
                        if (nameEl) nameEl.focus();
                    }
                }
            });
        }

        if (btnCancel) {
            btnCancel.addEventListener('click', function () {
                if (form) form.classList.remove('show');
                clearAddForm();
            });
        }

        if (btnSave) {
            btnSave.addEventListener('click', function () {
                var name      = (document.getElementById('rpa-new-node-name') || {}).value || '';
                var url       = (document.getElementById('rpa-new-node-url') || {}).value || '';
                var screenUrl = (document.getElementById('rpa-new-node-screen-url') || {}).value || '';
                var isActive  = (document.getElementById('rpa-new-node-active') || {}).checked ? 1 : 0;
                var msgEl     = document.getElementById('rpa-add-node-msg');

                name      = name.trim();
                url       = url.trim();
                screenUrl = screenUrl.trim();

                if (!name) { if (msgEl) msgEl.innerHTML = '<span style="color:#DC2626;">' + (I18N.no_name || 'Name required.') + '</span>'; return; }
                if (!url)  { if (msgEl) msgEl.innerHTML = '<span style="color:#DC2626;">' + (I18N.no_url  || 'URL required.')  + '</span>'; return; }

                btnSave.disabled = true;
                setSpinner('spinner-add-node', true);

                post(NODES_API, { action: 'create', name: name, url: url, screen_url: screenUrl, is_active: isActive })
                    .then(function (resp) {
                        btnSave.disabled = false;
                        setSpinner('spinner-add-node', false);
                        if (resp && resp.success && resp.node) {
                            farmNodes.push(resp.node);
                            renderNodesTable();
                            if (form) form.classList.remove('show');
                            clearAddForm();
                            if (msgEl) msgEl.innerHTML = '';
                        } else {
                            if (msgEl) msgEl.innerHTML = '<span style="color:#DC2626;">' + esc((resp && resp.message) || 'Error') + '</span>';
                        }
                    })
                    .catch(function (e) {
                        btnSave.disabled = false;
                        setSpinner('spinner-add-node', false);
                        if (msgEl) msgEl.innerHTML = '<span style="color:#DC2626;">' + esc(e.message || String(e)) + '</span>';
                    });
            });
        }
    }

    function clearAddForm() {
        var els = ['rpa-new-node-name', 'rpa-new-node-url', 'rpa-new-node-screen-url'];
        els.forEach(function (id) { var el = document.getElementById(id); if (el) el.value = ''; });
        var active = document.getElementById('rpa-new-node-active');
        if (active) active.checked = true;
        var msg = document.getElementById('rpa-add-node-msg');
        if (msg) msg.innerHTML = '';
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Device Manager tab — farm node dropdown sync
    // ─────────────────────────────────────────────────────────────────────────
    function syncNodeSelectDropdown() {
        var sel     = document.getElementById('rpa-farm-node-select');
        var noNodes = document.getElementById('rpa-dm-no-nodes');
        if (!sel) return;

        var active = farmNodes.filter(function (n) { return n.is_active; });

        if (active.length === 0) {
            sel.innerHTML = '<option value="">' + '— No active nodes —' + '</option>';
            if (noNodes) noNodes.style.display = 'flex';
        } else {
            if (noNodes) noNodes.style.display = 'none';
            var prevVal = sel.value;
            sel.innerHTML = '<option value="">— Select a farm node —</option>';
            active.forEach(function (n) {
                var opt = document.createElement('option');
                opt.value = n.id;
                opt.textContent = '#' + n.id + ' — ' + n.name;
                if (String(n.id) === String(prevVal)) opt.selected = true;
                sel.appendChild(opt);
            });
        }
    }

    function getSelectedNodeId() {
        var sel = document.getElementById('rpa-farm-node-select');
        return sel ? parseInt(sel.value, 10) || 0 : 0;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Searchable user picker
    // ─────────────────────────────────────────────────────────────────────────
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
            if (!q) return esc(text);
            var escapedText = esc(text);
            var escapedQ    = q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            return escapedText.replace(new RegExp('(' + escapedQ + ')', 'gi'), '<strong>$1</strong>');
        }

        function render(q) {
            q = (q || '').toLowerCase();
            var matches = users.filter(function (u) {
                if (!q) return true;
                return u.username.toLowerCase().indexOf(q) !== -1
                    || (u.email || '').toLowerCase().indexOf(q) !== -1
                    || String(u.id).indexOf(q) !== -1;
            });

            if (matches.length === 0) {
                dropdown.innerHTML = '<div style="padding:10px 12px; color:#999; font-size:12px;">No users found</div>';
                dropdown.style.display = 'block';
                activeIdx = -1;
                return;
            }

            var html = '';
            matches.slice(0, 60).forEach(function (u, i) {
                html += '<div class="rpa-user-opt" data-id="' + u.id + '" data-index="' + i + '"'
                    + ' style="padding:8px 12px; cursor:pointer; font-size:13px; transition:background .1s;">'
                    + highlight(label(u), q) + '</div>';
            });
            dropdown.innerHTML = html;
            dropdown.style.display = 'block';
            activeIdx = -1;

            dropdown.querySelectorAll('.rpa-user-opt').forEach(function (opt) {
                opt.addEventListener('mouseenter', function () {
                    clearActive(); this.style.background = 'rgba(59,130,246,.1)';
                    activeIdx = parseInt(this.getAttribute('data-index'), 10);
                });
                opt.addEventListener('mouseleave', function () { this.style.background = ''; });
                opt.addEventListener('mousedown', function (e) {
                    e.preventDefault();
                    selectUser(this.getAttribute('data-id'));
                });
            });
        }

        function clearActive() {
            dropdown.querySelectorAll('.rpa-user-opt').forEach(function (o) { o.style.background = ''; });
        }

        function setActive(idx) {
            var opts = dropdown.querySelectorAll('.rpa-user-opt');
            clearActive();
            if (opts[idx]) { opts[idx].style.background = 'rgba(59,130,246,.1)'; opts[idx].scrollIntoView({ block: 'nearest' }); }
        }

        function selectUser(id) {
            id = parseInt(id, 10);
            hidden.value = id;
            var u = users.find(function (u) { return u.id === id; });
            if (u) input.value = label(u);
            dropdown.style.display = 'none';
            input.style.borderColor = 'rgba(59,130,246,.5)';
        }

        input.addEventListener('focus', function () { render(hidden.value ? '' : input.value); });
        input.addEventListener('input', function () { hidden.value = ''; input.style.borderColor = 'rgba(0,0,0,.15)'; render(this.value); });
        input.addEventListener('keydown', function (e) {
            var opts = dropdown.querySelectorAll('.rpa-user-opt');
            if (!opts.length) return;
            if (e.key === 'ArrowDown') { e.preventDefault(); activeIdx = activeIdx < opts.length - 1 ? activeIdx + 1 : 0; setActive(activeIdx); }
            else if (e.key === 'ArrowUp')  { e.preventDefault(); activeIdx = activeIdx > 0 ? activeIdx - 1 : opts.length - 1; setActive(activeIdx); }
            else if (e.key === 'Enter')    { e.preventDefault(); if (activeIdx >= 0 && opts[activeIdx]) selectUser(opts[activeIdx].getAttribute('data-id')); }
            else if (e.key === 'Escape')   { dropdown.style.display = 'none'; }
        });

        document.addEventListener('click', function (e) {
            if (!e.target.closest('#rpa-user-picker')) dropdown.style.display = 'none';
        });
    }

    function getSelectedUserId() {
        var el = document.getElementById('rpa-admin-user');
        return el ? parseInt(el.value, 10) || 0 : 0;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Device Manager result panel
    // ─────────────────────────────────────────────────────────────────────────
    function updateResult(data) {
        var pre    = document.getElementById('rpa-devices-result');
        var status = document.getElementById('rpa-result-status');
        if (!pre) return;

        var ok = data && data.success;
        pre.className = 'rpa-result-pre ' + (ok ? 'success-bg' : 'error-bg');

        if (status) {
            status.innerHTML = ok
                ? '<span style="color:#059669; font-size:12px; font-weight:600;"><span class="mdi mdi-check-circle-outline"></span> ' + (data.message || 'Success') + '</span>'
                : '<span style="color:#DC2626; font-size:12px; font-weight:600;"><span class="mdi mdi-alert-circle-outline"></span> ' + esc((data && data.message) || 'Error') + '</span>';
        }

        try { pre.textContent = JSON.stringify(data, null, 2); }
        catch (e) { pre.textContent = String(data || ''); }
    }

    function initDeviceManager() {
        var btnUserDevs = document.getElementById('btn-rpa-list-user-devices');
        var btnAvail    = document.getElementById('btn-rpa-list-available');
        var btnAssign   = document.getElementById('btn-rpa-assign');
        var btnCreate   = document.getElementById('btn-rpa-create-device');
        var btnDelete   = document.getElementById('btn-rpa-delete-device');
        var btnClear    = document.getElementById('btn-rpa-clear-result');

        if (btnClear) {
            btnClear.addEventListener('click', function () {
                var pre = document.getElementById('rpa-devices-result');
                var st  = document.getElementById('rpa-result-status');
                if (pre) { pre.textContent = 'Results will appear here after an action.'; pre.className = 'rpa-result-pre'; }
                if (st)  st.innerHTML = '';
            });
        }

        function requireNode() {
            var id = getSelectedNodeId();
            if (!id) { alert(I18N.select_node || 'Please select a farm node first.'); return 0; }
            return id;
        }

        function requireUser() {
            var id = getSelectedUserId();
            if (!id) { alert(I18N.select_user || 'Please select a user first.'); return 0; }
            return id;
        }

        function exec(payload) {
            return post(DEVICES_API, payload)
                .then(updateResult)
                .catch(function (e) { updateResult({ success: false, message: e.message || String(e) }); });
        }

        if (btnUserDevs) {
            btnUserDevs.addEventListener('click', function () {
                var nodeId = requireNode(); if (!nodeId) return;
                var uid    = requireUser(); if (!uid)    return;
                exec({ action: 'list_user_devices', farm_node_id: nodeId, user_id: uid });
            });
        }

        if (btnAvail) {
            btnAvail.addEventListener('click', function () {
                var nodeId = requireNode(); if (!nodeId) return;
                exec({ action: 'list_available', farm_node_id: nodeId });
            });
        }

        if (btnAssign) {
            btnAssign.addEventListener('click', function () {
                var nodeId = requireNode(); if (!nodeId) return;
                var uid    = requireUser(); if (!uid)    return;
                var countEl = document.getElementById('rpa-assign-count');
                var count   = countEl ? parseInt(countEl.value, 10) || 1 : 1;
                if (count < 1) count = 1;
                exec({ action: 'assign_devices', farm_node_id: nodeId, user_id: uid, count: count });
            });
        }

        if (btnCreate) {
            btnCreate.addEventListener('click', function () {
                var nodeId = requireNode(); if (!nodeId) return;
                var devId  = ((document.getElementById('rpa-create-device-id') || {}).value || '').trim();
                var name   = ((document.getElementById('rpa-create-device-name') || {}).value || '').trim();
                if (!devId || !name) { alert(I18N.fill_device || 'Please fill device_id and Name.'); return; }
                exec({ action: 'create_device', farm_node_id: nodeId, device_id: devId, name: name });
            });
        }

        if (btnDelete) {
            btnDelete.addEventListener('click', function () {
                var nodeId = requireNode(); if (!nodeId) return;
                var devId  = ((document.getElementById('rpa-delete-device-id') || {}).value || '').trim();
                if (!devId) { alert(I18N.enter_device || 'Please enter device_id to delete.'); return; }
                var confirmMsg = I18N.confirm_delete_device || 'Are you sure you want to delete/unassign this device?';
                if (!confirm(confirmMsg)) return;
                exec({ action: 'delete_device', farm_node_id: nodeId, device_id: devId });
            });
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HTML escaping
    // ─────────────────────────────────────────────────────────────────────────
    function esc(s) {
        return String(s || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Init
    // ─────────────────────────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
        initTabs();
        renderNodesTable();
        initAddNodeForm();
        initUserPicker();
        initDeviceManager();
    });

})();
