/* подключим плагин */
var Encore = require('@symfony/webpack-encore');

Encore
   /* Установим путь куда будет осуществляться сборка */
   .setOutputPath('public/build/')
   /* Укажем web путь до каталога web/build */
   .setPublicPath('/build')
   /* Каждый раз перед сборкой будем очищать каталог /build */
   .cleanupOutputBeforeBuild()
   /* Добавим наш главный файл ресурсов в сборку */
   .addStyleEntry('styles', './assets/app.scss')
   /* Включим поддержку sass/scss файлов */
   .enableSassLoader()
   /* В режиме разработки будем генерировать карту ресурсов */
   .enableSourceMaps(!Encore.isProduction());

/* Экспортируем финальную конфигурацию */
module.exports = Encore.getWebpackConfig();
