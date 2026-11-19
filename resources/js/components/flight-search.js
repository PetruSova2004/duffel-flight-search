// Search form state: dependent country -> city lists and a single in-flight submit.
const text = (value) => (typeof value === 'string' ? value : '');

export default ({ citiesUrl, countries, values, messages }) => ({
    countries,
    messages,
    origin_country: text(values.origin_country),
    origin: text(values.origin),
    destination_country: text(values.destination_country),
    destination: text(values.destination),
    cities: { origin: [], destination: [] },
    loading: { origin: false, destination: false },
    errors: { origin: '', destination: '' },
    requests: { origin: 0, destination: 0 },
    submitting: false,

    init() {
        ['origin', 'destination'].forEach((side) => {
            if (this[`${side}_country`]) this.loadCities(side);
        });

        // Back/forward cache may restore the page with the spinner still on
        window.addEventListener('pageshow', () => {
            this.submitting = false;
        });
    },

    setCountry(side, code) {
        if (this[`${side}_country`] === code) return;

        this[`${side}_country`] = code;
        this[side] = '';
        this.loadCities(side);
    },

    async loadCities(side) {
        // Only the latest request per side may touch the state
        const id = ++this.requests[side];
        const country = this[`${side}_country`];

        this.cities[side] = [];
        this.errors[side] = '';
        this.loading[side] = Boolean(country);

        if (!country) return;

        let cities = [];
        let error = '';

        try {
            const response = await fetch(`${citiesUrl}?country=${encodeURIComponent(country)}`, {
                headers: { Accept: 'application/json' },
            });
            const json = await response.json().catch(() => ({}));

            if (response.ok) {
                cities = json.data ?? [];
            } else {
                // 503 carries our own translated Duffel message; anything else gets the generic one
                error = response.status === 503 && json.message ? json.message : messages.citiesError;
            }
        } catch {
            error = messages.citiesError;
        }

        if (id !== this.requests[side]) return;

        this.cities[side] = cities;
        this.errors[side] = error;
        this.loading[side] = false;
    },

    cityPlaceholder(side) {
        if (this.loading[side]) return messages.citiesLoading;

        return this[`${side}_country`] ? messages.cityPlaceholder : messages.cityDisabled;
    },

    cityDisabled(side) {
        return !this[`${side}_country`];
    },

    submit(event) {
        if (this.submitting) {
            event.preventDefault();
            return;
        }

        this.submitting = true;
    },

    // Re-runs the search through the form, so the loading state and double-click guard apply
    retry() {
        this.$root.querySelector('form')?.requestSubmit();
    },
});
