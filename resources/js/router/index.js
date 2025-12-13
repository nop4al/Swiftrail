import { createRouter, createWebHistory } from 'vue-router'
import Home from '../components/Home.vue'
import Login from '../components/Login.vue'
import Register from '../components/Register.vue'
import Profile from '../components/Profile.vue'
import EditProfile from '../components/EditProfile.vue'
import ChangePassword from '../components/ChangePassword.vue'
import Admin from '../components/Admin.vue'
import TrainSchedule from '../components/TrainSchedule.vue'
import SelectSeat from '../components/SelectSeat.vue'
import OrderConfirmation from '../components/OrderConfirmation.vue'
import PaymentMethods from '../components/PaymentMethods.vue'
import Checkout from '../components/Checkout.vue'
import PaymentSuccess from '../components/PaymentSuccess.vue'
import CheckRefund from '../components/CheckRefund.vue'
import ETicket from '../components/ETicket.vue'
import MyTicket from '../components/MyTicket.vue'
import Refund from '../components/Refund.vue'
import Tracking from '../components/Tracking.vue'

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
    component: Profile
  },
  {
    path: '/edit-profile',
    name: 'EditProfile',
    component: EditProfile
  },
  {
    path: '/change-password',
    name: 'ChangePassword',
    component: ChangePassword
  },
  {
    path: '/admin',
    name: 'Admin',
    component: Admin
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
    path: '/check-refund',
    name: 'CheckRefund',
    component: CheckRefund
  },
  {
    path: '/eticket',
    name: 'ETicket',
    component: ETicket
  },
  {
    path: '/my-ticket',
    name: 'MyTicket',
    component: MyTicket
  },
  {
    path: '/refund',
    name: 'Refund',
    component: Refund
  },
  {
    path: '/tracking',
    name: 'Tracking',
    component: Tracking
  }
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes
})

export default router
