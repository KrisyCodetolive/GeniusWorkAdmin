<script>
// Fonction pour créer un son de notification via l'API Web Audio
function createNotificationSound() {
    // Créer un contexte audio
    const audioContext = new (window.AudioContext || window.webkitAudioContext)();
    
    // Créer un oscillateur pour générer un son
    const oscillator = audioContext.createOscillator();
    const gainNode = audioContext.createGain();
    
    // Configurer l'oscillateur
    oscillator.type = 'sine'; // Type de son (sine, square, sawtooth, triangle)
    oscillator.frequency.setValueAtTime(880, audioContext.currentTime); // La (A5)
    
    // Configurer le gain (volume)
    gainNode.gain.setValueAtTime(0, audioContext.currentTime);
    gainNode.gain.linearRampToValueAtTime(0.5, audioContext.currentTime + 0.01);
    gainNode.gain.linearRampToValueAtTime(0, audioContext.currentTime + 0.5);
    
    // Connecter les nœuds
    oscillator.connect(gainNode);
    gainNode.connect(audioContext.destination);
    
    // Démarrer et arrêter l'oscillateur
    oscillator.start(audioContext.currentTime);
    oscillator.stop(audioContext.currentTime + 0.5);
    
    // Retourner l'oscillateur pour pouvoir l'arrêter si nécessaire
    return oscillator;
}

// Exposer la fonction globalement
window.playNotificationSound = function() {
    createNotificationSound();
};
</script>
