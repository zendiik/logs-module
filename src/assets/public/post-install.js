'use strict'

let gentlyCopy = require('gently-copy')
const fs = require('fs')
const path = require('path')

const filesTocopy = ['css', 'js']
const wwwPath = path.resolve(process.cwd(), '../../../www')
const assetsPath = path.resolve(process.cwd(), '../../../www/assets')

if (!fs.existsSync(wwwPath)) {
	fs.mkdirSync(wwwPath)
}

if (!fs.existsSync(assetsPath)) {
	fs.mkdirSync(assetsPath)
} else {
	fs.readdirSync(assetsPath).forEach(dir => {
		fs.readdirSync(assetsPath + '/' + dir).forEach(file => {
			fs.unlinkSync(assetsPath + '/' + dir + '/' + file)
		})
	})
}

gentlyCopy(filesTocopy, assetsPath)
