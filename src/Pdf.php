<?php

namespace WebChefs\PuppeteerToPdf;

use Symfony\Component\Process\Process;
use WebChefs\PuppeteerToPdf\TemporaryDirectory;
use WebChefs\PuppeteerToPdf\Exceptions\CouldMakeTakePdf;
use Symfony\Component\Process\Exception\ProcessFailedException;

class Pdf
{
    protected $nodeBinary = null;
    protected $npmBinary = null;
    protected $nodeModulePath = null;
    protected $includePath = '$PATH:/usr/local/bin';
    protected $html = '';
    protected $noSandbox = false;
    protected $proxyServer = '';
    protected $showBackground = false;
    protected $showScreenshotBackground = true;
    protected $temporaryHtmlDirectory;
    protected $timeout = 60;
    protected $url = '';
    protected $additionalOptions = [];

    /**
     * @param string $url
     *
     * @return static
     */
    public static function url($url)
    {
        return (new static)->setUrl($url);
    }

    /**
     * @param string $html
     *
     * @return static
     */
    public static function html($html)
    {
        return (new static)->setHtml($html);
    }

    public function __construct($url = '')
    {
        $this->url = $url;
        $this->windowSize(800, 600);
    }

    public function setNodeBinary($nodeBinary)
    {
        $this->nodeBinary = $nodeBinary;

        return $this;
    }

    public function setNpmBinary($npmBinary)
    {
        $this->npmBinary = $npmBinary;

        return $this;
    }

    public function setIncludePath($includePath)
    {
        $this->includePath = $includePath;

        return $this;
    }

    public function setNodeModulePath($nodeModulePath)
    {
        $this->nodeModulePath = $nodeModulePath;

        return $this;
    }

    public function setChromePath($executablePath)
    {
        $this->setOption('executablePath', $executablePath);

        return $this;
    }

    /**
     * @deprecated This option is no longer supported by modern versions of Puppeteer.
     */
    public function setNetworkIdleTimeout($networkIdleTimeout)
    {
        $this->setOption('networkIdleTimeout');

        return $this;
    }

    public function waitUntilNetworkIdle($strict = true)
    {
        $this->setOption('waitUntil', $strict ? 'networkidle0' : 'networkidle2');

        return $this;
    }

    public function setUrl($url)
    {
        $this->url = $url;
        $this->html = '';

        return $this;
    }

    public function setProxyServer($proxyServer)
    {
        $this->proxyServer = $proxyServer;

        return $this;
    }

    public function setHtml($html)
    {
        $this->html = $html;
        $this->url = '';

        $this->hideBrowserHeaderAndFooter();

        return $this;
    }

    public function showBrowserHeaderAndFooter()
    {
        return $this->setOption('displayHeaderFooter', true);
    }

    public function hideBrowserHeaderAndFooter()
    {
        return $this->setOption('displayHeaderFooter', false);
    }

    public function hideHeader()
    {
        return $this->headerHtml('<p></p>');
    }

    public function hideFooter()
    {
        return $this->footerHtml('<p></p>');
    }

    public function headerHtml($html)
    {
        return $this->setOption('headerTemplate', $html);
    }

    public function footerHtml($html)
    {
        return $this->setOption('footerTemplate', $html);
    }

    public function deviceScaleFactor($deviceScaleFactor)
    {
        // Google Chrome currently supports values of 1, 2, and 3.
        return $this->setOption('viewport.deviceScaleFactor', max(1, min(3, $deviceScaleFactor)));
    }

    public function fullPage()
    {
        return $this->setOption('fullPage', true);
    }

    public function showBackground()
    {
        $this->showBackground = true;
        $this->showScreenshotBackground = true;

        return $this;
    }

    public function hideBackground()
    {
        $this->showBackground = false;
        $this->showScreenshotBackground = false;

        return $this;
    }

    public function ignoreHttpsErrors()
    {
        return $this->setOption('ignoreHttpsErrors', true);
    }

    public function mobile($mobile = true)
    {
        return $this->setOption('viewport.isMobile', true);
    }

    public function touch($touch = true)
    {
        return $this->setOption('viewport.hasTouch', true);
    }

    public function landscape($landscape = true)
    {
        return $this->setOption('landscape', $landscape);
    }

    public function margins($top, $right, $bottom, $left)
    {
        return $this->setOption('margin', [
            'top' => $top.'mm',
            'right' => $right.'mm',
            'bottom' => $bottom.'mm',
            'left' =>  $left.'mm',
        ]);
    }

    public function noSandbox()
    {
        $this->noSandbox = true;

        return $this;
    }

    public function dismissDialogs()
    {
        return $this->setOption('dismissDialogs', true);
    }

    public function pages($pages)
    {
        return $this->setOption('pageRanges', $pages);
    }

    public function paperSize($width, $height)
    {
        return $this
            ->setOption('width', $width.'mm')
            ->setOption('height', $height.'mm');
    }

    // paper format
    public function format($format)
    {
        return $this->setOption('format', $format);
    }

    public function timeout($timeout)
    {
        $this->timeout = $timeout;

        return $this;
    }

    public function userAgent($userAgent)
    {
        $this->setOption('userAgent', $userAgent);

        return $this;
    }

    public function emulateMedia($media)
    {
        $this->setOption('emulateMedia', $media);

        return $this;
    }

    public function windowSize($width, $height)
    {
        return $this
            ->setOption('viewport.width', $width)
            ->setOption('viewport.height', $height);
    }

    public function setDelay($delayInMilliseconds)
    {
        return $this->setOption('delay', $delayInMilliseconds);
    }

    public function setOption($key, $value)
    {
        $this->arraySet($this->additionalOptions, $key, $value);

        return $this;
    }

    public function save($targetPath)
    {
        return $this->savePdf($targetPath);
    }

    public function bodyHtml()
    {
        $command = $this->createBodyHtmlCommand();

        return $this->callBrowser($command);
    }

    public function pdf()
    {
        $command = $this->createPdfCommand();

        $encoded_pdf = $this->callBrowser($command);

        return base64_decode($encoded_pdf);
    }

    public function savePdf($targetPath)
    {
        $command = $this->createPdfCommand($targetPath);

        $this->callBrowser($command);

        $this->cleanupTemporaryHtmlFile();

        if (! file_exists($targetPath)) {
            throw CouldMakeTakePdf::chromeOutputEmpty($targetPath);
        }
    }

    public function createBodyHtmlCommand()
    {
        $url = $this->html ? $this->createTemporaryHtmlFile() : $this->url;

        return $this->createCommand($url, 'content');
    }

    public function createPdfCommand($targetPath = null)
    {
        $url = $this->html ? $this->createTemporaryHtmlFile() : $this->url;

        $options = [];
        if ($targetPath) {
            $options['path'] = $targetPath;
        }

        $command = $this->createCommand($url, 'pdf', $options);

        if ($this->showBackground) {
            $command['options']['printBackground'] = true;
        }

        return $command;
    }

    protected function getOptionArgs()
    {
        $args = [];

        if ($this->noSandbox) {
            $args[] = '--no-sandbox';
        }

        if ($this->proxyServer) {
            $args[] = '--proxy-server='.$this->proxyServer;
        }

        return $args;
    }

    protected function createCommand($url, $action, array $options = [])
    {
        $command = compact('url', 'action', 'options');

        $command['options']['args'] = $this->getOptionArgs();

        if (! empty($this->additionalOptions)) {
            $command['options'] = array_merge_recursive($command['options'], $this->additionalOptions);
        }

        return $command;
    }

    protected function createTemporaryHtmlFile()
    {
        $this->temporaryHtmlDirectory = (new TemporaryDirectory())->create();

        file_put_contents($temporaryHtmlFile = $this->temporaryHtmlDirectory->path('index.html'), $this->html);

        return "file://{$temporaryHtmlFile}";
    }

    protected function cleanupTemporaryHtmlFile()
    {
        if ($this->temporaryHtmlDirectory) {
            $this->temporaryHtmlDirectory->delete();
        }
    }

    protected function callBrowser(array $command)
    {
        $setIncludePathCommand = "PATH={$this->includePath}";

        $nodeBinary = $this->nodeBinary ?: 'node';

        $setNodePathCommand = $this->getNodePathCommand($nodeBinary);

        $binPath = __DIR__.'/../bin/browser.js';

        $fullCommand =
            $setIncludePathCommand.' '
            .$setNodePathCommand.' '
            .$nodeBinary.' '
            .escapeshellarg($binPath).' '
            .escapeshellarg(json_encode($command));

        $process = (new Process($fullCommand))->setTimeout($this->timeout);

        $process->run();

        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $process->getOutput();
    }

    protected function getNodePathCommand($nodeBinary)
    {
        if ($this->nodeModulePath) {
            return "NODE_PATH='{$this->nodeModulePath}'";
        }
        if ($this->npmBinary) {
            return "NODE_PATH=`{$nodeBinary} {$this->npmBinary} root -g`";
        }

        return 'NODE_PATH=`npm root -g`';
    }

    protected function arraySet(array &$array, $key, $value)
    {
        if (is_null($key)) {
            return $array = $value;
        }

        $keys = explode('.', $key);

        while (count($keys) > 1) {
            $key = array_shift($keys);

            // If the key doesn't exist at this depth, we will just create an empty array
            // to hold the next value, allowing us to create the arrays to hold final
            // values at the correct depth. Then we'll keep digging into the array.
            if (! isset($array[$key]) || ! is_array($array[$key])) {
                $array[$key] = [];
            }

            $array = &$array[$key];
        }

        $array[array_shift($keys)] = $value;

        return $array;
    }
}
