# Convert a webpage to pdf using headless Chrome

[![Latest Version](https://img.shields.io/github/release/web-chefs/PuppeteerToPdf.svg?style=flat-square)](https://github.com/web-chefs/PuppeteerToPdf/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Total Downloads](https://img.shields.io/packagist/dt/web-chefs/puppeteer-to-pdf.svg?style=flat-square)](https://packagist.org/packages/web-chefs/puppeteer-to-pdf)

The package can convert a webpage to a pdf. The conversion is done behind the scenes by [Puppeteer](https://github.com/GoogleChrome/puppeteer) which controls a headless version of Google Chrome.

Originally base on [spatie/browsershot](https://github.com/).

## Why Fork

WebChefs current tries where possible to support PHP 5.6 and [spatie/browsershot](https://github.com/) requires PHP 7.0 and above.

[spatie/browsershot](https://github.com/) supports a lot of image screen shot functionality that adds a lot of extra PHP 7.0 dependencies.

So to meet our requirements of generating a PDF running on PHP 5.6 required that we strip out any PHP 7.0 specific functionality this meant removing screen shot image functionality.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
