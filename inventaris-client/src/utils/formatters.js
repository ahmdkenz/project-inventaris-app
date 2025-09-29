/**
 * Utility functions for formatting data
 */

/**
 * Format number to IDR currency format
 * @param {Number} value - Number to format
 * @param {Boolean} withSymbol - Whether to include 'Rp' symbol
 * @returns {String} - Formatted number
 */
export const formatIDR = (value, withSymbol = false) => {
  if (value === null || value === undefined || value === '') return '';
  
  // Convert to number if it's a string
  const numValue = typeof value === 'string' ? parseInt(value) : value;
  
  if (isNaN(numValue)) return '';
  
  // Format number with thousand separator
  const formatted = numValue.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
  
  return withSymbol ? `Rp ${formatted}` : formatted;
};

/**
 * Parse IDR formatted string to number
 * @param {String} value - Formatted string (e.g. "1.000.000")
 * @returns {Number} - The numeric value
 */
export const parseIDR = (value) => {
  if (!value) return 0;
  // Remove non-numeric characters (except decimal point)
  return parseFloat(value.toString().replace(/\./g, ''));
};