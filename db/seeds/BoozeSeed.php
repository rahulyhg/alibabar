<?php

use Phinx\Seed\AbstractSeed;

class BoozeSeed extends AbstractSeed
{
    public function run()
    {

        $data = [
            ['booze_id' => null, 'name' => 'rum'],
            ['booze_id' => null, 'name' => 'whisky'],
            ['booze_id' => null, 'name' => 'gin'],
            ['booze_id' => null, 'name' => 'wine'],
            ['booze_id' => null, 'name' => 'beer'],
            ['booze_id' => null, 'name' => 'tequila'],
            ['booze_id' => null, 'name' => 'baiju'],
            ['booze_id' => null, 'name' => 'vodka'],
        ];
        $booze = $this->table('booze');
        $booze->insert($data)->save();
    }
}
