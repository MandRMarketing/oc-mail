<?php namespace Mandr\Mail\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateEmailLogTable extends Migration
{

    public function up()
    {
        Schema::create('mandr_mail_email_log', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('code')->nullable();
            $table->text('to');
            $table->text('cc')->nullable();
            $table->text('bcc')->nullable();
            $table->string('subject')->nullable();
            $table->text('body')->nullable();
            $table->string('sender')->nullable();
            $table->string('reply_to')->nullable();
            $table->string('date')->nullable();
            $table->text('response')->nullable();
            $table->boolean('sent')->default(false);
            $table->string('hash');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('mandr_mail_email_log');
    }

}
