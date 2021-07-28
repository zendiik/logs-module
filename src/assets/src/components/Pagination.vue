<template>
	<nav aria-label="Pagination" class="text-center">
		<ul :class="paginationClasses.ul">
			<li
					v-if="paginationLabels.prev"
					:class="`${paginationClasses.li} ${hasFirst ? paginationClasses.liDisable : ''}`"
			>
				<a
						@click="prev"
						:disabled="hasFirst"
						:class="`${paginationClasses.button} ${hasFirst ? paginationClasses.buttonDisable : ''}`"
						v-html="paginationLabels.prev"
				></a>
			</li>

			<li
					v-for="page in items"
					:key="page.label"
					:class="`${paginationClasses.li} ${page.active ? paginationClasses.liActive : ''} ${page.disable ? paginationClasses.liDisable : ''}`"
			>
		<span
				v-if="page.disable"
				:class="`${paginationClasses.button} ${paginationClasses.buttonDisable}`"
		>...</span>
				<a
						v-else
						@click="goto(page.label)"
						:class="`${paginationClasses.button} ${page.active ? paginationClasses.buttonActive : ''}`"
				>
					{{ page.label }}
				</a>
			</li>

			<li
					v-if="paginationLabels.next"
					:class="`${paginationClasses.li} ${hasLast ? paginationClasses.liDisable : ''}`"
			>
				<a
						@click="next"
						:disabled="hasLast"
						:class="`${paginationClasses.button} ${hasLast ? paginationClasses.buttonDisable : ''}`"
						v-html="paginationLabels.next"
				></a>
			</li>
		</ul>
	</nav>
</template>

<script>
	const defaultClasses = {
		ul: 'pagination',
		li: 'pagination-item',
		liActive: 'pagination-item--active',
		liDisable: 'pagination-item--disable',
		button: 'pagination-link',
		buttonActive: 'pagination-link--active',
		buttonDisable: 'pagination-link--disable'
	}
	const defaultLabels = {
		first: '&laquo;',
		prev: '&lsaquo;',
		next: '&rsaquo;',
		last: '&raquo;'
	}

	export default {
		name: "Pagination",
		props: {
			value: {  // current page
				type: Number,
				required: true
			},
			pageCount: { // page numbers
				type: Number,
				required: true
			},
			classes: {
				type: Object,
				required: false,
				default: () => ({})
			},
			labels: {
				type: Object,
				required: false,
				default: () => ({})
			}
		},
		data() {
			return {
				paginationClasses: {
					...defaultClasses,
					...this.classes
				},
				paginationLabels: {
					...defaultLabels,
					...this.labels
				}
			}
		},
		mounted() {
			if (this.value > this.pageCount) {
				this.$emit('input', this.pageCount)
			}
		},
		computed: {
			items() {
				let valPrev = this.value > 1 ? (this.value - 1) : 1 // for easier navigation - gives one previous page
				let valNext = this.value < this.pageCount ? (this.value + 1) : this.pageCount // one next page
				let extraPrev = valPrev === 3 ? 2 : null
				let extraNext = valNext === (this.pageCount - 2) ? (this.pageCount - 1) : null
				let dotsBefore = valPrev > 3 ? 2 : null
				let dotsAfter = valNext < (this.pageCount - 2) ? (this.pageCount - 1) : null
				let output = []
				for (let i = 1; i <= this.pageCount; i += 1) {
					if ([1, this.pageCount, this.value, valPrev, valNext, extraPrev, extraNext, dotsBefore, dotsAfter].includes(i)) {
						output.push({
							label: i,
							active: this.value === i,
							disable: [dotsBefore, dotsAfter].includes(i)
						})
					}
				}
				return output
			},
			hasFirst() {
				return (this.value === 1)
			},
			hasLast() {
				return (this.value === this.pageCount)
			},
		},
		watch: {
			value: function () {
				this.$emit('change')
			}
		},
		methods: {
			prev() {
				if (!this.hasFirst) {
					const page = this.value - 1;

					this.$emit('input', page)

					window.history.pushState({},"", `#${page}`);
				}
			},
			goto(page) {
				this.$emit('input', page)
				window.history.pushState({},"", `#${page}`);
			},
			next() {
				if (!this.hasLast) {
					const page = this.value + 1;

					this.$emit('input', page)

					window.history.pushState({},"", `#${page}`);
				}
			},
		}
	}
</script>

<style scoped>
	a:hover {
		cursor: pointer;
	}

	a {
		-webkit-user-select: none;
		-moz-user-select: none;
		-ms-user-select: none;
		user-select: none;
	}
</style>
