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
  initialValues: Object,
  fieldConfig: Object
});

const emit = defineEmits(["submit","cancel"]);
const form = useForm({
  validationSchema: toTypedSchema(props.schema),
  initialValues: props.initialValues || {}
});

</script>

<template>
  <div class="flex flex-col items-center justify-center w-full h-full p-4">
    <AutoForm
      class="w-2/3 space-y-6"
      :schema="schema"
      :form="form"
      :field-config="fieldConfig"
      @submit="emit('submit', form.values )"
    >
    <slot></slot>

      <Button type="submit">
        {{ props.submitText ?? 'Submit' }}
      </Button>
      <Button class="mx-3" :variant="'secondary'" @click="$emit('cancel')" >
        Cancel
      </Button>
    </AutoForm>
  </div>
</template>
