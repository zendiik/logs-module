'use strict'

let gentlyCopy = require('gently-copy')
const fs = require('fs')

const filesTocopy = ['css', 'js']
const wwwPath = process.env.INIT_CWD + '/www'
const assetsPath = process.env.INIT_CWD + '/www/assets'

if (!fs.existsSync(wwwPath)) {
	fs.mkdirSync(wwwPath)
}

if (!fs.existsSync(assetsPath)) {
	fs.mkdirSync(assetsPath)
}

gentlyCopy(filesTocopy, assetsPath)
