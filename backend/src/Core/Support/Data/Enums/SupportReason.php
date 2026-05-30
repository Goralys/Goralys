<?php

namespace Goralys\Core\Support\Data\Enums;

enum SupportReason: string
{
    case PASSWORD_FORGOT = "password-forgot";
    case SUBJECT_ERROR = "subject-error";
    case OTHER = "other";

    /**
     * Creates a SupportReason from a string representation.
     * Returns OTHER if the string does not match any known reason.
     * @param string $str The reason string to convert.
     * @return SupportReason The matching reason.
     */
    public static function fromString(string $str): SupportReason
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
            default => "Autre"
        };
    }
}
