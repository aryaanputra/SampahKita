/* =====================================================================
   Bank Sampah Kali Laya Berseri
   assets/js/app.js - Interaksi tambahan (search, konfirmasi, form dinamis)
   ===================================================================== */

document.addEventListener('DOMContentLoaded', function () {

    /* -----------------------------------------------------------------
     * 1. Pencarian sederhana: saring baris tabel & item daftar
     * ----------------------------------------------------------------- */
    var searchInput = document.querySelector('#main-search');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            var term = this.value.toLowerCase().trim();
            var targets = document.querySelectorAll('table tbody tr, .transaction-item, .clean-list-item');
            targets.forEach(function (el) {
                var text = el.textContent.toLowerCase();
                el.style.display = (term === '' || text.indexOf(term) !== -1) ? '' : 'none';
            });
        });
    }

    /* -----------------------------------------------------------------
     * 2. Konfirmasi aksi (form dengan data-confirm)
     * ----------------------------------------------------------------- */
    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (ev) {
            if (!window.confirm(this.getAttribute('data-confirm'))) {
                ev.preventDefault();
            }
        });
    });

    /* -----------------------------------------------------------------
     * 3. Toggle visibilitas password (input-group)
     * ----------------------------------------------------------------- */
    document.querySelectorAll('.password-toggle-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var target = document.getElementById(this.getAttribute('data-target'));
            if (!target) { return; }
            var isPassword = target.type === 'password';
            target.type = isPassword ? 'text' : 'password';
            this.innerHTML = isPassword ? '<i class="bi bi-eye-slash"></i>' : '<i class="bi bi-eye"></i>';
        });
    });

    /* -----------------------------------------------------------------
     * 4. Form timbang dinamis: tambah/hapus baris jenis sampah
     * ----------------------------------------------------------------- */
    var itemsWrap = document.querySelector('#items-wrap');
    if (itemsWrap) {
        var tpl = document.querySelector('#item-row-template');

        function updateTotals() {
            var grand = 0;
            itemsWrap.querySelectorAll('.item-row').forEach(function (row) {
                var jenis = row.querySelector('select');
                var berat = row.querySelector('.input-berat');
                var harga = row.querySelector('.input-harga');
                var subEl = row.querySelector('.item-subtotal');
                var opt = jenis && jenis.options[jenis.selectedIndex];
                var price = opt ? parseFloat(opt.getAttribute('data-harga') || '0') : 0;
                if (harga) { harga.value = price ? price : ''; }
                var w = parseFloat(berat && berat.value ? berat.value : '0') || 0;
                var sub = w * price;
                grand += sub;
                if (subEl) { subEl.textContent = formatRupiah(sub); }
            });
            var grandEl = document.querySelector('#grand-total');
            if (grandEl) { grandEl.textContent = formatRupiah(grand); }
        }

        function formatRupiah(n) {
            return 'Rp ' + (Math.round(n) || 0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }

        function bindRow(row) {
            row.querySelectorAll('select, .input-berat').forEach(function (el) {
                el.addEventListener('input', updateTotals);
                el.addEventListener('change', updateTotals);
            });
            var removeBtn = row.querySelector('.item-row-remove');
            if (removeBtn) {
                removeBtn.addEventListener('click', function () {
                    var rows = itemsWrap.querySelectorAll('.item-row');
                    if (rows.length > 1) {
                        row.remove();
                        updateTotals();
                    }
                });
            }
        }

        itemsWrap.querySelectorAll('.item-row').forEach(bindRow);

        var addBtn = document.querySelector('#add-item');
        if (addBtn && tpl) {
            addBtn.addEventListener('click', function () {
                var clone = tpl.content.cloneNode(true);
                itemsWrap.appendChild(clone);
                bindRow(itemsWrap.querySelectorAll('.item-row')[itemsWrap.querySelectorAll('.item-row').length - 1]);
                updateTotals();
            });
        }

        updateTotals();
    }

    /* -----------------------------------------------------------------
     * 5. Flatpickr untuk input tanggal berkelas .flatpickr-date
     * ----------------------------------------------------------------- */
    if (window.flatpickr) {
        document.querySelectorAll('.flatpickr-date').forEach(function (el) {
            flatpickr(el, {
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'd M Y',
                allowInput: true
            });
        });
        document.querySelectorAll('.flatpickr-datetime').forEach(function (el) {
            flatpickr(el, {
                enableTime: true,
                dateFormat: 'Y-m-d H:i',
                altInput: true,
                altFormat: 'd M Y H:i',
                time_24hr: true,
                allowInput: true
            });
        });
    }

    /* -----------------------------------------------------------------
     * 6. Auto-submit filter (select dengan data-autosubmit)
     * ----------------------------------------------------------------- */
    document.querySelectorAll('select[data-autosubmit]').forEach(function (sel) {
        sel.addEventListener('change', function () {
            var form = this.closest('form');
            if (form) { form.submit(); }
        });
    });

    /* -----------------------------------------------------------------
     * 6b. Scroll halus ke tujuan (a/button dengan data-scroll)
     * ----------------------------------------------------------------- */
    document.querySelectorAll('[data-scroll]').forEach(function (el) {
        el.addEventListener('click', function (ev) {
            var target = document.querySelector(this.getAttribute('data-scroll'));
            if (target) {
                ev.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                var inp = target.querySelector('input, textarea');
                if (inp) { setTimeout(function () { inp.focus(); }, 400); }
            }
        });
    });

    /* -----------------------------------------------------------------
     * 6c. Pencarian lokal per-tabel (.table-local-search dalam .table-card-custom)
     * ----------------------------------------------------------------- */
    document.querySelectorAll('.table-local-search').forEach(function (inp) {
        inp.addEventListener('input', function () {
            var term = this.value.toLowerCase().trim();
            var card = this.closest('.table-card-custom') || this.closest('.card');
            if (!card) { return; }
            card.querySelectorAll('table tbody tr').forEach(function (row) {
                var text = row.textContent.toLowerCase();
                row.style.display = (term === '' || text.indexOf(term) !== -1) ? '' : 'none';
            });
        });
    });

    /* -----------------------------------------------------------------
     * 7. Pencetakan kuitansi
     * ----------------------------------------------------------------- */
    document.querySelectorAll('[data-print]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            window.print();
        });
    });

    /* -----------------------------------------------------------------
     * 8. Grafik (ApexCharts) dari data yang disuntik halaman PHP
     * ----------------------------------------------------------------- */
    var GREEN = '#0C5429';
    var LIME = '#6FBF2E';

    function hexToRgba(hex, a) {
        var h = hex.replace('#', '');
        if (h.length === 3) { h = h[0] + h[0] + h[1] + h[1] + h[2] + h[2]; }
        var n = parseInt(h, 16);
        return 'rgba(' + ((n >> 16) & 255) + ',' + ((n >> 8) & 255) + ',' + (n & 255) + ',' + a + ')';
    }

    if (window.ApexCharts) {
        var ApexCtor = window.ApexCharts;

        // Grafik saldo (single series, area)
        var saldoEl = document.querySelector('#saldo-chart');
        if (saldoEl && window.__saldoChart) {
            new ApexCtor(saldoEl, {
                chart: { type: 'area', height: 300, toolbar: { show: false }, zoom: { enabled: false } },
                series: [{ name: 'Saldo', data: window.__saldoChart.data }],
                labels: window.__saldoChart.labels,
                xaxis: { categories: window.__saldoChart.labels, labels: { style: { fontSize: '11px' } } },
                yaxis: { labels: { formatter: function (v) { return 'Rp ' + Math.round(v).toLocaleString('id-ID'); } } },
                stroke: { curve: 'smooth', width: 3 },
                colors: [GREEN],
                fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.35, opacityTo: 0.02 } },
                dataLabels: { enabled: false },
                grid: { borderColor: 'rgba(0,0,0,0.05)' },
                tooltip: { y: { formatter: function (v) { return 'Rp ' + Math.round(v).toLocaleString('id-ID'); } } }
            }).render();
        }

        // Grafik generik: window.__charts = [{ id, type, series, labels, stacked, horizontal }]
        (window.__charts || []).forEach(function (c) {
            var el = document.querySelector('#' + c.id);
            if (!el) { return; }
            new ApexCtor(el, {
                chart: {
                    type: c.type || 'bar',
                    height: c.height || 300,
                    stacked: !!c.stacked,
                    toolbar: { show: false },
                    zoom: { enabled: false },
                    stackedBar100: !!c.stacked100
                },
                series: c.series,
                labels: c.labels,
                plotOptions: {
                    bar: { horizontal: !!c.horizontal, borderRadius: 4, columnWidth: '55%' },
                    pie: { donut: { size: '65%' } }
                },
                dataLabels: c.type === 'donut' || c.type === 'pie' ? { enabled: true } : { enabled: false },
                stroke: c.type === 'bar' ? { show: false } : { width: 0 },
                xaxis: { categories: c.labels, labels: { style: { fontSize: '11px' } } },
                yaxis: c.horizontal
                    ? { labels: { formatter: function (v) { return c.money ? formatRupiahShort(v) : Math.round(v); } } }
                    : [{ labels: { formatter: function (v) { return c.money ? formatRupiahShort(v) : Math.round(v); } } }],
                colors: [GREEN, LIME, '#3b82f6', '#f59e0b', '#8b5cf6', '#ef4444'],
                fill: { opacity: 1 },
                legend: { position: 'bottom', fontSize: '12px' },
                grid: { borderColor: 'rgba(0,0,0,0.05)' },
                tooltip: {
                    y: c.money ? { formatter: function (v) { return formatRupiahShort(v); } } : {}
                }
            }).render();
        });
    }

    function formatRupiahShort(v) {
        return 'Rp ' + Math.round(v).toLocaleString('id-ID');
    }
});
