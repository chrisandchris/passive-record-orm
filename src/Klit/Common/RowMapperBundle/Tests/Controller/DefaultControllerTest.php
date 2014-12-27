<?php

namespace Klit\Common\RowMapperBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase {
    public function testIndex() {
        // no idea why, but without this line - nothing works anymore
        static::createClient();
    }
}
