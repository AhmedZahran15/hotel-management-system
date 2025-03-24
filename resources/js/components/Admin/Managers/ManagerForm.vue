<script setup>
import { ref, watch } from "vue";
import axios from "axios";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Button } from "@/components/ui/button";

const props = defineProps({
  manager: Object,
});

const emit = defineEmits(["close", "refresh"]);

const form = ref({
  name: "",
  email: "",
  password: "",
  national_id: "",
  avatar_image: null,
});

const handleFileChange = (event) => {
  const file = event.target.files[0];
  if (file) {
    form.value.avatar_image = file;
  }
};

// ✅ Prefill form when editing
watch(
  () => props.manager,
  (newVal) => {
    if (newVal) {
      form.value = {
        name: newVal.name,
        email: newVal.email,
        national_id: newVal.profile?.national_id || "",
        password: "",
        avatar_image: null,
      };
    } else {
      form.value = {
        name: "",
        email: "",
        password: "",
        national_id: "",
        avatar_image: null,
      };
    }
  }
);

const submitForm = async () => {
  const formData = new FormData();
  Object.keys(form.value).forEach((key) => {
    if (form.value[key] !== null) {
      formData.append(key, form.value[key]);
    }
  });

  try {
    if (props.manager) {
      await axios.post(`/dashboard/managers/${props.manager.id}`, formData);
    } else {
      await axios.post("/dashboard/managers", formData);
    }

    emit("close");
    emit("refresh");
  } catch (error) {
    console.error("❌ Error:", error.response?.data || error.message);
  }
};
</script>

<template>
  <form @submit.prevent="submitForm" class="space-y-4">
    <div>
      <Label for="name">Name</Label>
      <Input id="name" v-model="form.name" type="text" required />
    </div>

    <div>
      <Label for="email">Email</Label>
      <Input id="email" v-model="form.email" type="email" required />
    </div>

    <div v-if="!props.manager">
      <Label for="password">Password</Label>
      <Input id="password" v-model="form.password" type="password" required />
    </div>

    <div>
      <Label for="national_id">National ID</Label>
      <Input id="national_id" v-model="form.national_id" type="text" required />
    </div>

    <div>
      <Label for="avatar">Avatar</Label>
      <Input id="avatar" type="file" @change="handleFileChange" />
    </div>

    <!-- ✅ No extra buttons here, since `ManageModal.vue` will handle it -->
  </form>
</template>
