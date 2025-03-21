// Views
import { createApp } from 'vue';  // Correct way to import Vue 3
import Navbar from "./components/global/Navbar";
import Footer from "./components/global/Footer";
import Sidebar from "./components/global/Sidebar";
import Container from "./components/Container";
import Login from "./components/auth/Login";
import Register from "./components/auth/Register";
import PasswordReset from "./components/auth/PasswordReset";
import PasswordUpdate from "./components/auth/PasswordUpdate";
import LandingPage from "./components/LandingPage";
import Transactions from "./components/Transactions";
// Initialize the app
createApp({
    components: {
        Container,
        Navbar,
        Sidebar,
        Footer,
        Login,
        Register,
        PasswordReset,
        PasswordUpdate,
        LandingPage,
        Transactions
    }
}).mount('#app');  // Mount the app to the element with id 'app'
