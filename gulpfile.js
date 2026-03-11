//Gulpfile created by Rowan van Zijl for Vilpy

const { src, dest, watch, series, parallel } = require('gulp');
const options = {
	paths: {
		css: "assets/css",
	}
}

const sass = require('gulp-sass')(require('sass')); //Dart sass as compiler because node sass is not supported anymore
// const cleanCSS = require('gulp-clean-css');

function vilpyStyles(){
  return src(`${options.paths.css}/sass/**/main.scss`).pipe(sass({outputStyle: 'compressed'}).on('error', sass.logError))
    .pipe(dest(`./assets/css`));
}

function watchFiles(){
  watch([`${options.paths.css}/sass/**/*.scss`], series(vilpyStyles));
}

exports.default = series(
  vilpyStyles, //Run All tasks in parallel
  watchFiles // Watch for Live Changes
);