<?php

require __DIR__ . "/Routes/user.php";
require __DIR__ . "/Routes/security.php";
require __DIR__ . "/Routes/toast.php";
require __DIR__ . "/Routes/subjects.php";
require __DIR__ . "/Routes/topics.php";
require __DIR__ . "/Routes/coffee-tea.php";

use Goralys\App\Router\GoralysRouter;

return function (GoralysRouter $router) {
    createUserRoutes($router);
    createSecurityRoutes($router);
    createToastRoutes($router);
    createSubjectsRoutes($router);
    createTopicsRoutes($router);

    createCoffeeTeaRoutes($router);
};
