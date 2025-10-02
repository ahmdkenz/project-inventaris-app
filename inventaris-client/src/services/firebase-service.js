import { db } from './firebase';
import { 
  collection, 
  doc, 
  getDoc, 
  getDocs, 
  addDoc, 
  updateDoc, 
  deleteDoc, 
  query,
  where,
  orderBy,
  limit,
  Timestamp
} from 'firebase/firestore';

/**
 * Base service untuk operasi Firebase Firestore
 */
export default class FirebaseService {
  constructor(collectionName) {
    this.collectionName = collectionName;
  }

  /**
   * Mendapatkan referensi koleksi
   * @returns {CollectionReference}
   */
  getCollectionRef() {
    return collection(db, this.collectionName);
  }

  /**
   * Mendapatkan referensi dokumen
   * @param {string} id ID dokumen
   * @returns {DocumentReference}
   */
  getDocumentRef(id) {
    return doc(db, this.collectionName, id);
  }

  /**
   * Mendapatkan semua dokumen dari koleksi
   * @param {object} options Opsi query (orderByField, orderDirection, limit, whereField, whereOperator, whereValue)
   * @returns {Promise<Array>} Array of documents
   */
  async getAll(options = {}) {
    try {
      let queryRef = this.getCollectionRef();
      
      if (options.whereField && options.whereOperator && options.whereValue !== undefined) {
        queryRef = query(queryRef, where(options.whereField, options.whereOperator, options.whereValue));
      }
      
      if (options.orderByField) {
        queryRef = query(queryRef, orderBy(options.orderByField, options.orderDirection || 'asc'));
      }
      
      if (options.limitCount) {
        queryRef = query(queryRef, limit(options.limitCount));
      }

      const querySnapshot = await getDocs(queryRef);
      const documents = [];
      
      querySnapshot.forEach((doc) => {
        documents.push({
          id: doc.id,
          ...this.convertTimestamps(doc.data())
        });
      });
      
      return documents;
    } catch (error) {
      console.error(`Error getting documents from ${this.collectionName}:`, error);
      throw error;
    }
  }

  /**
   * Mendapatkan dokumen berdasarkan ID
   * @param {string} id ID dokumen
   * @returns {Promise<object|null>} Dokumen atau null jika tidak ditemukan
   */
  async getById(id) {
    try {
      const docRef = this.getDocumentRef(id);
      const docSnap = await getDoc(docRef);
      
      if (docSnap.exists()) {
        return {
          id: docSnap.id,
          ...this.convertTimestamps(docSnap.data())
        };
      } else {
        return null;
      }
    } catch (error) {
      console.error(`Error getting document ${id} from ${this.collectionName}:`, error);
      throw error;
    }
  }

  /**
   * Menambahkan dokumen baru
   * @param {object} data Data dokumen
   * @returns {Promise<object>} Dokumen yang ditambahkan dengan ID
   */
  async create(data) {
    try {
      const docRef = await addDoc(this.getCollectionRef(), this.prepareDataForSave(data));
      return {
        id: docRef.id,
        ...data
      };
    } catch (error) {
      console.error(`Error adding document to ${this.collectionName}:`, error);
      throw error;
    }
  }

  /**
   * Memperbarui dokumen
   * @param {string} id ID dokumen
   * @param {object} data Data yang akan diperbarui
   * @returns {Promise<object>} Dokumen yang diperbarui
   */
  async update(id, data) {
    try {
      const docRef = this.getDocumentRef(id);
      await updateDoc(docRef, this.prepareDataForSave(data));
      return {
        id,
        ...data
      };
    } catch (error) {
      console.error(`Error updating document ${id} in ${this.collectionName}:`, error);
      throw error;
    }
  }

  /**
   * Menghapus dokumen
   * @param {string} id ID dokumen
   * @returns {Promise<void>}
   */
  async delete(id) {
    try {
      const docRef = this.getDocumentRef(id);
      await deleteDoc(docRef);
      return true;
    } catch (error) {
      console.error(`Error deleting document ${id} from ${this.collectionName}:`, error);
      throw error;
    }
  }

  /**
   * Mencari dokumen berdasarkan kondisi
   * @param {string} field Field yang akan dicari
   * @param {string} operator Operator perbandingan ('==', '!=', '>', '>=', '<', '<=')
   * @param {any} value Nilai yang akan dibandingkan
   * @returns {Promise<Array>} Array of documents
   */
  async findBy(field, operator, value) {
    try {
      const q = query(
        this.getCollectionRef(),
        where(field, operator, value)
      );
      
      const querySnapshot = await getDocs(q);
      const documents = [];
      
      querySnapshot.forEach((doc) => {
        documents.push({
          id: doc.id,
          ...this.convertTimestamps(doc.data())
        });
      });
      
      return documents;
    } catch (error) {
      console.error(`Error finding documents in ${this.collectionName} where ${field} ${operator} ${value}:`, error);
      throw error;
    }
  }

  /**
   * Mengkonversi Timestamps Firebase ke objek Date
   * @param {object} data Data dengan timestamp Firebase
   * @returns {object} Data dengan objek Date
   */
  convertTimestamps(data) {
    const result = { ...data };
    
    Object.keys(result).forEach(key => {
      if (result[key] instanceof Timestamp) {
        result[key] = result[key].toDate();
      } else if (typeof result[key] === 'object' && result[key] !== null) {
        result[key] = this.convertTimestamps(result[key]);
      }
    });
    
    return result;
  }

  /**
   * Mempersiapkan data untuk disimpan ke Firestore
   * @param {object} data Data yang akan disimpan
   * @returns {object} Data yang sudah dipersiapkan
   */
  prepareDataForSave(data) {
    const result = { ...data };
    
    // Hapus ID jika ada
    delete result.id;
    
    // Konversi Date ke Timestamp Firebase
    Object.keys(result).forEach(key => {
      if (result[key] instanceof Date) {
        result[key] = Timestamp.fromDate(result[key]);
      } else if (typeof result[key] === 'object' && result[key] !== null) {
        result[key] = this.prepareDataForSave(result[key]);
      }
    });
    
    return result;
  }
}