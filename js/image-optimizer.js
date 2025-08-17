/**
 * Image Optimizer Library
 * Optimizes images by creating WebP versions when possible and implementing responsive loading
 */

class ImageOptimizer {
  constructor(options = {}) {
    this.options = {
      lazyLoad: true,
      useWebP: true,
      placeholderColor: 'rgba(255, 105, 180, 0.2)',
      lowQualityPreview: true,
      ...options
    };
    
    this.supportsWebP = false;
    this.checkWebPSupport();
  }
  
  /**
   * Check if browser supports WebP
   */
  checkWebPSupport() {
    const canvas = document.createElement('canvas');
    if (canvas.getContext && canvas.getContext('2d')) {
      // Check WebP support
      this.supportsWebP = canvas.toDataURL('image/webp').indexOf('data:image/webp') === 0;
    }
  }
  
  /**
   * Create a low-quality placeholder image
   * @param {HTMLImageElement} img - The image element to create a placeholder for
   */
  createPlaceholder(img) {
    const canvas = document.createElement('canvas');
    canvas.width = 30; // Very small dimension for low quality preview
    canvas.height = 30;
    
    const ctx = canvas.getContext('2d');
    ctx.fillStyle = this.options.placeholderColor;
    ctx.fillRect(0, 0, canvas.width, canvas.height);
    
    return canvas.toDataURL('image/jpeg', 0.1);
  }
  
  /**
   * Convert an image to WebP format using canvas
   * @param {HTMLImageElement} img - The image element to convert
   * @param {number} quality - Quality of the WebP image (0-1)
   */
  convertToWebP(img, quality = 0.8) {
    return new Promise((resolve, reject) => {
      try {
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        
        // Wait for the image to load
        if (img.complete) {
          canvas.width = img.naturalWidth;
          canvas.height = img.naturalHeight;
          ctx.drawImage(img, 0, 0);
          resolve(canvas.toDataURL('image/webp', quality));
        } else {
          img.onload = () => {
            canvas.width = img.naturalWidth;
            canvas.height = img.naturalHeight;
            ctx.drawImage(img, 0, 0);
            resolve(canvas.toDataURL('image/webp', quality));
          };
          img.onerror = () => {
            reject(new Error('Image failed to load'));
          };
        }
      } catch (err) {
        reject(err);
      }
    });
  }
  
  /**
   * Create a responsive image with multiple sizes
   * @param {HTMLImageElement} img - The image element to make responsive
   */
  makeResponsive(img) {
    // Original image path
    const originalSrc = img.getAttribute('data-src') || img.src;
    
    // Skip if already processed
    if (img.hasAttribute('data-optimized')) return;
    
    // Mark as processed
    img.setAttribute('data-optimized', 'true');
    
    // Create different sizes for srcset
    if (this.options.useWebP && this.supportsWebP) {
      // Generate WebP version if supported
      this.convertToWebP(img).then(webpDataUrl => {
        img.srcset = webpDataUrl;
      }).catch(() => {
        // Fallback if conversion fails
        console.log('WebP conversion failed for:', originalSrc);
      });
    }
    
    // Add loading attribute if not present
    if (!img.hasAttribute('loading')) {
      img.setAttribute('loading', 'lazy');
    }
    
    // Add decoding attribute
    img.setAttribute('decoding', 'async');
    
    // Add fade-in effect
    img.style.transition = 'opacity 0.3s ease-in';
    
    return img;
  }
  
  /**
   * Apply lazy loading to images
   * @param {HTMLImageElement} img - The image element to lazy load
   */
  applyLazyLoading(img) {
    if (!this.options.lazyLoad) return;
    
    // Skip if already processed
    if (img.hasAttribute('data-lazy')) return;
    
    // Store original source
    const originalSrc = img.src;
    img.setAttribute('data-src', originalSrc);
    
    // Create placeholder
    if (this.options.lowQualityPreview) {
      img.src = this.createPlaceholder(img);
    } else {
      // Transparent pixel fallback
      img.src = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100"%3E%3C/svg%3E';
    }
    
    img.style.opacity = '0.6';
    img.setAttribute('data-lazy', 'true');
    
    // Create observer for lazy loading
    if ('IntersectionObserver' in window) {
      const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            const lazyImage = entry.target;
            const src = lazyImage.getAttribute('data-src');
            
            if (src) {
              lazyImage.src = src;
              lazyImage.style.opacity = '1';
              observer.unobserve(lazyImage);
            }
          }
        });
      }, {
        rootMargin: '100px',
        threshold: 0.01
      });
      
      observer.observe(img);
    } else {
      // Fallback for browsers that don't support Intersection Observer
      img.src = originalSrc;
      img.style.opacity = '1';
    }
  }
  
  /**
   * Process all images on the page
   */
  optimizeAllImages() {
    const images = document.querySelectorAll('img:not([data-optimized])');
    images.forEach(img => {
      this.applyLazyLoading(img);
      this.makeResponsive(img);
    });
  }
  
  /**
   * Optimize a specific image
   * @param {HTMLImageElement} img - The image element to optimize
   */
  optimizeImage(img) {
    this.applyLazyLoading(img);
    this.makeResponsive(img);
  }
  
  /**
   * Initialize the optimizer
   */
  init() {
    // Process all images when DOM is loaded
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', () => this.optimizeAllImages());
    } else {
      this.optimizeAllImages();
    }
    
    // Process new images when they are added
    if (window.MutationObserver) {
      const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
          mutation.addedNodes.forEach((node) => {
            if (node.nodeName === 'IMG') {
              this.optimizeImage(node);
            } else if (node.querySelectorAll) {
              const images = node.querySelectorAll('img:not([data-optimized])');
              images.forEach(img => this.optimizeImage(img));
            }
          });
        });
      });
      
      observer.observe(document.body, {
        childList: true,
        subtree: true
      });
    }
  }
}

// Create global instance and initialize
window.imageOptimizer = new ImageOptimizer();
document.addEventListener('DOMContentLoaded', () => {
  window.imageOptimizer.init();
});