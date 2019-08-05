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
		filterWarning: true,
		filterError: false,

		showFilterInfo: true,
		showFilterDebug: true,
		showFilterException: true,
		showFilterTerminal: true,
		showFilterWarning: true,
		showFilterError: true,

		actualPage: 1,
		filterDate: null,
	},
	getters: {
		getFilterInfo: state => {
			return state.filterInfo
		},
		getFilterDebug: state => {
			return state.filterDebug
		},
		getFilterException: state => {
			return state.filterException
		},
		getFilterTerminal: state => {
			return state.filterTerminal
		},
		getFilterWarning: state => {
			return state.filterWarning
		},
		getFilterError: state => {
			return state.filterError
		},
		getShowFilterInfo: state => {
			return state.showFilterInfo
		},
		getShowFilterDebug: state => {
			return state.showFilterDebug
		},
		getShowFilterException: state => {
			return state.showFilterException
		},
		getShowFilterTerminal: state => {
			return state.showFilterTerminal
		},
		getShowFilterWarning: state => {
			return state.showFilterWarning
		},
		getShowFilterError: state => {
			return state.showFilterError
		},
		getActualPage: state => {
			return state.actualPage
		},
		getFilterDate: state => {
			return state.filterDate
		},
	},
	mutations: {
		toggleFilterInfo(state) {
			state.showFilterInfo = !state.showFilterInfo
		},
		toggleFilterDebug(state) {
			state.showFilterDebug = !state.showFilterDebug
		},
		toggleFilterException(state) {
			state.showFilterException = !state.showFilterException
		},
		toggleFilterTerminal(state) {
			state.showFilterTerminal = !state.showFilterTerminal
		},
		toggleFilterWarning(state) {
			state.showFilterWarning = !state.showFilterWarning
		},
		toggleFilterError(state) {
			state.showFilterError = !state.showFilterError
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
		setFilterWarning(state, warning) {
			state.filterWarning = warning
		},
		setFilterError(state, error) {
			state.filterError = error
		},
		setActualPage(state, page) {
			state.actualPage = page
		},
		setFilterDate(state, date) {
			state.filterDate = date
		},
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
		toggleFilterWarning({ commit }) {
			commit('toggleFilterWarning')
		},
		toggleFilterError({ commit }) {
			commit('toggleFilterError')
		},
	},
	plugins: [new VuexPersist().plugin],
})
