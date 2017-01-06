<?php

use Phinx\Migration\AbstractMigration;

class Init extends AbstractMigration
{
    public function change(){
        $booze = $this->table('booze', ['id' => false, 'primary_key' => ['booze_id']]);
        $booze
            ->addColumn('booze_id', 'integer', ['signed' => false, 'identity' => true])
            ->addColumn('name', 'string')
            ->create()
        ;
    }
}
