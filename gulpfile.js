'use strict';

const gulp = require('gulp');
const scss = require('gulp-sass');
const sourcemaps = require('gulp-sourcemaps');
const rename = require('gulp-rename');
const plumber = require('gulp-plumber');
const autoprefixer = require('gulp-autoprefixer');
const concat = require('gulp-concat');
const uglify = require('gulp-uglify');
const babel = require('gulp-babel');
const clean = require('gulp-clean');

gulp.task('styles', async () => {
    return gulp.src('./build/scss/turbosmtp.scss')
        .pipe(plumber({
            errorHandler: function (err) {
                console.log(err);
                this.emit('end');
            }
        }))
        .pipe(sourcemaps.init({loadMaps: true}))
        .pipe(scss({outputStyle: 'compressed'}).on('error', scss.logError))
        .pipe(autoprefixer('last 2 versions'))
        .pipe(sourcemaps.write(undefined, {sourceRoot: null}))
        .pipe(rename({
            suffix: '.min'
        }))
        .pipe(gulp.dest('./dist/css'));
});

gulp.task('plugins', async () => {
    const copyChart = gulp.src([
        './node_modules/chart.js/dist/Chart.bundle.min.js',
        './node_modules/chart.js/dist/Chart.min.css',
    ]).pipe(gulp.dest('./dist/lib/chart.js'));

    const copyDrp = gulp.src([
        './node_modules/daterangepicker/daterangepicker.js',
        './node_modules/daterangepicker/daterangepicker.css'
    ]).pipe(gulp.dest('./dist/lib/daterangepicker'));

    return copyChart && copyDrp;
});

gulp.task('scripts', async () => {

    let src = [
        './build/js/turbosmtp.js'
    ];

    gulp.src(src)
        .pipe(concat('turbosmtp.min.js'))
        .pipe(babel({
            presets: ['@babel/preset-env']
        }))
        .pipe(uglify())
        .pipe(gulp.dest('./dist/js'));
});


gulp.task('clean', function () {
    return gulp.src('./dist/*', {read: false})
        .pipe(clean());
});

gulp.task('default', gulp.series('clean', 'plugins', 'styles', 'scripts'));

gulp.task('scss:watch', () => {
    gulp.watch('build/scss/**/*.scss', gulp.series('styles'))
});

gulp.task('js:watch', () => {
    gulp.watch('build/js/**/*.js', gulp.series('scripts'))
});


gulp.task('watch', gulp.parallel('scss:watch', 'js:watch'));
