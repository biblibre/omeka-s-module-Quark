<?php

namespace Quark\Test\Validator;

use Quark\Test\TestCase;
use Quark\Validator\ArkShoulder;

class ArkShoulderTest extends TestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function testValidator($ark, $isValid, $messages = [])
    {
        $validator = new ArkShoulder();
        $this->assertSame($isValid, $validator->isValid($ark));
        $this->assertEquals($messages, $validator->getMessages());
    }

    public function dataProvider()
    {
        $data = [
            [3, false, ['string' => 'value is not a string']],
            ['', false, ['empty' => 'value is empty']],
            ['l', false, ['betanumeric' => "'l' contains non-betanumeric characters"]],
            [' ', false, ['betanumeric' => "' ' contains non-betanumeric characters"]],
            ['b', false, ['end_digit' => "'b' does not end with a digit"]],
            ['9', false, ['start_letter' => "'9' does not start with a letter"]],
            ['x9', true],
        ];

        return $data;
    }
}
