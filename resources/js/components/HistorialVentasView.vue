<template>
    <div>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h4 mb-0">Historial de ventas</h2>
            <button class="btn btn-outline-brand" :disabled="loading" @click="load">Actualizar</button>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="thead-brand">
                        <tr>
                            <th>Numero</th>
                            <th>Cliente</th>
                            <th>Fecha</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Metodo</th>
                            <th>Creado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!ventas.length">
                            <td colspan="7" class="text-center text-body-secondary py-4">Sin ventas registradas.</td>
                        </tr>
                        <tr v-for="venta in ventas" :key="venta.id">
                            <td><code>{{ venta.numero }}</code></td>
                            <td>{{ venta.cliente?.nombre ?? 'Consumidor final' }}</td>
                            <td>{{ fmtDate(venta.fecha_venta) }}</td>
                            <td>{{ venta.detalles_count ?? 0 }}</td>
                            <td class="fw-semibold">Q {{ Number(venta.total || 0).toFixed(2) }}</td>
                            <td class="text-uppercase">{{ venta.metodo_pago }}</td>
                            <td class="text-body-secondary small">{{ fmtDate(venta.created_at) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import axios from '@/bootstrap';

const loading = ref(false);
const ventas = ref([]);

onMounted(load);

async function load() {
    loading.value = true;
    try {
        const { data } = await axios.get('/ventas/historial/get');
        ventas.value = data?.data ?? [];
    } finally {
        loading.value = false;
    }
}

function fmtDate(value) {
    if (!value) return '-';
    return new Date(value).toLocaleString('es-GT');
}
</script>
