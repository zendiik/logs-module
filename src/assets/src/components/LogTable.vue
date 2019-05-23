<template>
	<div class="log-table">
		<div class="row-f">
			<div class="column" v-if="types.info">
				<div class="click panel" :class="filterInfo ? 'panel-info' : 'panel-default'" @click="toggleFilterInfo">
					<div class="panel-heading text-center filter" data-type="info">
						<h2><strong>{{ countLogInfo }}</strong></h2>
						info.log
					</div>
				</div>
			</div>
			<div class="column" v-if="types.debug">
				<div class="click panel" :class="filterDebug ? 'panel-success' : 'panel-default'" @click="toggleFilterDebug">
					<div class="panel-heading text-center filter" data-type="info">
						<h2><strong>{{ countLogDebug }}</strong></h2>
						debug.log
					</div>
				</div>
			</div>
			<div class="column" v-if="types.exception">
				<div class="click panel" :class="filterException ? 'panel-warning' : 'panel-default'" @click="toggleFilterException">
					<div class="panel-heading text-center filter" data-type="info">
						<h2><strong>{{ countLogException }}</strong></h2>
						exception.log
					</div>
				</div>
			</div>
			<div class="column" v-if="types.terminal">
				<div class="click panel" :class="filterTerminal ? 'panel-terminal' : 'panel-default'" @click="toggleFilterTerminal">
					<div class="panel-heading text-center filter" data-type="info">
						<h2><strong>{{ countLogTerminal }}</strong></h2>
						terminal.log
					</div>
				</div>
			</div>
			<div class="column" v-if="types.error">
				<div class="click panel" :class="filterError ? 'panel-danger' : 'panel-default'" @click="toggleFilterError">
					<div class="panel-heading text-center filter" data-type="info">
						<h2><strong>{{ countLogError }}</strong></h2>
						error.log
					</div>
					<!--<div class="panel-footer text-center">
						<div class="btn-group">
							<button class="btn btn-default filter" data-type="403" data-bool="{$accessDenied}"><span class="fa fa-ban{if !$accessDenied} text-danger{else} text-success{/if}"></span> 403</button>
							<button class="btn btn-default filter" data-type="404" data-bool="{$notFound}"><span class="fa fa-{if !$notFound}times text-danger{else}check text-success{/if}"></span> 404</button>
						</div>
					</div>-->
				</div>
			</div>
		</div>

		<br>

		<Pagination
				v-model="currentPage"
				:page-count="pages"
				:classes="bootstrapPaginationClasses"
				:labels="customLabels"
		></Pagination>

		<table class="table table-stripped table-bordered table-hover table-condensed">
			<thead>
				<tr>
					<th>Date And Time</th>
					<th>Description</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				<tr v-for="(log, index) in filteredLogPaginated" :key="index">
					<td v-html="$options.filters.date(log.dateTime)"></td>
					<td>
						<span class="label label-danger" v-if="log.type === 'error'">{{ log.type }}</span>
						<span class="label label-terminal" v-else-if="log.type === 'terminal'">{{ log.type }}</span>
						<span class="label label-warning" v-else-if="log.type === 'exception'">{{ log.type }}</span>
						<span class="label label-info" v-else-if="log.type === 'info'">{{ log.type }}</span>
						<span class="label label-success" v-else-if="log.type === 'debug'">{{ log.type }}</span>
						<span class="label label-default" v-else>{{ log.type }}</span>
						{{ log.message }}
					</td>
					<td>
						<a data-toggle="modal" data-target="#iframe" class="logLink" v-if="log.file !== null" @click="loadIframe(log.fileContent)">
							<fa-icon icon="file" />
						</a>
					</td>
				</tr>
			</tbody>
		</table>

		<Pagination
				v-model="currentPage"
				:page-count="pages"
				:classes="bootstrapPaginationClasses"
				:labels="customLabels"
		></Pagination>

		<div class="modal fade" tabindex="-1" role="dialog" id="iframe" aria-labelledby="myLargeModalLabel">
			<div class="modal-dialog modal-lg" role="document">
				<iframe frameborder="0" id="fileContent" class="modal-content" width="100%" height="700vh"></iframe>
			</div>
		</div>
	</div>
</template>


<script>
	import Pagination from '@/components/Pagination'
	import momentjs from 'moment'

	const initialState = document.querySelector('#__INITIAL_STATE__');

	export default {
		name: 'LogTable',
		components: {
			Pagination
		},
		data() {
			return {
				perPage: 20,
				currentPage: 1,
				bootstrapPaginationClasses: {
					ul: 'pagination',
					liActive: 'active',
					liDisable: 'disabled'
				},
				customLabels: {
					prev: '&larr;',
					next: '&rarr;'
				},
				logs: [],
				types: {
					"info": true,
					"debug": true,
					"exception": true,
					"terminal": true,
					"error": true,
				}
			}
		},
		computed: {
			pages() {
				return Math.ceil(this.rows / this.perPage)
			},
			rows() {
				return this.filteredLog.length
			},
			filterInfo() {
				return this.$store.getters.filterInfo
			},
			filterDebug() {
				return this.$store.getters.filterDebug
			},
			filterException() {
				return this.$store.getters.filterException
			},
			filterTerminal() {
				return this.$store.getters.filterTerminal
			},
			filterError() {
				return this.$store.getters.filterError
			},
			filteredLog() {
				let result = []

				if (this.filterInfo) {
					Array.prototype.push.apply(result, this.filterLog('info'))
				}

				if (this.filterDebug) {
					Array.prototype.push.apply(result, this.filterLog('debug'))
				}

				if (this.filterException) {
					Array.prototype.push.apply(result, this.filterLog('exception'))
				}

				if (this.filterTerminal) {
					Array.prototype.push.apply(result, this.filterLog('terminal'))
				}

				if (this.filterError) {
					Array.prototype.push.apply(result, this.filterLog('error'))
				}

				return result
			},
			filteredLogPaginated() {
				let start = (this.currentPage - 1) * this.perPage

				return this.filteredLog
					.slice()
					.sort((a, b) => {
						return this.momentDate(b.dateTime) - this.momentDate(a.dateTime)
					})
					.slice(start, start + this.perPage)
			},
			countLogInfo() {
				return this.filterLog('info').length
			},
			countLogDebug() {
				return this.filterLog('debug').length
			},
			countLogException() {
				return this.filterLog('exception').length
			},
			countLogTerminal() {
				return this.filterLog('terminal').length
			},
			countLogError() {
				return this.filterLog('error').length
			},
		},
		created() {
			this.getLogs()
			this.getTypes()
		},
		filters: {
			date(value) {
				return momentjs.utc(value, 'DD.MM.YYYY HH:mm:ss').format('<b>DD.MM.YYYY</b><br>HH:mm:ss')
			}
		},
		methods: {
			momentDate(dateTime) {
				return momentjs.utc(dateTime, 'DD.MM.YYYY HH:mm:ss').toDate()
			},
			toggleFilterInfo() {
				this.$store.dispatch('toggleFilterInfo')
			},
			toggleFilterDebug() {
				this.$store.dispatch('toggleFilterDebug')
			},
			toggleFilterException() {
				this.$store.dispatch('toggleFilterException')
			},
			toggleFilterTerminal() {
				this.$store.dispatch('toggleFilterTerminal')
			},
			toggleFilterError() {
				this.$store.dispatch('toggleFilterError')
			},
			loadIframe(data) {
				document.querySelector('#fileContent').src = 'data:text/html;charset=utf-8,' + escape(data);
			},
			filterLog(type) {
				return this.logs.filter(log => {
					return !type || log.type.indexOf(type) > -1
				})
			},
			getTypes() {
				if (initialState !== null) {
					let types = JSON.parse(initialState.innerHTML).types

					this.types.info = types.info === undefined ? true : types.info
					this.types.debug = types.debug === undefined ? true : types.debug
					this.types.exception = types.exception === undefined ? true : types.exception
					this.types.terminal = types.terminal === undefined ? false : types.terminal
					this.types.error = types.error === undefined ? true : types.error

					this.$store.commit('setFilterInfo', this.types.info)
					this.$store.commit('setFilterDebug', this.types.debug)
					this.$store.commit('setFilterException', this.types.exception)
					this.$store.commit('setFilterTerminal', this.types.terminal)
					this.$store.commit('setFilterError', this.types.error)
				} else {
					this.types.info = true
					this.types.debug = true
					this.types.exception = true
					this.types.terminal = false
					this.types.error = true
				}
			},
			getLogs() {
				if (initialState !== null) {
					this.logs = JSON.parse(initialState.innerHTML).logs
				} else {
					this.logs = []
				}
			}
		},
	}
</script>
<style scoped lang="less">
	@brand-lighter: #9FE9C1;
	@brand-lightest: #a8f4bf;
	@brand-primary: #009645;

	.panel-terminal {
		background-color: @brand-lightest;
		border-color: @brand-lighter;
		color: @brand-primary;
	}

	.label-terminal {
		background-color: @brand-lighter;
		color: @brand-primary;
	}

	.logLink {
		cursor: pointer;
	}

	.click {
		cursor: pointer;
	}

	.row-f {
		display: flex;
		justify-content: center;
	}

	.column {
		width: 12%;
		margin-right: 1%;
		text-align: center;
	}
</style>
