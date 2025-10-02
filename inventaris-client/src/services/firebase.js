// Import the functions you need from the SDKs you need
import { initializeApp } from "firebase/app";
import { getAnalytics } from "firebase/analytics";
import { getFirestore } from "firebase/firestore";
import { getAuth } from "firebase/auth";
import { getStorage } from "firebase/storage";
// https://firebase.google.com/docs/web/setup#available-libraries

// Your web app's Firebase configuration
// For Firebase JS SDK v7.20.0 and later, measurementId is optional
const firebaseConfig = {
  apiKey: "AIzaSyBp_Ep0d2NgTQR1iiUbhzBxlX_zGj1Ns9Y",
  authDomain: "project-inventaris-apps.firebaseapp.com",
  projectId: "project-inventaris-apps",
  storageBucket: "project-inventaris-apps.firebasestorage.app",
  messagingSenderId: "722457047239",
  appId: "1:722457047239:web:299022f1bfae361874cee2",
  measurementId: "G-ZY1Q276WBS"
};

// Initialize Firebase
const app = initializeApp(firebaseConfig);
const analytics = getAnalytics(app);
const db = getFirestore(app);
const auth = getAuth(app);
const storage = getStorage(app);

export { db, auth, storage, analytics };