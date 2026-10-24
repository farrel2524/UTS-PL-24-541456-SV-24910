import { createRouter, createWebHistory } from 'vue-router';
import ApiLogin from '@/views/ApiLogin.vue';
import SpddataManager from '@/views/SpddataManager.vue';

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {path : '/', redirect: '/login'},
    {path : '/login', component: ApiLogin},
    {path : '/spddata', component: SpddataManager},
    

  ],
});

export default router;
