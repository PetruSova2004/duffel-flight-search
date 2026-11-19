// Re-orders rendered offer cards by a data-* attribute; price breaks ties.
export default () => ({
    sort: 'price',

    apply() {
        const list = this.$refs.offers;
        const key = this.sort;

        [...list.children]
            .sort((a, b) => Number(a.dataset[key]) - Number(b.dataset[key]) || Number(a.dataset.price) - Number(b.dataset.price))
            .forEach((card) => list.appendChild(card));
    },
});
