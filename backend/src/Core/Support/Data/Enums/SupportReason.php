<?php

namespace Goralys\Core\Support\Data\Enums;

enum SupportReason: string
{
    case PASSWORD_FORGOT = "password-forgot";
    case SUBJECT_ERROR = "subject-error";
    case PERSONAL_INFO_ERROR = "personal-info-error";
    case OTHER = "other";

    /**
     * Creates a {@see SupportReason} from a string representation.
     * Returns {@see SupportReason::OTHER} if the string does not match any known reason.
     * @param string $str The reason string to convert.
     * @return self The matching reason.
     */
    public static function fromString(string $str): self
    {
        return match (strtolower(trim($str))) {
            "password-forgot" => SupportReason::PASSWORD_FORGOT,
            "subject-error" => SupportReason::SUBJECT_ERROR,
            default => SupportReason::OTHER,
        };
    }

    public static function getDisplay(self $reason): string
    {
        return match ($reason) {
            self::PASSWORD_FORGOT => "Mot de passe oublié",
            self::SUBJECT_ERROR => "Question envoyée/validée/rejetée par erreur",
            self::PERSONAL_INFO_ERROR => "Informations personnelles erronées",
            default => "Autre"
        };
    }
}
