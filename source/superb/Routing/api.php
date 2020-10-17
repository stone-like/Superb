<?php

use Superb\UserController;

//authの:には参照したいtable名を書く
Router()->group(["middleware" => ["auth:user"]], function () {
    Router()->get("/users", [UserController::class, "run"]);
});
