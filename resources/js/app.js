// main.js or app.js (or any name of your main entry file)

// Import Vue 3
import { createApp } from 'vue';

// Import Bootstrap CSS
import 'bootstrap/dist/css/bootstrap.css';

// Import jQuery and Bootstrap JS
import $ from 'jquery';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';

// You can use jQuery globally in Vue if necessary
window.$ = $;   // Import Bootstrap JS (includes modal functionality)

// Import components
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

// Initialize the app with the components
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
