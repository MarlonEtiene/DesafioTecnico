import Vue from 'vue'
import Vuex from 'vuex'
import axios from 'axios'

Vue.use(Vuex)

axios.defaults.baseURL = '/api'
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'

axios.interceptors.request.use(config => {
    const token = localStorage.getItem('token')
    if (token) {
        config.headers.Authorization = `Bearer ${ token }`
    }
    return config
})

axios.interceptors.response.use(
    response => response,
    error => {
        const isLoginPath = error.response.config.url.endsWith('/auth/login');

        if (error.response?.status === 401 && !isLoginPath) {
            store.commit('CLEAR_AUTH')
            if (window.location.pathname !== '/login') {
                window.location.href = '/login'
            }
        }
        return Promise.reject(error)
    }
)

export default new Vuex.Store({
    state: {
        token: localStorage.getItem('token') || null,
        user: JSON.parse(localStorage.getItem('user') || 'null'),
        tasks: [],
        companies: [],
        loading: false
    },

    getters: {
        isAuthenticated: state => !!state.token,
        user: state => state.user,
        tasks: state => state.tasks,
        companies: state => state.companies,
        loading: state => state.loading
    },

    mutations: {
        SET_TOKEN(state, token) {
            state.token = token
            localStorage.setItem('token', token)
        },

        SET_USER(state, user) {
            state.user = user
            localStorage.setItem('user', JSON.stringify(user))
        },

        SET_TASKS(state, tasks) {
            state.tasks = tasks
        },

        SET_COMPANIES(state, companies) {
            state.companies = companies
        },

        SET_LOADING(state, loading) {
            state.loading = loading
        },

        CLEAR_AUTH(state) {
            state.token = null
            state.user = null
            localStorage.removeItem('token')
            localStorage.removeItem('user')
        }
    },

    actions: {
        async login({ commit }, credentials) {
            try {
                const response = await axios.post('/auth/login', credentials)
                const { access_token, user } = response.data

                commit('SET_TOKEN', access_token)
                commit('SET_USER', user)

                return response.data
            } catch (error) {
                throw error
            }
        },

        async register({ commit }, userData) {
            try {
                const response = await axios.post('/auth/register', userData)
                const { access_token, user } = response.data

                commit('SET_TOKEN', access_token)
                commit('SET_USER', user)

                return response.data
            } catch (error) {
                throw error
            }
        },

        async logout({ commit }) {
            try {
                await axios.post('/auth/logout')
            } catch (error) {
                console.error('Logout error:', error)
            } finally {
                commit('CLEAR_AUTH')
                window.location.href = '/login'
            }
        },

        async fetchUser({ commit }) {
            try {
                const response = await axios.get('/auth/me')
                commit('SET_USER', response.data)
                return response.data
            } catch (error) {
                throw error
            }
        },

        async fetchCompanies({ commit }) { // Adicionado: ação para buscar as empresas
            try {
                const response = await axios.get('/companies');
                commit('SET_COMPANIES', response.data);
            } catch (error) {
                console.error('Erro ao carregar empresas:', error);
                throw error;
            }
        },

        async fetchTasks({ commit }, filters = {}) {
            try {
                commit('SET_LOADING', true)
                const params = new URLSearchParams()

                if (filters.status) params.append('status', filters.status)
                if (filters.priority) params.append('priority', filters.priority)

                const response = await axios.get(`/tasks?${ params.toString() }`)
                commit('SET_TASKS', response.data.data || response.data)
                return response.data
            } catch (error) {
                throw error
            } finally {
                commit('SET_LOADING', false)
            }
        },

        async createTask({ dispatch }, taskData) {
            try {
                const response = await axios.post('/tasks', taskData)
                await dispatch('fetchTasks')
                return response.data
            } catch (error) {
                throw error
            }
        },

        async updateTask({ dispatch }, { id, ...taskData }) {
            try {
                const response = await axios.put(`/tasks/${ id }`, taskData)
                await dispatch('fetchTasks')
                return response.data
            } catch (error) {
                throw error
            }
        },

        async deleteTask({ dispatch }, id) {
            try {
                await axios.delete(`/tasks/${ id }`)
                await dispatch('fetchTasks')
            } catch (error) {
                throw error
            }
        },

        async completeTask({ dispatch }, id) {
            try {
                const response = await axios.post(`/tasks/${ id }/complete`)
                await dispatch('fetchTasks')
                return response.data
            } catch (error) {
                throw error
            }
        },

        async exportTasks({ commit }, filters) {
            try {
                const response = await axios.post('/tasks/export', filters);
                return response.data.message;
            } catch (error) {
                console.error('Erro ao iniciar a exportação:', error);
                throw error;
            }
        },
    }
})
