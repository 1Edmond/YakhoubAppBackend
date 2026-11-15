importScripts('https://www.gstatic.com/firebasejs/8.3.2/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/8.3.2/firebase-messaging.js');
importScripts('https://www.gstatic.com/firebasejs/8.3.2/firebase-auth.js');

firebase.initializeApp({
    apiKey: "AIzaSyB8ycUpE-lfXzyggQxCzu9qo7x3_lPoWgk",
    authDomain: "multishop-tchad.firebaseapp.com",
    projectId: "multishop-tchad",
    storageBucket: "multishop-tchad.firebasestorage.app",
    messagingSenderId: "282612823748",
    appId: "1:282612823748:android:9e37a835d7c0cba98cbb91",
    measurementId: ""
});

const messaging = firebase.messaging();
messaging.setBackgroundMessageHandler(function(payload) {
    return self.registration.showNotification(payload.data.title, {
        body: payload.data.body || '',
        icon: payload.data.icon || ''
    });
});
