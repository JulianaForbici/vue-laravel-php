<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
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
    task: Task;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Editar tarefa', href: `/tasks/${props.task.id}/edit` },
];

const form = useForm({
    title: props.task.title ?? '',
    description: props.task.description ?? '',
    status: props.task.status,
    due_date: props.task.due_date ? String(props.task.due_date).slice(0, 10) : '',
});

function submit() {
    form.put(`/tasks/${props.task.id}`);
}
</script>

<template>
    <Head title="Editar tarefa" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="max-w-2xl mx-auto px-4 py-8">
            <h1 class="text-3xl font-bold">Editar tarefa</h1>

            <div class="card bg-base-100 shadow mt-8">
                <div class="card-body">
                    <form @submit.prevent="submit">
                        <div class="form-control w-full mb-4">
                            <label class="label">
                                <span class="label-text font-medium">Título</span>
                            </label>
                            <input
                                v-model="form.title"
                                type="text"
                                maxlength="120"
                                required
                                class="input input-bordered w-full"
                                :class="{ 'input-error': form.errors.title }"
                            />
                            <div v-if="form.errors.title" class="label">
                                <span class="label-text-alt text-error">
                                    {{ form.errors.title }}
                                </span>
                            </div>
                        </div>

                        <div class="form-control w-full mb-4">
                            <label class="label">
                                <span class="label-text font-medium">Descrição</span>
                            </label>
                            <textarea
                                v-model="form.description"
                                rows="4"
                                class="textarea textarea-bordered w-full resize-none"
                                :class="{ 'textarea-error': form.errors.description }"
                            />
                            <div v-if="form.errors.description" class="label">
                                <span class="label-text-alt text-error">
                                    {{ form.errors.description }}
                                </span>
                            </div>
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
                                <div v-if="form.errors.status" class="label">
                                    <span class="label-text-alt text-error">
                                        {{ form.errors.status }}
                                    </span>
                                </div>
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
                                <div v-if="form.errors.due_date" class="label">
                                    <span class="label-text-alt text-error">
                                        {{ form.errors.due_date }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="card-actions justify-between mt-6">
                            <Link href="/dashboard" class="btn btn-ghost btn-sm">
                                Cancelar
                            </Link>

                            <button type="submit" class="btn btn-primary btn-sm" :disabled="form.processing">
                                {{ form.processing ? 'Atualizando...' : 'Atualizar tarefa' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>