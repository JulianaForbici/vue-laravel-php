<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import { Calendar } from '@/components/ui/calendar';
import { Field, FieldContent, FieldDescription, FieldError, FieldLabel } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { cn } from '@/lib/utils';
import type { BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import type { DateValue } from '@internationalized/date';
import { DateFormatter, getLocalTimeZone, today } from '@internationalized/date';
import axios from 'axios';
import { CalendarIcon, Loader2 } from 'lucide-vue-next';
import { useForm, Field as VeeField } from 'vee-validate';
import { ref, type Ref } from 'vue';

// const taskSchema = z.object({
//     title: z.string().max(120, 'O título é obrigatório e deve contar no máximo 120 caracteres.'),
//     description: z.string().nullable(),
//     status: z.enum(['todo', 'doing', 'done']),
//     due_date: z.string().nullable(),
// });

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

const formContext = useForm({
    initialValues: {
        title: props.task.title ?? '',
        description: props.task.description ?? '',
        status: props.task.status,
        due_date: props.task.due_date ? String(props.task.due_date).slice(0, 10) : '',
    },
    // validationSchema: toTypedSchema(taskSchema),
});

const onSubmit = formContext.handleSubmit(async (data) => {
    await axios.put(`/tasks/${props.task.id}`, data);

    console.log(JSON.stringify(data, null, 2));
});

const defaultPlaceholder = today(getLocalTimeZone());

const date = ref() as Ref<DateValue>;

const df = new DateFormatter('en-US', {
    dateStyle: 'long',
});
</script>

<template>
    <Head title="Editar tarefa"></Head>

    <AppLayout :breadcrumbs="[]">
        <div class="mx-auto max-w-2xl px-4 py-8">
            <h1 class="text-3xl font-bold">Editar tarefa</h1>

            <div class="card bg-base-100 mt-8 shadow">
                <div class="card-body">
                    <form @submit="onSubmit">
                        <VeeField v-slot="{ field, errors }" name="title">
                            <Field :data-invalid="!!errors.length">
                                <FieldLabel for="title"> More about you </FieldLabel>
                                <Input
                                    id="title"
                                    v-model="field.value"
                                    @update:model-value="field.onChange"
                                    :aria-invalid="!!errors.length"
                                    placeholder="shadcn"
                                    autocomplete="username"
                                />
                                <FieldDescription>
                                    Tell us more about yourself. This will be used to help us personalize your experience.
                                </FieldDescription>
                                <FieldError v-if="errors.length" :errors="errors" />
                            </Field>
                        </VeeField>

                        <VeeField v-slot="{ field, errors }" name="description">
                            <Field :data-invalid="!!errors.length">
                                <FieldLabel for="description"> More about you </FieldLabel>

                                <Textarea
                                    id="description"
                                    v-model="field.value"
                                    @update:model-value="field.onChange"
                                    placeholder="I'm a software engineer..."
                                    class="min-h-[120px]"
                                    :aria-invalid="!!errors.length"
                                >
                                </Textarea>
                                <FieldDescription>
                                    Tell us more about yourself. This will be used to help us personalize your experience.
                                </FieldDescription>
                                <FieldError v-if="errors.length" :errors="errors" />
                            </Field>
                        </VeeField>

                        <VeeField v-slot="{ field, errors }" name="status">
                            <Field orientation="responsive" :data-invalid="!!errors.length">
                                <FieldContent>
                                    <FieldLabel for="status"> Task Status</FieldLabel>
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

                        <Popover v-slot="{ close }">
                            <PopoverTrigger as-child>
                                <Button
                                    type="button"
                                    variant="outline"
                                    :class="cn('w-[240px] justify-start text-left font-normal', !date && 'text-muted-foreground')"
                                >
                                    <CalendarIcon />
                                    {{ date ? df.format(date.toDate(getLocalTimeZone())) : 'Pick a date' }}
                                </Button>
                            </PopoverTrigger>
                            <PopoverContent class="w-auto p-0" align="start">
                                <Calendar
                                    v-model="date"
                                    :default-placeholder="defaultPlaceholder"
                                    layout="month-and-year"
                                    initial-focus
                                    @update:model-value="close"
                                />
                            </PopoverContent>
                        </Popover>

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
