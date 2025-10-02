// Import the functions you need from the SDKs you need
import { initializeApp } from "firebase/app";
import { getAnalytics } from "firebase/analytics";
import { getFirestore } from "firebase/firestore";
import { getAuth } from "firebase/auth";
import { getStorage } from "firebase/storage";
import { getDatabase } from "firebase/database";
// https://firebase.google.com/docs/web/setup#available-libraries

// Your web app's Firebase configuration
// For Firebase JS SDK v7.20.0 and later, measurementId is optional
const firebaseConfig = {
  apiKey: import.meta.env.VITE_FIREBASE_API_KEY || "AIzaSyBp_Ep0d2NgTQR1iiUbhzBxlX_zGj1Ns9Y",
  authDomain: import.meta.env.VITE_FIREBASE_AUTH_DOMAIN || "project-inventaris-apps.firebaseapp.com",
  projectId: import.meta.env.VITE_FIREBASE_PROJECT_ID || "project-inventaris-apps",
  storageBucket: import.meta.env.VITE_FIREBASE_STORAGE_BUCKET || "project-inventaris-apps.firebasestorage.app",
  messagingSenderId: import.meta.env.VITE_FIREBASE_MESSAGING_SENDER_ID || "722457047239",
  appId: import.meta.env.VITE_FIREBASE_APP_ID || "1:722457047239:web:299022f1bfae361874cee2",
  measurementId: import.meta.env.VITE_FIREBASE_MEASUREMENT_ID || "G-ZY1Q276WBS",
  databaseURL: import.meta.env.VITE_FIREBASE_DATABASE_URL || "https://project-inventaris-apps-default-rtdb.firebaseio.com/"
};

// Initialize Firebase
const app = initializeApp(firebaseConfig);
const analytics = getAnalytics(app);
const firestore = getFirestore(app);
const auth = getAuth(app);
const storage = getStorage(app);
const database = getDatabase(app);

export { firestore, auth, storage, analytics, database };