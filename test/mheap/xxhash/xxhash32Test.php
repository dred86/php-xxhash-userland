<?php

namespace mheap\xxhashTest;

class xxhash32Test extends \PHPUnit_Framework_TestCase {

    /**
     * @dataProvider canBeInitialisedWithASeedProvider
     */
    public function testCanBeInitialisedWithASeed($input, $expected) {
        $hasher = new \mheap\xxhash\xxhash32($input);
        $this->assertEquals($expected, $hasher->getSeed());
    }

    public function canBeInitialisedWithASeedProvider() {
        $r = array();

        $r[] = array(1,1);

        $r[] = array(88,88);


        return $r;
    }

    public function testSmallInputMultipleOfFour() {
        $hasher = new \mheap\xxhash\xxhash32(0);
        $this->assertEquals(1042293711, $hasher->update("abcd")->digest());
    }

}
