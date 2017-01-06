<?php
namespace tests\integration\api;

use tests\BaseTest;

class ApiTest extends BaseTest{
    function setUp(){
        parent::setUp();
    }

    /**
     * @test
     */
    function get(){
        $this->_client->get(
            200,
            $this->_gen->generate('api-init'),
            '{"menu":[{"booze_id":"1","name":"rum"},{"booze_id":"2","name":"whisky"},{"booze_id":"3","name":"gin"},{"booze_id":"4","name":"wine"},{"booze_id":"5","name":"beer"},{"booze_id":"6","name":"tequila"},{"booze_id":"7","name":"baiju"},{"booze_id":"8","name":"vodka"}],"drunk":[],"my-messages":[]}'
        );
    }
}