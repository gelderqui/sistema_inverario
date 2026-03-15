<template>
    <div>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h4 mb-0">Compras</h2>
            <button class="btn btn-brand" :disabled="actionLocked" @click="openCreate">
                <FontAwesomeIcon icon="fa-solid fa-cart-plus" class="me-2" />
                Nueva compra
            </button>
        </div>

        <div v-if="loading" class="text-center py-5">
            <p class="text-body-secondary mb-0">Cargando información...</p>
        </div>

        <div v-else class="card border-0 shadow-sm mb-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="thead-brand">
                        <tr>
                            <th>Numero</th>
                            <th>Proveedor</th>
                            <th>Fecha</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th>Creado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!compras.length">
                            <td colspan="7" class="text-center text-body-secondary py-4">Sin compras registradas</td>
                        </tr>
                        <tr v-for="compra in compras" :key="compra.id">
                            <td><code>{{ compra.numero }}</code></td>
                            <td>{{ compra.proveedor?.nombre ?? '-' }}</td>
                            <td>{{ formatDate(compra.fecha_compra) }}</td>
                            <td>{{ compra.detalles_count ?? 0 }}</td>
                            <td class="fw-semibold">Q {{ Number(compra.total ?? 0).toFixed(2) }}</td>
                            <td><span class="badge text-bg-success text-uppercase">{{ compra.estado }}</span></td>
                            <td class="text-body-secondary small">{{ formatDate(compra.created_at) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div ref="formModalRef" class="modal fade" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header modal-header-brand">
                        <h5 class="modal-title">Nueva compra</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" />
                    </div>

                    <form novalidate @submit.prevent="save">
                        <div class="modal-body d-grid gap-3">
                            <FormErrors :errors="formErrors" />

                            <div class="row g-3">
                                <div class="col-12 col-md-4">
                                    <label class="form-label fw-semibold">Fecha compra *</label>
                                    <input v-model="form.fecha_compra" type="date" class="form-control" required>
                                </div>
                                <div class="col-12 col-md-8">
                                    <label class="form-label fw-semibold">Proveedor *</label>
                                    <input
                                        v-model="form.proveedor_query"
                                        list="prov-datalist"
                                        type="text"
                                        class="form-control"
                                        placeholder="Buscar proveedor..."
                                        autocomplete="off"
                                        @input="resolveProveedorFromQuery"
                                        @blur="resolveProveedorFromQuery"
                                    >
                                    <datalist id="prov-datalist">
                                        <option v-for="prov in catalogs.proveedores" :key="prov.id" :value="prov.nombre" />
                                    </datalist>
                                    <div v-if="form.proveedor_query && !form.proveedor_id" class="form-text text-danger">Proveedor no encontrado en el listado.</div>
                                </div>
                            </div>

                            <div>
                                <label class="form-label fw-semibold">Observaciones</label>
                                <textarea v-model="form.observaciones" rows="2" class="form-control" />
                            </div>

                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">Detalle de productos</h6>
                                <button type="button" class="btn btn-outline-brand btn-sm" :disabled="saving" @click="addItem">Agregar item</button>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-sm compra-items-table">
                                    <thead>
                                        <tr>
                                            <th style="min-width: 150px;">Categoria</th>
                                            <th style="min-width: 220px;">Producto</th>
                                            <th>Cantidad</th>
                                            <th>Medida</th>
                                            <th>Costo unitario</th>
                                            <th>Precio venta</th>
                                            <th>Caducidad</th>
                                            <th>Subtotal</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-if="!form.items.length">
                                            <td colspan="9" class="text-center text-body-secondary py-3">Agrega al menos un producto.</td>
                                        </tr>
                                        <tr v-for="(item, idx) in form.items" :key="idx">
                                            <td>
                                                <select v-model="item.categoria_id" class="form-select form-select-sm" @change="onCategoryChange(item)">
                                                    <option :value="null">Todas</option>
                                                    <option v-for="cat in catalogs.categorias" :key="cat.id" :value="cat.id">
                                                        {{ cat.nombre }}
                                                    </option>
                                                </select>
                                            </td>
                                            <td>
                                                <input
                                                    :id="`producto-search-${idx}`"
                                                    v-model="item.producto_query"
                                                    :list="`productos-list-${idx}`"
                                                    type="text"
                                                    class="form-control form-control-sm"
                                                    placeholder="Nombre, codigo barra o palabra clave"
                                                    @input="resolveProductFromQuery(item)"
                                                    @blur="resolveProductFromQuery(item)"
                                                >
                                                <datalist :id="`productos-list-${idx}`">
                                                    <option
                                                        v-for="prod in filteredProducts(item)"
                                                        :key="prod.id"
                                                        :value="productSearchText(prod)"
                                                    />
                                                </datalist>
                                                <div v-if="selectedProductName(item)" class="form-text compra-product-hint">
                                                    {{ selectedProductName(item) }}
                                                </div>
                                            </td>
                                            <td><input v-model.number="item.cantidad" type="number" step="0.0001" min="0.0001" class="form-control form-control-sm"></td>
                                            <td><input :value="item.unidad_medida || 'unidad'" type="text" class="form-control form-control-sm" readonly></td>
                                            <td><input v-model.number="item.costo_unitario" type="number" step="0.0001" min="0.0001" class="form-control form-control-sm"></td>
                                            <td><input v-model.number="item.precio_venta" type="number" step="0.0001" min="0" class="form-control form-control-sm"></td>
                                            <td><input v-model="item.fecha_caducidad" type="date" class="form-control form-control-sm"></td>
                                            <td class="fw-semibold">Q {{ itemSubtotal(item).toFixed(2) }}</td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-action-brand" :disabled="saving" @click="removeItem(idx)">
                                                    <FontAwesomeIcon icon="fa-solid fa-trash" class="icon-action-delete" />
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="compra-total-box d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="small text-body-secondary">Total estimado</div>
                                    <div class="h5 mb-0">Q {{ totalCompra.toFixed(2) }}</div>
                                </div>
                                <div class="text-end small text-body-secondary">El precio de venta puede modificarse por item.</div>
                            </div>

                            <div v-if="alerts.length" class="alert alert-warning mb-0">
                                <strong>Alertas de precio:</strong>
                                <ul class="compra-alert-list mt-2">
                                    <li v-for="(alert, index) in alerts" :key="index">{{ alert }}</li>
                                </ul>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-brand" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-brand" :disabled="saving">
                                {{ saving ? 'Guardando...' : 'Registrar compra' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Modal } from 'bootstrap';
import { computed, onMounted, ref } from 'vue';

import axios from '@/bootstrap';
import FormErrors from '@/components/FormErrors.vue';

const compras = ref([]);
const catalogs = ref({ categorias: [], proveedores: [], productos: [] });
const proveedorGeneralId = ref(null);
const proveedorGeneralNombre = ref('');
const loading = ref(true);
const saving = ref(false);
const formErrors = ref([]);
const alerts = ref([]);

const formModalRef = ref(null);
let formModal = null;

const emptyItem = () => ({
    categoria_id: null,
    producto_id: null,
    producto_query: '',
    cantidad: 1,
    unidad_medida: '',
    costo_unitario: 0,
    precio_venta: 0,
    fecha_caducidad: null,
});

const emptyForm = () => ({
    proveedor_id: proveedorGeneralId.value,
    proveedor_query: proveedorGeneralNombre.value,
    fecha_compra: new Date().toISOString().slice(0, 10),
    observaciones: '',
    items: [emptyItem()],
});

const form = ref(emptyForm());

const totalCompra = computed(() => form.value.items.reduce((sum, item) => sum + itemSubtotal(item), 0));
const actionLocked = computed(() => loading.value || saving.value);

onMounted(async () => {
    formModal = new Modal(formModalRef.value);
    await Promise.all([loadCompras(), loadCatalogs()]);
});

function itemSubtotal(item) {
    return Number(item.cantidad || 0) * Number(item.costo_unitario || 0);
}

async function loadCompras() {
    loading.value = true;
    try {
        const { data } = await axios.get('/compras/get');
        compras.value = data.data;
    } finally {
        loading.value = false;
    }
}

async function loadCatalogs() {
    const { data } = await axios.get('/compras/get/catalogs');
    catalogs.value = data.data;
    const generalId = data.data.proveedor_general_id;
    if (generalId) {
        const prov = data.data.proveedores.find((p) => p.id === generalId);
        if (prov) {
            proveedorGeneralId.value = prov.id;
            proveedorGeneralNombre.value = prov.nombre;
        }
    }
}

function openCreate() {
    form.value = emptyForm();
    formErrors.value = [];
    alerts.value = [];
    formModal.show();
}

function addItem() {
    form.value.items.push(emptyItem());
}

function filteredProducts(item) {
    if (!item.categoria_id) return catalogs.value.productos;
    return catalogs.value.productos.filter((prod) => prod.categoria_id === item.categoria_id);
}

function productSearchText(producto) {
    const parts = [producto.nombre];

    if (producto.codigo_barra) parts.push(`Barra: ${producto.codigo_barra}`);
    if (producto.palabras_clave) parts.push(`Clave: ${producto.palabras_clave}`);

    return parts.join(' | ');
}

function selectedProductName(item) {
    const producto = catalogs.value.productos.find((prod) => prod.id === item.producto_id);
    return producto ? `Producto seleccionado: ${producto.nombre}` : '';
}

function onCategoryChange(item) {
    item.producto_id = null;
    item.producto_query = '';
}

function resolveProveedorFromQuery() {
    const query = String(form.value.proveedor_query || '').trim().toLowerCase();
    if (!query) {
        form.value.proveedor_id = null;
        return;
    }
    const exact = catalogs.value.proveedores.find((p) => p.nombre.toLowerCase() === query);
    if (exact) {
        form.value.proveedor_id = exact.id;
        return;
    }
    const matches = catalogs.value.proveedores.filter((p) => p.nombre.toLowerCase().includes(query));
    if (matches.length === 1) {
        form.value.proveedor_id = matches[0].id;
        form.value.proveedor_query = matches[0].nombre;
    } else {
        form.value.proveedor_id = null;
    }
}

function resolveProductFromQuery(item) {
    const query = String(item.producto_query || '').trim().toLowerCase();
    const productos = filteredProducts(item);

    if (!query) {
        item.producto_id = null;
        return;
    }

    const exactByCode = productos.find((prod) => (prod.codigo_barra || '').toLowerCase() === query);
    if (exactByCode) {
        item.producto_id = exactByCode.id;
        syncItemMeasure(item);
        return;
    }

    const matches = productos.filter((prod) => {
        const nombre = (prod.nombre || '').toLowerCase();
        const codigoBarra = (prod.codigo_barra || '').toLowerCase();
        const palabrasClave = (prod.palabras_clave || '').toLowerCase();
        const searchable = productSearchText(prod).toLowerCase();

        return nombre.includes(query)
            || codigoBarra.includes(query)
            || palabrasClave.includes(query)
            || searchable.includes(query);
    });

    if (matches.length === 1) {
        item.producto_id = matches[0].id;
        syncItemMeasure(item);
    }
}

function syncItemMeasure(item) {
    const producto = catalogs.value.productos.find((prod) => prod.id === item.producto_id);
    if (!producto) return;

    const um = producto.unidad_medida;
    if (um && typeof um === 'object') {
        item.unidad_medida = `${um.nombre} (${um.abreviatura})`;
    } else {
        item.unidad_medida = um || '—';
    }
}

function removeItem(index) {
    form.value.items.splice(index, 1);
}

async function save() {
    if (!form.value.proveedor_id) {
        formErrors.value = ['Seleccione un proveedor válido de la lista.'];
        return;
    }
    saving.value = true;
    formErrors.value = [];
    alerts.value = [];
    try {
        const payload = {
            proveedor_id: form.value.proveedor_id,
            fecha_compra: form.value.fecha_compra,
            observaciones: form.value.observaciones || null,
            items: form.value.items.filter((item) => item.producto_id).map((item) => ({
                producto_id: item.producto_id,
                cantidad: Number(item.cantidad || 0),
                costo_unitario: Number(item.costo_unitario || 0),
                precio_venta: item.precio_venta === '' || item.precio_venta === null ? null : Number(item.precio_venta),
                fecha_caducidad: item.fecha_caducidad || null,
            })),
        };

        const { data } = await axios.post('/compras/store', payload);
        compras.value.unshift(data.data);
        alerts.value = data.alerts ?? [];
        formModal.hide();
    } catch (error) {
        const errors = error.response?.data?.errors;
        formErrors.value = errors ? Object.values(errors).flat() : [error.response?.data?.message ?? 'No se pudo registrar la compra.'];
    } finally {
        saving.value = false;
    }
}

function formatDate(value) {
    if (!value) return '-';
    return new Date(value).toLocaleDateString('es-GT');
}
</script>
