import { initConfirmDialog } from './modules/confirm-dialog';
import { initFormFeedback } from './modules/form-feedback';
import { initFlash } from './modules/flash';
import { initToggles } from './modules/toggles';
import { initFilePreview } from './modules/file-preview';
import { initCookieConsent } from './modules/cookie-consent';
import { initServiceBoard } from './modules/service-board';
import { initRouteTransition } from './modules/route-transition';
import { initLocationPicker } from './modules/location-picker';
import { initGeolocation } from './modules/geolocation';
import { initRouteMap } from './modules/route-map';

document.addEventListener('DOMContentLoaded', () => {
    initRouteTransition();
    initConfirmDialog();
    initFormFeedback();
    initFlash();
    initToggles();
    initFilePreview();
    initCookieConsent();
    initServiceBoard();
    initLocationPicker();
    initGeolocation();
    initRouteMap();
});
