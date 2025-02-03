<script setup>
import {Head, useForm} from '@inertiajs/vue3'
import ImageAuthentication from "@/Components/User/Image/ImageAuthentication.vue"
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue'
import HeadingAuth from '@/Components/User/Heading/HeadingAuth.vue'
import InputError from '@/Components/InputError.vue'
import InputLabel from '@/Components/InputLabel.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import TextInput from '@/Components/TextInput.vue'
import { useI18n } from 'vue-i18n'

defineProps({
    status: String
})

const form = useForm({
    email: ''
})

const submit = () => {
    form.post(route('password.email'))
}

const {t} = useI18n();
</script>

<template>
    <Head :title="t('passwordRecovery')"/>

    <div class="flex flex-row flex-wrap w-full">
        <!-- Content -->
        <div class="w-full md:w-1/2">
            <div class="min-h-screen h-full flex flex-col justify-center items-center">

                <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
                    {{ status }}
                </div>

                <div class="flex flex-col justify-center items-center max-w-sm mx-auto px-4 py-8">
                    <div class="mb-4 flex flex-col justify-center items-center">
                        <!-- Logo -->
                        <AuthenticationCardLogo/>
                    </div>
                    <div>

                        <!-- Heading -->
                        <HeadingAuth>{{ t('passwordReset') }}</HeadingAuth>

                        <div class="mb-4 font-semibold text-md text-gray-900">
                            {{ t('forgotPasswordMessage') }}
                        </div>

                        <!-- Form -->
                        <form @submit.prevent="submit">
                            <div>
                                <InputLabel for="email" :value="t('email')"/>
                                <TextInput
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    class="mt-1 block w-full"
                                    required
                                    autofocus
                                    autocomplete="username"
                                />
                                <InputError class="mt-2" :message="form.errors.email"/>
                            </div>

                            <div class="flex items-center justify-center mt-4">
                                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                    {{ t('resetPasswordLink') }}
                                </PrimaryButton>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
        <!-- Image -->
        <ImageAuthentication/>
    </div>
</template>
