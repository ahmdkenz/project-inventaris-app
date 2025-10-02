import { ref, get, set, update, remove, query, orderByChild, limitToLast, push } from 'firebase/database';
import { database } from './firebase';

/**
 * Layanan untuk berinteraksi dengan Firebase Realtime Database
 */
export default {
  /**
   * Mendapatkan data dari Realtime Database
   * 
   * @param {string} path - Path ke data
   * @returns {Promise<Object|Array>} Data dari Firebase
   */
  async getData(path) {
    try {
      const snapshot = await get(ref(database, path));
      if (snapshot.exists()) {
        return snapshot.val();
      }
      return null;
    } catch (error) {
      console.error('Gagal mengambil data dari Firebase:', error);
      throw error;
    }
  },

  /**
   * Mendapatkan data dengan filter dan pagination
   * 
   * @param {string} path - Path ke data
   * @param {string} orderBy - Field untuk pengurutan
   * @param {number} limit - Jumlah maksimal data
   * @returns {Promise<Object|Array>} Data dari Firebase
   */
  async getDataFiltered(path, orderBy, limit = 10) {
    try {
      const dataQuery = query(
        ref(database, path), 
        orderByChild(orderBy), 
        limitToLast(limit)
      );
      
      const snapshot = await get(dataQuery);
      if (snapshot.exists()) {
        // Convert dari snapshot ke array
        const data = [];
        snapshot.forEach((childSnapshot) => {
          data.push({
            key: childSnapshot.key,
            ...childSnapshot.val()
          });
        });
        return data;
      }
      return [];
    } catch (error) {
      console.error('Gagal mengambil data terfilter dari Firebase:', error);
      throw error;
    }
  },

  /**
   * Menyimpan data ke Realtime Database
   * 
   * @param {string} path - Path ke data
   * @param {Object} data - Data untuk disimpan
   * @param {string|null} key - Key/ID untuk data (opsional)
   * @returns {Promise<string>} Key data yang tersimpan
   */
  async saveData(path, data, key = null) {
    try {
      if (key) {
        await set(ref(database, `${path}/${key}`), data);
        return key;
      } else {
        const newRef = push(ref(database, path));
        await set(newRef, data);
        return newRef.key;
      }
    } catch (error) {
      console.error('Gagal menyimpan data ke Firebase:', error);
      throw error;
    }
  },

  /**
   * Memperbarui data di Realtime Database
   * 
   * @param {string} path - Path ke data
   * @param {string} key - Key/ID data
   * @param {Object} data - Data untuk diperbarui
   * @returns {Promise<void>}
   */
  async updateData(path, key, data) {
    try {
      await update(ref(database, `${path}/${key}`), data);
    } catch (error) {
      console.error('Gagal memperbarui data di Firebase:', error);
      throw error;
    }
  },

  /**
   * Menghapus data dari Realtime Database
   * 
   * @param {string} path - Path ke data
   * @param {string} key - Key/ID data yang akan dihapus
   * @returns {Promise<void>}
   */
  async deleteData(path, key) {
    try {
      await remove(ref(database, `${path}/${key}`));
    } catch (error) {
      console.error('Gagal menghapus data dari Firebase:', error);
      throw error;
    }
  },
  
  /**
   * Mendapatkan produk dari Realtime Database
   * 
   * @returns {Promise<Array>} Daftar produk
   */
  async getProducts() {
    return this.getData('products');
  },
  
  /**
   * Mendapatkan supplier dari Realtime Database
   * 
   * @returns {Promise<Array>} Daftar supplier
   */
  async getSuppliers() {
    return this.getData('suppliers');
  },
  
  /**
   * Mendapatkan transaksi terbaru dari Realtime Database
   * 
   * @param {number} limit - Jumlah maksimal data
   * @returns {Promise<Array>} Daftar transaksi
   */
  async getRecentTransactions(limit = 10) {
    return this.getDataFiltered('transactions', 'created_at', limit);
  },
  
  /**
   * Mendapatkan purchase order dari Realtime Database
   * 
   * @returns {Promise<Array>} Daftar purchase order
   */
  async getPurchaseOrders() {
    return this.getData('purchase_orders');
  },
  
  /**
   * Mendapatkan sales order dari Realtime Database
   * 
   * @returns {Promise<Array>} Daftar sales order
   */
  async getSalesOrders() {
    return this.getData('sales_orders');
  }
};