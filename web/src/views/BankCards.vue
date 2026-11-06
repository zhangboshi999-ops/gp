<template>
  <div class="page">
    <van-nav-bar :title="$t('bank_card')" left-arrow @click-left="$router.back()" />

    <div style="padding: 12px">
      <div class="card">
        <div
          v-for="item in list"
          :key="item.id"
          style="padding: 12px 0; border-bottom: 1px solid var(--border)"
        >
          <div class="row-between">
            <div>
              <div style="font-weight: 600">{{ item.bank_name }} <span class="muted">{{ item.branch_name }}</span></div>
              <div class="muted">{{ item.account_type }} · {{ item.account_no }} · {{ item.account_name }}</div>
            </div>
            <van-button size="mini" plain type="danger" @click="remove(item.id)">{{ $t('cancel') }}</van-button>
          </div>
        </div>
        <EmptyState v-if="!list.length" icon="card" />
      </div>

      <van-button block type="primary" @click="show = true">+ {{ $t('bank_card') }}</van-button>
    </div>

    <!-- 居中卡片弹窗：teleport 到 body 避免被页面布局影响；
         用 before-close 控制关闭时机，校验不通过 / 提交失败都不关闭 -->
    <van-dialog
      v-model:show="show"
      :title="$t('add_bank_card')"
      show-cancel-button
      teleport="body"
      class="bank-dialog"
      :before-close="beforeClose"
      :close-on-click-overlay="false"
      :cancel-button-text="$t('cancel')"
      :confirm-button-text="$t('confirm')"
    >
      <div class="dialog-body">
        <van-field v-model="form.bank_name" :label="$t('bank_name')" :placeholder="$t('bank_name')" />
        <van-field v-model="form.branch_name" :label="$t('branch_name')" :placeholder="$t('branch_name')" />
        <van-field v-model="form.account_no" :label="$t('account_no')" :placeholder="$t('account_no')" />
        <van-field v-model="form.account_name" :label="$t('account_name')" :placeholder="$t('account_name')" />
        <van-field :label="$t('id_type')">
          <template #input>
            <van-radio-group v-model="form.account_type" direction="horizontal">
              <van-radio name="普通">普通</van-radio>
              <van-radio name="当座">当座</van-radio>
            </van-radio-group>
          </template>
        </van-field>
      </div>
    </van-dialog>
  </div>
</template>

<script setup>
import EmptyState from '@/components/EmptyState.vue'
import { onMounted, reactive, ref } from 'vue'
import { showToast, showLoadingToast, closeToast } from 'vant'
import { api } from '@/api'
import { t } from '@/locales'

const list = ref([])
const show = ref(false)
const submitting = ref(false)
const form = reactive({
  bank_name: '',
  branch_name: '',
  account_no: '',
  account_name: '',
  account_type: '普通'
})

async function load() {
  try {
    list.value = await api.banks()
  } catch (e) {
    list.value = []
  }
}

/**
 * 弹窗关闭前的钩子（Vant 4 的 beforeClose 没有 done 回调）：
 * 返回 true / resolve(true)  → 关闭
 * 返回 false / resolve(false) → 保持打开
 * 所以校验不通过、接口报错都不关闭，避免用户白填一遍
 */
async function beforeClose(action) {
  if (action !== 'confirm') {
    return true
  }
  if (!form.bank_name.trim() || !form.account_no.trim() || !form.account_name.trim()) {
    showToast(t('fill_bank_info'))
    return false
  }
  submitting.value = true
  const loading = showLoadingToast({ message: t('submitting'), forbidClick: true, duration: 0 })
  try {
    await api.addBank({ ...form })
    showToast(t('operation_success'))
    Object.assign(form, { bank_name: '', branch_name: '', account_no: '', account_name: '' })
    await load()
    return true
  } catch (e) {
    showToast(e.message || t('network_error'))
    return false
  } finally {
    loading.close()
    submitting.value = false
  }
}

async function remove(id) {
  try {
    await api.delBank({ id })
    showToast(t('operation_success'))
    load()
  } catch (e) {
    showToast(e.message || t('network_error'))
  }
}

onMounted(load)
</script>

<!-- 弹窗被 teleport 到 body，这里用不带 scoped 的样式块保证生效 -->
<style>
.bank-dialog {
  width: 86%;
  max-width: 340px;
  border-radius: 16px;
  overflow: hidden;
}

.bank-dialog .van-dialog__header {
  padding-top: 18px;
  font-weight: 700;
}

.bank-dialog .van-dialog__content {
  padding: 0 4px;
}

.dialog-body {
  padding: 4px 0 8px;
  max-height: 56vh;
  overflow-y: auto;
}
</style>
