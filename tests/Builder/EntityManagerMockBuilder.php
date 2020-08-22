<?php
/**
 * Created by PhpStorm.
 * User: pedromdev
 * Date: 11/08/20
 * Time: 23:21
 */

namespace App\Tests\Builder;


use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\MockObject\Matcher\Invocation;
use PHPUnit\Framework\TestCase;

class EntityManagerMockBuilder extends BaseMockBuilder
{

    public function __construct(TestCase $testCase, array $methods, $type = EntityManager::class)
    {
        parent::__construct($testCase, $methods, $type);
    }

    /**
     * @param string $entityClassName
     * @param Invocation $matcher
     * @return $this
     */
    public function persist($entityClassName, Invocation $matcher = null)
    {
        $mockObject = $this->getMock();

        if (is_null($matcher)) {
            $matcher = $this->testCase->once();
        }

        $mockObject->expects($matcher)
            ->method('persist')
            ->with($this->testCase->isInstanceOf($entityClassName));

        return $this;
    }

    /**
     * @param string $entityClassName
     * @param Invocation $matcher
     * @return $this
     */
    public function remove($entityClassName, Invocation $matcher = null)
    {
        $mockObject = $this->getMock();

        if (is_null($matcher)) {
            $matcher = $this->testCase->once();
        }

        $mockObject->expects($matcher)
            ->method('remove')
            ->with($this->testCase->isInstanceOf($entityClassName));

        return $this;
    }

    /**
     * @param Invocation $matcher
     * @return $this
     */
    public function flush(Invocation $matcher = null)
    {
        $mockObject = $this->getMock();

        if (is_null($matcher)) {
            $matcher = $this->testCase->once();
        }

        $mockObject->expects($matcher)
            ->method('flush');

        return $this;
    }


}
