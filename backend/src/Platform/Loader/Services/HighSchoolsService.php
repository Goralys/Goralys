<?php

namespace Goralys\Platform\Loader\Services;

use Goralys\Shared\Config\GoralysConfig as Config;
use Goralys\Shared\Exception\GoralysRuntimeException;

/**
 * This service is used to load the list of high schools.
 * It is used to determine which database to use.
 * It reads the data from the `Assets/schools_list` file (INI format).
 */
class HighSchoolsService
{
    private array $schools;
    private array $tokenToDb = [];

    /**
     * @throws GoralysRuntimeException
     */
    public function __construct()
    {
        $result = parse_ini_file(Config::DIRECTORIES::ASSETS . "schools_list", true);

        if ($result === false) {
            throw new GoralysRuntimeException("Unable to read or parse schools_list file.");
        }

        $this->schools = $result;

        foreach ($this->schools as $school) {
            $this->tokenToDb[$school["TOKEN"]] = $school["DB"];
        }
    }

    /**
     * Gets the database name for a given school.
     * @param string $token The secret token of the school to get the database for.
     * @return ?string The name of the database to use for the given school.
     */
    public function getDbForSchool(string $token): ?string
    {
        return $this->tokenToDb[$token] ?? null;
    }

    /**
     * Gets the secret token for a given school.
     * @param string $code The code of the school to get the token for.
     * @return ?string The secret token of the school.
     */
    public function getTokenForSchool(string $code): ?string
    {
        return $this->schools[$code]["TOKEN"] ?? null;
    }

    /**
     * Gets the list of all schools' public information.
     * @return array The list of all schools' code associated to their name.
     */
    public function getAllSchools(): array
    {
        return array_map(function ($school) {
            return $school["NAME"];
        }, $this->schools);
    }
}
