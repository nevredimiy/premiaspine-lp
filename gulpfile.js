//region Config
const prjectsDir = ''; // !!!НЕ ТРОГАТЬ!!!
const baseDir = prjectsDir + './'; // местоположение файлов относительно корня проекта
const config = {
    browserSync: {
        config: {
            server: baseDir,
            directory: true,
            index: "index.htm"  // только кода прокси отключен
            //proxy: "https://www.agadolia.co.il" //Для работы на удаленном сервере
        },
        delay: 0 // Для работы с удуленным серверос, чтоб дать время залится файлу
    },
    src: {
        images: {
            folder: baseDir + 'src/images',
            watchPattern: '/**/*.*'
        },
        html: {
            folder: baseDir + 'src',
            partsFolder: baseDir + 'src/parts',
            watchPattern: '/**/*.html',
            watchPatternParts: '/**/*.htm',
        },
        scss: {
            folder: baseDir + 'src/sass',
            watchPattern: '/**/*.scss'
        },
        js: {
            folder: baseDir + 'src/js',
            presets: ['@babel/preset-env'],
            plugins: [/*'transform-class-properties','transform-object-assign','es6-promise'*/],
            files : [
                {
                    src :'main.app.js',
                    build : 'main.js',
                    presets : ['@babel/preset-env','@babel/preset-react'],
                    plugins : ['@babel/plugin-proposal-class-properties','@babel/plugin-proposal-async-generator-functions','@babel/plugin-transform-runtime']

                }
            ]
        }
    },
    build: {
        images: {
            folder: baseDir + 'images',
            webp: false // TODO : применить ключ
        },
        html: {
            folder: baseDir + ''
        },
        css: {
            folder: baseDir + 'css'
        },
        js: {
            folder: baseDir + 'js'
        }
    }
}
//endregion


//region Plugins
const gulp = require('gulp');
const gutil = require('gulp-util');
const watch = require('gulp-watch');
const pathUtil = require('path');
const filter = require('gulp-filter');
const replace = require('gulp-string-replace');
const fileinclude = require('gulp-file-include');
// const sass = require('gulp-sass');
const rename = require('gulp-rename');
const clean = require('gulp-clean');
const progectDir = 'lol';
const notifier = require('node-notifier');
const beautify = require('gulp-jsbeautifier');


const browserify = require('browserify');
const source = require('vinyl-source-stream');
const buffer = require('vinyl-buffer');
const uglify = require('gulp-uglify');
const sourcemaps = require('gulp-sourcemaps');
const imagemin = require('gulp-imagemin');
const imageminWebp = require('imagemin-webp');
const imageminPngquant = require('imagemin-pngquant');
const imageminZopfli = require('imagemin-zopfli');
const imageminMozjpeg = require('imagemin-mozjpeg'); //need to run 'brew install libpng'
const imageminGiflossy = require('imagemin-giflossy');
const browserSync = require("browser-sync").create();
//endregion


//region browserSync
function updateBrowser() {
    if (!config.browserSync) {
        return
    }
    if (!config.browserSync.delay) {
        browserSync.reload()
    } else {
        setTimeout(browserSync.reload, config.browserSync.delay);
    }

}

//endregion


//region JAVASCRIPT
gulp.task('js:build', (done) => {
    config.src.js.files.forEach(function (item) {
        babel(item.src, item.presets, item.plugins, item.build,done)
    });
//		updateBrowser();
});

function babel(file, presets, plugins, newName,done) {
    browserify({
        debug: true,
        entries: config.src.js.folder + '/' + file,
        sourceMaps: true,
        extensions :['.js','.jsx']
    })
        .transform('babelify', {
            presets: presets ? presets : config.src.js.presets,
            sourceMaps: true,
            plugins: plugins ? plugins : config.src.js.plugins
        })
        .bundle()
        .on('error', function (err) {
            errorNotifier(err, '[JAVASCRIPT error]');
            this.emit('end');
        })
        .pipe(source(newName ? newName : file))
        .pipe(buffer()) // <----- convert from streaming to buffered vinyl file object
        .pipe(sourcemaps.init({loadMaps: true}))
        .pipe(uglify())
        .pipe(sourcemaps.write('./'))
        .pipe(gulp.dest(config.build.js.folder)).on('end', function () {
        updateBrowser();
        done();
    });
}

//TODO : применить конфиг
gulp.task('js:watch', (done) => {
    var watcher = gulp.watch(config.src.js.folder + '/**/*.*', ['js:build']);
    done()
});
//endregion

//region SCSS
// gulp.task('scss', function (done) {
//     gulp.src(config.src.scss.folder + config.src.scss.watchPattern)
//         .pipe(sourcemaps.init({loadMaps: true}))
//         .pipe(sass({outputStyle: 'compressed'})
//             .on('error', function (err) {
//                 errorNotifier(err, '[SCSS error]');
//                 //sass.logError(err);
//                 //this.emit('end');
//             }))
//         //.pipe(replace(/.jpg"/g, '.jpg.webp"'))// TODO : незабыть регулярку сделать
//         //.pipe(replace(/.png"/g, '.png.webp"'))// TODO : незабыть регулярку сделать
//         .pipe(sourcemaps.write('./'))
//         .pipe(gulp.dest(config.build.css.folder)).on('end', function () {
//         updateBrowser();
//         done();
//     });
// });
//
// gulp.task('scss:watch', function () {
//     gulp.watch(config.src.scss.folder + config.src.scss.watchPattern, ['scss']);
// });

//region  HTML
gulp.task('html:watch', function (done) {
    var watcher = watch(config.src.html.folder + config.src.html.watchPattern);
    watcher.on('change', function (path, stats) {
        html(path);
    });
    watcher.on('add', function (path, stats) {
        html(path);
    });
    watcher.on('unlink', function (path, stats) {
        var filePathFromSrc = pathUtil.relative(pathUtil.resolve(config.src.html.folder), path);
        var destFilePath = pathUtil.resolve(config.build.html.folder, filePathFromSrc);
        gulp.src(destFilePath).pipe(clean());
    });
    watch(config.src.html.partsFolder + config.src.html.watchPatternParts, function () {
        html();
    });
    done();
});


function html(path) {
    if (!path) {
        path = config.src.html.folder + config.src.html.watchPattern;
    }
    gulp.src(path, {base: config.src.html.folder})
        .pipe(fileinclude({
            basepath: config.src.html.partsFolder
        }))
        .on('error', function (err) {
            errorNotifier(err, '[include error]');
            this.emit('end');
        })
        .pipe(replace(/[\r\n]+[\t\n]*[\r\n]/g, ''))
        .pipe(replace(/(src=[\"|\']|srcset=[\"|\'])(?!https?:\/\/)([^\/].+(png|jpg))[\"|\']/g, function (searchStr, url, ext) {
                searchStr = searchStr.replace(ext, ext + '.webp');
                return searchStr;
            }, {
                logs: {
                    enabled: false
                }
            })
        )
        .pipe(beautify({
            "indent_char": "\t",
            "indent_size": 1,
        }))
        .pipe(fileinclude({
            prefix: '@_@',
            basepath: config.src.html.partsFolder
        }))
        .pipe(gulp.dest(config.build.html.folder)).on('end', updateBrowser);
}

//endregion


//region IMAGES
gulp.task('jpgs', function (done) {
    return minifiy();
    done();
});


gulp.task('img:watch', function (done) {
    var watcher = watch(config.src.images.folder + config.src.images.watchPattern);
    watcher.on('change', function (path, stats) {
        minifiy(path);
    });
    watcher.on('add', function (path, stats) {
        minifiy(path);
    });
    watcher.on('unlink', function (path, stats) {
        console.log(path);
        console.log('unlink');
        console.log(stats);
        var filePathFromSrc = pathUtil.relative(pathUtil.resolve(config.src.images.folder), path);
        var destFilePath = pathUtil.resolve(config.build.images.folder, filePathFromSrc);
        var test = gulp.src(destFilePath)
        test.pipe(clean()).pipe(rename(function (path) {
            path.extname += ".webp";
        })).pipe(clean());
    });
    done();
});

function imgFilter(file) {
    console.log(file.File);
    return false;
}

function minifiy(path) {
    const f = filter(['{**/*.png,**/*.PNG,**/*.jpg,**/*.JPG}'], {restore: true});
    if (!path) {
        path = config.src.images.folder + config.src.images.watchPattern
    } else {

    }
    gulp.src(path, {base: config.src.images.folder})
        .pipe(imagemin([
            //png
            imageminPngquant({
                quality: [0.7, 0.9] //lossy settings
            }),
            imageminZopfli({
                more: true,
                iterations: 50 // very slow but more effective
            }),
            //gif
            imagemin.gifsicle({
                interlaced: true,
                optimizationLevel: 3
            }),
            //svg
            imagemin.svgo({
                plugins: [{
                    removeViewBox: false
                }]
            }),
            //jpg lossless
            /*imagemin.jpegtran({
                progressive: true
            }),*/
            //jpg very light lossy, use vs jpegtran
            imageminMozjpeg({
                quality: 80,
            })
        ]))
        .pipe(gulp.dest(config.build.images.folder))
    // add webp
    gulp.src(path, {base: config.src.images.folder}).pipe(f)
        .pipe(imagemin([
            imageminWebp({
                quality: 50,
                //lossless : false
                //method : 6
            })
        ], {verbose: true}))
        .pipe(rename(function (path) {
            path.extname += ".webp";
        }))
        .pipe(gulp.dest(config.build.images.folder));
}

//endregion


gulp.task('watch', [
        'scss:watch',
        'js:watch',
        'img:watch',
        'html:watch'
    ]
);

// TODO: ПУСТЬ ПОКА БУДЕТ
gulp.task('apply-prod-environment', function (done) {
    process.env.NODE_ENV = 'production';
    if (config.browserSync) {
        browserSync.init(config.browserSync.config);
    }
    done();
});


gulp.task('default',['apply-prod-environment', 'watch']);


//region Helpers
function errorNotifier(err, type) {
    notifier.notify(
        {
            title: 'Error : ' + type,
            message: err.message,
            subtitle: 'test',
            icon: '..\\gulp\\error.png' // TODO : сделать нормальную иконку
        },
        function (err, response) {
            console.log(response)
        }
    );
    gutil.log(
        gutil.colors.red.bold(type) +
        "\n" +
        gutil.colors.red.bold('-----------------------------------------------------------------------------------') +
        "\n" +
        gutil.colors.yellow.bold(err.message) +
        "\n" +
        gutil.colors.red.bold('-----------------------------------------------------------------------------------')
    );
}

//endregion
