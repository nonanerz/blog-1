var gulp = require('gulp'),
    newer = require('gulp-newer'),
    imagemin = require('gulp-imagemin'),
    concat = require('gulp-concat'),
    uglify = require('gulp-uglify'),
    rename = require('gulp-rename'),
    less = require('gulp-less'),
    cssmin = require('gulp-cssmin'),
    sourcemaps = require('gulp-sourcemaps'),
    del = require('del');

var
    src = 'web-src/',
    web = 'web/includes/';

gulp.task('images', function () {
    return gulp.src(src + 'img/**')
        .pipe(newer(web + 'img/'))
        .pipe(imagemin())
        .pipe(gulp.dest(web + 'img/'))
});

gulp.task('clean', function () {
   del([
       web + '*'
   ]);
});

gulp.task('concat', function () {
    return gulp.src([
        'bower_components/jquery/dist/jquery.js',
        'bower_components/bootstrap/dist/js/bootstrap.js',
        'web-src/js/*.js'
    ])
        .pipe(concat('app.js'))
        .pipe(gulp.dest(web + 'js'))
});

gulp.task('js', ['concat'], function () {
    return gulp.src(web + 'js/app.js')
        .pipe(uglify())
        .pipe(rename('app.min.js'))
        .pipe(gulp.dest(web + 'js'))
});

gulp.task('less', function () {
   return gulp.src(src + 'less/app.less')
       .pipe(sourcemaps.init())
       .pipe(less())
       .pipe(cssmin())
       .pipe(sourcemaps.write('./'))
       .pipe(gulp.dest(web + 'css'))
});

gulp.task('fonts', function () {
   gulp.src('bower_components/bootstrap/dist/fonts/*.*')
       .pipe(gulp.dest(web + 'fonts'))
});

gulp.task('default', ['images', 'less', 'js', 'fonts'], function () {
    gulp.watch(src + 'img/*.*', ['images']);
    gulp.watch(src + 'less/**/*', ['less']);
    gulp.watch(src + 'js/*.*', ['js']);
});