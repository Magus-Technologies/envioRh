import './bootstrap';

import Alpine from 'alpinejs';
import { consultarDocumento } from './services/apisPeru';

window.Alpine = Alpine;
window.consultarDocumento = consultarDocumento;

Alpine.start();
