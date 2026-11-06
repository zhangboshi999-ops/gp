<template>
  <div class="page">
    <van-nav-bar :title="$t('verification')" left-arrow @click-left="$router.back()" />

    <div style="padding: 12px">
      <div class="card" v-if="status">
        <div class="row-between">
          <span>{{ $t('status') }}</span>
          <van-tag :type="statusTag">{{ statusText }}</van-tag>
        </div>
      </div>

      <div class="card">
        <van-form @submit="submit">
          <van-cell-group inset>
            <van-field
              v-model="form.real_name"
              :label="$t('real_name')"
              :placeholder="$t('real_name')"
              :rules="[{ required: true }]"
            />
            <van-field name="id_type" :label="$t('id_type')">
              <template #input>
                <van-radio-group v-model="form.id_type" direction="horizontal">
                  <van-radio name="idcard">ID</van-radio>
                  <van-radio name="passport">PASSPORT</van-radio>
                  <van-radio name="license">LICENSE</van-radio>
                </van-radio-group>
              </template>
            </van-field>
            <van-field
              v-model="form.id_number"
              :label="$t('id_number')"
              :placeholder="$t('id_number')"
              :rules="[{ required: true }]"
            />
            <van-field v-model="form.country" :label="$t('status')" placeholder="日本" />
          </van-cell-group>

          <div class="card-title" style="margin-top: 14px">{{ $t('upload_front') }}</div>
          <van-uploader v-model="front" :max-count="1" :after-read="(file) => afterRead(file, 'front_image')" />
          <div class="card-title" style="margin-top: 14px">{{ $t('upload_back') }}</div>
          <van-uploader v-model="back" :max-count="1" :after-read="(file) => afterRead(file, 'back_image')" />

          <div style="margin-top: 18px">
            <van-button block type="primary" native-type="submit" :loading="loading">{{ $t('submit') }}</van-button>
          </div>
        </van-form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { showToast } from 'vant'
import { api, uploadFile } from '@/api'
import { useUserStore } from '@/store/user'
import { t } from '@/locales'

const user = useUserStore()
const loading = ref(false)
const front = ref([])
const back = ref([])
const status = ref('')
const form = ref({
  real_name: '',
  id_number: '',
  id_type: 'idcard',
  country: '日本',
  front_image: '',
  back_image: ''
})

const statusMap = {
  pending: { text: 'kyc_pending', tag: 'warning' },
  pass: { text: 'kyc_pass', tag: 'success' },
  reject: { text: 'kyc_reject', tag: 'danger' },
  none: { text: 'kyc_none', tag: 'default' }
}

const statusText = computed(() => t((statusMap[status.value] || statusMap.none).text))
const statusTag = computed(() => (statusMap[status.value] || statusMap.none).tag)

async function afterRead(file, field) {
  try {
    if (Array.isArray(file)) {
      file = file[0]
    }
    const url = await uploadFile(file.file)
    form.value[field] = url
    showToast(t('submit_success'))
  } catch (e) {
    showToast(t('network_error'))
  }
}

async function submit() {
  loading.value = true
  try {
    await api.verify(form.value)
    showToast(t('submit_success'))
    status.value = 'pending'
    await user.fetchInfo()
  } catch (e) {
    showToast(e.message || t('network_error'))
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  try {
    const data = await api.myVerify()
    status.value = data.status || 'none'
    if (data.real_name) {
      form.value.real_name = data.real_name
      form.value.id_type = data.id_type
      form.value.country = data.country
    }
  } catch (e) {
    status.value = 'none'
  }
})
</script>
