<?php

namespace Illuminate\Tests\Integration\Database\EloquentModelLoadMaxTest;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Tests\Integration\Database\DatabaseTestCase;

class EloquentModelLoadMaxTest extends DatabaseTestCase
{
    protected function afterRefreshingDatabase()
    {
        Schema::create('base_models', function (Blueprint $table) {
            $table->increments('id');
        });

        Schema::create('related1s', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('base_model_id');
            $table->integer('number');
        });

        Schema::create('related2s', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('base_model_id');
            $table->integer('number');
        });

        BaseModel::create();

        Related1::create(['base_model_id' => 1, 'number' => 10]);
        Related1::create(['base_model_id' => 1, 'number' => 11]);
        Related2::create(['base_model_id' => 1, 'number' => 12]);
        Related2::create(['base_model_id' => 1, 'number' => 13]);
    }

    public function testLoadMaxSingleRelation()
    {
        $model = BaseModel::first();

        DB::enableQueryLog();

        $model->loadMax('related1', 'number');

        $this->assertCount(1, DB::getQueryLog());
        $this->assertEquals(11, $model->related1_max_number);
    }

    public function testLoadMaxMultipleRelations()
    {
        $model = BaseModel::first();

        DB::enableQueryLog();

        $model->loadMax(['related1', 'related2'], 'number');

        $this->assertCount(1, DB::getQueryLog());
        $this->assertEquals(11, $model->related1_max_number);
        $this->assertEquals(13, $model->related2_max_number);
    }
}

class BaseModel extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    public function related1()
    {
        return $this->hasMany(Related1::class);
    }

    public function related2()
    {
        return $this->hasMany(Related2::class);
    }
}

class Related1 extends Model
{
    public $timestamps = false;

    protected $fillable = ['base_model_id', 'number'];

    public function parent()
    {
        return $this->belongsTo(BaseModel::class);
    }
}

class Related2 extends Model
{
    public $timestamps = false;

    protected $fillable = ['base_model_id', 'number'];

    public function parent()
    {
        return $this->belongsTo(BaseModel::class);
    }
}
