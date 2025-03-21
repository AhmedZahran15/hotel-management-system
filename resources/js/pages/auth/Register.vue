<script setup lang="ts">
import TextLink from '@/components/TextLink.vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import { Input } from '@/components/ui/input';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { toTypedSchema } from '@vee-validate/zod';
import { AlertCircle, LoaderCircle } from 'lucide-vue-next';
import { useForm } from 'vee-validate';
import { computed, ref } from 'vue';
import { z } from 'zod';

// Define Country interface
interface Country {
    id: number;
    name: string;
}

const page = usePage();
const errors = computed(() => page.props.errors);
const countries = computed<Country[]>(() => page.props.countries || []);

const profilePicture = ref<File | null>(null);
const profilePicturePreview = ref<string | null>(null);

// Define validation schema with zod
const validationSchema = toTypedSchema(
    z
        .object({
            name: z.string().min(2, 'Name is required'),
            email: z.string().email('Invalid email address'),
            password: z.string().min(8, 'Password must be at least 8 characters long'),
            password_confirmation: z.string().min(1, 'Password confirmation is required'),
            gender: z.enum(['male', 'female'], { required_error: 'Please select a gender' }),
            country: z.string().min(1, 'Please select a country'),
            profile_picture: z.any().refine((val) => val !== null, { message: 'Profile picture is required' }),
        })
        .refine((data) => data.password === data.password_confirmation, {
            message: "Passwords don't match",
            path: ['password_confirmation'],
        }),
);

// Use vee-validate's useForm
const { handleSubmit, isFieldDirty, isSubmitting, setFieldError, setFieldValue } = useForm({
    validationSchema,
    initialValues: {
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        gender: 'male',
        country: '',
        profile_picture: null,
    },
});

// Track Inertia form submission state
const isProcessing = ref(false);

// Handle file upload
const handleFileUpload = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        const file = target.files[0];

        // Check if the file is a JPG or JPEG
        const allowedTypes = ['image/jpeg', 'image/jpg'];
        if (!allowedTypes.includes(file.type)) {
            alert('Only JPG/JPEG files are allowed');
            // Reset the file input
            target.value = '';
            return;
        }

        profilePicture.value = file;
        profilePicturePreview.value = URL.createObjectURL(file);

        // Update form field value to clear error
        setFieldValue('profile_picture', file);
    }
};

// Handle file removal
const removeProfilePicture = () => {
    profilePicture.value = null;
    profilePicturePreview.value = null;
    // Reset the file input
    const fileInput = document.getElementById('profile-picture') as HTMLInputElement;
    if (fileInput) fileInput.value = '';
};

// Submit handler
const onSubmit = handleSubmit((values) => {
    if (!profilePicture.value) {
        setFieldError('profile_picture', 'Profile picture is required');
        return;
    }
    isProcessing.value = true;
    // Create form data for file upload
    const formData = new FormData();
    formData.append('name', values.name);
    formData.append('email', values.email);
    formData.append('password', values.password);
    formData.append('password_confirmation', values.password_confirmation);
    formData.append('gender', values.gender);
    formData.append('country', values.country);
    formData.append('profile_picture', profilePicture.value);
    router.post(route('register'), formData, {
        onFinish: () => {
            values.password = '';
            values.password_confirmation = '';
            isProcessing.value = false;
        },
    });
});
</script>

<template>
    <AuthBase title="Create an account" description="Enter your details below to create your account">
        <Head title="Register" />

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
                <!-- Improved Profile Picture Upload -->
                <div class="mx-auto flex flex-col items-center gap-3">
                    <div
                        class="relative h-28 w-28 overflow-hidden rounded-full border-2 border-dashed border-muted-foreground/25 bg-muted transition-all hover:border-primary/50"
                    >
                        <img v-if="profilePicturePreview" :src="profilePicturePreview" alt="Profile Preview" class="h-full w-full object-cover" />
                        <div v-else class="flex h-full w-full flex-col items-center justify-center p-2 text-center text-sm text-muted-foreground">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                class="mb-1 h-8 w-8 opacity-50"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.5"
                                    d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"
                                />
                            </svg>
                            Profile Photo
                        </div>

                        <!-- Remove button that appears when an image is selected -->
                        <button
                            v-if="profilePicturePreview"
                            type="button"
                            @click="removeProfilePicture"
                            class="absolute right-0 top-0 rounded-full bg-destructive p-1 text-white shadow-sm transition-transform hover:scale-110"
                            title="Remove image"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
                                <path
                                    d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z"
                                />
                            </svg>
                        </button>
                    </div>

                    <div class="flex flex-wrap items-center justify-center gap-2">
                        <label
                            for="profile-picture"
                            class="inline-flex cursor-pointer items-center gap-1 rounded-md border border-input bg-background px-3 py-1 text-xs font-medium text-primary hover:bg-accent hover:text-accent-foreground"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
                                <path
                                    d="M9.25 13.25a.75.75 0 001.5 0V4.636l2.955 3.129a.75.75 0 001.09-1.03l-4.25-4.5a.75.75 0 00-1.09 0l-4.25 4.5a.75.75 0 101.09 1.03L9.25 4.636v8.614z"
                                />
                                <path
                                    d="M3.5 12.75a.75.75 0 00-1.5 0v2.5A2.75 2.75 0 004.75 18h10.5A2.75 2.75 0 0018 15.25v-2.5a.75.75 0 00-1.5 0v2.5c0 .69-.56 1.25-1.25 1.25H4.75c-.69 0-1.25-.56-1.25-1.25v-2.5z"
                                />
                            </svg>
                            Upload Photo
                        </label>
                        <p class="text-xs font-medium text-destructive">Required</p>
                    </div>
                    <input id="profile-picture" type="file" accept="image/jpeg,image/jpg" class="hidden" @change="handleFileUpload" />
                    <p class="text-xs text-muted-foreground">JPG/JPEG format only, max 2MB</p>

                    <!-- Fix: Wrap FormMessage in FormItem -->
                    <FormField name="profile_picture" v-slot="{ errorMessage }">
                        <FormItem class="mt-0">
                            <FormMessage v-if="errorMessage" class="mt-0">{{ errorMessage }}</FormMessage>
                        </FormItem>
                    </FormField>
                </div>

                <!-- Name -->
                <FormField name="name" v-slot="{ field }" :validate-on-blur="!isFieldDirty">
                    <FormItem>
                        <FormLabel for="name">Name</FormLabel>
                        <FormControl>
                            <Input id="name" type="text" autofocus :tabindex="1" autocomplete="name" placeholder="Full name" v-bind="field" />
                        </FormControl>
                        <FormMessage />
                    </FormItem>
                </FormField>

                <!-- Email -->
                <FormField name="email" v-slot="{ field }" :validate-on-blur="!isFieldDirty">
                    <FormItem>
                        <FormLabel for="email">Email address</FormLabel>
                        <FormControl>
                            <Input id="email" type="email" :tabindex="2" autocomplete="email" placeholder="email@example.com" v-bind="field" />
                        </FormControl>
                        <FormMessage />
                    </FormItem>
                </FormField>

                <!-- Password -->
                <FormField name="password" v-slot="{ field }" :validate-on-blur="!isFieldDirty">
                    <FormItem>
                        <FormLabel for="password">Password</FormLabel>
                        <FormControl>
                            <Input id="password" type="password" :tabindex="3" autocomplete="new-password" placeholder="Password" v-bind="field" />
                        </FormControl>
                        <FormMessage />
                    </FormItem>
                </FormField>

                <!-- Password Confirmation -->
                <FormField name="password_confirmation" v-slot="{ field }" :validate-on-blur="!isFieldDirty">
                    <FormItem>
                        <FormLabel for="password_confirmation">Confirm password</FormLabel>
                        <FormControl>
                            <Input
                                id="password_confirmation"
                                type="password"
                                :tabindex="4"
                                autocomplete="new-password"
                                placeholder="Confirm password"
                                v-bind="field"
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
                                            @click="setFieldValue('gender', 'male')"
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
                                            @click="setFieldValue('gender', 'female')"
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
                            <Select v-bind="field">
                                <SelectTrigger id="country" :tabindex="5" class="w-full">
                                    <SelectValue placeholder="Select your country" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="country in countries" :key="country.id" :value="country.id.toString()">
                                        {{ country.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </FormControl>
                        <FormMessage />
                    </FormItem>
                </FormField>

                <Button type="submit" class="mt-2 w-full" tabindex="6" :disabled="isSubmitting || isProcessing">
                    <LoaderCircle v-if="isSubmitting || isProcessing" class="mr-2 h-4 w-4 animate-spin" />
                    Create account
                </Button>
            </div>

            <div class="text-center text-sm text-muted-foreground">
                Already have an account?
                <TextLink :href="route('login')" class="underline underline-offset-4" :tabindex="7">Log in</TextLink>
            </div>
        </form>
    </AuthBase>
</template>
