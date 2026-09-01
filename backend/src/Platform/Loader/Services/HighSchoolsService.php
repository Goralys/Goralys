<?php

namespace Goralys\Platform\Loader\Services;

use Goralys\Shared\Config\GoralysConfig as Config;
use Goralys\Shared\Exception\GoralysRuntimeException;

/**
 * This service is used to load the list of high schools.
 * It is used to determine which database to use.
 * It reads the data from the `Assets/schools_list` file (INI format).
 */
final class HighSchoolsService
{
    private array $schools;
    private array $tokenToSchool = [];

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
            $this->tokenToSchool[$school["TOKEN"]] = [
                "NAME" => $school["NAME"],
                "DOMAIN" => $school["DOMAIN"],
                "DB" => $school["DB"],
            ];
        }
    }

    /**
     * Gets the database name for a given school.
     * @param string $token The public token of the school to get the database for.
     * @return ?string The name of the database to use for the given school.
     */
    public function getDbForSchool(string $token): ?string
    {
        return $this->tokenToSchool[$token]["DB"] ?? null;
    }

    /**
     * Gets the domain for a given school.
     * @param string $token The public token of the school to get the domain for.
     * @return ?string The domain of the school.
     */
    public function getDomainForSchool(string $token): ?string
    {
        return $this->tokenToSchool[$token]["DOMAIN"] ?? null;
    }

    /**
     * Gets the public token for a given school.
     * @param string $code The code of the school to get the token for.
     * @return ?string The public token of the school.
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
