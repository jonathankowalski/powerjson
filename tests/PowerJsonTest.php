<?php

use PHPUnit\Framework\TestCase;
use PowerJson\PowerJson;

class PowerJsonTest extends TestCase
{
    public function testReturns()
    {
        $pj = new PowerJson();
        $this->assertInstanceOf('PowerJson\PowerJson', $pj->contexts([]));
        $this->assertInstanceOf('PowerJson\PowerJson', $pj->context(__DIR__));
        $this->assertInstanceOf('PowerJson\PowerJson', $pj->assign('', ''));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testDecodeException()
    {
        $pj = new PowerJson();
        $pj->decode();
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testDirException()
    {
        $pj = new PowerJson();
        $pj->context('');
    }

    public function testVariable()
    {
        $pj = new PowerJson();
        $pj->context(__DIR__);
        $pj->assign('var', 'testrock');
        $content = $pj->generate(__DIR__ . '/sample.json')->output();
        $this->assertJson($content);
        $this->assertContains('testrock', $content);
    }

    public function testExpectedResult()
    {
        $pj = new PowerJson();
        $pj->context(__DIR__);
        $pj->assign('var', 'testrock');
        $pj->generate(__DIR__ . '/sample.json');
        $expectedContent = json_decode(file_get_contents(__DIR__ . '/expected.json'), true);
        $this->assertEquals($expectedContent, $pj->decode());
        $this->assertJson($pj->output());
    }
}