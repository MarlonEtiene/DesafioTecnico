<template>
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-lg p-3 mb-5 bg-white rounded">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">{{ isLogin ? 'Login' : 'Registrar' }}</h4>
                </div>
                <div class="card-body">
                    <form @submit.prevent="submitForm">
                        <div v-if="!isLogin">
                            <div class="form-check form-check-inline mb-3">
                                <input class="form-check-input" type="radio" id="newCompany" v-model="isNewCompany"
                                       :value="true">
                                <label class="form-check-label" for="newCompany">Criar nova empresa</label>
                            </div>
                            <div class="form-check form-check-inline mb-3">
                                <input class="form-check-input" type="radio" id="existingCompany" v-model="isNewCompany"
                                       :value="false">
                                <label class="form-check-label" for="existingCompany">Escolher empresa existente</label>
                            </div>
                        </div>

                        <div class="mb-3" v-if="!isLogin && isNewCompany">
                            <label class="form-label">Nome da Empresa</label>
                            <input
                                type="text"
                                class="form-control"
                                :class="{'is-invalid': validationErrors.company_name}"
                                v-model="form.company_name"
                                required
                            >
                            <div class="invalid-feedback" v-if="validationErrors.company_name">
                                {{ validationErrors.company_name[0] }}
                            </div>
                        </div>

                        <div class="mb-3" v-if="!isLogin && !isNewCompany">
                            <label class="form-label">Escolha a Empresa</label>
                            <select
                                class="form-control"
                                :class="{'is-invalid': validationErrors.company_id}"
                                v-model="form.company_id"
                                required
                            >
                                <option disabled value="">Selecione uma empresa</option>
                                <option v-for="company in companies" :key="company.id" :value="company.id">
                                    {{ company.name }}
                                </option>
                            </select>
                            <div class="invalid-feedback" v-if="validationErrors.company_id">
                                {{ validationErrors.company_id[0] }}
                            </div>
                        </div>

                        <div class="mb-3" v-if="!isLogin">
                            <label class="form-label">Nome</label>
                            <input
                                type="text"
                                class="form-control"
                                :class="{'is-invalid': validationErrors.name}"
                                v-model="form.name"
                                required
                            >
                            <div class="invalid-feedback" v-if="validationErrors.name">
                                {{ validationErrors.name[0] }}
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input
                                type="email"
                                class="form-control"
                                :class="{'is-invalid': validationErrors.email}"
                                v-model="form.email"
                                required
                            >
                            <div class="invalid-feedback" v-if="validationErrors.email">
                                {{ validationErrors.email[0] }}
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Senha</label>
                            <input
                                type="password"
                                class="form-control"
                                :class="{'is-invalid': validationErrors.password}"
                                v-model="form.password"
                                required
                            >
                            <div class="invalid-feedback" v-if="validationErrors.password">
                                {{ validationErrors.password[0] }}
                            </div>
                        </div>

                        <div class="mb-3" v-if="!isLogin">
                            <label class="form-label">Confirmar Senha</label>
                            <input
                                type="password"
                                class="form-control"
                                :class="{'is-invalid': validationErrors.password_confirmation}"
                                v-model="form.password_confirmation"
                                required
                            >
                            <div class="invalid-feedback" v-if="validationErrors.password_confirmation">
                                {{ validationErrors.password_confirmation[0] }}
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary" :disabled="loading">
                            {{ loading ? 'Carregando...' : (isLogin ? 'Login' : 'Register') }}
                        </button>
                    </form>

                    <div class="mt-3 text-center">
                        <a href="#" @click.prevent="toggleMode">
                            {{ isLogin ? 'Precisa de uma conta? Registre-se' : 'Tem uma conta? Login' }}
                        </a>
                    </div>

                    <div v-if="error" class="alert alert-danger mt-3">
                        {{ error }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { mapActions, mapState } from 'vuex';

export default {
    name: 'Login',
    data() {
        return {
            isLogin: true,
            loading: false,
            error: '',
            isNewCompany: true,
            form: {
                name: '',
                email: '',
                password: '',
                password_confirmation: '',
                company_name: '',
                company_id: null
            },
            validationErrors: {}
        };
    },
    computed: {
        ...mapState(['companies'])
    },
    watch: {
        isNewCompany(newValue) {
            if (newValue) {
                this.form.company_id = null;
            } else {
                this.form.company_name = '';
            }
        }
    },
    methods: {
        ...mapActions(['login', 'register', 'fetchCompanies']),

        async submitForm() {
            this.loading = true;
            this.error = '';
            this.validationErrors = {};

            try {
                if (this.isLogin) {
                    const loginData = {
                        email: this.form.email,
                        password: this.form.password
                    };
                    await this.login(loginData);
                } else {
                    const payload = {
                        name: this.form.name,
                        email: this.form.email,
                        password: this.form.password,
                        password_confirmation: this.form.password_confirmation,
                    };

                    if (this.isNewCompany) {
                        payload.company_name = this.form.company_name;
                    } else {
                        payload.company_id = this.form.company_id;
                    }

                    await this.register(payload);
                }
                this.$router.push('/dashboard');
            } catch (error) {
                if (error.response?.status === 422) {
                    this.validationErrors = error.response.data.errors || error.response.data;
                } else if (error.response?.status === 401) {
                    // Trata o erro 401, exibindo a mensagem do backend ou uma padrão
                    this.error = error.response?.data?.message || 'E-mail ou senha inválidos.';
                } else {
                    this.error = error.response?.data?.message || 'Ocorreu um erro ao processar sua solicitação.';
                }
            } finally {
                this.loading = false;
            }
        },

        toggleMode() {
            this.isLogin = !this.isLogin;
            this.error = '';
            this.validationErrors = {};
            this.form = {
                name: '',
                email: '',
                password: '',
                password_confirmation: '',
                company_name: '',
                company_id: null
            };

            if (!this.isLogin) {
                this.fetchCompanies();
            }
        }
    },
    created() {
        if (!this.isLogin) {
            this.fetchCompanies();
        }
    }
};
</script>
