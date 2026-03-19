<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

type TaskStatus = 'todo' | 'doing' | 'done';

interface TaskUser {
    id: number;
    name: string;
    email: string;
}

interface Task {
    id: number;
    title: string;
    description: string | null;
    status: TaskStatus;
    due_date: string | null;
    created_at: string;
    updated_at: string;
    user?: TaskUser | null;
    user_id : number;
}

const props = defineProps<{
    task: Task;
}>();

const page = usePage();
const authUser = computed(() => page.props.auth?.user);

const canEdit = computed(() => {
    return authUser.value && props.task.user_id === authUser.value.id;
});

function deleteTask() {
    if (confirm('Tem certeza que deseja excluir esta tarefa?')) {
        router.delete(`/tasks/${props.task.id}`, {
            preserveScroll: true,
        });
    }
}

function formatDate(date: string | null) {
    if (!date) return '';
    return new Date(date).toLocaleDateString('pt-BR');
}

function formatRelativeDate(date: string) {
    return new Intl.RelativeTimeFormat('pt-BR', { numeric: 'auto' });
}

function createdAtLabel(date: string) {
    const now = new Date();
    const createdAt = new Date(date);
    const diffMs = createdAt.getTime() - now.getTime();

    const diffMinutes = Math.round(diffMs / (1000 * 60));
    const diffHours = Math.round(diffMs / (1000 * 60 * 60));
    const diffDays = Math.round(diffMs / (1000 * 60 * 60 * 24));

    const rtf = new Intl.RelativeTimeFormat('pt-BR', { numeric: 'auto' });

    if (Math.abs(diffMinutes) < 60) return rtf.format(diffMinutes, 'minute');
    if (Math.abs(diffHours) < 24) return rtf.format(diffHours, 'hour');
    return rtf.format(diffDays, 'day');
}

function wasEdited(createdAt: string, updatedAt: string) {
    const created = new Date(createdAt).getTime();
    const updated = new Date(updatedAt).getTime();
    return updated - created > 5000;
}

function userName() {
    return props.task.user?.name ?? 'Usuário';
}
</script>

<template>
    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <div class="flex space-x-3">
                <div class="min-w-0 flex-1">
                    <div class="flex justify-between w-full">
                        <div class="flex items-center gap-1 flex-wrap">
                            <span class="text-sm font-semibold">
                                {{ userName() }}
                            </span>

                            <span class="text-base-content/60">·</span>

                            <span class="text-sm text-base-content/60">
                                {{ createdAtLabel(task.created_at) }}
                            </span>

                            <template v-if="wasEdited(task.created_at, task.updated_at)">
                                <span class="text-base-content/60">·</span>
                                <span class="text-sm text-base-content/60 italic">editada</span>
                            </template>
                        </div>

                        <div v-if="canEdit" class="flex gap-1">
                            <Link :href="`/tasks/${task.id}/edit`" class="btn btn-ghost btn-xs">
                                Editar
                            </Link>

                            <button
                                type="button"
                                class="btn btn-ghost btn-xs text-error"
                                @click="deleteTask"
                            >
                                Excluir
                            </button>
                        </div>
                    </div>

                    <div class="mt-1 flex items-center gap-2 flex-wrap">
                        <p class="font-medium">{{ task.title }}</p>

                        <span
                            v-if="task.status === 'todo'"
                            class="badge badge-warning badge-outline badge-sm"
                        >
                            To do
                        </span>

                        <span
                            v-else-if="task.status === 'doing'"
                            class="badge badge-info badge-outline badge-sm"
                        >
                            Doing
                        </span>

                        <span
                            v-else-if="task.status === 'done'"
                            class="badge badge-success badge-outline badge-sm"
                        >
                            Done
                        </span>
                    </div>

                    <p v-if="task.description" class="mt-2 text-sm text-base-content/80">
                        {{ task.description }}
                    </p>

                    <p v-if="task.due_date" class="mt-2 text-sm text-base-content/60">
                        Prazo: {{ formatDate(task.due_date) }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>