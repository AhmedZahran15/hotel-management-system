<script setup lang="ts">
import TextLink from '@/components/TextLink.vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import { Input } from '@/components/ui/input';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3'; // Add usePage import
import { toTypedSchema } from '@vee-validate/zod';
import { AlertCircle } from 'lucide-vue-next';
import { useForm } from 'vee-validate';
import { computed, ref } from 'vue'; // Add computed import
import { z } from 'zod';
defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

// Get page from Inertia to access errors
const page = usePage();
const errors = computed(() => page.props.errors);

// Define validation schema with zod
const validationSchema = toTypedSchema(
    z.object({
        email: z.string().email('Invalid email address'),
        password: z.string().min(1, 'Password is required').min(6, 'Password must be at least 6 characters long'),
        remember: z.boolean().default(false),
    }),
);

// Use vee-validate's useForm
const { handleSubmit, isFieldDirty, isSubmitting } = useForm({
    validationSchema,
    initialValues: {
        email: '',
        password: '',
        remember: false,
    },
});

// Track Inertia form submission state
const isProcessing = ref(false);

// Submit handler
const onSubmit = handleSubmit((values) => {
    isProcessing.value = true;
    router.post(route('login'), values, {
        onFinish: () => {
            values.password = '';
            isProcessing.value = false;
        },
    });
});
</script>

<template>
    <AuthBase title="Log in to your account" description="Enter your email and password below to log in">
        <Head title="Log in" />

        <div v-if="status" class="mb-4 text-center text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <Alert v-if="Object.keys(errors).length > 0" variant="destructive" class="mb-4">
            <AlertCircle class="h-4 w-4" />
            <AlertTitle>Error</AlertTitle>
            <AlertDescription>
                <ul class="list-disc pl-4">
                    <li v-for="(error, key) in errors" :key="key">{{ error }}</li>
                </ul>
            </AlertDescription>
        </Alert>

        <form @submit="onSubmit" class="flex flex-col gap-6">
            <div class="grid gap-6">
                <FormField name="email" v-slot="{ field }" :validate-on-blur="!isFieldDirty">
                    <FormItem>
                        <FormLabel for="email">Email address</FormLabel>
                        <FormControl>
                            <Input
                                id="email"
                                type="email"
                                autofocus
                                :tabindex="1"
                                autocomplete="email"
                                placeholder="email@example.com"
                                v-bind="field"
                            />
                        </FormControl>
                        <FormMessage />
                    </FormItem>
                </FormField>

                <FormField name="password" v-slot="{ field }" :validate-on-blur="!isFieldDirty">
                    <FormItem>
                        <div class="flex items-center justify-between">
                            <FormLabel for="password">Password</FormLabel>
                            <TextLink v-if="canResetPassword" :href="route('password.request')" class="text-sm" :tabindex="5">
                                Forgot password?
                            </TextLink>
                        </div>
                        <FormControl>
                            <Input
                                id="password"
                                type="password"
                                :tabindex="2"
                                autocomplete="current-password"
                                placeholder="Password"
                                v-bind="field"
                            />
                        </FormControl>
                        <FormMessage />
                    </FormItem>
                </FormField>

                <FormField name="remember" v-slot="{ value, handleChange }">
                    <FormItem class="flex items-center gap-2">
                        <FormControl class="">
                            <Checkbox :tabindex="3" :checked="value" @update:checked="(checked) => handleChange(checked)" />
                        </FormControl>
                        <FormLabel class="!my-auto cursor-pointer">Remember me</FormLabel>
                        <FormMessage />
                    </FormItem>
                </FormField>

                <Button type="submit" class="mt-4 flex w-full gap-1" :tabindex="4" :disabled="isSubmitting || isProcessing">
                    <svg v-if="isSubmitting || isProcessing" class="h-6 w-6 animate-spin" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                        <path
                            class="opacity-75"
                            fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                        ></path>
                    </svg>
                    <span>Log in</span>
                </Button>
            </div>

            <div class="text-center text-sm text-muted-foreground">
                Don't have an account?
                <TextLink :href="route('register')" :tabindex="5">Sign up</TextLink>
            </div>
        </form>
    </AuthBase>
</template>
