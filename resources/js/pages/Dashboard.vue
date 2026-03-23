<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import { Calendar } from '@/components/ui/calendar';
import { Field, FieldContent, FieldError, FieldLabel } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { cn } from '@/lib/utils';
import { Head, Link } from '@inertiajs/vue3';
import type { DateValue } from '@internationalized/date';
import { DateFormatter, getLocalTimeZone, today } from '@internationalized/date';
import { Award, BadgeCheck, Flower, Flower2 } from '@lucide/vue';
import { toTypedSchema } from '@vee-validate/zod';
import axios from 'axios';
import { CalendarIcon, Loader2 } from 'lucide-vue-next';
import { useForm, Field as VeeField } from 'vee-validate';
import { computed, ref } from 'vue';
import { z } from 'zod';
// import {
//   AlertDialog,
//   AlertDialogAction,
//   AlertDialogCancel,
//   AlertDialogContent,
//   AlertDialogDescription,
//   AlertDialogFooter,
//   AlertDialogHeader,
//   AlertDialogTitle,
//   AlertDialogTrigger,
// } from "@/components/ui/alert-dialog"

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

const todayString = today(getLocalTimeZone()).toString();

const taskSchema = z.object({
    title: z.string().trim().min(1, { message: 'O título é obrigatório.' }).max(120, { message: 'O título deve ter no máximo 120 caracteres.' }),
    description: z.string().nullable(),
    status: z.enum(['todo', 'doing', 'done']),
    due_date: z
        .string()
        .nullable()
        .refine((value) => !value || value >= todayString, { message: 'A data de vencimento não pode ser anterior a data atual.' }),
});

const formContext = useForm({
    initialValues: {
        title: '',
        description: '',
        status: 'todo' as TaskStatus,
        due_date: '',
    },
    validationSchema: toTypedSchema(taskSchema),
});

const onSubmit = formContext.handleSubmit(async (data) => {
    try {
        await axios.post('/tasks', data, {
            headers: {
                Accept: 'application/json',
            },
        });
        window.location.href = '/dashboard';
    } catch (error: any) {
        if (error.response?.status === 422) {
            const errors = error.response.data.errors ?? {};

            if (errors.title?.[0]) formContext.setFieldError('title', errors.title[0]);
            if (errors.description?.[0]) formContext.setFieldError('description', errors.description[0]);
            if (errors.status?.[0]) formContext.setFieldError('status', errors.status[0]);
            if (errors.due_date?.[0]) formContext.setFieldError('due_date', errors.due_date[0]);
            return;
        }
        console.error(error);
    }
});

const destroyTask = async (taskId: number) => {
    if (!confirm('Você tem certeza que deseja excluir essa tarefa?')) {
        return;
    }
    await axios.delete(`/tasks/${taskId}`, {
        headers: {
            Accept: 'application/json',
        },
    });
    window.location.href = '/dashboard';
};

const defaultPlaceholder = today(getLocalTimeZone());

function handleDateSelect(value: DateValue | undefined) {
    date.value = value;
    formContext.setFieldValue('due_date', value ? value.toString() : '');
    popoverOpen.value = false;
}

const date = ref<DateValue | undefined>(undefined);

const popoverOpen = ref(false);

const df = new DateFormatter('pt-BR', {
    dateStyle: 'long',
});

function formatDate(date: string | null) {
    if (!date) return '';

    return new Date(date).toLocaleDateString('pt-BR');
}

const todoCount = computed(() => props.tasks.filter((task) => task.status === 'todo').length);

const doingCount = computed(() => props.tasks.filter((task) => task.status === 'doing').length);

const doneCount = computed(() => props.tasks.filter((task) => task.status === 'done').length);

const showAlert = ref(false);
</script>

<template>
    <Head title="Dashboard"></Head>
    <AppLayout>
        <div class="mx-auto max-w-4xl space-y-8 px-4 py-8">
            <div class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <Card class="rounded-2xl border border-border/60 bg-card shadow-sm transition-all hover:shadow-md">
                    <CardHeader class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-muted">
                                <Flower2 class="h-5 w-5" />
                            </div>
                            <span class="text-xs font-medium uppercase tracking-wide text-muted-foreground">Todo</span>
                        </div>
                        <div class="space-y-1">
                            <CardTitle class="text-4xl font-bold leading-none">{{ todoCount }}</CardTitle>
                            <CardDescription class="text-sm text-muted-foreground"> Tarefas pendentes</CardDescription>
                        </div>
                    </CardHeader>
                </Card>

                <Card class="rounded-2xl border border-border/60 bg-card shadow-sm transition-all hover:shadow-md">
                    <CardHeader class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-muted">
                                <Flower class="h-5 w-5" />
                            </div>
                            <span class="text-xs font-medium uppercase tracking-wide text-muted-foreground"> Doing</span>
                        </div>
                        <div class="space-y-1">
                            <CardTitle class="text-4xl font-bold leading-none">{{ doingCount }}</CardTitle>
                            <CardDescription class="text-sm text-muted-foreground"> Em andamento</CardDescription>
                        </div>
                    </CardHeader>
                </Card>

                <Card class="rounded-2xl border border-border/60 bg-card shadow-sm transition-all hover:shadow-md">
                    <CardHeader class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-muted">
                                <BadgeCheck class="h-5 w-5" />
                            </div>
                            <span class="text-xs font-medium uppercase tracking-wide text-muted-foreground">Done</span>
                        </div>
                        <div class="space-y-1">
                            <CardTitle class="text-4xl font-bold leading-none">{{ doneCount }}</CardTitle>
                            <CardDescription class="text-sm text-muted-foreground"> Concluídas</CardDescription>
                        </div>
                    </CardHeader>
                </Card>

                <Card class="rounded-2xl border border-border/60 bg-card shadow-sm transition-all hover:shadow-md">
                    <CardHeader class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-muted">
                                <Award class="h-5 w-5" />
                            </div>
                            <span class="text-xs font-medium uppercase tracking-wide text-muted-foreground">Total</span>
                        </div>
                        <div class="space-y-1">
                            <CardTitle class="text-4xl font-bold leading-none">{{ tasks.length }}</CardTitle>
                            <CardDescription class="text-sm text-muted-foreground"> Todas as tarefas</CardDescription>
                        </div>
                    </CardHeader>
                </Card>
            </div>

            <div class="mx-auto max-w-2xl px-4 py-8">
                <div class="mt-8 rounded-2xl border border-border bg-card shadow-sm">
                    <div class="space-y-6 p-6">
                        <h1 class="text-3xl font-bold">Criar tarefa</h1>
                        <form @submit.prevent="onSubmit">
                            <VeeField v-slot="{ field, errors }" name="title">
                                <Field :data-invalid="!!errors.length">
                                    <FieldLabel for="title"> Título </FieldLabel>
                                    <Input
                                        id="title"
                                        v-model="field.value"
                                        @update:model-value="field.onChange"
                                        :aria-invalid="!!errors.length"
                                        placeholder="Ex: Tarefa importante"
                                    />
                                    <FieldError :errors="errors.map((message) => ({ message }))" />
                                </Field>
                            </VeeField>

                            <VeeField v-slot="{ field, errors }" name="description">
                                <Field :data-invalid="!!errors.length">
                                    <FieldLabel for="description"> Descrição </FieldLabel>

                                    <Textarea
                                        id="description"
                                        v-model="field.value"
                                        @update:model-value="field.onChange"
                                        placeholder="Ex: Finalizar formulário do projeto Laravel + Vue"
                                        class="min-h-[120px]"
                                        :aria-invalid="!!errors.length"
                                    >
                                    </Textarea>
                                </Field>
                            </VeeField>

                            <VeeField v-slot="{ errors }" name="due_date">
                                <Field :data-invalid="!!errors.length">
                                    <FieldLabel>Prazo</FieldLabel>
                                    <Popover v-model:open="popoverOpen">
                                        <PopoverTrigger as-child>
                                            <Button
                                                type="button"
                                                variant="outline"
                                                :class="cn('w-[240px] justify-start text-left font-normal', !date && 'text-muted-foreground')"
                                            >
                                                <CalendarIcon />
                                                {{ date ? df.format(date.toDate(getLocalTimeZone())) : 'Escolha a data do prazo' }}
                                            </Button>
                                        </PopoverTrigger>

                                        <PopoverContent class="w-auto p-0" align="start">
                                            <Calendar
                                                v-model="date"
                                                :default-placeholder="defaultPlaceholder"
                                                layout="month-and-year"
                                                initial-focus
                                                @update:model-value="handleDateSelect"
                                            />
                                        </PopoverContent>
                                    </Popover>
                                    <FieldError :errors="errors.map((message) => ({ message }))" />
                                </Field>
                            </VeeField>

                            <VeeField v-slot="{ field, errors }" name="status">
                                <Field orientation="responsive" :data-invalid="!!errors.length">
                                    <FieldContent>
                                        <FieldLabel for="status"> Status</FieldLabel>
                                    </FieldContent>
                                    <Select :model-value="field.value" @update:model-value="field.onChange" @blur="field.onBlur">
                                        <SelectTrigger id="status" class="min-w-[120px]" :aria-invalid="!!errors.length">
                                            <SelectValue placeholder="Select" />
                                        </SelectTrigger>
                                        <SelectContent position="item-aligned">
                                            <SelectItem value="todo"> Todo </SelectItem>
                                            <SelectItem value="doing"> Doing </SelectItem>
                                            <SelectItem value="done"> Done </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </Field>
                            </VeeField>

                            <Button type="submit" :disabled="formContext.isSubmitting.value" class="min-w-25">
                                <template v-if="formContext.isSubmitting.value">
                                    <Loader2 class="mr-2 h-4 w-4 animate-spin" />
                                    Salvando...
                                </template>
                                <template v-else> Salvar </template>
                            </Button>
                        </form>
                    </div>
                </div>
            </div>
            <div>
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold">Minhas tarefas</h2>
                        <p class="text-sm text-muted-foreground">Gerencie suas atividades e acompanhe o andamento.</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div v-for="task in tasks" :key="task.id" class="rounded-2xl border border-border bg-card shadow-sm">
                        <div class="p-6">
                            <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                                <div class="flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="text-lg font-semibold tracking-tight">{{ task.title }}</h3>

                                        <div
                                            class="inline-flex items-center rounded-full border border-border bg-background px-2.5 py-0.5 text-xs font-medium capitalize"
                                            :class="task.status"
                                        >
                                            {{ task.status }}
                                        </div>
                                    </div>

                                    <p v-if="task.description" class="mt-4 flex flex-wrap gap-2 text-sm text-muted-foreground">
                                        {{ task.description }}
                                    </p>

                                    <div class="text-base-content/60 mt-4 flex flex-wrap gap-2 text-sm">
                                        <div
                                            v-if="task.due_date"
                                            class="inline-flex items-center rounded-full bg-muted px-2.5 py-0.5 text-xs font-medium text-muted-foreground"
                                        >
                                            Prazo: {{ formatDate(task.due_date) }}
                                        </div>

                                        <div
                                            class="inline-flex items-center rounded-full bg-muted px-2.5 py-0.5 text-xs font-medium text-muted-foreground"
                                        >
                                            Criada em {{ formatDate(task.created_at) }}
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2">
                                    <Link
                                        :href="`/tasks/${task.id}/edit`"
                                        class="inline-flex h-9 items-center justify-center rounded-md border border-input bg-background px-4 py-2 text-sm font-medium shadow-sm transition-colors hover:bg-accent hover:text-accent-foreground"
                                    >
                                        Editar
                                    </Link>

                                    <AlertDialog>
  <AlertDialogTrigger asChild>
    <Button variant="outline">Show Dialog</Button>
  </AlertDialogTrigger>
  <AlertDialogContent>
    <AlertDialogHeader>
      <AlertDialogTitle>Are you absolutely sure?</AlertDialogTitle>
      <AlertDialogDescription>
        This action cannot be undone. This will permanently delete your account
        from our servers.
      </AlertDialogDescription>
    </AlertDialogHeader>
    <AlertDialogFooter>
      <AlertDialogCancel>Cancel</AlertDialogCancel>
      <AlertDialogAction>Continue</AlertDialogAction>
    </AlertDialogFooter>
  </AlertDialogContent>
</AlertDialog>

                                    <button
                                        type="button"
                                        class="inline-flex h-9 items-center justify-center rounded-md border border-input bg-background px-4 py-2 text-sm font-medium shadow-sm transition-colors hover:bg-destructive hover:text-destructive-foreground"
                                        @click="destroyTask(task.id)"
                                    >
                                        Excluir
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="tasks.length === 0" class="rounded-2xl border border-dashed border-border bg-card py-12 shadow-sm">
                        <div class="mt-2 text-sm text-muted-foreground">
                            <div>
                                <svg class="mx-auto h-12 w-12 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5h6m-7 4h8m-9 4h10m-11 4h12" />
                                </svg>
                                <h3 class="mt-4 text-xl font-semibold">Nenhuma tarefa cadastrada</h3>
                                <p class="text-base-content/60 mt-2">Comece criando sua primeira task no formulário acima.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
