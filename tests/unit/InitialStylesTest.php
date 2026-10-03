<?php

use App\Filters\InitialStylesFilter;
use App\Libraries\InitialStyles;
use CodeIgniter\Test\CIUnitTestCase;

final class InitialStylesTest extends CIUnitTestCase
{
    public function testMovesPageRulesInOriginalOrderWithoutChangingMarkupOrScripts(): void
    {
        $head = '<head><style>.btn{color:red}</style></head>';
        $script = '<script>const sample = "<style>.btn{color:black}</style></head>";</script>';
        $form = '<form method="post"><input name="token" value="keep"><button name="action" value="save">Save</button></form>';
        $one = '<style nonce="keep">.btn{color:green}</style>';
        $two = '<style media="print">.btn{display:none}</style>';
        $html = '<html>' . $head . '<body>' . $script . $form . $one . $two . '</body></html>';
        $expected = '<html><head><style>.btn{color:red}</style>' . $one . $two . '</head><body>' . $script . $form . '</body></html>';
        $this->assertSame($expected, InitialStyles::prepare($html));
        $this->assertSame(strlen($html), strlen($expected));
        $this->assertSame($expected, InitialStyles::prepare($expected));
    }

    public function testKeepsInactiveAndNestedStylesAndFragmentsUntouched(): void
    {
        $nested = '<!-- <style>comment</style> -->'
            . '<template><style>template</style></template>'
            . '<noscript><style>noscript</style></noscript>'
            . '<svg><style>svg</style><circle r="4" /></svg>'
            . '<textarea><style>text</style></textarea>';
        $html = '<html><head></head><body>' . $nested . '</body></html>';
        $this->assertSame($html, InitialStyles::prepare($html));
        $this->assertSame('<style>.x{color:red}</style><div>Fragment</div>', InitialStyles::prepare('<style>.x{color:red}</style><div>Fragment</div>'));
    }

    public function testFilterProcessesHtmlAndPreservesApiResponses(): void
    {
        $filter = new InitialStylesFilter();
        $request = service('request');
        $response = service('response');
        $html = '<html><head></head><body><button>Save</button><style>button{color:red}</style></body></html>';
        $response->setContentType('text/html')->setBody($html);
        $this->assertSame($response, $filter->after($request, $response));
        $this->assertSame(InitialStyles::prepare($html), $response->getBody());
        $json = json_encode(['html' => $html]);
        $response->setContentType('application/json')->setBody($json);
        $filter->after($request, $response);
        $this->assertSame($json, $response->getBody());
        $this->assertContains('initialstyles', config('Filters')->globals['after']);
    }
}
