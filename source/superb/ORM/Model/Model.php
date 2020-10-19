<?php

namespace Superb\ORM\Model;

use Superb\ORM\EloquentBuilder\EloquentBuilder;
use Superb\Traits\ForwardCallTo;

//きちんとmodelとEloquentBuilderとQueryBuilderで責任を分割する
//modelはUser::findからEloquentBuilderだったり、QueryBuilderに処理を移譲する役
//EloquentBuilderはmodelから渡された値をいろいろいじってQueryBuilderに渡す
//QueryBuilderは$whereとか$limitとかに値をsetして実際にqueryを発火

//ModelからEloquentBuilder、QueryBuilderを生成する際にScopeの関係もあっていろいろ生成methodがあるんだけど今回はScope機能は作らずシンプルに
abstract class Model
{
    use ForwardCallTo;

    //writeConnectionName
    protected $connection;

    protected $table;
    protected $with = [];
    protected $primaryKey = "id";
    protected $primaryKeyType = "int";
    protected $isAutoIncrement = true;


    public function __construct()
    {
        //ここで$connectionをconfigからとってくればいいかも？

    }

    public function newQuery()
    {
        return $this->newModelQuery()->with();
    }

    public function newModelQuery()
    {
        return $this->newEloquentBuilder(
            $this->newQueryBuilder()
        )->setModel($this); //setModelで後にtable情報とか取ってこれる、後はconnectionを取る方法も調べておく、多分configだけど
    }



    public function newQueryBuilder()
    {
        return $this->getConnection()->query();
    }

    public function newEloquentBuilder($query)
    {
        return new EloquentBuilder($query);
    }

    public function getConnection()
    {
        //ここでconnectionNameからConnectionクラスでPDOを生成する、これも移譲の発想？
        //ここでConnectionInterfaceとかにすればテストしやすそう？
        return $this->connection;
    }

    public function getConnectionName()
    {
        return $this->connection;
    }

    public function _call($method, $parameters)
    {

        //forwardCallToで第一引数のclassへ処理を移譲(今回はEloquentBuilder)
        return $this->forwardCallTo($this->newQuery(), $method, $parameters);
    }

    public static function __callStatic($method, $parameters)
    {
        //ここでconstructorが呼ばれるのでUser::でも初期化がされることになる
        return (new static)->$method(...$parameters);
    }
}
