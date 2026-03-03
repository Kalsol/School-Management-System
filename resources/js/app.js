
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */
 import './bootstrap';

window.Vue = require('vue');

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

Vue.component('example-component', require('./components/ExampleComponent.vue'));

const app = new Vue({
    el: '#app'
});

console.log('Hello from app.js');

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

// resources/js/app.js

// import Echo from "laravel-echo";
// import Pusher from "pusher-js";

// window.Pusher = Pusher;

// // Use 'new Echo' and ensure it is assigned to window.Echo
// window.Echo = new Echo({
//     broadcaster: "pusher",
//     key: "f9e3c241ba45fcba1f84", // Replace with your actual key
//     cluster: "ap2", // Replace with your actual cluster
//     forceTLS: true,
//     encrypted: true,
// });


