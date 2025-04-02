<script setup lang="ts">
import { defineProps, defineEmits, ref, watch } from 'vue';
import { useForm } from 'vee-validate';
import { toTypedSchema } from '@vee-validate/zod';
import { Button } from '@/components/ui/button';
import { AutoForm } from '@/components/ui/auto-form';
import { Input } from '@/components/ui/input';

const props = defineProps({
  schema: Object,
  submitText: String,
  initialValues: Object
});

const emit = defineEmits(["submit","cancel"]);
const form = useForm({
  validationSchema: toTypedSchema(props.schema),
  initialValues: props.initialValues || {}
});

// Handle file input manually
const selectedFile = ref<File | null>(null);

const handleFileChange = (event: Event) => {
  const input = event.target as HTMLInputElement;
  if (input.files && input.files.length > 0) {
    selectedFile.value = input.files[0];
  }
};

watch(selectedFile, (newFile) => {
  if (newFile) {
    form.setFieldValue('image', newFile);
  }
});
</script>

<template>
  <div class="flex flex-col items-center justify-center w-full h-full p-4">
    <AutoForm
      class="w-2/3 space-y-6"
      :schema="schema"
      :form="form"
      @submit="emit('submit', { ...form.values, image: selectedFile })"
    >
      <!-- Custom File Input -->
      <div class="form-group">
        <label for="image">Room Image</label>
        <Input type="file" id="image" @change="handleFileChange" />
      </div>

      <Button type="submit">
        {{ props.submitText ?? 'Submit' }}
      </Button>
      <Button class="mx-4" :variant="'secondary'" @click="$emit('cancel')" >
        Cancel
      </Button>
    </AutoForm>
  </div>
</template>
