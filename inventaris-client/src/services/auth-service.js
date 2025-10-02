import FirebaseService from './firebase-service';
import { auth } from './firebase';
import { 
  signInWithEmailAndPassword,
  createUserWithEmailAndPassword,
  signOut as firebaseSignOut,
  sendPasswordResetEmail,
  updateProfile,
  onAuthStateChanged
} from 'firebase/auth';

/**
 * Service untuk autentikasi dan manajemen pengguna
 */
class AuthService {
  /**
   * FirebaseService instance untuk operasi pada koleksi 'users'
   */
  userService = new FirebaseService('users');

  /**
   * Login menggunakan email dan password
   * @param {string} email Email pengguna
   * @param {string} password Password pengguna
   * @returns {Promise<object>} Data pengguna
   */
  async login(email, password) {
    try {
      const userCredential = await signInWithEmailAndPassword(auth, email, password);
      const userData = await this.getUserProfile(userCredential.user.uid);
      return userData;
    } catch (error) {
      console.error('Login error:', error);
      throw error;
    }
  }

  /**
   * Registrasi pengguna baru
   * @param {string} email Email pengguna
   * @param {string} password Password pengguna
   * @param {string} name Nama pengguna
   * @returns {Promise<object>} Data pengguna
   */
  async register(email, password, name) {
    try {
      const userCredential = await createUserWithEmailAndPassword(auth, email, password);
      await updateProfile(userCredential.user, { displayName: name });
      
      // Simpan data tambahan ke Firestore
      const userData = {
        id: userCredential.user.uid,
        email,
        name,
        role: 'user',
        created_at: new Date()
      };
      
      await this.userService.update(userCredential.user.uid, userData);
      return userData;
    } catch (error) {
      console.error('Registration error:', error);
      throw error;
    }
  }

  /**
   * Logout pengguna
   * @returns {Promise<void>}
   */
  async logout() {
    try {
      await firebaseSignOut(auth);
      return true;
    } catch (error) {
      console.error('Logout error:', error);
      throw error;
    }
  }

  /**
   * Reset password pengguna
   * @param {string} email Email pengguna
   * @returns {Promise<void>}
   */
  async resetPassword(email) {
    try {
      await sendPasswordResetEmail(auth, email);
      return true;
    } catch (error) {
      console.error('Password reset error:', error);
      throw error;
    }
  }

  /**
   * Mendapatkan profil pengguna saat ini
   * @returns {Promise<object|null>} Data pengguna atau null jika tidak ada pengguna login
   */
  async getCurrentUser() {
    return new Promise((resolve) => {
      const unsubscribe = onAuthStateChanged(auth, (user) => {
        unsubscribe();
        if (user) {
          this.getUserProfile(user.uid).then(userData => {
            resolve(userData);
          }).catch(() => {
            resolve(null);
          });
        } else {
          resolve(null);
        }
      });
    });
  }

  /**
   * Mendapatkan profil pengguna berdasarkan ID
   * @param {string} userId ID pengguna
   * @returns {Promise<object>} Data pengguna
   */
  async getUserProfile(userId) {
    try {
      return await this.userService.getById(userId);
    } catch (error) {
      console.error('Get user profile error:', error);
      throw error;
    }
  }
}

export default new AuthService();