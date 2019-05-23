import Vue from 'vue'
import Vuex from 'vuex'
import VuexPersist from 'vuex-persist'

Vue.use(Vuex)

export default new Vuex.Store({
	state: {
		filterInfo: false,
		filterDebug: false,
		filterException: false,
		filterTerminal: true,
		filterError: false,
	},
	getters: {
		filterInfo: state => {
			return state.filterInfo
		},
		filterDebug: state => {
			return state.filterDebug
		},
		filterException: state => {
			return state.filterException
		},
		filterTerminal: state => {
			return state.filterTerminal
		},
		filterError: state => {
			return state.filterError
		},
	},
	mutations: {
		toggleFilterInfo(state) {
			state.filterInfo = !state.filterInfo
		},
		toggleFilterDebug(state) {
			state.filterDebug = !state.filterDebug
		},
		toggleFilterException(state) {
			state.filterException = !state.filterException
		},
		toggleFilterTerminal(state) {
			state.filterTerminal = !state.filterTerminal
		},
		toggleFilterError(state) {
			state.filterError = !state.filterError
		},
		setFilterInfo(state, info) {
			state.filterInfo = info
		},
		setFilterDebug(state, debug) {
			state.filterDebug = debug
		},
		setFilterException(state, exception) {
			state.filterException = exception
		},
		setFilterTerminal(state, terminal) {
			state.filterTerminal = terminal
		},
		setFilterError(state, error) {
			state.filterError = error
		}
	},
	actions: {
		toggleFilterInfo({ commit }) {
			commit('toggleFilterInfo')
		},
		toggleFilterDebug({ commit }) {
			commit('toggleFilterDebug')
		},
		toggleFilterException({ commit }) {
			commit('toggleFilterException')
		},
		toggleFilterTerminal({ commit }) {
			commit('toggleFilterTerminal')
		},
		toggleFilterError({ commit }) {
			commit('toggleFilterError')
		},
	},
	plugins: [new VuexPersist().plugin],
})
