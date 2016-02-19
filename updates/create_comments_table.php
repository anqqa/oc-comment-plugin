<?php namespace Klubitus\Comment\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateCommentsTable extends Migration {

    public function up() {
        Schema::create('comments', function($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('commentable_id')->unsigned();
            $table->string('commentable_type');
            $table->integer('parent_id')->unsigned()->nullable();
            $table->integer('user_id')->unsigned();
            $table->boolean('is_private')->default(0);
            $table->text('content');
            $table->timestamps();

            $table->index(['commentable_type', 'commentable_id'], 'comments_commentable');
            $table->foreign('parent_id')
                ->references('id')
                ->on('comments')
                ->onDelete('set null');
        });
    }

    public function down() {
//        Schema::dropIfExists('comments');
    }

}
