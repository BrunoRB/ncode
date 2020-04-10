<template>
  <div>
    <div class="container">
      <div v-if="loadingAccount">
        <b-overlay :show="true" rounded="sm">
          <h2>Loading account...</h2>
        </b-overlay>
      </div>
      <div v-else-if="accountError">
        <b-alert variant="danger" show>
          {{ accountError }}
        </b-alert>

        <b-button class="float-left" size="sm" nuxt-link to="/">
          Go back
        </b-button>
      </div>
      <div v-else>
        <b-card :header="'Welcome, ' + account.name" class="mt-3">
          <b-card-text>
            <div>
              Account: <code>{{ account.id }}</code>
            </div>
            <div>Balance: {{ account.balance }}</div>
          </b-card-text>
          <b-button size="sm" variant="success" @click="show = !show">
            New payment
          </b-button>

          <b-button
            class="float-right"
            variant="danger"
            size="sm"
            nuxt-link
            to="/"
          >
            Logout
          </b-button>
        </b-card>

        <b-card v-show="show" class="mt-3" header="New Payment">
          <b-form @submit.prevent="onSubmit">
            <b-form-group id="input-group-1" label="To:" label-for="input-1">
              <b-form-input
                id="input-1"
                v-model="payment.to"
                size="sm"
                type="number"
                min="1"
                required
                placeholder="Destination ID"
              />
            </b-form-group>

            <b-form-group
              id="input-group-2"
              label="Amount:"
              label-for="input-2"
            >
              <currency-input
                v-model="payment.amount"
                :currency="account.currency"
              />
            </b-form-group>

            <b-form-group
              id="input-group-3"
              label="Details:"
              label-for="input-3"
            >
              <b-form-textarea
                id="input-3"
                v-model="payment.details"
                size="sm"
                placeholder="Payment details"
              />
            </b-form-group>

            <b-button
              type="submit"
              size="sm"
              variant="primary"
              :disabled="loadingNewTransaction"
            >
              Submit
              <b-spinner v-if="loadingNewTransaction" small />
            </b-button>
          </b-form>
        </b-card>

        <div v-if="loadingTransactions">
          <b-overlay :show="true" rounded="sm">
            <h2>Loading transactions...</h2>
          </b-overlay>
        </div>
        <div v-else-if="transactionsError">
          <b-alert variant="danger" show>
            {{ transactionsError }}
          </b-alert>
        </div>
        <div v-else>
          <b-card class="mt-3" header="Payment History">
            <b-table
              id="transactionTable"
              striped
              hover
              :items="transactions"
              :per-page="transactionsPaginationData.meta.per_page"
              :current-page="transactionsPaginationData.meta.path"
            >
              <template v-slot:cell(details)="data">
                <span
                  :title="data.value"
                  v-text="data.value ? data.value.substr(0, 20) + '...' : ''"
                />
              </template>
            </b-table>

            <b-pagination
              v-model="transactionsCurrentPage"
              :total-rows="transactionsPaginationData.meta.total"
              :per-page="transactionsPaginationData.meta.per_page"
              aria-controls="transactionTable"
            />
          </b-card>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import axios from "axios"
import moment from "moment"

export default {
  data() {
    return {
      show: false,

      payment: {},
      loadingPayment: false,
      loadingNewTransaction: false,

      account: null,
      accountError: "",
      loadingAccount: true,

      transactions: null,
      transactionsPaginationData: {
        links: null,
        meta: null,
      },
      transactionsCurrentPage: 1,
      transactionsError: "",
      loadingTransactions: true,
    }
  },

  watch: {
    transactionsCurrentPage: function (val) {
      let current = 1
      if (!isNaN(this.$route.query.page) && this.$route.query.page > 0) {
        current = this.$route.query.page
      }
      if (current != parseInt(val, 10)) {
        this.$router.push({ path: this.$router.path, query: { page: val } })
        this.loadTransactions(val)
      }
    },
  },

  mounted() {
    if (!isNaN(this.$route.query.page) && this.$route.query.page > 0) {
      this.transactionsCurrentPage = this.$route.query.page
    }
    this.loadAccount().then(() => {
      this.loadTransactions()
    })
  },

  methods: {
    loadAccount() {
      return axios
        .get(`/api/accounts/${this.$route.params.id}`)
        .then((response) => {
          this.account = response.data.data
          this.loadingAccount = false
        })
        .catch((err) => {
          this.loadingAccount = false
          let { data, status, text } = err.response
          if (status === 404) {
            this.accountError = "This account does not exist"
          } else if (data && data.message) {
            this.accountError = data.message
          } else {
            this.accountError = text
          }
        })
    },

    loadTransactions(page) {
      page = page || this.$route.query.page || 1
      return axios
        .get(`/api/accounts/${this.$route.params.id}/transactions?page=${page}`)
        .then((response) => {
          this.transactions = response.data.data.map((data) => {
            return {
              ...data,
              created_at: moment(data.created_at).format("LLLL"),
            }
          })
          this.transactionsPaginationData.links = response.data.links
          this.transactionsPaginationData.meta = response.data.meta
          this.loadingTransactions = false
        })
        .catch((err) => {
          const { statusText } = err.response
          this.loadingTransactions = false
          this.transactionsError = statusText
        })
    },

    onSubmit() {
      let data = { ...this.payment, ...{ amount: String(this.payment.amount) } }
      this.loadingNewTransaction = true
      axios
        .post(`/api/accounts/${this.$route.params.id}/transactions`, data)
        .then(() => {
          this.payment = {}
          this.show = false
          this.loadingNewTransaction = false

          this.loadAccount().then(() => {
            if (this.transactionsCurrentPage !== 1) {
              this.transactionsCurrentPage = 1
            } else {
              this.loadTransactions()
            }
          })
        })
        .catch((err) => {
          this.loadingNewTransaction = false
          let { data, status, text } = err.response
          if (status === 404) {
            text = "This account does not exist"
          } else if (data && data.message) {
            text = data.message
          }
          this.$bvToast.toast(text, {
            title: "Error",
            autoHideDelay: 5000,
            variant: "danger",
          })
          this.loadingPayment = false
        })
    },
  },
}
</script>
