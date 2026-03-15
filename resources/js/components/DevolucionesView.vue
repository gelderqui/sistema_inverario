<template>
    <div>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h4 mb-0">Devoluciones</h2>
            <button class="btn btn-outline-brand" :disabled="loading || saving" @click="reloadAll">Actualizar</button>
        </div>

        <FormErrors :errors="errors" />

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <strong>Registrar devolucion</strong>
            </div>
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold">Venta</label>
                        <select v-model="selectedVentaId" class="form-select" @change="onVentaChange">
                            <option :value="null">Seleccione una venta</option>
                            <option v-for="v in ventasCatalogo" :key="v.id" :value="v.id">
                                {{ v.numero }} | {{ fmtDate(v.fecha_venta) }} | Q {{ Number(v.total || 0).toFixed(2) }}
                            </option>
                        </select>
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label fw-semibold">Fecha</label>
                        <input v-model="form.fecha" type="date" class="form-control">
                    </div>
                    <div class="col-12 col-md-3 d-grid">
                        <button class="btn btn-brand" :disabled="saving || !form.items.length" @click="guardar">Guardar devolucion</button>
                    </div>
                </div>

                <div class="table-responsive mt-3">
                    <table class="table table-sm align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Cantidad vendida</th>
                                <th>Ya devuelta</th>
                                <th>Disponible</th>
                                <th>Devolver</th>
                                <th>Motivo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="!detallesVenta.length">
                                <td colspan="6" class="text-center text-body-secondary py-3">Seleccione una venta para cargar productos.</td>
                            </tr>
                            <tr v-for="row in detallesVenta" :key="row.id">
                                <td>{{ row.producto?.nombre }}</td>
                                <td>{{ Number(row.cantidad || 0).toFixed(2) }}</td>
                                <td>{{ Number(row.cantidad_devuelta || 0).toFixed(2) }}</td>
                                <td class="fw-semibold">{{ Number(row.cantidad_disponible_devolucion || 0).toFixed(2) }}</td>
                                <td style="max-width: 120px;">
                                    <input v-model.number="row.devolver" type="number" min="0" step="0.0001" class="form-control form-control-sm">
                                </td>
                                <td>
                                    <input v-model="row.motivo" type="text" class="form-control form-control-sm" placeholder="Motivo">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white"><strong>Historial de devoluciones</strong></div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Venta</th>
                            <th>Fecha</th>
                            <th>Usuario</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!devoluciones.length">
                            <td colspan="5" class="text-center text-body-secondary py-3">Sin devoluciones registradas.</td>
                        </tr>
                        <tr v-for="d in devoluciones" :key="d.id">
                            <td>#{{ d.id }}</td>
                            <td>{{ d.venta?.numero || '-' }}</td>
                            <td>{{ fmtDate(d.fecha) }}</td>
                            <td>{{ d.usuario?.name || '-' }}</td>
                            <td class="fw-semibold">Q {{ Number(d.total || 0).toFixed(2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import axios from '@/bootstrap';
import FormErrors from '@/components/FormErrors.vue';

const loading = ref(false);
const saving = ref(false);
const errors = ref([]);
const ventasCatalogo = ref([]);
const devoluciones = ref([]);
const selectedVentaId = ref(null);

const form = ref({
    fecha: new Date().toISOString().slice(0, 10),
    items: [],
});

const detallesVenta = computed(() => {
    const venta = ventasCatalogo.value.find((v) => v.id === Number(selectedVentaId.value));
    return venta?.detalles ?? [];
});

onMounted(reloadAll);

async function reloadAll() {
    loading.value = true;
    try {
        const [catRes, listRes] = await Promise.all([
            axios.get('/ventas/devoluciones/get/catalogs'),
            axios.get('/ventas/devoluciones/get'),
        ]);
        ventasCatalogo.value = catRes?.data?.data?.ventas ?? [];
        devoluciones.value = listRes?.data?.data ?? [];
    } finally {
        loading.value = false;
    }
}

function onVentaChange() {
    for (const row of detallesVenta.value) {
        row.devolver = 0;
        row.motivo = '';
    }
}

async function guardar() {
    errors.value = [];
    saving.value = true;
    try {
        const items = detallesVenta.value
            .filter((row) => Number(row.devolver || 0) > 0)
            .map((row) => ({
                venta_detalle_id: row.id,
                cantidad: Number(row.devolver || 0),
                motivo: row.motivo || null,
            }));

        if (!items.length) {
            errors.value = ['Ingrese al menos un producto con cantidad a devolver.'];
            return;
        }

        await axios.post('/ventas/devoluciones/store', {
            venta_id: Number(selectedVentaId.value),
            fecha: form.value.fecha,
            items,
        });

        selectedVentaId.value = null;
        form.value.items = [];
        await reloadAll();
    } catch (error) {
        const backend = error.response?.data?.errors;
        errors.value = backend ? Object.values(backend).flat() : [error.response?.data?.message ?? 'No se pudo guardar la devolucion.'];
    } finally {
        saving.value = false;
    }
}

function fmtDate(value) {
    if (!value) return '-';
    return new Date(value).toLocaleString('es-GT');
}
</script>
