import Vue from 'vue'
import VueRouter from 'vue-router'
import store from '../store'
import Login from '../components/Login.vue'
import Dashboard from '../components/Dashboard.vue'

Vue.use(VueRouter)

const routes = [
    {
        path: '/',
        redirect: '/dashboard'
    },
    {
        path: '/login',
        name: 'Login',
        component: Login,
        meta: { guest: true }
    },
    {
        path: '/dashboard',
        name: 'Dashboard',
        component: Dashboard,
        meta: { requiresAuth: true }
    }
]

const router = new VueRouter({
    mode: 'history',
    base: process.env.BASE_URL,
    routes
})

// Navigation guards
router.beforeEach((to, from, next) => {
    const isAuthenticated = store.getters.isAuthenticated

    if (to.matched.some(record => record.meta.requiresAuth)) {
        if (!isAuthenticated) {
            next('/login')
        } else {
            next()
        }
    } else if (to.matched.some(record => record.meta.guest)) {
        if (isAuthenticated) {
            next('/dashboard')
        } else {
            next()
        }
    } else {
        next()
    }
})

export default router
