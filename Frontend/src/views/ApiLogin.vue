<script setup lang="ts">
import {useRouter} from 'vue-router';
import {ref} from 'vue';
import api from '@/lib/api';

document.title = 'Api Login'

const email = ref('test@example.com');
const password = ref('');
const loading = ref(false);
const error = ref('');
const router = useRouter();

async function login() {
    error.value = '';
    loading.value = true;
    try {
        const res = await api.post('/login', {
            email: email.value,
            password: password.value,
        });
        localStorage.setItem('token', res.data.token);
        router.push('/spddata');
    } catch (e: any) {
        error.value = e.response?.data?.errors?.email?.[0] ?? 'Login failed.';
    } finally {
        loading.value = false;
    }
}

</script>

<template>
    <div style="max-width: 360px; margin: 80px auto">
        <h2 style="font-weight : bold; font-size:41px;">Login</h2>
        <form @submit.prevent="login">
            <div><input v-model="email" type="email" placeholder="Enter Email" class="rounded border border-gray-400 px-2 py-2" /></div>
            <div><input v-model="password" type="password" placeholder="Enter Password" class="rounded border border-gray-400 px-2 py-2" /></div>
            <p v-if="error" style="color: red">{{error}}</p>
            <button type="submit" :disabled="loading" style="color: white; font-weight:bold; background-color:Green; padding : 12px; border-radius:8px">
                {{loading ? 'Loading' : 'Login'}}       
            </button>
        </form>
    </div>    

</template>