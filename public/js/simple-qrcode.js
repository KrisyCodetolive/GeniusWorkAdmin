/**
 * Simple QR Code Generator
 * Une version simplifiée pour générer des QR codes en JavaScript
 */

// Fonction principale pour générer un QR code
function generateQRCode(container, data, size) {
    // Utiliser l'API QR Server pour générer le QR code
    var url = 'https://api.qrserver.com/v1/create-qr-code/?size=' + size + 'x' + size + '&data=' + encodeURIComponent(data);
    
    // Créer une image
    var img = document.createElement('img');
    img.src = url;
    img.width = size;
    img.height = size;
    img.alt = "QR Code";
    img.style.display = 'block';
    img.style.margin = '0 auto';
    
    // Vider le conteneur et ajouter l'image
    container.innerHTML = '';
    container.appendChild(img);
    
    return img;
}

// Exposer la fonction dans le contexte global
window.generateQRCode = generateQRCode;
