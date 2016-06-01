/**
 *
 * @author Aleynikov Boris <alynikov.boris@gmail.com>.
 */

'use strict';

var gulp = require('gulp'),
	path = require('path'),
	sass = require('gulp-sass'),
	concat = require('gulp-concat'),
	resolution = ['480','576','720','1080'];





gulp.task('sass:480', function () { makeSass(480); });
gulp.task('sass:576', function () { makeSass(576); });
gulp.task('sass:720', function () { makeSass(720); });
gulp.task('sass:1080', function () { makeSass(1080); });

gulp.task('sass', ['sass:480','sass:576', 'sass:720','sass:1080']);

gulp.task('sass:watch', function () {
	gulp.watch('./sass/*.scss', ['sass']);
});

function makeSass ( resolution ) {
	return gulp.src(['./sass/vars.scss', './sass/' + resolution +'.scss', './sass/main.scss'])
		.pipe(concat(resolution.toString() + '.scss'))
		.pipe(sass().on('error', sass.logError))
		.pipe(gulp.dest('./'));
}

gulp.task('default', ['sass', 'sass:watch']);
