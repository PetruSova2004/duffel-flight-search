import Alpine from 'alpinejs';
import combobox from './components/combobox';
import flightSearch from './components/flight-search';
import offerSort from './components/offer-sort';

Alpine.data('combobox', combobox);
Alpine.data('flightSearch', flightSearch);
Alpine.data('offerSort', offerSort);

window.Alpine = Alpine;

Alpine.start();
