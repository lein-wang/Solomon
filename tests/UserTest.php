<?php
/**
 * Created by IntelliJ IDEA.
 * User: new
 * Date: 2018-09-07
 * Time: 16:34
 */

use App\Models\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    /**
     * @dataProvider additionProvider
     */
    public function testGetUserRoles($a, $b, $expected)
    {
        $this->assertEquals($expected, $a + $b);
    }

    public function additionProvider()
    {
        return [
            'adding zeros' => [0, 0, 0],
            'zero plus one' => [0, 1, 1],
            'one plus zero' => [1, 0, 1],
            'one plus one' => [1, 1, 3]
        ];
    }


    public function testReturnArgumentStub()
    {
        // 为 SomeClass 类创建桩件。
        $stub = $this->createMock(User::class);

//        // 配置桩件。
//        $stub->method('getUserRoles')
//            ->will($this->returnArgument(0));

        // $stub->doSomething('foo') 返回 'foo'
        $this->assertEquals(null, $stub->getUserRoles('1'));

        // $stub->doSomething('bar') 返回 'bar'
        $this->assertEquals(null, $stub->getUserRoles('0'));
    }
}
