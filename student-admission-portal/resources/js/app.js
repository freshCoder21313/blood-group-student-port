import './bootstrap';

import Alpine from 'alpinejs';
import imageUploader from './components/image-uploader';
import notify from './components/toast';

window.Alpine = Alpine;
window.notify = notify;

Alpine.data('imageUploader', imageUploader);

Alpine.start();
