<template>
    <div>
        <div class="row mb-4">
            <div class="col-md-8">
                <h2>Minhas Tarefas</h2>
            </div>
            <div class="col-md-4 text-right">
                <button class="btn btn-primary" @click="showCreateModal = true">
                    <i class="fas fa-plus"></i> Nova Tarefa
                </button>
                <button class="btn btn-success ml-2" @click="handleExportTasks">
                    <i class="fas fa-download"></i> Exportar para CSV
                </button>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-3">
                <select class="form-control" v-model="filters.status">
                    <option value="">Todos os Status</option>
                    <option value="pending">Pendente</option>
                    <option value="in_progress">Em Andamento</option>
                    <option value="completed">Finalizado</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-control" v-model="filters.priority">
                    <option value="">Todas as Prioridades</option>
                    <option value="low">Baixa</option>
                    <option value="medium">Média</option>
                    <option value="high">Alta</option>
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div v-if="loading" class="text-center">
                            <div class="spinner-border" role="status">
                                <span class="sr-only">Carregando...</span>
                            </div>
                        </div>

                        <div v-else-if="tasks.length === 0" class="text-center">
                            <p>No tasks found.</p>
                        </div>

                        <div v-else>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Título</th>
                                            <th>Status</th>
                                            <th>Prioridade</th>
                                            <th>Data Limite</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="task in tasks" :key="task.id">
                                            <td>{{ task.title }}</td>
                                            <td>
                                                <span :class="getStatusClass(task.status)">
                                                    {{ getStatusText(task.status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span :class="getPriorityClass(task.priority)">
                                                    {{ getPriorityText(task.priority) }}
                                                </span>
                                            </td>
                                            <td>{{ formatDate(task.due_date) }}</td>
                                            <td>
                                                <button class="btn btn-sm btn-info" @click="editTask(task)">
                                                    Editar
                                                </button>
                                                <button
                                                    v-if="task.status !== 'completed'"
                                                    class="btn btn-sm btn-success ml-1"
                                                    @click="handleCompleteTask(task.id)"
                                                >
                                                    Concluir
                                                </button>
                                                <button class="btn btn-sm btn-danger ml-1"
                                                        @click="handleDeleteTask(task.id)">
                                                    Excluir
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div
            v-if="showCreateModal"
            class="modal fade show"
            style="display: block;"
            tabindex="-1"
            role="dialog"
        >
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ editingTask ? 'Editar Tarefa' : 'Nova Tarefa' }}</h5>
                        <button type="button" class="close" @click="closeModal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form @submit.prevent="saveTask">
                            <div class="form-group">
                                <label>Título</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    :class="{'is-invalid': validationErrors.title}"
                                    v-model="taskForm.title"
                                    required
                                >
                                <div class="invalid-feedback" v-if="validationErrors.title">
                                    {{ validationErrors.title[0] }}
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Descrição</label>
                                <textarea
                                    class="form-control"
                                    :class="{'is-invalid': validationErrors.description}"
                                    v-model="taskForm.description"
                                    rows="3"
                                ></textarea>
                                <div class="invalid-feedback" v-if="validationErrors.description">
                                    {{ validationErrors.description[0] }}
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Status</label>
                                <select
                                    class="form-control"
                                    :class="{'is-invalid': validationErrors.status}"
                                    v-model="taskForm.status"
                                    required
                                >
                                    <option value="pending">Pendente</option>
                                    <option value="in_progress">Em Andamento</option>
                                    <option value="completed">Finalizado</option>
                                </select>
                                <div class="invalid-feedback" v-if="validationErrors.status">
                                    {{ validationErrors.status[0] }}
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Prioridade</label>
                                <select
                                    class="form-control"
                                    :class="{'is-invalid': validationErrors.priority}"
                                    v-model="taskForm.priority"
                                    required
                                >
                                    <option value="low">Baixa</option>
                                    <option value="medium">Média</option>
                                    <option value="high">Alta</option>
                                </select>
                                <div class="invalid-feedback" v-if="validationErrors.priority">
                                    {{ validationErrors.priority[0] }}
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Data Limite</label>
                                <input
                                    type="date"
                                    class="form-control"
                                    :class="{'is-invalid': validationErrors.due_date}"
                                    v-model="taskForm.due_date"
                                >
                                <div class="invalid-feedback" v-if="validationErrors.due_date">
                                    {{ validationErrors.due_date[0] }}
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" @click="closeModal">Cancelar</button>
                                <button type="submit" class="btn btn-primary" :disabled="saving">
                                    {{ saving ? 'Salvando...' : 'Salvar' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-backdrop fade show" v-if="showCreateModal"></div>
    </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex'

export default {
    name: 'Dashboard',
    data() {
        return {
            loading: false,
            saving: false,
            showCreateModal: false,
            editingTask: null,
            filters: {
                status: '',
                priority: ''
            },
            taskForm: {
                title: '',
                description: '',
                status: 'pending',
                priority: 'medium',
                due_date: ''
            },
            validationErrors: {}
        }
    },
    computed: {
        ...mapGetters(['tasks'])
    },
    methods: {
        ...mapActions(['fetchTasks', 'createTask', 'updateTask', 'deleteTask', 'completeTask', 'exportTasks']),

        async loadTasks() {
            this.loading = true
            try {
                await this.fetchTasks(this.filters)
            } catch (error) {
                console.error('Erro ao carregar tarefas:', error)
            } finally {
                this.loading = false
            }
        },

        async saveTask() {
            this.saving = true
            this.validationErrors = {}
            try {
                if (this.editingTask) {
                    await this.updateTask({ id: this.editingTask.id, ...this.taskForm })
                } else {
                    await this.createTask(this.taskForm)
                }

                this.closeModal()
                await this.loadTasks()

                this.$nextTick(() => {
                    if (this.editingTask) {
                        Swal.fire(
                            'Atualizado!',
                            'A tarefa foi atualizada com sucesso.',
                            'success'
                        )
                        return
                    }
                    Swal.fire(
                        'Adicionado!',
                        'A tarefa foi adicionada com sucesso.',
                        'success'
                    )
                })

            } catch (error) {
                if (error.response?.status === 422) {
                    this.validationErrors = error.response.data.errors
                } else {
                    console.error('Erro ao salvar tarefa:', error)
                    this.closeModal()
                    const msg = (error.response?.data?.message || error.message)
                    Swal.fire(
                        'Erro ao salvar!',
                        msg,
                        'error'
                    )
                }
            } finally {
                this.saving = false
            }
        },

        editTask(task) {
            this.editingTask = task
            const formattedTask = { ...task }
            if (task.due_date) {
                const date = new Date(task.due_date)
                formattedTask.due_date = date.toISOString().split('T')[0]
            }
            this.taskForm = formattedTask
            this.showCreateModal = true
        },

        closeModal() {
            this.showCreateModal = false
            this.editingTask = null
            this.validationErrors = {}

            this.taskForm = {
                title: '',
                description: '',
                status: 'pending',
                priority: 'medium',
                due_date: ''
            }

            this.$nextTick(() => {
                const backdrop = document.querySelector('.modal-backdrop')
                if (backdrop) {
                    backdrop.remove()
                }

                document.body.classList.remove('modal-open')
            })
        },

        async handleDeleteTask(id) {
            Swal.fire({
                title: 'Tem certeza?',
                text: "Você não poderá reverter esta ação!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sim, excluir!',
                cancelButtonText: 'Cancelar'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    try {
                        await this.deleteTask(id)
                        this.loadTasks()
                        Swal.fire(
                            'Excluído!',
                            'A tarefa foi excluída com sucesso.',
                            'success'
                        )
                    } catch (error) {
                        console.error('Erro ao excluir tarefa:', error)
                        Swal.fire(
                            'Erro!',
                            'Houve um erro ao tentar excluir a tarefa.',
                            'error'
                        )
                    }
                }
            });
        },

        async handleCompleteTask(id) {
            try {
                await this.completeTask(id)
                this.loadTasks()
            } catch (error) {
                console.error('Erro ao concluir tarefa:', error)
            }
        },

        async handleExportTasks() {
            try {
                const message = await this.exportTasks(this.filters);
                Swal.fire({
                    title: 'Sucesso!',
                    text: message,
                    icon: 'success'
                });

            } catch (error) {
                Swal.fire({
                    title: 'Erro!',
                    text: 'Ocorreu um erro ao iniciar a exportação.',
                    icon: 'error'
                });
            }
        },

        getStatusClass(status) {
            const classes = {
                pending: 'badge badge-warning text-dark',
                in_progress: 'badge badge-info text-dark',
                completed: 'badge badge-success text-dark'
            }
            return classes[status] || 'badge badge-secondary text-white'
        },

        getPriorityClass(priority) {
            const classes = {
                low: 'badge badge-success text-dark',
                medium: 'badge badge-warning text-dark',
                high: 'badge badge-danger text-dark'
            }
            return classes[priority] || 'badge badge-secondary text-white'
        },

        formatDate(date) {
            if (!date) return '-'
            try {
                const dateObj = new Date(date)
                if (isNaN(dateObj.getTime())) return '-'

                const day = String(dateObj.getUTCDate()).padStart(2, '0')
                const month = String(dateObj.getUTCMonth() + 1).padStart(2, '0')
                const year = dateObj.getUTCFullYear()

                const formatted = `${ day }/${ month }/${ year }`
                return formatted
            } catch (error) {
                console.error('Error formatting date:', error, 'Original date:', date)
                return '-'
            }
        },

        getStatusText(status) {
            const statusMap = {
                pending: 'Pendente',
                in_progress: 'Em Andamento',
                completed: 'Concluída'
            }
            return statusMap[status] || status
        },

        getPriorityText(priority) {
            const priorityMap = {
                low: 'Baixa',
                medium: 'Média',
                high: 'Alta'
            }
            return priorityMap[priority] || priority
        }
    },

    watch: {
        filters: {
            handler() {
                this.loadTasks()
            },
            deep: true
        }
    },

    mounted() {
        this.loadTasks()
    }
}
</script>

<style scoped>
.modal {
    background-color: rgba(0, 0, 0, 0.5);
}
</style>
