<div id="purchase-quick-create-config" class="hidden"
    data-supplier-url="{{ route('compras.quick-supplier') }}"
    data-spare-url="{{ route('compras.quick-spare-part') }}">
</div>

{{-- Modal: proveedor --}}
<div id="modal-quick-supplier-purchase" class="hidden fixed inset-0 z-[110] flex items-center justify-center p-4" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-black/60 dark:bg-black/75" data-close-modal-supplier></div>
    <div class="relative w-full max-w-md rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-900 shadow-2xl p-6 max-h-[90vh] overflow-y-auto">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">Nuevo proveedor</h3>
        <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">Se guardará en el catálogo y podrá elegirlo en esta compra.</p>
        <div id="quick-supplier-errors" class="mb-3 text-sm text-red-600 dark:text-red-400 hidden"></div>
        <form id="form-quick-supplier-purchase" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">Nombre <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="quick-supplier-name" required class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">RUT</label>
                <input type="text" name="rut" id="quick-supplier-rut" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">Contacto</label>
                <input type="text" name="contact_name" id="quick-supplier-contact" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">Teléfono</label>
                    <input type="text" name="phone" id="quick-supplier-phone" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">Correo</label>
                    <input type="email" name="email" id="quick-supplier-email" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" class="px-4 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200" data-close-modal-supplier>Cancelar</button>
                <button type="submit" class="px-4 py-2 text-sm rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white" id="btn-submit-quick-supplier">Guardar</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal: repuesto --}}
<div id="modal-quick-spare-purchase" class="hidden fixed inset-0 z-[110] flex items-center justify-center p-4" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-black/60 dark:bg-black/75" data-close-modal-spare></div>
    <div class="relative w-full max-w-lg rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-900 shadow-2xl p-6 max-h-[90vh] overflow-y-auto">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">Nuevo repuesto</h3>
        <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">Se creará en bodega y se seleccionará en la línea actual.</p>
        <div id="quick-spare-errors" class="mb-3 text-sm text-red-600 dark:text-red-400 hidden"></div>
        <form id="form-quick-spare-purchase" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">Código <span class="text-red-500">*</span></label>
                <input type="text" name="code" id="quick-spare-code" required class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">Descripción <span class="text-red-500">*</span></label>
                <input type="text" name="description" id="quick-spare-description" required class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">Marca</label>
                <input type="text" name="brand" id="quick-spare-brand" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">Categoría <span class="text-red-500">*</span></label>
                <select name="category" id="quick-spare-category" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    @foreach(\App\Models\SparePart::CATEGORIES as $key => $label)
                        <option value="{{ $key }}" @if($key === 'spare_part') selected @endif>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">Precio referencia ($)</label>
                <input type="number" name="reference_price" id="quick-spare-ref-price" min="0" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" name="has_expiration" id="quick-spare-has-exp" value="1" class="rounded border-gray-300 dark:border-gray-600">
                <label for="quick-spare-has-exp" class="text-sm text-gray-700 dark:text-gray-300">Tiene vencimiento (lote)</label>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" class="px-4 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200" data-close-modal-spare>Cancelar</button>
                <button type="submit" class="px-4 py-2 text-sm rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white" id="btn-submit-quick-spare">Guardar</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const cfg = document.getElementById('purchase-quick-create-config');
    if (!cfg) return;

    const supplierUrl = cfg.dataset.supplierUrl;
    const spareUrl = cfg.dataset.spareUrl;
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    function showErrors(el, messages) {
        if (!el) return;
        if (!messages || !messages.length) {
            el.classList.add('hidden');
            el.textContent = '';
            return;
        }
        el.textContent = messages.join(' ');
        el.classList.remove('hidden');
    }

    const modalSupplier = document.getElementById('modal-quick-supplier-purchase');
    const modalSpare = document.getElementById('modal-quick-spare-purchase');
    let spareTargetSelect = null;

    document.querySelectorAll('[data-close-modal-supplier]').forEach(function(el) {
        el.addEventListener('click', function() {
            modalSupplier.classList.add('hidden');
        });
    });
    document.querySelectorAll('[data-close-modal-spare]').forEach(function(el) {
        el.addEventListener('click', function() {
            modalSpare.classList.add('hidden');
            spareTargetSelect = null;
        });
    });

    const btnSupplier = document.getElementById('btn-new-supplier-purchase');
    if (btnSupplier && modalSupplier) {
        btnSupplier.addEventListener('click', function() {
            document.getElementById('form-quick-supplier-purchase').reset();
            showErrors(document.getElementById('quick-supplier-errors'), []);
            modalSupplier.classList.remove('hidden');
        });
    }

    const tbody = document.getElementById('items-tbody');
    if (tbody && modalSpare) {
        tbody.addEventListener('click', function(e) {
            const btn = e.target.closest('.btn-quick-new-spare');
            if (!btn) return;
            const row = btn.closest('tr');
            spareTargetSelect = row ? row.querySelector('.item-spare-part') : null;
            document.getElementById('form-quick-spare-purchase').reset();
            const cat = document.getElementById('quick-spare-category');
            if (cat) cat.value = 'spare_part';
            document.getElementById('quick-spare-has-exp').checked = false;
            showErrors(document.getElementById('quick-spare-errors'), []);
            modalSpare.classList.remove('hidden');
        });
    }

    function appendSparePartOptions(part) {
        if (window.__purchaseSpareParts) {
            window.__purchaseSpareParts.push({ id: part.id, code: part.code, description: part.description });
        }
        document.querySelectorAll('.item-spare-part').forEach(function(sel) {
            const opt = document.createElement('option');
            opt.value = String(part.id);
            opt.textContent = part.code + ' — ' + part.description;
            sel.appendChild(opt);
        });
        if (spareTargetSelect) {
            spareTargetSelect.value = String(part.id);
        }
    }

    const formSupplier = document.getElementById('form-quick-supplier-purchase');
    if (formSupplier) {
        formSupplier.addEventListener('submit', function(e) {
            e.preventDefault();
            const errEl = document.getElementById('quick-supplier-errors');
            showErrors(errEl, []);
            const btn = document.getElementById('btn-submit-quick-supplier');
            btn.disabled = true;
            const fd = new FormData(formSupplier);
            const body = {
                name: fd.get('name'),
                rut: fd.get('rut') || null,
                contact_name: fd.get('contact_name') || null,
                phone: fd.get('phone') || null,
                email: fd.get('email') || null,
            };
            fetch(supplierUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(body),
            })
            .then(function(r) { return r.json().then(function(data) { return { ok: r.ok, status: r.status, data: data }; }); })
            .then(function(res) {
                btn.disabled = false;
                if (!res.ok) {
                    const msgs = [];
                    if (res.data && res.data.errors) {
                        Object.keys(res.data.errors).forEach(function(k) {
                            (res.data.errors[k] || []).forEach(function(m) { msgs.push(m); });
                        });
                    } else {
                        msgs.push(res.data.message || 'No se pudo crear el proveedor.');
                    }
                    showErrors(errEl, msgs);
                    return;
                }
                const sel = document.getElementById('supplier_id');
                if (sel && res.data.id) {
                    const opt = document.createElement('option');
                    opt.value = String(res.data.id);
                    opt.textContent = res.data.name;
                    sel.appendChild(opt);
                    sel.value = String(res.data.id);
                }
                modalSupplier.classList.add('hidden');
                if (window.Swal) Swal.fire({ icon: 'success', title: 'Proveedor creado', toast: true, timer: 2000, showConfirmButton: false, position: 'top-end' });
            })
            .catch(function() {
                btn.disabled = false;
                showErrors(errEl, ['Error de red. Intente de nuevo.']);
            });
        });
    }

    const formSpare = document.getElementById('form-quick-spare-purchase');
    if (formSpare) {
        formSpare.addEventListener('submit', function(e) {
            e.preventDefault();
            const errEl = document.getElementById('quick-spare-errors');
            showErrors(errEl, []);
            const btn = document.getElementById('btn-submit-quick-spare');
            btn.disabled = true;
            const refIn = document.getElementById('quick-spare-ref-price');
            let refPrice = null;
            if (refIn && refIn.value !== '') refPrice = parseInt(refIn.value, 10);
            const body = {
                code: document.getElementById('quick-spare-code').value,
                description: document.getElementById('quick-spare-description').value,
                brand: document.getElementById('quick-spare-brand').value || null,
                category: document.getElementById('quick-spare-category').value,
                reference_price: refPrice,
                has_expiration: document.getElementById('quick-spare-has-exp').checked,
            };
            fetch(spareUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(body),
            })
            .then(function(r) { return r.json().then(function(data) { return { ok: r.ok, data: data }; }); })
            .then(function(res) {
                btn.disabled = false;
                if (!res.ok) {
                    const msgs = [];
                    if (res.data && res.data.errors) {
                        Object.keys(res.data.errors).forEach(function(k) {
                            (res.data.errors[k] || []).forEach(function(m) { msgs.push(m); });
                        });
                    } else {
                        msgs.push(res.data.message || 'No se pudo crear el repuesto.');
                    }
                    showErrors(errEl, msgs);
                    return;
                }
                appendSparePartOptions(res.data);
                modalSpare.classList.add('hidden');
                spareTargetSelect = null;
                if (window.Swal) Swal.fire({ icon: 'success', title: 'Repuesto creado', toast: true, timer: 2000, showConfirmButton: false, position: 'top-end' });
            })
            .catch(function() {
                btn.disabled = false;
                showErrors(errEl, ['Error de red. Intente de nuevo.']);
            });
        });
    }
});
</script>
@endpush
