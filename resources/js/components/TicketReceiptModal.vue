<template>
    <div ref="modalRef" class="modal fade" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header modal-header-brand">
                    <h5 class="modal-title">{{ title }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" />
                </div>

                <div class="modal-body p-0">
                    <iframe
                        v-if="pdfUrl"
                        ref="iframeRef"
                        :src="pdfUrl"
                        title="Recibo PDF"
                        style="width: 100%; height: 72vh; border: 0;"
                    />
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-brand" @click="printPdf" :disabled="!pdfUrl">Imprimir</button>
                    <button type="button" class="btn btn-brand" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Modal } from 'bootstrap';
import { onMounted, ref } from 'vue';

const props = defineProps({
    title: {
        type: String,
        default: 'Recibo',
    },
});

const modalRef = ref(null);
const iframeRef = ref(null);
const pdfUrl = ref('');
let modal = null;

onMounted(() => {
    modal = new Modal(modalRef.value);
});

function open(url) {
    pdfUrl.value = url;
    modal?.show();
}

function close() {
    modal?.hide();
}

function printPdf() {
    if (!pdfUrl.value) return;

    const frame = iframeRef.value;
    if (frame?.contentWindow) {
        frame.contentWindow.focus();
        frame.contentWindow.print();
        return;
    }

    window.open(pdfUrl.value, '_blank', 'noopener');
}

defineExpose({
    open,
    close,
});
</script>
