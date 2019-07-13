var gulp    = require('gulp'),
    plumber = require('gulp-plumber'),
    sass    = require('gulp-sass'),
    concat  = require('gulp-concat'),
    uglify  = require('gulp-uglify');


gulp.task('js', function(){
  return gulp.src('assets/src/frontend/js/*.js')
  .pipe(plumber())
  .pipe(uglify())
  .pipe(concat('actions.js', { newLine: '\r\n\r\n' }))
  .pipe(gulp.dest('assets/dist/frontend/js/'));
});

gulp.task('adminJs', function(){
  return gulp.src('assets/src/dashboard/js/*.js')
  .pipe(plumber())
  .pipe(uglify())
  .pipe(concat('actions.js', { newLine: '\r\n\r\n' }))
  .pipe(gulp.dest('assets/dist/dashboard/js/'));
});


gulp.task('sass', function(){
  return gulp.src('assets/src/frontend/sass/*.scss')
  .pipe(plumber())
  .pipe(sass({outputStyle: 'compressed'}))
  .pipe(gulp.dest('assets/dist/frontend/css/'));
});

gulp.task('adminSass', function(){
  return gulp.src('assets/src/dashboard/sass/*.scss')
  .pipe(plumber())
  .pipe(sass({outputStyle: 'compressed'}))
  .pipe(gulp.dest('assets/dist/dashboard/css/'));
});


gulp.task('watch', function(){
  gulp.watch('assets/src/*/js/*.js', gulp.series('js', 'adminJs' ) );
  gulp.watch('assets/src/*/sass/*.scss', gulp.series('sass', 'adminSass' ) );
});


gulp.task('build', gulp.series( 'js', 'sass', 'adminJs', 'adminSass' ) );


gulp.task('default', gulp.series( 'build', 'watch' ) );
