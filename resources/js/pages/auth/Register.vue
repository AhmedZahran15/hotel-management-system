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
import { AlertCircle, LoaderCircle, Upload } from 'lucide-vue-next';
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
const countries = computed<Country[]>(() => (Array.isArray(page.props.countries) ? (page.props.countries as Country[]) : []));
const avatarImage = ref<File | null>(null);
const avatarImagePreview = ref<string | null>(null);

// Define validation schema with zod
const validationSchema = toTypedSchema(
    z
        .object({
            name: z.string().min(1, 'Name is required').min(3, 'Name must be at least 3 characters long'),
            email: z.string().email('Invalid email address'),
            password: z.string().min(8, 'Password must be at least 8 characters long'),
            password_confirmation: z.string().min(1, 'Password confirmation is required'),
            gender: z.enum(['male', 'female'], { required_error: 'Please select a gender' }),
            country: z.string().min(1, 'Please select a country'),
            phone_number: z
                .string()
                .min(1, 'Phone number is required')
                .regex(/^\+?[0-9]{8,15}$/, 'Please enter a valid phone number (8-15 digits, may start with +)'),
            avatar_image: z.any().refine((val) => val !== null, { message: 'Profile picture is required' }),
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
        phone_number: '',
        avatar_image: null,
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

// Submit handler
const onSubmit = handleSubmit((values) => {
    if (!avatarImage.value) {
        setFieldError('avatar_image', 'Profile picture is required');
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
    formData.append('phone_number', values.phone_number);
    formData.append('avatar_image', avatarImage.value);

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

                <!-- Phone Number -->
                <FormField name="phone_number" v-slot="{ field }" :validate-on-blur="!isFieldDirty">
                    <FormItem>
                        <FormLabel for="phone_number">Phone Number</FormLabel>
                        <FormControl>
                            <Input id="phone_number" type="tel" :tabindex="3" autocomplete="tel" placeholder="Phone number" v-bind="field" />
                        </FormControl>
                        <FormMessage />
                    </FormItem>
                </FormField>

                <!-- Password -->
                <FormField name="password" v-slot="{ field }" :validate-on-blur="!isFieldDirty">
                    <FormItem>
                        <FormLabel for="password">Password</FormLabel>
                        <FormControl>
                            <Input id="password" type="password" :tabindex="4" autocomplete="new-password" placeholder="Password" v-bind="field" />
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
                                :tabindex="5"
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
                                <SelectTrigger id="country" :tabindex="6" class="w-full">
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

                <Button type="submit" class="mt-2 w-full" tabindex="7" :disabled="isSubmitting || isProcessing">
                    <LoaderCircle v-if="isSubmitting || isProcessing" class="mr-2 h-4 w-4 animate-spin" />
                    Create account
                </Button>
            </div>

            <div class="text-center text-sm text-muted-foreground">
                Already have an account?
                <TextLink :href="route('login')" class="underline underline-offset-4" :tabindex="8">Log in</TextLink>
            </div>
        </form>
    </AuthBase>
</template>
