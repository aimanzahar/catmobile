import './bootstrap';
import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import './native-image-picker';

Alpine.plugin(collapse);
window.Alpine = Alpine;
Alpine.start();
