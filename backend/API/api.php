<?php

return function () {
    require __DIR__ . "/Routes/user.php";
    require __DIR__ . "/Routes/security.php";
    require __DIR__ . "/Routes/toast.php";
    require __DIR__ . "/Routes/subjects.php";
    require __DIR__ . "/Routes/topics.php";
    require __DIR__ . "/Routes/support.php";
    require __DIR__ . "/Routes/high-schools.php";

    if (date("d/m") === "01/04") {
        require __DIR__ . "/Routes/coffee-tea.php";
    }
};
