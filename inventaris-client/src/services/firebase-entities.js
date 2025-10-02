import FirebaseService from './firebase-service';

/**
 * Service untuk operasi produk
 */
class ProductService extends FirebaseService {
  constructor() {
    super('products');
  }

  /**
   * Mendapatkan produk dengan kategori tertentu
   * @param {string} category Nama kategori
   * @returns {Promise<Array>} Daftar produk
   */
  async getByCategory(category) {
    return this.findBy('category', '==', category);
  }

  /**
   * Mendapatkan produk dengan stok di bawah threshold
   * @param {number} threshold Batas minimum stok
   * @returns {Promise<Array>} Daftar produk
   */
  async getLowStock(threshold) {
    return this.findBy('quantity', '<=', threshold);
  }

  /**
   * Mencari produk berdasarkan nama
   * @param {string} name Nama produk (partial)
   * @returns {Promise<Array>} Daftar produk
   */
  async searchByName(name) {
    // Firestore tidak mendukung pencarian teks lengkap
    // Ini adalah solusi sederhana untuk pencarian nama
    const products = await this.getAll();
    const lowerName = name.toLowerCase();
    
    return products.filter(product => 
      product.name.toLowerCase().includes(lowerName)
    );
  }
}

/**
 * Service untuk operasi supplier
 */
class SupplierService extends FirebaseService {
  constructor() {
    super('suppliers');
  }
}

/**
 * Service untuk operasi stok
 */
class StockService extends FirebaseService {
  constructor() {
    super('stocks');
  }

  /**
   * Mendapatkan stok produk tertentu
   * @param {string} productId ID produk
   * @returns {Promise<Array>} Data stok
   */
  async getByProductId(productId) {
    return this.findBy('product_id', '==', productId);
  }
}

/**
 * Service untuk operasi pesanan pembelian
 */
class PurchaseOrderService extends FirebaseService {
  constructor() {
    super('purchase_orders');
  }

  /**
   * Mendapatkan pesanan pembelian berdasarkan supplier
   * @param {string} supplierId ID supplier
   * @returns {Promise<Array>} Daftar pesanan pembelian
   */
  async getBySupplierId(supplierId) {
    return this.findBy('supplier_id', '==', supplierId);
  }

  /**
   * Mendapatkan pesanan pembelian berdasarkan status
   * @param {string} status Status pesanan
   * @returns {Promise<Array>} Daftar pesanan pembelian
   */
  async getByStatus(status) {
    return this.findBy('status', '==', status);
  }

  /**
   * Mendapatkan item pesanan pembelian
   * @param {string} purchaseOrderId ID pesanan pembelian
   * @returns {Promise<Array>} Daftar item pesanan pembelian
   */
  async getItems(purchaseOrderId) {
    const itemService = new FirebaseService('purchase_order_items');
    return itemService.findBy('purchase_order_id', '==', purchaseOrderId);
  }
}

/**
 * Service untuk operasi pesanan penjualan
 */
class SalesOrderService extends FirebaseService {
  constructor() {
    super('sales_orders');
  }

  /**
   * Mendapatkan pesanan penjualan berdasarkan status
   * @param {string} status Status pesanan
   * @returns {Promise<Array>} Daftar pesanan penjualan
   */
  async getByStatus(status) {
    return this.findBy('status', '==', status);
  }

  /**
   * Mendapatkan item pesanan penjualan
   * @param {string} salesOrderId ID pesanan penjualan
   * @returns {Promise<Array>} Daftar item pesanan penjualan
   */
  async getItems(salesOrderId) {
    const itemService = new FirebaseService('sales_order_items');
    return itemService.findBy('sales_order_id', '==', salesOrderId);
  }
}

export {
  ProductService,
  SupplierService,
  StockService,
  PurchaseOrderService,
  SalesOrderService
};