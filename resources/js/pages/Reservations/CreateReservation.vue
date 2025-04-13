<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { toast } from '@/components/ui/toast/use-toast';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { onMounted, onUnmounted, ref } from 'vue';

// Define props
const props = defineProps({
    room: {
        type: Object,
        required: true,
    },
});

// Form and state management
const form = ref({
    room_number: props.room.number,
    accompany_number: 0,
    payment_method_id: '',
    return_url: window.location.origin,
    automatic_payment_methods: true,
});

// Stripe elements and state
const stripe = ref(null);
const card = ref(null);
const isSubmitting = ref(false);
const cardErrors = ref('');
const stripeReady = ref(false);
const stripeKey = ref(import.meta.env.VITE_STRIPE_KEY || '');
const cardMounted = ref(false);

// Single function to handle Stripe initialization and mounting
const initializeStripe = async () => {
    try {
        // Load Stripe.js if needed
        if (typeof window.Stripe !== 'function') {
            await new Promise((resolve, reject) => {
                const script = document.createElement('script');
                script.src = 'https://js.stripe.com/v3/';
                script.async = true;
                script.onload = resolve;
                script.onerror = reject;
                document.head.appendChild(script);
            });
        }

        // Check for Stripe key
        if (!stripeKey.value) {
            toast({
                title: 'Configuration Error',
                description: 'Payment system is not properly configured. Please contact support.',
                variant: 'destructive',
            });
            return;
        }

        // Initialize Stripe and create card element
        stripe.value = window.Stripe(stripeKey.value);
        const elements = stripe.value.elements();

        card.value = elements.create('card', {
            style: {
                base: {
                    fontSize: '16px',
                    color: document.documentElement.classList.contains('dark') ? '#ffffff' : '#32325d',
                    fontFamily: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
                    '::placeholder': { color: document.documentElement.classList.contains('dark') ? '#a1a1aa' : '#aab7c4' },
                },
                invalid: {
                    color: '#fa755a',
                    iconColor: '#fa755a',
                },
            },
            classes: {
                base: 'stripe-element',
                focus: 'stripe-element-focus',
                invalid: 'stripe-element-invalid',
            },
            hidePostalCode: true,
        });

        // Set up card error handling
        card.value.on('change', (event) => {
            cardErrors.value = event.error ? event.error.message : '';
        });

        stripeReady.value = true;

        // Wait a bit before attempting to mount
        setTimeout(() => {
            mountCardElement();
        }, 500);
    } catch (error) {
        console.error('Error initializing Stripe:', error);
        toast({
            title: 'Error',
            description: 'Failed to initialize payment system. Please refresh and try again.',
            variant: 'destructive',
        });
    }
};

// Separate function to mount card element
const mountCardElement = () => {
    try {
        const cardElement = document.getElementById('card-element');
        if (!cardElement) {
            console.warn('Card element not found in DOM');
            // Try again after a delay
            setTimeout(mountCardElement, 500);
            return;
        }

        if (cardMounted.value) {
            return;
        }

        // Clean the element before mounting
        while (cardElement.firstChild) {
            cardElement.removeChild(cardElement.firstChild);
        }

        // Mount the card element
        card.value.mount('#card-element');
        cardMounted.value = true;

        // Ensure iframe is visible by adding styles after mounting
        setTimeout(() => {
            const iframe = cardElement.querySelector('iframe');
            if (iframe) {
                iframe.style.opacity = '1';
                iframe.style.height = '24px';
                iframe.style.display = 'block';
                iframe.style.width = '100%';
            }
        }, 300);
    } catch (error) {
        console.error('Error mounting card element:', error);
        // Don't try again automatically to avoid infinite loops
        toast({
            title: 'Error',
            description: 'Could not set up payment form. Please refresh the page.',
            variant: 'destructive',
        });
    }
};

// Handle form submission
const submit = async () => {
    // Validate capacity
    if (form.value.accompany_number > props.room.capacity) {
        toast({
            title: 'Error',
            description: 'Accompany number cannot exceed room capacity.',
            variant: 'destructive',
        });
        return;
    }

    if (!cardMounted.value || !stripeReady.value) {
        toast({
            title: 'Payment System Not Ready',
            description: 'Please wait for the payment system to initialize.',
            variant: 'destructive',
        });
        return;
    }

    isSubmitting.value = true;

    try {
        // Create payment method
        const result = await stripe.value.createPaymentMethod({
            type: 'card',
            card: card.value,
        });

        if (result.error) {
            cardErrors.value = result.error.message;
            isSubmitting.value = false;
            return;
        }

        // Add the payment method ID to the form data
        form.value.payment_method_id = result.paymentMethod.id;

        // Ensure return_url is set - but don't double the domain
        if (!form.value.return_url) {
            form.value.return_url = window.location.origin;
        }
        // Submit the form to backend
        router.post(route('reservations.store'), form.value, {
            onSuccess: () => {
                toast({
                    title: 'Success',
                    description: 'Your reservation has been confirmed!',
                    variant: 'default',
                });
            },
            onError: (errors) => {
                // Try to handle error response that might be in non-standard format
                let errorMessage = 'Something went wrong.';

                if (typeof errors === 'string') {
                    errorMessage = errors;
                } else if (errors.message) {
                    errorMessage = errors.message;
                } else if (errors.error) {
                    errorMessage = errors.error;
                }

                toast({
                    title: 'Error',
                    description: errorMessage,
                    variant: 'destructive',
                });
            },
            onFinish: () => {
                isSubmitting.value = false;
            },
        });
    } catch (e) {
        console.error('Payment processing error:', e);
        toast({
            title: 'Error',
            description: 'Payment processing failed. Please try again.',
            variant: 'destructive',
        });
        isSubmitting.value = false;
    }
};

// Lifecycle hooks
onMounted(() => {
    initializeStripe();
});

onUnmounted(() => {
    if (card.value && cardMounted.value) {
        try {
            card.value.unmount();
        } catch (error) {
            console.error('Error unmounting card element:', error);
        }
    }
});

// Note: Don't pass breadcrumbs to PublicLayout - it doesn't accept this prop
const navItems = [
    { title: 'Home', href: route('home') },
    { title: 'Make a Reservation', href: route('reservations.make') },
    { title: `Room ${props.room.number}`, active: true },
];
</script>

<template>
    <Head title="Make Reservation" />
    <PublicLayout>
        <div class="container mx-auto px-4 py-8">
            <!-- Breadcrumbs navigation rendered manually -->
            <div class="mb-4 flex text-sm">
                <div class="flex items-center">
                    <template v-for="(item, index) in navItems" :key="index">
                        <a v-if="!item.active" :href="item.href" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                            {{ item.title }}
                        </a>
                        <span v-else class="font-medium text-gray-900 dark:text-gray-100">{{ item.title }}</span>

                        <span v-if="index < navItems.length - 1" class="mx-2 text-gray-400">/</span>
                    </template>
                </div>
            </div>

            <Card class="mx-auto w-full max-w-md">
                <CardHeader>
                    <CardTitle>Make Reservation for Room {{ props.room.number }}</CardTitle>
                    <CardDescription>Fill in the details below to complete your reservation.</CardDescription>
                </CardHeader>

                <CardContent class="grid gap-4">
                    <!-- Accompanying people input -->
                    <div class="grid gap-2">
                        <Label for="accompany_number">Number of Accompanying People</Label>
                        <Input
                            type="number"
                            id="accompany_number"
                            v-model.number="form.accompany_number"
                            min="0"
                            :max="props.room.capacity"
                            placeholder="Number of people"
                        />
                        <p class="text-sm text-gray-500 dark:text-gray-400">Maximum capacity for this room is {{ props.room.capacity }} people.</p>
                    </div>

                    <!-- Stripe Card Element -->
                    <div class="grid gap-2">
                        <Label for="card-element">Credit or debit card</Label>
                        <div
                            id="card-element"
                            class="flex min-h-[40px] items-center justify-center rounded-md border border-input bg-white p-3 shadow-sm dark:border-gray-700 dark:bg-gray-800"
                            style="min-height: 40px; position: relative"
                        >
                            <div v-if="!stripeReady" class="text-sm text-gray-500">
                                <svg
                                    class="-ml-1 mr-3 inline-block h-5 w-5 animate-spin text-primary"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path
                                        class="opacity-75"
                                        fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                    ></path>
                                </svg>
                                Loading payment form...
                            </div>
                        </div>

                        <div v-if="cardErrors" class="mt-1 text-sm text-red-500 dark:text-red-400">{{ cardErrors }}</div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Enter your card details above. This form is secure and encrypted.</p>
                    </div>

                    <!-- Status indicator - simplified -->
                    <div v-if="!cardMounted" class="rounded-md bg-gray-100 p-2 text-xs text-gray-500 dark:bg-gray-700 dark:text-gray-400">
                        <p>Payment system is initializing... {{ stripeKey ? 'Stripe API key is available.' : 'Stripe API key is missing!' }}</p>
                    </div>

                    <!-- Price summary -->
                    <div class="mt-4 rounded-lg bg-gray-50 p-4 dark:bg-gray-800">
                        <h3 class="mb-2 font-medium">Reservation Summary</h3>
                        <div class="flex justify-between border-b pb-2">
                            <span>Room {{ props.room.number }}</span>
                            <span>{{ new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(props.room.price) }}</span>
                        </div>
                        <div class="mt-2 flex justify-between font-semibold">
                            <span>Total</span>
                            <span>{{ new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(props.room.price) }}</span>
                        </div>
                    </div>
                </CardContent>

                <CardFooter>
                    <Button @click="submit" class="w-full" :disabled="isSubmitting || !cardMounted">
                        <span v-if="isSubmitting">
                            <svg
                                class="-ml-1 mr-3 inline-block h-5 w-5 animate-spin text-white"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                            >
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path
                                    class="opacity-75"
                                    fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                ></path>
                            </svg>
                            Processing...
                        </span>
                        <span v-else-if="!cardMounted">Loading Payment System...</span>
                        <span v-else>Pay Now</span>
                    </Button>
                </CardFooter>
            </Card>
        </div>
    </PublicLayout>
</template>

<style scoped>
/* Add specific styling for Stripe Elements */
:deep(.stripe-element) {
    width: 100%;
    padding: 5px;
}

:deep(.stripe-element-focus) {
    border-color: var(--focus-border-color, #80bdff);
    outline: 0;
    box-shadow: 0 0 0 0.2rem var(--focus-shadow-color, rgba(0, 123, 255, 0.25));
}

:deep(.stripe-element-invalid) {
    border-color: var(--error-color, #dc3545);
}

/* Ensure iframe has proper display */
:deep(#card-element iframe) {
    display: block !important;
    opacity: 1 !important;
    height: 24px !important;
    width: 100% !important;
}

/* Define CSS variables for theme support */
:root {
    --focus-border-color: #80bdff;
    --focus-shadow-color: rgba(0, 123, 255, 0.25);
    --error-color: #dc3545;
}

:root.dark {
    --focus-border-color: #2563eb;
    --focus-shadow-color: rgba(37, 99, 235, 0.25);
    --error-color: #ef4444;
}
</style>
