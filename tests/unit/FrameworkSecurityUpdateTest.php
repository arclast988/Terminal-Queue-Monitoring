<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\View\Exceptions\ViewException;
use CodeIgniter\View\Parser;
use CodeIgniter\View\View;
use Config\View as ViewConfig;

/** @internal */
final class FrameworkSecurityUpdateTest extends CIUnitTestCase
{
    public function testSubstitutedParserSyntaxCannotDisableEscaping(): void
    {
        $parser = new Parser(new ViewConfig(), APPPATH . 'Views');
        $parser->setData([
            'label' => '{text|raw}',
            'text' => '<script>alert(1)</script>',
        ]);

        $output = $parser->renderString('{label} {text}');

        $this->assertStringNotContainsString('<script>', $output);
        $this->assertStringContainsString('{text|raw}', $output);
        $this->assertStringContainsString('&lt;script&gt;', $output);
    }

    public function testViewDataCannotReplaceTheTemplateSource(): void
    {
        $view = new View(new ViewConfig(), APPPATH . 'Views');
        $view->setData(['view' => '<?php echo "unexpected-template"; ?>']);

        $this->assertSame('trusted-template', $view->renderString('trusted-template'));

        $parser = new Parser(new ViewConfig(), APPPATH . 'Views');
        $parser->setData(['template' => '<?php echo "unexpected-template"; ?>']);

        $this->assertSame('trusted-template', $parser->renderString('trusted-template'));
    }

    public function testRestrictedParserConditionalsStillAllowComparisons(): void
    {
        $parser = new Parser(new ViewConfig(), APPPATH . 'Views');
        $parser->setData(['seats' => 3]);

        $this->assertSame('available', $parser->renderString('{if $seats > 0}available{else}full{endif}'));
    }

    public function testParserRejectsFunctionCallsInConditionals(): void
    {
        $parser = new Parser(new ViewConfig(), APPPATH . 'Views');
        $this->expectException(ViewException::class);

        $parser->renderString('{if strlen("test") > 0}unexpected{endif}');
    }
}
