<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import type { BreadcrumbItem } from '@/types';

type TaskStatus = 'todo' | 'doing' | 'done';

interface Task {
    id: number;
    title: string;
    description: string | null;
    status: TaskStatus;
    due_date: string | null;
    created_at: string;
    updated_at: string;
}

const props = defineProps<{
    tasks: Task[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const todoCount = computed(() =>
    props.tasks.filter((task) => task.status === 'todo').length,
);

const doingCount = computed(() =>
    props.tasks.filter((task) => task.status === 'doing').length,
);

const doneCount = computed(() =>
    props.tasks.filter((task) => task.status === 'done').length,
);

const form = useForm({
    title: '',
    description: '',
    status: 'todo' as TaskStatus,
    due_date: '',
});

function submit() {
    form.post('/tasks', {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
}

function destroyTask(taskId: number) {
    if (confirm('Tem certeza que deseja excluir esta tarefa?')) {
        router.delete(`/tasks/${taskId}`, {
            preserveScroll: true,
        });
    }
}

function formatDate(date: string | null) {
    if (!date) return '';

    return new Date(date).toLocaleDateString('pt-BR');
} 

function formatDateTime(date: string) {
    return new Date(date).toLocaleString('pt-BR', {
        dateStyle: 'short',
        timeStyle: 'short',
    });
}

function statusLabel(status: TaskStatus) {
    if (status === 'todo') return 'To do';
    if (status === 'doing') return 'Doing';
    return 'Done';
}

function statusBadgeClass(status: TaskStatus) {
    if (status === 'todo') return 'badge-warning';
    if (status === 'doing') return 'badge-info';
    return 'badge-success';
}
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="max-w-4xl mx-auto px-4 py-8 space-y-8">
            <div class="hero bg-base-200 rounded-3xl shadow-sm">
                <div class="hero-content text-center py-10">
                    <div class="max-w-2xl">
                        <h1 class="text-4xl font-bold">Organize suas tarefas aqui</h1>
                        <p class="py-4 text-base-content/70">
                            Crie, acompanhe e conclua suas tasks.
                        </p>

                        <div class="stats stats-vertical lg:stats-horizontal shadow bg-base-100 mt-4">
                            <div class="stat">
                                <div class="stat-title">To do</div>
                                <div class="stat-value text-warning">{{ todoCount }}</div>
                                <div class="stat-desc">Tarefas pendentes</div>
                            </div>

                            <div class="stat">
                                <div class="stat-title">Doing</div>
                                <div class="stat-value text-info">{{ doingCount }}</div>
                                <div class="stat-desc">Em andamento</div>
                            </div>

                            <div class="stat">
                                <div class="stat-title">Done</div>
                                <div class="stat-value text-success">{{ doneCount }}</div>
                                <div class="stat-desc">Concluídas</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card bg-base-100 shadow-xl border border-base-200">
                <div class="card-body">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="card-title text-2xl">Nova tarefa</h2>
                            <p class="text-sm text-base-content/60">
                                Adicione uma nova task para acompanhar seu progresso.
                            </p>
                        </div>
                    </div>

                    <form @submit.prevent="submit" class="space-y-4">
                        <div class="form-control w-full">
                            <label class="label">
                                <span class="label-text font-medium">Título</span>
                            </label>
                            <input
                                v-model="form.title"
                                type="text"
                                placeholder="Ex: Finalizar bootcamp do Laravel"
                                class="input input-bordered w-full"
                                :class="{ 'input-error': form.errors.title }"
                                maxlength="120"
                                required
                            />
                            <label v-if="form.errors.title" class="label">
                                <span class="label-text-alt text-error">{{ form.errors.title }}</span>
                            </label>
                        </div>

                        <div class="form-control w-full">
                            <label class="label">
                                <span class="label-text font-medium">Descrição</span>
                            </label>
                            <textarea
                                v-model="form.description"
                                placeholder="Descreva a tarefa..."
                                class="textarea textarea-bordered w-full resize-none"
                                :class="{ 'textarea-error': form.errors.description }"
                                rows="4"
                            />
                            <label v-if="form.errors.description" class="label">
                                <span class="label-text-alt text-error">{{ form.errors.description }}</span>
                            </label>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="form-control w-full">
                                <label class="label">
                                    <span class="label-text font-medium">Status</span>
                                </label>
                                <select
                                    v-model="form.status"
                                    class="select select-bordered w-full"
                                    :class="{ 'select-error': form.errors.status }"
                                >
                                    <option value="todo">To do</option>
                                    <option value="doing">Doing</option>
                                    <option value="done">Done</option>
                                </select>
                                <label v-if="form.errors.status" class="label">
                                    <span class="label-text-alt text-error">{{ form.errors.status }}</span>
                                </label>
                            </div>

                            <div class="form-control w-full">
                                <label class="label">
                                    <span class="label-text font-medium">Prazo</span>
                                </label>
                                <input
                                    v-model="form.due_date"
                                    type="date"
                                    class="input input-bordered w-full"
                                    :class="{ 'input-error': form.errors.due_date }"
                                />
                                <label v-if="form.errors.due_date" class="label">
                                    <span class="label-text-alt text-error">{{ form.errors.due_date }}</span>
                                </label>
                            </div>
                        </div>

                        <div class="flex justify-end pt-2">
                            <button type="submit" class="btn btn-primary" :disabled="form.processing">
                                {{ form.processing ? 'Criando...' : 'Criar tarefa' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div>
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-2xl font-bold">Minhas tarefas</h2>
                        <p class="text-sm text-base-content/60">
                            Gerencie suas atividades e acompanhe o andamento.
                        </p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div
                        v-for="task in tasks"
                        :key="task.id"
                        class="card bg-base-100 shadow border border-base-200"
                    >
                        <div class="card-body">
                            <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <h3 class="card-title text-lg">{{ task.title }}</h3>

                                        <div
                                            class="badge badge-outline"
                                            :class="statusBadgeClass(task.status)"
                                        >
                                            {{ statusLabel(task.status) }}
                                        </div>
                                    </div>

                                    <p
                                        v-if="task.description"
                                        class="mt-2 text-base-content/70"
                                    >
                                        {{ task.description }}
                                    </p>

                                    <div class="mt-4 flex flex-wrap gap-2 text-sm text-base-content/60">
                                        <div v-if="task.due_date" class="badge badge-ghost">
                                            Prazo: {{ formatDate(task.due_date) }}
                                        </div>

                                        <div class="badge badge-ghost">
                                            Criada em {{ formatDateTime(task.created_at) }}
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2">
                                    <Link
                                        :href="`/tasks/${task.id}/edit`"
                                        class="btn btn-outline btn-sm"
                                    >
                                        Editar
                                    </Link>

                                    <button
                                        type="button"
                                        class="btn btn-error btn-outline btn-sm"
                                        @click="destroyTask(task.id)"
                                    >
                                        Excluir
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="tasks.length === 0"
                        class="hero bg-base-100 rounded-2xl shadow border border-dashed border-base-300 py-12"
                    >
                        <div class="hero-content text-center">
                            <div>
                                <svg
                                    class="mx-auto h-12 w-12 opacity-30"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 5h6m-7 4h8m-9 4h10m-11 4h12"
                                    />
                                </svg>
                                <h3 class="mt-4 text-xl font-semibold">Nenhuma tarefa cadastrada</h3>
                                <p class="mt-2 text-base-content/60">
                                    Comece criando sua primeira task no formulário acima.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>