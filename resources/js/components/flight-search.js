// Search form state: dependent country -> city lists and a single in-flight submit.
export default ({ citiesUrl, countries, values, messages }) => ({
    countries,
    messages,
    origin_country: values.origin_country ?? '',
    origin: values.origin ?? '',
    destination_country: values.destination_country ?? '',
    destination: values.destination ?? '',
    cities: { origin: [], destination: [] },
    loading: { origin: false, destination: false },
    citiesError: '',
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
        const country = this[`${side}_country`];

        this.cities[side] = [];
        this.citiesError = '';

        if (!country) return;

        this.loading[side] = true;

        try {
            const response = await fetch(`${citiesUrl}?country=${encodeURIComponent(country)}`, {
                headers: { Accept: 'application/json' },
            });
            const json = await response.json();

            if (!response.ok) throw new Error(json.message);

            // Ignore a late answer if the country was changed meanwhile
            if (country === this[`${side}_country`]) this.cities[side] = json.data;
        } catch (error) {
            this.citiesError = error.message || messages.citiesError;
        } finally {
            this.loading[side] = false;
        }
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
});
