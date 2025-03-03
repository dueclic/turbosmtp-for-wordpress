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
    return gulp.src('./admin/scss/turbosmtp-admin.scss')
        .pipe(plumber({
            errorHandler: function (err) {
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
        .pipe(gulp.dest('./admin/css'));
});

gulp.task('plugins', async () => {
    const copyChart = gulp.src([
        './node_modules/chart.js/dist/Chart.bundle.min.js',
        './node_modules/chart.js/dist/Chart.min.css',
    ]).pipe(gulp.dest('./admin/bundle/chart.js'));

    const copyDrp = gulp.src([
        './node_modules/daterangepicker/daterangepicker.js',
        './node_modules/daterangepicker/daterangepicker.css'
    ]).pipe(gulp.dest('./admin/bundle/daterangepicker'));

    const copyAggsChartJs = gulp.src('./node_modules/ts-aggs-chartjs/index.js')
        .pipe(babel({
            presets: ['@babel/preset-env'],
            plugins: [['@babel/plugin-transform-modules-umd', { exactGlobals: true, globals: { 'index': 'tsAggsChartJs' } }]]
        }))
        .pipe(rename('turbosmtp-summarizer.min.js'))
        .pipe(gulp.dest('./admin/bundle/turbosmtp'));

    return copyChart && copyDrp && copyAggsChartJs;
});

gulp.task('scripts', async () => {
    const files = [
        { src: "./admin/js/turbosmtp-stats.js", dest: "turbosmtp-stats.min.js" },
        { src: "./admin/js/turbosmtp-admin.js", dest: "turbosmtp-admin.min.js" }
    ];

    files.forEach(file => {
        gulp.src(file.src)
            .pipe(babel({ presets: ['@babel/preset-env'] }))
            .pipe(uglify())
            .pipe(rename(file.dest))
            .pipe(gulp.dest('./admin/bundle/turbosmtp'));
    });
});


gulp.task('clean', function () {
    return gulp.src(['./admin/bundle/*', "./admin/css"], {read: false})
        .pipe(clean());
});

gulp.task('default', gulp.series('clean', 'plugins', 'styles', 'scripts'));

gulp.task('scss:watch', () => {
    gulp.watch('admin/scss/**/*.scss', gulp.series('styles'))
});

gulp.task('js:watch', () => {
    gulp.watch([
        "./admin/js/turbosmtp-table.js",
        "./admin/js/turbosmtp-admin.js"
    ], gulp.series('scripts'))
});


gulp.task('watch', gulp.parallel('scss:watch', 'js:watch'));
