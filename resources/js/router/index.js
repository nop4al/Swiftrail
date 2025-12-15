import { createRouter, createWebHistory } from 'vue-router'
import Home from '../components/Home.vue'
import Login from '../components/Login.vue'
import Register from '../components/Register.vue'
import Profile from '../components/Profile.vue'
import Admin from '../components/Admin.vue'
import TrainSchedule from '../components/TrainSchedule.vue'
import SelectSeat from '../components/SelectSeat.vue'
import OrderConfirmation from '../components/OrderConfirmation.vue'
import PaymentMethods from '../components/PaymentMethods.vue'
import Checkout from '../components/Checkout.vue'
import PaymentSuccess from '../components/PaymentSuccess.vue'
import Refund from '../components/Refund.vue';
import Ticket from '../components/Ticket.vue'
import Tracking from '../components/Tracking.vue'
import SwiftPay from '../components/SwiftPay.vue'

const routes = [
  {
    path: '/',
    name: 'Home',
    component: Home
  },
  {
    path: '/home',
    name: 'HomeRedirect',
    component: Home
  },
  {
    path: '/login',
    name: 'Login',
    component: Login
  },
  {
    path: '/register',
    name: 'Register',
    component: Register
  },
  {
    path: '/profile',
    name: 'Profile',
    component: Profile,
    meta: { requiresAuth: true }
  },
  {
    path: '/admin',
    name: 'Admin',
    component: Admin,
    meta: { requiresAuth: true }
  },
  {
    path: '/trains',
    name: 'TrainSchedule',
    component: TrainSchedule
  },
  {
    path: '/select-seat',
    name: 'SelectSeat',
    component: SelectSeat
  },
  {
    path: '/order-confirmation',
    name: 'OrderConfirmation',
    component: OrderConfirmation
  }
  ,
  {
    path: '/payment-methods',
    name: 'PaymentMethods',
    component: PaymentMethods
  },
  {
    path: '/checkout',
    name: 'Checkout',
    component: Checkout
  },
  {
    path: '/payment-success',
    name: 'PaymentSuccess',
    component: PaymentSuccess
  },
  {
    path: '/refund-center',
    name: 'RefundCenter',
    component: Refund
  },
  {
    path: '/check-refund',
    name: 'CheckRefund',
    component: Refund
  },
  {
    path: '/refund',
    name: 'Refund',
    component: Refund
  },
  {
    path: '/ticket',
    name: 'Ticket',
    component: Ticket,
    meta: { requiresAuth: false }
  },
  {
    path: '/tracking',
    name: 'Tracking',
    component: Tracking
  },
  {
    path: '/swiftpay',
    name: 'SwiftPay',
    component: SwiftPay
  }
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes
})

// Navigation guard to check authentication
router.beforeEach((to, from, next) => {
  const requiresAuth = to.matched.some(record => record.meta.requiresAuth)
  const token = localStorage.getItem('authToken')

  if (requiresAuth && !token) {
    // Show warning modal and prevent navigation
    if (window.showAuthWarning) {
      window.showAuthWarning()
    }
    next(false)
  } else {
    next()
  }
})

export default router
