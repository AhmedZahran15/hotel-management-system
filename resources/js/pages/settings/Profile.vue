<script setup lang="ts">
import DeleteUser from '@/components/DeleteUser.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import { Input } from '@/components/ui/input';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem, type SharedData, type User } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { toTypedSchema } from '@vee-validate/zod';
import { AlertCircle, LoaderCircle, Upload } from 'lucide-vue-next';
import { useForm } from 'vee-validate';
import { computed, onMounted, ref } from 'vue';
import { z } from 'zod';

interface Props {
    mustVerifyEmail: boolean;
    status?: string;
    countries: Country[];
}

interface Country {
    id: number;
    name: string;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Profile settings',
        href: '/settings/profile',
    },
];

const page = usePage<SharedData>();
const user = page.props.auth.user as User;
const user_type = user.user_type;
const errors = computed(() => page.props.errors);
const avatarImage = ref<File | null>(null);
const avatarImagePreview = ref<string | null>(user.avatar || null);

// Define validation schema with zod - conditionally based on user type
const validationSchema = computed(() => {
    const baseSchema = {
        name: z.string().min(1, 'Name is required').min(3, 'Name must be at least 3 characters long'),
        email: z.string().email('Invalid email address'),
        avatar_image: z.any().optional(),
    };

    if (user_type === 'client') {
        return toTypedSchema(
            z.object({
                ...baseSchema,
                gender: z.enum(['male', 'female'], { required_error: 'Please select a gender' }),
                country: z.string().min(1, 'Please select a country'),
                phone_number: z
                    .string()
                    .min(1, 'Phone number is required')
                    .regex(/^\+?[0-9]{8,15}$/, 'Please enter a valid phone number (8-15 digits, may start with +)'),
            }),
        );
    } else if (user_type === 'employee') {
        return toTypedSchema(
            z.object({
                ...baseSchema,
                national_id: z.string().optional(),
            }),
        );
    } else {
        // user
        return toTypedSchema(
            z.object({
                ...baseSchema,
            }),
        );
    }
});

// Define types for form values
interface BaseFormValues {
    name: string;
    email: string;
    avatar_image: File | null;
}

interface ClientFormValues extends BaseFormValues {
    gender: 'male' | 'female';
    country: string; // Stores country ID as string
    phone_number: string;
}

interface EmployeeFormValues extends BaseFormValues {
    national_id: string;
}

type FormValues = BaseFormValues & Partial<ClientFormValues & EmployeeFormValues>;

// Use vee-validate's useForm with computed schema
const { handleSubmit, isFieldDirty, isSubmitting, setFieldError, setFieldValue, setValues } = useForm<FormValues>({
    validationSchema,
    initialValues: {
        name: '',
        email: '',
        gender: 'male',
        country: '',
        phone_number: '',
        avatar_image: null,
        national_id: '',
    },
});

// Track Inertia form submission state
const isProcessing = ref(false);

// Set initial values after component mounts
onMounted(() => {
    // Force immediate update of form values
    const formValues: FormValues = {
        name: user.name || '',
        email: user.email || '',
        avatar_image: null,
    };
    console.log(user);
    if (user_type === 'client' && user.profile) {
        formValues.gender = (user.profile?.gender as 'male' | 'female') || 'male';
        formValues.country = user.profile?.country?.id.toString() || '';
        formValues.phone_number = user.profile?.phones?.[0] || '';
    } else if (user_type === 'employee' && user.profile) {
        formValues.national_id = user.profile?.national_id || '';
    }

    setValues(formValues);
});

// Remove console.log for production
// console.log(user);

// Handle file upload
const handleFileUpload = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        const file = target.files[0];

        // Check if the file is a JPG or JPEG
        const allowedTypes = ['image/jpeg', 'image/jpg'];
        if (!allowedTypes.includes(file.type)) {
            setFieldError('avatar_image', 'Only JPG/JPEG files are allowed');
            target.value = '';
            return;
        }

        // Check file size (max 2MB)
        if (file.size > 2 * 1024 * 1024) {
            setFieldError('avatar_image', 'File size must be less than 2MB');
            target.value = '';
            return;
        }

        avatarImage.value = file;
        avatarImagePreview.value = URL.createObjectURL(file);

        // Update form field value to clear error
        setFieldValue('avatar_image', file);
    }
};

// Submit handler with proper type annotation
const onSubmit = handleSubmit((values: FormValues) => {
    isProcessing.value = true;
    // Create form data for file upload
    const formData = new FormData();
    formData.append('name', values.name);
    formData.append('email', values.email);

    // Only append fields based on user type
    if (user_type === 'client' && values.gender && values.country && values.phone_number) {
        formData.append('gender', values.gender);
        formData.append('country', values.country);
        formData.append('phone_number', values.phone_number);
    }

    // Only append avatar if a new one was selected
    if (avatarImage.value) {
        formData.append('avatar_image', avatarImage.value);
    }

    formData.append('_method', 'PATCH');

    router.post(route('profile.update'), formData, {
        onSuccess: () => {
            // Reload the page data without refreshing the page
            // This will update all components that use page.props.auth.user
            router.visit(window.location.pathname, {
                only: ['auth'],
                preserveScroll: true,
                preserveState: true,
            });
        },
        onFinish: () => {
            isProcessing.value = false;
        },
        preserveScroll: true,
    });
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Profile settings" />

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall title="Profile information" description="Update your profile information and account settings" />

                <Alert v-if="Object.keys(errors).length > 0" variant="destructive" class="mb-4">
                    <AlertCircle class="h-4 w-4" />
                    <AlertTitle>Error</AlertTitle>
                    <AlertDescription>
                        <ul class="list-disc pl-4">
                            <li v-for="(error, key) in errors" :key="key">{{ error }}</li>
                        </ul>
                    </AlertDescription>
                </Alert>
                <form @submit.prevent="onSubmit" class="space-y-6">
                    <!-- Avatar Image Upload -->
                    <FormField name="avatar_image" v-slot="{ errorMessage }">
                        <FormItem class="flex flex-col items-center">
                            <FormLabel class="text-center">Profile Picture</FormLabel>
                            <FormControl>
                                <div class="flex w-full flex-col items-center space-y-4">
                                    <!-- Image Preview -->
                                    <div
                                        class="relative h-28 w-28 overflow-hidden rounded-full border-2 border-dashed border-input bg-muted transition-all hover:border-primary/50"
                                    >
                                        <img
                                            v-if="avatarImagePreview"
                                            :src="avatarImagePreview"
                                            alt="Avatar Preview"
                                            class="h-full w-full object-cover"
                                        />
                                        <div v-else class="flex h-full w-full flex-col items-center justify-center text-muted-foreground">
                                            <Upload class="h-8 w-8 opacity-50" />
                                        </div>
                                    </div>
                                    <div class="flex flex-col items-center gap-2">
                                        <div class="relative">
                                            <label
                                                for="avatar_image"
                                                class="flex h-10 w-full max-w-xs cursor-pointer items-center justify-center gap-2 rounded-md border border-input bg-background px-4 py-2 text-sm font-medium text-foreground ring-offset-background transition-colors hover:bg-accent hover:text-accent-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                                            >
                                                <Upload class="h-4 w-4" />
                                                {{ avatarImagePreview ? 'Change photo' : 'Upload photo' }}
                                            </label>
                                            <Input
                                                id="avatar_image"
                                                type="file"
                                                accept="image/jpeg, image/jpg"
                                                class="sr-only"
                                                @change="handleFileUpload"
                                            />
                                        </div>
                                        <p class="text-xs text-muted-foreground">JPG/JPEG format only, max 2MB</p>
                                    </div>
                                </div>
                            </FormControl>
                            <FormMessage v-if="errorMessage">{{ errorMessage }}</FormMessage>
                        </FormItem>
                    </FormField>

                    <!-- Name -->
                    <FormField name="name" v-slot="{ field }" :validate-on-blur="!isFieldDirty">
                        <FormItem>
                            <FormLabel for="name">Name</FormLabel>
                            <FormControl>
                                <Input id="name" type="text" autocomplete="name" placeholder="Full name" v-bind="field" class="!text-foreground" />
                            </FormControl>
                            <FormMessage />
                        </FormItem>
                    </FormField>

                    <!-- Email -->
                    <FormField name="email" v-slot="{ field }" :validate-on-blur="!isFieldDirty">
                        <FormItem>
                            <FormLabel for="email">Email address</FormLabel>
                            <FormControl>
                                <Input
                                    id="email"
                                    type="email"
                                    autocomplete="email"
                                    placeholder="email@example.com"
                                    v-bind="field"
                                    class="!text-foreground"
                                />
                            </FormControl>
                            <FormMessage />
                        </FormItem>
                    </FormField>

                    <!-- National ID (only for employee type) -->
                    <FormField v-if="user_type === 'employee'" name="national_id" v-slot="{ field }" :validate-on-blur="!isFieldDirty">
                        <FormItem>
                            <FormLabel for="national_id">National ID</FormLabel>
                            <FormControl>
                                <Input id="national_id" type="text" v-bind="field" disabled class="!text-foreground" />
                            </FormControl>
                            <FormMessage />
                        </FormItem>
                    </FormField>

                    <!-- Fields only shown for client type -->
                    <template v-if="user_type === 'client'">
                        <!-- Phone Number -->
                        <FormField name="phone_number" v-slot="{ field }" :validate-on-blur="!isFieldDirty">
                            <FormItem>
                                <FormLabel for="phone_number">Phone Number</FormLabel>
                                <FormControl>
                                    <Input
                                        id="phone_number"
                                        type="tel"
                                        autocomplete="tel"
                                        placeholder="Phone number"
                                        v-bind="field"
                                        class="!text-foreground"
                                    />
                                </FormControl>
                                <FormMessage />
                            </FormItem>
                        </FormField>

                        <!-- Improved Gender Selection -->
                        <FormField v-slot="{ componentField }" type="radio" name="gender">
                            <FormItem class="space-y-2">
                                <FormLabel>Gender</FormLabel>
                                <FormControl>
                                    <RadioGroup class="flex flex-wrap items-center gap-4" v-bind="componentField">
                                        <FormItem class="m-0 flex items-center space-y-0 p-0">
                                            <FormControl>
                                                <div
                                                    class="flex cursor-pointer items-center space-x-2 rounded-md border border-input px-3 py-2 shadow-sm transition-colors hover:bg-accent"
                                                    @click="() => setFieldValue('gender', 'male')"
                                                >
                                                    <RadioGroupItem value="male" id="male" />
                                                    <FormLabel for="male" class="cursor-pointer text-sm font-normal">Male</FormLabel>
                                                </div>
                                            </FormControl>
                                        </FormItem>
                                        <FormItem class="m-0 flex items-center space-y-0 p-0">
                                            <FormControl>
                                                <div
                                                    class="flex cursor-pointer items-center space-x-2 rounded-md border border-input px-3 py-2 shadow-sm transition-colors hover:bg-accent"
                                                    @click="() => setFieldValue('gender', 'female')"
                                                >
                                                    <RadioGroupItem value="female" id="female" />
                                                    <FormLabel for="female" class="cursor-pointer text-sm font-normal">Female</FormLabel>
                                                </div>
                                            </FormControl>
                                        </FormItem>
                                    </RadioGroup>
                                </FormControl>
                                <FormMessage />
                            </FormItem>
                        </FormField>

                        <!-- Country Selection -->
                        <FormField name="country" v-slot="{ field }" :validate-on-blur="!isFieldDirty">
                            <FormItem>
                                <FormLabel for="country">Country</FormLabel>
                                <FormControl>
                                    <Select :model-value="field.value" @update:model-value="setFieldValue('country', String($event))">
                                        <SelectTrigger id="country" class="w-full !text-foreground">
                                            <SelectValue placeholder="Select your country" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="country in props.countries" :key="country.id" :value="country.id.toString()">
                                                {{ country.name }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </FormControl>
                                <FormMessage />
                            </FormItem>
                        </FormField>
                    </template>

                    <div v-if="props.mustVerifyEmail && !user.email_verified_at">
                        <p class="text-sm text-muted-foreground">
                            Your email address is unverified.
                            <Link
                                :href="route('verification.send')"
                                method="post"
                                as="button"
                                class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:!decoration-current dark:decoration-neutral-500"
                            >
                                Click here to resend the verification email.
                            </Link>
                        </p>

                        <div v-if="props.status === 'verification-link-sent'" class="mt-2 text-sm font-medium text-green-600">
                            A new verification link has been sent to your email address.
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <Button :disabled="isSubmitting || isProcessing">
                            <LoaderCircle v-if="isSubmitting || isProcessing" class="mr-2 h-4 w-4 animate-spin" />
                            Save Changes
                        </Button>

                        <Transition
                            enter-active-class="transition ease-in-out"
                            enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out"
                            leave-to-class="opacity-0"
                        >
                            <p v-show="!isSubmitting && !isProcessing && Object.keys(errors).length === 0" class="text-sm text-green-600">
                                {{ props.status === 'profile-updated' ? 'Profile updated successfully.' : '' }}
                            </p>
                        </Transition>
                    </div>
                </form>
            </div>

            <DeleteUser />
        </SettingsLayout>
    </AppLayout>
</template>
