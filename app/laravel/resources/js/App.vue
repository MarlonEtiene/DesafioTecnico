<template>
    <div id="app">
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark" v-if="isAuthenticated">
            <div class="container">
                <a class="navbar-brand" href="#">Gerenciador de Tarefas</a>
                <div class="navbar-nav ml-auto">
                    <span class="navbar-text">
                        {{ user.name }} - {{ user.company.name }}
                    </span>
                    <button class="btn btn-outline-light ms-3" @click="logout">Sair</button>
                </div>
            </div>
        </nav>

        <div class="container mt-4">
            <router-view></router-view>
        </div>
    </div>
</template>

<script>
import { mapGetters, mapActions } from 'vuex'

export default {
    name: 'App',
    computed: {
        ...mapGetters(['isAuthenticated', 'user'])
    },
    methods: {
        ...mapActions(['logout'])
    }
}
</script>

<style>
#app {
    font-family: 'Avenir', Helvetica, Arial, sans-serif;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    color: #2c3e50;
}

.navbar {
    margin-bottom: 20px;
}
</style>
