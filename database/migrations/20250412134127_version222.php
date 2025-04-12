<?php

use think\migration\Migrator;

class Version222 extends Migrator
{
    public function up(): void
    {
        // 修复附件表 name 字段长度可能不够的问题
        $attachment = $this->table('attachment');
        $attachment->changeColumn('name', 'string', ['limit' => 120, 'default' => '', 'comment' => '原始名称', 'null' => false])->save();
    }
}
