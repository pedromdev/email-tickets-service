<?php
/**
 * Created by PhpStorm.
 * User: pedromdev
 * Date: 11/08/20
 * Time: 23:34
 */

namespace App\Tests\Builder;


use PHPUnit\Framework\MockObject\MockBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class BaseMockBuilder
 * @package App\Tests\Builder
 *
 * @method self disableOriginalConstructor()
 */
class BaseMockBuilder extends MockBuilder
{

    /**
     * @var TestCase
     */
    protected $testCase;

    /**
     * @var MockObject
     */
    private $mockObject;

    public function __construct(TestCase $testCase, array $methods, $type)
    {
        parent::__construct($testCase, $type);

        $this->testCase = $testCase;

        if (!empty($methods)) {
            $this->setMethods($methods);
        }

        $this->disableOriginalConstructor();
    }

    public function getMock()
    {
        if (!$this->mockObject) {
            $this->mockObject = parent::getMock();
        }

        return $this->mockObject;
    }


}
