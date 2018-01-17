<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHd10UpgradeFileInfoTable extends Migration
{


    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hd10_upgrade_file_info', function (Blueprint $table) {
            $table->uuid('id',36)->comment('升级表ID');
            $table->string('file_id',200)->comment('文件id');
            $table->integer('package_number',FALSE,TRUE)->default(0)->comment('包序号');
            $table->integer('packet_size',FALSE,TRUE)->default(0)->comment('单数据包长度');
            $table->text('content')->comment('数据内容,base64_encode数据');

            $table->timestamps();
            //$table->softDeletes();

            $table->primary('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hd10_upgrade_file_info');
    }
}
