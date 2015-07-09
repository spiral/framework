<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Tests\Cases\Support\Validation\Checkers;

use Spiral\Components\I18n\Translator;
use Spiral\Core\Configurator;
use Spiral\Core\Container;
use Spiral\Support\Tests\TestCase;
use Spiral\Support\Validation\Validator;
use Spiral\Tests\RuntimeCache;

class AddressCheckerTest extends TestCase
{
    protected function setUp()
    {
        Container::getInstance()->bind(
            'i18n',
            new Translator(new Configurator([
                'i18n' => [
                    'default'   => 'en',
                    'plurals'   => 'plural-phrases',
                    'languages' => [
                        'en' => [
                            'dataFolder' => directory('runtime') . '/i18n/english/',
                            'pluralizer' => 'Spiral\Components\I18n\Pluralizers\EnglishPluralizer'
                        ]
                    ]
                ]
            ]), new RuntimeCache())
        );
    }

    protected function tearDown()
    {
        Container::getInstance()->removeBinding('i18n');
    }

    public function testEmail()
    {
        $validator = new Validator(
            ['email' => 'bad'],
            ['email' => [['email']]]
        );

        $this->assertFalse($validator->isValid());

        $this->assertArrayHasKey('email', $validator->getErrors());

        $this->assertSame(
            'Field \'email\' is not a valid email address.',
            $validator->getErrors()['email']
        );

        $validator = new Validator(
            ['email' => 'wolfy.jd@gmail.com'],
            ['email' => ['email']]
        );

        $this->assertTrue($validator->isValid());

        $validator = new Validator(
            ['email' => 'wolfy.jd@gmail.com'],
            ['email' => ['address::email']]
        );

        //We relying on filter_var
        $this->assertTrue($validator->isValid());
    }

    public function testFullEmail()
    {
        $validator = new Validator(
            ['email' => 'Wolfy <bad>'],
            ['email' => ['address::fullEmail']]
        );

        $this->assertFalse($validator->isValid());

        $this->assertArrayHasKey('email', $validator->getErrors());
        $this->assertSame(
            'Field \'email\' is not a valid email address.',
            $validator->getErrors()['email']
        );

        $validator = new Validator(
            ['email' => 'Wolfy wolfy.jd@gmail.com'],
            ['email' => ['address::fullEmail']]
        );

        $this->assertFalse($validator->isValid());

        $validator = new Validator(
            ['email' => 'Wolfy <wolfy.jd@gmail.com>'],
            ['email' => ['address::fullEmail']]
        );

        $this->assertTrue($validator->isValid());
    }

    public function testUrl()
    {
        $validator = new Validator(
            ['url' => 'bad'],
            ['url' => ['url']]
        );

        $this->assertFalse($validator->isValid());

        $this->assertArrayHasKey('url', $validator->getErrors());
        $this->assertSame(
            'Field \'url\' is not a valid URL.',
            $validator->getErrors()['url']
        );

        $validator = new Validator(
            ['url' => 'http://google.com'],
            ['url' => ['url']]
        );

        $this->assertTrue($validator->isValid());

        $validator = new Validator(
            ['url' => 'http://google.com'],
            ['url' => ['address::url']]
        );

        //We relying on filter_var
        $this->assertTrue($validator->isValid());
    }

    public function testSoftUrl()
    {
        $validator = new Validator(
            ['url' => 'http://--'],
            ['url' => [
                ['url', false]
            ]]
        );

        $this->assertFalse($validator->isValid());

        $this->assertArrayHasKey('url', $validator->getErrors());
        $this->assertSame(
            'Field \'url\' is not a valid URL.',
            $validator->getErrors()['url']
        );

        $validator = new Validator(
            ['url' => 'http://google.com'],
            ['url' => [
                ['url', false]
            ]]
        );

        $this->assertTrue($validator->isValid());

        $validator = new Validator(
            ['url' => 'google.com'],
            ['url' => [
                ['address::url', false]
            ]]
        );

        //We relying on filter_var
        $this->assertTrue($validator->isValid());

        $validator = new Validator(
            ['url' => '--'],
            ['url' => [
                ['url', false]
            ]]
        );

        $this->assertFalse($validator->isValid());

        $this->assertArrayHasKey('url', $validator->getErrors());
        $this->assertSame(
            'Field \'url\' is not a valid URL.',
            $validator->getErrors()['url']
        );

        $validator = new Validator(
            ['url' => 'google.com'],
            ['url' => [
                ['url', false]
            ]]
        );

        $this->assertTrue($validator->isValid());

        $validator = new Validator(
            ['url' => 'google.com'],
            ['url' => [
                ['address::url', false]
            ]]
        );

        //We relying on filter_var
        $this->assertTrue($validator->isValid());
    }
}