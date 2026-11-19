// Select with type-to-filter (WAI-ARIA combobox pattern). Value lives in the parent component.
const LIMIT = 100;

export default ({ options, value, onChange, disabled = () => false, busy = () => false, showCode = false }) => ({
    query: '',
    open: false,
    active: -1,

    init() {
        this.query = this.labelOf(this.selected);
        this.$watch('selected', (item) => {
            if (!this.open) this.query = this.labelOf(item);
        });
    },

    get items() {
        return options() ?? [];
    },

    get value() {
        return value();
    },

    get isDisabled() {
        return disabled();
    },

    get isBusy() {
        return busy();
    },

    get selected() {
        return this.items.find((item) => item.code === this.value) ?? null;
    },

    get matches() {
        // While the field still shows the chosen label, list everything
        const raw = this.query === this.labelOf(this.selected) ? '' : this.query;
        const q = raw.trim().toLowerCase();

        if (q === '') return this.items;

        return this.items.filter((item) => item.name.toLowerCase().includes(q) || item.code.toLowerCase() === q);
    },

    get visible() {
        return this.matches.slice(0, LIMIT);
    },

    get truncated() {
        return this.matches.length > LIMIT;
    },

    labelOf(item) {
        if (!item) return '';

        return showCode ? `${item.name} (${item.code})` : item.name;
    },

    show() {
        if (this.isDisabled) return;
        this.open = true;
        this.active = Math.max(0, this.visible.findIndex((item) => item.code === this.value));
        this.scrollToActive();
    },

    close() {
        this.open = false;
        this.active = -1;
        this.query = this.labelOf(this.selected);
    },

    onInput() {
        this.open = true;
        this.active = this.visible.length > 0 ? 0 : -1;
    },

    move(step) {
        if (!this.open) return this.show();
        if (this.visible.length === 0) return;

        this.active = (this.active + step + this.visible.length) % this.visible.length;
        this.scrollToActive();
    },

    onEnter(event) {
        if (!this.open) return;

        // An open list must never submit the form by accident
        event.preventDefault();

        if (this.active >= 0) this.choose(this.visible[this.active]);
    },

    choose(item) {
        onChange(item.code);
        this.open = false;
        this.active = -1;
        this.query = this.labelOf(item);
    },

    scrollToActive() {
        this.$nextTick(() => this.$refs.list?.querySelector('[data-active="true"]')?.scrollIntoView({ block: 'nearest' }));
    },
});
