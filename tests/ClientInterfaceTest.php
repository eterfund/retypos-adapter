<?php
namespace Etersoft\Typos\Tests;

use Etersoft\Typos\TyposArticle;
use My\MyClientInterface;
use PHPUnit\Framework\TestCase;

final class ClientInterfaceTest extends TestCase
{
    /**
     * Tests a TypoClientInterface::replaceTypoInArticle method.
     */
    public function testCorrectRightTypo() {
        $typo = "tpo";
        $corrected = "typo";

        $context = "text contain one tpo. You should fix";
        $text = "<p><b>How many tpo have this text?</b><br/> This text contain one tpo. You should fix them all. <span>Because this tpo is very very bad tpo.</span></p>";

        $expectedText = "<p><b>How many tpo have this text?</b><br/> This text contain one typo. You should fix them all. <span>Because this tpo is very very bad tpo.</span></p>";

        $article = new TyposArticle(0, $text);
        $interface = new MyClientInterface();

        // Test the method replaceTypoInArticle
        $interface->replaceTypoInArticle($typo, $corrected, $context, $article);

        $this->assertEquals($expectedText, $article->text);
    }
}