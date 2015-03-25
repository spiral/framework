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
use Spiral\Core\Container;
use Spiral\Support\Tests\TestCase;
use Spiral\Support\Validation\Validator;
use Spiral\Tests\MemoryCore;

class AddressCheckerTest extends TestCase
{
    protected function setUp()
    {
        $core = MemoryCore::getInstance()->setConfig('i18n', array(
            'default'   => 'en',
            'plurals'   => 'plural-phrases',
            'languages' => array(
                'en' => array(
                    'dataFolder' => directory('runtime') . '/i18n/english/',
                    'pluralizer' => array(
                        'countForms' => 2,
                        'formula'    => '$number==1?$form[0]:$form[1]'
                    )
                )
            )
        ));

        Container::bind('i18n', new Translator($core));
    }


    protected function tearDown()
    {
        Container::removeBinding('i18n');
    }

    public function testEmail()
    {
        $validator = new Validator(
            array('email' => 'bad'),
            array('email' => array(['email']))
        );

        $this->assertFalse($validator->isValid());

        $this->assertArrayHasKey('email', $validator->getErrors());
        $this->assertSame(
            'Field \'email\' is not a valid email address.',
            $validator->getErrors()['email']
        );

        $validator = new Validator(
            array('email' => 'wolfy.jd@gmail.com'),
            array('email' => array(['email']))
        );

        $this->assertTrue($validator->isValid());

        $validator = new Validator(
            array('email' => 'wolfy.jd@gmail.com'),
            array('email' => array(['address::email']))
        );

        //We relying on filter_var
        $this->assertTrue($validator->isValid());
    }

    public function testFullEmail()
    {
        $validator = new Validator(
            array('email' => 'Wolfy <bad>'),
            array('email' => array(['address::fullEmail']))
        );

        $this->assertFalse($validator->isValid());

        $this->assertArrayHasKey('email', $validator->getErrors());
        $this->assertSame(
            'Field \'email\' is not a valid email address.',
            $validator->getErrors()['email']
        );

        $validator = new Validator(
            array('email' => 'Wolfy wolfy.jd@gmail.com'),
            array('email' => array(['address::fullEmail']))
        );

        $this->assertFalse($validator->isValid());

        $validator = new Validator(
            array('email' => 'Wolfy <wolfy.jd@gmail.com>'),
            array('email' => array(['address::fullEmail']))
        );

        $this->assertTrue($validator->isValid());
    }

    public function testUrl()
    {
        $validator = new Validator(
            array('url' => 'bad'),
            array('url' => array(['url']))
        );

        $this->assertFalse($validator->isValid());

        $this->assertArrayHasKey('url', $validator->getErrors());
        $this->assertSame(
            'Field \'url\' is not a valid URL.',
            $validator->getErrors()['url']
        );

        $validator = new Validator(
            array('url' => 'http://google.com'),
            array('url' => array(['url']))
        );

        $this->assertTrue($validator->isValid());

        $validator = new Validator(
            array('url' => 'http://google.com'),
            array('url' => array(['address::url']))
        );

        //We relying on filter_var
        $this->assertTrue($validator->isValid());
    }

    public function testSoftUrl()
    {
        $validator = new Validator(
            array('url' => 'http://--'),
            array('url' => array(['url', false]))
        );

        $this->assertFalse($validator->isValid());

        $this->assertArrayHasKey('url', $validator->getErrors());
        $this->assertSame(
            'Field \'url\' is not a valid URL.',
            $validator->getErrors()['url']
        );

        $validator = new Validator(
            array('url' => 'http://google.com'),
            array('url' => array(['url', false]))
        );

        $this->assertTrue($validator->isValid());

        $validator = new Validator(
            array('url' => 'google.com'),
            array('url' => array(['address::url', false]))
        );

        //We relying on filter_var
        $this->assertTrue($validator->isValid());

        $validator = new Validator(
            array('url' => '--'),
            array('url' => array(['url', false]))
        );

        $this->assertFalse($validator->isValid());

        $this->assertArrayHasKey('url', $validator->getErrors());
        $this->assertSame(
            'Field \'url\' is not a valid URL.',
            $validator->getErrors()['url']
        );

        $validator = new Validator(
            array('url' => 'google.com'),
            array('url' => array(['url', false]))
        );

        $this->assertTrue($validator->isValid());

        $validator = new Validator(
            array('url' => 'google.com'),
            array('url' => array(['address::url', false]))
        );

        //We relying on filter_var
        $this->assertTrue($validator->isValid());
    }
}