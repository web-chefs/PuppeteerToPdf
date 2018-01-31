<?php

namespace  WebChefs\PuppeteerToPdf\Test;

use WebChefs\PuppeteerToPdf\Pdf;
use WebChefs\PuppeteerToPdf\Exceptions\CouldMakeTakePdf;
use Symfony\Component\Process\Exception\ProcessFailedException;

class PdfTest extends TestCase
{
    public function setUp()
    {
        $this->emptyTempDirectory();
    }

    /** @test */
    // public function it_can_get_the_body_html()
    // {
    //     $html = Pdf::url('https://example.com')
    //         ->bodyHtml();

    //     $this->assertContains('<h1>Example Domain</h1>', $html);
    // }

    /** @test */
    // public function it_can_save_a_pdf()
    // {
    //     $targetPath = __DIR__.'/temp/testPdf.pdf';

    //     Pdf::url('https://example.com')
    //         ->save($targetPath);

    //     $this->assertFileExists($targetPath);

    //     $this->assertEquals('application/pdf', mime_content_type($targetPath));
    // }

    /** @test */
    // public function it_can_save_a_highly_customized_pdf()
    // {
    //     $targetPath = __DIR__.'/temp/customPdf.pdf';

    //     Pdf::url('https://example.com')
    //         ->hideBrowserHeaderAndFooter()
    //         ->showBackground()
    //         ->landscape()
    //         ->margins(5, 25, 5, 25)
    //         ->pages('1')
    //         ->savePdf($targetPath);

    //     $this->assertFileExists($targetPath);

    //     $this->assertEquals('application/pdf', mime_content_type($targetPath));
    // }

    /** @test */
    public function it_can_handle_a_permissions_error()
    {
        $targetPath = '/cantWriteThisPdf.pdf';

        $this->expectException(ProcessFailedException::class);

        Pdf::url('https://example.com')
            ->save($targetPath);
    }

    /** @test */
    public function it_can_create_a_command_to_generate_a_pdf()
    {
        $command = Pdf::url('https://example.com')
            ->showBackground()
            ->landscape()
            ->margins(10, 20, 30, 40)
            ->pages('1-3')
            ->paperSize(210, 148)
            ->createPdfCommand('document.pdf');

        $this->assertEquals([
            'url' => 'https://example.com',
            'action' => 'pdf',
            'options' => [
                'path' => 'document.pdf',
                'printBackground' => true,
                'landscape' => true,
                'margin' => ['top' => '10mm', 'right' => '20mm', 'bottom' => '30mm', 'left' => '40mm'],
                'pageRanges' => '1-3',
                'width' => '210mm',
                'height' => '148mm',
                'viewport' => [
                    'width' => 800,
                    'height' => 600,
                ],
                'args' => [],
            ],
        ], $command);
    }

    /** @test */
    public function it_can_create_a_command_to_generate_a_pdf_with_a_custom_header()
    {
        $command = Pdf::url('https://example.com')
            ->showBackground()
            ->landscape()
            ->margins(10, 20, 30, 40)
            ->pages('1-3')
            ->paperSize(210, 148)
            ->showBrowserHeaderAndFooter()
            ->headerHtml('<p>Test Header</p>')
            ->createPdfCommand('document.pdf');

        $this->assertEquals([
            'url' => 'https://example.com',
            'action' => 'pdf',
            'options' => [
                'path' => 'document.pdf',
                'printBackground' => true,
                'landscape' => true,
                'margin' => ['top' => '10mm', 'right' => '20mm', 'bottom' => '30mm', 'left' => '40mm'],
                'pageRanges' => '1-3',
                'width' => '210mm',
                'height' => '148mm',
                'displayHeaderFooter' => true,
                'headerTemplate' => '<p>Test Header</p>',
                'viewport' => [
                    'width' => 800,
                    'height' => 600,
                ],
                'args' => [],
            ],
        ], $command);
    }

    /** @test */
    public function it_can_create_a_command_to_generate_a_pdf_with_a_custom_footer()
    {
        $command = Pdf::url('https://example.com')
            ->showBackground()
            ->landscape()
            ->margins(10, 20, 30, 40)
            ->pages('1-3')
            ->paperSize(210, 148)
            ->showBrowserHeaderAndFooter()
            ->footerHtml('<p>Test Footer</p>')
            ->createPdfCommand('document.pdf');

        $this->assertEquals([
            'url' => 'https://example.com',
            'action' => 'pdf',
            'options' => [
                'path' => 'document.pdf',
                'printBackground' => true,
                'landscape' => true,
                'margin' => ['top' => '10mm', 'right' => '20mm', 'bottom' => '30mm', 'left' => '40mm'],
                'pageRanges' => '1-3',
                'width' => '210mm',
                'height' => '148mm',
                'displayHeaderFooter' => true,
                'footerTemplate' => '<p>Test Footer</p>',
                'viewport' => [
                    'width' => 800,
                    'height' => 600,
                ],
                'args' => [],
            ],
        ], $command);
    }

    /** @test */
    public function it_can_create_a_command_to_generate_a_pdf_with_the_header_hidden()
    {
        $command = Pdf::url('https://example.com')
            ->showBackground()
            ->landscape()
            ->margins(10, 20, 30, 40)
            ->pages('1-3')
            ->paperSize(210, 148)
            ->showBrowserHeaderAndFooter()
            ->hideHeader()
            ->createPdfCommand('document.pdf');

        $this->assertEquals([
            'url' => 'https://example.com',
            'action' => 'pdf',
            'options' => [
                'path' => 'document.pdf',
                'printBackground' => true,
                'landscape' => true,
                'margin' => ['top' => '10mm', 'right' => '20mm', 'bottom' => '30mm', 'left' => '40mm'],
                'pageRanges' => '1-3',
                'width' => '210mm',
                'height' => '148mm',
                'displayHeaderFooter' => true,
                'headerTemplate' => '<p></p>',
                'viewport' => [
                    'width' => 800,
                    'height' => 600,
                ],
                'args' => [],
            ],
        ], $command);
    }

    /** @test */
    public function it_can_create_a_command_to_generate_a_pdf_with_the_footer_hidden()
    {
        $command = Pdf::url('https://example.com')
            ->showBackground()
            ->landscape()
            ->margins(10, 20, 30, 40)
            ->pages('1-3')
            ->paperSize(210, 148)
            ->showBrowserHeaderAndFooter()
            ->hideFooter()
            ->createPdfCommand('document.pdf');

        $this->assertEquals([
            'url' => 'https://example.com',
            'action' => 'pdf',
            'options' => [
                'path' => 'document.pdf',
                'printBackground' => true,
                'landscape' => true,
                'margin' => ['top' => '10mm', 'right' => '20mm', 'bottom' => '30mm', 'left' => '40mm'],
                'pageRanges' => '1-3',
                'width' => '210mm',
                'height' => '148mm',
                'displayHeaderFooter' => true,
                'footerTemplate' => '<p></p>',
                'viewport' => [
                    'width' => 800,
                    'height' => 600,
                ],
                'args' => [],
            ],
        ], $command);
    }

    /** @test */
    public function it_can_create_a_command_to_generate_a_pdf_with_paper_format()
    {
        $command = Pdf::url('https://example.com')
            ->showBackground()
            ->landscape()
            ->margins(10, 20, 30, 40)
            ->pages('1-3')
            ->format('a4')
            ->createPdfCommand('document.pdf');

        $this->assertEquals([
            'url' => 'https://example.com',
            'action' => 'pdf',
            'options' => [
                'path' => 'document.pdf',
                'printBackground' => true,
                'landscape' => true,
                'margin' => ['top' => '10mm', 'right' => '20mm', 'bottom' => '30mm', 'left' => '40mm'],
                'pageRanges' => '1-3',
                'format' => 'a4',
                'viewport' => [
                    'width' => 800,
                    'height' => 600,
                ],
                'args' => [],
            ],
        ], $command);
    }

    /** @test */
    public function it_can_set_emulate_media_option()
    {
        $command = Pdf::url('https://example.com')
            ->emulateMedia('screen')
            ->createPdfCommand('document.pdf');

        $this->assertEquals([
            'url' => 'https://example.com',
            'action' => 'pdf',
            'options' => [
                'path' => 'document.pdf',
                'viewport' => [
                    'width' => 800,
                    'height' => 600,
                ],
                'emulateMedia' => 'screen',
                'args' => [],
            ],
        ], $command);
    }

    /** @test */
    public function it_can_set_another_node_binary()
    {
        $this->expectException(ProcessFailedException::class);

        $targetPath = __DIR__.'/temp/testDocument.pdf';

        Pdf::html('Foo')
            ->setNodeBinary('non-existant/bin/wich/causes/an/exception')
            ->save($targetPath);
    }

    /** @test */
    public function it_can_set_another_chrome_executable_path()
    {
        $this->expectException(ProcessFailedException::class);

        $targetPath = __DIR__.'/temp/testDocument.pdf';

        Pdf::html('Foo')
            ->setChromePath('non-existant/bin/wich/causes/an/exception')
            ->save($targetPath);
    }

    /** @test */
    // public function it_can_set_the_include_path_and_still_works()
    // {
    //     $targetPath = __DIR__.'/temp/testDocument.pdf';

    //     Pdf::html('Foo')
    //         ->setIncludePath('$PATH:/usr/local/bin:/mypath')
    //         ->save($targetPath);

    //     $this->assertFileExists($targetPath);
    // }

    /** @test */
    public function it_can_run_without_sandbox()
    {
        $command = Pdf::url('https://example.com')
            ->noSandbox()
            ->createPdfCommand('document.pdf');

        $this->assertEquals([
            'url' => 'https://example.com',
            'action' => 'pdf',
            'options' => [
                'viewport' => [
                    'width' => 800,
                    'height' => 600,
                ],
                'args' => [
                    '--no-sandbox',
                ],
            ],
        ], $command);
    }

    /** @test */
    public function it_can_dismiss_dialogs()
    {
        $command = Pdf::url('https://example.com')
            ->dismissDialogs()
            ->createPdfCommand('document.pdf');

        $this->assertEquals([
            'url' => 'https://example.com',
            'action' => 'pdf',
            'options' => [
                'dismissDialogs' => true,
                'path' => 'document.pdf',
                'viewport' => [
                    'width' => 800,
                    'height' => 600,
                ],
                'args' => [],
            ],
        ], $command);
    }

    /** @test */
    public function it_can_ignore_https_errors()
    {
        $command = Pdf::url('https://example.com')
            ->ignoreHttpsErrors()
            ->createPdfCommand('document.pdf');

        $this->assertEquals([
            'url' => 'https://example.com',
            'action' => 'pdf',
            'options' => [
                'ignoreHttpsErrors' => true,
                'path' => 'document.pdf',
                'viewport' => [
                    'width' => 800,
                    'height' => 600,
                ],
                'args' => [],
            ],
        ], $command);
    }

    /** @test */
    public function it_can_use_a_proxy_server()
    {
        $command = Pdf::url('https://example.com')
            ->setProxyServer('1.2.3.4:8080')
            ->createPdfCommand('document.pdf');

        $this->assertEquals([
            'url' => 'https://example.com',
            'action' => 'pdf',
            'options' => [
                'path' => 'document.pdf',
                'viewport' => [
                    'width' => 800,
                    'height' => 600,
                ],
                'args' => ['--proxy-server=1.2.3.4:8080'],
            ],
        ], $command);
    }

    /** @test */
    public function it_can_set_arbitrary_options()
    {
        $command = Pdf::url('https://example.com')
            ->setOption('foo.bar', 100)
            ->setOption('foo.bar', 150)
            ->setOption('foo.baz', 200)
            ->createPdfCommand('document.pdf');

        $this->assertEquals([
            'url' => 'https://example.com',
            'action' => 'pdf',
            'options' => [
                'path' => 'document.pdf',
                'viewport' => [
                    'width' => 800,
                    'height' => 600,
                ],
                'foo' => [
                    'bar' => 150,
                    'baz' => 200,
                ],
                'args' => [],
            ],
        ], $command);
    }

    /** @test */
    // public function it_can_get_the_output_of_a_pdf()
    // {
    //     $output = Pdf::url('https://example.com')
    //         ->pdf();

    //     $finfo = finfo_open();

    //     $mimeType = finfo_buffer($finfo, $output, FILEINFO_MIME_TYPE);

    //     $this->assertEquals($mimeType, 'application/pdf');
    // }

}
