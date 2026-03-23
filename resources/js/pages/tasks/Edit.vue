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
import { Head } from '@inertiajs/vue3';
import type { DateValue } from '@internationalized/date';
import { DateFormatter, getLocalTimeZone, parseDate, today } from '@internationalized/date';
import { toTypedSchema } from '@vee-validate/zod';
import axios from 'axios';
import { CalendarIcon, Loader2 } from 'lucide-vue-next';
import { useForm, Field as VeeField } from 'vee-validate';
import { ref } from 'vue';
import { z } from 'zod';

const todayString = today(getLocalTimeZone()).toString();

const taskSchema = z.object({
    title: z.string().trim().min(1, { message: 'O título é obrigatório.', }).max(120, { message: 'O título deve ter no máximo 120 caracteres.', }),
    description: z.string().nullable(),
    status: z.enum(['todo', 'doing', 'done']),
    due_date: z.string().nullable().refine((value) => !value || value >= todayString, { message: 'A data de vencimento não pode ser anterior a data atual.' }),
});

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

const formContext = useForm({
    initialValues: {
        title: props.task.title ?? '',
        description: props.task.description ?? '',
        status: props.task.status,
        due_date: props.task.due_date ? String(props.task.due_date).slice(0, 10) : '',
    },
    validationSchema: toTypedSchema(taskSchema),
});

const onSubmit = formContext.handleSubmit(async (data) => {
    await axios.put(`/tasks/${props.task.id}`, data, {
        headers: {
            Accept: 'application/json',
        },
    });
    window.location.href = '/dashboard';
});

const defaultPlaceholder = today(getLocalTimeZone());

const date = ref<DateValue | undefined>(props.task.due_date ? parseDate(String(props.task.due_date).slice(0, 10)) : undefined);

const popoverOpen = ref(false);

const df = new DateFormatter('pt-BR', {
    dateStyle: 'long',
});

function handleDateSelect(value: DateValue | undefined) {
    date.value = value;
    formContext.setFieldValue('due_date', value ? value.toString() : '');
    popoverOpen.value = false;
}
</script>

<template>
    <Head title="Editar tarefa"></Head>

    <AppLayout>
        <div class="mx-auto max-w-2xl px-4 py-8">
            <h1 class="text-3xl font-bold">Editar tarefa</h1>

            <div class="card mt-8 bg-base-100 shadow">
                <div class="card-body">
                    <form @submit="onSubmit">
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
                                <FieldError v-if="errors.length" :errors="errors" />
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
                                <FieldError v-if="errors.length" :errors="errors" />
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
                                <FieldError v-if="errors.length" :errors="errors" />
                            </Field>
                        </VeeField>

                        <VeeField v-slot="{ field, errors }" name="status">
                            <Field orientation="responsive" :data-invalid="!!errors.length">
                                <FieldContent>
                                    <FieldLabel for="status"> Status</FieldLabel>
                                    <FieldError v-if="errors.length" :errors="errors" />
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
    </AppLayout>
</template>
