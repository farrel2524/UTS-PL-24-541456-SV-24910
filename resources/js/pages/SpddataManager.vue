<script setup lang="ts">
import {Head, router} from '@inertiajs/vue3';
import {onMounted, reactive, ref} from 'vue';
import api from '@/lib/api';

interface spddata {
    id: number;
    Username: string;
    Time: string;
    Difficulty: string;
}

const items = ref<spddata[]>([]);
const editingId = ref<number | null>(null);
const errors = ref<Record<string, string[]>>({});
const form = reactive({Username: '', Time: '', Difficulty: ''});

async function load() {
    const res = await api.get('/spddatas');
    items.value=res.data.data ?? res.data;
}

function resetForm() {
    form.Username = '';
    form.Time = '';
    form.Difficulty = '';
    editingId.value = null;
    errors.value = {};
    
}


async function save() {
    errors.value = {};
    try {
        if (editingId.value){
            await api.put(`/spddatas/${editingId.value}`, form);
        } else {
            await api.post('/spddatas', form);
        }
        resetForm();
        await load();
    } catch (e: any) {
        if (e.response?.status === 422) {
            errors.value = e.response.data.errors;
        }
    }
}

function edit(item: Spddata) {
    editingId.value = item.id;
    form.Username = item.Username;
    form.Time = item.Time;
    form.Difficulty = item.Difficulty;
}

async function remove(id: number) {
    if (!confirm('Hapus data?')) return;
    await api.delete(`/spddatas/${id}`);
    await load();
}

async function logout() {
    try {
        await api.post('/logout');
    } finally {
        localStorage.removeItem('token');
        router.visit('/api-login');
    }
}

onMounted(() => {
    if (!localStorage.getItem('token')) {
        router.visit('/api-login');
        return;
    }
    load();
});

</script>

<template>
    <Head title="speedrun data" />
    <div>
        <div >
            <h2 style="font-size: 21px; font-weight:bold;">Speedrun data</h2>
            <button @click="logout" class="rounded border border-gray-400 px-2 py-2">logout</button>
        </div>

        <form @submit.prevent="save">
            <h4 style="font-size: 21px; font-weight:bold;">{{editingId ? `Edit #${editingId}` : 'Add data'}} </h4>
            <div>
                <input v-model ="form.Username" class="rounded border border-gray-400 px-2 py-2" placeholder="Enter Username"/>
                <small v-if="errors.Username" style="color : blue">{{errors.Username[0]}}</small>
            </div>
            <div>
                <input v-model ="form.Time" class="rounded border border-gray-400 px-2 py-2" placeholder="Enter User Speedrun Time"/>
                <small v-if="errors.Time" style="color : blue">{{errors.Time[0]}}</small>
            </div>
            <div>
                <input v-model ="form.Difficulty" class="rounded border border-gray-400 px-2 py-2" placeholder="Easy/Normal/Hard/Nightmare"/>
                <small v-if="errors.Difficulty" style="color : blue">{{errors.Difficulty[0]}}</small>
            </div>
            <button type="submit" class="rounded border border-gray-400 px-2 py-2">{{editingId ? 'Update' : 'Save'}}</button>
            <button v-if="editingId" type="button" @click="resetForm">Cancel</button>
        </form>

        <table border="1" cellpadding="5" width="105%" class="rounded border border-gray-400 px-2 py-2">
            <thead>
                <tr class="rounded border border-gray-400 px-2 py-2"><th>ID</th><th>Username</th><th>Time</th><th>Difficulty</th></tr>
            </thead>
            <tbody>
                <tr v-for="item in items" :key="item.id">
                    <td class="rounded border border-gray-400 px-2 py-2">{{item.id}}</td>
                    <td class="rounded border border-gray-400 px-2 py-2">{{item.Username}}</td>
                    <td class="rounded border border-gray-400 px-2 py-2">{{item.Time}}</td>
                    <td class="rounded border border-gray-400 px-2 py-2">{{item.Difficulty}}</td>
                <td>
                    <button @click="edit(item)" class="rounded border border-gray-400 px-2 py-2">Edit</button>
                    <button @click="remove(item.id)" class="rounded border border-gray-400 px-2 py-2">Delete</button>
                    
                </td>
            </tr>
        </tbody>
    </table>
</div>   
</template>