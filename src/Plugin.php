<?php

namespace Skillcraft\ContactManager;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Skillcraft\Core\Abstracts\CoreOperationAbstract;

class Plugin extends CoreOperationAbstract
{
    public static function pluginTables():array
    {
        $pluginTables = parent::pluginTables();

        $pluginTables[] = 'contacts_tags';

        return $pluginTables;
    }

    public function pluginInstallSchema():void
    {
        parent::pluginInstallSchema();

        Schema::create('contacts_tags', function (Blueprint $table) {
            $table->id();
            $table->integer('tag_id')->unsigned()->references('id')->on('contact_tags')->onDelete('cascade');
            $table->integer('contact_id')->unsigned()->references('id')->on('contact_manager')->onDelete('cascade');
        });
    }
}
